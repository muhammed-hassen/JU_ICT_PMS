{{-- resources/views/admin/rbac/roles/index.blade.php --}}
@extends('layouts.master')

@section('subtitle', 'Roles')
@section('content_header_title', 'Roles')
@section('content_header_subtitle', 'Manage roles and their permissions')

@section('content_body')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $roles->total() }}</h3>
                    <p>Total Roles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tag"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $roles->sum('permissions_count') }}</h3>
                    <p>Total Permissions Assigned</p>
                </div>
                <div class="icon">
                    <i class="fas fa-key"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $roles->sum('users_count') }}</h3>
                    <p>Users with Roles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>
                        <a href="{{ route('admin.roles.create') }}" class="text-white" style="text-decoration: none;">
                            <i class="fas fa-plus-circle"></i> New
                        </a>
                    </h3>
                    <p>Create Role</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Role Catalog
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="roleSearch" class="form-control float-right" placeholder="Search role...">
                    <div class="input-group-append">
                        <button class="btn btn-default" id="clearRoleSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="rolesTable">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Role Name</th>
                            <th width="25%">Description</th>
                            <th width="15%" class="text-center">Permissions</th>
                            <th width="15%" class="text-center">Users</th>
                            <th width="20%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $index => $role)
                        <tr class="role-row">
                            <td><span class="badge badge-light">{{ $roles->firstItem() + $index }}</span></td>
                            <td>
                                <strong class="role-name">{{ $role->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    <span class="badge badge-secondary">{{ $role->guard_name }}</span>
                                </small>
                            </td>
                            <td>
                                <span class="description-text">{{ $role->description ?: 'No description' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="permissions-badge">
                                    <i class="fas fa-key mr-1"></i>
                                    {{ $role->permissions_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="users-badge">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $role->users_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    {{-- SHOW BUTTON --}}
                                    <a href="{{ route('admin.roles.show', $role) }}" 
                                       class="btn-action btn-show" 
                                       title="View Role">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- EDIT BUTTON --}}
                                    <a href="{{ route('admin.roles.edit', $role) }}" 
                                       class="btn-action btn-edit" 
                                       title="Edit Role">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    {{-- DELETE BUTTON --}}
                                    <button type="button" 
                                            class="btn-action btn-delete" 
                                            title="Delete Role"
                                            onclick="confirmRoleDelete({{ $role->id }}, '{{ $role->name }}', {{ $role->users_count }})"
                                            {{ $role->users_count > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <form id="delete-role-form-{{ $role->id }}" 
                                      action="{{ route('admin.roles.destroy', $role) }}" 
                                      method="POST" 
                                      class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-user-tag fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No Roles Found</h5>
                                <p class="text-muted">Create your first role to get started.</p>
                                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create Role
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($roles->hasPages())
        <div class="card-footer clearfix">
            <div class="row">
                <div class="col-sm-12 col-md-5">
                    <div class="dataTables_info" role="status" aria-live="polite">
                        Showing {{ $roles->firstItem() }} to {{ $roles->lastItem() }} 
                        of {{ $roles->total() }} entries
                    </div>
                </div>
                <div class="col-sm-12 col-md-7">
                    <div class="float-right">
                        {{ $roles->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@stop

@push('css')
<style>
    .card-primary.card-outline { border-top: 3px solid #0d6efd; }
    .table { margin-bottom: 0; }
    .table thead th {
        background: #fafbfc;
        color: #2c3e50;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    .table tbody td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        background: white;
    }
    .table tbody tr:hover td { background: #fafbfc; }
    .badge-light {
        background: #f0f0f0;
        color: #6c757d;
        font-weight: 500;
        padding: 0.4rem 0.7rem;
        border-radius: 20px;
        font-size: 0.75rem;
    }
    .role-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2c3e50;
    }
    .description-text {
        color: #5a6e7c;
        font-size: 0.85rem;
        line-height: 1.4;
    }
    .permissions-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e8f5e9;
        color: #2e7d32;
        padding: 0.35rem 0.9rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .users-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e3f2fd;
        color: #0d6efd;
        padding: 0.35rem 0.9rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        transition: all 0.2s ease;
        margin: 0 2px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    .btn-show {
        background: #e3f2fd;
        color: #0d6efd;
    }
    .btn-show:hover {
        background: #bbdefb;
        color: #0a58ca;
        transform: translateY(-2px);
    }
    .btn-edit {
        background: #e8f5e9;
        color: #2e7d32;
    }
    .btn-edit:hover {
        background: #c8e6c9;
        color: #1b5e20;
        transform: translateY(-2px);
    }
    .btn-delete {
        background: #ffebee;
        color: #c62828;
    }
    .btn-delete:hover:not(:disabled) {
        background: #ffcdd2;
        color: #b71c1c;
        transform: translateY(-2px);
    }
    .btn-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .small-box {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }
    .small-box:hover { transform: translateY(-5px); }
    .small-box .inner h3 a { color: white; text-decoration: none; }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .pagination .page-link { color: #0d6efd; }
    .pagination .page-link:hover { color: #0a58ca; }
    .btn-group .btn-action {
        margin: 0 3px;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $("#roleSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#rolesTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        $("#clearRoleSearch").click(function() {
            $("#roleSearch").val("");
            $("#rolesTable tbody tr").show();
        });
    });
    
    function confirmRoleDelete(id, name, usersCount) {
        if (usersCount > 0) {
            Swal.fire({
                title: 'Cannot Delete!',
                html: `<strong>${name}</strong> is assigned to ${usersCount} user(s).<br><br>
                       Please remove the role from all users first.`,
                icon: 'error',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'OK'
            });
            return;
        }
        Swal.fire({
            title: 'Delete Role?',
            html: `Are you sure you want to delete <strong>${name}</strong>?<br><br>
                   <small class="text-danger">This action cannot be undone.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-role-form-${id}`).submit();
            }
        });
    }
</script>
@endpush