<?php
/**
 * 安智网产品管理平台 渠道管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:高硕 2011.5.17
 * ----------------------------------------------------------------------------
*/
class ChannelsAction extends CommonAction {

    private $channel_db;     //渠道表
    private $channel_list;   //渠道列表
    private $cid;            //渠道CID
    private $soft_db;        //软件表
    private $soft_list;      //软件列表
    //渠道管理_渠道列表
	function channels(){

        define("GO_HIDE_SKIN", 4);
        $Form = M("channel");
        import("@.ORG.Page");
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);
        $source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_SHOW_TYPE;
        $map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $res = $Form->table('sj_admin_filter')->where($map)->find();
        $show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
        $this->assign( "show_chl", $show_chl );

        $chl = '';
        if(isset($_REQUEST['chl'])&&$_REQUEST['chl']){
        	$chl = $_REQUEST['chl'];
        }
        $chname = '';
		if(isset($_REQUEST['chname'])&&$_REQUEST['chname']){
        	$chname = $_REQUEST['chname'];
        }
		$chl_cid = '';
		if(isset($_REQUEST['chl_cid'])&&$_REQUEST['chl_cid']){
        	$chl_cid = $_REQUEST['chl_cid'];
        }
		$only_auth = '';
        if(isset($_REQUEST['only_auth'])){
        	$only_auth = $_REQUEST['only_auth'];
			$this->assign('only_auth', $only_auth);
        }
		$update_select = '';
		if(isset($_REQUEST['update_select'])){
			$update_select = $_REQUEST['update_select'];
			$this->assign('update_select',$update_select);
		}
		$activation_type = '';
		if(isset($_REQUEST['activation_type']) && $_REQUEST['activation_type']){
			$activation_type = $_REQUEST['activation_type'];
			$this->assign('activation_type',$activation_type);
		}
	
        $category_id = '';
		if(isset($_REQUEST['category_id'])&& $_REQUEST['category_id']!=''){
        	$category_id = $_REQUEST['category_id'];
        	$this->assign('category_id', $category_id);
        }
		
		$soft_update_select = '';
		if(isset($_REQUEST['soft_update_select'])){
			$soft_update_select = $_REQUEST['soft_update_select'];
			$this->assign('soft_update_select',$soft_update_select);
		}
		
        $source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql_where=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			//$target_type= CHANNEL_SHOW_CONTROL;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql_where = " cid in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}')  and  ";
		}
		$sql_where .= 'status=1';
		if($chl){
			$sql_where .= " and chl='{$chl}'";
		}
		if($update_select!=''){
			$sql_where .= " and alone_update='{$update_select}'";
		}
		if($soft_update_select!=''){
			$sql_where .= " and soft_update='{$soft_update_select}'";
		}
		if($activation_type!=''){
			$sql_where .= " and activation_type='{$activation_type}'";
		}
		
		if($chname){
			$sql_where .= " and chname like '%{$chname}%'";
		}
		if($chl_cid){
			$sql_where .= " and chl_cid like '%{$chl_cid}%'";
		}
		if($category_id!=''){
			$sql_where .= " and category_id = '{$category_id}'";
		}
		
		if($only_auth!=''){
			$sql_where .= " and only_auth = '{$only_auth}'";
		}
		$platform = '';
        if(isset($_REQUEST['platform'])&&$_REQUEST['platform']){
        	$platform = escape_string($_REQUEST['platform']);
			$sql_where .= " and platform = '{$platform}'";
			
			$this -> assign('platform', $_REQUEST['platform']);
        }
		
        /*$sql = "select count(cid) as total from sj_channel where {$sql_where}";
		$sql=mysql_real_escape_string($sql);
        $res = $Form->query($sql);
        $count = $res[0]['total'];*/
		
		$count = $Form -> where($sql_where) ->count();
        $p=new Page($count,20);

        //$sql = "select * from sj_channel {$sql_where}";
        $list=$Form->where($sql_where)->limit($p->firstRow.','.$p->listRows)->order('submit_tm desc')->select();
        //$list = $Form->query($sql);
        if($chl) $this -> assign('chl', $chl);
        if($chname) $this -> assign('chname', $chname);
        if($chl_cid) $this -> assign('chl_cid', $chl_cid);
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        $this->assign("page", $page);
        foreach($list as $i => $v)
        {
        	$category_id = $v['category_id'];
        	if (!isset($category_result[$category_id])) $category_result[$category_id] = $category_list[$category_id];
            if($v['alone_update'] == 1) $list[$i]['alone_update'] = '独立更新';
            else $list[$i]['alone_update'] = '不独立更新';
            if($v['only_auth'] == 1) $list[$i]['only_auth'] = '不显示未授权软件';
            else $list[$i]['only_auth'] = '显示未授权软件';

            $list[$i]['soft_update'] = ($v['soft_update'] == 1) ? '更新' : '不更新';
            $list[$i]['category_name'] = $category_result[$category_id]['name'];
            switch ($list[$i]['activation_type']){
                case 5:  $list[$i]['activation_type_name'] = '普通非山寨';break;
                case 6:  $list[$i]['activation_type_name'] = '严格非山寨';break;
                case 9:  $list[$i]['activation_type_name'] = '普通山寨';break;
                case 10:  $list[$i]['activation_type_name'] = '严格山寨';break;
            }
            $category_result[$category_id]['result'][] = $list[$i];
        }
        //$this->writelog('查看了渠道列表');
        $this->assign ( "list", $list );
        $this->assign ( "category_result", $category_result );
		$util = D('Sj.Util');
		$this->assign('product_list',$util->getProducts($platform));
		
		
          

		$this->display('channels');
	}
	
	function category_channels(){
        define("GO_HIDE_SKIN", 4);
        $Form = M("channel");
        import("@.ORG.Page");
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);

		//分组
		$team_va = $channel_category->getteam();
		$this->assign('team_value', $team_va);

		//部门
		$depart_va = $channel_category->getdepartment();
		$this->assign('depart_value', $depart_va);

		//负责人
		$fuze_va = $channel_category->getfuzeren();
		$this->assign('fuze_value', $fuze_va);
		
	    //合作分类
        if (!isset($_REQUEST['co_group'])) {
            $hezuo_va = -1;
        } else {
		    $hezuo_va=$_REQUEST['co_group'];
		}
        $this->assign('hezuo_value',$hezuo_va);
		
		//客户名称
		$clientname_va = $_REQUEST['clientname'];
		$this->assign('clientname_value', $clientname_va);

		//渠道名称
		$chname_va = $_REQUEST['chname'];
		$this->assign('chname_value', $chname_va);

		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		$zh_map = array(
			'source_type' => $source_type,
		   	'source_value' => $_SESSION['admin']['admin_id'],
		 	'target_type' => $target_type
		);
		$zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql_where=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			//$target_type= CHANNEL_SHOW_CONTROL;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql_where = " cid in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}')  and  ";
		}

		$sql_where .= "status=1";
        $chname = '';
  
		if(isset($_REQUEST['cid'])&&$_REQUEST['cid']){
			$cid = $_REQUEST['cid'];
			if(is_array($cid)){
				$cid_array=$cid;
				foreach($cid_array as $k){
					if($k!=0){
						$new_cid_array[]=$k;
					}
				} 
				$cid=implode(",",$new_cid_array);
			}else{
				$cid_array=explode(",",$cid);
			}
			$sql_where.=" and cid in (".$cid.")";

        }
	
        $category_id = '';
		if(isset($_REQUEST['category_id'])&& $_REQUEST['category_id']!=''){
        	$category_id = $_REQUEST['category_id'];
			$sql_where .= " and category_id = '{$category_id}'";
        	$this->assign('category_id', $category_id);
        }
		

		$department = '';
		if(isset($_REQUEST['department'])&& $_REQUEST['department']!=''){
        	$department = $_REQUEST['department'];
        	if($department=='---')
        	{
        		$sql_where .= " and department = ''";
        	}else
        	{
        		$sql_where .= " and department = '{$department}'";
        	}
			
        	$this->assign('department', $department);
        }

        $team = '';
		if(isset($_REQUEST['team'])&& $_REQUEST['team']!=''){
        	$team = $_REQUEST['team'];
        	if($team=='---')
        	{
        		$sql_where .= " and team = ''";
        	}else
        	{
        		$sql_where .= " and team = '{$team}'";
        	}
			
        	$this->assign('team', $team);
        }

        $fuzeren = '';
		if(isset($_REQUEST['fuzeren'])&& $_REQUEST['fuzeren']!=''){
        	$fuzeren = $_REQUEST['fuzeren'];
        	if($fuzeren=='---'){
        		$sql_where .= " and fuzeren = ''";
        	}else
        	{
        		$sql_where .= " and fuzeren = '{$fuzeren}'";
        	}
			
        	$this->assign('fuzeren', $fuzeren);
        }
		/*合作分类*/
		$co_group = '';
		if(isset($_REQUEST['co_group']) && $_REQUEST['co_group']!= -1 && $_REQUEST['co_group']!= ''){
        	$co_group = $_REQUEST['co_group'];
        	if($co_group=='0'){
        		$sql_where .= " and co_group = ''";
        	}else
        	{
        		$sql_where .= " and co_group = '{$co_group}'";
        	}
			
        	$this->assign('co_group', $co_group);
        }

        $chname ='';
        if(isset($_REQUEST['chname'])&& $_REQUEST['chname']!=''){
        	$chname = $_REQUEST['chname'];
			$sql_where .= " and chname like '%{$chname}%'";
        	$this->assign('chname', $chname);
        }

        $clientname ='';
        if(isset($_REQUEST['clientname'])&& $_REQUEST['clientname']!=''){
        	$clientname = $_REQUEST['clientname'];
			$sql_where .= " and clientname like '%{$clientname}%'";
        	$this->assign('clientname', $clientname);
        }
  		
		$count = $Form -> where($sql_where) ->count();
		
		$param = http_build_query($_GET);
		//print_r($param);
		$param="cid=".$cid."&category_id=".$category_id."&".$param;
		$Page = new Page($count, 10, $param);
		//$Page->callback = 'return get_params();';
		
		
        $list=$Form->where($sql_where)->limit($Page->firstRow . ',' . $Page->listRows)->order('team asc,fuzeren asc,chname asc')->select();
       

        $channel = M('channel_category');
        foreach ($list as $key => $val){
        	$name = $channel->where("category_id = '${val['category_id']}'")->field('name')->select();
        	$list[$key]['name'] = $name[0][name];
        	
        }
		$Page->parameter.="&team=".urlencode($_REQUEST['team'])."&department=".urlencode($_REQUEST['department'])."&fuzeren=".urlencode($_REQUEST['fuzeren'])."&chname=".urlencode($_REQUEST['chname'])."&clientname=".urlencode($_REQUEST['clientname']);



		$zh_cids=array();
		foreach($cid_array as $k=>$val){
			if($val!=0){
				$zh_cids[$k]['cid']=$val;
				$c_where['status']=1;
				$c_where['cid']=$val;
				$zh_chname=$Form->where($c_where)->getfield("chname");
				$zh_cids[$k]['chname']=$zh_chname;
			}
			
		}
        //$list = $Form->query($sql);
        if($chl) $this -> assign('chl', $chl);
        if($chname) $this -> assign('chname', $chname);
		$this->assign("cid_array",$zh_cids);
		$this->assign("zh_cid_str",$cid);
        $Page->setConfig('header','篇记录');
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','末页');
		$show = $Page->show();
        $this->assign("page", $show);
        //$this->writelog('查看了渠道列表');
        $pan_id =$_GET['category_id'];
        $this->assign('panid',$pan_id);
        $this->assign("list", $list);
        //渠道名百度效果
        if($this->isAjax())
        {
            $keyword = escape_string($_GET['query']);
			
		   	$chname =$Form->query("select chname from sj_channel where chname like '%$keyword%'");
		 
             $data = array(
                    'query' => $keyword,
                    'suggestions' => array(),
             );
                    foreach($chname as $v) {
                            $data['suggestions'][] = $v['chname'];
                    }
              
                    exit(json_encode($data));
             }

        if($_POST){
           $this->redirect('/Channels/category_channels',array('team'=>$_REQUEST['team'],'department'=>$_REQUEST['department'],'fuzeren'=>$_REQUEST['fuzeren'],'category_id'=>$_REQUEST['category_id']));
        }/**/		
		$this->assign("co_group_arr", C('co_group'));
        $this->display('category_channels');
	}
    

	//渠道批量转入——显示
	function channel_to_show()
	{
		$this->display();
	}


	//渠道转移(单个转移)
	function channel_jump()
	{
		$Form = M("channel");
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);
		//分组
		$team_va = $channel_category->getteam();
		$this->assign('team_value', $team_va);
		//部门
		$depart_va = $channel_category->getdepartment();
		$this->assign('depart_value', $depart_va);

		//负责人
		$fuze_va = $channel_category->getfuzeren();
		$this->assign('fuze_value', $fuze_va);
		
		//合作分类
		$hezuo_va=$_REQUEST['co_group'];
		$this->assign('hezuo_value',$hezuo_va);
		
		//客户名称
		$clientname_va = $_POST['clientname'];
		$this->assign('clientname_value', $clientname_va);

		//渠道名称
		$chname_va = $_POST['chname'];
		$this->assign('chname_value', $chname_va);
		$cid = $_REQUEST['cid'];
		$chname = $_REQUEST['chname'];
		//$chname = iconv("gb2312","utf-8",$_GET['chname']);
		//echo $chname;
		//die;
		$category_id = $_REQUEST['category_id'];
		$department = $_REQUEST['department'];
		$team = $_REQUEST['team'];
		$fuzeren =$_REQUEST['fuzeren'];
		$this->assign('chname_name', $chname);
		$this->assign('category_id', $category_id);
		$this->assign('department_name', $department);
		$this->assign('team_name', $team);
		$this->assign('fuzeren_name', $fuzeren);
		$this->assign('cid', $cid);
		$this->display();
	}

	//单个渠道转移
	function channel_to_one()
	{
		$User = M("channel");
		$cid = $_POST['cid'];

		if($_POST['category_id']!='')
		{
			$data['category_id'] = $_POST['category_id'];
			$category_id = $_POST['category_id'];
		}else
		{
			$data['category_id'] = $_POST['ca_id'];
			$category_id = $_POST['category_id'];
		}
		
		if( $_POST['department']!='')
		{
			$data['department'] = $_POST['department'];	
		}else
		{
			$data['department'] = $_POST['de_name'];
		}
		if($_POST['team']!='')
		{
			$data['team'] = $_POST['team'];
		}else
		{
			$data['team'] = $_POST['te_name'];
		}
		if($_POST['fuzeren']!='')
		{
			$data['fuzeren'] = $_POST['fuzeren'];
		}else{
			$data['fuzeren'] = $_POST['fu_name'];
		}
		if($_POST['co_group']!='')
		{
			$data['co_group'] = $_POST['co_group'];
		}
		$logg= $this->logcheck(array('cid'=>$cid),'sj_channel',$data,$User);
		$update = $User->where("cid=$cid")->save($data);
		//$this->writelog("将cid为'".$cid."'的渠道转移到负责人为'".$data['fuzeren']."'，分组为'".$data['team']."'，部门为'".$data['department']."', 渠道分组为'".$data['category_id']."'");
		$this->writelog("渠道管理_渠道列表_转移cid为$cid".$logg,'sj_channel',$cid,__ACTION__ ,"","edit");
		$filterMod = M('admin_filter');
		$where = array(
			'source_type' => USER_FILTER_TYPE,
			'target_type' => CHANNEL_SHOW_CONTROL_BY_CATEGORY,
			'target_value' => $category_id,
			'filter_type' => 2
		);
		$filters = $filterMod->where($where)->field('source_value')->select();
		$uid = array();
		while (list($key, $val) = each($filters)){
			array_push($uid, $val['source_value']);
		}
		$uid = array_unique($uid);
		$maps = array(
			'source_type' => USER_FILTER_TYPE,
			'filter_type' => 2,
			'addtime' => time(),		
			'target_type'=>CHANNEL_FILTER_TYPE,
		);
		foreach ($uid as $v){
			$maps['source_value'] = $v;
			if ($filterMod->add($maps)){
				$this->writelog("把{$_POST['cid']}的渠道同时更新到sj_admin_filter表拥有此分表的用户{$v}",'sj_admin_filter',$v,__ACTION__ ,"","add");
			}
		}

		if(!empty($update)||$update==0)
		{
			$this->success('转移成功');
		}
	}


	//批量操作
	function channels_to_category()
	{
		
		//if (!empty($_POST['category_id']) && !empty($_POST['sid'])) 
		if ((strlen($_POST['category_id'])>0) && !empty($_POST['sid'])){
			$category_id = $_POST['category_id'];
			$cid = explode(',', $_POST['sid']);
		
			$in = array();
			foreach($cid as $v){
				if (!empty($v)) $in[] = $v;
			}
			
			if (!empty($in)) {
				$map = array(
					'cid' => array('in', $in)
				);
				$model = M('channel');
				if($category_id==zz)
				{
					$data1 = array('fuzeren'=> $_POST['fuzeren'],'team'=>$_POST['team'],'department'=>$_POST['department']);
					$log = $this->logcheck(array('cid'=>$_POST['sid']),'sj_channel',$data1,$model);
					$affect = $model -> where($map) -> save($data1);
					$this->writelog("渠道管理_渠道列表_转移cid为'".$_POST['sid']."'".$log,'sj_channel',$_POST['sid'],__ACTION__ ,"","edit");
					//$this->writelog("将cid为'".$_POST['sid']."'的渠道转移到负责人为'".$_POST['fuzeren']."'，分组为'".$_POST['team']."'，部门为'".$_POST['department']."'");

				}
				else if($category_id==tt)
				{
				 $data3=array('co_group'=>$_POST['co_group']);
				 $log3 = $this->logcheck(array('cid'=>$_POST['sid']),'sj_channel',$data3,$model);
				 $affect = $model -> where($map) -> save($data3);
				 $this->writelog("渠道管理_渠道列表_转移cid为'".$_POST['sid']."'".$log3,'sj_channel',$_POST['sid'],__ACTION__ ,"","edit");
				}
				else if($category_id!=zz && $category_id!=tt)
				{
					$data2 =array('category_id'=>$category_id);
					$log2= $this->logcheck(array('cid'=>$_POST['sid']),'sj_channel',$data2,$model);
					$affect = $model -> where($map) -> save($data2);
					//$this->writelog("将cid为'".$_POST['sid']."'的渠道转移到渠道分类为'".$_POST['category_id']."'");
					$this->writelog("渠道管理_渠道列表_转移cid为'".$_POST['sid']."'".$log2,'sj_channel',$_POST['sid'],__ACTION__ ,"","edit");
					if($affect > 0){
					$string = implode(',', $_POST['sid']);
					//$this->writelog("将cid为{$string}的渠道加入到id为{$category_id}的分类中");

					$filterMod = M('admin_filter');
					$where = array(
						'source_type' => USER_FILTER_TYPE,
						'target_type' => CHANNEL_SHOW_CONTROL_BY_CATEGORY,
						'target_value' => $category_id,
						'filter_type' => 2
					);
					$filters = $filterMod->where($where)->field('source_value')->select();
					$uid = array();
					while (list($key, $val) = each($filters)){
						array_push($uid, $val['source_value']);
					}
					$uid = array_unique($uid);
					$maps = array(
						'source_type' => USER_FILTER_TYPE,
						'filter_type' => 2,
						'addtime' => time(),		
						'target_type'=>CHANNEL_FILTER_TYPE,
					);
/*					$t = array(
						'source_type' => USER_FILTER_TYPE,
						'target_type' => CHANNEL_FILTER_TYPE,
						'target_value'=>$category_id
					);
					$filterMod->where($t)->delete();*/					
					foreach ($uid as $v){
						$maps['source_value'] = $v;
						foreach ($in as $val){
							$maps['target_value']=$val;
							if ($filterMod->add($maps)){
								$this->writelog("把{$val}的渠道同时添加到sj_admin_filter表拥有此分表的用户",'sj_admin_filter',"target_value:{$val}",__ACTION__ ,"","add");
								}
							}
						}					
					}
				}			
			}
		}
		$this->assign('jumpUrl','/index.php/Sj/Channels/category_channels/category_id/'.$category_id);
		//$this->success('数据更新成功');
		echo 1;
	}
	//百度效果渠道名称
	function channel_name_sel()
	{
			 //百度下拉效果
               $optionmodel = M("channel");
                if($this->isAjax())
                {
                    $keyword = escape_string($_GET['query']);
					
		   			$chname =$optionmodel->query("select chname from sj_channel where chname like '%$keyword%'");
		   			//file_put_contents('11.txt', $optionmodel->getlastsql());
                    $data = array(
                            'query' => $keyword,
                            'suggestions' => array(),
                    );
                    foreach($chname as $v) {
                            $data['suggestions'][] = $v['chname'];
                    }
                    //file_put_contents('data.txt',serialize($data));
                    exit(json_encode($data));
                }

	}
    //渠道管理_渠道编辑_显示
	function channels_edit(){
		if(!empty($_GET['cid'])) {
			$channel_category = D('Sj.ChannelCategory');
			$category_list = $channel_category->getCategory();
			$this->assign('category_list', $category_list);
			$Form = M("channel");
	        $source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$map = array(
				'source_type' => USER_FILTER_TYPE,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => CHANNEL_FILTER_TYPE,
				'target_value' => $_GET['cid'],

			);
			$source_type = USER_FILTER_TYPE;
			$target_type= CHANNEL_SHOW_CONTROL;
			//$target_type = CHANNEL_FILTER_TYPE;
			$zh_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
			if(!$zh_res){
				if (!$Form->table('sj_admin_filter')->where($map)->select()){
				$this->error('禁止查看/编辑该渠道');
				}
			}
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_SHOW_TYPE;
			$map = array(
	        	'source_type' => $source_type,
	        	'source_value' => $_SESSION['admin']['admin_id'],
	        	'target_type' => $target_type
			);
			$res = $Form->table('sj_admin_filter')->where($map)->find();
			$show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
			$this->assign( "show_chl", $show_chl );

			$map = array(
				'cid' => $_GET['cid']
			);
			$vo = $Form->where($map)->select();
			$Form = M("pu_operating");
			$l1=$Form->query("SELECT p.oid, p.mname FROM sj_channel AS s JOIN pu_operating AS p ON s.oid = p.oid WHERE s.cid ='".$_GET['cid']."'");
			$this->assign('l1',$l1[0]);
			$list1=$Form->query("select oid,mname from pu_operating");
			//$Form = M("pu_manufacturer ");
			$l2=$Form->query("SELECT p.mid, p.mname FROM sj_channel AS s JOIN pu_manufacturer AS p ON s.mid = p.mid WHERE s.cid ='".$_GET['cid']."'");
			$this->assign('l2',$l2);
			//$list2=$Form->query("select mid,mname from pu_manufacturer ");
			//$Form = M("pu_device  ");
			$l3=$Form->query("SELECT p.did, p.dname FROM sj_channel AS s JOIN pu_device AS p ON s.did = p.did WHERE s.cid ='".$_GET['cid']."'");
			$this->assign('l3',$l3);
	        //$list3=$Form->query("select did,dname from pu_device  where status=1 order by did ");
			$Form = M("channel");
			$this->assign('list1',$list1);
			//$this->assign('list2',$list2);
			//$this->assign('list3',$list3);
			if($vo) {
				$util = D('Sj.Util');
				$this->assign('product_list',$util->getProducts($vo[0]['platform']));
				$this->assign('vo',$vo[0]);
				$this->display('channels_edit');
			}else{
				exit('编辑项不存在！');
			}
		}else{
			exit('编辑项不存在！');
		}
	}
    //渠道管理_渠道编辑_提交
	function channelsupdate(){
			$Form = M("channel");
			$_POST['last_refresh']=time();
            $checkpassword=$_POST['checkpassword'];
            if(empty($checkpassword) || strlen($checkpassword)<5) {
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
                $this->error('数据插入失败,查看密码不得为空且不能小于6位存在！');
            }   
            $chl = $_POST['chl'];
            $name=$_POST['checkname'];
            $chname=$_POST['chname'];
			$note = $_POST['note'];
			$co_group=$_POST['co_group'];
			//$is_filte = $_POST['is_filte'];
			//echo $is_filte;exit;
            $map='';
            $map['cid']=array('neq'=>$_POST['cid']);
            $arr = $Form->field("chname,checkname,chl")->where($map)->select();
            $newarr = array();
            $newname = array();
            $newchname=array();
            foreach($arr as $v){
                $newarr[] = $v['chl'];
                $newname[] = $v['checkname'];
                $newchname[]=$v['chname'];
            }

            if(in_array($chl,$newarr)){
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
                $this->error('数据插入失败,渠道号存在！');
            }
            if(in_array($name,$newname)){
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
                $this->error('数据插入失败,查看用户名存在！');
            }
            if(in_array($chname,$newchname)){
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
                $this->error('数据插入失败,渠道名存在！');
            }
			$where =	array('cid' =>	$_POST['cid']);
			$data =	array(); 
			isset($_POST['checkname']) ? $data['checkname'] = 	$_POST['checkname'] : '';
			isset($_POST['chname']) ? $data['chname'] =	$_POST['chname'] : '';
			isset($_POST['checkpassword']) ? $data['checkpassword']	=	$_POST['checkpassword'] : '';
			isset($_POST['alone_update']) ? $data['alone_update']	=	$_POST['alone_update'] : '';
			isset($_POST['only_auth']) ? $data['only_auth']	=	$_POST['only_auth'] : '';
			//isset($_POST['soft_auth']) ? $data['soft_auth']	=	$_POST['soft_auth'] : '';
			isset($_POST['soft_update']) ? $data['soft_update']	=	$_POST['soft_update'] : '';
			isset($_POST['oid']) ? $data['oid']	= 	$_POST['oid'] : '';
			isset($_POST['note']) ? $data['note']	=	$_POST['note'] : '';
			$data['last_refresh']	=	time();
			isset($_POST['is_filter']) ? $data['is_filter']	=	$_POST['is_filter']  : '';
			isset($_POST['purpose']) ? $data['purpose'] =	$_POST['purpose']  : '';
			isset($_POST['activation_type']) ? $data['activation_type'] = $_POST['activation_type'] : '';
			isset($_POST['category_id']) ? $data['category_id']	=	$_POST['category_id'] : '';
			isset($_POST['channel_ad']) ? $data['channel_ad']	=	$_POST['channel_ad'] : '';
			isset($_POST['switch']) ? $data['switch'] = $_POST['switch'] : '';
			isset($_POST['platform']) ? $data['platform'] = $_POST['platform'] : '';
			isset($_POST['co_group']) ? $data['co_group'] = $_POST['co_group'] : '';
			isset($_POST['inputtext']) ? $data['inputtext'] = $_POST['inputtext'] : '';
			isset($_POST['dnakey']) ? $data['dnakey'] = $_POST['dnakey'] : '';
            $log = $this->logcheck(array('cid'=>$_POST['cid']),'sj_channel',$data,$Form);
			$last_list = $Form->field("alone_update")->where($where)->find();
			$list=$Form -> where($where) -> save($data);
			//渠道版本同步通知
			if($_POST['alone_update'] != $last_list['alone_update']){
				$ucenter = C('ucenter');
				$data_array = array(
					'data'=>array(
						'serviceId' =>$ucenter['client_serviceId'],
						'syncDataType' => 3,
						'standAlone' => $_POST['alone_update'],
						'pk' => $_POST['cid'],
					),
				);	
				request_task(C('ucenter_api'),$data_array);	
			}
			if(($_POST['activation_type'] & 8) != 8){
				if($_POST['purpose'] == 2 || $_POST['purpose'] == 3 || $_POST['purpose'] == 4){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
                $this->error('数据插入失败,出现异常');
				}
			}
			
			if ($_POST['org_category_id'] != $_POST['category_id']){
				$filterMod = M('admin_filter');
				/*$t = array(
					'source_type' => USER_FILTER_TYPE,
					'target_type' => CHANNEL_FILTER_TYPE,
					'target_value'=>$_POST['cid']
				);
				$filterMod->where($t)->delete();*/
	
				$wheres = array(
					'source_type' => USER_FILTER_TYPE,
					'target_type' => CHANNEL_SHOW_CONTROL_BY_CATEGORY,
					'target_value' => $_POST['category_id'],
					'filter_type' => 2
				);
				$filters = $filterMod->where($wheres)->field('source_value')->select();
				$uid = array();
				while (list($key, $val) = each($filters)){
					array_push($uid, $val['source_value']);
				}
				$uid = array_unique($uid);
				$maps = array(
					'source_type' => USER_FILTER_TYPE,
					'filter_type' => 2,
					'addtime' => time(),		
					'target_type'=>CHANNEL_FILTER_TYPE,
					'target_value'=>$_POST['cid']
				);
				foreach ($uid as $v){
					$maps['source_value'] = $v;
					$r = $filterMod->add($maps);
					if ($r){
						$this->writelog("把{$_POST['cid']}的渠道同时更新到sj_admin_filter表拥有此分表的用户{$v}",'sj_admin_filter',"source_value:{$v}",__ACTION__ ,"","add");
					}
				}				
			}
			
			if($list!==false){
				$map_go['channel_name']=$_POST['chname'];
				$map_go['ch_name']=$_POST['checkname'];
				$map_go['ch_pwd']=$_POST['checkpassword'];
				$map_go['chl']=$_POST['chl'];
				$map_go['alone_update']=$_POST['alone_update'];
				$map_go['oid']= $_POST['oid'];
				$map_go['note']=$_POST['note'];
				$map_go['only_auth']=$_POST['only_auth'];
				$map_go['soft_update']=$_POST['soft_update'];
				$map_go['update_tm']=time();
				$map_go['is_filter']=$_POST['is_filter'];
				$map_go['category_id']=$_POST['category_id'];	
				$map_go['activation_type']=$_POST['activation_type'];
				$map_go['purpose']=$_POST['purpose'];
				$map_go['channel_ad']=$_POST['channel_ad'];
				$map_go['switch']=$_POST['switch'];
				$map_go['platform']=$_POST['platform'];
				$map_go['co_group']=$_POST['co_group'];
				$map_go['inputtext']=$_POST['inputtext'];
				$map_go['dnakey']=$_POST['dnakey'] ? $_POST['dnakey'] : '';
				$str_md5="abcdefgh";
				$md5_key=md5($str_md5);
				$map_go['md5_key']=$md5_key;
				$map_go['request']="PACKAGE_EDIT";
				$map_go['ip']=$_SERVER["REMOTE_ADDR"];
				$map_go['id']=$_POST['cid'];
				$channel_url=C("channel_url");
				$edit_channel_url = $channel_url."/api.php";
				$edit_channel = $this->require_url($edit_channel_url,$map_go);
				if($edit_channel=="error"){
					$this -> error("对不起，mysql_connect failed\n");
					exit;
				}
				if($edit_channel==2){
					$this -> error("对不起，请按照正确的流程添加渠道\n");
					exit;
				}
			 	if(empty($edit_channel)){
					$this -> error("向打包后台编辑渠道失败");
				}
                $this->writelog('编辑ID为['.$_POST['cid'].']的渠道:'.$log,'sj_channel',$_POST['cid'],__ACTION__ ,"","edit");
			    $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
			    $this->success('数据更新成功');
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
				$this->error('数据更新失败');
			}
	}
    //渠道管理_渠道设置_显示
	function channels_setup(){		
		//error_reporting(E_ALL);
		//ini_set('display_errors', true);
		//ini_set("memory_limit", "512M");
		$Form = new Model();
		$list1=$Form->query("select oid,mname from pu_operating");
		//$list2=$Form->query("select mid,mname from pu_manufacturer where status=1 and mid<>0");
		//$list3=$Form->query("select did,dname from pu_device where `dname`='通用' and `status`=1");
		$this->assign('list1',$list1);
		
		//$this->assign('list2',$list2);
		//$this->assign('list3',$list3);
                //supwater 展示各种下拉

                //百度下拉效果
                $optionmodel = M("channel_option");
                if($this->isAjax())
                {
                    $keyword = $_GET['query'];
		    $clientname =$optionmodel->query("select distinct(name) from sj_channel_option where type=1 and name like '%$keyword%'");
                    $data = array(
                            'query' => $keyword,
                            'suggestions' => array(),
                    );
                    foreach($clientname as $v) {
                            $data['suggestions'][] = $v['name'];
                    }
                    exit(json_encode($data));
                }

                $tmp_array = array();
		$bumen=$optionmodel->query("select name,type from sj_channel_option");
                foreach($bumen as $v)
                {
                    $tmp_array[$v['type']][]=$v['name'];
                }

		$this->assign('bumen', array_unique($tmp_array[2]));
		$this->assign('team', array_unique($tmp_array[3]));
		$this->assign('fuzeren', array_unique($tmp_array[4]));

		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);
		$util = D('Sj.Util');
		$this->assign('product_list',$util->getProducts());
		$this->display('channels_setup');
	}
	
	function channels_batch()
	{
		if(isset($_FILES['csv'])){
			if (!empty($_FILES['csv']['tmp_name'])) {
				mkdir('/tmp/admin/', 0755, true);
				$file = '/tmp/admin/'. session_id(). '_'. $_FILES['csv']['name'];
				move_uploaded_file($_FILES['csv']['tmp_name'], $file);
				$_SESSION['tmp_batch_channel_file'] = $file;
				
				$fp = fopen($file, 'r');
				$csv = array();
				$check_chl = array();
				$check_chname = array();
				$enabled = true;
				$i = 0;
				$Form = M("channel"); // 实例化User对象
				$arr = $Form->field("cid,chname,checkname,chl")->select();
				
				$cid = array();
				$newarr = array();
				$newname = array();
				$newchname=array();
				foreach($arr as $v){
					$cid[] = $v['cid'];
					$newarr[] = $v['chl'];
					$newname[] = $v['checkname'];
					$newchname[]=$v['chname'];
				}
				while(!feof($fp)){
					list($chl, $chname) = fgetcsv($fp);
					if (empty($chl) || empty($chname)) continue;
					$encode = mb_detect_encoding($chname, array("ASCII","UTF-8","GB2312","GBK","BIG5"));
					if($encode != "UTF-8"){
						$chname = iconv($encode,"UTF-8",$chname);
					}
					$tmp = array(
						'chl' => $chl,
						'checkname' => $chname,
						'chname' => $chname,
						'status' => 0
					);
					
					if (isset($check_chl[$chl])) {
						$tmp['status'] = $tmp['status'] | 1;
						$csv[$check_chl[$chl]]['status'] = $csv[$check_chl[$chl]]['status'] | 1;
						$enabled = false;
					}
					if (isset($check_chname[$chname])) {
						$tmp['status'] = $tmp['status'] | 2;
						$csv[$check_chname[$chname]]['status'] = $csv[$check_chname[$chname]]['status'] | 2;
						$enabled = false;
					}

					if(in_array($chl,$newarr)){
						$tmp['status'] = $tmp['status'] | 4;
						$enabled = false;
					}
					if(in_array($chname,$newname)){
						$tmp['status'] = $tmp['status'] | 8;
						$enabled = false;
					}
					if(in_array($chname,$newchname)){
						$tmp['status'] = $tmp['status'] | 16;
						$enabled = false;
					}
					
					$check_chl[$chl] = $i;
					$check_chname[$chname] = $i;

					$csv[$i] = $tmp;
					$i++;
				}
				fclose($fp);
				//--------2013-4-7 庄超滨 渠道设置---------
				$list1=$Form->query("select oid,mname from pu_operating");
				$this->assign('list1',$list1);
				$channel_category = D('Sj.ChannelCategory');
				$category_list = $channel_category->getCategory();
				$this->assign('category_list', $category_list);
				$util = D('Sj.Util');
				$this->assign('product_list',$util->getProducts());
				//-------------------
				$this->assign('csv', $csv);
				$this->assign('enabled', $enabled);
				$this->display('channels_batch_check');
			} else {
				$this->error('请上传文件');
			}
		} elseif(isset($_POST['submit_to_db'])) {
			$model = M("channel");
			$file = $_SESSION['tmp_batch_channel_file'];
			if (!is_file($file)) {
				$this->error('请上传文件', '/index.php/'.GROUP_NAME.'/Channels/channels_batch');
			}
			$fp = fopen($file, 'r');
			//-----------2013-4-7-庄超滨-------添加渠道设置—提交----------------
			$oid = $_POST['oid'];//渠道运营商
			$alone_update =$_POST['alone_update'];//是否独立更新
			$only_auth = $_POST['only_auth'];//软件显示选项
			$soft_update = $_POST['soft_update'];//软件更新选项
			$is_filter = $_POST['is_filter'];//软件搜索时是否过滤
			$category_id = $_POST['category_id'];//渠道类型选择
			$activation_type = $_POST['activation_type'];//渠道用途选择
			$purpose = $_POST['purpose'];//渠道device_id选择
			$channel_ad = $_POST['channel_ad'];//轮播图接口(GET_RECOMMEND_NEW)轮播图显示方式
			$platform = $_POST['platform'];//平台类型
			$switch = $_POST['switch'];//渠道推送开关
			$note = $_POST['note'];//备注
			$inputtext = $_POST['inputtext'];//备注
			//var_dump($oid,$alone_update,$only_auth,$soft_update,$is_filter,$category_id);exit;
			$csv = array();
			$check_chl = array();
			$check_chname = array();
//			$sql = 'insert into sj_channel (`chl`,`chl_cid`,`checkname`,`chname`,`checkpassword`,`oid`,`mid`,`did`,`alone_update`,`only_auth`,`soft_update`,`is_filter`,`category_id`,`activation_type`,`purpose`,`channel_ad`,`platform`,`switch`,`note`,`submit_tm`,`last_refresh`) values ';
			$affectid = array();
			while(!feof($fp)){
				list($chl, $chname) = fgetcsv($fp);
				if (empty($chl) || empty($chname)) continue;
				$encode = mb_detect_encoding($chname, array("ASCII","UTF-8","GB2312","GBK","BIG5"));
				if($encode != "UTF-8"){
					$chname = iconv($encode,"UTF-8",$chname);
				}
				$tmp = array(
					'chl' => $chl,
					'chl_cid'=>substr($chl,0,8),
					'checkname' => $chname,
					'chname' => $chname,
					'checkpassword' => substr($chl, rand(0,20), 6),
					'oid' => $oid,
					'mid' => 1,
					'did' => 1,
					'alone_update' => $alone_update,
					'only_auth' => $only_auth,
					'soft_update' => $soft_update,
					'is_filter'=>$is_filter,
					'category_id'=>$category_id,
					'activation_type'=>$activation_type,
					'purpose'=>$purpose,
					'channel_ad'=>$channel_ad,
					'platform'=>$platform,
					'switch'=>$switch,
					'note'=>$note,					
					'inputtext'=>$inputtext,					
					'submit_tm' => time(),
					'last_refresh' => time(),
				);
//				$sql .= "('{$tmp['chl']}','{$tmp['chl_cid']}','{$tmp['checkname']}','{$tmp['chname']}','{$tmp['checkpassword']}','{$tmp['oid']}','{$tmp['mid']}','{$tmp['did']}','{$tmp['alone_update']}','{$tmp['only_auth']}','{$tmp['soft_update']}','{$tmp['is_filter']}','{$tmp['category_id']}','{$tmp['activation_type']}','{$tmp['purpose']}','{$tmp['channel_ad']}','{$tmp['platform']}','{$tmp['switch']}','{$tmp['note']}','{$tmp['submit_tm']}','{$tmp['last_refresh']}'),";
				if (isset($check_chl[$chl])) {
					$this->error("渠道号{$chl} 存在重复");
				}
				if (isset($check_chname[$chname])) {
					$this->error("渠道名{$chl} 存在重复");
				}					
				$check_chl[$chl] = $i;
				$check_chname[$chname] = $i;
				$i++;
				$csv[$i] = $tmp;
				
				$affect = $model->add($tmp);
				//渠道版本同步通知
				$ucenter = C('ucenter');
				$data_array = array(
					'data'=>array(
						'serviceId' =>$ucenter['client_serviceId'],
						'syncDataType' => 1,
						'standAlone' => $_POST['alone_update'],
						'pk' => $affect,
					),
				);	
				request_task(C('ucenter_api'),$data_array);					
				array_push($affectid, $affect);
			}
//			$sql = preg_replace('/,$/', ';', $sql);
//			$affect = $model->query( $sql);

			/** haoxian Start */
			$filterMod = M('admin_filter');
			$wheres = array(
				'source_type' => USER_FILTER_TYPE,
				'target_type' => CHANNEL_SHOW_CONTROL_BY_CATEGORY,
				'target_value' => $category_id,
				'filter_type' => 2
			);
			$filters = $filterMod->where($wheres)->field('source_value')->select();
			$uid = array();
			while (list($key, $val) = each($filters)){
				array_push($uid, $val['source_value']);
			}
			$uid = array_unique($uid);
			$maps = array(
				'source_type' => USER_FILTER_TYPE,
				'filter_type' => 2,
				'addtime' => time(),		
				'target_type'=>CHANNEL_FILTER_TYPE,
			);
			foreach ($uid as $val){
				$maps['source_value'] = $val;
				foreach ($affectid as $row){
					$maps['target_value'] = $row;
					if ($filterMod->add($maps)){
						$this->writelog("把{$_POST['cid']}的渠道同时更新到sj_admin_filter表拥有此分表的用户{$val}",'sj_admin_filter',"source_value:{$val}",__ACTION__ ,"","add");
					}
				}
			}	
			/** haoxian End */		
			
			foreach ($csv as $k =>$v){
				$cid = $model->where(array('chl'=>$v['chl']))->select();
				$date['chl_cid'] = substr($v['chl'],0,8).$cid[0]['cid'];
				$affects = $model ->where(array('chl'=>$v['chl'])) -> save($date);
				$this->writelog("id为{$cid[0]['cid']}的成功添加了渠道记录",'sj_channel',$cid[0]['cid'],__ACTION__ ,"","edit");
			}
			/* $pack_tmp = array(
				'chl' => $chl,
				'ch_name' => $chname,
				'channel_name' => $chname,
				'ch_pwd' => substr($chl, rand(0,20), 6),
				'oid' => 1,
				'alone_update' => 0,
				'only_auth' => 0,
				'soft_update' => 1,
				'create_tm' => time(),
				'update_tm' => time(),
			);
			$pack_db = D('Sj.Package');
			$pack_sql .= "('{$pack_tmp['chl']}','{$pack_tmp['ch_name']}','{$pack_tmp['channel_name']}','{$pack_tmp['ch_pwd']}','{$pack_tmp['alone_update']}','{$pack_tmp['only_auth']}','{$pack_tmp['soft_update']}','{$pack_tmp['create_tm']}','{$pack_tmp['update_tm']}'),";
			if($affect){
				$pack_result = $pack_db -> query($pack_sql);
				if(!$pack_result){
					$this -> error("渠道批量添加失败");
				}
			}else{
				$this -> error("渠道批量添加失败");
			} */
			if($affects){
				$this->writelog('成功批量添加了'. count($csv). '条渠道记录');
				$this->assign('jumpUrl','/index.php/Sj/Channels/channels');
				$this->success('渠道批量添加成功');
			}else{
				$this -> error("渠道批量添加失败");
			}
			fclose($fp);
		} else {
			$this->display('channels_batch');
		}
		
	}
	
    //渠道管理_渠道设置_提交
        function channels_setupadd()
        {

		$chl = $_POST['chl'];
        $name=$_POST['checkname'];
        $chname=$_POST['chname'];
        $map['chl']=$_POST['chl'];
		$chl6 =  substr($chl,0,8);//截取字符串8位
		//$map['chl_cid'] = $chl6.$cid;
        $map['checkname']=$_POST['checkname'];
        $map['chname']=$_POST['chname'];
        $map['checkpassword']=$_POST['checkpassword'];
        $map['alone_update']=$_POST['alone_update'];
        $map['only_auth']=$_POST['only_auth'];
        //$map['soft_auth']=$_POST['soft_auth'];
        $map['soft_update']=$_POST['soft_update'];
        $map['oid']= $_POST['oid'];
        $map['mid']=1;
        $map['did']=1;
        $map['note']=$_POST['note'];
		$map['submit_tm']=time();
		$map['last_refresh']=time();
		$map['is_filter']=$_POST['is_filter'];
		$map['category_id']=$_POST['category_id'];	
        $map['activation_type']=$_POST['activation_type'];
        $map['purpose']=$_POST['purpose'];
		$map['channel_ad']=$_POST['channel_ad'];
		$map['switch']=$_POST['switch'];
		$map['platform']=$_POST['platform'];
	    $map['co_group']=$_POST['co_group'];
	    $map['inputtext']=$_POST['inputtext'];
		//echo $map['is_filte'];exit;
        $checkpassword=$_POST['checkpassword'];
		if(empty($chl) || strlen($chl)<8 || strlen($chl)>64){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
			$this->error('渠道号不能为空,且不能小于8位存在！');
		}
		
        if(empty($checkpassword) || strlen($checkpassword)<5) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
			$this->error('数据插入失败,查看密码不得为空且不能小于6位存在！');
        }


		$_POST['submit_tm']=time();
		$_POST['last_refresh']=time();
		$Form = M("channel"); // 实例化User对象
		$arr = $Form->field("chname,checkname,chl")->select();

		
		$newarr = array();
		$newname = array();
        $newchname=array();
		foreach($arr as $v){
			$newarr[] = $v['chl'];
			$newname[] = $v['checkname'];
            $newchname[]=$v['chname'];
		}

		if(in_array($chl,$newarr)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
			$this->error('数据插入失败,渠道号存在！');
		}
		if(in_array($name,$newname)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
			$this->error('数据插入失败,查看用户名存在！');
		}
		if(in_array($chname,$newchname)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');
			$this->error('数据插入失败,渠道名存在！');
		}

            //-------客户名称等4个字段 操作开始 supwater
            $optionmodel = M("channel_option");
            $clientname = $_POST['clientname'];
            $department_type = $_POST['department_type'];

            if(strlen($clientname)>0)
            {
                $data['name'] = $clientname;
                $data['type']= 1;
                $optionmodel->add($data);
            }

            if($department_type=='1')//其他
            {
                $department=$_POST['input_department_other'];
                //新增该记录到optioin表
                $data['name'] = $department;
                $data['type']= 2;
                $optionmodel->add($data);
            }else if($department_type=='0'){
                $department='';
            }else{
                $department=$department_type;
            }

            $team_type = $_POST['team_type'];
            if($team_type=='1')//其他
            {
                $team = $_POST['input_team_other'];
                //新增该记录到optioin表
                $data['name'] = $team;
                $data['type']= 3;
                $optionmodel->add($data);
            }else if($team_type=='0'){
                $team='';
            }else{
                $team =$team_type;
            }

            $fuzeren_type = $_POST['fuzeren_type'];
            if($fuzeren_type=='1')//其他
            {
                $fuzeren =$_POST['input_fuzeren_other'];
                //新增该记录到optioin表
                $data['name'] = $fuzeren;
                $data['type']= 4;
                $optionmodel->add($data);
            }else if($fuzeren_type=='0'){
                $fuzeren='';
            }else{
                $fuzeren=$fuzeren_type;
            }
            $map['clientname']=$clientname;
            $map['department']=$department;
            $map['team']=$team;
            $map['fuzeren']=$fuzeren;
            $map['dnakey']=$_POST['dnakey'] ? $_POST['dnakey'] : '';
            //--------客户名称等4个字段操作结束


        $Form ->Create();
        $cid = $Form->add($map); // 写入用户数据到数据库
		$date['chl_cid'] = $chl6.$cid;//渠道号+连接cid[渠道id]
		$affect = $Form ->where("cid={$cid}") -> save($date);//更新字段数据
		
		if ($cid){
			//渠道版本同步通知
			$ucenter = C('ucenter');
			$data_array = array(
				'data'=>array(
					'serviceId' =>$ucenter['client_serviceId'],
					'syncDataType' => 1,
					'standAlone' => $_POST['alone_update'],
					'pk' => $cid,
				),
			);	
			request_task(C('ucenter_api'),$data_array);	
			$filterMod = M('admin_filter');
			$where = array(
				'source_type' => USER_FILTER_TYPE,
				'target_type' => CHANNEL_SHOW_CONTROL_BY_CATEGORY,
				'target_value' => $map['category_id'],
				'filter_type' => 2
			);
			$filters = $filterMod->where($where)->field('source_value')->select();
			$uid = array();
			while (list($key, $val) = each($filters)){
				array_push($uid, $val['source_value']);
			}
			$uid = array_unique($uid);
			$maps = array(
				'source_type' => USER_FILTER_TYPE,
				'filter_type' => 2,
				'addtime' => time(),		
				'target_type'=>CHANNEL_FILTER_TYPE,
				'target_value'=>$cid
			);
			foreach ($uid as $v){
				$maps['source_value'] = $v;
				if ($filterMod->add($maps)){
					$this->writelog("把{$cid}的渠道同时添加到sj_admin_filter表拥有此分表的用户{$v}",'sj_admin_filter',"source_value:{$v}",__ACTION__ ,"","add");
				}
			}
		}
		
		//$pack_db = D('Sj.Package');
		
		$map_go['channel_name']=$_POST['chname'];
        $map_go['ch_name']=$_POST['checkname'];
        $map_go['ch_pwd']=$_POST['checkpassword'];
        $map_go['chl']=$_POST['chl'];
		$map_go['alone_update']=$_POST['alone_update'];
		$map_go['chl_cid'] = $chl6.$cid;
		$map_go['oid']= $_POST['oid'];
        
		$map_go['note']=$_POST['note'];

		$map_go['only_auth']=$_POST['only_auth'];
		$map_go['soft_update']=$_POST['soft_update'];
		$map_go['create_tm']=time();
		$map_go['update_tm']=time();
		$map_go['status'] = 1;
		$map_go['is_filter']=$_POST['is_filter'];
		$map_go['category_id']=$_POST['category_id'];	
        $map_go['activation_type']=$_POST['activation_type'];
        $map_go['purpose']=$_POST['purpose'];
		$map_go['channel_ad']=$_POST['channel_ad'];
		$map_go['switch']=$_POST['switch'];
        $map_go['platform']=$_POST['platform'];
        $map_go['inputtext']=$_POST['inputtext'];
        $map_go['dnakey']=$_POST['dnakey'] ? $_POST['dnakey'] : '';
		$str_md5="abcdefgh";
		$md5_key=md5($str_md5);
		$map_go['md5_key']=$md5_key;
        $map_go['request']="PACKAGE_CHANNEL";
        $map_go['ip']=$_SERVER["REMOTE_ADDR"];
        $map_go['id']=$cid;
		$channel_url=C("channel_url");
        //$add_channel_url = $channel_url."/api.php";
        if(($_POST['activation_type'] & 8) != 8){
            if($_POST['purpose'] == 2 || $_POST['purpose'] == 3 || $_POST['purpose'] == 4){
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_setup');
                $this->error('数据插入失败,出现异常');
            }
        }
        /*
        $add_channel = $this->require_url($add_channel_url,$map_go);
        if($add_channel=="error"){
            $this -> error("对不起，mysql_connect failed\n");
            exit;
        }
        if($add_channel==2){
            $this -> error("对不起，请按照正确的流程添加渠道\n");
            exit;
        }
        if(empty($add_channel)){
            $this -> error("向打包后台添加渠道失败");
        }*/

		$this->writelog('添加渠道号为['.$chl.']的渠道','',$chl,__ACTION__ ,"","add");
        $this->success("数据保存成功！");
	}
    //渠道管理_渠道软件过滤列表页面
	function Channels_filtering(){
/* 		$Form = M("pu_operating");
		$list1=$Form->query("select oid,mname from pu_operating");
		$Form = M("pu_manufacturer");
		$list2=$Form->query("select mid,mname from pu_manufacturer where status=1 and mid<>0 order by mid");
		$Form = M("pu_device");
		$list3=$Form->query("select did,dname from pu_device  where status=1 and dname <> '' order by did ");
		$this->assign('list1',$list1);
		$this->assign('list2',$list2);
		$this->assign('list3',$list3); */
		$this->display('Channels_filtering');
	}
    //渠道管理_渠道软件过滤查找渠道
	function Channels_filter(){
		$Form = M();
		$list1=$Form->query("select oid,mname from pu_operating");
		$list2=$Form->query("select mid,mname from pu_manufacturer where status=1 and mid<>0 order by mid");
		$list3=$Form->query("select did,dname from pu_device  where status=1 and dname <>'' order by did ");
		$this->assign('list1',$list1);
		$this->assign('list2',$list2);
		$this->assign('list3',$list3);

		$Form = M ('channel');
		$list=$Form->where(array("oid" => $_POST['oid'],"mid" =>$_POST['mid'],"did" =>$_POST['did']))->select();
		$this->assign('list',$list[0]['chl']);
		$Form = M ('channel_adapter ');
		 $info = $Form->query("SELECT u.package, u.cid, u.status, s.softname FROM sj_channel_adapter AS u JOIN sj_soft AS s ON u.package = s.package WHERE u.cid ='".$list[0]['cid']."' AND u.status =1");
		$this->assign('info',$info);
		$this->display('Channels_filtering');
	}
    //渠道管理_渠道软件过滤查找渠道号
	function Channels_fi(){
		$Form = M("pu_operating");
		$list1=$Form->query("select oid,mname from pu_operating");
		$Form = M("pu_manufacturer ");
		$list2=$Form->query("select mid,mname from pu_manufacturer where status=1 and mid!=0 order by mid");
		$Form = M("pu_device");
		$list3=$Form->query("select did,dname from pu_device  where status=1 order by did ");
		$this->assign('list1',$list1);
		$this->assign('list2',$list2);
		$this->assign('list3',$list3);

		$Form = M ('channel');
		$list=$Form->where(array('chl' => $_POST['chl']))->getField('cid');
		$Form = M ('channel_adapter ');
		$arr = $Form->query("SELECT u.package, u.cid, u.status, s.softname FROM sj_channel_adapter AS u JOIN sj_soft AS s ON u.package = s.package WHERE u.cid ='".$list."' AND u.status =1 group by u.package");
		$this->assign('arr',$arr);
		$this->display('Channels_filtering');
	}
    //渠道管理_取消过滤
	function Channels_f(){
		$Form = D("channel_adapter");
        $list['package']=$_GET['package'];
        $list['cid']=$_GET['cid'];
        if(empty($list['cid']) ||empty($list['package'])) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/Channels_filtering/');
			$this->error('发生了一点点错误,请重试');
        }
		$map['last_refresh']=time();
		$map['status']=0;
		$type=$Form->where($list)->save($map);
		if($type!==false){
            $this->writelog('更新渠道ID为['.$list['cid'].']的渠道','sj_channel_adapter',$list['cid'],__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/Channels_filtering/');
			$this->success('数据更新成功');
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/Channels_filtering/');
			$this->error('数据更新失败');
		}
	}
    //渠道管理_添加过滤页面
	function channels_filteradd(){
		/* $Form = M("pu_operating");
		$list1=$Form->query("select oid,mname from pu_operating");
		$Form = M("pu_manufacturer ");
		$list2=$Form->query("select mid,mname from pu_manufacturer where status=1 and mid<>0 order by mid");
		$Form = M("pu_device");
		$list3=$Form->query("select did,dname from pu_device  where status=1 and dname <> '' order by did ");
		$this->assign('list1',$list1);
		$this->assign('list2',$list2);
		$this->assign('list3',$list3); */
		$this->display('channels_filteradd');
	}
    /*
    **   Edit By :Jinshan 2011/5/24
    **   判断正确包名
    */
    // 渠道管理_添加过滤_执行
	function channels_filterad(){
		$Form = M ('cid');
		if($_POST['cid']){
			$cid=$_POST['cid'];
			//$cid = $Form->where(array("chl" => $_POST['channel']))->getField('cid');
		}
        if(empty($cid)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_filteradd');
			$this->error('对不起，请输入有效渠道号，或选择有效渠道！');
        }
        if($_POST['filter']){
         $filter = $_POST['filter'];
        }
        $this->soft_db=M('soft');
        $channel_db = M('channel_adapter');
        $pkgs = $_POST['pkg'];
        $pkg_str = "'".implode("','",$pkgs)."'";
        $data = array();
        if($filter == "added"){
            $data['upload_time']=time();
            $data['last_refresh']=time();
            $data['status']=0;
            $channel_db -> where(array("cid" => $cid,"package" =>array("in",$pkg_str))) -> save($data);
        }else if($filter == "add"){
            foreach($pkgs as $pkg){
                $data = array();
                $data['package'] = $pkg;
                $data['upload_time'] = time();
                $data['last_refresh'] = time();
                $data['status'] = 1;
                $data['note'] = '';
                $data['cid'] = (int) $cid;
                $affected = $channel_db -> add($data);
            }
        }
        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_soft_list/cid/'.$_POST['cid'].'/filter/'.$filter);
        if($filter == 'added'){
           $this->writelog('删除 从渠道id为:'.$cid.'的过滤软件中 删除 package 为: ['.$pkg_str.'] 的软件','sj_channel_adapter',$cid,__ACTION__ ,"","del");
           $this->success('数据删除成功');
        }else{
           $this->writelog('添加 渠道id为:'.$cid.'的过滤软件  package 为: ['.$pkg_str.'] 的软件','sj_channel_adapter',$cid,__ACTION__ ,"","add");
          $this->success('数据添加成功');
        }
	}
    /*
    **   Edit By :Jinshan 2011/5/24
    **   查看密码页面
    */
    public function showcheckpassword() {
        $this->cid=$_GET['cid'];
        if(empty($this->cid)) {
            echo '对不起,出现了一点点错误';
        }
        $this->channel_db=M('channel');
        $password='';
        $map='';
        $map['cid']=$this->cid;
        $password=$this->channel_db->where($map)->getField('checkpassword');
        echo '密码为：'.$password;

    }
//=========================================================================
    //渠道管理_渠道不更新列表页面
	function channels_update(){
		$Form = M("pu_operating");
		$list1=$Form->query("select oid,mname from pu_operating");
//		$Form = M("manufacturer ");
//		$list2=$Form->query("select mid,mname from pu_manufacturer where status=1 and mid!=0 order by mid");
//		$Form = M("pu_device  ");
//		$list3=$Form->query("select did,dname from pu_device  where status=1 order by did ");
		$this->assign('list1',$list1);
		$this->assign('list2',$list2);
		$this->assign('list3',$list3);
		$this->display('channels_update');
	}
    //渠道管理_渠道不更新查找渠道
	function Channels_up(){
		$Form = M("pu_operating");
		$list1=$Form->query("select oid,mname from pu_operating");
		$Form = M("manufacturer ");
		$this->assign('list1',$list1);
		$this->display('channels_update');
	}
    //渠道管理_渠道不更新查找渠道号
	function Channels_upda(){
		$Form = M("pu_operating");
		$list1=$Form->query("select oid,mname from pu_operating");
		$this->assign('list1',$list1);
		$Form = M ('channel');
		if(empty($_GET['cid'])){
			$this->error("请选择渠道！！！");
			exit;
		}
		$cid_array['cid']=$_GET['cid'];
		$cid_array['chname']=$Form->where(array('status'=>1,'cid'=>$_GET['cid']))->getfield("chname");
		$Form = M ('channel_update');
		$arr = $Form->query("SELECT u.package, u.cid, u.status, s.softname FROM sj_channel_update AS u JOIN sj_soft AS s ON u.package = s.package WHERE u.cid ='".$cid_array['cid']."' AND u.status =1 group by u.package");
		$this->assign('arr',$arr);
		$this->assign('cid_array',$cid_array);
		$this->display('channels_update');
	}
    //渠道管理_取消不升级
	function Channels_u(){
		$Form = D("channel_update");

        $list['package']=$_GET['package'];
        $list['cid']=$_GET['cid'];
        if(empty($list['cid']) ||empty($list['package'])) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_update/');
			$this->error('发生了一点点错误,请重试');
        }
		//$map['last_refresh']=time();
		//$map['status']=0;
		$result=$Form->where($list)->delete();

		if($result!==false){
			$this->writelog('数据保存渠道取消不升级id为['.$list['cid'].']','sj_channel_adapter',$list['cid'],__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/Channels_upda/cid/'.$list['cid']);
			$this->success('数据更新成功');
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_update/');
			$this->error('数据更新失败');
		}
	}
	//批量操作不升级页面
	function channels_updateaddall(){
		if($_GET['submit'])
		if($_GET['cid']){
			if(!$_POST){
				import("@.ORG.Page");
				$filter = $_GET['filter'];
				$cid =$_GET["cid"];
				$channel_db  = M("channel");
				$chl_ad = M ('channel_update ');
				$category_db = M('category');
				$category_list = $category_db -> where("status = 1 and parentid <> 0") -> select();
				$soft_db = new Model();
				$chl_name = $channel_db -> where(array("cid" =>$cid,"status" => 1)) ->getfield("chname");
				//$cid = (int)$chl_info[0]['cid'];
				$chl_ad_pkg = $chl_ad -> where(array("cid" =>$cid , "status" => 1)) -> getField("package,cid");
				$pkgs = array_keys($chl_ad_pkg);
				$pkgs_str = "('".implode("','",$pkgs)."')";
				if($filter == "added"){
					$where = "ss.package in ".$pkgs_str." and ss.status = 1 and ss.hide = 1  ";
				}else if($filter == 'add'){
					$where = "ss.package not in ".$pkgs_str." and ss.status = 1 and ss.hide = 1  ";
				}
				if(!empty($_GET['softname'])) {
					$this->assign ("softname", $_GET['softname'] );
					$where .=' and ss.softname like "%'.trim(escape_string($_GET['softname'])).'%"';
				}
				if(!empty($_GET['package'])) {
					$this->assign ("package", $_GET['package'] );
					$where .=' and ss.package like "%'.trim(escape_string($_GET['package'])).'%"';
				}
				if(!empty($_GET['category_id']) && $_GET['category_id'] != 'all'){
					$this->assign ("category_id", $_GET['category_id'] );
					$where .=' and ss.category_id ='.'\','.escape_string($_GET['category_id']).',\'';
				}
				$count_sql = "select count(*) count from sj_soft as ss left join sj_soft_file as ssf on ss.softid = ssf.softid left join sj_category as sc on ss.category_id = concat(',',sc.category_id,',') where ".$where;
				$count_info = $soft_db -> query($count_sql);
				$count = $count_info[0]['count'];
				$p=new Page($count,20);
				$sql = "select ss.softid softid,ss.package,ss.softname,ssf.iconurl iconurl,sc.name cname from sj_soft as ss left join sj_soft_file as ssf on ss.softid = ssf.softid left join sj_category as sc on ss.category_id = concat(',',sc.category_id,',') where ".$where." limit ".$p->firstRow.','.$p->listRows;
				$softlist = $soft_db -> query($sql);
				$p->setConfig('header','篇记录');
				$p->setConfig('prev',"上一页");
				$p->setConfig('next','下一页');
				$p->setConfig('first','首页');
				$p->setConfig('last','末页');
				$page = $p->show();
				$this -> assign('filter',$filter);
				$this -> assign('chname',$chl_name);
				$this -> assign('cid',$cid);
				$this -> assign("page", $page );
				$this -> assign("softlist",$softlist);
				$this -> assign("category_list",$category_list);
				$this -> display("channels_soft_listall");
				exit;
			}else{
				if($_POST['cid']){
					$Form = M ('channel');
					//$cid = $Form->where(array("chl"=>$_POST['channel']))->getField('cid');
					$cid=$_POST['cid'];
				}
				if(empty($cid)) {
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_updateaddall');
					$this->error('对不起，请输入有效渠道号，或选择有效渠道！');
				}
				if($_POST['filter']){
					$filter = $_POST['filter'];
				}
				$this->soft_db=M('soft');
				$channel_db = M('channel_update');
				$pkgs = $_POST['pkg'];
				$pkg_str = "'".implode("','",$pkgs)."'";
				$data = array();
				if($filter == "added"){
					$data['upload_time']=time();
					$data['last_refresh']=time();
					$data['status']=0;
					$channel_db -> where("cid =".$cid." and package in (".$pkg_str.")") -> delete();
				}else if($filter == "add"){
					foreach($pkgs as $pkg){
						$data = array();
						$data['package'] = $pkg;
						$data['upload_time'] = time();
						$data['last_refresh'] = time();
						$data['status'] = 1;
						$data['note'] = '';
						$data['cid'] = (int) $cid;
						$affected = $channel_db -> add($data);
						
					}
				}
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_updateaddall/cid/'.$_POST['cid'].'/submit/tianjia/filter/'.$filter);
				if($filter == 'added'){
					$this->writelog('删除 从渠道id为:'.$cid.'的过滤软件中 删除 package 为: ['.$pkg_str.'] 的软件','sj_channel_adapter',$cid,__ACTION__ ,"","del");
					$this->success('数据删除成功');
				}else{
					$this->writelog('添加 渠道id为:'.$cid.'的过滤软件  package 为: ['.$pkg_str.'] 的软件','sj_channel_adapter',$cid,__ACTION__ ,"","add");
					$this->success('数据添加成功');
				}
			}
		}else{
			$this->error("请输入渠道号");
		}
		$this->display();
	}
    //添加不升级页面
	function channels_updateadd(){
		$Form = M("pu_operating");
		$list1=$Form->query("select oid,mname from pu_operating");
		$Form = M("pu_manufacturer ");
		$list2=$Form->query("select mid,mname from pu_manufacturer where status=1 and mid<>0 order by mid");
//		$Form = M("pu_device  ");
//		$list3=$Form->query("select did,dname from pu_device  where status=1 order by did ");
		$this->assign('list1',$list1);
		$this->assign('list2',$list2);
		//$this->assign('list3',$list3);
		$this->display('channels_updateadd');
	}
    //添加不升级页面方法
	function channels_updatead(){
		if($_POST['cid']){
			$Form =M ('channel');
			$list=$_POST['cid'];
			//$list=$Form->where(array('chl' => $_POST['chl']))->getField('cid');

		}else{
			$Form =M ('channel');
			$list=$Form->where(array("oid" => $_POST['oid'],"mid" => $_POST['mid'],"did" => $_POST['did']))->getField('cid');
		}
        if(empty($list)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_updateadd');
			$this->error('对不起，请输入有效渠道号，或选择有效渠道！');
        }
        $this->soft_db=M('soft');
        $this->soft_list=$this->soft_db->where(array('package' => $_POST['package']))->getField('softid');
        if(empty($this->soft_list)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_updateadd');
			$this->error('对不起，请输入有效包名，或选择有效渠道！');
        }


        $channel_db = M ('channel_update');
        $data['cid']=$list;
        $data['package']=$_POST['package'];
        $num=$channel_db->where($data)->count();
        if(empty($num)) {
            $data['upload_time']=time();
            $data['last_refresh']=time();

            $data['status']=1;
            $data['note']=$_POST['note'];
            $type=$channel_db->add($data);
        }else
        {
            $map['last_refresh']=time();
            $map['status']=1;
            $type=$channel_db->where($data)->save($map);
        }
        if($type ===false){
            $this->error('数据插入失败');
        }
        else{
        	$this->writelog('数据保存渠道不升级id为['.$data['cid'].']包名为['.$data['package'].']','sj_channel_adapter',$data['cid'],__ACTION__ ,"","edit");
            $this->success("数据保存成功！");
        }

	}
	/**
	 *
	 * 渠道用户激活系数，用于在前台的合作显示
	 */
	public function channels_coefficient_list(){
		define("GO_HIDE_SKIN", 4);
		$Form = M ('channel');
		$model = new Model();
        import("@.ORG.Page");

		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_SHOW_TYPE;
        $map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $res = $Form->table('sj_admin_filter')->where($map)->find();
        $show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
        $this->assign( "show_chl", $show_chl );

        $chl = '';
        if(isset($_REQUEST['chl'])&&$_REQUEST['chl']){
        	$chl = $_REQUEST['chl'];
        }
        $chname = '';
		if(isset($_REQUEST['chname'])&&$_REQUEST['chname']){
        	$chname = $_REQUEST['chname'];
        }
		if(isset($_GET['category_id'])){
			$this->assign('category_id', $_GET['category_id']);
        }
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql_where=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			//$target_type= CHANNEL_SHOW_CONTROL;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql_where = " cid in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}')  and  ";
		}
		$sql_where .= 'status=1';
		if($chl){
			$sql_where .= " and chl='{$chl}'";
		}
		if($chname){
			$sql_where .= " and chname like '%{$chname}%'";
		}
		if($_GET['category_id']){
			$category_id = substr($_GET['category_id'],0,-1);
			$sql_where .= " and category_id in ({$category_id})";
		}

		$count= $Form -> where($sql_where) ->count();
		$this -> assign('total', $count);
        $p=new Page($count,20);

        $list=$Form->where($sql_where)->limit($p->firstRow.','.$p->listRows)->order('submit_tm desc')->select();
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        if($chl) $this -> assign('chl', $chl);
        if($chname) $this -> assign('chname', $chname);
        $this->assign( "page", $page );
        $i=0;
        foreach($list as &$v)
        {
            $cid = $v['cid'];
            $sql = "select coefficient,create_time from sj_channel_coefficient where cid={$cid} and status=1" ;
            $coefficient = $model->query($sql);
            $v['coefficient'] = $coefficient[0]['coefficient'];
            $v['create_time'] = $coefficient[0]['create_time']?date("Y-m-d",$coefficient[0]['create_time']):"";
            $v['category_name'] = $category_list[$v['category_id']]['name'];
        }
        $this->assign ( "list", $list );
		$param = http_build_query($_GET);
		$this -> assign('param',$param);
        $this->display('channels_coefficient_list');
	}

	public function channels_coefficient_edit(){
		$cid = isset($_REQUEST['cid'])?escape_string($_REQUEST['cid']):'';
		$model = new Model();
		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_SHOW_TYPE;
        $map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $res = $model->table('sj_admin_filter')->where($map)->find();
        $show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
        $this->assign( "show_chl", $show_chl );
		if(empty($cid)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('没有cid参数错误');
		}
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $model->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql="select * from sj_channel where  cid={$cid} and status=1";
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;

			$sql = "select * from sj_channel where  cid  in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}') and cid={$cid} and status=1";
		}
		$channel = $model->query($sql);

		if(empty($channel)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('没有相应的渠道');
		}
		$sql = "select * from sj_channel_coefficient where cid={$cid} and status=1";
		$list = $model->query($sql);

		$list = $list[0];
		$list['cid'] = $cid;
		$list['chl'] = $channel[0]['chl'];
		$list['chname'] = $channel[0]['chname'];
		$list['coefficient'] = isset($list['coefficient']) ? $list['coefficient'] : 100;

		$this->assign('vo',$list);
		$this->display('channels_coefficient_edit');
	}
	public function channels_coefficient_update(){
		$cid = isset($_REQUEST['cid'])?escape_string($_REQUEST['cid']):'';
		$coefficient = isset($_REQUEST['coefficient'])?(int)$_REQUEST['coefficient']:0;
		if(empty($cid)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('没有cid参数错误');
		}
		if($coefficient>100 || $coefficient<0){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('数据大小错误');
		}

		$model = new Model();
		$sql = "select * from sj_channel_coefficient where cid={$cid} and status=1";
		$sql=mysql_real_escape_string($sql);
		$old = $model->query($sql);
		$t = time();
		if(!empty($old)){
			if($old[0]['coefficient']!=$coefficient){
				$sql = "update sj_channel_coefficient set last_refresh =".$t." ,status=0 where cid={$cid}";
				$model->query($sql);
				$sql = "insert into sj_channel_coefficient (cid,coefficient,create_time,status,last_refresh) values ($cid,$coefficient,$t,1,$t)";
				$model->query($sql);
			}
		}else{
			$sql = "insert into sj_channel_coefficient (cid,coefficient,create_time,status,last_refresh) values ($cid,$coefficient,$t,1,$t)";
			$model->query($sql);
		}

		if($list!==false){
			$this->writelog('编辑CID为['.$cid.']的渠道系数,渠道系数由['.$old[0]['coefficient'].']改为['.$coefficient.']','sj_channel_coefficient',$cid,__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->success('数据更新成功');
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels_coefficient_list');
			$this->error('数据更新失败');
		}
	}
    //渠道软件列表显示
    function channels_soft_list() {
        import("@.ORG.Page");
        $filter = $_GET['filter'];
        $cid =$_GET["cid"];
        $channel_db  = M("channel");
        $chl_ad = M ('channel_adapter ');
        $category_db = M('category');
        $category_list = $category_db -> where("status = 1 and parentid <> 0") -> select();

        $soft_db = new Model();
        $chl_name= $channel_db -> where(array("cid" =>$cid,"status" => 1)) ->getfield("chname");
        //$cid = (int)$chl_info[0]['cid'];
        $chl_ad_pkg = $chl_ad -> where('cid = '.$cid .' and status = 1') -> getField("package,cid");
        $pkgs = array_keys($chl_ad_pkg);
        $pkgs_str = "('".implode("','",$pkgs)."')";
        if($filter == "added"){
            $where = "ss.package in ".$pkgs_str." and ss.status = 1 and ss.hide = 1  ";
        }else if($filter == 'add'){
            $where = "ss.package not in ".$pkgs_str." and ss.status = 1 and ss.hide = 1  ";
        } else {
        	$where = 'ss.status = 1 and ss.hide = 1';
        }

        if(!empty($_GET['softname'])) {
    		$this->assign ("softname", $_GET['softname'] );
    		$where .=' and ss.softname like "%'.trim(escape_string($_GET['softname'])).'%"';
    	}
    	if(!empty($_GET['package'])) {
    		$this->assign ("package", $_GET['package'] );
    		$where .=' and ss.package like "%'.trim(escape_string($_GET['package'])).'%"';
    	}
        if(!empty($_GET['category_id']) && $_GET['category_id'] != 'all'){
    		$this->assign ("category_id", $_GET['category_id'] );
    		$where .=' and ss.category_id ='.'\','.escape_string($_GET['category_id']).',\'';
        }
        /*$count_sql = "select count(*) count from sj_soft as ss where ".$where;
		$count_sql=mysql_real_escape_string($count_sql);
        $count_info = $soft_db -> query($count_sql);
        $count = $count_info[0]['count'];*/
		$count = $soft_db -> table('sj_soft ss')->where($where) ->count();
        $p=new Page($count,20);
        $sql = "select ss.softid softid,ss.package,ss.softname,ssf.iconurl iconurl,sc.name cname from sj_soft as ss left join sj_soft_file as ssf on ss.softid = ssf.softid left join sj_category as sc on ss.category_id = concat(',',sc.category_id,',') where ".$where." limit ".$p->firstRow.','.$p->listRows;
        $softlist = $soft_db -> query($sql);
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show();
        $this -> assign('filter',$filter);
        $this -> assign('chname',$chl_name);
		 $this -> assign('cid',$cid);
        $this -> assign("page", $page );
        $this -> assign("softlist",$softlist);
        $this -> assign("category_list",$category_list);
        $this -> display("channels_soft_list");
    }

	function channels_recommend()
	{
		$type = 0;
		$dupl = 0;
		$succ = 0;
		$where='status=1 and hide=1';
		if(!empty($_GET['softid'])) {
    	$this->assign ("softid", $_GET['softid'] );
    	$where.=' and softid="'.trim(escape_string($_GET['softid'])).'"';
    }
    if(!empty($_GET['softname'])) {
    	$this->assign ("softname", $_GET['softname'] );
    	$where.=' and softname like "%'.trim(escape_string($_GET['softname'])).'%"';
    }
    if(!empty($_GET['package'])) {
    	$this->assign ("package", $_GET['package'] );
    	$where.=' and package like "%'.trim(escape_string($_GET['package'])).'%"';
    }
    if(!empty($_GET['dev_name'])) {
    	$this->assign ("dev_name", $_GET['dev_name'] );
    	$where.=' and dev_name like "%'.trim(escape_string($_GET['dev_name'])).'%"';
    }
    if(!empty($_GET['dev_id'])) {
    	$this->assign ("dev_id", $_GET['dev_id'] );
    	$where.=' and dev_id="'.trim(escape_string($_GET['dev_id'])).'"';
    }
    if(!empty($_GET['categoryid'])) {
   		$this->assign ("categoryid", $_GET['categoryid'] );
   		$where.=' and (SELECT find_in_set  ('.trim(escape_string($_GET['categoryid'])).',`category_id`)>0)';

    }
		if(!empty($_GET['channels']))
		{
			
			$type = 1;
			$channel_db=M('channel');
			$ret = $channel_db->field("`cid`,`chname`")->where(array("status" => 1,"chname" => array("like","%".$_GET['channels']."%")))->findAll();
			if($ret != NULL)
			{
				$this->assign('channel_list',$ret);
				$this->assign('channels',$_GET['channels']);
				$type = 1;
			}else{
				$type = 2;
			}
			$this->assign('type',$type);
		}
		if(!empty($_GET['cid']))
		{
			$this->assign('cid',$_GET['cid']);
		}
		if(!empty($_POST))
		{
			if(empty($_POST['cid'])) {
				$this->error('请选择要添加的渠道');
			}
			$this->assign('cid',$_POST['cid']);
			$channel_model = M('channel_recommend');
			$now = time();
			$start = strtotime($_POST['fromdate']);
			$end = strtotime($_POST['todate'])+86399;
			if(($start >= $end)||empty($start)||empty($end)||($start+86400 <= time()))
			{
				$this->error('请填写有效的时间');
			}
			$num = $channel_model->where(array("channel_cid" => $_POST['cid'],"status" => 1))->order('rank  desc')->find();
			if($num == NULL){
				$num['rank'] = 0;
			}
			foreach($_POST['id'] as $key => $value)
			{
				$zh_id=escape_string($_POST['id']);
				$chret = $channel_model->where("(`channel_cid` = {$zh_id}  and `status` = 1 and end_time > $start and  start_time >= $start  and `package` = '$value') or (`channel_cid` = {$zh_id}  and `status` = 1 and end_time < $end  and start_time > $end and `package` = '$value')")->find();
				if($chret != NULL)
				{
					$dupl++;
					continue;
				}else{
					$date = array('package'=> $value ,
												'channel_cid' => trim($_POST['cid']) ,
												'status' => 1 ,
												'created_at' => $now ,
												'updated_at' => $now ,
												'start_time' => $start,
												'end_time' => $end,
												'rank' => ++$num['rank'],
												);
					$addchret = $channel_model->data($date)->add();
					if($addchret > 0)
					{
						$writelog .= $addchret.',';
						$succ++;
					}else{
						$dupl++;
					}

				}
				$this->writelog('渠道推荐添加ID为'.$writelog,'sj_channel_recommend',$writelog,__ACTION__ ,"","add");
			}
			$this->assign('succ',$succ);
			$this->assign('dupl',$dupl);
			$this->assign('js',1);
		}

    $soft_db=M('soft');
    $soft_file_model = M('soft_file');
    import("@.ORG.Page");
    $count= $soft_db->where($where)->count();
    $Page=new Page($count,15, $param);
    $soft_list = $soft_db->field("`softid`,`softname`,`package`,`version`,`version_code`,from_unixtime(`upload_tm`) as `created_at`,from_unixtime(`last_refresh`) as `updated_at`")->order('last_refresh desc')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		$soft_list_mapped = array();
		foreach ($soft_list as $v)
			$soft_list_mapped[$v['softid']] = $v;
		$softids = array_keys($soft_list_mapped);
		$sids = implode(',', $softids);
		$file_list = $soft_file_model->field('`softid`,`iconurl`')->where("`softid` in ({$sids})")->select();
		foreach ($file_list as $v) {
			if (isset($soft_list_mapped[$v['softid']]))
				$soft_list_mapped[$v['softid']]['iconurl'] = $v['iconurl'];
		}

		$category_db = M('category');
    $category_list_parent = $category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();
		$category_list_child = $category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();
		for($i = 0;$i<count($category_list_parent);$i++) {

            for($j = 0;$j<count($category_list_child);$j++) {
                if($category_list_child[$j]['parentid'] == $category_list_parent[$i]['category_id']) {
                    $category_list_parent[$i]['child'][] = $category_list_child[$j];
                }
            }
        }
   	$this->assign('softlist',$soft_list_mapped);
   	$this->assign('categorylist',$category_list_parent);
		$Page->setConfig('header','篇记录');
    $Page->setConfig('first','<<');
    $Page->setConfig('last','>>');
    $show =$Page->show();
    if(!empty($_POST['fromdate'])&&!empty($_POST['todate'])){
    	$from_value = $_POST['fromdate'];
    	$to_value = $_POST['todate'];
    }elseif(!empty($_GET['fromdate'])&&!empty($_GET['todate'])){
    	$from_value = $_GET['fromdate'];
    	$to_value = $_GET['todate'];
    }else{
    	$from_value = date("Y-m-j",time());
    	$to_value = date("Y-m-j",time()+(7*86400));
    }
    if(!empty($_POST['cid'])&&!empty($_POST['fromdate'])&&!empty($_POST['todate'])) {
			if(!strstr($show,'/cid/')) {
				$p=array('/?p=' => "/cid/{$_POST['cid']}/fromdate/{$_POST['fromdate']}/todate/{$_POST['todate']}/?p=");
				$show = strtr($show,$p);
			}else{
				$show = preg_replace("/\/cid\/[^\/]*\//", "/cid/{$_POST['cid']}/", $show);
				$show = preg_replace("/\/fromdate\/[^\/]*\//", "/fromdate/{$_POST['fromdate']}/", $show);
				$show = preg_replace("/\/todate\/[^\/]*\//", "/todate/{$_POST['todate']}/", $show);
			}
		}
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid[]",
			"selected"=>$_GET['categoryid'],
		);
		$conf_list = $category->getCategory($array_config);
		$this->assign('conflist',$conf_list);
		$this->assign('from_value',$from_value);
		$this->assign('to_value',$to_value);
		$this->assign ("page", $show );
		$this->display();
	}

	function channels_recommend_check()
	{
		$type = 0;
		$dupl = 0;
		$succ = 0;
   	$soft_db=M('channel_recommend');
   	$order = 'updated_at desc';
   	$where = "`status` = 1";
   	$sys = 0;
   	if(($_GET['timetype'] == 1)){
   		$where .= " and start_time < ".time().' and end_time >'.time()." ";
   		$sys = 1;
   	}elseif($_GET['timetype'] == 2){
   		$where .= " and start_time > ".time().' and end_time >'.time()." ";
   		$this->assign('timetype',2);
   		$sys = 1;
   	}elseif($_GET['timetype'] == 3){
   		$where .= " and start_time < ".time().' and end_time <'.time()." ";
   		$this->assign('timetype',3);
   		$sys = 1;
   	}elseif($_GET['timetype'] == 4){
   		$this->assign('timetype',4);
   		$sys = 1;
   	}else{
   		$where .= " and start_time < ".time().' and end_time >'.time()." ";
   	}

   	if(!empty($_GET['cid'])){
			$where .= " and `channel_cid` = '".trim(escape_string($_GET['cid']))."'";
			$order = 'rank asc';
			$this->assign('cid',$_GET['cid']);
			$sys = 1;
		}

		if(!empty($_GET['softname'])) {
    	$this->assign ("softname", $_GET['softname'] );
    	$ret = $soft_db->table("sj_soft")->where(array("softname" =>trim($_GET['softname'])))->find();
    	if($ret != NULL)
    	{
    		$where.=' and package="'.$ret['package'].'"';
    		$sys = 1;
    	}
    }

		if(!empty($_GET['softid'])) {
    	$this->assign ("softid", $_GET['softid'] );
    	$ret = $soft_db->table("sj_soft")->where(array("softid" =>trim($_GET['softid'])))->find();
    	if($ret != NULL)
    	{
    		$where.=' and package="'.$ret['package'].'"';
    		$sys = 1;
    	}
    }
   	if(!empty($_GET['package'])) {
    	$this->assign ("package", $_GET['package'] );
    	$where.=' and package like "%'.trim(escape_string($_GET['package'])).'%"';
    	$sys = 1;
    }

   	$now = time();

   	IF(!empty($_POST))
   	{
   		foreach($_POST[id] as $key => $value)
   		{
   			$ret = $soft_db->data(array("status" => 0 , "updated_at" => $now))->where(array("id" => $value))->save();
   			if($ret > 0){
   				$log .= $ret.',';
   				$succ++;
   			}else{
   				$dupl++;
					continue;
   			}
   		}
   		$this->writelog('渠道推荐删除ID为'.$log,'sj_channel_recommend',$log,__ACTION__ ,"","del");
   		$this->assign('succ',$succ);
			$this->assign('dupl',$dupl);
   		$this->assign('js',1);
   	}

   	if(!empty($_GET['channels']))
		{
			$type = 1;
			$channel_db=M('channel');
			$ret = $channel_db->field("`cid`,`chname`")->where(array("status" => 1,"chname" =>array("like","%".$_GET['channels']."%")))->findAll();
			if($ret != NULL)
			{
				$this->assign('channel_list',$ret);
				$this->assign('channels',$_GET['channels']);
				$type = 1;
			}else{
				$type = 2;
			}
			$this->assign('type',$type);
		}

		if($sys == 1)
		{

   	$category_db = M('category');


    $category_list_parent = $category_db->field('category_id,name')->where('status=1 and parentid=0')->order('orderid')->select();
		$category_list_child = $category_db->field('category_id,name,parentid')->where('status=1 and parentid!=0')->order('orderid')->select();
		for($i = 0;$i<count($category_list_parent);$i++) {
    	for($j = 0;$j<count($category_list_child);$j++) {
        if($category_list_child[$j]['parentid'] == $category_list_parent[$i]['category_id']) {
        	$category_list_parent[$i]['child'][] = $category_list_child[$j];
      	}
    	}
    }
   	$this->assign('categorylist',$category_list_parent);

   	import("@.ORG.Page");
    $count= $soft_db->where($where)->count();
   	$Page=new Page($count,15, $param);
   	$soft_list = $soft_db->order($order)->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
   	foreach($soft_list as $key => $value)
   	{
   		$list_channel = $soft_db->table('sj_channel')->field("`cid`,`chname`")->where(array("status" => 1,"cid" =>$value['channel_cid']))->find();
   		$list_soft = $soft_db->table('sj_soft')->field("`softid`,`softname`,`package`,`version`,`version_code`")->where("`package` = '{$value['package']}'")->find();
			$list_soft_file = $soft_db->table('sj_soft_file')->field('`softid`,`iconurl`')->where("`softid` in ({$list_soft['softid']})")->find();
			$soft_list[$key]['softname'] =  $list_soft['softname'];
			$soft_list[$key]['version'] =  $list_soft['version'];
			$soft_list[$key]['version_code'] =  $list_soft['version_code'];
			$soft_list[$key]['iconurl'] =  $list_soft_file['iconurl'];
			$soft_list[$key]['chname'] =  $list_channel['chname'];
			$soft_list[$key]['softid'] = $list_soft['softid'];
				if($value['start_time'] > $now){
					$soft_list[$key]['timetype'] = 2;
				}elseif($value['end_time'] < $now){
					$soft_list[$key]['timetype'] = 3;
				}
   	}
   	if(!empty($_GET['p'])) {
   		$this->assign("p",$_GET['p']);
   		$this->assign("tpage" , 1);
   	}
   	$this->assign('softlist',$soft_list);
		$Page->setConfig('header','篇记录');
    $Page->setConfig('first','<<');
    $Page->setConfig('last','>>');
    $show =$Page->show();
    $this->assign ("page", $show );
  	}
		$this->display();
	}

	function channels_recommend_del()
	{
		if(!empty($_GET))
		{
			$channel_db=M('channel_recommend');
			$ret = $channel_db->data(array("status" => 0,"updated_at"=> time()))->where(array("id" => $_GET['id']))->save();
			if($ret > 0){
				$this->writelog('渠道推荐删除ID为'.$_GET['id'],'sj_channel_recommend',$_GET['id'],__ACTION__ ,"","del");
				$this->success('删除成功');
			}else{
				$this->error('数据更新失败');
			}
		}
	}
	function channels_recommend_edit()
	{
		if(empty($_GET['id']))
		{
			$this->error("非法操作失败,如频繁出现，请联系管理员！");
		}
		$channel_db=M('channel_recommend');
		$ret = $channel_db->where(array("id" =>trim($_GET['id'])))->find();

		if(!empty($_POST))
		{
			$start = strtotime($_POST['fromdate']);
			$end = strtotime($_POST['todate']);
			$zh_where_id=escape_string($_GET['id']);
			$rank = trim($_POST['rank']);
			$chret = $channel_db->where("(`id` <> {$zh_where_id} and `channel_cid` = {$ret['channel_cid']}  and `status` = 1 and  `end_time` > $start and  `start_time` > $start  and  `package` = '{$ret['package']}' ) or (`id` <> {$zh_where_id} and `channel_cid` = {$ret['channel_cid']}  and `status` = 1  and `end_time` < $end  and `start_time` > $end  and  `package` = '{$ret['package']}' ) ")->find();
			if($chret != NULL)
				{
					$this->error('数据更新失败');
				}else{
					$chretlist = $channel_db->where("`channel_cid` = {$ret['channel_cid']} and `status` = 1 and `end_time`> ".time()." ")->order('rank')->findAll();
					$c = count($chretlist);
					$i=1;
					foreach($chretlist as $key => $value)
					{
						if($value['id']==$_GET['id']){
							$chretlist[$key]['rank']=$rank>$c?$c:$rank;
							$chretlist[$key]['start_time'] = $start;
							$chretlist[$key]['end_time'] = $end;
							$channel_db->data($chretlist[$key])->where("`id` = {$chretlist[$key]['id']}")->save();
						}else{
							$chretlist[$key]['rank'] = ($i==$rank?++$i:$i);
							$channel_db->data($chretlist[$key])->where("`id` = {$chretlist[$key]['id']}")->save();
							$i++;
						}
					}
					$this->writelog('渠道推荐修改ID为'.$_GET['id'],'sj_channel_recommend',$_GET['id'],__ACTION__ ,"","edit");
					$this->success('修改成功');
				}

		}
		$ret = $channel_db->where("`id`= ".trim($_GET['id']))->find();
		$softret = $channel_db->table('sj_soft')->where("`package` = '{$ret['package']}' ")->find();
		$ret['softname'] = $softret['softname'];
		$this->assign('url',"/cid/{$ret['channel_cid']}/p/{$_GET['p']}");
		$this->assign('featuresoftlist',$ret);
		$this->display();
	}
    function chl_app2sd() {
        import("@.ORG.Page");
        $chl_app2sd_db = M("channel_app2sd");
        $chl_db = M("channel");
        $where = "status = 1";
        $count = $chl_db -> where($where) -> count();
        $chl_app2sd_list = $chl_app2sd_db -> where("status =1 ") -> select();
        $chl_app_str= "";
        foreach($chl_app2sd_list as $info){
            $app_chl_arr[] = $info['chl_id'];
        }
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $chl_db->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$where.=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$zh_sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
			$res = $chl_app2sd_db->query($zh_sql);
			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
			$not_in_string = empty($in_cid_array) ? 'cid IN("")': ' and cid IN('. implode(',', $in_cid_array). ')';
			$where .= empty($not_in_string) ? '' : "{$not_in_string} ";
		}
		
		
		/* $source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_FILTER_TYPE;
        $zh_sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
		$res = $chl_app2sd_db->query($zh_sql);
		foreach ($res as $item) {
        	$in_cid_array[] = $item['cid'];
        }
			$not_in_string = empty($in_cid_array) ? 'cid IN("")': ' and cid IN('. implode(',', $in_cid_array). ')';
			$where .= empty($not_in_string) ? '' : "{$not_in_string} ";
		 */
			
        if($app_chl_arr){
           $where .= " and cid not in (".implode(',',$app_chl_arr).")";
        }
        if($_POST['srch_key']){
           $where .=   $_POST['type'] == 2 ? " and chl like  '%".escape_string($_POST['srch_key'])."%'" : " and chname like  '%".escape_string($_POST['srch_key'])."%'";
        }
        $count = $chl_db -> where($where)->count();
        $Page=new Page($count,100);
        $chl_list = $chl_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this -> assign("page",$show);
        $this -> assign("chl_list" , $chl_list);
        $this -> assign("search_key",$_POST['srch_key']);
        $this -> display("chl_app2sd");
    }
    function chl_add2sd() {
        $chl_app2sd_db = M("channel_app2sd");
        $cid_arr = $_POST['chk_chl'];
        foreach($cid_arr as $cid){
            $ct = time();
            $data['chl_id'] = $cid;
            $data['status'] = 1;
            $data['create_tm'] = $ct;
            $data['last_refresh'] = $ct;
            $chl_app2sd_db -> add($data);
            $data = array();
            $this->writelog('渠道适配app2sd 添加渠道ID为：'.$cid,'sj_channel_app2sd',$cid,__ACTION__ ,"","add");
        }
            $this->success('添加成功');
    }

    function chl_delete2sd() {
        $chl_app2sd_db = M("channel_app2sd");
        $cid_arr = $_POST['chk_chl'];
        foreach($cid_arr as $cid){
            $ct = time();
            $where = "chl_id =".$cid;
            $data['status'] = 0;
            $data['last_refresh'] = $ct;
            $chl_app2sd_db -> where($where) -> save($data);
            $data = array();
            $this->writelog('渠道适配app2sd 删除渠道ID为：'.$cid,'sj_channel_app2sd',$cid,__ACTION__ ,"","del");
        }
            $this->success("删除成功");
    }
    function app2sd_chl_list() {
        import("@.ORG.Page");
         $chl_app2sd_db = M("channel_app2sd");
        $chl_db = M("channel");
        $count = $chl_db -> where($where) -> count();
        $chl_app2sd_list = $chl_app2sd_db -> where("status =1 ") -> select();
        if($_POST['srch_key']){
           $where .=   $_POST['type'] == 2 ? " and sc.chl like  '%".trim(escape_string($_POST['srch_key']))."%'" : " and sc.chname like  '%".trim(escape_string($_POST['srch_key']))."%'";
        }
		//by zhang
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $chl_db->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$where .=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$zh_sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
			$res = $chl_app2sd_db->query($zh_sql);
			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
			$not_in_string = empty($in_cid_array) ? 'cid IN("")': ' and cid IN('. implode(',', $in_cid_array). ')';
			$where .= empty($not_in_string) ? '' : "{$not_in_string} ";
		}
		//by zhang end
        $sql = "select sc.chname,sca.chl_id cid from sj_channel_app2sd as sca join sj_channel as sc on sca.chl_id = sc.cid where sca.status = 1 and sc.status = 1 ".$where;
        $result = $chl_db -> query($sql);
        $count = count($result);
        $Page=new Page($count,100);
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this -> assign("page",$show);
        $limit = " limit ".$Page->firstRow.','.$Page->listRows;
        $sql .= $limit;
        $chl_list = $chl_db -> query($sql);
        $this -> assign("page",$show);
        $this -> assign("chl_list" , $chl_list);
        $this -> assign("search_key",$_POST['srch_key']);
        $this -> display("app2sd_chl_list");
    }
	function channels_bounding(){
		$model = new Model();
		$opt_list = $model -> table('pu_operating') -> select();
		$str = explode('&',$_GET['cid']);
		$cid = $str[0];
		$chl_info = $model -> table('sj_channel') -> where('cid='.$cid) -> select();
		$this -> assign('opt_list',$opt_list);
		$this -> assign('chl_info',$chl_info[0]);
		$this -> display('channels_bounding');
	}
	function channels_bounding_do(){
		$model = M('channel');
		$map['cid'] = $_GET['cid'];
		$data['oid'] = $_GET['oid'];
		$affect = $model-> where($map) -> save($data);
		if($affect){
			$this -> writelog("cid".$_GET['cid']."的运营商重新绑定为oid:".$_GET['oid'],'sj_channel',$cid,__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Channels/channels');			
			$this->success('运营商重新绑定成功！');
		}else{
			$this -> error('绑定失败！！');
		}
	}
	public function require_url($url, $data = array(), $timeout = 4){
		if ( !is_array($data) || !$data) { return False; }
		if ($data) { $str = http_build_query($data); }
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		if ($str) { curl_setopt($ch, CURLOPT_POSTFIELDS, $str); }
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$result = curl_exec($ch);
		//var_dump(curl_getinfo($ch));
		curl_close($ch);
		return $result;
	}

	function channels_update_time(){
		$model = M("channel");
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);
      
        $source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $model->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql_where=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql_where = " where cid in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}')  and status=1 ";
		}
		$sql = "select * from sj_channel {$sql_where}";
		$chname = $model->query($sql);
		foreach($chname as $k=>$v){
			$id_data[] = $v['cid'];
		}

		$id_str = "(".implode(",",$id_data).")";
		$list = $model->table('sj_channel A LEFT JOIN sj_channel_update_time B ON A.cid=B.cid')->where("B.status=1 and B.cid in $id_str")->field('A.*, B.*')->order('B.create_time desc')->select();
		$count = $model->table('sj_channel_update_time')->where("status=1 and cid in $id_str")->count(); 
		foreach($list as $key=>$val){
				switch ($list[$key]['activation_type']){
                case 5:  $list[$key]['activation_type_name'] = '普通非山寨';break;
                case 6:  $list[$key]['activation_type_name'] = '严格非山寨';break;
                case 9:  $list[$key]['activation_type_name'] = '普通山寨';break;
                case 10:  $list[$key]['activation_type_name'] = '严格山寨';break;
            }
            $list[$key]['p_id']=$count--;
		}
		$day = $model->table("pu_config")->where("config_type = 'channel_update_time'")->field('configcontent')->find();
		$this->assign('day',$day['configcontent']);
		$this->assign('list',$list);
		$this->display('channels_update_time');
	}


	function channels_update_add(){
		$cid[] = $_POST['cid'];

		$update_time = $_POST['update_time'];
		$time = time();
		if(!isset($_POST['cid'])){
			$this -> error("请选择渠道");
		}
			if(is_numeric($update_time)==1){
			$model = M("channel_update_time");
			foreach ($cid as $key => $value) {
				$str =  implode(",",$value);
			}
			$cid_str = "(".$str.")";

			//$sql = "select * from sj_channel_update_time as A left join  sj_channel as B on A.cid=B.cid where A.cid in $cid";
			$sql = "select * from sj_channel_update_time where cid in $cid_str and status = 1";
			$list = $model->query($sql);
			foreach($list as $k=>$v){
				$chname = $v['chname'].',';
			}
		
			if(!empty($list)){
				$this -> error("添加失败,该渠道已存在，请直接前往修改即可");
			}else{
				// $log = $this->logcheck(array('id'=>$cid_str),'sj_channel_update_time',$data,$model);
				foreach($cid[0] as $key=>$val){
					$data['cid'] = $val;
					$data['create_time'] = $time;
					$data['status'] = 1;
					$data['update_time'] = $update_time;
					$ret=$model->data($data)->add();
					$this->writelog("渠道管理-渠道更新时间设置-添加ID为{$ret}",'sj_channel_update_time',$ret,__ACTION__ ,"","add");
				}
				$this -> success('添加成功！！');
			}
		}
	else{
			$this -> error('更新时间仅支持大于等于0的数字，请重新输入');
		}
	}


	function channels_updatetime_del(){
		$User = new Model();
		$id = $_GET['id'];
		$data['status'] = 0;
		$result = $User->table('sj_channel_update_time')->where("id = $id")->save($data);
		$this->writelog("渠道管理-渠道更新时间设置-删除ID为{$id}的信息",'sj_channel_update_time',$id,__ACTION__ ,"","del");
		$this -> success('删除成功！！');
	}

	function channels_updatetime_del_glob(){
		$User = new Model();
		$val = (int)$_POST['val'];
		$time = time();
		if($val >=1 && is_int($val)==1){
			$data['configcontent'] = $val;
			$data['uptime'] = $time;
			$list = $User->table('pu_config')->where("config_type = 'channel_update_time'")->find();
			$conf_id = $list['conf_id'];
			if($val == $list['configcontent']){
				echo 2;
			}else{

				$result = $User->table('pu_config')->where("conf_id = $conf_id")->save($data);
				$this->writelog("渠道管理-渠道更新时间设置-编辑全局时间为{$val}天",'pu_config',$conf_id,__ACTION__ ,"","edit");
				if($result==1){
					echo 1;
				}else{
					echo 0;
				}
			}
		}else{
			 echo 3;
		}
	}


	function channels_update_edit(){
		$User = new Model();
		$id = $_POST['id'];
		$result = $User->table('sj_channel_update_time')->where("id = $id")->find();
		echo $result['update_time'];
	}

	function channels_update_edit_to(){
		$User = new Model();
		$data['update_time'] = $_POST['update_time'];

		$data['up_time'] = time();
		$id = $_POST['id'];
		if(is_numeric($data['update_time'])!=1){
			$this -> error('更新时间仅支持大于等于0的数字，请重新输入');
		}else{
			$log = $this->logcheck(array('id'=>$id),'sj_channel_update_time',$data,$User);
			$result = $User->table('sj_channel_update_time')->where("id = $id")->save($data);
			$this->writelog("渠道管理-渠道更新时间设置-编辑ID为{$id},".$log,'sj_channel_update_time',$id,__ACTION__ ,"","edit");
			if($result==1){
				$this -> success('编辑成功！！');
			}else{
				$this -> error('编辑失败');
			}
		}
	}
	//渠道类型
	function pub_get_channelCategory(){
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		$this->assign('category_list', $category_list);
		if(isset($_GET['category_id'])){
			$category_id = explode(',',substr($_GET['category_id'],0,-1));
			$this->assign('category_id',$category_id);
		}	
		$this->display('channelcategory');
	}
	//渠道系数列表导出
	function exp_channels_coefficient(){
		define("GO_HIDE_SKIN", 4);
		$Form = M ('channel');
		$model = new Model();
        import("@.ORG.Page");

		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_SHOW_TYPE;
        $map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $res = $Form->table('sj_admin_filter')->where($map)->find();
        $show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;

        $chl = '';
        if(isset($_REQUEST['chl'])&&$_REQUEST['chl']){
        	$chl = $_REQUEST['chl'];
        }
        $chname = '';
		if(isset($_REQUEST['chname'])&&$_REQUEST['chname']){
        	$chname = $_REQUEST['chname'];
        }
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql_where=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql_where = " cid in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}')  and  ";
		}
		$sql_where .= 'status=1';
		if($chl){
			$sql_where .= " and chl='{$chl}'";
		}
		if($chname){
			$sql_where .= " and chname like '%{$chname}%'";
		}
		if($_GET['category_id']){
			$category_id = substr($_GET['category_id'],0,-1);
			$sql_where .= " and category_id in ({$category_id})";
		}
		$total = $_GET['count'];
		$p = isset($_GET['pp']) ? $_GET['pp'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 1000;	
		$totalPages = ceil($total/$limit);
		if($p == 1){
			$firstRow = 0;
		}else{
			$firstRow = ($p-1) * $limit;
		}

        $list=$Form->where($sql_where)->order('submit_tm desc')->limit($firstRow.','.$limit)->select();
		if (!isset($_GET['fid'])) {
			$fid = uniqid();
		} else {
			$fid = $_GET['fid'];
		}
		mkdir('/tmp/export/', 0755, true);
		$file = '/tmp/export/'. session_id(). '_'.$fid.'export'. ".csv";
		$file = fopen($file, 'a');
		if($p ==1){
			fwrite($file,chr(0xEF).chr(0xBB).chr(0xBF)); 
			$heade = array('渠道号','渠道名','渠道类型','渠道系数','生效时间');		
			fputcsv($file, $heade);
		}
        foreach($list as $v){
			$put_arr = array();
			$put_arr['chl'] = $show_chl ? $v['chl'] : "\t";
			$put_arr['chname'] = $v['chname'] ? $v['chname'] : "\t";
			$put_arr['category_name'] = $category_list[$v['category_id']]['name'] ? $category_list[$v['category_id']]['name'] : "\t";
			
            $sql = "select coefficient,create_time from sj_channel_coefficient where cid={$v['cid']} and status=1" ;
			$sql=mysql_real_escape_string($sql);
            $coefficient = $model->query($sql);
			
			$put_arr['coefficient'] = $coefficient[0]['coefficient'] ? $coefficient[0]['coefficient'] : "\t";
			$put_arr['create_time'] = $coefficient[0]['create_time']?date("Y-m-d",$coefficient[0]['create_time']): "\t";
			fputcsv($file, $put_arr);	
        }
		fclose($file);	
		$next_page = $p + 1;
		if ($p != $totalPages) {
			$par = $_GET;
			unset($par['pp'],$par['fid'],$par['button'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Sj/Channels/exp_channels_coefficient/pp/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$name_str = "渠道系数列表_".date("Y-m-d",time());
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}/name/{$name_str}",
			);	
		}
		exit(json_encode($data));
	}
}
?>
