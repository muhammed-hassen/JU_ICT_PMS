<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacUserSeeder extends Seeder
{
    /**
     * Seed the initial RBAC users.
     */
    public function run(): void
    {
        foreach (config('rbac.seed_users', []) as $seedUser) {
            $user = User::query()->updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $seedUser['name'],
                    'password' => Hash::make($seedUser['password']),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$seedUser['role']]);
        }
    }
}
