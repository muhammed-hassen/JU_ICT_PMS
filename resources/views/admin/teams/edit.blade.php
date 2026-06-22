@extends('layouts.master')

@section('content_body')

<div class="card">
    <div class="card-header">
        <h4>Edit Team</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('admin.teams.update', $team->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Team Name --}}
            <div class="form-group">
                <label>Team Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ $team->name }}" required>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control">
                    {{ $team->description }}
                </textarea>
            </div>

            {{-- Team Leader --}}
            <div class="form-group">
                <label>Team Leader</label>
                <select name="team_leader_id" class="form-control">
                    <option value="">Select Leader</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            {{ $team->team_leader_id == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Parent Team (IMPORTANT HIERARCHY) --}}
            <div class="form-group">
                <label>Parent Team</label>
                <select name="parent_team_id" class="form-control">
                    <option value="">None (Top Level)</option>
                    @foreach($teams as $parent)
                        <option value="{{ $parent->id }}"
                            {{ $team->parent_team_id == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Members --}}
            <div class="form-group">
                <label>Team Members</label>
                <select name="members[]" class="form-control" multiple>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            {{ in_array($user->id, $selectedMembers ?? []) ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                Update Team
            </button>

            <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>
</div>

@endsection
