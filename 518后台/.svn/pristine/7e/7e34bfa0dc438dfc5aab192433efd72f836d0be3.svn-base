<?php
	class CoopwebsetAction extends CommonAction {
	    private $rank_arr = array('year_rank','area_rank','type_rank','actor_rank');
		private  $show_arr = array('year_show','area_show','type_show','actor_show');
		private $show_name_arr = array('状态','集数','年代','地区','类型','主演');
		//资讯列表展示
		function webset_list() {
			// header('content-type:text/html;charset=utf-8');
			$model = M('');
			$caiji_model =  D('Sj.Coopwebset');
            // 分页
            import("@.ORG.Page");
            $limit = 10;
			$where['type'] = 2;
            // $count = $model->table('caiji_coop_site_config')->where($where)->count();
            $count = $caiji_model->table('caiji_video_config')->count();
            $where=array();
            $where['config_type']='coopweb_6.3';
            // $where['configname']=63;
            $conf_id = $model->table('pu_config')->field('conf_id,configcontent')->where($where)->find();
			$c_conf_id  = $model->table('pu_config')->field('conf_id,configcontent')->where(array('config_type'=>'COOP_C_WEB_RANK','status'=>1))->find();
          
            $page  = new Page($count, $limit);
            // 当前页数据
			// $list = $model->table('coop_site_config')->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
			$where=array();
			$where['type'] = 2;
			$list = $caiji_model->table('caiji_video_config')->where($where)->field('id,website_name,rank,show_count,status,package')->limit($page->firstRow . ',' . $page->listRows)->select();
			$pkg = array();
			foreach($list as $k=>$v){
				if($v['package']) $pkg[] = $v['package'];
			}
			list($soft_arr,$chl_soft_arr) =$this->pkg_info($pkg);
			$this->assign('list', $list);
            $this->assign('conf_id', $conf_id['conf_id']);
			$this->assign('c_conf_id',$c_conf_id['conf_id']);
			$this->assign('c_conf_val',$c_conf_id['configcontent']);
            $this->assign('configcontent', $conf_id['configcontent']);
			$this -> assign('soft_arr',$soft_arr);
			$this -> assign('chl_soft_arr',$chl_soft_arr);
            $this->assign("page", $page->show());
			$this->display();
		}
        //资讯修改表单数据展示
		function update_webset()
		{
			//渠道
			$model = D('Sj.Coopwebset');
			$device_db=D('Sj.Coopwebset');
			$channel_model = M('channel');
			$map['status']=1;
			$where['type'] = 2;
			$where['id']=$_GET['id'];
			// $where['status']=1;
			$sj_text_page_one=$device_db->table('caiji_video_config')->where($where)->find();
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
			// echo "<pre>";var_dump($chl);die;
			$this->assign('chl_list', $chl);
			$this->assign("list", $sj_text_page_one);
			$this->display();
		}
		//资讯修改表单数据处理和添加修改
		function upd_webset_to()
		{	
            $map = array();
			$model = D('Sj.Coopwebset');
            $id = $_POST['id'];
            $where = array(
                'id' => $id,
            );
            $find = $model->table('caiji_video_config')->where($where)->find();
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
            $where = array('id' => $id);
            $log = $this->logcheck($where, 'caiji_video_config', $map, $model);
			$ret = $model->table('caiji_video_config')->where($where)->save($map);
			if ($ret || $ret === 0) {
				if ($ret) {
					$this->writelog("合作站点配置：编辑了id为{$id}的记录，{$log}",'caiji_video_config',$id,__ACTION__ ,'','edit');
				}
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败");
			}
		}
		//资讯列表数据的删除
		function del_webset() {
			$model = D('Sj.Coopwebset');
			$id = $_GET['id'];
			$res=$model->table('caiji_video_config')->where("id = $id")->find();
			$data['status'] = ($res['status']==1)?0:1;
			$data['update_time'] = time();
			$del = $model->table('caiji_video_config')->where("id = $id")->save($data);
			if($del){
				if($data['status']==1){
					$this->writelog("合作站点配置：启用了id为{$id}的配置",'caiji_video_config',$id,__ACTION__ ,'','edit');
				}else{
					$this->writelog("合作站点配置：停用了id为{$id}的配置",'caiji_video_config',$id,__ACTION__ ,'','edit');
				}
				$this -> success("操作成功");
			}else{
				$this->error("操作失败");
			}
		}
		//资讯列表精确投放表单数据展示
		function jztf_downset_show()
		{
			$model = D('Sj.Coopwebset');
			$where['id']=$_GET['id'];
			// $where['status']=1;
			$result=$model->table('caiji_video_config')->where($where)->find();
			$list = $this->show_config($result);
			$this->assign("list",$list);
			$this->display();
		}
		//资讯编辑优先级和展现次数
		function edit_coop_num(){
			$model = D('Sj.Coopwebset');
			if($_POST){
				$id = $_POST['id'];
				if(!$id) exit(json_encode(array('code'=>0,'msg'=>'缺少参数')));
				// $model = D('Sj.Coopwebset');
				$old_info = $model->table('caiji_video_config')->where(array("id"=>$id))->find();
				$save_data = array();
				if(isset($_POST['rank'])) $save_data['rank'] = $_POST['rank'];
				if(isset($_POST['show_count'])) $save_data['show_count'] = $_POST['show_count'];
				$ret = $model->table('caiji_video_config')->where(array('id'=>$id))->save($save_data);
				if($ret){
					$str = "";
					if(isset($_POST['rank'])&&$_POST['rank']!=$old_info['rank']){
						$str .= ",优先级由[{$old_info['rank']}]修改为[{$_POST['rank']}]";
					}
					if(isset($_POST['show_count'])&&$_POST['show_count']!=$old_info['show_count']){
						$str .= ",展现次数由[{$old_info['show_count']}]修改为[{$_POST['show_count']}]";
					}
					if(!empty($str))
					$this->writelog("搜索合作-资讯：修改id为{$id}的资讯{$str}",'caiji_video_config',$id,__ACTION__ ,'','edit');
					exit(json_encode(array('code'=>1,'msg'=>'保存成功')));
				}else{
					exit(json_encode(array('code'=>0,'msg'=>'保存失败')));
				}
			}
			$find = $model->table('caiji_video_config')->where(array('id'=>$_GET['id']))->find();
			$type = $_GET['type'];
			$this->assign('type',$type);
			$this->assign('find',$find);
			$this->assign('mess_id',$_GET['id']);
			$this->display();
		}
		function show_config($result){
			$list=array();
			$channel_model = M('channel');
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
			return $list;
		}

		//编辑资讯内容优先级
		function pub_change_rank(){
			$num = $_GET['num'];
			$conf_id = $_GET['conf_id'];
			$model = M();
			$where = array(
					'conf_id' => $conf_id
			);
			$data = array(
					"configcontent" => $num,
					'uptime' => time()
			);
			$old_info = $model->table('pu_config')->where($where)->find();
			$video_rank = $model->table('pu_config')->where(array('config_type'=>'COOP_C_VIDEO_RANK','status'=>1))->field('configcontent')->find();
			if($num==$video_rank['configcontent']){
				echo '视频内容优先级与资讯内容优先级不能相同';exit();
			}
			$res = $model->table('pu_config')->where($where)->save($data);
			if(!empty($res)){
				$str="";
				if(isset($num)&&$num!=$old_info['configcontent']){
					$str .= ",优先级次数由[{$old_info['configcontent']}]修改为[{$num}]";
				}
				if(!empty($str))
					$this->writelog("搜索合作-资讯：修改id为{$old_info['conf_id']}的资讯{$str}",'pu_config',$old_info['conf_id'],__ACTION__ ,'','edit');
				echo "编辑资讯内容排序成功";exit();
			}else{
				echo "编辑资讯内容排序失败";exit();
			}
		}
        //编辑资讯内容排序
        function change_rank() {
            $num = $_GET['num'];
            $conf_id = $_GET['conf_id'];
            $model = M();
            $where = array(
                'conf_id' => $conf_id
            );
            $data = array(
                "configcontent" => $num,
                'uptime' => time()
            );
            $old_info = $model->table('pu_config')->where($where)->find();
            $res = $model->table('pu_config')->where($where)->save($data);
            if(!empty($res)){
        		$str="";
        		if(isset($num)&&$num!=$old_info['configcontent']){
					$str .= ",展现次数由[{$old_info['configcontent']}]修改为[{$num}]";
				}
				if(!empty($str))
				$this->writelog("搜索合作-资讯：修改id为{$old_info['conf_id']}的资讯{$str}",'pu_config',$old_info['conf_id'],__ACTION__ ,'','edit');
            	echo "编辑资讯内容排序成功";
            }else{
            	echo "编辑资讯内容排序失败";
            }
     	}
		//搜索合作-视频配置列表
		function  video_config(){
			$model = D('Sj.Coopwebset');
			$where = array();
			$where['type'] = 1;
			$total = $model->table("caiji_video_config")->where($where)->count();
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
			$param = http_build_query($_GET);
			import('@.ORG.Page2');
			$Page = new Page($total, $limit, $param);
			$Page->rollPage = 10;
			$Page->setConfig('header', '篇记录');
			$Page->setConfig('first', '首页');
			$Page->setConfig('last', '尾页');
			$list = $model->table("caiji_video_config")->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
			$video_rank = $model->get_video_rank();
			$p_model = M('');
			$video_rank_c = $p_model->table('pu_config')->where(array('config_type'=>'COOP_C_VIDEO_RANK','status'=>1))->find();
			array_unshift($this->show_arr,"status_show","number_show");
			$pkg = array();
			foreach($list as $k=>$v){
				$show_column = $model->get_show_column(array('video_id'=>$v['id']));
				$v['show_column'] = '';
				for($i=0;$i<count($this->show_arr);$i++){
					if($show_column[$this->show_arr[$i]]==1){
						$v['show_column'] .= $this->show_name_arr[$i]."<br>";
					}
				}
				if($v['package']) $pkg[] = $v['package'];
				$list[$k] = $v;
			}
			list($soft_arr,$chl_soft_arr) =$this->pkg_info($pkg);
			$this->assign('c_video_rank',$video_rank_c['configcontent']);
			$this->assign('video_rank',$video_rank);
			$this->assign('list',$list);
			$this->assign('page', $Page->show());
			$this->assign('lr', $Page->rollPage);
			$this->assign('p', $_GET['p']);
			$this->assign('page', $Page->show());
			$this -> assign('soft_arr',$soft_arr);
			$this -> assign('chl_soft_arr',$chl_soft_arr);
			$this->display();
		}

		function pkg_info($pkg){
			$model = D('Cooperate.Contentcooperation');
			$config = $model -> return_config('coop_chl_id');
			$where = array(
					'status' => 1,
					'hide' => array('in',array(1,1024)),
					'package' => array('in',$pkg)
			);
			$soft_list = $model->table("sj_soft")->where($where)->order("version_code asc")->field("package,softid,softname,version,version_code,hide,channel_id")->select();
			$where = array(
					'package_status' => 1,
					'apk_name' => array('in',$pkg)
			);
			$file = get_table_data($where,"sj_soft_file","softid","softid,url");
			$soft_arr =  array();
			$chl_soft_arr =  array();
			foreach($soft_list as $key => $val){
				$channel_id = array_filter(explode(",",$val['channel_id']));
				if($val['hide'] == 1024 && in_array($config['configcontent'],$channel_id)){
					$chl_soft_arr[$val['package']] = $val;
					$chl_soft_arr[$val['package']]['url'] = $file[$val['softid']]['url'];
				}else if($val['hide'] == 1){
					$soft_arr[$val['package']] = $val;
					$soft_arr[$val['package']]['url'] = $file[$val['softid']]['url'];
				}
			}
			return array($soft_arr,$chl_soft_arr);
		}
		//编辑视频内容优先级
		function pub_edit_video_con_rank(){
			$model = M();
			if(isset($_POST['c_video_rank'])&&is_numeric($_POST['c_video_rank'])&&$_POST['c_video_rank']>0){
				$where = array('config_type'=>'COOP_C_VIDEO_RANK','status'=>1);
				$save_data = array(
						'configcontent' => $_POST['c_video_rank'],
						'uptime' => time()
				);
				$web_rank = $model->table('pu_config')->where(array('config_type'=>'COOP_C_WEB_RANK','status'=>1))->field('configcontent')->find();
				if($_POST['c_video_rank']==$web_rank['configcontent']){
					$this->error('视频内容优先级与资讯内容优先级不能相同');
				}
				$save_rank = $model->table('pu_config')->where($where)->save($save_data);
				if($save_rank){
					$this->writelog("搜索合作-视频：修改视频内容优先级为{$_POST['c_video_rank']}成功","pu_config",'config_type:COOP_C_WEB_RANK',__ACTION__ ,"","edit");
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			}else if(isset($_POST['c_video_rank'])){
				$this->error('优先级为空或不是大于0的数字');
			}
		}
		//编辑视频内容排序
		function edit_video_con_rank(){
			$model = M();
			if(isset($_POST['video_rank'])&&is_numeric($_POST['video_rank'])&&$_POST['video_rank']>0){
				$where = array('config_type'=>'COOP_VIDEO_RANK','status'=>1);
				$save_data = array(
						'configcontent' => $_POST['video_rank'],
						'uptime' => time()
				);
				$save_rank = $model->table('pu_config')->where($where)->save($save_data);
				if($save_rank){
					$this->writelog("搜索合作-视频：修改视频内容排序值为{$_POST['video_rank']}成功","pu_config",'config_type:COOP_VIDEO_RANK',__ACTION__ ,"","edit");
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			}else if(isset($_POST['video_rank'])){
				$this->error('排序值为空或不是大于0的数字');
			}

		}

		//编辑展示字段
		function edit_show_column(){
			$rank_arr = $this->rank_arr;
			$show_arr = $this->show_arr;
			if($_POST){
				$id = $_POST['video_id'];
				if(!$id) exit(json_encode(array('code'=>0,'msg'=>'缺少参数')));
				$rank = array();
				foreach($rank_arr as $v){
					if(!empty($_POST[$v])&&(!is_numeric($_POST[$v])||$_POST[$v]<0)){
						$result = array('code'=>0,'msg'=>'排序值为大于0的数字');
						exit(json_encode($result));
					}else if(empty($_POST[$v])){
						if($_POST[$v]=='0'){
							$result = array('code'=>0,'msg'=>'排序值不能为0');
						}else{
							$result = array('code'=>0,'msg'=>'排序值不能为空');
						}

						exit(json_encode($result));
					}else{
						$rank[] = $_POST[$v];
					}
				}
				if(count($rank) != count(array_unique($rank))){
					exit(json_encode(array('code'=>0,'msg'=>'排序值重复')));
				}
				$data = array_merge($rank_arr,$show_arr);
				$save_data = array();
				foreach($data as $v){
					if($_POST[$v]=='undefined') unset($_POST[$v]);
					$save_data[$v] = $_POST[$v];
				}
				$model = D('Sj.Coopwebset');
				$old_info = $model->table('caiji_video_show_column')->where(array('video_id'=>$id))->find();
				$res = $model->save_show_column($id,$save_data);
				if($res){
					$str = '';
					$this->show_name_arr = array('年代','地区','类型','主演');
					for($i=0;$i<count($this->show_name_arr);$i++){
						//var_dump($this->rank_arr[$i]."--".$_POST[$this->rank_arr[$i]]."--".$old_info[$this->rank_arr[$i]]);
						if(isset($_POST[$this->rank_arr[$i]])&&$_POST[$this->rank_arr[$i]]!=$old_info[$this->rank_arr[$i]]){
							$str .=",{$this->show_name_arr[$i]}排序值由{$old_info[$this->rank_arr[$i]]}修改为{$_POST[$this->rank_arr[$i]]}";
						}
						if(isset($_POST[$this->show_arr[$i]])&&$_POST[$this->show_arr[$i]]!=$old_info[$this->show_arr[$i]]){
							$str .=",{$this->show_name_arr[$i]}是否展示由{$old_info[$this->show_arr[$i]]}修改为{$_POST[$this->show_arr[$i]]}";
						}
					}
					//var_dump($str);
					if(!empty($str))
					$this->writelog("搜索合作-视频：修改id为{$id}的视频{$str}",'caiji_video_show_column',$id,__ACTION__ ,'','edit');
					exit(json_encode(array('code'=>1,'msg'=>'保存成功')));
				}else{
					exit(json_encode(array('code'=>0,'msg'=>'保存失败')));
				}
			}
			$this->assign('video_id',$_GET['id']);
			$model = D('Sj.Coopwebset');
			$show_column = $model->get_show_column(array('video_id'=>$_GET['id']));
			$this->assign('show_column',$show_column);
			$this->display();
		}
		//编辑弹窗文案
		function edit_coop_doc(){
			$model = D('Sj.Coopwebset');
			if($_POST){
				$id = $_POST['id'];
				if(!$id) exit(json_encode(array('code'=>0,'msg'=>'缺少参数')));
				$model = D('Sj.Coopwebset');
				$old_info = $model->table('caiji_video_config')->where(array("id"=>$id))->find();
				$save_data = array();
				if(isset($_POST['coop_doc'])) $save_data['coop_doc'] = $_POST['coop_doc'];
				if(isset($_POST['rank'])) $save_data['rank'] = $_POST['rank'];
				if(isset($_POST['show_count'])) $save_data['show_count'] = $_POST['show_count'];
				$res = $model->save_video_config($id,$save_data);
				if($res){
					$str = "";
					if(isset($_POST['coop_doc'])&&$_POST['coop_doc']!=$old_info['coop_doc']){
						$str .= ",弹窗文案由[{$old_info['coop_doc']}]修改为[{$_POST['coop_doc']}]";
					}
					if(isset($_POST['rank'])&&$_POST['rank']!=$old_info['rank']){
						$str .= ",优先级由[{$old_info['rank']}]修改为[{$_POST['rank']}]";
					}
					if(isset($_POST['show_count'])&&$_POST['show_count']!=$old_info['show_count']){
						$str .= ",展现次数由[{$old_info['show_count']}]修改为[{$_POST['show_count']}]";
					}
					if(!empty($str))
					$this->writelog("搜索合作-视频：修改id为{$id}的视频{$str}",'caiji_video_config',$id,__ACTION__ ,'','edit');
					exit(json_encode(array('code'=>1,'msg'=>'保存成功')));
				}else{
					exit(json_encode(array('code'=>0,'msg'=>'保存失败')));
				}
			}
			$find = $model->get_video_config(array('id'=>$_GET['id']));
			$type = !isset($_GET['type'])?'coop_doc':$_GET['type'];
			$this->assign('type',$type);
			$this->assign('find',$find);
			$this->assign('video_id',$_GET['id']);
			$this->display();
		}

		//编辑视频状态
		function edit_video_status(){
			$id = $_GET['id'];
			if(!$id) $this->error('缺少参数');
			$model = D('Sj.Coopwebset');
			$res = $model->save_video_config($id,array('status'=>$_GET['status']));
			if($res){
				if($_GET['status']==1){
					$str = '启用';
				}else{
					$str = '停用';
				}
				$this->writelog("搜索合作-视频：编辑视频状态为{$str}",'caiji_video_config',$id,__ACTION__ ,'','edit');
				$this->success('保存成功');
			}else{
				$this->error('保存失败');
			}
		}

		//编辑投放条件
		function edit_delivery(){
			//渠道
			$model = D('Sj.Coopwebset');
			$device_db=M();
			$channel_model = M('channel');
			$where['id']=$_GET['id'];
			// $where['status']=1;
			$sj_text_page_one=$model->table('caiji_video_config')->where($where)->find();
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
			// echo "<pre>";var_dump($chl);die;
			$this->assign('chl_list', $chl);
			$this->assign("list", $sj_text_page_one);
			$this->display();
		}

		//保存视频投放条件
		function save_video_delivery(){
			$model = D('Sj.Coopwebset');
			$map = array();
			$id = $_POST['id'];
			$where = array(
					'id' => $id,
			);
			$find = $model->get_video_config($where);

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
				$map['is_upload_csv'] = 0;
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
			$where = array('id' => $id);
			$log = $this->logcheck($where, 'caiji_video_config', $map, $model);
			$ret = $model->table('caiji_video_config')->where($where)->save($map);
			if ($ret || $ret === 0) {
				if ($ret) {
					$this->writelog("搜索合作-视频：编辑了id为{$id}的记录，{$log}",'caiji_video_config',$id,__ACTION__ ,'','edit');
				}
				$this->success("编辑成功！");
			} else {
				$this->error("编辑失败");
			}
		}

		//查看视频投放条件
		function show_video_delivery(){
			$model = D('Sj.Coopwebset');
			$id = $_GET['id'];
			$where = array(
					'id' => $id,
			);
			$result = $model->get_video_config($where);
			$list = $this->show_config($result);
			$this->assign('list',$list);
			$this->display('jztf_downset_show');
		}

		//视频审核列表
		function video_list(){
			$status = $_GET['status']?$_GET['status']:1;
			$model = D('Sj.Coopwebset');
			$where = array('a.status'=>$status);
			$total = $model->table("caiji_video_mess a")->where($where)->count();
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
			$param = http_build_query($_GET);
			import('@.ORG.Page2');
			$Page = new Page($total, $limit, $param);
			$Page->rollPage = 10;
			$Page->setConfig('header', '篇记录');
			$Page->setConfig('first', '首页');
			$Page->setConfig('last', '尾页');
			$list = $model->table("caiji_video_mess a")->join("caiji_video_config b on a.website_id=b.id")->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->field('a.*,b.website_name')->select();
			foreach($list as $k=>$v){
				if(!preg_match('/http:/',$v['video_pic'])){
					$v['video_pic'] = IMGATT_HOST.$v['video_pic'];
					$list[$k] = $v;
				}
			}
			$this->assign('list',$list);
			$this->assign('status',$status);
			$this->assign('page', $Page->show());
			$this->display();
		}

		//保存视频信息
		function save_video_info(){
			if($_POST['is_more']==1){
				$id = explode(',',$_POST['id']);
				array_pop($id);
			}else{
				$id = $_POST['id'];
			}
			if(!$id){
				exit(json_encode(array('code'=>0,'msg'=>'参数错误')));
			}
			$model = D('Sj.Coopwebset');
			$res = $model->save_video_info($id,$_POST);
			$status_arr = array('1'=>'撤销','2'=>'通过','3'=>'驳回');
			if(isset($_POST['status'])){
				$msg = $status_arr[$_POST['status']];
			}else{}
			if($res){
				if(is_array($id)){
					$this->writelog("搜索合作-视频审核:批量{$msg}了id为{$_POST['id']}的视频",'caiji_video_config',$_POST['id'],__ACTION__ ,'','edit');
				}else{
					$this->writelog("搜索合作-视频审核:{$msg}了id为{$_POST['id']}的视频",'caiji_video_config',$_POST['id'],__ACTION__ ,'','edit');
				}
				exit(json_encode(array('code'=>1,'msg'=>$msg."成功")));
			}else{
				exit(json_encode(array('code'=>0,'msg'=>$msg.'错误')));
			}
		}

		//编辑视频
		function  edit_video(){
			$model = D('Sj.Coopwebset');
			if($_POST){
				$id = $_POST['id'];
				unset($_POST['__hash__']);
				$data = $_POST;
				$video_pic = $_FILES['video_pic'];
				if ($video_pic['size']) {
					preg_match("/\.(?:png|jpg|jpeg)$/i", $video_pic['name'], $matches);
					if (!$matches) {
						$this->error("上传图片类型错误！");
					}
					if ($video_pic['size'] > 35 * 1024) {
						//$this->error("默认图片尺寸小于35K");
					}
					if (!$video_pic['name']) {
						$this->error("图片不能为空");
					}
					$file_img_path= UPLOAD_PATH.'/img/' . date("Ym/d/",time());
					if(!is_dir($file_img_path)){
						if(!mkdir(iconv("UTF-8", "GBK", $file_img_path),0777,true)){
							jsmsg("创建目录失败", -1);
						}
					}
					$config = array(
							'multi_config' => array(
									'video_pic' => array(
											'savepath' => $file_img_path,
											'saveRule' => 'getmsec'
									),
							),
					);
					$list = $this->_uploadapk(0, $config);
					$video_url = $list['image'][0]['url'];
					$data['video_pic'] = $video_url;
				}
				//var_dump($_POST);exit();
				$old_info = $model->table('caiji_video_mess')->where(array("id"=>$id))->find();
				$key_arr = array('video_name','show_name','update_sta','number','video_year','video_area','video_type','video_actor');
				$column_arr = array('video_name'=>'视频名称','show_name'=>'展示名称','update_sta'=>'状态','number'=>'集数','video_year'=>'年代','video_area'=>'地区','video_type'=>'类型','video_actor'=>'主演');
				$res = $model->save_video_info($id,$data);
				if($res){
					$str = '';
					for($i=0;$i<count($key_arr);$i++){
						if(isset($_POST[$key_arr[$i]])&&$_POST[$key_arr[$i]]!=$old_info[$key_arr[$i]]){
							$str .=",{$column_arr[$key_arr[$i]]}由[{$old_info[$key_arr[$i]]}]编辑成[{$_POST[$key_arr[$i]]}]";
						}
					}
					if(!empty($str))
					$this->writelog("搜索合作-视频审核:编辑了id为{$id}的视频{$str}",'caiji_video_config',$id,__ACTION__ ,'','edit');
					$this->success('编辑成功');
				}else{
					$this->error('编辑失败');
				}
			}
			$video = $model->table('caiji_video_mess')->where("id  = {$_GET['id']}")->find();
			$this->assign('video',$video);
			$this->assign('id',$_GET['id']);
			$this->display();
		}
	}


?>
