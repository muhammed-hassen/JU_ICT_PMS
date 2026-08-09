@extends('layouts.master')

@section('subtitle', 'Director Details')
@section('content_header_title', $director->name)
@section('content_header_subtitle', 'ICT Director')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Director Profile</h3>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> {{ $director->email }}</p>
            <p><strong>Led Teams:</strong></p>
            @forelse ($director->ledTeams as $team)
                <a href="{{ route('admin.organization.teams.show', $team) }}" class="badge badge-info mr-1">{{ $team->name }}</a>
            @empty
                <span class="text-muted">No teams assigned.</span>
            @endforelse
        </div>
        <div class="card-footer">
            @can('manage-organization-structure')
                <a href="{{ route('admin.organization.directors.edit', $director) }}" class="btn btn-primary btn-sm">Edit Director</a>
            @endcan
            <a href="{{ route('admin.organization.directors.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>
@stop
