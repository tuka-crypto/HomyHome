<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
    protected $fillable = [
        'apartment_id',
        'tenant_id',
        'start_date',
        'end_date',
        'total_price',
        'status',
        'guest_count',
        'owner_approved',
        'booking_number',
    ];

    // العلاقة مع الشقة
    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }

    // العلاقة مع المستأجر (المستخدم)
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }
}

