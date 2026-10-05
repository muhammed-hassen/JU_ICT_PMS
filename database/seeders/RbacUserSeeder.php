<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * One demo account per role. Each gets its role only; what the role may do
 * is defined in config/rbac.php and applied by RoleSeeder.
 */
class RbacUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'ICT Director', 'email' => 'director@ict.ju.edu.et', 'password' => 'Director@123', 'role' => 'ICT Director'],
            ['name' => 'System Administrator', 'email' => 'admin@ict.ju.edu.et', 'password' => 'Admin@123', 'role' => 'System Administrator'],
            ['name' => 'Team Leader', 'email' => 'teamleader@ict.ju.edu.et', 'password' => 'Leader@123', 'role' => 'Team Leader'],
            ['name' => 'Team Member', 'email' => 'member@ict.ju.edu.et', 'password' => 'Member@123', 'role' => 'Team Member'],
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
            $this->command?->info("Created {$userData['role']}: {$userData['email']}");
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
