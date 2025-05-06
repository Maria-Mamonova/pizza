<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="Pizza Delivery API",
 *     version="1.0.0",
 *     description="Документация REST API для проекта доставки пиццы. Здесь описаны доступные эндпоинты, форматы данных, авторизация и многое другое.",
 *     @OA\Contact(
 *         email="support@pizzadelivery.local",
 *         name="Техподдержка Pizza Delivery"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Основной сервер (localhost)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Введите токен в формате: Bearer {token}"
 * )
 */

class SwaggerController
{
    //
}
