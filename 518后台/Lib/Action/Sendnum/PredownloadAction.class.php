<?php

class PredownloadAction extends CommonAction {

	function page_list(){
		$model = new Model();
		$where = array(
			'status' => 1
		);
		if(isset($_GET['ap_name'])){
			$this->assign("ap_name", $_GET['ap_name']);
			$where['activity_name'] = array('like','%'.$_GET['ap_name'].'%');
		}
		if(isset($_GET['ap_id'])){
			$this->assign("ap_id", $_GET['ap_id']);
			$where['id'] = $_GET['ap_id'];
		}	
		$count = $model -> table('sj_pre_download') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $model -> table('sj_pre_download') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('create_tm DESC') -> select();
		foreach($result as $key => $val){
			if($val['banner_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['banner_img'],1);
				$banner_img_size = $banner_img_header['Content-Length'];
			}
			if($val['download_one_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['download_one_img'],1);
				$download_one_img_size = $banner_img_header['Content-Length'];
			}
			if($val['download_two_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['download_two_img'],1);
				$download_two_img_size = $banner_img_header['Content-Length'];
			}
			if($val['first_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['first_img'],1);
				$first_img_size = $banner_img_header['Content-Length'];
			}
			if($val['second_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['second_img'],1);
				$second_img_size = $banner_img_header['Content-Length'];
			}
			if($val['third_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['third_img'],1);
				$third_img_size = $banner_img_header['Content-Length'];
			}
			if($val['bbs_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['bbs_img'],1);
				$bbs_img_size = $banner_img_header['Content-Length'];
			}
			if($val['page_img']) {
				$banner_img_header = get_headers(IMGATT_HOST . $val['page_img'],1);
				$page_img_size = $banner_img_header['Content-Length'];
			}
			$img_size = $banner_img_size + $download_one_img_size + $download_two_img_size + $first_img_size + $second_img_size + $third_img_size + $bbs_img_size + $page_img_size;
			$val['img_size'] = sprintf("%.1f", ($img_size/1024/1024));
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
			$lr = 20;
		}
		
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$Page -> setConfig('header', '篇记录');
		$Page -> setConfig('first', '<<');
		$Page -> setConfig('last', '>>');
		$show = $Page->show();
		$this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> display("");
	}
	
	function add_page_show(){
		$this -> display();
	}
	
	function add_page_do(){
		$model = new Model();
		$activity_name = $_POST['activity_name'];
		if(!$activity_name){
			$this -> error("请填写页面名称");
		}
		$page_result = $model -> table('sj_pre_download') -> where(array('activity_name' => $activity_name,'status' => 1)) -> select();
		if($page_result){
			$this -> error("该页面名称已存在");
		}
		$data['activity_name'] = $activity_name;
		$module = $_POST['module'];
		$data['module'] = $module;
		/*
		$package = trim($_POST['package']);
		if(!$package){
			$this -> error("请输入包名");
		}
		$have_result = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$have_result){
			$this -> error('该包名不存在');
		}
		$data['package'] = $package;
		$softname = $_POST['softname'];
		$data['softname'] = $softname;
		*/
		$banner_img = $_FILES['banner_img'];
		if(!$banner_img['size']){
			$this -> error("请上传banner图");
		}
		if($banner_img['type'] != 'image/png' && $banner_img['type'] != 'image/jpeg' && $banner_img['type'] != 'image/jpg' && $banner_img['type'] != 'image/bmp'){
			$this -> error("banner图格式错误");
		}
		
		$is_telephone = $_POST['is_telephone'];
		$data['is_telephone'] = $is_telephone;
		if($is_telephone == 1){
			$telephone_text = $_POST['telephone_text'];
			$data['telephone_text'] = $telephone_text;
		}
		$download_one = $_POST['download_one'];
		$download_one_btn = $_POST['download_one_btn'];
		$download_one_pic = $_FILES['download_one_pic'];
		$first_type = $_POST['first_type'];
		if($first_type){
			$data['first_type'] = $first_type;
		}
		$first_img = $_FILES['first_img'];
		$data['first_img'] = $first_img;
		$first_text = htmlspecialchars($_POST['first_text']);
		$first_text = str_replace("\\","",$first_text);
		$data['first_text'] = $first_text;
		$first_bg_color = $this -> hex2rgb($_POST['first_bg_color']);
		$data['first_bg_color'] = implode(',',$first_bg_color);
		$bbs_address = $_POST['bbs_address'];
		$data['bbs_address'] = $bbs_address;
		$bbs_type = $_POST['bbs_type'];
		$bbs_btn = $_POST['bbs_btn'];
		$bbs_img = $_FILES['bbs_img'];
		$second_type = $_POST['second_type'];
		if($second_type){
			$data['second_type'] = $second_type;
		}
		$second_img = $_FILES['second_img'];
		$data['second_img'] = $second_img;
		$second_text = htmlspecialchars($_POST['second_text']);
		$second_text = str_replace("\\","",$second_text);
		$data['second_text'] = $second_text;
		$second_bg_color = $this -> hex2rgb($_POST['second_bg_color']);
		$data['second_bg_color'] = implode(',',$second_bg_color);
		$download_two = $_POST['download_two'];
		$download_two_btn = $_POST['download_two_btn'];
		$download_two_pic = $_FILES['download_two_pic'];
		$third_type = $_POST['third_type'];
		if($third_type){
			$data['third_type'] = $third_type;
		}
		$third_img = $_FILES['third_img'];
		$data['third_img'] = $third_img;
		$third_text = htmlspecialchars($_POST['third_text']);
		$third_text = str_replace("\\","",$third_text);
		$data['third_text'] = $third_text;
		$third_bg_color = $this -> hex2rgb($_POST['third_bg_color']);
		$data['third_bg_color'] = implode(',',$third_bg_color);
		$page_type = $_POST['page_type'];
		$page_color = $this -> hex2rgb($_POST['page_color']);
		$data['page_color'] = implode(',',$page_color);
		$page_img = $_FILES['page_img'];
		$download_one_bg = $_FILES['download_one_bg'];
		$bbs_bg = $_FILES['bbs_bg'];
		$download_two_bg = $_FILES['download_two_bg'];
		$first_focus_width = $_POST['first_focus_width'];
		if(ceil($first_focus_width)!=$first_focus_width && $first_focus_width){
			$this -> error("模板1轮播图宽度必须为整数");
		}
		$data['first_focus_width'] = $first_focus_width;
		$second_focus_width = $_POST['second_focus_width'];
		if(ceil($second_focus_width)!=$second_focus_width && $second_focus_width){
			$this -> error("模板2轮播图宽度必须为整数");
		}
		$data['second_focus_width'] = $second_focus_width;
		$third_focus_width = $_POST['third_focus_width'];
		if(ceil($third_focus_width)!=$third_focus_width && $third_focus_width){
			$this -> error("模板3轮播图宽度必须为整数");
		}
		$data['third_focus_width'] = $third_focus_width;
		if($first_type == 3){
			$first_focus_bg = $_FILES['first_focus_bg'];
			if($first_focus_bg['size']){
				$high_wd = getimagesize($first_focus_bg['tmp_name']);
				$widhig_hg = $high_wd[3];
				$wh_hg = explode(' ', $widhig_hg);
				$wh1_hg = $wh_hg[0];
				$widths_hg = explode('=', $wh1_hg);
				$width1_hg = substr($widths_hg[1], 0, -1);
				$width_go_hg = substr($width1_hg, 1);
				if ($width_go_hg != 640) {
					$this->error("模板1轮播背景图宽度不等于640");
				}
			}
			$first_focus_pic1 = $_FILES['first_focus_pic1'];
			$first_focus_pic2 = $_FILES['first_focus_pic2'];
			$first_focus_pic3 = $_FILES['first_focus_pic3'];
			$first_focus_pic4 = $_FILES['first_focus_pic4'];
			$first_focus_pic5 = $_FILES['first_focus_pic5'];
			$first_focus_pic6 = $_FILES['first_focus_pic6'];
			if($first_focus_bg['type'] == 'image/gif' || $first_focus_pic1['type'] == 'image/gif' || $first_focus_pic2['type'] == 'image/gif' || $first_focus_pic3['type'] == 'image/gif' || $first_focus_pic4['type'] == 'image/gif' || $first_focus_pic5['type'] == 'image/gif' || $first_focus_pic6['type'] == 'image/gif'){
				$this -> error("轮播图不允许上传gif格式");
			}
		}
		if($second_type == 3){
			$second_focus_bg = $_FILES['second_focus_bg'];
			if($second_focus_bg['size']){
				$high_wd = getimagesize($second_focus_bg['tmp_name']);
				$widhig_hg = $high_wd[3];
				$wh_hg = explode(' ', $widhig_hg);
				$wh1_hg = $wh_hg[0];
				$widths_hg = explode('=', $wh1_hg);
				$width1_hg = substr($widths_hg[1], 0, -1);
				$width_go_hg = substr($width1_hg, 1);
				if ($width_go_hg != 640) {
					$this->error("模板2轮播背景图宽度不等于640");
				}
			}
			$second_focus_pic1 = $_FILES['second_focus_pic1'];
			$second_focus_pic2 = $_FILES['second_focus_pic2'];
			$second_focus_pic3 = $_FILES['second_focus_pic3'];
			$second_focus_pic4 = $_FILES['second_focus_pic4'];
			$second_focus_pic5 = $_FILES['second_focus_pic5'];
			$second_focus_pic6 = $_FILES['second_focus_pic6'];
			if($second_focus_bg['type'] == 'image/gif' || $second_focus_pic1['type'] == 'image/gif' || $second_focus_pic2['type'] == 'image/gif' || $second_focus_pic3['type'] == 'image/gif' || $second_focus_pic4['type'] == 'image/gif' || $second_focus_pic5['type'] == 'image/gif' || $second_focus_pic6['type'] == 'image/gif'){
				$this -> error("轮播图不允许上传gif格式");
			}
		}
		if($third_type == 3){
			$third_focus_bg = $_FILES['third_focus_bg'];
			if($third_focus_bg['size']){
				$high_wd = getimagesize($third_focus_bg['tmp_name']);
				$widhig_hg = $high_wd[3];
				$wh_hg = explode(' ', $widhig_hg);
				$wh1_hg = $wh_hg[0];
				$widths_hg = explode('=', $wh1_hg);
				$width1_hg = substr($widths_hg[1], 0, -1);
				$width_go_hg = substr($width1_hg, 1);
				if ($width_go_hg != 640) {
					$this->error("模板3轮播背景图宽度不等于640");
				}
			}
			$third_focus_pic1 = $_FILES['third_focus_pic1'];
			$third_focus_pic2 = $_FILES['third_focus_pic2'];
			$third_focus_pic3 = $_FILES['third_focus_pic3'];
			$third_focus_pic4 = $_FILES['third_focus_pic4'];
			$third_focus_pic5 = $_FILES['third_focus_pic5'];
			$third_focus_pic6 = $_FILES['third_focus_pic6'];
			if($third_focus_bg['type'] == 'image/gif' || $third_focus_pic1['type'] == 'image/gif' || $third_focus_pic2['type'] == 'image/gif' || $third_focus_pic3['type'] == 'image/gif' || $third_focus_pic4['type'] == 'image/gif' || $third_focus_pic5['type'] == 'image/gif' || $third_focus_pic6['type'] == 'image/gif'){
				$this -> error("轮播图不允许上传gif格式");
			}
		}
		
		if($banner_img['type'] == 'image/gif' || $download_one_pic['type'] == 'image/gif' || $first_img['type'] == 'image/gif' || $bbs_img['type'] == 'image/gif' || $second_img['type'] == 'image/gif' || $download_two_pic['type'] == 'image/gif' || $third_img['type'] == 'image/gif' || $page_img['type'] == 'image/gif' || $download_one_bg['type'] == 'image/gif' || $bbs_bg['type'] == 'image/gif' || $download_two_bg['type'] == 'image/gif'){
			$this -> error("gif图不允许被上传");
		}
		if($banner_img['size'] || $download_one_pic['size'] || $first_img['size'] || $bbs_img['size'] || $second_img['size'] || $download_two_pic['size'] || $third_img['size'] || $download_one_bg['size'] || $bbs_bg['size'] || $download_two_bg['size'] || $first_focus_pic1['size'] || $first_focus_pic2['size'] || $first_focus_pic3['size'] || $first_focus_pic4['size'] || $first_focus_pic5['size'] || $first_focus_pic6['size'] || $second_focus_pic1['size'] || $second_focus_pic2['size'] || $second_focus_pic3['size'] || $second_focus_pic4['size'] || $second_focus_pic5['size'] || $second_focus_pic6['size'] || $third_focus_pic1['size'] || $third_focus_pic2['size'] || $third_focus_pic3['size'] || $third_focus_pic4['size'] || $third_focus_pic5['size'] || $third_focus_pic6['size'] || $first_focus_bg['size'] || $second_focus_bg['size'] || $third_focus_bg['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'banner_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_one_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'bbs_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_two_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'page_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_one_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'bbs_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_two_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
			));
			$lists = $this->_uploadapk(0, $config);
			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'banner_img') {
                    $data['banner_img'] = $val['url'];
                }
                if ($val['post_name'] == 'download_one_pic') {
                    $download_one_img_go = $val['url'];
                }
				if ($val['post_name'] == 'first_img') {
                    $data['first_img'] = $val['url'];
                }
				if ($val['post_name'] == 'bbs_img') {
                    $bbs_img_go = $val['url'];
                }
				if ($val['post_name'] == 'second_img') {
                    $data['second_img'] = $val['url'];
                }
				if ($val['post_name'] == 'download_two_pic') {
                    $download_two_img_go = $val['url'];
                }
				if ($val['post_name'] == 'third_img') {
                    $data['third_img'] = $val['url'];
                }
				if ($val['post_name'] == 'page_img') {
                    $data['page_img'] = $val['url'];
                }
				if ($val['post_name'] == 'download_one_bg') {
                    $data['download_one_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'bbs_bg') {
                    $data['bbs_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'download_two_bg') {
                    $data['download_two_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_bg') {
                    $first_focus_bg = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_bg') {
                    $second_focus_bg = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_bg') {
                    $third_focus_bg = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic1') {
                    $first_focus_pics1 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic2') {
                    $first_focus_pics2 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic3') {
                    $first_focus_pics3 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic4') {
                    $first_focus_pics4 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic5') {
                    $first_focus_pics5 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic6') {
                    $first_focus_pics6 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic1') {
                    $second_focus_pics1 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic2') {
                    $second_focus_pics2 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic3') {
                    $second_focus_pics3 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic4') {
                    $second_focus_pics4 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic5') {
                    $second_focus_pics5 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic6') {
                    $second_focus_pics6 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic1') {
                    $third_focus_pics1 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic2') {
                    $third_focus_pics2 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic3') {
                    $third_focus_pics3 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic4') {
                    $third_focus_pics4 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic5') {
                    $third_focus_pics5 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic6') {
                    $third_focus_pics6 = $val['url'];
                }
            }
		}
		
		if($first_type == 3){
			$first_focus_pic = '';
			if(!$first_focus_pics1 && !$first_focus_pics2 && !$first_focus_pics3 && !$first_focus_pics4 && !$first_focus_pics5 && !$first_focus_pics6){
				$this -> error("模板1请至少上传1张轮播图");
			}
			if($first_focus_pics1){
				$first_focus_pic = $first_focus_pics1;
			}
			if($first_focus_pics2 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics2;
			}elseif($first_focus_pics2 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics2;
			}
			if($first_focus_pics3 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics3;
			}elseif($first_focus_pics3 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics3;
			}
			if($first_focus_pics4 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics4;
			}elseif($first_focus_pics4 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics4;
			}
			if($first_focus_pics5 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics5;
			}elseif($first_focus_pics5 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics5;
			}
			if($first_focus_pics6 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics6;
			}elseif($first_focus_pics5 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics6;
			}
			$data['first_focus_pic'] = $first_focus_pic;
			$data['first_focus_bg'] = $first_focus_bg;
		}

		if($second_type == 3){
			$second_focus_pic = '';
			if(!$second_focus_pics1 && !$second_focus_pics2 && !$second_focus_pics3 && !$second_focus_pics4 && !$second_focus_pics5 && !$second_focus_pics6){
				$this -> error("模板2请至少上传1张轮播图");
			}
			if($second_focus_pics1){
				$second_focus_pic = $second_focus_pics1;
			}
			if($second_focus_pics2 && $second_focus_pic){
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics2;
			}elseif($second_focus_pics2 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics2;
			}
			if($second_focus_pics3 && $second_focus_pic){
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics3;
			}elseif($second_focus_pics3 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics3;
			}
			if($second_focus_pics4 && $second_focus_pic){
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics4;
			}elseif($second_focus_pics4 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics4;
			}
			if($second_focus_pics5 && $second_focus_pic){
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics5;
			}elseif($second_focus_pics5 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics5;
			}
			if($second_focus_pics6 && $second_focus_pic){
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics6;
			}elseif($second_focus_pics6 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics6;
			}
			$data['second_focus_pic'] = $second_focus_pic;
			$data['second_focus_bg'] = $second_focus_bg;
		}
		
		if($third_type == 3){
			$third_focus_pic = '';
			if(!$third_focus_pics1 && !$third_focus_pics2 && !$third_focus_pics3 && !$third_focus_pics4 && !$third_focus_pics5 && !$third_focus_pics6){
				$this -> error("模板3请至少上传1张轮播图");
			}
			if($third_focus_pics1){
				$third_focus_pic = $third_focus_pics1;
			}
			if($third_focus_pics2 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics2;
			}elseif($third_focus_pics2 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics2;
			}
			if($third_focus_pics3 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics3;
			}elseif($third_focus_pics3 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics3;
			}
			if($third_focus_pics4 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics4;
			}elseif($third_focus_pics4 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics4;
			}
			if($third_focus_pics5 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics5;
			}elseif($third_focus_pics5 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics5;
			}
			if($third_focus_pics6 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics6;
			}elseif($third_focus_pics6 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics6;
			}
			$data['third_focus_pic'] = $third_focus_pic;
			$data['third_focus_bg'] = $third_focus_bg;
		}
		
		if($download_one == 1){
			$data['download_one_btn'] = $download_one_btn;
			$data['download_one_img'] = '';
		}else{
			$data['download_one_btn'] = '';
			$data['download_one_img'] = $download_one_img_go;
		}
		
		if($bbs_type == 1){
			$data['bbs_btn'] = $bbs_btn;
			$data['bbs_img'] = '';
		}else{
			$data['bbs_img'] = $bbs_img_go;
			$data['bbs_btn'] = '';
		}
		
		if($download_two == 1){
			$data['download_two_img'] = '';
			$data['download_two_btn'] = $download_two_btn;
		}else{
			$data['download_two_img'] = $download_two_img_go;
			$data['download_two_btn'] = '';
		}
		$data['page_link'] = ACTIVITY_URL . "/lottery/pre_download.php";
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$result = $model -> table('sj_pre_download') -> add($data);
		if($result){
			$this -> writelog("已添加id为{$result}的预下载活动页面", 'sj_pre_download',$result,__ACTION__ ,'','add');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Predownload/page_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}	
	}
	
	function edit_page_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_pre_download') -> where(array('id' => $id)) -> select();
		foreach($result as $key => $val){
			$val['first_bg_colors'] = $this -> RGBToHex("rgb({$val['first_bg_color']})");
			$val['second_bg_colors'] = $this -> RGBToHex("rgb({$val['second_bg_color']})");
			$val['third_bg_colors'] = $this -> RGBToHex("rgb({$val['third_bg_color']})");
			$val['page_colors'] = $this -> RGBToHex("rgb({$val['page_color']})");
			$result[$key] = $val;
		}
		$first_focus_pic = $result[0]['first_focus_pic'];
		$first_focus_pic_arr = explode(',',$first_focus_pic);
		$second_focus_pic = $result[0]['second_focus_pic'];
		$second_focus_pic_arr = explode(',',$second_focus_pic);
		$third_focus_pic = $result[0]['third_focus_pic'];
		$third_focus_pic_arr = explode(',',$third_focus_pic);
		$this -> assign('first_focus_pic_arr',$first_focus_pic_arr);
		$this -> assign('second_focus_pic_arr',$second_focus_pic_arr);
		$this -> assign('third_focus_pic_arr',$third_focus_pic_arr);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_page_do(){
		$model = new Model();
		$id = $_POST['id'];
		$activity_name = $_POST['activity_name'];
		if(!$activity_name){
			$this -> error("请填写页面名称");
		}
		$page_where['_string'] = "activity_name = '{$activity_name}' and status = 1 and id != {$id}";
		$page_result = $model -> table('sj_pre_download') -> where($page_where) -> select();
		if($page_result){
			$this -> error("该页面名称已存在");
		}
		$data['activity_name'] = $activity_name;
		$module = $_POST['module'];
		$data['module'] = $module;
		/*
		$package = trim($_POST['package']);
		if(!$package){
			$this -> error("请输入包名");
		}
		$data['package'] = $package;
		$have_result = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
		if(!$have_result){
			$this -> error('该包名不存在');
		}
		$softname = $_POST['softname'];
		$data['softname'] = $softname;
		*/
		$banner_img = $_FILES['banner_img'];
		if($banner_img['size']){
			if($banner_img['type'] != 'image/png' && $banner_img['type'] != 'image/jpeg' && $banner_img['type'] != 'image/jpg' && $banner_img['type'] != 'image/bmp'){
				$this -> error("banner图格式错误");
			}
		}
		$is_telephone = $_POST['is_telephone'];
		$data['is_telephone'] = $is_telephone;
		if($is_telephone == 1){
			$telephone_text = $_POST['telephone_text'];
			$data['telephone_text'] = $telephone_text;
		}
		$download_one = $_POST['download_one'];
		$download_one_btn = $_POST['download_one_btn'];
		$download_one_pic = $_FILES['download_one_pic'];
		$first_type = $_POST['first_type'];
		if($first_type){
			$data['first_type'] = $first_type;
		}
		$first_img = $_FILES['first_img'];
		$data['first_img'] = $first_img;
		$first_text = htmlspecialchars($_POST['first_text']);
		$first_text = str_replace("\\","",$first_text);
		$data['first_text'] = $first_text;
		$first_bg_color = $this -> hex2rgb($_POST['first_bg_color']);
		$bbs_address = $_POST['bbs_address'];
		$data['bbs_address'] = $bbs_address;
		$bbs_type = $_POST['bbs_type'];
		$bbs_btn = $_POST['bbs_btn'];
		$bbs_img = $_FILES['bbs_img'];
		$second_type = $_POST['second_type'];
		if($second_type){
			$data['second_type'] = $second_type;
		}
		$second_img = $_FILES['second_img'];
		$data['second_img'] = $second_img;
		$second_text = htmlspecialchars($_POST['second_text']);
		$second_text = str_replace("\\","",$second_text);
		$data['second_text'] = $second_text;
		$second_bg_color = $this -> hex2rgb($_POST['second_bg_color']);
		$data['second_bg_color'] = implode(',',$second_bg_color);
		$download_two = $_POST['download_two'];
		$download_two_btn = $_POST['download_two_btn'];
		$download_two_pic = $_FILES['download_two_pic'];
		$third_type = $_POST['third_type'];
		if($third_type){
			$data['third_type'] = $third_type;
		}
		$third_img = $_FILES['third_img'];
		$data['third_img'] = $third_img;
		$third_text = htmlspecialchars($_POST['third_text']);
		$third_text = str_replace("\\","",$third_text);
		$data['third_text'] = $third_text;
		$third_bg_color = $this -> hex2rgb($_POST['third_bg_color']);
		$data['third_bg_color'] = implode(',',$third_bg_color);
		$page_type = $_POST['page_type'];
		$page_color = $this -> hex2rgb($_POST['page_color']);
		$page_img = $_FILES['page_img'];
		$download_one_bg = $_FILES['download_one_bg'];
		$bbs_bg = $_FILES['bbs_bg'];
		$download_two_bg = $_FILES['download_two_bg'];
		$first_focus_width = $_POST['first_focus_width'];
		if(ceil($first_focus_width)!=$first_focus_width && $first_focus_width){
			$this -> error("模板1轮播图宽度必须为整数");
		}
		$data['first_focus_width'] = $first_focus_width;
		$second_focus_width = $_POST['second_focus_width'];
		if(ceil($second_focus_width)!=$second_focus_width && $second_focus_width){
			$this -> error("模板2轮播图宽度必须为整数");
		}
		$data['second_focus_width'] = $second_focus_width;
		$third_focus_width = $_POST['third_focus_width'];
		if(ceil($third_focus_width)!=$third_focus_width && $third_focus_width){
			$this -> error("模板3轮播图宽度必须为整数");
		}
		$data['third_focus_width'] = $third_focus_width;
		if($first_type == 3){
			$first_focus_bg = $_FILES['first_focus_bg'];
			if($first_focus_bg['size']){
				$high_wd = getimagesize($first_focus_bg['tmp_name']);
				$widhig_hg = $high_wd[3];
				$wh_hg = explode(' ', $widhig_hg);
				$wh1_hg = $wh_hg[0];
				$widths_hg = explode('=', $wh1_hg);
				$width1_hg = substr($widths_hg[1], 0, -1);
				$width_go_hg = substr($width1_hg, 1);
				if ($width_go_hg != 640) {
					$this->error("模板1轮播背景图宽度不等于640");
				}
			}
			$first_focus_pic1 = $_FILES['first_focus_pic1'];
			$first_focus_pic2 = $_FILES['first_focus_pic2'];
			$first_focus_pic3 = $_FILES['first_focus_pic3'];
			$first_focus_pic4 = $_FILES['first_focus_pic4'];
			$first_focus_pic5 = $_FILES['first_focus_pic5'];
			$first_focus_pic6 = $_FILES['first_focus_pic6'];
			if($first_focus_bg['type'] == 'image/gif' || $first_focus_pic1['type'] == 'image/gif' || $first_focus_pic2['type'] == 'image/gif' || $first_focus_pic3['type'] == 'image/gif' || $first_focus_pic4['type'] == 'image/gif' || $first_focus_pic5['type'] == 'image/gif' || $first_focus_pic6['type'] == 'image/gif'){
				$this -> error("轮播图不允许上传gif格式");
			}
		}
		if($second_type == 3){
			$second_focus_bg = $_FILES['second_focus_bg'];
			if($second_focus_bg['size']){
				$high_wd = getimagesize($second_focus_bg['tmp_name']);
				$widhig_hg = $high_wd[3];
				$wh_hg = explode(' ', $widhig_hg);
				$wh1_hg = $wh_hg[0];
				$widths_hg = explode('=', $wh1_hg);
				$width1_hg = substr($widths_hg[1], 0, -1);
				$width_go_hg = substr($width1_hg, 1);
				if ($width_go_hg != 640) {
					$this->error("模板2轮播背景图宽度不等于640");
				}
			}
			$second_focus_pic1 = $_FILES['second_focus_pic1'];
			$second_focus_pic2 = $_FILES['second_focus_pic2'];
			$second_focus_pic3 = $_FILES['second_focus_pic3'];
			$second_focus_pic4 = $_FILES['second_focus_pic4'];
			$second_focus_pic5 = $_FILES['second_focus_pic5'];
			$second_focus_pic6 = $_FILES['second_focus_pic6'];
			if($second_focus_bg['type'] == 'image/gif' || $second_focus_pic1['type'] == 'image/gif' || $second_focus_pic2['type'] == 'image/gif' || $second_focus_pic3['type'] == 'image/gif' || $second_focus_pic4['type'] == 'image/gif' || $second_focus_pic5['type'] == 'image/gif' || $second_focus_pic6['type'] == 'image/gif'){
				$this -> error("轮播图不允许上传gif格式");
			}
		}
		if($third_type == 3){
			$third_focus_bg = $_FILES['third_focus_bg'];
			if($third_focus_bg['size']){
				$high_wd = getimagesize($third_focus_bg['tmp_name']);
				$widhig_hg = $high_wd[3];
				$wh_hg = explode(' ', $widhig_hg);
				$wh1_hg = $wh_hg[0];
				$widths_hg = explode('=', $wh1_hg);
				$width1_hg = substr($widths_hg[1], 0, -1);
				$width_go_hg = substr($width1_hg, 1);
				if ($width_go_hg != 640) {
					$this->error("模板3轮播背景图宽度不等于640");
				}
			}
			$third_focus_pic1 = $_FILES['third_focus_pic1'];
			$third_focus_pic2 = $_FILES['third_focus_pic2'];
			$third_focus_pic3 = $_FILES['third_focus_pic3'];
			$third_focus_pic4 = $_FILES['third_focus_pic4'];
			$third_focus_pic5 = $_FILES['third_focus_pic5'];
			$third_focus_pic6 = $_FILES['third_focus_pic6'];
			if($third_focus_bg['type'] == 'image/gif' || $third_focus_pic1['type'] == 'image/gif' || $third_focus_pic2['type'] == 'image/gif' || $third_focus_pic3['type'] == 'image/gif' || $third_focus_pic4['type'] == 'image/gif' || $third_focus_pic5['type'] == 'image/gif' || $third_focus_pic6['type'] == 'image/gif'){
				$this -> error("轮播图不允许上传gif格式");
			}
		}
		if($banner_img['type'] == 'image/gif' || $download_one_pic['type'] == 'image/gif' || $first_img['type'] == 'image/gif' || $bbs_img['type'] == 'image/gif' || $second_img['type'] == 'image/gif' || $download_two_pic['type'] == 'image/gif' || $third_img['type'] == 'image/gif' || $page_img['type'] == 'image/gif' || $download_one_bg['type'] == 'image/gif' || $bbs_bg['type'] == 'image/gif' || $download_two_bg['type'] == 'image/gif'){
			$this -> error("gif图不允许被上传");
		}
		if($banner_img['size'] || $download_one_pic['size'] || $first_img['size'] || $bbs_img['size'] || $second_img['size'] || $download_two_pic['size'] || $third_img['size'] || $download_one_bg['size'] || $bbs_bg['size'] || $download_two_bg['size'] || $first_focus_pic1['size'] || $first_focus_pic2['size'] || $first_focus_pic3['size'] || $first_focus_pic4['size'] || $first_focus_pic5['size'] || $first_focus_pic6['size'] || $second_focus_pic1['size'] || $second_focus_pic2['size'] || $second_focus_pic3['size'] || $second_focus_pic4['size'] || $second_focus_pic5['size'] || $second_focus_pic6['size'] || $third_focus_pic1['size'] || $third_focus_pic2['size'] || $third_focus_pic3['size'] || $third_focus_pic4['size'] || $third_focus_pic5['size'] || $third_focus_pic6['size'] || $first_focus_bg['size'] || $second_focus_bg['size'] || $third_focus_bg['size']){
			$path=date("Ym/d/",time());
			$config = array(
			'multi_config' => array(
				'banner_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_one_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'bbs_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_two_pic' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'page_img' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_one_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'bbs_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'download_two_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_bg' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'first_focus_pic6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'second_focus_pic6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic1' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic2' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic3' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic4' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic5' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
				'third_focus_pic6' => array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'enable_resize' => false,
				),
			));
			$lists = $this->_uploadapk(0, $config);
			foreach ($lists['image'] as $key => $val) {
                if ($val['post_name'] == 'banner_img') {
                    $data['banner_img'] = $val['url'];
                }
                if ($val['post_name'] == 'download_one_pic') {
                    $download_one_img_go = $val['url'];
                }
				if ($val['post_name'] == 'first_img') {
                    $first_img_go = $val['url'];
                }
				if ($val['post_name'] == 'bbs_img') {
                    $bbs_img_go = $val['url'];
                }
				if ($val['post_name'] == 'second_img') {
                    $second_img_go = $val['url'];
                }
				if ($val['post_name'] == 'download_two_pic') {
                    $download_two_img_go = $val['url'];
                }
				if ($val['post_name'] == 'third_img') {
                    $third_img_go = $val['url'];
                }
				if ($val['post_name'] == 'page_img') {
                    $page_img_go = $val['url'];
                }
				if ($val['post_name'] == 'download_one_bg') {
                    $data['download_one_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'bbs_bg') {
                    $data['bbs_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'download_two_bg') {
                    $data['download_two_bg'] = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_bg') {
                    $first_focus_bg = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_bg') {
                    $second_focus_bg = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_bg') {
                    $third_focus_bg = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic1') {
                    $first_focus_pics1 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic2') {
                    $first_focus_pics2 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic3') {
                    $first_focus_pics3 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic4') {
                    $first_focus_pics4 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic5') {
                    $first_focus_pics5 = $val['url'];
                }
				if ($val['post_name'] == 'first_focus_pic6') {
                    $first_focus_pics6 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic1') {
                    $second_focus_pics1 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic2') {
                    $second_focus_pics2 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic3') {
                    $second_focus_pics3 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic4') {
                    $second_focus_pics4 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic5') {
                    $second_focus_pics5 = $val['url'];
                }
				if ($val['post_name'] == 'second_focus_pic6') {
                    $second_focus_pics6 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic1') {
                    $third_focus_pics1 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic2') {
                    $third_focus_pics2 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic3') {
                    $third_focus_pics3 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic4') {
                    $third_focus_pics4 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic5') {
                    $third_focus_pics5 = $val['url'];
                }
				if ($val['post_name'] == 'third_focus_pic6') {
                    $third_focus_pics6 = $val['url'];
                }
            }
		}
		
		$old_result = $model -> table('sj_pre_download') -> where(array('id' => $id)) -> select();
		if($first_type == 3){
			$first_focus_pic = '';
			if(!$first_focus_pics1 && !$first_focus_pics2 && !$first_focus_pics3 && !$first_focus_pics4 && !$first_focus_pics5 && !$first_focus_pics6 && !$old_result[0]['first_focus_pic']){
				$this -> error("模板1请至少上传1张轮播图");
			}
			if($first_focus_pics1){
				$first_focus_pic = $first_focus_pics1;
			}
			if($first_focus_pics2 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics2;
			}elseif($first_focus_pics2 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics2;
			}
			if($first_focus_pics3 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics3;
			}elseif($first_focus_pics3 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics3;
			}
			if($first_focus_pics4 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics4;
			}elseif($first_focus_pics4 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics4;
			}
			if($first_focus_pics5 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics5;
			}elseif($first_focus_pics5 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics5;
			}
			if($first_focus_pics6 && $first_focus_pic){
				$first_focus_pic = $first_focus_pic.','.$first_focus_pics6;
			}elseif($first_focus_pics5 && !$first_focus_pic){
				$first_focus_pic = $first_focus_pics6;
			}
			if($first_focus_pic){
				$data['first_focus_pic'] = $first_focus_pic;
			}
			if($_FILES['first_focus_bg']){
				$data['first_focus_bg'] = $first_focus_bg;
			}
		}

		if($second_type == 3){
			$second_focus_pic = '';
			if(!$second_focus_pics1 && !$second_focus_pics2 && !$second_focus_pics3 && !$second_focus_pics4 && !$second_focus_pics5 && !$second_focus_pics6 && !$old_result[0]['first_focus_pic']){
				$this -> error("模板2请至少上传1张轮播图");
			}
			if($second_focus_pics1){
                            echo 1;
				$second_focus_pic = $second_focus_pics1;
			}
			if($second_focus_pics2 && $second_focus_pic){
                            echo 2;
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics2;
			}elseif($second_focus_pics2 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics2;
			}
			if($second_focus_pics3 && $second_focus_pic){
                            echo 3;
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics3;
			}elseif($second_focus_pics3 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics3;
			}
			if($second_focus_pics4 && $second_focus_pic){
                            echo 4;
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics4;
			}elseif($second_focus_pics4 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics4;
			}
			if($second_focus_pics5 && $second_focus_pic){
                            echo 5;
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics5;
			}elseif($second_focus_pics5 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics5;
			}
			if($second_focus_pics6 && $second_focus_pic){
                            echo 6;
				$second_focus_pic = $second_focus_pic.','.$second_focus_pics6;
			}elseif($second_focus_pics6 && !$second_focus_pic){
				$second_focus_pic = $second_focus_pics6;
			}
			if($second_focus_pic){
				$data['second_focus_pic'] = $second_focus_pic;
			}
			if($_FILES['second_focus_bg']){
				$data['second_focus_bg'] = $second_focus_bg;
			}
		}
		
		if($third_type == 3){
			$third_focus_pic = '';
			if(!$third_focus_pics1 && !$third_focus_pics2 && !$third_focus_pics3 && !$third_focus_pics4 && !$third_focus_pics5 && !$third_focus_pics6 && !$old_result[0]['first_focus_pic']){
				$this -> error("模板3请至少上传1张轮播图");
			}
			if($third_focus_pics1){
				$third_focus_pic = $third_focus_pics1;
			}
			if($third_focus_pics2 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics2;
			}elseif($third_focus_pics2 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics2;
			}
			if($third_focus_pics3 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics3;
			}elseif($third_focus_pics3 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics3;
			}
			if($third_focus_pics4 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics4;
			}elseif($third_focus_pics4 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics4;
			}
			if($third_focus_pics5 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics5;
			}elseif($third_focus_pics5 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics5;
			}
			if($third_focus_pics6 && $third_focus_pic){
				$third_focus_pic = $third_focus_pic.','.$third_focus_pics6;
			}elseif($third_focus_pics6 && !$third_focus_pic){
				$third_focus_pic = $third_focus_pics6;
			}
			if($third_focus_pic){
				$data['third_focus_pic'] = $third_focus_pic;
			}
			if($_FILES['third_focus_bg']){
				$data['third_focus_bg'] = $third_focus_bg;
			}
		}
		
		if($download_one == 1){
			$data['download_one_img'] = '';
			$data['download_one_btn'] = $download_one_btn;
		}else{
			if($download_one_img_go){
				$data['download_one_img'] = $download_one_img_go;
			}
			$data['download_one_btn'] = '';
		}
		
		if($bbs_type == 1){
			$data['bbs_img'] = '';
			$data['bbs_btn'] = $bbs_btn;
		}else{
			if($bbs_img_go){
				$data['bbs_img'] = $bbs_img_go;
			}
			$data['bbs_btn'] = '';
		}
		
		if($download_two == 1){
			$data['download_two_img'] = '';
			$data['download_two_btn'] = $download_two_btn;
		}else{
			if($download_two_img_go){
				$data['download_two_img'] = $download_two_img_go;
			}
			$data['download_two_btn'] = '';
		}
		
		if($first_type == 1){
			if($first_img_go){
				$data['first_img'] = $first_img_go;
			}
			$data['first_text'] = '';
			$data['first_bg_color'] = '';
		}elseif($first_type == 2){
			$data['first_img'] = '';
			$data['first_text'] = $first_text;
			$data['first_bg_color'] = implode(',',$first_bg_color);
		}
		
		if($second_type == 1){
			if($second_img_go){
				$data['second_img'] = $second_img_go;
			}
			$data['second_text'] = '';
			$data['second_bg_color'] = '';
		}elseif($second_type == 2){
			$data['second_img'] = '';
			$data['second_text'] = $second_text;
			$data['second_bg_color'] = implode(',',$second_bg_color);
		}
		
		if($third_type == 1){
			if($third_img_go){
				$data['third_img'] = $third_img_go;
			}
			$data['third_text'] = '';
			$data['third_bg_color'] = '';
		}elseif($third_type == 2){
			$data['third_img'] = '';
			$data['third_text'] = $third_text;
			$data['third_bg_color'] = implode(',',$third_bg_color);
		}
		
		if($page_type == 1){
			$data['page_img'] = '';
			$data['page_color'] = implode(',',$page_color);
		}elseif($page_type == 2){
			if($page_img_go){
				$data['page_img'] = $page_img_go;
			}
			$data['page_color'] = '';
		}
		
		$data['update_tm'] = time();
		$result = $model -> table('sj_pre_download') -> where(array('id' => $id)) -> save($data);

		if($result){
			$this -> writelog("已编辑id为{$id}的预下载活动页面", 'sj_pre_download',$id,__ACTION__ ,'','edit');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Predownload/page_list");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_img(){
		$model = new Model();
		$id = $_GET['id'];
		$obj = $_GET['obj'];
		if($obj == 1){
			$where = 'download_one_bg';
		}elseif($obj == 2){
			$where = 'bbs_bg';
		}elseif($obj == 3){
			$where = 'download_two_bg';
		}
		$result = $model -> table('sj_pre_download') -> where(array('id' => $id)) -> save(array($where => ''));
		if($result){
			echo 200;
			return 200;
		}
	}
	
	
	function get_soft(){
		$model = new Model();
		$package = $_GET['package'];
		$result = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> order('softid DESC') -> limit(1) -> select();
		echo $result[0]['softname'];
		return $result[0]['softname'];
		
	}
	
	function del_page(){
		$model = new Model();
		$id = $_GET['id'];
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$result = $model -> table('sj_pre_download') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
				$this -> writelog("已删除id为{$id}的预下载活动页面", 'sj_pre_download',$id,__ACTION__ ,'','del');
			$this -> assign('jumpUrl',"/index.php/Sendnum/Predownload/page_list/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function hex2rgb($hexColor) {
		$color = str_replace('#', '', $hexColor);
		if (strlen($color) > 3) {
			$rgb = array(
				'r' => hexdec(substr($color, 0, 2)),
				'g' => hexdec(substr($color, 2, 2)),
				'b' => hexdec(substr($color, 4, 2))
			);
		} else {
			$color = $hexColor;
			$r = substr($color, 0, 1) . substr($color, 0, 1);
			$g = substr($color, 1, 1) . substr($color, 1, 1);
			$b = substr($color, 2, 1) . substr($color, 2, 1);
			$rgb = array(
				'r' => hexdec($r),
				'g' => hexdec($g),
				'b' => hexdec($b)
				);
		}
		return $rgb;
	}

	function RGBToHex($rgb){
		$regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
		$re = preg_match($regexp, $rgb, $match);
		$re = array_shift($match);
		$hexColor = "#";
		$hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');  
		for ($i = 0; $i < 3; $i++) {  
		$r = null;
		$c = $match[$i];
		$hexAr = array(); 
		while ($c > 16) {
			$r = $c % 16;
			$c = ($c / 16) >> 0;
			array_push($hexAr, $hex[$r]);
		}
		array_push($hexAr, $hex[$c]);
		$ret = array_reverse($hexAr);
		$item = implode('', $ret);
		$item = str_pad($item, 2, '0', STR_PAD_LEFT);
		$hexColor .= $item;  
		}
		return $hexColor;  
	} 

	
}
