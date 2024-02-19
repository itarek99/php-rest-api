<?php



use TODO\Database;
use TODO\TaskGateway;
use TODO\TasksController;
use TODO\AuthController;

require_once 'vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

set_error_handler("TODO\\ErrorHandler::handleError");
set_exception_handler("TODO\\ErrorHandler::handleException");

header('Content-Type: application/json; charset=utf-8');

$uri = $_SERVER['REQUEST_URI'];
$database = new Database('localhost', 'oop', 'root', 'root');


if ($uri === '/tasks') {
    $task_id = explode('/', $uri)[2] ?? null;
    $taskGateway = new TaskGateway($database);
    $taskController = new TasksController($taskGateway, $_SERVER['REQUEST_METHOD'], $task_id);
} elseif ($uri === '/register' || $uri === '/login') {
    $userController = new AuthController($database, $_SERVER['REQUEST_METHOD'], $uri);
} else {
    http_response_code(404);
    echo json_encode(["code" => 404, 'message' => 'Page Not Found']);
    exit;
}
