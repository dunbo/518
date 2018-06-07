<?php
define('ABI_ARMEABI', 1);
define('ABI_ARMEABI_V7A', 2);
define('ABI_X86', 4);
define('ABI_MIPS', 8);
class SoftauditModel extends Model {
	//获取审核中的数据
	public function getsoftaudit($status,$record_type,$params, $limit,$room_total=0){
		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		if($record_type == 2 || $record_type == 4 ){	
			$is_tmp = 0;
		}else{
			$is_tmp = 1;
		}
		//var_dump($params);
		//搜索功能
		if($params['form_type'] == "softupgrade" || $params['form_type'] == "debut" || $params['form_type'] == "time_shelves"){
			$where_d = array();
			$tm = time();
			$where_d['_string'] = "status=2 and del_status=1 and debut_time+(debut_length*3600) >= {$tm} and is_apk=1";
			$debut = $this->table('sj_soft_debut')->where($where_d)->field('package')->select();
			$package_d = array();
			foreach($debut as $key=>$val){
				if($val['package'])
				$package_d[] = $val['package'];
			}
		}
		$where['s.sdk_status'] = array("exp","<=1");		
		if(isset($params['form_type']) && $params['form_type'] == "softupgrade"){
			if($package_d){
				$where['s.package'] = array("not in",$package_d);
			}
			//移除定时通过的软件
			$where['s.pass_status'] = array("neq",1);
		}else if(isset($params['form_type']) && $params['form_type'] == "debut"){
			$where['s.package'] = array("in",$package_d);
			//移除定时通过的软件
			$where['s.pass_status'] = array("neq",1);			
		}else if(isset($params['form_type']) && $params['form_type'] == "time_shelves"){
			//定时上架的软件
			$where['s.pass_status'] = array("eq",1);	
			$where['s.record_type'] = array("in",array(1,3));
		}else if(isset($params['form_type']) && $params['form_type'] == "sdk_test"){
			//sdk待审核
			if($params['sdk_status'] == 2 || $status == 3){	
				$where['s.sdk_status'] = 2;
			}else if($params['sdk_status'] == 1){
				$where['s.sdk_status'] = 1;
			}
			$where['s.record_type'] = array("in",array(1,2,3,8));
		}
		if($record_type ==1){
			//移除定时通过的软件
			$where['s.pass_status'] = array("neq",1);	
		}
		$where['s.status'] = $status;
		if(!empty($record_type)){
			$where['s.record_type'] = $record_type;
		}
		if(isset($params['softid'])){
			$where['s.softid'] = array("eq","{$params['softid']}");
		}	
		if(isset($params['package_id'])){
			if($params['package_id_type'] == 1){	
				$where['s.package'] = trim($params['package_id']);	
			}else if($params['package_id_type'] == 2){
				if($record_type == 3){
					$where['s.update_from'] = trim($params['package_id']);
				}else{
					$where['s.softid'] = trim($params['package_id']);
				}
			}
		}	
		if(isset($params['dev_id'])){
			$where['s.dev_id'] = array("eq","{$params['dev_id']}");
		}	
		if(isset($params['softids'])){
			$where['s.softid'] = array("eq","{$params['softids']}");
			$where['s.update_from'] = array("eq","{$params['softids']}");
		}
		if(isset($params['ip'])){
			$where['s.ip'] = array("eq","{$params['ip']}");
		}
		if(isset($params['update_from'])){
			$where['s.update_from'] = array("eq","{$params['update_from']}");
		}
		if(isset($params['softname'])){
			$where['s.softname'] = array("like","%{$params['softname']}%");
		}
		if(isset($params['package'])){
			$params['package'] = trim($params['package']);
			$where['s.package'] = array("eq","{$params['package']}");
		}
		if(isset($params['package_arr'])){
			$package_arr = explode(',',$params['package_arr']);
			$where['s.package'] = array("in",$package_arr);
		}
		if(isset($params['room']) && $room_total){		    
		    $room = $params['room']-1;
		    $where['s.id'] = array("exp",">0 and mod(id,{$room_total})=$room");
		}
		if(isset($params['dev_name']) || isset($params['email']) || isset($params['dev_type'])){
			if(isset($params['email'])){
				$params['email'] = trim($params['email']);
				$wheres['email'] = array("eq","{$params['email']}");
			}
			if(isset($params['dev_name'])){	
				$wheres['dev_name'] = array('like',"%{$params['dev_name']}%");
			}
			if(isset($params['dev_type'])){
				$wheres['type'] = array("eq","{$params['dev_type']}");
			}
			$devname = $this->table('pu_developer')->where($wheres)->field('dev_id')->select();
			foreach ($devname as $n => $m ){
				$dev_id[] = $m['dev_id'];
			}
			$where['s.dev_id'] = array("in",$dev_id);
		}
		if( !empty($params['Official']) || isset($params['shield_status']) || isset($params['type'])){
			$tm = time();
			if(isset($params['shield_status'])){
				if($params['shield_status'] == 2){
					$where['_string'] = "(n.`shield`='2' OR n.shield_end <={$tm} or n.shield IS NULL)";
				}else{
					$where['n.shield'] = $params['shield_status'];
					$where['n.shield_start'] = array('exp',"<={$tm}");			
					$where['n.shield_end'] = array('exp',">={$tm}");
				}
			}
			if(isset($params['type'])){
				$where['n.type'] = $params['type'];			
				$where['n.type_start'] = array('exp',"<={$tm}");			
				$where['n.type_end'] = array('exp',">={$tm}");
			}
			if(!empty($params['Official'])){
				if($params['Official'] == 2){
					$where['_string'] =  "(n.status in(0,2) or n.status IS NULL or n.terminal_time <={$tm})";
				}else{
					$where['_string']  = "n.status='{$params['Official']}'  and n.start_time <= {$tm} and n.terminal_time >={$tm} ";
				}
			}			
		}		
		//搜索广告__sdk接入
		if(!empty($params['ad_id']) || !empty($params['sdk'])){
			$ad_where = "package_status = 1 ";
			if(!empty($params['ad_id'])){
				$adid_arr = explode(',',$params['ad_id']);	
				$ad_where .= "and (";
				foreach($adid_arr as $v){
					if($v == '') continue;
					if($v != 10005 && $v != 10006 && $v != 0 ){			
						$ad_where .= " ad_new like '%,{$v},%' or";
					}else if($v == 0){
						//$ad_where .= " ad_new = '' or";
						$ad_where .= " leafstatus = 2 or";
					}else if($v == 10005){
						$ad_where .= "  ad_new != '' or leafstatus = 1 or";
					}else if($v == 10006){
						$ad_where .= " leafstatus = 0 or";
					}
				}
				$ad_where = substr($ad_where,0,-3);
				$ad_where .= " )";
			}
			if(!empty($params['sdk'])){
				$ad_where .=  " and anzhi_sdk != ''";
			}			
			$subQuery = $this->table('sj_soft_file_tmp')->field('tmp_id')->where($ad_where)->buildSql(); 
			if(!empty($params['sdk']) && $params['sdk'] == 2){
				//否
				$where['s.id'] = array('not in',"{$subQuery}");
			}else{
				$where['s.id'] = array('in',"{$subQuery}");
			}
		}		
		if(!empty($params['begintime']) && !empty($params['endtime'])){
			$begintime = strtotime($params['begintime']);
			$endtime = strtotime($params['endtime']);
			if(isset($params['form_type']) && $params['form_type'] == "time_shelves"){
				$key_v = 's.pass_time';
			}else if(isset($params['form_type']) && $params['form_type'] == "sdk_test"){
				$key_v = 's.pass_time';
			}else{
				$key_v = 's.upload_tm';
			}
			$where[$key_v] = array(array("egt",$begintime),array("elt",$endtime));
		}
		if(!empty($params['begintime_up']) && !empty($params['endtime_up'])){
			$begintime_up = strtotime($params['begintime_up']);
			$endtime_up = strtotime($params['endtime_up']);
			$where['s.upload_tm'] = array(array("egt",$begintime_up),array("elt",$endtime_up));
		}
		//软件来源
		if(isset($params['soft_source'])){
			$where['s.update_type'] = $params['soft_source'];
		}
		//安全状态
		if(isset($params['safe'])){
			if($params['safe'] == 1){
				$where['s.safe'] = array('elt',1);
			}elseif($params['safe'] == 2){
				$where['s.safe'] = array('gt',1);
			}
		}else{
			$where['s.safe'] = array('elt',1);
		}
		$shield_package=$this->table('sj_soft_bj_shield')->where(array('is_shield'=>1))->field('package')->select();

		
		$shield_package_str = '';
		foreach ($shield_package as $n => $m ){
			$shield_package_str .= "'".$m['package']."',";
		}
		
		$shield_package_str = substr($shield_package_str,0,-1);
		if(isset($params['is_bj_shield'])){
			if($params['is_bj_shield'] == 1){
				$where['s.package'] = array('in',$shield_package_str);
			}else{
				$where['s.package'] = array('not in',$shield_package_str);
			}
		}
        //联运状态
        if($params['is_sdk']!='-1'&&!is_null($params['is_sdk'])&&$record_type != 2){
			$where['s.sdk_status'] = $params['is_sdk'];
		}
		//未通过列表
		if(!empty($params['begintime_a']) && !empty($params['endtime_a'])){
			$begintime_a = strtotime($params['begintime_a']);
			$endtime_a = strtotime($params['endtime_a']);
			$where['s.last_refresh'] = array(array("egt",$begintime_a),array("elt",$endtime_a));
		}                
		if(!empty($params['cateid'])){
			$cateids = explode(',',$params['cateid']);
			$cateidarr = array();
			foreach($cateids as $vv){
				if($vv != ''){
					$cateidarr[] = ",".$vv.",";
				}
			}
			if($cateidarr) $where['s.category_id'] = array('in',$cateidarr);
		}
		//下载量和更新时间排序--提交时间排序 
 		if(!empty($params['orderby'])){
			$orderby = '';
			if ($params['orderby'] == 'download') {
				$orderby = '(s.total_downloaded+s.total_downloaded_add-s.total_downloaded_detain)';
			} elseif ($params['orderby'] == 'time') {
				$orderby = 's.last_refresh';
			}elseif($params['orderby'] == 'upload_tm'){
				$orderby = 's.last_refresh';
			}elseif($params['orderby'] == 'but_tm'){
				$orderby = 'd.debut_time';
			}else if($params['orderby'] == 'shelves_tm'){
				$orderby = 's.pass_time';
			}
		}else{
			// if($status == 2){
				// $orderby = 's.upload_tm';
			// }elseif($status == 3){
				$orderby = 's.last_refresh';
			//}
		}
		if($status == 3){
			$order  = !empty($params['order']) ? $params['order'] : 'd';
		}else{
			$order  = !empty($params['order']) ? $params['order'] : 'a';
		}
		if ($order == 'd') {
			$order_str = $orderby.' desc';
		} elseif ($order == 'a') {
			$order_str = $orderby.' asc';
		}
		//-------分页
		if($record_type==2){
			if($params['is_sdk']=="1"){
				$where['a.package'] = array('exp','IS NOT NULL');
			}else if($params['is_sdk']=="0"){
				$where['a.package'] = array('exp','IS NULL');
			}
		}
		//测试类型
		if(isset($params['test_type'])){
			if($params['sdk_status'] !=1){
				if(in_array($params['test_type'],array('1','2','3'))){
						$where['a.is_online'] = 0;				
					}else if(in_array($params['test_type'],array('4','5','6'))){
						$where['a.is_online'] = 1;	
					}
				if($status == 3){
					if($params['test_type'] == 1||$params['test_type']==4){
						$where['a.is_accept_sdk'] = 0;
					}else if($params['test_type'] == 2||$params['test_type']==5){
						$where['a.is_accept_sdk'] = 1;
						$where["s.single_sdk"] =  3;	
					}else if($params['test_type'] == 3||$params['test_type']==6){
					}
									
				}else{					
					if($params['test_type'] == 1||$params['test_type']==4){
						$where['a.is_accept_sdk'] = 0;
						$where["s.single_sdk"] = array('not in',array('3','1'));
					}else if($params['test_type'] == 2||$params['test_type']==5){
						$where['s.single_sdk'] = 2;
					}else if($params['test_type'] == 3||$params['test_type']==6){
						$where['s.single_sdk'] = 0;
					}	
				}
				
			}else if($params['sdk_status'] ==1){
				if($params['test_type'] == 1){
					$where['a.is_online'] = 0;		
				}else if($params['test_type'] == 2){
					$where['a.is_online'] = 1;	
				}
			}
			
		}else{
			if($status == 2){
				$where["s.single_sdk"] = array('not in',array('3','1'));
			}
			
		}
		//账号接入
		if(isset($params['accept_account_status'])){
			$where['a.is_accept_account'] = $params['accept_account_status'];	
		}
		//单机sdk测试状态
		//var_dump($params['sdk_status']);
		if($status != 3){
			//$where["s.single_sdk"][] = array('not in',array('3','1'));
		}
		if( isset($params['shield_status']) || isset($params['type'])||isset($params['Official'])){
			if(($params['is_sdk']!='-1'&&!is_null($params['is_sdk'])&&$record_type == 2)||isset($params['accept_account_status'])||isset($params['test_type'])){
				$total = $this->table('sj_soft_tmp s')->join("sj_soft_whitelist a on s.package = a.package ")->join("sj_soft_note n ON n.package = s.package")->where($where)->count();
			}else{
				$total = $this->table('sj_soft_tmp s')->join("sj_soft_note n ON n.package = s.package")->where($where)->count();
			}
		}else if(isset($params['form_type']) && $params['form_type'] == "debut"){
			$where['d.status'] = 2;
			$where['d.del_status'] = 1;
			$where['_string'] = "d.debut_time+(d.debut_length*3600) >= {$tm}";
			$total = $this->table('sj_soft_debut d')->join("sj_soft_note n ON n.package = d.package")->join("sj_soft_tmp s ON d.package = s.package")->where($where)->count();
		}else{
			if(($params['is_sdk']!='-1'&&!is_null($params['is_sdk'])&&$record_type == 2)||isset($params['accept_account_status'])||isset($params['test_type'])){
				$total = $this->table('sj_soft_tmp s')->join("sj_soft_whitelist a on s.package = a.package ")->where($where)->count();
			}else{
				$total = $this->table('sj_soft_tmp s')->where($where)->count();
			}
				
		}

		import('@.ORG.Page2');
		$param = http_build_query($params);
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		if( isset($params['shield_status']) || isset($params['type'])){
			if(($params['is_sdk']!='-1'&&!is_null($params['is_sdk'])&&$record_type == 2)||isset($params['accept_account_status'])||isset($params['test_type'])){
				$soft_tmp = $this->table('sj_soft_tmp s')->join("sj_soft_whitelist a on s.package = a.package ")->join("sj_soft_note n ON n.package = s.package")->where($where)->field('s.*,n.status as f_status,n.start_time as f_start_time,n.terminal_time as f_terminal_time,n.shield,n.shield_start,n.shield_end,n.type,n.type_start,n.type_end,n.start_time,n.terminal_time,n.game_charge,n.charge_start,n.charge_end')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select(); 
			}else{
				$soft_tmp = $this->table('sj_soft_tmp s')->join("sj_soft_note n ON n.package = s.package")->where($where)->field('s.*,n.status as f_status,n.start_time as f_start_time,n.terminal_time as f_terminal_time,n.shield,n.shield_start,n.shield_end,n.type,n.type_start,n.type_end,n.start_time,n.terminal_time,n.game_charge,n.charge_start,n.charge_end')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select(); 
			}
			
		}else if(isset($params['form_type']) && $params['form_type'] == "debut"){
			$where['d.status'] = 2;
			$where['d.del_status'] = 1;
			$where['_string'] = "d.debut_time+(d.debut_length*3600) >= {$tm}";
			$soft_tmp = $this->table('sj_soft_debut d')->join("sj_soft_note n ON n.package = d.package")->join("sj_soft_tmp s ON d.package = s.package")->where($where)->field('s.*,n.status as f_status,n.start_time as f_start_time,n.terminal_time as f_terminal_time,n.shield,n.shield_start,n.shield_end,n.type,n.type_start,n.type_end,n.start_time,n.terminal_time,n.game_charge,n.charge_start,n.charge_end')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select();
		}else{
			if(($params['is_sdk']!='-1'&&!is_null($params['is_sdk'])&&$record_type == 2)||isset($params['accept_account_status'])||isset($params['test_type'])){
				$soft_tmp = $this->table('sj_soft_tmp s')->join("sj_soft_whitelist a on s.package = a.package ")->join("sj_soft_note n ON n.package = s.package")->where($where)->field('s.*,n.status as f_status,n.start_time as f_start_time,n.terminal_time as f_terminal_time,n.shield,n.shield_start,n.shield_end,n.game_charge,n.charge_start,n.charge_end')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select();
			}else{
				$soft_tmp = $this->table('sj_soft_tmp s')->join("sj_soft_note n ON n.package = s.package")->where($where)->field('s.*,n.status as f_status,n.start_time as f_start_time,n.terminal_time as f_terminal_time,n.shield,n.shield_start,n.shield_end,n.game_charge,n.charge_start,n.charge_end')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select();
			}
			
		}
		//echo $this->getlastsql();
		$now_time = time();
		$ip = array();
		$softlist = array();
		$devids = '';
		$categoryids = '';
		$ascll_softname = array(45, 91, 92, 93 ,40, 41 ,46, 44, 58, 39, 34, 45, 60 ,62,239,227);
		$appcert_status = C('appcert_status');
		foreach ($soft_tmp as $k => $v){
			$softlist[$k]['id'] = $v['id'];
			$softlist[$k]['softid'] = $v['softid'];
			$softlist[$k]['dev_id'] = $v['dev_id'];
			$softlist[$k]['safe'] = $v['safe'];
			$softlist[$k]['softname'] = $v['softname'];
			$softlist[$k]['package'] = $v['package'];
			$softlist[$k]['language'] = $v['language'];
			$softlist[$k]['version'] = $v['version'];
			$softlist[$k]['version_code'] = $v['version_code'];
			$softlist[$k]['update_from'] = $v['update_from'];
			$softlist[$k]['update_type'] = $v['update_type'];
			$softlist[$k]['record_type'] = $v['record_type'];
			$softlist[$k]['shelf_reason'] = $v['shelf_reason'];
			$softlist[$k]['pass_time'] = $v['pass_time'] ? date('Y-m-d H:i:s',$v['pass_time']) : '';
			$softlist[$k]['min_firmware'] = $v['min_firmware'];
			$softlist[$k]['max_firmware'] = $v['max_firmware'];
			$softlist[$k]['test_report'] = $v['test_report'];
			$softlist[$k]['type_report'] = $v['type_report'];
			$softlist[$k]['sdk_version'] = $v['sdk_version'];
			$softlist[$k]['sdk_status'] = $v['sdk_status'];
			$softlist[$k]['single_sdk'] = $v['single_sdk'];
			//判断包名是否属于可疑竞品
			$extension_arr = C('extension_arr');
			foreach($extension_arr as $val){
				if(strpos($v['package'],$val)){
					$softlist[$k]['packExtension'] = 1;
					continue;
				}
			}
			//判断是否支持x86
			if ($v['abi'] == 0 || ($v['abi'] & ABI_X86) == ABI_X86){
				$softlist[$k]['abi_support'] = "1";
			}else{
				$softlist[$k]['abi_support'] = "2";
			}
			//总下载量
			$softlist[$k]['total_downloaded'] = $v['total_downloaded'];
			//增量
			$softlist[$k]['total_downloaded_add'] = $v['total_downloaded_add'];
			//扣量数量
			$softlist[$k]['total_downloaded_detain'] = $v['total_downloaded_detain'];
			//剩余量
			$softlist[$k]['total_downloaded_surplus'] = number_format($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add']);
			//是否为官方
			if($v['f_status'] == 1 && $v['f_start_time'] < $now_time && $v['f_terminal_time'] > $now_time ){
			    $softlist[$k]['f_status_now'] = 1;
			}
			if($v['upload_tm']){
				$softlist[$k]['upload_tm'] = date("Y-m-d H:i:s",$v['upload_tm']);
			}
			$softlist[$k]['ip'] = $v['ip'];
			//abi显示
			foreach($known_abis as $abi_key => $abi_value){
				if($abi_value & $v['abi'] || $v['abi'] == 0){
					$softlist[$k]['abis'][] = $abi_key."&nbsp;&nbsp;";
				}
			}
			if(!empty($v['category_id'])){
				$categoryids .= substr("{$v['category_id']}",1);
			}
			if(!empty($v['dev_id'])){
				$devids .= "{$v['dev_id']}".",";
			}
			if($v['id']) $tid[] = $v['id'];	
			if($v['softid']) $sid[] = $v['softid'];
			if($v['package']){
				$package[] = $v['package']; 
			}
			//含标点
			$softname_mark = '';
			$is_ascll = false;
			$softname = preg_replace('/\xa3([\xa1-\xfe])/e', 'chr(ord(\1)-0x80)', $v['softname']);
			$softname_len = mb_strlen($softname,'UTF8'); 
			$softname_mark = ''; 
			for($i=0;$i<$softname_len;$i++){
			    $char = mb_substr($softname, $i, 1, 'utf-8');
			    if(in_array(ord($char),$ascll_softname)){
			        $is_ascll = true;
			        break;
			    }else{
			        $softname_mark .= $char;
			    }
			}
			if($is_ascll){
			    if(strlen($softname_mark)<=0){
			        $softname_mark = mb_substr($softname,1,$softname_len,'utf-8');
			    }
			    $softlist[$k]['softname_mark'] = $softname_mark;
			}else{
			    $softlist[$k]['softname_mark'] = '';
			}
			if($v['ip']) $ip[] = $v['ip'];
		}
		$ip = array_unique($ip);
		$ip_num = array();
		if($ip){
			$where = array(
				'status'=>2,
				'record_type'=>$record_type,
				'ip'=>array('in',$ip),
			);
			$ip_list = $this->table('sj_soft_tmp s')->where($where)->field('count(ip) as counts,ip,package')->group('ip')->select();
			foreach($ip_list as $v){
				$ip_num[$v['ip']] = $v['counts'];
			}
		}
		$md5sum_list = array();
		$sha1sum_list = array();	
		$file_icon_arr = array();
		$file_tmp_icon = array();	
		$icon_tmp_arr = array();		
		if($tid){                                                     
			//sj_soft_file_tmp数据
			$where = array(
				'tmp_id'=> array('in',$tid),
				'package_status' => array('exp',"> 0"),
			);	
			$file_tmp = $this ->table('sj_soft_file_tmp')->where($where)->select();
			foreach($file_tmp as $key => $val){
				if($val['md5_file']){
					$md5sum_list[] = $val['md5_file'];
				}
				if($val['sha1_file']){
					$sha1sum_list[] = $val['sha1_file'];
				}
			}	
			if($md5sum_list && $sha1sum_list){
				$md5sum =  array_merge($md5sum_list,$sha1sum_list);
			}else if($md5sum_list){
				$md5sum = $md5sum_list;
			}else if($sha1sum_list){
				$md5sum = $sha1sum_list;
			}	
			if($md5sum){
				//广告--积分--推广商
				$md5_adinfo = $this -> getAdsByHash($md5sum);
				//安全扫描
				$sha1_adinfo = $this -> getbyhash($md5sum);
			}
			foreach($file_tmp as $key => $val){
				//无广告
				$str = '';
				$last_refresh = $val['last_refresh'] ? date("Y-m-d H:i:s",$val['last_refresh']):'';
				if($val['leafstatus'] == 2){
					$str = "无广告<br/><来自于金山><br/>".$last_refresh."<br/>无广告<br/><来自于腾讯><br/>".$last_refresh."<br/>";
				}else if($val['leafstatus'] == 0){
					$str = "未打标<br/>".$last_refresh;
				}
				$val['md5_adinfo'] = $md5_adinfo[$val['sha1_file']].$md5_adinfo[$val['md5_file']].$str;
				$val['sha1_adinfo'] = $sha1_adinfo[$val['sha1_file']].$sha1_adinfo[$val['md5_file']];
				$file_tmp_icon[$val['tmp_id']] = $val; 
				$file_sign[$val['apk_name']] = $val['sign'];
			}		
		}
		//sj_soft_file表数据	
		if($sid){
			$where = array(
				'softid' => array('in',$sid),
				'package_status' => array('exp',"> 0"),
			);
			$file_icon_arr = get_table_data($where,"sj_soft_file","softid","id,softid,iconurl,iconurl_72,sign,apk_name");
		}		
		//app认证
		if($tid){
			$app_info = get_table_data(array('tmp_id'=>array('in',$tid)),"sj_appcert_soft","tmp_id","tmp_id,status,msg");                        
		}
		//类别名称
		$category_all = array();		
		if($categoryids){	
			$where = array(
				'status' => 1,
				'category_id' => array('in',substr($categoryids,0,-1)),
			);
			$category_all = get_table_data($where,"sj_category","category_id","category_id,name,status");
		}
		//开发者名称
		if($devids){
			$where = array(
				'dev_id' =>  array('in',substr($devids,0,-1)),
			);
			$dev_all = get_table_data($where,"pu_developer","dev_id","dev_id,dev_name,type,email,status");
		}
		$icon_arr = array();
		$brush_adapter_list = array(); 		
		if($package){
			//白名单数据
			$where = array(
				'status' => 1,
				'package' => array('in',$package),
			);
			$brush_adapter_list = get_table_data($where,"sj_safe_white_package","package","package");
		}
		//软件包名高亮显示
		$config = $this -> table('pu_config') -> where("config_type = 'soft_cp_highlight_edit' and status =1")->field('configcontent')->find();
		$package_arr =  explode("|",$config['configcontent']);
		$package_highlight = array(); 
		foreach($package_arr as $key => $val){
			$package_highlight[trim($val)] = $val;
		}
		if($package){
			$where = array(
				'package_status' => 1,
				'apk_name' => array('in',$package),
			);
			$fileicon_arr_t = get_table_data($where,"sj_soft_fileicon_tmp","apk_name","apk_name,md5_icon");
			
			$fileicon_arr = get_table_data($where,"sj_soft_fileicon","apk_name","apk_name,md5_icon");
			//检测排期
			$soft_mdel = D('Dev.Softlist');
			$whiteList = $soft_mdel ->soft_WhiteList($package);
			//首发时间
			if(isset($params['form_type']) && $params['form_type'] == "debut"){
				$where = array(
					'package'=>array('in',$package)
				);
				$debut_arr = get_table_data($where,"sj_soft_debut","package","package,debut_time");
			}
			//白名单数据
			if((isset($params['form_type']) && $params['form_type'] == "sdk_test")||$record_type==2){
				$where = array(
					'package'=>array('in',$package),
					'status'=>1
				);
				$soft_whitelist = get_table_data($where,"sj_soft_whitelist","package","package,is_sdk,is_accept_account,is_accept_sdk,is_online");
			}
			//屏蔽北京
			$bj_shield_list = get_table_data(array('is_shield' => 1,'package' => array('in',$package)),"sj_soft_bj_shield","package","package,is_shield");
		}
		$where_sign = array(
		        'package'=>array('in',$package),
		        'status'=> 1
		);
		$soft_sign = get_table_data($where_sign,"sj_soft_sign","package","package,sign");
		foreach($file_icon_arr as $v){
			$next_sign[$v['apk_name']] = $v['sign'];
		}
		foreach($soft_tmp as $k => $v){
		    //签名
		    if($soft_sign && $soft_sign[$v['package']] && $soft_sign[$v['package']]['sign'] != $file_sign[$v['package']] && $file_sign[$v['package']]){
				$softlist[$k]['changeSign'] = 1 ;		
			}else{
			    if($next_sign[$v['package']] && $file_sign[$v['package']] && $file_sign[$v['package']] != $next_sign[$v['package']]){
			        $softlist[$k]['changeSign'] = 1 ;
			    }else{
			        $softlist[$k]['changeSign'] = 0 ;
			    }				
			}
			$softlist[$k]['nextSign'] = $next_sign[$v['package']];			
			$softlist[$k]['fileSign'] = $file_sign[$v['package']] ;
			$softlist[$k]['softSign'] = $soft_sign[$v['package']]['sign'];
			//sdk接入
			if($record_type == 2){
				if(isset($soft_whitelist[$v['package']]) && $soft_whitelist[$v['package']]['is_sdk'] == 1){
					$softlist[$k]['sdk_status'] = 1;
				}
			}
			if(isset($params['form_type']) && $params['form_type'] == "sdk_test"){
				$softlist[$k]['sdk_type'] = $soft_whitelist[$v['package']]?$soft_whitelist[$v['package']]['is_online']:'';	
				$softlist[$k]['is_accept_account'] = $soft_whitelist[$v['package']]['is_accept_account'];	
				$softlist[$k]['is_accept_sdk'] = $soft_whitelist[$v['package']]['is_accept_sdk'];	
			}
            if(in_array($record_type, array(1,2,3)) && $v['ip']){
            		$softlist[$k]['ip_num'] = $ip_num[$v['ip']];
            }		
			//图标		    
 			if($record_type == 2 || $record_type == 4 || $status == 3){
				if($file_tmp_icon[$v['id']]['iconurl_72']){
					$softlist[$k]['iconurl'] = $file_tmp_icon[$v['id']]['iconurl_72'];	
					$softlist[$k]['fileid_t'] = $file_tmp_icon[$v['id']]['id'];		
				}else{
					$softlist[$k]['iconurl'] = $file_icon_arr[$v['softid']]['iconurl_72'];
					$softlist[$k]['fileid'] = $file_icon_arr[$v['softid']]['id'];	
				}	
			}else{		
				$softlist[$k]['iconurl'] = $file_tmp_icon[$v['id']]['iconurl_72'];
				$softlist[$k]['leafletname'] = $file_tmp_icon[$v['id']]['leafletname'];
				$softlist[$k]['fileid_t'] = $file_tmp_icon[$v['id']]['id'];		
			}
			//sdk 
			$softlist[$k]['anzhi_sdk'] = $file_tmp_icon[$v['id']]['anzhi_sdk'];
			//广告
			$softlist[$k]['advertisement'] = $this->ad($file_tmp_icon[$v['id']]['ad_new']) ;
			$softlist[$k]['feiwo_sdk_v'] = $file_tmp_icon[$v['id']]['feiwo_sdk_v'];
			//判断是否有替包
			if($record_type == 2 && $this->getchangeapk($dev_all[$v['dev_id']]['dev_id']) && $file_tmp_icon[$v['id']]['iconurl_72']  ){ 			    
			    $softlist[$k]['iconurl'] = $file_tmp_icon[$v['id']]['iconurl_72'];
			}
		
			//积分、广告----推广商
			if($file_tmp_icon[$v['id']]['md5_adinfo']){
				$softlist[$k]['scan1'] =  $file_tmp_icon[$v['id']]['md5_adinfo'];	
			}else{
				$softlist[$k]['scan1'] = '';
			}
			//不安全信息
			if($v['safe'] > 1){
				if($file_tmp_icon[$v['id']]['sha1_adinfo']){
					$softlist[$k]['scan_result'] = $file_tmp_icon[$v['id']]['sha1_adinfo'];
				}else{
					$softlist[$k]['scan_result'] = '';
				}
			}
			if(!empty($v['category_id'])){
				$categoryid = substr("{$v['category_id']}",1,-1);
			}
			$softlist[$k]['category_name'] = $category_all[$categoryid]['name'];
			//app认证状态
			if($app_info[$softlist[$k]['id']]['status']!='0'){
			$softlist[$k]['appcert_status'] = $appcert_status[$app_info[$softlist[$k]['id']]['status']];
			}
			if($app_info[$softlist[$k]['id']]['status']==3){
			$msg = json_decode($app_info[$softlist[$k]['id']]['msg'],true);
			$softlist[$k]['certinfo'] = $msg['certinfo'];
			}else if($app_info[$softlist[$k]['id']]['status']==4){
			$softlist[$k]['certinfo'] = '提供的信息有误，无法进行认证工作';
			}
			//type 0公司 1个人 2团队
			$softlist[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
			$softlist[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
			$softlist[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
			$softlist[$k]['dev_id'] = $dev_all[$v['dev_id']]['dev_id'];
			$softlist[$k]['dev_status'] = $dev_all[$v['dev_id']]['status'];
			if(($record_type == 1 || $record_type == 2 || $record_type == 3) && $status = 2){
				if($file_tmp_icon[$v['id']]['iconurl']){
					$fid = $file_tmp_icon[$v['id']]['id'];
					$type = "temp";
				}else{
					$fid = $file_icon_arr[$v['softid']]['id'];
					$type = "online";
				}
				//如果排期中有值就不做比较
				if(empty($whiteList[$v['package']])){		
					//盗版风险
					if($file_tmp_icon[$v['id']]['apk_icon']){
						$md5icon = $fileicon_arr_t[$v['package']]['md5_icon'];
					}else{
						$md5icon = $fileicon_arr[$v['package']]['md5_icon'];
					}
					$softlist[$k]['Pirate'] = getPiracyWarning($v['softname'],$v['package'],$md5icon);		
				}		
			}
			//屏蔽
			if($v['shield_start'] <= time() && $v['shield_end'] >= time()){
				$softlist[$k]['shield'] = $v['shield'];
			}
			//白名单数据
			$softlist[$k]['package_adapter'] = $brush_adapter_list[$v['package']];
			$softlist[$k]['is_bj_shield'] = $bj_shield_list[$v['package']]['is_shield'];
			//软件包名高亮显示
			$softlist[$k]['package_highlight']  =  $package_highlight[$v['package']];
			//上次通过或驳回状态
			$softlist[$k]['pre_status'] = $v['pre_status'];
			if($v['pre_status'] == 3){
				$per_status = $this->table('sj_reject_log')->where("tmp_id={$v['id']}")->order('id desc')->find();
				$softlist[$k]['reason'] = $per_status['reason'];
				if($per_status['create_tm']){
					$softlist[$k]['create_tm'] = date("Y-m-d H:i:s",$per_status['create_tm']);
				}
			}else{
				if($record_type == 3){
					$per_status1 = $this->table('sj_soft_tmp')->where("package='{$v['package']}'and status !=2 and status!=0")->field('status,last_refresh,review_time,deny_msg')->order("id desc")->find();
					if(!empty($per_status1) && $per_status1['review_time']){
						$softlist[$k]['pkg_last_refresh'] = date("Y-m-d H:i:s",$per_status1['review_time']);
					}
				}elseif($record_type == 2){
					$per_status1 = $this->table('sj_soft_tmp')->where("softid='{$v['softid']}' and status !=2 and status!=0")->field('status,last_refresh,review_time,deny_msg')->order("id desc")->find();
					//echo $this->getlastsql();
					if(!empty($per_status1) && $per_status1['review_time'] ){
						$softlist[$k]['pkg_last_refresh'] = date("Y-m-d H:i:s",$per_status1['review_time']);
					}
				}
			} 
			$softlist[$k]['last_refresh'] = $v['last_refresh'] ? date("Y-m-d H:i:s",$v['last_refresh']) : '';
			if($status == 3 || $record_type == 4){
				if($v['review_time']){
					$softlist[$k]['review_time'] = date("Y-m-d H:i:s",$v['review_time']);
				}
				$softlist[$k]['deny_msg'] = $v['deny_msg'];
			} 
			//首发时间
			if($debut_arr[$v['package']]['debut_time']){
				$tm = strtotime(date("Y-m-d",time()));
				$date = date("Y-m-d H:i:s",$debut_arr[$v['package']]['debut_time']);
				if($debut_arr[$v['package']]['debut_time']-86400 == $tm){
					$debut_tm =  "<div style='width:70px;color:red;'>".$date."</div>";
				}else{
					$debut_tm =  "<div style='width:70px;'>".$date."</div>";
				}
				$softlist[$k]['debut_time'] =  $debut_tm;
			}
			//
		}
		//驳回原因
		if($record_type){
			$reason_type = 4;
		}else{
			//SDK驳回原因
			$reason_type = 11;
		}
		$reason_list = $this -> table("dev_reason") -> where(array("status" => 1,"reason_type" => $reason_type ))->order('pos asc,id desc')->select();
		foreach($reason_list as &$val){
		 	if($val['content2']){		 	    
		 	    $val['content2'] = explode('<br />', $val['content2']);	    
		 	}   
		}		
		return array($softlist,$total,$Page,$reason_list);
	}
	//广告配置
	public function ad_select() {
		include_once SERVER_ROOT . '/tools/functions.php';
		return get_addon_map();
	}
	//广告类型排序
	public function asort_ad_select(){
		if(!S('name_map')){
			$name_map = $this->ad_select();
			S('name_map',$name_map,3600);
		}else{
			$name_map = S('name_map');
		}
		$name_map2 = array(
			'33'=>$name_map[33],
			'70'=>$name_map[70],
			'71'=>$name_map[71],
			'72'=>$name_map[72],
			'73'=>$name_map[73],
			'74'=>$name_map[74],
			'75'=>$name_map[75],
			'77'=>$name_map[77],
		);		
		$name_map3 =  array(
			'59'=>$name_map[59],
			'19'=>$name_map[19],
			'25'=>$name_map[25],
			'10'=>$name_map[10],
		);
		foreach($name_map2 as $key=>$val){
			unset($name_map[$key]);
		}
		foreach($name_map3 as $key=>$val){
			unset($name_map[$key]);
		}
		$pinyin = array();
		foreach($name_map as $key => $v){
			$pinyin[$key] = Pinyin($v);
		}
		//拼音排序
		asort($pinyin);
		$name_map4 = array();
		foreach($pinyin as $k=>$v){
			$name_map4[$k] = $name_map[$k];
		}
		$all_map = array_merge($name_map4,$name_map2,$name_map3);
		$all_map_str = '';
		foreach($all_map as $k=>$v){
			$all_map_str .= '"'.$v.'",';
		}
		$all_map_str = substr($all_map_str,0,-1);
        $all_map_str .= ',"有广告标","无广告标","未打标"';
		return array($name_map4,$name_map2,$name_map3,$all_map_str);
	}
	//获取的广告
	public function ad($advers_code) {
		$name_map = $this->ad_select();
		$codes = explode(',', $advers_code);
		rsort($codes);
		$name = '';
		foreach ($codes as $code) {
			if (!empty($code) && isset($name_map[$code])) {
				if (strlen($name) > 0) {
					$name .= '、';
				}
				$name .= $name_map[$code];
			}
		}
		return $name;
	}
	
	public function getAds($fileid, $is_tmp = 0){
		$model = new Model();
		$where = array(
			'sfid' => array('in',$fileid),
			'is_tmp' => $is_tmp
		);
		$result = $model -> table('sj_soft_scan_result') -> where($where) -> select();
        $ad_types = array(
			1 => '通知栏广告',
			2 => '内嵌广告条',
			3 => '弹窗广告',
			4 => '积分墙广告',
			5 => '伪造短信广告',
			6 => '广告插件自动启动',
        );
        $ad_actions = array(
			1 => '获取您的手机号，并上传',
			2 => '获取手机通讯录，并上传',
			3 => '获取手机所在位置，并上传',
			4 => '获取手机安装软件内容，并上传',
			5=>'加载DEX',
			6=>'启动服务',
			7=>'获取IMEI信息',
			8=>'读取账户信息',
			9=>'设置自启动',
			10=>'唤醒',
        );
		foreach($result as $key => $val){
			$desc = $val['description'];
			$desc_arr = json_decode($desc,true);
			$ad_infos = '';
			if ($val['provider'] == 1){
				if($desc_arr['notifybar'] == 0 || $desc_arr['integralwall'] == 0){
					continue;
				}else{
					if($desc_arr['chabo'] == 1) {
						$ad_infos .= '插播广告</br>';
					}					
					if($desc_arr['notifybar'] == 1) {
						$ad_infos .= '通知栏广告</br>';
					}	
					if($desc_arr['integralwall'] == 1) {
						$ad_infos .= "积分墙广告<br/>";
					}
					$ad_infos .= "<来自于腾讯>";
				}
			}else if($val['provider'] == 4){

				if(empty($desc_arr['adinfo'])){
					continue;
				}else{
					foreach($desc_arr['adinfo'] as $ad){
                        foreach($ad['adtype'] as $adtype){
                            $ad_infos .= isset($ad_types[$adtype]) ? $ad_types[$adtype] : $adtype;
                            $ad_infos .='|';
                        }
                        $ad_infos .= "<br/>adname : ".$ad['adname']."<br/>";
                        foreach($ad['adaction'] as $adaction){
                            $ad_infos .= isset($ad_actions[$adaction]) ? $ad_actions[$adaction] : $adaction;
                            $ad_infos .='|';
                        }
						$ad_infos .= "<br/><来自于金山>";
					}
					
				}
			}
			$return[$val['sfid']] .= $ad_infos;
		}
		return $return;
	}
	
	public function getAdsByHash($hash){
		if(!$hash) return false;
		$model = new Model();
		$where = array(
			'hash' => array('in',$hash),
		);
		$result = $model -> table('sj_soft_scan_result') -> where($where) -> select();
        $ad_types = array(
			1 => '通知栏广告',
			2 => '内嵌广告条',
			3 => '弹窗广告',
			4 => '积分墙广告',
			5 => '伪造短信广告',
			6 => '广告插件自动启动',
        );
        $ad_actions = array(
			1 => '获取您的手机号，并上传',
			2 => '获取手机通讯录，并上传',
			3 => '获取手机所在位置，并上传',
			4 => '获取手机安装软件内容，并上传',
			5=>'加载DEX',
			6=>'启动服务',
			7=>'获取IMEI信息',
			8=>'读取账户信息',
			9=>'设置自启动',
			10=>'唤醒',
        );
		$return = $temp = array();
		foreach($result as $key => $val){
			$temp[$val['hash']. '_'. $val['provider']] = $val;
		}
		unset($result);
		foreach($temp as $key => $val){
			$ad_infos = '';
			$desc = $val['description'];
			$desc_arr = json_decode($desc,true);
			if ($val['provider'] == 1){
				if($desc_arr['chabo'] == 0 && $desc_arr['notifybar'] == 0 && $desc_arr['integralwall'] == 0 && $desc_arr['banner'] == 0 && $desc_arr['floatwindows'] == 0 && $desc_arr['boutiquerecommand'] == 0){
					continue;
				}else{
					if($desc_arr['chabo'] == 1) {
						$ad_infos .= '插播广告</br>';
					}	
					if($desc_arr['notifybar'] == 1) {
						$ad_infos .= '通知栏广告</br>';
					}	
					if($desc_arr['integralwall'] == 1) {
						$ad_infos .= "积分墙广告<br/>";

					}
					if($desc_arr['floatwindows'] == 1){
						$ad_infos .= "浮动窗广告<br/>";						
					}
					if($desc_arr['boutiquerecommand'] == 1){
						$ad_infos .= "精品推荐列表广告<br/>";					
					}
					if($desc_arr['pluginlist']){
						foreach($desc_arr['pluginlist'] as $plugin){
							$ad_infos .= $plugin['plugindesc']."<br/>";
						}
					}
					$ad_infos .= "<来自于腾讯><br/>";	
				}
				$ad_infos .= ($val['time_rep'] and $ad_infos) ? date("Y-m-d H:i:s",$val['time_rep'])."</br>" : '';
			}else if($val['provider'] == 4){
				if(empty($desc_arr['adinfo'])){
					continue;
				}else{
					foreach($desc_arr['adinfo'] as $ad){
                        foreach($ad['adtype'] as $adtype){
                            $ad_infos .= isset($ad_types[$adtype]) ? $ad_types[$adtype] : $adtype;
                            $ad_infos .='|';
                        }
                        $ad_infos .= "<br/>adname : ".$ad['adname']."<br/>";
                        foreach($ad['adaction'] as $adaction){
                            $ad_infos .= isset($ad_actions[$adaction]) ? $ad_actions[$adaction] : $adaction;
                            $ad_infos .='|';
                        }
						$ad_infos .= "<来自于金山><br/>";
					}
				}
				$ad_infos .= ($val['time_rep'] and $ad_infos) ? date("Y-m-d H:i:s",$val['time_rep'])."</br>" : '';
			} else if($val['provider'] == 2) {
				if(!empty($desc_arr) && !empty($desc_arr['leafletname'])){
					$ad_infos = "推广商：".$desc_arr['leafletname']."<br />广告类别：".$desc_arr['leafletaction']."";
					$ad_infos .= "<br/><来自安全管家><br/>";
				}
				$ad_infos .= ($val['time_rep'] and $ad_infos) ? date("Y-m-d H:i:s",$val['time_rep'])."</br>" : '';
			}else if ($val['provider'] == 5){
				if(!empty($desc_arr) && !empty($desc_arr['safe'])){
					$ad_infos = $desc_arr['safe'].'/'.$desc_arr['desc'];
					$ad_infos .= "<br/><来自360><br/>";
				}
			}
			$return[$val['hash']] .= $ad_infos;
		}
		return $return;
	}
	//安全扫描
	public function getbyhash($hash,$safe=null){
		if(!$hash) return false;
		if($safe == 3){
			$map1['safe'] = array("eq", 1);
			$map1['provider'] = 1;
		}elseif($safe == 1){
			$map1['safe'] = array("eq", 1);
		}elseif($safe == 0){
			$map1['safe'] = array("egt", 1);
		}else{
			$map1['safe'] = array("egt", 2);
		}
		$map1['hash'] = array("in",$hash); 
		
		$scan_result_db = M('soft_scan_result');
		$scan_result_list = $scan_result_db->where($map1)->select();
		$req_info = $scan_result_db->table("pu_config")->where(array("config_type"=>"js_code","status"=>1))->getField("configcontent");
		$req_code = json_decode($req_info,true);
		$des = '';
		$scan_result_hash = array();
		foreach ($scan_result_list as $val) {
			$des = $val['description'];
			//发出时间
			$tm = date("Y-m-d H:i:s",$val['time_req'])."<br/>";
			if ($val['provider'] == 1 && substr($val['description'], 0, 1) == '{') {
				$des = json_decode($val['description'], true);
				if(!isset($des["response"]["trojan"]["description"])) {
					$des = $tm.$des["virusdesc"];
				}else{
					$des = $tm.$des["response"]["trojan"]["description"];
				}
				$des .="<来自腾讯><br/>";
				if($val['safe'] == 1) $des = $tm.'通过安全检测<来自腾讯安全>';
			} elseif ($val['provider'] == 2 && substr($val['description'], 0, 1) == '{') {
				$r_des = json_decode($val['description'], true);
				$des = $tm.$r_des["des"];
				if (empty($des))
					$des = $tm.$r_des["app"]["des"];
				if (empty($des))
					$des = $tm.$r_des["res"];
				$des .= "<来自安全管家><br/>";
				if($val['safe'] == 1) $des = $tm.'通过安全检测<来自安全管家>';
			} elseif ($val['provider'] == 3 && substr($val['description'], 0, 1) == '{') {
				$r_des = json_decode($val['description'], true);
				$des = $tm.$r_des['ScanInfo']['responseInfo']['reason'];
				if(empty($des)){
					$des = $val['description'];
				}
				$des .= "<来自网秦><br/>";
				if($val['safe'] == 1) $des = $tm.'通过安全检测<来自网秦>';
			} elseif ($val['provider'] == 4 && substr($val['description'], 0, 1) == '{') {
				$r_des = json_decode($val['description'], true);
				if($r_des['virus_desc']){
					$des='';
					foreach($r_des['virus_desc'] as $kz=>$vz){
						$des.= $tm.$req_code[$vz];
					}
				}else{
					$des = $tm.$val['description'];
				}
				$adinfo = $r_des['adinfo'];
				$ad_types = array(
					5 => '伪造短信广告',
					6 => '广告插件自动启动',
				);
				foreach($adinfo as $info){
					$ad_type = $info['adtype'];
					foreach($ad_type as $type){
					  if(isset($ad_types[$type])){
						$des .= $ad_types[$type]."<br>";
					  }
					}
				}
				$des .= "<来自金山><br/>";
				if($val['safe'] == 1) $des = $tm.'通过安全检测<来自金山>';
			}elseif ($val['provider'] == 5 && substr($val['description'], 0, 1) == '{'){
				$r_des = json_decode($val['description'],true);
				$des = $tm.$r_des['safe'].'/'.$r_des['desc'];
				$des .= "<来自360><br/>";
				if($val['safe'] == 1) $des = $tm.'通过安全检测<来自360>';
			}
			$scan_result_hash[$val['hash']] .= $des . "<br>";
		}
		foreach($scan_result_hash as $hash => $info){
			if(empty($info)){
				$des = '';
				$scan_jinshan = $scan_result_db->field('sfid,provider,description,time_req')->where(array('sfid'=>$hash,'provider' => 4))->select();
				$pro_des = json_decode($scan_jinshan[0]['description'],true);
				$adinfo = $pro_des['adinfo'];
				$tm = date("Y-m-d H:i:s",$scan_jinshan[0]['time_req'])."<br/>";
				//$des .= $tm;
				$ad_types = array(
					5 => '伪造短信广告',
					6 => '广告插件自动启动',
				);
				foreach($adinfo as $info){
					$ad_type = $info['adtype'];
					foreach($ad_type as $type){
					  if(isset($ad_types[$type])){
						$des .= $ad_types[$type]."<br>";
					  }
					}
				}
				if($des){
					 $des .= $tm;
					 $des .= "<来自金山><br/>";
				}
				$scan_result_hash[$hash] = $des;					
			}
		}
		return $scan_result_hash;
	}
	//软件类别
	public function getCategoryArray(){
		$conf_list = $this->table('sj_category')->where("status=1 or status = 3")->field('category_id,name,orderid,parentid')->order('orderid')->select();
		$types = array();
		foreach($conf_list as $val){
			$category_id = $val['category_id'];
			$types[$category_id] = $val;
		}
		foreach($types as $key => $val){
			$parentid = $types[$key]['parentid'];
			$category_id = $val['category_id'];
			!isset($all_types[$parentid]) && $all_types[$parentid] = array();
			$all_types[$parentid][$category_id] = $val;
		}
		return $all_types;
	}
	public function return_category(){
	//	if(!S('cname')){	
			$cname = array();
			$catname = $this ->getCategoryArray();
			foreach($catname[0] as $n){
				$threecat = array();
				foreach($catname[$n['category_id']] as $v){
					foreach( $catname[$v['category_id']] as $m){
						$threecat[] = $m;
					}
				}
				$n['sub'] = $threecat;
				$cname[] = $n;			
			}
			// S('cname',$cname,300);
		// }else{
			// $cname = S('cname');
		// }	
		return $cname;
	}
	//获取电子书分类id
	public function get_book_categoryid(){
		if(!S('book_categoryid')){
			$book = $this -> return_category();
			$categoryid = array();
			foreach($book[3]['sub'] as $v){
				$categoryid[] = $v['category_id'];
			}
			S('book_categoryid',$categoryid,300);
			$book_categoryid = $categoryid;
		}else{
			$book_categoryid = S('book_categoryid');
		}
		return $book_categoryid;
	}
	//修改描述审核通过
	public function update_soft($id_arr){
		$dir = '/tmp/audit_check/';
		if(!file_exists($dir)) mkdir($dir);	
		$add_result = array();
		foreach ($id_arr as $v) {
			$add_result[$v] = array(
				'package' => '',
				'softid' => '',
			);
		}	
		$soft_tmp_db = M('soft_tmp');	
		$file_db = M('soft_file');	
		$fileicon_db = M('soft_fileicon');		
		$fileicon_tmp_db = M('soft_fileicon_tmp');	
		$thumb_db = M('soft_thumb');	
		$thumbgif_db = M('soft_thumbgif');	
		$bookright_tmp_db = M('soft_bookright_tmp');	
		$bookright_db = M('soft_bookright');	
		$note_db = M('soft_note');				
		$error = '';
		$msg_str = '';
		$now = time();	
		$id_count = count();
		foreach ($id_arr as $k => $v) {
		
			$add_file =  false;
			$add_thumb =  false;
			$add_thumbgif =  false;
			//文件锁开始
			$path = $dir.$v.".lock";
			if(file_exists($path)){
				$time = file_get_contents($path);
				if ($now - $time < 600) {
					$msg_str .= "请过十分钟后再操作\n"; 
					continue;
				}
			}
			file_put_contents($path, $now);
			//更新soft表数据
			$tmplist = $soft_tmp_db->where("status=2 and record_type=2 and id={$v}")->find();	
			//是否支持x86
			if(isset($_GET['x86'])){
				$this -> x86_check($_GET['x86'],$tmplist['abi'],$v);
				//检测abi兼容
				$data = array(
					'dev_id' => $tmplist['dev_id'],
					'package' => $tmplist['package'],
					'softid' =>$tmplist['softid'],
					'abi' =>$tmplist['abi'],
					'min_firmware' => $tmplist['min_firmware'],
					'max_firmware' => $tmplist['max_firmware']
				);
				$this->abi_check($data);
			}
			if($tmplist){	
				$save_soft = $this -> save_soft($tmplist);
				if($save_soft ==0){
					$msg_str .= "id为{$tmplist['softid']}soft表更新失败\n"; 
					//删除锁文件
					unlink($path);			
					continue;					
				}
				$add_result[$v]['softid'] = $tmplist['softid'];
				$add_result[$v]['package'] = $tmplist['package'];
			}else{
				$error .= "id为{$tmplist['softid']}的软件记录已失效\n"; 
				//删除锁文件
				unlink($path);			
				continue;
			}		
			$filetmp_list = $this->table('sj_soft_file_tmp')->where("tmp_id={$v} and package_status=1")->find();
			if($filetmp_list){
				//状态删除file原有的值
				$file_db-> where(array("softid"=>$tmplist['softid']))->save(array("package_status" => 0 ));
				//插入file表数据
				$add_file =  $this->add_file($filetmp_list,$v,$tmplist['softid'],'save');
				if($add_file == 0){
					unset($add_result[$v]);
					//失败返回上一步的操作
					$file_db-> where(array("softid"=>$tmplist['softid']))->save(array("package_status" => 1 ));
					$msg_str .= "id为{$tmplist['softid']}插入sj_soft_file表失败\n";			
					//删除锁文件
					unlink($path);
					continue;	
				}else{
					if($_GET['sign'] == 1 && $id_count == 1){
						if($file_list[$v]['sign']) $this->update_sign($file_list[$v]['apk_name'],$file_list[$v]['sign']);
					}
				}
				//替包时更新baidu_docid_map
				$this->table('baidu_docid_map')-> where("softid={$tmplist['softid']}") -> save(array('status'=>2));
			}
			//更新sj_icon表数据
			$icon_list  = $this -> table('sj_icon_tmp') ->where(array("tmpid"=>$v,"status"=>1))->find();
			if($icon_list){
				$data = array(
					'iconurl' => $icon_list['iconurl'],
					'iconurl_72' => $icon_list['iconurl_72'],
					'iconurl_96' => $icon_list['iconurl_96'],
					'iconurl_125' => $icon_list['iconurl_125'],
                    'iconurl_160' => $icon_list['iconurl_160'],
                    'iconurl_512' => $icon_list['iconurl_512'],
                    'iconurl_gif' => $icon_list['iconurl_gif'],
					'iconurl_gif_160' => $icon_list['iconurl_gif_160'],
					'apk_icon' => $icon_list['apk_icon'],
					'add_time' => $icon_list['add_time'],
					'update_time' => time(),
					'softid' => $tmplist['softid'],
				);
				
				$res = $this->table('sj_icon')-> where("softid='{$tmplist['softid']}' and status=1")->find();
				if($res){
					$ret = $this->table('sj_icon')-> where("softid='{$tmplist['softid']}' and status=1")->save($data);
					if(!$ret){
						unset($add_result[$v]);
						$this -> writelog("save_icon_error.log",$this->getlastsql());
						$msg_str .= "tmpid为{$v}操作失败，更新sj_icon表失败\n";
						//删除锁文件
						unlink($path);
						continue;
					}	
				}else{
					$data['update_time'] =  time();
					$data['package'] = $icon_list['package'];
					$data['status'] = 1;
					$ret = $this->table('sj_icon')->add($data);
					if(!$ret){
						unset($add_result[$v]);
						$this -> writelog("add_icon_error.log",$this->getlastsql());
						$msg_str .= "tmpid为{$v}操作失败，插入sj_icon表失败\n";
						//删除锁文件
						unlink($path);
						continue;
					}	
				}				
			}
			//修改soft_fileicon表中数据
			$fileiconlist = $fileicon_tmp_db->where("tmp_id={$v}")->find();
			if($fileiconlist){
				$fileicon =  $fileicon_db-> where(array("softid"=>$tmplist['softid']))->field('softid')->find();
				$map = array();
				$map['file_id'] = $add_file ? $add_file : $filetmp_list['file_id'];
				$map['apk_name'] = $fileiconlist['apk_name'];
				$map['apk_icon'] = $fileiconlist['apk_icon'];
				$map['md5_icon'] = $fileiconlist['md5_icon'];
				if($fileicon){
					$fileicon_save = $fileicon_db-> where(array("softid"=>$tmplist['softid']))->save($map);	
				}else{
					$map['softid'] = $tmplist['softid'];
					$map['package_status'] = 1;
					$map['create_time'] = $now;
					$fileicon_save = $fileicon_db->add($map);
				}
				if(!$fileicon_save){
					unset($add_result[$v]);
					$this -> writelog("save_fileicon_error.log",$fileicon_db->getlastsql());
					$msg_str .= "id为{$tmplist['softid']}更新soft_fileicon表失败\n";
					//失败返回上一步的操作
					$file_db-> where(array("softid"=>$tmplist['softid']))->save(array("package_status" => 1 ));				
					$this->table('sj_soft_tmp')-> where("id={$v}") -> save(array('status'=>2));		
					//删除锁文件
					unlink($path);
					continue;				
				}
			}
			//状态删除thumb表原有的值
			$thumb_db->where("status=1 and softid={$tmplist['softid']}")->save(array('status'=>0));	
			//插入thumb表数据
			$add_thumb = $this -> add_thumb($v,$tmplist['softid'],'save');
			if($add_thumb == 0){
				unset($add_result[$v]);
				$msg_str .= "id为{$tmplist['softid']}插入sj_soft_thumb表数据失败\n";
				//如果失败删除上一步的数据
				$file_db-> where(array("softid"=>$tmplist['softid']))->save(array("package_status" => 1 ));
				$thumb_db->where("status=1 and softid={$tmplist['softid']}")->save(array('status'=>0));
				//删除锁文件
				unlink($path);
				continue;
			}
			//状态删除thumbgif表原有的值
			$thumbgif_db->where("status=1 and softid={$tmplist['softid']}")->save(array('status'=>0));				
			//插入thumbgif表数据
			$add_thumbgif = $this -> add_thumbgif($v,$tmplist['softid'],'save');
			if($add_thumbgif == 0){
				unset($add_result[$v]);
				$msg_str .= "id为{$tmplist['softid']}插入sj_soft_thumbgif表数据失败\n";
				//如果失败删除上一步的数据
				$file_db-> where(array("softid"=>$tmplist['softid']))->save(array("package_status" => 1 ));
				$thumbgif_db->where("status=1 and softid={$tmplist['softid']}")->save(array('status'=>0));
				//删除锁文件
				unlink($path);
				continue;
			}
			//更新bookright表
			$bookright_tmp_list = $bookright_tmp_db ->where("status=1 and tmp_id={$v}")->find();
			$bookright = $bookright_db ->where(array("softid"=>$tmplist['softid'],'status'=>1))->find();	
			$data = array();
			$data['softid'] = $bookright_tmp_list['softid'];
			$data['identity_pic'] = $bookright_tmp_list['identity_pic'];
			$data['right_pic'] = $bookright_tmp_list['right_pic'];
			$data['business_pic'] = $bookright_tmp_list['business_pic'];
			$data['upload_tm'] = $bookright_tmp_list['upload_tm'];
			$data['status'] = $bookright_tmp_list['status'];
			if($bookright){
				$update_book = $bookright_db -> data($data)->where("softid={$tmplist['softid']}")->save();
				if(!$update_book){
					$this -> writelog("save_book_error.log",$bookright_db->getlastsql());
					$msg_str .= "id为{$tmplist['softid']}更新sj_soft_bookright表数据失败\n";
					//如果失败删除上一步的数据
					$this->table('sj_soft_tmp')-> where("id={$v}") -> save(array('status'=>2));						
					$file_db-> where(array("softid"=>$tmplist['softid']))->save(array("package_status" => 1 ));
					$thumb_db->where("status=1 and softid={$tmplist['softid']}")->save(array('status'=>0));
					//删除锁文件
					unlink($path);
					continue;					
				}
			}else{
				$add_book = $bookright_db -> data($data)->add();
				if(!$add_book){
					$this -> writelog("add_book_error.log",$bookright_db->getlastsql());
					$msg_str .= "id为{$tmplist['softid']}插入sj_soft_bookright表数据失败\n";
					//删除锁文件
					unlink($path);
					continue;				
				}
			}	
			$time = time();
			$soft_tmp_db -> where("id={$v}") -> save(array('status'=>1,'last_refresh' => $time,'review_time' => $time));
			//审核通过后自动去除软件的屏蔽标识
			$data = array(
				'package' => $tmplist['package'],
				'dev_id' => $tmplist['dev_id'],
			);
			$this -> update_shield($data);
			$this -> add_video($v,$tmplist['softid'],'save',1);
			$this -> add_video($v,$tmplist['softid'],'save',2);
			//发送提醒信息			
			$tm = date("Y-m-d",time());
			if($tmplist['dev_id'] != 0){
				static $emailmodel;
				if(!$emailmodel){
					$emailmodel = D("Dev.Sendemail");
				}
				static $config_txt;
				if(!$config_txt){
					$config_txt = C('_config_txt_');
				}
				$search   = array("softname", "tm");
				$replace    = array($tmplist['softname'], $tm);
				$msg = str_replace($search,$replace,$config_txt['soft_pass']);		
				$emailmodel -> dev_remind_add($tmplist['dev_id'],$msg);
				//发送邮件提醒
				$dever = $this-> table('pu_developer')->where("dev_id={$tmplist['dev_id']}")-> field('dev_id,email,dev_name') ->find();
				$subject = $config_txt['soft_subject'];		
				$search2   = array("devname", "softname", "tm","pkg");
				$replace2    = array($dever['dev_name'],$tmplist['softname'], $tm ,$tmplist['package']);	
				$email_cont = str_replace($search2,$replace2,$config_txt['soft_save_pass_txt']);	
				$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
			}	
			//如果是电子书通过
			$Tags = D('Sj.Tags');
			$book_categoryid = $this -> get_book_categoryid();			
			if(in_array(substr($tmplist['category_id'],1,-1),$book_categoryid)){
				$tags = $tmplist['tags'];
				$Tags -> del_dev_tag($tmplist['package']);
				$Tags -> add_package_tags($tmplist['package'],$tags);
			}else{
				$tag_list = $Tags -> get_tag($tmplist['package']);
				if($tag_list){
					$Tags -> save_tag_history($tmplist['package'],$tag_list[1],1);
					$Tags -> del_dev_tag($tmplist['package']);
					if(!empty($tag_list[1])){
						$custom_tags = $tag_list[1].",";
					}
					$Tags -> add_package_tags($tmplist['package'],$custom_tags.$tag_list[2].','.$tag_list[3]);				
				}
			}		
			
			//删除锁文件开始
			if(file_exists($path)){
				unlink($path);
			}			
		}	
		if($msg_str || $error){
			$this -> writelog("save_error.log",$error.$msg_str);
		}
		//修改sj_soft_status表
		foreach($add_result as $v){
			update_soft_status(array('soft_status'=>50,'update_tm'=>$time),$v['package']);
		}

		relevance_softname($tmplist['package'],array('softname'=>$tmplist['softname']));
		return array($error,$msg_str,$add_result);	
	}
	//更新soft表数据
	public function save_soft($tmplist){
		$time = time();
		$data = array();
		$data['softname'] = $tmplist['softname'];
		$data['category_id'] = $tmplist['category_id'];
		$data['intro'] = $tmplist['intro'];
		$data['version'] = $tmplist['version'];
		$data['update_msg'] = $tmplist['update_msg'];
		$data['deny_msg'] = $tmplist['deny_msg'];
		$data['tags'] = $tmplist['tags'];
		$data['safe'] = $tmplist['safe'];
		$data['abi'] = $tmplist['abi'];
		$data['hide_prev'] = 4;
		$data['update_content'] = $tmplist['update_content'];
		$data['platform'] = $tmplist['platform'];
		$data['language'] = $tmplist['language'];
		$data['last_refresh'] = $time;
		$data['review_time'] = $time;
		$update_soft = $this->table('sj_soft')-> where("softid='{$tmplist['softid']}' and status=1 and hide=1")->data($data)->save();
		if($update_soft){
			//审核通过后category_id 用最新的
			$this->table('sj_soft')-> where("package='{$tmplist['package']}'")->data(array('category_id'=>$tmplist['category_id']))->save();
			$this->sendMailWhilelist($tmplist['package'],$tmplist['softname']);					
			return 1;
		}else{
			$this -> writelog("save_soft_error.log",$this->getlastsql());
			return 0;
		}		
	}
	//驳回
	public function reject_do($id_arr,$msg,$from){
		$return = array();
		$package =  array();
		foreach ($id_arr as $v) {
			$return[$v] = 1;
			$package[$v] =  array(
				'package' => '',
			);
		}
		$tmp_soft = D('soft_tmp');
		$log = D('reject_log');
		$emailmodel = D("Dev.Sendemail");
		$config_txt = C('_config_txt_');
		$model = new Model();		
		$where = array();
		$where['id'] = array('in',$id_arr);
		$where['pass_status'] = 0;//只操作不是定时上架软件的数据
		$tmp = $tmp_soft->where($where)-> field('id,status,package,dev_id,dever_email,softname,dev_name,record_type,sdk_status,single_sdk') ->select();
		$time = time();
		foreach($tmp as $v){
			if($v['status'] == 2 && in_array($v['record_type'],array(1,2,3,4,8))){
				$data = array(
					"status"=>3,
					"pre_status"=>3,
					"deny_msg"=>$msg,
					"last_refresh"=>$time,
					"review_time"=>$time
				);
				if($from == 'sdk'){
					$data['sdk_send'] = 0;
					if($v['single_sdk']==2){
						$data['single_sdk'] = 3;	
					}
				}
				$result = $tmp_soft -> where("id={$v['id']}") -> save($data);
				if($result){
					$tm = date("Y-m-d",$time);
                    //从运营白名单直接添加的或已接入sdk后不改变软件sdk状态
                    //$is_sdk = $this->table('sj_soft_whitelist')->where(array('package'=>$v['package'],'is_sdk'=>1,'status'=>1))->find();
					$now_soft_status = $this->table('sj_soft_status')->where(array('package'=>$v['package'],'del'=>0))->field('sdk_status')->find();
					if($from == 'sdk'){
						$this->table('sj_sdk_info')->where("package='{$v['package']}'")->save(array('sdk_test_status'=>2,'up_tm'=>$time));	 
						if($now_soft_status['sdk_status']!=5&&$now_soft_status['sdk_status']!=7){
							update_soft_status(array('soft_status'=>21,'sdk_status' =>6,'update_tm'=>$time),$v['package']);
						}else{
							update_soft_status(array('soft_status'=>21,'update_tm'=>$time),$v['package']);
						}

						//发送提醒信息			
						if($v['dev_id'] != 0){
							$search   = array("softname", "tm",'msg');
							$replace    = array($v['softname'], $tm, $msg);
							$msgs = str_replace($search,$replace,$config_txt['sdk_test_reject']);	
							$emailmodel -> dev_remind_add($v['dev_id'],$msgs);
							//发送邮件提醒
							$dever = $model-> table('pu_developer')->where("dev_id={$v['dev_id']}")-> field('dev_id,email,dev_name') ->find();
							$subject = $config_txt['sdk_test_subject'];		
							$search2   = array("devname", "softname", "tm", 'msg');
							$replace2    = array($v['dev_name'],$v['softname'], $tm, $msg);		
							$email_cont = str_replace($search2,$replace2,$config_txt['sdk_test_reject_txt']);	
							$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
						}						
					}else{
						//修改sj_soft_status表
						if($v['record_type'] == 1){
							$soft_status = 11; 
						}else if($v['record_type'] == 2){
							$soft_status = 12; 
						}else if($v['record_type'] == 3){
							$soft_status = 13; 
						}else if($v['record_type'] == 4){
							$soft_status = 14; 
						}

						if(($now_soft_status['sdk_status']!=5&&$now_soft_status['sdk_status']!=7)&&$v['sdk_status']!=0){
							update_soft_status(array('soft_status'=>$soft_status,'sdk_status'=>'9','update_tm'=>$time),$v['package']);
						}else{
							update_soft_status(array('soft_status'=>$soft_status,'update_tm'=>$time),$v['package']);	
						}

						//发送提醒信息			
						if($v['dev_id'] != 0){
							$search   = array("softname", "tm",'msg');
							$replace    = array($v['softname'], $tm, $msg);
							$msgs = str_replace($search,$replace,$config_txt['soft_reject']);	
							$emailmodel -> dev_remind_add($v['dev_id'],$msgs);
							//发送邮件提醒
							$dever = $model-> table('pu_developer')->where("dev_id={$v['dev_id']}")-> field('dev_id,email,dev_name') ->find();
							$subject = $config_txt['soft_subject'];		
							$search2   = array("devname", "softname", "tm", 'msg',"pkg");
							$replace2  = array($v['dev_name'],$v['softname'], $tm, $msg,$v['package']);		
							$email_cont = str_replace($search2,$replace2,$config_txt['soft_reject_txt']);	
							$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
						}
					}

					$package[$v['id']]['package'] = $v['package'];
					$map = array();
					$map['tmp_id'] = $v['id'];
					$map['reason'] = $msg;
					$map['create_tm'] = $time;
					$map['adminid'] = $_SESSION['admin']['admin_id'];
					$add_log = $log-> add($map);
					if(!$add_log){
						$return[$v['id']] = 3;//驳回内容插入失败
					}
				}else{
					$return[$v['id']] = 2;//软件驳回失败
				}
			}else{
				$return[$v['id']] = 0;
			}
		}
		return array($return,$package);
	}
	//新软件--版本升级审核通过
	public function update_tmp($id_arr,$record_type,$is_shelves=0){
		$dir = '/tmp/audit_check/';
		if(!file_exists($dir)) mkdir($dir);
		$add_result = $push_data = array();
		foreach ($id_arr as $k => $v) {						
			$add_result[$v] = array(
				'softid' => '',
				'package' => '',
				'update_from' => '',
				'pass_status' => '',
			);
		}
		$error = '';
		$msg_str = '';
		$now = time();
		// $uniqid = uniqid();
		// $uniqid =  $_SESSION['admin']['admin_id'].'-'.__ACTION__ .'-'. __URL__ ;
		$where = array(
			'id' => array('in',$id_arr),
			'status' => 2,
		);
		$tmplist = get_table_data($where,"sj_soft_tmp","id","*");	
		$where = array(
			'tmp_id' => array('in',$id_arr),
			'package_status' => 1
		);	
		$file_list = get_table_data($where,"sj_soft_file_tmp","tmp_id","*");	
		$fileicon_list  = get_table_data($where,"sj_soft_fileicon_tmp","tmp_id","*");	
		$where = array(
			'tmpid' => array('in',$id_arr),
			'status' => 1,
		);
		$icon_list  = get_table_data($where,"sj_icon_tmp","tmpid","*");
		$id_count = count($id_arr);
		foreach ($id_arr as $k => $v) {
			$add_soft =  false;
			$add_file =  false;
			$add_thumb =  false;
			$add_thumbgif =  false;
			$add_book =  false;
			$package = false;
			//文件锁开始
			$path = $dir.$v.".lock";	
			if(file_exists($path)){
				$time = file_get_contents($path);
				if ($now - $time < 600) {
					$msg_str .= "请过十分钟后再操作\n"; 
					continue;
				}
			}
			file_put_contents($path, $now);
			
			//$this->__useage($uniqid, __LINE__);
			if(!$file_list[$v]){
				$error .= "tmpid为{$v}文件不存在\n"; 
				//删除锁文件
				unlink($path);			
				continue;
			}
			//本地不做文件验证
			if($_SERVER['SERVER_ADDR']!='127.0.0.1' && $_SERVER['SERVER_ADDR']!='10.0.3.15' ) {
				$file_path = UPLOAD_PATH.$file_list[$v]['url'];
				if (
					!file_exists($file_path)
					|| ($file_list[$v]['filesize'] != filesize($file_path))
					|| ($file_list[$v]['md5_file'] != md5_file($file_path))
				) {
					$error .= "tmpid为{$v}apk文件异常\n"; 
					$upload_filesize = filesize($file_path);
					$upload_md5file = md5_file($file_path);
					$msg = "{$file_list[$v]['id']} {$file_path} {$file_list[$v]['filesize']}-{$upload_filesize} {$file_list[$v]['md5_file']}-{$upload_md5file}";
					$this -> writelog("add_file_error.log", $msg);
					//删除锁文件
					unlink($path);
					continue;
				}
			}
			//$this->__useage($uniqid, __LINE__);
			if(!$tmplist[$v]){
				$error .= "tmpid为{$v}不在审核状态\n"; 
				//删除锁文件
				unlink($path);			
				continue;
			}
			//判断是否有白名单的上线软件有就回写
			$whitelist = $this -> table('sj_soft_whitelist')->where(array('package' => $tmplist[$v]['package'],'status'=>1))->field('is_time_shelves,is_sdk,is_online,fen_lei')->find();
			//从运营白名单直接添加的或已接入sdk后不改变软件sdk状态
			$now_soft_status = $this->table('sj_soft_status')->where(array('package'=>$file_list[$v]['apk_name'],'del'=>0))->field('sdk_status')->find();
			//判断是否SDK通过的软件
			if($record_type == 'sdk'){
				if($whitelist['fen_lei']=='网游'){
					$sdk_sign = $this->check_sdk_sign($file_list[$v],$now_soft_status);
					if(!$sdk_sign){
						$msg_str .= "tmpid为{$v}的正在二次签名中，请5分钟后再试\n";
						continue;
					}
				}
				

				if($tmplist[$v]['single_sdk']==2){
					//单机sdk测试状态
					$save_data = array('single_sdk'=>1,'last_refresh'=>time());
				}else{
					$save_data = array('sdk_status'=>1,'last_refresh'=>time());
				}
				$res = $this->table('sj_soft_tmp')->where("status=2 and package='{$file_list[$v]['apk_name']}' and sdk_status in(2,3) and id='{$v}'")->save($save_data);
				if($res) {
					
					if($tmplist[$v]['single_sdk']!=2){
						$is_new_soft = $this->table('yx_product')->where(array('package'=>$file_list[$v]['apk_name'],'del'=>0))->field('is_new_soft')->find();
						$soft = $this->table('sj_soft')->where(array('package'=>$file_list[$v]['apk_name'],'hide'=>1,'status'=>1))->find();
						if($is_new_soft['is_new_soft']==1||$soft){
							$soft_status = 33;
						}else{
							$soft_status = 31;
						}
						
						if($soft_status && ($now_soft_status['sdk_status'] == 5||$now_soft_status['sdk_status'] == 7)){
							update_soft_status(array('soft_status'=>$soft_status,'update_tm'=>time()),$file_list[$v]['apk_name']);
						}else{																			
							$this->table('yx_product')->where("package='{$file_list[$v]['apk_name']}'")->save(array('step'=>4));
							update_soft_status(array('sdk_status'=>4,'soft_status'=>$soft_status,'update_tm'=>time()),$file_list[$v]['apk_name']);
						}

						$add_result[$v]['package'] = $tmplist[$v]['package'];
						$add_result[$v]['sdk_status'] = 1;
						$send_mail = array(
							'dev_id' => $tmplist[$v]['dev_id'],
							'softname' => $tmplist[$v]['softname'],
						);
						//sdk测试通过提醒
						if($tmplist[$v]['record_type'] == 8){
							//$this -> sdk_test_pass_mail($send_mail);
						}
					}else{
						if($now_soft_status['sdk_status'] != 5&&$now_soft_status['sdk_status'] != 7){
							$this->table('yx_product')->where("package='{$file_list[$v]['apk_name']}'")->save(array('step'=>3));
						}
						
					}
                    $this->table('sj_sdk_info')->where("package='{$file_list[$v]['apk_name']}'")->save(array('sdk_test_status'=>1,'up_tm'=>time()));

					//SDK审核通过处理渠道游戏
					$sdkchannel = D('sendNum.SdkChannel');
					$need_c = $sdkchannel->is_need_channel_update($file_list[$v]['apk_name'],$tmplist[$v]['version_code']);
					if($need_c){
						$s_data = array(
							'tmp_id' =>$v,
							'file_tmp_id' =>$file_list[$v]['id'],
							'softname' =>$tmplist[$v]['softname'],
							'package' =>$file_list[$v]['apk_name'],
							'url' =>$file_list[$v]['url'],
							'version_code' =>$tmplist[$v]['version_code'],
							'version' =>$tmplist[$v]['version'],
							'sdk_version' => $tmplist[$v]['sdk_version']
						);
						$sdkchannel->save_sdk_channel_game_tmp($s_data);
					}
				}	
				//删除锁文件
				unlink($path);
				continue;
			}
			//CP审核通过后，会自动加入运营白名单
			$data = array(
				 'dev_id' => $tmplist[$v]['dev_id'],
				 'softname' => $tmplist[$v]['softname'],
				 'package' => $tmplist[$v]['package'],
				 'dev_name'=>$tmplist[$v]['dev_name'],
			);				
			$add_white = $this -> sdk_add_soft_whitelist($data,$whitelist,$record_type,$tmplist[$v]['package']);
			if($add_white == 0){
				$msg_str .= "包名为{$tmplist[$v]['package']}操作sj_soft_whitelist表数据失败\n";
			}
			$push_data[$v]['package'] = $tmplist[$v]['package'];
			$push_data[$v]['softname'] = $tmplist[$v]['softname'];
			$push_data[$v]['version'] = $tmplist[$v]['version'];
			$push_data[$v]['version_code'] = $tmplist[$v]['version_code'];
			$push_data[$v]['filesize'] = $file_list[$v]['filesize'];
			if(isset($icon_list[$v])&&!empty($icon_list[$v]['iconurl_125'])){
				$push_icon = $icon_list[$v]['iconurl_125'];
			}else{
				$push_icon = $file_list[$v]['iconurl_125'];
			}
			$push_data[$v]['iconurl'] = 'http://img3.anzhi.com'.$push_icon;
			$push_data[$v]['update_content'] = $tmplist[$v]['update_content'];
			//联运游戏上线同步到用户中心
			$isSdkGame = isSdkGame($tmplist[$v]['package']);
			if($isSdkGame){
				$appkey = getAppKey($tmplist[$v]['package']);
				//将新版本的分类同步到旧版本
				$this->sync_sdk_soft_category($tmplist[$v]['package'],$tmplist[$v]['category_id']);
			}
			//判断升级审核是否有定时上架的软件
			//if($record_type == 3){
				if($whitelist && $whitelist['is_time_shelves']==1 && !empty($tmplist[$v]['pass_time']) && $tmplist[$v]['pass_time'] > $now &&(!$is_shelves||($is_shelves==2&&!in_array($isSdkGame['fen_lei'],array('网游','棋牌'))))){
					$ret = $this->table('sj_soft_tmp')->where("status=2 and package='{$file_list[$v]['apk_name']}' and pass_status =0")->save(array('pass_status'=>1,'last_refresh'=>time()));
					update_soft_status(array('soft_status'=>40,'update_tm'=>time()),$file_list[$v]['apk_name']);
					$add_result[$v]['package'] = $tmplist[$v]['package'];
					$add_result[$v]['pass_time'] = $tmplist[$v]['pass_time'];
					$add_result[$v]['update_from'] = $tmplist[$v]['update_from'];
					//推送
//					$push_data[$v]['start_time'] = $tmplist[$v]['pass_time'];
//					$push_data[$v]['end_time'] = $tmplist[$v]['pass_time']+(86400*3);
//					$push_data[$v]['review_time'] = $tmplist[$v]['pass_time'];
//					push_soft_update_msg($push_data[$v]);
					if($ret) $add_result[$v]['pass_status'] = 1;
					//删除锁文件
					unlink($path);
					//联运游戏上线同步到用户中心
					if($isSdkGame&&$appkey&&$now_soft_status['sdk_status'] != 5&&$now_soft_status['sdk_status'] != 7){
						$msg_str .= $this->relevance_to_ucenter($isSdkGame,$add_result[$v]['package'],$tmplist[$v]['dev_id'],$tmplist[$v]['softname'],date("Y-m-d H:i:s",$tmplist[$v]['pass_time']));
					}
					continue;
				}
			//}
//$this->__useage($uniqid, __LINE__);
	
			////插入soft表的数据
			$preceding_softlis = $this->table('sj_soft') -> where("softid={$tmplist[$v]['update_from']}")->find();
			list($add_soft,$package) = $this -> add_soft($v,$tmplist[$v],$preceding_softlis);
			if($add_soft == 0){
				$error .= "tmpid为{$v}的软件记录已失效\n"; 
				//删除锁文件
				unlink($path);			
				continue;
			}else if($add_soft == 1){
				$msg_str .= "tmpid为{$v}soft_tmp表更新状态失败\n"; 
				//删除锁文件
				unlink($path);			
				continue;
			}else if($add_soft ==2 ){
				$msg_str .= "tmpid为{$v}插入soft表出错\n";
				//删除锁文件
				unlink($path);			
				continue;
			}else{
				 $add_result[$v]['softid'] = $add_soft;
				 $add_result[$v]['package'] = $package;
			}
//$this->__useage($uniqid, __LINE__);
			//是否支持x86
			if(isset($_GET['x86'])){
				$this -> x86_check($_GET['x86'],$tmplist[$v]['abi'],$v,$add_soft,"sj_soft_tmp",$tmplist[$v]['package'],$tmplist[$v]['softname']);
			}
			//插入file表数据
			$add_file =  $this->add_file($file_list[$v],$v,$add_soft,'',$preceding_softlis['total_downloaded']);
			if($add_file == 0){
				unset($add_result[$v]);
				$msg_str .= "tmpid为{$v}插入sj_soft_file表失败\n";
				//删除锁文件
				unlink($path);
				continue;	
			}
			if($_GET['sign'] == 1 && $id_count == 1){
				if($file_list[$v]['sign']) $this->update_sign($file_list[$v]['apk_name'],$file_list[$v]['sign']);
			}
//$this->__useage($uniqid, __LINE__);
			//插入fileicon表数据
			$add_fileicon = $this ->add_fileicon($v,$add_file,$add_soft,$fileicon_list[$v]);	
			if($add_fileicon == 0){
				unset($add_result[$v]);
				$msg_str .= "tmpid为{$v}操作失败，插入sj_soft_fileicon表失败\n";
				//删除锁文件
				unlink($path);
				continue;
			}
			//插入sj_icon表数据
			$add_newicon = $this -> add_newicon($v,$add_soft,$icon_list[$v]);
			if($add_newicon == 0){
				unset($add_result[$v]);
				$msg_str .= "tmpid为{$v}操作失败，插入sj_icon表失败\n";
				//删除锁文件
				unlink($path);
				continue;
			}
//$this->__useage($uniqid, __LINE__);
			//插入thumb表数据
			$add_thumb = $this -> add_thumb($v,$add_soft);
			if($add_thumb == 0){
				unset($add_result[$v]);
				$msg_str .= "tmpid为{$v}插入sj_soft_thumb表数据失败\n";
				//删除锁文件
				unlink($path);
				continue;
			}
			//插入thumbgif表数据
			$add_thumbgif = $this -> add_thumbgif($v,$add_soft);
			if($add_thumbgif == 0){
				unset($add_result[$v]);
				$msg_str .= "tmpid为{$v}插入sj_soft_thumbgif表数据失败\n";
				//删除锁文件
				unlink($path);
				continue;
			}
//$this->__useage($uniqid, __LINE__);
			//插入soft_bookright表数据
			$add_book = $this -> add_book($v,$add_soft);
			if($add_book == 0 ){
				unset($add_result[$v]);
				$msg_str .= "tmpid为{$v}插入sj_soft_bookright表数据失败\n";
				//删除锁文件
				unlink($path);			
				continue;
			}

			//插入视频表数据
			$add_video = $this->add_video($v,$add_soft,'',1);
			$add_video2 = $this->add_video($v,$add_soft,'',2);
			if($add_video == 0 ||$add_video2 == 0){
				unset($add_result[$v]);
				$msg_str .= "tmpid为{$v}插入sj_soft_extra表数据失败\n";
				//删除锁文件
				unlink($path);
				continue;
			}
//$this->__useage($uniqid, __LINE__);
			if($record_type == 1 || $record_type == 3 ){	
				$notelist = $this->table('sj_soft_note')-> where("package='{$add_result[$v]['package']}'")->find();
				$note_single_list = $this->table('sj_soft_note_single')->where("softid={$add_soft}")->find();
				$map = array();
				$map['note'] = '';
				$map['package'] = $add_result[$v]['package'];		
				//加入到soft_note表中
				if(!$notelist){
					$map['auth'] = 1;
					$this->table('sj_soft_note') -> add($map);
				}
				//加入到sj_soft_note_single表中
				if(!$note_single_list){
					$map['softid'] = $add_soft;
					$this->table('sj_soft_note_single') -> add($map);
				}
                                                                     
			}
			//发送提醒信息			
			$tm = date("Y-m-d",$now);
			if($tmplist[$v]['dev_id'] != 0){
				static $emailmodel;
				if(!$emailmodel){
					$emailmodel = D("Dev.Sendemail");
				}
				static $config_txt;
				if(!$config_txt){
					$config_txt = C('_config_txt_');
				}
				$search   = array("softname", "tm","down_url");
				$downurl = $whitelist ? "<br/>最新版本下载地址： http://m.anzhi.com/download.php?package=".$tmplist[$v]['package'] : '';
				$replace  = array($tmplist[$v]['softname'], $tm,$downurl );
				$msg = str_replace($search,$replace,$config_txt['soft_pass']);		
				//发送邮件提醒
				$subject = $config_txt['soft_subject'];		
				$pass_txt  = $config_txt['soft_pass_txt'];
				$emailmodel->dev_remind_add($tmplist[$v]['dev_id'],$msg);
				$dever = $this->table('pu_developer')->where("dev_id={$tmplist[$v]['dev_id']}")-> field('dev_id,email,dev_name') ->find();
				$search2   = array("devname", "softname", "tm","pkg","down_url");
				$down_url = $whitelist ? 'http://m.anzhi.com/download.php?package='.$tmplist[$v]['package'] : 'http://www.apk.anzhi.com' .$file_list[$v]['url'];
				$replace2    = array($dever['dev_name'],$tmplist[$v]['softname'], $tm ,$tmplist[$v]['package'],$down_url);	
				$email_cont = str_replace($search2,$replace2,$pass_txt);			
				//$dever['email'] = '1216063767@qq.com';	
				$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
				$new_push_package=array('com.ss.android.ugc.live','com.ss.android.essay.joke','com.ss.android.article.video','com.ss.android.article.news','com.ss.android.article.lite','com.ss.android.ugc.aweme');
				if(in_array($tmplist[$v]['package'], $new_push_package)){
					//发送邮件
					$subject_shangwu = "商务合作软件最新版本在安智市场上架邮件通知";
					$email_cont="软件名称为{$tmplist[$v]['softname']}，软件包名为".$tmplist[$v]['package']."，版本为{$tmplist[$v]['version']}，版本号为{$tmplist[$v]['version_code']}的软件在安智市场上架";
					$receivers=array(array('email'=>'dingtingting@anzhi.com','name'=>'丁婷婷'),array('email'=>'liushen@anzhi.com','name'=>'刘珅'),array('email'=>'chenyanlong@anzhi.com','name'=>'陈延龙'));
					foreach($receivers as $receiver){
						$emailmodel ->realsend($receiver['email'],$receiver['name'],$subject_shangwu,$email_cont);
					}
				}
			}		
			
			if($record_type == 3){
				//sdk渠道游戏升级插入
				$sdkchannel = D('sendNum.SdkChannel');
				$sdkchannel->save_channel_game_sdk($add_result,1);
				$add_result[$v]['update_from'] = $tmplist[$v]['update_from'];
				//如果是来自sdk的升级就前面的所对应的版本置成历史
				$map = array(
					'hide' => 0,
					'back_mtime' => $now,
				);
				if($tmplist[$v]['record_from'] ==1){
					$this->table('sj_soft') -> where("package='{$preceding_softlis['package']}' and hide=1 and status=1 and softid != {$add_soft}")->save($map);
				}else{
					//检测abi兼容
					$data = array(
						'dev_id' => $tmplist[$v]['dev_id'],
						'package' => $tmplist[$v]['package'],
						'softid' => $add_soft,
						'abi' =>$tmplist[$v]['abi'],
						'min_firmware' => $tmplist[$v]['min_firmware'],
						'max_firmware' => $tmplist[$v]['max_firmware']
					);				
					$this->abi_check($data);
					//审核通过后将后台采集的相同版本设置成历史
					$this->table('sj_soft') -> where("package='{$tmplist[$v]['package']}' and hide=1 and status=1 and update_type=3 and version_code <= '{$tmplist[$v]['version_code']}'")->save($map);
					//审核通过后category_id 用最新的
					$this->table('sj_soft') -> where("package='{$tmplist[$v]['package']}'")->save(array('category_id'=>$tmplist[$v]['category_id']));
				}          
			}
			//如果是电子书通过
			$Tags = D('Sj.Tags');
			$book_categoryid = $this -> get_book_categoryid();			
			if(in_array(substr($tmplist[$v]['category_id'],1,-1),$book_categoryid)){
				$tags = $tmplist[$v]['tags'];
				$Tags -> del_dev_tag($tmplist[$v]['package']);
				$Tags -> add_package_tags($tmplist[$v]['package'],$tags);
			}else{
				$tag_list = $Tags -> get_tag($tmplist[$v]['package']);
				if($tag_list){
					$Tags -> save_tag_history($tmplist[$v]['package'],$tag_list[1],1);
					$Tags -> del_dev_tag($tmplist[$v]['package']);
					if(!empty($tag_list[1])){
						$custom_tags = $tag_list[1].",";
					}
					$Tags -> add_package_tags($tmplist[$v]['package'],$custom_tags.$tag_list[2].','.$tag_list[3]);	
				}
			}				
//$this->__useage($uniqid, __LINE__);
			$option = array(
				'soft_status'=>50,
				'update_tm'=>$now,
				'version' => $tmplist[$v]['version'],
				'softid' => $add_soft,
			);
			if($tmplist[$v]['sdk_status']==1){
				$this->table('yx_product')->where("package='{$add_result[$v]['package']}'")->save(array('step'=>5)); 
				//修改sj_soft_status表  	
				$option['sdk_status'] = 5;	 
			}
			update_soft_status($option,$tmplist[$v]['package']);
			//审核通过后自动去除软件的屏蔽标识
			$data = array(
				'package' => $add_result[$v]['package'],
				'dev_id' => $tmplist[$v]['dev_id'],
			);
			$this -> update_shield($data);
			//删除锁文件开始
			if(file_exists($path)){
				unlink($path);
			}
			//推送
			$push_data[$v]['softid'] = $add_soft;
			$push_data[$v]['end_time'] =  time()+(86400*3);
			$push_data[$v]['review_time'] = time();
			//push_soft_update_msg($push_data[$v]);
			
			//联运游戏上线同步到用户中心
			if($isSdkGame&&$appkey&&$now_soft_status['sdk_status'] != 5&&$now_soft_status['sdk_status'] != 7){
				$msg_str .= $this->relevance_to_ucenter($isSdkGame,$add_result[$v]['package'],$tmplist[$v]['dev_id'],$tmplist[$v]['softname']);
			}

			//V6.4游戏预约上线操作
			if($isSdkGame){
				$this->pro_activity_soft($add_result[$v]['package'],$tmplist[$v]);
			}

		}	
		
		if($error || $msg_str){
			$this -> writelog("soft_error.log",$error."\n".$msg_str);
		}

		return array($error,$msg_str,$add_result);		
	}

	public function  pro_activity_soft($package,$info){
		$model = M('');
		$product = $model->table('yx_product')->where(array('package'=>$package,'del'=>0,'type' => array('exp',' > 0')))->field('soft_id')->find();
		if($product){
			$activity_soft = $model->table('sj_activity_page')->where(array('get_lottery_type'=>$product['soft_id']))->field('ap_id')->find();
			if($activity_soft){
				$model->table('sj_actives_soft')->where(array('status'=>1,'page_id'=>$activity_soft['ap_id'],'package'=>array('neq','cn.goapk.market')))->save(array('status'=>0));
			$info['category_id'] = substr($info['category_id'],1,-1);
			$time = time();
			$model->query("insert into `sj_actives_soft` (`category_id`, `soft_name`, `package`, `rank`, `recomment`, `award_recomment`, `create_tm`, `update_tm`, `status`, `page_id`) values('{$info['category_id']}','{$info['softname']}','{$package}','2','','','{$time}','{$time}','1',{$activity_soft['ap_id']})");

			}
		}
	}

	private function relevance_to_ucenter($isSdkGame,$package,$dev_id,$softname,$onlineTimeStr=''){
		$appkey = getAppKey($package,1);
		$contractAppName = getAppContractName($package);
		$vals = array(
			'appKey' => $appkey['app_id'],
			'appSecret' =>$appkey['app_secret'],
			'dev_id'=>$dev_id,
			'softname'=>$softname,
			'contractAppName'=>$contractAppName['contract_name']?$contractAppName['contract_name']:$contractAppName['softname'],
			'status'=>1,
			'pay_url'=>$appkey['pay_url'],
			'usernotice_url'=>$appkey['usernotice_url'],
			'packageName'=>$package,
			'p_fenlei'=>$isSdkGame['fen_lei'],
			'isOnline' => 1,
			'isJoinUcenter'=>$isSdkGame['is_accept_account']
		);
		if(!empty($onlineTimeStr)){
			$vals['onlineTimeStr'] = $onlineTimeStr;
		}else{
			$vals['onlineTimeStr'] = date('Y-m-d H:i:s');
		}
		$res = json_decode(modifyAppNew($vals), true);
		if(!$res['code']=='success'){
			$msg_str = "上线同步到用户中心失败";
		}else{
			$msg_str = "";
		}
		return $msg_str;
	}
	public function add_newicon($tmpid,$softid,$icon_list){
		if($icon_list){
			//删除icon表原有的数据
			$this->table('sj_icon') ->where(array("softid"=>$softid))->save(array('status'=>0));
			$map = array();
			$map['softid'] =  $softid;
			$map['package'] =  $icon_list['package'];
			$map['iconurl'] =  $icon_list['iconurl'];
			$map['iconurl_72'] =  $icon_list['iconurl_72'];
			$map['iconurl_96'] =  $icon_list['iconurl_96'];
			$map['iconurl_125'] =  $icon_list['iconurl_125'];
			$map['iconurl_160'] =  $icon_list['iconurl_160'];
			$map['iconurl_512'] =  $icon_list['iconurl_512'];
			$map['iconurl_gif'] =  $icon_list['iconurl_gif'];
			$map['iconurl_gif_160'] =  $icon_list['iconurl_gif_160'];			
			$map['apk_icon'] =  $icon_list['apk_icon'];
			$map['add_time'] =  $icon_list['add_time'];
			$map['update_time'] =  time();
			$map['status'] =  1;
			$res = $this->table('sj_icon') -> add($map);
			if(!$res){
				$this -> writelog("add_newicon_error.log",$this->getlastsql());
				//如果插入sj_soft_fileicon表失败删除上一步的数据
				$this -> rollback($softid,$tmpid);
				return 0;
			}else{
				//回写softid
				$this->table('sj_icon_tmp') -> where("tmpid={$tmpid}") -> save(array('softid' => $softid));			
				return $res;
			}
		}else{
			//如果没数据继续走下面的流程
			return 1;
		}
	}
	public function x86_check($status,$abi,$tmpid,$softid,$table,$package,$softname){
		if($status == 1){
			if ($abi != 0){
				if($table == "sj_soft_tmp"){
					$this->table($table) -> where("id={$tmpid}")->save(array('abi'=>$abi | ABI_X86));
				}else if($table == "sj_soft"){
					$this->table($table) -> where("softid={$softid}")->save(array('abi'=>$abi | ABI_X86 ,'last_refresh'=>time()));
				}
			}
		}else if($status == 2){
			if ($abi == 0 || ($abi & ABI_X86) == ABI_X86){
				if($abi == 0) $abi = 15;
				//按位取返~
				if($table == "sj_soft_tmp"){
					$this->table($table) -> where("id={$tmpid}")->save(array('abi'=>$abi & (~ABI_X86)));
				}else if($table == "sj_soft"){
					$this->table($table) -> where("softid={$softid}")->save(array('abi'=>$abi & (~ABI_X86),'last_refresh'=>time()));					
				}
			}
		}
		$map = array();
		$map['softid'] = $softid;
		$map['package'] = $package;
		$map['softname'] = $softname;
		$map['x86_status'] = $status;
		$map['add_tm'] = time();
		$this ->table('sj_soft_x86_log')->add($map);
	}	
	public function abi_check($data){
		$softlist = $this->table('sj_soft') -> where("dev_id='{$data['dev_id']}' and package='{$data['package']}' and status=1 and hide=1 and softid != '{$data['softid']}'")->field('abi,softid,min_firmware,max_firmware')->select();
        $this->writelog('abi.log', $data['package']. ' '. $this->getLastSql());
		$abi = intval($data['abi']);
        foreach($softlist as $v){
			$abilog = "{$data['package']} {$abi} {$v['abi']} {$v['softid']} \n";
			$abilog .= var_export(($abi == 0 || ($abi & $v['abi']) == $v['abi']), true)."\n";
			//$abilog .= "{$data['min_firmware']} {$v['min_firmware']} {$data['max_firmware']} {$v['max_firmware']} \n";
			//$abilog .= var_export(($data['min_firmware'] ==0 && $data['max_firmware'] ==0) || ($data['min_firmware'] <= $v['min_firmware']) && (($v['max_firmware'] >0 && $data['max_firmware'] >= $v['max_firmware']) || $data['max_firmware'] ==0),true);			
            $this->writelog('abi.log', $abilog);
			$int_abi = intval($v['abi']);
		/*	
			//最小固件大于7并如果abi兼容就把软件制成历史
			if($data['min_firmware'] > 7){
				if ($abi == 0 || ($abi & $int_abi) == $int_abi){
					if(				
					//如果新的固件值都为0就是兼容
						($data['min_firmware'] ==0 && $data['max_firmware'] ==0)
					//最小固件小于等于当前最小固件并最大固件大于等于当前最大固件或最大固件等于0	
						|| ($data['min_firmware'] <= $v['min_firmware']) && (($v['max_firmware'] >0 && $data['max_firmware'] >= $v['max_firmware']) || $data['max_firmware'] ==0)  
					){
						$ret = $this->table('sj_soft') -> where("softid={$v['softid']}")->save(array('hide'=>0));
						if(!$ret){
							$this->writelog('abi.log', "\n更新失败:".$this->getlastsql());
						}
					}
				}
			}else
		*/
			if ($abi == 0 || ($abi & $int_abi) == $int_abi){
				$map = array(
					'hide' => 0,
					'back_mtime' => $now,
				);
				$ret = $this->table('sj_soft') -> where("softid={$v['softid']}")->save($map);
				if(!$ret){
					$this->writelog('abi.log', "\n更新失败:".$this->getlastsql());
				}
			}
		}
	}
	//添加sj_soft 表数据
	public function add_soft($tmpid,$tmplist,$preceding_softlis){
//$uniqid =  $_SESSION['admin']['admin_id'].'-'.__ACTION__ .'-'. __URL__ ;
		static $emailmodel;
		if(!$emailmodel){
			$emailmodel = D("Dev.Sendemail");
		}
		//判断status是否为审核中状态
		if($tmplist){				
			$time = time();
			$map = array(
				'softname' => '',
				'package' => '',
				'dev_id' => '',
				'dev_name' => '',
				'category_id' => '',
				'costs' => '',
				'version' => '',
				'version_code' => '',
				'intro' => '',
				'update_msg' => '',
				'claim_status' => '',
				'tags' => '',
				'safe' => '',
				'deny_msg' => '',
				'only_search' => '',
				'abi' => '',
				'channel_id' => '',
				'type' => '',
				'hide_prev' => '2',
				'hide' => '1',
				'status' => '1',
				'update_type' => '',
				'update_from' => '',
				'upload_tm' => '',
				'update_content' => '',
				'platform' => '',
				'language' => '',
				'last_refresh' => $time,
				'last_publish' => $time,
				'first_time' => $time,
				'review_time' => $time,
				'min_firmware' => '',
				'max_firmware' => '',
				'is_force_update' =>''
			);
			$data = array();
			foreach ($map as $k => $v) {
				if ($v == '') {
					$data[$k] = !is_null($tmplist[$k]) ? $tmplist[$k] : '';
				} else {
					$data[$k] = $v;
				}
			}
			//下载量
			if($tmplist['update_from']){
				//升级通过 --更新hide_prev字段
				$data['hide_prev'] = 5;
				$data['msgnum'] = $preceding_softlis['msgnum'] ? $preceding_softlis['msgnum'] : 0;
				$data['score'] = $preceding_softlis['score'] ? $preceding_softlis['score'] : 0;
				$data['total_downloaded'] = $preceding_softlis['total_downloaded'] ?$preceding_softlis['total_downloaded'] : 0;
				$data['total_downloaded_add'] = $preceding_softlis['total_downloaded_add'] ? $preceding_softlis['total_downloaded_add'] :0;
				$data['total_downloaded_detain'] = $preceding_softlis['total_downloaded_detain'] ? $preceding_softlis['total_downloaded_detain'] : 0;
				if($preceding_softlis['total_downloaded'] >= 50000000){
					$emailmodel -> realsend('linhongqing@anzhi.com','linhongqing@anzhi.com','下载量大于5000万',"更新通过包名：".$tmplist['package']);
				}
			}else{ //新软件下载量默认值
				$data['total_downloaded_add'] = rand(8000,10000);
			}
			$add_soft =  $this->table('sj_soft')-> data($data)->add();
//$this->__useage($uniqid, __LINE__);
			//把softid存到add_result数组中
			if($add_soft){	
				$this->sendMailWhilelist($tmplist['package'],$tmplist['softname']);
				//更新soft_tmp表 status值
				$update_tmp = $this->table('sj_soft_tmp') -> where("id={$tmplist['id']}") -> save(array('status'=>1,'softid'=>$add_soft,'pre_status'=>2,'last_refresh' => $time,'review_time' => $time));
//$this->__useage($uniqid, __LINE__);				
				if(!$update_tmp){
					//soft_tmp表更新状态失败-- 删除soft表中的相关数据
					$del_soft =  $this->table('sj_soft')-> where("softid={$add_soft}")->delete();
					return 1;
				}
				//更新pu_developer字段statistics_on
				if($tmplist['record_type'] == 1 && $tmplist['dev_id'] != 0){
					$this -> update_developer($tmplist['dev_id']);
				}
//$this->__useage($uniqid, __LINE__);				
			}else{
				$this -> writelog("add_soft_error.log",$this->getlastsql());
				return 2;
			}
		}else{
			return 0;
		}
		return array($add_soft,$tmplist['package']);
	}
	//添加sj_soft_file 数据
	public function add_file($filetmplist,$tmpid,$softid,$from='',$total_downloaded){	
		$file_tmp_db = M('soft_file_tmp');
		$file_db = M('soft_file');
		$map = array(
			'package_status'=>'',
			'softid'=>$softid,
			'apk_name'=>'',
			'url'=>'',
			'filesize'=>'',
			'screen'=>'',
			'resolutionid'=>'',
			'min_firmware'=>'',
			'max_firmware'=>'',
			'iconurl'=>'',
			'iconurl_72'=>'',
			'iconurl_72_old'=>'',
			'iconurl_96'=>'',
			'iconurl_125'=>'',
			'upload_time'=>'',
			'safe'=>'',
			'abi'=>'',
			'last_refresh'=>'',
			'apk_icon'=>'',
			'md5_file'=>'',
			'sha1_file'=>'',
			'advertisement'=>'',
			'sign'=>'',
			'leaflet'=>'',
			'leafletname'=>'',
			'leafletcnt'=>'',
			'leafstatus'=>'',
			'file_status'=>'',
			'ad_new'=>'',
			'anzhi_sdk'=>'',
			'feiwo_sdk_v'=>'',
		);
		$data = array();
		foreach ($map as $k => $v) {
			if ($v == '') {
				$data[$k] = !is_null($filetmplist[$k]) ? $filetmplist[$k] : '';
			}else{
				$data[$k] = $v;
			}			
		}
		$add_file = $file_db -> data($data)->add();
		if($add_file){			
			//替包时修改权限表
			//成功后更新soft_scan_result和soft_permission表
			$soft_permission = M('soft_permission');			
			$soft_scan_result = M('soft_scan_result');
			$soft_uses_library = M('soft_uses_library');
			$soft_permission -> where("fileid={$filetmplist['tmp_id']} and is_tmp=1")->save(array('is_tmp'=>0,'fileid'=>$add_file));
			$soft_scan_result -> where("sfid={$filetmplist['tmp_id']} and is_tmp=1")->save(array('is_tmp'=>0,'sfid'=>$add_file));	
			$soft_uses_library -> where("fileid={$filetmplist['tmp_id']} and is_tmp=1")->save(array('is_tmp'=>0,'fileid'=>$add_file));			
			//@发增量更新
			$this -> send_incremental_update($softid,$filetmplist['apk_name'],$total_downloaded);
			if($from !='save'){
				//回写softid/file_id
				$map = array();
				$map['softid'] = $softid;
				$map['file_id'] = $add_file;
				$file_tmp_db -> where("tmp_id={$tmpid}") -> save($map);
			}
		}else{
			$this -> writelog("add_file_error.log",$file_db->getlastsql());
			if($from !='save'){
				//如果插入sj_soft_file表失败删除上一步的数据
				$this -> rollback($softid,$filetmplist['tmp_id'],$from);
			}
			return 0;
		}
		return $add_file;
	}
	//添加sj_fileicon 数据
	public function add_fileicon($tmpid,$fid,$softid,$fileicon_list){
		if($fileicon_list){
			$map = array(
				'file_id'=>$fid,
				'softid'=>$softid,
				'package_status'=>'',
				'apk_name'=>'',
				'apk_icon'=>'',
				'md5_icon'=>'',
				'create_time'=>'',
			);
			$data = array();
			foreach ($map as $k => $v) {
				if ($v == '') {
					$data[$k] = !is_null($fileicon_list[$k]) ? $fileicon_list[$k] : '';
				}else{
					$data[$k] = $v;
				}			
			}
			$add_fileicon = $this->table('sj_soft_fileicon')->add($data);
			if(!$add_fileicon){
				$this -> writelog("add_fileicon_error.log",$fileicon_db->getlastsql());
				//如果插入sj_soft_fileicon表失败删除上一步的数据
				$this -> rollback($softid,$tmpid);
				return 0;
			}
			return $add_fileicon;
		}else{
			//返回1继续走下面的流程
			return 1;
		}	
	}
	//添加视频数据
	public function add_video($tmpid,$softid,$from='',$video_num=1){
		$video_tmp_db = M('soft_extra_tmp');
		$video_db = M('soft_extra');
		$videotmplist = $video_tmp_db ->where(array("tmpid"=>$tmpid,'status'=>'1','video_num'=>$video_num))->find();
		if($videotmplist){
			//处理正式表修改成直接更新此包记录
			$video_online = $video_db->where(array('status'=>'1','video_num'=>$video_num,'package'=>$videotmplist['package']))->order('id desc')->find();
			$map = array();
			$map['video_title'] = $videotmplist['video_title'];
			$map['video_pic'] = $videotmplist['video_pic'];
			$map['video_url'] = $videotmplist['video_url'];
			$map['video_md5'] = $videotmplist['video_md5'];
			$map['video_filesize'] = $videotmplist['video_filesize'];
			$map['video_type'] = $videotmplist['video_type'];
			$map['video_pic_30'] = $videotmplist['video_pic_30'];
			$map['video_pic_60'] = $videotmplist['video_pic_60'];
			$map['screen_mode'] = $videotmplist['screen_mode'];
			$map['video_h263_url'] = $videotmplist['video_h263_url'];
			$map['update_tm'] = time();
			$map['softid'] = $softid;
			if(!$video_online){
				$map['package'] = $videotmplist['package'];
				$map['add_tm'] = time();
				$map['video_num'] = $video_num;
				$add_video = $video_db -> add($map);
			}else{
				$where = array('id'=>$video_online['id']);
				if($video_online['video_h263_url']!=$videotmplist['video_h263_url']){
					//视频路径更新需重新注入
					$map['inject_status'] = 0;
				}
				$add_video = $video_db ->where($where)-> save($map);
			}
			if(!$add_video){
				$this -> writelog("add_video_error.log",$video_db->getlastsql());
				//如果插入sj_soft_extra表失败删除上两步的数据
				$this -> rollback($softid,$tmpid,$from);
				return 0;
			}else{
				//回写softid
				$map2 =  array();
				$map2['softid'] = $softid;
				$map2['update_tm'] = time();
				$video_tmp_db -> where("tmpid={$tmpid}") -> save($map2);
			}
		}
		return 1;
	}
	//添加sj_soft_thumb表数据
	public function add_thumb($tmpid,$softid,$from=''){
		$thumb_tmp_db = M('soft_thumb_tmp');
		$thumb_db = M('soft_thumb');
		$thumbtmplist = $thumb_tmp_db ->where(array("tmp_id"=>$tmpid,'status'=>'1'))->select();		
		if($thumbtmplist){
			$thumbOnlinelist = $thumb_db ->where(array("softid"=>$softid,'status'=>'0'))->select();
			$thumbOnlineArr = array();
			foreach($thumbOnlinelist as $v){
				$thumbOnlineArr[$v['url']] = $v;
			}
			foreach($thumbtmplist as $val){
				$map = array();
				$map['softid'] = $softid;
				$map['status'] = $val['status'];
				$map['url'] = $val['url'];
				$map['url_60'] = $val['url_60'];
				$map['rank'] = $val['rank'];
				$map['upload_time'] = $val['upload_time'];
				$map['last_refresh'] = $val['last_refresh'];
				$map['image_raw'] = $val['image_raw'];
				$map['image_thumb'] = $val['image_thumb'];
				if($thumbOnlineArr[$val['url']]){
					$map['url_90_480'] = $thumbOnlineArr[$val['url']]['url_90_480'];
					$map['url_150_480'] = $thumbOnlineArr[$val['url']]['url_150_480'];
					$map['url_20_190'] = $thumbOnlineArr[$val['url']]['url_20_190'];
					$map['url_40_190'] = $thumbOnlineArr[$val['url']]['url_40_190'];				
				}
				$add_thumb = $thumb_db -> add($map);
				if(!$add_thumb){	
					$this -> writelog("add_thumb_error.log",$thumb_db->getlastsql());				
					//如果插入sj_soft_thumb表失败删除上两步的数据
					$this -> rollback($softid,$tmpid,$from);
					return 0;
				}else{
					//回写softid/thumb_id
					$map2 = array();
					$map2['softid'] = $softid;
					$map2['thumb_id'] =  $add_thumb;
					$update_tmpthumb = $thumb_tmp_db -> where("tmp_id={$tmpid} and id={$val['id']}") -> save($map2);	
				}
			}
		}
		return 1;	
	}
	//添加sj_soft_thumbgif表数据
	public function add_thumbgif($tmpid,$softid,$from=''){
		$thumb_tmp_db = M('soft_thumbgif_tmp');
		$thumb_db = M('soft_thumbgif');
		$thumbtmplist = $thumb_tmp_db ->where(array("tmp_id"=>$tmpid,'status'=>'1'))->select();
		if($thumbtmplist){
			foreach($thumbtmplist as $val){
				$map = array();
				$map['softid'] = $softid;
				$map['status'] = $val['status'];
				$map['url'] = $val['url'];
				$map['rank'] = $val['rank'];
				$map['upload_time'] = $val['upload_time'];
				$map['last_refresh'] = $val['last_refresh'];
				$map['image_raw'] = $val['image_raw'];
				$add_thumb = $thumb_db -> add($map);
				if(!$add_thumb){	
					$this -> writelog("add_thumbgif_error.log",$thumb_db->getlastsql());				
					//如果插入sj_soft_thumb表失败删除上两步的数据
					$this -> rollback($softid,$tmpid,$from);
					return 0;
				}else{
					//回写softid/thumb_id
					$map2 = array();
					$map2['softid'] = $softid;
					$map2['thumb_id'] =  $add_thumb;
					$update_tmpthumb = $thumb_tmp_db -> where("tmp_id={$tmpid} and id={$val['id']}") -> save($map2);	
				}
			}
		}
        return 1;	
	}
	//添加sj_soft_bookright表数据
	public function add_book($tmpid,$softid){	
		//soft_bookright表操作开始
		$bookright_tmp_db = M('soft_bookright_tmp');
		$bookright_db = M('soft_bookright');
		$bookright_list = $bookright_tmp_db ->where(array("tmp_id"=>$tmpid,'status'=>1))->find();	
		if($bookright_list){
			$map = array();
			$map['softid'] = $softid;
			$map['identity_pic'] = $bookright_list['identity_pic'];
			$map['right_pic'] = $bookright_list['right_pic'];
			$map['business_pic'] = $bookright_list['business_pic'];
			$map['upload_tm'] = $bookright_list['upload_tm'];
			$map['status'] = $bookright_list['status'];
			$add_book = $bookright_db -> add($map);
			//echo $bookright_db->getlastsql();
			if(!$add_book){
				$this -> writelog("add_book.log",$bookright_db->getlastsql());						
				//如果插入sj_soft_bookright表失败删除上三步的数据
				$this -> rollback($softid,$tmpid);
				return 0;
			}else{	
				//回写softid/bookright_id
				$map3 =  array();
				$map3['softid'] = $softid;
				$map3['bookright_id'] =  $add_book;
				$bookright_tmp_db -> where("tmp_id={$tmpid}") -> save($map3);
			}
			return 	$add_book;
		}else{
			$this -> writelog("sel_bookright_tmp.log",$bookright_tmp_db->getlastsql());
			return 1;
		}
	}
	//记录向豌豆夹发送更新请求
	public function request_data_log($method, $request_url, $softid, $type, $date) {
		$dir = P_LOG_DIR . "/admin.goapk.com/" . date("Y-m-d", $date);
		if (!is_dir($dir))
			mkdir($dir, 0755);
		file_put_contents($dir . "/curl.log", date("Y-m-d H:i:s", $date) . '=> Took  method ' . $method . ' to send a request to ' . $request_url . "in to softid " . $softid . " " . $type . " \n", FILE_APPEND);
	}
	//更新统计statistics_on字段（上架的数据）
	public function update_developer($dev_id){
		$model = new Model();
		$where['status'] = 1; 
		$where['hide'] = 1; 
		$where['channel_id'] = ''; 
		$where['claim_status'] = 2;
		if(is_array($dev_id)){
			foreach($dev_id as $val){
				$where['dev_id'] = $val; 
				$count = $model -> table('sj_soft') -> where($where) -> count();
				$model -> query("UPDATE pu_developer SET statistics_on = {$count} WHERE dev_id={$val}");
			}
		}else{	
			$where['dev_id'] = $dev_id; 
			$count = $model -> table('sj_soft') -> where($where) -> count();
			$model -> query("UPDATE pu_developer SET statistics_on = {$count} WHERE dev_id={$dev_id}");
		}
	}
	
	public function sendMailWhilelist($package,$softname){
		static $emailmodel;
		if(!$emailmodel){
			$emailmodel = D("Dev.Sendemail");
		}
	    $whitelist = $this-> table('sj_soft_whitelist')->where("package='{$package}' and status=1")-> field('*') ->find();
	    $time = date('Y-m-d H :i:s',time());
	    if($whitelist && $whitelist['softname'] != $softname){
	        $email_data = $this-> table('sj_email_setting')->where("(app_sign & 32) = 32 and status = 1")-> field('*') ->findAll();
	        if($email_data){
	            foreach ($email_data as $email){	            
		            $mailsubject = "运营白名单-软件名称变更邮件";
		            $mailbody = "登录前往518后台>游戏运营tab>运营白名单里，对'白名单软件名称'进行更新<br><br>";
		            $mailbody .= "<table border=1 ><tr><td>包名</td><td>白名单软件名称</td><td>线上软件最新名称</td><td>负责人</td><td>开发者</td><td>线上软件最新名称更新时间</td><td>操作</td></tr><tr><td>{$package}</td><td>{$whitelist['softname']}</td><td>{$softname}</td><td>{$whitelist['adminname']}</td><td>{$whitelist['dev_name']}</td><td>{$time}</td><td><a href='http://518.anzhi.com/index.php/Public/login' target='_blank'>点此登录</a></td></tr></table> " ;
		            //SendMail($email['email_address'],$mailsubject,$mailbody);
		            $emailmodel -> realsend($email['email_address'],'admin',$mailsubject,$mailbody);
 
	        	}
	    	}
	        
	    }
	}
	
	public function getchangeapk($dev_id){
	    $model = new Model();
	    $id = $model->table('sj_userapk')->where("dev_id={$dev_id} and status=1")->getField('id');
	    if ($id) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	function __useage($uniqid, $line)
	{
		static $start;
		if (empty($start)) {
			$start = gettimeofday(true);
		}
		$end = gettimeofday(true);
		$spend = $end - $start;
		file_put_contents('/tmp/debug.log', "{$uniqid}: {$spend} {$line}\n", FILE_APPEND);
		$start = $end;
	}
	function writelog($filename,$msg){
		$now = time();
		$path = "/data/att/permanent_log/admin_data_log/".date("Y-m-d", $now);
		if(!file_exists($path)){
			mkdir($path, 0755, true);
		}	
		$path_log = $path."/".$filename;
		$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
		file_put_contents($path_log, $msg, FILE_APPEND);
	}
	//软件操作失败回滚函数
	function rollback($softid,$tmpid,$from = ''){
	//如果是修改描述就不做这个操作----只在add_file和add_thumb函数中有from值
		if($from != 'save'){
			$res = $this->table('sj_soft')-> where("softid={$softid}")->delete();
			if(!$res){
				$this -> writelog('del_soft.log',$this -> getlastsql());
			}
		}
		$res = $this->table('sj_soft_tmp')-> where("id={$tmpid}") -> save(array('status'=>2));
		if(!$res){
			$this -> writelog('del_soft_tmp.log',$this -> getlastsql());
		}
	}
	//@发送增量更新
	function send_incremental_update($softid,$pkg,$total_downloaded){
		if($_SERVER['SERVER_ADDR'] !='192.168.0.99' && $_SERVER['SERVER_ADDR'] != '10.0.3.15') {
			$update_whitelist = $this->table('sj_update_whitelist')-> where("del=0 and package='{$pkg}'") ->field('id')-> find();
			$push_soft = $this->table('sj_update_push')->where("status=1 and soft_package = '{$pkg}'")->field('id')-> find();
			$task_client = get_task_client();
			//下载量大于5千万和存在白名单优先发增量更新
			if($update_whitelist || $total_downloaded > 50000000||$push_soft){
				$task_client->doHighBackground("incremental_update_high", json_encode(array("softid" =>$softid,"atime"=>time())));
			}else{
				$task_client->doBackground("incremental_update", json_encode(array("softid" =>$softid,"atime"=>time())));
			}
		}
	}
	//接入SDK自动加入白名单
	function sdk_add_soft_whitelist($data,$whitelist,$record_type='',$package=''){
		if($data){
			$time = time();
			$where = array(
				'package' => $data['package'],
			);
			$where_y = array(
				'package' => $data['package'],
				'del'=>0,
				'step'=>4,
				'from'=>1
			);
            $ret = $this -> table('yx_product')->where($where_y)->field('p_fenlei')->find();
			if($ret){
				$map = array(
					 'dev_id' => $data['dev_id'],
					 'package' => $data['package'],
					 'status' => 1,
					 'is_sdk' => 1,
					 'is_online' => 1,
					 'newgamelist' => 1,
					 'is_time_shelves' => 1,
					 'dev_name'=>$data['dev_name'],
				);
				if($ret['p_fenlei'] == "网游" || $ret['p_fenlei'] == "棋牌"){
					$map['sdk_fun'] = 3;
				}else if($ret['p_fenlei'] == "单机(安智SDK)"){
					$map['sdk_fun'] = 1;
				}
				if(!$whitelist){
					$map['create_at'] = $time;
					$map['update_at'] = $time;
					$map['softname'] = $data['softname'];
					$map['add_from'] = 1;
					$res = $this -> table('sj_soft_whitelist') -> add($map);
				}else{
					$res = $this -> table('sj_soft_whitelist')->where($where) -> save($map);
				}
				if($record_type == 1 || $record_type == 3 ){	
					$notelist = $this->table('sj_soft_note')-> where($where)->find();
					$map = array();
					$map['note'] = '';
					$map['package'] = $package;		
					//加入到soft_note表中
					if($res){
						$map['status'] = 1;
						$map['start_time'] = time();
						$map['terminal_time'] = time()+8*365*24*3600;
					}
					if(!$notelist){
						$map['auth'] = 1;
						$this->table('sj_soft_note') -> add($map);
					}
					if($notelist['status']==0){
						//$map['auth'] = 1;
						$this->table('sj_soft_note') ->where($where)->save($map);
					}										 
				}

                $has_app = $this->table('sj_sdk_info')->where(array('package' => $data['package']))->field('app_id')->find();

				$map = array();
				if($ret['p_fenlei'] == '网游'){
					$map['game_type'] = 1;
					$map['type'] = 2;
				}else if($ret['p_fenlei'] == '棋牌'){
					$map['game_type'] = 2;
					$map['type'] = 0;
				}else{
					$map['game_type'] = 0;
					$map['type'] = 0;
				}			
                $map['op_type'] = 1;
                $map['app_key'] = $has_app['app_id'];
                $map['dev_status'] = 0;
                $map['pkg'] = $data['package'];               
                //updata_app_message($map);
			}else{
				if($whitelist){
					$data =array(
						'is_online' => 1,
						'is_time_shelves' => 1,
						//'update_at' => $time,//袁明叫去掉
					);
					$res = $this -> table('sj_soft_whitelist')->where($where) -> save($data);
				}else{
					return 1;
				}
			}
			//echo $this->getlastsql();
			if(!$res){
				$this -> writelog('sdk_add_soft_whitelist.log',$this -> getlastsql());
				return 1;
			}else{
				return 1;
			}
		}
	}
	//SDK测试通过发邮件
	function sdk_test_pass_mail($tmplist){
		//发送提醒信息			
		$tm = date("Y-m-d",time());
		if($tmplist['dev_id'] != 0){
			static $emailmodel;
			if(!$emailmodel){
				$emailmodel = D("Dev.Sendemail");
			}
			static $config_txt;
			if(!$config_txt){
				$config_txt = C('_config_txt_');
			}
			$search   = array("softname", "tm");
			$replace    = array($tmplist['softname'],$tm);

			$msg = str_replace($search,$replace,$config_txt['sdk_test_pass']);		
			//发送邮件提醒
			$subject = $config_txt['sdk_test_subject'];	
			$pass_txt  = $config_txt['sdk_test_pass_txt'];		

			$emailmodel->dev_remind_add($tmplist['dev_id'],$msg);
			$dever = $this->table('pu_developer')->where("dev_id={$tmplist['dev_id']}")-> field('dev_id,email,dev_name') ->find();
			$search2   = array("devname", "softname", "tm");
			$replace2    = array($dever['dev_name'],$tmplist['softname'], $tm );	
			$email_cont = str_replace($search2,$replace2,$pass_txt);				
			$emailmodel -> realsend($dever['email'],$dever['dev_name'],$subject,$email_cont);
		}				
	}
	function update_sign($package,$sign){
		$where['package']	= $package;
		$data['sign']		= $sign;
		$res = $this -> table('sj_soft_sign')->where($where) -> save($data);
		if($res){
			$this->writelog('sign.log', "\n更新成功:".$this->getlastsql()."\n操作人:".$_SESSION['admin']['admin_id']);
		}else{
			$this->writelog('sign.log', "\n更新失败:".$this->getlastsql()."\n操作人:".$_SESSION['admin']['admin_id']);
		}
	}
	function update_shield($data){
		//审核通过后自动去除软件的屏蔽标识
		$map = array(
			'shield' => 2
		);
		//devid存在软件仅搜索的配置里
		if (in_array($data['dev_id'], C('only_search_devid'))) {
			$map['only_search'] = 1;
		}
		$this->table('sj_soft_note') ->where("package='{$data['package']}'")-> save($map);
		if (in_array($data['dev_id'], C('only_search_devid'))) {
			//软件仅搜索可见
			$time = time();
			$map = array(
				'start_time' => $time,
				'end_time' => $time+365*86400,
				'status' => 1
			);
			$res = $this->table('sj_search_adapter') ->where("package='{$data['package']}'")->find();
			if($res){
				$this->table('sj_search_adapter') ->where("package='{$data['package']}'")-> save($map);
			}else{
				$map['package'] = $data['package'];
				$this->table('sj_search_adapter') -> add($map);
			}
		}
	}
	
	//联运软件升级通过将分类同步到老版本
	private function  sync_sdk_soft_category($package,$category){
		$option = array(
			'package' => $package,
			'hide' =>array('exp','in(1,0,3)'),
			'category_id'=>array('exp',"!='{$category}'"),
			'status' => 1
		);

		$old_version = $this->table('sj_soft')->where($option)->field('softid')->select();
		if($old_version){
			foreach($old_version as $v){
				$res = $this->table('sj_soft')->where(array('softid'=>$v['softid']))->save(array('category_id'=>$category));
				if(!$res) $this->writelog('sync_sdk_soft_category_error.log', "\n同步分类失败:".$this->getlastsql());
			}
		}
	}

	//联运游戏二次签名（新联运游戏及旧联运游戏升级）
	function check_sdk_sign($data){
		$sign = '-736551223,412919359';
		$sdk_sign = $this->table('sj_sdk_sign')->where("package = '{$data['apk_name']}'")->find();

		if($sdk_sign){
			if($sign != $data['sign']){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}


	}
}
?>