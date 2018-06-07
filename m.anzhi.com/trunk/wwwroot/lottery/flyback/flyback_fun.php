<?php
/**
 * --------------------------------------------------------------------------------------------------
 * Date: 2015-10-28
 * 11月回归安智100%有礼 派现金红包活动
 * -------------------------------------------------------------------------------------------------- 
 */
 
//获取中奖信息
function getMyAward($redis,$uid){
	$model = new GoModel();
	//$redis -> delete("flyback_user_award_".$uid);
	$data = $redis -> get("flyback_user_award_".$uid);
	if(!$data){
		$option = array(
                'where' => array(
                        'status' => 1,
						'uid' => $uid
                ),
                'table' => 'flyback_winer'
        );
        $data = $model->findOne($option,'lottery/lottery');
		if($data){
			if($data['pid']>7){
				$data['prizename'] = json_decode($data['prizename'],true);
			}
			
			$data['create_tm'] = date("Y-m-d",$data['create_tm']);
            $redis -> set("flyback_user_award_".$uid,$data,86400*10);
        }
	}
	return $data;
}
//获取中奖最后10条
function getUserAward($redis,$model,$limit =10){
    $data = array();
	//$redis -> delete("flyback_user_award_limit".$limit);
    $data = $redis -> get("flyback_user_award_limit".$limit);
    if(!$data){
        $option = array(
                'where' => array(
                        'status' => 1,
						'pid'=>array("exp"," <= 7")
                ),
                'table' => 'flyback_winer',
                'limit' => 10,
                'order' => 'id desc'
        );
        $data = $model->findAll($option,'lottery/lottery');
        if($data){
            $redis -> set("flyback_user_award_limit".$limit,$data,60*5);
        }
    }
    if($data){        
        foreach($data as &$val){
        	$val['username'] = str_replace(mb_substr($val['username'],2,4,'utf-8'),'XXXX',$val['username']);
        }
    }
    return $data;
}

//更新用户联系信息
function update_user_info($redis,$uid,$data){
    $model = new GoModel();
    if(get_user_info($redis,$uid)){
        $where = array('uid' => $uid);
        $data_update = array(
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'address' => $data['address'],
                '__user_table' => 'flyback_winer_info'
        );
        $res = $model -> update($where,$data_update,'lottery/lottery');
    }else{
        $option = array(
                'uid' => $uid,
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'address' => $data['address'],
                'create_tm' => time(),
                '__user_table' => 'flyback_winer_info'
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
    $redis->set('flyback_winer_info'.$uid,$data,86400*10);
}

//获取用户联系信息
function get_user_info($redis,$uid){
    $model = new GoModel();
	//$redis -> delete("flyback_winer_info".$uid);
	$user_info = $redis -> get("flyback_winer_info".$uid);
	if(!$user_info){
		$option = array('where' => array('uid' => $uid ),
            'table' => 'flyback_winer_info',
		);
		$user_info = $model->findOne($option,'lottery/lottery');
		//echo $model->getSql();
		$redis -> set("flyback_winer_info".$uid,$user_info,86400*10);
	}
    return $user_info;
}

//获取用户是否能抽奖
function get_user_draw($redis,$model,$uid){
	//$redis -> delete("flyback_num_uid_".$uid);
	$draw_num= $redis -> get("flyback_num_uid_".$uid);
	//$draw_num = false;
	if(!$draw_num){
		$option = array('where' => array('uid' => $uid ),
				'table' => 'flyback_user',
				'field' => 'draw'
		);
		$res = $model->findOne($option,'lottery/lottery');
		//echo $model->getSql();
		if($res){
			if($res['draw']==1){
				//不可抽奖
				$redis -> set("flyback_num_uid_".$uid,0,86400*4);
				return 0;
			}else{
				//可抽奖
				$redis -> set("flyback_num_uid_".$uid,1,86400*4);
				return 1;
			}	
		}else{
			//无资格
			$redis -> set("flyback_num_uid_".$uid,-1,86400*4);
			return -1;
		}
	}else{
		return $draw_num;
	}
    
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