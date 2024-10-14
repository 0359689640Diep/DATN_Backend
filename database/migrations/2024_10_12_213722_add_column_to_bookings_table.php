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
        Schema::table('bookings', function (Blueprint $table) {
            // **Cọc tiền**
            $table->integer('deposit_amount')->nullable(); // Số tiền cọc
            $table->string('deposit_status')->default('pending'); // Trạng thái cọc: 'pending', 'paid', 'refunded'
            $table->timestamp('deposit_date')->nullable(); // Ngày cọc tiền
            $table->timestamp('deposit_refund_date')->nullable(); // Ngày hoàn cọc (nếu có)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('deposit_amount');
            $table->dropColumn('deposit_status');
            $table->dropColumn('deposit_date'); 
            $table->dropColumn('deposit_refund_date');  
        });
    }
};
