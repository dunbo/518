<?php

//尝鲜频道
class EarlyadoptersAction extends CommonAction{

	function adopters_list(){
		$model = new Model();
        
        // 专题类别
        $feature_type_list = $model->table('sj_feature_type')->where(array('status' => 1))->select();
        $feature_type_map = array();
        foreach ($feature_type_list as $feature_type) {
            $feature_type_map[$feature_type['id']] = $feature_type['feature_type_name'];
        }
        
		$count = $model -> table('sj_adopters') -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_adopters') -> limit($Page->firstRow . ',' . $Page->listRows) -> order('status DESC,rank,create_tm') -> select();
		foreach($result as $key => $val){
			if($val['type'] == 3){
                $val['more_name'] = $feature_type_map[$val['more_type']];
			}elseif($val['type'] == 3){
				$val['more_name'] = '活动列表';
			}elseif($val['type'] == 1 || $val['type'] == 2){
				if ($val['content_type'] == 3) {
					$val['more_name'] = '专题-'.ContentTypeModel::convertFeatureId2FeatureName($val['feature_id']);
				}elseif($val['content_type'] == 4) {
					$val['more_name'] = '页面-'.ContentTypeModel::convertPageType2PageName($val['page_type']);
				}
			}
			$result[$key] = $val;
		}
		foreach($result as $key => $val){
			if($val['rank'] == 999999){
				$val['rank'] = '';
			}
			$result[$key] = $val;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 15;
		}
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_adopters_show(){
		$lr = $_GET['lr'];
		$p = $_GET['p'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
        
        // 专题类别
        $model = M();
        $feature_type_list = $model->table('sj_feature_type')->where(array('status' => 1))->select();
        $this->assign('feature_type_list', $feature_type_list);
		$this -> display();
	}
	
	function add_adopters_do(){
		$model = new Model();
		$p = $_POST['p'];
		$p = $_POST['lr'];
		$name = $_POST['name'];
		if(!$name){
			$this -> error("请填写频道名称");
		}
		if($this -> strlen_az($name) > 8){
			$this -> error("尝鲜名称不能超过4个字");
		}
		$have_been = $model -> table('sj_adopters') -> where(array('name' => $name,'status' => 1)) -> select();
		if($have_been){
			$this -> error("该尝鲜名称已存在");
		}
		if(!$_POST['rank']){
			$data['rank'] = 999999;
		}else{
			if(!is_numeric($_POST['rank'])){
				$this -> error("排序值格式错误");
			}
			$data['rank'] = $_POST['rank'];
		}
		$data['name'] = $name;
		$adopters_type = $_POST['adopters_type'];
		$data['type'] = $adopters_type;
		
		if($adopters_type == 3){
			$more_type = $_POST['more_type_2'];
		}
		
		$content_type = $_POST['content_type'];
		if($adopters_type == 1 ||  $adopters_type == 2){
			if(!$content_type){
				$this -> error("请选择推荐内容");
			}
		}
		$data['content_type'] = $content_type;
		switch($content_type){
			case 3:
				$feature_id = $_POST['feature_id'];
				$my_feature = $model -> table('sj_feature') -> where(array('feature_id' => $feature_id)) -> find();
				
				if(!$feature_id){
					$this -> error("请填写专题名称");
				}
				if($my_feature['type'] == 2){
					$this -> error("不可添加活动专题");
				}
                $data['feature_id'] = $feature_id;
                // 获得专题详情页的展示编码
                $data['page_flag'] = ContentTypeModel::getFeatureDetailPageFlag();
				if($adopters_type == 1 || $adopters_type == 2){
					$more_type = 5;
				}
				break;
			case 4:
				$page_type = $_POST['page_type'];
				if(!$page_type){
					$this -> error("请填写页面内容");
				}
				if($page_type == 'top_taste_fresh'){
					$this -> error("尝鲜更多页面内容不能添加首页尝鲜");
				}
				if($page_type == 'otherfixed_gamegift_list'){
					$this -> error("尝鲜更多页面内容不能添加游戏礼包列表");
				}
				if($page_type == 'fixed_app_category'){
					$this -> error("尝鲜更多页面内容不能添加应用分类");
				}
				if($page_type == 'fixed_game_category'){
					$this -> error("尝鲜更多页面内容不能添加游戏分类");
				}
				if($page_type == 'otherfixed_gamenewserver_list'){
					$this -> error("尝鲜更多页面内容不能添加游戏新服列表");
				}
				if($page_type == 'otherfixed_featurelist_1'){
					$this -> error("尝鲜更多页面内容不能添加专题列表");
				}
				if($page_type == 'otherfixed_featurelist_3'){
					$this -> error("尝鲜更多页面内容不能添加汉化专区");
				}
				if($page_type == 'otherfixed_featurelist_4'){
					$this -> error("尝鲜更多页面内容不能添加其他专题列表");
				}
				if($page_type == 'otherfixed_activity_list'){
					$this -> error("尝鲜更多页面内容不能添加活动列表");
				}
				if($page_type == 'otherfixed_homepage_recommend'){
					$this -> error("尝鲜更多页面内容不能添加首页推荐");
				}
				if($page_type == 'otherfixed_homepage_necessary'){
					$this -> error("尝鲜更多页面内容不能添加首页必备");
				}
				if($page_type == 'top_1d_2'){
					$this -> error("尝鲜更多页面内容不能添加游戏日排行");
				}
				
				
                $data['page_type'] = $page_type;
                // 生成page_flag和page_id值
                $ret = ContentTypeModel::getPageFlagAndIds($page_type);
                if ($ret['page_flag']) {
                    $data['page_flag'] = $ret['page_flag'];
                }
                if (!empty($ret['page_ids'])) {
                    if (isset($ret['page_ids'][0])) {
                        $data['page_id1'] = $ret['page_ids'][0];
                        if (isset($ret['page_ids'][1])) {
                            $data['page_id2'] = $ret['page_ids'][1];
                        }
                    }
                }
				if($adopters_type == 1 || $adopters_type == 2){
					$more_type = 6;
				}
				break;
		}
		
		$data['more_type'] = $more_type;
		if(!$_POST['most_num']){
			$this -> error("请填写最大数量值");
		}
		if(!is_numeric($_POST['most_num']) || strpos($postn,”.”)!==false){
			$this -> error("请填写正确的最大数量值");
		}
		if($_POST['most_num'] > 10 || $_POST['most_num'] < 1){
			$this -> error("请填写正确的最大数量值");
		}
		$data['most_num'] = $_POST['most_num'];
		$data['adopters_color'] = $_POST['adopters_color'];
		$pic_url = $_FILES['pic_url'];
		if($pic_url['size']){
			$pic_tmp = getimagesize($pic_url['tmp_name']);
			$my_highs = $pic_tmp[3];
			$wh_hgs = explode(' ',$my_highs);
			$wh1_hgs = $wh_hgs[0];
			$widths_hgs = explode('=',$wh1_hgs);
			$width1_hgs = substr($widths_hgs[1], 0, -1);
			$width_go_hgs = substr($width1_hgs,1);
			$heigth_go = $wh_hgs[1];
			$height_hgs = explode('=',$heigth_go);
			$height_hgs = substr($height_hgs[1], 0, -1);
			$height_go_hgs = substr($height_hgs,1);

			if($width_go_hgs != 40 || $height_go_hgs != 40){
				$this -> error("请上传宽度为40,高度为40的jgp/png图片");
			}
			
			$config['multi_config']['pic_url'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'img_p_size' => 1024 * 100,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$data['pic_url'] = $list['image'][0]['url'];
		}else{
			$this -> error("请上传图片");
		}
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$result = $model -> table('sj_adopters') -> add($data);
		if($result){
			$this -> writelog("已添加id为{$result}的尝鲜类别",'sj_adopters',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/adopters_list/p/{$p}/lr/{$lr}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	
	function edit_adopters_show(){
		$model = new Model();
		$id = $_GET['id'];

		$result = $model -> table('sj_adopters') -> where(array('id' => $id)) -> select();
		$count = $model -> table('sj_adopters') -> where(array('status' => 1)) -> count();
		if($result[0]['rank'] == 999999){
			$result[0]['rank'] = '';
		}
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$lr = $_GET['lr'];
		$p = $_GET['p'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}

	function edit_adopters_do(){
		$model = new Model();
		$id = $_POST['id'];
		$name = trim($_POST['name']);
		$lr = $_POST['lr'];
		$p = $_POST['p'];
		if(!$name){
			$this -> error("请填写标题");
		}
		if($this -> strlen_az($name) > 8){
			$this -> error("标题名称不能大于4个字");
		}
		$data['name'] = $name;
		if(!$_POST['rank']){
			$data['rank'] = 999999;
		}else{
			$data['rank'] = $_POST['rank'];
		}
	
		$most_num = $_POST['most_num'];
		if(!$most_num){
			$this -> error("请填写最大数量值");
		}
		
		if(!is_numeric($_POST['most_num']) || strpos($_POST['most_num'],".")!==false){
			$this -> error("请填写正确的最大数量值");
		}
		if($_POST['most_num'] > 10 || $_POST['most_num'] < 1){
			$this -> error("请填写正确的最大数量值");
		}
		$where_have_name['_string'] = "name = '{$name}' and id != {$id} and status = 1";
		$have_name = $model -> table('sj_adopters') -> where($where_have_name) -> select();
		if($have_name){
			$this -> error("该标题已存在");
		}
		$data['most_num'] = $most_num;
		$adopters_color = $_POST['adopters_color'];
		$data['adopters_color'] = $adopters_color;
		$been_result = $model -> table('sj_adopters') -> where(array('id' => $id)) -> select();
		$log_result = $this -> logcheck(array('id' => $id),'sj_adopters',array('name' => $name,'rank' => $_POST['rank']),$model);
		$rank_result = $this -> select_rank($been_result[0]['rank'],$rank,'sj_adopters',$id);
		
		
		$pic_url = $_FILES['pic_url'];
		if($pic_url['size']){
			$pic_tmp = getimagesize($pic_url['tmp_name']);
			$my_highs = $pic_tmp[3];
			$wh_hgs = explode(' ',$my_highs);
			$wh1_hgs = $wh_hgs[0];
			$widths_hgs = explode('=',$wh1_hgs);
			$width1_hgs = substr($widths_hgs[1], 0, -1);
			$width_go_hgs = substr($width1_hgs,1);
			$heigth_go = $wh_hgs[1];
			$height_hgs = explode('=',$heigth_go);
			$height_hgs = substr($height_hgs[1], 0, -1);
			$height_go_hgs = substr($height_hgs,1);

			if($width_go_hgs != 40 || $height_go_hgs != 40){
				$this -> error("请上传宽度为40,高度为40的jgp/png图片");
			}
			
			$config['multi_config']['pic_url'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'img_p_size' => 1024 * 100,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$data['pic_url'] = $list['image'][0]['url'];
		}
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$data['update_tm'] = time();
		$name_result = $model -> table('sj_adopters') -> where(array('id' => $id)) -> save($data);
		if($rank_result || $name_result){
			$this -> writelog("已编辑id为{$id}的尝鲜频道".$log_result,'sj_adopters',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/adopters_list/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
		
	}
	
	function activity_list(){
		$model = new Model();
		//百度下拉效果
		if($this->isAjax())
		{
			$keyword = $_GET['query'];
			$active_name =$model->query("select name from sj_activity where status=1 and name like '%{$keyword}%'");
			$data = array(
					'query' => $keyword,
					'suggestions' => array(),
			);
			foreach($active_name as $v) {
					$data['suggestions'][] = $v['name'];
			}
			exit(json_encode($data));
		}
		$adopters_id = $_GET['adopters_id'];
		if($_GET['my_time'] == 1){
			$where['_string'] = "adopters_id = {$adopters_id} and start_tm > ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 2){
			$where['_string'] = "adopters_id = {$adopters_id} and end_tm < ".time()." and status = 1";
			$order = "end_tm DESC";
		}elseif($_GET['my_time'] == 3 || !$_GET['my_time']){
			$where['_string'] = "adopters_id = {$adopters_id} and start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			$order = "start_tm";
		}
		$count = $model -> table('sj_adopters_activity') -> where($where) -> count();
	
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);

		$result = $model -> table('sj_adopters_activity') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();

		foreach($result as $key => $val){
			$activity_name = $model -> table('sj_activity') -> where(array('id' => $val['activity_id'])) -> select();
			$val['package'] = $activity_name[0]['package'];
			$val['activity_name'] = $activity_name[0]['name'];
			$result[$key] = $val;
		}
		
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('adopters_id',$adopters_id);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_activity_show(){
		$model = new Model();
		$adopters_id = $_GET['adopters_id'];
		$activity_result = $model -> table('sj_activity') -> where(array('status' => 1)) -> select();
		$this -> assign('adopters_id',$adopters_id);
		$this -> assign('activity_result',$activity_result);
		$this -> display();
	}
	
	function add_activity_do(){
		$model = new Model();
		$adopters_id = $_POST['adopters_id'];
		$active_name = trim($_POST['active_name']);
		$activity_id_result = $model -> table('sj_activity') -> where(array('name' => $active_name,'status' => 1)) -> select();
		if(!$activity_id_result){
			$this -> error("该活动名称不存在");
		}else{
			$activity_id = $activity_id_result[0]['id'];
		}
		$rank = $_POST['rank'];
	
		if(!$rank){
			$this -> error("请填写排序");
		}
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$title = trim($_POST['title']);
		if(!$title){
			$this -> error("请填写标题");
		}
		
		$start_tm = date('Y-m-d H:i:s',strtotime($_POST['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_POST['end_tm']));
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		$where_have_title['_string'] = "adopters_id = {$adopters_id} and title = '{$title}' and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$title_have_been = $model -> table('sj_adopters_activity') -> where($where_have_title) -> select();
		if($title_have_been){
			$this -> error("相同排期下不能添加相同标题");
		}

		$where_time_have_been['_string'] = "adopters_id = {$adopters_id} and activity_id = {$activity_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$time_have_been = $model -> table('sj_adopters_activity') -> where($where_time_have_been) -> select();
		if($time_have_been){
			$this -> error("该时间段内该活动已被添加");
		}
		
		$where_rank_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$rank_have_been = $model -> table('sj_adopters_activity') -> where($where_rank_have_been) -> select();
		if($rank_have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $activity_id)) -> select();
	
		
		$package = $activity_result[0]['package'];
		
		/*运营后台临时需求  活动列表的图片非必填 
		  2015-3-27  add by shitingting  */
		if($_FILES['pic_url']['size'])
		{
			$pic_tmp = getimagesize($_FILES['pic_url']['tmp_name']);
			$my_highs = $pic_tmp[3];
			$wh_hgs = explode(' ',$my_highs);
			$wh1_hgs = $wh_hgs[0];
			$widths_hgs = explode('=',$wh1_hgs);
			$width1_hgs = substr($widths_hgs[1], 0, -1);
			$width_go_hgs = substr($width1_hgs,1);
			$heigth_go = $wh_hgs[1];
			$height_hgs = explode('=',$heigth_go);
			$height_hgs = substr($height_hgs[1], 0, -1);
			$height_go_hgs = substr($height_hgs,1);
			
			if($width_go_hgs != 480 || $height_go_hgs != 180){
				$this -> error("请上传宽度为480,高度为180的图片");
			}
			
			$config['multi_config']['pic_url'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			foreach($list['image'] as $key => $val){
				if($val['post_name'] == 'pic_url'){
					$data['pic_url'] = $val['url'];
				}
			}
		}
		else
		{
			$data['pic_url']="";
		}
		
		
		$data['adopters_id'] = $adopters_id;
		$data['activity_id'] = $activity_id;
		$data['rank'] = $rank;
		$data['title'] = $title;
		$data['start_tm'] = strtotime($start_tm);
		$data['end_tm'] = strtotime($end_tm);
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$result = $model -> table('sj_adopters_activity') -> add($data);

		if($result){
			$this -> writelog("已添加id为{$result}的尝鲜频道劲爆活动,排序为{$rank},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_adopters_activity',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/activity_list/adopters_id/{$adopters_id}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	
	}
	
	function edit_activity_show(){
		$model = new Model();
		$id = $_GET['id'];
		
		$result = $model -> table('sj_adopters_activity') -> where(array('id' => $id)) -> select();
		foreach($result as $key => $val){
			$activity_result = $model -> table('sj_activity') -> where(array('id' => $val['activity_id'],'status' => 1)) -> select(); 
			$val['activity_name'] = $activity_result[0]['name'];
			$result[$key] = $val;
		}
		//$this -> assign('activity_result',$activity_result);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	function edit_activity_do(){
		$model = new Model();
		$id = $_POST['id'];
		$been_result = $model -> table('sj_adopters_activity') -> where(array('id' => $id)) -> select();
		$adopters_id = $been_result[0]['adopters_id'];
		$active_name = trim($_POST['active_name']);
		$activity_id_result = $model -> table('sj_activity') -> where(array('name' => $active_name,'status' => 1)) -> select();
		if(!$activity_id_result){
			$this -> error("该活动名称不存在");
		}else{
			$activity_id = $activity_id_result[0]['id'];
		}
	
		$rank = $_POST['rank'];
		
		if(!$rank){
			$this -> error("请填写排序");
		}
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$title = trim($_POST['title']);
		if(!$title){
			$this -> error("请填写标题");
		}

		$start_tm = date('Y-m-d H:i:s',strtotime($_POST['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_POST['end_tm']));
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
        if($_POST['life']==1)
		{
		  if(strtotime($end_tm)<time())
		  {
			$this->error("您修改的复制上线的日期还是无效日期");
		  }
		}	
        if($_POST['life']==1)
        {
		   $where_have_title['_string'] = "adopters_id = {$adopters_id} title = '{$title}' and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		}  
        else
 		{
		  $where_have_title['_string'] = "adopters_id = {$adopters_id} and id != {$id} and title = '{$title}' and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		}
		$title_have_been = $model -> table('sj_adopters_activity') -> where($where_have_title) -> select();
		if($title_have_been){
			$this -> error("相同排期下不能添加相同标题");
		}
        if($_POST['life']==1)
		{
		    $where_time_have_been['_string'] = "adopters_id = {$adopters_id} activity_id = {$activity_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		}
		else
		{
		  $where_time_have_been['_string'] = "adopters_id = {$adopters_id} and id != {$id} and activity_id = {$activity_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		}
		$time_have_been = $model -> table('sj_adopters_activity') -> where($where_time_have_been) -> select();
		if($time_have_been){
			$this -> error("该时间段内该活动已被添加");
		}
		if($_POST['life']==1)
		{
		  $where_rank_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		}
		else
		{
		 $where_rank_have_been['_string'] = "adopters_id = {$adopters_id} and id != {$id} and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		 }
		$rank_have_been = $model -> table('sj_adopters_activity') -> where($where_rank_have_been) -> select();
		if($rank_have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		
		$activity_result = $model -> table('sj_activity') -> where(array('id' => $activity_id)) -> select();
		
		$package = $activity_result[0]['package'];
		
		
		
		$data['activity_id'] = $activity_id;
		$data['rank'] = $rank;
		$data['title'] = $title;
		$data['start_tm'] = strtotime($start_tm);
		$data['end_tm'] = strtotime($end_tm);
		$data['update_tm'] = time();
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$log_result = $this -> logcheck(array('id' => $id),'sj_adopters_activity',$data,$model);
		if($_POST['life']==1)
		{
		  $select = $model -> table('sj_adopters_activity') -> where(array('id' => $id)) ->select();
		  $data['status']=1;
		  $data['create_tm']=time();
		  $data['adopters_id']=$select[0]['adopters_id'];
		  $data['pic_url']=$select[0]['pic_url'];
		  $result = $model -> table('sj_adopters_activity') -> add($data);
			if($result){
				$this -> writelog("已复制上线id为{$result}的尝鲜频道劲爆活动".$log_result,'sj_adopters_activity',$result,__ACTION__ ,"","add");
				$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/activity_list/adopters_id/{$been_result[0]['adopters_id']}");
				$this -> success("复制上线成功");
			}else{
				$this -> error("复制上线失败");
			}
		}
		else
		{
			$result = $model -> table('sj_adopters_activity') -> where(array('id' => $id)) -> save($data);
			if($result){
				$this -> writelog("已编辑id为{$result}的尝鲜频道劲爆活动".$log_result,'sj_adopters_activity',$result,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/activity_list/adopters_id/{$been_result[0]['adopters_id']}");
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
	
	}
	
	function del_activity(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_adopters_activity') -> where(array('id' => $id)) -> select();
		$result = $model -> table('sj_adopters_activity') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
		if($result){
			$this -> writelog("已删除id为{$id}的尝鲜频道劲爆活动",'sj_adopters_activity',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/activity_list/adopters_id/{$been_result[0]['adopters_id']}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function gift_list(){
		$model = new Model();
		//百度下拉效果
		if($this->isAjax())
		{
			$gift_model = D('sendNum.sendNum');
			$keyword = $_GET['query'];
			$gift_name =$gift_model->query("select active_name from sendnum_active where status=1 and active_name like '%{$keyword}%' and active_from & 4 and end_tm > ".time()."");
			
			$data = array(
					'query' => $keyword,
					'suggestions' => array(),
			);
			foreach($gift_name as $v) {
					$data['suggestions'][] = $v['active_name'];
			}
			exit(json_encode($data));
		}
		
		if($_GET['my_time'] == 1){
			$where['_string'] = "start_tm > ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 2){
			$where['_string'] = "end_tm < ".time()." and status = 1";
			$order = "end_tm DESC";
		}elseif($_GET['my_time'] == 3 || !$_GET['my_time']){
			$where['_string'] = "start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			$order = "start_tm";
		}
		$count = $model -> table('sj_adopters_gift') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_adopters_gift') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();

		$sendnum_model = D('sendNum.sendNum');
		foreach($result as $key => $val){
			$gift_name = $sendnum_model -> table('sendnum_active') -> where(array('id' => $val['gift_id'])) -> select();
			$val['gift_name'] = $gift_name[0]['active_name'];
			$val['surplus_num'] = $gift_name[0]['num_cnt'] - $gift_name[0]['used_cnt'];
			$result[$key] = $val;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('result',$result);
		$this -> display();
	
	}
	
	function add_gift_show(){
		$sendnum_model = D('sendNum.sendNum');
		$result = $sendnum_model -> table('sendnum_active') -> where(array('active_from' => 2,'status' => 1)) -> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_gift_do(){
		$model = new Model();
		$gift_name = trim($_GET['gift_name']);
		$gift_model = D('sendNum.sendNum');
		$gift_where['_string'] = "active_name = '{$gift_name}' and status = 1 and active_from & 4 and end_tm > ".time()."";
		$gift_name_result = $gift_model -> table('sendnum_active') -> where($gift_where) -> select();
		
		if(!$gift_name_result){
			$this -> error("该礼包名称不存在或已过期");
		}else{
			$gift_id = $gift_name_result[0]['id'];
		}
		$rank = $_GET['rank'];
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$start_tm = date('Y-m-d H:i:s',strtotime($_GET['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_GET['end_tm']));
		if(!$_GET['start_tm'] || !$_GET['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$where_have_been['_string'] = "rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$have_been = $model -> table('sj_adopters_gift') -> where($where_have_been) -> select();
		if($have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		$where_gift_have_been['_string'] = "gift_id = {$gift_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$gift_have_been = $model -> table('sj_adopters_gift') -> where($where_gift_have_been) -> select();
		if($gift_have_been){
			$this -> error("该时间段内该礼包已被添加");
		}
		$data = array(
			'gift_id' => $gift_id,
			'rank' => $rank,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$result = $model -> table('sj_adopters_gift') -> add($data);

		if($result){
			$this -> writelog("已添加尝鲜频道礼包的id为{$result},排序为{$rank},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_adopters_gift',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Earlyadopters/gift_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_gift_show(){
		$model = new Model();
		$id = $_GET['id'];
		$sendnum_model = D('sendNum.sendNum');
		
		$result = $model -> table('sj_adopters_gift') -> where(array('id' => $id)) -> select();
		foreach($result as $key => $val){
			$gift_result = $sendnum_model -> table('sendnum_active') -> where(array('id' => $val['gift_id'])) -> select();
			$val['gift_name'] = $gift_result[0]['active_name'];
			$result[$key] = $val;
		}
		$this -> assign('result',$result);
		//$this -> assign('gift_result',$gift_result);
		$this -> display();
	}
	
	function edit_gift_do(){
		$model = new Model();
		$id = $_GET['id'];
		$gift_name = trim($_GET['gift_name']);
		$gift_model = D('sendNum.sendNum');
		$gift_where['_string'] = "active_name = '{$gift_name}' and status = 1 and active_from & 4 and end_tm > ".time()."";
		$gift_name_result = $gift_model -> table('sendnum_active') -> where($gift_where) -> select();
		
		if(!$gift_name_result){
			$this -> error("该礼包名称不存在或已过期");
		}else{
			$gift_id = $gift_name_result[0]['id'];
		}
		$rank = $_GET['rank'];
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$start_tm = date('Y-m-d H:i:s',strtotime($_GET['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_GET['end_tm']));
		if(!$_GET['start_tm'] || !$_GET['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		
		$where_have_been['_string'] = "rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and id != {$id}";
		$have_been = $model -> table('sj_adopters_gift') -> where($where_have_been) -> select();
		if($have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		$where_gift_have_been['_string'] = "id != {$id} and gift_id = {$gift_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$gift_have_been = $model -> table('sj_adopters_gift') -> where($where_gift_have_been) -> select();
		if($gift_have_been){
			$this -> error("该时间段内该礼包已被添加");
		}
		$data = array(
			'gift_id' => $gift_id,
			'rank' => $rank,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'update_tm' => time(),
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_adopters_gift',$data,$model);
		$result = $model -> table('sj_adopters_gift') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑尝鲜频道礼包的id为{$id}".$log_result,'sj_adopters_gift',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Earlyadopters/gift_list');
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_gift(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_adopters_gift') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
		if($result){
			$this -> writelog("已删除尝鲜频道礼包id为{$id}",'sj_adopters_gift',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Earlyadopters/gift_list');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function feature_list(){
		$model = new Model();
		//百度下拉效果
		if($this->isAjax())
		{
			$keyword = $_GET['query'];
			$feature_name =$model->query("select name from sj_feature where status=1 and pid like '%,1,%' and name like '%{$keyword}%'");
			$data = array(
					'query' => $keyword,
					'suggestions' => array(),
			);
			foreach($feature_name as $v) {
					$data['suggestions'][] = $v['name'];
			}
			exit(json_encode($data));
		}
		$adopters_id = $_GET['adopters_id'];
		if($_GET['my_time'] == 1){
			$where['_string'] = "adopters_id = {$adopters_id} and start_tm > ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 2){
			$where['_string'] = "adopters_id = {$adopters_id} and end_tm < ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 3 || !$_GET['my_time']){
			$where['_string'] = "adopters_id = {$adopters_id} and start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			$order = "start_tm";
		}
		$count = $model -> table('sj_adopters_feature') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_adopters_feature') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
	
		foreach($result as $key => $val){
			$feature_name = $model -> table('sj_feature') -> where(array('feature_id' => $val['feature_id'])) -> select();
			$val['feature_name'] = $feature_name[0]['name'];
			$result[$key] = $val;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('adopters_id',$adopters_id);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_feature_show(){
		$model = new Model();
		$result = $model -> table('sj_feature') -> where(array('status' => 1)) -> select();
		$adopters_id = $_GET['adopters_id'];
		$this -> assign('adopters_id',$adopters_id);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	
	function add_feature_do(){
		$model = new Model();
		$adopters_id = $_GET['adopters_id'];
		$feature_name = trim($_GET['feature_name']);
		$feature_name_result = $model -> table('sj_feature') -> where(array('name' => $feature_name,'status' => 1,'pid'=>array('like','%,1,%'))) -> select();
		if(!$feature_name_result){
			$this -> error("该专题名称不存在");
		}else{
			$feature_id = $feature_name_result[0]['feature_id'];
		}
		$push = $_GET['push'];
		
		$rank = $_GET['rank'];
		
		$start_tm = date('Y-m-d H:i:s',strtotime($_GET['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_GET['end_tm']));
		if(!$_GET['start_tm'] || !$_GET['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		$where_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$have_been = $model -> table('sj_adopters_feature') -> where($where_have_been) -> select();
		if($have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		$where_feature_have_been['_string'] = "adopters_id = {$adopters_id} and feature_id = {$feature_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$feature_have_been = $model -> table('sj_adopters_feature') -> where($where_feature_have_been) -> select();
		if($feature_have_been){
			$this -> error("该时间段内该专题已被添加");
		}
		$data = array(
			'adopters_id' => $adopters_id,
			'feature_id' => $feature_id,
			'rank' => $rank,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$result = $model -> table('sj_adopters_feature') -> add($data);
		if($result){
			$this -> writelog("已添加尝鲜频道火热专题的id为{$result},排序为{$rank},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_adopters_feature',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/feature_list/adopters_id/{$adopters_id}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_feature_show(){
		$model = new Model();
		$id = $_GET['id'];
		
		$result = $model -> table('sj_adopters_feature') -> where(array('id' => $id)) -> select();
		foreach($result as $key => $val){
			$feature_result = $model -> table('sj_feature') -> where(array('status' => 1,'feature_id' => $val['feature_id'])) -> select();
			$val['feature_name'] = $feature_result[0]['name'];
			$result[$key] = $val;
		}
		//$this -> assign('feature_result',$feature_result);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_feature_do(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_adopters_feature') -> where(array('id' => $id)) -> select();
		$adopters_id = $been_result[0]['adopters_id'];
		$rank = $_GET['rank'];
		$feature_name = trim($_GET['feature_name']);
		$feature_name_result = $model -> table('sj_feature') -> where(array('name' => $feature_name,'status' => 1)) -> select();
		if(!$feature_name_result){
			$this -> error("该专题名称不存在");
		}else{
			$feature_id = $feature_name_result[0]['feature_id'];
		}
		$start_tm = date('Y-m-d H:i:s',strtotime($_GET['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_GET['end_tm']));
		if(!$_GET['start_tm'] || !$_GET['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($_GET['life']==1)
		{
		   $where_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)."";
		}
		else
		{
		   $where_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and id != {$id}";
		}
		$have_been = $model -> table('sj_adopters_feature') -> where($where_have_been) -> select();
		if($have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		if($_GET['life']==1)
		{
		   $where_feature_have_been['_string'] = "adopters_id = {$adopters_id} and feature_id = {$feature_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		}
		else
		{
		   $where_feature_have_been['_string'] = "adopters_id = {$adopters_id} and id != {$id} and feature_id = {$feature_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		}
		$feature_have_been = $model -> table('sj_adopters_feature') -> where($where_feature_have_been) -> select();
		if($feature_have_been){
			$this -> error("该时间段内该专题已被添加");
		}
		if($_GET['life']==1)
			{
			  if(strtotime($end_tm)<time())
			  {
			    $this->error("您修改的复制上线的日期还是无效日期");
			  }
			}
		$data = array(
			'feature_id' => $feature_id,
			'rank' => $rank,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'update_tm' => time(),
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_adopters_feature',$data,$model);
		if($_GET['life']==1)
		{
			$select = $model -> table('sj_adopters_feature') -> where(array('id' => $id)) -> select();
			$data['adopters_id']=$select[0]['adopters_id'];
			$data['create_tm']=time();
			$result = $model -> table('sj_adopters_feature') -> add($data);
			if($result){
				$this -> writelog("已复制上线尝鲜频道火热专题的id为{$data['feature_id']}".$log_result,'sj_adopters_feature',$result,__ACTION__ ,"","add");
				$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/feature_list/adopters_id/{$select[0]['adopters_id']}");
				$this -> success("复制上线成功");
			}else{
				$this -> error("复制上线失败");
			}
		}
		else
		{
			$result = $model -> table('sj_adopters_feature') -> where(array('id' => $id)) -> save($data);
			if($result){
				$this -> writelog("已编辑尝鲜频道火热专题的id为{$id}".$log_result,'sj_adopters_feature',$id,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/feature_list/adopters_id/{$been_result[0]['adopters_id']}");
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
	}
	
	function del_feature(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_adopters_feature') -> where(array('id' => $id)) -> select();
		$result = $model -> table('sj_adopters_feature') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
		if($result){
			$this -> writelog("已删除尝鲜频道火热专题id为{$id}",'sj_adopters_feature',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/feature_list/adopters_id/{$been_result[0]['adopters_id']}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function exclusive_list(){
		$model = new Model();
		$adopters_id = $_GET['adopters_id'];
		if($_GET['my_time'] == 1){
			$where['_string'] = "adopters_id = {$adopters_id} and start_tm > ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 2){
			$where['_string'] = "adopters_id = {$adopters_id} and end_tm < ".time()." and status = 1";
			$order = "end_tm DESC";
		}elseif($_GET['my_time'] == 3 || !$_GET['my_time']){
			$where['_string'] = "adopters_id = {$adopters_id} and start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			$order = "start_tm";
		}
		$count = $model -> table('sj_adopters_exclusive') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_adopters_exclusive') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
		foreach($result as $key => $val){
			$soft_name = $model -> table('sj_soft') -> where(array('package' => $val['package'],'status' => 1,'hide' => 1)) -> select();
			$operating_name = $model -> table('pu_operating') -> where(array('oid' => $val['operating_id'])) -> find();
			$val['operating_name'] = $operating_name['mname'];
			$cid_arr = explode(',',$val['cid']);
			$chname_str_go = '';
			if($val['cid']){
				foreach($cid_arr as $k => $v){
					$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> find();
					$chname_str_go .= $chname_result['chname'].',';
				}
				
				$chname_str = substr($chname_str_go,1,-2);
				$chname_arr = explode(',',$chname_str);
				if(count($chname_arr) > 2){
					$chname_arr_str = '';
					foreach(array_slice($chname_arr,0,2) as $k => $v){
						$chname_arr_str .= $v.',';
					}
					$chname_arr_str_go = substr($chname_arr_str,0,-1);
					$val['chname'] = $chname_arr_str_go.'...';
				}else{
					$val['chname'] = $chname_str;
				}
			}
			$val['soft_name'] = $soft_name[0]['softname'];
			$result[$key] = $val;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$parent_result = $model -> table('sj_adopters') -> where(array('id' => $adopters_id)) -> find();

		$this -> assign('adopters_type',$parent_result['type']);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('adopters_id',$adopters_id);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function show_channel(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_adopters_exclusive') -> where(array('id' =>$id)) -> find();
		$cid_arr = explode(',',$result['cid']);
		foreach($cid_arr as $k => $v){
			$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> find();
			$chname_str_go .= $chname_result['chname'].',';
		}
		$chname_str = substr($chname_str_go,1,-2);
		$this -> assign('chname_str',$chname_str);
		$this -> display();
		
	}
	
	function add_exclusive_show(){
		$model = new Model();
		$operating_result = $model -> table('pu_operating') -> select();
		$adopters_id = $_GET['adopters_id'];
		$this -> assign('adopters_id',$adopters_id);
		$this -> assign('operating_result',$operating_result);
		$this -> display();
	}
	
	function add_exclusive_do(){
		$model = new Model();
		$package = trim($_POST['package']);
		if(!$package){
			$this -> error("请填写软件包名");
		}
		$adopters_id = $_POST['adopters_id'];
		$package_have = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> select();
		if(!$package_have){
			$this -> error("此包名不存在");
		}
		
		$rank = $_POST['rank'];
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$operating_id = $_POST['operating_id'];
		$cid = $_POST['cid'];
		foreach($cid as $key => $val){
			$cid_str_go .= $val.',';
		}
		if($cid_str_go){
			$cid_str = ','.substr($cid_str_go,0,-1).',';
		}else{
			$cid_str = 0;
		}
		$start_tm = date('Y-m-d H:i:s',strtotime($_POST['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_POST['end_tm']));
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($cid_str){
			foreach($cid as $key => $val){
				$where_have_been['_string'] = "adopters_id = {$adopters_id} and package = '{$package}' and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				$have_been = $model -> table('sj_adopters_exclusive') -> where($where_have_been) -> select();	
				
				if($have_been){
					$this -> error("该渠道该时间段内该软件已被添加");
				}
				
				$where_rank_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				$have_rank_been = $model -> table('sj_adopters_exclusive') -> where($where_rank_have_been) -> select();	
				
				if($have_rank_been){
					$this -> error("该渠道该时间段内该排序已被添加");
				}
			}
		}else{
			$where_have_been['_string'] = "adopters_id = {$adopters_id} and package = '{$package}' and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			$have_been = $model -> table('sj_adopters_exclusive') -> where($where_have_been) -> select();	
			
			if($have_been){
				$this -> error("该渠道该时间段内该软件已被添加");
			}
			$where_rank_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			$have_rank_been = $model -> table('sj_adopters_exclusive') -> where($where_rank_have_been) -> select();	
			
			if($have_rank_been){
				$this -> error("该渠道该时间段内该排序已被添加");
			}
		}
		$data = array(
			'adopters_id' => $adopters_id,
			'package' => $package,
			'rank' => $rank,
			'operating_id' => $operating_id,
			'cid' => $cid_str,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$result = $model -> table('sj_adopters_exclusive') -> add($data);
	
		if($result){
			$this -> writelog("已添加尝鲜频道独家首发的id为{$result},排序为{$rank},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_adopters_exclusive',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/exclusive_list/adopters_id/{$adopters_id}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_exclusive_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_adopters_exclusive') -> where(array('id' => $id)) -> select();
		$str_go = substr($result[0]['cid'],1,-1);
		$chl_where['_string'] = "cid in ({$str_go})";
		$chl_result = $model -> table('sj_channel') -> where($chl_where) -> select();
		$operating_result = $model -> table('pu_operating') -> select();

		$this -> assign('operating_result',$operating_result);
		$this -> assign('chl_list',$chl_result);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_exclusive_do(){
		$model = new Model();
		$id = $_POST['id'];
		$been_result = $model -> table('sj_adopters_exclusive') -> where(array('id' => $id)) -> select();
		$adopters_id = $been_result[0]['adopters_id'];
		$package = trim($_POST['package']);
		if(!$package){
			$this -> error("请填写软件包名");
		}
		$package_have = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> select();
		if(!$package_have){
			$this -> error("此包名不存在");
		}
		$rank = $_POST['rank'];
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$operating_id = $_POST['operating_id'];
		$cid = $_POST['cid'];
		foreach($cid as $key => $val){
			$cid_str_go .= $val.',';
		}
		if($cid_str_go){
			$cid_str = ','.substr($cid_str_go,0,-1).',';
		}else{
			$cid_str = 0;
		}
		$start_tm = date('Y-m-d H:i:s',strtotime($_POST['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_POST['end_tm']));
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($_POST['life']==1)
			{
			  if(strtotime($end_tm)<time())
			  {
			    $this->error("您修改的复制上线的日期还是无效日期");
			  }
			}
		if($cid_str){
			foreach($cid as $key => $val){
			    if($_POST['life']==1)
				{
				 $where_have_been['_string'] = "adopters_id = {$adopters_id}  and package = '{$package}' and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				}
				else
				{
				$where_have_been['_string'] = "id != {$id} and adopters_id = {$adopters_id}  and package = '{$package}' and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				}
				$have_been = $model -> table('sj_adopters_exclusive') -> where($where_have_been) -> select();	
			
				if($have_been){
					$this -> error("该渠道该时间段内该软件已被添加");
				}
				if($_POST['life']==1)
				{
				 $where_rank_have_been['_string'] = "adopters_id = {$adopters_id} and rank = {$rank} and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				}
				else
				{
				 $where_rank_have_been['_string'] = "id != {$id} and adopters_id = {$adopters_id} and rank = {$rank} and (cid like '%,{$val},%' or cid = '0') and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
				}
				$have_rank_been = $model -> table('sj_adopters_exclusive') -> where($where_rank_have_been) -> select();	
			
				if($have_rank_been){
					$this -> error("该渠道该时间段内该排序已被添加");
				}
			}
		}else{
		    if($_POST['life']==1)
			{
			$where_have_been['_string'] = "adopters_id = {$adopters_id}  and package = '{$package}' and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			}
			else
			{
			 $where_have_been['_string'] = "id != {$id} and  adopters_id = {$adopters_id}  and package = '{$package}' and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			}
			$have_been = $model -> table('sj_adopters_exclusive') -> where($where_have_been) -> select();	
			
			if($have_been){
				$this -> error("该渠道该时间段内该软件已被添加");
			}
			if($_POST['life']==1)
			{
			 $where_rank_have_been['_string'] = "adopters_id = {$adopters_id}  and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			}
			else
			{
			 $where_rank_have_been['_string'] = "id != {$id} and  adopters_id = {$adopters_id}  and rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
			}
			$have_rank_been = $model -> table('sj_adopters_exclusive') -> where($where_rank_have_been) -> select();	
			
			if($have_rank_been){
				$this -> error("该渠道该时间段内该排序已被添加");
			}
		}

		
		$data = array(
			'package' => $package,
			'rank' => $rank,
			'operating_id' => $operating_id,
			'cid' => $cid_str,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'update_tm' => time(),
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_adopters_exclusive',$data,$model);
		if($_POST['life']==1)
		{
		 $select=$model->table('sj_adopters_exclusive')->where(array('id' => $id))->select();
		 $data['create_tm']=time();
		 $data['status']=1;
		 $data['adopters_id']=$select[0]['adopters_id'];
		 $result = $model -> table('sj_adopters_exclusive') -> add($data);
		 if($result){
			$this -> writelog("已复制上线尝鲜频道独家首发的package为{$package}".$log_result,'sj_adopters_exclusive',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/exclusive_list/adopters_id/{$been_result[0]['adopters_id']}");
			$this -> success("复制上线成功");
		}
		else
		{
			$this -> error("复制上线失败");
		}
		}
		else
		{
			$result = $model -> table('sj_adopters_exclusive') -> where(array('id' => $id)) -> save($data);
			if($result){
				$this -> writelog("已编辑尝鲜频道独家首发的id为{$id}".$log_result,'sj_adopters_exclusive',$id,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/exclusive_list/adopters_id/{$been_result[0]['adopters_id']}");
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
	}
	
	function del_exclusive(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_adopters_exclusive') -> where(array('id' => $id)) -> select();
		$result = $model -> table('sj_adopters_exclusive') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
		if($result){
			$this -> writelog("已删除尝鲜频道独家首发的id为{$id}",'sj_adopters_exclusive',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Earlyadopters/exclusive_list/adopters_id/{$been_result[0]['adopters_id']}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function chinesization_list(){
		$model = new Model();
		//百度下拉效果
		if($this->isAjax())
		{
			$keyword = $_GET['query'];
			$feature_name =$model->query("select name from sj_feature where status=1 and type = 3 and name like '%{$keyword}%'");
			//echo $model -> getLastSql();
			$data = array(
					'query' => $keyword,
					'suggestions' => array(),
			);
			foreach($feature_name as $v) {
					$data['suggestions'][] = $v['name'];
			}
			exit(json_encode($data));
		}
		
		if($_GET['my_time'] == 1){
			$where['_string'] = "start_tm > ".time()." and status = 1";
			$order = "start_tm";
		}elseif($_GET['my_time'] == 2){
			$where['_string'] = "end_tm < ".time()." and status = 1";
			$order = "end_tm DESC";
		}elseif($_GET['my_time'] == 3 || !$_GET['my_time']){
			$where['_string'] = "start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			$order = "start_tm";
		}
		$count = $model -> table('sj_adopters_chinesization') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_adopters_chinesization') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
		foreach($result as $key => $val){
			$feature_name = $model -> table('sj_feature') -> where(array('feature_id' => $val['feature_id'])) -> find();
			$val['feature_name'] = $feature_name['name'];
			$result[$key] = $val;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('my_time',$_GET['my_time']);
		$this -> assign('result',$result);
		$this -> display();
	
	}
	
	function add_chinesization_show(){
		$this -> display();
	}
	
	function add_chinesization_do(){
		$model = new Model();
		$feature_name = $_GET['feature_name'];
		$feature_result = $model -> table('sj_feature') -> where(array('name' => $feature_name,'status' => 1,'type' => 3)) -> select();
		if($feature_result){
			$feature_id = $feature_result[0]['feature_id'];
		}else{
			$this -> error("该汉化专题名称不存在");
		}
		$push = $_GET['push'];
		if($push == 1){
			$rank = 1;
		}else{
			$rank = $_GET['rank'];
		}
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$start_tm = date('Y-m-d H:i:s',strtotime($_GET['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_GET['end_tm']));
		if(!$_GET['start_tm'] || !$_GET['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		$where_have_been['_string'] = "rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$have_been = $model -> table('sj_adopters_chinesization') -> where($where_have_been) -> select();
		if($have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		
		$where_chinesization_have_been['_string'] = "feature_id = {$feature_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$chinesization_have_been = $model -> table('sj_adopters_chinesization') -> where($where_chinesization_have_been) -> select();
		if($chinesization_have_been){
			$this -> error("该时间段内该汉化专题已被添加");
		}

		$data = array(
			'feature_id' => $feature_id,
			'push' => $push,
			'rank' => $rank,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$result = $model -> table('sj_adopters_chinesization') -> add($data);
		if($result){
			$this -> writelog("已添加尝鲜频道汉化专题id为{$result},排序为{$rank},开始时间为{$start_tm},结束时间为{$end_tm}",'sj_adopters_chinesization',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Earlyadopters/chinesization_list');
			$this -> success("添加成功"); 
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_chinesization_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_adopters_chinesization') -> where(array('id' => $id)) -> select();
		foreach($result as $key => $val){
			$feature_result = $model -> table('sj_feature') -> where(array('feature_id' => $val['feature_id'])) -> find();
			$val['feature_name'] = $feature_result['name'];
			$result[$key] = $val;
		}
		
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_chinesization_do(){
		$model = new Model();
		$id = $_GET['id'];
		$feature_name = $_GET['feature_name'];
		$feature_result = $model -> table('sj_feature') -> where(array('name' => $feature_name,'status' => 1,'type' => 3)) -> select();
		if($feature_result){
			$feature_id = $feature_result[0]['feature_id'];
		}else{
			$this -> error("该汉化专题名称不存在");
		}
		$been_result = $model -> table('sj_adopters_chinesization') -> where(array('id' => $id)) -> select();
		
		if($been_result[0]['push'] == 1){
			$rank = 1;
		}else{
			$rank = $_GET['rank'];
		}
		if(!$rank){
			$this -> error("请填写排序");
		}
		if(!intval($rank) || $rank < 0){
			$this -> error("请填写正整数排序");
		}
		$start_tm = date('Y-m-d H:i:s',strtotime($_GET['start_tm']));
		$end_tm = date('Y-m-d H:i:s',strtotime($_GET['end_tm']));
		if(!$_GET['start_tm'] || !$_GET['end_tm']){
			$this -> error("请填写开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		$where_have_been['_string'] = "rank = {$rank} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and id != {$id}";
		$have_been = $model -> table('sj_adopters_chinesization') -> where($where_have_been) -> select();
	
		if($have_been){
			$this -> error("该时间段内该排序已被添加");
		}
		
		$where_chinesization_have_been['_string'] = "id != {$id} and feature_id = {$feature_id} and status = 1 and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm);
		$chinesization_have_been = $model -> table('sj_adopters_chinesization') -> where($where_chinesization_have_been) -> select();
		if($chinesization_have_been){
			$this -> error("该时间段内该汉化专题已被添加");
		}
		$data = array(
			'feature_id' => $feature_id,
			'rank' => $rank,
			'start_tm' => strtotime($start_tm),
			'end_tm' => strtotime($end_tm),
			'update_tm' => time(),
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_adopters_chinesization',$data,$model);
		$result = $model -> table('sj_adopters_chinesization') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑尝鲜频道汉化专题id为{$result}".$log_result,'sj_adopters_chinesization',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Earlyadopters/chinesization_list');
			$this -> success("编辑成功"); 
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_chinesization(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_adopters_chinesization') -> where(array('id' => $id)) -> save(array('status' => 0,'update_tm'=>time()));
		if($result){
			$this -> writelog("已删除尝鲜频道id为{$id}的汉化专题",'sj_adopters_chinesization',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Earlyadopters/chinesization_list');
			$this -> success("删除成功"); 
		}else{
			$this -> error("删除失败");
		}
	}
	
	
	function change_status(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_adopters') -> where(array('id' => $id)) -> find();
		if($been_result['status'] == 1){
			$data['status'] = 0;
			$log = "已停用id为{$id}的尝鲜频道";
		}else{
			$data['status'] = 1;
			$log = "已启用id为{$id}的尝鲜频道";
		}
		$result = $model -> table('sj_adopters') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog($log,'sj_adopters',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Earlyadopters/adopters_list');
			$this -> success("操作成功");
		}else{
			$this -> success("操作失败");
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






}
