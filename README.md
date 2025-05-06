# 🍕 Laravel Food Ordering API

REST API для оформления заказов на доставку еды.

---

## 🚀 Установка проекта

```bash
git clone https://github.com/Maria-Mamonova/pizza.git
cd pizza
cp .env.docker .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```

## 📚 Swagger-документация
Открыть в браузере:
http://localhost/api/documentation

## 🔐 Авторизация

API использует Sanctum-токены.  
Добавляй в заголовки запросов:

```http
Authorization: Bearer {token}
```

## 👤 Роли пользователей

- **Пользователь** — может регистрироваться, входить и создавать заказы
- **Администратор** (`is_admin = true`) — доступ к `/api/admin/*`

## 🧹 Очистка старых заказов

Artisan-команда (удаляет заказы старше N часов):

```bash
./vendor/bin/sail artisan app:clear-old-orders
```

## 🐳 Docker / Laravel Sail

Весь проект работает через Laravel Sail.

**Запуск:**

```bash
./vendor/bin/sail up -d
```

**Остановка:**

```bash
./vendor/bin/sail down
```

## 🧪 Тестирование

```bash
./vendor/bin/sail test
```
