<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class InvestigateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSecurity() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'investigation_notes' => ['nullable', 'string', 'max:5000'],
            'investigation_photo' => ['nullable', 'image', 'max:5120'],
            'invalid_reason' => ['required_if:action,invalid', 'nullable', 'string', 'max:2000'],
            'action' => ['required', 'in:verify,invalid'],
        ];
    }
}
