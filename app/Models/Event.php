<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'organizer_id', 'category_id', 'title', 'description', 'image',
        'location', 'start_date', 'end_date', 'status', 'capacity', 'is_public'
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function registrationsCount()
    {
        return Registration::whereHas('ticket', function ($q) {
            $q->where('event_id', $this->id);
        })->count();
    }

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function registrations()
    {
        return $this->hasManyThrough(Registration::class, Ticket::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'event_favorites')->withTimestamps();
    }
}
