<?php

use Firebase\JWT\JWT;

class AuthController {
  private $conn;
  public function __construct( $database ) {
    $this->conn = $database->getConnection();
  }

  public function processRequest( $method, $action ) {
    if( $method === 'POST' ) {
      if( $action === 'register' ) {
        $this->register();
      } else if( $action === 'login' ) {
        $this->login();
      } else {
        http_response_code( 404 );
      }
    } else {
      http_response_code( 405 );
    }
  }

  private function getUserById( $id ) {
    $sql = "SELECT id, name, email FROM users WHERE id = :id";
    $stmt = $this->conn->prepare( $sql );
    $stmt->execute( [ 'id' => $id ] );
    return $stmt->fetch( PDO::FETCH_ASSOC );
  }

  private function register() {
    $data = json_decode(file_get_contents('php://input'), true);
    $this->validateRegisterData($data);

    $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(':name', $data['name']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT));
    
    try {
        $stmt->execute();
        $data = $this->getUserById($this->conn->lastInsertId());

        // Generate JWT token
        $jwtPayload = [
            'id' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email']
        ];
        $jwt = JWT::encode($jwtPayload, $_ENV['JWT_SECRET'], 'HS256');

        // Set JWT token in response
        $responseData = [
            'data' => $jwtPayload,
            'token' => $jwt
        ];
        echo json_encode($responseData);
        
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { 
            http_response_code(400); 
            echo json_encode(['message' => 'Email is already in use']);
        } else {
            
            http_response_code(500); 
            echo json_encode(['message' => 'Error occurred while registering user']);
        }
    }
}

  private function login () {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $sql = "SELECT id, name, email, password FROM users WHERE email = :email";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['email' => $data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
        exit;
    }
    
    // Generate JWT token
    $jwtPayload = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email']
    ];
    $jwt = JWT::encode($jwtPayload, $_ENV['JWT_SECRET'], 'HS256');

    // Set JWT token in response
    $responseData = [
        'data' => $jwtPayload,
        'token' => $jwt
    ];
    echo json_encode($responseData);
}


  private function validateRegisterData( $data ) {
    if( !isset( $data['name'] ) || !isset( $data['email'] ) || !isset( $data['password'] ) ) {
      http_response_code( 400 );
      echo json_encode( [ 'error' => 'Missing required fields' ] );
      exit;
    }
  }
}