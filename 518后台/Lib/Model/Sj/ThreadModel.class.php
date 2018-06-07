<?php
class ThreadModel extends Model {
	protected $trueTableName = 'sj_thread';
	/**
	 * 检查是否有该主题,有的返回条数
	 * Enter description here ...
	 * @param $rid int id
	 * @param $type string 1=feedback 2=comment
	 */
	public function checkHaveThread($rid,$type){
		$type = $this->reType($type);
		if($type=='') return false;
		
		$sql = "select count(*) as c from sj_soft where rid=$rid and `type`=$type and admin_status=1";
		
		$re = $this->query($sql);
		
		return $re?$re[0]['c']:false;
	}
	
	public function checkHaveThreadlist($rid,$type){
		
		$sql = "select * from sj_thread where `rid` = $rid and `type` = $type and `admin_status` = 1";
		
		$re = $this->query($sql);
		
		return $re?true:false;
	}
	
	/**
	 * 
	 * 添加数据
	 * @param int $rid
	 * @param string $type
	 * @param array $data
	 */
	public function addThread($rid,$type,$data){
		$type = $this->reType($type);
		if($type=='') return false;
		
		$datetime = time();
		
		$sql = "
			INSERT INTO sj_thread 
			(rid,type,imsi,mac,userid,username,imei,did,vcode,cid,ip,dateline,last_refresh,admin_status,re_status,thread) 
			VALUES 
			($rid,$type,'{$data['imsi']}','{$data['mac']}','{$data['userid']}','{$data['username']}','{$data['imei']}','{$data['did']}','{$data['vcode']}','{$data['cid']}','{$data['ip']}',$datetime,$datetime,1,1,'{$data['thread']}')
		";
		
		$re = $this->execute($sql);
		
		return $re?true:false;
	}
	
	/**
	 * 
	 * 更新表数据
	 * @param array $data
	 * @param string $where
	 */
	public function updateThread($data,$where){
		if($where=='') return false;
		
		$datetime = time();
		
		$field = '';
		foreach($data as $k=>$v){
			$field .= "`$k`='{$v}',"; 
		}
		$field = substr($field,0,-1);
		
		$sql = "
			UPDATE sj_thread SET 
			$field
			WHERE $where
		";
			
		$re = $this->execute($sql);
		
		return $re?true:false;
	}
	
	private function reType($type){
		if($type=='feedback'){
			$type=1;
		}elseif($type=='comment'){
			$type=2;
		}else{
			return "";
		}
		return $type;
	}
}
?>
