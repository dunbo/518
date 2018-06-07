<?php
/**
 * 系统管理:
 */
class SysManagerAction extends CommonAction{
	// 显示初使配置信息
	function initConf(){
		$model = D('Cooperative.SysManager');
		$info = $model->select_conf();

		$this->assign('info',$info);
		$this->display();
	}
	// 编辑初使配置信息
	function editConf(){
		foreach ($_POST as $k=>$v){
			$_POST[$k] = trim($v);
		}
	
		if (!is_numeric($_POST['ad_price']) || $_POST['ad_price']>10){
			$this -> error("单个软件下载单价格式错误");
		} else {
			if ($_POST['ad_price']!=number_format($_POST['ad_price'],2)){
				$this -> error('单个软件下载单价小数点最多两位小数');
			}
		}
		if (!is_numeric($_POST['ad_max_down']) || $_POST['ad_max_down']>100){
			$this -> error('防刷量值格式错误');
		} else {
			if ($_POST['ad_max_down']!=number_format($_POST['ad_max_down'])){
				$this -> error('防刷量值为整数');
			}
		}
		if (!is_numeric($_POST['ad_pre']) || $_POST['ad_pre']>100){
			$this -> error('广告扣量比例格式错误');
		} else {
			if ($_POST['ad_pre']!=number_format($_POST['ad_pre'],2)){
				$this -> error('广告扣量比例最多保留两位小数');
			}
		}		
		if (!is_numeric($_POST['game_pre']) || $_POST['game_pre']>100){
			$this -> error('游戏扣量比例格式错误');
		} else {
			if ($_POST['game_pre']!=number_format($_POST['game_pre'],2)){
				$this -> error('游戏扣量比例最多保留两位小数');
			}
		}		
		if (!is_numeric($_POST['corporate_pre']) || $_POST['corporate_pre']>100){
			$this -> error('企业扣税比例格式错误');
		} else {
			if ($_POST['corporate_pre']!=number_format($_POST['corporate_pre'],2)){
				$this -> error('企业扣税比例最多保留两位小数');
			}
		}		
		if (!is_numeric($_POST['preson_pre']) || $_POST['preson_pre']>100){
			$this -> error('个人扣税比例格式错误');
		} else {
			if ($_POST['preson_pre']!=number_format($_POST['preson_pre'],2)){
				$this -> error('个人扣税比例最多保留两位小数');
			}
		}		
		
		$data = array(
			'ad_price'=>$_POST['ad_price'],
			'ad_max_down'=>$_POST['ad_max_down'],
			'ad_pre'=>$_POST['ad_pre'],
			'game_pre'=>$_POST['game_pre'],
			'corporate_pre'=>$_POST['corporate_pre'],
			'preson_pre'=>$_POST['preson_pre'],
			'status'=>1,
			'reflash_time'=>time()
		);
		$log_model = D('Cooperative.SysManager');

		$log_all_need = $log_model -> logcheck(array('id' => 1),'t_init_config',array('ad_price'=> $_POST['ad_price'],'ad_max_down' => $_POST['ad_max_down'],'ad_pre' => $_POST['ad_pre'],'game_pre' => $_POST['game_pre'],'corporate_pre' => $_POST['corporate_pre'],'preson_pre' => $_POST['preson_pre']));
		foreach($log_all_need as $key => $val){
			$msg .= "{$val[0]}(编辑前:{$val[1]};编辑后{$val[2]}),";
		}
		$id = FALSE;
		if ($_POST['id']){
			$id = $_POST['id'];
		}
		$model = D('Cooperative.SysManager');
		$b = $model->edit_conf($data, $id);
	
		if($b){
			$this -> writelog($msg);
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SysManager/initConf');
			$this -> success("修改成功");
		}else{
			$this -> error("修改失败");
		}
	}
	// 管理员列表
	function managerPurview(){
		$limit = $_GET['lr'] ? $_GET['lr'] : 10;
		if (isset($_GET['search'])){
			$search = trim($_GET['search']);
		}
		$model = D('Cooperative.SysManager');
		$admin_model = new Model();
		import("@.ORG.Page");
		
		if ($search && $search != '请输入管理员用户名'){
			/*$where['aname']  = array('like', "%$search%");
			$where['aname_zh']  = array('like', "%$search%");
			$where['_logic'] = 'or';*/
			$where = "(aname like '%$search%' OR aname_zh like '%$search%') AND status<>0";
			$n = $model -> table("t_manager") -> where($where) -> count();
			$Page = new Page($n, $limit);
			$list = $model -> table("t_manager") -> where($where) -> order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
		} else {
			$n = $model -> table("t_manager") -> where("status<>0")-> count();
		
			$Page = new Page($n, $limit);
			$list = $model -> table("t_manager") -> where("status<>0")-> order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
		}
		foreach($list as $k => $v){
			if(!$_GET['p']){
				$_GET['p'] = 1;
			}
		
			$v['num'] = ($_GET['p'] - 1)*$limit + 1 + $k;
			$list[$k]['create_time'] = !empty($v['create_time']) ? date("Y-m-d H:i:s",$v['create_time']) : '';
			$admin_vist = $admin_model -> table('sj_admin_users') -> where(array('admin_user_id' => $v['aid'])) -> select();
			$v['admin_visits'] = $admin_vist[0]['admin_visits'];
			$list[$k] = $v;
		} 
		$today = date("Y-m-d");
		$month_ago = date("Y-m-d",(time()-86400*90));
		$this -> assign('month_ago',$month_ago);
		$this -> assign('today',$today);
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');	
		$show = $Page->show();
		$this->assign('page',$show);
		$this -> assign('lr',$limit);	
		$this -> assign('search',$search);
		$this -> assign("list",$list);
		$this -> display();
	}
	// admin管理员
	function adminList(){
		$search = $_POST['admin_name'];
		if ($search){
			$w = " admin_state = 1 AND admin_user_name LIKE '%$search%' OR user_name_py LIKE '%$search%'";
		}else{
			$w = " admin_state = 1";
		}
		$model = new Model();
		$admin = $model->table('sj_admin_users') -> where($w)->field('admin_user_id,admin_user_name,last_logintime,user_name_py')-> order("last_logintime DESC") -> select();
		$D = D('Cooperative.SysManager');
		$mid = $D->table('t_manager')->where('status != 0') -> field('aid')->select();
		$m_id = array();
		foreach ($mid as $val){
			$m_id[]=$val['aid'];
		}
		
		foreach ($admin as $k=>$v){
			$admin[$k]['last_logintime'] = date("Y-m-d H:i:s",$v['last_logintime']);
			if (in_array($v['admin_user_id'], $m_id)){
				$admin[$k]['join'] = 1;
			}
		}
		if(isset($_POST['admin_name'])){
			if($admin){
				echo json_encode($admin);
				return;
			} else {
			    echo json_encode(array());
			    return;
			}
		}
		$this->assign('admin',$admin);
		$this->display();
	}
	// 添加管理员
	function addAdmin(){
		if (count($_POST['aid'])>=1){
			$aid = implode(",", $_POST['aid']);
		}
		
		$model = new Model();
		$co_model = D('Cooperative.SysManager');
		foreach($_POST['aid'] as $key => $val){
			$have_result = $co_model -> table('t_manager') -> where("aid = {$val} and status != 0") -> select();
			if($have_result){
				$this -> error('已添加该管理员');
			}
		}
		$admin = $model-> table('sj_admin_users') -> where("admin_user_id IN ({$aid})")->field('admin_user_id,admin_user_name,user_name_py')->select();
	
		$D = D('Cooperative.SysManager');
		foreach ($admin as $v){
			$data = array();
			$data['aid'] = $v['admin_user_id'];
			$data['aname'] = $v['admin_user_name'];
			$data['aname_zh'] = $v['user_name_py'];
			$data['create_time'] = $data['update_time'] = time();
			$data['status'] = 1;
			$result = $D->table('t_manager')->data($data)->add();
			if($result){
				$msg .= "添加用户(用户名称:{$data['aname']})";
			}
		}
		
		if($result){
			$this -> writelog($msg);
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SysManager/managerPurview");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	// 停用管理员
	function stopAdmin(){
		if ($_GET['id']){
			$model = D('Cooperative.SysManager');
			$result = $model->changeAdminStauts(trim($_GET['id']),2);
			
			if($result){
				echo json_encode(1);
			} else {
				echo json_encode(0);
			}
		} else {
			echo json_encode(0);
		}
		$result_manager = $model -> table('t_manager') -> where(array('id' => $_GET['id'])) -> select();
		if($result){
			$this -> writelog("编辑管理员(管理员{$result_manager[0]['aname']})的状态为停用");
		}
	}
	// 恢复管理员
	function restoreAdmin(){
		if ($_GET['id']){
			$model = D('Cooperative.SysManager');
			if ($result = $model->changeAdminStauts(trim($_GET['id']),1)){
				echo json_encode(1);
			} else {
				echo json_encode(0);
			}
		} else {
			echo json_encode(0);
		}
		$result_manager = $model -> table('t_manager') -> where(array('id' => $_GET['id'])) -> select();
		if($result){
			$this -> writelog("编辑管理员(管理员{$result_manager[0]['aname']})的状态为正常");
		}
	}
	// 删除管理员
	function delAdmin(){
	
		if ($_GET['id']){
			$model = D('Cooperative.SysManager');
			$result = $model->changeAdminStauts(trim($_GET['id']),0);
	
		}
		$result_manager = $model -> table('t_manager') -> where(array('id' => $_GET['id'])) -> select();
		if($result){
			$this -> writelog("编辑管理员(管理员{$result_manager[0]['aname']})的状态为删除");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/SysManager/managerPurview');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	//复制权限显示
	function copyAdmin(){
		$id = $_GET['id'];
		$search = $_GET['search'];
		$model = D('Cooperative.SysManager');
		$list = $model -> table("t_manager") -> where("status<>0 AND id<>{$id}")-> field('id,aid,aname') -> order('create_time DESC')->select();
		$this->assign('search',$search);
		$this->assign('fromId',$_GET['aid']);
		$this->assign('admin', $list);
		$this->display();
	}
	
	
	//复制权限提交
	function toAdmin(){
		$co_model = D('Cooperative.SysManager');
		$model = new Model();
		$fromid = $_POST['fromid'];
		$aid = $_POST['aid'];
		$search = $_POST['search'];
		if(!$aid){
			$this -> error("请选择复制人");
		}
		$my_admin_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $fromid)) -> select();
		$other_admin_result = $model -> table('sj_admin_users') -> where(array('admin_user_id' => $aid)) -> select();
		$my_account_result = $co_model -> table('t_manager_purview') -> where(array('aid' => $fromid,'charge_type' => 2)) -> select();
		$my_charge_result = $co_model -> table('t_manager_purview') -> where(array('aid' => $fromid,'charge_type' => 1)) -> select();
		
		foreach($my_account_result as $key => $val){
			$data['charge_type'] = 2;
			$data['charge_value'] = $val['charge_value'];
			$data['create_time'] = time();
			$data['aid'] = $aid;
			$result_user = $co_model -> diffAccount($data);
			$result_all[] = $result_user;
		}
		foreach($my_charge_result as $key => $val){
			$data['charge_type'] = 1;
			$data['charge_value'] = $val['charge_value'];
			$data['create_time'] = time();
			$data['aid'] = $aid;
			$result_charge = $co_model -> diffAccount($data);
			$result_charge_all[] = $result_charge;
		}
		
		if($result_user && $result_charge){
			$this -> writelog("编辑用户{$my_admin_result[0]['admin_user_name']}的账号查看权限复制给用户{$other_admin_result[0]['admin_user_name']}");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SysManager/managerPurview/search/{$search}");
			$this -> success("复制成功");
		}else{
			if(!$result_all || !$result_charge_all){
				$this -> writelog("编辑用户{$my_admin_result[0]['admin_user_name']}的账号查看权限复制给用户{$other_admin_result[0]['admin_user_name']}");
				$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SysManager/managerPurview/search/{$search}");
				$this -> success("复制成功");
			}
		}
	}
	function editAdmin(){
		$aid = trim($_GET['aid']);
		$search = $_GET['search'];
		$D = D('Cooperative.SysManager');
		$adminAccount = $D->seeAccount($aid);
		$charge_result = $D -> table('t_charge') -> where(array('status' => 1)) -> select();;
		$have_result = $D -> table('t_manager_purview') -> where(array('charge_type' => 2,'aid' => $aid)) -> select();
		$have_charge_result = $D -> table('t_manager_purview') -> where(array('charge_type' => 1,'aid' => $aid)) -> select();
		
		foreach($have_result as $key => $val){
			$have_uid[] = $val['charge_value'];
		}
		
		foreach($have_charge_result as $key => $val){
			$have_charge[] = $val['charge_value'];
		}
		foreach($adminAccount as $key => $val){
			$val['count'] = count($val['account']);
			$adminAccount[$key] = $val;
		}
		$this -> assign('charge_result',$charge_result);
		$this -> assign('have_uid',$have_uid);
		$this -> assign('have_charge',$have_charge);
		$this -> assign('aid',$aid);
		$this -> assign('search',$search);
		$this->assign('adminAccount',$adminAccount);
		$this->display();
	}
	
	function editAdmin_submit(){
		$aid = $_POST['aid'];
		$uid = $_POST['uid'];
		$charge_id = $_POST['charge_id'];
		$search = $_POST['search'];
		$co_model = D('Cooperative.SysManager');
		$model = new Model();
		$have_result = $co_model -> table('t_manager_purview') -> where(array('aid' => $aid,'charge_type' => 2)) -> select();
		$have_charge_result = $co_model -> table('t_manager_purview') -> where(array('aid' => $aid,'charge_type' => 1)) -> select();
		
		foreach($have_charge_result as $key => $val){
			$have_charge[] = $val['charge_value'];
		}
		foreach($have_result as $key => $val){
			$have_uid[] = $val['charge_value'];
		}
	
		if($have_uid){
			$new_uid = array_diff($uid,$have_uid);
		}else{
			$new_uid = $uid;
		}
		if($have_charge){
			$new_charge = array_diff($charge_id,$have_charge);
		}else{
			$new_charge = $charge_id;
		}

		if($have_uid && $uid){
			$del_uid = array_diff($have_uid,$uid);
		}elseif(!$have_uid && $uid){
			$del_uid = array();
		}elseif($have_uid && !$uid){
			$del_uid = $have_uid;
		}
		
		if($have_charge && $charge_id){
			$del_charge = array_diff($have_charge,$charge_id);
		}elseif(!$have_charge && $charge_id){
			$del_charge = array();
		}elseif($have_charge && !$charge_id){
			$del_charge = $have_charge;
		}
		
		if($new_uid){
			if(is_array($new_uid)){
				foreach($new_uid as $key => $val){
					$data['aid'] = $aid;
					$data['charge_value'] = $val;
					$data['charge_type'] = 2;
					$data['create_time'] = time();
					$result = $co_model -> diffAccount($data);
				}
			}else{
				$data['aid'] = $aid;
				$data['charge_value'] = $new_uid;
				$data['charge_type'] = 2;
				$data['create_time'] = time();
				$result = $co_model -> diffAccount($data);
			}
		}
		
		if($new_charge){
			if(is_array($new_charge)){
				foreach($new_charge as $key => $val){
					$data['aid'] = $aid;
					$data['charge_value'] = $val;
					$data['charge_type'] = 1;
					$data['create_time'] = time();
					$charge_result = $co_model -> diffAccount($data);
				}
			}else{
				$data['aid'] = $aid;
				$data['charge_value'] = $new_charge;
				$data['charge_type'] = 1;
				$data['create_time'] = time();
				$charge_result = $co_model -> diffAccount($data);
			}
		}

		if($del_uid){
			foreach($del_uid as $key => $val){
				$where['aid'] = $aid;
				$where['charge_value'] = $val;
				$where['charge_type'] = 2;
				$del_result = $co_model -> table('t_manager_purview') -> where($where) -> delete();
			}
		}

		if($del_charge){
			foreach($del_charge as $key => $val){
				$where['aid'] = $aid;
				$where['charge_value'] = $val;
				$where['charge_type'] = 1;
				$del_charge_result = $co_model -> table('t_manager_purview') -> where($where) -> delete();
			}
		}
		
		$admin_result = $co_model -> table('sj_admin_users') -> where(array('admin_user_id' => $aid)) -> select();
		foreach($new_uid as $key => $val){
			$new_str .= $val.',';
		}
		foreach($del_uid as $key=> $val){
			$del_str .= $val.',';
		}
		if($result || $del_result || empty($new_uid) || $charge_result || $del_charge_result){
			$this -> writelog("已添加用户{$admin_result[0]['admin_user_name']}的账号查看权限id为{$new_str},删除账号查看权限id为{$del_str},添加负责人id为{$new_charge},删除负责人id为{$del_charge}");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/SysManager/managerPurview/search/{$search}");
			$this -> success("编辑成功");
		}else{
			$this -> error('编辑失败');
		}
	}
	// 账号负责人列表
	function chargeList(){
		if (isset($_GET['charge']) && trim($_GET['charge'])){
			$charge = trim($_GET['charge']);
			$this->assign('charge_name',$charge);
		}
		$limit = $_GET['lr'] ? $_GET['lr'] : 10;
		
		$model = D('Cooperative.SysManager');
		import("@.ORG.Page");
		$where['status'] = 1;
		if ($charge){
			$where['charge_name']  = array('like', "%$charge%");
			//$where['charge_name_zh']  = array('like', "%$charge%");
			$n = $model -> table("t_charge") -> where($where) -> count();
			$Page = new Page($n, $limit);
			$list = $model -> table("t_charge") -> where($where) -> order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		} else {
			$n = $model -> table("t_charge") -> where($where) -> count();
			$Page = new Page($n, $limit);
			$list = $model -> table("t_charge") -> where($where) -> order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
		}
		foreach($list as $k => $v){
			$list[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
			$cid[] = $v['id'];
		} 
		$cid = implode(",", $cid);
		$num = $model->table('t_user')->where("charge_person IN({$cid})")->field('charge_person,COUNT(*) num')->group('charge_person')->select();
		foreach ($list as $k=>$v){
			foreach ($num as $val){
				if ($val['charge_person']==$v['id']){
					$list[$k]['charge_number'] = $val['num'];
				}
			}
		}
		
		foreach($list as $key => $val){
			if(empty($_GET['p']) || $_GET['p'] == 1){
				$val['num'] = $key + 1;
			}else{
				$val['num'] = $n*($_GET['p']-1)+$key+1;
			}
			$list[$key] = $val;
		}

		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign('lr', $limit);
		$this -> assign("charge",$list);
		$this -> display();
	}
	function addCharge(){
	    $charge = trim($_POST['charge']);

		if (empty($charge) && isset($_POST['charge'])) {
            echo json_encode(3);
            return;
		}
		
		if ($charge){
		    // 中英文和数字,不允许出现特殊字符
		    if (preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/iu', $charge) && mb_strlen($charge, 'utf-8') <30){
				$model = D('Cooperative.SysManager');
				// 是否已经存在
				if ($model->checkCharge($charge)==0){
					echo json_encode(1);
					$r = $model->addCharge($charge);
					//var_dump($r);
					if ($r){	// 添加
						$this->writelog("添加了账号：{$charge}");		
					}
				} else {
					echo json_encode(2);
				}
			} else {
				echo json_encode(4);
			}
		} else {
			$this->display();
		}
	}
	function editCharge(){
		$id = '';
		if ($_GET['id']){
			$id = $_GET['id'];
			$D = D('Cooperative.SysManager');
			$charge = $D->table('t_charge')->where("id={$id} and status=1")->select();
			$this->assign('charge',$charge[0]);
			$this->display();
		} elseif ($_POST['id']){
			$id = $_POST['id'];
			$chname = trim($_POST['charge']);
			if(strlen($_POST['charge']) > 30){
				echo json_encode(4);
				return ;
			}
			if (empty($chname)) {
			    echo json_encode(3);
				return ;
			}
			if (!(preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/iu', $chname) && mb_strlen($chname, 'utf-8') <30)) {
			    echo json_encode(0);
			    return ;
			}
			
			$data = array('status'=>1, 'update_time'=>time(), 'charge_name'=>$chname);
			$D = D('Cooperative.SysManager');
			$n = $D->table('t_charge')->where("charge_name='{$chname}' AND id<>$id AND status=1")->count();
			if ($n){
				echo json_encode(2);
				return ;
			} else {
			    $re = $D->logcheck(array('id'=>$id),'t_charge',array('charge_name'=>$chname));
				$b = $D->table('t_charge')->where("id={$id} and status=1")->save($data);
				if ($b){
				    $str = "编辑了：";
				    foreach ($re as $v) {
				        $str .= $v[0]."由".$v[1]."改成了".$v[2];
				    }
				    $this->writelog($str);
					echo json_encode(1);
				} else {
					echo json_encode(0);
				}	
				return ;		
			}
		}
	}
	function delCharge(){
		$id = '';
		if ($_POST['id']){
			$id = trim($_POST['id']);
			$id = (int) $id;
			$D = D('Cooperative.SysManager');
			$b = $D->delcharge($id);
			if ($b){
			    $charge = $D->table('t_charge')->where("id={$id}")->select();
			    $this->writelog("删除了负责人，id为$id，名称为".$charge[0]['charge_name']);
				echo json_encode(1);
			} else {
				echo json_encode(0);
			}
		}
	}
	function moveCharge(){
		$D = D('Cooperative.SysManager');
		if ($_GET['id']){
			$id = (int) $_GET['id'];
			$chuser = $D->table('t_user')->where("charge_person=$id")->field('uid,user_name,charge_person,create_time,status')->order('create_time DESC')->select();
			$charge = $D->table('t_charge')->where('status=1')->field('id,charge_name')->select();
			foreach ($chuser as $k=>$v){
				$chuser[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
				switch ($v['status']){
					case 1:
						$chuser[$k]['stat'] = '正常';
						break;
					case 2:
						$chuser[$k]['stat'] = '暂停';
						break;
					default:
						break;
				}
				foreach ($charge as $val){
					if ($val['id']==$v['charge_person']){
						$chuser[$k]['charge_name'] = $val['charge_name'];
					}
				}
			}
			foreach ($charge as $key=>$v){
				if ($v['id']==$id){
					$from = $v;
					unset($charge[$key]);
					break;
				}
			}
			$charge_name = $_GET['charge'];
			$this -> assign('charge_name',$charge_name);
			$this->assign('from',$from);
			$this->assign('charge',$charge);
			$this->assign('chuser',$chuser);
			$this->display();
		}
	}
	
	function moveChargeDo() {
	    if ($_POST && !empty($_POST['uid'])) {
			$uid = $_POST['uid'];
			$D = D('Cooperative.SysManager');
			if ($D->moveAccount($uid,$_POST['fromCharge'],$_POST['toCharge'])){
			    $from_charge = $D->table('t_charge')->where('status=1 and id='.$_POST['fromCharge'])->field('id,charge_name')->select();
			    $to_charge = $D->table('t_charge')->where('status=1 and id='.$_POST['toCharge'])->field('id,charge_name')->select();
			    $user = $D->table('t_user')->where("uid in({$uid})")->field('user_name')->select();
			    foreach ($user as $v) {
			        $uname[] = $v['user_name']; 
			    }
			    $uname = implode(",", $uname);
				$charge_name = $_POST['charge_name'];
			    $str = "将账号'".$uname."'从负责人'{$from_charge[0]['charge_name']}'转移给'{$to_charge[0]['charge_name']}'";
                $this->writelog($str);
				echo 1;
				return;
			} else {
				echo 0;
				return; 
			}
		} else {
		    echo 2;
		    return;
		}
	}
	
	// 操作日志展示
	function logList(){
	    //操作页面规整
	    $action_list = array (
			'账号列表' => '/index.php/Cooperative/CoAccount/addAccount,/index.php/Cooperative/CoAccount/editSettlement,/index.php/Cooperative/CoAccount/stopAccount,/index.php/Cooperative/CoAccount/restoreAccount,/index.php/Cooperative/CoAccount/editAccount',
			'渠道列表' => '/index.php/Cooperative/CoAccount/addChannel,/index.php/Cooperative/CoAccount/editChannel,/index.php/Cooperative/CoAccount/delChannel,/index.php/Cooperative/CoAccount/stopChannel,/index.php/Cooperative/CoAccount/recChannel,/index.php/Cooperative/CoAccount/addChannel_do,/index.php/Cooperative/CoAccount/editChannel_do',
			'待审核列表' => '/index.php/Cooperative/Incomemanage/check_edit_do',
			'报警设置列表' => '/index.php/Cooperative/Incomemanage/update_warning_do,/index.php/Cooperative/Incomemanage/add_warning_do,/index.php/Cooperative/Incomemanage/delete_warning',
			'业务审核列表' => '/index.php/Cooperative/Balancemanage/update_salvation,/index.php/Cooperative/Balancemanage/update_tax,/index.php/Cooperative/Balancemanage/edit_account_do,/index.php/Cooperative/Balancemanage/operation_check_do,/index.php/Cooperative/Balancemanage/operation_freeze_do,/index.php/Cooperative/Balancemanage/operation_remark_add',
			'财务审核列表' => '/index.php/Cooperative/Balancemanage/operation_remark_add,/index.php/Cooperative/Balancemanage/finance_check_do',
	        '待付款列表' => '/index.php/Cooperative/Balancemanage/obligation_check,/index.php/Cooperative/Balancemanage/obligation_complemented_do',
	        '已冻结列表' => '/index.php/Cooperative/Balancemanage/unfreeze_do',
	        '初始配置管理' => '/index.php/Cooperative/SysManager/editConf',
	        '管理员权限管理' => '/index.php/Cooperative/SysManager/addAdmin,/index.php/Cooperative/SysManager/restoreAdmin,/index.php/Cooperative/SysManager/stopAdmin,/index.php/Cooperative/SysManager/delAdmin,/index.php/Cooperative/SysManager/toAdmin',
	        '账号负责人列表' => '/index.php/Cooperative/SysManager/addCharge,/index.php/Cooperative/SysManager/editCharge,/index.php/Cooperative/SysManager/moveChargeDo,/index.php/Cooperative/SysManager/delCharge',
		);
	    
		//获取所有的操作人员
		$model = D('Cooperative.SysManager');
		$all_manager = $model->getAllManager();
		$all_charge = $model->getAllCharge();
		
		// 搜索字段create_time、referer、operator、charge、account_name、channel_name、field
		$limit = $_GET['lr'] ? $_GET['lr'] : 10;
		$p = $_GET['p'] ? $_GET['p'] : 1;
				
		$model = D('Cooperative.CooperativeLog');
		$action_list = $model->nodename2id($action_list);
		$action_map = array();
		$action_id_array = array();
	    foreach ($action_list as $key => $value) {
	    	# code...
	    	$tmp = explode(',', $value);
	    	foreach ($tmp as $v) {
	    		# code...
	    		$action_map[$v] = $key;
	    		$action_id_array[] = $v;
	    	}
	    }
	    $this->assign('action_map', $action_map);
	    $this->assign('action_list', $action_list);
	    
	    $log_model=M("admin_log");
		$user_model=M("admin_users");
		
	    if (!empty($_REQUEST['action_id'])) {
			if (!strstr($_REQUEST['action_id'], ':')) {
				$where['action_id'] = array('in', $_REQUEST['action_id']);
			} else {
				$tmp = explode(',', $_REQUEST['action_id']);
				$str1 = array();
				$str2 = array();
				$w = array();
				foreach ($tmp as $val) {
					if (!strstr($val, ':')) {
						$str1[] = $val;
					} else {
						$t = explode(':', $val);
						$str2[] = "(action_id={$t[0]} and extra='{$t[1]}')";
					}
				}
				if (!empty($str1)) {
					$w[] = 'action_id in ('. implode(',', $str1). ')';
				}
				
				if (!empty($str2)) {
					$w[] = implode(' or ', $str2);
				}
				if (!empty($w)) {
					$where['_string'] = implode(' or ', $w);
				}
				
				
			}
			$this->assign('action_id', $_REQUEST['action_id']);
		} else {
		    $where['action_id'] = array('in', $action_id_array);
		}
		
		$this->check_where($where, 'admin_id', 'noempty');
		
	    $where_arr = array();
        if (!empty($_REQUEST['actionexp'])) {
            $actionexp = $_REQUEST['actionexp'];
            $str = " actionexp like '%{$actionexp}%'";
            $where_arr[] = $str;
            $this->assign('actionexp',$_REQUEST['actionexp']);
        }

        if (!empty($_REQUEST['charge_name'])) {
            $charge_name = $_REQUEST['charge_name'];
            $charge_name = $all_charge[$charge_name]['charge_name'];
            $str = " actionexp like '%{$charge_name}%'";
            //$where['_string'] .= $str;\
            $where_arr[] = $str;
            $this->assign('charge_name',$_REQUEST['charge_name']);
        }   

        if (!empty($_REQUEST['chname'])) {
            $chname = $_REQUEST['chname'];
            $str = " actionexp like '%{$chname}%'";
            $where_arr[] = $str;
            $this->assign('chname',$_REQUEST['chname']);
        }

        if (!empty($_REQUEST['user_name'])) {
            $user_name = $_REQUEST['user_name'];
            $str = " actionexp like '%{$user_name}%'";
            $where_arr[] = $str;
            $this->assign('user_name',$_REQUEST['user_name']);
        }

        if (!empty($where_arr)) {
            $where['_string'] = implode(" and ",$where_arr);
        }
		
		$this->check_range_where($where, 'start_tm', 'end_tm', 'logtime', true);
    	$where_t = array();
		$this->check_other_table_where($where, $where_t, 'admin_user_name', array('admin_id', 'admin_user_id'), 'sj_admin_users', 'isset');
		$count = $log_model->where($where)->count();		
		import("@.ORG.Page");
	    $Page=new Page($count,30, $limit);
		$list = $log_model->where($where)
			->limit($Page->firstRow.','.$Page->listRows)
			->order('logtime desc')
			->select();
		
		$admin_ids = array();
		
		foreach ($list as $val) {
			# code...
			$admin_ids[] = $val['admin_id'];
		}
		$where = array(
			'admin_user_id' => array('in', $admin_ids),
		);
		$admins = $user_model->where($where)->select();
		$admin_info = array();
		foreach ($admins as $val) {
			# code...
			$admin_id = $val['admin_user_id'];
			$admin_info[$admin_id] = $val;
		}
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');	
		$this->assign('page', $Page->show());
		$this->assign('list', $list);
		$this->assign('admin_info', $admin_info);
		$this->assign("p", $p);
		$this->assign('lr', $limit);
		$this->assign("list",$list);
		$this->assign('all_manager',$all_manager);
		$this->assign('all_charge',$all_charge);
		$this->display();

		
		/*
		var_dump($action_list);exit;

	    
	    
		$limit = $_REQUEST['lr'] ? $_GET['lr'] : 10;
		if (isset($_POST['search'])){
			$search = trim($_POST['search']);
		}
				
		$model = D('Cooperative.SysManager');
		
		import("@.ORG.Page");
		if ($search){
			$where['charge_name']  = array('like', "%$search%");
			$where['aname_zhcharge_name_zh']  = array('like', "%$search%");
			$where['_logic'] = 'or';
			$n = $model -> table("t_log") -> where($where) -> count();
			$Page = new Page($n, $limit);
			$list = $model -> table("t_log") -> where($where) -> order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
		} else {
			$n = $model -> table("t_log") -> count();
			$Page = new Page($n, $limit);
			$list = $model -> table("t_log") -> order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
		}
		foreach($list as $k => $v){
			$list[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
		} 

		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');	
		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign("p", $p);
		$this->assign('lr', $limit);
		$this -> assign("list",$list);
		$this -> display();
		*/		
	}
	
    function check_where(&$where , $column, $check_func = 'isset', $where_type = 'eq', $assign = true, $column_alias='') {
 		$has_rule = false;
 		$where_key = empty($column_alias) ? $column : $column_alias;
 		$dot_pos = stripos('.', $column);
 		$key = ($dot_pos===false) ? $column : substr($column, $dot_pos);

 		switch ($check_func) {
 			case 'isset':
 				$has_rule = isset($_REQUEST[$key]);
 				break;
 			case 'noempty':
 				$has_rule = !empty($_REQUEST[$key]);
 				break; 			
 			default:
 				$has_rule = isset($_REQUEST[$key]);
 				break;
 		}
 		if ($has_rule) {
 			if ($where_type == 'eq') {
				$where[$where_key] = $_REQUEST[$key];
 			} else if ($where_type == 'like') {
 				$where[$where_key] = array('like', '%'.escape_string($_REQUEST[$key]).'%');
 			}
			$assign && $this->assign($key, $_REQUEST[$key]);
		}
		return $has_rule;
 	}

 	function check_other_table_where(&$where, $other_where, $column, $join_field, $table, $check_func = 'isset', $where_type = 'eq', $assign = true)
 	{
		$model = new Model();
		$has_rule = false;
		if (is_array($join_field)) {
			$inner_key = $join_field[0];
			$join_key = $join_field[1];
		} else {
			$inner_key = $inner_key = $join_field;
		}
		if ($this->check_where($other_where, $column, $check_func, $where_type, $assign)) {
 			$dot_pos = stripos('.', $join_key);
 			$field = ($dot_pos===false) ? $join_key : substr($join_key, $dot_pos);

			$res = $model->table($table)->where($other_where)->field($field)->select();
			$ids = array();
			if ($res) {
				foreach ($res as $value) {
					$ids[] = $value[$field];
				}
			}
			$where[$inner_key] = array('in', $ids);
		}
		return $has_rule;
 	}

 	function check_range_where(& $where, $start, $end, $column, $is_time = false, $assign = true)
 	{
 		$has_rule = false;

		if (!empty($_REQUEST[$start]) && !empty($_REQUEST[$end])) {
			if($_REQUEST[$start] > $_REQUEST[$end]){
				$this -> error("开始时间不能大于结束时间");
			}
			$where[$column] = array(
				array('egt', $is_time ? strtotime($_REQUEST[$start]) : $_REQUEST[$start]),
				array('elt', $is_time ? strtotime($_REQUEST[$end])+86399 : $_REQUEST[$end]),
			);
			$assign && $this->assign($start, $_REQUEST[$start]);
			$assign && $this->assign($end, $_REQUEST[$end]);

		} elseif (!empty($_REQUEST[$start])) {
			$where[$column] = array('egt', $is_time ? strtotime($_REQUEST[$start]) : $_REQUEST[$start]);
			
			$assign && $this->assign($start, $_REQUEST[$start]);
		} elseif (!empty($_REQUEST[$end])) {
			$where[$column] = array('elt', $is_time ? strtotime($_REQUEST[$end])+86399 : $_REQUEST[$end]);
			$assign && $this->assign($end, $_REQUEST[$end]);
		}
		return $has_rule;
 	}
}