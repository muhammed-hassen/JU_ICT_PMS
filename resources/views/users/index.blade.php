@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Users</h1>
    </div>
@stop

@section('content')

<div class="card">

    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">

        <h3 class="card-title">Manage Users</h3>

        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Add User
        </a>

    </div>

    <div class="card-body">
      {{-- ALERTS --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
        {{-- SEARCH --}}
        <form method="GET" action="{{ route('users.index') }}" class="mb-3">
            <div class="input-group">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       class="form-control"
                       placeholder="Search by name or email...">

                <div class="input-group-append">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">

                <thead class="bg-primary text-white">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>

                            {{-- ROLE --}}
                            <td>
                          @php $role = $user->role->name ?? 'Member'; @endphp

<span class="badge
    @if($role == 'Admin') badge-danger
    @elseif($role == 'Director') badge-warning
    @elseif($role == 'Team Leader') badge-info
    @else badge-success
    @endif">
    {{ $role }}
</span>


                            </td>

                            {{-- ACTIONS --}}
                            <td class="text-nowrap">
                            <div class="btn-group">

                                {{-- EDIT --}}
                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="btn btn-sm btn-primary mr-1">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- DELETE --}}
                                <form action="{{ route('users.destroy', $user->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this user?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>
                            </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
            <div class="d-flex justify-content-center mt-3">
    <div class="d-flex justify-content-center mt-3">
    {{ $users->links('pagination::simple-bootstrap-4') }}
</div>
</div>
           
        </div>

    </div>
</div>

@stop
@push('js')
<script>
    setTimeout(function () {
        $(".alert").fadeOut("slow");
    }, 2000); // 2 seconds
</script>
@endpush
