<?php
include 'init.php';


$lamp = $double->get_lamp();
if($lamp!=false){
	foreach ($lamp as $key => $value) {
		$lamp[$key]['username'] = $a->str_replace_cn($value['username'], 1, -2 );
	}
}

$a->tplObj -> out['has_lamp'] = !empty($lamp)?1:2;
$a->tplObj -> out['lamp'] = $lamp;

$azb=0;
if(!empty($a->uid))
{
	$res = get_azb($a->uid,$a->aid);
	$azb_data = json_decode($res['data'],true);
	$azb = $azb_data['azmoney'];
	//$azb = 1;
}
$a->tplObj -> out['azb'] = $azb;


/*if($_GET['page']=='buy'){//todo
	$orderarr = array();
	$ii = rand(2,3);
	for($i=1;$i<=$ii;$i++){
		$orderinfo = array(
			'number'=>Double::get_rand_number(),
			'buynumber'=>rand(2,4),
		);
		
		$orderarr[] = $orderinfo;
	}

	$buynumber=0;
	foreach ($orderarr as $key => $value) {
		$buynumber +=$value['buynumber']; 
	}
	print_r($orderarr);
	$rs = $double->add_order($a->puid,$orderarr,$buynumber);
	var_dump($rs);

}
*/

if($_GET['page']=='detail')//活动介绍详情页
{
	$tplObj->display("lottery/{$prefix}/detail.html");
	exit(0);
}

//期号 奖池

$a->index_log();

if($_POST['type']==1)
{
	$number_str='';
	$rand_number = Double::get_rand_number();
	$rand_number_json = json_encode($rand_number);
	foreach ($rand_number as $key => $value) {
		$number_str.='<li>'.$value.'</li>';
	}
	$res_json = array(
		'number_str'=>$number_str,
		'rand_number_json'=>$rand_number_json
	);
	echo json_encode($res_json);exit(0);
}
$rand_number = Double::get_rand_number();
$tplObj->out['rand_number_json']= json_encode($rand_number);
$tplObj->out['rand_number']=$rand_number;


$tplObj->display("lottery/{$prefix}/index.html");
