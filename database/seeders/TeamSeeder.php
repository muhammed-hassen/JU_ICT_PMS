<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // SUPER ADMIN - director@ict.ju.edu.et
        // ============================================================
        $director = User::where('email', 'director@ict.ju.edu.et')->first();

        if (! $director) {
            $director = User::firstOrCreate(
                ['email' => 'director@ict.ju.edu.et'],
                [
                    'name' => 'ICT Director',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
            $director->syncRoles(['System Administrator']);
            $director->givePermissionTo(Permission::all());
        }

        // ============================================================
        // CREATE TEAM MEMBERS
        // ============================================================
        $member1 = User::firstOrCreate(
            ['email' => 'member1@ict.ju.edu.et'],
            ['name' => 'Team Member 1', 'password' => bcrypt('password')]
        );
        $member1->syncRoles(['Team Member']);

        $member2 = User::firstOrCreate(
            ['email' => 'member2@ict.ju.edu.et'],
            ['name' => 'Team Member 2', 'password' => bcrypt('password')]
        );
        $member2->syncRoles(['Team Member']);

        $member3 = User::firstOrCreate(
            ['email' => 'member3@ict.ju.edu.et'],
            ['name' => 'Team Member 3', 'password' => bcrypt('password')]
        );
        $member3->syncRoles(['Team Member']);

        // ============================================================
        // CREATE TEAM LEADERS
        // ============================================================
        $teamLeaders = [
            ['name' => 'Development Team Leader', 'email' => 'dev.lead@ict.ju.edu.et'],
            ['name' => 'Infrastructure Team Leader', 'email' => 'infra.lead@ict.ju.edu.et'],
            ['name' => 'Support Team Leader', 'email' => 'support.lead@ict.ju.edu.et'],
        ];

        $createdLeaders = [];
        foreach ($teamLeaders as $leader) {
            $user = User::firstOrCreate(
                ['email' => $leader['email']],
                ['name' => $leader['name'], 'password' => bcrypt('password')]
            );
            $user->syncRoles(['Team Leader']);
            $createdLeaders[] = $user;
        }

        // ============================================================
        // CREATE TEAMS
        // ============================================================
        // Create ICT Directorate
        $directorate = Team::updateOrCreate(
            ['name' => 'ICT Directorate'],
            [
                'description' => 'The main ICT directorate overseeing all ICT operations',
                'team_leader_id' => $director->id,
                'parent_team_id' => null,
            ]
        );

        // Create sub-teams
        $teams = [
            ['name' => 'Software Development Team', 'description' => 'Develops and maintains software applications', 'leader_index' => 0],
            ['name' => 'Infrastructure Team', 'description' => 'Manages servers, networks, and cloud infrastructure', 'leader_index' => 1],
            ['name' => 'IT Support Team', 'description' => 'Provides technical support to staff and students', 'leader_index' => 2],
        ];

        $createdTeams = [];
        foreach ($teams as $teamData) {
            $team = Team::updateOrCreate(
                ['name' => $teamData['name']],
                [
                    'description' => $teamData['description'],
                    'team_leader_id' => $createdLeaders[$teamData['leader_index']]->id,
                    'parent_team_id' => $directorate->id,
                ]
            );
            $createdTeams[] = $team;
        }

        // ============================================================
        // ASSIGN MEMBERS TO TEAMS
        // ============================================================
        $members = [$member1, $member2, $member3];

        foreach ($createdTeams as $index => $team) {
            // Assign each member to at least one team
            $team->members()->syncWithoutDetaching([$members[$index % count($members)]->id]);

            // Add team leader as member
            if (isset($createdLeaders[$index])) {
                $team->members()->syncWithoutDetaching([$createdLeaders[$index]->id]);
            }
        }

        // Add director to directorate as member
        $directorate->members()->syncWithoutDetaching([$director->id]);

        // Add all team leaders to directorate as well
        foreach ($createdLeaders as $leader) {
            $directorate->members()->syncWithoutDetaching([$leader->id]);
        }

        $this->command->info('✅ Team seeding completed!');
        $this->command->info('   - '.Team::count().' teams created');
        $this->command->info('   - '.User::count().' users total');
        $this->command->info('   - Super Admin: director@ict.ju.edu.et / password');
    }
}
