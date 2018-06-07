<?php


//需配置page_result share_text不为空
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();
$activity_option = array(
    'table' => 'superbowl_ranklist',
    'field'=>'package'
);
$activity_result = $model -> findAll($activity_option,'lottery/lottery');
//var_dump($activity_result);
$pacakge = array();
foreach($activity_result as $k=>$v){
    $package[] = $v['package'];
}
$option = array(
    'table' => 'sj_actives_soft',
    'where'=>array('package'=>$package,'category_id'=>array('242','243'),'status'=>1),
    'field'=>'soft_name,package'
);
$result = $model -> findAll($option);

foreach($result as $k=>$v){
    $where = array('package'=>$v);
    $u_data = array(
        'softname'=>$v['soft_name'],
        '__user_table' => 'superbowl_ranklist'
    );
    $model -> update($where,$u_data,'lottery/lottery');
    echo $model->getSql()."<br>";
}
exit();
$config_type  = 'SUPERBOWL_LOTTERY_AWARD';
$configname = '安智超级碗之爱普快到碗里来';
$configcontent = array(
    'activity_id' => '415',
    'promotion_share_url' =>'http://promotion.anzhi.com/lottery/superbowl/index.php',
    'award_config'=>array(
        "0"=>array("一等奖",'iPad mini','1','',''),
        "1"=>array("二等奖",'iWatch','1','',''),
        "2"=>array("三等奖",'OPPO R7 plus手机','1','',''),
        "3"=>array("四等奖",'500元苏宁易购卡','1','',''),
        "4"=>array("五等奖",'百度影棒','1','',''),
        "5"=>array("六等奖",'100元京东卡','1','',''),
        "6"=>array("七等奖",'50元京东卡','1','','')
    )
);
$configcontent = json_encode($configcontent);
$sql = "insert into `pu_config`  (`config_type`, `configname`, `configcontent`) values('{$config_type}','{$configname}','{$configcontent}')";
echo $sql;
exit();
$res = $model->query($sql);
echo $model->getSql();
var_dump($res);

