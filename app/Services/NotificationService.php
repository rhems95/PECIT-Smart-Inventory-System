<?php

namespace App\Services;

use App\Mail\PsisNotificationMail;
use App\Models\PsisNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationService
{
    public function notify(User $user, string $type, string $title, string $message, ?string $link = null): PsisNotification
    {
        $notification = PsisNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);

        $this->sendEmail($user, $title, $message, $link);

        return $notification;
    }

    public function notifyRole(string $role, string $type, string $title, string $message, ?string $link = null): void
    {
        User::role($role)->where('is_active', true)->each(function (User $user) use ($type, $title, $message, $link) {
            $this->notify($user, $type, $title, $message, $link);
        });
    }

    protected function sendEmail(User $user, string $title, string $message, ?string $link = null): void
    {
        if (! config('psis.mail_notifications', true)) {
            return;
        }

        if (blank($user->email) || ! $user->is_active) {
            return;
        }

        try {
            $actionUrl = $this->resolveActionUrl($link);

            Mail::to($user->email)->send(new PsisNotificationMail(
                alertTitle: $title,
                alertMessage: $message,
                actionUrl: $actionUrl,
                recipientName: $user->name,
            ));
        } catch (Throwable $e) {
            // Never block approve/release/payment flows if SMTP fails.
            Log::warning('PSIS email notification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function resolveActionUrl(?string $link): ?string
    {
        if (blank($link)) {
            return url('/dashboard');
        }

        if (str_starts_with($link, 'http://') || str_starts_with($link, 'https://')) {
            return $link;
        }

        return url($link);
    }
}
