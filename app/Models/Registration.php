<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = ['attendee_id', 'ticket_id', 'status'];

    public function attendee()
    {
        return $this->belongsTo(User::class, 'attendee_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
