<?php
/**
 * 推荐管理-活动专区
 * Added by 黄文强
 * 2013/05/13
 */
class GameSubscribeAction extends CommonAction {
	
	private $image_width=480;
	private $image_height=181;
	private $image_width_high=684;
	private $image_height_high=231;
	private $image_width_low=444;
	private $image_height_low=150;
	/**
	 * 展示预约列表
	 */
	public function showGameorderList() {
		$data = array();
		// $game_subscriber = D('Sj.game_subscriber');
		$game_subscriber = M('game_subscriber');
		$model = new Model();
		if(!$_GET){
			$this->display('showGameorderList');
			EXIT;
		}
		//初始化查询条件
		$options = array();
		$options['a.status'] = 1;
		$options['sj_activity_page.status'] = 1;
		if(isset($_GET['order_name']) && $_GET['order_name'] != "")
		{
			$order_name=urldecode($_GET['order_name']);
			$options['a.title'] = array('like',"%".$order_name."%");
		}
		if(isset($_GET['id']) && $_GET['id'] != "")
		{
			$options['a.id'] = $_GET['id'];
			$this->id = $_GET['id'];
		}
		// if(!isset($_GET['startDate']) && !isset($_GET['endDate']) && !isset($_GET['id']) && !isset($_GET['order_name']) && !isset($_GET['game_name'])){
		// 	$model = D('Sj.Config');
		// 	$res = $model -> get_config('subscriber_config');
		// 	$subscriber_config=json_decode($res['configcontent'],true);
		// 	$start=time()-$subscriber_config['subscriber_num']*86400;
		// 	$options['a.start_tm'] = array('egt',$start);
		// 	$options['a.end_tm'] = array('elt',time());
		// }
		if(isset($_GET['startDate']) && $_GET['startDate'] != "")
		{
			$start = strtotime($_GET['startDate']);
			$options['a.start_tm'] = array('egt',$start);
		}
		if(isset($_GET['endDate']) && $_GET['endDate'] != "")
		{
			$end = strtotime($_GET['endDate']." 23:59:59");
			$options['a.end_tm'] = array('elt',$end);
		}
		if(isset($_GET['game_name']) && $_GET['game_name'] != "")
		{
			$options['sj_activity_page.download_comment'] = array('like',"%".$_GET['game_name']."%");
		}
		$this->url_subff = $this->get_url_suffix(array('order_name','startDate','endDate','game_name','p','lr'));
		$this->order_name = urldecode($_GET['order_name']);
		$this->startDate = $_GET['startDate'];
		$this->endDate = $_GET['endDate'];
		$this->game_name = $_GET['game_name'];
		import("@.ORG.Page");
		$count = $model->table('sj_game_subscriber a')->join('sj_activity_page ON sj_activity_page.ap_id  = a.ap_id')->where($options)->count();
		
		$Page = new Page($count, 10);
		$show = $Page->show();
		$this->assign('page', $show);
		$this->assign('count',$count);
		//分段排序
		// $order = "IF (
		// 	a.start_tm <= UNIX_TIMESTAMP(NOW())  AND UNIX_TIMESTAMP(NOW()) < a.end_tm, a.rank, 
		// 	IF( UNIX_TIMESTAMP(NOW()) <= a.start_tm ,
		// 		a.rank + POW(2, 40), 
		// 		a.end_tm * (-1) + POW(2, 41)
		// 	  )
  //       ),IF (
		// 	a.start_tm <= UNIX_TIMESTAMP(NOW())  AND UNIX_TIMESTAMP(NOW()) < a.end_tm, a.start_tm ,a.start_tm
  //       )";
		$order='a.rank asc,a.start_tm desc ';
		$arr = $model->table('sj_game_subscriber a')->join('sj_activity_page ON sj_activity_page.ap_id  = a.ap_id')->where($options)->order($order)->limit($Page->firstRow.','.$Page->listRows)->field('a.*, sj_activity_page.download_comment as game_name,sj_activity_page.end_tm as shelves_time ')->select();
		// echo $model->getlastsql();die;
		//获取合作样式列表
		$util = D("Sj.Util"); 
		foreach($arr as $key => $val){
			// $label = $model -> table('sj_active_filter_label') -> where(array('id' => $val['filter_label_id'],'status'=>1)) -> find();
			// $arr[$key]['filter_label_id']=$label['label_name']?$label['label_name']:'无';
			$arr[$key]['end_tm_str'] = $val['end_tm'];
			$arr[$key]['start_tm_str'] = $val['start_tm'];
			$arr[$key]['end_tm'] = date('Y-m-d H:i:s',$val['end_tm']);
			$arr[$key]['start_tm'] = date('Y-m-d H:i:s',$val['start_tm']);
			$arr[$key]['shelves_time'] = date('Y-m-d H:i:s',$val['shelves_time']);
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($val['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$arr[$key]['co_types'] = $v[0];
				}
			}
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
		// echo IMGATT_HOST;
		//  header("Content-type: text/html; charset=utf-8");
		// echo "<pre>";var_dump($arr);die;
		$this->assign('p', $p);
		$this->assign('lr', $lr);
		$this->assign('list', $arr);
		$this->display('showGameorderList');
	}
	public function addGameorder_show(){
		$model = new Model();
		$this->url_subff = $this->get_url_suffix(array('order_name','startDate','endDate','game_name','p','lr'));
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);

		// $labels = $model -> table('sj_active_filter_label') ->field('id,label_name')-> where(array('status' => 1)) -> select();
		// $this -> assign("labels",$labels);
		$this -> assign("image_width",$this->image_width);
		$this -> assign("image_height",$this->image_height);
		$this -> assign("image_width_high",$this->image_width_high);
		$this -> assign("image_height_high",$this->image_height_high);
		$this -> assign("image_width_low",$this->image_width_low);
		$this -> assign("image_height_low",$this->image_height_low);
		$this -> display();
	}
	
	/**
	 * 添加游戏预约
	 */
	public function addGameorder() {
		$data = array();
		$model = new Model();
		$game_subscriber = M('game_subscriber');
		if (empty($_POST['order_name'])){
			$this->error('预约名称不能为空');
		}else{
			$data['title'] = trim($_POST['order_name']);
			if (mb_strlen(iconv('utf-8', 'gbk', $data['order_name'])) > 25)
				$this->error('预约名称不能多于25个字');
		}

		$have_been = $game_subscriber -> where(array('name' => $data['name'],'status' => 1)) -> select();
		if($have_been){
			$this -> error("该预约名称已存在");
		}
		//合作形式
		if(isset($_POST['co_type'])){
			$data['co_type'] = $_POST['co_type'];
		}else{
			$data['co_type'] = 0;
		}	
		if($_POST['rank']){
			$data['rank'] = $_POST['rank'];		
		}
		if(empty($_POST['msg'])){
			$this->error('消息文案不能为空');
		}else{
			$data['msg'] = trim($_POST['msg']);		
		}	
		if (empty($_FILES['low_image']['size']))	$this->error('请上传有效低分图片');
		if (empty($_FILES['high_image']['size']))	$this->error('请上传有效高分图片');

		// if (empty($_FILES['image']['size']))
		// 	$this->error('请上传有效图片');
		// else
		// 	$image = $_FILES['image'];

		if (empty($_POST['start_tm']))
			$this->error('开始时间不能为空');
		else
			$data['start_tm'] = strtotime($_POST['start_tm']);

		if (empty($_POST['end_tm']))
			$this->error('结束时间不能为空');
		else
			$data['end_tm'] = strtotime($_POST['end_tm']);

		if ($data['end_tm'] <= $data['start_tm'])
			$this->error('结束时间不能小于开始时间');

		$path = date("Ym/d/");
		$config = array();

		// $image = getimagesize($_FILES['image']['tmp_name']);
		// if($image[0] != $this->image_width || $image[1] != $this->image_height){
		// 	$this -> error("图片：:请上传宽度为{$this->image_width},高度为{$this->image_height}的图片");
		// }
		$image_604_204 = getimagesize($_FILES['high_image']['tmp_name']);
		if($image_604_204[0] != $this->image_width_high || $image_604_204[1] != $this->image_height_high){
			$this -> error("高分图片：:请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}图片");
		}
		$image_444_150 = getimagesize($_FILES['low_image']['tmp_name']);
		if($image_444_150[0] != $this->image_width_low || $image_444_150[1] != $this->image_height_low){
			$this -> error("低分图片：:请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
		}
        // $data['image']=$this->upload_img('image');
        $data['low_image']=$this->upload_img('low_image');
        $data['high_image']=$this->upload_img('high_image');
		if (empty($data['low_image']))
			$this->error('图片保存失败');
		$url_subff = $_POST['url_subff'];
		
		// $platform_arr = $_POST['platform'];
		// if(!$platform_arr){
		// 	$this -> error("请选择投放平台");
		// }
		// $the_platform = implode(',',$platform_arr);
		// $data['platform'] = $the_platform;
		// if(in_array(13,$platform_arr)){
		// 	$data['sdk_type'] = $_POST['sdk_type'];
		// }
		if(empty($_POST['activity_id'])){
			$this -> error("活动id不能为空");
		}else{
			//检查此活动是否与预约关联
			$check_da =$game_subscriber -> where(array('activity_id' => $_POST['activity_id'],'status' => 1)) -> find();
			if(isset($check_da)){
				$this -> error("活动id已与预约关联");
			}
			$activity = M('activity') -> where(array('id' => $_POST['activity_id'],'status' => 1)) -> find();
			if(!$activity){
				$this -> error("活动id不存在");
			}else{
				if(!$activity['pre_url']){
					if($data['start_tm']<$activity['start_tm']){
						$this -> error("游戏预约的开始时间不得早于预约活动的开始时间。 ");
					}
				}
				$ap_id=$activity['activity_page_id'];
				$ap_data = M('activity_page') -> where(array('ap_id' => $ap_id,'status' => 1)) -> find();
				if(!$ap_data){
					$this -> error("活动页面id不存在");
				}
				$data['ap_id'] = $ap_id;
				$data['activity_id'] = $activity['id'];
				// $data['activity_id'] = $activity['id'];
				$data['yx_id'] = $ap_data['get_lottery_type'];
				// $data['show_num'] = $ap_data['marquee_text_color'];
			}
		}
		if ($my_result = $game_subscriber->add($data))
		{
			
			$this->writelog('添加了游戏预约id为'.$my_result, 'sj_game_subscriber',$my_result,__ACTION__ ,'','add');
			$this -> assign("jumpUrl",'/index.php/Sj/GameSubscribe/showGameorderList/id/'.$my_result);
			$this->success('添加成功');
		}
		else
		{
			$this->error('添加失败');
		}
	}
	function upload_img($re_pb){
        // $model = D('sendNum.Product');
        //附件存放目录
        // $static_data = C('YUYUE_PATH'); //附件的绝对路径
        list($msec, $sec) = explode(' ', microtime());
        $msec = substr($msec, 2);
        if ($_FILES["$re_pb"]['name']) {
            $dir_img = UPLOAD_PATH. '/img/' . date('Ym/d') . '/';
            if (!is_dir($dir_img)) {
                if (!mkdir($dir_img, 0777, true)) {
                    $this->error('目录创建失败');
                }
            }
            // $array = array('jpg', 'png', 'pdf');
            $ytypes = $_FILES["$re_pb"]['name'];
            $info = pathinfo($ytypes);
            $type = strtolower($info['extension']); //获取文件件扩展名
            // if (!in_array($type, $array)) {
            //     $this->error($str.'材料文件格式不正确！');
            // }
            // if ($_FILES["$re_pb"]['size']>=1024*1024*20) {
            //     $this->error($str.'材料大小小于20M！');
            // }
            $img_path = $dir_img . $file . $msec . '.' . $type;
            if (move_uploaded_file($_FILES["$re_pb"]['tmp_name'], $img_path)) {
                $file_path = str_replace(UPLOAD_PATH, '', $img_path);
                return $file_path;
                // $_POST["$re_pb"]=$file_ba;
            }
        }
    }
	//展示编辑表单
	public function edit_test_show() {
		$model = M();
		$id =$_GET['id'];
		$result = $model -> table('sj_game_subscriber') -> where(array('id' => $id)) -> select();
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 10;
		}
		$this->url_subff = $this->get_url_suffix(array('id','order_name','startDate','endDate','game_name','p','lr'));
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($result[0]['co_type']);
		$this->assign('typelist',$typelist);

		$platform_arr = explode(',',$result[0]['platform']);
		$this->assign('platform_arr',$platform_arr);
		$this->assign('p', $p);
		$this->assign('lr', $lr);
		$result[0]['end_tm'] = date('Y-m-d H:i:s',$result[0]['end_tm']);
		$result[0]['start_tm'] = date('Y-m-d H:i:s',$result[0]['start_tm']);
		$act = $model -> table('sj_activity') -> where(array('id' => $result[0]['activity_id'])) -> find();
		$result[0]['act_name'] = $act['name'];
		$this -> assign('result',$result);
		$this -> assign("url_subff",$this->url_subff);
		$this -> assign("image_width",$this->image_width);
		$this -> assign("image_height",$this->image_height);
		$this -> assign("image_width_high",$this->image_width_high);
		$this -> assign("image_height_high",$this->image_height_high);
		$this -> assign("image_width_low",$this->image_width_low);
		$this -> assign("image_height_low",$this->image_height_low);
		$this -> display();
	}

	/**
	 * 编辑游戏预约
	 */
	public function editGameorder() {
		$data = array();
		$model = new Model();
		$game_subscriber = M('game_subscriber');
		$edit_id=trim($_POST['edit_id']);
		if(empty($edit_id)) $this->error('参数错误，请重试');
		$data['id'] = $edit_id;
		if (empty($_POST['order_name'])){
			$this->error('预约名称不能为空');
		}else{
			$data['title'] = trim($_POST['order_name']);
			if (mb_strlen(iconv('utf-8', 'gbk', $data['order_name'])) > 25)
				$this->error('预约名称不能多于25个字');
		}
		$have_been = $game_subscriber -> where(array('title' => $data['title'],'status' => 1,'id'=>array('neq',$edit_id))) -> select();
		if($have_been){
			$this -> error("该预约名称已存在");
		}
		//合作形式
		if(isset($_POST['co_type'])){
			$data['co_type'] = $_POST['co_type'];
		}else{
			$data['co_type'] = 0;
		}	
		if($_POST['rank']){
			$data['rank'] = $_POST['rank'];		
		}	
		if(empty($_POST['msg'])){
			$this->error('消息文案不能为空');
		}else{
			$data['msg'] = trim($_POST['msg']);		
		}		
		if (empty($_FILES['low_image']['size']) && empty($_POST['low_image_tmp']))	$this->error('请上传有效低分图片');
		if (empty($_FILES['high_image']['size']) && empty($_POST['high_image_tmp']))	$this->error('请上传有效高分图片');
		// if (empty($_FILES['image']['size']) && empty($_POST['image_tmp']))   $this->error('请上传有效图片');
		if (empty($_POST['start_tm']))
			$this->error('开始时间不能为空');
		else
			$data['start_tm'] = strtotime($_POST['start_tm']);

		if (empty($_POST['end_tm']))
			$this->error('结束时间不能为空');
		else
			$data['end_tm'] = strtotime($_POST['end_tm']);

		if ($data['end_tm'] <= $data['start_tm'])
			$this->error('结束时间不能小于开始时间');

		// $path = date("Ym/d/");
		// $config = array();
		// if(!empty($_FILES['image']['tmp_name'])){
		// 	$image = getimagesize($_FILES['image']['tmp_name']);
		// 	if($image[0] != $this->image_width || $image[1] != $this->image_height){
		// 		$this -> error("图片：:请上传宽度为{$this->image_width},高度为{$this->image_height}的图片");
		// 	}
		// 	$data['image']=$this->upload_img('image');
		// 	if (empty($data['image'])) $this->error('图片保存失败');
		// }
		if(!empty($_FILES['high_image']['tmp_name'])){
			$image_604_204 = getimagesize($_FILES['high_image']['tmp_name']);
			if($image_604_204[0] != $this->image_width_high || $image_604_204[1] != $this->image_height_high){
				$this -> error("高分图片：:请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}图片");
			}
			$data['high_image']=$this->upload_img('high_image');
			if (empty($data['high_image'])) $this->error('高分图片保存失败');
		}
		if(!empty($_FILES['low_image']['tmp_name'])){
			$image_444_150 = getimagesize($_FILES['low_image']['tmp_name']);
			if($image_444_150[0] != $this->image_width_low || $image_444_150[1] != $this->image_height_low){
				$this -> error("低分图片：:请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
			}
			$data['low_image']=$this->upload_img('low_image');
			if (empty($data['low_image'])) $this->error('低分图片保存失败');
		}
		$url_subff = $_POST['url_subff'];
		// $platform_arr = $_POST['platform'];
		// if(!$platform_arr){
		// 	$this -> error("请选择投放平台");
		// }
		// $the_platform = implode(',',$platform_arr);
		// $data['platform'] = $the_platform;
		// if(in_array(13,$platform_arr)){
		// 	$data['sdk_type'] = $_POST['sdk_type'];
		// }
		if(empty($_POST['activity_id'])){
			$this -> error("活动id不能为空");
		}else{
			//检查此活动是否与预约关联
			$check_da =$game_subscriber -> where(array('activity_id' => $_POST['activity_id'],'status' => 1,'id'=>array('neq',$data['id']))) -> find();
			if(isset($check_da)){
				$this -> error("活动id已与预约关联");
			}
			$activity = M('activity') -> where(array('id' => $_POST['activity_id'],'status' => 1)) -> find();
			if(!$activity){
				$this -> error("活动id不存在");
			}else{
				if(!$activity['pre_url']){
					if($data['start_tm']<$activity['start_tm']){
						$this -> error("游戏预约的开始时间不得早于预约活动的开始时间。 ");
					}
				}
				$ap_id=$activity['activity_page_id'];
				$ap_data = M('activity_page') -> where(array('ap_id' => $ap_id,'status' => 1)) -> find();
				if(!$ap_data){
					$this -> error("活动页面id不存在");
				}
				$data['ap_id'] = $ap_id;
				$data['activity_id'] = $activity['id'];
				$data['yx_id'] = $ap_data['get_lottery_type'];
				// $data['show_num'] = $ap_data['marquee_text_color'];
			}
		}
		$log = $this -> logcheck(array('id' => $data['id']),'sj_game_subscriber',$data,$game_subscriber);
		if(!$log){
			$this -> assign("jumpUrl",'/index.php/Sj/GameSubscribe/showGameorderList/id/'.$edit_id);
			$this->success('修改成功');
		}
		if ($my_result = $game_subscriber->save($data))
		{
			$this->writelog('修改了游戏预约id为'.$data["id"].'。'.$log, 'sj_game_subscriber',$data["id"],__ACTION__ ,'','edit');
			$this -> assign("jumpUrl",'/index.php/Sj/GameSubscribe/showGameorderList/id/'.$edit_id);
			$this->success('修改成功');
		}
		else
		{
			$this->error('修改失败');
		}
	}
	/**
	 * 展示预约用户列表
	 */
	public function showpuserlist() {
		$model=D('Sj.GameSub');
		//初始化查询条件
		$options = array();
		$options_two = array();
		// $options['a.status'] = 1;
		$options['pre_users.status'] = 1;
		$options_two['status'] = 1;
		$options['a.aid'] = trim($_GET['aid']);
		$options['pre_users.aid'] = trim($_GET['aid']);
		$options_two['aid'] =  trim($_GET['aid']);
		if(isset($_GET['username']) && $_GET['username'] != "")
		{
			$options['a.username'] = array('like',"%".$_GET['username']."%");
		}
		if(isset($_GET['uid']) && $_GET['uid'] != "")
		{
			$options['a.uid'] = $_GET['uid'];
			$this->uid = $_GET['uid'];
		}
		if(isset($_GET['pre_time']) && $_GET['pre_time'] != "")
		{
			$start = strtotime($_GET['pre_time']);
			$end = $start+86400;
			$options['pre_users.create_tm'] = array(array('EGT',$start),array('LT',$end));
			$options_two['create_tm'] =  array(array('EGT',$start),array('LT',$end));
		}
		$this->url_subff = $this->get_url_suffix(array('uid','username','pre_time','subscriber_id','aid','game_name','p','lr'));
		$this->username = $_GET['username'];
		$this->pre_time = $_GET['pre_time'];
		import("@.ORG.Page");
		$options_two['uid']=0;
		$order='a.id desc';
		$arr = $model->table('gm_lottery_num a')->join('pre_users ON pre_users.imei  = a.imei and pre_users.mac  = a.mac')->where($options)->order($order)->field('a.*, pre_users.create_tm as create_tm')->select();
		$arr_new = $model->table('pre_users')->where($options_two)->select();
		$arr_2=$arr;
		foreach($arr_new as $k=>$v){
			if($v){
				$arr_2[]=array('aid'=>$v['aid'],'imei'=>$v['imei'],'mac'=>$v['mac'],'create_tm'=>$v['create_tm'],'uid'=>'');
			}
		}
		if(!(isset($_GET['uid']) || isset($_GET['username']))){
			$arr=$arr_2;
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
		// echo "<pre>";var_dump($arr);
		$Page = new Page(count($arr), 10);
		$show = $Page->show();
		$this->assign('page', $show);
		$this->assign('count',count($arr));
	    $arr=array_slice($arr,($p-1)*$lr,$lr);
	    $m=M('');
		$time = time();
		foreach($arr as $key => $val){
			if(!$val['aid']) continue;
			$arr[$key]['pre_time'] = date('Y-m-d H:i:s',$val['create_tm']);
			$data=$m->table('sj_activity')->where(array('id'=>$val['aid'],'status'=>1))->find();
			$data_two=$m->table('sj_activity_page')->where(array('ap_id'=>$data['activity_page_id'],'status'=>1))->find();
			if($val['uid']){
				$arr[$key]['flash_show'] =$data_two['my_prize_button_color'];
			}
			$arr[$key]['game_name'] =$_GET['game_name'];
			if($time > $data['end_tm']){
				$pre_day = ($data['end_tm']-$val['create_tm'])/86400;
			}else{
				$pre_day = ($time-$val['create_tm'])/86400;
			}
			$arr[$key]['pre_day'] = substr(round($pre_day,2),0,-1);
		}
		if($_GET['export_csv']==1){
			$options_2 = array();
			$options_2['pre_users.status'] = 1;
			$options_2['a.aid'] = trim($_GET['aid']);
			$options_2['pre_users.aid'] = trim($_GET['aid']);
			$arr_2 = $model->table('gm_lottery_num a')->join('pre_users ON pre_users.imei  = a.imei and pre_users.mac  = a.mac')->where($options_2)->order($order)->field('a.*, pre_users.create_tm as create_tm')->select();
			$options_two = array();
			$options_two['status'] = 1;
			$options_two['aid'] =  trim($_GET['aid']);
			$options_two['uid']=0;
			$arr_new = $model->table('pre_users')->where($options_two)->select();
			foreach($arr_new as $k=>$v){
				if($v){
					$arr_2[]=array('imei'=>$v['imei'],'mac'=>$v['mac'],'create_tm'=>$v['create_tm'],'uid'=>'');
				}
			}
			foreach($arr_2 as $key => $val){
				if(!$val['aid']) continue;
				$arr_2[$key]['pre_time'] = date('Y-m-d H:i:s',$val['create_tm']);
				$data=$m->table('sj_activity')->where(array('id'=>$val['aid'],'status'=>1))->find();
				$data_two=$m->table('sj_activity_page')->where(array('ap_id'=>$data['activity_page_id'],'status'=>1))->find();
				if(!$data_two['my_prize_button_color'] || !$val['uid']){
					$arr_2[$key]['lottery_num'] ='';
				}
				$arr_2[$key]['game_name'] = $_GET['game_name'];
				if($time > $data['end_tm']){
					$pre_day = ($data['end_tm']-$val['create_tm'])/86400;
				}else{
					$pre_day = ($time-$val['create_tm'])/86400;
				}
				$arr_2[$key]['pre_day'] = substr(round($pre_day,2),0,-1);
			}
			$this->export_csv($arr_2,"预约用户列表_".date('Y-m-d').".csv");
		}
		$this->assign('p', $p);
		$this->assign('game_name', $_GET['game_name']);
		$this->assign('aid', $_GET['aid']);
		$this->assign('subscriber_id', $_GET['subscriber_id']);
		$this->assign('lr', $lr);
		$this->assign('list', $arr);
		// header("Content-type: text/html; charset=utf-8");
		// echo "<pre>";var_dump( $arr);die;
		$this->display('showpuserlist');
	}
	//导出预约用户列表
	public function export_csv($lists, $filename) { 
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何信息');
        } else {
            $str = iconv('utf-8', 'gb2312', 'ID') . "," . iconv('utf-8', 'gb2312', '设备ID') . "," . iconv('utf-8', 'gb2312', 'MAC地址'). "," . iconv('utf-8', 'gb2312', 'UID') . "," . iconv('utf-8', 'gb2312', '用户名') . "," . iconv('utf-8', 'gb2312', '手机号') . "," . iconv('utf-8', 'gb2312', '预约时间'). "," . iconv('utf-8', 'gb2312', '游戏'). "," . iconv('utf-8', 'gb2312', '参与抽奖次数') ."\r\n";
            foreach ($lists as $key => $val) {
                $str.= iconv('utf-8', 'gb2312', $val['id']) . "," .  $val['imei']. "," . iconv('utf-8', 'gb2312', $val['mac']) . "," . iconv('utf-8', 'gb2312', $val['uid']) . "," . iconv('utf-8', 'gb2312', $val['username']) . "," . iconv('utf-8', 'gb2312', $val['tel']) .  "," . iconv('utf-8', 'gb2312', $val['pre_time']) .  "," . iconv('utf-8', 'gb2312', $val['game_name']).  "," . iconv('utf-8', 'gb2312', $val['lottery_num'])."\r\n";
            }
        }
        echo $str;
        exit;
    }
    //增加抽奖次数
    public function add_lottery_num(){
    	$model=D('Sj.GameSub');
    	if(!$_POST){
    		$id = $_GET['id'];
    		$this->assign('user_id', $id);
			$this->display('add_lottery_num');
    	}else{
    		//redis已经注释 上线需要开启
    		//  'LOTTERY_REDIS_HOST'                => '192.168.1.151',
    		// 'LOTTERY_REDIS_PORT'                => 6380,
   			$redis = new Redis();
   			$subscriber=C('subscriber_redis');
			$redis->connect($subscriber['host'],$subscriber['port']);
			$id = $_POST['id'];
			$lottery_num = trim($_POST['lottery_num']);
			$data =$model->table('gm_lottery_num')->where(array('id' => $id))->find();
			$num=$lottery_num+$data['lottery_num'];
			$flag =$model->table('gm_lottery_num')->where(array('id' => $id))->save(array('lottery_num' => $num));
			if ($flag)
			{
				$imsi_num = "general_lottery:{$data['uid']}_num_{$data['aid']}";
				$redis ->incrBy($imsi_num,$lottery_num);
				$this->writelog("预约用户列表-id为{$id}的增加抽奖机会：{$lottery_num}，增加前为{$data['lottery_num']}", 'sj_game_subscriber',$id,__ACTION__ ,'','edit');
				echo json_encode(array('code'=>1,'msg'=>'已成功添加'.$lottery_num.'次抽奖机会'));
			}
			else
			{
				echo json_encode(array('code'=>2,'msg'=>'操作失败'));
			}
    	}
	}
	/**
	 * 删除游戏预约
	 */
	public function deleteGameorder() {
		if (!isset($_GET['id']))
		{
			$this->assign('jumpUrl', '/index.php/Sj/GameSubscribe/showGameorderList');
			$this->error('参数错误');
		}
		$id = $_GET['id'];
		$game_subscriber = M('game_subscriber');
		$flag =$game_subscriber->where(array('id' => $id))->save(array('status' => 0));

		$url_subff = $this->get_url_suffix(array('order_name','startDate','endDate','game_name','p','lr'));
		if ($flag)
		{
			$this->writelog('游戏预约-游戏预约管理-删除了ID为' . $id . '的游戏预约', 'sj_game_subscriber',$id,__ACTION__ ,'','del');
			$this->assign('jumpUrl', '/index.php/Sj/GameSubscribe/showGameorderList'.$url_subff);
			$this->success('删除成功');
		}
		else
		{
			$this->assign('jumpUrl', '/index.php/Sj/GameSubscribe/showGameorderList'.$url_subff);
			$this->error('删除失败');
		}
	}
	public function change_rank(){
		$bs=trim($_GET['bs']);
		if($bs==1){
			$id = $_GET['id'];
			$activity = M('activity');
			$data =$activity->where(array('id' => $id,'status'=>1))->find();
			$data_two =M('activity_page')->where(array('ap_id' => $data['activity_page_id'],'status'=>1, 'activate_type' =>9))->find();
			if($data && $data_two){
				echo json_encode($data['name']);
			}
		}else if($bs==2){
			$id = trim($_GET['id']);
			$order_name = urldecode(trim($_GET['order_name']));
			$game_subscriber = M('game_subscriber');
			$where=array();
			$where['status']=1;
			$where['title'] = $order_name;
			if($id){
				$where['id']  = array('neq',$id);
			}
			$data =$game_subscriber->where($where)->find();
			if($data){
				echo 2;
			}else{
				echo 1;
			}
		}else{
			$id = $_GET['id'];
			$rank = trim($_GET['rank']);
			$rank_old = trim($_GET['rank_old']);
			$game_subscriber = M('game_subscriber');
			$flag =$game_subscriber->where(array('id' => $id))->save(array('rank' => $rank));
			if ($flag)
			{
				$log  = "编辑前{$rank_old},编辑后：{$rank}";
				$this->writelog("运营合作-游戏预约-游戏预约管理-id位{$id}的排序值：{$log}", 'sj_game_subscriber',$id,__ACTION__ ,'','edit');
				echo 1;
			}
			else
			{
				echo 2;
			}
		}
	}
}
?>
