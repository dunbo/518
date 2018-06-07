<?php
define(GETUI,'/../ORG/getuiApi');

require_once(dirname(__FILE__) . GETUI . '/IGt.Push.php');
require_once(dirname(__FILE__) . GETUI . '/igetui/template/IGt.TransmissionTemplate.php');
require_once(dirname(__FILE__) . GETUI . '/igetui/template/notify/IGt.Notify.php');
require_once(dirname(__FILE__) . GETUI . '/igetui/IGt.Req.php');
class GetuiModel extends Model {
    private $AppId = 'RJVUl8Vsk79kjlI4AZfZU3';
    private $AppKey = 'ckqPohqUkcADizx4ecNyn4';
    private $MasterSecret = 'ici5bmq7FB8QK1Ovwem991';
    private $Host = 'http://sdk.open.api.igexin.com/apiex.htm';
    private $Type = array(
        1 => NotifyInfo_Type::_intent,
        2 => NotifyInfo_Type::_url,
        3 => NotifyInfo_Type::_payload
    );
    const pattern = '/^intent:#Intent;.*;end$/';
    public function chk_validate($data){
        if(!$data['id']){
            return json_encode(array('code'=>0,'msg'=>'缺少参数ID'));
        }
        if(!$data['title']){
            return json_encode(array('code'=>0,'msg'=>'标题为空'));
        }
        if(!$data['content']){
            return json_encode(array('code'=>0,'msg'=>'内容为空'));
        }
        if(!preg_match(self::pattern,$data['intent'])){
            return json_encode(array('code'=>0,'msg'=>'intent格式错误，需以"intent:#Intent;"开始并已";end"结束'));
        }
        if(!empty($data['cid'])){
            $cid = explode(',',$data['cid']);
            if(count($cid)==1){
                return $this->pushMessageToSingle($data);
            }else{
                return $this->pushMessageToList($data,$cid);
            }
        }else{
            return $this->pushMessageNoCid($data);
        }
    }

    public function count_time($data){
        return $data['end_tm'] - $data['start_tm'];
    }

    public function pushMessageNoCid($data){
        $igt = new IGeTui($this->Host,$this->AppKey,$this->MasterSecret);
        $template = $this->IGtTransmission($data);
        //个推信息体
        //基于应用消息体
        $message = new IGtAppMessage();
        $message->set_isOffline(true);
        $time = $this->count_time($data);
        $message->set_offlineExpireTime($time);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);

        $appIdList=array($this->AppId);
        $phoneTypeList=array('ANDROID');

        $cdt = new AppConditions();
        $cdt->addCondition(AppConditions::PHONE_TYPE, $phoneTypeList);
        $message->set_appIdList($appIdList);
        $message->set_conditions($cdt);
        $rep = $igt->pushMessageToApp($message);
        return $this->repProcess($rep,$data);
    }

    //对指定列表用户推送消息
    public function pushMessageToList($data,$cid){
        putenv("gexin_pushList_needDetails=true");
        $igt = new IGeTui($this->Host,$this->AppKey,$this->MasterSecret);
        //$igt = new IGeTui('',APPKEY,MASTERSECRET);//此方式可通过获取服务端地址列表判断最快域名后进行消息推送，每10分钟检查一次最快域名
        //消息模版：
        // LinkTemplate:通知打开链接功能模板
        $template = $this->IGtTransmission($data);


        //定义"ListMessage"信息体
        $message = new IGtListMessage();
        $message->set_isOffline(true);//是否离线
        $time = $this->count_time($data);
        $message->set_offlineExpireTime($time);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(1);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $contentId = $igt->getContentId($message);
        $targetList = array();
        foreach($cid as $v){
            $target = new IGtTarget();
            $target->set_appId($this->AppId);
            $target->set_clientId($v);
            $targetList[] = $target;
        }
        $rep = $igt->pushMessageToList($contentId, $targetList);
        $res = $this->repProcess($rep,$data);
        return $res;
    }

    //单个推送用户
    public function pushMessageToSingle($data){
        $igt = new IGeTui($this->Host,$this->AppKey,$this->MasterSecret);
        $template = $this->IGtTransmission($data);

        //个推信息体
        $message = new IGtSingleMessage();

        $message->set_isOffline(true);//是否离线
        $time = $this->count_time($data);
        $message->set_offlineExpireTime($time);//离线时间
        $message->set_data($template);//设置推送消息类型
//	$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new IGtTarget();
        $target->set_appId($this->AppId);
        $target->set_clientId($data['cid']);
//    $target->set_alias(Alias);


        try {
            $rep = $igt->pushMessageToSingle($message, $target);
        }catch(RequestException $e){
            $requstId =$e->getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target,$requstId);
        }
        $info = $this->repProcess($rep,$data);
        return $info;
    }


    public function IGtTransmission($data){
        $trans =  new IGtTransmissionTemplate();
        $trans->set_appId($this->AppId);//应用appid
        $trans->set_appkey($this->AppKey);//应用appkey
        $trans->set_transmissionType(2);//透传消息类型
        $trans->set_transmissionContent($data['content']);//透传内容
        //$trans->set_duration(date('Y-m-d H:i:s', $data['start_tm']), date('Y-m-d H:i:s', $data['end_tm'])); //设置ANDROID客户端在此时间区间内展示消息
        //第三方厂商推送透传消息带通知处理
        $notify = new IGtNotify();
        $notify -> set_title($data['title']);
        $notify -> set_content($data['content']);
        $notify -> set_intent($data['intent']);
        $notify -> set_type($this->Type[$data['type']]);
        $notify -> set_url($data['url']);
        $notify -> set_payload($data['payload']);
        $trans -> set3rdNotifyInfo($notify);
        //var_dump($trans);

        return $trans;
    }

    public function save_trans_res($data){
        $where = array('id'=>$data['id']);
        $u_data = array('send_status'=>$data['send_status'],'contentId'=>$data['contentId'],'update_tm'=>time());
        $this->table('sj_push_api')->where($where)->save($u_data);
    }

    public function pushMessageToApp($data){
        $info = $this->chk_validate($data);
        return $info;

    }

    public function repProcess($rep,$data){
		if ($rep['taskId']) {
			$rep['contentId'] = $rep['taskId'];
		}
        if($rep['result']=='ok'){
            //成功
            $data['send_status'] = 1;
            $data['contentId'] = $rep['contentId'];
            $this->save_trans_res($data);
            return json_encode(array('code'=>200,'msg'=>'成功'));
        }else{
            //失败
            $data['send_status'] = 2;
            $this->save_trans_res($data);
            return json_encode(array('code'=>0,'msg'=>'发送失败'));
        }
    }

    function stoptask($id){
        $where = array('id'=>$id);
        $push = $this->table('sj_push_api')->where($where)->find();
        if($push['contentId']) {
            $igt = new IGeTui($this->Host, $this->AppKey, $this->MasterSecret);
            $rep = $igt->stop($push['contentId']);
            if ($rep) {
                $this->table('sj_push_api')->where($where)->save(array('send_status' => 3, 'update_tm' => time()));
            }
        }
        return $rep;
    }

    function push_relate($id,$data){
        //暂只支持push
        if($data['push_type']!=1||$data['is_getui']!=1) return false;
        if(!$id||!$data['info_title']||!$data['info_content']){
            return false;
        }
        $where = array('push_id'=>$id,'status'=>1);
        $push = $this->table('sj_push_api')->where($where)->find();
        $now = time();
        $save_data = array(
            'title' => $data['info_title'],
            'content' => $data['info_content'],
            'url' => isset($data['page_link'])?$data['page_link']:'',
            'payload' => '',
            'admin_id' => $_SESSION['admin']['admin_id'],
            'update_tm' => $now,
            'fromdate' => isset($data['daily_start_tm'])?$data['daily_start_tm']:'',
            'todate' => isset($data['daily_end_tm'])?$data['daily_end_tm']:'',
            'start_tm' => isset($data['start_tm'])?$data['start_tm']:'',
            'end_tm' => isset($data['end_tm'])?$data['end_tm']:'',
            'intent' => "intent:#Intent;launchFlags=0x10000000;package=cn.goapk.market;component=cn.goapk.market/cn.goapk.market.AnZhiBrandPushInvokeActivity;i.PUSH_ID={$id};end"
        );
        if($push){
            //编辑
            if($push['send_status']==0){
                $res = $this->table('sj_push_api')->where($where)->save($save_data);
            }else if($push['send_status']==1&&$push['end_tm']>$data['end_tm']){
                $save_data = array(
                    'update_tm' => $now,
                    'end_tm' => isset($data['end_tm'])?$data['end_tm']:''
                );
                $res = $this->table('sj_push_api')->where($where)->save($save_data);
            }
        }else{
            //新增
            $save_data['create_tm'] = $now;
            $save_data['push_id'] = $id;
            $save_data['type'] = 1;
            $res = $this->table('sj_push_api')->add($save_data);
        }
        return $res;
    }
}
?>
