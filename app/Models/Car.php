<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'title',
        'make',
        'model',
        'year',
        'price',
        'description',
        'mileage',
        'mileage_unit',
        'fuel_type',
        'transmission',
        'owner_number',
        'color',
        'body_type',
        'location',
        'registration_year',
        'insurance_validity',
        'engine_cc',
        'variant',
        'power_windows',
        'abs',
        'airbags',
        'sunroof',
        'navigation',
        'rear_camera',
        'leather_seats',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class)->with('user');
    }

    public function testDrives()
    {
        return $this->hasMany(TestDrive::class);
        
    }

    public function scopeFilter($query, array $filters = [])
    {
        return $query
            ->when($filters['make'] ?? false, fn($q, $make) =>
                $q->where('make', 'ILIKE', "%$make%"))
            ->when($filters['model'] ?? false, fn($q, $model) =>
                $q->where('model', 'ILIKE', "%$model%"))
            ->when($filters['min_year'] ?? false, fn($q, $year) =>
                $q->where('year', '>=', $year))
            ->when($filters['max_year'] ?? false, fn($q, $year) =>
                $q->where('year', '<=', $year))
            // Instead of a combined price_range, use separate min_price and max_price filters:
            ->when($filters['min_price'] ?? false, fn($q, $minPrice) =>
                $q->where('price', '>=', $minPrice))
            ->when($filters['max_price'] ?? false, fn($q, $maxPrice) =>
                $q->where('price', '<=', $maxPrice))
            ->when($filters['fuel_type'] ?? false, fn($q, $fuel) =>
                $q->where('fuel_type', $fuel))
            ->when($filters['transmission'] ?? false, fn($q, $trans) =>
                $q->where('transmission', $trans))
            ->when($filters['body_type'] ?? false, fn($q, $body) =>
                $q->where('body_type', $body))
            ->when($filters['min_mileage'] ?? false, fn($q, $mileage) =>
                $q->where('mileage', '>=', $mileage))
            // Features filter: ensure features is an array, then add a condition for each feature.
            ->when($filters['features'] ?? false, function($q, $features) {
                if (!is_array($features)) {
                    $features = explode(',', $features);
                }
                foreach ($features as $feature) {
                    switch (strtolower(trim($feature))) {
                        case 'abs':
                            $q->where('abs', true);
                            break;
                        case 'airbags':
                            $q->where('airbags', true);
                            break;
                        case 'sunroof':
                            $q->where('sunroof', true);
                            break;
                        case 'navigation':
                            $q->where('navigation', true);
                            break;
                    }
                }
                return $q;
            })
            ->when($filters['color'] ?? false, fn($q, $color) =>
                $q->where('color', $color))
            ->when($filters['owner_number'] ?? false, fn($q, $owner) =>
                $q->where('owner_number', $owner));
    }
    
    
    

    // Additional scopes for sorting
    public function scopeNewest($query)
    {
        return $query->orderBy('year', 'desc');
    }

    public function scopePriceLowToHigh($query)
    {
        return $query->orderBy('price');
    }

    public function scopePriceHighToLow($query)
    {
        return $query->orderBy('price', 'desc');
    }

    public function scopeMileageLowToHigh($query)
    {
        return $query->orderBy('mileage');
    }

}