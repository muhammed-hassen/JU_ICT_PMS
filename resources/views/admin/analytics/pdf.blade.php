{{-- PDF version of the Reports page, rendered by dompdf. Plain CSS only: dompdf does not run Tailwind or JavaScript. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>JU ICT PMS report</title>
    <style>
        @page { margin: 28mm 18mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1f2937; }
        header { position: fixed; top: -20mm; left: 0; right: 0; border-bottom: 2px solid #0b2a5b; padding-bottom: 6px; }
        header .brand { font-size: 15px; font-weight: bold; color: #0b2a5b; }
        header .meta { font-size: 9.5px; color: #6b7280; }
        footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 9px; color: #6b7280; text-align: center; }
        h2 { font-size: 13px; color: #0b2a5b; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eef2f7; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: .04em; color: #4b5563; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .num { text-align: right; }
        .stats td { width: 25%; border: 1px solid #e5e7eb; padding: 10px; }
        .stats .label { color: #6b7280; font-size: 9.5px; }
        .stats .value { font-size: 18px; font-weight: bold; color: #111827; }
        .danger { color: #b42318; font-weight: bold; }
    </style>
</head>
<body>
<header>
    <div class="brand">JU ICT PMS · Project Report</div>
    <div class="meta">Jimma University, JU-ICT Team · Scope: {{ $scopeLabel }} · Generated {{ $generatedAt->format('M j, Y g:i A') }}</div>
</header>
<footer>JU ICT Project Management System</footer>

<h2>Summary</h2>
<table class="stats">
    <tr>
        <td><div class="label">Projects</div><div class="value">{{ $totalProjects }}</div></td>
        <td><div class="label">Active</div><div class="value">{{ $activeProjects }}</div></td>
        <td><div class="label">Completed</div><div class="value">{{ $completedProjects }}</div></td>
        <td><div class="label">Draft / archived</div><div class="value">{{ $draftProjects }} / {{ $archivedProjects }}</div></td>
    </tr>
    <tr>
        <td><div class="label">Tasks</div><div class="value">{{ $totalTasks }}</div></td>
        <td><div class="label">Completed tasks</div><div class="value">{{ $completedTasks }}</div></td>
        <td><div class="label">Overdue tasks</div><div class="value {{ $overdueTasks ? 'danger' : '' }}">{{ $overdueTasks }}</div></td>
        <td><div class="label">Completion rate</div><div class="value">{{ $completionRate }}%</div></td>
    </tr>
    <tr>
        <td><div class="label">Total budget (ETB)</div><div class="value">{{ number_format($totalBudget) }}</div></td>
        <td><div class="label">Spent (ETB)</div><div class="value {{ $totalSpent > $totalBudget && $totalBudget ? 'danger' : '' }}">{{ number_format($totalSpent) }}</div></td>
        <td></td><td></td>
    </tr>
</table>

<h2>Projects</h2>
@if (count($projectRows))
    <table>
        <thead>
            <tr><th>Project</th><th>Status</th><th class="num">Progress</th><th class="num">Planned</th><th class="num">Spent / budget (ETB)</th><th>Schedule</th><th class="num">Phases</th><th class="num">Tasks done</th><th class="num">Overdue</th></tr>
        </thead>
        <tbody>
            @foreach ($projectRows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td class="num">{{ $row['progress'] }}%</td>
                    <td class="num">{{ $row['planned'] !== null ? $row['planned'] . '%' : 'N/A' }}</td>
                    <td class="num {{ $row['budget'] !== null && $row['spent'] > $row['budget'] ? 'danger' : '' }}">{{ number_format($row['spent']) }} / {{ $row['budget'] !== null ? number_format($row['budget']) : 'N/A' }}</td>
                    <td>{{ $row['start'] ?: 'N/A' }} to {{ $row['end'] ?: 'N/A' }}</td>
                    <td class="num">{{ $row['phases'] }}</td>
                    <td class="num">{{ $row['completed'] }} / {{ $row['tasks'] }}</td>
                    <td class="num {{ $row['overdue'] ? 'danger' : '' }}">{{ $row['overdue'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No projects in scope.</p>
@endif

<h2>People</h2>
@if ($topPerformers->count())
    <table>
        <thead><tr><th>Name</th><th>Email</th><th class="num">Tasks</th><th class="num">Completed</th><th class="num">Completion rate</th></tr></thead>
        <tbody>
            @foreach ($topPerformers as $person)
                <tr>
                    <td>{{ $person->name }}</td>
                    <td>{{ $person->email }}</td>
                    <td class="num">{{ $person->total_tasks }}</td>
                    <td class="num">{{ $person->completed_tasks }}</td>
                    <td class="num">{{ $person->completion_rate }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No assigned tasks in scope.</p>
@endif
</body>
</html>
