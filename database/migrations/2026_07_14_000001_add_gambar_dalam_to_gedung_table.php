<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->string('gambar_dalam')->nullable()->after('gambar');
            $table->text('deskripsi_dalam')->nullable()->after('gambar_dalam');
        });
    }

    public function down(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->dropColumn(['gambar_dalam', 'deskripsi_dalam']);
        });
    }
};
