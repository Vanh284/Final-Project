<?php
/**
 * Simple front-controller router.
 * Routes:  ?page=tickets&action=index  =>  TicketsController->index()
 */
class Router
{
    private static array $map = [
        'auth'       => 'AuthController',
        'dashboard'  => 'DashboardController',
        'tickets'    => 'TicketController',
        'categories' => 'CategoryController',
        'departments'=> 'DepartmentController',
        'users'      => 'UserController',
        'comments'   => 'CommentController',
        'surveys'    => 'SurveyController',
        'reports'    => 'ReportController',
        'backend'    => 'BackendController',
    ];

    public static function dispatch(): void
    {
        $page   = preg_replace('/[^a-z_]/', '', strtolower($_GET['page'] ?? 'dashboard'));
        $action = preg_replace('/[^a-zA-Z_]/', '', $_GET['action'] ?? 'index');

        if (!isset(self::$map[$page])) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $controllerName = self::$map[$page];
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            die("Controller not found: {$controllerName}");
        }

        require_once $controllerFile;
        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $controller->$action();
    }
}
