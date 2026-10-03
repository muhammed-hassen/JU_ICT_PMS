<?php
// One-time backfill: add ICT Director users to all existing project_members
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$directors = \App\Models\User::role('ICT Director')->pluck('id')->toArray();

if (empty($directors)) {
    echo "No ICT Director users found.\n";
    exit;
}

echo "Found directors: " . implode(', ', $directors) . "\n";

$count = 0;
\App\Models\Project::all()->each(function ($project) use ($directors, &$count) {
    $existing = $project->members()->pluck('users.id')->toArray();
    $missing  = array_diff($directors, $existing);
    if (!empty($missing)) {
        $project->members()->syncWithoutDetaching($missing);
        $count++;
        echo "Updated project #{$project->id}: {$project->name}\n";
    }
});

echo "Done. Updated {$count} projects.\n";
