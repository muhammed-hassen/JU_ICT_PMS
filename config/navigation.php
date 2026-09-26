<?php

/*
| Sidebar navigation.
|
| Each item is shown only when the user holds its 'can' permission and the
| route behind 'url' would let them in (see layouts/partials/sidebar.blade.php).
| Icons are lucide names (https://lucide.dev/icons).
*/

return [
    'brand' => 'JU ICT PMS',

    'menu' => [
        // ============================================================
        // DASHBOARD - ALL USERS
        // ============================================================
        [
            'text' => 'Dashboard',
            'url' => 'home',
            'icon' => 'layout-dashboard',
        ],

        // ============================================================
        // MY TASKS - ALL USERS (with view-tasks permission)
        // ============================================================
        [
            'text' => 'My Tasks',
            'url' => 'tasks/my',
            'icon' => 'list-todo',
            'can' => 'view-tasks',
        ],

        // Messaging. The sidebar adds the unread count at render time.
        [
            'text' => 'Messages',
            'url' => 'messages',
            'icon' => 'messages-square',
            'can' => 'view-own-conversations',
            'active' => ['messages*'],
        ],

        // ============================================================
        // PROJECT MANAGEMENT HEADER
        // ============================================================
        [
            'header' => 'PROJECT MANAGEMENT',
        ],

        // ============================================================
        // PROJECTS - ALL USERS (with view-projects permission)
        // ============================================================
        [
            'text' => 'Projects',
            'icon' => 'folder-kanban',
            'can' => 'view-projects',
            'submenu' => [
                [
                    'text' => 'All Projects',
                    'url' => 'projects',
                    'icon' => 'list',
                    'can' => 'view-projects',
                    'active' => ['projects*'],
                ],
                [
                    'text' => 'Create Project',
                    'url' => 'projects/create',
                    'icon' => 'plus',
                    'can' => 'create-project',
                    'active' => ['projects/create*'],
                ],
            ],
        ],

        // ============================================================
        // PHASE MANAGEMENT - DIRECTORS & TEAM LEADERS
        // ============================================================
        [
            'text' => 'Phase Management',
            'icon' => 'layers',
            'can' => 'view-phases',
            'submenu' => [
                [
                    'text' => 'All Phases',
                    'url' => 'phases',
                    'icon' => 'list',
                    'can' => 'view-phases',
                    'active' => ['phases*'],
                ],
                [
                    'text' => 'Create Phase',
                    'url' => 'phases/create',
                    'icon' => 'plus',
                    'can' => 'create-phase',
                    'active' => ['phases/create*'],
                ],
                [
                    'text' => 'Phase Dashboard',
                    'url' => 'phase-dashboard',
                    'icon' => 'chart-column',
                    'can' => 'view-phases',
                    'active' => ['phase-dashboard*'],
                ],
            ],
        ],

        // ============================================================
        // TASK MANAGEMENT - DIRECTORS & TEAM LEADERS
        // ============================================================
        [
            'text' => 'Task Management',
            'icon' => 'list-todo',
            'can' => ['view-team-tasks', 'view-all-tasks'],
            'submenu' => [
                [
                    'text' => 'All Tasks',
                    'url' => 'tasks',
                    'icon' => 'list-checks',
                    'can' => ['view-team-tasks', 'view-all-tasks'],
                    'active' => ['tasks*'],
                ],
                [
                    'text' => 'Create Task',
                    'url' => 'tasks/create',
                    'icon' => 'plus',
                    'can' => 'create-task',
                    'active' => ['tasks/create'],
                ],
                [
                    'text' => 'Board View',
                    'url' => 'tasks/kanban',
                    'icon' => 'square-kanban',
                    'can' => ['view-team-tasks', 'view-all-tasks'],
                    'active' => ['tasks/kanban*'],
                ],
                [
                    'text' => 'My Tasks',
                    'url' => 'tasks/my',
                    'icon' => 'user-check',
                    'active' => ['tasks/my*'],
                ],
                [
                    'text' => 'Overdue Tasks',
                    'url' => 'tasks?overdue=1',
                    'icon' => 'triangle-alert',
                    'can' => ['view-team-tasks', 'view-all-tasks'],
                    'label' => 'Overdue',
                    'label_color' => 'danger',
                    'active' => ['tasks*'],
                ],
            ],
        ],

        // ============================================================
        // PROJECT TEMPLATES - DIRECTORS & TEAM LEADERS
        // ============================================================
        [
            'text' => 'Project Templates',
            'url' => 'templates',
            'icon' => 'copy',
            'can' => 'view-templates',
            'active' => ['templates*'],
        ],

        // ============================================================
        // ORGANIZATION HEADER - DIRECTORS & TEAM LEADERS
        // ============================================================
        [
            'header' => 'ORGANIZATION',
            'can' => 'view-organization-structure',
        ],

        [
            'text' => 'Org Chart',
            'url' => 'admin/organization/chart',
            'icon' => 'network',
            'can' => 'view-organization-structure',
            'active' => ['admin/organization/chart'],
        ],

        // ============================================================
        // TEAMS - DIRECTORS & TEAM LEADERS
        // ============================================================
        [
            'text' => 'Teams',
            'icon' => 'users',
            'can' => 'view-teams',
            'submenu' => [
                [
                    'text' => 'All Teams',
                    'url' => 'admin/organization/teams',
                    'icon' => 'list',
                    'can' => 'view-teams',
                    'active' => ['admin/organization/teams*'],
                ],
                [
                    'text' => 'Create Team',
                    'url' => 'admin/organization/teams/create',
                    'icon' => 'plus',
                    'can' => 'manage-teams',
                    'active' => ['admin/organization/teams/create*'],
                ],
            ],
        ],

        // ============================================================
        // MEMBERS - DIRECTORS & TEAM LEADERS
        // ============================================================
        [
            'text' => 'Members',
            'icon' => 'users-round',
            'can' => 'view-members',
            'submenu' => [
                [
                    'text' => 'All Members',
                    'url' => 'admin/organization/members',
                    'icon' => 'list',
                    'can' => 'view-members',
                    'active' => ['admin/organization/members*'],
                ],
                [
                    'text' => 'Add Member',
                    'url' => 'admin/organization/members/create',
                    'icon' => 'user-plus',
                    'can' => 'manage-members',
                    'active' => ['admin/organization/members/create*'],
                ],
            ],
        ],

        // ============================================================
        // DIRECTORS - DIRECTORS ONLY
        // ============================================================
        [
            'text' => 'Directors',
            'icon' => 'briefcase-business',
            'can' => 'view-directors',
            'submenu' => [
                [
                    'text' => 'All Directors',
                    'url' => 'admin/organization/directors',
                    'icon' => 'list',
                    'can' => 'view-directors',
                    'active' => ['admin/organization/directors*'],
                ],
                [
                    'text' => 'Add Director',
                    'url' => 'admin/organization/directors/create',
                    'icon' => 'user-plus',
                    'can' => 'manage-directors',
                    'active' => ['admin/organization/directors/create*'],
                ],
            ],
        ],

        // ============================================================
        // TEAM LEADERS - DIRECTORS ONLY
        // ============================================================
        [
            'text' => 'Team Leaders',
            'icon' => 'user-cog',
            'can' => 'view-team-leaders',
            'submenu' => [
                [
                    'text' => 'All Team Leaders',
                    'url' => 'admin/organization/team-leaders',
                    'icon' => 'list',
                    'can' => 'view-team-leaders',
                    'active' => ['admin/organization/team-leaders*'],
                ],
                [
                    'text' => 'Add Team Leader',
                    'url' => 'admin/organization/team-leaders/create',
                    'icon' => 'user-plus',
                    'can' => 'manage-team-leaders',
                    'active' => ['admin/organization/team-leaders/create*'],
                ],
            ],
        ],

        // ============================================================
        // ADMINISTRATION HEADER - DIRECTORS ONLY
        // ============================================================
        [
            'header' => 'ADMINISTRATION',
            'can' => 'access-admin',
        ],

        // ============================================================
        // ROLES & PERMISSIONS - DIRECTORS ONLY
        // ============================================================
        [
            'text' => 'Roles & Permissions',
            'icon' => 'shield',
            'can' => 'access-admin',
            'submenu' => [
                [
                    'text' => 'Roles',
                    'url' => 'admin/roles',
                    'icon' => 'users-round',
                    'can' => 'access-admin',
                    'active' => ['admin/roles*'],
                ],
                [
                    'text' => 'Permissions',
                    'url' => 'admin/permissions',
                    'icon' => 'key-round',
                    'can' => 'configure-system',
                    'active' => ['admin/permissions*'],
                ],
            ],
        ],

        // ============================================================
        // USER MANAGEMENT - DIRECTORS ONLY
        // ============================================================
        [
            'text' => 'User Management',
            'icon' => 'users-round',
            'can' => 'access-admin',
            'submenu' => [
                [
                    'text' => 'All Users',
                    'url' => 'users',
                    'icon' => 'users',
                    'can' => 'access-admin',
                    'active' => ['users*'],
                ],
                [
                    'text' => 'Add User',
                    'url' => 'users/create',
                    'icon' => 'user-plus',
                    'can' => 'access-admin',
                    'active' => ['users/create*'],
                ],
            ],
        ],

        // ============================================================
        // ACTIVITY LOG - DIRECTORS ONLY
        // ============================================================
        [
            'text' => 'Reports',
            'url' => 'analytics',
            'icon' => 'chart-line',
            'can' => 'view-reports',
            'active' => ['analytics*'],
        ],
        [
            'text' => 'Activity Log',
            'url' => 'admin/activity',
            'icon' => 'history',
            'can' => 'view-audit-logs',
            'active' => ['admin/activity*'],
        ],
    ],
];
