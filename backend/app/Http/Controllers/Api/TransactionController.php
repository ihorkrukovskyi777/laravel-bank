<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from_account_id' => 'required|exists:accounts,id',
            'to_iban' => 'required|string|exists:accounts,iban',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $fromAccount = Account::findOrFail($request->from_account_id);
        $toAccount = Account::where('iban', $request->to_iban)->firstOrFail();
        $amount = $request->amount;
        $description = $request->description ?? 'Funds Transfer';

        // 1. Authorization Check: Ensure the user owns the source account
        if ($fromAccount->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to perform this transaction.'], 403);
        }

        // 2. Self-transfer Check
        if ($fromAccount->id === $toAccount->id) {
            return response()->json(['success' => false, 'message' => 'Cannot transfer to the same account.'], 422);
        }

        // 3. Sufficient Funds Check
        if (bccomp((string) $fromAccount->balance, (string) $amount, 2) === -1) {
            return response()->json(['success' => false, 'message' => 'Insufficient funds.'], 422);
        }
        
        // 4. Currency Check (for simplicity, we assume same currency for now)
        if ($fromAccount->currency !== $toAccount->currency) {
            return response()->json(['success' => false, 'message' => 'Cross-currency transfers are not supported.'], 422);
        }

        try {
            DB::transaction(function () use ($fromAccount, $toAccount, $amount, $description) {
                $fromAccount->balance -= $amount;
                $fromAccount->save();

                $toAccount->balance += $amount;
                $toAccount->save();

                $transferTransactionType = TransactionType::where('name', 'transfer')->firstOrFail();

                // Створюємо транзакцію для відправника
                Transaction::create([
                    'transaction_type_id' => $transferTransactionType->id,
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'amount' => -$amount,
                    'currency' => $fromAccount->currency,
                    'fee_amount' => 0.00,
                    'net_amount' => -$amount,
                    'status' => 'completed',
                    'description' => $description,
                    'reference_number' => 'TRN' . time() . $fromAccount->id,
                ]);

                // Створюємо транзакцію для отримувача
                 Transaction::create([
                    'transaction_type_id' => $transferTransactionType->id,
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'amount' => $amount,
                    'currency' => $toAccount->currency,
                    'fee_amount' => 0.00,
                    'net_amount' => $amount,
                    'status' => 'completed',
                    'description' => $description,
                    'reference_number' => 'TRN' . time() . $toAccount->id,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Transaction failed, please try again later.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Transfer completed successfully.']);
    }

    /**
     * Deposit funds into an account.
     */
    public function deposit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1.00',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $account = Account::findOrFail($request->account_id);
        $amount = $request->amount;

        // Ensure the user owns the account
        if ($account->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to access this account.'], 403);
        }

        try {
            DB::transaction(function () use ($account, $amount) {
                // Credit the account
                $account->balance += $amount;
                // For deposits, we can also increase the available balance immediately
                $account->available_balance += $amount;
                $account->save();

                $depositTransactionType = TransactionType::where('name', 'deposit')->firstOrFail();

                // Create transaction record for the deposit
                Transaction::create([
                    'to_account_id' => $account->id,
                    'transaction_type_id' => $depositTransactionType->id,
                    'amount' => $amount,
                    'currency' => $account->currency,
                    'fee_amount' => 0.00,
                    'net_amount' => $amount,
                    'status' => 'completed',
                    'description' => 'Account deposit',
                    'reference_number' => 'DEP' . time() . $account->id,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Deposit failed, please try again later.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Deposit successful.']);
    }
}
