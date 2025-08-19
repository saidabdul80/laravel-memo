<?php

namespace Saidabdulsalam\LaravelMemo\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MemoAssigned extends Notification
{
    use Queueable;

    public $memo;

    public function __construct($memo)
    {
        $this->memo = $memo;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontend = config('memo.frontend_url') ?? 'http://localhost:5173';
        $url = rtrim($frontend, '/') . '/#/memos/' . $this->memo->id;

        return (new MailMessage)
                    ->subject('You have a memo to review')
                    ->greeting('Hello ' . ($notifiable->full_name ?? ''))
                    ->line('A memo was assigned to you for review: ' . ($this->memo->title ?? ''))
                    ->action('Open Memo', $url)
                    ->line('Thank you for using our memo system.');
    }
}
