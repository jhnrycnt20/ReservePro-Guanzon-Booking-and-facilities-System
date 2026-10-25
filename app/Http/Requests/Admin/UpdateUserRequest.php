<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\Role;
use App\Rules\GmailAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => preg_replace('/\s+/u', ' ', trim($this->input('name'))) ?? '']);
        }
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
        if (is_string($this->input('phone'))) {
            $phone = preg_replace('/[\s\-()]/u', '', trim($this->input('phone'))) ?? '';
            if (preg_match('/^(?:\+?63)9(\d{9})$/', $phone, $matches)) {
                $phone = '09'.$matches[1];
            }
            $this->merge(['phone' => $phone]);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');
        $editingSelf = (int) $userId === (int) $this->user()?->id;

        return [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/\pL/u', 'not_regex:/\pC/u'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId), new GmailAddress],
            'phone' => [Rule::requiredIf(fn (): bool => $this->selectedRoleIsGuest()), 'nullable', 'regex:/^09\d{9}$/'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->whereIn('slug', array_map(fn (UserRole $role): string => $role->value, UserRole::cases())), ...($editingSelf ? [Rule::in([$this->user()->role_id])] : [])],
            'is_active' => ['nullable', 'boolean', function (string $attribute, mixed $value, \Closure $fail) use ($editingSelf): void {
                if ($editingSelf && ! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $fail('You cannot deactivate your own account.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already associated with an account, including deleted accounts. Use a different email.',
            'phone.required' => 'A contact number is required for a Guest account.',
            'phone.regex' => 'Enter a valid PH mobile number, e.g. 09171234567.',
            'name.regex' => 'Enter a name containing letters.',
            'name.not_regex' => 'The name cannot contain control characters.',
        ];
    }

    private function selectedRoleIsGuest(): bool
    {
        $roleId = filter_var($this->input('role_id'), FILTER_VALIDATE_INT);

        return $roleId !== false && Role::query()
            ->whereKey($roleId)
            ->where('slug', UserRole::Guest->value)
            ->exists();
    }
}
