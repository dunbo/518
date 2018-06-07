<?php
/**
 * 安智网产品管理平台 后台配置管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2011.5.20
 * ----------------------------------------------------------------------------
*/
class ConfigAction extends CommonAction {
    private $typeid;        //类型id
    private $uid;           //用户id
    private $map;           //存储条件
    private $users_db;      //用户表
    private $lists;         //常用列表
    private $notallowword;  //敏感词
    private $user_log_db;   //用户日志表
    private $user_log_list; //用户日志列表
    private $conf_db;        //配置表
    private $conf_list;      //配置列表
    private $templists;       //temp表中里列表
    private $pid;            //临时ID
	//_________________________________________________________________________________________________
	//短信系统报警配置管理
	function sms_alarm() {
		$this->conf_db = D('Sj.Config');
		$res = $this->conf_db->where("config_type='sms_alarm' AND configname IN ('sms_alarm_counter','sms_alarm_cont','sms_alarm_balance','sms_balance_cont','sms_stop_counter','sms_alarm_rate') AND status=1")->select();
		$_res = array();
		if($res) {
			foreach($res as $val) {
				$_res[$val['configname']] = $val['configcontent'];
			}
		}
		//频率
		$redis = new Redis();
		$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
		$rate = $redis->get('sms_alarm_rate');

		$this->assign('_res', $_res);
		//$this->assign('list', $list);
		$this->assign('rate', $rate);
		$this->display();
	}
	function sms_alarm_one(){
				//监测人员列表
		$person = M('sms_alarm_person');
		$list = $person->order("status desc")->select();
		if($list) {
			foreach($list as $key=>$val) {
				$list[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
				if($val['scene']==3) {
					$list[$key]['scene_str'] = '短信超量报警/短信余额报警';
				} else if($val['scene']==1) {
					$list[$key]['scene_str'] = '短信超量报警';
				} else if($val['scene']==2) {
					$list[$key]['scene_str'] = '短信余额报警';
				}
				if($val['type']==3) {
					$list[$key]['type_str'] = '邮件/手机';
				} else if($val['type']==1) {
					$list[$key]['type_str'] = '邮件';
				} else if($val['type']==2) {
					$list[$key]['type_str'] = '手机';
				}
				if($val['status']==1) {
					$list[$key]['do'] = "<a href='/index.php/Dev/Config/sms_alarm_person/id/{$val['id']}'>【编辑】</a>&nbsp;|&nbsp;<a href='javascript:void(0);' onclick=\"do_status('/index.php/Dev/Config/sms_alarm_person_status/id/{$val['id']}/status/0')\">【停用】</a>";
				} else {
					$list[$key]['do'] = "<a href='/index.php/Dev/Config/sms_alarm_person/id/{$val['id']}'>【编辑】</a>&nbsp;|&nbsp;<a href='javascript:void(0);' onclick=\"do_status('/index.php/Dev/Config/sms_alarm_person_status/id/{$val['id']}/status/1')\">【启用】</a>";
				}
			}
		}
		//频率
		$redis = new Redis();
		$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
		$rate = $redis->get('sms_alarm_rate');

		$this->assign('list', $list);
		$this->assign('rate', $rate);
		$this->display();
	}
	//短信系统报警配置管理_报警内容编辑
	function sms_alarm_edit() {
		$this->gpcFilter();

		$log_str = '';

		$word_list = array(
			'sms_alarm_counter' => '短信超量数量',
			'sms_alarm_cont' => '短信超量报警内容',
			'sms_alarm_balance' => '系统报警余额',
			'sms_balance_cont' => '余额不足时报警内容',
			'sms_stop_counter' => '停止短信系统的数量',
			'sms_alarm_rate' => '报警发送频率',
		);

		$this->conf_db = D('Sj.Config');
		if (!empty($_POST)) {
			$redis = new Redis();
			$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
			foreach(array('sms_alarm_counter','sms_alarm_cont','sms_alarm_balance','sms_balance_cont','sms_stop_counter','sms_alarm_rate') as $val) {
				$res = $this->conf_db->where("config_type='sms_alarm' AND configname='{$val}' AND status=1")->find();
				if($res) {
					$map = array();
					$map['configcontent'] = trim($_POST[$val]);
					$map['uptime'] = time();
					$this->conf_db->where("conf_id='{$res['conf_id']}'")->save($map);

					if($redis->get($val)!=$_POST[$val]) {
						$log_str .= "修改 {$word_list[$val]} 由 ".$redis->get($val)." 为 {$_POST[$val]},";
						$handle='edit';
					}
				} else {
					$map = array();
					$map['config_type'] = 'sms_alarm';
					$map['configname'] = $val;
					$map['configcontent'] = trim($_POST[$val]);
					$map['uptime'] = time();
					$map['status'] = 1;
					$this->conf_db->add($map);

					$log_str .= "添加 {$word_list[$val]} 为 {$_POST[$val]},";
					$handle='add';
				}

				//Redis更新
				$redis->set($val,trim($_POST[$val]));
			}

			if($log_str) $this->writelog($log_str,'pu_config',"config_type:{sms_alarm}",__ACTION__ ,"",$handle);

			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Config/sms_alarm');
			$this->success("编辑成功！");
		} else {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Config/sms_alarm');
			$this->error("没有提交内容，发生错误！");
		}
	}
	//短信系统报警配置管理_添加人员/编辑
	function sms_alarm_person() {
		$this->gpcFilter();

		if($_GET['id'] && is_numeric($_GET['id'])) {
			$person = M('sms_alarm_person');
			$res = $person->where("id='{$_GET['id']}'")->find();
		} else {
			$res = array();
		}

		$this->assign('res',$res);
		$this->display();
	}
	//短信系统报警配置管理_停用/启用
	function sms_alarm_person_status() {
		$this->gpcFilter();

		if(is_numeric($_GET['id']) && is_numeric($_GET['status'])) {
			$person = M('sms_alarm_person');
			$data = array();
			$data['status'] = $_GET['status'];
			$person->where("id='{$_GET['id']}'")->save($data);

			//人员放入redis，便于短信平台使用，开始
			$tmp = $person->where("status=1")->select();
			$sms_alarm_send_email = array();
			$sms_alarm_send_mobile = array();
			$sms_balance_send_email = array();
			$sms_balance_send_mobile = array();
			if($tmp) {
				foreach($tmp as $key=>$val) {
					if($val['scene']==3 || $val['scene']==1) {	//短信超量报警
						if($val['type']==3 || $val['type']==1) {	//邮件
							$sms_alarm_send_email[] = $val['email'];
						}
						if($val['type']==3 || $val['type']==2) {	//手机
							$sms_alarm_send_mobile[] = $val['mobile'];
						}
					}
					if($val['scene']==3 || $val['scene']==2) {	//短信余额报警
						if($val['type']==3 || $val['type']==1) {	//邮件
							$sms_balance_send_email[] = $val['email'];
						}
						if($val['type']==3 || $val['type']==2) {	//手机
							$sms_balance_send_mobile[] = $val['mobile'];
						}
					}
				}
			}

			$redis = new Redis();
			$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));

			$redis->set('sms_alarm_send_email',serialize($sms_alarm_send_email));
			$redis->set('sms_alarm_send_mobile',serialize($sms_alarm_send_mobile));
			$redis->set('sms_balance_send_email',serialize($sms_balance_send_email));
			$redis->set('sms_balance_send_mobile',serialize($sms_balance_send_mobile));
			//人员放入redis，便于短信平台使用，结束

			$this->writelog("编辑短信系统报警配置中的检测人员ID为{$_GET['id']}的状态为".($_GET['status']==1 ? '正常' : '停用'),'sj_sms_alarm_person',$_GET['id'],__ACTION__ ,"",'edit');

			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Config/sms_alarm');
			$this->success("操作成功！");
		}

		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Config/sms_alarm');
		$this->error("发生错误！");
	}
	//短信系统报警配置管理_添加人员_添加/编辑
	function sms_alarm_person_add() {
		$this->gpcFilter();

		$old = array();

		$person = M('sms_alarm_person');
		if($_POST['id'] && is_numeric($_POST['id'])) {	//编辑
			$res = $person->where("email='{$_POST['email']}' AND mobile='{$_POST['mobile']}' AND id!='{$_POST['id']}'")->find();
			if($res) {
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Config/sms_alarm_person/id/'.$_POST['id']);
				$this->error("该邮件地址或手机号码已存在，编辑失败！");
			}

			$old = $person->where("id='{$_POST['id']}'")->find();
		} else {						//添加
			$res = $person->where("email='{$_POST['email']}' AND mobile='{$_POST['mobile']}'")->find();
			if($res) {
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Config/sms_alarm_person');
				$this->error("该邮件地址或手机号码已存在，添加失败！");
			}
		}

		$data = array();
		$data['name'] = $_POST['name'];
		$data['email'] = $_POST['email'];
		$data['mobile'] = $_POST['mobile'];
		if($_POST['scene1'] && $_POST['scene2']) {
			$data['scene'] = 3;
		} else if($_POST['scene1']) {
			$data['scene'] = 1;
		} else if($_POST['scene2']) {
			$data['scene'] = 2;
		}
		if($_POST['type1'] && $_POST['type2']) {
			$data['type'] = 3;
		} else if($_POST['type1']) {
			$data['type'] = 1;
		} else if($_POST['type2']) {
			$data['type'] = 2;
		}
		if($_POST['id'] && is_numeric($_POST['id'])) {	//编辑
			$person->where("id='{$_POST['id']}'")->save($data);

			//日志记录
			$_fields = array(
				'name' => '检测姓名',
				'email' => '邮件地址',
				'mobile' => '手机号码',
				'scene' => '应用场景',
				'type' => '接收方式',
			);
			$_scene = array(
				'1' => '短信超量报警',
				'2' => '短信余额报警',
				'3' => '短信超量报警/短信余额报警',
			);
			$_type = array(
				'1' => '邮件',
				'2' => '手机',
				'3' => '邮件/手机',
			);
			$log_str = "修改检测人员 {$old['name']} 的 ";
			$log_str2 = '';
			if($data) {
				foreach(array('name','email','mobile','scene','type') as $key=>$val) {
					if($old[$val]!=$data[$val]) {
						if($val=='scene') {
							$log_str2 .= "{$_fields[$val]} 由 {$_scene[$old[$val]]} 变为 {$_scene[$data[$val]]},";
						} else if($val=='type') {
							$log_str2 .= "{$_fields[$val]} 由 {$_type[$old[$val]]} 变为 {$_type[$data[$val]]},";
						} else {
							$log_str2 .= "{$_fields[$val]} 由 {$old[$val]} 变为 {$data[$val]},";
						}
					}
				}
			}
			if($log_str2) $this->writelog($log_str.$log_str2,'sj_sms_alarm_person',$_POST['id'],__ACTION__ ,"",'edit');
		} else {	//添加
			$data['addtime'] = time();
			$data['status'] = 1;
			$ret=$person->add($data);

			$this->writelog("短信系统报警配置：添加检测人员{$_POST['name']}",'sj_sms_alarm_person',$ret,__ACTION__ ,"",'add');
		}

		//人员放入redis，便于短信平台使用，开始
		$tmp = $person->where("status=1")->select();
		$sms_alarm_send_email = array();
		$sms_alarm_send_mobile = array();
		$sms_balance_send_email = array();
		$sms_balance_send_mobile = array();
		if($tmp) {
			foreach($tmp as $key=>$val) {
				if($val['scene']==3 || $val['scene']==1) {	//短信超量报警
					if($val['type']==3 || $val['type']==1) {	//邮件
						$sms_alarm_send_email[] = $val['email'];
					}
					if($val['type']==3 || $val['type']==2) {	//手机
						$sms_alarm_send_mobile[] = $val['mobile'];
					}
				}
				if($val['scene']==3 || $val['scene']==2) {	//短信余额报警
					if($val['type']==3 || $val['type']==1) {	//邮件
						$sms_balance_send_email[] = $val['email'];
					}
					if($val['type']==3 || $val['type']==2) {	//手机
						$sms_balance_send_mobile[] = $val['mobile'];
					}
				}
			}
		}

		$redis = new Redis();
		$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));

		$redis->set('sms_alarm_send_email',serialize($sms_alarm_send_email));
		$redis->set('sms_alarm_send_mobile',serialize($sms_alarm_send_mobile));
		$redis->set('sms_balance_send_email',serialize($sms_balance_send_email));
		$redis->set('sms_balance_send_mobile',serialize($sms_balance_send_mobile));
		//人员放入redis，便于短信平台使用，结束

		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Config/sms_alarm');
		$this->success("操作成功！");
	}
}
?>