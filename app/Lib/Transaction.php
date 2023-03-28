<?php


use App\Models\EMoney;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (!function_exists('accept_withdraw_transaction')) {
    function accept_withdraw_transaction($receiver_user_id, $amount)
    {
        $admin_user_id = User::where('type', 0)->first()->id;

        DB::transaction(function () use ($admin_user_id, $receiver_user_id, $amount) {
            //RECEIVER TRANSACTION
            $account = EMoney::where('user_id', $receiver_user_id)->first();
            $account->current_balance -= $amount;
            $account->save();

            $primary_transaction = Transaction::create([
                'user_id' => $receiver_user_id,
                'ref_trans_id' => null,
                'transaction_type' => WITHDRAW,
                'debit' => $amount,
                'credit' => 0,
                'balance' => $account->current_balance,
                'from_user_id' => $receiver_user_id,
                'to_user_id' => $admin_user_id,
                'note' => null,
                'transaction_id' => Str::random(5) . Carbon::now()->timestamp,
            ]);

            //ADMIN TRANSACTION
            $account = EMoney::where('user_id', $admin_user_id)->first();
            $account->current_balance += $amount;
            $account->save();

            Transaction::create([
                'user_id' => $admin_user_id,
                'ref_trans_id' => $primary_transaction['transaction_id'],
                'transaction_type' => WITHDRAW,
                'debit' => 0,
                'credit' => $amount,
                'balance' => $account->current_balance,
                'from_user_id' => $admin_user_id,
                'to_user_id' => $receiver_user_id,
                'note' => null,
                'transaction_id' => Str::random(5) . Carbon::now()->timestamp,
            ]);
        });
    }
}
