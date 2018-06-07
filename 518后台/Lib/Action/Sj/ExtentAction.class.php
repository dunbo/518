<?php
class ExtentAction extends CommonAction {

	function ExtentAction()
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
		$util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
	
		if($pid == 5){
			$model = new Model();
			$extend_result = $model -> table('sj_extent') -> where(array('pid' => 5,'status' => 1)) -> select();
			foreach($extend_result as $key => $val){
				$extent_size_arr[] = $val['extent_size'];
				$soft_where['_string'] = "extent_id = {$val['extent_id']} and status = 1 and start_at <= ".time()." and end_at >= ".time()."";
				$soft_result = $model -> table('sj_extent_soft') -> where($soft_where) -> count();
		
				$my_soft[] = $soft_result;
		
			}
			$soft_all = array_sum($my_soft);
			$extent_all = array_sum($extent_size_arr);
			$this -> assign('soft_all',$soft_all);
			$this -> assign('extent_all',$extent_all);
		}
		import("@.ORG.Page");
        $param = http_build_query($_GET);
		$limit = 10;
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
		
		$model = M('extent');
		$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
		
		$map = array(
			'status' => 1,
			'pid' => $pid,
			'parent_id' => $parent_id 
		);

		$count_total = $model -> where($map)->count();
		$page  = new Page($count_total, $limit, $param);
		
		
		$this->assign('parent_id', $parent_id);
		$this->assign('pid', $pid);

		$now = time();
		$list = $model->where($map)->order('rank asc, type desc')->limit($page->firstRow . ',' . $page->listRows)->select();

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
				$count = $model->table('sj_extent_soft')->where($where)->count();
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
		
		$this->assign('product_list',$util->getProducts($pid));
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
			$model = M('extent');
			$channel=M('channel');
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			
			isset($_POST['extent_name']) && $map['extent_name'] = $_POST['extent_name'];
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			isset($_POST['cid']) && $map['cid'] = $_POST['cid'];
			isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			!empty($_POST['parent_id']) && $map['parent_id'] = $_POST['parent_id'];
			!empty($_POST['pid']) && $map['pid'] = $_POST['pid'];
			!empty($_POST['location']) && $map['location'] = $_POST['location'];
			
			if(empty($map['cid'])){
				$zh_chname="全部可见";
			}else{
				$zh_chname=$channel->where(array("status"=>1,"cid"=>$map['cid']))->getfield("chname");
			}
			if(empty($map['oid'])){
				$zh_oid_name="全部可见";
			}else{
				$zh_oid_name=$channel->table("pu_operating")->where(array("oid"=>$map['oid']))->getfield("mname");
			}
			$rank  = (int)$_POST['rank'];
			$pid  = $_POST['pid'];
			
			if ($id = $model->add($map)) {
				$where = array(
					'status' => 1,
					'pid' => $pid,
				);
				if(isset($_POST['parent_id'])){
					$where['parent_id'] = (int)$_POST['parent_id'];
				}else{
					$where['parent_id'] = 0;
				}
			    //更新排序
		        $this->_updateRankInfo('sj_extent','rank',$id,$where,$rank);
				$this->assign('jumpUrl', '/index.php/Sj/Extent/index/pid/'. $pid);
				$msg='安卓游戏首页软件列表:添加了id为'.$id.'的区间';
				$msg .="可见渠道为[{$zh_chname}] \n";
				$msg .="运营商为[{$zh_oid_name}]";
				$this->writelog($msg, 'sj_extent',$id,__ACTION__ ,'','add');
				$this->success('添加成功');
			}

		} else {
			$address_edit_able = true;
			if ($_GET['parent_id']) {
				$extent_model = M('extent');
				$where = array(
					'id' => $_GET['parent_id']
				);
				
				$extent = $extent_model->where(array('extent_id' => $_GET['parent_id']))->find();
				if (!empty($extent['location'])) {
					$address_edit_able = false;
					$this->assign('location',$extent['location']);
				}
			}
		
			$channel_model = M('channel');
			$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
			$this->assign('channel_list', $channels);

			$operating_db = D('Sj.Operating');
			$operating_list = $operating_db->field('oid,mname')->select();
			$this->assign('operatinglist',$operating_list);
			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);
			!empty($_GET['pid']) && $this->assign('pid',$_GET['pid']);

			$extent_model = M('extent');
			$pid  = $_GET['pid'];
			$map = array(
				'status' => 1,
				'pid' => $pid,
				'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0
			);
			$count = count($extent_model -> where($map)->select()) + 1;
			$this->assign('count',$count);
			$this->assign('address_edit_able',$address_edit_able);
			$this->display();
		}
	}
	
	function edit_extent()
	{
		$id = $_REQUEST['extent_id'];
		$where = array(
			'status'=>1,
			'extent_id' => $id
		);
		$model = M('extent');
		$old_extent = $extent = $model->where($where)->find();
		$pid = $extent['pid'];
		if (!empty($_POST)){
			$channel=M('channel');
			if(!empty($old_extent['cid'])){
				$old_where['status']=1;
				$old_where['cid']=$old_extent['cid'];
				$old_ch_chname=$channel->where($old_where)->getfield("chname");
				$old_zh_chname=$old_ch_chname;
			}else{
				$old_zh_chname="全部可见";
			}
			if(empty($old_extent['oid'])){
				$old_oid_name="全部可见";
			}else{
				$old_oid_name=$channel->table("pu_operating")->where(array("oid"=>$old_extent['oid']))->getfield("mname");
			}
			$map = array();
			$map['update_at'] = time();
			
			isset($_POST['extent_name']) && $map['extent_name'] = $_POST['extent_name'];
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			isset($_POST['cid']) && $map['cid'] = $_POST['cid'];
			//isset($_POST['rank']) && $map['rank'] = $_POST['rank'];
			isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			isset($_POST['location']) && $map['location'] = $_POST['location'];
			$where_rank = array(
				'status' => 1,
				'pid' => $pid,
			);
			if(isset($_POST['parent_id'])){
				$where_rank['parent_id'] = (int)$_POST['parent_id'];
			}else{
				$where_rank['parent_id'] = 0;
			}
			if(!empty($map['cid'])){
				$new_where['status']=1;
				$new_where['cid']=$map['cid'];
				$new_ch_chname=$channel->where($new_where)->getfield("chname");
				$new_zh_chname=$new_ch_chname;
			}else{
				$new_zh_chname="全部可见";
			}
			if(empty($map['oid'])){
				$new_oid_name="全部可见";
			}else{
				$new_oid_name=$channel->table("pu_operating")->where(array("oid"=>$map['oid']))->getfield("mname");
			}
			
			$rank = (int)$_POST['rank'];
			//更新排序
		    $this->_updateRankInfo('sj_extent','rank',$id,$where_rank,$rank);
			
			if ($model->where($where)->save($map)) {
				if (isset($map['location']) && $map['location'] != $old_extent['location']) {
					$soft_where = array(
						'extent_id' => $id
					);
					$soft_data = array(
						'location' => '',
					);
					isset($map['location']) && $soft_data['location'] = $map['location'];
				
					$model->table('sj_extent_soft')->where($soft_where)->save($soft_data);
					
					//活动区间会覆盖子区间的设置
					if ($old_extent['type'] == 2) {
						$extent_where = array(
							'parent_id' => $old_extent['extent_id']
						);
						$extent_data = $soft_data;
						$model->where($extent_where)->save($extent_data);
						
						$extents = $model->where($extent_where)->select();
						if ($extents) {
							$extent_ids = array();
							foreach ($extents as $key => $value) {
								$extent_ids[] = $value['extent_id'];
							}
							$soft_where = array(
								'extent_id' => array('in', $extent_ids)
							);
							$model->table('sj_extent_soft')->where($soft_where)->save($soft_data);
						}
					}
					
				}
				
				
				$configModel = D('Sj.Config');
				$column_desc = $configModel->getExtentColumnDesc();
				$msg = "安卓游戏首页软件列表:编辑了extent_id为[{$id}],名为[{$old_extent['extent_name']}]的区间\n";
				foreach ($map as $key => $val) {
					if (isset($column_desc[$key]) && $map[$key] != $old_extent[$key]) {
						$desc = $column_desc[$key];
						if($key=="cid"){
							$msg .= "将{$desc} 从'{$old_zh_chname}'修改成'{$new_zh_chname}'\n";	
						}else{
							$msg .= "将{$desc} 从'{$old_oid_name}'修改成 '{$new_oid_name}'\n";
						}
						
					}
				}
				$this->writelog($msg, 'sj_extent',$id,__ACTION__ ,'','edit');
				$this->assign('jumpUrl', '/index.php/Sj/Extent/index/pid/'.$pid);
				$this->success('编辑成功');
			}

		} else {
			$address_edit_able = true;
			if ($old_extent['parent_id']) {
				$where = array(
					'id' => $old_extent['parent_id']
				);
				
				$pextent = $model->where(array('extent_id' => $old_extent['parent_id']))->find();
				if (!empty($pextent['location'])) {
					$address_edit_able = false;
					$this->assign('location',$pextent['location']);
				}
			}
			$channel_model = M('channel');
			$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
			$this->assign('channel_list', $channels);

			$operating_db = D('Sj.Operating');
			$operating_list = $operating_db->field('oid,mname')->select();
			$this->assign('operatinglist',$operating_list);
			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);
			
			
			$condition = array(
				'status' => 1,
				'pid' => $pid,
				'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0 
			);
			
			$count = count($model -> where($condition)->select());
			$this->assign('count',$count);

			
			if(!empty($extent['cid'])){
				$cid_array=array();
				$cid_array['cid']=$extent['cid'];
				$cid_array['chname']=$channel_model->where(array('status' => 1,'cid'=>$cid_array['cid']))->getfield("chname");
				$this->assign("cid_array",$cid_array);
			}
			$this->assign('extent', $extent);
			$this->assign('address_edit_able',$address_edit_able);
			$this->display();
		}
	}
	
	//更新某个排序
	function edit_rank(){
	    if(isset($_GET)){
			$table       = 'sj_extent';
			$field       = 'rank';
			$where       = '`status` = 1';
			$extent_id   = (int)$_GET['extent_id'];
			$parent_id   = (int)$_GET['parent_id'];
			$pid   = (int)$_GET['pid'];
			$target_rank = (int)$_GET['rank'];
			$lr          = isset($_GET['lr']) ? (int)$_GET['lr'] : 20;
		    $p           = isset($_GET['p'])  ? (int)$_GET['p']  : 1;
		
			$where_rank = array(
				'status' => 1,
				'pid' => $pid,
			);
			if(isset($parent_id)){
				$where_rank['parent_id'] = (int)$_POST['parent_id'];
			}else{
				$where_rank['parent_id'] = 0;
			}
			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank,$lr,$p);
			$this -> writelog('安卓游戏首页软件列表:更新了extent_id为'.$extent_id.'的区间', 'sj_extent', $extent_id,__ACTION__ ,'','edit');
		    exit(json_encode($param));
		}
	}
	
	//批量更新排序
	function batch_rank(){
	    if(isset($_GET)){
		    $model = M('extent');
			$ids   = (string)$_GET['id'];
			$pid   = (string)$_GET['pid'];
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
			
			$this->writelog('安卓游戏首页软件列表:批量更新了extent_id为'.$ids.'的排序',__TABLE__, $ids,__ACTION__ ,'','edit');
			$this->assign('jumpUrl','/index.php/Sj/Extent/index/pid/'.$pid);
			$this->success('批量更新成功');
		}
	}

	function add_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		
		$model = M('extent_soft');
		
		$extent = $model->table('sj_extent')->where(array('extent_id' => $_REQUEST['extent_id']))->find();
		$address_edit_able = true;
		if (!empty($extent['location'])) {
			$address_edit_able = false;
		}
		
		if (!empty($_POST)){
			
			$es_extent_id = escape_string($_POST['extent_id']);
			$sql = "select extent_name, oid,cid,pid from sj_extent where extent_id={$es_extent_id}";
			$find = $model->query($sql);
			$extent_name = $find[0]['extent_name'];
			$cid = $find[0]['cid'];
			$oid = $find[0]['oid'];
			$pid = $find[0]['pid'];
			$s = strtotime($_POST['start_at']);
			$e = strtotime($_POST['end_at']. ' 23:59:59');
			if ($s > $e) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_soft/extent_id/'. $_POST['extent_id']);
				$this->error("开始时间不能大于结束时间");
			}
			
			$es_package = escape_string($_POST['search_package']);
			$sql = "select B.extent_name, from_unixtime(A.start_at) as start_at,from_unixtime(A.end_at) as end_at from sj_extent_soft AS A left join sj_extent B on A.extent_id=B.extent_id where A.package='{$es_package}' and A.status=1 and B.status=1 and (A.start_at<{$e} and A.end_at>{$s}) and B.cid='{$cid}' and B.oid='{$oid}' and B.pid='{$pid}' limit 1";
			$find = $model->query($sql);
			if ($find[0]['extent_name']) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_soft/extent_id/'. $_POST['extent_id']);
				$this->error("包名{$_POST['search_package']} 已经在区间 '{$find[0]['extent_name']}' 中存在, 排期{$find[0]['start_at']}~{$find[0]['end_at']}");
			}
			$where = array(
				'package' => $_POST['search_package'],
				'status' => 1,
				'hide' => array('in', array(1, 1024)),
			);
			$find = $model->table('sj_soft')->where($where)->find();

			if (!$find) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_soft/extent_id/'. $_POST['extent_id']);
				$this->error("包名{$_POST['search_package']} 不存在于市场软件库中，请确认包名的正确性!!");
			}
			
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];

			isset($_POST['extent_id']) && $map['extent_id'] = $_POST['extent_id'];
			isset($_POST['search_package']) && $map['package'] = $_POST['search_package'];
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			if ($address_edit_able) {
				isset($_POST['location']) && $map['location'] = $_POST['location'];
			} else {
				$map['location'] = $extent['location'];
			}
			if ($id = $model->add($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_soft/extent_id/'.$_POST['extent_id']);
				$this->writelog("安卓游戏首页软件列表:在区间[{$_POST['extent_id']}]中添加了软件[{$_POST['search_package']}]", 'sj_extent_soft', $id,__ACTION__ ,'','add');
				$this->success('添加成功');
			} else {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/index');
				$this->error('添加失败');				
			}
		} else {
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			$this->assign('extent_id',$_GET['extent_id']);
			$this->assign('location',$extent['location']);
			$this->assign('address_edit_able',$address_edit_able);
			$this->display();			
		}
	}

	function list_area_soft()
	{
		$util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
		$model = M('extent_soft');
		$extent_where = array(
			'pid' => $pid,
			'status' => 1	
		);
		$extents = $model->table('sj_extent')->where($extent_where)->select();
		$extent_ids = array();
		foreach ($extents as $value) {
			$extent_ids[] = $value['extent_id'];
		}
		$srch_type = $_GET['srch_type'];
		$where = array(
			'extent_id' => array('IN', $extent_ids),
			'status' => 1
		);
		if (empty($_GET['city']) && empty($_GET['province'])) {
			$_complex = array(
				'location' => array('NEQ', ''),
				'_logic' => 'or'
			);
			$where['_complex'] = $_complex;
		} else {
			if (isset($_GET['province'])){
				$where['location'] = array('LIKE', '%'. escape_string($_GET['province']). '%');
				$this->assign('province', $_GET['province']);
			}
			if (isset($_GET['city'])){
				$where['location'] = array('LIKE', '%'. escape_string($_GET['city']). '%');
				$this->assign('city', $_GET['city']);
			}
		}
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
		
		$count = $model->where($where)->count(); 
		
		import("@.ORG.Page");
		$p = new Page ( $count, 15 );
		$list = $model->limit($p->firstRow.','.$p->listRows)->where($where)->findAll();
		$page = $p->show ();
		
		$package = array();
		$result_package = array();
		foreach($list as $val) {
			$package[] = $val['package'];
			$result_package[$val['package']] = array();
		}
		$soft = $model->table('sj_soft')->where(array('package' => array('in', $package)))->field('softname,softid,package')->group('package')->select();
		foreach($soft as $val) {
			$result_package[$val['package']]['softname'] = $val['softname'];
			$result_package[$val['package']]['softid'] = $val['softid'];
		}
		$result = array();
		foreach($list as $val) {
			$val['softname'] = $result_package[$val['package']]['softname'];
			$val['softid'] = $result_package[$val['package']]['softid'];
			$result[] = $val;
		}
		
		$where = array(
			'status' => 1
		);
		$extent_result = $extents = $model->table('sj_extent')->where($where)->order('parent_id asc, rank asc, type desc')->select();
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
			if ($v['extent_id'] == $extent_id) {
				$this->assign('pid', $v['pid']);
			}
		}
		$this->assign('extent_name', $extent_select[$extent_id]);
		$this->assign('srch_type', $srch_type);
		$this->assign('extent_id', $extent_id);
		$this->assign('list', $result);
		$this->assign('extent_select', $extent_select);
		$this->assign('extents', $extents);
		$this->assign('isAjax', $this->isAjax());
		$this->assign('page', $page);
		$this->assign('pid', $pid);
		$this->assign('product_list',$util->getProducts($pid));
		$this->display();
	}
	
	function list_soft()
	{
		$model = M('extent_soft');
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
		$list = $model->where($where)->select();
		$package = array();
		$result_package = array();
		foreach($list as $val) {
			$package[] = $val['package'];
			$result_package[$val['package']] = array();
		}
		$soft = $model->table('sj_soft')->where(array('package' => array('in', $package)))->field('softname,softid,package')->group('package')->select();
		foreach($soft as $val) {
			$result_package[$val['package']]['softname'] = $val['softname'];
			$result_package[$val['package']]['softid'] = $val['softid'];
		}
		$util = D("Sj.Util");
		$result = array();
		foreach($list as $key=>$val) {
			$val['softname'] = $result_package[$val['package']]['softname'];
			$val['softid'] = $result_package[$val['package']]['softid'];
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true){
					$val['types'] = $v[0];
				}				
			}
			$result[] = $val;
		}
		//var_dump($result);
		
		$extent = $model->table('sj_extent')->where(array('extent_id' => $extent_id))->find();
		$pid = $extent['pid'];
		$this->assign('pid', $pid);
		$where = array(
			'status' => 1,
			'pid' => $pid
		);
		
		$extent_result = $extents = $model->table('sj_extent')->where($where)->order('parent_id asc, rank asc, type desc')->select();
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
		//print_r($result);exit;
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
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$model = M('extent_soft');
		$soft = $model->where($where)->find();
		
		$extent = $model->table('sj_extent')->where(array('extent_id' => $soft['extent_id']))->find();
		$address_edit_able = false;
		if (empty($extent['location'])) {
			$address_edit_able = true;
		}
		
		if (!empty($_POST)){
			$map = array();
			$map['update_at'] = time();

			$s = strtotime($_POST['start_at']);
			$e = strtotime($_POST['end_at']. ' 23:59:59');
			
			if ($s > $e) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_soft/extent_id/'. $_POST['extent_id']);
				$this->error("开始时间不能大于结束时间");
			}
			
			$es_extent_id = escape_string($_POST['extent_id']);
			$sql = "select extent_name, oid,cid,pid from sj_extent where extent_id={$es_extent_id}";
			$find = $model->query($sql);
			$extent_name = $find[0]['extent_name'];
			$cid = $find[0]['cid'];
			$oid = $find[0]['oid'];
			$pid = $find[0]['pid'];
			
			$es_package = escape_string($soft['package']);
			$sql = "select B.extent_name,from_unixtime(A.start_at) as start_at,from_unixtime(A.end_at) as end_at from sj_extent_soft AS A left join sj_extent B on A.extent_id=B.extent_id where A.package='{$es_package}' and A.status=1 and (A.start_at<{$e} and A.end_at>{$s}) and id<>{$id} and B.cid='{$cid}' and B.oid='{$oid}' and B.pid='{$pid}' limit 1";
			$find = $model->query($sql);
			if ($find[0]['extent_name']) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_soft/extent_id/'. $_POST['extent_id']);
				$this->error("包名{$soft['package']} 已经在区间 '{$find[0]['extent_name']}' 中存在, 排期{$find[0]['start_at']}~{$find[0]['end_at']}");
			}
			
			isset($_POST['package']) && $map['package'] = $_POST['package'];
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			if ($address_edit_able) {
				isset($_POST['location']) && $map['location'] = $_POST['location'];	
			}
			$log_result = $this -> logcheck(array('id' => $id),'sj_extent_soft',$map,$model);
			if ($model->where($where)->save($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_soft/extent_id/'. $soft['extent_id']);
				$this->writelog("安卓游戏首页软件列表:编辑了软件[".$_POST['package']."]".$log_result, "",$_POST['package'], 'sj_extent_soft', $id,__ACTION__ ,'','edit');
				$this->success('编辑成功');
			}
		} else {
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList($soft['type']);
			$this->assign('typelist',$typelist);
			
			$this->assign('soft', $soft);
			$this->assign('address_edit_able', $address_edit_able);
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
			'status' => 0
		);
		$model = M('extent');
		
		$model->where($where)->save($map);
		$extent = $model->where($where)->find();
		
		if(isset($_REQUEST['parent_id'])){
		   $parent_id = (int)$_REQUEST['parent_id'];
		}else{
		   $parent_id = 0;
		}
		$where = array(
			'status' => 1,
			'parent_id' => $parent_id,
			'pid' => $extent['pid'],
		);
		
		
		$extent_list = $model->where($where)->order('rank ASC')-> select();
		$count = count($extent_list);
		for($i = 1;$i <= $count; $i++){
			$sql   = 'UPDATE __TABLE__ SET rank ='.$i.' WHERE `status` = 1 AND `parent_id` = '.$parent_id.' AND extent_id ='.$extent_list[$i-1]['extent_id']; 
			$model -> query($sql);
		}
		
		$this->writelog('安卓游戏首页软件列表:删除了id为'.$extent_id.'的区间', 'sj_extent', $extent_id,__ACTION__ ,'','del');
		$this->success('删除成功');
	}
	
	function move_soft()
	{
		$selected_ids = $_POST['selected_ids'];
		$extent_id = $_POST['extent_id'];
		$where = array(
			'id' => array('in' ,$selected_ids)
		);
		$model = M('extent_soft');
		$extent = $model->table('sj_extent')->where(array('extent_id' => $extent_id))->find();
		$map = array(
			'extent_id' => $extent_id,
		);
		if ($extent['location'] != '') {
			$map['location'] = $extent['location'];
		}
		
		
		$model->where($where)->save($map);
		$this->assign('jumpUrl', '/index.php/Sj/Extent/index');
		//$selected_ids = implode(',', $selected_ids);
		$this->writelog("将id为[{$selected_ids}]的软件移动到了区间{$extent_id}", 'sj_extent_soft', $selected_ids,__ACTION__ ,'','edit');
		$this->success('移动成功');
	}
	
	function del_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$map = array(
			'status' => 0
		);
		$model = M('extent_soft');
		$package = $model->where($where)->find();
		$model->where($where)->save($map);
		$this->writelog("安卓游戏首页软件列表：删除了id为[$id]包名为{$package['package']}的区间推荐软件", 'sj_extent_soft', $id,__ACTION__ ,'','del');
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
		
		$model = M('extent_soft');
		$result = $model->where($where)->field('sum(prob) as prob')->find();
		$total_prob = $result['prob'];
		
		$where = array(
			'extent_id' => $extent_id,
			'status' => 1,
		);
		$result = $model->table('sj_extent')->where($where)->find();
		$limit_prob = $result['extent_size'] * 100;
		echo $total_prob > $limit_prob ? 0: 1;
		$result = array(
			'total' => $total_prob,
			'max' => $limit_prob
		);
		exit(json_ecode($result));
	}
	
	function checkCandidateAct()
	{
		$package = $_REQUEST['package'];
		$where = array(
			'package' => $package
		);
		$model = M('extent_candidate');
		$soft = $model->where($where)->find();
		
		echo $soft ? 1 : 0;
	}
	
	function list_candidate_soft()
	{
		$model = M('extent_candidate');
		$srch_type = $_GET['srch_type'];
		$where = array(
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
		$count = $model->where($where)->count(); 
		
		import("@.ORG.Page");
		$p = new Page ( $count, 15 );
		$list = $model->limit($p->firstRow.','.$p->listRows)->where($where)->order('id desc')->findAll();
		$page = $p->show ();
		
		$list = $model->where($where)->order('`order` asc')->limit($p->firstRow.','.$p->listRows)->select();
		
		$package = array();
		$result = array();
		foreach($list as $val) {
			$package[] = $val['package'];
			$result[$val['package']] = $val;
		}
		$soft = $model->table('sj_soft')->where(array('package' => array('in', $package)))->field('softname,softid,package')->group('package')->select();
		foreach($soft as $val) {
			$result[$val['package']]['softname'] = $val['softname'];
			$result[$val['package']]['softid'] = $val['softid'];
		}
		
		
		
		$this->assign('srch_type', $srch_type);
		$this->assign('list', $result);
		$this->assign('isAjax', $this->isAjax());
		$this->assign('page', $page);
		$this->display();
	}
	
	function add_candidate_soft()
	{
		if (!empty($_POST)){
			$model = M('extent_candidate');
			$map = array();
			$map['status'] = 1;
			$map['created_at'] = time();
			$map['updated_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			
			isset($_POST['package']) && $map['package'] = $_POST['package'];
			isset($_POST['order']) && $map['order'] = $_POST['order'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			
			if ($id = $model->add($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_candidate_soft');
				$this->writelog("在推荐位备选库中添加了id为[{$id}]包名为[{$_POST['package']}]的备选软件", 'sj_extent_candidate', $id,__ACTION__ ,'','add');
				$this->success('添加成功');
			} else {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_candidate_soft');
				$this->error('添加失败');				
			}
		} else {
			$this->display();			
		}
	}
	
	function edit_candidate_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$model = M('extent_candidate');
		$soft = $model->where($where)->find();
		if (!empty($_POST)){
			$map = array();
			$map['updated_at'] = time();
			isset($_POST['package']) && $map['package'] = $_POST['package'];
			isset($_POST['order']) && $map['order'] = $_POST['order'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			
			if ($model->where($where)->save($map)) {
				$this->assign('jumpUrl', '/index.php/Sj/Extent/list_candidate_soft');
				$this->writelog("编辑了id为[{$id}]包名为{$_POST['package']}的备选软件", 'sj_extent_candidate', $id,__ACTION__ ,'','edit');
				$this->success('编辑成功');
			}
		} else {
			$this->assign('soft', $soft);
			$this->display();
		}
	}
	function del_candidate_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$map = array(
			'status' => 0
		);
		$model = M('extent_candidate');
		$package = $model->where($where)->find();
		$model->where($where)->save($map);
		$this->writelog("删除了id为[{$id}]包名为{$package['package']}的备选软件", 'sj_extent_candidate', $id,__ACTION__ ,'','del');
		$this->success('删除成功');
	}
	
	function pub_get_address()
	{
		$util = D("Sj.Util");
		$result = $util->getAddress();
		$json = json_encode($result);
		$this->assign('json', $json);
		$js = $this->fetch('get_address');
		header('content-type:application/x-javascript');
		exit($js);
	}
	
	function pub_check_extent_address()
	{
		$extent_id = $_POST['extent_id'];
		$model = new Model();
		$extent = $model->table('sj_extent')->where(array('extent_id' => $extent_id))->find();
		if (empty($extent['location']) && !empty($_POST['location'])) {
			$sql = "select count(*) as total from sj_extent_soft where extent_id='{$extent_id}' and location<>'' ";
			$res = $model->query($sql);
			if ($res[0]['total'] > 0) {
				exit('0');
			}
			if ($extent['type'] == 2) {
				$sql = "select count(*) as total from sj_extent where parent_id='{$extent_id}' and location<>'' ";
				$res = $model->query($sql);
				if ($res[0]['total'] > 0) {
					exit('0');
				}
			}
			
		} 
		exit('1');
	}
}
