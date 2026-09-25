{{-- resources/views/admin/projects/phases/create.blade.php --}}
@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'Create Phase')

@section('content_header')
    <h1>
        <i class="fas fa-plus-circle text-success"></i>
        Create New Phase
        <small>for {{ $project->name }}</small>
    </h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Phase Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.projects.phases.index', $project) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.projects.phases.store', $project) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        {{-- Name --}}
                        <div class="form-group">
                            <label for="name">Phase Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Enter phase name (e.g., Planning, Development)"
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description"
                                      id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4"
                                      placeholder="Describe the phase objectives and scope">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status and Order --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phase_status_id">Status <span class="text-danger">*</span></label>
                                    <select name="phase_status_id"
                                            id="phase_status_id"
                                            class="form-control @error('phase_status_id') is-invalid @enderror"
                                            required>
                                        <option value="">Select Status</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}"
                                                    {{ old('phase_status_id', $phase->phase_status_id ?? '') == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('phase_status_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number"
                                           name="sort_order"
                                           id="sort_order"
                                           class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', $maxOrder) }}"
                                           min="1">
                                    <small class="text-muted">Leave empty to add at the end</small>
                                    @error('sort_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Planned Start Date</label>
                                    <input type="date"
                                           name="start_date"
                                           id="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Planned End Date</label>
                                    <input type="date"
                                           name="end_date"
                                           id="end_date"
                                           class="form-control @error('end_date') is-invalid @enderror"
                                           value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Create Phase
                        </button>
                        <a href="{{ route('admin.projects.phases.index', $project) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Info</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">Project Name</dt>
                        <dd class="col-sm-7">{{ $project->name }}</dd>

                        <dt class="col-sm-5">Total Phases</dt>
                        <dd class="col-sm-7">{{ $project->phases->count() }}</dd>

                        <dt class="col-sm-5">Created By</dt>
                        <dd class="col-sm-7">{{ $project->creator?->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            <span class="badge badge-{{ $project->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($project->status ?? 'Draft') }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tips</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> Each phase can have multiple tasks</li>
                        <li><i class="fas fa-check-circle text-success"></i> Phases can be reordered</li>
                        <li><i class="fas fa-check-circle text-success"></i> Progress is automatically calculated</li>
                        <li><i class="fas fa-check-circle text-success"></i> You can assign status to each phase</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection