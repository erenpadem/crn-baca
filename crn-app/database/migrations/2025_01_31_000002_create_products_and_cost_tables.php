<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_params', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // SAC FİYATI, MAŞON FİYATI, TUTACAK FİYATI
            $table->string('key')->unique(); // sac_fiyati, mason_fiyati, tutacak_fiyati
            $table->decimal('value', 12, 4)->default(0);
            $table->string('unit', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('malzeme_kodu', 100)->unique();
            $table->string('malzeme_aciklamasi');
            $table->decimal('uzunluk_m', 10, 4)->nullable();
            $table->decimal('sac_kalinlik', 10, 4)->nullable();
            $table->decimal('birim_kilo', 10, 4)->nullable();
            $table->string('birim', 20)->default('AD');
            $table->decimal('sac_fiyati', 12, 4)->nullable();
            $table->decimal('izole_fiyati', 12, 4)->nullable();
            $table->decimal('kilif_430_fiyati', 12, 4)->nullable();
            $table->decimal('fiyat_liste', 12, 4)->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('cost_params');
    }
};
