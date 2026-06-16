@extends('layouts.master')

@section('subtitle', 'Dashboard')
@section('content_header_title', 'Dashboard')
@section('content_header_subtitle', 'Welcome')

@section('content_body')

<div class="row">

    <div class="col-md-12">

        <div class="card shadow-sm border-0">

            <div class="card-body text-center py-5">

                <h2 class="font-weight-bold text-primary mb-3">
                    JU ICT Project Management System
                </h2>

                <p class="text-muted mx-auto"
                   style="max-width:700px;font-size:17px;line-height:1.8;">
                    This system is designed to simplify ICT project management by
                    organizing users, assigning roles, improving collaboration,
                    and supporting efficient workflow management within the university environment.
                </p>

                <div class="mt-4">

                    <a href="{{ route('users.index') }}"
                       class="btn btn-primary px-4 mr-2">
                        <i class="fas fa-users"></i> Manage Users
                    </a>

                    <a href="#"
                       class="btn btn-outline-secondary px-4">
                        <i class="fas fa-layer-group"></i> View Roles
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

@stop
