<?php
use TODO\Database;
use TODO\TaskGateway;
use TODO\TasksController;
use TODO\AuthController;
use TODO\ErrorHandler;

require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

set_error_handler("TODO\\ErrorHandler::handleError");
set_exception_handler("TODO\\ErrorHandler::handleException");

header('Content-Type: application/json; charset=utf-8');

$parts = explode('/', $_SERVER['REQUEST_URI']);
$database = new Database('localhost', 'oop', 'root', 'root');

try {
    if ( isset($parts[1]) && $parts[1] === 'tasks') {
    $id = $parts[2]??null;
    $taskGateway = new TaskGateway($database);
    $taskController = new TasksController($taskGateway);
    $taskController->processRequest($_SERVER['REQUEST_METHOD'], $id);
} elseif ($parts[1] === 'register' || $parts[1] === 'login') {
    $userController = new AuthController( $database );
    $userController->processRequest($_SERVER['REQUEST_METHOD'], $parts[1]);
} else {
    http_response_code(404);
    exit;
}
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}