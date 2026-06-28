<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // Find or create users for team leaders
        $director = User::firstOrCreate(
            ['email' => 'director@ict.ju.edu.et'],
            ['name' => 'ICT Director', 'password' => bcrypt('password')]
        );
        $director->syncRoles(['ICT Director']);

        $teamLeaders = [
            ['name' => 'Development Team Leader', 'email' => 'dev.lead@ict.ju.edu.et'],
            ['name' => 'Infrastructure Team Leader', 'email' => 'infra.lead@ict.ju.edu.et'],
            ['name' => 'Support Team Leader', 'email' => 'support.lead@ict.ju.edu.et'],
        ];

        $createdLeaders = [];
        foreach ($teamLeaders as $leader) {
            $createdLeaders[] = User::firstOrCreate(
                ['email' => $leader['email']],
                ['name' => $leader['name'], 'password' => bcrypt('password')]
            );

            $createdLeaders[array_key_last($createdLeaders)]->syncRoles(['Team Leader']);
        }

        // Create the ICT Directorate (top level)
        $directorate = Team::updateOrCreate(
            ['name' => 'ICT Directorate'],
            [
                'description' => 'The main ICT directorate overseeing all ICT operations',
                'team_leader_id' => $director->id,
                'parent_team_id' => null,
            ]
        );

        // Create teams under directorate
        $teams = [
            ['name' => 'Software Development Team', 'description' => 'Develops and maintains software applications', 'leader_index' => 0],
            ['name' => 'Infrastructure Team', 'description' => 'Manages servers, networks, and cloud infrastructure', 'leader_index' => 1],
            ['name' => 'IT Support Team', 'description' => 'Provides technical support to staff and students', 'leader_index' => 2],
        ];

        foreach ($teams as $teamData) {
            Team::updateOrCreate(
                ['name' => $teamData['name']],
                [
                    'description' => $teamData['description'],
                    'team_leader_id' => $createdLeaders[$teamData['leader_index']]->id,
                    'parent_team_id' => $directorate->id,
                ]
            );
        }
    }
}
