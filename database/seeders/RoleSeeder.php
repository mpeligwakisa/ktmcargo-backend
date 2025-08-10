<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        DB::table('roles')->insert([
            ['name' => 'admin'],
            ['name' => 'china_user'],
        ]);
    }
}