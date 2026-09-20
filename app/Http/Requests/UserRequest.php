<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Support\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user') ?? $this->route('id');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'username' => ['required', 'string', 'max:50', Rule::unique('tb_user', 'username')->ignore($id, 'id_user')],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            // min:3 agar password dummy "123" tetap valid; keamanan dijamin
            // bcrypt (cast `hashed` di TbUser), bukan panjang minimal.
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:3', 'max:100'],
            'id_role' => ['required', 'integer', 'exists:roles,id_role'],
            'id_sekolah' => ['sometimes', 'integer', 'exists:tb_sekolah,id_sekolah'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $actor = $this->user();
            if (! $actor) {
                return;
            }
            $actor->loadMissing('role');

            // Admin sekolah hanya boleh mengelola kasir (tidak boleh memberi
            // role super admin / admin).
            if ($actor->role?->nama_role === Role::ADMIN) {
                $roleId = (int) $this->input('id_role');
                $nama = Role::where('id_role', $roleId)->value('nama_role');
                if ($nama !== Role::KASIR) {
                    $validator->errors()->add('id_role', 'Admin hanya boleh mengelola akun kasir.');
                }
                // id_sekolah dari frontend untuk admin DIABAIKAN dan dipaksa ke
                // sekolahnya sendiri di controller (Tenant::resolveSchoolId),
                // sehingga tidak ada jalur privilege escalation via input.
            }
        });
    }
}
