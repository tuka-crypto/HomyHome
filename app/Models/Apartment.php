<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $fillable = [
        'owner_id',
        'city',
        'country',
        'address',
        'price',
        'description',
        'is_available',
        'number_of_room',
        'status',
        'space'
    ];
    /** @use HasFactory<\Database\Factories\ApartmentFactory> */
    use HasFactory;
    function bookings(){
        return $this->hasMany(Booking::class,'apartment_id');
    }
    function reviews(){
        return $this->hasMany(Review::class,'apartment_id');
    }
    public function images()
{
    return $this->hasMany(Apartment_image::class);
}
public function owner()
{
    return $this->belongsTo(User::class, 'owner_id');
}

}
