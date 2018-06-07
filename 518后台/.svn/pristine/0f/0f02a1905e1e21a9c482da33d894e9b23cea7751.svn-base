<?php
class LogStaticAction extends CommonAction {
	function log_static_list(){
		$category=M("admin_log_category");
		$internal=M("admin_log_nodemap");
		$log_user=M("admin_log_usermap");
		$log_statistic=M("admin_log_statistic");
		$model=M();
		$user_log=array();

		$res=$category->where(array("status"=>1))->findAll();
		
		$category_result = array();
		$catrgory_map = array();
		foreach ($res as $row) {
			# code...
			$pid = $row['parentid'];
			$id = $row['id'];
			!isset($category_result[$pid]) && $category_result[$pid] = array();
			$category_result[$pid][$id] = $row;
			$catrgory_map[$id] = $row;
		}
		$this->assign("category",$category_result[0]);
		$where = array(
			'status' => 1
		);
		$res = $log_user->where($where)->group('userid')->field('userid,name')->select();
		if (isset($_GET['cid'])) {
			$cids = array($_GET['cid']);
			if ($category_result[$_GET['cid']]) {
				$cids = array_merge($cids, array_keys($category_result[$_GET['cid']]));
			}
			$where['cid'] = array('in', $cids);
		}

		$users = array();
		foreach ($res as $val) {
			$users[$val['userid']] = array(
				'data' => array(),
				'name' => $val['name'],
				'userid' => $val['userid'],
			);
		}
		if (!isset($_GET['fromdate']) && !isset($_GET['todate'])) {
			$day = strtotime(date("Y-m-d",time()));
			$_GET['fromdate']=date("Y-m-d H:i:s",$day-7*3600*24);
			$_GET['todate']=date("Y-m-d H:i:s",$day-3600*24+86399);
		}

		$this->check_range_where($where, 'fromdate', 'todate', 'create_at', true);

		$logs = $log_statistic->where($where)->group('userid,mid,cid')->field('sum(cnt) as cnt,userid,mid,cid')->select();
		$total_result = array();
		foreach ($logs as $val) {
			# code...
			$userid = $val['userid'];
			$top_id = $cid = $val['cid'];


			if ($catrgory_map[$cid]['parentid']>0) {
				$top_id = $catrgory_map[$cid]['parentid'];
			}
			if (!isset($users[$userid])) continue;
			!isset($users[$userid]['data'][$top_id]) && $users[$userid]['data'][$top_id] = 0;
			!isset($total_result[$top_id]) && $total_result[$top_id] = 0;
			$users[$userid]['data'][$top_id] += $val['cnt'];
			$total_result[$top_id] += $val['cnt'];
		}
		$this->assign("users",$users);
		$this->assign("total_result",$total_result);
		$this->display();
	}

	function log_static_list_sub(){
		$category=M("admin_log_category");
		$log_nodemap=M("admin_log_nodemap");
		$log_user=M("admin_log_usermap");
		$log_statistic=M("admin_log_statistic");
		$model=M();
		$user_log=array();
		$where = array();
		
		$res=$category->where(array("status"=>1))->findAll();
		$category_result = array();
		$catrgory_map = array();
		foreach ($res as $row) {
			# code...
			$pid = $row['parentid'];
			$id = $row['id'];
			!isset($category_result[$pid]) && $category_result[$pid] = array();
			$category_result[$pid][$id] = $row;
			$catrgory_map[$id] = $row;
		}
		$where = array(
			'status' => 1
		);
		$cids = array($_GET['cid']);
		if ($category_result[$_GET['cid']]) {
			$cids = array_merge($cids, array_keys($category_result[$_GET['cid']]));
		}
		$nodemap_where = $where;
		$this->check_where($where, 'userid');
		$res = $log_user->where($where)->group('userid')->field('userid,name')->select();
		$users = array();
		foreach ($res as $val) {
			$users[$val['userid']] = array(
				'data' => array(),
				'name' => $val['name'],
				'userid' => $val['userid']
			);
		}
		$where['cid'] = array('in', $cids);
		$res = $log_nodemap->where($nodemap_where)->select();
		$node_map = array();
		$nodes = array();
		$complex_row = false;
		foreach ($res as $val) {
			$cid = $val['cid'];
			!isset($node_map[$cid]) && $node_map[$cid] = array();
			$node_map[$cid][] = $val;
			$nodes[$val['id']] = $val;
			if (count($node_map[$cid])>1) {
				$complex_row = true;
			}
		}
		$category_tpl = $category_result[$_GET['cid']];
		//$category_tpl[$_GET['cid']] = $catrgory_map[$_GET['cid']];
	
		foreach ($category_tpl as $key => $value) {
			# code...
			$category_tpl[$key]['rowspan'] = '';
			$category_tpl[$key]['colspan'] = '';
			$category_tpl[$key]['total'] = 0;
			$category_tpl[$key]['display'] = $value['name'].'小计';
			if (count($node_map[$key])>=1) {
				$category_tpl[$key]['colspan'] = ' colspan="'. count($node_map[$key]). '"';
			} elseif ($complex_row) {
				$category_tpl[$key]['rowspan'] = ' rowspan="2"';
			}
			$category_tpl[$key]['sub'] = $node_map[$key];
		}
		if (isset($node_map[$_GET['cid']])) {
			foreach ($node_map[$_GET['cid']] as $key => $value) {
				$mid = $value['id'];
				$cid = $value['cid'];

				$k = $cid. '_'. $mid;
				$category_tpl[$k]['rowspan'] = '';
				$category_tpl[$k]['colspan'] = '';
				$category_tpl[$k]['total'] = 0;
				$category_tpl[$k]['display'] = $nodes[$mid]['name'];
				if ($complex_row) $category_tpl[$k]['rowspan'] = ' rowspan="2"';

				$category_tpl[$k]['sub'] = array($nodes[$mid]);
			}

		}
		$this->assign("complex_row", $complex_row);
		if (!isset($_GET['fromdate']) && !isset($_GET['todate'])) {
			$_GET['fromdate']=date("Y-m-d",strtotime(date("Y-m-d",time()))-7*3600*24);
			$_GET['todate']=date("Y-m-d",time()-3600*24);
		}

		$this->check_range_where($where, 'fromdate', 'todate', 'create_at', true);
		
		$logs = $log_statistic->where($where)->group('userid,mid')->field('sum(cnt) as cnt,userid,mid,cid')->select();
		$total_result = array();
		foreach ($logs as $val) {
			# code...
			$userid = $val['userid'];
			if (!isset($users[$userid])) continue;
			$mid = $val['mid'];
			$cid = $val['cid'];
			if (isset($category_tpl[$cid])){
				$category_tpl[$cid]['total'] += $val['cnt'];	
			}
			!isset($users[$userid]['data'][$mid]) && $users[$userid]['data'][$mid] = 0;
			!isset($total_result[$mid]) && $total_result[$mid] = 0;
			$users[$userid]['data'][$mid] += $val['cnt'];
			$total_result[$mid] += $val['cnt'];
		}
		ksort($total_result);
		$this->assign("total_result",$total_result);
       	$this->assign("category",$category_tpl);
		$this->assign("users",$users);
		$this->assign("cid", $_GET['cid']);
		$this->assign('catrgory_info',$catrgory_map[$_GET['cid']]);
		$this->display();
	}

	function log_static_view()
	{
	    $action_list = array (
			'SDK测试' =>'/index.php/Dev/SoftwareReview/sdk_pass,/index.php/Dev/SoftwareReview/sdk_reject',
			'SDK测试未通过列表'=>'/index.php/Dev/SoftwareReview/sdk_cancel,/index.php/Dev/SoftwareReview/del_sdk',
			'未通过列表'=>'/index.php/Dev/SoftwareReview/newsoft_cancel,/index.php/Dev/SoftwareReview/del_status_tmp',
			'新软件审核' => '/index.php/Dev/SoftwareReview/newsoft_pass,/index.php/Dev/SoftwareReview/newsoft_down,/index.php/Dev/Soft/shield_soft_do:newsoft_shield',
			'修改描述审核' => '/index.php/Dev/SoftwareReview/describe_pass,/index.php/Dev/SoftwareReview/edit_down,/index.php/Dev/Soft/shield_soft_do:edit_audit_shield',
			'版本升级审核' => '/index.php/Dev/SoftwareReview/updatesoft_pass,/index.php/Dev/SoftwareReview/update_down,/index.php/Dev/Soft/shield_soft_do:softupgrade_shield',
			'申请下架审核' => '/index.php/Dev/SoftwareReview/undercarriage_pass,/index.php/Dev/SoftwareReview/nextframe_down',
			'定时上架列表'=>'/index.php/Dev/SoftwareReview/shelves_dropped',
			'未通过撤销' => '/index.php/Dev/SoftwareReview/newsoft_cancel',
			'软件认领' => '/index.php/Dev/Claim/do_pass,/index.php/Dev/Claim/do_reject,/index.php/Dev/Claim/do_back,/index.php/Dev/Claim/do_del',
			'举报申诉列表' => '/index.php/Dev/Claim/report_reply,/index.php/Dev/Claim/report_del',
			'开发者审核' => '/index.php/Dev/User/auditforuser_confirm:0,/index.php/Dev/User/auditforuser_confirm:-1,/index.php/Dev/User/auditforuser_confirm:1,/index.php/Dev/User/denyuser',
			'开发者反馈' => '/index.php/Dev/InformationManagement/reply_add,/index.php/Dev/InformationManagement/feedback_del,/index.php/Dev/InformationManagement/handle_status',	
			'已上架' => '/index.php/Dev/Soft/undercarriage,/index.php/Dev/Apk/confirm:mod_line,/index.php/Dev/Apk/confirm:update,/index.php/Dev/Apk/confirm:update_apk,/index.php/Dev/Soft/update_claim,/index.php/Dev/Soft/setOfficial,/index.php/Dev/Authentication/passTvCheck,/index.php/Dev/Soft/shield_soft_do:soft_shield,/index.php/Dev/Soft/newsoft_dropped',
			'已下架' => '/index.php/Dev/Soft/shelves,/index.php/Dev/Soft/shield_soft_do:undercarriage_shield',
			'软件反馈'=>'/index.php/Dev/Message/feedback_reback,/index.php/Dev/Message/deleteFeedback:feedback,/index.php/Dev/Message/department',
			'发布软件'=>'/index.php/Dev/Apk/add_new,/index.php/Dev/Apk/confirm:new',
			'采集待审核'=>'/index.php/Dev/Apk/confirm:cj_add,/index.php/Dev/Apk/confirm:cj_update',
			'软件举报' => '/index.php/Dev/Message/deleteSoftFeedback,/index.php/Dev/Message/deleteFeedback:report',
			'软件评论' => '/index.php/Dev/Message/addBadFilter,/index.php/Dev/Message/delete,/index.php/Dev/Message/showoff,/index.php/Dev/Message/editBadFilter,/index.php/Dev/Message/delBadFilter,/index.php/Dev/Message/addCommentBlock,/index.php/Dev/Message/Comment_reply_do',
			'开发者通过列表' => '/index.php/Dev/User/denyuser_do',
			'安智提醒' => '/index.php/Dev/Anzhiremind/send_remind_add,/index.php/Dev/Anzhiremind/remind_del',
			'平台动态' => '/index.php/Dev/Information/information_add_submit,/index.php/Dev/Information/information_stop,/index.php/Dev/Information/information_start,/index.php/Dev/Information/information_del',
			'盗版软件' => '/index.php/Dev/SoftPiracyWarning/DeletePiracy,/index.php/Dev/SoftPiracyWarning/EditPiracy',
			'运营白名单'=>'/index.php/Dev/SoftWhitelist/EditWhitelist,/index.php/Dev/SoftWhitelist/DeleteWhite,/index.php/Dev/SoftWhitelist/feedback_authority',
			'刷量白名单'=>'/index.php/Dev/DownloadBrush/brush_white_list,/index.php/Dev/DownloadBrush/brush_white_add_do,/index.php/Dev/DownloadBrush/brush_white_edit,/index.php/Dev/DownloadBrush/brush_white_edit_do,/index.php/Dev/DownloadBrush/brush_white_del,/index.php/Dev/DownloadBrush/import_whitelist',
			'盗版白名单' => '/index.php/Dev/SoftPiracyWarning/DeletePiracy,/index.php/Dev/SoftPiracyWarning/EditPiracy',
			'刷量操作'=>'/index.php/Dev/DownloadBrush/brush_oper,/index.php/Dev/DownloadBrush/import_data_oper',
			'刷量配置规则'=>'/index.php/Dev/DownloadBrush/brush_config_add,/index.php/Dev/DownloadBrush/brush_config_oper',
			'申请首发管理'=>'/index.php/Dev/ApplyDebut/debut_oper,/index.php/Dev/ApplyDebut/admin_remark',
			'申请闪屏管理'=>'/index.php/Dev/ApplyScreen/screen_oper,/index.php/Dev/ApplyScreen/admin_remark',
			'软件反馈分部门配置'=>'/index.php/Dev/FeedbackFilter/addfilter,/index.php/Dev/FeedbackFilter/editfilter,/index.php/Dev/FeedbackFilter/del_filter',
			'添加子账号'=>'/index.php/Dev/User/edit_son_authority,/index.php/Dev/User/show_sonuser,/index.php/Dev/User/edit_son,/index.php/Dev/User/del_son,/index.php/Dev/User/verify_son_email',
			'采集忽略'=>'/index.php/Caiji/Collection/update_ignored:update,/index.php/Caiji/Collection/update_ignored:add',
			'头像昵称管理'=>'/index.php/Dev/Message/comment_pictures,/index.php/Dev/Message/del_comment_pictures',
			'合作站点管理'=>'/index.php/Cooperate/Contentcooperation/save_chl_pkg:update'
		);
		$action_list = $this->nodename2id($action_list);
	    $action_map = array();
	    foreach ($action_list as $key => $value) {
	    	# code...
	    	$tmp = explode(',', $value);
	    	foreach ($tmp as $v) {
	    		# code...
	    		$action_map[$v] = $key;
	    	}
	    }
	    $this->assign('action_map', $action_map);
	    $this->assign('action_list', $action_list);

		import("@.ORG.Page2");
		$log_nodemap=M("admin_log_nodemap");
		$log_model=M("admin_log_new");
		$user_model=M("admin_users");
		$where = array();
		if (!empty($_GET['action_id'])) {
			if (!strstr($_GET['action_id'], ':')) {
				$where['action_id'] = array('in', $_GET['action_id']);
			} else {
				$tmp = explode(',', $_GET['action_id']);
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
			$this->assign('action_id', $_GET['action_id']);
		}
		if(isset($_GET['logtime'])){
			$where['logtime'] = array('EQ', $_GET['logtime']);
		}
		$this->check_where($where, 'actionexp', 'isset', 'like');
		$this->check_where($where, 'admin_id');
		$this->check_range_where($where, 'fromdate', 'todate', 'logtime', true);
    	$where_t = array();
		$this->check_other_table_where($where, $where_t, 'admin_user_name', array('admin_id', 'admin_user_id'), 'sj_admin_users', 'isset');
		$count = $log_model->where($where)->count();
		$Page=new Page($count, 15);
		$list = $log_model->where($where)
			->limit($Page->firstRow.','.$Page->listRows)
			->order('logtime desc')
			->select();
		//echo $log_model->getLastSql();
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
		$this->assign('page', $Page->show());
		$this->assign('list', $list);
		$this->assign('count', $count);
		$this->assign('admin_info', $admin_info);
		$this->display();	
	}
	
	function nodename2id($action_list)
	{
		$model = new Model();
		$nodes = '';
		$i = 0;
		foreach ($action_list as $val){
			if ($i > 0) $nodes .= ',';
			$nodes .= $val;
			$i++;
		}
		$nodes = str_replace(',', "','", $nodes);
		$nodes = preg_replace('/:[a-z_0-9]+/', '', $nodes);

		$sql = "select node_id, nodename from sj_admin_node where nodename in ('{$nodes}')";
		$res = $model->query($sql);
		$node_map = array();
		foreach($res as $row) {
			$node_map[$row['nodename']] = $row['node_id'];
		}

		$action_list_new = $action_list;

		foreach ($action_list_new as $k => $v){
			$tmp = explode(',', $v);
			if (is_array($tmp)) {
				$i = 0;
				$str = '';
				foreach ($tmp as $val) {
					if ($i > 0) $str .= ',';
					$t = explode(':', $val);
					if (count($t)>1) {
						$str .= $node_map[$t[0]].':'.$t[1];
					} else {
						$str .= $node_map[$val];
					}
					$i++;
				}
				$action_list_new[$k] = $str;
			} else {
				$action_list_new[$k] = $node_map[$v];
			}
		//var_dump($k);
		}
		return $action_list_new;
	}
	
	
	function dev_log_static_view()
	{
        $action_list = array(
            '新软件发布' => '2',
            '修改描述' => '3',
            '版本升级' => '5',
            '已上架' => '101',
            '草稿箱' => '102,103,105,151',
            '认领举报' => '150',
            '登录' => '152',
            '未通过' => '157',
            '修改个人资料' => '153',
            '修改密码' => '154',
            '反馈建议' => '155,156',
            '申请首发' => '170,171',
            '申请闪屏' => '172,173',
            '运营合作' => '174,175',
            '礼包发布' => '177,178,179',
            '新服发布' =>'180,181,182',
            '子账户'   =>'185,186,187,188',
            '软件评论' =>'183,184',
            'SDK申请' =>'189,190,191',
			'礼券申请' =>'197',
        );
	    $action_map = array();
	    foreach ($action_list as $key => $value) {
	    	# code...
	    	$tmp = explode(',', $value);
	    	foreach ($tmp as $v) {
	    		# code...
	    		$action_map[$v] = $key;
	    	}
	    }


		import("@.ORG.Page2");
		$log_model = new Model();

		$where = array();
		if (!empty($_GET['action_id'])) {
			$where['action_id'] = array('in', $_GET['action_id']);
			$this->assign('action_id', $_GET['action_id']);
		}
		if (!empty($_GET['fromip'])) {
		    $where['fromip'] = trim($_GET['fromip']);
		    $this->assign('fromip', $_GET['fromip']);
		}
		if(!empty($_GET['time'])){
			$_GET['fromdate'] = date("Y-m-d 00:00:00",$_GET['time']);
			$_GET['todate'] =   date("Y-m-d 23:59:59",$_GET['time']);
			$this->check_where($where,'fromip');
			$where['action_id'] = array('in','2,3,5');
		}
		$this->check_where($where, 'actionexp', 'isset', 'like');
		$this->check_range_where($where, 'fromdate', 'todate', 'logtime', true);
    	$where_t = array();
		$this->check_other_table_where($where, $where_t, 'dev_name',array('user_id','dev_id'), 'pu_developer', 'isset');
		$count = $log_model->table('pu_dev_log')->where($where)->count();
		$Page=new Page($count, 15);
		$list = $log_model->table('pu_dev_log')
			->where($where)
			->limit($Page->firstRow.','.$Page->listRows)
			->order('logtime desc')
			->select();
		//echo $log_model->getLastSql();
		$dev_ids = array();
		foreach ($list as $val) {
			# code...
			$dev_ids[] = $val['user_id'];
		}
		$where = array(
			'dev_id' => array('in', $dev_ids),
		);
		$devs = $log_model->table('pu_developer')->where($where)->select();
		
		$dev_info = array();
		foreach ($devs as $val) {
			# code...
			$dev_id = $val['dev_id'];
			$dev_info[$dev_id] = $val;
		}
		$this->assign('page', $Page->show());
		$this->assign('count', $count);
		$this->assign('list', $list);
		$this->assign('dev_info', $dev_info);
		$this->assign('action_list', $action_list);
		$this->assign('action_map', $action_map);
		$this->display();
	}
	//添加用户名
	function add_user(){
		$user=M("admin_users");
		$log_user=M("admin_log_usermap");
		if($_POST){
			$data['name']=trim($_POST['username']);
			if(empty($data['name'])){
				$this->error("输入不能为空");
			}
			$user_result=$user->where(array("admin_state"=>1,"admin_user_name"=>$data['name']))->find();
			if(empty($user_result)){
				$this->error("请正确填写用户，此用户不存在用户表");
			}else{
				$data['userid']=$user_result['admin_user_id'];
			}
			$data['cid']=$_POST['type'];
			$fromdate=$_POST['fromdate'];
			$todate=$_POST['todate'];
			$data['status']=1;
			$data['create_tm']=time();
			$data['update_tm']=time();
			if($id=$log_user->add($data)){
				$log_str = "增加了用户名称为[{$data['name']}]";
				if(isset($_POST['type'])) $log_str .= "分类为{$data['cid']}的用户信息";
				$this->writelog($log_str, 'sj_admin_log_usermap', $id,__ACTION__ ,"","add");
				$this->assign("jumpUrl","/index.php/".GROUP_NAME."/LogStatic/log_static_list/zh_type/{$data['cid']}/fromdate/{$fromdate}/todate/{$todate}");
				$this->success("添加用户成功！");
			}else{
				$this->error("添加用户失败");
			}
		}
		$zh_type=$_GET['zh_type'];
		$fromdate=$_GET['fromdate'];
		$todate=$_GET['todate'];
		$this->assign("zh_type",$zh_type);
		$this->assign("fromdate",$fromdate);
		$this->assign("todate",$todate);
		$this->display();
	}

	function del_user(){
    	$user=M("admin_log_usermap");
    	$data = array(
    		'status' => 0
    	);
    	$id = '';
    	if (isset($_GET['id'])) {
	    	$where = array(
	    		'userid' => $_GET['id']
	    	);
	    	$id = $_GET['id'];
    	} else if (isset($_GET['ids'])) {
	    	$where = array(
	    		'userid' => array('IN', explode(',', $_GET['ids']))
	    	);
	    	$id = $_GET['ids'];
    	}
    	$user->where($where)->save($data);
    	$this->writelog('对id['.$id.']的操作人员进行删除', 'sj_admin_log_usermap', $id,__ACTION__ ,"","del");
    	$this->success('删除成功');
	}

	function pub_find_user(){
    	$user_model=M("admin_users");

    	$where = array();
    	$t = array();
    	$t['admin_user_name'] = array('like', '%'. $_GET['name'].'%');
    	$t['user_name_py'] = array('like', '%'. $_GET['name'].'%');
    	$t['_logic'] = 'or';
    	$where['_complex'] = $t;

    	$d = $user_model->where($where)->select();
    	echo $user_model->getLastSql();
    	//echo 
    	var_dump($d);
	}

	public function pub_show_admin()
	{
		$model = new Model();
		$keyword = $_GET['query'];
		$real_keyword = escape_string($keyword);
		$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$sql = "select * from sj_admin_users where user_name_py like '{$keyword}%';";
		$softs = $model->query($sql);

		$data = array(
			'query' => $keyword,
			'suggestions' => array(),
			'data' => array(),
		);
		foreach($softs as $v) {
			$data['suggestions'][] = $v['admin_user_name'];
			$data['data'][] = $v['admin_user_id'];
		}

		exit(json_encode($data));
	}
	//客服已处理反馈日报
	function processed_feedback_daily(){
		$model = D("Dev.Log");
		$time = time();
		$where = array(
			'status' => 1
		);			
		$this->check_where($where, 'admin_id', 'isset');
		$this->check_where($where, 'shift', 'isset');
		if(empty($_GET['fromdate']) && empty($_GET['todate'])){
			$_GET['fromdate'] = date('Y-m-d 00:00:00',$time);
			$_GET['todate'] = date('Y-m-d 23:59:59',$time);
		}
		$this->check_range_where($where, 'fromdate', 'todate', 'add_tm', true);
		list($list,$adminname,$c_name,$total,$Page) = $model -> get_processed_daily($where);
		$this -> assign('list',$list);		
		$this -> assign('page', $Page->show());
		$this -> assign('total', $total);		
		$this -> assign('adminname',$adminname);	
		$this -> assign('c_name',$c_name);	
		$param = http_build_query($_GET);
		$this -> assign('param',$param);		
		$this -> display();
	}
	//客服已处理反馈日报__编辑
	function processed_feedback_daily_save(){
		$model = D("Dev.Log");
		if($_POST){
			$where = array(
				'id' => $_POST['id']
			);
			$map = array(
				'shift' =>$_POST['shift'],
				'remark' => $_POST['remark'],
			);
			$ret = $model -> table('sj_processed_daily')->where($where)->save($map);
			if($ret){
				$msg = "编辑了id为{$_POST['id']}的客服已处理反馈日报,编辑内容：把班次编辑成：{$_POST['shift']}(1白天2晚班)，备注编辑成：{$_POST['remark']}";
				$this->writelog($msg, 'sj_processed_daily',$_POST['id'],__ACTION__ ,"","edit");
				$this->success('操作成功');
			}else{
				$this -> error("操作失败");
			}
		}else{
			$id = $_GET['id'];
			$list = $model -> get_daily_find($id);
			$this -> assign('list',$list);	
			$this -> display();
		}
	}
	//客服已处理反馈日报__删除
	function processed_feedback_daily_del(){
		$model = D("Dev.Log");
		$fromdate = strtotime(urldecode($_GET['fromdate']));
		$todate = strtotime(urldecode($_GET['todate']));
		$where = array(
			'admin_id' => $_GET['admin_id'],
			'add_tm' => array('exp',">='{$fromdate}' and add_tm <= {$todate}"),
		);
		$map = array(
			'status' => 0
		);
		$res = $model->table('sj_processed_daily')->where($where)->save($map);
		if($res){
			$this->writelog("删除了客服id为{$_GET['admin_id']}时间段为{$_GET['fromdate']}到{$_GET['todate']}的客服已处理反馈日报数据", 'sj_processed_daily',$_GET['admin_id'],__ACTION__ ,"","del");
			$this->success('操作成功');
		}else{
			$this -> error("操作失败");
		}
	}
	//客服已处理反馈日报__查看备注
	function pub_remark_list(){
		$model = D("Dev.Log");
		$where = array(
			'status' => 1,
			'remark' => array('exp',"!=''")
		);			
		$this->check_where($where, 'admin_id', 'isset');		
		$this->check_range_where($where, 'fromdate', 'todate', 'add_tm', true);
		$where_c = array(
			'type' => 1,
			'status' => 1
		);		
		$subQuery = $model->table('sj_feedback_config')->where($where_c)->field('id')->buildSql(); 		
		$where['ques_id'] = array('in',$subQuery);			
		$list = $model->table('sj_processed_daily')->where($where)->order('add_tm desc')->field('add_tm,remark,day_tm')->select();
		$arr =  array();
		foreach($list as $k => $v){
			$arr[$v['day_tm']]['add_tm'] = $v['add_tm'];
			$arr[$v['day_tm']]['remark'] = $v['remark'];
		}
		unset($list);
		$this -> assign('list',$arr);	
		$this -> display('remark_list');		
	}
	//客服已处理反馈日报__导出当前数据
	function exp_daily(){
		$model = D("Dev.Log");
		$where = array(
			'status' => 1
		);			
		$this->check_where($where, 'admin_id', 'isset');
		$this->check_where($where, 'shift', 'isset');
		$this->check_range_where($where, 'fromdate', 'todate', 'add_tm', true);		
		$data = $model->get_exp_daily($where);
		exit(json_encode($data));
	}
	//添加客服
	function add_customer(){
		$model = D("Dev.Log");
		if($_POST){
			if(empty($_POST['user_518'])){
				$this -> error("518用户名不可为空");
			}
			// if(empty($_POST['user_open'])){
				// $this -> error("open用户名不可为空");
			// }
			$res = $model->bind_user();
			if($res['code'] == 1){
				$this->writelog("客服已处理反馈日报：添加了admin_id为{$res['id']}的账号", 'sj_staff_config',$res['id'],__ACTION__ ,"","add");
				$this->success($res['msg']);
			}else{
				$this->error($res['msg']);
			}
		}else{
			$this -> display();
		}
	} 
}
?>
