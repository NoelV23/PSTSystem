<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        DB::statement('ALTER TABLE sale_items MODIFY product_id BIGINT UNSIGNED NULL');

        Schema::table('sale_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('product_id');
            $table->string('custom_item_name')->nullable()->after('description');
            $table->string('custom_color')->nullable()->after('custom_item_name');
            $table->string('custom_thickness')->nullable()->after('custom_color');
            $table->string('custom_measurement')->nullable()->after('custom_thickness');
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        DB::statement("ALTER TABLE sale_items MODIFY fulfillment_source ENUM('inventory', 'remainder', 'custom') NOT NULL DEFAULT 'inventory'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sale_items MODIFY fulfillment_source ENUM('inventory', 'remainder') NOT NULL DEFAULT 'inventory'");

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'description',
                'custom_item_name',
                'custom_color',
                'custom_thickness',
                'custom_measurement',
            ]);
        });

        DB::statement('ALTER TABLE sale_items MODIFY product_id BIGINT UNSIGNED NOT NULL');

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
