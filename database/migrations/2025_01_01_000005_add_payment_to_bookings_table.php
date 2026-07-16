<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_code', 20)->unique()->after('id');
            $table->enum('payment_type', ['dp', 'lunas'])->after('total_harga');
            $table->string('payment_status', 20)->default('unpaid')->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_code', 'payment_type', 'payment_status']);
        });
    }
};
