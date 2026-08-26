<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'po_number')) {
                $table->string('po_number')->nullable()->after('supplier');
            }

            if (!Schema::hasColumn('transactions', 'delivery_receipt')) {
                $table->string('delivery_receipt')->nullable()->after('po_number');
            }

            if (!Schema::hasColumn('transactions', 'office')) {
                $table->string('office')->nullable()->after('delivery_receipt');
            }

            if (!Schema::hasColumn('transactions', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->nullable()->after('office');
            }

            if (!Schema::hasColumn('transactions', 'receipt_status')) {
                $table->string('receipt_status')->nullable()->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $columns = ['receipt_status', 'unit_price', 'office', 'delivery_receipt', 'po_number'];
            $existing = array_filter($columns, fn (string $column) => Schema::hasColumn('transactions', $column));

            if ($existing) {
                $table->dropColumn($existing);
            }
        });
    }
};