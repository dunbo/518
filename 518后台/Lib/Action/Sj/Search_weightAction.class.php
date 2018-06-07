<?php
/* 软件搜索关键字权重管理 */
class Search_weightAction extends CommonAction{
	private $SwModel;
	private $map;
	private $max_related_num = 100; //关键字强关联数量上限
	function __construct() {
		parent::__construct();
		require_once "CategoryExtentAction.class.php";
		$this->map = ContentTypeModel::getCategoryTypes();
		unset($this->map['']);
		$this->map['page_ebook_hot'] = '电子书热门';
		$this->map['page_ebook_rank'] = '电子书排行榜';
		$this->SwModel = D('Sj.Search_weight'); 
	}
	function search_key_list(){
        // 搜索条件拼成个字符串
        $param = '';
        foreach ($_GET as $key => $value) {
            $param .= $param ? '&' : '';
            $param .= "{$key}={$value}";
        }
		$sk_db = M("search_key");
		$key = escape_string($_GET['srh_key']);
		if(mb_strlen($key,'UTF-8') > 50){
		  $this -> error('您要搜索的关键字不存在！！');
		}
		$key_where = $key ? " and srh_key like '%".$key."%'" : '';
		$_GET['product_id'] = isset($_GET['product_id']) ? $_GET['product_id'] : 1;	
        if($_GET['product_id']){
			$key_where .= " and pid=".$_GET['product_id'];
			$this->assign('product_id',$_GET['product_id']);			
		}
		$count = $sk_db -> where("status = 1".$key_where) -> count();
        import("@.ORG.Page");
		$p = new Page ($count, 25);
		$search_key_list = $sk_db -> where("status = 1".$key_where)->limit($p->firstRow.','.$p->listRows) ->order("update_tm desc") -> select();
        $page = $p->show();
		$this -> assign("page",$page);
		$this -> assign("srh_key",$key);
		//$this -> assign("stop_tm",date("Y-m-d 23:59:59",time()+24*3600*7));
		//$this -> assign("start_tm",date("Y-m-d 00:00:00",time()));
		$this -> assign('key_list',$search_key_list);
        $this -> assign('param', $param);
		#产品列表
        $product_model = M();
        $product_list = $product_model ->table('pu_product')->where('status = 1')->findAll();
        $this-> assign ("product_list", $product_list);			
		$this -> display('search_key_list');
	}
	//关键字列表by张辉
	function search_list(){
		ini_set('memory_limit','1024M');
                set_time_limit(0);
		$serch = D("Sj.Search");
		$year=date("Y",time());
		$mon=date("m",time());
		import("@.ORG.Page");
		$start_time = strtotime($_GET['start_tm']);
		$end_time = strtotime($_GET['stop_tm']);
		$zh_serch=$_GET['key'];
		if($start_time && $end_time ||(!empty($zh_serch))){
			if(empty($start_time)||empty($end_time)&&(!empty($zh_serch))){
				$zh_week=$serch->table('serch')->where(array("serch_key"=>$zh_serch));
				$count=count($zh_week);
				$Page=new Page($count,10);
				$zh_week=$serch->table('serch')->field('serch_key,serch_count as zong')->where(array("serch_key"=>$zh_serch))->limit($Page->firstRow.','.$Page->listRows) ->select();
			}else{
				if(($end_time-$start_time)>3600*24*184){
					$this -> error('选择区间超过六个月！！');
				}
					$start_tm = $_GET['start_tm'];
					$stop_tm = $_GET['stop_tm'];
					$biao=$this->back_biao($start_tm,$stop_tm);
					if(!empty($zh_serch)){
						$where="where serch_key = '".$zh_serch."' and serch_time >='".$start_time."' and serch_time <='".$end_time."'";
					}else{
						$where="where serch_time >='".$start_time."' and serch_time <='".$end_time."'";
					}
					$zh_table=$this->back_sql($biao,$where);
					$sql="select count(distinct serch_key) as num from (".$zh_table.") as zhang";
					$zh_week=$serch->query($sql);
					$count=$zh_week[0]['num'];
					if($count<1000){
					$count=$count;
					}else{
						$count=1000;
					}
					$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
					$Page=new Page($count,$getpage);
					$limit = " limit ".$Page->firstRow.','.$Page->listRows;
					$sql="select serch_key,sum(serch_count) as zong,serch_time from (".$zh_table.") as zhang group by serch_key order by zong desc".$limit;
					$zh_week=$serch->query($sql);
			}
		}else{
			$start_time = $this->start_time();
			$end_time = time()-86400;
			$biao=$this->biao($year,$mon);
			$where="where serch_time >='".$start_time."' and serch_time <='".$end_time."'";
			$zh_table=$this->back_bsql($biao,$where);
				$sql="select count(distinct serch_key) as num  from (".$zh_table.") as zhang";
				$zh_week=$serch->query($sql);
				$count=$zh_week[0]['num'];
				if($count<1000){
					$count=$count;
				}else{
					$count=1000;
				}
				$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
				$Page=new Page($count,$getpage);
				$limit = " limit ".$Page->firstRow.','.$Page->listRows;
				$sql="select serch_key,sum(serch_count) as zong,serch_time from (".$zh_table.") as zhang group by serch_key order by zong desc".$limit;
				$zh_week=$serch->query($sql);
				$stop_tm=date("Y-m-d H:i:s",$end_time);
				$start_tm=date("Y-m-d H:i:s",$start_time);
		}
		$page = $Page->show();
		$this -> assign("page",$page);
		$this -> assign("getpage",$getpage);
		$this -> assign("stop_tm",$stop_tm);
		$this -> assign("start_tm",$start_tm);
		$this -> assign('key',$zh_serch);
		$this -> assign('key_list',$zh_week);
		$this -> display('search_list');
	}
	//搜索单个字在某段时间内的搜索情况
	function  serch_key(){
		ini_set('memory_limit','1024M');
        set_time_limit(0);
		$serch = D("Sj.Search");
		$year=date("Y",time());
		$mon=date("m",time());
		import("@.ORG.Page");
		$start_tm=$_GET['start_tm'];
		$stop_tm=$_GET['stop_tm'];
		$start_time = strtotime($_GET['start_tm']);
		$end_time = strtotime($_GET['stop_tm']);
		$zh_serch=$_GET['key'];
		$serch_key=$_GET['serch_key'];
		//echo $serch_key;
		if(empty($start_tm)||empty($stop_tm)){
			$start_time = $this->start_time();
			$end_time = time()-86400;
			$biao=$this->biao($year,$mon);
			$where="where serch_key='".$serch_key."' and serch_time >='".$start_time."' and serch_time <='".$end_time."'";
			$zh_table=$this->back_bsql($biao,$where);
			
			$sql="select count(serch_key) as num from (".$zh_table.") as zhang";
			$zh_week=$serch->query($sql);
			$count=$zh_week[0]['num'];
			$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
			$Page=new Page($count,$getpage);
			$limit = " limit ".$Page->firstRow.','.$Page->listRows;
			$sql="select serch_key,serch_count as zong,serch_time from (".$zh_table.") as zhang order by serch_time desc".$limit;
			$zh_week=$serch->query($sql);
		}else{
			$biao=$this->back_biao($start_tm,$stop_tm);
			$where="where serch_key='".$serch_key."' and serch_time >='".$start_time."' and serch_time <='".$end_time."'";
			$zh_table=$this->back_sql($biao,$where);
			$sql="select count(serch_key) as num from (".$zh_table.") as zhang";
			$zh_week=$serch->query($sql);
			$count=$zh_week[0]['num'];
			$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
			$Page=new Page($count,$getpage);
			$limit = " limit ".$Page->firstRow.','.$Page->listRows;
			$sql="select serch_key,serch_count as zong,serch_time from (".$zh_table.") as zhang order by serch_time desc".$limit;
			$zh_week=$serch->query($sql);
		}
		$page = $Page->show();
		//print_r($page);exit;
		$this -> assign("page",$page);
		$this -> assign("getpage",$getpage);
		$this -> assign("stop_tm",$stop_tm);
		$this -> assign("start_tm",$start_tm);
		$this -> assign('key_list',$zh_week);
		$this -> assign('zh_key',1);
		$this -> assign('key',$zh_serch);
		$this -> display('search_list');
	
	}
	//返回查询数据表$biao;

	function back_biao($start_time,$end_time){
			$start_date = explode("-",$start_time);
			$end_date=explode("-",$end_time);
			if($start_date[0]!=$end_date[0]){
				for($i=(int)$start_date[1];$i<=12;$i++)
				{
					if($i<10){
						$i="0".$i;
					}
					$biao1.="serch".$start_date[0].$i.",";
				}
				for($i=(int)$end_date[1];$i>=1;$i--)
				{
					if($i<10){
						$i="0".$i;
					}
					$biao2.="serch".$end_date[0].$i.",";
				}
				$biao=$biao1.$biao2;
			}else{
				for($i=(int)$start_date[1];$i<=$end_date[1];$i++){
					if($i<10){
						$i="0".$i;
					}
					$biao.="serch".$start_date[0].$i.",";
				}
			}
			//$time = $date[2].'-'.$date[0].'-'.$date[1];
			$biao = substr($biao,0,strlen($biao)-1);
			return $biao;
		}
		
	function biao($year,$mon){
		if((int)$mon==1){
			$biao="serch".($year-1)."12,serch".$year.$mon;
		}else{
			if((int)$mon<10){
				if((int)$mon==4&&(int)$year==2012){
						$biao="serch".$year.$mon;
					}else{
						$biao="serch".$year."0".((int)$mon-1).",serch".$year.$mon;
						}
			}else{
				$biao="serch".$year.((int)$mon-1).",serch".$year.$mon;
			}
		}
		return $biao;
}

	function back_sql($biao,$where){
			$biao=explode(",",$biao);
			$count=count($biao);
			for($i=0;$i<$count;$i++){
				$zh_table.="select serch_key,serch_count,serch_time from {$biao[$i]} ".$where." union ";
			}
			$zh_table = substr($zh_table,0,strlen($zh_table)-6);
			return $zh_table;
		}
	function back_bsql($biao,$where){
			$biao=explode(",",$biao);
			$count=count($biao);
			for($i=0;$i<$count;$i++){
				$zh_table.="select serch_key,serch_count,serch_time from {$biao[$i]} ".$where." union ";
			}
			$zh_table = substr($zh_table,0,strlen($zh_table)-6);
			return $zh_table;
		}

	function  start_time(){
		//$n=date("N",time());
		$one_time=time()-(7*86400);
		$One_date=date("Y-m-d H:i:s",$one_time);
		$start_time=strtotime($One_date);
		return $start_time;
		
	}
	//关键字列表结束by 张辉
	function search_key_add(){
		$sk_db = M("search_key");
		$srh_key = trim($_POST['srh_key']);
		$is_force_related = trim($_POST['is_force_related']);
		$rank = trim($_POST['rank']);
        // 关键字统一转小写
        $srh_key = strtolower($srh_key);
		$data['srh_key'] = $srh_key;
		if(mb_strlen($data['srh_key'],'UTF-8')>50){
			$this -> error('添加的关键字不要超过50个字');
		}
		$data['is_force_related'] = $is_force_related;
		if($is_force_related==1){
			if($rank==''){
				$this->error('优先级不能为空');
			}
			if(!preg_match("/^[1-9]+(\d)*$/", $rank)){
				$this->error('优先级应为正整数');
			}
			$count = $sk_db -> where('status=1 and is_force_related=1') -> count();
			if($count >= $this->max_related_num){
				$this->error('强关联关键词已达到上限');
			}
			$data['rank'] = $rank;
			$data['is_related_tm'] = time();
		}
		$data['create_tm'] = time();
		$data['update_tm'] = $data['create_tm'];
		$data['status'] = 1;
		$data['pid'] = $_POST['pid'];
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$where = array(
			'status' => 1,
			'pid' => $_POST['pid'],
			'srh_key' => escape_string($srh_key),
		);
		$result = $sk_db -> where($where) -> select();
		//$count = count($result);
        // var_dump($data);exit;
		if(!$result){
		  $affect = $sk_db -> add($data);
		  if($affect){
		     $this->writelog("搜索关键字管理_搜索关键字列表_添加关键字{$srh_key}id:".$affect,"sj_search_key",$affect,__ACTION__ ,"","add");
		     $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_weight/search_key_list/product_id/'.$_POST['pid']);
		    $this -> success("关键字添加成功");
		  }else{
		  $this -> error("关键字添加失败");
		  }
		}else{
		  $this -> error("有效关键字已存在！！");
		}
	}
	function format_date($strtime){
		$date = explode("/",$strtime);
		$time = $date[2].'-'.$date[0].'-'.$date[1];
		return $time;
	}
	function search_key_update(){
        // 接受条件拼成个字符串并传出去
        $param = '';
        foreach ($_GET as $key => $value) {
            if ($key == 'id') continue;
            $param .= $param ? '&' : '';
            $param .= "{$key}={$value}";
        }
		$id = escape_string($_GET['id']);
		if(strlen($id) > 12){
			$this -> error('你的操作失误 请确认你的操作');
		}
		$sk_db = M("search_key");
		$info = $sk_db -> where("id =".$id)-> select();
		$this -> assign("key_info",$info[0]);
		$this -> assign("id",$id);
        $this -> assign("param", $param);
		$this -> display("search_key_update");
	}
    function search_key_update_do(){
		$id = escape_string($_POST['id']);
		$srh_key = trim($_POST['srh_key']);
		$is_force_related = trim($_POST['is_force_related']);
		$rank = trim($_POST['rank']);
        // 关键字统一转小写
        $srh_key = strtolower($srh_key);
		if(strlen($id) > 12 || mb_strlen($srch_key,'UTF-8') > 50){
			$this -> error('您的操作有误');
		}
		$sk_db = M("search_key");
		$data['is_force_related'] = $is_force_related;
		if($is_force_related==1){
			if($rank==''){
				$this->error('优先级不能为空');
			}
			if(!preg_match("/^[1-9]+(\d)*$/", $rank)){
				$this->error('优先级应为正整数');
			}
			$count = $sk_db -> where('status=1 and is_force_related=1 and id !='.$id) -> count();
			if($count >= $this->max_related_num){
				$this->error('强关联关键词已达到上限');
			}
			$data['rank'] = $rank;
			$data['is_related_tm'] = time();
		}
		$where['status']=1;
		$where['id']=$id;
		$old_sk = $sk_db->where($where)->find();
        $data['srh_key'] = $srh_key;
		$data['update_tm'] = time();
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$count = $sk_db -> where("srh_key = '".escape_string($srh_key)."' and id <> ".$id." and status = 1") -> count('id');
		if($count > 0){
		  $this -> error("关键词已存在");
		}
		$log = $this->logcheck(array('id'=>$id),'sj_search_key',$data,$sk_db);
		$affect = $sk_db -> where("id = ".$id) -> save($data);
		if($affect){
		   	$this->writelog("搜索关键字管理_搜索关键字列表_编辑了id为$id,关键词为{$old_sk['srh_key']}".$log,"sj_search_key",$id,__ACTION__ ,"","edit");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_weight/search_key_list?'.$_POST['param']);
		    $this -> success("关键字修改成功");
		}else{
		    $this -> error("关键字修改失败");
		}
	}
	function search_key_package_add(){
		$sk_db = M("search_key");
		$soft_db = M("soft");
	    $sk_pkg_db = M("search_key_package");
		$id = escape_string($_GET['id']);
		$package = escape_string($_GET['package']);
		//新增加搜索状态
		$search_status = $_GET['search_status'];
		$search_key = $_GET['package'] ? " and package like '%".$package."%'" : '';
		if($search_status==3)//未开始
		{
			$search_key.=" and start_tm > ".time()."";
		}
		else if($search_status==2)//已过期
		{
			$search_key.=" and stop_tm < ".time()."";
		}
		else//默认正在运营的
		{
			$search_key.=" and start_tm <= ".time()." and stop_tm >= ".time()."";
		}
		if(strlen($id) > 12 || strlen($_GET['package']) > 100){
			$this -> error('您的操作有误');
		}
		$sk_info = $sk_db -> where("id = ".$id) -> select();
		$current_tm = time();
		//$date_where = " and start_tm <= ".$current_tm." and  stop_tm >".$current_tm;
		$count = $sk_pkg_db -> where("kid =".$id." and  status = 1".$search_key) -> count();
		import("@.ORG.Page");
		$p = new Page($count, 25);
		$sk_pkg_list = $sk_pkg_db -> where("kid =".$id." and  status = 1".$search_key)->limit($p->firstRow.','.$p->listRows) ->order("pos asc,start_tm asc,stop_tm asc") -> select();
		  $page = $p->show ();
		  $this -> assign("page",$page);
		  $util = D("Sj.Util"); 
		 foreach($sk_pkg_list as $key => $info){
			$srh_key = $sk_db -> where("id = ".$info['kid']) -> getField("srh_key");
			if ($info['type'] != 1) {
				$softname = '';
				$softname = $soft_db -> where("package = '".$info['package']."'") -> getField("softname");
				$sk_pkg_list[$key]['softname'] = $softname;
				$this->assign('ispage', false);
			} else {
				$sk_pkg_list[$key]['package'] = $this->map[$info['package']];	
				$this->assign('ispage', true);
			}
			$sk_pkg_list[$key]['key_name'] = $srh_key;
			$typelist = $util->getHomeExtentSoftTypeList($info['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$sk_pkg_list[$key]['types'] = $v[0];
				}
			}
		}
		
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);

		$this -> assign("page",$page);
		$this -> assign("stop_tm",date("Y-m-d 23:59:59",time()+24*3600*7));
		$this -> assign("start_tm",date("Y-m-d 00:00:00",time()));
		$this -> assign("sk_info",$sk_info[0]);
		$this -> assign("search_key",$_GET['package']);
		$this -> assign("search_status",$_GET['search_status']);
		$this -> assign("sk_pkg_list",$sk_pkg_list);
		$this -> assign("srh_key",$sk_info[0]['srh_key']);
		$this->assign('category_list', $this->map);
		$this -> display("search_key_package_add");
	}
    
    // 原来的函数，改成调用统一检查函数
    function search_key_package_add_precheck($isajax = true) {
        $content_arr = array(
            0 => $_GET,
        );
        $type_info = $this->search_key_package_add_precheck_logic($content_arr);
        if ($isajax) {
            if ($type_info[0] == 1) {
                echo "ispage";	
            } else if ($type_info[0] == 0) {
                echo "ispackage";
            } else {
				echo "nodata";	
			}
        } else {
			if ($type_info[0] == 1) {
				$this->error('该关键词已有设置推荐页面，无法继续添加。如果需要添加请先删除推荐页面');
			}
		}
    }
    // 在批量导入时调用的函数，也调用统一检查函数
    function search_key_package_add_precheck_multiple($content_arr) {
        $type_info = $this->search_key_package_add_precheck_logic($content_arr);
        $hint_info = "";
        foreach ($type_info as $key => $type) {
            $k = $key + 1;
            if ($type == 1) {
                $hint_info .= "第{$k}行的关键字已有设置推荐页面，无法继续添加。如果需要添加请先删除推荐页面;";
            } else if ($type == 0 && $content_arr[$key]['type'] == 1) {
                $hint_info .= "第{$k}行的关键字存在包名，如需添加页面，请先删除这些包;";
            }
        }
        if ($hint_info != "")
            $this->ajaxReturn($hint_info, $hint_info, 1);
    }
    // 统一检查关键字存的是包还是页面
    function search_key_package_add_precheck_logic($content_arr) {
        $type_info = array();
        foreach($content_arr as $key => $record) {
			if($record['kid'])
			{
				$where = array(
                'kid' => array('EQ', $record['kid']),
                'status' => array('NEQ', 0),
				);
				if (isset($record['id']))
					$where['id'] = array('NEQ', $record['id']);
				$package = $this->SwModel->getPackage($where);
				if ($package) {
					if ($package['type'] == 1) {
						$type_info[$key] = 1;//1 页面 0包
					} else {
						$type_info[$key] = 0;
					}
				} else {
					$type_info[$key] = -1;
				}
			}
        }
        return $type_info;
    }

	function search_key_package_add_do(){
		$sk_db = M("search_key");
	    $sk_pkg_db = M("search_key_package");
		$soft_db = M("soft");

		//如果已经有有效页面存在不允许添加其他
		$this->search_key_package_add_precheck(false);
        // 将_POST或_GET传进来的参数统一转成与表里字段一样的名称
        $column_convert_arr = array(
            'kid' => 'kid',
			'srh_key' => 'srh_key',
            'type' => 'type',
            'pos' => 'pos',
            'start_tm' => 'start_tm',
            'stop_tm' => 'stop_tm',
			'co_type' => 'co_type',
			'beid' => 'beid',
        );
        $check_column_arr = array();
        foreach($column_convert_arr as $key=>$value) {
            if (array_key_exists($key, $_POST)) {
                $check_column_arr[$value] = $_POST[$key];
            }
        }
        // 兼容以前代码，作一些转化
        // 判断type的类型
        if ($_POST['res_type'] == 'package') {
            $check_column_arr['type'] = 0;
            $check_column_arr['package'] = $_POST['package'];
        } else if ($_POST['res_type'] == 'page') {
            $check_column_arr['type'] = 1;
            $check_column_arr['package'] = $_POST['page_type'];
        }
        // 时间格式转换一下
        $check_column_arr['start_tm'] = $_POST['start_tm'];
        $check_column_arr['stop_tm'] = $_POST['stop_tm'];
        foreach($check_column_arr as $key=>$value) {
            $check_column_arr[$key] = trim($value);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
        $error_msg = $this->logic_check($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->error($msg);
        }
        
        $data['kid'] = $_POST['kid'];
		$data['update_tm'] = time();
		$data['start_tm'] = strtotime($_POST['start_tm']);
		$data['stop_tm'] = strtotime($_POST['stop_tm']);
		if(isset($_POST['co_type'])){
			$data['co_type'] = $_POST['co_type'];
		}else{
			$data['co_type'] = 0;
		}	


		$data['is_show_prompt'] = $_POST['is_show_prompt'];
		if($data['is_show_prompt']==2){
			$percent=trim($_POST['percent']);
			if(!(($percent>0 && $percent<=100) && preg_match("/^\d*$/",$percent))){
				$this -> error('百分比的数字必须为大于0且小于等于100的整数');
			}
			$data['percent'] = $percent;
		}
        
		if ($_POST['res_type'] == 'package') {
			$data['package'] = $_POST['package'];
			$data['beid'] = trim($_POST['beid']);
			//相同时间内软件排序不可重复
			$data['pos'] = $_POST['pos'];
			if($data['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['stop_tm'],1);
				if($shield_error){
				    $this -> error($shield_error);
				}
			}
		} else if ($_POST['res_type'] == 'page') {
			$this->SwModel->delValidPackage('kid = '.$_POST['kid'].' and status = 1');
			$data['package'] = $_POST['page_type'];	
			$data['type'] = 1;
			$data['pos'] = -1;
		}

		$data['status'] = 1;
		if(strlen($data['package']) >= 100 || strlen($data['kid']) >= 10){
			$this -> error('您的操作有误');
		}
		$now_time = time();
		$data['weight'] = $_POST['weight'];
		
		$data['create_tm'] = time();
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		
		$affect = $sk_pkg_db -> add($data);
		//排序为1的同步到阿拉丁
		if($data['pos']==1)
		{
			$key_words=$sk_db->where(array('id' => $_POST['kid']))->find();
			$map=array(
				'package' =>$_POST['package'],
				'associate' =>";".$key_words['srh_key'].";",
				'begin' =>strtotime($_POST['start_tm']),
				'end' =>strtotime($_POST['stop_tm']),
				'create_time' =>time(),
				'update_time' =>time(),
				'stat' =>1,
				'type' => $_POST['co_type'],
				'admin_id'=>$_SESSION['admin']['admin_id'],
			);
			$result= $sk_pkg_db ->table('sj_soft_associate_hot_word')->add($map);
		}
		if($result)
		{
			$this->writelog("搜索关键字管理-单个结果运营同步到搜索阿拉丁-同步包名'".$_POST['package']."'的关联词为'".$key_words['srh_key'],"sj_soft_associate_hot_word",$result,__ACTION__ ,"","add");
		}
		if($affect){
			$this->writelog('搜索关键字管理_搜索关键字列表_单个添加关键字 软件'.$affect.'包名为'.$data['package'],"sj_search_key_package",$affect,__ACTION__ ,"","add");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_weight/search_key_package_add/id/'.$_POST['kid']);
		    $this -> success("软件添加成功");
		}else{
			$this -> error("添加失败！！");
		}
	}
	function search_key_delete(){
		$id = escape_string($_GET['id']);
		$sk_db = M("search_key");
		$sk_pkg_db = M("search_key_package");
		if(strlen($id) > 12){
			$this -> error('您的操作有误');
		}
		$data['status'] = 0;
		$data['update_tm'] = time();
		$affect = $sk_db -> where("id = ".$id) -> save($data);
		if($affect){
			$count = $sk_pkg_db -> where('kid = '.$id) -> count();
			if($count > 0){
				$affected = $sk_pkg_db -> where("kid =".$id) -> save($data);
				if($affected){
					$this->writelog('搜索关键字管理_搜索关键字列表_删除关键字id为'.$id,"sj_search_key",$id,__ACTION__ ,"","del");
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_weight/search_key_list');
				$this -> success("关键字及包删除成功");
				}else{
				$data['status'] = 1;
				$data['update_tm'] = time();
				$affect = $sk_db -> where("id =".$id) -> save($data);
				$this -> error("关键字删除失败1");
				}
			}
			$this->writelog('搜索关键字管理_搜索关键字列表_删除关键字id为'.$id,"sj_search_key",$id,__ACTION__ ,"","del");
			//$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_weight/search_key_list');
			$this -> success("关键字删除成功");
		}else{
			$this -> error("关键字删除失败2");
		}
	}
	function search_key_package_delete(){
	 $id = escape_string($_GET['id']);
	 $sk_pkg_db = M("search_key_package");
	 if(strlen($id) > 10){
		$this -> error('您的操作有误');
	 }
	 $data['status'] = 0;
	 $data['update_tm'] = time();
	 $result=$sk_pkg_db -> where(array("id"=>$id,"status"=>1)) -> find();
	 $affect = $sk_pkg_db -> where("id = ".$id) -> save($data);
	
	 if($affect){
			$this->writelog('搜索关键字管理_搜索关键字列表_删除关键字软件 id为'.$id.'包名为'.$result['package'],"sj_search_key_package",$id,__ACTION__ ,"","del");
		    $this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_weight/search_key_package_add/id/'.$_GET['kid']);
		    $this -> success("软件已经删除");
	 }else{
	   $this -> error("软件删除失败");
	 }
	}
	function search_key_package_list(){
	  $sk_pkg_db = M("search_key_package");
	  $sk_db = M("search_key");
	  $soft_db = M("soft");
	  $current_tm = time();
	  if(isset($_GET['date_status'])){
	    switch($_GET['date_status']){
		 case 0: $date_where = " and stop_tm >=".$current_tm; break;
		 case 1: $date_where = " and start_tm <= ".$current_tm." and  stop_tm >".$current_tm; break;
		 case 2: $date_where = " and start_tm > ".$current_tm; break;
		}
	  }else{
	   $date_where = " and start_tm <= ".$current_tm." and  stop_tm >".$current_tm;
	  }
	  $key_where = $_GET['package'] ? " and  package like '%".escape_string($_GET['package'])."%'" : "";
	  $count = $sk_pkg_db -> where("status =1".$key_where.$date_where) -> count();
	  import("@.ORG.Page");
	  $p = new Page ($count, 25);
	  $sk_pkg_list = $sk_pkg_db -> where("status = 1".$key_where.$date_where)->limit($p->firstRow.','.$p->listRows) ->order("weight,update_tm desc") -> select();
	  $page = $p->show ();
	  $this -> assign("page",$page);
	 foreach($sk_pkg_list as $key => $info){
		$srh_key = $sk_db -> where("id = ".$info['kid']) -> getField("srh_key");
		$softname = $sk_db -> where("package = '".$info['package']."'") -> getField("softname");
		$sk_pkg_list[$key]['key_name'] = $srh_key;
		$sk_pkg_list[$key]['softname'] = $softname;
	 }
	  $this -> assign("sk_pkg_list",$sk_pkg_list);
	  $this -> display("search_key_package_list");
	}
	
	
	function search_key_package_update(){
		$kid = escape_string($_GET['kid']);
		$id_str = $_GET['id'];
		$arr = explode("&",$id_str);
		$id = escape_string($arr[0]);
		$sk_pkg_db = M("search_key_package");
		$sk_db = M("search_key");
		$info = $sk_pkg_db -> where("id=".$id) -> select();
		$kid=$info[0]['kid'];
		$srh_key = $sk_db -> where("id =".$kid) -> getField("srh_key");

		if ($info[0]['type'] == 1) { 
		  $this->assign('ispage', true);
		} else {
		  $this->assign('ispage', false);
		}
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($info[0]['co_type']);
		$this->assign('typelist',$typelist);

		$this->assign('category_list', $this->map);
		$this -> assign("srh_key",$srh_key);
		$this -> assign("pkginfo",$info[0]);
		$this -> display("search_key_package_update");
	}

	function search_key_package_update_do(){
		$sk_pkg_db = M("search_key_package");
		$sk_db = M("search_key");
        
        // 将_POST或_GET传进来的参数统一转成与表里字段一样的名称
        $column_convert_arr = array(
            'id' => 'id',
            'kid' => 'kid',
			'srh_key' => 'srh_key',
            'type' => 'type',
            'pos' => 'pos',
            'start_tm' => 'start_tm',
            'stop_tm' => 'stop_tm',
			'co_type' =>'co_type',
			'beid'  => 'beid',
        );
        $check_column_arr = array();
        foreach($column_convert_arr as $key=>$value) {
            if (array_key_exists($key, $_POST)) {
                $check_column_arr[$value] = $_POST[$key];
            }
        }
        // 兼容以前代码，作一些转化
        // 判断type的类型
        if ($_POST['res_type'] == 'package') {
            $check_column_arr['type'] = 0;
            $check_column_arr['package'] = $_POST['package'];
        } else if ($_POST['res_type'] == 'page') {
            $check_column_arr['type'] = 1;
            $check_column_arr['package'] = $_POST['page_type'];
        }
        // 时间格式转换一下
        $check_column_arr['start_tm'] = $_POST['start_tm'];
        $check_column_arr['stop_tm'] = $_POST['stop_tm'];
        foreach($check_column_arr as $key=>$value) {
            $check_column_arr[$key] = trim($value);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
        $error_msg = $this->logic_check($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->error($msg);
        }
        
		$kid = escape_string($_POST['kid']);
		$id = escape_string($_POST['id']);
		$zh_where['status']=1;
		$zh_where['id']=$id;
		$old_skp = $sk_pkg_db->where($zh_where)->find();
		$data['start_tm'] = strtotime($_POST['start_tm']);
		$data['stop_tm'] = strtotime($_POST['stop_tm']);
		$data['package'] =  $_POST['package'];
		if(isset($_POST['co_type'])){
				$data['co_type'] = $_POST['co_type'];
			}else{
				$data['co_type'] = 0;
			}
		

		$data['is_show_prompt'] = $_POST['is_show_prompt'];
		if($data['is_show_prompt']==2){
			$percent=trim($_POST['percent']);
			if(!(($percent>0 && $percent<=100) && preg_match("/^\d*$/",$percent))){
				$this -> error('百分比的数字必须为大于0且小于等于100的整数');
			}
			$data['percent'] = $percent;
		}
		if ($_POST['res_type'] == 'package') {
			$data['package'] = $_POST['package'];
			$data['beid'] = trim($_POST['beid']);
			$data['pos'] = $_POST['pos'];
			$data['type'] = 0;
			if($data['package']){
				//屏蔽软件上排期时报警需求 新增  yuesai
				$AdSearch = D("Sj.AdSearch");
				$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['stop_tm'],1);
				if($shield_error){
				    $this -> error($shield_error);
				}
			}
		} else if ($_POST['res_type'] == 'page') {
			$this->SwModel->delValidPackage('kid = '.$_POST['kid'].' and status = 1 and id <> '.$_POST['id']);
			$data['package'] = $_POST['page_type'];	
			$data['type'] = 1;
			$data['pos'] = -1;
		}

		$data['weight'] = $_POST['weight'];
		$data['update_tm'] = time();
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		if($_POST['res_type'] == 'package' && $data['pos'] <= "0"){
		$this -> error("排序必须大于0");
		}
		
		//$log_result = $this -> logcheck(array('id' => $id),'sj_search_key_package',$data,$sk_pkg_db);
		$affect = $sk_pkg_db -> where("kid =".$kid." and id=".$id) -> save($data);
		if($affect){
		$configModel = D('Sj.Config');
		$column_desc = $configModel->getSearchPackageColumnDesc();

		if ($old_skp['type'] == 1) {
			$old_skp['package'] = $this->map[$old_skp['package']];
		}

		if ($data['type'] == 1) {
			$data['package'] = $this->map[$data['package']];
		}
		
		$msg = "编辑了id为[{$id}],包名为[{$old_skp['package']}]\n";
		foreach ($data as $key => $val) {
			if (isset($column_desc[$key]) && $data[$key] != $old_skp[$key]) {
				$desc = $column_desc[$key];
				$msg .= "将{$desc} 从'{$old_skp[$key]}'修改成 '{$data[$key]}'\n";	
			}
		}
		$this->writelog($msg,"sj_search_key_package",$id,__ACTION__ ,"","edit");
		//$this->writelog("搜索关键字管理_搜索关键字列表_已编辑id为{$id}的软件".$log_result,"sj_search_key_package",$id,__ACTION__ ,"","edit");
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Search_weight/search_key_package_add/id/'.$kid);
		$this -> success("软件已经修改");
		}else{
		$this -> error("软件修改失败");
		}

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
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            // 'kid' => '关键字id',
            'srh_key'     =>  '关键字',
            'package'  =>   '包名/页面',
            'type' => '类型',
            'pos'  =>   '排序',
            'start_tm'  =>   '开始时间(yyyy/MM/dd)',
            'stop_tm'  =>   '结束时间(yyyy/MM/dd)',
			'co_type' => '合作形式',
			'beid' =>'行为id',
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
        // 业务逻辑：检查列填写是否为预期内容（固定的内容），如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 有些字段输入为空时是合法的，有些字段输入为空不允许，当为空不允许时，将其值设为false以作区别
        $expected_words['type'] = array("" => 0, "包名" => 0, "页面" => 1);
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
                        $this->append_error_msg($error_msg, $key, 1, "【{$column}】列内容填写有误;");
                        $content_arr[$key][$r_key] = false;
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 检查是否为false，如果不是，则表示可以为空，替换成相应的数字，否则不处理，即还是为空，在logic_check()里会进行非空值判断
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_tm' || $r_key == 'stop_tm') {
                    if ($r_key == 'start_tm') {
                        $type = 0;
                        $hint = '开始';
                    } else {
                        $type = 1;
                        $hint = '结束';
                    }
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
        // 将页面由中文转成后台存储的英文对应值
        unset($expected_words);
        $expected_words['package'] = array("" => false);
        foreach($this->map as $key=>$value) {
            $expected_words['package'][$value] = $key;
        }
        foreach($content_arr as $key=>$record) {
            if ($record['type'] != 1)
                continue;
            foreach($record as $r_key=>$r_value) {
                if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "页面【{$r_value}】不存在;");
                        $content_arr[$key][$r_key] = false;
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 检查是否为false，如果不是，则表示可以为空，替换成相应的数字，否则不处理，即还是为空，在logic_check()里会进行非空值判断
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
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
        $M_keyword_table = 'search_key';
        $M_keyword_soft_table = 'search_key_package';
        // 获得三个表的model
        $keyword_model = M($M_keyword_table);
        $keyword_soft_model = M($M_keyword_soft_table);
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 关键字ID
            if ($record['type'] !== false) {
                if (isset($record['type']) && $record['type'] !== "") {
                    if ($record['package'] !== false) {
                        if (isset($record['package']) && $record['package'] != "") {
                            if ($record['type'] == 0) {
                                // 检查包名是否在sj_soft表里
                                $where = array(
                                    'package' => $record['package'],
                                    'status' => 1,
                                    'hide' => array('EQ', 1),
                                );
                                $find = $soft_model->where($where)->find();
                                if (!$find) {
                                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                                }
                                // 检查排序是否为数字
                                if (isset($record['pos']) && $record['pos'] != "") {
                                    if (!preg_match("/^\d+$/", $record['pos'])) {
                                        $this->append_error_msg($error_msg, $key, 1, "排序应为整数;");
                                    }
                                } else {
                                    $this->append_error_msg($error_msg, $key, 1, "排序值不能为空;");
                                }
                            } 
                        } else {
                            $this->append_error_msg($error_msg, $key, 1, "包名/页面不能为空;");
                        }
                    }
                } else {
                    $this->append_error_msg($error_msg, $key, 1, "类型不能为空;");
                }
            }
            // 检查开始时间
            if (isset($record['start_tm']) && $record['start_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_tm']);
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
            if (isset($record['stop_tm']) && $record['stop_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['stop_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['stop_tm']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
        }
        $model = M();
        // 检查关键字在阿拉丁上是否已绑定包名
        foreach ($content_arr as $key => $record) {
            if (!isset($record['srh_key']) || !isset($record['package']) || !isset($record['bk_start_time']) || !isset($record['bk_end_time']) || !isset($record['type']))
                continue;
            // 根据kid查找关键字名，然后对阿拉丁表里查找此时间段有没有绑定这个关键字
            $where = array(
                'srh_key' => array('eq', $record['srh_key']),
                'status' => array('NEQ', 0)
            );
            $find = $model->table('sj_search_key')->where($where)->find();
            // echo $model->getLastSql();
            // var_dump($find);die;
            if (!$find)
                continue;
            $srh_key = $find['srh_key'];
            $where = array(
                'associate' => array('like', "%;{$srh_key};%"),
                'begin' => array('elt', $record['bk_end_time']),
                'end' => array('egt', $record['bk_start_time']),
                'stat' => 1,
            );
            $find = $model->table('sj_soft_associate_hot_word')->where($where)->find();
            //  echo $model->getLastSql();
            // var_dump($find);die;
            if (!$find)
                continue;
            $ald_package = $find['package'];
            // 找到此关键字有配置阿拉丁，判断当前要添加的内容是页面还是包名
            if ($record['type'] == 1) {
                $this->append_error_msg($error_msg, $key, 1, "此关键字已设置了阿拉丁推荐，不可以添加页面;");
                continue;
            }
            if (!isset($record['pos']))
                continue;
            if ($record['pos'] == 1) {
                if ($ald_package == $record['package'])
                    continue;
                $this->append_error_msg($error_msg, $key, 1, "此关键字已设置了阿拉丁推荐;");
            } else {
                // 不是第一位，判断会不会设置成和阿拉丁一样
                if ($ald_package != $record['package'])
                    continue;
                $this->append_error_msg($error_msg, $key, 1, "{$record['package']}软件已在此关键字设置了阿拉丁推荐，无法在相同关键字推荐相同软件");
            }
        }
        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
		$c_rank = $db_rank = $last_rank = array();
        foreach($content_arr as $key1=>$record1) 
		{

            // 如果1填写时间的不完善，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                $k1 = $key1 + 1;
                $k2 = $key2 + 1;
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 关键字不一样，不比较
                // if ($record1['kid'] != $record2['kid'])
                    // continue;
				if($record1['srh_key'] != $record2['srh_key'])
					continue;
                // 如果类型是页面，需要检查批量导入中该关键字有没有其他的包或页面
                if ($record1['type'] == 1) {
                    $this->append_error_msg($error_msg, $key1, 1, "当前行已对关键字添加页面，请不要在第{$k2}行继续添加包名或页面;");
                    $this->append_error_msg($error_msg, $key2, 1, "第{$k1}行已对关键字添加页面，请不要在当前行继续添加包名或页面;");
                }
                // 如果2填写时间的不完善，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                // 包名相同
                if ($record1['package'] == $record2['package']) {
                    if ($record1['type'] == 0 && $record2['type'] == 0) {
                        // 时间是否交叉
                        if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                            $k1 = $key1 + 1; $k2 = $key2 + 1;
                            $this->append_error_msg($error_msg, $key1, 1, "同一包名下，投放区间与第{$k2}行有重叠;");
                            $this->append_error_msg($error_msg, $key2, 1, "同一包名下，投放区间与第{$k1}行有重叠;");
                        }
                    }
                }
                // 排序相同
                if ($record1['pos'] == $record2['pos']) {
                    if ($record1['type'] == 0 && $record2['type'] == 0) {
                        // 时间是否交叉
                        if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                            $k1 = $key1 + 1; $k2 = $key2 + 1;
                            $this->append_error_msg($error_msg, $key1, 1, "同一排序下，投放区间与第{$k2}行有重叠;");
                            $this->append_error_msg($error_msg, $key2, 1, "同一排序下，投放区间与第{$k1}行有重叠;");
                        }
                    }
                }
            }
			$c_rank[$record1['srh_key']][] = $record1['pos'];
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
			//if(!$record['kid'])
			//	continue; //如果没有关键词id说明是新增加的关键词  数据库中肯定没有有关软件 直接不用比较
            //根据srh_key获取srh_key的id
			$srh_key_result=$model->table('sj_search_key')->where(array('srh_key' => $record['srh_key'],'status'=>1))->find();
			if(!$srh_key_result['id'])
				continue; //关键词没有ID说明是新增加的词，数据库中肯定没有不用比较
			 $where = array(
                'kid' => array('EQ', $srh_key_result['id']),
                'status' => array('NEQ', 0),
                'stop_tm' => array('EGT', time())
            );
	    	// $where = array(
      //           'kid' => array('EQ', $record['kid']),
      //           'status' => array('NEQ', 0),
      //       );
            if (isset($record['id']))
                $where['id'] = array('NEQ', $record['id']);
            $db_records = $keyword_soft_model->where($where)->select();
            // var_dump($db_records);die;
            if ($record['type'] !== false && $record['type'] == 0) {
                // 如果填写的是包，需要判断后台里该关键字填写的是什么，如果是页面，报错
                if (!$db_records)
                    continue;
                if ($db_records[0]['type'] == 1) {
                    $this->append_error_msg($error_msg, $key, 1, "后台中该关键字已添加页面，不能再添加包;");
                } else {
                    // 检查时间、位置冲突
                    // 如果填写时间的不完善，则不比较
                    if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                        continue;
			foreach($db_records as $db_record){
				if($db_record['stop_tm']>time())
				$db_rank[$record['srh_key']][] = $db_record['pos'];
			}
			if(count($last_rank[$record['srh_key']])==0){
				$last_rank[$record['srh_key']] = array_unique(array_merge($db_rank[$record['srh_key']],$c_rank[$record['srh_key']]));
			}
			if(isset($rank[$record['srh_key']])) $last_rank[$record['srh_key']][] = $rank[$record['srh_key']];
			foreach($db_records as $db_record)
			{
                        if ($record['package'] == $db_record['package']) {
                            // 将开始时间和结束时间转成时间戳
                            $start1 = $record['bk_start_time']; $end1 = $record['bk_end_time'];
                            $start2 = $db_record['start_tm']; $end2 = $db_record['stop_tm'];
                            if ($start1 <= $end2 && $start2 <= $end1) {
                                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                                $end_at_str = date('Y-m-d H:i:s',$db_record['stop_tm']);
                                $status_paused_hint = "";
                                if ($db_record['status'] == 2) {
                                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                                }
                                $this->append_error_msg($error_msg, $key, 1, "同一包名下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
                            }
                        }
                        if ($record['pos'] == $db_record['pos']) {
                            // 将开始时间和结束时间转成时间戳
                            $start1 = $record['bk_start_time']; $end1 = $record['bk_end_time'];
                            $start2 = $db_record['start_tm']; $end2 = $db_record['stop_tm'];
                            if ($start1 <= $end2 && $start2 <= $end1) {
                                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                                $end_at_str = date('Y-m-d H:i:s',$db_record['stop_tm']);
                                $status_paused_hint = "";
                                if ($db_record['status'] == 2) {
                                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                                }
								$rank[$record['srh_key']]= get_rank($last_rank[$record['srh_key']]);
                                $this->append_error_msg($error_msg, $key, 1, "同一位置下，投放时间与后台id为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;rank={$rank[$record['srh_key']]}");
                            }
                        }
                    }
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
    
    function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        $this->search_key_package_add_precheck_multiple($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_tm','stop_tm','',1);
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
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "sousuoguanjianzi_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('搜索关键字列表批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }
    
    // 批量导入访问的页面节点
    function import_softs() {
        if ($_GET['down_moban']) {
            $this->down_moban();
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
            $error_msg = $this->import_array_convert_and_check($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
				//针对rank值处理
				preg_match('/rank=([\d]+)/',$value['msg'],$matches);
				if($matches){
					$value['rank'] = $matches[1];
					$value['msg'] = preg_replace('/rank=([\d]+)/',' ',$value['msg']);
				}
				$error_msg[$key] = $value;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_search_key_package_importing';
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
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("搜素结果运营：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $result_arr = array();
        $model = M('search_key_package');
        $AdSearch = D("Sj.AdSearch");
        $arr_shields=array();
        foreach($content_arr as $key => $record) {
            // 根据条件忽视或设置以下值
            if ($record['type'] == 1) {
                $record['pos'] = -1;
            }
            $map = array();
            // 设置默认值
			$map['status'] = 1;
            $map['create_tm'] = time();
			$map['update_tm'] = time();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 赋值，以下为必填的值
			// if($record['kid'])
			// {
			// 	$map['kid'] = $record['kid'];
			// }
			// else
			// {
				//如果没传来kid说明是新增加的 添加关键字 添加软件
				//走到这里 说明之前的判断都已经判断过了  可以添加
				$srh_where =array(
					'srh_key' => $record['srh_key'],
					'status' =>1,
				);
				$srh_result = $this->SwModel ->getSrh_key($srh_where);
				if(!$srh_result)
				{
					$id = $this->SwModel ->add_srh($record['srh_key']);
					$this->writelog("搜索关键字管理_搜索关键字列表_批量添加关键字【{$record['srh_key']}】id:【".$id,"】sj_search_key","sj_search_key",$id,__ACTION__ ,"","add");
					$map['kid'] = $id;
				}
				else
				{
					$map['kid'] = $srh_result[0]['id'];
				}
			// }
			$map['package'] = $record['package'];
			$map['beid'] = trim($record['beid']);
			$map['pos'] = $record['pos'];
			$map['start_tm'] = strtotime($record['start_tm']);
			$map['stop_tm'] = strtotime($record['stop_tm']);
            $map['type'] = $record['type'];
			$map['co_type'] = isset($record['co_type']) ? $record['co_type'] : 0;
            $data_error=$AdSearch->pub_check_soft_filter($map['package']);
            if($data_error && $data_error['code']==1){
            	$result_arr[$key]=array('flag'=>0,'msg'=>$data_error['message'],'package'=>$map['package']);
            	$arr_shields[]=$record;
            	continue;
            }
			//排序为1的同步到阿拉丁
			if($map['pos']==1)
			{
				$map_hot=array(
					'package' =>$map['package'],
					'associate' =>";".$record['srh_key'].";",
					'begin' =>$map['start_tm'],
					'end' =>$map['stop_tm'],
					'create_time' =>time(),
					'update_time' =>time(),
					'stat' =>1,
					'type' =>$map['co_type'],
					'admin_id'=>$_SESSION['admin']['admin_id'],
				);
				$result= $model ->table('sj_soft_associate_hot_word')->add($map_hot);
			}
			if($result)
			{
				$this->writelog("搜索关键字管理-从运营批量同步到搜索阿拉丁-同步包名'".$map['package']."'的关联词为'".$record['srh_key'],"sj_soft_associate_hot_word",$result,__ACTION__ ,"","add");
			}
		
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog('搜索关键字管理_搜索关键字列表_批量添加关键字内容'.$record['srh_key'].' 软件'.$id.'包名为'.$map['package'],"sj_search_key_package",$id,__ACTION__ ,"","add");
                $result_arr[$key]['flag'] = 1;
                $result_arr[$key]['msg'] = "添加成功";
			}
			 // else {
                // 未知原因添加失败
                // $result_arr[$key]['flag'] = 0;
                // $result_arr[$key]['msg'] = "添加失败";
			// }
        }
        if(count($arr_shields) && $file_data=$AdSearch->generate_ignore_file($arr_shields,'sj_search_key_package')){
        	$result_arr['table_name']='sj_search_key_package';
        	$result_arr['filename']=$file_data['filename'];
        }
        return $result_arr;
    }
	//批量修改结束时间
	function save_endtm(){
		if($_POST){
			$model = M('search_key_package');	
			$id_arr  = explode(",",$_POST['ids']);
			$end_tm = strtotime($_POST['end_tm']);
			if(!$end_tm){
				$array = array('code' => 0,'msg'=>'请填写结束时间');
				exit(json_encode($array));	
			}
			$error_msg = '';
			$success_num = 0;
			$error_num = 0;
			$type = 0;
			foreach($id_arr as $v){
				$where = array('id'=>$v);
				$list = $model->where($where)->find();
				$type = $list['type'];
				$page = $list['type'] == 0 ? '包名':'页面';
				if(!$list){
					$error_msg .= "后台id为【".$v."】,无这条数据";
					$error_num++;
					continue;
				}
				if($end_tm < $list['start_tm']){
					$error_msg .= "后台id为【".$v."】,".$page."为【".$list['package']."】,结束时间不可早于开始时间";
					$error_num++;
					continue;
				}
				$check_column_arr = array(
					'id' => $v,
					'kid' => $list['kid'],
					'srh_key' => $_POST['srh_key'],
					'pos' => $list['pos'],
					'start_tm' => date("Y-m-d H:i:s",$list['start_tm']),
					'stop_tm' => $_POST['end_tm'],
					'co_type' => $list['co_type'],
					'beid' => $list['beid'],
					'type' => $list['type'],
					'package' => $list['package'],
				);				
				// 调用通用的检查函数
				$content_arr = array();
				$content_arr[0] = $check_column_arr;
				$error = $this->logic_check($content_arr);
				$qualified_flag = true;
				foreach($error as $key=>$value){
					if ($value['flag'] == 1)
						$qualified_flag = false;
				}
				if (!$qualified_flag){
					$error_msg .= $error[0]['msg'];
					$error_num++;
					continue;
				}				
				$map = array('stop_tm' => $end_tm,'update_tm'=>time());
				$log = $this -> logcheck($where,'sj_search_key_package',$map,$model);	
				$res = $model->where($where)->save($map);
				//echo $model->getlastsql();exit;
				if($res){
					$success_num++;
					$this -> writelog("【市场搜索管理-搜索结果运营-批量修改结束时间】id为{$v}".$log,'sj_search_key_package',$v,__ACTION__ ,'','edit');
				}else{
					$error_msg .= "后台id为【".$v."】,".$page."为【".$list['package']."】,修改失败";
					$error_num++;
					continue;
				}
			}
			if($type == 0){
				$str = "包名";
			}else{
				$str = "页面";
			}
			if($error_msg != ''){
				$success_msg = "成功修改".$str."：".$success_num."个\n修改失败：".$error_num."\n";
				$array = array('code' => 0,'msg'=>$success_msg.$error_msg,'success_num'=>$success_num);
				exit(json_encode($array));	
			}
			$array = array('code' => 1,'msg'=>"成功修改".$str.":".$success_num."个");
			exit(json_encode($array));	
		}else{
			$this->assign('srh_key', $_GET['srh_key']);
			$this->assign('num', $_GET['num']);
			$this->assign('ids', $_GET['ids']);
			$this->display('Sj:Search_tips:save_endtm');
		}		
	}    
    
}
