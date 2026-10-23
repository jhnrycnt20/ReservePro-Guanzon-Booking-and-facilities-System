@extends('layouts.dashboard')

@section('title', 'Users')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Users')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
@endif

<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <button type="button" class="btn rp-user-add-button" data-bs-toggle="modal" data-bs-target="#rpUserCreateModal">Add User</button>
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
                                <button type="button" class="btn btn-sm rp-user-edit-button" data-bs-toggle="modal" data-bs-target="#rpUserEditModal{{ $user->id }}">Edit</button>
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

{{-- Create User Modal --}}
@php
    $isUserCreate = old('_modal') === 'user_create';
@endphp
<div class="modal fade" id="rpUserCreateModal" tabindex="-1" aria-labelledby="rpUserCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg rp-user-create-dialog">
        <div class="modal-content rp-admin-modal rp-user-create-modal">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input type="hidden" name="_modal" value="user_create">
                <div class="modal-header">
                    <div class="rp-user-create-heading">
                        <span class="rp-user-create-icon" aria-hidden="true"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <h2 class="modal-title" id="rpUserCreateModalLabel">Add user</h2>
                            <p class="rp-user-create-subtitle">Create an account and assign its access level.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <section class="rp-user-create-section" aria-labelledby="rpUserAccountDetails">
                        <div class="rp-user-create-section-heading">
                            <h3 id="rpUserAccountDetails">Account details</h3>
                            <p>Basic information for the new user.</p>
                        </div>
                        <div class="rp-user-create-grid">
                            <div class="rp-user-create-field">
                                <label class="form-label" for="rpCreateUserName">Full name <span class="rp-user-create-required" aria-hidden="true">*</span></label>
                                <input id="rpCreateUserName" type="text" name="name" class="form-control {{ $isUserCreate && $errors->has('name') ? 'is-invalid' : '' }}" value="{{ $isUserCreate ? old('name') : '' }}" placeholder="Enter full name" maxlength="255" required>
                                @if($isUserCreate && $errors->has('name'))<div class="rp-user-create-error" role="alert">{{ $errors->first('name') }}</div>@endif
                            </div>
                            <div class="rp-user-create-field">
                                <label class="form-label" for="rpCreateUserEmail">Email address <span class="rp-user-create-required" aria-hidden="true">*</span></label>
                                <input id="rpCreateUserEmail" type="email" name="email" class="form-control {{ $isUserCreate && $errors->has('email') ? 'is-invalid' : '' }}" value="{{ $isUserCreate ? old('email') : '' }}" placeholder="you@gmail.com" maxlength="255" required>
                                @if($isUserCreate && $errors->has('email'))<div class="rp-user-create-error" role="alert">{{ $errors->first('email') }}</div>@endif
                            </div>
                            <div class="rp-user-create-field">
                                <label class="form-label" for="rpCreateUserPhone">Phone number <span class="rp-user-create-optional">Optional</span></label>
                                <input id="rpCreateUserPhone" type="tel" name="phone" class="form-control {{ $isUserCreate && $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ $isUserCreate ? old('phone') : '' }}" placeholder="Enter phone number" maxlength="50">
                                @if($isUserCreate && $errors->has('phone'))<div class="rp-user-create-error" role="alert">{{ $errors->first('phone') }}</div>@endif
                            </div>
                            <div class="rp-user-create-field">
                                <label class="form-label" for="rpCreateUserRole">Role <span class="rp-user-create-required" aria-hidden="true">*</span></label>
                                <select id="rpCreateUserRole" name="role_id" class="form-select {{ $isUserCreate && $errors->has('role_id') ? 'is-invalid' : '' }}" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected($isUserCreate && old('role_id') == $role->id)>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @if($isUserCreate && $errors->has('role_id'))<div class="rp-user-create-error" role="alert">{{ $errors->first('role_id') }}</div>@endif
                            </div>
                        </div>
                    </section>
                    <section class="rp-user-create-section rp-user-create-security" aria-labelledby="rpUserSecurity">
                        <div class="rp-user-create-section-heading">
                            <h3 id="rpUserSecurity">Security</h3>
                            <p>Set the password this user will sign in with.</p>
                        </div>
                        <div class="rp-user-create-grid">
                            <div class="rp-user-create-field">
                                <label class="form-label" for="rpCreateUserPassword">Password <span class="rp-user-create-required" aria-hidden="true">*</span></label>
                                <input id="rpCreateUserPassword" type="password" name="password" class="form-control {{ $isUserCreate && $errors->has('password') ? 'is-invalid' : '' }}" autocomplete="new-password" minlength="8" aria-describedby="rpCreateUserPasswordHint" required>
                                @if($isUserCreate && $errors->has('password'))<div class="rp-user-create-error" role="alert">{{ $errors->first('password') }}</div>@endif
                            </div>
                            <div class="rp-user-create-field">
                                <label class="form-label" for="rpCreateUserPasswordConfirm">Confirm password <span class="rp-user-create-required" aria-hidden="true">*</span></label>
                                <input id="rpCreateUserPasswordConfirm" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                            </div>
                        </div>
                        <p class="rp-user-create-hint" id="rpCreateUserPasswordHint"><i class="bi bi-shield-check" aria-hidden="true"></i> Use at least 8 characters.</p>
                    </section>
                </div>
                <div class="modal-footer">
                    <span class="rp-user-create-footer-note"><span class="rp-user-create-required" aria-hidden="true">*</span> Required fields</span>
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn rp-user-add-button">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($users as $user)
@php
    $userModalKey = 'user_edit_'.$user->id;
    $isThisUserEdit = old('_modal') === $userModalKey;
    $userName = $isThisUserEdit ? old('name', $user->name) : $user->name;
    $userEmail = $isThisUserEdit ? old('email', $user->email) : $user->email;
    $userPhone = $isThisUserEdit ? old('phone', $user->phone) : $user->phone;
    $userRoleId = $isThisUserEdit ? old('role_id', $user->role_id) : $user->role_id;
    $userActiveVal = $isThisUserEdit ? old('is_active', $user->is_active ? '1' : '0') : ($user->is_active ? '1' : '0');
    $userPasswordErrors = $isThisUserEdit && ($errors->has('password') || $errors->has('password_confirmation'));
    $userStartInEdit = $isThisUserEdit && $errors->any();
    $userHasAvatar = filled($user->avatar_url);
    $userInitial = strtoupper(mb_substr($user->name ?? 'U', 0, 1));
    $formId = 'rpUserEditForm'.$user->id;
@endphp
<div class="modal fade" id="rpUserEditModal{{ $user->id }}" tabindex="-1" aria-labelledby="rpUserEditModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
        <div class="modal-content rp-admin-modal rp-profile-modal">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title h5 mb-0" id="rpUserEditModalLabel{{ $user->id }}">Edit User</h2>
                    <p class="rp-profile-modal-sub mb-0">Account details</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="rp-profile-layout" data-rp-profile-manage @if($userStartInEdit) data-rp-profile-editing="1" @endif>
                    <div class="rp-profile-view" data-rp-profile-view @if($userStartInEdit) hidden @endif>
                        <div class="rp-profile-hero">
                            <button
                                type="button"
                                class="rp-profile-photo-hit{{ $userHasAvatar ? '' : ' is-empty' }} rp-profile-hero-avatar"
                                data-rp-avatar-view
                                @if($userHasAvatar) data-rp-avatar-src="{{ $user->avatar_url }}" @endif
                                aria-label="{{ $userHasAvatar ? 'View profile photo' : 'No profile photo yet' }}"
                                @if(! $userHasAvatar) disabled @endif
                            >
                                @if($userHasAvatar)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                @else
                                    <span class="rp-profile-avatar-fallback">{{ $userInitial }}</span>
                                @endif
                            </button>
                            <div class="rp-profile-hero-text">
                                <div class="rp-profile-hero-top">
                                    <div class="rp-profile-identity-name">{{ $user->name }}</div>
                                    <button type="button" class="rp-profile-edit-link" data-rp-profile-edit>Edit profile</button>
                                </div>
                                <div class="rp-profile-identity-meta">{{ $user->email }}</div>
                            </div>
                        </div>

                        <dl class="rp-profile-details">
                            <div>
                                <dt>Full name</dt>
                                <dd>{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt>Email</dt>
                                <dd>{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt>Contact number</dt>
                                <dd>{{ $user->phone ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt>Role</dt>
                                <dd>{{ $user->role->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Active account</dt>
                                <dd>{{ $user->is_active ? 'Active' : 'Inactive' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rp-profile-edit" data-rp-profile-edit-panel @if(! $userStartInEdit) hidden @endif>
                        <div class="rp-profile-edit-shell">
                            <div class="rp-profile-edit-bar">
                                <button type="button" class="rp-profile-back-btn" data-rp-profile-cancel aria-label="Back">
                                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                </button>
                                <h3 class="rp-profile-edit-title mb-0">Edit Profile</h3>
                                <button type="submit" form="{{ $formId }}" class="rp-profile-save-icon" aria-label="Save changes" title="Save changes">
                                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                                </button>
                            </div>

                            <form
                                id="{{ $formId }}"
                                method="POST"
                                action="{{ route('admin.users.update', $user) }}"
                                class="rp-profile-form rp-profile-edit-form"
                                data-rp-password-form
                                novalidate
                            >
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_modal" value="{{ $userModalKey }}">

                                <div class="rp-profile-stack">
                                    <div>
                                        <label class="form-label" for="rpUserName{{ $user->id }}">Name</label>
                                        <input id="rpUserName{{ $user->id }}" type="text" name="name" class="form-control rp-profile-soft-input{{ $isThisUserEdit && $errors->has('name') ? ' is-invalid' : '' }}" value="{{ $userName }}" required>
                                        @if($isThisUserEdit)
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @endif
                                    </div>
                                    <div>
                                        <label class="form-label" for="rpUserEmail{{ $user->id }}">Email address</label>
                                        <input id="rpUserEmail{{ $user->id }}" type="email" name="email" class="form-control rp-profile-soft-input{{ $isThisUserEdit && $errors->has('email') ? ' is-invalid' : '' }}" value="{{ $userEmail }}" required>
                                        @if($isThisUserEdit)
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @endif
                                    </div>
                                    <div>
                                        <label class="form-label" for="rpUserPhone{{ $user->id }}">Phone number</label>
                                        <input id="rpUserPhone{{ $user->id }}" type="text" name="phone" class="form-control rp-profile-soft-input{{ $isThisUserEdit && $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ $userPhone }}" placeholder="09171234567">
                                        @if($isThisUserEdit)
                                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @endif
                                    </div>
                                    <div>
                                        <label class="form-label" for="rpUserRole{{ $user->id }}">Role</label>
                                        <select id="rpUserRole{{ $user->id }}" name="role_id" class="form-select rp-profile-soft-input{{ $isThisUserEdit && $errors->has('role_id') ? ' is-invalid' : '' }}" required>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" @selected($userRoleId == $role->id)>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($isThisUserEdit)
                                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @endif
                                    </div>
                                    <div>
                                        <label class="form-label" for="rpUserActive{{ $user->id }}">Active account</label>
                                        <select id="rpUserActive{{ $user->id }}" name="is_active" class="form-select rp-profile-soft-input{{ $isThisUserEdit && $errors->has('is_active') ? ' is-invalid' : '' }}">
                                            <option value="1" @selected($userActiveVal == '1' || $userActiveVal === true || $userActiveVal === 1)>Active</option>
                                            <option value="0" @selected($userActiveVal == '0' || $userActiveVal === false || $userActiveVal === 0)>Inactive</option>
                                        </select>
                                        @if($isThisUserEdit)
                                            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @endif
                                    </div>
                                </div>

                                <div class="rp-profile-pw-dropdown{{ $userPasswordErrors ? ' is-open' : '' }}" data-rp-password-dropdown>
                                    <button
                                        type="button"
                                        class="rp-profile-pw-toggle"
                                        data-rp-password-toggle
                                        aria-expanded="{{ $userPasswordErrors ? 'true' : 'false' }}"
                                    >
                                        <span>Change password</span>
                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                    </button>
                                    <div class="rp-profile-pw-panel" data-rp-password-panel @if(! $userPasswordErrors) hidden @endif>
                                        <div class="rp-profile-stack">
                                            <div>
                                                <label class="form-label" for="rpUserPassword{{ $user->id }}">New password</label>
                                                <input id="rpUserPassword{{ $user->id }}" type="password" name="password" class="form-control rp-profile-soft-input{{ $isThisUserEdit && $errors->has('password') ? ' is-invalid' : '' }}" data-rp-password-field="password" autocomplete="new-password" placeholder="Leave blank to keep">
                                                <div class="invalid-feedback{{ $isThisUserEdit && $errors->has('password') ? ' d-block' : '' }}" data-rp-password-feedback="password">
                                                    @if($isThisUserEdit)
                                                        @error('password'){{ $message }}@enderror
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label" for="rpUserPasswordConfirm{{ $user->id }}">Confirm password</label>
                                                <input id="rpUserPasswordConfirm{{ $user->id }}" type="password" name="password_confirmation" class="form-control rp-profile-soft-input" data-rp-password-field="password_confirmation" autocomplete="new-password">
                                                <div class="invalid-feedback" data-rp-password-feedback="password_confirmation"></div>
                                            </div>
                                        </div>
                                        <ul class="rp-profile-pw-checks list-unstyled mb-0" data-rp-password-pw-checks aria-live="polite">
                                            <li data-rp-pw-rule="length"><i class="bi bi-check-circle" aria-hidden="true"></i><span>8+ characters</span></li>
                                            <li data-rp-pw-rule="upper"><i class="bi bi-check-circle" aria-hidden="true"></i><span>Uppercase</span></li>
                                            <li data-rp-pw-rule="lower"><i class="bi bi-check-circle" aria-hidden="true"></i><span>Lowercase</span></li>
                                            <li data-rp-pw-rule="number"><i class="bi bi-check-circle" aria-hidden="true"></i><span>Number</span></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="rp-profile-actions d-md-none">
                                    <button type="submit" class="btn btn-rp-primary w-100">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalKey = @json(old('_modal'));
    if (!modalKey) return;
    const map = { user_create: 'rpUserCreateModal' };
    const targetId = map[modalKey] || (modalKey.startsWith('user_edit_') ? `rpUserEditModal${modalKey.replace('user_edit_', '')}` : null);
    if (!targetId) return;
    const el = document.getElementById(targetId);
    if (el && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    }
});
</script>
@endpush
