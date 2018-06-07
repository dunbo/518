<?php

class DoublecolorModel extends AdvModel {
	protected $connect_id = 18;
	protected $tablePrefix = 'gm_';
	public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ACTIVITY');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
	//获取期号
	public function get_issue_config(){
		$issue = S('Doublecolor_issue');
		if(!$issue){
			$issue = $this -> table('double_color_issue') ->field('id') -> order('id desc')->select();
			S('Doublecolor_issue',$issue,5);
		}
		return $issue;
	}

	public function get_prizelevel_config(){
		$prizelevel_arr = array(
			0 => '未中奖',
			1 => '一等奖',
			2 => '二等奖',
			3 => '三等奖',
			4 => '四等奖',
			5 => '五等奖',
			6 => '六等奖',
		);
		return $prizelevel_arr;
	}
	//奖池剩余安智币
	public function get_res_azb(){
		//$resazb = S('Doublecolor_resazb');
		//if(!$resazb){
			$info = $this -> table('double_color_issue') ->field('jackpot') -> order('id desc')->find();
			$resazb = $info['jackpot'];
		//	S('Doublecolor_resazb',$resazb,30);
		//}
		return $resazb;	
	}
	//开奖号
	public function get_winning_num($issue){
		$url = "http://kaijiang.500.com/ssq.shtml";
		//$url = "http://kaijiang.500.com/shtml/ssq/".substr($issue,2).".shtml";
		$file = file_get_contents($url);
		preg_match_all('#<li\s*class="ball_(red|blue)"[^>]*>([\d]+)</li>#',$file,$msg);
		return implode(',',$msg[2]);
	}
	function get_exp(){
		$pp = $_POST['pp'];
		$total = $_POST['total'];
		if (!$_POST['fid']) {
			$fid = uniqid();
		} else {
			$fid = $_POST['fid'];
		}		
		$exp_sql = base64_decode($_POST['exp_sql']);
		$limit = 1000;
		if($pp == 1){
			$firstRow = 0;
		}else{
			$firstRow = ($pp-1) * $limit;
		}		
		$sql = substr($exp_sql,0,strpos($exp_sql,'LIMIT')+5)." ".$firstRow.",".$limit;
		$totalPages = ceil($total/$limit);
		$list = $this->query($sql);
		mkdir('/tmp/export/', 0755, true);
		$file = '/tmp/export/'. session_id(). '_'.$fid.'export'. ".csv";
		$file = fopen($file, 'a');
		if($pp == 1){
			fwrite($file,chr(0xEF).chr(0xBB).chr(0xBF)); 
			$heade = array('序号','用户id','活动期号','购买号码','购买注数','购买时间','开奖号码','中奖类型','中奖礼券金额',"开奖时间","发奖状态","发奖时间");
			fputcsv($file, $heade);
		}	
		$issue_arr = array();
		foreach($list as $k => $vv){
			$issue_arr[$vv['issue']] = $vv['issue'];
		}
		$where = array('id' => array('in',$issue_arr));
		$kai_num =  $this->table('double_color_issue')-> where($where) ->field("id,kai_number,kai_time")-> select();
		$kai_num_new = array();
		foreach($kai_num as $key => $val){
			$kai_num_new[$val['id']] = $val;
		}		
		$prizelevel_config = $this->get_prizelevel_config();
		foreach($list as $k => $v){
			$put_arr = array();
			$put_arr['id'] = $v['id'] ? $v['id'] : "\t";
			$put_arr['uid'] = $v['uid'] ? $v['uid'] : "\t";
			$put_arr['issue'] = $v['issue'] ? $v['issue'] : "\t";
			$put_arr['purchase_num'] = $v['red_num'].$v['blue_num'];
			$put_arr['buynumber'] = $v['buynumber'] ? $v['buynumber'] : "\t";
			$put_arr['add_time'] = date("Y-m-d H:i:s",$v['add_time']);
			$put_arr['last_kai_number'] = $kai_num_new[$v['issue']]['kai_number'] ? $kai_num_new[$v['issue']]['kai_number'] : "\t";
			$put_arr['prizelevel'] = $prizelevel_config[$v['prizelevel']] ? $prizelevel_config[$v['prizelevel']] : "\t";
			$put_arr['prizenum'] = $v['prizenum'] ? $v['prizenum'] : "\t";
			$put_arr['kai_time'] = $kai_num_new[$v['issue']]['kai_time'] ? date("Y-m-d H:i:s",$kai_num_new[$v['issue']]['kai_time']) : "\t";
			$put_arr['is_send'] = $v['is_send'] == 1 ? "已发奖" : "未发奖";
			$put_arr['send_time'] = $v['send_time'] ? date("Y-m-d H:i:s",$v['send_time']) : "\t";
			fputcsv($file, $put_arr);				
		}
		fclose($file);	
		$next_page = $pp + 1;
		if ($pp != $totalPages) {
			$data = array(
				'type' => 'pager',
				'pp' => $next_page,
				'fid' => $fid
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}/name/{$name_str}",
			);	
		}
		return $data;		
	}	
}
