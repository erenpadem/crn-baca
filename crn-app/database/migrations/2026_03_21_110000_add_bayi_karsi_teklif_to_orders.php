<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('bayi_karsi_iskonto_yuzde', 5, 2)->nullable()->after('bayiye_fiyat_goster');
            $table->text('bayi_karsi_not')->nullable()->after('bayi_karsi_iskonto_yuzde');
            $table->timestamp('bayi_karsi_gonderim_at')->nullable()->after('bayi_karsi_not');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('bayi_karsi_birim_fiyat', 12, 4)->nullable()->after('tutar');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'bayi_karsi_iskonto_yuzde',
                'bayi_karsi_not',
                'bayi_karsi_gonderim_at',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('bayi_karsi_birim_fiyat');
        });
    }
};
