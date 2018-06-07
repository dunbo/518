<?php
/*
 * 活动管理
 */
class SetlikeAction extends CommonAction {

	function activity_list(){
		$model = D('sendNum.Activity');	
		$where = array(
			'activate_type' => 5,
			'status' => 1
		);
		$this->check_where($where, 'ap_name', 'isset', 'like');
		$this->check_where($where, 'ap_id', 'isset');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total,$Page) = $model -> get_activity_page($where,$limit);
		$ap_id = array();
		foreach($result as $key => $val){
			if($val['ap_id']){
				$ap_id[] =  $val['ap_id'];
			}	
		}
		$where = array(	'page_id' =>  array('in',$ap_id));
		$soft_result = get_table_data($where,"sj_actives_soft","page_id","page_id,soft_name,package");
		foreach($result as $key => $val){
			$result[$key]['soft_name'] = $soft_result[$val['ap_id']]['soft_name'];
			$result[$key]['package'] = $soft_result[$val['ap_id']]['package'];		
			$like_result = $model -> table('sj_setlike_grade') -> where(array('ap_id' => $val['ap_id'],'status' => 1)) -> order('like_grade') -> select();
			$val['like_result'] = $like_result;
			$result[$key] = $val;			
		}
		$this -> assign("page",$Page->show());
		$this -> assign('result',$result);
		$this -> display();
	}

	
	function add_activity_show(){
		$this -> display();
	}
	
	function add_activity_do(){
		$model = new Model();
		$ap_name = $_POST['ap_name'];
		$data['ap_name'] = $ap_name;
		if(!$ap_name){
			$this -> error("请填写活动名称");
		}
		$have_where['_string'] = "ap_name = '{$ap_name}' and status = 1";
		$have_result = $model -> table('sj_activity_page') -> where($have_where) -> select();
		if($have_result){
			$this -> error("该页面名称已存在");
		}
		if(mb_strlen($ap_name,'utf8') > 30){
			$this -> error("活动名称不能超过30字");
		}
		$package = trim($_POST['package']);
		if(!$package){
			$this -> error("请填写软件包名");
		}
		$is_channel = $_POST['is_channel'];
		$data['is_channel'] = $is_channel;
		$ap_imgurl = $_FILES['ap_imgurl'];
		$soft_bg = $_POST['soft_bg'];
		$data['soft_bg'] = $soft_bg;
		$bg_img = $_FILES['bg_img'];
		$ap_imgurl_bg = $_FILES['ap_imgurl_bg'];
		$bg_color = $_POST['bg_color'];
		$data['bg_color'] = $bg_color;
		if(!$bg_color){
			$this -> error("请填写背景颜色值");
		}
		$bottom_color = $_POST['bottom_color'];
		$data['bottom_color'] = $bottom_color;
		if(!$bottom_color){
			$this -> error("请填写底部背景颜色值");
		}
		$rule_color = $_POST['rule_color'];
		$data['rule_color'] = $rule_color;
		if(!$rule_color){
			$this -> error("请填写活动规则背景颜色值");
		}
		$title = $_POST['title'];
		$data['title'] = $title;
		if(!$title){
			$this -> error("请填写标题");
		}
		if(mb_strlen($title,'utf8') > 8){
			$this -> error("标题不能超过8字");
		}
		$describe = $_POST['describe'];
		$data['describe'] = $describe;
		if(!$describe){
			$this -> error("请填写描述");
		}
		if(mb_strlen($describe,'utf8') > 20){
			$this -> error("描述不能超过20字");
		}
		$like_grade = $_POST['like_grade'];
		$telephone_warning = $_POST['telephone_warning'];
		$data['telephone_warning'] = $telephone_warning;
		if(!$telephone_warning){
			$this -> error("请填写手机号文字提示");
		}
		if(mb_strlen($telephone_warning,'utf8') > 20){
			$this -> error("手机号文字提示不能超过20字");
		}
		$grade_1 = $_POST['grade_1'];
		$grade_2 = $_POST['grade_2'];
		$grade_3 = $_POST['grade_3'];
		$grade_4 = $_POST['grade_4'];
		$grade_5 = $_POST['grade_5'];
		$prize_1 = $_POST['prize_1'];
		$prize_2 = $_POST['prize_2'];
		$prize_3 = $_POST['prize_3'];
		$prize_4 = $_POST['prize_4'];
		$prize_5 = $_POST['prize_5'];
		$is_max = $_POST['is_max'];
		if($like_grade >= $is_max){
			$data['is_max'] = $is_max;
		}else{
			$data['is_max'] = 0;
		}
		$start_tms = $_POST['start_tm'];
		$data['start_tm'] = strtotime($start_tms);
		$end_tms = $_POST['end_tm'];
		$data['end_tm'] = strtotime($end_tms);
		if(!$start_tms || !$end_tms){
			$this -> error("请填写开始时间和结束时间");
		}
		if($data['start_tm'] > $data['end_tm']){
			$this -> error("开始时间不能大于结束时间");
		}
		$like_limit = intval($_POST['like_limit']);
		$data['like_limit'] = $like_limit;
		if($like_limit < 1 || $like_limit > 10){
			$this -> error("每人每日可点赞次数在1-10之间");
		}
		$share_text = $_POST['share_text'];
		$data['share_text'] = $share_text;
		if(!$share_text){
			$this -> error("请填写分享文案");
		}
		if(mb_strlen($share_text,'utf8') > 30){
			$this -> error("分享文案不能超过30字");
		}
		$ap_rule = $_POST['ap_rule'];
		$data['ap_rule'] = nl2br($ap_rule);
		if(!$ap_rule){
			$this -> error("请填写活动规则");
		}
		$ap_rules = $this -> str_br($ap_rule);
		if(mb_strlen($ap_rules,'utf8') > 1000){
			$this -> error("活动规则不能超过1000字");
		}
		$is_telephone = $_POST['is_telephone'];
		$data['is_telephone'] = $is_telephone;
		if($ap_imgurl['size']){
			$high_wd = getimagesize($ap_imgurl['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			if ($width_go_hg != 640 || $height_go_hg != 300) {
				$this->error("活动banner图大小不符合条件");
			}

			if($ap_imgurl['type'] != 'image/png' && $ap_imgurl['type'] != 'image/jpeg' && $ap_imgurl['type'] != 'image/jpg'){
				$this -> error("活动banner图标格式错误");
			}
		}else{
			$this -> error("请上传活动banner图");
		}
		if($bg_img['size']){
			$high_wd = getimagesize($bg_img['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			//if ($width_go_hg != 480 || $height_go_hg != 684) {
			//	$this->error("中间背景图大小不符合条件");
			//}
			if($bg_img['type'] != 'image/png' && $bg_img['type'] != 'image/jpeg' && $bg_img['type'] != 'image/jpg'){
				$this -> error("中间背景图标格式错误");
			}
		}
		if($ap_imgurl_bg['size']){
			$high_wd = getimagesize($ap_imgurl_bg['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			//if ($width_go_hg != 480 || $height_go_hg != 636) {
			//	$this->error("底部图大小不符合条件");
			//}
			if($ap_imgurl_bg['type'] != 'image/png' && $ap_imgurl_bg['type'] != 'image/jpeg' && $ap_imgurl_bg['type'] != 'image/jpg'){
				$this -> error("底部图标格式错误");
			}
		}
		if($ap_imgurl['size'] || $bg_img['size'] || $ap_imgurl_bg['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_imgurl' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec'
				),
				'bg_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'ap_imgurl_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				)
			));
			$lists=$this->_uploadapk(0, $config);
			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'ap_imgurl') {
                    $data['ap_imgurl'] = $val['url'];
                }
                if ($val['post_name'] == 'ap_imgurl_bg') {
                    $data['ap_imgurl_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'bg_img') {
                    $data['bg_img'] = $val['url'];
                }
            }
		}
		$data['activate_type'] = 5;
		$data['ap_ctm'] = time();
		$data['ap_utm'] = time();
		$data['status'] = 1;
		$result = $model -> table('sj_activity_page') -> add($data);
		if($result){
			for($i=1;$i<=$like_grade;$i++){
				$data_like['like_grade'] = $i;
				$data_like['grade_value'] = $_POST['grade_'.$i.''];
				if(!$data_like['grade_value'] || $data_like['grade_value'] < 0){
					$model -> table('sj_activity_page') -> where(array('ap_id' => $result)) -> delete();
					$this -> error("请填写不小于0的点赞等级数目");
				}
				$data_like['grade_prize'] = $_POST['prize_'.$i.''];
				if(mb_strlen($data_like['grade_prize'],'utf8') > 8){
					$model -> table('sj_activity_page') -> where(array('ap_id' => $result)) -> delete();
					$this -> error("获得奖励不能超过8个字");
				}
				if(!$data_like['grade_prize']){
					$model -> table('sj_activity_page') -> where(array('ap_id' => $result)) -> delete();
					$this -> error("请填写获得奖励");
				}
				$data_like['status'] = 1;
				$data_like['ap_id'] = $result;
				$data_like['create_tm'] = time();
				$data_like['update_tm'] = time();
				$like_result = $model -> table('sj_setlike_grade') -> add($data_like);
			}
			$category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> select();
			if(!$category_result){
				$data_category['active_id'] = $ap_id;
				$data_category['category_name'] = '集赞活动分类';
				$data_category['status'] = 1;
				$data_category['rank'] = 1;
				$data_category['create_tm'] = time();
				$data_category['update_tm'] = time();
				$add_category = $model -> table('sj_actives_category') -> add($data_category);
				if($add_category){
					$category_id = $add_category;
				}else{
					$this -> error("添加分类失败");
				}
			}else{
				$category_id = $category_result[0]['id'];
			}
			$package = trim($_POST['package']);
			$soft_where['package'] = $package;
			$soft_where['status'] = 1;
			$soft_result = $model -> table('sj_soft') -> where($soft_where) -> order('softid DESC') -> limit(1) -> select();
			if(!$soft_result){
				$this -> error("该软件包名不存在");
			}
			$soft_name = $soft_result[0]['softname'];
			$data_soft = array(
				'soft_name' => $soft_name,
				'package' => $package,
				'page_id' => $result,
				'rank' => 1,
				'create_tm' => time(),
				'update_tm' => time(),
				'status' => 1,
				'category_id' => $category_id
			);
			$soft_result = $model -> table('sj_actives_soft') -> add($data_soft);
			$update_data['ap_link'] = ACTIVITY_URL . "/lottery/setlike_lottery.php";
			$link_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $result)) -> save($update_data);
			$this -> writelog("已添加id为{$result}的集赞活动页面", 'sj_activity_page', $result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Setlike/activity_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_activity_show(){
		$model = new Model();
		$ap_id = $_GET['id'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $ap_id)) -> find();
		$like_result = $model -> table('sj_setlike_grade') -> where(array('ap_id' => $ap_id,'status' => 1)) -> order('like_grade') -> select();
		$soft_result = $model -> table('sj_actives_soft') -> where(array('page_id' => $ap_id)) -> find();
		$result['soft_name'] = $soft_result['soft_name'];
		$result['package'] = $soft_result['package'];
		$like_grade = count($like_result);
		$result['like_grade'] = $like_grade;
		$result['share_text'] = str_replace("<br />","",$result['share_text']);
		$result['ap_rule'] = str_replace("<br />","",$result['ap_rule']);
		$this -> assign('result',$result);
		$this -> assign('like_result',$like_result);
		$this -> display();
	}
	
	function edit_activity_do(){
		$model = new Model();
		$ap_id = $_POST['ap_id'];
		$ap_name = $_POST['ap_name'];
		$data['ap_name'] = $ap_name;
		if(!$ap_name){
			$this -> error("请填写活动名称");
		}
		$have_where['_string'] = "ap_name = '{$ap_name}' and status = 1 and ap_id != {$ap_id}";
		$have_result = $model -> table('sj_activity_page') -> where($have_where) -> select();
		if($have_result){
			$this -> error("该页面名称已存在");
		}
		if(mb_strlen($ap_name,'utf8') > 30){
			$this -> error("活动名称不能超过30字");
		}
		$package = trim($_POST['package']);
		if(!$package){
			$this -> error("请填写软件包名");
		}
		$is_channel = $_POST['is_channel'];
		$data['is_channel'] = $is_channel;
		$ap_imgurl = $_FILES['ap_imgurl'];
		$bg_img = $_FILES['bg_img'];
		$soft_bg = $_POST['soft_bg'];
		$data['soft_bg'] = $soft_bg;
		$ap_imgurl_bg = $_FILES['ap_imgurl_bg'];
		$bg_color = $_POST['bg_color'];
		$data['bg_color'] = $bg_color;
		if(!$bg_color){
			$this -> error("请填写背景颜色值");
		}
		$bottom_color = $_POST['bottom_color'];
		$data['bottom_color'] = $bottom_color;
		if(!$bottom_color){
			$this -> error("请填写底部背景颜色值");
		}
		$rule_color = $_POST['rule_color'];
		$data['rule_color'] = $rule_color;
		if(!$rule_color){
			$this -> error("请填写活动规则背景颜色值");
		}
		$title = $_POST['title'];
		$data['title'] = $title;
		if(!$title){
			$this -> error("请填写标题");
		}

		if(mb_strlen($title,'utf8') > 8){
			$this -> error("标题不能超过8字");
		}
		$describe = $_POST['describe'];
		$data['describe'] = $describe;
		if(!$describe){
			$this -> error("请填写描述");
		}
		if(mb_strlen($describe,'utf8') > 20){
			$this -> error("描述不能超过20字");
		}
		$like_grade = $_POST['like_grade'];
		$telephone_warning = $_POST['telephone_warning'];
		$data['telephone_warning'] = $telephone_warning;
		if(!$telephone_warning){
			$this -> error("请填写手机号文字提示");
		}
		if(mb_strlen($telephone_warning,'utf8') > 20){
			$this -> error("手机号文字提示不能超过20字");
		}
		$grade_1 = $_POST['grade_1'];
		$grade_2 = $_POST['grade_2'];
		$grade_3 = $_POST['grade_3'];
		$grade_4 = $_POST['grade_4'];
		$grade_5 = $_POST['grade_5'];
		$prize_1 = $_POST['prize_1'];
		$prize_2 = $_POST['prize_2'];
		$prize_3 = $_POST['prize_3'];
		$prize_4 = $_POST['prize_4'];
		$prize_5 = $_POST['prize_5'];
		$is_max = $_POST['is_max'];
		if($like_grade >= $is_max){
			$data['is_max'] = $is_max;
		}else{
			$data['is_max'] = 0;
		}
		$start_tms = $_POST['start_tm'];
		$data['start_tm'] = strtotime($start_tms);
		$end_tms = $_POST['end_tm'];
		$data['end_tm'] = strtotime($end_tms);

		if(!$start_tms || !$end_tms){
			$this -> error("请填写开始时间和结束时间");
		}
		if($data['start_tm'] > $data['end_tm']){
			$this -> error("开始时间不能大于结束时间");
		}
		$like_limit = intval($_POST['like_limit']);
		$data['like_limit'] = $like_limit;
		if($like_limit < 1 || $like_limit > 10){
			$this -> error("每人每日可点赞次数在1-10之间");
		}
		$share_text = $_POST['share_text'];
		$data['share_text'] = $share_text;
		if(!$share_text){
			$this -> error("请填写分享文案");
		}
		if(mb_strlen($share_text,'utf8') > 30){
			$this -> error("分享文案不能超过30字");
		}
		$ap_rule = $_POST['ap_rule'];
		$data['ap_rule'] = nl2br($ap_rule);
		if(!$ap_rule){
			$this -> error("请填写活动规则");
		}
		$ap_rules = $this -> str_br($ap_rule);
		if(mb_strlen($ap_rules,'utf8') > 1000){
			$this -> error("活动规则不能超过1000字");
		}
		
		$is_telephone = $_POST['is_telephone'];
		$data['is_telephone'] = $is_telephone;
		if($ap_imgurl['size']){
			$high_wd = getimagesize($ap_imgurl['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			if ($width_go_hg != 640 || $height_go_hg != 300) {
				$this->error("活动banner图大小不符合条件");
			}

			if($ap_imgurl['type'] != 'image/png' && $ap_imgurl['type'] != 'image/jpeg' && $ap_imgurl['type'] != 'image/jpg'){
				$this -> error("活动banner图标格式错误");
			}
		}
		if($bg_img['size']){
			$high_wd = getimagesize($bg_img['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			//if ($width_go_hg != 480 || $height_go_hg != 684) {
			//	$this->error("中间背景图大小不符合条件");
			//}
			if($bg_img['type'] != 'image/png' && $bg_img['type'] != 'image/jpeg' && $bg_img['type'] != 'image/jpg'){
				$this -> error("中间背景图标格式错误");
			}
		}
		if($ap_imgurl_bg['size']){
			$high_wd = getimagesize($ap_imgurl_bg['tmp_name']);
			$widhig_hg = $high_wd[3];
			$wh_hg = explode(' ', $widhig_hg);
			$wh1_hg = $wh_hg[0];
			$widths_hg = explode('=', $wh1_hg);
			$width1_hg = substr($widths_hg[1], 0, -1);
			$width_go_hg = substr($width1_hg, 1);
			$hi1_hg = $wh_hg[1];
			$heights_hg = explode('=', $hi1_hg);
			$height1_hg = substr($heights_hg[1], 0, -1);
			$height_go_hg = substr($height1_hg, 1);
			//if ($width_go_hg != 480 || $height_go_hg != 636) {
			//	$this->error("底部图大小不符合条件");
			//}
			if($ap_imgurl_bg['type'] != 'image/png' && $ap_imgurl_bg['type'] != 'image/jpeg' && $ap_imgurl_bg['type'] != 'image/jpg'){
				$this -> error("底部图标格式错误");
			}
		}
		if($ap_imgurl['size'] || $bg_img['size'] || $ap_imgurl_bg['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_imgurl' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec'
				),
				'bg_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'ap_imgurl_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				)
			));
			$lists=$this->_uploadapk(0, $config);
			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'ap_imgurl') {
                    $data['ap_imgurl'] = $val['url'];
                }
                if ($val['post_name'] == 'ap_imgurl_bg') {
                    $data['ap_imgurl_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'bg_img') {
                    $data['bg_img'] = $val['url'];
                }
            }
		}
		$data['ap_utm'] = time();
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $ap_id)) -> save($data);

		if($result){
			$del_result = $model -> table('sj_setlike_grade') -> where(array('ap_id' => $ap_id)) -> delete();
			for($i=1;$i<=$like_grade;$i++){
				$data_like['like_grade'] = $i;
				$data_like['grade_value'] = $_POST['grade_'.$i.''];
				if(!$data_like['grade_value'] || $data_like['grade_value'] < 0){
					$this -> error("请填写不小于0的点赞等级数目");
				}
				$data_like['grade_prize'] = $_POST['prize_'.$i.''];
				if(mb_strlen($data_like['grade_prize'],'utf8') > 8){
					$this -> error("获得奖励不能超过8个字");
				}
				if(!$data_like['grade_prize']){
					$this -> error("请填写获得奖励");
				}
				$data_like['status'] = 1;
				$data_like['ap_id'] = $ap_id;
				$data_like['create_tm'] = time();
				$data_like['update_tm'] = time();
				$like_result = $model -> table('sj_setlike_grade') -> add($data_like);
			}
			
			$package = trim($_POST['package']);
			$soft_where['package'] = $package;
			$soft_where['status'] = 1;
			$soft_result = $model -> table('sj_soft') -> where($soft_where) -> order('softid DESC') -> limit(1) -> select();
			if(!$soft_result){
				$this -> error("该软件包名不存在");
			}
			$soft_name = $soft_result[0]['softname'];
			$data_soft = array(
				'soft_name' => $soft_name,
				'package' => $package,
				'update_tm' => time(),
			);
			$soft_wheres['page_id'] = $ap_id;
			$soft_wheres['status'] = 1;
			$soft_result = $model -> table('sj_actives_soft') -> where($soft_wheres) -> save($data_soft);
			$this -> writelog("已编辑id为{$ap_id}的集赞活动页面", 'sj_activity_page', $ap_id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Setlike/activity_list");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function get_softname(){
		$model = new Model();
		$package = $_GET['package'];
		$where['package'] = $package;
		$where['status'] = 1;
		$result = $model -> table('sj_soft') -> where($where) -> order('softid') -> limit(1) -> select();
		echo json_encode($result[0]['softname']);
		return json_encode($result[0]['softname']);
	}

	function del_activity(){
		$model = new Model();
		$ap_id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $ap_id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$ap_id}的集赞活动页面", 'sj_activity_page', $ap_id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Setlike/activity_list/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function str_br($str){
		
		$strs = str_replace("\r\n","\n",$str);
		$strss = str_replace("\r","\n",$strs);
		return $strss;
	}
}