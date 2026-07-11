<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'UP1 Admin',
            'email' => 'up1',
            'password' => Hash::make('147235689'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }
}
