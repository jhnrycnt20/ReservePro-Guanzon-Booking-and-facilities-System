@extends('layouts.dashboard')

@section('title', 'Add User')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Add User')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" data-rp-user-create-form>
    @csrf
    <div class="rp-flow-card">
        <h3 class="h6 mb-3">Account Details</h3>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone <span data-rp-user-phone-required hidden>*</span><span data-rp-user-phone-optional>(optional)</span></label>
                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="09171234567" maxlength="20" data-rp-user-phone>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-lg-5">
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" data-rp-user-role required>
                        <option value="" disabled @selected(! old('role_id'))>Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" data-rp-requires-phone="{{ $role->slug === 'guest' ? '1' : '0' }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Use at least 8 characters, with uppercase, lowercase, and a number.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.users.index') }}" class="rp-avail-btn-secondary rp-avail-btn-secondary--inline">Back</a>
            <button type="submit" class="rp-avail-btn-primary rp-avail-btn-primary--inline">Create User</button>
        </div>
    </div>
</form>
@endsection
