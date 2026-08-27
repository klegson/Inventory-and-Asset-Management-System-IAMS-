<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_item_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_item_id')->constrained('purchase_order_items')->cascadeOnDelete();
            $table->foreignId('pr_referral_id')->constrained('pr_referrals')->cascadeOnDelete();
            // Share of the po_item's ordered quantity earmarked for this referral
            $table->integer('quantity_allocated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_item_referrals');
    }
};
