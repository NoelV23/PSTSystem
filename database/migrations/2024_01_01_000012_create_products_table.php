<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_bundle')->default(false);
            $table->string('base_unit');
            $table->float('default_length')->nullable();
            $table->float('default_width')->nullable();
            $table->float('default_height')->nullable();
            $table->decimal('price_per_unit', 12, 2)->nullable();
            $table->decimal('price_per_piece', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}; 