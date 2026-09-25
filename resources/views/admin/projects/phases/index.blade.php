@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', "Phases - {$project->name}")

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-layer-group text-primary"></i>
            Project Phases: {{ $project->name }}
        </h1>
        <div>
            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Project
            </a>
            @can('create-phase')
                <a href="{{ route('admin.projects.phases.create', $project) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Phase
                </a>
            @endcan
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Phases ({{ $phases->count() }})</h3>
            <div class="card-tools">
                <span class="badge bg-info">Total: {{ $phases->count() }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($phases->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No phases created yet.</p>
                    @can('create-phase')
                        <a href="{{ route('admin.projects.phases.create', $project) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First Phase
                        </a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px">#</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Tasks</th>
                                <th>Progress</th>
                                <th>Dates</th>
                                <th>Created</th>
                                <th style="min-width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-phases">
                            @foreach($phases as $phase)
                                <tr data-id="{{ $phase->id }}">
                                    <td>
                                        <span class="badge bg-secondary">{{ $phase->sort_order }}</span>
                                    </td>
                                    <td>
                                        <strong>
                                            <a href="{{ route('admin.phases.show', $phase) }}">
                                                {{ $phase->name }}
                                            </a>
                                        </strong>
                                        @if($phase->description)
                                            <br><small class="text-muted">{{ Str::limit($phase->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $phase->status_color }}">
                                            {{ $phase->status?->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $phase->tasks->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; width: 100px;">
                                            <div class="progress-bar bg-{{ $phase->progress_percentage == 100 ? 'success' : 'primary' }}"
                                                 role="progressbar"
                                                 style="width: {{ $phase->progress_percentage }}%">
                                                {{ $phase->progress_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($phase->start_date)
                                            <small>Start: {{ $phase->start_date->format('M d, Y') }}</small>
                                        @endif
                                        @if($phase->end_date)
                                            <br><small>End: {{ $phase->end_date->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $phase->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- VIEW - All authenticated users --}}
                                            <a href="{{ route('admin.phases.show', $phase) }}"
                                               class="btn btn-info"
                                               title="View Phase">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            {{-- EDIT - Only users with edit-phase permission --}}
                                            @can('edit-phase')
                                                <a href="{{ route('admin.phases.edit', $phase) }}"
                                                   class="btn btn-warning"
                                                   title="Edit Phase">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            
                                            {{-- DELETE - Only users with delete-phase permission --}}
                                            @can('delete-phase')
                                                <form action="{{ route('admin.phases.destroy', $phase) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this phase? All tasks will be deleted.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Delete Phase">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @can('reorder-phases')
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Drag and drop phases to reorder them
                </small>
            </div>
        @endcan
    </div>
@endsection

@push('css')
<style>
    .sortable-ghost {
        opacity: 0.5;
        background: #f0f0f0;
    }
    .sortable-chosen {
        background: #e9ecef;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('sortable-phases');
        if (el) {
            new Sortable(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    const order = [];
                    document.querySelectorAll('#sortable-phases tr').forEach(row => {
                        order.push(row.dataset.id);
                    });
                    
                    fetch('{{ route("admin.projects.phases.reorder", $project) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ phases: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
                }
            });
        }
    });
</script>
@endpush