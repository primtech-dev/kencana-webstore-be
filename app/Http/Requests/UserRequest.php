<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // izin via middleware permission; hak atas target user dicek di controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');
        $targetId = $this->route('id');

        /** @var User $actor */
        $actor = $this->user();

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($targetId),
            ],
            'password' => [$isCreate ? 'required' : 'nullable', 'string', 'confirmed', Password::min(8)],
            'branch_id' => [
                'nullable', 'integer',
                Rule::exists('branches', 'id')->whereNull('deleted_at'),
            ],
            'is_superadmin' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => ['string', Rule::in($actor->assignableRoleNames())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak benar',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'password.confirmed' => 'Konfirmasi password tidak sama',
            'password.min' => 'Password minimal 8 karakter',
            'branch_id.exists' => 'Cabang tidak ditemukan',
            'roles.*.in' => 'Role tidak diizinkan untuk Anda',
        ];
    }
}
