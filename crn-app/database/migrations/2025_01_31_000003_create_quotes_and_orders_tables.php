<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('teklif_no', 50)->unique();
            $table->foreignId('dealer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('durum', 30)->default('taslak'); // taslak, gonderildi, onaylandi, reddedildi
            $table->decimal('musteri_iskonto_yuzde', 5, 2)->nullable();
            $table->text('musteri_not')->nullable();
            $table->timestamp('gonderim_tarihi')->nullable();
            $table->timestamp('musteri_yanit_tarihi')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('birim_fiyat', 12, 4);
            $table->decimal('adet', 12, 4)->default(1);
            $table->decimal('musteri_birim_fiyat', 12, 4)->nullable();
            $table->decimal('tutar', 12, 4)->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('siparis_no', 50)->unique();
            $table->foreignId('dealer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('siparis_tarihi');
            $table->string('proje_adi')->nullable();
            $table->string('cihaz_marka_model')->nullable();
            $table->string('durum', 30)->default('beklemede');
            $table->decimal('iskonto_yuzde', 5, 2)->nullable();
            $table->text('aciklama')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('birim_fiyat', 12, 4);
            $table->decimal('adet', 12, 4)->default(1);
            $table->decimal('tutar', 12, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
