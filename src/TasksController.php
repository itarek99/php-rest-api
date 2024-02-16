<?php

class TasksController {

    public function __construct( private TaskGateway $gateway ) {}

    public function processRequest( $method, $id ) {
      if( $id ) {
        $this->processEntityRequest( $method, $id );
      } else {
        $this->processCollectionRequest( $method );
      }
    }

    private function processEntityRequest( $method, $id ) {
        if( $method === 'GET' ) {
            $this->getById( $id );
        } 
        else if( $method === 'DELETE' ) {
            $this->deleteTask( $id );
        }
        else if( $method === 'PUT' ) {
            $this->update( $id );
        }
        else {
            http_response_code( 405 );
        }
    }

    private function getById( $id ) {
        echo json_encode( $this->gateway->getById( $id ) );
    }

    private function update( $id ) {
        $data = json_decode( file_get_contents( 'php://input' ), true );
        $updated = $this->gateway->update( $id, $data );

        if( $updated ) {
            http_response_code( 200 );
            echo json_encode( $this->gateway->getById( $id ) );
        } else {
            http_response_code( 404 );
            echo json_encode( [ 'message' => 'something went wrong' ]);
        }
    }

    private function deleteTask( $id ) {
        $deleted = $this->gateway->deleteTask( $id );
        if( $deleted ) {
            http_response_code( 202 );
            echo json_encode( [ 'message' => 'task deleted successfully' ]);
        } else {
            http_response_code( 404 );
            echo json_encode( [ 'message' => 'something went wrong' ]);
        }
    }

  

    private function processCollectionRequest( $method ) {
        if( $method === 'GET' ) {
            $this->getAll();
        } else if( $method === 'POST' ) {
            $this->create();
        } else {
            http_response_code( 405 );
        }
    }

    private function getAll() {
        echo json_encode( $this->gateway->getAll() );
    }

    private function create() {
        $data = json_decode( file_get_contents( 'php://input' ), true );
        $id = $this->gateway->create( $data );
        http_response_code( 201 );
        echo json_encode( $this->gateway->getById( $id ) );
    }
}
