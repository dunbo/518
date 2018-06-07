<?php
/*
*软件管理__认领管理
*
*
*
*
*
*
*
****************
******************
**************/
define('ABI_ARMEABI', 1);
define('ABI_ARMEABI_V7A', 2);
define('ABI_X86', 4);
define('ABI_MIPS', 8);
class AuthenticationAction extends CommonAction {
	//官方认证列表
	public function Official_certification_list(){
		$model = new Model();
		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
   		import('@.ORG.Page2');
		$where_soft = "a.status = 1 and a.hide = 1 and a.channel_id = '' and b.status != 0";
		$time = time();
		if(!empty($_GET['zh_type'])){
			if($_GET['zh_type'] == 1){
				$wheres['start_time'] = array('elt',$time);
				$wheres['terminal_time'] = array( 'egt',$time);
				$where_soft .= " and b.start_time <= {$time} and b.terminal_time >= {$time}";
				$this->assign("zh_type",$_GET['zh_type']);
			}else if($_GET['zh_type'] == 2){
				$wheres['terminal_time'] = array('lt',time());
				$where_soft .= " and b.terminal_time < {$time}";
				$this->assign("zh_type",$_GET['zh_type']);
			}else if($_GET['zh_type'] == 3){
				$wheres['start_time'] = array ('gt',time());
				$where_soft .= " and b.start_time > {$time}";
				$this->assign("zh_type",$_GET['zh_type']);
			}
		}

		if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
			$begintime = strtotime($_GET['begintime']);
			$endtime = strtotime($_GET['endtime']);
			$wheres['update_time'] = array(array('egt',$begintime),array('elt',$endtime));
			$where_soft .= " and ((b.update_time>= {$begintime}) and (b.update_time<= {$endtime}))";
			$this->assign('begintime', $_GET['begintime']);
			$this->assign('endtime', $_GET['endtime']);
		}
		if (isset($_GET['package'])){
			$package = $_GET['package'];
			$wheres['package'] = array("like","%{$package}%");
			$where_soft .=  " and b.package like '%{$package}%'";
			$this->assign('package', $package);
		}
		if (isset($_GET['softid'])){
			$softid = trim($_GET['softid']);
			$where['softid'] = array("eq","{$softid}");
			$where_soft .= " and a.softid = {$softid}";
			$this->assign('softid', $softid);
		}
		if (isset($_GET['softname'])){
			$softname = $_GET['softname'];
			$where['softname'] = array("like","%{$softname}%");
			$where_soft .= " and a.softname like '%{$softname}%'";
			$this->assign('softname', $softname);
		}
		if(isset($_GET['dev_name']) || isset($_GET['email']) || isset($_GET['dev_type'])){
			$where2['status'] =0;
			if(isset($_GET['email'])){
				$this->assign('email',$_GET['email']);
				$email = trim($_GET['email']);
				$where2['email'] = array("eq","{$email}");
			}
			if(isset($_GET['dev_name'])){	
				$this->assign('dev_name',$_GET['dev_name']);
				$where2['dev_name'] = array('like',"%{$_GET['dev_name']}%");
				$where['dev_name'] = array('like',"%{$_GET['dev_name']}%");
				//$where_soft .= ' and a.dev_name like "%'.$_GET['dev_name'].'%"';
			}
			if(isset($_GET['dev_type'])){
				$this->assign('dev_type',$_GET['dev_type']);
				$where2['type'] = array("eq","{$_GET['dev_type']}");
			}
			$devname = $model->table('pu_developer')->where($where2)->field('dev_id')->select();
			$dev_id = array();
			foreach ($devname as $n => $m ){
				$dev_id[] = $m['dev_id'];
			}
		//	if(!empty($dev_id)){
				$where['dev_id'] = array("in", implode(',', $dev_id));
				$where_soft .= " and a.dev_id in (".implode(',', $dev_id).")";
		//	}
		}
		//软件类别条件
		if(!empty($_GET['cateid'])){
			$cateids = explode(',',$_GET['cateid']);
			$cateid = array_flip($cateids);
			$this -> assign('cateid',$cateid);

			$cateidarr = array();
			foreach($cateids as $vv){
				if($vv != ''){
					$cateidarr[] = ",".$vv.",";
				}
			}
			$where['category_id'] = array('in',$cateidarr);
			$where_soft .= " and a.category_id in ({$cateidarr})";
		}
		//下载量搜索
		if(isset($_GET['uplode']) && isset($_GET['uplode1'])){
			$this->assign('uplode', $_GET['uplode']);
			$this->assign('uplode1', $_GET['uplode1']);	
			$where['_string'] = "total_downloaded+total_downloaded_add-total_downloaded_detain >= {$_GET['uplode']} and total_downloaded+total_downloaded_add-total_downloaded_detain <= {$_GET['uplode1']}";
			$where_soft .= " and a.total_downloaded+a.total_downloaded_add-a.total_downloaded_detain >= {$_GET['uplode']} and a.total_downloaded+a.total_downloaded_add-a.total_downloaded_detain <= {$_GET['uplode1']}";
		}	
		if(!empty($_GET['order'])){
			if($_GET['order']==1){
				if ($_GET['ordertype'] == 'update_time') {
					$order= array('b.update_time'=>'desc');
				} else {
					$order= array('total_downloaded'=>'desc');
				}
				$this->assign('order', 2);
			}else if($_GET['order']==2){
				if ($_GET['ordertype'] == 'update_time') {
					$order= array('b.update_time'=>'asc');
				} else {
					$order= array('total_downloaded'=>'asc');
				}
				$this->assign('order', 1);
			}
		} else {
			$this->assign('order', 2);	
		}

		$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
		$where['a.status'] = array('eq',1);
		$where['a.hide'] = array('eq',1);
		$where['a.channel_id'] = array('eq','');
		$wheres['status'] = array('neq',0);
		$where['b.status'] = array('neq',0);
		$sub_query = $model->table('sj_soft_note')->where($wheres)->order($order)->field('package')->buildSql();
		$where['a.package']=  array("exp","in ({$sub_query})");
		$param = http_build_query($_GET);
		//$totals = $model -> table('sj_soft') -> where($where) ->field('COUNT(DISTINCT package) AS tp_count')-> find();
		$totals = $model->table('sj_soft a LEFT JOIN sj_soft_note b ON a.package=b.package')->where($where_soft)->field('COUNT(DISTINCT a.package) AS tp_count')-> find();
		//$note_package['package'] = array("exp","in ({$sub_query})");
		//$total = $model -> table('sj_soft_note') ->where($note_package)->count();
		$total = $totals['tp_count'];
		$page = new Page($total, $limit, $param);
		$page->rollPage = 10; 
		$page->setConfig('header','条记录&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		$page->setconfig('first','首页');
		$page->setconfig('last','尾页');
		if (!$order) {
			$order = array('b.update_time' => 'desc');	
		}
		//old where name is $where modify by lipeng 2013829
		$softlist = $model -> table('sj_soft a LEFT JOIN sj_soft_note b ON a.package=b.package')->where($where_soft)->group('a.package')->order($order)->limit($page->firstRow.','.$page->listRows)->select();
		//整合数据
		$officialfetch = array();
		$categoryids = '';
		$devids = '';
		$ids = '';
		//sj_soft_note表中的数据
		$package_where = array();
		foreach ($softlist as $k => $v){
			$package_where[] = $v['package'];
		}
		$package_note['package'] = array("in",$package_where);
		$list = $model->table('sj_soft_note')->where($package_note)->field('type, update_time, package,start_time,terminal_time,status,official_note')->select();
		$note_list = array();
		foreach ($list as $key => $val){
			$note_list[$val['package']] = $val;
		}
		$jb_status = array(
			0 => '普通',
			1 => '首发',
			2 => '汉化',
			3 => '推荐',
			null => '无'
		);
		foreach ($softlist as $k => $v){
			$officialfetch[$k]['softid'] = $v['softid'];
			$officialfetch[$k]['softname'] = $v['softname'];
			$officialfetch[$k]['dev_name_soft'] = $v['dev_name'];
			$officialfetch[$k]['dev_id'] = $v['dev_id'];
			$officialfetch[$k]['update_type'] = $v['update_type'];
			$officialfetch[$k]['official_note'] = $v['official_note'];
			$officialfetch[$k]['jb'] = $jb_status[$note_list[$v['package']]['type']];
			$officialfetch[$k]['package'] = $note_list[$v['package']]['package'];
			$officialfetch[$k]['status'] = $note_list[$v['package']]['status'];
				if($note_list[$v['package']]['start_time'] == 0){
					$officialfetch[$k]['start_time'] = "------";
					$officialfetch[$k]['start_time1'] = date("Y-m-d H:i:s",time());
				}else{
					$officialfetch[$k]['start_time'] = date("Y-m-d H:i:s",$note_list[$v['package']]['start_time']);
				}
				if($note_list[$v['package']]['terminal_time'] == 0){
					$officialfetch[$k]['terminal_time'] ="------";
				}else{
					$officialfetch[$k]['terminal_time'] = date("Y-m-d H:i:s",$note_list[$v['package']]['terminal_time']);
				}
			if (!$note_list[$v['package']]['update_time']) {
				$officialfetch[$k]['update_time'] = "------";
			} else {
				$officialfetch[$k]['update_time'] = date("Y-m-d H:i:s",$note_list[$v['package']]['update_time']);;
			}
			$officialfetch[$k]['version'] = $v['version'];
			$officialfetch[$k]['version_code'] = $v['version_code'];
			$officialfetch[$k]['total_downloaded'] = $v['total_downloaded'];
			$officialfetch[$k]['total_downloaded_add'] = $v['total_downloaded_add'];
			$officialfetch[$k]['total_downloaded_detain'] = $v['total_downloaded_detain'];
			//剩余量
			$officialfetch[$k]['total_downloaded_surplus'] = ($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add']);
			if(($note_list[$v['package']]['start_time'] <= time())&&($note_list[$v['package']]['terminal_time'] >= time())){
				$officialfetch[$k]['type'] = 1; //当前显示
			}elseif($note_list[$v['package']]['terminal_time'] < time()){
				$officialfetch[$k]['type'] = 2;//过期
			}elseif($note_list[$v['package']]['start_time'] > time()){
				$officialfetch[$k]['type'] = 3;//未开始
			}else{
				$officialfetch[$k]['type'] = 0;
			}
			//abi显示
			foreach($known_abis as $abi_key => $abi_value){
				if($abi_value & $v['abi'] || $v['abi'] == 0){
					$officialfetch[$k]['abis'][] = $abi_key;
				}
			}
			$categoryids .= substr("{$v['category_id']}",1);
			if(!empty($v['dev_id'])){
				$devids .= "{$v['dev_id']}".",";
			}
			$ids .= "{$v['softid']}".",";
		}
		//类别名称
		$categoryid['status'] =1;
		$categoryid['category_id'] =array('in',substr($categoryids,0,-1));		
		$category = $model ->table('sj_category')->where($categoryid)->field('category_id,name,status')->select();
		$category_all = array();
		foreach($category as $val){
			$category_all[$val['category_id']] = $val['name'];
		}
		//开发者名称
	 	$dev['status'] = 0;
		$dev['dev_id'] = array('in',substr($devids,0,-1));
		$dev_name = $model->table('pu_developer')->where($dev)->field('dev_id,dev_name,type,email')->select();
		$dev_all = array();
		foreach($dev_name as $m){
			$dev_all[$m['dev_id']] = $m;
		}
		//软件图标
		$filewhere = array();
		$filewhere['softid'] = array('in',substr($ids,0,-1));
		$filewhere['package_status'] = array('gt',0);		
		$file = $model ->table('sj_soft_file')->where($filewhere)->field('id,softid,advertisement,leafletname,iconurl')->select();
		$filearr = array();
		foreach($file as $f){
			$filearr[$f['softid']] = $f['iconurl'] ;
		}
		// $softmodel = D('Dev.Softlist');
		// $iconlist = $softmodel -> new_icon_list('',$package_where);		
		foreach($softlist as $k => $v){
			$categoryid = substr("{$v['category_id']}",1,-1);
			$officialfetch[$k]['category_name'] = $category_all[$categoryid];
			//type 0公司 1个人 2团队
			if(!empty($v['dev_id'])){
				$officialfetch[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
				$officialfetch[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
				$officialfetch[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
			}
			// if($iconlist[1][$v['package']]['iconurl']){
				// $iconurl = $iconlist[1][$v['package']]['iconurl'];
			// }else{
				$iconurl = $filearr[$v['softid']];
			//}
			$officialfetch[$k]['iconurl'] = $iconurl;
		}
		//软件类别__弹出层展示
		$soft_tmp = D("Dev.Softaudit");
		$catname = $soft_tmp ->getCategoryArray();
		//var_dump($catname);
		$cname = array();
		foreach($catname[0] as $n){
			$threecat = array();
			foreach($catname[$n['category_id']] as $v){
				foreach( $catname[$v['category_id']] as $m){
					$threecat[] = $m;
				}
			}
			$n['sub'] = $threecat;
			$cname[] = $n;			
		}
		$this -> assign('cname',$cname);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$this->assign('officialfetch_list', $officialfetch);
		
		$param = $paramut = $_GET;
		if ($param['order'] == ''){ 
			$param['order'] = 2;
		}elseif($param['order'] == 1){
			$param['order'] = 2;
			$this -> assign('order',$param['order']);
		}elseif($param['order'] == 2){
			$param['order'] = 1;
		}

		if ($paramut['order'] == ''){ 
			$paramut['order'] = 2;
		}elseif($paramut['order'] == 1){
			$paramut['order'] = 2;
			$this -> assign('order',$paramut['order']);
		}elseif($paramut['order'] == 2){
			$paramut['order'] = 1;
		}
		$paramut['ordertype'] = 'update_time';
		unset($param['ordertype']);

		$param = http_build_query($param);
		$paramut = http_build_query($paramut);
		$this -> assign('paramut', $paramut);
		$this -> assign('param',$param);
		
		$this -> display();
	}
	//官方认证__删除
	public function del_official(){
		$Authentication = D("Dev.Authentication");
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));
		$status = 0;
		list($return,$add_result) = $Authentication -> edit_official_status($id_arr,$status);
		$package = '';
		foreach($add_result as $val){
			$package .= $val['package'].",";
		}
		foreach($return as $v){
			if($v == 2){
				exit(json_encode(array('code'=>'0','msg'=>'note表修改不成功')));
			}elseif($v == 4){				
				exit(json_encode(array('code'=>'0','msg'=>'sj_soft_note_single表修改不成功')));
			}elseif($v == 6){				
				exit(json_encode(array('code'=>'0','msg'=>"插入sj_soft_note数据失败
				")));
			}elseif($v == 7){				
				exit(json_encode(array('code'=>'0','msg'=>"插入sj_soft_note_single数据失败
				")));
			}else{
				$id_str = implode(',',$id_arr);
				$this->writelog('修改软件包名为' . substr($package,0,-1) . '的软件"官方认证状态"为0','sj_soft',$id_str,__ACTION__ ,'','edit');
				exit(json_encode(array('code'=>1,'msg'=>"删除成功")));
			}
		}
	}
	//官方认证__撤销
	public function Revocation_official(){
		$Authentication = D("Dev.Authentication");
		$model = new Model();
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}

		if(!$id_arr) exit(json_encode(array('code'=>'0','msg'=>'请选择要驳回的对象！')));
		
		$status = 2;
		list($return,$add_result) = $Authentication -> edit_official_status($id_arr,$status);
		$package = '';
		foreach($add_result as $val){
			$package .= $val['package'].",";
		}
		foreach($return as $key => $v){
			if($v == 2){
				exit(json_encode(array('code'=>'0','msg'=>"软件ID为{$key}note表修改不成功")));
			}elseif($v == 4){				
				exit(json_encode(array('code'=>'0','msg'=>"软件ID为{$key}note表修改不成功")));
			}elseif($v == 6){				
				exit(json_encode(array('code'=>'0','msg'=>"软件ID为{$key}插入sj_soft_note数据失败
				")));
			}elseif($v == 7){				
				exit(json_encode(array('code'=>'0','msg'=>"软件ID为{$key}插入sj_soft_note_single数据失败
				")));
			}else{
				$id_str = implode(',',$id_arr);
				$this->writelog('修改软件包名为' . substr($package,0,-1) . '的软件"官方认证状态"为2','sj_soft',$id_str,__ACTION__ ,'','edit');
				exit(json_encode(array('code'=>1,'msg'=>"撤销认证成功")));
			}
		}	
	}

	public function tv_certification_list(){
		$model = new Model();
		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
   		import('@.ORG.Page2');
		$where['status'] =1;
		$where['hide'] =1;
		$where['channel_id'] ='';
		if($_GET){
			if (isset($_GET['softid'])){
				$softid = trim($_GET['softid']);
				$where['softid'] = $softid;
				$this->assign('softid', $softid);
			}
			if (isset($_GET['softname'])){
				$softname = $_GET['softname'];
				$where['softname'] = array("like","%{$softname}%");
				$this->assign('softname', $softname);
			}	
			if (isset($_GET['package'])){
				$package = trim($_GET['package']);
				$where['package'] = $package;
				$this->assign('package', $package);
			}
			if(isset($_GET['dev_name']) || isset($_GET['email']) || isset($_GET['dev_type'])){
				$where2['status'] =0;
				if(isset($_GET['email'])){
					$this->assign('email',$_GET['email']);
					$email = trim($_GET['email']);
					$where2['email'] = array("eq","{$email}");
				}
				if(isset($_GET['dev_name'])){	
					$this->assign('dev_name',$_GET['dev_name']);
					$where2['dev_name'] = array('like',"%{$_GET['dev_name']}%");
				}
				if(isset($_GET['dev_type'])){
					$this->assign('dev_type',$_GET['dev_type']);
					$where2['type'] = array("eq","{$_GET['dev_type']}");
				}
				$devname = $model->table('pu_developer')->where($where2)->field('dev_id')->select();
				foreach ($devname as $n => $m ){
					$dev_id[] = $m['dev_id'];
				}
				if(!empty($dev_id)){
					$where['dev_id'] = array("in",$dev_id);
				}
			}
			if(isset($_GET['terrace'])){
				$where['terrace'] = $_GET['terrace'];
				$this->assign('terrace', $_GET['terrace']);
			}
			if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
				$where['category_id'] = array('in',$_GET['cateid']);
			}
		}
	
		$prove_time = array();
		$package_arr = array();
		$prove_result = $model->table('sj_prove_soft')->where('status=1')->group('package')->select();
		foreach ($prove_result as $key => $value) {
			# code...
			$package = $value['package'];
			$prove_time[$package] = $value['update_tm'];
			$package_arr[] = $value['package'];
		}
		if(!$_GET['package']){
			$where['package']=  array("in",$package_arr);
		}
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$total =  $model -> table('sj_soft') -> where($where) ->field('COUNT(package) AS tp_count')-> find();
		$page = new Page($total['tp_count'], $limit, $param);
		$softlist = $model -> table('sj_soft')->where($where)->order("last_refresh desc")->limit($page->firstRow.','.$page->listRows)->select();
		$page->rollPage = 10; 
		$page->setConfig('header','条记录&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		$page->setconfig('first','首页');
		$page->setconfig('last','尾页');

		//整合数据
		$softid = array();
		$officialfetch = array();
		$categoryids = '';
		$devids = '';
		$prove_package = array();
		foreach ($softlist as $k => $v){
			$officialfetch[$k]['softid'] = $v['softid'];
			$officialfetch[$k]['terrace'] = $v['terrace'];
			if ($v['terrace'] >= 1) {
				$prove_package[] = $v['package'];
			}
			$officialfetch[$k]['softname'] = $v['softname'];
			$officialfetch[$k]['dev_name_soft'] = $v['dev_name'];
			$officialfetch[$k]['dev_id'] = $v['dev_id'];
			$officialfetch[$k]['package'] = $v['package'];
			$officialfetch[$k]['version'] = $v['version'];
			$officialfetch[$k]['version_code'] = $v['version_code'];
			$officialfetch[$k]['total_downloaded'] = $v['total_downloaded'];
			$officialfetch[$k]['total_downloaded_add'] = $v['total_downloaded_add'];
			$officialfetch[$k]['total_downloaded_detain'] = $v['total_downloaded_detain'];
			//剩余量
			$officialfetch[$k]['total_downloaded_surplus'] = ($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add']);
			//abi显示
			foreach($known_abis as $abi_key => $abi_value){
				if($abi_value & $v['abi'] || $v['abi'] == 0){
					$officialfetch[$k]['abis'][] = $abi_key;
				}
			}
			$categoryids .= substr("{$v['category_id']}",1);
			if (!empty($v['dev_id']))$devids .= "{$v['dev_id']}".",";
			if(!empty($v['softid'])){
				$softid[] = $v['softid'];
			}
		}
		//类别名称
		$categoryid['status'] =1;
		$categoryid['category_id'] =array('in',substr($categoryids,0,-1));		
		$category = $model ->table('sj_category')->where($categoryid)->field('category_id,name,status')->select();
		$category_all = array();
		foreach($category as $val){
			$category_all[$val['category_id']] = $val['name'];
		}
		//开发者名称
	 	$dev['status'] = 0;
		$dev['dev_id'] = array('in',substr($devids,0,-1));
		$dev_name = $model->table('pu_developer')->where($dev)->field('dev_id,dev_name,type,email')->select();
		$dev_all = array();
		foreach($dev_name as $m){
			$dev_all[$m['dev_id']] = $m;
		}
		if($softid){
			//sj_soft_file表中的数据
			$file_where['softid']  = array('in',$softid);
			$file_where['package_status']  = array('exp'," > 0");
			$file = $model ->table('sj_soft_file')->where($file_where)->field('id,softid,advertisement,leafletname,iconurl,url,md5_file,sha1_file,ad_new')->select();
			$filearr = array();
			$md5sum_list = array();
			$sha1sum_list = array();
			$newicon_list = array();
			
			foreach($file as $key => $val){
				if($val['md5_file']){
					$md5sum_list[] = $val['md5_file'];
				}
				if($val['sha1_file']){
					$sha1sum_list[] = $val['sha1_file'];
				}
			}
			$soft_tmp = D("Dev.Softaudit");
			$md5_adinfo = $soft_tmp -> getAdsByHash($md5sum_list);
			$sha1_adinfo = $soft_tmp -> getAdsByHash($sha1sum_list);
			$scan_result_hash_tmp = $soft_tmp -> getbyhash($md5sum_list);
			$sha1sum_tmp = $soft_tmp -> getbyhash($sha1sum_list);
			
			foreach($file as $key => $val){
				$val['sha1_adinfo'] = $sha1_adinfo[$val['sha1_file']];
				$val['md5_adinfo'] = $md5_adinfo[$val['md5_file']];
				//安全扫描
				$val['sha1_adinfo_t'] = $sha1sum_tmp[$val['sha1_file']];
				$val['md5_adinfo_t'] = $scan_result_hash_tmp[$val['md5_file']];
				$filearr[$val['softid']] =  $val;
			}

		}		
		foreach($softlist as $k => $v){
			$categoryid = substr("{$v['category_id']}",1,-1);
			$officialfetch[$k]['category_name'] = $category_all[$categoryid];
			//type 0公司 1个人 2团队
			if (!empty($v['dev_id'])){
				$officialfetch[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
				$officialfetch[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
				$officialfetch[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
			}
			//icon
			$officialfetch[$k]['iconurl'] = $filearr[$v['softid']]['iconurl'];
			$officialfetch[$k]['url'] = $filearr[$v['softid']]['url'];
			//广告
			$officialfetch[$k]['advertisement'] = $soft_tmp->ad($filearr[$v['softid']]['ad_new']);
			//推广商
			if(!empty($filearr[$v['softid']]['sha1_adinfo'])){
				$officialfetch[$k]['scan'] = $filearr[$v['softid']]['sha1_adinfo'];
			}else{
				$officialfetch[$k]['scan'] = '';
			}
			
			if($filearr[$v['softid']]['md5_adinfo']){
				//积分、广告
				$officialfetch[$k]['scan1'] =  $filearr[$v['softid']]['md5_adinfo'];	
			}			
		}
		//软件类别__弹出层展示
		$soft_tmp = D("Dev.Softaudit");
		$catname = $soft_tmp ->getCategoryArray();
		//var_dump($catname);
		$cname = array();
		foreach($catname[0] as $n){
			$threecat = array();
			foreach($catname[$n['category_id']] as $v){
				foreach( $catname[$v['category_id']] as $m){
					$threecat[] = $m;
				}
			}
			$n['sub'] = $threecat;
			$cname[] = $n;			
		}

		$tvCategoryModel = M('prove_category');
		$res = $tvCategoryModel->where(array('status'=>1))->field('id,category_name,prove_type')->select();

		$provider = array(
			'1' => 'LG'
		);

		$category = array();
		foreach ($res as $key => $value) {
			# code...
			$prove_type = $value['prove_type'];
			!isset($category[$prove_type]) && $category[$prove_type] = array();
			$category[$prove_type][] = $value;
		}
		$this->assign('category', $category);
		$this->assign('provider', $provider);

		$this -> assign('cname',$cname);
		$this -> assign('prove_time',$prove_time);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total['tp_count']);
		$this->assign('officialfetch_list', $officialfetch);
		$this -> display();
	}
	function batch_download_pkg(){
		$model = new Model();
		$id_arr = explode(',',$_GET['softid']);
		$where = array(
			'softid' => array('in',$id_arr),
			'package_status' => array('exp'," > 0"),
			'url' =>  array('exp',"!= ''"),
		);
		$file = $model ->table('sj_soft_file')->where($where)->field('url')->select();
		$file_arr = array();
		foreach($file as $v){
			// if(!file_exists(UPLOAD_PATH .$v['url'])){
				// file_put_contents('/tmp/batch_download_pkg.log',$v['url'],FILE_APPEND);
				// continue;
			// }	
			//$file_arr[] = UPLOAD_PATH .$v['url'];
			$file_arr[] = IMGATT_HOST . $v['url'];
		}
		exit(json_encode($file_arr));
		//packZipDown('batch_download_pkg',$file_arr);
	}
	function passTvCheck()
	{
		if (!empty($_POST)) {
			$softid = $_POST['softid'];
			$soft_model = M('soft');
			$where = array(
				'softid' => $softid
			);
			$provier = array();
			$provier[] = $_POST['provider'];
			if(empty($provier[0]) || $provier[0] == 'undefined'){
				if($_POST['type'] ==1){
					exit(json_encode(array('code'=>0,'msg'=>"请选择厂商！")));
				}else{
					$this -> error('请选择厂商！');
				}
			}
			$soft = $soft_model->where($where)->find();
			$model = M('prove_soft');
			$flag = false;
			foreach ($provier as $key => $value) {
				# code...
				$package = $soft['package'];
				$category_id = $_POST['category_id_'. $value];
				$t_where = array(
					'package' => $package, 'category_id' => $category_id
				);
				$res = $model->where($t_where)->find();
				if (!$res) {
					$data = array(
						'package' => $package,
						'category_id' => $category_id,
						'create_tm' => time(),
						'status' => 1,
						'update_tm' => time()
					);
					$model->add($data);
				} else {
					$model->where($t_where)->save(array('update_tm'=>time()));
				}
				$flag = true;
			}
			if ($flag) {
				$data = array(
					'terrace' => 1,
					'last_refresh' => time()
				);
				$soft_model->where($where)->save($data);
				$this->writelog("tv认证了软件id为{$_POST['softid']}包名为{$package}", 'sj_soft',$_POST['softid'],__ACTION__ ,'','edit');
				//thickbox 关闭层
				if($_POST['type'] ==1){
					exit(json_encode(array('code'=>1,'msg'=>$softid)));
				}else{
					echo "<script>self.parent.window.location.reload();</script> ";
					$this->success('认证成功');	
				}				
			} else {
				$this->error('未选择认证选项，认证失败');
			}
		} else {
			$tvCategoryModel = M('prove_category');
			$res = $tvCategoryModel->where(array('status'=>1))->field('id,category_name,prove_type')->select();

			$provider = array(
				'1' => 'LG'
			);

			$category = array();
			foreach ($res as $key => $value) {
				# code...
				$prove_type = $value['prove_type'];
				!isset($category[$prove_type]) && $category[$prove_type] = array();
				$category[$prove_type][] = $value;
			}
			$this->assign('category', $category);
			$this->assign('provider', $provider);
			$this->assign('softid', $_GET['softid']);
			$this->assign('type', $_GET['type']);
			$this->display();
			
		}
	}
	//加入合作渠道
	function passTvCheck_arr(){
		//var_dump($_GET);exit;
		$tmp_id = $_POST['tmp_id'];	
		$soft_model = M('soft');
		$softids = explode(',',$tmp_id);
		$softwhere['softid'] = array('in',$softids);
		$softlist = $soft_model->where($softwhere)->field('terrace,softid')->select();
		$softid_str = '';
		foreach($softlist as $val){
			if($val['terrace'] ==1){
				$softid_str .= $val['softid'].',';
			}
		}
		if(!empty($softid_str)){
			if($_GET['type'] ==1){
				exit(json_encode(array('code'=>0,'msg'=>"ID为{$softid_str}已经认证通过了请不要重复认证！")));
			}else{
				$this -> error("ID为{$softid_str}已经认证通过了请不要重复认证");
			}
		}
		foreach($softids as $val){
			$where = array(
				'softid' => $val
			);
			$provier = array();
			$provier[] = $_POST['provider'];
			
			if(empty($provier[0]) || $provier[0] == 'undefined'){
				if($_GET['type'] ==1){
					exit(json_encode(array('code'=>0,'msg'=>"请选择厂商！")));
				}else{
					$this -> error('请选择厂商！');
				}
			}
			$soft = $soft_model->where($where)->find();
	
			$model = M('prove_soft');
			$flag = false;
			foreach ($provier as $key => $value) {
				# code...
				$package = $soft['package'];
				$category_id = $_POST['category_id_'. $value];
				$t_where = array(
					'package' => $package, 'category_id' => $category_id
				);
				$res = $model->where($t_where)->find();
				if (!$res) {
					$data = array(
						'package' => $package,
						'category_id' => $category_id,
						'create_tm' => time(),
						'status' => 1,
						'update_tm' => time()
					);
					$model->add($data);
				} else {
					$model->where($t_where)->save(array('update_tm'=>time()));
				}
				$flag = true;
			}
			if ($flag) {
				$data = array(
					'terrace' => 1,
					//不许去掉last_refresh
					'last_refresh' => time()
				);
				$soft_model->where($where)->save($data);
				$this->writelog("tv认证了软件id为{$val}包名为{$package}",'sj_soft',$val,__ACTION__ ,'','edit');
			} 
		}
		if($_GET['type'] ==1){
			exit(json_encode(array('code'=>1,'msg'=>$softids)));
		}else{
			$this->success('认证成功');	
		}
	}
	function ignoreTvCheck()
	{
		$softid = $_GET['softid'];
		$model = M('soft');
		$softid_arr = explode(',',$softid);
		$where['softid'] = array('in',$softid_arr);
		$softlist = $model->where($where)->field('terrace,softid')->select();
		$softid_str = '';
		foreach($softlist as $val){
			if($val['terrace'] == -1){
				$softid_str .= $val['softid'].',';
			}
		}
		if(!empty($softid_str)){
			exit(json_encode(array('code'=>0,'msg'=>"ID为{$softid_str}已经忽略了请不要重复忽略！")));
		}
		$model->where($where)->save(array('terrace' => -1));
		$this->writelog("忽略软件id为{$_GET['softid']}的tv认证",'sj_soft',$_GET['softid'],__ACTION__ ,'','edit');
		exit(json_encode(array('code'=>1,'msg'=>$softid_arr)));
	}
}
?>
