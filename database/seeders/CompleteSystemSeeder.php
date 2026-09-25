<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CompleteSystemSeeder extends Seeder
{
    public function run(): void
    {
        // ========== CREATE ALL PERMISSIONS ==========
        $this->call(PermissionSeeder::class);

        // ========== CREATE ALL ROLES ==========
        $this->call(RoleSeeder::class);

        // ========== CREATE ICT DIRECTOR WITH ALL PERMISSIONS ==========
        $director = User::updateOrCreate(
            ['email' => 'director@ict.ju.edu.et'],
            [
                'name' => 'ICT Director',
                'password' => Hash::make('Director@123'),
                'email_verified_at' => now(),
            ]
        );

        // Get or create ICT Director role
        $directorRole = Role::firstOrCreate([
            'name' => 'ICT Director',
            'guard_name' => 'web',
        ]);

        // Give ALL permissions to ICT Director role
        $allPermissions = Permission::all()->pluck('name');
        $directorRole->syncPermissions($allPermissions);

        // Assign role to user
        $director->syncRoles(['ICT Director']);

        $this->command->info('✅ ICT Director created with ALL permissions!');
        $this->command->info('   Email: director@ict.ju.edu.et');
        $this->command->info('   Password: Director@123');

        // ========== CREATE SUPER ADMIN ==========
        $admin = User::updateOrCreate(
            ['email' => 'admin@ict.ju.edu.et'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
            ]
        );

        $adminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions($allPermissions);
        $admin->syncRoles(['Super Admin']);

        $this->command->info('✅ Super Admin created with ALL permissions!');
        $this->command->info('   Email: admin@ict.ju.edu.et');
        $this->command->info('   Password: Admin@123');

        // ========== CREATE TEAM LEADER ==========
        $teamLeader = User::updateOrCreate(
            ['email' => 'teamleader@ict.ju.edu.et'],
            [
                'name' => 'Team Leader',
                'password' => Hash::make('Leader@123'),
                'email_verified_at' => now(),
            ]
        );

        $leaderRole = Role::firstOrCreate([
            'name' => 'Team Leader',
            'guard_name' => 'web',
        ]);
        $teamLeader->syncRoles(['Team Leader']);

        $this->command->info('✅ Team Leader created!');
        $this->command->info('   Email: teamleader@ict.ju.edu.et');
        $this->command->info('   Password: Leader@123');

        // ========== CREATE TEAM MEMBER ==========
        $member = User::updateOrCreate(
            ['email' => 'member@ict.ju.edu.et'],
            [
                'name' => 'Team Member',
                'password' => Hash::make('Member@123'),
                'email_verified_at' => now(),
            ]
        );

        $member->syncRoles(['Team Member']);

        $this->command->info('✅ Team Member created!');
        $this->command->info('   Email: member@ict.ju.edu.et');
        $this->command->info('   Password: Member@123');

        // ========== CLEAR CACHE ==========
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('✅ All users created successfully!');
        $this->command->info('✅ ICT Director and Super Admin have ALL permissions!');
    }
}
