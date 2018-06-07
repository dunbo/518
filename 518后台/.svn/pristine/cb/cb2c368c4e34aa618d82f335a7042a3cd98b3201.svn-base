<?php
class safeScanAction extends CommonAction{
	function scanlist(){
		$scanpartner = array(
			'全部' => 100,
			'QQ' => array(1,'qq',' safetype > 1 '),
			'安全管家' => array(2,'aqgj',' level > 0 '),
			//'网秦' => 3,
			'金山' => array(4,'js',' safe_type > 2 '),
			'360' => array(5,'360',' sign in (3,4) '),
		);
        $model = new Model();
		$type = $_GET['ty'] ? $_GET['ty'] : 100; 
		$this -> assign('ty',$type);
		$cyc_date = '2013-07-04 23:59:59';
		if($_GET['cycle'] && $_GET['select_type'] == 1){
			$cycle_info = $_GET['cycle'];
			$info1 = substr($cycle_info,2);
			$info2 = explode('---',$info1);
			$cycle = $info1[0];
			$this -> assign('cycle',$cycle);
			$fromdate = $info2[0];
			$todate = $info2[1];
		}else if($_GET['select_type'] == 2){
			$fromdate = $_GET['s_tm'].' 00:00:00';
			$todate = $_GET['e_tm'].' 23:59:59';
		}else{
			$fromdate = date('Y-m-d', strtotime('-1 days')) . ' 00:00:00';
			$todate = date('Y-m-d', strtotime('-1 days')) . ' 23:59:59';
			$_GET['select_type'] = 2;
		}
		$this -> assign('select_type',$_GET['select_type']);
		$start_tm = $fromdate ? strtotime($fromdate) : strtotime(date('Y-m-d',time()));
		$this -> assign('from_value',date("Y-m-d H:i:s",$start_tm));
		$this -> assign('from_time',$start_tm);
		$time = time();
		$end_tm = $todate ? strtotime($todate) : $time;
		$this -> assign('to_value',date("Y-m-d H:i:s",$end_tm));
		$this -> assign('to_time',$end_tm);
		$scanpartner_flip = array_flip($scanpartner);
		if($type != 100){
			$where['provider'] = $type;
			$scanpartners[$scanpartner_flip[$type]] = $type;
		}else{
			$scanpartners = $scanpartner;
		}
		$start_time = $start_tm;
		$end_time = $end_tm;
		$where_string  = "time_req > {$start_time} and time_req <= {$end_time}";
		foreach($scanpartners as $idx => $scan_info){
			if( $idx == '全部'){continue;}
			$provider = $scan_info[0];
			$scaner = $scan_info[1];
			$unsafe_str = $scan_info[2];
			$where = array();
			$where['provider'] = $provider;
			$where['_string'] = $where_string;
			$req_count = $model -> table('sj_scan_safe_'.$scaner) ->where($where) ->count();
			$where['time_rep'] = 0;
			$no_resp_count = $model -> table('sj_scan_safe_'.$scaner) ->where($where) ->count();
			$softlist[$provider]['tpl_count'] = $req_count; 
			$softlist[$provider]['provider'] = $provider; 
			$softlist[$provider]['no_resp_count'] = $no_resp_count; 
			$softlist[$provider]['resp_count'] = $req_count - $no_resp_count;
			unset($where['time_rep']);
			$where['_string'] .= " and {$unsafe_str} "; 
			$unsafe_count = $model -> table('sj_soft_scan_result') ->where($where) ->count();
			$softlist[$provider]['unsafe_count'] = $unsafe_count;	
		}
		$statics = $softlist;
		$this -> assign('cycle_date',$this -> cycle($cyc_date));
		$this -> assign('scanpartner',$scanpartner);
		$this -> assign('scanpartner_arr', array_flip($scanpartner));
		$this -> assign('statics',$statics);
		$this -> display('safe_show');
	}
	function softlist(){
		$provider = $_GET['provider'];
		$type = $_GET['type'];
		$start_tm = $_GET['s_tm'] ? strtotime($_GET['s_tm']) : $_GET['start_tm'];
		$end_tm = $_GET['e_tm'] ? strtotime($_GET['e_tm']." 23:59:59") : $_GET['end_tm'];
		$this -> assign('from_value',date('Y-m-d',$start_tm));
		$this -> assign('to_value',date('Y-m-d',$end_tm));
		$this -> assign('ty',$provider);
		$scanpartner = array(
			'全部' => 100,
			'QQ' => 1,
			'安全管家' => 2,
			//'网秦' => 3,
			'金山' => 4,
			'360' => 5
		);
		$scanpart_arr = array_flip($scanpartner);
		$this -> assign('scanpartname',$scanpart_arr[$provider]);
		$where = array();
		switch($type){
			case 1:
			$mark = '发送总量软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm} ";
			break;
			case 2:
			$mark = '扫描返回软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm}  and time_rep <> 0";
			break;
			case 3:
			$mark = '扫描未返回软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm}  and time_rep = 0";
			break;
			case 4:
			$mark = '扫描返回不安全软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm} and safe > 1";
			break;
		}
		//search
		$where_s['status'] = 1;
		if ($package = addslashes($_GET['package'])) {
			$where_s['_string'] = ' package like "%' . $package . '%"'; 	
			$this->assign('package', $package);
			$status = 1;
		}
		if ($softid = addslashes($_GET['softid'])) {
			$where_s['softid'] = $softid; 	
			$this->assign('softid', $softid);
			$status = 1;
		}
		if ($softname = addslashes($_GET['softname'])) {
			$where_s['_string'] = ' softname like "%' . $softname . '%"'; 	
			$this->assign('softname', $softname);
			$status = 1;
		}
		$model = new Model();
		if ($status == 1) {
			$softlist = $model -> table('sj_soft')-> field('softid') -> where($where_s) -> select();
			foreach($softlist as $val) {
				$ids[] = $val['softid'];		
			}
			$where_sf['softid'] = array('in', $ids);
			$softfilelist = $model -> table('sj_soft_file')-> field('id,md5_file,sha1_file') -> where($where_sf) -> select();
			foreach ($softfilelist as $v) {
				$idfs[] = $v['id'];	
				$hash_md5[] = $v['md5_file'];
				$hash_sha1[] = $v['sha1_file']; 
			}
			if($provider == 2) $where['hash'] = array('in',$hash_sha1); 
			//$where['sfid'] = array('in', $idfs);
			else $where['hash'] = array('in',$hash_md5);
		}
		

		import('@.ORG.Page');
		$sfid_count = $model -> table('sj_soft_scan_result') ->where($where) -> field('sfid,provider,safe,time_req,time_rep,description') -> count();
		$page = new Page($sfid_count,20);
		$sfid_list = $model -> table('sj_soft_scan_result') ->where($where) -> field('sfid,provider,safe,time_req,time_rep,description') -> limit($page -> firstRow.','.$page -> listRows) -> select();
		$sfid = array();
		$req_info=$model ->table("pu_config")->where(array("config_type"=>"js_code","status"=>1))->getField("configcontent");
		$req_code=json_decode($req_info,true);
		foreach($sfid_list as $info){
			$sfid[] = $info['sfid'];
			$sfid_map[$info['sfid']] = $info; 
			$des = json_decode($info['description'],true);
			$virus = '';
			if($info['provider'] == 1){		
				if(!isset($des["response"]["trojan"]["description"])) {
					$virus = $des["virusdesc"];
				}else{
					$virus = $des["response"]["trojan"]["description"];
				}
			}
			if($info['provider'] == 2){
                if(isset($des["app"]["des"])){
                    $virus = $des["app"]["des"];
                }else if(isset($des["des"])){
                    $virus = $des["des"];
				}else if(isset($des["res"])){
					$virus = $des["res"];
				}
			}
			if($info['provider'] == 3){
				$virus = $des['ScanInfo']['responseInfo']['reason'];
			}
			if($info['provider'] == 4){
				$virus='';
				foreach($des['virus_desc'] as $kz=>$vz){
					$virus.=$req_code[$vz];
				}
			}
			
			if($info['provider'] == 5){
				$virus = $des['safe'];
			}
			$sfid_map[$info['sfid']]['virus'] = $virus;
		}

		$where = array();
		$where['id'] = array('in',$sfid);
		//$where['package_status'] = 1;
		$softids = $model -> table('sj_soft_file') -> where($where) -> field('id,softid') -> select();
		$softid_arr = array();
		foreach($softids as $info){
			$softid_arr[] = $info['softid'];
			$sfid_softid[$info['softid']] = $info['id']; 
		}
		$where = array();
		$where['softid'] = array('in',$softid_arr);
		$count = $model -> table('sj_soft') -> where($where) -> count();

		$softlist = $model -> table('sj_soft')-> field('softid,package,softname,hide,safe') -> where($where) -> select();
		foreach($softlist as $idx => $infos){
			$sfid = $sfid_softid[$infos['softid']];
			$softlist[$idx]['result'] = 
			$softlist[$idx]['time_req'] = $sfid_map[$sfid]['time_req'];
			$softlist[$idx]['time_rep'] = $sfid_map[$sfid]['time_rep'];
			$softlist[$idx]['description'] = $sfid_map[$sfid]['virus'];
			$softlist[$idx]['safe'] = $sfid_map[$sfid]['safe'];
		}
		$hide_type = array(
			'0'=>'历史',
			'1'=>'正常',
			'10'=>'正常',
			'2'=>'新软件审核',
			'20'=>'新软件审核',
			'3'=>'下架',
			'4'=>'编辑审核',
			'5'=>'升级审核',
			'50'=>'升级审核',
			'6'=>'驳回',
			'7'=>'驳回审核',
			'1024'=>'渠道软件',
		);
		$this -> assign('hide_arr',$hide_type);
		$this -> assign('scanpartner',$scanpartner[$provider]);
		$this -> assign('page',$page -> show());
		$this -> assign('softlist',$softlist);
		$this -> display('softlist');
	}
	
	
	function softlist_bak(){
		$provider = $_GET['provider'];
		$type = $_GET['type'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$this -> assign('from_value',date('Y-m-d',$start_tm));
		$this -> assign('to_value',date('Y-m-d',$end_tm));
		$this -> assign('ty',$provider);
		$scanpartner = array(
			'全部' => 100,
			'QQ' => 1,
			'安全管家' => 2,
			//'网秦' => 3,
			'金山' => 4,
			'360' => 5
		);
		$scanpart_arr = array_flip($scanpartner);
		$this -> assign('scanpartname',$scanpart_arr[$provider]);
		$where = array();
		switch($type){
			case 1:
			$mark = '发送总量软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm} ";
			break;
			case 2:
			$mark = '扫描返回软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm}  and time_rep <> 0";
			break;
			case 3:
			$mark = '扫描未返回软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm}  and time_rep = 0";
			break;
			case 4:
			$mark = '扫描返回不安全软件';
			$where['_string'] = "provider = {$provider} and time_req > {$start_tm} and time_req <= {$end_tm} and safe > 1";
			break;
		}
		$model = new Model();
		import('@.ORG.Page');

		$sfid_count = $model -> table('sj_soft_scan_result') ->where($where) -> field('sfid,provider,safe,time_req,time_rep,description') -> count();
		$page = new Page($sfid_count,20);
		$sfid_list = $model -> table('sj_soft_scan_result') ->where($where) -> field('sfid,provider,safe,time_req,time_rep,description') ->  select();
		//echo $model -> getLastSql();
		$sfid = array();
		$req_info=$model ->table("pu_config")->where(array("config_type"=>"js_code","status"=>1))->getField("configcontent");
		$req_code=json_decode($req_info,true);
		foreach($sfid_list as $info){
			$sfid[] = $info['sfid'];
			$sfid_map[$info['sfid']] = $info; 
			$des = json_decode($info['description'],true);
			$virus = '';
			if($info['provider'] == 1){		
				if(!isset($des["response"]["trojan"]["description"])) {
					$virus = $des["virusdesc"];
				}else{
					$virus = $des["response"]["trojan"]["description"];
				}
			}
			if($info['provider'] == 2){
                if(isset($des["app"]["des"])){
                    $virus = $des["app"]["des"];
                }else if(isset($des["des"])){
                    $virus = $des["des"];
				}else if(isset($des["res"])){
					$virus = $des["res"];
				}
			}
			if($info['provider'] == 3){
				$virus = $des['ScanInfo']['responseInfo']['reason'];
			}
			if($info['provider'] == 4){
				$virus='';
				foreach($des['virus_desc'] as $kz=>$vz){
					$virus.=$req_code[$vz];
				}
			}
			
			if($info['provider'] == 5){
				$virus = $des['safe'];
			}
			$sfid_map[$info['sfid']]['virus'] = $virus;
		}

		$where = array();
		$where['id'] = array('in',$sfid);
		//$where['package_status'] = 1;
		$softids = $model -> table('sj_soft_file') -> where($where) -> field('id,softid') -> select();
		$softid_arr = array();
		foreach($softids as $info){
			$softid_arr[] = $info['softid'];
			$sfid_softid[$info['softid']] = $info['id']; 
		}
		$where = array();
		$where['softid'] = array('in',$softid_arr);
		$softlist = $model -> table('sj_soft')-> field('softid,package,softname,hide,safe') -> where($where) -> select();
		foreach($softlist as $idx => $infos){
			$sfid = $sfid_softid[$infos['softid']];
			$softlist[$idx]['result'] = 
			$softlist[$idx]['time_req'] = $sfid_map[$sfid]['time_req'];
			$softlist[$idx]['time_rep'] = $sfid_map[$sfid]['time_rep'];
			$softlist[$idx]['description'] = $sfid_map[$sfid]['virus'];
			$softlist[$idx]['safe'] = $sfid_map[$sfid]['safe'];
		}
		$hide_type = array(
			'0'=>'历史',
			'1'=>'正常',
			'10'=>'正常',
			'2'=>'新软件审核',
			'20'=>'新软件审核',
			'3'=>'下架',
			'4'=>'编辑审核',
			'5'=>'升级审核',
			'50'=>'升级审核',
			'6'=>'驳回',
			'7'=>'驳回审核',
			'1024'=>'渠道软件',
		);
		$this -> assign('hide_arr',$hide_type);
		$this -> assign('scanpartner',$scanpartner[$provider]);
		$this -> assign('page',$page -> show());
		$this -> assign('softlist',$softlist);
		$this -> display('softlist');
	}
	
	function cycle($start_tm){
		/*$start_time_cycle = strtotime($start_tm);
		$curtime_c =  time() - $start_time_cycle;
		$cycle = 7*24*60*60;
		$days =  array();
		if($curtime_c >= $cycle){
			$num = $curtime_c / $cycle;
			$num_int = intval($num);
			for($i=1;$i<=$num_int;$i++){
				$cycle_time = $start_time_cycle - $cycle + $i * $cycle;
				$date_arr[$i] = array(date('Y-m-d H:i:s',$cycle_time),date('Y-m-d H:i:s',$cycle_time+$cycle));
			}
		}else{
			$date_arr[1] = array($start_tm,date('Y-m-d',strtotime($start_tm)).' 23:59:59');
		}*/

		$f = fopen('/tmp/scan_safe_all_recode_date.log','r');
		$i = 0;
		while(!feof($f)){
			$line = fgets($f,134217728);
			$d_arr = explode(',',trim($line));
			if($d_arr[0] && $d_arr[1]){
				$i++;
				$date_arr[$i] = array($d_arr[0],$d_arr[1]);
			}
		}
/*		$date = trim(file_get_contents('/tmp/scan_safe_all_recode_date.log'));
		$d_arr = explode(',',$date);
		$date_arr[1] = array($d_arr[0],$d_arr[1]);*/
		return $date_arr;
	}
}
