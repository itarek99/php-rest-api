<?php

namespace TODO;

use Exception;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TasksController {
    public $user_id;
    public $task_id;
    private $method;
    private $gateway;

    public function __construct($gateway, $method, $task_id) {
        $this->gateway = $gateway;
        $this->method = $method;
        $this->task_id = $task_id;
        $this->processRequest();
    }

    public function processRequest() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if ($authHeader) {
            $token = explode(' ', $authHeader)[1];
            try {
                $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
                if (isset($decoded->id)) {
                    $this->user_id = $decoded->id;
                }
                if ($this->task_id) {
                    $this->processEntityRequest();
                } else {
                    $this->processCollectionRequest();
                }
            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(['message' => 'Invalid token', 'error' => $e->getMessage()]);
                exit;
            }
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Token not found']);
            exit;
        }
    }

    private function processEntityRequest() {
        if ($this->method === 'GET') {
            $this->getById();
        } else if ($this->method === 'DELETE') {
            $this->deleteTask();
        } else if ($this->method === 'PUT') {
            $this->update();
        } else {
            http_response_code(405);
        }
    }

    private function getById() {
        echo json_encode($this->gateway->getById($this->task_id));
    }

    private function update() {
        $data = json_decode(file_get_contents('php://input'), true);
        $updated = $this->gateway->update($this->task_id, $data);

        if ($updated) {
            http_response_code(200);
            echo json_encode($this->gateway->getById($this->task_id));
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'something went wrong']);
        }
    }

    private function deleteTask() {
        $deleted = $this->gateway->deleteTask($this->task_id);
        if ($deleted) {
            http_response_code(202);
            echo json_encode(['message' => 'task deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'something went wrong']);
        }
    }

    private function processCollectionRequest() {
        if ($this->method === 'GET') {
            $this->getAll($this->user_id);
        } else if ($this->method === 'POST') {
            $this->create($this->user_id);
        } else {
            http_response_code(405);
        }
    }

    private function getAll() {
        echo json_encode($this->gateway->getAll($this->user_id));
    }

    private function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $this->gateway->create($data, $this->user_id);
        http_response_code(201);
        echo json_encode($this->gateway->getById($id));
    }
}
