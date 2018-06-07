<?php
error_reporting(E_ALL ^ E_NOTICE);
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

session_start();
if (empty($_SESSION['USER_ID'])||$_SESSION['USER_ID']==13176) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}


//加密串

$key='conaudit';

$lable=$_POST['LABLE'];
$category=$_POST['PSURTWDS'];
if(!$category){
	header('HTTP/1.1 417 Expectation Failed');
    exit;
}

$upLoadTime=$_POST['DOWNLOAD_TIME'];
if(!$upLoadTime){
	header('HTTP/1.1 417 Expectation Failed');
    exit;
}

$LRTS=$_POST['RLRTS'];
$lastSign=$_POST['LRTS'];
$DES=new GoDes($key);
$LRTS=$DES->getDecodedDecrypt($LRTS);
$LRTS=json_decode($LRTS,true);


if(count($LRTS)!=8){
	header('HTTP/1.1 417 Expectation Failed');
    exit;
}
$package=$LRTS['package'];
$softname=$LRTS['softname'];
$task_id=$LRTS['task_id'];

$abort_tm=strtotime($LRTS['abort_tm']);//新增审核截止时间
$start_tm=strtotime($LRTS['start_tm']);//新增审核截止时间
$end_tm=strtotime($LRTS['end_tm']);//新增审核截止时间
$taskDes=$LRTS['taskDes'];//新增审核截止时间
$taskTypeText=$LRTS['taskTypeText'];
// $task_type=$LRTS['task_type'];




$imei=$_SESSION['USER_IMEI'];
$mac=$_SESSION['MAC'];
$uid=$_SESSION['USER_ID'];


$image=$_FILES['image'];
//上传图片数量
$count=count($image['name']);

$upload_thumb=array();

$vals=array();
for($i=0;$i<$count;$i++){
	$content_audit_image_file_name = $package."_".msec().".png";
	$vals['raw'][$content_audit_image_file_name]=base64_encode(file_get_contents($image['tmp_name'][$i]));
}
$vals['timestamp']=time();
$vals['checksum']=md5(http_build_query(ksort($vals['raw'])). $vals['timestamp']. 'goDUMMY%x0516');
$host=$configs['dummy']['host'];
$host_dam=$configs['dummy']['host_dam'];

$result=httpGetInfo("http://{$host}/call.php?m=tool&a=uploadImage",$host_dam,http_build_query($vals),'register_pic.log');
$result=json_decode($result,true);
if($result['code']!=200){
	header('HTTP/1.1 417 Expectation Failed');
    exit;
}else{
	$j=0;
	foreach($result['data'] as $k=>$v){
		$upload_thumb["pic_{$j}"]=$v;
		$j++;
	}
}

$model=new GoModel();
if(count($upload_thumb)<1 || count($upload_thumb)>5){
	header('HTTP/1.1 417 Expectation Failed');
    exit;
}
// if($lable==1){
	//旧数据置为无效
	$where = array(
		'uid' => $uid,
		// 'imei' => $imei,
		'task_id' => $task_id,
		'status' => 1,
	);
	
	$data = array();
	$data['status'] = 0;
	$data['__user_table'] = 'sj_red_user_register_taskcontent';
	if ($data) {
		$model->update($where, $data);
	}
// }
$json_data=submit_task(array('task_id'=>$task_id,'package'=>$package,'category'=>$category,'upLoadTime'=>$upLoadTime),$lastSign);
//插入用户表
$insert_o = array(
	'mac' => $mac,
	'imei' => $imei,
	'uid' => $uid,			
	'nickname' => !empty($_SESSION['USER_NICKNAME']) ? $_SESSION['USER_NICKNAME'] : $_SESSION['USER_NAME'],			
	'softname' => $softname,			
	'package' => $package,			
	'create_tm' => time(),
	'upload_thumb' => json_encode($upload_thumb),
	'task_id' => $task_id,
	'abort_tm' => $abort_tm,
	'start_tm' => $start_tm,
	'end_tm' => $end_tm,
	'task_intro' => $taskDes,
	'taskTypeText' => $taskTypeText,
	'submit_task_data' => $json_data,
	'__user_table' => 'sj_red_user_register_taskcontent'
);

// $model->insert($insert_o,'lottery/lottery');	
$model->insert($insert_o);	

header('HTTP/1.1 200 OK');
exit;

// 取得毫秒
function msec ()
{
    list ($msec, $sec) = explode(' ', microtime());
    $msec = substr($msec, 2);
    return $msec;
}

function submit_task($task,$lastSign)
{
	if(!is_array($task)){
		return false;
	}

	$device_arr = array(
        'deviceid'=>$_SESSION['DEVICEID'],
        'osver'=>$_SESSION['FIRMWARE'],
        'nettype'=>$_SESSION['NET_TYPE'],
        'netserver'=>$_SESSION['MODEL_OID'],
        'screen'=>$_SESSION['RESOLUTION'],
        'imsi'=>$_SESSION['USER_IMSI'],
        'mac'=>$_SESSION['MAC'],
        'ip'=>$_SESSION['ip'],
        'abi'=>$_SESSION['ABI'],
        'appversion' => $_SESSION['VERSION_CODE']
    );
	$header = array('appchannel'=>$_SESSION['CHANNEL_ID']);

	$sid = $_SESSION['UCENTER_SID'];

    $extra = array('version'=>'v65');
    //此data缺失数据
    $data = array(
        'pid' => $_SESSION['USER_ID'],
        'taskId' => $task['task_id'],
        'category' =>$task['category'],
        'upLoadTime' =>$task['upLoadTime'],
        'taskType' =>'T50',
        'serviceId' =>'014',
        'taskUniq' =>$task['package'],
        'softPack' =>$task['package'],
        'lastSign' =>$lastSign,//lastSign
        'rewardType' =>'1',
        'softOrActivityId' =>'',
        'sid' =>$sid,
    );
    //一个json把这些全部存进去
    $arr=array(
    	'sid'=>$sid,
    	'data'=>$data,
    	'device_arr'=>$device_arr,
    	'header'=>$header,
    	'extra'=>$extra,
    );
    return json_encode($arr);
}


