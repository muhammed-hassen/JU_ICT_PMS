{{-- resources/views/admin/rbac/permissions/show.blade.php --}}
@extends('layouts.master')

@section('subtitle', 'Permission Details')
@section('content_header_title', 'Permission Details')
@section('content_header_subtitle', $permission->name)

@section('content_body')
<div class="container-fluid">
    {{-- Action Bar --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-secondary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                                <i class="fas fa-arrow-left"></i> Back to Permissions
                            </a>
                            <span class="text-muted">|</span>
                            <span class="ml-2 text-muted">
                                <i class="fas fa-key text-primary"></i> 
                                Permission ID: <strong>#{{ $permission->id }}</strong>
                            </span>
                        </div>
                        <div>
                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit Permission
                            </a>
                            @if($permission->roles_count == 0)
                                <button type="button" class="btn btn-danger btn-sm ml-1" 
                                        onclick="confirmDelete({{ $permission->id }}, '{{ $permission->name }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            @endif
                            <button type="button" class="btn btn-outline-primary btn-sm ml-1" onclick="window.print()">
                                <i class="fas fa-print"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Details Card --}}
        <div class="col-md-8">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="permissionTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="details-tab" data-toggle="pill" href="#details" role="tab">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="roles-tab" data-toggle="pill" href="#roles" role="tab">
                                <i class="fas fa-users"></i> Assigned Roles
                                <span class="badge badge-primary ml-1">{{ $permission->roles_count }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="audit-tab" data-toggle="pill" href="#audit" role="tab">
                                <i class="fas fa-history"></i> Audit
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="permissionTabsContent">
                        {{-- Details Tab --}}
                        <div class="tab-pane fade show active" id="details" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="permission-header mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="permission-icon mr-3">
                                                <i class="fas fa-key fa-3x text-primary"></i>
                                            </div>
                                            <div>
                                                <h2 class="mb-0">{{ $permission->name }}</h2>
                                                <div class="mt-1">
                                                    <span class="badge badge-primary">{{ $permission->module }}</span>
                                                    <span class="badge badge-secondary">{{ $permission->guard_name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box mb-3">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-tag"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Permission Name</span>
                                                    <span class="info-box-number">
                                                        <code class="text-dark">{{ $permission->name }}</code>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box mb-3">
                                                <span class="info-box-icon bg-success"><i class="fas fa-layer-group"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Module</span>
                                                    <span class="info-box-number">
                                                        <span class="badge badge-primary">{{ ucfirst($permission->module) }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="info-box mb-3">
                                                <span class="info-box-icon bg-info"><i class="fas fa-align-left"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Description</span>
                                                    <span class="info-box-number" style="font-size: 0.95rem; font-weight: 400;">
                                                        {{ $permission->description ?: 'No description provided' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Roles Tab --}}
                        <div class="tab-pane fade" id="roles" role="tabpanel">
                            @if($permission->roles->isNotEmpty())
                                <div class="row">
                                    @foreach($permission->roles as $role)
                                        <div class="col-md-6">
                                            <div class="card card-outline card-info mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h5 class="card-title mb-0">
                                                                <i class="fas fa-user-tag text-primary"></i>
                                                                <a href="{{ route('admin.roles.show', $role) }}" class="text-dark">
                                                                    {{ $role->name }}
                                                                </a>
                                                            </h5>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-users"></i> {{ $role->users_count }} users
                                                                <span class="ml-2">
                                                                    <i class="fas fa-key"></i> {{ $role->permissions_count }} permissions
                                                                </span>
                                                            </small>
                                                        </div>
                                                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-4x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No Roles Assigned</h5>
                                    <p class="text-muted">This permission is not assigned to any role yet.</p>
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Manage Roles
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Audit Tab --}}
                        <div class="tab-pane fade" id="audit" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-success"><i class="fas fa-calendar-plus"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Created At</span>
                                            <span class="info-box-number" style="font-size: 0.9rem; font-weight: 400;">
                                                {{ $permission->created_at ? $permission->created_at->format('F d, Y h:i A') : 'N/A' }}
                                                <br>
                                                <small class="text-muted">{{ $permission->created_at ? $permission->created_at->diffForHumans() : '' }}</small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-edit"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Last Updated</span>
                                            <span class="info-box-number" style="font-size: 0.9rem; font-weight: 400;">
                                                {{ $permission->updated_at ? $permission->updated_at->format('F d, Y h:i A') : 'N/A' }}
                                                <br>
                                                <small class="text-muted">{{ $permission->updated_at ? $permission->updated_at->diffForHumans() : '' }}</small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-secondary"><i class="fas fa-shield-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Guard Name</span>
                                            <span class="info-box-number" style="font-size: 0.9rem; font-weight: 400;">
                                                <span class="badge badge-secondary">{{ $permission->guard_name }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Quick Stats Card --}}
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Quick Stats
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-key text-primary"></i> Permission ID</span>
                        <span class="font-weight-bold">#{{ $permission->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-users text-success"></i> Assigned Roles</span>
                        <span class="font-weight-bold">{{ $permission->roles_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-layer-group text-info"></i> Module</span>
                        <span class="font-weight-bold">{{ ucfirst($permission->module) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted"><i class="fas fa-shield-alt text-secondary"></i> Guard</span>
                        <span class="font-weight-bold">{{ $permission->guard_name }}</span>
                    </div>
                </div>
            </div>

            {{-- Usage Card --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Usage Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="progress" style="height: 30px;">
                            @php
                                $totalRoles = App\Models\Role::count();
                                $percentage = $totalRoles > 0 ? round(($permission->roles_count / $totalRoles) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-{{ $percentage > 75 ? 'success' : ($percentage > 50 ? 'warning' : 'danger') }}"
                                 role="progressbar"
                                 style="width: {{ $percentage }}%">
                                {{ $percentage }}% of roles
                            </div>
                        </div>
                        <small class="text-muted">{{ $permission->roles_count }} out of {{ $totalRoles }} roles</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Role Coverage</span>
                        <span>{{ $percentage }}%</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Permission
                    </a>
                    @if($permission->roles_count == 0)
                        <button type="button" class="btn btn-danger btn-block" 
                                onclick="confirmDelete({{ $permission->id }}, '{{ $permission->name }}')">
                            <i class="fas fa-trash"></i> Delete Permission
                        </button>
                    @else
                        <button class="btn btn-danger btn-block" disabled>
                            <i class="fas fa-trash"></i> Cannot Delete
                            <span class="badge badge-light ml-1">{{ $permission->roles_count }} roles</span>
                        </button>
                        <small class="text-muted d-block text-center mt-1">
                            Remove from all roles before deleting
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Form --}}
<form id="delete-form-{{ $permission->id }}" 
      action="{{ route('admin.permissions.destroy', $permission) }}" 
      method="POST" 
      class="d-none">
    @csrf
    @method('DELETE')
</form>
@stop

@push('css')
<style>
    .permission-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e7f1ff;
        border-radius: 12px;
    }
    .info-box {
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
    }
    .info-box:hover {
        background: #f1f3f5;
        border-color: #dee2e6;
    }
    .info-box .info-box-icon {
        border-radius: 8px 0 0 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    .info-box .info-box-content {
        padding: 0.75rem 1rem;
    }
    .info-box .info-box-text {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 2px;
    }
    .info-box .info-box-number {
        font-size: 1rem;
        font-weight: 600;
    }
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: 0;
        font-weight: 500;
        transition: all 0.2s;
    }
    .nav-tabs .nav-link:hover {
        color: #0d6efd;
        background: #f8f9fa;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 3px solid #0d6efd;
        background: transparent;
    }
    .nav-tabs .nav-link i {
        margin-right: 6px;
    }
    .card.card-outline .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.08);
    }
    .badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    .progress {
        border-radius: 8px;
        background: #e9ecef;
    }
    .progress-bar {
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .border-bottom {
        border-bottom: 1px solid #e9ecef !important;
    }
    .btn .badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Delete Permission?',
            html: `Are you sure you want to delete <strong>${name}</strong>?<br><br>
                   <small class="text-danger">⚠️ This action cannot be undone.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    }
</script>
@endpush