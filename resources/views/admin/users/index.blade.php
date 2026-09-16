@extends('layouts.dashboard')

@section('title', 'Users & Staff')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Users & Staff')
@section('page_subtitle', 'Manage accounts and role access')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <button type="button" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#userCreateModal">Add User</button>
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
        'searchPlaceholder' => 'Name, email, or phone',
        'clearUrl' => route('admin.users.index'),
        'embedded' => true,
    ])
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>{{ $user->role->name ?? '—' }}</td>
                        <td>{{ $user->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-rp-soft">Edit</a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" data-rp-confirm="Delete this user?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endif
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

<div class="modal fade" id="userCreateModal" tabindex="-1" aria-labelledby="userCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5" id="userCreateModalLabel">Add User</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12 form-check ms-1">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createUserIsActive" @checked(old('is_active', true))>
                            <label class="form-check-label" for="createUserIsActive">Active account</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-rp-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if(($errors->any() && !old('_method')) || request()->query('open') === 'create')
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('userCreateModal');
    if (modalEl && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
});
@endif
</script>
@endpush
