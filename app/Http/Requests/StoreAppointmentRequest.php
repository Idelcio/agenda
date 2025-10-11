<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação de compromissos.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'inicio' => ['required', 'date'],
            'fim' => ['nullable', 'date', 'after_or_equal:inicio'],
            'dia_inteiro' => ['boolean'],
            'notificar_whatsapp' => ['boolean'],
            'whatsapp_numero' => ['nullable', 'string', 'regex:/^\\+[0-9]{8,16}$/'],
            'whatsapp_mensagem' => ['nullable', 'string', 'max:500'],
            'antecedencia_minutos' => ['nullable', 'integer', 'min:5', 'max:10080'],
        ];
    }

    /**
     * Normaliza alguns campos booleanos e o número de WhatsApp.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'dia_inteiro' => $this->boolean('dia_inteiro'),
            'notificar_whatsapp' => $this->boolean('notificar_whatsapp'),
            'whatsapp_numero' => $this->normalizeWhatsapp($this->input('whatsapp_numero')),
        ]);
    }

    private function normalizeWhatsapp(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed);

        if ($digits === '') {
            return null;
        }

        return '+' . ltrim($digits, '+');
    }
}
