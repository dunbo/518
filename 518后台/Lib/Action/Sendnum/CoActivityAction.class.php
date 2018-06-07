<?php
/*
*通用活动
*/
class CoActivityAction extends CommonAction{

	function activity_list(){
		$model = D('sendNum.Activity');	
		$where = array(
			'activate_type' => 4,
			'status' => 1
		);
		$this->check_where($where, 'ap_name', 'isset', 'like');
		$this->check_where($where, 'ap_id', 'isset');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($result,$total,$Page) = $model -> get_activity_page($where,$limit);
		$this -> assign("page", $Page->show());
		$this -> assign('result',$result);
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/activity_list'));	
		$this -> display("");
	}

	function add_activity_show(){
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> display();
	}

	function add_activity_do(){
		$model = new Model();
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$ap_name = $_POST['ap_name'];
		$data['ap_name'] = $ap_name;
		if(!$ap_name){
			$this -> error("请填写活动名称");
		}
		$name_where['_string'] = "ap_name = '{$ap_name}' and status = 1";
		$name_result = $model -> table('sj_activity_page') -> where($name_where) -> select();
		if($name_result){
			$this -> error("该活动名称已存在");
		}
		$ap_type = $_POST['ap_type'];
		$data['ap_type'] = $ap_type;
		$ap_desc = trim($_POST['ap_desc']);
		$ap_desc = htmlspecialchars($_POST['ap_desc']);
		$ap_desc = str_replace("\\","",$ap_desc);
		$data['ap_desc'] = $ap_desc;
		$desc_color = $_POST['desc_color'];
		$data['desc_color'] = $_POST['desc_color'];
		$bg_color = $_POST['bg_color'];
		$third_text_color = $_POST['third_text_color'];
		$data['third_text_color'] = $third_text_color;
		$second_text_color = $_POST['second_text_color'];
		$data['second_text_color'] = $second_text_color;
		if(!$ap_desc){
			$this -> error("请填写活动说明");
		}
		$ap_pic = $_FILES['ap_pic'];
		if(!$ap_pic['size']){
			$this -> error("请上传页面banner图");
		}
		if($ap_pic['size']){
			$high_wd = getimagesize($ap_pic['tmp_name']);
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
			if ($width_go_hg != 640 || $height_go_hg != 290) {
				$this->error("高分辨率图标大小不符合条件");
			}
		}
		$ap_imgurl_bg = $_FILES['ap_imgurl_bg'];
                /*
		if(!$ap_imgurl_bg['size']){
			$this -> error("请上传页面底部图片");
                }*/
		if($ap_type == 2){
			$ap_rule = trim($_POST['ap_rule']);
			$ap_rule = htmlspecialchars($_POST['ap_rule']);
			$ap_rule = str_replace("\\","",$ap_rule);
			$data['ap_rule'] = $ap_rule;
			if(!$ap_rule){
				$this -> error("请填写活动规则");
			}
			$version_code = $_POST['version_code'];
			if(!$version_code){
				$this -> error("请填写限制参加活动版本号");
			}
			if($version_code && !eregi('^[0-9]*$',$version_code)){
				$this -> error("版本号格式错误");
			}
			
			$data['version_code'] = $version_code;
			$change_switch = $_POST['change_switch'];
			$data['change_switch'] = $change_switch;
			$lottery_style = $_POST['lottery_style'];
			$data['lottery_style'] = $lottery_style;
			$share_switch = $_POST['share_switch'];
			$data['share_switch'] = $share_switch;
			$share_add = $_POST['share_add'];
			$data['share_add'] = $share_add;
			$share_add_all = $_POST['share_add_all'];
			$data['share_add_all'] = $share_add_all;
			$must_share = $_POST['must_share'];
			$data['must_share'] = $must_share;
			$share_bgcolor = $_POST['share_bgcolor'];
			$data['share_bgcolor'] = $share_bgcolor;
			$download_bgcolor = $_POST['download_bgcolor'];
			$data['download_bgcolor'] = $download_bgcolor;
			$warning_bgcolor = $_POST['warning_bgcolor'];
			$data['warning_bgcolor'] = $warning_bgcolor;
			$share_weixin_pic = $_FILES['share_weixin_pic'];
			$share_other_pic = $_FILES['share_other_pic'];
			$share_text = $_POST['share_text'];
			$data['share_text'] = $share_text;
			$button_color = $_POST['button_color'];
			$button_pic = $_FILES['button_pic'];
			$button_text_color = $_POST['button_text_color'];
			$data['button_text_color'] = $button_text_color;
			$award_color = $_POST['award_color'];
			$data['award_color'] = $award_color;
			$rule_color = $_POST['rule_color'];
			$data['rule_color'] = $rule_color;
			if($share_switch == 1){
				if(!$share_weixin_pic['size']){
					$this -> error("请上传分享到微信图");
				}
				if(!$share_other_pic['size']){
					$this -> error("请上传分享图");
				}
				if(!$share_text){
					$this -> error("请填写分享文案");
				}
			}
			$free_day_switch = $_POST['free_day_switch'];
			$data['free_day_switch'] = $free_day_switch;
			$soft_style = $_POST['soft_style'];
			$data['soft_style'] = $soft_style;
			$soft_order = $_POST['soft_order'];
			$data['soft_order'] = $soft_order;
			$down_addlotterynum_switch = $_POST['down_addlotterynum_switch'];
			$data['down_addlotterynum_switch'] = $down_addlotterynum_switch;
			$is_filter = $_POST['is_filter'];
			$data['is_filter'] = $is_filter;
			$is_score = $_POST['is_score'];
			$data['is_score'] = $is_score;
			$package_size_switch = $_POST['package_size_switch'];
			$data['package_size_switch'] = $package_size_switch;
			$download_config = $_POST['download_config'];
			$data['download_config'] = $download_config;
			$lose_no_img = $_FILES['lose_no_img'];
	
			if($_POST['dep_type']==1){
				if(!$lose_no_img['size']){
						$this -> error("请上传未中奖无抽奖机会提示图");
				}
				$lost_no_desc = $_POST['lost_no_desc'];
				$data['lost_no_desc'] = $lost_no_desc;
				if(!$lost_no_desc){
						$this -> error("请填写未中奖无抽奖机会提示语");
				}
				$lose_yes_img = $_FILES['lose_yes_img'];
				if(!$lose_yes_img['size']){
						$this -> error("请上传未中奖有抽奖机会提示图");
				}
				$lose_yes_desc = $_POST['lose_yes_desc'];
				$data['lose_yes_desc'] = $lose_yes_desc;
				if(!$lose_yes_desc){
						$this -> error("请填写未中奖有抽奖机会提示语");
				}
			}

			if($_POST['dep_type']==2){
				if(!$lose_no_img['size']){
						$this -> error("请上传未中奖提示图");
				}
				$lost_no_desc = $_POST['lost_no_desc'];
				$data['lost_no_desc'] = $lost_no_desc;
				if(!$lost_no_desc){
						$this -> error("请填写未中奖提示语");
				}
				$lose_yes_img = $_FILES['lose_yes_img'];
				if(!$lose_yes_img['size']){
						$this -> error("请上传无抽奖机会提示图");
				}
				$lose_yes_desc = $_POST['lose_yes_desc'];
				$data['lose_yes_desc'] = $lose_yes_desc;
				if(!$lose_yes_desc){
						$this -> error("请填写无抽奖机会提示语");
				}
			}


			$last_lottery_img = $_FILES['last_lottery_img'];

			$last_lottery_desc = $_POST['last_lottery_desc'];
			$data['last_lottery_desc'] = $last_lottery_desc;
			$is_warning = $_POST['is_warning'];
			$data['is_warning'] = $is_warning;
			$is_repeat = $_POST['is_repeat'];
			$data['is_repeat'] = $is_repeat;
			$bg_img = $_FILES['bg_img'];
			$lottery_num_limit = $_POST['lottery_num_limit'];
			if(!$lottery_num_limit){
				$this -> error("请填写用户每日抽奖次数限制");
			}
			if($lottery_num_limit && !eregi('^[0-9]*$',$lottery_num_limit)){
				$this -> error("用户每日抽奖次数限制格式错误");
			}
			$data['lottery_num_limit'] = $lottery_num_limit;
			
			$lottery_pic = $_FILES['lottery_pic'];
			$click_lottery_pic = $_FILES['click_lottery_pic'];
			$unclick_lottery_pic = $_FILES['unclick_lottery_pic'];
			$alert_color = $_POST['alert_color'];
			$data['alert_color'] = $alert_color;
			$alert_button_color = $_POST['alert_button_color'];
			$data['alert_button_color'] = $alert_button_color;
			$first_text_color = $_POST['first_text_color'];
			$data['first_text_color'] = $first_text_color;
			$update_warning_pic = $_FILES['update_warning_pic'];

			if($_POST['dep_type']==1){
				if(!$update_warning_pic['size']){
					$this -> error("请上传更新提示图");
				}
			}
			$update_button_color = $_POST['update_button_color'];
			$data['update_button_color'] = $update_button_color;
			$rule_pic = $_FILES['rule_pic'];
			$prize_text_color = $_POST['prize_text_color'];
			$data['prize_text_color'] = $prize_text_color;
			$my_prize_button_color = $_POST['my_prize_button_color'];
			$data['my_prize_button_color'] = $my_prize_button_color;
			$popup_bg_pic = $_POST['popup_bg_pic'];
			$data['popup_bg_pic'] = $popup_bg_pic;
			$prize_bg_color = $_POST['prize_bg_color'];
			$data['prize_bg_color'] = $prize_bg_color;
			$soft_bg = $_POST['soft_bg'];
			$data['soft_bg'] = $soft_bg;
			$no_prize_pic = $_FILES['no_prize_pic'];
			$no_prize_text = $_POST['no_prize_text'];
			$data['no_prize_text'] = $no_prize_text;
		}
		
		if($ap_pic || $ap_imgurl_bg || $share_weixin_pic || $share_other_pic || $lose_no_img || $lose_yes_img || $last_lottery_img || $button_pic || $bg_img || $lottery_pic || $click_lottery_pic || $unclick_lottery_pic || $update_warning_pic || $rule_pic || $no_prize_pic){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'ap_imgurl_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_weixin_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_other_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_no_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_yes_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'last_lottery_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'button_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'bg_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'click_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'unclick_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'update_warning_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'rule_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'no_prize_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				)
			));
			$lists=$this->_uploadapk(0, $config);
			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'ap_pic') {
                    $data['ap_imgurl'] = $val['url'];
                }
                if ($val['post_name'] == 'ap_imgurl_bg') {
                    $data['ap_imgurl_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'share_weixin_pic') {
                    $data['share_weixin_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'share_other_pic') {
                    $data['share_other_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_no_img') {
                    $data['lose_no_img'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_yes_img') {
                    $data['lose_yes_img'] = $val['url'];
                }
				if ($val['post_name'] == 'last_lottery_img') {
                    $data['last_lottery_img'] = $val['url'];
                }
				if ($val['post_name'] == 'button_pic') {
                    $button_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'bg_img') {
                    $bg_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'lottery_pic') {
                    $lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'click_lottery_pic') {
                    $click_lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'unclick_lottery_pic') {
                    $unclick_lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'update_warning_pic') {
                    $update_warning_pic = $val['url'];
                }
				if ($val['post_name'] == 'rule_pic') {
                    $rule_pic = $val['url'];
                }
				if ($val['post_name'] == 'no_prize_pic') {
                    $no_prize_pic = $val['url'];
                }
            }
		}
		

		$data['bg_color'] = $bg_color;
		$data['bg_img'] = $bg_pic_url;
		
		if($lottery_style != 2){
			$data['lottery_pic'] = $lottery_pic;
		}elseif($lottery_style == 2){
			$sudoku_color = $_POST['sudoku_color'];
			$data['sudoku_color'] = $sudoku_color;
		}
		
		
		$data['click_lottery_pic'] = $click_lottery_pic;
		$data['unclick_lottery_pic'] = $unclick_lottery_pic;
		$data['update_warning_pic'] = $update_warning_pic;
		$data['rule_pic'] = $rule_pic;
		$data['no_prize_pic'] = $no_prize_pic;

		$data['button_color'] = $button_color;
		$data['button_pic'] = $button_pic_url;

		if($ap_type == 2){
			$get_lottery_type_arr = $_POST['get_lottery_type'];
			if(!$get_lottery_type_arr){
				$this -> error("请选择抽奖机会获取方式");
			}else{
				$data['get_lottery_type'] = array_sum($get_lottery_type_arr);
			}
			$gift_type = $_POST['gift_type'];
			if(!$gift_type){
				$this -> error("请选择礼包发放优先级");
			}else{
				$data['gift_type'] = $gift_type;
			}
		}
		$data['status'] = 1;
		$data['dep_type'] = $_POST['dep_type'];
		$data['activate_type'] = 4;
		$data['ap_ctm'] = time();
		$data['ap_utm'] = time();

                if($_POST['dep_type']==2)//如果是游戏运营
                {
                    $data['lose_yes_img']=$data['lose_no_img'];
                }
		$result = $model -> table('sj_activity_page') -> add($data);
		if($result){
			if($ap_type == 1){
				$update_data['ap_link'] = ACTIVITY_URL . "/lottery/coactivity_prepare.php";
			}elseif($ap_type == 2){
				$update_data['ap_link'] = ACTIVITY_URL . "/lottery/coactivity_lottery.php";
			}elseif($ap_type == 3){
				$update_data['ap_link'] = ACTIVITY_URL . "/lottery/coactivity_end.php";
			}
			$update_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $result)) -> save($update_data);
			$this -> writelog("已添加id为{$result}的通用活动页面", 'sj_activity_page',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/activity_list/p/{$p}/lr/{$lr}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_activity_show(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id,'status' => 1)) -> select();
		$result[0]['ap_desc'] = str_replace("<br />","",$result[0]['ap_desc']);
		$result[0]['ap_rule'] = str_replace("<br />","",$result[0]['ap_rule']);
		$this -> assign('result',$result);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> display();
	}
	
	function edit_activity_do(){
		$model = new Model();
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$id = $_POST['id'];
		$the_result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> select();
		$ap_name = $_POST['ap_name'];
		if(!$ap_name){
			$this -> error("请填写活动名称");
		}
		$data['ap_name'] = $ap_name;
		$name_where['_string'] = "ap_name = '{$ap_name}' and status = 1 and ap_id != {$id}";
		$name_result = $model -> table('sj_activity_page') -> where($name_where) -> select();
		if($name_result){
			$this -> error("该活动名称已存在");
		}
		$ap_type = $the_result[0]['ap_type'];
		$ap_desc = trim($_POST['ap_desc']);
		$ap_desc = htmlspecialchars($_POST['ap_desc']);
		$ap_desc = str_replace("\\","",$ap_desc);
		$data['ap_desc'] = $ap_desc;
		$desc_color = $_POST['desc_color'];
		$data['desc_color'] = $_POST['desc_color'];
		$bg_color = $_POST['bg_color'];
		$third_text_color = $_POST['third_text_color'];
		$data['third_text_color'] = $third_text_color;
		$second_text_color = $_POST['second_text_color'];
		$data['second_text_color'] = $second_text_color;
		if(!$ap_desc){
			$this -> error("请填写活动说明");
		}
		$ap_pic = $_FILES['ap_pic'];
		if($ap_pic['size']){
			$high_wd = getimagesize($ap_pic['tmp_name']);
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
			if ($width_go_hg != 640 || $height_go_hg != 290) {
				$this->error("高分辨率图标大小不符合条件");
			}
		}
		$ap_imgurl_bg = $_FILES['ap_imgurl_bg'];
			$bg_img = $_FILES['bg_img'];
		if($ap_type == 2){
			$ap_rule = trim($_POST['ap_rule']);
			$ap_rule = htmlspecialchars($_POST['ap_rule']);
			$ap_rule = str_replace("\\","",$ap_rule);
			$data['ap_rule'] = $ap_rule;
			if(!$ap_rule){
				$this -> error("请填写活动规则");
			}
			$version_code = $_POST['version_code'];
			if(!$version_code){
				$this -> error("请填写限制参加活动版本号");
			}
			if($version_code && !eregi('^[0-9]*$',$version_code)){
				$this -> error("版本号格式错误");
			}
			
			$data['version_code'] = $version_code;
			$change_switch = $_POST['change_switch'];
			$data['change_switch'] = $change_switch;
			$lottery_style = $_POST['lottery_style'];
			$data['lottery_style'] = $lottery_style;
			$share_switch = $_POST['share_switch'];
			$data['share_switch'] = $share_switch;
			$share_add = $_POST['share_add'];
			$data['share_add'] = $share_add;
			$share_add_all = $_POST['share_add_all'];
			$data['share_add_all'] = $share_add_all;
			$must_share = $_POST['must_share'];
			$data['must_share'] = $must_share;
			$share_bgcolor = $_POST['share_bgcolor'];
			$data['share_bgcolor'] = $share_bgcolor;
			$download_bgcolor = $_POST['download_bgcolor'];
			$data['download_bgcolor'] = $download_bgcolor;
			$warning_bgcolor = $_POST['warning_bgcolor'];
			$data['warning_bgcolor'] = $warning_bgcolor;
			$share_weixin_pic = $_FILES['share_weixin_pic'];
			$share_other_pic = $_FILES['share_other_pic'];
			$share_text = $_POST['share_text'];
			$data['share_text'] = $share_text;
			$button_color = $_POST['button_color'];
			$button_pic = $_FILES['button_pic'];
			$button_text_color = $_POST['button_text_color'];
			$data['button_text_color'] = $button_text_color;
			$award_color = $_POST['award_color'];
			$data['award_color'] = $award_color;
			$rule_color = $_POST['rule_color'];
			$data['rule_color'] = $rule_color;
			$free_day_switch = $_POST['free_day_switch'];
			$data['free_day_switch'] = $free_day_switch;
			$soft_style = $_POST['soft_style'];
			$data['soft_style'] = $soft_style;
			$soft_order = $_POST['soft_order'];
			$data['soft_order'] = $soft_order;
			$down_addlotterynum_switch = $_POST['down_addlotterynum_switch'];
			$data['down_addlotterynum_switch'] = $down_addlotterynum_switch;
			$is_filter = $_POST['is_filter'];
			$data['is_filter'] = $is_filter;
			$is_score = $_POST['is_score'];
			$data['is_score'] = $is_score;
			$package_size_switch = $_POST['package_size_switch'];
			$data['package_size_switch'] = $package_size_switch;
			$download_config = $_POST['download_config'];
			$data['download_config'] = $download_config;
			$lose_no_img = $_FILES['lose_no_img'];
			$lost_no_desc = $_POST['lost_no_desc'];
			$data['lost_no_desc'] = $lost_no_desc;
			if(!$lost_no_desc){
				if($_POST['dep_type']==1){
					$this -> error("请填写未中奖无抽奖机会提示语");
				}else{
					$this -> error("请填写未中奖提示语");
				}
			}
			$lose_yes_img = $_FILES['lose_yes_img'];
			$lose_yes_desc = $_POST['lose_yes_desc'];
			$data['lose_yes_desc'] = $lose_yes_desc;
			if(!$lose_yes_desc){
                            if($_POST['dep_type']==1){
				$this -> error("请填写未中奖有抽奖机会提示语");
                            }else{
				$this -> error("请填写无抽奖机会提示语");
                            }
			}
			$last_lottery_img = $_FILES['last_lottery_img'];

			$last_lottery_desc = $_POST['last_lottery_desc'];
			$data['last_lottery_desc'] = $last_lottery_desc;
			$is_warning = $_POST['is_warning'];
			$data['is_warning'] = $is_warning;
			$is_repeat = $_POST['is_repeat'];
			$data['is_repeat'] = $is_repeat;
			$lottery_num_limit = $_POST['lottery_num_limit'];
			if(!$lottery_num_limit){
				$this -> error("请填写用户每日抽奖次数限制");
			}
			if($lottery_num_limit && !eregi('^[0-9]*$',$lottery_num_limit)){
				$this -> error("用户每日抽奖次数限制格式错误");
			}
			$data['lottery_num_limit'] = $lottery_num_limit;
			
			$lottery_pic = $_FILES['lottery_pic'];
			$click_lottery_pic = $_FILES['click_lottery_pic'];
			$unclick_lottery_pic = $_FILES['unclick_lottery_pic'];
			$alert_color = $_POST['alert_color'];
			$data['alert_color'] = $alert_color;
			$alert_button_color = $_POST['alert_button_color'];
			$data['alert_button_color'] = $alert_button_color;
			$first_text_color = $_POST['first_text_color'];
			$data['first_text_color'] = $first_text_color;
			$update_warning_pic = $_FILES['update_warning_pic'];
			$update_button_color = $_POST['update_button_color'];
			$data['update_button_color'] = $update_button_color;
			$rule_pic = $_FILES['rule_pic'];
			$prize_text_color = $_POST['prize_text_color'];
			$data['prize_text_color'] = $prize_text_color;
			$my_prize_button_color = $_POST['my_prize_button_color'];
			$data['my_prize_button_color'] = $my_prize_button_color;
			$popup_bg_pic = $_POST['popup_bg_pic'];
			$data['popup_bg_pic'] = $popup_bg_pic;
			$prize_bg_color = $_POST['prize_bg_color'];
			$data['prize_bg_color'] = $prize_bg_color;
			$soft_bg = $_POST['soft_bg'];
			$data['soft_bg'] = $soft_bg;
			$no_prize_pic = $_FILES['no_prize_pic'];
			$no_prize_text = $_POST['no_prize_text'];
			$data['no_prize_text'] = $no_prize_text;
                }else if($ap_type==3){
                	$ap_rule = trim($_POST['ap_rule']);
			$ap_rule = htmlspecialchars($_POST['ap_rule']);
			$ap_rule = str_replace("\\","",$ap_rule);
			$data['ap_rule'] = $ap_rule;
                }
		
		if($ap_pic['size'] || $ap_imgurl_bg['size'] || $share_weixin_pic['size'] || $share_other_pic['size'] || $lose_no_img['size'] || $lose_yes_img['size'] || $last_lottery_img['size'] || $button_pic['size'] || $bg_img['size'] || $lottery_pic['size'] || $click_lottery_pic['size'] || $unclick_lottery_pic['size'] || $update_warning_pic['size'] || $rule_pic['size'] || $no_prize_pic['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'ap_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'ap_imgurl_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_weixin_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'share_other_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_no_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lose_yes_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'last_lottery_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'button_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'bg_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'click_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'unclick_lottery_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'update_warning_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'rule_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				),
				'no_prize_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				)
			));
			$lists=$this->_uploadapk(0, $config);
			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'ap_pic') {
                    $data['ap_imgurl'] = $val['url'];
                }
                if ($val['post_name'] == 'ap_imgurl_bg') {
                    $data['ap_imgurl_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'share_weixin_pic') {
                    $data['share_weixin_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'share_other_pic') {
                    $data['share_other_pic'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_no_img') {
                    $data['lose_no_img'] = $val['url'];
                }
				if ($val['post_name'] == 'lose_yes_img') {
                    $data['lose_yes_img'] = $val['url'];
                }
				if ($val['post_name'] == 'last_lottery_img') {
                    $data['last_lottery_img'] = $val['url'];
                }
				if ($val['post_name'] == 'button_pic') {
                    $button_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'bg_img') {
                    $bg_pic_url = $val['url'];
                }
				if ($val['post_name'] == 'lottery_pic') {
                    $lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'click_lottery_pic') {
                    $click_lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'unclick_lottery_pic') {
                    $unclick_lottery_pic = $val['url'];
                }
				if ($val['post_name'] == 'update_warning_pic') {
                    $update_warning_pic = $val['url'];
                }
				if ($val['post_name'] == 'rule_pic') {
                    $rule_pic = $val['url'];
                }
				if ($val['post_name'] == 'no_prize_pic') {
                    $no_prize_pic = $val['url'];
                }
            }
		}
		

		$data['bg_color'] = $bg_color;
		//if($bg_img['size']){
			$data['bg_img'] = $bg_pic_url;
		//}

		if($lottery_style != 2){
			$data['lottery_pic'] = $lottery_pic;
		}elseif($lottery_style == 2){
			$sudoku_color = $_POST['sudoku_color'];
			$data['sudoku_color'] = $sudoku_color;
		}
		if($click_lottery_pic){
			$data['click_lottery_pic'] = $click_lottery_pic;
		}
		if($unclick_lottery_pic){
			$data['unclick_lottery_pic'] = $unclick_lottery_pic;
		}
		if($update_warning_pic){
			$data['update_warning_pic'] = $update_warning_pic;
		}
		if($rule_pic['size']){
			$data['rule_pic'] = $rule_pic;
		}
		if($no_prize_pic['size']){
			$data['no_prize_pic'] = $no_prize_pic;
		}
		
		$data['button_color'] = $button_color;
		if($button_pic['size']){
			$data['button_pic'] = $button_pic_url;
		}
		
		if($ap_type == 2){
			$get_lottery_type_arr = $_POST['get_lottery_type'];
			if(!$get_lottery_type_arr){
				$this -> error("请选择抽奖机会获取方式");
			}else{
				$data['get_lottery_type'] = array_sum($get_lottery_type_arr);
			}
			$gift_type = $_POST['gift_type'];
			if(!$gift_type){
				$this -> error("请选择礼包发放优先级");
			}else{
				$data['gift_type'] = $gift_type;
			}
		}
		$data['ap_utm'] = time();
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);

		if($result){
			$this -> writelog("已编辑id为{$id}的通用活动页面", 'sj_activity_page',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/activity_list/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	function del_activity(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的通用活动页面", 'sj_activity_page',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/activity_list/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}
	}

	function soft_list(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> select(); 
		$soft_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $category_result[0]['id'],'status' => 1)) -> order('rank') -> select();

		for($i=1;$i<=count($soft_result);$i++){
			$rank[] = $i;
		}
		$this -> assign("soft_result",$soft_result);
		$this -> assign('id',$id);
		$this -> assign("p",$p);
		$this -> assign("lr",$lr);
		$this -> assign("rank",$rank);
		$this -> assign("referer_url",base64_decode($_GET['referer_url']));
		$this -> display();
	}

	function add_soft_show(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> select();

		$result = $model -> table('sj_actives_soft') -> where(array('category_id' => $category_result[0]['id'],'status' => 1)) -> count();

		for($i=1;$i<=$result;$i++){
			$rank[] = $i;
		}
		$this -> assign('rank',$rank);
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('id',$id);
		$this -> display();
	}
	
	function add_soft_do(){
		$model = new Model();
		$id = $_POST['id'];
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		$category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> select();
		if(!$category_result){
			$data['active_id'] = $id;
			$data['category_name'] = '通用活动分类';
			$data['status'] = 1;
			$data['rank'] = 1;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$add_category = $model -> table('sj_actives_category') -> add($data);
			if($add_category){
				$category_id = $add_category;
			}else{
				$this -> error("添加分类失败");
			}
		}else{
			$category_id = $category_result[0]['id'];
		}
		
		$soft_name = trim($_POST['soft_name']);
		$package = trim($_POST['package']);
		$rank = $_POST['rank'];
		$recomment = $_POST['recomment'];
		$award_recomment = $_POST['award_recomment'];
		if($this -> strlen_az($soft_name) > 20 || !$soft_name){
			$this -> error("请填写10字以内的软件名称");
		}
		$active_have_been_where['_string'] = "soft_name = '{$soft_name}' and category_id = {$category_id} and status = 1";
		$active_have_been = $model -> table('sj_actives_soft') -> where($active_have_been_where) -> select();
		
		if($active_have_been){
			$this -> error("该活动已存在此软件名称");
		}
		$my_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$my_package){
			$this -> error("该软件包名不存在");
		}
		$active_have_been_where_package['_string'] = "package = '{$package}' and category_id = {$category_id} and status = 1";
		$active_have_been_package = $model -> table('sj_actives_soft') -> where($active_have_been_where_package) -> select();
		if($active_have_been_package){
			$this -> error("该活动已存在此软件包名");
		}
		
		$rank_where['_string'] = "category_id = {$category_id} and rank >= {$rank} and status = 1";
		$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
		if($rank_result){
			foreach($rank_result as $key => $val){
				$rank_data = array(
					'rank' => $val['rank'] + 1
				);
				$change_result = $model -> table('sj_actives_soft') -> where(array('id' => $val['id'])) -> save($rank_data);
			}
		}
		$data = array(
			'soft_name' => $soft_name,
			'package' => $package,
			'page_id' => $id,
			'rank' => $rank,
			'recomment' => $recomment,
			'award_recomment' => $award_recomment,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'category_id' => $category_id
		);
		$result = $model -> table('sj_actives_soft') -> add($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已添加页面id为{$result}的软件", 'sj_actives_soft',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/soft_list/id/{$id}/p/{$p}/lr/{$lr}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}


	function edit_soft_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		$rank_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $result[0]['category_id'],'status' => 1)) -> count();
		for($i=1;$i<=$rank_result;$i++){
			$rank[] = $i;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}

	function edit_soft_do(){
		$model = new Model();
		$id = $_POST['id'];
		$soft_name = trim($_POST['soft_name']);
		$package = trim($_POST['package']);
		$rank = $_POST['rank'];
		$recomment = $_POST['recomment'];
		$award_recomment = $_POST['award_recomment'];
		$my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		$page_result = $model -> table('sj_actives_category') -> where(array('id' => $my_result[0]['category_id'])) -> select();
		//该活动所拥有的分类
		$active_have_been_where['_string'] = "soft_name = '{$soft_name}' and category_id = {$my_result[0]['category_id']} and status = 1 and id != {$id}";
		$active_have_been = $model -> table('sj_actives_soft') -> where($active_have_been_where) -> select();
		
		if($active_have_been){
			$this -> error("该活动已存在此软件名称");
		}
		
		if($this -> strlen_az($soft_name) > 20 || !$soft_name){
			$this -> error("请填写10字以内的软件名称");
		}

		$my_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$my_package){
			$this -> error("该软件包名不存在");
		}
		
		$active_have_been_where_package['_string'] = "package = '{$package}' and category_id = {$my_result[0]['category_id']} and status = 1 and id != {$id}";
		$active_have_been_package = $model -> table('sj_actives_soft') -> where($active_have_been_where_package) -> select();
		if($active_have_been_package){
			$this -> error("该活动已存在此软件包名");
		}
		
		if($rank != $my_result[0]['rank']){
			if($rank < $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank >= {$rank}  and rank < {$my_result[0]['rank']} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] + 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}elseif($rank > $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] - 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}
		}
		
		$data = array(
			'soft_name' => $soft_name,
			'package' => $package,
			'rank' => $rank,
			'recomment' => $recomment,
			'award_recomment' => $award_recomment,
			'update_tm' => time(),
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_actives_soft',$data,$model);
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已编辑id为{$id}的活动分类软件".$log_result, 'sj_actives_soft',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/soft_list/id/{$page_result[0]['active_id']}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	
	function change_soft_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		$category_result = $model -> table('sj_actives_category') -> where(array('id' => $my_result[0]['category_id'],'status' => 1)) -> select();
		if($rank != $my_result[0]['rank']){
			if($rank < $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank >= {$rank}  and rank < {$my_result[0]['rank']} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] + 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}elseif($rank > $my_result[0]['rank']){
				$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
				$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
				if($rank_result){
					foreach($rank_result as $key => $val){
						$rank_data = array(
							'rank' => $val['rank'] - 1
						);
						$rank_where['_string'] = "id = {$val['id']}";
						$change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
					}
				}
			}
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$log_result = $this -> logcheck(array('id' => $id),'sj_actives_soft',array('rank' => $rank),$model);
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save(array('rank' => $rank));
		if($result){
			$this -> writelog("已编辑id为{$id}的活动分类软件".$log_result, 'sj_actives_soft',$id,__ACTION__ ,'','edit');
			$this -> assign("jumpUrl","/index.php/Sendnum/CoActivity/soft_list/id/{$category_result[0]['active_id']}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_soft(){
		$model = new Model();
		$id = $_GET['id'];
		$my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
		$category_result = $model -> table('sj_actives_category') -> where(array('id' => $my_result[0]['category_id'],'status' => 1)) -> select();
		$rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and status = 1";
		$rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
		foreach($rank_result as $key => $val){
			$rank_data = array(
				'rank' => $val['rank'] - 1
			);
			$rank_result = $model -> table('sj_actives_soft') -> where(array('id' => $val['id'])) -> save($rank_data);
		}
		$result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save(array('status' => 0));
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		if($result){
			$this -> writelog("已删除id为{$id}活动分类软件", 'sj_actives_soft',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/soft_list/id/{$category_result[0]['active_id']}/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
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
	//常规充值活动--列表
	function routine_activity(){
		$model = D('sendNum.Activity');		
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
		}			
		list($result,$total, $page) = $model->get_routine_activity();
		$this -> assign('list',$result);
		$this -> assign('page', $page->show());		
		$this -> assign('total',$total);	
		//var_dump(base64_encode('/index.php/Sendnum/CoActivity/routine_activity'));
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/routine_activity'));		
		$this ->display();			
		//var_dump($result,$total, $page);
	}
	//常规充值活动--添加活动页面
	function add_routine_activity(){
		if($_POST){
			$model = D('sendNum.Activity');		
			$ret = $model -> routine_activity_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
				$res = $model -> activityAdd($data);	
				if($res){
					$this -> writelog("添加了【常规充值返利活动】活动页面id为{$res}活动页面", 'sj_activity',$res,__ACTION__ ,'','add');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/routine_activity");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/add_routine_activity");
					$this -> error("操作失败");
				}
			}else{
				$this -> error($ret['msg']);
			}
		}else{
			$this -> assign("referer_url",$_SERVER['HTTP_REFERER']);
			$this ->display();	
		}		
	}
	//常规充值活动--编辑活动页面
	function edit_routine_activity(){
		$model = D('sendNum.Activity');	
		if($_POST){
			$ret = $model -> routine_activity_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
				//	var_dump($config);
					$list = $this->_uploadapk(0, $config);
				//	var_dump($list);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
		//	var_dump($data);exit;
				$where = array('ap_id' => $_POST['ap_id']);
				$res = $model -> updatePage($where,$data);	
				if($res){
					$this -> writelog("编辑了【常规充值返利活动】活动页面id为{$_POST['ap_id']}活动页面", 'sj_activity_page',$_POST['ap_id'],__ACTION__ ,'','edit');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/routine_activity");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/edit_routine_activity");
					$this -> error("操作失败");
				}
			}else{
				$this -> error($ret['msg']);
			}			
		}else{			
			$where = array(
				'status' => 1,
				'ap_id' => $_GET['ap_id']
			);
			$list = $model -> where($where)->find();
			$list['ap_imgurl'] = IMGATT_HOST . $list['ap_imgurl'];
			$list['ap_desc'] = IMGATT_HOST . $list['ap_desc'];
			$list['soft_bg'] = IMGATT_HOST . $list['soft_bg'];
			$list['ap_imgurl_bg'] = IMGATT_HOST . $list['ap_imgurl_bg'];
			$list['bottom_color'] = IMGATT_HOST . $list['bottom_color'];
			$this -> assign('list',$list);	
			$this -> assign("referer_url",base64_decode($_GET['referer_url']));
			$this ->display('add_routine_activity');	
		}
		
	}
	//常规充值活动--删除
	function del_routine_activity(){
		$model = D('sendNum.Activity');	
		$where = array('ap_id'=>$_GET['ap_id']);
		$ret = $model -> activityDel($where);
		if($ret){
			$this -> writelog("删除了【常规充值返利活动】活动页面id为{$_GET['ap_id']}活动页面", 'sj_activity_page',$_GET['ap_id'],__ACTION__ ,'','del');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}

	} 
	//软件管理批量添加
	function batch_add_soft(){
		$model = D('sendNum.Activity');	
		$res = $model -> post_batch_soft();
		if($res['code'] == 1){
			$pkg_str = implode(';',$res['pkg_arr']);
			$this -> writelog("软件管理页面-批量添加了分类id为{$res['category_id']},包名为{$pkg_str}的软件。", 'sj_activity_page',$res['category_id'],__ACTION__ ,'','add');
		}
		$res['referer_url'] = $_GET['referer_url'];
		exit(json_encode($res));
	}
	//排行榜活动---列表
	function ranking_activity(){
		$model = D('sendNum.Activity');	
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
		}		
		list($result,$total, $page) = $model->get_routine_activity(8);
		$this -> assign('list',$result);
		$this -> assign('page', $page->show());		
		$this -> assign('total',$total);	
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/ranking_activity'));	
		$this ->display();		
	}
	//排行榜活动---添加活动页面
	function ranking_activity_add(){
		if($_POST){
			$model = D('sendNum.Activity');		
			$ret = $model -> ranking_activity_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url_original'];				
					}
				}	
				$res = $model -> activityAdd($data);	
				if($res){
					 $model -> del_pic_url($_POST['del_prize_pic_str']);
					$this -> writelog("添加了【充值消费类】活动页面id为{$res}", 'sj_activity',$res,__ACTION__ ,'','add');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/ranking_activity");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl', 'javascript:history.back(-1);');
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this -> assign("referer_url",$_SERVER['HTTP_REFERER']);
			$this ->display();	
		}		
	}	
	//排行榜活动--编辑活动页面
	function ranking_activity_edit(){
		$model = D('sendNum.Activity');	
		if($_POST){
			$ret = $model -> ranking_activity_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url_original'];				
					}
				}	
				$where = array('ap_id' => $_POST['ap_id']);
				$log = $this -> logcheck($where,'sj_activity_page',$data,$model);	
				$res = $model -> updatePage($where,$data);	
				if($res){
					$model -> del_pic_url($_POST['del_prize_pic_str']);
					$this -> writelog("编辑了【充值消费类】活动页面id为{$_POST['ap_id']}".$log,'sj_activity_page',$_POST['ap_id'],__ACTION__ ,'','edit');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/ranking_activity");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/ranking_activity_edit");
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{			
			$where = array(
				'status' => 1,
				'ap_id' => $_GET['ap_id']
			);
			$list = $model -> where($where)->find();
			$activity_list = $model->table('sj_activity')->where(array('activity_page_id'=>$_GET['ap_id']))->field('id,start_tm,end_tm')->find();
			
			$activity_model = D('Sj.CoActivity');
			$prize = $activity_model -> table('gm_lottery_prize') -> where(array('aid' => $activity_list['id'])) -> find();
			if($prize){
				$list['is_prize'] = 1;
			}else{
				$list['is_prize'] = 2;
			}
			$list['start_tm'] = $activity_list['start_tm'];
			//处理连续抽奖数据
			if($list['ap_desc']){
				$list = array_merge($list,json_decode($list['ap_desc'],true));
				unset($list['ap_desc']);
			}
			$this -> assign('IMGATT_HOST',IMGATT_HOST);	
			$this -> assign('list',$list);	
			$this -> assign("referer_url",base64_decode($_GET['referer_url']));
			$this ->display('ranking_activity_add');	
		}
	}
	//排行榜活动--删除
	function ranking_activity_del(){
		$model = D('sendNum.Activity');	
		$where = array('ap_id'=>$_GET['ap_id']);
		$ret = $model -> activityDel($where);
		$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/ranking_activity");
		if($ret){
			$this -> writelog("删除了【排行榜活动】活动页面id为{$_GET['ap_id']}活动页面", 'sj_activity_page',$_GET['ap_id'],__ACTION__ ,'','del');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	} 	
	//奖品轮播图上传
	public function pub_prize_pic(){
		$array = array('jpg','gif','png','bmp','jpeg');
		$ytypes = $_FILES['prize_pic_up']['name'];
		$info = pathinfo($ytypes);
		$type =  $info['extension'];//获取文件件扩展名
		if(!in_array($type,$array)){
			$return = array(
				'code' => 0,
				'msg' => "上传格式错误",
			);
			exit(json_encode($return));			
		}	
		$src = $_FILES['prize_pic_up']['tmp_name'];
		$prize_pic = getimagesize($src);
		if($prize_pic[0] != 577){
			$return = array(
				'code' => 0,
				'msg' => "奖品轮播图：请上传宽度为577的图片",
			);
			exit(json_encode($return));
		}	
		list($msec,$sec) = explode(' ',microtime());
		$msec = substr($msec,2);
		$dir_img = UPLOAD_PATH. '/img/'. date("Ym/d/");
		if(!is_dir($dir_img)) {
			if(!mkdir($dir_img,0777,true)) {
				$return = array(
					'code' => 0,
					'msg' => "创建目录失败{$dir_img}",
				);
				exit(json_encode($return));		 
			}
		}
		$dst = $dir_img.$msec.'.'.$type;
		if(move_uploaded_file($src,$dst)) {
			$path = str_replace(UPLOAD_PATH,'',$dst);
		} 
        $return	= array(
			'code' =>1,
			'img_path' => IMGATT_HOST . $path,
			'path' => $path,
			'name' => $msec,
		);
		exit(json_encode($return));
	}
	//对外预约活动
	public function booking_activity(){
		$model = D('sendNum.Activity');	
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
		}		
		list($result,$total, $page) = $model->get_routine_activity(10);
		$this -> assign('list',$result);
		$this -> assign('page', $page->show());		
		$this -> assign('total',$total);	
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/booking_activity'));	
		$this ->display();			
	}
	//对外预约活动、添加
	public function booking_activity_add(){
		if($_POST){
			$model = D('sendNum.Activity');		
			$ret = $model -> booking_activity_post();
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
				$res = $model -> activityAdd($data);	
				if($res){
					$this -> writelog("添加了【对外预约】活动页面id为{$res}活动页面", 'sj_activity_page',$res,__ACTION__ ,'','add');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/booking_activity");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl', 'javascript:history.back(-1);');
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this -> assign("referer_url",$_SERVER['HTTP_REFERER']);
			$this ->display();	
		}		
	}
	//对外预约活动、编辑
	public function booking_activity_edit(){
		$model = D('sendNum.Activity');	
		if($_POST){
			$ret = $model -> booking_activity_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];				
					}
				}	
				$where = array('ap_id' => $_POST['ap_id']);
				$log = $this -> logcheck($where,'sj_activity_page',$data,$model);		
				$res = $model -> updatePage($where,$data);	
				if($res){
					$this -> writelog("编辑了【对外预约活动】活动页面id为{$_POST['ap_id']}活动页面".$log, 'sj_activity_page',$_POST['ap_id'],__ACTION__ ,'','edit');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/booking_activity");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/booking_activity_edit/ap_id/".$_POST['ap_id']);
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{			
			$where = array(
				'status' => 1,
				'ap_id' => $_GET['ap_id']
			);
			$list = $model -> where($where)->find();
			$activity_list = $model->table('sj_activity')->where(array('activity_page_id'=>$_GET['ap_id']))->field('id,start_tm,end_tm')->find();
			
			$activity_model = D('Sj.CoActivity');
			$prize = $activity_model -> table('gm_lottery_prize') -> where(array('aid' => $activity_list['id'])) -> find();
			$this -> assign('IMGATT_HOST',IMGATT_HOST);	
			$this -> assign('list',$list);	
			$this -> assign("referer_url",base64_decode($_GET['referer_url']));
			$this ->display('booking_activity_add');	
		}
	}
	//对外预约活动、删除
	function booking_activity_del(){
		$model = D('sendNum.Activity');	
		$where = array('ap_id'=>$_GET['ap_id']);
		$ret = $model -> activityDel($where);
		$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/booking_activity");
		if($ret){
			$this -> writelog("删除了【对外预约活动】活动页面id为{$_GET['ap_id']}活动页面", 'sj_activity_page',$_GET['ap_id'],__ACTION__ ,'','del');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	} 	
	//运营预下载活动---列表
	function pre_down_operation_list(){
		$model = D('sendNum.Activity');	
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
		}		
		list($result,$total, $page) = $model->get_routine_activity(11);
		$this -> assign('list',$result);
		$this -> assign('page', $page->show());		
		$this -> assign('total',$total);	
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/pre_down_operation_list'));	
		$this ->display();		
	}
	//运营预下载活动---添加活动页面
	function pre_down_operation_add(){
		if($_POST){
			$model = D('sendNum.Activity');		
			$ret = $model -> pre_down_operation_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				$pic_arr = array("prize_pic_up1","prize_pic_up2","prize_pic_up3","prize_pic_up4","prize_pic_up5","prize_pic_up6");
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					$gm_pic_arr = array();
					foreach($list['image'] as $val) {
						if(in_array($val['post_name'],$pic_arr)){
							$gm_pic_arr[$val['post_name']] = $val['url_original'];
						}else{
							$data[$val['post_name']] = $val['url_original'];	
						}
					}
				}
				$data['prize_pic'] = json_encode($gm_pic_arr);		
				$res = $model -> activityAdd($data);	
				if($res){	
					if(!empty($data['yes_marquee'])){
						$task_client = get_task_client();
						$task_data = array();
						$task_data['type'] = 2;
						$task_data['url'] = $data['yes_marquee'];
						$task_data['ap_id'] = $res;
						$task_client->doBackground('activity_video_change_format', json_encode($task_data));
					}				
					$this -> writelog("添加了【运营预下载活动】活动页面id为{$res}活动页面","sj_activity_page",$res,__ACTION__ ,'','add');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/pre_down_operation_list");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl', 'javascript:history.back(-1);');
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this -> assign("referer_url",$_SERVER['HTTP_REFERER']);
			$this ->display();	
		}		
	}	
	//运营预下载活动--编辑活动页面
	function pre_down_operation_edit(){
		$model = D('sendNum.Activity');	
		if($_POST){
			$ret = $model -> pre_down_operation_post();	
			if($ret['code'] == 1){
				$gm_pic_arr = json_decode($_POST['prize_pic'],true);			
				$config = $ret['config'];
				$data = $ret['data'];
				$pic_arr = array("prize_pic_up1","prize_pic_up2","prize_pic_up3","prize_pic_up4","prize_pic_up5","prize_pic_up6");
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						if(in_array($val['post_name'],$pic_arr)){
							$pathinfo = pathinfo(UPLOAD_PATH .$gm_pic_arr[$val['post_name']]);
							$pic_name = basename($gm_pic_arr[$val['post_name']],".png");
							unlink($pathinfo['dirname']."/".$pic_name.".".$pathinfo['extension']);
							unlink($pathinfo['dirname']."/".$pic_name."_o.".$pathinfo['extension']);
							unlink($pathinfo['dirname']."/".$pic_name."_O.PNG");
							unset($gm_pic_arr[$val['post_name']]);
							$gm_pic_arr[$val['post_name']] = $val['url_original'];
						}else{
							$data[$val['post_name']] = $val['url_original'];
						}
					}
				}	
				$where = array('ap_id' => $_POST['ap_id']);
				$log = $this -> logcheck($where,'sj_activity_page',$data,$model);	
				$data['prize_pic'] = json_encode($gm_pic_arr);				
				$res = $model -> updatePage($where,$data);	
				if($res){
					if(!empty($data['yes_marquee'])){
						$task_client = get_task_client();
						$task_data = array();
						$task_data['type'] = 2;
						$task_data['url'] = $data['yes_marquee'];
						$task_data['ap_id'] = $_POST['ap_id'];
						$task_client->doBackground('activity_video_change_format', json_encode($task_data));
					}						
					$this -> writelog("编辑了【运营预下载活动】活动页面id为{$_POST['ap_id']}活动页面".$log,"sj_activity_page",$_POST['ap_id'],__ACTION__ ,'','edit');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/pre_down_operation_list");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/pre_down_operation_edit");
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{			
			$where = array(
				'status' => 1,
				'ap_id' => $_GET['ap_id']
			);
			$list = $model -> where($where)->find();
			$activity_list = $model->table('sj_activity')->where(array('activity_page_id'=>$_GET['ap_id']))->field('id,start_tm,end_tm')->find();
			
			$activity_model = D('Sj.CoActivity');
			$prize = $activity_model -> table('pre_down_operation_prize') -> where(array('aid' => $activity_list['id'])) -> find();
			if($prize){
				$list['is_prize'] = 1;
			}else{
				$list['is_prize'] = 2;
			}
			$list['start_tm'] = $activity_list['start_tm'];
			$this -> assign('IMGATT_HOST',IMGATT_HOST);	
			$prize_pic = json_decode($list['prize_pic'],true);		
			$this -> assign('list',array_merge($list,$prize_pic));	
			$this -> assign("referer_url",base64_decode($_GET['referer_url']));
			$this ->display('pre_down_operation_add');	
		}
	}
	//运营预下载活动--删除
	function pre_down_operation_del(){
		$model = D('sendNum.Activity');	
		$where = array('ap_id'=>$_GET['ap_id']);
		$ret = $model -> activityDel($where);
		$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/pre_down_operation_list");
		if($ret){
			$this -> writelog("删除了【运营预下载活动】活动页面id为{$_GET['ap_id']}活动页面","sj_activity_page",$_GET['ap_id'],__ACTION__ ,'','del');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	} 	
	//签到活动---列表
	function sign_activity(){
		$model = D('sendNum.Activity');	
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
		}			
		list($result,$total, $page) = $model->get_routine_activity(12);
		$this -> assign('list',$result);
		$this -> assign('page', $page->show());		
		$this -> assign('total',$total);	
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/sign_activity'));	
		$this ->display();		
	}
	//签到活动---添加活动页面
	function sign_activity_add(){
		if($_POST){
			$model = D('sendNum.Activity');		
			$ret = $model -> sign_activity_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url_original'];				
					}
				}	
				$res = $model -> activityAdd($data);	
				if($res){
					$this -> writelog("添加了【签到活动】活动页面id为{$res}活动页面", 'sj_activity',$res,__ACTION__ ,'','add');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/sign_activity");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl', 'javascript:history.back(-1);');
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this -> assign("referer_url",$_SERVER['HTTP_REFERER']);
			$this ->display();	
		}		
	}	
	//签到活动--编辑活动页面
	function sign_activity_edit(){
		$model = D('sendNum.Activity');	
		if($_POST){
			$ret = $model -> sign_activity_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url_original'];				
					}
				}	
				$where = array('ap_id' => $_POST['ap_id']);
				$log = $this -> logcheck($where,'sj_activity_page',$data,$model);		
				$res = $model -> updatePage($where,$data);	
				if($res){
					$this -> writelog("编辑了【签到活动】活动页面id为{$_POST['ap_id']}活动页面".$log,'sj_activity_page',$_POST['ap_id'],__ACTION__ ,'','edit');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/sign_activity/ap_id/".$_POST['ap_id']);
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/sign_activity_edit");
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{			
			$where = array(
				'status' => 1,
				'ap_id' => $_GET['ap_id']
			);
			$list = $model -> where($where)->find();
			$list['download_bgcolor'] = explode(",",$list['download_bgcolor']);
			$this -> assign('IMGATT_HOST',IMGATT_HOST);	
			$this -> assign('list',$list);	
			$this -> assign("referer_url",base64_decode($_GET['referer_url']));
			$this ->display('sign_activity_add');	
		}
	}
	//签到活动--删除
	function sign_activity_del(){
		$model = D('sendNum.Activity');	
		$where = array('ap_id'=>$_GET['ap_id']);
		$ret = $model -> activityDel($where);
		$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/sign_activity");
		if($ret){
			$this -> writelog("删除了【签到活动】活动页面id为{$_GET['ap_id']}活动页面", 'sj_activity_page',$_GET['ap_id'],__ACTION__ ,'','del');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	}

	// 评论可回复活动列表
	function comment_reply_list()
	{
		$model = D('sendNum.Activity');	
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
		}			
		list($result,$total, $page) = $model->get_routine_activity(14);
		$this -> assign('result',$result);
		$this -> assign('page', $page->show());		
		$this -> assign('total',$total);	
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/comment_reply_list'));
		$this ->display();	
	}

	// 添加评论可回复活动页面
	function add_comment_reply_show()
	{
		$this->display();
	}

	// 添加评论可回复活动
	function add_comment_reply_do()
	{
		$model = D('sendNum.Activity');		
		$ret = $model -> comment_reply_post();
		if($ret['code'] == 1){
			$config = $ret['config'];
			$data = $ret['data'];
			if (!empty($config['multi_config'])) {
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $val) {
					$data[$val['post_name']] = $val['url'];				
				}
			}
			$award_color = '';
			for($i=0;$i<6;$i++){
				if($data['award_color_'.$i]){
					$award_color .= $data['award_color_'.$i].',';
					unset($data['award_color_'.$i]);
				}
			}
			$award_color = rtrim($award_color, ',');
			$data['award_color'] = $award_color;
			$res = $model -> activityAdd($data);
			if($res){
				$this -> writelog("添加了【评论可回复活动】活动页面id为{$res}活动页面", 'sj_activity_page',$res,__ACTION__ ,'','add');
				$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/comment_reply_list");
				$this -> success("操作成功");
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error("操作失败");
			}
		}else{
			$this -> assign('jumpUrl', 'javascript:history.back(-1);');
			$this -> error($ret['msg']);
		}
	}

	// 编辑评论可回复活动页面
	function edit_comment_reply_show()
	{
		$model = D('sendNum.Activity');
		$where = array(
			'status' => 1,
			'ap_id' => $_GET['ap_id']
		);
		$list = $model -> where($where)->find();
		$this -> assign('list',$list);
		$this -> assign('IMGATT_HOST',IMGATT_HOST);
		$this -> display('add_comment_reply_show');
	}

	// 编辑评论可回复活动
	function edit_comment_reply_do()
	{
		$model = D('sendNum.Activity');
		$ret = $model -> comment_reply_post();	
		if($ret['code'] == 1){
			$config = $ret['config'];
			$data = $ret['data'];
			if (!empty($config['multi_config'])) {
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $val) {
					$data[$val['post_name']] = $val['url'];				
				}
			}
			$award_color = '';
			$have_award_file = 0;
			for($i=0;$i<6;$i++){
				if($data['award_color_'.$i]){
					$have_award_file = 1;
					$award_color .= $data['award_color_'.$i].',';
					unset($data['award_color_'.$i]);
				}
			}
			$award_color = rtrim($award_color, ',');
			if ($have_award_file) {
				$data['award_color'] = $award_color;
			}
			$where = array('ap_id' => $_POST['ap_id']);
			$log = $this -> logcheck($where,'sj_activity_page',$data,$model);		
			$res = $model -> updatePage($where,$data);
			if($res){
				$this -> writelog("编辑了【可评论回复活动】活动页面id为{$_POST['ap_id']}活动页面".$log,'sj_activity_page',$_POST['ap_id'],__ACTION__ ,'','edit');
				$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/comment_reply_list/ap_id/".$_POST['ap_id']);
				$this -> success("操作成功");
			}else{
				$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/edit_comment_reply_show");
				$this -> error("操作失败");
			}
		}else{
			$this -> assign('jumpUrl', 'javascript:history.back(-1);');
			$this -> error($ret['msg']);
		}
	}

	// 删除评论可回复活动
	function del_comment_reply()
	{
		$model = D('sendNum.Activity');	
		$where = array('ap_id'=>$_GET['ap_id']);
		$ret = $model -> activityDel($where);
		$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/comment_reply_list");
		if($ret){
			$this -> writelog("删除了【评论可回复活动】活动页面id为{$_GET['ap_id']}活动页面", 'sj_activity_page',$_GET['ap_id'],__ACTION__ ,'','del');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	}
	//会员折扣活动
	function vip_discount(){
		$model = D('sendNum.Activity');	
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
		}			
		list($result,$total, $page) = $model->get_routine_activity(15);
		$this -> assign('list',$result);
		$this -> assign('page', $page->show());		
		$this -> assign('total',$total);	
		$this -> assign("referer_url",base64_encode('/index.php/Sendnum/CoActivity/vip_discount'));
		$this ->display();			
	}
	//会员折扣活动---添加活动页面
	function vip_discount_add(){
		if($_POST){
			$model = D('sendNum.Activity');		
			$ret = $model -> vip_discount_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url_original'];					
					}
				}	
				$res = $model -> activityAdd($data);	
				if($res){
					$this -> writelog("添加了【会员折扣活动】活动页面id为{$res}活动页面", 'sj_activity',$res,__ACTION__ ,'','add');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/vip_discount");
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl', 'javascript:history.back(-1);');
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this -> assign("referer_url",$_SERVER['HTTP_REFERER']);
			$this ->display();	
		}		
	}	
	//会员折扣活动--编辑活动页面
	function vip_discount_edit(){
		$model = D('sendNum.Activity');	
		if($_POST){
			$ret = $model -> vip_discount_post();	
			if($ret['code'] == 1){
				$config = $ret['config'];
				$data = $ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url_original'];
					}
				}	
				$where = array('ap_id' => $_POST['ap_id']);
				$log = $this -> logcheck($where,'sj_activity_page',$data,$model);		
				$res = $model -> updatePage($where,$data);	
				if($res){
					$this -> writelog("编辑了【会员折扣活动】活动页面id为{$_POST['ap_id']}活动页面".$log,'sj_activity_page',$_POST['ap_id'],__ACTION__ ,'','edit');
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/vip_discount/ap_id/".$_POST['ap_id']);
					$this -> success("操作成功");
				}else{
					$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/vip_discount");
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}			
		}else{			
			$where = array(
				'status' => 1,
				'ap_id' => $_GET['ap_id']
			);
			$list = $model -> where($where)->find();
			$list['download_bgcolor'] = explode(",",$list['download_bgcolor']);
			$this -> assign('IMGATT_HOST',IMGATT_HOST);	
			$this -> assign('list',$list);	
			$this -> assign("referer_url",base64_decode($_GET['referer_url']));
			$this ->display('vip_discount_add');	
		}
	}
	//会员折扣活动--删除
	function vip_discount_del(){
		$model = D('sendNum.Activity');	
		$where = array('ap_id'=>$_GET['ap_id']);
		$ret = $model -> activityDel($where);
		$this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/vip_discount");
		if($ret){
			$this -> writelog("删除了【会员折扣活动】活动页面id为{$_GET['ap_id']}活动页面", 'sj_activity_page',$_GET['ap_id'],__ACTION__ ,'','del');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	}
}
