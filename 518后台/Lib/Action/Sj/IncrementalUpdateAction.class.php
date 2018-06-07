<?php
class IncrementalUpdateAction extends CommonAction {
	/*
	 *
	 * add increment_update_whitelist
	 */
	function incremental_update_whitelist()
	{
		$white_list_model = new Model("incrementalupdate_white_list");
		import('@.ORG.Page');
		$where = isset($_GET['package_name'])&&!empty($_GET['package_name'])?"package_name='".$_GET['package_name']."'":"1=1";
		$count = $white_list_model->where($where)->count();
		$Page = new Page($count, 20);
		$white_list_lists = $white_list_model ->where($where)->order("id")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign("soft_list",$white_list_lists);
		$this->assign("page",$Page->show());
		$this->display("incremental_update_whitelist");
	}

	/*
	 *processs incremental_update_whitelist
	 */
	function  incremental_update_whitelist_process()
	{
		$post_paramter  = $_POST;
		if(empty($post_paramter))
		{
			return false;
		}
		$array = array("soft_name","package_name","version","version_code");
		//var_dump($post_paramter);
		$white_list_paramter = array();
		foreach($post_paramter as $key =>$val)
		{
			if(in_array($key,$array))
			{
				$white_list_paramter[$key] = $post_paramter[$key];
			}
		}
		//var_dump($white_list_paramter);
		$white_list_paramter['create_time'] = time();
		$model = new Model("incrementalupdate_white_list");
		$where = "package_name='".$white_list_paramter['package_name']."'";
		$exist_info = $model->where($where)->select();
		if(!$exist_info){
			$flag = $model->add($white_list_paramter);
			if($flag)
			{
				$this -> writelog("添加白名单软件，id为".$flag.",软件名为".$white_list_paramter['soft_name'].",软件包名为".$white_list_paramter['package_name']."",'sj_incrementalupdate_white_list',$flag,__ACTION__ ,"","add");
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->success("加入白名单成功！");
			}else {
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error("加入白名单失败！");
			}
		}else{
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			$this->error("加入白名单失败,名单中已存在！");
		}

	}
	function  incremental_update_list()
	{
		$incremental_update_model = new Model("soft_patch");
		$sj_soft = new Model("soft");
		$sj_soft_file = new Model("soft_file");
		import('@.ORG.Page');
		ini_set("memory_limit", "4096M");
		$package = $_GET['package'];
		$soft_name = $_GET['soft_name'];
		$where = "1=1";
		$where .= isset($_GET['package'])&&!empty($_GET['package'])?' AND package  like  "%'.$package.'%"':'';
		//$where .= isset($_GET['soft_name'])&&!empty($_GET['soft_name'])? 'AND soft_name like "%'.$soft_name.'%"':'';
		$where .= isset($_GET['status'])? " AND status='".$_GET['status']."'":'';
		$where .= isset($_GET['date0']) && !empty($_GET['date0'])? "  AND create_at>=".strtotime($_GET['date0']." 00:00:00"):'';
		$where .= isset($_GET['date1']) && !empty($_GET['date1'])?  "  AND create_at<=".strtotime($_GET['date1']." 23:59:59"):'';
		$count = $incremental_update_model->where($where)->count();
		$Page = new Page($count, 20);
		$soft_lists = $incremental_update_model ->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$package_list = array();
		foreach($soft_lists as $key =>$val)
		{
			$new_version_package = $sj_soft->where("softid=".$val['new_softid'])->select();
			$old_version_package = $sj_soft->where("softid=".$val['old_softid'])->select();
			$val['softname'] = $new_version_package[0]['softname'];
			$val['new_version_code'] = $new_version_package[0]['version_code'];
			$val['old_version_code'] = $old_version_package[0]['version_code'];
			$soft_lists[$key] = $val;
		}
		$temp_soft_lists = $incremental_update_model ->where($where)->order("id desc")->select();
		foreach($temp_soft_lists as $k=>$v)
		{
			$package_list[$v['package']] = $v['softid'];

		}
		
		$packages_size = count($package_list);
		//过滤
		//$this->gpcFilter($soft_lists);
		$packages_size = isset($_GET['package'])?$count:$packages_size;
		$this->assign("package",$_GET['package']);
		$this->assign("soft_name",$_GET['soft_name']);
		$this->assign("status",$_GET['status']);
		$this->assign("start_time",$_GET['date0']);
		$this->assign("end_time",$_GET['date1']);
		$this->assign("soft_list",$soft_lists);
		$this->assign("soft_num",$packages_size);
		$this->assign("page",$Page->show());
		$this->display("incremental_update_list");
	}

	function  incremental_update_process()
	{
		$id = intval($_GET['id']);
		$incremental_update_model = new Model("soft_patch");
		$where = "id='".$id."'";
		$status = $_GET['status'];
		if(!empty($id))
		{
			$list_result = $incremental_update_model->where($where)->select();
			if(!empty($list_result))
			{
				$permit['status'] = $status;
				$flag = $incremental_update_model->where($where)->save($permit);
				if($flag)
				{
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->success("审核成功！");
				}else{
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("操作参数错误！");
				}

			}else{
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error("参数错误！");
			}
		}

	}
	function incremental_update_delete()
	{
		$id = intval($_GET['id']);
		$incremental_update_model = new Model("incrementalupdate_white_list");
		$where = "id='".$id."'";
		if(!empty($id))
		{
			$package = $incremental_update_model->where($where)->find();
			$delete_result = $incremental_update_model->where($where)->delete();
			if($delete_result)
			{	
				$this -> writelog("删除白名单软件，软件id为".$id.",软件名为".$package['soft_name'].",软件包名为".$package['package_name']."",'sj_incrementalupdate_white_list',$id,__ACTION__ ,"","del");
				$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/incremental_update_whitelist');
				$this->success("删除白名单成功！");
			}else {
				$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/incremental_update_whitelist');
				$this->error("删除白名单失败！");
			}
		}
	}
	function incremental_rule_make()
	{
		$model = new Model("incremental_rule");
		$where = "1=1";
		$result = $model->where($where)->select();
		if($result){
			$rule_config = json_decode($result[0]['rule_config'],true);
			$this->assign("rule_config",$rule_config);
			$this->assign("download_num",$result[0]['download_num']);
			$this->assign("version_distance",$result[0]['version_distance']);
			$this->assign("auto_publish",$result[0]['auto_publish']);

		}
		$this->display("incremental_rule_make");


	}
	//渠道配置
	function  channel_config()
	{
		$sj_channel = new Model("channel");
		$where = "1=1";
		$channel_result = $sj_channel->where($where)->select();
		//var_dump($channel_result);
		$this->display("incremental_channel_config");
	}
	function update_incremental_rule(){
		$rule_start = $_POST['rule_start'];
		$rule_end = $_POST['rule_end'];
		$rule_rate = $_POST['rule_rate'];
		$model = new Model("incremental_rule");
		$where = "1=1";
		$add_rule = false;
		$result = $model->where($where)->select();
		if($result)
		{
			$rule_config = $result[0]['rule_config'];
			$rule_config = json_decode($rule_config,true);
			if(count($rule_config)>0)
			{
				foreach($rule_config as $key =>$val)
				{
					if($rule_start>=$val[0]&&$rule_end<=$val[1])
					{
						$val[0] = $rule_start;
						$val[1] = $rule_end;
						$val[2] = $rule_rate;
						$add_rule = true;
					}
					$rule_config[$key] = $val;
				}
				$rule_config[] = array($rule_start*1024*1024,$rule_end*1024*1024,$rule_rate);
			}else{
				$rule_config = array(array($rule_start*1024*1024,$rule_end*1024*1024,$rule_rate));
			}
			$result['rule_config'] = json_encode($rule_config);
			$res = $model->where("id=1")->save($result);
			if($res){
				$this->writelog("增量更新审核增加规则：{$res}",'sj_incremental_rule','1',__ACTION__ ,"","add");
				$add_rule = true;
			}else{
				$add_rule = false;
			}

		}
		if($add_rule){
			$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/incremental_rule_make');
			$this->success("添加成功！");
		}else{
			$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/incremental_rule_make');
			$this->error("添加失败！");
		}

			
	}
	function delete_incremental_rule(){
		$rule_start = $_POST['rule_start'];
		$rule_end = $_POST['rule_end'];
		$rule_rate = $_POST['rule_rate'];
		$model = new Model("incremental_rule");
		$where = "1=1";
		$delete_rule = false;
		$result = $model->where($where)->select();
		if($result)
		{
			$rule_config = $result[0]['rule_config'];
			$rule_config = json_decode($rule_config,true);
			if(count($rule_config)>=1)
			{
				foreach($rule_config as $key =>$val)
				{
					if($rule_start*1024*1024>=$val[0]&&$rule_end*1024*1024<=$val[1])
					{
						if(isset($_POST['button1'])){
							unset($rule_config[$key]);
							$delete_rule = true;
						}
						if(isset($_POST['button2'])){
							$val[0] = $rule_start*1024*1024;
							$val[1] = $rule_end*1024*1024;
							$val[2] = $rule_rate;
							$rule_config[$key] = $val;
							$update_rule = true;
						}
					}
					//var_dump($rule_config[$key]);
					//$rule_config[$key] = $val;

				}
				$result['rule_config'] = json_encode($rule_config);
				$res = $model->where("id=1")->save($result);
				if($res){
					$this->writelog("增量更新审核删除规则：{$result['id']}",'sj_incremental_rule',$result['id'],__ACTION__ ,"","del");
					$del_rule = true;
				}else{
					$del_rule = false;
				}

			}
		}
		if($delete_rule||$update_rule){
			$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/incremental_rule_make');
			if(isset($_POST['button1'])){
				$this->success("删除成功！");
			}elseif(isset($_POST['button2']))
			{
				$this->success("修改成功！");
			}
		}else{
			$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/incremental_rule_make');
			if(isset($_POST['button1'])){
				$this->error("删除失败！");
			}elseif(isset($_POST['button2'])){
				$this->error("修改失败！");
			}
		}
	}
	function update_incremental_download(){
		$download_num = $_POST['download_num'];
		$version_distance = $_POST['version_code'];
		$check_publish = $_POST['check'];
		$model = new Model("incremental_rule");
		$where = "1=1";
		$add_rule = false;
		$result = $model->where($where)->select();
		if($result[0])
		{
			if(!empty($download_num))$data['download_num'] = $download_num;
			if(!empty($version_distance)) $data['version_distance'] = $version_distance;
			$data['auto_publish'] = $check_publish;
			$log_result = $this->logcheck(array('id'=>1),'sj_incremental_rule',$data,$model);
			$res = $model->where("id=1")->save($data);
			if($res)
			{
				$this->writelog("增量更新审核编辑规则：{$log_result}",'sj_incremental_rule','1',__ACTION__ ,"","edit");
				$add_rule = true;
			}else{
				$add_rule = false;
			}
		}
		if($add_rule){
			$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/incremental_rule_make');
			$this->success("添加成功！");
		}else{
			$this->assign('jumpUrl','/index.php/Sj/IncrementalUpdate/Soft/incremental_rule_make');
			$this->error("添加失败！");
		}
	}
	function Incremental_channel_process()
	{
		$version_code = $_POST['version_code'];
		$channel = $_POST['cid'];
		$model = new Model("incremental_channel");

		if(!empty($channel))
		{
			$data['channel_cat_id'] =  explode(",",$channel);
			$data['channel_id'] = explode(",", $channel);
			$exists = $model->where("channel_id='".$data['channel_id'])->select();
			if(!$exists){
				$insert_flag = $model->add($data);
				$this->writelog("渠道新增：{$exists}",'sj_incremental_channel',$insert_flag,__ACTION__ ,"","add");
				if($insert_flag){
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->success("添加成功！");
				}else{
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error("添加失败！");
				}
			}else{
				$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
				$this->error("数据已经存在！");
			}
		}
	}
	
	function Incremental_export()
	{
		//var_dump($_GET);
		$logpath = "/data/att/permanent_log/unknown/";
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		//var_dump($type);
		if ($type == '') {
			$this->error('导出数据出错！');
		}
		$date = isset($_GET['date']) ? $_GET['date'] : '';
		if (empty($date)) {
			$this->error('日期不能为空！');
		}
		//var_dump($date);
		$date = date('Y-m-d', strtotime($date));
		switch ($type)
		{
			case 0:
				$file = $logpath . $date . "/incremental.log";
				$filename = "incremental.log";
				break;
			case 1:
				$file = $logpath . $date . "/incremental.csv";
				$filename = "incremental.csv";
				break;
			default:
				$this->error('导出数据出错！');
				break;
		}
		
		$content = file_get_contents($file);
		if (empty($content))
			$this->error('日志文件为空');
		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=$filename");
		header('Content-Type:APPLICATION/OCTET-STREAM');
		ob_start();
		echo $content;
		ob_end_flush();
	}
}