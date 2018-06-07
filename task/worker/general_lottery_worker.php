<?php
/*
 *   通用抽奖worker
 */
include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("general_lottery", "get_award");
while ($worker->work());

function get_award($jobs){
                global $model;

                global $redis;

                $jobs= $jobs->workload();

                $jobs = json_decode($jobs,true);
                print_r($jobs);
                
                $aid = $jobs['aid'];
                $imsi = $jobs['imsi'];
                $package = $jobs['package'];
                $other_packages = $jobs['package_arr'];


                $redis->pingConn();
                $res = $redis -> get('general_lottery_probability:'.$aid);
                //$res = 1;//supwater
                if(empty($res)){
                    echo 'once more!';
                                  $nowtime = time();

                                  $option = array(
                                            'where' => array(//查询条件
                                                'b.activate_type' => 4,
                                                'a.status' => 1,
                                                'b.status' => 1,
                                                'a.start_tm' => array('exp', '<='.$nowtime.''),
                                                'a.end_tm' => array('exp', '>='.$nowtime.''),
                                            ),
                                            'table' => 'sj_activity AS a',
                                            'field' => 'a.id',
                                            'join' => array(
                                                'sj_activity_page AS b' => array(
                                                        'on' => array('a.activity_page_id', 'b.ap_id')
                                                )
                                            )
                                        );
                                $aidarr = $model->findAll($option);

                                foreach($aidarr as $aid){
                                    $aid = $aid['id'];
                                    //$aid=210;
                                    //处理中奖率
                                    $nowday= strtotime(date('Y-m-d'));

                                               $my_option = array(
                                                        'where' => array(//查询条件
                                                            'a.aid' => $aid,
                                                            'a.status' => 1,
                                                            'b.status' => 1,
                                                            'a.begin_tm' => array('exp', '<='.$nowday.''),
                                                            'a.end_tm' => array('exp', '>='.$nowday.''),
                                                        ),
                                                        'order' => 'a.pid',
                                                        'table' => 'gm_probability AS a',
                                                        'field' => 'a.*',
                                                        'join' => array(
                                                            'gm_lottery_prize AS b' => array(
                                                                    'on' => array('a.pid', 'b.pid')
                                                            )
                                                        )
                                                    );

                                                $my_result = $model -> findAll($my_option,'lottery/lottery');
                                                //echo $model->getsql();
                                                //取分母的最大值 作为基数
                                                foreach($my_result as $v){
                                                    $redis -> set('general_lottery_prize_probabilityid:'.$v['pid'],$v['id'],86400);
                                                    //处理中奖率
                                                    if($v['probability']==0){continue;}
                                                    $rs = explode('/',$v['probability']);
                                                    if(!isset($basenum)){
                                                        $basenum = $rs[1];
                                                        continue;
                                                    }else{
                                                        $basenum = min_multiple($basenum,$rs[1]);
                                                        //$basenum = $basenum*$rs[1];
                                                    }
                                                }

                                                $gift_base = array();
                                                foreach($my_result as $vvv){
                                                    $rs = explode('/',$vvv['probability']);
                                                    $gift_base[$vvv['pid']] = $rs['0']/$rs['1']*$basenum;
                                                }
                                                $newarr = array();
                                                $newarr['basenum'] = $basenum;
                                                $newarr['gift_base'] = $gift_base;

                                                $rs = $redis -> set('general_lottery_probability:'.$aid,$newarr,1200);
                                                unset($basenum);

                                                //查询奖品类型以及当日的中奖率ID  每日累加处理
                                                $my_option_prize = array(
                                                        'where' => array(
                                                                'aid' =>$aid,
                                                                'status' =>1,
                                                        ),
                                                        'field' => 'pid,type,level,name',
                                                        'table' => 'gm_lottery_prize'
                                                );
                                                $my_result_prize = $model -> findAll($my_option_prize,'lottery/lottery');
                                                foreach($my_result_prize as $vv)
                                                {
                                                    $pid = $vv['pid'];
                                                    $type = $vv['type'];
                                                    $redis -> set('general_lottery_prize_type:'.$pid,$type,86400);
                                                    $redis -> set('general_lottery_prize_level:'.$pid,$vv['level'],86400);
                                                    $redis -> set('general_lottery_prize_name:'.$pid,$vv['name'],86400);
                                                }


                                               $my_option = array(
                                                        'where' => array(//查询条件
                                                            'a.aid' => $aid,
                                                            'a.status' => 1,
                                                            'b.status' => 1,
                                                            'a.begin_tm' => array('exp', '<='.$nowday.''),
                                                            'a.end_tm' => array('exp', '>='.$nowday.''),
                                                        ),
                                                        'order' => 'a.pid',
                                                        'table' => 'gm_probability AS a',
                                                        'field' => 'a.*',
                                                        'join' => array(
                                                            'gm_lottery_prize AS b' => array(
                                                                    'on' => array('a.pid', 'b.pid')
                                                            )
                                                        )
                                                    );

                                                
                                                $my_result = $model -> findAll($my_option,'lottery/lottery');                
                                                foreach($my_result as $lv){
                                                    $redis -> set('general_lottery_nownum:'.$aid.':'.$lv['pid'], intval($lv['now_num']));
                                                }
                                }
                }
                $aid = $jobs['aid'];
                $res = $redis -> get('general_lottery_probability:'.$aid);
                //$nowtime = strtotime(date('Y-m-d'));
                echo 'general_lottery_probability:'.$aid;
                var_dump($res);
                if(empty($res)){
                    return -1; //supwater
                }

                $pid = lottery($res['gift_base'],$res['basenum']); //supwater
                echo 'pid:';
                var_dump($pid);
                
                //中奖，检查数量 如数量不足 返回未中奖
                //$pid=176;//supwater
                if($pid!=-1){
                    // 奖品数量-1
                    $now_num = $redis -> setx('incr','general_lottery_nownum:'.$aid.':'.$pid, -1);
                    //$now_num = 1;//supwater
                    if ($now_num < 0) {
                        echo 'now_num:';
                        var_dump($now_num);
                        // 没有剩余奖品了，把缓存数量还原为0
                        $now_num = $redis -> set('general_lottery_nownum:'.$aid.':'.$pid, 0);
                        return -1;
                    }

                    //减少库中实际数量  按照中奖率ID去更新 每个奖品ID 当日对应的中奖率ID
                    $vid = $redis -> get('general_lottery_prize_probabilityid:'.$pid);
                    $where = array(
                        'id' =>$vid
                    );

                    $data = array(
                        //'now_num' => $now_num,
                        'now_num' => array('exp','now_num-1'),
                        '__user_table' => 'gm_probability'
                    );
                    $model -> update($where,$data,'lottery/lottery');

                        $now_tm= time();
                        $type = $redis -> get('general_lottery_prize_type:'.$pid);
                        $level= $redis -> get('general_lottery_prize_level:'.$pid);
                        $name = $redis -> get('general_lottery_prize_name:'.$pid);

                        //$type=2; //supwater
                        if($type==1){ //实体
                                $award_data = array(
                                        'imsi' => $imsi,
                                        'level' => $level,
                                        'name' => '',
                                        'telphone' => '',
                                        'address' => '',
                                        'time' => $now_tm,
                                        'status' => 0,
                                        'aid' => $aid,
                                        'pid' => $pid,
                                        '__user_table' => 'gm_lottery_award'
                                );
                                $insertid = $model -> insert($award_data,'lottery/lottery');
                                var_dump($model->getsql());
                                //写入缓存
                                $imsiarr= array();
                                $imsiarr[$imsi] = array(
                                'level'=>$level,
                                'time'=>$now_tm,
                                'status'=>0,
                                'pid'=>$pid,
                            );
                                //$redis -> sethash_overwrite("general_lottery_imsi:info_{$aid}",$imsiarr);
                                $redis -> sethash("general_lottery_imsi:info_{$aid}",$imsiarr);
                        }else if($type==2){ //虚拟

                                //处理  redis 的礼包
                                if(empty($package)){
                                    return -1;
                                    /*
                                    $gift_number = json_decode($redis -> rpop("general_lottery:virtual_{$pid}"),true);
                                    if(empty($gift_number['first_text'])){
                                        return -1;
                                    }
                                    */
                                }else{
                                    $gift_number = $redis->getlist("general_lottery:virtual_{$package}_{$pid}");
                                    $gift_number = json_decode($gift_number[0],true);
                                    if($package != $gift_number['first_text']){
                                        $gift_number = get_gift($pid,$other_packages,$package);
                                        if($gift_number == -1){return -1;}
                                        $gift_number = json_decode($gift_number,true);
                                    }else{
                                        $nodb = 200;
                                    }
                                }
                                if($nodb!=200){
                                    $where = array(
                                        'first_text' =>$gift_number['first_text'], //redis里来的
                                        //'aid' =>$aid,
                                        'pid' =>$pid,
                                    );
                                    $data = array(
                                        'imsi' => $imsi,
                                        'status' => 1,
                                        'update_tm' => time(),
                                        '__user_table' => 'gm_virtual_prize'
                                    );
                                    $model -> update($where,$data,'lottery/lottery');
                                    var_dump($model->getsql());
                                }
                        }
                        $resarr = array();
                        $resarr['pid'] = $pid;
                        $resarr['insertid'] = $insertid;
                        $resarr['name'] = $name;
                        $resarr['level'] = $level;
                        $resarr['imsi'] = $imsi;
                        $resarr['type'] = $type;
                        $resarr['gift_number'] = $gift_number;
                        print_r($resarr);
                        return json_encode($resarr);
                }
                return -1;
}


function get_gift($pid,$other_packages,$package){
    //$other_packages 是该活动所有虚拟奖的包名集合

    global $redis;

    $new_pkg = $redis->get("general_lottery:package_{$pid}");

    if(!empty($other_packages)){
        //递归之后 可能删掉用完的了 所以需要取交集
        $new_pkg = array_intersect($new_pkg,$other_packages);
    }

    print_r($new_pkg);
    if(empty($new_pkg)){
        //有可能没有礼包了，也有可能配置是过滤已发过，所有的都装过了
        return -1;
    }

    if(empty($package)||!in_array($package,$new_pkg)){
        //正常来说 $package不会为空， 这里为递归用
        $pkg_key = array_rand($new_pkg);
        $package = $new_pkg[$pkg_key];
    }


    //中礼包   包名 
    //处理  redis 的礼包
    $gift_number = $redis -> rpop("general_lottery:virtual_{$package}_{$pid}");
    if(empty($gift_number)){
        $gift_number = $redis -> rpop("general_lottery:virtual_{$package}_{$pid}");
        if(empty($gift_number)){
            //删除 没有礼包的包名
            $new_pkg = $redis->get("general_lottery:package_{$pid}");
            foreach($new_pkg as $k=>$v){
                if($v==$package){
                    echo 'unset...pkg';
                    unset($new_pkg[$k]);
                }
            }
            $redis->set("general_lottery:package_{$pid}",$new_pkg);
            return get_gift($pid,$other_packages);
        }
    }
    return $gift_number;
}


function min_multiple($a,$b)  //最小公倍数
{    
     $m = max($a,$b);
     $n = min($a,$b);
     for($i=1; ; $i++)
     {
         if (is_int($m*$i/$n))
         {
            return $mix=$m*$i;
         }
     }
}


function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    if(!$gift_base){
        return -1;
    }

    foreach ($gift_base as $k=>$v) {
        $gift_line[$k] = array($nows+1, $nows+$v);
        $nows += $v;
    }

    $rand = mt_rand(1, $sum);

    foreach ($gift_line as $k => $v) {
        if ($rand >= $v[0] && $rand <= $v[1]) {
            return $k;
        }
    }
    return -1;
}
