<?php
//得到抽奖次数
function get_uid_draw_num($redis,$model,$uid){
    $uid_num_arr = array();
    $draw_num = 0;
    $uid_num_arr = $redis->get('rebate_uid_money_data'.$uid);
    if(!$uid_num_arr){
        $option = array(
                'where' => array(
                        'uid' => $uid,
                ),
                'table' => 'rebate_draw_money',
        );
        $uid_money = $model->findAll($option,'lottery/lottery');
        if($uid_money){
            foreach($uid_money as $v){
                $uid_num_arr[$v['money_date']] = $v['draw_data_num'];
            }
            $redis -> set("rebate_uid_money_data".$uid,$uid_num_arr,86400*4);
        }
    }
    $uid_all_num = array_sum($uid_num_arr);
    
    $draw_num= $redis -> get("rebate_draw_num_uid_".$uid);
    if($draw_num===false){
        $option = array(
                'where' => array(
                        'uid' => $uid,
                ),
                'field' => 'count(*) as draw_num',
                'table' => 'rebate_draw_award',
        );
        $draw_num_arr = $model->findOne($option,'lottery/lottery');
        $draw_num = intval($draw_num_arr['draw_num']);
        $redis -> set("rebate_draw_num_uid_".$uid,$draw_num,86400*4);
    }
     if($uid_all_num>0 && $uid_all_num >= $draw_num){
         return $uid_all_num-$draw_num;
     }else{
         return 0;
     }     
}
//得到中奖最后10条
function getUserAward($redis,$model,$limit =10){
    $data = array();
    $data = $redis -> get("rebate_draw_user_award_limit".$limit);
    if(!$data){
        $option = array(
                'where' => array(
                        'status' => 1,
                        'pid'=>array('exp','<6'),
                ),
                'table' => 'rebate_draw_award',
                'limit' => 10,
                'order' => 'id desc'
        );
        $data = $model->findAll($option,'lottery/lottery');
        if($data){
            $redis -> set("rebate_draw_user_award_limit".$limit,$data,60*5);
        }
    }
    if($data){        
        foreach($data as &$val){
        	$val['username'] = str_replace(mb_substr($val['username'],2,4,'utf-8'),'XXXX',$val['username']);
        }
    }
    return $data;
}

//更新用户收货地址
function update_user_info($redis,$uid,$data){
    $model = new GoModel();
    if(get_user_info($uid)){
        $where = array('uid' => $uid);
        $data_update = array(
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'address' => $data['address'],
                '__user_table' => 'rebate_draw_userinfo'
        );
        $res = $model -> update($where,$data_update,'lottery/lottery');
    }else{
        $option = array(
                'uid' => $uid,
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'address' => $data['address'],
                'create_tm' => time(),
                '__user_table' => 'rebate_draw_userinfo'
        );
        $res = $model->insert($option,'lottery/lottery');
    }
    //日志
    $log_data = array(
            'uid' => $uid,
            'users' =>$_SESSION['USER_NAME'],
            'imsi' => $_SESSION['USER_IMSI'],
            'device_id' => $_SESSION['DEVICEID'],
            'activity_id' => $data['activity_id'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'sid' => $data['sid'],
            'time' => time(),
            'key' => 'update_userinfo'
    );
    permanentlog('activity_'.$data['activity_id'].'.log', json_encode($log_data));
    $redis->set('rebate_draw_userinfo'.$uid,$data,86400*10);
}

//取得用户信息
function get_user_info($uid){
    $model = new GoModel();
    $option = array('where' => array('uid' => $uid ),
            'table' => 'rebate_draw_userinfo',
    );
    return $model->findOne($option,'lottery/lottery');
}

//加密 $str = $uid.$_SESSION['USER_IMSI'].$pid
function str_encode($str){
	$my_str = 'anzhi_05231815';
	return md5($my_str.$str);
}

//解密 
function str_decode($o_str,$str){
    $my_str = 'anzhi_05231815';
    if(md5($my_str.$o_str)== $str){
        return true;
    }else{
        return false;
    }
}

//取得奖品信息
function get_prize_info($pid){
    $model = new GoModel();
    $option = array('where' => array('id' => $pid ),
            'table' => 'rebate_draw_prize',
    );
    return $model->findOne($option,'lottery/lottery');
}
//取得用户所有奖品
function get_user_all_award($uid){
    if(!$uid) return  array();
    $model = new GoModel();

    $option2 = array('where' => array('uid' => $uid,'status'=>1),
            		'table' => 'rebate_draw_award',
            		'order' => 'id desc'
    				);
    $prize =  $model->findAll($option2,'lottery/lottery');
    if($prize){
    	foreach($prize as &$val){
    		if($val['pid'] >4){
    			$val['prizename']=json_decode($val['prizename'],true);
    		}
    	}
    }
    return $prize;   



    
}