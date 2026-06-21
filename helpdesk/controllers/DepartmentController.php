<?php
class DepartmentController extends Controller
{
    private DepartmentModel $deptModel;
    private UserModel       $userModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/DepartmentModel.php';
        require_once __DIR__ . '/../models/UserModel.php';
        $this->deptModel = new DepartmentModel();
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        $this->requireRole('admin');
        $departments = $this->deptModel->allWithManager();
        $staff       = $this->userModel->allStaff();
        $this->view('departments/index', [
            'pageTitle'   => 'Quản lý Bộ phận',
            'departments' => $departments,
            'staff'       => $staff,
        ]);
    }

    public function store(): void
    {
        $this->requireRole('admin');
        $name      = trim($_POST['name'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $managerId = (int)($_POST['manager_id'] ?? 0) ?: null;

        if (!$name) { $this->json(['success' => false, 'message' => 'Tên bộ phận không được để trống'], 422); }

        $id = $this->deptModel->create(['name' => $name, 'description' => $desc, 'manager_id' => $managerId]);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function update(): void
    {
        $this->requireRole('admin');
        $id        = (int)($_POST['id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $managerId = (int)($_POST['manager_id'] ?? 0) ?: null;
        $active    = isset($_POST['is_active']) ? 1 : 0;

        if (!$name) { $this->json(['success' => false, 'message' => 'Tên bộ phận không được để trống'], 422); }

        $this->deptModel->update($id, ['name' => $name, 'description' => $desc, 'manager_id' => $managerId, 'is_active' => $active]);
        $this->json(['success' => true]);
    }

    public function destroy(): void
    {
        $this->requireRole('admin');
        $id = (int)($_POST['id'] ?? 0);
        $this->deptModel->delete($id);
        $this->json(['success' => true]);
    }
}
