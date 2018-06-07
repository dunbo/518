<?php
//渠道通用配置管理
class ChannelconfigAction extends CommonAction{

	//平台通用配置管理
	Public function config_manage(){
		$model = D('Cooperate.Channeluser');
		$key_word = $_GET['key_word'];
		$key_type = $_GET['key_type'];
		$admin_id = 75;//李娜娜
		if($key_word && $key_type){
			if($key_type == 1){
				$where['_string'] = "username like '%{$key_word}%' and status != 0 && status != 2";
				$uid_arr = $model -> table('t_user') -> where($where) -> field('id') -> select();
				foreach($uid_arr as $key => $val){
					if($_SESSION['admin']['admin_id'] == $admin_id){
						$cid_arr = $model -> table('t_user_channel') -> where(array('uid' => $val['id'])) -> field('cid') -> select();
					}else{
						$cid_arr = $model -> table('t_user_channel') -> where(array('uid' => $val['id'],'create_uid' => $_SESSION['admin']['admin_id'])) -> field('cid') -> select();
					}
					$all_cid[] = $cid_arr;
				}
				foreach($all_cid as $key => $val){			
					if($val){
						foreach($val as $k => $v){
							$all_cids = array();
							$vals = array();
							$rv .= $v['cid'].',';
							$vals[] = $rv;
							$all_cids[] = $vals;
						}
					}	
				}
				$cid_need = substr($all_cids[0][0],0,strlen($all_cids[0][0])-1);
				$channel_where['_string'] = " cid in ({$cid_need})";
				$channel_result = $model -> table ('t_channel_config') -> where($channel_where) -> order("create_tm DESC") -> select();
			}elseif($key_type == 2){
				$admin_model =  new Model();
				$where['_string'] = "admin_user_name like '%{$key_word}%' and admin_state = 1";
				$create_id = $admin_model -> table('sj_admin_users') -> where($where) -> field('admin_user_id') -> select();
				foreach($create_id as $key => $val){
					$cid_arr = $model -> table('t_user_channel') -> where(array('create_uid' => $val['admin_user_id'],'status' => 1)) -> field('cid,create_uid') -> select();
					$all_cids[] = $cid_arr;
				}

				foreach($all_cids as $key => $val){
					foreach($val as $k => $v){
						if($_SESSION['admin']['admin_id'] == $admin_id){
								$all_cid[][] = $v['cid'];
						}else{
							if($v['create_uid'] == $_SESSION['admin']['admin_id']){
								$all_cid[][] = $v['cid'];
							}
						}
					}
				}
			
				foreach($all_cid as $key => $val){	
					$all_cidss .= $val[0].',';
				}
		
				$cid_need = substr($all_cidss,0,strlen($all_cidss)-1);
				$channel_where['_string'] = " cid in ({$cid_need})";
				$channel_result = $model -> table ('t_channel_config') -> where($channel_where) -> order("create_tm DESC") -> select();
				
			}elseif($key_type == 3){
				$admin_model =  new Model();
				$channel_where['_string'] = "chname like '%{$key_word}%'";
				$cid_arr = $admin_model -> table('sj_channel') -> where($channel_where) -> field('cid') -> select();
				if($_SESSION['admin']['admin_id'] == $admin_id){
					$create_cid = $model -> table('t_user_channel') -> select();
				}else{
					$create_cid = $model -> table('t_user_channel') -> where(array('create_uid' => $_SESSION['admin']['admin_id'])) -> select();
				}
				foreach($create_cid as $key => $val){
					$cid_dous .= $val['cid'].',';
				}
				$cid_dou = substr($cid_dous,0,strlen($cid_dous) - 1);
				foreach($cid_arr as $key => $val){
					$cid_needs .= $val['cid'].',';
				}
				$cid_need = substr($cid_needs,0,strlen($cid_needs) - 1);
				$config_where['_string'] = " cid in ({$cid_need}) and cid in ({$cid_dou})";
				$channel_result = $model -> table ('t_channel_config') -> where($config_where) -> order("create_tm DESC") -> select();
				
			}
		}else{
			if($_SESSION['admin']['admin_id'] == $admin_id){
				$create_cid = $model -> table('t_user_channel') -> select();
			}else{
				$create_cid = $model -> table('t_user_channel') -> where(array('create_uid' => $_SESSION['admin']['admin_id'])) -> select();
			}
			foreach($create_cid as $key => $val){
				$cid_dous .= $val['cid'].',';
			}
			$cid_dou = substr($cid_dous,0,strlen($cid_dous) - 1);
			$where['_string'] = "status = 1 and cid in ({$cid_dou})";
			$channel_result = $model -> table('t_channel_config') -> where($where) -> order("create_tm DESC") -> select();
		}
		foreach($channel_result as $key => $val){
			$user_result = $model -> table('t_user_channel') -> where(array('status' => 1,'cid' => $val['cid'])) -> select();
			if($user_result){
				$val['uid'] = $user_result[0]['uid'];
				$val['create_uid'] = $user_result[0]['create_uid'];
				$channel_results[$key] = $val;
			}
		}
		foreach($channel_results as $key => $val){
			$user_model = new Model();
			$chname = $user_model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> field('chname') -> select();
			$val['chname'] = $chname[0]['chname'];
			$channel_user = $model -> table('t_user') -> where(array('id' => $val['uid'])) -> field('username') -> select();
			$username = $user_model -> table('sj_admin_users') -> where(array('admin_user_id' => $val['create_uid'])) -> field('admin_user_name') -> select();
			$val['create_person'] = $username[0]['admin_user_name'];
			$val['username'] = $channel_user[0]['username'];
			$channel_results[$key] = $val;
		}
		$this -> assign('key_word',$key_word);
		$this -> assign('key_type',$key_type);
		$this -> assign('channel_results',$channel_results);
		$this -> display();
	}

	//编辑安智市场激活成分显示
	public function active_edit(){
		$cid = $_GET['cid'];
		$model = D('Cooperate.Channeluser');
		$channel = $model -> table('t_channel_config') -> where(array('cid' => $cid)) -> select();
		if($channel[0]['ad_switch'] == 0 && $channel[0]['game_switch'] == 0){
			$this -> assign('active_go',1);
		}
		$this -> assign('channel',$channel[0]);
		$this -> assign('cid',$cid);
		$this -> display();
	}
	
	//编辑安智市场激活成分提交
	public function active_edit_do(){
		$data['active_switch'] = trim($_GET['active_switch']) == 1 ? 1 : 0;
		$data['active_price'] = floatval(trim($_GET['active_price'])) > 0 ? floatval(trim($_GET['active_price'])) : 0 ;
		if($this -> check_point(floatval(trim($_GET['active_price'])),4,10)){
			$this -> error("安智市场激活单价格式错误");
		}
		$cid = $_GET['cid'];
		$where['cid'] = $cid;
		$data['update_tm'] = time();
		$model = D('Cooperate.Channeluser');
		$no = $model -> table('t_channel_config') -> where($where) -> select();
		if(!$no[0]['ad_switch'] && !$no[0]['game_switch']){
			if($data['active_switch'] == 0){
				$this -> error("请至少选择一项分成模式");
			}
		}
		$result = $model -> save_channel_config($where,$data);
		if($result){
			$this -> writelog("已修改id为{$cid}的渠道配置，激活开关为{$_GET['active_switch']},激活单价为{$_GET['active_price']}");
			$this -> success("编辑成功");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channelconfig/config_manage');
		}
		
	}
	
	//编辑广告分成显示
	public function ad_edit(){
		$cid = $_GET['cid'];
		$model = D('Cooperate.Channeluser');
		$channel = $model -> table('t_channel_config') -> where(array('cid' => $cid)) -> select();
		if($channel[0]['active_switch'] == 0 && $channel[0]['game_switch'] == 0){
			$this -> assign('ad_go',1);
		}
		$this -> assign('channel',$channel[0]);
		$this -> assign("cid",$cid);
		$this -> display();
	}

	//编辑广告分成显示
	public function ad_edit_do(){
		$cid = $_GET['cid'];
		$data['ad_switch'] = trim($_GET['ad_switch']) == 1 ? 1 : 0;
		$data['ad_price'] = floatval(trim($_GET['ad_price'])) > 0 ? floatval(trim($_GET['ad_price'])) : 0;
		if($this -> check_point(floatval(trim($_GET['ad_price'])),4,10)){
			$this -> error("单个软件下载单价格式错误");
		}
		$data['max_down'] = trim($_GET['max_down']) > 0 ? trim($_GET['max_down']) : 0;
		if($this -> check_point(trim($_GET['max_down']),0,100)){
			$this -> error("防刷量值格式错误");
		}
		$data['ad_cut_pre'] = floatval(trim($_GET['ad_cut_pre'])) > 0 ? floatval(trim($_GET['ad_cut_pre'])) : 0;
		$data['update_tm'] = time();
		$model = D('Cooperate.Channeluser');
		$where['cid'] = $cid;
		$no = $model -> table('t_channel_config') -> where($where) -> select();
		if(!$no[0]['active_switch'] && !$no[0]['game_switch']){
			if($data['ad_switch'] == 0){
				$this -> error("请至少选择一项分成模式");
			}
		}
		$result = $model -> save_channel_config($where,$data);
		if($result){
			if($data['ad_switch'] == 1){
				$switch = '开';
			}elseif($data['ad_switch'] == 0){
				$switch = '关';
			}
			$this -> writelog("已修改id为{$cid}的渠道的广告分成信息开关为{$switch},单个软件下载单价为{$data['ad_price']},防刷量值为{$data['max_down']},广告分成扣量比例为{$data['ad_cut_pre']}");
			$this -> success("编辑成功");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channelconfig/config_manage');
		}
	}

	//编辑游戏分成显示
	public function game_edit(){
		$cid = $_GET['cid'];
		$model = D('Cooperate.Channeluser');
		$channel = $model -> table('t_channel_config') -> where(array('cid' => $cid)) -> select();
		if($channel[0]['active_switch'] == 0 && $channel[0]['ad_switch'] == 0){
			$this -> assign('game_go',1);
		}
		$this -> assign('channel',$channel[0]);
		$this -> assign('cid',$cid);
		$this -> display();
	}

	//编辑游戏分成提交
	public function game_edit_do(){
		$cid = $_GET['cid'];
		$data['game_switch'] = trim($_GET['game_switch']) == 1 ? 1 : 0;
		$data['game_cut_pre'] = floatval(trim($_GET['game_cut_pre'])) > 0 ? floatval(trim($_GET['game_cut_pre'])) : 0;
		$data['update_tm'] = time();
		$model = D('Cooperate.Channeluser');
		$where['cid'] = $cid;
		$no = $model -> table('t_channel_config') -> where($where) -> select();
		if(!$no[0]['ad_switch'] && !$no[0]['active_switch']){
			if($data['game_switch'] == 0){
				$this -> error("请至少选择一项分成模式");
			}
		}
		$result = $model -> save_channel_config($where,$data);
		if($result){
			if($data['game_switch'] == 1){
				$switch = '开';
			}elseif($data['game_switch'] == 0){
				$switch = '关';
			}
			$this -> writelog("已修改id为{$cid}的渠道的游戏分成信息开关为{$switch},游戏分成扣量比例为{$data['game_cut_pre']}");
			$this -> success("编辑成功");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channelconfig/config_manage');
		}
		
	}
	//通用默认信息配置显示
	public function default_config(){
		$model = D('Cooperate.Channeluser');
		$result = $model -> table('t_init_config') -> where(array('status' => 1)) -> select();
		foreach($result as $key => $val){
			if($val['id'] == 1){
				$val['coefficient_value'] = $val['coefficient_value'].'(元)';
			}elseif($val['id'] == 2){
				$val['coefficient_value'] = $val['coefficient_value'].'(个)';
			}else{
				$val['coefficient_value'] = $val['coefficient_value'].'(%)';
			}
			$result[$key] = $val;
		}
		$this -> assign('result',$result);
		$this -> display();
	}
	
	//编辑通用默认配置信息显示
	public function config_edit(){
		$model = D('Cooperate.Channeluser');
		$id = $_GET['id'];
		$result = $model -> table('t_init_config') -> where(array('id' => $id)) -> select();
		$this -> assign('id',$id);
		
		if($id == 1){
			$result[0]['unit'] = '元';
		}elseif($id == 2){
			$result[0]['unit'] = '个';
		}else{
			$result[0]['unit'] = '%';
		}
		$this -> assign('result',$result[0]);
		$this -> display();
	}
	
	//通用默认信息配置提交
	public function submit_default_config(){
		$model = D('Cooperate.Channeluser');
		//$data['coefficient_name'] = trim($_GET['co_name']);
		if($_GET['id'] != 2){
			$data['coefficient_value'] = floatval(trim($_GET['co_value'])) > 0 ? $this -> keep_point(trim($_GET['co_value']),4) : 0;
		}else{
			$data['coefficient_value'] = trim($_GET['co_value']) > 0 ? trim($_GET['co_value']) : 0;
		}
		$data['remark'] = trim($_GET['remark']);
		$data['updatetime'] = time();
		$where['id'] = $_GET['id'];
		$where['status'] = 1;
		$result = $model -> save_code($where,$data);
		if($result){
			$this -> writelog("已修改id为{$_GET['id']}的默认配置系数值为{$data['coefficient_value']},备注为{$data['remark']}");
			$this -> success("编辑成功");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channelconfig/default_config');
		}
	}
	
	//检查是否保留指定小数点，范围是否正确
	protected function check_point($val,$point,$max){
		$str = explode(',',$val);
		$length = strlen($str[1]);
		if($length > $point || $val > $max){
			return true;
		}else{
			return false;
		}
	
	}


}