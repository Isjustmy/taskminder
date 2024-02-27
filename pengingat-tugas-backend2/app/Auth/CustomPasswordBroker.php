<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;

class CustomPasswordBroker extends PasswordBroker
{
    protected function resetUrl($user, $token)
    {
        // Di sini Anda dapat menyesuaikan URL sesuai kebutuhan aplikasi Anda
        return config('app.frontend_url') . '/reset-password/' . $token . '?email=' . urlencode($user->getEmailForPasswordReset());
    }
}
