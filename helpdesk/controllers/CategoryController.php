<?php
class CategoryController extends Controller
{
    private CategoryModel    $categoryModel;
    private DepartmentModel  $deptModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/CategoryModel.php';
        require_once __DIR__ . '/../models/DepartmentModel.php';
        $this->categoryModel = new CategoryModel();
        $this->deptModel     = new DepartmentModel();
    }

    public function index(): void
    {
        $this->requireRole('admin');
        $categories  = $this->categoryModel->allWithDepartment();
        $departments = $this->deptModel->all();
        $this->view('categories/index', [
            'pageTitle'   => 'Quản lý Danh mục',
            'categories'  => $categories,
            'departments' => $departments,
        ]);
    }

    public function store(): void
    {
        $this->requireRole('admin');
        $name      = trim($_POST['name'] ?? '');
        $deptId    = (int)($_POST['department_id'] ?? 0);
        $sla       = (int)($_POST['sla_hours'] ?? 24);
        $priority  = $_POST['priority_default'] ?? 'medium';
        $keywords  = trim($_POST['keywords'] ?? '');

        if (!$name || !$deptId) { $this->json(['success' => false, 'message' => 'Thiếu thông tin bắt buộc'], 422); }

        $id = $this->categoryModel->create([
            'name'             => $name,
            'department_id'    => $deptId,
            'sla_hours'        => $sla,
            'priority_default' => $priority,
            'keywords'         => $keywords,
        ]);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function update(): void
    {
        $this->requireRole('admin');
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $deptId   = (int)($_POST['department_id'] ?? 0);
        $sla      = (int)($_POST['sla_hours'] ?? 24);
        $priority = $_POST['priority_default'] ?? 'medium';
        $keywords = trim($_POST['keywords'] ?? '');
        $active   = isset($_POST['is_active']) ? 1 : 0;

        if (!$name || !$deptId) { $this->json(['success' => false, 'message' => 'Thiếu thông tin bắt buộc'], 422); }

        $this->categoryModel->update($id, [
            'name'             => $name,
            'department_id'    => $deptId,
            'sla_hours'        => $sla,
            'priority_default' => $priority,
            'keywords'         => $keywords,
            'is_active'        => $active,
        ]);
        $this->json(['success' => true]);
    }

    public function destroy(): void
    {
        $this->requireRole('admin');
        $id = (int)($_POST['id'] ?? 0);
        $this->categoryModel->delete($id);
        $this->json(['success' => true]);
    }
}
