<?php
/**
 * Middleware Authentication
 */
class AuthMiddleware
{
    public static function requireLogin(): void
    {
        if (!isLogin()) {
            redirect('index.php?page=login');
        }
    }

    public static function requireGuest(): void
    {
        if (isLogin()) {
            redirect('index.php');
        }
    }
}
