<?php

class TasksController {
    public function processRequest( $method, $id ) {
      if( $id ) {
        $this->processEntityRequest( $method, $id );
      } else {
        $this->processCollectionRequest( $method );
      }
    }

    private function processEntityRequest( $method, $id ) {

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
       $sampleData = [
           ['id' => 1, 'title' => 'First task'],
           ['id' => 2, 'title' => 'Second task'],
           ['id' => 3, 'title' => 'Third task'],
        ];

        echo json_encode( $sampleData );
    }

    private function create() {
        $data = json_decode( file_get_contents( 'php://input' ), true );
        $task = new Task( $data );
        $task->save();
        echo json_encode( $task );
    }
}
