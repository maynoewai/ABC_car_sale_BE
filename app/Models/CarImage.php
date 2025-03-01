<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'url',
        'public_id' // For Cloudinary image management
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}