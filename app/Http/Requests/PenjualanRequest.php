<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenjualanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pelanggan' => ['nullable', 'integer', 'exists:tb_pelanggan,id_pelanggan'],
            'cara_bayar' => ['nullable', 'string', 'max:50'],
            'jenis_transaksi' => ['required', 'in:tunai,kredit'],
            'total_bayar' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id_barang' => ['required', 'integer', 'exists:tb_barang,id_barang'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
            'lines.*.diskon_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
