<?php
/**
 * 安智网产品管理平台 信息管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.4.25
 * ----------------------------------------------------------------------------
*/
class PushmsgAction extends CommonAction {
    function msgList() {
        $msgObj = D("Sj.Pmd");
        $deviceObj = D("Sj.Device");
        $channelObj = D("Sj.Channel");
        import("@.ORG.Page");
        $count = $msgObj -> where("status = 1") -> count();
        $Page=new Page($count,15);
        $msgList = $msgObj -> where("status = 1") ->  limit($Page->firstRow.','.$Page->listRows) -> select();
        //$devicelList = $deviceObj -> where("status = 1") -> getField('did,dname');
        $channelList = $channelObj -> where("status = 1") -> getField('cid,chname');
        foreach($msgList as $idx => $info){
             //$msgList[$idx]['dname'] =$devicelList[$info['did']];
             $msgList[$idx]['cname'] =$channelList[$info['cid']];
        }
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this -> assign('msgList',$msgList);
        $this -> assign('channelDisabled', !isset($_GET['cname']));
        $this -> assign('deviceDisabled', isset($_GET['cname']));
        $this -> display('msgList');
    }
    function searchdevice() {
       $dname = $_GET['dname'];
       $deviceObj = D("Sj.Device");
	   $zh_dname['dname']=array('like',"'%".$dname."%'");
       $result = $deviceObj -> where($zh_dname) -> select();
       if($result){
             $this -> assign("devicelist",$result);
             $this -> msgList();
       }else{
        $this -> error("没有该机型！请确认。");
       }
    }
    function searchchannel() {
       $cname = $_GET['cname'];
       $chlObj = D("Sj.Channel");
	   $zh_chname['chname']=array('like',"'%".$cname."%'");
       $result = $chlObj -> where($zh_chname) -> select();
       if($result){
             $this -> assign("channellist",$result);
             $this -> msgList();
       }else{
        $this -> error("没有该渠道！请确认。");
       }
    }
    function addMsg() {
    	$msgObj = D("Sj.Pmd");
    	$deviceObj = D("Sj.Device");
    	$channelObj = D("Sj.Channel");
    	$type = $_POST['type'];

    	
    	$dinfo = '';
    	$dname = escape_string($_POST['dname']);
    	$dinfo = $dname;
    	if($dname){
    		$where ="dname = ".$dname;
    	}
    	if($_POST['search_dname']){
    		$did = escape_string($_POST['search_dname']);
    		$where = " did = ".$did;
    		$dinfo = $did;
    	}

    	$chlinfo = "";
    	$cname = escape_string($_POST["cname"]);
    	if($dname){
    		$where ="chname = ".$cname;
    	}
    	if($_POST['search_cname']){
    		$cid = escape_string($_POST['search_cname']);
    		$where = " cid = ".$cid;
    		$chlinfo = $cid;
    	}

    	$msg = $_POST['message'];
    	$url = $_POST['url'];

		if($type=='device'){
	    	if(empty($dinfo) || empty($msg) || empty($url)){
	    		$this -> error("请输入有效信息！");
	    	}
	    	$create_time = time();
	    	$exists = $deviceObj -> where($where) -> select();
	    	$data = array();
	    	if($exists){
	    		$msgExists = $msgObj -> where("did = ".$exists[0]['did']." and status=1") -> select();
	    		if(!empty($msgExists)){
	    			$this -> error("该机型的推送公告已存在！");
	    		}else{
	    			$data['did'] = $exists[0]['did'];
	    			$data['url'] = $url;
	    			$data['message_type'] = 1;
	    			$data['message'] = $msg;
	    			$data['create_time'] = $create_time;
	    			$data['status'] = 1;
	    			$data['lasted_at'] = $create_time;
	    			$affect = $msgObj -> add($data);
	    			if($affect > 0){
	    				$this->writelog('手机公告信息管理_手机公告信息列表_机型ID 为'.$data['did']."添加相应的公告信息",'pu_did_message',$affect,__ACTION__ ,"","add");
	    				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Pushmsg/msgList');
	    				$this->success("添加成功！");
	    			}
	    		}
	    	}else{
	    		$this -> error("该机型不存在！");
	    	}
		}elseif($type=='channel'){
			if(empty($chlinfo) || empty($msg) || empty($url)){
	    		$this -> error("请输入有效信息！");
	    	}
	    	$create_time = time();
	    	$exists = $channelObj -> where($where) -> select();
	    	$data = array();
	    	if($exists){
	    		$msgExists = $msgObj -> where("cid = ".$exists[0]['cid']." and status=1") -> select();
	    		if(!empty($msgExists)){
	    			$this -> error("该渠道的推送公告已存在！");
	    		}else{
	    			$data['cid'] = $exists[0]['cid'];
	    			$data['url'] = $url;
	    			$data['message_type'] = 2;
	    			$data['message'] = $msg;
	    			$data['create_time'] = $create_time;
	    			$data['status'] = 1;
	    			$data['lasted_at'] = $create_time;
	    			$affect = $msgObj -> add($data);
	    			if($affect > 0){
	    				$this->writelog('手机公告信息管理_手机公告信息列表_渠道ID 为'.$data['cid']."添加相应的公告信息",'pu_did_message',$affect,__ACTION__ ,"","add");
	    				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Pushmsg/msgList');
	    				$this->success("添加成功！");
	    			}
	    		}
	    	}else{
	    		$this -> error("该渠道不存在！");
	    	}
		}
    }
    function editMsgForm() {
        $msgObj = D("Sj.Pmd");
        $deviceObj = D("Sj.Device");
        $channelObj = D("Sj.Channel");
        $id = $_GET['id'];
        $result = $msgObj -> where(array("id"=>$id)) -> select();
        if(!empty($result)){
             $devicInfo = $deviceObj -> where("did = ".$result[0]['did']) -> select();
             $channelInfo = $channelObj -> where("cid = ".$result[0]['cid']) -> select();
             $this -> assign("dname",$devicInfo[0]['dname']);
             $this -> assign("chname",$channelInfo[0]['chname']);
             $this -> assign("msginfo",$result[0]);
        }else{
           $this -> error("信息不存在");
        }
        $this -> display("editMsgForm");
    }
    function doEditMsg() {
    	$msgObj = D("Sj.Pmd");
    	$id = $_POST['id'];
    	$msg = $_POST['message'];
    	$url = $_POST['url'];
    	if(!empty($msg)&&!empty($url)){
    		$data['message'] = $msg;
    		$data['url'] = $url;
    		$data['lasted_at'] = time();
    	}else{
    		$this -> error("信息不能为空！");
    	}
        $log = $this->logcheck(array('id'=>$id),'pu_did_message',$data,$msgObj);
    	$affect = $msgObj -> where(array("id"=>$id)) -> save($data);
    	if($affect > 0){
    		//$this->writelog('手机公告ID 为'.$id."编辑相应的公告信息");
            $this->writelog("手机公告信息管理_手机公告信息列表_手机公告ID 为$id".$log,'pu_did_message',$id,__ACTION__ ,"","edit");
    		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Pushmsg/msgList');
    		$this->success("编辑成功！");
    	}else{
    		$this -> error("请编辑有效信息！");
    	}
    }
    function delMsg() {
        $msgObj = D("Sj.Pmd");
        $id = $_GET['id'];
        $data['status'] =0;
        $data['lasted_at'] = time();
        $affect = $msgObj -> where(array("id" => $id)) -> save($data);
        if($affect > 0){
             $this->writelog('手机公告信息管理_手机公告信息列表_手机公告ID 为'.$id."删除相应的公告信息",'pu_did_message',$id,__ACTION__ ,"","del");
             $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Pushmsg/msgList');
             $this->success("删除成功！");
        }
    }
}
?>