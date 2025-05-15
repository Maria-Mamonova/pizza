<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;


/**
 * @OA\Schema(
 *     schema="CartUpdateRequest",
 *     type="object",
 *     required={"product_id", "quantity"},
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         example=3,
 *         description="ID продукта"
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         minimum=1,
 *         example=2,
 *         description="Количество"
 *     )
 * )
 */
class CartUpdateRequest extends FormRequest
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
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $productId = $this->input('product_id');
            $quantity = $this->input('quantity');

            $product = Product::find($productId);
            if (! $product) return;

            $type = $product->type; // 'pizza' или 'drink'
            $user = $this->user();
            $sessionToken = $this->header('X-Session-Token'); // для гостей
            $cartQuery = \App\Models\Cart::query();

            if ($user) {
                $cartQuery->where('user_id', $user->id);
            } elseif ($sessionToken) {
                $cartQuery->where('session_token', $sessionToken);
            }

            $cart = $cartQuery->first();

            $currentTotal = 0;
            if ($cart) {
                $currentTotal = $cart->items()
                    ->whereHas('product', fn ($q) => $q->where('type', $type))
                    ->sum('quantity');
            }

            $maxAllowed = $type === 'pizza' ? 10 : 20;
            $newTotal = $currentTotal + $quantity;

            if ($newTotal > $maxAllowed) {
                $validator->errors()->add('quantity', "Вы не можете добавить больше {$maxAllowed} товаров типа {$type}.");
            }
        });
    }
}
