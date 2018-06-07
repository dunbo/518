<?php
/**
 * 安智网产品管理平台 开发者管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * By:金山 2010.5.19
 * ----------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Shanghai');

class UserAction extends CommonAction {
    private $uid;           //用户id;传值用
    private $sid;           //用户id;
    private $users_db;      //用户表
    private $userslist;     //用户列表
    private $conf_db;       //配置表
    private $configlist;    //配置列表
    private $hashs;         //默认hashs
    private $map;           //条件
    private $errarr;        //错误数组
	public function index() {
		//$this->display();
	}
    //开发者管理_待审核开发者列表
    public function auditforuser() {
		//获取驳回原因列表
		$model = new Model();
		$reason_list = $model -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 1 ))->select();
		foreach($reason_list as &$val){
		    if($val['content2']){
		        $val['content2'] = explode('<br />', $val['content2']);
		    }
		}
		if ($_REQUEST['p'])
			$this->assign('p', $_REQUEST['p']);
		else
			$this->assign('p', 1);

		if ($_REQUEST['lr'])
			$this->assign('lr', $_REQUEST['lr']);
		else
			$this->assign('lr', 10);
		
		require_once(realpath(dirname(__FILE__).'/../../../../').'/GoPHP/config/config.inc.php');

		$list_type = isset($_GET['list_type']) && in_array($_GET['list_type'],array('audit','reject')) ? $_GET['list_type'] : 'audit';
		if($list_type == 'audit') {	//待审核
			$status = 1;
		} else if($list_type == 'reject') {	//驳回
			$status = -1;
		} else {
			$status = 1;
		}
        if(empty($_GET['p'])) {
            $this->map="p.status='{$status}' ";
			if($status==1) {	//email验证后才进入待审核
				$this->map.=' and p.email_verified=1';
			}
			if(!empty($_GET['username'])){
				//获取userid
				$userid = $model -> table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
				$this->map.=" and p.dev_id='{$userid}'";
 			}else{
				if(!empty($_GET['dev_id'])) {
					$this->map.=" and p.dev_id='{$_GET['dev_id']}'";
				}
				if(!empty($_GET['truename'])) {
					$this->map.=" and p.truename like '%{$_GET['truename']}%'";
				}
				if(isset($_GET['type']) && $_GET['type']!=-1) {
					$this->map.=" and p.type='{$_GET['type']}'";
				}
				if(!empty($_GET['mobile'])) {
					$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
				}
				if(!empty($_GET['dev_name'])) {
					$this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
				}
				if(!empty($_GET['cardnumber'])) {
					$this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
				}
				if(!empty($_GET['charter'])) {
					$this->map.=" and p.charter like '%{$_GET['charter']}%'";
				}
				if(!empty($_GET['location'])) {
					$this->map.=" and p.location='{$_GET['location']}'";
				}
				if(!empty($_GET['email'])) {
					$this->map.=" and p.email like '%{$_GET['email']}%'";
				}
				if(!empty($_GET['begintime'])) {
					$begintime = strtotime($_GET['begintime']);
					$this->map.=" and p.register_time>='{$begintime}'";
				}
				if(!empty($_GET['reg_ip'])){
					$this->map.=" and p.reg_ip='{$_GET['reg_ip']}'";
				}
				if(!empty($_GET['endtime'])) {
					$endtime = strtotime($_GET['endtime']);
					$this->map.=" and p.register_time<='{$endtime}'";
				}	
			}
            $_SESSION['admin']['userlist']['where']=$this->map;
        }else
        {
            $this->map=$_SESSION['admin']['userlist']['where'];
        }
        //dump($this->map);
        $this->users_db=D('Sj.Developer');
        import("@.ORG.Page");
		$count = $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
        $Page=new Page($count,10);
		$this->userslist=$model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.register_time asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$devid_arr = array();
		if($this->userslist) {
			foreach($this->userslist as $key=>$val) {
				$devid_arr[] = $val['dev_id'];		//身份证
				$devid_arr[] = 'a'.$val['dev_id'];	//营业执照

				if($val['email']) {
					$this->userslist[$key]['email_verified_str'] = $val['email_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['mobile']) {
					$this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['type']==0) {
					$this->userslist[$key]['type_str'] = '公司';
				} else if($val['type']==1) {
					$this->userslist[$key]['type_str'] = '个人';
				} else if($val['type']==2) {
					$this->userslist[$key]['type_str'] = '团队';
				}
				$this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST.$val['cardpic'] : '';
				$this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST.$val['charterpic'] : '';
				if($val['im_type']==1) {
					$this->userslist[$key]['im_type_str'] = 'QQ';
				} else if($val['im_type']==2) {
					$this->userslist[$key]['im_type_str'] = 'Gtalk';
				} else if($val['im_type']==3) {
					$this->userslist[$key]['im_type_str'] = 'Msn';
				} else if($val['im_type']==4) {
					$this->userslist[$key]['im_type_str'] = 'Skype';
				}
				$this->userslist[$key]['register_time'] = date('Y-m-d H:i:s',$val['register_time']);

				if($val['location']==1) {
					$this->userslist[$key]['location_str'] = '中国大陆';
				} else if($val['location']==2) {
					$this->userslist[$key]['location_str'] = '港澳台和国外';
				}
			}
		}
		$devid_str = "'".implode("','",$devid_arr)."'";
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("reason_list", $reason_list );
		$this->assign ("list_type", $list_type);
        $this->assign('userslist',$this->userslist);
		$this->assign('devid_str',$devid_str);
        $this->display();
    }
    //开发者管理_待审核开发者_审核
    public function auditforuser_confirm() {
		$denyurl = $_POST['denyurl'];
		//referer定义
		$c = $_GET['key'];
		$referer = '/index.php/'.GROUP_NAME.'/User/auditforuser/list_type/'.$_GET['list_type'];
		if($_POST['preurl']) {
			$referer = $_POST['preurl'];
		}
        $this->uid=$_GET['uid'];

        if(empty($this->uid)) {
            $this->assign('jumpUrl',$referer);
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }

        $type=$_GET['state'];
		$reason = '';	//驳回原因
		if($_POST['reject']) {
			foreach($_POST['reject'] as $key=>$val) {
				$reason .= $val."<br />";
			}
		}
		if($_POST['reject_reason'] && $_POST['reject_reason']!='请输入其他驳回原因：') {
			$reason .= $_POST['reject_reason'];
		}

        if($type==null) {
            $this->assign('jumpUrl',$referer);
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if($type==-1 && $reason == '') {
            $this->assign('jumpUrl',$referer);
            $this->error("请选择或填写驳回原因！");
        }

        $this->users_db=D('Sj.Developer');
        $map['status']=$type;
		$map['pass_time']=time();
		if($type==-1) {
			$map['dismissed'] = $reason;
			$map['dismissed_time'] = time();
		}

		$where = '';
		if(strpos($this->uid,',')!==FALSE) {
			$where = "dev_id IN (".mysql_escape_string($this->uid).")";
		} else {
			$where = "dev_id='{$this->uid}'";
		}

        if(false!==$this->users_db->where($where)->save($map)) {
			$do_str = '';
			if($_GET['state']==1) {
				$do_str = '撤销';
			} else if($_GET['state']==0) {
				$do_str = '通过';
			} else if($_GET['state']==-1) {
				$do_str = '驳回';
				$reason_log = "驳回原因：{$reason}";
			} else {
				$do_str = '审核';
			}
			foreach (explode(",",$this->uid) as $value){
				if ($value) 
					$this->writelog("{$do_str}了ID为".$value."的开发者。".$reason_log,'pu_developer',$value,__ACTION__ ,"","edit");
			}
			if($c == 1 && (strpos($this->uid,',')===FALSE)){
				$this->ajaxReturn(1,"确认成功",1);
			}else{
				$this->assign('jumpUrl',$denyurl);
				$this->success("确认成功");
			}
        }else
        {
			if($c == 1 && strpos($this->uid,',')===FALSE){
				$this->ajaxReturn(0,"通过失败！",0);
			}else{
				$this->assign('jumpUrl',$denyurl);
				$this->error('确认失败');
			}
        }
    }
    //开发者管理_驳回开发者列表
    public function reject_users() {
		$model = new Model();
		require_once(realpath(dirname(__FILE__).'/../../../../').'/GoPHP/config/config.inc.php');

		$list_type = 'reject';
		$status = -1;
        if(empty($_GET['p'])) {
            $this->map="p.status='{$status}' ";
			if(!empty($_GET['username'])){
				//获取userid
				$userid = $model -> table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
				$this->map.=" and p.dev_id='{$userid}'";
 			}else{
				if(!empty($_GET['dev_id'])) {
					$this->map.=" and p.dev_id='{$_GET['dev_id']}'";
				}
				if(!empty($_GET['truename'])) {
					$this->map.=" and p.truename like '%{$_GET['truename']}%'";
				}
				if(isset($_GET['type']) && $_GET['type']!=-1) {
					$this->map.=" and p.type='{$_GET['type']}'";
				}
				if(!empty($_GET['mobile'])) {
					$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
				}
				if(!empty($_GET['dev_name'])) {
					$this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
				}
				if(!empty($_GET['cardnumber'])) {
					$this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
				}
				if(!empty($_GET['charter'])) {
					$this->map.=" and p.charter like '%{$_GET['charter']}%'";
				}
				if(!empty($_GET['location'])) {
					$this->map.=" and p.location='{$_GET['location']}'";
				}
				if(!empty($_GET['email'])) {
					$this->map.=" and p.email like '%{$_GET['email']}%'";
				}
				if(!empty($_GET['begintime'])) {
					$begintime = strtotime($_GET['begintime']);
					$this->map.=" and p.register_time>='{$begintime}'";
				}
				if(!empty($_GET['endtime'])) {
					$endtime = strtotime($_GET['endtime']);
					$this->map.=" and p.register_time<='{$endtime}'";
				}	
			}
            $_SESSION['admin']['userlist']['where']=$this->map;
        }else
        {
            $this->map=$_SESSION['admin']['userlist']['where'];
        }
        //dump($this->map);
        $this->users_db=D('Sj.Developer');
        import("@.ORG.Page");
        $count= $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
        $Page=new Page($count,10);
        $this->userslist=$model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.register_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$devid_arr = array();
		if($this->userslist) {
			foreach($this->userslist as $key=>$val) {
				$devid_arr[] = $val['dev_id'];		//身份证
				$devid_arr[] = 'a'.$val['dev_id'];	//营业执照
				if($val['email']) {
					$this->userslist[$key]['email_verified_str'] = $val['email_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['mobile']) {
					$this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['type']==0) {
					$this->userslist[$key]['type_str'] = '公司';
				} else if($val['type']==1) {
					$this->userslist[$key]['type_str'] = '个人';
				} else if($val['type']==2) {
					$this->userslist[$key]['type_str'] = '团队';
				}
				$this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST.$val['cardpic'] : '';
				$this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST.$val['charterpic'] : '';
				if($val['im_type']==1) {
					$this->userslist[$key]['im_type_str'] = 'QQ';
				} else if($val['im_type']==2) {
					$this->userslist[$key]['im_type_str'] = 'Gtalk';
				} else if($val['im_type']==3) {
					$this->userslist[$key]['im_type_str'] = 'Msn';
				} else if($val['im_type']==4) {
					$this->userslist[$key]['im_type_str'] = 'Skype';
				}
				$this->userslist[$key]['register_time'] = date('Y-m-d H:i:s',$val['register_time']);
				$this->userslist[$key]['dismissed_time'] = date('Y-m-d H:i:s',$val['dismissed_time']);

				if($val['location']==1) {
					$this->userslist[$key]['location_str'] = '中国大陆';
				} else if($val['location']==2) {
					$this->userslist[$key]['location_str'] = '港澳台和国外';
				}
			}
		}
		$devid_str = "'".implode("','",$devid_arr)."'";
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ( "page", $show );
		$this->assign ("list_type", $list_type);
        $this->assign('userslist',$this->userslist);
		$this->assign('devid_str',$devid_str);
        $this->display();
    }
    //开发者管理_驳回开发者列表_删除
    public function reject_users_delete() {
		$preurl = $_POST['preurl'] ? $_POST['preurl'] : '/index.php/'.GROUP_NAME.'/User/reject_users';
		if(!is_numeric($_GET['uid']) && !preg_match('/[0-9,]+/i',$_GET['uid'])) {
			$this->assign('jumpUrl',$preurl);
			$this->error('参数错误！');
		}
		//删除 pu_developer 表中开发者记录
		$where = '';
		if(is_numeric($_GET['uid'])) {
			$where = "dev_id='{$_GET['uid']}'";
		} else {
			$where = "dev_id IN ({$_GET['uid']})";
		}
		$model = new Model();
		$rs = $model->query("DELETE FROM pu_developer WHERE {$where} AND (status='-1' OR status='-2')");	//驳回,屏蔽列表中删除操作

		$this->writelog("删除ID：{$_GET['uid']}开发者",'pu_developer',$_GET['uid'],__ACTION__ ,"","del");

		$this->assign('jumpUrl',$preurl);
		$this->success("恭喜您，该开发者删除成功！");
    }
	//开发者管理_屏蔽开发者列表
	public function shield_users() {
		$model = new Model();
        if(empty($_GET['p'])) {
            $this->map='p.status=-2 ';
			if(!empty($_GET['username'])){
				//获取userid
				$userid = $model -> table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
				$this->map.=" and p.dev_id='{$userid}'";
 			}else{
				if(!empty($_GET['dev_id'])) {
					$this->map.=" and p.dev_id='{$_GET['dev_id']}'";
				}
				if(!empty($_GET['truename'])) {
					$this->map.=" and p.truename like '%{$_GET['truename']}%'";
				}
				if(isset($_GET['type']) && $_GET['type']!=-1) {
					$this->map.=" and p.type='{$_GET['type']}'";
				}
				if(!empty($_GET['mobile'])) {
					$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
				}
				if(!empty($_GET['dev_name'])) {
					$this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
				}
				if(!empty($_GET['cardnumber'])) {
					$this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
				}
				if(!empty($_GET['charter'])) {
					$this->map.=" and p.charter like '%{$_GET['charter']}%'";
				}
				if(!empty($_GET['location'])) {
					$this->map.=" and p.location='{$_GET['location']}'";
				}
				if(!empty($_GET['email'])) {
					$this->map.=" and p.email like '%{$_GET['email']}%'";
				}
				if(!empty($_GET['begintime'])) {
					$begintime = strtotime($_GET['begintime']);
					$this->map.=" and p.register_time>='{$begintime}'";
				}
				if(!empty($_GET['endtime'])) {
					$endtime = strtotime($_GET['endtime']);
					$this->map.=" and p.register_time<='{$endtime}'";
				}
			}
            $_SESSION['admin']['userlist']['where']=$this->map;
        }else
        {
            $this->map=$_SESSION['admin']['userlist']['where'];
        }
        //dump($this->map);
        $this->users_db=D('Sj.Developer');
        import("@.ORG.Page");
        $count= $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
        $Page=new Page($count,10);
        $this->userslist=$model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.register_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$model = new Model();
		foreach($this->userslist as $indx => $info){
		 $status = $model -> table('pu_user') -> where('userid = '.$info['dev_id']) -> getField('status'); 	
		 $this -> userslist[$indx]['deny_status'] = $status;
		}
		$_soft_db = M('soft');
		$devid_arr = array();
		if($this->userslist) {
			foreach($this->userslist as $key=>$val) {
				$devid_arr[] = $val['dev_id'];		//身份证
				$devid_arr[] = 'a'.$val['dev_id'];	//营业执照
				if($val['email']) {
					$this->userslist[$key]['email_verified_str'] = $val['email_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['mobile']) {
					$this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['type']==0) {
					$this->userslist[$key]['type_str'] = '[公司]';
				} else if($val['type']==1) {
					$this->userslist[$key]['type_str'] = '[个人]';
				} else if($val['type']==2) {
					$this->userslist[$key]['type_str'] = '[团队]';
				}
				$this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST.$val['cardpic'] : '';
				$this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST.$val['charterpic'] : '';
				if($val['im_type']==1) {
					$this->userslist[$key]['im_type_str'] = 'QQ';
				} else if($val['im_type']==2) {
					$this->userslist[$key]['im_type_str'] = 'Gtalk';
				} else if($val['im_type']==3) {
					$this->userslist[$key]['im_type_str'] = 'Msn';
				} else if($val['im_type']==4) {
					$this->userslist[$key]['im_type_str'] = 'Skype';
				}
				$this->userslist[$key]['register_time'] = date('Y-m-d H:i:s',$val['register_time']);
				$this->userslist[$key]['pass_time'] = $val['pass_time'] ? date('Y-m-d H:i:s',$val['pass_time']) : '';
				$this->userslist[$key]['shield_time'] = $val['shield_time'] ? date('Y-m-d H:i:s',$val['shield_time']) : '';
				//软件数
				$num = $_soft_db->where("status=1 and hide=1 and channel_id='' and dev_id='{$val['dev_id']}'")->count();
				$this->userslist[$key]['soft_num'] = $num ? $num : 0;

				if($val['location']==1) {
					$this->userslist[$key]['location_str'] = '中国大陆';
				} else if($val['location']==2) {
					$this->userslist[$key]['location_str'] = '港澳台和国外';
				}
			}
		}
		$devid_str = "'".implode("','",$devid_arr)."'";
        $Page->setConfig('header','篇记录');
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ( "page", $show );
        $this->assign('userslist',$this->userslist);
		$this->assign('devid_str',$devid_str);
		$this->display();
	}

    //开发者管理_开发者列表
    public function userlists() {
		$model = new Model();
        if(empty($_GET['p'])) {
			$p = 1;
			unset($_SESSION['dev_person']);
            $this->map='p.status=0 ';
			if(!empty($_GET['username'])){
				//获取userid
				$userid = $model -> table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
				$this->map.=" and p.dev_id='{$userid}'";
				
 			}else{
 				if(!empty($_GET['last_ip'])) {
					$login_ip = ip2long($_GET['last_ip']);
					$this->assign("last_ip",$login_ip);
					$_SESSION['dev_person']['last_ip'] = $login_ip;
					$this->map.=" and u.last_ip='{$login_ip}'";
				}
				if(!empty($_GET['dev_id'])) {
					$dev_id = $_GET['dev_id'];
					$this->assign("dev_id",$dev_id);
					$_SESSION['dev_person']['dev_id'] = $dev_id;
					$this->map.=" and p.dev_id='{$_GET['dev_id']}'";
				}
				if(!empty($_GET['truename'])) {
					$truename = $_GET['truename'];
					$this->assign("truename",$truename);
					$_SESSION['dev_person']['truename'] = $dev_id;
					$this->map.=" and p.truename like '%{$_GET['truename']}%'";
				}
				if($_GET['type']==="0" ||  $_GET['type']=== "1"){
					$type = $_GET['type'];
					$this->assign("type",$type);
					$_SESSION['dev_person']['type'] = $type;
					$this->map.=" and p.type='{$_GET['type']}'";
				}else{
					$type = 3;
					$this->assign("type",$type);
					$_SESSION['dev_person']['type'] = $type;
				}
				if(!empty($_GET['mobile'])) {
					$mobile = $_GET['mobile'];
					$this->assign("mobile",$mobile);
					$_SESSION['dev_person']['mobile'] = $mobile;
					$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
				}
				if(!empty($_GET['dev_name'])) {
					$dev_name = $_GET['dev_name'];
					$this->assign("dev_name",$dev_name);
					$_SESSION['dev_person']['dev_name'] = $dev_name;
					$this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
				}
				if(!empty($_GET['cardnumber'])) {
					$cardnumber = $_GET['cardnumber'];
					$this->assign("cardnumber",$cardnumber);
					$_SESSION['dev_person']['cardnumber'] = $cardnumber;
					$this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
				}
				if(!empty($_GET['charter'])) {
					$charter = $_GET['charter'];
					$this->assign("charter",$charter);
					$_SESSION['dev_person']['charter'] = $charter;
					$this->map.=" and p.charter like '%{$_GET['charter']}%'";
				}
				if(!empty($_GET['location'])) {
					$location = $_GET['location'];
					$this->assign("location",$location);
					$_SESSION['dev_person']['location'] = $location;
					$this->map.=" and p.location='{$_GET['location']}'";
				}
				if(!empty($_GET['email'])) {
					$email = $_GET['email'];
					$this->assign("email",$email);
					$_SESSION['dev_person']['email'] = $email;
					$this->map.=" and p.email like '%{$_GET['email']}%'";
				}
				if(!empty($_GET['begintime'])) {
					$begintime = strtotime($_GET['begintime']);
					$this->assign("begintime",$begintime);
					$_SESSION['dev_person']['begintime'] = $begintime;
					$this->map.=" and p.register_time>='{$begintime}'";
				}
				if(!empty($_GET['endtime'])) {
					$endtime = strtotime($_GET['endtime']);
					$this->assign("endtime",$endtime);
					$_SESSION['dev_person']['endtime'] = $endtime;
					$this->map.=" and p.register_time<='{$endtime}'";
				}
			}
            $_SESSION['admin']['userlist']['where']=$this->map;
        }else
        {	
        	if($_GET['dev_id']){
				$dev_id = $_GET['dev_id'];
				$_SESSION['dev_person']['dev_id'] = $dev_id;
			}else{
				$dev_id = $_SESSION['dev_person']['dev_id'];
			}
			$this->assign("dev_id",$dev_id);
			if($_GET['truename']){
				$truename = $_GET['truename'];
				$_SESSION['dev_person']['truename'] = $truename;
			}else{
				$truename = $_SESSION['dev_person']['truename'];
			}
			$this->assign("truename",$truename);
			if($_GET['type']==="0" ||  $_GET['type']=== "1" || $_GET['type']=== "3"){
				$type = $_GET['type'];
				$_SESSION['dev_person']['type'] = $type;
			}else{
				$type = $_SESSION['dev_person']['type'] ;
			}
			$this->assign("type",$type);
			if($_GET['mobile']){
				$mobile = $_GET['mobile'];
				$_SESSION['dev_person']['mobile'] = $mobile;
			}else{
				$mobile = $_SESSION['dev_person']['mobile'];
			}
			$this->assign("mobile",$mobile);
			if($_GET['dev_name']){
				$dev_name = $_GET['dev_name'];
				$_SESSION['dev_person']['dev_name'] = $dev_name;
			}else{
				$dev_name = $_SESSION['dev_person']['dev_name'];
			}
			$this->assign("dev_name",$dev_name);
			if($_GET['cardnumber']){
				$cardnumber = $_GET['cardnumber'];
				$_SESSION['dev_person']['cardnumber'] = $cardnumber;
			}else{
				$cardnumber = $_SESSION['dev_person']['cardnumber'];
			}
			$this->assign("cardnumber",$cardnumber);
			if($_GET['charter']){
				$charter = $_GET['charter'];
				$_SESSION['dev_person']['charter'] = $charter;
			}else{
				$charter = $_SESSION['dev_person']['charter'];
			}
			$this->assign("charter",$charter);
			if($_GET['charter']){
				$location = $_SESSION['dev_person']['location'];
			}else{
				$location = $_SESSION['dev_person']['location'];
			}
			$this->assign("location",$location);
			if($_GET['email']){
				$email = $_GET['email'];
				$_SESSION['dev_person']['email'] = $email;
			}else{
				$email = $_SESSION['dev_person']['email'];
			}
			$this->assign("email",$email);
			if($_GET['begintime']){
				$begintime = $_GET['begintime'];
				$_SESSION['dev_person']['begintime'] = $begintime;
			}else{
				$begintime = $_SESSION['dev_person']['begintime'];
			}
			$this->assign("begintime",$begintime);
			if($_GET['endtime']){
				$endtime = $_GET['endtime'];
				$_SESSION['dev_person']['endtime'] = $endtime;
			}else{
				$endtime = $_SESSION['dev_person']['endtime'];
			}
			$this->assign("endtime",$endtime);
			$p = $_GET['p'];
            $this->map=$_SESSION['admin']['userlist']['where'];
        }
        //dump($this->map);
        $this->users_db=D('Sj.Developer');
        import("@.ORG.Page");
        $count= $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
        $Page=new Page($count,15);
        $this->userslist=$model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.register_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //echo $model->getLastSql();
       foreach($this->userslist as $indx => $info){
		 $status = $model -> table('pu_user') -> where('userid = '.$info['dev_id']) -> getField('status'); 	
		 $this -> userslist[$indx]['deny_status'] = $status;
		}
		$_soft_db = M('soft');
		$devid_arr = array();
		if($this->userslist) {
			foreach($this->userslist as $key=>$val) {
				$devid_arr[] = $val['dev_id'];		//身份证
				$devid_arr[] = 'a'.$val['dev_id'];	//营业执照
				if($val['email']) {
					$this->userslist[$key]['email_verified_str'] = $val['email_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['mobile']) {
					$this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified']==1 ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['type']==0) {
					$this->userslist[$key]['type_str'] = '[公司]';
				} else if($val['type']==1) {
					$this->userslist[$key]['type_str'] = '[个人]';
				} else if($val['type']==2) {
					$this->userslist[$key]['type_str'] = '[团队]';
				}
				$this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST.$val['cardpic'] : '';
				$this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST.$val['charterpic'] : '';
				if($val['im_type']==1) {
					$this->userslist[$key]['im_type_str'] = 'QQ';
				} else if($val['im_type']==2) {
					$this->userslist[$key]['im_type_str'] = 'Gtalk';
				} else if($val['im_type']==3) {
					$this->userslist[$key]['im_type_str'] = 'Msn';
				} else if($val['im_type']==4) {
					$this->userslist[$key]['im_type_str'] = 'Skype';
				}
				$this->userslist[$key]['register_time'] = date('Y-m-d H:i:s',$val['register_time']);
				$this->userslist[$key]['pass_time'] = $val['pass_time'] ? date('Y-m-d H:i:s',$val['pass_time']) : '';
				//软件数
				$num = $_soft_db->where("status=1 and hide=1 and channel_id='' and dev_id='{$val['dev_id']}'")->count();
				$this->userslist[$key]['soft_num'] = $num ? $num : 0;

				if($val['location']==1) {
					$this->userslist[$key]['location_str'] = '中国大陆';
				} else if($val['location']==2) {
					$this->userslist[$key]['location_str'] = '港澳台和国外';
				}
			}
		}
		$devid_str = "'".implode("','",$devid_arr)."'";

        $Page->setConfig('header','篇记录');
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ( "page", $show );
        $this->assign ("p", $p);
        $this->assign('userslist',$this->userslist);
		$this->assign('referer',$_SERVER['PHP_SELF']);
		$this->assign('devid_str',$devid_str);
		$this->display();
    }
    //开发者管理_编辑开发者_显示
    public function usersedit() {
        $this->sid=$_GET['uid'];
		$p = $_GET['p'];
        if(empty($this->sid)) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/User/userlists');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        $this->users_db=D('Sj.Developer');
        $this->userslist=$this->users_db->where(array('dev_id'=>$this->sid))->select();
		$this->assign('preurl',$_POST['preurl']);
		$this->assign('p',$p);
        $this->assign('userslist',$this->userslist[0]);
        $this->display();
    }
	//上传身份证/营业执照扫描件,摘自tools/ClsFactory.php中http_post函数,同dev.goapk.com/common.php中http_post
	public static function _http_post($vals) {
		if($_SERVER['SERVER_ADDR']=='192.168.0.99' || $_SERVER['SERVER_ADDR']=='127.0.0.1') {	//测试环境
			$host = '192.168.0.99';
			$host_dam = 'Host: 9.dummy.goapk.com';
		} else {
			$host = '192.168.1.18';
			$host_dam = 'Host: dummy.goapk.com';
		}

		$res = curl_init();
		curl_setopt($res, CURLOPT_URL, "http://${host}/service_dev.php");
		curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
		curl_setopt($res, CURLOPT_POST, true);
		curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
		$result = curl_exec($res);

		curl_close($res);
		return $result;
	} 
    //开发者管理_开发者信息编辑_执行
    public function usersedit_edit() {
		$preurl = $_POST['preurl'] ? $_POST['preurl'] : '';
        $this->sid=$_GET['uid'];
		$p = $_POST['p'];
		//$preurl = $preurl."/p/{$p}";
        if(empty($this->sid)) {
            $this->assign('jumpUrl',$preurl);
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
		
		$this->users_db=D('Sj.Developer');
		//一些唯一性信息检查
		if($_POST['type']==0) {	//公司
			if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND dev_name='{$_POST['dev_name']}'")->count()) {
				$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
				$this->error("该作者已存在，请重填！");
			}
		} else {
			if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND dev_name='{$_POST['dev_name']}' AND type=0")->count()) {
				$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
				$this->error("该作者已存在，请重填！");
			}
		}
		if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND email='{$_POST['email']}' AND email_verified=1")->count()) {
            $this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
            $this->error("该电子邮件已存在，请重填！");
		}
		if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND cardnumber='{$_POST['cardnumber']}' AND cardtype=1")->count()) {
            $this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
            $this->error("该身份证号已存在，请重填！");
		}
		if($_POST['im_id']) {
			if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND im_type='{$_POST['im_type']}' AND im_id='{$_POST['im_id']}'")->count()) {
				$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
				$this->error("该联系IM号已存在，请重填！");
			}
		}
		if($_POST['phone']) {
			if($this->users_db->where("dev_id!='{$_POST['dev_id']}' AND phone='{$_POST['phone']}'")->count()) {
				$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
				$this->error("该固定电话已存在，请重填！");
			}
		}

		//上传身份证,营业执照
		if($_POST['type']==1) {
			unset($_FILES['charterpic']);
		}

		$file = array();
		if($_FILES) {
			foreach($_FILES as $key=>$val) {
				if($key=='cardpic' && $val['size']>2097152) {
					$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
					$this->error('身份证扫描件不能大于2M，请重传！');
				}
				if($key=='charterpic' && $val['size']>2097152) {
					$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
					$this->error('营业执照扫描件不能大于2M，请重传！');
				}
				$file[$key] = '@'.$val['tmp_name'];
			}
		}
		if($file) {
			//保留，本地测试用 $file['static_data'] = UPLOAD_PATH;
			$file['static_data'] = '/data/att/m.goapk.com';
			$file['do'] = 'save';
		}
		$upload_model = D("Dev.Uploadfile");
		$upload = $upload_model -> _http_post($file);
		if($upload['info']['http_code'] !=200 ) {
			$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
			$this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
		}

		$this->userslist = array();
		if($upload) {
			foreach($upload as $key=>$val) {
				if($val=='failed') {
					if($key=='cardpic') {
						$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
						$this->error('身份证扫描件上传失败，请重试！');
					} else if($key=='charterpic') {
						$this->assign('jumpUrl',"__URL__/usersedit_edit/uid/{$_POST['dev_id']}/p/{$p}");
						$this->error('营业执照扫描件上传失败，请重试！');
					}
				} else {
					$this->userslist[$key] = $val;
				}
			}
		}
		unset($upload['errno'],$upload['error'],$upload['info']);
		$this->userslist['type']=$_POST['type'];
        $this->userslist['dev_name']=trim($_POST['dev_name']);
		$this->userslist['truename']=trim($_POST['truename']);
        $this->userslist['email']=$_POST['email'];
        $this->userslist['mobile']=$_POST['mobile'];
        $this->userslist['cardnumber']=$_POST['cardnumber'];
        $this->userslist['charter']=$_POST['charter'];
		$this->userslist['im_type']=$_POST['im_type'];
		$this->userslist['im_id']=$_POST['im_id'];
        $this->userslist['phone']=$_POST['phone'];
        $this->userslist['site']=$_POST['site'];
		$this->userslist['zipcode']=$_POST['zipcode'];
		$this->userslist['location']=$_POST['location'];
        
		$log_result = $this->logcheck(array('dev_id'=>$this->sid),'pu_developer',$this->userslist,$this->users_db);
        $list = $this->users_db->where(array('dev_id'=>$this->sid))->save($this->userslist);
        if(false!==$list) {
            $this->writelog('修改ID为['.$this->sid.']的开发者信息'.$log_result,'pu_developer',$this->sid,__ACTION__ ,"","edit");
            $this->assign('jumpUrl',$preurl);
            $this->success("恭喜您，编辑开发者成功！");
        }else
        {
            $this->assign('jumpUrl',$preurl);
           ('编辑失败，请重试！');
        }

    }
	function denyuser(){
		$model = new Model();
		$reason_list = $model -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 2 ))->select();
		foreach($reason_list as &$val){
		    if($val['content2']){
		        $val['content2'] = explode('<br />', $val['content2']);
		    }
		}
		$dev_id = escape_string($_GET['uid']);
		$count  = $model -> table("pu_user") -> where('userid ='.$dev_id.' and status = 1') -> count();
		if($count == 0){
			$this -> error("开发者不存在！！");
		}
		$userinfo = $model -> table("pu_user") -> where('userid ='.$dev_id.' and status = 1') -> select();
		$comment_cnt = $model -> table("sj_soft_comment") -> where('userid='.$dev_id.' and status =1') -> count();
		$softlist = $model -> table('sj_soft') -> where('dev_id = '.$dev_id.' and status = 1 and hide in (1,2,4,5)') -> select();
		foreach($softlist as $info){
			$soft_list[$info['hide']][] = $info;
		}

		$this -> assign('userinfo',$userinfo[0]);
		$this -> assign('comment_cnt',$comment_cnt);
		$this -> assign('userid',$dev_id);
		$this -> assign('reason_list',$reason_list);
		$this -> assign('p',$_GET['p']);
		$this -> assign('softlist',$soft_list);
		$this -> display('denyuser');
	}
	function denyuser_user_softid(){
		$model = new Model();
		$search = isset($_POST['search']) ? "softname like '%".$_POST['search']."%' and " : '';
		$dev_id = escape_string(isset($_POST['dev_id']) ? $_POST['dev_id'] : '');
		$softlist = $model -> table('sj_soft') -> where($search.'dev_id = '.$dev_id.' and status = 1 and hide in (1,2,4,5) ') -> select();
		foreach($softlist as $info){
			$soft_list[$info['hide']][] = $info;
		}
		$str = '';
		$i = 1;
		$a= 5;
		$soft_type = array('1' => '上架软件','2' => '新软件','4' => '编辑软件','5' => '升级(更新)软件');		
		$str = "<table>";
		$str .='<tr><td><label for="all"><input type="checkbox" id="all" name="all" onclick="checkall()">全选</label></td></tr>';
		foreach($soft_type as $key => $title){
		if(!$soft_list[$key]) continue; 
		$list = $soft_list[$key];
		$count = count($soft_list[$key]);
		$str .= '<tr bgColor="green"><td>'.$title.'</td><td></td><td></td><td></td><td></td></tr>';
		$i = 1;
			foreach($list as  $info){
				if($i%$a == 1) $str .='<tr>';
				$str .= '<td><label for="softid_'.$info['softid'].'"><input type="checkbox" name="softid[]" id="softid_'.$info['softid'].'" value="'.$info['softid'].'"/>'.$info['softname'].'</label></td>';
				if($i%$a == 0 || $i == $count) $str .='</tr>';
				$i++;
			} 
			$str .= "</table>";
		}
		echo $str;
	}
	function denyuser_do(){
		$userid = $_POST['userid'];
		$p = $_POST['p'];
		//echo $p;exit;
		$cmmt_userid = $_POST['cmmt_userid'] ? $_POST['cmmt_userid'] : 0;
		$softids = $_POST['softid']?escape_array($_POST['softid']):null;
		//获取屏蔽原因
		$shield_reason = '';
		if($_POST['reason']) {
			foreach($_POST['reason'] as $val) {
				$shield_reason .= $val."<br />";
			}
		}
		if($_POST['reason2'] && $_POST['reason2']!='请输入其他驳回原因：') {
			$shield_reason .= $_POST['reason2'];
		}

		$model = new Model();
		$data['status'] = 0;
		$devid = escape_string($_POST['dev_id'] ? $_POST['dev_id'] : 0);
		if(empty($devid) && empty($cmmt_userid) && empty($softids)){
			$this -> error('请选择你要进行的操作！');
		}
		$status = 0;
		if($devid){
			$affect = $model -> table('pu_user') -> where('userid = '.$devid) -> save($data);
			if($affect){
				$status = 1;
				$this -> writelog('userid 为 '.$userid.'的用户被屏蔽','pu_user',"userid:{$devid}",__ACTION__ ,"","edit");
			}
			//屏蔽信息写入 pu_developer
			$model->table('pu_developer')->where("dev_id='{$devid}'")->save(array('status'=>'-2','shield_reason'=>$shield_reason,'shield_time'=>time()));
		}
		if($cmmt_userid){
			$affect = $model -> table('sj_soft_comment') -> where(array('userid'=>$userid)) -> save($data);
			if($affect){
				$status += 2;
				$this -> writelog('userid 为 '.$userid.'的评论已经全部永久性删除','pu_user',"userid:{$devid}",__ACTION__ ,"","del");
			}
		}
		$data = array();
		$data['hide'] = 3; //软件下架
		$data['deny_msg'] = '该软件已经在开发者屏蔽管理中下架';
		$data['last_refresh'] = time();
		if(!empty($softids)){
			$softid_str = '('.implode(',',$softids).')';
			$soft_cnt = $model -> table('sj_soft') -> where('softid in '.$softid_str.' and hide = 1') ->count();
			if($soft_cnt>0){
				$affect = $model -> table('sj_soft') -> where('softid in '.$softid_str.' and hide = 1') -> save($data);
				$this -> writelog('softids 为 '.$softid_str.'的软件已经全部下架','sj_soft',$softid_str,__ACTION__ ,"","edit");
			}
			$data['hide'] = 6;
			$data['deny_msg'] = '从开发者屏蔽管理中驳回软件';
			$soft_cnt = $model -> table('sj_soft') -> where('softid in '.$softid_str.' and hide in (2,4,5)') -> count();
			if($soft_cnt > 0){
				$affect = $model -> table('sj_soft') -> where('softid in '.$softid_str.' and hide in (2,4,5)') -> save($data);
				$this -> writelog('softids 为 '.$softid_str.'的软件已经全部驳回','sj_soft',$softid_str,__ACTION__ ,"","edit");
			}
			$status += 4;
		}
		$message = '';
		switch($status){
			case 1:$message = '该用户账户已经被屏蔽';break;
			case 2:$message = '该用户的评论已经被删除';break;
			case 3:$message = '该用户的账户已经被屏蔽,评论已经被删除';break;
			case 4:$message = '该开发者的软件已经被下架';break;
			case 5:$message = '该用户账户已经被屏蔽,软件已经被下架';break;
			case 6:$message = '该用户的评论已经被删除,开发的软件已经被下架';break;
			case 7:$message = '该用户账户被屏蔽,评论被删除,软件被下架';break;
		}
		if($status){
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/User/userlists/p/{$p}");
            $this->success($message);			
		}
	}
	//恢复开发者的账户
	function permit_user(){
		$preurl = $_POST['preurl'] ? $_POST['preurl'] : str_replace("@","/",$_GET['preurl']);
		$user_id = $_GET['uid'];
		$where_user = '';
		$where_dev = '';
		if(is_numeric($user_id)) {	//单个用户
			$where_user = "userid='{$user_id}'";
			$where_dev = "dev_id='{$user_id}'";
		} else if(preg_match('/[0-9,]+/',$user_id)) {	//批量用户
			$where_user = "userid IN ({$user_id})";
			$where_dev = "dev_id IN ({$user_id})";
		} else {
			$this->assign('jumpUrl',$preurl);
			$this->error('参数错误！');
		}
		$model = new Model();
		$data['status'] = 1;
		$affect = $model->table('pu_user')->where($where_user)->save($data);

		$model->table('pu_developer')->where($where_dev)->save(array('status'=>'0'));

		$this->writelog("恢复ID：{$_GET['uid']}开发者",'pu_developer',$_GET['uid'],__ACTION__ ,"","edit");

		$this->assign('jumpUrl',$preurl);
		$this->success("该用户已经被恢复！");
	}
	//认证审核
	function approve(){
		$user_id = $_GET['uid'];
		$p = $_GET['p'];
		$this->users_db=D('Sj.Developer');
		$data['approve'] = 1;
		$affect = $this->users_db-> where(array('dev_id'=>$user_id)) ->save($data);
		if($affect){
			$this->writelog('软件开发者的ID' . $user_id . '的认证状态为已认证','pu_developer',$user_id,__ACTION__ ,"","edit");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/User/userlists/p/{$p}");
            $this->success("已认证该开发者！");				
		} else {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/User/userlists/p/{$p}");
			$this->error("认证失败！");
		}
	}
	//取消认证
	function reapprove(){
		$user_id = $_GET['uid'];
		$this->users_db=D('Sj.Developer');
		$data['approve'] = 0;
		$affect = $this->users_db-> where(array('dev_id'=>$user_id)) ->save($data);
		if($affect){
			$this->writelog('软件开发者的ID' . $user_id . '的认证状态已撤消','pu_developer',$user_id,__ACTION__ ,"","edit");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/User/userlists');
            $this->success("已取消对该开发者的认证！");				
		} else {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/User/userlists');
			$this->error("认证失败！");			
		}
	}

	//短信问题反馈
	public function problem_sms() {
		require_once(realpath(dirname(__FILE__).'/../../../../').'/GoPHP/config/config.inc.php');

		$model = new Model();
        if(empty($_GET['p'])) {
			$this->map.="s.status=1";
            if(!empty($_GET['dev_id'])) {
                $this->map.=" and s.dev_id='{$_GET['dev_id']}'";
            }
			if(!empty($_GET['username'])) {
				$this->map.=" and u.user_name like '%{$_GET['username']}%'";
			}
            if(!empty($_GET['truename'])) {
                $this->map.=" and d.truename like '%{$_GET['truename']}%'";
            }
            if(isset($_GET['type']) && $_GET['type']!=-1) {
                $this->map.=" and d.type='{$_GET['type']}'";
            }
            if(!empty($_GET['mobile'])) {
                $this->map.=" and d.mobile like '%{$_GET['mobile']}%'";
            }
            if(!empty($_GET['dev_name'])) {
                $this->map.=" and d.dev_name like '%{$_GET['dev_name']}%'";
            }
            if(!empty($_GET['cardnumber'])) {
                $this->map.=" and d.cardnumber like '%{$_GET['cardnumber']}%'";
            }
            if(!empty($_GET['charter'])) {
                $this->map.=" and d.charter like '%{$_GET['charter']}%'";
            }
            if(!empty($_GET['location'])) {
                $this->map.=" and d.location='{$_GET['location']}'";
            }
            if(!empty($_GET['email'])) {
                $this->map.=" and d.email like '%{$_GET['email']}%'";
            }
            if(!empty($_GET['begintime'])) {
				$begintime = strtotime($_GET['begintime']);
                $this->map.=" and s.quest_time>='{$begintime}'";
            }
            if(!empty($_GET['endtime'])) {
				$endtime = strtotime($_GET['endtime']);
                $this->map.=" and s.quest_time<='{$endtime}'";
            }
            $_SESSION['admin']['userlist']['where']=$this->map;
        }else
        {
            $this->map=$_SESSION['admin']['userlist']['where'];
        }
        //dump($this->map);
        $this->users_db=D('Sj.Developer');
        import("@.ORG.Page");
        $count= $model->Table('pu_problem_sms s LEFT JOIN pu_developer d ON s.dev_id=d.dev_id LEFT JOIN pu_user u ON s.dev_id=u.userid')->where($this->map)->count();
        $Page=new Page($count,15);
        $this->userslist=$model->Table('pu_problem_sms s LEFT JOIN pu_developer d ON s.dev_id=d.dev_id LEFT JOIN pu_user u ON s.dev_id=u.userid')->where($this->map)->field('s.*,d.dev_name,d.truename,d.site,d.introduction,d.zipcode,d.address,d.phone,d.im_id,d.im_type,d.email,d.email_verified,d.email_time,d.mobile,d.mobile_verified,d.mobile_time,d.company,d.type,d.fax,d.cardtype,d.cardnumber,d.cardpic,d.charter,d.charterpic,d.message,d.approve,d.old_start,d.register_time,d.pass_time,d.verify_email,d.verify_mobile,d.verify_time,d.dismissed,d.shield_reason,d.shield_time,d.active_email,d.active_email_sendtime,d.location,d.complete_time,d.last_time,d.dismissed_time,u.userid,u.user_name')->order('s.quest_time asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$devid_arr = array();
		if($this->userslist) {
			foreach($this->userslist as $key=>$val) {
				$devid_arr[] = $val['id'];		//身份证
				$devid_arr[] = 'a'.$val['id'];	//营业执照

				$this->userslist[$key]['email_verified_str'] = $val['email_verified'] ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				$this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified'] ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				if($val['type']==0) {
					$this->userslist[$key]['type_str'] = '公司';
				} else if($val['type']==1) {
					$this->userslist[$key]['type_str'] = '个人';
				} else if($val['type']==2) {
					$this->userslist[$key]['type_str'] = '团队';
				}
				$this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST.$val['cardpic'] : '';
				$this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST.$val['charterpic'] : '';
				if($val['im_type']==1) {
					$this->userslist[$key]['im_type_str'] = 'QQ';
				} else if($val['im_type']==2) {
					$this->userslist[$key]['im_type_str'] = 'Gtalk';
				} else if($val['im_type']==3) {
					$this->userslist[$key]['im_type_str'] = 'Msn';
				} else if($val['im_type']==4) {
					$this->userslist[$key]['im_type_str'] = 'Skype';
				}

				if($val['location']==1) {
					$this->userslist[$key]['location_str'] = '中国大陆';
				} else if($val['location']==2) {
					$this->userslist[$key]['location_str'] = '港澳台和国外';
				}

				$this->userslist[$key]['register_time'] = date('Y-m-d H:i:s',$val['register_time']);
				$this->userslist[$key]['quest_time'] = date('Y-m-d H:i:s',$val['quest_time']);
			}
		}
		$devid_str = "'".implode("','",$devid_arr)."'";
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ( "page", $show );
		$this->assign ("list_type", $list_type);
        $this->assign('userslist',$this->userslist);
		$this->assign('devid_str',$devid_str);
        $this->display();
	}

	//短信问题反馈_删除
	public function problem_sms_del() {
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/User/problem_sms');
			$this->error("参数错误！");
		}
		$id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
		if(!$id_arr) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/User/problem_sms');
			$this->error("参数错误！(1)");
		}
		$id_str = implode(',',$id_arr);

		$model = new Model();
		$data = array(
			'status' => 0,
		);
		$affect = $model->table('pu_problem_sms')->where("id IN ({$id_str})")->save($data);
		if($affect) {
			$this->writelog("删除了id为".$id_str."的短信反馈问题",'pu_problem_sms',$id_str,__ACTION__ ,"","del");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/User/problem_sms');
			$this->success("删除成功！");
		} else {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/User/problem_sms');
			$this->error("数据更新失败，请重试！");
		}
	}
	//全局搜索页面显示所有开发者
	    //开发者管理_待审核开发者列表
    public function developer_list() {
		$model = new Model();
		require_once(realpath(dirname(__FILE__).'/../../../../').'/GoPHP/config/config.inc.php');
        if(empty($_GET['p'])) {
            $this->map="p.email_verified = 1";
			if(!empty($_GET['username'])){
				//获取userid
				$userid = $model -> table("pu_user")->where(array("user_name" => $_GET['username']))->getfield("userid");
				$this->map.=" and p.dev_id='{$userid}'";
 			}else{
				if(!empty($_GET['dev_id'])) {
					$this->map.=" and p.dev_id='{$_GET['dev_id']}'";
				}
				if(!empty($_GET['truename'])) {
					$this->map.=" and p.truename like '%{$_GET['truename']}%'";
				}
				if(isset($_GET['type']) && $_GET['type']!=-1) {
					$this->map.=" and p.type='{$_GET['type']}'";
				}
				if(!empty($_GET['mobile'])) {
					$this->map.=" and p.mobile like '%{$_GET['mobile']}%'";
				}
				if(!empty($_GET['dev_name'])) {
					$this->map.=" and p.dev_name like '%{$_GET['dev_name']}%'";
				}
				if(!empty($_GET['cardnumber'])) {
					$this->map.=" and p.cardnumber like '%{$_GET['cardnumber']}%'";
				}
				if(!empty($_GET['charter'])) {
					$this->map.=" and p.charter like '%{$_GET['charter']}%'";
				}
				if(!empty($_GET['location'])) {
					$this->map.=" and p.location='{$_GET['location']}'";
				}
				if(!empty($_GET['email'])) {
					$this->map.=" and p.email like '%{$_GET['email']}%'";
				}
				if(!empty($_GET['begintime'])) {
					$begintime = strtotime($_GET['begintime']);
					$this->map.=" and p.register_time>='{$begintime}'";
				}
				if(!empty($_GET['endtime'])) {
					$endtime = strtotime($_GET['endtime']);
					$this->map.=" and p.register_time<='{$endtime}'";
				}	
			}
            $_SESSION['admin']['userlist']['where']=$this->map;
        }else
        {
            $this->map=$_SESSION['admin']['userlist']['where'];
        }
        //dump($this->map);
        $this->users_db=D('Sj.Developer');
        import("@.ORG.Page");
        $count= $model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->count();
        $Page=new Page($count,10);
        $this->userslist=$model->table('pu_developer p LEFT JOIN pu_user u ON p.dev_id=u.userid')->where($this->map)->field('p.*,u.user_name')->order('p.register_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$devid_arr = array();
		if($this->userslist) {
			foreach($this->userslist as $key=>$val) {
				$devid_arr[] = $val['dev_id'];		//身份证
				$devid_arr[] = 'a'.$val['dev_id'];	//营业执照
				if($val['email']) {
					$this->userslist[$key]['email_verified_str'] = $val['email_verified'] ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['mobile']) {
					$this->userslist[$key]['mobile_verified_str'] = $val['mobile_verified'] ? '<span style="color:red;">[已验证]</span>' : '[未验证]';
				}
				if($val['type']==0) {
					$this->userslist[$key]['type_str'] = '公司';
				} else if($val['type']==1) {
					$this->userslist[$key]['type_str'] = '个人';
				} else if($val['type']==2) {
					$this->userslist[$key]['type_str'] = '团队';
				}
				$this->userslist[$key]['cardpic'] = $val['cardpic'] ? IMGATT_HOST.$val['cardpic'] : '';
				$this->userslist[$key]['charterpic'] = $val['charterpic'] ? IMGATT_HOST.$val['charterpic'] : '';
				if($val['im_type']==1) {
					$this->userslist[$key]['im_type_str'] = 'QQ';
				} else if($val['im_type']==2) {
					$this->userslist[$key]['im_type_str'] = 'Gtalk';
				} else if($val['im_type']==3) {
					$this->userslist[$key]['im_type_str'] = 'Msn';
				} else if($val['im_type']==4) {
					$this->userslist[$key]['im_type_str'] = 'Skype';
				}
				$this->userslist[$key]['register_time'] = date('Y-m-d H:i:s',$val['register_time']);

				if($val['location']==1) {
					$this->userslist[$key]['location_str'] = '中国大陆';
				} else if($val['location']==2) {
					$this->userslist[$key]['location_str'] = '港澳台和国外';
				}
			}
		}
		$devid_str = "'".implode("','",$devid_arr)."'";
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show);
		$this->assign ("list_type", $list_type);
        $this->assign('userslist',$this->userslist);
		$this->assign('devid_str',$devid_str);
        $this->display();
    }
}
?>