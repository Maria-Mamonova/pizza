<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string", example="Иван Петров"),
 *     @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="MySecretPass123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="MySecretPass123"),
 *     @OA\Property(property="phone", type="string", example="+79001234567", nullable=true),
 * )
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string'],
        ];
    }
}
