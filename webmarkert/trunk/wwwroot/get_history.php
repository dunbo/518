<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
ini_set('display_errors', 'on');
error_reporting(E_ERROR);
if($_GET['history_softid']){
	$history_vode = gomarket_action('soft.GoGetHistorySoft',array("ID"=>$_GET['history_softid'],'GET_COUNT' => true));
	foreach($history_vode['DATA'] as $key => $val){
		$vode_arr[] = $val[5];
	}
	$my_vode = FetchRepeatMemberInArray($vode_arr);

	if($my_vode){
		foreach($history_vode['DATA'] as $key => $val){
			if(in_array($val[5],$my_vode)){
				$val[5] = $val[5].'_'.$val[6];
				$history_vode['DATA'][$key] = $val;
			}
		}
	}

	$history_vodes = array_slice($history_vode['DATA'],0,6);
	$history_vodes_count = $history_vode['COUNT'];
	$history_message = array($history_vodes,$history_vodes_count);
	echo json_encode($history_message);
	return json_encode($history_message);
}

//历史版本
function FetchRepeatMemberInArray($array) {  
    // 获取去掉重复数据的数组  
    $unique_arr = array_unique ( $array );  
    // 获取重复数据的数组  
    $repeat_arr = array_diff_assoc ( $array, $unique_arr );  
    return $repeat_arr;  
} 