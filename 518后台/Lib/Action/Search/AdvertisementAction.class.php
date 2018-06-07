<?php
/*
 * 活动管理
 */
class AdvertisementAction extends CommonAction {
	private $icon_width = 63;
	private $icon_height = 63;
	private $big_icon_width = 464;
	private $big_icon_height = 274;
	public $product_id = 0;
	public function _initialize() {
        parent::_initialize();
        $this -> product_id = isset($_GET['product_id']) ? $_GET['product_id'] : (isset($_POST['product_id']) ? $_POST['product_id'] : 1);
    }
	//搜索热词
	public function searchkeywords_list_hot(){
		$model = new Model();
		$keyword_type = isset($_GET['keyword_type'])?$_GET['keyword_type']:1;
		$time = time();
		$qu_array=array(
			"1"=>"上升",
			"2"=>"下降",
			"3"=>"持平",
		);
		$show_pic_array=array(
			"0"=>"-",
			"1"=>"显示小图",
			"2"=>"显示大图",
		);
		#产品列表
        $product_model = M();
        $product_list = $product_model ->table('pu_product')->where('status = 1')->findAll();
        $this-> assign ("product_list", $product_list);

		$select_pid = $this -> product_id;

		$this -> assign('select_pid',$select_pid);
		//之前有排序顺序  增加start_tm asc
		$where = "status = 1 and end_tm > $time and keyword_type = '{$keyword_type}' and pid = {$select_pid} ";
		$result = $model -> table('sj_search_keywords') -> where($where) -> order('rank asc,start_tm asc') -> select();

		$count = count($result);
		$util = D("Sj.Util"); 
		foreach($result as $key => $val){
			$val['num'] = $key + 1;
			$val['starts_tm'] = $val['start_tm'];
			$val['key_name']=$qu_array[$val['key_type']];
			$val['show_pic_type']=$show_pic_array[$val['show_pic']];
			if($val['start_tm']){
				$val['start_tm'] = date('Y-m-d',$val['start_tm']);
			}
			if($val['end_tm']){
				$val['end_tm'] = date('Y-m-d',$val['end_tm']);
			}
			$result[$key] = $val;

			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$result[$key]['types'] = $v[0];
				}
			}			//content_type内容类型,1:软件,2:活动,3:专题,4:页面,5:网页,6礼包7攻略8预约9应用内览10游戏预约
			if($val['content_type'] == 0){
				//activity_id
				$result[$key]['content_type'] = "添加推荐";
			}else if($val['content_type'] == 5){
				$result[$key]['content_type'] = "网页-".$val['website'];
			}else if($val['content_type'] == 6){
				$result[$key]['content_type'] = "礼包-".$val['gift_id'];
			}else if($val['content_type'] == 7){
				$result[$key]['content_type'] = "攻略-".$val['strategy_id'];
			}else if($val['content_type'] == 9||$val['content_type'] == 4){
				$used_info = json_decode($val['parameter_field'],true);
				if($val['content_type'] == 4){
					$result[$key]['re_keyword'] = $used_info['re_keyword'];
				}else{
					$result[$key]['content_type'] = "应用内览-".$used_info['softname']."-".$used_info['title'];
				}

			}
		}
		$this -> assign('result',$result);
		$this -> assign('count',$count);
		$this-> assign('keyword_type',$keyword_type);
		$this -> display();
	}	


	//过期热词
	function stale_searchkeywords_out_show(){
	    import("@.ORG.Page2");
		$model = new Model();
		$time = time();
		$qu_array=array(
			"1"=>"上升",
			"2"=>"下降",
			"3"=>"持平",
		);
		$show_pic_array=array(
			"0"=>"-",
			"1"=>"显示小图",
			"2"=>"显示大图",
		);
		$size = 50;
		$param = http_build_query($_GET);
		$where = "end_tm < $time and status = 1 and pid = {$this -> product_id}";
		$count = $model -> table('sj_search_keywords') -> where($where)->count();
		$Page = new Page($count, $size);
		//运营位  开始时间升序 modify by shitingting end_tm desc => start_tm asc
		$result = $model -> table('sj_search_keywords') -> where($where) ->order('start_tm asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		$util = D("Sj.Util"); 
		foreach($result as $key => $val){
			$val['num'] = $key + 1;
			$val['start_tm'] = date('Y-m-d',$val['start_tm']);
			$val['end_tm'] = date('Y-m-d',$val['end_tm']);
			$val['key_name']=$qu_array[$val['key_type']];
			$val['show_pic_type']=$show_pic_array[$val['show_pic']];
			$result[$key] = $val;
			
			$typelist = $util->getHomeExtentSoftTypeList($val['type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$result[$key]['types'] = $v[0];
				}
			}
		}
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('param',$param);
		$this -> assign("result",$result);
		$this -> assign('count',$count);
		$this -> display();
	}


		//更新某个排序
	function edit_rank_to(){
	    if(isset($_GET)){
			$table       = 'sj_search_keywords';
			$field       = 'rank';
			$target_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['rank'];
			$where = array(
				'status' => 1,
			);
			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$target_id,$where,$target_rank);
			//$this -> writelog('更新了extent_id为'.$extent_id.'的区间', 'sj_extent', $extent_id);

		    exit(json_encode($param));
		}
	}


	public function add_searchkeywords_to_show(){
		$model = new Model();
		$time = time();
		// $query = ("select * from sj_search_keywords where b.end_tm > $time and status = 1 order by b.rank");
		$query = ("select * from sj_search_keywords where end_tm > $time and status = 1 order by rank");
		$result = $model->query($query);
		$ma = ("select * from  sj_search_keywords where end_tm > $time and status = 1 order by rank desc limit 1");
		$max = $model->query($ma);
		$count = $max[0]['rank'] + 1;
		$this -> assign('num',$count);
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$keyword_type = $_GET['keyword_type'];
		$this->assign('select_pid',$this->product_id);		
		$this->assign('icon_width',$this->icon_width);
		$this->assign('icon_height',$this->icon_height);
		$this->assign('big_icon_width',$this->big_icon_width);
		$this->assign('big_icon_height',$this->big_icon_height);
		$this->assign('keyword_type', $keyword_type);
		$this->assign('typelist',$typelist);
		$this -> display();
	}


		//添加
	public function add_searchkeywords_to(){
	   
		if (!empty($_POST))
		{  
			$keywords = $_POST['keywords'];
			$package = $_POST['package'];
			$key_type = $_POST['key_type'];
			$show_pic = $_POST['show_pic'];
			if(isset($_POST['type'])){
				$type = $_POST['type'];
			}else{
				$type = 0;
			}
			$start_tm = strtotime(date('Y-m-d H:i:s',strtotime($_POST['fromdate'])));
			$end_tm = strtotime(date('Y-m-d H:i:s',strtotime($_POST['todate'])));
			$today_time = strtotime(date('Y-m-d 00:00:00',time()));     
			$time = time();
		    if(strpos($_POST['fromdate'],":")==false)
			{
			   $_POST['fromdate'] .= ' 00:00:00';
			}
		    if(strpos($_POST['todate'],":")==false)
			{
			   $_POST['todate'] .= ' 23:59:59';
			}
			$start_tm = strtotime(date('Y-m-d H:i:s',strtotime($_POST['fromdate'])));
			$end_tm = strtotime(date('Y-m-d H:i:s',strtotime($_POST['todate'])));
            // tpl（网页）里的名称和数据库字段对应数组
            $column_convert_arr = array(
                'keywords' => 'key_word',
                'package' => 'package',
                'key_type' => 'key_type',
                'fromdate' => 'start_tm',
                'todate' => 'end_tm',
				'rank'  => 'rank',
				'type' =>'type',
				'show_pic'=>'show_pic_type',
				//'keyword_type' => 'keyword_type'
				'is_personalize' => 'is_personalize'
            );
			 // $check_column_arr数组存放_POST/_GET对应数据库字段的值（因为logic_check里的变量名跟数据库字段名一样）
            $check_column_arr = array();
            foreach($column_convert_arr as $key=>$value) {
                if (array_key_exists($key, $_POST)) {
                    $check_column_arr[$value] = $_POST[$key];
                }
            }
            // trim一下
            foreach($check_column_arr as $key=>$value) 
			{
                $check_column_arr[$key] = trim($value);
            }
            // 调用通用的检查函数
            $content_arr = array();
            $content_arr[0] = $check_column_arr;
            $error_msg = $this->logic_check_hotwords_recommend($content_arr);
            $qualified_flag = true;
            foreach($error_msg as $key=>$value) 
			{
                if ($value['flag'] == 1)
                    $qualified_flag = false;
            }
            if (!$qualified_flag) 
			{
                $msg = $error_msg[0]['msg'];
                $this->error($msg);
            }
		}
		$model = new Model();
		if($package == '为空表示不关联')
		{
			$package = '';
			$show_pic = 0;
		}
		 
		$been_where['_string'] = "status = 1 and end_tm >= ".time()."";
		$been_result = $model -> table('sj_search_keywords') -> where($been_where) -> select();
		$ma = ("select * from sj_search_keywords where end_tm > $time and status = 1 order by rank desc limit 1");
		$max = $model->query($ma);
		$count = $max[0]['rank'] + 1;
		/*if(count($been_result) >= 60){
			$this -> error('搜索热词不可超过60个');
		}*/
		$data = array(
			'key_word' => $keywords,
			'package' => $package,
			'update_tm' => time(),
			'rank' => $count,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'status' => 1,
			'key_type' => $key_type,
			'type' =>$type,
			'show_pic' =>$show_pic,
			'admin_id'=>$_SESSION['admin']['admin_id'],
		);
		$this->save_searchkeywords_extra($data);
		if($data['package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm'],1);
			if($shield_error){
			    $this -> error($shield_error);
			}
		}
		$data['pid'] = $this -> product_id;	
		$result = $model -> table('sj_search_keywords') -> add($data);
		if($result){	
			$this->writelog("广告管理-搜索热词管理V5-添加热词{$keywords},关联包名为{$package}",'sj_search_keywords',$result,__ACTION__ ,"","add");
			$keyword_type = isset($_POST['tab_type'])?$_POST['tab_type']:1;
			$this->assign('jumpUrl', '/index.php/Search/Advertisement/searchkeywords_list_hot/keyword_type/'.$keyword_type);
			$this->success('添加成功');
		}else{
			$this -> error('添加失败');
		}
	}

	function save_searchkeywords_extra(&$data){
		$package = $_POST['package'];
		$show_pic = $_POST['show_pic'];
		$keyword_type = $_POST['keyword_type'];
		if((!$package&&$show_pic==1&&$keyword_type==1)||($keyword_type==2)){
			//类型为热词，未添加包名，选择小图片时图标为必填项
			$required = true;
		}else{
			$required = false;
		}
		if($keyword_type==1){
			$width = $this->icon_width;
			$height = $this->icon_height;
		}else{
			$width = $this->big_icon_width;
			$height = $this->big_icon_height;
		}
		$res = process_image($required,'icon_url', $width, $height, '图标', 'jpg|png|gif');
		if($res['code']==0){
			$this->error($res['msg']);
		}else{
			$data['keyword_type'] = $keyword_type;
			$data['color'] = $_POST['color']?$_POST['color']:'#545454';
			$data['icon_url'] = $res['msg']?$res['msg']:'';
//			$data['show_keyword'] = $_POST['show_keyword'];
			if($show_pic == 2){
				//选择显示大图时存在是否更换位置
				$data['change_rank'] = $_POST['change_rank'];
			}else{
				$data['change_rank'] = 0;
			}
			$data['is_personalize'] = $_POST['is_personalize'];
		}
	}

	//编辑显示页面
	function edit_searchkeywords_to_show(){
		$model = new Model();
		$time = time();
		$qu_array=array(
			"1"=>"上升",
			"2"=>"下降",
			"3"=>"持平",
		);
		$id=$_GET['id'];
		$life=$_GET['life'];
		$keyword_type = $_GET['keyword_type'];
		if($id&&$life==1)
		{
		  $result = $model -> table('sj_search_keywords') -> where("id= $id") -> select();
		}
		else
		{
		  $result = $model -> table('sj_search_keywords') -> where("end_tm > $time and status = 1 and keyword_type = '{$keyword_type}' and pid = {$this -> product_id}") -> order('rank') -> select();
		}
		
		$count = count($result);
		$util = D("Sj.Util");
		foreach($result as $key => $val){
			$val['start_tm'] = date('Y-m-d',$val['start_tm']);
			$val['end_tm'] = date('Y-m-d',$val['end_tm']);
			$val['key_name']=$qu_array[$val['key_type']];
			$typelist[$key] = $util->getHomeExtentSoftTypeList($val['type']);
			$result[$key] = $val;
		}
		$domain_url = ATTACHMENT_HOST;
		$this -> assign('select_pid',$this->product_id);
		$this->assign('domain_url', $domain_url);
		$this->assign('keyword_type',$keyword_type);
		$this->assign('typelist',$typelist);
		$this -> assign("count",$count);
		$this -> assign("result",$result);
		$this->assign('icon_width',$this->icon_width);
		$this->assign('icon_height',$this->icon_height);
		$this->assign('big_icon_width',$this->big_icon_width);
		$this->assign('big_icon_height',$this->big_icon_height);
		$this -> display();
	}



	//执行编辑
	function update_searchkeywords_to(){
		$model = new Model();
		if($_POST['is_contenttye'] == 1){
			$this->pub_add_content();
			exit;	
		}
		$package = $_POST['package'];
		$key_word = $_POST['key_word'];
		$fromdate = $_POST['fromdate'];
		$todate = $_POST['todate'];
		$key_type = $_POST['key_type'];
		$hot_id = $_POST['hot_id'];
		$id = $_POST['id'];
		$rank = $_POST['rank'];
		$type = $_POST['type'];
		$show_pic = $_POST['show_pic'];
		$keyword_type = $_POST['keyword_type'];
//		$show_keyword = $_POST['show_keyword'];
		$color = $_POST['color'];
		$is_personalize = $_POST['is_personalize'];
		foreach($is_personalize as $k=>$v){
			if($v==2){
				array_splice($package, $k, 0, '');
				array_splice($key_word, $k, 0, '');
				array_splice($color, $k, 0, '');
				array_splice($show_pic, $k, 0, 0);
				array_splice($type, $k, 0, 0);
			}
		}
		foreach($rank as $k=>$v){
			$rank_data[]['rank'] = $v;
		}
		foreach ($fromdate as $k=>$v){
			$rank_data[$k]['fromdate'] = $v;	
		}
		foreach ($todate as $k2=>$v2){
			$rank_data[$k2]['todate'] = $v2;	
		}
		foreach($type as $k3=>$v3){
			$rank_data[$k3]['type'] = $v3;
		}
		
		foreach($rank_data as $k=>$v){
			$new_rank[$v['rank']][] = $v;
		}
		foreach($new_rank as $key =>$val){
			if($key==0 || $key<1){
				$this -> error("您填写的排序有问题");
				exit;

			}
		}

		/*foreach($new_rank as $k=>$val)
		{
			if(count($new_rank[$k])>1)
			{			
				$this -> error("排序发生冲突");
				exit;
			}
		}*/

		$r = true;
		
		foreach ($new_rank as $k=>$v){
			foreach($v as $key=>$val){
				$r = $this->check_xx($v,$val,$key);
				if ($r == false) {
					break;
				}
			}
			if ($r == false) {
				break;
			}
		}
		if($r==false){
			$this -> error("当前排期内排序发生冲突");
			exit;
		}

		
		foreach($rank as $k=>$v){
			if($v==''){
				$this -> error("排序不能为空");
			}
		}

		foreach ($location as $key1 => $value) {
			$data[]['location'] = $value;
		}
		foreach ($fromdate as $k=>$v){
			$data[$k]['fromdate'] = $v;	
		}
		foreach ($todate as $k2=>$v2){
			$data[$k2]['todate'] = $v2;	
		}
		

		foreach($show_pic as $k_pic =>$v_pic)
		{
			$show_pic[$k_pic] = $v_pic;
		}


		foreach($_POST['change_rank'] as $cr_k=>$cr_v){
			//选择大图时有是否更换位置
			if($_POST['show_pic'][$cr_k]==2){
				$change_rank[$cr_k] = $cr_v;
			}else{
				$change_rank[$cr_k] = 0;
			}
		}
		$icon_url = array();
		foreach($_FILES['icon_url']['tmp_name'] as $f_k=>$f_v){
			$file = array(
				'tmp_name' => $f_v,
				'name' => $_FILES['icon_url']['name'][$f_k]
			);
			$required = false;
			if(empty($_POST['old_icon_url'][$f_k])&&	($show_pic[$f_k]==1&&empty($package[$f_k])||$keyword_type[$f_k]==2)){
				//未添加包名，选择小图时，图标必须上传
				$required = true;
			}
			if($keyword_type[$f_k]==1){
				$width = $this->icon_width;
				$height = $this->icon_height;
			}else{
				$width = $this->big_icon_width;
				$height = $this->big_icon_height;
			}
			$res = process_image($required,$file, $width, $height, '图标', 'jpg|png|gif',true);

			if($res['code']==0){
				$this->error($res['msg']);
			}
			$icon_url[] = $res['msg'];
		}

		foreach($package as $key => $val){
			if($val == "为空表示不关联"||empty($val))
			{
				$val = "";
				//$show_pic[$key] = 0;//只有关联包名的时候才能显示选择大图或者小图
			}
			else
			{
				if($show_pic[$key]==0&&$keyword_type==1)
				{
					$this -> error("关联包必须选择显示图片样式");
				}
			}
			$package[$key] = $val;
		}
	
		//$id = $_POST['id'];
		foreach($package as $key => $val){
			$been_result = $model -> table('sj_soft') -> where(array('package' => $val,'hide' => 1)) -> select();
			if($val && $val != "为空表示不关联"){
				if(!$been_result){
					$this -> error("关联包名'{$val}'不存在");
				}
			}
		}
		foreach($key_word as $key => $val){
			if($is_personalize[$key]!=2&&trim(empty($val))){
				$this -> error("搜索热词推荐不能为空");
			}
//			if($keyword_type[$key]==2){
//				$max_len = 30;
//				$max = 15;
//			}else{
//				$max_len = 20;
//				$max = 10;
//			}
//			if($this -> strlen_az($val) > $max_len || $this -> strlen_az($val) < 1){
//				$str = "请填写不超过{$max}个汉字的搜索热词";
//				$this -> error($str);
//			}
//			if(!preg_match("/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u",$val)){
//				$this -> error("搜索热词'{$val}'不可含有特殊字符");
//			}
		}
		foreach($rank as $key =>$val){

		}
		foreach($fromdate as $key => $val){
			if(!$val || !$todate[$key]){
				$this -> error("请填写开始时间和结束时间！");
			}
			$vals = strtotime(date('Y-m-d 00:00:00',strtotime($val)));
			$eval = strtotime(date('Y-m-d 23:59:59',strtotime($todate[$key])));
			$start_tm[$key] = $vals;
			$end_tm[$key] = $eval;
		}
		foreach($start_tm as $key => $val){
			if($val > $end_tm[$key]){
				$this -> error("开始时间不能大于结束时间！");
			}
		}
		  //已过期的信息复制上线判断
		if($_POST['life']==1)
		{
		  $time_now = strtotime(date('Y-m-d 00:00:00',time()));
		  if($eval<time())
		  {
			$this->error("您修改的复制上线的日期还是无效日期");
		  }
		  if($vals <$time_now)
		  {
		    $this->error("您修改的复制上线的开始日期必须大于当前日期");
		  }
		}
		$my_result = $model -> table('sj_search_keywords') -> where(array('status' => 1,'pid' => $this -> product_id)) -> select();
		
		foreach($id as $key => $val){

		//检查排序是否冲突
		   if($_POST['life']==1)
		    {
			  $searchkey_have_where = "key_word = '{$key_word[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >= {$start_tm[$key]} and end_tm >= ".time()." and status = 1";
			}
			else
			{
			  $searchkey_have_where = "key_word = '{$key_word[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >= {$start_tm[$key]} and end_tm >= ".time()." and status = 1 and id != {$val}";
			}

			$searchkey_have_where .= ' and pid '.$this -> product_id;
			$searchkey_have_result = $model -> table('sj_search_keywords') -> where($searchkey_have_where) -> select();
			
			if($searchkey_have_result){
				if($package[$key]){
					$this -> error("当前排期内已存在该搜索热词{$key_word[$key]},包名为{$package[$key]}！");
				}else{
					$this -> error("当前排期内已存在该搜索热词{$key_word[$key]}");
				}
			}


			if($package[$key]){
				if($_POST['life']==1)
				{
				  $package_have_where = "package = '{$package[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >={$start_tm[$key]} and end_tm >=".time()." and status = 1";
				}
				else
				{
				  $package_have_where = "package = '{$package[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >={$start_tm[$key]} and end_tm >=".time()." and status = 1 and id != {$val}";
				}
				$package_have_where .= ' and pid '.$this -> product_id;
				$package_have_result = $model -> table('sj_search_keywords') -> where($package_have_where) -> select();

				if($package_have_result){
					$this -> error("当前排序内已存在该关联包名{$package[$key]},搜索热词为{$key_word[$key]}！");
				}
			}
		
		//编辑已过期，排序改变
		
			if($end_tm[$key] < time()){
				$my_where['id'] = $val;
				$my_result = $model -> table('sj_search_keywords') -> where($my_where) -> select();
				$need_where['_string'] = "rank > {$my_result[0]['rank']} and status = 1 and end_tm >= ".time()."";
				$need_result = $model -> table('sj_search_keywords') -> where($need_where) -> select();
				foreach($need_result as $k => $v){
					$the_where['id'] = $v['id'];
					$the_data = array(
						'rank' => $v['rank'] - 1
					);
					$the_result = $model -> table('sj_search_keywords') -> where($the_where) -> save($the_data);
					
				}
			}

			$where['id'] = $val;
			$data = array(
				'key_word' => $key_word[$key],
				'package' => $package[$key],
				'start_tm' => $start_tm[$key],
				'end_tm' => $end_tm[$key],
				'update_tm' => time(),
				'rank' =>$rank[$key],
				'key_type' => $_POST['key_type'][$key],
				'type' => $type[$key],
				'show_pic' => $show_pic[$key],
				'admin_id'=>$_SESSION['admin']['admin_id'],
				'keyword_type' => $keyword_type[$key],
				'color' => $color[$key]?$color[$key]:'#545454',
				'change_rank' => $change_rank[$key],
//				'show_keyword' => $show_keyword[$key]
				'is_personalize' => $is_personalize[$key],
				'pid' => $this -> product_id,
			);

			if(!empty($icon_url[$key])){
				$data['icon_url'] = $icon_url[$key];
			}

			if($data['keyword_type']==1&&($data['show_pic']==0||$data['show_pic']==2)) $data['icon_url'] = '';
			$hotid = $hot_id[$key];
			$where_hot['hot_id'] = $hotid;
			if($data['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm'],1);
				if($shield_error){
				    $this -> error($shield_error);
				}
			}
			
			$log_result = $this -> logcheck(array('id' => $val),'sj_search_keywords',$data,$model);
			if($_POST['life']==1)
		    {
				$result = $model -> table('sj_search_keywords') -> add($data);
				$log_id =  implode(',',$id);
				
				if($result){				
					$this -> writelog("搜索热词管理V5_已复制上线搜索热词{$val}".$log_result,'sj_search_keywords',$val,__ACTION__ ,"","add");
				}
			}
			else
			{
			  $result = $model -> table('sj_search_keywords') -> where($where) -> save($data);
			  $log_id =  implode(',',$id);
				if($result)
				{				
				  $this -> writelog("搜索热词管理V5_已编辑搜索热词{$val}".$log_result,'sj_search_keywords',$val,__ACTION__ ,"","edit");
				}
			}
			$all_result[] = $result;
		}

		if($all_result){
			$this->assign('jumpUrl', '/index.php/Search/Advertisement/searchkeywords_list_hot/keyword_type/'.$_POST['keywordtype']);
			$this->success('编辑成功');
		}else{
			$this -> error('编辑失败');
		}
	}

	//判断重复
	function check_xx($v,$val,$k) {
		$start = strtotime($val['fromdate']);
		$end = strtotime($val['todate']);
		$r = true;

		foreach ($v as $key => $value) {
			if ($k != $key) {
				$s = strtotime($value['fromdate']);
				$e = strtotime($value['todate']);

				if (($start >= $s && $start <= $e) || ($end >= $s && $end <= $e) || ($end >= $e && $start <= $s)) {
					$r = false;
					break;
				}
			}
		}

		return $r;
	}



	//删除
	function delete_searchkeywords_to(){
		$model = new Model();
		$id = $_GET['id'];
		$hot_id = $_GET['hot_id'];
		if(!empty($hot_id)){
			$jump = 1;
		}else{
			$jump = 2;
		}
		$data['status'] = 0;
		$data['update_tm'] =time();
		$where['id'] = $id;
		$my_result = $model -> table('sj_search_keywords') -> where($where) -> select();
		/*
		if($my_result[0]['end_tm'] >= time()){
			$need_where['_string'] = "rank > {$my_result[0]['rank']} and status = 1 and end_tm >= ".time()."";
			
			$need_result = $model -> table('sj_search_keywords') -> where($need_where) -> select();
			
			foreach($need_result as $key => $val){
				

				$update_where['id'] = $val['id'];
				$update_where['status'] = 1;
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table('sj_search_keywords') -> where($update_where) -> save($update_data);
			}
		}
		*/
		$log1 = $this -> logcheck(array('id' => $id),'sj_search_keywords',$data,$model);
		$result = $model -> table('sj_search_keywords') -> where($where) -> save($data);
		$productid_param = '/product_id/'.$this -> product_id;
		if($result){
			$this -> writelog("搜索热词管理V5_已删除id为{$id}的搜索热词".$log1,'sj_search_keywords',$id,__ACTION__ ,"","del");
			if($my_result[0]['end_tm'] >= time()){
				if($jump==1){
					$this->assign('jumpUrl', '/index.php/Search/Advertisement/searchkeywords_list_hot/keyword_type/'.$my_result[0]['keyword_type'].$productid_param);
				}else if($jump==2){
					$this->assign('jumpUrl', '/index.php/Search/Advertisement/stale_searchkeywords_out_show'.$productid_param);
				}
			}else{
				if($jump==1){
					$this->assign('jumpUrl', '/index.php/Search/Advertisement/searchkeywords_list_hot/keyword_type/'.$my_result[0]['keyword_type'].$productid_param);
				}else if($jump==2){
					$this->assign('jumpUrl', '/index.php/Search/Advertisement/stale_searchkeywords_out_show'.$productid_param);
				}
			}
			$this->success('删除成功');
		}else{
			$this -> error('删除失败');
		}
	}

	function check_package(){
		$model = new Model();
		$package = $_GET['package'];
		if($package){
			$have_been = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> select();
			if(!$have_been){
				echo json_encode(1);
				return json_encode(1);
			}else{
				echo json_encode(2);
				return json_encode(2);
			}
		}else{
			echo json_encode(2);
			return json_encode(2);
		}
	}

	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}

        //已屏蔽
        function searchkeywords_list_shield()
        {
            $model = new Model();

            $id = $_GET['id'];
            $info = $model->table('sj_keyword_shield')->where("id=$id")->find();

            if(isset($_GET['id']))
            {
                $this->assign('id',$id);
                $this->assign('word',$info['word']);
                $this->assign('begin_tm',date('Y-m-d',$info['begin_tm']));
                $this->assign('end_tm',date('Y-m-d',$info['end_tm']));
            }

            $rs = $model->table('sj_keyword_shield')->where('status=1')->order('begin_tm asc')->select();
            if($this->isAjax())
            {
                $id = $_POST['id'];
                $pbword = $_POST['pbword'];
                $begin_tm = $_POST['begin_tm'];
                $end_tm = $_POST['end_tm'];

                if(strtotime($begin_tm)>strtotime($end_tm)){
                    echo 3;exit(0);
                }

                /*
                $retinfoarr = $model->table('sj_keyword_shield')->where("word = '$pbword' and status=1")->select();
                foreach($retinfoarr as $retinfo)
                {
                    if(!empty($retinfo['id']))
                    {
                        if(strtotime($begin_tm)>=$retinfo['begin_tm']&&strtotime($begin_tm)<=$retinfo['end_tm']){
                            echo 4;exit(0);
                        }
                        if(strtotime($end_tm)>=$retinfo['begin_tm']&&strtotime($end_tm)<=$retinfo['end_tm']){
                            echo 4;exit(0);
                        }
                    }
                }
                */

                if(empty($id))
                {
                    //新增
                    $data = array();
                    $data['word'] = $pbword;
                    $data['begin_tm'] = strtotime($begin_tm);
                    $data['end_tm'] = strtotime($end_tm);
					$data['admin_id'] = $_SESSION['admin']['admin_id'];
                    $res =  $model->table('sj_keyword_shield')->add($data);
	            $this -> writelog('新增了屏蔽热词:'.$pbword.',开始时间'.$begin_tm.',结束时间'.$end_tm,'sj_keyword_shield',$res,__ACTION__ ,"","add");
                    echo 1;exit(0);
                }else
                {
                    //修改
                    $data = array();
                    $data['word'] = $pbword;
                    $data['begin_tm'] = strtotime($begin_tm);
                    $data['end_tm'] = strtotime($end_tm);
					$data['admin_id'] = $_SESSION['admin']['admin_id'];
                    $log_all_need = $this->logcheck(array('id' => $id), 'sj_keyword_shield', $data, $model);
                    $model->table('sj_keyword_shield')->where("id = $id")->save($data);
                    $msg = "编辑了id为{$id}的屏蔽热词,";
                    $msg .= $log_all_need;
                    $this->writelog($msg,'sj_keyword_shield',$id,__ACTION__ ,"","edit");
	            //$this -> writelog('编辑了id为'.$id.'的屏蔽热词为:'.$pbword.',开始时间'.$begin_tm.',结束时间'.$end_tm);
                    echo 2;exit(0);
                }
            }
            $this->assign('list',$rs);
            $this->display();
        }

        function del_keywords_shield()
        {
            if($this->isAjax())
            {
                $model = new Model();
                $id = $_POST['id'];
                $model->table('sj_keyword_shield')->where("id = $id")->save(array('status'=>0));
	        $this -> writelog('删除了id为'.$id.'的屏蔽热词','sj_keyword_shield',$id,__ACTION__ ,"","del");
                echo 1;exit(0);
            }
        }
	// 下载批量导入模版
    function down_moban($type) 
	{
        if ($type == 'hotwords_recommend') 
		{
            $file_dir = C("ADLIST_PATH") . "sousuorecituijian_import_moban.csv";
            $file_name = '搜索热词推荐';
        } 
        if (file_exists($file_dir)) 
		{
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode($file_name . "批量导入模版") . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } 
		else 
		{
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }	
	function import_softs_hotwords_recommend() 
	{
		if ($_GET['down_moban']) 
		{
			$this->down_moban('hotwords_recommend');
		} 
		else if ($_FILES) 
		{
            $err = $_FILES["upload_file"]["error"];
            if ($err) 
			{
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
			
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") 
			{
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
			
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } 
			else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check_hotwords_recommend($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_search_keywords_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array_hotwords_recommend($content_arr);
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
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("搜索热词推荐：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } 
			else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } 
		else 
		{
            $this->display("import_softs_hotwords_recommend");
        }
    }
	// 第一行标题列忽略，只保存之后的内容
	function import_file_to_array($file) 
	{
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
	function import_array_convert_and_check_hotwords_recommend(&$content_arr) 
	{
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check_hotwords_recommend($content_arr);
        // 文件转换数据后的检查（搜索热词、包名是否冲突，排序是否有效等）
        $error_msg2 = $this->logic_check_hotwords_recommend($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_tm','end_tm','',1);
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
	
	// 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check_hotwords_recommend(&$content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) 
		{
            $this->init_error_msg($error_msg, $key);
        }
		
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'key_word'     =>  '搜索热词',
			'package'      =>  '关联应用包名(没有为空)',
            'rank'         =>  '所在位置',
            'start_tm'     =>  '开始时间(yyyy/MM/dd)',
            'end_tm'       =>  '结束时间(yyyy/MM/dd)',
            'key_type'     =>  '趋势',
			'type'         =>  '合作形式',
			'show_pic_type'=>  '图片展示（显示大图/显示小图）'
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) 
		{
            foreach($record as $r_key=>$r_record) 
			{
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) 
		{
            $new_key[] = $key;
        }
		
        foreach($content_arr as $key=>$record) 
		{
            foreach($new_key as $new_key_key=>$new_key_value) 
			{
                if (isset($record[$new_key_key])) 
				{
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        // 业务逻辑：检查列填写是否为预期内容（固定的内容），如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 有些字段输入为空时是合法的，有些字段输入为空不允许，当为空不允许时，将其值设为false以作区别
        $expected_words['key_type'] = array("" => false, "上升" => 1, "下降" => 2, "持平" => 3);
		$expected_words['show_pic_type'] = array("" => "", "显示小图" => 1, "显示大图" => 2);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
        foreach($content_arr as $key=>$record) 
		{
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) 
			{
                if (array_key_exists($r_key, $expected_words)) 
				{
                    if (!array_key_exists($r_value, $expected_words[$r_key])) 
					{
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } 
					else 
					{
                        $tmp = $expected_words[$r_key][$r_value];
                        // 检查是否为false，如果不是，则表示可以为空，替换成相应的数字，否则不处理，即还是为空，在logic_check_hotwords_recommend()里会进行非空值判断
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 判断开始时间和结束时间，去掉T如果没有精确到秒后面加上00:00:00,23:59:59
                if ($r_key == 'start_tm') 
				{
				    if($r_value!="")
				    {
					  if(strpos($content_arr[$key][$r_key],"T")==0)
					  {
					    $content_arr[$key][$r_key]=substr($content_arr[$key][$r_key], 1);
					  }
					  if(strpos($content_arr[$key][$r_key],"/")==true)
					  {
						$content_arr[$key][$r_key]=str_replace("/","-",$content_arr[$key][$r_key]);
					  }
					  if(strpos($content_arr[$key][$r_key],":")==false)
					  {
						$content_arr[$key][$r_key] .= ' 00:00:00';
					  }
				    }
                } 
				else if ($r_key == 'end_tm') 
				{
				   if($r_value!="")
				    {
					   if(strpos($content_arr[$key][$r_key],"T")==0)
					   {
					    $content_arr[$key][$r_key]=substr($content_arr[$key][$r_key], 1);
					   }
					   if(strpos($content_arr[$key][$r_key],"/")==true)
					   {
						$content_arr[$key][$r_key]=str_replace("/","-",$content_arr[$key][$r_key]);
					   }
					   if(strpos($content_arr[$key][$r_key],":")==false)
					   {
						$content_arr[$key][$r_key] .= ' 23:59:59';
					   }
                    }    
			    }
            }
        }
        return $error_msg;
    }
	
	// 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) 
	{
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) 
	{
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
	
	 // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check_hotwords_recommend($content_arr) 
	{
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：获得热词表model
        $hotwords_model = M("search_keywords");
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) 
		{
            // 检查搜索热词推荐是否为空
            if (isset($record['key_word']) && !empty($record['key_word']))
			{
            } 
			else 
			{
				if($record['is_personalize']!=2)
                $this->append_error_msg($error_msg, $key, 1, "搜索热词推荐不能为空;");
            }
            // 检查当前位置是否为数字
            if (isset($record['rank']) && !empty($record['rank'])) 
			{
                if (!preg_match("/^\d+$/", $record['rank'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "顺序应为整数;");
                }
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "顺序不能为空;");
            }
			//检查趋势是否为空
			if (isset($record['key_type']) && !empty($record['key_type'])) 
			{
            } 
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "搜索热词推荐趋势不能为空;");
            }
            // 检查开始时间
            if (isset($record['start_tm']) && $record['start_tm'] != "") 
			{
			    if(strpos($record['start_tm'],":")==false)
				{
				 $record['start_tm'].=' 00:00:00';
				}
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_tm'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } 
				else 
				{  
				    $time = strtotime($record['start_tm']);
                    if ($time) {
                        $content_arr[$key]['bk_start_tm'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            }
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_tm']) && $record['end_tm'] != "") 
			{
			   if(strpos($record['end_tm'],":")==false)
				   {
                    $record['end_tm'] .= ' 23:59:59';
				   }
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_tm'])) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } 
				else 
				{
				    $time = strtotime($record['end_tm']);
                    if ($time) {
                        $content_arr[$key]['bk_end_tm'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            }
			else 
			{
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_tm']) && isset($content_arr[$key]['bk_end_tm'])) 
			{
                if ($content_arr[$key]['bk_start_tm'] > $content_arr[$key]['bk_end_tm']) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
			//检查开始时间是否小于当前时间
			if (isset($content_arr[$key]['bk_start_tm'])) 
			{
                if (date("Y-m-d",$content_arr[$key]['bk_start_tm']) < date("Y-m-d",time())) 
				{
                    $this->append_error_msg($error_msg, $key, 1, "开始时间不能小于当前时间;");
                }
            }
//			if($content_arr[$key]['keyword_type']==2){
//				$max_len = 30;
//				$max = 15;
//			}else{
//				$max_len = 20;
//				$max = 10;
//			}
			//检查搜素热词不能超过10个
//			if($this->strlen_az($content_arr[$key]['key_word'])>$max_len||$this->strlen_az($content_arr[$key]['key_word'])<1)
//			{
//				$str = "请填写不超过{$max}个汉字的搜索热词";
//			 	$this->append_error_msg($error_msg, $key, 1, $str);
//			}
			//检查搜索热词只能为汉字，数字，英文
			//v6.4.7 支持字符
//		  if(!preg_match("/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u",$content_arr[$key]['key_word']))
//		  {
//			 $this ->append_error_msg($error_msg, $key, 1, "关键字只能为汉字，数字，英文;");
//		  }
		$model = new Model(); 
		$soft_result = $model -> table('sj_soft') -> where(array('package' => $content_arr[$key]['package'],'hide' => 1)) -> select();
		if($content_arr[$key]['package'])
		{
			if(!$soft_result && $content_arr[$key]['package'] != '为空表示不关联')
			{
				$this ->append_error_msg($error_msg, $key, 1, "关联包名不存在;");
			}
			if($content_arr[$key]['package'] != '为空表示不关联'&&$content_arr[$key]['show_pic_type']=="")
			{
				$this ->append_error_msg($error_msg, $key, 1, "关联包名填写后图片显示为必填;");
			}
		}
			if(!empty($content_arr[$key]['key_word'])){
				$searchkey_have_where = "key_word = '{$content_arr[$key]['key_word']}' and start_tm <= {$content_arr[$key]['bk_end_tm']} and end_tm >= {$content_arr[$key]['bk_start_tm']} and status = 1";
				$searchkey_have_result = $model -> table('sj_search_keywords') -> where($searchkey_have_where) -> select();

				if($searchkey_have_result){
					$this -> append_error_msg($error_msg, $key, 1, "当前排期内已存在该搜索热词;");
				}
			}
		
			if($content_arr[$key]['package']){
				$package_have_where = "package = '{$content_arr[$key]['package']}' and start_tm <= {$content_arr[$key]['bk_end_tm']} and end_tm >={$content_arr[$key]['bk_start_tm']} and status = 1";
				$package_have_result = $model -> table('sj_search_keywords') -> where($package_have_where) -> select();

				if($package_have_result)
				{
				$this ->append_error_msg($error_msg, $key, 1, "当前排序内已存在该关联包名;");
				}
			}
        }
        
        // 检查行与行之间的数据是否冲突
        foreach($content_arr as $key1=>$record1) 
		{
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record1['bk_start_tm']) || !isset($record1['bk_end_tm']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 如果开始时间或结束时间无效，则不比较
                if (!isset($record2['bk_start_tm']) || !isset($record2['bk_end_tm']))
                    continue;
                $k1 = $key1 + 1;
                $k2 = $key2 + 1;
                if ($record1['rank'] == $record2['rank']) 
				{
                    // 时间是否交叉
                    if ($record1['bk_start_tm'] <= $record2['bk_end_tm'] && $record2['bk_start_tm'] <= $record1['bk_end_tm']) 
					{
                        $this->append_error_msg($error_msg, $key1, 1, "同一顺序下，投放时间与第{$k2}行有冲突;");
                        $this->append_error_msg($error_msg, $key2, 1, "同一顺序下，投放时间与第{$k1}行有冲突;");
                    }
                }
                if ($record1['key_word'] == $record2['key_word']) 
				{
                    // 时间是否交叉
                    if ($record1['bk_start_tm'] <= $record2['bk_end_tm'] && $record2['bk_start_tm'] <= $record1['bk_end_tm']) 
					{
                        $this->append_error_msg($error_msg, $key1, 1, "同一热词下，投放时间与第{$k2}行有冲突;");
                        $this->append_error_msg($error_msg, $key2, 1, "同一热词下，投放时间与第{$k1}行有冲突;");
                    }
                }
            }
        }
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) 
		{
            // 如果开始时间或结束时间无效，则不比较
            if (!isset($record['bk_start_tm']) || !isset($record['bk_end_tm']))
                continue;
            // 查找位置相同的记录
            $where = array(
                'rank' => array('EQ', $record['rank']),
                'status' => array('NEQ', 0),
                'start_tm' => array('ELT', $record['bk_end_tm']),
                'end_tm' => array('EGT', $record['bk_start_tm']),
            );
            // 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $db_records = $hotwords_model->where($where)->select();
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) {
                $start_time_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_time_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) 
				{
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "同一排序下，投放时间与后台记录ID为【{$db_record['id']}】、热词为【{$db_record['key_word']}】的记录有冲突（其时间从【{$start_time_str}】到【{$end_time_str}】{$status_paused_hint}）;");
            }
            // 查找热词相同的记录
            $where = array(
                'key_word' => array('EQ', $record['key_word']),
                'status' => array('NEQ', 0),
                'start_tm' => array('ELT', $record['bk_end_tm']),
                'end_tm' => array('EGT', $record['bk_start_tm']),
            );
            // 如果是编辑，需在后台记录中排除自己
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $db_records = $hotwords_model->where($where)->select();
            // 有冲突的记录
            foreach($db_records as $db_key=>$db_record) {
                $start_time_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_time_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "同一热词下，与后台记录ID为【{$db_record['id']}】、热词为【{$db_record['key_word']}】的记录有冲突（其时间从【{$start_time_str}】到【{$end_time_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
	
	// 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array_hotwords_recommend($content_arr) {
        $result_arr = array();
        $model = M('search_keywords');
        $AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
			$map['update_tm'] = time();
            // 赋值，以下为必填的值
			if(strpos($record['start_tm'],"T")==0)
			 {
			  $record['start_tm']=substr($record['start_tm'], 1);
			 }
			 if(strpos($record['end_tm'],"T")==0)
			 {
			  $record['end_tm']=substr($record['end_tm'], 1);
			 }
			$map['key_word'] = $record['key_word'];
			$map['rank'] = $record['rank'];
			$map['key_type'] = $record['key_type'];
			$map['start_tm'] = strtotime($record['start_tm']);
			$map['end_tm'] = strtotime($record['end_tm']);
			$map['package']=$record['package'];
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			if($map['package'])
			{
				$map['show_pic']=$record['show_pic_type'];
			}
			else
			{
				$map['show_pic']=0;
			}
			//非必填
			$map['type'] = isset($record['type']) ? $record['type'] : 0;

            $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$map;
            	continue;
            }

            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('广告批量排期管理_广告位列表_搜索热词推荐批量添加'.$record['key_word'],'sj_search_keywords',$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			} 
			// else {
                // 未知原因添加失败
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_search_keywords')){
        	$result_arr['table_name']='sj_search_keywords';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
	//添加内容推荐
	function pub_add_content(){
		$model = new Model();
		$table = "sj_search_keywords";	
		$id = $_POST['id'] ? $_POST['id'] : $_GET['id'];
		$where 	= array('id'=>$id,'status' => 1);
		if ($_POST) {
			unset($_POST['is_contenttye']);
			$map = array();
			$rcontent=ContentTypeModel::saveRecommendContent($_POST,'', $map);
			if($rcontent!==true){
				$this -> error($rcontent);
			}
			$map['update_tm'] = time();	 
			// 记录一下日志
            $log = $this -> logcheck(array('id' => $id),$table,$map, $model);
			$ret = $model->table($table)->where($where)->save($map);
			//echo $model->getlastsql();	
			if ($ret) {
				$this->writelog("市场搜索管理-搜索热词推荐-编辑了id为{$id}的记录：{$log}",$table,$id,__ACTION__ ,"","edit");
				$this->success('推荐内容成功');
			} else {
				$this->error('推荐内容编辑失败');
			}			
        }else{		
			$list = $model->table($table)->where($where)->find();
			exit(json_encode($list));	
		}
	}
	//6.4.8榜单推荐

	function list_recommend()
	{
		$model = M('search_list_recommend');
		$where = array(
			'status' => 1
		);
		$now = time();

		$count = $model->where($where)->count();
		import("@.ORG.Page");
		$p = new Page ( $count, 15 );
		$list = $model->limit($p->firstRow.','.$p->listRows)->where($where)->order('start_tm asc')->select();
		$page = $p->show();

		$content_id_arr=array();
		foreach($list as $k=>$v){
			$content_id_arr[]=$v['content_id'];
		}
		$common_jump_data=$model->table('sj_common_jump')->where(array('id'=>array('in',$content_id_arr)))->select();
		
		$common_jump_data_new=array();
		foreach($common_jump_data as $k=>$v){
			$content_id=$v['id'];
			unset($v['id']);
			unset($v['create_at']);
			unset($v['update_at']);
			unset($v['status']);

			$common_jump_data_new[$content_id]=$v;
		}
		$result = array();
		foreach($list as $key=>$val) {
			$val['start_tm']=date('Y-m-d H:i:s',$val['start_tm']);
			$val['end_tm']=date('Y-m-d H:i:s',$val['end_tm']);


			$result[] = array_merge($val,$common_jump_data_new[$val['content_id']]);
		}
		foreach($result as $k=>$val){
			if($val['content_type'] == 5){
				$result[$k]['content_type'] = "网页-".$val['website'];
			}else if($val['content_type'] == 6){
				$result[$k]['content_type'] = "礼包-".$val['gift_id'];
			}else if($val['content_type'] == 7){
				$result[$k]['content_type'] = "攻略-".$val['strategy_id'];
			}else if($val['content_type'] == 9){
				$used_info = json_decode($val['parameter_field'],true);
				$result[$k]['content_type'] = "应用内览-".$used_info['softname']."-".$used_info['title'];
			}
		}
		// echo "<pre>";var_dump($result);die;
		$this->assign('list', $result);
		$this -> assign('product_id',$this -> product_id);
		$this->assign('page', $page);
		$this->display();
	}

	function edit_list_recommend()
	{
		$id = $_REQUEST['id'];

		$where = array(
			'id' => $id
		);
		$model = M('search_list_recommend');
		$recommend = $model->where($where)->find();

		if (!empty($_POST)){
            
			$map = array();
			$map['update_tm'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];


			isset($_POST['key_word']) && $map['key_word'] = trim($_POST['key_word']);
			isset($_POST['rank']) && $map['rank'] = $_POST['rank'];
			isset($_POST['show_rule']) && $map['show_rule'] = $_POST['show_rule'];
            
			isset($_POST['start_tm']) && $map['start_tm'] = strtotime($_POST['start_tm']);
			isset($_POST['end_tm']) && $map['end_tm'] = strtotime($_POST['end_tm']);
			
			
			$conflict_data=$model->where(array('rank'=>$map['rank'],'id'=>array('neq',$id),'start_tm'=>array('elt',$map['start_tm']),'end_tm'=>array('egt',$map['end_tm'])))->select();
			// echo $model->getlastsql();
			if($conflict_data){
				$this->error('同一时间同一位置只可保留一条数据');
			}
			
			
			
			//6.4.6
			
			$conetnt_map_1 = array();
			$rcontent_1 = ContentTypeModel::saveRecommendContent($_POST,$_POST['content_type'],$conetnt_map_1);
			if($rcontent_1==true)
			{
				$conetnt_map_1['create_at'] = time();
				$conetnt_map_1['update_at'] = time();
				$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
				$map['content_id'] = $conetnt_id_1;
			}else {
				$this -> error($rcontent_1);
			}
				
			$log_msg = $this->logcheck($where, 'sj_search_list_recommend', $map, $model);
			
			if ($model->where($where)->save($map)) 
			{
				$this->assign('jumpUrl', '/index.php/Search/Advertisement/list_recommend/');
				
				$this->writelog("市场搜索管理- 榜单推荐:编辑了id为{$id},{$log_msg}",'sj_search_list_recommend',$id,__ACTION__ ,"","edit");
				
				$this->success('编辑成功');
			}
		} else {
			$content_list = M('')->table('sj_common_jump')->where(array('id' => $recommend['content_id']))->find();

			$this->assign('content_list',$content_list);

			$this->assign('recommend', $recommend);

			$this->display();
		}
	}


	function add_list_recommend()
	{


		$model = M('search_list_recommend');


		if (!empty($_POST)){

			$map = array();
			$map['status'] = 1;
			$map['add_tm'] = time();
			$map['update_tm'] = time();
            $map['admin_id'] = $_SESSION['admin']['admin_id'];

	
			isset($_POST['rank']) && $map['rank'] = $_POST['rank'];
			isset($_POST['start_tm']) && $map['start_tm'] = strtotime($_POST['start_tm']);
			isset($_POST['end_tm']) && $map['end_tm'] = strtotime($_POST['end_tm']);
			isset($_POST['show_rule']) && $map['show_rule'] = $_POST['show_rule'];
			isset($_POST['key_word']) && $map['key_word'] = $_POST['key_word'];



			//6.4.6
			// if( !$content_type_one ) {
			// 	$this->error('请填写推荐内容');
			// }
			
			$conflict_data=$model->where(array('rank'=>$map['rank'],'start_tm'=>array('elt',$map['start_tm']),'end_tm'=>array('egt',$map['end_tm'])))->select();
			if($conflict_data){
				$this->error('同一时间同一位置只可保留一条数据');
			}
			
			$conetnt_map_1 = array();
			$rcontent_1 = ContentTypeModel::saveRecommendContent($_POST,$_POST['content_type'],$conetnt_map_1);
			if($rcontent_1==true)
			{
				$conetnt_map_1['create_at'] = time();
				$conetnt_map_1['update_at'] = time();
				// echo "<pre>";var_dump($conetnt_map_1);die;
				$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
				$map['content_id'] = $conetnt_id_1;
			}else {
				$this -> error($rcontent_1);
			}
			// $id = $model->add($map);
			// echo $model->getlastsql();die;
			if ($id = $model->add($map)) {
				$this->assign('jumpUrl', '/index.php/Search/Advertisement/list_recommend/');
				$this->writelog("市场搜索管理-榜单列表:榜单列表添加了名称为[{$_POST['key_word']}],排序为{$_POST['rank']},开始时间为{$_POST['start_tm']},结束时间为{$_POST['end_tm']},", 'sj_search_list_recommend',$id,__ACTION__ ,"","add");

				$this->success('添加成功');
			} else {
				$this->error('添加失败');
			}
		} else {
			$this->display();
		}
	}

	function del_list_recommend()
	{
		$id = $_REQUEST['id'];
		$content_id = $_REQUEST['content_id'];
		$where = array(
			'id' => $id
		);
		$t=time();
		$map = array(
			'status' => 0,
			'update_tm'=>$t,
		);
		$model = M('search_list_recommend');
		$re=$model->where($where)->save($map);
		if($re){
			$model->where(array('id'=>$content_id))->save(array('status'=>0,'update_at'=>$t));
			$this->writelog("市场搜索管理-榜单推荐:删除了id为[$id]榜单推荐", 'sj_search_list_recommend', $id,__ACTION__ ,"","del");
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
		
	}
}
