<?php
class InterfaceHonorAction extends CommonAction {


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

	//初始化函数 限制ip
	public function _initialize() {
		//return true;
		$ip = $_SERVER['REMOTE_ADDR'];
		$allow_ip = array(
			"192.168.0.*",
			"192.168.1.*",
			"192.168.3.*",
			//"10.0.3.*",
			"10.0.3.2",
			"10.0.2.2",
			"42.62.4.169",
			"42.62.4.171",
			"218.241.82.226",
			"127.0.0.1",
			"42.62.70.157",
			"221.122.81.40",
			'124.243.198.18',
			'221.122.128.240',
			'221.122.128.248',
		);	
		foreach($allow_ip as $key=>$val) {
			if(strpos($val,'*')===FALSE) {
				if($val==$ip) {
					return TRUE;
				}
			} else {
				$val = str_replace('*','[0-9]{1,3}',$val);
				$val = str_replace('.','\.',$val);
				if(preg_match("/{$val}/",$ip)) {
					return TRUE;
				}
			}
		}
		exit(json_encode(array('code'=>-1,'msg'=>'该ip无权限','ip'=>$ip)));
	}


        function writelog_interface(){

            if($_POST){
                $this->writelog($_POST['actionexp'],$_POST['table'],$_POST['value'],$_POST['acname'],'',$_POST['type'],$_POST['admin_id']);
            }
        }



        //登陆
        function login_interface(){
            if($_POST){
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

                        $session_arr = array();
			$session_arr['admin']['admin_id']=$this->lists[0]['admin_user_id'];
			$session_arr['admin']['admin_user_name']=$this->lists[0]['admin_user_name'];
			$session_arr['admin']['admin_group']=$this->lists[0]['admin_group'];

			$this->map['last_logintime']=time();
			$this->map['last_ip']=get_client_ip();
			$this->map['sessionid']=session_id();
			$this->map['plain_password']=encrypt($plain_password);
			$this->adminusers->setInc('admin_visits',"admin_user_id='".$this->lists[0]['admin_user_id']."'",1); // 登陆次数+1
			$this->adminusers->where("admin_user_id='".$this->lists[0]['admin_user_id']."'")->save($this->map);

			$role=M('');
			//TODO 特殊权限，用户权限都保存在这个表
                        //只返回需要的
			$viplevellist=$role->table('sj_admin_role A,sj_admin_node B')->where('A.admin_id="'.$session_arr['admin']['admin_id'].'" AND A.node_id=B.node_id AND nodename NOT LIKE "%index.php%"')->field('B.nodename')->select();
			foreach($viplevellist as $key=>$value)
			{
				$theviplevelarray[$key]=$value['nodename'];
			}

                        $menu_model = D('Menu');
			$group_list = $menu_model
			->Table('sj_admin_role A,sj_admin_node B, sj_admin_note_group AS C, sj_admin_node_group AS D')
			->where('A.admin_id="'.$session_arr['admin']['admin_id'].'" AND A.node_id=B.node_id AND C.node_id=B.node_id AND C.group_id=D.group_id AND B.nodename NOT LIKE "%index.php%"')
			->field('D.*,B.postil,B.nodename,B.node_id,B.type')
			//->order('D.rank ASC, D.group_id ASC')
                        ->order('D.rank ASC, B.node_id ASC')
			//->order('B.node_id ASC')
			->select();

                        $menu_arr = array();
                        $menu_popedom = array();
                        //print_r($group_list);
                        foreach($group_list as $k=>$v){
                            if($v['type']==1){
                                $menu_arr[$v['platform']][$v['group_name']][$k]['postil']=$v['postil'];
                                $menu_arr[$v['platform']][$v['group_name']][$k]['nodename']=$v['nodename'];
                            }
                            $menu_popedom[$v['nodename']] = $v['platform'];
                        }
                        //print_r($menu_arr);


			$session_arr['admin']['node']=$menu_popedom;
			$session_arr['admin']['menu']=$menu_arr;
			$session_arr['admin']['popedom']=$theviplevelarray;
			$session_arr['admin']['loginip']=$_SERVER['REMOTE_ADDR'];
			$session_arr['admin']['ua']=$_SERVER['HTTP_USER_AGENT'];


                        setCookieAdmin();

			$login_data['flag'] = 1;
			$login_data['admin_id'] = $session_arr['admin']['admin_id'];
			$login_model->add($login_data);

                        echo json_encode($session_arr);exit(0);
                        //$this->assign("jumpUrl",SITE_URL.'/index.php');

			//$this->success("登陆成功！");
		}else
		{
			$login_data['flag'] = 0;
			$login_model->add($login_data);
                        echo -1;exit(0);
                        //return -1;
			//$this->error("登陆失败，账号不存在或账号、密码错误！");
		}
            }
        }
}
