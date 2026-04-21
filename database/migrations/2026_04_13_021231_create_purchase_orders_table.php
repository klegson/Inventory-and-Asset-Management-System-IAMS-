<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('entity_name')->nullable();
            $table->string('po_no')->unique();
            $table->string('supplier_name');
            $table->string('supplier_address');
            $table->date('po_date');
            $table->string('procurement_mode');
            $table->string('auth_official');
            $table->string('chief_accountant');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('Pending Delivery');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
