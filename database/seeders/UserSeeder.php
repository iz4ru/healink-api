<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Satria Admin',
            'email' => 'admin@healink.test',
            'username' => 'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '080000000000',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        // Owner
        User::create([
            'name' => 'Satria Owner',
            'email' => 'owner@healink.test',
            'username' => 'owner',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '081200000001',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        // Cashier
        User::create([
            'name' => 'Satria Cashier',
            'email' => 'cashier@healink.test',
            'username' => 'cashier',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'phone' => '081200000002',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        // Cashier 2
        User::create([
            'name' => 'Satria Cashier 2',
            'email' => 'cashier2@healink.test',
            'username' => 'cashier2',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'phone' => '081200000003',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);
    }
}
