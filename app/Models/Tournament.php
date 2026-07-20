<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'court_id',
        'title',
        'description',
        'banner',
        'tournament_date',
        'registration_last_date',
        'start_time',
        'end_time',
        'entry_fee',
        'max_participants',
        'prize',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class,'owner_id');
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
    public function participants(){
        return $this->hasMany(TournamentParticipant::class);
    }
}
