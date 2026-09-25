@php
    $selectedPermissions = old('permissions', $selectedPermissions ?? []);
@endphp
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
<div class="card-body">
    <div class="form-group">
        <label for="name">Role Name</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $role->name) }}"
            class="form-control @error('name') is-invalid @enderror"
            placeholder="System Administrator"
            required
        >
        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea
            name="description"
            id="description"
            rows="3"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Short summary of what this role is allowed to do"
        >{{ old('description', $role->description) }}</textarea>
        @error('description')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group mb-0">
        <label>Permissions</label>
    </div>

    <div class="row">
        @forelse ($permissionGroups as $module => $permissions)
            <div class="col-lg-6">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">{{ config('rbac.modules')[$module] ?? \Illuminate\Support\Str::headline($module) }}</h3>
                    </div>
                    <div class="card-body">
                        @foreach ($permissions as $permission)
                            <div class="custom-control custom-checkbox mb-2">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    id="permission_{{ $permission->id }}"
                                    class="custom-control-input"
                                    @checked(in_array($permission->id, $selectedPermissions))
                                >
                                <label class="custom-control-label" for="permission_{{ $permission->id }}">
                                    <strong>{{ $permission->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $permission->description ?: 'No description' }}</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning mb-0">
                    No permissions found. Seed permissions first.
                </div>
            </div>
        @endforelse
    </div>

    @error('permissions')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
    @error('permissions.*')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-default">Cancel</a>
</div>
