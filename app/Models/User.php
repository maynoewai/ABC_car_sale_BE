<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $fillable = ['name', 'email', 'password', 'role'];

    public function cars() 
    {
        return $this->hasMany(Car::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        // Assumes you have a 'role' column in your users table.
        return $this->role === $role;
    }

    public function testDrives()
{
    return $this->hasMany(TestDrive::class);
}
}