<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ResetPassword;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_INSTRUCTOR = 'instructor';
    public const ROLE_PARENT = 'parent';

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'role',
        'newsletter_subscribed',
        'newsletter_subscribed_at',
        'phone',
        'dob',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'newsletter_subscribed' => 'boolean',
        'newsletter_subscribed_at' => 'datetime',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'owner_id')->where('owner_type', 'user');
    }

    /**
     * Send a password reset notification to the user.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
