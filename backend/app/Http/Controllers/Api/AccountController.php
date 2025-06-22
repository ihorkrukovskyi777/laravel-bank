<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    /**
     * Get user's accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $accounts = $user->accounts()->with('accountType')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'account_number' => $account->account_number,
                        'iban' => $account->iban,
                        'account_type' => [
                            'id' => $account->accountType->id,
                            'name' => $account->accountType->name,
                            'display_name' => $account->accountType->display_name,
                            'description' => $account->accountType->description,
                        ],
                        'currency' => $account->currency,
                        'balance' => $account->balance,
                        'available_balance' => $account->available_balance,
                        'formatted_balance' => $account->formatted_balance,
                        'formatted_available_balance' => $account->formatted_available_balance,
                        'status' => $account->status,
                        'opened_at' => $account->opened_at,
                        'created_at' => $account->created_at,
                    ];
                })
            ]
        ]);
    }

    /**
     * Create a new account.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_type_id' => ['required', 'exists:account_types,id'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $accountType = AccountType::findOrFail($request->account_type_id);

        // Генеруємо унікальний номер рахунку
        do {
            $accountNumber = 'UA' . date('Y') . strtoupper(Str::random(2)) . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $accountNumber)->exists());

        // Генеруємо IBAN
        $iban = 'UA' . str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT) . '000000' . $accountNumber;

        $account = Account::create([
            'user_id' => $user->id,
            'account_type_id' => $request->account_type_id,
            'account_number' => $accountNumber,
            'iban' => $iban,
            'currency' => $request->currency,
            'balance' => 1000.00,
            'available_balance' => 1000.00,
            'status' => 'active',
            'opened_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'account' => [
                    'id' => $account->id,
                    'account_number' => $account->account_number,
                    'iban' => $account->iban,
                    'account_type' => [
                        'id' => $accountType->id,
                        'name' => $accountType->name,
                        'display_name' => $accountType->display_name,
                    ],
                    'currency' => $account->currency,
                    'balance' => $account->balance,
                    'available_balance' => $account->available_balance,
                    'status' => $account->status,
                    'opened_at' => $account->opened_at,
                    'created_at' => $account->created_at,
                ]
            ]
        ], 201);
    }

    /**
     * Get account details.
     */
    public function show(Request $request, Account $account): JsonResponse
    {
        $user = $request->user();

        // Перевіряємо, чи рахунок належить користувачу
        if ($account->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'account' => [
                    'id' => $account->id,
                    'account_number' => $account->account_number,
                    'iban' => $account->iban,
                    'account_type' => [
                        'id' => $account->accountType->id,
                        'name' => $account->accountType->name,
                        'display_name' => $account->accountType->display_name,
                        'description' => $account->accountType->description,
                        'min_balance' => $account->accountType->min_balance,
                        'monthly_fee' => $account->accountType->monthly_fee,
                        'interest_rate' => $account->accountType->interest_rate,
                    ],
                    'currency' => $account->currency,
                    'balance' => $account->balance,
                    'available_balance' => $account->available_balance,
                    'formatted_balance' => $account->formatted_balance,
                    'formatted_available_balance' => $account->formatted_available_balance,
                    'status' => $account->status,
                    'opened_at' => $account->opened_at,
                    'closed_at' => $account->closed_at,
                    'created_at' => $account->created_at,
                ]
            ]
        ]);
    }

    /**
     * Get account transactions.
     */
    public function transactions(Request $request, Account $account): JsonResponse
    {
        $user = $request->user();

        // Перевіряємо, чи рахунок належить користувачу
        if ($account->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        $transactions = $account->transactions()
            ->with(['transactionType', 'fromAccount.user', 'toAccount.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'account' => [
                    'id' => $account->id,
                    'account_number' => $account->account_number,
                    'balance' => $account->balance,
                    'currency' => $account->currency,
                ],
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]
        ]);
    }

    /**
     * Get account balance.
     */
    public function balance(Request $request, Account $account): JsonResponse
    {
        $user = $request->user();

        // Перевіряємо, чи рахунок належить користувачу
        if ($account->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'account_id' => $account->id,
                'account_number' => $account->account_number,
                'balance' => $account->balance,
                'available_balance' => $account->available_balance,
                'currency' => $account->currency,
                'formatted_balance' => $account->formatted_balance,
                'formatted_available_balance' => $account->formatted_available_balance,
                'status' => $account->status,
                'last_updated' => $account->updated_at,
            ]
        ]);
    }

    /**
     * Block account.
     */
    public function block(Request $request, Account $account): JsonResponse
    {
        $user = $request->user();

        // Перевіряємо, чи рахунок належить користувачу
        if ($account->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        if ($account->isBlocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is already blocked'
            ], 400);
        }

        $account->update(['status' => 'blocked']);

        return response()->json([
            'success' => true,
            'message' => 'Account blocked successfully',
            'data' => [
                'account_id' => $account->id,
                'status' => $account->status,
            ]
        ]);
    }

    /**
     * Unblock account.
     */
    public function unblock(Request $request, Account $account): JsonResponse
    {
        $user = $request->user();

        // Перевіряємо, чи рахунок належить користувачу
        if ($account->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }

        if (!$account->isBlocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is not blocked'
            ], 400);
        }

        $account->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Account unblocked successfully',
            'data' => [
                'account_id' => $account->id,
                'status' => $account->status,
            ]
        ]);
    }

    /**
     * Get available account types.
     */
    public function accountTypes(): JsonResponse
    {
        $accountTypes = AccountType::all();

        return response()->json([
            'success' => true,
            'data' => [
                'account_types' => $accountTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'display_name' => $type->display_name,
                        'description' => $type->description,
                        'min_balance' => $type->min_balance,
                        'monthly_fee' => $type->monthly_fee,
                        'interest_rate' => $type->interest_rate,
                        'annual_interest_rate' => $type->annual_interest_rate,
                    ];
                })
            ]
        ]);
    }
}
