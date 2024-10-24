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
        Schema::create('booking_service_users', function (Blueprint $table) {
            $table->id();
            $table->integer("booking_service_id");
            $table->integer("user_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_service_users', function (Blueprint $table) {
            Schema::dropIfExists('booking_service_users');
        });
    }
};
