<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->decimal('harga_per_jam', 15, 0)->nullable()->after('harga_sewa');
            $table->integer('minimum_jam')->default(2)->after('harga_per_jam');
        });

        DB::statement('UPDATE gedung SET harga_per_jam = ROUND(harga_sewa / 8), minimum_jam = 2 WHERE harga_per_jam IS NULL');
    }

    public function down(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->dropColumn(['harga_per_jam', 'minimum_jam']);
        });
    }
};
