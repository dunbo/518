<?php
include 'init.php';

if($_GET['page']=='kai')
{
	//缓存化
	$show_lists = $a->redis->get('double_color_ball:show_lists_kai'.$a->puid);
	//if(empty($show_lists)){ //todo
	if(true){
		$buy_list = $double->get_end_issues();
		$show_lists = array();
		foreach ($buy_list as $key => $value) {
			$number = str_replace(array('[0',',0'),array('[',','),$value['kai_number']);
			$buy_infos = $double->get_number_kai_byuid($value['id'],$a->puid,$number);
			foreach (json_decode($number,true) as $k => $v) {
	            		$str .= "<li>".$v."</li>";
	        }

			$show_lists[$key]['kai_number']= $str;
			$show_lists[$key]['issue']= $value['id'];
			if($buy_infos==false){
				$message='本期未参与';
			}else if(empty($buy_infos['aw_str'])){
				$message='本期未中奖';
			}else{
				$message=$buy_infos['aw_str'];
				unset($buy_infos['aw_str']);
			}
			$show_lists[$key]['message']= $message;
			$str='';
			$show_lists[$key]['buy_infos']= $buy_infos;
		}
		$a->redis->set('double_color_ball:show_lists_kai'.$a->puid,$show_lists,3600);
	}
	// print_r($show_lists);
	$tplObj -> out['show_lists'] = $show_lists;
	$tplObj->display("lottery/{$prefix}/myprize_kai.html");
	exit(0);
}


$buy_list = $double->get_number_byuid($a->puid);
$tplObj -> out['buy_list'] = $buy_list;
$tplObj->display("lottery/{$prefix}/myprize.html");

