<?php
#红包队列脚本
require_once(dirname(__FILE__).'/../init.php');
ini_set('displays_errors', true);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('memory_limit','1024m');
define('MY_PATH', dirname(realpath(__FILE__)));
load_helper('task');
$packdir = '/data/att/permanent_log/redpack/';

$config = load_config('cache/redis','redpacket');
//获取能写的redis config配置
$writeConf = selectConfig($config);

$model = new GoModel();
$redis = new redis();
load_helper('utiltool');
$_SERVER['HTTP_HOST']='api.test.anzhi.com';

$redis->connect($writeConf['host'],$writeConf['port']);
$worker = get_task_worker();
$worker->addFunction("create_red_queue", "create_red_queue");
while ($worker->work());


function create_red_queue($jobs){
    global $packdir,$model,$redis;
	$db_m = 'redpk_m/redpacket';
    $db_s = 'redpk_s/redpacket';
    $redid = $jobs->workload();
    $time = date("H:i:s");
    // $sql = "select * from sj_red_packet_conf where status = 1 and questatus=0 limit 10";
    permanentlog('red_queue_worker.log',"{$time} receive pid: {$redid}");
    $option = array(
        'where'=>array(
            'status' => 1,
            // 'questatus' =>0,
            'id'=>trim($redid),
        ),
        'table'=>'sj_red_packet_conf',
        // 'limit' => 10,
    );
    $pkg = $model->findOne($option,$db_m);
    
    if($pkg){
        $file = $packdir.'/redpack_'.$pkg['id'].'.csv';
        $queuename = 'redpack_list_'.$pkg['id'];
        //如果队列已存在，需要删除，重新生成
        if($redis->exists($queuename)){
            $redis->delete($queuename);
        }
        if(file_exists($file)){
            $con = file($file);
            $i = 1;
            $cashs = array();
            foreach($con as $key=> $val){
                if($key <= 4) continue;
                $pack = explode(',',$val);
                $cashs[] = trim($pack[1]);
                if($i%5000 == 0){
                    $eval_code = implode(',',$cashs);
                    eval("\$redis->lpush('$queuename',$eval_code);");
                    $cashs = array();
                }
                $i++;
            }
            if(!empty($cashs)){
                $eval_code = implode(',',$cashs);
                eval("\$redis->lpush('$queuename',$eval_code);");
            }
            $data['questatus'] = 1;
            $data['__user_table'] = 'sj_red_packet_conf';
            $where = array('id'=>$pkg['id']);
            $model->update($where,$data,$db_m);
            permanentlog('red_queue_worker.log',"{$time} success pid: {$redid}");   
        }else{
            permanentlog('red_queue_worker.log',"{$time} error pid: {$redid},reason: no csv file");   
        }
        
        
    }else{
        permanentlog('red_queue_worker.log',"{$time} error pid: {$redid},reason: packet not exist in db");   
    }
}

function selectConfig($config){
    //单个配置不处理
    $writeConf ='';
    if (isset($config['host'])) {
        $writeConf = $config;
    }elseif(is_array($config)){
        foreach ($config as $item) {
            if($item['write'] == true){
                $writeConf =  $item;
            }
        }
    }
    return $writeConf;
}
