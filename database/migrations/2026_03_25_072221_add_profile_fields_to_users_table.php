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
        Schema::table('users', function (Blueprint $table) {
            // Safely check if columns exist before adding them to prevent crashes
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('lastname');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
            if (Schema::hasColumn('users', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('users', 'contact_number')) {
                $table->dropColumn('contact_number');
            }
        });
    }
};