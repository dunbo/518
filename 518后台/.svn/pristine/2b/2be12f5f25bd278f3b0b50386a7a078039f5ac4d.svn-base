<?php
/**
 * 福利管理
 * 2017/11/7
 */
class WelfareAction extends CommonAction {
	
	public function list_type()
	{
		$model	=	D('Sj.Welfare');
		$where	=	array('status' => 1);
		$count	=	$model->table('fl_welfare_type')->where($where)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('fl_welfare_type')->where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('pos asc,id desc') -> select();
		$time = time();
		$where = " status = 1 ";
		$where_e = $where." and start_tm >= '{$time}' ";
		$where_n = $where." and start_tm <= '{$time}' and {$time} < end_tm ";
		$where_f = $where." and end_tm < '{$time}' ";
		foreach ($list as $key => $val) {
			$_where = " typeid = {$val['id']} and ";
			$count_e = $model->table('fl_welfare')->where($_where.$where_e)->count();
			$count_n = $model->table('fl_welfare')->where($_where.$where_n)->count();
			$count_f = $model->table('fl_welfare')->where($_where.$where_f)->count();
			$list[$key]['count_e'] = $count_e;
			$list[$key]['count_n'] = $count_n;
			$list[$key]['count_f'] = $count_f;
		}
		$url = self::get_url();
		$this->assign('url', $url);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}
	
	//添加福利类型
	public function add_type()
	{
		if (!empty($_POST)) {
			//验证
			$model		=	D('Sj.Welfare');
			$name		=	trim($_POST['name']);
			$img_position	=	$_POST['img_position'];
			$pos		=	intval($_POST['pos']);
			$list_num	=	intval($_POST['list_num']);
			$time		=	time();
			//表单验证
			if(!$name) {
				$this->error('请填写福利类型名称');
			}
			$date	=	date("Ym/d/");
			if($img_position!=1){
				if(empty($_FILES['image']['tmp_name'])){
					$this->error('请上传福利类型图片');
				}
				if(!preg_match("/\.(jpg|png|gif)$/", $_FILES['image']['name'])){
					$this->error('福利类型图片格式错误');
				}
				$config['multi_config']['image'] = array(
					'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
					'saveRule'	 =>	'getmsec',
					'img_p_size' =>	1024 * 200,
				);				
			}
			
			$data = array(
				'name'		=>	$name,
				'img_position' => $img_position,
				'pos'		=>	$pos,
				'list_num'	=>	$list_num,
				'status'	=>	1,
				'update_tm'	=>	$time,
				'create_tm'	=>	$time,	
			);
			if (!empty($config['multi_config'])) {
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $val) {
					$data[$val['post_name']] = $val['url'];
				}
			}
			$ret = $model -> table('fl_welfare_type') -> add($data);
			if( $ret ) {
				$this -> writelog("添加福利类型id为{$ret}的福利类型",'fl_welfare_type',$ret,__ACTION__ ,'','add');
				$this->success('操作成功');
			}else {
				$this->error('操作失败');
			}	
		}else {
			$this->display();
		}
	}
	
	//编辑福利类型
	public function edit_type()
	{
		$id		=	$_REQUEST['id'] ? (Int)$_REQUEST['id'] : 0;
		$model	=	D('Sj.Welfare');
		if($_POST) {
			$name		=	trim($_POST['name']);
			$img_position	=	$_POST['img_position'];
			$old_image	=	trim($_POST['old_image']);
			$pos		=	intval($_POST['pos']);
			$list_num	=	intval($_POST['list_num']);
			$time		=	time();
			if(!$name) {
				$this->error('请填写福利类型名称');
			}
			$date	=	date("Ym/d/");
			if($img_position!=1){
				if(empty($_FILES['image']['tmp_name']) && empty($old_image)){
					$this->error('请上传福利类型图片');
				}
				if(!empty($_FILES['image']['tmp_name'])){
					if(!preg_match("/\.(jpg|png|gif)$/", $_FILES['image']['name'])){
						$this->error('福利类型图片格式错误');
					}
					$config['multi_config']['image'] = array(
						'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
						'saveRule'	 =>	'getmsec',
						'img_p_size' =>	1024 * 200,
					);
				}
			}
			
			$data = array(
				'name'		=>	$name,
				'img_position' => $img_position,
				'pos'		=>	$pos,
				'list_num'	=>	$list_num,
				'update_tm'	=>	$time,
			);
			if (!empty($config['multi_config'])) {
				$list = $this->_uploadapk(0, $config);
				foreach($list['image'] as $val) {
					$data[$val['post_name']] = $val['url'];
				}
			}
			$where		=	array('id' => $id);
			$log_result	=	$this->logcheck(array('id'=>$id),'fl_welfare_type',$data,$model);
			$ret		=	$model -> table('fl_welfare_type')->where($where) -> save($data);
			if($ret){
				$this -> writelog("编辑福利类型id为{$id}".$log_result,'fl_welfare_type',$id,__ACTION__ ,'','edit');
				$this -> success("操作成功");
			}else{
				$this -> error("操作失败");
			}
		}else {
			$where	=	array('id'=>$id);
			$rows	=	$model->table('fl_welfare_type')->where($where)->find();
			$this -> assign('rows',$rows);
			$this -> display('add_type');
		}
	}
	
	//批量修改福利数
	public function batch_list_num()
	{
		$ids		=	trim($_GET['ids']);
		$list_num	=	intval($_GET['batch_list_num']);
		$model		=	D('Sj.Welfare');
		if(!$ids) {
			$this->error('参数有误');
		}
		$q_sql = "select list_num from fl_welfare_type where id in ($ids) ";
		$data_num = $model->query($q_sql);
		foreach ($data_num as $val){
			$num_log .= $val['list_num'].',';
		}
		$num_log = trim($num_log, ',');
		$sql = "update fl_welfare_type set list_num={$list_num} where id in ($ids) ";
		$ret = $model->execute($sql);
		if( $ret ) {
			$this -> writelog("批量修改了福利类型ids为{$ids}由{$num_log}改为{$list_num}",'fl_welfare_type',$ids,__ACTION__ ,'','edit');
			$this->success('操作成功');
		}else {
			$this->error('操作失败');
		}
	}
	
	//删除福利类型
	public function del_type()
	{
		$id		=	$_REQUEST['id'] ? (Int)$_REQUEST['id'] : 0;
		$map	=	array('status' => 0);
		$model	=	D('Sj.Welfare');
		$ret	=	$model -> table('fl_welfare_type') -> where(array('id'=>$id)) -> save($map);
		if($ret){
			$this -> writelog("删除福利类型id为{$id}的福利类型",'fl_welfare_type',$id,__ACTION__ ,'','delete');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	}
	
	//福利列表
	public function list_fl(){
		$srch_type	=	$_GET['srch_type'] ? trim($_GET['srch_type']) : '';
		$typeid		=	$_GET['typeid'] ? (Int)$_GET['typeid'] : 0;
		$s_name		=	$_GET['s_name'] ? trim($_GET['s_name']) : '';
		$s_package	=	$_GET['s_package'] ? trim($_GET['s_package']) : '';
		$s_softname	=	$_GET['s_softname'] ? trim($_GET['s_softname']) : '';
		$s_start_tm	=	$_GET['s_start_tm'] ? strtotime($_GET['s_start_tm']) : '';
		$s_end_tm	=	$_GET['s_end_tm'] ? strtotime($_GET['s_end_tm']) : '';
		$time		=	time();
		
		$model	=	D('Sj.Welfare');
		$where	=	" typeid = {$typeid} and status = 1 ";
		
		$s_name		&&	$where .= " and name like '%{$s_name}%' ";
		$s_package	&&	$where .= " and package = '{$s_package}' ";
		$s_softname	&&	$where .= " and softname = '{$s_softname}' ";
		
		$s_start_tm	&&	$where .= " and start_tm >= {$s_start_tm} ";
		$s_end_tm	&&	$where .= " and end_tm <= {$s_end_tm} ";
		$order = ' pos asc';
		if( !$s_name && !$s_package && !$s_softname && !$s_start_tm && !$s_end_tm ) {
			switch ($srch_type) {
				case 'e':
					//未开始
					$where .= " and start_tm >= {$time} ";
					$order .= ",end_tm asc";
					break;
				case 'n':
					//当前
					$where .= " and start_tm <= {$time} and {$time} < end_tm ";
					$order .= ",end_tm asc";
					break;
				case 'f':
					//过期
					$where .= " and end_tm < {$time} ";
					$order .= ",end_tm desc";
					break;
				default:
					$where .= " and start_tm <= {$time} and {$time} < end_tm ";
					$order .= ",end_tm asc";
					$srch_type = 'n';
					break;
			}
		}
		$where_go['_string'] = $where;
		$count	=	$model->table('fl_welfare')->where($where)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('fl_welfare')->where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order($order) -> select();
		foreach($list as $key => $val){
			$list[$key]['list_pic'] = json_decode($val['list_pic'], true);
		}
		$wfr_type	=	$model->table('fl_welfare_type')->where(array('status'=>1))->select();
		$type_name = '';
		foreach ($wfr_type as $val) {
			if($val['id'] == $typeid) {
				$type_name = $val['name'];
			}
		}
		$this->assign('time', $time);
		$this->assign('type_name', $type_name);
		$this->assign('typeid', $typeid);
		$this->assign('wfr_type', $wfr_type);
		$this->assign('s_name', $s_name);
		$this->assign('s_package', $s_package);
		$this->assign('s_softname', $s_softname);
		$this->assign('s_start_tm', $_GET['s_start_tm']);
		$this->assign('s_end_tm', $_GET['s_end_tm']);
		$this->assign('srch_type', $srch_type);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}
	
	//添加福利
	public function add_fl(){
		$id		=	$_GET['id'] ? (Int)$_GET['id'] : 0;
		$model	=	D('Sj.Welfare');
		if($_POST) {
			$table	=	"fl_welfare";
			$ret	=	$model -> welfare_add_do($table);
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						if(in_array($val['post_name'],array('list_pic','list_pic2','list_pic3'))){
							$data['list_pic'][$val['post_name']] = $val['url'];
							continue;
						}
						$data[$val['post_name']] = $val['url'];
					}
				}
				if(is_array($data['list_pic']) && !empty($data['list_pic'])){
					$data['list_pic'] = json_encode($data['list_pic']);
				}
				$res = $model -> table($table) -> add($data);
				if($res){
					$this -> writelog("已添加id为{$res}的福利",$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$wfr_type = $model->table('fl_welfare_type')->where(array('status'=>1))->select();
			$where	=	array('id'=>$id);
			$this -> assign('typeid', $id);
			$this -> assign('wfr_type', $wfr_type);
			$this -> display();
		}
	}
	
	//添加福利
	public function edit_fl(){
		$id		=	$_GET['id'] ? (Int)$_GET['id'] : 0;
		$model	=	D('Sj.Welfare');
		$table	=	"fl_welfare";
		if($_POST) {
			$ret = $model -> welfare_add_do($table);
			if($ret['code'] == 1){
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						if(in_array($val['post_name'], array('list_pic','list_pic2','list_pic3'))){
							$data['list_pic'][$val['post_name']] = $val['url'];
							continue;
						}
						$data[$val['post_name']] = $val['url'];
					}
				}
				if(is_array($data['list_pic']) && !empty($data['list_pic'])){
					$data['list_pic'] = json_encode($data['list_pic']);
				}
				$where		=	array('id'=>$_POST['id']);
				$log_result	=	$this->logcheck(array('id'=>$_POST['id']),'fl_welfare',$data,$model);
				$res		=	$model -> table($table)->where($where) -> save($data);
				if($res){
					$this -> writelog("已编辑id为{$_POST['id']}".$log_result,$table,$_POST['id'],__ACTION__ ,'','edit');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else {
			$where	=	array('id'=>$id);
			$rows	=	$model->get_welfare_info($id);
			$wfr_type = $model->table('fl_welfare_type')->where(array('status'=>1))->select();
			if($rows['list_pic']!='' && $list_pic=json_decode($rows['list_pic'], true)){
				$rows['list_pic'] = $list_pic;
			}
			$this -> assign('wfr_type',$wfr_type);
			$this -> assign('rows',$rows);
			$this -> assign('typeid',$rows['typeid']);
			$this -> display('add_fl');
		}
	}
	
	//删除福利
	public function del_fl(){
		$id		=	$_REQUEST['id'] ? (Int)$_REQUEST['id'] : 0;
		$map	=	array('status' => 0);
		$model	=	D('Sj.Welfare');
		$ret	=	$model -> table('fl_welfare') -> where(array('id'=>$id)) -> save($map);
		if($ret){
			$this -> writelog("删除福利id为{$id}的福利类型",'fl_welfare_type',$id,__ACTION__ ,'','delete');
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
	}
	
	public function pub_soft_val(){
		$package	=	trim($_GET['package']);
		$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if( $pkg_data ) {
			exit( json_encode(array('code'=>1,'msg'=>'','softname'=>$pkg_data['softname'])) );
		}else {
			exit( json_encode(array('code'=>0,'msg'=>'包名不存在')) );
		}
	}
	
	public static function get_url(){
		if( C('is_test') == 1 ) {
			$url = "http://m.test.anzhi.com/lottery/welfare/index.php";
		}else {
			$url = "http://m.anzhi.com/lottery/welfare/index.php";
		}
		return $url;
	}

}
?>
