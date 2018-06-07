<?php
class SendnumAction extends CommonAction {
	//添加活动
	function add_active(){
		$model = D('sendNum.sendNum');
		$typelist = $model -> table('sendnum_active_type') -> where('status = 1') ->select();
		$this -> assign('start_tm',date('Y-m-d'));
		$this -> assign('end_tm',date('Y-m-d',strtotime('1 day')));
		$this -> assign('typelist',$typelist);
		$this -> display('add_active');
	}
	//添加活动_do
	function add_active_do(){
		$active_info = $_POST;
		foreach ($active_info['rule_type'] as $val) {
			$active_info['typeid'] |= $val;
		}
		$model = D('sendNum.sendNum');
		$where = array(
			'active_name' => $active_info['active_name'],
		);
		$count = $model -> table('sendnum_active') -> where($where) -> count();
		if(mb_strlen(trim($active_info['active_name'])) < 2){
			$this -> error('活动名称无效');
		}
		//modify
		if($count){
			$this -> error('该活动名已经存在');
		}
		$data = array();
		$data['active_name'] = $active_info['active_name'];
		$start_tm = strtotime($active_info['begintime'].' '.$active_info['hour1'].':'.$active_info['min1'].':'.$active_info['sec1']);
		$endtm = $active_info['endtime'];
		$end_tm = strtotime($endtm.' '.$active_info['hour2'].':'.$active_info['min2'].':'.$active_info['sec2']);
		if($start_tm > $end_tm){
			$this -> error('无效时间');
		}
		$data['start_tm'] = $start_tm;
		$data['end_tm'] = $end_tm;

		$format_conf_cnt = preg_match('/^[1-9][0-9]{0,9}$/', $active_info['conf_cnt']);

		$rule_all = ($active_info['typeid'] | 64) == $active_info['typeid'] ? true : false;
		if (isset($active_info['conf_cnt']) && !$rule_all && !$format_conf_cnt) {
			$this -> error('发号数量限制格式不正确,请填写最多10位的数字');
		}

		if (!$rule_all) {
			$data['conf_cnt'] = $active_info['conf_cnt'];
		} else {
			$data['conf_cnt'] = 0;
		}

		$data['active_type'] = $active_info['typeid'];


		if(!$_SESSION['admin']['admin_id']) $this -> error('非法登录');
		$data['creater_id'] = $_SESSION['admin']['admin_id'];
		$data['creater'] = $_SESSION['admin']['admin_user_name'];
		$data['status'] = 1;
		if(empty($data)) $this -> error('数据库操作失败');
		$add_active_id = $model -> add_active($data);
		if(!$add_active_id) $this -> error('数据插入失败');
		$this -> writelog("添加活动 id:{$add_active_id}",'sendnum.sendnum_active',$add_active_id,__ACTION__ ,"","add");
		$active_type = $active_info['typeid'];
		list($num_sum,$num_ab_sum) = $this -> active_num_add($add_active_id,$active_type,$model);
		if($num_ab_sum > 0){
			$where = array(
				'id' => $add_active_id,
			);
			$save = array(
				'num_cnt' => $num_ab_sum,
			);
			//按总量
			if ($active_info['typeid'] == 64) {
				$save['conf_cnt'] = $num_ab_sum;
			}

			$model -> active_save($where,$save);
			$this -> writelog("添加活动 id:{$add_active_id}的号码为 {$num_ab_sum} 个",'sendnum.sendnum_active',$add_active_id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Sendnum/active_list');
			$this -> success("活动添加成功,实际上传{$num_ab_sum}个号码！");
		}else{
			$where = array(
				'id' => $add_active_id
			);
			$model -> table('sendnum_active') -> where($where) -> delete();
			$this -> error('活动添加失败,请检查号码的长度是否在25个字符以内');
		}
	}
	//活动列表
	function active_list(){
		$model = D('sendNum.sendNum');
		$where[] = "active_from=1";
		$url_suffix = "";
		if(isset($_GET['active_name']))
		{
			$where[] = "active_name like '%{$_GET['active_name']}%'";
			$url_suffix = $url_suffix."/active_name/".$_GET['active_name'];
		}

		if(!isset($_GET['search_type'])) {
			$_GET['search_type'] = 2;

		}
		$url_suffix = $url_suffix."/search_type/".$_GET['search_type'];
		$this->url_suffix = $url_suffix;
// 		echo $url_suffix;exit();
		if(isset($_GET['order'])){
			$order_str = "";
			if($_GET['order'] =='id') {
				$order = ($_GET['type'] == 2) ?  $_GET['type'] : 1;
				if($order == 2){
					$order_str = $_GET['order']." desc";
					$order = 1;
				}else if($order == 1){
					$order_str = $_GET['order']." asc";
					$order = 2;
				}
				$this -> assign('order',$order);
			}else if($_GET['order'] == 'start_tm'){
				$order = ($_GET['type'] == 2) ?  $_GET['type'] : 1;
				if($order == 2){
					$order_str = $_GET['order']." desc";
					$order = 1;
				}else if($order == 1){
					$order_str = $_GET['order']." asc";
					$order = 2;
				}
				$this -> assign('order',$order);
			}
		}else{
			$order_str = 'id desc';
		}
		switch ($_GET['search_type']){
			case 1 : break;
			case 2 : $where[] =  "start_tm < ".time()." and end_tm > ".time();break;
			case 3 : $where[] =  "start_tm > ".time();break;
			case 4 : $where[] =  "end_tm < ".time();break;
			case 5 : $where[] =   "start_tm < ".time()." and end_tm > ".time()." and status = 1";break;
			case 6 : $where[] =  "start_tm < ".time()." and end_tm > ".time()." and status = 0";break;
			//default: $where[] =  "start_tm < ".time()." and end_tm > ".time();break;
		}
		$where_string = implode(' and ',$where);
		$cnt = $model -> table('sendnum_active') -> where($where_string) -> count();
		import('@.ORG.Page');
		$page = new Page($cnt, 10);
		$activelist = $model -> table('sendnum_active') -> where($where_string) -> order($order_str) -> limit($page->firstRow . ',' . $page->listRows) -> select();
		$this -> assign('page',$page -> show());
		$active_type_list = array(
			array(1,'全部'),
			array(2,'进行中'),
			array(3,'未开始'),
			array(4,'已结束'),
			array(5,'正常'),
			array(6,'暂停'),
		);
// 		echo "<pre>";
// 		print_r($activelist);
// 		echo "</pre>";
// 		exit();
		$this -> assign('active_type_list',$active_type_list);
		$this -> assign('list',$activelist);
		$this -> assign('search_type',$_GET['search_type']);
		$this -> assign('active_name',$_GET['active_name']);
		$this -> display('active_list');
	}
	//活动编辑
	function active_modify(){
		$id = $_GET['id'];
		$where = array(
			'id' => $id,
		);
		$model = D('sendNum.sendNum');
		$url_suffix = "";
		if(isset($_GET['active_name']))
		{
			$url_suffix = $url_suffix."/active_name/".$_GET['active_name'];
		}
		if(isset($_GET['search_type']))
		{
			$url_suffix = $url_suffix."/search_type/".$_GET['search_type'];
		}
		$this->url_suffix = $url_suffix;
		$result = $model ->table('sendnum_active') -> where($where) -> select();
		if(count($result) == 0) $this -> error('该活动不存在！');
		$where['status'] = 0;
		$count = $model ->table('sendnum_active') -> where($where) -> count();
		if($count == 0 && !isset($_GET['view'])) $this -> error('当前页面已失效！');
		$typelist = $model -> table('sendnum_active_type') -> where('status = 1') ->select();
		$type = $model -> table('sendnum_active_type') -> where('id = '.$result[0]['active_type']) -> select();
		$start_tm = date("Y-m-d H:i:s",$result[0]['start_tm']);
		$date_arr = explode(' ',$start_tm);
		$time_arr = explode(':',$date_arr[1]);
		$result[0]['hour1'] = $time_arr[0];
		$result[0]['min1'] = $time_arr[1];
		$result[0]['sec1'] = $time_arr[2];
		$end_tm = date("Y-m-d H:i:s",$result[0]['end_tm']);
		$date_arr = explode(' ',$end_tm);
		$time_arr = explode(':',$date_arr[1]);
		$result[0]['hour2'] = $time_arr[0];
		$result[0]['min2'] = $time_arr[1];
		$result[0]['sec2'] = $time_arr[2];
		$this -> assign('typelist',$typelist);
		$this -> assign('result',$result[0]);
		if(isset($_GET['view'])) $this -> assign('view',$_GET['view']);
		$this -> assign('type_name',$type[0]['type_name']);
		$this -> display('active_modify');
	}

	//活动编辑_do
	function active_modify_do() {
		$active_info = $_POST;
		foreach ($active_info['rule_type'] as $val) {
			$active_info['typeid'] |= $val;
		}
		$model = D('sendNum.sendNum');
		$where = array(
			'id' => $active_info['active_id'],
		);
	    if(mb_strlen(trim($active_info['active_name'])) < 2){
            $this -> error('活动名称无效');
        }
        $url_suffix = $_POST['url_suffix'];
		$endtm = $active_info['endtime'];
		$start_tm = strtotime($active_info['begintime'].' '.$active_info['hour1'].':'.$active_info['min1'].':'.$active_info['sec1']);
		$end_tm = strtotime($endtm.' '.$active_info['hour2'].':'.$active_info['min2'].':'.$active_info['sec2']);
		if ( $start_tm > $end_tm) {
			$this->error('无效时间');
		}
		$count = $model -> table('sendnum_active') -> where("id = {$active_info['active_id']} and status = 1") -> count();
		if($count > 0) $this -> error('当前页面已失效！');
		$data = array(
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'active_type' => $active_info['typeid'],
			'active_name' => $active_info['active_name'],
		);

		$format_conf_cnt = preg_match('/^[1-9][0-9]{0,9}$/', $active_info['conf_cnt']);

		$rule_all = ($active_info['typeid'] | 64) == $active_info['typeid'] ? true : false;
		if (isset($active_info['conf_cnt']) && !$rule_all && !$format_conf_cnt) {
			$this -> error('发号量限制格式不正确,请填写最多10位的数字');
		}

		if (!$rule_all) {
			$data['conf_cnt'] = $active_info['conf_cnt'];
		} else {
			$doc = $model -> table('sendnum_active') -> where("id = {$active_info['active_id']}") -> select();
			$num_cnt = $doc[0]['num_cnt'];
			$data['conf_cnt'] = $num_cnt;
		}


		$count = $model -> table('sendnum_active') -> where("active_name = '{$active_info['active_name']}' and id <> {$active_info['active_id']}") -> count();
		if($count) $this -> error('活动名称已存在！');
		$log_result = $this->logcheck(array('id'=>$active_info['active_id']),'sendnum_active',$data,$model);
		$affect = $model -> active_save($where,$data);

		if($affect){
			$this -> writelog("修改active id为{$active_info['active_id']} 的内容 data:".json_encode($data),'sendnum.sendnum_active',$active_info['active_id'],__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Sendnum/active_list'.$url_suffix);
			$this -> success("活动修改成功");
		}else{
			$this -> error('活动未做修改');
		}
	}

	//活动恢复,停用
	function active_contrl(){
		$id = $_GET['id'];
		$type = $_GET['type'];
		if($type > 1) $this -> error('无效操作');
		$where = array(
			'id' => $id,
		);
		$save = array(
			'status' => $type,
		);
		$model = D('sendNum.sendNum');
		$count = $model -> table('sendnum_active') -> where("id = {$id} and status = {$type}") -> count();
		if($count > 0) $this -> error('当前页面已失效！');
		$affect = $model -> active_save($where,$save);
		if($affect){
			$status = $type == 1 ? '正常' : '停用';
			$this -> writelog("活动id:{$id} 的 status 修改为 {$status}",'sendnum.sendnum_active',$id,__ACTION__ ,"","edit");
			$this -> success("活动状态修改成功！");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Sendnum/active_list');
		}else{
			$this -> error('修改失败!');
		}
	}
	function active_num_add($add_active_id,$active_type,$model){
		ini_set('memory_limit', '756M');
		set_time_limit(0);
		$tmp_name = $_FILES['num_file']['tmp_name'];
		$file_name = $_FILES['num_file']['name'];
		$file_size = $_FILES['num_file']['size'];
		$f_arr = explode('.',$file_name);
		$file_ext = $f_arr[1];
		if($file_ext != 'csv'){
			$model -> table('sendnum_active') -> where('id ='.$add_active_id) -> delete();
			$this -> error('请上传csv格式文件');

		}
		if($file_size == 0){
			$model -> table('sendnum_active') -> where('id ='.$add_active_id) -> delete();
			$this -> error('文件没有内容');
		}
		$handel = fopen($tmp_name,"r");
		$nums_info = array();
		$num_sum = 0;
		$num_ab_sum = 0;
		while(!feof($handel)){
			$nums = array();
			$num_row = fgets($handel);
			$num_sum ++;
			$num_arr = explode(',',$num_row);
			$number = iconv('GB2312','UTF-8', trim($num_arr[0]));
			if(count($num_arr) == 1 && (strlen($number) >0 && strlen($number) <= 25)){
				//if(preg_match("/[".chr(0xa1)."-".chr(0xff)."]+/",$number) || preg_match("/[\x{4e00}-\x{9fa5}]+/u",$number)) continue;
				//file_put_contents('/tmp/lipeng.log',$number."\n",FILE_APPEND);
				if(!preg_match("/^[0-9a-zA-Z]+$/",$number)) continue;
				$nums['active_num'] = $number;
				$nums['active_id'] = $add_active_id;
				$nums['status'] = 0;
			}else continue;
			// $tabid = strlen($num_ab_sum,-1,1); //数据分表
			// $affectid = $model -> add_active_num($nums,$tabid);
			// $tabid = strlen($num_ab_sum,-1,1); //数据分表
			$cnt = $model -> table('sendnum_number') -> where(" active_num = '{$nums['active_num']}' and active_id = {$add_active_id}") -> count();
			if(!$cnt){
				$affectid = $model -> add_active_num($nums);
				if($affectid > 0) $num_ab_sum++;
			}
		//	$affectid = $model -> add_active_num($nums);
		//	if($affectid > 0) $num_ab_sum++;
		}
		unlink($tmp_name);
		return array($num_sum,$num_ab_sum);
	}
	function active_csv_dl(){
      	$filename = date('YmdHis').".csv";//文件名
      	header("Content-type:text/csv");
      	header("Content-Disposition:attachment;filename=".$filename);
      	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
      	header('Expires:0');
     	header('Pragma:public');
		if(!$_GET['activeid']){
		//echo iconv("UTF-8","GB2312","请将号码依次输入第一列")."\n";
		}elseif($_GET['activeid']){
			set_time_limit(0);
			$model = D('sendNum.sendNum');
			$active_id = $_GET['activeid'];
			$result = $model -> table('sendnum_number') -> where("active_id = {$active_id}") -> select();
			$activeinfo = $model -> table('sendnum_active') -> where('id = '.$active_id) -> select();
			$actname = 	iconv("UTF-8","GB2312",$activeinfo[0]['active_name']);
			echo iconv("UTF-8","GB2312","活动名称,号码,获取时间,uid,ip\n");
			foreach($result as $info) {
				$take_tm = $info['take_tm'] ? date('Y-m-d H:i:s',$info['take_tm']) : "";
				echo $actname.','.iconv('UTF-8','GB2312',$info['active_num']).','.$take_tm.','.$info['uid'].','.$info['ip']."\n";
			}
		}
	}
}
