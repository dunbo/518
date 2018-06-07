<?php
class NecessaryExtentAction extends CommonAction {

	function NecessaryExtentAction()
	{
		//C('SHOW_RUN_TIME',false);			// 运行时间显示
		//C('SHOW_PAGE_TRACE',false);
		parent::__construct();
	}
	function index()
	{
		if (isset($_GET['act']) && method_exists($this, $_GET['act'].'Act')) {
			$act = $_GET['act'].'Act';
			$this->$act();
			exit;
		}
		
		import("@.ORG.Page");
        $param = http_build_query($_GET);
		$limit = 100;
		if(isset($_GET['lr'])){
		    $this->assign("lr",(int)$_GET['lr']);
		}else{
		    $this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}
		
		$model = M('necessary_extent');
		
		$map   = array( 'status' => 1,'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0 );
		$count_total = count($model -> where($map)->select());
		$page  = new Page($count_total, $limit, $param);
		
		$where = array(
			'parent_id' => 0,
			'status' => 1
		);
		if (isset($_GET['parent_id'])) {
			$where['parent_id'] = $_GET['parent_id'];
			$this->assign('parent_id', $_GET['parent_id']);
		}
		$now = time();
		$list = $model->where($where)->order('rank asc, type desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$channel_model = M('channel');
		$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
		$channels_key = array();
		foreach($channels as $v) {
			$channels_key[$v['cid']] = $v['chname'];
		}

		$operating_db = D('Sj.Operating');
		$operating_list = $operating_db->field('oid,mname')->select();
		$operators_key = array();
		foreach($operating_list as $v) {
			$operators_key[$v['oid']] = $v['mname'];
		}
			
		foreach ($list as $k => $v) {
			if ($v['type'] == 1) {
				$where = array(
					'extent_id' => $v['extent_id'],
					'start_at' => array('elt',$now),
					'end_at' => array('egt',$now),
					'status' => 1
				);
				$count = $model->table('sj_necessary_extent_soft')->where($where)->count();
				$list[$k]['soft_counts'] = intval($count);
			} else {
				$list[$k]['soft_counts'] = '-';
				$list[$k]['extent_size'] = '-';
			}
			$list[$k]['chname'] = isset($channels_key[$v['cid']]) ? $channels_key[$v['cid']] : '-';
			$list[$k]['mname'] = isset($operators_key[$v['oid']]) ? $operators_key[$v['oid']] : '-';
		}
		$this->assign('list', $list);
		$this->assign('isAjax', $this->isAjax());
		
		
		$page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign("page", $page->show());
		
		$this->assign('count',$count_total);
		$html = $this->fetch();
		header("Cache-control: no-store");
		header("pragma:no-cache");
		exit($html);
	}

	function add_extent()
	{
		if (!empty($_POST)){
			$model = M('necessary_extent');
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			
			isset($_POST['extent_name']) && $map['extent_name'] = $_POST['extent_name'];
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			isset($_POST['cid']) && $map['cid'] = $_POST['cid'];
			isset($_POST['channel_id']) && $map['channel_id'] = $_POST['channel_id'];
			isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			!empty($_POST['parent_id']) && $map['parent_id'] = $_POST['parent_id'];

			$rank  = (int)$_POST['rank'];
			$where = ' status = 1 ';
			if(isset($_POST['parent_id'])){
			    $where .= ' AND parent_id = '.(int)$_POST['parent_id'];
			}else{
			    $where .= ' AND parent_id = 0 ';
			}
			
			$n = $model->where("extent_name='{$_POST['extent_name']}' AND `status`=1")->count();
			if ($n){
				$this->error('区间名称已存在！');
				exit();
			}
			
			if ($id = $model->add($map)) {
			    //更新排序
		        $this->_updateRankInfo('sj_necessary_extent','rank',$id,$where,$rank);
				$this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/index');
				$this->writelog('市场软件运营推荐-必备频道软件推荐:添加了id为'.$id.'的区间', 'sj_necessary_extent',"{$id}",__ACTION__ ,"","add");
				$this->success('添加成功');
			}

		} else {
	        $channel_model = M('channel');
	        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
	        $this->assign('channel_list', $channels);

        	$operating_db = D('Sj.Operating');
        	$operating_list = $operating_db->field('oid,mname')->select();
        	$this->assign('operatinglist',$operating_list);
			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);
			
			$extent_model = M('necessary_extent');
			$map = array( 'status' => 1,'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0);
			$count = count($extent_model -> where($map)->select()) + 1;
			$this->assign('count',$count);
			$this->display();
		}
	}
	
	function edit_extent()
	{
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}
		
		$id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $id
		);
		if (!empty($_POST)){
			$model = M('necessary_extent');
			$map = array();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			
			isset($_POST['extent_name']) && $map['extent_name'] = $_POST['extent_name'];
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			isset($_POST['cid']) && $map['cid'] = $_POST['cid'];
			//isset($_POST['rank']) && $map['rank'] = $_POST['rank'];
			isset($_POST['channel_id']) && $map['channel_id'] = $_POST['channel_id'];
			isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
            
			$where_rank      = ' `status` = 1 ';
			
			if(isset($_POST['parent_id'])){
			    $where_rank .= ' AND `parent_id` = '.(int)$_POST['parent_id'];
			}else{
			    $where_rank .= ' AND `parent_id` = 0 ';
			}
			
			$rank = (int)$_POST['rank'];
			//更新排序
			$log = '';
			$log .=  $this->logcheck(array('extent_id'=>$id),'sj_necessary_extent',array('rank'=>$rank),$model);
		    $this->_updateRankInfo('sj_necessary_extent','rank',$id,$where_rank,$rank);
			$log .= $this->logcheck(array('extent_id'=>$id),'sj_necessary_extent',$map,$model);

			if ($model->where($where)->save($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/index/p/'.$_POST['p']);
//				$this->writelog('编辑了extent_id为'.$id.'的区间', 'sj_necessary_extent', $id);
				// $this -> writelog('编辑了extent_id为'.$id."的区间", 'sj_necessary_extent', '', __ACTION__, 'rank_config');
				$this->writelog("软件必备_软件必备区间管理-编辑了extent_id为$id".$log,'sj_necessary_extent',$id, __ACTION__, 'rank_config','edit');
				$this->success('编辑成功');
			}

		} else {
	        $channel_model = M('channel');
	        $channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
	        $this->assign('channel_list', $channels);

        	$operating_db = D('Sj.Operating');
        	$operating_list = $operating_db->field('oid,mname')->select();
        	$this->assign('operatinglist',$operating_list);
			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);
			
			$model = M('necessary_extent');
			$condition = array( 'status' => 1,'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0 );
			$count = count($model -> where($condition)->select());
			$this->assign('count',$count);

			$extent = $model->where($where)->find();
			$this->assign('extent', $extent);
			$this->display();
		}
	}
	
	//更新某个排序
	function edit_rank(){
	    if(isset($_GET)){
			$table       = 'sj_necessary_extent';
			$field       = 'rank';
			$where       = '`status` = 1';
			$extent_id   = (int)$_GET['extent_id'];
			$parent_id   = (int)$_GET['parent_id'];
			$target_rank = (int)$_GET['rank'];
			$lr          = isset($_GET['lr']) ? (int)$_GET['lr'] : 20;
		    $p           = isset($_GET['p'])  ? (int)$_GET['p']  : 1;
		    
			$necessary_extent = M('necessary_extent');
			$old_rank = $necessary_extent->field('rank')->where("extent_id=$extent_id")->limit(1)->select();
			$oldrank = $old_rank[0]['rank'];
		    
            if(isset($parent_id)){
			    $where  .= ' AND `parent_id` = '.$parent_id;
			}else{
			    $where  .= ' AND `parent_id` = 0';
			}
			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where,$target_rank,$lr,$p);
			$this -> writelog('软件必备_软件必备区间管理-重新排序了分区id为'.$extent_id."的区间从'$oldrank'变为'$target_rank'", 'sj_necessary_extent', $extent_id, __ACTION__, 'rank_config','edit');
		    exit(json_encode($param));
		}
	}
	
	//批量更新排序
	function batch_rank(){
	    if(isset($_GET)){
		    $model = M('necessary_extent');
			$ids   = (string)$_GET['id'];
			$ranks = (string)$_GET['rank'];
			$ids   = substr($ids,0,strlen($ids)-1);
			$ranks = substr($ranks,0,strlen($ranks)-1);
			
			$extent_list = array();
			$allids   = explode(",",$ids);
			$allranks = explode(",", $ranks);
			
			$extent_list = array_combine($allids,$allranks);
			foreach($extent_list as $id => $rank){
				$model -> query("UPDATE __TABLE__ set rank = ".$rank." WHERE status = 1 AND extent_id = " .$id);
			}
			
			$this->writelog('软件必备_软件必备区间管理-批量更新了extent_id为'.$ids.'的排序',__TABLE__, $ids, __ACTION__, '','edit');
			$this->assign('jumpUrl','/index.php/Sj/NecessaryExtent/index');
			$this->success('批量更新成功');
		}
	}

	function add_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		if (!empty($_POST)){
            // 业务逻辑：trim一下需要用到的数据
            $useful_key = array('extent_id', 'package', 'beid', 'prob', 'start_at', 'end_at', 'been_install','type');
            foreach($useful_key as $key=>$value) {
                if (isset($_POST[$value]))
                    $_POST[$value] = trim($_POST[$value]);
            }
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $_POST;
            //var_dump($content_arr);exit;
            $error_msg = $this->logic_check($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) {
                $msg = $error_msg[0]['msg'];
                // 业务逻辑：设置返回的跳转页面
                $this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/index');
				$this->error($msg);
			}
			
			$model = M('necessary_extent_soft');
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];

			isset($_POST['extent_id']) && $map['extent_id'] = $_POST['extent_id'];
			isset($_POST['package']) && $map['package'] = $_POST['package'];
			$map['beid'] = isset($_POST['beid']) ? $_POST['beid'] : 0;
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			isset($_POST['been_install']) && $map['been_install'] = $_POST['been_install'];
			if(isset($_POST['type'])){
				$map['type'] = $_POST['type'];
			}else{
				$map['type'] = 0;
			}
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
			if($shield_error){
			    $this -> error($shield_error);
			}
			if ($id = $model->add($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/index');
				$this->writelog("软件必备_软件必备区间管理-添加了ID为[{$id}]软件包名[{$_POST['package']}],显示概率为{$_POST['prob']},已装最新版用户不展示{$_POST['been_install']},开始时间{$_POST['start_at']},结束时间{$_POST['end_at']},", 'sj_necessary_extent_soft', $id, __ACTION__, '','add');
				$this->success('添加成功');
			} else {
				$this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/index');
                                //echo $model -> getLastSql();
				$this->error('添加失败');				
			}
		} else {
			//合作样式显示
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			
			$this->assign('extent_id',$_GET['extent_id']);
			$this->display();			
		}
	}
	
	function list_soft()
	{
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}
		
		$model = M('necessary_extent_soft');
		$extent_id = $_GET['extent_id'];
		$srch_type = $_GET['srch_type'];
		$where = array(
			'extent_id' => $extent_id,
			'status' => 1
		);
		$now = time();
		switch($srch_type) {
			case 'e':
				$where['end_at'] = array('elt',$now);
			break;

			case 'f':
				$where['start_at'] = array('egt',$now);
			break;	

			case 'n':
			default:
				$where['start_at'] = array('elt',$now);
				$where['end_at'] = array('egt',$now);
				$srch_type = 'n';
			break;
		}
		$list = $model->where($where)->order('start_at asc')->select();
		$package = array();
		$result = array();
		foreach($list as $val) {
			$val['package'] = strtolower($val['package']);
			$package[] = $val['package'];
			$result[] = $val;
		}
		$soft = $model->table('sj_soft')->where(array('package' => array('in', $package)))->field('softname,softid,package')->group('package')->select();
		foreach($soft as $val) {
			$val['package'] = strtolower($val['package']);
			$package_result[$val['package']]['softname'] = $val['softname'];
			$package_result[$val['package']]['softid'] = $val['softid'];
		}
		$util = D("Sj.Util"); 
		foreach($result as $k=>$v)
		{
			$result[$k]['softname']=$package_result[$v['package']]['softname'];
			$result[$k]['softid']=$package_result[$v['package']]['softid'];
			$typelist = $util->getHomeExtentSoftTypeList($v['type']);
			foreach($typelist as $key => $val){
				if($val[1] == true)
				{
					$result[$k]['types'] = $val[0];
				}
			}
		}
		
		
		$where = array(
			'status' => 1
		);
		$extent_result = $extents = $model->table('sj_necessary_extent')->where($where)->order('parent_id asc, rank asc, type desc')->select();
		$extent_list = array();
		foreach($extent_result as $v){
			$extent_list[$v['extent_id']] = $v;
		}
		
		$extent_select = array();
		foreach($extent_result as $v) {
			if ($v['type'] == 1) {
				if($v['parent_id'] > 0) {
					$extent_select[$v['extent_id']] = $extent_list[$v['parent_id']]['extent_name'] . ' > ' . $v['extent_name'];
				} else {
					$extent_select[$v['extent_id']] = $v['extent_name'];
				}
			}
		}
		$this->assign('extent_name', $extent_select[$extent_id]);
		
		$this->assign('srch_type', $srch_type);
		$this->assign('extent_id', $extent_id);
		$this->assign('list', $result);
		$this->assign('extent_select', $extent_select);
		$this->assign('extents', $extents);
		$this->assign('isAjax', $this->isAjax());
		$this->display();
	}
	
	function edit_soft()
	{
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}
				
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$model = M('necessary_extent_soft');
		$soft = $model->where($where)->find();

		if (!empty($_POST)){
            // 业务逻辑：trim一下需要用到的数据
            $useful_key = array('extent_id', 'package', 'beid', 'prob', 'start_at', 'end_at', 'been_install','type');
            foreach($useful_key as $key=>$value) {
                if (isset($_POST[$value]))
                    $_POST[$value] = trim($_POST[$value]);
            }            
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $_POST;
            $error_msg = $this->logic_check($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) {
                $msg = $error_msg[0]['msg'];
                // 业务逻辑：设置返回的跳转页面
                $this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/index');
				$this->error($msg);
            }
            
			$map = array();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			isset($_POST['package']) && $map['package'] = $soft['package'];
			$map['beid'] = isset($_POST['beid']) ? $_POST['beid'] : 0;
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			if(isset($_POST['been_install'])){
				$map['been_install'] = $_POST['been_install'];
			}else{
				$map['been_install'] = 0;
			}
			if(isset($_POST['type'])){
				$map['type'] = $_POST['type'];
			}else{
				$map['type'] = 0;
			}
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
			if($shield_error){
			    $this -> error($shield_error);
			}
            $log_all_need = $this->logcheck($where,'sj_necessary_extent_soft', $map, $model);
			if ($model->where($where)->save($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/list_soft/extent_id/'. $soft['extent_id']."/p/{$_POST['p']}");
				$this->writelog("软件必备_软件必备区间管理-编辑了{$soft['extent_id']}区间里的{$id}软件包名[{$_POST['package']}],".$log_all_need, 'sj_necessary_extent_soft', $id, __ACTION__, '','edit');
				$this->success('编辑成功');
			}
		} else {
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList($soft['type']);
			$this->assign('typelist',$typelist);

			$this->assign('soft', $soft);
			$this->display();
		}
	}
	
	function del_extent()
	{
		$extent_id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $extent_id
		);
		$map = array(
			'status' => 0,
			'update_at' => time(),
		);
		$model = M('necessary_extent');
		
		$model->table('sj_necessary_extent')->where($where)->save($map);
		$model->table('sj_necessary_extent_soft')->where($where)->save($map);
		
		$where = array(
			'parent_id' =>$extent_id
		);
		$res = $model->table('sj_necessary_extent')->where($where)->field('extent_id')->select();
		$extent_ids = array();
		foreach($res as $v){
			$extent_ids[] = $v['extent_id'];
		}
		if (!empty($extent_ids)) {
			$where = array(
				'extent_id' => array('in', $extent_ids)
			);
			$model->table('sj_necessary_extent')->where($where)->save($map);
			$model->table('sj_necessary_extent_soft')->where($where)->save($map);
		}
		
		if(isset($_REQUEST['parent_id'])){
		   $parent_id = (int)$_REQUEST['parent_id'];
		}else{
		   $parent_id = 0;
		}
		
		$extent_list = $model->where('`status` = 1 AND `parent_id` ='.$parent_id)->order('rank ASC')-> select();
		$count = count($extent_list);
		for($i = 1;$i <= $count; $i++){
			$sql   = 'UPDATE __TABLE__ SET rank ='.$i.' WHERE `status` = 1 AND `parent_id` = '.$parent_id.' AND extent_id ='.$extent_list[$i-1]['extent_id']; 
			$model -> query($sql);
		}
		$this->writelog('软件必备_软件必备区间管理-删除了id为'.$extent_id.'的区间', 'sj_necessary_extent', $extent_id, __ACTION__, '','edit');
		$this->success('删除成功');
	}
	
	function move_soft()
	{
		$selected_ids = $_POST['selected_ids'];
		if (strpos($selected_ids, ',')){
			$selected_ids = substr($selected_ids, 0, strripos($selected_ids, ','));
		}
		$extent_id = $_POST['extent_id'];
		$where = array(
			'id' => array('in' ,$selected_ids)
		);
		$map = array(
			'extent_id' => $extent_id
		);
		$model = M('necessary_extent_soft');
		$model->where($where)->save($map);
		$this->assign('jumpUrl', '/index.php/Sj/NecessaryExtent/index');
//		$this->writelog("将id为[{$selected_ids}]的软件移动到了区间{$extent_id}", 'sj_necessary_extent_soft');
		$soft_where['id'] = $selected_ids;
		$package = $model -> where($soft_where) -> field('package') -> select();
		$this -> writelog("软件必备_软件必备区间管理-将id为[{$selected_ids}]的软件,包名为[{$package[0]['package']}]移动到了区间{$extent_id}", 'sj_necessary_extent_soft', $selected_ids, __ACTION__, 'rank_config','edit');
		$this->success('移动成功');
	}
	
	function del_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$map = array(
			'status' => 0,
			'update_at' => time(),
		);
		$model = M('necessary_extent_soft');
		$package = $model->where($where)->find();
		$model->where($where)->save($map);
		$this->writelog("软件必备_软件必备区间管理-删除了id为[$id]包名为{$package['package']}的区间推荐软件", 'sj_necessary_extent_soft', $id, __ACTION__, '','del');
		$this->success('删除成功');
	}
	
    function isAjax ()
    {
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest")  return true;
		return false;
    }
	
	function checkPropAct()
	{
		$extent_id = $_GET['extent_id'];
		$now = time();
		$where = array(
			'extent_id' => $extent_id,
			'status' => 1,
			'start_at' => array('elt',$now),
			'end_at' => array('egt',$now),
		);
		if (isset($_GET['id'])) {
			$where['id'] = array('neq', $_GET['id']);
		}
		
		$model = M('necessary_extent_soft');
		$result = $model->where($where)->field('sum(prob) as prob')->find();
		$total_prob = $result['prob'];
		
		$where = array(
			'extent_id' => $extent_id,
			'status' => 1,
		);
		$result = $model->table('sj_necessary_extent')->where($where)->find();
		$limit_prob = $result['extent_size'] * 100;
		echo $total_prob > $limit_prob ? 0: 1;
		$result = array(
			'total' => $total_prob,
			'max' => $limit_prob
		);
		exit(json_ecode($result));
	}
    
    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
    
    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'extent_id'     =>  '区间ID',
            'extent_name'   =>  '区间名',
            'package'  =>   '包名',
            'prob'  =>   '显示概率',
            'start_at'  =>   '开始时间(yyyy/MM/dd)',
            'end_at'  =>   '结束时间(yyyy/MM/dd)',
            'been_install'  =>   '已装最新版用户不展示',
			'type' =>   '合作形式',
			'beid'	=> '行为id',
			//批量刷数据库 增加一个创建时间 add by shitingting 2016-11-7
			'create_tm' =>'创建时间',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        // 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 当输入为空不允许时，将其值设为false以作区别
        $expected_words['been_install'] = array("" => 0, "是" => 1, "否" => 0);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
                if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 如果是false不处理（在后台的logic_check()里会统一进行非空检查），即还是为空，否则替换成相应的数字
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_at' || $r_key == 'end_at' ||$r_key == 'create_tm') {
                    if ($r_key == 'start_at') {
                        $type = 0;
                        $hint = '开始';
                    } elseif($r_key == 'end_at') {
                        $type = 1;
                        $hint = '结束';
                    } elseif($r_key == 'create_tm')
					{
						$type = 2;
                        $hint = '创建';
					}
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $this->append_error_msg($error_msg, $key, 1, "{$hint}时间需以T开头;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }// else转换错误，保持原始值，后面的logic_check会校验原始格式
                }
            }
        }
        return $error_msg;
    }
    
    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'necessary_extent';
        $M_extent_soft_table = 'necessary_extent_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $extent_soft_model->where($where)->find();
                $record['extent_id'] = $find['extent_id'];
            }
            // 检测区间ID
            if (isset($record['extent_id']) && $record['extent_id'] != "") {
                $where = array(
                    'extent_id' => array('EQ', $record['extent_id']),
                    'type' => array('EQ', 1),
                    'status' => array('EQ', 1),
                );
                $find = $extent_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "区间位ID【{$record['extent_id']}】无效;");
                } else {
                    if (isset($record['extent_name'])) {
                        // 检查区间ID与区间名是否对应
                        if ($find['extent_name'] != $record['extent_name']) {
                            $this->append_error_msg($error_msg, $key, 1, "区间位ID与区间名不对应;");
                        }
                    }
                    // 得到该记录区间的cid、oid和pid，并保存起来，方便后面的区间冲突检查
                    $content_arr[$key]['cid'] = $find['cid'];
                    $content_arr[$key]['oid'] = $find['oid'];
                    //$content_arr[$key]['pid'] = $find['pid'];
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "区间位ID不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if (isset($record['package']) && $record['package'] != "") {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
            // 检查行为id
            if (isset($record['beid']) && $record['beid'] != "") {
                if (!preg_match("/^\d+$/", $record['beid'])) {
                    $this->append_error_msg($error_msg, $key, 1, "行为id应为大于等于0的整数;");
                }
            }
            // 检查显示概率是否为数字
            if (isset($record['prob']) && $record['prob'] != "") {
                if (!preg_match("/^\d+$/", $record['prob'])) {
                    $this->append_error_msg($error_msg, $key, 1, "显示概率应为整数;");
                } else if ($record['prob'] > 100) {
                    $this->append_error_msg($error_msg, $key, 1, "显示概率不能大于100;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "显示概率值不能为空;");
            }
            // 检查开始时间
            //var_dump($record['start_at']);exit;
            if (isset($record['start_at']) && $record['start_at'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_at'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_at']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_at']) && $record['end_at'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_at'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_at']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
			
			//刷量开始时间  年月日 时分秒
			 if (isset($record['create_tm']) && $record['create_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['create_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "创建时间日期格式不对;");
                }
				else {
                    $time = strtotime($record['create_tm']);
                    if ($time) {
                        $content_arr[$key]['bk_creat_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "创建时间日期格式不对;");
                    }
                }
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
			//刷量中的创建时间不能大于开始时间和结束时间
			if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_creat_time'])) {
                if ($content_arr[$key]['bk_creat_time']> $content_arr[$key]['bk_start_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "创建时间需小于开始时间;");
                }
            }
        }
        
        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) {
            // 如果填写的区间无效，则不比较
            if (!isset($record1['cid']) || !isset($record1['oid']))
                continue;
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果填写的区间无效，则不比较
                if (!isset($record2['cid']) || !isset($record2['oid']))
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                // 区间属性不同，则不比较
                if ($record1['cid'] != $record2['cid'] || $record1['oid'] != $record2['oid'])
                    continue;
                // 包名相同
                if ($record1['package'] == $record2['package']) {
                    // 时间是否交叉
                    if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                        $k1 = $key1 + 1; $k2 = $key2 + 1;
                        $this->append_error_msg($error_msg, $key1, 1, "投放区间与第{$k2}行有重叠;");
                        $this->append_error_msg($error_msg, $key2, 1, "投放区间与第{$k1}行有重叠;");
                    }
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 业务逻辑：如果填写的区间无效，则不比较
            if (!isset($record['cid']) || !isset($record['oid']))
                continue;
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            // 检查是否与sj_extent_soft_v1表里有相同包名且区间冲突的包
            // 业务逻辑：获得当前记录的信息：package、cid、oid
            $es_package = escape_string($record['package']);
            $cid = escape_string($record['cid']);
            $oid = escape_string($record['oid']);
            $start_time = escape_string($record['bk_start_time']);
            $end_time = escape_string($record['bk_end_time']);
            // 业务逻辑：构造sql语句，查找出与该记录包名相同、也是在相同属性的区间的所有后台记录
            $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.package as package, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
            $sql_from = " from {$extent_soft_table} left join {$extent_table}";
            $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
            $sql_where = " where {$extent_soft_table}.package='{$es_package}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.type=1 and {$extent_table}.cid='{$cid}' and {$extent_table}.oid='{$oid}'";
            // 如果有传id过来，说明是编辑，这时要排除此id
            $sql_where_except = "";
            if (isset($record['id'])) {
                $except_id = escape_string($record['id']);
                $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
            }
            // 将select、from、on、where、except拼接起来
            $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
            // 执行sql
            $db_records = $extent_soft_model->query($sql);
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) {
                $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
    
    // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
            }
        }
        return $content_arr;
    }
    
    function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_at','end_at');
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        foreach($error_msg3 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    // 下载批量导入模版
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "bibei_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('必备批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }
    
    // 批量导入访问的页面节点
    function import_softs() {
        if ($_GET['down_moban']) {
            $this->down_moban();
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_necessary_extent_soft_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            $ret = move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("必备频道软件推荐：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $model = M('necessary_extent_soft');
        $AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
            $map['status'] = 1;
			if($record['create_tm'])
			{
				$map['create_at'] = strtotime($record['create_tm']);
				$map['update_at'] = strtotime($record['create_tm']);
				$map['anzhi_ad_type'] = 1;
			}
			else
			{
				$map['create_at'] = time();
				$map['update_at'] = time();
			}
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
            $map['extent_id'] = $record['extent_id'];
            $map['package'] = $record['package'];
            $map['beid'] = $record['beid'];
            $map['prob'] = $record['prob'];
            $map['start_at'] = strtotime($record['start_at']);
            $map['end_at'] = strtotime($record['end_at']);
            // 赋值，以下为非必填项，有默认值
            $map['been_install'] = isset($record['been_install']) ? $record['been_install'] : 0;
			$map['type'] = isset($record['type']) ? $record['type'] : 0;
			$data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }
            if ($id = $model->add($map)) {
                $this->writelog("软件必备_软件必备区间管理-在装机必备区间[{$record['extent_id']}]中添加了软件[{$record['package']}]", 'sj_necessary_extent_soft', $id,__ACTION__, '','add');
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
            }
            //  else {
            //     // 未知原因添加失败
            //     $result_arr[$key]['flag'] = 0;
            //     $result_arr[$key]['msg'] = "添加失败";			
            // }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_necessary_extent_soft')){
        	$result_arr['table_name']='sj_necessary_extent_soft';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
    
    
}
