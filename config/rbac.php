<?php

return [
    "guard_name" => "web",

    "modules" => [
        "project" => "Project Management",
        "project_reference" => "Project Reference Data",
        "phase" => "Phase/Milestone Management",
        "task" => "Task and Subtask Management",
        "user" => "User Management",
        "organization" => "Organization Structure",
        "team" => "Team/Department Management",
        "report" => "Reporting",
        "dashboard" => "Dashboard",
        "budget" => "Budget",
        "system" => "System",
        "audit" => "Audit and Traceability",
        "template" => "Project Templates",
        "communication" => "Notification and Communication",
        "file" => "File Management",
    ],

    "permissions" => [
        "project" => [
            [
                "name" => "view-all-projects",
                "description" => "Can see every project in the system",
            ],
            [
                "name" => "view-team-projects",
                "description" => "Can see projects only from their team",
            ],
            [
                "name" => "view-own-projects",
                "description" => "Can see only projects they are assigned to",
            ],
            [
                "name" => "create-project",
                "description" => "Can create new projects",
            ],
            [
                "name" => "edit-project",
                "description" => "Can edit any project details",
            ],
            [
                "name" => "edit-own-project",
                "description" => "Can edit only projects they created",
            ],
            [
                "name" => "delete-project",
                "description" => "Can delete projects",
            ],
            [
                "name" => "approve-project",
                "description" => "Can approve project budgets and changes",
            ],
            [
                "name" => "view-project-budget",
                "description" => "Can view project budget information",
            ],
            [
                "name" => "edit-project-budget",
                "description" => "Can edit project budget",
            ],
            [
                "name" => "view-project-members",
                "description" => "Can view users assigned to project teams",
            ],
            [
                "name" => "manage-project-members",
                "description" => "Can manage project team membership",
            ],
            [
                "name" => "assign-project-member",
                "description" => "Can assign users to project teams",
            ],
            [
                "name" => "remove-project-member",
                "description" => "Can remove users from project teams",
            ],
            [
                "name" => "view-project-responsibilities",
                "description" =>
                    "Can view project-specific member responsibilities",
            ],
            [
                "name" => "assign-project-responsibility",
                "description" =>
                    "Can assign project-specific responsibilities to members",
            ],
            [
                "name" => "edit-project-responsibility",
                "description" => "Can edit project-specific responsibilities",
            ],
            [
                "name" => "end-project-responsibility",
                "description" => "Can end project-specific responsibilities",
            ],
            [
                "name" => "update-project-progress",
                "description" => "Can update project progress percentage",
            ],
            [
                "name" => "change-project-status",
                "description" => "Can change project status",
            ],
            [
                "name" => "change-project-lifecycle-stage",
                "description" => "Can change project lifecycle stage",
            ],
        ],
        "project_reference" => [
            [
                "name" => "view-reference-data",
                "description" =>
                    "Can view project reference data such as types, statuses, stages, and priorities",
            ],
            [
                "name" => "manage-reference-data",
                "description" => "Can manage all project reference data",
            ],
            [
                "name" => "manage-project-types",
                "description" => "Can manage project type records",
            ],
            [
                "name" => "manage-project-statuses",
                "description" => "Can manage project status records",
            ],
            [
                "name" => "manage-lifecycle-stages",
                "description" => "Can manage project lifecycle stage records",
            ],
            [
                "name" => "manage-phase-statuses",
                "description" => "Can manage phase status records",
            ],
            [
                "name" => "manage-task-statuses",
                "description" => "Can manage task status records",
            ],
            [
                "name" => "manage-task-priorities",
                "description" => "Can manage task priority records",
            ],
        ],
        "phase" => [
            [
                "name" => "view-phases",
                "description" => "Can view project phases and milestones",
            ],
            [
                "name" => "create-phase",
                "description" => "Can create project phases and milestones",
            ],
            [
                "name" => "edit-phase",
                "description" => "Can edit project phases and milestones",
            ],
            [
                "name" => "delete-phase",
                "description" => "Can delete project phases and milestones",
            ],
            [
                "name" => "reorder-phases",
                "description" => "Can reorder phases within a project",
            ],
            [
                "name" => "update-phase-progress",
                "description" => "Can update phase progress percentage",
            ],
            [
                "name" => "complete-phase",
                "description" => "Can mark phases as completed",
            ],
        ],
        "task" => [
            [
                "name" => "view-all-tasks",
                "description" => "Can see every task in the system",
            ],
            [
                "name" => "view-team-tasks",
                "description" => "Can see tasks from their team only",
            ],
            [
                "name" => "view-own-tasks",
                "description" => "Can see only tasks assigned to them",
            ],
            ["name" => "create-task", "description" => "Can create new tasks"],
            [
                "name" => "assign-task",
                "description" => "Can assign tasks to team members",
            ],
            ["name" => "edit-task", "description" => "Can edit any task"],
            [
                "name" => "edit-own-task",
                "description" => "Can edit only tasks assigned to them",
            ],
            ["name" => "delete-task", "description" => "Can delete tasks"],
            [
                "name" => "complete-task",
                "description" => "Can mark tasks as completed",
            ],
            [
                "name" => "review-task",
                "description" => "Can review and approve tasks",
            ],
            [
                "name" => "view-all-subtasks",
                "description" => "Can see every subtask in the system",
            ],
            [
                "name" => "view-team-subtasks",
                "description" => "Can see subtasks from their team only",
            ],
            [
                "name" => "view-own-subtasks",
                "description" => "Can see only subtasks assigned to them",
            ],
            [
                "name" => "create-subtask",
                "description" => "Can create subtasks under tasks",
            ],
            [
                "name" => "assign-subtask",
                "description" => "Can assign subtasks to team members",
            ],
            ["name" => "edit-subtask", "description" => "Can edit any subtask"],
            [
                "name" => "edit-own-subtask",
                "description" => "Can edit only subtasks assigned to them",
            ],
            [
                "name" => "delete-subtask",
                "description" => "Can delete subtasks",
            ],
            [
                "name" => "complete-subtask",
                "description" => "Can mark subtasks as completed",
            ],
            [
                "name" => "reorder-subtasks",
                "description" => "Can reorder subtasks within a task",
            ],
        ],
        "user" => [
            [
                "name" => "view-all-users",
                "description" => "Can see all users in the system",
            ],
            [
                "name" => "view-team-users",
                "description" => "Can see users only from their team",
            ],
            [
                "name" => "create-user",
                "description" => "Can create new user accounts",
            ],
            [
                "name" => "edit-user",
                "description" => "Can edit any user details",
            ],
            [
                "name" => "edit-team-user",
                "description" => "Can edit users only from their team",
            ],
            [
                "name" => "delete-user",
                "description" => "Can delete user accounts",
            ],
            [
                "name" => "assign-role",
                "description" => "Can assign roles to users",
            ],
            [
                "name" => "reset-password",
                "description" => "Can reset user passwords",
            ],
            [
                "name" => "view-user-profile",
                "description" => "Can view user profile information",
            ],
            [
                "name" => "edit-own-profile",
                "description" => "Can edit their own profile",
            ],
            [
                "name" => "activate-user",
                "description" => "Can activate user accounts",
            ],
            [
                "name" => "deactivate-user",
                "description" => "Can deactivate user accounts",
            ],
        ],
        "organization" => [
            [
                "name" => "view-organization-structure",
                "description" => "Can view the ICT Directorate hierarchy",
            ],
            [
                "name" => "manage-organization-structure",
                "description" => "Can manage the ICT Directorate hierarchy",
            ],
            [
                "name" => "view-reporting-lines",
                "description" =>
                    "Can view supervisor and reporting-line relationships",
            ],
            [
                "name" => "assign-supervisor",
                "description" => "Can assign supervisors to users",
            ],
            [
                "name" => "edit-reporting-line",
                "description" =>
                    "Can edit supervisor and reporting-line relationships",
            ],
        ],
        "team" => [
            [
                "name" => "create-team",
                "description" => "Can create new teams or departments",
            ],
            [
                "name" => "edit-team",
                "description" => "Can edit team or department details",
            ],
            [
                "name" => "delete-team",
                "description" => "Can delete teams or departments",
            ],
            [
                "name" => "view-all-teams",
                "description" => "Can view all teams or departments",
            ],
            [
                "name" => "assign-team-leader",
                "description" => "Can assign team leaders",
            ],
            [
                "name" => "manage-team-members",
                "description" => "Can add or remove team members",
            ],
            [
                "name" => "view-team-members",
                "description" => "Can view members of a team or department",
            ],
            [
                "name" => "assign-team-member",
                "description" => "Can assign users to teams or departments",
            ],
            [
                "name" => "remove-team-member",
                "description" => "Can remove users from teams or departments",
            ],
            [
                "name" => "transfer-team-member",
                "description" =>
                    "Can transfer users between teams or departments",
            ],
        ],
        "report" => [
            [
                "name" => "view-all-reports",
                "description" => "Can see all system reports",
            ],
            [
                "name" => "view-team-reports",
                "description" => "Can see reports only for their team",
            ],
            [
                "name" => "export-reports",
                "description" => "Can export reports to PDF or Excel",
            ],
            [
                "name" => "schedule-reports",
                "description" => "Can schedule automated reports",
            ],
            ["name" => "view-dashboard", "description" => "Can view dashboard"],
        ],
        "dashboard" => [
            [
                "name" => "view-director-dashboard",
                "description" => "Can view the ICT Director dashboard",
            ],
            [
                "name" => "view-team-leader-dashboard",
                "description" => "Can view the Team Leader dashboard",
            ],
            [
                "name" => "view-member-dashboard",
                "description" => "Can view the Team Member dashboard",
            ],
            [
                "name" => "view-system-dashboard",
                "description" => "Can view the system administration dashboard",
            ],
        ],
        "budget" => [
            [
                "name" => "view-budget",
                "description" => "Can view project budgets",
            ],
            [
                "name" => "edit-budget",
                "description" => "Can edit project budgets",
            ],
            [
                "name" => "approve-budget",
                "description" => "Can approve budget changes",
            ],
            ["name" => "view-cost", "description" => "Can view actual costs"],
        ],
        "system" => [
            [
                "name" => "access-admin",
                "description" => "Can access system administration panel",
            ],
            [
                "name" => "view-audit-logs",
                "description" => "Can view activity logs",
            ],
            [
                "name" => "backup-database",
                "description" => "Can perform database backups",
            ],
            [
                "name" => "restore-database",
                "description" => "Can restore from backups",
            ],
            [
                "name" => "configure-system",
                "description" => "Can change system settings",
            ],
            [
                "name" => "view-system-status",
                "description" => "Can view system health status",
            ],
        ],
        "audit" => [
            [
                "name" => "view-all-activity-logs",
                "description" => "Can view all activity log entries",
            ],
            [
                "name" => "view-user-activity-logs",
                "description" => "Can view activity logs for a specific user",
            ],
            [
                "name" => "export-audit-logs",
                "description" => "Can export audit and activity logs",
            ],
        ],
        "template" => [
            [
                "name" => "view-templates",
                "description" => "Can view project templates",
            ],
            [
                "name" => "create-template",
                "description" => "Can create new templates",
            ],
            ["name" => "edit-template", "description" => "Can edit templates"],
            [
                "name" => "delete-template",
                "description" => "Can delete templates",
            ],
            [
                "name" => "apply-template",
                "description" => "Can apply templates to projects",
            ],
            [
                "name" => "manage-template-phases",
                "description" => "Can manage phases inside project templates",
            ],
            [
                "name" => "manage-template-tasks",
                "description" => "Can manage tasks inside project templates",
            ],
            [
                "name" => "reorder-template-phases",
                "description" => "Can reorder phases inside project templates",
            ],
            [
                "name" => "reorder-template-tasks",
                "description" => "Can reorder tasks inside project templates",
            ],
        ],
        "communication" => [
            [
                "name" => "view-own-conversations",
                "description" => "Can view conversations they participate in",
            ],
            [
                "name" => "view-team-conversations",
                "description" => "Can view conversations related to their team",
            ],
            [
                "name" => "view-all-conversations",
                "description" => "Can view all conversations in the system",
            ],
            [
                "name" => "create-conversation",
                "description" => "Can create new conversations",
            ],
            [
                "name" => "manage-conversation-participants",
                "description" => "Can add or remove conversation participants",
            ],
            [
                "name" => "send-message",
                "description" => "Can send messages in conversations",
            ],
            [
                "name" => "edit-own-message",
                "description" => "Can edit their own messages",
            ],
            [
                "name" => "delete-own-message",
                "description" => "Can delete their own messages",
            ],
            [
                "name" => "delete-any-message",
                "description" => "Can delete any message",
            ],
            [
                "name" => "view-own-notifications",
                "description" => "Can view notifications sent to them",
            ],
            [
                "name" => "mark-notifications-read",
                "description" => "Can mark their notifications as read",
            ],
            [
                "name" => "send-notifications",
                "description" => "Can send system or project notifications",
            ],
            [
                "name" => "manage-notifications",
                "description" => "Can manage notification records",
            ],
        ],
        "file" => [
            ["name" => "upload-files", "description" => "Can upload files"],
            ["name" => "download-files", "description" => "Can download files"],
            ["name" => "delete-files", "description" => "Can delete files"],
            ["name" => "view-all-files", "description" => "Can view all files"],
            [
                "name" => "view-project-files",
                "description" => "Can view files attached to projects",
            ],
            [
                "name" => "view-task-files",
                "description" => "Can view files attached to tasks or subtasks",
            ],
            [
                "name" => "view-message-files",
                "description" => "Can view files attached to messages",
            ],
            [
                "name" => "upload-project-files",
                "description" => "Can upload files to projects or phases",
            ],
            [
                "name" => "upload-task-files",
                "description" => "Can upload files to tasks or subtasks",
            ],
            [
                "name" => "upload-message-files",
                "description" => "Can upload files to messages",
            ],
            [
                "name" => "delete-own-files",
                "description" => "Can delete files they uploaded",
            ],
            [
                "name" => "delete-any-files",
                "description" => "Can delete any file",
            ],
        ],
    ],

    "roles" => [
        "System Administrator" => [
            "description" => "Full system access - can manage everything",
            "permissions" => "*",
        ],
        "ICT Director" => [
            "description" => "Full director access - can manage all ICT projects, teams, users, templates, roles, and system records",
            "permissions" => "*",
        ],
        "Team Leader" => [
            "description" => "Manages team and assigns tasks",
            "permissions" => [
                "view-team-projects",
                "create-project",
                "edit-own-project",
                "view-project-budget",
                "view-project-members",
                "manage-project-members",
                "assign-project-member",
                "remove-project-member",
                "view-project-responsibilities",
                "assign-project-responsibility",
                "edit-project-responsibility",
                "update-project-progress",
                "change-project-status",
                "view-phases",
                "create-phase",
                "edit-phase",
                "reorder-phases",
                "update-phase-progress",
                "complete-phase",
                "view-team-tasks",
                "create-task",
                "assign-task",
                "edit-task",
                "complete-task",
                "review-task",
                "view-team-subtasks",
                "create-subtask",
                "assign-subtask",
                "edit-subtask",
                "complete-subtask",
                "reorder-subtasks",
                "view-team-users",
                "edit-team-user",
                "view-user-profile",
                "view-organization-structure",
                "view-reporting-lines",
                "view-team-members",
                "assign-team-member",
                "remove-team-member",
                "transfer-team-member",
                "view-team-reports",
                "view-dashboard",
                "view-team-leader-dashboard",
                "view-cost",
                "view-templates",
                "apply-template",
                "view-team-conversations",
                "create-conversation",
                "manage-conversation-participants",
                "send-message",
                "edit-own-message",
                "delete-own-message",
                "view-own-notifications",
                "mark-notifications-read",
                "send-notifications",
                "upload-files",
                "download-files",
                "view-project-files",
                "view-task-files",
                "view-message-files",
                "upload-project-files",
                "upload-task-files",
                "upload-message-files",
                "delete-own-files",
            ],
        ],
        "Team Member" => [
            "description" => "Executes assigned tasks",
            "permissions" => [
                "view-own-projects",
                "view-project-members",
                "view-project-responsibilities",
                "view-phases",
                "view-own-tasks",
                "edit-own-task",
                "complete-task",
                "view-own-subtasks",
                "edit-own-subtask",
                "complete-subtask",
                "view-user-profile",
                "edit-own-profile",
                "view-dashboard",
                "view-member-dashboard",
                "view-templates",
                "view-own-conversations",
                "create-conversation",
                "send-message",
                "edit-own-message",
                "delete-own-message",
                "view-own-notifications",
                "mark-notifications-read",
                "upload-files",
                "download-files",
                "view-project-files",
                "view-task-files",
                "view-message-files",
                "upload-project-files",
                "upload-task-files",
                "upload-message-files",
                "delete-own-files",
            ],
        ],
    ],

    "seed_users" => [
        [
            "name" => "System Administrator",
            "email" => "test@example.com",
            "password" => "password",
            "role" => "System Administrator",
        ],
    ],
];
