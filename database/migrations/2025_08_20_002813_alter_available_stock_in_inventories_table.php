<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Change available_stock to decimal (10 total digits, 2 decimals)
            $table->decimal('available_stock', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Rollback to integer (if needed)
            $table->integer('available_stock')->change();
        });
    }
};
