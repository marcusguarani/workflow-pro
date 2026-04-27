<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('index.php');
    }
}

function attempt_login(string $email, string $password): bool
{
    if (!table_exists('users')) {
        return false;
    }

    $stmt = db()->prepare('SELECT u.*, d.name AS department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.email = ? AND u.is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $hash = $user['password_hash'] ?? '';
    if ($hash === '' || !password_verify($password, $hash)) {
        return false;
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'Administrador';
}
