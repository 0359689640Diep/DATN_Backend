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
        Schema::table('rooms_type', function (Blueprint $table) {
            $table->string("title")->nullable();
            $table->text("description_detail")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms_type', function (Blueprint $table) {
            $table->dropColumn("title");
            $table->dropColumn("description_detail");
        });
    }
};
