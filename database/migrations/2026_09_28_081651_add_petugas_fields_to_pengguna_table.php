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
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('nik')->unique()->nullable()->after('id');
            $table->string('nip')->unique()->nullable()->after('nik');
            $table->date('tanggal_lahir')->nullable()->after('nama');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('no_hp')->nullable()->after('alamat');
            $table->string('jabatan')->nullable()->after('no_hp'); // Dokter Umum, Perawat, dll
            
            // Karena login pakai NIK/NIP, email bisa jadi nullable
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['nik', 'nip', 'tanggal_lahir', 'alamat', 'no_hp', 'jabatan']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
