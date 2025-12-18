<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Jobs\ProcessTransfer; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * 1. TOP UP 
     */
    public function topup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();
        $user->increment('balance', $request->amount);

        Transaction::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'topup',
            'status' => 'success',
        ]);

        return response()->json([
            'message' => 'Top up berhasil',
            'current_balance' => $user->balance
        ]);
    }

    /**
     * 2. PAYMENT 
     */
    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();

        if ($user->balance < $request->amount) {
            return response()->json(['message' => 'Saldo tidak mencukupi'], 400);
        }

        $user->decrement('balance', $request->amount);

        Transaction::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'pay',
            'status' => 'success',
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil',
            'remaining_balance' => $user->balance
        ]);
    }

    /**
     * 3. TRANSFER (Background Job / Queue)
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sender = auth()->user(); 

        if ($sender->balance < $request->amount) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi'], 400);
        }

        if ($sender->id === $request->target_user_id) {
            return response()->json(['message' => 'Tidak bisa transfer ke diri sendiri'], 400);
        }

        $transaction = Transaction::create([
            'id' => Str::uuid(),
            'user_id' => $sender->id,
            'target_user_id' => $request->target_user_id,
            'amount' => $request->amount,
            'type' => 'transfer',
            'status' => 'pending', 
        ]);

        ProcessTransfer::dispatch([
            'transaction_id' => $transaction->id,
            'user_id' => $sender->id,
            'target_user_id' => $request->target_user_id,
            'amount' => $request->amount
        ]);

        return response()->json([
            'message' => 'Transfer sedang diproses',
            'transaction_id' => $transaction->id,
            'status' => 'pending'
        ], 202);
    }

    /**
     * 4. TRANSACTION REPORT (Laporan)
     */
    public function report()
    {
        $user = auth()->user();

        $transactions = Transaction::where('user_id', $user->id)
            ->orWhere('target_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat transaksi berhasil dimuat',
            'data' => $transactions
        ]);
    }
}