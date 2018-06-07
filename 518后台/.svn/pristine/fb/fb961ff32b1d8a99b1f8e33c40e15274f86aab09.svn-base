<?php
/*生成红包队列脚本*/
if(php_sapi_name()!='cli'){
    exit(0);
}
set_time_limit(0);
ini_set('memory_limit','1024m');
error_reporting(E_ALL & ~E_NOTICE);
//$alive = 3600*24*30;
define('MY_PATH', dirname(realpath(__FILE__)));
$config = include_once MY_PATH.'/../Conf/config.php';
$link = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME']);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
$dir =$config['RED_PACKET_CSV'];
mysqli_set_charset($link,'utf8');
$sql = "select * from sj_red_packet_conf where status = 1 and questatus=0 limit 20";
$query = mysqli_query($link,$sql);
$redis = new redis();
$redis->connect($config['REDPACKET_REDIS_HOST'],$config['REDPACKET_REDIS_PORT']);

while($res = mysqli_fetch_assoc($query)){
    if($res['taskid']>0||$res['activeid']>0){
        $file = $dir.'/redpack_'.$res['id'];
        $queuename = 'redpack_list_'.$res['id'];
        //如果队列已存在，需要删除，重新生成
        if($redis->exists($queuename)){
            $redis->delete($queuename);
        }
        if(file_exists($file)){
            $con = file($file);
            foreach($con as $key=> $val){
                if($key <= 4) continue;
                $pack = explode(',',$val);
                $redis -> RPUSH($queuename,trim($pack[1]));
            }
            $sqlupdate = "update sj_red_packet_conf set questatus = 1 where id= {$res['id']}";
            mysqli_query($link,$sqlupdate);
            
        }
    }
    
}
