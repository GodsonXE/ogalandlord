<?php
declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Infrastructure\Database\Connection;
use PDO;

class AuthService
{
    public function __construct(private readonly Connection $db) {}

    public function attempt(string $email, string $password): ?array
    {
        $pdo = $this->db->getPdo();
        $cleanEmail = strtolower(trim($email));
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1");
        $stmt->execute(['email' => $cleanEmail]);
        $user = $stmt->fetch();

        // Support both @ogalandlord.ng and @propertycare.ng seamlessly
        if (!$user) {
            if (str_ends_with($cleanEmail, '@ogalandlord.ng')) {
                $altEmail = str_replace('@ogalandlord.ng', '@propertycare.ng', $cleanEmail);
                $stmt->execute(['email' => $altEmail]);
                $user = $stmt->fetch();
            } elseif (str_ends_with($cleanEmail, '@propertycare.ng')) {
                $altEmail = str_replace('@propertycare.ng', '@ogalandlord.ng', $cleanEmail);
                $stmt->execute(['email' => $altEmail]);
                $user = $stmt->fetch();
            }
        }

        if (!$user || empty($user['password_hash'])) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        unset($user['password_hash']);
        return $user;
    }

    public function login(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['auth_user'] = [
            'id'        => (int) $user['id'],
            'uuid'      => $user['uuid'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'phone'     => $user['phone_number'],
            'role'      => strtoupper($user['role']),
        ];
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public function currentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['auth_user'] ?? null;
    }

    public function redirectPathForRole(string $role): string
    {
        return match (strtoupper($role)) {
            'SUPERADMIN'          => '/admin',
            'CARETAKER', 'ADMIN' => '/caretaker',
            'LANDLORD'            => '/landlord',
            'TENANT'              => '/tenant',
            default               => '/',
        };
    }
}