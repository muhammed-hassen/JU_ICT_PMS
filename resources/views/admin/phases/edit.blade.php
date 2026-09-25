{{-- resources/views/admin/projects/phases/edit.blade.php --}}
@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'Edit Phase')

@section('content_header')
    <h1>
        <i class="fas fa-edit text-warning"></i>
        Edit Phase: {{ $phase->name }}
    </h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Phase Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.phases.show', $phase) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.phases.update', $phase) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        {{-- Name --}}
                        <div class="form-group">
                            <label for="name">Phase Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Enter phase name"
                                   value="{{ old('name', $phase->name) }}"
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
                                      placeholder="Describe the phase">{{ old('description', $phase->description) }}</textarea>
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
                                                    {{ old('phase_status_id', $phase->phase_status_id) == $status->id ? 'selected' : '' }}>
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
                                           value="{{ old('sort_order', $phase->sort_order) }}"
                                           min="1"
                                           required>
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
                                           value="{{ old('start_date', $phase->start_date ? $phase->start_date->format('Y-m-d') : '') }}">
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
                                           value="{{ old('end_date', $phase->end_date ? $phase->end_date->format('Y-m-d') : '') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Phase
                        </button>
                        <a href="{{ route('admin.phases.show', $phase) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Phase Info</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Progress</dt>
                        <dd class="col-sm-6">{{ $phase->progress_percentage }}%</dd>

                        <dt class="col-sm-6">Total Tasks</dt>
                        <dd class="col-sm-6">{{ $phase->tasks->count() }}</dd>

                        <dt class="col-sm-6">Completed Tasks</dt>
                        <dd class="col-sm-6">
                            {{ $phase->tasks->where('progress_percentage', 100)->count() }}
                        </dd>

                        <dt class="col-sm-6">Project</dt>
                        <dd class="col-sm-6">{{ $phase->project->name }}</dd>

                        <dt class="col-sm-6">Created</dt>
                        <dd class="col-sm-6">{{ $phase->created_at->diffForHumans() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection