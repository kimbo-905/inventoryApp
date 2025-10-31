<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1 // Assuming Admin is ID 1
           //token = 1|061O3ar13JzexNN7jZvTwpJKvLWzRQZU1p9T9e4ic4898eb2
        ]);
    }
}
