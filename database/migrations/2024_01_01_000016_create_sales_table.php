<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->string('reference_number')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->string('customer_name')->nullable();
            $table->string('payment_method');
            $table->boolean('is_delivered')->default(false);
            $table->string('delivered_to')->nullable();
            $table->string('delivery_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('delivery_note')->nullable();
            $table->decimal('delivery_fee', 12, 2);
            $table->boolean('is_installation')->default(false);
            $table->text('installation_address')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending');


            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
}; 