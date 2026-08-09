{{-- resources/views/admin/rbac/permissions/index.blade.php --}}
@extends('layouts.master')

@section('subtitle', 'Permissions')
@section('content_header_title', 'Permissions')
@section('content_header_subtitle', 'Manage permission catalog')

@section('content_body')
<div class="container-fluid">
    {{-- Stats Cards Row --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $permissions->total() }}</h3>
                    <p>Total Permissions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-key"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $permissions->groupBy('module')->count() }}</h3>
                    <p>Modules</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $permissions->sum('roles_count') }}</h3>
                    <p>Role Assignments</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>
                        <a href="{{ route('admin.permissions.create') }}" class="text-white" style="text-decoration: none;">
                            <i class="fas fa-plus-circle"></i> New
                        </a>
                    </h3>
                    <p>Create Permission</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Permissions Table Card --}}
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>
                Permission Catalog
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="permissionSearch" class="form-control float-right" placeholder="Search permission...">
                    <div class="input-group-append">
                        <button class="btn btn-default" id="clearSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="permissionsTable">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="22%">Permission Name</th>
                            <th width="18%">Module</th>
                            <th width="30%">Description</th>
                            <th width="10%" class="text-center">Roles</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $index => $permission)
                        <tr class="permission-row">
                            <td><span class="badge badge-light">{{ $permissions->firstItem() + $index }}</span></td>
                            <td>
                                <code class="permission-name">{{ $permission->name }}</code>
                            </td>
                            <td>
                                @php
                                    $moduleStyles = [
                                        'project' => ['color' => '#006633', 'bg' => '#e8f5e9', 'icon' => 'fa-project-diagram'],
                                        'phase' => ['color' => '#0d6efd', 'bg' => '#e7f1ff', 'icon' => 'fa-layer-group'],
                                        'task' => ['color' => '#fd7e14', 'bg' => '#fff3e0', 'icon' => 'fa-tasks'],
                                        'template' => ['color' => '#0d6efd', 'bg' => '#e7f1ff', 'icon' => 'fa-copy'],
                                        'organization' => ['color' => '#6f42c1', 'bg' => '#f3e8ff', 'icon' => 'fa-sitemap'],
                                        'team' => ['color' => '#20c997', 'bg' => '#e8f8f0', 'icon' => 'fa-users'],
                                        'system' => ['color' => '#dc3545', 'bg' => '#ffe8e8', 'icon' => 'fa-cog'],
                                        'user' => ['color' => '#6f42c1', 'bg' => '#f3e8ff', 'icon' => 'fa-user'],
                                        'activity' => ['color' => '#17a2b8', 'bg' => '#e3f4f7', 'icon' => 'fa-history'],
                                    ];
                                    $style = $moduleStyles[$permission->module] ?? ['color' => '#6c757d', 'bg' => '#f8f9fa', 'icon' => 'fa-tag'];
                                @endphp
                                <span class="module-badge" style="background: {{ $style['bg'] }}; color: {{ $style['color'] }};">
                                    <i class="fas {{ $style['icon'] }} mr-1"></i>
                                    {{ ucfirst($permission->module) }}
                                </span>
                            </td>
                            <td>
                                <span class="description-text">{{ $permission->description ?: '—' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="roles-badge">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $permission->roles_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    {{-- SHOW BUTTON --}}
                                    <a href="{{ route('admin.permissions.show', $permission) }}" 
                                       class="btn-action btn-show" 
                                       title="View Permission">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- EDIT BUTTON --}}
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" 
                                       class="btn-action btn-edit" 
                                       title="Edit Permission">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    {{-- DELETE BUTTON --}}
                                    <button type="button" 
                                            class="btn-action btn-delete" 
                                            title="Delete Permission"
                                            onclick="confirmDelete({{ $permission->id }}, '{{ $permission->name }}', {{ $permission->roles_count }})"
                                            {{ $permission->roles_count > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $permission->id }}" 
                                      action="{{ route('admin.permissions.destroy', $permission) }}" 
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
                                <i class="fas fa-key fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No Permissions Found</h5>
                                <p class="text-muted">Create your first permission to get started.</p>
                                <a href="{{ route('admin.permissions.create') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus"></i> Create Permission
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($permissions->hasPages())
        <div class="card-footer clearfix">
            <div class="row">
                <div class="col-sm-12 col-md-5">
                    <div class="dataTables_info" role="status" aria-live="polite">
                        Showing {{ $permissions->firstItem() }} to {{ $permissions->lastItem() }} 
                        of {{ $permissions->total() }} entries
                    </div>
                </div>
                <div class="col-sm-12 col-md-7">
                    <div class="float-right">
                        {{ $permissions->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@push('css')
<style>
    .card-success.card-outline { border-top: 3px solid #006633; }
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
    .permission-name {
        background: #f8f9fa;
        color: #2c3e50;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        border: 1px solid #e9ecef;
        display: inline-block;
    }
    .module-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    .description-text {
        color: #5a6e7c;
        font-size: 0.85rem;
        line-height: 1.4;
    }
    .roles-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e7f3ff;
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
        background-color: #006633;
        border-color: #006633;
    }
    .pagination .page-link { color: #006633; }
    .pagination .page-link:hover { color: #004d26; }
    .card-tools .input-group { border-radius: 8px; overflow: hidden; }
    .card-tools .form-control:focus {
        border-color: #006633;
        box-shadow: none;
    }
    .btn-group .btn-action {
        margin: 0 3px;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $("#permissionSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#permissionsTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        $("#clearSearch").click(function() {
            $("#permissionSearch").val("");
            $("#permissionsTable tbody tr").show();
        });
    });
    
    function confirmDelete(id, name, rolesCount) {
        if (rolesCount > 0) {
            Swal.fire({
                title: 'Cannot Delete!',
                html: `<strong>${name}</strong> is assigned to ${rolesCount} role(s).<br><br>
                       Please remove the permission from all roles first.`,
                icon: 'error',
                confirmButtonColor: '#006633',
                confirmButtonText: 'OK'
            });
            return;
        }
        Swal.fire({
            title: 'Delete Permission?',
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
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    }
</script>
@endpush