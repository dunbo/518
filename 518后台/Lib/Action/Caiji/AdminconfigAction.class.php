<?php

class AdminconfigAction extends CommonAction {

    function index(){
        import("@.ORG.Page");
        $admin_model = D('Caiji.Adminconfig');
        $count = $admin_model->count();
        $page = new Page($count, 15);
        $adminconfig_list = $admin_model->field("`id`,`cj_website`,`cj_status`,`cj_beizhu`")->limit($page->firstRow.','.$page->listRows)->select();
        	
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("page", $show);
        $this->assign("adminconfiglist" , $adminconfig_list);

        $this->display('index');
    }
    
    function adminconfig_add(){
        $this->display('adminconfig_add');
    }
     
    function adminconfig_modify(){
        $id=$_GET['id'];
        $admin_model = D('Caiji.Adminconfig');
        $adminconfig_info = $admin_model->field(" `id`,`cj_website`,`cj_url`,`cj_liebaostr`,`cj_fenyestr`,`cj_softname`,`cj_softxt`,`cj_status`,`cj_beizhu`,`cj_submittype`,`cj_apkurl`")->where(array('id' => $id))->select();

        $this->assign("id" , $adminconfig_info[0]['id']);
        $this->assign("cj_website" , $adminconfig_info[0]['cj_website']);
        $this->assign("cj_url" , $adminconfig_info[0]['cj_url']);
        $this->assign("cj_liebaostr" , $adminconfig_info[0]['cj_liebaostr']);
        $this->assign("cj_softname" , $adminconfig_info[0]['cj_softname']);
        $this->assign("cj_softxt" , $adminconfig_info[0]['cj_softxt']);
        $this->assign("cj_status" , $adminconfig_info[0]['cj_status']);
        $this->assign("cj_beizhu" , $adminconfig_info[0]['cj_beizhu']);
        $this->assign("cj_submittype" , $adminconfig_info[0]['cj_submittype']);
        $this->assign("cj_apkurl" , $adminconfig_info[0]['cj_apkurl']);
        $this->display('adminconfig_modify');
    }
    
    function adminconfig_save(){
        //未作判断，直接insert，应加入网址与名称是否重复的判断
        $cj_website = trim($_POST['cj_website']);
        $cj_url = trim($_POST['cj_url']);
        $cj_liebaostr =  trim($_POST['cj_liebaostr']);
        $cj_fenyestr = trim($_POST['cj_fenyestr']);
        $cj_softname =  trim($_POST['cj_softname']);
        $cj_softxt =  trim($_POST['cj_softxt']);
        $cj_status = $_POST['cj_status'];
        $cj_beizhu = trim($_POST['cj_beizhu']);
        $cj_submittype = $_POST['cj_submittype'];
        $cj_apkurl	=  trim($_POST['cj_apkurl']);

        $action = $_GET['action'];

        if(empty($cj_website) || empty($cj_url) ){
            $this->error("参数错误！");
        }
        $admin_model = D('Caiji.Adminconfig');
        if(!empty($action)){
            $id = $_GET['id'];
            $data=array('cj_website'=>$cj_website,'cj_url'=>$cj_url,'cj_liebaostr'=>$cj_liebaostr,'cj_fenyestr'=>$cj_fenyestr,'cj_softname' => $cj_softname,'cj_softxt'=>$cj_softxt,'cj_status'=>$cj_status,'cj_beizhu'=>$cj_beizhu,'cj_submittype'=>$cj_submittype,'cj_apkurl'=>$cj_apkurl);
            $log_result = $this->logcheck(array('id' => $id),'cj_admin_config',$data,$admin_model);
            $affect = $admin_model->where(array('id' => $id))->save($data);
            if ($affect) {
                $msg = '采集站点配置，修改id为'.$id.'的配置.'.$log_result;
            }
			if ($affect > 0) {
				$this->writelog($msg,'cj_admin_config',$id,__ACTION__ ,"","edit");
				$this->assign('jumpUrl',"__URL__/index");
				$this->success("修改成功！");
			}else {
				$this->error("修改失败！");
			}
	    }else{
	        $affect = $admin_model->add(array('cj_website'=>$cj_website,'cj_url'=>$cj_url,'cj_liebaostr'=>$cj_liebaostr,'cj_fenyestr'=>$cj_fenyestr,'cj_softname' => $cj_softname,'cj_softxt'=>$cj_softxt,'cj_status'=>$cj_status,'cj_beizhu'=>$cj_beizhu,'cj_submittype'=>$cj_submittype,'cj_apkurl'=>$cj_apkurl));
	        if ($affect) {
	            $msg = '添加采集站点配置,采集站点名称为'.$cj_website.',采集站点url:'.$cj_url.',采集列表正则:'.$cj_liebaostr.',软件名正则:'.$cj_softname.',软件简介正则:'.$cj_softxt.',采集站点状态:'.$cj_status.',备注:'.$cj_beizhu.',提交方式:'.$cj_submittype.',apk正则:'.$cj_apkurl.'';
	        }
	    }
	
		if ($affect > 0) {
		    $this->writelog($msg,'cj_admin_config',$affect,__ACTION__ ,"","add");
		    $this->assign('jumpUrl',"__URL__/index");
		    $this->success("添加成功！");
		}else {
		    $this->error("添加失败！");
		}
	}
}
?>