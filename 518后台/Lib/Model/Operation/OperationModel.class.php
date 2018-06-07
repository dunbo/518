<?php
class OperationModel extends AdvModel {

    protected $connect_id = 25;

    public function __construct(){
            parent::__construct();
			if(!$_GET['form']){
				$cj_Connect = C('DB_YW');
			}else{
				$cj_Connect = C('DB_YW_NEW');
			}
            $this -> addConnect($cj_Connect, $this->connect_id);
            $this -> switchConnect($this->connect_id);
    }

//获取数据
	function get_data($where,$params,$table){
		if($params['type'] ==1 || $params['type'] ==2){
			$total = $this->table($table)->where($where)->count();	
		}else{
			$total = $this->table($table)->where($where)->field("count(DISTINCT file_name) as total")->find();	
			$total = $total['total'];
		}
		import('@.ORG.Page2');
		//分页		
		$limit = isset($params['limit']) ? $params['limit'] : 500;	
		$params['file_name'] = base64_encode($params['file_name']);
		$param = http_build_query($params);
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','条记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		if($params['type'] ==1 || $params['type'] ==2){
			$list = $this->table($table)->where($where)->limit($Page->firstRow.','.$Page->listRows)->field('*')->select();
		}else{
			$list = $this->table($table)->where($where)->limit($Page->firstRow.','.$Page->listRows)->group('file_name')->field('*,count(*) as toal')->select();
		}
		return array($list,$total,$Page);
	}

}
