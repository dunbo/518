<?php
/**
 * 内容管理平台
 */
class ContentPlatformAction extends CommonAction{
	
	//用户审核列表
	public function user_list()
	{
		$list_type	=	!empty($_REQUEST['list_type'])?$_REQUEST['list_type']:2; //默认待审核
		$username	=	!empty($_REQUEST['username'])?trim($_REQUEST['username']):null;
		$start_tm	=	!empty($_REQUEST['start_tm'])?strtotime($_REQUEST['start_tm']):null;
		$end_tm		=	!empty($_REQUEST['end_tm'])?strtotime($_REQUEST['end_tm']):null;
		if($start_tm && $end_tm && $start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间");
		}
		$model	=	M('');
		$where = 'status = '.$list_type;
		$username && $where .= " and `username` like '%{$username}%' ";
		if( $start_tm && !$end_tm ){
			$where .= " and create_tm >= {$start_tm} ";
		}elseif( !$start_tm && $end_tm) {
			$where .= " and create_tm <= {$end_tm} ";
		}elseif($start_tm && $end_tm) {
			$where .= " and create_tm <= {$end_tm} and create_tm >= {$start_tm} ";
		}
		$where_go['_string'] = $where;
		$count = $model->table('content_platform_user')->where($where_go)->count();
		
		import("@.ORG.Page");
		$param	=	http_build_query($_REQUEST);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('content_platform_user') -> where($where_go) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('update_tm DESC') -> select();
		foreach ($list as $key => $val) {
			if($val['status']==1){
				$card = $model->table('content_platform_user_card')->where(array('userid'=>$val['userid']))->field('bank_card,alipay_num,wechat_num,type')->find();
				if($card){
					if($card['type']==1){
						$list[$key]['card'] = '银行卡：'.$card['bank_card'];
					}elseif($card['type']==2){
						$list[$key]['card'] = '微信：'.$card['wechat_num'];
					}elseif($card['type']==3){
						$list[$key]['card'] = '支付宝：'.$card['alipay_num'];
					}
					
				}
			}
		}
		
		$this->assign('timestamp', time());
		$this->assign('list_type', $list_type);
		$this->assign('username', $username);
		$this->assign('start_tm', $_REQUEST['start_tm']);
		$this->assign('end_tm', $_REQUEST['end_tm']);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function batch_handle(){
		$time = time();
		$id = $_POST['id'] ? $_POST['id'] : $_GET['id'];
		$status = $_POST['status'] ? $_POST['status'] : $_GET['status'];
		$bh_explain = trim($_POST['bh_explain']);
		if($_GET['bs']==1 && $status==3){
			$this->assign('status', $status);
			$this->assign('id', $id);
			$this->display();exit;
		}
		$id_arr = explode(',', $id);
        $where = array(
            'userid' => array('in', $id_arr),
        );
        $data = array(
            'status' => $status,
            'update_tm' => $time,
        );
        if($status==3){
        	if($bh_explain==''){
        		$this -> error('驳回理由未填写');
        	}
            $data['bh_explain'] = $bh_explain;
        }
        $res = M('')->table('content_platform_user')->where($where)->save($data);
        if($res){
        	if($status==1){
        		$this -> writelog("通过了userid为{$id}的账号信息",'content_platform_user',$id,__ACTION__ ,'','edit');
        	}elseif($status==3){
        		$this -> writelog("驳回了userid为{$id}的账号信息",'content_platform_user',$id,__ACTION__ ,'','edit');
        	}
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	}
	
	//修改用户信息
	function user_edit()
	{
		$userid 	= 	$_REQUEST['userid'];
		$username 	= 	trim(strip_tags($_POST['username']));
		$model		=	M('');
		$table		=	"content_platform_user";

		if($_GET['check']){
			//ajax验证账号名称是否存在
			$one = $model->table($table)->where(array('username'=>$username))->find();
			if($one){
				echo json_encode(array('code'=>1));
			}else{
				echo json_encode(array('code'=>0));
			}
			exit;
		}

		$where	=	array('userid' => $userid);
		if($_POST) {
			$type 			= 	$_POST['shuxing'];
			$username_old 	= 	trim($_POST['username_old']);
    		$info 			= 	trim(strip_tags($_POST['info']));
    		$userpic_old 	= 	trim($_POST['userpic_old']);
    		$cardpic1_old 	= 	trim($_POST['cardpic1_old']);
    		$cardpic2_old 	= 	trim($_POST['cardpic2_old']);
    		$cardpic_hand_old 	= 	trim($_POST['cardpic_hand_old']);
    		$province 		= 	trim($_POST['province']);
    		$city 			= 	trim($_POST['city']);
    		$truename 		= 	trim(strip_tags($_POST['truename']));
    		$phone 			= 	trim($_POST['phone']);
    		$mail 			= 	trim($_POST['mail']);
    		$cardnumber 	= 	trim($_POST['cardnumber']);

    		if(!$userpic_old){
    			$this->check_image('userpic', 200, 200, '账号头像');
    		}
    		if(!$cardpic1_old){
    			$this->check_image('cardpic1', 0, 0, '身份证正面照');
    		}
    		if(!$cardpic2_old){
    			$this->check_image('cardpic2', 0, 0, '身份证背面照');
    		}
    		if(!$cardpic_hand_old){
    			$this->check_image('cardpic_hand', 0, 0, '手持身份证照');
    		}

	        $data = array();
	        $data['type'] = $type;
	        $data['address_sf'] = $province;
	        $data['address_area'] = $city;

	        //账号名称验证
			if($username == ''){
				$this->error('账号名称不能为空');
			}elseif(mb_strlen($username, 'utf-8')>10 || mb_strlen($username, 'utf-8')<2){
				$this->error('账号名称长度应在2~5个中文或2~10个英文字符以内');
			}elseif(!preg_match("/^[\x{4e00}-\x{9fa5}0-9a-zA-Z_]+$/u", $username)){
				$this->error('账号名称包含非法字符');
			}elseif($username != $username_old){
				$return = $model -> table($table)->where(array('username'=>$username)) -> find();
				if($return){
					$this->error('账号名称已存在！');
				}
			}
			$data['username'] = $username;
			
			//账号简介验证
			if($info == ''){
				$this->error('账号简介不能为空');
			}elseif(mb_strlen($info)>30 || mb_strlen($info)<10){
				$this->error('账号简介长度应在10~30个字符以内');
			}elseif(!preg_match("/^[\x{4e00}-\x{9fa5}0-9a-zA-Z_]+$/u", $info)){
				$this->error('账号简介包含非法字符');
			}else{
				$data['info'] = $info;
			}
	        
	        //联系人
			if($truename == ''){
				$this->error('联系人不能为空');
			}elseif(mb_strlen($truename)>15 || mb_strlen($truename)<2){
				$this->error('账号简介长度应在2~15个字符以内');
			}else{
				$data['truename'] = $truename;
			}
	        
	        //手机号验证
			if($phone == ''){
				$this->error('手机号不能为空');
			}elseif(!preg_match("/^1[34578][\d]{9}$/", $phone)){
				$this->error('手机号格式不正确');
			}else{
				$data['phone'] = $phone;
			}

			//邮箱验证
			if($mail == ''){
				$this->error('邮箱不能为空');
			}elseif(!preg_match("/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/", $mail)){
				$this->error('邮箱格式不正确');
			}else{
				$data['mail'] = $mail;
			}

	        //身份证号验证
			if($cardnumber == ''){
				$this->error('身份证号不能为空');
			}elseif(!preg_match("/^[0-9]{17}[xX0-9]{1}$/", $cardnumber)){
				$this->error('身份证号格式不正确');
			}else{
				$data['cardnumber'] = $cardnumber;
			}

	        $config = array();
			foreach($_FILES as $k => $v){
				if($v['tmp_name']){
					$config['multi_config'][$k] = array(
						'savepath' => UPLOAD_PATH. '/img/'. date("Ym/d/"),
						'saveRule' => 'getmsec',
						'img_p_size' => 1024 * 200,
					);			
				}			
			}
			if(!empty($config['multi_config'])){
				$return = $this->_uploadapk(0, $config);
				foreach($return['image'] as $val) {
					$data[$val['post_name']] = $val['url'];
				}
			}
	        
			$res = $model -> table($table)->where($where) -> save($data);
			if($res){
				$this -> assign('jumpUrl', "__URL__/user_list");
				$this -> writelog("已编辑userid为{$userid}的用户信息",$table,$userid,__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else {
			$list =	$model->table($table)->where($where)->find();
			$this -> assign('userid', $userid);
			$this -> assign('userlist', $list);
			$this -> display();
		}
	}

	private function check_image($image_url,$image_width=0,$image_height=0,$image_name='图片',$expression='jpg|png|jpeg'){
	    if(!$_FILES[$image_url]['tmp_name']){
	        $this->error("请上传{$image_name}！");
	    }
	    // 取得图片后缀
	    $suffix = preg_match("/\.({$expression})$/", $_FILES[$image_url]['name'], $matches);
	    if ($matches) {
	        $suffix = $matches[0];
	    } else {
	    	$this->error("{$image_name}格式错误！");
	    }
	    // 判断图片长和宽
	    $img_info_arr = getimagesize($_FILES[$image_url]['tmp_name']);
	    if (!$img_info_arr) {
	        $this->error("{$image_name}格式非法！");
	    }
	    $width = $img_info_arr[0];
	    $height = $img_info_arr[1];
	    if($image_width!=0 && $image_height!=0){
	        if ($width!=$image_width || $height!=$image_height){
	        	$this->error("{$image_name}尺寸错误！宽需为{$image_width}px，高需为{$image_height}px");
	        }
	    }
	}
	
}