<?php
header("Content-type: text/html; charset=utf-8"); 
class AdCustomer_pModel extends AdvModel {
	protected $connect_id = 1811;
	protected $tablePrefix = 'co_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_CO_CHANNEL_P');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	function affairs(){
		//$sql = 'SELECT co.id coid,ch.id chid,co.group_name,co.status,ch.status,ch.charge_name,ch.group_id,ch.create_tm FROM co_group co LEFT JOIN co_charge ch ON ch.group_id = co.id WHERE co.charge_section = 1 and co.status = 1 and ch.status = 1 ORDER BY create_tm DESC';
		//$result = $this->query($sql);
		//print_r($result);
		$ret = $this->table('co_group')->where(array('status' => 1))->select();
		
		foreach($ret as $key => $val){
			$charge_result = $this -> table('co_charge') -> where(array('group_id' => $val['id'],'status' => 1)) ->ORDER('create_tm desc')-> select();
			$val['charge_result'] = $charge_result;
			if($charge_result){
			foreach($val['charge_result'] as $k => $v){
			//$count = $this->table('co_client_list')->where(array('charge_id' =>$v['id'],'status' => 1))->count();
					$count_where['_string'] = "charge_id = {$v['id']} and status != 0";
					$count = $this->table('co_client_list')->where($count_where)->count();
					$val['charge_result'][$k]['count'] = $count;
				}
			}
			$ret[$key] = $val;
		}
		//print_r($ret);
	
		return $ret;
	}

	function affairs_Terminal(){
		$sqltwo = 'SELECT co.id coid,ch.id chid,co.group_name,co.status,ch.status,ch.charge_name,ch.group_id,ch.create_tm FROM co_group co LEFT JOIN co_charge ch ON ch.group_id = co.id WHERE co.charge_section = 2 and co.status = 1 and ch.status = 1 ORDER BY create_tm DESC';
		$res = $this->query($sqltwo);
		
		$arrs = array();
		foreach ($res as $key => &$val) {
				$p_count = $this->table('co_client_list')->where('charge_id = ' . $val['chid'])->count();
				$val['p_count'] = $p_count;
				$arrs[$val['group_name']]['coid'] = $val['coid'];
				$arrs[$val['group_name']][$key]['val'] = $val;
		}
		return $arrs;
	}  
	public function delete_xiaozudel($id) {
        $where = array();
        $where['id'] = array('EQ', $id);
        $data = array();
        $data['status'] = 0;
        $ret = $this->table('co_charge')->where($where)->save($data);
        if ($ret) {
            return $ret;
        }
	}
	public function showtransfer($id) {
		$sql = 'SELECT co.id,cl.id,cl.client_name,cl.charge_id,cl.create_tm,cl.status FROM co_charge co INNER JOIN co_client_list cl on co.id = cl.charge_id where cl.`status` != 0 and co.`status` = 1 and co.id ='.$id;
		$result = $this->query($sql);
		return $result;
	}
	function showhead(){
		$ret = $this->table('co_charge')->where(array('status' => 1))->select();
		return $ret;
	}
	public function sel_showtransfer($id,$customer_name) {
		$sql = "SELECT co.id,cl.id,cl.client_name,cl.charge_id,cl.create_tm,cc.login_name,cl.status FROM co_charge co INNER JOIN co_client_list cl on co.id = cl.charge_id inner join co_account cc on cc.client_id = cl.id where cl.`status` != 0 and co.`status` = 1 and co.id =$id and cl.client_name like '%".$customer_name."%'";
		$result = $this->query($sql);
		return $result;
	}
	function transfersdo($check,$fuzeren){
		$arr = "(". implode(",",$check).")";
		$sql = "update co_client_list set charge_id =$fuzeren where id in $arr";
		$result = $this->query($sql);
		return $arr;
	}
}