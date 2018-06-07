<?php
	class TextpageAction extends CommonAction {
		function textpagelist() {
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
                $order = "end_time desc";
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
            //什么值得玩平台
            $pid = $_GET['pid'] ? $_GET['pid'] : 1;//默认为1
            $where['pid'] = $pid;
            $util = D("Sj.Util");
	        $product_list = $util->getProducts($pid);
	        $this->assign('pid', $pid);
	        $this->assign('product_list', $product_list);
            // 分页
            import("@.ORG.Page");
            $limit = 10;
            $count = $model->table('sj_text_page')->where($where)->count();
            $page  = new Page($count, $limit);
            // 当前页数据
			$list = $model->table('sj_text_page')->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
			$util = D("Sj.Util"); 
            // 处理list
            foreach ($list as $key => $value) {
                $show_place = $value['show_place'];
                $oid = $value['oid'];
                $cid = $value['cid'];
                $show_place_name = $this->get_show_place_name($show_place);
                $list[$key]['show_place_name'] = $this->shorten_sentence($show_place_name);
                $content_type = $value['content_type'];
                if ($content_type == 2) {
                    // 活动名称
                    $list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($value['activity_id']);
                } else if ($content_type == 3) {
                    // 专题名称
                    $list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($value['feature_id']);
                } else if ($content_type == 4) {
                    // 页面名称
                    $list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($value['page_type']);
                }else if ($content_type == 8) {
                    // 预约名称
                    $list[$key]['order_name'] = ContentTypeModel::convertOrderId2OrderName($value['activity_id']);
                }
				$typelist = $util->getHomeExtentSoftTypeList($value['co_type']);
				foreach($typelist as $k => $v){
					if($v[1] == true)
					{
						$list[$key]['co_types'] = $v[0];
					}
				}
            }
			$category_list = ContentTypeModel::getCategoryTypes();

			$this->assign('list', $list);
			$this->assign('category_list', $category_list);
            $this->assign('overdue', $overdue);
            $this->assign('now', $now);
            $this->assign("page", $page->show());
			$this->display();
		}
        
        function show_all_content() {
            $model = new Model();
			$id = $_GET['id'];
			$find = $model->table('sj_text_page')->where(array('id'=>$id))->find();
            $show_place_name = $this->get_show_place_name($find['show_place']);
            $this->assign('show_place_name', $show_place_name);
            $this->display('show_all_content');
        }
		function add_textpage_show(){
			//合作形式
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			//什么值得玩平台
			$pid = $_GET['pid'] ? $_GET['pid'] : 1;
			$this->assign('pid',$pid);
			$this->display();
		}
		function add_textpage() {
            $model = M();
            $map = array();
			$type = $_POST['type'];
			if($type==1){
				// 标题
				$title = trim($_POST['title']);
				if (!$title) {
					$this->error("标题不能为空");
				}
				if (mb_strlen($title, 'utf-8') > 3) {
					$this->error("标题长度不能大于3个字");
				}
				$map['title'] = $title;
				// 描述 V6.4如果选中 自动关联详情页标题  描述就是非必填
				$description = trim($_POST['description']);
				$map['is_auto_connect'] = trim($_POST['auto_connect'])?trim($_POST['auto_connect']):0;
				if(!$_POST['auto_connect'])
				{
					if (!$description)
					{
						$this->error("描述不能为空");
					}
				}
				else
				{
					if (!$description)
					{
						//关联详情页的标题取出来  coop_detail_url_id
						if($_POST['coop_detail_url_id']) //选择关联的标题
						{
							$coop_where=array(
									'id' =>trim($_POST['coop_detail_url_id']),
							);
							$coop_result = $model->table('coop_content')->where($coop_where)->find();
							//$description = msubstr($coop_result['title'], $start=0,17, $charset='utf-8', $suffix=true);
							$description = $coop_result['title'];
						}
					}
				}
				/*if (mb_strlen($description, 'utf-8') > 20) {
                $this->error("描述长度不能大于20个字");
            }*/
				$map['description'] = $description;
			}


            // 展示页面
            $show_place_arr = $_POST['show_place'];
            $map['show_place'] = 0;
			if(in_array("32768",$show_place_arr))
			{
			 $map['single_soft']=trim($_POST['single_pk']);
			}
			else
			{
			 $map['single_soft']="";
			}
			if(in_array("1048576",$show_place_arr))
			{
				//V6.2搜索结果页
				//处理上传指定搜索词csv
				$filename_search_key=$_FILES['upload_search_keys']['name'];
				if($type==2&&!$_POST['search_keys_count']){
					$this->error("请上传指定搜索词csv");
				}
				if(!$filename_search_key&&!$_POST['search_keys_count'])
				{
					$map['search_keys_url'] = "";
					$map['search_keys_count'] = 0; 
				}
				if($filename_search_key&&!$_POST['search_keys_count'])
				{
					$this -> error("选择好的文件请点击上传才有效");
				}
				if($_POST['search_keys_count']&&$_POST['search_keys_url'])
				{
					$map['search_keys_count'] = $_POST['search_keys_count'];
					$map['search_keys_url'] = $_POST['search_keys_url'];
				}
				unset($_FILES['upload_search_keys']);
			}
			else
			{
				$map['search_keys_url'] = "";
				$map['search_keys_count'] = 0; 
			}
            foreach ($show_place_arr as $show_place) {
                $map['show_place'] |= $show_place;
            }
            if (!$map['show_place']) {
                $this->error("页面位置不能为空");
            }
			//合作形式
			if(isset($_POST['co_type'])){	
				$map['co_type'] = $_POST['co_type'];	
			}else{			
				$map['co_type'] = 0;		
			}
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
			//推荐内容处理 合并
            $rcontent=ContentTypeModel::saveRecommendContent($_POST,'', $map);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
			if($type==2)
			$this->save_web_game($map,1);
            // 检查冲突
            $conflict_ids = $this->check_conflict($map);
            if ($conflict_ids) {
                $this->error("排期与id为{$conflict_ids}的记录相冲突");
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
			$map['type'] = $type;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$map['pid'] = $_POST['pid'] ? $_POST['pid'] : 1;//什么值得玩平台
            // 添加
            $ret = $model->table('sj_text_page')->add($map);
            if ($ret) {
                $this->writelog("文字链推广位：添加了id为{$ret}的记录","sj_text_page",$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
		}
        
		function update_textpage()
		{
			//渠道
			$device_db=M();
			$channel_model = M('channel');
			$map['status']=1;
			$where['id']=$_GET['id'];
			$where['status']=1;
			$sj_text_page_one=$device_db->table('sj_text_page')->where($where)->find();
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
			//合作形式
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList($sj_text_page_one['co_type']);
			$sj_text_page_one['parameter_field1'] = json_decode($sj_text_page_one['parameter_field'],true);
			$this->assign('typelist',$typelist);

			$this->assign('chl_list', $chl);
			$this->assign("list", $sj_text_page_one);
			$this->display();
		}

		function update_textpage_to()
		{
            $map = array();
            $model = M();
			$type = $_POST['type'];
			$map['type'] = $type;
			if($type==1){
				// 标题
				$title = trim($_POST['title']);
				if (!$title) {
					$this->error("标题不能为空");
				}
				if (mb_strlen($title, 'utf-8') > 3) {
					$this->error("标题长度不能大于3个字");
				}
				$map['title'] = $title;
				// 描述 V6.4如果选中 自动关联详情页标题  描述就是非必填
				$description = trim($_POST['description']);
				$map['is_auto_connect'] = trim($_POST['auto_connect'])?trim($_POST['auto_connect']):0;
				if(!$_POST['auto_connect'])
				{
					if (!$description)
					{
						$this->error("描述不能为空");
					}
				}
				else
				{
					if (!$description)
					{
						//关联详情页的标题取出来  coop_detail_url_id
						if($_POST['coop_detail_url_id']) //选择关联的标题
						{
							$coop_where=array(
									'id' =>trim($_POST['coop_detail_url_id']),
							);
							$coop_result = $model->table('coop_content')->where($coop_where)->find();
							//$description = msubstr($coop_result['title'], $start=0,17, $charset='utf-8', $suffix=true);
							$description = $coop_result['title'];
						}
					}
				}
				/*if (mb_strlen($description, 'utf-8') > 20) {
                    $this->error("描述长度不能大于20个字");
                }*/
				$map['description'] = $description;
			}

            // 展示页面
            $show_place_arr = $_POST['show_place'];
            $map['show_place'] = 0;
            foreach ($show_place_arr as $show_place) {
                $map['show_place'] |= $show_place;
            }
			if($map['show_place']&32768)
			{
			   $map['single_soft']=trim($_POST['single_pk']);
			}
			else
			{
			  $map['single_soft']="";
			}
			if($map['show_place']&1048576)
			{
				//V6.2搜索结果页
				//处理上传指定搜索词csv 可以编辑
				$filename_search_key=$_FILES['upload_search_keys']['name'];
				/*	ym 无用的代码				if(!$filename_search_key&&!trim($_POST['search_keys_count'])&&trim($_POST['have_keys_url']))
				{
					$map['search_keys_count'] = trim($_POST['have_keys_count']);
					$map['search_keys_url'] = trim($_POST['have_keys_url']);
				}*/
				if(!$filename_search_key&&!$_POST['search_keys_url']&&!trim($_POST['have_keys_url']))
				{
					$map['search_keys_count'] = 0;
					$map['search_keys_url'] = "";
				}
				if($filename_search_key&&!$_POST['search_keys_count'])
				{
					$this -> error("选择好的文件请点击上传才有效");
				}
				if(trim($_POST['search_keys_url'])&&trim($_POST['search_keys_count']))
				{
					$map['search_keys_count'] = $_POST['search_keys_count'];
					$map['search_keys_url'] = $_POST['search_keys_url'];
				}
				unset($_FILES['upload_search_keys']);
			}
			else
			{
				$map['search_keys_url'] = "";
				$map['search_keys_count'] = 0;
			}
            if (!$map['show_place']) {
                $this->error("页面位置不能为空");
            }          
            $id = $_POST['id'];
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $find = $model->table('sj_text_page')->where($where)->find();
            // 编辑不允许改变类型，获得此记录数据库中的类型
            $content_type = $find['content_type'];
            $map['content_type'] = $content_type;//检查冲突时需传此值
			//推荐内容处理
			$rcontent=ContentTypeModel::saveRecommendContent($_POST,$content_type,$map);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
			//合作形式
			if(isset($_POST['co_type'])){
				$map['co_type'] = $_POST['co_type'];
			}else{
				$map['co_type'] = 0;
			}

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
			$map['life']=$_POST['life'];
			if($type==2)
				$this->save_web_game($map,2,$find);
            // 检查冲突
            $conflict_ids = $this->check_conflict($map, $id);
            if ($conflict_ids) {
                $this->error("排期与id为{$conflict_ids}的记录相冲突");
            }
            $where = array('id' => $id);
            $log = $this->logcheck($where, 'sj_text_page', $map, $model);
            if($find['package']){
            	//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($find['package'],$map['start_time'],$map['end_time']);
				if($shield_error){
				    $this -> error($shield_error);
				}
            }
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$map['pid'] = $_POST['pid'] ? $_POST['pid'] : 1;//什么值得玩平台
			if($_POST['life']==1)
			{
			  $map['create_time']=time();
			  $map['up_time']=time();
			  unset($map['life']);
			  $ret = $model->table('sj_text_page')->add($map);
				if ($ret || $ret === 0) {
					if ($ret) {
						$this->writelog("文字链推广位：复制上线了title为{$map['title']}的记录，{$log}","sj_text_page",$ret,__ACTION__ ,"","add");
					}
					$this->success("复制上线成功！");
				} else {
					$this->error("复制上线失败");
				}
			}
			else
			{
			    unset($map['life']);
				$ret = $model->table('sj_text_page')->where($where)->save($map);
				if ($ret || $ret === 0) {
					if ($ret) {
						$this->writelog("文字链推广位：编辑了id为{$id}的记录，{$log}","sj_text_page",$id,__ACTION__ ,"","edit");
					}
					$this->success("编辑成功！");
				} else {
					$this->error("编辑失败");
				}
			}
		}

		function del_textpage() {
			$model = new model();
			$id = $_GET['id'];
			$data['status'] = 0;
			$data['up_time'] = time();
			$del = $model->table('sj_text_page')->where("id = $id")->save($data);
			if($del){
                $this->writelog("文字链推广位：删除了id为{$id}的记录","sj_text_page",$id,__ACTION__ ,"","del");
				$this -> success("删除成功");
			}else{
				$this->error("删除失败");
			}
		}
		function jztf_textpage_show()
		{
			$model=M();
			$channel_model = M('channel');
			$list=array();
			$where['id']=$_GET['id'];
			$where['status']=1;
			$result=$model->table('sj_text_page')->where($where)->find();
			
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
        
        // 返回冲突id，否则返回0
        function check_conflict($record, $id = 0) {
            $content_type = $record['content_type'];
            $show_place = $record['show_place'];
            $oid = $record['oid'];
            $cid = $record['cid'];
            $start_time = $record['start_time'];
            $end_time = $record['end_time'];
			$life=$record['life'];
			$single_soft=$record['single_soft'];
            $model = M();
            if ($content_type == 1) {
                // 查找包名
                $content_key = 'package';
                $content_value = $record['package'];
            } else if ($content_type == 2) {
                // 查找活动
                $content_key = 'activity_id';
                $content_value = $record['activity_id'];
            } else if ($content_type == 3) {
                // 查找专题
                $content_key = 'feature_id';
                $content_value = $record['feature_id'];
            } else if ($content_type == 4) {
                // 查找专题
                $content_key = 'page_type';
                $content_value = $record['page_type'];
				$parameter_field = $record['parameter_field'];
            } else if ($content_type == 5) {
                // 查找网页
                $content_key = 'website';
                $content_value = $record['website'];
            } else if ($content_type == 6) {
                // 查找礼包id
                $content_key = 'gift_id';
                $content_value = $record['gift_id'];
            }else if ($content_type == 7) {
                // 查找攻略
                $content_key = 'strategy_id';
                $content_value = $record['strategy_id'];
            }else {
                return false;
            }
			if($parameter_field)
			{
				$where = array(
					"{$content_key}" => $content_value,
					'status' => 1,
					'oid' => $oid,
					'cid' => $cid,
					'start_time' => array('elt', $end_time),
					'end_time' => array('egt', $start_time),
					'parameter_field'=>$parameter_field,
				);
			}
			else
			{
				$where = array(
					"{$content_key}" => $content_value,
					'status' => 1,
					'oid' => $oid,
					'cid' => $cid,
					'start_time' => array('elt', $end_time),
					'end_time' => array('egt', $start_time),
				);
			}
           
            if ($id&&$life!=1) {
                $where['id'] = array('neq', $id);
            }
            $conflict_list = $model->table('sj_text_page')->where($where)->select();
            if (!empty($conflict_list)) {
                $id_arr = array();
                foreach ($conflict_list as $value) 
				{
                    if ($show_place & $value['show_place']) 
					{
					  if($value['show_place'] & 32768 && $show_place & 32768)
					  {
					     if($value['single_soft']==$single_soft)
						 {
						   $id_arr[] = $value['id'];
						 }
					  }
					  else
					  { 
						$res = $this->check_text_page($record,$value);
						if($res){
							// 如果位置有重叠
							$id_arr[] = $value['id'];
						}
                        
					  }
                    }
					else
					{
					 if($show_place & 1 && $value['show_place'] & 32768||$show_place & 32768 && $value['show_place'] & 1)
					   {
						 $id_arr[] = $value['id'];
					   }
					}
                }
                if (!empty($id_arr))
                    return implode(',', $id_arr);
            }
            return 0;
        }
        
        function get_show_place_name($show_place) {
            $show_place_arr = array();
            if ($show_place & 4)
                $show_place_arr[] = '首页推荐';
            if ($show_place & 1)
                $show_place_arr[] = '详情页';
            if ($show_place & 2)
                $show_place_arr[] = '可更新页';
            if ($show_place & 8)
                $show_place_arr[] = '飙升';
            if ($show_place & 16)
                $show_place_arr[] = '首页必备';
            /*
            if ($show_place & 32)
                $show_place_arr[] = '首页尝鲜';
            */
            if ($show_place & 64)
                $show_place_arr[] = '应用-最热';
            if ($show_place & 128)
                $show_place_arr[] = '应用分类Tab';
            if ($show_place & 256)
                $show_place_arr[] = '应用日排行';
            if ($show_place & 512)
                $show_place_arr[] = '游戏-最热';
            if ($show_place & 1024)
                $show_place_arr[] = '网游';
            if ($show_place & 2048)
                $show_place_arr[] = '单机';
            if ($show_place & 4096)
                $show_place_arr[] = '游戏分类Tab';
            if ($show_place & 8192)
                $show_place_arr[] = '管理';
			if ($show_place & 16384)
			    $show_place_arr[] = '个人中心';
			if ($show_place & 32768)
                $show_place_arr[] = '单一软件详情页';
			if ($show_place & 65536)
                $show_place_arr[] = '发现-推荐';
			if ($show_place & 131072)
                $show_place_arr[] = '发现-汉化';
			if ($show_place & 262144)
                $show_place_arr[] = '发现-专题';
			if ($show_place & 524288)
                $show_place_arr[] = '6.0应用排行';
			if ($show_place & 1048576)
                $show_place_arr[] = '搜索结果页';
			if ($show_place & 33554432)
                $show_place_arr[] = '金币列表';
            $show_place_name = '';
            foreach ($show_place_arr as $name) {
                if ($show_place_name != '') {
                    $show_place_name .= '，';
                }
                $show_place_name .= $name;
            }
            return $show_place_name;
        }
        
        function shorten_sentence($sentence, $len = 10) {
            $sen_len = mb_strlen($sentence, 'utf-8');
            if ($sen_len > $len) {
                $sentence = mb_substr($sentence, 0, $len - 2, 'utf-8') . ' ...';
            }
            return $sentence;
        }
		
		function save_web_game(&$map,$type=1,$find=''){
			$rank = (int)(trim($_POST['rank']));
			if(!$rank||$rank > 20){
				$this->error("排序值20以内");
			}
			$title = trim($_POST['i_title']);
			if (!$title) {
				$this->error("图标名称不能为空");
			}
			if (mb_strlen($title, 'utf-8') > 20||mb_strlen($title, 'utf-8') < 2) {
				$this->error("图标名称2-20字符");
			}
			$map['title'] = $title;
			$s_description = trim($_POST['s_description']);
			if(!$s_description) $this->error("一句话简介不能为空");
			if (mb_strlen($s_description, 'utf-8') > 40) {
				$this->error("一句话简介40字符以内");
			}
			$map['description'] = $s_description;
			$matches="/^[1-9]\d*$/";
			if($_POST['init_val'])
			{
				if(!preg_match($matches,$_POST['init_val']))
				{
					$this->error("人气值的初始值为正整数");
				}
			}
			else
			{
				$this->error("人气值的初始值不能为空");
			}
			if($_POST['random_start'])
			{
				if(!preg_match($matches,$_POST['random_start']))
				{
					$this->error("人气值的随机开始值为正整数");
				}
			}
			else
			{
				$this->error("人气值的随机开始值不能为空");
			}
			if($_POST['random_end'])
			{
				if(!preg_match($matches,$_POST['random_end']))
				{
					$this->error("人气值的随机结束值为正整数");
				}
			}
			else
			{
				$this->error("人气值的随机结束值不能为空");
			}
			if($type==1&&!$_FILES['i_image_url']['name']) $this->error('未上传图标素材');
			if($_FILES['i_image_url']['name']){
				$folder = "/img/" . date("Ym/d/");
				$this->mkDirs(UPLOAD_PATH . $folder);
				// 取得图片后缀
				$suffix_low = preg_match("/\.(jpg|png|JPG|PNG)$/", $_FILES['i_image_url']['name'],$matches_low);
				if ($matches_low) {
					$suffix_low = $matches_low[0];
				} else {
					$this->error('上传图标素材格式错误！');
				}
				// 判断图片长和宽
				$img_info_arr = getimagesize($_FILES['i_image_url']['tmp_name']);
				if (!$img_info_arr) {
					$this->error('上传图标素材出错！');
				}
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				 if ($width != 60 || $height != 60)
                        $this->error("上传图标素材大小错误，宽需为60px，高需为60px");
				$image_path = $folder . time() . rand(1000,9999) . $suffix_low;
                $img_path = UPLOAD_PATH . $image_path;
                $ret = move_uploaded_file($_FILES['i_image_url']['tmp_name'], $img_path);
                $image_url = $image_path;
			}
			if($_FILES['gif_image_url']['name']){
				$folder = "/img/" . date("Ym/d/");
				$this->mkDirs(UPLOAD_PATH . $folder);
				// 取得图片后缀
				$suffix_low = preg_match("/\.(gif|GIF)$/", $_FILES['gif_image_url']['name'],$matches_low);
				if ($matches_low) {
					$suffix_low = $matches_low[0];
				} else {
					$this->error('上传gif图片格式错误！');
				}
				// 判断图片长和宽
				$img_info_arr = getimagesize($_FILES['gif_image_url']['tmp_name']);
				if (!$img_info_arr) {
					$this->error('上传gif图片出错！');
				}
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				if ($width != 60 || $height != 60)
					$this->error("上传gif图片大小错误，宽需为60px，高需为60px");
				$gif_path = $folder . time() . rand(1000,9999) . $suffix_low;
				$g_path = UPLOAD_PATH . $gif_path;
				$ret = move_uploaded_file($_FILES['gif_image_url']['tmp_name'], $g_path);
				$gif_url = $gif_path;
			}
//			var_dump($gif_url);
			if($map['parameter_field']) $map['parameter_field'] = json_decode($map['parameter_field'],true);

			$map['parameter_field']['rank'] = $rank;
			$map['parameter_field']['init_val'] = $_POST['init_val'];
			$map['parameter_field']['random_start'] = $_POST['random_start'];
			$map['parameter_field']['random_end'] = $_POST['random_end'];
			if($type==1){
				$map['parameter_field']['image_url'] = $image_url;
				$map['parameter_field']['gif_image_url'] = $gif_url;
			}else{
				$old_info = json_decode($find['parameter_field'],true);
				$map['parameter_field']['image_url'] = $image_url?$image_url:$old_info['image_url'];
				$map['parameter_field']['gif_image_url'] = $gif_url?$gif_url:$old_info['gif_image_url'];
			}
//			var_dump($map['parameter_field']);
//			exit();
			$map['parameter_field'] = json_encode($map['parameter_field']);
		}
		
		//文字推广位-网页游戏细节优化4661 过滤
		function check_text_page($record,$value){
			if(!(($record['content_type']==$value['content_type'])&&($value['content_type']==5))){
				return true;
			}
			if(!(($record['show_place']==$value['show_place'])&&($value['show_place']==1048576))){
				return true;
			}
			if(!($record['start_time']==$value['start_time'])){
				return true;
			}
			if(!($record['end_time']==$value['end_time'])){
				return true;
			}
			$param_1 = json_decode($record['parameter_field'],true);
			$rank_1 = $param_1['rank'];
			$param_2 = json_decode($value['parameter_field'],true);
			$rank_2 = $param_2['rank'];
			if(($rank_1==$rank_2)||($rank_1<=0)||($rank_2<=0)){
				return true;
			}
			/*
			$search_1 = getAnzhiFile($record['search_keys_url']);
			$search_2 = getAnzhiFile($value['search_keys_url']);
			$search_3 = array_intersect($search_1,$search_2);
			if(count($search_3)==0){
				return true;
			}*/
			return false;			
		}
	}
	
	
?>
