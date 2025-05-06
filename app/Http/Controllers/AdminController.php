<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;

class AdminController extends Controller
{
    // Получить все заказы
    /**
     * Получить список всех заказов (только для админа).
     *
     * @OA\Get(
     *     path="/api/admin/orders",
     *     summary="Получить все заказы (админ)",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Список всех заказов")
     * )
     */
    public function index(): JsonResponse
    {
        $orders = Order::with(['items.product', 'user'])->orderByDesc('created_at')->get();
        return response()->json($orders);
    }

    // Обновить статус заказа
    /**
     * Обновить статус заказа (только для админа).
     *
     * @OA\Post(
     *     path="/api/admin/orders/{order}/status",
     *     summary="Обновить статус заказа (админ)",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"new", "preparing", "on_the_way", "delivered"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Статус обновлён"),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=403, description="Нет доступа")
     * )
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->status = $request->validated()['status'];
        $order->save();

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }

    // Получить один заказ
    public function show(Order $order): JsonResponse
    {
        $order->load(['items.product', 'user']);
        return response()->json($order);
    }

    // Удалить заказ
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }
}
