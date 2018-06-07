<?php
/*
 *  @desc  检测市场版本
 *               $alone_update 是否为独立更新包
*/
function chk_market_version($alone_update,$cid){
    global $model;
    $intro_result = '';
    if ($_SESSION['VERSION_CODE'] < 5700) {
        // 版本号小于6.0，需要检查是否为独立更新包，且如果是独立更新包，需要判断能否升级
        if ($alone_update) {
            // 独立更新包
            $channel_option = array(
                'where' => array(
                    'cid' => $cid,
                    'status' => 1,
                    'version_code' => array('exp','>=5700'),
                    'limit_rules' => array('exp'," ='' or limit_rules is null "),
                ),
                'cache_time' => 3600,
                'table' => 'sj_market',
            );
            $channel_result = $model -> findAll($channel_option);
            if (!empty($channel_result)) {
                //可升级
                $check_status = 3;
            } else {
                //不可升级
                $check_status = 4;
            }
        } else {
            $check_status = 2;
            // 非独立更新包
            $intro_option = array(
                'where' => array(
                    'package' => 'cn.goapk.market'
                ),
                'field' => 'softid,softname,version_code',
                'order' => 'softid DESC',
                'limit' => 1,
                'cache_time' => 86400,
                'table' => 'sj_soft'
            );
            $intro_result = $model -> findOne($intro_option);

            $intro_size_option = array(
                'where' => array(
                    'softid' => $intro_result['softid']
                ),
                'field' => 'filesize',
                'table' => 'sj_soft_file',
                'cache_time' => 86400
            );
            $intro_size_result = $model -> findOne($intro_size_option);
            $intro_result['soft_sizes'] = formatFileSize('',$intro_size_result['filesize']);
            $intro_result['soft_size'] = $intro_size_result['filesize'];
        }
    } else {
        $check_status = 1;
    }
    //var_dump($check_status);
    return array($check_status,$intro_result);
}

/*
 *  @desc  获取中奖最后10条
*/
function getUserAward($limit =10){
    global $model,$redis,$activity_config;
    $data = array();
    //$redis -> delete("superbowl_award_limit".$limit);
    $data = $redis -> get("superbowl_award_limit".$limit);
    if(!$data){
        $option = array(
            'where' => array(
                'status' => 1
            ),
            'table' => 'superbowl_winner',
            'limit' => 10,
            'order' => 'id desc'
        );
        $data = $model->findAll($option,'lottery/lottery');
       // echo $model->getSql();
        if($data){
            $redis -> set("superbowl_award_limit".$limit,$data,60*5);
        }
    }
    if($data){
        foreach($data as &$val){
            $val['add_tm'] = date("Y-m-d",$val['add_tm']);
            $val['prizename'] = $activity_config['award_config'][$val['award']-1][0]."&nbsp;&nbsp;". $activity_config['award_config'][$val['award']-1][1];
            $val['telephone'] = str_replace(mb_substr($val['telephone'],3,4,'utf-8'),'****',$val['telephone']);
        }
    }
    return $data;
}

/*
 *获取无效（未填电话信息）的中奖信息
 */
function getInvalidAward(){
    global $model,$redis,$imsi;

    $option = array(
        'where' => array(
            'imsi' => $imsi,
            'status' => 0,
            'telephone'=>'',
            'name'=>''
        ),
        'table' => 'superbowl_winner'
    );
    $data = $model->findOne($option,'lottery/lottery');
    //echo $model->getSql();
    if($data){
       return $data;
    }else{
        return 0;
    }
}
/*
 *  @desc 获取用户中奖信息
*/
function getMyAward($from_redis=1){
    global $model,$redis,$imsi,$active_id;
  //$redis -> delete("superbowl_award_{$active_id}_{$imsi}");
    if($from_redis == 1){
        $data = $redis -> gethash("superbowl_award_{$active_id}_{$imsi}");
    }
    if(!$data){
        $option = array(
            'where' => array(
                'imsi' => $imsi
            ),
            'table' => 'superbowl_winner'
        );
        $data = $model->findAll($option,'lottery/lottery');
        //echo $model->getSql();
        if($data){
            foreach($data as $k=>$v){
                $data[$k]['add_tm'] = date("Y-m-d",$v['add_tm']);
            }
            $redis -> sethash("superbowl_award_{$active_id}_{$imsi}",$data,86400*15);
        }
    }
    return $data;
}

/*
 *  @desc 更新用户中奖联系信息
*/
function updateUserInfo($id,$data){
    global $model,$active_id,$sid,$redis,$imsi;
    $where = array('id' => $id);
    $data_update = array(
        'name' => $data['name'],
        'telephone' => $data['telephone'],
        'up_tm' => time(),
        '__user_table' => 'superbowl_winner'
    );
    $res = $model -> update($where,$data_update,'lottery/lottery');
    //日志
    $log_data = array(
        'imsi' => $_SESSION['USER_IMSI'],
        'device_id' => $_SESSION['DEVICEID'],
        'activity_id' => $active_id,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'sid' => $sid,
        'time' => time(),
        'key' => 'update_userinfo'
    );
    permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
    //更新用户获奖缓存
    $redis -> delete("superbowl_award_{$active_id}_{$imsi}");
    getMyAward(0);
}
/*
 *  @desc 获取抽奖次数
*/
function getLotteryNum() {
    global $imsi, $model, $redis, $rkey_imsi_lottery_num;
    //$redis->delete($rkey_imsi_lottery_num);
    $now = time();
    $now_num = $redis->get($rkey_imsi_lottery_num);
//    var_dump($imsi);
//    var_dump($now_num);
    if (empty($now_num) && $now_num !== 0) {
        // 尝试从表里读出来
        $option = array(
            'where' => array(
                'imsi' => $imsi
            ),
            'table' => 'superbowl_lottery',
        );
        $find = $model->findOne($option, 'lottery/lottery');
        if (empty($find)) {
            $now_num = 0;
            $ret = $model->query("insert into `superbowl_lottery` (`imsi`, `lottery_num`, `up_tm`) values ('{$imsi}', {$now_num}, {$now}) ON DUPLICATE KEY UPDATE `lottery_num`={$now_num};", 'lottery/lottery');
        } else {
            $now_num = $find['lottery_num'];
            $now_num = (int)$now_num;
        }
        // 更新缓存
        $redis->set($rkey_imsi_lottery_num, $now_num, 86400*15);
    }
    return $now_num;
}

/*
 *  @desc 用户可抽奖次数-1，如果没有抽奖机会，直接返回false
 */
function reduceLotteryNum() {
    global $imsi, $model, $redis, $rkey_imsi_lottery_num;

    // 先尝试获得抽奖机会
    $now_num = getLotteryNum();
    $now_num = (int)$now_num;
    if ($now_num <= 0) {
        // 没有抽奖机会
        return false;
    }

    $now = time();
    // 可抽奖次数-1
    $now_num = $redis -> setx('incr',$rkey_imsi_lottery_num, -1);
    if (!is_int($now_num)) {
        // 出错
        return false;
    }
    if ($now_num < 0) {
        // 没有抽奖机会，把缓存数量还原为0
        $now_num = $redis -> set($rkey_imsi_lottery_num, 0);
        return false;
    }
    // 写回库里
    $where = array(
        'imsi' => $imsi
    );
    $data = array(
        'lottery_num' => array('exp', '`lottery_num`-1'),
        'up_tm' => $now,
        '__user_table' => 'superbowl_lottery'
    );
    $model -> update($where,$data,'lottery/lottery');
    return true;
}
/*
 * @desc设置抽奖次数
 */
function setLotteryNum($now_num) {
    global $imsi, $model, $redis, $rkey_imsi_lottery_num;

    $now = time();
    //更新缓存
    $now_num = (int)$now_num;
    $redis->set($rkey_imsi_lottery_num, $now_num);
    //更新数据库
    $where = array(
        'imsi' => $imsi,
    );
    $data = array(
        'lottery_num' => $now_num,
        'up_tm' => $now,
        '__user_table' => 'superbowl_lottery',
    );
    $ret = $model->update($where, $data, 'lottery/lottery');
    if (!$ret) {
        $data = array(
            'imsi' => $imsi,
            'lottery_num' => $now_num,
            'up_tm' => $now,
            '__user_table' => 'superbowl_lottery',
        );
        $ret = $model->insert($data, 'lottery/lottery');
    }
    return $ret;
}

/*
 * @desc 获取page_result信息
 */
function get_page_result(){
    global  $model,$active_id,$redis;
    //$redis -> delete("super_bowl_active_{$active_id}_pageresult");
    $page_result = $redis->get("super_bowl_active_{$active_id}_pageresult");
    if(!$page_result){
        $activity_option = array(
            'where' => array(
                'id' => $active_id
            ),
            'cache_time' => 300,
            'table' => 'sj_activity'
        );
        $activity_result = $model -> findOne($activity_option);
        //echo $model->getSql();
        //var_dump($activity_result);
        $page_option = array(
            'where' => array(
                'ap_id' => $activity_result['activity_page_id']
            ),
            'cache_time' => 300,
            'table' => 'sj_activity_page'
        );
        $page_result = $model -> findOne($page_option);
        $redis->set("super_bowl_active_{$active_id}_pageresult",$page_result,86400*15);
    }
    return $page_result;
   // var_dump($page_result);
}
/*
 * @desc 获取软件分类信息（获奖或非获奖id）
 */
function award_soft_category(){
    global  $model,$active_id,$redis;
    $soft_category = $redis->get("super_bowl_active_{$active_id}_softcategory");
    if(!$soft_category){
        $option = array(
            'where' => array(
                'id' => $active_id
            ),
            'cache_time' => 300,
            'field' => 'activity_page_id',
            'table' => 'sj_activity'
        );
        $activity_page_id = $model -> findOne($option);
        $category_option = array(
            'where' => array(
                'active_id' => $activity_page_id['activity_page_id'],
                'status'=>1
            ),
            'cache_time' => 300,
            'field' => 'id',
            'order'=>'rank',
            'table' => 'sj_actives_category'
        );
        $soft_category =  $model -> findAll($category_option);
        $redis->set("super_bowl_active_{$active_id}_softcategory",$soft_category,86400*15);
    }

    return $soft_category;
}

/*
 * @desc 投票入库
 */
function saveVoteApp($softid,$softname,$package,$category,$icon){
    global  $model,$aid,$redis,$imsi,$vote_app_num;
    $time = strtotime(date("Y-m-d"));

    $sql = "insert into `superbowl_uservote` (`softid`, `icon`,`softname`, `package`,`category`,`imsi`,`time`) values ";
    foreach($package as $k=>$v){
        $where = array('package'=>$v);
        $u_data = array(
            'vote_num' => array('exp', '`vote_num`+1'),
            '__user_table' => 'superbowl_ranklist'
        );
        $model -> update($where,$u_data,'lottery/lottery');
        //更新缓存投票数

        $vote_res = $redis->gethash("{$vote_app_num}","{$v}");
        if($vote_res){
            $vote_num = (int)$vote_res['num'];
            //permanentlog('activity_'.$aid.'.log',$vote_num);
            $vote_num++;
			$vote_res['num'] = $vote_num;
            $r_data = array(
              $v =>$vote_res
            );
            $redis -> sethash("{$vote_app_num}",$r_data,86400*30);

        }else{
            $r_data = array(
                $v =>array('num'=>1)
            );
            $redis->sethash("{$vote_app_num}",$r_data,86400*30);
        }


        if(count($package)==$k+1){
            $sql .="('{$softid[$k]}','{$icon[$k]}','{$softname[$k]}','{$v}','{$category[$k]}','{$imsi}','{$time}')";
        }else{
            $sql .="('{$softid[$k]}','{$icon[$k]}','{$softname[$k]}','{$v}','{$category[$k]}','{$imsi}','{$time}'),";
        }

    }
    $res = $model->query($sql, 'lottery/lottery');

}

/*
 * @desc 获取用户当天投票软件
 */
function getUserVoteApp(){
    global  $model,$active_id,$redis,$imsi;
    $today = date("Y-m-d");
    //$redis->delete("superbowl_{$active_id}_{$imsi}_{$today}");
    $vote_app = $redis->get("superbowl_{$active_id}_{$imsi}_{$today}");
    if(!$vote_app){
        $option = array(
            'where' => array(
                'imsi' => $imsi,
                'time' => strtotime($today)
            ),
            'field' => 'softname,icon',
            'table' => 'superbowl_uservote',
            'limit' => 4
        );
        $vote_app = $model -> findAll($option,'lottery/lottery');
        //echo $model->getSql();
        $redis->set("superbowl_{$active_id}_{$imsi}_{$today}",$vote_app,86400);
    }
    return $vote_app;
}

//活动相关信息（活动时间、活动奖品等）
function getActiveConfig(){
    global  $model;
    $activity_option = array(
        'where' => array(
            'config_type' => 'SUPERBOWL_LOTTERY_AWARD',
            'status' => 1
        ),
        'cache_time' => 600,
        'table' => 'pu_config'
    );
    $result = $model -> findOne($activity_option);
    //echo $model->getSql();
    return $result;
}
