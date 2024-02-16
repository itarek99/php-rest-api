<?php
require_once 'vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

spl_autoload_register(function ($class) {
    require_once __DIR__ . '/src/' . $class . '.php';
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");
header('Content-Type: application/json; charset=utf-8');

$parts = explode('/', $_SERVER['REQUEST_URI']);
$database = new Database('localhost', 'oop', 'root', 'root');

if ($parts[1] === 'tasks') {
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