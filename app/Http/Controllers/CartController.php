<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CartUpdateRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * Получить содержимое корзины пользователя или гостя
     *
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Получить корзину",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список товаров в корзине",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/CartItem"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        if (! $cart) {
            return response()->json([]);
        }

        $items = $cart->items()->with('product')->get();
        return response()->json($items);
    }

    /**
     * Добавить или обновить товар в корзине
     *
     * @OA\Post(
     *     path="/api/cart",
     *     summary="Добавить или обновить товар",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=3),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Товар добавлен или обновлён",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item added or updated"),
     *             @OA\Property(property="item", ref="#/components/schemas/CartItem")
     *         )
     *     )
     * )
     */
    public function addOrUpdate(CartUpdateRequest $request)
    {
        $cart = $this->getOrCreateCart($request);

        $item = $cart->items()->updateOrCreate(
            ['product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json(['message' => 'Item added or updated', 'item' => $item]);
    }

    /**
     * Удалить товар из корзины
     *
     * @OA\Delete(
     *     path="/api/cart/{item}",
     *     summary="Удалить товар из корзины",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         required=true,
     *         description="ID элемента корзины",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Товар удалён",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item removed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нельзя удалить чужой товар"
     *     )
     * )
     */
    public function destroy(Request $request, CartItem $item)
    {
        $cart = $this->getOrCreateCart($request);

        if ($item->cart_id !== $cart->id) {
            return response()->json(['error' => 'Item not in your cart'], 403);
        }

        $item->delete();
        return response()->json(['message' => 'Item removed']);
    }

    private function getOrCreateCart(Request $request): Cart
    {
        $user = $request->user();
        $sessionToken = $request->header('X-Session-Token');

        // 1. Если есть токен, ищем гостевую корзину
        if ($sessionToken) {
            $guestCart = Cart::where('session_token', $sessionToken)->first();

            if ($guestCart) {
                if ($user) {
                    // У авторизованного пользователя уже есть корзина?
                    $userCart = Cart::where('user_id', $user->id)->first();

                    if ($userCart) {
                        // Сливаем товары из гостевой корзины в пользовательскую
                        foreach ($guestCart->items as $item) {
                            $userCart->items()->updateOrCreate(
                                ['product_id' => $item->product_id],
                                ['quantity' => $item->quantity]
                            );
                        }

                        // Удаляем гостевую корзину
                        $guestCart->delete();

                        return $userCart;
                    }

                    // Привязываем гостевую корзину к пользователю
                    $guestCart->user_id = $user->id;
                    $guestCart->session_token = null;
                    $guestCart->save();
                }

                return $guestCart;
            }
        }

        // 2. Если есть пользователь — ищем его корзину
        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                return $cart;
            }
        }

        // 3. Если ничего не нашли — создаём новую
        return Cart::create([
            'user_id' => $user?->id,
            'session_token' => $user ? null : $sessionToken,
        ]);
    }
}
