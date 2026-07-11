<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void { $this->view('auth/login'); }
    public function login(): void
    {
        Auth::verifyCsrf(post('csrf_token'));
        $email = filter_var(post('email'), FILTER_VALIDATE_EMAIL);
        $password = (string)post('password');
        $user = $email ? (new User())->findByEmail($email) : null;
        if ($user && password_verify($password, $user['password_hash'])) {
            Auth::login($user);
            $this->redirect('dashboard');
        }
        $this->view('auth/login', ['error' => 'Invalid email or password']);
    }
    public function logout(): void
    {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard');
        }
        Auth::verifyCsrf(post('csrf_token'));
        Auth::logout();
        $this->redirect('login');
    }
}
