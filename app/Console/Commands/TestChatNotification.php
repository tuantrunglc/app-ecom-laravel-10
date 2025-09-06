<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Notifications\ChatNewMessageNotification;

class TestChatNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:chat-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test chat notification system';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $this->error('No admin user found');
            return;
        }

        $admin->notify(new ChatNewMessageNotification(
            'test-conversation-123',
            999,
            'Test User',
            'This is a test message for Method B',
            'text',
            time()
        ));

        $this->info('Test notification created for admin: ' . $admin->name);
        $this->info('Unread notifications count: ' . $admin->unreadNotifications()->count());
    }
}
