<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
//$morelist *= 20;
$inofret = gomarket_action('soft.GoGetNecessaryExtent',array("LIST_INDEX_START" => $morelist * 20, "LIST_INDEX_SIZE"=>20,'VR' => 1));
foreach($inofret['DATA'] as $key => $value){
	foreach($value['CHILD_GROUP'] as $k => $v){
		if($v[3]){
			$i = $k1 =0;
			$inofret['DATA'][$key]['CHILD_GROUP'][$k]['scorehtml']="";
			$i = floor($v[3] / 2);
			$k1 = $v[3] % 2;
			for($i1=$i;$i1>0;$i1--){
				$inofret['DATA'][$key]['CHILD_GROUP'][$k]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
			}
			if($k1!=0)
				$inofret['DATA'][$key]['CHILD_GROUP'][$k]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
			if(($i+$k1)<5) {
				for($i2=(5-$i-$k1);$i2>0;$i2--){
					$inofret['DATA'][$key]['CHILD_GROUP'][$k]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
				}	
			}
		} else {
			$img = '';
			for ($i=1; $i<=5; $i++){
				$img .= '<img alt="" src="/images/star_03.png">';
			}
			$inofret['DATA'][$key]['CHILD_GROUP'][$k]['scorehtml'] = $img;
		}
	}
}
$tplObj->out['home_install'] = $inofret['DATA'];
$tplObj->out['title'] = '装机必备';
if ($_GET['morelist'] >= 1){
	$tplObj->display("inapp_ajax.html");
} else {
	$tplObj->display("inapp.html");
}
