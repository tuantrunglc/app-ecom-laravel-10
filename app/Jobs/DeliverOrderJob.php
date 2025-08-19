<?php

namespace App\Jobs;

use App\Models\Order;
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
        $order = Order::find($this->orderId);
        if (!$order) {
            return; // order removed
        }
        // Only auto-deliver if still processing
        if ($order->status === 'process') {
            $order->status = 'delivered';
            $order->save();
        }
    }
}