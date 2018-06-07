<?php

class SignAwardModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'qd_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	
	/**
	 * 总数
	 */
	public function getcount($options)
	{
		$result = $this->table('qd_draw_prize')->where($options)->count();
		return $result;
	}
	
	//添加或编辑通用奖品库
	public function pub_award_add_do( $table )
	{
		//表单验证
		if(!$_POST['name'])	{
			$return = array(
				'code'	=>	0,
				'msg'	=>	"请填写奖品名称",
			);
			return $return;		
		}
		if( strlen($_POST['name']) > 30 ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"奖品名称长度不能超过10个汉字",
			);
			return $return;
		}
		if(!$_POST['id'] && empty($_FILES['pic_path']['tmp_name'])) {
			$return = array(
				'code'	=>	0,
				'msg'	=>	"请上传奖品图片",
			);
			return $return;
		}
		$width = 164; $height = 123;
		$date	=	date("Ym/d/");
		if($_FILES['pic_path']['tmp_name']) {
			$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
			if($pic_path[0] != $width || $pic_path[1] != $height){
				$return = array(
					'code'	=>	0,
					'msg'	=>	"分辨率图标大小不符合条件",
				);
				return $return;
			}
			if( !in_array($_FILES['pic_path']['type'], array('image/png','image/jpg','image/jpeg')) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请添加图片格式为：jpg，png的弹窗图片",
				);
				return $return;
			}
			$config['multi_config']['pic_path'] = array(
				'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
				'saveRule'	 =>	'getmsec',
				'img_p_size' =>	1024 * 200,
			);			
		}
		$desc = trim($_POST['desc']);	
		$data = array(
			'name'		=>	trim($_POST['name']),
			'type'		=>	$_POST['type'],
			'desc'		=>	trim($desc),
			'is_pub'	=>	1,//通用的
		);

		$time = time();
		if($_POST['id']){
			$data['update_tm']	=	$time;
		}else{
			$data['create_tm']	=	$time;
			$data['update_tm']	=	$time;
		}		
		$return = array(
			'code'		=>	1,
			'data'		=>	$data,
			'config'	=>	$config,
		);
		return $return;
	}

	public function sign_award_add_do($table)
	{
		$mode			=	$_POST['mode'];//添加方式
		$award_id		=	$_POST['award_id'];
		$award_type_id	=	$_POST['award_type_id']?(Int)$_POST['award_type_id']:'';
		$gift_id		=	$_POST['gift_id']?(Int)$_POST['gift_id']:'';
		$prize_num		=	$_POST['prize_num']?(Int)$_POST['prize_num']:0;
		$prize_money	=	$_POST['prize_money']?$_POST['prize_money']:0;
		$award_name		=	$_POST['award_name']?trim($_POST['award_name']):'';
		$start_tm		=	$_POST['start_tm']?strtotime($_POST['start_tm']):0;
		$end_tm			=	$_POST['end_tm']?strtotime($_POST['end_tm']):0;
		
		$add_award_type	=	$_POST['add_award_type'];
		$add_prize_num	=	$_POST['add_prize_num']?(Int)$_POST['add_prize_num']:'';
		$add_prize_money=	$_POST['add_prize_money']?$_POST['add_prize_money']:'';
		$add_gift_id	=	$_POST['add_gift_id']?(Int)$_POST['add_gift_id']:'';
		$add_start_tm	=	$_POST['add_start_tm']?strtotime($_POST['add_start_tm']):0;
		$add_end_tm		=	$_POST['add_end_tm']?strtotime($_POST['add_end_tm']):0;
		
		$desc			=	trim($_POST['desc']);
		$time			=	time();
		if( $mode == 1 ) {
			if( !$award_id ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请选择通用奖品",
				);
				return $return;
			}
			if( !$prize_num && $award_type_id != 6 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请填写奖品数量",
				);
				return $return;
			}
			if( !$prize_money && $award_type_id == 2 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请填写金额",
				);
				return $return;
			}
			if( (!preg_match('/^[0-9]*$/', $prize_money ) || $prize_money<=0 || $prize_money>100) && $award_type_id == 2 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"只可输入大于0小于等于100的整数",
				);
				return $return;
			}
			if( !$gift_id && $award_type_id==3 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请填写礼券ID",
				);
				return $return;
			}
			$data = array(
				'award_id'	=>	$award_id,
				'gift_file'	=>	$gift_id,
				'num'		=>	$prize_num,
				'prize_num'	=>	$prize_num,
				'is_pub'	=>	1,
				'pkg_path'	=>	'',
				'create_tm'	=>	$time,
				'update_tm'	=>	$time,
			);
			if($award_type_id == 2) {
				$data['gift_file'] = $prize_money;
			}
			if( $award_type_id == 4 && empty($_POST['code']) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"礼包文件不能为空或者数据有误",
				);
				return $return;
			}
			if( $award_type_id == 4 && !empty($_POST['code']) ) {
				$data_file	=	explode(';', $_POST['code']);
				$gift_code	=	array();
				foreach ($data_file as $k => $v) {
					$gift_code[]	=	explode(':', $v);
				}
				$code_count = count($gift_code);
				if( $prize_num > $code_count ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"奖品数量不能大于上传的礼包总数",
					);
					return $return;
				}
			}
			if($award_type_id == 4) {
				if( !$start_tm ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"请填写礼包使用开始时间",
					);
					return $return;
				}
				if( !$end_tm ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"请填写礼包使用结束时间",
					);
					return $return;
				}
				if( $start_tm > $end_tm ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"礼包使用开始时间不能大于礼包使用结束时间",
					);
					return $return;
				}
				//编辑礼包有效期时间，则重新上传礼包文件
				if($_POST['id'] && empty($gift_code) ) {
					$prize_info = $this->table('qd_draw_prize')->where(array('id' => $_POST['id']))->find();
					$gift_tm = json_decode($prize_info['gift_file'], true);
					if( $gift_tm['start_tm']!= $start_tm || $gift_tm['end_tm'] != $end_tm ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"修改礼包使用时间时，请先上传礼包",
						);
						return $return;
					}
				}
				$data['gift_file'] = json_encode(array('start_tm'=>$start_tm, 'end_tm'=>$end_tm));
			}
			if( $award_type_id == 5 && empty($_POST['code']) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"礼包（直接发放）文件不能为空或者数据有误",
				);
				return $return;
			}
			if( $award_type_id == 5 && !empty($_POST['code']) ) {
				foreach ($_POST['code'] as $k => $v) {
					$gift_val[] = explode(':', $v);
				}
				$gift_data = array();
				foreach ($gift_val as $kk => $vv) {
					if( $kk == 0 ){
						$tmp = $vv[0];
						$gift_data[$vv[0]][] = $vv[1];
					}else {
						if( $vv[0] == $tmp ) {
							$gift_data[$vv[0]][] = $vv[1];
						}else {
							$tmp = $vv[0];
							$gift_data[$vv[0]][] = $vv[1];
						}
					}
				}
				$data['gift_file'] = json_encode($gift_data);
			}
			if( $award_type_id == 4 || $award_type_id == 5 ) {
				//上传礼包
				$date = date("Ym/d/");
				$dir_img = C('ACTIVITY_CSV') . '/gift/'.$date;
				if(!is_dir($dir_img)) {
					if(!mkdir($dir_img,0777,true)) {
						//创建gift目录{$dir_img}失败
						$return = array(
								'code' => 0,
								'msg' => "创建gift目录{$dir_img}失败",
						);
						return $return;
					}
				}
				list($msec,$sec) = explode(' ',microtime());
				$types = 'csv';
				$msec = substr($msec,2);
				$dst = $dir_img.'gift'.$_POST['aid'].'_'.$_POST['level'].'_'.$msec.'.'.$types;
				if(move_uploaded_file($_FILES['gift_file']['tmp_name'],$dst)) {
					$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
					$data['pkg_path'] = $path;
				}
				unset($_FILES['gift_file']);
			}
			$return = array(
					'code'		=>	1,
					'data'		=>	$data,
					'gift_code'	=>	$gift_code,
			);
			return $return;
		}elseif( $mode ==2 ) {
			if( !$award_name ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请填写奖品名称",
				);
				return $return;
			}
			if( strlen($award_name) > 30 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"奖品名称长度不能超过10个汉字",
				);
				return $return;
			}
			if( !$add_prize_money && $add_award_type == 2 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请填写金额",
				);
				return $return;
			}
			if( (!preg_match('/^[0-9]*$/', $add_prize_money ) || $add_prize_money<=0 || $add_prize_money>100) && $add_award_type == 2 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"只可输入大于0小于等于100的整数",
				);
				return $return;
			}
			if( !$add_prize_num && $add_award_type != 6 ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请填写奖品数量",
				);
				return $return;
			}
			if( $add_award_type==3 && !$add_gift_id ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请填写礼券ID",
				);
				return $return;
			}
			if(!$_POST['id'] && empty($_FILES['pic_path']['tmp_name'])) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请上传奖品图片",
				);
				return $return;
			}
			$width = 164; $height = 123;
			$date	=	date("Ym/d/");
			if($_FILES['pic_path']['tmp_name']) {
				$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
				if($pic_path[0] != $width || $pic_path[1] != $height){
					$return = array(
							'code'	=>	0,
							'msg'	=>	"分辨率图标大小不符合条件",
					);
					return $return;
				}
				if( !in_array($_FILES['pic_path']['type'], array('image/png','image/jpg','image/jpeg')) ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"请添加图片格式为：jpg，png的弹窗图片",
					);
					return $return;
				}
				$config['multi_config']['pic_path'] = array(
						'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
						'saveRule'	 =>	'getmsec',
						'img_p_size' =>	1024 * 200,
				);
			}
			$data = array(
					'name'		=>	$award_name,
					'type'		=>	$add_award_type,
					'num'		=>	$add_prize_num,
					'prize_num'	=>	$add_prize_num,
					//'gift_file'	=>	$add_gift_id,
					'desc'		=>	$desc,
					'is_pub'	=>	0,
			);
			if( $add_award_type == 2 ) {
				$data['gift_file'] = $add_prize_money;
			}elseif( $add_award_type == 3 ) {
				$data['gift_file'] = $add_gift_id;
			}
			$prize_info = array();
			if( $add_award_type == 4 || $add_award_type == 5 ) {
				$prize_info = $this->table('qd_draw_prize')->where(array('id' => $_POST['id']))->find();
			}else {
				$data['pkg_path']	=	'';
			}
			if( $add_award_type == 6 ) {
				$data['prize_num']	=	0;
			}
			if( !in_array($add_award_type, array(2,3,4,5)) ) {
				$data['gift_file']	=	'';
			}
			if( !in_array($add_award_type, array(2,3,4,5)) ) {
				$data['desc']	=	'';
			}
			if( $_POST['id'] && $prize_info['type'] != 4 && $add_award_type == 4 && empty($_POST['code'])) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请重新上传礼包文件",
				);
				return $return;
			}
			if( !$_POST['id'] && $add_award_type == 4 && empty($_POST['code']) ) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"礼包文件不能为空或者数据有误",
				);
				return $return;
			}
			if( $add_award_type == 4 && !empty($_POST['code']) ) {
				$data_file	=	explode(';', $_POST['code']);
				$gift_code	=	array();
				foreach ($data_file as $k => $v) {
					$gift_code[]	=	explode(':', $v);
				}
				$code_count = count($gift_code);
				if( $add_prize_num > $code_count ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"奖品数量不能大于上传的礼包总数",
					);
					return $return;
				}
			}
			if($add_award_type == 4) {
				if( !$add_start_tm ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"请填写礼包使用开始时间",
					);
					return $return;
				}
				if( !$add_end_tm ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"请填写礼包使用结束时间",
					);
					return $return;
				}
				if( $add_start_tm > $add_end_tm ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"礼包使用开始时间不能大于礼包使用结束时间",
					);
					return $return;
				}
				//编辑礼包有效期时间，则重新上传礼包文件
				if($_POST['id'] && empty($gift_code) ) {
					$prize_info = $this->table('qd_draw_prize')->where(array('id' => $_POST['id']))->find();
					$gift_tm = json_decode($prize_info['gift_file'], true);
					if( $gift_tm['start_tm']!= $add_start_tm || $gift_tm['end_tm'] != $add_end_tm ) {
						$return = array(
								'code'	=>	0,
								'msg'	=>	"修改礼包使用时间时，请先上传礼包",
						);
						return $return;
					}
				}
				$data['gift_file'] = json_encode(array('start_tm'=>$add_start_tm, 'end_tm'=>$add_end_tm));
			}
			if( $_POST['id'] && $prize_info['type'] != 5 && $add_award_type == 5 && empty($_POST['code'])) {
				$return = array(
						'code'	=>	0,
						'msg'	=>	"请重新上传礼包（直接发放）文件",
				);
				return $return;
			}
			if( !$_POST['id'] && $add_award_type == 5 && empty($_POST['code']) ) {
					$return = array(
							'code'	=>	0,
							'msg'	=>	"礼包（直接发放）文件不能为空或者数据有误",
					);
					return $return;
			}
			if( $add_award_type == 5 && !empty($_POST['code']) ) {
				foreach ($_POST['code'] as $k => $v) {
					$gift_val[] = explode(':', $v);
				}
				$gift_data = array();
				foreach ($gift_val as $kk => $vv) {
					if( $kk == 0 ){
						$tmp = $vv[0];
						$gift_data[$vv[0]][] = $vv[1];
					}else {
						if( $vv[0] == $tmp ) {
							$gift_data[$vv[0]][] = $vv[1];
						}else {
							$tmp = $vv[0];
							$gift_data[$vv[0]][] = $vv[1];
						}
					}
				}
				$data['gift_file'] = json_encode($gift_data);
			}
			if( $add_award_type == 4 || $add_award_type == 5 ) {
				//上传礼包
				$date = date("Ym/d/");
				$dir_img = C('ACTIVITY_CSV') . '/gift/'.$date;
				if(!is_dir($dir_img)) {
					if(!mkdir($dir_img,0777,true)) {
						//创建gift目录{$dir_img}失败
						$return = array(
								'code' => 0,
								'msg' => "创建gift目录{$dir_img}失败",
						);
						return $return;
					}
				}
				list($msec,$sec) = explode(' ',microtime());
				$types = 'csv';
				$msec = substr($msec,2);
				$dst = $dir_img.'gift'.$_POST['aid'].'_'.$_POST['level'].'_'.$msec.'.'.$types;
				if(move_uploaded_file($_FILES['gift_file']['tmp_name'],$dst)) {
					$path = str_replace(C('ACTIVITY_CSV'),'',$dst);
					$data['pkg_path'] = $path;
				}
				unset($_FILES['gift_file']);
			}
			if($_POST['id']) {
				$data['update_tm']	=	$time;
			}else {
				$data['create_tm']	=	$time;
				$data['update_tm']	=	$time;
			}
			$return = array(
					'code'		=>	1,
					'data'		=>	$data,
					'gift_code'	=>	$gift_code,
					'config'	=>	$config,
			);
			return $return;
		}else {
			$return = array(
					'code'	=>	0,
					'msg'	=>	"请选择添加方式",
			);
			return $return;
		}
	}
	
	//礼包直接发放获取接口
	function httpGet($val)
	{
		if( C('is_test') == 1 ) {
			$url = "http://dev.heifer.anzhi.com/giftBagManage/getCommonGift?params=";
		}else {
			$url = "http://heifer.anzhi.com/giftBagManage/getCommonGift?params=";
		}
		$param  = is_array($val) ? implode(',', $val) : $val;
		$newUrl = $url.$param;
		$result = httpGetInfo($newUrl, '', 'heifer_gift.log');
		$result = json_decode($result, true);
		return $result;
	}
	
	//获取礼券信息接口
	function httpGetCoupon($id)
	{
		if( C('is_test') == 1 ) {
			$url = "http://dev.heifer.anzhi.com/couponsManage/getCommonCoupon?id=";
		}else {
			$url = "http://heifer.anzhi.com/couponsManage/getCommonCoupon?id=";
		}
		$newUrl = $url.$id;
		$result = httpGetInfo($newUrl, '', 'heifer_coupon.log');
		$result = json_decode($result, true);
		return $result;
	}
	
}
