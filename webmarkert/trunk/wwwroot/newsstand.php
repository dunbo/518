<?php
include_once (dirname(realpath( __FILE__ )) . '/init.php');
//define('CAIJI_ATTACHMENT_HOST', 'http://192.168.0.99/testdata/data3');
define('CAIJI_ATTACHMENT_HOST', 'http://caijigameinfo.goapk.com/');
$model = new GoModel();
$tplObj -> out['CAIJI_ATTACHMENT_HOST'] = CAIJI_ATTACHMENT_HOST;
if($_GET['is_details']){
	$id = $_GET['id'];
	newsstand_detail($id);
	//exit;
}elseif($_GET['read_num']){
	$id = $_GET['id'];
	read_num($id);
}else{
	$page = (int)$_GET['page']?$_GET['page'] : 1;
	$limit = 10;
	$offset = ($page - 1) *$limit; 
	$ftype = $_GET['ftype'];
	$fftype = $_GET['fftype'];
	$anzhi_information = array(
		'game' => array(
			'name' => '游戏资讯',
			'id'  => 1,
			'news' => array(
				'name' => '游戏新闻',
				'id' => 1,
			),
			'strategy' => array(
				'name' => '游戏攻略',
				'id' => 2,
			),
		),
		'science' => array(
			'name' => '科技资讯',
			'id'  => 2,
			'net' => array(
				'name' => '互联网',
				'id' => 5,
			),
			'ai' => array(
				'name' => '人工智能',
				'id' => 4,
			),	
			'it' => array(
				'name' => 'it资讯',
				'id' => 3,
			),		
		),
		'society' => array(
			'name' => '社会资讯',
			'id'  => 3,
		),
	);

	$where = array('status' => 1,'passed'=>1);
	if($ftype){
		$where['anzhi_information'] = $anzhi_information[$ftype]['id'];
	}
	if($fftype){
		$where['information_subclass'] = $anzhi_information[$ftype][$fftype]['id'];
	}
	$option = array(
		'table' => 'seo_caiji_news',
		'where' => $where,
		'field' => 'count(*) as total',
		'cache_time' => 600,
	);
	$info = $model->findOne($option,'caiji');
	$count = $info['total'];
	$option['offset'] = $offset;
	$option['limit'] = $limit;
	$option['order'] = 'news_date desc';
	unset($option['field']);
	$flist = $model->findAll($option,'caiji');
	//echo $model->getsql();
	$area = 10;
	$tplObj->out['page'] =  pagination_arr($page, $count, $limit, $area);
	$tplObj -> out['list'] = $flist;
	$tplObj->out['type'] = 'newsstand';

	$tplObj->out['ftype'] = $ftype;
	$tplObj->out['fftype'] = $fftype;
	$tplObj->out['ftypename'] = $anzhi_information[$ftype]['name'] ? $anzhi_information[$ftype]['name'] : '资讯中心';
	$tplObj->out['fftypename'] = $anzhi_information[$ftype][$fftype]['name'] ? $anzhi_information[$ftype][$fftype]['name'] : '';

	$tplObj->out['hot_list'] = hot_information();
	$tplObj->display('newsstand.html');
}
function newsstand_detail($id){
	global $model,$tplObj;
	$where = array('id' => $id);
	$option = array(
		'table' => 'seo_caiji_news',
		'where' => $where,
		'cache_time' => 600,
	);
	$info = $model->findOne($option,'caiji');	
	$info['module_content'] = htmlspecialchars_decode($info['module_content']);
	$find = array("<!--{ANZHI_IMAGE_HOST}-->","CAIJI_ATTACHMENT_HOST");
	$info['module_content'] = str_replace($find, CAIJI_ATTACHMENT_HOST, $info['module_content']);	
	$ftype_config = array(
		1 => array(
			'name'=>'游戏资讯',
			'ftype' => 'game',
		),
		2 => array(
			'name'=>'科技资讯',
			'ftype' => 'science',
		),
		3 => array(
			'name'=>'社会资讯',
			'ftype' => 'society',
		),
	);	
	$info['ftype_config'] = $ftype_config[$info['anzhi_information']];	
	$fftype_config = array(
		1 => array(
			'name' => '游戏新闻',
			'fftype' => 'news',
		),
		2 => array(
			'name' => '游戏攻略',
			'fftype' => 'strategy',
		),
		3 => array(
			'name' => 'it资讯',
			'fftype' => 'it',
		),
		4 => array(
			'name' =>  '人工智能',
			'fftype' => 'ai',
		),
		5 => array(
			'name' =>  '互联网',
			'fftype' => 'net',
		),
	);
	$info['fftype_config'] = $fftype_config[$info['information_subclass']];	
	$info['news_name1'] = mb_substr($info['news_name'],0,25)."...";	
	$tplObj->out['info'] = $info;
	$tplObj->out['hot_list'] = hot_information();
	$tplObj->out['type'] = 'newsstand_detail';
	$tplObj->display('newsstand_detail.html');	
}
function read_num($id){
	$dir = '/tmp/newsstand_lock/';
	if(!file_exists($dir)) mkdir($dir);			
	//文件锁开始
	$path = $dir.date("Ymd").":".$_SERVER['REMOTE_ADDR'].":".$id.".lock";
	if(file_exists($path)){
		return false;
	}else{
		global $model;	
		$data = array(
			'read_num' => array('exp',"`read_num`+1"),
			'__user_table' => 'seo_caiji_news',
		);
		$where = array('id' => $id);
		$ret = $model->update($where, $data, 'caiji');		
		if($ret){
			file_put_contents($path,time());
			return true;
		}else{
			return false;
		}
		
	}
}
function hot_information(){
	global $model;	
	$where = array('status' => 1,'passed' => 1);
	$option = array(
		'table' => 'seo_caiji_news',
		'where' => $where,
		'field' => 'id,news_name,news_pic,news_content',
		'order' => 'read_num desc',
		'limit' => 10,
		'cache_time' => 600,
	);
	$hot_list = $model->findAll($option,'caiji');	
	return $hot_list;
}