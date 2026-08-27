<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_request_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ris_item_id')->constrained('ris_items')->cascadeOnDelete();
            $table->foreignId('supply_batch_id')->constrained('supply_batches')->cascadeOnDelete();
            $table->integer('quantity_allocated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_request_allocations');
    }
};
