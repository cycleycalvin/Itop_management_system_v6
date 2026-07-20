<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\User;

final class AuthController
{
    public function loginForm(): void
    {
        View::render('auth/login');
    }

    public function login(): void
    {
        Security::verifyCsrf();
        $userModel = new User();
        $email = Security::cleanString($_POST['email'] ?? '');
        $user = $userModel->findByEmail($email);
        if (!$user || $user['status'] !== 'active' || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
            $userModel->logLogin($user['id'] ?? null, $email, 'failed');
            View::render('auth/login', ['error' => 'Invalid credentials or inactive account.']);
            return;
        }

        Auth::login($user);
        $userModel->touchLastLogin((int) $user['id']);
        $userModel->logLogin((int) $user['id'], $email, 'success');
        Activity::log('Logged in', (int) $user['id']);
        header('Location: index.php?page=dashboard');
    }

    public function registerForm(): void
    {
        View::render('auth/register');
    }

    public function register(): void
    {
        Security::verifyCsrf();
        $name = Security::cleanString($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');

        if (!$name || !$email || strlen($password) < 8) {
            View::render('auth/register', ['error' => 'Please provide a valid name, email, and password of at least 8 characters.']);
            return;
        }

        (new User())->create([
            'role_slug' => 'trainee',
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => Security::cleanString($_POST['phone'] ?? ''),
            'status' => 'pending',
        ]);
        View::render('auth/login', ['success' => 'Account registered. An administrator must approve it before login.']);
    }

    public function logout(): void
    {
        Activity::log('Logged out');
        Auth::logout();
        header('Location: index.php');
    }
}
