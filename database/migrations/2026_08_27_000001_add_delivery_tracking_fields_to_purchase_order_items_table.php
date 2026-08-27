<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'supply_id')) {
                // Links this PO line directly to the supply it will stock/issue
                $table->foreignId('supply_id')->nullable()->after('purchase_order_id')
                    ->constrained('supplies')->nullOnDelete();
            }

            if (!Schema::hasColumn('purchase_order_items', 'source_type')) {
                $table->enum('source_type', ['procurement_stock', 'direct_issuance'])
                    ->default('procurement_stock')->after('inventory_synced');
            }

            if (!Schema::hasColumn('purchase_order_items', 'requesting_office')) {
                // Only set when source_type = direct_issuance
                $table->string('requesting_office')->nullable()->after('source_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'requesting_office')) {
                $table->dropColumn('requesting_office');
            }
            if (Schema::hasColumn('purchase_order_items', 'source_type')) {
                $table->dropColumn('source_type');
            }
            if (Schema::hasColumn('purchase_order_items', 'supply_id')) {
                $table->dropConstrainedForeignId('supply_id');
            }
        });
    }
};
