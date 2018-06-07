<?php


//新轮播图
class AdextentAction extends CommonAction{
	
	private $image_width_high = 720;
    private $image_height_high = 420;
	private $image_width_low = 480;
    private $image_height_low = 280;
	private $ad_pic_count_limit = 8;
	private $gif_width = 480;
	private $gif_height = 181;
	//V6.4新增加 红包助手图片大小
	private $red_width = 480;
	private $red_height = 120;
	private $red_gif_width = 480;
	private $red_gif_height = 120;
	//6.4.4 增加图片	
	private $image_width_high_644 = 720;
	private $image_height_high_644 = 366;
	private $image_width_low_644 = 480;
	private $image_height_low_644 = 244;

	function extent_list(){
		$model = new Model();
		$util = D("Sj.Util");
		if($_GET['parent_id']){
			$parent_id = $_GET['parent_id'];
		}else{
			$parent_id = 1;
		}
		if($_GET['child_id']){
			$child_id = $_GET['child_id'];
		}else{
			$child_id = 1;
		}
		if($child_id!=9)
		{
			$result_count = $model -> table('sj_ad_extent') -> where(array('status' => 1,'pid' => $parent_id,'child_type' => $child_id)) -> count();
		}
		else  //9是内容合作
		{
			//合作站点和合作频道 展示
			$coop_new_arr =  ContentTypeModel::getCoopChannel(array('type'=>3));
			$this->assign('coop_result',$coop_new_arr);
			
			if($_GET['coop_channel'])
			{
				$coop_channel = $_GET['coop_channel'];
				$special_where=array(
					'status' => 1,
					'pid' => $parent_id,
					'child_type' => $coop_channel,
				);
				$result_count = $model -> table('sj_ad_extent') -> where($special_where) -> count();
				$this -> assign('coop_channel',$coop_channel);
			}
			else
			{
				$this -> assign('coop_channel',$coop_new_arr[0]['coop_key_val']);
			}
		}
		import("@.ORG.Page");
        $param = http_build_query($_GET);
		$page  = new Page($result_count, 20, $param);
		if($child_id!=9)
		{
			$result = $model -> table('sj_ad_extent') ->where(array('status' => 1,'pid' => $parent_id,'child_type' => $child_id))->order('rank asc')->limit($page->firstRow . ',' . $page->listRows)->select();
		}
		else
		{
			if($_GET['coop_channel'])
			{
				$result = $model -> table('sj_ad_extent') ->where(array('status' => 1,'pid' => $parent_id,'child_type' => $_GET['coop_channel']))->order('rank asc')->limit($page->firstRow . ',' . $page->listRows)->select();
			}
			else
			{
				$result = $model -> table('sj_ad_extent') ->where(array('status' => 1,'pid' => $parent_id,'child_type' => $coop_new_arr[0]['coop_key_val']))->order('rank asc')->limit($page->firstRow . ',' . $page->listRows)->select();
			}
		}
		
		foreach($result as $key => $val){
			$pic_where['_string'] = "extent_id = {$val['extent_id']} and start_tm <= ".time()." and end_tm >= ".time()." and status = 1";
			$pic_count = $model -> table('sj_ad_new') -> where($pic_where) -> count();
			if($pic_count){
				$val['pic_num'] = $pic_count;
			}else{
				$val['pic_num'] = 0;
			}
			$cid_arr = explode(',',substr($val['cid'],1,-1));
			$cname_str = '';
			if(in_array("0",$cid_arr))
			{
				$cname_str .= "通用";
			}
			foreach($cid_arr as $k => $v){
				$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> find();
				$cname_str .= $chname_result['chname'].',';
			}
			if($val['cid'] != ''){
				$val['chname_str'] = substr($cname_str,0,-1);
			}else{
				$val['chname_str'] = '通用';
			}
			$operation_result = $model -> table('pu_operating') -> where(array('oid' => $val['oid'])) -> find();
			if($operation_result){
				$val['operation_name'] = $operation_result['mname'];
			}else{
				$val['operation_name'] = '不限制';
			}
			$result[$key] = $val;
		}
	
		for($i=1;$i<=$result_count;$i++){
			$rank_result[] = $i;
		}
			
		$this -> assign('parent_id',$parent_id);
		$this -> assign('child_id',$child_id);
		$this -> assign('product_list',$util->getProducts($pid));
		$page -> setConfig('header', '篇记录');
        $page -> setConfig('first', '<<');
        $page -> setConfig('last', '>>');
        $this -> assign("page", $page->show());
		$this -> assign('rank_result',$rank_result);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	
	function add_extent_show(){
		$model = new Model();
		$parent_id = $_GET['parent_id'];
		$child_id = $_GET['child_id'];
		if($_GET['coop_channel'])
		{
			$coop_channel = $_GET['coop_channel'];
			$this -> assign('coop_channel',$coop_channel);
		}
		$result_count = $model -> table('sj_ad_extent') -> where(array('status' => 1,'pid' => $parent_id,'child_type' => $child_id)) -> count();
	
		$operation_result = $model -> table('pu_operating') -> select();
		
		for($i=1;$i<=$result_count;$i++){
			$rank_result[] = $i;
		}

		$this -> assign('parent_id',$parent_id);
		$this -> assign('rank_result',$rank_result);
		$this -> assign('child_id',$child_id);
		$this -> assign('result_count',$result_count);
		$this -> assign('operation_result',$operation_result);
		$this -> display();
	}
	
	
	function add_extent_do(){
		$model = new Model();
		$parent_id = $_POST['parent_id'];
		if($_POST['child_id']==9)
		{
			$child_id = $_POST['coop_channel'];
		}
		else
		{
			$child_id = $_POST['child_id'];
		}
		$extent_name = $_POST['extent_name'];
		$extent_have = $model -> table('sj_ad_extent') -> where(array('extent_name' => $extent_name,'pid' => $parent_id,'child_type' => $child_id,'status'=>1)) -> find();
		if($extent_have){
			$this -> error("该区间名称在此分类下已存在");
		}
		$type = $_POST['type'];
		$oid = $_POST['oid'];
		$cid = $_POST['cid'];
		$extent_size = $_POST['extent_size'];
		$rank = $_POST['rank'];
		foreach($cid as $key => $val){
			$cid_str_go .= $val.',';
		}
		if($cid){
			$cid_str = ','.$cid_str_go;
		}else{
			$cid_str = 0;
		}
		$rank_where['_string'] = "rank >= {$rank} and status =1 and pid = {$parent_id} and child_type = '{$child_id}' ";
		$rank_have = $model -> table('sj_ad_extent') -> where($rank_where) -> select();
		
		foreach($rank_have as $key => $val){
			$rank_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $val['extent_id'])) -> save(array('rank' => $val['rank'] + 1));
		}
		$extent_size_result = $model -> table('pu_config') -> where(array('config_type' => 'AD_EXTENT_SIZE','status' => 1)) -> find();
		$data = array(
			'extent_name' => $extent_name,
			'type' => $type,
			'pid' => $parent_id,
			'child_type' => $child_id,
			'extent_size' => $extent_size_result['configcontent'],
			'cid' => $cid_str,
			'oid' => $oid,
			'rank' => $rank,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
		);
		//添加推送区域
	    $data['push_area'] = $_POST['area_value'];		
		$result = $model -> table('sj_ad_extent') -> add($data);
		
		if($result){
			$this -> writelog("已添加id为{$result}的轮播图区间",'sj_ad_extent',"{$result}",__ACTION__ ,"","add");
			if(strpos($child_id,'coop')===0)
			{
				$this -> assign('jumpUrl',"/index.php/Sj/Adextent/extent_list/parent_id/{$parent_id}/child_id/9/coop_channel/{$child_id}");
			}
			else
			{
				$this -> assign('jumpUrl',"/index.php/Sj/Adextent/extent_list/parent_id/{$parent_id}/child_id/{$child_id}");
			}
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	
	function change_rank(){
		$model = new Model();
		$extent_id = $_GET['extent_id'];
		$parent_id = $_GET['parent_id'];
		$child_id = $_GET['child_id'];
		$new_rank = $_GET['rank'];
		$myself_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id)) -> find();
		$old_rank = $myself_result['rank'];
		if($old_rank > $new_rank){
			$where_need['_string'] = "rank >= {$new_rank} and rank < {$old_rank} and pid = {$parent_id} and child_type = '{$child_id}' and status = 1";
			$need_result = $model -> table('sj_ad_extent') -> where($where_need) -> select();

			foreach($need_result as $key => $val){
				$update_where['extent_id'] = $val['extent_id'];
				$update_data = array(
					'rank' => $val['rank'] + 1
				);
				$update_result = $model -> table('sj_ad_extent') -> where($update_where) -> save($update_data);
			}
		}elseif($old_rank < $new_rank){
			$where_need['_string'] = "rank <= {$new_rank} and rank > {$old_rank} and pid = {$parent_id} and child_type = '{$child_id}' and status = 1";
			$need_result = $model -> table('sj_ad_extent') -> where($where_need) -> select();
			foreach($need_result as $key => $val){
				$update_where['extent_id'] = $val['extent_id'];
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table('sj_ad_extent') -> where($update_where) -> save($update_data);
			}
		}
		$where['extent_id'] = $extent_id;
		$data = array(
			'rank' => $new_rank
		);
		$result = $model -> table('sj_ad_extent') -> where($where) -> save($data);
		if($result){
			echo 1;
			return 1;
		}
	}
	
	
	function edit_extent_show(){
		$model = new Model();
		$extent_id = $_GET['extent_id'];
		$parent_id = $_GET['parent_id'];
		$child_id = $_GET['child_id'];
		$result_count = $model -> table('sj_ad_extent') -> where(array('status' => 1,'pid' => $parent_id,'child_type' => $child_id)) -> count();
		$result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id)) -> find();
		$operation_result = $model -> table('pu_operating') -> select();
		$my_cid = substr($result['cid'],1,-1);
		$array = explode(',', $my_cid);	
		$chl_where['_string'] = "cid in ({$my_cid})";
		$chl_result = $model -> table('sj_channel') -> where($chl_where) -> select();
		if (in_array("0",$array)&&$chl_result!=NULL)
		{
			$tong = array("cid"=> "0" ,"chname"=> "通用");
			array_unshift($chl_result, $tong);
		}
		for($i=1;$i<=$result_count;$i++){
			$rank_result[] = $i;
		}
		$area_list=explode(';',$result['push_area']);
		$this->assign("push_area",$area_list);

		$this -> assign('result',$result);
		$this -> assign('parent_id',$parent_id);
		$this -> assign('rank_result',$rank_result);
		$this -> assign('child_id',$child_id);
		$this -> assign('result_count',$result_count);
		$this -> assign('operation_result',$operation_result);
		$this -> assign('chl_list',$chl_result);
		$this -> display();
	
	}
	
	function edit_extent_do(){
		$model = new Model();
		$extent_id = $_POST['extent_id'];
		$parent_id = $_POST['parent_id'];
		$child_id = $_POST['child_id'];
		$extent_name = $_POST['extent_name'];
		$have_where['_string'] = "extent_name = '{$extent_name}' and pid = {$parent_id} and child_type = {$child_id} and extent_id != {$extent_id}";
		$extent_have = $model -> table('sj_ad_extent') -> where($have_where) -> find();
		
		if($extent_have){
			$this -> error("该区间名称在此分类下已存在");
		}
		$type = $_POST['type'];
		$oid = $_POST['oid'];
		$cid = $_POST['cid'];
		$extent_size = $_POST['extent_size'];
		$new_rank = $_POST['rank'];
		$have_been = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id)) -> find();
		$old_rank = $have_been['rank'];
		
		foreach($cid as $key => $val){
			$cid_str_go .= $val.',';
		}
		if($cid){
			$cid_str = ','.$cid_str_go;
		}else{
			$cid_str = 0;
		}
		$rank_where['_string'] = "rank >= {$rank} and status =1 and pid = {$parent_id} and child_type = {$child_id} ";
		$rank_have = $model -> table('sj_ad_extent') -> where($rank_where) -> select();
		
		foreach($rank_have as $key => $val){
			$rank_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $val['extent_id'])) -> save(array('rank' => $val['rank'] + 1));
		}
		
		if($old_rank > $new_rank){
			$where_need['_string'] = "rank >= {$new_rank} and rank < {$old_rank} and pid = {$parent_id} and child_type = {$child_id} and status = 1";
			$need_result = $model -> table('sj_ad_extent') -> where($where_need) -> select();

			foreach($need_result as $key => $val){
				$update_where['extent_id'] = $val['extent_id'];
				$update_data = array(
					'rank' => $val['rank'] + 1
				);
				$update_result = $model -> table('sj_ad_extent') -> where($update_where) -> save($update_data);
			}
		}elseif($old_rank < $new_rank){
			$where_need['_string'] = "rank <= {$new_rank} and rank > {$old_rank} and pid = {$parent_id} and child_type = {$child_id} and status = 1";
			$need_result = $model -> table('sj_ad_extent') -> where($where_need) -> select();
			foreach($need_result as $key => $val){
				$update_where['extent_id'] = $val['extent_id'];
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table('sj_ad_extent') -> where($update_where) -> save($update_data);
				
			}
		}
		
		$data = array(
			'extent_name' => $extent_name,
			'type' => $type,
			'pid' => $parent_id,
			'child_type' => $child_id,
		
			'cid' => $cid_str,
			'oid' => $oid,
			'rank' => $new_rank,
			'update_tm' => time(),
		);
		$data['push_area'] = $_POST['area_value'];
		$log_result = $this -> logcheck(array('extent_id' => $extent_id),'sj_ad_extent',$data,$model);
		$result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id)) -> save($data);
		
		if($result){
			$this -> writelog("已编辑id为{$extent_id}的轮播图区间".$log_result,'sj_ad_extent',"{$extent_id}",__ACTION__ ,"","edit");
			if(strpos($child_id,'coop')===0)
			{
				$this -> assign('jumpUrl',"/index.php/Sj/Adextent/extent_list/parent_id/{$parent_id}/child_id/9/coop_channel/{$child_id}");
			}
			else
			{
				$this -> assign('jumpUrl',"/index.php/Sj/Adextent/extent_list/parent_id/{$parent_id}/child_id/{$child_id}");
			}
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	
	function del_extent(){
		$model = new Model();
		$parent_id = $_GET['parent_id'];
		$child_id = $_GET['child_id'];
		$extent_id = $_GET['extent_id'];
		$been_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id)) -> find();
		$rank_where['_string'] = "rank > {$been_result['rank']} and status = 1 and pid = {$parent_id} and child_type = {$child_id}";
		$rank_have = $model -> table('sj_ad_extent') -> where($rank_where) -> select();
		foreach($rank_have as $key => $val){
			$rank_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $val['extent_id'])) -> save(array('rank' => $val['rank'] - 1));
		}
		$result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id)) -> save(array('status' => 0));
		
		if($result){
			$this -> writelog("已删除id为{$extent_id}的轮播图区间",'sj_ad_extent',"{$extent_id}",__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Sj/Adextent/extent_list/parent_id/{$parent_id}/child_id/{$child_id}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function ad_list(){
		$model = new Model();
		$extent_id = $_GET['extent_id'];
		$cid=$_GET['cid'];
		$extent_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id)) -> find();
		if($_GET['my_time']){
			$my_time = $_GET['my_time'];
		}else{
			$my_time = 2;
		}
		if($my_time == 1){
			$where['_string'] = "end_tm < ".time()." and extent_id = {$extent_id} and status = 1";
		}elseif($my_time == 2){
			$where['_string'] = "start_tm <= ".time()." and end_tm >= ".time()." and extent_id = {$extent_id} and status = 1";
		}elseif($my_time == 3){
			$where['_string'] = "start_tm > ".time()." and extent_id = {$extent_id} and status = 1";
		}
		
		$result = $model -> table('sj_ad_new') -> where($where) -> order('start_tm asc') -> select();
		$util = D("Sj.Util"); 
		foreach($result as $key => $val){
			if($val['featureid']){
				$feature_result = $model -> table('sj_feature') -> where(array('feature_id' => $val['featureid'])) -> find();
				$val['feature'] = $feature_result['name'];
			}
			if($val['activityid']){
				$activity_result = $model -> table('sj_activity') -> where(array('id' => $val['activityid'])) -> find();
				$val['activity'] = $activity_result['name'];
			}
			if($val['page_name'])
			{
				//页面
				$val['page_names'] = ContentTypeModel::convertPageType2PageName($val['page_name']);		
			}
			if($val['parameter_field'])  {
				//应用内览
				$param_field = json_decode($val['parameter_field'], true);
				if($param_field['used_id']) {
					$used_data = ContentTypeModel::convertUsedId2UsedName($param_field['used_id']);
					$val['page_names'] = $used_data['title'];
				}
			}
			$result[$key] = $val;
			//合作形式展示
			$typelist = $util->getHomeExtentSoftTypeList($val['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$result[$key]['co_types'] = $v[0];
				}
			}
		}
		$this -> assign('extent_result',$extent_result);
		$this -> assign('my_time',$my_time);
		$this -> assign("result",$result);
		$this -> assign('extent_id',$extent_id);
		$this -> assign('cid',$cid);
		$this -> display();
	}
	
	function add_ad_show()
	{
		$model = new Model();
		$extent_id = $_GET['extent_id'];
                    
		$activity = D('Sj.Activity');
		$activity_list = $activity->where(array('status' => 1))->field('id,name')->order(' id desc ')->select();
		//$feature_list=$model -> table('sj_feature')->where(array('status' => 1))->field('feature_id,name')->select();

		$rs = $model->table('sj_ad_extent')->field('pid,child_type')->where("extent_id = $extent_id")->find();
		$pid = '%,'.$rs['pid'].',%';
		$where['status']=1;
		$where['pid']=array('like',$pid);
		$feature_list=$model -> table('sj_feature')->where($where)->field('feature_id,name')->order(' feature_id desc ')->select();
		//获取红包助手的频道id
		$red_assis=$model -> table('coop_channel')->where(array('status'=>1,'type'=>2))->field('id')->find();
		if(strpos($rs['child_type'],'coop')===0)
		{
			$child_type_arr = explode('_',$rs['child_type']);
			if($red_assis['id']==$child_type_arr[2])//说明是红包助手
			{
				$this->assign('is_red',$red_assis['id']);
			}
		}
		
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);

		$this->assign('conflist',$feature_list);
		$this->assign('activitylist', $activity_list);
		$this -> assign('extent_id',$extent_id);
		$this-> assign('cid',$_GET['cid']);
		$this-> assign('child_type',$rs['child_type']);
		$this->assign('image_width_high',$this->image_width_high);
		$this->assign('image_height_high',$this->image_height_high);
		$this->assign('image_width_low',$this->image_width_low);
		$this->assign('image_height_low',$this->image_height_low);
		$this->assign('gif_width',$this->gif_width);
		$this->assign('gif_height',$this->gif_height);
		//红包助手图片大小
		$this->assign('red_width',$this->red_width);
		$this->assign('red_height',$this->red_height);
		$this->assign('red_gif_width',$this->red_gif_width);
		$this->assign('red_gif_height',$this->red_gif_height);
		
		$this -> display();
	}
	
	function add_ad_do()
	{
		$model = new Model();
		$extent_id = $_POST['extent_id'];
		
		$ad_name = trim($_POST['ad_name']);
		if(!$ad_name){
			$this -> error("请填写轮播图名称");
		}
		
		/*运营位临时需求 去掉广告名称不可重复的限制
		2015-3-27 added by shitingting*/
		
		/*$have_been = $model -> table('sj_ad_new') -> where(array('ad_name' => $ad_name,'extent_id' => $extent_id,'status' =>1)) -> find();
		if($have_been){
			$this -> error("该轮播图名称在该区间内已存在");
		}*/
		$prob = $_POST['prob'];
		if($prob > 100 || $prob < 0 || !$prob){
			$this -> error("请填写正确的显示概率");
		}
		$ad_type = $_POST['ad_type'];
		$note = $_POST['note'];
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if(isset($_POST['co_type'])){			
			$co_type = $_POST['co_type'];	
		}else{		
			$co_type = 0;	
		}
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请选择开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		//V6.2添加同一位置不能超过8张
		$find_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id,'status' =>1)) -> find();
		$child_type = $find_result['child_type'];
		
		$cid=$_POST['cid']; //添加渠道 同一时间通用渠道轮播图不超过8张
		$cid_str=preg_replace('/^,/','',$cid);
		$cid_str=preg_replace('/,$/','',$cid_str);
		if($cid_str==""||$cid_str=="0")
		{
			$max_time=max(strtotime($start_tm),time());
			$end_time=strtotime($end_tm);
			$where=array(
					'a.start_tm'=>array('elt',$end_time),
					'a.end_tm'=>array('egt',$max_time),
					'a.status'=>1,
					'b.status'=>1,
					'b.pid'=>1,
					'b.child_type'=>$child_type,
					'b.cid'=>array('in',array('',',0,','0')),
				);
		/*	
			$ad_pic_result=$model->table('sj_ad_new a')->join('sj_ad_extent b on a.extent_id = b.extent_id')->where($where)->select();
			$n=count($ad_pic_result);
			if($n>=$this->ad_pic_count_limit)
			{
				$time_arr=array();
				foreach($ad_pic_result as $k => $val)
				{	
					$time_arr[$k][0]=$val['start_tm'];
					$time_arr[$k][1]=$val['end_tm'];	
					$time_arr[$k][2]=$val['ad_name'];
				}
				usort($time_arr, array('AdextentAction','reorder'));
				
				for($i=0;$i<$n;$i++) 
				{
					$last_tmp = array();
					$ad_data = array();
					$time = 1;
					$tmp = $time_arr[$i];
					for($j=$i;$j<$n-1;$j++) 
					{
						$last_tmp = $tmp;
						$tmp = $this->my_intersect($tmp, $time_arr[$j+1]);
						if (!$tmp) 
						{
							break;
						}
						$ad_data[]=$time_arr[$j][2];
						$ad_data[]=$time_arr[$j+1][2];
						$time++;
					}
					if($time>=$this->ad_pic_count_limit)
					{
						$this->error("同一时间通用渠道的轮播图不能超过{$this->ad_pic_count_limit}张,广告名称分别为：".implode(',',array_unique($ad_data)));
					}
				}
			}
			*/
		}
		
		if($_POST['beid']){
			$beid_where['_string'] = "beid = {$_POST['beid']} and end > ".time()." and status = 1";
			$beid_result = $model -> table('sj_push_be_detail') -> where($beid_where) -> select();
		
			if(!$beid_result){
				$this -> error("填写的行为id不存在，请检查");
			} 
		}
		
		$featureid = 0;
		$package   = '';
		$href      = '';
		$activityid = 0;
		$page_type='';
		$page_type_name='';
		$page_title='';
		$beid = trim($_POST['beid']);
		$self_extent = $model -> table('sj_ad_extent') -> where(array('extent_id' => $extent_id,'status'=>1)) -> find();
		$same_extent_result = $model -> table('sj_ad_extent') -> where(array('pid' => $self_extent['pid'],'child_type' => $self_extent['child_type'],'status'=>1)) -> select();
	
		foreach($same_extent_result as $key => $val){
			$same_extent_str_go .= $val['extent_id'].',';
		}
		$same_extent_str = substr($same_extent_str_go,0,-1);
		switch ($_POST['ad_type'])
		{
			case 1:
				$featureid  =   $_POST['featureid'];
			
				if(!$featureid){
					$this -> error("请选择专题");
				}
				if($featureid){
					$feature_have_result = $model -> table('sj_feature') -> where(array('feature_id' => $featureid,'status' => 1)) -> select();
					if(!$feature_have_result){
						$this -> error("专题id不存在");
					}
				}
				$feature_have_where['_string'] = "featureid = {$featureid} and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str})";
				$feature_have_been = $model -> table('sj_ad_new') -> where($feature_have_where) -> select();
				if($feature_have_been){
					$this -> error("该专题已存在于其他轮播图");
				}
			
				break;
			case 2:
				$package   =  preg_replace('/[\s]+/','',$_POST['package']);
				$start_to_page = isset($_POST['start_to_page']) ? trim($_POST['start_to_page']) : '';
				$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> find();
				if(!$have_package){
					$this -> error("包名不存在");
				}
				$package_have_where['_string'] = "package = '{$package}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str})";
				$package_have_been = $model -> table('sj_ad_new') -> where($package_have_where) -> select();
				if($package_have_been){
					$this -> error("该包名已存在于其他轮播图");
				}
				break;
			case 3:
				$href = trim($_POST['href']);
				$page_title = trim($_POST['page_title']);
				if(!$_POST['page_title']){
					$this -> error("请填写网页标题");
				}
				if(!$_POST['href']){
					$this -> error("请填写网页链接");
				}
				if($_POST['open_type'])
				{
					$open_type = $_POST['open_type'];
				}
				else
				{
					$this-> error("请选择网页广告打开方式");
				}
				//增加对网址的判断  				2015-3-27 added by shitingting
				$url_have_where['_string'] = "href = '{$href}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1  and extent_id in ({$same_extent_str})";
				$url_have_been = $model -> table('sj_ad_new') -> where($url_have_where) -> select();
				if($url_have_been){
					$this -> error("该网址已存在于其他轮播图");
				}
				if($_POST['open_type']==1){
					$parameter_field = json_encode(array('website_is_sync_accout'=>$_POST['is_sync_accout'],'website_is_actionbar'=>$_POST['is_actionbar'],'website_screen_show'=>$_POST['screen_show'],'website_is_h5'=>$_POST['is_h5']));
				}else{
					$parameter_field = '';
				}
				break;
			case 4:
				$activityid = $_POST['activityid'];
				
				if(!$activityid){
					$this -> error("请选择活动");
				}
				if($activityid){
					$activity_have_result = $model -> table('sj_activity') -> where(array('id' => $activityid,'status' => 1)) -> select();
					if(!$activity_have_result){
						$this -> error("活动id不存在");
					}
				}
				$activity_have_where['_string'] = "activityid = {$activityid} and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1  and extent_id in ({$same_extent_str})";
				$activity_have_been = $model -> table('sj_ad_new') -> where($activity_have_where) -> select();
			
				if($activity_have_been){
					$this -> error("该活动已存在于其他轮播图");
				}
				break;
			case 5:
				$map=array();
				$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				if($rcontent!==true)
				{
					$this -> error($rcontent);
					//return $rcontent;
				}
				else
				{
					$page_type_name=$map['page_type'];
					$parameter_field=$map['parameter_field'];
					if($parameter_field)
					{
						$page_have_where['_string'] = "page_name = '".$page_type_name."' and parameter_field = '".quotemeta($parameter_field)."' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str})";
					}
					else
					{
						$page_have_where['_string'] = "page_name = '{$page_type_name}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str})";
					}
					$page_have_been = $model -> table('sj_ad_new') -> where($page_have_where) -> select();
					if($page_have_been)
					{
						$this -> error("该页面已存在于其他轮播图");
					}
					else
					{
						$page_name = $map['page_type'];
						$content_type = $map['content_type'];
					}						
				}
				break; 
				//V6.0添加跳转页面
                /*$page_type=$_POST['page_type'];
				if($page_type==4)
				{
					$page_name=trim($_POST['page_name4']);
				}
				elseif($page_type==1)
				{
					$page_name=trim($_POST['page_name1']);
				}
				if(!$page_name)
				{
					$this -> error("页面不能为空");
				}
				else
				{
					$page_type_name = ContentTypeModel::convertPageName2PageType($page_name, $page_type);
					if (!$page_type_name) 
					{
						$this -> error("页面不存在");
					}
					else
					{
						if($page_type_name=="fixed_bbs_detailpage")
						{
							$bbs_detail_page_id = trim($_POST['bbs_detail_page_id']);
							if(!$_POST['bbs_detail_page_id']){
								$this -> error("请填写帖子TID");
							}
							$page_type_name = "bbs_detailpage_".$bbs_detail_page_id;
						}	
						$page_have_where['_string'] = "page_name = '{$page_type_name}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str})";
						$page_have_been = $model -> table('sj_ad_new') -> where($page_have_where) -> select();
						if($page_have_been)
						{
							$this -> error("该页面已存在于其他轮播图");
						}
					}
				}
				break; */
		}
		$path = date("Ym/d/");
		$config = array(
			'multi_config' => array(),
		);
		list($width_new, $height_new, $type_new, $attr_new)=getimagesize($_FILES['image_new']['tmp_name']);
		list($width_old, $height_old, $type_old, $attr_old)=getimagesize($_FILES['image_old']['tmp_name']);
		list($width_high, $height_high, $type_high, $attr_high)=getimagesize($_FILES['image_high']['tmp_name']);
		list($width_low, $height_low, $type_low, $attr_low)=getimagesize($_FILES['image_low']['tmp_name']);
		list($width_gif, $height_gif, $type_low, $attr_low)=getimagesize($_FILES['image_gif']['tmp_name']);
		list($width_high_644, $height_high_644, $type_high_644, $attr_high_644)=getimagesize($_FILES['image_high_644']['tmp_name']);
		list($width_low_644, $height_low_644, $type_low_644, $attr_low_644)=getimagesize($_FILES['image_low_644']['tmp_name']);
		//获取红包助手的频道id
		$red_assis=$model -> table('coop_channel')->where(array('status'=>1,'type'=>2))->field('id')->find();
		if(!empty($_FILES['image_new']['size']))
		{
			$suffix_new = preg_match("/\.(jpg|png)$/", $_FILES['image_new']['name'],$matches_new);
			if (!$suffix_new) {
				$this->error('上传图片格式错误！');
			} 
			//V6.4新增红包助手频道，图片大小和以前的不一样，逻辑和游戏推荐频道一样
			if(strpos($_POST['child_type'],'coop')===0)
			{
				$child_type_arr = explode('_',$_POST['child_type']);
				if($red_assis['id']==$child_type_arr[2])//说明是红包助手
				{
					$is_red = 1;
				}
			}
		
			if($is_red)
			{
				if($width_new==$this->red_width&&$height_new==$this->red_height)
				{
					$config['multi_config']['image_new'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->red_width,
						'img_p_height' => $this->red_height,
						'img_p_size'   => 1024 * 35,
						'img_s_size'   => 1024 * 90,     
					);
				}
				else
				{
				 $this -> error("新版轮播图仅限上传{$this->red_width}*{$this->red_height}尺寸的图片");
				}
			}
			else
			{
				if($width_new==480&&$height_new==181)
				{
					$config['multi_config']['image_new'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => 480,
						'img_p_height' => 181,
						'img_p_size'   => 1024 * 35,
						'img_s_size'   => 1024 * 90,     
					);
				}
				else
				{
				 $this -> error("新版轮播图仅限上传480*181尺寸的图片");
				}
			}
		}
		else
		{
			$this -> error("请上传新版轮播图");
		}
		if(!empty($_FILES['image_gif']['size']))
		{
			$suffix_gif = preg_match("/\.(gif)$/", $_FILES['image_gif']['name'],$matches_gif);
			if (!$suffix_gif) {
				$this->error('上传图片格式错误！');
			} 
			$folder = "/img/" . date("Ym/d/");
			$this->mkDirs(UPLOAD_PATH . $folder);
			//V6.4新增红包助手频道，图片大小和以前的不一样，逻辑和游戏推荐频道一样
			if(strpos($_POST['child_type'],'coop')===0)
			{
				$child_type_arr = explode('_',$_POST['child_type']);
				if($red_assis['id']==$child_type_arr[2])//说明是红包助手
				{
					$is_red = 1;
				}
			}
		
			if($is_red)
			{
				if($width_gif==$this->red_gif_width&&$height_gif==$this->red_gif_height)
				{
					$config['multi_config']['image_gif'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->red_gif_width,
						'img_p_height' => $this->red_gif_height,
						'enable_resize' =>false,
						//'img_p_size'   => 1024 * 80,
						//'img_s_size'   => 1024 * 80,  
					);
				}
				else
				{
				 $this -> error("红包助手的gif仅限上传{$this->red_gif_width}*{$this->red_gif_height}尺寸的图片");
				}
			}
			else
			{
			   if($width_gif==$this->gif_width&&$height_gif==$this->gif_height)
				{
					$config['multi_config']['image_gif'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->gif_width,
						'img_p_height' => $this->gif_height,
						'enable_resize' =>false,
						//'img_p_size'   => 1024 * 80,
						//'img_s_size'   => 1024 * 80,     
					);
				}
				else
				{
					$this -> error("6.3gif图片仅限上传{$this->gif_width}*{$this->gif_height}尺寸的图片");
				}
			}
		}
		//V6.2轮播图 如果不是游戏频论坛频道和礼包频道 这四张图是必填  只有这三个频道只上传480*181的图片 v6.4新增加应用热门频道和红包助手
		$exclude_type = array(
			6 => 1,
			7 => 1,
			8 => 1,
			10 => 1,
			11 => 1,
		);
		if(!isset($exclude_type[$_POST['child_type']]) && strpos($_POST['child_type'],'coop') === false)
		{
			if(!empty($_FILES['image_old']['size']))
			{
				$suffix_old = preg_match("/\.(jpg|png)$/", $_FILES['image_old']['name'],$matches_old);
				if (!$suffix_old) {
					$this->error('上传图片格式错误！');
				} 
				if($width_old==225&&$height_old==125)
				{
					$config['multi_config']['image_old'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => 225,
						'img_p_height' => 125,
						'img_p_size'   => 1024 * 35,
					);
				}
				else
				{
					$this -> error("旧版轮播图仅限上传225*125尺寸的图片");
				}
			}
			else
			{
				$this -> error("请上传旧版轮播图");
			}
			if(!empty($_FILES['image_high']['size']))
			{
				$suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_high']['name'],$matches_high);
				if (!$suffix_high) {
					$this->error('上传图片格式错误！');
				} 
			   if($width_high==$this->image_width_high&&$height_high==$this->image_height_high)
				{
					$config['multi_config']['image_high'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_high,
						'img_p_height' => $this->image_height_high,
						//'img_p_size'   => 1024 * 80,
						'img_s_size'   => 1024 * 80,     
					);
				}
				else
				{
				 $this -> error("6.0高分图片仅限上传{$this->image_width_high}*{$this->image_height_high}尺寸的图片");
				}
			}
			else
			{
				$this -> error("请上传6.0高分轮播图");
			}
			if(!empty($_FILES['image_low']['size']))
			{
				$suffix_low = preg_match("/\.(jpg|png)$/", $_FILES['image_low']['name'],$matches_low);
				if (!$suffix_low) {
					$this->error('上传图片格式错误！');
				} 
			   if($width_low==$this->image_width_low&&$height_low==$this->image_height_low)
				{
					$config['multi_config']['image_low'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_low,
						'img_p_height' => $this->image_height_low,
						//'img_p_size'   => 1024 * 40,
						'img_s_size'   => 1024 * 40,     
					);
				}
				else
				{
				 $this -> error("6.0低分轮播图仅限上传{$this->image_width_low}*{$this->image_height_low}尺寸的图片");
				}
			}
			else
			{
				$this -> error("请上传6.0低分轮播图");
			}
			
		}
		
		//6.4.4
		if(!empty($_FILES['image_high_644']['size'])) {
			$suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_high_644']['name'],$matches_high);
			if (!$suffix_high) {
				$this->error('上传图片格式错误！');
			}
			if( $width_high_644==$this->image_width_high_644 && $height_high_644 == $this->image_height_high_644 )
			{
				$config['multi_config']['image_high_644'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_high_644,
						'img_p_height' => $this->image_height_high_644,
						//'img_p_size'   => 1024 * 80,
						// 'img_s_size'   => 1024 * 80,
				);
			}else {
				$this -> error("6.4.4高分图片仅限上传{$this->image_width_high_644}*{$this->image_height_high_644}尺寸的图片");
			}
		}else {
			$this -> error("请上传6.4.4高分广告图");
		}
		//6.4.4
		if(!empty($_FILES['image_low_644']['size'])) {
			$suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_low_644']['name'],$matches_high);
			if (!$suffix_high) {
				$this->error('上传图片格式错误！');
			}
			if( $width_low_644==$this->image_width_low_644 && $height_low_644 == $this->image_height_low_644 )
			{
				$config['multi_config']['image_low_644'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_low_644,
						'img_p_height' => $this->image_height_low_644,
						//'img_p_size'   => 1024 * 80,
						// 'img_s_size'   => 1024 * 80,
				);
			}else {
				$this -> error("6.4.4低分图片仅限上传{$this->image_width_low_644}*{$this->image_height_low_644}尺寸的图片");
			}
		}else {
			$this -> error("请上传6.4.4低分广告图");
		}
		

		if (!empty($config['multi_config'])) {
			$list = $this->_uploadapk(0, $config);
			foreach($list['image'] as $val) 
			{
				if (empty($val['url'])) continue;
				if ($val['post_name'] == 'image_new') {
					$image_new = $val['url'];
					$image_new_90 = $val['url_resize'];
					$image_new_o = $val['url_original'];
					$data['image_new'] = $image_new;
					$data['image_new_90'] = $image_new_90;
					$data['image_new_o'] = $image_new_o;
				}
				if ($val['post_name'] == 'image_old') {
					$image_old = $val['url'];
					$data['image_old'] = $image_old;
				}
				if ($val['post_name'] == 'image_high') {
					$data['high_image_url'] = $val['url_original'];
					//$data['image_high_80'] = $val['url'];
					$data['high_image_url_80'] = $val['url_resize'];
				}
				if ($val['post_name'] == 'image_low') {
					$data['low_image_url'] = $val['url_original'];
					//$data['image_low_40'] = $val['url'];
					$data['low_image_url_40'] = $val['url_resize'];
				}
				if ($val['post_name'] == 'image_gif') {
					$data['gif_image_url'] = $val['url_original'];
					//$data['image_low_40'] = $val['url'];
					//$data['low_image_url_40'] = $val['url_resize'];
				}
				if ($val['post_name'] == 'image_high_644') {
					$data['high_image_url_644'] = $val['url_original'];
				}
				if ($val['post_name'] == 'image_low_644') {
					$data['low_image_url_644'] = $val['url_original'];
				}
			}
		}

		
		//市场版本选择  非必填
		$data['version_type'] = $_POST['type'];
		if($_POST['type']==1)
		{
			$data['version_code'] = $_POST['version_code1'];
		}
		if($_POST['type']==2)
		{
			$data['version_code'] = $_POST['version_code2'];
		}
		if($_POST['type']==3)
		{
			$data['version_code'] = $_POST['force_update_version'];
		}
			
			
		$data['extent_id'] = $extent_id;
		$data['ad_name'] = $ad_name;
		$data['ad_type'] = $ad_type;
		$data['package'] = $package;
		$data['start_to_page'] = isset($start_to_page) ? $start_to_page : '';
		$data['href'] = $href;
		$data['featureid'] = $featureid;
		$data['page_title'] = $page_title;
		$data['open_type'] = $open_type;
		$data['beid'] = $beid;
		$data['activityid'] = $activityid;
		$data['note'] = $note;
		$data['prob'] = $prob;
		//$data['page_type'] = $page_type;
		$data['content_type'] = $content_type;

		$parameter_field=json_decode($parameter_field,true);
		
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



		$data['parameter_field'] = json_encode($parameter_field);

		$data['page_name'] = $page_type_name;
		$data['start_tm'] = strtotime($start_tm);
		$data['end_tm'] = strtotime($end_tm);
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$data['co_type'] = $co_type;
		if($data['package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
			    $this -> error($shield_error);
			}
		}
		$this->get_csv_data($data);
		$result = $model -> table('sj_ad_new') -> add($data);
		
		if(strtotime($start_tm) > time()){
			$my_time = 3;
		}elseif(strtotime($start_tm) <= time() && strtotime($end_tm) >= time() ){
			$my_time = 2;
		}elseif(strtotime($end_tm) < time()){
			$my_time = 1;
		}
		if($result){
			$this -> writelog("已添加id为{$result}的广告轮播图",'sj_ad_new',"{$result}",__ACTION__ ,"","add");
			$this -> assign("jumpUrl","/index.php/Sj/Adextent/ad_list/extent_id/{$extent_id}/my_time/{$my_time}/cid/{$cid}");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
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
	function edit_ad_show()
	{
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_ad_new') -> where(array('id' => $id)) -> find();

		$parameter_field=json_decode($result['parameter_field'],true);

        $result['cover_user_type']=$parameter_field['cover_user_type'];
        $result['activation_date_end']=$parameter_field['activation_date_end'];
        $result['activation_date_start']=$parameter_field['activation_date_start'];

		if($result['ad_type']==3&&!empty($result['parameter_field'])){
			$info = json_decode($result['parameter_field'],true);
			$result['is_sync_accout'] = $info['website_is_sync_accout'];
			$result['is_actionbar'] = $info['website_is_actionbar'];
			$result['screen_show'] = $info['website_screen_show'];
			$result['is_h5'] = $info['website_is_h5'];
		}

		$activity = D('Sj.Activity');
		$activity_list = $activity->where(array('status' => 1))->field('id,name')->select();

		$extent_id = $result['extent_id'];
		$rs = $model->table('sj_ad_extent')->field('pid,child_type')->where("extent_id = $extent_id")->find();
		$pid = '%,'.$rs['pid'].',%';
		$where['status']=1;
		$where['pid']=array('like',$pid);
		$feature_list=$model -> table('sj_feature')->where($where)->field('feature_id,name')->select();
		
		//V6.0 英文页面标识转换汉字显示
		/*if($result['page_name'])
		{
			$page_type = $result['page_name'];
        
			$page_name = ContentTypeModel::convertPageType2PageName($page_type);
			if (!$page_name)
			{
				$page_names="";
			}
			else
			{
				$page_names=$page_name;
			}
			//论坛详情页TID
			if(strpos($page_type,'bbs_detailpage_')==0&&strpos($page_type,'bbs_detailpage_')!==false)
			{
				$page_type_arr = explode('_',$page_type);
				$tid = $page_type_arr[count($page_type_arr)-1];
				$this->assign('tip_tid',$tid);
			}
		}
		$this->assign('page_names',$page_names);*/
		//$feature_list=$model -> table('sj_feature')->where(array('status' => 1))->field('feature_id,name')->select();
		
		//获取红包助手的频道id
		$red_assis=$model -> table('coop_channel')->where(array('status'=>1,'type'=>2))->field('id')->find();
		if(strpos($rs['child_type'],'coop')===0)
		{
			$child_type_arr = explode('_',$rs['child_type']);
			if($red_assis['id']==$child_type_arr[2])//说明是红包助手
			{
				$this->assign('is_red',$red_assis['id']);
			}
		}
		
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($result['co_type']);
		$this->assign('typelist',$typelist);
		$this->assign('conflist',$feature_list);
		$this->assign('activitylist', $activity_list);
		$this -> assign('result',$result);
		$this -> assign('child_type',$rs['child_type']);
		$this->assign('image_width_high',$this->image_width_high);
		$this->assign('image_height_high',$this->image_height_high);
		$this->assign('image_width_low',$this->image_width_low);
		$this->assign('image_height_low',$this->image_height_low);
		$this->assign('gif_width',$this->gif_width);
		$this->assign('gif_height',$this->gif_height);
		//V6.4红包助手图片大小
		$this->assign('red_width',$this->red_width);
		$this->assign('red_height',$this->red_height);
		$this->assign('red_gif_width',$this->red_gif_width);
		$this->assign('red_gif_height',$this->red_gif_height);
		
		$this -> display();
	}
	
	
	function edit_ad_do()
	{
		$model = new Model();
		$id = $_POST['id'];
		$cid = $_POST['cid'];
		$myself = $model -> table('sj_ad_new') -> where(array('id' => $id)) -> find();
		$ad_name = $_POST['ad_name'];
		if(!$ad_name){
			$this -> error("请填写轮播图名称");
		}
		/*运营位临时需求 去掉广告名称不可重复的限制
		2015-3-27 added by shitingting*/
		
		/*$have_where['_string'] = "ad_name = '{$ad_name}' and extent_id = {$myself['extent_id']} and id != {$id} and status = 1";
		$have_been = $model -> table('sj_ad_new') -> where($have_where) -> find();
		if($have_been){
			$this -> error("该轮播图名称在该区间内已存在");
		}*/
		$prob = $_POST['prob'];
		if($prob > 100 || $prob < 0 || !$prob){
			$this -> error("请填写正确的显示概率");
		}
		$ad_type = $_POST['ad_type'];
		$note = $_POST['note'];
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if(isset($_POST['co_type'])){
			$co_type = $_POST['co_type'];
		}else{
			$co_type = 0;
		}
			
		if(!$_POST['start_tm'] || !$_POST['end_tm']){
			$this -> error("请选择开始时间和结束时间");
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		//V6.2添加同一位置不能超过8张
		$find_result = $model -> table('sj_ad_extent') -> where(array('extent_id' => $myself['extent_id'],'status' =>1)) -> find();
		$child_type = $find_result['child_type'];
		
		$cid=$_POST['cid']; //添加渠道 同一时间通用渠道轮播图不超过8张
		$cid_str=preg_replace('/^,/','',$cid);
		$cid_str=preg_replace('/,$/','',$cid_str);
		if($cid_str==""||$cid_str=="0")
		{
			$max_time=max(strtotime($start_tm),time());
			$end_time=strtotime($end_tm);
			
			$where=array(
					'a.start_tm'=>array('elt',$end_time),
					'a.end_tm'=>array('egt',$max_time),
					'a.status'=>1,
					'b.status'=>1,
					'b.pid'=>1,
					//'b.child_type'=>array('exp','in(1,4,5)'),
					'b.child_type'=>$child_type,
					'b.cid'=>array('in',array('',',0,','0')),
					'a.id'=>array('neq',$id),
				);
			/*
			$ad_pic_result=$model->table('sj_ad_new a')->join('sj_ad_extent b on a.extent_id = b.extent_id')->where($where)->select();
			$n=count($ad_pic_result);
			if($n>=$this->ad_pic_count_limit)
			{
				$time_arr=array();
				foreach($ad_pic_result as $k => $val)
				{	
					$time_arr[$k][0]=$val['start_tm'];
					$time_arr[$k][1]=$val['end_tm'];	
				}
				usort($time_arr, array('AdextentAction','reorder'));
				
				for($i=0;$i<$n;$i++) 
				{
					$last_tmp = array();
					$time = 1;
					$tmp = $time_arr[$i];
					for($j=$i;$j<$n-1;$j++) 
					{
						$last_tmp = $tmp;
						$tmp = $this->my_intersect($tmp, $time_arr[$j+1]);
						if (!$tmp) 
						{
							break;
						}
						$time++;
					}
					if($time>=$this->ad_pic_count_limit)
					{
						$this->error("同一时间通用渠道的轮播图不能超过{$this->ad_pic_count_limit}张");
					}
				}
			}
			*/
		}

		
		//已过期的信息复制上线判断
		if($_POST['life']==1)
		{
		  if(strtotime($_POST['end_tm'])<time())
		  {
			$this->error("您修改的复制上线的日期还是无效日期");
		  }
		}
		if($_POST['beid']){
			$beid_where['_string'] = "beid = {$_POST['beid']} and end > ".time()." and status = 1";
			$beid_result = $model -> table('sj_push_be_detail') -> where($beid_where) -> select();
		
			if(!$beid_result){
				$this -> error("填写的行为id不存在，请检查");
			} 
		}
		
		$featureid = 0;
		$package   = '';
		$href      = '';
		$activityid = 0;
		$page_type='';
		$page_type_name='';
		$page_title='';
		$beid = trim($_POST['beid']);
		$self_extent = $model -> table('sj_ad_extent') -> where(array('extent_id' => $myself['extent_id'])) -> find();
		$same_extent_where['_string'] = "pid = {$self_extent['pid']} and child_type = {$self_extent['child_type']} and status=1";//同一区间的也要判断是否存在 去掉and extent_id != {$myself['extent_id']} added by shitingting
		$same_extent_result = $model -> table('sj_ad_extent') -> where($same_extent_where) -> select();
		foreach($same_extent_result as $key => $val){
			$same_extent_str_go .= $val['extent_id'].',';
		}
		$same_extent_str = substr($same_extent_str_go,0,-1);

		switch ($_POST['ad_type'])
		{
			case 1:
				$featureid  =   $_POST['featureid'];
			
				if(!$featureid){
					$this -> error("请选择专题");
				}
				if($featureid){
					$feature_have_result = $model -> table('sj_feature') -> where(array('feature_id' => $featureid,'status' => 1)) -> select();
					if(!$feature_have_result){
						$this -> error("专题id不存在");
					}
				}
				$feature_have_where['_string'] = "featureid = {$featureid} and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
				$feature_have_been = $model -> table('sj_ad_new') -> where($feature_have_where) -> select();
				if($feature_have_been){
					$this -> error("该专题已存在于其他轮播图");
				}
				break;
			case 2:
				$package   =  preg_replace('/[\s]+/','',$_POST['package']);
				$start_to_page = isset($_POST['start_to_page'])?trim($_POST['start_to_page']):'';
				$have_package = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> find();
				if(!$have_package){
					$this -> error("包名不存在");
				}
				$package_have_where['_string'] = "package = '{$package}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
				$package_have_been = $model -> table('sj_ad_new') -> where($package_have_where) -> select();
				if($package_have_been){
					$this -> error("该包名已存在于其他轮播图");
				}
				break;
			case 3:
				$href = trim($_POST['href']);
				$page_title = trim($_POST['page_title']);
				if(!$_POST['page_title']){
					$this -> error("请填写网页标题");
				}
				if(!$_POST['href']){
					$this -> error("请填写网页链接");
				}
				if($_POST['open_type'])
				{
					$open_type = $_POST['open_type'];
				}
				else
				{
					$this-> error("请选择网页广告打开方式");
				}
				//增加对网址的判断  				2015-3-27 added by shitingting
				$url_have_where['_string'] = "href = '{$href}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1  and extent_id in ({$same_extent_str}) and id !={$id}";
				$url_have_been = $model -> table('sj_ad_new') -> where($url_have_where) -> select();
				if($url_have_been){
					$this -> error("该网址已存在于其他轮播图");
				}
				if($_POST['open_type']==1){
					$parameter_field = json_encode(array('website_is_sync_accout'=>$_POST['is_sync_accout'],'website_is_actionbar'=>$_POST['is_actionbar'],'website_screen_show'=>$_POST['screen_show'],'website_is_h5'=>$_POST['is_h5']));
				}else{
					$parameter_field = '';
				}
				break;
			case 4:
				$activityid = $_POST['activityid'];
				
				if(!$activityid){
					$this -> error("请选择活动");
				}
				if($activityid){
					$activity_have_result = $model -> table('sj_activity') -> where(array('id' => $activityid,'status' => 1)) -> select();
					if(!$activity_have_result){
						$this -> error("活动id不存在");
					}
				}
				$activity_have_where['_string'] = "activityid = {$activityid} and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
				$activity_have_been = $model -> table('sj_ad_new') -> where($activity_have_where) -> select();
				if($activity_have_been){
					$this -> error("该活动已存在于其他轮播图");
				}
				break;
			case 5:
				$map=array();
				$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				if($rcontent!==true)
				{
					$this -> error($rcontent);
					//return $rcontent;
				}
				else
				{
					$page_type_name=$map['page_type'];
					$parameter_field= $map['parameter_field'];

					if($parameter_field)
					{
						$page_have_where['_string'] = "page_name = '".$page_type_name."' and parameter_field = '".quotemeta($parameter_field)."' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
					}
					else
					{
						$page_have_where['_string'] = "page_name = ".$page_type_name." and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
					}
					$page_have_been = $model -> table('sj_ad_new') -> where($page_have_where) -> select();
					if($page_have_been)
					{
						$this -> error("该页面已存在于其他轮播图");
					}	
					else
					{
						$page_name = $map['page_type'];
						$content_type = $map['content_type'];
					}						
				}
				break; 
				//V6.0添加跳转页面
                /*$page_type=$_POST['page_type'];
				if($page_type==4)
				{
					$page_name=trim($_POST['page_name4']);
				}
				elseif($page_type==1)
				{
					$page_name=trim($_POST['page_name1']);
				}
				if(!$page_name)
				{
					$this -> error("页面不能为空");
				}
				else
				{
					$page_type_name = ContentTypeModel::convertPageName2PageType($page_name, $page_type);
					if (!$page_type_name) 
					{
						$this -> error("页面不存在");
					}
					else
					{	
						if($page_type_name=="fixed_bbs_detailpage")
						{
							$bbs_detail_page_id = trim($_POST['bbs_detail_page_id']);
							if(!$_POST['bbs_detail_page_id']){
								$this -> error("请填写帖子TID");
							}
							$page_type_name = "bbs_detailpage_".$bbs_detail_page_id;
						}
						$page_have_where['_string'] = "page_name = '{$page_type_name}' and start_tm <= ".strtotime($end_tm)." and end_tm >= ".strtotime($start_tm)." and status = 1 and extent_id in ({$same_extent_str}) and id !={$id}";
						$page_have_been = $model -> table('sj_ad_new') -> where($page_have_where) -> select();
						if($page_have_been)
						{
							$this -> error("该页面已存在于其他轮播图");
						}	
					}
				}
				break;*/
		}
		$path = date("Ym/d/");
		$config = array(
			'multi_config' => array(),
		);
		list($width_new, $height_new, $type_new, $attr_new)=getimagesize($_FILES['image_new']['tmp_name']);
		list($width_old, $height_old, $type_old, $attr_old)=getimagesize($_FILES['image_old']['tmp_name']);
		list($width_high, $height_high, $type_high, $attr_high)=getimagesize($_FILES['image_high']['tmp_name']);
		list($width_low, $height_low, $type_low, $attr_low)=getimagesize($_FILES['image_low']['tmp_name']);
		list($width_gif, $height_gif, $type_low, $attr_low)=getimagesize($_FILES['image_gif']['tmp_name']);
		list($width_high_644, $height_high_644, $type_high_644, $attr_high_644)=getimagesize($_FILES['image_high_644']['tmp_name']);
		list($width_low_644, $height_low_644, $type_low_644, $attr_low_644)=getimagesize($_FILES['image_low_644']['tmp_name']);
		
		//之前正确的图片尺寸再次判断
		$select=$model->table('sj_ad_new')-> where(array('id' => $id))->select();
		$image_new_url= IMGATT_HOST.$select[0]['image_new'];
		$image_old_url=IMGATT_HOST.$select[0]['image_old'];
		$high_image_url_edit= IMGATT_HOST.$select[0]['high_image_url'];
		$low_image_url_edit=IMGATT_HOST.$select[0]['low_image_url'];
		
		list($width_new_edit, $height_new_edit, $type_new_edit, $attr_new_edit)=getimagesize($image_new_url);
		list($width_old_edit, $height_old_edit, $type_old_edit, $attr_old_edit)=getimagesize($image_old_url);
		list($width_high_edit, $height_high_edit, $type_high_edit, $attr_high_edit)=getimagesize($high_image_url_edit);
		list($width_low_edit, $height_low_edit, $type_low_edit, $attr_low_edit)=getimagesize($low_image_url_edit);
		
		//获取红包助手的频道id
		$red_assis=$model -> table('coop_channel')->where(array('status'=>1,'type'=>2))->field('id')->find();
		if(!empty($_FILES['image_new']['size']))
		{
			$suffix_new = preg_match("/\.(jpg|png)$/", $_FILES['image_new']['name'],$matches_new);
			if (!$suffix_new) {
				$this->error('上传图片格式错误！');
			} 
			//V6.4新增红包助手频道，图片大小和以前的不一样，逻辑和游戏推荐频道一样
			if(strpos($_POST['child_type'],'coop')===0)
			{
				$child_type_arr = explode('_',$_POST['child_type']);
				if($red_assis['id']==$child_type_arr[2])//说明是红包助手
				{
					$is_red = 1;
				}
			}
			if($is_red)
			{
				if($width_new==$this->red_width&&$height_new==$this->red_height)
				{
					$config['multi_config']['image_new'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->red_width,
						'img_p_height' => $this->red_height,
						'img_p_size'   => 1024 * 35,
						'img_s_size'   => 1024 * 90,     
					);
				}
				else
				{
					$this -> error("新版轮播图仅限上传{$this->red_width}*{$this->red_height}尺寸的图片");
				}
			}
			else
			{
				if($width_new==480&&$height_new==181)
				{
					$config['multi_config']['image_new'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => 480,
						'img_p_height' => 181,
						'img_p_size'   => 1024 * 35,
						'img_s_size'   => 1024 * 90,     
					);
				}
				else
				{
				 $this -> error("新版轮播图仅限上传480*181尺寸的图片");
				}
			}
		}
		else
		{
			if(strpos($_POST['child_type'],'coop')===0)
			{
				$child_type_arr = explode('_',$_POST['child_type']);
				if($red_assis['id']==$child_type_arr[2])//说明是红包助手
				{
					$is_red = 1;
				}
			}
			if($is_red)
			{
				if($width_new_edit!==$this->red_width||$height_new_edit!==$this->red_height)
				{
					$this -> error("新版轮播图仅限上传{$this->red_width}*{$this->red_height}尺寸的图片");
				}	
			}
			else
			{
				if($width_new_edit!==480||$height_new_edit!==181)
				{
					$this -> error("新版轮播图仅限上传480*181尺寸的图片");
				}
			}
			
		}
		if(!empty($_FILES['image_gif']['size']))
		{
			$suffix_gif = preg_match("/\.(gif)$/", $_FILES['image_gif']['name'],$matches_gif);
			if (!$suffix_gif) {
				$this->error('上传图片格式错误！');
			} 
			//V6.4新增红包助手频道，图片大小和以前的不一样，逻辑和游戏推荐频道一样
			if(strpos($_POST['child_type'],'coop')===0)
			{
				$child_type_arr = explode('_',$_POST['child_type']);
				if($red_assis['id']==$child_type_arr[2])//说明是红包助手
				{
					$is_red = 1;
				}
			}
			if($is_red)
			{
				if($width_gif==$this->red_gif_width&&$height_gif==$this->red_gif_height)
				{
					$config['multi_config']['image_gif'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->red_gif_width,
						'img_p_height' => $this->red_gif_height,
						'enable_resize' =>false,
						//'img_p_size'   => 1024 * 80,
						//'img_s_size'   => 1024 * 80,  
					);
				}
				else
				{
				 $this -> error("红包助手的gif仅限上传{$this->red_gif_width}*{$this->red_gif_height}尺寸的图片");
				}
			}
			else
			{
			   if($width_gif==$this->gif_width&&$height_gif==$this->gif_height)
				{
					$config['multi_config']['image_gif'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->gif_width,
						'img_p_height' => $this->gif_height,
						'enable_resize' =>false,
						//'img_p_size'   => 1024 * 500,
						//'img_s_size'   => 1024 * 500,     
					);
				}
				else
				{
					$this -> error("6.3gif轮播图仅限上传{$this->gif_width}*{$this->gif_height}尺寸的图片");
				}
			}
		}
		//V6.2轮播图 选择游戏频道、礼包频道和论坛频道的时候图片只上传480*181的图片 其余的不上传 V6.4新增加红包助手和应用热门频道
		$exclude_type = array(
			6 => 1,
			7 => 1,
			8 => 1,
			10 => 1,
			11 => 1,
		);
		if(!isset($exclude_type[$_POST['child_type']]) && strpos($_POST['child_type'],'coop') === false)
		{
			if(!empty($_FILES['image_old']['size']))
			{
				$suffix_old = preg_match("/\.(jpg|png)$/", $_FILES['image_old']['name'],$matches_old);
				if (!$suffix_old) {
					$this->error('上传图片格式错误！');
				} 
				if($width_old==225&&$height_old==125)
				{
					$config['multi_config']['image_old'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => 225,
						'img_p_height' => 125,
						'img_p_size'   => 1024 * 35,
					);
				}
				else
				{
					$this -> error("旧版轮播图仅限上传225*125尺寸的图片");
				}
			}
			else
			{
				if($width_old_edit!==225||$height_old_edit!==125)
				{
					$this -> error("旧版轮播图仅限上传225*125尺寸的图片");
				}
			}
			if(!empty($_FILES['image_high']['size']))
			{
				$suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_high']['name'],$matches_high);
				if (!$suffix_high) {
					$this->error('上传图片格式错误！');
				} 
			   if($width_high==$this->image_width_high&&$height_high==$this->image_height_high)
				{
					$config['multi_config']['image_high'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_high,
						'img_p_height' => $this->image_height_high,
						//'img_p_size'   => 1024 * 80,
						'img_s_size'   => 1024 * 80,     
					);
				}
				else
				{
				 $this -> error("6.0高分图片仅限上传{$this->image_width_high}*{$this->image_height_high}尺寸的图片");
				}
			}
			else
			{
				//编辑旧数据的时候高低分图片如果没有则必填
				if(!$myself['high_image_url'])
				{
					$this -> error("请上传6.0高分轮播图");
				}
				else
				{
					if($width_high_edit!==$this->image_width_high||$height_high_edit!==$this->image_height_high)
					{
						$this -> error("6.0高分图片仅限上传{$this->image_width_high}*{$this->image_height_high}尺寸的图片");
					}
				}
			}
			if(!empty($_FILES['image_low']['size']))
			{
				$suffix_low = preg_match("/\.(jpg|png)$/", $_FILES['image_low']['name'],$matches_low);
				if (!$suffix_low) {
					$this->error('上传图片格式错误！');
				} 
			   if($width_low==$this->image_width_low&&$height_low==$this->image_height_low)
				{
					$config['multi_config']['image_low'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_low,
						'img_p_height' => $this->image_height_low,
						//'img_p_size'   => 1024 * 40,
						'img_s_size'   => 1024 * 40,     
					);
				}
				else
				{
				 $this -> error("6.0低分轮播图仅限上传{$this->image_width_low}*{$this->image_height_low}尺寸的图片");
				}
			}
			else
			{
				if(!$myself['low_image_url'])
				{
					$this -> error("请上传6.0低分轮播图");
				}
				else
				{
					if($width_low_edit!==$this->image_width_low||$height_low_edit!==$this->image_height_low)
					{
						$this -> error("6.0低分轮播图仅限上传{$this->image_width_low}*{$this->image_height_low}尺寸的图片");
					}
				}
			}	
		}
		
		//6.4.4
		if(!empty($_FILES['image_high_644']['size'])) {
			$suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_high_644']['name'],$matches_high);
			if (!$suffix_high) {
				$this->error('上传图片格式错误！');
			}
			if( $width_high_644==$this->image_width_high_644 && $height_high_644 == $this->image_height_high_644 )
			{
				$config['multi_config']['image_high_644'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_high_644,
						'img_p_height' => $this->image_height_high_644,
						// 'img_s_size'   => 1024 * 80,
				);
			}else {
				$this -> error("6.4.4高分图片仅限上传{$this->image_width_high_644}*{$this->image_height_high_644}尺寸的图片");
			}
		}else {
			if(!$myself['high_image_url_644'])
			{
				$this -> error("请上传6.4.4高分广告图");
			}
		}
		//6.4.4
		if(!empty($_FILES['image_low_644']['size'])) {
			$suffix_high = preg_match("/\.(jpg|png)$/", $_FILES['image_low_644']['name'],$matches_high);
			if (!$suffix_high) {
				$this->error('上传图片格式错误！');
			}
			if( $width_low_644==$this->image_width_low_644 && $height_low_644 == $this->image_height_low_644 )
			{
				$config['multi_config']['image_low_644'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => $this->image_width_low_644,
						'img_p_height' => $this->image_height_low_644,
						// 'img_s_size'   => 1024 * 80,
				);
			}else {
				$this -> error("6.4.4低分图片仅限上传{$this->image_width_low_644}*{$this->image_height_low_644}尺寸的图片");
			}
		}else {
			if(!$myself['low_image_url_644'])
			{
				$this -> error("请上传6.4.4低分广告图");
			}
		}

		if (!empty($config['multi_config'])) {
			$list = $this->_uploadapk(0, $config);
			foreach($list['image'] as $val) {
				if (empty($val['url'])) continue;
				if ($val['post_name'] == 'image_new') {
					$image_new = $val['url'];
					$data['image_new'] = $image_new;
					$data['image_new_90'] = $val['url_resize'];
					$data['image_new_o'] = $val['url_original'];
				}
				if ($val['post_name'] == 'image_old') {
					$image_old = $val['url'];
					$data['image_old'] = $image_old;
				}
				if ($val['post_name'] == 'image_high') {
					$data['high_image_url_80'] = $val['url_resize'];
					$data['high_image_url'] = $val['url_original'];
				}
				if ($val['post_name'] == 'image_low') {
					$data['low_image_url_40'] = $val['url_resize'];
					$data['low_image_url'] = $val['url_original'];
				}
				if ($val['post_name'] == 'image_gif') {
					//$data['low_image_url_40'] = $val['url_resize'];
					$data['gif_image_url'] = $val['url_original'];
				}
				if ($val['post_name'] == 'image_high_644') {
					$data['high_image_url_644'] = $val['url_original'];
				}
				if ($val['post_name'] == 'image_low_644') {
					$data['low_image_url_644'] = $val['url_original'];
				}
			}
		}

		//市场版本选择  非必填
		$data['version_type'] = $_POST['type'];
		if($_POST['type']==1)
		{
			$data['version_code'] = $_POST['version_code1'];
		}
		if($_POST['type']==2)
		{
			$data['version_code'] = $_POST['version_code2'];
		}
		if($_POST['type']==3)
		{
			$data['version_code'] = $_POST['force_update_version'];
		}
		
		$data['extent_id'] = $myself['extent_id'];
		$data['ad_name'] = $ad_name;
		$data['ad_type'] = $ad_type;
		$data['package'] = $package;
		$data['start_to_page'] = isset($start_to_page) ? $start_to_page : '';
		$data['href'] = $href;
		$data['featureid'] = $featureid;
		$data['page_title'] = $page_title;
		$data['open_type'] = $open_type;
		$data['beid'] = $beid;
		$data['activityid'] = $activityid;
		$data['note'] = $note;
		$data['prob'] = $prob;
		//$data['page_type'] = $page_type;
		$data['page_name'] = $page_type_name;
		$data['content_type'] = $content_type;

		
		$parameter_field=json_decode($parameter_field,true);
		
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

		$data['parameter_field'] = json_encode($parameter_field);

		$data['start_tm'] = strtotime($start_tm);
		$data['end_tm'] = strtotime($end_tm);
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$data['co_type'] = $co_type;
		$this->get_csv_data($data);
		if($data['package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm']);
			if($shield_error){
			    $this -> error($shield_error);
			}
		}
		
		$log_result = $this -> logcheck(array('id' => $id),'sj_ad_new',$data,$model);
		if(strtotime($start_tm) > time())
		{
			$my_time = 3;
		}
		elseif(strtotime($start_tm) <= time() && strtotime($end_tm) >= time())
		{
			$my_time = 2;
		}
		elseif(strtotime($end_tm) < time())
		{
			$my_time = 1;
		}
		if($_POST['life']==1)
		{
			if($data['image_new']=="")
			{
				$select=$model->table('sj_ad_new')-> where(array('id' => $id))->select();
				$data['image_new'] = $select[0]['image_new'];
				$data['image_new_90'] = $select[0]['image_new_90'];
				$data['image_new_o'] = $select[0]['image_new_o'];
			}
			if($data['image_old']=="")
			{
				$select_old=$model->table('sj_ad_new')->where(array('id' => $id))->select();
				$data['image_old'] = $select_old[0]['image_old'];
			}
			if($data['high_image_url']=="")
			{
				$select_high=$model->table('sj_ad_new')->where(array('id' => $id))->select();
				$data['high_image_url'] = $select_high[0]['high_image_url'];
				$data['high_image_url_80'] = $select_high[0]['high_image_url_80'];
			}
			if($data['low_image_url']=="")
			{
				$select_low=$model->table('sj_ad_new')->where(array('id' => $id))->select();
				$data['low_image_url'] = $select_old[0]['low_image_url'];
				$data['low_image_url_40'] = $select_old[0]['low_image_url_40'];
			}
			if($data['gif_image_url']=="")
			{
				$select_gif=$model->table('sj_ad_new')->where(array('id' => $id))->select();
				$data['gif_image_url'] = $select_old[0]['gif_image_url'];
			}
			//6.4.4
			if( $data['high_image_url_644']=="" || $data['low_image_url_644']=="" ) {
				$select_644=$model->table('sj_ad_new')->where(array('id' => $id))->select();
				$data['high_image_url_644'] = $select_644[0]['high_image_url_644'];
				$data['low_image_url_644']  = $select_644[0]['low_image_url_644'];
			}
			$ret=$model->table('sj_ad_new')->add($data);
			if($ret){
				$this -> writelog("已复制上线了广告名称为【{$ad_name}】原ID为【{$id}】的广告轮播图",'sj_ad_new',"{$id}",__ACTION__ ,"","edit");
				$this -> assign("jumpUrl","/index.php/Sj/Adextent/ad_list/extent_id/{$myself['extent_id']}/my_time/{$my_time}/cid/{$cid}");
				$this -> success("复制上线成功");
			}
			else
			{
				$this -> error("复制上线失败");
			}
		}
		else
		{
			$result = $model -> table('sj_ad_new') -> where(array('id' => $id)) -> save($data);
			if($result){
				$this -> writelog("已编辑id为{$id}的广告轮播图".$log_result,'sj_ad_new',"{$id}",__ACTION__ ,"","edit");
				$this -> assign("jumpUrl","/index.php/Sj/Adextent/ad_list/extent_id/{$myself['extent_id']}/my_time/{$my_time}/cid/{$cid}");
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
		
	}
	
	function del_ad(){
		$model = new Model();
		$id = $_GET['id'];
		$my_time = $_GET['my_time'];
		$myself = $model -> table('sj_ad_new') -> where(array('id' => $id)) -> find();
		$result = $model -> table('sj_ad_new') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的广告轮播图",'sj_ad_new',"{$id}",__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Sj/Adextent/ad_list/extent_id/{$myself['extent_id']}/my_time/{$my_time}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	
	}
	//8张轮播图的有关函数
	function my_intersect($a, $b) 
	{
		$c = max($a[0], $b[0]);
		$d = min($a[1], $b[1]);
		$s1 = date('Y-m-d H:i:s', $a[0]);
		$e1 = date('Y-m-d H:i:s', $a[1]);	
		$s2 = date('Y-m-d H:i:s', $b[0]);
		$e2 = date('Y-m-d H:i:s', $b[1]);	
		$s3 = date('Y-m-d H:i:s', $c);
		$e3 = date('Y-m-d H:i:s', $d);	
		if ($c<=$d) {
			return array($c, $d);
		} else {
			//var_dump($c, $d);
			//echo "{$s1}~{$e1} {$s2}~{$e2} $s3, $e3, +++++++\n";	
			return false;
		}
	}
	function reorder($a, $b) 
	{
		if ( $a[0]  ==  $b[0] ) {
			if ( $a[1]  ==  $b[1] ) {
				return  0 ;
			} else {
				return ( $a[1]  <  $b[1] ) ? - 1  :  1 ;
			}
		}
		return ( $a[0]  <  $b[0] ) ? - 1  :  1 ;
	}
}
