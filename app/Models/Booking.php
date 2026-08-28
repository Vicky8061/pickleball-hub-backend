<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'court_id',
        'time_slot_id',
        'booking_date',
        'court_price',
        'platform_fee',
        'admin_commission_rate',
        'admin_commission_amount',
        'owner_payout_amount',
        'total_amount',
        'payment_status',
        'booking_status',
    ];

    protected $casts = [
        'court_price' => 'float',
        'platform_fee' => 'float',
        'admin_commission_rate' => 'float',
        'admin_commission_amount' => 'float',
        'owner_payout_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
