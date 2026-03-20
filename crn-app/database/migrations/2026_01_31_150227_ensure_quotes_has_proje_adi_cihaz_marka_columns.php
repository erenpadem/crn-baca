<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds proje_adi and cihaz_marka_model to quotes if missing (e.g. DB was restored).
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'proje_adi')) {
                $table->string('proje_adi')->nullable()->after('durum');
            }
            if (! Schema::hasColumn('quotes', 'cihaz_marka_model')) {
                $table->string('cihaz_marka_model')->nullable()->after('proje_adi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $cols = array_filter(['proje_adi', 'cihaz_marka_model'], fn ($c) => Schema::hasColumn('quotes', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
