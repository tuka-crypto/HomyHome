<?php

namespace Database\Seeders;

use App\Models\Apartment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Apartment::create([
            'owner_id'=>2,
            'city'=>'damascus',
            'country'=>'syria',
            'address'=>'almalki street',
            'price'=>150.00,
            'discreption'=>'a nice apartment with a nice decoration',
            'is_available'=>true,
            'number_of_room'=>3,
            'space'=>120,
            'status'=>'pending',
        ]);
    }
}
