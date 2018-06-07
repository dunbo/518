<?php

class LadingmanageAction extends CommonAction{
	
	function ladingmanage_list(){

		$model = new Model();
		
		//前端间隔时间 展示
		$save_res=$model ->table('pu_config') -> where("config_type='SPACE_DAYS'") -> find();
		$old =$save_res['configcontent'];
		$this -> assign('days',$save_res['configcontent']);
			
		//6.4.6以前及以后前端样式
		$rs = $model->table('pu_config')->where("config_type='AKEY_STYLE'")->find();
		$old2 = $rs['configcontent'];
		$this->assign('res',$rs['configcontent']);

		//前端每页显示软件数配置
		$rs = $model->table('pu_config')->where("config_type='lading_page_soft_num'")->find();
		$old3 = $rs['configcontent'];
		$this->assign('page_soft_num',$rs['configcontent']);

		// //6.4.6及以后前端样式
		// $rs = $model->table('pu_config')->where("config_type='NEW_AKEY_STYLE'")->find();
		// $old3 = $rs['configcontent'];
		// $this->assign('new_res',$rs['configcontent']);

		if($this->isAjax())
		{
			if($_POST['akeystyle']){
				$akeystyle = $_POST['akeystyle'];
				$data['configcontent']=$akeystyle;
				$model->table('pu_config')->where("config_type='AKEY_STYLE'")->save($data);
				$this->writelog("运营位管理-一键装机运营-选择6.4.6以前及以后前端显示样式配置，从".$old2."改成了".$data['configcontent'],'pu_config',"AKEY_STYLE",__ACTION__ ,"","edit");
			}
			
			if($_POST['page_soft_num']){
				$page_soft_num = $_POST['page_soft_num'];
				$data['configcontent']=$page_soft_num;
				$model->table('pu_config')->where("config_type='lading_page_soft_num'")->save($data);
				$this->writelog("运营位管理-一键装机运营-前端每页显示软件数配置，从".$old3."改成了".$data['configcontent'],'pu_config',"lading_page_soft_num",__ACTION__ ,"","edit");
			}
			
			echo 1;exit(0);
		}

		if($_GET['cids']){
			$cid = $_GET['cids'];
		}else{
			$cid = 0;
		}
		 $util = D("Sj.Util");
		//////////////////////// 应该处理的GET参数
        // 添加平台默认为市场
        $pid = 1;//默认为1
        if ($_GET['pid']) {
            $pid = $_GET['pid'];
        }
        $this->assign('pid', $pid);
        $product_list = $util->getProducts($pid);
        $this->assign('product_list',$product_list);
        $where = array('status' => 1,'cid' => $cid);
        $where['pid'] = $pid;
	
		$count = $model -> table('sj_lading_category') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_lading_category')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('rank')->select();

		foreach($result as $key => $val){
			$where_soft['category_id'] = $val['id'];
			$where_soft['_string'] = "status = 1 and category_id = {$val['id']} and start_tm <= ".time()." and end_tm >= ".time()."";
			$soft_count = $model -> table('sj_lading_soft') -> where($where_soft) -> count();
	
			$val['soft_num'] = $soft_count;
			if($val['cid']){
				$my_chname_result = $model -> table('sj_channel') -> where(array('cid' => $val['cid'])) -> select();
				$val['chname'] = $my_chname_result[0]['chname'];
			}else{
				$val['chname'] = '通用';
			}
			if($val['device_did']){
				$device_did=explode(',', $val['device_did']);
				$device_data = $model -> table('pu_device') ->field('dname')-> where(array('did'=>array('in',$device_did))) -> select();
				$device_str='';
				foreach($device_data as $v){
					$device_str.=$v['dname'].',';
				}
				$val['device_str']=trim($device_str,',');
			}
			
			$result[$key] = $val;

		}
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		
		$channel_result = $model -> table('sj_lading_category') -> where(array('status' => 1)) -> select();
		if($cid){
			$my_channel_result = $model -> table('sj_channel') -> where(array('cid' => $cid)) -> select();
			$chname = $my_channel_result[0]['chname'];
		}else{
			$chname = "通用";
		}
		
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);

		$this -> assign('cid',$cid);
		$this -> assign('chname',$chname);
		$this -> assign('rank',$rank);

		$this -> assign('result',$result);
		$this -> display();
	}

	function lading_official_config(){
		$model = new Model();
		$result = $model -> table('pu_config') -> where(array('config_type' => 'LADING_OFFICIAL_CONFIG','status' => 1)) -> select();
		$my_result = json_decode($result[0]['configcontent'],true);
		$this -> assign('cid',$_GET['cid']);
		$this -> assign('my_result',$my_result);
		$this -> display();
	}
	
	function lading_official_config_add(){
		$model = new Model();
		$cid = $_GET['cid'];
		$been_have = $model -> table('pu_config') -> where(array('config_type' => 'LADING_OFFICIAL_CONFIG','status' => 1)) -> select();
		$title = $_GET['title'];
		$content = $_GET['content'];
		$wifi_warning = $_GET['wifi_warning'];
		if(!$title || !$content || !$wifi_warning){
			$this -> error("文档配置项均为必填项");
		}
		$data_arr = array(
			'title' => $_GET['title'],
			'content' => $_GET['content'],
			'wifi_warning' => $_GET['wifi_warning']
		);
		$need_data = json_encode($data_arr);
		if($been_have){
			$data = array(
				'configcontent' => $need_data,
				'uptime' => time()
			);
			$result = $model -> table('pu_config') -> where(array('config_type' => 'LADING_OFFICIAL_CONFIG','status' => 1)) -> save($data);
		}else{
			$data = array(
				'config_type' => 'LADING_OFFICIAL_CONFIG',
				'configname' => '一键装机文案配置',
				'configcontent' => $need_data,
				'status' => 1,
				'uptime' => time()
			);
			$result = $model -> table('pu_config') -> add($data);
		}

		if($result){
			$this -> writelog('已修改一键装机文案配置','pu_config',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Ladingmanage/ladingmanage_list/cids/'.$cid);
			$this -> success('编辑成功');
		}
	}

	function add_category_show(){
		$model = new Model();
		$result = $model -> table('sj_lading_category') -> where(array('status' => 1,'cid' => $_GET['cid'])) -> count();
		for($i=1;$i<=$result+1;$i++){
			$rank_result[] = $i;
		}
		$_GET['pid'] = $_GET['pid']?$_GET['pid']:1;
		$this -> assign('cid',$_GET['cid']);
		$this -> assign('pid',$_GET['pid']);
		$this -> assign('count',count($rank_result));
		$this -> assign('rank_result',$rank_result);
		$this -> display();
	}
	
	function add_category(){
		$model = new Model();
		$category_name = trim($_POST['category_name']);
		$cid = $_POST['cid'];
		$rank = $_POST['rank'];
		$top_bg_color = $_POST['top_bg_color'];
		$filter_been = $_POST['filter_been'];
		$bei_soft = isset($_POST['bei_soft']) ? $_POST['bei_soft'] : 0;
		$start_tm = $_POST['start_tm']?strtotime($_POST['start_tm']):'';
		$end_tm = $_POST['end_tm']?strtotime($_POST['end_tm']):'';
		$_POST['pid'] = $_POST['pid'] ? $_POST['pid'] : 1;
		if(!$start_tm) {
			$this -> error('开始时间不能为空');
		}
		if(!$end_tm) {
			$this -> error('结束时间不能为空');
		}
		if($end_tm < $start_tm){
			$this -> error('开始时间不能大于结束时间');
		}
		if(!$category_name){
			$this -> error('请输入分类名称');
		}
		$have_been = $model -> table('sj_lading_category') -> where(array('category_name' => $category_name,'status' => 1,'cid' => $cid,'pid'=>$_POST['pid'])) -> select();
		if($have_been){
			$this -> error("该分类在该渠道中已存在");
		}
		$where_need['_string'] = "rank >= {$rank} and status = 1 and cid = {$cid} and pid={$_POST['pid']}";
		$need_result = $model -> table('sj_lading_category') -> where($where_need) -> select();
		foreach($need_result as $key => $val){
			$update_data = array(
				'rank' => $val['rank'] + 1,
			);
			$update_result = $model -> table('sj_lading_category') -> where(array('id' => $val['id'])) -> save($update_data);
		}
		$data = array(
			'category_name' => $category_name,
			'rank' => $rank,
			'filter_been' => $filter_been,
			'bei_soft' => $bei_soft,
			'top_bg_color' => $top_bg_color,
			'cid' => $cid,
			'push_area' => trim($_POST['area_value']),
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$device_did_array=$_POST['did'];
        $dids = array_unique($device_did_array);
        if (count($dids) > 0) {
            $d= implode(',', $dids);
            $d = ",{$d},";
            $data['device_did'] = $d;
        }else{
        	$data['device_did'] ='';
        }

		if(empty($_FILES["top_image_url"]["name"]) && empty($_FILES["top_bg_image_url"]["name"]))
		{
			$this->error("请上传顶部图片");
		}
		
		$path=date("Ym/d/",time());
		$config=array();
		if($_FILES["top_image_url"]["name"]){
			$ext = strtolower(pathinfo($_FILES["top_image_url"]['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('png','jpg'))) {
				// $this->error("顶部图片：尺寸1256*706。支持JPG,PNG");
				$this->error("顶部图片：支持JPG,PNG");
			}
			$image_file = getimagesize(substr('@'.$_FILES["top_image_url"]['tmp_name'],1));
			if($image_file[0] != 270 || $image_file[1] != 270){
				$this->error("顶部图片：尺寸270x270。支持JPG,PNG");
			}
			$config['multi_config']['top_image_url']=array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						//'img_p_size' =>  1024*50,
					);
		}
		if($_FILES["top_bg_image_url"]["name"]){
			$ext = strtolower(pathinfo($_FILES["top_bg_image_url"]['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('png','jpg','gif'))) {
				// $this->error("顶部图片：尺寸1256*706。支持JPG,PNG");
				$this->error("顶部背景图片：支持JPG,PNG,GIF");
			}
			$image_file = getimagesize(substr('@'.$_FILES["top_bg_image_url"]['tmp_name'],1));
			if($image_file[0] != 1080 || $image_file[1] != 510){
				$this->error("顶部背景图片：尺寸1080x510。支持JPG,PNG,GIF");
			}
			$config['multi_config']['top_bg_image_url']=array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'enable_resize' => false,
						//'img_p_size' =>  1024*50,
					);
		}
		if($config){
			$lists=$this->_uploadapk(0, $config);
			foreach($lists['image'] as $val) {
				if ($val['post_name'] == 'top_image_url') {
					$data['top_image_url']= $val['url'];
				}else if($val['post_name'] == 'top_bg_image_url'){
					$data['top_bg_image_url']= $val['url'];
				}
			}
		}
		
		$data['pid'] = $_POST['pid'];
		

		$result = $model -> table('sj_lading_category') -> add($data);

		if($result){
			$this -> writelog("市场综合管理-一键装机管理已添加id为{$result}的分类名称为{$category_name}，排序为{$rank},渠道id为{$cid}",'sj_lading_category',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Ladingmanage/ladingmanage_list/cids/'.$cid);
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}


	function edit_category_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_lading_category') -> where(array('id' => $id)) -> select();
		$result_count = $model -> table('sj_lading_category') -> where(array('status' => 1,'cid'=>$result[0]['cid'])) -> count();
		for($i=1;$i<=$result_count;$i++){
			$rank_result[] = $i;
		}
		// if (strlen($sj_market_push_one['channel_id']) > 0) {
            $device_selected = explode(',', $result[0]['device_did']);
            $device_selected_ret = array();
            foreach ($device_selected as $ds) {
                if (empty($ds)) continue;
                $device_name = $model->table("pu_device")->where(array('did' => $ds))->field('did,dname')->select();
                $device_selected_ret[] = array('did' => $ds,'dname' => $device_name[0]['dname']);
            }
            $this->assign('device_selected', $device_selected_ret);

        // }
        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
        if($result[0]['push_area']){
        	$area_list=explode(';',$result[0]['push_area']);
        }else{
        	$area_list='';
        }
        
        $this->assign("push_area",$area_list);
        // var_dump($area_list);
		$this -> assign('rank_result',$rank_result);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_category(){
		$model = new Model();
		$id = $_POST['id'];
		$category_name = trim($_POST['category_name']);
		$rank = $_POST['rank'];
		$top_bg_color = $_POST['top_bg_color'];
		$start_tm = $_POST['start_tm']?strtotime($_POST['start_tm']):'';
		$end_tm = $_POST['end_tm']?strtotime($_POST['end_tm']):'';
		$_POST['pid'] = $_POST['pid'] ?$_POST['pid'] :1;
		if(!$start_tm) {
			$this -> error('开始时间不能为空');
		}
		if(!$end_tm) {
			$this -> error('结束时间不能为空');
		}
		if($end_tm < $start_tm){
			$this -> error('开始时间不能大于结束时间');
		}
		if(!$category_name){
			$this -> error('请输入分类名称');
		}
		$filter_been = $_POST['filter_been'];
		$my_cid = $model -> table('sj_lading_category') -> where(array('id' => $id)) -> select();
		$my_result = $model -> table('sj_lading_category') -> where(array('id' => $id,'cid' => $my_cid[0]['cid'])) -> select();
		
		if(!$category_name){
			$this -> error('请输入分类名称');
		}
		$where_have['_string'] = "category_name = '{$category_name}' and status = 1 and id != {$id} and cid = {$my_cid[0]['cid']} and pid={$my_cid[0]['pid']}";
		$have_been = $model -> table('sj_lading_category') -> where($where_have) -> select();

		if($have_been){
			$this -> error("该分类在该渠道中已存在");
		}
		if($rank != $my_result[0]['rank']){
			if($rank > $my_result[0]['rank']){
				$need_where['_string'] = "rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and cid = {$my_cid[0]['cid']} and pid={$my_cid[0]['pid']}";
				$need_result = $model -> table('sj_lading_category') -> where($need_where) -> select();			
				foreach($need_result as $key => $val){
					$update_data = array(
						'rank' => $val['rank'] - 1
					);
					$update_result = $model -> table('sj_lading_category') -> where(array('id' => $val['id'])) -> save($update_data);
				}
			}else{
				$need_where['_string'] = "rank < {$my_result[0]['rank']} and rank >= {$rank} and status = 1 and cid = {$my_cid[0]['cid']} and pid={$my_cid[0]['pid']}";
				$need_result = $model -> table('sj_lading_category') -> where($need_where) -> select();
				foreach($need_result as $key => $val){
					$update_data = array(
						'rank' => $val['rank'] + 1
					);
					$update_result = $model -> table('sj_lading_category') -> where(array('id' => $val['id'])) -> save($update_data);
				}
			}
		}
		$data = array(
			'category_name' => $category_name,
			'rank' => $rank,
			'filter_been' => $filter_been,
			'top_bg_color' => $top_bg_color,
			'push_area' => trim($_POST['area_value']),
			'update_tm' => time(),
			'start_tm'	=>	$start_tm,
			'end_tm'	=>	$end_tm,
		);
		$device_did_array=$_POST['did'];
        $dids = array_unique($device_did_array);
        if (count($dids) > 0) {
            $d= implode(',', $dids);
            $d = ",{$d},";
            $data['device_did'] = $d;
        }else{
        	$data['device_did'] ='';
        }
		if((empty($_FILES["top_image_url"]["name"]) && !$my_cid[0]['top_image_url'])&&(empty($_FILES["top_bg_image_url"]["name"]) && !$my_cid[0]['top_bg_image_url']))
		{
			$this->error("请上传顶部图片");
		}
		$path=date("Ym/d/",time());
		$config=array();
		if($_FILES["top_image_url"]["name"]){
			// $image_file = getimagesize(substr('@'.$_FILES["top_image_url"]['tmp_name'],1));
			// if($image_file[0] != 1256 || $image_file[1] != 706){
			// 	$this->error("顶部图片：尺寸1256*706。支持JPG,PNG");
			// }
			$ext = strtolower(pathinfo($_FILES["top_image_url"]['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('png','jpg'))) {
				// $this->error("顶部图片：尺寸1256*706。支持JPG,PNG");
				$this->error("顶部图片：支持JPG,PNG");
			}
			$image_file = getimagesize(substr('@'.$_FILES["top_image_url"]['tmp_name'],1));
			if($image_file[0] != 270 || $image_file[1] != 270){
				$this->error("顶部图片：尺寸270x270。支持JPG,PNG");
			}
			$config['multi_config']['top_image_url']=array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							// 'img_p_size' =>  480*800,
						);
		}
		if($_FILES["top_bg_image_url"]["name"]){
			// $image_file = getimagesize(substr('@'.$_FILES["top_image_url"]['tmp_name'],1));
			// if($image_file[0] != 1256 || $image_file[1] != 706){
			// 	$this->error("顶部图片：尺寸1256*706。支持JPG,PNG");
			// }
			$ext = strtolower(pathinfo($_FILES["top_bg_image_url"]['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('png','jpg','gif'))) {
				// $this->error("顶部图片：尺寸1256*706。支持JPG,PNG");
				$this->error("顶部背景图片：支持JPG,PNG，GIF");
			}
			$image_file = getimagesize(substr('@'.$_FILES["top_bg_image_url"]['tmp_name'],1));
			if($image_file[0] != 1080 || $image_file[1] != 510){
				$this->error("顶部背景图片：尺寸1080x510。支持JPG,PNG,GIF");
			}
			$config['multi_config']['top_bg_image_url']=array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							'enable_resize' => false,
							// 'img_p_size' =>  480*800,
						);
			
		}
		if($config){
			$lists=$this->_uploadapk(0, $config);
			foreach($lists['image'] as $val) {
				if ($val['post_name'] == 'top_image_url') {
					$data['top_image_url']= $val['url'];
				}else if($val['post_name'] == 'top_bg_image_url'){
					$data['top_bg_image_url']= $val['url'];
				}
			}
		}

		$log_result = $this -> logcheck(array('id' => $id),'sj_lading_category',$data,$model);
		
		$result = $model -> table('sj_lading_category') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("市场综合管理-一键装机管理已编辑渠道id为{$my_cid[0]['cid']},id为{$id}的分类".$log_result,'sj_lading_category',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Ladingmanage/ladingmanage_list/cids/'.$my_cid[0]['cid']);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	function delete_category(){
		$model = new Model();
		$id = $_GET['id'];
		$my_result = $model -> table('sj_lading_category') -> where(array('id' => $id)) -> select();
		$result_count = $model -> table('sj_lading_category') -> where(array('status' => 1,'cid' => $my_result[0]['cid'])) -> count();
		if($result_count <= 1){
			$this -> error("至少保留1个分类");
		}
		$soft_result = $model -> table('sj_lading_soft') -> where(array('category_id' => $id,'status' => 1)) -> select();
		foreach($soft_result as $key => $val){
			$update_where['id'] = $val['id'];
			$update_data = array(
				'status' => 0
			);
			$update_result = $model -> table('sj_lading_soft') -> where($update_where) -> save($update_data);
		}
		$been_result = $model -> table('sj_lading_category') -> where(array('status' => 1,'id' => $id)) -> select();
		$where_rank['_string'] = "rank > {$been_result[0]['rank']} and cid = {$my_result[0]['cid']}";
		$rank_result = $model -> table('sj_lading_category') -> where($where_rank) -> select();
		foreach($rank_result as $key => $val){
			$where_need['id'] = $val['id'];
			$data_need = array(
				'rank' => $val['rank'] - 1
			);
			$need_result = $model -> table('sj_lading_category') -> where($where_need) -> save($data_need); 
		}
		
		$data = array(
			'status' => 0,
			'update_tm' => time(),
		);
		$result = $model -> table('sj_lading_category') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("市场综合管理-一键装机管理已删除渠道id为{$my_result[0]['cid']},id为{$id}的一键装机类别",'sj_lading_category',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Ladingmanage/ladingmanage_list/cids/'.$my_result[0]['cid']);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	
	
	}
	
	function show_soft_list(){
		$model = new Model();
		$id = $_GET['id'];
		if(!$_GET['time_status'] || $_GET['time_status'] == 1){
			$where_soft['_string'] = "category_id = {$id} and status = 1 and start_tm <= ".time()." and end_tm >= ".time()."";
			$soft_result = $model -> table('sj_lading_soft') -> where($where_soft) -> order('rank asc,end_tm asc') -> select();
		}elseif($_GET['time_status'] == 2){
			$where_soft['_string'] = "category_id = {$id} and status = 1 and end_tm < ".time()."";
			$soft_result = $model -> table('sj_lading_soft') -> where($where_soft) -> order('rank asc,end_tm asc') -> select();
		}elseif($_GET['time_status'] == 3){
			$where_soft['_string'] = "category_id = {$id} and status = 1 and start_tm > ".time()."";
			$soft_result = $model -> table('sj_lading_soft') -> where($where_soft) -> order('rank asc,end_tm asc') -> select();
		}
		//获取合作样式列表
		$util = D("Sj.Util"); 
		foreach($soft_result as $key => $val) {
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$soft_result[$key]['types'] = $v[0];
				}
			}
		}
		
		$category_result = $model -> table('sj_lading_category') -> where(array('id' => $id)) -> select();
		$category_select = array();
		//同一渠道下 分类可以随便移动
		$category = $model-> table('sj_lading_category') -> where (array('status' => 1,'cid' => $category_result[0]['cid'])) -> select();
		foreach($category as $v) 
		{
			$category_select[$v['id']] = $v['category_name'];	
		}
		$this -> assign('cid',$category_result[0]['cid']);
		$this -> assign('category_result',$category_result);
		$this -> assign('time_status',$_GET['time_status']);
		$this -> assign('category_id',$id);
		$this -> assign('category_select',$category_select);
		$this -> assign('soft_result',$soft_result);
		$this -> display();
	}

	function add_soft_show(){
		$category_id = $_GET['category_id'];
		$this -> assign('cid',$_GET['cid']);
		$this -> assign('category_id',$category_id);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);
		$this -> display();
	}

	function add_soft()
	{
		$model = new Model();
		$id = $_POST['category_id'];
		
		$softname =  trim($_POST['softname']);
		$package = trim($_POST['package']);
		$recommend = trim($_POST['recommend']);
		$start_tm = strtotime($_POST['start_tm']);
		$end_tm = strtotime($_POST['end_tm']);
        $update_tm = strtotime("now");
		if(isset($_POST['type'])){
			$type = $_POST['type'];
		}else{
			$type = 0;
		}
		$beid = isset($_POST['beid']) ? trim($_POST['beid']) : 0; 
        
	    // tpl（网页）里的名称和数据库字段对应数组
		$column_convert_arr = array(
			'package' => 'package',
			'recommend' => 'recommend',
			'start_tm' => 'start_tm',
			'end_tm' => 'end_tm',
			'category_id' =>'category_id',
			'type' =>'type',
			'beid' => 'beid',
		);
		$check_column_arr = array();
		foreach($column_convert_arr as $key=>$value) 
		{
			if (array_key_exists($key, $_POST)) 
			{
				$check_column_arr[$value] = trim($_POST[$key]);
			}
		}
		// trim一下
		foreach($check_column_arr as $key=>$value) 
		{
			$check_column_arr[$key] = trim($value);
		}
		// 调用通用的检查函数
		$content_arr = array();
		$content_arr[0] = $check_column_arr;
		$error_msg = $this->logic_check($content_arr);
		$qualified_flag = true;
		foreach($error_msg as $key=>$value) 
		{
			if ($value['flag'] == 1)
				$qualified_flag = false;
		}
		if (!$qualified_flag) 
		{
			$msg = $error_msg[0]['msg'];
			$this->error($msg);
		}
		$data = array(
			'category_id' => $id,
			'softname' =>$softname,
			'package' => $package,
			'recommend' => $recommend,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'create_tm' => time(),
			'update_tm' => $update_tm,
			'status' => 1,
			'type' =>$type,
			'beid' =>$beid,
			'is_check' =>$_POST['is_check'],
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		//屏蔽软件上排期时报警需求 新增  yuesai
        $AdSearch = D("Sj.AdSearch");
        $shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm']);
        if($shield_error){
            $this -> error($shield_error);
        }
		$result = $model -> table('sj_lading_soft') -> add($data);
		if($start_tm > time()){
			$the_status = 3;
		}elseif($start_tm < time() and $end_tm > time()){
			$the_status = 1;
		}elseif($end_tm < time()){
			$the_status = 2;
		}
		if($result)
		{
			$this -> writelog("市场综合管理-一键装机管理已添加软件{$softname}到渠道id为{$result},软件类别id为{$id},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_lading_soft',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/show_soft_list/time_status/{$the_status}/id/{$id}");
			$this -> success("添加成功");
		}
		else
		{
			$this -> error("添加失败");
		}
	}

	function edit_soft_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_lading_soft') -> where(array('id' => $id)) -> select();
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($result[0]['type']);
		$this->assign('typelist',$typelist);

		$this -> assign('cid',$_GET['cid']);
		$this -> assign('result',$result);
		$this -> display();
	
	}
	
	function edit_soft()
	{
		$model = new Model();
		$id = $_GET['id'];
		$category_id =$_GET['category_id'];
		
		$softname = trim($_GET['softname']);
		$package = trim($_GET['package']);
		$recommend = $_GET['recommend'];
		$start_tm = strtotime($_GET['start_tm']);
		$end_tm = strtotime($_GET['end_tm']);
        $update_tm = time();
		if(isset($_GET['type'])){
			$type = $_GET['type'];
		}else{
			$type = 0;
		}

		$beid = isset($_GET['beid']) ? trim($_GET['beid']) : 0;
		
		// tpl（网页）里的名称和数据库字段对应数组
		$column_convert_arr = array(
			'package' => 'package',
			'recommend' => 'recommend',
			'start_tm' => 'start_tm',
			'end_tm' => 'end_tm',
			'category_id' =>'category_id',
			'id' => 'id',
			'type' =>'type',
			'beid' => 'beid',
		);
		$check_column_arr = array();
		foreach($column_convert_arr as $key=>$value) 
		{
			if (array_key_exists($key, $_GET)) 
			{
				$check_column_arr[$value] = trim($_GET[$key]);
			}
		}
		// trim一下
		foreach($check_column_arr as $key=>$value) 
		{
			$check_column_arr[$key] = trim($value);
		}
		// 调用通用的检查函数
		$content_arr = array();
		$content_arr[0] = $check_column_arr;
		$error_msg = $this->logic_check($content_arr);
		$qualified_flag = true;
		foreach($error_msg as $key=>$value) 
		{
			if ($value['flag'] == 1)
				$qualified_flag = false;
		}
		if (!$qualified_flag) 
		{
			$msg = $error_msg[0]['msg'];
			$this->error($msg);
		}
		$data = array(
			'category_id' => $category_id,
			'softname' =>$softname,
			'package' => $package,
			'recommend' => $recommend,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'create_tm' => time(),
			'update_tm' => $update_tm,
			'status' => 1,
			'type' =>$type,
			'beid' => $beid,
			'is_check' =>$_GET['is_check'],
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		// var_dump($data);
		//屏蔽软件上排期时报警需求 新增  yuesai
        $AdSearch = D("Sj.AdSearch");
        $shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm']);
        if($shield_error){
            $this -> error($shield_error);
        }
		$log_result = $this -> logcheck(array('id' => $id),'sj_lading_soft',$data,$model);
		$result = $model -> table('sj_lading_soft')->where(array('id'=>$id)) -> save($data);
		// echo $model ->getlastsql();die;
		$category_result = $model -> table('sj_lading_soft') -> where(array('id' => $id)) -> select();
		if($start_tm > time()){
			$the_status = 3;
		}elseif($start_tm < time() and $end_tm > time()){
			$the_status = 1;
		}elseif($end_tm < time()){
			$the_status = 2;
		}
		if($result){
			$this -> writelog("市场综合管理-一键装机管理已编辑id为{$id}的软件".$log_result,'sj_lading_soft',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/show_soft_list/time_status/{$the_status}/id/{$category_result[0]['category_id']}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	
	function delete_soft(){
		$model = new Model();
		$id = $_GET['id'];
		$data = array(
			'status' => 0,
			'update_tm' => time(),
		);
		$result = $model -> table('sj_lading_soft') -> where(array('id' => $id)) -> save($data);
		$category_result = $model -> table('sj_lading_soft') -> where(array('id' => $id)) -> select();
		$cid_result = $model -> table('sj_lading_category') -> where(array('id' => $category_result[0]['category_id'])) -> select();
		if($result){
			$this -> writelog("市场综合管理-一键装机管理已删除id为{$id}的类别软件,类别id为{$category_result[0]['category_id']},类别渠道id为{$cid_result[0]['cid']}",'sj_lading_soft',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/show_soft_list/id/{$category_result[0]['category_id']}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function batch_del_soft(){
		$model = new Model();
		$ids = $_GET['ids'];
		$category_id = $_GET['cid'];
		if(!$ids || !$category_id) {
			$this->error('参数有误');
		}
		$time = time();
		$sql = "update sj_lading_soft set status = 0, update_tm = {$time} where id in ({$ids}) ";
		$ret = $model->execute($sql);
		if($ret){
			$this -> writelog("市场综合管理-一键装机管理已删除id为{$ids}的类别软件,类别id为{$category_id}",'sj_lading_soft',$ids,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/show_soft_list/id/{$category_id}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function batch_edit_soft(){
		$model	=	new Model();
		$ids	=	$_POST['ids'];
		if(!$ids) {
			exit(json_encode(array('code'=>0,'msg'=>'参数有误')));
		}
		$id_arr		=	explode(',', $ids);
		$start_tm	=	$_POST['start_tm'];
		$end_tm		=	$_POST['end_tm'];
// 		if(!$start_tm) {
// 			exit(json_encode(array('code'=>0,'msg'=>'开始时间不能为空')));
// 		}
// 		if(!$end_tm) {
// 			exit(json_encode(array('code'=>0,'msg'=>'结束时间不能为空')));
// 		}
// 		if($end_tm < $start_tm){
// 			exit(json_encode(array('code'=>0,'msg'=>'开始时间不能大于结束时间')));
// 		}
		if(!$start_tm && !$end_tm) {
			exit(json_encode(array('code'=>0,'msg'=>'未填写项不进行修改')));
		}
		$msg = "";
		$success = $fail = 0;
		foreach($id_arr as $k => $v) {
			$soft_info = $model -> table('sj_lading_soft') -> where(array('id' => $v)) -> find();
			$id				=	$soft_info['id'];
			$category_id	=	$soft_info['category_id'];
			$softname		=	$soft_info['softname'];
			$package		=	$soft_info['package'];
			$recommend		=	$soft_info['recommend'];
			$type			=	$soft_info['type'];
			$beid			=	$soft_info['beid'];
			$is_check		=	$soft_info['is_check'];
			$update_tm		=	time();
			
			if(!$start_tm) {
				$start_tm = date("Y-m-d H:i:s", $soft_info['start_tm']);
			}
			if(!$end_tm) {
				$end_tm = date("Y-m-d H:i:s", $soft_info['end_tm']);
			}
			
			$hostry_start_tm	=	date("Y-m-d H:i:s", $soft_info['start_tm']);
			$hostry_end_tm		=	date("Y-m-d H:i:s", $soft_info['end_tm']);
			$check_column_arr = array(
					'package'		=>	$package,
					'recommend'		=>	$recommend,
					'start_tm'		=>	$start_tm,
					'end_tm'		=>	$end_tm,
					'category_id'	=>	$category_id,
					'id'			=>	$id,
					'type'			=>	$type,
					'beid'			=>	$beid,
			);
			// 调用通用的检查函数
			$content_arr = array();
			$content_arr[0] = $check_column_arr;
			$error_msg = $this->logic_check($content_arr);
			$qualified_flag = true;
			foreach($error_msg as $key=>$value)
			{
				if ($value['flag'] == 1)
					$qualified_flag = false;
			}
			if (!$qualified_flag)
			{
				$msg .= $error_msg[0]['msg'];
				$fail ++;
				continue;
			}
			$data = array(
					'category_id'	=>	$category_id,
					'softname'		=>	$softname,
					'package'		=>	$package,
					'recommend'		=>	$recommend,
					'start_tm'		=>	strtotime($start_tm),
					'end_tm'		=>	strtotime($end_tm),
					'create_tm'		=>	time(),
					'update_tm'		=>	$update_tm,
					'status'		=>	1,
					'type'			=>	$type,
					'beid'			=>	$beid,
					'is_check'		=>	$is_check,
					'admin_id'		=>	$_SESSION['admin']['admin_id'],
			);
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
				$msg .= $shield_error;
				$fail ++;
				continue;
			}
			$result = $model -> table('sj_lading_soft')->where(array('id'=>$id)) -> save($data);
			if($result) {
				$this -> writelog("市场综合管理-一键装机管理已编辑id为{$id}的软件的开始时间和结束时间由".$hostry_start_tm."/".$hostry_end_tm."改为".$start_tm."/".$end_tm,'sj_lading_soft',$id,__ACTION__ ,"","edit");
				$success ++;
			}else {
				$fail ++;
			}
		}
		if(!$fail) {
			exit(json_encode(array('code'=>1,'success'=>$success,'fail'=>$fail,'msg'=>$msg)));
		}else{
			exit(json_encode(array('code'=>2,'success'=>$success,'fail'=>$fail,'msg'=>$msg)));
		}
		
	}
	
	function change_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$cid = $_GET['cid'];
// 		$my_result = $model -> table('sj_lading_category') -> where(array('id' => $id,'cid' => $cid)) -> select();
// 		if($rank > $my_result[0]['rank']){
// 			$need_where['_string'] = "rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and cid = {$cid}";
// 			$need_result = $model -> table('sj_lading_category') -> where($need_where) -> select();
// 			foreach($need_result as $key => $val){
// 				$update_data = array(
// 					'rank' => $val['rank'] - 1
// 				);
// 				$update_result = $model -> table('sj_lading_category') -> where(array('id' => $val['id'])) -> save($update_data);
// 			}
// 		}else{
// 			$need_where['_string'] = "rank < {$my_result[0]['rank']} and rank >= {$rank} and status = 1 and cid = {$cid}";
// 			$need_result = $model -> table('sj_lading_category') -> where($need_where) -> select();
// 			foreach($need_result as $key => $val){
// 				$update_data = array(
// 					'rank' => $val['rank'] + 1
// 				);
// 				$update_result = $model -> table('sj_lading_category') -> where(array('id' => $val['id'])) -> save($update_data);
// 			}
// 		}
		
		$data = array(
			'rank' => $rank,
			'update_tm' => time()
		);
		$result = $model -> table('sj_lading_category') -> where(array('id' => $id)) -> save($data);
	
		if($result){
			$this -> writelog("已编辑一键装机id为{$id}的分类排序为{$rank}",'sj_lading_category',$id,__ACTION__ ,"","edit");
			echo json_encode(1);
			return json_encode(1);
		}
	}
	
	
	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}
	
	
	//备选库
	function bei_soft_list(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_lading_bei_soft') -> where(array('category_id' => $id,'status' => 1)) -> select();
		$this -> assign('id',$id);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_bei_soft_show(){
		$id = $_GET['id'];
		$this -> assign('category_id',$id);
		$this -> display();
	}
	
	function add_bei_soft(){
		$model = new Model();
		$category_id = $_GET['category_id'];
		$soft_name = trim($_GET['soft_name']);
		if(!$soft_name){
			$this -> error("请填写软件名称");
		}
		$have_been_soft = $model -> table('sj_lading_bei_soft') -> where(array('soft_name' => $soft_name,'status' => 1)) -> select();
		if($have_been_soft){
			$this -> error("该软件名称已存在于软件备选库");
		}
		$package = trim($_GET['package']);
		$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$have_package){
			$this -> error("请填写正确的软件包名");
		}
		$have_been_package = $model -> table('sj_lading_bei_soft') -> where(array('package' => $package,'status' => 1)) -> select();
		if($have_been_package){
			$this -> error("该软件包名已存在于软件备选库");
		}
		$recommend = $_GET['recommend'];
		if($this -> strlen_az($recommend) > 14 || $this -> strlen_az($recommend) == 0){
			$this -> error("请输入7个汉字以内的一句话推荐");
		}
		$recommend_have_where['_string'] = "recommend = '{$recommend}' and status = 1";
		$recommend_have_been = $model -> table('sj_lading_bei_soft') -> where($recommend_have_where) -> select();
		if($recommend_have_been){
			$this -> error("已存在该一句话推荐");
		}
		$show_probability = $_GET['show_probability'];
		if($show_probability > 100 || !$show_probability || !is_numeric($show_probability)){
			$this -> error("请填写不大于100的显示概率");
		}
		
		$data = array(
			'category_id' => $category_id,
			'soft_name' => $soft_name,
			'package' => $package,
			'show_probability' => $show_probability,
			'recommend' => $recommend,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$result = $model -> table('sj_lading_bei_soft') -> add($data);
		
		if($result){
			$this -> writelog("已添加一键装机分区id为{$category_id}的备选库软件id为{$result}",'sj_lading_bei_soft',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/bei_soft_list/id/{$category_id}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_bei_soft_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_lading_bei_soft') -> where(array('id' => $id)) -> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_bei_soft(){
		$model = new Model();
		$id = $_GET['id'];
		$soft_name = trim($_GET['soft_name']);
		$my_category = $model -> table('sj_lading_bei_soft') -> where(array('id' => $id)) -> select();
		if(!$soft_name){
			$this -> error("请填写软件名称");
		}
		$where_have_been_soft['_string'] = "soft_name = '{$soft_name}' and status = 1 and id != {$id}";
		$have_been_soft = $model -> table('sj_lading_bei_soft') -> where($where_have_been_soft) -> select();
		
		if($have_been_soft){
			$this -> error("该软件名称已存在于软件备选库");
		}
		$package = trim($_GET['package']);
		$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$have_package){
			$this -> error("请填写正确的软件包名");
		}
		$where_have_been_package['string'] = "package = '{$package}' and status = 1 and id != {$id}";
		$have_been_package = $model -> table('sj_lading_bei_soft') -> where($where_have_been_package) -> select();
		if($have_been_package){
			$this -> error("该软件包名已存在于软件备选库");
		}
		$recommend = $_GET['recommend'];
		if($this -> strlen_az($recommend) > 14 || $this -> strlen_az($recommend) == 0){
			$this -> error("请输入7个汉字以内的一句话推荐");
		}
		$recommend_have_where['_string'] = "recommend = '{$recommend}' and status = 1 and id != {$id}";
		$recommend_have_been = $model -> table('sj_lading_bei_soft') -> where($recommend_have_where) -> select();
		if($recommend_have_been){
			$this -> error("已存在该一句话推荐");
		}
		$show_probability = $_GET['show_probability'];
		if($show_probability > 100 || !$show_probability || !is_numeric($show_probability)){
			$this -> error("请填写不大于100的显示概率");
		}
		$data = array(
			'soft_name' => $soft_name,
			'package' => $package,
			'show_probability' => $show_probability,
			'recommend' => $recommend,
			'update_tm' => time(),
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_lading_bei_soft',$data,$model);
		$result = $model -> table('sj_lading_bei_soft') -> where(array('id' => $id)) -> save($data);
		
		if($result){
			$this -> writelog("已编辑id为{$id}的一键装机备选库软件".$log_result,'sj_lading_bei_soft',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/bei_soft_list/id/{$my_category[0]['category_id']}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	
	}
	
	function del_bei_soft(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_lading_bei_soft') -> where(array('id' => $id)) -> select();
		
		$result = $model -> table('sj_lading_bei_soft') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
		if($result){
			$this -> writelog("已删除一键装机分区id为{$been_result[0]['category_id']}的备选库软件,id为{$id}",'sj_lading_bei_soft',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/bei_soft_list/id/{$been_result[0]['category_id']}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	
	}
	
	function all_bei_soft(){
		$model = new Model();
		$cid = $_GET['cid'];
		$category_result = $model -> table('sj_lading_category') -> where(array('status' => 1,'cid' => $cid)) -> select();
		
		foreach($category_result as $key => $val){
			$category_str_go .= $val['id'].',';
		}
		$category_str = substr($category_str_go,0,-1);
		$where['_string'] = "end_tm >= ".time()." and status = 1 and category_id in ({$category_str})";
		$result = $model -> table('sj_lading_soft') -> where($where) -> select();
	
		foreach($result as $key => $val){
			$category_name = $model -> table('sj_lading_category') -> where(array('id' => $val['category_id'])) -> select();
			$val['category_name'] = $category_name[0]['category_name'];
			$result[$key] = $val;
		}
		$this -> assign('cid',$cid);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function update_rank(){
		$model = new Model();
		$id = $_POST['id'];
		$rank = $_POST['rank'];
		$cid = $_POST['cid'];
		foreach($rank as $key => $val){
			if(!$val){
				$this -> error("排序不能为0");
			}
			$log_result = $this -> logcheck(array('id' => $id[$key]),'sj_lading_soft',array('rank' => $val),$model);
			$result = $model -> table('sj_lading_soft') -> where(array('id' => $id[$key])) -> save(array('rank' => $val));
			if($result){
				$this -> writelog("已编辑id为{$id[$key]}的一键装机软件排序为{$val}",'sj_lading_soft',$id[$key],__ACTION__ ,"","edit");
			}
			$results[] = $result;
		}
		
		if($results){
			$this -> assign('jumpUrl','/index.php/Sj/Ladingmanage/all_bei_soft/cid/'.$cid);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
		
	}
        //修改专题排序
		function change_orders(){
			$model = new Model();
			$id = $_GET['id'];
			$rank = $_GET['rank'];
			$update_tm = strtotime("now");
                        $category_id = $_GET['category_id'];
			if(!$rank){
				$rank = 0;
			}
			$log_result = $this->logcheck(array('id' => $id),'sj_lading_soft',array('rank' => $rank),$model);
			$result = $model -> table('sj_lading_soft') -> where(array('id' => $id)) -> save(array('rank' => $rank,'update_tm' => $update_tm));
                        $softname11 = $model -> table('sj_lading_soft') -> where(array('id' => $id)) ->select();
                        $softname111 = $softname11[0]['softname'];
                        $category_name11 = $model -> table('sj_lading_category') -> where(array('id' => $category_id)) -> select();
                        $category_name111 = $category_name11[0]['category_name'];
			if($result){
                                $this -> writelog("已修改分类{$category_name111}下的软件名为{$softname111}的排序,".$log_result,'sj_lading_soft',$id,__ACTION__ ,"","edit");
				echo 1;
				return 1;
			}else{
				echo 2;
				return 2;
			}
		}
		
		    //检测该渠道下的软件个数
	   function  lading_check_soft_count()
	    {
			$model = new Model();
			if($_GET['cid'])
			{
				$cid = $_GET['cid'];
			}
			else
			{
				$cid = 0;
			}
			$result=$model->table('sj_lading_category category,sj_lading_soft soft')
			->where("category.cid={$cid} and category.status=1 and category.id=soft.category_id and soft.status=1 and soft.start_tm <= ".time()." and end_tm >= ".time()."")
			->field('soft.category_id,soft.start_tm,soft.end_tm')
			->order('soft.end_tm desc' )
			->select();
			$start_tm=time();
			$end_tm=$result[0]['end_tm'];
			// 先将start_at和end_at转成其当天的最早点和最晚点
			$real_start_at = date("Y/m/d", $start_tm);
			$real_start_at = strtotime($real_start_at);
			
			$real_end_at = date("Y/m/d 23:59:59", $end_tm);
			$real_end_at = strtotime($real_end_at);
			$current = $real_start_at;
			$time_arr = array();
			// 记算指定时间范围内的每天的凌晨
			for ($current = $real_start_at; $current < $real_end_at; $current += 86400) 
			{
				$time_arr[] = $current;
			} 
			 // 获得和每天的区间软件数，把软件个数不够的日期给记录下来
			foreach ($time_arr as $start_at) 
			{      
				$count=0;
				$end_at = $start_at + 86399;
				foreach($result as $key => $val)
				{
				  $a[]=$val['category_id'];
				}
				$b=array_unique($a);
				foreach($b as $key =>$val)
				{
					$where = array(
						'category_id' => $val,
						'start_tm' => array('elt',$end_at),
						'end_tm' => array('egt',$start_at),
						'status' => 1
					);
				   $soft_count= $model -> table('sj_lading_soft') -> where($where) -> count();
				   $count += $soft_count;
				}
				 if($count>0&&$count<6)
				{ 
				  $days[]=date("Y/m/d",$start_at);  
				}
			}
			if($days!=null)
			{
				$data = implode('</br>',$days);
				$emailmodel = D("Dev.Sendemail");
				$email_config_find = $emailmodel->table('pu_config')->where(array('config_type'=> 'EXTENDV1_EMAIL_SEND', 'status'=> 1))->find();
				if (!$email_config_find || !$email_config_find['configcontent'])
				return false;
				$email_content=$data."这些天的软件不够6个";
				$subject = '一键装机运营检测';
				$ret = $emailmodel->realsend($email_config_find['configcontent'], $email_config_find['configcontent'], $subject, $email_content);	
			}
			if($days)
			{
				$data = implode(',',$days);
				$this -> writelog("运营位管理-一键装机管理已检测渠道为{$cid}的渠道软件个数",'sj_lading_soft',$cid,__ACTION__ ,"","edit");
				$this -> assign("waitSecond","3");
				$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/ladingmanage_list");
				$this -> success($data." 这些天的软件个数有问题，详情请看邮件");
			}
			else
			{
				$this -> writelog("运营位管理-一键装机管理已检测渠道为{$cid}的渠道软件个数",'sj_lading_soft',$cid,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl',"/index.php/Sj/Ladingmanage/ladingmanage_list");
				$this -> success("检测成功");
			}	
	    }
	// 下载批量导入模版
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "yjzj.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('一键装机导入模版') . ".csv");
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
	function import_lading() {
		//$pid = empty($_GET['pid']) ? 1 : $_GET['pid'];
		//$this->assign('pid', $pid);
		if ($_GET['down_moban']) 
		{
			$this->down_moban();
		} 
		else if ($_FILES)
		{
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
			$lock_name = 'sj_lading';
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
			$count=count($result_arr);
			// save the import file for backups
			$save_dir = IMPORT_FILE_UPLOAD_PATH;
			$this->mkDirs($save_dir);
			$save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
			$save_file_name = $save_dir . $save_name;
			move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
			$this->writelog("一键装机运营：批量导入了{$save_file_name}。");
			// 返回数据给页面
			if ($flag) {
				// $this->ajaxReturn("","导入成功！", 0);
				$this->ajaxReturn("","成功添加{$count}款软件！", 0);
			} else {
				$this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
			}
		} else {
			if($_GET['from']==1){
				$this->assign('from',$_GET['from']);
			}
			$_GET['pid'] = $_GET['pid'] ? $_GET['pid'] : 1;
			$this->assign('pid',$_GET['pid']);

			$this->display("import_lading");
		}
	}
	// 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
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
	function import_array_convert_and_check(&$content_arr) 
	{
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
		//逻辑判断
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_tm','end_tm');
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
	// 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加一致
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'package'  =>   '软件包名',
            'category_name'  =>   '广告位',
            'recommend'  =>   '一句话推荐',
            'start_tm'  =>   '开始时间(yyyy/MM/dd)',
            'end_tm'  =>   '结束时间(yyyy/MM/dd)',
			'type' =>   '合作形式',
			'beid' => '行为id',
			'is_check' => '默认勾选',
			'rank' => '排序',
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
		$expected_words = array();    
		// 当输入为空不允许时，将其值设为false以作区别   
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
                if ($r_key == 'start_tm' || $r_key == 'end_tm') 
				{
                    if ($r_key == 'start_tm') 
					{
                        $type = 0;
                        $hint = '开始';
                    } 
					else 
					{
                        $type = 1;
                        $hint = '结束';
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
                if($r_key == 'is_check'){
                	if($content_arr[$key][$r_key]=='是'){
                		$content_arr[$key][$r_key]=1;
                	}else if($content_arr[$key][$r_key]=='否'){
                		$content_arr[$key][$r_key]=2;
                	}else{
                		$column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                	}
                }
                if($r_key == 'rank'){
                	if(!preg_match("/^\d*$/",$content_arr[$key][$r_key])){
                		$column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                	}
                }
            }
        }
        return $error_msg;
    }

    // 页面添加或编辑、批量导入共用的逻辑检查
    function logic_check($content_arr) 
	{
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：分区表、区间软件表
        $M_category_table = 'lading_category';
        $M_lading_soft_table = 'lading_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $lading_category_table = 'sj_' . $M_category_table;
        $lading_soft_table = 'sj_' . $M_lading_soft_table;
        // 获得三个表的model
        $lading_category_model = M($M_category_table);
        $lading_soft_model = M($M_lading_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
            // 检查是不是编辑
            if (isset($record['id'])) 
			{
                $where = array('id' => array('EQ', $record['id']));
                $find = $lading_soft_model->where($where)->find();
                // 获得区间名
                $content_arr[$key]['category_id'] = $find['category_id'];
                // 也赋给$record
                $record['category_id'] = $find['category_id'];
            }
            // 检测广告位
            if (isset($record['category_name']) && $record['category_name'] != ""||isset($record['category_id']) && $record['category_id'] != "") 
			{
				if($record['category_name'])
				{
					$where = array(
						'category_name' => array('EQ', $record['category_name']),
						'status' => array('EQ', 1),
					);
					$where['cid'] = $_POST['cid'] ? array('EQ',$_POST['cid']) : array('EQ',0);
					$where['pid'] = $_POST['pid'] ? array('EQ',$_POST['pid']) : array('EQ',1);
				}
				if($record['category_id'])
				{
					$where = array(
						'id' => array('EQ', $record['category_id']),
						'status' => array('EQ', 1),
					);
				}
                $find = $lading_category_model->where($where)->find();
                if (!$find) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "广告位【{$record['category_name']}】无效;");
                }
				else 
				{
					$content_arr[$key]['category_id']=$find['id'];
					$content_arr[$key]['cid']=$find['cid'];
                }
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "广告位不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if(isset($record['package']) && $record['package'] != "") 
			{
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                if (!$find) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
				else
				{
					$content_arr[$key]['softname']=$find['softname'];
				}
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
            // 检查开始时间
            if(isset($record['start_tm']) && $record['start_tm'] != "") 
			{
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_tm']);
                    if ($time) 
					{
                        $content_arr[$key]['bk_start_time'] = $time;
                    } 
					else 
					{
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_tm']) && $record['end_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                }
				else 
				{
                    $time = strtotime($record['end_tm']);
                    if ($time) 
					{
                        $content_arr[$key]['bk_end_time'] = $time;
                    } 
					else 
					{
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
			//检查一句话推荐
			if (isset($record['recommend']) && $record['recommend'] != "") 
			{
				$recommend=$record['recommend'];
				if($this -> strlen_az($recommend) > 14 || $this -> strlen_az($recommend) == 0)
				{
					$this->append_error_msg($error_msg, $key, 1, "一句话推荐【{$record['recommend']}】必须为7个汉字以内;");
				}
            } 
			/*else 
			{
                $this->append_error_msg($error_msg, $key, 1, "一句话推荐不能为空;");
            }*/
        }
        // 业务逻辑：检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) {
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            // 如果选择的渠道无效，则不比较
            if (!isset($record1['cid']))
                continue;
            foreach($content_arr as $key2=>$record2) 
			{
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果包名不同，则不比较
                if ($record1['package'] != $record2['package'])
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                // 如果填写的区间无效，则不比较
                if (!isset($record2['cid']))
                    continue;
                // 区间属性不同，则不比较
                if ($record1['cid'] != $record2['cid'])
                    continue;
                // 时间是否交叉
                if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                    $k1 = $key1 + 1; $k2 = $key2 + 1;
                    $this->append_error_msg($error_msg, $key1, 1, "投放广告位与第{$k2}行有重叠;");
                    $this->append_error_msg($error_msg, $key2, 1, "投放广告位与第{$k1}行有重叠;");
                }
            }
        }

        // 业务逻辑：检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            // 如果填写的区间无效，则不比较
            if (!isset($record['cid']))
                continue;
            //同一时间内同一渠道同一包名是否有冲突 
            // 业务逻辑：获得当前记录的信息：package、cid、
            $es_package = escape_string($record['package']);
            $cid = escape_string($record['cid']);
            $start_time = escape_string($record['bk_start_time']);
            $end_time = escape_string($record['bk_end_time']);
			$id=escape_string($record['category_id']);
			$softname=escape_string($record['softname']);
			$recommend=escape_string($record['recommend']);
			
			// 构造sql语句，查找出与该记录包名相同、也是在相同渠道的所有后台记录，相同平台
			$my_cid_result = $lading_category_model ->where(array('id' => $id)) -> select();
			$my_category_result = $lading_category_model -> where(
				array(
					'cid' => $my_cid_result[0]['cid'],
					'status' => 1,
					'pid' => $my_cid_result[0]['pid'],
				)
			) -> select();

			foreach($my_category_result as $key1 => $val){
				$my_category_str_go .= $val['id'].',';
			}

			$my_category_str = substr($my_category_str_go,0,-1);
			
			$have_package_where['_string'] = "category_id in ({$my_category_str}) and package = '{$es_package}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 ";
			// 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) 
			{
				$edit_id=$record['id'];
				$have_package_where['_string'] = "category_id in ({$my_category_str}) and package = '{$es_package}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 and id !='{$edit_id}'";
            }
			$have_package_result = $lading_soft_model -> where($have_package_where) -> select();
			// 有冲突的后台记录
			foreach($have_package_result as $db_key=>$db_record)
			{
				$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
				$fenlei=$lading_category_model -> where(array("id" =>$db_record['category_id'])) -> select();
	
                $this->append_error_msg($error_msg, $key, 1, "投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，软件包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
			}

			$have_softname_where['_string'] = "category_id in ({$my_category_str}) and softname = '{$softname}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 ";
			// 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) 
			{
				$edit_id=$record['id'];
				$have_softname_where['_string'] = "category_id in ({$my_category_str}) and softname = '{$softname}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 and id !={$edit_id}";
            }
			$have_softname_result = $lading_soft_model -> where($have_softname_where) -> select();
			// 有冲突的后台记录
			foreach($have_softname_result as $db_key=>$db_record)
			{
				$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
				$fenlei=$lading_category_model -> where(array("id" =>$db_record['category_id'])) -> select();
				
                $this->append_error_msg($error_msg, $key, 1, "投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，软件名称为【{$db_record['softname']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
			}
			if ($recommend) {
				$have_comment_where['_string'] = "category_id in ({$my_category_str}) and recommend = '{$recommend}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 ";
				// 如果是编辑，需在后台记录中排除自己
	            if (isset($record['id'])) 
				{
					$edit_id=$record['id'];
					$have_comment_where['_string'] = "category_id in ({$my_category_str}) and recommend = '{$recommend}' and start_tm <= {$end_time} and end_tm >= {$start_time} and status = 1 and id != {$edit_id}";
	            }
				$have_comment_result = $lading_soft_model -> where($have_comment_where) -> select();
				// 有冲突的后台记录
				foreach($have_comment_result as $db_key=>$db_record)
				{
					$start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
	                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
	                $status_paused_hint = "";
	                if ($db_record['status'] == 2) {
	                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
	                }
					$fenlei=$lading_category_model -> where(array("id" =>$db_record['category_id'])) -> select();
					
	                $this->append_error_msg($error_msg, $key, 1, "投放广告位与后台广告位ID为【{$db_record['id']}】，分类名称为【{$fenlei[0]['category_name']}】，推荐语句为【{$db_record['recommend']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
				}
			}
        }
        return $error_msg;
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
	// 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
	
        $result_arr = array();
        $model = M('lading_soft');
		$soft_model = M('soft');
		$category_model = M('lading_category');
		$AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) 
		{
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			$map['create_tm'] = time();
			$map['update_tm'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            //$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
			$where_soft = array(
                    'package' => $record['package'],
                    'status' => 1,
                    'hide' => array('in', array(1, 1024)),
                );
            $find_soft = $soft_model->where($where_soft)->find();
			$map['softname']=$find_soft['softname'];
			
			$where_category = array(
                    'category_name' => $record['category_name'],
                    'status' => 1,
                );
            $find_category = $category_model->where($where_category)->find();
			$map['category_id']=$find_category['id'];
			
			$map['package']=$record['package'];
			$map['recommend'] = $record['recommend'];
			$map['start_tm'] = strtotime($record['start_tm']);
			$map['end_tm'] = strtotime($record['end_tm']);
			//合作形式非必填
			$map['type'] = isset($record['type']) ? $record['type'] : 0;

			//行为id
			$map['beid'] = isset($record['beid']) ? trim($record['beid']) : 0;
			$map['is_check'] = isset($record['is_check']) ? trim($record['is_check']) : 2;

			$map['rank'] = isset($record['rank']) ? trim($record['rank']) : 0;

			$data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }

            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog("在广告位为[{$record['category_name']}]中添加了软件[{$record['package']}],一句话推荐为{$record['recommend']},开始时间为{$record['start_tm']},结束时间为{$record['end_tm']},排序为{$map['rank']},是否默认勾选为{$map['is_check']}(其中1为勾选，2为未勾选)", 'sj_lading_soft',$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			}
			 // else {
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_lading_soft')){
        	$result_arr['table_name']='sj_lading_soft';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
	function move_soft()
	{
		$selected_ids = $_POST['selected_ids'];
		$category_id = $_POST['category_id'];
		$where = array(
			'id' => array('in' ,$selected_ids)
		);
		$model = M('lading_soft');
		$category = $model->table('sj_lading_category')->where(array('id' => $category_id))->find();
		$map = array(
			'category_id' => $category_id,
		);

		$model->where($where)->save($map);
		//$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/index');
		//$selected_ids = implode(',', $selected_ids);
		$this->writelog("在新版推荐位中将id为[{$selected_ids}]的软件移动到了区间{$category_id}", 'sj_lading_soft',$selected_ids,__ACTION__ ,"","add");
		$this->success('移动成功');
	}
	//前端弹出时间间隔
	function change_days()
	{
		$action=$_POST['action'];
		$days=$_POST['days'];
		if($action=="编辑")
		{
			echo 1;
		}
		if($action=="取消")
		{
			echo 2;
		}
		if($action=="保存")
		{
			$model = new Model();
			$_march="/^(0|\+?[1-9][0-9]*)$/";	
			if(!preg_match($_march,$days)) 
			{  
				echo -1;  
				exit;
			} 
			$save_res=$model ->table('pu_config') -> where("config_type='SPACE_DAYS'") -> find();
			$old =$save_res['configcontent'];
			$data['configcontent']=$days;
			$model->table('pu_config')->where("config_type='SPACE_DAYS'")->save($data);
			$this->writelog("运营位管理-一键装机运营-前端弹出时间间隔，从".$old."天改成了".$data['configcontent']."天", 'pu_config','SPACE_DAYS',__ACTION__ ,"","edit");
			echo 3;
		}
	}
}
