<?php
/**
 * 安智网产品管理平台 公用控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.4.21
 * ----------------------------------------------------------------------------
 */
class PublicAction extends Action {
	// 检查用户是否登录
	private $adminlist; //管理员用户列
	private $adminusers;//管理员表
	private $hashs;     //hash值
	private $conf;      //配置表
	private $map;       //存储条件
	private $lists;     //常用存储列表
	private $password;
	private $titles;     //存储判断字段
	private $admingroup_db; //管理员权限分组表
	private $conf_db;
	private $pid;

	protected function checkUser() {
		if(!isset($_SESSION['admin']['admin_id'])) {
			session_destroy();
			$this->redirect('Public/login','' , 1,'对不起,登陆超时,页面跳转中~');
		}
	}

	// 顶部页面
	public function top() {
		C('SHOW_RUN_TIME',false);			// 运行时间显示
		C('SHOW_PAGE_TRACE',false);
		$this -> checkUser();
		//$model	=	M("Group");
		//$list	=	$model->where('status=1')->getField('id,title');
		//$this->assign('nodeGroupList',$list);
		$this->display('Public:top');
	}
	// 尾部页面
	public function footer() {
		C('SHOW_RUN_TIME',false);			// 运行时间显示
		C('SHOW_PAGE_TRACE',false);
		$this->display('Public:footer');
	}
	// 菜单页面
	public function menu() {

		if(!isset($_SESSION['admin']['admin_id'])) {
			/*session_destroy();
			$this->redirect('Public/login','' , 1,'对不起,登陆超时,页面跳转中~');*/

            if(empty($_SESSION['admin']['admin_id'])) {
                if(!checkCookieAdmin()){
                    $this->redirect('Public/login','' , 1,'对不起,登陆超时,页面跳转中~');
                }
            }
		}
		//$this->checkUser();
		/*
		 $this->titles=$_GET['title'];
		 if($this->titles=='phone') {
		 $this->display('Sj:Public:menu');
		 }else if($this->titles=='main') {
		 $this->display('Admin:Public:menu');
		 }else
		 {
		 echo '系统错误';exit;
		 }
		 */
		$this->getMenu();
	}


	// 后台首页 查看系统信息
	public function main() {

		$this->titles=$_GET['title'];

		if(empty($_GET['title'])) {
			$this->titles='phone';
		}
		if($this->titles=='phone') {

			echo '白天不努力，晚上就加班！<br />本平台推荐使用的浏览器为：chrome、ie9、ie10';

		}else if($this->titles=='main') {
			$info = array(
				'操作系统'=>PHP_OS,
				'运行环境'=>$_SERVER["SERVER_SOFTWARE"],
				'PHP运行方式'=>php_sapi_name(),
				'上传附件限制'=>ini_get('upload_max_filesize'),
				'执行时间限制'=>ini_get('max_execution_time').'秒',
				'服务器时间'=>date("Y年n月j日 H:i:s"),
				'北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
				'服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
				'剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
				'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
				'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
				'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
			);

			//$this->assign('info',$info);
			$this->display('Admin:Public:main');
		}else
		{
			echo '白天不努力，晚上就加班！<br />	本平台推荐使用的浏览器为：chrome、ie9、ie10';exit;

		}

		//echo '请您用心操作！';
	}

	// 用户登录页面
	public function login() {
		if(!isset($_SESSION['admin']['admin_id'])) {
			$login_qa = M('admin_login_qa');
			$question = $login_qa->select();
			if($question) {
				foreach($question as $k=>$v) {
					if(!$v['question']) unset($question[$k]);
				}
			}
			$vc_type = C('VC_TYPE');
			if(empty($vc_type)) {
				$vc_type = 1;
			}
			$this->assign('vc_type',$vc_type);
			$this->assign('question',$question);
			$this->display('Public:login');
		}else{
			$this->redirect('Index/index');
		}
	}

	public function index()
	{
		//如果通过认证跳转到首页
		redirect(__APP__);
	}

	// 用户登出
	public function logout()
	{
		session_destroy();
        cookie('admin', null);
        cookie('AZEUI', null);
		$this->assign("jumpUrl",__URL__.'/login/');
		$this->success('退出成功！');
	}
	public function drag() {
		C('SHOW_RUN_TIME',false);			// 运行时间显示
		C('SHOW_PAGE_TRACE',false);
		$this->display('Public:drag');
	}

	// 登录检测
	public function checkLogin() {
		
		
		
		
		
		
		
		if(empty($_POST['account'])) {
			$this->error('帐号错误！');
		}elseif (empty($_POST['password'])){
			$this->error('密码必须！');
		}elseif (!$_POST['question']){
			$this->error('问题必须选择！');
		}elseif($_POST['question']==-1 && empty($_POST['self_question'])) {
			$this->error('请填写自填问题！');
		}elseif (empty($_POST['answer'])){
			$this->error('回答必须！');
		
		}
		/*
		elseif (empty($_POST['verify'])){
			$this->error('验证码必须！');
		}
		elseif (empty($_POST['az_code'])){
			$this->error('验证码必须！');
		}*/

		
		
		
		
		
		
		//生成认证条件
		$map            =   array();
		// 支持使用绑定帐号登录
		
		$vc_type = C('VC_TYPE');
		if(empty($vc_type)) {
			$vc_type = 1;
		}
		
		
		
		//数字模式
		if($vc_type == 1) {
			//验证验证码是否正确
			if($_SESSION['verify'] != md5($_POST['verify'])) {
			$this->error('验证码错误！');
		}

		//汉字模式
		} else if($vc_type == 2) {

			$tmp_data = file_get_contents('http://'.C('VC_SERVER').'/check?data='.urlencode($_POST['az_code']));
			$tmp_data = json_decode($tmp_data,true);
			if(empty($tmp_data['data']['allows'])) {
				//$this->error('验证码错误');
			}
		
		}
		
		

		
		
		
		
		
		
		$this->adminusers = M('admin_users');
		$this->conf = D('Sj.Config');
		$this->adminlist['admin_user_name']=strip_tags(trim(htmlspecialchars($_POST['account'])));
		$plain_password = $this->adminlist['admin_user_password']=strip_tags(trim(htmlspecialchars($_POST['password'])));
		$this->adminlist['admin_state']=1;

		$this->hashs=$this->conf->where("configname='hash' and  status=1")->getField('configcontent');
		$this->adminlist['admin_user_password']=md5($this->adminlist['admin_user_password'].$this->hashs);
		//        var_dump($this->adminlist['admin_user_password']);exit;
		$this->lists=$this->adminusers->where($this->adminlist)->field('admin_user_id,admin_user_name,admin_group,question,answer,self_question,last_pwd_tm')->select();
		$login_model = M('admin_login_logs');
		$login_data = array(
			'add_time' => time(),
			'admin_name' => $_POST['account'],
			'ip' => $_SERVER['REMOTE_ADDR'],
		);
		if($this->lists)
		{
			$this->admingroup_db=M('admin_group');
			$admingroupstatus='';
			$admingroupstatus=$this->admingroup_db->where('group_id='.$this->lists[0]['admin_group'].'')->getfield('status');
			if($admingroupstatus!=1) {
				$login_data['flag'] = 2;
				$login_model->add($login_data);
				$this->assign("jumpUrl",SITE_URL.'/index.php');
				$this->error("登陆失败，账号不存在或账号、密码错误！");
			}
			//安全问题检查,开始
			$qa_verify = TRUE;
			if($this->lists[0]['question']==-1) {
				if($_POST['question']!=-1 || $_POST['self_question']!=$this->lists[0]['self_question'] || $_POST['answer']!=$this->lists[0]['answer']) {
					$qa_verify = FALSE;
				}
			} else {
				if($_POST['question']!=$this->lists[0]['question'] || $_POST['answer']!=$this->lists[0]['answer']) {
					$qa_verify = FALSE;
				}
			}
			if(!$qa_verify) {
				$this->assign("jumpUrl",SITE_URL.'/index.php');
				$this->error("登陆失败，问题或回答错误！");
			}
			//安全问题检查,结束
			$_SESSION['admin']['admin_id']=$this->lists[0]['admin_user_id'];
			$_SESSION['admin']['admin_user_name']=$this->lists[0]['admin_user_name'];
			$_SESSION['admin']['admin_group']=$this->lists[0]['admin_group'];
			$this->map['last_logintime']=time();
			$this->map['last_ip']=get_client_ip();
			$this->map['sessionid']=session_id();
			$this->map['plain_password']=encrypt($plain_password);
			$this->adminusers->setInc('admin_visits',"admin_user_id='".$this->lists[0]['admin_user_id']."'",1); // 登陆次数+1
			$this->adminusers->where("admin_user_id='".$this->lists[0]['admin_user_id']."'")->save($this->map);

			$role=M('');
			//TODO 特殊权限，用户权限都保存在这个表
			$viplevellist=$role->table('sj_admin_role A,sj_admin_node B')->where('A.admin_id="'.$_SESSION['admin']['admin_id'].'" AND A.node_id=B.node_id')->field('B.nodename')->select();
			foreach($viplevellist as $key=>$value)
			{
				$theviplevelarray[$key]=$value['nodename'];
			}
			$_SESSION['admin']['popedom']=$theviplevelarray;
			$_SESSION['admin']['loginip']=$_SERVER['REMOTE_ADDR'];
			$_SESSION['admin']['ua']=$_SERVER['HTTP_USER_AGENT'];
			//TODO 组权限，默认用户组权限
			/*
			 $viplevellist=$role->table('sj_admin_group_note A,sj_admin_node B')->where('A.group_id="'.$_SESSION['admin']['admin_group'].'" AND A.node_id=B.node_id')->field('B.nodename')->select();
			 foreach($viplevellist as $key=>$value)
			 {
			 $_SESSION['admin']['popedom'][]=$value['nodename'];
			 }
			 */

            setCookieAdmin();

			$login_data['flag'] = 1;
			$login_data['admin_id'] = $_SESSION['admin']['admin_id'];
			$login_model->add($login_data);
            $this->assign("jumpUrl",SITE_URL.'/index.php');

			if(C('30_edit_user_password') && ((time()-$this->lists[0]['last_pwd_tm']) > 30*24*60*60 )){
				if(C('type_30')) $_SESSION['admin']['type_30'] = 1; //强制修改密码
			//$this->assign("jumpUrl",SITE_URL.'/index.php/Public/password/type_30/1');
			//$this->error("超过30天没有修改密码，请修改密码！");
				$this->redirect('/Public/password/type_30/1');
			}else{
				$this->success("登陆成功！");
			}
		}else
		{
			$login_data['flag'] = 0;
			$login_model->add($login_data);
			$this->assign("jumpUrl",SITE_URL.'/index.php');
			$this->error("登陆失败，账号不存在或账号、密码错误！");
		}
	}
	//更换密码
	public function password() {
		$this->checkUser();
		$this->display('Public:password');
	}
	// 更换密码
	public function changePwd()
	{
		$time = time();
		//对表单提交处理进行处理或者增加非表单数据
		if(md5($_POST['verify'])	!= $_SESSION['verify']){
			$this->error('验证码错误！');
		}
		if($_POST['password']!=$_POST['repassword']){
			$this->error('两次密码不相符');
		}
		$map = array();
		$this->conf = D('Sj.Config');
		$this->hashs=$this->conf->where("configname='hash' and status=1")->getField('configcontent');
		$map['admin_user_password']= md5($_POST['oldpassword'].$this->hashs);
		$this->password['admin_user_password']= md5($_POST['password'].$this->hashs);

		//检查用户
		$User    =   M("admin_users");
		if(!$User->where($map)->field('admin_user_id')->find()) {
			$this->assign("jumpUrl",SITE_URL.'/index.php/Public/password');
			$this->error('旧密码不符！');
		}else {
			unset($_SESSION['admin']['type_30']);
			$map['admin_user_password']	=md5($_POST['password'].$this->hashs);
			$map['plain_password'] = encrypt($_POST['password']);
			$map['update_time'] = $time;
			$map['last_pwd_tm'] = $time;
			$User->where('admin_user_id="'.$_SESSION['admin']['admin_id'].'"')->save($map);
			//$this->writelog('用户ID为['.$_SESSION['admin']['admin_id'].']用户修改密码');
			$this->assign("jumpUrl",SITE_URL.'/index.php/Public/main');
			$this->success('密码修改成功！');
		}
	}
	//登录问答
	public function login_qa() {
		$login_qa = M('admin_login_qa');
		$question = $login_qa->where()->select();
		if($question) {
			foreach($question as $k=>$v) {
				if($v['question']=='') unset($question[$k]);
			}
		}
		$admin_user = M('admin_users');
		$qa = $admin_user->where("admin_user_id='{$_SESSION['admin']['admin_id']}'")->field('question,answer')->select();
		$this->assign('qa',$qa['0']);
		$this->assign('question',$question);
		$this->checkUser();
		$this->display('Public:login_qa');
	}
	//修改登录问答
	public function changeLoginQa() {
		if(!$_POST['_login_password_qa']) {
			$this->assign("jumpUrl",'/index.php/Public/login_qa/');
			$this->error('密码不能为空，请填写！');
		}
		if(!$_POST['question']) {
			$this->assign("jumpUrl",'/index.php/Public/login_qa/');
			$this->error('请选择登录问题！');
		}
		if($_POST['question']==-1 && !$_POST['self_question']) {
			$this->assign("jumpUrl",'/index.php/Public/login_qa/');
			$this->error('请选择自填问题！');
		}
		if(!$_POST['answer']) {
			$this->assign("jumpUrl",'/index.php/Public/login_qa/');
			$this->error('请填写登录回答！');
		}
		if(!$_POST['verify']) {
			$this->assign("jumpUrl",'/index.php/Public/login_qa/');
			$this->error('验证码不能为空，请填写！');
		}

		//登录管理员密码验证,开始
		$this->conf = D('Sj.Config');
		$this->hashs=$this->conf->where("configname='hash' and status=1")->getField('configcontent');
		$map = array();
		$map['admin_user_id'] = $_SESSION['admin']['admin_id'];
		$map['admin_user_password'] = md5($_POST['_login_password_qa'].$this->hashs);
		//检查用户密码
		$User = M("admin_users");
		$tmp = $User->where($map)->field('question,answer,self_question')->select();
		if(!$tmp) {
			//写日志
			$map = array();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$map['action_id'] = 10000;
			$map['actionexp'] = "修改登录问答时，密码验证失败";
			$map['logtime'] = time();
			$map['fromip'] = $_SERVER['REMOTE_ADDR'];
			$map['log_key'] = $map['action_id'];
			$admin_log_db=M('admin_log');
			$admin_log_db->add($map);

			$this->assign("jumpUrl",'/index.php/Public/login_qa/');
			$this->error('密码验证失败，操作失败！');
		}
		//登录管理员密码验证,结束
		if(md5($_POST['verify']) != $_SESSION['verify']) {
			$this->assign('jumpUrl','/index.php/Public/login_qa/');
			$this->error('验证码错误！');
		}
		//原登录问答验证
		$old_qa_verify = TRUE;
		$tmp = $tmp[0];
		if($tmp['question']==-1) {	//自填问题
			if($_POST['question']!=-1 || $tmp['self_question']!=$_POST['self_question'] || $tmp['answer']!=$_POST['answer']) {
				$old_qa_verify = FALSE;
			}
		} else {
			if($tmp['question']!=$_POST['question'] || $tmp['answer']!=$_POST['answer']) {
				$old_qa_verify = FALSE;
			}
		}
		if(!$old_qa_verify) {
			//写日志
			$map = array();
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
			$map['action_id'] = 10000;
			$map['actionexp'] = "修改登录问答时，原登录问答验证失败";
			$map['logtime'] = time();
			$map['fromip'] = $_SERVER['REMOTE_ADDR'];
			$map['log_key'] = $map['action_id'];
			$admin_log_db=M('admin_log');
			$admin_log_db->add($map);

			$this->assign("jumpUrl",'/index.php/Public/login_qa/');
			$this->error('原登录问答验证失败，操作失败！');
		}

		//更新新的登录问答
		$admin_user = M('admin_users');
		if($_POST['new_question']!=-1) $_POST['new_self_question'] = '';
		$admin_user->where("admin_user_id='{$_SESSION['admin']['admin_id']}'")->save(array('question'=>$_POST['new_question'],'answer'=>$_POST['new_answer'],'self_question'=>$_POST['new_self_question']));
		$this->assign('jumpUrl',SITE_URL.'/index.php/Public/main');
		$this->success('恭喜您，登录问答修改成功！');
	}
	public function profile() {
		$this->checkUser();
		$User	 =	 M("User");
		$vo	=	$User->getById($_SESSION[C('USER_AUTH_KEY')]);
		$this->assign('vo',$vo);
		$this->display('Public:profile');
	}
	public function verify()
	{
		$type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
		ini_set('display_errors',true);
		error_reporting(E_ALL);
		import("@.ORG.Image");
		Image::buildImageVerify(4,1,$type);
	}
	//临时数据soft_map迁移-辅助查询
	public function findsoft($oldid) {

		$db=D('Softmap');
		$map['old']=$oldid;
		$newid=$db->table('market.softid_map')->where($map)->getField('new');
		//dump($newid);
		return $newid;
		// if(empty($newid)) {
		//    return $oldid;
		// }
		// return $newid;
	}
	//递归
	public function digui($tid) {

		$id=$this->findsoft($tid);

		if(empty($id)) {

			return $tid;
		}else
		{   //dump($id);
			return $this->digui($id);
		}
		//return $tid;
	}
	//迁移
	public function testrun() {
		$olddb=D('Softmap');
		$newdb=M('soft');
		$idlist=$olddb->table('market.softid_map')->field('old')->select();
		//dump($idlist);
		$countnum=count($idlist);
		for($i=0;$i<$countnum;$i++) {
			$idarr['old'][]=$idlist[$i]['old'];
			$newid=$this->digui($idlist[$i]['old']);
			$idarr['new'][]=$newid;
			$map['softid']=$newid;
			$thumblist=$olddb->table('market.soft')->where($map)->select();
			if(empty($thumblist)) {
				$thumblist=$olddb->table('market.soft_history')->where($map)->select();
			}
			if(empty($thumblist)) {
				continue;
			}
			$newsoftlist=$newdb->where($map)->count();
			if(empty($newsoftlist)) {
				continue;
			}
			$map='';
			$map['softid']=$idlist[$i]['old'];
			$newsoftlist=$newdb->where($map)->count();
			if(!empty($newsoftlist)) {
				continue;
			}

			$softlist['softid']=$idlist[$i]['old'];
			$softlist['softname']=$thumblist[0]['name'];
			$softlist['package']=$thumblist[0]['package'];
			$softlist['dev_id']=$thumblist[0]['owner_id'];
			$softlist['dev_name']=$thumblist[0]['author'];
			$softlist['dever_email']=$thumblist[0]['dever_email'];


			$softlist['dever_page']=$thumblist[0]['dever_page'];
			$softlist['category_id']=$thumblist[0]['catalogid'];
			$softlist['costs']=$thumblist[0]['cost'];
			$softlist['version']=$thumblist[0]['version'];
			$softlist['version_code']=$thumblist[0]['version_code'];
			$softlist['intro']=$thumblist[0]['intro'];
			$softlist['downloaded']=$thumblist[0]['downloaded'];
			$softlist['score']=$thumblist[0]['score'];


			$softlist['msgnum']=$thumblist[0]['comments_cnt'];
			$softlist['upload_tm']=strtotime($thumblist[0]['upload_tm']);


			$softlist['last_refresh']=strtotime($thumblist[0]['last_refresh'])?strtotime($thumblist[0]['last_refresh']):0;
			$softlist['hide']=0;
			$softlist['status']=0;
			$softlist['claim_status']=$thumblist[0]['claim_status'];
			$softlist['tags']=$thumblist[0]['tags'];

			if(false==$newdb->add($softlist)) {
				$errorarr[]=$idlist[$i]['old'];
			}


		}
		//$aaa=$this->digui('7764');
		dump($errorarr);
		//$idarr=array_unique($idarr);
		$this->display('Public::header');
	}



	public function getoldsofthash() {
		$filedb=M('soft_file');
		$safedb=M('soft_file_safe');

		$filelist=$filedb->where('package_status=1')->field('id,url')->select();
		$num=count($filelist);
		for($i=0;$i<$num;$i++) {

			$fileurl=UPLOAD_PATH.$filelist[$i]['url'];
			if(is_file($fileurl)) {
				$soft_hash=$this->getSoftHash($fileurl);
				$map['id']=$filelist[$i]['id'];
				$map['hash']=$soft_hash;
				$map['safetype']=0;
				$safedb->add($map);
			}
		}
		$this->display('Public::header');


	}
	public function checkTopFilterPackage()
	{
		$model = M('top_filter');
		$map = array(
			'package' => $_GET['package'],
			'status' => 1,
		);
		$res = $model->where($map)->select();
		if ($res) {
			exit('false');
		} else {
			exit('true');
		}
	}



	public function getSoftHash($soft_file,$hash="sha1"){
		if(!is_file($soft_file)) return false;

		$re = hash_file($hash,$soft_file);
		return $re;
	}

	public function getMenu()
	{
		$menu_model = D('Menu');
		$result = $menu_model->getMenu($_SESSION['admin']['admin_id']);
		$t = isset($_GET['type']) ? $_GET['type'] : 'Sj' ;
		$t = ucfirst($t);
		
		foreach($result[$t] as $key => $val){
			if (!empty($val['platform']) && $val['platform'] != $t) {
				//若指定标签，则只在指定标签显示
				unset($result[$t][$key]);
			}
		}
        
		$this->assign('groups', $result[$t]);
		$this->display('Public::menu');
	}

	public function menuManager()
	{
		# code...
		$this->display('Public::menuManager');

	}

	public function getPlatform($value='')
	{
		$platform = array(
			'sj',
			'admin',
			'partner',
			'webmarket',
			'school',
			'coop',
			'caiji',
			'Sendnum',
			'dev',
			'Appquality',
			'Channel_cooperation',
			'Ad_operation',
			'Ad_cooperation',
			'Bbs_manage',
		);
		exit(json_encode($platform));
	}

	public function getGroup($value='')
	{
		$menu_model = D('Menu');
		$group = $menu_model->table('sj_admin_node_group')->select();
		exit(json_encode($group));
	}

	public function getSubMenu($value='')
	{
		$menu_model = D('Menu');
		$result = $menu_model->getMenu();
		$t = isset($_GET['type']) ? $_GET['type'] : 'Sj' ;
		$t = ucfirst($t);
		foreach($result[$t] as $key => $val){
			if (!empty($val['platform']) && $val['platform'] != $t) {
				//若指定标签，则只在指定标签显示
				unset($result[$t][$key]);
			}
		}
		$r = array();
		foreach ($result[$t] as $val) {
			# code...
			$r[] = $val;
		}
		//unset($r[2]);
		exit(json_encode($r));
	}

	public function saveMenu()
	{
		$action = $_POST['action'];
		$model = D('Menu');
		if ($action == 'move_group') {
			$des_platform = ucfirst($_POST['des_platform']);
			$src_group = implode(',', $_POST['src_group']);
			if ($src_group) {
				$sql = "update sj_admin_node_group set platform='{$des_platform}' where group_id in({$src_group});";
			}
		} elseif ($action == 'move_node') {
			$des_group = $_POST['des_group'];
			$src_node = implode(',', $_POST['src_node']);
			if ($src_node) {
				$sql = "update sj_admin_note_group set group_id='{$des_group}' where id in({$src_node});";
			}
		} elseif ($action == 'save_node') {
			$node_id = $_POST['node_id'];
			$postil = $_POST['node_name'];
			if ($postil) {
				$sql = "update sj_admin_node set postil='{$postil}' where node_id={$node_id};";
			}
		} elseif ($action == 'save_group') {
			$group_id = $_POST['group_id'];
			$group_name = $_POST['group_name'];
			if ($group_name) {
				$sql = "update sj_admin_node_group set group_name='{$group_name}' where group_id={$group_id};";
			}
		}
		if ($sql) {
			$res = $model->query($sql);
			echo $sql;
			file_put_contents('/media/sf_F_DRIVE/aaaa.sql', $sql."\n", FILE_APPEND);
		}
	}

	public function testNoti()
	{
		$data = array(
			'exec' => 'php',
			'file' => 'refresh_lack.php',
			'params' => array(30040),
		);
		sendNotification($data);
	}
    
    public function showVersion() {
        $rs = strpos(serialize($_GET),'random_isthickbox');
        if($rs>0){
            unset($_SERVER['HTTP_X_REQUESTED_WITH']);
            $this->assign('isbox',1);
        }
        // append_id
        $append_id = trim($_GET['append_id']);
        if (!$append_id) {
            $this->error("请传递append_id参数(选择版本号后需要append到的位置)");
        }
        // 获得所有版本号
		//sdk的版本号和市场的版本号不一样
		if($_GET['from']&&$_GET['from']=='sdk')
		{
			$this->assign('from', $_GET['from']);
			$sdk_version_db = json_decode(file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/index.php/Interface/sdk_list'),true);
			$this -> assign('sdk_version_db',$sdk_version_db['list']);
		}
		else
		{
			$util = D('Sj.Util');
			$all_version_list = $util->getMarketVersion(explode(',', $sj_market_push_one['version_code']));
			
			// 将all_version_list按键值倒序排一下，然后把最大的版本unset掉
			krsort($all_version_list);
			foreach ($all_version_list as $version => $value) {
				unset($all_version_list[$version]);
				break;
			}
			// 组成 5.X版本=>5400,5300, 4.X版本=>4400,4410格式
			$version_group = array();
			foreach ($all_version_list as $version => $value) {
				$before_dot_number = floor($version/1000);
				if (!in_array($version, $version_group[$before_dot_number])) {
					$version_group[$before_dot_number][] = $version;
				}
			}
			krsort($version_group);
			foreach ($version_group as $key => $version_list) {
				rsort($version_list);
				$version_group[$key] = $version_list;
			}
			$this->assign('version_group', $version_group);
		}
        $this->assign('append_id', $append_id);
        $this->display("Public::version");
    }
	//V6.0精准投放 公用展示部分
	public function showContions()
	{
		/**
        *获取机型、固件版本、运营商列表
		**/
		$util = D('Sj.Util');
        $this->assign('firmwarelist', $util->getFirmwareList());
        $this->assign('version_list', $util->getMarketVersion());
        $this->assign('operator_list', $util->getOperators());
        $this->assign('abilist', $util->getAbiList());
		$this->display('Public::public_condition');
	}
	//V6.5桌面红包公用模板
	public function showDeskRed()
	{
		$model = M();
		//V6.5新增加   桌面红包弹窗、高低分图片
		$desk_red_high_width=106;
		$desk_red_high_height=106;
		$desk_red_low_width=60;
		$desk_red_low_height=60;
		$desk_red_pop_width=80;
		$desk_red_pop_height=80;
		
		$desk_red_des_title_limit=20;//20个汉字 40个字符
		$desk_red_des_subtitle_limit=14;
		$red_result_pop_des_limit=20;
		if($_GET['id'])
		{
			$id = $_GET['id'];
			$table = $_GET['table'];
			$where_arr = array(
				'id' => $id,
				'status' => 1,
			);
			$red_find_result = $model->table($table)->where($where_arr)->find();
			$red_detail_arr = json_decode($red_find_result['desk_red_text'],true);
			//获取红包相关信息
			if( $red_detail_arr['red_type'] == 2 ) {
				$task_list = D('Sj.RedActivity')->get_red_soft_list(1,$red_detail_arr['red_soft_pkg'],$red_detail_arr['task_id']);
			}
			if( $red_detail_arr['red_id'] ) {
				$red_info = D('Sj.RedActivity')->get_red_package_info($red_detail_arr['red_id']);
				$red_package = array(
						'id'		=>	$red_info[0]['id'],
						'pname'		=>	$red_info[0]['pname'],
						'totalmon'	=>	$red_info[0]['totalmon'],
						'totalnum'	=>	$red_info[0]['totalnum'],
						'givetype'	=>	$red_info[0]['givetype'],
						'minrand'	=>	$red_info[0]['minrand'],
						'maxrand'	=>	$red_info[0]['maxrand'],
				);
			}
			$this->assign('task_list', $task_list);
			$this->assign('red_package', $red_package);
			$this->assign('red_detail_arr',$red_detail_arr);
		}
		
		//获取奖励类型是红包的情况
		$red_package_list	=	D('Sj.RedActivity')->get_red_pagckage_list(1);
		$this->assign('red_package_list', $red_package_list);
		
		//V6.5桌面红包图片
		$this->assign('desk_red_high_width',$desk_red_high_width);
		$this->assign('desk_red_high_height',$desk_red_high_height);
		$this->assign('desk_red_low_width',$desk_red_low_width);
		$this->assign('desk_red_low_height',$desk_red_low_height);
		$this->assign('desk_red_pop_width',$desk_red_pop_width);
		$this->assign('desk_red_pop_height',$desk_red_pop_height);
		
		$this->assign('desk_red_des_title_limit',$desk_red_des_title_limit);
		$this->assign('desk_red_des_subtitle_limit',$desk_red_des_subtitle_limit);
		$this->assign('red_result_pop_des_limit',$red_result_pop_des_limit);
		
		$this->display('Public::desk_red');
	}
	
	//获取红包任务
	function pub_red_soft_list()
	{
		$pkg	=	trim($_REQUEST['pkg']);
		//获取月份
		$task_list = D('Sj.RedActivity')->get_red_soft_list(1,$pkg);
		if( !empty($task_list) ) {
			$res = array(
					'code'	=>	1,
					'data'	=> $task_list,
			);
			exit(json_encode($res));
		}else {
			$res = array(
					'code'	=>	0,
			);
			exit(json_encode($res));
		}
	}
	
	//获取红包详情
	function pub_red_info() 
	{
		$red_type	=	(Int)$_REQUEST['red_type'];
		$pkg		=	trim($_REQUEST['pkg']);
		$task_id	=	(Int)$_REQUEST['task_id'];
		$red_id		=	(Int)$_REQUEST['red_id'];
		
		if( $red_type == 1 ) {
			//直接发放
			$red_package_info = D('Sj.RedActivity')->get_red_package_info($red_id);
			if( empty($red_package_info) ) {
				exit(json_encode(array('code'=>0, 'msg'=>'未获到红包信息')));
			}
			$red_info = array(
				'id'		=>	$red_package_info[0]['id'],
				'pname'		=>	$red_package_info[0]['pname'],
				'totalmon'	=>	$red_package_info[0]['totalmon'],
				'totalnum'	=>	$red_package_info[0]['totalnum'],
				'givetype'	=>	$red_package_info[0]['givetype'],
				'minrand'	=>	$red_package_info[0]['minrand'],
				'maxrand'	=>	$red_package_info[0]['maxrand'],
			);
			exit(json_encode(array('code'=>1, 'red_info'=>$red_info)));
		}elseif( $red_type == 2 ) {
			$task_info = D('Sj.RedActivity')->get_red_soft_list(1, $pkg, $task_id);
			$red_package_info = D('Sj.RedActivity')->get_red_package_info($task_info['red_id']);
			if( empty($red_package_info) ) {
				exit(json_encode(array('code'=>0, 'msg'=>'未获到红包信息')));
			}
			$red_info = array(
					'id'		=>	$red_package_info[0]['id'],
					'pname'		=>	$red_package_info[0]['pname'],
					'totalmon'	=>	$red_package_info[0]['totalmon'],
					'totalnum'	=>	$red_package_info[0]['totalnum'],
					'givetype'	=>	$red_package_info[0]['givetype'],
					'minrand'	=>	$red_package_info[0]['minrand'],
					'maxrand'	=>	$red_package_info[0]['maxrand'],
			);
			exit(json_encode(array('code'=>1, 'red_info'=>$red_info)));
		}else {
			exit(json_encode(array('code'=>0, 'msg'=>'参数有误')));
		}
	}
	
	//创建目录  CommonAction.php中copy过来的
	protected  function mkDirs($path){
            $adir = explode('/',$path);
            $dirlist = '';
            $rootdir = array_shift($adir);
            if(($rootdir!='.'||$rootdir!='..')&&!file_exists($rootdir)){
                @mkdir($rootdir);
            }
            foreach($adir as $key=>$val){
                $dirlist .= "/".$val;
                $dirpath = $rootdir.$dirlist;
                if(!file_exists($dirpath)){
                @mkdir($dirpath);
                @chmod($dirpath,0777);
                }
            }
    }
	//计算csv中的个数 并返回
	function pub_csv_count()
	{
		if($_FILES['upload_file'])
		{
			$filename=$_FILES['upload_file']['tmp_name'];
			$err = $_FILES["upload_file"]["error"];
			$file_name_csv=$_FILES['upload_file']['name'];
		}
		else if($_FILES['upload_search_keys'])
		{
			$filename=$_FILES['upload_search_keys']['tmp_name'];
			$err = $_FILES["upload_search_keys"]["error"];
			$file_name_csv=$_FILES['upload_search_keys']['name'];
		}
		if(empty($filename))
		{
			$error1=-1;
			echo '{"error1":"'.$error1.'"}';
			return;
		}
		$tmp_arr = explode(".", $file_name_csv);
		$name_suffix = array_pop($tmp_arr);
		if (strtoupper($name_suffix) != "CSV") 
		{
			$error2=-2;
			echo '{"error2":"'.$error2.'"}';
			return;
		}
		$fp=fopen($filename,'r');
		$n = 0; 
		while (!feof($fp)) 
		{
			/*$out[$n]=fgets($fp);
			$out[$n]=str_replace(array("\n","\r"),"",$out[$n]);//去掉换行符
			if(!empty($out[$n]))
			{
				$n++;
			}*/
			$out='';
			$out=str_replace(array("\n","\r"),"",fgets($fp));//去掉换行符
			if(!empty($out))
			{
				$n++;
			}
		}
		//$len_result=$n-1;
		$len_result=$n;
		
		// save the import file for backups
		$path=date("/Ym/d/",time());
		$save_dir = C("MARKET_PUSH_CSV").$path;
		$this->mkDirs($save_dir);
		$save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
		$save_file_name = $save_dir . $save_name;
		$db_save=$path.$save_name;
		if($_FILES['upload_file']['tmp_name'])
		{
			move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
		}
		elseif($_FILES['upload_search_keys']['tmp_name'])
		{
			move_uploaded_file($_FILES['upload_search_keys']['tmp_name'], $save_file_name);
		}
		echo '{"out_count":"' . $len_result . '","csv_url":"' . $db_save . '"}';
		//echo '{"out_count":"' . $len_result . '","csv_url":"' . $db_save . '","error1":"'.$error1.'","error2":"'.$error2.'"}';
	}
	// 下载模版
    function down_moban() 
	{
        
		$file_dir = C("MARKET_PUSH_CSV")."/yyxzmb.csv";
		$file_name = 'pre_download_moban';
        
        if (file_exists($file_dir)) 
		{
            $file = fopen($file_dir,"r");
			
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode($file_name . "模版") . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } 
		else 
		{
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }	
	public function showChannel()
	{
		if (!empty($_POST['all'])) {
			unset($_COOKIE['c_keyword']);
		} elseif(isset($_POST['keyword'])) {
			$_COOKIE['c_keyword'] = $_POST['keyword'];
		}

		$type_list = array(
			'checobox' => 1,
			'radio' => 1,
		);

		if (isset($_GET['type']) && isset($type_list[$_GET['type']])) {
			$input_type = $_GET['type'];
		} else {
			$input_type = 'checkbox';
		}
		$this->assign('input_type', $input_type);


		$Model = new Model();
		$where = array();
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
			'source_type' => $source_type,
			'source_value' => $_SESSION['admin']['admin_id'],
			'target_type' => $target_type
		);
		$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
        
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){

		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}'";
			$res = $Model->query($sql);

			foreach ($res as $item) {
				$not_in_cid[] = $item['cid'];
			}
			$where['cid'] = array('in', $not_in_cid);
		}

		if (!empty($_COOKIE['c_keyword'])) {
			$this->assign('keyword', $_COOKIE['c_keyword']);
			$db = Db::getInstance();
			$keyword = $db->escape_string(trim($_COOKIE['c_keyword']));
			$where['chname'] = array('like', '%'. $keyword. '%');
		}
		$where['status'] = 1;
		if (!empty($_GET['platform'])) {
			$where['platform'] = $_GET['platform'];
		}

		$channels = $Model->table('sj_channel')->where($where)->field('cid,chname,category_id')->select();
		$cids = explode('_', $_COOKIE['cids']);
		$cids = array_unique($cids);
		$in_cid = array();
		foreach ($cids as $cid){
			if (!empty($cid)) $in_cid[] = $cid;
		}
		$channel_category = D('Sj.ChannelCategory');
		$category_list = array();

		$category_list = $channel_category->getCategory();
		if($_GET['sel']=='no'){
		}else{
			if (empty($_POST['keyword']) || trim($_POST['keyword']) == '通用') {
				$category_list[0]['name'] = '未分类';
				$category_list[0]['result'][] = array(
					'cid' => 0,
					'chname' => '通用',
					'category_id' => 0,
				);
			}
		}
		foreach ($channels as $k => $v) {
			$channels[$k]['checked'] = in_array($v['cid'], $in_cid);
			$category_list[$v['category_id']]['result'][] = $channels[$k];
		}

		$this->assign('platform', $_GET['platform']);
		$this->assign('category_list', $category_list);
		$this->assign('channels', $channels);
		//$this->assign('selected_channel', $_SESSION['selected_channel']);
		$this->assign('callback', $_REQUEST['callback']);
		$this->assign('selected', $_REQUEST['selected']);
		$this->assign('ready', $_REQUEST['ready']);
		$this->display('Public::channel');
	}
	/* public function setChannel()
	 {
		//echo $_SERVER['HTTP_REFERER'];
		if(isset($_SERVER['HTTP_REFERER'])){
		$_SESSION["zh_http"]=$_SERVER['HTTP_REFERER'];
		}
		if (!isset($_SESSION['selected_channel'])){
		$_SESSION['selected_channel'] = array();
		}
		if ($_GET['act'] == 'add') {
		$_SESSION['selected_channel'][$_GET['cid']] = $_GET['cname'];
		} elseif ($_GET['act'] == 'del') {
		unset($_SESSION['selected_channel'][$_GET['cid']]);
		}

		} */
	public function showDevice()
	{
		if (!empty($_POST['all'])) {
			unset($_COOKIE['d_keyword']);
		} elseif(isset($_POST['keyword'])) {
			$_COOKIE['d_keyword'] = $_POST['keyword'];
		}
		$Model = new Model();

		$where = array();
		if (!empty($not_in_cid)) $where['cid'] = array('not in', $not_in_cid);
		if (!empty($_COOKIE['d_keyword'])) {
			$this->assign('keyword', $_COOKIE['d_keyword']);
			$db = Db::getInstance();
			$keyword = $db->escape_string(trim($_COOKIE['d_keyword']));
			$where['dname'] = array('like', '%'. $keyword. '%');
		}
		$where['status'] = 1;
		$total = $Model->table('pu_device')->where($where)->field('did,dname')->limit()->count();

		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($total, 50, $param);
		$devices = $Model->table('pu_device')->where($where)->field('did,dname')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();

		$this->assign("page", $show);

		$this->assign('devices', $devices);
		if (empty($_GET['reset_device'])) {
			//$_SESSION['selected_device'] = array();
			$this->assign('selected_devices', $_SESSION['selected_device']);
		}
		$this->assign('callback', $_REQUEST['callback']);
		$this->display('Public::device');
	}
	public function setDevice()
	{
		if (!isset($_SESSION['selected_device'])){
			$_SESSION['selected_device'] = array();
		}
		if ($_GET['act'] == 'add') {
			$_SESSION['selected_device'][$_GET['did']] = $_GET['dname'];
		} elseif ($_GET['act'] == 'del') {
			unset($_SESSION['selected_device'][$_GET['did']]);
		}

	}
    
    public function showSoft()
	{
		$model = new Model();
		$keyword = $_GET['query'];
		$real_keyword = escape_string($keyword);
		$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
		$offset = intval($offset);
		$size = 20;
		$sql = "select package, softname from sj_soft where status=1 and hide=1 and (package like '%{$real_keyword}%' or softname like '%{$real_keyword}%') group by package order by total_downloaded desc limit {$offset}, {$size};";
		$softs = $model->query($sql);

		$data = array(
			'query' => $keyword,
			'suggestions' => array(),
			'data' => array(),
		);
		foreach($softs as $v) {
			$data['suggestions'][] = "{$v['softname']} : {$v['package']}";
			$data['data'][] = $v['package'];
		}

		exit(json_encode($data));
		$this->assign('offset', $offset + $size);
		$this->assign('query', $keyword);
		$this->assign('softs', $softs);
		$this->display('Public::soft');
	}

	function filter_word(){
		$param = array();
		if (isset($_REQUEST['softname'])) {
			$param['softname'] = $_REQUEST['softname'];
		}
		if (isset($_REQUEST['dev_name'])) {
			$param['dev_name'] = $_REQUEST['dev_name'];
		}
		if (isset($_REQUEST['ename'])) {
			$param['ename'] = $_REQUEST['ename'];
		}
		if (isset($_REQUEST['tags'])) {
			$param['tags'] = $_REQUEST['tags'];
		}
		if (isset($_REQUEST['dev_enname'])) {
			$param['dev_enname'] = $_REQUEST['dev_enname'];
		}
		if (isset($_REQUEST['dever_email'])) {
			$param['dever_email'] = $_REQUEST['dever_email'];
		}
		if (isset($_REQUEST['intro'])) {
			$param['intro'] = $_REQUEST['intro'];
		}
		if (isset($_REQUEST['update_content'])) {
			$param['update_content'] = $_REQUEST['update_content'];
		}
		if (isset($_REQUEST['note'])) {
			$param['note'] = $_REQUEST['note'];
		}
		if (isset($_REQUEST['type'])) {
			$param['type'] = $_REQUEST['type'];
		}

	    $special = D('Dev.Sensitive');
		if(isset($param['type']) && $param['type'] == 'special'){
		    $result = $special->pub_filter_special_word($param);
		}else{
		    $result = $special->check_special_word($param);
		}
		exit(json_encode($result));

	}

	public function check_word()
	{
		$this->display('Public::check_word');
	}
	function test_encoding() {
		$model = new Model();
		$test = urldecode("%CC%CC");
		$map = array(
			'package' => 'xxx.xxx.xxx',
		'auth' => 1,
			'note' => $test,
		);
		$model->table('sj_soft_note')->add($map);
	}
	public function  showMarketIncremental()
	{
		if (isset($_GET['type']) && isset($type_list[$_GET['type']])) {
			$input_type = $_GET['type'];
		} else {
			$input_type = 'checkbox';
		}
		$platform = isset($_GET['platform'])?$_GET['platform']:0;
		$cid = isset($_GET['cid'])?$_GET['cid']:0;
		$model = new Model();
		$sql = "select version_code,version_name,id from sj_market where platform=".$platform." AND cid=".$cid." group by version_code";
		$version_info = $model->query($sql);
		$this->assign('input_type', $input_type);
		$this->assign('callback', $_REQUEST['callback']);
		$this->assign('selected_version', $_SESSION['selected_version']);
		$this->assign('version_info', $version_info);
		$this->display('Public::incremental');
	}
	public function setMarketIncremental(){
		if (!isset($_SESSION['selected_version'])){
			$_SESSION['selected_version'] = array();
		}
		if ($_GET['act'] == 'add') {
			$_SESSION['selected_version'][$_GET['did']] = $_GET['dname'];
		} elseif ($_GET['act'] == 'del') {
			unset($_SESSION['selected_version'][$_GET['did']]);
		}
	}
	//公用的包名验证
	function package_check(){
        $model = new Model();
        $package = $_GET['soft_package'];
        $result = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
        if($result){
        	echo json_encode($result[0]['softname']);
            return json_encode($result[0]['softname']);           
        }else{
            echo json_encode(1);
            return json_encode(1);
        }
    }
	//检查包名是否预约
	function check_game_order(){
        $model = new Model();
        $id = $_GET['id'];
        $result = $model -> table('sj_game_subscriber') -> where(array('id' => $id,'status' => 1)) -> find();
        if($result){
			$game_result = $model ->table('sj_activity_page') -> where(array('ap_id' => $result['ap_id'],'status' => 1)) -> find();
			if($game_result)
			{
				echo json_encode($game_result['download_comment']);
				return json_encode($game_result['download_comment']);
			}
			else
			{
				echo "";
				return "";
			}
        }else{
            echo 1;
            return 1;
        }
    }
	//获取包名和地址
	function get_soft_info($soft_package)
	{
		$model = new Model();
		if($_GET['soft_package'])
		{
			$package=$_GET['soft_package'];
		}
		else
		{
			$package=$soft_package;
		}
		$result = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> find();
		if($result)
		{
			$where=array(
				'softid' => $result['softid'],
				'package_status' => 1,
			);
			$ret = $model -> table('sj_soft_file') ->where($where)->find();
			if($ret)
			{
				$soft_info = '{"soft_name":"'.$result['softname'].'","soft_url":"'.$ret['url'].'"}';
				if($_GET['soft_package'])
				{
					echo json_encode($soft_info);
					return json_encode($soft_info);
				}
				else
				{
					return $soft_info;
				}
			}
			else
			{
				if($_GET['soft_package'])
				{
					echo 2;
					return json_encode(2); //没有获取到地址
				}
				else
				{
					return 2;
				}
			}
		}
		else
		{
			if($_GET['soft_package'])
			{
				echo 1;
				return json_encode(1); //没有获取到软件名称
				
			}
			else
			{
				return 1; 
			}
		}
	}
	function showEditPush()
	{
		/**
        *获取机型、固件版本、运营商列表
		**/
		$device_db=M();
        $channel_model = M('channel');
		
		$cid=$_POST['cid'];
		$oid=$_POST['oid'];
		$csv_url=$_POST['csv_url'];
		$csv_count=$_POST['csv_count'];
		$device_did=$_POST['device_did'];
		$firmware=$_POST['firmware'];
		$abi=$_POST['abi'];
		$version_code=$_POST['version_code'];
		$push_area=$_POST['push_area'];
		$paichu_area=$_POST['paichu_area'];
		$is_upload_csv = $_POST['is_upload_csv'];
		
		//渠道
		$cookstr = preg_replace('/^,/','',$cid);
		$cookstr = preg_replace('/,$/','',$cookstr);
		$array = explode(',', $cookstr);
		$chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
		if (in_array("0",$array)&&$chl!=NULL)
		{
		  $tong = array("cid"=> "0" ,"chname"=> "通用");
		  array_unshift($chl, $tong);
		}
		if (in_array("0",$array)&&$chl==NULL)
		{
		  $chl[0]['cid']="0";
		  $chl[0]['chname']="通用";
		}
		//机型
		$device_did = preg_replace('/^,/','',$device_did);
		$device_did = preg_replace('/,$/','',$device_did);
		if (strlen($device_did) > 0) 
		{
            $device_selected = explode(',', $device_did);
            $device_selected_ret = array();
            foreach ($device_selected as $ds) 
			{
                if (empty($ds)) continue;
                $device_name = $device_db->table("pu_device")->where(array('did' => $ds))->field('did,dname')->select();
                $device_selected_ret[] = array('did' => $ds,'dname' => $device_name[0]['dname']);
            }
            $this->assign('device_selected', $device_selected_ret);
        }
		//推送区域
        $area_list=explode(';',$push_area);
		//排除区域
		$paichu_area_list=explode(';',$paichu_area);
        //其他
		$util = D('Sj.Util');
        $this->assign('abilist', $util->getAbiList($abi));
        $this->assign('firmwarelist', $util->getFirmwareList(explode(',', $firmware)));
        $this->assign('version_list', $util->getMarketVersion(explode(',', $version_code)));
        $this->assign('operator_list', $util->getOperators(explode(',',$oid)));
		
		$this->assign('chl_list', $chl);
		$this->assign("push_area",$area_list);
		$this->assign("paichu_area",$paichu_area_list);
		$this->assign("csv_url",$csv_url);
		$this->assign("csv_count",$csv_count);
		$this->assign("is_upload_csv",$is_upload_csv);
		$this->display('Public::push_common_edit');
	}
    public function pub_get_softname() {
        $model = new Model();
        $package = trim($_GET['package']);
        $softname_result = $model->table('sj_soft')->where(array('package' => $package, 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
        $my_category = substr($softname_result[0]['category_id'], 1, -1);

        $category_result = $model->table('sj_category')->where(array('category_id' => $my_category, 'status' => 1))->select();

        $data = array(
            'soft_name' => $softname_result[0]['softname'],
            'category_name' => $category_result[0]['name']
        );
        if ($softname_result) {
            echo json_encode($data);
            return json_encode($data);
        } else {
            echo 2;
            return 2;
        }
    }
}
