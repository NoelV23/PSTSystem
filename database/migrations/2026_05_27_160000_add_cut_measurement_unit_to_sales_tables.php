<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cut_remainders', function (Blueprint $table) {
            $table->string('cut_measurement_unit', 32)->nullable()->after('height_remaining');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('cut_measurement_unit', 32)->nullable()->after('cut_height');
        });

        Schema::table('installation_product_usages', function (Blueprint $table) {
            $table->string('cut_measurement_unit', 32)->nullable()->after('cut_height');
        });
    }

    public function down(): void
    {
        Schema::table('cut_remainders', function (Blueprint $table) {
            $table->dropColumn('cut_measurement_unit');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('cut_measurement_unit');
        });

        Schema::table('installation_product_usages', function (Blueprint $table) {
            $table->dropColumn('cut_measurement_unit');
        });
    }
};
