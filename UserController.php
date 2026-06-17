<?php
class UserController extends Controller
{
    private UserModel       $userModel;
    private DepartmentModel $deptModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../models/DepartmentModel.php';
        $this->userModel = new UserModel();
        $this->deptModel = new DepartmentModel();
    }

    public function index(): void
    {
        $this->requireRole('admin');
        $users = $this->userModel->allWithDepartment();
        $depts = $this->deptModel->all();
        $this->view('users/index', [
            'pageTitle' => 'Quản lý Người dùng',
            'users'     => $users,
            'depts'     => $depts,
        ]);
    }

    public function store(): void
    {
        $this->requireRole('admin');
        $name     = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        $deptId   = (int)($_POST['department_id'] ?? 0) ?: null;

        $errors = [];
        if (strlen($name) < 2)              $errors[] = 'Họ tên tối thiểu 2 ký tự.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
        if (strlen($password) < 6)          $errors[] = 'Mật khẩu tối thiểu 6 ký tự.';
        if (!in_array($role, ['admin','staff','user'])) $errors[] = 'Vai trò không hợp lệ.';

        if ($errors) { $this->json(['success' => false, 'errors' => $errors], 422); }

        if ($this->userModel->findByEmail($email)) {
            $this->json(['success' => false, 'errors' => ['Email đã tồn tại.']], 422);
        }

        $id = $this->userModel->create([
            'full_name'     => $name,
            'email'         => $email,
            'password'      => password_hash($password, PASSWORD_BCRYPT),
            'role'          => $role,
            'department_id' => $deptId,
            'is_active'     => 1,
        ]);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function update(): void
    {
        $this->requireRole('admin');
        $id     = (int)($_POST['id'] ?? 0);
        $name   = trim($_POST['full_name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $role   = $_POST['role'] ?? 'user';
        $deptId = (int)($_POST['department_id'] ?? 0) ?: null;
        $active = isset($_POST['is_active']) ? 1 : 0;

        $data = [
            'full_name'     => $name,
            'email'         => $email,
            'role'          => $role,
            'department_id' => $deptId,
            'is_active'     => $active,
        ];

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                $this->json(['success' => false, 'errors' => ['Mật khẩu tối thiểu 6 ký tự.']], 422);
            }
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);
        $this->json(['success' => true]);
    }

    public function destroy(): void
    {
        $this->requireRole('admin');
        $id = (int)($_POST['id'] ?? 0);
        if ($id === ($_SESSION['user']['id'] ?? 0)) {
            $this->json(['success' => false, 'message' => 'Không thể xoá tài khoản đang đăng nhập'], 403);
        }
        $this->userModel->delete($id);
        $this->json(['success' => true]);
    }
}
