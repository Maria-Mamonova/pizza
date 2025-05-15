<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderCreateRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Получить все заказы текущего пользователя.
     *
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Получить заказы текущего пользователя",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Список заказов")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()->with('items.product')->get();
        return response()->json($orders);
    }

    /**
     * Получить конкретный заказ пользователя.
     *
     * @OA\Get(
     *     path="/api/orders/{order}",
     *     summary="Получить заказ по ID",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Информация о заказе"),
     *     @OA\Response(response=403, description="Нет доступа"),
     *     @OA\Response(response=404, description="Заказ не найден")
     * )
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        return response()->json($order->load('items.product'));
    }

    /**
     * Получить все заказы (только для админа) с пагинацией.
     *
     * @OA\Get(
     *     path="/api/orders/all",
     *     summary="Получить все заказы (пагинация, только для админов)",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество элементов на страницу (по умолчанию 50, максимум 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=50, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов с пагинацией"
     *     ),
     *     @OA\Response(response=403, description="Нет доступа")
     * )
     */
    public function all(Request $request): JsonResponse
    {
        $this->authorize('admin');

        $perPage = (int) $request->get('per_page', 50);
        $perPage = min($perPage, 100); // максимум 100

        $orders = Order::with('items.product')->paginate($perPage);
        return response()->json($orders);
    }

    /**
     * Создать новый заказ из корзины.
     *
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Оформить заказ",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address", "phone", "delivery_time"},
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="delivery_time", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Заказ оформлен"),
     *     @OA\Response(response=400, description="Корзина пуста"),
     *     @OA\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function store(OrderCreateRequest $request): JsonResponse
    {
        $user = $request->user(); // Гарантированно авторизован (по middleware)
        $sessionToken = $request->header('X-Session-Token');

        $cart = Cart::query()
            ->where(function ($query) use ($user, $sessionToken) {
                $query->when($user, fn($q) => $q->orWhere('user_id', $user->id));
                $query->when($sessionToken, fn($q) => $q->orWhere('session_token', $sessionToken));
            })
            ->with('items.product')
            ->first();

        // Добавлена: проверка, пуста ли корзина
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // Привязка корзины к пользователю, если ещё не привязана
        if ($user && $cart->user_id === null) {
            $cart->update([
                'user_id' => $user->id,
                'session_token' => null,
            ]);
        }

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'phone' => $request->phone,
                'delivery_time' => $request->delivery_time,
                'status' => 'new',
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }

            $cart->items()->delete();
            DB::commit();

            return response()->json([
                'message' => 'Заказ оформлен',
                'order' => $order
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            // Генерируем уникальный идентификатор ошибки
            $errorId = Str::uuid()->toString();

            // Логируем подробности
            Log::error("Ошибка при оформлении заказа [{$errorId}]: {$e->getMessage()}", [
                'exception' => $e,
                'user_id' => optional($user)->id,
                'cart_id' => optional($cart)->id,
            ]);

            return response()->json([
                'error' => 'Произошла ошибка при оформлении заказа. Сообщите в поддержку: ' . $errorId,
            ], 500);
        }
    }
}
