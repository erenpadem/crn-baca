<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (! Schema::hasColumn('orders', 'is_manual_on')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_manual_on')->default(false)->after('tutar_kdvsiz_on');
            });
        }

        if (! Schema::hasColumn('orders', 'is_manual_nihai')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_manual_nihai')->default(false)->after('tutar_kdvsiz_nihai');
            });
        }

        // Eski davranış: sütunda değer varsa manuel kabul edilmişti.
        if (Schema::hasColumn('orders', 'tutar_kdvsiz_on') && Schema::hasColumn('orders', 'is_manual_on')) {
            DB::table('orders')
                ->whereNotNull('tutar_kdvsiz_on')
                ->update(['is_manual_on' => true]);
        }

        if (Schema::hasColumn('orders', 'tutar_kdvsiz_nihai') && Schema::hasColumn('orders', 'is_manual_nihai')) {
            DB::table('orders')
                ->whereNotNull('tutar_kdvsiz_nihai')
                ->update(['is_manual_nihai' => true]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_manual_on')) {
                $table->dropColumn('is_manual_on');
            }
            if (Schema::hasColumn('orders', 'is_manual_nihai')) {
                $table->dropColumn('is_manual_nihai');
            }
        });
    }
};
