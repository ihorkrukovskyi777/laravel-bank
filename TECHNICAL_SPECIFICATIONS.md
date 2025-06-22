# Технічні Специфікації Банківського Проекту

## 🏗️ Архітектурні рішення

### Backend Architecture (Laravel 10)

#### 1. API-First підхід
- **RESTful API** з JSON відповідями
- **API версіонування** (v1, v2, etc.)
- **OpenAPI/Swagger** документація
- **Rate limiting** та throttling
- **CORS** налаштування

#### 2. Аутентифікація та авторизація
```php
// Використання Laravel Sanctum
composer require laravel/sanctum

// JWT токени для API
// Двофакторна аутентифікація
// Ролі та дозволи (RBAC)
```

#### 3. База даних
```sql
-- Основні таблиці з індексами
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);

CREATE TABLE accounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    account_number VARCHAR(34) UNIQUE NOT NULL,
    account_type ENUM('current', 'savings', 'credit') NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'UAH',
    balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('active', 'blocked', 'closed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_account_number (account_number),
    INDEX idx_status (status)
);

CREATE TABLE transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    from_account_id BIGINT UNSIGNED NULL,
    to_account_id BIGINT UNSIGNED NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    transaction_type ENUM('transfer', 'payment', 'deposit', 'withdrawal') NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    description TEXT NULL,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (to_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_from_account (from_account_id),
    INDEX idx_to_account (to_account_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_reference_number (reference_number)
);
```

### Frontend Architecture (Vue.js 3)

#### 1. Структура проекту
```
frontend/
├── src/
│   ├── components/
│   │   ├── common/
│   │   │   ├── BaseButton.vue
│   │   │   ├── BaseInput.vue
│   │   │   └── BaseModal.vue
│   │   ├── auth/
│   │   │   ├── LoginForm.vue
│   │   │   └── RegisterForm.vue
│   │   ├── dashboard/
│   │   │   ├── AccountCard.vue
│   │   │   ├── TransactionList.vue
│   │   │   └── BalanceWidget.vue
│   │   └── admin/
│   │       ├── UserManagement.vue
│   │       └── SystemStats.vue
│   ├── views/
│   │   ├── Dashboard.vue
│   │   ├── Accounts.vue
│   │   ├── Transactions.vue
│   │   ├── Loans.vue
│   │   └── Admin.vue
│   ├── stores/
│   │   ├── auth.js
│   │   ├── accounts.js
│   │   ├── transactions.js
│   │   └── notifications.js
│   ├── router/
│   │   └── index.js
│   ├── services/
│   │   ├── api.js
│   │   ├── auth.js
│   │   └── websocket.js
│   └── utils/
│       ├── formatters.js
│       ├── validators.js
│       └── constants.js
```

#### 2. State Management (Pinia)
```javascript
// stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/services/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const isAuthenticated = computed(() => !!token.value)

  const login = async (credentials) => {
    try {
      const response = await authApi.login(credentials)
      user.value = response.user
      token.value = response.token
      localStorage.setItem('token', response.token)
      return response
    } catch (error) {
      throw error
    }
  }

  const logout = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    logout
  }
})
```

#### 3. API Service
```javascript
// services/api.js
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
```

## 🔐 Безпека

### 1. Backend Security
```php
// Middleware для rate limiting
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Валідація даних
class TransactionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255'
        ];
    }
}

// Шифрування чутливих даних
class User extends Authenticatable
{
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted'
    ];
}
```

### 2. Frontend Security
```javascript
// Валідація форм
import { ref } from 'vue'
import * as yup from 'yup'

const schema = yup.object({
  amount: yup.number()
    .positive('Сума повинна бути позитивною')
    .max(999999.99, 'Максимальна сума 999,999.99')
    .required('Сума обов\'язкова'),
  to_account: yup.string()
    .required('Номер рахунку обов\'язковий')
    .matches(/^[0-9]{20}$/, 'Невірний формат номера рахунку')
})

// Безпечне зберігання токенів
const secureStorage = {
  setToken: (token) => {
    sessionStorage.setItem('token', token)
  },
  getToken: () => {
    return sessionStorage.getItem('token')
  },
  removeToken: () => {
    sessionStorage.removeItem('token')
  }
}
```

## 📊 База даних - Детальна схема

### 1. Користувачі та аутентифікація
```sql
-- Таблиця користувачів
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NULL,
    password VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    last_login_at TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Ролі користувачів
CREATE TABLE roles (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Зв'язок користувачів з ролями
CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### 2. Банківські рахунки
```sql
-- Типи рахунків
CREATE TABLE account_types (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    min_balance DECIMAL(15,2) DEFAULT 0.00,
    monthly_fee DECIMAL(10,2) DEFAULT 0.00,
    interest_rate DECIMAL(5,4) DEFAULT 0.0000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Банківські рахунки
CREATE TABLE accounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    account_type_id INT UNSIGNED NOT NULL,
    account_number VARCHAR(34) UNIQUE NOT NULL,
    iban VARCHAR(34) UNIQUE NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'UAH',
    balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    available_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('active', 'blocked', 'closed', 'pending') NOT NULL DEFAULT 'pending',
    opened_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_type_id) REFERENCES account_types(id),
    INDEX idx_user_id (user_id),
    INDEX idx_account_number (account_number),
    INDEX idx_iban (iban),
    INDEX idx_status (status),
    INDEX idx_currency (currency)
);
```

### 3. Транзакції
```sql
-- Типи транзакцій
CREATE TABLE transaction_types (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    fee_percentage DECIMAL(5,4) DEFAULT 0.0000,
    fee_fixed DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Транзакції
CREATE TABLE transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transaction_type_id INT UNSIGNED NOT NULL,
    from_account_id BIGINT UNSIGNED NULL,
    to_account_id BIGINT UNSIGNED NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    fee_amount DECIMAL(10,2) DEFAULT 0.00,
    net_amount DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    description TEXT NULL,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    external_reference VARCHAR(100) NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (transaction_type_id) REFERENCES transaction_types(id),
    FOREIGN KEY (from_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (to_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_from_account (from_account_id),
    INDEX idx_to_account (to_account_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_reference_number (reference_number),
    INDEX idx_external_reference (external_reference)
);
```

## 🚀 API Endpoints - Детальна специфікація

### Аутентифікація
```php
// routes/api.php
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('resend-verification', [AuthController::class, 'resendVerification']);
});
```

### Рахунки
```php
Route::middleware(['auth:sanctum'])->prefix('accounts')->group(function () {
    Route::get('/', [AccountController::class, 'index']);
    Route::post('/', [AccountController::class, 'store']);
    Route::get('/{account}', [AccountController::class, 'show']);
    Route::put('/{account}', [AccountController::class, 'update']);
    Route::delete('/{account}', [AccountController::class, 'destroy']);
    Route::get('/{account}/transactions', [AccountController::class, 'transactions']);
    Route::get('/{account}/balance', [AccountController::class, 'balance']);
    Route::post('/{account}/block', [AccountController::class, 'block']);
    Route::post('/{account}/unblock', [AccountController::class, 'unblock']);
});
```

### Транзакції
```php
Route::middleware(['auth:sanctum'])->prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::post('/', [TransactionController::class, 'store']);
    Route::get('/{transaction}', [TransactionController::class, 'show']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
    Route::post('/{transaction}/cancel', [TransactionController::class, 'cancel']);
    Route::get('/export', [TransactionController::class, 'export']);
});
```

## 📱 UI/UX Компоненти

### 1. Базові компоненти
```vue
<!-- BaseButton.vue -->
<template>
  <button
    :class="buttonClasses"
    :disabled="disabled || loading"
    @click="handleClick"
  >
    <span v-if="loading" class="spinner"></span>
    <slot v-else></slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning'].includes(value)
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  disabled: Boolean,
  loading: Boolean
})

const emit = defineEmits(['click'])

const buttonClasses = computed(() => [
  'btn',
  `btn-${props.variant}`,
  `btn-${props.size}`,
  { 'btn-loading': props.loading }
])

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>
```

### 2. Компонент рахунку
```vue
<!-- AccountCard.vue -->
<template>
  <div class="account-card" :class="{ 'account-card--blocked': account.status === 'blocked' }">
    <div class="account-card__header">
      <h3 class="account-card__title">{{ account.account_type.display_name }}</h3>
      <span class="account-card__number">{{ formatAccountNumber(account.account_number) }}</span>
    </div>
    
    <div class="account-card__body">
      <div class="account-card__balance">
        <span class="account-card__currency">{{ account.currency }}</span>
        <span class="account-card__amount">{{ formatAmount(account.balance) }}</span>
      </div>
      
      <div class="account-card__status">
        <span class="status-badge" :class="`status-badge--${account.status}`">
          {{ getStatusText(account.status) }}
        </span>
      </div>
    </div>
    
    <div class="account-card__footer">
      <BaseButton @click="$emit('view-details', account)" variant="secondary" size="small">
        Деталі
      </BaseButton>
      <BaseButton @click="$emit('make-transfer', account)" variant="primary" size="small">
        Переказ
      </BaseButton>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { formatAmount, formatAccountNumber } from '@/utils/formatters'

const props = defineProps({
  account: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['view-details', 'make-transfer'])

const getStatusText = (status) => {
  const statusMap = {
    active: 'Активний',
    blocked: 'Заблокований',
    closed: 'Закритий',
    pending: 'В обробці'
  }
  return statusMap[status] || status
}
</script>
```

## 🔄 Робота з чергами

### 1. Laravel Queue Configuration
```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'redis'),

'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],

// Jobs для обробки транзакцій
class ProcessTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public function __construct(
        public Transaction $transaction
    ) {}

    public function handle(TransactionService $transactionService)
    {
        $transactionService->process($this->transaction);
    }

    public function failed(Throwable $exception)
    {
        Log::error('Transaction processing failed', [
            'transaction_id' => $this->transaction->id,
            'error' => $exception->getMessage()
        ]);
    }
}
```

## 📊 Моніторинг та логування

### 1. Laravel Logging
```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => env('LOG_LEVEL', 'critical'),
    ],
],

// Middleware для логування API запитів
class ApiLoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $request->user()?->id,
            'duration' => round($duration * 1000, 2) . 'ms',
            'status' => $response->getStatusCode(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        return $response;
    }
}
```

## 🧪 Тестування

### 1. Backend Testing
```php
// tests/Feature/TransactionTest.php
class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_make_transfer()
    {
        $user = User::factory()->create();
        $fromAccount = Account::factory()->create(['user_id' => $user->id, 'balance' => 1000]);
        $toAccount = Account::factory()->create(['user_id' => $user->id, 'balance' => 0]);

        $response = $this->actingAs($user)
            ->postJson('/api/transactions/transfer', [
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => 500,
                'currency' => 'UAH',
                'description' => 'Test transfer'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reference_number',
                    'amount',
                    'status',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $fromAccount->id,
            'balance' => 500
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $toAccount->id,
            'balance' => 500
        ]);
    }
}
```

### 2. Frontend Testing
```javascript
// tests/unit/AccountCard.spec.js
import { mount } from '@vue/test-utils'
import AccountCard from '@/components/dashboard/AccountCard.vue'

describe('AccountCard', () => {
  const mockAccount = {
    id: 1,
    account_number: '12345678901234567890',
    account_type: { display_name: 'Поточний рахунок' },
    balance: 1000.50,
    currency: 'UAH',
    status: 'active'
  }

  it('renders account information correctly', () => {
    const wrapper = mount(AccountCard, {
      props: { account: mockAccount }
    })

    expect(wrapper.text()).toContain('Поточний рахунок')
    expect(wrapper.text()).toContain('1,000.50')
    expect(wrapper.text()).toContain('UAH')
  })

  it('emits view-details event when details button is clicked', async () => {
    const wrapper = mount(AccountCard, {
      props: { account: mockAccount }
    })

    await wrapper.find('[data-test="details-button"]').trigger('click')
    
    expect(wrapper.emitted('view-details')).toBeTruthy()
    expect(wrapper.emitted('view-details')[0]).toEqual([mockAccount])
  })
})
```

---

*Цей документ містить детальні технічні специфікації для банківського проекту та може використовуватися як довідник під час розробки.* 