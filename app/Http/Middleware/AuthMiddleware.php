<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Auth\Services\AuthService;
use RuntimeException;

class AuthMiddleware
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * Enforces authentication and optional role restrictions.
     * Redirects to /login if unauthenticated, or throws/aborts if unauthorized.
     */
    public function requireRole(string ...$roles): array
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            header('Location: /login');
            exit;
        }

        if (!empty($roles) && !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../../../resources/views/errors/403.php';
            exit;
        }

        return $user;
    }
}