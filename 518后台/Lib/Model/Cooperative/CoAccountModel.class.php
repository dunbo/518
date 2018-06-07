<?php
class CoAccountModel extends AdvModel{
	protected $connect_id = 18;
	protected $tablePrefix = 't_';
	
	
	public function __construct(){
		parent::__construct();
	    $co_Connect = C('DB_COOPERATIVE');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	// 查询所有负责人
	function allCharge(){
		$this->trueTableName = 't_charge';
		$this->flush();
		return $this->where('status=1')->field('id,charge_name')->select();
	}
	// 添加账号
	function addUser($data){
		$this->trueTableName = 't_user';
		$this->flush();
		return $this->add($data);
	}
	function delUser($id){
		$this->trueTableName = 't_user';
		$this->flush();
		return $this->where("uid='$id'")->delete();
	}
	// 添加账号详细信息
	function addAccount($data){
		$this->trueTableName = 't_account';
		$this->flush();
		return $this->add($data);
	}

	// 渠道和用户、负责人关联
	function allChannel(){
		$this->trueTableName = 't_user_channel';
		$this->flush();
		$channel = $this->join('t_user ON t_user.uid=t_user_channel.uid')->field('t_user_channel.id,t_user_channel.uid,t_user_channel.cid,t_user_channel.status,t_user.user_name,t_user.charge_person,t_user_channel.create_time')->select();
		foreach ($channel as $k=>$v){
			if ($v['charge_person']){
				$charge[]=$v['charge_person'];
			}
			switch ($v['status']){
				case 1:
					$channel[$k]['stat'] = '正常';
					break;
				case 0:
					$channel[$k]['stat'] = '未添加';
					break;
				case 2:
					$channel[$k]['stat'] = '未启用';
					break;
				case 3:
					$channel[$k]['stat'] = '暂停';
					break;
				case 4:
					$channel[$k]['stat'] = '账号暂停';
					break;
				default:
					break;
			}
			$channel[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
		}
		$charge = implode(',', $charge);
		
		$this->trueTableName = 't_charge';
		$this->flush();
		$charge = $this->where("id in ($charge)")->field('id,charge_name')->select();
		foreach ($channel as $k=>$v){
			foreach ($charge as $val){
				if ($val['id']==$v['charge_person']){
					$channel[$k]['charge_name'] = $val['charge_name'];
				}
			}
		}
		return $channel;
	}
	// 添加渠道
	function addChannel($data){
		$this->trueTableName = 't_user_channel';
		$this->flush();
		$affectid = $this -> add($data);
		return $affectid;
	}
	
	
	// 编辑渠道
	function editChannel($where,$data){
		$this->trueTableName = 't_user_channel';
		$this->flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	// 添加用户渠道配置
	function addChannel_config($data){
		$this->trueTableName = 't_channel_config';
		$this->flush();
		$affectid = $this->add($data);
		return $affectid;
	}
	
	// 编辑用户渠道配置
	function editChannel_config($where,$data){
		$this->trueTableName = 't_channel_config';
		$this->flush();
		
		return $this->where($where)->save($data);
	}
	// 账号信息
	function accountInfo($uid){
		$this->trueTableName = 't_user';
		$this->flush();
		
		return $this->join('t_account ON t_account.uid=t_user.uid')->where("t_user.uid=$uid")->find();
	}
	
	//修改账号状态
	function stopAccount($where,$data){
		$this->trueTableName = 't_user';
		$this->flush();
		$affectid = $this -> where($where) -> save($data);
		return $affectid;
	}
	
	// 编辑账号详细信息
	function editAccount($where,$data){
		$this->trueTableName = 't_user';
		$this->flush();
		$affectid = $this-> where($where) -> save($data);
		return $affectid;
	}

	
	// 编辑结算配置
	function editSettlement($where,$data){
		$this->trueTableName = 't_account';
		$this->flush();
		$affectid = $this-> where($where) -> save($data);
		return $affectid;
	}
}