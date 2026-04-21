<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to purchase_orders safely
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'auth_official_designation')) {
                $table->string('auth_official_designation')->nullable()->default('REGIONAL DIRECTOR');
            }
            if (!Schema::hasColumn('purchase_orders', 'chief_accountant_designation')) {
                $table->string('chief_accountant_designation')->nullable()->default('ACCOUNTANT II');
            }
            if (!Schema::hasColumn('purchase_orders', 'place_of_delivery')) {
                $table->string('place_of_delivery')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'date_of_delivery')) {
                $table->string('date_of_delivery')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'delivery_term')) {
                $table->string('delivery_term')->nullable();
            }
            if (!Schema::hasColumn('purchase_orders', 'payment_term')) {
                $table->string('payment_term')->nullable();
            }
        });

        // Add missing checkbox column to purchase_order_items safely
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'is_delivered')) {
                $table->boolean('is_delivered')->default(false);
            }
        });
    }

    public function down(): void {}
};