<?php

return [
    'guard_name' => 'web',

    'modules' => [
        'project' => 'Project Management',
        'phase' => 'Phase/Milestone Management',
        'task' => 'Task Management',
        'user' => 'User Management',
        'organization' => 'Organization Structure',
        'team' => 'Team/Department Management',
        'system' => 'System',
        'template' => 'Project Templates',
        'activity' => 'Activity Log',
        'reference' => 'Reference Data',
        'budget' => 'Budget',
        'dashboard' => 'Dashboard',
        'report' => 'Reports',
        'communication' => 'Communication',
        'file' => 'File Management',
        'analytics' => 'Analytics',
        'audit' => 'Audit',
    ],

    'permissions' => [
        // ============================================================
        // PROJECT PERMISSIONS (13)
        // ============================================================
        'project' => [
            ['name' => 'view-all-projects', 'description' => 'Can see every project in the system'],
            ['name' => 'view-team-projects', 'description' => 'Can see projects only from their team'],
            ['name' => 'view-own-projects', 'description' => 'Can see only projects they are assigned to'],
            ['name' => 'view-project-details', 'description' => 'Can view project details'],
            ['name' => 'view-projects', 'description' => 'Can view projects'],
            ['name' => 'create-project', 'description' => 'Can create new projects'],
            ['name' => 'edit-project', 'description' => 'Can edit any project details'],
            ['name' => 'edit-own-project', 'description' => 'Can edit only projects they created'],
            ['name' => 'delete-project', 'description' => 'Can delete projects'],
            ['name' => 'view-project-members', 'description' => 'Can view users assigned to project teams'],
            ['name' => 'manage-project-members', 'description' => 'Can manage project team membership'],
            ['name' => 'update-project-progress', 'description' => 'Can update project progress percentage'],
            ['name' => 'change-project-status', 'description' => 'Can change project status'],
        ],

        // ============================================================
        // PHASE PERMISSIONS (10)
        // ============================================================
        'phase' => [
            ['name' => 'view-all-phases', 'description' => 'Can view all phases'],
            ['name' => 'view-team-phases', 'description' => 'Can view phases from their team'],
            ['name' => 'view-own-phases', 'description' => 'Can view only phases they are assigned to'],
            ['name' => 'view-phases', 'description' => 'Can view project phases'],
            ['name' => 'create-phase', 'description' => 'Can create project phases'],
            ['name' => 'edit-phase', 'description' => 'Can edit project phases'],
            ['name' => 'delete-phase', 'description' => 'Can delete project phases'],
            ['name' => 'reorder-phases', 'description' => 'Can reorder phases within a project'],
            ['name' => 'update-phase-progress', 'description' => 'Can update phase progress percentage'],
            ['name' => 'complete-phase', 'description' => 'Can mark phases as completed'],
        ],

        // ============================================================
        // TASK PERMISSIONS (13)
        // ============================================================
        'task' => [
            ['name' => 'view-all-tasks', 'description' => 'Can see every task in the system'],
            ['name' => 'view-team-tasks', 'description' => 'Can see tasks from their team only'],
            ['name' => 'view-own-tasks', 'description' => 'Can see only tasks assigned to them'],
            ['name' => 'view-tasks', 'description' => 'Can view tasks'],
            ['name' => 'create-task', 'description' => 'Can create new tasks'],
            ['name' => 'assign-task', 'description' => 'Can assign tasks to team members'],
            ['name' => 'edit-task', 'description' => 'Can edit any task'],
            ['name' => 'edit-own-task', 'description' => 'Can edit only tasks assigned to them'],
            ['name' => 'delete-task', 'description' => 'Can delete tasks'],
            ['name' => 'complete-task', 'description' => 'Can mark tasks as completed'],
            ['name' => 'review-task', 'description' => 'Can review and approve tasks'],
            ['name' => 'update-task-progress', 'description' => 'Can update task progress'],
            ['name' => 'reorder-tasks', 'description' => 'Can reorder tasks within a phase'],
        ],

        // ============================================================
        // ORGANIZATION PERMISSIONS (10)
        // ============================================================
        'organization' => [
            ['name' => 'view-organization-structure', 'description' => 'Can view organization structure'],
            ['name' => 'view-teams', 'description' => 'Can view teams'],
            ['name' => 'view-team-members', 'description' => 'Can view team members'],
            ['name' => 'view-directors', 'description' => 'Can view directors'],
            ['name' => 'view-team-leaders', 'description' => 'Can view team leaders'],
            ['name' => 'view-members', 'description' => 'Can view team members'],
            ['name' => 'manage-teams', 'description' => 'Can manage teams'],
            ['name' => 'manage-directors', 'description' => 'Can manage directors'],
            ['name' => 'manage-team-leaders', 'description' => 'Can manage team leaders'],
            ['name' => 'manage-members', 'description' => 'Can manage team members'],
        ],

        // ============================================================
        // TEMPLATE PERMISSIONS (5)
        // ============================================================
        'template' => [
            ['name' => 'view-templates', 'description' => 'Can view project templates'],
            ['name' => 'create-template', 'description' => 'Can create new templates'],
            ['name' => 'edit-template', 'description' => 'Can edit templates'],
            ['name' => 'delete-template', 'description' => 'Can delete templates'],
            ['name' => 'apply-template', 'description' => 'Can apply templates to projects'],
        ],

        // ============================================================
        // SYSTEM PERMISSIONS (5)
        // ============================================================
        'system' => [
            ['name' => 'access-admin', 'description' => 'Can access system administration panel'],
            ['name' => 'configure-system', 'description' => 'Can change system settings'],
            ['name' => 'view-reports', 'description' => 'Can view system reports'],
            ['name' => 'view-dashboard', 'description' => 'Can view dashboard'],
            ['name' => 'view-audit-logs', 'description' => 'Can view activity logs'],
        ],

        // ============================================================
        // USER PERMISSIONS (6)
        // ============================================================
        'user' => [
            ['name' => 'view-all-users', 'description' => 'Can see all users in the system'],
            ['name' => 'view-team-users', 'description' => 'Can see users only from their team'],
            ['name' => 'view-user-profile', 'description' => 'Can view user profile information'],
            ['name' => 'edit-own-profile', 'description' => 'Can edit their own profile'],
            ['name' => 'edit-user', 'description' => 'Can edit any user details'],
            ['name' => 'delete-user', 'description' => 'Can delete user accounts'],
        ],

        // ============================================================
        // ACTIVITY PERMISSIONS (3)
        // ============================================================
        'activity' => [
            ['name' => 'view-all-activity-logs', 'description' => 'Can view all activity log entries'],
            ['name' => 'view-user-activity-logs', 'description' => 'Can view activity logs for a specific user'],
            ['name' => 'export-audit-logs', 'description' => 'Can export audit and activity logs'],
        ],

        // ============================================================
        // REFERENCE DATA PERMISSIONS (8)
        // ============================================================
        'reference' => [
            ['name' => 'view-reference-data', 'description' => 'Can view project reference data'],
            ['name' => 'manage-reference-data', 'description' => 'Can manage all project reference data'],
            ['name' => 'manage-project-types', 'description' => 'Can manage project type records'],
            ['name' => 'manage-project-statuses', 'description' => 'Can manage project status records'],
            ['name' => 'manage-lifecycle-stages', 'description' => 'Can manage project lifecycle stage records'],
            ['name' => 'manage-phase-statuses', 'description' => 'Can manage phase status records'],
            ['name' => 'manage-task-statuses', 'description' => 'Can manage task status records'],
            ['name' => 'manage-task-priorities', 'description' => 'Can manage task priority records'],
        ],

        // ============================================================
        // BUDGET PERMISSIONS (4)
        // ============================================================
        'budget' => [
            ['name' => 'view-budget', 'description' => 'Can view project budgets'],
            ['name' => 'edit-budget', 'description' => 'Can edit project budgets'],
            ['name' => 'approve-budget', 'description' => 'Can approve budget changes'],
            ['name' => 'view-cost', 'description' => 'Can view actual costs'],
        ],

        // ============================================================
        // DASHBOARD PERMISSIONS (4)
        // ============================================================
        'dashboard' => [
            ['name' => 'view-director-dashboard', 'description' => 'Can view the ICT Director dashboard'],
            ['name' => 'view-team-leader-dashboard', 'description' => 'Can view the Team Leader dashboard'],
            ['name' => 'view-member-dashboard', 'description' => 'Can view the Team Member dashboard'],
            ['name' => 'view-system-dashboard', 'description' => 'Can view the system administration dashboard'],
        ],

        // ============================================================
        // REPORT PERMISSIONS (4)
        // ============================================================
        'report' => [
            ['name' => 'view-all-reports', 'description' => 'Can see all system reports'],
            ['name' => 'view-team-reports', 'description' => 'Can see reports only for their team'],
            ['name' => 'export-reports', 'description' => 'Can export reports to PDF or Excel'],
            ['name' => 'schedule-reports', 'description' => 'Can schedule automated reports'],
        ],

        // ============================================================
        // COMMUNICATION PERMISSIONS (13)
        // ============================================================
        'communication' => [
            ['name' => 'view-own-conversations', 'description' => 'Can view conversations they participate in'],
            ['name' => 'view-team-conversations', 'description' => 'Can view conversations related to their team'],
            ['name' => 'view-all-conversations', 'description' => 'Can view all conversations in the system'],
            ['name' => 'create-conversation', 'description' => 'Can create new conversations'],
            ['name' => 'manage-conversation-participants', 'description' => 'Can add or remove conversation participants'],
            ['name' => 'send-message', 'description' => 'Can send messages in conversations'],
            ['name' => 'edit-own-message', 'description' => 'Can edit their own messages'],
            ['name' => 'delete-own-message', 'description' => 'Can delete their own messages'],
            ['name' => 'delete-any-message', 'description' => 'Can delete any message'],
            ['name' => 'view-own-notifications', 'description' => 'Can view notifications sent to them'],
            ['name' => 'mark-notifications-read', 'description' => 'Can mark their notifications as read'],
            ['name' => 'send-notifications', 'description' => 'Can send system or project notifications'],
            ['name' => 'manage-notifications', 'description' => 'Can manage notification records'],
        ],

        // ============================================================
        // FILE PERMISSIONS (15)
        // ============================================================
        'file' => [
            ['name' => 'upload-files', 'description' => 'Can upload files'],
            ['name' => 'download-files', 'description' => 'Can download files'],
            ['name' => 'delete-files', 'description' => 'Can delete files'],
            ['name' => 'view-all-files', 'description' => 'Can view all files'],
            ['name' => 'view-project-files', 'description' => 'Can view files attached to projects'],
            ['name' => 'view-task-files', 'description' => 'Can view files attached to tasks or subtasks'],
            ['name' => 'view-message-files', 'description' => 'Can view files attached to messages'],
            ['name' => 'upload-project-files', 'description' => 'Can upload files to projects or phases'],
            ['name' => 'upload-task-files', 'description' => 'Can upload files to tasks or subtasks'],
            ['name' => 'upload-message-files', 'description' => 'Can upload files to messages'],
            ['name' => 'delete-own-files', 'description' => 'Can delete files they uploaded'],
            ['name' => 'delete-any-files', 'description' => 'Can delete any file'],
            ['name' => 'view-file-details', 'description' => 'Can view file details'],
            ['name' => 'edit-file', 'description' => 'Can edit file details'],
            ['name' => 'share-files', 'description' => 'Can share files with others'],
        ],
    ],

    // ============================================================
    // ROLES
    // ============================================================
    'roles' => [
        // ============================================================
        // ICT DIRECTOR - ALL PERMISSIONS
        // ============================================================
        'ICT Director' => [
            'description' => 'Full system access - can manage everything',
            'permissions' => '*',
        ],

        // ============================================================
        // SYSTEM ADMINISTRATOR - ALL PERMISSIONS
        // ============================================================
        'System Administrator' => [
            'description' => 'Full system access - can manage everything',
            'permissions' => '*',
        ],

        // ============================================================
        // TEAM LEADER
        // ============================================================
        'Team Leader' => [
            'description' => 'Manages team and assigns tasks',
            'permissions' => [
                // Projects
                'view-team-projects',
                'view-project-details',
                'view-projects',
                'view-project-members',
                'manage-project-members',
                'update-project-progress',
                'change-project-status',
                'create-project',
                'edit-own-project',

                // Phases
                'view-team-phases',
                'view-phases',
                'create-phase',
                'edit-phase',
                'reorder-phases',
                'update-phase-progress',
                'complete-phase',

                // Tasks
                'view-team-tasks',
                'view-tasks',
                'create-task',
                'assign-task',
                'edit-task',
                'complete-task',
                'review-task',
                'update-task-progress',

                // Organization
                'view-organization-structure',
                'view-teams',
                'view-team-members',
                'view-members',

                // Templates
                'view-templates',
                'apply-template',

                // Dashboard
                'view-dashboard',

                // Reports
                'view-reports',

                // Users
                'view-user-profile',
                'edit-own-profile',
            ],
        ],

        // ============================================================
        // TEAM MEMBER
        // ============================================================
        'Team Member' => [
            'description' => 'Executes assigned tasks',
            'permissions' => [
                // Projects
                'view-own-projects',
                'view-project-details',
                'view-projects',

                // Phases
                'view-own-phases',
                'view-phases',

                // Tasks
                'view-own-tasks',
                'view-tasks',
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
            ],
        ],
    ],

    // ============================================================
    // SEED USERS
    // ============================================================
    'seed_users' => [
        [
            'name' => 'ICT Director',
            'email' => 'director@ict.ju.edu.et',
            'password' => 'Director@123',
            'role' => 'ICT Director',
        ],
        [
            'name' => 'System Administrator',
            'email' => 'admin@ict.ju.edu.et',
            'password' => 'Admin@123',
            'role' => 'System Administrator',
        ],
        [
            'name' => 'Team Leader',
            'email' => 'teamleader@ict.ju.edu.et',
            'password' => 'Leader@123',
            'role' => 'Team Leader',
        ],
        [
            'name' => 'Team Member',
            'email' => 'member@ict.ju.edu.et',
            'password' => 'Member@123',
            'role' => 'Team Member',
        ],
        [
            'name' => 'Development Team Leader',
            'email' => 'dev.lead@ict.ju.edu.et',
            'password' => 'password',
            'role' => 'Team Leader',
        ],
        [
            'name' => 'Infrastructure Team Leader',
            'email' => 'infra.lead@ict.ju.edu.et',
            'password' => 'password',
            'role' => 'Team Leader',
        ],
        [
            'name' => 'Support Team Leader',
            'email' => 'support.lead@ict.ju.edu.et',
            'password' => 'password',
            'role' => 'Team Leader',
        ],
        [
            'name' => 'Team Member 1',
            'email' => 'member1@ict.ju.edu.et',
            'password' => 'password',
            'role' => 'Team Member',
        ],
        [
            'name' => 'Team Member 2',
            'email' => 'member2@ict.ju.edu.et',
            'password' => 'password',
            'role' => 'Team Member',
        ],
        [
            'name' => 'Team Member 3',
            'email' => 'member3@ict.ju.edu.et',
            'password' => 'password',
            'role' => 'Team Member',
        ],
    ],
];
