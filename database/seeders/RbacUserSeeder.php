<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get all permissions
        $allPermissions = Permission::all();
        $permissionNames = $allPermissions->pluck('name')->toArray();

        // Make sure ICT Director role has ALL permissions
        $directorRole = Role::firstOrCreate([
            'name' => 'ICT Director',
            'guard_name' => 'web',
        ]);
        $directorRole->syncPermissions($allPermissions);

        // Make sure System Administrator has ALL permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'System Administrator',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions($allPermissions);

        // Create users
        $users = [
            [
                'name' => 'ICT Director',
                'email' => 'director@ict.ju.edu.et',
                'password' => 'Director@123',
                'role' => 'ICT Director',
            ],
            [
                'name' => 'System Administrator',
                'email' => 'admin@ict.ju.edu.et',
                'password' => 'Admin@123',
                'role' => 'System Administrator',
            ],
            [
                'name' => 'Team Leader',
                'email' => 'teamleader@ict.ju.edu.et',
                'password' => 'Leader@123',
                'role' => 'Team Leader',
            ],
            [
                'name' => 'Team Member',
                'email' => 'member@ict.ju.edu.et',
                'password' => 'Member@123',
                'role' => 'Team Member',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$userData['role']]);

            // If ICT Director or System Admin, give ALL permissions directly
            if (in_array($userData['role'], ['ICT Director', 'System Administrator'])) {
                $user->givePermissionTo($permissionNames);
            }

            $this->command->info("✅ Created: {$userData['name']} ({$userData['email']})");
        }

        // Clear cache
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('✅ ALL USERS CREATED!');
        $this->command->info('📧 ICT Director: director@ict.ju.edu.et');
        $this->command->info('🔑 Password: Director@123');
    }
}
