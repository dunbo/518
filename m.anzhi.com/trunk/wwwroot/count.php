<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

require_once dirname(__FILE__) . '/init.php';

$id = $_GET['id'];
if (empty($id))
    exit;
$model = new GoModel();
$where = array('id' => $id );

if($_GET['type'] == 1){
	$num = rand(3,10);
	$data  = array('visit_count' => array("exp", "visit_count+{$num}"));
	$where['status'] = 1 ;
	$table = 'sj_soft_content_explicit';
}else{
	$data  = array('visit_count' => array('exp', 'visit_count+1'));
	$where['status'] = 2 ;
	$table = 'sj_olgame_news';
	$data['visit_update_tm']  = time();
}

$data['__user_table']  =  $table;
$model->update($where, $data);

?>
