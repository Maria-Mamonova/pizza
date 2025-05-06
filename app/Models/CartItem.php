<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CartItem",
 *     type="object",
 *     required={"id", "cart_id", "product_id", "quantity"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="cart_id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=2)
 * )
 */
class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
