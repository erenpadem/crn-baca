<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puantaj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personel_id')->constrained('personel')->cascadeOnDelete();
            $table->date('tarih');
            $table->string('durum', 20)->default('tam_gun'); // tam_gun, yarim_gun, gelmedi
            $table->text('aciklama')->nullable();
            $table->text('notlar')->nullable();
            $table->time('giris_saati')->nullable();
            $table->time('cikis_saati')->nullable();
            $table->timestamps();

            $table->unique(['personel_id', 'tarih']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puantaj');
    }
};
