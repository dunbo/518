<?php
class FlexibleExtentAction extends CommonAction {
    // 图片配置
    private $image_width_long = 466;
    private $image_height_long = 140;
    private $image_width_short = 230;
    private $image_height_short = 133;
 
    private $image_width_long_multi = 466;
    private $image_height_long_multi = 112; 
	
    //v6.0高低分图片配置
	private $high_image_width_long = 684;
    private $high_image_height_long = 185;
	private $low_image_width_long = 444;
    private $low_image_height_long = 120;
	
	private $high_image_width_multi = 840;
    private $high_image_height_multi = 330;
	private $low_image_width_multi = 560;
    private $low_image_height_multi = 220;
	//V6.2图片底部文字描述
	private $pic_bottom_des = 20;
	//v6.3gif图片
	private $gif_width = 444;
	private $gif_height = 120;
	private $gif_width_62 = 684;
	private $gif_height_62 = 185;
	//V6.4.1图标尺寸
	private $image_width = 60;
	private $image_height = 60;

    // v6.4.3  凭类型获取图片尺寸，用于页面展示和逻辑验证
    private $image_size = array(
        2  => array( //单排
            'image_url' => array(466,140),
            'high_image_url' => array(684,185),
            'low_image_url' => array(444,120),
        ),
        22 => array(  //多软件(预约)背景图尺寸
            'image_url' => array(1080,438),
        ),
        24 => array(  //单软件(列表单图)图片尺寸
            'image_url' => array(464,274),
        ),
        25 => array(   //单软件(图)图片尺寸
            'image_url' => array(684,185),
        ),
        26 => array(   //单软件(3图)图片尺寸
            'image_url' => array(160,160),
            'high_image_url' => array(160,160),
            'low_image_url' => array(160,160),
        ),
		30 => array( //单排（含推荐人）
			'image_url' => array(684,185),
		),
		31 => array( //banner+软件
			'image_url' => array(480,227),
		),
        40 => array( //大图论坛内容
            'image_url' => array(480,227),
        )
    );

	private $search_type = array(
		1 => '指定搜索词',
		2 => '全部搜索词',
		3 => '全部游戏搜索词',
		4 => '全部应用搜索词'
	);
	private $special_package = 'cn.goapk.market';
    private $native_ad_page_type = 'native_ad'; //原生广告
    public function index() {
        // 准备好展示数据
        // 产品平台
        $util = D("Sj.Util");
        $product_list = $util->getProducts($pid);
        // 所有运营商
        $operating_db = D('Sj.Operating');
		$operating_list = $operating_db->field('oid,mname')->select();
		$operators_key = array();
		foreach($operating_list as $v) {
			$operators_key[$v['oid']] = $v['mname'];
		}
        $channel_model = M('channel');
        $model = M();
        $where = array();
        // 开始
        // 搜索平台
        $pid = $_GET['pid'] ? $_GET['pid'] : 1;
        $where['pid'] = $pid;
        // 搜索运营页面的大分类
        $general_page_type = $_GET['general_page_type'] ? $_GET['general_page_type'] : 0;
        if ($general_page_type&&$general_page_type!=8) {
            $where['belong_page_type'] = ContentTypeModel::getWhereConditionOfPageType($general_page_type);
        }
		if($general_page_type==8||($_GET['belong_page_type']&&strpos($_GET['belong_page_type'],'coop')===0))
		{
			//合作站点和合作频道 展示
			$coop_new_arr =  ContentTypeModel::getCoopChannel(array('type'=>3));
			$this->assign('coop_result',$coop_new_arr);
			
			if($_GET['coop_channel'])
			{
				$coop_channel = $_GET['coop_channel'];
			}
			else if($_GET['belong_page_type'])
			{
				$coop_channel = $_GET['belong_page_type'];
			}
			else
			{
				$coop_channel = $coop_new_arr[0]['coop_key_val'];
			}
			$this -> assign('coop_channel',$coop_channel);
		}

		$this->assign('general_page_type', $general_page_type);
        // 根据运营的页面(所属具体页面)编码搜索
        $belong_page_type = '';
        if ($_GET['belong_page_type']) {
            // 直接传类型
            $belong_page_type = $_GET['belong_page_type'];
            // 返回该类型的名称
            $page_name = ContentTypeModel::convertPageType2PageNameOfFlexible($belong_page_type,$pid);
        } else if ($_GET['page_name']) {
            // 如果是通过搜索标签页面名称
            $page_name = $_GET['page_name'];
            $belong_page_type = ContentTypeModel::convertPageName2PageTypeOfFlexible($page_name, $general_page_type);
            if (!$belong_page_type) {
                $this->error("请输入正确的页面名称");
            }
        }else if($_GET['coop_channel']||$coop_channel)
		{
			$belong_page_type =$_GET['coop_channel']?$_GET['coop_channel']:$coop_channel;
		}
        if ($page_name) {
            $this->assign('page_name', $page_name);
        }
        if ($belong_page_type) {
            // 重新计算类型类型
			if($general_page_type!=8)
			{
				$general_page_type = ContentTypeModel::getGeneralPageType($belong_page_type);
			}
			if($general_page_type == 9){
				$customlist_type = explode('_',$belong_page_type);
				$_GET['custom_channel'] = $customlist_type[1];
			}
            $where['belong_page_type'] = $belong_page_type; 
            $this->assign('general_page_type', $general_page_type);
            $this->assign('belong_page_type', $belong_page_type);
        }
		if($general_page_type==9){
            $custom_list_channel = ContentTypeModel::getCustomLIstChannel();
            if($_GET['custom_channel']){
                $custom_channel = $_GET['custom_channel'];
                $this -> assign('custom_channel',$custom_channel);

                $custom_arr = explode('_',$_GET['belong_page_type']);
                $custom_id = $custom_arr[2];
				if(!$_GET['belong_page_type']||!$custom_id){
                    $custom_list_name = ContentTypeModel::getCustomListCategory($custom_channel,1);
                    $sql_str = implode("','",$custom_list_name);
					$where['belong_page_type'] = array('exp'," in ('{$sql_str}')");
				}
            }else{
				//V6.4.8 隐藏外投频道，此频道为自动数据不展示在后台
				$where['belong_page_type'] = array('exp'," like '%customlist1\\_%' and `belong_page_type` not like '%customlist1\\_27\\_%'");
			}
            $this -> assign('custom_list_channel',$custom_list_channel);
        }
        // 默认条件
        $where['status'] = 1;
        // 翻页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->table('sj_flexible_extent')->where($where)->count();
        $page  = new Page($count, $limit);
        if($belong_page_type=='fixed_resource_channel') {
        	$list = $model->table('sj_flexible_extent')->where($where)->order('(extent_type=28),extent_id desc')->limit($page->firstRow.','.$page->listRows)->select();
        }else {
        	$list = $model->table('sj_flexible_extent')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        }
        // 处理list
        $now = time();
        foreach ($list as $key => $value) {
			$belong_page_type = $value['belong_page_type'];
			$general_page_type = ContentTypeModel::getGeneralPageType($belong_page_type);
			// 位置信息
			$rank = $value['rank'];
			if($general_page_type==9 || $value['belong_page_type']=='exclusive'){
				$rank_from = $rank;$rank_to=$rank+$value['extent_size']-1;
				$list[$key]['position_detail'] = "{$rank_from}~{$rank_to}";
			}else{
				$rank_from = $rank-1;$rank_to=$rank;
				$list[$key]['position_detail'] = "{$rank_from}~{$rank_to}";
			}
            
            
            // 所属页面
            
            $belong_page_name = ContentTypeModel::convertPageType2PageNameOfFlexible($belong_page_type,$pid);
            $list[$key]['belong_page_name'] = $belong_page_name;
            // 所属运营商
            $oid = $value['oid'];
            $list[$key]['oname'] = $oid ? $operators_key[$oid] : '不限';
            // 所属渠道名
			//V6.0渠道修改为多选
            $cid = $value['cid'];
			$cid_str = preg_replace('/^,/','', $cid);
			$cid_str = preg_replace('/,$/','', $cid_str);
			$array = explode(',', $cid_str);
			$cname = $channel_model->where("cid in ({$cid_str})")->findAll();
			
			if($cid_str=="")
			{
				$list[$key]['chname'] ='不限';
			}
			if (in_array("0",$array))
			{
				$list[$key]['chname'] .= "<p>通用</p>";
			}
			foreach ($cname as $k1 => $v1) 
			{
				if($list[$key]['chname']=="")
				{
					if($k1==0)
					{
						if(mb_strlen($v1['chname'],'utf-8')>10)
						{
						 $short_chname=mb_substr($v1['chname'],0,10,'utf-8');
						 $list[$key]['chname'].="<p>{$short_chname}</p>";
						}
						else
						{
						   $list[$key]['chname'] .= "<p>{$v1['chname']}</p>";
						}
					}
					if($k1>=1)
					{
					  $short=mb_substr($v1['chname'],0,6,'utf-8');
					  $list[$key]['chname'] .= "<p>{$short}...</p>";
					  break;
					}
				}
				else
				{
					if($k1>=0)
					{
					  $short=mb_substr($v1['chname'],0,6,'utf-8');
					  $list[$key]['chname'] .= "<p>{$short}...</p>";
					  break;
					}
				}
			}
			
            // 描述只显示一部分
            $list[$key]['display_description'] = $this->shorten_sentence($value['display_description']);
            // 软件数
            $where = array(
                'extent_id' => $value['extent_id'],
                'start_at' => array('elt', $now),
                'end_at' => array('egt', $now),
                'status' => 1
            );
			if($value['extent_type']==32){
				$where['package'] = array('exp'," != '{$this->special_package}'");
			}
            $count = $model->table('sj_flexible_extent_soft')->where($where)->count();
            $list[$key]['soft_counts'] = $count;
        }
        if($general_page_type == 9){
            $category_list = ContentTypeModel::getCustomListCategory($custom_channel);
        }else{
            $category_list = ContentTypeModel::getCategoryTypesOfFlexible($pid,$general_page_type);
        }
		$extent_type = ContentTypeModel::getExtentType();
        $cont = $_GET['cont'] ? $_GET['cont'] : 0;
        if($cont == 1) $this->assign('cont', $cont);
		$this->assign('extent_type', $extent_type);
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->assign('product_list', $product_list);
        $this->assign('products_key', $products_key);
        $this->assign('category_list', $category_list);
        $this->assign('apkurl', ATTACHMENT_HOST);
        
        $this->assign('pid', $pid);
        $this->display('index');
    }
    
    public function add_extent() 
	{
        if ($_POST) 
		{
            $model = M('flexible_extent');
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
		
			isset($_POST['extent_name']) && $map['extent_name'] = trim($_POST['extent_name']);
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];

            // v6.4.4新增场景卡片名称
            $extent_name = trim($_POST['extent_name']);
            if (!$extent_name) {
                $this->error("区间名不能为空");
            }
            $w = array(
                'extent_name' => $extent_name,
                'status' => 1,
                'pid' => $_POST['pid'],
                'belong_page_type' => $_POST['belong_page_type'],
            );
            $n = $model->where($w)->count();
            if ($n){
                $this->error("区间名称 {$extent_name} 已存在！");
                exit;
            }
//             if(mb_strlen($extent_name,'utf-8')>10){
//                 $this->error('分区名称不能超过10个字');
//             }
            if($_POST['extent_type']==28){
                $scene_card = trim($_POST['scene_card']);
                if(!$scene_card){
                    $this->error('场景卡片不能为空');
                }
                /*if(mb_strlen($scene_card,'utf-8')>10){
                    $this->error('场景卡片名称不能超过10个字');
                }*/
                $we = array(
                    'scene_card' => $scene_card,
                    'status' => 1,
                    'pid' => $_POST['pid'],
                    'belong_page_type' => $_POST['belong_page_type'],
                );
                $nu = $model->where($we)->count();
                if ($nu){
                    $this->error("场景卡片名称 {$_POST['scene_card']} 已存在！");
                }
                $map['scene_card'] = $scene_card;
            }elseif($_POST['extent_type']==32){
				$info = $model->where(array('extent_type'=>32,'status'=>1,'belong_page_type'=>$_POST['belong_page_type']))->find();
				if($info){
					$this->error('已存在区间类型为多排-专题/页面（自动取软件），此类型只能配置一条');
				}
			}
			$general_page_type = ContentTypeModel::getGeneralPageType($_POST['belong_page_type']);
			
			//V6.0渠道修改为多选
			//isset($_POST['cid']) && $map['cid'] = $_POST['cid'];
			$channel_id_array=$_POST['cid'];
			$cids = array_unique($channel_id_array);
            if (count($cids) > 0) {
                $s = implode(',', $cids);
                $s = ",{$s},";
                $map['cid'] = $s;
            }
			
			isset($_POST['to_pos']) && $map['rank'] = $_POST['to_pos'];
			isset($_POST['channel_id']) && $map['channel_id'] = $_POST['channel_id'];
            // 现在的extent_size固定为1
			///isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			if($general_page_type == 9||$_POST['belong_page_type']=="exclusive"){
				$map['extent_size'] = $_POST['to_pos']-$_POST['from_pos']+1;
			}else{
				$map['extent_size'] = 1;
			}
            
			!empty($_POST['belong_page_type']) && $map['belong_page_type'] = $_POST['belong_page_type'];
			!empty($_POST['pid']) && $map['pid'] = $_POST['pid'];
            
            /////////////////////////新增区间类型：单排、双排、多软件 V6.0去掉双排
            $map['extent_type'] = $_POST['extent_type'];
            // 多软件类型区间去掉，用自定义列表替代之
            if ($map['extent_type'] == 4) {
                $this->error("不可以添加此类型区间！");
            }
            if ($map['extent_type'] != 1) {
                $map['release_time'] = time();
            }
			if ($map['extent_type'] == 32||$map['extent_type'] == 35||$map['extent_type'] == 36) {
				$map['display_title'] = $_POST['display_title'];
			}
            if(isset($_POST['display_description'])) $map['display_description']=$_POST['display_description'];
            $chk_func = "chk_extent_{$map['extent_type']}";
            if(in_array($map['extent_type'],array(35,38))){
                $this->$chk_func($_POST, $map);
            }

            /*
            if ($map['extent_type'] == 4) {
                $map['display_title'] = $_POST['display_title'];
                // 图片和描述（即列表介绍）至少填一个
                if ($_POST['display_description'] == '' && $_FILES['display_image']['name'] == '') {
                    $this->error("图片和列表介绍至少填一个");
                }
                if ($_POST['display_description'] != '') {
                    if (mb_strlen($_POST['display_description'], 'utf-8') < 30)
                        $this->error("列表介绍不得少于30个字");
                    $map['display_description'] = $_POST['display_description'];
                }
                if ($_FILES['display_image']['name'] != '') {
                     // 将图片存储起来
                    $folder = "/img/" . date("Ym/d/");
                    $this->mkDirs(UPLOAD_PATH . $folder);
                    // 取得图片后缀
                    $suffix = preg_match("/\.(jpg|png)$/", $_FILES['display_image']['name'],$matches);
                    if ($matches) {
                        $suffix = $matches[0];
                    } else {
                        $this->error('上传图片格式错误！');
                    }
                    // 判断图片长和宽
                    $img_info_arr = getimagesize($_FILES['display_image']['tmp_name']);
                    if (!$img_info_arr) {
                        $this->error('上传图片出错！');
                    }
                    $width = $img_info_arr[0];
                    $height = $img_info_arr[1];
                    if ($width != $this->image_width_long_multi || $height != $this->image_height_long_multi)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_long_multi}px，高需为{$this->image_height_long_multi}px");
                    $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                    $img_path = UPLOAD_PATH . $relative_path;
                    $ret = move_uploaded_file($_FILES['display_image']['tmp_name'], $img_path);
                    $map['display_image'] = $relative_path;
                }
            }
            */
            /////////////////////////////////////

			$rank  = (int)$_POST['to_pos'];
			$pid  = $_POST['pid'];
			$belong_page_type  = $_POST['belong_page_type'];

			//合作内容  合作站点
			if(strpos($belong_page_type,'coop')==0)
			{
				$map['coop_site'] = $_POST['coop_site']?$_POST['coop_site']:0;
			}
            //如果是首页推荐2 不用验证所在位置
			if( $belong_page_type == 'fixed_homepage_recommend' ) {
                $rank = intval($_POST['rank']);
                if(!$rank){
                    $this->error("所在位置填写有误！");
                    exit;
                }
                $map['rank'] = $rank;
                $extent_size = intval($_POST['extent_size']);
                if(!$extent_size){
                    $this->error('区间位置数必填');
                }
                $map['extent_size'] = $extent_size;
                $depot_limit = intval($_POST['depot_limit']);
                if(!$depot_limit){
                    $this->error('区间默认返回备选库数量必填');
                }
                $map['depot_limit'] = $depot_limit;
                $map['filter_installed'] = $_POST['filter_installed'];
                $map['filter_rule'] = $_POST['filter_rule'];
                $extent_type = $_POST['extent_type'];
                if(in_array($extent_type, array(2,24,26,28,29,1000,1001)) ) {
                	$map['is_resource'] = $_POST['is_resource']?1:0;
                    if(in_array($extent_type,array(1000,1001))) $_POST['is_resource'] = $map['is_resource'] =1;
                	if($_POST['is_resource'] && in_array($extent_type, array(2,24,26,29,1000,1001))) {
                		$map['cont_level']		=	implode(",",$_POST['cont_level']);
                		$map['cont_quality']	=	$_POST['cont_quality'];
                		$map['cont_src']		=	$_POST['cont_src'];
                		//if($extent_type == 2) {
                			$map['cont_type']	=	$_POST['cont_type'];
                			$map['soft_type']		=	$_POST['soft_type'];
                		//}
                	}
                }
            }else {
                if($general_page_type==9||$_POST['belong_page_type']=="exclusive"){
                    $where = "where status=1 and ((rank >= '{$_POST['from_pos']}' and rank <= '{$_POST['to_pos']}') or (rank <= '{$_POST['from_pos']}' and rank+extent_size-1 >= '{$_POST['to_pos']}') or (rank+extent_size-1 >= '{$_POST['from_pos']}' and rank+extent_size-1 <= '{$_POST['to_pos']}')) and pid='{$_POST['pid']}' and belong_page_type='{$_POST['belong_page_type']}'";
                }else{
                    $s = $map['rank'];
                    $e = $map['rank'] + $map['extent_size'] - 1;
                    $where = "where status=1 and rank<={$e} and rank+extent_size-1>={$s} and pid='{$_POST['pid']}' and belong_page_type='{$_POST['belong_page_type']}'";
                }
                $sql = "select * from sj_flexible_extent {$where}";
                //echo $sql;exit();
                $n = $model->query($sql);
                if ($n){
                    $msg = '';
                    foreach ($n as $v) {
                        $s1 = $v['rank'];
                        $e1 = $v['rank'] + $v['extent_size'] - 1;
                        $msg .= "区间[{$v['extent_name']}]:位置{$s1}~{$e1},";
                    }
                    //$this->error("设置的位置{$s} ~ {$e} 与{$msg}有冲突！");
                    $this->error("该位置已有广告位存在");
                    exit;
                }
            }

			
			if(isset($_POST['is_more'])) $map['is_more'] = $_POST['is_more'];
			if($general_page_type==9||$_POST['belong_page_type']=="exclusive")$map['rank'] = $_POST['from_pos'];
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			if ($id = $model->add($map)) {

                #用于 内容生产邮件系统 统计用
                $cont = $_POST['cont'];
                if($cont == 1){ 
                    $cont_Levelids = $resid = '';
                    $bgcard_id = $id;#用于 内容生产邮件系统 统计用
                    $cont_level = $_POST['content_level'];#用于 内容生产邮件系统 统计用
                    $cont_nature = $_POST['content_nature'];#用于 内容生产邮件系统 统计用
                    $cont_level_extent_type = $_POST['extent_type'];#用于 内容生产邮件系统 统计用
                    if($bgcard_id && $cont_level && $cont_level_extent_type == 28){  
                        $contlevelMod = D('Sj.ContLevel');
                        if($cont_level_extent_type == 28) $cont_type = 5;
                        $cont_Levelids_arr = $cont_Levelids;
                        $cont_level = $cont_level;
                        $conlevelfrom = 1;
                        $contlevelMod -> addData($cont_Levelids_arr,$cont_level,$cont_nature,$cont_type,$conlevelfrom,$resid,$bgcard_id);
                    }
                }

				//此频道排序需要特殊处理
				if( $belong_page_type == 'fixed_homepage_recommend' ) {
					$this->_updateRankInfoExt('-1', $pid, 0, $id, $_POST['rank']);
				}
				if ($map['extent_type'] == 32||$map['extent_type'] == 34) {
					$this->deal_extent_soft($id, $map['extent_type'], $_POST);
				}
				//$this->assign('jumpUrl', "__URL__/index/belong_page_type/{$belong_page_type}/pid/{$pid}");
				$this->writelog('推荐管理-灵活运营样式-添加了id为'.$id.'的区间', 'sj_extent',$id,__ACTION__ ,"","add");
				$this->success('添加成功');
			}
        } else {
        	//首页推荐2的频道情况
        	if($_GET['belong_page_type'] == 'fixed_homepage_recommend') {
        		$where_extent_v1 = array(
        				'status' => 1,
        				'pid' => $_GET['pid'],
        				'parent_id' =>  0,
        				'type' => array('NEQ', 3),
        				'extent_type' => array('NEQ', 4),
        		);
        		$count_1 = M('')->table('sj_extent_v2')-> where($where_extent_v1)->count();
        		$where_flexible = array(
        				'belong_page_type'	=>	'fixed_homepage_recommend',
        				'status'	=>	1,
        				'pid'		=>	$_GET['pid'],
        		);
        		$count_2 = M('')->table('sj_flexible_extent')-> where($where_flexible)->count();
        		
        		$count = $count_1 + $count_2 + 1;
        		$this->assign('count', $count);
        	}
        	
			$general_page_type = ContentTypeModel::getGeneralPageType($_GET['belong_page_type']);
            $this->assign('general_page_type', $general_page_type);
            // 运营商
            $operating_db = D('Sj.Operating');
        	$operating_list = $operating_db->field('oid,mname')->select();
			//内容合作
			$coop_site_result = ContentTypeModel::getCoopSite();
			$extent_type = ContentTypeModel::getExtentType();
            $cont = $_GET['cont'] ? $_GET['cont'] : 0;
            if($cont == 1){
                $content_zl = content_level_selecttag();#用于 内容生产邮件系统 统计用
                $content_xz = content_nature_selecttag();#用于 内容生产邮件系统 统计用
                $this->assign('content_zl', $content_zl);
                $this->assign('content_xz', $content_xz);
                $this->assign('cont', $cont);
            }
            
			$this->assign('extent_type', $extent_type);
			$this->assign('coop_site', $coop_site_result);
			 
        	$this->assign('operatinglist',$operating_list);
            // 在哪个平台添加
            $this->assign('pid', $_GET['pid']);
            // 在哪个运营页面添加
            $this->assign('belong_page_type', $_GET['belong_page_type']);
            $this->display();
        }
    }
    
    function edit_extent() 
	{
        $extent_id = $_REQUEST['extent_id'];
        $model = M('flexible_extent');
        $where = array(
			'extent_id' => $extent_id
		);
        $extent = $model->where($where)->find();
        $general_page_type = ContentTypeModel::getGeneralPageType($extent['belong_page_type']);
        $this->assign('general_page_type', $general_page_type);
        if ($_POST) 
		{
            $map = array();
            $map['update_at'] = time();
            // 区间名
            $extent_name = trim($_POST['extent_name']);
            if (!$extent_name) {
                $this->error("区间名不能为空");
            }
//             if(mb_strlen($extent_name,'utf-8')>10){
//                 $this->error('分区名称不能超过10个字');
//             }
            // 查找区间名是否在其所在的平台和页面已存在
            $pid = $extent['pid'];
            $belong_page_type = $extent['belong_page_type'];
            $where = array(
                'extent_name' => $extent_name,
                'pid' => $pid,
                'belong_page_type' => $belong_page_type,
                'status' => 1,
                'extent_id' => array('neq', $extent_id),
            );
            $find = $model->where($where)->find();
            if ($find) {
                $this->error("区间名称 {$extent_name} 已存在！");
            }
            $map['extent_name'] = $extent_name;

            // v6.4.4新增场景卡片名称
            if($extent['extent_type']==28){
                $scene_card = trim($_POST['scene_card']);
                if(!$scene_card){
                    $this->error('场景卡片不能为空');
                }
                /*if(mb_strlen($scene_card,'utf-8')>10){
                    $this->error('场景卡片名称不能超过10个字');
                }*/
                $we = array(
                    'scene_card' => $scene_card,
                    'status' => 1,
                    'pid' => $pid,
                    'belong_page_type' => $belong_page_type,
                    'extent_id' => array('neq', $extent_id),
                );
                $nu = $model->where($we)->count();
                if ($nu){
                    $this->error("场景卡片名称 {$scene_card} 已存在！");
                }
                $map['scene_card'] = $scene_card;
            }

			//合作内容  合作站点
			if(strpos($belong_page_type,'coop')==0)
			{
				$map['coop_site'] = $_POST['coop_site']?$_POST['coop_site']:0;
			}

              //如果是首页推荐2 不用验证所在位置
            if( $belong_page_type == 'fixed_homepage_recommend' ) {
                if($extent['extent_type']!=34){
                    //原生广告编辑时不能编辑位置
                    $rank = intval($_POST['rank']);
                    if(!$rank){
                        $this->error("所在位置填写有误！");
                        exit;
                    }
                    $map['rank'] = $rank;
                }
                $extent_size = intval($_POST['extent_size']);
                if(!$extent_size){
                    $this->error('区间位置数必填');
                }
                $map['extent_size'] = $extent_size;
                $depot_limit = intval($_POST['depot_limit']);
                if(!$depot_limit){
                    $this->error('区间默认返回备选库数量必填');
                }
                $map['depot_limit'] = $depot_limit;
                $map['filter_installed'] = $_POST['filter_installed'];
                $map['filter_rule'] = $_POST['filter_rule'];
                $extent_type = $_POST['extent_ty'];
                if(in_array($extent_type, array(2,24,26,28,29,1000,1001)) ) {
                	$map['is_resource'] = $_POST['is_resource']?1:0;
                    if(in_array($extent_type,array(1000,1001))) $_POST['is_resource'] = $map['is_resource'] = 1;
                	if($_POST['is_resource'] && in_array($extent_type, array(2,24,26,29,1000,1001))) {
                		$map['cont_level']		=	implode(',',$_POST['cont_level']);
                		$map['cont_quality']	=	$_POST['cont_quality'];
                		$map['cont_src']		=	$_POST['cont_src'];
                		// if($extent_type == 2) {
                			$map['cont_type']	=	$_POST['cont_type'];
                			$map['soft_type']	=	$_POST['soft_type'];
                		// }
                	}
                }
            }elseif( $belong_page_type == 'fixed_resource_channel' ) {
            	$map['rank'] = 0;
            }else {
                // 位置
    			$map['rank'] = $rank = $_POST['to_pos'];
                if (!$rank) {
                    $this->error("请填写区间位置");
                }
                // 检查位置是否有冲突
    			if($general_page_type == 9||$belong_page_type=="exclusive"){
    				$map['extent_size'] = $_POST['to_pos']-$_POST['from_pos']+1;
    			}else{
    				$map['extent_size'] = 1;
    			}

    			if($general_page_type==9||$belong_page_type=="exclusive"){
    				$where = "where status=1 and ((rank >= '{$_POST['from_pos']}' and rank <= '{$_POST['to_pos']}') or (rank <= '{$_POST['from_pos']}' and rank+extent_size-1 >= '{$_POST['to_pos']}') or (rank+extent_size-1 >= '{$_POST['from_pos']}' and rank+extent_size-1 <= '{$_POST['to_pos']}')) and pid='{$extent['pid']}' and belong_page_type='{$belong_page_type}' and extent_id<>{$extent_id};";
    			}else{
    				$s = $map['rank'];
    				$e = $map['rank'] + $map['extent_size'] - 1;
    				$where = "where status=1 and rank<={$e} and rank+extent_size-1>={$s} and pid='{$extent['pid']}' and belong_page_type='{$belong_page_type}' and extent_id<>{$extent_id};";
    			}
    			$sql = "select * from sj_flexible_extent {$where}";
                $conflict_rank_list = $model->query($sql);
                if (!empty($conflict_rank_list)) {
                    $this->error("该位置已有广告位存在");
                }
                $map['rank'] = $rank;
    			if($general_page_type==9||$belong_page_type=="exclusive")$map['rank'] = $_POST['from_pos'];
            }
            // 运营商和渠道
            $map['oid'] = $_POST['oid'] ? $_POST['oid'] : 0;
			
			//V6.0渠道修改为多选
           // $map['cid'] = $_POST['cid'] ? $_POST['cid'] : 0;
			$channel_id_array=$_POST['cid'];
			$cids = array_unique($channel_id_array);
			$s = implode(',', $cids);
			$s = ",{$s},";
			$map['cid'] = $s;
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			
            // 如果是多排-软件区间，还应填多几个数据
            $extent_type = $extent['extent_type'];
            // 多软件类型区间去掉，用自定义列表替代之
            if ($extent_type == 4) {
                $this->error("不可以添加此类型区间！");
            }
            $chk_func = "chk_extent_{$extent_type}";
            if(in_array($extent_type,array(35,38))){
                $this->$chk_func($_POST, $map);
            }

			if(isset($_POST['is_more'])) $map['is_more'] = $_POST['is_more'];
			if ($extent_type == 32||$extent_type == 35||$extent_type == 36) {
				$map['display_title'] = $_POST['display_title'];
			}
            if(isset($_POST['display_description'])) $map['display_description']= $_POST['display_description'];
            /*
            if ($extent_type == 4) {
                $map['display_title'] = $_POST['display_title'];
                if ($_POST['display_description'] == '' && $_FILES['display_image']['name'] == '') {
                    // 查看一下该记录原来有没有存储图片
                    $find = $model->where("extent_id={$extent_id}")->find();
                    if (!$find['display_image']) {
                        $this->error("图片和列表介绍至少填一个");
                    }
                }
                if ($_POST['display_description'] != '') {
                    if (mb_strlen($_POST['display_description'], 'utf-8') < 30)
                        $this->error("列表介绍不得少于30个字");
                }
                $map['display_description'] = $_POST['display_description'];
                if ($_FILES['display_image']['name'] != '') {
                    // 将图片存储起来
                    $folder = "/img/" . date("Ym/d/");
                    $this->mkDirs(UPLOAD_PATH . $folder);
                    // 取得图片后缀
                    $suffix = preg_match("/\.(jpg|png)$/", $_FILES['display_image']['name'],$matches);
                    if ($matches) {
                        $suffix = $matches[0];
                    } else {
                        $this->error('上传图片格式错误！');
                    }
                    // 判断图片长和宽
                    $img_info_arr = getimagesize($_FILES['display_image']['tmp_name']);
                    if (!$img_info_arr) {
                        $this->error('上传图片出错！');
                    }
                    $width = $img_info_arr[0];
                    $height = $img_info_arr[1];
                    if ($width != $this->image_width_long_multi || $height != $this->image_height_long_multi)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_long_multi}px，高需为{$this->image_height_long_multi}px");
                    $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                    $img_path = UPLOAD_PATH . $relative_path;
                    move_uploaded_file($_FILES['display_image']['tmp_name'], $img_path);
                    $map['display_image'] = $relative_path;
                }
            }
            */
            // 准备日志
            $log = $this -> logcheck(array('extent_id' => $extent_id),'sj_flexible_extent',$map,$model);
            // 更新库
            $where = array('extent_id' => $extent_id);
            $ret = $model->where($where)->save($map);
			if ($ret || $ret === 0) {
                if ($extent_type == 34) {
                    $this->deal_extent_soft($extent_id, $extent_type, $_POST, 'save');
                }
				//$this->assign('jumpUrl', "__URL__/index/belong_page_type/{$belong_page_type}/pid/{$pid}");
				$this -> writelog('推荐管理-灵活运营样式-编辑了extent_id为'.$extent_id."的区间".$log, 'sj_extent', '', __ACTION__, 'rank_config','edit');
				$this->success('编辑成功');
			} else {
				$this->error('编辑失败');
			}
            
        } else {
            if($extent['cont_level']){
                $extent['cont_level'] = explode(',',$extent['cont_level']);
            }
        	//首页推荐2的频道情况
        	if($extent['belong_page_type'] == 'fixed_homepage_recommend') {
        		$where_extent_v1 = array(
        				'status' => 1,
        				'pid' => $_GET['pid'],
        				'parent_id' =>  0,
        				'type' => array('NEQ', 3),
        				'extent_type' => array('NEQ', 4),
        		);
        		$count_1 = M('')->table('sj_extent_v2')-> where($where_extent_v1)->count();
        		$where_flexible = array(
        				'belong_page_type'	=>	'fixed_homepage_recommend',
        				'status'	=>	1,
        				'pid'		=>	$_GET['pid'],
        		);
        		$count_2 = M('')->table('sj_flexible_extent')-> where($where_flexible)->count();
        		$count = $count_1 + $count_2;
        		$this->assign('count', $count);
        	}
            // 运营商
            $operating_db = D('Sj.Operating');
        	$operating_list = $operating_db->field('oid,mname')->select();
        	$this->assign('operatinglist',$operating_list);
            // 该区间所在的区间名，编辑时展示用到
			//V6.0渠道修改为多选
            $cid = $extent['cid'];
			$cookstr = preg_replace('/^,/','', $cid);
			$cookstr = preg_replace('/,$/','', $cookstr);
			$array = explode(',', $cookstr);
			$chl = $model -> table('sj_channel')->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
			
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
            if($extent['extent_type']==34){
                $extent_soft = $model->table('sj_flexible_extent_soft')->where("extent_id = '{$extent_id}'")->field('id,parameter_field')->find();
                $parameter_field = json_decode($extent_soft['parameter_field'],true);
                $extent['native_ad_id'] = $parameter_field['native_ad_id'];
                $extent['native_ad_type'] = $parameter_field['native_ad_type'];
            }
			//内容合作
			$coop_site_result = ContentTypeModel::getCoopSite();
			$this->assign('coop_site', $coop_site_result);
			$extent_type = ContentTypeModel::getExtentType();
			$this->assign('extent_type', $extent_type);
            $this->assign('chl_list',$chl);
            $this->assign('extent', $extent);
            $this->display();
        }
    }
    
    function del_extent() {
		$extent_id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $extent_id
		);
		$map = array(
			'status' => 0,
            'update_at' => time()
		);
		$model = M('flexible_extent');
		$extent_info = $model->table('sj_flexible_extent')->where($where)->find();
		$model->table('sj_flexible_extent')->where($where)->save($map);
		$model->table('sj_flexible_extent_soft')->where($where)->save($map);
		
		$where = array(
			'parent_id' =>$extent_id
		);
		$res = $model->table('sj_flexible_extent')->where($where)->field('extent_id')->select();
		$extent_ids = array();
		foreach($res as $v){
			$extent_ids[] = $v['extent_id'];
		}
		if (!empty($extent_ids)) {
			$where = array(
				'extent_id' => array('in', $extent_ids)
			);
			$model->table('sj_flexible_extent')->where($where)->save($map);
			$model->table('sj_flexible_extent_soft')->where($where)->save($map);
		}
		$this->_updateRankInfoExtDel($extent_info['pid'], $extent_info['parent_id']);
		$this->writelog('推荐管理-灵活运营样式-删除了id为'.$extent_id.'的区间', 'sj_extent', $extent_id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}
    
    function release_extent() {
        $model = M('flexible_extent');
        $soft_model = M('flexible_extent_soft');
        $extent_id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $extent_id
		);
        $result = $model->where($where)->field('extent_type')->find();
        if($result['extent_type']==28){
            $count = $soft_model->where(array('extent_id'=>$extent_id,'status'=>1))->count();
            if($count<3){
                $this->error('场景卡片分区至少有3款软件才能发布');
            }
        }
        $now = time();
		$map = array(
            'update_at' => $now,
			'release_time' => $now,
		);		
		$model->where($where)->save($map);
        $this->writelog("灵活运营样式：发布了id为{$extent_id}的区间", 'sj_flexible_extent', $extent_id,__ACTION__ ,"","edit");
        $this->success('发布成功');
    }
    
    public function list_soft(){
        if($_GET['from']=='fixed_resource_channel'){
            //资源库列表包名搜索
            $this -> assign('from', 'fixed_resource_channel');
            $this->res_list_soft();exit();
        }
        $model = M('flexible_extent_soft');
        $extent_id = $_GET['extent_id'];
        // 判断extent_id在哪个页面中，方便导航栏数据
        $where_e = array(
        		'extent_id' => $extent_id,
        		'status' => 1,
        );
        $extent = $model->table('sj_flexible_extent')->where($where_e)->find();
        //如何是区间是资源库则跳转到资源库列表
        if($extent['extent_type'] != 28 && $extent['belong_page_type'] == 'fixed_resource_channel') {
        	$this->assign('belong_page_type', 'fixed_resource_channel');
        	$this->assign('extent_name', $extent['extent_name']);
            $cont = $_GET['cont'] ? $_GET['cont'] : 0;
            $this->assign('cont', $cont);
        	$this->res_list_soft();die;
        }
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        // 请求的是哪个时间段
        $period = $_GET['period'];
        if ($period != 1 && $period != 2 && $period != 3) {
            $period = 2;
        }
        $now = time();
        if ($period == 1) {
            // 已过期
            $where['end_at'] = array('lt', $now);
        } else if ($period == 2) {
            // 正在排期中的
            $where['start_at'] = array('elt', $now);
            $where['end_at'] = array('egt', $now);
        } else {
            // 还未排期中的
            $where['start_at'] = array('gt', $now);
        }
        // 翻页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->where($where)->count();
        $page  = new Page($count, $limit);
		if($extent['extent_type']==32){
			$where['package'] = array('exp'," != '{$this->special_package}'");
		}
        $list = $model->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('start_at asc')->select();
        $util = D("Sj.Util"); 
        // 处理list
        $activity_arr = array();
        foreach ($list as $key => $value) {
            if(!empty($value['res_id'])){
                $val = $model->table('sj_flexible_extent_soft')->where(array('id'=>$value['res_id'], 'status'=>1))->find();
                $list[$key]['title'] = $val['title'];
                $list[$key]['image_url'] = $val['image_url'];
                $list[$key]['high_image_url'] = $val['high_image_url'];
                $list[$key]['low_image_url'] = $val['low_image_url'];
                $list[$key]['gif_image_url'] = $val['gif_image_url'];
                $list[$key]['gif_image_url_62'] = $val['gif_image_url_62'];
                $list[$key]['content_type'] = $val['content_type'];
                $list[$key]['gift_id'] = $val['gift_id'];
                $list[$key]['strategy_id'] = $val['strategy_id'];
                $list[$key]['activity_name'] = $val['activity_name'];
                $list[$key]['feature_name'] = $val['feature_name'];
                $list[$key]['page_name'] = $val['page_name'];
                $list[$key]['website'] = $val['website'];
                $list[$key]['recommend_order_name'] = $val['recommend_order_name'];
                
                if($val['resource_type'] == 29 && $val['is_dev']==1) {
                	$parameter_field = json_decode($val['parameter_field'], true);
                	$video_id = $parameter_field['video_id'];
                	$video_one = $model->table('sj_soft_extra')->field('video_title,video_pic,video_h263_url')->where("id={$video_id}")->find();
                	$list[$key]['title'] = $video_one['video_title'];
                	$list[$key]['video_pic'] = $video_one['video_pic'];
                	$list[$key]['video_url'] = $video_one['video_h263_url'];
                }
                
                $content_type = $val['content_type'];
                //运营标识 软件=>推荐、专题=>专题、活动=>活动、礼包=>礼包、攻略=>攻略、
                if ($content_type == 1) {
                    // 软件名
                    $package = $val['package'];
                    $where = array(
                        'package' => $package,
                        'status' => 1,
                        'hide' => 1,
                    );
                    $find = $model->table('sj_soft')->where($where)->order('version_code desc')->find();
                    $softname = $find['softname'];
                    $list[$key]['lead_content'] = "{$package}({$softname})";
                    $list[$key]['mark_name'] = "推荐";
                } if ($content_type == 2) {
                    // 活动名称
                    $list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($val['activity_id']);
                    $list[$key]['lead_content'] = $list[$key]['activity_name'];
                    $list[$key]['mark_name'] = "活动";
                } else if ($content_type == 3) {
                    // 专题名称
                    $list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($val['feature_id']);
                    $list[$key]['lead_content'] = $list[$key]['feature_name'];
                    $list[$key]['mark_name'] = "专题";
                } else if ($content_type == 4) {
                    // 页面名称
                    $list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($val['page_type']);
                    $list[$key]['lead_content'] = $list[$key]['page_name'];
                    if($val['opera_mark_num']==30)
                    {
                        $list[$key]['mark_name'] = $val['opera_mark_name'];
                    }
                    else
                    {
                        $list[$key]['mark_name'] = ContentTypeModel::convertnum2MarkName($val['opera_mark_num']);
                    }
                    
                } else if ($content_type == 5) {
                    $list[$key]['lead_content'] = $list[$key]['website'];
                    if($val['opera_mark_num']==30)
                    {
                        $list[$key]['mark_name'] = $val['opera_mark_name'];
                    }
                    else
                    {
                        $list[$key]['mark_name'] = ContentTypeModel::convertnum2MarkName($val['opera_mark_num']);
                    }
                }else if ($content_type == 6) {
                    $list[$key]['lead_content'] = $list[$key]['gift_id'];
                    $list[$key]['mark_name'] = "礼包";
                }else if ($content_type == 7) {
                    $list[$key]['lead_content'] = $list[$key]['strategy_id'];
                    $list[$key]['mark_name'] = "攻略";
                }else if ($content_type == 8) {
                    // 活动名称
                    $list[$key]['recommend_order_name'] = ContentTypeModel::convertOrderId2OrderName($value['activity_id']);
                    $list[$key]['lead_content'] = $list[$key]['recommend_order_name'];
                    $list[$key]['mark_name'] = "预约";
                }else if ($content_type == 9) {
                    $used_info = json_decode($val['parameter_field'],true);
                    $list[$key]['lead_content'] = isset($used_info['title'])?$used_info['title']:'';
                    $list[$key]['mark_name'] = "应用内览";
                }
            }else{
                $content_type = $value['content_type'];
                //运营标识 软件=>推荐、专题=>专题、活动=>活动、礼包=>礼包、攻略=>攻略、
                if ($content_type == 1) {
                    // 软件名
                    $package = $value['package'];
                    $where = array(
                        'package' => $package,
                        'status' => 1,
                        'hide' => 1,
                    );
                    $find = $model->table('sj_soft')->where($where)->order('version_code desc')->find();
                    $softname = $find['softname'];
                    $list[$key]['lead_content'] = "{$package}({$softname})";
                    $list[$key]['mark_name'] = "推荐";
                } if ($content_type == 2) {
                    // 活动名称
                    $list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($value['activity_id']);
                    $list[$key]['lead_content'] = $list[$key]['activity_name'];
                    $list[$key]['mark_name'] = "活动";
                } else if ($content_type == 3) {
                    // 专题名称
                    $list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($value['feature_id']);
                    $list[$key]['lead_content'] = $list[$key]['feature_name'];
                    $list[$key]['mark_name'] = "专题";
                } else if ($content_type == 4) {
                    // 页面名称
                    $list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($value['page_type']);
                    $list[$key]['lead_content'] = $list[$key]['page_name'];
                    if($value['opera_mark_num']==30)
                    {
                        $list[$key]['mark_name'] = $value['opera_mark_name'];
                    }
                    else
                    {
                        $list[$key]['mark_name'] = ContentTypeModel::convertnum2MarkName($value['opera_mark_num']);
                    }
                    
                } else if ($content_type == 5) {
                    $list[$key]['lead_content'] = $list[$key]['website'];
                    if($value['opera_mark_num']==30)
                    {
                        $list[$key]['mark_name'] = $value['opera_mark_name'];
                    }
                    else
                    {
                        $list[$key]['mark_name'] = ContentTypeModel::convertnum2MarkName($value['opera_mark_num']);
                    }
                }else if ($content_type == 6) {
                    $list[$key]['lead_content'] = $list[$key]['gift_id'];
                    $list[$key]['mark_name'] = "礼包";
                }else if ($content_type == 7) {
                    $list[$key]['lead_content'] = $list[$key]['strategy_id'];
                    $list[$key]['mark_name'] = "攻略";
                }else if ($content_type == 8) {
                    // 活动名称
                    $list[$key]['recommend_order_name'] = ContentTypeModel::convertOrderId2OrderName($value['activity_id']);
                    $list[$key]['lead_content'] = $list[$key]['recommend_order_name'];
                    $list[$key]['mark_name'] = "预约";
                }else if ($content_type == 9) {
                    $used_info = json_decode($value['parameter_field'],true);
                    $list[$key]['lead_content'] = isset($used_info['title'])?$used_info['title']:'';
                    $list[$key]['mark_name'] = "应用内览";
                }
            }
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($value['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$list[$key]['co_types'] = $v[0];
				}
			}
			if($value['package'])
			{
                $where = array(
                    'package' => $value['package'],
                    'status' => 1,
                    'hide' => 1,
                );
                $find = $model->table('sj_soft')->where($where)->order('version_code desc')->find();
				$list[$key]['new_softname'] = $find['softname'];

			}
			if($value['order_id'])
			{
                $where = array(
                    'id' => $value['order_id'],
                    'status' => 1,
                );
				$result = $model -> table('sj_game_subscriber') -> where($where) -> find();
				$game_result = $model ->table('sj_activity_page') -> where(array('ap_id' => $result['ap_id'],'status' => 1)) -> find();
				$list[$key]['game_name'] = $game_result['download_comment'];
			}
			if($value['order_type']==1)
			{
				$list[$key]['order_types'] = "样式一（预约人数）";
			}else if($value['order_type']==2)
			{
				$list[$key]['order_types'] = "样式二（游戏列表）";
			}
			ContentTypeModel::get_flexible_param($list[$key],$extent['extent_type']);
            if(isset($list[$key]['activity_id'])) $activity_arr[] = $list[$key]['activity_id'];
        }
        if($activity_arr){
            $activity = ContentTypeModel::get_activity(array('id'=>$activity_arr));
            $this->assign('activity', $activity);
        }

        $belong_page_type = $extent['belong_page_type'];

        if(in_array($belong_page_type, array('otherfixed_homepage_recommend','fixed_homepage_recommend','search_result_page'))){
            $this->assign('show_batch_edit', 1);
        }
        $belong_page_name = ContentTypeModel::convertPageType2PageNameOfFlexible($belong_page_type);
        $general_page_type = ContentTypeModel::getGeneralPageType($belong_page_type);
        $general_page_name = ContentTypeModel::convertGeneralPageType2GeneralPageName($general_page_type);
        $page_name = $extent['page_name'];
        $this->assign('general_page_type', $general_page_type);
        $this->assign('general_page_name', $general_page_name);
        $this->assign('belong_page_type', $belong_page_type);
        $this->assign('belong_page_name', $belong_page_name);
        $this->assign('extent_name', $extent['extent_name']);
		$this->assign('extent_type', $extent['extent_type']);
		$this->assign('search_type',$this->search_type);
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->assign('domain_url', ATTACHMENT_HOST);
        $this->assign('extent_id', $extent_id);
        $this->assign('period', $period);
        $this->display();
    }
    
    public function add_soft($from_action=""){
        //标题字数限制 
        $text_length = 10;
        if ($_POST) {
            $cont_Levelids = $_POST['extent_id'];#用于 内容生产邮件系统 统计用
            $cont_level = $_POST['content_level'];#用于 内容生产邮件系统 统计用
            $cont_nature = $_POST['content_nature'];#用于 内容生产邮件系统 统计用
            $cont_tags = $_POST['content_tags'];
            $tagstatus = $_POST['content_tags'] ? 1 : 0;
            $cont = $_POST['cont'];#用于 内容生产邮件系统 统计用
            $model = M();
            $map = array();
            if( !empty($from_action) ) {
            	$extent_type = $_POST['extent_type'];
            	$map['resource_type'] = $_POST['extent_type'];
            	$find = array();
            	$map['extent_id'] = 0;
            }else {
            	$extent_id = $_POST['extent_id'];
            	$map['extent_id'] = $extent_id;
            	$where = array('extent_id' => $extent_id, 'status' => 1);
            	$find = $model->table('sj_flexible_extent')->where($where)->find();
                $extent_type = $find['extent_type'];
            	if ($find['belong_page_type'] == 'fixed_resource_channel') {
            		$map['resource_type'] = $extent_type;
            	}	
            }
            if($extent_type==19){
                $text_length = 6;
            }elseif($extent_type==22||$extent_type==23){
                $text_length = 8;
            }elseif($extent_type==24){
                $text_length = 25;
            }elseif($extent_type==20||$extent_type==21||$extent_type==25||$extent_type==27||$extent_type==29){
                $text_length = 20;
            }elseif($extent_type==30){
                $text_length = 15;
            }elseif($extent_type==26){
                $text_length = 1000;
            }elseif($extent_type==5&&$_POST['position']==1){
				//V6.4.5多排新样式
				$text_length = 15;
			}
			//V6.4新增加的三个 软件热搜、软件热门下载、预约没有推荐内容
            if(($extent_type==2&&empty($_POST['resource_id']))||$extent_type==5||$extent_type==19||$extent_type==20||$extent_type==21||($extent_type==24&&empty($_POST['resource_id']))||$extent_type==25||($extent_type==26&&empty($_POST['resource_id']))||$extent_type==27||($extent_type==29&&$_POST['is_tag']==1)||$extent_type==30||$extent_type == 31){
				$map['from'] = "flexible";
				//推荐内容处理 合并
				if( !empty($from_action) ) {
					if(!empty($_POST['content_type']['one'])) {
						$rcontent=ContentTypeModel::saveRecommendContent_new($_POST,'',$map, 'one');
					}else{
						$rcontent=true;
					}
                    if(!empty($_POST['content_type']['two'])) {
                        $rcontent=ContentTypeModel::saveRecommendContent_new($_POST,'',$map, 'two');
                    }else{
                        $rcontent=true;
                    }
				}else {
					$content_type = $_POST['content_type'];
					$map['content_type'] = $content_type;
					$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				}
				if($rcontent!==true){
					$this -> error($rcontent);
				}
			}
            //v6.4.3
            if($extent_type==22||$extent_type==23){
                $title = $_POST['title'];
                if (!$title) {
                    $this->error("模块名称不能为空");
                }
                if (mb_strlen($title, 'utf-8') > $text_length) {
                    $this->error("模块名称不能超过{$text_length}个字");
                }
                $map['title'] = $title;
                if($extent_type==22){
                    $map['image_url'] = $this->check_imagesize('image_url',$extent_type,'图片','jpg|png|gif');
                }
            }
            if($extent_type == 24|| $extent_type == 26||$extent_type == 29 || $extent_type == 2){
                $map['cont_src'] = $_POST['con_source'] ? $_POST['con_source'] : 0 ;
                $map['user_tend'] = $_POST['user_tend'] ? $_POST['user_tend'] : 0 ;
                $map['cont_level'] = $_POST['content_level'];
                $map['cont_quality'] = $_POST['content_nature'];
            }
            $map['soft_type'] = $_POST['soft_type'] ? $_POST['soft_type'] : 0;
            if(($extent_type==2&&empty($_POST['resource_id']))||($extent_type==24&&empty($_POST['resource_id']))||($extent_type==26&&empty($_POST['resource_id']))||$extent_type==25||$extent_type==27 ||($extent_type==29&&empty($_POST['resource_id_29'])&&empty($_POST['is_dev']) )||$extent_type == 39||$extent_type == 40){
            	$title = $_POST['title'];
                if (!$title) {
                    $this->error("标题不能为空");
                }
                if($extent_type != 39&&$extent_type != 40){
                    if (mb_strlen($title, 'utf-8') > $text_length) {
                        $this->error("标题最多{$text_length}个字符");
                    }
                }

                $map['title'] = $title;
            }
			if($extent_type==31){
				$subtitle = $_POST['subtitle'];
				if(!$subtitle){
                    $msg = 'banner副标题不能为空';
					$this->error($msg);
				}
			}
            if(in_array($extent_type,array(24,25,26,27,29,30,31))){
            //if($extent_type==24||$extent_type==26||$extent_type==25||$extent_type==27||$extent_type==29||$extent_type==30||$extent_type==31){
                if(trim($_POST['new_package'])){
                    $map['package_643'] = trim($_POST['new_package']);
                    $map['package_name'] = trim($_POST['package_name']);
                }else{
					$check_pack = false;
					if($extent_type!=27&&$extent_type!=31 ) $check_pack = true;
                    if($check_pack){
                        $this->error("软件包名不能为空");
                    }
                }
                if ($find['belong_page_type'] == 'fixed_resource_channel' || !empty($from_action) ) {
                	if(($extent_type!=29 || empty($_POST['is_dev'])) && $extent_type!=28){
                		if (empty($_POST['title'])) {
                			$this->error("标题不能为空");
                		}
                		$map['title'] = $_POST['title'];
                	}
                	if(!empty($_POST['is_dev'])){
                		$map['is_dev'] = $_POST['is_dev'];
                		if($extent_type==29){
                			$map['parameter_field'] = json_encode(array('video_id'=>$_POST['video_id']));
                		}
                	}else{
                		if($extent_type==24){
                			$map['image_url'] = $this->deal_img('image_url', 464, 274);
                		}elseif($extent_type==26){
                			$map['image_url'] = $this->deal_img('image_url', 160, 160);
                			$map['high_image_url'] = $this->deal_img('high_image_url', 160, 160, '图片2');
                			$map['low_image_url'] = $this->deal_img('low_image_url', 160, 160, '图片3');
                		}elseif($extent_type==29){
                			$map['video_url'] = $_POST['video_url'];
                			//表单中ajax获取的图片链接带域名，入库前要去掉
                			$map['video_pic'] = str_replace(ATTACHMENT_HOST, '', $_POST['video_pic']);
                			$map['parameter_field'] = json_encode(array('video_id'=>$_POST['video_id']));
                		}
                	}
                }else {
                	if($extent_type != 29){
                		//v6.4.4勾选开发者不用上传图
                		if($_POST['resource_id']){
                			$map['res_id'] = $_POST['resource_id'];
                		}elseif($_POST['phone_dis']){
                			$map['phone_dis'] = $_POST['phone_dis'];
                		}else{
                			if($extent_type==24 || $extent_type==25|| $extent_type==30 || $extent_type==31){
								$image_type = 'jpg|png|gif';
								if($extent_type==25|| $extent_type==31) $image_type = 'jpg|png';
                				$map['image_url'] = $this->check_imagesize('image_url',$extent_type,'图片',$image_type);
                			}
                			if($extent_type==26){
                				$map['image_url'] = $this->check_imagesize('image_url',$extent_type);
                				$map['high_image_url'] = $this->check_imagesize('high_image_url',$extent_type,'图片2');
                				$map['low_image_url'] = $this->check_imagesize('low_image_url',$extent_type,'图片3');
                			}
                		}
                	}
                }
            }
            if($extent_type == 40){
                $map['image_url'] = $this->check_imagesize('image_url',$extent_type);
            }
            if(in_array($extent_type, array(16,17,28,32,35,38,39))){
			//if($extent_type == 16||$extent_type == 17||$extent_type==28||$extent_type==32){
				$new_pkg = trim($_POST['new_package']);
				if($new_pkg){
					$map['package'] = $new_pkg;
					$map['package_name'] = trim($_POST['package_name']);
				}else{
					$this->error("软件包名不能为空");
				}
			}

            if($extent_type == 28||$extent_type == 38){
                $package_column = 'package';
                if($extent_type == 28){
                    $msg = '场景卡片分区不能添加相同软件';
                }else{
                    $msg = '多行软件分区不能添加相同软件';
                }
                $pkgs = $model->table('sj_flexible_extent_soft')->where(array('extent_id'=>$extent_id,'status'=>1,'end_at' => array('gt',time())))->field($package_column)->select();
                foreach($pkgs as $pkg){
                    if($new_pkg == $pkg[$package_column]){
                        $this->error($msg);
                    }
                }
            }
            if($extent_type==28  ){
                $des = trim($_POST['s_description']);
                $len = mb_strlen($des, 'utf-8');
                if($len > 40){
                    $this->error('一句话点评最多40个字符');
                }
                $map['description'] = $des;
            }
            if($find['belong_page_type'] != 'fixed_resource_channel' && empty($from_action)) {
				//合作形式
				if(isset($_POST['co_type'])){
					$map['co_type'] = $_POST['co_type'];	
				}else{		
					$map['co_type'] = 0;	
				}
				//市场版本 非必填 2016-12-2 
				$map['version_type'] = $_POST['type'];
	            $version_code1 = $_POST['version_code1'];
	            if($version_code1 && !preg_match('/^\d*$/',$version_code1)){
	                $this->error('市场版本只能填写数字');
	            }
	            $version_code2 = $_POST['version_code2'];
	            if($version_code2 && !preg_match('/^\d*$/',$version_code2)){
	                $this->error('市场版本只能填写数字');
	            }
				if($_POST['type']==1){
					$map['version_code'] = $version_code1;
				}
				if($_POST['type']==2){
					$map['version_code'] = $version_code2;
				}
				if($_POST['type']==3){
					$map['version_code'] = $_POST['force_update_version'];
				}
            }
		
			//V6.2单排添加图片底部说明 非必填
			if (( ($extent_type==2&&empty($_POST['resource_id']))  ||$extent_type == 30)&&$_POST['pic_bottom_des']) {
				if(mb_strlen(trim($_POST['pic_bottom_des']),'utf-8')>$this->pic_bottom_des){
					$this->error("底部图片描述字数不能超过{$this->pic_bottom_des}");
				}else{
					$map['pic_bottom_des'] = trim($_POST['pic_bottom_des']);
				}
			}
			//V6.0去掉双排
            // 如果图片是单排或双排或多排专题页面，应存储标题
            if( ($extent_type==2&&empty($_POST['resource_id'])) || $extent_type == 5 || ($extent_type==29&&empty($_POST['resource_id_29'])&&!empty($_POST['is_de'])) || $extent_type == 30 || $extent_type == 31||$extent_type==33) $check_title = true;
             if($check_title){
                $title = $_POST['title'];
				$error_title = '标题';
				if($extent_type==31) $error_title = 'banner主标题';
                if (!$title) {
                    $this->error("{$error_title}不能为空");
                }
                if (mb_strlen($title, 'utf-8') > $text_length) {
                    $this->error("{$error_title}长度不能超过{$text_length}个字");
                }
                $map['title'] = $title;
             }
            if ($extent_type==2 || $extent_type == 5) {
				$description = $_POST['description'];
				$image_name = $_FILES['image_url']['name'];
				//V6.0添加高低分的图片
				$high_image_name = $_FILES['high_image_url']['name'];
				$low_image_name = $_FILES['low_image_url']['name'];
				//V6.3添加gif的图片
				$gif_image_name = $_FILES['gif_image_url']['name'];
				$gif_image_name_62 = $_FILES['gif_image_url_62']['name'];
            }
            if ($extent_type==2 || $extent_type ==5 ) {
				if (!$high_image_name) {
                    $this->error("请上传高分图片");
                }
				if (!$low_image_name) {
                    $this->error("请上传低分图片");
                }
            }
            if($extent_type==5 && $_POST['position']==2) {
            	if (!$image_name) {
            		$this->error("请上传图片");
            	}
            }
            if ($image_name && ( $extent_type==2 || ($extent_type ==5 && $_POST['position']==2) )) {
                // 将图片存储起来
                $folder = "/img/" . date("Ym/d/");
                $this->mkDirs(UPLOAD_PATH . $folder);
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
                // 如果是单排
                if ($extent_type == 2) { // 如果是单排
                    if ($width != $this->image_width_long || $height != $this->image_height_long)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_long}px，高需为{$this->image_height_long}px");
                } /*else if ($extent_type == 3) { // 如果是双排 V6.0去掉双排
                    if ($width != $this->image_width_short || $height != $this->image_height_short)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_short}px，高需为{$this->image_height_short}px");
                }*/ else { // 如果是多排专题页面
                    if ($width != $this->image_width_long_multi || $height != $this->image_height_long_multi)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_long_multi}px，高需为{$this->image_height_long_multi}px");
                }
                $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                $img_path = UPLOAD_PATH . $relative_path;
                $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
                $map['image_url'] = $relative_path;
            }
			include_once SERVER_ROOT. '/tools/functions.php';
			// 将图片存储起来
			$folder = "/img/" . date("Ym/d/");
			$this->mkDirs(UPLOAD_PATH . $folder);
			//V6.0添加高分图片和低分图片  单排必填  多排有描述的时候选填
			if ($high_image_name && ($extent_type == 2  || $extent_type ==5)) {
                // 取得图片后缀
                $suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['high_image_url']['name'],$matches_high);
                if ($matches_high) {
                    $suffix_high = $matches_high[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr = getimagesize($_FILES['high_image_url']['tmp_name']);
                if (!$img_info_arr) {
                    $this->error('上传图片出错！');
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                // 如果是单排
                if ($extent_type == 2) { 
                    if ($width != $this->high_image_width_long || $height != $this->high_image_height_long)
                        $this->error("上传图片大小错误，宽需为{$this->high_image_width_long}px，高需为{$this->high_image_height_long}px");
                }  else { // 如果是多排专题页面
                    if ($width != $this->high_image_width_multi || $height != $this->high_image_height_multi)
                        $this->error("上传图片大小错误，宽需为{$this->high_image_width_multi}px，高需为{$this->high_image_height_multi}px");
                }
                $relative_path_high = $folder . time() . 'high_' . rand(1000,9999) . $suffix_high;
				$relative_path_high_80 = $folder . time() . 'high_80_' . rand(1000,9999) . $suffix_high;
                $img_path_high = UPLOAD_PATH . $relative_path_high;
				$img_path_high_80 = UPLOAD_PATH . $relative_path_high_80;
                $ret = move_uploaded_file($_FILES['high_image_url']['tmp_name'], $img_path_high);
                $map['high_image_url'] = $relative_path_high;
				$high_80=image_strip_size($img_path_high,$img_path_high_80,80*1024);
				if($high_80){
					$map['high_image_url_80'] = $relative_path_high_80;
				}
            }
			if ($low_image_name && ($extent_type == 2  || $extent_type ==5) ) {
                // 取得图片后缀
                $suffix_low = preg_match("/\.(jpg|png)$/", $_FILES['low_image_url']['name'],$matches_low);
                if ($matches_low) {
                    $suffix_low = $matches_low[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr = getimagesize($_FILES['low_image_url']['tmp_name']);
                if (!$img_info_arr) {
                    $this->error('上传图片出错！');
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                // 如果是单排
                if ($extent_type == 2) { 
                    if ($width != $this->low_image_width_long || $height != $this->low_image_height_long)
                        $this->error("上传图片大小错误，宽需为{$this->low_image_width_long}px，高需为{$this->low_image_height_long}px");
                }  else { // 如果是多排专题页面
                    if ($width != $this->low_image_width_multi || $height != $this->low_image_height_multi)
                        $this->error("上传图片大小错误，宽需为{$this->low_image_width_multi}px，高需为{$this->low_image_height_multi}px");
                }
                $relative_path_low = $folder . time() . 'low_' . rand(1000,9999) . $suffix_low;
				$relative_path_low_40 = $folder . time() . 'low_40_' . rand(1000,9999) . $suffix_low;
                $img_path_low = UPLOAD_PATH . $relative_path_low;
				$img_path_low_40 = UPLOAD_PATH . $relative_path_low_40;
                $ret = move_uploaded_file($_FILES['low_image_url']['tmp_name'], $img_path_low);
                $map['low_image_url'] = $relative_path_low;
				$low_40=image_strip_size($img_path_low,$img_path_low_40,40*1024);
				if($low_40){
					$map['low_image_url_40'] = $relative_path_low_40;
				}
            }
			//单排的gif图片
			if( $extent_type==2 ){
				if ($gif_image_name) {
					// 取得图片后缀
					$suffix_gif = preg_match("/\.(gif)$/", $_FILES['gif_image_url']['name'],$matches_gif);
					if ($matches_gif) {
						$suffix_gif = $matches_gif[0];
					} else {
						$this->error('上传图片格式错误！');
					}
					// 判断图片长和宽
					$img_info_arr_gif = getimagesize($_FILES['gif_image_url']['tmp_name']);
					if (!$img_info_arr_gif) {
						$this->error('上传图片出错！');
					}
					$width_gif = $img_info_arr_gif[0];
					$height_gif = $img_info_arr_gif[1];
				   
					if ($width_gif != $this->gif_width || $height_gif != $this->gif_height)
						$this->error("上传图片大小错误，宽需为{$this->gif_width}px，高需为{$this->gif_height}px");
					 
					$relative_path_gif = $folder . time() . 'gif_' . rand(1000,9999) . $suffix_gif;
					$img_path_gif = UPLOAD_PATH . $relative_path_gif;
					$ret = move_uploaded_file($_FILES['gif_image_url']['tmp_name'], $img_path_gif);
					$map['gif_image_url'] = $relative_path_gif;
				}
				if ($gif_image_name_62) {
					// 取得图片后缀
					$suffix_gif_62 = preg_match("/\.(gif)$/", $_FILES['gif_image_url_62']['name'],$matches_gif_62);
					if ($matches_gif_62) {
						$suffix_gif_62 = $matches_gif_62[0];
					} else {
						$this->error('上传图片格式错误！');
					}
					// 判断图片长和宽
					$img_info_arr_gif_62 = getimagesize($_FILES['gif_image_url_62']['tmp_name']);
					if (!$img_info_arr_gif_62) {
						$this->error('上传图片出错！');
					}
					$width_gif_62 = $img_info_arr_gif_62[0];
					$height_gif_62 = $img_info_arr_gif_62[1];
				   
					if ($width_gif_62 != $this->gif_width_62 || $height_gif_62 != $this->gif_height_62)
						$this->error("上传图片大小错误，宽需为{$this->gif_width_62}px，高需为{$this->gif_height_62}px");
					 
					$relative_path_gif_62 = $folder . time() . 'gif_62_' . rand(1000,9999) . $suffix_gif_62;
					$img_path_gif_62 = UPLOAD_PATH . $relative_path_gif_62;
					$ret = move_uploaded_file($_FILES['gif_image_url_62']['tmp_name'], $img_path_gif_62);
					$map['gif_image_url_62'] = $relative_path_gif_62;
				}
			}
			
            if (isset($description) && $extent_type == 5 && $_POST['position']==2) {
                if (mb_strlen($description, 'utf-8') > 100 || mb_strlen($description, 'utf-8') < 30) {
                    $this->error("列表介绍需在30～100字之间");
                }
                $map['description'] = $description;
            }
            // 如果是多软件-软件位，还有默认展示字段
            if ($extent_type == 4) {
                $default_display = $_POST['default_display'] ? 1 : 0;
                $map['default_display'] = $default_display;
            }
			//V6.4 新增区间类型：包名、搜索词、人气初始值、随机最小值、随机最大值
			if ($extent_type == 16||$extent_type == 17) {
				if($extent_type == 16){
					$search_key = $_POST['search_keys'];
					if($_POST['search_keys']){
						//搜索词 可以填写多个，以逗号隔开 但是不能超过30个
						$keys_arr = explode(',',$_POST['search_keys']);
						if(count($keys_arr)>30){
							$this->error("搜索词不能超过30个");
						}else{
							$map['search_keys'] = $_POST['search_keys'];
						}
					}else{
						$this->error("搜索词不能为空");
					}
				}
			}
			if($extent_type == 16||$extent_type == 17||$extent_type==20||$extent_type==21){
                //人气值 都是大于0的整数
				$matches="/^[1-9]\d*$/";
				if($_POST['init_val']){
					if(!preg_match($matches,$_POST['init_val'])){
						$this->error("人气值的初始值为正整数");
					}else{	
						$map['init_val'] = $_POST['init_val'];
					}
				}else{
					$this->error("人气值的初始值不能为空");
				}

				if($_POST['random_start']){
					if(!preg_match($matches,$_POST['random_start'])){
						$this->error("人气值的随机开始值为正整数");
					}else{	
						$map['random_start'] = $_POST['random_start'];
					}
				}else{
					$this->error("人气值的随机开始值不能为空");
				}

				if($_POST['random_end']){
					if(!preg_match($matches,$_POST['random_end'])){
						$this->error("人气值的随机结束值为正整数");
					}else{	
						$map['random_end'] = $_POST['random_end'];
					}
				}else{
					$this->error("人气值的随机结束值不能为空");
				}
			}
			if ($extent_type == 18) {
				if($_POST['order_id']){
					$map['order_id'] = $_POST['order_id'];
				}else{
					$this->error("预约名称不能为空");
				}	
				$map['order_type'] = $_POST['order_type'];
			}
			$this->check_info($extent_type,$map,$folder);
			if($extent_type == 28 || ($find['belong_page_type'] != 'fixed_resource_channel' && empty($from_action)) ) {
				if ($extent_type != 19&&$extent_type != 20&&$extent_type != 21&&$extent_type != 38&&$extent_type != 39&&$extent_type != 40){
					// 概率
					$prob = $_POST['prob'];
					if (!$prob) {
						$this->error("概率不能为空");
					}
					if (!preg_match("/^\d+$/", $prob)) {
						$this->error("显示概率应为整数");
					}
					if ($prob > 100) {
						$this->error("显示概率不能大于100");
					}
					$map['prob'] = $prob;
				}
	            // 开始和结束时间
	            $map['start_at'] = strtotime($_POST['start_at']);
	            $map['end_at'] = strtotime($_POST['end_at']);
	            if (!$map['start_at']) {
	                $this->error("开始时间不能为空");
	            }
	            if (!$map['end_at']) {
	                $this->error("结束时间不能为空");
	            }
	            if ($map['start_at'] > $map['end_at']) {
	                $this->error("开始时间不能大于结束时间");
	            }


                if($extent_type == 37){
                    $func = "chk_flexible_soft_data_{$extent_type}";
                    $chk_res = ContentTypeModel::$func($_POST, $map);
                    if($chk_res){
                        $this->error($chk_res);
                    }
                }
	            //V6.4.4搜索结果页
	            //处理上传指定搜索词csv
	            $filename_search_key=$_FILES['upload_search_keys']['tmp_name'];
	            if($find['belong_page_type']=='search_result_page' && !$filename_search_key&&!in_array($_POST['search_type'],array(2,3,4))){
	                $this->error("请上传指定搜索词csv");
	            }
	            if($filename_search_key&&!$_POST['search_keys_count']){
	                $this -> error("csv文件点击上传后才有效");
	            }
	            
	            $csv_arr = array();
	            //同一个区间下不能包含相同搜索词
	            if($_POST['search_keys_count']&&$_POST['search_keys_url']){
	                $map['search_keys_count'] = $_POST['search_keys_count'];
	                $map['search_keys_url'] = $_POST['search_keys_url'];
	            }
	            //屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				if($_POST['package']){
					$package_check =$map['package'];
					$shield_error=$AdSearch->check_shield($_POST['package'],$map['start_at'],$map['end_at']);
					$shield_error.=$AdSearch->check_shield_old($_POST['package'],$extent_id,0,'sj_flexible_extent');
					if($shield_error){
						$this -> error($shield_error);
					}
				}
				$this->get_csv_data($map);
				//V6.4.5
				//多排新样式||单软件(视频)
                if(in_array($extent_type,array(5,29,31,32,33,35,36,37,38,39,40))){
                    ContentTypeModel::save_flexible_data($_POST,$map,$extent_type);
                }

			}
            if($extent_type == 37){
                $map['activity_id'] = $_POST['activity_id'];
            }
            if($extent_type == 38){
                $map['content_type'] = 1;
            }
            if($extent_type == 36||$extent_type == 40) $map['content_type'] = 4;
			// 其他默认
            $map['tagstatus'] = $tagstatus;
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			if(in_array($_SESSION['admin']['admin_user_name'], array('刘铄麟','范国良1','杨波'))){
				$map['update_at'] = $_POST['update_at']?strtotime($_POST['update_at']):time();
			}else{
				$map['update_at'] = time();
			}

            #用于 内容生产邮件系统 统计用
            if($cont_Levelids && $cont_level && $cont_nature){  
                $contlevelMod = D('Sj.ContLevel');
                if($extent_type == 2) $cont_type = 1;
                if($extent_type == 24) $cont_type = 3;
                if($extent_type == 26) $cont_type = 4;
                if($extent_type == 29) $cont_type = 2;
                $bgcard_id = $cont_Levelids;
                $conlevelfrom = 1;
                $resid = '';
                
            }

            // 添加
			if($extent_type !=28 && $find['belong_page_type'] == 'fixed_resource_channel') {
				$is_edit = trim($_POST['is_edit']);
				if(!empty($is_edit) && $extent_type==24){
					// 同包名同内容类型已存在则编辑
					$where = array('id'=>$is_edit);
					$log = $this->logcheck($where, 'sj_flexible_extent_soft', $map, $model);
					$ret = $model->table('sj_flexible_extent_soft')->where($where)->save($map);
				}else{
					// 添加
					$map['create_at'] = time();
					$affect = $model->table('sj_flexible_extent_soft')->add($map);
				}
				if (!empty($is_edit) && $extent_type==24) {
					if ($ret || $ret === 0) {
                        $cont_Levelids_arr = $is_edit;#用于 内容生产邮件系统
                        if($cont == 1 && $cont_Levelids_arr && in_array($extent_type,array(2,24,26,29))){
                            $contlevelMod -> addData($cont_Levelids_arr,$cont_level,$cont_nature,$cont_type,$conlevelfrom,$resid,$bgcard_id);
                        }#用于 内容生产邮件系统

                        //添加内容标签映射数据
                        if(in_array($extent_type,array(2,24,26,29)) && $cont_Levelids_arr){
                            $tag_data = array();
                            $tag_data['pkgname'] = trim($_POST['new_package']);
                            $tag_data['eid'] = $cont_Levelids_arr;
                            $tag_data['tags'] = $cont_tags;
                            //先删除原有映射内容
                            $tag_res = M('')->table('cont_tags_content')->where(array('eid'=>$tag_data['eid']))->delete();
                            if($tag_res){
                                $this->writelog('内容标签映射表删除关联数据，eid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','delete');
                            }
                            //添加新数据
                            if($cont_tags){
                                $cont_model = D('Sj.ContAttribute');
                                $tag_result = $cont_model->add_cont_attrribute($tag_data);
                                if($tag_result){
                                    $this->writelog('内容标签映射表添加关联数据，cid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','add');
                                }
                            }
                        }

						$this->writelog("灵活运营资源库：编辑了id为{$is_edit}的记录，{$log}", 'sj_flexible_extent_soft', $is_edit, __ACTION__ , '', 'edit');
						$this->success("替换成功！");
					} else {
						$this->error("替换失败！");
					}
				}else{
					if($affect){
                        $cont_Levelids_arr = $affect;#用于 内容生产邮件系统
                        if($cont == 1 && $cont_Levelids_arr && in_array($extent_type,array(2,24,26,29))){
                            $contlevelMod -> addData($cont_Levelids_arr,$cont_level,$cont_nature,$cont_type,$conlevelfrom,$resid,$bgcard_id);
                        }#用于 内容生产邮件系统

                        //添加内容标签映射数据
                        if(in_array($extent_type,array(2,24,26,29)) && $cont_Levelids_arr){
                            $tag_data = array();
                            $tag_data['pkgname'] = trim($_POST['new_package']);
                            $tag_data['eid'] = $cont_Levelids_arr;
                            $tag_data['tags'] = $cont_tags;
                            //先删除原有映射内容
                            $tag_res = M('')->table('cont_tags_content')->where(array('eid'=>$tag_data['eid']))->delete();
                            if($tag_res){
                                $this->writelog('内容标签映射表删除关联数据，eid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','delete');
                            }
                            //添加新数据
                            if($cont_tags){
                                $cont_model = D('Sj.ContAttribute');
                                $tag_result = $cont_model->add_cont_attrribute($tag_data);
                                if($tag_result){
                                    $this->writelog('内容标签映射表添加关联数据，cid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','add');
                                }
                            }
                        }

						$this -> writelog('灵活运营资源库添加了id为['.$affect.']的记录', 'sj_flexible_extent_soft', $affect, __ACTION__, '', 'add');
						$this->success('添加成功');
					}else{
						$this->error('添加失败');
					}
				}
			}else {
				$ret = $model->table('sj_flexible_extent_soft')->add($map);
			}

            if ($ret) {
            	if( !empty($from_action) ){
            		$extent_soft_data = $_POST['data'];
            		$extent_soft_data['c_id_1'] = $ret;
            		$soft_id = M('')->table('sj_extent_soft_v2')->add($extent_soft_data);
            		$this->writelog("市场软件运营推荐-市场首页软件列表:在区间ID为[{$_POST['extent_v2_id']}]中添加了软件[{$_POST['package_ext']}],显示概率为{$_POST['prob']},开始时间为{$_POST['start_at']},结束时间为{$_POST['end_at']},合作方式为{$_POST['type']},", 'sj_extent_soft_v2',$soft_id,__ACTION__ ,"","add");
            		// 检查导入的区间中有没有软件数大于区间位置数的，有的话要发邮件提醒运营
            		$check_extent = array(
            				$_POST['extent_v2_id']	=>	array(
            						'start_at'	=>	$map['start_at'],
            						'end_at'	=>	$map['end_at']
            				)
            		);
            		$this->send_size_notice_email($check_extent);
            		$this->success('添加成功');
            	} 
                //$this->assign('jumpUrl', "__URL__/list_soft/extent_id/{$extent_id}");
                $this->writelog("灵活运营样式：在区间[{$extent_id}]中添加了id为{$ret}的记录","sj_flexible_extent_soft",$ret,__ACTION__ ,"","add");
                $this->success("添加成功");
            } else {
                $this->error("添加失败");
            }
        } else {
            $model = M();
            $extent_id = $_GET['extent_id'];
            $where = array('extent_id' => $extent_id, 'status' => 1);
            $find = $model->table('sj_flexible_extent')->where($where)->find();
            //资源库的情况
            if($find['belong_page_type'] == 'fixed_resource_channel' ) {
            	$extent_id		=	$_GET['extent_id'];
            	$resource_type	=	$_GET['resource_type'];
            	if($resource_type==2) {
            		$text_length = 10;
            	}elseif($resource_type==24) {
            		$text_length = 25;
            	}elseif($resource_type==26) {
            		$text_length = 1000;
            	}elseif($resource_type==29) {
            		$text_length = 20;
            	}
                $cont = $_GET['cont'] ? $_GET['cont'] : 0;

               /* $config = array(
                    'key'=>'CONTENT_SOURCE',
                    'type'=>'select',
                    'tag_id'=>'con_source',
                    'tag_name'=>'con_source',
                    'tag_tip'=>'请选择内容来源',
                    'default'=>'0',

                );
                $con_source = content_html_unit($config);
                $this->assign('con_source',$con_source);*/
                $soft_type = content_soft_type_selecttag();
                $this->assign('soft_type',$soft_type);

                if($cont == 1){
                    //获取内容属性，标签
                    $cont_model = D('Sj.ContAttribute');
                    $content_xz = $cont_model -> get_cont_quality('','radio');
                    $content_zl = $cont_model -> get_cont_level('','radio');
                    $config = array(
                        'key'=>'CONTENT_SOURCE',
                        'type'=>'radio',
                        'tag_id'=>'con_source',
                        'tag_name'=>'con_source',
                        'tag_tip'=>'请选择内容来源',
                        'default'=>'0',

                    );
                    $con_source = $cont_model -> get_cont_src($config);
                    //用户倾向
                    $config_user = array(
                        'key'=>'USER_TEND',
                        'type'=>'radio',
                        'tag_id'=>'user_tend',
                        'tag_name'=>'user_tend',
                        'tag_tip'=>'全部',
                        'default'=>'0',

                    );
                    $user_tend = $cont_model -> get_user_tend($config_user);    
                    //内容标签
                    $content_tag = $cont_model->get_cont_tags();
                    $this->assign('content_tag',$content_tag);
                    $this->assign('content_xz', $content_xz);
                    $this->assign('content_zl', $content_zl);
                    $this->assign('con_source',$con_source); 
                    $this->assign('user_tend',$user_tend);
                    $this->assign('cont', $cont);
                }
                
                $this->assign('admin_user_name', $_SESSION['admin']['admin_user_name']);
            	$this->assign('text_length', $text_length);
            	$this->assign('extent_id', $extent_id);
            	$this->assign('resource_type', $resource_type);
            	$this->display('add_res_soft');die;
            }
			$pid = $find['pid'];
            $extent_type = $find['extent_type'];
            if($extent_type==19){
                $text_length = 6;
            }elseif($extent_type==22||$extent_type==23){
                $text_length = 8;
            }elseif($extent_type==24){
                $text_length = 25;
            }elseif($extent_type==20||$extent_type==21||$extent_type==25||$extent_type==27||$extent_type==29){
                $text_length = 20;
            }elseif($extent_type==26){
                $text_length = 1000;
            }elseif($extent_type==30){
                $text_length = 15;
            }


            
			$this->assign('search_type',$this->search_type);
			$this->assign('pid', $pid);
            $this->assign('text_length',$text_length);
            $this->assign('extent_id', $extent_id);
            $this->assign('extent_type',$extent_type);
            $this->assign('final_content_type',ContentTypeModel::get_final_content_type($find['belong_page_type']));
			$this->assign('belong_page_type',$find['belong_page_type']);
			//图片大小
			$this->assign('image_width_short',$this->image_width_short);
			$this->assign('image_height_short',$this->image_height_short);
			$this->assign('image_width_long_multi',$this->image_width_long_multi);
			$this->assign('image_height_long_multi',$this->image_height_long_multi);
			
			$this->assign('high_image_width_multi',$this->high_image_width_multi);
			$this->assign('high_image_height_multi',$this->high_image_height_multi);
			$this->assign('low_image_width_multi',$this->low_image_width_multi);
			$this->assign('low_image_height_multi',$this->low_image_height_multi);
			$this->assign('gif_width',$this->gif_width);
			$this->assign('gif_height',$this->gif_height);
			$this->assign('gif_width_62',$this->gif_width_62);
			$this->assign('gif_height_62',$this->gif_height_62);
			$this->assign('image_height',$this->image_height);
			$this->assign('image_width',$this->image_width);

            //v6.4.3之后用这个
            $this->assign('image_size',$this->image_size[$extent_type]);

			//合作形式
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();
			$this->assign('typelist',$typelist);
			//底部图片文字描述个数
			$this->assign('pic_bottom_des',$this->pic_bottom_des);
            $this->display();
        }
    }
    function get_csv_data(&$map){
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
            $map['is_upload_csv'] = 0;
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
    }
    public function edit_soft($from_action="") {
        $text_length = 10;
        if ($_POST) {
            $map = array();
            $model = M('flexible_extent_soft');
            if( !empty($from_action) ) {
            	$id = $_POST['f_id'];
            	$extent_type = $_POST['extent_type'];
            	$map['resource_type'] = $_POST['extent_type'];
            	$find = array();
            }else {
            	$id = $_POST['id'];
            	$where = array(
            			'id' => $id
            	);
            	$soft = $model->where($where)->find();
            	$extent_id = $soft['extent_id'];
            	// 根据extent_id查找extent_type
            	$where = array('extent_id' => $extent_id, 'status' => 1);
            	$find = $model->table('sj_flexible_extent')->where($where)->find();
            	$extent_type = $find['extent_type'];
            }
            if($extent_type==19){
                $text_length = 6;
            }elseif($extent_type==22||$extent_type==23){
                $text_length = 8;
            }elseif($extent_type==24){
                $text_length = 25;
            }elseif($extent_type==20||$extent_type==21||$extent_type==25||$extent_type==27||$extent_type==29){
                $text_length = 20;
            }elseif($extent_type==26){
                $text_length = 1000;
            }elseif($extent_type==30){
                $text_length = 15;
            }elseif($extent_type==5&&$_POST['position']==1){
				//V6.4.5多排新样式
				$text_length = 15;
			}
			if($extent_type==31){
				$subtitle = $_POST['subtitle'];
				if(!$subtitle){
                    $msg = 'banner副标题不能为空';
                    $this->error($msg);
				}
			}
			//V6.4新增加的三个 软件热搜、软件热门下载、预约没有推荐内容
			if(($extent_type==2&&empty($_POST['resource_id']))||$extent_type==5||$extent_type==19||$extent_type==20||$extent_type==21||($extent_type==24&&empty($_POST['resource_id']))||$extent_type==25||($extent_type==26&&empty($_POST['resource_id']))||$extent_type==27||($extent_type==29&&$_POST['is_tag']==1)||$extent_type==30||$extent_type == 31){
				// 内容类型和内容不允许编辑，所以直接从库中读此字段
				$content_type = $soft['content_type'];
				$map['content_type'] = $content_type;
				$map['from'] = "flexible";
				// 先清除一下内容字段数据
				$this->clear_content($map);
				//推荐内容处理 合并
				if( !empty($from_action) ) {
					if(!empty($_POST['content_type']['one'])) {
						$rcontent=ContentTypeModel::saveRecommendContent_new($_POST,'',$map, 'one');
					}else{
						$rcontent=true;
					}
				}else {
					$content_type = $_POST['content_type'];
					$map['content_type'] = $content_type;
					$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				}
				if($rcontent!==true){
					$this -> error($rcontent);
				}
			}

            if($find['belong_page_type'] != 'fixed_resource_channel' && empty($from_action)) {
    			//合作样式
    			if(isset($_POST['co_type'])){
    				$map['co_type'] = $_POST['co_type'];
    			}else{
    				$map['co_type'] = 0;
    			}
    			//市场版本 非必填 2016-12-2 
                $map['version_type'] = $_POST['type'];
                $version_code1 = $_POST['version_code1'];
                if($version_code1 && !preg_match('/^\d*$/',$version_code1)){
                    $this->error('市场版本只能填写数字');
                }
                $version_code2 = $_POST['version_code2'];
                if($version_code2 && !preg_match('/^\d*$/',$version_code2)){
                    $this->error('市场版本只能填写数字');
                }
                if($_POST['type']==1){
                    $map['version_code'] = $version_code1;
                }
                if($_POST['type']==2){
                    $map['version_code'] = $version_code2;
                }
                if($_POST['type']==3){
                    $map['version_code'] = $_POST['force_update_version'];
                }
            }
			//V6.2单排添加图片底部说明 非必填
			if (($extent_type==2&&empty($_POST['resource_id']))||$extent_type == 30) {
				if($_POST['pic_bottom_des']){
					if(mb_strlen(trim($_POST['pic_bottom_des']),'utf-8')>$this->pic_bottom_des){
						$this->error("底部图片描述字数不能超过{$this->pic_bottom_des}");
					}else{
						$map['pic_bottom_des'] = trim($_POST['pic_bottom_des']);
					}
				}else{
					$map['pic_bottom_des']="";
				}
			}
			
            // 如果图片是单排或双排或多排专题页面，应存储标题
			//V6.0去掉双排  $extent_type == 3
            if (($extent_type==2&&empty($_POST['resource_id'])) || $extent_type == 5) {
                $title = $_POST['title'];
                if (!$title) {
                    $this->error("标题不能为空");
                }
                if (mb_strlen($title, 'utf-8') > $text_length) {
                    $this->error("标题长度不能超过{$text_length}个字");
                }
                $map['title'] = $title;
				$image_name = $_FILES['image_url']['name'];
				$high_image_name = $_FILES['high_image_url']['name'];
				$low_image_name = $_FILES['low_image_url']['name'];
				$gif_image_name = $_FILES['gif_image_url']['name'];
				$gif_image_name_62 = $_FILES['gif_image_url_62']['name'];
				$description = $_POST['description'];
            }
			//之前正确的图片尺寸再次判断
			$select=$model->table('sj_flexible_extent_soft')-> where(array('id' => $id))->select();
			$image_old_url=IMGATT_HOST.$select[0]['image_url'];
			$high_image_url_edit= IMGATT_HOST.$select[0]['high_image_url'];
			$low_image_url_edit=IMGATT_HOST.$select[0]['low_image_url'];
			
			list($width_old_edit, $height_old_edit, $type_old_edit, $attr_old_edit)=getimagesize($image_old_url);
			list($width_high_edit, $height_high_edit, $type_high_edit, $attr_high_edit)=getimagesize($high_image_url_edit);
			list($width_low_edit, $height_low_edit, $type_low_edit, $attr_low_edit)=getimagesize($low_image_url_edit);
			
            if ($extent_type == 5) {
				 // 查看一下该记录原来有没有存储图片
				$find_soft = $model->where("id={$id}")->find();
				//多排图片改为必填
				if (!$image_name && !$high_image_name && !$low_image_name) {
					if (!$find_soft['image_url']&&!$find_soft['high_image_url']&&!$find_soft['low_image_url']) {
						$this->error("图片为必填项");
					}
                }
				if(!$image_name&&$_POST['position']==2){
					if (!$find_soft['image_url']) {
                        $this->error("请上传图片");
                    }else{
						if($width_old_edit!==$this->image_width_long_multi||$height_old_edit!==$this->image_height_long_multi){
							$this->error("上传图片大小错误，宽需为{$this->image_width_long_multi}px，高需为{$this->image_height_long_multi}px");
						}	
					}
				}
				if(!$high_image_name){
					if (!$find_soft['high_image_url']) {
                        $this->error("请上传高分图片");
                    }else{
						if($width_high_edit!==$this->high_image_width_multi||$height_high_edit!==$this->high_image_height_multi){
							$this->error("上传图片大小错误，宽需为{$this->high_image_width_multi}px，高需为{$this->high_image_height_multi}px");
						}
					}
				}
				if(!$low_image_name){
					if (!$find_soft['low_image_url']) {
                        $this->error("请上传低分图片");
                    }else{
						if($width_low_edit!==$this->low_image_width_multi||$height_low_edit!==$this->low_image_height_multi){
							$this->error("上传图片大小错误，宽需为{$this->low_image_width_multi}px，高需为{$this->low_image_height_multi}px");
						}
					}
				}
            }
            
			if ( $extent_type==2 ) {	
				//单排必须要有三张图片
				$find_soft = $model->where("id={$id}")->find();
                if (!$image_name) {
                    if (!$find_soft['image_url']){
                        $this->error("请上传图片");
                    }else{
						if($width_old_edit!==$this->image_width_long||$height_old_edit!==$this->image_height_long)	{
							$this->error("上传图片大小错误，宽需为{$this->image_width_long}px，高需为{$this->image_height_long}px");
						}
					}
				}
				if (!$high_image_name) {
					if (!$find_soft['high_image_url']){
                        $this->error("请上传高分图片");
                    }else{
						if($width_high_edit!==$this->high_image_width_long||$height_high_edit!==$this->high_image_height_long){
							$this->error("上传图片大小错误，宽需为{$this->high_image_width_long}px，高需为{$this->high_image_height_long}px");
						}
					}
				}
				if (!$low_image_name ) {
					if (!$find_soft['low_image_url']){
                        $this->error("请上传低分图片");
                    }else{
						if($width_low_edit!==$this->low_image_width_long||$height_low_edit!==$this->low_image_height_long){
							$this->error("上传图片大小错误，宽需为{$this->low_image_width_long}px，高需为{$this->low_image_height_long}px");
						}
					}
                }
            }
			//V6.0去掉双排  $extent_type == 3 
            if ($image_name && ( ($extent_type==2&&empty($_POST['resource_id'])) || ($extent_type ==5&&$_POST['position']=1) )) {
                // 将图片存储起来
                $folder = "/img/" . date("Ym/d/");
                $this->mkDirs(UPLOAD_PATH . $folder);
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
                // 如果是单排
                if ($extent_type == 2) { // 如果是单排
                    if ($width != $this->image_width_long || $height != $this->image_height_long)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_long}px，高需为{$this->image_height_long}px");
                }
                /* else if ($extent_type == 3) { // 如果是双排  v6.0去掉
                    if ($width != $this->image_width_short || $height != $this->image_height_short)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_short}px，高需为{$this->image_height_short}px");
                }*/ else { // 如果是多排专题页面
					if ($width != $this->image_width_long_multi || $height != $this->image_height_long_multi)
                        $this->error("上传图片大小错误，宽需为{$this->image_width_long_multi}px，高需为{$this->image_height_long_multi}px");
                }
                $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
                $img_path = UPLOAD_PATH . $relative_path;
                $ret = move_uploaded_file($_FILES['image_url']['tmp_name'], $img_path);
                $map['image_url'] = $relative_path;
            }
			include_once SERVER_ROOT. '/tools/functions.php';
			// 将图片存储起来
            $folder = "/img/" . date("Ym/d/");
            $this->mkDirs(UPLOAD_PATH . $folder);
			//V6.0添加高分图片和低分图片  单排必填  多排选填
			if ($high_image_name && ( ($extent_type==2&&empty($_POST['resource_id']))  || $extent_type ==5 )) {
                // 取得图片后缀
                $suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['high_image_url']['name'],$matches_high);
                if ($matches_high) {
                    $suffix_high = $matches_high[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr = getimagesize($_FILES['high_image_url']['tmp_name']);
                if (!$img_info_arr) {
                    $this->error('上传图片出错！');
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                // 如果是单排
                if ($extent_type == 2) { 
                    if ($width != $this->high_image_width_long || $height != $this->high_image_height_long)
                        $this->error("上传图片大小错误，宽需为{$this->high_image_width_long}px，高需为{$this->high_image_height_long}px");
                }else { // 如果是多排专题页面
                    if ($width != $this->high_image_width_multi || $height != $this->high_image_height_multi)
                        $this->error("上传图片大小错误，宽需为{$this->high_image_width_multi}px，高需为{$this->high_image_height_multi}px");
                }
                $relative_path_high = $folder . time() . 'high_' . rand(1000,9999) . $suffix_high;
				$relative_path_high_80 = $folder . time() . 'high_80_' . rand(1000,9999) . $suffix_high;
                $img_path_high = UPLOAD_PATH . $relative_path_high;
				$img_path_high_80 = UPLOAD_PATH . $relative_path_high_80;
                $ret = move_uploaded_file($_FILES['high_image_url']['tmp_name'], $img_path_high);
                $map['high_image_url'] = $relative_path_high;
				$high_80=image_strip_size($img_path_high,$img_path_high_80,80*1024);
				if($high_80){
					$map['high_image_url_80'] = $relative_path_high_80;
				}
            }
			if ($low_image_name && ( ($extent_type==2&&empty($_POST['resource_id']))  || $extent_type ==5 )) {
                // 取得图片后缀
                $suffix_low = preg_match("/\.(jpg|png)$/", $_FILES['low_image_url']['name'],$matches_low);
                if ($matches_low) {
                    $suffix_low = $matches_low[0];
                } else {
                    $this->error('上传图片格式错误！');
                }
                // 判断图片长和宽
                $img_info_arr = getimagesize($_FILES['low_image_url']['tmp_name']);
                if (!$img_info_arr) {
                    $this->error('上传图片出错！');
                }
                $width = $img_info_arr[0];
                $height = $img_info_arr[1];
                // 如果是单排
                if ($extent_type == 2) { 
                    if ($width != $this->low_image_width_long || $height != $this->low_image_height_long)
                        $this->error("上传图片大小错误，宽需为{$this->low_image_width_long}px，高需为{$this->low_image_height_long}px");
                }else { // 如果是多排专题页面
                    if ($width != $this->low_image_width_multi || $height != $this->low_image_height_multi)
                        $this->error("上传图片大小错误，宽需为{$this->low_image_width_multi}px，高需为{$this->low_image_height_multi}px");
                }
                $relative_path_low = $folder . time() . 'low_' . rand(1000,9999) . $suffix_low;
				$relative_path_low_40 = $folder . time() . 'low_40_' . rand(1000,9999) . $suffix_low;
                $img_path_low = UPLOAD_PATH . $relative_path_low;
				$img_path_low_40 = UPLOAD_PATH . $relative_path_low_40;
                $ret = move_uploaded_file($_FILES['low_image_url']['tmp_name'], $img_path_low);
                $map['low_image_url'] = $relative_path_low;
				$low_40=image_strip_size($img_path_low,$img_path_low_40,40*1024);
				if($low_40)	{
					$map['low_image_url_40'] = $relative_path_low_40;
				}
            }
			//单排的gif图片
			if(($extent_type==2&&empty($_POST['resource_id']))){
				if ($gif_image_name) {
					// 取得图片后缀
					$suffix_gif = preg_match("/\.(gif)$/", $_FILES['gif_image_url']['name'],$matches_gif);
					if ($matches_gif) {
						$suffix_gif = $matches_gif[0];
					} else {
						$this->error('上传图片格式错误！');
					}
					// 判断图片长和宽
					$img_info_arr_gif = getimagesize($_FILES['gif_image_url']['tmp_name']);
					if (!$img_info_arr_gif) {
						$this->error('上传图片出错！');
					}
					$width_gif = $img_info_arr_gif[0];
					$height_gif = $img_info_arr_gif[1];
				   
					if ($width_gif != $this->gif_width || $height_gif != $this->gif_height)
						$this->error("上传图片大小错误，宽需为{$this->gif_width}px，高需为{$this->gif_height}px");
					 
					$relative_path_gif = $folder . time() . 'gif_' . rand(1000,9999) . $suffix_gif;
					$img_path_gif = UPLOAD_PATH . $relative_path_gif;
					$ret = move_uploaded_file($_FILES['gif_image_url']['tmp_name'], $img_path_gif);
					$map['gif_image_url'] = $relative_path_gif;
				}
				if ($gif_image_name_62) {
					// 取得图片后缀
					$suffix_gif_62 = preg_match("/\.(gif)$/", $_FILES['gif_image_url_62']['name'],$matches_gif_62);
					if ($matches_gif_62) {
						$suffix_gif_62 = $matches_gif_62[0];
					} else {
						$this->error('上传图片格式错误！');
					}
					// 判断图片长和宽
					$img_info_arr_gif_62 = getimagesize($_FILES['gif_image_url_62']['tmp_name']);
					if (!$img_info_arr_gif_62) {
						$this->error('上传图片出错！');
					}
					$width_gif_62 = $img_info_arr_gif_62[0];
					$height_gif_62 = $img_info_arr_gif_62[1];
				   
					if ($width_gif_62 != $this->gif_width_62 || $height_gif_62 != $this->gif_height_62)
						$this->error("上传图片大小错误，宽需为{$this->gif_width_62}px，高需为{$this->gif_height_62}px");
					 
					$relative_path_gif_62 = $folder . time() . 'gif_62_' . rand(1000,9999) . $suffix_gif_62;
					$img_path_gif_62 = UPLOAD_PATH . $relative_path_gif_62;
					$ret = move_uploaded_file($_FILES['gif_image_url_62']['tmp_name'], $img_path_gif_62);
					$map['gif_image_url_62'] = $relative_path_gif_62;
				}
			}
            if ($extent_type == 5) {
                if (isset($description)&&$_POST['position']==2) {
                    if (mb_strlen($description, 'utf-8') > 100 || mb_strlen($description, 'utf-8') < 30) {
                        $this->error("列表介绍需在30～100字之间");
                    }
                    $map['description'] = $description;
                } else {
                    $map['description'] = '';
                }
            }
            // 如果是多软件-软件位，还有默认展示字段
            if ($extent_type == 4) {
                $default_display = $_POST['default_display'] ? 1 : 0;
                $map['default_display'] = $default_display;
            }
			//V6.4 新增区间类型：包名、搜索词、人气初始值、随机最小值、随机最大值
            if(in_array($extent_type, array(16,17,28,32,35,38,39))){
			//if ($extent_type == 16||$extent_type == 17||$extent_type == 28||$extent_type==32) {
				$new_pkg = trim($_POST['new_package']);
				if($new_pkg){
					$map['package'] = $new_pkg;
					$map['package_name'] = trim($_POST['package_name']);
				}else{
					$this->error("软件包名不能为空");
				}
			}
			if ($extent_type == 16||$extent_type == 17) {
				if($extent_type == 16){
					$search_key = $_POST['search_keys'];
					if($_POST['search_keys']){
						//搜索词 可以填写多个，以逗号隔开 但是不能超过30个
						$keys_arr = explode(',',$_POST['search_keys']);
						if(count($keys_arr)>30)	{
							$this->error("搜索词不能超过30个");
						}else{
							$map['search_keys'] = $_POST['search_keys'];
						}
					}else{
						$this->error("搜索词不能为空");
					}
				}
			}
			
			if($extent_type == 16||$extent_type == 17||$extent_type==20||$extent_type==21){
				//人气值 都是大于0的整数
				$matches="/^[1-9]\d*$/";
				if($_POST['init_val']){
					if(!preg_match($matches,$_POST['init_val'])){
						$this->error("人气值的初始值为正整数");
					}else{	
						$map['init_val'] = $_POST['init_val'];
					}
				}else{
					$this->error("人气值的初始值不能为空");
				}
				if($_POST['random_start']){
					if(!preg_match($matches,$_POST['random_start'])){
						$this->error("人气值的随机开始值为正整数");
					}else{	
						$map['random_start'] = $_POST['random_start'];
					}
				}else{
					$this->error("人气值的随机开始值不能为空");
				}
				if($_POST['random_end']){
					if(!preg_match($matches,$_POST['random_end'])){
						$this->error("人气值的随机结束值为正整数");
					}else{	
						$map['random_end'] = $_POST['random_end'];
					}
				}else{
					$this->error("人气值的随机结束值不能为空");
				}
			}
			if ($extent_type == 18) {
				if($_POST['order_id'])	{
					$map['order_id'] = $_POST['order_id'];
				}else{
					$this->error("预约名称不能为空");
				}	
				$map['order_type'] = $_POST['order_type'];
			}
			if($extent_type==28 || ($find['belong_page_type'] != 'fixed_resource_channel' && empty($from_action)) ) {
                if ($extent_type != 19&&$extent_type != 20&&$extent_type != 21&&$extent_type != 38&&$extent_type != 39&&$extent_type != 40) {
    				// 概率
    				$prob = $_POST['prob'];
    				if (!$prob) {
    					$this->error("概率不能为空");
    				}
    				if (!preg_match("/^\d+$/", $prob)) {
    					$this->error("显示概率应为整数");
    				}
    				if ($prob > 100) {
    					$this->error("显示概率不能大于100");
    				}
    				$map['prob'] = $prob;
    			}
            }
			$this->check_info($extent_type,$map,$folder,2);
            //v6.4.3
            if($extent_type==22||$extent_type==23){
                $title = $_POST['title'];
                if (!$title) {
                    $this->error("模块名称不能为空");
                }
                if (mb_strlen($title, 'utf-8') > $text_length) {
                    $this->error("模块名称不能超过{$text_length}个字");
                }
                $map['title'] = $title;
                if($extent_type==22 && $_FILES['image_url']['tmp_name']){
                    $map['image_url'] = $this->check_imagesize('image_url',$extent_type,'图片','jpg|png|gif');
                }
            }
            
            if($find['belong_page_type'] == 'fixed_resource_channel' || !empty($from_action)) {
            	if($extent_type==24 || $extent_type==26 || $extent_type==29 || $extent_type==2){
            		if(($extent_type!=29 || empty($_POST['is_dev'])) && $extent_type!=28){
            			if (empty($_POST['title'])) {
            				$this->error("标题不能为空");
            			}
            			$map['title'] = $_POST['title'];
            		}
            		if(!empty($_POST['is_dev'])){
            			$map['is_dev'] = 1;
            			if($extent_type==29){
            				$map['video_url'] = '';
            				$map['video_pic'] = '';
            				$map['parameter_field'] = json_encode(array('video_id'=>$_POST['video_id']));
            			}
            		}else{
            			$map['is_dev'] = 0;
            			if($extent_type==24){
            				if($_FILES['image_url']['tmp_name']){
            					$map['image_url'] = $this->check_imagesize('image_url',$extent_type,'图片','jpg|png|gif');
            				}
            			}elseif($extent_type==26){
            				if($_FILES['image_url']['tmp_name']){
            					$map['image_url'] = $this->check_imagesize('image_url',$extent_type,'图片1','jpg|png|gif');
            				}
            				if($_FILES['high_image_url']['tmp_name']){
            					$map['high_image_url'] = $this->check_imagesize('high_image_url',$extent_type,'图片2');
            				}
            				if($_FILES['low_image_url']['tmp_name']){
            					$map['low_image_url'] = $this->check_imagesize('low_image_url',$extent_type,'图片3');
            				}
            			}elseif($extent_type==29){
            				$map['video_url'] = $_POST['video_url'];
            				//表单中ajax获取的图片链接带域名，入库前要去掉
            				$map['video_pic'] = str_replace(ATTACHMENT_HOST, '', $_POST['video_pic']);
            				$map['parameter_field'] = json_encode(array('video_id'=>$_POST['video_id']));
            			}
            		}
            	}
            }else {
            	if($extent_type==24||$extent_type==26||$extent_type==25||$extent_type==27|| $extent_type==29||$extent_type==30||$extent_type==31||$extent_type==33){
            		if(trim($_POST['new_package'])) {
            			$map['package_643'] = trim($_POST['new_package']);
            			$map['package_name'] = trim($_POST['package_name']);
            		}else{
            			$check_pack = false;
            			if($extent_type!=27&&$extent_type!=31&&$extent_type!=33) $check_pack = true;
            			if($check_pack){
            				$this->error("软件包名不能为空");
            			}
            		}
            		if(!empty($_POST['resource_id'])){
            			$map['res_id'] = $_POST['resource_id'];
            		}elseif(!empty($_POST['resource_id_29'])){
            			$map['res_id'] = $_POST['resource_id_29'];
            		}else{
            			if($extent_type != 28) {
            				$title = $_POST['title'];
            				if (!$title) {
            					$this->error("标题不能为空");
            				}
            				if (mb_strlen($title, 'utf-8') > $text_length) {
            					$this->error("标题最多{$text_length}个字符");
            				}
            				$map['title'] = $title;
            			}
            			if($extent_type != 29){
            				if($_FILES['image_url']['tmp_name']&&$extent_type!=27){
								$image_type = 'jpg|png|gif';
								if($extent_type==25 || $extent_type==31) $image_type = 'jpg|png';
            					$map['image_url'] = $this->check_imagesize('image_url',$extent_type,'图片',$image_type);
            				}
            				if($extent_type==26){
            					if($_FILES['high_image_url']['tmp_name']){
            						$map['high_image_url'] = $this->check_imagesize('high_image_url',$extent_type,'图片2');
            					}
            					if($_FILES['low_image_url']['tmp_name']){
            						$map['low_image_url'] = $this->check_imagesize('low_image_url',$extent_type,'图片3');
            					}
            				}
            			}
            		}
            	}
            }
            if($_FILES['image_url']['name']&&$extent_type == 40){
                $map['image_url'] = $this->check_imagesize('image_url',$extent_type);
            }
            if($extent_type == 28||$extent_type == 38)
            $pkgs = $model->table('sj_flexible_extent_soft')->where(array('extent_id'=>$extent_id,'status'=>1,'end_at' => array('gt',time()),'id'=>array('neq',$id)))->field('package')->select();
            $msg = '场景卡片分区不能添加相同软件';
            if($extent_type == 38) $msg = '多行软件分区不能添加相同软件';
            foreach($pkgs as $pkg){
                if($new_pkg == $pkg['package']){
                    $this->error($msg);
                }
            }
            if($extent_type==28){

                $des = trim($_POST['s_description']);
                $len = mb_strlen($des, 'utf-8');
                if($len > 40){
                    $this->error('一句话点评最多40个字符');
                }
                $map['description'] = $des;
            }
            
            if($extent_type == 28 || ($find['belong_page_type'] != 'fixed_resource_channel' && empty($from_action)) ) {
                // 开始和结束时间
                $map['start_at'] = strtotime($_POST['start_at']);
                $map['end_at'] = strtotime($_POST['end_at']);
                if (!$map['start_at']) {
                    $this->error("开始时间不能为空");
                }
                if (!$map['end_at']) {
                    $this->error("结束时间不能为空");
                }
                if ($map['start_at'] > $map['end_at']) {
                    $this->error("开始时间不能大于结束时间");
                }
                //V6.4.4搜索结果页
                //处理上传指定搜索词csv
                $filename_search_key=$_FILES['upload_search_keys']['tmp_name'];
                if($find['belong_page_type']=='search_result_page' && !$filename_search_key && !trim($_POST['have_keys_url'])&&!in_array($_POST['search_type'],array(2,3,4))){
                    $this->error("请上传指定搜索词csv");
                }
                if($filename_search_key&&!$_POST['search_keys_count']) {
                    $this -> error("选择好的文件请点击上传才有效");
                }
                $csv_arr = array();
                //同一个区间下不能包含相同搜索词
                if($_POST['search_keys_count']&&$_POST['search_keys_url']){
                    $map['search_keys_count'] = $_POST['search_keys_count'];
                    $map['search_keys_url'] = $_POST['search_keys_url'];
                }
    			//已过期的信息复制上线判断
    			if($_POST['life']==1){
    			  if(strtotime($_POST['end_at'])<time()) {
    			    $this->error("您修改的复制上线的日期还是无效日期");
    			  }
    			}
    			//灵活运营的排期冲突不要了  2016-09-21 产品需求
                // 检查排期冲突
    			$map['life']=$_POST['life'];
                // 其他默认
                $map['status'] = 1;
                $this->get_csv_data($map);
                //屏蔽软件上排期时报警需求 新增  yuesai
                $AdSearch = D("Sj.AdSearch");
    			if($_POST['package']){
    				$package_check =$map['package'];
    				$shield_error=$AdSearch->check_shield($_POST['package'],$map['start_at'],$map['end_at']);
    				$shield_error.=$AdSearch->check_shield_old($_POST['package'],$extent_id,0,'sj_flexible_extent');
    				if($shield_error){
    					$this -> error($shield_error);
    				}
    			}
    			//v6.4.5
                //单软件(视频)||多排新样式
                if($extent_type==5||$extent_type==29||$extent_type==31||$extent_type==32||$extent_type==33||$extent_type == 35||$extent_type == 36||$extent_type == 37||$extent_type == 38||$extent_type == 39||$extent_type == 40){
    				if($extent_type==29&&$_POST['is_tag']==0){
    					$this->clear_content($map);
    					$map['content_type'] = 0;
    				}
                    ContentTypeModel::save_flexible_data($_POST,$map,$extent_type);
                }
            }
            if($extent_type == 37){
                $map['activity_id'] = $_POST['activity_id'];
            }
            if($extent_type == 38){
                $map['content_type'] = 1;
            }
            if($extent_type == 36||$extent_type == 40) $map['content_type'] = 4;
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            if(in_array($_SESSION['admin']['admin_user_name'], array('刘铄麟','范国良1','杨波'))){
            	$map['update_at'] = $_POST['update_at']?strtotime($_POST['update_at']):time();
            }else{
            	$map['update_at'] = time();
            }
            
            if($extent_type == 24|| $extent_type == 26||$extent_type == 29 || $extent_type == 2){
                $map['user_tend'] = $_POST['user_tend'];
                $map['cont_src'] = $_POST['con_source'];
                $map['cont_level'] = $_POST['content_level'];
                $map['cont_quality'] = $_POST['content_nature'];
                $map['tagstatus'] = $_POST['content_tags'] ? 1 : 0;
                $cont_tags = $_POST['content_tags'];
            }
            $map['soft_type'] = $_POST['soft_type'] ? $_POST['soft_type'] : 0;
            // 记录一下日志
            $log = $this -> logcheck(array('id' => $id),'sj_flexible_extent_soft',$map, $model);
			//已过期的信息复制上线  添加
			if($_POST['life']==1){
				$select = $model->table('sj_flexible_extent_soft')->where(array('id'=>$id))->select();
				unset($map['life']);
				$map['extent_id']=$select[0]['extent_id'];
				$map['create_at']=time();
				if($_FILES['image_url']['name']==""){
					$map['image_url']=$select[0]['image_url'];
				}
				if($_FILES['high_image_url']['name']==""){
					$map['high_image_url']=$select[0]['high_image_url']; 
					$map['high_image_url_80']=$select[0]['high_image_url_80']; 
				}
				if($_FILES['low_image_url']['name']==""){
					$map['low_image_url']=$select[0]['low_image_url']; 
					$map['low_image_url_40']=$select[0]['low_image_url_40'];
				}
				if($_FILES['gif_image_url']['name']==""){
					$map['gif_image_url']=$select[0]['gif_image_url']; 
				}
				$ret = $model->table('sj_flexible_extent_soft')->add($map);
				if ($ret || $ret === 0) {
					$this->assign('jumpUrl', '__URL__/list_soft/extent_id/'. $soft['extent_id']."/p/{$_POST['p']}");
					$this->writelog("推荐管理-灵活运营样式-复制上线了title为{$map['title']}的记录：{$log}","sj_flexible_extent_soft",$ret,__ACTION__ ,"","add");
					$this->success('复制上线成功');
				} else {
					$this->error('复制上线失败');
				}
			}else{
				// 添加
				unset($map['life']);
				$ret = $model->table('sj_flexible_extent_soft')->where(array('id'=>$id))->save($map);

                //添加内容标签映射数据
                if(in_array($extent_type,array(2,24,26,29))){
                    $tag_data = array();
                    $tag_data['pkgname'] = trim($_POST['new_package']);
                    $tag_data['eid'] = $id;
                    $tag_data['tags'] = $cont_tags;
                    //先删除原有映射内容
                    $tag_res = M('')->table('cont_tags_content')->where(array('eid'=>$tag_data['eid']))->delete();
                    if($tag_res){
                        $this->writelog('内容标签映射表删除关联数据，eid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','delete');
                    }
                    //添加新数据
                    if($cont_tags){
                        $cont_model = D('Sj.ContAttribute');
                        $tag_result = $cont_model->add_cont_attrribute($tag_data);
                        if($tag_result){
                            $this->writelog('内容标签映射表添加关联数据，cid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','add');
                        }
                    } 
                }
				if ($ret || $ret === 0) {
					if(!empty($from_action)) {
						$extent_soft_data = $_POST['data'];
						M('')->table('sj_extent_soft_v2')->where(array('id'=>$_POST['id']))->save($extent_soft_data);
 						$this->writelog("市场软件运营推荐- 市场首页软件列表:编辑了软件[".$_POST['package_ext']."]".$_POST['log_msg'],'sj_extent_soft_v2',$id,__ACTION__ ,"","edit");
 						// 检查导入的区间中有没有软件数大于区间位置数的，有的话要发邮件提醒运营
						$check_extent = array(
							$_POST['extent_v2_id']	=>	array(
								'start_at'	=>	$map['start_at'],
								'end_at'	=>	$map['end_at']
							)
						);
 						$this->send_size_notice_email($check_extent);
						$this->success('编辑成功');
					}
					$this->writelog("推荐管理-灵活运营样式-编辑了id为{$id}的记录：{$log}","sj_flexible_extent_soft",$id,__ACTION__ ,"","edit");
					$this->success('编辑成功');
				} else {
					$this->error('编辑失败');
				}
			}
            
        } else {
            $id = $_GET['id'];
            $where = array(
                'id' => $id
            );
            $model = M('flexible_extent_soft');
            $soft = $model->where($where)->find();
            $extent_id = $soft['extent_id'];
            // 根据extent_id查找extent_type
            $where = array('extent_id' => $extent_id, 'status' => 1);
            $find = $model->table('sj_flexible_extent')->where($where)->find();
            $extent_type = $find['extent_type'];
            $pid = $find['pid'];
            if(in_array($extent_type, array(5,29,31,32,33,35,36,37,38,39,40))){
            //if($extent_type==5||$extent_type==29||$extent_type==31||$extent_type==32||$extent_type==33||$extent_type==35||$extent_type==36){
            	ContentTypeModel::get_flexible_param($soft,$extent_type);
            }
            if($extent_type==19){
            	$text_length = 6;
            }elseif($extent_type==22||$extent_type==23){
            	$text_length = 8;
            }elseif($extent_type==24){
            	$text_length = 25;
            }elseif($extent_type==20||$extent_type==21||$extent_type==25||$extent_type==27||$extent_type==29){
            	$text_length = 20;
            }elseif($extent_type==26){
            	$text_length = 1000;
            }elseif($extent_type==30){
            	$text_length = 15;
            }elseif($extent_type==5&&$soft['position']==1){
            	//V6.4.5多排新样式
            	$text_length = 15;
            }elseif($extent_type==2){
            	$text_length = 10;
            }
            //资源库的情况
            if($find['belong_page_type'] == 'fixed_resource_channel' ) {
            	$id = $_GET['id'];
            	$list = $model->table('sj_flexible_extent_soft')->where(array('id'=>$id))->find();
            	$this->assign('resource_type', $list['resource_type']);
            	$this->assign('text_length', $text_length);
            	$parameter_field = json_decode($list['parameter_field'], true);
            	$video_id = $parameter_field['video_id'];
            	$video_ret = M('')->table('sj_soft_extra')->where("id={$video_id}")->find();
                
                $soft_type = content_soft_type_selecttag($list['soft_type']);
                $this->assign('soft_type',$soft_type);

                //获取内容属性，标签
                $cont_model = D('Sj.ContAttribute');
                $content_xz = $cont_model -> get_cont_quality($list['cont_quality'],'radio');
                $content_zl = $cont_model -> get_cont_level($list['cont_level'],'radio');
                $config = array(
                    'key'=>'CONTENT_SOURCE',
                    'type'=>'radio',
                    'tag_id'=>'con_source',
                    'tag_name'=>'con_source',
                    'tag_tip'=>'请选择内容来源',
                    'default'=>$list['cont_src'],

                );
                $con_source = $cont_model -> get_cont_src($config); 
                //用户倾向
                $config_user = array(
                    'key'=>'USER_TEND',
                    'type'=>'radio',
                    'tag_id'=>'user_tend',
                    'tag_name'=>'user_tend',
                    'tag_tip'=>'全部',
                    'default'=>$list['user_tend'],
                );
                $user_tend = $cont_model -> get_user_tend($config_user);     
                //内容标签
                $tag_result = M('') -> table('cont_tags_content')->where(array('eid'=>$id))->select();
                $second_tag = array();
                $three_rank = array();
                foreach ($tag_result as $tag_key => $tag_value) {
                    array_push($second_tag,$tag_value['second_tagid']);
                    if($tag_value['three_tagid']) array_push($second_tag,$tag_value['three_tagid']);
                    if($tag_value['tagidlevel']){
                        $thlrank = $tag_value['three_tagid'].'_'.$tag_value['tagidlevel'];
                        array_push($three_rank, $thlrank);
                    }  
                }
                $second_tag = array_unique($second_tag);
                $content_tag = $cont_model->get_cont_tags($second_tag,$three_rank);
                $this->assign('content_tag',$content_tag);
                $this->assign('content_xz', $content_xz);
                $this->assign('content_zl', $content_zl);
                $this->assign('con_source',$con_source); 
                $this->assign('user_tend',$user_tend);


            	$list['video_id']  = $video_id;
            	if($_GET['show_video']==1){
            		//播放采用未转码后的视频地址
            		$list['video_url'] = $video_ret['video_url'];
            		$this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
            		$this->assign('list', $list);
            		$this->display('video_show');die;
            	}else{
            		$this->assign('admin_user_name', $_SESSION['admin']['admin_user_name']);
            		$this->assign('domain_url', ATTACHMENT_HOST);
            		$this->assign('list', $list);
            		$this->display('edit_res_soft');die;
            	}
            }
            
            $this->assign('text_length',$text_length);
            $this->assign('belong_page_type',$find['belong_page_type']);
            $this->assign('final_content_type',ContentTypeModel::get_final_content_type($find['belong_page_type']));
            // 给个默认值，要不然javascript那边可能会出错
            $soft['general_page_type'] = 0;
            $soft['page_name'] = "";
            // 获得活动名称，专题名称，页面名称
            $content_type = $soft['content_type'];
            if ($content_type == 1) {
                // 软件
            }else if ($content_type == 2) {
                // 活动名称
                $soft['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($soft['activity_id']);
            } else if ($content_type == 3) {
                // 专题名称
                $soft['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($soft['feature_id']);
            } else if ($content_type == 4) {
                // 页面类型
                $soft['general_page_type'] = ContentTypeModel::getGeneralPageType($soft['page_type']);

                // 页面名称
                $soft['page_name'] = ContentTypeModel::convertPageType2PageName($soft['page_type']);
            }
			//合作形式
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList($soft['co_type']);
			$this->assign('typelist',$typelist);
			$this->assign('soft', $soft);
            $this->assign('extent_type', $extent_type);
			//图片大小
			$this->assign('image_width_short',$this->image_width_short);
			$this->assign('image_height_short',$this->image_height_short);
			$this->assign('image_width_long_multi',$this->image_width_long_multi);
			$this->assign('image_height_long_multi',$this->image_height_long_multi);
			
			$this->assign('high_image_width_multi',$this->high_image_width_multi);
			$this->assign('high_image_height_multi',$this->high_image_height_multi);
			$this->assign('low_image_width_multi',$this->low_image_width_multi);
			$this->assign('low_image_height_multi',$this->low_image_height_multi);
			
			$this->assign('gif_width',$this->gif_width);
			$this->assign('gif_height',$this->gif_height);
			$this->assign('gif_width_62',$this->gif_width_62);
			$this->assign('gif_height_62',$this->gif_height_62);
			//底部图片文字描述个数
			$this->assign('pic_bottom_des',$this->pic_bottom_des);
			$this->assign('image_height',$this->image_height);
			$this->assign('image_width',$this->image_width);
            //v6.4.3之后用这个
            $this->assign('image_size',$this->image_size[$extent_type]);
            $this->assign('pid',$pid);
			$this->assign('search_type',$this->search_type);
            $this->display();
        }
    }
    
    public function del_soft() {
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$map = array(
            'update_at' => time(),
			'status' => 0
		);
		$model = M('flexible_extent_soft');
		$model->where($where)->save($map);
		$this->writelog("推荐管理-灵活运营样式-删除了id为[$id]的记录","sj_flexible_extent_soft",$id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}
    
     // 返回冲突id，否则返回0
     // 冲突逻辑：查找该软件所在区间的页面下所有区间是否有与该软件有排期冲突的软件
    private function check_conflict($record, $id = 0) {
        $content_type = $record['content_type'];
        $start_at = $record['start_at'];
        $end_at = $record['end_at'];
        $extent_id = $record['extent_id'];
		$life=$record['life'];
        $model = M();
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
        } else if ($content_type == 5) {
            // 查找网页
            $content_key = 'website';
            $content_value = $record['website'];
        } else {
            return false;
        }
        // 区间表、区间软件表
        $M_extent_table = 'flexible_extent';
        $M_extent_soft_table = 'flexible_extent_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        
        // 如果没有extent_id，根据软件id查找其所在extent_id
        if (!$extent_id) {
            if (!$id) {
                $this->error("出错啦");
            }
            $find = $extent_soft_model->where(array('id'=>$id))->find();
            $extent_id = $find['extent_id'];
        }
        
        // 根据extent_id查找其所在的平台和页面，因为检查冲突时，是要检查该区间所在页面（某个平台）下的所有区间
        $where = array(
            'extent_id' => $extent_id,
        );
        $find = $extent_model->where($where)->find();
        $pid = $find['pid'];
        $belong_page_type = $find['belong_page_type'];
        $json_a = json_decode($record['parameter_field'],true);
 
        // 构造sql
        $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.{$content_key} as content_key,{$extent_soft_table}.parameter_field as parameter_field, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
        $sql_from = " from {$extent_soft_table} left join {$extent_table}";
        $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
        $sql_where = " where {$extent_soft_table}.{$content_key}='{$content_value}' and {$extent_soft_table}.start_at <= {$end_at} and {$extent_soft_table}.end_at >= {$start_at} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.belong_page_type='{$belong_page_type}' and {$extent_table}.pid='{$pid}'";
        // 如果有传id过来，说明是编辑，这时要排除此id
        $sql_where_except = "";
        if ($id&&$life!=1) {
            $except_id = escape_string($id);
            // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
            $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
        }
        // 将select、from、on、where、except拼接起来
        $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
        // 执行sql
        $db_records = $extent_soft_model->query($sql);
        $id = array();
        if (!empty($db_records)) {
            foreach ($db_records as $value) {
				//解析一下json数据 如果参数值相同  页面相同就不能添加 
				$json_arr = json_decode($value["parameter_field"],true);
				$diff = array_diff($json_arr, $json_a);
				if(!$diff)
				{
					$id[] = $value['id'];
				}
            }
			if($id)
			{
				return $id;
			}
        } else {
            return 0;
        }
    }
    
    // 显示指定字段的全部内容
    public function show_content() {
        $extent_id = $_GET['extent_id'];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $model = M('flexible_extent');
        $find = $model->where($where)->find();
        $content = '';
        if ($find) {
            $content = $find['display_description'];
        }
        $this->assign('content', $content);
        $this->display('show_content');
    }
    
    function shorten_sentence($sentence, $len = 10) {
        $sen_len = mb_strlen($sentence, 'utf-8');
        if ($sen_len > $len) {
            $sentence = mb_substr($sentence, 0, $len - 2, 'utf-8') . '...';
        }
        return $sentence;
    }
	private function clear_content(&$map) {
        $map['activity_id'] = 0;
        $map['feature_id'] = 0;
        $map['page_type'] = '';
        $map['website'] = '';
        $map['page_flag'] = '';
        $map['page_id1'] = 0;
        $map['page_id2'] = 0;
		$map['parameter_field'] ='';
    }
	
	//V6.4.1
	function check_info($extent_type,&$map,$folder,$type=1){
		if($extent_type == 19||$extent_type == 20||$extent_type == 21){
			$title = $_POST['i_title'];
			if (!$title) {
				$this->error("图标名称不能为空");
			}
			$len = mb_strlen($title, 'utf-8');
			if($extent_type == 19){
				$max_len = 6;
			}else{
				$max_len = 20;
			}
			if($len > $max_len||$len < 2) {
				$this->error("图标名称2-{$max_len}个字符");
			}
			$map['title'] = $title;
			if($type==1&&!$_FILES['i_image_url']['name']){
				$this->error('请上传图标素材');
			}

			if($_FILES['i_image_url']['name']){
				// 取得图片后缀
				$suffix_low = preg_match("/\.(jpg|png|gif)$/", $_FILES['i_image_url']['name'],$matches_low);
				if ($matches_low) {
					$suffix_low = $matches_low[0];
				} else {
					$this->error('上传图标素材格式错误！');
				}
				// 判断图片长和宽
				$img_info_arr = getimagesize($_FILES['i_image_url']['tmp_name']);
				if (!$img_info_arr) {
					$this->error('上传图标素材出错！');
				}
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				 if ($width != $this->image_width || $height != $this->image_height)
                        $this->error("上传图标素材大小错误，宽需为{$this->image_width}px，高需为{$this->image_width}px");
				$image_path = $folder . time() . rand(1000,9999) . $suffix_low;
                $img_path = UPLOAD_PATH . $image_path;
                $ret = move_uploaded_file($_FILES['i_image_url']['tmp_name'], $img_path);
                $map['image_url'] = $image_path;
			}
			if($_FILES['gif_image_url1']['name']){
				// 取得图片后缀
				$suffix_low = preg_match("/\.(gif)$/", $_FILES['gif_image_url1']['name'],$matches_low);
				if ($matches_low) {
					$suffix_low = $matches_low[0];
				} else {
					$this->error('上传GIF图片格式错误！');
				}
				// 判断图片长和宽
				$img_info_arr = getimagesize($_FILES['gif_image_url1']['tmp_name']);
				if (!$img_info_arr) {
					$this->error('上传GIF图片出错！');
				}
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				if ($width != $this->image_width || $height != $this->image_height)
					$this->error("上传GIF图片大小错误，宽需为{$this->image_width}px，高需为{$this->image_width}px");
				$image_path = $folder . time() . rand(1000,9999) . $suffix_low;
				$img_path = UPLOAD_PATH . $image_path;
				$ret = move_uploaded_file($_FILES['gif_image_url1']['tmp_name'], $img_path);
				$map['gif_image_url'] = $image_path;
			}
			if($extent_type == 20){
				$des = $_POST['s_description'];
				$len = mb_strlen($des, 'utf-8');
				if($len < 2||$len > 40){
					$this->error('一句话简介为2-40个字符');
				}
				$map['description'] = $_POST['s_description'];
			}else if($extent_type == 21){//V6.5增加
				$des = $_POST['s_description'];
				$len = mb_strlen($des, 'utf-8');
				if($len < 2||$len > 8){
					$this->error('软件类型为2-8个字符');
				}
				$map['description'] = $_POST['s_description'];
			}
		}
	}

    /*
    * v6.4.4验证图片格式、尺寸，并上传
    * @access private
    * @param string $image_url  图片文件名
    * @param integer $extent_type  内容类型 用来获取图片尺寸
    * @param string $image_name  图片名字 默认'图片' 用于错误提示
    * @param string $expression  要求图片的格式 默认'jpg|png'
    * @return string  上传后图片的路径 用于字段存储
    */
    private function check_imagesize($image_url,$extent_type,$image_name='图片',$expression='jpg|png')
    {
        if(!$_FILES[$image_url]['tmp_name']){
            $this->error("请上传{$image_name}！");
        }
        // 取得图片后缀
        $suffix = preg_match("/\.({$expression})$/", $_FILES[$image_url]['name'],$matches);
        if ($matches) {
            $suffix = $matches[0];
        } else {
            $this->error("{$image_name}格式应为{$expression}！");
        }
        // 判断图片长和宽
        $img_info_arr = getimagesize($_FILES[$image_url]['tmp_name']);
        if (!$img_info_arr) {
            $this->error("上传{$image_name}出错！");
        }
        $width = $img_info_arr[0];
        $height = $img_info_arr[1];
        $image_width = $this->image_size[$extent_type][$image_url][0];
        $image_height = $this->image_size[$extent_type][$image_url][1];
        if($image_width!=0&&$image_height!=0){
            if ($width!=$image_width || $height!=$image_height){
                $this->error("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
            }
        }
        $folder = "/img/".date("Ym/d/");
        $this->mkDirs(UPLOAD_PATH . $folder);
        $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
        $img_path = UPLOAD_PATH . $relative_path;
        $ret = move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path);
        return $relative_path;
    }

    function pub_get_video(){
     	$package = $_POST['package'];
        $video_num = $_POST['video_num'];
        $is_dev = $_POST['is_dev']; 
        $where = array('package'=>$package,'status'=>1,'video_h263_url'=>array('exp'," != ''"),'check_status'=>1);
        if($video_num && $is_dev){
            $where['video_num'] = $video_num;
        }
        if($package){
            $model = M('');
            $info = $model->table('sj_soft_extra')->where($where)->field('id,video_title,video_h263_url,video_pic')->select();
            foreach($info as $k=>$v){
                $v['video_pic'] = ATTACHMENT_HOST.$v['video_pic'];
                $v['video_url'] = $v['video_h263_url'];
                $info[$k] = $v;
            }
            if($info) exit(json_encode($info));
        }
    }





    //场景化卡片批量导入
    function import_softs_AH() {
        $this->import_softs(1);
    }

    //批量导入多行软件
    function import_flexible_soft_38(){
        $this->import_softs(38);
    }

    //场景化卡片批量导入
    // 批量导入访问的页面节点
    function import_softs($type) {
        // $pid = empty($_GET['pid']) ? 1 : $_GET['pid'];
        // $this->assign('pid', $pid);
        if ($_GET['down_moban']) {
            $this->down_moban($type);
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check($content_arr, $type);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_flexible_extent_soft_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr, $type);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($result_arr as $key=>$value) {
                if ($value['flag'] == 0)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            $ret = move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            if($type == 38){
                $msg = '灵活运营样式-多行软件';
            }else{
                $msg = '灵活运营样式-场景化卡片';
            }
            $this->writelog("{$type}：批量导入了{$save_file_name}。");
            if($_GET['belong_page_type']) {
            	$jump_url = __URL__.'/reslibrary';
            }else{
            	$jump_url = __URL__;
            }
            if ($flag) {
                $this->ajaxReturn($jump_url,'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
        	$this->assign('belong_page_type', $_GET['belong_page_type']);
            $this->assign('type', $type);
            $this->display("import_softs");
        }
    }

    //下载批量导入模版
    function down_moban($type) {
        if ($type == 1) {
            $file_dir = C("ADLIST_PATH") . "scene_card_import_moban.csv";
            $file_name = '场景话卡片';
        }
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode($file_name . '批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }

    // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
            }
        }
        return $content_arr;
    }

    function import_array_convert_and_check(&$content_arr, $type) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr, $type);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr, $type);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        //屏蔽软件上排期时报警需求 新增  yuesai
        $AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_at','end_at');
        foreach($error_msg3 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr, $type = 1) {
        $result_arr = array();
        $AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        // 要添加数据的表名
        $model = M('flexible_extent_soft');
        foreach($content_arr as $key => $record) {
            // // 根据条件忽视以下值
            // if ($record['phone_dis'] == 1) {
            //     // 忽视old_phone字段（显示在旧版本中）的值
            //     unset($record['old_phone']);
            // }
            $map = array();
            // 设置默认值
            $map['status'] = 1;
            if($record['create_at'])
            {
                $map['create_at'] = strtotime($record['create_at']);
                $map['update_at'] = strtotime($record['create_at']);
                // $map['anzhi_ad_type'] = 1;
            }
            else
            {
                $map['create_at'] = time();
                $map['update_at'] = time();
            }
            $map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
            $map['extent_id'] = $record['extent_id'];
            $map['package'] = $record['package'];
            $map['start_at'] = strtotime($record['start_at']);
            $map['end_at'] = strtotime($record['end_at']);
            // $map['phone_dis'] = $record['phone_dis'];
            // 赋值，以下为非必填项，有默认值
            // $map['old_phone'] = isset($record['old_phone']) ? $record['old_phone'] : 0;
            $map['co_type'] = isset($record['co_type']) ? $record['co_type'] : 0;
            // $map['beid'] = isset($record['beid']) ? $record['beid'] : 0;
            if($type == 38){
                $map['parameter_field'] = json_encode(array('rank'=>$record['rank']));
                $map['resource_type'] = 38;
            }else{
                $map['prob'] = $record['prob'];
                //一句话推荐
                $map['description'] = isset($record['description']) ? $record['description'] : '';
                $map['resource_type'] = 28;
            }

            $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
                $result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
                $arr_shields[]=$map;
                continue;
            }

            // 添加到表中
            if ($id = $model->add($map)) {
                if($type == 38){
                    $msg = "在区间[{$record['extent_id']}]中添加了软件[{$record['package']}],开始时间为:[{$record['start_at']}],结束时间为:[{$record['end_at']}],合作形式为：[{$record['type']}],排序为{$record['rank']}的记录";
                }else{
                    $msg = "在区间[{$record['extent_id']}]中添加了软件[{$record['package']}],显示概率为[{$record['prob']}],开始时间为:[{$record['start_at']}],结束时间为:[{$record['end_at']}],合作形式为：[{$record['type']}]的记录";
                }

                $this->writelog($msg,'sj_flexible_extent_soft',$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
            } 
            // else {
                // 未知原因添加失败
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
            // }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_flexible_extent_soft')){
            $result_arr['table_name']='sj_flexible_extent_soft';
            $result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }

    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }

    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0...
    function handwriting_convert_and_check(&$content_arr, $type=1) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }

        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'extent_id'     =>  '区间ID',
            // 'general_page_type' => '频道类型',
            // 'category_name' => '频道',
            'extent_name'   =>  '区间名',
            'package'  =>   '包名',
            // 'phone_dis'  =>   '高低配区分展示',
            // 'old_phone'  =>   '旧版展示(低于V4.4.1)',
        );
        if($type == 38){
            $correct_title_arr['rank'] = '排序';
        }else{
            $correct_title_arr['prob'] = '显示概率';
        }
        $correct_title_arr['start_at'] = '开始时间(yyyy/MM/dd)';
        $correct_title_arr['end_at'] = '结束时间(yyyy/MM/dd)';
        $correct_title_arr['co_type'] = '合作形式';
        if($type == 1)
        $correct_title_arr['description'] = '一句话推荐';
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        ///print_r($content_arr);die;
        // 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 当输入为空不允许时，将其值设为false以作区别
        // $expected_words['general_page_type'] = array("" => false, "普通" => 1, "标签" => 2, "常用标签" => 3, "榜单" => 5);
        // $expected_words['phone_dis'] = array("" => false, "不做区分" => 1, "仅高配手机展示" => 2, "仅低配手机展示" => 3);
        // $expected_words['old_phone'] = array("" => 0, "是" => 1, "否" => 0);
        //合作形式
        $util = D("Sj.Util");
        $typelist = $util->get_cooperation();
        $expected_words['co_type'] =$typelist;
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
                if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 如果是false不处理（在后台的logic_check()里会统一进行非空检查），即还是为空，否则替换成相应的数字
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_at' || $r_key == 'end_at' ||$r_key == 'create_at') {
                    if ($r_key == 'start_at') {
                        $type = 0;
                        $hint = '开始';
                    } elseif($r_key == 'end_at') {
                        $type = 1;
                        $hint = '结束';
                    }elseif($r_key == 'create_at'){
                        $type = 2;
                        $hint = '创建';
                    }
                    //批量投放 统一修改时间带T
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $this->append_error_msg($error_msg, $key, 1, "{$hint}时间需以T开头;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }
                }
                if($r_key == 'rank'){
                    if(!is_numeric($content_arr[$key][$r_key])||$content_arr[$key][$r_key]<0||floor($content_arr[$key][$r_key])!=$content_arr[$key][$r_key]){
                        $this->append_error_msg($error_msg, $key, 1, "排序需为正整数");
                    }
                }
            }
        }

        return $error_msg;
    }

    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr, $type = 1) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'flexible_extent';
        $M_extent_soft_table = 'flexible_extent_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        $extent_type = 28;
        $pack_column = 'package';
        if($type == 38){
            $extent_type = 38;
        }
        foreach($content_arr as $key=>$record) {
            // 检测区间ID
            if (isset($record['extent_id']) && $record['extent_id'] != "") {
                $where = array(
                    'extent_id' => array('EQ', $record['extent_id']),
                    'status' => array('EQ', 1),
                    'extent_type' => array('EQ', $extent_type),
                );
                $find = $extent_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "区间位ID【{$record['extent_id']}】无效;");
                }
            }else{
                $this->append_error_msg($error_msg, $key, 1, "区间位ID不能为空;");
            }
            
            if(!isset($record['package']) || $record['package']=='') {
            	$this->append_error_msg($error_msg, $key, 1, "包名不能为空");
            }
            
            // 检查显示概率是否为数字
            if($type == 1){
                if (isset($record['prob']) && $record['prob'] != "") {
                    if (!preg_match("/^\d+$/", $record['prob'])) {
                        $this->append_error_msg($error_msg, $key, 1, "显示概率应为整数;");
                    } else if ($record['prob'] > 100) {
                        $this->append_error_msg($error_msg, $key, 1, "显示概率不能大于100;");
                    }
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "显示概率值不能为空;");
                }
            }

            // 检查开始时间
            if (isset($record['start_at']) && $record['start_at'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_at'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } 
                else {
                    $time = strtotime($record['start_at']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_at']) && $record['end_at'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_at'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                }
                else {
                    $time = strtotime($record['end_at']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            //刷量开始时间  年月日 时分秒
             if (isset($record['create_at']) && $record['create_at'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['create_at'])) {
                    $this->append_error_msg($error_msg, $key, 1, "创建时间日期格式不对;");
                }
                else {
                    $time = strtotime($record['create_at']);
                    if ($time) {
                        $content_arr[$key]['bk_creat_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "创建时间日期格式不对;");
                    }
                }
            }
            
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time']> $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
            //刷量中的创建时间不能大于开始时间和结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_creat_time'])) {
                if ($content_arr[$key]['bk_creat_time']> $content_arr[$key]['bk_start_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "创建时间需小于开始时间;");
                }
            }
        }

        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) {

            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                
                if ($record1['package'] != $record2['package'])
                        continue;

                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;

                // 时间是否交叉
                if($record1['extent_id']==$record2['extent_id'] && $record1['package'] == $record2['package']){
                    $k1=$key1+1;
                    $k2=$key2+1;
                     $this->append_error_msg($error_msg, $key1, 1, "投放区间与第{$k2}行有重叠;");
                     $this->append_error_msg($error_msg, $key2, 1, "投放区间与第{$k1}行有重叠;");
                }
                // if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                //     $k1 = $key1 + 1; $k2 = $key2 + 1;
                //     $this->append_error_msg($error_msg, $key1, 1, "投放区间与第{$k2}行有重叠;");
                //     $this->append_error_msg($error_msg, $key2, 1, "投放区间与第{$k1}行有重叠;");
                // }
            }
        }

        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // // 业务逻辑：如果填写的区间无效，则不比较

            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
                $db_records = $extent_soft_model->table('sj_flexible_extent_soft')->where(array('extent_id'=>$record['extent_id'],'status'=>1,'end_at' => array('gt',time())))->field('*')->select();
                foreach($db_records as $db_record){
                    if($db_record[$pack_column] == $record['package']){
                        $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                        $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                        $extent_data = $extent_soft_model->table('sj_flexible_extent')->where(array('extent_id'=>$db_record['extent_id']))->field('*')->find();
                        $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$extent_data['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record[$pack_column]}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}）;");
                    }

                }

        }
        return $error_msg;
    }
    
    //灵活运营资源库
    function res_list_soft(){
    	$model = M('sj_flexible_extent_soft');
    	import("@.ORG.Page");
    	$where['status'] = 1;
    	if(!isset($_GET['resource_type'])){
    		$resource_type = 24;
    	}else{
    		$resource_type = $_GET['resource_type'];
    	}
    	if($_GET['extent_id']) {
    		$where['extent_id'] = $_GET['extent_id'];
    	}
    	$where['resource_type'] = $resource_type;
    	if(!empty($_GET['srch_title'])){
    		$where['title'] = array('like', "%".trim($_GET['srch_title'])."%");
    	}
    	if(!empty($_GET['srch_pkg'])){
    		$where['package_643'] = trim($_GET['srch_pkg']);
    	}
        if($_GET['from']=='fixed_resource_channel'){
            //资源库搜索
            $where['resource_type'] = array('exp',"in (24,26,29,28)");
            $where['extent_id'] = array('exp'," !=0 ");
            $package = trim($_GET['package']);
            $soft = $model->table('sj_soft')->where("status = 1 and hide = 1 and package = '{$package}'")->field('softid,softname')->order('softid desc')->find();
            $this->assign('softname',$soft['softname']);
            $where['package_643'] = array('exp', " = '{$package}' or package = '{$package}' and IF((start_at IS NOT NULL AND start_at <= 1521698423 AND `end_at` >= 1521698423) OR start_at = 0,1,0)");
        }

        //添加内容选项
        if(!empty($_GET['resource_type'])){
            if(in_array($_GET['resource_type'],array(2,24,26,29))){
                //搜索条件
                if($_GET['content_level']) $where["cont_level"] = $_GET['content_level'];
                if($_GET['content_nature']) $where["cont_quality"] = $_GET['content_nature'];
                if($_GET['cont_src']) $where["cont_src"] = $_GET['cont_src'];
                if($_GET['user_tend']) $where["user_tend"] = $_GET['user_tend'];
                if($_GET['s_content_column']){
                    $where["cont_column"] = array('like','%,'.$_GET['s_content_column'].',%');
                    $this->assign('s_content_column',$_GET['s_content_column']);
                } 
                if(!is_null($_GET['s_status_tag'])){
                    $where["tagstatus"] = $_GET['s_status_tag'];
                    $this->assign('s_status_tag',$_GET['s_status_tag']);
                }
                if($_GET['s_soft_type']){
                    $where['soft_type'] = $_GET['s_soft_type'];
                    $this->assign('s_soft_type',$_GET['s_soft_type']);
                }

                $cont_model = D('Sj.ContAttribute');
                $column_model = D('Sj.ContColumn');
                $column_list = $column_model->getall_cont_column();
                $content_xz = content_nature_selecttag($_GET['content_nature'],'',1);
                $content_zl = content_level_selecttag($_GET['content_level'],'',1);
                #内容来源
                $config = array(
                    'key'=>'CONTENT_SOURCE',
                    'type'=>'select',
                    'tag_id'=>'cont_src',
                    'tag_name'=>'cont_src',
                    'tag_tip'=>'请选择内容来源',
                    'default'=> isset($_GET['cont_src']) ? $_GET['cont_src'] : 0,

                );
                $con_source = content_html_unit($config);
                #用户倾向
                $config_user = array(
                    'key'=>'USER_TEND',
                    'type'=>'select',
                    'tag_id'=>'user_tend',
                    'tag_name'=>'user_tend',
                    'tag_tip'=>'全部',
                    'default'=> isset($_GET['user_tend']) ? $_GET['user_tend'] : 0,

                );
                $user_tend = $cont_model -> get_user_tend($config_user);
                $this->assign('user_tend',$user_tend);
                $this->assign('con_source',$con_source);
                $this->assign('content_zl', $content_zl);
                $this->assign('content_xz', $content_xz);
                $this->assign('column_list', $column_list);

                $content_level = C('CONTENT_LEVEL');
                $content_quality = C('CONTENT_NATURE');
                $content_source = C('CONTENT_SOURCE');
                $content_softtype = C('CONTENT_SOFT_TYPE');
                $user_tend = C('USER_TEND');
            }
        }
        
    	$count = $model->table('sj_flexible_extent_soft')->where($where)->count();
    	$param = http_build_query($_GET);
    	$Page = new Page($count, 20, $param);
    	$list = $model->table('sj_flexible_extent_soft')->where($where)->order('update_at desc')->limit($Page->firstRow.','.$Page->listRows)->select();
    	foreach ($list as $key => $val) {
    		$content_type = $val['content_type'];
    		if ($content_type == 1) {
    			// 软件名
    			$package = $val['package'];
    			$where = array(
    					'package' => $package,
    					'status' => 1,
    					'hide' => 1,
    			);
    			$find = $model->table('sj_soft')->where($where)->order('version_code desc')->find();
    			$softname = $find['softname'];
    			$list[$key]['lead_content'] = "{$package}({$softname})";
    			$list[$key]['mark_name'] = "推荐";
    		} if ($content_type == 2) {
    			// 活动名称
    			$list[$key]['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($val['activity_id']);
    			$list[$key]['lead_content'] = $list[$key]['activity_name'];
    			$list[$key]['mark_name'] = "活动";
    		} else if ($content_type == 3) {
    			// 专题名称
    			$list[$key]['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($val['feature_id']);
    			$list[$key]['lead_content'] = $list[$key]['feature_name'];
    			$list[$key]['mark_name'] = "专题";
    		} else if ($content_type == 4) {
    			// 页面名称
    			$list[$key]['page_name'] = ContentTypeModel::convertPageType2PageName($val['page_type']);
    			$list[$key]['lead_content'] = $list[$key]['page_name'];
    			if($val['opera_mark_num']==30)
    			{
    				$list[$key]['mark_name'] = $val['opera_mark_name'];
    			}
    			else
    			{
    				$list[$key]['mark_name'] = ContentTypeModel::convertnum2MarkName($val['opera_mark_num']);
    			}
    		} else if ($content_type == 5) {
    			$list[$key]['lead_content'] = $list[$key]['website'];
    			if($val['opera_mark_num']==30)
    			{
    				$list[$key]['mark_name'] = $val['opera_mark_name'];
    			}
    			else
    			{
    				$list[$key]['mark_name'] = ContentTypeModel::convertnum2MarkName($val['opera_mark_num']);
    			}
    		}else if ($content_type == 6) {
    			$list[$key]['lead_content'] = $list[$key]['gift_id'];
    			$list[$key]['mark_name'] = "礼包";
    		}else if ($content_type == 7) {
    			$list[$key]['lead_content'] = $list[$key]['strategy_id'];
    			$list[$key]['mark_name'] = "攻略";
    		}else if ($content_type == 8) {
    			// 活动名称
    			$list[$key]['recommend_order_name'] = ContentTypeModel::convertOrderId2OrderName($val['activity_id']);
    			$list[$key]['lead_content'] = $list[$key]['recommend_order_name'];
    			$list[$key]['mark_name'] = "预约";
    		}else if ($content_type == 9) {
    			$used_info = json_decode($val['parameter_field'],true);
    			$list[$key]['lead_content'] = isset($used_info['title'])?$used_info['title']:'';
    			$list[$key]['mark_name'] = "应用内览";
    		}

    		//单软件(视频)勾选开发者，关联标题和视频默认图
    		if($val['resource_type']==29 && !empty($val['is_dev'])){
    			$parameter_field = json_decode($val['parameter_field'], true);
    			$video_id = $parameter_field['video_id'];
    			$video_one = $model->table('sj_soft_extra')->field('video_title,video_pic,video_url,video_h263_url')->where("id={$video_id}")->find();
    			$list[$key]['title'] = $video_one['video_title'];
    			$list[$key]['video_pic'] = $video_one['video_pic'];
    			$list[$key]['video_url'] = $video_one['video_url'];
    		}
            if(!empty($_GET['resource_type'])){//拼接组装内容属性列
                if(in_array($val['resource_type'],array(2,24,26,29))){
                    $list[$key]['content_select'] = '';
                    if($list[$key]['cont_level']) $list[$key]['content_select'] .= '<li style="list-style:none;">内容质量：'.$content_level[$list[$key]['cont_level']]."</li>";
                    if($list[$key]['cont_quality']) $list[$key]['content_select'] .= '<li style="list-style:none;">内容性质：'.$content_quality[$list[$key]['cont_quality']];
                    if($list[$key]['cont_src']) $list[$key]['content_select'] .= '<li style="list-style:none;">内容来源：'.$content_source[$list[$key]['cont_src']];
                    //用户倾向 user_tend
                    if($list[$key]['user_tend']) $list[$key]['content_select'] .= '<li style="list-style:none;">用户倾向：'.$user_tend[$list[$key]['user_tend']];
                    if($list[$key]['cont_column']){
                        $cont_column_num = explode(',',trim($list[$key]['cont_column'],','));
                        $column_select = '';
                        foreach ($column_list as $column_value) {
                            if(in_array($column_value['cont_id'],$cont_column_num)){
                                $column_select .= $column_value['name'].',';
                            }
                        }
                        $list[$key]['content_select'] .= '<li style="list-style:none;">内容栏目：'.$column_select;
                    }
                    if($list[$key]['tagstatus'] == 1) $list[$key]['content_select'] .= '<li style="list-style:none;">标签状态：是';
                    if($list[$key]['soft_type'] > 0) $list[$key]['content_select'] .= '<li style="list-style:none;">软件类型：'.$content_softtype[$list[$key]['soft_type']];
                }
            }
    	}
    	$show = $Page->show();
    	if($_GET['lr']){
    		$lr = $_GET['lr'];
    	}else{
    		$lr = 20;
    	}
    	if($_GET['p']){
    		$p = $_GET['p'];
    	}else{
    		$p = 1;
    	}
    	$Page -> setConfig('header', '篇记录');
    	$Page -> setConfig('first', '<<');
    	$Page -> setConfig('last', '>>');
    	$this -> assign('domain_url', ATTACHMENT_HOST);
    	$this -> assign('srch_title', $_GET['srch_title']);
    	$this -> assign('srch_pkg', $_GET['srch_pkg']);
    	$this -> assign('srch_pkg_name', $_GET['srch_pkg_name']);
    	$this -> assign('lr', $lr);
    	$this -> assign('p', $p);
    	$this -> assign("page", $show);
    	$this -> assign('list', $list);
    	$this -> assign('extent_id', $_GET['extent_id']);
    	$this -> assign('resource_type', $resource_type);
    	$this -> display('res_list_soft');
    }
    


    
    private function deal_img($image_url,$image_width=0,$image_height=0,$image_name='图片',$expression='jpg|png'){
    	if(!$_FILES[$image_url]['tmp_name']){
    		$this->error("请上传{$image_name}！");
    	}
    	// 取得图片后缀
    	$suffix = preg_match("/\.({$expression})$/", $_FILES[$image_url]['name'],$matches);
    	if ($matches) {
    		$suffix = $matches[0];
    	} else {
    		$this->error("{$image_name}格式应为{$expression}！");
    	}
    	// 判断图片长和宽
    	$img_info_arr = getimagesize($_FILES[$image_url]['tmp_name']);
    	if (!$img_info_arr) {
    		$this->error("上传{$image_name}出错！");
    	}
    	$width = $img_info_arr[0];
    	$height = $img_info_arr[1];
    	if($image_width!=0&&$image_height!=0){
    	    if ($width!=$image_width || $height!=$image_height){
    	        $this->error("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
    	    }
    	}
    	$folder = "/img/".date("Ym/d/");
    	$this->mkDirs(UPLOAD_PATH . $folder);
    	$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
    	$img_path = UPLOAD_PATH . $relative_path;
    	$ret = move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path);
    	return $relative_path;
    }
    public function edit_soft_batch(){
        if($_GET['edit_batch']==1){
            $idlist=rtrim($_GET['idlist'],',');
            $this->assign('idlist',$idlist);

            $num=count(explode(',',$idlist));

            $this->assign('num',$num);
            $this->display('edit_all');
            return;
        }
        //批量编辑执行
        $idlist=explode(',', $_POST['idlist']);

        $model = M('flexible_extent_soft');
        $datas = $model->where(array('id'=>array('in',$idlist)))->select();
        $error=array();
        foreach($datas as $k=>$v){

            $start_at=$_POST['start_at']?strtotime($_POST['start_at']):$v['start_at'];
            $end_at=$_POST['end_at']?strtotime($_POST['end_at']):$v['end_at'];

            $id=$v['id'];
            $extent_id=$v['extent_id'];
            // $rank=$v['rank'];
            $package=$v['package'];
            // $name_id = $v['name_id'];
            if($start_at>$end_at){
				$error[$id]['title'] = $v['title']?$v['title']:'';
                $error[$id]['package']=$package;
                $error[$id]['error']='结束时间不可早于开始时间';
                continue;
            }

            //屏蔽软件上排期时报警需求 新增  yuesai
			/*
            $AdSearch = D("Sj.AdSearch");
            if($package){
                $shield_error=$AdSearch->check_shield($package,$start_at,$end_at);
                $shield_error.=$AdSearch->check_shield_old($package,$extent_id,0,'sj_flexible_extent');
                if($shield_error){
                    $error[$id]['package']=$package;
                    $error[$id]['error']=$shield_error;
                    continue;
                }
            }
            $find = $model->table('sj_flexible_extent')->where(array('extent_id' => $extent_id, 'status' => 1))->find();
            $extent_type=$find['extent_type'];
            if($extent_type==28){

                $pkgs = $model->table('sj_flexible_extent_soft')->where(array('extent_id'=>$extent_id,'status'=>1,'end_at' => array('gt',time()),'id'=>array('neq',$id)))->field('package')->select();
                foreach($pkgs as $pkg){
                    if($package == $pkg['package']){
                        $error[$id]['package']=$package;
                        $error[$id]['error']='场景卡片分区不能添加相同软件';
                        continue;
                    }
                }
            } */

            $log = $this->logcheck(array('id'=>$id), 'sj_flexible_extent_soft', array('end_at'=>$end_at,'start_at'=>$start_at), $model);

            $ret = $model->where(array('id'=>$id))->save(array('end_at'=>$end_at,'start_at'=>$start_at));
            if ($ret || $ret === 0) {
                if ($ret) {
                    $this->writelog("灵活运营样式-软件推荐：编辑了id为{$id}的数据，{$log}",'sj_flexible_extent_soft', $id,__ACTION__ ,"","edit");
                }
            }
        }
        $re_error="";
        foreach ($error as $key => $value) {
			if($value['package']){
				$re_error.= '包名:'.$value['package']."&nbsp;&nbsp;" ;
			}
			if($value['title']){
				$re_error.= '标题:'.$value['title']."&nbsp;&nbsp;" ;
			}
			$re_error.= "(".$value['error'].")"."<br>";			
        }
        if($error_num=count($error)){
            $success_num=count($idlist)-$error_num;
            echo json_encode(array('code'=>0,'success_num'=>$success_num?$success_num:0,'error_num'=>$error_num,'error'=>$re_error));
            return;
        }else{
            echo json_encode(array('code'=>1,'success_num'=>count($idlist)));
            return;
        }

    }
    
    protected function _updateRankInfoExt($show_form,$pid,$parent_id,$target_id,$target_rank,$lr,$p){
    	$table_1 = 'sj_extent_v2';
    	$table_2 = 'sj_flexible_extent';
    	$m     = (int)$target_rank;
    	$id    = $target_id.'_'.$show_form;
    
    	$left_where = " `status`=1 and pid={$pid} and parent_id={$parent_id} and type !=3 and extent_type !=4";
    	$right_where = " `status` =1 and pid ={$pid} and belong_page_type='fixed_homepage_recommend' ";
    	$sql = "SELECT * FROM  (SELECT  extent_id,show_form,rank,create_at FROM {$table_1} where {$left_where} UNION ALL SELECT extent_id,-1,rank,create_at FROM sj_flexible_extent where {$right_where}) as A ORDER BY A.rank ASC,A.create_at DESC";
    	$result = M('')->query($sql);
    	$rank_db_list = array();
    	$rank_list = array();
    
    	$i = 1;
    	foreach ($result as $row) {
    		$pkk = $row['extent_id'].'_'.$row['show_form'];
    		$rank_list[$i] = array($pkk, $i, $row['show_form'],$row['extent_id']);
    		$rank_db_list[$pkk] = array($row['rank'], $i);
    		$i++;
    	}
    	$old_pos = $rank_db_list[$id][1];
    	$new_pos = $m;
    
    	//$m新值, $n旧值(直接取修正后的值，不需要通过get方法传递)
    	$rank_list[$old_pos][1] = $new_pos;
    
    	if($old_pos > $new_pos){//上升
    		for ($j = $new_pos; $j < $old_pos; $j++) {
    			$rank_list[$j][1] += 1;
    		}
    	} elseif($old_pos < $new_pos) { //下降
    		for ($j = $new_pos; $old_pos < $j; $j--) {
    			$rank_list[$j][1] -= 1;
    		}
    	}
    	//print_r($rank_list);die;
    	$update = array();
    	foreach ($rank_list as $k => $v) {
    		if ($v[1] != $rank_db_list[$v[0]][0]) {
    			$update[$v[0]] = $v[1];
    			$w = array(
    					'extent_id' => $v[3]
    			);
    			$d = array(
    					'rank' => $v[1]
    			);
    			if($v[2] != -1 ) {
    				M('')->table($table_1)-> where($w) -> save($d);
    			}else {
    				M('')->table($table_2)-> where($w) -> save($d);
    			}
    		}
    	}
    	if(is_int($lr) && $lr > 0){
    		//解析分页
    		$yu  = $new_pos % $lr;
    		if($yu != 0 && $new_pos > $lr){
    			$page = floor($new_pos/$lr) + 1;
    		}else{
    			$page = floor($new_pos/$lr);
    		}
    		return array('p'=>$page,'lr'=>$lr);
    	}else{
    		return true;
    	}
    }
    
 	public function pub_package_check(){
        $model = new Model();
        $id = $_GET['id'];
        $extent_id = trim($_GET['extent_id']);
        $package_643 = trim($_GET['package_643']);
        $content_type = trim($_GET['content_type']);
        $resource_type = trim($_GET['resource_type']);
        $show_style = trim($_GET['show_style']);
        
        $where['status'] = 1;
        if(!empty($resource_type)){
            $where['resource_type'] = $resource_type;
        }
        $where['package_643'] = $package_643;
        if(!empty($show_style)){
            if($show_style==1){
                $where['resource_type'] = 24;
            }elseif($show_style==2){
                $where['resource_type'] = array('in', '24,26');
            }
        }
        if(!empty($content_type)){
            $where['content_type'] = $content_type;
        }
        if(!empty($id)){
            $where['id'] = array('exp', " != {$id}");
        }
        $where['extent_id'] = array('NEQ', 0);
        $result = $model->table('sj_soft')->where(array('package'=>$package_643, 'hide'=>1, 'status'=>1))->select();
        if(!empty($result)){
        	if($resource_type == 28) {
        		$where_28 = array('extent_id'=>$extent_id,'package'=> $package_643,'status'=>1,'end_at' => array('gt',time()));
        		if(!empty($id)){
        			$where_28['id'] = array('neq',$id);
        		}
        		$res =$model->table('sj_flexible_extent_soft')->where($where_28)->field('id,package')->select();
        	}else {
        		$res = $model->table('sj_flexible_extent_soft')->where($where)->select();
        	}
            if(!empty($res)){
                exit(json_encode(array('code'=>2, 'msg'=>$result[0]['softname'], 'edit'=>$res[0]['id'])));
            }else{
                exit(json_encode(array('code'=>0, 'msg'=>$result[0]['softname'])));
            }           
        }else{
            exit(json_encode(array('code'=>1)));
        }
    }
    
    public function pub_get_package(){
    	$model = new Model();
    	$package_643 = trim($_GET['package_643']);
    	$resource_type = trim($_GET['resource_type']);
    	if(empty($package_643)){
    		exit(json_encode(array('code'=>0)));
    	}
    	$where['status'] = 1;
    	if(!empty($resource_type)){
    		$where['resource_type'] = $resource_type;
    	}
    	if($resource_type==28) {
    		$where['package'] = $package_643;
    	}else {
    		$where['package_643'] = $package_643;
    	}
    	$where['extent_id'] = array('NEQ', 0);
    	$res = $model->table('sj_flexible_extent_soft')->field('id,content_type,title,description,is_dev,parameter_field,resource_type')->where($where)->select();
    	if(!empty($res)){
    		foreach ($res as $key => $value) {
    			if($value['resource_type']==29 && $value['is_dev']==1 ) {
    				$video_arr  = json_decode($value['parameter_field'], true);
    				$video_info = $model->table('sj_soft_extra')->field('video_title,video_pic')->where(array('id'=>$video_arr['video_id']))->find();
    				$res[$key]['title'] = $video_info['video_title'];
    			}
    		}
    		exit(json_encode(array('code'=>1,'info'=>$res)));
    	}else{
    		exit(json_encode(array('code'=>0)));
    	}
    }
    
    protected function _updateRankInfoExtDel($pid,$parent_id){
    	$table_1 = 'sj_extent_v2';
    	$table_2 = 'sj_flexible_extent';
    	$left_where = " `status`=1 and pid={$pid} and parent_id={$parent_id} and type !=3 and extent_type !=4";
    	$right_where = " `status` =1 and pid ={$pid} and belong_page_type='fixed_homepage_recommend' ";
    	$sql = "SELECT * FROM  (SELECT  extent_id,show_form,rank,create_at FROM {$table_1} where {$left_where} UNION ALL SELECT extent_id,-1,rank,create_at FROM sj_flexible_extent where {$right_where}) as A ORDER BY A.rank ASC,A.create_at DESC";
    	$result = M('')->query($sql);
    	$count = count($result);
    	for($i = 1;$i <= $count; $i++){
    		if($result[$i-1]['show_form'] != -1 ) {
    			$sql_1   = 'UPDATE '.$table_1.' SET rank ='.$i.' WHERE `status` = 1 AND extent_id ='.$result[$i-1]['extent_id'];
    			M('')->table($table_1) -> query($sql_1);
    		}else {
    			$sql_2   = 'UPDATE '.$table_2.' SET rank ='.$i.' WHERE `status` = 1 AND extent_id ='.$result[$i-1]['extent_id'];
    			M('')->table($table_2) -> query($sql_2);
    		}
    	}
    	return true;
    }
    
    // 自动检查要不要发在指定时间段内软件数超出区间位置大小的邮件
    // 参数extent_info_arr是个二维数组，格式大致为array('$extent_id'=>array('extent_id'=>$extent_id, 'start_at'=>$start_at, 'end_at'=>$end_at))
    function send_size_notice_email($extent_info_arr) {
    	if (!$extent_info_arr || empty($extent_info_arr))
    		return false;
    	$email_content = '';
    	$model = M();
    	foreach ($extent_info_arr as $extent_id => $extent_info) {
    		// 先根据extent_id获得区间信息，最主要是获得区间名称及区间大小
    		$where = array(
    				'status' => 1,
    				'extent_id' => $extent_id
    		);
    		$extent = $model->table('sj_extent_v2')->where($where)->find();
    		if (!$extent)
    			continue;
    		$extent_name = $extent['extent_name'];
    		// 获得区间位置数
    		if ($extent['type'] == 3) {
    			// 所有子区间的大小才构成联合区间的大小
    			$where = array(
    					'status' => 1,
    					'type' => array('NEQ', 3),
    					'parent_union_id' => $extent_id,
    			);
    			$result = $model->table('sj_extent_v2')->field('sum(extent_size) as c')->group('parent_union_id')->where($where)->find();
    			$extent_size = $result['c'];
    		} else {
    			$extent_size = $extent['extent_size'];
    		}
    
    		// 开始检查数量
    		if (!isset($extent_info['start_at']) || !isset($extent_info['end_at']))
    			continue;
    		$orginal_start_at = $extent_info['start_at'];
    		$orginal_end_at = $extent_info['end_at'];
    
    		// 先将start_at和end_at转成其当天的最早点和最晚点
    		$real_start_at = date("Y/m/d", $orginal_start_at);
    		$real_start_at = strtotime($real_start_at);
    
    		$real_end_at = date("Y/m/d 23:59:59", $orginal_end_at);
    		$real_end_at = strtotime($real_end_at);
    
    		$current = $real_start_at;
    		// 记算指定时间范围内的每天的凌晨
    		$time_arr = array();
    		for ($current = $real_start_at; $current < $real_end_at; $current += 86400) {
    			$time_arr[] = $current;
    		}
    		// 获得和每天的区间软件数，把超出区间大小的排期日期给记录下来
    		$exceed_soft_counts_arr = array();
    		foreach ($time_arr as $start_at) {
    			$end_at = $start_at + 86399;
    			// 分别查询不区分高低配、高配、低配的数量
    			$common_where = array(
    					'extent_id' => $extent_id,
    					'start_at' => array('elt',$end_at),
    					'end_at' => array('egt',$start_at),
    					'status' => 1
    			);
    			$nodis_where = array_merge($common_where, array('phone_dis' => 1));
    			$high_where = array_merge($common_where, array('phone_dis' => 2));
    			$low_where = array_merge($common_where, array('phone_dis' => 3));
    			$nodis_count = $model->table('sj_extent_soft_v2')->where($nodis_where)->count();
    			$high_count = $model->table('sj_extent_soft_v2')->where($high_where)->count();
    			$low_count = $model->table('sj_extent_soft_v2')->where($low_where)->count();
    			$soft_counts = $nodis_count + max($high_count, $low_count);
    			if ($soft_counts > $extent_size) {
    				$exceed_soft_counts_arr[$start_at] = $soft_counts;
    			}
    		}
    		// 组织语言准备发邮件
    		if (empty($exceed_soft_counts_arr)) {
    			continue;
    		}
    		$email_content .= "区间id为【{$extent_id}】，区间名为【{$extent_name}】的区间，";
    		foreach ($exceed_soft_counts_arr as $start_at => $soft_counts) {
    			$start_at_str = date('Y/m/d', $start_at);
    			$email_content .= "{$start_at_str}，";
    		}
    		$email_content .= '其区间软件数大于区间位置数。<br/>';
    	}
    	if (!$email_content)
    		return true;
    	// 发邮件提醒运营人员
    	$emailmodel = D("Dev.Sendemail");
    	$email_config_find = $emailmodel->table('pu_config')->where(array('config_type'=> 'EXTENDV1_EMAIL_SEND', 'status'=> 1))->find();
    	if (!$email_config_find || !$email_config_find['configcontent'])
    		return false;
    	$subject = '市场首页软件列表';
    	$ret = $emailmodel->realsend($email_config_find['configcontent'], $email_config_find['configcontent'], $subject, $email_content);
    	return $ret;
    }
    
    public function reslibrary(){
    	$_GET['belong_page_type'] = 'fixed_resource_channel';
        $_GET['cont'] = 1;
        $this->index();
    }

	public function deal_extent_soft($id, $extent_type, $post, $type='add'){
        //add 添加 save 修改
		$model = M('');
        if($type == 'add'){
            $data = array(
                'extent_id' => $id,
                'prob' => 100,
                'start_at' => time(),
                'end_at' => strtotime("+10 year"),
                'create_at' => time(),
            );
            if($extent_type == 32){
                //多排自动
                $data['package'] = $this->special_package;
                $data['parameter_field'] = '{"soft_rank":"1","search_type":"2"}';
                $data['version_type'] = 3;
            }else if($extent_type == 34){
                //原生广告
                $data['content_type'] = 4;
                $data['page_type'] = $this->native_ad_page_type;
                $data['parameter_field'] = json_encode(array('native_ad_id'=>$post['native_ad_id'],'native_ad_type'=>$post['native_ad_type']));
            }
            $model->table('sj_flexible_extent_soft')->add($data);
        }else{
            $where = array();
            if($extent_type == 34){
                $data['parameter_field'] = json_encode(array('native_ad_id'=>$post['native_ad_id'],'native_ad_type'=>$post['native_ad_type']));
                $where['extent_id'] = $id;
            }
            $model->table('sj_flexible_extent_soft')->where($where)->save($data);
        }
	}

    public function chk_extent_35($data, &$map){
        if(empty($data['attention_button'])){
            $this->error('请选择按钮样式');
        }
        $map['attention_button'] = $data['attention_button'];
    }

    public function chk_extent_38($data, &$map){
        if(empty($data['show_rank'])){
            $this->error('请选择是否显示排名');
        }
        $map['show_rank'] = $data['show_rank'];
    }

    public  function pub_get_activity(){
        $id = $_POST['activity_id'];
        $res = ContentTypeModel::get_activity(array('id'=>$id));
        echo json_encode($res);exit();
    }

    public function pub_check_forum(){
        $key = 'EWdi9dR81';
        $url = 'http://bbs.zhiyoo.com/api/cp.php';
        $mod = 'forum';
        $fid = $_POST['fid'];
        $salt = md5($mod.$fid.$key);
        $url .= "?mod={$mod}&fid={$fid}&salt={$salt}";
        $res = file_get_contents($url);
        $res = json_decode($res, true);        
        if(isset($res['error'])){
            echo '';
        }else{
            echo $res['forum']['name'];
        }
        exit();
    }

}
?>