<?php

class CollectionAction extends CommonAction {

    function index(){
        import("@.ORG.Page");
        $coll_model= D('Caiji.Collection');
        $count = $coll_model->count();
        $page = new Page($count, 15);
        $config_list = $coll_model->field("`id`,`cj_website`,`cj_status`,`cj_url`")->limit($page->firstRow.','.$page->listRows)->select();
        	
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("page", $show);
        $this->assign("configlist" , $config_list);

        $this->display('index');
    }
    
    function collection_add(){
        $this->display('collection_add');
    }
     
    function collection_modify(){
        $id=$_GET['id'];
        $coll_model= D('Caiji.Collection');
        $config_info = $coll_model->field(" `id`,`cj_website`,`cj_weburl`,`cj_url`,`rootnode`,`cj_liststr`,`cj_detailstr`,`cj_status`,`pagelistend`,`listfunc`,`detailfunc`")->where(array('id' => $id))->select();

        $this->assign("id" , $config_info[0]['id']);
        $this->assign("cj_website" , $config_info[0]['cj_website']);
        $this->assign("cj_weburl" , $config_info[0]['cj_weburl']);
        $this->assign("cj_url" , $config_info[0]['cj_url']);
        $this->assign("rootnode" , $config_info[0]['rootnode']);
        $this->assign("cj_liststr" , $this->arr2str($config_info[0]['cj_liststr']));
        $this->assign("cj_detailstr" , $this->arr2str($config_info[0]['cj_detailstr']));
        $this->assign("cj_status" , $config_info[0]['cj_status']);
        $this->assign("pagelistend" , $config_info[0]['pagelistend']);
        $this->assign("listfunc" , $config_info[0]['listfunc']);
        $this->assign("detailfunc" , $config_info[0]['detailfunc']);
        $this->display('collection_modify');
    }
    
    function collection_save(){
        $cj_website = trim($_POST['cj_website']);
        $cj_weburl = trim($_POST['cj_weburl']);
        $cj_url =  trim($_POST['cj_url']);
        $rootnode =  $_POST['rootnode'];
        $cj_liststr = $this->str2arr($_POST['cj_liststr']);
        $cj_detailstr =  $this->str2arr(($_POST['cj_detailstr']));
        $cj_status = $_POST['cj_status'];
        $pagelistend = trim($_POST['pagelistend']);
        $listfunc = $_POST['listfunc'];
        $detailfunc = $_POST['detailfunc'];

        $action = $_GET['action'];

        if(empty($cj_website) || empty($cj_url) ){
            $this->error("参数错误！");
        }
        $coll_model = D('Caiji.Collection');
        if(!empty($action)){
            $id = $_GET['id'];
            $data=array('cj_website'=>$cj_website,'cj_weburl'=>$cj_weburl,'cj_url'=>$cj_url,'rootnode'=>$rootnode,'cj_liststr'=>$cj_liststr,'cj_detailstr'=>$cj_detailstr,'cj_status'=>$cj_status,'pagelistend'=>$pagelistend,'listfunc'=>$listfunc,'detailfunc'=>$detailfunc);
            $log_result = $this->logcheck(array('id' => $id),'caiji_config',$data,$coll_model);
            $affect = $coll_model->where(array('id' => $id))->save($data);
            if ($affect) {
	            $msg = "修改采集站点配置,{$log_result}";
            }
			if ($affect > 0) {
				$this->writelog($msg,'caiji_config',$id,__ACTION__ ,"","edit");
				$this->assign('jumpUrl',"__URL__/index");
				$this->success("修改成功！");
			}else {
				$this->error("修改失败！");
			}
	    }else{
	        $affect = $coll_model->add(array('cj_website'=>$cj_website,'cj_weburl'=>$cj_weburl,'cj_url'=>$cj_url,'rootnode'=>$rootnode,'cj_liststr'=>$cj_liststr,'cj_detailstr'=>$cj_detailstr,'cj_status'=>$cj_status,'pagelistend'=>$pagelistend,'listfunc'=>$listfunc,'detailfunc'=>$detailfunc));
	        if ($affect) {
	            $msg = '添加采集站点配置,采集站点名称为'.$cj_website.',采集站点url:'.$cj_url;
	        }
	    }
	
		if ($affect > 0) {
		    $this->writelog($msg,'caiji_config',$affect,__ACTION__ ,"","add");
		    $this->assign('jumpUrl',"__URL__/index");
		    $this->success("添加成功！");
		}else {
		    $this->error("添加失败！");
		}
	}

    private function str2arr($str)
    {
        $new_arr = array();
        $str = str_replace("\r\n","",trim($str));
        //$str = str_replace(" ","",$str);
        $str_arr = explode(';',$str);
        foreach($str_arr as $k=>$v)
        {
            $ll[$k] = explode(':',$v);
        }

        foreach($ll as $k1 => $v1)
        {
            $new_arr[$v1[0]] = $v1[1];
        }
        return serialize($new_arr);
    }

    private function arr2str($str)
    {
        $arr = unserialize($str);

        $str_new = '';
        foreach($arr as $k=>$v)
        {
            $str_new = $str_new.$k.":".$v.";\r";
        }
        return substr($str_new,0,-2);
    }   
	//采集更新待审核
	function collection_update_audit(){
		$model = new Model();
		$caiji_model = D('Caiji.Caiji');
		$time = time();
		$where = array(
			'status' => 1
		);
		if(isset($_GET['dev_type']) && isset($_GET['dev_data'])){
			$where_d = array();
			if($_GET['dev_type'] == 1){
				$where_d['p.dev_name'] = array('like',"%{$_GET['dev_data']}%");
			}else if($_GET['dev_type'] == 2){
				$where_d['p.dev_id'] = $_GET['dev_data'];
			}else if($_GET['dev_type'] == 3){
				$where_d['p.email'] = $_GET['dev_data'];
			}
			$this -> assign('dev_type',$_GET['dev_type']);	
			$this -> assign('dev_data',$_GET['dev_data']);	
			$dev_arr = $model->table('pu_developer p')->join('sj_soft s ON p.dev_id = s.dev_id')->where($where_d)->field('s.package')->select();
			foreach ($dev_arr as $v ){
				$pkg[] = $v['package'];
			}
			$where['package'] = array("in",$pkg);
		}
		$this->check_where($where, 'softname', 'isset', 'like');
		isset($_GET['examine_type']) ? $_GET['examine_type'] : $_GET['examine_type'] = 2;	
		$this->check_where($where, 'examine_type');
		$this->check_where($where, 'package');
		$this->check_where($where, 'az_category');
		$this->check_where($where, 'appfrom');
		$this->check_where($where, 'is_office', 'isset');	
		$this->check_range_where($where, 'begintime', 'endtime', 'create_time', true);
		$down_str = $_GET['down_str']*10000;
		$down_end = $_GET['down_end']*10000;
		if(strlen($_GET['down_str'])>0){
			if(strlen($_GET['down_end'])>0){
				$where['az_downloaded']  = array('between',''.$down_str.','.$down_end);
			}else{
				$where['az_downloaded']  = array('egt',$down_str);
			}
		}else if(strlen($_GET['down_end'])>0){
			$where['az_downloaded']  = array('elt',$down_end);
		}
		$this->assign('down_str',$_GET['down_str']);
		$this->assign('down_end',$_GET['down_end']);

		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_update');
		$examine_total = $caiji_model ->query("SELECT COUNT(DISTINCT package) AS tp_count,examine_type FROM `cj_soft_update` WHERE ( `status` = 1 ) GROUP BY examine_type ");
		foreach($examine_total as $k => $v){
			$examine_total[$v['examine_type']]['tp_count'] = $v['tp_count'] ? $v['tp_count'] : 0;
		}
		$this->assign('examine_total', $examine_total);
		$pkg = array();
		foreach($list as $k => $v){
			$pkg[] = $v['package'];
		}
		if($pkg){
			$where = array(
				'package' => array('in',$pkg),
				'status' => 1,
				'hide' => 1,
			);
			$softinfo = get_table_data($where,"sj_soft","package","dev_id,package,version_code,version,safe,softid,softname");	
			$where = array(
				'package' => array('in',$pkg),
				'status' => 1,
				'start_time' => array('exp',"<={$time}"),
				'terminal_time' => array('exp',">={$time}"),
			);	
			$note_arr = get_table_data($where,"sj_soft_note","package","package,status");
			$where = array(
				'apk_name' => array('in',$pkg),
				'package_status' => 1,
			);	
			$file_arr = get_table_data($where,"sj_soft_file","apk_name","id,apk_name,leafstatus");
			$where = array(
				'package' => array('in',$pkg),
				'status' => 1,
			);	
			$ad_library = get_table_data($where,"sj_ad_library","package","id,package");
			//检测排期
			$soft_mdel = D('Dev.Softlist');
			$whiteList = $soft_mdel ->soft_WhiteList($pkg);
		}	
//var_dump(md5(UPLOAD_PATH . $list[0]['icon_path']));		
		//整理数据	
		$dev_id = array();
		foreach($list as $k => $v){
                        if($list[$k]['version_code']>=5*$softinfo[$v['package']]['version_code']){
			    $list[$k]['ver_code_warn'] = 1;
                        }else{
			    $list[$k]['ver_code_warn'] = 2;
                        }

			$list[$k]['pkg_str'] = str_replace('.','_',$v['package']);
			$list[$k]['create_time'] = $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']):'';
			$list[$k]['az_downloaded'] = number_format($v['az_downloaded']);
			$list[$k]['az_version_code'] = $softinfo[$v['package']]['version_code'];
			$list[$k]['az_version'] = $softinfo[$v['package']]['version'];
			$list[$k]['az_safe'] = $softinfo[$v['package']]['safe'];
			$list[$k]['dev_id'] = $softinfo[$v['package']]['dev_id'];
			$list[$k]['softid'] = $softinfo[$v['package']]['softid'];
			$list[$k]['softname'] = $v['softname'];
			$list[$k]['az_ad'] = $file_arr[$v['package']]['leafstatus'];
			$list[$k]['fileid'] = $file_arr[$v['package']]['id'];
			$list[$k]['is_anzhi_ad'] = $ad_library[$v['package']]['id'] ? 1 : 0 ;
			$list[$k]['az_office'] = $note_arr[$v['package']]['status'];
			$list[$k]['is_ignore'] = $caiji_model -> check_is_ignore('cj_soft_update',$v['package'],$v['version_code']);
			$dev_id[] = $softinfo[$v['package']]['dev_id'];
			//如果排期中有值就不做比较
			if(empty($whiteList[$v['package']])){		
				//盗版风险
				$list[$k]['Pirate'] = getPiracyWarning($v['softname'],$v['package'],$v['icon_md5']);	
			}			
		}	
		if($dev_id){	
			$where = array(
				'dev_id' => array('in',$dev_id),
			);	
			$developer = get_table_data($where,"pu_developer","dev_id","dev_name,dev_id,email");			
			$this->assign('developer', $developer);
		}
		
		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);	
		//下载量排序和时间排序
		if($_GET['order'] == 'a'){
			$order = 'd';
		}else if($_GET['order'] == 'd'){
			$order = 'a';
		}
		$this -> assign('orderby',$_GET['orderby']);			
		$this -> assign('order',$order);	
		unset($_GET['orderby'],$_GET['order']);
		$param = http_build_query($_GET);
		$this -> assign('param',$param);	
		//来源网站	
		$website_name = $caiji_model -> get_update_website_config('cj_update_website_config','website_name');
		$this -> assign('website_name',$website_name);
		//包名高亮库
		$pkg_g = get_table_data(array('type'=>5),"sj_sensitive","word","type,word");
		$this -> assign('pkg_g',$pkg_g);
		//签名
		$where = array(
			'package' => array('in',$pkg),
			'status' => 1,
		);
		$sign_arr = get_table_data($where,"sj_soft_sign","package","package,sign");	
		$this -> assign('sign_arr',$sign_arr);
		$this ->display();
	}
	//采集新增待审核
	function collection_add_audit(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => 1,
		);	
		isset($_GET['is_safe']) ? $_GET['is_safe'] : $_GET['is_safe']=1;
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');	
		$this->check_where($where, 'run_risk', 'isset');	
		$this->check_where($where, 'is_office', 'isset');	
		$this->check_range_where($where, 'begintime', 'endtime', 'create_time', true);	
		if($_GET['is_safe'] == 3){
			$this->assign('is_safe',$_GET['is_safe']);
		}else{
			$this->check_where($where, 'is_safe');//安全
		}
		$this->check_where($where, 'is_md5_same');//MD5一致
		$this->check_where($where, 'soft_type');//软件类型			
		//下载量
		$down_str = $_GET['down_str']*10000;
		$down_end = $_GET['down_end']*10000;
		if(strlen($_GET['down_str'])>0)
		{
			if(strlen($_GET['down_end'])>0)
			{
				$where['download_count']  = array('between',''.$down_str.','.$down_end);
			}else
			{
				$where['download_count']  = array('egt',$down_str);
			}
		}else if(strlen($_GET['down_end'])>0)
		{
			$where['download_count']  = array('elt',$down_end);
		}
		$this->assign('down_str',$_GET['down_str']);
		$this->assign('down_end',$_GET['down_end']);   
			
		//网站更新时间
		$website_update_time=strtotime($_GET['updatetime']);
		$website_updaate_to_time=strtotime($_GET['updatetotime'])+86399;
		if(!empty($_GET['updatetime']))
		{
			if(!empty($_GET['updatetotime']))
			{
				if($website_update_time>$website_updaate_to_time)
				{
					$this->error("开始时间不能大于结束时间！");
				}
				$where['website_update_time']  = array('between',''.$website_update_time.','.$website_updaate_to_time);
			}
			else
			{
				$where['website_update_time']  = array('egt',$website_update_time);
			}
		}
		elseif(!empty($_GET['updatetotime']))
		{
			$where['website_update_time']  = array('elt',$website_updaate_to_time);
		}
		$this->assign('updatetime',$_GET['updatetime']);
        $this->assign('updatetotime',$_GET['updatetotime']);
		
		//var_dump($_GET);exit;
                if(!isset($_GET['appfrom'])){
		    $where['appfrom'] = array('exp','!="taptap" and appfrom!="apk_pure"');
                }
		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_add');
		$pkg = array();
		foreach($list as $k => $v){
			$pkg[] = $v['package'];
		}
		//检测排期
		$soft_mdel = D('Dev.Softlist');
		$whiteList = $soft_mdel ->soft_WhiteList($pkg);		
		//整理数据	
		foreach($list as $k => $v){
			$list[$k]['pkg_str'] = str_replace('.','_',$v['package']);
			$list[$k]['create_time'] = $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']):'';
			$list[$k]['download_count'] = number_format($v['download_count']);
			
			//获取同一包名，同一版本号不同来源的apk_md5
			$current=$caiji_model -> get_md5($v['appfrom'],$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['current_md5']=$current['apk_md5'];
			
			$wandoujia=$caiji_model -> get_md5("豌豆荚",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['wandoujia_md5']=$wandoujia['apk_md5'];
			
			$md5_360=$caiji_model -> get_md5("360",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['360_md5']=$md5_360['apk_md5'];
			
			$baidu=$caiji_model -> get_md5("搜索失败",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['baidu_md5']=$baidu['apk_md5'];
			
			//如果360、搜索失败、豌豆荚都没有值，说明没有同包名的,如果当前是其中的来源，其他站点都为空说明没有同包名
			if($v['appfrom']=="360")
			{
				if($wandoujia['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			elseif($v['appfrom']=="豌豆荚")
			{
				if($md5_360['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			elseif($v['appfrom']=="搜索失败")
			{
				if($wandoujia['apk_md5']==null&&$md5_360['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			else
			{
				if($wandoujia['apk_md5']==null&&$md5_360['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			
			//判断最近更新时间是否有效
			$web_time=$v['website_update_time'] ? date('Y-m-d',$v['website_update_time']) : "";
			if($web_time=="1970-01-01")
			{
				$list[$k]['website_up_tm']="";
			}
			else
			{
				$list[$k]['website_up_tm']=$web_time;
			}
			//软件类型名称  1是应用  2是游戏 0无
			if($v['soft_type']==1)
			{
				$list[$k]['soft_type_name']="应用-";
			}
			elseif($v['soft_type']==2)
			{
				$list[$k]['soft_type_name']="游戏-";
			}
			else
			{
				$list[$k]['soft_type_name']="";
			}
			
			//如果排期中有值就不做比较
			if(empty($whiteList[$v['package']])){		
				//盗版风险
				$list[$k]['Pirate'] = getPiracyWarning($v['softname'],$v['package'],$v['icon_md5']);	
			}		
			$list[$k]['is_ignore'] = $caiji_model -> check_is_ignore('cj_soft_add',$v['package'],$v['version_code']);			
		}	
		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		//下载量排序和时间排序
		if($_GET['order'] == 'a'){
			$order = 'd';
		}else if($_GET['order'] == 'd'){
			$order = 'a';
		}
		$this -> assign('orderby',$_GET['orderby']);			
		$this -> assign('order',$order);	
		unset($_GET['orderby'],$_GET['order']);
		$param = http_build_query($_GET);
		$this -> assign('param',$param);	
		//来源网站	
		$website_name = $caiji_model -> get_update_website_config('cj_add_package_website_config','website_name');
		$this -> assign('website_name',$website_name);	
		//包名高亮库
		$pkg_g = get_table_data(array('type'=>5),"sj_sensitive","word","type,word");
		$this -> assign('pkg_g',$pkg_g);
		$this ->display();
	}
	//采集更新已入库
	function collection_update_storage(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => 2
		);
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');	
		$this->check_where($where, 'examine_type');	
		$this->check_where($where, 'az_category');
		$this->check_where($where, 'remark', 'isset', 'like');
                $this->check_range_where($where, 'begintime', 'endtime', 'review_time', true);
                $down_str = $_GET['down_str']*10000;
                $down_end = $_GET['down_end']*10000;
                if(strlen($_GET['down_str'])>0)
                {
                    if(strlen($_GET['down_end'])>0)
                    {
                        $where['az_downloaded']  = array('between',''.$down_str.','.$down_end);
                    }else
                    {
                        $where['az_downloaded']  = array('egt',$down_str);
                    }
                }else if(strlen($_GET['down_end'])>0)
                {
                    $where['az_downloaded']  = array('elt',$down_end);
                }
                $this->assign('down_str',$_GET['down_str']);
                $this->assign('down_end',$_GET['down_end']);                
		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_update');
		if($_GET['order'] == 'a'){
			$order = 'd';
		}else if($_GET['order'] == 'd'){
			$order = 'a';
		}
		$this -> assign('order',$order);

		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);	
		$param = http_build_query($_GET);
		$this -> assign('param',$param);
		//来源网站	
		$website_name = $caiji_model -> get_update_website_config('cj_update_website_config','website_name');
		$this -> assign('website_name',$website_name);		
		$this ->display();
	}
	//采集新增已入库
	function collection_add_storage(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => 2
		);	
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');	
		$this->check_where($where, 'examine_type');	
		$this->check_where($where, 'az_category');
		$this->check_where($where, 'remark', 'isset', 'like');
		$this->check_range_where($where, 'begintime', 'endtime', 'review_time', true);		
                $down_str = $_GET['down_str']*10000;
                $down_end = $_GET['down_end']*10000;
                if(strlen($_GET['down_str'])>0)
                {
                    if(strlen($_GET['down_end'])>0)
                    {
                        $where['download_count']  = array('between',''.$down_str.','.$down_end);
                    }else
                    {
                        $where['download_count']  = array('egt',$down_str);
                    }
                }else if(strlen($_GET['down_end'])>0)
                {
                    $where['download_count']  = array('elt',$down_end);
                }
                $this->assign('down_str',$_GET['down_str']);
                $this->assign('down_end',$_GET['down_end']);


		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_add');
        if($_GET['order'] == 'a'){
			$order = 'd';
		}else if($_GET['order'] == 'd'){
			$order = 'a';
		}
		$this -> assign('order',$order);

		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);	
		$param = http_build_query($_GET);
		$this -> assign('param',$param);	
		//来源网站	
		$website_name = $caiji_model -> get_update_website_config('cj_add_package_website_config','website_name');
		$this -> assign('website_name',$website_name);		
		$this ->display();
	}
	//采集更新已忽略
	function collection_update_ignored(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => 3
		);
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');	
		$this->check_where($where, 'az_category');
		$this->check_where($where, 'ignore_contents', 'isset', 'like');
		$this->check_range_where($where, 'begintime', 'endtime', 'ignore_tm', true);		

                $down_str = $_GET['down_str']*10000;
                $down_end = $_GET['down_end']*10000;
                if(strlen($_GET['down_str'])>0)
                {
                    if(strlen($_GET['down_end'])>0)
                    {
                        $where['az_downloaded']  = array('between',''.$down_str.','.$down_end);
                    }else
                    {
                        $where['az_downloaded']  = array('egt',$down_str);
                    }
                }else if(strlen($_GET['down_end'])>0)
                {
                    $where['az_downloaded']  = array('elt',$down_end);
                }
                $this->assign('down_str',$_GET['down_str']);
                $this->assign('down_end',$_GET['down_end']);

		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_update');
                foreach($list as $k=>$v)
                {
                    $list[$k]['az_downloaded'] = number_format($v['az_downloaded']);
                }
                $this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		$param = http_build_query($_GET);
		$this -> assign('param',$param);	
		//来源网站	
		$website_name = $caiji_model -> get_update_website_config('cj_update_website_config','website_name');
		$this -> assign('website_name',$website_name);		
		$this ->display();
	}	
	//采集新增已忽略
	function collection_add_ignored(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => 3
		);	
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');	
		$this->check_where($where, 'az_category');
		$this->check_where($where, 'ignore_contents', 'isset', 'like');
		$this->check_range_where($where, 'begintime', 'endtime', 'ignore_tm', true);		
                $down_str = $_GET['down_str']*10000;
                $down_end = $_GET['down_end']*10000;
                if(strlen($_GET['down_str'])>0)
                {
                    if(strlen($_GET['down_end'])>0)
                    {
                        $where['download_count']  = array('between',''.$down_str.','.$down_end);
                    }else
                    {
                        $where['download_count']  = array('egt',$down_str);
                    }
                }else if(strlen($_GET['down_end'])>0)
                {
                    $where['download_count']  = array('elt',$down_end);
                }
                $this->assign('down_str',$_GET['down_str']);
                $this->assign('down_end',$_GET['down_end']);
		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_add');

                foreach($list as $k=>$v)
                {
                    $list[$k]['download_count'] = number_format($v['download_count']);
                }

		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);	

		$param = http_build_query($_GET);
		$this -> assign('param',$param);	
		//来源网站	
		$website_name = $caiji_model -> get_update_website_config('cj_add_package_website_config','website_name');
		$this -> assign('website_name',$website_name);		
		$this ->display();
	}
	//忽略操作
	function update_ignored(){
		$caiji_model = D('Caiji.Caiji');
		if($_POST){
			$id = explode(',',$_POST['id']);
			$ret = $caiji_model -> update_ignored_do($id);
			if($ret == 1){
				foreach($id as $v){
					//操作日志
					if($_POST['type'] == 'add'){
						$str = '新增';
						$log_key = 'add';
						$log_key_2 = 'edit';
						$table='caiji.cj_soft_add';
					}else{
						$str = '更新';
						$log_key = 'update';
						$log_key_2 = 'edit';
						$table='caiji.cj_soft_update';
					}
					$log_result="log_result_".$v;
					$this->writelog("忽略了采集".$str."id为{$v}的软件",$table,$v,__ACTION__ ,$log_key,$log_key_2);
				}
				exit(json_encode(array('code'=>'1','msg'=>$id)));
			}else{
				exit(json_encode(array('code'=>'0','msg'=>'操作失败','id'=>$id)));
			}
		}else{
			list($softname,$package,$appfrom) = $caiji_model ->get_soft_info();
			$this -> assign('softname', implode(',',$softname));	
			$this -> assign('package', implode(',',$package));	
			$this -> assign('appfrom', $appfrom);
			$this -> assign('tmp_id', $_GET['id']);
			$this -> assign('type', $_GET['type']);
			//来源网站	
			if($_GET['type'] == 'add'){
				$table = 'cj_add_package_website_config';
			}else{
				$table = 'cj_update_website_config';
			}
			$website_name = $caiji_model -> get_update_website_config($table,'website_name');
			$this -> assign('website_name',$website_name);				
			//原因
			$coll_model= D('Caiji.Collection');
			$reason_list = $coll_model->get_reason();
			$this -> assign('reason_list', $reason_list);	
			$this ->display('ignored');
		}
	}	
	//山寨提醒
	function pub_soft_notice(){
		$result = array();
		if(!$_POST['data']){
			return false;
		}
		$model = new Model();
		foreach ($_POST['data'] as $key => $val) {
			list($counts,$softids,$tmp_ids) = get_copycat_num($val['softname'], $val['icon_md5'],$val['package']);
			if ($counts>=3) {
				if($softids){
					$softid_str = implode(',',$softids);					
				}
				if($tmp_ids){
					$tmpid_str = implode(',',$tmp_ids);
				}	
				$result[$key] = array($counts,$softid_str ? $softid_str : 0,$tmpid_str ? $tmpid_str :0 );
			}
		}
		exit(json_encode($result));
	}
	//获取软件多来源数据
	function pub_soft_from(){
		$caiji_model = D('Caiji.Caiji');
		$result = array();
		foreach ($_POST['data'] as $k => $v) {			
			$result[$k] = $caiji_model -> get_from_soft($v);
		}
		exit(json_encode($result));
	}	
	//导出
	function collection_export(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => $_GET['status']
		);	
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');	
		$this->check_where($where, 'az_category');
		$this->check_where($where, 'ignore_contents', 'isset', 'like');
		$this->check_where($where, 'remark', 'isset', 'like');
		$this->check_where($where, 'examine_type');
		if($_GET['status'] == 2){
			$field = 'review_time';
		}else{
			$field = 'ignore_tm';
		}
		$this->check_range_where($where, 'begintime', 'endtime', $field, true);
		$data = $caiji_model->get_exp($where);
		exit(json_encode($data));
	}

    //合作软件
    function cooperation()
    {
        $cj_model = D('Caiji.Caiji');
        $res = $cj_model->getcooplist($_GET);

        if(isset($_GET['down'])){
            $ua = $_SERVER["HTTP_USER_AGENT"]; 
            $filename = date('Y-m-d H-i');
            $filename = "采集后台_合作软件_".$filename.".csv";
            $res = $cj_model->getcooplist($_GET,2);
            $allist = $res['list'];
            header("Content-type:application/vnd.ms-excel");

            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }

            $desc ="软件名称,包名,软件分类,下载量,状态,安智版本,采集信息,采集时间,开发者,来源,添加时间\r\n";
            foreach($allist as $key=>$v)
            {
                $softname= $v['softname'];
                /*
                $softname= str_replace('?','',$v['softname']);//处理特殊字符 非空格
                $softname= str_replace('?','',$softname);
                $softname= str_replace('?','',$softname);
                $softname= str_replace('?','',$softname);
                 */
                if($v['status']==1){
                    $status = '最新';
                }
                if($v['status']==2){
                    $status = '审核中';
                }
                if($v['status']==3){
                    $status = '开发者未上传';
                }
                if(empty($v['fetch_tm']))
                {
                    $fetch_tm = '';
                }else
                {
                    $fetch_tm = date("Y-m-d H:i:s",$v['fetch_tm']);
                }

                $cj_info = str_replace('<br/>','',$v['cj_info']);


                $az_version = $v['az_version_code'].'('.$v['az_version_name'].')';
                $desc = $desc.$softname.','.$v['package'].','.$v['az_category'].',"'.number_format($v['az_downloaded']).'",'.$status.','.$az_version.','.$cj_info.','.$fetch_tm.','.$v['dev_name'].','.$v['sync_from'].','.date("Y-m-d H:i:s",$v['create_tm'])."\r";
            }
            //$desc = iconv('utf-8','gbk',$desc);
            echo chr(0xEF).chr(0xBB).chr(0xBF);
            echo $desc;
            exit(0);
        }
        $this->assign("get" , $_GET);

        $new_data = $res['list'];
        foreach($res['list'] as $k=>$v)
        {
            $new_data[$k]['az_downloaded'] = number_format($v['az_downloaded']);
        }

        $this->assign("list" , $new_data);
        $this->assign("page" , $res['page']);
        $this->assign("count" , $res['count']);
        $this->display();
    }


    //版本更新待采集
    function standbyfetch()
    {
        $cj_model = D('Caiji.Caiji');
        $res = $cj_model->get_standby_fetch($_GET);

        if(isset($_GET['down'])){
            $ua = $_SERVER["HTTP_USER_AGENT"]; 
            $filename = date('Y-m-d H-i');
            $filename = "采集后台_待采集_".$filename.".csv";
            $res = $cj_model->get_standby_fetch($_GET,2);
            $allist = $res['list'];
            header("Content-type:application/vnd.ms-excel");
            //header("content-Disposition:filename=采集后台_待采集_".time().".csv");
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }

            $desc ="软件名称,包名,软件分类,安智下载量,第三方下载量,审核流程,添加时间,状态,包名来源\r\n";
            foreach($allist as $v)
            {
                $softname= $v['softname'];
                /*
                $softname= str_replace('?','',$softname);
                $softname= str_replace('?','',$softname);
                $softname= str_replace('?','',$softname);
                */
                if($v['examine_type']==1){
                    $examine_type = '最新';
                }
                if($v['examine_type']==2){
                    $examine_type = '审核中';
                }
                if($v['examine_type']==3){
                    $examine_type = '开发者未上传';
                }

                if($v['status']==0){
                    $status= '已停用';
                }
                if($v['status']==1){
                    $status= '已启用';
                }

                if($v['package_from']==1){
                    $package_from = '市场扫描';
                }
                if($v['package_from']==2){
                    $package_from = '人工扫描';
                }

                $az_version = $v['az_version_code'].'('.$v['az_version_name'].')';
                $desc = $desc.'"'.$softname.'",'.$v['package'].','.$v['az_category'].',"'.number_format($v['az_downloaded']).'","'.number_format($v['download_count']).'",'.$examine_type.','.date("Y-m-d H:i:s",$v['create_tm']).','.$status.','.$package_from."\r";
            }
            echo chr(0xEF).chr(0xBB).chr(0xBF);
            echo $desc;
            exit(0);
        }

        $status= isset($_GET['status'])?$_GET['status']:-1;
        $dev_type = isset($_GET['dev_type'])?$_GET['dev_type']:-1;
        $this->assign("dev_type" , $dev_type);
        $this->assign("status" , $status);
        $this->assign("get" , $_GET);

        $new_data = $res['list'];
        foreach($res['list'] as $k=>$v)
        {
            $new_data[$k]['az_downloaded'] = number_format($v['az_downloaded']);
            $new_data[$k]['download_count'] = number_format($v['download_count']);
        }
        $this->assign("list" , $new_data);
        $this->assign("page" , $res['page']);
        $this->assign("count" , $res['count']);
        $this->display();
    }

    //版本更新待采集 编辑
    function edit_standby()
    {
        $cj_model = D('Caiji.Caiji');
        $id = $_GET['id'];
        $rs = $cj_model->get_standby_info($id);
		if($_GET['act']=='stop' || $_POST['act']=='stop'){
			if($this->isPost())
			{
				$model = new model();
				$data = array();
				$data['package']=$_POST['package'];
				$package = $_POST['package'];
				//属于合作软件中非闪屏通过的包名则不允许添加
				$rets = $cj_model->get_fromcoop($package);
				if($rets!=null){
					echo -3;exit(0);
				}

				//属于合作软件中非闪屏通过的包名则不允许添加
				$rets = $cj_model->get_cj_black_list($package);
				if($rets!=null){
					echo -3;exit(0);
				}

				$res = $model->table('sj_soft')->where("package = '$package' and status=1 and hide=1")->find();
				if($res==null){
					$res = $model->table('sj_soft_tmp')->where("package = '$package' and status=2")->find();
					if($res==null){
						echo -2;exit(0);
					}
				}

				$cid = str_replace(',','',$res['category_id']);
				$sql = "SELECT parentid FROM sj_category WHERE category_id = (SELECT parentid FROM sj_category WHERE category_id = $cid)";
				$ret = $model->query($sql);
				$cname = $ret[0]['parentid']==1?'应用':'游戏';

				$data['softname']=$_POST['softname'];
				$data['examine_type']=$_POST['examine'];
				$data['desc']=$_POST['desc'];
				$data['package_from']=2;
				$data['status']=$_POST['sid'];
				if($_POST['ptype']==1)
				{
					$log = $this->logcheck(array('id'=>$_POST['pid']),'cj_standby_fetch',$data,$cj_model);
					$this->writelog('采集-版本更新待采集,编辑了id为'.$_POST['pid'].',的数据'.$log,'caiji.cj_standby_fetch',$_POST['pid'],__ACTION__ ,"","edit");
				
				}else if($_POST['ptype']==2)
				{
					$this->writelog('采集-版本更新待采集,新增了包名为'.$_POST['package'].',软件名为'.$_POST['softname'].',审核流程类型为'.$_POST['examine'].',备注为'.$_POST['desc'].'的数据','caiji.cj_standby_fetch',$_POST['package'],__ACTION__ ,"","add");
				}
				
				$cj_model->updatestatus_standby($_POST['pid'],$_POST['sid']);
				
				$rs = $cj_model->savestandby($_POST,$cname,$res['total_downloaded']);//1
				
				echo $rs;exit(0);
			}
			$this->assign('type',$_GET['type']);
			$this->assign('info',$rs);
			$this->display('edit_remarks');
		}else{
			if($this->isPost())
			{
				$model = new model();
				$data = array();
				$data['package']=$_POST['package'];
				$package = $_POST['package'];
				//属于合作软件中非闪屏通过的包名则不允许添加
				$rets = $cj_model->get_fromcoop($package);
				if($rets!=null){
					echo -3;exit(0);
				}

				//属于合作软件中非闪屏通过的包名则不允许添加
				$rets = $cj_model->get_cj_black_list($package);
				if($rets!=null){
					echo -3;exit(0);
				}

				$res = $model->table('sj_soft')->where("package = '$package' and status=1 and hide=1")->find();
				if($res==null){
					$res = $model->table('sj_soft_tmp')->where("package = '$package' and status=2")->find();
					if($res==null){
						echo -2;exit(0);
					}
				}

				$cid = str_replace(',','',$res['category_id']);
				$sql = "SELECT parentid FROM sj_category WHERE category_id = (SELECT parentid FROM sj_category WHERE category_id = $cid)";
				$ret = $model->query($sql);
				$cname = $ret[0]['parentid']==1?'应用':'游戏';

				$data['softname']=$_POST['softname'];
				$data['examine_type']=$_POST['examine'];
				$data['desc']=$_POST['desc'];
				$data['package_from']=2;
				if($_POST['ptype']==1)
				{
					$log = $this->logcheck(array('id'=>$_POST['pid']),'cj_standby_fetch',$data,$cj_model);
					$this->writelog('采集-版本更新待采集,编辑了id为'.$_POST['pid'].',的数据'.$log,'caiji.cj_standby_fetch',$_POST['pid'],__ACTION__ ,"","edit");
				
				}else if($_POST['ptype']==2)
				{
					$this->writelog('采集-版本更新待采集,新增了包名为'.$_POST['package'].',软件名为'.$_POST['softname'].',审核流程类型为'.$_POST['examine'].',备注为'.$_POST['desc'].'的数据','caiji.cj_standby_fetch',$_POST['package'],__ACTION__ ,"","add");
				}
				$rs = $cj_model->savestandby($_POST,$cname,$res['total_downloaded']);

				echo $rs;exit(0);
			}
			$this->assign('type',$_GET['type']);
			$this->assign('info',$rs);
			$this->display('edit_standby');
		}
        
    }

    //批量添加
    function add_more_standby()
    {
        $cj_model = D('Caiji.Caiji');

        if($this->isPost())
        {
            $model = new model();
            $packages =trim($_POST['package']);
            $softnames =$_POST['softname'];
            $package_arr = explode(';',$packages);
            $softname_arr = explode(';',$softnames);
            foreach($package_arr as $k=>$v)
            {
                if(!empty($v))
                {
                    $package = $v;
                    $res = $model->table('sj_soft')->where("package = '$package' and status=1 and hide=1")->find();
                    if($res==null){
                        $res = $model->table('sj_soft_tmp')->where("package = '$package' and status=2")->find();
                    }

                    $cid = str_replace(',','',$res['category_id']);
                    $sql = "SELECT parentid FROM sj_category WHERE category_id = (SELECT parentid FROM sj_category WHERE category_id = $cid)";
                    $ret = $model->query($sql);
                    $cname = $ret[0]['parentid']==1?'应用':'游戏';

                    //supwater   软件名的问题  改成MODEL 里 每次都查?
                    $rs = $cj_model->savestandby($_POST,$cname,$res['total_downloaded'],$package);
                }
            }
            if(empty($_POST['desc']))
            {
                $desc = '';
            }else
            {
                $desc = ',备注为'.$_POST['desc'];
            }
            $this->writelog('采集-版本更新待采集,新增了包名为'.$_POST['package'].',软件名为'.$_POST['softname'].',审核流程类型为'.$_POST['examine'].$desc.'的数据','caiji.cj_standby_fetch',$_POST['package'],__ACTION__ ,"","add");
            return 1;
        }
        $this->display();
    }

    //根据包名获取软件名
    function checkpackage() {
        if($_POST){
            $model = new model();
            $package = $_POST['package'];//填入到软件名里
            $res = $model->table('sj_soft')->where("package = '$package' and status=1 and hide=1")->find();
            if($res==null){
                $ret = $model->table('sj_soft_tmp')->where("package = '$package' and status=2")->find();
                if($ret==null){
                    echo -2;exit(0);
                }else
                {
                    $this->ajaxReturn($ret,"获取成功！",1);
                }
            }else
            {
                $this->ajaxReturn($res,"获取成功！",1);
            }
        }
    }

    //根据包名获取软件名(批量)
    function checkmorepackage() {
        if($_POST){
            $cj_model = D('Caiji.Caiji');
            $model = new model();
            $packages = trim($_POST['package']);
            $package_arr = explode(';',$packages);
            $softnames = '';
            foreach($package_arr as $v)
            {
                if(!empty($v))
                {
                    $rets = $cj_model->get_standby_infobyrg($v);
                    if($rets!=null){
                        $this->ajaxReturn($v,"获取失败！",2);
                    }

                    $rets = $cj_model->get_cj_black_list($v);
                    if($rets!=null){
                        $this->ajaxReturn($v,"获取失败！",3);
                    }

                    $rets = $cj_model->get_fromcoop($v);
                    if($rets!=null){
                        $this->ajaxReturn($v,"获取失败！",4);
                    }

                    $res = $model->table('sj_soft')->where("package = '$v' and status=1 and hide=1")->find();
                    if($res==null){
                        $ret = $model->table('sj_soft_tmp')->where("package = '$v' and status=2")->find();
                        if($ret==null){
                            $this->ajaxReturn($v,"获取失败！",5);
                        }else
                        {
                            $softnames = empty($softnames)?$ret['softname']:$softnames.';'.$ret['softname'];
                        }
                    }else
                    {
                        $softnames = empty($softnames)?$res['softname']:$softnames.';'.$res['softname'];
                    }
                }
            }
            $this->ajaxReturn($softnames,"获取成功！",1);
        }
    }

    //启用停用待采集
    function update_status()
    {
        if($this->isAjax())
        {
            $cjmodel = D('Caiji.Caiji');
            $id = $_POST['h_id'];
            $status = $_POST['status_v'];
            $data = array();
            $data['status']= $status;
            $data['update_tm']=time();
            $log = $this->logcheck(array('id'=>$id),'cj_standby_fetch',$data,$cjmodel);
            $this->writelog('版本更新待采集,编辑了id为'.$_POST['h_id'].',的数据'.$log,'caiji.cj_standby_fetch',$_POST['package'],__ACTION__ ,"","edit");
            $rs = $cjmodel->updatestatus_standby($id,$status);
            if($rs>0){
                echo 1;exit(0);
            }
        }
    }

    //删除待采集数据
    function deletestandby()
    {
        $cjmodel = D('Caiji.Caiji');
        if($this->isAjax())
        {
            $id = $_POST['d_id'];
            $rs = $cjmodel->deletestandby($id);
            $this->writelog('版本更新待采集,删除了id为'.$_POST['d_id'].',的数据','caiji.cj_standby_fetch',$_POST['d_id'],__ACTION__ ,"","del");
            if($rs>0){
                echo 1;exit(0);
            }
        }
    }    

    //批量操作
    function all_update_status()
    {
        $cjmodel = D('Caiji.Caiji');
        if($this->isAjax())
        {
            $type = $_POST['type'];
            $str= $_POST['str'];
            $idarr = explode(',',$str);
            foreach($idarr as $v)
            {
                if(!empty($v))
                {
                    if($type==2)
                    {
                        $rs = $cjmodel->deletestandby($v);
                    
                    }else
                    {
                        $rs = $cjmodel->updatestatus_standby($v,$type);
                    }
                }
            }
            echo 1;exit(0);
        }
    }
	//采集更新入库
	function collection_update_pass(){
		$model = new Model();
		$caiji_model = D('Caiji.Caiji');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		$where = array(
			'id' => $_GET['id'],
			'status' => 1
		);
		$mark = $_GET['id'];
		$ret = $caiji_model -> table('cj_soft_update') -> where($where)->find();
		if($ret){
			$where = array(
				'package' => $ret['package'],
				'status' => 1,
				'hide' => array('exp',' !=0'),
			);
			$soft = $model -> table('sj_soft')->where($where)->order('softid desc')->find();
			if(!$soft){
				$caiji_model->save_cj_update($ret['package'],'该软件在历史数据中,自动忽略','cj_soft_update');	
				$this->error("该软件不在上架列表");
			}
			if($soft['hide']==1 && $soft['version_code'] >= $ret['version_code']){
				$caiji_model->save_cj_update($ret['package'],'该软件在软件列表已经存在,自动忽略','cj_soft_update');
				$this->error("该软件在软件列表已经存在");
			}elseif($soft['safe']>1){
				$caiji_model->save_cj_update($ret['package'],'该软件不安全自动忽略','cj_soft_update');
				$this->error("该软件不安全");
			}elseif($soft['hide']==3 && $soft['version_code'] >= $ret['version_code']){
				$caiji_model->save_cj_update($ret['package'],'该软件已下架自动忽略','cj_soft_update');
				$this->error("该软件已下架");
			}else{
				$black_list = $caiji_model->get_cj_black_list($ret['package']);
				if($black_list){
					$caiji_model->save_cj_update($ret['package'],'该软件在采集黑名单,自动忽略','cj_soft_update');
					$this->error("该软件已在采集黑名单中");
				}
				unset($_SESSION['apk_info'.$mark],$_SESSION['apk_form'.$mark]);
				$_SESSION['apk_info'.$mark] = $soft;
				$_SESSION['apk_info'.$mark]['_iconurl'] = CAIJI_ATTACHMENT_HOST . $ret['icon_path'];
				$_SESSION['apk_info'.$mark]['versionCode'] = $ret['version_code'];
				$_SESSION['apk_info'.$mark]['versionName'] = $ret['version_name'];
				$_SESSION['apk_info'.$mark]['filesize2'] = $ret['apk_size'];
				$_SESSION['apk_info'.$mark]['packagename'] = $ret['package'];
				$_SESSION['apk_form'.$mark]['language'] = $soft['language'];
				$_SESSION['apk_form'.$mark]['a_language'] = $ret['language'];
				$_SESSION['apk_form'.$mark]['softname'] = $soft['softname'];
				$_SESSION['apk_form'.$mark]['dev_name'] = $soft['dev_name'] ? $soft['dev_name'] : '来自互联网';
				$_SESSION['apk_form'.$mark]['update_content'] = $soft['update_content'];
				$_SESSION['apk_form'.$mark]['intro'] = $soft['intro'];
				$_SESSION['apk_form'.$mark]['tags_split'] = explode(',', $ret['keyword']);
				$apkmodel = D("Dev.Apk");
				//获取分类信息
				$res = $apkmodel -> getCategory();
				if($res!='ok') {
					$this->assign('jumpUrl',$referer);
					$this->error($res);
				}
				//分类信息处理
				$_SESSION['apk_form'.$mark]['soft_type'] = $apkmodel -> getTopCategory($soft['category_id']);
				$_SESSION['apk_form'.$mark]['category_topid'] = '-1';
				$category_id = intval(substr($soft['category_id'],1));
				foreach(array_keys($_SESSION['category_arr']) as $val) {
					if(in_array($category_id,$_SESSION['category_arr'][$val])) {
						$_SESSION['apk_form'.$mark]['category_topid'] = $val;
						break;
					}
				}
				$_SESSION['apk_form'.$mark]['category_id'] = $soft['category_id'];
				unset($category_id);
				//截图
				$thumb = $model -> table('sj_soft_thumb')->where(array('softid'=>$soft['softid'], 'status'=>'1'))->order('rank asc,id asc')->field('rank,url')->select();
				foreach($thumb as $key=>$val) {
					$key = $key+1;
					if($val['url']){
						$_SESSION['apk_form'.$mark]['thumb']['thumb_'.$key]['url'] = IMGATT_HOST . $val['url'];
						$_SESSION['apk_info'.$mark]['thumb']['thumb_'.$key]['url'] = $val['url'];
					}	
				}
				unset($thumb);	
				$_SESSION['apk_form'.$mark]['thumb'] ? $_SESSION['apk_form'.$mark]['thumb_js'] = json_encode(array_keys($_SESSION['apk_form'.$mark]['thumb'])) : '';
				//icon
				$new_icon =  $model -> table('sj_icon')->where(array('softid'=>$soft['softid'], 'status'=>'1'))->field('apk_icon')->find();	
				$_SESSION['apk_form'.$mark]['new_icon'] = $new_icon['apk_icon'] ? IMGATT_HOST . $new_icon['apk_icon'] : '';
				$_SESSION['apk_form'.$mark]['new_apk_icon'] = $new_icon['apk_icon'];
				$this->assign('cj_list',$ret);
				$this->assign('from_type','cj_update');
				$_GET['type'] = 'update';
				$_GET['softid'] = $soft['softid'];
				$_SESSION['apk_form'.$mark]['record_type'] = 3;
				$_SESSION['apk_info'.$mark]['record_type'] = 3;
				$_SESSION['apk_form'.$mark]['get_type'] = 'update';
				$_SESSION['apk_info'.$mark]['get_type'] = 'update';
				//$_SESSION['apk_form'.$mark]['auth'] = 1;
				$_SESSION['apk_info'.$mark]['cj_data'] = $ret;
				$_SESSION['apk_form'.$mark]['update_content'] = $ret['update_log'];
				//获取标签
				$Tagsmodel = D('Sj.Tags');
				$tag_list = $Tagsmodel -> get_tag($ret['package']);
				$this->assign('mark',$mark);
				$is_new = $Tagsmodel -> getTagidbyname($tag_list[1]);
				$this->assign('is_new',$is_new);			
				$this->assign('tag_list',$tag_list);	
				//签名
				$sign_arr =  $model -> table('sj_soft_sign')->where(array('package'=>$ret['package'], 'status'=>'1'))->field('sign')->find();	
				$this->assign('official_sign',$sign_arr['sign']);				
				$this->assign('apk_sign',$ret['sign']);				
				$this->display('Dev:Apk:add_new_confirm'); 	
			}
		}else{
			$this->error("该软件不在审核中");
		}
	}
	//采集新增入库
	function collection_add_pass(){
		$model = new Model();
		$caiji_model = D('Caiji.Caiji');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		$where = array(
			'id' => $_GET['id'],
			'status' => 1
		);
		$mark = $_GET['id'];
		$ret = $caiji_model -> table('cj_soft_add') -> where($where)->find();
		if($ret){
			$where = array(
				'package' => $ret['package'],
				'status' => 1,
				'hide' => 1,
			);
			$soft = $model -> table('sj_soft')->where($where)->find();
			$where = array(
				'package' => $ret['package'],
				'status' => 2,
			);
			$soft_tmp = $model -> table('sj_soft_tmp')->where($where)->find();
			if($soft || $soft_tmp){
				$caiji_model->save_cj_update($ret['package'],'该软件在软件列表已经存在,自动忽略','cj_soft_add');
				$this->error("该软件在软件列表已经存在");
			}else{
				$black_list = $caiji_model->get_cj_black_list($ret['package']);
				if($black_list){
					$caiji_model->save_cj_update($ret['package'],'该软件在软件在采集黑名单,自动忽略','cj_soft_add');
					$this->error("该软件已在采集黑名单中");
				}
				unset($_SESSION['apk_info'.$mark],$_SESSION['apk_form'.$mark]);
				$_SESSION['apk_info'.$mark]['_iconurl'] = CAIJI_ATTACHMENT_HOST . $ret['icon_path'];
				$_SESSION['apk_info'.$mark]['versionCode'] = $ret['version_code'];
				$_SESSION['apk_info'.$mark]['versionName'] = $ret['version_name'];
				$_SESSION['apk_info'.$mark]['filesize2'] = $ret['apk_size'];
				$_SESSION['apk_info'.$mark]['packagename'] = $ret['package'];
				$_SESSION['apk_info'.$mark]['package'] = $ret['package'];
				$_SESSION['apk_form'.$mark]['softname'] = $ret['softname'];
				$_SESSION['apk_form'.$mark]['dev_name'] = $ret['author'] ? $ret['author'] : '来自互联网';
				$_SESSION['apk_form'.$mark]['intro'] = $ret['description'];
				$_SESSION['apk_form'.$mark]['tags_split'] = explode(',', $ret['keyword']);
				$_SESSION['apk_form'.$mark]['new_icon'] =  $ret['icon_path'];
				$_SESSION['apk_form'.$mark]['new_apk_icon'] = $ret['icon_path'];
				$apkmodel = D("Dev.Apk");
				//获取分类信息
				$res = $apkmodel -> getCategory();
				if($res!='ok') {
					$this->assign('jumpUrl',$referer);
					$this->error($res);
				}
				$this->assign('cj_list',$ret);
				$this->assign('from_type','cj_add');
				$_GET['type'] = 'new';
				$_SESSION['apk_form'.$mark]['get_type'] = 'new';
				$_SESSION['apk_info'.$mark]['get_type'] = 'new';				
				$_SESSION['apk_form'.$mark]['record_type'] = 1;
				$_SESSION['apk_info'.$mark]['record_type'] = 1;
				//$_SESSION['apk_form'.$mark]['auth'] = 1;
				$_SESSION['apk_info'.$mark]['cj_data'] = $ret;
				//获取标签
				$Tagsmodel = D('Sj.Tags');
				$tag_list = $Tagsmodel -> get_tag($ret['package']);
				$this->assign('mark',$mark);
				$is_new = $Tagsmodel -> getTagidbyname($tag_list[1]);
				$this->assign('is_new',$is_new);					
				$this->assign('tag_list',$tag_list);	
				//截图
				$thumb_list = $caiji_model -> get_cj_thumb($ret['id']);
				foreach($thumb_list as $k => $v){
					$i = $v['rank']+1;
					$_SESSION['apk_form'.$mark]['thumb']['thumb_'.$i]['url'] = CAIJI_ATTACHMENT_HOST . $v['screenshot_path'];
					$thumb_arr['thumb_'.$i] = CAIJI_UPLOAD_PATH .$v['screenshot_path'];
				}
				unset($thumb_list);
				$_SESSION['apk_info'.$mark]['cj_data']['cj_thumb'] = $thumb_arr;
				$_SESSION['apk_form'.$mark]['thumb'] ? $_SESSION['apk_form'.$mark]['thumb_js'] = json_encode(array_keys($_SESSION['apk_form'.$mark]['thumb'])) : '';
				$this->assign('from',$_GET['from']);
				$this->display('Dev:Apk:add_new_confirm');
			}
		}else{
			$this->error("当前记录已失效");
		}
	}
	//过滤上架列表已存在的软件
	function pub_filter_soft(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'package' => array('in',$_POST['data']),
			'status' => 1,
			'hide' =>1
		);
		$soft_list = get_table_data($where,"sj_soft","package","package,version_code");
		$where = array(
			'package' => array('in',$_POST['data']),
			'status' => 2,
		);
		$soft_tmp = get_table_data($where,"sj_soft_tmp","package","package,version_code");
		$where = array(
			'package' => array('in',$_POST['data']),
			'status' => 1,
		);
		if($_POST['type'] == 'update'){
			$table = 'cj_soft_update';
		}else{
			$table = 'cj_soft_add';
		}
		$cj_list = $caiji_model -> table($table)->where($where)->field('id,package,version_code')->select();
		$id = array();
		foreach($cj_list as $v){
			if($soft_list[$v['package']]['version_code'] >= $v['version_code'] || $soft_tmp[$v['package']]['version_code'] >= $v['version_code']){
				$id[] = $v['id'];
			}
		}
		if($id){
			$where = array(
				'id' => array('in',$id),
				'status' => 1,
			);
			$map = array(
				'status' => 3,
				'ignore_tm' => time(),
				'ignore_contents' => '该软件在软件列表已经存在,自动忽略',
			);
			$res = $caiji_model -> table($table)->where($where)->save($map);
		}
		if($res){
			exit(json_encode(array('code'=>1,'msg'=>$id)));
		}else{
			exit(json_encode(array('code'=>0)));
		}
    }

    //全局搜索
    function globalsearch()
    {
        $datay = array();
        $cj_model = D('Caiji.Caiji');
        $package = $_GET['package'];
        $softname= $_GET['softname'];

        //如果有包名 不用管是否有软件名 则直接包名查询, 如果只有软件名 则软件名like
        //$package = 'cn.safetrip.edog';

        if(!empty($package))
        {
            $packages[] = $package;
        }else if(!empty($softname))
        {
            $packages = $cj_model->globalsearchlike($softname);
        }

        $model = M();
        foreach($packages as $k=>$v)
        {
            $res = $cj_model->globalsearch($v);
            foreach($res as $kk=>$vv)
            {
                //$kk = "com.xiaomi.xshare";
                $rs_soft = $model->table('sj_soft')->field('version,version_code,category_id')->where('package="'.$kk.'" and status=1 and hide=1')->order('version_code desc')->find();
                $res[$kk]['az_version'] = $rs_soft['version'];
                $res[$kk]['az_version_code'] = $rs_soft['version_code'];
                if(!empty($rs_soft['category_id']))
                {
                    $res[$kk]['categoryname'] = $this->getcategoryname($rs_soft['category_id']);
                }
            }

            if(!empty($res)){
               $data[$v] = $res[$v];
            }
        }

        import("@.ORG.Page");
        $page = new Page(count($data), 10);
        $page->setConfig('header','条记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();            

        $rs = array_slice($data,$page->firstRow,$page->listRows);
        $this->assign("get" , $_GET);
        $this->assign("list" , $rs);
        $this->assign("page" , $show);
        $this->display();
    }

    //根据分类ID获取 所属顶级分类
    function getcategoryname($cid)
    {
        $model=M();
        $cid = str_replace(',','',$cid);
        $sql = "SELECT parentid FROM sj_category WHERE category_id = (SELECT parentid FROM sj_category WHERE category_id = $cid)";
        $ret = $model->query($sql);
        return $ret[0]['parentid']==1?'应用':'游戏';
    }
	//批量下载apk
	function batch_download_pkg(){
		$cj_model = D('Caiji.Caiji');
		$id_arr = explode(',',$_POST['id']);
		if($_POST['type'] == 'update'){
			$table = 'cj_soft_update';
		}else{
			$table = 'cj_soft_add';
		}
		$where = array(
			'id' => array('in',$id_arr)
		);
		$cj_list = $cj_model -> table($table)->where($where)->field('apk_path')->select();
		$file_arr = array();
		foreach($cj_list as $v){
			if(!file_exists(CAIJI_UPLOAD_PATH .$v['apk_path'])) continue;
			$file_arr[] = CAIJI_ATTACHMENT_HOST . $v['apk_path'];
		}
		exit(json_encode($file_arr));
	}

	//采集待审核taptap
	function collection_add_audit_taptap(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => 1,
		);
		isset($_GET['is_safe']) ? $_GET['is_safe'] : $_GET['is_safe']=3;
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');
		$this->check_where($where, 'run_risk', 'isset');
		$this->check_where($where, 'is_office', 'isset');
		$this->check_range_where($where, 'begintime', 'endtime', 'create_time', true);
		if($_GET['is_safe'] == 3){
			$this->assign('is_safe',$_GET['is_safe']);
		}else{
			$this->check_where($where, 'is_safe');//安全
		}
		$this->check_where($where, 'is_md5_same');//MD5一致
		$this->check_where($where, 'soft_type');//软件类型
		//下载量
		$down_str = $_GET['down_str']*10000;
		$down_end = $_GET['down_end']*10000;
		if(strlen($_GET['down_str'])>0)
		{
			if(strlen($_GET['down_end'])>0)
			{
				$where['download_count']  = array('between',''.$down_str.','.$down_end);
			}else
			{
				$where['download_count']  = array('egt',$down_str);
			}
		}else if(strlen($_GET['down_end'])>0)
		{
			$where['download_count']  = array('elt',$down_end);
		}
		$this->assign('down_str',$_GET['down_str']);
		$this->assign('down_end',$_GET['down_end']);

		//网站更新时间
		$website_update_time=strtotime($_GET['updatetime']);
		$website_updaate_to_time=strtotime($_GET['updatetotime'])+86399;
		if(!empty($_GET['updatetime']))
		{
			if(!empty($_GET['updatetotime']))
			{
				if($website_update_time>$website_updaate_to_time)
				{
					$this->error("开始时间不能大于结束时间！");
				}
				$where['website_update_time']  = array('between',''.$website_update_time.','.$website_updaate_to_time);
			}
			else
			{
				$where['website_update_time']  = array('egt',$website_update_time);
			}
		}
		elseif(!empty($_GET['updatetotime']))
		{
			$where['website_update_time']  = array('elt',$website_updaate_to_time);
		}
		$this->assign('updatetime',$_GET['updatetime']);
		$this->assign('updatetotime',$_GET['updatetotime']);

		//var_dump($_GET);exit;
		$where['appfrom'] = 'taptap';
		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_add');
		$pkg = array();
		foreach($list as $k => $v){
			$pkg[] = $v['package'];
		}
		//检测排期
		$soft_mdel = D('Dev.Softlist');
		$whiteList = $soft_mdel ->soft_WhiteList($pkg);
		//整理数据
		foreach($list as $k => $v){
			$list[$k]['pkg_str'] = str_replace('.','_',$v['package']);
			$list[$k]['create_time'] = $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']):'';
			$list[$k]['download_count'] = number_format($v['download_count']);

			//获取同一包名，同一版本号不同来源的apk_md5
			$current=$caiji_model -> get_md5($v['appfrom'],$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['current_md5']=$current['apk_md5'];

			$wandoujia=$caiji_model -> get_md5("豌豆荚",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['wandoujia_md5']=$wandoujia['apk_md5'];

			$md5_360=$caiji_model -> get_md5("360",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['360_md5']=$md5_360['apk_md5'];

			$baidu=$caiji_model -> get_md5("搜索失败",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['baidu_md5']=$baidu['apk_md5'];

			//如果360、搜索失败、豌豆荚都没有值，说明没有同包名的,如果当前是其中的来源，其他站点都为空说明没有同包名
			if($v['appfrom']=="360")
			{
				if($wandoujia['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			elseif($v['appfrom']=="豌豆荚")
			{
				if($md5_360['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			elseif($v['appfrom']=="搜索失败")
			{
				if($wandoujia['apk_md5']==null&&$md5_360['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			else
			{
				if($wandoujia['apk_md5']==null&&$md5_360['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}

			//判断最近更新时间是否有效
			$web_time=$v['website_update_time'] ? date('Y-m-d',$v['website_update_time']) : "";
			if($web_time=="1970-01-01")
			{
				$list[$k]['website_up_tm']="";
			}
			else
			{
				$list[$k]['website_up_tm']=$web_time;
			}
			//软件类型名称  1是应用  2是游戏 0无
			if($v['soft_type']==1)
			{
				$list[$k]['soft_type_name']="应用-";
			}
			elseif($v['soft_type']==2)
			{
				$list[$k]['soft_type_name']="游戏-";
			}
			else
			{
				$list[$k]['soft_type_name']="";
			}

			//如果排期中有值就不做比较
			if(empty($whiteList[$v['package']])){
				//盗版风险
				$list[$k]['Pirate'] = getPiracyWarning($v['softname'],$v['package'],$v['icon_md5']);
			}
			$list[$k]['is_ignore'] = $caiji_model -> check_is_ignore('cj_soft_add',$v['package'],$v['version_code']);
		}
		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		//下载量排序和时间排序
		if($_GET['order'] == 'a'){
			$order = 'd';
		}else if($_GET['order'] == 'd'){
			$order = 'a';
		}
		$this -> assign('orderby',$_GET['orderby']);
		$this -> assign('order',$order);
		unset($_GET['orderby'],$_GET['order']);
		$param = http_build_query($_GET);
		$this -> assign('param',$param);
		//来源网站
		$website_name = $caiji_model -> get_update_website_config('cj_add_package_website_config','website_name');
		$this -> assign('website_name',$website_name);
		//包名高亮库
		$pkg_g = get_table_data(array('type'=>5),"sj_sensitive","word","type,word");
		$this -> assign('pkg_g',$pkg_g);
		$this ->display();
	}

	//采集待审核apkpure
	function collection_add_audit_apkpure(){
		$caiji_model = D('Caiji.Caiji');
		$where = array(
			'status' => 1,
		);
		isset($_GET['is_safe']) ? $_GET['is_safe'] : $_GET['is_safe']=3;
		$this->check_where($where, 'softname', 'isset', 'like');
		$this->check_where($where, 'package');
		$this->check_where($where, 'appfrom');
		$this->check_where($where, 'run_risk', 'isset');
		$this->check_where($where, 'is_office', 'isset');
		$this->check_range_where($where, 'begintime', 'endtime', 'create_time', true);
		if($_GET['is_safe'] == 3){
			$this->assign('is_safe',$_GET['is_safe']);
		}else{
			$this->check_where($where, 'is_safe');//安全
		}
		$this->check_where($where, 'is_md5_same');//MD5一致
		$this->check_where($where, 'soft_type');//软件类型
		//下载量
		$down_str = $_GET['down_str']*10000;
		$down_end = $_GET['down_end']*10000;
		if(strlen($_GET['down_str'])>0)
		{
			if(strlen($_GET['down_end'])>0)
			{
				$where['download_count']  = array('between',''.$down_str.','.$down_end);
			}else
			{
				$where['download_count']  = array('egt',$down_str);
			}
		}else if(strlen($_GET['down_end'])>0)
		{
			$where['download_count']  = array('elt',$down_end);
		}
		$this->assign('down_str',$_GET['down_str']);
		$this->assign('down_end',$_GET['down_end']);

		//网站更新时间
		$website_update_time=strtotime($_GET['updatetime']);
		$website_updaate_to_time=strtotime($_GET['updatetotime'])+86399;
		if(!empty($_GET['updatetime']))
		{
			if(!empty($_GET['updatetotime']))
			{
				if($website_update_time>$website_updaate_to_time)
				{
					$this->error("开始时间不能大于结束时间！");
				}
				$where['website_update_time']  = array('between',''.$website_update_time.','.$website_updaate_to_time);
			}
			else
			{
				$where['website_update_time']  = array('egt',$website_update_time);
			}
		}
		elseif(!empty($_GET['updatetotime']))
		{
			$where['website_update_time']  = array('elt',$website_updaate_to_time);
		}
		$this->assign('updatetime',$_GET['updatetime']);
		$this->assign('updatetotime',$_GET['updatetotime']);

		//var_dump($_GET);exit;
		$where['appfrom'] = 'apk_pure';
		list($list,$total, $page) = $caiji_model -> get_data($where,$_GET,'cj_soft_add');
		$pkg = array();
		foreach($list as $k => $v){
			$pkg[] = $v['package'];
		}
		//检测排期
		$soft_mdel = D('Dev.Softlist');
		$whiteList = $soft_mdel ->soft_WhiteList($pkg);
		//整理数据
		foreach($list as $k => $v){
			$list[$k]['pkg_str'] = str_replace('.','_',$v['package']);
			$list[$k]['create_time'] = $v['create_time'] ? date('Y-m-d H:i:s',$v['create_time']):'';
			$list[$k]['download_count'] = number_format($v['download_count']);

			//获取同一包名，同一版本号不同来源的apk_md5
			$current=$caiji_model -> get_md5($v['appfrom'],$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['current_md5']=$current['apk_md5'];

			$wandoujia=$caiji_model -> get_md5("豌豆荚",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['wandoujia_md5']=$wandoujia['apk_md5'];

			$md5_360=$caiji_model -> get_md5("360",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['360_md5']=$md5_360['apk_md5'];

			$baidu=$caiji_model -> get_md5("搜索失败",$v['package'],$v['version_code'],'cj_soft_add');
			$list[$k]['baidu_md5']=$baidu['apk_md5'];

			//如果360、搜索失败、豌豆荚都没有值，说明没有同包名的,如果当前是其中的来源，其他站点都为空说明没有同包名
			if($v['appfrom']=="360")
			{
				if($wandoujia['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			elseif($v['appfrom']=="豌豆荚")
			{
				if($md5_360['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			elseif($v['appfrom']=="搜索失败")
			{
				if($wandoujia['apk_md5']==null&&$md5_360['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}
			else
			{
				if($wandoujia['apk_md5']==null&&$md5_360['apk_md5']==null&&$baidu['apk_md5']==null)
				{
					$list[$k]['no_package']=1;
				}
				else
				{
					$list[$k]['no_package']="";
				}
			}

			//判断最近更新时间是否有效
			$web_time=$v['website_update_time'] ? date('Y-m-d',$v['website_update_time']) : "";
			if($web_time=="1970-01-01")
			{
				$list[$k]['website_up_tm']="";
			}
			else
			{
				$list[$k]['website_up_tm']=$web_time;
			}
			//软件类型名称  1是应用  2是游戏 0无
			if($v['soft_type']==1)
			{
				$list[$k]['soft_type_name']="应用-";
			}
			elseif($v['soft_type']==2)
			{
				$list[$k]['soft_type_name']="游戏-";
			}
			else
			{
				$list[$k]['soft_type_name']="";
			}

			//如果排期中有值就不做比较
			if(empty($whiteList[$v['package']])){
				//盗版风险
				$list[$k]['Pirate'] = getPiracyWarning($v['softname'],$v['package'],$v['icon_md5']);
			}
			$list[$k]['is_ignore'] = $caiji_model -> check_is_ignore('cj_soft_add',$v['package'],$v['version_code']);
		}
		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);
		//下载量排序和时间排序
		if($_GET['order'] == 'a'){
			$order = 'd';
		}else if($_GET['order'] == 'd'){
			$order = 'a';
		}
		$this -> assign('orderby',$_GET['orderby']);
		$this -> assign('order',$order);
		unset($_GET['orderby'],$_GET['order']);
		$param = http_build_query($_GET);
		$this -> assign('param',$param);
		//来源网站
		$website_name = $caiji_model -> get_update_website_config('cj_add_package_website_config','website_name');
		$this -> assign('website_name',$website_name);
		//包名高亮库
		$pkg_g = get_table_data(array('type'=>5),"sj_sensitive","word","type,word");
		$this -> assign('pkg_g',$pkg_g);
		$this ->display();
	}


}
?>
