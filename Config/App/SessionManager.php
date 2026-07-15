<?php

final class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        session_name('CYBERPUNKSESSID');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => self::getCookiePath(),
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    private static function getCookiePath(): string
    {
        $path = parse_url(BASE_URL, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        $path = trim($path, '/');

        return $path === ''
            ? '/'
            : '/' . $path . '/';
    }

    private static function isHttps(): bool
    {
        return (
            !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
        )
            || (
                isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
            )
            || (
                isset($_SERVER['SERVER_PORT'])
                && (int) $_SERVER['SERVER_PORT'] === 443
            );
    }
}
