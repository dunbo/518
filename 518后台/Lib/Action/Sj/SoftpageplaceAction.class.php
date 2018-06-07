<?php

class SoftpageplaceAction extends CommonAction {


	function addsoft(){
		$add_model = M("soft");
		$category_model = M("category");
		$place_model = M("soft_page_place");
		$soft_category = $category_model -> where("status = 1") -> select();
		$soft_type_add = escape_string($_GET['soft_type_add']);
		//$date['id'] = escape_string($_GET['id']);
		$date['package'] = escape_string($_GET['package']);

			if(empty($date['id']) && !empty($date['package'])){
				if( mb_strlen($date['package']) >= 2){
					$search_soft = $add_model -> field("softid,package") -> where(" package like '%".$date['package']."%' and status = 1") -> select();	
				}
			}elseif(empty($date['package']) && !empty($date['id'])){
				$search_soft = $add_model -> where("softid =".$date['id']." and status=1 ") -> select();
				$date['package'] = $search_soft[0]['package'];
				
			
			}elseif(empty($date['package']) && empty($date['id'])){
				$this -> error("请输入软件ID或软件包名");
			}
			
				if($search_soft){
						$this -> assign("select",1);
					}else{
						$this -> error("该软件不存在或已被删除");
					}
		//echo $date['id'].'aaaa';
		$this -> assign("package_list",$search_soft);
		$this -> assign("softid",$date['id']);
		$this -> assign("package",$date['package']);
		$this -> assign("soft_type_add",$soft_type_add);
		$this -> assign("soft_page_category",$soft_category);
		$this -> display("softpageplace");
	}	
	
	function addsoft_do(){
	
		$search_model = M("soft");
		$add_do_model = M("soft_page_place");
		$date['package'] = escape_string($_GET['soft_package']);
		//$date['package'] = escape_string($_GET['soft_package']);
		
		$date['soft_type'] = escape_string($_GET['soft_type_add']);
		$date['pos'] = escape_string($_GET['pos']);
		$date['start_tm'] = escape_string(strtotime($_GET['begintime']));
		
		$date['status'] = 1;
		$date['create_tm'] = time();
		$date['update_tm'] = time();
		$soft_name = $search_model -> field("softname") -> where(" package='".$date['package']."' and status = 1") -> select();
		//echo $search_model -> getLastSql();
		if(!$soft_name){
			$this -> error("对不起，此软件不存在于软件列表");
		}
		$date['soft_name'] = $soft_name[0]['softname'];
		
		$date['log'] = $date['pos'].','.time();
		
		//$date_package = $search_model -> where("package = ".$date['package']." and status = 1") -> select();
		//var_dump($date_package);		
		//$date['package'] = $date_package[0]['package'];
		//echo $date['package'].'aaa';
			//获取软件类别的名称
		$category = M("category");
		
		//echo $date['soft_type'];
		$type_datech = explode('_',$date['soft_type']);
		//print_r($type_datech);exit;
		//var_dump($type_datech);
		if($type_datech[1] == 'new'){
			$date['type_name'] = "最新";
		}elseif($type_datech[2] == 'new'){
			$category_name = $category ->field("name") -> where("category_id = ".$type_datech[1]." and status = 1") ->select();
			//var_dump($category_name);
			$date['type_name'] = $category_name[0]['name']."_最新";
		}elseif($type_datech[1] == 'hot'){
			$date['type_name'] = "最热";
		}elseif($type_datech[2] == 'hot'){
			$category_name = $category ->field("name") -> where("category_id = ".$type_datech[1]." and status = 1") ->select();
			$date['type_name'] = $category_name[0]['name']."_最热";
		}elseif($type_datech[1] == '1d'){
			$date['type_name'] = "日排行";
		}elseif($type_datech[1] == '7d'){
			$date['type_name'] = "周排行";
		}elseif($type_datech[1] == '30d'){
			$date['type_name'] = "月排行";
		}
		$date['stop_tm'] = strtotime(date('Ymd 23:59:59',escape_string(strtotime($_GET['endtime']))));
	
		if($date['start_tm'] > $date['stop_tm']){
			$this -> error("开始时间必须小于结束时间");
		}
		
		//echo $date['soft_name'].'aaaa';
		if(empty($date['soft_type'])){
			$this -> error("请选择软件类别");
		}
		if(empty($date['pos'])){
			$this -> error("请填写软件位置");
		}
		if(empty($date['start_tm'])){
			$this -> error("请选择开始时间");
		}
		if(empty($date['stop_tm'])){
			$this -> error("请选择结束时间");
		}
		
		$select_soft_same = $add_do_model -> where("package =".$date['package']." and soft_type='".$date['soft_type']."' and status = 1") -> select();
		//echo $add_do_model -> getLastSql();
		if($select_soft_same){
			$this -> error("软件在该类别里已存在");
		}
		$select_same = $add_do_model -> where("start_tm <='".$date['stop_tm']."' and stop_tm>='".$date['start_tm']."' and pos ='".$date['pos']."' and soft_type = '".$date['soft_type']."' and status =1") -> select();
		//echo $add_do_model -> getLastSql();
		if($select_same){
			$this -> error("对不起，相同时间相同类别里软件不能有相同位置！");
		}else{
			$affect = $add_do_model -> add($date);
				//echo $add_do_model -> getLastSql();
				//exit;
			if($affect){
				$this->writelog("添加软件id为{$affect},包名为{$date['package']}软件类别为：{$date['type_name']},位置为{$date['pos']}","soft_page_place",$date['package']);
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Softpageplace/softlist/soft_type_show/'.$date['soft_type'].'/submit/确定');
				$this -> success("软件添加成功");
			}else{
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Softpageplace/softlist/soft_type/'.$date['soft_type']);
				$this -> error("添加失败！！");
			}
		}
	
	}

	
	function softlist(){
		$soft_type = escape_string($_GET['soft_type']);
		$soft_model = M("soft_page_place");
		$soft_category = M("category");
		$soft_type_show = escape_string($_GET['soft_type_show']);
		if($soft_type_show == 'top_1_hot' || $soft_type_show == 'top_2_hot'){
			$show_go = 1;
		}
		$soft_time = escape_string($_GET['soft_time']);
		$now = strtotime(date('Y-m-d',time()));
		if($soft_type_show == "all"){
		
			if($soft_type_show == "all" && $soft_time == "online"){
				 $where['_string'] = " start_tm <='".$now."' and stop_tm >= '".$now."' and status =1 ";
			}elseif($soft_type_show == "all" && $soft_time == "outline"){
				$where['_string'] = " stop_tm <'".$now."' and status =1";
			}elseif($soft_type_show == "all" && $soft_time == "furline"){
				$where['_string'] = " start_tm > '".$now."' and status =1";
			}elseif($soft_type_show == "all" && $soft_time == "all"){
				$where['_string'] = "status = 1";
			}
			
		}else{
			if($soft_time == "" || $soft_time == "all"){
				$where['_string'] = "soft_type ='".$soft_type_show."'  and status =1";
			}elseif($soft_time == "online" ){
				$where['_string'] = " soft_type ='".$soft_type_show."' and start_tm <= '".$now."' and stop_tm >= '".$now."' and status =1";
			}elseif($soft_time == "outline"){
				$where['_string'] = "soft_type ='".$soft_type_show."' and stop_tm < '".$now."' and status =1";
			}elseif($soft_time == "furline"){
				$where['_string'] = "soft_type ='".$soft_type_show."' and start_tm > '".$now."' and status =1";
			}
		}

		 
        import("@.ORG.Page");
        $count = $soft_model -> where($where) -> count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $soft_list = $soft_model -> where($where) -> order("pos")-> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		//$soft_list = $soft_model -> where("soft_type ='".$soft_type_show."' and status =1") -> order("pos") -> select();
		//echo $soft_model -> getLastSql();
	
		$soft_page_category = $soft_category -> where(" status = 1") -> select();
		
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
		$this -> assign('show_go',$show_go);
		$this -> assign("soft_time",$soft_time);
		$this -> assign("soft_type_show",$soft_type_show);
		$this -> assign("soft_page_category",$soft_page_category);
		$this -> assign("soft_list",$soft_list);
		$this -> display("softpageplace");
	}
	
	function setlist(){
		$model = new Model();
		$show_go = $_GET['show_go'];
		$where['category'] = $show_go;
		$result = $model -> table('sj_list_count') -> where($where) -> field('count') -> select();
		$count = $result[0]['count'];
		$this -> assign('count',$count);
		$this -> assign('show_go',$show_go);
		$this -> display("setlist");
	}
	
	function setlist_do(){
		$model = new Model();
		$map['count'] = $_GET['math'];
		$map['category'] = $_GET['category'];
		$map['status'] = 1;
		$map_go['status'] = 1;
		$map_go['count'] = $_GET['math'];
		
		if($_GET['math'] == null){
			$this -> error("榜单设置数不能为空");
		}
		if($_GET['math'] > 100){
			$this -> error("榜单设置数不能大于100");
		}
		if($_GET['math'] < 0){
			$this -> error("榜单设置数不能小于0");
		}
		$where['category'] = $_GET['category'];
		$category_go = $model -> table('sj_list_count') -> where($where) -> select();

		if($category_go && $_GET['math']){
			$map_go['updatetime'] = time();
			$affect = $model -> table('sj_list_count') -> where($where) -> save($map_go);
		}else{
			$map['createtime'] = time();
			$affect = $model -> table('sj_list_count') -> add($map);
		}
	
		if($affect){
				$this -> writelog("软件库管理_频道运营管理_已设置类别为".$_GET['category']."的榜单数量为".$_GET['math']);
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Softpageplace/softlist/soft_type_show/'.$_GET['category'].'/submit/确定');
				$this -> success("设置成功");
		}
	}
	function deletesoft(){
		$soft_model = M("soft_page_place");
		$soft_id = escape_string($_GET['id']);
		$soft_type = escape_string($_GET['soft_type']);
		
		$category = M("category");
		$type_datech = explode('_',$soft_type);
		if($type_datech[1] == 'new'){
			$date['type_name'] = "最新";
		}elseif($type_datech[2] == 'new'){
			$category_name = $category ->field("name") -> where("category_id = ".$type_datech[1]." and status = 1") ->select();
			$date['type_name'] = $category_name[0]['name']."_最新";
		}elseif($type_datech[1] == 'hot'){
			$date['type_name'] = "最热";
		}elseif($type_datech[2] == 'hot'){
			$category_name = $category ->field("name") -> where("category_id = ".$type_datech[1]." and status = 1") ->select();
			$date['type_name'] = $category_name[0]['name']."_最热";
		}elseif($type_datech[1] == '1d'){
			$date['type_name'] = "日排行";
		}elseif($type_datech[1] == '7d'){
			$date['type_name'] = "周排行";
		}elseif($type_datech[1] == '30d'){
			$date['type_name'] = "月排行";
		}
		
		
		$pos = escape_string($_GET['pos']);
		$update['update_tm'] = time();
		$update['status'] = 0;
		$pos_result = $soft_model -> where( "status = 1 and soft_type='".$soft_type."' and id=".$soft_id." ") -> find();
		
		$affect = $soft_model -> query("update __TABLE__ set status =".$update['status'].",update_tm = ".$update['update_tm']." where id = ".$soft_id." and soft_type = '".$soft_type."'");
		//echo $soft_model -> getLastSql();
		if(empty($affect)){
			$where_soft['_string'] = 'id = '.$soft_id;
			$pos_list = $soft_model -> where($where_soft) -> field('start_tm,stop_tm') -> select();
			$start_time = $pos_list[0]['start_tm'];
			$end_time = $pos_list[0]['stop_tm'];
			if($start_time > time()){
				$where.=" pos >".$pos." and status = 1 and soft_type ='".$soft_type."' and start_tm > ".time()."";
				$go = 'furline';
			}else if($end_time < time()){
				$where.=" pos >".$pos." and status = 1 and soft_type ='".$soft_type."' and stop_tm < ".time()."";
				$go = 'outline';
			}else if($start_time < time() && $end_time > time()){
				$where.=" pos >".$pos." and status = 1 and soft_type ='".$soft_type."' and start_tm < ".time()." and stop_tm > ".time()."";
				$go = 'online';
			}

			$this->writelog('软件库管理_频道运营管理_删除id为'.$soft_id.'包名为'.$pos_result['package']."分类为{$date['type_name']}","sj_soft_page_place",$pos_result['package']);
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Softpageplace/softlist/soft_type_show/'.$soft_type.'/soft_time/'.$go.'/submit/确定');
		    $this -> success("软件删除成功");
		}else{
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Softpageplace/softlist');
			$this -> error("删除失败！！");
		}
		
	}
	
	function searchsoft(){
		$soft_model = M("soft_page_place");
		$category_model = M("category");
		$soft_id = escape_string($_GET['search_id']);
		$soft_search = escape_string($_GET['soft_search']);
		//$soft_type_search = escape_string($_GET['soft_type_search']);
		$soft_name = escape_string($_GET['search_name']);
		$soft_pkg = escape_string($_GET['search_pkg']);
		$soft_category = $category_model -> field("category_id,name") -> where("status =1") -> select();
		$where['status']=1;
		if(empty($soft_id) && empty($soft_pkg)){
			$this -> error("请填写查询条件");
		}
		if(!empty($soft_id)){
			$where['id']=$soft_id;
		}
		if(!empty($soft_pkg)){
			$where['package']=$soft_pkg;
		}
		/* if(empty($soft_type_search)){
			$this -> error("请选择软件类别！");
		} */
		$soft_search = $soft_model -> where($where) -> select();
		//echo $soft_model -> getLastSql();
		$this -> assign("search_id",$soft_id);
		$this -> assign("search_name",$soft_name);
		//$this -> assign("soft_type_search",$soft_type_search);
		$this -> assign("search_pkg",$soft_pkg);
		$this -> assign("soft_list",$soft_search);
		$this -> assign("soft_page_category",$soft_category);
		$this -> display("softpageplace");
	}
	
	function alterpos(){
		$pos_model = M("soft_page_place");
		$m = $pos = escape_string($_GET['pos']); 
		$n = $curpos = escape_string($_GET['curpos']);
		$soft_id = escape_string($_GET['softid']);
		$soft_type = escape_string($_GET['soft_type']);
		$category = M("category");
		$type_datech = explode('_',$soft_type);
		if($type_datech[1] == 'new'){
			$date['type_name'] = "最新";
		}elseif($type_datech[2] == 'new'){
			$category_name = $category ->field("name") -> where("category_id = ".$type_datech[1]." and status = 1") ->select();
			//var_dump($category_name);
			$date['type_name'] = $category_name[0]['name']."_最新";
		}elseif($type_datech[1] == 'hot'){
			$date['type_name'] = "最热";
		}elseif($type_datech[2] == 'hot'){
			$category_name = $category ->field("name") -> where("category_id = ".$type_datech[1]." and status = 1") ->select();
			$date['type_name'] = $category_name[0]['name']."_最热";
		}elseif($type_datech[1] == '1d'){
			$date['type_name'] = "日排行";
		}elseif($type_datech[1] == '7d'){
			$date['type_name'] = "周排行";
		}elseif($type_datech[1] == '30d'){
			$date['type_name'] = "月排行";
		}
		$old_log = escape_string($_GET['log']);
		$start_tm = escape_string($_GET['start_tm']);
		$stop_tm = escape_string($_GET['stop_tm']);
		$pos_select = $pos_model -> where("start_tm <='".$stop_tm."' and stop_tm>='".$start_tm."' and pos ='".$m."' and soft_type = '".$soft_type."' and status =1") -> select();
		if($pos_select){
			$this -> error("对不起，相同时段内排序不能相同");
		}
		
		$pos_time = $pos_model -> where("id = '".$soft_id."' and status = 1") -> field('start_tm,stop_tm') -> select();
		$start_time = $pos_time[0]['start_tm'];
		$end_time = $pos_time[0]['stop_tm'];
		if($start_time > time()){
			$go = 'furline';
		}
		if($end_time < time()){
			$go = 'outline';
		}
		if($start_time < time() && $end_time > time()){
			$go = 'online';
		}

		$log = $old_log."|".$m.",".time();
		$update_tm = time();
		$pos_result=$pos_model->where(array("id"=>$soft_id,"soft_type"=>$soft_type,"status" => 1))->find();
		
		$pos_model -> query("update __TABLE__ set pos=".$m.",log='".$log."' where id=".$soft_id." and soft_type='".$soft_type."'");
		//echo $pos_model -> getLastSql();
			
		if($pos_model){
			$this -> writelog("软件库管理_频道运营管理_修改了软件id=".$soft_id."包名为".$pos_result['package']."的位置变为:".$m."软件类别为：{$date['type_name']}","sj_soft_page_place",$pos_result['package']);
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Softpageplace/softlist/soft_type_show/'.$soft_type.'/soft_time/'.$go.'/submit/确定/');
			$this -> success("修改成功");
			}else{
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Softpageplace/softlist');
				$this -> error("修改失败");
			}
	}	
		
	function pageplacerecord(){
		$record_model = M("soft_page_place");
		$category_model = M("category");
		$soft_type_record = escape_string($_GET['soft_type_record']);
		$start_tm = escape_string(strtotime($_GET['begintime']));
		$stop_tm = escape_string(strtotime($_GET['endtime']));
		$soft_category = $category_model -> where("status = 1") -> select();
		if(empty($start_time)||empty($stop_tm)){
				$start_time=time()-86400*10;
				$end_time=time()+86400;
			}
		import("@.ORG.Page");
        $count = $record_model -> where(" soft_type ='".$soft_type_record."' and start_tm <=".$stop_tm." and stop_tm >=".$start_tm) -> count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $soft_list = $record_model -> where(" soft_type ='".$soft_type_record."' and start_tm <=".$stop_tm." and stop_tm >=".$start_tm) -> order("pos")-> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		//echo $record_model -> getLastSql();
		//var_dump($soft_list).'aaaa';
		$soft_list_array = array();
		$log_list_array= array();
		foreach($soft_list as $key => $val){
			$soft_list_array[$key]['id'] = $val['id'];
			$log_array = explode('|',$val['log']);
			//var_dump($val);
		//var_dump($log_array);
		//var_dump(array_pop($log_array));
				foreach($log_array as $k=>$info){
					$detach_log_array=explode(",",$info);
					$log_list_array[$soft_list_array[$key]['id']][$k]['pos']=$detach_log_array[0];	
					$log_list_array[$soft_list_array[$key]['id']][$k]['update_tm']=date("Y-m-d H:i:s",$detach_log_array[1]);
				//var_dump($log_list_array);
				}
		}
		
		
			
				//var_dump($log_list_array);
		$start_tm = date('Y-m-d',$start_tm);
		$stop_tm = date('Y-m-d',$stop_tm);
		$Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
		$this -> assign("start_tm",$start_tm);
		$this -> assign("stop_tm",$stop_tm);
		$this -> assign("soft_list",$soft_list);
		$this -> assign("log_list",$log_list_array);
		$this -> assign("soft_type_record",$soft_type_record);
		$this -> assign("soft_page_category",$soft_category);
		$this -> display("pageplacerecord");
	}
	
}
?>