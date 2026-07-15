<?php
/**
 * Helper Validation
 */
class ValidationHelper
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field {$field} wajib diisi.";
            }
        }
        return $errors;
    }

    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function verifyCaptcha(string $secret, string $token, string $ip): array
    {
        $response = @file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret='
            . urlencode($secret)
            . '&response=' . urlencode($token)
            . '&remoteip='  . urlencode($ip)
        );
        if ($response === false) return ['success' => false, '_connection_error' => true];
        return json_decode($response, true) ?? ['success' => false];
    }
}
