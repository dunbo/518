<?php
class WidgtAction extends CommonAction {
  function lists(){
	$model = new Model();
	//运营商列表
	$op_list = $model -> table('pu_operating') -> select();
	import('@.ORG.Page');
	$where['_string'] = 'status = 1';
	if($_GET['search'] == 1){
		$where['_string'] .= $_GET['oid'] ? ' and oid = '.$_GET['oid'] : '';
		$where['_string'] .= $_GET['cid'] ? ' and cid = '.$_GET['cid'] : '';
		$where['_string'] .= $_GET['did'] ? ' and did = '.$_GET['did'] : '';
		$where['_string'] .= $_GET['type'] ? ' and type = '.$_GET['type'] : '';
		$where['_string'] .= $_GET['cateid'] ? ' and cateid = '.$_GET['cateid'] : '';
	}
	$count = $model -> table('sj_widge') -> where($where)-> count();
	$page = new Page($count,50);
	$widgt_list = $model -> table('sj_widge') -> where($where) -> order('create_tm desc')->limit($page->firstRow.','.$page->listRows) ->select();
	$count = count($widgt_list);
	$this -> assign('op_list',$op_list);
	foreach($widgt_list as $idx => $info){
	$owhere['_string'] = 'oid = '.$info['oid'];
	$dwhere['_string'] = 'did = '.$info['did'].' and status = 1';
	$cwhere['_string'] = 'cid = '.$info['cid'].' and status = 1';
	$oinfo = $model -> table('pu_operating') -> where($owhere) -> select();
	$dinfo = $model -> table('pu_device') -> where($dwhere) -> select();
	$cinfo = $model -> table('sj_channel') -> where($cwhere) -> select();
	$tab =  ($info['type'] == 1) ? 'sj_feature' : 'sj_category';
	if($tab == 'sj_feature'){
	$w['_string']= 'feature_id ='.$info['cateid'].' and status = 1';
	$cateinfo = $model -> table($tab) -> where($w) -> select();
	}else{
	$w['_string'] = 'category_id ='.$info['cateid'].' and status = 1';
	$cateinfo = $model -> table($tab) -> where($w) -> select();	
	}
	$widgt_list[$idx]['typename'] = $cateinfo[0]['name'];
	$widgt_list[$idx]['oname'] = $oinfo[0]['mname'];
	$widgt_list[$idx]['dname'] = $dinfo[0]['dname'];
	$widgt_list[$idx]['cname'] = $cinfo[0]['chname'];
	}
	$this -> assign('widgt_list',$widgt_list);
	$this -> assign('page',$page->show());
	$this -> display('lists');
  }
  //渠道列表
  function ajaxChlList(){
	$model = new Model();
	$chname = $_POST['cname'];
	$where['_string'] = "chname like '%".$chname."%' and status = 1";
	$clist = $model -> table('sj_channel') -> where($where) -> select();
	$out = "<option value='0'>默认不选</option>\n";
	foreach($clist as $info){
	  $out .= "<option value='{$info['cid']}'>{$info['chname']}</option>\n";
	}
	echo $out;
  }
  function ajaxDeviceList(){
  	$model = new Model();
	$dname = $_POST['dname'];
	$where['_string'] = "dname like '%".$dname."%' and status = 1";
	$dlist = $model -> table('pu_device') -> where($where) -> select();
	$out = "<option value='0'>默认不选</option>\n";
	foreach($dlist as $info){
	  $out .= "<option value='{$info['did']}'>{$info['dname']}</option>\n";
	}
	echo $out;
  }
  function ajaxCategory(){
	$type_id = $_POST['id'];
	$model = new Model();
	if($type_id == 1){
	$table_name = 'sj_feature';
	}else{
	$table_name = 'sj_category';
	}
	$where['_string'] = 'status = 1';
	$list = $model -> table($table_name) -> where($where) -> select();
	$out = "<option value=''>请选择</option>\n";
	if($type_id == 1){
	  foreach($list as $info){
	    $out .= "<option value='{$info['feature_id']}'>{$info['name']}</option>\n";
	  }
	}else{
	  foreach($list as $info){
		if($info['parentid'] == 0 ){
			$clist[$info['category_id']]= array();
			$cp[$info['category_id']] = $info['name'];
		}else{
			$clist[$info['parentid']][] = $info;
		}
	  }	
	  foreach($clist as $key => $info){
		$out .= "<optgroup label='".$cp[$key]."'>\n";
		foreach($info as $val){
	  	$out .= "<option value='{$val['category_id']}'>{$val['name']}</option>\n";
		}
		$out .="</optgroup>\n";
	  }
	}
	echo $out;
  } 
  function addwidgt(){
		$model = new Model();
		$tabel_name = 'sj_widge';
		$where['oid'] = $_POST['oid'] ? $_POST['oid'] : 0;
		$where['cid'] = $_POST['cid'] ? $_POST['cid'] : 0;
		$where['did'] = $_POST['did'] ? $_POST['did'] : 0;
		$where['status'] = 1;
		$count = $model -> table($tabel_name) -> where($where) ->count(); 
		if($count > 0){
		$this -> error('该widget已经存在');
		}
		$data = array();
		$data['oid'] = $_POST['oid'] ? $_POST['oid'] : 0;
		$data['cid'] = $_POST['cid'] ? $_POST['cid'] : 0;
		$data['did'] = $_POST['did'] ? $_POST['did'] : 0;
		$data['status'] = 1;
		$data['type'] = $_POST['type'];
		$data['cateid'] = $_POST['cateid'];
		$data['cnt'] = $_POST['cnt'];
		$data['create_tm'] = time();
		$affect = $model -> table($tabel_name) -> add($data);
		if($affect > 0){
			$this -> writelog('widgt管理_widgt添加_在 sj_widge 添加 id为'.$affect.'信息','sj_widge',$affect,__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/Sj/Widgt/lists');
			$this->success('数据更新成功');
		}else{
			$this -> error('数据更新失败');
		}
  }
  function addwidge(){
	$model = new Model();
	//运营商列表
	$op_list = $model -> table('pu_operating') -> select();
	$this -> assign('op_list',$op_list);
	$this -> display('addwidge');
  }
  function delwidgt(){
	$id = $_GET['id'];
	$model = new Model();
	$where['id'] = $id;
	$data['status'] = 0;
	$data['create_tm'] = time();
	$affect = $model -> table('sj_widge') -> where($where) -> save($data);
	if($affect){
	 $this -> writelog('widgt管理_widgt列表_在 sj_widge 删除 id 为'.$id.'信息','sj_widge',$id,__ACTION__ ,"","del");
	 $this->assign('jumpUrl','/index.php/Sj/Widgt/lists');
	 $this->success('数据删除成功');	 
	}else{
		$this -> error('数据删除失败');
	}
  }
  function categorysoftlist(){
	$category_id = $_GET['id'];
	$type = $_GET['type'];
	$model = new Model();
	if($type != 1){
		$soft_table = 'sj_soft';
		$where['category_id'] = ','.$category_id.','; 
		$cwhere['_string'] = 'category_id = '.$category_id.' and status = 1';
		$cateinfo = $model -> table('sj_category') -> where($cwhere) -> select();
	}else{
		$soft_table = 'sj_feature_soft';
		$where['_string'] = 'feature_id ='.$category_id.' and status = 1'; 
		$pkginfo = $model -> table($soft_table) -> where($where) -> select();
		$pkg_arr = array();
		foreach($pkginfo as $info){
			$pkg_arr[] = $info['package'];
		}
		unset($where['_string']);
		$where['package'] = array('IN',"'".implode("','",$pkg_arr)."'");
		$cwhere['_string'] = 'feature_id = '.$category_id.' and status = 1';
		$cateinfo = $model -> table('sj_feature') -> where($cwhere) -> select();
	}
 	if(isset($_GET['softname']) && isset($_GET['id'])){
		$softname = $_GET['softname'];
		$where['softname'] = array("like",'%'.$softname.'%');
		$this -> assign('softname',$softname);
	} 
    $where['status'] = 1;
    $where['hide'] = 1;
	$count = $model -> table('sj_soft') -> where($where)->count();
	import('@.ORG.Page');
	$page = new Page($count,50);
	$softlist = $model -> table('sj_soft') -> where($where) -> limit($page -> firstRow.','.$page -> listRows) -> select();
	$this -> assign('softlist',$softlist);
	$catename = $cateinfo[0]['name'];
	$this -> assign('page',$page->show());
	$this -> assign('catename',$catename);
	$this -> assign('category',$category_id);
	$this -> assign('type',$type);
	$this -> display('category_soft_list');
  }
  function editwidgt(){
	$model = new Model();
	$id = $_GET['id'];
	$widge_info = $model -> table('sj_widge') -> where('id = '.$id) -> select();
	$info = $widge_info[0];
	$owhere['_string'] = 'oid = '.$info['oid'];
	$dwhere['_string'] = 'did = '.$info['did'].' and status = 1';
	$cwhere['_string'] = 'cid = '.$info['cid'].' and status = 1';
	$oinfo = $model -> table('pu_operating') -> where($owhere) -> select();
	$dinfo = $model -> table('pu_device') -> where($dwhere) -> select();
	$cinfo = $model -> table('sj_channel') -> where($cwhere) -> select();
	if($info['type'] == 1){
		$finfo = $model -> table('sj_feature') -> where('feature_id = '.$info['cateid'].' and status = 1') -> select();
		$cname = $finfo[0]['name'];
	}else{
		$cainfo = $model -> table('sj_category') -> where('category_id = '.$info['cateid'].' and status = 1') -> select();
		$cname = $cainfo[0]['name'];
	}
	$op_list = $model -> table('pu_operating') -> select();
	$this -> assign('op_list',$op_list);
	$this -> assign('widge',$widge_info[0]);
	$this -> assign('oinfo',$oinfo[0]);
	$this -> assign('cinfo',$cinfo[0]);
	$this -> assign('dinfo',$dinfo[0]);
	$this -> assign('cname',$cname);
	$this -> display('editwidgt');
  }
  function editwidgt_do(){
  		$model = new Model();
		$tabel_name = 'sj_widge';
		$where['id'] = $_POST['id'];
		$data = array();
		$data['status'] = 1;
		 if($_POST['type'] && $_POST['cateid']) $data['type'] = $_POST['type'];
		if($_POST['cateid']) $data['cateid'] = $_POST['cateid'];
		$data['create_tm'] = time();
		$data['oid'] = $_POST['oid'] ? $_POST['oid'] : 0;
		$data['cid'] = $_POST['cid'] ? $_POST['cid'] : 0;
		$data['did'] = $_POST['did'] ? $_POST['did'] : 0;
		$data['cnt'] = $_POST['cnt'];
		$log = $this -> logcheck(array('id' =>$_POST['id']),'sj_widge',$data,$model);
		$affect = $model -> table($tabel_name) -> where($where)  -> save($data);
		if($affect > 0){

			$this -> writelog('widgt管理-widge列表在 sj_widge 修改 id为'.$_POST['id'].'信息'.$log,'sj_widge',$_POST['id'],__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/Sj/Widgt/lists');
			$this->success('数据更新成功');
		}else{
			$this -> error('数据更新失败！');
		}
  }
	//对战游戏管理  added by shitingting
	function fighting_game_list()
	{
        $model = M('sj_fighting_game');
		import("@.ORG.Page");
		$count = $model->table('sj_fighting_game')->where(array('status' => 1))->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$result = $model -> table('sj_fighting_game') -> where(array('status' => 1)) -> order('rank asc,create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> display();
	}
	function add_fighting_game()
	{
		if($_POST)
		{
			$model=M('fighting_game');
			$soft_model = M("soft");//软件大表
			$rank= trim($_POST['rank']);
			$package=trim($_POST['package']);
			$soft_name=trim($_POST['soft_name']);
			$game_description=trim($_POST['game_description']);
			$support_person=trim($_POST['support_person']);
			$online_type=trim($_POST['online_type']);
			$wanip=trim($_POST['wanip']);
			$wanport=trim($_POST['wanport']);
			$is_recommend=trim($_POST['is_recommend']);
			if($rank=="")
			{
			 $this -> error("排序不能为空");
			}
			else
			{
				$rank1=floatval($rank);
				if($rank1<=0 || $rank1!=floor($rank1)||(string)floatval($rank)!=$rank)
				{
				  $this -> error("排序必须是大于0的整数");
				}
			}
			if($support_person=="")
			{
			  $this -> error("支持人数不能为空");
			}
			else
			{   
				$_march="/^[0-9-,\[\]]+$/";		
				if(!preg_match($_march,$support_person)) 
				{  
					$this->error('填写支持人数的格式不正确，请重新填写');  
				} 	
				/*if($this->strlen_az($support_person,'utf-8')>8)
				{
					$this -> error("支持人数长度不能超过10");
				} */
			}
			if($online_type)
			{
				$_march="/^[0-1]{3}$/";
				if(!preg_match($_march,$online_type)) 
				{
					$this->error('填写联机模式错误，请重新填写！'); 
				}
			}
			if($wanport)
			{
				$_march="/^[0-9]+$/";
				if(!preg_match($_march,$wanport)) 
				{
					$this->error('填写游戏广域网连接服务器端口号必须是整数！'); 
				}
			}
			if (isset($package) && $package != "") 
			{
				$where = array(
					'package' => $package,
					'status' => 1,
					'hide' => array('in', array(1,1024)),
				);
				$where_have= array(
					'package' => $package,
					'status' => 1,
				);
				$find = $soft_model->where($where)->find();
				$result=$model->where($where_have)->select();
				if (!$find) 
				{
					$this->error("包名【{$package}】不存在于市场软件库中;");
				}
				if($result)
				{
					$this->error("包名【{$package}】已存在对战游戏列表中，请重新填写");
				}
			} 
			else 
			{
				$this->error("包名不能为空;");
			}
			if($game_description)
			{
			  if(mb_strlen($game_description,'utf8')>100)
			  {
				$this -> error("游戏描述为1-100个字");
			  }
			}
			$data=array(
			 'package'=>$package,
			 'soft_name'=>$soft_name,
			 'game_description'=>$game_description,
			 'create_tm'=>time(),
			 'update_tm'=>time(),
			 'rank'=>$rank,
			 'stat'=>trim($_POST['stat']),
			 'status'=>1,
			 'support_person'=>$support_person,
			 'online_type'=>$online_type,
			 'wanip'=>$wanip,
			 'wanport'=>$wanport,
			 'is_recommend'=>$is_recommend,
			 'war_type'=>$_POST['war_type'],
			);
			$affect = $model -> table('sj_fighting_game') -> add($data);

			if($affect)
			{
			 $this -> writelog('对战游戏管理添加  在sj_fighting_game添加了id为【'.$affect.'】包名为【'.$package.'】的信息','sj_fighting_game',$affect,__ACTION__ ,"","add");
			 $this->assign('jumpUrl','/index.php/Sj/Widgt/fighting_game_list');
			 $this->success('数据添加成功');	 
			}
			else
			{
				$this -> error('数据添加失败');
			}
		}
		else
		{
			$this->display();
		}
	}
	function modify_fighting_game()
	{
		if($_POST)
		{
			$model = M('fighting_game');
			$soft_model = M("soft");//软件大表
			$id = $_POST['id'];
			$soft_name = trim($_POST['soft_name']);
			$rank =floatval($_POST['rank']);
			$package=trim($_POST['package']);
			$game_description = trim($_POST['game_description']);
			$support_person=trim($_POST['support_person']);
			$online_type=trim($_POST['online_type']);
			$wanip=trim($_POST['wanip']);
			$wanport=trim($_POST['wanport']);
			$is_recommend=trim($_POST['is_recommend']);
			if($rank=="")
			{
			 $this -> error("排序不能为空");
			}
			else
			{
				$rank1=floatval($rank);
				if($rank1<=0 || $rank1!=floor($rank1)||(string)floatval($rank)!=$rank)
				{
				  $this -> error("排序必须是大于0的整数");
				}
			}
			if($support_person=="")
			{
			 $this -> error("支持人数不能为空");
			}
			else
			{
				$_march="/^[0-9-,\[\]]+$/";		
				if(!preg_match($_march,$support_person)) 
				{  
					$this->error('填写支持人数的格式不正确，请重新填写');  
				} 	
				/*if($this->strlen_az($support_person,'utf-8')>8)
				{
					$this -> error("支持人数长度不能超过10");
				} */
			}
			if($online_type)
			{
				$_march="/^[0-1]{3}$/";
				if(!preg_match($_march,$online_type)) 
				{
					$this->error('填写联机模式错误，请重新填写！'); 
				}
			}
			if($wanport)
			{
				$_march="/^[0-9]+$/";
				if(!preg_match($_march,$wanport)) 
				{
					$this->error('填写游戏广域网连接服务器端口号必须是整数！'); 
				}
			}
			if (isset($package) && $package != "") 
			{
				$where = array(
					'package' => $package,
					'status' => 1,
					'hide' => array('in', array(0, 1, 1024)),
				);
				$where_have= array(
					'package' => $package,
					'status' => 1,
					'id'=>array('neq',$id),
				);
				$find = $soft_model->where($where)->find();
				$result=$model->where($where_have)->select();
				if (!$find) 
				{
					$this->error("包名【{$package}】不存在于市场软件库中;");
				}
				if($result)
				{
					$this->error("包名【{$package}】已存在对战游戏列表中，请重新填写");
				}
			} 
			else 
			{
				$this->error("包名不能为空;");
			}
			if($game_description)
			{
			  if(mb_strlen($game_description,'utf8')>100)
			  {
				$this -> error("游戏描述为1-100个字");
			  }
			}
			$data=array(
				'package'=>$package,
				'soft_name'=>$soft_name,
				'game_description'=>$game_description,
				'update_tm'=>time(),
				'rank'=>$rank,
				'stat'=>trim($_POST['stat']),
				'support_person'=>$support_person,
				'online_type'=>$online_type,
				'wanip'=>$wanip,
				'wanport'=>$wanport,
				'is_recommend'=>$is_recommend,
				'war_type'=>$_POST['war_type'],
			);
			$log_result = $this -> logcheck(array('id' => $id),'sj_fighting_game',$data,$model);
			$result = $model -> table('sj_fighting_game') -> where(array('id' => $id)) -> save($data);
			if($result)
			{
				$this -> writelog("已编辑id为【{$id}】的对战游戏信息".$log_result,'sj_fighting_game',$id,__ACTION__ ,"","edit");
				$this -> assign("jumpUrl","/index.php/index.php/Sj/Widgt/fighting_game_list");
				$this -> success("编辑成功");
			}else
			{
				$this -> error("编辑失败");
			}
		}
		else
		{
			$model = M('fighting_game');
			$id = $_GET['id'];
			$result = $model -> table('sj_fighting_game') -> where(array('id' => $id)) -> find();
			$this -> assign('result',$result);
			$this -> display();
		}
	}
	function delete_fighting_game()
	{
		$model = M('sj_fighting_game');
		$id = $_GET['id'];
		$result = $model -> table('sj_fighting_game') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为【{$id}】的游戏对战记录",'sj_fighting_game',$id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/index.php/Sj/Widgt/fighting_game_list");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}	
	}
    function change_rank()
    {
		$model = M('sj_fighting_game');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data = array(
			'rank' => $rank,
			'update_tm' => time()
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_fighting_game',$data,$model);
		$result = $model -> table('sj_fighting_game') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已修改id为【{$id}】的游戏排序".$log_result,'sj_fighting_game',$id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function change_stat()
    {
		$model = M('sj_fighting_game');
		$id = $_GET['id'];
		$stat=$_GET['stat'];
		$data = array(
			'stat' => $stat,
			'update_tm' => time()
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_fighting_game',$data,$model);
		$result = $model -> table('sj_fighting_game') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已修改id为【{$id}】的游戏上下线状态".$log_result,'sj_fighting_game',$id,__ACTION__ ,"","edit");
			$this -> assign("jumpUrl","/index.php/index.php/Sj/Widgt/fighting_game_list");
			if($stat==1)
			{
			 $this -> success("上线成功");
			}
			else
			{
			 $this -> success("下线成功");
			}
		    
		}
		else
		{
		    if($stat==1)
			{
			 $this -> success("上线失败");
			}
			else
			{
			 $this -> success("下线失败");
			}
		}
	}
	//昵称库管理  
	function nickname_list()
	{
        $model = M('sj_nickname');
		import("@.ORG.Page");
		$count = $model->table('sj_nickname')->where(array('status' => 1))->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$result = $model -> table('sj_nickname') -> where(array('status' => 1)) -> order('create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> display();
	}
  function add_nickname()
  {
    if($_POST)
	{
	    $model=M('sj_nickname');
	    $nickname= trim($_POST['nickname']);
		if($nickname=="")
		{
		 $this -> error("昵称不能为空");
		}
		else
		{
		    $_march="/^[a-z0-9A-Z\x{4e00}-\x{9fa5}]+$/u";		
            if(!preg_match($_march,$nickname)) 
			{  
                $this->error('昵称非法，请重新输入正确的昵称');  
            } 	
		  if($this->strlen_az($nickname,'utf-8')>8)
		  {
			$this -> error("昵称为1-4个字");
		  }
          // 检查昵称重名
            $conflict_where = array(
                'status' => 1,
                'nickname' => $nickname,
            );
            $conflict_find = $model->table('sj_nickname')->where($conflict_where)->find();
            if ($conflict_find) 
			{
                $this->error("昵称与id为{$conflict_find['id']}的昵称重复！");
            }  
		}
		$data=array(
		 'nickname'=>$nickname,
		 'create_tm'=>time(),
		 'update_tm'=>time(),
		 'status'=>1,
		);
		$affect = $model -> table('sj_nickname') -> add($data);
		if($affect)
		{
		 $this -> writelog('昵称管理_在sj_nickname添加了id为【' .$affect. '】昵称为【' .$nickname. '】的名称','sj_nickname',$affect,__ACTION__ ,"","add");
		 $this->assign('jumpUrl','/index.php/Sj/Widgt/nickname_list');
		 $this->success('数据添加成功');	 
		}
		else
		{
			$this -> error('数据添加失败');
		}
	}
	else
	{
     $this->display();
	}
  }
  function modify_nickname()
  {
    if($_POST)
	{
		  $model = M('sj_nickname');
		  $id = $_POST['id'];
		  $nickname = $_POST['nickname'];
		  if($nickname=="")
			{
			 $this -> error("昵称不能为空");
			}
			else
			{
			  $_march="/^[a-z0-9A-Z\x{4e00}-\x{9fa5}]+$/u";		  
			  if(!preg_match($_march, $nickname)) 
			    {  
					$this->error('昵称非法，请重新输入正确的昵称');  
				}
              if($this->strlen_az($nickname,'utf-8')>8)
                {
				 $this -> error("昵称为1-4个字");
				}	   
 				// 检查昵称重名
				$conflict_where = array(
					'status' => 1,
					'nickname' => $nickname,
					'id' => array('neq', $id),
				);
				$conflict_find = $model->table('sj_nickname')->where($conflict_where)->find();
				if ($conflict_find) 
				{
					$this->error("昵称与id为【{$conflict_find['id']}】的昵称重复！");
				}  
			}
			  $data=array(
			 'nickname'=>$nickname,
			 'update_tm'=>time(),
			);
			$log_result = $this -> logcheck(array('id' => $id),'sj_nickname',$data,$model);
			$result = $model -> table('sj_nickname') -> where(array('id' => $id)) -> save($data);
			if($result)
			{
				$this -> writelog("已修改id为【{$id}】的昵称".$log_result,'sj_nickname',$id,__ACTION__ ,"","edit");
				$this -> assign("jumpUrl","/index.php/Sj/Widgt/nickname_list");
				$this -> success("编辑成功");
			}else
			{
				$this -> error("编辑失败");
			}
	}
	else
	{
	  $model = M('sj_nickname');
	  $id = $_GET['id'];
	  $result = $model -> table('sj_nickname') -> where(array('id' => $id)) -> find();
	  $this -> assign('result',$result);
	  $this -> display();
	}
  }
  function delete_nickname()
  {
	$model = M('sj_nickname');
	$id = $_GET['id'];
	$result = $model -> table('sj_nickname') -> where(array('id' => $id)) -> save(array('status' => 0));
	if($result){
		$this -> writelog("已删除id为【{$id}】的昵称",'sj_nickname',$id,__ACTION__ ,"","del");
		$this -> assign("jumpUrl","/index.php/Sj/Widgt/nickname_list");
		$this -> success("删除成功");
	}else{
		$this -> error("删除失败");
	}	
  }
}
