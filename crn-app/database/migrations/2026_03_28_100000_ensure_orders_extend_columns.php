<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eski / yarım veritabanlarında eksik orders ve order_items sütunlarını ekler.
 * Sütun sırası önemli değil; after() kullanılmaz (MySQL sona ekler).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $addOrders = function (string $column, callable $def): void {
            if (! Schema::hasColumn('orders', $column)) {
                Schema::table('orders', function (Blueprint $table) use ($column, $def) {
                    $def($table, $column);
                });
            }
        };

        $addOrders('on_siparis_no', fn (Blueprint $t, string $c) => $t->string($c, 50)->nullable());
        $addOrders('bac_cap_mm', fn (Blueprint $t, string $c) => $t->decimal($c, 10, 2)->nullable());
        $addOrders('bac_yukseklik_mm', fn (Blueprint $t, string $c) => $t->decimal($c, 10, 2)->nullable());
        $addOrders('yon', fn (Blueprint $t, string $c) => $t->string($c, 10)->nullable());

        foreach (['attr_n', 'attr_m', 'attr_a', 'attr_h', 'attr_di'] as $c) {
            $addOrders($c, fn (Blueprint $t, string $col) => $t->boolean($col)->default(false));
        }

        $addOrders('kur', fn (Blueprint $t, string $c) => $t->decimal($c, 14, 4)->nullable());
        $addOrders('kur_farki_yuzde', fn (Blueprint $t, string $c) => $t->decimal($c, 5, 2)->nullable()->default(10));
        $addOrders('tutar_kdvsiz_on', fn (Blueprint $t, string $c) => $t->decimal($c, 16, 4)->nullable());
        $addOrders('is_manual_on', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));
        $addOrders('tutar_kdvsiz_nihai', fn (Blueprint $t, string $c) => $t->decimal($c, 16, 4)->nullable());
        $addOrders('is_manual_nihai', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));

        $addOrders('opsiyonel_nakliye', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));
        $addOrders('nakliye_tutari', fn (Blueprint $t, string $c) => $t->decimal($c, 16, 4)->nullable());
        $addOrders('opsiyonel_akreditif', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));
        $addOrders('akreditif_tutari', fn (Blueprint $t, string $c) => $t->decimal($c, 16, 4)->nullable());
        $addOrders('opsiyonel_montaj', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));
        $addOrders('montaj_tutari', fn (Blueprint $t, string $c) => $t->decimal($c, 16, 4)->nullable());
        $addOrders('opsiyonel_havalandirma', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));
        $addOrders('havalandirma_tutari', fn (Blueprint $t, string $c) => $t->decimal($c, 16, 4)->nullable());
        $addOrders('opsiyonel_diger', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));
        $addOrders('diger_tutari', fn (Blueprint $t, string $c) => $t->decimal($c, 16, 4)->nullable());
        $addOrders('diger_aciklama', fn (Blueprint $t, string $c) => $t->string($c, 500)->nullable());

        $addOrders('kdv_orani', fn (Blueprint $t, string $c) => $t->decimal($c, 5, 2)->default(20));
        $addOrders('kvkk_onay', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));

        $addOrders('bayiye_fiyat_goster', fn (Blueprint $t, string $c) => $t->boolean($c)->default(false));
        $addOrders('bayi_karsi_iskonto_yuzde', fn (Blueprint $t, string $c) => $t->decimal($c, 5, 2)->nullable());
        $addOrders('bayi_karsi_not', fn (Blueprint $t, string $c) => $t->text($c)->nullable());
        $addOrders('bayi_karsi_gonderim_at', fn (Blueprint $t, string $c) => $t->timestamp($c)->nullable());

        $addOrders('seri_no', fn (Blueprint $t, string $c) => $t->string($c, 80)->nullable());
        $addOrders('yeni_seri_no', fn (Blueprint $t, string $c) => $t->string($c, 80)->nullable());
        $addOrders('yeni_seri_tarihi', fn (Blueprint $t, string $c) => $t->date($c)->nullable());
        $addOrders('imalat_listesi_cikti_at', fn (Blueprint $t, string $c) => $t->timestamp($c)->nullable());

        if (Schema::hasTable('order_items') && ! Schema::hasColumn('order_items', 'bayi_karsi_birim_fiyat')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('bayi_karsi_birim_fiyat', 12, 4)->nullable();
            });
        }
    }

    public function down(): void
    {
        //
    }
};
