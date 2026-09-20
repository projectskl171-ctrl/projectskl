<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat_supplier' => ['nullable', 'string'],
        ];
    }
}
