<div class="card-body">
    <div class="form-group">
        <label for="name">Permission Name</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $permission->name) }}"
            class="form-control @error('name') is-invalid @enderror"
            placeholder="view-all-projects"
            required
        >
        <small class="form-text text-muted">Use lowercase slugs with hyphens, for example <code>view-all-projects</code>.</small>
        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="module">Module</label>
        <input
            type="text"
            name="module"
            id="module"
            list="rbac-module-options"
            value="{{ old('module', $permission->module) }}"
            class="form-control @error('module') is-invalid @enderror"
            placeholder="project"
            required
        >
        <datalist id="rbac-module-options">
            @foreach ($modules as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </datalist>
        @error('module')
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
            placeholder="Explain what this permission allows"
        >{{ old('description', $permission->description) }}</textarea>
        @error('description')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    @if ($permission->exists)
        <div class="form-group mb-0">
            <label>Assigned Roles</label>
            <div>
                @forelse ($permission->roles as $role)
                    <span class="badge badge-info mr-1">{{ $role->name }}</span>
                @empty
                    <span class="text-muted">Not assigned to any role yet.</span>
                @endforelse
            </div>
        </div>
    @endif
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.permissions.index') }}" class="btn btn-default">Cancel</a>
</div>
