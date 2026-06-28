@php
    $selectedMembers = old('member_ids', $team->exists ? $team->members->pluck('id')->all() : []);
@endphp

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="name">Team Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $team->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $team->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="team_leader_id">Team Leader</label>
                    <select name="team_leader_id" id="team_leader_id" class="form-control @error('team_leader_id') is-invalid @enderror">
                        <option value="">Unassigned</option>
                        @foreach ($leaders as $leader)
                            <option value="{{ $leader->id }}" @selected(old('team_leader_id', $team->team_leader_id) == $leader->id)>
                                {{ $leader->name }} ({{ $leader->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('team_leader_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="parent_team_id">Parent Team</label>
                    <select name="parent_team_id" id="parent_team_id" class="form-control @error('parent_team_id') is-invalid @enderror">
                        <option value="">Top level</option>
                        @foreach ($parentTeams as $parentTeam)
                            <option value="{{ $parentTeam->id }}" @selected(old('parent_team_id', $team->parent_team_id) == $parentTeam->id)>
                                {{ $parentTeam->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_team_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="member_ids">Team Members</label>
            <select name="member_ids[]" id="member_ids" class="form-control @error('member_ids') is-invalid @enderror" multiple size="8">
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" @selected(in_array($member->id, $selectedMembers))>
                        {{ $member->name }} ({{ $member->email }})
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Hold Ctrl to select more than one member.</small>
            @error('member_ids')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.organization.teams.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>
</div>
