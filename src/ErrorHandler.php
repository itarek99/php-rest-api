<?php

namespace TODO;

use ErrorException;
use Throwable;

class ErrorHandler {
  public static function handleException(Throwable $exception): void {

    $error_message = "[" . $exception->getCode() . "] " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log($error_message, 3, 'debug.log');

    echo json_encode([
      'code' => $exception->getCode(),
      'message' => 'Something Went Wrong'
    ]);
  }

  public static function handleError($errno, $errstr, $errfile, $errline): void {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
  }
}
