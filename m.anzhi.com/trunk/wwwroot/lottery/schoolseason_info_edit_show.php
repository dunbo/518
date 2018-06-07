<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$id = $_GET['id'];
$model = new GoModel();
$option = array(
	'where' => array(
		'id' => $id
	),
	'field' => 'name,telephone',
	'table' => 'schoolseason_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');
if($result){
	echo json_encode($result);
	return json_encode($result);
}
