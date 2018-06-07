<?php

class ContentTypeAction extends Action {

    // 灵活运营添加推荐内容的通用模块
    public function showContentType() {
		$model = M();
        $rs = strpos(serialize($_GET),'random_isthickbox');
        if($rs>0){
            unset($_SERVER['HTTP_X_REQUESTED_WITH']);
            $this->assign('isbox',1);
        }
        // 选择推荐内容后添加到的div，必填
        $append_div_id = $_GET['append_div_id'];
        if (!$append_div_id) {
            $this->error("请传递要append到的元素id");
        }
        $this->assign('append_div_id', $append_div_id);
        // 要使用到的推荐内容类型
        $enabled_content_type = $_GET['enabled_content_type'];
		if(!$enabled_content_type)
		{
			if ($_GET['function_from']=="flexible") 
			{
				$enabled_content_type = '1,2,3,4,5,6,7,8,9,10'; //只有灵活运营有预约
			}else{
				// 没有传的话，默认类型全开
				$enabled_content_type = '1,2,3,4,5,6,7';
			}
		}
       
        // 将推荐内容类型字符串去重去空转成数组
        $enabled_content_type_arr = explode(',', $enabled_content_type);
        $enabled_content_type_arr = array_unique($enabled_content_type_arr);
        $enabled_content_type_arr = array_filter($enabled_content_type_arr);
        $this->assign('enabled_content_type', implode(',', $enabled_content_type_arr));
        
        // 存储自定义元素名和编辑传过来的值，其中自定义元素名非必填
        $content_arr = array();
        // 内容类型自定义元素名称
        $content_type_ename = $_GET['content_type_ename'];
        if ($content_type_ename) {
            $content_arr['content_type_ename'] = $content_type_ename;
        }
        // 根据传递的推荐内容判断需要传递哪些参数
        // 每种类型需要传的参数
        $ness_param_arr = array(
            1 => array('package_ename', 'uninstall_setting_ename', 'install_setting_ename', 'start_to_page_ename', 'lowversion_setting_ename','parameter_field'),
            2 => array('activity_id_ename'),
            3 => array('feature_id_ename'),
            4 => array('page_type_ename'),
            5 => array('website_ename', 'website_open_type_ename'),
			6 => array('gift_id'),
			7 => array('strategy_id'),
			8 => array('order_id_ename'),//V6.4预约名称
        	9 => array('used_id'),//V6.4.4
        	10 => array('order_id_ename'),//V6.4.4
        );
        $correct_type_arr = array();
        foreach ($ness_param_arr as $key => $value) {
            $correct_type_arr[] = $key;
        }
        foreach ($enabled_content_type_arr as $value) {
            if (!in_array($value, $correct_type_arr)) {
                $this->error("enabled_content_type参数值仅支持" . implode(',', $correct_type_arr));
            }
        }
        // 检查参数是否传齐
        foreach ($enabled_content_type_arr as $type) {
            $arr = $ness_param_arr[$type];
            foreach ($arr as $param) {
                if (!array_key_exists($param, $_GET)) {
                    // 可以不传，用默认值
                    //$this->error("请传递{$param}参数");
                } else {
                    //$this->assign($param, $_GET[$param]);
                    $content_arr[$param] = $_GET[$param];
                }
            }
        }
        
        //////////////////////////////////////
        // 编辑用到的参数
        $content_type = trim($_GET['content_type']);
        // 编辑用到的参数
        $edit_param_arr = array(
            1 => array('package', 'uninstall_setting', 'install_setting', 'lowversion_setting'),
            2 => array('activity_id'),
            3 => array('feature_id'),
            4 => array('page_type'),
            5 => array('website', 'website_open_type'),
			6 => array('gift_id'),
			7 => array('strategy_id'),
			8 => array('recommend_order_id'),//V6.4预约名称
        	9 => array('used_id'),//V6.4.4
        	10 => array('recommend_order_id'),//V6.4.4
        );
        if ($content_type) {
            //$this->assign('content_type', $content_type);
            $content_arr['content_type'] = $_GET['content_type'];
            // 检查参数是否传齐
            $arr = $edit_param_arr[$content_type];
            foreach ($arr as $param) {
                if (!array_key_exists($param, $_GET)) {
                    $this->error("请传递{$param}参数");
                }
                //$this->assign($param, $_GET[$param]);
                $content_arr[$param] = $_GET[$param];
            }
			if($content_type == 4||$content_type == 5)
			{
				if($_GET['operate_mark'])
				{
					if($_GET['operate_mark']==30)
					{
						$content_arr['operate_mark'] = $_GET['operate_mark'];
						$content_arr['custom_mark'] = $_GET['custom_mark'];
					}
					else
					{
						$content_arr['operate_mark'] = $_GET['operate_mark'];
					}
				}
			}
			if($content_type == 4)
			{
				//if($_GET['page_type']=='fixed_personal_center'||$_GET['page_type']=='otherfixed_activity_list')//对外推广的个人中心 和文字快捷入口的 活动列表
				//{
				//}
				if($_GET['is_auto_connect'])
				{
					$content_arr['is_auto_connect'] = $_GET['is_auto_connect'];
				}
			}
            if($content_type == 4||$content_type == 5)
            {
                if($_GET['parameter_field'])
                {
                    $content_arr['parameter_field'] = $_GET['parameter_field'];
                }
            }
            if ($content_type == 1) {
                if ($_GET['install_setting'] == 4) {
                    if (!array_key_exists('start_to_page', $_GET)) {
                        $this->error("请传递start_to_page参数");
                    }
                    //$this->assign('start_to_page', $_GET['start_to_page']);
                    $content_arr['start_to_page'] = $_GET['start_to_page'];
                }
				$content_arr['parameter_field'] = $_GET['parameter_field'];
            }
        }
		//内容合作  展示频道、标签和合作站点
		$coop_where=array(
			'status'=>1,
			'type' =>array('not in',array(3,4,5)),  //V6.4.1新增加的市场游戏，应用，首页不包括
		);
		$coop_site = $model->table('coop_site')->where($coop_where)->select();
		$this->assign('coop_site',$coop_site);
		$this->assign('is_developer',$_GET['is_developer']);
		$this->assign('function_from',$_GET['function_from']);
		$this->assign('extent_type',$_GET['extent_type']);
        $this->assign('pid',$_GET['pid']);
        $this->assign('content_arr', json_encode($content_arr));
        $this->display('Public::content_type');
    }

    public function showActivity() {
        $model = M();
        $keyword = $_GET['query'];
		$real_keyword = escape_string($keyword);
		$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;

        $where = array(
            'name' => array('like', "%{$real_keyword}%"),
            'status' => 1
        );

        $list = $model->table('sj_activity')->where($where)->select();

        $data = array(
			'query' => $keyword,
			'suggestions' => array(),
			'data' => array(),
		);
		
		//活动类型
		//1、多软件，2、单软件，3、无软件，4、预下载，5、九宫格，6、天降红包雨，7、红包翻翻乐，8、红包叠叠乐
		$activity_types=array('',"多软件","单软件","无软件","预下载","紅包-九宫格","紅包-天降红包雨","紅包-翻翻乐","红包-叠叠乐");
		foreach($list as $v) {
			//红包活动检查是否过期
			if( in_array($v['activity_type'], array(5,6,7,8)) && $v['end_tm'] < time() ) {
				continue;
			}
			$data['suggestions'][] = "{$activity_types[$v['activity_type']]}——{$v['name']}";
			$data['data'][] = $v['name'];
		}

        exit(json_encode($data));
    }
	
    function pub_showGame() {
    	$model = M();
    	$keyword = $_GET['query'];
    	$real_keyword = escape_string($keyword);
    	$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
    	$offset = intval($offset);
    	$size = 100;
    	$time = time();
    	$where = array(
    			'title'		=>	array('like', "%{$real_keyword}%"),
    			'start_tm'	=>	array('elt', $time),
    			'end_tm'	=>	array('egt', $time),
    			'status'	=>	1
    	);
    	
    	$list = $model->table('sj_game_subscriber')->where($where)->select();
    	$data = array(
    			'query' => $keyword,
    			'suggestions' => array(),
    			'data' => array(),
    	);
    	
    	foreach($list as $v) {
    		$data['suggestions'][] = $v['title'];
    		$data['data'][] = $v['title'];
    	}
    	exit(json_encode($data));
    }
    
    function pub_showUsed() {
    	$model = M();
    	$package = $_GET['query'];
    	$package = escape_string($package);
    	$time = time();
    	$where = array(
    			'package'	=>	$package,
    			//'start_tm'	=>	array('elt', $time),
    			//'end_tm'	=>	array('egt', $time),
    			'status'	=>	1,
    			'passed'	=>	2,
    	);
        if(isset($_GET['is_developer'])&&!empty($_GET['is_developer'])){
            if($_GET['extent_type'] == 24){
                $where['show_style'] = 1;
            }elseif($_GET['extent_type'] == 26){
                $where['show_style'] = 2;
            }
        }
    	$data = $model->table('sj_soft_content_explicit')->where($where)->select();
    	if( !empty($data) ) {
    		$ret = array(
    			'code'	=>	1,
    			'data'	=>	$data,
    		);
    	}else {
    		$ret = array(
    				'code'	=>	0,
    				'data'	=>	'',
    		);
    	}
    	exit(json_encode($ret));
    }
    
    
	public function showOrder() {
        $model = M();
        $keyword = $_GET['query'];
		$real_keyword = escape_string($keyword);
		$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;
		$now = time();
        $where = array(
            'title' => array('like', "%{$real_keyword}%"),
            'status' => 1,
			'start_tm' => array('elt', $now),
            'end_tm' => array('egt', $now),
        );

        $list = $model->table('sj_game_subscriber')->where($where)->select();
		//$sql="select a.* from sj_game_subscriber as a left join sj_activity_page as b on a.ap_id = b.ap_id and a.title like '%{$real_keyword}%' and a.status=1 and b.status=1 and a.start_tm <= {$now} and a.end_tm >= {$now}";
		//$list = $model->query($sql);
        $data = array(
			'query' => $keyword,
			'suggestions' => array(),
			'data' => array(),
		);

        foreach($list as $v) {
			$data['suggestions'][] = "{$v['title']}";
			$data['data'][] = $v['title'];
		}
		
        exit(json_encode($data));
    }

    public function showFeature() {
        $pid = isset($_GET['pid'])?$_GET['pid']:1;

        $model = M();
        $keyword = $_GET['query'];
		$real_keyword = escape_string($keyword);
		$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;

        $where = array(
            'name' => array('like', "%{$real_keyword}%"),
            'pid' => array('like', "%,$pid,%"),
            'status' => 1
        );

        $list = $model->table('sj_feature')->where($where)->select();

        $data = array(
			'query' => $keyword,
			'suggestions' => array(),
			'data' => array(),
		);

        foreach($list as $v) {
			$data['suggestions'][] = "{$v['name']}";
			$data['data'][] = $v['name'];
		}

        exit(json_encode($data));
		//$this->assign('offset', $offset + $size);
		//$this->assign('query', $keyword);
		//$this->assign('softs', $softs);
		//$this->display('Public::soft');
    }

    public function showCategoryPage() {
        $model = new Model();
		$extent_type= $_GET['extent_type'];
        $keyword = $_GET['query'];
        $real_keyword = escape_string($keyword);
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;

        $data = array(
            'query' => $keyword,
            'suggestions' => array(),
			'data' => array(),
			//'param'=>array(),
        );

        $reg = "/{$keyword}/";
        $category_list = ContentTypeModel::getCategoryTypes();
        if($_GET['function_from']=='content_cooperation'){
            $category_list=array(
                'otherfixed_gamenewserver_list'=>'游戏新服列表',
                'otherfixed_new_game'=>'新锐游戏',
                // 'otherfixed_activity_list'=>'活动列表',
                'fixed_order_list'=>'预约列表',
                'fixed_content_coop'=>'内容合作',
                'fixed_red_packet_task_list'=>'红包任务列表',
            );
        }
        unset($category_list['']);
		//灵活运营位置多排去掉发现_汉化频道跳转页面
		if($extent_type==5)
		{
			unset($category_list['fixed_discovery_chinesize']);
		}
        foreach ($category_list as $key => $value) {
			if(is_array($value))
			{
				if (preg_match($reg, $value[0])) {
					$data['suggestions'][] = $value[0];
					$data['data'][] = $value[1];
				}
			}
			else
			{
                if($_GET['function_from']=='content_cooperation'){
                        // $data['suggestions'][] = $value;
                        // $data['data'][] = $value;
                        if (preg_match($reg, $value)) {
                            $data['suggestions'][] = $value;
                            $data['data'][] = $value;
                        }
                }else{
                    if (preg_match($reg, $value)) {
                        $data['suggestions'][] = $value;
                        $data['data'][] = $value;
                    }
                }
				
			} 
        }
		//var_dump($data);exit;
        exit(json_encode($data));
		//$this->assign('offset', $offset + $size);
		//$this->assign('query', $keyword);
		//$this->display('Public::soft');
    }

    public function showTagPage() {
        $model = new Model();
        $keyword = $_GET['query'];
        $real_keyword = escape_string($keyword);
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;
        // 从标签model里调用方法查找与关键字相似的标签列表
        $tags_model = D("Sj.Tags");
        $tags = $tags_model->getTaglistbylike($real_keyword, $offset, $size);

        $data = array(
            'query' => $keyword,
            'suggestions' => array(),
			'data' => array(),
        );

        $suf_arr = array('最热');

        foreach ($tags as $v) {
            foreach ($suf_arr as $suf) {
                $data['suggestions'][] = "{$v['tag_name']}-{$suf}";
                $data['data'][] = "{$v['tag_name']}-{$suf}";
            }
        }

        exit(json_encode($data));
		//$this->assign('offset', $offset + $size);
		//$this->assign('query', $keyword);
		//$this->assign('softs', $softs);
		//$this->display('Public::soft');
    }

    public function showCommonTagPage() {
        $model = new Model();
        $keyword = $_GET['query'];
        $real_keyword = escape_string($keyword);

        $data = array(
            'query' => $keyword,
            'suggestions' => array(),
			'data' => array(),
        );
        $suf_arr = array('最新', '最热');

        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;

        // 获得所有有效的三级分类
        $category_model = D('Sj.Category');
        $third_level_category = $category_model->getThirdLevelCatgoryList();
        // 第一步：与关键字匹配上的三级分类记录
        $match_categorys = array();
        foreach ($third_level_category as $third) {
            $name = $third['name'];
            if (preg_match("/{$real_keyword}/", $name)) {
                $match_categorys[] = $third;
            }
        }
        $tags_model = D("Sj.Tags");
        foreach ($match_categorys as $v) {
            if (!$v['tag_ids'])
                continue;
            $tag_ids = $v['tag_ids'];
            $tag_arr = explode(',', $tag_ids);
            foreach ($tag_arr as $tag_id) {
                if (!$tag_id)
                    continue;
                $tag_id = ltrim($tag_id, "j");
                $tag_name = $tags_model->getTagnamebyid($tag_id);
                if ($tag_name) {
                    foreach ($suf_arr as $suf) {
                        $str = "{$v['name']}-{$tag_name}-{$suf}";
                        if (!in_array($str, $data['suggestions'])) {
                            $data['suggestions'][] = $data['data'][] = $str;
                        }
                    }
                }
            }
        }

        // 第二步：与关键字匹配上的常用标签
        $match_tags = $tags_model->getTaglistbylike($real_keyword, $offset, $size);
        foreach ($match_tags as $tag_record) {
            $tag_id = $tag_record['tag_id'];
            $tag_name = $tag_record['tag_name'];
            // 从所有三级分类中查找有此标签的分类
            foreach ($third_level_category as $category_record) {
                $tag_ids = $category_record['tag_ids'];
                if (!preg_match("/,j?{$tag_id},/", $tag_ids)) {
                    continue;
                }
                foreach ($suf_arr as $suf) {
                    $str = "{$category_record['name']}-{$tag_name}-{$suf}";
                    if (!in_array($str, $data['suggestions'])) {
                        $data['suggestions'][] = $data['data'][] = $str;
                    }
                }
            }
        }
        exit(json_encode($data));
		//$this->assign('offset', $offset + $size);
		//$this->assign('query', $keyword);
		//$this->assign('softs', $softs);
		//$this->display('Public::soft');
    }
    
    function showCustomList() {
        $model = new Model();
        $keyword = $_GET['query'];
        $real_keyword = escape_string($keyword);
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;

        $data = array(
            'query' => $keyword,
            'suggestions' => array(),
			'data' => array(),
        );

        $reg = "/{$keyword}/";
        $category_list = ContentTypeModel::getCustomList($name_like);
        foreach ($category_list as $key => $value) {
            if (preg_match($reg, $value)) {
                $data['suggestions'][] = $value;
                $data['data'][] = $value;
            }
        }
        exit(json_encode($data));
		//$this->assign('offset', $offset + $size);
		//$this->assign('query', $keyword);
		//$this->display('Public::soft');
    }
    function showChannelList() {
        $model = new Model();
        $keyword = $_GET['query'];
        $real_keyword = escape_string($keyword);
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
        $offset = intval($offset);
        $size = 100;

        $data = array(
            'query' => $keyword,
            'suggestions' => array(),
            'data' => array(),
        );

        $reg = "/{$keyword}/";
        // $category_list = ContentTypeModel::getCustomLIstChannel();
        $where = array(
            'channel_name' => array('like', "%{$real_keyword}%"),
            'status' => 1,
        );
        $category_list = $model->field('id,channel_name')->table('sj_custom_list_channel')->where($where)->select();

        // var_dump($category_list);
        foreach ($category_list as $key => $value) {
            $custom_list_name = $model->field('*')->table('sj_custom_list_name')->where(array('status'=>1,'channel_id'=>$value['id']))->select();
            if(!$custom_list_name){
                continue;
            }
            if (preg_match($reg, $value['channel_name'])) {
                $data['suggestions'][] = $value['channel_name'];
                $data['data'][] = $value['channel_name'];
            }
        }
        exit(json_encode($data));
        //$this->assign('offset', $offset + $size);
        //$this->assign('query', $keyword);
        //$this->display('Public::soft');
    }
    
    function checkIfPackagExists() {
        $package = trim($_POST['package']);
        $find = ContentTypeModel::checkIfPackagExists($package);
        if ($find) {
            echo 1;
            exit;
        }
        echo 0;
    }
    
    function convertPackage2Softname() {
        $package = trim($_POST['package']);
        $softname = ContentTypeModel::convertPackage2Softname($package);
        if (!$softname)
            echo '';
        echo $softname;
    }
    
    function convertActivityName2ActivityId() {
        $activity_name = trim($_POST['activity_name']);
        $activity_id = ContentTypeModel::convertActivityName2ActivityId($activity_name);
        if (!$activity_id)
            echo '';
        echo $activity_id;
    }
    function convertOrderName2OrderId() {
        $order_name = trim($_POST['order_name']);
        $order_id = ContentTypeModel::convertOrderName2OrderId($order_name);
        if (!$order_id)
            echo '';
        echo $order_id;
    }
	
    function convertActivityId2ActivityName() {
        $activity_id = trim($_POST['activity_id']);
        $activity_name = ContentTypeModel::convertActivityId2ActivityName($activity_id);
        if (!$activity_name)
            echo '';
        echo $activity_name;
    }
	function convertOrderId2OrderName() {
        $order_id = trim($_POST['order_id']);
        $order_name = ContentTypeModel::convertOrderId2OrderName($order_id);
        if (!$order_name)
            echo '';
        echo $order_name;
    }
    
    function convertFeatureName2FeatureId() {
        $feature_name = trim($_POST['feature_name']);
        $ppid = trim($_POST['ppid']);
        $feature_id = ContentTypeModel::convertFeatureName2FeatureId($feature_name,$ppid);
        if (!$feature_id)
            echo '';
        echo $feature_id;
    }
    
    function convertFeatureId2FeatureName() {
        $feature_id = trim($_POST['feature_id']);
        $feature_name = ContentTypeModel::convertFeatureId2FeatureName($feature_id);
        if (!$feature_name)
            echo '';
        echo $feature_name;
    }
    
    function convertPageName2PageType() {
        $general_page_type = trim($_POST['general_page_type']);
        $page_name = trim($_POST['page_name']);
        
        $page_type = ContentTypeModel::convertPageName2PageType($page_name, $general_page_type);
        if (!$page_type) {
            echo '';
        }
        echo $page_type;
    }
    
    function convertPageType2PageName() {
        $page_type = trim($_POST['page_type']);
        $method=trim($_POST['method']);
        $page_name = ContentTypeModel::convertPageType2PageName($page_type,$method);
        if (!$page_name) {
            echo '';
        }
		if(is_array($page_name))
		{
			echo json_encode($page_name);
		}
		else
		{
			echo $page_name;
		}
    }
    
    function pub_convertUsedName2UsedId() {
    	$package = trim($_POST['package']);
    	$used_id = ContentTypeModel::convertUsedName2UsedId($package);
    	if (!$used_id) {
    		echo '';
    	}else {
    		echo $used_id;
    	}
    }
    
    function pub_convertUsedId2UsedName() {
    	$used_id = trim($_POST['used_id']);
    	$used_info = ContentTypeModel::convertUsedId2UsedName($used_id);
    	if( $used_info ) {
    		echo json_encode($used_info);
    	}else {
    		echo '';
    	}
    }
    
    
    function getGeneralPageType() {
        $page_type = trim($_POST['page_type']);
        $general_page_type = ContentTypeModel::getGeneralPageType($page_type);
        echo $general_page_type;
    }
    
    function checkUrl() {
        $website = trim($_POST['website']);
        $ret = ContentTypeModel::check_url($website);
        if ($ret)
            echo 1;
        else
            echo 0;
    }
	function check_gift_id() 
	{
        $gift_id = trim($_POST['gift_id']);
        $ret = ContentTypeModel::check_gift_id($gift_id);
        if ($ret)
            echo 1;
        else
            echo 0;
    }
	function check_strategy_id() 
	{
        $strategy_id = trim($_POST['strategy_id']);
        $ret = ContentTypeModel::check_strategy_id($strategy_id);
        if ($ret)
            echo 1;
        else
            echo 0;
    }
	function getcoopchannel()
	{
		$model = M();
		$id = $_GET['id'];
        $atl2=$_GET['atl2'];
        $where = array(
            'site_id' => $id,
            'status' => 1,
			'type' =>array('in',array(1,2)), //V6.4.1频道类型是普通的和手机清理不显示
        );
        if($atl2==1){
            $where['type']=array('in',array('2'));
        }
        $list = $model->table('coop_channel')->where($where)->select();
		
		exit(json_encode($list));
	}
	function getcooptag()
	{
		$model = M();
		$id = $_GET['id'];
        $where = array(
            'site_id' => $id,
            'status' => 1
        );
        $list = $model->table('coop_site_tag')->where($where)->select();
		
		exit(json_encode($list));
	}
	function coopid2name()
	{
		$model = M();
		$id = $_GET['id'];
		$type = $_GET['type'];
        $where = array(
            'id' => $id,
            'status' => 1,
        );
		if($type==1) //站点
		{
			$list = $model->table('coop_site')->where($where)->find();
			echo $list['website_name'];
			
		}
		elseif($type==2) //频道
		{
			$list = $model->table('coop_channel')->where($where)->find();
			echo $list['channel_name'];
		}
		else //标签
		{
			$list = $model->table('coop_site_tag')->where($where)->find();
			echo $list['tag_name'];
		}
	}
	public function detailurl2title()
	{
		$model = M();
		$url = trim($_POST['url']);
		$where = array(
			'url' => $url,
			'status' =>1,
			'az_status'=>1,
		);
		$list = $model->table('coop_content')->where($where)->find();
		echo $list['title'];
	}
	public function detailtitle2url()
	{
		$model = M();
		$title = trim($_POST['title']);
		$where = array(
			'title' => $title,
			'status' =>1,
			'az_status'=>1,
		);
		$list = $model->table('coop_content')->where($where)->find();
		echo $list['url'];
	}
	public function detailid2url()
	{
		$model = M();
		$id = trim($_POST['id']);
		$where = array(
			'id' => $id,
			'status' =>1,
			'az_status'=>1,
		);
		$list = $model->table('coop_content')->where($where)->find();
		echo $list['url'];
	}
	public function check_site2id()
	{
		$model = M();
		$id = trim($_POST['id']);
		$site_id = trim($_POST['site_id']);
		$where = array(
			'site_id'=>$site_id,
			'id' => $id,
			'status' =>1,
			'az_status'=>1,
		);
		$list = $model->table('coop_content')->where($where)->find();
		if($list['id'])
		{
			echo 1;
		}
	}
	public function check_site2title()
	{
		$model = M();
		$title = trim($_POST['title']);
		$site_id = trim($_POST['site_id']);
		$where = array(
			'site_id'=>$site_id,
			'title' => $title,
			'status' =>1,
			'az_status'=>1,
		);
		$list = $model->table('coop_content')->where($where)->find();
		if($list['id'])
		{
			echo 1;
		}
	}
	public function DetailTitleName() {
        $model = new Model();
        $keyword = $_GET['query'];
        $real_keyword = escape_string($keyword);
		$site_id = $_GET['id'];
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 100;
        // 从标签model里调用方法查找与关键字相似的标签列表
        //$tags_model = D("Sj.Tags");
        //$tags = $tags_model->getTaglistbylike($real_keyword, $offset, $size);
		$title_list = ContentTypeModel::getDetailTitlelike($real_keyword,$site_id,$offset, $size);
		
        $data = array(
            'query' => $keyword,
            'suggestions' => array(),
			'data' => array(),
        );


        foreach ($title_list as $v) {
            $data['suggestions'][] = "{$v['id']}:{$v['content_id_new']}:{$v['title']}";
            $data['data'][] = "{$v['url']}";
        }

        exit(json_encode($data));
		//$this->assign('offset', $offset + $size);
		//$this->assign('query', $keyword);
		//$this->assign('softs', $softs);
		//$this->display('Public::soft');
    }

    //编辑器中内览根据包名获取内容标题
    function pub_showUsed_content_explain() {
        $model = M();
        $package = $_GET['query'];
        $package = escape_string($package);
        $where = array(
            'package'   =>  $package,
            'status'    =>  1,
            'passed'    =>  2,
        );
        $data = $model->table('sj_soft_content_explicit')->where($where)->select();
        if( !empty($data) ) {
            $ret = array(
                'code'  =>  1,
                'data'  =>  $data,
                'img_host'  =>  GAMEINFO_ATTACHMENT_HOST,
                'url_host'  =>  ACTIVITY_URL,
            );
        }else {
            $ret = array(
                'code'  =>  0,
                'data'  =>  '',
            );
        }
        exit(json_encode($ret));
    }
}

?>
