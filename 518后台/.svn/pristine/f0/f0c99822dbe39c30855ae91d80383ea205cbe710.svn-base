<?php

class BusinessModel extends AdvModel
{
	protected $dbName = 'settlement';
	protected $tableName = 'business';
	protected $tablePrefix = 'ad_';



    public function getBusList($search, $page, $size)
    {
        $where = array();
		if(!empty($search['bname'])) {
			$where['bname'] = array('like' , "%{$search['bname']}%");
		}
		if(!empty($search['type'])) {
			$where['type'] = $search['type'];
		}
		if(!empty($search['status'])) {
			$where['status'] = $search['status'];
		}
		return array('item' => $this->field(" * ")->where($where)->page("{$page},{$size}")->order("bid desc")->select(),'count' => $this->where($where)->count());
    }


	public function editById($bid,$data) {
		return $this->field(" * ")->where("bid=$bid")->save($data);
	}

	public function addItem($item) {
	
		return $this->add($item);
	}


	public function getItemById($bid) {
		$result = $this->field(" * ")->where(array('bid' => $bid))->select();
		return is_array($result) ? $result[0] : null;
	
	}


	public function getBusListByNC($n, $c) {
		$n = mysql_escape_string($n);
		$c = mysql_escape_string($c);
		if($n&&$c)
		{
			return $this->query("SELECT * FROM __TABLE__ WHERE  bname='$n' OR color='$c'");
		}
		elseif($n&&!$c)
		{
			return $this->query("SELECT * FROM __TABLE__ WHERE  bname='$n'");
		}
		elseif(!$n&&$c)
		{
			return $this->query("SELECT * FROM __TABLE__ WHERE  color='$c'");
		}
		//return $this->query("SELECT * FROM __TABLE__ WHERE  bname='$n'"); //颜色功能去掉
	}
}

