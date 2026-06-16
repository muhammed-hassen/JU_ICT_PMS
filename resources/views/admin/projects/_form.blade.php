<div class="card-body">
    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label for="name">Project Name <span class="text-danger">*</span></label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $project->name) }}"
                       required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $project->description) }}</textarea>
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="template_id">Project Template</label>
                <select name="template_id" 
                        id="template_id" 
                        class="form-control @error('template_id') is-invalid @enderror">
                    <option value="">-- No template (manual setup) --</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" 
                            {{ old('template_id', $project->template_id) == $template->id ? 'selected' : '' }}>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    Selecting a template will automatically generate phases and tasks.
                </small>
                @error('template_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            @if(isset($project) && $project->exists)
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" 
                        id="status" 
                        class="form-control @error('status') is-invalid @enderror">
                    <option value="draft" {{ old('status', $project->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="archived" {{ old('status', $project->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" 
                       name="start_date" 
                       id="start_date" 
                       class="form-control @error('start_date') is-invalid @enderror"
                       value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                @error('start_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" 
                       name="end_date" 
                       id="end_date" 
                       class="form-control @error('end_date') is-invalid @enderror"
                       value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}">
                @error('end_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="team_ids">Assign Teams</label>
                <select name="team_ids[]" 
                        id="team_ids" 
                        class="form-control select2"
                        multiple>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}"
                            {{ in_array($team->id, old('team_ids', $project->teams->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Select teams that will work on this project.</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="member_ids">Assign Individual Members</label>
                <select name="member_ids[]" 
                        id="member_ids" 
                        class="form-control select2"
                        multiple>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            {{ in_array($user->id, old('member_ids', $project->members->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Assign specific team members directly to this project.</small>
            </div>
        </div>
    </div>
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> {{ $submitLabel }}
    </button>
    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet">
<style>
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: calc(2.5rem + 2px);
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    });
</script>
@endpush