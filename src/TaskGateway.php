<?php

namespace TODO;

use PDO;

class TaskGateway {
  private $conn;

  public function __construct($database) {
    $this->conn = $database->getConnection();
  }

  public function getAll($userId) {
    $sql = 'SELECT * FROM tasks WHERE user_id = :user_id';
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getById($id) {
    $sql = 'SELECT * FROM tasks WHERE id = :id';
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function deleteTask($id) {
    $sql = 'DELETE FROM tasks WHERE id = :id';
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
      return false;
    }
    return true;
  }

  public function create($data, $userId) {
    $errors = $this->getValidationErrors($data);
    if (!empty($errors)) {
      http_response_code(422);
      echo json_encode(['errors' => $errors]);
      exit;
    }

    $sql = 'INSERT INTO tasks ( description, user_id ) VALUES ( :description, :user_id )';
    $data['user_id'] = $userId;
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($data);
    return $this->conn->lastInsertId();
  }

  public function update($id, $data) {
    $errors = $this->getValidationErrors($data);
    if (!empty($errors)) {
      http_response_code(422);
      echo json_encode(['errors' => $errors]);
      exit;
    }

    $sql = 'UPDATE tasks SET description = :description WHERE id = :id';
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id, 'description' => $data['description']]);

    if ($stmt->rowCount() === 0) {
      return false;
    }
    return $id;
  }

  private function getValidationErrors($data) {
    $errors = [];
    if (empty($data['description'])) {
      $errors[] = 'Description is required';
    }
    return $errors;
  }
}
