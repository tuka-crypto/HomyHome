<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment_image extends Model
{
    /** @use HasFactory<\Database\Factories\ApartmentImageFactory> */
    use HasFactory;
    protected $fillable = ['apartment_id', 'image_path'];
    public function apartment()
{
    return $this->belongsTo(Apartment::class);
}

}
