<?php
/**
 * Middleware Role
 */
class RoleMiddleware
{
    public static function requireRole(string ...$roles): void
    {
        AuthMiddleware::requireLogin();
        if (!isRole(...$roles)) {
            http_response_code(403);
            die('<h1 style="text-align:center;margin-top:50px;">Akses Ditolak. Anda tidak memiliki izin.</h1>');
        }
    }
}
