<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("rooms", function(Blueprint $table){
            $table->id();
            $table->integer("room_type_id");
            $table->integer("number");
            $table->integer("price_per_night");
            $table->string("description")->default('null');
            $table->integer('defaul_people');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("rooms");
    }
};
