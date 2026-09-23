@extends('layouts.dashboard')

@section('title', 'Users')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Users')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <a href="{{ route('admin.users.create') }}" class="btn btn-rp-primary">Add User</a>
    </div>
    @include('partials.list-filters', [
        'filters' => [
            [
                'name' => 'role_id',
                'label' => 'Role',
                'empty' => 'All roles',
                'value' => request('role_id'),
                'options' => $roles->pluck('name', 'id')->all(),
            ],
            [
                'name' => 'active',
                'label' => 'Active',
                'empty' => 'All',
                'value' => request('active'),
                'options' => [
                    '1' => 'Active',
                    '0' => 'Inactive',
                ],
            ],
        ],
        'searchPlaceholder' => 'Name, Email, or Phone Number',
        'clearUrl' => route('admin.users.index'),
        'embedded' => true,
    ])
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 20%;">Name</th>
                    <th style="width: 24%;">Email</th>
                    <th style="width: 16%;">Phone</th>
                    <th style="width: 14%;">Role</th>
                    <th class="text-center" style="width: 10%;">Active</th>
                    <th style="width: 16%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>{{ $user->role->name ?? '—' }}</td>
                        <td class="text-center">{{ $user->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-nowrap">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-rp-primary">Edit</a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-rp-confirm="Delete this user?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($users, 'links')) {{ $users->withQueryString()->links() }} @endif
</div>
@endsection
