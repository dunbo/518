<?php
/**
 * 软件官方审核
 */
class ActivityModel extends Model {

	protected $trueTableName = 'sj_activity_page';
	private $image_width=640;
	public function produceList() {
		$result = $this-> where(array('activate_type' => 1,'status' => 1)) -> order('ap_ctm desc')->select();
		return $result;
	}

	public function getActivityOne($id) {
		$result = $this->where('ap_id='.$id)->select();	
		return $result;
	}

	public function activityAdd($insertData) {
		$result = $this->add($insertData);	
		return $result;
	}

	public function activityEdit($where, $editData) {
		$result = $this->where($where)->save($editData);	
		return $result;
	}

	public function activityDel($where) {
		$result = $this->where($where)->save(array('status' => 0));	
		return $result;
	}

	public function updatePage($where, $data) {
		$result = $this->where($where)->save($data);	
		return $result;
	}

	public function checkPackage($package) {
		if ($this->query("select * from sj_soft where status = 1 and package = '".$package."'")) {
			return 'true';	
		} else {
			return 'false';	
		}	
	}

	public function getSoft($package) {
		$result = $this->query("select * from sj_soft where status = 1 and package = '".$package."'");
		return $result[0]['softname'];
	}
	//获取常规返利活动页面数据
	public function get_routine_activity($activate_type){
		$where = array(
			'activate_type' => $activate_type ? $activate_type : 7,
			'status' => 1
		);
		if(isset($_GET['ap_name'])){
			$where['ap_name'] = array('like','%'.$_GET['ap_name'].'%');
		}
		if(isset($_GET['ap_id'])){
			$where['ap_id'] = $_GET['ap_id'];
		}
		$limit = 10;
		$total = $this->where($where)->count();
		import('@.ORG.Page2');
		$param = http_build_query($_GET);
		$Page = new Page($total,$limit,$param);		
		$field = "ap_id,ap_name,ap_imgurl,ap_type,ap_link,ap_ctm,ap_utm";
		$list = $this-> where($where) -> order('ap_utm desc')->field($field)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k => $v){
			$list[$k]['ap_utm'] = date("Y-m-d H:i",$v['ap_utm']);
			$list[$k]['ap_ctm'] = date("Y-m-d H:i",$v['ap_ctm']);
		}
		return array($list,$total,$Page);
	}
	//
	public function routine_activity_post(){
		if(empty($_POST['ap_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写活动名称",
			);
			return $return;				
		}
		$where = array(
			'ap_name' => $_POST['ap_name'],
			//'ap_type' => $_POST['ap_type'],
		);
		if($_POST['ap_id']){
			$where['ap_id'] = array('exp',"!={$_POST['ap_id']}");
		}
		$res = $this->table('sj_activity_page')->where($where)->field('ap_name')->find();
		//var_dump($res);
		if($res){
			$return = array(
				'code' => 0,
				'msg' => "活动名称已存在",
			);
			return $return;				
		}
		if($_POST['ap_type'] == 2){
			/*
			if(empty($_POST['winning_comment']) || mb_strlen($_POST['winning_comment'],'utf8') > 5){
				$return = array(
					'code' => 0,
					'msg' => "打开按键不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			if(empty($_POST['download_comment']) || mb_strlen($_POST['download_comment'],'utf8') > 5){
				$return = array(
					'code' => 0,
					'msg' => "安装按键不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			if(empty($_POST['button_comment']) || mb_strlen($_POST['button_comment'],'utf8') > 5){
				$return = array(
					'code' => 0,
					'msg' => "下载按键不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			*/
			if(empty($_POST['share_text']) || mb_strlen($_POST['share_text'],'utf8') > 5){
				$return = array(
					'code' => 0,
					'msg' => "换一换按键不可为空并按键名称不超过5个",
				);
				return $return;				
			}
		}
		if(!$_POST['ap_id']){
			if(empty($_FILES['ap_imgurl']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传活动头图",
				);
				return $return;				
			}
			if($_POST['ap_type'] == 2 && empty($_FILES['soft_bg']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传推荐游戏背景图",
				);
				return $return;				
			}
			if($_POST['ap_type'] == 2 && empty($_FILES['ap_imgurl_bg']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传返利说明图",
				);
				return $return;				
			}
			if(empty($_FILES['bottom_color']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上轮播背景图",
				);
				return $return;				
			}
		}
		$config = array();
		$path = date("Ym/d/");
		if($_FILES['ap_imgurl']['tmp_name']){
			$ap_imgurl = getimagesize($_FILES['ap_imgurl']['tmp_name']);
			if($ap_imgurl[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "图片：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['ap_imgurl'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['soft_bg']['tmp_name']){
			$soft_bg = getimagesize($_FILES['soft_bg']['tmp_name']);
			if($soft_bg[0] != $this->image_width || $soft_bg[1] != 530){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width},高度为530的图片",
				);
				return $return;
			}
			$config['multi_config']['soft_bg'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['ap_imgurl_bg']['tmp_name']){
			$ap_imgurl_bg = getimagesize($_FILES['ap_imgurl_bg']['tmp_name']);
			if($ap_imgurl_bg[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['ap_imgurl_bg'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['bottom_color']['tmp_name']){
			$bottom_color = getimagesize($_FILES['bottom_color']['tmp_name']);
			if($bottom_color[0] != $this->image_width || $bottom_color[1] != 425){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width},高度为425的图片",
				);
				return $return;
			}
			$config['multi_config']['bottom_color'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['ap_desc']['tmp_name']){
			$ap_desc = getimagesize($_FILES['ap_desc']['tmp_name']);
			if($ap_desc[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['ap_desc'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		$data = array(
			'status' => 1,
			'activate_type' => 7,
			'ap_link' => ACTIVITY_URL . "/lottery/routine_rebate_index.php",
			'ap_name' => $_POST['ap_name'],
			'ap_type' => $_POST['ap_type'],
			'ap_notice' => $_POST['ap_notice'] ? $_POST['ap_notice'] : '',
			'warning_bgcolor' => $_POST['warning_bgcolor'] ? $_POST['warning_bgcolor'] : '',
			'winning_comment' => $_POST['winning_comment'] ? $_POST['winning_comment'] : '',
			'bg_color' => $_POST['bg_color'] ? $_POST['bg_color'] : '',
			'download_comment' => $_POST['download_comment'] ? $_POST['download_comment'] : '',
			'download_bgcolor' => $_POST['download_bgcolor'] ? $_POST['download_bgcolor'] : '',
			'button_comment' => $_POST['button_comment'] ? $_POST['button_comment'] : '',
			'share_bgcolor' => $_POST['share_bgcolor'] ? $_POST['share_bgcolor'] : '',
			'share_text' => $_POST['share_text'] ? $_POST['share_text'] : '',		
			'share_other_pic' => $_POST['share_other_pic'] ? $_POST['share_other_pic'] : '',
			'lost_no_desc' => $_POST['lost_no_desc'] ? $_POST['lost_no_desc'] : '',
			'lose_yes_desc' => $_POST['lose_yes_desc'] ? $_POST['lose_yes_desc'] : '',
			'last_lottery_desc' => $_POST['last_lottery_desc'] ? $_POST['last_lottery_desc'] : '',
			'download_config' => $_POST['download_config'],
		);
		$time = time();
		if($_POST['ap_id']){
			$data['ap_utm'] = $time;
		}else{
			$data['ap_ctm'] = $time;
			$data['ap_utm'] = $time;
		}
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return; 
	}
	function post_batch_soft(){
		$time = time();
		$where = array(	'status' => 1 );
		if($_GET['ap_id']){
			$where['active_id'] = $_GET['ap_id'];
		}
		if($_GET['category_id']){
			$where['id'] = $_GET['category_id'];
		}
		$category_result = $this -> table('sj_actives_category') -> where($where)->field('id') -> find();
		if(!$category_result){
			$data['active_id'] =  $_GET['ap_id'];
			$data['category_name'] = '通用活动分类';
			$data['status'] = 1;
			$data['rank'] = 1;
			$data['create_tm'] = $time;
			$data['update_tm'] = $time;
			$add_category = $this -> table('sj_actives_category') -> add($data);
			if($add_category){
				$category_id = $add_category;
			}else{
				$return = array('code' =>0,	'msg' =>'添加分类失败');
				return $return;					
			}
		}else{
			$category_id = $category_result['id'];
		}				
		//把文件中的数据取出转字符编码				
		if (!empty($_FILES['csv']['tmp_name'])){		
			$data = file_get_contents($_FILES['csv']['tmp_name']);
			//判断是否是utf-8编辑
			if(mb_check_encoding($data,"utf-8") != true){
				$data = iconv("gbk","utf-8", $data);
			}
			$data = str_replace("\r\n","\n",$data);	
			$data_arr = explode("\n", $data);
			$data_arr = array_unique($data_arr);
			$where = array(
				'status' => 1,
				'hide' => 1,
				'package' => array('in',$data_arr),
			);
			$soft_list  = get_table_data($where,"sj_soft","package","package,softname");
			$where = array(
				'status' => 1,
				'category_id' => $category_id,
				'package' => array('in',$data_arr),
			);
			$actives_soft  = get_table_data($where,"sj_actives_soft","package","package,soft_name");
			//$data_arr = array_slice($data_arr,500);
			$err = '';
			$pkg_arr = array();
			foreach ($data_arr as $key => $val) {
				if($key == 0) continue;
				if(empty($val)) continue;
				if(!$soft_list[$val]){//不在上架列表的包名
					$err .= "包名".$val."不在上架列表;<br>\r\n";  
					continue;
				}
				if($actives_soft[$val]){
					$err .= "包名".$val."请不要重复添加;<br>\r\n";  
					continue;
				}
				$pkg_arr[] = $val;
			}
			if($err != ''){
				$return = array('code' =>0,	'msg' => $err);
				return $return;
			}
			unset($data_arr);
			$total = $this -> table('sj_actives_soft') -> where("category_id={$category_id} and status=1")->count();
			$sql_str = '';
			$rank = $total;
			foreach($pkg_arr as $k => $v){	
				$rank++;	
				$sql_str .= ",('{$soft_list[$v]['softname']}','{$v}','{$_GET['ap_id']}','{$rank}','{$time}','{$time}','{$category_id}')";
			}
			$sql_str =  substr($sql_str,1);
			$sql = "INSERT INTO sj_actives_soft (`soft_name`,`package`,`page_id`,`rank`,`create_tm`,`update_tm`,`category_id`) VALUES ".$sql_str;
			$affect = $this -> query($sql);
			$return = array('code' =>1,	'msg' => '添加成功','category_id'=>$category_id,'pkg_arr'=>$pkg_arr);
			return $return;
		}else{
			$return = array('code' =>0,	'msg' => '请上传文件');
			return $return;
		}
	}
	//排行榜活动---添加、编辑活动
	public function ranking_activity_post(){
		//表单验证===开始
		if(empty($_POST['ap_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写活动名称",
			);
			return $return;				
		}
		$where = array(
			'ap_name' => $_POST['ap_name'],
		);
		if($_POST['ap_id']){
			$where['ap_id'] = array('exp',"!={$_POST['ap_id']}");
		}
		$res = $this->table('sj_activity_page')->where($where)->field('ap_name')->find();
		//var_dump($res);
		if($res){
			$return = array(
				'code' => 0,
				'msg' => "活动名称已存在",
			);
			return $return;				
		}
		if($_POST['ap_type'] == 2){	
			if(empty($_POST['prize_text_color'])){
				$return = array(
					'code' => 0,
					'msg' => "请填写抽大奖标题",
				);
				return $return;				
			}			
			if($_POST['must_share'] == 1 && empty($_POST['yes_marquee'])){
				$return = array(
					'code' => 0,
					'msg' => "有排行状态跑马灯不可为空",
				);
				return $return;				
			}
			if($_POST['must_share'] == 1 && empty($_POST['no_marquee'])){
				$return = array(
					'code' => 0,
					'msg' => "无排行状态跑马灯不可为空",
				);
				return $return;				
			}
			if($_POST['back_top'] == 1){
				if(empty($_POST['describe']) || mb_strlen($_POST['describe'],'utf8') > 5){
					$return = array(
						'code' => 0,
						'msg' => "查看详情按键不可为空并按键名称不超过5个",
					);
					return $return;				
				}
			}
			if(empty($_POST['button_pic'])){
				$return = array(
					'code' => 0,
					'msg' => "未登录时文字说明不可为空",
				);
				return $return;				
			}		
			if(empty($_POST['change_button']) || mb_strlen($_POST['change_button'],'utf8') > 5){
				$return = array(
					'code' => 0,
					'msg' => "修改按钮不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			if($_POST['must_share'] == 1 && (empty($_POST['title']) || mb_strlen($_POST['title'],'utf8') > 5)){
				$return = array(
					'code' => 0,
					'msg' => "推荐游戏文字不可为空",
				);
				return $return;				
			}
			if($_POST['must_share'] == 1 && empty($_POST['is_score'])){
				$return = array(
					'code' => 0,
					'msg' => "排行榜显示数量不可为空",
				);
				return $return;				
			}
			if($_POST['must_share'] == 1 &&  (empty($_POST['uppage']) || mb_strlen($_POST['uppage'],'utf8') > 5)){
				$return = array(
					'code' => 0,
					'msg' => "排行榜上一页按钮不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			if($_POST['must_share'] == 1 &&  (empty($_POST['nextpage']) || mb_strlen($_POST['nextpage'],'utf8') > 5)){
				$return = array(
					'code' => 0,
					'msg' => "排行榜下一页按钮不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			if(empty($_POST['back_button']) || mb_strlen($_POST['back_button'],'utf8') > 5){
				$return = array(
					'code' => 0,
					'msg' => "活动说明页面返回按键不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			if(empty($_POST['prize_back']) || mb_strlen($_POST['prize_back'],'utf8') > 5){
				$return = array(
					'code' => 0,
					'msg' => "我的奖品页面返回按键不可为空并按键名称不超过5个",
				);
				return $return;				
			}
			if($_POST['change_switch'] == 1 && (empty($_POST['share_text']) || mb_strlen($_POST['share_text'],'utf8') > 5)){
				$return = array(
					'code' => 0,
					'msg' => "换一换按键不可为空并按键名称不超过5个",
				);
				return $return;				
			}			

			if($_POST['share_add'] == 1){//开启抽奖
				if(empty($_POST['my_prize_button']) || mb_strlen($_POST['my_prize_button'],'utf8') > 5){
					$return = array(
						'code' => 0,
						'msg' => "我的奖品按键不可为空并按键名称不超过5个",
					);
					return $return;				
				}				
				if(empty($_POST['draw_button_text']) || mb_strlen($_POST['draw_button_text'],'utf8') > 5){
					$return = array(
						'code' => 0,
						'msg' => "我要抽奖按钮不可为空按键名称不超过5个",
					);
					return $return;				
				}				
				if(empty($_POST['no_winning_marquee'])){
					$return = array(
						'code' => 0,
						'msg' => "未有人中奖跑马灯不可为空",
					);
					return $return;				
				}
				if(empty($_POST['get_lottery_type'])){
					$return = array(
						'code' => 0,
						'msg' => "获得抽奖次数条件不可为空",
					);
					return $return;				
				}
				if(empty($_POST['free_day_switch'])){
					$return = array(
						'code' => 0,
						'msg' => "单日单用户最多获取抽奖次数不可为空",
					);
					return $return;				
				}
				if(empty($_POST['not_winning_tips']) || mb_strlen($_POST['not_winning_tips'],'utf8') > 40){
					$return = array(
						'code' => 0,
						'msg' => "未中奖弹窗提示语不可为空提示语不超过40个",
					);
					return $return;				
				}			
				if(empty($_POST['close_button']) || mb_strlen($_POST['close_button'],'utf8') > 5){
					$return = array(
						'code' => 0,
						'msg' => "弹窗内关闭按键不可为空并按键名称不超过5个",
					);
					return $return;				
				}				
				if(empty($_POST['again']) || mb_strlen($_POST['again'],'utf8') > 5){
					$return = array(
						'code' => 0,
						'msg' => "弹窗内再抽一次按键不可为空并按键名称不超过5个",
					);
					return $return;				
				}				
				if(empty($_POST['submit_button']) || mb_strlen($_POST['submit_button'],'utf8') > 5){
					$return = array(
						'code' => 0,
						'msg' => "弹窗内提交按键不可为空并按键名称不超过5个",
					);
					return $return;				
				}						
				if($_POST['share_switch'] == 1){//开启推荐软件
					$_POST['down_addlotterynum_switch'] = 0;
					$_POST['like_limit']= 0;
					//去掉下载增加抽奖机会 需求：518后台——活动管理排行榜活动优化http://pms.anzhi.com/index.php?m=story&f=view&storyID=5648
// 					if($_POST['down_addlotterynum_switch'] == 1 && empty($_POST['like_limit'])){
// 						$return = array(
// 							'code' => 0,
// 							'msg' => "单日下载抽奖次数上限不可为空",
// 						);
// 						return $return;				
// 					}				
				}
			}

		}else if($_POST['ap_type'] == 3){
			if(empty($_POST['ap_award'])){
				$return = array(
					'code' => 0,
					'msg' => "中奖奖品列表不可为空",
				);
				return $return;				
			}				
		}
        if(!$_POST['ap_id']){
			if($_POST['dep_type'] == 1){ //分享开关
				if(empty($_FILES['update_warning_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传分享微信图",
					);
					return $return;				
				}
				if(empty($_FILES['unclick_lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传其他分享图",
					);
					return $return;				
				}				
            }

			if(empty($_FILES['ap_imgurl']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传活动头图",
				);
				return $return;				
			}
			if(empty($_FILES['desc_color']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传活动说明背景图",
				);
				return $return;	
			}		
			if($_POST['must_share'] == 1 && empty($_FILES['ranking_pic1']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传排行榜背景图",
				);
				return $return;	
			}
			if($_POST['ap_type'] == 2){
				if( $_POST['back_top'] == 1 && empty($_FILES['ap_rule']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传活动说明页面背景图",
					);
					return $return;	
				}					
				if($_POST['must_share'] == 1 && empty($_FILES['ranking_no_pic1']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传排行榜没信息时背景图",
					);
					return $return;				
				}										
				if(empty($_POST['prize_text_color'])){
					$return = array(
						'code' => 0,
						'msg' => "请填写抽大奖标题",
					);
					return $return;				
				}
				if(empty($_FILES['prize_bg_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传我的奖品页面背景图",
					);
					return $return;				
				}
				if($_POST['share_add'] == 1){//开启抽奖
					if(empty($_FILES['lose_yes_img']['tmp_name'])){
						$return = array(
							'code' => 0,
							'msg' => "请上传抽奖背景图（上）",
						);
						return $return;				
					}
					if(empty($_FILES['last_lottery_img']['tmp_name'])){
						$return = array(
							'code' => 0,
							'msg' => "请上传抽奖背景图（下）",
						);
						return $return;				
					}
					if(empty($_FILES['popup_bg_pic']['tmp_name'])){
						$return = array(
							'code' => 0,
							'msg' => "请上传弹出框背景图",
						);
						return $return;				
					}
					if($_POST['share_switch'] == 1){//开启推荐软件						
						if(empty($_FILES['soft_bg']['tmp_name'])){
							$return = array(
								'code' => 0,
								'msg' => "请上传推荐游戏背景图片",
							);
							return $return;				
						}
					}
				}
				if(empty($_FILES['bg_img']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传收货地址背景图",
					);
					return $return;				
				}
			}else if($_POST['ap_type'] == 3){
				if(empty($_FILES['share_weixin_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传页尾中奖背景图",
					);
					return $return;				
				}			
			}
		}
		//表单验证===结束
		unset($_FILES['prize_pic_up']);
		$path = date("Ym/d/");
		if($_FILES['ap_imgurl']['tmp_name']){
			$ap_imgurl = getimagesize($_FILES['ap_imgurl']['tmp_name']);
			if($ap_imgurl[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动头图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['desc_color']['tmp_name']){
			$desc_color = getimagesize($_FILES['desc_color']['tmp_name']);
			if($desc_color[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动说明背景图：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['ap_rule']['tmp_name']){
			$ap_rule = getimagesize($_FILES['ap_rule']['tmp_name']);
			if($ap_rule[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "活动说明页面背景图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['ranking_pic1']['tmp_name']){
			$ranking_pic1 = getimagesize($_FILES['ranking_pic1']['tmp_name']);
			if($ranking_pic1[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "排行榜背景图:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['ranking_no_pic1']['tmp_name']){
			$ranking_no_pic1 = getimagesize($_FILES['ranking_no_pic1']['tmp_name']);
			if($ranking_no_pic1[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "排行榜没信息时背景图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['bottom_color']['tmp_name']){
			$bottom_color = getimagesize($_FILES['bottom_color']['tmp_name']);
			if($bottom_color[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "奖品轮播背景图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}

		if($_FILES['prize_bg_pic']['tmp_name']){
			$prize_bg_pic = getimagesize($_FILES['prize_bg_pic']['tmp_name']);
			if($prize_bg_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "我的奖品页面背景图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['lose_yes_img']['tmp_name']){
			$lose_yes_img = getimagesize($_FILES['lose_yes_img']['tmp_name']);
			if($lose_yes_img[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "抽奖背景图（上）:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}			
		}
		if($_FILES['last_lottery_img']['tmp_name']){
			$last_lottery_img = getimagesize($_FILES['last_lottery_img']['tmp_name']);
			if($last_lottery_img[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "抽奖背景图（下）:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
        }

		if($_FILES['rank_lottery_desc_pic']['tmp_name']){
			$rank_lottery_desc_pic = getimagesize($_FILES['rank_lottery_desc_pic']['tmp_name']);
			if($rank_lottery_desc_pic[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "抽奖说明背景图:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}

		if($_FILES['popup_bg_pic']['tmp_name']){
			$popup_bg_pic = getimagesize($_FILES['popup_bg_pic']['tmp_name']);
			if($popup_bg_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "弹出框背景图：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['soft_bg']['tmp_name']){
			$soft_bg = getimagesize($_FILES['soft_bg']['tmp_name']);
			if($soft_bg[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
                
		if($_FILES['bg_img']['tmp_name']){
			$bg_img = getimagesize($_FILES['bg_img']['tmp_name']);
			if($bg_img[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['sudoku_color']['tmp_name']){
			$sudoku_color = getimagesize($_FILES['sudoku_color']['tmp_name']);
			if($sudoku_color[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['share_weixin_pic']['tmp_name']){
			$share_weixin_pic = getimagesize($_FILES['share_weixin_pic']['tmp_name']);
			if($share_weixin_pic[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		$config = $this -> pic_config();
        $rank_lottery_desc_text = htmlspecialchars($_POST['rank_lottery_desc_text']);
		$rank_lottery_desc_text = str_replace("\\","",$rank_lottery_desc_text);
		$data = array(
			'status' => 1,
			'activate_type' => 8,
			'ap_link' => ACTIVITY_URL ."/lottery/ranking/index.php",
			'rank_lottery_desc_text' => $_POST['rank_lottery_desc_text'] ? $rank_lottery_desc_text : '',
		);
		unset($_POST['__hash__']);
		foreach($_POST as $k => $v){
			if(in_array($k,array("yes_marquee","rule_pic","ap_award"))){
				$data[$k] = trim($v);
			}else{
				$data[$k] = $v;
			}
		}	
		$time = time();
		if($_POST['ap_id']){
			$data['ap_utm'] = $time;
		}else{
			$data['ap_ctm'] = $time;
			$data['ap_utm'] = $time;
		}
		unset($data['del_prize_pic_str']);
		$new_data = $this -> continuity_data($data);
				
		$return = array(
			'code' => 1,
			'data' => $new_data,
			'config' => $config,
		);
		return $return; 
	}
	//排行榜处理10连抽100连抽数据
	function continuity_data($data){
		$new_arr = array();
		foreach($data as $k => $v){
			$exp_arr = explode("_",$k);
			if($exp_arr[0] == 'continuity'){
				$new_arr[$k] =  $v;
				unset($data[$k]);
			}
		}
		$data['ap_desc'] = json_encode($new_arr);
		return $data;
	}
	function del_pic_url($data){
		$data = explode(',',$data);
		foreach($data as $v){
			 @unlink(UPLOAD_PATH.$v);
		}
	}
	function get_activity_page($where,$limit){
		$total =  $this -> where($where) -> count();
		import('@.ORG.Page2');
		$param = http_build_query($_GET);
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');		
		$result = $this-> where($where)-> limit($Page->firstRow . ',' . $Page->listRows)  -> order('ap_ctm desc')->select();
		return array($result,$total,$Page);
	}
	//对外预约活动---添加、编辑活动
	public function booking_activity_post(){
		//表单验证===开始
		if(empty($_POST['ap_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写活动名称",
			);
			return $return;				
		}
		//对外预约增加分享
		if(empty($_POST['title'])){
			$return = array(
				'code' => 0,
				'msg' => "分享标题不能为空",
			);
			return $return;				
		}
		else
		{
			if(mb_strlen($_POST['title'],'utf-8')>26)
			{
				$return = array(
					'code' => 0,
					'msg' => "分享标题不能超过26个字符",
				);
				return $return;	
			}
		}
		if(empty($_POST['share_text'])){
			$return = array(
				'code' => 0,
				'msg' => "分享文案不能为空",
			);
			return $return;				
		}
		else
		{
			if(mb_strlen($_POST['share_text'],'utf-8')>26)
			{
				$return = array(
					'code' => 0,
					'msg' => "分享文案不能超过26个字符",
				);
				return $return;	
			}
		}
			
		if($_POST['share_switch']!=1)
		{
			if(empty($_POST['telephone_warning'])){
				$return = array(
					'code' => 0,
					'msg' => "手机号输入框文字不可为空",
				);
				return $return;				
			}
		}
		
		$where = array(
			'ap_name' => $_POST['ap_name'],
		);
		if($_POST['ap_id']){
			$where['ap_id'] = array('exp',"!={$_POST['ap_id']}");
			$where['status'] = 1;
		}
		$res = $this->table('sj_activity_page')->where($where)->field('ap_name')->find();
		//var_dump($res);
		if($res){
			$return = array(
				'code' => 0,
				'msg' => "活动名称已存在",
			);
			return $return;				
		}
		if(!$_POST['ap_id']){
			if($_POST['share_switch']==1)
			{
				//分享模板
				if(empty($_FILES['popup_bg_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传下载按钮背景图片",
					);
					return $return;				
				}	
				if(empty($_FILES['lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传下载按钮引导遮层图片",
					);
					return $return;				
				}
				if(empty($_FILES['unclick_lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传分享按钮图片",
					);
					return $return;				
				}
				if(empty($_FILES['update_warning_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传分享按钮引导遮层图片",
					);
					return $return;				
				}
			}
			else
			{
				//预约模板
				if($_POST['change_switch'] == 2 && empty($_FILES['button_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传预约按键背景图片",
					);
					return $return;					
				}		
			}	
			if(empty($_FILES['rule_pic']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传分享朋友圈解析图片",
				);
				return $return;				
			}
			/*if(empty($_FILES['no_prize_pic']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传分享给朋友解析图片",
				);
				return $return;				
			}*/
			
			if($_POST['back_top']==1)
			{
				if(empty($_FILES['bg_color']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传背景音乐",
					);
					return $return;				
				}				
				if(empty($_FILES['bg_img']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传背景音乐图标",
					);
					return $return;				
				}			
				if(empty($_FILES['soft_bg']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传背景音乐图标(关闭)",
					);
					return $return;				
				}			
			}
			/*if(empty($_FILES['nextpage']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传下拉箭头图片",
				);
				return $return;				
			}*/
		}
		if($_FILES['bg_color']['tmp_name']){
			$file_type = explode('.', $_FILES['bg_color']['name']);
			if ($file_type[1] != 'mp3') {
				$return = array(
					'code' => 0,
					'msg' => "请上传mp3格式文件",
				);
				return $return;				
			}	
			$size = $_FILES['bg_color']['size'];	
			if($size > 10*1024*1024){
				$return = array(
					'code' => 0,
					'msg' => "音乐最大支持10M",
				);
				return $return;						
			}
		}
		//表单验证===结束
		$config = array();
		$path = date("Ym/d/");
		if($_FILES['lose_no_img']['tmp_name']){
			$lose_no_img = getimagesize($_FILES['lose_no_img']['tmp_name']);
			if($lose_no_img[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动图片1：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['lose_no_img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['lost_no_desc']['tmp_name']){
			$lost_no_desc = getimagesize($_FILES['lost_no_desc']['tmp_name']);
			if($lost_no_desc[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "渐变图层1：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['lost_no_desc'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['lose_yes_img']['tmp_name']){
			$lose_yes_img = getimagesize($_FILES['lose_yes_img']['tmp_name']);
			if($lose_yes_img[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动图片2：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['lose_yes_img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['lose_yes_desc']['tmp_name']){
			$lose_yes_desc = getimagesize($_FILES['lose_yes_desc']['tmp_name']);
			if($lose_yes_desc[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "渐变图层2：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['lose_yes_desc'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['last_lottery_img']['tmp_name']){
			$last_lottery_img = getimagesize($_FILES['last_lottery_img']['tmp_name']);
			if($last_lottery_img[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动图片3：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['last_lottery_img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['last_lottery_desc']['tmp_name']){
			$last_lottery_desc = getimagesize($_FILES['last_lottery_desc']['tmp_name']);
			if($last_lottery_desc[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "渐变图层3：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['last_lottery_desc'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['share_weixin_pic']['tmp_name']){
			$share_weixin_pic = getimagesize($_FILES['share_weixin_pic']['tmp_name']);
			if($share_weixin_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动图片4：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['share_weixin_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['share_other_pic']['tmp_name']){
			$share_other_pic = getimagesize($_FILES['share_other_pic']['tmp_name']);
			if($share_other_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "渐变图层4：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['share_other_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['ranking_no_pic1']['tmp_name']){
			$ranking_no_pic1 = getimagesize($_FILES['ranking_no_pic1']['tmp_name']);
			if($ranking_no_pic1[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动图片5：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['ranking_no_pic1'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['ranking_pic1']['tmp_name']){
			$ranking_pic1 = getimagesize($_FILES['ranking_pic1']['tmp_name']);
			if($ranking_pic1[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "渐变图层5：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['ranking_pic1'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['prize_pic']['tmp_name']){
			$prize_pic = getimagesize($_FILES['prize_pic']['tmp_name']);
			if($prize_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动图片6：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['prize_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['prize_bg_pic']['tmp_name']){
			$prize_bg_pic = getimagesize($_FILES['prize_bg_pic']['tmp_name']);
			if($prize_bg_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "渐变图层6：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['prize_bg_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		//分享模板新增加的图片判断
		if($_POST['share_switch']==1)
		{
			if($_FILES['popup_bg_pic']['tmp_name']){
				$config['multi_config']['popup_bg_pic'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
			if($_FILES['lottery_pic']['tmp_name']){
				$lottery_pic = getimagesize($_FILES['lottery_pic']['tmp_name']);
				if($lottery_pic[0] != $this->image_width ){
					$return = array(
						'code' => 0,
						'msg' => "下载按钮引导遮层图片：请上传宽度为{$this->image_width}的图片",
					);
					return $return;
				}
				$config['multi_config']['lottery_pic'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
			if($_FILES['unclick_lottery_pic']['tmp_name']){
				$config['multi_config']['unclick_lottery_pic'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
			if($_FILES['update_warning_pic']['tmp_name']){
				$update_warning_pic = getimagesize($_FILES['update_warning_pic']['tmp_name']);
				if($update_warning_pic[0] != $this->image_width ){
					$return = array(
						'code' => 0,
						'msg' => "分享页面引导遮层图片：请上传宽度为{$this->image_width}的图片",
					);
					return $return;
				}
				$config['multi_config']['update_warning_pic'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
		}
		else
		{
			//预约按键背景图片
			if($_FILES['button_pic']['tmp_name']){
				$config['multi_config']['button_pic'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
		}
		if($_FILES['rule_pic']['tmp_name']){
			$rule_pic = getimagesize($_FILES['rule_pic']['tmp_name']);
			/*if($rule_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "分享到朋友圈解析图片：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}*/
			$config['multi_config']['rule_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		/*if($_FILES['no_prize_pic']['tmp_name']){
			$no_prize_pic = getimagesize($_FILES['no_prize_pic']['tmp_name']);
			if($no_prize_pic[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "分享给朋友解析图片：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['no_prize_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}*/
			
		if($_POST['back_top']==1)
		{
			//背景音乐图标
			if($_FILES['bg_img']['tmp_name']){
				$config['multi_config']['bg_img'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
			//背景音乐图标（关闭）
			if($_FILES['soft_bg']['tmp_name']){
				$config['multi_config']['soft_bg'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
		}
		//下拉箭头图片
		if($_FILES['nextpage']['tmp_name']){
			$config['multi_config']['nextpage'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
		{
			$ap_link = 'http://m.test.anzhi.com/lottery/booking.php';
		}
		elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
		{
			$ap_link = 'http://9.m.anzhi.com/lottery/booking.php';
		}
		else 
		{
			$ap_link = 'http://promotion.anzhi.com/lottery/booking.php';
		}
		$data = array(
			'status' => 1,
			'activate_type' => 10,
			'ap_link' => $ap_link,
			'ap_name' => $_POST['ap_name'],
			'telephone_warning' => $_POST['telephone_warning'],
			'button_color' => $_POST['button_color'],
			'change_switch' => $_POST['change_switch'],
			'share_switch' => $_POST['share_switch'],
			'title' => $_POST['title'],
			'share_text' => $_POST['share_text'],
			'back_top' => $_POST['back_top'],
		);
		
		//背景音乐
		if($_FILES['bg_color']['tmp_name']){
			$dir_img = UPLOAD_PATH . '/mp3/'.$path;
			if(!is_dir($dir_img)) {
				if(!mkdir($dir_img,0777,true)) {
					//创建mp3目录{$dir_img}失败
					$return = array(
						'code' => 0,
						'msg' => "创建mp3目录{$dir_img}失败",
					);
					return $return;							
				}
			}
			list($msec,$sec) = explode(' ',microtime());
				$msec = substr($msec,2);
				$dst = $dir_img.$msec.'.mp3';
				if(move_uploaded_file($_FILES['bg_color']['tmp_name'],$dst)) {
					$data['bg_color'] = str_replace(UPLOAD_PATH,'',$dst);
				} 	
		}		
		$time = time();
		if($_POST['ap_id']){
			$data['ap_utm'] = $time;
		}else{
			$data['ap_ctm'] = $time;
			$data['ap_utm'] = $time;
		}
		unset($_FILES['bg_color']);
		unset($config['multi_config']['bg_color']);
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return; 
	}	
	//运营预下载活动---添加、编辑活动
	public function pre_down_operation_post(){
		//表单验证===开始
		if(empty($_POST['ap_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写活动名称",
			);
			return $return;				
		}
		$where = array(
			'ap_name' => $_POST['ap_name'],
			'status' =>1
		);
		if($_POST['ap_id']){
			$where['ap_id'] = array('exp',"!={$_POST['ap_id']}");
		}
		$res = $this->table('sj_activity_page')->where($where)->field('ap_name')->find();
		//var_dump($res);
		if($res){
			$return = array(
				'code' => 0,
				'msg' => "活动名称已存在",
			);
			return $return;				
		}
		if($_POST['ap_type'] == 2){	
			//@
			if(!$_POST['ap_id'] && $_POST['share_add'] == 1){
				if(empty($_FILES['lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传抽奖区背景图片",
					);
					return $return;				
					}
				if(empty($_FILES['click_lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传点击抽奖图",
					);
					return $return;				
				}
				if(empty($_FILES['unclick_lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传禁止抽奖图",
					);
					return $return;				
				}
			}
			if(!$_POST['ap_id'] && $_POST['share_switch'] == 1){
				if(empty($_FILES['share_weixin_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传分享微信图",
					);
					return $return;				
				}
				if(empty($_FILES['share_other_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传其他分享图",
					);
					return $return;				
				}				
			}
		}else if($_POST['ap_type'] == 3){
			//@	
		}
		if(!$_POST['ap_id']){
			if(empty($_FILES['ap_imgurl']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传活动头图",
				);
				return $return;				
			}
			if($_POST['ap_type'] == 2 && $_POST['is_score']==2 && empty($_FILES['soft_bg']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传推荐软件标题（背景图）",
				);
				return $return;	
			}		
		}
		if($_FILES['yes_marquee']['tmp_name']){
			$file_type = explode('.', $_FILES['yes_marquee']['name']);
			if ($file_type[1] != 'mp4') {
				$return = array(
					'code' => 0,
					'msg' => "请上传mp4格式文件",
				);
				return $return;				
			}	
		}		
		//表单验证===结束

		
		$config = array();
		$path = date("Ym/d/");
		if($_FILES['ap_imgurl']['tmp_name']){
			$ap_imgurl = getimagesize($_FILES['ap_imgurl']['tmp_name']);
			if($ap_imgurl[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动头图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['ap_imgurl'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['bg_img']['tmp_name']){
			$bg_img = getimagesize($_FILES['bg_img']['tmp_name']);
			if($bg_img[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动说明背景图：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['bg_img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['button_pic']['tmp_name']){
			$button_pic = getimagesize($_FILES['button_pic']['tmp_name']);
			if($button_pic[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "活动说明页面背景图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}
			$config['multi_config']['button_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['describe']['tmp_name']){
			$config['multi_config']['describe'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['rule_pic']['tmp_name']){
			$config['multi_config']['rule_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['button_comment']['tmp_name']){
			$config['multi_config']['button_comment'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['soft_bg']['tmp_name']){
			$config['multi_config']['soft_bg'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}

		if($_FILES['lottery_pic']['tmp_name']){
			$config['multi_config']['lottery_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['click_lottery_pic']['tmp_name']){
			$config['multi_config']['click_lottery_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['unclick_lottery_pic']['tmp_name']){
			$config['multi_config']['unclick_lottery_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['my_prize_text_color']['tmp_name']){
			$config['multi_config']['my_prize_text_color'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['draw_font_color']['tmp_name']){
			$config['multi_config']['draw_font_color'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['lose_no_img']['tmp_name']){			
			$config['multi_config']['lose_no_img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['lose_yes_img']['tmp_name']){
			$config['multi_config']['lose_yes_img'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['share_weixin_pic']['tmp_name']){
			$config['multi_config']['share_weixin_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['share_other_pic']['tmp_name']){
			$config['multi_config']['share_other_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['prize_bg_pic']['tmp_name']){
			$config['multi_config']['prize_bg_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['draw_button_color']['tmp_name']){
			$config['multi_config']['draw_button_color'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['prize_text_color']['tmp_name']){
			$config['multi_config']['prize_text_color'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['no_prize_pic']['tmp_name']){
			$config['multi_config']['no_prize_pic'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['ranking_no_pic1']['tmp_name']){
			$config['multi_config']['ranking_no_pic1'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['ranking_pic1']['tmp_name']){
			$config['multi_config']['ranking_pic1'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['nextpage']['tmp_name']){
			$config['multi_config']['nextpage'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		if($_FILES['uppage']['tmp_name']){
			$config['multi_config']['uppage'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}
		for($i=1;$i<=6;$i++){
			if($_FILES['prize_pic_up'.$i]['tmp_name']){
				$config['multi_config']['prize_pic_up'.$i] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}
		}		
		if($_FILES['ap_imgurl_bg']['tmp_name']){
			$config['multi_config']['ap_imgurl_bg'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' => 1024 * 200,
			);			
		}		
		$data = array(
			'status' => 1,
			'activate_type' => 11,
			'ap_link' => ACTIVITY_URL ."/lottery/pre_down_operation/index.php",
			'ap_name' => $_POST['ap_name'],
			'ap_type' => $_POST['ap_type'],
			'dep_type' => $_POST['dep_type'],
			'bg_color' => $_POST['bg_color'] ? $_POST['bg_color'] : '',
			'back_top' => $_POST['back_top'] ? $_POST['back_top'] : 0,
			'button_color' => $_POST['button_color'] ? $_POST['button_color'] : '',
			'is_score' => $_POST['is_score'] ,
			'desc_color' => $_POST['desc_color'] ? $_POST['desc_color'] : '',
			'mean_text_color' => $_POST['mean_text_color'] ? $_POST['mean_text_color'] : '',
			'ap_rule' => $_POST['ap_rule'] ? $_POST['ap_rule'] : '',
			'rule_color' => $_POST['rule_color'] ? $_POST['rule_color'] : '',
			'download_bgcolor' => $_POST['download_bgcolor'] ? $_POST['download_bgcolor'] : '',
			'share_add' => $_POST['share_add'] ? $_POST['share_add'] : 0,
			'get_lottery_type' => $_POST['get_lottery_type'] ? $_POST['get_lottery_type'] : 1,
			'lottery_style' => $_POST['lottery_style'] ? $_POST['lottery_style'] : 1,
			'version_code' => $_POST['version_code'] ? $_POST['version_code'] : '',
			'my_prize_button_color' => $_POST['my_prize_button_color'] ? $_POST['my_prize_button_color'] : '',
			'my_prize_button' => $_POST['my_prize_button'] ? $_POST['my_prize_button'] : '',
			'change_button_color' => $_POST['change_button_color'] ? $_POST['change_button_color'] : '',
			'change_button' => $_POST['change_button'] ? $_POST['change_button'] : '',
			'free_day_switch' => $_POST['free_day_switch'] ? $_POST['free_day_switch'] : 0,
			'lottery_num_limit' => $_POST['lottery_num_limit'] ? $_POST['lottery_num_limit'] : 0,
			'is_repeat' => $_POST['is_repeat'] ? $_POST['is_repeat'] : 0,
			'lost_no_desc' => $_POST['lost_no_desc'] ? $_POST['lost_no_desc'] : '',
			'lose_yes_desc' => $_POST['lose_yes_desc'] ? $_POST['lose_yes_desc'] : '',
			'not_winning_tips' => $_POST['not_winning_tips'] ? $_POST['not_winning_tips'] : '',
			'alert_color' => $_POST['alert_color'] ? $_POST['alert_color'] : '',
			'alert_button_color' => $_POST['alert_button_color'] ? $_POST['alert_button_color'] : '',
			'share_switch' => $_POST['share_switch'] ? $_POST['share_switch'] : 0,
			'share_add_all' => $_POST['share_add_all'] ? $_POST['share_add_all'] : 0,
			'share_text' => $_POST['share_text'] ? $_POST['share_text'] : '',
			'prize_back' => $_POST['prize_back'] ? $_POST['prize_back'] : '',
			'prize_back_text_color' => $_POST['prize_back_text_color'] ? $_POST['prize_back_text_color'] : '',
			'prize_bg_color' => $_POST['prize_bg_color'] ? $_POST['prize_bg_color'] : '',
			'prize_back_color' => $_POST['prize_back_color'] ? $_POST['prize_back_color'] : '',
			'no_prize_text' => $_POST['no_prize_text'] ? $_POST['no_prize_text'] : '',
			'title' => $_POST['title'] ? $_POST['title'] : '',
			'info_color' => $_POST['info_color'] ? $_POST['info_color'] : '',
			'uppage_color' => $_POST['uppage_color'] ? $_POST['uppage_color'] : '',
			'nextpage_color' => $_POST['nextpage_color'] ? $_POST['nextpage_color'] : '',
			'first_text_color' => $_POST['first_text_color'] ? $_POST['first_text_color'] : '',
			'second_text_color' => $_POST['second_text_color'] ? $_POST['second_text_color'] : '',
			'no_marquee' => $_POST['no_marquee'] ? $_POST['no_marquee'] : '',
			'third_text_color' => $_POST['third_text_color'] ? $_POST['third_text_color'] : '',
			'telephone_warning' => $_POST['telephone_warning'] ? $_POST['telephone_warning'] : '',
			'sudoku_color' => $_POST['sudoku_color'] ? $_POST['sudoku_color'] : '',
		);
		//游戏视频
		if($_FILES['yes_marquee']['tmp_name']){
			$dir_img = UPLOAD_PATH . '/mp4/'.$path;
			if(!is_dir($dir_img)) {
				if(!mkdir($dir_img,0777,true)) {
					//创建mp4目录{$dir_img}失败
					$return = array(
						'code' => 0,
						'msg' => "创建mp4目录{$dir_img}失败",
					);
					return $return;							
				}
			}
			list($msec,$sec) = explode(' ',microtime());
			$msec = substr($msec,2);
			$dst = $dir_img.$msec.'.mp4';
			if(move_uploaded_file($_FILES['yes_marquee']['tmp_name'],$dst)) {
				$data['yes_marquee'] = str_replace(UPLOAD_PATH,'',$dst);
			} 	
		}	
		unset($_FILES['yes_marquee']);		
		$time = time();
		if($_POST['ap_id']){
			$data['ap_utm'] = $time;
		}else{
			$data['ap_ctm'] = $time;
			$data['ap_utm'] = $time;
		}
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return; 
	}
	//签到活动---添加、编辑活动
	public function sign_activity_post(){
		//表单验证===开始
		if(empty($_POST['ap_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写活动名称",
			);
			return $return;				
		}
		$where = array(
			'ap_name' => $_POST['ap_name'],
			'status' =>1
		);
		if($_POST['ap_id']){
			$where['ap_id'] = array('exp',"!={$_POST['ap_id']}");
		}
		$res = $this->table('sj_activity_page')->where($where)->field('ap_name')->find();
		//var_dump($res);
		if($res){
			$return = array(
				'code' => 0,
				'msg' => "活动名称已存在",
			);
			return $return;				
		}
		if($_POST['ap_type'] == 2){
			if($_POST['rank_lottery_desc_pic'] > 0 && $_POST['rank_lottery_desc_text']%10 != 0){
				$return = array(
					'code' => 0,
					'msg' => "【单次安智币补签扣除安智币个数】请填写10的整数倍",
				);
				return $return;						
			}
			//@
			if(!$_POST['ap_id'] && $_POST['share_add'] == 1){
				if(empty($_FILES['lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传抽奖区背景图片",
					);
					return $return;				
				}
				if(empty($_FILES['click_lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传点击抽奖图",
					);
					return $return;				
				}
				if(empty($_FILES['unclick_lottery_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传禁止抽奖图",
					);
					return $return;				
				}
			}
			if(!$_POST['ap_id'] && $_POST['share_switch'] == 1){
				if(empty($_FILES['share_weixin_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传分享微信图",
					);
					return $return;				
				}
				if(empty($_FILES['share_other_pic']['tmp_name'])){
					$return = array(
						'code' => 0,
						'msg' => "请上传其他分享图",
					);
					return $return;				
				}				
			}
		}
		if(!$_POST['ap_id']){
			if(empty($_FILES['ap_imgurl']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传活动头图",
				);
				return $return;				
			}	
		}	
		//表单验证===结束
		if($_FILES['button_pic']['tmp_name']){
			$button_pic = getimagesize($_FILES['button_pic']['tmp_name']);
			if($button_pic[0] != $this->image_width){
				$return = array(
					'code' => 0,
					'msg' => "子版块背景图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}		
		if($_FILES['ap_imgurl']['tmp_name']){
			$ap_imgurl = getimagesize($_FILES['ap_imgurl']['tmp_name']);
			if($ap_imgurl[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动头图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['bg_img']['tmp_name']){
			$bg_img = getimagesize($_FILES['bg_img']['tmp_name']);
			if($bg_img[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "页面背景图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		$config = $this -> pic_config();
	
		$data = array(
			'status' => 1,
			'activate_type' => 12,
			'ap_link' => ACTIVITY_URL ."/lottery/sign/index.php",
		);
		unset($_POST['__hash__']);
		$_POST['download_bgcolor'] = implode(",",$_POST['download_bgcolor']);
		foreach($_POST as $k => $v){
			$data[$k] = $v;
		}
		$time = time();
		if($_POST['ap_id']){
			$data['ap_utm'] = $time;
		}else{
			$data['ap_ctm'] = $time;
			$data['ap_utm'] = $time;
		}
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return; 
	}	
	
	function pic_config(){
		$config = array();
		$path = date("Ym/d/");
		foreach($_FILES as $k => $v){
			if($v['tmp_name']){
				$config['multi_config'][$k] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}			
		}
		return $config;
	}

	// 评论可回复---添加、编辑活动
	public function comment_reply_post()
	{
		//表单验证===开始
		if(empty($_POST['ap_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写活动名称",
			);
			return $return;				
		}
		$where = array(
			'ap_name' => $_POST['ap_name'],
			'status' =>1
		);
		if($_POST['ap_id']){
			$where['ap_id'] = array('exp',"!={$_POST['ap_id']}");
		}
		$res = $this->table('sj_activity_page')->where($where)->field('ap_name')->find();
		if($res){
			$return = array(
				'code' => 0,
				'msg' => "活动名称已存在",
			);
			return $return;				
		}
		if(!$_POST['ap_id']){
			if(empty($_FILES['ap_imgurl']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传页面banner图片",
				);
				return $return;				
			}
			/*if(empty($_FILES['bottom_color']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传页面底部图片",
				);
				return $return;				
			}*/
		}	
		//表单验证===结束		
		if($_FILES['ap_imgurl']['tmp_name']){
			$ap_imgurl = getimagesize($_FILES['ap_imgurl']['tmp_name']);
			if($ap_imgurl[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动头图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['bottom_color']['tmp_name']){
			$bg_img = getimagesize($_FILES['bottom_color']['tmp_name']);
			if($bg_img[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "页面底部图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}

		$config = $this -> pic_config();
		if ($_POST['ap_type'] == '1') {
			$ap_link = ACTIVITY_URL ."/lottery/cmt_reply/pre.php";
		} elseif ($_POST['ap_type'] == '2') {
			$ap_link = ACTIVITY_URL ."/lottery/cmt_reply/index.php";
		} elseif ($_POST['ap_type'] == '3') {
			$ap_link = ACTIVITY_URL ."/lottery/cmt_reply/end.php";
		}
		$data = array(
			'status' => 1,
			'activate_type' => 14,
			'ap_link' => $ap_link,
		);
		unset($_POST['__hash__']);
		foreach($_POST as $k => $v){
			if ($v) {
				$data[$k] = $v;
			}
		}
		$time = time();
		if($_POST['ap_id']){
			$data['ap_utm'] = $time;
		}else{
			$data['ap_ctm'] = $time;
			$data['ap_utm'] = $time;
		}
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return;
	}
	//会员折扣活动---添加、编辑活动
	public function vip_discount_post(){
		//表单验证===开始
		if(empty($_POST['ap_name'])){
			$return = array(
				'code' => 0,
				'msg' => "请填写活动名称",
			);
			return $return;				
		}
		$where = array(
			'ap_name' => $_POST['ap_name'],
			'status' =>1
		);
		if($_POST['ap_id']){
			$where['ap_id'] = array('exp',"!={$_POST['ap_id']}");
		}
		$res = $this->table('sj_activity_page')->where($where)->field('ap_name')->find();
		//var_dump($res);
		if($res){
			$return = array(
				'code' => 0,
				'msg' => "活动名称已存在",
			);
			return $return;				
		}
		if(!$_POST['ap_id']){
			if(empty($_FILES['ap_imgurl']['tmp_name'])){
				$return = array(
					'code' => 0,
					'msg' => "请上传活动头图",
				);
				return $return;				
			}	
		}	
		//表单验证===结束	
		if($_FILES['ap_imgurl']['tmp_name']){
			$ap_imgurl = getimagesize($_FILES['ap_imgurl']['tmp_name']);
			if($ap_imgurl[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "活动头图：请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		if($_FILES['bg_img']['tmp_name']){
			$bg_img = getimagesize($_FILES['bg_img']['tmp_name']);
			if($bg_img[0] != $this->image_width ){
				$return = array(
					'code' => 0,
					'msg' => "页面背景图片：:请上传宽度为{$this->image_width}的图片",
				);
				return $return;
			}		
		}
		$config = $this -> pic_config();
	
		$data = array(
			'status' => 1,
			'activate_type' => 15,
			'ap_link' => ACTIVITY_URL ."/lottery/vip/vip_discount.php",
		);
		unset($_POST['__hash__']);
		foreach($_POST as $k => $v){
			$data[$k] = $v;
		}
		$time = time();
		if($_POST['ap_id']){
			$data['ap_utm'] = $time;
		}else{
			$data['ap_ctm'] = $time;
			$data['ap_utm'] = $time;
		}
		$return = array(
			'code' => 1,
			'data' => $data,
			'config' => $config,
		);
		return $return; 
	}	
}
?>
