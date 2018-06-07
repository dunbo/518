<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
$option = array(
	'where' => array(
		'activity_type' => 1,
		'activity_type_bank' => array('exp','is null')
	),
	'field' => 'id',
	'table' => 'sj_activity'
);
$result = $model -> findAll($option);

foreach($result as $key => $val){
	$id_arr[] = $val['id'];
}

$where = array(
	'id' => $id_arr
);

$data = array(
	'activity_type_bank' => 1,
	'__user_table' => 'sj_activity'
);
$model -> update($where,$data);
echo $model -> getSql();
