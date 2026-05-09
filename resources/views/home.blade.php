@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="mb-2">
        <h1 class="font-weight-bold text-primary">
            JU ICT Project Management System
        </h1>

        <p class="text-muted mb-0">
            Smart platform for managing users, teams, and ICT projects.
        </p>
    </div>
@stop

@section('content')

<div class="row">

    <div class="col-md-12">

        <div class="card shadow-sm border-0">

            <div class="card-body text-center py-5">

                <h2 class="font-weight-bold text-primary mb-3">
                    Welcome to the Dashboard
                </h2>

                <p class="text-muted mx-auto" style="max-width: 700px; font-size: 17px; line-height: 1.8;">
                    This system is designed to simplify ICT project management by
                    organizing users, assigning roles, improving collaboration,
                    and supporting efficient workflow management within the university environment.
                </p>

                <div class="mt-4">

                    <a href="{{ route('users.index') }}" class="btn btn-primary px-4 mr-2">
                        <i class="fas fa-users"></i> Manage Users
                    </a>

                    <a href="#" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-layer-group"></i> View Roles
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

@stop
