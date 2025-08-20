<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Place columns after total_price if fulfillment_source is not present yet
            $afterColumn = Schema::hasColumn('sale_items', 'fulfillment_source') ? 'fulfillment_source' : 'total_price';
            $table->unsignedBigInteger('created_remainder_id')->nullable()->after($afterColumn);
            $table->unsignedBigInteger('consumed_remainder_id')->nullable()->after('created_remainder_id');
            $table->json('remainder_before_json')->nullable()->after('consumed_remainder_id');
            $table->unsignedBigInteger('remainder_after_id')->nullable()->after('remainder_before_json');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['created_remainder_id', 'consumed_remainder_id', 'remainder_before_json', 'remainder_after_id']);
        });
    }
};


