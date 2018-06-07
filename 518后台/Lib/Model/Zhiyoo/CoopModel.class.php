<?php 
class CoopModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
    public function wstatus(){
        $res = $this->table('zy_coop_wstatus')->where('status=1')->select();
        $arr = array();
        foreach($res as $v){
            $arr[$v['wstatus']] = $v;
        }
        
        return $arr;
    }
    
    public function procate(){
        $res = $this->table('zy_coop_procate')->where('status=1')->select();
        $arr = array();
        foreach($res as $v){
            $arr[$v['procate']] = $v;
        }
        
        return $arr;
    }
    
}