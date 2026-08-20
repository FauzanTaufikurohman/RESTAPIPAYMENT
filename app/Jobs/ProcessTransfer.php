<?php

namespace App\Jobs;

use App\Models\Transfer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public string $transferId)
    {
    }

    public function handle(): void
    {
        $transfer = Transfer::with(['sender', 'receiver'])->find($this->transferId);

        if (! $transfer || $transfer->status !== 'queued') {
            return;
        }

        DB::transaction(function () use ($transfer) {
            $sender = $transfer->sender;
            $receiver = $transfer->receiver;

            if (! $sender || ! $receiver) {
                $transfer->update(['status' => 'failed', 'description' => 'Sender or receiver not found']);
                return;
            }

            if ($sender->user_id === $receiver->user_id) {
                $transfer->update(['status' => 'failed', 'description' => 'Cannot transfer to yourself']);
                return;
            }

            if ((float) $sender->balance < (float) $transfer->amount) {
                $transfer->update(['status' => 'failed', 'description' => 'Insufficient funds']);
                return;
            }

            $sender->balance = (float) $sender->balance - (float) $transfer->amount;
            $receiver->balance = (float) $receiver->balance + (float) $transfer->amount;

            $sender->save();
            $receiver->save();
            $transfer->update(['status' => 'completed']);
        });
    }
}
