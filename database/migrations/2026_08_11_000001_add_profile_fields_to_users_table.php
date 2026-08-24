<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'firstname')) {
                $table->string('firstname')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname')->nullable()->after('firstname');
            }
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('lastname');
            }
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('designation');
            }
            if (!Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('department');
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->after('contact_number');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('staff')->after('username');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('Active')->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('users', 'contact_number')) {
                $table->dropColumn('contact_number');
            }
            if (Schema::hasColumn('users', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('users', 'designation')) {
                $table->dropColumn('designation');
            }
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
            if (Schema::hasColumn('users', 'lastname')) {
                $table->dropColumn('lastname');
            }
            if (Schema::hasColumn('users', 'firstname')) {
                $table->dropColumn('firstname');
            }
        });
    }
};
