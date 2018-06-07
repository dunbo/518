<?php
	class DeskdownsetAction extends CommonAction {
		//桌面预下载配置列表展示
		function downset_list() {
			$model = M();
            $where = array();
            // 是已过期列表还是未过期列表
            $now = time();
            $overdue = $_GET['overdue'];
            if (!$overdue || $overdue != 1) {
                // 默认是未过期
                $overdue = -1;
            }
            if ($overdue == 1) {
                // 如果是已过期列表，判断搜索的结束时间是否比当前要大
                if ($where['end_time']) {
                    $where['end_time'][1] .= " and end_time<{$now}";
                } else {
                    $where['end_time'] = array('exp', "<{$now}");
                }
                $order = "start_time asc";
            } else if ($overdue == -1) {
                // 如果是未过期列表，判断搜索的结束时间是否比当前要大
                if ($where['end_time']) {
                    $where['end_time'][1].= " and end_time>{$now}";
                } else {
                    $where['end_time'] = array('exp', ">{$now}");
                }
                $order = "start_time asc";
            }
            $where['status'] = 1;
            // 分页
            import("@.ORG.Page");
            $limit = 10;
            $count = $model->table('sj_desk_icon')->where($where)->count();
            $page  = new Page($count, $limit);
            // 当前页数据
			$list = $model->table('sj_desk_icon')->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
			//遍历获得预下载软件名称和指定下载软件名称
			foreach ($list as $key => $v) {
				if($v['package']){
						$softname_result = $model->table('sj_soft')->where(array('package' => $v['package'], 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
						$list[$key]['package_name']=$softname_result[0]['softname'];
				}

				if($v['package_appoint']){
						$softname_result = $model->table('sj_soft')->where(array('package' => $v['package_appoint'], 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
						$list[$key]['package_appoint_name']=$softname_result[0]['softname'];
				}
			}
			$this->assign('list', $list);
			// $this->assign('category_list', $category_list);
            $this->assign('overdue', $overdue);
            $this->assign('now', $now);
            $this->assign("page", $page->show());
			$this->display();
		}
		//桌面预下载配置添加表单展示
		function add_downset_show(){
			$this->display();
		}
		//桌面预下载配置添加表单数据处理和添加
		function add_downset() {
            $model = M();
            $map = array();
            //判断预下载软件包名是否存在
        	$package = trim($_POST['package']);
	        $softname_result = $model->table('sj_soft')->where(array('package' => $package, 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
            if(!$softname_result){
            	$this->error("该预下载软件软件不存在");
			}
             // 开始时间和结束时间
            $start_time = strtotime($_POST['start_time']);
            $end_time = strtotime($_POST['end_time']);
            if (!$start_time) {
                $this->error("开始时间不能为空");
            }
            if (!$end_time) {
                $this->error("结束时间不能为空");
            }
            if ($start_time > $end_time) {
                $this->error("开始时间不能大于结束时间");
            }
            $map['start_time'] = $start_time;
            $map['end_time'] = $end_time;
            $map['package'] = $package;
            $map['del_condition'] = $_POST['del_condition'];
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            //判断更新指定软件的包名是否存在
            $package_appoint = trim($_POST['package_appoint']);
            if($_POST['trigger_condition']==1){
	            if(!empty($package_appoint)){
	            	$softname_result = $model->table('sj_soft')->where(array('package' => $package_appoint, 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
	            	if(!$softname_result){
	            		$this->error("该更新指定软件包不存在");
	           		}else{
	           			$map['package_appoint'] =$package_appoint;
	           			if($map['package']==$map['package_appoint']){
	           				$this->error("预下载软件包名和更新指定软件包名不能相同");
	           			}
	           			if($this->check_pkg_appoint($map)!=2){
	           				$this->error("相同时间段，指定软件不可重复。");
	           			}
	           		}
		         }else{
		         		$this->error("更新指定软件时软件包名不存在");
		            	// $map['package_appoint'] = "";
		         }
            }
            

            // 更新全部软件的数量
            if($_POST['trigger_condition']==0){
            	if(!empty($_POST['upd_count'])){
            	$upd_count = intval($_POST['upd_count']);
            	if(strlen($upd_count)!=strlen($_POST['upd_count'])){
            		$this->error("软件更新数量必须为整数");
            	}
	            if (!is_int($upd_count)) {
	                $this->error("软件更新数量必须为整数");
	            }
	            if ($_POST['upd_count']<=0) {
                $this->error("软件更新数量不为零");
            	}
            	$map['upd_count'] = $upd_count;
           	 }
            }
            
         	
            $map['trigger_condition']=trim($_POST['trigger_condition']);
			//V6.0精准投放
			//处理上传csv
			$filename=$_FILES['upload_file']['name'];
			if(!$filename&&!$_POST['csv_count'])
			{
				$map['csv_count'] = 0;
				$map['csv_url'] = "";
				$map['is_upload_csv'] = 0; //标注是否上传csv
			}
			if($filename&&!$_POST['csv_count'])
			{
				$this -> error("选择好的文件请点击上传才有效");
			}
			if($_POST['csv_count']&&$_POST['csv_url'])
			{
				$map['csv_count'] = $_POST['csv_count'];
				$map['csv_url'] = $_POST['csv_url'];
				$map['is_upload_csv'] = 1;
			}
			unset($_FILES['upload_file']);
			//渠道id和机型id
			$channel_id_array=$_POST['cid'];
			$cids = array_unique($channel_id_array);
            if (count($cids) > 0) {
                $s = implode(',', $cids);
                $s = ",{$s},";
                $map['cid'] = $s;
            }
			else
			{
				$map['cid'] = ",,";
			}

			$device_did_array=$_POST['did'];
			$dids = array_unique($device_did_array);
            if (count($dids) > 0) 
			{
				$d= implode(',', $dids);
				$d = ",{$d},";
				$map['device_did'] = $d;
			}
			else
			{
				$map['device_did'] = ",,";
			}
			//运营商和固件版本和市场版本
			$map['oid'] = ','. implode(',', $_POST['oid']). ',';
			$map['firmware'] = ','. implode(',', $_POST['firmware']). ',';
			$map['version_code'] = ','. implode(',', $_POST['version_code']). ',';
			// 更新全部软件的数量
            if($_POST['trigger_condition']==0){
	            //检查冲突
	            $conflict_ids = $this->check_pkg_appoint($map,1);
	            if ($conflict_ids!=2) {
	            	$str="";
	            	foreach($conflict_ids as $v){
	            		$str.=$v.',';
	            	}
	                $this->error("相同开始结束时间段，面向全部渠道的更新全部软件只能配置一条，与id为".$str."冲突");
	            }
        	}
        	if($_POST['trigger_condition']==1){
	            //检查冲突
	            $conflict_ids = $this->check_conflict($map);
	            if (count(explode(",",$conflict_ids))>=4) {
	                $this->error("相同开始结束时间段，更新指定软件最多配置4条。与id为".$conflict_ids."冲突");
	            }
        	}
            // 创建时间和更新时间
            $map['create_time'] = $map['up_time'] = time();
			if($map['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($map['package'],$map['start_time'],$map['end_time']);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
            // 添加
            $ret = $model->table('sj_desk_icon')->add($map);
            if ($ret) {
                $this->writelog("桌面预下载配置：添加了id为{$ret}的记录",'sj_desk_icon',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
		}
        //桌面预下载配置修改表单数据展示
		function update_downset()
		{
			//渠道
			$device_db=M();
			$channel_model = M('channel');
			$map['status']=1;
			$where['id']=$_GET['id'];
			$where['status']=1;
			$sj_text_page_one=$device_db->table('sj_desk_icon')->where($where)->find();
			$cookstr = preg_replace('/^,/','',$sj_text_page_one['cid']);
			$cookstr = preg_replace('/,$/','',$cookstr);
			$array = explode(',', $cookstr);
			$chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
			if (in_array("0",$array)&&$chl!=NULL)
            {
              $tong = array("cid"=> "0" ,"chname"=> "通用");
              array_unshift($chl, $tong);
            }
			if (in_array("0",$array)&&$chl==NULL)
            {
              $chl[0]['cid']="0";
              $chl[0]['chname']="通用";
            }
			//机型
			if (strlen($sj_text_page_one['device_did']) > 0)
			{
				$device_selected = explode(',', $sj_text_page_one['device_did']);
				$device_selected_ret = array();
				foreach ($device_selected as $ds) 
				{
					if (empty($ds)) continue;
					$device_name = $device_db->table("pu_device")->where(array('did' => $ds))->field('did,dname')->select();
					$device_selected_ret[] = array('did' => $ds,'dname' => $device_name[0]['dname']);
				}
				$this->assign('device_selected', $device_selected_ret);
			}
			//固件、市场版本、运营商
			$util = D('Sj.Util');
			$this->assign('firmwarelist', $util->getFirmwareList(explode(',', $sj_text_page_one['firmware'])));
			$this->assign('version_list', $util->getMarketVersion(explode(',', $sj_text_page_one['version_code'])));
			$this->assign('operator_list', $util->getOperators(explode(',', $sj_text_page_one['oid'])));
			//获取预下载软件包名称
			$model=M();
			if($sj_text_page_one['package']){
					$softname_result = $model->table('sj_soft')->where(array('package' => $sj_text_page_one['package'], 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
					$sj_text_page_one['package_name']=$softname_result[0]['softname'];
			}
			if($sj_text_page_one['package_appoint']){
					$softname_result = $model->table('sj_soft')->where(array('package' => $sj_text_page_one['package_appoint'], 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
					$sj_text_page_one['package_appoint_name']=$softname_result[0]['softname'];
			}
			$this->assign('chl_list', $chl);
			$this->assign("list", $sj_text_page_one);
			$this->display();
		}
		//桌面预下载配置修改表单数据处理和添加修改
		function upd_downset_to()
		{	
            $map = array();
            $model = M();
   			$package = trim($_POST['package']);
	        $softname_result = $model->table('sj_soft')->where(array('package' => $package, 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
            if(!$softname_result){
            	$this->error("该预下载软件软件不存在");
            }
        	$map['package'] = $package;
            // 开始时间和结束时间
            $start_time = strtotime($_POST['start_time']);
            $end_time = strtotime($_POST['end_time']);
            if (!$start_time) {
                $this->error("开始时间不能为空");
            }
            if (!$end_time) {
                $this->error("结束时间不能为空");
            }
            if ($start_time > $end_time) {
                $this->error("开始时间不能大于结束时间");
            }
			if($_POST['life']==1)
			{
			  if($end_time<time())
			  {
			    $this->error("您修改的复制上线的日期还是无效日期");
			  }
			}
            $map['start_time'] = $start_time;
            $map['end_time'] = $end_time;
            $map['del_condition'] = $_POST['del_condition'];
            //判断更新指定软件的包名是否存在
            $package_appoint = trim($_POST['package_appoint']);
            if(!empty($package_appoint)){
            	$softname_result = $model->table('sj_soft')->where(array('package' => $package_appoint, 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
            	if(!$softname_result){
            		$this->error("该更新指定软件包不存在");
           		}else{
           			if($_POST['trigger_condition']==1){
           				$map['package_appoint'] =$package_appoint;
           				if($map['package']==$map['package_appoint']){
	           				$this->error("预下载软件包名和更新指定软件包名不能相同");
	           			}
	           			$arr=$this->check_pkg_appoint($map);
	           			foreach($arr as $v){
	           				if($v!=$_POST['id']){
	           					$this->error("相同时间段，指定软件不可重复。");
	           				}
	           			}
	           			// if($this->check_pkg_appoint($map)==1){
	           			// 	$this->error("相同时间段，指定软件不可重复。");
	           			// }
           			}else{
           				$map['package_appoint'] = "";
           			}
           		}
            }else{
            	$map['package_appoint'] = "";
            }
            // 更新全部软件的数量
            if($_POST['trigger_condition']==0){
            	$upd_count = intval($_POST['upd_count']);
	            if(strlen($upd_count)!=strlen($_POST['upd_count'])){
	            	$this->error("软件更新数量必须为整数");
	            }
	            if (!is_int($upd_count)) {
	                $this->error("软件更新数量必须为整数");
	            }
	            if($upd_count<=0){
	            	$this->error("软件更新数量不为零");
	            }	
            }
            
            if($_POST['trigger_condition']==0){
           		$map['upd_count'] = $upd_count;
           	}else{
           		$map['upd_count'] = " ";
           	}
            $map['trigger_condition']=trim($_POST['trigger_condition']);   
            $id = $_POST['id'];
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $find = $model->table('sj_desk_icon')->where($where)->find();
			//V6.0精准投放
			//处理上传csv
			$filename=$_FILES['upload_file']['name'];
			if(!$filename&&!trim($_POST['csv_count'])&&trim($_POST['have_pre_dl']))
			{
				$map['csv_count'] = trim($_POST['pre_dl_count']);
				$map['csv_url'] = trim($_POST['have_pre_dl']);
				$map['is_upload_csv'] = 1;
			}
			if(!$filename&&!$_POST['csv_url']&&!trim($_POST['have_pre_dl']))
			{
				$map['csv_count'] = 0;
				$map['csv_url'] = "";
			}
			if($filename&&!$_POST['csv_count'])
			{
				$this -> error("选择好的文件请点击上传才有效");
			}
			if(trim($_POST['csv_url'])&&trim($_POST['csv_count']))
			{
				$map['csv_count'] = $_POST['csv_count'];
				$map['csv_url'] = $_POST['csv_url'];
				$map['is_upload_csv'] = 1;
			}
			unset($_FILES['upload_file']);
			
			//渠道id和机型id
			$channel_id_array=$_POST['cid'];
			$cids = array_unique($channel_id_array);
            if (count($cids) > 0) {
                $s = implode(',', $cids);
                $s = ",{$s},";
                $map['cid'] = $s;
            }
			else
			{
				$map['cid'] = ",,";
			}
			$device_did_array=$_POST['did'];
			$dids = array_unique($device_did_array);
            if (count($dids) > 0) 
			{
				$d= implode(',', $dids);
				$d = ",{$d},";
				$map['device_did'] = $d;
			}
			else
			{
				$map['device_did'] = ",,";
			}
			//运营商和固件版本和市场版本
			$map['oid'] = ','. implode(',', $_POST['oid']). ',';
			$map['firmware'] = ','. implode(',', $_POST['firmware']). ',';
			$map['version_code'] = ','. implode(',', $_POST['version_code']). ',';
            
			$map['life']=$_POST['life'];
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            //检查冲突
            // 更新全部软件的数量
            if($_POST['trigger_condition']==0){
	            //检查冲突
	           $conflict_ids = $this->check_pkg_appoint($map,1);
	            if ($conflict_ids!=2) {
	            	foreach($conflict_ids as $v){
	            		if($v!=$_POST['id']){
	            			$this->error("相同开始结束时间段，面向全部渠道的更新全部软件只能配置一条与id为".$v."冲突");
	            		}
	            	}
	            }
        	}
        	if($_POST['trigger_condition']==1){
	            //检查冲突
	            $conflict_ids = $this->check_conflict($map);
	            if (count(explode(",",$conflict_ids))>=4 && !in_array($_POST['id'],explode(",",$conflict_ids))) {
	                $this->error("相同开始结束时间段，更新指定软件最多配置4条。与".$conflict_ids."冲突");
	            }
        	}
            $where = array('id' => $id);
            $log = $this->logcheck($where, 'sj_desk_icon', $map, $model);
            if($map['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($map['package'],$map['start_time'],$map['end_time']);
				if($shield_error){
					$this -> error($shield_error);
				}
			}
			if($_POST['life']==1)
			{
			  $map['create_time']=time();
			  $map['up_time']=time();
			  unset($map['life']);
			  $ret = $model->table('sj_desk_icon')->add($map);
				if ($ret || $ret === 0) {
					if ($ret) {
						$this->writelog("桌面预下载配置：复制上线了此配置的记录，{$log}",'sj_desk_icon',$ret,__ACTION__ ,"","add");
					}
					$this->success("复制上线成功！");
				} else {
					$this->error("复制上线失败");
				}
			}
			else
			{
			    unset($map['life']);
				$ret = $model->table('sj_desk_icon')->where($where)->save($map);
				if ($ret || $ret === 0) {
					if ($ret) {
						$this->writelog("桌面预下载配置：编辑了id为{$id}的记录，{$log}",'sj_desk_icon',$id,__ACTION__ ,"","edit");
					}
					$this->success("编辑成功！");
				} else {
					$this->error("编辑失败");
				}
			}
		}
		//桌面预下载配置列表数据的删除
		function del_downset() {
			$model = new model();
			$id = $_GET['id'];
			$data['status'] = 0;
			$data['up_time'] = time();
			$del = $model->table('sj_desk_icon')->where("id = $id")->save($data);
			if($del){
                $this->writelog("桌面预下载配置：删除了id为{$id}的记录",'sj_desk_icon',$id,__ACTION__ ,"","del");
				$this -> success("删除成功");
			}else{
				$this->error("删除失败");
			}
		}
		//桌面预下载配置列表精确投放表单数据展示
		function jztf_downset_show()
		{
			$model=M();
			$channel_model = M('channel');
			$list=array();
			$where['id']=$_GET['id'];
			$where['status']=1;
			$result=$model->table('sj_desk_icon')->where($where)->find();
			
			//渠道	
			$cid_str = preg_replace('/^,/','',$result['cid']);
			$cid_str = preg_replace('/,$/','',$cid_str);
			$array = explode(',', $cid_str);
			$cname = $channel_model->where("cid in ({$cid_str})")->findAll();
			if($cid_str=="")
			{
				$list['cname'] = "<p>不限</p>";
			}
			else
			{
				if (in_array("0",$array))
				{
					$list['cname'] .= "<p>通用</p>";
				}
				foreach ($cname as $k1 => $v1) 
				{
					$list['cname'] .= "<p>{$v1['chname']}</p>";
				}
			}
			//机型
			$did_str = preg_replace('/^,/','',$result['device_did']);
			$did_str = preg_replace('/,$/','',$did_str);
			$dname = $channel_model->table("pu_device")->where("did in ({$did_str})")->findAll();
			
			if($did_str=="")
			{
				$list['dname'] .= "<p>不限</p>";		
			}
			else
			{
				foreach ($dname as $k2 => $v2) 
				{
						
					$list['dname'] .= "<p>{$v2['dname']}</p>";				
				}
			}
			//覆盖人数
			if($result['csv_count']==0)
			{
				$list['cover_num']="不限";
			}
			else
			{
				$list['cover_num']=$result['csv_count'];
			}
			if($result['show_place']&1048576)
			{
				//v6.2搜索结果页
				if($result['search_keys_count']==0)
				{
					$list['search_keys_count']="不限";
				}
				else
				{
					$list['search_keys_count']=$result['search_keys_count'];
				}
			}
			
			//运营商
			$operating_db = D('Sj.Operating');
			$oid_str = preg_replace('/^,/','',$result['oid']);
			$oid_str = preg_replace('/,$/','',$oid_str);
			$oid_array = explode(',', $oid_str);
			$oname = $operating_db->where("oid in ({$oid_str})")->findAll();
			
			if($oid_str=="")
			{
				$list['oname'] = "不限";
			}
			else
			{
				if(in_array("-1",$oid_array))
				{
					$list['oname'] .="未插卡";
				}
				foreach ($oname as $k3 => $v3) 
				{
					$list['oname'] .= "<p>{$v3['mname']}</p>";
				}
			}
			//固件版本
			$firmware_str = preg_replace('/^,/','',$result['firmware']);
			$firmware_str = preg_replace('/,$/','',$firmware_str);
			$firmware_array = explode(',', $firmware_str);
			
			if($firmware_str==""||$result['firmware']=="")
			{
				$list['firmwares']="不限";
			}
			else
			{
				$firmwares = $channel_model->table('pu_config')->field('configname,configcontent')->where("config_type='firmware' and status=1 and configname in ({$firmware_str})")->select();

				foreach ($firmwares as $k4 => $v4) 
				{	
					$list['firmwares'] .= "<p>{$v4['configcontent']}</p>";				
				}
			}
			//市场版本
			$util = D('Sj.Util');
			$version_str = preg_replace('/^,/','',$result['version_code']);
			$version_str = preg_replace('/,$/','',$version_str);
			$version_array = explode(',', $version_str);
			if($version_str==""||$result['version_code']=="")
			{
				$list['version_name']="不限";
			}
			else
			{
				$version_list=$util->getMarketVersion($version_array);
				foreach ($version_list as $k5 => $v5) 
				{	
					if($v5[1]==true)
					{
						$list['version_name'] .= "<p>{$v5[0]}</p>";
					}
				}
			}
			$this->assign("list",$list);
			$this->display();
		}
        
        //返回冲突id，否则返回0
        function check_conflict($record, $id = 0) {
            $start_time = $record['start_time'];
            $end_time = $record['end_time'];
            $model = M();
            $where = array(
                // "{$content_key}" => $content_value,
                'status' => 1,
                'start_time' => array('elt', $end_time),
                'end_time' => array('egt', $start_time),
                'package_appoint'=>array('neq'," "),
            );
            $conflict_list = $model->table('sj_desk_icon')->where($where)->select();
            if (!empty($conflict_list)) {
            	$id_arr = array();
            	foreach($conflict_list as $v){
            		$id_arr[]=$v['id'];
            	}
            	if (!empty($id_arr))
                    return implode(',', $id_arr);
            }
            return 0;
        }
        //返回冲突id，否则返回0
        function check_conflict11($record, $id = 0) {
            $oid = $record['oid'];
            $cid = $record['cid'];
            $start_time = $record['start_time'];
            $end_time = $record['end_time'];
			$life=$record['life'];
			$content_key = 'package';
            $content_value = $record['package'];
            $model = M();
            $where = array(
                "{$content_key}" => $content_value,
                'status' => 1,
                'oid' => $oid,
                'cid' => $cid,
                'start_time' => array('elt', $end_time),
                'end_time' => array('egt', $start_time),
            );
            if ($id&&$life!=1) {
                $where['id'] = array('neq', $id);
            }
            $conflict_list = $model->table('sj_desk_icon')->where($where)->select();
            if (!empty($conflict_list)) {
            	$id_arr = array();
            	foreach($conflict_list as $v){
            		$id_arr[]=$v['id'];
            	}
            	if (!empty($id_arr))
                    return implode(',', $id_arr);
            }
            return 0;
        }
        //更新指定软件时：返回冲突id，否则返回0
        function check_package_appoint() {
        		$start_time = strtotime($_GET['start_time']);
	            $end_time = strtotime($_GET['end_time']);
				$content_key = 'package_appoint';
	            $content_value = $_GET['package_appoint'];
	            $id = $_GET['id'];
	            $model = M();
	            $where = array(
	                "{$content_key}" => $content_value,
	                'status' => 1,
	                'start_time' => array('elt', $end_time),
	                'end_time' => array('egt', $start_time),
	            );
	            $conflict_list = $model->table('sj_desk_icon')->where($where)->select();
	            $arr=array();
	            foreach($conflict_list as $v){
	            	$arr[]=$v['id'];
	            }
	            if(!empty($id)){
	            	if(in_array($id, $arr) && count($arr)==1){
		            	echo 1;
		            	return  1;
	            	}
	            }
	            if(!empty($conflict_list)){
	            	echo "相同时间段，指定软件不可重复。";
	            	return "相同时间段，指定软件不可重复。";
	            }else{
	            	echo 1;
	            	return  1;
	            }
     	}
	     //更新指定软件时：返回冲突id，否则返回0
	        function check_pkg_appoint($record="",$num=0) {
	    		$start_time = $record['start_time'];
	            $end_time = $record['end_time'];
	            $model = M();
	            if($num==1){
	            	 $where = array(
	                'status' => 1,
	                'start_time' => array('elt', $end_time),
	                'end_time' => array('egt', $start_time),
	                'upd_count'=>array('gt',0),
	            	);
	            }else{
	            	$content_key = 'package_appoint';
	            	$content_value = $record['package_appoint'];
	            	 $where = array(
	                $content_key => $content_value,
	                'status' => 1,
	                'start_time' => array('elt', $end_time),
	                'end_time' => array('egt', $start_time),
	            	);
	            }
	            $conflict_list = $model->table('sj_desk_icon')->where($where)->select();
	            if(!empty($conflict_list)){
	            	$arr=array();
	            	foreach($conflict_list as $v){
	            		$arr[]=$v['id'];
	            	}
	            	// echo "<pre>";var_dump($arr);die;
	            	// return $conflict_list[0]['id'];
	            	return $arr;
	            }else{
	            	return 2;
	            }
	     }


	}
?>
