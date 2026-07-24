<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasFactory;


    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'price_per_hour',
        'court_type',
        'opening_time',
        'closing_time',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function images()
    {
        return $this->hasMany(CourtImage::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}
