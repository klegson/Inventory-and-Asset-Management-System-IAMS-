<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ics_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ics_no')->unique();
            $table->string('fund_cluster')->nullable();
            $table->string('category')->nullable();
            $table->string('sig_received_from_name')->nullable();
            $table->string('sig_received_from_pos')->nullable();
            $table->date('sig_from_date')->nullable();
            $table->string('sig_received_by_name')->nullable();
            $table->string('sig_received_by_pos')->nullable();
            $table->date('sig_by_date')->nullable();
            $table->string('status')->default('Pending');
            $table->text('items_json')->nullable(); // We will store items as JSON for simplicity
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ics_requests');
    }
};