<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'consumed_remainder_id')) {
                $table->dropColumn('consumed_remainder_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'consumed_remainder_id')) {
                $table->unsignedBigInteger('consumed_remainder_id')->nullable()->after('created_remainder_id');
            }
        });
    }
};


