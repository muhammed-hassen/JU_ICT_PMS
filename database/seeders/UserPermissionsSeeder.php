<?php

// database/seeders/UserPermissionsSeeder.php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class UserPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info("\n========================================");
        $this->command->info('  🔐 ASSIGNING PERMISSIONS TO ALL ROLES');
        $this->command->info("========================================\n");

        // ============================================================
        // GET ALL EXISTING PERMISSIONS FROM DATABASE
        // ============================================================
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $this->command->info('📋 Total permissions available: '.count($allPermissions)."\n");

        // ============================================================
        // ========== 1. ICT DIRECTOR - ALL PERMISSIONS ==========
        // ============================================================
        $director = User::where('email', 'director@ict.ju.edu.et')->first();
        if ($director) {
            $director->syncPermissions($allPermissions);
            $director->syncRoles(['ICT Director']);
            $this->command->info('✅ ICT Director: '.$director->getAllPermissions()->count().' permissions');
            $this->command->info('   Email: director@ict.ju.edu.et');
            $this->command->info("   Password: Director@123\n");
        }

        // ============================================================
        // ========== 2. SYSTEM ADMINISTRATOR - ALL PERMISSIONS ==========
        // ============================================================
        $admin = User::where('email', 'admin@ict.ju.edu.et')->first();
        if ($admin) {
            $admin->syncPermissions($allPermissions);
            $admin->syncRoles(['System Administrator']);
            $this->command->info('✅ System Administrator: '.$admin->getAllPermissions()->count().' permissions');
            $this->command->info('   Email: admin@ict.ju.edu.et');
            $this->command->info("   Password: Admin@123\n");
        }

        // ============================================================
        // ========== 3. TEAM LEADER PERMISSIONS ==========
        // ============================================================
        $teamLeaderPermissions = [
            // Projects
            'view-projects',
            'view-team-projects',
            'view-project-details',
            'view-project-members',
            'create-project',
            'edit-own-project',
            'update-project-progress',
            'change-project-status',

            // Phases
            'view-phases',
            'view-team-phases',
            'create-phase',
            'edit-phase',
            'reorder-phases',
            'update-phase-progress',
            'complete-phase',

            // Tasks
            'view-tasks',
            'view-team-tasks',
            'create-task',
            'assign-task',
            'edit-task',
            'complete-task',
            'review-task',
            'update-task-progress',

            // Templates
            'view-templates',
            'apply-template',

            // Organization
            'view-organization-structure',
            'view-teams',
            'view-team-members',
            'view-members',
            'view-team-leaders',
            'view-directors',

            // Dashboard
            'view-dashboard',

            // Reports
            'view-reports',

            // Users
            'view-user-profile',
            'edit-own-profile',

            // Admin (Limited)
            'access-admin',
        ];

        // Assign to ALL Team Leaders
        $teamLeaders = User::whereHas('roles', function ($q) {
            $q->where('name', 'Team Leader');
        })->get();

        foreach ($teamLeaders as $leader) {
            $leader->syncPermissions($teamLeaderPermissions);
            $leader->syncRoles(['Team Leader']);
            $this->command->info("✅ {$leader->name}: ".$leader->getAllPermissions()->count().' permissions');
            $this->command->info("   Email: {$leader->email}");
            $this->command->info("   Password: password\n");
        }

        // ============================================================
        // ========== 4. TEAM MEMBER PERMISSIONS ==========
        // ============================================================
        $teamMemberPermissions = [
            // Projects
            'view-projects',
            'view-own-projects',
            'view-project-details',
            'view-project-members',

            // Phases
            'view-phases',
            'view-own-phases',

            // Tasks
            'view-tasks',
            'view-own-tasks',
            'edit-own-task',
            'complete-task',
            'update-task-progress',

            // Organization
            'view-organization-structure',
            'view-teams',
            'view-team-members',

            // Dashboard
            'view-dashboard',

            // Users
            'view-user-profile',
            'edit-own-profile',
        ];

        // Assign to ALL Team Members
        $teamMembers = User::whereHas('roles', function ($q) {
            $q->where('name', 'Team Member');
        })->get();

        foreach ($teamMembers as $member) {
            $member->syncPermissions($teamMemberPermissions);
            $member->syncRoles(['Team Member']);
            $this->command->info("✅ {$member->name}: ".$member->getAllPermissions()->count().' permissions');
            $this->command->info("   Email: {$member->email}");
            $this->command->info("   Password: password\n");
        }

        // ============================================================
        // ========== 5. CLEAR CACHE ==========
        // ============================================================
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // ============================================================
        // ========== 6. SUMMARY ==========
        // ============================================================
        $this->command->info('========================================');
        $this->command->info('  ✅ PERMISSION ASSIGNMENT COMPLETE!');
        $this->command->info("========================================\n");

        $this->command->info('📊 Summary:');
        $this->command->info('  🔴 ICT Director: '.($director ? $director->getAllPermissions()->count() : 0).' permissions');
        $this->command->info('  🔴 System Admin: '.($admin ? $admin->getAllPermissions()->count() : 0).' permissions');
        $this->command->info('  🟡 Team Leaders: '.$teamLeaders->count().' users with '.count($teamLeaderPermissions).' permissions each');
        $this->command->info('  🟢 Team Members: '.$teamMembers->count().' users with '.count($teamMemberPermissions)." permissions each\n");

        $this->command->info('🔑 Login Credentials:');
        $this->command->info('  🔴 ICT Director: director@ict.ju.edu.et / Director@123');
        $this->command->info('  🔴 System Admin: admin@ict.ju.edu.et / Admin@123');
        $this->command->info('  🟡 Team Leader: dev.lead@ict.ju.edu.et / password');
        $this->command->info("  🟢 Team Member: member1@ict.ju.edu.et / password\n");

        $this->command->info('🎉 All permissions assigned successfully!');
    }
}
