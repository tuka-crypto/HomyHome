<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name'   => 'karam',
            'last_name'    => 'alahmad',
            'mobile_phone' => '0937178880',
            'password'     => Hash::make('admin123'),
            'role'         => 'admin',
            'is_approved'  => true,
            'date_of_birth'=> '1990-01-01',
        ]);

    }
}
