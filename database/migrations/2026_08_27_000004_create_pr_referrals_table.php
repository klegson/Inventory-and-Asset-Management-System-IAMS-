<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ris_id')->constrained('ris_requests')->cascadeOnDelete();
            $table->foreignId('ris_item_id')->constrained('ris_items')->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('supplies')->cascadeOnDelete();
            $table->integer('quantity_needed');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('referred_at')->nullable();
            // Filled in once BAC issues the PO for this referral
            $table->string('pr_number')->nullable();
            $table->enum('status', ['referred', 'po_issued', 'fulfilled', 'cancelled'])
                ->default('referred');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_referrals');
    }
};
