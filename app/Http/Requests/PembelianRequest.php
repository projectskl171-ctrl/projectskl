<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembelianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_supplier' => ['required', 'integer', 'exists:tb_supplier,id_supplier'],
            'tanggal_faktur' => ['nullable', 'date'],
            'nomor_faktur' => ['nullable', 'string', 'max:50'],
            'jenis_transaksi' => ['required', 'in:tunai,kredit'],
            'cara_bayar' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id_barang' => ['required', 'integer', 'exists:tb_barang,id_barang'],
            'lines.*.jumlah' => ['required', 'integer', 'min:1'],
            'lines.*.harga_beli' => ['required', 'numeric', 'min:0'],
        ];
    }
}
