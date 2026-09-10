<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Auth\Services\AuthService;

class AuthController
{
    public function __construct(private readonly AuthService $auth) {}

    public function showLogin(): void
    {
        $user = $this->auth->currentUser();
        // Allow user to view login screen if they explicitly specify a role or request a switch
        if ($user !== null && !isset($_GET['switch']) && !isset($_GET['role'])) {
            header('Location: ' . $this->auth->redirectPathForRole($user['role']));
            exit;
        }

        $auth = $this->auth;
        require __DIR__ . '/../../../resources/views/auth/login.php';
    }

    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $error = null;

        if (empty($email) || empty($password)) {
            $error = 'Please enter both your email address and password.';
            require __DIR__ . '/../../../resources/views/auth/login.php';
            return;
        }

        $user = $this->auth->attempt($email, $password);
        if (!$user) {
            $error = 'Incorrect email or password. Please try again.';
            require __DIR__ . '/../../../resources/views/auth/login.php';
            return;
        }

        $this->auth->login($user);
        $redirectUrl = $this->auth->redirectPathForRole($user['role']);
        header("Location: {$redirectUrl}");
        exit;
    }

    public function logout(): void
    {
        $this->auth->logout();
        header('Location: /login?logged_out=1');
        exit;
    }
}