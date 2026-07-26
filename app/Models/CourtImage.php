<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CourtImage extends Model
{
    use HasFactory;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'court_id',
        'image',
        'is_primary',
    ];

    /**
     * Automatically append image_url in API response
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * Court Relationship
     */
    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get Full Image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }

        return null;
    }
}