<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('status', 20)->default('received')->after('total_cost');
            $table->string('po_number', 64)->nullable()->after('status');
            $table->text('ship_to')->nullable()->after('po_number');
            $table->string('payment_terms', 255)->nullable()->after('ship_to');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('purchase_receipt_no')->nullable()->change();
        });

        foreach (DB::table('purchase_orders')->select('id')->orderBy('id')->get() as $row) {
            DB::table('purchase_orders')->where('id', $row->id)->update([
                'status' => 'received',
                'po_number' => 'PO-LEG-'.str_pad((string) $row->id, 6, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['status', 'po_number', 'ship_to', 'payment_terms']);
        });

        DB::table('purchase_orders')->whereNull('purchase_receipt_no')->update(['purchase_receipt_no' => 'N/A']);

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('purchase_receipt_no')->nullable(false)->change();
        });
    }
};
