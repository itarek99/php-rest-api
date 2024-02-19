<?php
namespace TODO;
use PDO;

class Database {
  function __construct(private $host, private $name, private $user, private $password) {
   // automatically create this properties
  }

  function getConnection() {
    $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
    return new PDO($dsn, $this->user, $this->password);
  }
}