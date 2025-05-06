<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Получить список всех продуктов.
     *
     * @OA\Get(
     *     path="/api/products",
     *     summary="Получить список продуктов",
     *     tags={"Products"},
     *     @OA\Response(response=200, description="Список продуктов")
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Product::all());
    }

    /**
     * Получить один продукт по ID.
     *
     * @OA\Get(
     *     path="/api/products/{product}",
     *     summary="Получить продукт по ID",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID продукта",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Продукт найден"),
     *     @OA\Response(response=404, description="Продукт не найден")
     * )
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * Создать новый продукт.
     *
     * @OA\Post(
     *     path="/api/products",
     *     summary="Создать продукт",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Продукт создан"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        return response()->json(['message' => 'Product created', 'product' => $product], 201);
    }

    /**
     * Обновить продукт.
     *
     * @OA\Put(
     *     path="/api/products/{product}",
     *     summary="Обновить продукт",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID продукта",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Продукт обновлён"),
     *     @OA\Response(response=404, description="Продукт не найден")
     * )
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        return response()->json(['message' => 'Product updated', 'product' => $product]);
    }

    /**
     * Удалить продукт.
     *
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     summary="Удалить продукт",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="ID продукта",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Продукт удалён"),
     *     @OA\Response(response=404, description="Продукт не найден")
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
}
