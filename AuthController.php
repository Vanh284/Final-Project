<?php
class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/UserModel.php';
        $this->userModel = new UserModel();
    }

    public function login(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect(APP_URL . '/index.php?page=dashboard');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                $error = 'Vui lòng nhập email và mật khẩu.';
            } else {
                $user = $this->userModel->findByEmail($email);
                if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
                    $_SESSION['user'] = [
                        'id'        => $user['id'],
                        'full_name' => $user['full_name'],
                        'email'     => $user['email'],
                        'role'      => $user['role'],
                        'dept_id'   => $user['department_id'],
                    ];
                    $this->redirect(APP_URL . '/index.php?page=dashboard');
                } else {
                    $error = 'Email hoặc mật khẩu không đúng.';
                }
            }
        }

        $this->view('auth/login', ['error' => $error, 'pageTitle' => 'Đăng nhập']);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect(APP_URL . '/index.php?page=auth&action=login');
    }
}
