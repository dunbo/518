<?php
	class AnimationAdAction extends CommonAction {
        // 动画广告图片大小
        private $image_width = 70;
        private $image_height = 70;
		// 动画广告645图片大小
		private $image_width_645 = 100;
		private $image_height_645 = 100;
        
        // 弹窗广告图片大小
        private $popup_image_width = 170;
        private $popup_image_height = 220;
		private $popup_image_width_high = 226;
        private $popup_image_height_high = 294;
		
		//居中弹窗广告
		private $center_image_width = 468;
        private $center_image_height = 566;
		private $center_image_width_high = 684;
        private $center_image_height_high = 872;

		//ICON图片
		private $icon_width = 60;
		private $icon_height = 60;


		function index() {
			$model = M();
            $where = array();
            // 是已过期列表还是未过期列表
            $now = time();
            $overdue = $_GET['overdue'];
            if (!$overdue) {
                // 默认是未过期
                $overdue = -1;
            }
            if ($overdue == 1) {
                // 如果是已过期列表，判断搜索的结束时间是否比当前要大
                if ($where['end_at']) {
                    $where['end_at'][1] .= " and end_at<{$now}";
                } else {
                    $where['end_at'] = array('exp', "<{$now}");
                }
                //$order = "end_at desc";
				$order = "start_at asc";
            } else if ($overdue == -1) {
                // 如果是未过期列表，判断搜索的结束时间是否比当前要大
                if ($where['end_at']) {
                    $where['end_at'][1].= " and end_at>{$now}";
                } else {
                    $where['end_at'] = array('exp', ">{$now}");
                }
                $order = "start_at asc";
            }
            $where['status'] = 1;
            //添加产品pid
            $pid = $_GET['pid'] ? $_GET['pid'] : 1;
            if($pid) $where['pid'] = $pid;
            // 分页
            import("@.ORG.Page");
            $limit = 10;
            $count = $model->table('sj_animation_ad')->where($where)->count();
            $page  = new Page($count, $limit);
            // 当前页数据
			$list = $model->table('sj_animation_ad')->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
			//合作形式
			$util = D("Sj.Util"); 
            // 处理list
            foreach ($list as $key => $value) {
                $oid = $value['oid'];
                $cid = $value['cid'];
                $show_place = $value['show_place'];
                $show_place_name = $this->get_show_place_name($show_place);
                $list[$key]['show_place_name'] = $this->shorten_sentence($show_place_name);
                $content_type = $value['content_type'];
                if ($content_type == 2) {
                    // 活动名称
                    $list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($value['activity_id']);
                } else if ($content_type == 3) {
                    // 专题名称
                    $list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($value['feature_id']);
                } else if ($content_type == 4) {
                    // 页面名称
                    $list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($value['page_type']);
                }
                $list[$key]['oname'] = $oid ? $operators_key[$oid] : '不限';
                $list[$key]['cname'] = $cid ? $channels_key[$cid] : '不限';
				//合作形式
				$typelist = $util->getHomeExtentSoftTypeList($value['co_type']);
				foreach($typelist as $k => $v){
					if($v[1] == true)
					{
						$list[$key]['co_types'] = $v[0];
					}
				}
            }
			$category_list = ContentTypeModel::getCategoryTypes();

			//获取产品列表
			$product_model = M('product');
	        $product_list = $product_model ->table('pu_product')->where('status = 1')->findAll();
	        foreach ($product_list as $product_value) {
	            $product_lists[$product_value['pid']] = $product_value;
	        }
	        $this->assign ("product_list", $product_lists);
	        $this->assign ("pid", $pid);

			$this->assign('list', $list);
            $this->assign('apkurl', ATTACHMENT_HOST);
			$this->assign('category_list', $category_list);
            $this->assign('overdue', $overdue);
            $this->assign("page", $page->show());
			$this->display();
		}
        
        function show_all_content() {
            $model = new Model();
			$id = $_GET['id'];
			$find = $model->table('sj_animation_ad')->where(array('id'=>$id))->find();
            $show_place_name = $this->get_show_place_name($find['show_place']);
            $this->assign('show_place_name', $show_place_name);
            $this->display('show_all_content');
        }

		public function check_name($name,$id,$start_tm,$end_tm){
			$time = time();
			if (mb_strlen($name, 'utf-8') > 6||mb_strlen($name, 'utf-8') < 2) {
				$this->error("ICON名称2-6字符");
			}
			if(!$start_tm) $this->error("开始时间不能为空");
			if(!$end_tm) $this->error("结束时间不能为空");
			$start_tm = strtotime($start_tm);
			$end_tm = strtotime($end_tm);
			$model = M();
			$where = array('ad_name'=>$name,'image_type'=>5);
			if(!empty($id)) $where['id'] = array('exp'," != {$id} ");

			$where['end_at'] = array('exp'," >= {$time} ");
			$info = $model->table('sj_animation_ad')->where($where)->select();
			if($info){
				$bo = false;
				foreach($info as $k=>$v){
					if(is_time_cross($v['start_at'], $v['end_at'],$start_tm,$end_tm)){
						$bo = true;
						break;
					}
				}
				if($bo)
				$this->error('ICON名称已存在');
			}
		}

		public function save_pub(&$map,$type = '', $image_type){
			if($image_type == 1||$image_type == 6){
				if($type != 'edit'){
					if (!$_FILES['image_url_645']['name'])
						$this->error("请上传动画图片6.4.5");
				}
				if($_FILES['image_url_645']['name']){
					// 取得图片后缀
					$suffix = preg_match("/\.(jpg|png|gif)$/", $_FILES['image_url_645']['name'],$matches);
					if ($matches) {
						$suffix = $matches[0];
					} else {
						$this->error('上传动画图片6.4.5格式错误！');
					}
					// 判断图片长和宽
					$img_info_arr = getimagesize($_FILES['image_url_645']['tmp_name']);
					if (!$img_info_arr) {
						$this->error('上传动画图片6.4.5出错！');
					}
					$width = $img_info_arr[0];
					$height = $img_info_arr[1];
					if ($width != $this->image_width_645 || $height != $this->image_height_645)
						$this->error("上传动画图片6.4.5大小错误，宽需为{$this->image_width_645}px，高需为{$this->image_height_645}px");
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);

					$image_path =  $folder . time() . '_' . rand(1000,9999) . $suffix;
					$i_path = UPLOAD_PATH . $image_path;
					$ret = move_uploaded_file($_FILES['image_url_645']['tmp_name'], $i_path);
					$map['high_image_url'] = $image_path;
				}
			}

			$file_name = array();
			$file_limit = array();
			if($image_type == 3){
				$name = 'high_png_url';
				$file_name[] = $name;
				$file_limit[$name] = array(
					'png|PNG',
					'上传高分PNG图片格式错误,需为png格式',
					"上传高分PNG图片大小错误,宽需为{$this->center_image_width_high}px，高需为{$this->center_image_height_high}px",
					array($this->center_image_width_high,$this->center_image_height_high)
				);
			}
			include_once SERVER_ROOT. '/tools/functions.php';
			foreach($file_name as $v){
				if($_FILES[$v]['name']){
					$suffix = preg_match("/\.({$file_limit[$v][0]})$/", $_FILES[$v]['name'],$matches);
					if ($matches) {
						$suffix = $matches[0];
					} else {
						$this->error($file_limit[$v][1]);
					}
					// 判断图片长和宽
					$img_info_arr = getimagesize($_FILES[$v]['tmp_name']);
					if (!$img_info_arr) {
						$this->error($file_limit[$v][2]);
					}
					$width = $img_info_arr[0];
					$height = $img_info_arr[1];
					if ($width != $file_limit[$v][3][0] || $height != $file_limit[$v][3][1])
						$this->error($file_limit[$v][2]);
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);

					$image_path =  $folder . time() . '_' . rand(1000,9999) . $suffix;
					$i_path = UPLOAD_PATH . $image_path;
					move_uploaded_file($_FILES[$v]['tmp_name'], $i_path);
					$map[$v] = $image_path;
					if($v == 'high_png_url'){
						$high_png_path =  $folder . time() . '_high_png_80_' . rand(1000,9999) . $suffix;
						$high_png_80_path = UPLOAD_PATH . $high_png_path;
						$high_80=image_strip_size($i_path,$high_png_80_path,80*1024);
						if($high_80)
						{
							$map['high_png_url_80'] = $high_png_path;
						}
					}
				}
			}
		}

        public function add_content() {
			$model = M();
            if ($_POST) {
                $map = array();
                // 样式
                $image_type = trim($_POST['image_type']);
                if (!$image_type) {
                    $this->error("样式不能为空");
                }
                $map['image_type'] = $image_type;
                // 标题
                $ad_name = trim($_POST['ad_name']);
                if (!$ad_name) {
					if($image_type == 5){
						$this->error("ICON名称不能为空");
					}else{
						$this->error("广告名称不能为空");
					}

                }
				if($image_type == 5){
					$this->check_name($ad_name,'',$_POST['start_at'],$_POST['end_at']);
				}
                $map['ad_name'] = $ad_name;
                $content_type = $_POST['content_type'];
                if (!$content_type) {
                    $this->error("请输入类型");
                }
                // 图片不能为空
				if($image_type!=4&&$image_type!=5)
				{

					if (!$_FILES['image_url']['name'])
						$this->error("请上传图片");
					// 取得图片后缀
					$suffix = preg_match("/\.(jpg|png|gif)$/", $_FILES['image_url']['name'],$matches);
					if ($matches) {
						$suffix = $matches[0];
					} else {
						$this->error('上传图片格式错误！');
					}
					// 判断图片长和宽
					$img_info_arr = getimagesize($_FILES['image_url']['tmp_name']);
					if (!$img_info_arr) {
						$this->error('上传图片出错！');
					}
					$width = $img_info_arr[0];
					$height = $img_info_arr[1];
				}
                if ($image_type == 1||$image_type == 6) {
                    // 如果样式为动画广告
                    if ($width != $this->image_width || $height != $this->image_height)
                        $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
                } else if($image_type==2) {
                    // 如果样式为弹窗广告
                    if ($width != $this->popup_image_width || $height != $this->popup_image_height)
                        $this->error("上传图片大小错误，宽需为{$this->popup_image_width}px，高需为{$this->popup_image_height}px");
                }else if($image_type==3) {
                    // 如果样式为居中弹窗广告  v6.4增加
                    if ($width != $this->center_image_width || $height != $this->center_image_height)
                        $this->error("上传图片大小错误，宽需为{$this->center_image_width}px，高需为{$this->center_image_height}px");
                }
				//V6.0弹窗广告增加高分图片 v6.4新增居中弹窗广告
				if($image_type==2||$image_type==3)
				{
					$image_limit = 'jpg|png|gif';
					if($image_type == 3) $image_limit = 'jpg|gif';
					if (!$_FILES['image_url_high']['name'])
                    $this->error("请上传高分图片");
					// 取得图片后缀
					$suffix_high = preg_match("/\.({$image_limit})$/", $_FILES['image_url_high']['name'],$matches_high);
					if ($matches_high) {
						$suffix_high = $matches_high[0];
					} else {
						$this->error('上传高分图片格式错误！');
					}
					// 判断图片长和宽
					$img_info_arr_high = getimagesize($_FILES['image_url_high']['tmp_name']);
					if (!$img_info_arr_high) {
						$this->error('上传高分图片出错！');
					}
					$width_high = $img_info_arr_high[0];
					$height_high = $img_info_arr_high[1];
					
					if($image_type==2)
					{
						if ($width_high != $this->popup_image_width_high || $height_high != $this->popup_image_height_high)
						$this->error("上传高分图片大小错误，宽需为{$this->popup_image_width_high}px，高需为{$this->popup_image_height_high}px");
					}
					else if($image_type==3)
					{
						if ($width_high != $this->center_image_width_high || $height_high != $this->center_image_height_high)
						$this->error("上传高分图片大小错误，宽需为{$this->center_image_width_high}px，高需为{$this->center_image_height_high}px");
					}
				}

				//V6.4.1 新增桌面ICON
				if($image_type == 5){
					if (!$_FILES['icon_url']['name'])
						$this->error("请上传ICON图片");
					// 取得图片后缀
					$suffix_icon = preg_match("/\.(jpg|png)$/", $_FILES['icon_url']['name'],$matches_icon);
					if ($matches_icon) {
						$suffix_icon = $matches_icon[0];
					} else {
						$this->error('上传ICON图片格式错误！');
					}
					// 判断图片长和宽
					$icon_info_arr = getimagesize($_FILES['icon_url']['tmp_name']);
					if (!$icon_info_arr) {
						$this->error('上传ICON图片出错！');
					}
					$icon_width = $icon_info_arr[0];
					$icon_height = $icon_info_arr[1];
					if ($icon_width != $this->icon_width || $icon_height != $this->icon_height)
						$this->error("上传ICON图片大小错误，宽需为{$this->icon_width}px，高需为{$this->icon_width}px");
				}
				if($image_type == 1||$image_type == 6||$image_type == 3){
					$this->save_pub($map, 'add', $image_type);
				}
				//合作形式
				if(isset($_POST['co_type'])){		
					$map['co_type'] = $_POST['co_type'];	
				}else{		
					$map['co_type'] = 0;	
				}
                // 展示页面
                $show_place_arr = $_POST['show_place'];
                $map['show_place'] = 0;
                foreach ($show_place_arr as $show_place) {
                    $map['show_place'] |= $show_place;
                }
				if($image_type!=4&&$image_type!=5&&$image_type!=6)
				{
					if (!$map['show_place']) {
						$this->error("页面位置不能为空");
					}
				}
				//v6.3合作资讯和合作站点
				if($map['show_place']&2097152)
				{
					if($_POST['coop_channel'])
					{
						$place_param_arr['2097152']=$_POST['coop_channel'];
					}
					else
					{
						$this->error("请选择6.3资讯站点！");
					}
				}
				if($map['show_place']&4194304)
				{
					if($_POST['coop_site'])
					{
						$place_param_arr['4194304']=$_POST['coop_site'];
					}
					else
					{
						$this->error("请选择6.3合作站点！");
					}
				}
				if($map['show_place']&8388608)
				{
					if($_POST['coop_app_channel'])
					{
						$place_param_arr['8388608']=$_POST['coop_app_channel'];
					}
					else
					{
						$this->error("请选择6.4.1应用频道！");
					}
				}
				if($map['show_place']&16777216)
				{
					if($_POST['coop_game_channel'])
					{
						$place_param_arr['16777216']=$_POST['coop_game_channel'];
					}
					else
					{
						$this->error("请选择6.4.1游戏频道！");
					}
				}
				$map['page_place_param']=json_encode($place_param_arr)?json_encode($place_param_arr):'';
				
				//推荐内容处理 合并
				$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				if($rcontent!==true)
				{
					$this -> error($rcontent);
				}
				
                //V6.0精准投放
				//处理上传csv
				$filename=$_FILES['upload_file']['name'];
				if(!$filename&&!$_POST['csv_count'])
				{
					$map['csv_count'] = 0;
					$map['csv_url'] = "";
					$map['is_upload_csv'] = 0;
				}
				if($filename&&!$_POST['csv_count'])
				{
					$this -> error("选择好的文件请点击上传才有效");
				}
				if($_POST['csv_count']&&$_POST['csv_url'])
				{
					$map['csv_count'] = $_POST['csv_count'];
					$map['csv_url'] = $_POST['csv_url'];
					$map['is_upload_csv'] = 1;
				}
				unset($_FILES['upload_file']);
				//渠道id和机型id
				$channel_id_array=$_POST['cid'];
				$cids = array_unique($channel_id_array);
				if (count($cids) > 0) 
				{
					$s = implode(',', $cids);
					$s = ",{$s},";
					$map['cid'] = $s;
				}
				else
				{
					$map['cid'] = ",,";
				}

				$device_did_array=$_POST['did'];
				$dids = array_unique($device_did_array);
				if (count($dids) > 0) 
				{
					$d= implode(',', $dids);
					$d = ",{$d},";
					$map['device_did'] = $d;
				}
				else
				{
					$map['device_did'] = ",,";
				}
				//运营商和固件版本和市场版本
				$map['oid'] = ','. implode(',', $_POST['oid']). ',';
				$map['firmware'] = ','. implode(',', $_POST['firmware']). ',';
				$map['version_code'] = ','. implode(',', $_POST['version_code']). ',';
				
                // 开始时间和结束时间
                $start_at = strtotime($_POST['start_at']);
                $end_at = strtotime($_POST['end_at']);
                if (!$start_at) {
                    $this->error("开始时间不能为空");
                }
                if (!$end_at) {
                    $this->error("结束时间不能为空");
                }
                if ($start_at > $end_at) {
                    $this->error("开始时间不能大于结束时间");
                }
                $map['start_at'] = $start_at;
                $map['end_at'] = $end_at;
				//V5.5允许同一类型广告扩充为可配置多个
                $conflict_id = $this->check_conflict($map);
                if ($conflict_id) {
                    $this->error("与后台id为{$conflict_id}的投放时间的页面位置有冲突");
                }
			
				include_once SERVER_ROOT. '/tools/functions.php';
				if($_FILES['image_url']['name']||$_FILES['icon_url']['name']){
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
				}
				if($_FILES['image_url']['name'])
				{
					// 将图片存储起来
					$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
					$relative_path_40 = $folder . time() . '_40_' . rand(1000,9999) . $suffix;
					$img_path = UPLOAD_PATH . $relative_path;
					$img_path_40 = UPLOAD_PATH . $relative_path_40;
					$ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
					$map['image_url'] = $relative_path;
					$low_40=image_strip_size($img_path,$img_path_40,40*1024);
					if($low_40)
					{
						$map['low_image_url_40'] = $relative_path_40;
					}
				}

				if($_FILES['icon_url']['name']){
					$icon_path =  $folder . time() . '_' . rand(1000,9999) . $suffix_icon;
					$i_path = UPLOAD_PATH . $icon_path;
					$ret = move_uploaded_file($_FILES['icon_url']['tmp_name'], $i_path);
					$map['image_url'] = $icon_path;
				}
				if($image_type==2||$image_type==3)
				{
					$relative_path_high = $folder . time() . '_high' . '_' . rand(1000,9999) . $suffix_high;
					$relative_path_high_80 = $folder . time() . '_high_80' . '_' . rand(1000,9999) . $suffix_high;
					$img_path_high = UPLOAD_PATH . $relative_path_high;
					$img_path_high_80 = UPLOAD_PATH . $relative_path_high_80;
					
					$ret_high = move_uploaded_file($_FILES['image_url_high']['tmp_name'], $img_path_high);
					$map['high_image_url'] = $relative_path_high;
					
					$high_80=image_strip_size($img_path_high,$img_path_high_80,80*1024);
					if($high_80)
					{
						$map['high_image_url_80'] = $relative_path_high_80;
					}
				}

                // 创建时间和更新时间
                $map['create_at'] = $map['update_at'] = time();
				$map['admin_id'] = $_SESSION['admin']['admin_id'];
				//关闭类型和点击类型  added by shitingting
				$map['closed_type']=$_POST['close'];
				$map['click_type']=$_POST['click'];
				//V6.4居中弹窗广告 增加关闭倒计时
				$map['closed_count_type']=$_POST['closed_count_type'];
				if($map['closed_count_type']==1) //设置倒计时时间
				{
					if(!$_POST['closed_time'])
					{
						 $this->error("选择关闭倒计时，时间必填");
					}
					else
					{
						$matches = "/^[1-9][0-9]*$/";
						if (!preg_match($matches,trim($_POST['closed_time']))) 
						{
							$this -> error("倒计时时间为正整数");
						}
						else
						{
							$map['closed_time']=trim($_POST['closed_time']);
						}
					}
				}
				if($_POST['package']){
					//屏蔽软件上排期时报警需求 新增  yuesai
	                $AdSearch = D("Sj.AdSearch");
	                $shield_error=$AdSearch->check_shield($_POST['package'],$map['start_at'],$map['end_at']);
	                if($shield_error){
	                    $this -> error($shield_error);
	                }
				}
                
                // 添加


                $parameter_field=json_decode($map['parameter_field'],true);
                $parameter_field['cover_user_type']=trim($_POST['cover_user_type'])?trim($_POST['cover_user_type']):0;
                if($parameter_field['cover_user_type']==1){
                	$parameter_field['activation_date_start']=0;
                	$parameter_field['activation_date_end']=0;
                }elseif($parameter_field['cover_user_type']==2){
                	if(!$_POST['activation_date_start'] || !$_POST['activation_date_end']){
                		$this->error("选择定向用户时，激活日期必填！");
                	}else{
                		if(strtotime(trim($_POST['activation_date_start']))>=strtotime(trim($_POST['activation_date_end']))){
                			$this->error("激活结束日期必须大于开始日期！");
                		}
                	}
                	$parameter_field['activation_date_start']=strtotime(trim($_POST['activation_date_start']));
                	$parameter_field['activation_date_end']=strtotime(trim($_POST['activation_date_end']));
                }else{
                	$parameter_field['activation_date_start']=0;
                	$parameter_field['activation_date_end']=0;
                }

                $map['parameter_field']=json_encode($parameter_field);

                $map['pid'] = $_POST['pid'] ? $_POST['pid'] : 1;//添加产品pid

                $ret = $model->table('sj_animation_ad')->add($map);
                if ($ret) {
                    $this->writelog("灵活运营样式-动画广告：添加了id为{$ret}的内容", 'sj_animation_ad', $ret,__ACTION__ ,"","add");
                    $this->success("添加成功！");
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->assign('image_width', $this->image_width);
                $this->assign('image_height', $this->image_height);
				$this->assign('image_width_645', $this->image_width_645);
				$this->assign('image_height_645', $this->image_height_645);
                $this->assign('popup_image_width', $this->popup_image_width);
                $this->assign('popup_image_height', $this->popup_image_height);
				$this->assign('popup_image_width_high', $this->popup_image_width_high);
                $this->assign('popup_image_height_high', $this->popup_image_height_high);
				$this->assign('icon_width',$this->icon_width);
				$this->assign('icon_height',$this->icon_height);
				//V6.4新增居中弹窗广告
				$this->assign('center_image_width', $this->center_image_width);
                $this->assign('center_image_height', $this->center_image_height);
				$this->assign('center_image_width_high', $this->center_image_width_high);
                $this->assign('center_image_height_high', $this->center_image_height_high);
				//合作形式
				$util = D("Sj.Util");
				$typelist = $util->getHomeExtentSoftTypeList();
				$this->assign('typelist',$typelist);
				
				//6.3除去安智市场的站点
				$coop_where=array(
					'status'=>1,
					'type' => array('in','1,2'),
					//'id'=>array('neq',1),
				);
				$coop_site = $model->table('coop_site')->where($coop_where)->select();
				$this->assign('coop_site',$coop_site);
				//6.3安智市场下的频道（资讯站点） 合作站点type=0 安智市场频道，3 应用下面频道 4游戏下面频道
				$site_where=array(
					'status'=>1,
					'type' => 0,
				);
				$az_site = $model->table('coop_site')->where($site_where)->find();
				
				$channel_where = array(
					'site_id' => $az_site['id'],
					'status' => 1,//1表示启用 0表示停用
					'type'=>array('in','1,3'), //显示普通和内容合作
					'del'=>0,
				);
				$channel_list = $model->table('coop_channel')->where($channel_where)->select();
				$this->assign('channel_list',$channel_list);
				
				//6.4.1 应用下面的频道
				$site_app_where=array(
					'status'=>1,
					'type' => 3,
				);
				$az_site_app = $model->table('coop_site')->where($site_app_where)->find();
				$channel_app_where = array(
					'site_id' => $az_site_app['id'],
					'status' => 1,//1表示启用 0表示停用
					'type'=>array('in','1,3'), //显示普通和内容合作
					'del'=>0,
				);
				$channel_app_list = $model->table('coop_channel')->where($channel_app_where)->select();
				$this->assign('channel_app_list',$channel_app_list);
				//6.4.1 游戏下面的频道
				$site_game_where=array(
					'status'=>1,
					'type' => 4,
				);
				$az_site_game = $model->table('coop_site')->where($site_game_where)->find();
				
				$channel_game_where = array(
					'site_id' => $az_site_game['id'],
					'status' => 1, //1表示启用 0表示停用
					'type'=>array('in','1,3'), //显示普通和内容合作
					'del'=>0,
				);
				$channel_game_list = $model->table('coop_channel')->where($channel_game_where)->select();
				$this->assign('channel_game_list',$channel_game_list);
				//添加产品pid
				$pid = $_GET['pid'] ? $_GET['pid'] : 1;
				$this->assign('pid',$pid);
                $this->display();
            }
        }
        
        public function edit_content() {
            if ($_POST) {
                $model = M();
                $id = $_POST['id'];
                $find = $model->table('sj_animation_ad')->where(array('id' => $id))->find();
                $map = array();
                // 样式不允许编辑
                $image_type = $find['image_type'];
                if (!$image_type) {
                    $this->error("样式不能为空");
                }
                // 标题
                $ad_name = trim($_POST['ad_name']);
                if (!$ad_name) {
					if($image_type == 5){
						$this->error("ICON名称不能为空");
					}else{
						$this->error("广告名称不能为空");
					}
                }
				if($image_type == 5){
					$this->check_name($ad_name,$id,$_POST['start_at'],$_POST['end_at']);
				}
				$map['image_type'] =$image_type;
                $map['ad_name'] = $ad_name;
                // 内容类型不允许编辑，直接从库中读出来
                //$content_type = $_POST['content_type'];
                $content_type = $find['content_type'];
                if (!$content_type) {
                    $this->error("请输入类型");
                }
				//之前正确的图片尺寸再次判断
				$image_old_url=IMGATT_HOST.$find['image_url'];
				$high_image_url_edit= IMGATT_HOST.$find['high_image_url'];
				
				list($width_old_edit, $height_old_edit, $type_old_edit, $attr_old_edit)=getimagesize($image_old_url);
				list($width_high_edit, $height_high_edit, $type_high_edit, $attr_high_edit)=getimagesize($high_image_url_edit);
			
                if ($_FILES['image_url']['name']) {
                    // 取得图片后缀
                    $suffix = preg_match("/\.(jpg|png|gif)$/", $_FILES['image_url']['name'],$matches);
                    if ($matches) {
                        $suffix = $matches[0];
                    } else {
                        $this->error('上传图片格式错误！');
                    }
                    // 判断图片长和宽
                    $img_info_arr = getimagesize($_FILES['image_url']['tmp_name']);
                    if (!$img_info_arr) {
                        $this->error('上传图片出错！');
                    }
                    $width = $img_info_arr[0];
                    $height = $img_info_arr[1];
                    if ($image_type == 1||$image_type == 6) {
                        // 如果样式为动画广告
                        if ($width != $this->image_width || $height != $this->image_height)
                            $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
                    } else if($image_type == 2) {
                        // 如果样式为弹窗广告
                        if ($width != $this->popup_image_width || $height != $this->popup_image_height)
                            $this->error("上传图片大小错误，宽需为{$this->popup_image_width}px，高需为{$this->popup_image_height}px");
                    }else if($image_type == 3) {
                        // V6.4新增如果样式为居中弹窗广告
                        if ($width != $this->center_image_width || $height != $this->center_image_height)
                            $this->error("上传图片大小错误，宽需为{$this->center_image_width}px，高需为{$this->center_image_height}px");
                    }
                }
				else
				{
					if(!$find['image_url']&&$image_type!=4&&$image_type!=5)
					{
						$this->error("请上传图片");
					}
					else
					{
						if ($image_type == 1||$image_type == 6)
						{
							// 如果样式为动画广告
							if ($width_old_edit != $this->image_width || $height_old_edit != $this->image_height)
								$this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
						} 
						else  if($image_type == 2)
						{
							// 如果样式为弹窗广告
							if ($width_old_edit != $this->popup_image_width || $height_old_edit != $this->popup_image_height)
								$this->error("上传图片大小错误，宽需为{$this->popup_image_width}px，高需为{$this->popup_image_height}px");
						}
						else  if($image_type == 3)
						{
							// V6.4如果样式为居中弹窗广告
							if ($width_old_edit != $this->center_image_width || $height_old_edit != $this->center_image_height)
								$this->error("上传图片大小错误，宽需为{$this->center_image_width}px，高需为{$this->center_image_height}px");
						}
					}
				}
				if (($image_type==2||$image_type==3)&&$_FILES['image_url_high']['name']) 
				{
                    // 取得图片后缀
					$prefix = 'jpg|png|gif';
					if($image_type == 3) $prefix = 'jpg|gif';
                    $suffix_high = preg_match("/\.({$prefix})$/", $_FILES['image_url_high']['name'],$matches_high);
                    if ($matches_high) {
                        $suffix_high = $matches_high[0];
                    } else {
                        $this->error('上传高分图片格式错误！');
                    }
                    // 判断图片长和宽
                    $img_info_arr_high = getimagesize($_FILES['image_url_high']['tmp_name']);
                    if (!$img_info_arr_high) {
                        $this->error('上传高分图片出错！');
                    }
                    $width_high = $img_info_arr_high[0];
                    $height_high = $img_info_arr_high[1];
                   
					if($image_type==2)
					{
						if ($width_high != $this->popup_image_width_high || $height_high != $this->popup_image_height_high)
						$this->error("上传高分图片大小错误，宽需为{$this->popup_image_width_high}px，高需为{$this->popup_image_height_high}px");
					}
					else if($image_type==3)
					{
						if ($width_high != $this->center_image_width_high || $height_high != $this->center_image_height_high)
						$this->error("上传高分图片大小错误，宽需为{$this->center_image_width_high}px，高需为{$this->center_image_height_high}px");
					}
                }
				elseif(($image_type==2||$image_type==3)&&!$_FILES['image_url_high']['name'])
				{
					if(!$find['high_image_url'])
					{
						$this->error("请上传高分图片");
					}
					else
					{	
						if($image_type==2)
						{
							if ($width_high_edit != $this->popup_image_width_high || $height_high_edit != $this->popup_image_height_high)
							$this->error("上传图片大小错误，宽需为{$this->popup_image_width_high}px，高需为{$this->popup_image_height_high}px");
						}
						else if($image_type==3)
						{
							if ($width_high_edit != $this->center_image_width_high || $height_high_edit != $this->center_image_height_high)
							$this->error("上传图片大小错误，宽需为{$this->center_image_width_high}px，高需为{$this->center_image_height_high}px");
						}
					}
				}
				//V6.4.1 新增桌面ICON
				if($image_type == 5){
					if($_FILES['icon_url']['name']){
						// 取得图片后缀
						$suffix_icon = preg_match("/\.(jpg|png)$/", $_FILES['icon_url']['name'],$matches_icon);
						if ($matches_icon) {
							$suffix_icon = $matches_icon[0];
						} else {
							$this->error('上传ICON图片格式错误！');
						}
						// 判断图片长和宽
						$icon_info_arr = getimagesize($_FILES['icon_url']['tmp_name']);
						if (!$icon_info_arr) {
							$this->error('上传ICON图片出错！');
						}
						$icon_width = $icon_info_arr[0];
						$icon_height = $icon_info_arr[1];
						if ($icon_width != $this->icon_width || $icon_height != $this->icon_height)
							$this->error("上传ICON图片大小错误，宽需为{$this->icon_width}px，高需为{$this->icon_width}px");
					}

				}
				if($image_type == 1||$image_type == 6||$image_type==3){
					$this->save_pub($map,'edit', $image_type);
				}
				//合作形式
				if(isset($_POST['co_type'])){
					$map['co_type'] = $_POST['co_type'];
				}else{
					$map['co_type'] = 0;
				}
                // 展示页面
                $show_place_arr = $_POST['show_place'];
                $map['show_place'] = 0;
                foreach ($show_place_arr as $show_place) {
                    $map['show_place'] |= $show_place;
                }
				if($image_type!=4&&$image_type!=5&&$image_type!=6)
				{
					if (!$map['show_place']) {
						$this->error("页面位置不能为空");
					}
				}
				
				//v6.3合作资讯和合作站点
				if($map['show_place']&2097152)
				{
					if($_POST['coop_channel'])
					{
						$place_param_arr['2097152']=$_POST['coop_channel'];
					}
					else
					{
						$this->error("请选择6.3资讯站点！");
					}
				}
				if($map['show_place']&4194304)
				{
					if($_POST['coop_site'])
					{
						$place_param_arr['4194304']=$_POST['coop_site'];
					}
					else
					{
						$this->error("请选择6.3合作站点！");
					}
				}
				if($map['show_place']&8388608)
				{
					if($_POST['coop_app_channel'])
					{
						$place_param_arr['8388608']=$_POST['coop_app_channel'];
					}
					else
					{
						$this->error("请选择6.4.1应用频道！");
					}
				}
				if($map['show_place']&16777216)
				{
					if($_POST['coop_game_channel'])
					{
						$place_param_arr['16777216']=$_POST['coop_game_channel'];
					}
					else
					{
						$this->error("请选择6.4.1游戏频道！");
					}
				}
				$map['page_place_param']=json_encode($place_param_arr)?json_encode($place_param_arr):'';
				
				//推荐内容处理 合并
				$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				if($rcontent!==true)
				{
					$this -> error($rcontent);
				}
                //V6.0精准投放
				//处理上传csv
				$filename=$_FILES['upload_file']['name'];
				if(!$filename&&!trim($_POST['csv_count'])&&trim($_POST['have_pre_dl']))
				{
					$map['csv_count'] = trim($_POST['pre_dl_count']);
					$map['csv_url'] = trim($_POST['have_pre_dl']);
					$map['is_upload_csv'] = 1;
				}
				if(!$filename&&!$_POST['csv_url']&&!trim($_POST['have_pre_dl']))
				{
					$map['csv_count'] = 0;
					$map['csv_url'] = "";
				}
				if($filename&&!$_POST['csv_count'])
				{
					$this -> error("选择好的文件请点击上传才有效");
				}
				if(trim($_POST['csv_url'])&&trim($_POST['csv_count']))
				{
					$map['csv_count'] = $_POST['csv_count'];
					$map['csv_url'] = $_POST['csv_url'];
					$map['is_upload_csv'] = 1;
				}
				unset($_FILES['upload_file']);
				//渠道id和机型id
				$channel_id_array=$_POST['cid'];
				$cids = array_unique($channel_id_array);
				if (count($cids) > 0) {
					$s = implode(',', $cids);
					$s = ",{$s},";
					$map['cid'] = $s;
				}
				else
				{
					$map['cid'] = ",,";
				}
				$device_did_array=$_POST['did'];
				$dids = array_unique($device_did_array);
				if (count($dids) > 0) 
				{
					$d= implode(',', $dids);
					$d = ",{$d},";
					$map['device_did'] = $d;
				}
				else
				{
					$map['device_did'] = ",,";
				}
				//运营商和固件版本和市场版本
				$map['oid'] = ','. implode(',', $_POST['oid']). ',';
				$map['firmware'] = ','. implode(',', $_POST['firmware']). ',';
				$map['version_code'] = ','. implode(',', $_POST['version_code']). ',';
                
                // 开始时间和结束时间
                $start_at = strtotime($_POST['start_at']);
                $end_at = strtotime($_POST['end_at']);
                if (!$start_at) {
                    $this->error("开始时间不能为空");
                }
                if (!$end_at) {
                    $this->error("结束时间不能为空");
                }
                if ($start_at > $end_at) {
                    $this->error("开始时间不能大于结束时间");
                }
				//已过期的信息复制上线判断
				if($_POST['life']==1)
				{
				  if($end_at<time())
				  {
					$this->error("您修改的复制上线的日期还是无效日期");
				  }
				}
                $map['start_at'] = $start_at;
                $map['end_at'] = $end_at;
				include_once SERVER_ROOT. '/tools/functions.php';
				$folder = "/img/" . date("Ym/d/");
				$this->mkDirs(UPLOAD_PATH . $folder);
                if ($_FILES['image_url']['name']) {
                    // 将图片存储起来
                    $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
					$relative_path_40 = $folder . time() . '_40_' . rand(1000,9999) . $suffix;
                    $img_path = UPLOAD_PATH . $relative_path;
					$img_path_40 = UPLOAD_PATH . $relative_path_40;
                    $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
                    $map['image_url'] = $relative_path;
					$low_40=image_strip_size($img_path,$img_path_40,40*1024);
					if($low_40)
					{
						$map['low_image_url_40'] = $relative_path_40;
					}
                }
				if ($_FILES['image_url_high']['name']&&($image_type==2||$image_type==3)) 
				{
                    // 将图片存储起来
                    $folder = "/img/" . date("Ym/d/");
                    $this->mkDirs(UPLOAD_PATH . $folder);
                    $relative_path_high = $folder . time() .'_high' . '_' . rand(1000,9999) . $suffix_high;
					$relative_path_high_80 = $folder . time() . '_high_80' . '_' . rand(1000,9999) . $suffix_high;
                    $img_path_high = UPLOAD_PATH . $relative_path_high;
					$img_path_high_80 = UPLOAD_PATH . $relative_path_high_80;
                    $ret_high = move_uploaded_file($_FILES['image_url_high']['tmp_name'], $img_path_high);
                    $map['high_image_url'] = $relative_path_high;

					$high_80=image_strip_size($img_path_high,$img_path_high_80,80*1024);
					if($high_80)
					{
						$map['high_image_url_80'] = $relative_path_high_80;
					}
                }
				if($_FILES['icon_url']['name']){
					$icon_path =  $folder . time() . '_' . rand(1000,9999) . $suffix_icon;
					$i_path = UPLOAD_PATH . $icon_path;
					$ret = move_uploaded_file($_FILES['icon_url']['tmp_name'], $i_path);
					$map['image_url'] = $icon_path;
				}
                // 创建时间和更新时间
                $map['update_at'] = time();
				$map['admin_id'] = $_SESSION['admin']['admin_id'];
                // 检查冲突
				$map['life']=$_POST['life'];
				
				//关闭类型和点击类型  added by shitingting
				$map['closed_type']=$_POST['close'];
				$map['click_type']=$_POST['click'];
				//V6.4居中弹窗广告 增加关闭倒计时
				$map['closed_count_type']=$_POST['closed_count_type'];
				if($map['closed_count_type']==1) //设置倒计时时间
				{
					if(!$_POST['closed_time'])
					{
						 $this->error("选择关闭倒计时，时间必填");
					}
					else
					{
						$matches = "/^[1-9][0-9]*$/";
						if (!preg_match($matches,trim($_POST['closed_time']))) 
						{
							$this -> error("倒计时时间为正整数");
						}
						else
						{
							$map['closed_time']=trim($_POST['closed_time']);
						}
					}
				}
				
				//V5.5允许同一类型广告可配置多个
                $conflict_id = $this->check_conflict($map, $id);
                if ($conflict_id) {
                    $this->error("与后台id为{$conflict_id}的记录投放时间的页面位置有冲突");
                }
                if($find['package']){
                	//屏蔽软件上排期时报警需求 新增  yuesai
	                $AdSearch = D("Sj.AdSearch");
	                $shield_error=$AdSearch->check_shield($find['package'],$map['start_at'],$map['end_at']);
	                if($shield_error){
	                    $this -> error($shield_error);
	                }
                }
                // 编辑


                $parameter_field=json_decode($map['parameter_field'],true);
                $parameter_field['cover_user_type']=trim($_POST['cover_user_type'])?trim($_POST['cover_user_type']):0;
                if($parameter_field['cover_user_type']==1){
                	$parameter_field['activation_date_start']=0;
                	$parameter_field['activation_date_end']=0;
                }elseif($parameter_field['cover_user_type']==2){
                	if(!$_POST['activation_date_start'] || !$_POST['activation_date_end']){
                		$this->error("选择定向用户时，激活日期必填！");
                	}else{
                		if(strtotime(trim($_POST['activation_date_start']))>=strtotime(trim($_POST['activation_date_end']))){
                			$this->error("激活结束日期必须大于开始日期！");
                		}
                	}
                	$parameter_field['activation_date_start']=strtotime(trim($_POST['activation_date_start']));
                	$parameter_field['activation_date_end']=strtotime(trim($_POST['activation_date_end']));
                }else{
                	$parameter_field['activation_date_start']=0;
                	$parameter_field['activation_date_end']=0;
                }

                $map['parameter_field']=json_encode($parameter_field);



                $where = array('id' => $id);
                $log = $this->logcheck($where, 'sj_animation_ad', $map, $model);
				
				//添加产品pid
				$map['pid'] = $_POST['pid'] ? $_POST['pid'] : 1;
				
				//已过期的信息复制上线添加
				if($_POST['life']==1)
				{
				    $select = $model->table('sj_animation_ad')->where($where)->select();
					if ($_FILES['image_url']['name']=="")
					{
						$map['image_url']= $select[0]['image_url'];
						$map['low_image_url_40']=$select[0]['low_image_url_40'];
					}
					if($_FILES['image_url_high']['name']=="")
					{
						$map['high_image_url']=$select[0]['high_image_url']; 
						$map['high_image_url_80']=$select[0]['high_image_url_80']; 
					}
					$map['create_at']= time();
					$map['image_type']=$select[0]['image_type'];
					unset($map['life']);
					$ret = $model->table('sj_animation_ad')->add($map);
					if ($ret) {
						$this->writelog("灵活运营样式-动画广告：复制上线了ad_name为{$data['ad_name']}的内容，{$log}", 'sj_animation_ad', $ret,__ACTION__ ,"","add");
						$this->success("复制上线成功！");
					} else {
						$this->error("复制上线失败！");
					}
				}
				else
				{
				    unset($map['life']);
					unset($map['image_type']);
				    $ret = $model->table('sj_animation_ad')->where($where)->save($map);
					if ($ret) {
						$this->writelog("灵活运营样式-动画广告：编辑了id为{$id}的内容，{$log}",'sj_animation_ad', $id,__ACTION__ ,"","edit");
						$this->success("编辑成功！");
					} else {
						$this->error("编辑失败！");
					}
				}
               
            } else {
                $model = M();
                $id = $_GET['id'];
                $find = $model->table('sj_animation_ad')->where("id = $id")->find();
                // 给个默认值，要不然javascript那边可能会出错
                $find['general_page_type'] = 0;
                $find['page_name'] = "";

                $parameter_field=json_decode($find['parameter_field'],true);

                $find['cover_user_type']=$parameter_field['cover_user_type'];
                $find['activation_date_end']=$parameter_field['activation_date_end'];
                $find['activation_date_start']=$parameter_field['activation_date_start'];

                // 获得活动名称，专题名称，页面名称
                $content_type = $find['content_type'];
                if ($content_type == 2) {
                    // 活动名称
                    $find['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($find['activity_id']);
                } else if ($content_type == 3) {
                    // 专题名称
                    $find['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($find['feature_id']);
                } else if ($content_type == 4) {
                    // 页面类型
                    $find['general_page_type'] = ContentTypeModel::getGeneralPageType($find['page_type']);
                    // 页面名称
                    $find['page_name'] = ContentTypeModel::convertPageType2PageName($find['page_type']);
                }
                
				//渠道
				$channel_model = M('channel');
				$cookstr = preg_replace('/^,/','',$find['cid']);
				$cookstr = preg_replace('/,$/','',$cookstr);
				$array = explode(',', $cookstr);
				$chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
				if (in_array("0",$array)&&$chl!=NULL)
				{
				  $tong = array("cid"=> "0" ,"chname"=> "通用");
				  array_unshift($chl, $tong);
				}
				if (in_array("0",$array)&&$chl==NULL)
				{
				  $chl[0]['cid']="0";
				  $chl[0]['chname']="通用";
				}
				//机型
				if (strlen($find['device_did']) > 0)
				{
					$device_selected = explode(',', $find['device_did']);
					$device_selected_ret = array();
					foreach ($device_selected as $ds) 
					{
						if (empty($ds)) continue;
						$device_name = $model->table("pu_device")->where(array('did' => $ds))->field('did,dname')->select();
						$device_selected_ret[] = array('did' => $ds,'dname' => $device_name[0]['dname']);
					}
					$this->assign('device_selected', $device_selected_ret);
				}
				//固件、市场版本、运营商
				$util = D('Sj.Util');
				$this->assign('firmwarelist', $util->getFirmwareList(explode(',', $find['firmware'])));
				$this->assign('version_list', $util->getMarketVersion(explode(',', $find['version_code'])));
				$this->assign('operator_list', $util->getOperators(explode(',', $find['oid'])));
				//合作形式
				$util = D("Sj.Util");
				$typelist = $util->getHomeExtentSoftTypeList($find['co_type']);
				$this->assign('typelist',$typelist);
				
				//合作站点和资讯
				if($find['page_place_param'])
				{
					$have_page_place_arr=json_decode($find['page_place_param'],true);
					$this->assign('have_page_place_arr',$have_page_place_arr);
				}
				
				//6.3除去安智市场的站点
				$coop_where=array(
					'status'=>1,
					'type' => array('in','1,2'),
					//'id'=>array('neq',1),
				);
				$coop_site = $model->table('coop_site')->where($coop_where)->select();
				$this->assign('coop_site',$coop_site);
				//6.3安智市场下的频道（资讯站点） 合作站点type=0 安智市场频道，3 应用下面频道 4游戏下面频道
				$site_where=array(
					'status'=>1,
					'type' => 0,
				);
				$az_site = $model->table('coop_site')->where($site_where)->find();
				
				$channel_where = array(
					'site_id' => $az_site['id'],
					'status' => 1,
					'type'=>array('in','1,3'), //显示普通和内容合作
				);
				$channel_list = $model->table('coop_channel')->where($channel_where)->select();
				$this->assign('channel_list',$channel_list);
				
				//6.4.1 应用下面的频道
				$site_app_where=array(
					'status'=>1,
					'type' => 3,
				);
				$az_site_app = $model->table('coop_site')->where($site_app_where)->find();
				$channel_app_where = array(
					'site_id' => $az_site_app['id'],
					'status' => 1,
					'type'=>array('in','1,3'), //显示普通和内容合作
				);
				$channel_app_list = $model->table('coop_channel')->where($channel_app_where)->select();
				$this->assign('channel_app_list',$channel_app_list);
				//6.4.1 游戏下面的频道
				$site_game_where=array(
					'status'=>1,
					'type' => 4,
				);
				$az_site_game = $model->table('coop_site')->where($site_game_where)->find();
				
				$channel_game_where = array(
					'site_id' => $az_site_game['id'],
					'status' => 1,
					'type'=>array('in','1,3'), //显示普通和内容合作
				);
				$channel_game_list = $model->table('coop_channel')->where($channel_game_where)->select();
				$this->assign('channel_game_list',$channel_game_list);
				
				
				$this->assign('chl_list', $chl);
                $this->assign("list", $find);
                $this->assign('image_width', $this->image_width);
                $this->assign('image_height', $this->image_height);
				$this->assign('image_width_645', $this->image_width_645);
				$this->assign('image_height_645', $this->image_height_645);
                $this->assign('popup_image_width', $this->popup_image_width);
                $this->assign('popup_image_height', $this->popup_image_height);
				$this->assign('popup_image_width_high', $this->popup_image_width_high);
                $this->assign('popup_image_height_high', $this->popup_image_height_high);
				//V6.4新增居中弹窗广告
				$this->assign('center_image_width', $this->center_image_width);
                $this->assign('center_image_height', $this->center_image_height);
				$this->assign('center_image_width_high', $this->center_image_width_high);
                $this->assign('center_image_height_high', $this->center_image_height_high);
				$this->assign('icon_width',$this->icon_width);
				$this->assign('icon_height',$this->icon_height);
                $this->display();
            }
        }

		function delete_content() {
			$model = M();
			$id = $_GET['id'];
			$data['status'] = 0;
			$data['update_at'] = time();
			$del = $model->table('sj_animation_ad')->where("id = {$id}")->save($data);
			if($del){
                $this->writelog("灵活运营样式-动画广告：删除了id为{$id}的内容",'sj_animation_ad', $id,__ACTION__ ,"","del");
				$this -> success("删除成功");
			}else{
				$this->error("删除失败");
			}
		}
        
        private function clear_content(&$map) {
            $map['package'] = '';
            $map['activity_id'] = 0;
            $map['feature_id'] = 0;
            $map['page_type'] = '';
            $map['website'] = '';
            $map['page_flag'] = '';
            $map['page_id1'] = 0;
            $map['page_id2'] = 0;
        }
        
        // 返回冲突id，否则返回0（不管什么内容，相同页面同一类型的广告一个排期可以添加多个）
        function check_conflict($record, $id = 0) {
            $content_type = $record['content_type'];
			 //广告类型
			/*if($record['image_type']==1)
			{
			  $image_type=2; 
			}
			if($record['image_type']==2)
			{
			  $image_type=1; 
			}*/
            $start_at = $record['start_at'];
            $end_at = $record['end_at'];
            $oid = $record['oid'];
            $cid = $record['cid'];
            $show_place = $record['show_place'];
			$life=$record['life'];
            $model = M();
             // 同一类型的广告不能重复  不管内容了
            if ($content_type == 1) {
                // 查找包名
                $content_key = 'package';
                $content_value = $record['package'];
            } else if ($content_type == 2) {
                // 查找活动
                $content_key = 'activity_id';
                $content_value = $record['activity_id'];
            } else if ($content_type == 3) {
                // 查找专题
                $content_key = 'feature_id';
                $content_value = $record['feature_id'];
            } else if ($content_type == 4) {
                // 查找专题
                $content_key = 'page_type';
                $content_value = $record['page_type'];
				$parameter_field = $record['parameter_field'];
            } else if ($content_type == 5) {
                // 查找网页
                $content_key = 'website';
                $content_value = $record['website'];
            } else {
                return false;
            }
            //同一位置不能同时有动画广告和弹框广告
            $where = array(
                //"{$content_key}" => $content_value,
				//'image_type'=>$image_type,//广告类型
				'image_type'=>array('neq',$record['image_type']),//广告类型
                //'oid' => $oid,
                // 'cid' => $cid,
                'status' => 1,
                'start_at' => array('elt', $end_at),
                'end_at' => array('egt', $start_at),
                'show_place' => array('exp', "&{$show_place}!=0"),//不能在同一个页面
            );
            if ($id&&$life!=1) {
                $where['id'] = array('neq', $id);
            }
			//同一位置同一类型的广告不能重复
			if($parameter_field)
			{
				$where_same = array(
					"{$content_key}" => $content_value,
					'image_type'=>$record['image_type'],//广告类型
					'oid' => $oid,
					'cid' => $cid,
					'status' => 1,
					'start_at' => array('elt', $end_at),
					'end_at' => array('egt', $start_at),
					'show_place' => array('exp', "&{$show_place}!=0"),//不能在同一个页面
					'parameter_field' =>$parameter_field,
				);
			}
			else
			{
				$where_same = array(
					"{$content_key}" => $content_value,
					'image_type'=>$record['image_type'],//广告类型
					'oid' => $oid,
					'cid' => $cid,
					'status' => 1,
					'start_at' => array('elt', $end_at),
					'end_at' => array('egt', $start_at),
					'show_place' => array('exp', "&{$show_place}!=0"),//不能在同一个页面
				);
			}
			 
            if ($id&&$life!=1) {
                $where_same['id'] = array('neq', $id);
            }
			
            $find = $model->table('sj_animation_ad')->where($where)->find();
			$find_same = $model->table('sj_animation_ad')->where($where_same)->find();
            if ($find['id'])
                return $find['id'];
			if($find_same['id'])
			   return $find_same['id'];
			return 0;
        }
        
        function get_show_place_name($show_place) {
            $show_place_arr = array();
            if ($show_place & 4)
                $show_place_arr[] = '首页推荐';
            if ($show_place & 1)
                $show_place_arr[] = '详情页';
            if ($show_place & 2)
                $show_place_arr[] = '可更新页';
            if ($show_place & 8)
                $show_place_arr[] = '飙升';
            if ($show_place & 16)
                $show_place_arr[] = '首页必备';
            /*
            if ($show_place & 32)
                $show_place_arr[] = '首页尝鲜';
            */
            if ($show_place & 64)
                $show_place_arr[] = '应用-最热';
            if ($show_place & 128)
                $show_place_arr[] = '应用分类Tab';
            if ($show_place & 256)
                $show_place_arr[] = '应用日排行';
            if ($show_place & 512)
                $show_place_arr[] = '游戏-最热';
            if ($show_place & 1024)
                $show_place_arr[] = '网游';
            if ($show_place & 2048)
                $show_place_arr[] = '单机';
            if ($show_place & 4096)
                $show_place_arr[] = '游戏分类Tab';
            if ($show_place & 8192)
                $show_place_arr[] = '管理';
			if ($show_place & 65536)
                $show_place_arr[] = '发现-推荐';
			if ($show_place & 131072)
                $show_place_arr[] = '发现-汉化';
			if ($show_place & 262144)
                $show_place_arr[] = '发现-专题';
			if ($show_place & 524288)
                $show_place_arr[] = '6.0应用排行';
			if ($show_place & 2097152)
                $show_place_arr[] = '6.3资讯站点';
			if ($show_place & 4194304)
                $show_place_arr[] = '6.3合作站点';
			if ($show_place & 8388608)
                $show_place_arr[] = '6.4.1应用';
			if ($show_place & 16777216)
                $show_place_arr[] = '6.4.1游戏';
            $show_place_name = '';
            foreach ($show_place_arr as $name) {
                if ($show_place_name != '') {
                    $show_place_name .= '，';
                }
                $show_place_name .= $name;
            }
            return $show_place_name;
        }
        
        function shorten_sentence($sentence, $len = 10) {
            $sen_len = mb_strlen($sentence, 'utf-8');
            if ($sen_len > $len) {
                $sentence = mb_substr($sentence, 0, $len - 2, 'utf-8') . ' ...';
            }
            return $sentence;
        }
		function jztf_content_show()
		{
			$model=M();
			$channel_model = M('channel');
			$list=array();
			$where['id']=$_GET['id'];
			$where['status']=1;
			$result=$model->table('sj_animation_ad')->where($where)->find();
			
			//渠道	
			$cid_str = preg_replace('/^,/','',$result['cid']);
			$cid_str = preg_replace('/,$/','',$cid_str);
			$array = explode(',', $cid_str);
			$cname = $channel_model->where("cid in ({$cid_str})")->findAll();
			if($cid_str=="")
			{
				$list['cname'] = "<p>不限</p>";
			}
			else
			{
				if (in_array("0",$array))
				{
					$list['cname'] .= "<p>通用</p>";
				}
				foreach ($cname as $k1 => $v1) 
				{
					$list['cname'] .= "<p>{$v1['chname']}</p>";
				}
			}
			//机型
			$did_str = preg_replace('/^,/','',$result['device_did']);
			$did_str = preg_replace('/,$/','',$did_str);
			$dname = $channel_model->table("pu_device")->where("did in ({$did_str})")->findAll();
			
			if($did_str=="")
			{
				$list['dname'] .= "<p>不限</p>";		
			}
			else
			{
				foreach ($dname as $k2 => $v2) 
				{
						
					$list['dname'] .= "<p>{$v2['dname']}</p>";				
				}
			}
			//覆盖人数
			if($result['csv_count']==0)
			{
				$list['cover_num']="不限";
			}
			else
			{
				$list['cover_num']=$result['csv_count'];
			}
			//运营商
			$operating_db = D('Sj.Operating');
			$oid_str = preg_replace('/^,/','',$result['oid']);
			$oid_str = preg_replace('/,$/','',$oid_str);
			$oid_array = explode(',', $oid_str);
			$oname = $operating_db->where("oid in ({$oid_str})")->findAll();
			if($oid_str=="")
			{
				$list['oname'] = "不限";
			}
			else
			{
				if(in_array("-1",$oid_array))
				{
					$list['oname'] .="未插卡";
				}
				foreach ($oname as $k3 => $v3) 
				{
					$list['oname'] .= "<p>{$v3['mname']}</p>";
				}
			}
			//固件版本
			$firmware_str = preg_replace('/^,/','',$result['firmware_str']);
			$firmware_str = preg_replace('/,$/','',$firmware_str);
			$firmware_array = explode(',', $firmware_str);
			
			if($firmware_str==""||$result['firmware']=="")
			{
				$list['firmwares']="不限";
			}
			else
			{
				$firmwares = $channel_model->table('pu_config')->field('configname,configcontent')->where("config_type='firmware' and status=1 and configname in ({$firmware_str})")->select();

				foreach ($firmwares as $k4 => $v4) 
				{	
					$list['firmwares'] .= "<p>{$v4['configcontent']}</p>";				
				}
			}
			//市场版本
			$util = D('Sj.Util');
			$version_str = preg_replace('/^,/','',$result['version_code']);
			$version_str = preg_replace('/,$/','',$version_str);
			$version_array = explode(',', $version_str);
			if($version_str==""||$result['version_code']=="")
			{
				$list['version_name']="不限";
			}
			else
			{
				$version_list=$util->getMarketVersion($version_array);
				foreach ($version_list as $k5 => $v5) 
				{	
					if($v5[1]==true)
					{
						$list['version_name'] .= "<p>{$v5[0]}</p>";
					}
				}
			}
			$this->assign("list",$list);
			$this->display();
		}
	}
?>
