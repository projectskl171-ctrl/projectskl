<?php

namespace App\Http\Requests;

use App\Models\TbKategori;
use App\Models\TbKelompokKategori;
use App\Models\TbSupplier;
use App\Support\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi role ditangani middleware `role`
    }

    public function rules(): array
    {
        $user = $this->user();
        $schoolId = Tenant::resolveSchoolId($this->input('id_sekolah'), $user);
        $id = $this->route('produk') ?? $this->route('id');

        return [
            'barcode' => [
                'required', 'string', 'max:50',
                Rule::unique('tb_barang', 'barcode')
                    ->where('id_sekolah', $schoolId)
                    ->where('is_delete', 0)
                    ->ignore($id, 'id_barang'),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'id_kategori' => ['required', 'integer', 'exists:tb_kategori,id_kategori'],
            'id_kelompok_kategori' => ['required', 'integer', 'exists:tb_kelompok_kategori,id_kelompok'],
            'id_supplier' => ['required', 'integer', 'exists:tb_supplier,id_supplier'],
            'satuan' => ['nullable', 'string', 'max:20'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if (Tenant::isSuperAdmin($user)) {
                return;
            }
            $schoolId = $user->id_sekolah;

            // FK harus berasal dari sekolah yang sama.
            if ($this->filled('id_kategori')) {
                $ok = TbKategori::where('id_kategori', $this->input('id_kategori'))
                    ->where('is_delete', 0)
                    ->whereHas('kelompok', fn ($q) => $q->where('id_sekolah', $schoolId))
                    ->exists();
                if (! $ok) {
                    $validator->errors()->add('id_kategori', 'Kategori tidak valid untuk sekolah anda.');
                }
            }
            if ($this->filled('id_kelompok_kategori')) {
                $ok = TbKelompokKategori::where('id_kelompok', $this->input('id_kelompok_kategori'))
                    ->where('id_sekolah', $schoolId)->exists();
                if (! $ok) {
                    $validator->errors()->add('id_kelompok_kategori', 'Kelompok kategori tidak valid untuk sekolah anda.');
                }
            }
            if ($this->filled('id_supplier')) {
                $ok = TbSupplier::where('id_supplier', $this->input('id_supplier'))
                    ->where('id_sekolah', $schoolId)->where('is_delete', 0)->exists();
                if (! $ok) {
                    $validator->errors()->add('id_supplier', 'Supplier tidak valid untuk sekolah anda.');
                }
            }
        });
    }
}
