<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
//include_once 'rebate_draw_function.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();

$file = fopen("userid-new.txt","r");
while(!feof($file)){
    $uid = fgets($file); 
    $uid = trim($uid);
    $num = $redis -> get("rebate_draw_num_uid_".$uid);
    if(!is_int($num)){
        $option = array(
                'where' => array(
                        'uid' => $uid,
                ),
                'field' => 'count(*) as draw_num',
                'table' => 'rebate_draw_award',
        );
        $draw_num_arr = $model->findOne($option,'lottery/lottery');
        $draw_num = intval($draw_num_arr['draw_num']);
        
        $option2 = array(
                'where' => array(
                        'uid' => $uid,
                ),
                'table' => 'rebate_draw_money',
        );
        $uid_money = $model->findAll($option2,'lottery/lottery');
        if($uid_money){
            foreach($uid_money as $v){
                $uid_num_arr[$v['money_date']] = $v['draw_data_num'];
            }
        }
        $uid_all_num = intval(array_sum($uid_num_arr));
        if($draw_num >= $uid_all_num ){
        	$update_num = $uid_all_num;
        }else{
            $update_num = $draw_num;
        }
        //$redis -> set("rebate_draw_num_uid_".$uid,$update_num,86400*4);
        
    	echo $uid.'------';  var_dump($num); echo '-----'; var_dump($update_num); 
    	echo "\n";
    }
}

fclose($file);

exit;