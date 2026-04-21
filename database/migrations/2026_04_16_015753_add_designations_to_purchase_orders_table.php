<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('auth_official_designation')->default('REGIONAL DIRECTOR');
            $table->string('chief_accountant_designation')->default('ACCOUNTANT II');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['auth_official_designation', 'chief_accountant_designation']);
        });
    }
};