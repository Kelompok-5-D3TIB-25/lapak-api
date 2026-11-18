<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DaftarAuditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'peristiwa' => ['sometimes', 'string', 'max:60'],
            'jangkar' => ['sometimes', 'string'],
            'halaman' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}