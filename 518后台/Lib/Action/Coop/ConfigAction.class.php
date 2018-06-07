<?php
class ConfigAction extends CommonAction {

	protected function coop_exists($coop_id) {
		$coop_model = D('Coop.Cooperator');
		$test = $coop_model->where(array('id' => $coop_id, 'status' => 1))->select();
		return (count($test) == 1);
	}
	
	protected function config_exists($coop_id, $key) {
		$config_model = D('Coop.Config');
		$test = $config_model->where(array('coop_id' => $coop_id, 'key' => $key))->select();
		return (count($test) > 0);
	}

	public function config_add() {
		if (!isset($_GET['coop_id']) || !isset($_GET['key']) || !isset($_GET['value'])) {
			$this->error("参数错误！");
		}
		$coop_id = trim($_GET['coop_id']);
		$key = trim($_GET['key']);
		$value = trim($_GET['value']);
		if (strlen($key) < 1 || strlen($value) < 1) {
			$this->error("参数错误！");
		}
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		if ($this->config_exists($coop_id, $key)) {
			$this->error("字段${key}已经存在！");
		}
		$config_model = D('Coop.Config');
		$now = time();
		$data = array(
			'coop_id' => $coop_id,
			'key' => $key,
			'value' => $value,
			'status' => 1,
			'created_at' => $now,
			'updated_at' => $now,
		);
		$affect = $config_model->add($data);
		if ($affect > 0) {
			$this->writelog("天翼推广添加数据，数据内容".print_r($data,true),'pu_coop_config',$affect,__ACTION__ ,"","add");
			$this->assign('jumpUrl', '/index.php/'. GROUP_NAME. '/Config/config_list/coop_id/'. $coop_id);
            $this->success("添加成功！");
		}
		else {
			$this->error("添加失败！");
		}
	}

	public function config_edit() {
		if (!isset($_GET['coop_id']) || !isset($_GET['key'])) {
			$this->error("参数错误！");
		}
		$coop_id = trim($_GET['coop_id']);
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		$config_model = D('Coop.Config');
		$data = array('updated_at' => time());
		$key = trim($_GET['key']);
		if (isset($_GET['value']))
			$data['value'] = trim($_GET['value']);
		if (isset($_GET['status']))
			$data['status'] = trim($_GET['status']);
		$log = $this->logcheck(array('coop_id'=>$coop_id),'pu_coop_config',$data,$config_model);
		$affect = $config_model->where(array('coop_id' => $coop_id, 'key' => $key))->save($data);
		if ($affect > 0) {
			if(isset($_GET['status'])){
				$str=($_GET['status']==1)?'启用':'停用';
				$this->writelog("天翼推广-配置-{$str}了coop_id:{$coop_id}的配置",'pu_coop_config',"coop_id:{$coop_id}",__ACTION__ ,"","edit");
			}else{
				$this->writelog("天翼推广-配置-修改数据键为{$key}.".$log,'pu_coop_config',"coop_id:{$coop_id}",__ACTION__ ,"","edit");
			}
			//$this->writelog("天翼推广修改数据，coop_id为".$coop_id.",KEY为".$key);
			$this->assign('jumpUrl', '/index.php/'. GROUP_NAME. '/Config/config_list/coop_id/'. $coop_id);
            $this->success("修改成功！");
		}else {
			$this->error("修改失败！");
		}
	}

	public function config_list() {
		$coop_id = $_GET['coop_id'];
		if (empty($coop_id)) {
			$this->error("参数错误！");
		}
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		import("@.ORG.Page");
		$config_model = D('Coop.Config');
        $count = $config_model->where(array('coop_id' => $coop_id))->count();
        $page = new Page($count, 15);
        $config_list = $config_model->field("`key`,`value`,`status`,from_unixtime(`created_at`) as `created_at`,from_unixtime(`updated_at`) as `updated_at`")->where(array('coop_id' => $coop_id))->limit($page->firstRow.','.$page->listRows)->select();
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("coop_id", $coop_id);
        $this->assign("page", $show);
        $this->assign("config_list" , $config_list);
        $this->display('config_list');
	}
	
}

