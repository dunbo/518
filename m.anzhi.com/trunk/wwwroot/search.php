<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
$keyword = $_REQUEST['keyword'];
$morelist = isset($_GET['morelist']) && !empty($_GET['morelist']) ? $_GET['morelist'] : 0;
$result = get_softlist("softv2.GoSearchSoft", $morelist*PAGE_LIMITE, PAGE_LIMITE, 0, array(
            'SEARCH_QUERY' => $keyword,
            'QUERY_CONDITION' => 1234, 
));
$tplObj->out['title'] = $keyword;
//if ($_COOKIE['wap']=="concise"){}
	if ($_GET['morelist'] >= 1){
		if ($result['list']){
			$tplObj->out['search_app'] = scorehtml($result['list']);
		} else {
			$tplObj->out['search_app'] = '';
		}
		$tplObj->display("search_ajax.html");
	} else {
		if ($result['list']){
			$tplObj->out['search_app'] = scorehtml($result['list']);
			$tplObj->out['count'] = $result['count'];
			$tplObj->display("search.html");	
		} else {
			$tplObj->display("search_none.html");
		}
	}
function scorehtml($result){
	foreach($result as $key => $value) {
		$i = $k =0;
		$result[$key]['scorehtml']="";
		$i = floor($value[score] / 2);
		$k = $value[score] % 2;
		for($i1=$i;$i1>0;$i1--){
			$result[$key]['scorehtml'] .='<img alt="" src="/images/star_01.png">';
		}
		if($k!=0)
			$result[$key]['scorehtml'] .= '<img alt="" src="/images/star_02.png">';
		if(($i+$k)<5) {
			for($i2=(5-$i-$k);$i2>0;$i2--){
				$result[$key]['scorehtml'] .='<img alt="" src="/images/star_03.png">';
			}	
		}
	}
	return 	$result;
}