<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $firebaseService;

    /**
     * Create the event listener.
     */
    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        try {
            Log::info('Processing order notification', [
                'order_id' => $event->order->id,
                'user_id' => $event->user->id,
                'user_name' => $event->user->name
            ]);

            // Send notification through Firebase service
            $result = $this->firebaseService->sendOrderNotification($event->order);

            if ($result) {
                Log::info('Order notification sent successfully', [
                    'order_id' => $event->order->id
                ]);
            } else {
                Log::error('Failed to send order notification', [
                    'order_id' => $event->order->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendOrderNotification listener', [
                'order_id' => $event->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderCreated $event, $exception)
    {
        Log::error('SendOrderNotification job failed permanently', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}
