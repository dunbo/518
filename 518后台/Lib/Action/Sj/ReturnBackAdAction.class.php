<?php

class ReturnBackAdAction extends CommonAction {
    private $image_width = 383;
    private $image_height = 180;
	private $image_width_high = 466;
    private $image_height_high = 200;
	private $image_width_low = 350;
    private $image_height_low = 150;
	private $des_limit= 16;

    public function index() {
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
            $order = "priority asc,start_at";
        }
        $where['status'] = 1;
        //添加产品pid
        $pid = $_GET['pid'] ? $_GET['pid'] : 1;
        if($pid) $where['pid'] = $pid;
        // 分页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_return_back_ad')->where($where)->count();
        $page  = new Page($count, $limit);
        // 当前页数据
        $list = $model->table('sj_return_back_ad')->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
		//合作形式
		$util = D("Sj.Util"); 
        // 处理list
        foreach ($list as $key => $value) {
			if($value['frequency']==0)
			{
				$list[$key]['freq']="不限";
			}
			if($value['frequency']==1)
			{
				$list[$key]['freq']="只展示一次";
			}
			if($value['frequency']==2)
			{
				$list[$key]['freq']="只展示两次";
			}
			if($value['frequency']==3)
			{
				$list[$key]['freq']="只展示三次";
			}
			if($value['csv_count']==0)
			{
				$list[$key]['csv_counts']="全部";
			}
			else
			{
				$list[$key]['csv_counts']=$value['csv_count'];
			}
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
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($value['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$list[$key]['co_types'] = $v[0];
				}
			}
			//合作站点
			if($value['site_type'])
			{
				$site_name = $util->getCoopSiteName($value['site_type']);
				$list[$key]['site_names'] = $site_name;
			}
			else
			{
				$list[$key]['site_names'] = "安智市场";
			}
			
        }
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
        $this->assign('overdue', $overdue);
        $this->assign("page", $page->show());
        $this->display();
    }
    
	
    public function add_content() 
	{
        if ($_POST) 
		{
            $model = M();
            $map = array();
			
			//V6.0精准投放
			//处理上传csv
			$filename=$_FILES['upload_file']['name'];
			if(!$filename&&!$_POST['csv_count'])
			{
				$map['csv_count'] = 0;
				$map['csv_url'] = "";
				$map['is_upload_csv'] = 0; //标注是否上传csv
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
			
			
			$priority =$_POST['priority'];
			$map['frequency']=$_POST['frequency'];
			if($priority)
			{
				$_march="/^([1-9][0-9]?|100)$/";		
				if(!preg_match($_march,$priority)) 
				{  
					$this->error('优先级必须是1-100的整数，请重新输入');  
				} 
				$map['priority'] = $priority;
			}
			else
			{
				$this->error("优先级不能为空");
			}
            $ad_name = $_POST['ad_name'];
            if (!$ad_name) {
                $this->error("广告名称不能为空");
            }
            $map['ad_name'] = $ad_name;
			//合作形式
			if(isset($_POST['co_type'])){			
				$map['co_type'] = $_POST['co_type'];		
			}else{		
				$map['co_type'] = 0;	
			}
			//合作站点
			if(isset($_POST['site_type'])){			
				$map['site_type'] = $_POST['site_type'];		
			}else{		
				$map['site_type'] = 0;	
			}
            // 图片不能为空
            if (!$_FILES['image_url']['name'])
                $this->error("请上传图片");
			if (!$_FILES['image_url_high']['name'])
                $this->error("请上传高分图片");
			if (!$_FILES['image_url_low']['name'])
                $this->error("请上传低分图片");
            // 取得图片后缀
            $suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url']['name'],$matches);
			$suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_url_high']['name'],$matches_high);
			$suffix_low = preg_match("/\.(jpg|png)$/", $_FILES['image_url_low']['name'],$matches_low);
            if ($matches) {
                $suffix = $matches[0];
            } else {
                $this->error('上传图片格式错误！');
            }
			if ($matches_high) {
                $suffix_high = $matches_high[0];
            } else {
                $this->error('上传图片格式错误！');
            }
			if ($matches_low) {
                $suffix_low = $matches_low[0];
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
            if ($width != $this->image_width || $height != $this->image_height)
                $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
			
			$img_info_arr_high = getimagesize($_FILES['image_url_high']['tmp_name']);
            if (!$img_info_arr_high) {
                $this->error('上传高分图片出错！');
            }
            $width_high = $img_info_arr_high[0];
            $height_high = $img_info_arr_high[1];
            if ($width_high != $this->image_width_high || $height_high != $this->image_height_high)
                $this->error("上传高分图片大小错误，宽需为{$this->image_width_high}px，高需为{$this->image_height_high}px");
			
			$img_info_arr_low = getimagesize($_FILES['image_url_low']['tmp_name']);
            if (!$img_info_arr_low) {
                $this->error('上传低分图片出错！');
            }
            $width_low = $img_info_arr_low[0];
            $height_low = $img_info_arr_low[1];
            if ($width_low != $this->image_width_low || $height_low != $this->image_height_low)
                $this->error("上传低分图片大小错误，宽需为{$this->image_width_low}px，高需为{$this->image_height_low}px");
			
            $description = $_POST['description'] ? $_POST['description'] : '';
            if ($description) {
                if (mb_strlen($description, 'utf-8') > $this->des_limit)
                    $this->error("描述不能超出{$this->des_limit}个字！");
            }
            $map['description'] = $description;
            $button_name = $_POST['button_name'];
            if (!$button_name) {
                $this->error("按钮名称不能为空");
            }
            if (mb_strlen($button_name, 'utf-8') > 6)
                $this->error('按钮名称不能超出6个字！');
            $map['button_name'] = $button_name;
            //推荐内容处理 合并
            $rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
			if($rcontent !==true)
			{
				$this -> error($rcontent);
			}
			
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
			
			//同一时间内可以配置多条广告
            $conflict_id = $this->check_conflict($map);
            if ($conflict_id) {
                $this->error("与后台id为{$conflict_id}的记录投放时间有冲突");
            }
			
            // 将图片存储起来
            $folder = "/img/" . date("Ym/d/");
            $this->mkDirs(UPLOAD_PATH . $folder);
            $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
			$relative_path_high = $folder . time() .'_high'. '_' . rand(1000,9999) . $suffix_high;
			$relative_path_low = $folder . time() .'_low'. '_' . rand(1000,9999) . $suffix_low;
			$relative_path_high_80 = $folder . time() .'_high_80'. '_' . rand(1000,9999) . $suffix_high;
			$relative_path_low_40 = $folder . time() .'_low_40'. '_' . rand(1000,9999) . $suffix_low;
            $img_path = UPLOAD_PATH . $relative_path;
			$img_path_high = UPLOAD_PATH . $relative_path_high;
			$img_path_low = UPLOAD_PATH . $relative_path_low;
			$img_path_high_80 = UPLOAD_PATH . $relative_path_high_80;
			$img_path_low_40 = UPLOAD_PATH . $relative_path_low_40;
            $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
			$ret_high = move_uploaded_file($_FILES['image_url_high']['tmp_name'], $img_path_high);
			$ret_low = move_uploaded_file($_FILES['image_url_low']['tmp_name'], $img_path_low);
			include_once SERVER_ROOT. '/tools/functions.php';
			$high_80=image_strip_size($img_path_high,$img_path_high_80,80*1024);
			if($high_80)
			{
				$map['high_image_url_80'] = $relative_path_high_80;
			}
			$low_40=image_strip_size($img_path_low,$img_path_low_40,40*1024);
			if($low_40)
			{
				$map['low_image_url_40'] = $relative_path_low_40;
			}
            $map['image_url'] = $relative_path;
			$map['high_image_url'] = $relative_path_high;
			$map['low_image_url'] = $relative_path_low;
			
            // 创建时间和更新时间
            $map['create_at'] = $map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];

            $map['pid'] = $_POST['pid'] ? $_POST['pid'] : 1;//添加产品pid

            if($_POST['package']){
            	//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($_POST['package'],$map['start_at'],$map['end_at']);
				if($shield_error){
				    $this -> error($shield_error);
				}
            }
            $ret = $model->table('sj_return_back_ad')->add($map);
            if ($ret) {
                $this->writelog("灵活运营样式-返回运营：添加了id为{$ret}的内容",'sj_return_back_ad', $ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        } else {
            $this->assign('image_width', $this->image_width);
            $this->assign('image_height', $this->image_height);
			$this->assign('image_width_high', $this->image_width_high);
            $this->assign('image_height_high', $this->image_height_high);
			$this->assign('image_width_low', $this->image_width_low);
            $this->assign('image_height_low', $this->image_height_low);
			$this->assign('des_limit', $this->des_limit);
			//合作形式
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			//合作站点
			// $coop_site_list = $util->getCoopSiteList();
			// unset($coop_site_list[0]);
			// $this->assign('coop_site_list',$coop_site_list);
			
			//添加产品pid
			$pid = $_GET['pid'] ? $_GET['pid'] : 1;
			$this->assign('pid',$pid);
            $this->display();
        }
    }
    
    function edit_content() {
        if ($_POST) {
            $model = M();
            $id = $_POST['id'];
            $map = array();
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
			
			$priority =$_POST['priority'];
			$map['frequency']= $_POST['frequency'];
			if($priority)
			{
				$_march="/^([1-9][0-9]?|100)$/";		
				if(!preg_match($_march,$priority)) 
				{  
					$this->error('优先级必须是1-100的整数，请重新输入');  
				} 
				$map['priority']=$_POST['priority'];
			}
			else
			{
				$this->error("优先级不能为空");
			}
            $ad_name = $_POST['ad_name'];
            if (!$ad_name) {
                $this->error("广告名称不能为空");
            }
            $map['ad_name'] = $ad_name;
			//合作形式
			if(isset($_POST['co_type'])){
				$map['co_type'] = $_POST['co_type'];
			}else{
				$map['co_type'] = 0;
			}
			
			//合作站点
			if(isset($_POST['site_type'])){
				$map['site_type'] = $_POST['site_type'];
			}else{
				$map['site_type'] = 0;
			}

			$have_result=$model->table('sj_return_back_ad')->where(array('id'=>$id))->find();
			
			//之前正确的图片尺寸再次判断
			$image_old_url=IMGATT_HOST.$have_result['image_url'];
			$high_image_url_edit= IMGATT_HOST.$have_result['high_image_url'];
			$low_image_url_edit=IMGATT_HOST.$have_result['low_image_url'];
			
			list($width_old_edit, $height_old_edit, $type_old_edit, $attr_old_edit)=getimagesize($image_old_url);
			list($width_high_edit, $height_high_edit, $type_high_edit, $attr_high_edit)=getimagesize($high_image_url_edit);
			list($width_low_edit, $height_low_edit, $type_low_edit, $attr_low_edit)=getimagesize($low_image_url_edit);
			
            // 判断图片的大小格式
            if ($_FILES['image_url']['name']) {
                // 取得图片后缀
                $suffix = preg_match("/\.(jpg|png)$/", $_FILES['image_url']['name'],$matches);
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
                if ($width != $this->image_width || $height != $this->image_height)
                    $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
            }
			else
			{
				if(!$have_result['image_url'])
				{
					$this->error("请上传图片");
				}
				else
				{
					if($width_old_edit!==$this->image_width||$height_old_edit!==$this->image_height)
					{
						 $this->error("上传图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_height}px");
					}
				}
			}
			// 判断高分图片的大小格式
            if ($_FILES['image_url_high']['name']) {
                // 取得图片后缀
                $suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_url_high']['name'],$matches_high);
                if ($matches_high) {
                    $suffix_high = $matches_high[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr_high = getimagesize($_FILES['image_url_high']['tmp_name']);
                if (!$img_info_arr_high) {
                    $this->error('上传图片出错！');
                }
                $width_high = $img_info_arr_high[0];
                $height_high = $img_info_arr_high[1];
                if ($width_high != $this->image_width_high || $height_high != $this->image_height_high)
                    $this->error("上传高分图片大小错误，宽需为{$this->image_width_high}px，高需为{$this->image_height_high}px");
            }
			else
			{
				if(!$have_result['high_image_url'])
				{
					$this->error("请上传高分图片");
				}
				else
				{
					if($width_high_edit!==$this->image_width_high||$height_high_edit!==$this->image_height_high)
					{
						$this->error("上传高分图片大小错误，宽需为{$this->image_width_high}px，高需为{$this->image_height_high}px");
					}
				}
			}
			// 判断低分图片的大小格式
            if ($_FILES['image_url_low']['name']) {
                // 取得图片后缀
                $suffix_low = preg_match("/\.(jpg|png)$/", $_FILES['image_url_low']['name'],$matches_low);
                if ($matches_low) {
                    $suffix_low = $matches_low[0];
                } else {
                    $this->error('上传低分图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr_low = getimagesize($_FILES['image_url_low']['tmp_name']);
                if (!$img_info_arr_low) {
                    $this->error('上传低分图片出错！');
                }
                $width_low = $img_info_arr_low[0];
                $height_low = $img_info_arr_low[1];
                if ($width_low != $this->image_width_low || $height_low != $this->image_height_low)
                    $this->error("上传低分图片大小错误，宽需为{$this->image_width_low}px，高需为{$this->image_height_low}px");
            }
			else
			{
				if(!$have_result['low_image_url'])
				{
					$this->error("请上传低分图片");
				}
				else
				{
					if($width_low_edit!==$this->image_width_low||$height_low_edit!==$this->image_height_low)
					{
						$this->error("上传图片大小错误，宽需为{$this->image_width_low}px，高需为{$this->image_height_low}px");
					}
				}
			}
            $description = $_POST['description'] ? $_POST['description'] : '';
            $map['description'] = $description;
            if ($description) {
                if (mb_strlen($description, 'utf-8') > $this->des_limit)
                    $this->error("描述不能超出{$this->des_limit}个字！");
            }
            $button_name = $_POST['button_name'];
            if (!$button_name) {
                $this->error("按钮名称不能为空");
            }
            if (mb_strlen($button_name, 'utf-8') > 6)
                $this->error('按钮名称不能超出6个字！');
            $map['button_name'] = $button_name;
            $content_type = $_POST['content_type'];
            if (!$content_type) {
                $this->error("内容类型不能为空");
            }
            // 先清除一下内容字段数据
            $this->clear_content($map);
			 //推荐内容处理 合并
            $rcontent=ContentTypeModel::saveRecommendContent($_POST,$content_type,$map);
			if($rcontent!==true)
			{
				$this -> error($rcontent);
			}
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
			$map['life'] =$_POST['life'];
			
			//同一时间可以配置多条广告
            $conflict_id = $this->check_conflict($map, $id);
            if ($conflict_id) {
                $this->error("与后台id为{$conflict_id}的记录投放时间有冲突");
            }
			include_once SERVER_ROOT. '/tools/functions.php';
            if ($suffix) {
                // 将图片存储起来
                $folder = "/img/" . date("Ym/d/");
                $this->mkDirs(UPLOAD_PATH . $folder);
                $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                $img_path = UPLOAD_PATH . $relative_path;
                $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
                $map['image_url'] = $relative_path;
            }
			if ($suffix_high) {
                // 将图片存储起来
                $folder = "/img/" . date("Ym/d/");
                $this->mkDirs(UPLOAD_PATH . $folder);
                $relative_path_high = $folder . time() .'_high'. '_' . rand(1000,9999) . $suffix_high;
				$relative_path_high_80 = $folder . time() .'_high_80'. '_' . rand(1000,9999) . $suffix_high;
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
			if ($suffix_low) {
                // 将图片存储起来
                $folder = "/img/" . date("Ym/d/");
                $this->mkDirs(UPLOAD_PATH . $folder);
                $relative_path_low = $folder . time() .'_low'. '_' . rand(1000,9999) . $suffix_low;
				$relative_path_low_40 = $folder . time() .'_low_40'. '_' . rand(1000,9999) . $suffix_low;
                $img_path_low = UPLOAD_PATH . $relative_path_low;
				$img_path_low_40 = UPLOAD_PATH . $relative_path_low_40;
                $ret_low = move_uploaded_file($_FILES['image_url_low']['tmp_name'], $img_path_low);
                $map['low_image_url'] = $relative_path_low;
				$low_40=image_strip_size($img_path_low,$img_path_low_40,40*1024);
				if($low_40)
				{
					$map['low_image_url_40'] = $relative_path_low_40;
				}
            }
            // 创建时间和更新时间
            $map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 添加
            $where = array('id'=>$id);
            if($have_result['package']){
            	//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($have_result['package'],$map['start_at'],$map['end_at']);
				if($shield_error){
				    $this -> error($shield_error);
				}
            }
            
            $log = $this->logcheck($where, 'sj_return_back_ad', $map, $model);

            //添加产品pid
			$map['pid'] = $_POST['pid'] ? $_POST['pid'] : 1;

			//已过期的信息复制上线 添加
			if($_POST['life']==1)
			{
			   $select = $model->table('sj_return_back_ad')->where($where)->select();
			   if ($_FILES['image_url']['name']=="")
			   {
					$map['image_url']=$select[0]['image_url'];
			   }
			   if($_FILES['image_url_high']['name']=="")
				{
					$map['high_image_url']=$select[0]['high_image_url']; 
					$map['high_image_url_80']=$select[0]['high_image_url_80']; 
				}
				if($_FILES['image_url_low']['name']=="")
				{
					$map['low_image_url']=$select[0]['low_image_url']; 
					$map['low_image_url_40']=$select[0]['low_image_url_40'];
				}
			   $map['create_at']=time();
			   unset($map['life']);
			   $ret = $model->table('sj_return_back_ad')->add($map);
				if ($ret) {
					$this->writelog("灵活运营样式-返回运营：复制上线了id为{$id}的内容，{$log}",'sj_return_back_ad', $id,__ACTION__ ,"","add");
					$this->success("复制上线成功");
				} else {
					$this->error("复制上线失败");
				}
			}
			else
			{
			    unset($map['life']);
				$ret = $model->table('sj_return_back_ad')->where($where)->save($map);
				if ($ret || $ret === 0) {
					$this->writelog("灵活运营样式-返回运营：编辑了id为{$id}的内容，{$log}",'sj_return_back_ad', $id,__ACTION__ ,"","edit");
					$this->success("编辑成功！");
				} else {
					$this->error("编辑失败！");
				}
			}
        } else if ($_GET) 
		{
			//渠道
			$model=M();
			$channel_model = M('channel');
			$where['id']=$_GET['id'];
			$where['status']=1;
			$sj_return_ad_one=$model->table('sj_return_back_ad')->where($where)->find();
			$cookstr = preg_replace('/^,/','',$sj_return_ad_one['cid']);
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
			if (strlen($sj_return_ad_one['device_did']) > 0)
			{
				$device_selected = explode(',', $sj_return_ad_one['device_did']);
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
			$this->assign('firmwarelist', $util->getFirmwareList(explode(',', $sj_return_ad_one['firmware'])));
			$this->assign('version_list', $util->getMarketVersion(explode(',', $sj_return_ad_one['version_code'])));
			$this->assign('operator_list', $util->getOperators(explode(',', $sj_return_ad_one['oid'])));
			//合作形式
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList($sj_return_ad_one['co_type']);
			$this->assign('typelist',$typelist);
			
			//合作站点
			// $coop_site_list = $util->getCoopSiteList();
			// unset($coop_site_list[0]);
			// $this->assign('coop_site_list',$coop_site_list);

			$this->assign('chl_list', $chl);
            $this->assign('list', $sj_return_ad_one);
            $this->assign('image_width', $this->image_width);
            $this->assign('image_height', $this->image_height);
			$this->assign('image_width_high', $this->image_width_high);
            $this->assign('image_height_high', $this->image_height_high);
			$this->assign('image_width_low', $this->image_width_low);
            $this->assign('image_height_low', $this->image_height_low);
			$this->assign('des_limit', $this->des_limit);
            $this->display();
        }
    }
    function jztf_return_ad_show()
	{
		$model=M();
		$channel_model = M('channel');
		$list=array();
		$where['id']=$_GET['id'];
		$where['status']=1;
		$result=$model->table('sj_return_back_ad')->where($where)->find();
		
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
		$firmware_str = preg_replace('/^,/','',$result['firmware']);
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
    function delete_content() {
        $model = M();
        $id = $_GET['id'];
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $map = array('status' => 0, 'update_at' => time());
        $ret = $model->table('sj_return_back_ad')->where($where)->save($map);
        if ($ret) {
            $this->writelog("灵活运营样式-返回运营：删除了id为{$id}的内容",'sj_return_back_ad', $id,__ACTION__ ,"","del");
            $this->success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }
    
    // 返回冲突id，否则返回0（不管什么内容，一个排期只能添加一个）
    function check_conflict($record, $except_id = 0) {
        $content_type = $record['content_type'];
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
        $oid = $record['oid'];
        $cid = $record['cid'];
		$life=$record['life'];
        $model = M();
        //不管内容了
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
            // 查找页面
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
        if($parameter_field)
		{
			$where = array(
				"{$content_key}" => $content_value,
				'oid' => $oid,
				'cid' => $cid,
				'status' => 1,
				'start_at' => array('elt', $end_at),
				'end_at' => array('egt', $start_at),
				'parameter_field'=>$parameter_field,
			);
		}
		else
		{
			$where = array(
				"{$content_key}" => $content_value,
				'oid' => $oid,
				'cid' => $cid,
				'status' => 1,
				'start_at' => array('elt', $end_at),
				'end_at' => array('egt', $start_at),
			);
		}
		if ($except_id&&$life!=1) 
		{
			$where['id'] = array('neq', $except_id);
		}
		
        $find = $model->table('sj_return_back_ad')->where($where)->find();
        if ($find['id'])
            return $find['id'];
        return 0;
    }
    
    private function clear_content(&$map) {
        $map['activity_id'] = 0;
        $map['feature_id'] = 0;
        $map['page_type'] = '';
        $map['website'] = '';
        $map['page_flag'] = '';
        $map['page_id1'] = 0;
        $map['page_id2'] = 0;
    }
	
	function change_priority(){
		$model = M('sj_return_back_ad');
		$id = $_GET['id'];
		$priority = $_GET['priority'];
		$data = array(
			'priority' => $priority,
			'update_at' => time()
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_return_back_ad',$data,$model);
		$result = $model -> table('sj_return_back_ad') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("灵活运营样式-返回运营：已编辑id为{$id}的优先级".$log_result,'sj_return_back_ad', $id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
}

?>