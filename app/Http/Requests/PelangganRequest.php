<?php

namespace App\Http\Requests;

use App\Models\TbKelompokPelanggan;
use App\Support\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class PelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_kelompok_pelanggan' => ['required', 'integer', 'exists:tb_kelompok_pelanggan,id_kelompok_pelanggan'],
            'nama_pelanggan' => ['required', 'string', 'max:150'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if (Tenant::isSuperAdmin($user)) {
                return;
            }
            // Kelompok pelanggan harus milik sekolah kasir (tenant via relasi).
            $ok = TbKelompokPelanggan::where('id_kelompok_pelanggan', $this->input('id_kelompok_pelanggan'))
                ->where('id_sekolah', $user->id_sekolah)->exists();
            if (! $ok) {
                $validator->errors()->add('id_kelompok_pelanggan', 'Kelompok pelanggan tidak valid untuk sekolah anda.');
            }
        });
    }
}
