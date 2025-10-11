<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'whatsapp_number' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^\\+[0-9]{8,16}$/',
                Rule::unique(User::class, 'whatsapp_number')->ignore($this->user()->id),
            ],
        ];
    }

    /**
     * Normaliza o número de WhatsApp antes da validação.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('whatsapp_number')) {
            return;
        }

        $raw = trim((string) $this->input('whatsapp_number'));

        if ($raw === '') {
            $this->merge(['whatsapp_number' => null]);
            return;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            $this->merge(['whatsapp_number' => null]);
            return;
        }

        $formatted = '+' . ltrim($digits, '+');

        $this->merge(['whatsapp_number' => $formatted]);
    }
}
