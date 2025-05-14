<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     required={"id", "name", "price"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Пицца Маргарита"),
 *     @OA\Property(property="type", type="string", example="pizza"),
 *     @OA\Property(property="price", type="number", format="float", example=599.99)
 * )
 */
class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'price'];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
