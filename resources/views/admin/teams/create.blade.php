@extends('layouts.master')

@section('content_body')

<div class="card">
    <div class="card-header">
        <h4>Create Team</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('admin.teams.store') }}" method="POST">
            @csrf

            {{-- Team Name --}}
            <div class="form-group">
                <label>Team Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            {{-- Team Leader --}}
            <div class="form-group">
                <label>Team Leader</label>
                <select name="team_leader_id" class="form-control">
                    <option value="">Select Leader</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Parent Team --}}
            <div class="form-group">
                <label>Parent Team</label>
                <select name="parent_team_id" class="form-control">
                    <option value="">None (Top Level)</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Team Members (ONLY ONCE - FIXED) --}}
            <div class="form-group">
                <label>Team Members</label>

                <select name="members[]" class="form-control" multiple>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                <small class="text-muted">
                    Hold Ctrl (Windows) or Cmd (Mac) to select multiple.
                </small>

                <div class="mt-2">
                    <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">
                        + Add New Member
                    </a>
                </div>
            </div>

            {{-- Save Button --}}
            <button type="submit" class="btn btn-primary">
                Save Team
            </button>

        </form>

    </div>
</div>

@endsection
