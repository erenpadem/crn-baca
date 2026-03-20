<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('on_siparis_no', 50)->nullable()->after('siparis_no');
            $table->decimal('bac_cap_mm', 10, 2)->nullable()->after('cihaz_marka_model');
            $table->string('yon', 10)->nullable()->after('bac_cap_mm');
            $table->boolean('attr_n')->default(false)->after('yon');
            $table->boolean('attr_m')->default(false)->after('attr_n');
            $table->boolean('attr_a')->default(false)->after('attr_m');
            $table->boolean('attr_h')->default(false)->after('attr_a');
            $table->boolean('attr_di')->default(false)->after('attr_h');

            $table->decimal('kur', 14, 4)->nullable()->after('iskonto_yuzde');
            $table->decimal('kur_farki_yuzde', 5, 2)->nullable()->default(10)->after('kur');
            $table->decimal('tutar_kdvsiz_on', 16, 4)->nullable()->after('kur_farki_yuzde');
            $table->decimal('tutar_kdvsiz_nihai', 16, 4)->nullable()->after('tutar_kdvsiz_on');

            $table->boolean('opsiyonel_nakliye')->default(false)->after('tutar_kdvsiz_nihai');
            $table->decimal('nakliye_tutari', 16, 4)->nullable()->after('opsiyonel_nakliye');
            $table->boolean('opsiyonel_akreditif')->default(false)->after('nakliye_tutari');
            $table->decimal('akreditif_tutari', 16, 4)->nullable()->after('opsiyonel_akreditif');
            $table->boolean('opsiyonel_montaj')->default(false)->after('akreditif_tutari');
            $table->decimal('montaj_tutari', 16, 4)->nullable()->after('opsiyonel_montaj');
            $table->boolean('opsiyonel_havalandirma')->default(false)->after('montaj_tutari');
            $table->decimal('havalandirma_tutari', 16, 4)->nullable()->after('opsiyonel_havalandirma');
            $table->boolean('opsiyonel_diger')->default(false)->after('havalandirma_tutari');
            $table->decimal('diger_tutari', 16, 4)->nullable()->after('opsiyonel_diger');
            $table->string('diger_aciklama', 500)->nullable()->after('diger_tutari');

            $table->decimal('kdv_orani', 5, 2)->default(20)->after('diger_aciklama');
            $table->boolean('kvkk_onay')->default(false)->after('kdv_orani');

            $table->string('seri_no', 80)->nullable()->after('kvkk_onay');
            $table->string('yeni_seri_no', 80)->nullable()->after('seri_no');
            $table->date('yeni_seri_tarihi')->nullable()->after('yeni_seri_no');
            $table->timestamp('imalat_listesi_cikti_at')->nullable()->after('yeni_seri_tarihi');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'on_siparis_no',
                'bac_cap_mm',
                'yon',
                'attr_n',
                'attr_m',
                'attr_a',
                'attr_h',
                'attr_di',
                'kur',
                'kur_farki_yuzde',
                'tutar_kdvsiz_on',
                'tutar_kdvsiz_nihai',
                'opsiyonel_nakliye',
                'nakliye_tutari',
                'opsiyonel_akreditif',
                'akreditif_tutari',
                'opsiyonel_montaj',
                'montaj_tutari',
                'opsiyonel_havalandirma',
                'havalandirma_tutari',
                'opsiyonel_diger',
                'diger_tutari',
                'diger_aciklama',
                'kdv_orani',
                'kvkk_onay',
                'seri_no',
                'yeni_seri_no',
                'yeni_seri_tarihi',
                'imalat_listesi_cikti_at',
            ]);
        });
    }
};
