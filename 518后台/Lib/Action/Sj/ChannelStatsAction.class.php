<?php
class ChannelStatsAction extends CommonAction {
	//统计管理_渠道用户激活量统计显示页
	function channel_activation(){
		import("@.ORG.Page");
		$Model = new Model();
		$channel_table = 'sj_channel';
		$activaDB = D('Sj.ChannelStats');
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
		#$category_list =  $this->category_config();
		$in_cid_array = array();
		$in_cid = array();
		$not_in_string = '';
		$in_string = '';
		//new
		$b_where = "b.cid >0";
		if (!empty($_REQUEST['cid'])) {
			// XXX: 渠道检查形同虚设
			if(is_array($_REQUEST['cid'])){
				$zh_data['cid']=implode(",",$_REQUEST['cid']);
			}else{
				$zh_data['cid']=$_REQUEST['cid'];
				$_REQUEST['cid']=explode(",",$zh_data['cid']);
			}
			$this->assign("zh_cid_arra",$zh_data['cid']);
			$zh_cids=array();
			foreach($_REQUEST['cid'] as $m=>$n){
				if($n!=0){
					$zh_cids[$m]['cid']=$n;
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$Model->table($channel_table)->where($c_where)->getfield("chname");
					$zh_cids[$m]['chname']=$zh_chname;
				}
			}
			$this->assign("channel_selected",$zh_cids);
			$not_in_string = 'a.cid IN('. implode(',',$_REQUEST['cid']).')';
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type= CHANNEL_SHOW_CONTROL;
			$zh_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
			if(!empty($zh_res) && ($zh_res['filter_type'])==1){
				$res = $Model->table($channel_table)->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}
			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
			$not_in_string = empty($in_cid_array) ? '': ' a.cid IN('. implode(',', $in_cid_array). ')';
		}
		$where = empty($not_in_string) ? '' : " WHERE {$not_in_string} ";
		$sum_user_where = $where;
		$loop_where = '';

		if (array_key_exists('fromdate', $_REQUEST)&&array_key_exists('todate', $_REQUEST)){
			$from_time = strtotime( $_REQUEST['fromdate']);
			$to_time = strtotime($_REQUEST['todate']. ' 23:59:59');

			$from_value = date("Y-m-d",$from_time);
			$to_value = date("Y-m-d",$to_time);
			$zh_data['fromdate']=$from_value;
			$zh_data['todate']=$to_value;
			//new
			$b_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";

			$loop_where .= " AND submit_tm >={$from_time} AND submit_tm <{$to_time}";

			$this->assign('start',$from_value);
			$this->assign('end',$to_value);
			}else{
				$to_value=date("Y-m-d",time());
				$from_value=date("Y-m-d",strtotime("-1 week"));
				$from_time = strtotime("-1 week");
				$to_time = time();
				$this->assign('start','');
				$this->assign('end','');
			}

		$where .= empty($where) ? ' where a.cid IN("") and a.status=1 ' : ' and a.status=1 ';
		//new
		$sql_1 = "select cid from {$channel_table} a ".$where;
		$resinfo = $Model -> query($sql_1);
		$cid_1 = array();
		foreach($resinfo as $info){
			$cid_1[] = $info['cid'];
		}
		$cid_str = 'b.cid in ('.implode(',',$cid_1).')';
		$sql_2 = "select count(DISTINCT b.cid) as counts from activation_state b where ".$cid_str.' and '.$b_where;

		$res = $activaDB->query($sql_2);
		$count = $res[0]['counts'];
		$param = http_build_query($_GET);
		foreach($zh_data as $k=>$val){
			$param .= "&".$k."=".urlencode($val);
		}
		$Page = new Page($count, 500, $param);
		$Page->callback = 'return get_params();';
		$sql_1 = 'select b.cid, sum(b.activation_num) as num from activation_state b  where '.$cid_str.' and '.$b_where.' group by b.cid order by num desc limit '.$Page->firstRow.','.$Page->listRows;
		$info=$activaDB->query($sql_1);
		//echo $activaDB->getlastsql();
		$l_info = array();
		$category_result = array();
		foreach($info as $k => $v){
			$info_cid = $v['cid'];
			$info_sql = "select a.category_id,a.cid,a.chname,a.co_group from {$channel_table} a where a.cid = ".$info_cid." limit 1";
			$channel_info = $Model -> query($info_sql);
			$category_id = $channel_info[0]['category_id'];
			if (!isset($category_result[$category_id])) $category_result[$category_id] = $category_list[$category_id];
			$v['category_id'] = $category_id;
			$v['chname'] = $channel_info[0]['chname'];
			$v['co_group'] = $category_list[$category_id]['name'];
			$category_result[$category_id]['result'][] = $v;

			$cid = $v['cid'];
			$l_info[$cid] = array();
			$sql = "SELECT cid,sum(activation_num) as num,sum(internal_num) as internal_num,sum(adjust_num) as adjust_num FROM `activation_state` where cid={$cid} {$loop_where}";
			$t=$activaDB->query($sql);
			$t = array_pop($t);
			$l_info[$cid]['num'] = $t['num'];
			$l_info[$cid]['internal_num'] = $t['internal_num'];
			$l_info[$cid]['adjust_num'] = $t['adjust_num'];
		}

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_COEFFICIENT_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}' AND filter_type = 2";
		$res = $Model->query($sql);
		if (empty($res)) {
			$this->assign('show_coefficient',false);
		} else {
			$this->assign('show_coefficient',true);
		}


		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_TOTAL_FILTER_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
		$res = $Model->query($sql);
		$sum = false;
		if (empty($res)) {
			$sql = "select sum(b.activation_num) as sum from activation_state b where ".$cid_str.' and '.$b_where;
			$r=$activaDB->query($sql);
			$sum = $r[0]['sum'];
			$this->assign('showsum',true);
		} else {
			$this->assign('showsum',false);
		}

		$channel_arr = $_REQUEST['cid'];
		foreach($channel_arr as $key => $val){
			$channel_str_go .= $val.',';
		}
		$this->writelog('运营合作_渠道管理：查看了渠道用户激活统计显示页信息', '','',__ACTION__ ,'','view');
		$channel_str = substr($channel_str_go,0,-1);
		$start_tm = $_REQUEST['fromdate'];
		$end_tm = $_REQUEST['todate'];
		$page = $Page->show();
		$this -> assign('channel_str',$channel_str);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('num',$state_num);
		$this->assign('sum',$sum);
		$this->assign('info',$info);
		$this->assign('category_list',$category_result);
		$this->assign('l_info',$l_info);
		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		$this->assign('page',$page);
		$this->display();
	}

	//统计管理_渠道激活量统计_导出列表
	function derive_data_new($from = ''){
		ini_set('memory_limit','1024M');
		set_time_limit (600);
		$Model = new Model();
		$channel_table = 'sj_channel';
		$activaDB = D('Sj.ChannelStats');
		$channel_category = D('Sj.ChannelCategory');
		//$activaDB -> getConnection();
		$category_list = $channel_category->getCategory();
		#$category_list = $this->category_config();
		$start = strtotime($_REQUEST['start_tm']);
		$start_to = date('Ymd 00:00:00',$start);
		$end = strtotime($_REQUEST['end_tm']);
		$end_to = date('Ymd 23:59:59',$end);
		$start_tm = strtotime($start_to);
		$end_tm = strtotime($end_to);
		$channel_arr = explode(',',$_REQUEST['channel_all']);
		if(empty($start_tm)){
			$end_tm = time();
			$start_tm = time() - 7 * 86400;
		}
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;

		if($channel_arr[0] == ''){
			$ch_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$ch_res = $Model->table('sj_admin_filter')->where($ch_map)->find();

			if(!empty($ch_res) && ($ch_res['filter_type'])==1){
				$res = $Model->table($channel_table)->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}

			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
		}else{
			$in_cid_array = $channel_arr;
		}
		if($from == 1){
			$file = 'adjust_num';
		}else{
			$file = 'activation_num';
		}
		foreach($in_cid_array as $key => $val){
			$sql = "select sum({$file}) as counts from activation_state where cid = $val and submit_tm >= $start_tm and submit_tm <= $end_tm";
			$counts_go = $activaDB -> query($sql);

			if($counts_go[0]['counts'] == null){
				$counts_go[0]['counts'] = 0;
			}
			$counts_total[$val] = $counts_go[0]['counts'];
		}
		asort($counts_total);
		$counts_total = array_reverse($counts_total,true);
		foreach($counts_total as $key => $val){
			$channel[] = $key;
		};

		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$result_id = $Model -> table($channel_table) -> where($where) -> select();
			$category_id = $result_id[0]['category_id'];
			$category_name[] = $category_list[$category_id]['name'];
			$category_arr[] = $category_id;
		}

		foreach($category_name as $Key => $val){
			$category .= $val.',';
		}

		$errand = ($end_tm - $start_tm)/86400;

		for($i=0;$i<=$errand;$i++){
			$time[$i] = date('Y-m-d',$start_tm + $i*86400);
		}

		foreach($time as $t => $m){
			$result_counts_all = array();
			$start_go = strtotime($m.'00:00:00');
			$end_go = strtotime($m.'23:59:59');
			foreach($channel as $key => $val){
				//$where_counts['_string'] = "cid = $val and status = 1 and pid = 1 and submit_tm >= $start_go and submit_tm <= $end_go";
				$sql_go = "select sum({$file}) as counts from activation_state where cid=$val  and submit_tm = $start_go";
				//$result_counts = $Model -> table('activation_state') -> where($where_counts) -> select();
				$result_counts = $activaDB -> query($sql_go);
				$result_counts_all[$key] = $result_counts;
			}
			$counts_all[$m] = $result_counts_all;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				if($v == null){
					$v = 0;
				}
				$val[$k] = $v;
			}
			$counts_all[$key] = $val;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				$result['counts'] = $v[0]['counts'];
				if($v[0]['counts'] == ''){
					$result['counts'] = 0;
				}
				$all[$k] = $result['counts'];
			}
			$result_all[$key]['counts'] = $all;
		}
		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$where['status'] = 1;
			$result = $Model -> table($channel_table) -> where($where) -> select();
			$co_group .= $category_list[$result[0]['category_id']]['name'].',';
			$channel_go .= $result[0]['chname'].',';
		}

		foreach($result_all as $key => $val){
			foreach($val['counts'] as $v => $k){
				$the_result[$v]['counts'] = $k;
			}
			$all_result[$key] = $the_result;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$result_go[$k] = $v['counts'];
			}
			$all_result[$key] = $result_go;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$file_str .= $v.',';
			}
			$list[$key] = $file_str;
			$file_str="";
		}

		foreach($list as $key => $val){
			$file_arr[] = $key.','.$val;
		}

		foreach($file_arr as $key => $val){
			$file_str_go .= $val."\n";
		}
		foreach($counts_total as $key => $val){
			$total_to .= $val.',';
		}
		$total_go = '合计,'.$total_to;

		$file_go = "{$file}".time();
		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition:attachment;filename='.$file_go.'.csv');
		header('Content-Type:APPLICATION/OCTET-STREAM');
		ob_start();
		$header_str =  iconv("UTF-8",'GBK',"日期,".$channel_go."");
		$category_str = iconv("UTF-8",'GBK',"类型,".$category."");
		$file_str_go=  iconv("UTF-8",'GBK',$file_str_go);
		$total_str = iconv("UTF-8",'GBK',$total_go);
		echo $category_str."\n";
		echo $header_str."\n";
		echo $file_str_go;
		echo $total_str;
		ob_end_flush();
		exit;
	}
	//统计管理_渠道量统计__导出扣量列表
	function derive_data_co_new(){
		$this -> derive_data_new(1);
	}
    //渠道管理_渠道列表
	function channels(){
        define("GO_HIDE_SKIN", 4);
		$Form = new Model();
		$table = 'sj_chl';
        import("@.ORG.Page");
		$category_list =  $this->category_config();
		$this->assign('category_list', $category_list);
        $source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_SHOW_TYPE;
        $map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $res = $Form->table('sj_admin_filter')->where($map)->find();
        $show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
        $this->assign( "show_chl", $show_chl );

        $chl = '';
        if(isset($_REQUEST['chl'])&&$_REQUEST['chl']){
        	$chl = $_REQUEST['chl'];
        }
        $chname = '';
		if(isset($_REQUEST['chname'])&&$_REQUEST['chname']){
        	$chname = $_REQUEST['chname'];
        }
		$chl_cid = '';
		if(isset($_REQUEST['chl_cid'])&&$_REQUEST['chl_cid']){
        	$chl_cid = $_REQUEST['chl_cid'];
        }
		$only_auth = '';
        if(isset($_REQUEST['only_auth'])){
        	$only_auth = $_REQUEST['only_auth'];
			$this->assign('only_auth', $only_auth);
        }
		$update_select = '';
		if(isset($_REQUEST['update_select'])){
			$update_select = $_REQUEST['update_select'];
			$this->assign('update_select',$update_select);
		}
		$activation_type = '';
		if(isset($_REQUEST['activation_type']) && $_REQUEST['activation_type']){
			$activation_type = $_REQUEST['activation_type'];
			$this->assign('activation_type',$activation_type);
		}

        $category_id = '';
		if(isset($_REQUEST['category_id'])&& $_REQUEST['category_id']!=''){
        	$category_id = $_REQUEST['category_id'];
        	$this->assign('category_id', $category_id);
        }

		$soft_update_select = '';
		if(isset($_REQUEST['soft_update_select'])){
			$soft_update_select = $_REQUEST['soft_update_select'];
			$this->assign('soft_update_select',$soft_update_select);
		}

        $source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			$sql_where=" ";
		}else{
			$source_type = USER_FILTER_TYPE;
			//$target_type= CHANNEL_SHOW_CONTROL;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql_where = " cid in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}')  and  ";
		}
		$sql_where .= 'status=1';
		if($chl){
			$sql_where .= " and chl='{$chl}'";
		}
		if($update_select!=''){
			$sql_where .= " and alone_update='{$update_select}'";
		}
		if($soft_update_select!=''){
			$sql_where .= " and soft_update='{$soft_update_select}'";
		}
		if($activation_type!=''){
			$sql_where .= " and activation_type='{$activation_type}'";
		}

		if($chname){
			$sql_where .= " and chname like '%{$chname}%'";
		}
		if($chl_cid){
			$sql_where .= " and chl_cid like '%{$chl_cid}%'";
		}
		if($category_id!=''){
			$sql_where .= " and category_id = '{$category_id}'";
		}

		if($only_auth!=''){
			$sql_where .= " and only_auth = '{$only_auth}'";
		}
		$platform = '';
        if(isset($_REQUEST['platform'])&&$_REQUEST['platform']){
        	$platform = escape_string($_REQUEST['platform']);
			$sql_where .= " and platform = '{$platform}'";

			$this -> assign('platform', $_REQUEST['platform']);
        }

		$count = $Form->table($table)-> where($sql_where) ->count();
        $p=new Page($count,20);
        $list=$Form->table($table)->where($sql_where)->limit($p->firstRow.','.$p->listRows)->order('submit_tm desc')->select();
        //$list = $Form->query($sql);
        if($chl) $this -> assign('chl', $chl);
        if($chname) $this -> assign('chname', $chname);
        if($chl_cid) $this -> assign('chl_cid', $chl_cid);
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        $this->assign("page", $page);
        foreach($list as $i => $v)
        {
        	$category_id = $v['category_id'];
        	if (!isset($category_result[$category_id])) $category_result[$category_id] = $category_list[$category_id];
            if($v['alone_update'] == 1) $list[$i]['alone_update'] = '独立更新';
            else $list[$i]['alone_update'] = '不独立更新';
            if($v['only_auth'] == 1) $list[$i]['only_auth'] = '不显示未授权软件';
            else $list[$i]['only_auth'] = '显示未授权软件';

            $list[$i]['soft_update'] = ($v['soft_update'] == 1) ? '更新' : '不更新';
            $list[$i]['category_name'] = $category_result[$category_id]['name'];
            switch ($list[$i]['activation_type']){
                case 5:  $list[$i]['activation_type_name'] = '普通非山寨';break;
                case 6:  $list[$i]['activation_type_name'] = '严格非山寨';break;
                case 9:  $list[$i]['activation_type_name'] = '普通山寨';break;
                case 10:  $list[$i]['activation_type_name'] = '严格山寨';break;
            }
            $category_result[$category_id]['result'][] = $list[$i];
        }
        $this->assign ( "list", $list );
        $this->assign ( "category_result", $category_result );
		$util = D('Sj.Util');
		$this->assign('product_list',$util->getProducts($platform));
		$this->display('channels');
	}
   //渠道管理_渠道设置_显示
	function channels_setup(){
		$Form = new Model();
		if($_POST){
			$table = 'sj_chl';
			$chl = $_POST['chl'];
			$chname=$_POST['chname'];
			$map['chl']=$chl;
			$chl6 =  substr($chl,0,8);//截取字符串8位
			//$map['chl_cid'] = $chl6.$cid;
			$map['chname']=$_POST['chname'];
			$map['alone_update']=$_POST['alone_update'];
			$map['soft_update']=$_POST['soft_update'];
			$map['oid']= $_POST['oid'];
			$map['mid']=1;
			$map['did']=1;
			$map['note']=$_POST['note'];
			$map['submit_tm']=time();
			$map['last_refresh']=time();
			$map['is_filter']=$_POST['is_filter'];
			$map['category_id']=$_POST['category_id'];
			$map['activation_type']=$_POST['activation_type'];
			$map['purpose']=$_POST['purpose'];
			$map['channel_ad']=$_POST['channel_ad'];
			$map['switch']=$_POST['switch'];
			$map['co_group']=$_POST['co_group'];
			$map['inputtext']=$_POST['inputtext'] ? $_POST['inputtext'] : '';
			$jumpurl = '/index.php/'.GROUP_NAME.'/ChannelStats/channels';
			if(empty($chl) || strlen($chl)<8 || strlen($chl)>64){
				$this->assign('jumpUrl',$jumpurl);
				$this->error('渠道号不能为空,且不能小于8位存在！');
			}
			$_POST['submit_tm']=time();
			$_POST['last_refresh']=time();
			$arr = $Form->table("{$table}")->field("chname,chl")->select();


			$newarr = array();
			$newchname=array();
			foreach($arr as $v){
				$newarr[] = $v['chl'];
				$newchname[]=$v['chname'];
			}

			if(in_array($chl,$newarr)){
				$this->assign('jumpUrl',$jumpurl);
				$this->error('数据插入失败,渠道号存在！');
			}
			if(in_array($chname,$newchname)){
				$this->assign('jumpUrl',$jumpurl);
				$this->error('数据插入失败,渠道名存在！');
			}
			$map['dnakey']=$_POST['dnakey'] ? $_POST['dnakey'] : '';
			$Form ->table("{$table}")->Create();
			$cid = $Form->table("{$table}")->add($map); // 写入用户数据到数据库
			$date['chl_cid'] = $chl6.$cid;//渠道号+连接cid[渠道id]
			$affect = $Form->table("{$table}") ->where("cid={$cid}") -> save($date);//更新字段数据

			$this->writelog('运营合作_渠道管理：添加渠道号为['.$chl.']的渠道','',$chl,__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/ChannelStats/channels');
			$this->success("数据保存成功！");
		}else{
			$list1=$Form->query("select oid,mname from pu_operating");
			$this->assign('list1',$list1);

			$this->assign('category_list', $this->category_config());
			$this->display('channels_setup');
		}
	}
	function category_config(){
		$category_list = array(
			101 =>array("name"=>"付费"),
			102 =>array("name"=>"换量"),
			103 =>array("name"=>"其他"),
		);
		return $category_list;
	}
    //渠道管理_渠道编辑
	function channels_edit(){
		$Form = new Model();
		$channel_table = 'sj_chl';
		if($_POST){
			$_POST['last_refresh']=time();
            $chl = $_POST['chl'];
            $name=$_POST['checkname'];
            $chname=$_POST['chname'];
			$note = $_POST['note'];
			$co_group=$_POST['co_group'];
			//$is_filte = $_POST['is_filte'];
			//echo $is_filte;exit;
            $map='';
            $map['cid']=array('neq'=>$_POST['cid']);
            $arr = $Form->table($channel_table)->field("chname,checkname,chl")->where($map)->select();
            $newarr = array();
            $newname = array();
            $newchname=array();
            foreach($arr as $v){
                $newarr[] = $v['chl'];
                $newname[] = $v['checkname'];
                $newchname[]=$v['chname'];
            }
			$jumpurl = '/index.php/'.GROUP_NAME.'/ChannelStats/channels';
            if(in_array($chl,$newarr)){
                $this->assign('jumpUrl',$jumpurl);
                $this->error('数据插入失败,渠道号存在！');
            }
            if(in_array($name,$newname)){
                $this->assign('jumpUrl',$jumpurl);
                $this->error('数据插入失败,查看用户名存在！');
            }
            if(in_array($chname,$newchname)){
                $this->assign('jumpUrl',$jumpurl);
                $this->error('数据插入失败,渠道名存在！');
            }
			$where =	array('cid' =>	$_POST['cid']);
			$data =	array();
			isset($_POST['checkname']) ? $data['checkname'] = 	$_POST['checkname'] : '';
			isset($_POST['chname']) ? $data['chname'] =	$_POST['chname'] : '';
			isset($_POST['checkpassword']) ? $data['checkpassword']	=	$_POST['checkpassword'] : '';
			isset($_POST['alone_update']) ? $data['alone_update']	=	$_POST['alone_update'] : '';
			isset($_POST['only_auth']) ? $data['only_auth']	=	$_POST['only_auth'] : '';
			//isset($_POST['soft_auth']) ? $data['soft_auth']	=	$_POST['soft_auth'] : '';
			isset($_POST['soft_update']) ? $data['soft_update']	=	$_POST['soft_update'] : '';
			isset($_POST['oid']) ? $data['oid']	= 	$_POST['oid'] : '';
			isset($_POST['note']) ? $data['note']	=	$_POST['note'] : '';
			$data['last_refresh']	=	time();
			isset($_POST['is_filter']) ? $data['is_filter']	=	$_POST['is_filter']  : '';
			isset($_POST['purpose']) ? $data['purpose'] =	$_POST['purpose']  : '';
			isset($_POST['activation_type']) ? $data['activation_type'] = $_POST['activation_type'] : '';
			isset($_POST['category_id']) ? $data['category_id']	=	$_POST['category_id'] : '';
			isset($_POST['channel_ad']) ? $data['channel_ad']	=	$_POST['channel_ad'] : '';
			isset($_POST['switch']) ? $data['switch'] = $_POST['switch'] : '';
			isset($_POST['platform']) ? $data['platform'] = $_POST['platform'] : '';
			isset($_POST['co_group']) ? $data['co_group'] = $_POST['co_group'] : '';
			isset($_POST['inputtext']) ? $data['inputtext'] = $_POST['inputtext'] : '';
			isset($_POST['dnakey']) ? $data['dnakey'] = $_POST['dnakey'] : '';
            $log = $this->logcheck(array('cid'=>$_POST['cid']),$channel_table,$data,$Form);
			$last_list = $Form->table($channel_table)->field("alone_update")->where($where)->find();
			$list=$Form->table($channel_table) -> where($where) -> save($data);

			if($list!==false){
                $this->writelog('运营合作_渠道管理_渠道设置：编辑ID为['.$_POST['cid'].']的渠道:'.$log,$channel_table,$_POST['cid'],__ACTION__ ,"","edit");
			    $this->assign('jumpUrl',$jumpurl);
			    $this->success('数据更新成功');
			}else{
				$this->error('数据更新失败');
			}
		}else{
			if(!empty($_GET['cid'])) {
				$category_list = $this->category_config();
				$this->assign('category_list', $category_list);
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$map = array(
					'source_type' => USER_FILTER_TYPE,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => CHANNEL_FILTER_TYPE,
					'target_value' => $_GET['cid'],
				);
				$source_type = USER_FILTER_TYPE;
				$target_type= CHANNEL_SHOW_CONTROL;
				//$target_type = CHANNEL_FILTER_TYPE;
				$zh_map = array(
					'source_type' => $source_type,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => $target_type
				);
				$zh_res = $Form->table('sj_admin_filter')->where($zh_map)->find();
				if(!$zh_res){
					if (!$Form->table('sj_admin_filter')->where($map)->select()){
					$this->error('禁止查看/编辑该渠道');
					}
				}
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_SHOW_TYPE;
				$map = array(
					'source_type' => $source_type,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => $target_type
				);
				$res = $Form->table('sj_admin_filter')->where($map)->find();
				$show_chl = (empty($res) || $res['filter_type'] == 1) ? false : true;
				$this->assign( "show_chl", $show_chl );

				$map = array(
					'cid' => $_GET['cid']
				);
				$vo = $Form->table($channel_table)->where($map)->select();
				$Form = M("pu_operating");
				$l1=$Form->query("SELECT p.oid, p.mname FROM {$channel_table} AS s JOIN pu_operating AS p ON s.oid = p.oid WHERE s.cid ='".$_GET['cid']."'");
				$this->assign('l1',$l1[0]);
				$list1=$Form->query("select oid,mname from pu_operating");
				$l2=$Form->query("SELECT p.mid, p.mname FROM {$channel_table} AS s JOIN pu_manufacturer AS p ON s.mid = p.mid WHERE s.cid ='".$_GET['cid']."'");
				$this->assign('l2',$l2);
				$l3=$Form->query("SELECT p.did, p.dname FROM {$channel_table} AS s JOIN pu_device AS p ON s.did = p.did WHERE s.cid ='".$_GET['cid']."'");
				$this->assign('l3',$l3);
				$Form = M("channel");
				$this->assign('list1',$list1);
				if($vo) {
					$util = D('Sj.Util');
					$this->assign('product_list',$util->getProducts($vo[0]['platform']));
					$this->assign('vo',$vo[0]);
					$this->display('channels_edit');
				}else{
					exit('编辑项不存在！');
				}
			}else{
				exit('编辑项不存在！');
			}
		}
	}
	public function pub_showChannel(){
		if (!empty($_POST['all'])) {
			unset($_COOKIE['c_keyword']);
		} elseif(isset($_POST['keyword'])) {
			$_COOKIE['c_keyword'] = $_POST['keyword'];
		}

		$type_list = array(
			'checobox' => 1,
			'radio' => 1,
		);

		if (isset($_GET['type']) && isset($type_list[$_GET['type']])) {
			$input_type = $_GET['type'];
		} else {
			$input_type = 'checkbox';
		}
		$this->assign('input_type', $input_type);


		$Model = new Model();
		$where = array();
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
			'source_type' => $source_type,
			'source_value' => $_SESSION['admin']['admin_id'],
			'target_type' => $target_type
		);
		$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();

		if(!empty($zh_res) && ($zh_res['filter_type'])==1){

		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}'";
			$res = $Model->query($sql);

			foreach ($res as $item) {
				$not_in_cid[] = $item['cid'];
			}
			$where['cid'] = array('in', $not_in_cid);
		}

		if (!empty($_COOKIE['c_keyword'])) {
			$this->assign('keyword', $_COOKIE['c_keyword']);
			$db = Db::getInstance();
			$keyword = $db->escape_string(trim($_COOKIE['c_keyword']));
			$where['chname'] = array('like', '%'. $keyword. '%');
		}
		$where['status'] = 1;
		if (!empty($_GET['platform'])) {
			$where['platform'] = $_GET['platform'];
		}
		$where['category_id'] =  array('exp',"not in(101,102,102)");
		$channels = $Model->table('sj_channel')->where($where)->field('cid,chname,category_id')->select();
		$cids = explode('_', $_COOKIE['cids']);
		$cids = array_unique($cids);
		$in_cid = array();
		foreach ($cids as $cid){
			if (!empty($cid)) $in_cid[] = $cid;
		}
		//$category_list = $this->category_config();
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();	
		foreach ($channels as $k => $v) {
			$channels[$k]['checked'] = in_array($v['cid'], $in_cid);
			if(!in_array($v['category_id'],array(101,102,103))){
				$category_list[$v['category_id']]['result'][] = $channels[$k];
			}
		}
		unset($category_list[101]);
		unset($category_list[102]);
		unset($category_list[103]);
		$this->assign('platform', $_GET['platform']);
		$this->assign('category_list', $category_list);
		$this->assign('channels', $channels);
		//$this->assign('selected_channel', $_SESSION['selected_channel']);
		$this->assign('callback', $_REQUEST['callback']);
		$this->assign('selected', $_REQUEST['selected']);
		$this->assign('ready', $_REQUEST['ready']);
		$this->display('Public::channel');
	}
}
?>
