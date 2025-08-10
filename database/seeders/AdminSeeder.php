<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@ktmcargo.com',
            'password' => Hash::make('admin123'), // Secure password
            'role' => 'admin', // Ensure your users table has a "role" column
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}