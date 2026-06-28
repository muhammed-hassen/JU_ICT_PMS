@extends('layouts.master')

@section('subtitle', 'Member Details')
@section('content_header_title', $member->name)
@section('content_header_subtitle', 'Team member')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Member Profile</h3>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> {{ $member->email }}</p>
            <p><strong>Teams:</strong></p>
            @forelse ($member->teams as $team)
                <a href="{{ route('admin.organization.teams.show', $team) }}" class="badge badge-info mr-1">{{ $team->name }}</a>
            @empty
                <span class="text-muted">No teams assigned.</span>
            @endforelse
        </div>
        <div class="card-footer">
            @canany(['manage-team-members', 'transfer-team-member'])
                <a href="{{ route('admin.organization.members.edit', $member) }}" class="btn btn-primary btn-sm">Edit Member</a>
            @endcanany
            <a href="{{ route('admin.organization.members.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>
@stop
