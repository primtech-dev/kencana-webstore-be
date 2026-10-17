<?php

namespace App\Http\Requests;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi dilakukan oleh middleware permission + pengecekan cabang di controller
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        }
        foreach (['latitude', 'longitude'] as $f) {
            if ($this->input($f) === '') {
                $this->merge([$f => null]);
            }
        }
    }

    public function rules(): array
    {
        $branchId = $this->route('id');

        return [
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('branches', 'code')->whereNull('deleted_at')->ignore($branchId),
            ],
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+\-\s()]{4,50}$/'],
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_user_id' => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where(function ($q) use ($branchId) {
                    // manager harus user aktif milik cabang ini
                    $q->where('is_active', true)->where('branch_id', $branchId);
                }),
            ],
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode cabang tidak boleh kosong',
            'code.unique' => 'Kode cabang sudah digunakan',
            'name.required' => 'Nama cabang tidak boleh kosong',
            'phone.regex' => 'Format nomor telepon tidak sesuai',
            'latitude.between' => 'Latitude harus di antara -90 dan 90',
            'longitude.between' => 'Longitude harus di antara -180 dan 180',
            'manager_user_id.exists' => 'Manager harus user aktif milik cabang ini',
        ];
    }
}
