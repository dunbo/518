<?php

/**
 * Desc:   软件评论
 */
class SoftCommentAction extends CommonAction {
	private $url = 'http://m.test.anzhi.com/comment_soft.php';
	public function index(){
		$admin_id = $_SESSION['admin']['admin_id'];
		$this->assign('admin_id',$admin_id);
		if($_POST){
			$model = D('soft');
			$package = $_POST['package'];
			$soft = $model->where(array('package'=>$package,'hide'=>1,'status'=>1))->find();
			if(!$soft){
				$this->error('软件不存在');
			}
			
			$len = count($_POST['content']);
			if($len==0){
				$this->error('没有评论内容');
			}else{
				foreach($_POST['content'] as $v){
					$con = trim($v);
					if(empty($con)){
						$this->error('评论内容为空');
					}
				}
			}
			$vals['sid'] = session_id();
			$vals['admin_id'] = $_POST['ad'];
			$vals['softid'] = $soft['softid'];
			$vals['time_rule'] = $_POST['time_rule']?$_POST['time_rule']:0;
			$vals['package'] = $_POST['package'];
			$vals['content'] = json_encode($_POST['content']);
			$vals['ip'] = $_SERVER['REMOTE_ADDR'];
			$res = httpGetInfo($this->url,$vals,'comment_soft.log');
			$res = json_decode($res,true);
			if($res['code']!=200){
				$this->error('评论失败');
			}else{
				$comment = implode(";",$_POST['content']);
				$this->writelog("评论了软件{$package}内容为:{$comment}", 'sj_soft_comment', $package,__ACTION__ ,"","edit");
				$this->success('评论成功');
			}
		}
		$this->assign('from','index');
		$this->display();
	}

	public function comment_history(){
		$where = array();
		$model = M('');
		if(!empty($_GET['begintime'])){
			$get_start = strtotime($_GET['begintime']);
			$where['a.add_tm'] = array('exp'," >= '{$get_start}'");
			$this->assign('begintime',$_GET['begintime']);
		}
		if(!empty($_GET['endtime'])){
			$get_end = strtotime($_GET['endtime']);
			if($where['a.add_tm']){
				$where['a.add_tm'][1] .= " and a.add_tm <= '{$get_end}'";
			}else{
				$where['a.add_tm'] = array('exp'," <= '{$get_end}'");
			}
			$this->assign('endtime',$_GET['endtime']);
		}

		if(!empty($_GET['softname'])){
			$info = $model->table('sj_soft')->where(array('softname'=>array('exp', " like '%{$_GET['softname']}%'")))->field('package,softname')->select();
			$s_pack = array();
			if($info){
				foreach($info as $k=>$v){
					$s_pack[] = $v['package'];
				}
				$s_pack_str = implode("','",$s_pack);
				$where['a.package'] = array('exp', " in ('{$s_pack_str}')");
			}

			$this->assign('softname',$_GET['softname']);
		}
		if(!empty($_GET['package'])){
			$where['a.package'] = $_GET['package'];
			$this->assign('package',$_GET['package']);
		}
		if(!empty($_GET['admin_user_name'])){
			$where['c.admin_user_name'] = array('exp', " like '%{$_GET['admin_user_name']}%'");
			$this->assign('admin_user_name',$_GET['admin_user_name']);
		}
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;

		$total = $model->table('sj_comment_import as a')->join('sj_admin_users as c on a.admin_id = c.admin_user_id')->where($where)->field('a.*,c.admin_user_name')->count();
		import('@.ORG.Page2');
		$param = http_build_query($_GET);
		$Page = new Page($total,$limit,$param);
		$Page->rollPage = 10;
		$Page->setConfig('header','篇记录');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','尾页');
		$list = $model->table('sj_comment_import as a')->join('sj_admin_users as c on a.admin_id = c.admin_user_id')->where($where)->field('a.*,c.admin_user_name')->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
//		echo $model->getLastSql();
//		var_dump($list);
		if($list){
			$package = array();
			$refresh = false;
			foreach($list as $k=>$v){
				$package[] = $v['package'];
				if ($v['status'] == 1) {
					$refresh = true;
				}
			}
			$this->assign('refresh', $refresh);
			$package_str = implode("','",$package);
			$soft = get_table_data(array('package'=>array('exp'," in ('{$package_str}')"),'hide'=>1,'status'=>1),'sj_soft','package','package,softname');
			$this->assign('soft',$soft);
		}

		$this -> assign('page', $Page->show());
		$this->assign('list',$list);
		$this->assign('from','comment_history');

		$this->display();
	}

	public function import_comment(){
		if ($_POST) {
			$rs = $this->read_csv();
			if($rs['code']==0){
				echo json_encode($rs);
				exit(0);
			}
		}
	}

	function read_csv() {
		$file = $_FILES['hiddenFile']['tmp_name'];
		$tmp_houzhui = $_FILES['hiddenFile']['name'];
		$tmp_arr = explode('.', $tmp_houzhui);
		$houzhui = array_pop($tmp_arr);
		if (strtoupper($houzhui) != 'CSV') {
			$this->ajaxReturn(array('code'=>0,'msg'=>'请上传格式为csv的文件'), '', 1);
		}

		$arr = array();
		$handel = fopen($file, "r");
		$i = 0;
		$package = array();
		while (($num_arr = $this->mygetcsv($handel, null, ",")) !== FALSE) {
			//标题行不写入
			if ($i != 0) {
				$package[] = $this->convert_encoding($num_arr[0]);
				foreach($num_arr as $k=>$v){
					$v = $this->convert_encoding($v);
					$num_arr[$k] = $v;
				}
				$arr[$i] =  $num_arr;
			}
			$i++;
		}
		//处理方法
		if(count($arr)==0){
			$this->ajaxReturn(array('code'=>0,'msg'=>'文件内容为空'), '', 1);
		}
		list($fail_num,$correctnum,$r_data) = $this->save_import_comment($package,$arr);
		fclose($handel);
		//var_dump($r_data);
		if($fail_num>0){
			$this->ajaxReturn(array($fail_num,$correctnum,$r_data), '导入失败！', 1);
		}else{
			$task_client = get_task_client();
			foreach($arr as $k=>$v){
				$task_data = array();
				$task_data['sid'] = session_id();
				$task_data['package'] = $v[0];
				$task_data['content'] = json_encode((array)$v[1]);
				$task_data['time_rule'] = $v[2];
				$task_data['admin_id'] = $_SESSION['admin']['admin_id'];
				$task_data['ip'] = $_SERVER['REMOTE_ADDR'];
				$task_client->doBackground('comment_soft', json_encode($task_data));
			}
			$this->ajaxReturn(array('code'=>2,'msg'=>'导入成功'), '', 1);
		}
	}

	public function save_import_comment($package,$data){
		$failnum = 0;
		$correctnum = 0;
		$package_str = implode("','",$package);
		$model = M('');
		$soft = $model->table('sj_soft')->where(array('hide'=>1,'status'=>1,'package'=>array('exp'," in ('{$package_str}')")))->field('softid,softname,package')->findAll();
		$has_pack = array();
		$soft_info = array();
		foreach($soft as $k=>$v){
			$soft_info[$v['package']] = $v;
			$has_pack[] = $v['package'];
		}
		$no_pack = array_flip(array_diff($package,$has_pack));
		$fail_arr = array();
		foreach($data as $k=>$v){
			$bo = true;
			$v['error'] = '';
			if(empty($v[0])){
				$v['error'] .= '包名为空;';
				$bo = false;
			}elseif(isset($no_pack[$v[0]])){
				$v['error'] .= '包名错误;';
				$bo = false;
			}
			$v[1] = trim($v[1]);
			if(empty($v[1])){
				$v['error'] .= '评论为空;';
				$bo = false;
			}else{
				$len = mb_strlen($v[1],'utf-8');
				if($len > 300){
					$v['error'] .= '评论过长;';
					$bo = false;
				}
			}

			if(empty($v[2])||!in_array($v[2],array(1,2))){
				$v['error'] .= '时间显示规则填写错误;';
				$bo = false;
			}
			if(!empty($v[1])){
				$v[1] = mb_substr($v[1],0,10,'utf-8');//
			}
			if(!$bo){
				$fail_arr[]  = $v;
				$failnum++;
				continue;
			}
			$correctnum++;
		}
		return array($failnum,$correctnum,$fail_arr);
	}
}