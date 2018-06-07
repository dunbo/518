<?php
class GameSubModel extends AdvModel {
    protected $connect_id = 18;
    public function __construct()
    {
        $myConnect1 = C('DB_ACTIVITY');
        $this -> addConnect($myConnect1, $this->connect_id);
        $this -> switchConnect($this->connect_id);
    }
}