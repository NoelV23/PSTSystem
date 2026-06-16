<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            $table->decimal('cut_length', 10, 3)->nullable()->after('custom_measurement');
            $table->decimal('cut_width', 10, 3)->nullable()->after('cut_length');
            $table->decimal('cut_height', 10, 3)->nullable()->after('cut_width');
            $table->string('cut_measurement_unit', 32)->nullable()->after('cut_height');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('cut_length', 10, 3)->nullable()->after('cost_price');
            $table->decimal('cut_width', 10, 3)->nullable()->after('cut_length');
            $table->decimal('cut_height', 10, 3)->nullable()->after('cut_width');
            $table->string('cut_measurement_unit', 32)->nullable()->after('cut_height');
        });
    }

    public function down(): void
    {
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            $table->dropColumn(['cut_length', 'cut_width', 'cut_height', 'cut_measurement_unit']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['cut_length', 'cut_width', 'cut_height', 'cut_measurement_unit']);
        });
    }
};
