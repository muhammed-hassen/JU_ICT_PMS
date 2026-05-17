@extends('layouts.master')

@section('subtitle', 'Project Templates')
@section('content_header_title', 'Project Templates')
@section('content_header_subtitle', 'Manage predefined project phases and tasks')

@section('content_body')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 template-success-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <div class="flex-grow-1">
                    <strong>Success!</strong> {{ session('status') }}
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="card card-elevated border-0 shadow-sm">
        <div class="card-header template-index-header d-flex justify-content-between align-items-center border-0">
            <div>
                <h3 class="card-title mb-1 text-white fw-600">Template Catalog</h3>
                <p class="template-count-label text-white mb-0">
                    {{ $templates->total() }} {{ \Illuminate\Support\Str::plural('template', $templates->total()) }} available
                </p>
            </div>
            <a href="{{ route('admin.templates.create') }}" class="btn btn-light fw-600 px-4 template-create-button">
                <i class="fas fa-plus mr-2"></i>
                Create Template
            </a>
        </div>

        <div class="card-body p-0">
            @if ($templates->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0 template-index-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-center template-metric-col">Phases</th>
                                <th class="text-center template-metric-col">Tasks</th>
                                <th class="text-center template-status-col">Status</th>
                                <th class="text-right template-actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($templates as $template)
                                <tr>
                                    <td class="template-name-cell">
                                        <div class="template-name-text">{{ $template->name }}</div>
                                    </td>
                                    <td class="template-description-cell">
                                        <span>{{ $template->description ?: '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge template-badge template-badge-phase">
                                            {{ $template->phases_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge template-badge template-badge-task">
                                            {{ $template->tasks_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($template->is_active)
                                            <span class="badge template-badge template-badge-active">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Active
                                            </span>
                                        @else
                                            <span class="badge template-badge template-badge-inactive">
                                                <i class="fas fa-pause-circle mr-1"></i>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right template-actions-cell">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.templates.edit', $template) }}" class="btn template-action-btn template-action-edit">
                                                <i class="fas fa-pen mr-1"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn template-action-btn template-action-delete">
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="template-empty-state">
                    <i class="far fa-folder-open template-empty-icon"></i>
                    <h5 class="text-muted fw-600">No templates yet</h5>
                    <p class="text-muted small">Create your first template to get started.</p>
                    <a href="{{ route('admin.templates.create') }}" class="btn btn-primary template-empty-cta">
                        <i class="fas fa-plus mr-2"></i>
                        Create First Template
                    </a>
                </div>
            @endif
        </div>

        @if ($templates->hasPages())
            <div class="card-footer bg-light border-top">
                <div class="template-pagination-meta text-muted text-center mb-3">
                    Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} templates
                </div>
                <div class="d-flex justify-content-center">
                    {{ $templates->links() }}
                </div>
            </div>
        @endif
    </div>
@stop

@push('css')
<style>
    .card-elevated {
        border-radius: 0.5rem;
        transition: box-shadow 0.3s ease;
    }

    .card-elevated:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .template-success-alert {
        background-color: #d4edda;
        border-left: 4px solid #28a745 !important;
        border-radius: 0.5rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        color: #155724;
    }

    .template-index-header {
        background: linear-gradient(135deg, #1f75cb 0%, #3b5bd6 100%);
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }

    .fw-600 {
        font-weight: 600;
    }

    .template-count-label {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.875rem;
    }

    .template-create-button {
        color: #1f75cb;
    }

    .template-index-table {
        font-size: 0.9rem;
    }

    .template-index-table thead {
        background-color: #f8f9fa;
        border-bottom: 2px solid #e0e0e0;
    }

    .template-index-table thead th {
        color: #212529;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 1.25rem 1.5rem;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .template-index-table tbody tr {
        border-bottom: 1px solid #e0e0e0;
        transition: background-color 0.2s ease;
    }

    .template-index-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .template-index-table tbody td {
        padding: 1.25rem 1.5rem;
        vertical-align: middle;
    }

    .template-name-cell {
        color: #212529;
        font-weight: 600;
    }

    .template-name-text {
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }

    .template-description-cell {
        color: #666;
    }

    .template-metric-col {
        width: 90px;
    }

    .template-status-col {
        width: 110px;
    }

    .template-actions-col {
        width: 190px;
    }

    .template-actions-cell .btn-group {
        gap: 0.375rem;
    }

    .template-badge {
        border-radius: 0.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.4rem 0.8rem;
        text-transform: capitalize;
    }

    .template-badge-phase {
        background-color: #e7f0f7;
        color: #1f75cb;
    }

    .template-badge-task {
        background-color: #f0e7f7;
        color: #6c4285;
    }

    .template-badge-active {
        background-color: #d4edda;
        color: #155724;
    }

    .template-badge-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }

    .template-action-btn {
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        transition: all 0.2s ease;
    }

    .template-action-btn:hover {
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .template-action-edit {
        background-color: #e7f0f7;
        border: 1px solid #1f75cb;
        color: #1f75cb;
    }

    .template-action-edit:hover {
        background-color: #d8e9f8;
        color: #145fa7;
    }

    .template-action-delete {
        background-color: #ffe7e7;
        border: 1px solid #dc3545;
        color: #dc3545;
    }

    .template-action-delete:hover {
        background-color: #ffd8d8;
        color: #b92c3b;
    }

    .template-empty-state {
        padding: 3rem 2rem;
        text-align: center;
    }

    .template-empty-icon {
        color: #ccc;
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.6;
    }

    .template-empty-cta {
        background: linear-gradient(135deg, #1f75cb 0%, #3b5bd6 100%);
        border: none;
        font-weight: 600;
        margin-top: 1rem;
    }

    .template-pagination-meta {
        font-size: 0.875rem;
    }
</style>
@endpush
