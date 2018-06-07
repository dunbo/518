<?php
define('ABI_ARMEABI', 1);
define('ABI_ARMEABI_V7A', 2);
define('ABI_X86', 4);
define('ABI_MIPS', 8);
class SoftlistModel extends Model {	
	//获取soft数据
	public function getsoftlist($hide,$params, $limit,$safe){
		$soft_tmp = D("Dev.Softaudit");
		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		//搜索功能
		$where =" s.status = 1 ";
		if(isset($hide)){
			if($hide == 1){
				$where .= " and s.hide=1 and s.channel_id =''";
			}else{
				$where .= " and s.hide='{$hide}'";
			}
		}
		//不安全软件搜索
		if(!empty($safe)){ $where .= " and s.safe >=2";}
		if(isset($params['softid'])){ $where .= " and s.softid ='{$params['softid']}'"; }
		if(isset($params['dev_id'])){ $where .= " and s.dev_id ='{$params['dev_id']}'"; }	
		if(isset($params['softname'])){	$where .= " and s.softname like '%{$params['softname']}%'"; }
		if(isset($params['package'])){
			$params['package'] = trim($params['package']);
			$where .= " and s.package='{$params['package']}'";
		}
		if(isset($params['dev_name']) || isset($params['email']) || isset($params['dev_type'])){
			$wheres = array();
			if(isset($params['email']) && $params['email']!=''){
				$params['email'] = trim($params['email']);
				$wheres['email'] = array("eq","{$params['email']}");
			}
			if(isset($params['dev_name']) && $params['dev_name']!=''){	
				$wheres['dev_name'] = array('like',"%{$params['dev_name']}%");		
			}
			if(isset($params['dev_type'])){
				$wheres['type'] = array("eq","{$params['dev_type']}");
			}
			$devname = $this->table('pu_developer')->where($wheres)->field('dev_id')->select();
			$devid_str = '';
			foreach ($devname as $n => $m ){
				$devid_str .= "'".$m['dev_id']."',";
			}
			$devid_str = substr($devid_str,0,-1);
			if(isset($params['dev_name']) && $params['dev_name']!=''){
				if(!empty($devid_str)){
					$where .= " and (s.dev_name like '%{$params['dev_name']}%' and s.dev_id='' or s.dev_id in({$devid_str}))";
				}else{
					$where .= " and s.dev_name like '%{$params['dev_name']}%'";
				}
			} else {
				$where .= " and s.dev_id in ({$devid_str})";
			}
		}
		//搜索广告
		if(!empty($params['ad_id'])){
			$adid_arr = explode(',',$params['ad_id']);	
			$ad_where = "package_status = 1 and (";
			foreach($adid_arr as $v){
				if($v == '') continue;
				if($v != 10005 && $v != 10006 && $v != 0 ){			
					$ad_where .= " ad_new like '%,{$v},%' or";
				}else if($v == 0){
					//$ad_where .= " ad_new = '' or";
					$ad_where .= " leafstatus = 2 or";
				}else if($v == 10005){
					$ad_where .= " leafstatus = 1 or";
				}else if($v == 10006){
					$ad_where .= " ad_new = '' and leafstatus = 0 or";
				}
			}
			$ad_where = substr($ad_where,0,-3);
			$ad_where .= " )";
			$subQuery = $this->table('sj_soft_file')->field('softid')->where($ad_where)->buildSql(); 
			$where .= " and s.softid in ({$subQuery})";
		}
		if(!empty($params['begintime']) && !empty($params['endtime'])){
			$begintime = strtotime($params['begintime']);
			$endtime = strtotime($params['endtime']);
			$where .= " and (s.last_refresh >= {$begintime} and s.last_refresh <= {$endtime})";
		}
		//下载量搜索
		if(isset($params['uplode']) && isset($params['uplode1'])){
			$where .= " and s.total_downloaded+s.total_downloaded_add-s.total_downloaded_detain >= {$params['uplode']} and s.total_downloaded+s.total_downloaded_add-s.total_downloaded_detain <= {$params['uplode1']}";
		}
		if(!empty($params['cateid'])){
			$cateids = explode(',',$params['cateid']);
			$cateidstr = '';
			foreach($cateids as $vv){
				if($vv != ''){
					$cateidstr .= "',".$vv.",',";
				}
			}
			$cateidstr = substr($cateidstr,0,-1);
			$where .= " and s.category_id in ({$cateidstr})";
		}
		//下载量和更新时间排序
 		if(!empty($params['orderby'])){
			$orderby = '';
			if ($params['orderby'] == 'download') {
				$orderby = '(s.total_downloaded+s.total_downloaded_add-s.total_downloaded_detain)';
			} elseif ($params['orderby'] == 'time') {
				$orderby = 's.last_refresh';
			}
		}else{
			$orderby = 's.last_refresh';
		}
		$order  = !empty($params['order']) ? $params['order'] : 'd';
		if ($order == 'd') {
			$order_str = $orderby.' desc';
		} elseif ($order == 'a') {
			$order_str = $orderby.' asc';
		}
		//官方认证--屏蔽状态--游戏内付费--小编点评--安智精选
		if(!empty($params['Official']) || isset($params['type']) || isset($params['shield_status']) || isset($params['game_charge']) || isset($params['comment']) || isset($params['azjx_type'])){
			$tm = time();
			if(isset($params['type'])){
			//	$where .= " and n.type ={$params['type']} and (n.type_start <= {$tm} and n.type_end >={$tm})";
			$where .= " and n.type ={$params['type']} and (n.type_end >= {$tm})";
			}
			if(isset($params['shield_status'])){
				if($params['shield_status'] == 2){
					$where .= " and (n.`shield`='2' OR n.shield_end <={$tm} or n.shield IS NULL)";
				}else{
					$where .=  " and n.shield='{$params['shield_status']}' and (n.shield_start <= {$tm} and n.shield_end >={$tm})";
				}
			}
			if(!empty($params['Official'])){
				if($params['Official'] == 2){
					$where .=  " and (n.status in(0,2) or n.status IS NULL or n.terminal_time <={$tm})";
				}else{
					$where .=  " and n.status='{$params['Official']}'  and (n.start_time <= {$tm} and n.terminal_time >={$tm}) ";
				}
			}
			if(!empty($params['game_charge'])){
				if($params['game_charge'] == 2){
					$where .=  " and (n.game_charge='0' or n.game_charge IS NULL or n.charge_end <={$tm})";
				}else{
					$where .=  " and n.game_charge='1'  and (n.charge_start <= {$tm} and n.charge_end >={$tm}) ";
				}
			}
			if(isset($params['comment'])){
				if($params['comment'] == 1){
					$where .= " and n.`comment`!=''";
				}else{
					$where .= " and n.`comment`=''";
				}
			}
			if(isset($params['azjx_type'])){
				if($params['azjx_type'] == 1){
					$where .= " and n.`azjx_type`='1'";
				}else{
					$where .= " and n.`azjx_type`='0'";
				}
			}			
		}
		if(isset($params['TV'])){
			$where .= " and s.terrace = {$params['TV']}";
		}
		//软件来源
		if(isset($params['soft_source'])){
			$where .= " and s.update_type = {$params['soft_source']}";
		}
		//软件简介
		if(isset($params['intro'])){
			$where .= " and (s.intro like '%{$params['intro']}%' or s.update_content like '%{$params['intro']}%' or s.tags like '%{$params['intro']}%')";
		}
		//认领选择
		if(isset($params['claim'])){
			if($params['claim'] == 2){
				$where .= " and s.claim_status =2";
			}elseif($params['claim'] == 0){
				$where .= " and s.claim_status !=2";
			}
		}
		//安全状态
		$provider = '';
		if(isset($params['safe'])){
			if($params['safe'] == 1 ||$params['safe'] == 3){				
				$where .=" and s.safe = 1";
			}elseif($params['safe'] == 2){
				$where .=" and s.safe >1";
			}
			if($params['safe'] == 3){
				$provider = 1; //只显示返回安全
			}
		}
		//x86
		if(isset($params['x86'])){
			if($params['x86'] == 1){
				$where .=" and (s.abi = 0 or s.abi & 4 = 4)";
			}elseif($params['x86'] == 0){
				$where .=" and  s.abi & 4 != 4 and s.abi !=0";
			}
        }

		
		
		if(isset($params['is_bj_shield'])){
			$shield_package=$this->table('sj_soft_bj_shield')->where(array('is_shield'=>1))->field('package')->select();

		
			$shield_package_str = '';
			foreach ($shield_package as $n => $m ){
				$shield_package_str .= "'".$m['package']."',";
			}
			
			$shield_package_str = substr($shield_package_str,0,-1);

			if($params['is_bj_shield'] == 1){
				$where .= " and s.package in ({$shield_package_str})";
			}else{
				$where .= " and s.package not in ({$shield_package_str})";
			}
		}
		if(isset($params['is_official'])){
			$sdk_official_softids=$this->table('sj_sdk_official')->where(array('is_official'=>$params['is_official'],'softid'=>array('neq','')))->field('softid')->select();

			$sdk_official_softid_str = '';
			foreach ($sdk_official_softids as $n => $m ){
				$sdk_official_softid_str .= "'".$m['softid']."',";
			}
			$sdk_official_softid_str = substr($sdk_official_softid_str,0,-1);
			$where .= " and s.softid in ({$sdk_official_softid_str})";
		}
		if(isset($params['download_type'])){
			if($params['download_type'] == 1){
				$where .= " and (e.download_type = 1 or e.download_type is null)";
			}else if($params['download_type'] == 2){
				$where .= " and e.download_type = 2";
			}
		}

		//-------分页
		if(!empty($params['Official']) || isset($params['type']) || isset($params['shield_status'])|| isset($params['game_charge']) || isset($params['comment']) || isset($params['azjx_type'])){
				$total = $this->table('sj_soft s')->join("sj_soft_note n ON n.package = s.package")->join("sj_soft_expand e on s.package = e.package")->where($where)->count();
		}else{
				$total = $this->table('sj_soft s')->join("sj_soft_expand e on s.package = e.package")->where($where)->count();
		}
		import('@.ORG.Page2');
		$param = http_build_query($params);
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		if(!empty($params['Official']) || isset($params['type']) || isset($params['shield_status']) || isset($params['game_charge']) || isset($params['comment']) || isset($params['azjx_type'])){
				$soft_list = $this->table('sj_soft s')->join("sj_soft_note n ON n.package = s.package")->join("sj_soft_expand e on s.package = e.package")->where($where)->field('s.*,n.*,n.status as status_n,n.type as type_n,n.shield as shield_n,e.download_type')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select();
		}else{
				$soft_list = $this->table('sj_soft s')->join("sj_soft_note n ON n.package = s.package")->join("sj_soft_expand e on s.package = e.package")->where($where)->field('s.*,n.in_short,n.type,n.new_content,e.download_type')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select();
		}//增加n.type判断type=1并且新版特性不为空展示新版特性

		$softlist = array();
		$categoryid = '';
		$softids = '';
		$devids = array();
		$package = array();
		$softid = array();
		//整合数据
		foreach ($soft_list as $k => $v){
			$softlist[$k]['tags'] = $v['tags'];
			$softlist[$k]['intro'] = $v['intro'];
			$softlist[$k]['update_content'] = $v['update_content'];
			$softlist[$k]['terrace'] = $v['terrace'];
			$softlist[$k]['softid'] = $v['softid'];
			$softlist[$k]['dev_id'] = $v['dev_id'];
			$softlist[$k]['safe'] = $v['safe'];
			$softlist[$k]['softname'] = $v['softname'];
			$softlist[$k]['package'] = $v['package'];
			$softlist[$k]['language'] = $v['language'];
			$softlist[$k]['version'] = $v['version'];
			$softlist[$k]['version_code'] = $v['version_code'];
			$softlist[$k]['hide_prev'] = $v['hide_prev'];
			$softlist[$k]['claim_status'] = $v['claim_status'];
			$softlist[$k]['update_type'] = $v['update_type'];
			$softlist[$k]['min_firmware'] = $v['min_firmware'];
			$softlist[$k]['max_firmware'] = $v['max_firmware'];	
			$softlist[$k]['in_short'] = $v['in_short'];
			$softlist[$k]['download_type'] = $v['download_type'];
			if($v['last_refresh']){
				$softlist[$k]['last_refresh'] = date("Y-m-d H:i:s",$v['last_refresh']);
			}
			$softlist[$k]['dev_name_soft'] = $v['dev_name'];
			$softlist[$k]['dev_id'] = $v['dev_id'];
			$softlist[$k]['only_search'] = $v['only_search'];
			//总下载量
			$softlist[$k]['total_downloaded'] = $v['total_downloaded'];
			//增量
			$softlist[$k]['total_downloaded_add'] = $v['total_downloaded_add'];
			//扣量数量
			$softlist[$k]['total_downloaded_detain'] = $v['total_downloaded_detain'];
			//剩余量
			$softlist[$k]['total_downloaded_surplus'] = number_format($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add']);
			//判断是否支持x86
			if ($v['abi'] == 0 || (intval($v['abi']) & ABI_X86) == ABI_X86){
				$softlist[$k]['abi_support'] = "<font color='red'>支持x86</font>";
			}else{
				$softlist[$k]['abi_support'] = "<font color='red'>不支持x86</font>";
			}
			$softlist[$k]['upload_tm'] = date("Y-m-d H:i:s",$v['upload_tm']);	
			//abi显示
			foreach($known_abis as $abi_key => $abi_value){
				if($abi_value & $v['abi'] || $v['abi'] == 0){
					$softlist[$k]['abis'][] = $abi_key."&nbsp;&nbsp;";
				}
			}			
			$categoryids .= substr("{$v['category_id']}",1);
			if(!empty($v['dev_id'])){
				$devids[] = $v['dev_id'];
			}
			if(!empty($v['package'])){
				$package[] = $v['package'];
			}
			if(!empty($v['softid'])){
				$softid[] = $v['softid'];
				$softids .= $v['softid'].",";
			}

		}
		//隐藏下载量配置
		if($package){
			//白名单数据
			$hidden_downloads_where = array(
				'is_hidden' => 1,
				'package' => array('in',$package)
			);
			$hidden_downloads_list = get_table_data($hidden_downloads_where,"sj_soft_downloads_hidden_config","package","package,is_hidden");
			// var_dump($hidden_downloads_list);die;

			//屏蔽北京
			$bj_shield_list = get_table_data(array('is_shield' => 1,'package' => array('in',$package)),"sj_soft_bj_shield","package","package,is_shield");
			

		}
		

		//标签
		$sj_user_tag = $this->table('sj_tag_history')->where(array('package'=>array('in',$package),'type'=>1,'status'=>1))->order('id desc')->group('package')->select();
		
		$sj_tag = $this->table('sj_tag_history')->where(array('package'=>array('in',$package),'type'=>array('in','2,3'),'status'=>1))->select();
		$sj_tag_arr = array();
		foreach($package as $k=>$v){
			foreach($sj_user_tag as $u_k=>$u_v){
				if($v==$u_v['package']){
					if(isset($sj_tag_arr[$v])&&!empty($sj_tag_arr[$v])){
						
					} 
					$sj_tag_arr[$v] = $u_v['tag_name'];
				}
			}
			foreach($sj_tag as $t_k=>$t_v){
				if($v==$t_v['package']){
					if(isset($sj_tag_arr[$v])&&!empty($sj_tag_arr[$v])){
						$sj_tag_arr[$v] .= ','.$t_v['tag_name'];	
					}else{
						$sj_tag_arr[$v] = $t_v['tag_name'];
					}
					
				}
			}
		}

		//类别名称
		if($categoryids){
			$where = array(
				'status' => array('exp','in (1,3)'),
				'category_id' => array('in',substr($categoryids,0,-1))
			);
			$category_all = get_table_data($where,"sj_category","category_id","category_id,name,status");
		}
		//开发者名称
		$devids = array_unique($devids);
		if($devids){
			$where = array(
				'dev_id' => array('in',$devids)
			);
			$dev_all = get_table_data($where,"pu_developer","dev_id","dev_id,dev_name,type,email,status");
		}
		//官方认证
		$sj_corner_mark = $this -> table('sj_corner_mark')->where("status=1")->field('id,name')->select();
		$mark = array('0'=>'普通');
		foreach($sj_corner_mark as $k){
			$mark[$k['id']] = $k['name'];
		}
		if(empty($params['Official']) && !isset($params['type']) && !isset($params['shield_status']) && !empty($package) && !isset($params['comment']) && !isset($params['azjx_type'])){
			$data = array();
			$data['package'] =array('in',$package);
			$note = $this ->table('sj_soft_note') -> where($data) ->select();
			$note_all = array();	
			foreach($note as $n){
				//角标名称
				$n['type_name'] = $mark[$n['type']];
				$note_all[$n['package']] = $n;
			}
		}
		if($softid){
			//显示sdk相关接入类型
			$sdk_official_list = get_table_data(array('softid' => array('in',$softid)),"sj_sdk_official","package","package,is_official");
			//sj_soft_file表中的数据
			$file_where['softid']  = array('in',$softid);
			$file_where['package_status']  = array('exp'," > 0");
			$file_list = $this ->table('sj_soft_file')->where($file_where)->select();
			$filearr = array();
			$md5sum_list = array();
			$sha1sum_list = array();
			$newicon_list = array();
			
			foreach($file_list as $key => $val){
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
			//广告--积分--推广商
			$md5_adinfo = $soft_tmp -> getAdsByHash($md5sum);
			//安全扫描
			$sha1_adinfo = $soft_tmp -> getbyhash($md5sum,$params['safe']);
			foreach($file_list as $key => $val){
				//无广告
				$str = '';
				$last_refresh = $val['last_refresh'] ? date("Y-m-d H:i:s",$val['last_refresh']):'';
				if($val['leafstatus'] == 2){
					$str = "无广告<br/><来自于金山><br/>".$last_refresh."<br/>无广告<br/><来自于腾讯><br/>".$last_refresh."<br/>";
				}else if($val['leafstatus'] == 0){
					$str = "无广告<br/><来自于金山><br/>".$last_refresh;
				}			
				$val['md5_adinfo'] = $md5_adinfo[$val['sha1_file']].$md5_adinfo[$val['md5_file']].$str;
				$val['sha1_adinfo'] = $sha1_adinfo[$val['sha1_file']].$sha1_adinfo[$val['md5_file']];	//var_dump($sha1_adinfo,$sha1_adinfo);exit;
				$filearr[$val['softid']] =  $val;
			}
			
		}
		if($package){
			//白名单数据
			$where = array(
				'status' => 1,
				'package' => array('in',$package)
			);
			$brush_adapter_list = get_table_data($where,"sj_safe_white_package","package","package");
		}
		//下架原因
		if($hide == 3){
			$softids = substr($softids,0,-1);
			$tmp = $this -> query("SELECT shelf_reason,softid FROM (SELECT shelf_reason,softid FROM sj_soft_tmp WHERE softid IN ({$softids}) and record_type=4 and status=1 ORDER BY id DESC) A GROUP BY A.softid");
			$tmp_arr = array();
			foreach($tmp as $key => $val){
				$tmp_arr[$val['softid']] = $val;
			}
			$reject_log = $this -> query("SELECT reason,softid FROM (SELECT reason,softid FROM sj_reject_log WHERE softid IN ({$softids}) ORDER BY id DESC) A GROUP BY A.softid");
			$log_arr = array();
			foreach($reject_log as $key => $val){
				$log_arr[$val['softid']] = $val;
			}
		}
		if($package){	
			$where = array(
				'package_status' => 1,
				'apk_name' => array('in',$package)
			);
			$fileicon_arr = get_table_data($where,"sj_soft_fileicon","apk_name","apk_name,md5_icon");
			$whiteList = $this ->soft_WhiteList($package);
		}	
		foreach($soft_list as $k => $v){
			$softlist[$k]['user_tag'] = $sj_tag_arr[$v['package']];
		    $softlist[$k]['only_search'] = $note_all[$v['package']]['only_search'];
			if($hide == 1 && $v['dev_id'] && $v['softname']){
				//如果排期中有值就不做比较
				if(empty($whiteList[$v['package']])){
					//盗版风险
					$softlist[$k]['Pirate'] = getPiracyWarning($v['softname'],$v['package'],$fileicon_arr[$v['package']]['md5_icon']);	
				}else{
					if(strpos($ret,"在运营白名单中")){
						$softlist[$k]['operate'] ="<font style='color:red'>运营</font>";
					}
					if(strpos($ret,"在商务白名单中")){
						$softlist[$k]['commerce'] ="<font style='color:red'>商务</font>";
					}
				}	
			}	
			if($hide == 3){
				if(!empty($tmp_arr[$v['softid']]['shelf_reason'])){
					$softlist[$k]['deny_msg'] = "主动申请下架 原因：<br/>".$tmp_arr[$v['softid']]['shelf_reason'];
				}else{
					if($v['safe'] > 2){
						$str = "系统检测下架";
					}else{
						$str = "后台下架";
					}
					if($log_arr[$v['softid']]['reason']){
						$softlist[$k]['deny_msg'] = $str."原因：<br/>".$log_arr[$v['softid']]['reason'];
					}else{
						$softlist[$k]['deny_msg'] = $str."原因：<br/>".$v['deny_msg'];
					}
				}
			}
			$categoryid = substr("{$v['category_id']}",1,-1);
			$softlist[$k]['category_name'] = $category_all[$categoryid]['name'];
			//type 0公司 1个人 2团队
			if(!empty($v['dev_id'])){
				$softlist[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
				$softlist[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
				$softlist[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
				$softlist[$k]['dev_status'] = $dev_all[$v['dev_id']]['status'];
			}
			if(!empty($params['Official']) || isset($params['type']) || isset($params['shield_status']) || isset($params['game_charge']) || isset($params['comment']) || isset($params['azjx_type'])){				
				//官方认证--角标类型
				if($v['start_time'] <= time() && $v['terminal_time'] >= time()){
					$softlist[$k]['setOfficial'] = $v['status_n'];
				}
				if($v['type_end'] >= time()){
					$type_n = '';
					if($v['type_start'] > time()){
						$type_n = "待";
					}
					$softlist[$k]['Angle_type'] = $type_n.$mark[$v['type_n']];
					$softlist[$k]['type_start'] = date("Y-m-d H:i:s",$v['type_start']);
					$softlist[$k]['type_end'] = date("Y-m-d H:i:s",$v['type_end']);
                    $softlist[$k]['new_version_feature'] = $v['new_version_feature'];
					$softlist[$k]['type'] = $v['type'];//新版特性为首发显示
					$softlist[$k]['new_content'] = $v['new_content'];//特权红包展示的内容
				}
				//屏蔽
				if($v['shield_start'] <= time() && $v['shield_end']>= time()){
					$softlist[$k]['shield'] = $v['shield_n'];
				}
				//游戏内付费
				if($v['game_charge'] == 1){
					if($v['charge_start'] <= time() && $v['charge_end']>= time()){
						$softlist[$k]['game_charge'] = 1;
					}	
				}	
				//小编点评
				$softlist[$k]['comment'] = $v['comment'];
				//安智精选	
				$softlist[$k]['azjx_type'] = $v['azjx_type'];				
			}else{
				//官方认证--角标类型
				if($note_all[$v['package']]['start_time'] <= time() && $note_all[$v['package']]['terminal_time'] >= time()){
					$softlist[$k]['setOfficial'] = $note_all[$v['package']]['status'];
				}
				if($note_all[$v['package']]['type_end'] >= time()){
					$type_n = '';
					if($note_all[$v['package']]['type_start'] > time()){
						$type_n = "待";
					}
					$softlist[$k]['Angle_type'] = $type_n.$note_all[$v['package']]['type_name'];
					$softlist[$k]['type_start'] = date("Y-m-d H:i:s",$note_all[$v['package']]['type_start']);
					$softlist[$k]['type_end'] = date("Y-m-d H:i:s",$note_all[$v['package']]['type_end']);
                    $softlist[$k]['new_version_feature'] = $note_all[$v['package']]['new_version_feature'];
					$softlist[$k]['type'] = $v['type'];//新版特性为首发显示
					$softlist[$k]['new_content'] = $v['new_content'];//特权红包展示的内容
				}
				//屏蔽
				if($note_all[$v['package']]['shield_start'] <= time() && $note_all[$v['package']]['shield_end']>= time()){
					$softlist[$k]['shield'] = $note_all[$v['package']]['shield'];
				}
				//游戏内付费
				if($note_all[$v['package']]['game_charge'] == 1){
					if($note_all[$v['package']]['charge_start'] <= time() && $note_all[$v['package']]['charge_end']>= time()){
						$softlist[$k]['game_charge'] = 1;
					}	
				}
				//小编点评
				$softlist[$k]['comment'] = $note_all[$v['package']]['comment'];
				//安智精选	
				$softlist[$k]['azjx_type'] = $note_all[$v['package']]['azjx_type'];
			}
			//图标
 			$softlist[$k]['advertisement'] = $soft_tmp->ad($filearr[$v['softid']]['ad_new']);
			$softlist[$k]['leafletname'] = $filearr[$v['softid']]['leafletname'];
			$softlist[$k]['iconurl'] = $filearr[$v['softid']]['iconurl_72'];
			$softlist[$k]['url'] = $filearr[$v['softid']]['url'];
			$softlist[$k]['is_ad'] = $filearr[$v['softid']]['leafstatus'];
			$fileid = $filearr[$v['softid']]['id'];
			$softlist[$k]['fileid'] = $fileid;
			//安全扫描
			$softlist[$k]['scan_result'] = $filearr[$v['softid']]['sha1_adinfo'];
			if($provider&&empty($softlist[$k]['scan_result'])){
				unset($softlist[$k]); continue;
			}
			if($filearr[$v['softid']]['md5_adinfo']){
				//积分、广告、推广商
				$softlist[$k]['scan1'] =  $filearr[$v['softid']]['md5_adinfo'];	
			}
			$softlist[$k]['package_adapter'] = $brush_adapter_list[$v['package']];
			//隐藏下载量
			$softlist[$k]['package_hidden_downloads'] = $hidden_downloads_list[$v['package']];
			$softlist[$k]['is_bj_shield'] = $bj_shield_list[$v['package']]['is_shield'];
			$softlist[$k]['is_official'] = $sdk_official_list[$v['package']]['is_official'];

			
			$softlist[$k]['feiwo_sdk_v'] = $filearr[$v['softid']]['feiwo_sdk_v'];
			//var_dump(md5_file(UPLOAD_PATH . $softlist[$k]['iconurl']));
		}
		//下架原因
		$reason_list = $this -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 5 ))->order('pos asc,id desc')->select();
		foreach($reason_list as &$val){
		    if($val['content2']){
		        $val['content2'] = explode('<br />', $val['content2']);
		    }
		}

		return array($softlist,$total, $Page,$reason_list,$sj_corner_mark);
	}

	
	//不安全列表
	public function getsoft_unsafe($params, $limit,$safe){
		$soft_tmp = D("Dev.Softaudit");
		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		//搜索功能
		if(!empty($params)){
			$wheres = array();
		}	
		$where_str = '';
		if(!empty($safe)){
			//不安全软件搜索
			$where_str .=" and s.safe >= 2";
		}
		if(isset($params['softid'])){
			$where_soft .= " and s.softid = '{$params['softid']}'";
			$where_tmp .= " and (s.softid = '{$params['softid']}' or s.update_from = {$params['softid']})";
		}		
		if(isset($params['dev_id'])){
			$where_str .= " and s.dev_id = {$params['dev_id']}";
		}	
		if(isset($params['softname'])){
			$params['softname'] = trim($params['softname']);
			$where_str .= " and s.softname like '%{$params['softname']}%' ";
		}
		if(isset($params['package'])){
			$params['package'] = trim($params['package']);
			$where_str .= " and s.package = '{$params['package']}'";
		}
		if(isset($params['dev_name']) || isset($params['email']) || isset($params['dev_type'])){
			if(isset($params['email']) && $params['email']!=''){
				$params['email'] = trim($params['email']);
				$wheres['email'] = array("eq","{$params['email']}");
			}
			if(isset($params['dev_name']) && $params['dev_name']!=''){	
				$wheres['dev_name'] = array('like',"%{$params['dev_name']}%");		
			}
			if(isset($params['dev_type'])){
				$wheres['type'] = array("eq","{$params['dev_type']}");
			}
			$devname = $this->table('pu_developer')->where($wheres)->field('dev_id')->select();
			$dev_id = '';
			foreach ($devname as $n => $m ){
				$dev_id .= $m['dev_id'].",";
			}
			$dev_id =substr($dev_id,0,-1);
			if(isset($params['dev_name']) && $params['dev_name']!=''){
				if(!empty($dev_id)){
					$where_str .= " and s.dev_name like '%{$params['dev_name']}%' or dev_id in ({$dev_id})";
				}else{
					$where_str .= " and s.dev_name like '%{$params['dev_name']}%'";
				}
			} else {
				$where_str .= " and s.dev_id in ({$dev_id})";
			}
		}

		//搜索广告
		if(!empty($params['ad_id'])){
			$adid_arr = explode(',',$params['ad_id']);	
			$ad_where = "package_status = 1 and (";
			foreach($adid_arr as $v){
				if($v == '') continue;
				if($v != 10005 && $v != 10006 && $v != 0 ){			
					$ad_where .= " ad_new like '%,{$v},%' or";
				}else if($v == 0){
					//$ad_where .= " ad_new = '' or";
					$ad_where .= " leafstatus = 2 or";
				}else if($v == 10005){
					$ad_where .= " leafstatus = 1 or";
				}else if($v == 10006){
					$ad_where .= " leafstatus = 0 or";
				}
			}
			$ad_where = substr($ad_where,0,-3);
			$ad_where .= " )";
			$where_soft .= " and s.softid in (select softid from sj_soft_file where {$ad_where})";
			$where_tmp .= " and s.package in (select apk_name from sj_soft_file_tmp where {$ad_where})";
		}
		if(!empty($params['begintime']) && !empty($params['endtime'])){
			$begintime = strtotime($params['begintime']);
			$endtime = strtotime($params['endtime'])+86399;
			$where_str .= " and s.last_refresh >= {$begintime} and s.last_refresh <= {$endtime}";
		}
		//下载量搜索
		if(isset($params['uplode']) && isset($params['uplode1'])){
			$where_str .= " and s.total_downloaded+s.total_downloaded_add-s.total_downloaded_detain >= {$params['uplode']} and s.total_downloaded+s.total_downloaded_add-s.total_downloaded_detain <= {$params['uplode1']}";
		}
		if(!empty($params['cateid'])){
			$cateids = explode(',',$params['cateid']);
			$cateidarr = '';
			foreach($cateids as $vv){
				if($vv != ''){
					$cateidarr .= "',".$vv.",',";
				}
			}
			$cateidarr = substr($cateidarr,0,-1);
			$where_str .= " and s.category_id in ({$cateidarr})";
		}
		//下载量和更新时间排序
 		if(!empty($params['orderby'])){
			$orderby = '';
			if ($params['orderby'] == 'download') {
				$orderby = '(s.total_downloaded+s.total_downloaded_add-s.total_downloaded_detain)';
			} else if ($params['orderby'] == 'time') {
				$orderby = 's.last_refresh';
			}
		}else{
			$orderby = 's.last_refresh';
		}
		$order  = !empty($params['order']) ? $params['order'] : 'd';
		if ($order == 'd') {
			$order_str = $orderby.' desc';
		} elseif ($order == 'a') {
			$order_str = $orderby.' asc';
		}
		//官方认证
		if(!empty($params['Official']) || isset($params['type'])|| isset($params['shield_status'])){
			$note = array();
			$tm = time();
			if(isset($params['type'])){	
				$where_str .= " and n.type='{$params['type']}' and (n.type_start <={$tm} and n.type_end >={$tm} )";			
			}
			if(isset($params['shield_status'])){
				if($params['shield_status'] == 2){
					$where_str .= " and (n.`shield`='2' OR n.shield_end <={$tm} or n.shield IS NULL)";
				}else{
					$where_str .= " and n.shield='{$params['shield_status']}' and (n.shield_start <={$tm} and n.shield_end >={$tm} )";
				}
			}			
			if(!empty($params['Official'])){
				if($params['Official'] == 2){
					$where_str .= " and (n.status in(0,2) or n.status IS NULL or n.terminal_time <={$tm} )";
				}else{
					$where_str .= " and n.status='{$params['Official']}' and (n.start_time <={$tm} and n.terminal_time >={$tm} )";
				}
			}
		}
		if(isset($params['TV'])){
			$where_str .= " and s.errace = {$params['TV']}";
		}
		//软件来源
		if(isset($params['soft_source'])){
				$where_str .= " and s.update_type = {$params['soft_source']}";

		}
		//认领选择
		if(isset($params['claim'])){
			if($params['claim'] == 2){
				$where_str .= " and s.claim_status = 2";
			}elseif($params['claim'] == 0){
				$where_str .= " and s.claim_status != 2";
			}
		}
		$count  = $this -> query("select sum(counts) from(select count(*) as counts from sj_soft s  left join sj_soft_note n ON n.package = s.package where (s.hide =1 or s.hide =3 or s.hide <0) and s.status=1 and s.channel_id =''{$where_str}{$where_soft}  union select count(*) as counts from sj_soft_tmp s left join sj_soft_note n ON n.package = s.package where s.status >=2 and record_type<=3 {$where_str}{$where_tmp})A");
		foreach($count as $c){
			$total = $c['sum(counts)'];
		}
		import('@.ORG.Page2');
		$param = http_build_query($params);
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		
		$soft_list = $this -> query("select * from (select 'soft' as go,0 as tmpid,1 as record_type,s.hide,softid,softname,s.package,dev_id,version,version_code,language,hide_prev,last_refresh,update_type,total_downloaded,total_downloaded_add,total_downloaded_detain,s.status,update_from,n.status as status_n,n.type as type_n,n.shield as shield_n,n.start_time as start_time_n,n.terminal_time as terminal_time_n,n.type_start as type_start_n,n.type_end as type_end_n,n.shield_start as shield_start_n,n.shield_end as shield_end_n from sj_soft s left join sj_soft_note n ON n.package = s.package where (s.hide =1 or s.hide =3 or s.hide <0) and s.status=1 and s.channel_id ='' {$where_str}{$where_soft} union select 'tmp' as go,id as tmpid,record_type,2 as hide ,softid,softname,s.package,dev_id,version,version_code,language,hide_prev,last_refresh,update_type,total_downloaded,total_downloaded_add,total_downloaded_detain,s.status,update_from,n.status as status_n,n.type as type_n,n.shield as shield_n ,n.start_time as start_time_n,n.terminal_time as terminal_time_n,n.type_start as type_start_n,n.type_end as type_end_n,n.shield_start as shield_start_n,n.shield_end as shield_end_n from sj_soft_tmp s left join sj_soft_note n ON n.package = s.package where s.status >=2 and record_type<=3 {$where_str}{$where_tmp}) A limit {$Page->firstRow},{$Page->listRows}");
		//echo $this->getlastsql();
		//广告信息
		include_once SERVER_ROOT . '/tools/functions.php';	
		$softlist = array();
		$categoryid = '';
		$softid = array();
		$tid = array();
		$devids = array();
		$package = array();
		//整合数据
		foreach ($soft_list as $k => $v){
			$softlist[$k]['terrace'] = $v['terrace'];
			$softlist[$k]['softid'] = $v['softid'];
			$softlist[$k]['tmpid'] = $v['tmpid'];	
			$softlist[$k]['softname'] = $v['softname'];
			$softlist[$k]['package'] = $v['package'];
			$softlist[$k]['language'] = $v['language'];
			$softlist[$k]['version'] = $v['version'];
			$softlist[$k]['version_code'] = $v['version_code'];
			$softlist[$k]['hide_prev'] = $v['hide_prev'];
			$softlist[$k]['claim_status'] = $v['claim_status'];
			$softlist[$k]['update_type'] = $v['update_type'];
			if($v['last_refresh']){
				$softlist[$k]['last_refresh'] = date("Y-m-d H:i:s",$v['last_refresh']);
			}
			$softlist[$k]['dev_name_soft'] = $v['dev_name'];
			$softlist[$k]['dev_id'] = $v['dev_id'];
 			$softlist[$k]['record_type'] = $v['record_type'];
			$softlist[$k]['hide'] = $v['hide'];
			$softlist[$k]['status'] = $v['status'];
			$softlist[$k]['go'] = $v['go'];
			$softlist[$k]['update_from'] = $v['update_from'];
			//总下载量
			$softlist[$k]['total_downloaded'] = $v['total_downloaded'];
			//增量
			$softlist[$k]['total_downloaded_add'] = $v['total_downloaded_add'];
			//扣量数量
			$softlist[$k]['total_downloaded_detain'] = $v['total_downloaded_detain'];
			//剩余量
			$softlist[$k]['total_downloaded_surplus'] = number_format($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add']);
			//$softlist[$k]['abi'] = $v['abi'];
			if($v['upload_tm']){
				$softlist[$k]['upload_tm'] = date("Y-m-d H:i:s",$v['upload_tm']);	
			}
			//abi显示
			foreach($known_abis as $abi_key => $abi_value){
				if($abi_value & $v['abi'] || $v['abi'] == 0){
					$softlist[$k]['abis'][] = $abi_key."&nbsp;&nbsp;";
				}
			}			
			$categoryids .= substr("{$v['category_id']}",1);
			if(!empty($v['dev_id'])){
				$devids[] = $v['dev_id'];
			}
			if(!empty($v['package'])){
				$package[] = $v['package'];
			}	
			if($v['go'] == 'tmp' && $v['tmpid'] != 0 && $v['record_type'] == 1 || $v['record_type'] == 3){
				if($v['tmpid']){
					$tid[] = $v['tmpid'];	
				}
			}else{
				if($v['softid']){
					$softid[] = $v['softid'];
				}
			}
		}
		//类别名称
		$categoryid['status'] =1;
		$categoryid['category_id'] =array('in',substr($categoryids,0,-1));		
		$category = $this ->table('sj_category')->where($categoryid)->field('category_id,name,status')->select();
		$category_all = array();
		foreach($category as $val){
			$category_all[$val['category_id']] = $val['name'];
		}
		//开发者名称
		$dev['dev_id'] = array('in',$devids);
		$dev_name = $this->table('pu_developer')->where($dev)->field('dev_id,dev_name,type,email,status')->select();
		$dev_all = array();
		foreach($dev_name as $m){
			$dev_all[$m['dev_id']] = $m;
		}
		//官方认证
		$sj_corner_mark = $this -> table('sj_corner_mark')->where("status=1")->field('id,name')->select();
		$mark = array('0'=>'普通');
		foreach($sj_corner_mark as $k){
			$mark[$k['id']] = $k['name'];
		}
		//安全扫描
		if($tid){
			$t_where = array();
 			$t_where['tmp_id'] = array('in',$tid);
			$t_where['package_status'] = array('exp',"> 0");
 			$file_tmp = $this ->table('sj_soft_file_tmp')->where($t_where)->select();
			$md5sum_list_t = array();
			$sha1sum_list_t = array();
			foreach($file_tmp as $key => $val){
				$sfid[] = $val['tmp_id'];
				if($val['md5_file']){
					$md5sum_list_t[] = $val['md5_file'];
				}
				if($val['sha1_file']){
					$sha1sum_list_t[] = $val['sha1_file'];
				}
			}
			$scan_result_hash_tmp = $soft_tmp -> getbyhash($md5sum_list_t);
			$sha1sum_tmp = $soft_tmp -> getbyhash($sha1sum_list_t);
			$filearr_tmp = array();
			foreach($file_tmp as $key => $val){
				$val['sha1_adinfo_tmp'] = $sha1sum_tmp[$val['sha1_file']];
				$val['md5_adinfo_tmp'] = $scan_result_hash_tmp[$val['md5_file']];
				$filearr_tmp[$val['tmp_id']] =  $val;
			} 			
		}
		if($softid){
			//sj_soft_file表中的数据
			$file_where = array();
			$file_where['softid']  = array('in',$softid);
			$file_where['package_status']  = array('exp'," > 0");
			$file_list = $this ->table('sj_soft_file')->where($file_where)->select();
			$filearr = array();
			$md5sum_list = array();
			$sha1sum_list = array();
			foreach($file_list as $key => $val){
				$sfid[] = $val['id'];
				if($val['md5_file']){
					$md5sum_list[] = $val['md5_file'];
				}
				if($val['sha1_file']){
					$sha1sum_list[] = $val['sha1_file'];
				}
			}
			if($md5sum_list){
				$md5_adinfo = $soft_tmp -> getAdsByHash($md5sum_list);
				$scan_result_hash_tmp = $soft_tmp -> getbyhash($md5sum_list);
			}
			if($sha1sum_list){
				$sha1_adinfo = $soft_tmp -> getAdsByHash($sha1sum_list);	
				$sha1sum_tmp = $soft_tmp -> getbyhash($sha1sum_list);
			}
			foreach($file_list as $key => $val){
				//无广告
				$str = '';
				$last_refresh = $val['last_refresh'] ? date("Y-m-d H:i:s",$val['last_refresh']):'';
				if($val['leafstatus'] == 2){
					$str = "无广告<br/><来自于金山><br/>".$last_refresh."<br/>无广告<br/><来自于腾讯><br/>".$last_refresh."<br/>";
				}else if($val['leafstatus'] == 0){
					$str = "无广告<br/><来自于金山><br/>".$last_refresh;
				}
				$val['sha1_adinfo'] = $sha1_adinfo[$val['sha1_file']].$str;
				$val['md5_adinfo'] = $md5_adinfo[$val['md5_file']];
				//安全扫描
				$val['sha1_adinfo_t'] = $sha1sum_tmp[$val['sha1_file']];
				$val['md5_adinfo_t'] = $scan_result_hash_tmp[$val['md5_file']];
				$filearr[$val['softid']] =  $val;
			}
		}
		foreach($soft_list as $k => $v){
			$categoryid = substr("{$v['category_id']}",1,-1);
			$softlist[$k]['category_name'] = $category_all[$categoryid];
			//type 0公司 1个人 2团队
			if(!empty($v['dev_id'])){
				$softlist[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
				$softlist[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
				$softlist[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
				$softlist[$k]['dev_status'] = $dev_all[$v['dev_id']]['status'];
			}
			//官方认证--角标类型
			if($v['start_time_n'] <= time() && $v['terminal_time_n'] >= time()){
				$softlist[$k]['setOfficial'] = $v['status_n'];
			}
			if($v['type_start_n'] <= time() && $v['type_end_n'] >= time()){
				$softlist[$k]['Angle_type'] = $mark[$v['type_n']];
			}
			//屏蔽
			if($v['shield_start_n'] <= time() && $v['shield_end_n'] >= time()){
				$softlist[$k]['shield'] = $v['shield_n'];
			}			
			//图标
			if($v['go'] == 'tmp' && $v['record_type'] == 1 || $v['record_type'] == 3){
				//广告
				$softlist[$k]['advertisement'] = $soft_tmp->ad($filearr_tmp[$v['id']]['ad_new']);
				$softlist[$k]['leafletname'] = $filearr_tmp[$v['tmpid']]['leafletname'];
				$softlist[$k]['iconurl'] = $filearr_tmp[$v['tmpid']]['iconurl'];
				$softlist[$k]['url'] = $filearr_tmp[$v['tmpid']]['url'];
				$fileid = $filearr_tmp[$v['tmpid']]['id'];
				//安全扫描
				$softlist[$k]['scan_result'] = $filearr_tmp[$v['tmpid']]['sha1_adinfo_tmp'];
				$softlist[$k]['scan_result1'] = $filearr_tmp[$v['tmpid']]['md5_adinfo_tmp'];
			}else{
				//广告
				$softlist[$k]['advertisement'] = $soft_tmp->ad($filearr[$v['softid']]['ad_new']);
				$softlist[$k]['leafletname'] = $filearr[$v['softid']]['leafletname'];
				$softlist[$k]['iconurl'] = $filearr[$v['softid']]['iconurl'];
				$softlist[$k]['url'] = $filearr[$v['softid']]['url'];
				$fileid = $filearr[$v['softid']]['id'];
				//安全扫描
				$softlist[$k]['scan_result'] = $filearr[$v['softid']]['sha1_adinfo_t'];
				$softlist[$k]['scan_result1'] = $filearr[$v['softid']]['md5_adinfo_t'];
			}
			//推广商
			if(!empty($filearr[$v['softid']]['sha1_adinfo'])){
				$softlist[$k]['scan'] = $filearr[$v['softid']]['sha1_adinfo'];
			}else{
				$softlist[$k]['scan'] = '';
			}
			
			if($filearr[$v['softid']]['md5_adinfo']){
				//积分、广告
				$softlist[$k]['scan1'] =  $filearr[$v['softid']]['md5_adinfo'];	
			}			
		}
		return array($softlist,$total,$Page,$sj_corner_mark);
	}
	//批量管理数据
	public function getsoftinformation($params, $limit,$uniqid,$package){
		$soft_tmp = D("Dev.Softaudit");
		//abi信息
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		//搜索功能		
 		if(!empty($params)){
			$where = array();
			$wheres = array();
		} 
		$where['status'] = 1;
		$where['hide'] = 1;				
		$where['package'] = array('in',$package);	
		if(isset($params['softid'])){
			$where['softid'] = array("eq","{$params['softid']}");
		}
		if(isset($params['softname'])){
			$where['softname'] = array("like","%{$params['softname']}%");
		}
		if(isset($params['package'])){
			$params['package'] = trim($params['package']);
			$where['package'] = array("eq","{$params['package']}");
		}
		if(isset($params['dev_name']) || isset($params['email']) || isset($params['dev_type'])){
			if(isset($params['email']) && $params['email']!=''){
				$params['email'] = trim($params['email']);
				$wheres['email'] = array("eq","{$params['email']}");
			}
			if(isset($params['dev_type'])){
				$wheres['type'] = array("eq","{$params['dev_type']}");
			}
			if(isset($params['dev_name']) && $params['dev_name']!=''){	
				$wheres['dev_name'] = array('like',"%{$params['dev_name']}%");
			}
			$devname = $this->table('pu_developer')->where($wheres)->field('dev_id')->select();
			$dev_id = array();
			foreach ($devname as $n => $m ){
				$dev_id[] = $m['dev_id'];
			}
				
			if(isset($params['dev_name'])&& $params['dev_name']!=''){
				$twda['dev_id'] = array("in",$dev_id);
				$twda['_string'] = "dev_name like '%{$params['dev_name']}%' and dev_id=''";
				$twda['_logic']='or';
				$where['_complex']=$twda;
			} else {
				$where['dev_id'] = array("in",$dev_id);
			}
		}
		if(isset($params['type'])){
			$data = array();
			$data['type'] = $params['type'];
			$data['start_time'] = array("elt",time());
			$data['terminal_time'] = array("egt",time());
			$notr = $this ->table('sj_soft_note') -> where($data) -> field('package')->select();
			$package = array();
			foreach($notr as $k => $v){
				$package[] = $v['package'];
			}
			$where['package'] = array("in",$package);
		}
		//搜索广告
		if(!empty($params['ad_id'])){
			$adid_arr = explode(',',$params['ad_id']);	
			$ad_where = "package_status = 1 and (";
			foreach($adid_arr as $v){
				if($v == '') continue;
				if($v != 10005 && $v != 10006 && $v != 0 ){			
					$ad_where .= " ad_new like '%,{$v},%' or";
				}else if($v == 0){
					//$ad_where .= " ad_new = '' or";
					$ad_where .= " leafstatus = 2 or";
				}else if($v == 10005){
					$ad_where .= " ad_new != '' or leafstatus = 1 or";
				}else if($v == 10006){
					$ad_where .= " leafstatus = 0 or";
				}
			}
			$ad_where = substr($ad_where,0,-3);
			$ad_where .= " )";
			$where .= " and s.softid in (select softid from sj_soft_file where {$ad_where})";
		}
		if(isset($params['uplode']) && isset($params['uplode1'])){
			$where['_string'] = "total_downloaded+total_downloaded_add-total_downloaded_detain >= {$params['uplode']} and total_downloaded+total_downloaded_add-total_downloaded_detain <= {$params['uplode1']}";
		}
		if(!empty($params['begintime']) && !empty($params['endtime'])){
			$begintime = strtotime($params['begintime']);
			$endtime = strtotime($params['endtime'])+86399;
			$where['last_refresh'] = array(array("egt",$begintime),array("elt",$endtime));
		}
		if(!empty($params['cateid'])){
			$cateids = explode(',',$params['cateid']);
			$cateidarr = array();
			foreach($cateids as $vv){
				if($vv != ''){
					$cateidarr[] = ",".$vv.",";
				}
			}
			$where['category_id'] = array('in',$cateidarr);
		}		 
		//下载量和更新时间排序
 		if(!empty($params['orderby'])){
			$orderby = '';
			if ($params['orderby'] == 'download') {
				$orderby = '(total_downloaded+total_downloaded_add-total_downloaded_detain)';
			} elseif ($params['orderby'] == 'time') {
				$orderby = 'last_refresh';
			}elseif($params['orderby'] == 'name'){
				$orderby = 'softname';
			}
		}else{
			$orderby = 'last_refresh';
		}
		$order  = !empty($params['order']) ? $params['order'] : 'd';
		if ($order == 'd') {
			$order_str = $orderby.' desc';
		} elseif ($order == 'a') {
			$order_str = $orderby.' asc';
		}
		
		//-------分页
		$total = $this->table('sj_soft')->where($where)->count();
		import('@.ORG.Page2');	
		$param = http_build_query($params);
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->parameter = 'uid='.$uniqid;		
		$soft_list = $this->table('sj_soft')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select();
		//echo $this->getlastsql();
		//广告信息
		include_once SERVER_ROOT . '/tools/functions.php';	
		$softlist = array();
		$categoryid = '';
		$devids = array();
		$ids = '';
		$package = array();
		//整合数据
		foreach ($soft_list as $k => $v){
			$softlist[$k]['softid'] = $v['softid'];
			$softlist[$k]['softname'] = $v['softname'];
			$softlist[$k]['package'] = $v['package'];
			$softlist[$k]['language'] = $v['language'];
			$softlist[$k]['version'] = $v['version'];
			$softlist[$k]['version_code'] = $v['version_code'];
			$softlist[$k]['deny_msg'] = $v['deny_msg'];
			$softlist[$k]['hide_prev'] = $v['hide_prev'];
			$softlist[$k]['update_type'] = $v['update_type'];
			$softlist[$k]['claim_status'] = $v['claim_status'];
			if($v['last_refresh']){
				$softlist[$k]['last_refresh'] = date("Y-m-d H:i:s",$v['last_refresh']);
			}
			$softlist[$k]['dev_name_soft'] = $v['dev_name'];
			$softlist[$k]['dev_id'] = $v['dev_id'];
			//总下载量
			$softlist[$k]['total_downloaded'] = $v['total_downloaded'];
			//增量
			$softlist[$k]['total_downloaded_add'] = $v['total_downloaded_add'];
			//扣量数量
			$softlist[$k]['total_downloaded_detain'] = $v['total_downloaded_detain'];
			//剩余量
			$softlist[$k]['total_downloaded_surplus'] = number_format($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add']);
			//$softlist[$k]['abi'] = $v['abi'];
			if($v['upload_tm']){
				$softlist[$k]['upload_tm'] = date("Y-m-d H:i:s",$v['upload_tm']);
			}				
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
				$devids[] = $v['dev_id'];
			}
			$package[] = $v['package'];
			if(!empty($v['softid'])){
				$softid[] = $v['softid'];
			}
		}
		//类别名称
		$categoryid['status'] =1;
		$categoryid['category_id'] =array('in',substr($categoryids,0,-1));		
		$category = $this ->table('sj_category')->where($categoryid)->field('category_id,name,status')->select();
		$category_all = array();
		foreach($category as $val){
			$category_all[$val['category_id']] = $val['name'];
		}
		//开发者名称
		$dev['dev_id'] = array('in',$devids);
		$dev_name = $this->table('pu_developer')->where($dev)->field('dev_id,dev_name,type,email,status')->select();
		$dev_all = array();
		foreach($dev_name as $m){
			$dev_all[$m['dev_id']] = $m;
		}
		//sj_soft_file表中的数据
		$file_where['softid']  = array('in',$softid);
		$file_where['package_status']  = array('exp'," > 0");
		$file_list = $this ->table('sj_soft_file')->where($file_where)->field('id,softid,advertisement,leafletname,iconurl,url,md5_file,sha1_file,ad_new')->select();
		$filearr = array();
		$md5sum_list = array();
		$sha1sum_list = array();
		foreach($file_list as $key => $val){
			$sfid[] = $val['id'];
			if($val['md5_file']){
				$md5sum_list[] = $val['md5_file'];
			}
			if($val['sha1_file']){
				$sha1sum_list[] = $val['sha1_file'];
			}
		}
		$md5_adinfo = $soft_tmp -> getAdsByHash($md5sum_list);
		$sha1_adinfo = $soft_tmp -> getAdsByHash($sha1sum_list);

		foreach($file_list as $key => $val){
			//无广告
			$str = '';
			$last_refresh = $val['last_refresh'] ? date("Y-m-d H:i:s",$val['last_refresh']):'';
			if($val['leafstatus'] == 2){
				$str = "无广告<br/><来自于金山><br/>".$last_refresh."<br/>无广告<br/><来自于腾讯><br/>".$last_refresh."<br/>";
			}else if($val['leafstatus'] == 0){
				$str = "未打标<br/>".$last_refresh;
			}		
			$val['sha1_a dinfo'] = $sha1_adinfo[$val['sha1_file']].$str;
			$val['md5_adinfo'] = $md5_adinfo[$val['md5_file']];
			$filearr[$val['softid']] =  $val;
		}
		$data = array();
		$data['package'] =array('in',$package);
		$note = $this ->table('sj_soft_note') -> where($data) -> select();
		$note_all = array();	
		foreach($note as $n){
			$note_all[$n['package']] = $n;
		}				
		foreach($soft_list as $k => $v){
			//图标--广告
 			$softlist[$k]['advertisement'] = $soft_tmp->ad($filearr[$v['softid']]['ad_new']);
			$softlist[$k]['leafletname'] = $filearr[$v['softid']]['leafletname'];
			$iconurl =  $filearr[$v['softid']]['iconurl'];
			$softlist[$k]['iconurl'] = $iconurl;
			$softlist[$k]['url'] = $filearr[$v['softid']]['url'];
			$fileid = $filearr[$v['softid']]['id'];
			//推广商
			if(!empty($filearr[$v['softid']]['sha1_adinfo'])){
				$softlist[$k]['scan'] = $filearr[$v['softid']]['sha1_adinfo'];
			}else{
				$softlist[$k]['scan'] = '';
			}
			//屏蔽
			if($note_all[$v['package']]['shield_start'] <= time() && $note_all[$v['package']]['shield_end']>= time()){
				$softlist[$k]['shield'] = $note_all[$v['package']]['shield'];
			}			
			//积分、广告
			$softlist[$k]['scan1'] =  $filearr[$v['softid']]['md5_adinfo'];				
			
			$categoryid = substr("{$v['category_id']}",1,-1);
			$softlist[$k]['category_name'] = $category_all[$categoryid];
			//type 0公司 1个人 2团队
			if(!empty($v['dev_id'])){
				$softlist[$k]['dev_type'] = $dev_all[$v['dev_id']]['type'];
				$softlist[$k]['dever_email'] = $dev_all[$v['dev_id']]['email'];
				$softlist[$k]['dev_name'] = $dev_all[$v['dev_id']]['dev_name'];
				$softlist[$k]['dev_status'] = $dev_all[$v['dev_id']]['status'];
			}
		}
		//驳回原因
		$reason_list = $this -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 5 ))->order('pos asc,id desc')->select();
		return array($softlist,$total, $Page,$reason_list,$packages);
	}
	//下架前检测是否在排期内
	public function ajax_pro_soft($packages) { 
		$model = new Model();
		$shelf_tm = time ();
		$extent_soft_sql = "select count(*) as total from sj_extent_soft where status='1' and  (start_at<=$shelf_tm and  end_at>=$shelf_tm) and package='{$packages}'";
		
		$extent_soft_sql_v1 = "select count(*) as total from sj_extent_soft_v1 where status='1' and  (start_at<=$shelf_tm and  end_at>=$shelf_tm) and package='{$packages}'";
		
		$category_extent_soft = "select count(*) as total from sj_category_extent_soft where status='1' and  (start_at<=$shelf_tm and  end_at>=$shelf_tm) and package='{$packages}'"; 
		
		$necessary_extent_soft_sql = "select count(*) as total from sj_necessary_extent_soft where status='1' and  (start_at<=$shelf_tm and  end_at>=$shelf_tm) and package='{$packages}'";
		
		$sj_ad_sql = "select count(*) as total from sj_ad where status='1' and ad_type='2' and  (begintime<=$shelf_tm and endtime>=$shelf_tm) and package='{$packages}'";
		$query_extent_soft = $model->query ($extent_soft_sql);
		$query_extent_soft_v1 = $model->query ($extent_soft_sql_v1);
		$query_category = $model->query ($category_extent_soft);
		$query_necessary = $model->query ($necessary_extent_soft_sql);
		$query_ad = $model->query ($sj_ad_sql);
		

		if ($query_extent_soft[0]['total'] > 0 || $query_extent_soft_v1[0]['total'] > 0 || $query_category[0]['total'] > 0 || $query_necessary[0]['total'] > 0 || $query_ad[0]['total'] > 0) { //如果在排期内返回 
			return true;
		} else {
			return false;
		}
	}
	//原因表添加
	public function reason_Operation($type,$type_only,$content,$admin_id){
		$model = M('reason');
		$where = array();
		$where['type'] = $type;
		$where['type_only'] = $type_only;
		$list = $model -> where($where)->find();
		$map = array();
		$map['content'] = $content;
		$map['admin_id'] = $admin_id;
		$map['update_tm'] = time();
		if($list){
			$model->where($where)->save($map);
		}else{
			$map['type'] = $type;
			$map['type_only'] = $type_only;
			$map['add_tm'] = time();
			$model->add($map);
		}
	}
	//获取原因数据
	public function get_reason_content($type,$type_only){
		$model = M('reason');
		$where = array();
		$where['type'] = $type;
		$where['type_only'] = array('in',$type_only);
		$list = $model -> where($where)->select();
		$reason = array();
		foreach($list  as $key => $val){
			$reason[$val['type_only']] = $val;
		}
		return $reason;
	}

	//屏蔽列表
	public function getsoft_shield($params, $limit){
		//搜索功能
		$where = array();
		$where['shield'] = 1;
		$where['shield_end'] = array("egt",time());
		if(isset($params['softid']) || isset($params['softname']) || isset($params['dev_id']) || isset($params['dev_name']) || isset($params['email']) || isset($params['dev_type']) || (isset($params['uplode']) && isset($params['uplode1'])) || $params['cateid']){
			$where_s = "status=1 and (hide=1 or hide=3)";
			$where_t = "record_type<=3 and (status !=1 and status != 0)";
			if (isset($params['softid'])) {
				$where_s .= " and softid={$params['softid']}";
				$where_t .= " and (softid={$params['softid']} or update_from = {$params['softid']})";
			}	
			if (isset($params['softname'])) {
				$where_s .= " and softname like '%{$params['softname']}%'";
				$where_t .= " and softname like '%{$params['softname']}%'";
			}	
			if (isset($params['dev_id'])) {			
				$where_s .= " and dev_id = {$params['dev_id']}";
				$where_t .= " and dev_id = {$params['dev_id']}";
			}
			if(isset($params['dev_name']) || isset($params['email']) || isset($params['dev_type'])){
				if(isset($params['email']) && $params['email']!=''){
					$params['email'] = trim($params['email']);
					$wheres['email'] = array("eq","{$params['email']}");
				}
				if(isset($params['dev_name']) && $params['dev_name']!=''){	
					$wheres['dev_name'] = array('like',"%{$params['dev_name']}%");		
				}
				if(isset($params['dev_type'])){
					$wheres['type'] = array("eq","{$params['dev_type']}");
				}
				$devname = $this->table('pu_developer')->where($wheres)->field('dev_id')->select();
				$dev_id = '';
				foreach ($devname as $n => $m ){
					$dev_id .= $m['dev_id'].",";
				}
				$dev_id =substr($dev_id,0,-1);
				$where_s .= " and dev_id in ({$dev_id})";
				$where_t .= " and dev_id in ({$dev_id})";
			}		
			//下载量搜索
			if(isset($params['uplode']) && isset($params['uplode1'])){
				$where_s .= " and total_downloaded+total_downloaded_add-total_downloaded_detain >= {$params['uplode']} and total_downloaded+total_downloaded_add-total_downloaded_detain <= {$params['uplode1']}";
				$where_t .= " and total_downloaded+total_downloaded_add-total_downloaded_detain >= {$params['uplode']} and total_downloaded+total_downloaded_add-total_downloaded_detain <= {$params['uplode1']}";
			}	
			if(!empty($params['cateid'])){
				$cateids = explode(',',$params['cateid']);
				$cateidarr = '';
				foreach($cateids as $vv){
					if($vv != ''){
						$cateidarr .= "',".$vv.",',";
					}
				}
				$cateidarr = substr($cateidarr,0,-1);
				$where_s .= " and category_id in ({$cateidarr})";
				$where_t .= " and category_id in ({$cateidarr})";
			}				
			$package1 = $this->table('sj_soft')->where($where_s)->field('package')->select();	
			$package2 =  $this->table('sj_soft_tmp')->where($where_t)->field('package')->select();		
			if($package1 and $package2){
				$package = array_merge($package1,$package2);
			}else if($package1){
				$package = $package1; 
			}else if($package2){
				$package = $package2;
			}
			$package_arr = array();
			foreach($package as $v){
				$package_arr[] = $v['package'];
			}
			$where['package'] = array("in",$package_arr);
		}
		if(isset($params['package'])){
			$where['package'] = trim($params['package']);
		}	
		if(!empty($params['begintime']) && !empty($params['endtime'])){
			$begintime = strtotime($params['begintime']);
			$endtime = strtotime($params['endtime']);
			$where_r = array();
			$where_r['_string'] = "add_tm >= {$begintime} and add_tm <= {$endtime} and type='sj_soft_note'";
			$time = $this->table('sj_reason')->where($where_r)->select();
			foreach($time as $val){
				$package3 .= "'".$val['type_only']."',";
			}
			$package3 = substr($package3,0,-1);
			$where['package'] = array("in",$package3);
		}	
		//分页
		import('@.ORG.Page2');
		$param = http_build_query($params);
		$total = $this -> table('sj_soft_note') -> where($where)->count();
		$Page = new Page($total,$limit,$param);		
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');			
		$note_list = $this -> table('sj_soft_note') -> where($where)->field('shield_start,shield_end,package')->limit($Page->firstRow.','.$Page->listRows)->select();
		$package_arr = array();
		foreach($note_list as $k=>$v){
			$package_arr[] = $v['package']; 
		}	
		$where = array(
			'package'=>array('in',$package_arr),
			'channel_id'=> '',
		//	'status'=> 1,
			'hide' => array('in',array(1,3)),
		);
		$field = "hide,softid,softname,package,category_id,dev_id,version,version_code,language,hide_prev,last_refresh,update_type,total_downloaded,total_downloaded_add,total_downloaded_detain,status,update_from";
		$soft_info = get_table_data($where,"sj_soft","package",$field,"softid asc");
		$where = array(
			'status'=>array('exp',"!=1 and status !=0"),
			'record_type' => array('exp',"<=3"),
			'package'=>array('in',$package_arr),			
		);
		$field = "id,record_type,softid,softname,package,category_id,dev_id,version,version_code,language,hide_prev,last_refresh,update_type,total_downloaded,total_downloaded_add,total_downloaded_detain,status,update_from";
		$soft_info_tmp = get_table_data($where,"sj_soft_tmp","package",$field,"id asc");
		$softid = array();
		$devids = array();
		$categoryids = array();		
		foreach($soft_info as $k =>$v){
			if($v['softid']) $softid[] = $v['softid'];
			if(!empty($v['dev_id'])) $devids[] = $v['dev_id'];	
			$categoryids[] = substr("{$v['category_id']}",1,-1);				
		}
		$tid = array();
		foreach($soft_info_tmp as $k =>$v){
			$tid[] = $v['id'];
			if(!empty($v['dev_id'])) $devids[] = $v['dev_id'];	
			$categoryids[] = substr("{$v['category_id']}",1,-1);
		}
		//sj_soft_file表中的数据	
		if($softid){
			$where = array(
				'softid'=>array('in',$softid),
				'package_status' => array('exp',">0"),
			);
			$soft_file = get_table_data($where,"sj_soft_file","softid","softid,iconurl");		
		}
		//sj_soft_file_tmp表中的数据	
		if($tid){
			$where = array(
				'tmp_id'=>array('in',$tid),
				'package_status' => array('exp',">0"),
			);
			$soft_file_tmp = get_table_data($where,"sj_soft_file_tmp","tmp_id","tmp_id,iconurl");		
		}	
		//开发者名称	
		if($devids){
			$devids = array_unique($devids);
			$where = array(
				'dev_id'=>array('in',$devids),
			);
			$developer_info = get_table_data($where,"pu_developer","dev_id","dev_id,dev_name,type,email,status");			
		}
		//类别名称
		if($categoryids){
			$categoryids = array_unique($categoryids);
			$where = array(
				'status'=>1,
				'category_id'=>array('in',$categoryids),
			);
			$category_info = get_table_data($where,"sj_category","category_id","category_id,name,status");			
		}
		//获取原因内容
		$reason = $this->get_reason_content('sj_soft_note',$package_arr);	
		$return_arr = array(
			'note_list' => $note_list,
			'soft_info' => $soft_info,
			'soft_info_tmp' => $soft_info_tmp,
			'soft_file' => $soft_file,
			'soft_file_tmp' => $soft_file_tmp,
			'developer_info' => $developer_info,
			'category_info' => $category_info,
			'reason' => $reason
		);
		return array($total,$Page,$return_arr);
	}
	//获取排期
	public function get_Schedule($id_arr,$is_tmp){
		$model = new Model();
		$where = array();
		if($is_tmp == "tmp"){
			$where['id'] = array('in',$id_arr);
			$package = $model -> table('sj_soft_tmp') -> where($where)-> field('package,softname') -> select();
		}else{
			$where['softid'] = array('in',$id_arr);
			$package = $model -> table('sj_soft') -> where($where)-> field('package,softname') -> select();
		}
		//echo $model->getlastsql();
		$return = "";
		foreach($package as $v){
			$ret = $this ->filterWhiteListSoft($v['package'],$v['softname']);
			if($ret) $return .=  $ret;
		}
		return $return;	
	}
	//山寨 盗版 过滤白名单软件
	public function filterWhiteListSoft($package,$soft_name){
	    $result	= '';
	    $model	= new Model();
	    $time	= time();
	    //运营白名单
	    $whiteSoft = $model->table('sj_soft_whitelist')->where("package='{$package}' and status=1")-> field('*') ->find();
	    if($whiteSoft){
			$result .= "{$package}，{$soft_name}的软件在运营白名单中<br>";
	    }
	    //商务白名单
	    $business = $model -> table('sj_safe_white_package') ->where("package='{$package}' and status=1")-> field('*') ->find();
	    if($business){
			$result .= "{$package}，{$soft_name}的软件在商务白名单中<br>";
	    }
	    //排期中
	    if($this->ajax_pro_soft_2($package)){
	        $result .= "{$package}，{$soft_name}的软件在广告排期中<br>";
	    }
	    //官标
	    $note = $model->table('sj_soft_note')->where("package='{$package}'")-> field('*') ->find();
	    if($note && $note['status']==1 && $note['terminal_time'] > $time ){
	        $result .= "{$package}，{$soft_name}的软件在官标中<br>";
	    }
	    //角标
	    if($note && $note['type']>0 && $note['type_end'] > $time ){
	        $result .= "{$package}，{$soft_name}的软件在角标中<br>";
	    }
		//首发
		$soft_debut = $model->table('sj_soft_debut') -> where("package = '{$package}' and status=2 and debut_time+(debut_length*3600) >= '{$time}' and del_status=1")->find();
		if($soft_debut){
			 $result .= "{$package}，{$soft_name}的软件在首发中<br>";
		}	
		//闪屏
		$soft_screen = $model->table('sj_soft_screen') -> where("package = '{$package}' and status=2")->find();
		if($soft_screen){
			 $result .= "{$package}，{$soft_name}的软件在闪屏中<br>";
		}
		return $result;
	
	}
	//检测是否有排期
	public function ajax_pro_soft_2($packages) {
	    $model = new Model();
	    $shelf_tm = time ();
	    $extent_soft_sql = "select count(*) as total from sj_extent_soft where status='1' and  end_at>=$shelf_tm and package='{$packages}'";
	
	    $extent_soft_sql_v1 = "select count(*) as total from sj_extent_soft_v1 where status='1' and  end_at>=$shelf_tm and package='{$packages}'";
	
	    $category_extent_soft = "select count(*) as total from sj_category_extent_soft where status='1' and  end_at>=$shelf_tm and package='{$packages}'";
	
	    $necessary_extent_soft_sql = "select count(*) as total from sj_necessary_extent_soft where status='1' and  end_at>=$shelf_tm and package='{$packages}'";
	
	    $sj_ad_sql = "select count(*) as total from sj_ad where status='1' and ad_type='2'  and endtime>=$shelf_tm and package='{$packages}'";
	    $query_extent_soft = $model->query ($extent_soft_sql);
	    $query_extent_soft_v1 = $model->query ($extent_soft_sql_v1);
	    $query_category = $model->query ($category_extent_soft);
	    $query_necessary = $model->query ($necessary_extent_soft_sql);
	    $query_ad = $model->query ($sj_ad_sql);
	
	
	    if ($query_extent_soft[0]['total'] > 0 || $query_extent_soft_v1[0]['total'] > 0 || $query_category[0]['total'] > 0 || $query_necessary[0]['total'] > 0 || $query_ad[0]['total'] > 0) { //如果在排期内返回
	        return true;
	    } else {
	        return false;
	    }
	}
	/************检测排期只在审核中列表和上架列表****************/
	public function soft_WhiteList($packages){
	    $shelf_tm = time ();
		$return = array();
		$package = '';
		foreach($packages as $v){
			if($v) $package .= ",'".$v."'";
		}
		$package = substr($package,1);
		$extent = $this -> table('sj_extent_soft')->where("status='1' and  end_at >=$shelf_tm and package in ($package)")->field('package')->select();
		foreach((array)$extent as $v){
			$return[$v['package']] = 1;
		}
		unset($extent);
		
		$extent_soft_sql_v1 = $this -> table('sj_extent_soft_v1')->where("status='1' and  end_at >=$shelf_tm and package in ( $package )")->field('package')->select();
		foreach((array)$extent_soft_sql_v1 as $v){
			$return[$v['package']] = 1;
		}
		unset($extent_soft_sql_v1);
		$category_extent_soft = $this -> table('sj_category_extent_soft')->where("status='1' and  end_at >=$shelf_tm and package in ( $package )")->field('package')->select();
		foreach((array)$category_extent_soft as $v){
			$return[$v['package']] = 1;
		}
		unset($category_extent_soft);
		
		$necessary_extent_soft = $this -> table('sj_necessary_extent_soft')->where("status='1' and  end_at >=$shelf_tm and package in ( $package )")->field('package')->select();
		foreach((array)$necessary_extent_soft as $v){
			$return[$v['package']] = 1;
		}
		unset($necessary_extent_soft);
		
		$sj_ad = $this -> table('sj_ad')->where("status='1' and  endtime >=$shelf_tm and package in ( $package )")->field('package')->select();
		foreach((array)$sj_ad as $v){
			$return[$v['package']] = 1;
		}
		unset($sj_ad);
	    //运营白名单
	    $whiteSoft = $this->table('sj_soft_whitelist')->where("package in ( $package ) and status=1")-> field('package') ->select();	
	    foreach((array)$whiteSoft as $v){
			$return[$v['package']] = 1;
	    }
		unset($whiteSoft);
	    //商务白名单
	    $business = $this -> table('sj_safe_white_package') ->where("package in ( $package ) and status=1")-> field('package') ->select();	
	    foreach((array)$business as $v){
			$return[$v['package']] = 1;
	    }
		unset($business);
	    //官标---角标
	    $note = $this->table('sj_soft_note')->where("package in ( $package )")-> field('package,status,terminal_time,type,type_end') ->select();	
	    foreach((array)$note as $v){
			if($v['status']==1 && $v['terminal_time'] > $shelf_tm ){
				$return[$v['package']] = 1;
			}
			//角标
			if($v['type']>0 && $v['type_end'] > $shelf_tm ){
				$return[$v['package']] = 1;
			}			
	    }
		unset($note);
		//首发
		$soft_debut = $this->table('sj_soft_debut') -> where("package in ( $package ) and status=2 and debut_time+(debut_length*3600) >= '{$shelf_tm}' and del_status=1")-> field('package') ->select();	
	    foreach((array)$soft_debut as $v){
			$return[$v['package']] = 1;
	    }	
		unset($soft_debut);
		//闪屏
		$soft_screen = $this->table('sj_soft_screen') -> where("package in ( $package ) and status=2")-> field('package') ->select();	
	    foreach((array)$soft_screen as $v){
			$return[$v['package']] = 1;
	    }	
		unset($soft_screen);
		
		return $return;
	}
	//更新运营白名单 是否上线
	public function updateWhitelistOnline($data,$type){
	    $model = new Model();
	    if($type == 1){
	        $sql = "update sj_soft_whitelist set  is_online = 1,dev_id= {$data['dev_id']}   where package='{$data['package']}'";
	    }else{
	        $sql = "update sj_soft_whitelist set  is_online = 0  where package='{$data['package']}'";
	    }				
		return $model->query ($sql);
	}
	//软件图标
	function  new_icon_list($tmpid,$package){
		$model = new Model();
		$where = array();
		if($tmpid){
			$where['tmpid'] = array('in',$tmpid);
			$tmp_icon = $model -> table('sj_icon_tmp')->where($where)->field('tmpid,iconurl')->select();
			$tmp_icon_list = array();
			foreach($tmp_icon as $k => $v){
				$tmp_icon_list[$v['tmpid']] = $v;
			}
		}
		if($package){
			$where['package'] = array('in',$package);
			$icon = $model -> table('sj_icon')->where($where)->field('package,iconurl')->select();
			$icon_list = array();
			foreach($icon as $k => $v){
				$icon_list[$v['package']] = $v;
			}
		}
		return array('0'=>$tmp_icon_list,'1'=>$icon_list);
	}

	//软件屏蔽，获取上架时间
	function get_pass_time($id,$key='id'){
		$id = implode(',',$id);
		$pass_time = get_table_data(array("id"=>array('in',"{$id}")),"sj_soft_tmp","{$key}","id,package,pass_time");
		$package = '';
		$model = new Model();
		foreach($pass_time as $k=>$v){
			$package .="'{$v['package']}',";
		}
		$package = substr($package,0,-1);
		$package_category =  get_table_data(array("package"=>array("in",$package),'status'=>1),"sj_soft_whitelist","package","id,package,fen_lei");
		foreach($package_category as $k=>$v){
			//单机任然走定时上架流程，其他屏蔽后直接上架
			if(!in_array($v['fen_lei'],array('网游','棋牌'))){
				foreach($pass_time as $p_k=>$p_v){
					if($v['package']==$p_v['package']){
						$p_v['pass_time'] = '';
					}
					$pass_time[$p_k] = $p_v;
				}
			}
		}
		return $pass_time;
	}
}
?>
