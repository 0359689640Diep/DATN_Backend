<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class email extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('email')->insert([
            ["content" => "Xin chào mã xác nhận của bạn là:", "type" => 1],
            ["content" => "Chúc mừng bạn đã đăng ký thành công!", "type" => 1]
        ]);
    }
}
    