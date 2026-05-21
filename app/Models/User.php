<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'last_login_ip',
        'suspended_until',
        'credits',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'suspended_until' => 'datetime',
        ];
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'attendee_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'attendee_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function favoriteEvents()
    {
        return $this->belongsToMany(Event::class, 'event_favorites')->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function addCredits($amount)
    {
        $this->credits += $amount;
        $this->save();
    }

    public function deductCredits($amount)
    {
        if ($this->credits < $amount) {
            throw new \Exception("Insufficient balance");
        }
        $this->credits -= $amount;
        $this->save();
    }
}
