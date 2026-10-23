<?php

namespace App\Http\Requests\Admin;

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
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');
        $editingSelf = (int) $userId === (int) $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId), new GmailAddress],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role_id' => ['required', 'exists:roles,id', ...($editingSelf ? [Rule::in([$this->user()->role_id])] : [])],
            'is_active' => ['nullable', 'boolean', function (string $attribute, mixed $value, \Closure $fail) use ($editingSelf): void {
                if ($editingSelf && ! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $fail('You cannot deactivate your own account.');
                }
            }],
        ];
    }
}
