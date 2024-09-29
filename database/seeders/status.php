<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class status extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('status')->insert(
            [
                ["name" => "Hoạt động", "type" => "1", "color" => "#28a745"],
                ["name" => "Ngưng hoạt động", "type" => "1", "color" => "#dc3545"],
            ]
        );
    }
}
