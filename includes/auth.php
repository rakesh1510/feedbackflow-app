<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

class Auth {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('ff_session');
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function login(string $email, string $password): bool {
        $user = DB::fetch("SELECT * FROM ff_users WHERE email = ? AND is_active = 1", [$email]);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        self::setSession($user);
        DB::query("UPDATE ff_users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        return true;
    }

    public static function register(string $name, string $email, string $password): int|false {
        if (DB::fetch("SELECT id FROM ff_users WHERE email = ?", [$email])) {
            return false; // Email already exists
        }
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userId = DB::insert('ff_users', [
            'name'     => $name,
            'email'    => $email,
            'password' => $hash,
            'role'     => 'owner',
        ]);
        $user = DB::fetch("SELECT * FROM ff_users WHERE id = ?", [$userId]);
        self::setSession($user);
        return $userId;
    }

    public static function logout(): void {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    public static function user(): ?array {
        self::start();
        if (!empty($_SESSION['user_id'])) {
            return DB::fetch("SELECT * FROM ff_users WHERE id = ? AND is_active = 1", [$_SESSION['user_id']]);
        }
        return null;
    }

    public static function check(): bool {
        return self::user() !== null;
    }

    public static function require(): array {
        $user = self::user();
        if (!$user) {
            header('Location: ' . APP_URL . '/index.php?page=login');
            exit;
        }
        return $user;
    }

    public static function isAdmin(array $user): bool {
        return in_array($user['role'], ['owner', 'admin']);
    }

    public static function canManageProject(array $user, int $projectId): bool {
        if (in_array($user['role'], ['owner', 'admin'])) return true;
        $member = DB::fetch(
            "SELECT role FROM ff_project_members WHERE project_id = ? AND user_id = ?",
            [$projectId, $user['id']]
        );
        return $member && in_array($member['role'], ['admin', 'manager']);
    }

    private static function setSession(array $user): void {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
    }
}
