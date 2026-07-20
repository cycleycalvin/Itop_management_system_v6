<?php
declare(strict_types=1);

namespace App\Core;

final class Security
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!$token) {
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            $token = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRF_token'] ?? '';
            if (!$token) {
                $raw = json_decode((string) file_get_contents('php://input'), true);
                $token = $raw['_csrf'] ?? '';
            }
        }
        if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $token)) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
    }

    public static function cleanString(string $value, int $max = 255): string
    {
        $value = trim($value);
        return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
    }

    public static function excerpt(?string $value, int $max = 120): string
    {
        $value = trim((string) $value);
        return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
    }

    public static function validateUpload(array $file, array $extensions): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
            throw new \RuntimeException('File exceeds the 25MB upload limit.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensions, true)) {
            throw new \RuntimeException('Unsupported file type.');
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        return $safeName;
    }
}
