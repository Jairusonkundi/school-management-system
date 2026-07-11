<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Lookup;
use App\Models\User;
use Throwable;

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $this->view('users/index', ['users' => (new User())->all(), 'lookups' => new Lookup(), 'roles' => Auth::roles()]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            if (!empty($_POST['id'])) {
                (new User())->update((int)$_POST['id'], $_POST);
                Auth::flash('success', 'User updated.');
            } else {
                (new User())->create($_POST);
                Auth::flash('success', 'User created.');
            }
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('users');
    }

    public function deactivate(): void
    {
        Auth::requireRole(['admin']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            if ((int)post('id') === (int)Auth::user()['id']) {
                throw new \InvalidArgumentException('You cannot deactivate your own account.');
            }
            (new User())->deactivate((int)post('id'));
            Auth::flash('success', 'User deactivated.');
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('users');
    }
}
