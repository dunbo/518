<?php
/*
 * 广告扫描统计
 */
class AdScanCountAction extends CommonAction {
	public function index(){
		$provider_type = empty($_GET['provider'])?'1':$_GET['provider'];
		$start_tm = empty($_GET['start_tm'])?strtotime(date("Y-m-d")):strtotime($_GET['start_tm']);
		$end_tm = empty($_GET['end_tm'])?time():strtotime($_GET['end_tm']);
		//var_dump($start_tm."--".$end_tm);
		$this->assign("provider_type",$provider_type);
		$this->assign("start_tm",$_GET['start_tm']);
		$this->assign("end_tm",$_GET['end_tm']);
		// $where = "a.provider = ".$provider_type;
		//$start_tm = '1376761068';
		// if($provider_type != 5){
			// $where .= " and a.time_rep > '".$start_tm."' and a.time_rep < '".$end_tm."'";
		// }
		// $where .= " and b.ad_new = '' and b.package_status = 1 and c.hide = 1 and c.status = 1 and a.description !=''";
		//echo $where;	
		$model = M('');
		//$ad_scan = $model->table('sj_soft_scan_result a')->join('sj_soft_file b ON b.id = a.sfid')->join('sj_soft c on b.softid = c.softid')->where($where)->field('a.provider,a.description,b.apk_name,b.id')->select();
		//echo $model->getLastSql();
		$ad_scan = $this->ad_scan_data_process($start_tm,$end_tm,$provider_type);
		foreach($ad_scan as $key=>$val){
			$val['description'] = json_decode($val['description'],true);
			$ad_scan[$key] = $val;
		}
		//var_dump($ad_scan);
		switch ($provider_type){
			case 1:
			$res = $this->tencent_ad_scan($ad_scan);
			break;
			case 2:
			$res = $this->aqgj_ad_scan($ad_scan);
			break;
			case 4:
			$res = $this->jinshan_ad_scan($ad_scan);
			break;
			case 5:
			$res = $this->safe_ad_scan($ad_scan);
			break;
		}
		$this->assign('res',$res);
		$this->display();
	}
	
	//腾讯广告扫描结果处理
	public function tencent_ad_scan($data){
	//var_dump($data);
		$provider_ad = $provider_ad_count = $provider_ad_name = array();
		foreach($data as $key=>$val){
		//var_dump($val['apk_name']);
			if(isset($val['description']['pluginlist'])&&count($val['description']['pluginlist'])>0){
				$plugintype_arr = array('1'=>'通知栏广告','2'=>'积分墙广告','3'=>'banner广告','4'=>'悬浮窗图标广告','5'=>'精品推荐列表广告','6'=>'插播广告');
				$provider_ad[$val['provider']][] = $val['description'];
				foreach($val['description']['pluginlist'] as $_k=>$_v){
						//统计每个广告类型个数
						if(!isset($provider_ad_count[$val['provider']][$_v['plugintype']])) $provider_ad_count[$val['provider']][$_v['plugintype']] = 0;
						$provider_ad_count[$val['provider']][$_v['plugintype']]++;
						//统计广告名称个数
						if(!isset($provider_ad_name[$_v['pluginname']]['num'])) $provider_ad_name[$_v['pluginname']]['num'] = 0;
						$provider_ad_name[$_v['pluginname']]['num']++;
						$provider_ad_name[$_v['pluginname']]['type'] = $plugintype_arr[$_v['plugintype']];
						$provider_ad_name[$_v['pluginname']]['desc'] = $_v['plugindesc'];
						$provider_ad_name[$_v['pluginname']]['pack'][$val['id']]['name'] = $val['apk_name'];
						$provider_ad_name[$_v['pluginname']]['pack'][$val['id']]['file_id'] = $val['id'];
				}
			
			}
		}
		$len = count($provider_ad_name);
		//var_dump($len);
		// for($i=1;$i<$len;$i++)
		// {
			// for($j=$len-1;$j>=$i;$j--){
				// if($provider_ad_name[$j]['num']<$provider_ad_name[$j-1]['num'])
				// {
				 // $x=$provider_ad_name[$j];
				 // $provider_ad_name[$j]=$provider_ad_name[$j-1];
				 // $provider_ad_name[$j-1]=$x;
				// }
			// }	
		// }
		//var_dump($provider_ad_count);
		//var_dump($provider_ad_name);
		return $provider_ad_name;
	}
	
	//安全管家安全扫描
	public function aqgj_ad_scan($data){
		//var_dump($data);
		$provider_ad_name = array();
		foreach($data as $key=>$val){
			if(isset($val['description']['leafletaction'])){
				if(!isset($provider_ad_name[$val['description']['leafletname']]['num'])) $provider_ad_name[$val['description']['leafletname']]['num'] = 0;
				$provider_ad_name[$val['description']['leafletname']]['num']++;
				$provider_ad_name[$val['description']['leafletname']]['type'] = $val['description']['leafletaction'];	
				$provider_ad_name[$val['description']['leafletname']]['desc'] = $val['description']['leafletdes'];
				$provider_ad_name[$val['description']['leafletname']]['pack'][$val['id']]['name'] = $val['apk_name'];
				$provider_ad_name[$val['description']['leafletname']]['pack'][$val['id']]['file_id'] = $val['id'];
			}
		}
		//var_dump($provider_ad_name);
		return $provider_ad_name;
	}
	
	//金山广告扫描结果处理
	public function jinshan_ad_scan($data){
		//var_dump($data);
		$ad_type = array('1'=>'通知栏广告','2'=>'内嵌广告条','3'=>'弹窗广告','4'=>'积分墙广告','5'=>'伪造短信广告','6'=>'广告插件自动启动');
		$ad_desc = array('1'=>'获取您的手机号，并上传','2'=>'获取手机所在位置，并上传','3'=>'获取手机安装软件内容，并上传','4'=>'获取手机通讯录，并上传','5'=>'加载DEX','6'=>'启动服务','7'=>'获取IMEI信息','8'=>'读取账户信息','9'=>'设置自启动','10'=>'唤醒');
		$provider_ad_name = array();
		foreach($data as $key=>$val){
			if(isset($val['description']['adinfo'])&&count($val['description']['adinfo'])>=1){
			//var_dump($val['description']['adinfo']);
				foreach($val['description']['adinfo'] as $_k=>$_v){
					if(!isset($provider_ad_name[$_v['adname']]['num'])) $provider_ad_name[$_v['adname']]['num'] = 0;
					$provider_ad_name[$_v['adname']]['num']++;
					$provider_ad_name[$_v['adname']]['type'] = $ad_type[$_v['adtype'][0]];	
					$provider_ad_name[$_v['adname']]['pack'][$val['id']]['name'] = $val['apk_name'];
					$provider_ad_name[$_v['adname']]['pack'][$val['id']]['file_id'] = $val['id'];
					if(count($_v['adaction'])>=1){
					//var_dump($_v['adaction']);
						foreach($_v['adaction'] as $a_k=>$a_v){
							if(!isset($provider_ad_name[$_v['adname']]['desc']))$provider_ad_name[$_v['adname']]['desc']="";
							$provider_ad_name[$_v['adname']]['desc'] .= $ad_desc[$a_v].',';
						}
					}
				}
				
			}
		}
		//var_dump($provider_ad_name);
		foreach($provider_ad_name as $k=>$v){
			$desc_tmp = array_unique(explode(',',$v['desc']));
			$v['desc'] = substr(implode(',',$desc_tmp),0,-1);
			$provider_ad_name[$k] = $v;
		}
		return $provider_ad_name;
	}
	
	//360
	public function safe_ad_scan($data){
		//var_dump($data);
		$provider_ad_name = array();
		foreach($data as $k=>$v){
		
			if($v['description']['safe']!=''){
				if(!isset($provider_ad_name[$v['description']['safe']]['num'])) $provider_ad_name[$v['description']['safe']]['num'] = 0;
				$provider_ad_name[$v['description']['safe']]['num']++;
				$provider_ad_name[$v['description']['safe']]['type'] = $v['description']['safe'];	
				$provider_ad_name[$v['description']['safe']]['desc'] = $v['description']['desc'];	
				$provider_ad_name[$v['description']['safe']]['pack'][$v['id']]['name'] = $v['apk_name'];
				$provider_ad_name[$v['description']['safe']]['pack'][$v['id']]['file_id'] = $v['id'];
			}
		}
		//var_dump($provider_ad_name);
		return $provider_ad_name;
	}
	
	//获取包路径
	public function get_down_path(){
		$id = $_POST['id'];
		$model = M('');
		$file_path = $model->table('sj_soft_file')->where(array('id'=>$id))->field('url')->find();
		echo IMGATT_HOST.$file_path['url'];
	}
	
	public function ad_scan_data_process($start_tm,$end_tm,$provider_type){
		$model = M('');
		$where = "package_status = 1 and ad_new = '' and leafstatus = '1'";
		if(!empty($start_tm)){
			$where .= ' and upload_time >'.$start_tm;
		}
		if(!empty($start_tm)){
			$where .= ' and upload_time <'.$end_tm;
		}
		$soft_file = $model->table('sj_soft_file')->where($where)->field('id,softid,apk_name')->select();
		 //echo $model->getLastSql();
		// var_dump($soft_file);

		$soft_sql = '';
		foreach($soft_file as $k=>$v){
			if($k==(count($soft_file)-1)){
				$soft_sql .= 'select softid from sj_soft where hide = 1 and status = 1 and softid = '.$v['softid'];
			}else{
				$soft_sql .= 'select softid from sj_soft where hide = 1 and status = 1 and softid = '.$v['softid'].' union ';
			}
		}

		$online_soft = $model->query($soft_sql);
		foreach($online_soft as $o_k=>$o_v){
			$online_soft[$o_k] = $o_v['softid'];
		}
		foreach($soft_file as $s_k=>$s_v){
			if(!in_array($s_v['softid'],$online_soft)){
				//echo $s_v['softid']."<br>";
				unset($soft_file[$s_k]);
			}		
		}
		//有效包file
		//var_dump($soft_file);
		$soft_file = array_values($soft_file);
		$file_sql = '';
		foreach($soft_file as $so_k=>$so_v){
			if(!empty($provider_type)){
				$where_str = "sfid = ".$so_v['id']." and description !='' and is_tmp = '0' and provider = ".$provider_type;
			}else{
				$where_str = "sfid = ".$so_v['id']." and description !='' and is_tmp = '0'";
			}
			if($so_k==(count($soft_file)-1)){
				$file_sql .= 'select sfid,provider,description from sj_soft_scan_result where '.$where_str;
			}else {
				$file_sql .= 'select sfid,provider,description from sj_soft_scan_result where '.$where_str.' union ';
			}
		}
		
		$file_scan = $model->query($file_sql);
		//var_dump($file_scan);
		$last_arr = array();
		foreach($file_scan as $last_k=>$last_v){
			$last_arr[$last_v['sfid']]['id'] = $last_v['sfid'];
			$last_arr[$last_v['sfid']]['provider'] = $last_v['provider'];
			$last_arr[$last_v['sfid']]['description'] = $last_v['description'];
		}
		foreach($soft_file as $l_k=>$l_v){
			$last_arr[$l_v['id']]['apk_name'] = $l_v['apk_name'];
		}
		$last_arr = array_values($last_arr);
		return $last_arr;
	}
}
