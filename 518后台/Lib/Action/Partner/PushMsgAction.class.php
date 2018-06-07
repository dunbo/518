<?php
class PushMsgAction extends CommonAction {
    function msgList() {
        $msgObj = D("Partner.Pm");
        $channelObj = M("channel");
        import("@.ORG.Page");
        $count = $msgObj -> where("status = 1") -> count();
        $Page=new Page($count,15);
        $msgList = $msgObj -> where("status = 1") ->  limit($Page->firstRow.','.$Page->listRows) -> select();
        $channelList = $channelObj -> where("status = 1") -> getField('cid,chname');
        foreach($msgList as $idx => $info){
             $msgList[$idx]['chname'] =$channelList[$info['cid']];
        }
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this -> assign('msgList',$msgList);
        $this -> display('msgList');
    }
    function addMsg() {
        $msgObj = D("Partner.Pm");
        $channelObj = M("channel");
        $chl = $_GET['chl'];
        $msg = $_GET['message'];
        if(empty($chl) || empty($msg)){
                    $this -> error("请输入有效信息！");
        }
        $create_time = time();
        $exists = $channelObj -> where(array('chl' => $chl)) -> select();
        $data = array();
        if($exists){
              $msgExists = $msgObj -> where("cid = ".$exists[0]['cid']." and status=1") -> select();
              if(!empty($msgExists)){
                    $this -> error("该渠道的推送公告已存在！");
              }else{
                  $data['cid'] = $exists[0]['cid'];
                  $data['message'] = $msg;
                  $data['create_time'] = $create_time;
                  $data['status'] = 1;
                  $affect = $msgObj -> add($data);
                  if($affect > 0){
					 $this -> writelog("公告信息列表：已添加id为{$affect}的公共信息", 'partner_cid_message', $affect,__ACTION__ ,'','add');
                     $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/PushMsg/msgList');
                     $this->success("添加成功！");
                  }
              }
        }else{
                    $this -> error("该渠道不存在！");
        }
    }
    function editMsgForm() {
        $msgObj = D("Partner.Pm");
        $channelObj = M("channel");
        $id = $_GET['id'];
		
        $result = $msgObj -> where(array('id' => $id)) -> select();
        if(!empty($result)){
             $channelInfo = $channelObj -> where("cid = ".$result[0]['cid']) -> select();
             $this -> assign("chname",$channelInfo[0]['chname']);
             $this -> assign("msginfo",$result[0]);
        }else{
           $this -> error("信息不存在");
        }
        $this -> display("editMsgForm");
    }
    function doEditMsg() {
       $msgObj = D("Partner.Pm");
       $id= $_POST['id'];
       $msg = $_POST['message'];
       if(!empty($msg)){
         $data['message'] = $msg;
       }else{
           $this -> error("信息不能为空！");
       }
	   $log_result = $this->logcheck(array('id'=>$id),'partner_cid_message',$data,$msgObj);
	
       $affect = $msgObj -> where(array('id' => $id)) -> save($data);
       if($affect > 0){
			 $this -> writelog("公告信息列表：已编辑公共信息列表id为{$id}".$log_result, 'partner_cid_message', $id,__ACTION__ ,'','edit');
             $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/PushMsg/msgList');
             $this->success("编辑成功！");
       }else{
             $this -> error("请编辑有效信息！");
       }
    }
    function delMsg() {
        $msgObj = D("Partner.Pm");
        $id = $_GET['id'];
        $data['status'] =0;
        $affect = $msgObj -> where(array('id' => $id)) -> save($data);
        if($affect > 0){
			 $this -> writelog("公告信息列表：已删除id为{$id}的公告信息", 'partner_cid_message', $id,__ACTION__ ,'','del');
             $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/PushMsg/msgList');
             $this->success("删除成功！");
        }
    }
}

?>