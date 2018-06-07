<?php
class AuthAction extends CommonAction {
     public function partner_auths_list() {
         $partnerObj = D("Partner.Partner");
         import("@.ORG.Page");
         $count = $partnerObj  ->count();
         $Page=new Page($count,15);
         $partnerList = $partnerObj  -> limit($Page->firstRow.','.$Page->listRows) ->select();
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
         $this -> assign("userList" , $partnerList);
         $this -> display('partner_auths_list');
     }
     public function doAddPartner() {
         $username =   $_GET['username'];
         $passwd     =   $_GET['passwd'];
         $partnerObj = D("Partner.Partner");
         $map['username'] = $username;
         $map['passwd'] = $passwd;
         $map['status'] = 1;
         $userList = $partnerObj -> where($map) -> select();
         if(!empty($userList)){
           $this -> error("该账号已存在！请另设账号。");
         }else{
           $map['create_time'] = time();
           $affect = $partnerObj -> add($map);
           if($affect > 0){
				$this -> writelog("合作方权限列表：已添加id为{$affect}的合作方用户", 'partner_user', $affect,__ACTION__ ,'','add');
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Auth/partner_auths_list');
                $this->success("添加成功！");
           }
         }
     }
     public function EditPartnerAuthByCid() {
        $uid  = $_GET['uid'];
        $userObj  = D("Partner.Partner");
        $channelObj = M("channel");
        $cList  = $channelObj -> where("status = 1") -> select();
        $userinfo = $userObj -> where(array('uid' => $uid)) -> select();
        $channelList = array();
        foreach($cList as $info){
           $channelList[$info['cid']]= $info;
        }
        $this -> assign('username',$userinfo[0]['username']);
        $usercid = explode(',',$userinfo[0]['cid_collect']);
        $this -> assign('cids',$usercid);
        $this -> assign('uid',$uid);
        $this -> assign('channelList',$channelList);
		$this -> display('EditPartnerAuthByCid');
     }
     public function doEditByCid() {
         $uid= $_POST['uid'];
         $cid = $_POST['cid'];
         if(empty($cid)){
            $this -> error('请选择渠道号！');
         }
         $partnerObj = D("Partner.Partner");
         $exists = $partnerObj -> where(array('uid' => $uid)) -> select();
         if(!empty($exists)){
              $data['cid_collect'] = implode(',',$cid);
			  $log_result = $this->logcheck(array('uid'=>$uid),'partner_user',$data,$partnerObj);
              $affect = $partnerObj -> where(array('uid' => $uid))->save($data);
              if($affect > 0){
				$this -> writelog("合作方权限列表：已编辑合作方权限列表uid为{$uid}的渠道".$log_result, 'partner_user', $uid,__ACTION__ ,'','edit');
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Auth/partner_auths_list');
                $this->success("编辑渠道成功！");
              }else{
                $this -> error("请修改有效信息！");
              }
         }
     }
     public function DelUser() {
           $uid = $_GET['uid'];
           $partnerObj = D("Partner.Partner");
           $data['status'] = '0';
           $affect = $partnerObj -> where(array('uid' => $uid)) -> save($data);
           if($affect > 0){
				$this -> writelog("合作方权限列表：已删除id为{$uid}的合作方用户", 'partner_user', $uid,__ACTION__ ,'','del');
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Auth/partner_auths_list');
                $this->success("删除用户成功！");
           }else{
                $this -> error("删除失败！");
           }
     }
     public function backAuth() {
           $uid = $_GET['uid'];
           $partnerObj = D("Partner.Partner");
           $data['status'] = '1';
           $affect = $partnerObj -> where(array('uid' => $uid)) -> save($data);
           if($affect > 0){
				$this -> writelog("合作方权限列表：已恢复id为{$uid}的合作方用户", 'partner_user', $uid,__ACTION__ ,'','edit');
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Auth/partner_auths_list');
                $this->success("恢复用户成功！");
           }else{
                $this -> error("恢复失败！");
           }
     }
}
?>