<?php
/*
 *  超级碗-我的奖品
 */
include_once (dirname(realpath(__FILE__)).'/superbowl_init.php');
if(isset($_POST['id'])&&!empty($_POST['id'])){
    //编辑中奖姓名，手机
    updateUserInfo($_POST['id'],array('name' => $_POST['name'], 'telephone' => $_POST['telephone']));
    echo 1;
    return 1;
}
$active_info  = getActiveConfig();
$activity_config = json_decode($active_info['configcontent'],true);
$activity_award = $activity_config['award_config'];

$myaward = getMyAward(1);

$last_award = array();
foreach($myaward as $k=>$v){
    $v['prizename'] = $activity_award[$v['award']-1]['0'].'&nbsp;&nbsp;'. $activity_award[$v['award']-1]['1'];
    $last_award[] = $v['award'];
    $myaward[$k] = $v;
}
array_multisort($last_award, SORT_ASC, $myaward);
if(isset($_GET['end'])){
    $tplObj->out['end'] = 1;
}
$tplObj->out['myaward'] = $myaward;
$tplObj->display("superbowl/superbowl_myaward.html");