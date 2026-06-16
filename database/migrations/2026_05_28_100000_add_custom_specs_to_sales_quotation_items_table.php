<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional structured specs for quotation lines with no catalog product (product_id null).
     */
    public function up(): void
    {
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            $table->string('custom_item_name', 255)->nullable()->after('description');
            $table->string('custom_color', 255)->nullable()->after('custom_item_name');
            $table->string('custom_thickness', 255)->nullable()->after('custom_color');
            $table->string('custom_measurement', 255)->nullable()->after('custom_thickness');
        });
    }

    public function down(): void
    {
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            $table->dropColumn([
                'custom_item_name',
                'custom_color',
                'custom_thickness',
                'custom_measurement',
            ]);
        });
    }
};
