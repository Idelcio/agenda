<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendQuickMessageRequest extends FormRequest
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
            'destinatario' => ['required', 'string', 'regex:/^\\+[0-9]{8,16}$/'],
            'mensagem' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,application/pdf'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasMessage = filled($this->input('mensagem'));
            $hasFile = $this->hasFile('attachment');

            if (! $hasMessage && ! $hasFile) {
                $validator->errors()->add('mensagem', 'Informe uma mensagem ou anexe um arquivo.');
            }
        });
    }
}
