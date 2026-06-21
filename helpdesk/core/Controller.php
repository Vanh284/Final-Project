<?php
/**
 * Base Controller
 */
abstract class Controller
{
    /** Render a view file with data */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View not found: {$view}");
        }
        require $viewFile;
    }

    /** Send JSON response */
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Redirect to a URL */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /** Check that the current user is logged in */
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
            }
            $this->redirect(APP_URL . '/index.php?page=auth&action=login');
        }
    }

    /** Check role: 'admin', 'staff', or 'user' */
    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
            }
            $this->redirect(APP_URL . '/index.php?page=error&code=403');
        }
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /** Sanitize string input */
    protected function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /** Validate required POST fields; returns error string or empty string */
    protected function validateRequired(array $fields): string
    {
        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                return "Trường '{$field}' là bắt buộc.";
            }
        }
        return '';
    }
}
