<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliverOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // seconds

    /**
     * Create a new job instance.
     */
    public function __construct(public int $orderId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['cart.product', 'user'])->find($this->orderId);
        if (!$order) {
            return; // order removed
        }

        // Only auto-deliver if still processing
        if ($order->status !== 'process') {
            return;
        }

        // Mark as delivered
        $order->status = 'delivered';
        $order->save();

        // Refund + commission logic with idempotency
        $user = $order->user;
        if (!$user) {
            return;
        }

        $orderTag = '#' . $order->order_number;
        $balancePointer = (float) ($user->wallet_balance ?? 0);

        // 1) Refund purchase amount once
        $refundAmount = (float) ($order->total_amount ?? 0);
        if ($refundAmount > 0) {
            $alreadyRefunded = WalletTransaction::where('user_id', $user->id)
                ->where('type', 'purchase_refund')
                ->where('description', 'like', "%order {$orderTag}%")
                ->exists();

            if (!$alreadyRefunded) {
                $newBalance = $balancePointer + $refundAmount;
                $user->wallet_balance = $newBalance;
                $user->save();

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'purchase_refund',
                    'amount' => $refundAmount,
                    'balance_before' => $balancePointer,
                    'balance_after' => $newBalance,
                    'description' => 'Refund purchase amount for order ' . $orderTag,
                    'status' => 'completed',
                ]);

                $balancePointer = $newBalance;
            }
        }

        // 2) Calculate and add commission once
        $totalCommission = 0.0;
        $commissionDetails = [];
        foreach ($order->cart as $cart) {
            $product = $cart->product;
            if ($product && (float) ($product->commission ?? 0) > 0) {
                $commissionAmount = ($cart->price * $cart->quantity * $product->commission) / 100;
                $totalCommission += (float) $commissionAmount;
                $commissionDetails[] = [
                    'product' => $product->title,
                    'quantity' => (int) $cart->quantity,
                    'price' => (float) $cart->price,
                    'commission_rate' => (float) $product->commission,
                    'commission_amount' => (float) $commissionAmount,
                ];
            }
        }

        if ($totalCommission > 0) {
            $alreadyCommissioned = WalletTransaction::where('user_id', $user->id)
                ->where('type', 'commission')
                ->where('description', 'like', "%order {$orderTag}%")
                ->exists();

            if (!$alreadyCommissioned) {
                $newBalance = $balancePointer + $totalCommission;
                $user->wallet_balance = $newBalance;
                $user->save();

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'commission',
                    'amount' => $totalCommission,
                    'balance_before' => $balancePointer,
                    'balance_after' => $newBalance,
                    'description' => 'Product commission from order ' . $orderTag . '. Details: ' . json_encode($commissionDetails),
                    'status' => 'completed',
                ]);
            }
        }
    }
}