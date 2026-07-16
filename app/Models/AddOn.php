<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddOn extends Model
{
    use HasFactory;

    protected $table = 'add_ons';

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_add_ons')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
