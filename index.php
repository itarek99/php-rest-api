<?php

spl_autoload_register( function ( $class ) {
    require_once __DIR__ . '/src/' . $class . '.php';
});

set_error_handler( "ErrorHandler::handleError" );
set_exception_handler( "ErrorHandler::handleException" );

header( 'Content-Type: application/json; charset=utf-8' );

$parts = explode( '/', $_SERVER[ 'REQUEST_URI' ] );
if ($parts[1] !== 'tasks') {
    http_response_code( 404 );
    exit;
}

$id = $parts[2] ?? null;

$database = new Database( 'localhost', 'oop', 'root', 'root' );
$gateway = new TaskGateway( $database );

$controller = new TasksController( $gateway );
$controller->processRequest( $_SERVER[ 'REQUEST_METHOD' ], $id );