{{-- resources/views/admin/rbac/roles/show.blade.php --}}
@extends('layouts.master')

@section('subtitle', 'Role Details')
@section('content_header_title', 'Role Details')
@section('content_header_subtitle', $role->name)

@section('content_body')
<div class="container-fluid">
    {{-- Action Bar --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-secondary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                                <i class="fas fa-arrow-left"></i> Back to Roles
                            </a>
                            <span class="text-muted">|</span>
                            <span class="ml-2 text-muted">
                                <i class="fas fa-user-tag text-primary"></i> 
                                Role ID: <strong>#{{ $role->id }}</strong>
                            </span>
                        </div>
                        <div>
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit Role
                            </a>
                            @if($role->users_count == 0)
                                <button type="button" class="btn btn-danger btn-sm ml-1" 
                                        onclick="confirmRoleDelete({{ $role->id }}, '{{ $role->name }}')">
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
                    <ul class="nav nav-tabs" id="roleTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="details-tab" data-toggle="pill" href="#details" role="tab">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="permissions-tab" data-toggle="pill" href="#permissions" role="tab">
                                <i class="fas fa-key"></i> Permissions
                                <span class="badge badge-primary ml-1">{{ $role->permissions_count }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="users-tab" data-toggle="pill" href="#users" role="tab">
                                <i class="fas fa-users"></i> Users
                                <span class="badge badge-success ml-1">{{ $role->users_count }}</span>
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
                    <div class="tab-content" id="roleTabsContent">
                        {{-- Details Tab --}}
                        <div class="tab-pane fade show active" id="details" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="role-header mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="role-icon mr-3">
                                                <i class="fas fa-user-tag fa-3x text-primary"></i>
                                            </div>
                                            <div>
                                                <h2 class="mb-0">{{ $role->name }}</h2>
                                                <div class="mt-1">
                                                    <span class="badge badge-secondary">{{ $role->guard_name }}</span>
                                                    <span class="badge badge-info">{{ $role->permissions_count }} Permissions</span>
                                                    <span class="badge badge-success">{{ $role->users_count }} Users</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="info-box mb-3">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-align-left"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Description</span>
                                                    <span class="info-box-number" style="font-size: 0.95rem; font-weight: 400;">
                                                        {{ $role->description ?: 'No description provided' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Permissions Tab --}}
                        <div class="tab-pane fade" id="permissions" role="tabpanel">
                            @if($role->permissions->isNotEmpty())
                                <div class="row">
                                    @php
                                        $groupedPermissions = $role->permissions->groupBy('module');
                                    @endphp
                                    @foreach($groupedPermissions as $module => $permissions)
                                        <div class="col-md-6">
                                            <div class="card card-outline card-info mb-3">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-layer-group"></i>
                                                        {{ ucfirst($module) }}
                                                        <span class="badge badge-primary ml-1">{{ $permissions->count() }}</span>
                                                    </h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <ul class="list-group list-group-flush">
                                                        @foreach($permissions as $permission)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <code>{{ $permission->name }}</code>
                                                                    <br>
                                                                    <small class="text-muted">{{ $permission->description ?: 'No description' }}</small>
                                                                </div>
                                                                <a href="{{ route('admin.permissions.show', $permission) }}" 
                                                                   class="btn btn-sm btn-outline-info" 
                                                                   title="View Permission">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-key fa-4x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No Permissions Assigned</h5>
                                    <p class="text-muted">This role has no permissions yet.</p>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Assign Permissions
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Users Tab --}}
                        <div class="tab-pane fade" id="users" role="tabpanel">
                            @if($role->users->isNotEmpty())
                                <div class="row">
                                    @foreach($role->users as $user)
                                        <div class="col-md-6">
                                            <div class="card card-outline card-success mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar mr-3">
                                                            <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" 
                                                                 class="img-circle img-size-40" alt="{{ $user->name }}">
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="card-title mb-0">
                                                                <a href="{{ route('users.show', $user) }}" class="text-dark">
                                                                    {{ $user->name }}
                                                                </a>
                                                            </h5>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-envelope"></i> {{ $user->email }}
                                                            </small>
                                                        </div>
                                                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info">
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
                                    <h5 class="text-muted">No Users Assigned</h5>
                                    <p class="text-muted">This role is not assigned to any users yet.</p>
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
                                                {{ $role->created_at ? $role->created_at->format('F d, Y h:i A') : 'N/A' }}
                                                <br>
                                                <small class="text-muted">{{ $role->created_at ? $role->created_at->diffForHumans() : '' }}</small>
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
                                                {{ $role->updated_at ? $role->updated_at->format('F d, Y h:i A') : 'N/A' }}
                                                <br>
                                                <small class="text-muted">{{ $role->updated_at ? $role->updated_at->diffForHumans() : '' }}</small>
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
                                                <span class="badge badge-secondary">{{ $role->guard_name }}</span>
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
                        <span class="text-muted"><i class="fas fa-user-tag text-primary"></i> Role ID</span>
                        <span class="font-weight-bold">#{{ $role->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-key text-success"></i> Permissions</span>
                        <span class="font-weight-bold">{{ $role->permissions_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-users text-info"></i> Users</span>
                        <span class="font-weight-bold">{{ $role->users_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted"><i class="fas fa-shield-alt text-secondary"></i> Guard</span>
                        <span class="font-weight-bold">{{ $role->guard_name }}</span>
                    </div>
                </div>
            </div>

            {{-- Permission Coverage Card --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Permission Coverage
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="progress" style="height: 30px;">
                            @php
                                $totalPermissions = App\Models\Permission::count();
                                $percentage = $totalPermissions > 0 ? round(($role->permissions_count / $totalPermissions) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-{{ $percentage > 75 ? 'success' : ($percentage > 50 ? 'warning' : 'danger') }}"
                                 role="progressbar"
                                 style="width: {{ $percentage }}%">
                                {{ $percentage }}% of all permissions
                            </div>
                        </div>
                        <small class="text-muted">{{ $role->permissions_count }} out of {{ $totalPermissions }} permissions</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Coverage</span>
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
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Role
                    </a>
                    @if($role->users_count == 0)
                        <button type="button" class="btn btn-danger btn-block" 
                                onclick="confirmRoleDelete({{ $role->id }}, '{{ $role->name }}')">
                            <i class="fas fa-trash"></i> Delete Role
                        </button>
                    @else
                        <button class="btn btn-danger btn-block" disabled>
                            <i class="fas fa-trash"></i> Cannot Delete
                            <span class="badge badge-light ml-1">{{ $role->users_count }} users</span>
                        </button>
                        <small class="text-muted d-block text-center mt-1">
                            Remove from all users before deleting
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Form --}}
<form id="delete-role-form-{{ $role->id }}" 
      action="{{ route('admin.roles.destroy', $role) }}" 
      method="POST" 
      class="d-none">
    @csrf
    @method('DELETE')
</form>
@stop

@push('css')
<style>
    .role-icon {
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
        margin-bottom