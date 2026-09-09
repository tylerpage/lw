<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'max:0'],
            'form_started_at' => ['required', 'integer'],
        ];
    }

    public function passedValidation(): void
    {
        $startedAt = (int) $this->input('form_started_at');

        if (now()->timestamp - $startedAt < 3) {
            abort(422, 'Please wait a moment before submitting.');
        }
    }
}
