<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('sekolah') ?? $this->route('id');

        return [
            'kode_sekolah' => ['required', 'string', 'max:20', Rule::unique('tb_sekolah', 'kode_sekolah')->ignore($id, 'id_sekolah')],
            'nama_sekolah' => ['required', 'string', 'max:150'],
            'alamat_sekolah' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:200'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
