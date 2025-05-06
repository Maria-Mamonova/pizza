<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     required={"id", "user_id", "address", "phone", "status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="address", type="string", example="ул. Примерная, 123"),
 *     @OA\Property(property="phone", type="string", example="+79991234567"),
 *     @OA\Property(property="delivery_time", type="string", format="date-time", example="2024-04-25T18:00:00Z"),
 *     @OA\Property(property="status", type="string", example="pending")
 * )
 */
class Order extends Model
{
    protected $fillable = [
        'user_id', 'address', 'phone', 'delivery_time', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
