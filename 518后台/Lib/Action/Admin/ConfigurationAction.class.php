<?php
/**
 * 安智网产品管理平台 后台配置管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2011.5.20
 * ----------------------------------------------------------------------------
*/
class ConfigurationAction extends CommonAction {
	private $typeid;        //类型id
	private $uid;           //用户id
	private $map;           //存储条件
	private $users_db;      //用户表
	private $lists;         //常用列表
	private $notallowword;  //敏感词
	private $user_log_db;   //用户日志表
	private $user_log_list; //用户日志列表
	private $conf_db;        //配置表
	private $conf_list;      //配置列表
	private $templists;       //temp表中里列表
	private $pid;            //临时ID
//------------------------------------敏感词管理--------------------------------------
	//配置管理_敏感词管理
	public function notallowword() {
		$this->pid='badword';
		$this->conf_db = D('Sj.Config');
		$this->map['config_type']=$this->pid;
		$this->map['status']=1;
		$res=$this->conf_db->where($this->map)->find();
		$notallowword = $res['configcontent'];
		$this->assign('notallowword',$notallowword);
		$this->display();
	}
	//配置管理_敏感词管理_编辑
	public function notallowword_edit() {
		import("@.ORG.Input");
		$this->pid='badword';
		$this->conf_db = D('Sj.Config');
		$this->map['config_type']=$this->pid;
		$this->map['status']=1;
		$this->conf_list['configcontent']=Input::getVar($_POST['notallowword']);

		$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);
		$this->writelog('修改敏感词为'.$_POST['notallowword'].'','pu_config',"config_type:{$this->pid}",__ACTION__ ,"","edit");
		$this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/Configuration/notallowword/type/'.$type.'');
		$this->success("敏感词修改成功！");
	}

	public function badcomment() {
		if (!empty($_POST)) {
			import("@.ORG.Input");
			$this->pid='BAD_COMMENT';
			$this->conf_db = D('Sj.Config');
			$this->map['config_type']=$this->pid;
			$this->map['status']=1;
			$this->conf_list['configcontent']=Input::getVar($_POST['badcomment']);

			$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);
			$this->writelog('修改评论敏感词为'.$_POST['badcomment'].'','pu_config',"config_type:{$this->pid}",__ACTION__ ,"","edit");
			$this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/Configuration/badcomment');
			$this->success("评论敏感词修改成功！");
		} else {
			$this->pid='BAD_COMMENT';
			$this->conf_db = D('Sj.Config');
			$this->map['config_type']=$this->pid;
			$this->map['status']=1;
			$res=$this->conf_db->where($this->map)->find();
			$badcomment = $res['configcontent'];
			$this->assign('badcomment',$badcomment);
			$this->display();
		}
	}

//------------------------------------网站配置管理--------------------------------------
	//配置管理_配置列表
	public function configurable() {
		$this->conf_db = D('Sj.Config');
		$limit = 10;
		$where['status'] = 1;
		$this->check_where($where, 'config_type', 'isset', 'like');
		$this->check_where($where, 'configname', 'isset', 'like');
		$this->check_where($where, 'configcontent', 'isset', 'like');
		$count = $this->conf_db->where($where)->count();
		$param = http_build_query($_GET);
        import("@.ORG.Page2");		
		$Page = new Page($count, $limit, $param);		
		$this->conf_list=$this->conf_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('conf_id desc')->select();
		$this->assign('conflist',$this->conf_list);
		$this -> assign('page', $Page->show());
		$this -> assign('total', $total);		
		$this->display();
	}
	//配置管理_配置管理_删除
	public function configurabledel() {
		$this->pid=$_GET['id'];
		$this->conf_db = D('Sj.Config');
		$this->map['conf_id']=$this->pid;
		$this->conf_list['status']=0;
		if(false!==$this->conf_db->where($this->map)->save($this->conf_list))
		{
			$this->writelog('删除了id为'.$this->pid.'的配置','pu_config',$this->pid,__ACTION__ ,"","del");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configurable');
			$this->success("删除配置项成功！");
		}else
		{
		   $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configurable');
		   $this->error("删除配置项失败,发生错误！");
		}
	}
	//配置管理_配置管理_增加_显示
	public function configurableadd() {
		$this->display();
	}
	//配置管理_配置管理_增加_执行
	public function configurable_add() {
		import("@.ORG.Input");

		$this->reload();
		$this->conf_db = D('Sj.Config');
		$this->conf_list['config_type']=trim($_POST['config_type']);
		$this->conf_list['configname']=trim($_POST['configname']);
		$this->conf_list['configcontent']=Input::getVar(trim($_POST['configcontent']));
		$this->conf_list['uptime']=time();
		$this->conf_list['status']=1;

		if(empty($this->conf_list['config_type']) || empty($this->conf_list['configname']) || empty($this->conf_list['configcontent'])) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configurable');
			$this->error('对不起,配置类型、配置名称、配置内容皆不可为空');
		}

		$ret=$this->conf_db->add($this->conf_list);
		if(false!==$ret) {
			$this->writelog('增加了type为'.$this->conf_list['config_type'].',名称为['.$this->conf_list['configname'].'],内容为['.$this->conf_list['configcontent'].']的配置','pu_config',$ret,__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configurable');
			$this->success("添加配置项成功！");
		}else
		{
		   $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configurable');
		   $this->error("添加配置项失败,发生错误！");
		}
	}
	//配置管理_配置管理_编辑_显示
	public function configurableedit() {
		$this->pid=$_GET['id'];
		$this->conf_db = D('Sj.Config');
		$this->map['conf_id']=$this->pid;
		$this->map['status']=1;
		$this->conf_list=$this->conf_db->where($this->map)->select();
		$this->assign('conflist',$this->conf_list[0]);
		$this->display();
	}
	 //配置管理_配置管理_编辑_执行
	public function configurable_edit() {
		import("@.ORG.Input");
		$this->pid=$_POST['conf_id'];
		$this->conf_db = D('Sj.Config');
		$this->map['conf_id']=$this->pid;
		$this->map['status']=1;
		$this->conf_list['config_type']=$_POST['config_type'];
		$this->conf_list['configname']=$_POST['configname'];
		$this->conf_list['configcontent']=Input::getVar($_POST['configcontent']);
		$this->conf_list['uptime']=time();
		$log_result = $this->logcheck(array('conf_id'=>$this->pid),'pu_config',$this->conf_list,$this->conf_db);
		$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);

		if(false!==$list) {
			$this->writelog('编辑了id为'.$this->pid.',的配置'.$log_result,'pu_config',$this->pid,__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configurable');
			$this->success("修改配置项成功！");
		}else
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configurable');
			$this->error('修改配置项失败！');
		}
	}

	//配置更新显示V4.0
	public function configure_edit(){
		$this->conf_db = D('Sj.Config');
		$conf_type = array('NET_CHANGE_TIP_FREQUENCY','SINA_OAUTH_URL','SINA_SHARE_URL','SWITCH_WEIBO_SINA','PUSH_LEVEL','SWITCH_SECURE','SINA_SHARE_URL_BACKUP','BACKUP_DOMAIN');
		$map['config_type']= array('in',$conf_type);
		$map['status']=1;
		$conflist = $this->conf_db->where($map)->select();
		$this->assign('conflist',$conflist);

		$this->getPushLevelList($conflist[2]['configcontent']);
		$this->display();
	}
	//配置更新执行V4.0
	public function configure_edit_do(){

		$nctf   = trim($_POST['NET_CHANGE_TIP_FREQUENCY']);
		$switch = trim($_POST['PUSH_LEVEL_SWITCH']);
		$sou    = trim($_POST['SINA_OAUTH_URL']);
		$ssu    = trim($_POST['SINA_SHARE_URL']);
		$ss     = trim($_POST['SWITCH_SECURE']);
		$sws    = trim($_POST['SWITCH_WEIBO_SINA']);
		$ssub   = trim($_POST['SINA_SHARE_URL_BACKUP']);
		$bd     = trim($_POST['BACKUP_DOMAIN']);

		$this->conf_db = D('Sj.Config');
		$nctf_count = $this->conf_db->where("status=1 and config_type = 'NET_CHANGE_TIP_FREQUENCY' ")->select();
		if(count($nctf_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$nctf."',uptime='".time()."' WHERE config_type= 'NET_CHANGE_TIP_FREQUENCY' ";
		   $this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(status,config_type,configname,configcontent,note,uptime) values('1,NET_CHANGE_TIP_FREQUENCY','NET_CHANGE_TIP_FREQUENCY','".$nctf."','NET_CHANGE_TIP_FREQUENCY','".time()."') ";
		   $this->conf_db->query($sql);
		}

		if($switch == 0){
		  $push_level  = 0;
		}else{
		  $push_level = array_sum($_POST['PUSH_LEVEL']);
		}

		$pl_count = $this->conf_db->where("status=1 and config_type = 'PUSH_LEVEL' ")->select();
		if(count($pl_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$push_level."',uptime='".time()."',status=1 WHERE config_type= 'PUSH_LEVEL' ";
		   $this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(config_type,configname,configcontent,note,uptime,status) values('PUSH_LEVEL','PUSH_LEVEL','".$push_level."','PUSH_LEVEL','".time()."',1) ";
		   $this->conf_db->query($sql);
		}

		$sou_count = $this->conf_db->where("status=1 and config_type = 'SINA_OAUTH_URL' ")->select();
		if(count($sou_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$sou."',uptime='".time()."',status=1 WHERE config_type= 'SINA_OAUTH_URL' ";
		   $this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(config_type,configname,configcontent,note,uptime,status) values('SINA_OAUTH_URL','SINA_OAUTH_URL','".$sou."','SINA_OAUTH_URL','".time()."',1) ";
		   $this->conf_db->query($sql);
		}

		$ssu_count = $this->conf_db->where("status=1 and config_type = 'SINA_SHARE_URL' ")->select();
		if(count($ssu_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$ssu."',uptime='".time()."' WHERE config_type= 'SINA_SHARE_URL' ";
		   $this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(config_type,configname,configcontent,note,uptime,status) values('SINA_SHARE_URL','SINA_SHARE_URL','".$ssu."','SINA_SHARE_URL','".time()."',1) ";
		   $this->conf_db->query($sql);
		}

		$ss_count = $this->conf_db->where("status=1 and config_type = 'SWITCH_SECURE' ")->select();
		if(count($ss_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$ss."',uptime='".time()."' WHERE config_type= 'SWITCH_SECURE' ";
		   $result = $this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(config_type,configname,configcontent,note,uptime,status) values('SWITCH_SECURE','SWITCH_SECURE','".$ss."','SWITCH_SECURE','".time()."',1) ";
		   $this->conf_db->query($sql);
		}

		$sws_count = $this->conf_db->where("status=1 and config_type = 'SWITCH_WEIBO_SINA' ")->select();
		if(count($sws_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$sws."',uptime='".time()."' WHERE config_type= 'SWITCH_WEIBO_SINA' ";
		   $this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(config_type,configname,configcontent,note,uptime,status) values('SWITCH_WEIBO_SINA','SWITCH_WEIBO_SINA','".$sws."','SWITCH_WEIBO_SINA','".time()."',1) ";
		   $this->conf_db->query($sql);
		}

		$ssub_count = $this->conf_db->where("status=1 and config_type = 'SINA_SHARE_URL_BACKUP' ")->select();
		if(count($ssub_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$ssub."',uptime='".time()."' WHERE config_type= 'SINA_SHARE_URL_BACKUP' ";
		   $this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(config_type,configname,configcontent,note,uptime,status) values('SINA_SHARE_URL_BACKUP','SINA_SHARE_URL_BACKUP','".$ssub."','SINA_SHARE_URL_BACKUP','".time()."',1) ";
		   $this->conf_db->query($sql);
		}

		$bd_count = $this->conf_db->where("status=1 and config_type = 'BACKUP_DOMAIN' ")->select();
		if(count($bd_count) > 0){
		   $sql  = "UPDATE pu_config SET configcontent = '".$bd."',uptime='".time()."' WHERE config_type= 'BACKUP_DOMAIN' ";
		   $ret=$this->conf_db->query($sql);
		}else{
		   $sql  = "INSERT INTO pu_config(config_type,configname,configcontent,note,uptime,status) values('BACKUP_DOMAIN','BACKUP_DOMAIN','".$bd."','BACKUP_DOMAIN','".time()."',1) ";
		   $ret=$this->conf_db->query($sql);
		}

		if(true){
			$url = "http://dummy.goapk.com/call.php?m=config&a=refresh";
			file_get_contents($url);
			$this->writelog('手机高低配配置：配置更新执行了'.$nctf.'的推送信息','pu_config',$nctf,__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configure_edit');
			$this->success("配置更新执行成功！");
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/configure_edit');
			$this->error("配置更新执行失败！");
		}
	}

	//获取推送等级
	public function getPushLevelList($push_level = 0)
	{
		$push_level = array(
			'1' => array('登录市场推送', $push_level & 1),
			'2' => array('网络切换',$push_level & 2),
			'4' => array('实时推送',$push_level & 4),
		    '8' => array('云推送',$push_level & 8)
		);
		$this->assign('pushlevel', $push_level);
	}

	//邮件配置列表
	public function email_list() {
		import('@.ORG.Page');
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this -> assign('lr',(int)$_GET['lr']);
		}else{
			$this -> assign('lr',$limit);
		}
		if(isset($_GET['p'])){
			$this -> assign('p',(int)$_GET['p']);
		}else{
			$this -> assign('p', 1);
		}
		$where = array();
		if(isset($_GET['email_address'])){
			$where['`email_address`'] = array('like',"%".trim($_GET['email_address'])."%");
			$this -> assign('email_address',$_GET['email_address']);
		}else{
			$this -> assign('email_address','');
		}
		if(isset($_GET['accept_frequency'])){
			$where['`accept_frequency`'] = intval($_GET['accept_frequency']);
			$this -> assign('accept_frequency',intval($_GET['accept_frequency']));
		}else{
			$this -> assign('accept_frequency',1);
		}
		$where['_logic'] = 'or';
		if(isset($_GET['email_address']) && isset($_GET['accept_frequency'])){
			$map['_complex'] = $where;
			$map['`status`'] = array('eq',1);
		}else if(isset($_GET['accept_frequency'])){
			$map['`accept_frequency`'] = intval($_GET['accept_frequency']);
			$map['`status`'] = 1;
		}else{
			$map['`status`'] = 1;
		}
		$model = M('email_setting');
		$email_count = $model -> where($map) -> count();
		$page  = new Page($email_count, $limit, $param);
		$email_list = $model  -> where($map) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
		$this -> assign('email_list',$email_list);
		$af   =  $this -> getAcceptFrequencyList(isset($_GET['accept_frequency']) ? intval($_GET['accept_frequency']) : 1);
		$app_list = json_decode($this -> getAppSignList(),true);
		$this -> assign('app_list',$app_list);
		$this -> assign('accept_frequency',$af);
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('page', $page -> show());
		$this -> display();
	}


	//添加邮件地址
	public function email_add(){
		$app_list = $this -> getAppSignList();
		$this -> assign('app_list',json_decode($app_list,true));
		$af   =  $this -> getAcceptFrequencyList();
		$this -> assign ('accept_frequency',$af);
		$this -> display();
	}

	//添加邮件地址do
	public function email_add_do(){
		if(isset($_POST)){
			isset($_POST['email_address']) && $data['email_address'] = trim($_POST['email_address']);
			isset($_POST['accept_frequency']) && $data['accept_frequency'] = intval($_POST['accept_frequency']);
			isset($_POST['app_sign']) && $app_sign = $_POST['app_sign'];
			if(is_array($app_sign)){
				$data['app_sign'] = array_sum($app_sign);
			}
			$data['status']      = 1;
			$data['update_time'] = time();
			if(empty($data['email_address'])){
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_add');
				$this -> error('请填写邮件地址');
			}
			if(!eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$",$data['email_address'])) {
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_add');
				$this -> error('邮件地址格式有误');
			}
			if(empty($data['app_sign'])){
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_add');
				$this -> error('请选择应用场景');
			}
			$model = new Model();
			$map   = array('`email_address`' => $data['email_address'],'`status`' => 1);
			$count = $model -> table('sj_email_setting') -> where($map)-> count();
			if($count > 0){
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_edit');
				$this -> error('邮件地址不允许重复');
			}
			if ($email_id = $model -> table('sj_email_setting') -> add($data)) {
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_list');
				$this -> writelog('添加了address_id为'.$email_id.'的邮件地址', 'sj_email_setting', $email_id,__ACTION__ ,"","add");
				$this -> success('添加成功');
			}else{
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_add');
				$this -> error('添加失败');
			}
		}
	}

	//编辑邮件地址
	public function email_edit(){
		if(isset($_GET)){
			$email_id = intval($_GET['email_id']);
			$where = array('`email_id`' => $email_id);
			$model = new Model();
			$email_info = $model -> table('sj_email_setting') -> where($where) -> limit(1) -> select();
			$this -> assign('email_info', $email_info[0]);
			$af   =  $this -> getAcceptFrequencyList($email_info[0]['accept_frequency']);
			$this -> assign ('accept_frequency',$af);
			$app_list = $this -> getAppSignList();
			$this -> assign('app_list',json_decode($app_list,true));
			$this -> display();
		}
	}
	//编辑邮件地址do
	public function email_edit_do(){
		if(isset($_POST)){
			$email_id = intval($_POST['email_id']);
			$where = array('`email_id`' => $email_id);
			$data['email_address']    = trim($_POST['email_address']);
			$data['app_sign']         = intval($_POST['app_sign']);
			$data['accept_frequency'] = intval($_POST['accept_frequency']);
			$app_sign                 = $_POST['app_sign'];
			$data['update_time']      = time();
			if(is_array($app_sign)){
				$data['app_sign']  = array_sum($app_sign);
			}
			if(empty($data['email_address'])){
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_edit');
				$this -> error('请填写邮件地址');
			}
			if(!eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$",$data['email_address'])) {
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_edit');
				$this -> error('邮件地址格式有误');
			}
			if(empty($data['app_sign'])){
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_edit');
				$this -> error('请选择应用场景');
			}
			$model = new Model();
			$log_result = $this->logcheck(array('email_id'=>$email_id),'sj_email_setting',$data,$model);
		
			if($model -> table('sj_email_setting') -> where($where) -> save($data)){
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_list');
				$this -> writelog('编辑了email_id为'.$email_id.'的邮件地址'.$log_result, 'sj_email_setting', $email_id,__ACTION__ ,"","edit");
				$this -> success('编辑成功');
			}else{
				$this -> assign('jumpUrl', '/index.php/Admin/Configuration/email_edit/email_id/'.$email_id);
				$this -> error('编辑失败');
			}
		}
	}

	//启停用邮件地址
	public function  email_del(){
		if(isset($_GET)){
			$email_id = intval($_GET['email_id']);
			$where = array('email_id' => $email_id);
			$data['status']      = 0;
			$data['update_time'] = time();
			$model = M('email_setting');
			$model -> where($where) -> save($data);
			$this  -> assign('jumpUrl', '/index.php/Admin/Configuration/email_list');
			$this  -> writelog('停用了email_id为'.$email_id.'的邮件地址', 'sj_email_setting', $email_id,__ACTION__ ,"","edit");
			$this  -> success('操作成功');
		}
	}

	//获取应用场景
	public function getAppSignList(){
		$model = M('config');
		$condition = array('status'=>1,'config_type' => 'EMAIL_APP_SIGN');
		$app_list = $model -> table('pu_config')  -> where($condition) -> field('configcontent') -> select();
		return $app_list[0]['configcontent'];
	}

	//获取接收频率
	public function getAcceptFrequencyList($accept_id = 0){
		$accept_frequency = array(1=>'1天',2=>'2天',3=>'3天',4=>'4天',5=>'5天',6=>'6天',7=>'7天');
		$af = '<select name="accept_frequency" >';
		foreach($accept_frequency as $key => $val){
			if($key == $accept_id){
				$af .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
			}else{
				$af .= '<option value="'.$key.'">'.$val.'</option>';
			}
		}
		$af .= '</select>';
		return $af;
	}

	//邮件发送记录
	public function email_send_log(){
		import('@.ORG.Page');
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this -> assign('lr',(int)$_GET['lr']);
		}else{
			$this -> assign('lr',$limit);
		}
		if(isset($_GET['p'])){
			$this -> assign('p',(int)$_GET['p']);
		}else{
			$this -> assign('p', 1);
		}
		$model = new Model();
		if($_GET['email']){
			$this -> assign('email',$_GET['email']);
			$email_list = $model -> table('sj_email_setting') -> where("email_address like '%{$_GET['email']}%'") -> select();
			$where_mail = array();
			foreach($email_list as $email){
				$where_mail[] = $email['email_id'];
			}
			if($where_mail){
				$where['email_id'] = array('in',$where_mail);
			}
		}
		
		if($_GET['edit_stm']||$_GET['edit_etm']){
			$edit_stm = strtotime($_GET['edit_stm']);
			$edit_etm = strtotime($_GET['edit_etm']);
			$this -> assign('edit_stm',$_GET['edit_stm']);
			$this -> assign('edit_etm',$_GET['edit_etm']);
			if($edit_stm > $edit_etm){
				$this -> error('开始时间不能大于结束时间');
			}
			$where['_string'] = ' send_time >'.$edit_stm.' and send_time < '.$edit_etm;
		}
		
		if(isset($_GET['content'])){
			$where['_string'] = "'%{$_GET['content']}%'";
			$this -> assign('content',$_GET['content']);
		}
		$where['status'] = 1;
		$email_count =  $model -> table('sj_email_send_log') -> where($where) -> count();
		$page  = new Page($email_count, $limit, $param);
		$email_list =  $model -> table('sj_email_send_log') -> where($where) -> limit($page->firstRow . ',' . $page->listRows) -> field('email_id,send_time,content,status') -> select();
		$email_info = array();
		for($i = 0 ;$i < count($email_list) ; $i ++){
			$map = array('`email_id`' => $email_list[$i]['email_id'] , '`status`' => 1) ;
			$email_setting = $model -> table('sj_email_setting') -> where($map)
							-> field('email_address,app_sign') -> limit(1) -> select();
			$email_list[$i]['email_address'] = $email_setting[0]['email_address'];
			$email_list[$i]['app_sign'] = $email_setting[0]['app_sign'];
		}
		$this -> assign('email_list', $email_list);
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('page', $page->show());
		$this -> display();
	}

	//4.4配置管理
	function configV44() {
		$config = D('Sj.Config');
		$category = M('category');
		if (empty($_POST))
		{
			$where = array(
				'config_type' => 'ireader_display',
				'status' => 1,
			);
			$ireader_display = $config->where($where)->find();
			$this->assign('ireader_display', $ireader_display['configcontent']);
			$where['config_type'] = 'double_row_switch';
			$double_row_switch = $config->where($where)->find();
			$this->assign('double_row_switch', $double_row_switch['configcontent']);
			$where = array(
				'category_id' => 3,
			);
			$ireader_searchable = $category->where($where)->find();
			$this->assign('ireader_searchable', $ireader_searchable['searchable']);
			$this->display();
		}
		else
		{
			$flag = true;
			$ireader_display = $_POST['ireader_display'] ? $_POST['ireader_display'] : 0;
			$ireader_searchable = $_POST['ireader_searchable'] ? $_POST['ireader_searchable'] : 0;
			$double_row_switch = $_POST['double_row_switch'] ? $_POST['double_row_switch'] : 0;
			$where = array(
				'config_type' => 'ireader_display',
				'status' => 1,
			);
			$ret = $config->where($where)->save(array('configcontent' => $ireader_display));
			if ($ret !== false)
			{
				$where['config_type'] = 'double_row_switch';
				$ret = $config->where($where)->save(array('configcontent' => $double_row_switch));
				if ($ret !== false)
				{
					$ireader_category = array(3);
					while (!empty($ireader_category))
					{
						$cid = array_pop($ireader_category);
						$where = array(
							'category_id' => $cid,
						);
						$ret = $category->where($where)->save(array('searchable' => $ireader_searchable));
						if ($ret === false)
						{
							$flag = false;
						}
						$where = array(
							'parentid' => $cid,
						);
						$to_push = $category->where($where)->select();
						//var_dump($to_push);
						foreach ($to_push as $v)
						{
							array_push($ireader_category, $v['category_id']);
						}
					}
				}
				else
				{
					$flag = false;
				}
			}
			else
			{
				$flag = false;
			}
			if ($flag)
			{
				$this->writelog("修改了v4.4的配置为$ireader_display $ireader_searchable $double_row_switch",'pu_config,pu_config,sj_category',"config_type:ireader_display;config_type:double_row_switch;category_id:{$cid},三者与表一一对应",__ACTION__ ,"","edit");
				$this->success('配置修改成功');
			}
			else
				$this->error('配置修改失败');
		}
	}

	//------------------------------------软件敏感词管理--------------------------------------
	//配置管理_敏感词管理
	public function soft_notallowword() {
		$this->pid='soft_badword';
		$this->conf_db = D('Sj.Config');
		$this->map['config_type']=$this->pid;
		$this->map['status']=1;
		$res=$this->conf_db->where($this->map)->find();
		$notallowword = $res['configcontent'];
		$this->assign('notallowword',$notallowword);
		$this->display();
	}
	//配置管理_敏感词管理_编辑
	public function soft_notallowword_edit() {
		$this->gpcFilter();

		import("@.ORG.Input");
		$this->pid='soft_badword';
		$this->conf_db = D('Sj.Config');
		$this->map['config_type']=$this->pid;
		$this->map['status']=1;
		$this->conf_list['configcontent']=Input::getVar($_POST['notallowword']);

		$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);
		$this->writelog('修改敏感词为'.$_POST['notallowword'].'','pu_config',"config_type:{$this->pid}",__ACTION__ ,"","edit");
		$this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/Configuration/soft_notallowword/');
		$this->success("敏感词修改成功！");
	}
		//------------------------------------软件提醒词管理--------------------------------------
	//软件管理_提醒词管理
	public function soft_remindwords() {
		$this->pid='soft_remind_words';
		$this->conf_db = D('Sj.Config');
		$this->map['config_type']=$this->pid;
		$this -> map['status'] =1;
		$res=$this->conf_db->where($this->map)->find();
		$remindword = $res['configcontent'];
		$this->assign('remindword',$remindword);
		$this->display();
	}
	//软件管理_提醒词管理_编辑
	public function soft_remindwords_edit() {
		$this->gpcFilter();

		import("@.ORG.Input");
		$this->pid='soft_remind_words';
		$this->conf_db = D('Sj.Config');
		$this->map['config_type']=$this->pid;
		$this->map['status']=1;
		$this->conf_list['configcontent']=Input::getVar($_POST['remindword']);

		$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);
		$this->writelog('提醒敏感词为'.$_POST['remindword'].'','pu_config',"config_type:{$this->pid}",__ACTION__ ,"","edit");
		$this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/Configuration/soft_remindwords/');
		$this->success("提醒词修改成功！");
	}

	//--------------------------------------------屏蔽软件包名管理-------------------------------------------------
	public function soft_shieldpackagename(){
		if (!empty($_POST)) {
			import("@.ORG.Input");
			$this->pid='soft_shieldpackagename';
			$this->conf_db = D('Sj.Config');
			$this->map['config_type']=$this->pid;
			$this->map['status']=1;
			$this->conf_list['configcontent']=Input::getVar($_POST['shieldpackagename']);

			$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);
			$this->writelog('修改屏蔽软件包名为'.$_POST['shieldpackagename'].'','pu_config',"config_type:{$this->pid}",__ACTION__ ,"","edit");
			$this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/Configuration/soft_shieldpackagename');
			$this->success("屏蔽软件包名修改成功！");
		} else {
			$this->pid='soft_shieldpackagename';
			$this->conf_db = D('Sj.Config');
			$this->map['config_type']=$this->pid;
			$this->map['status']=1;
			$res=$this->conf_db->where($this->map)->find();
			$shieldpackagename = $res['configcontent'];
			$this->assign('shieldpackagename',$shieldpackagename);
			$this->display();
		}
	}

	//--------------------软件包名高亮显示----------------------------------
	public function soft_cp_highlight()
	{
		$this->pid='soft_cp_highlight_edit';
		$this->conf_db = D('Sj.Config');
		//print_R($this -> conf_db);
		$this->map['config_type']=$this->pid;
		$this->map['status']=1;
		$res=$this->conf_db->where($this->map)->find();
		$highlight = $res['configcontent'];
		$this->assign('highlight',$highlight);
		$this->display();
	}

	//配置管理_包名高亮显示管理_编辑
	public function soft_cp_highlight_edit() {
		$this->gpcFilter();

		import("@.ORG.Input");
		$this->pid='soft_cp_highlight_edit';
		$this->conf_db = D('Sj.Config');
		$this->map['config_type']=$this->pid;
		$this->map['status']=1;
		$this->conf_list['configcontent']=Input::getVar($_POST['highlight']);

		$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);
		$this->writelog('修改需要高亮显示的包名为'.$_POST['highlight'].'','pu_config',"config_type:{$this->pid}",__ACTION__ ,"","edit");
		$this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/Configuration/soft_cp_highlight/');
		$this->success("包名高亮显示修改成功！");
	}

	public function check_word()
	{
		$this->display('Public::check_word');
	}
	// 显示4.2版本配置页面
	function soft_new_config(){
		$this->conf_db = D('Sj.Config');
		if (!empty($_GET)){
			$confid = $_GET['confid'];
			$map['configcontent']=trim($_GET['status']);
			if ($this->conf_db->where("conf_id='$confid'")->save($map)){
				$this->writelog("切换4.3版本开关conf_id={$confid}状态{$map['configcontent']}", 'pu_config', $confid,__ACTION__ ,"","edit");
				$this->ajaxReturn(1, '修改成功！', 1);
			}
			return ;
		}
		

		
		$d = $this->conf_db -> where("config_type='4.2VER_DOWNLOAD'")->select();
		if ($d[0]['status'] == 1){
			$confid = $_POST['confid'];
			$map['configcontent']=$_POST['autonum'];
			if ($this->conf_db->where("conf_id='$confid'")->save($map)){
				$this->writelog('修改4.3版本页面为空时，推送信息数量为'.$map['configcontent'], 'pu_config', $confid,__ACTION__ ,"","edit");
				//$this->ajaxReturn(1, '修改成功！', 1);
				unset($_GET);
				unset($_POST);
				//$this->soft_new_config();
			}
		}
		$p = $this->conf_db -> where("config_type='4.2VER_DISPLAY'")->select();
		$n = $this->conf_db -> where("config_type='4.2VER_PUSH_NUM'")->select();
		$f = $this->conf_db -> where("config_type='VER_FLOW'")->select();
		$this->assign('fow', $f[0]);
		$this->assign('dis', $p[0]);		// 4.2版本日排行显示控制
		$this->assign('dow', $d[0]);		// 4.2版本【更新/下载】 页面推送信息是否下载
		$this->assign('num', $n[0]);

		$icon = M('soft_icon');
		$icon_list0 = $icon->where('status=1')-> order('rank') ->select();	// 审核的
		//$icon_list1 = $icon->where('icon_status=1')->select();	// 认证的
		for($i=1;$i<=count($icon_list0);$i++){
			$rank_result[] = $i;
		}
		$animaction_result = $this -> conf_db -> where(array('config_type' => 'NEW_YEAR_ANIMATION','status' => 1)) -> select();
		$anima_result = json_decode($animaction_result[0]['configcontent'],true);
		
		//过滤
		$this->gpcFilter($icon_list0);
		$this->gpcFilter($icon_list1);
		$this -> assign('start_tm',date('Y-m-d',$anima_result['start_tm']));
		$this -> assign('end_tm',date('Y-m-d',$anima_result['end_tm']));
		$this->assign('rank_result',$rank_result);
		$this->assign('icon_list0', $icon_list0);// 审核的
		$this->assign('count',count($icon_list0));
		$this->assign('icon_list1', $icon_list1);	// 认证的
		$this->display();
	}
	function soft_icon_del(){
		$icon = M('soft_icon');
		$id=$_GET['id'];
		$have_been = $icon -> where(array('id' => $id)) -> select();
		if (!$id){
			$this->ajaxReturn(0, '未知错误！', 0);
			return ;
		}
		//$map['icon_status']=7;
		$map['status'] = 0;
		$affect = $icon -> where("id='{$id}'")-> save($map);
		if ($affect){
			$need_where['_string'] = "status = 1 and rank > {$have_been[0]['rank']}";
			$need_result = $icon -> where($need_where) -> select();
			foreach($need_result as $key => $val){
				$update_where['id'] = $val['id'];
				$update_data['rank'] = $val['rank'] - 1;
				$update_result = $icon -> where($update_where) -> save($update_data);
			}
			$this->writelog('删除了名称ID为['.$id.']的图标', 'sj_soft_icon', $id,__ACTION__ ,"","del");
			$this->ajaxReturn(0, '删除成功！', 0);
		} else {
			$this->ajaxReturn(1, '删除失败！', 1);
		}
	}
	function soft_icon_edit(){
		$id=$_GET['id'];
		
		if($_GET['start_tm'] && $_GET['end_tm']){
			$this -> conf_db = D('Sj.Config');
			$map['configcontent'] = json_encode(array('start_tm' => strtotime($_GET['start_tm']),'end_tm' => strtotime($_GET['end_tm'])));
			$map['uptime'] = time();
			if(strtotime($_GET['start_tm']) > strtotime($_GET['end_tm'])){
				$this -> assign('jumpUrl','/index.php/Admin/Configuration/soft_new_config');
				$this -> error("开始时间不能大于结束时间");
			}
			if(strtotime($_GET['end_tm']) < time()){
				$this -> assign('jumpUrl','/index.php/Admin/Configuration/soft_new_config');
				$this -> error("结束时间不能小于当前时间");
			}
			if($this -> conf_db -> where(array('config_type' => 'NEW_YEAR_ANIMATION','status' => 1)) -> save($map)){
			$this->writelog("已修改5.0新年动画时间设置开始时间:{$_GET['start_tm']},结束时间{$_GET['end_tm']}", 'pu_config', "config_type:NEW_YEAR_ANIMATION",__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Admin/Configuration/soft_new_config');
			$this -> success("修改成功");
			}
	
		}
		if ($_POST){
			//过滤
			$this->gpcFilter();

			$id = $_POST['iconid'];
			$data['icon_name']=$_POST['iconName'];
			$data['icon_name']=trim($_POST['iconName']);
			$data['varname'] = trim($_POST['varname']);
			$data['describe'] = trim($_POST['describe']);
			$data['icon_update_time']=time();
			$tmp1 = $_FILES["img_url_h"]["name"];
			$tmp2 = $_FILES["img_url_m"]["name"];
			$tmp3 = $_FILES["img_url_new_h"]["name"];
			$tmp4 = $_FILES["img_url_new_m"]["name"];
			$tmp5 = $_FILES["img_url_snew_h"]["name"];
			$tmp6 = $_FILES["img_url_snew_m"]["name"];
			$path=date("Ym/d/",time());
			if ($tmp1) {
				$config['multi_config']['img_url_h'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' =>  1024*40,
				);
			}
			if ($tmp2) {
				$config['multi_config']['img_url_m'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_size' =>  1024*30,
				);
			}
			if ($tmp3) {
				$config['multi_config']['img_url_new_h'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if ($tmp4) {
				$config['multi_config']['img_url_new_m'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if ($tmp5) {
				$config['multi_config']['img_url_snew_h'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if ($tmp6) {
				$config['multi_config']['img_url_snew_m'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
				);
			}
			if(!empty($config['multi_config'])){
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'img_url_h') {
						$data['img_url_h']=$val['url'];
					}
					if ($val['post_name'] == 'img_url_m') {
						$data['img_url_m']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_new_h') {
						$data['img_url_new_h']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_new_m') {
						$data['img_url_new_m']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_snew_h') {
						$data['img_url_snew_h']= $val['url'];
					}
					if ($val['post_name'] == 'img_url_snew_m') {
						$data['img_url_snew_m']= $val['url'];
					}
				}
			}

			$icon = M('soft_icon');
			$res = $icon->where("id != {$id} and varname = '".$data['varname']."' AND status = 1 and icon_name = '".$data['icon_name']."'")->find();
			if ($res){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/soft_new_config');
				$this->error("修改软件详细图标失败,你编辑的varname的值已存在请先删除！");
				return ;
			}
			$been_result = $icon -> where(array('id' => $id)) -> select();
			//$log_result = $this-> logcheck(array('id' => $id),'sj_soft_icon',array('icon_name' => $_POST['icon_name'],'varname' => $_POST['varname'],'describe' => $_POST['describe'],'img_url_h' => $data['img_url_h'],'img_url_m' => $data['img_url_m'],'img_url_new_h' => $data['img_url_new_h'],'img_url_new_m' => $data['img_url_new_m'],'img_url_snew_h' => $data['img_url_snew_h'],'img_url_snew_m' => $data['img_url_snew_m']));
			$log_result = $this-> logcheck(array('id' => $id),'sj_soft_icon',$data,$icon);
			if($icon->where("id='$id'")->save($data)){
				if($_POST['rank'] > $been_result[0]['rank']){
					$where_need['_string'] = "status = 1 and rank > {$been_result[0]['rank']} and rank <= {$_POST['rank']} and id != {$id}";
					$need_result = $icon -> where($where_need) -> select();
					foreach($need_result as $key => $val){
						$update_where['id'] = $val['id'];
						$update_data['rank'] = $val['rank'] - 1;
						$udpate_result = $icon -> where($udpate_where) -> save($update_data);
					}
				}elseif($_POST['rank'] < $been_result[0]['rank']){
					$where_need['_string'] = "status = 1 and rank < {$been_result[0]['rank']} and rank >= {$_POST['rank']} and id != {$id}";
					$need_result = $icon -> where($where_need) -> select();
					foreach($need_result as $key => $val){
						$update_where['id'] = $val['id'];
						$update_data['rank'] = $val['rank'] + 1;
						$udpate_result = $icon -> where($udpate_where) -> save($update_data);
					}
				}
				$this->writelog("软件列表-版本控制编辑—修改了名称ID为".$id."的图标,".$log_result,'sj_soft_icon',$id,__ACTION__ ,"","edit");
				//$this->writelog('修改了名称ID为['.$id.']的图标'.$log_result, 'sj_soft_icon', $id);
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/soft_new_config');
				$this->success("修改软件详细图标成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/soft_new_config');
				$this->error("修改软件详细图标失败,发生错误！");
			}
		} else {
			$icon = M('soft_icon');
			$icon_list = $icon->where("id=$id")->select();	// 审核的
			$have_been = $icon -> where(array('status' => 1)) -> count();
			for($i=1;$i<=$have_been+1;$i++){
				$rank_result[] = $i;
			}
			$this->assign('rank_result',$rank_result);
			$this->assign('iconList', $icon_list[0]);
			$this->display();
		}
	}
	
	//修改排序
	function select_my_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$new_rank = $_GET['new_rank'];
		$been_result = $model -> table('sj_soft_icon') -> where(array('id' => $id)) -> select();
		$result = $this -> select_rank($been_result[0]['rank'],$new_rank,'sj_soft_icon',$id);
		$time_result = $model -> table('sj_soft_icon') -> where(array('id' => $id)) -> save(array('icon_update_time' => time()));
		if($result){
			echo json_encode(1);
			return json_encode(1);
		}
	}
	
	// 显示添加图标图片页面
	// 不回图标图片处理
	function soft_icon_add(){
		$s = $_GET['s'];
		if($_POST){
			$icon = M('soft_icon');
			$data['varname'] = trim($_POST['varname']);
			$have_been = $icon -> where(array('varname' => $_POST['varname'],'status' => 1)) -> select();
			if($have_been){
				$this -> error("已存在varname为{$_POST['varname']}的图标");
			}
			$data['icon_name']=trim($_POST['iconName']);
			$data['describe']=$_POST['describe'];
		
			$data['icon_update_time']=time();
			$tmp1 = $_FILES["img_url_h"]["name"];
			$tmp2 = $_FILES["img_url_m"]["name"];
			$tmp3 = $_FILES["img_url_new_h"]["name"];
			$tmp4 = $_FILES["img_url_new_m"]["name"];
			$tmp5 = $_FILES["img_url_snew_h"]["name"];
			$tmp6 = $_FILES["img_url_snew_m"]["name"];
			
			$path=date("Ym/d/",time());
			$config = array(
					'multi_config' => array(
						'img_url_h' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							'img_p_size' =>  1024*40,
						),
						'img_url_m' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							'img_p_size' =>  1024*30,
						),
						'img_url_new_h' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
						),
						'img_url_new_m' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
						),
						'img_url_snew_h' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
							
						),
						'img_url_snew_m' => array(
							'savepath' => UPLOAD_PATH. '/img/'. $path,
							'saveRule' => 'getmsec',
	
						),
					),
				);
			$lists=$this->_uploadapk(0, $config);
			foreach($lists['image'] as $val) {
				if ($val['post_name'] == 'img_url_h') {
					$data['img_url_h']= $val['url'];
				}
				if ($val['post_name'] == 'img_url_m') {
					$data['img_url_m']= $val['url'];
				}
				if ($val['post_name'] == 'img_url_new_h') {
					$data['img_url_new_h']= $val['url'];
				}
				if ($val['post_name'] == 'img_url_new_m') {
					$data['img_url_new_m']= $val['url'];
				}
				if ($val['post_name'] == 'img_url_snew_h') {
					$data['img_url_snew_h']= $val['url'];
				}
				if ($val['post_name'] == 'img_url_snew_m') {
					$data['img_url_snew_m']= $val['url'];
				}
			}
			
			
			$have_been = $icon -> where(array('status' => 1)) -> count();
			$data['rank'] = $have_been+1;
			$res = $icon->where("varname = '".$data['varname']."' AND icon_name = '".$data['icon_name']."'")->find();
			if ($res){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/soft_new_config');
				$this->error("添加软件详细图标失败,你添加的varname的值已存在请先删除！");
				return ;
			}
			
			if($id=$icon->add($data)){
					
				$this->writelog('增加了名称ID为['.$id.']为['.$data['icon_name'].']的图标', 'sj_soft_icon', $id,__ACTION__ ,"","add");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/soft_new_config');
				$this->success("添加图标成功！");
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/soft_new_config');
				$this->error("添加图标失败,发生错误！");
			}
		} else {
			$icon = M('soft_icon');
			$have_been = $icon -> where(array('status' => 1)) -> count();
			
			for($i=1;$i<=($have_been+1);$i++){
				$rank_result[] = $i;
			}
		
			$this->assign('rank_result',$rank_result);
			$this->assign('rank',count($rank_result));
			$this->assign('s', $s);
			$this->display();
		}
	}
	//短信系统报警配置管理
	function sms_alarm() {
		$this->conf_db = D('Sj.Config');
		$res = $this->conf_db->where("config_type='sms_alarm' AND configname IN ('sms_alarm_counter','sms_alarm_cont','sms_alarm_balance','sms_balance_cont','sms_stop_counter','sms_alarm_rate') AND status=1")->select();
		$_res = array();
		if($res) {
			foreach($res as $val) {
				$_res[$val['configname']] = $val['configcontent'];
			}
		}

		//监测人员列表
		$person = M('sms_alarm_person');
		$list = $person->order("status desc")->select();
		if($list) {
			foreach($list as $key=>$val) {
				$list[$key]['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
				if($val['scene']==3) {
					$list[$key]['scene_str'] = '短信超量报警/短信余额报警';
				} else if($val['scene']==1) {
					$list[$key]['scene_str'] = '短信超量报警';
				} else if($val['scene']==2) {
					$list[$key]['scene_str'] = '短信余额报警';
				}
				if($val['type']==3) {
					$list[$key]['type_str'] = '邮件/手机';
				} else if($val['type']==1) {
					$list[$key]['type_str'] = '邮件';
				} else if($val['type']==2) {
					$list[$key]['type_str'] = '手机';
				}
				if($val['status']==1) {
					$list[$key]['do'] = "<a href='/index.php/Admin/Configuration/sms_alarm_person/id/{$val['id']}'>【编辑】</a>&nbsp;&nbsp;<a href='javascript:void(0);' onclick=\"do_status('/index.php/Admin/Configuration/sms_alarm_person_status/id/{$val['id']}/status/0')\">【停用】</a>";
				} else {
					$list[$key]['do'] = "<a href='/index.php/Admin/Configuration/sms_alarm_person/id/{$val['id']}'>【编辑】</a>&nbsp;&nbsp;<a href='javascript:void(0);' onclick=\"do_status('/index.php/Admin/Configuration/sms_alarm_person_status/id/{$val['id']}/status/1')\">【启用】</a>";
				}
			}
		}

		//频率
		$redis = new Redis();
		$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
		$rate = $redis->get('sms_alarm_rate');

		$this->assign('_res', $_res);
		$this->assign('list', $list);
		$this->assign('rate', $rate);
		$this->display();
	}
	//短信系统报警配置管理_报警内容编辑
	function sms_alarm_edit() {
		$this->gpcFilter();

		$log_str = '';

		$word_list = array(
			'sms_alarm_counter' => '短信超量数量',
			'sms_alarm_cont' => '短信超量报警内容',
			'sms_alarm_balance' => '系统报警余额',
			'sms_balance_cont' => '余额不足时报警内容',
			'sms_stop_counter' => '停止短信系统的数量',
			'sms_alarm_rate' => '报警发送频率',
		);

		$this->conf_db = D('Sj.Config');
		if (!empty($_POST)) {
			$redis = new Redis();
			$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
			foreach(array('sms_alarm_counter','sms_alarm_cont','sms_alarm_balance','sms_balance_cont','sms_stop_counter','sms_alarm_rate') as $val) {
				$res = $this->conf_db->where("config_type='sms_alarm' AND configname='{$val}' AND status=1")->find();
				if($res) {
					$map = array();
					$map['configcontent'] = trim($_POST[$val]);
					$map['uptime'] = time();
					$ret=$this->conf_db->where("conf_id='{$res['conf_id']}'")->save($map);

					if($redis->get($val)!=$_POST[$val]) {
						$log_str .= "修改 {$word_list[$val]} 由 ".$redis->get($val)." 为 {$_POST[$val]},";
					}
					if($log_str) $this->writelog($log_str,'pu_config',$res['conf_id'],__ACTION__ ,"","edit");
				} else {
					$map = array();
					$map['config_type'] = 'sms_alarm';
					$map['configname'] = $val;
					$map['configcontent'] = trim($_POST[$val]);
					$map['uptime'] = time();
					$map['status'] = 1;
					$ret=$this->conf_db->add($map);

					$log_str .= "添加 {$word_list[$val]} 为 {$_POST[$val]},";
					if($log_str) $this->writelog($log_str,'pu_config',$ret,__ACTION__ ,"","add");
				}

				//Redis更新
				$redis->set($val,trim($_POST[$val]));
			}

			// if($log_str) $this->writelog($log_str,'pu_config',$ret,__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/sms_alarm');
			$this->success("编辑成功！");
		} else {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/sms_alarm');
			$this->error("没有提交内容，发生错误！");
		}
	}
	//短信系统报警配置管理_添加人员/编辑
	function sms_alarm_person() {
		$this->gpcFilter();

		if($_GET['id'] && is_numeric($_GET['id'])) {
			$person = M('sms_alarm_person');
			$res = $person->where("id='{$_GET['id']}'")->find();
		} else {
			$res = array();
		}

		$this->assign('res',$res);
		$this->display();
	}
	//短信系统报警配置管理_停用/启用
	function sms_alarm_person_status() {
		$this->gpcFilter();

		if(is_numeric($_GET['id']) && is_numeric($_GET['status'])) {
			$person = M('sms_alarm_person');
			$data = array();
			$data['status'] = $_GET['status'];
			$person->where("id='{$_GET['id']}'")->save($data);

			//人员放入redis，便于短信平台使用，开始
			$tmp = $person->where("status=1")->select();
			$sms_alarm_send_email = array();
			$sms_alarm_send_mobile = array();
			$sms_balance_send_email = array();
			$sms_balance_send_mobile = array();
			if($tmp) {
				foreach($tmp as $key=>$val) {
					if($val['scene']==3 || $val['scene']==1) {	//短信超量报警
						if($val['type']==3 || $val['type']==1) {	//邮件
							$sms_alarm_send_email[] = $val['email'];
						}
						if($val['type']==3 || $val['type']==2) {	//手机
							$sms_alarm_send_mobile[] = $val['mobile'];
						}
					}
					if($val['scene']==3 || $val['scene']==2) {	//短信余额报警
						if($val['type']==3 || $val['type']==1) {	//邮件
							$sms_balance_send_email[] = $val['email'];
						}
						if($val['type']==3 || $val['type']==2) {	//手机
							$sms_balance_send_mobile[] = $val['mobile'];
						}
					}
				}
			}

			$redis = new Redis();
			$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));

			$redis->set('sms_alarm_send_email',serialize($sms_alarm_send_email));
			$redis->set('sms_alarm_send_mobile',serialize($sms_alarm_send_mobile));
			$redis->set('sms_balance_send_email',serialize($sms_balance_send_email));
			$redis->set('sms_balance_send_mobile',serialize($sms_balance_send_mobile));
			//人员放入redis，便于短信平台使用，结束

			$this->writelog("编辑短信系统报警配置中的检测人员ID为{$_GET['id']}的状态为".($_GET['status']==1 ? '正常' : '停用'),'sj_sms_alarm_person',$_GET['id'],__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/sms_alarm');
			$this->success("操作成功！");
		}

		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/sms_alarm');
		$this->error("发生错误！");
	}
	//短信系统报警配置管理_添加人员_添加/编辑
	function sms_alarm_person_add() {
		$this->gpcFilter();

		$old = array();

		$person = M('sms_alarm_person');
		if($_POST['id'] && is_numeric($_POST['id'])) {	//编辑
			$res = $person->where("email='{$_POST['email']}' AND mobile='{$_POST['mobile']}' AND id!='{$_POST['id']}'")->find();
			if($res) {
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/sms_alarm_person/id/'.$_POST['id']);
				$this->error("该邮件地址或手机号码已存在，编辑失败！");
			}

			$old = $person->where("id='{$_POST['id']}'")->find();
		} else {						//添加
			$res = $person->where("email='{$_POST['email']}' AND mobile='{$_POST['mobile']}'")->find();
			if($res) {
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/sms_alarm_person');
				$this->error("该邮件地址或手机号码已存在，添加失败！");
			}
		}

		$data = array();
		$data['name'] = $_POST['name'];
		$data['email'] = $_POST['email'];
		$data['mobile'] = $_POST['mobile'];
		if($_POST['scene1'] && $_POST['scene2']) {
			$data['scene'] = 3;
		} else if($_POST['scene1']) {
			$data['scene'] = 1;
		} else if($_POST['scene2']) {
			$data['scene'] = 2;
		}
		if($_POST['type1'] && $_POST['type2']) {
			$data['type'] = 3;
		} else if($_POST['type1']) {
			$data['type'] = 1;
		} else if($_POST['type2']) {
			$data['type'] = 2;
		}
		if($_POST['id'] && is_numeric($_POST['id'])) {	//编辑
			$person->where("id='{$_POST['id']}'")->save($data);

			//日志记录
			$_fields = array(
				'name' => '检测姓名',
				'email' => '邮件地址',
				'mobile' => '手机号码',
				'scene' => '应用场景',
				'type' => '接收方式',
			);
			$_scene = array(
				'1' => '短信超量报警',
				'2' => '短信余额报警',
				'3' => '短信超量报警/短信余额报警',
			);
			$_type = array(
				'1' => '邮件',
				'2' => '手机',
				'3' => '邮件/手机',
			);
			$log_str = "修改检测人员 {$old['name']} 的 ";
			$log_str2 = '';
			if($data) {
				foreach(array('name','email','mobile','scene','type') as $key=>$val) {
					if($old[$val]!=$data[$val]) {
						if($val=='scene') {
							$log_str2 .= "{$_fields[$val]} 由 {$_scene[$old[$val]]} 变为 {$_scene[$data[$val]]},";
						} else if($val=='type') {
							$log_str2 .= "{$_fields[$val]} 由 {$_type[$old[$val]]} 变为 {$_type[$data[$val]]},";
						} else {
							$log_str2 .= "{$_fields[$val]} 由 {$old[$val]} 变为 {$data[$val]},";
						}
					}
				}
			}
			if($log_str2) $this->writelog($log_str.$log_str2,'sj_sms_alarm_person',$_POST['id'],__ACTION__ ,"","edit");
		} else {	//添加
			$data['addtime'] = time();
			$data['status'] = 1;
			$ret=$person->add($data);

			$this->writelog("短信系统报警配置：添加检测人员{$_POST['name']}",'sj_sms_alarm_person',$ret,__ACTION__ ,"","add");
		}

		//人员放入redis，便于短信平台使用，开始
		$tmp = $person->where("status=1")->select();
		$sms_alarm_send_email = array();
		$sms_alarm_send_mobile = array();
		$sms_balance_send_email = array();
		$sms_balance_send_mobile = array();
		if($tmp) {
			foreach($tmp as $key=>$val) {
				if($val['scene']==3 || $val['scene']==1) {	//短信超量报警
					if($val['type']==3 || $val['type']==1) {	//邮件
						$sms_alarm_send_email[] = $val['email'];
					}
					if($val['type']==3 || $val['type']==2) {	//手机
						$sms_alarm_send_mobile[] = $val['mobile'];
					}
				}
				if($val['scene']==3 || $val['scene']==2) {	//短信余额报警
					if($val['type']==3 || $val['type']==1) {	//邮件
						$sms_balance_send_email[] = $val['email'];
					}
					if($val['type']==3 || $val['type']==2) {	//手机
						$sms_balance_send_mobile[] = $val['mobile'];
					}
				}
			}
		}

		$redis = new Redis();
		$redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));

		$redis->set('sms_alarm_send_email',serialize($sms_alarm_send_email));
		$redis->set('sms_alarm_send_mobile',serialize($sms_alarm_send_mobile));
		$redis->set('sms_balance_send_email',serialize($sms_balance_send_email));
		$redis->set('sms_balance_send_mobile',serialize($sms_balance_send_mobile));
		//人员放入redis，便于短信平台使用，结束

		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Configuration/sms_alarm');
		$this->success("操作成功！");
	}


}


?>
