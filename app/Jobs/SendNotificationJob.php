<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $notificationId;

    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    public function handle(): void
    {
        $notification = Notification::query()->whereKey($this->notificationId)->first();

        if (!$notification || $notification->status === Notification::STATUS_SENT) {
            return;
        }

        Log::info('Queued notification', [
            'id' => $notification->id,
            'channel' => $notification->channel,
            'recipient' => $notification->recipient,
            'template' => $notification->template,
            'payload' => $notification->payload_json,
        ]);

        $notification->status = Notification::STATUS_SENT;
        $notification->sent_at = now();
        $notification->save();
    }
}
