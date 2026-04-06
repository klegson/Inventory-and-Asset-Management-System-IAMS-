<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ris_requests', function (Blueprint $table) {
            // Adds the user_id securely
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('ris_requests', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};