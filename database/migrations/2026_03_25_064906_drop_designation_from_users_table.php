<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only drop the column if it actually exists to prevent crashes
        if (Schema::hasColumn('users', 'designation')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('designation');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add it back if we ever rollback this migration
        if (!Schema::hasColumn('users', 'designation')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('designation')->nullable();
            });
        }
    }
};