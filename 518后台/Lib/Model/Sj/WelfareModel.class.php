<?php

class WelfareModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'fl_';
	public $self_config = array();
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
		
		$this->self_config['upload_api'] = 'service_dev.php'; //上传图片接口名称
		$this->self_config['upload_api_save_path'] = UPLOAD_PATH; //图片保存地址
		$this->self_config['upload_api_image_host'] = '<!--{ANZHI_IMAGE_HOST}-->'; //内容中要替换图片路径的标记
		$this->self_config['upload_api_image_url'] = IMGATT_HOST; //cdn调用地址
		
		if($_SERVER['SERVER_ADDR'] == '118.26.203.23'){
			$this->self_config['upload_api_host'] = '118.26.203.23';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com'; //上传图片接口域名
			$this->self_config['static_url'] = 'http://118.26.203.23/perfect_%d.html'; //静态文件地址
			$this->self_config['perview_url'] = 'http://118.26.203.23/perfect.php?id=%d'; //预览文件地址
		}elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99'){
			$this->self_config['upload_api_host'] = '192.168.0.99';
			$this->self_config['upload_api_domain'] = '9.dummy.goapk.com';
			$this->self_config['static_url'] = 'http://9.m.anzhi.com/perfect_%d.html';
			$this->self_config['perview_url'] = 'http://9.m.anzhi.com/perfect.php?id=%d';
		}else{
			$this->self_config['upload_api_host'] = '192.168.1.18';
			$this->self_config['upload_api_domain'] = 'dummy.goapk.com';
			$this->self_config['static_url'] = 'http://m.anzhi.com/perfect_%d.html';
			$this->self_config['perview_url'] = 'http://m.anzhi.com/perfect.php?id=%d';
		}
// 		$this->assign('self_config', $this->self_config);
	}
	
	
	/**
	 * 总数
	 */
	public function getcount($options)
	{
		$result = $this->where($options)->count();
		return $result;
	}
	
	
	public function welfare_add_do()
	{
		$id			=	$_POST['id'];
		$name		=	trim($_POST['name']);
		$typeid		=	$_POST['typeid'];
		$pos		=	$_POST['pos'];
		$package	=	trim($_POST['package']);
		$start_tm	=	strtotime($_POST['start_tm']);
		$end_tm		=	strtotime($_POST['end_tm']);
		$init_val	=	$_POST['init_val'];
		$begin_val	=	$_POST['begin_val'];
		$end_val	=	$_POST['end_val'];
		$welfare_draw_url = trim($_POST['welfare_draw_url']);
		$list_pic_type	=	trim($_POST['list_pic_type']); //列表页图片样式
		$old_list_pic_type	=	trim($_POST['old_list_pic_type']); //列表页图片样式旧值
		$detail_pic_status	=	trim($_POST['detail_pic_status']); //详情页图片是否展示
		$old_detail_pic	=	trim($_POST['old_detail_pic']); //详情页图片旧值
		if(!$name) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"请填写福利名称",
			);
			return $return;
		}
		if(!$typeid) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"请填写福利类型",
			);
			return $return;
		}
		if(!$package) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"请填写包名",
			);
			return $return;
		}
		if($pos=="") {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"排序值不能为空",
			);
			return $return;
		}
		if(!preg_match("/^[0-9]+$/", $pos)) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"排序值必须为正整数",
			);
			return $return;
		}
		if(!$start_tm) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"开始时间不能为空",
			);
			return $return;
		}
		if(!$end_tm) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"结束时间不能为空",
			);
			return $return;
		}
		if($start_tm > $end_tm) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"开始时间不能大于结束时间",
			);
			return $return;
		}
		if(!$init_val) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"已领人数初始值必填，只能输入大于0的整数",
			);
			return $return;
		}
		if(!preg_match("/^[0-9]+$/", $init_val)) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"已领人数初始值必须为正整数",
			);
			return $return;
		}
		if( !$begin_val) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"随机数最小值必填，只能输入大于0的整数",
			);
			return $return;
		}
		if(!preg_match("/^[0-9]+$/", $begin_val)) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"随机数最小值必须为正整数",
			);
			return $return;
		}
		if( !$end_val ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"随机数最大值必填，只能输入大于0的整数",
			);
			return $return;
		}
		if(!preg_match("/^[0-9]+$/", $end_val)) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"随机数最大值必须为正整数",
			);
			return $return;
		}
		if( $begin_val > $end_val ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"随机数最大值必须大于随机数最小值",
			);
			return $return;
		}
		$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if( !$pkg_data ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"包名不存在",
			);
			return $return;
		}
		//列表页图
		if(!$id || ($id&&$list_pic_type!=$old_list_pic_type)){
			//添加操作或编辑操作并且列表页图片样式改变，需重新上传图片并验证图片格式
			if($list_pic_type==2){
				if($_FILES['list_pic']['tmp_name']==''){
					$return = array(
						'code'	=>	0,
						'msg'	=>	"请上传列表页图片",
					);
					return $return;
				}
				if($return = $this->check_img($config,'list_pic','列表页图片',272,152)){
					return $return;
				}
			}
			if($list_pic_type==3){
				if($_FILES['list_pic']['tmp_name']=='' && $_FILES['list_pic2']['tmp_name']=='' && $_FILES['list_pic3']['tmp_name']==''){
					$return = array(
						'code'	=>	0,
						'msg'	=>	"请上传列表页图片",
					);
					return $return;
				}
				if($return = $this->check_img($config,'list_pic','列表页图片',160,160)){
					return $return;
				}
				if($return = $this->check_img($config,'list_pic2','列表页图片2',160,160)){
					return $return;
				}
				if($return = $this->check_img($config,'list_pic3','列表页图片3',160,160)){
					return $return;
				}
			}
		}else if($id&&$list_pic_type==$old_list_pic_type){
			//编辑操作并且列表页图片样式未改变，若上传图片验证图片格式
			if($list_pic_type==2){
				if($return = $this->check_img($config,'list_pic','列表页图片',272,152)){
					return $return;
				}
			}
			if($list_pic_type==3){
				if($return = $this->check_img($config,'list_pic','列表页图片',160,160)){
					return $return;
				}
				if($return = $this->check_img($config,'list_pic2','列表页图片2',160,160)){
					return $return;
				}
				if($return = $this->check_img($config,'list_pic3','列表页图片3',160,160)){
					return $return;
				}
			}
		}
		
		//详情页
		if($detail_pic_status==1){
			if(empty($_FILES['detail_pic']['tmp_name']) && empty($old_detail_pic)){
				$return = array(
					'code'	=>	0,
					'msg'	=>	"请上传详情页图片",
				);
				return $return;
			}
			if($return = $this->check_img($config,'detail_pic','详情页图片',640,290)){
				return $return;
			}
		}
		
		//分享图片
		$width = 200; $height = 200;
		$date	=	date("Ym/d/");
		if($_FILES['share_pic']['tmp_name']) {
			$pic_path = getimagesize($_FILES['share_pic']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
						'code'	=>	0,
						'msg'	=>	"分辨率图标大小不符合条件",
				);
				return $return;
			}
			if( !in_array($_FILES['share_pic']['type'], array('image/png','image/jpg','image/jpeg','image/gif')) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请添加图片格式为：jpg，png，gif的弹窗图片",
				);
				return $return;
			}
			$config['multi_config']['share_pic'] = array(
					'savepath'		=>	UPLOAD_PATH. '/img/'. $date,
					'saveRule'		=>	'getmsec',
					'img_p_size'	=>	1024 * 200,
			);
		}
		
		$content = isset($_POST['content']) ? stripcslashes($_POST['content']) : NULL;
		if($content == '') {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"福利详情不能为空",
			);
			return $return;
		}
		$path_arr = $this->parse_images($content);
		if(count($path_arr))
		{
			// 2、将图片复制到服务器指定位置
			$result = $this->upload_images($path_arr);
			// 3、新路径
			$new_path_arr = array();
			foreach($result['new_path'] as $key=>$path)
			{
				$tmp = explode('_', $key);
				$index = $tmp[1];
				$new_path_arr[$index] = $this->self_config['upload_api_image_host'].$path;
			}
 			// 4、将内容中的路径替换
 			$content = str_replace($path_arr, $new_path_arr, $content);
		}
		//处理编辑图片的时候，图片地址替换的问题
		if( $id ) {
			$content = str_replace($this->self_config['upload_api_image_url'], $this->self_config['upload_api_image_host'], $content);
		}
		
		$data = array(
			'name'		=>	$name,
			'typeid'	=>	$typeid,
			'pos'		=>	$pos,
			'package'	=>	$package,
			'softname'	=>	$pkg_data['softname'],
			'start_tm'	=>	$start_tm,
			'end_tm'	=>	$end_tm,
			'welfare_draw_url'	=>	$welfare_draw_url,
			'init_val'	=>	$init_val,
			'begin_val'	=>	$begin_val,
			'end_val'	=>	$end_val,
			'content'	=>	$content,
			'list_pic_type' => $list_pic_type,
			'detail_pic_status' => $detail_pic_status,
		);
		if($detail_pic_status != 1) {
			$data['detail_pic'] = '';
		}
		if($list_pic_type == 1) {
			$data['list_pic'] = '';
		}
		if($id) {
			$data['update_tm'] = time();
		}else {
			$data['create_tm'] = time();
			$data['update_tm'] = time();
		}
		
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
				'config'	=>	$config,
		);
		return $return;
	}
	public function get_welfare_info($id){
		$where	=	array('id'=>$id);
		$rows	=	$this->table('fl_welfare')->where($where)->find();
		$content = str_replace($this->self_config['upload_api_image_host'], $this->self_config['upload_api_image_url'], $rows['content']);
		$rows['content'] = $content;
		return $rows;
	}
	
	/**
	 * 从内容中提取出图片路径
	 * @param unknown $content
	 */
	private function parse_images($content, $pattern=NULL)
	{
		if(is_null($pattern))
			$pattern = "/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u";
		preg_match_all($pattern, $content, $matches_pic);
	
		$pattern = "/<embed.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u";
		preg_match_all($pattern, $content, $matches_mp4);
		$pattern = "/<embed.+?imgurl=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u";
		preg_match_all($pattern, $content, $matches_imgurl);
	
	
		if(count($matches_pic[1]) || count($matches_mp4[1]) || count($matches_imgurl[1])){
			return array_merge($matches_pic[1],$matches_mp4[1],$matches_imgurl[1]);
		}else{
			return array();
		}
	}
	
	/**
	 * 复制图片到指定服务器位置
	 */
	private function upload_images($path_arr)
	{
		$data['do'] = 'save';
		$data['static_data'] = $this->self_config['upload_api_save_path'];
		foreach($path_arr as $key=>$path){
			$data['key_'.$key] = '@'.$_SERVER['DOCUMENT_ROOT'].$path;
		}
		$upload_model = D("Dev.Uploadfile");
		$upload = $upload_model->_http_post($data);
		return array(
				'new_path'	=>	$upload['ret'],
				'info'		=>	$upload['info'],
				'errno'		=>	$upload['errno'],
				'error'		=>	$upload['error']
		);
	}

	private function check_img(&$config,$post_name,$name,$width=0,$height=0,$expression='jpg|png|gif'){
		if($_FILES[$post_name]['tmp_name']){
	     	if(!preg_match("/\.({$expression})$/", $_FILES[$post_name]['name'])){
		    	$return = array(
					'code'	=>	0,
					'msg'	=>	"{$name}格式错误",
				);
				return $return;
		    }
		    $img_info_arr = getimagesize($_FILES[$post_name]['tmp_name']);
		    if (!$img_info_arr) {
		        $return = array(
					'code'	=>	0,
					'msg'	=>	"{$name}格式无效",
				);
				return $return;
		    }
		    $image_width = $img_info_arr[0];
		    $image_height = $img_info_arr[1];
		    if($width!=0 && $height!=0){
		        if ($width!=$image_width || $height!=$image_height){
		        	$return = array(
						'code'	=>	0,
						'msg'	=>	"{$name}尺寸错误，宽需为{$width}px，高需为{$height}px",
					);
					return $return;
		        }
		    }
		    $date = date("Ym/d/");
		    $config['multi_config'][$post_name] = array(
				'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
				'saveRule'	 =>	'getmsec',
				'img_p_size' =>	1024 * 200,
			);
	    }
	    return false;
	}
	
}
