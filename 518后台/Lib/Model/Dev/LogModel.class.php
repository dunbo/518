<?php

class LogModel extends Model {
	//获取已处理反馈日报数据
	function get_processed_daily($where) {
		//分页		
		import('@.ORG.Page2');
		$limit = isset($params['limit']) ? $params['limit'] : 10;	
		$param = http_build_query($params);
		$Page = new Page($total['total'],$limit,$param);
		$where_c = array(
			'type' => 1,
			'status' => 1
		);
		$subQuery = $this->table('sj_feedback_config')->where($where_c)->field('id')->buildSql(); 		
		$where['ques_id'] = array('in',$subQuery);	
		$total = $this->table('sj_processed_daily')->where($where)->field("count(DISTINCT admin_id) as total")->find();			
		$list = $this->table('sj_processed_daily')->where($where)->group('admin_id')->order('add_tm desc')->limit($Page->firstRow.','.$Page->listRows)->field('*,count(*) as toal')->select();
		$where_n = $where['add_tm'];
		//客服名称
		$adminname = get_table_data('',"sj_staff_config","admin_id","admin_id,admin_user_name");
		//渠道
		$where = array(
			'status' => 1,
			'type' => 1
		);
		$c_name = get_table_data($where,"sj_feedback_config","id","id,c_name");	
		//整理数据
		$return_arr = array();
		foreach($list as $k => $v){
			$key = $v['admin_id'];
			$return_arr[$key]['admin_name'] = $adminname[$v['admin_id']]['admin_user_name'];
			$return_arr[$key]['id'] = $v['id'];
			$return_arr[$key]['add_tm'] = date("Y-m-d H:i:s",$v['add_tm']);
			$return_arr[$key]['shift'] = $v['shift'] == 1 ? '白班' : '晚班' ;
			$return_arr[$key]['remark'] = $v['remark'] ? mb_substr($v['remark'],0,10, 'UTF-8')."..." : '';
			//取渠道个数
			if($v['toal'] > 1){
				$where = array(
					'admin_id'=>$v['admin_id'],
					'status'=>1,
					'add_tm'=>$where_n,
					'ques_id' => array('in',$subQuery)
				);
				$num = $this->table('sj_processed_daily')->where($where)->field('ques_id,num')->select();
				foreach($num as $vv){
					$return_arr[$key]['num'][$vv['ques_id']] = $return_arr[$key]['num'][$vv['ques_id']]+$vv['num'];
					$return_arr[$key]['count'] =  $return_arr[$key]['count']+$vv['num'];
				}
			}else{
				$return_arr[$key]['num'][$v['ques_id']] = $v['num'];
				$return_arr[$key]['count'] =  $return_arr[$key]['count']+$v['num'];
			}
		}
		return array($return_arr,$adminname,$c_name,$total['total'],$Page);
	}
	//获取日报单条数据
	function get_daily_find($id){
		$where = array(
			'id' => $id,
		);
		$res = $this->table('sj_processed_daily')->where($where)->find();
		$where = array(
			'admin_id' => $res['admin_id'],
		);
		$ret = $this->table('sj_staff_config')->where($where)->field('admin_user_name')->find();
		$res['admin_user_name'] = $ret['admin_user_name'];
		return $res;
	}
	//获取导出数据
	function get_exp_daily($where){
		//分页		
		$total = $_GET['count'];
		$p = isset($_GET['pp']) ? $_GET['pp'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 1000;	
		$totalPages = ceil($total/$limit);
		if($p == 1){
			$firstRow = 0;
		}else{
			$firstRow = ($p-1) * $limit;
		}
		$where_c = array(
			'type' => 1,
			'status' => 1
		);		
		$subQuery = $this->table('sj_feedback_config')->where($where_c)->field('id')->buildSql(); 		
		$where['ques_id'] = array('in',$subQuery);			
		$list = $this->table('sj_processed_daily')->where($where)->group('admin_id')->order('add_tm desc')->limit($firstRow.','.$limit)->field('*,count(*) as toal')->select();		
		$where_n = $where['add_tm'];
		//客服名称
		$adminname = get_table_data('',"sj_staff_config","admin_id","admin_id,admin_user_name");
		//渠道
		$where = array(
			'status' => 1,
			'type' => 1
		);
		$c_name = get_table_data($where,"sj_feedback_config","id","id,c_name");			
		if (!isset($_GET['fid'])) {
			$fid = uniqid();
		} else {
			$fid = $_GET['fid'];
		}
		mkdir('/tmp/export/', 0755, true);
		$file = '/tmp/export/'. session_id(). '_'.$fid.'export'. ".csv";
		$file = fopen($file, 'a');
		if($p ==1){
			fwrite($file,chr(0xEF).chr(0xBB).chr(0xBF)); 
			$heade1 = array();
			foreach($c_name as $v){ 
				$heade1[] =  $v['c_name'];
			}
			$heade = array('客服','班次','合计');	
			$heade2 = array('备注');	
			$heade = array_merge($heade,$heade1,$heade2);
			fputcsv($file, $heade);
		}
		
		foreach($list as $k => $v){
			$put_arr = array();
			$put_arr['admin_user_name'] = $adminname[$v['admin_id']]['admin_user_name'] ? $adminname[$v['admin_id']]['admin_user_name'] : "\t";
			$put_arr['shift'] = $v['shift'] == 1 ? '白班' : '晚班' ;
			//取渠道个数
			$num_arr = array();
			$remark_arr = array();
			$where = array(
				'admin_id'=>$v['admin_id'],
				'add_tm'=>$where_n,
				'status' => 1,
				'ques_id' => array('in',$subQuery)
			);
			$num = $this->table('sj_processed_daily')->where($where)->field('ques_id,num,day_tm,remark')->order('add_tm,ques_id desc')->order('add_tm desc')->select();
			if($v['toal'] > 1){
				foreach($num as $vv){
					$put_arr['count'] =  $put_arr['count']+$vv['num'];
					$num_arr['num_'.$vv['ques_id']] = $num_arr['num_'.$vv['ques_id']]+$vv['num'];
					if($vv['remark']) $remark_arr[$vv['day_tm']] = $vv['remark']; 
				}
			}else{
				$put_arr['count'] =  $put_arr['count']+$v['num'];
				$num_arr['num_'.$v['ques_id']] = $v['num'];
				foreach($num as $vv){
					if($vv['remark'])  $remark_arr[$v['day_tm']]  = $vv['remark']; 
				}
			}	
			foreach($c_name as $val){
				if(empty($num_arr['num_'.$val['id']])){
					$put_arr['num_'.$val['id']] = 0;
				}else{
					$put_arr['num_'.$val['id']] = $num_arr['num_'.$val['id']];
				}
			}	
			$put_arr['remark'] = implode(';',$remark_arr);	
			fputcsv($file, $put_arr);				
		}
		fclose($file);	
		$next_page = $p + 1;
		if ($p != $totalPages) {
			$par = $_GET;
			unset($par['pp'],$par['fid'],$par['button'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/LogStatic/exp_daily/pp/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			// if($_GET['fromdate'] && $_GET['todate']){
				// $name = $_GET['fromdate']."~".$_GET['todate'];
			// }else{
				$name = date("Y-m-d",time());
			//}
			$name_str = "客服已处理反馈日报".$name;
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}/name/{$name_str}",
			);	
		}
		return $data;				
	}
	//更新反馈已处理记录
	function update_records($pkg='',$feedback_type,$pid){		
		$time = time();
		$strtime = strtotime(date("Y-m-d",$time));
		$where = array(
			'package' => $pkg,
			'type' => 1,
			'ques_id' => $feedback_type,
			'count_tm' => $strtime,
			'status' => 1
		);
		if(empty($pkg) || $pkg==''){//包名为空时就是非合作游戏
			$where['type'] = 2;
		}
		$list = $this -> table('sj_feedback_count') -> where($where)->field('package')->find();
		$map = array(
			'package' => $pkg,
			'ques_id' => $feedback_type,
			'type' => 1,
			'count_tm' => $strtime,
			'num' => array('exp',"`num`+1"),
			'update_tm' => $time,
		);
		if(empty($pkg) || $pkg==''){//包名为空时就是非合作游戏
			$map['type'] = 2;
		}		
		if($list){
			$this -> table('sj_feedback_count') -> where($where)->save($map);
		}else{
			$map['num'] = 1;
			$this -> table('sj_feedback_count')->add($map);
		}
		//有添加客服才走下面的操作
		$where = array(
			'admin_id' => $_SESSION['admin']['admin_id'],
		);
		$admin_id = $this -> table('sj_staff_config') -> where($where)->field('admin_id')->find();
		if($admin_id){
			//sj_processed_daily 操作用户已处理表
			$day = date("Ymd",$time);
			$where = array(
				'admin_id' => $_SESSION['admin']['admin_id'],
				'day_tm' => $day,
				'ques_id' => $pid,
				'status' => 1
			);
			$daily = $this -> table('sj_processed_daily') -> where($where)->field('id')->find();
			$map = array(
				'admin_id' => $_SESSION['admin']['admin_id'],
				'day_tm' => $day,
				'status' => 1,
				'num' => array('exp',"`num`+1"),
				'update_tm' => $time,
			);
			if($daily){
				$this -> table('sj_processed_daily') -> where($where)->save($map);
			}else{
				$map['add_tm'] = $time;
				$map['ques_id'] = $pid;
				$map['num'] = 1;
				if(date("H") >= 13){
					$map['shift'] = 2;
				}
				$this -> table('sj_processed_daily')->add($map);
			}	
		}
	}
	//绑定用户
	function bind_user(){
		$admin_user = trim($_POST['user_518']);
		$open_user = trim($_POST['user_open']);
		$where = array(
			'admin_state' => 1,
			'admin_user_name' => $admin_user
		);
		$adminuser = $this -> table('sj_admin_users') -> where($where)->field('admin_user_id,admin_user_name')->find();
		$error = '';
		if(!$adminuser){
			$error .= "518无效用户";
		}
		$where = array(
			'admin_user_name' => $admin_user,
		);		
		if($open_user){
			$where['open_user'] = $open_user;
			$where['_logic'] = 'or';
		}
		$openuser = $this -> table('sj_staff_config') -> where($where)->field('admin_user_name,open_user')->find();
		if(!empty($openuser)){
			$error .= "账号已经绑定请不要重复添加";
		}		
		if($error == ''){
			$data = array(
				'admin_id' => $adminuser['admin_user_id'],
				'admin_user_name' => $adminuser['admin_user_name'],
				'open_user' =>  $open_user,
				'add_tm' =>  time(),
			);
			$res = $this -> table('sj_staff_config') -> add($data);
			if($res){
				return array('code'=>1,'msg'=> '添加成功','id'=>$adminuser['admin_user_id']);
			}else{
				return array('code'=>0,'msg' => '添加失败');
			}
		}else{
			return array('code'=>0,'msg' => $error);
		}
	}
}
?>