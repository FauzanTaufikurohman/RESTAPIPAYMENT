<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessTransfer;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletController
{
    public function topUp(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $user = $request->user();
        $amount = (float) $validated['amount'];

        $user->balance = (float) $user->balance + $amount;
        $user->save();

        $transaction = Transaction::create([
            'transaction_id' => (string) Str::uuid(),
            'user_id' => $user->user_id,
            'type' => 'top_up',
            'amount' => $amount,
            'description' => 'Top up balance',
            'balance_before' => $user->balance - $amount,
            'balance_after' => $user->balance,
            'status' => 'success',
        ]);

        return response()->json([
            'message' => 'Top up successful.',
            'transaction' => $transaction,
            'balance' => $user->balance,
        ], 201);
    }

    public function payment(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $amount = (float) $validated['amount'];

        if ((float) $user->balance < $amount) {
            return response()->json(['message' => 'Insufficient balance.'], 422);
        }

        $user->balance = (float) $user->balance - $amount;
        $user->save();

        $transaction = Transaction::create([
            'transaction_id' => (string) Str::uuid(),
            'user_id' => $user->user_id,
            'type' => 'payment',
            'amount' => $amount,
            'description' => $validated['description'] ?? 'Payment',
            'balance_before' => $user->balance + $amount,
            'balance_after' => $user->balance,
            'status' => 'success',
        ]);

        return response()->json([
            'message' => 'Payment successful.',
            'transaction' => $transaction,
            'balance' => $user->balance,
        ], 201);
    }

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'receiver_phone' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $sender = $request->user();
        $receiver = User::where('phone_number', $validated['receiver_phone'])->first();

        if (! $receiver) {
            return response()->json(['message' => 'Receiver not found.'], 404);
        }

        if ((float) $sender->balance < (float) $validated['amount']) {
            return response()->json(['message' => 'Insufficient balance.'], 422);
        }

        $transfer = Transfer::create([
            'transfer_id' => (string) Str::uuid(),
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? 'Transfer',
            'status' => 'queued',
        ]);

        ProcessTransfer::dispatch($transfer->transfer_id);

        return response()->json([
            'message' => 'Transfer queued for processing.',
            'status' => 'queued',
            'transfer' => $transfer,
        ], 202);
    }
}
