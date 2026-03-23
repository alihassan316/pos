<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add returned_qty tracking to sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->integer('returned_qty')->default(0)->after('total_price');
        });

        // Add return_note and refund_amount to sales
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('refund_amount', 10, 2)->default(0)->after('due_amount');
            $table->string('return_note')->nullable()->after('refund_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('returned_qty');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'return_note']);
        });
    }
};
