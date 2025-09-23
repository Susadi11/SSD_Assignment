<?php

class Session
{
    public static function init()
    {
        // Configure secure session cookie attributes before starting the session
        $isHttps = (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        );

        // Always prefer cookies (no URL-based session IDs)
        @ini_set('session.use_only_cookies', '1');
        @ini_set('session.use_cookies', '1');
        @ini_set('session.use_trans_sid', '0');
        @ini_set('session.cookie_httponly', '1');
        if ($isHttps) {
            @ini_set('session.cookie_secure', '1');
        }

        // Set SameSite when supported
        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            // PHP >= 7.3 supports SameSite via array options
            $params = [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ];
            // Apply only if not already active
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_set_cookie_params($params);
            }
        } else {
            // Best-effort fallback: some older PHP builds accept ini for SameSite
            @ini_set('session.cookie_samesite', 'Lax');
        }

        if (version_compare(phpversion(), '5.4.0', '<')) {
            if (session_id() == '') {
                session_start();
            }
        }
        else {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }
    }

    public static function set($key, $val)
    {
        $_SESSION[$key] = $val;
    }

    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        else {
            return false;
        }
    }

    public static function checkSession()
    {
        self::init();
        if (self::get("adminlogin") == false) {
            self::destroy();
            header("Location:login.php");
        }
    }

    public static function checkLogin()
    {
        self::init();
        if (self::get("adminlogin") == true) {
            header("Location:dashboard.php");
        }
    }

    public static function destroy()
    {
        session_destroy();
        header("Location:login.php");
    }
}
?>