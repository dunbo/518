<?php
/**
 * 安智网产品管理平台 框架首页控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.4.21
 * ----------------------------------------------------------------------------
*/
class IndexAction extends Action {
	// 框架首页
	public function index() {
		/*if(empty($_SESSION['admin']['admin_id'])) {
           $this->redirect('Public/login','' , 0,'页面跳转中~');
        }*/

        if(empty($_SESSION['admin']['admin_id'])) {
            if(!checkCookieAdmin()){
                $this->redirect('Public/login','' , 0,'页面跳转中~');
            }
        }     
		$this->display('Public:index');
	}
}
?>