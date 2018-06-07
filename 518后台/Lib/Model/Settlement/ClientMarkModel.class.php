<?php

class ClientMarkModel extends AdvModel
{
	protected $dbName = 'settlement';
	protected $tableName = 'client_mark';
	protected $tablePrefix = 'ad_';

	private $logTable = 'ad_mark_change_log';



public function getList($search, $page, $size)
{

	$where = array('status' => 0);


	foreach(array('cmark','softname','cooperation') as $v) {
		if(empty($search[$v])) {
			continue;
		}
		$where[$v] = array('like' , "%".$search[$v]."%");
	}

	if(!empty($search['bid'])) {
		$where['bid'] = $search['bid'];
	}
	if(!empty($search['package'])) {
		$where['package'] = $search['package'];
	}
	
	if(!empty($search['stype'])) {
		$where['stype'] = $search['stype'];
	}
	/*
	return array('item' => $this->field(" * ")->where($where)->page("{$page},{$size}")->order("bid desc")->select(),'count' => $this->where($where)->count());

	*/
	$order = "mid DESC";
	if(!empty($search['order'])) {
		$order = $search['order'];
	}


	return array('item' => $this->field(" * ")->where($where)->page("{$page},{$size}")->order($order)->select(),'count' => $this->where($where)->count());
}


	public function editById($mid,$data) {
		
		$result = $this->field(" * ")->where(array('mid' => $mid))->select();
		
		if(!empty($result) && isset($data['bid']) && $result[0]['bid'] != $data['bid']) {
			$this->addLog(array('mid' => $mid,'bid' => $data['bid']));
		}

		$result = $this->field(" * ")->where("mid=$mid")->save($data);
		
		//var_dump($result);
		return $result;
	}

	public function addItem($item) {
		
		$log = array('mid');
		$iid = $this->add($item);
		$this->addLog(array('mid' => $iid,'bid' => $item['bid']));
		
		return $iid;
	}



	public function getItemByMark($mark) {
		$result = $this->field(" * ")->where(array('cmark' => $mark))->select();
		return is_array($result) ? $result[0] : null;
	
	}


	public function getLogsByMid($mid) {
		$result = $this->table($this->dbName.'.'.$this->logTable)->where(array('mid' => $mid))->order("lid desc")->select();
		//var_dump($result);
		return $result;
	}
	
	
	private function addLog($log) {
		$log['posttime'] = time();
		$result = $this->table($this->dbName.'.'.$this->logTable)->add($log);
	}



}

