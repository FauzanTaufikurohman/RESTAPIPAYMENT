<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Http\Request;

class TransactionController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::where('user_id', $user->user_id)
            ->orderByDesc('created_at')
            ->get();

        $transferSent = Transfer::where('sender_id', $user->user_id)
            ->orderByDesc('created_at')
            ->get();

        $transferReceived = Transfer::where('receiver_id', $user->user_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => [
                'transactions' => $transactions,
                'sent_transfers' => $transferSent,
                'received_transfers' => $transferReceived,
            ],
        ]);
    }
}
