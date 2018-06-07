<?php
class CategoryExtentAction extends CommonAction {

    private $image_width_long = 466;
    private $image_height_long = 140;
    private $image_width_short = 230;
    private $image_height_short = 133;

    private $image_width_long_multi = 466;
    private $image_height_long_multi = 112;
	private $sdk5_channel = array('recommend_nongift', 'recommend_noncoupon', 'recommend_strategy');

    function index() {
        $util = D("Sj.Util");
        // 平台参数
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
        // 频道类型参数
        $page_type = isset($_GET['page_type']) ? $_GET['page_type'] : 0;
        // 频道页面父页面id，貌似已经废弃此变量
        $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;
        


        $expand = isset($_GET['expand']) ? $_GET['expand'] : '';


        // 详细频道参数
        $category_type = '';
		if(isset($_GET['category_type']) ) {
			$category_type = $_GET['category_type'];
            // 根据category_type得到类型并分配给页面
            $page_type = ContentTypeModel::getGeneralPageType($category_type);

            $page_name = ContentTypeModel::convertPageType2PageNameOfCategory($category_type,$pid);
            if ($page_type == 2) {
                $this->assign('tag_name', $page_name);//返回给搜索栏
            } else if ($page_type == 3) {
                $this->assign('commontag_name', $page_name);//返回给搜索栏
            }
		} else {
            // 标签频道参数
            $tag_name = '';
            if (isset($_GET['tag_name'])) {
                $tag_name = $_GET['tag_name'];
                $tag_page_type = ContentTypeModel::convertTagPageName2TagPageType($tag_name);
                if (!$tag_page_type)
                    $this->error("输入的标签不存在");
                $category_type = $tag_page_type;
                $this->assign('tag_name', $tag_name);
            }
            // 常用标签频道参数
            $commontag_name = '';
            if (isset($_GET['commontag_name'])) {
                $commontag_name = $_GET['commontag_name'];
                $commontag_page_type = ContentTypeModel::convertCommonTagPageName2CommonTagPageType($commontag_name);
                if (!$commontag_page_type)
                    $this->error("输入的常用标签不存在");
                $category_type = $commontag_page_type;
                $this->assign('commontag_name', $commontag_name);
            }
        }
        // 页数参数
        if(isset($_GET['lr'])) {
		    $this->assign("lr",(int)$_GET['lr']);
		} else {
		    $this->assign("lr",$limit);
		}
		if(isset($_GET['p'])) {
		    $this->assign("p",(int)$_GET['p']);
		} else {
		    $this->assign("p", 1);
		}
        // 所有平台
        $util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;
        // 所有渠道
        $channel_model = M('channel');
		$channels = $channel_model->field("`cid`,`chname`")->where(array('status' => 1))->select();
		$channels_key = array();
		foreach($channels as $v) {
			$channels_key[$v['cid']] = $v['chname'];
		}
        // 所有运营商
        $operating_db = D('Sj.Operating');
		$operating_list = $operating_db->field('oid,mname')->select();
		$operators_key = array();
		foreach($operating_list as $v) {
			$operators_key[$v['oid']] = $v['mname'];
		}

        $map = array(
			'status' => 1,
			'pid' => $pid,
			'parent_id' => $parent_id
		);
		if ($expand) {
			$map['category_type_expand'] = $expand;
		}


        if ($page_type)
            $map['category_type'] = ContentTypeModel::getWhereConditionOfPageType($page_type);
        if ($category_type) {
            $map['category_type'] = $category_type;
        }

        

        import("@.ORG.Page");
		$param = http_build_query($_GET);
		$limit = 20;
        $model = M('category_extent');
        $count_total = $model -> where($map)->count();
		$page  = new Page($count_total, $limit, $param);

        $now = time();
		$list = $model->where($map)->order('rank asc, type desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		// $list = $model->where($map)->order('extent_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		// echo "<pre>";var_dump($list);die;

		if(!$page_name){
			$category_type_arr=array();
			foreach($list as $v){
				if($category_type_arr[$v['category_type']]){
					continue;
				}
				$category_type_arr[$v['category_type']]=ContentTypeModel::convertPageType2PageNameOfCategory($v['category_type']);
			}
		}

		// var_dump($category_type_arr);
        foreach ($list as $k => $v) {
			if ($v['type'] == 1) {
				$where = array(
					'extent_id' => $v['extent_id'],
					'start_at' => array('elt',$now),
					'end_at' => array('egt',$now),
					'status' => 1
				);
				$count = $model->table('sj_category_extent_soft')->where($where)->count();
				$list[$k]['soft_counts'] = intval($count);
			} else {
				$list[$k]['soft_counts'] = '-';
				$list[$k]['extent_size'] = '-';
			}
			if($v['filter_has_installed']==0)
			{
				$list[$k]['filter']="是";
			}
			if($v['filter_has_installed']==1)
			{
				$list[$k]['filter']="否";
			}
			$list[$k]['chname'] = isset($channels_key[$v['cid']]) ? $channels_key[$v['cid']] : '-';
			$list[$k]['mname'] = isset($operators_key[$v['oid']]) ? $operators_key[$v['oid']] : '-';
				$list[$k]['display_description'] = $this->shorten_sentence($v['display_description']);

				//所属页面为空 表明所属页面被删除 则隐藏掉该记录
				// $rs = $page_name?$page_name:ContentTypeModel::convertPageType2PageNameOfCategory($v['category_type']);
				$rs = $page_name?$page_name:$category_type_arr[$v['category_type']];
				if(empty($rs)){
					unset($list[$k]);
				}else{
					// $list[$k]['category_name'] = ContentTypeModel::convertPageType2PageNameOfCategory($v['category_type']);
					$list[$k]['category_name'] = $rs;
				}

		}
		if($page_type == 4){
			// 自定义列表页面
			$category_list = ContentTypeModel::getCustomListRec();
		}else{
			// 普通频道所有页面
			$category_list = ContentTypeModel::getCategoryTypesOfCategory($pid);
		}

		$map_all = ContentTypeModel::export_all_pages_operation();
		
        // 普通频道所有页面
        $bd_list = ContentTypeModel::getbdList();
		$this->assign('bd_list', $bd_list);

		//导出页码与页面名称表
		if($_GET['export'] == '1')
		{
			//$this->add_db($map_all);
			$this->export($map_all,"对应关系表_".date('Y-m-d-h-i').".csv");
		}
			
        


		$category_type_list = array();

		foreach (D('Sj.Category')->getCategoryArray() as $k => $v) {
			if($v['category_id'] < 4) {
				continue;
			}
			$category_type_list[$v['category_id']] = $v['name'];
		}

		//print_r($category_type_list);


		$this->assign('category_type_list', $category_type_list);

        $this->assign('list', $list);
		$this->assign('category_list', $category_list);

		

		$this->assign('isAjax', $this->isAjax());// 貌似已经废弃

		$this->assign('product_list',$util->getProducts($pid));
		$page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign("page", $page->show());
        $this->assign("apkurl",IMGATT_HOST);// 已经废弃

        $this->assign('parent_id', $parent_id);// 貌似已经废弃
		$this->assign('pid', $pid);
        $this->assign('page_type', $page_type);
		$this->assign('category_type', $category_type);

		$this->assign('count',$count_total);// 貌似已经废弃
        $this->assign('expand', $expand);
        $this->display();
//		$html = $this->fetch();
//		header("Cache-control: no-store");
//		header("pragma:no-cache");
//		exit($html);

    }
	
	/**
	 * 导出表  参考运营合作-合同列表-导出列表功能
	 */
	public function export($list,$filename)
	{	
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		$str = "";
		if(empty($list))
		{
			$str.=iconv('utf-8','gb2312','没有任何信息');
		}else
		{
			echo iconv('utf-8','gb2312','字符串').",";
			echo iconv('utf-8','gb2312','页面名称')."\r\n";
			
			foreach ($list as $key => $val)
			{
				echo iconv('utf-8','gb2312',$key).",";
				if(is_array($val))
				{
					echo iconv('utf-8','gb2312',$val[0])."\r\n";
				}
				else
				{
					echo iconv('utf-8','gb2312',$val)."\r\n";
				}
			}
		}
		exit;
	}
		
    function shorten_sentence($sentence, $len = 10) {
        $sen_len = mb_strlen($sentence, 'utf-8');
        if ($sen_len > $len) {
            $sentence = mb_substr($sentence, 0, $len - 2, 'utf-8') . '...';
        }
        return $sentence;
    }

    function show_content() {
        $extent_id = $_GET['extent_id'];
        $where = array(
            'extent_id' => $extent_id,
            'status' => 1,
        );
        $model = M('category_extent');
        $find = $model->where($where)->find();
        $content = '';
        if ($find) {
            $content = $find['display_description'];
        }
        $this->assign('content', $content);
        $this->display('show_content');
    }

	function add_extent()
	{
		if (!empty($_POST)){
			$model = M('category_extent');
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			
			//热门游戏 增加过滤已安装
			isset($_POST['filter_has_installed']) && $map['filter_has_installed'] = $_POST['filter_has_installed'];
			
			isset($_POST['extent_name']) && $map['extent_name'] = $_POST['extent_name'];
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			isset($_POST['cid']) && $map['cid'] = $_POST['cid'];
			isset($_POST['rank']) && $map['rank'] = $_POST['rank'];
			isset($_POST['channel_id']) && $map['channel_id'] = $_POST['channel_id'];
			isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			!empty($_POST['parent_id']) && $map['parent_id'] = $_POST['parent_id'];
			!empty($_POST['category_type']) && $map['category_type'] = $_POST['category_type'];
			!empty($_POST['pid']) && $map['pid'] = $_POST['pid'];
            !empty($_POST['area_value']) && $map['push_area'] = $_POST['area_value'];
			!empty($_POST['expand']) && $map['category_type_expand'] = strtolower(trim($_POST['expand']));
			$map['list_display'] = $_POST['list_display'];				
			$map['is_more'] = $_POST['is_more']?$_POST['is_more']:0;
			if($_POST['list_display'] == 1){
				$len = mb_strlen(trim($_POST['card_name']),'utf-8');
				if($len < 1 || $len > 20){
					$this->error("卡片名称暂限1~20，当前字数：".$len);
				}
				$map['card_name'] = $_POST['card_name'];				
			} 
			$rank  = (int)$_POST['rank'];
			$pid  = $_POST['pid'];
			$category_type  = $_POST['category_type'];
			$w = array(
				'extent_name' => $_POST['extent_name'],
				'status' => 1,
				'pid' => $_POST['pid'],
				'category_type' => $_POST['category_type'],
			);

			if(isset($map['category_type_expand'])) {
				$w['category_type_expand'] = $map['category_type_expand'];
			}
			





			$n = $model->where($w)->count();
			if ($n){
				$this->error("区间名称 {$_POST['extent_name']} 已存在！");
				exit;
			}

			$s = $_POST['rank'];
			$e = $_POST['rank'] + $_POST['extent_size'] - 1;
			
			$where = '';
			if (!empty($map['category_type_expand'])) {
				$where = " AND category_type_expand='{$map['category_type_expand']}' ";
			}

			$sql = "select * from sj_category_extent where status=1 and rank<={$e} and rank+extent_size-1>={$s} and pid='{$_POST['pid']}' and category_type='{$_POST['category_type']}' $where";

			$n = $model->query($sql);
			if ($n){
				$msg = '';
				foreach ($n as $v) {
					$s1 = $v['rank'];
					$e1 = $v['rank'] + $v['extent_size'] - 1;
					$msg .= "区间[{$v['extent_name']}]:位置{$s1}~{$e1},";
				}
				$this->error("设置的位置{$s} ~ {$e} 与{$msg}有冲突！");
				exit;
			}
			$id = $model->add($map);
			if ($id) {

				$this->assign('jumpUrl', "/index.php/Sj/CategoryExtent/index/category_type/{$category_type}/pid/{$pid}/expand/{$_POST['expand']}");
				$this->writelog('市场软件运营推荐-频道列表软件推荐-添加了id为'.$id.'的区间', 'sj_extent',$id,__ACTION__ ,"","add");
				$this->success('添加成功');
			}

		} else {

        	$operating_db = D('Sj.Operating');
        	$operating_list = $operating_db->field('oid,mname')->select();
        	$this->assign('operatinglist',$operating_list);
			

			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);
			!empty($_GET['category_type']) && $this->assign('category_type',$_GET['category_type']);
			!empty($_GET['pid']) && $this->assign('pid',$_GET['pid']);
			$expand = isset($_GET['expand']) ? $_GET['expand'] : '';
			$extent_model = M('category_extent');
			$pid  = $_GET['pid'];
			$category_type  = $_GET['category_type'];

			$map = array(
				'status' => 1,
				'category_type' => $category_type,
				'pid' => $pid,
				'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0
			);
			$count = count($extent_model -> where($map)->select()) + 1;

			$this->assign('sdk5_channel',$this->sdk5_channel);
			$this->assign('expand',$expand);
			$this->assign('count',$count);
			$this->display();
		}
	}

	function edit_extent()
	{
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}

		$id = $_REQUEST['extent_id'];

		$where = array(
			'extent_id' => $id
		);
		$model = M('category_extent');
		$extent = $model->where($where)->find();
		$category_type = $extent['category_type'];
        // extent_size只有1的情况，其他的类型已经挪到灵活运营样式中
        // $extent_type = $extent['extent_type'];
        // $extent_type = 1;
		$pid = $extent['pid'];
		if (!empty($_POST)){
			$map = array();
			$map['update_at'] = time();
			
			//热门游戏 增加过滤已安装
			isset($_POST['filter_has_installed']) && $map['filter_has_installed'] = $_POST['filter_has_installed'];
			
			isset($_POST['filter_installed']) && $map['filter_installed'] = $_POST['filter_installed'];
			isset($_POST['depot_limit']) && $map['depot_limit'] = $_POST['depot_limit'];
			isset($_POST['oid']) && $map['oid'] = $_POST['oid'];
			isset($_POST['cid']) && $map['cid'] = $_POST['cid'];
			isset($_POST['channel_id']) && $map['channel_id'] = $_POST['channel_id'];
			isset($_POST['extent_size']) && $map['extent_size'] = $_POST['extent_size'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']. ' 23:59:59');
			isset($_POST['area_value']) && $map['push_area'] = $_POST['area_value'];
			$map['list_display'] = $_POST['list_display'];	
			$map['is_more'] = $_POST['is_more'];
			if($_POST['list_display'] == 1){
				$len = mb_strlen(trim($_POST['card_name']),'utf-8');
				if($len < 1 || $len > 20){
					$this->error("卡片名称暂限1~20，当前字数：".$len);
				}			
				$map['card_name'] = $_POST['card_name'];				
			} 			
            if (isset($_POST['extent_name'])) {
				$w = array(
					'extent_name' => $_POST['extent_name'],
					'status' => 1,
					'pid' => $pid,
					'category_type' => $category_type,
					'category_type_expand' => $extent['category_type_expand'],
					'extent_id' => array('NEQ', $id),
				);
				$n = $model->where($w)->count();
				if ($n){
					$this->error("区间名称 {$_POST['extent_name']} 已存在！");
					exit;
				}
				$map['extent_name'] = $_POST['extent_name'];
			}
			if (isset($_POST['rank'])) {
				$s = $_POST['rank'];
				$e = $_POST['rank'] + $_POST['extent_size'] - 1;
				$sql = "select * from sj_category_extent where status=1 and rank<={$e} and rank+extent_size-1>={$s} and pid='{$pid}' and category_type='{$category_type}'and category_type_expand='{$extent['category_type_expand']}' and extent_id<>{$id};";
				$n = $model->query($sql);
				if ($n){
					$msg = '';
					foreach ($n as $v) {
						$s1 = $v['rank'];
						$e1 = $v['rank'] + $v['extent_size'] - 1;
						$msg .= "区间[{$v['extent_name']}]:位置{$s1}~{$e1},";
					}
					$this->error("设置的位置{$s} ~ {$e} 与{$msg}有冲突！");
					exit;
				}
				$n = $model->where($w)->count();
				if ($n){
					$this->error("位置{$_POST['rank']}已经被占用！");
					exit;
				}
				$map['rank'] = $_POST['rank'];
			}
			$log = $this -> logcheck(array('extent_id' => $id),'sj_category_extent',$map,$model);
			if ($model->where($where)->save($map)) {
				$this->assign('jumpUrl', "/index.php/Sj/CategoryExtent/index/category_type/{$category_type}/pid/{$pid}/expand/{$extent['category_type_expand']}");
				$this -> writelog('市场软件运营推荐-频道列表软件推荐-编辑了ID为'.$id."的区间".$log, 'sj_extent',$id, __ACTION__, 'rank_config','edit');
				$this->success('编辑成功');
			} else {
				$this->error('编辑失败');
			}

		} else {
        	$operating_db = D('Sj.Operating');
        	$operating_list = $operating_db->field('oid,mname')->select();
        	$this->assign('operatinglist',$operating_list);
			!empty($_GET['parent_id']) && $this->assign('parent_id',$_GET['parent_id']);

			$condition = array(
				'status' => 1,
				'category_type' => $category_type,
				'pid' => $pid,
				'parent_id' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0
			);
			$count = $model -> where($condition)->count();
			$this->assign('count',$count);
			if (!empty($extent['cid'])) {
				$where = array(
					'cid' => $extent['cid']
				);
				$channel = $model -> table('sj_channel') -> where($where)->find();
				$this->assign('chname',$channel['chname']);
			}

			$area_list=explode(';',$extent['push_area']);
			$this->assign('category_type', $category_type);
			$this->assign('sdk5_channel',$this->sdk5_channel);
            $this->assign("push_area",$area_list);
			$this->assign('extent', $extent);
			$this->display();
		}
	}

	function add_soft()
	{
		if (!empty($_POST)){
            // 业务逻辑：trim一下需要用到的数据
            $useful_key = array('extent_id', 'package', 'phone_dis', 'old_phone', 'prob', 'start_at', 'end_at', 'title', 'image_url', 'content_type', 'activity_id', 'feature_id', 'category_type','type','beid');
            foreach($useful_key as $key=>$value) {
                if (isset($_POST[$value]))
                    $_POST[$value] = trim($_POST[$value]);
            }
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $_POST;
            $content_arr[0]['image_url'] = $_FILES['image_url']['name'];
            //$content_arr[0]['extent_type'] = $extent_type;
            $error_msg = $this->logic_check($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
			$jumpUrl = 	"/index.php/Sj/CategoryExtent/list_soft/extent_id/{$_POST['extent_id']}/list_display/".$_POST['list_display'];	   
			$this->assign('jumpUrl',$jumpUrl );			
            if (!$qualified_flag) {
                $msg = $error_msg[0]['msg'];
				$this->error($msg);
            }
            $model = M('category_extent_soft');
			$extent_id = $_POST['extent_id'];
			$map = array();
			$map['status'] = 1;
			$map['create_at'] = time();
			$map['update_at'] = time();
			if($_POST['description']){
				$len = mb_strlen(trim($_POST['description']),'utf-8');
				if($len > 50){
					$this->error("卡片名称暂限50个以内，当前字数：".$len);
				}	
			}			
			$map['description'] = $_POST['description'] ? $_POST['description'] : '';
			$map['admin_id'] = $_SESSION['admin']['admin_id'];

			isset($_POST['extent_id']) && $map['extent_id'] = $extent_id;

            isset($_POST['package']) && $map['package'] = $_POST['package'];
                        //增加一个判断 如果类型是榜单 要判断下012
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
            isset($_POST['default_display']) && $map['default_display'] = $_POST['default_display'];
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			isset($_POST['phone_dis']) && $map['phone_dis'] = $_POST['phone_dis'];
			if(isset($_POST['old_phone'])){
				$map['old_phone'] = $_POST['old_phone'];
			}else{
				$map['old_phone'] = 0;
			}
			if(isset($_POST['type'])){
				$map['type'] = $_POST['type'];
			}else{
				$map['type'] = 0;
			}
			//添加行为id  added by shiting
			isset($_POST['beid']) && $map['beid'] = $_POST['beid'];

			//屏蔽软件上排期时报警需求 新增  yuesai
            $AdSearch = D("Sj.AdSearch");
            $shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
            $shield_error.=$AdSearch->check_shield_old($map['package'],$extent_id,0,'sj_category_extent');
            if($shield_error){
                $this -> error($shield_error);
            }
                       // var_dump($map);exit;
			if ($id = $model->add($map)) {
				$this->writelog("市场软件运营推荐-频道列表软件推荐:在区间[{$extent_id}]中添加了id为{$id}的记录,包名为:{$_POST['package']},显示概率为:{$_POST['prob']},开始时间为:{$_POST['start_at']},结束时间为:{$_POST['end_at']},合作形式为：{$_POST['type']}", 'sj_category_extent_soft', $id,__ACTION__ ,"","add");
				$this->success('添加成功');
			} else {
				$this->error('添加失败');
			}
		} else {
			$util = D("Sj.Util");
			$typelist = $util->getHomeExtentSoftTypeList();

			$this->assign('typelist',$typelist);

			$this->assign('extent_id',$_GET['extent_id']);
			$this->display();
		}
	}

	function list_soft()
	{
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}
		$query = array();
		$model = M('category_extent_soft');
		$extent_id = $_GET['extent_id'];
		$srch_type = $_GET['srch_type'];
		$where = array(
			'status' => 1
		);
		if (!empty($extent_id)) {
			$where['extent_id'] = $extent_id;
			$query['extent_id'] = $extent_id;
		}
		$now = time();
		switch($srch_type) {
			case 'e':
				$where['end_at'] = array('elt',$now);
			break;

			case 'f':
				$where['start_at'] = array('egt',$now);
			break;

			case 'n':
			default:
				$where['start_at'] = array('elt',$now);
				$where['end_at'] = array('egt',$now);
				$srch_type = 'n';
			break;
		}
		if (!empty($srch_type)) {
			$query['srch_type'] = $srch_type;
		}
		if (!empty($_GET['category_type'])) {
			$query['category_type'] = $_GET['category_type'];
		}
		if (!empty($_GET['pid'])) {
			$query['pid'] = $_GET['pid'];
		}
		if (isset($_GET['search_key'])) {
			$pkg = $_GET['search_key'];
			$query['search_key'] = $pkg;
			$where['package'] = array('like', "%{$pkg}%");
			$this->assign('search_key', $pkg);
		}
		$list = $model->where($where)->order('start_at asc')->select();
		$package = array();
		$package_result = array();
        // 获得频道数组
        $category_list = ContentTypeModel::getCategoryTypesOfCategory();
		//获取合作样式列表
		$util = D("Sj.Util"); 
		foreach($list as $key => $val) {
			$package[] = $val['package'];
			$package_result[$val['package']] = $val;
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$list[$key]['types'] = $v[0];
				}
			}
		}
		$soft = $model->table('sj_soft')->where(array('package' => array('in', $package), 'status'=>1, 'hide'=>1))->field('softname,softid,package')->group('package')->select();
		foreach($soft as $val) {
			$package_result[$val['package']]['softname'] = $val['softname'];
			$package_result[$val['package']]['softid'] = $val['softid'];
		}


		$where = array(
			'status' => 1
		);
		$extent_result = $extents = $model->table('sj_category_extent')->where($where)->order('parent_id asc, rank asc, type desc')->select();
		$extent_list = array();
		foreach($extent_result as $v){
			$extent_list[$v['extent_id']] = $v;
		}

		$extent_select = array();
		foreach($extent_result as $v) {
			if ($v['type'] == 1) {
				if($v['parent_id'] > 0) {
					$extent_select[$v['extent_id']] = $extent_list[$v['parent_id']]['extent_name'] . ' > ' . $v['extent_name'];
				} else {
					$extent_select[$v['extent_id']] = $v['extent_name'];
				}
			}
			if ($v['extent_id'] == $extent_id) {
				$this->assign('pid', $v['pid']);
				$this->assign('category_type', $v['category_type']);
                $category_name = ContentTypeModel::convertPageType2PageNameOfCategory($v['category_type']);
				$this->assign('category_name', $category_name);
			}
		}
		$this->assign('extent_name', $extent_select[$extent_id]);
		$this->assign('srch_type', $srch_type);
		$this->assign('extent_id', $extent_id);
        //$this->assign('extent_type', $extent_type);
		$this->assign('list', $list);
		$this->assign('extent_list', $extent_list);
		$this->assign('package_result', $package_result);
		$this->assign('extent_select', $extent_select);
		$this->assign('extents', $extents);
		$this->assign('query', $query);
		$this->assign('category_list', $category_list);
		$this->assign('isAjax', $this->isAjax());
        $this->assign("apkurl",IMGATT_HOST);
		$this->display();
	}

	function edit_soft()
	{
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}

		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$model = M('category_extent_soft');
		$soft = $model->where($where)->find();
		if(!empty($soft['parameter_field'])){
			$soft['parameter_field'] = json_decode($soft['parameter_field'],true);
			$soft['game_sdk_version_rule'] = isset($soft['parameter_field']['game_sdk_version_rule'])?$soft['parameter_field']['game_sdk_version_rule']:'';
			$soft['game_sdk_version_code'] = isset($soft['parameter_field']['game_sdk_version_code'])?$soft['parameter_field']['game_sdk_version_code']:'';
		}
        $extent_id = $soft['extent_id'];
		if (!empty($_POST)){
            // 业务逻辑：trim一下需要用到的数据
            $useful_key = array('extent_id', 'package', 'phone_dis', 'old_phone', 'prob', 'start_at', 'end_at','title', 'image_url', 'content_type', 'activity_id','type','description');
            foreach($useful_key as $key=>$value) {
                if (isset($_POST[$value]))
                    $_POST[$value] = trim($_POST[$value]);
            }

            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $_POST;
            $content_arr[0]['image_url'] = $_FILES['image_url']['name'];
            //$content_arr[0]['extent_type'] = $extent_type;
            $error_msg = $this->logic_check($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) {
                $msg = $error_msg[0]['msg'];
                // 业务逻辑：设置返回的跳转页面
                $this->assign('jumpUrl', '/index.php/Sj/CategoryExtent/list_soft/extent_id/'. $extent_id."/list_display/".$_POST['list_display']);
				$this->error($msg);
            }

            $map = array();
			$map['update_at'] = time();
			if($_POST['description']){
				$len = mb_strlen(trim($_POST['description']),'utf-8');
				if($len > 50){
					$this->error("卡片名称暂限50个以内，当前字数：".$len);
				}	
			}				
			$map['description'] = $_POST['description'] ? $_POST['description'] : '';
            $s = strtotime($_POST['start_at']);
			$e = strtotime($_POST['end_at']. ' 23:59:59');
            // 包名和活动ID不允许编辑
			isset($_POST['package']) && $map['package'] = $soft['package'];
			isset($_POST['prob']) && $map['prob'] = $_POST['prob'];
            $map['default_display'] = $_POST['default_display'] ? $_POST['default_display'] : 0;
			isset($_POST['start_at']) && $map['start_at'] = strtotime($_POST['start_at']);
			isset($_POST['end_at']) && $map['end_at'] = strtotime($_POST['end_at']);
			if($_POST['life']==1)
			{
			  if(strtotime($_POST['end_at'])<time())
			  {
			    $this->error('您修改的复制上线的日期还是无效日期');
			  }
			}
			isset($_POST['phone_dis']) && $map['phone_dis'] = $_POST['phone_dis'];
			if(isset($_POST['old_phone'])){
				$map['old_phone'] = $_POST['old_phone'];
			}else{
				$map['old_phone'] = 0;
			}
			if(isset($_POST['type'])){
				$map['type'] = $_POST['type'];
			}else{
				$map['type'] = 0;
			}
			//添加行为id  added by shiting
			isset($_POST['beid']) && $map['beid'] = $_POST['beid'];
			$where['id']=$id;
			$log = $this -> logcheck(array('id' => $_POST['id']),'sj_category_extent_soft',$map,$model);
			//屏蔽软件上排期时报警需求 新增  yuesai
            $AdSearch = D("Sj.AdSearch");
            $shield_error=$AdSearch->check_shield($map['package'],$map['start_at'],$map['end_at']);
            $shield_error.=$AdSearch->check_shield_old($map['package'],$extent_id,0,'sj_category_extent');
            if($shield_error){
                $this -> error($shield_error);
            }
			//复制上线 保留原记录  操作者变成当前操作者
			if($_POST['life']==1)
			{
			    $result = $model->where($where)->select();
			    $map['admin_id'] = $_SESSION['admin']['admin_id'];
			    $map['package']=$_POST['package'];
				$map['extent_id']=$soft['extent_id'];
				$map['create_at']=time();
			    $ret = $model->table('sj_category_extent_soft')->add($map);
				if ($ret || $ret === 0) {
					if ($ret) {
						$this->writelog("市场软件运营推荐-频道列表软件推荐-复制上线了package为{$_POST['package']}的记录，{$log}",'sj_category_extent_soft',$ret,__ACTION__ ,"","add");
					}
					$this->success("复制上线成功！");
				} else {
					$this->error("复制上线失败");
				}
			}
            else
			{
				$ret = $model->where($where)->save($map);
				if ($ret || $ret === 0) {
					$this->assign('jumpUrl', '/index.php/Sj/CategoryExtent/list_soft/extent_id/'. $soft['extent_id']."/list_display/".$_POST['list_display']."/p/{$_POST['p']}");
					$this->writelog("市场软件运营推荐-频道列表软件推荐-编辑了id为{$id}的记录：{$log}",'sj_category_extent_soft',$id,__ACTION__ ,"","edit");
					$this->success('编辑成功');
				} else {
					$this->error('编辑失败');
				}
			}
		} else {
			$util = D("Sj.Util");
            $typelist = $util->getHomeExtentSoftTypeList($soft['type']);
            $this->assign('typelist',$typelist);
			
			$this->assign('soft', $soft);
			$this->display();
		}
	}

	function del_extent()
	{
		$extent_id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $extent_id
		);
		$map = array(
			'status' => 0,
			'update_at' =>time(),
		);
		$model = M('category_extent');

		$model->table('sj_category_extent')->where($where)->save($map);
		$model->table('sj_category_extent_soft')->where($where)->save($map);

		$where = array(
			'parent_id' =>$extent_id
		);
		$res = $model->table('sj_category_extent')->where($where)->field('extent_id')->select();
		$extent_ids = array();
		foreach($res as $v){
			$extent_ids[] = $v['extent_id'];
		}
		if (!empty($extent_ids)) {
			$where = array(
				'extent_id' => array('in', $extent_ids)
			);
			$model->table('sj_category_extent')->where($where)->save($map);
			$model->table('sj_category_extent_soft')->where($where)->save($map);
		}

		$this->writelog('市场软件运营推荐-频道列表软件推荐：删除了id为'.$extent_id.'的区间', 'sj_extent', $extent_id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}

    function release_extent() {
        $extent_id = $_REQUEST['extent_id'];
		$where = array(
			'extent_id' => $extent_id
		);
        $now = time();
		$map = array(
			'release_time' => $now,
		);
		$model = M('category_extent');
		$model->where($where)->save($map);
        $this->writelog("频道运营区间管理：发布了id为{$extent_id}的区间",'sj_category_extent',$extent_id,__ACTION__ ,"","edit");
        $this->success('发布成功');
    }

	function move_soft()
	{
		$selected_ids = $_POST['selected_ids'];
		if (strpos($selected_ids, ',')){
			$selected_ids = substr($selected_ids, 0, strripos($selected_ids, ','));
		}
		$extent_id = $_POST['extent_id'];
		$where = array(
			'id' => array('in' ,$selected_ids)
		);
		$map = array(
			'extent_id' => $extent_id,
			'update_at' =>time(),
		);
		$model = M('category_extent_soft');
		$model->where($where)->save($map);
		$this->assign('jumpUrl', '/index.php/Sj/CategoryExtent/index');
//		$selected_ids = implode(',', $selected_ids);
//		$this->writelog("将id为[{$selected_ids}]的软件移动到了区间{$extent_id}", 'sj_extent_soft');
		$this -> writelog("将id为[{$selected_ids}]的软件移动到了区间{$extent_id}", 'sj_extent_soft', '', __ACTION__, 'rank_config','edit');
		$this->success('移动成功');
	}

	function del_soft()
	{
		$id = $_REQUEST['id'];
		$where = array(
			'id' => $id
		);
		$map = array(
			'status' => 0,
			'update_at' =>time(),
		);
		$model = M('category_extent_soft');
		$model->where($where)->save($map);
		$this->writelog("市场软件运营推荐-频道列表软件推荐:删除了id为[$id]的记录", 'sj_category_extent_soft', $id,__ACTION__ ,"","del");
		$this->success('删除成功');
	}

    function isAjax ()
    {
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest")  return true;
		return false;
    }

	function checkPropAct()
	{
		$extent_id = $_GET['extent_id'];
		$now = time();
		$where = array(
			'extent_id' => $extent_id,
			'status' => 1,
			'start_at' => array('elt',$now),
			'end_at' => array('egt',$now),
		);
		if (isset($_GET['id'])) {
			$where['id'] = array('neq', $_GET['id']);
		}

		$model = M('category_extent_soft');
		$result = $model->where($where)->field('sum(prob) as prob')->find();
		$total_prob = $result['prob'];

		$where = array(
			'extent_id' => $extent_id,
			'status' => 1,
		);
		$result = $model->table('sj_category_extent')->where($where)->find();
		$limit_prob = $result['extent_size'] * 100;
		echo $total_prob > $limit_prob ? 0: 1;
		$result = array(
			'total' => $total_prob,
			'max' => $limit_prob
		);
		exit(json_ecode($result));
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
        if ($type != 3) {
            $general_page_type = '普通';
            if ($type == 1)
                $category_name = '应用-最热';
            else if ($type == 2)
                $category_name = '游戏-最热';
            else if ($type == 4)
                $category_name = '飙升';
			/*else if ($type == 5)
                $category_name = '用户还下载推荐';*/
			else if ($type == 6)
                $category_name = '用户还下载';
			else if ($type == 7)
				$category_name = '应用-最热2';
			else if ($type == 8)
				$category_name = '游戏-最热2';
            // 在第0列和第1列之间新增一列“频道类型”
            // 在第1列和第2列之间新增一列“频道”
            foreach($content_arr as $key => $record) {
                $count = count($record);
                for($i = $count-1; $i > 0; $i--) {
                    $content_arr[$key][$i+2] = $content_arr[$key][$i];
                }
                $content_arr[$key][1] = $general_page_type;
                $content_arr[$key][2] = $category_name;
            }
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'extent_id'     =>  '区间ID',
            'general_page_type' => '频道类型',
            'category_name' => '频道',
            'extent_name'   =>  '区间名',
            'package'  =>   '包名',
            'phone_dis'  =>   '高低配区分展示',
            'old_phone'  =>   '旧版展示(低于V4.4.1)',
            'prob'  =>   '显示概率',
            'start_at'  =>   '开始时间(yyyy/MM/dd)',
            'end_at'  =>   '结束时间(yyyy/MM/dd)',
			'type' =>   '合作形式',
			'beid' => '行为id',
			//批量刷数据库 增加一个创建时间 add by shitingting 2016-11-7
        	'description'	=>	'一句话推荐',
			'create_tm' =>'创建时间',
        	
        );
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
        $expected_words['general_page_type'] = array("" => false, "普通" => 1, "标签" => 2, "常用标签" => 3, "榜单" => 5);
        $expected_words['phone_dis'] = array("" => false, "不做区分" => 1, "仅高配手机展示" => 2, "仅低配手机展示" => 3);
        $expected_words['old_phone'] = array("" => 0, "是" => 1, "否" => 0);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
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
                if ($r_key == 'start_at' || $r_key == 'end_at' ||$r_key == 'create_tm') {
                    if ($r_key == 'start_at') {
                        $type = 0;
                        $hint = '开始';
                    } elseif($r_key == 'end_at') {
                        $type = 1;
                        $hint = '结束';
                    }elseif($r_key == 'create_tm'){
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
                    }// else转换错误，保持原始值，后面的logic_check会校验原始格式
                }
            }
        }
        return $error_msg;
    }

    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check($content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：区间表、区间软件表
        $M_extent_table = 'category_extent';
        $M_extent_soft_table = 'category_extent_soft';
        // 加一下前缀（真正的表名），主要用在join sql里
        $extent_table = 'sj_' . $M_extent_table;
        $extent_soft_table = 'sj_' . $M_extent_soft_table;
        // 获得三个表的model
        $extent_model = M($M_extent_table);
        $extent_soft_model = M($M_extent_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        /*
        // 获得分类（频道）
        $category_map = ContentTypeModel::getCategoryTypesOfCategory();
        */
        $expand = '';
        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $extent_soft_model->where($where)->find();
                $content_arr[$key]['extent_id'] = $find['extent_id'];
                $record['extent_id'] = $find['extent_id'];
            }

            // 检测区间ID
            if (isset($record['extent_id']) && $record['extent_id'] != "") {
                $where = array(
                    'extent_id' => array('EQ', $record['extent_id']),
                    'status' => array('EQ', 1),
                );
                $find = $extent_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "区间位ID【{$record['extent_id']}】无效;");
                } else {
                    //榜单验证
                    $category_type = $find['category_type'];
                    $expand = $find['category_type_expand'];
                    $general_page_type = ContentTypeModel::getGeneralPageType($category_type);
					
                    if($general_page_type==5)
                    {
                        $tmp = explode('_',$category_type);
                        $bdid = $tmp[1];
                        $res = $extent_model->table('sj_list')->where('id='.$bdid)->find();
                            $package = $record['package'];
                            $softinfo = $extent_model->table('sj_soft')->field('category_id')->where('package="'.$package.'"')->find();

                            $cid = str_replace(',','',$softinfo['category_id']);
                            $sql = "SELECT parentid FROM sj_category WHERE category_id = $cid";
                            $ret = $extent_model->query($sql);
                            if($ret[0]['parentid']>3)
                            {
                                $sql = "SELECT parentid FROM sj_category WHERE category_id = (SELECT parentid FROM sj_category WHERE category_id = $cid)";
                                $ret = $extent_model->query($sql);
                            }

                        if($res['type']!=0)
                        {
                           /* if($res['type']!=$ret[0]['parentid'])
                            {
                                $this->append_error_msg($error_msg, $key, 1, "添加失败,软件与榜单限制分类不符;");
                            }*/
                        }else if($ret[0]['parentid']==3)
                        {
                            $this->append_error_msg($error_msg, $key, 1, "添加失败,不能添加电子书类型的软件;");
                        }
                    }


                    if (isset($record['general_page_type'])) {
                        // 批量导入时会set这个字段
                        if ($record['general_page_type'] != '') {
                            // 检查这个区间ID所在的频道是否在这个频道类型中
                            $category_type = $find['category_type'];
                            $general_page_type = ContentTypeModel::getGeneralPageType($category_type);
                            if ($record['general_page_type'] != $general_page_type) {
                                $this->append_error_msg($error_msg, $key, 1, "区间位ID与频道类型不对应;");
                            }
                        } else {
                            $this->append_error_msg($error_msg, $key, 1, "频道类型不能为空;");
                        }
                    }
                    if (isset($record['category_name'])) {
                        // 检查区间ID与频道是否对应
                        $category_name = ContentTypeModel::convertPageType2PageNameOfCategory($find['category_type']);
                        if ($category_name != $record['category_name']) {
                            $this->append_error_msg($error_msg, $key, 1, "区间位ID与频道不对应，该区间ID在频道【{$category_name}】中，不在频道【{$record['category_name']}】里;");
                        }
                    }
                    if (isset($record['extent_name'])) {
                        // 检查区间ID与区间名是否对应
                        if (trim($find['extent_name']) != $record['extent_name']) {
                            $this->append_error_msg($error_msg, $key, 1, "区间位ID与区间名不对应;");
                        }
                    }
                    // 得到该记录区间的cid、oid和pid，并保存起来，方便后面的区间冲突检查
                    // 保证同一频道下所有区间时间不能有冲突，现需求暂不检查属性
                    //$content_arr[$key]['cid'] = $find['cid'];
                    //$content_arr[$key]['oid'] = $find['oid'];
                    $content_arr[$key]['bk_pid'] = $find['pid'];
                    $content_arr[$key]['bk_category_type'] = $find['category_type'];
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "区间位ID不能为空;");
            }
            // 判断应该检查包名还是活动ID
            $content_arr[$key]['which_checked'] = 1;
            /*
            if ($record['extent_type'] == 2 || $record['extent_type'] == 3) {
                // 如果区间为单排或双排区间，则应检查标题和图片
                if (isset($record['title']) && $record['title'] != '') {
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "标题不能为空;");
                }
                if (isset($record['image_url']) && $record['image_url'] != '') {
                } else if (!isset($record['id'])) {
                    $this->append_error_msg($error_msg, $key, 1, "图片不能为空;");
                }

            }
            if ($record['extent_type'] != 1) {
                // 存储内容类型选择
                if (isset($record['content_type']))
                    $content_arr[$key]['which_checked'] = $record['content_type'];
            }
            */
            if ($content_arr[$key]['which_checked'] == 1) {
                // 检查包名是否在sj_soft表里
                if (isset($record['package']) && $record['package'] != "") {
                    $where = array(
                        'package' => $record['package'],
                        'status' => 1,
                        'hide' => array('in', array(0, 1, 1024)),
                    );
                    $find = $soft_model->where($where)->find();
                    if (!$find) {
                        $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                    }
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
                }
            } else if ($content_arr[$key]['which_checked'] == 2) {
                // 检查活动ID是否在sj_activity里
                if (isset($record['activity_id']) && $record['activity_id'] != "") {
                    $where = array(
                        'id' => $record['activity_id'],
                        'status' => 1,
                    );
                    $activity_model = M('activity');
                    $find = $activity_model->where($where)->find();
                    if (!$find) {
                        $this->append_error_msg($error_msg, $key, 1, "活动ID【{$record['activity_id']}】不存在;");
                    }
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "活动ID不能为空;");
                }
            } else if ($content_arr[$key]['which_checked'] == 3) {
                // 检查专题ID是否存在
                if (isset($record['feature_id']) && $record['feature_id'] != "") {
                    $where = array(
                        'feature_id' => $record['feature_id'],
                        //'pid' => $content_arr[$key]['bk_pid'],
                        'status' => 1,
                    );
                    $feature_model = M('feature');
                    $find = $feature_model->where($where)->find();
                    if (!$find) {
                        $this->append_error_msg($error_msg, $key, 1, "专题ID【{$record['feature_id']}】不存在;");
                    }
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "专题ID不能为空;");
                }
            } else {
                // 检查页面是否存在
                if (isset($record['category_type']) && $record['category_type'] != "") {
                    if (!array_key_exists($record['category_type'], $category_map)) {
                        $this->append_error_msg($error_msg, $key, 1, "页面【{$record['category_type']}】不存在;");
                    }
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "页面不能为空;");
                }
            }
			if(!isset($record['game_sdk_version_rule'])){
				// 检查高低配区分展示的值
				if (isset($record['phone_dis']) && $record['phone_dis'] != "") {
				} else {
					$this->append_error_msg($error_msg, $key, 1, "高低配区分展示值不能为空;");
				}
			}

			
            // 检查显示概率是否为数字
            if (isset($record['prob']) && $record['prob'] != "") {
                if (!preg_match("/^\d+$/", $record['prob'])) {
                    $this->append_error_msg($error_msg, $key, 1, "显示概率应为整数;");
                } else if ($record['prob'] > 100) {
                    $this->append_error_msg($error_msg, $key, 1, "显示概率不能大于100;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "显示概率值不能为空;");
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
			 if (isset($record['create_tm']) && $record['create_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['create_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "创建时间日期格式不对;");
                }
				else {
                    $time = strtotime($record['create_tm']);
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
            // 如果填写的区间无效，则不比较
            if (!isset($record1['bk_category_type']))
                continue;
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                if ($record1['which_checked'] == 1) {
                    // 包名不相同
                    if ($record1['package'] != $record2['package']){
                        continue;
                    }else{
                        if($record1['bk_category_type']=='fixed_app_hot'||$record1['bk_category_type']=='fixed_game_hot'){                          
                            continue;
                        }
                    }
                } else if ($record1['which_checked'] == 2) {
                    if ($record1['activity_id'] != $record2['activity_id'])
                        continue;
                } else if ($record1['which_checked'] == 3) {
                    if ($record1['feature_id'] != $record2['feature_id'])
                        continue;
                } else if ($record1['which_checked'] == 4) {
                    if ($record1['category_type'] != $record2['category_type'])
                        continue;
                } else {
                    continue;
                }
                if (!isset($record2['bk_category_type']))
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                // 区间不在一个频道里，则不比较
                if ($record1['bk_category_type'] != $record2['bk_category_type'])
                    continue;
                // 时间是否交叉
                if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                    $k1 = $key1 + 1; $k2 = $key2 + 1;
                    $this->append_error_msg($error_msg, $key1, 1, "投放区间与第{$k2}行有重叠;");
                    $this->append_error_msg($error_msg, $key2, 1, "投放区间与第{$k1}行有重叠;");
                }
            }
        }

        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            // 业务逻辑：如果填写的区间无效，则不比较
            if (!isset($record['bk_category_type']))
                continue;
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            if ($record['which_checked'] == 1) {
            	
            	if($record['bk_category_type']=='fixed_app_hot'||$record['bk_category_type']=='fixed_game_hot'){
            		$data_schedule=$extent_model->where(array('extent_id'=>$record['extent_id'],'status'=>1))->find();
            		// var_dump($data_schedule);die;
            		if($data_schedule['list_display']==1){
            			continue;
            		}
            	}
                // 检查是否与sj_extent_soft_v1表里有相同包名且区间冲突的包
                // 业务逻辑：获得当前记录的信息：package、cid、oid、pid
                $es_package = escape_string($record['package']);
                //$cid = escape_string($record['cid']);
                //$oid = escape_string($record['oid']);
                $pid = escape_string($record['bk_pid']);
                $start_time = escape_string($record['bk_start_time']);
                $end_time = escape_string($record['bk_end_time']);
                // 业务逻辑：获得当前记录的信息：频道
                $category_type = escape_string($record['bk_category_type']);
                // 业务逻辑：构造sql语句，查找出与该记录包名相同、也是在相同属性的区间的所有后台记录
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.package as package, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.package='{$es_package}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}' and {$extent_table}.pid='{$pid}'";
                // 如果有传id过来，说明是编辑，这时要排除此id    如果传过来有life=1则说明是复制上线
                $sql_where_except = "";
                if (isset($record['id'])&&$record['life']!=1) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                if ($expand) {
                    $sql_where .= " and category_type_expand={$expand}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }
            } else if ($record['which_checked'] == 2) {
                // 说明填写的是活动ID
                $es_activity_id = escape_string($record['activity_id']);
                $start_time = escape_string($record['bk_start_time']);
                $end_time = escape_string($record['bk_end_time']);
                $category_type = escape_string($record['bk_category_type']);
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.activity_id as activity_id, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.activity_id='{$es_activity_id}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}'";
                // 如果有传id过来，说明是编辑，这时要排除此id
                $sql_where_except = "";
                if (isset($record['id'])) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['activity_id']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }
            } else if ($record['which_checked'] == 3) {
                // 说明填写的是专题ID
                $es_feature_id = escape_string($record['feature_id']);
                $start_time = escape_string($record['bk_start_time']);
                $end_time = escape_string($record['bk_end_time']);
                $category_type = escape_string($record['bk_category_type']);
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.feature_id as feature_id, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.feature_id='{$es_feature_id}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}'";
                // 如果有传id过来，说明是编辑，这时要排除此id
                $sql_where_except = "";
                if (isset($record['id'])) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['feature_id']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }
            } else {
                // 说明填写的是页面
                $es_category_type = escape_string($record['category_type']);
                $start_time = escape_string($record['bk_start_time']);
                $end_time = escape_string($record['bk_end_time']);
                $category_type = escape_string($record['bk_category_type']);
                $sql_select = "select {$extent_soft_table}.id as id, {$extent_soft_table}.category_type as category_type, {$extent_soft_table}.status as status, {$extent_table}.extent_name, {$extent_soft_table}.start_at as start_at, {$extent_soft_table}.end_at as end_at";
                $sql_from = " from {$extent_soft_table} left join {$extent_table}";
                $sql_on = " on {$extent_soft_table}.extent_id={$extent_table}.extent_id";
                $sql_where = " where {$extent_soft_table}.category_type='{$es_category_type}' and {$extent_soft_table}.start_at <= {$end_time} and {$extent_soft_table}.end_at >= {$start_time} and {$extent_soft_table}.status!=0 and {$extent_table}.status=1 and {$extent_table}.category_type='{$category_type}'";
                // 如果有传id过来，说明是编辑，这时要排除此id
                $sql_where_except = "";
                if (isset($record['id'])) {
                    $except_id = escape_string($record['id']);
                    // 拼接sql，A为下面的$sql里表sj_extent_soft_v1的别名，注意二者需一致
                    $sql_where_except = " and {$extent_soft_table}.id != {$except_id}";
                }
                // 将select、from、on、where、except拼接起来
                $sql = $sql_select . ' '. $sql_from . ' ' . $sql_on . ' ' . $sql_where . ' ' .  $sql_where_except;
                // 执行sql
                $db_records = $extent_soft_model->query($sql);
                // 有冲突的后台记录
                foreach($db_records as $db_key=>$db_record) {
                    $start_at_str = date('Y-m-d H:i:s',$db_record['start_at']);
                    $end_at_str = date('Y-m-d H:i:s',$db_record['end_at']);
                    $status_paused_hint = "";
                    if ($db_record['status'] == 2) {
                        $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                    }
                    $this->append_error_msg($error_msg, $key, 1, "投放区间与后台区间【{$db_record['extent_name']}】里ID为【{$db_record['id']}】，活动ID为【{$db_record['category_type']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                }

            }
        }
        return $error_msg;
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
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_at','end_at','sj_category_extent_soft');
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        foreach($error_msg3 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    // 下载批量导入模版
    function down_moban($type) {
        if ($type == 1) {
            $file_dir = C("ADLIST_PATH") . "yingyongremen_import_moban.csv";
            $file_name = '应用热门';
        }
        else if ($type == 2) {
            $file_dir = C("ADLIST_PATH") . "youxiremen_import_moban.csv";
            $file_name = '游戏热门';
        }
        else if ($type == 3) {
            $file_dir = C("ADLIST_PATH") . "leibiezhiding_import_moban.csv";
            $file_name = '类别置顶';
        }
        else if($type == 4) {
            $file_dir = C("ADLIST_PATH") . "zuixin_import_moban.csv";
            $file_name = '最新';
        }
		/*else if($type == 5) {
            $file_dir = C("ADLIST_PATH") . "haixiazaituijian_import_moban.csv";
            $file_name = '用户还下载推荐';
        }*/
		else if($type == 6) {
            $file_dir = C("ADLIST_PATH") . "haixiazai_import_moban.csv";
            $file_name = '用户还下载';
        }else if($type == 7) {
        	$file_dir = C("ADLIST_PATH") . "yingyongzuiri2_import_moban.csv";
        	$file_name = '应用最热2';
        }else if($type == 8) {
        	$file_dir = C("ADLIST_PATH") . "yingyongzuiri2_import_moban.csv";
        	$file_name = '游戏最热2';
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

    //应用热门批量导入
    function import_softs_AH() {
        $this->import_softs(1);
    }

    //游戏热门批量导入
    function import_softs_GH() {
        $this->import_softs(2);
    }

    //类别置顶批量导入
    function import_softs_CA() {
        $this->import_softs(3);
    }

    //最新顶批量导入
    function import_softs_TN() {
        $this->import_softs(4);
    }
	//下载推荐批量导入
    /*function import_softs_DR() {
        $this->import_softs(5);
    }*/
	//猜你喜欢批量导入
    function import_softs_PR() {
        $this->import_softs(6);
    }
	//应用最热2
    function import_softs_FAH() {
    	$this->import_softs(7);
    }
    //游戏最热2
    function import_softs_FGH() {
    	$this->import_softs(8);
    }
    
    // 批量导入访问的页面节点
    function import_softs($type) {
    	$pid = empty($_GET['pid']) ? 1 : $_GET['pid'];
    	$this->assign('pid', $pid);
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
            $lock_name = 'sj_category_extent_soft_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
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
            $this->writelog("频道列表软件推荐：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->assign('type', $type);
            $this->display("import_softs");
        }
    }

    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        // 要添加数据的表名
        $model = M('category_extent_soft');
        foreach($content_arr as $key => $record) {
            // 根据条件忽视以下值
            if ($record['phone_dis'] == 1) {
                // 忽视old_phone字段（显示在旧版本中）的值
                unset($record['old_phone']);
            }
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			if($record['create_tm'])
			{
				$map['create_at'] = strtotime($record['create_tm']);
				$map['update_at'] = strtotime($record['create_tm']);
				$map['anzhi_ad_type'] = 1;
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
			$map['prob'] = $record['prob'];
			$map['start_at'] = strtotime($record['start_at']);
			$map['end_at'] = strtotime($record['end_at']);
            $map['phone_dis'] = $record['phone_dis'];
            // 赋值，以下为非必填项，有默认值
            $map['old_phone'] = isset($record['old_phone']) ? $record['old_phone'] : 0;
            $map['type'] = isset($record['type']) ? $record['type'] : 0;
            //添加行为id added by shiting
            $map['beid'] = isset($record['beid']) ? $record['beid'] : 0;
			//一句话推荐
            $map['description'] = isset($record['description']) ? $record['description'] : '';
            
            $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }

            // 添加到表中
			if ($id = $model->add($map)) {
                $this->writelog("在区间[{$record['extent_id']}]中添加了软件[{$record['package']}],显示概率为[{$record['prob']}],开始时间为:[{$record['start_at']}],结束时间为:[{$record['end_at']}],合作形式为：[{$record['type']}]的记录",'sj_category_extent_soft',$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} 
			// else {
                // 未知原因添加失败
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_category_extent_soft')){
        	$result_arr['table_name']='sj_category_extent_soft';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
}
