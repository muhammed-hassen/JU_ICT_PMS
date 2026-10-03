@extends('layouts.console')

@section('title', 'Users')

@section('content_header')
    <x-ui.page-header title="Users" description="Everyone who can sign in to the system.">
        <x-ui.button :href="route('users.create')">
            <i data-lucide="user-plus" class="size-4"></i>
            Add user
        </x-ui.button>
    </x-ui.page-header>
@stop

@section('content')

    <x-ui.card flush>
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 mr-auto font-sans text-[15px] font-semibold">All users</h3>
            <div class="relative w-full sm:w-72">
                <label for="user-search" class="sr-only">Search users</label>
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
                <x-ui.input id="user-search" size="sm" type="text" name="search" class="pl-9" placeholder="Search by name or email" :value="$search ?? ''" />
            </div>
            <x-ui.button type="submit" variant="outline" size="sm">Search</x-ui.button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted">
                    <tr>
                        <x-ui.th class="w-12">#</x-ui.th>
                        <x-ui.th>Name</x-ui.th>
                        <x-ui.th>Email</x-ui.th>
                        <x-ui.th>Role</x-ui.th>
                        <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php $role = $user->roles->first()?->name ?? 'No role'; @endphp
                        <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                            <x-ui.td class="tabular text-muted-foreground">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</x-ui.td>
                            <x-ui.td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-[12px] font-semibold text-ju-navy">
                                        {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                                    </span>
                                    <span class="font-medium">{{ $user->name }}</span>
                                </div>
                            </x-ui.td>
                            <x-ui.td class="text-muted-foreground">{{ $user->email }}</x-ui.td>
                            <x-ui.td><x-ui.badge>{{ $role }}</x-ui.badge></x-ui.td>
                            <x-ui.td>
                                <div class="flex items-center justify-end gap-0.5">
                                    <x-ui.icon-button icon="pencil" label="Edit user" :href="route('users.edit', $user->id)" />
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.icon-button type="submit" icon="trash-2" label="Delete user" tone="destructive" />
                                    </form>
                                </div>
                            </x-ui.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state icon="users" title="No users found" description="Try a different name or email." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-slot:footer>
            <div class="flex w-full justify-center">
                {{ $users->links('pagination::simple-bootstrap-4') }}
            </div>
        </x-slot:footer>
    </x-ui.card>
@stop

@push('js')
<script>
    setTimeout(function () {
        $('[data-autohide]').fadeOut('slow');
    }, 2000);
</script>
@endpush
