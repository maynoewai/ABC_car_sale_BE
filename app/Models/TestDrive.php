<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestDrive extends Model
{
    protected $fillable = ['car_id', 'scheduled_time', 'status'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
