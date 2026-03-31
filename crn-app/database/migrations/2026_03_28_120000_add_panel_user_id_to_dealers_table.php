<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->foreignId('panel_user_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('panel_user_id');
        });
    }
};
