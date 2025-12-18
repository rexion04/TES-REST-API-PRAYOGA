<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $sender = User::lockForUpdate()->find($this->data['user_id']);
            $receiver = User::lockForUpdate()->find($this->data['target_user_id']);

            // Potong saldo pengirim & Tambah saldo penerima
            $sender->decrement('balance', $this->data['amount']);
            $receiver->increment('balance', $this->data['amount']);

            // Update status transaksi menjadi sukses
            Transaction::where('id', $this->data['transaction_id'])
                ->update(['status' => 'success']);
        });
    }
}