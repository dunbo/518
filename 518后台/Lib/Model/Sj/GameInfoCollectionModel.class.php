<?php
class GameInfoCollectionModel extends AdvModel {

    protected $connect_id = 24;

    public function __construct(){
            parent::__construct();
            $cj_Connect = C('DB_CAIJI');

            $this -> addConnect($cj_Connect, $this->connect_id);
            $this -> switchConnect($this->connect_id);
    }
    
}