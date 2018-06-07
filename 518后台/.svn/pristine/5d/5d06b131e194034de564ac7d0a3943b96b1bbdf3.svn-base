<?php
class UserAction extends CommonAction {

	protected function validate_parameters($coop_id, $username, $password) {
		if (empty($coop_id) || empty($username) || empty($password))
			return false;
		if (strlen($username) < 4 || strlen($password) < 8)
			return false;
		return true;
	}
		
	protected function coop_exists($coop_id) {
		$coop_model = D('Coop.Cooperator');
		$test = $coop_model->where(array('id' => $coop_id, 'status' => 1))->select();
		return (count($test) == 1);
	}
	
	protected function user_exists($coop_id, $username) {
		$user_model = D('Coop.User');
		$test = $user_model->where(array('coop_id' => $coop_id, 'username' => $username))->select();
		return (count($test) > 0);
	}

	public function user_add() {
		$coop_id = $_GET['coop_id'];
		$username = $_GET['username'];
		$password = $_GET['password'];
		if (!$this->validate_parameters($coop_id, $username, $password)) {
			$this->error("用户名长度至少4位，密码长度至少8位！");
		}
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		if ($this->user_exists($coop_id, $username)) {
			$this->error("用户${username}已经存在！");
		}
		$user_model = D('Coop.User');
		$now = time();
		$data = array(
			'coop_id' => $coop_id,
			'username' => $username,
			'password' => $password,
			'status' => 1,
			'created_at' => $now,
			'updated_at' => $now,
		);
		$affect = $user_model->add($data);
		if ($affect > 0) {
			$this->writelog("添加用户：".$username,'pu_coop_user',$affect,__ACTION__ ,"","add");
			$this->assign('jumpUrl', '/index.php/'. GROUP_NAME. '/User/user_list/coop_id/'. $coop_id);
            $this->success("添加成功！");
		}
		else {
			$this->error("添加失败！");
		}
	}
	
	public function user_modify_status() {
		if (!isset($_GET['coop_id']) || !isset($_GET['username']) || !isset($_GET['status'])) {
			$this->error("参数错误！");
		}
		$coop_id = $_GET['coop_id'];
		$username = $_GET['username'];
		$status = escape_string($_GET['status']);
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		$user_model = D('Coop.User');
		$str=($status==1)?'启用':'停用';
		$affect = $user_model->where(array('coop_id' => $coop_id, 'username' => $username))->save(array('status' => $status, 'updated_at' => time()));
		if ($affect > 0) {
			$this->writelog("天翼推广-用户管理-修改用户状态，{$str}用户名为{$username}的用户。",'pu_coop_user',"coop_id:{$coop_id}",__ACTION__ ,"","edit");
			$this->assign('jumpUrl', '/index.php/'. GROUP_NAME. '/User/user_list/coop_id/'. $coop_id);
            $this->success("修改成功！");
		}else {
			$this->error("修改失败！");
		}
	}

	public function user_list() {
		$coop_id = $_GET['coop_id'];
		if (empty($coop_id)) {
			$this->error("参数错误！");
		}
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		import("@.ORG.Page");
		$user_model = D('Coop.User');
        $count = $user_model->where(array('coop_id' => $coop_id))->count();
        $page = new Page($count, 15);
        $user_list = $user_model->field("`username`,`password`,`status`,from_unixtime(`created_at`) as `created_at`,from_unixtime(`updated_at`) as `updated_at`")->where(array('coop_id' => $coop_id))->limit($page->firstRow.','.$page->listRows)->select();
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("coop_id", $coop_id);
        $this->assign("page", $show);
        $this->assign("user_list" , $user_list);
        $this->display('user_list');
	}
}

