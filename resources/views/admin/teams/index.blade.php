@extends('layouts.master')

@section('content_body')

<div class="card">

    <div class="card-header">

        <h3 class="d-inline">
            Teams
        </h3>

        <a href="{{ route('admin.teams.create') }}"
           class="btn btn-primary float-right">
            Add Team
        </a>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Leader</th>
                    <th>Parent Team</th>
                    <th>Members</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>

            <tbody>

            @forelse($teams as $team)

                <tr>

                    <td>
                        {{ $team->name }}
                    </td>

                    <td>
                        {{ $team->teamLeader->name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $team->parentTeam->name ?? 'None' }}
                    </td>

                    <td>
    {{ $team->members->pluck('name')->implode(', ') ?: 'No Members' }}
</td>
                    <td>

                        <a href="{{ route('admin.teams.edit', $team->id) }}"
                           class="btn btn-primary btn-sm">
                            Edit
                        </a>

                        <form action="{{ route('admin.teams.destroy', $team->id) }}"
                              method="POST"
                              style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="text-center">
                        No teams found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
