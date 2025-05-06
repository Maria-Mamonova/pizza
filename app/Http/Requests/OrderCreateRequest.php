<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); // Только для авторизованных
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'delivery_time' => 'required|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'address.required' => 'Адрес обязателен',
            'phone.required' => 'Телефон обязателен',
            'delivery_time.required' => 'Укажите время доставки',
            'delivery_time.after' => 'Время должно быть позже текущего',
        ];
    }
}
