<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Role_3',
                'username' => 'role_3',
                'email' => 'role3@gmail.com',
                // 'password' => bcrypt('admin'),
                'password' => Hash::make(111),
                'role' => 2,
                'status' => 1,
            ],
        ]);
    }
}