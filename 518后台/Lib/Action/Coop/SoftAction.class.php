<?php
class SoftAction extends CommonAction {

	protected function coop_exists($coop_id) {
		$coop_model = D('Coop.Cooperator');
		$test = $coop_model->where(array('id' => $coop_id, 'status' => 1))->select();
		return (count($test) == 1);
	}

	protected function prom_exists($prom_id) {
		$prom_model = D('Coop.Promotion');
		$test = $prom_model->where(array('id' => $prom_id, 'status' => 1))->select();
		return (count($test) == 1);
	}

	public function soft_search_add() {
		if (!isset($_POST['coop_id']) || !isset($_POST['package'])) {
			$this->ajaxReturn("", "参数错误！", 0);
		}
		$coop_id = $_POST['coop_id'];
		if (!$this->coop_exists($coop_id)) {
			$this->ajaxReturn("", "参数错误！", 0);
		}
		$package = trim($_POST['package']);
		$package = explode(',', $package);
		$software_model = D('Coop.Software');
		$succ = 0;
		$dupl = 0;
		$total = count($package);
		$now = time();
		$log='';
		foreach ($package as $p) {
			$test = $software_model->where(array('package' => $p, 'coop_id' => $coop_id))->select();
			if (count($test) > 0) {
				$dupl += 1;
				continue;
			}
			$data = array(
				'coop_id' => $coop_id,
				'package' => $p,
				'status' => 1,
				'created_at' => $now,
				'updated_at' => $now,
			);
			$affect = $software_model->add($data);
			if ($affect > 0) {
				$succ += 1;
				$log .= $affect.",";
			}
		}
		if($log){
			$this->writelog("coopid为".$_POST['coop_id']."添加报名为".trim($_POST['package'])."添加成功id为".$log."添加${total}个，已经存在${dupl}个，成功${succ}个！",'pu_coop_software',$log,__ACTION__ ,"","add");
		}
        $this->ajaxReturn("", "添加${total}个，已经存在${dupl}个，成功${succ}个！", 1);
	}

	public function soft_search_list() {
		$coop_id = escape_string($_GET['coop_id']);
		if (empty($coop_id)) {
			$this->error("参数错误！");
		}
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		$where = "(`status` = 1) AND (`hide` = 1)";
		if (isset($_GET['softname'])) {
			$softname = trim(escape_string($_GET['softname']));
			$where .= " AND (`softname` like '%${softname}%')";
			$this->assign('softname', $softname);
		}
		if (isset($_GET['package'])) {
			$package = trim(escape_string($_GET['package']));
			$where .= " AND (`package` like '%${package}%')";
			$this->assign('package', $package);
		}
		import("@.ORG.Page");
		$soft_model = M('soft');
		$count = $soft_model->where($where)->count();
		$page = new Page($count, 15);
		$soft_list = $soft_model->field("`softid`,`softname`,`package`,`version`,`version_code`,from_unixtime(`upload_tm`) as `created_at`,from_unixtime(`last_refresh`) as `updated_at`")->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$soft_list_mapped = array();
		foreach ($soft_list as $v)
			$soft_list_mapped[$v['softid']] = $v;
		$softids = array_keys($soft_list_mapped);
		$sids = implode(',', $softids);
		$soft_file_model = M('soft_file');
		$file_list = $soft_file_model->field('`softid`,`iconurl`')->where("`softid` in (${sids})")->select();
		foreach ($file_list as $v) {
			if (isset($soft_list_mapped[$v['softid']]))
				$soft_list_mapped[$v['softid']]['iconurl'] = $v['iconurl'];
		}
		$page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("coop_id", $coop_id);
        $this->assign("page", $show);
        $this->assign("soft_list" , array_values($soft_list_mapped));
        $this->display('soft_add');
	}

	
	public function soft_search_coop() {
		$coop_id = $_GET['coop_id'];
		
		$coop_model = D('Coop.Cooperator');
		$coop_info = $coop_model->where(array('id' => $coop_id, 'status' => 1))->find();
		
		if (empty($coop_id)) {
			$this->error("参数错误！");
		}
		if (!$coop_info) {
			$this->error("参数错误！");
		}
		$where = "(`status` = 1)";
		if (isset($_GET['softname'])) {
			$softname = trim(escape_string($_GET['softname']));
			$where .= " AND (`softname` like '%${softname}%')";
			$this->assign('softname', $softname);
		}
		if (isset($_GET['package'])) {
			$package = trim(escape_string($_GET['package']));
			$where .= " AND (`package` like '%${package}%')";
			$this->assign('package', $package);
		}
		$cids = explode(',', $coop_info['channel_id']);
		$feature_model = M('feature');
		foreach ($cids as $cid) {
			if (!empty($cid)) {
				$channel_where[] = 'hide=' . ($cid + 1024);
				$channel_where[] = "channel_id like '%,{$cid},%'";
				$channel_where[] = "(package in (select B.package from sj_feature A left join sj_feature_soft B on A.feature_id=B.feature_id where A.channel_id like '%,{$cid},%') AND hide=1)";
			}
		}
		$channel_where = implode(' OR ', $channel_where);
		if (!empty($channel_where)) {
			$where .= " AND ({$channel_where})";
		}
		
		import("@.ORG.Page");
		$soft_model = M('soft');
		$count = $soft_model->where($where)->count();
		$page = new Page($count, 15);
		$soft_list = $soft_model->field("`softid`,`softname`,`package`,`version`,`version_code`,from_unixtime(`upload_tm`) as `created_at`,from_unixtime(`last_refresh`) as `updated_at`")->where($where)->limit($page->firstRow.','.$page->listRows)->select();
		$soft_list_mapped = array();
		foreach ($soft_list as $v)
			$soft_list_mapped[$v['softid']] = $v;
		$softids = array_keys($soft_list_mapped);
		$sids = implode(',', $softids);
		$soft_file_model = M('soft_file');
		$file_list = $soft_file_model->field('`softid`,`iconurl`')->where("`softid` in (${sids})")->select();
		foreach ($file_list as $v) {
			if (isset($soft_list_mapped[$v['softid']]))
				$soft_list_mapped[$v['softid']]['iconurl'] = $v['iconurl'];
		}
		$page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("coop_id", $coop_id);
        $this->assign("page", $show);
        $this->assign("soft_list" , array_values($soft_list_mapped));
        $this->display('soft_add');
	}
	
	public function soft_display() {
		$coop_id = $_GET['coop_id'];
		if (empty($coop_id)) {
			$this->error("参数错误！");
		}
		if (!$this->coop_exists($coop_id)) {
			$this->error("参数错误！");
		}
		$map = array(
			'coop_id' => $coop_id
		);
		if (isset($_GET['status'])) {
			$map['status'] = $_GET['status'];
			$this->assign("status", intval($_GET['status']));
		}
		
		import("@.ORG.Page");
		$soft_model = D('Coop.Software');
		$count = $soft_model->where($map)->count();
		$page = new Page($count, 15);
		$soft_list = $soft_model->order("rank asc")->field("`id`,`package`,`rank`,`coop_id`,`status`,from_unixtime(`created_at`) as `created_at`,from_unixtime(`updated_at`) as `updated_at`")->where($map)->limit($page->firstRow.','.$page->listRows)->select();
		$package = array();
		$soft_list_mapped = array();
		foreach ($soft_list as $v) {
			$soft_list_mapped[$v['package']] = $v;
			$package[] = "'". $v['package']. "'";
		}
		$package = implode(',', $package);
		$temp_soft_model = M('soft');
		$temp_soft_list = $temp_soft_model->field("`softid`,`package`,`softname`,`version`,`version_code`")->where("`status` = 1 AND (`hide` = 1 or hide>=1024) AND `package` IN (${package})")->select();
		$softid_package = array();
		foreach ($temp_soft_list as $v) {
			if (isset($soft_list_mapped[$v['package']])) {
				$soft_list_mapped[$v['package']]['softname'] = $v['softname'];
				$soft_list_mapped[$v['package']]['version'] = $v['version'];
				$soft_list_mapped[$v['package']]['version_code'] = $v['version_code'];
				$softid_package[$v['softid']] = $v['package'];
			}
		}
		$softid = array_keys($softid_package);
		$softid = implode(',', $softid);
		$temp_soft_file_model = M('soft_file');
		$temp_soft_file_list = $temp_soft_file_model->field("`softid`,`iconurl`")->where("`softid` IN (${softid})")->select();
		foreach ($temp_soft_file_list as $v) {
			if (isset($softid_package[$v['softid']])) {
				$p = $softid_package[$v['softid']];
				if (isset($soft_list_mapped[$p]))
					$soft_list_mapped[$p]['iconurl'] = $v['iconurl'];
			}
		}
		$page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $this->assign("coop_id", $coop_id);
        $this->assign("page", $show);
        $this->assign("soft_list" , array_values($soft_list_mapped));
        $this->display('soft_list');
	}
    function modifysoft() {
		$soft_model = D('Coop.Software');
        $id = $_GET['id'];
        $status = $_GET['status'];
        $data['updated_at']=time();
        if($status == 2){
            $data['status'] = 0;
        }elseif($status == 1){
            $data['status'] = 1;
        }
        $log = $this->logcheck(array('id'=>$id),'pu_coop_software',$data,$soft_model);
        $affect = $soft_model -> where(array('id' => $id)) ->save($data);
        if($affect){
        	//$this->writelog("coop/soft/modifysoft,应用ID为：[".$id."]status状态改为：".$status);
        	$this->writelog("天翼推广-软件列表".$log,'pu_coop_software',$id,__ACTION__ ,"","edit");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Soft/soft_display/coop_id/'. $_GET['coop_id']);
             $this -> success("软件状态修改成功！<br/> 用户无法再次选择此软件");
        }else{
             $this -> error("软件删除失败！！");
        }
    }
    function tcl_rank() {
    	if(!empty($_GET['id'])) {
    		$soft_model = D('Coop.Software');
			$coop_id = $_GET['coop_id'];
    		$ret = $soft_model->order('`rank` asc')->where('`coop_id` = '.$coop_id)->findall();
    		$i=1;
    		$rank = $_POST['num'];
    		if($rank <= 0){
    			$this->error("参数错误,请输入正整数！");
    		}
    		$id = escape_string($_GET['id']);
    		$c = count($ret);
    		$log2 = $this->logcheck(array('id'=>$id),'pu_coop_software',array('rank' => $rank),$soft_model);
    		foreach($ret as $key => $value) {
    			if($value['id'] == $_GET['id'] ) {
    				$r = $rank > $c ? $c : $rank;
    				//og2 = $this->logcheck(array('id'=>$id),'pu_coop_software',array('rank' => $r),$soft_model);
    				$soft_model->where("`id` = $id")->save(array('rank' => $r));
    			} else {
    				//og2 = $this->logcheck(array('id'=>$value['id']),'pu_coop_software',array('rank' => ($i==$rank? ++$i : $i)),$soft_model);
    				$soft_model->where("`id` = {$value['id']}")->save(array('rank' => ($i==$rank? ++$i : $i)));
    				$i++;
    			}

    		}
    		$this->writelog("天翼推广-软件列表修改排序ID为：$id".$log2,'pu_coop_software',$id,__ACTION__ ,"","edit");
    		//$this->writelog("修改排序ID为：".$id."排名为：".$rank);
    		$this -> success("修改成功");
    	}	
    }
}
