<?php
class Comment_readModel extends AdvModel {

    protected $connect_id = 25;

    public function __construct(){
            parent::__construct();
            $cj_Connect = C('DB_COMMENT');

            $this -> addConnect($cj_Connect, $this->connect_id);
            $this -> switchConnect($this->connect_id);
    }
 
}
