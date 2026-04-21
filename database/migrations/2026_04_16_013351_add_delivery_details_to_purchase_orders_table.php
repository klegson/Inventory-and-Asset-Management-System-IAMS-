<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('place_of_delivery')->nullable();
            $table->string('date_of_delivery')->nullable(); 
            $table->string('delivery_term')->nullable();
            $table->string('payment_term')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['place_of_delivery', 'date_of_delivery', 'delivery_term', 'payment_term']);
        });
    }
};