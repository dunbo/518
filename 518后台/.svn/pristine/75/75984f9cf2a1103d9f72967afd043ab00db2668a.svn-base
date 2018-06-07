<?php
define('ABI_ARMEABI', 1);
define('ABI_ARMEABI_V7A', 2);
define('ABI_X86', 4);
define('ABI_MIPS', 8);
class ExportModel extends Model {	
	//获取soft数据
	public function getsoft_export($hide,$params,$limit,$p){
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
		
		if(isset($params['softid'])){ $where .= " and s.softid ='{$params['softid']}'"; }
		if(isset($params['dev_id'])){ $where .= " and s.dev_id ='{$params['dev_id']}'"; }	
		if(isset($params['softname'])){	$where .= " and s.softname like '%{$params['softname']}%'"; }
		if(isset($params['package'])){
			$params['package'] = trim($params['package']);
			$where .= " and s.package='{$params['package']}'";
		}else{
			
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
			$begintime = strtotime(urldecode($params['begintime']));
			$endtime = strtotime(urldecode($params['endtime']));
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
		if(isset($params['safe'])){
			if($params['safe'] == 1){
				$where .=" and s.safe <=1";
			}elseif($params['safe'] == 2){
				$where .=" and s.safe >1";
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
		//-------分页
		if(isset($params['count'])){
			$total = $params['count'];
		}else{	
			if(!empty($params['Official']) || isset($params['type']) || isset($params['shield_status'])){		
				$total = $this->table('sj_soft s')->join("sj_soft_note n ON n.package = s.package")->where($where)->count();
			}else{		
				$total = $this->table('sj_soft s')->where($where)->count();
			}
		}
		import('@.ORG.Page2');
		$param = http_build_query($params);		
		//$Page = new Page($total,$limit,$param);
		$totalPages = ceil($total/$limit);
		if($p == 1){
			$firstRow = 0;
		}else{
			$firstRow = ($p-1) * $limit;
		}
		if(!empty($params['Official']) || isset($params['type']) || isset($params['shield_status']) || isset($params['game_charge']) || isset($params['comment']) || isset($params['azjx_type'])){		
			$soft_list = $this->table('sj_soft s')->join("sj_soft_note n ON n.package = s.package")->where($where)->field('s.*,n.*,n.status as status_n,n.type as type_n,n.shield as shield_n')->limit($firstRow.','.$limit)->order($order_str)->select();
		}else{
			$soft_list = $this->table('sj_soft s')->where($where)->limit($firstRow.','.$limit)->order($order_str)->select();
		}
		//广告信息
		$devids = array();
		$package = array();
		$softid = array();
		$categoryids = '';
		//整合数据
		foreach ($soft_list as $k => $v){		
			$categoryids .= substr("{$v['category_id']}",1);
			if(!empty($v['dev_id'])) $devids[] = $v['dev_id'];
			if(!empty($v['package'])) $package[] = $v['package'];
			if(!empty($v['softid'])) $softid[] = $v['softid'];
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
		if(empty($params['Official']) && !isset($params['type']) && !isset($params['shield_status'])){
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
			$md5_adinfo = $soft_tmp -> getAdsByHash($md5sum_list);
			$sha1_adinfo = $soft_tmp -> getAdsByHash($sha1sum_list);
			$scan_result_hash_tmp = $soft_tmp -> getbyhash($md5sum_list);
			$sha1sum_tmp = $soft_tmp -> getbyhash($sha1sum_list);
			foreach($file_list as $key => $val){
				$val['sha1_adinfo'] = $sha1_adinfo[$val['sha1_file']];
				$val['md5_adinfo'] = $md5_adinfo[$val['md5_file']];
				//安全扫描
				$val['sha1_adinfo_t'] = $sha1sum_tmp[$val['sha1_file']];
				$val['md5_adinfo_t'] = $scan_result_hash_tmp[$val['md5_file']];
				$filearr[$val['softid']] =  $val;
			}
		}
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
			$heade = array('ID','软件名','包名','软件类型','官方认证','合作渠道','语言','软件来源','角标状态','版本名','屏蔽状态','ABI','x86支持','安全状态','开发者名称','开发者ID','个人、公司','邮箱','总量','扣量','增量','剩余量','关键字','更新','简介','广告扫描','更新时间','仅搜索显示');		
			fputcsv($file, $heade);
		}	
		foreach($soft_list as $k => $v){
			$shield = '';
			$offic = '';	
			$type_name = '';	
			$terrace = '';
			$put_arr = array();
			$put_arr['softid'] = $v['softid'] ? $v['softid'] : "\t";
			$put_arr['softname'] = $v['softname'] ? $v['softname'] : "\t";
			$put_arr['package'] = $v['package'] ? $v['package'] : "\t";
			$categoryid = substr("{$v['category_id']}",1,-1);
			$put_arr['category_name'] = $category_all[$categoryid] ? $category_all[$categoryid] : "\t";
			if(!empty($params['Official']) || isset($params['type']) || isset($params['shield_status'])){		
				//官方认证--角标类型
				if($v['start_time'] <= time() && $v['terminal_time'] >= time()){
					if($v['status_n'] ==1) $offic = "官方";
				}
				if($v['type_start'] <= time() && $v['type_end'] >= time()){
					$type_name = $mark[$v['type_n']];
				}
				//屏蔽
				if($v['shield_start'] <= time() && $v['shield_end']>= time() && $v['shield_n'] == 1){
						$shield = "屏蔽";
				}
			}else{
				//官方认证--角标类型
				if($note_all[$v['package']]['start_time'] <= time() && $note_all[$v['package']]['terminal_time'] >= time()){
					if($note_all[$v['package']]['status'] ==1) $offic = "官方";
				}
				if($note_all[$v['package']]['type_start'] <= time() && $note_all[$v['package']]['type_end'] >= time()){
					$type_name = $note_all[$v['package']]['type_name'];
				}
				//屏蔽
				if($note_all[$v['package']]['shield_start'] <= time() && $note_all[$v['package']]['shield_end']>= time() && $note_all[$v['package']]['shield'] == 1 && !empty($note_all[$v['package']]['shield'])){
						$shield = "屏蔽";	
				}

			}	
			$put_arr['offic'] =	$offic ? $offic : "\t";	
			if($v['terrace'] ==1 ){
				$terrace = "LG_TV";
			}	
			$put_arr['terrace'] =	$terrace ? $terrace : "\t";				
			if($v['language'] == 1){
				$language = "中文";
			}elseif($v['language'] == 2){
				$language = "英文";
			}elseif($v['language'] == 3){
				$language = "其他";
			}else{
				$language = "\t";
			}
			$put_arr['language'] = $language;
			if($v['update_type'] == 1){
				$update_type = "后台";
			}elseif($v['update_type'] == 2){
				$update_type = "开发者";
			}elseif($v['update_type'] == 3){
				$update_type = "采集";
			}elseif($v['update_type'] == 4){
				$update_type = "批量上传";
			}else{
				$update_type = "未知";
			}
			$put_arr['update_type'] =$update_type;
			$put_arr['type_name'] = $type_name ? $type_name : "\t";	
			$put_arr['version'] = $v['version'] ? $v['version'] : "\t";	
			$put_arr['shield'] = $shield ? $shield : "\t";	 
			//abi显示
			$abis = '';
			foreach($known_abis as $abi_key => $abi_value){
				if($abi_value & $v['abi'] || $v['abi'] == 0){
					$abis .= $abi_key.",";
				}
			}		
			$put_arr['abis'] = $abis ? $abis : "\t";
			if ($v['abi'] == 0 || ($v['abi'] & ABI_X86) == ABI_X86){
				$x86 = "支持x86";
			}else{
				$x86 = "不支持x86";
			}
			$put_arr['x86'] = $x86;
			
			if($v['safe'] >1){
				$safe = "不安全";
			}else{
				$safe = "安全";
			}
			$put_arr['safe'] = $safe ;
			//type 0公司 1个人 2团队
			$put_arr['dev_name'] = $dev_all[$v['dev_id']]['dev_name'] ? $dev_all[$v['dev_id']]['dev_name'] : "\t";
			$put_arr['dev_id'] = $v['dev_id'];
			if($dev_all[$v['dev_id']]['type']  ==1){
				$dev_type = "个人";
			}else if($dev_all[$v['dev_id']]['type']  ==0){
				$dev_type = "公司";
			}
			$put_arr['dev_type'] = $dev_type ? $dev_type : "\t";
			$put_arr['dever_email'] = $dev_all[$v['dev_id']]['email'];
			//总下载量
			$put_arr['total_downloaded'] = $v['total_downloaded'];
			//扣量数量
			$put_arr['total_downloaded_detain'] = $v['total_downloaded_detain'];
			//增量
			$put_arr['total_downloaded_add'] = $v['total_downloaded_add'];
			//剩余量
			$put_arr['total_downloaded_surplus'] = number_format($v['total_downloaded']-$v['total_downloaded_detain']+$v['total_downloaded_add']);
			$put_arr['tags'] = $v['tags'] ? str_replace(",","",$v['tags']) : "\t";
			$replace = array(",","\r\n");
			$update_content = str_replace($replace,"",$v['update_content']);
			$update_content = str_replace("\n","",$update_content);			
			$put_arr['update_content'] = $v['update_content'] ? $update_content : "\t";
			//stripslashes()函数的功能与addslashes()‍正好相反，它的功能是去除转义的效果。 
			$intro = str_replace($replace,"",$v['intro']);
			//$intro = str_replace("\n","",stripslashes($intro));
			$intro = str_replace("\n","",$intro);
			$put_arr['intro'] = $v['intro'] ? $intro : "\t";
			//安全扫描
			$scan_result = $filearr[$v['softid']]['sha1_adinfo'].$filearr[$v['softid']]['md5_adinfo'];
			$put_arr['scan_result'] = $scan_result ? $scan_result : "\t";	
			$put_arr['last_refresh'] = $v['last_refresh'] ? date("Y-m-d H:i:s",$v['last_refresh']) : "\t";
			if($v['only_search'] == 1){
				$only_search = "是";
			}else{
				$only_search = "否";
			}
			$put_arr['only_search'] = $only_search;					
			fputcsv($file, $put_arr);				
		}
		fclose($file);			
		$next_page = $p + 1;
		if ($p != $totalPages) {
			$par = $_GET;
			unset($par['page'],$par['button'],$par['fid'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/Soft/softlist_export/page/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}",
			);	
		}
		return $data;
	}	
	public function get_soft_feedback($params,$limit,$p) {
        $feedback_db=M('feedback');
		$soft_db = M('soft');
		$where['status'] = 1;
		$where['softid'] = array('exp','!=0');	
		if (isset($params['softid'])) $where['softid'] = $params['softid'];	
		if (isset($params['softname']) || isset($params['package'])) {
			if(isset($params['package'])){
				$softname['package'] = array('eq',trim($params['package']));
			}
			if(isset($params['softname'])){
				$softname['softname'] = array('like', '%'.escape_string($params['softname']).'%');
			}
			$s_name = $soft_db ->where($softname)->field('softid')->select();
			$softids = array();
			foreach($s_name as $k => $v){
				$softids[] =  $v['softid']; 
			}
			$where['softid'] = array('in',$softids);
		}	
		if (!empty($params['feedbacktype'])) $where['feedbacktype'] = $params['feedbacktype'];
		if (!empty($params['jbori'])) $where['jbori'] = $params['jbori'];		
		if (isset($params['content'])) 	$where['content'] = array('like', '%'.escape_string($params['content']).'%');	
		if(!empty($params['begintime']) && !empty($params['endtime'])){
			$begintime = strtotime(urldecode($params['begintime']));
			$endtime = strtotime(urldecode($params['endtime']));
			$where['submit_tm'] = array(array("egt",$begintime),array("elt",$endtime));
		}			
		//分页
		import("@.ORG.Page2");
		if($params['count']){
			$count = $params['count'];
		}else{
			$count= $feedback_db->where($where)->count();
		}
		$param = http_build_query($params);		
		$Page = new Page($count,$limit,$param);		
		$feedback_list = $feedback_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('submit_tm desc')->select();
		$softid = array();
		foreach($feedback_list as $key => $val){
			$softid[] = $val['softid']; 
		}
		$softid = array_unique($softid);
		//软件信息
		$map['softid'] = array('in', $softid);
		$soft_list = $soft_db ->where($map)->select();
		$softlist = array();
		foreach($soft_list as $k => $v ){
			$softlist[$v['softid']] = $v;
		}
		//举报类型
		$conf_db = D('Sj.Config');
        $feedbacktype_list = $conf_db->where('config_type="feedbacktype" and status=1')->getField('configname,configcontent');
		$feedback_source = array(
        	'1' => '市场举报',
        	'2' => '一键举报'
        );
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
			$heade = array('ID','软件名','包名','版本号','举报类型','举报内容','举报来源','IP','IMEI','举报时间');		
			fputcsv($file, $heade);
		}		
		foreach($feedback_list as $k => $v){
			$put_arr = array();
			$put_arr['softid'] = $v['softid'] ? $v['softid'] : "\t";
			$put_arr['softname'] = $softlist[$v['softid']]['softname'] ? $softlist[$v['softid']]['softname'] : "\t";
			$put_arr['package'] = $softlist[$v['softid']]['package'] ? $softlist[$v['softid']]['package'] : "\t";
			$put_arr['version_code'] = $v['version_code'] ? $v['version_code'] : "\t";
			$put_arr['configname'] = $feedbacktype_list[$v['feedbacktype']] ? $feedbacktype_list[$v['feedbacktype']] : "\t";
			$put_arr['content'] = $v['content'] ? $v['content'] : "\t";
			$put_arr['jbori'] = $feedback_source[$v['jbori']] ;
			$put_arr['ipmsg'] = $v['ipmsg'] ? $v['ipmsg'] : "\t";
			$put_arr['imei'] = $v['imei'] ? $v['imei'] : "\t";
			$put_arr['submit_tm'] = $v['submit_tm'] ? date("Y-m-d H:i:s",$v['submit_tm']) : "\t";
			fputcsv($file, $put_arr);		
		}
		fclose($file);			
		$next_page = $p + 1;
		if ($p != $Page->totalPages) {
			$par = $_GET;
			unset($par['p'],$par['button'],$par['fid'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/Message/soft_feedback_export/p/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}",
			);	
		}
		return $data;
	}
	public function get_soft_message($params,$limit,$p) {
		$comment_model = M('soft_comment');
    	$modelpost = M("post");
    	$modelthread = M("thread");
		$where = array();
		//$where['is_new'] = 1;	
		if (isset($params['status'])) $where['status'] = $params['status'];	
		if (isset($params['admin_show'])) $where['admin_show'] = $params['admin_show'];
		if (isset($params['softid'])) $where['softid'] = $params['softid'];
		if (isset($params['package'])) $where['package'] = $params['package'];
		if (isset($params['imei'])) $where['imei'] = $params['imei'];
		if (isset($params['ipmsg'])) $where['ipmsg'] = $params['ipmsg'];
		if (isset($params['pid'])) $where['pid'] = $params['pid'];
		if (isset($params['content'])) 
			$where['content'] = array("like","%{$params['content']}%");			
		if (isset($params['denymsg'])) 
			$where['denymsg'] = array("like","%{$params['denymsg']}%");	
		if (isset($params['user_name'])) 
			$where['user_name'] = array("like","{$params['user_name']}");
		if(isset($params['begintime']) && isset($params['endtime'])){
			$begintime = strtotime(urldecode($params['begintime']));
			$endtime = strtotime(urldecode($params['endtime']));
			if($params['status'] ==0){
				$key = "update_time";
			}else{
				$key = "create_time";
			}	
			$where[$key] = array(array("egt",$begintime),array("elt",$endtime));
		}else{
			//默认起止时间
			$day = strtotime(date("Y-m-d"));
			$begintime = strtotime(date('Y-m-d H:i:s', $day-7*86400));
			$endtime = strtotime(date('Y-m-d H:i:s', $day+86399));
			if($params['status'] ==0){
				$key = "update_time";
			}else{
				$key = "create_time";
			}	
			$where[$key] = array(array("egt",$begintime),array("elt",$endtime));			
		}	
		if (isset($_GET['admin_name'])) {
			$where_admin = array('admin_state'=>1);
			$where_admin['admin_user_name'] = array('like','%'.escape_string($_GET['admin_name']).'%');
			$admin_user_id = $admin_users->where($where_admin)->field('admin_user_id')->buildSql();
			$where['update_user_id'] = array('IN', $admin_user_id);
		}				
		if(!empty($params['beginscore']) && !empty($params['endscore'])){
			$beginscore = $params['beginscore'];
			$endscore = $params['endscore'];
			$where['score'] = array(array("egt",$beginscore),array("elt",$endscore));
		}	
		if (isset($_GET['softname'])) {
			$where_soft = array(
				'status' => 1,
				'hide' => 1,
			);
			$where_soft['softname'] = array('like', '%'.escape_string($_GET['softname']).'%');
			$subquery = $comment_model->table('sj_soft')->field('distinct package')->where($where_soft)->buildSql();
			$where['package'] = array('IN', $subquery);
		}	
		if($params['status'] == 0){
			$where['content'] = array("exp"," !='' ");		
			$where['admin_show'] = 1;		
			unset($where['processed']);
		}		
		if($params['count']){
			$total = $params['count'];
		}else{	
			$total = $comment_model->table('sj_soft_comment force index (create_time)')->where($where)->count();
		}
		if($params['admin_show'] == 1){
			$order = "create_time desc";
		}elseif($params['admin_show'] == 0 || $params['status'] ==0){
			$order = "update_time desc";
		}		
        import("@.ORG.Page2");
		$param = http_build_query($params);		
		$Page = new Page($total,$limit,$param);	

		$comment_list = $comment_model->table('sj_soft_comment force index (create_time)')->where($where)
				->limit($Page->firstRow.','.$Page->listRows)
				->order($order)
				->select();		
		//echo 	$comment_model->getlastsql();exit;	
		$did = array();
		$softid = array();
		$adminid = array();
		$comment_id = array();
		foreach ($comment_list as $key => $value) {
			if($value['did']) $did[] = $value['did'] ;
			if($value['softid']) $softid[] = $value['softid'];
			if($value['update_user_id']) $adminid[] = $value['update_user_id'];
			$comment_id[] = $value['id'];				
		}
		/*
		if($comment_id){
			$where_sj_post = array('comment_id' => array('in',$comment_id));	
			$res = $comment_model->table('sj_post')->where($where_d)->field('comment_id,system_userid')->select();
			$postres_again = array();
			foreach ($res as $value) {
				$postres_again[$value['comment_id']] = $value['system_userid'];
				$adminid[] = $value['system_userid'];
			}				
		}
		*/
		$where_d = array();
		$dinfo = array();
		if (!empty($did)) {
			$where_d['did'] = array('IN', $did);
			$res = $comment_model->table('pu_device')->where($where_d)->field('did,dname')->select();
			foreach ($res as $value) {
				$dinfo[$value['did']] = $value['dname'];
			}
		}
		$where_s = array();
		$softinfo = array();
		if (!empty($softid)) {
			$where_s['softid'] = array('IN',$softid);
			$res = $comment_model->table('sj_soft')->where($where_s)->field('softname, softid, version, version_code,hide')->select();
			foreach ($res as $value) {
				$softinfo[$value['softid']] = $value;
			}
		}
		$admin_users = M("admin_users");
		$admin_user_name = $admin_users->where(array('admin_user_id'=>array('in',$adminid)))->field('admin_user_name,admin_user_id')->select();
		$username = array();
		foreach($admin_user_name as $k=>$v){
			$username[$v['admin_user_id']] = $v['admin_user_name'];
		}	
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
			if($params['admin_show'] == 0 && isset($params['admin_show'])){
				$heade = array('ID','软件名','版本状态','IMEI','用户名','机型','IP','评论时间','积分','来源','版本号','评论信息','忽略时间');	
			}else if($params['status'] == 0){
				$heade = array('ID','软件名','版本状态','IMEI','用户名','机型','IP','评论时间','积分','来源','版本号','评论信息','删除原因','操作人员','时间');
			}else{
				$heade = array('ID','软件名','版本状态','IMEI','用户名','机型','IP','评论时间','积分','来源','版本号','评论信息','评论回复','回复时间');
			}
			fputcsv($file, $heade);
		}	
		foreach($comment_list as $k => $v){
			$put_arr = array();	
			$put_arr['softid'] = $v['softid'] ? $v['softid'] : "\t";
			$put_arr['softname'] = $softinfo[$v['softid']]['softname'] ? $softinfo[$v['softid']]['softname'] : "\t";
			if($softinfo[$v['softid']]['hide'] == 1){
				$hide = "上架";
			}else{
				$hide = "历史";
			}
			$put_arr['hide'] = $hide;
			$put_arr['imei'] = $v['imei'] ? $v['imei'] : "\t";
			$put_arr['user_name'] = ($v['userid']==13176) ? '安智网友' : $val['user_name'];
			$put_arr['did'] = $dinfo[$v['did']] ? $dinfo[$v['did']] : "\t";
			$put_arr['ipmsg'] = $v['ipmsg'] ? $v['ipmsg'] : "\t";
			$put_arr['create_time'] = $v['create_time'] ? date("Y-m-d H:i:s",$v['create_time']) : "\t";
			$put_arr['score'] = $v['score'] ? $v['score'] : "\t";
			if($v['pid'] == 1){
				$pid = "安智市场";
			}elseif($v['pid'] ==5){
				$pid = "安卓游戏";
			}
			$put_arr['pid'] = $pid ? $pid : "\t";
			$put_arr['version_code'] = $v['version_code'] ? $v['version_code'] : "\t";
			$put_arr['content'] = $v['content'] ? $v['content'] : "\t";
			if($params['admin_show'] == 0 && isset($params['admin_show'])){
				$put_arr['update_time'] = $v['update_time'] ? date("Y-m-d H:i:s",$v['update_time']) : "\t";
			}else if($params['status'] == 0){
				$put_arr['denymsg'] = $v['denymsg'] ? $v['denymsg'] : "\t";
				$put_arr['adminid'] = $username[$v['update_user_id']] ? $username[$v['update_user_id']] : "\t";			
				$put_arr['update_time'] = $v['update_time'] ? date("Y-m-d H:i:s",$v['update_time']) : "\t";
			}else{
				$ret = $modelthread->where("rid='{$v['id']}'")->field('tid,new_post')->find();
				if ($ret) {
					$postres = $modelpost->where(array('tid' => $ret['tid'], 'user_type' => 1))->field('dateline, contents')->order('dateline desc')->find();
					if($postres){
						$put_arr['reply'] = $postres['contents'] ;
						$put_arr['reply_tm'] = $postres['dateline'] ? date("Y-m-d H:i:s",$postres['dateline']) : '';
					}
				}
			}
			fputcsv($file, $put_arr);		
		}
		fclose($file);			
		$next_page = $p + 1;
		if ($p != $Page->totalPages) {
			$par = $_GET;
			unset($par['p'],$par['button'],$par['fid'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/Message/soft_message_export/p/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}",
			);	
		}
		return $data;
    }	
	public function get_feedback_suggestions($params,$limit,$p){
		$model = new Model();
		if(isset($params['status'])) $where['status'] = $params['status'];
		if(isset($params['last_status'])){
			if($params['last_status'] == 1){
				$where['last_status'] = array('gt',0);				
			}elseif($params['last_status'] == 2){
				$where['last_status'] = array('eq',0);
			}
		}
		if(isset($params['content']))
			$where['content'] = array("like","%{$params['content']}%");
		if(isset($_GET['start_at']) && isset($_GET['end_at'])){
			$start_at = strtotime(urldecode($params['start_at']));
			$end_at = strtotime(urldecode($params['end_at']));
			$where['create_tm'] = array(array('egt',$start_at),array('elt',$end_at));
		}
		if(isset($params['dev_name']) || isset($params['email'])){
			if(isset($params['email']) && $params['email']!=''){
				$params['email'] = trim($params['email']);
				$wheres['email'] = array("eq","{$params['email']}");
			}
			if(isset($params['dev_name']) && $params['dev_name']!=''){	
				$wheres['dev_name'] = array('like',"%{$params['dev_name']}%");	
			}
			$devname = $model->table('pu_developer')->where($wheres)->field('dev_id')->select();
			$dev_id = array();
			foreach ($devname as $n => $m ){
				$dev_id[] = $m['dev_id'];
			}
			$where['dev_id'] = array("in",$dev_id);
		}
		if (!isset($params['count'])) {
			$total = $model->table('dev_feedback')->where($where)->count();
		} else {
			$total = $params['count'];
		}
		import('@.ORG.Page2');
		$param = http_build_query($params);		
		$Page = new Page($total,$limit,$param);	
		$list  = $model->table('dev_feedback')->where($where)->order('dev_tm desc')->limit($Page->firstRow.','.$Page->listRows)->select();		
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
			if($params['last_status'] == 2 && $params['status'] == 1){
				$heade = array('开发者','开发者类型','Email','反馈内容','更新时间','标签');
			}else if($params['last_status'] == 1 && $params['status'] == 1){
				$heade = array('开发者','开发者类型','Email','反馈内容','最新回复内容','回复时间','标签');
			}else if($params['status'] == 0){
				$heade = array('开发者','开发者类型','Email','反馈内容','最新回复内容','删除时间','标签');
			}
			fputcsv($file, $heade);
		}
		$devid =  array();
		foreach($list as $val){
			$devid[] = $val['dev_id'];
		}
		//开发者信息
		$dev['status'] = 0;
		$dev['dev_id'] = array('in',$devid);
		$dev_name = $model->table('pu_developer')->where($dev)->field('dev_id,dev_name,type,email')->select();
		$dev_all = array();
		foreach($dev_name as $m){
			$dev_all[$m['dev_id']] = $m;
		}
		foreach($list as $k =>$v){
			$put_arr = array();
			$put_arr['dev_name'] = $dev_all[$v['dev_id']]['dev_name'] ? $dev_all[$v['dev_id']]['dev_name'] : "\t";
			if($dev_all[$v['dev_id']]['type'] == 0 && !empty($dev_all[$v['dev_id']]['dev_name'])){
				$type = "公司"; 
			}elseif($dev_all[$v['dev_id']]['type'] == 1 && !empty($dev_all[$v['dev_id']]['dev_name'])){
				$type = "个人"; 
			}
			$put_arr['type'] = $type ? $type : "\t";
			$put_arr['email'] = $dev_all[$v['dev_id']]['email'] ? $dev_all[$v['dev_id']]['email'] : "\t";
			$put_arr['content'] = $v['content'] ? $v['content'] : "\t";
			if($params['last_status'] == 2 && $params['status'] == 1){
				$put_arr['create_tm'] = $v['create_tm'] ? date("Y-m-d H:i:s",$v['create_tm']) : "\t";
				$put_arr['tag'] = $v['tag'] ? $v['tag'] : "\t";
			}else if($params['last_status'] == 1 && $params['status'] == 1){
				$reply_desc = $model -> table('dev_feedback_reply') -> field('id,fid,create_tm,content')->where(array('fid'=>$v['id']))->order('id desc')->find();
				$put_arr['desc_content'] = $reply_desc['content'] ? $reply_desc['content'] : "\t";
				$put_arr['reply_tm'] = $reply_desc['create_tm'] ? date("Y-m-d H:i:s",$reply_desc['create_tm']) : "\t";
				$put_arr['tag'] = $v['tag'] ? $v['tag'] : "\t";
			}else if($params['status'] == 0){
				$reply_desc = $model -> table('dev_feedback_reply') -> field('id,fid,create_tm,content')->where(array('fid'=>$v['id']))->order('id desc')->find();
				$put_arr['desc_content'] = $reply_desc['content'] ? $reply_desc['content'] : "\t";
				
				$put_arr['last_tm'] = $v['last_tm'] ?  date("Y-m-d H:i:s",$v['last_tm']) : "\t";
				$put_arr['tag'] = $v['tag'] ? $v['tag'] : "\t";
			}
			fputcsv($file, $put_arr);
		}			
		//file_put_contents($file, iconv("utf-8", "gbk", $str), FILE_APPEND);
		fclose($file);	
		$next_page = $p + 1;
		if ($p != $Page->totalPages) {
			$par = $_GET;
			unset($par['p'],$par['button'],$par['fid'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/InformationManagement/feedback_suggestions_export/p/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}",
			);	
		}
		return $data;
	}
	public function get_developer($params,$limit,$p){	
		$model =  new Model();
		$where = array();
		if(isset($params['status'])) $where['status'] = $params['status'];
		if(isset($params['email_verified'])) $where['email_verified'] = $params['email_verified'];
		if(isset($params['mobile_verified'])) {
			$where['mobile_verified'] = $params['mobile_verified'];
			$where['mobile'] = array('exp',"!=''");
		}	
		if(isset($params['dev_id'])) $where['dev_id'] = $params['dev_id'];
		if(isset($params['username'])){
			$userid = $model -> table("pu_user")->where(array("user_name" => $params['username']))->getfield("userid");
			$where['dev_id'] = $userid;
		}
		if(isset($params['truename'])) 
			$where['truename'] = array("like","%{$params['truename']}%");
		if(isset($params['type']) && $params['type']!=-1) 
			$where['type'] = $params['type'];
		if(isset($params['mobile'])) 
			$where['mobile'] = array("like","%{$params['mobile']}%");
		if(isset($params['dev_name'])) 
			$where['dev_name'] = array("like","%{$params['dev_name']}%");
		if(isset($params['cardnumber'])) 
			$where['cardnumber'] = array("like","%{$params['cardnumber']}%");
		if(isset($params['charter'])) 
			$where['charter'] = array("like","%{$params['charter']}%");
		if(!empty($params['location'])) 
			$where['location'] = $params['location'];
		if(isset($params['email'])) 
			$where['email'] = $params['email'];	
		if(isset($params['begintime']) && isset($params['endtime'])) {
			$begintime = strtotime(urldecode($params['begintime']));
			$endtime = strtotime(urldecode($params['endtime']));
			$where['register_time'] = array(array('egt',$begintime),array('elt',$endtime));
		}
		if(isset($params['statistics_on'])) {
			if($params['statistics_on'] == 1){
				$where['statistics_on'] =0;
			}else{
				$where['statistics_on'] =array('gt',0);
			}
		}		
		if(isset($params['ip'])) 	$where['reg_ip'] = $params['ip'];
		if (!isset($params['count'])) {
			$total = $model->table('pu_developer')->where($where)->count();
		} else {
			$total = $params['count'];
		}
		import("@.ORG.Page2");
		$param = http_build_query($params);		
		$Page = new Page($total,$limit,$param);	
		//获取IP下监控数据
		static $ip_num;
		if(!$ip_num){
			$ip_num = $model->table('sj_monitor_class')->where('class=2')->select();
		}	
		$ip_num_arr = array();
		foreach ($ip_num as $val) {
			$ip_num_arr[$val['ip']][] = $val['num'];
		}
		if($params['email_verified'] == 1 && $params['status'] ==1){
			$order = "register_time asc";
		}elseif($params['status'] ==0){
			$order = "register_time desc";
		}elseif($params['status'] == -1){
			$order = "dismissed_time desc";
		}elseif	($params['status'] == -2){	
			$order = "shield_time desc";
		}		
		$list = $model->table('pu_developer')->where($where)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();	
		//echo $model->getlastsql();exit;
		$devid = array();	 
		foreach($list as $k =>$v){	
			$devid[] =$v['dev_id'];
		}
		$user_name = $model -> table("pu_user")->where(array("userid" => array("in",$devid)))->field('user_name,userid')->select();
		$user_list = array();
		foreach($user_name as $k => $v){
			$user_list[$v['userid']] = $v['user_name'];
		}
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
			if($params['email_verified'] == 1 && $params['status'] ==1){
				$heade = array('ID','开发者','类型','Email','email是否验证','手机','手机验证','账号','所在地','联系人','身份证号','公司名称','营业执照','IP','注册次数','QQ','电话','网站','邮编','上次审核结果','申请时间');	
			}elseif($params['status'] ==0){
				$heade = array('ID','开发者','类型','Email','email是否验证','手机','手机验证','账号','所在地','联系人','身份证号','公司名称','营业执照','IP','注册次数','QQ','电话','网站','邮编','已上架','已下架','新软件审核','编辑审核','升级审核','通过时间');	
			}elseif($params['status'] == -1){
				$heade = array('ID','开发者','类型','Email','email是否验证','手机','手机验证','账号','所在地','联系人','身份证号','公司名称','营业执照','IP','注册次数','QQ','电话','网站','邮编','驳回原因','驳回时间');		
			}elseif	($params['status'] == -2){	
				$heade = array('ID','开发者','类型','Email','email是否验证','手机','手机验证','账号','所在地','联系人','身份证号','公司名称','营业执照','IP','注册次数','QQ','电话','网站','邮编','已上架','已下架','新软件审核','编辑审核','升级审核','屏蔽时间','屏蔽原因');				
			}			
			fputcsv($file, $heade);
		}
		foreach($list as $k =>$v){
			$put_arr = array();
			$put_arr['dev_id'] = $v['dev_id'];
			$put_arr['dev_name'] = $v['dev_name'] ? $v['dev_name'] : "\t";
			if($v['type']==0) {
				$type = "公司";
			} else if($v['type']==1) {
				$type = "个人";
			}
			$put_arr['type'] = $type ? $type : "\t";
			$put_arr['email'] = $v['email'] ? $v['email'] : "\t";	
			if($v['email_verified'] == 1){
				$email_verified = "已验证";
			}
			$put_arr['email_verified'] = $email_verified ? $email_verified : "\t";
			$put_arr['mobile'] = $v['mobile'] ? $v['mobile'] : "\t";
			if($v['mobile_verified']==1){
				$mobile_verified =  "[已验证]" ;
			}
			$put_arr['mobile_verified'] = $mobile_verified ? $mobile_verified : "\t";
			$put_arr['user_name'] =  $user_list[$v['dev_id']] ? $user_list[$v['dev_id']] :"\t";
			if($v['location']==1) {
				$location = "中国大陆";
			} else if($v['location']==2) {
				$location = "港澳台和国外";
			}
			$put_arr['location'] =  $location ? $location : "\t";
			$put_arr['truename'] = $v['truename'] ? $v['truename'] : "\t";
			$put_arr['cardnumber'] = $v['cardnumber'] ? $v['cardnumber'] : "\t";
			$put_arr['company'] = $v['company'] ? $v['company'] : "\t";
			$put_arr['charter'] = $v['charter'] ? $v['charter'] : "\t";
			$put_arr['reg_ip'] = $v['reg_ip'] ? $v['reg_ip'] : "\t";	
			$ip_num = $ip_num_arr[$val['reg_ip']][0] ? $ip_num_arr[$val['reg_ip']][0] : 1;	
			$put_arr['ip_num'] = $ip_num ? $ip_num : "\t";
			if($v['im_type']==2) {
				$im_id =  "Gtalk:".$v['im_id'];
			} else if($v['im_type']==3) {
				$im_id =  "Msn:".$v['im_id'];
			} else if($v['im_type']==4) {
				$im_id =  "Skype:".$v['im_id'];
			}else{
				$im_id =  "QQ:".$v['im_id'];
			}
			$put_arr['im_id'] = $im_id ? $im_id : "\t";
			$put_arr['phone'] = $v['phone'] ? $v['phone'] : "\t";
			$put_arr['site'] = $v['site'] ? $v['site'] : "\t";
			$put_arr['zipcode'] = $v['zipcode'] ? $v['zipcode'] : "\t";
			if($params['status'] == -2 || $params['status'] == 0){
				//软件统计,开始
				$num = $model->table('sj_soft')->where("status=1 and hide=1 and channel_id='' and dev_id='{$v['dev_id']}'")->count();
				$undercarriage_num = $model->table('sj_soft')->where("status=1 and hide=3 and dev_id='{$v['dev_id']}'")->count();
				$soft_new = $model->table('sj_soft_tmp')->where("status=2 and record_type=1 and dev_id='{$v['dev_id']}'")->count();
				$soft_edit = $model->table('sj_soft_tmp')->where("status=2 and record_type=2 and dev_id='{$v['dev_id']}'")->count();
				$soft_update = $model->table('sj_soft_tmp')->where("status=2 and record_type=3 and dev_id='{$v['dev_id']}'")->count();
			}
			if($params['email_verified'] == 1 && $params['status'] ==1){
				if($v['dismissed_time'] && $v['dismissed']){
					$dismissed_time = date('Y-m-d H:i:s',$v['dismissed_time'])."--";
					$dismissed = $dismissed_time.$v['dismissed'];
				}
				$put_arr['dismissed'] = $dismissed ? $dismissed : "\t";
				$put_arr['register_time'] = $v['register_time'] ? date("Y-m-d H:i:s",$v['register_time']) : "\t";
			}elseif($params['status'] ==0){
				$put_arr['num'] = $num;
				$put_arr['undercarriage_num'] = $undercarriage_num;
				$put_arr['soft_new'] = $soft_new;
				$put_arr['soft_edit'] = $soft_edit;
				$put_arr['soft_update'] = $soft_update;
				$put_arr['register_time'] = $v['register_time'] ? date('Y-m-d H:i:s',$v['register_time']) : "\t";
			}elseif($params['status'] == -1){
				$put_arr['dismissed'] = $v['dismissed'] ? $v['dismissed'] : "\t";
				$put_arr['dismissed_time'] = $v['dismissed_time'] ? date('Y-m-d H:i:s',$v['dismissed_time']) : "\t";
			}elseif	($params['status'] == -2){
				$put_arr['num'] = $num;
				$put_arr['undercarriage_num'] = $undercarriage_num;
				$put_arr['soft_new'] = $soft_new;
				$put_arr['soft_edit'] = $soft_edit;
				$put_arr['soft_update'] = $soft_update;
				$put_arr['shield_time'] = $v['shield_time'] ? date('Y-m-d H:i:s',$v['shield_time']) : "\t";
				$put_arr['shield_reason'] = $v['shield_reason'] ? ltrim($v['shield_reason'],'LF') : "\t";
			}
			fputcsv($file, $put_arr);				
		}
		fclose($file);			
		$next_page = $p + 1;
		if ($p != $Page->totalPages) {
			$par = $_GET;
			unset($par['p'],$par['button'],$par['fid'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/User/developer_export/p/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}",
			);	
		}
		return $data;
	}
	function get_feedback($params,$limit,$p){
		$feedback_db=M('feedback');
    	$where = array(
    		'softid' => 0,
    		'feedbacktype' => 9
    	);
        $type=$params['type'];
        if(empty($type)) {
            $type='self';
            $where['status'] = 1;
        } else {
        	$type = 'unshow';
        	$where['status'] = 0;
        }
		if(isset($params['contact'])) $where['contact'] =  $params['contact'];
		if(isset($params['version_code'])) $where['version_code'] =  $params['version_code'];
		if(isset($params['ipmsg']))	$where['ipmsg'] =  $params['ipmsg'];
		if(isset($params['imei'])) $where['imei'] =  $params['imei'];
		if(isset($params['pid'])) $where['pid'] =  $params['pid'];
		if(isset($params['processed'])) $where['processed'] =  $params['processed'];
		if(isset($params['backtype'])) $where['backtype'] =  $params['backtype'];
    	$modelpost = M("post");
    	$modelthread = M("thread");
		if(isset($params['all_post']) && $params['all_post'] == 1){
			if(!S('ids')){
				$str = 'all_post >= 2 and type = 2';
				$rid_data = $modelthread->where($str)->field('rid')->findAll();
				$ids = array();
				if ($rid_data) {
					foreach ($rid_data as $value) {
						$ids[] = $value['rid'];
					}
				}
				$ids = S('ids',$ids);
			}else{
				$ids = S('ids');
			}
			$where['feedbackid'] = array('in', $ids);			
		}
		$model = new Model();
		if(isset($params['dname'])){ 
			$did = $model->table('pu_device')->where("status=1")->getField('did');
			$where['did'] = array('in', $did);	
		}
		if(isset($params['chname'])){ 
			$cid = $model->table('sj_channel')->where("status=1")->getField('cid');
			$where['cid'] = array('in', $cid);	
		}
		if(isset($params['content'])) $where['content'] =  array('like','%'.$params['content'].'%');
		if(($params['processed'] == 1 && isset($params['processed'])) || $params['type'] == 'unshow'){
			$order = "update_at";
		}elseif($params['processed'] == 0){
			$order = "submit_tm";
		}else{
			$order = "submit_tm";
		}
		if($params['fromdate'] && $params['todate']){
			$begintime = strtotime(urldecode($params['fromdate']));
			$endtime = strtotime(urldecode($params['todate']));
			$where[$order] = array(array("egt",$begintime),array("elt",$endtime));
		}
		$admin_users = M("admin_users");		
		if (!empty($_GET['admin_name'])) {
			$where_admin = array('admin_state'=>1);
			$where_admin['admin_user_name'] = array('like','%'.escape_string($_GET['admin_name']).'%');
			$admin_user_id = $admin_users->where($where_admin)->field('admin_user_id')->buildSql();
			$where['admin_id'] = array('IN', $admin_user_id);
			$this->assign('admin_name', $_GET['admin_name']);
		}	
        !$params['sectiontypeid']&&$params['sectiontypeid']=0;
		if($params['sectiontypeid']==0){
			$where['sectiontypeid'] = array('in', array(-1,0));
		}else if($params['sectiontypeid'] !=0 ){
			$where['sectiontypeid'] = $_GET['sectiontypeid'];
		}
		if($params['sectiontypeid'] == 100){
			unset($where['sectiontypeid']);
		}		
        $conf_db = D('Sj.Config');
        $config_list = $conf_db->where('config_type="backtype" and status=1')->getField('configcontent');
        $config_list = explode("|",$config_list);
		//分页
		import("@.ORG.Page2");
		if($params['count']){
			$count = $params['count'];
		}else{
			$count= $feedback_db->where($where)->count();
		}
		$param = http_build_query($params);		
		$Page = new Page($count,$limit,$param);	
        $feedbacklist = $feedback_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("{$order} desc")->select();
		//echo $feedback_db->getlastsql();exit;
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
			if($params['processed'] == 0 && isset($params['processed'])){
				$heade = array('ID','IMEI','机型','IP','联系方式','渠道','来源','version_code','信息类型','反馈内容','反馈时间');	
			}else if($params['processed'] == 1 && isset($params['processed'])){
				$heade = array('ID','IMEI','机型','IP','联系方式','渠道','来源','version_code','信息类型','反馈内容','安智回复','操作人员','回复时间');
			}else if($params['type'] == "unshow"){
				$heade = array('ID','IMEI','机型','IP','联系方式','渠道','来源','version_code','信息类型','反馈内容','安智回复','删除时间');
			}
			fputcsv($file, $heade);
		}	
        $dkey = array();
        $ckey = array();	
		$adminid =  array();
		foreach ($feedbacklist as $key => $value) {
			if($value['did']) $dkey[] = $value['did'];
			$ckey[$value['cid']] = 1;
			if($value['admin_id']) $adminid[] = $value['admin_id'];
		}
		if($dkey){
			$dinfo = $feedback_db->table('pu_device')->where(array('did' => array('in', array_unique($dkey))))
					->field('did,dname')
					->select();
			$dmap = array();
			foreach ($dinfo as $value) {
				# code...
				$dmap[$value['did']] = $value['dname'];
			}
		}	
		if($ckey){
			$cinfo = $feedback_db->table('sj_channel')->where(array('cid' => array('in', array_keys($ckey) ) ) )
					->field('cid,chname')
					->select();
			$cmap = array();
			foreach ($cinfo as $value) {
				# code...
				$cmap[$value['cid']] = $value['chname'];
			}
        }	
		$admin_user_name = $admin_users->where(array('admin_user_id'=>array('in',$adminid)))->field('admin_user_name,admin_user_id')->select();
		$username = array();
		foreach($admin_user_name as $k=>$v){
			$username[$v['admin_user_id']] = $v['admin_user_name'];
		}		
		foreach($feedbacklist as $k => $v){
			$put_arr = array();						
			$put_arr['feedbackid'] = $v['feedbackid'] ? $v['feedbackid'] : "\t";
			$put_arr['imei'] = $v['imei'] ? $v['imei'] : "\t";
			$put_arr['imei'] = $v['imei'] ? $v['imei'] : "\t";
			$put_arr['did'] = $dmap[$v['did']] ? $dmap[$v['did']] : "\t";
			$put_arr['ipmsg'] = $v['ipmsg'] ? $v['ipmsg'] : "\t";
			$put_arr['contact'] = $v['contact'] ? $v['contact'] : "\t";
			$put_arr['cid'] = $cmap[$v['cid']] ? $cmap[$v['cid']] : "\t";
			$put_arr['pid'] = $v['pid']== 1 ? '安智市场':'游戏客户端';
			$put_arr['version_code'] = $v['version_code'] ? $v['version_code'] :"\t";
			$put_arr['backtype'] = $config_list[$v['backtype']] ?$config_list[$v['backtype']] :"\t";
			$put_arr['content'] = $v['content'] ? $v['content'] :"\t";
			if($params['processed'] == 0 && isset($params['processed'])){
				$put_arr['submit_tm'] = $v['submit_tm'] ? date('Y-m-d H:i:s', $v['submit_tm']) :"\t";
			}else if(($params['processed'] == 1 && isset($params['processed'])) or $params['type'] == "unshow"){
				$modelpost = M("post");
				$parm2 = array('rid' => $v['feedbackid']);      	
				$res = $modelthread->where($parm2)->field('tid,new_post')->find();
				if($res){
					$postres = $modelpost->where(array('tid' => $res['tid'], 'user_type' => 1))->field('dateline, contents')->order('dateline desc')->find();
					$reply = $postres['contents'];
					$reply_tm = $postres['dateline'];
				}
				$put_arr['reply'] = $reply ? $reply : "\t";
				if($params['type'] == "unshow"){
					$put_arr['update_at'] = $v['update_at'] ? date("Y-m-d H:i:s",$v['update_at']) : "\t";
				}else{					
					$put_arr['admin_id'] = $username[$v['admin_id']] ? $username[$v['admin_id']] : "\t";
					$put_arr['reply_tm'] = $reply_tm ? date("Y-m-d H:i:s",$reply_tm) : "\t";
				}
			}
			fputcsv($file, $put_arr);
		}		
		fclose($file);			
		$next_page = $p + 1;
		if ($p != $Page->totalPages) {
			$par = $_GET;
			unset($par['p'],$par['button'],$par['fid'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/Message/exportExcel/p/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}",
			);	
		}
		return $data;
	}
	public function get_x86_export($params,$limit,$p){
		$where = "softid != '' and ";
		if(!empty($params['begintime']) && !empty($params['endtime'])){
			$begintime = strtotime(urldecode($params['begintime']));
			$endtime = strtotime(urldecode($params['endtime']));
			$where .= "add_tm >= {$begintime} and add_tm <= {$endtime}";
		}
		if(empty($params['count'])){
			$total = $this->table('sj_soft_x86_log')->where($where)->group('package')->field('package')->select();
			$total = count($total);
			$_GET['count'] = $total;
		}else{
			$total = $_GET['count'];
		}
		//import("@.ORG.Page2");
		$param = http_build_query($params);		
		//$Page = new Page($total,$limit,$param);	
		$totalPages = ceil($total/$limit);
		if($p == 1){
			$firstRow = 0;
		}else{
			$firstRow = ($p-1) * $limit;
		}
		$list = $this -> query("select * from (select * from sj_soft_x86_log where {$where} order by id desc ) A group by package limit {$firstRow},{$limit}");
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
			$heade = array('软件ID','软件包名','软件名称','x86状态');	
			fputcsv($file, $heade);
		}	
		foreach($list as $k => $v){
			$put_arr = array();
			$put_arr['softid'] = $v['softid'] ? $v['softid'] : "\t";
			$put_arr['package'] = $v['package'] ? $v['package'] : "\t";
			$put_arr['softname'] = $v['softname'] ? $v['softname'] : "\t";
			if($v['x86_status'] == 1){
				$x86_status = "支持";
			}else if($v['x86_status'] == 2){
				$x86_status = "不支持";
			}
			$put_arr['status'] = $x86_status ? $x86_status : "\t";
	
			fputcsv($file, $put_arr);				
		}
		fclose($file);			
		$next_page = $p + 1;
		if ($p != $totalPages) {
			$par = $_GET;
			unset($par['page'],$par['button'],$par['fid'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Dev/Soft/x86_exp/page/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}",
			);	
		}
		return $data;
	}	
	public function get_reject_contact_export($start,$end){
		$res = $this -> query("select dev_id, package, record_type, softid, softname,deny_msg from sj_soft_tmp where status=3 and (upload_tm >= {$start} and upload_tm <= {$end})");
		$dev_list = array();
		foreach($res as $k => $row){
			if (empty($row['dev_id'])) continue;
			if (!isset($dev_list[$row['dev_id']])) {
				 $dev_list[$row['dev_id']] = array();
			}
			$dev_list[$row['dev_id']][] = $row;
		}	
		$result = array();
		foreach ($dev_list as $dev_id => $v) {		
			$dev = $this -> table('pu_developer')->where("dev_id = {$dev_id}")->find();
			$result[$dev_id] = $dev;
			$user = $this -> table('pu_user')->where("userid = {$dev_id}")->find();
			$result[$dev_id]['user'] = $user['user_name'];		
			$info_soft = $this -> table('sj_soft')->where("dev_id = {$dev_id} and status=1 and hide=1")->field('package, total_downloaded')->select();
			$t = array();
			$total = 0;
			$download = 0;
			foreach ($info_soft as $val) {
				if (!isset($t[$val['package']])) {
					$total += 1;
					$download += $val['total_downloaded'];
					$t[$val['package']] = 1;
				}
			}
			$result[$dev_id]['total'] = $total;
			$result[$dev_id]['download'] = $download;
			$softs = $this -> table('sj_soft')->where("dev_id = {$dev_id} and status=1 and hide=1")->field('package')->order('total_downloaded desc')->group('package')->limit(3)->select();	
			$result[$dev_id]['soft'] = $softs;
			$result[$dev_id]['deny'] = $v;
		}
		$fid = uniqid();
		mkdir('/tmp/export/', 0755, true);
		$file = '/tmp/export/'. session_id(). '_'.$fid.'export'. ".csv";
		$file = fopen($file, 'a');
		fwrite($file,chr(0xEF).chr(0xBB).chr(0xBF)); 
		$heade = array("用户名","开发者姓名","已验证手机","联系邮箱","QQ","电话","开发者软件数量","开发者软件总下载量","驳回/下架应用名称","驳回/下架软件地址","下载量排名第一软件地址","下载量排名第二软件地址","下载量排名第三软件地址","驳回原因");	
		fputcsv($file, $heade);		
		foreach ($result as $dev_id => $info) {
			foreach ($info['deny'] as $val) {
				$put_arr = array(
					$info['user'],
					$info['dev_name'],
					!empty($info['mobile']) ? $info['mobile'] : '-',
					!empty($info['email']) ? $info['email'] : '-',
					!empty($info['im_id']) ? $info['im_id'] : '-',
					!empty($info['phone']) ? $info['phone'] : '-',
					!empty($info['total']) ? $info['total'] : '-',
					!empty($info['download']) ? $info['download'] : '-',
					!empty($val['softname']) ? $val['softname'] : '-',
					(!empty($val['package']) && $val['record_type'] !=1) ? 'http://www.anzhi.com/pkg/'. $val['package'] : '-',
					!empty($info['soft'][0]) ? 'http://www.anzhi.com/pkg/'. $info['soft'][0]['package'] : '-',
					!empty($info['soft'][1]) ? 'http://www.anzhi.com/pkg/'. $info['soft'][1]['package'] : '-',
					!empty($info['soft'][2]) ? 'http://www.anzhi.com/pkg/'. $info['soft'][2]['package'] : '-',
				    !empty($val['deny_msg']) ? $val['deny_msg'] : '-',
				);
				fputcsv($file, $put_arr);
			}
		}
		fclose($file);		
		$file = '/tmp/export/'. session_id(). '_'.$fid.'export'. ".csv";		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.date("Y/m/d",$start).'~'.date("Y/m/d",$end).'.csv"');
		header('Cache-Control: max-age=0');			
		echo file_get_contents($file);	
		exit;
	}
	
}
?>
