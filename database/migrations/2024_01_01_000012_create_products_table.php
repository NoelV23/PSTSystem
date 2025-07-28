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
            $table->string('base_unit');
            $table->boolean('is_set')->default(false);
            $table->string('color')->nullable();
            $table->string('measurement_unit')->nullable(); // e.g., ft, m, kg
            $table->float('default_length')->nullable();
            $table->float('default_width')->nullable();
            $table->float('default_height')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}; 