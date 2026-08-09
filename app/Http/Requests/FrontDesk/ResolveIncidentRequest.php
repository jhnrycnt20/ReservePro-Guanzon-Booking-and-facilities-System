<?php

namespace App\Http\Requests\FrontDesk;

use Illuminate\Foundation\Http\FormRequest;

class ResolveIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFrontDesk() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'resolution_notes' => ['required', 'string', 'max:5000'],
            'resolution_action' => ['nullable', 'string', 'max:255'],
        ];
    }
}
