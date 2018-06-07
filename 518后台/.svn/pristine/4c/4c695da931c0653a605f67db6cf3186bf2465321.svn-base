<?php

// if (!defined('IMG_HOST'))
//     define('IMG_HOST', '/data/att/m.goapk.com');
// if (!defined('IMG_URL'))
//     define('IMG_URL', IMGATT_HOST);

class UserRegisterTaskContentAction extends CommonAction {
    const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";


    function user_register_task_list() {
        $model = D('Sj.UserRegisterTaskContent');
        $where = array();
        if($_GET['s_package'] && $s_package = trim($_GET['s_package'])){
            $where['package'] = array('eq', $s_package);
            $this->assign("package", $s_package);
        }
        if($_GET['s_softname'] && $s_softname = trim($_GET['s_softname'])){
            $where['softname'] = array('like', "%{$s_softname}%");
            $this->assign("softname", $s_softname);
        }
        if($_GET['s_uid'] && $s_uid = trim($_GET['s_uid'])){
            $where['uid'] = array('like', "%{$s_uid}%");
            $this->assign("uid", $s_uid);
        }
        if($_GET['s_nickname'] && $s_nickname = trim($_GET['s_nickname'])){
            $where['nickname'] = array('like', "%{$s_nickname}%");
            $this->assign("nickname", $s_nickname);
        }
        if($_GET['begintime'] && $begintime = strtotime(trim($_GET['begintime']))){
            $where["create_tm"] = array('egt', $begintime);
            $this->assign("begintime", $_GET['begintime']);
        }
        if($_GET['endtime'] && $endtime = strtotime(trim($_GET['endtime']))){
            $where["create_tm"] = array('elt', $endtime);
            $this->assign("endtime", $_GET['endtime']);
        }
        if($begintime && $endtime){
            $where["create_tm"] = array('exp', ">=$begintime and create_tm<=$endtime");
        }
        $where['status'] = 1;
        $passed=$_GET['passed']?$_GET['passed']:1;
        $where['passed'] = $passed; 
        $this->assign('passed', $passed);

        $count = $model->where($where)->count();
        import("@.ORG.Page2");
        $pg=$_GET['p']?$_GET['p']:1;
        $this->assign('pg', $pg);
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $this->assign('total', $count);

        $list = $model->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('abort_tm asc,create_tm asc')->select();
        //批量根据任务id走java接口取数据
        $taskData=$this->getTaskData($list);
        $ids="";
        foreach($list as $k=>$v){
          $ids.=$v['id'].",";
          //走接口取任务数据
          $list[$k]['start_tm']=$taskData[$v['task_id']]["startTime"]?$taskData[$v['task_id']]["startTime"]:date('Y-m-d H:i:s',$v['start_tm']);
          $list[$k]['end_tm']=$taskData[$v['task_id']]["endTime"]?$taskData[$v['task_id']]["endTime"]:date('Y-m-d H:i:s',$v['end_tm']);
          $list[$k]['abort_tm']=$taskData[$v['task_id']]["abort_tm"]?$taskData[$v['task_id']]["abort_tm"]:date('Y-m-d H:i:s',$v['abort_tm']);
          $list[$k]['task_intro']=$taskData[$v['task_id']]["taskDes"]?$taskData[$v['task_id']]["taskDes"]:$v['task_intro'];


          $list[$k]['create_tm']=date('Y-m-d H:i:s',$v['create_tm']);
          $list[$k]['update_tm']=$v['update_tm']?date('Y-m-d H:i:s',$v['update_tm']):'';
          $list[$k]['upload_thumb']=json_decode($v['upload_thumb'],true);

        }
         if ($_GET['export'] == 1 && $_GET['ids']) {
            $where_two['id']=array('in',explode(',', $_GET['ids']));
            $list_check = $model->where($where_two)->select();
            $taskDataCheck=$this->getTaskData($list_check);
            foreach($list_check as $k=>$v){
                    
                    $user_info=$v['uid']."\r\n".$v['nickname'];
                    $list_check[$k]['user_info']="\"$user_info\"";
                    $soft_info=$v['package']."\r\n".$v['softname'];
                    $list_check[$k]['soft_info']="\"$soft_info\"";

                    //走接口取任务数据
                    $list_check[$k]['start_tm']=$taskDataCheck[$v['task_id']]["startTime"]?$taskDataCheck[$v['task_id']]["startTime"]:date('Y-m-d H:i:s',$v['start_tm']);
                    $list_check[$k]['end_tm']=$taskDataCheck[$v['task_id']]["endTime"]?$taskDataCheck[$v['task_id']]["endTime"]:date('Y-m-d H:i:s',$v['end_tm']);
                    $list_check[$k]['abort_tm']=$taskDataCheck[$v['task_id']]["abort_tm"]?$taskDataCheck[$v['task_id']]["abort_tm"]:date('Y-m-d H:i:s',$v['abort_tm']);
                    $list_check[$k]['task_intro']=$taskDataCheck[$v['task_id']]["taskDes"]?$taskDataCheck[$v['task_id']]["taskDes"]:$v['task_intro'];

                    $list_check[$k]['create_tm']=date("Y-m-d H:i:s",$v["create_tm"]);
                    $list_check[$k]['update_tm']=$v["update_tm"]?date("Y-m-d H:i:s",$v["update_tm"]):'';

                    // $task_info=$v['task_intro']."\r\n".$v['task_pic'];
                    // $list_check[$k]['task_info']="\"$task_info\"";

                    $upload_thumb='';
                    foreach(json_decode($v['upload_thumb'],true) as $v){
                      $upload_thumb.=GAMEINFO_ATTACHMENT_HOST.$v."\r\n";
                      // $a=GAMEINFO_ATTACHMENT_HOST.$v;
                      // $upload_thumb.="<a href='{$a}'>".GAMEINFO_ATTACHMENT_HOST.$v."</a>"."\r\n";
                    }
                    $list_check[$k]['upload_thumb']="\"$upload_thumb\"";
                    
                    
            }
            // echo "<pre>";var_dump($list_check);die;
            $this->export_deposit($list_check,"注册任务内容审核管理".date('Y-m-d').".csv", 'taskcontent');
        }
        $this->assign('list', $list);
        $this->assign('list2', $list);
        $this->assign('ids_str', $ids);
        // echo "<pre>";var_dump($list);
        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);

        $this->assign('page', $Page->show());
        $this->display();
    }
    //审核状态
    public function change_status() {
      if($_GET['rejected']==1){
          $this->assign('list_id', $_GET['id']);
          $this->assign('passed', $_GET['passed']);
          $this->display();
      }else{
          if(file_exists("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log")){
              unlink("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log");
          }
          $passed = $_GET['passed'];
          $id_last = $_GET['id_last'];
          $model = D('Sj.UserRegisterTaskContent');
          if($_GET['biaoshi']==1){
            $ids = $_GET['ids'];
            $ids=explode(',', $ids);
            //此处即可得出 是否有操作失败的用户
            $ids_fail=array();
            // array_diff($ids, $ids_fail)
            $ids_suess=array_diff($ids, $ids_fail);
            $where = array(
              'id' => array('in',$ids_suess)
            );
          }else{
            $id = $_GET['id'];
            $where = array(
              'id' => $id
            );
          }
          $data = array(
              "passed" => $passed,
              'update_tm' => time()
          );
          
          $old_info = $model->where($where)->select();
          
          //发送push
          if($passed==2){
             $data_push=array();
             foreach($old_info as $v){
                $v['taskTypeText']=$v['taskTypeText']?$v['taskTypeText']:'注册';
                $data_push[$v['id']]=array('TASKID'=>$v['task_id'],'MAC'=>$v['mac'],'IMEI'=>$v['imei'],'UID'=>$v['uid'],"CONTENT"=>"您完成[{$v['taskTypeText']}][{$v['softname']}]任务，审核已通过");
             }
              foreach($old_info as $v){
                  $submit_task_data=json_decode($v['submit_task_data'],true);
                  $submit_result = $this->uc_submitTask($submit_task_data['sid'], $submit_task_data['data'], $submit_task_data['device_arr'], $submit_task_data['header'], $submit_task_data['extra']);
                  // $submit_result['code']=200;
                  // var_dump($submit_result);
                  // var_dump($submit_result['success']);
                  //写文件到未成功
                  if(file_exists("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log")){
                    $data_array=file_get_contents("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log");
                    $data_array=json_decode($data_array,true);
                  }else{
                    $data_array=array();
                  }
                  // var_dump($data_array);
                  if(!$submit_result['success']){
                     $data_array['fail'][$v['id']]=$v['id'];
                     if($id_last){
                        $total=count($data_array['success'])+count($data_array['fail']);
                        $fail_count=count($data_array['fail']);
                        $succeed_count=count($data_array['success']);
                        $str=($passed==2)?'共审核通过':(($passed==1)?'共撤销':'共驳回');
                        $str.=$total."人，其中{$succeed_count}人成功，其中{$fail_count}人失败。";
                        echo json_encode(array('msg'=>$str,'fail_name'=>implode(',', $data_array['fail'])));
                        unlink("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log");
                        return;
                     }else{
                        file_put_contents("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log", json_encode($data_array));
                        return;
                     }
                     
                  }else{
                     $data_array['success'][$v['id']]=$v['id'];
                     file_put_contents("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log", json_encode($data_array));
                     $data_push[$v['id']]['SIGN']=$submit_result['businessObject']['completeSign'];
                  } 
              }
              //发送push
              // {"KEY":"ADD_NOTIFY","TASK_ID":"taskid","UIDS":'uid,uid,uid,uid',"CONTENT":"content"}
              // api.goapk.com
              
              
              if($_SERVER['SERVER_ADDR']=='124.243.198.97') {
                $url = "http://api.test.anzhi.com/goserv.php";//测试环境
              } else {
                $url = "http://gomarket.goapk.com/goserv.php";//线上环境
              }
              $vals = array(
                'KEY' => "ADD_NOTIFY",  
                'TYPE' => 1,
                'DATA' => $data_push,  
              );
               // var_dump($vals);
              $val = json_encode($vals);
              $re_push=httpGetInfo($url, $val,'register_content_audit.log');
              // var_dump($re_push);
          }
          // else{
          //   $data['reject_reason']='驳回提示友好文本';
          //   $data['reject_type']=3;
          // }
         if($_GET['reject_type']||$_GET['reject_reason']){
            $data['reject_reason']=$_GET['reject_reason'];
            $data['reject_type']=$_GET['reject_type'];
          }
          if($passed==2){
              $id_push=is_array($where['id'])?$where['id'][1]:array($where['id']);
              foreach($id_push as $k=>$v){
                $model->where(array('id'=>$v))->save(array('sign'=>$data_push[$v]['SIGN'])); 
              }
          }
          $res = $model->where($where)->save($data); 
          if(!empty($res)){
            $id_str=$id?$id:implode(',', array_diff($ids, $ids_fail));
            if($passed==1){
              if($old_info[0]['passed']==2){
                $this->writelog("红包配置_注册任务内容审核管理：id为{$id_str}的任务内容由通过变为审核中",'sj_user_register_taskcontent',$id_str,__ACTION__ ,'','edit');
              }else{
                $this->writelog("红包配置_注册任务内容审核管理：id为{$id_str}的任务内容由未通过变为审核中",'sj_user_register_taskcontent',$id_str,__ACTION__ ,'','edit');

              }
            }else if($passed==2){
              $this->writelog("红包配置_注册任务内容审核管理：通过了id为{$id_str}的任务内容",'sj_user_register_taskcontent',$id_str,__ACTION__ ,'','edit');
            }else{
              $this->writelog("红包配置_注册任务内容审核管理：id为{$id_str}的任务内容未通过",'sj_user_register_taskcontent',$id_str,__ACTION__ ,'','edit');
            }
            if($id_last && $passed==2){
              $data_array=file_get_contents("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log");
              $data_array=json_decode($data_array,true);

              $total=count($data_array['success'])+count($data_array['fail']);
              $fail_count=count($data_array['fail']);
              if($fail_count>0){
                $succeed_count=count($data_array['success']);
                $str=($passed==2)?'共审核通过':(($passed==1)?'共撤销':'共驳回');
                $str.=$total."人，其中{$succeed_count}人成功，其中{$fail_count}人失败。";
                echo json_encode(array('msg'=>$str,'fail_name'=>implode(',', $data_array['fail'])));
                unlink("/tmp/ucenter_task_request_id_{$_SESSION['admin']['admin_id']}.log");
              }else{
                echo 1;
                return;
              }
              // $total=count($ids);
              // $fail_count=count($ids_fail);
              // $succeed_count=$total-$fail_count;
              // $str=($passed==2)?'共通过':(($passed==1)?'共撤销':'共驳回').$total."人，其中{$succeed_count}人成功，其中{$fail_count}人失败。";
              // echo json_encode(array('msg'=>$str,'fail_name'=>implode(',', $ids_fail)));
            }else{
              echo 1;
            }
          }else{
            echo 2;
          }
      }
          
    }
    //导出
    public function export_deposit($lists, $filename,$category = "taskcontent") {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何信息');
        } else {
            if ($category == "taskcontent") {
                $str = iconv('utf-8', 'gb2312', '用户名/昵称')  . "," . iconv('utf-8', 'gb2312', '软件包名/软件名称') . "," . iconv('utf-8', 'gb2312', '任务时间'). "," . iconv('utf-8', 'gb2312', '审核截止时间'). "," . iconv('utf-8', 'gb2312', 'IMEI') . "," . iconv('utf-8', 'gb2312', 'MAC') . "," . iconv('utf-8', 'gb2312', '任务详细说明') . "," . iconv('utf-8', 'gb2312', '用户上传截图') . "," . iconv('utf-8', 'gb2312', '上传时间') . "," . iconv('utf-8', 'gb2312', '审核时间')  ."\r\n";
            }
            foreach ($lists as $key => $val) {
                if ($category == "taskcontent") {
                    $str.= iconv('utf-8', 'gb2312', $val['user_info']) . "," . iconv('utf-8', 'gb2312', $val['soft_info']). "," .iconv('utf-8', 'gb2312', $val['start_tm'].'至'.$val['end_tm']). "," . iconv('utf-8', 'gb2312', $val['end_tm']). "," . iconv('utf-8', 'gb2312', $val['imei']). "," . iconv('utf-8', 'gb2312', $val['mac']) . "," . iconv('utf-8', 'gb2312', $val['task_intro']) . "," . iconv('utf-8', 'gb2312', $val['upload_thumb']) . "," . iconv('utf-8', 'gb2312', $val['create_tm']) . "," . iconv('utf-8', 'gb2312', $val['update_tm'])  ."\r\n";
                }
            }
        }
        echo $str;
        exit;
    }
    
    function uc_submitTask($sid, $data, $device_arr=array(), $header=array(), $extra = array())
    {
      $apiname = '/api/tms/task/reSubmitTask4Reg';
      $api_info = array(
        'prefix'=>'task',
        'apiname'=> $apiname,
        'passthrough'=> true,
        'sid'=> $sid
      );
      $ucenter = C('ucenter');
      $data['serviceId'] = $ucenter['client_serviceId'];
      $data_array = array(
        'data'=> $data,
        'device'=>$device_arr,
        'header'=>$header
      );
      // $result = $this->request_task($api_info,$data_array, $extra);
      $result = request_task($api_info,$data_array, $extra);
      // echo "<pre>";var_dump($result);die;
      return $result;
    }


    public function getTaskData($list){
      //缓存相关配置待定
      $redis = new Redis();
      $task_config=C('task_redis');
      $redis->connect($task_config['host'],$task_config['port']);
      
      $task_data=array();
      foreach($list as $v){
          $key_task="v65UCENTER_SOFT_TASK_ID:";
          // var_dump($redis->get($key_task.$v['task_id']));
          $task_data[$v['task_id']]=json_decode($redis->get($key_task.$v['task_id']),true);
      }
     // var_dump($task_data['127']);die;
      return $task_data;
    }
}