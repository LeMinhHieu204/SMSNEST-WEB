<?php
class Auth
{
    public static function check()
    {
        return !empty($_SESSION['user_id']);
    }

    public static function user()
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return (new User())->getById($_SESSION['user_id']);
    }

    public static function login($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $balanceModel = new Balance();
        if (!$balanceModel->getByUserId($user['id'])) {
            $balanceModel->createForUser($user['id']);
        }
    }

    public static function logout()
    {
        unset($_SESSION['user_id']);
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            self::redirect('/login');
        }
        if (!self::user()) {
            self::logout();
            self::redirect('/login');
        }
    }

    public static function requireAdmin()
    {
        $user = self::user();
        if (!$user || $user['role'] !== 'admin') {
            self::redirect('/dashboard');
        }
    }

    public static function redirectByRole($user = null)
    {
        if ($user === null) {
            $user = self::user();
        }
        if ($user && $user['role'] === 'admin') {
            self::redirect('/admin');
        }
        self::redirect('/dashboard');
    }

    public static function redirect($path)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = rtrim($config['app']['base_url'], '/');
        header('Location: ' . $baseUrl . $path);
        exit;
    }
}
