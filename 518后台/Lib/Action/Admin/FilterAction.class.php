<?php
class FilterAction extends CommonAction {
	protected  $topFilterMap = array();
	public function __construct()
	{
		parent::__construct();
		$this->topFilterMap = array(
			'SUGGEST' => '推荐列表',
			'HOT' => '最热列表',
			'HOT1D' => '日排行',
			'HOT7D' => '周排行',
			'HOT30D' => '月排行',
			'CATEGORY_HOT' => '类别最热排行',
			'MAYBE_LIKE' => '猜你喜欢',
			'BDLIST' => '榜单',
		);
	}

	public function setFilter()
	{
		if (!empty($_POST)) {
			$this->updateFilter();
		} else {
			$model = M('admin_filter');
			$admin_id = $_GET['admin_id'];
			$all_channel = $model->Table('sj_channel')->where('status=1')->select();
			$source_str['source_type']=USER_FILTER_TYPE;
			//$source_str['filter_type']=2;
			$source_str['source_value']=$admin_id;
			$black_list = $model
				->table('sj_admin_filter')
				->where($source_str)
				->field('target_type, target_value,filter_type')
				->select();
			$black_channel = array();
			//print_r($black_list);
			$channel_coefficient = $channel_total_black = false;
			$categoryid = array();
			foreach ($black_list as $black) {
				if ($black['target_type'] == CHANNEL_FILTER_TYPE) {
					$black_channel[$black['target_value']] = 1;
				} elseif ($black['target_type'] == CHANNEL_TOTAL_FILTER_TYPE) {
					$channel_total_black = true;
				} elseif ($black['target_type'] == CHANNEL_COEFFICIENT_TYPE) {
					if ($black['filter_type'] == 2) {
						$channel_coefficient = true;
					} else {
						$channel_coefficient = false;
					}
				} elseif ($black['target_type'] == CHANNEL_SHOW_TYPE) {
					if ($black['filter_type'] == 2) {
						$channel_show = true;
					} else {
						$channel_show = false;
					}
				}elseif ($black['target_type'] == CHANNEL_SHOW_CONTROL) {
					if ($black['filter_type'] == 1) {
						$channel_user = true;
					} else {
						$channel_user = false;
					}
				}elseif($black['target_type'] == APK_SHOW){
					if($black['filter_type'] == 1){
						$apk_go = true;
					}else{
						$apk_go = false;
					}
				} elseif ($black['target_type'] == CHANNEL_SHOW_CONTROL_BY_CATEGORY){
					if ($black['filter_type'] == 2){
						array_push($categoryid, (int)$black['target_value']);
					}
				}
			}
			$categoryid = array_unique($categoryid);

			$channel_category = D('Sj.ChannelCategory');
			$category_list = $channel_category->getCategory();
			$category_list_2=$category_list;
			// echo "<pre>";var_dump($category_list);die;
			foreach ($all_channel as $key => $channel) {
				$all_channel[$key]['selected'] = isset($black_channel[$channel['cid']]);
				$category_id = $channel['category_id'];
				$category_list[$category_id]['result'][] = $all_channel[$key];
			}
			// echo "<pre>";var_dump($category_list);die;
			$this->assign('apk_go',$apk_go);
			$this->assign('category_list', $category_list);
			$this->assign('all_channel', $all_channel);
			$this->assign('admin_id', $admin_id);
			$this->assign('channel_total_black', $channel_total_black);
			$this->assign('channel_coefficient', $channel_coefficient);
			$this->assign('channel_show', $channel_show);
			$this->assign('channel_user', $channel_user);
			$this->assign('cid_arr', $categoryid);
			$util = D('Sj.Util');
			$this->assign('product_list',$util->getProducts($platform));
			// var_dump($category_list);die;
			$this->assign('category_list_2',$category_list_2);
			$this->display();
		}
	}

	protected function updateFilter()
	{
		$model = M('admin_filter');
		$admin_id = $_POST['admin_id'];
		//日志,开始
		$log_arr = array(
			'apk_show' => array(
				'1' => 'APK可见',
				'2' => 'APK不可见',
			),
			'show_channel_total' => array(
				'1' => '显示渠道用户总数',
				'0' => '隐藏渠道用户总数',
			),
			'show_channel_coefficient' => array(
				'2' => '显示渠道系数统计',
				'1' => '隐藏渠道系数统计',
			),
			'channel_show' => array(
				'2' => '显示渠道号',
				'1' => '隐藏渠道号',
			),
			'channel_user' => array(
				'2' => '开启渠道过滤设置',
				'1' => '关闭渠道过滤设置',
			),
		);
		$log = $log2 = '';
		if($_POST['_apk_show']!=$_POST['apk_show']) {
			$log .= "将“软件编辑->APK模块可见性”由“{$log_arr['apk_show'][$_POST['_apk_show']]}”修改为“{$log_arr['apk_show'][$_POST['apk_show']]}”<br />";
		}
		if($_POST['_show_channel_total']!=$_POST['show_channel_total']) {
			$log .= "将“渠道用户量统计->用户总数显示开关”由“{$log_arr['show_channel_total'][$_POST['_show_channel_total']]}”修改为“{$log_arr['show_channel_total'][$_POST['show_channel_total']]}”<br />";
		}
		if($_POST['_show_channel_coefficient']!=$_POST['show_channel_coefficient']) {
			$log .= "将“渠道用户量统计-> 渠道系数显示开关”由“{$log_arr['show_channel_coefficient'][$_POST['_show_channel_coefficient']]}”修改为“{$log_arr['show_channel_coefficient'][$_POST['show_channel_coefficient']]}”<br />";
		}
		if($_POST['_channel_show']!=$_POST['channel_show']) {
			$log .= "将“渠道号显示开关”由“{$log_arr['channel_show'][$_POST['_channel_show']]}”修改为“{$log_arr['channel_show'][$_POST['channel_show']]}”<br />";
		}
		if($_POST['_channel_user']!=$_POST['channel_user']) {
			$log .= "将“渠道设置显示开关”由“{$log_arr['channel_user'][$_POST['_channel_user']]}”修改为“{$log_arr['channel_user'][$_POST['channel_user']]}”<br />";
		}
		if($log) {
			$log = "对屏蔽信息进行了修改：<br />".$log;
		}
		//日志,结束
		//管理员登录密码验证,开始
		if(empty($_POST['_login_password2']) && empty($_POST['_login_password3'])) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
			$this->error('密码不能为空，操作失败！');
		} else if(!empty($_POST['_login_password2'])) {
			if(!$this->login_pwd_chk('_login_password2')) {
				$this->writelog("对管理员{$this->getNameById($_POST['admin_id'])}(UID:{$_POST['admin_id']})屏蔽信息管理时，密码验证失败",'sj_admin_filter',$_POST['admin_id'],__ACTION__ ,"","view");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
				$this->error('密码验证失败，操作失败！');
			}
		} else if(!empty($_POST['_login_password3'])) {
			if(!$this->login_pwd_chk('_login_password3')) {
				$this->writelog("对管理员{$this->getNameById($_POST['admin_id'])}(UID:{$_POST['admin_id']})屏蔽信息管理中渠道设置时，密码验证失败",'sj_admin_filter',$_POST['admin_id'],__ACTION__ ,"","view");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/WebAdmin/index');
				$this->error('密码验证失败，操作失败！');
			}
		}
		//管理员登录密码验证,结束
		if ($_POST['show_channel_total'] == 1) {
			$map = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'target_type' => CHANNEL_TOTAL_FILTER_TYPE,
				'filter_type' => 1,
			);
			$model->where($map)->delete();
		} elseif ($_POST['show_channel_total'] == 0) {
			$map = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'target_type' => CHANNEL_TOTAL_FILTER_TYPE,
				'filter_type' => 1,
			);
			$filter = $model->where($map)->select();

			if (!$filter) {
				$map['addtime'] = time();
				$map['filter_type'] = 1;
				$map['target_value'] = 1;
				$model->add($map);
			}
		}
		//apk编辑显示开关控制
		if(isset($_POST['apk_show'])){
			$where = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'target_type' => APK_SHOW
			);
			$filter = $model -> where($where) -> select();
			
			if(!$filter){
				$map = $where;
				$map['addtime'] = time();
				$map['target_value'] = 1;
				$map['filter_type'] = $_POST['apk_show'];
				$model -> add($map);
			}else{
				$data = array();
				$data['filter_type'] = $_POST['apk_show'];
				$model -> where($where) -> save($data);
			}
		}
		
		if (isset($_POST['show_channel_coefficient'])) {
			$where = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'target_type' => CHANNEL_COEFFICIENT_TYPE,
			);
			$filter = $model->where($where)->select();

			if (!$filter) {
				$map = $where;
				$map['addtime'] = time();
				$map['target_value'] = 1;
				$map['filter_type'] = $_POST['show_channel_coefficient'];
				$model->add($map);
			} else {
				$data = array();
				$data['filter_type'] = $_POST['show_channel_coefficient'];
				$model -> where($where) -> save($data);
			}
		}
		
		if (isset($_POST['channel_show'])) {
			$where = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'target_type' => CHANNEL_SHOW_TYPE,
			);
			$filter = $model->where($where)->select();

			if (!$filter) {
				$map = $where;
				$map['addtime'] = time();
				$map['target_value'] = 1;
				$map['filter_type'] = $_POST['channel_show'];
				$model->add($map);
			} else {
				$data = array();
				$data['filter_type'] = $_POST['channel_show'];
				$model -> where($where) -> save($data);
			}
		}
		//by zhang
		if (isset($_POST['channel_user'])) {
			$where = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'target_type' => CHANNEL_SHOW_CONTROL,
			);
			$filter = $model->where($where)->select();

			if (!$filter) {
				$map = $where;
				$map['addtime'] = time();
				$map['target_value'] = 1;
				$map['filter_type'] = $_POST['channel_user'];
				$model->add($map);
			} else {
				$data = array();
				$data['filter_type'] = $_POST['channel_user'];
				$model -> where($where) -> save($data);
			}
		}

		if (!empty($_POST['cid'])) {
			$zh_source['source_type']=USER_FILTER_TYPE;
			$zh_source['source_value']=$admin_id;
			$zh_source['filter_type']=2;
			$zh_source['target_type']=CHANNEL_FILTER_TYPE;
			$black_list = $model
				->table('sj_admin_filter')
				->where($zh_source)
				->field('target_value')
				->select();

			$black_cid = array();
			if ($black_list) {
				foreach ($black_list as $black) {
					$black_cid[] = $black['target_value'];
				}

			}

			$delete_cid = array_diff($black_cid, $_POST['cid']);
			$new_cid = array_diff($_POST['cid'], $black_cid);

			if(!empty($delete_cid)) {
				$map = array(
					'source_type' => USER_FILTER_TYPE,
					'source_value' => $admin_id,
					'target_type' => CHANNEL_FILTER_TYPE,
					'target_value' => array('in', array_values($delete_cid)),
					'filter_type' => 2,
				);
				$model->where($map)->delete();
				foreach($delete_cid as $val) {
					$log2 .= "取消了“{$_POST['cid_name'][$val]}”<br />";
				}
				
			}

			$map = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'filter_type' => 2,
				'addtime' => time(),
			);
			
			$map['target_type'] = CHANNEL_SHOW_CONTROL_BY_CATEGORY;
			$maps = $map;
			unset($maps['addtime']);
			$model->where($maps)->delete();
			if (isset($_POST['category_id']) && !empty($_POST['category_id'])){
				foreach ($_POST['category_id'] as $row){
					$map['target_value'] = $row;
					$model->add($map);		// 添加分类
				}
			}
			$map['target_type'] = CHANNEL_FILTER_TYPE;
			foreach ($new_cid as $cid) {
				$map['target_value'] = $cid;
				$model->add($map);

				$log2 .= "选中了“{$_POST['cid_name'][$cid]}”<br />";
			}
			if($log2) {
				$log2 = ($log ? '<br />' : '')."对渠道设置进行了修改：<br />".$log2;
			}
		} else {
			$map = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $admin_id,
				'target_type' => CHANNEL_FILTER_TYPE,
				'filter_type' => 2,
			);
			$model->where($map)->delete();
		}
		//写日志
		$this->writelog($log.$log2,'sj_admin_filter',$admin_id,__ACTION__ ,"","edit");
		//修改用户最后更新时间
		$admin_users = M('admin_users');
		$data['admin_user_id']= $admin_id;
		$data['update_time']  = time();
		$admin_users->save($data);
		$this->success('成功');
	}
	
	public function listBadFilter()
	{
		$filterDesc = array(
			1 => 'IP',
			2 => 'IMEI',
			3 => '用户名',
			4 => 'soft id',
		);
		$model = M('admin_filter');
		$where = array();
		if (isset($_GET['s_type'])) {
			$where['source_type'] = $_GET['s_type'];
		}
		
		if (isset($_GET['s_value'])) {
			$where['source_value'] = $_GET['s_value'];
		}
		
		if (isset($_GET['f_value'])) {
			$where['filter_type'] = $_GET['f_value'];
		}
				
		$black_list = $model
			->table('sj_bad_filter')
			->where($where)
			->select();
		$this->assign('black_list', $black_list);	
		$this->assign('filterDesc', $filterDesc);	
		$this->display();
	}
	public function addBadFilter()
	{
		$model = M('bad_filter');
		if (!empty($_POST)) {
			$map = array();

			$map['source_type'] = $_POST['source_type'];
			$map['source_value'] = $_POST['source_value'];
			$map['limit_time'] = $_POST['limit_time'];
			$map['addtime'] = time();
			if ($map['limit_time'] == 1) {
				$map['begintime'] = strtotime($_POST['begintime'] . ' 00:00:00');
				$map['endtime'] = strtotime($_POST['endtime'] . ' 23:59:59');
			}
			if (!empty($_POST['id'])) {
				$map['id'] = $_POST['id'];
				$log_result = $this->logcheck(array('id'=>$_POST['id']),'sj_bad_filter',$map,$model);
				$model->save($map);
				$this->writelog('修改id['.$_POST['id'].']的评论屏蔽', 'sj_bad_filter', $_POST['id'],__ACTION__ ,"","edit");
			} else {
				$log_result = $this->logcheck(array('id'=>$_POST['id']),'sj_bad_filter',$map,$model);
				$id = $model->add($map);
				$this->writelog('添加id['.$id.']的评论屏蔽', 'sj_bad_filter', $id,__ACTION__ ,"","add");
			}
			
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Filter/listBadFilter');
			$this->success('成功');
		} else {
			$this->display();
		}
	}	
	public function delBadFilter()
	{
		$model = M('bad_filter');
		$map = array(
			'id' => $_GET['id'],
		);
		
		$model->where($map)->delete();
		$this->writelog('id['.$_GET['id'].']的评论屏蔽', 'sj_bad_filter', $_GET['id'],__ACTION__ ,"","add");
        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Filter/listBadFilter');
		$this->success('删除成功');
	}

	

	public function topFilter()
	{
		$model = M('top_filter');
		$topFilterList = $model->where('status = 1')->select();
		foreach ($topFilterList as $k => $filter){
			$areas = explode(',', $filter['filter_area']);
			$topFilterList[$k]['filter_area'] = '';
			foreach ($areas as $key => $area) {
				$topFilterList[$k]['filter_area'] .= $this->topFilterMap[$area].',';
			}
			$topFilterList[$k]['filter_area'] = substr($topFilterList[$k]['filter_area'],0, -1);
			
			$soft = $model->table('sj_soft')->where("package='{$filter['package']}'")->getField('softname');
			$topFilterList[$k]['softname'] = $soft;
		}
		$this->assign('all_filter', $topFilterList);
		$this->assign('filter_area', $this->topFilterMap);
		$this->display();
	}

	public function addTopFilter()
	{
		$model = M('top_filter');
		if (!empty($_POST)) {
			$map = array();
			$map['package'] = $_POST['package'];
			$map['filter_area'] = implode(',', $_POST['filter_area']);
			$AdSearch = D("Sj.AdSearch");
			$error=$AdSearch->check_ad_old($_POST['package']);
			if(isset($error) && !empty($error)){
				exit($error);	
			}
			$map['uptime'] = time();
			$res = $model->add($map);
			$this->writelog('软件屏蔽列表：添加了package为['.$map['package'].']软件屏蔽了'.$map['filter_area'].'', 'sj_top_filter',$res,__ACTION__ ,'','add');
			exit('操作成功');
			// $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Filter/topFilter');
			// $this->success('成功');
		} else {
			$this->assign('filter_area', $this->topFilterMap);
			$this->display();
		}
	}

	public function editTopFilter()
	{
		$model = M('top_filter');
		if (!empty($_POST)) {
			$where = array(
				'id' => $_POST['id'],
			);
			$map = array();
			$map['filter_area'] = implode(',', $_POST['filter_area']);
			$AdSearch = D("Sj.AdSearch");
			$error=$AdSearch->check_ad_old($_POST['package']);
			if(isset($error) && !empty($error)){
				exit($error);	
			}
			$map['uptime'] = time();
			$model->where($where)->save($map);
			$this->writelog('软件屏蔽列表：修改了package为['.$_POST['package'].']软件屏蔽了'.$map['filter_area'].'', 'sj_top_filter',$_POST['id'],__ACTION__ ,'','edit');
			exit('操作成功');
			// $this->success('成功');
		} else {
			// $result = $model->table('sj_top_filter A LEFT JOIN sj_soft B ON A.package=B.package')->where('B.status=1 AND B.hide=1 AND A.id='. $_GET['id'])->field('A.*, B.softname')->select();
			$result = $model->table('sj_top_filter')->where('id='. $_GET['id'])->field('*')->select();
			$topFilter = $result[0];
			$topFilter['filter_area'] = explode(',', $topFilter['filter_area']);
			$this->assign('filter', $topFilter);
			$this->assign('filter_area', $this->topFilterMap);
			$this->display();
		}

	}

	public function delTopFilter()
	{
		$model = M('top_filter');
		$map = array(
			'id' => $_GET['id'],
		);
		$info = $model->where($map)->field('package')->find();
		
        $data = array(
           'status' => 0
        );
		$model->where($map)->save($data);
		$this->writelog('删除了软件屏蔽列表：package为['.$info['package'].']的软件屏蔽', 'sj_top_filter',$_GET['id'],__ACTION__ ,'','del');
        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Filter/topFilter');
		$this->success('删除成功');
	}
	//运营管理__市场运营基础配置__软件屏蔽列表
	public function soft_filter_list(){
		$model = D('Admin.Filter');
		$where = array(
			'status' => 1
		);
		// 如果有搜索条件
        if ($_GET['search_package']) {
        	$search_package = $_GET['search_package'];
            $where['package'] = $search_package;
            $this->assign('search_package', $search_package);
        }		
		list($list,$total,$page,$module) = $model->get_filter_list($where);
		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);	
		$this -> assign('module', $module);	
		//获取模块配置
		$this -> assign('module_conf', $model -> get_module_conf());			
		$this -> display();
	}
	//屏蔽列表-new校验广告排期
	function filter_check_ad(){
		//屏蔽软件上排期时报警需求 新增  yuesai
	    $AdSearch = D("Sj.AdSearch");
	    $shield_error=array();
	    $error="";
	    foreach($_POST['filter_module'] as $val){
			if($val){
				$start_tm =  strtotime($_POST[$val.'_begintime']);
				$end_tm =  strtotime($_POST[$val.'_endtime']);
				if($end_tm < $start_tm){
					$this ->error("结束时间不能小于开始时间");
				}else{
					$error=$AdSearch->check_ad($_POST['package'],$start_tm,$end_tm);
					if($error && !in_array($error, $shield_error)){
					    $shield_error[]=$error;
					}
				}
			}
		}
		if(count($shield_error))
	        $this -> error(implode("",$shield_error));
	}
	//运营管理__市场运营基础配置__软件屏蔽列表__添加
	public function soft_filter_add(){
		$model = D('Admin.Filter');
		if($_POST){
			if(!$_POST['package']) $this ->error('包名不可为空');
			$this->filter_check_ad();
			$ret = $model -> add_filter_soft();
			$this->assign("jumpUrl","/index.php/Admin/Filter/soft_filter_list");
			if($ret['code'] == 1){
				$this->writelog("屏蔽列表new【添加】了包名为{$_POST['package']}的过滤软件", 'sj_soft_filter',$_POST['package'],__ACTION__ ,'','add');
				$this ->success($ret['msg']);
			}else{
				$this ->error($ret['msg']);
			} 
		}else{
			if(isset($_GET['pkg'])){
				$res = $model -> check_package();
				exit(json_encode(array('code'=>$res['code'],'msg'=>$res['msg'])));
			}
			$this->assign('filter_module', $model->get_module_conf());
			$this->assign('package','');
			$this -> display();
		}
	}
	//运营管理__市场运营基础配置__软件屏蔽列表__编辑
	public function soft_filter_edit(){
		$model = D('Admin.Filter');
		if($_POST){
			if(!$_POST['package']) $this ->error('包名不可为空');
            $this->filter_check_ad();
			$ret = $model -> edit_filter_soft();
			$this->assign("jumpUrl",$_SERVER['HTTP_REFERER']);
			if($ret['code'] == 1){
				$this->writelog("屏蔽列表new【编辑】了包名为{$_POST['package']}的过滤软件".$ret['log'], 'sj_soft_filter',$_POST['package'],__ACTION__ ,'','edit');
				$this ->success($ret['msg']);
			}else{
				$this ->error($ret['msg']);
			} 
		}else{
			$res = $model -> get_pkg_data();
			$this->assign('package',$_GET['pkg']);
			$this->assign('list',$res);
			$this->assign('filter_module', $model->get_module_conf());
			$this->display('Admin:Filter:soft_filter_add'); 
		}		
	}
	//运营管理__市场运营基础配置__软件屏蔽列表__删除
	public function soft_filter_del(){
		$model = new Model();	
		$pkg = trim($_GET['pkg']);
		if(!$pkg) $this ->error("请选择有效软件！");
		$where = array('package' => $pkg);
		$map = array('status' => 0,'update_tm' => time());
		$res = $model -> table('sj_soft_filter') -> where($where) -> save($map);
		$this->assign("jumpUrl",$_SERVER['HTTP_REFERER']);
		if($res){
			$this->writelog("屏蔽列表new【删除】了包名为{$pkg}的过滤软件", 'sj_soft_filter',$pkg,__ACTION__ ,'','del');
			$this ->success('删除成功');
		}else{
			$this ->error('删除失败');
		}
	}
}
