<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = [
            ['Role_Name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['Role_Name' => 'Receptionist', 'created_at' => now(), 'updated_at' => now()],
            ['Role_Name' => 'Technician', 'created_at' => now(), 'updated_at' => now()],
            ['Role_Name' => 'Billing', 'created_at' => now(), 'updated_at' => now()],
            ['Role_Name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()],
            ['Role_Name' => 'Patient', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('roles')->insert($roles);

        // 2. Create Permissions
        $permissions = [
            ['Permission_Name' => 'view_users', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'create_users', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'update_users', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'delete_users', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'view_roles', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'create_roles', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'update_roles', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'delete_roles', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'view_patients', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'create_patient', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'update_patient', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'delete_patient', 'created_at' => now(), 'updated_at' => now()],
            ['Permission_Name' => 'view_logs', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('permissions')->insert($permissions);

        // 3. Assign all permissions to Admin (Role_ID = 1)
        $allPermissions = DB::table('permissions')->get();
        foreach ($allPermissions as $permission) {
            DB::table('role_permissions')->insert([
                'Role_ID' => 1,
                'Permission_ID' => $permission->Permission_ID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Create Admin User
        DB::table('users')->insert([
            'username' => 'admin',           // ✅ lowercase
            'Password' => Hash::make('123456'),
            'Role' => 'Admin',
            'Role_ID' => 1,
            'Full_Name' => 'System Administrator',
            'Email' => 'admin@lis.com',
            'Phone' => '01000000000',
            'Is_Active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Create Receptionist User
        DB::table('users')->insert([
            'username' => 'receptionist',    // ✅ lowercase
            'Password' => Hash::make('123456'),
            'Role' => 'Receptionist',
            'Role_ID' => 2,
            'Full_Name' => 'Front Desk Receptionist',
            'Email' => 'reception@lis.com',
            'Phone' => '01000000001',
            'Is_Active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}