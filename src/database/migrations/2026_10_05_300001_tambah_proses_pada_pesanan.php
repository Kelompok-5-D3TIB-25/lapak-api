<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $tabel) {
            $tabel->timestamp('dikemas_pada')->nullable()->after('dibayar_pada');
            $tabel->timestamp('dikirim_pada')->nullable()->after('dikemas_pada');
            $tabel->string('nomor_resi', 60)->nullable()->after('dikirim_pada');
            $tabel->timestamp('selesai_pada')->nullable()->after('nomor_resi');
            $tabel->string('dibatalkan_oleh', 20)->nullable()->after('dibatalkan_pada');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $tabel) {
            $tabel->dropColumn(['dikemas_pada', 'dikirim_pada', 'nomor_resi', 'selesai_pada', 'dibatalkan_oleh']);
        });
    }
};