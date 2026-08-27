<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained('supplies')->cascadeOnDelete();
            $table->foreignId('po_item_id')->nullable()
                ->constrained('purchase_order_items')->nullOnDelete();
            $table->enum('source_type', ['procurement_stock', 'direct_issuance'])
                ->default('procurement_stock');
            $table->string('dr_number')->nullable();
            $table->date('dr_date')->nullable();
            $table->integer('quantity');
            // FIFO-available quantity; direct_issuance batches are created already fully consumed (0)
            $table->integer('remaining_qty');
            $table->decimal('unit_price', 15, 2);
            $table->string('requesting_office')->nullable();
            $table->timestamps();

            $table->index(['supply_id', 'remaining_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_batches');
    }
};
