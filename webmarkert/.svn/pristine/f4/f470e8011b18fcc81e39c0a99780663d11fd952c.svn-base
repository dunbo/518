<?php
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
load_helper("utiltool");
include_once('../model/scanSoft.php');
$re = scanSoft::getResponseJS();
if(empty($re)){
	echo "error:no get response data";
	exit();
}
$result = $re;
$hash = $result['m'];

$safe_code_arr = array(0,1,-1);  //0为安全 1为病毒 -1为系统错误
if(!in_array($re['c'],$safe_code_arr)){
        permanentlog('js_response_error.log',json_encode($re).'|'.date('Y-m-d H:i:s'));
        exit;
}

$sis_error_code = array('upload failed','no result this moment'); //金山系统错误
$sec_request_code = array('bad url','invalid md5','download failed','get file md5 failed','unknown error');//需要重新发送请求
if($re['c'] == -1 ){
	permanentlog('js_response_error.log', json_encode(array(
				 'md5' => $re['m'],
				 'error_code' => $re['c'],
				 'code_msg' => $re['cm'],
				 'time'=>date("Y-m-d H:i:s",time()),
	)));
	if(in_array(strtolower($re['cm']),$sec_request_code)){
		$option = array(
			'table' => 'sj_soft_scan_result',
			'where' => array(
				'hash' => $hash,
				'provider' => 4,
			),
			'field' => 'sfid',
		);
		$err_list = $model -> findAll($option);
		foreach($err_list as $info){
		   $option = array(
			'where' => array(
				'id' => $info['sfid'],
			),
			'field' => 'url,md5_file',
			'table' => 'sj_soft_file'
		   );
		   $result = $model -> findOne($option);
			$params = array(
				"download_url" => load_config('full_static_host').$result['url'],
				"soft_hash" => $result['md5_file'],
			);
		   $re = scanSoft::requestGetJS($params);
		}
	}
exit;
}


# 更新到新表
$model = new GoModel();



//统计当天不安全软件的个数
$sj_unsafe_scan_cnt_table = 'sj_unsafe_scan_count';
$call_back_tm = strtotime(date('Y-m-d',time()));
$option = array(
	'table' => $sj_unsafe_scan_cnt_table,
	'where' => array(
		'safe_type' => 4,
		'callback_tm' => $call_back_tm,
	),
	'field' => 'count'
);
$info_cnt = $model -> findOne($option);
if(empty($info_cnt)){
	$insert_data = array(
		'safe_type' => 4,
		'callback_tm' => $call_back_tm,
		'count' => 0,	
		'__user_table' => $sj_unsafe_scan_cnt_table,
	);
	$model -> insert($insert_data);
}
$cnt = $info_cnt['count'] ? $info_cnt['count'] : 0;
$js_unsafe_cnt = $cnt ? $cnt : 0;


$data = array(
	'hash' => $hash,
);
$temp = $model->findAll(
	array(
		'table' => 'sj_soft_scan_result',
		'where' => $data,
	)
);
$id = 0;
$exists = false;
foreach ($temp as $t) {
	if ($t['provider'] == 4) {
		$exists = true;
		break;
	}
}
if ($exists) {
	$data = array();
	$data['time_rep'] = time();
	$safe = $result['c'];
	$safe_code = 0; //0为安全
	$status = $safe == $safe_code ? 1 : 2;
	$data['safe'] = $status;
	$data['description'] = json_encode($result['cm']);
	$data['__user_table'] = 'sj_soft_scan_result';
	if($js_unsafe_cnt > 100 && $data['safe'] > 1){ //当天不安全软件数量超100 and为不安全软件 写日志
		$sql = 'update '.$data['__user_table'].' set description ='.$data['description'].', safe ='.$data['safe'].', time_rep=UNIX_TIMESTAMP(NOW()) where hash ='.$hash.' and provider = 4'.";\n";
		permanentlog('js_unsafe_soft_sql.log',$sql);
	}else{
		$model->update(array('hash' => $hash, 'provider' => 4), $data, 'master');
		permanentlog('js_query_sql.log',$model -> getSql()."\n");
	}
}
# 更新到新表

$data = array(
	'hash' => $hash,
);
$temp = $model->findOne(
	array(
		'table' => 'sj_soft_scan_result',
		'where' => $data,
	)
);
if (!$temp) {
	echo 'found no soft';
    exit;
}
$id = $temp['sfid'];
$temp = $model->findOne(
    array(
        'table' => 'sj_soft_file',
        'where' => array('id' => $id),
    )
);
if (!$temp) {
    # 软件未找到
	echo 'found no soft';
    exit;
}
$softid = $temp['softid'];
if (!$softid || ($softid != intval($softid))) {
    # bad softid
	echo 'bad softid';
    exit;
}
$temp = $model->findAll(
    array(
        'table' => 'sj_soft_file',
        'where' => array('softid' => $softid)
    )
);
if (!$temp){
    echo 'soft no exists';
    exit;
}
$sfids = array();
foreach ($temp as $val)
    $sfids[] = $val['id'];

$temp = $model->findAll(
    array(
        'table' => 'sj_soft_scan_result',
        'where' => array('sfid' => $sfids),
    )
);

$j = 0;
$k = 0;
for ($i = 0; $i < count($temp); $i++)
{
    # 安全
    if ($temp[$i]['safe'] == 1)
        $j++;
    # 危险
    if ($temp[$i]['safe'] >= 2)
        $k++;
}
# 全部安全
if ($i == $j) {
    $safeinfo = array('safe' => 1, '__user_table' => 'sj_soft');
    $model->update(array('softid' => $softid), $safeinfo);
	exit;
}
if($k > 0){
	$js_unsafe_cnt = $js_unsafe_cnt + 1;  	//记录不安全软件下架的个数
	$scan_unsafeinfo = array('count' => $js_unsafe_cnt,'__user_table' => $sj_unsafe_scan_cnt_table);
	$model -> update(array('safe_type' => 4 ,'callback_tm' => $call_back_tm),$scan_unsafeinfo);
}
include_once GO_APP_ROOT.DS.'function.php';
if ($k > 0 && $js_unsafe_cnt <100) {
    $safeinfo=array('safe' => 2, '__user_table' => 'sj_soft');
	//白名单
	$white_pkg = existswhitepkg($softid);
	if($white_pkg['cnt']==0){
	    $safeinfo['hide'] = 3;   //只有软件在不在白名单中 hide 可直接设置为 3
	}else{
		$safeinfo['safe'] = -1;  //软件在白名单中 且 为不安全软件 safe 为 -1
	}
    $model->update(array('softid' => $softid), $safeinfo);
	$sql = $model->getSql();
	permanentlog('js_query_sql.log',$sql);
}else if($k > 0 && $js_unsafe_cnt >= 100){
	//白名单
	$white_pkg = existswhitepkg($softid);
	if($white_pkg['cnt']==0){
	    $set = 'hide = 3 , safe = 2';   		//只有软件在不在白名单中 hide 可直接设置为 3
	}else{
		$set = 'safe = -1';  					//软件在白名单中 且 为不安全软件 safe 为 -1
	}
	$sj_soft_sql  = 'update sj_soft set '.$set.' where softid = '.$softid.";\n";
	permanentlog('js_unsafe_soft_sql.log',$sj_soft_sql);
}

echo '200';
