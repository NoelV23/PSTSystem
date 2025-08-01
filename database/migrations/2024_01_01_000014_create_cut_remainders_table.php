<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cut_remainders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_id');
            $table->decimal('length_remaining', 8, 2)->nullable();
            $table->decimal('height_remaining', 8, 2)->nullable();
            $table->decimal('width_remaining', 8, 2)->nullable();
            $table->string('location_note')->nullable();
            $table->string('status')->default('available');
            $table->string('discard_reason')->nullable();
            $table->timestamp('discarded_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cut_remainders');
    }
}; 