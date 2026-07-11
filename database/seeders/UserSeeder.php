<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'up1'],
            [
                'name' => 'UP1 Admin',
                'email' => 'up1',
                'password' => Hash::make('147235689'),
                'is_admin' => true,
            ]
        );

        // Create business owner user
        User::updateOrCreate(
            ['phone' => '0600622677'],
            [
                'name' => 'Business Owner',
                'email' => 'business@up1.test',
                'phone' => '0600622677',
                'password' => Hash::make('147235689'),
                'is_admin' => false,
            ]
        );
    }
}
