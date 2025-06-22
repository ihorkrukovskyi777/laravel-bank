# 🏦 Банківський Проект на Laravel + Vue.js

Сучасний веб-додаток для управління банківськими операціями з використанням Laravel (Backend API) та Vue.js (Frontend SPA).

## 🚀 Швидкий старт

### Вимоги
- PHP 8.1+
- Composer
- Node.js 18+
- Docker & Docker Compose
- MySQL 8.0

### Встановлення

1. **Клонуйте репозиторій**
```bash
git clone <repository-url>
cd laravel-bank
```

2. **Запустіть Docker контейнери**
```bash
docker compose up -d
```

3. **Налаштуйте Laravel Backend**
```bash
cd backend
cp .env.example .env
# Відредагуйте .env файл для підключення до MySQL
composer install
php artisan key:generate
php artisan migrate --seed
```

4. **Запустіть Laravel сервер**
```bash
php artisan serve
```

5. **Налаштуйте Vue.js Frontend** (буде додано пізніше)
```bash
cd frontend
npm install
npm run dev
```

## 📁 Структура проекту

```
laravel-bank/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
├── frontend/               # Vue.js SPA (буде додано)
├── docker-compose.yml      # Docker конфігурація
└── README.md
```

## 🎯 Основні функції

### ✅ Реалізовано
- [x] Базова структура Laravel проекту
- [x] Міграції бази даних
- [x] Моделі Eloquent (User, Account, Transaction, etc.)
- [x] Docker налаштування (MySQL + phpMyAdmin)
- [x] Laravel Sanctum для аутентифікації

### 🔄 В процесі розробки
- [ ] API контролери
- [ ] Аутентифікація та авторизація
- [ ] Управління рахунками
- [ ] Система транзакцій
- [ ] Vue.js frontend

### 📋 Планується
- [ ] Кредитні продукти
- [ ] Депозитні продукти
- [ ] Система сповіщень
- [ ] Адміністративна панель
- [ ] Тестування

## 🛠️ Технологічний стек

### Backend
- **Laravel 10** - PHP фреймворк
- **MySQL 8.0** - база даних
- **Laravel Sanctum** - аутентифікація
- **Docker** - контейнеризація

### Frontend (планується)
- **Vue.js 3** - JavaScript фреймворк
- **Vue Router** - маршрутизація
- **Pinia** - управління станом
- **Tailwind CSS** - стилізація

## 📊 База даних

### Основні таблиці
- `users` - користувачі системи
- `account_types` - типи рахунків
- `accounts` - банківські рахунки
- `transaction_types` - типи транзакцій
- `transactions` - транзакції

### Доступ до бази
- **phpMyAdmin:** http://localhost:8081
- **Логін:** bankuser
- **Пароль:** bankpass
- **База:** bank

## 🔐 Безпека

- Валідація всіх вхідних даних
- Захист від SQL-ін'єкцій
- Шифрування чутливих даних
- Laravel Sanctum для API аутентифікації

## 📝 Ліцензія

MIT License

## 👥 Команда

- Розробник: [Ваше ім'я]

---

*Цей проект знаходиться в активній розробці.*