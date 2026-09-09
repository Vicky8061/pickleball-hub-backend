<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'payout_status',
        'payout_tx_reference',
        'payout_method',
        'payout_notes',
        'payout_settlement_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'payout_settlement_date' => 'datetime',
        ];
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'owner_id');
    }

    public function tournamentParticipants(): HasMany
    {
        return $this->hasMany(TournamentParticipant::class);
    }
    public function ownerApplications()
    {
        return $this->hasMany(OwnerApplication::class);
    }
}
