@extends('layouts.dashboard')

@section('title', 'Edit User')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Edit User')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="rp-flow-card">
                <h3 class="h6 mb-3">Account Details</h3>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Active account</label>
                    <select name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active', $user->is_active))>Active</option>
                        <option value="0" @selected(! old('is_active', $user->is_active))>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="rp-avail-btn-primary">Save Changes</button>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="rp-flow-card">
                <h3 class="h6 mb-3">Change Password</h3>
                <div class="mb-3">
                    <label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <button type="submit" class="rp-avail-btn-secondary">Update Password</button>
            </div>
        </div>
    </div>
</form>
@endsection
