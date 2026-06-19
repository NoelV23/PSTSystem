<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            $table->string('line_unit', 32)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            $table->dropColumn('line_unit');
        });
    }
};
