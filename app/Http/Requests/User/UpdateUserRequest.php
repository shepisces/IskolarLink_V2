<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'exists:users,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'avatar' => ['nullable', 'string'],
            'profile' => ['sometimes', 'array'],
        ];
    }
}
