<?php

class SoftwarelistAction extends CommonAction {
   
   function index(){
        import("@.ORG.Page");
		 
		$admin_model = D('Caiji.Softwarelist');
        $count = $admin_model->count();
        $page = new Page($count, 15);
        $Softwarelist_list = $admin_model->field("`soft_id`,`soft_name`,`soft_apk`,`soft_web`,`soft_adddate`,`soft_status`")->order('soft_id desc')->limit($page->firstRow.','.$page->listRows)->select();

        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("page", $show);
        $this->assign("Softwarelist" , $Softwarelist_list);
	 
        $this->display('index');
   }
}
?>