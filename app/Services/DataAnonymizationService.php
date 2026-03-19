<?php

namespace App\Services;

use App\Models\Child;
use App\Models\GdprSnapshot;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DataAnonymizationService
{
    public function run(int $batchSize = 200): array
    {
        $cutoff = Carbon::now()->subDays((int) config('libver.data_retention_days', 730));

        $children = $this->anonymizeChildren($cutoff, $batchSize);
        $users = $this->anonymizeUsers($cutoff, $batchSize);
        $registrations = $this->markRegistrations($cutoff);

        return [
            'children' => $children,
            'users' => $users,
            'registrations' => $registrations,
        ];
    }

    private function anonymizeChildren(Carbon $cutoff, int $limit): int
    {
        $children = Child::query()
            ->whereNull('anonymized_at')
            ->where('updated_at', '<', $cutoff)
            ->whereDoesntHave('registrations', function ($query) use ($cutoff) {
                $query->where('created_at', '>=', $cutoff);
            })
            ->whereDoesntHave('registrations.activity', function ($query) use ($cutoff) {
                $query->where(function ($inner) use ($cutoff) {
                    $inner->whereNull('end_at')
                          ->orWhere('end_at', '>=', $cutoff);
                });
            })
            ->withCount('registrations')
            ->with(['registrations' => function ($query) {
                $query->select('id', 'child_id', 'created_at')->latest('created_at')->limit(1);
            }])
            ->limit($limit)
            ->get();

        $count = 0;
        $now = Carbon::now();

        foreach ($children as $child) {
            DB::transaction(function () use ($child, $now, &$count) {
                $lastRegistration = $child->registrations->first();

                GdprSnapshot::query()->create([
                    'entity_type' => 'child',
                    'entity_id' => $child->id,
                    'payload' => [
                        'age_on_anonymization' => $child->dob ? $child->dob->diffInYears($now) : null,
                        'absence_count' => $child->absence_count,
                        'loyalty_points' => $child->loyalty_points,
                        'last_registration_at' => optional($lastRegistration?->created_at)->toIso8601String(),
                        'tags' => $child->tags,
                    ],
                    'captured_at' => $now,
                ]);

                $child->forceFill([
                    'first_name' => 'Anonymized',
                    'last_name' => 'Child',
                    'phone_emergency' => null,
                    'dob' => null,
                    'tags' => [],
                    'anonymized_at' => $now,
                ])->save();

                $count++;
            });
        }

        return $count;
    }

    private function anonymizeUsers(Carbon $cutoff, int $limit): int
    {
        $users = User::query()
            ->where('role', User::ROLE_PARENT)
            ->whereNull('anonymized_at')
            ->where('updated_at', '<', $cutoff)
            ->whereDoesntHave('registrations', function ($query) use ($cutoff) {
                $query->where('created_at', '>=', $cutoff)
                      ->whereNotIn('status', [Registration::STATUS_CANCELED]);
            })
            ->whereDoesntHave('registrations.activity', function ($query) use ($cutoff) {
                $query->where(function ($inner) use ($cutoff) {
                    $inner->whereNull('end_at')
                          ->orWhere('end_at', '>=', $cutoff);
                });
            })
            ->whereDoesntHave('children', function ($query) {
                $query->whereNull('anonymized_at');
            })
            ->withCount(['children', 'registrations'])
            ->limit($limit)
            ->get();

        $count = 0;
        $now = Carbon::now();

        foreach ($users as $user) {
            DB::transaction(function () use ($user, $now, &$count) {
                GdprSnapshot::query()->create([
                    'entity_type' => 'user',
                    'entity_id' => $user->id,
                    'payload' => [
                        'children_count' => $user->children_count,
                        'registrations_count' => $user->registrations_count,
                        'created_at' => optional($user->created_at)->toIso8601String(),
                    ],
                    'captured_at' => $now,
                ]);

                $placeholderEmail = 'anonymized+' . $user->id . '@anon.libver';

                $user->forceFill([
                    'name' => 'Anonymized',
                    'surname' => null,
                    'email' => $placeholderEmail,
                    'phone' => null,
                    'card_number' => null,
                    'dob' => null,
                    'password' => Hash::make(Str::random(32)),
                    'remember_token' => null,
                    'anonymized_at' => $now,
                ])->save();

                $count++;
            });
        }

        return $count;
    }

    private function markRegistrations(Carbon $cutoff): int
    {
        $query = Registration::query()
            ->whereNull('anonymized_at')
            ->where('created_at', '<', $cutoff)
            ->whereHas('activity', function ($activityQuery) use ($cutoff) {
                $activityQuery->where(function ($inner) use ($cutoff) {
                    $inner->whereNull('end_at')->orWhere('end_at', '<', $cutoff);
                });
            });

        return $query->update([
            'anonymized_at' => Carbon::now(),
            'check_in_token' => null,
        ]);
    }
}
