<?php
/**
 * 安智网产品管理平台 下载刷量管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：wwt
 * date:2012-07-27
 * ----------------------------------------------------------------------------
*/
class DownloadBrushAction extends CommonAction {

	//设备下载刷量
	function device_download_brush(){
		$model = new Model();
		$where = ' `status` = 1 ';
		import('@.ORG.Page');
		$param = http_build_query($_REQUEST);
		$limit = 10;
		if(isset($_REQUEST['lr'])){
			$this->assign('lr',(int)$_REQUEST['lr']);
		}else{
			$this->assign('lr',$limit);
		}
		if(isset($_REQUEST['p'])){
			$this->assign('p',(int)$_REQUEST['p']);
		}else{
			$this->assign('p', 1);
		}
		if(isset($_REQUEST['softname']) && isset($_REQUEST['package'])){
			$where .= ' AND `package` like "%'.(string)$_REQUEST['package'].'%"';
			$this -> assign('package',(string)$_REQUEST['package']);
		}else if(isset($_REQUEST['package'])){
			$where .= ' AND `package` like "%'.(string)$_REQUEST['package'].'%"';
			$this -> assign('package',(string)$_REQUEST['package']);
			$this -> assign('softname','');
		}else if(isset($_REQUEST['softname'])){
			$condition['`softname`'] = array('like','%'.$_REQUEST['softname'].'%');
			$soft_lists = $model -> table('sj_soft')->where($condition) ->field('package') -> select();
			$packages  = '';
			foreach($soft_lists as $soft_list){
				$packages .= ",'".$soft_list['package']."'";
			}
			if ($packages[0] == ',') {
				$packages = substr($packages, 1);
			}
			$where .= " AND `package` in ({$packages}) ";
			$this -> assign('softname',(string)$_REQUEST['softname']);
			$this -> assign('package','');
		}else{
			$this -> assign('package','');
			$this -> assign('softname','');
		}
		if(isset($_REQUEST['start_time'])){
			$start_time = (string)$_REQUEST['start_time'];
			$where .= " AND `brush_time` >= '".strtotime($start_time.' 00:00:00')."'";
			$this->assign('start_time',$start_time);
		}else{
			$where .= " AND `brush_time` >= '".strtotime(date('Y-m-d', strtotime('-7 days', time())).' 00:00:00')."'";
			$this->assign('start_time',date('Y-m-d', strtotime('-7 days', time())));
		}
		if(isset($_REQUEST['end_time'])){
			$end_time  = (string)$_REQUEST['end_time'];
			$where .= " AND `brush_time` <= '".strtotime($end_time.' 23:59:59')."'";
			$this->assign('end_time',$end_time);
		}else{
			$where .= " AND `brush_time` <= '".strtotime(date('Y-m-d').' 23:59:59')."'";
			$this->assign('end_time',date('Y-m-d'));
		}
		$zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM','status' => 1);
		$sum_info = $model -> table('pu_config') -> where($zonghe) -> field('configcontent') -> select();
		$baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT','status' => 1);
		$percent_info = $model -> table('pu_config') -> where($baifen) -> field('configcontent') -> select();
		if(isset($_REQUEST['exceed_sum'])){
			$where .= " AND `exceed_sum` >= '".intval($_REQUEST['exceed_sum'])."'";
			$this -> assign('exceed_sum',intval($_REQUEST['exceed_sum']));
		}else {
			$this -> assign('exceed_sum',$sum_info[0]['configcontent']);
		}
		
		if(isset($_REQUEST['percent'])){
			$percent = floatval(intval($_REQUEST['percent'])/100);
			$where .= " AND `percent` >= '".$percent."'";
			$this -> assign('percent',intval($_REQUEST['percent']));
		}else {
			$this -> assign('percent',$percent_info[0]['configcontent']);
		}
		$count_total = $model -> table('sj_device_download_brush') -> where($where) -> count();
		$page = new Page($count_total, $limit, $param);
		
		$order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
		$order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;
		if($order_go == 1 && $order_rule == 1){
			$brush_list =  $model -> table('sj_device_download_brush') -> where($where) -> order('package ASC,brush_time ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 1 && $order_rule == 2){
			$brush_list =  $model -> table('sj_device_download_brush') -> where($where) -> 	order('package DESC,brush_time DESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 1){
			$brush_list =  $model -> table('sj_device_download_brush') -> where($where) -> 	order('brush_time ASC,package ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 2){
			$brush_list =  $model -> table('sj_device_download_brush') -> where($where) -> 	order('brush_time DESC,package AESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}
	
		for($i = 0;$i < count($brush_list); $i++){
			$map['`package`'] = $brush_list[$i]['package'];
			$soft_info = $model -> table('sj_soft') ->where($map) ->field('softname,hide') -> order('softid DESC') -> limit(1) -> select();
			$brush_list[$i]['softname'] = $soft_info[0]['softname'];
			$brush_list[$i]['hide'] = $soft_info[0]['hide'];
			if(empty($brush_list[$i]['softname'])){
				$brush_list[$i]['softname'] = '<font color="cyan">软件已失效</font>';
			}
			$brush_list[$i]['percent']  = substr($brush_list[$i]['percent'] * 100,0,5) ."%";
			$mingdan = array('`package`' => $brush_list[$i]['package'],'`status`' => 1);
			$white_data = $model -> table('sj_brush_adapter') ->where($mingdan) -> limit(1) -> select();
			$white_count = count($white_data);
			if($white_count > 0){
				$brush_list[$i]['white']  = 1;
				$brush_list[$i]['note']  = $white_data[0]['note'];
			}else{
				$brush_list[$i]['white']  = 0;
				$brush_list[$i]['note']  = '';
			}
		}
		
		//是否显示白名单
		$white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;
	
		foreach($brush_list as $key => $val){
			if($white_list == 1){
				$brush_list_all = $brush_list;
			}elseif($white_list == 0){
				if($val['white'] == 0){
					$brush_list_all[] = $val;
				}
			}
		}
		
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('white_list',$white_list);
		$this -> assign('page', $page->show());
		$this -> assign('order_go',$order_go);
		$this -> assign('order_rule',$order_rule);
		$this -> assign('brush_list',$brush_list_all);
		$this -> display();
	}
	
	//IP下载刷量
	function ip_download_brush(){
		ini_set('memory_limit','256M');
		import('@.ORG.Page');
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign('lr',(int)$_GET['lr']);
		}else{
			$this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
			$this->assign('p',(int)$_GET['p']);
		}else{
			$this->assign('p', 1);
		}
		$model = new Model();
		$where = ' `status` = 1 ';
		if(isset($_GET['softname']) && isset($_GET['package'])){
			$where .= ' AND `package` like "%'.(string)$_GET['package'].'%"';
			$this -> assign('package',(string)$_GET['package']);
		}else if(isset($_GET['package'])){
			$where .= ' AND `package` like "%'.(string)$_GET['package'].'%"';
			$this -> assign('package',(string)$_GET['package']);
			$this -> assign('softname','');
		}else if(isset($_GET['softname'])){
			$condition['`softname`'] = array('like','%'.$_GET['softname'].'%');
			$soft_lists = $model -> table('sj_soft')->where($condition) ->field('package') -> select();
			$packages  = '';
			foreach($soft_lists as $soft_list){
				$packages .= ",'".$soft_list['package']."'";
			}
			if ($packages[0] == ',') {
				$packages = substr($packages, 1);
			}
			$where .= " AND `package` in ({$packages}) ";
			$this -> assign('softname',(string)$_GET['softname']);
			$this -> assign('package','');
		}else{
			$this -> assign('package','');
			$this -> assign('softname','');
		}
		if(isset($_GET['start_time'])){
			$start_time = (string)$_GET['start_time'];
			$where .= " AND `brush_time` >= '".strtotime($start_time.' 00:00:00')."'";
			$this->assign('start_time',$start_time);
		}else{
			$where .= " AND `brush_time` >= '".strtotime(date('Y-m-d', strtotime('-7 days', time())).' 00:00:00')."'";
			$this->assign('start_time',date('Y-m-d', strtotime('-7 days', time())));
		}
		if(isset($_GET['end_time'])){
			$end_time  = (string)$_GET['end_time'];
			$where .= " AND `brush_time` <= '".strtotime($end_time.' 23:59:59')."'";
			$this->assign('end_time',$end_time);
		}else{
			$where .= " AND `brush_time` <= '".strtotime(date('Y-m-d').' 23:59:59')."'";
			$this->assign('end_time',date('Y-m-d'));
		}
		$zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM','status' => 1);
		$sum_info = $model -> table('pu_config') -> where($zonghe) -> field('configcontent') -> select();
		$baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT','status' => 1);
		$percent_info = $model -> table('pu_config') -> where($baifen) -> field('configcontent') -> select();
		if(isset($_GET['exceed_sum'])){
			$where .= " AND `exceed_sum` >= '".intval($_GET['exceed_sum'])."'";
			$this -> assign('exceed_sum',intval($_GET['exceed_sum']));
		}else{
			$this -> assign('exceed_sum',$sum_info[0]['configcontent']);
		}
		if(isset($_GET['percent'])){
			$percent = floatval(intval($_GET['percent'])/100);
			$where .= " AND `percent` >= '".$percent."'";
			$this -> assign('percent',intval($_GET['percent']));
		}else{
			$this -> assign('percent',$percent_info[0]['configcontent']);
		}
		$count_total = $model -> table('sj_ip_download_brush') -> where($where) -> count();
		$page = new Page($count_total, $limit, $param);
	
		$order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
		$order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;
		if($order_go == 1 && $order_rule == 1){
			$brush_list =  $model -> table('sj_ip_download_brush') -> where($where) -> 	order('package ASC,brush_time ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 1 && $order_rule == 2){
			$brush_list =  $model -> table('sj_ip_download_brush') -> where($where) -> 	order('package DESC,brush_time AESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 1){
			$brush_list =  $model -> table('sj_ip_download_brush') -> where($where) -> 	order('brush_time ASC,package ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 2){
			$brush_list =  $model -> table('sj_ip_download_brush') -> where($where) -> 	order('brush_time DESC,package DESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}
		
		for($i = 0;$i < count($brush_list); $i++){
			$map['`package`'] = $brush_list[$i]['package'];
			$soft_info = $model -> table('sj_soft') ->where($map) ->field('softname,hide') -> order('softid DESC') -> limit(1) -> select();
			$brush_list[$i]['softname'] = $soft_info[0]['softname'];
			$brush_list[$i]['hide'] = $soft_info[0]['hide'];
			if(empty($brush_list[$i]['softname'])){
				$brush_list[$i]['softname'] = '<font color="cyan">软件已失效</font>';
			}
			$brush_list[$i]['percent']  = substr($brush_list[$i]['percent'] * 100,0,5) ."%";
			$mingdan = array('`package`' => $brush_list[$i]['package'],'`status`' => 1);
			$white_data = $model -> table('sj_brush_adapter') ->where($mingdan) -> limit(1) -> select();
			$white_count = count($white_data);
			if($white_count > 0){
				$brush_list[$i]['white']  = 1;
				$brush_list[$i]['note']  = $white_data[0]['note'];
			}else{
				$brush_list[$i]['white']  = 0;
				$brush_list[$i]['note']  = '';
			}
		}
		//是否显示白名单
		$white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;
	
		foreach($brush_list as $key => $val){
			if($white_list == 1){
				$brush_list_all = $brush_list;
			}elseif($white_list == 0){
				if($val['white'] == 0){
					$brush_list_all[] = $val;
				}
			}
		}
	
		
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('white_list',$white_list);
		$this -> assign('page', $page->show());
		$this -> assign('order_go',$order_go);
		$this -> assign('order_rule',$order_rule);
		$this -> assign('brush_list',$brush_list_all);
		$this -> display();
	}
	
	//m.anzhi.com下载刷量
	function manzhi_download_brush(){
		import('@.ORG.Page');
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign('lr',(int)$_GET['lr']);
		}else{
			$this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
			$this->assign('p',(int)$_GET['p']);
		}else{
			$this->assign('p', 1);
		}
		$model = new Model();
		$where = ' `status` = 1 ';
		if(isset($_GET['softname']) && isset($_GET['package'])){
			$where .= ' AND `package` like "%'.(string)$_GET['package'].'%"';
			$this -> assign('package',(string)$_GET['package']);
		}else if(isset($_GET['package'])){
			$where .= ' AND `package` like "%'.(string)$_GET['package'].'%"';
			$this -> assign('package',(string)$_GET['package']);
			$this -> assign('softname','');
		}else if(isset($_GET['softname'])){
			$condition['`softname`'] = array('like','%'.$_GET['softname'].'%');
			$soft_lists = $model -> table('sj_soft')->where($condition) ->field('package') -> select();
			$packages  = '';
			foreach($soft_lists as $soft_list){
				$packages .= ",'".$soft_list['package']."'";
			}
			if ($packages[0] == ',') {
				$packages = substr($packages, 1);
			}
			$where .= " AND `package` in ({$packages}) ";
			$this -> assign('softname',(string)$_GET['softname']);
			$this -> assign('package','');
		}else{
			$this -> assign('package','');
			$this -> assign('softname','');
		}
		if(isset($_GET['start_time'])){
			$start_time = (string)$_GET['start_time'];
			$where .= " AND `brush_time` >= '".strtotime($start_time.' 00:00:00')."'";
			$this->assign('start_time',$start_time);
		}else{
			$where .= " AND `brush_time` >= '".strtotime(date('Y-m-d', strtotime('-7 days', time())).' 00:00:00')."'";
			$this->assign('start_time',date('Y-m-d', strtotime('-7 days', time())));
		}
		if(isset($_GET['end_time'])){
			$end_time  = (string)$_GET['end_time'];
			$where .= " AND `brush_time` <= '".strtotime($end_time.' 23:59:59')."'";
			$this->assign('end_time',$end_time);
		}else{
			$where .= " AND `brush_time` <= '".strtotime(date('Y-m-d').' 23:59:59')."'";
			$this->assign('end_time',date('Y-m-d'));
		}
		$zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM','status' => 1);
		$sum_info = $model -> table('pu_config') -> where($zonghe) -> field('configcontent') -> select();
		$baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT','status' => 1);
		$percent_info = $model -> table('pu_config') -> where($baifen) -> field('configcontent') -> select();
		if(isset($_GET['m_sum'])){
			$where .= " AND `m_sum` >= '".intval($_GET['m_sum'])."'";
			$this -> assign('m_sum',intval($_GET['m_sum']));
		}else{
			$this -> assign('m_sum',$sum_info[0]['configcontent']);
		}
		if(isset($_GET['percent'])){
			$percent = floatval(intval($_GET['percent'])/100);
			$where .= " AND `percent` >= '".$percent."'";
			$this -> assign('percent',intval($_GET['percent']));
		}else{
			$this -> assign('percent',$percent_info[0]['configcontent']);
		}
		$count_total = $model -> table('sj_manzhi_download_brush') -> where($where) -> count();
		$page = new Page($count_total, $limit, $param);
		//排序规则
		$order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
		$order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;
		if($order_go == 1 && $order_rule == 1){
			$brush_list =  $model -> table('sj_manzhi_download_brush') -> where($where) -> 	order('package ASC,brush_time ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 1 && $order_rule == 2){
			$brush_list =  $model -> table('sj_manzhi_download_brush') -> where($where) -> 	order('package DESC,brush_time DESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 1){
			$brush_list =  $model -> table('sj_manzhi_download_brush') -> where($where) -> 	order('brush_time ASC,package ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 2){
			$brush_list =  $model -> table('sj_manzhi_download_brush') -> where($where) -> 	order('brush_time DESC,package DESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}
		for($i = 0;$i < count($brush_list); $i++){
			$map['`package`'] = $brush_list[$i]['package'];
			$soft_info = $model -> table('sj_soft') ->where($map) ->field('softname,hide') -> order('softid DESC') -> limit(1) -> select();
			$brush_list[$i]['softname'] = $soft_info[0]['softname'];
			$brush_list[$i]['hide'] = $soft_info[0]['hide'];
			if(empty($brush_list[$i]['softname'])){
				$brush_list[$i]['softname'] = '<font color="cyan">软件已失效</font>';
			}
			$brush_list[$i]['percent']  = round($brush_list[$i]['percent'] * 10000) / 100 ."%";
			$brush_list[$i]['percentm']  = round($brush_list[$i]['percentm'] * 10000) / 100 ."%";
			$mingdan = array('`package`' => $brush_list[$i]['package'],'`status`' => 1);
			$white_data = $model -> table('sj_brush_adapter') ->where($mingdan) -> limit(1) -> select();
			$white_count = count($white_data); 
			if($white_count > 0){
				$brush_list[$i]['white']  = 1;
				$brush_list[$i]['note']  = $white_data[0]['note'];
			}else{
				$brush_list[$i]['white']  = 0;
				$brush_list[$i]['note']  = '';
			}
			$brush_list[$i]['m_sum'] = $brush_list[$i]['m_sum'] - $brush_list[$i]['360_sum'] - $brush_list[$i]['qq_sum'] - $brush_list[$i]['sina_sum'] - $brush_list[$i]['uc_sum'];
		}
		

		//是否显示白名单
		$white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;
	
		foreach($brush_list as $key => $val){
			if($white_list == 1){
				$brush_list_all = $brush_list;
			}elseif($white_list == 0){
				if($val['white'] == 0){
					$brush_list_all[] = $val;
				}
			}
		}
		

		
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('white_list',$white_list);
		$this -> assign('page', $page->show());
		$this -> assign('order_go',$order_go);
		$this -> assign('order_rule',$order_rule);
		$this -> assign('brush_list',$brush_list_all);
		$this -> display();
	}
	
	//www.anzhi.com下载刷量
	function webanzhi_download_brush(){
		import('@.ORG.Page');
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign('lr',(int)$_GET['lr']);
		}else{
			$this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
			$this->assign('p',(int)$_GET['p']);
		}else{
			$this->assign('p', 1);
		}
		$model = new Model();
		$where = ' `status` = 1 ';
		if(isset($_GET['softname']) && isset($_GET['package'])){
			$where .= ' AND `package` like "%'.(string)$_GET['package'].'%"';
			$this -> assign('package',(string)$_GET['package']);
		}else if(isset($_GET['package'])){
			$where .= ' AND `package` like "%'.(string)$_GET['package'].'%"';
			$this -> assign('package',(string)$_GET['package']);
			$this -> assign('softname','');
		}else if(isset($_GET['softname'])){
			$condition['`softname`'] = array('like','%'.$_GET['softname'].'%');
			$soft_lists = $model -> table('sj_soft')->where($condition) ->field('package') -> select();
			$packages  = '';
			foreach($soft_lists as $soft_list){
				$packages .= ",'".$soft_list['package']."'";
			}
			if ($packages[0] == ',') {
				$packages = substr($packages, 1);
			}
			$where .= " AND `package` in ({$packages}) ";
			$this -> assign('softname',(string)$_GET['softname']);
			$this -> assign('package','');
		}else{
			$this -> assign('package','');
			$this -> assign('softname','');
		}
		if(isset($_GET['start_time'])){
			$start_time = (string)$_GET['start_time'];
			$where .= " AND `brush_time` >= '".strtotime($start_time.' 00:00:00')."'";
			$this->assign('start_time',$start_time);
		}else{
			$where .= " AND `brush_time` >= '".strtotime(date('Y-m-d', strtotime('-7 days', time())).' 00:00:00')."'";
			$this->assign('start_time',date('Y-m-d', strtotime('-7 days', time())));
		}
		if(isset($_GET['end_time'])){
			$end_time  = (string)$_GET['end_time'];
			$where .= " AND `brush_time` <= '".strtotime($end_time.' 23:59:59')."'";
			$this->assign('end_time',$end_time);
		}else{
			$where .= " AND `brush_time` <= '".strtotime(date('Y-m-d').' 23:59:59')."'";
			$this->assign('end_time',date('Y-m-d'));
		}
		$zonghe = array('config_type' => 'DOWNLOAD_EXCESS_SUM','status' => 1);
		$sum_info = $model -> table('pu_config') -> where($zonghe) -> field('configcontent') -> select();
		$baifen = array('config_type' => 'DOWNLOAD_EXCESS_PERCENT','status' => 1);
		$percent_info = $model -> table('pu_config') -> where($baifen) -> field('configcontent') -> select();
		if(isset($_GET['web_sum'])){
			$where .= " AND `web_sum` >= '".intval($_GET['web_sum'])."'";
			$this -> assign('web_sum',intval($_GET['web_sum']));
		}else{
			$this -> assign('web_sum',$sum_info[0]['configcontent']);
		}
		if(isset($_GET['percent'])){
			$percent = floatval(intval($_GET['percent'])/100);
			$where .= " AND `percent` >= '".$percent."'";
			$this -> assign('percent',intval($_GET['percent']));
		}else{
			$this -> assign('percent',$percent_info[0]['configcontent']);
		}
		
		
		$order_go = isset($_GET['order_go']) ? $_GET['order_go'] : 1;
		$order_rule = isset($_GET['order_rule']) ? $_GET['order_rule'] : 1;
		$count_total = $model -> table('sj_webanzhi_download_brush') -> where($where) -> count();
		$page = new Page($count_total, $limit, $param);
		//修改排序规则
		if($order_go == 1 && $order_rule == 1){
			$brush_list =  $model -> table('sj_webanzhi_download_brush') -> where($where) -> 	order('package ASC,brush_time ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 1 && $order_rule == 2){
			$brush_list =  $model -> table('sj_webanzhi_download_brush') -> where($where) -> 	order('package DESC,brush_time DESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 1){
			$brush_list =  $model -> table('sj_webanzhi_download_brush') -> where($where) -> 	order('brush_time ASC,package ASC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}elseif($order_go == 2 && $order_rule == 2){
			$brush_list =  $model -> table('sj_webanzhi_download_brush') -> where($where) -> 	order('brush_time DESC,package DESC') -> limit($page->firstRow.','.$page->listRows) -> select();
		}
		
		for($i = 0;$i < count($brush_list); $i++){
			$map['`package`'] = $brush_list[$i]['package'];
			$soft_info = $model -> table('sj_soft') ->where($map) ->field('softname,hide') -> order('softid DESC') -> limit(1) -> select();
			$brush_list[$i]['softname'] = $soft_info[0]['softname'];
			$brush_list[$i]['hide'] = $soft_info[0]['hide'];
			if(empty($brush_list[$i]['softname'])){
				$brush_list[$i]['softname'] = '<font color="cyan">软件已失效</font>';
			}
			$brush_list[$i]['percent']  = round($brush_list[$i]['percent'] * 10000) / 100 . "%";
			$brush_list[$i]['percentweb']  = round($brush_list[$i]['percentweb'] * 10000) / 100 ."%";
			$mingdan = array('`package`' => $brush_list[$i]['package'],'`status`' => 1);
			$white_data = $model -> table('sj_brush_adapter') ->where($mingdan) -> limit(1) -> select();
			$white_count = count($white_data);
			if($white_count > 0){
				$brush_list[$i]['white']  = 1;
				$brush_list[$i]['note'] = $white_data[0]['note'];
			}else{
				$brush_list[$i]['white']  = 0;
				$brush_list[$i]['note'] = '';
			}
			$brush_list[$i]['web_sum'] = $brush_list[$i]['web_sum'] - $brush_list[$i]['wdj_sum'] - $brush_list[$i]['tx_sum'] - $brush_list[$i]['baidu_sum'];
		}
		//是否显示白名单
		$white_list = isset($_GET['white_list']) ? $_GET['white_list'] : 1;
	
		foreach($brush_list as $key => $val){
			if($white_list == 1){
				$brush_list_all = $brush_list;
			}elseif($white_list == 0){
				if($val['white'] == 0){
					$brush_list_all[] = $val;
				}
			}
		}
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('white_list',$white_list);
		$this -> assign('page', $page->show());
		$this -> assign('order_go',$order_go);
		$this -> assign('order_rule',$order_rule);
		$this -> assign('brush_list',$brush_list_all);
		$this -> display();
	}
	
	//刷量白名单列表
	function brush_white_list(){
		import('@.ORG.Page');
		$param = http_build_query($_GET);
		$limit = 10;
		$where = array();
		if(isset($_GET['lr'])){
			$this->assign('lr',(int)$_GET['lr']);
		}else{
			$this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
			$this->assign('p',(int)$_GET['p']);
		}else{
			$this->assign('p', 1);
		}
		if(isset($_GET['status'])){
			$where = array('`status`' => intval($_GET['status']));
			$this -> assign('status',intval($_GET['status']));
		}else{
			$where = array('`status`' => 1);
			$this -> assign('status', 1);
		}
		if(isset($_GET['package'])){
			$where['`package`'] = array('like',"%".trim($_GET['package'])."%");
			$this -> assign('package',$_GET['package']);
		}else{
			$this -> assign('package', '');
		}
		if(isset($_GET['softname'])){
			$where['`softname`'] = array('like',"%".trim($_GET['softname'])."%");
			$this -> assign('softname',$_GET['softname']);
		}else{
			$this -> assign('softname', '');
		}
		$model = new Model();
		$count_total = $model -> table('sj_brush_adapter') -> where($where) -> count();
		$page  = new Page($count_total, $limit, $param);
		$white_list  = $model -> table('sj_brush_adapter') -> where($where) 
			  -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
		$this -> assign('white_list',$white_list);
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('page', $page->show());
		$this -> display();
	}
	
	//刷量白名单添加
	function brush_white_add(){
		$this->display();
	}
	
	//刷量白名单添加do
	function brush_white_add_do(){
		if(isset($_POST)){
			$data['package'] = trim($_POST['package']);
			$data['note'] = trim($_POST['note']);
			$data['publish_tm'] = time();
			$data['update_tm']  = time();
			$data['status']     = 1;
			if(empty($data['package'])){
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> error('请填写包名');
			}
			if(!preg_match("/^[a-z0-9_\.]+$/i",$data['package'])) {
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> error('包名格式有误');
			}
			$model = new Model();
			$adapter_where = array('`package`' => $data['package'],'`status`' => 1);
			$adapter_count = $model ->table('sj_brush_adapter') -> where($adapter_where) -> count();
			if($adapter_count > 0){
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> error('包名不允许重复');
			}
			$soft_where = array('`package`' => $data['package'],'`status`' => 1);
			$soft_count = $model->table('sj_soft') -> where($soft_where) -> count();
			if($soft_count == 0){
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> error('该包名不存在于软件表中');
			}
			$map = array('`package`' => $data['package'],'`status`' => 1);
			$soft_info = $model -> table('sj_soft') -> where($map) -> limit(1) -> field('softname') -> select();
			$data['softname']  = $soft_info[0]['softname'];
			if ($blank_id = $model -> table('sj_brush_adapter') -> add($data)) {
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> writelog('添加了blank_id为'.$blank_id.'包名为'.$data['package'].'的白名单', 'sj_brush_adapter', $blank_id);
				$this -> success('添加成功');
			}else{
				$this -> assign('jumpUrl','/index.php/Sj/DownloadBrush/brush_white_add');
				$this -> error('添加失败');
			}
		}
	}
	
	//刷量白名单编辑
	function brush_white_edit(){
		if(isset($_GET['blank_id'])){
			$blank_id = intval($_GET['blank_id']);
			$model = new Model();
			$where = array('`blank_id`' => $blank_id);
			$white_info = $model -> table('sj_brush_adapter') -> where($where) -> field('blank_id,package,note') 
						-> limit(1) -> select();
			$this -> assign('white_info',$white_info[0]);
			$this -> display();
		}
	}
	
	//刷量白名单编辑
	function brush_white_edit_do(){
		if(isset($_POST)){
			$blank_id = intval($_POST['blank_id']);
			$data['package']    = trim($_POST['package']);
			$data['note'] = trim($_POST['note']);
			$data['update_tm']  = time();
			if(empty($data['package'])){
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> error('请填写包名');
			}
			if(!preg_match("/^[a-z0-9_\.]+$/i",$data['package'])) {
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> error('包名格式有误');
			}
			$model = new Model();
			$white_where = array('`blank_id`' => $blank_id);
			$white_info = $model -> table('sj_brush_adapter') -> where($white_where) -> field('package') 
						-> limit(1) -> select();
			if($white_info[0]['package'] != $data['package']){
				$adapter_where = array('`package`' => $data['package'],'`status`' => 1);
				$adapter_count = $model ->table('sj_brush_adapter') -> where($adapter_where) -> count();
				if($adapter_count > 0){
					$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
					$this -> error('包名不允许重复');
				}
			}
			$soft_where = array('`package`' => $data['package'],'`status`' => 1);
			$soft_count = $model -> table('sj_soft') -> where($soft_where) -> count();
			if($soft_count == 0){
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				$this -> error('该包名不存在于软件表中');
			}
			$map = array('`package`' => $data['package'],'`status`' => 1);
			$soft_info = $model->table('sj_soft') -> where($map) -> limit(1) -> field('softname') -> select();
			$data['softname']  = $soft_info[0]['softname'];
			$condition = array('`blank_id`' => $blank_id);
			$log = $this->logcheck(array('blank_id'=>$blank_id),'sj_brush_adapter',$data,$model);

			if ($model -> table('sj_brush_adapter') -> where($condition) -> save($data)) {
				$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
				//$this -> writelog('刷量管理_刷量白名单列表_编辑了blank_id为'.$blank_id.'包名为'.$data['package'].'的白名单', 'sj_brush_adapter', $blank_id);
				$this -> writelog('刷量管理_刷量白名单列表_编辑了blank_id为'.$blank_id.$log);

				$this -> success('编辑成功');
			}else{
				$this -> assign('jumpUrl','/index.php/Sj/DownloadBrush/brush_white_edit');
				$this -> error('编辑失败');
			}
		}
	}
	
	//刷量白名单删除
	function brush_white_del(){
		if(isset($_GET['blank_id'])){
			$blank_id = intval($_GET['blank_id']);
			$data['update_tm']  = time();
			$data['status']     = 0;
			$condition = array('`blank_id`' => $blank_id);
			$model = new Model();
			$package = $model -> table('sj_brush_adapter') -> where($condition) -> find(); 
			$model -> table('sj_brush_adapter') -> where($condition) -> save($data); 
			$this -> assign('jumpUrl', '/index.php/Sj/DownloadBrush/brush_white_list');
			$this -> writelog('刷量管理_刷量白名单列表_删除了blank_id为'.$blank_id.'包名为'.$package['package'].'的白名单', 'sj_brush_adapter', $blank_id);
			$this -> success('删除成功');
		}
	}
}

?>
