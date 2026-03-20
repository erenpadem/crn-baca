<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, satis, musteri, vb.
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('firma_no', 50)->unique()->nullable();
            $table->string('unvan');
            $table->text('adres')->nullable();
            $table->string('il_ilce')->nullable();
            $table->string('ilgili_kisi')->nullable();
            $table->string('tel')->nullable();
            $table->string('tel_2')->nullable();
            $table->string('mail')->nullable();
            $table->text('sevk_adresi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('dealers');
    }
};
