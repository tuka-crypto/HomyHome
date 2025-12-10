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
            'mobile_phone' => '0963937178880',
            'password'     => Hash::make('admin123'),
            'role'         => 'admin',
            'is_approved'  => true,
            'date_of_birth'=> '1990-01-01',
        ]);
         User::create([
            'first_name'   => 'tasnim',
            'last_name'    => 'homsii',
            'mobile_phone' => '0963938349118',
            'password'     => Hash::make('tasnim1234'),
            'role'         => 'owner',
            'is_approved'  => false,
            'date_of_birth'=> '2005-01-01',
        ]);
        


    }
}
