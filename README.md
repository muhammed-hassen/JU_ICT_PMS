# JU ICT Project Management System

A web application for the Jimma University ICT Directorate (the JU-ICT Team) to plan, assign and track ICT projects. Work is organised as **Project → Phases (milestones) → Tasks**, and projects can be generated from reusable templates.

Built with Laravel 12 (PHP 8.2+), MySQL, Blade and Tailwind CSS. Access control uses [spatie/laravel-permission](https://spatie.be/docs/laravel-permission).

## Features

| Area | What it does |
|---|---|
| Roles and access | Four roles with strict, need-to-know permissions (see below). Permissions belong to roles only, never to individual users. Every route checks a permission on the server. |
| Organization | Directors, team leaders, teams (with parent teams) and members, plus an organization chart. |
| Projects | Create projects with objectives, a budget and dates, assign teams, and optionally generate phases and tasks from a template. The owner or the Director closes a project once every task is done. |
| Phases | Create, reorder and track phases, each with milestones (deliverable and due date). A phase completes itself when its last task does. |
| Tasks | Task list with filters, a drag-and-drop board, My Tasks, a status workflow (Not Started → In Progress → Under Review → Completed, plus Blocked), priorities, deadlines, overdue tracking, subtasks, and estimated and actual cost. Members move their own tasks up to Under Review; the Team Leader completes them. |
| Assignment | A primary assignee plus extra assignees. Team Leaders can only assign within their own teams. |
| Templates | Six example templates, including the standard ICT lifecycle (Initiation, Planning, Execution, Monitoring, Closure). Templates are editable, with phases and tasks. |
| Dashboards | A separate dashboard per role: office-wide for the Director, accounts and activity for the System Administrator, team workload and reviews for Team Leaders, and own work for Members. |
| Reports | Charts, planned against actual progress, budget against spending, and top performers, scoped to what the viewer can see. Export as **PDF** or **Excel (CSV)**. |
| Messaging | One-to-one and group conversations. Members reach their own team, the Director and the System Administrator; Team Leaders can also reach each other. Unread counts appear in the sidebar. |
| Notifications | A bell in the top bar for task assignments, status changes, new messages, and daily alerts for work due tomorrow or overdue. |
| Files | Upload, download and delete documents on a project. Members can delete only their own uploads. |
| Activity log | Who changed what and when, with filters. Task status history and project progress history are kept too. |

Self-registration is off, and there is no self-service profile page: accounts, emails and passwords are managed by the System Administrator and the Director in User Management.

## Who can do what

Defined in `config/rbac.php` (the `roles` section). Each role sees only what its job needs.

| Role | Can | Cannot |
|---|---|---|
| ICT Director | All projects, phases, tasks and reports; the organization (teams, leaders, members, org chart); the activity log; use templates | Manage login accounts, roles or permissions; edit templates |
| System Administrator | Login accounts and role assignment, roles and permissions, templates, reference data, the activity log | See or change projects, tasks, teams or reports |
| Team Leader | Their own teams' projects, phases, tasks, board and reports; create projects (from templates too) and edit the ones they created; assign and review tasks; their own team page | Other teams' work, the staff directories, the org chart, templates, accounts, the activity log |
| Team Member | Their own tasks (move them up to Under Review), the projects those tasks are in, messages to their own team | Other people's tasks, task lists and board, phases, reports, teams and every admin page |

## Requirements

- PHP 8.2 or newer, with the `pdo_mysql`, `mbstring`, `gd` and `dom` extensions
- Composer 2
- MySQL 8 (or MariaDB 10.6+)
- Node.js 20+, only needed to rebuild the CSS

## Setup

```bash
git clone <repository-url> ju-ict-pms
cd ju-ict-pms
composer install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database called `ju_ict_pms`, then set `DB_USERNAME` and `DB_PASSWORD` in `.env`.

```bash
php artisan migrate --seed
php artisan serve
```

Open http://localhost:8000.

The compiled stylesheet (`public/css/ju.css`) is committed, so you don't need Node to run the app. Rebuild it only after changing Blade classes or `resources/css/ju.css`:

```bash
npm install
npm run build
```

## Demo accounts

These are created by `php artisan migrate --seed`. **Change every password before real use.**

| Role | Email | Password |
|---|---|---|
| ICT Director | director@ict.ju.edu.et | Director@123 |
| System Administrator | admin@ict.ju.edu.et | Admin@123 |
| Team Leader | teamleader@ict.ju.edu.et | Leader@123 |
| Team Member | member@ict.ju.edu.et | Member@123 |
| Team Leaders (Development, Infrastructure, Support) | dev.lead@, infra.lead@, support.lead@ict.ju.edu.et | password |
| Team Members 1 to 3 | member1@ to member3@ict.ju.edu.et | password |

## Tests

The tests run against an in-memory SQLite database, so they don't touch your MySQL data.

```bash
php artisan test
```

## Project layout

| Path | Contents |
|---|---|
| `app/Http/Controllers/Admin` | Projects, phases, tasks, templates, reports, files, roles and permissions |
| `app/Http/Controllers/Organization` | Directors, team leaders, members and teams |
| `app/Http/Controllers/MessageController.php` | Messaging |
| `app/Http/Controllers/HomeController.php` | Chooses the dashboard for each role |
| `app/Services` | Template application, the task workflow, progress calculation and the activity log |
| `app/Console/Commands` | `pms:deadline-alerts`, the daily due-tomorrow and overdue alerts |
| `config/rbac.php` | Every permission and which role gets it. Seeders read this file. |
| `config/navigation.php` | The sidebar menu and the permission behind each item |
| `resources/views/dashboards` | The four role dashboards |
| `resources/views/components/ui` | Shared Blade components (button, card, badge, stat card and more) |

## Adding a permission

1. Add it to the right module in `config/rbac.php`, and to each role that should have it.
2. For existing databases, add a migration that creates the permission and grants it. See `database/migrations/2026_09_25_120100_grant_messaging_dashboard_report_permissions.php` for the pattern.
3. Protect the route with `->middleware('permission:your-permission')`.

## Deployment notes

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Run `php artisan migrate --force`, then `php artisan config:cache route:cache view:cache`.
- Uploaded project files live in `storage/app/private/project-files`. Include this folder in backups.
- Notifications and messages are stored in the database. No queue worker or mail server is required.
- Deadline alerts need Laravel's scheduler. Add this cron entry on the server: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
