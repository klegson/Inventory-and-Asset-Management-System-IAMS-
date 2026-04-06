<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        User::create([
            'firstname'      => 'Head',
            'lastname'       => 'Admin',
            'employee_id'    => 'ADMIN-001',
            'designation'    => 'Head of Asset Management Office',
            'department'     => 'Asset Management Office',
            'contact_number' => '09111111111',
            'email'          => 'admin@deped.gov.ph',
            'username'       => 'admin_user',
            'password'       => 'password',
            'role'           => 'admin',
            'status'         => 'Active',
        ]);

        // 2. Create Default Staff (Personnel) User
        User::create([
            'firstname'      => 'Supply',
            'lastname'       => 'Personnel',
            'employee_id'    => 'STAFF-001',
            'designation'    => 'Asset Manager',
            'department'     => 'Asset Management Office',
            'contact_number' => '09222222222',
            'email'          => 'staff@deped.gov.ph',
            'username'       => 'staff_user',
            'password'       => 'password',
            'role'           => 'staff',
            'status'         => 'Active',
        ]);

        // 3. Create Default Front User (End User / Requestor)
        User::create([
            'firstname'      => 'Division',
            'lastname'       => 'Requestor',
            'employee_id'    => 'USER-001',
            'designation'    => 'Teacher I',
            'department'     => 'Academic Division',
            'contact_number' => '09333333333',
            'email'          => 'user@deped.gov.ph',
            'username'       => 'end_user',
            'password'       => 'password',
            'role'           => 'frontuser',
            'status'         => 'Active',
        ]);
    }
}