<?php
class SignTaskSoftAction extends CommonAction{
	function task_soft_list()
	{
		$aid = $_GET['aid'];
		$atp = $_GET['atp'];//活动类型 0为签到日期任务软件 1为补签任务软件 
		if( !$aid ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.SignTaskSoft');
		$where	=	array(
				'status'=>	array('in','1,2'),
		);
		if( $atp == 1 ) {
			$where['mid'] = $aid;
		}else {
			$where['did'] = $aid;
		}
		$count	=	$model->getcount($where);
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 10, $param);
		$show	=	$Page->show();
		$order	=	'id desc';
		$list	=	$model->table('qd_task_soft') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		$this->assign('aid', $aid);
		$this->assign('atp', $atp);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();	
	}
	
	//输入包名智能验证包名是否存在
	public function pub_task_soft_val()
	{
		$package	=	trim($_GET['package']);
		$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if( $pkg_data ) {
			exit( json_encode(array('code'=>1,'msg'=>'','softname'=>$pkg_data['softname'])) );
		}else {
			exit( json_encode(array('code'=>0,'msg'=>'包名不存在')) );
		}
	}
	
	
	//添加任务软件库
	public function task_soft_add(){
		$aid = (Int)$_GET['aid'];
		$atp = (Int)$_GET['atp'];//活动类型 0为签到日期任务软件 1为补签任务软件 
		if($_POST){
			$model	=	D('Sj.SignTaskSoft');
			$table	=	"qd_task_soft";
			$ret	=	$model -> task_soft_validate($table);
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
// 				if (!empty($config['multi_config'])) {
// 					$list = $this->_uploadapk(0, $config);
// 					foreach($list['image'] as $val) {
// 						$data[$val['post_name']] = $val['url'];
// 					}
// 				}
				$res = $model -> table($table) -> add($data);
				if($res){
					$this -> writelog("已添加id为{$res}的任务软件库中",$table,$res,__ACTION__ ,'','add');
					$this -> success("操作成功");
				}else{
					$this -> error("操作失败");
				}
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this->assign('aid', $aid);
			$this->assign('atp', $atp);
			$this -> display();
		}
	}
	
	//编辑任务软件
	function task_soft_edit()
	{
		$id		=	$_GET['id'] ? (Int)$_GET['id'] : 0;
		$model	=	D('Sj.SignTaskSoft');
		if($_POST) {
			$table = "qd_task_soft";
			$ret = $model -> task_soft_validate($table);
			if($ret['code'] == 1){
				$config	=	$ret['config'];
				$data	=	$ret['data'];
// 				if (!empty($config['multi_config'])) {
// 					$list = $this->_uploadapk(0, $config);
// 					foreach($list['image'] as $val) {
// 						$data[$val['post_name']] = $val['url'];
// 					}
// 				}
				$where	=	array('id'=>$_POST['id']);
				$res	=	$model -> table($table)->where($where) -> save($data);
				if($res){
					$this -> writelog("已编辑id为{$id}的任务软件",$table,$id,__ACTION__ ,'','edit');
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
			$list	=	$model->table('qd_task_soft')->where($where)->find();
			if( $list['did'] ) {
				$atp = 0;
				$aid = $list['did'];
			}elseif( $list['mid'] ) {
				$atp = 1;
				$aid = $list['mid'];
			}else {
				$this->error('数据有误');
			}
			
			$this->assign('aid', $aid);
			$this -> assign('atp', $atp);
			$this -> assign('list',$list);
			$this -> display('task_soft_add');
		}
	}
	
	//任务软件系列操作
	function task_soft_operation()
	{
		$id = !empty($_GET['id']) ? (Int)$_GET['id'] : 0;
		$operation = !empty($_GET['operation']) ? $_GET['operation'] : '';
		$oper_arr = array(
				'del'	=>	0, //删除
				'qd'	=>	1, //启动
				'stp'	=>	2, //停用
		);
		$oper_arr_tran = array(
				'del'	=>	'删除', //删除
				'qd'	=>	'启动', //启动
				'stp'	=>	'停用', //停用
		);
		if( !$id || !$operation || !array_key_exists($operation, $oper_arr) ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.SignTaskSoft');
		$table	=	'qd_task_soft';
		$where	=	array('id' => $id);
		switch ( $oper_arr[$operation] ) {
			case 0:
				//删除
				$result = $model->table($table)->where($where)->save(array('status'=>0));
				break;
			case 1:
				//启动
				$result = $model->table($table)->where($where)->save(array('status'=>1));
				break;
			case 2:
				//停用
				$result = $model->table($table)->where($where)->save(array('status'=>2));
				break;
		}
		if( $result ) {
			$this -> writelog("已{$oper_arr_tran[$operation]}id为{$id}的任务软件",$table,$id,__ACTION__ ,'','edit');
			$this->success('操作成功');
		}else {
			$this->error('操作失败');
		}
	}
	
	function task_soft_import()
	{
		$aid = (Int)$_REQUEST['aid'];
		$atp = (Int)$_REQUEST['atp'];//活动类型 0为签到日期任务软件 1为补签任务软件 
		if($_POST) {
			$model		=	M('');
			$ad_file	=	$_FILES['ad_file'];
			$time		=	strtotime(date('Y-m-d', time()));
			if(!$ad_file['size']) {
				$this->error('请上传文件');
			}
			$ext	=	pathinfo( $ad_file['name'] );
			if( strtolower($ext['extension']) != 'csv' ) {
				$this->error('请上传csv格式文件');
			}
			$shili	 = fopen($ad_file['tmp_name'], "r");
			$package = $info = $repeat_pack = $not_found_pack=array();
			$str = '';
			while ( !feof($shili) ) {
				$shi = fgets($shili, 1024);
				$a = explode(',', $shi);
				if(count($a)>5){
					$this->error("文件格式错误");
				}
				$str .= $shi . ",";
			}
			$str_arr = explode("\r\n", $str);
			$i = 0;
			foreach($str_arr as $key => $val) {
				if(empty($val)||$val === ',,') {
					continue;
				}
				if($key==0){
					continue;
				}else{
					//list($pack) = explode(',',$val);
					$pack = trim($val, ',');
				}
				$pkg_data = $model->table('sj_soft')->where(array('package'=>$pack,'status'=>1,'hide'=>1))->find();
				//判断是否是接入的SDK软件  待完成
				if($pkg_data) {
					$pack	=	trim($pack);
					$info[$i]['package']	=	$pack;
					$info[$i]['softname']	=	$pkg_data['softname'];
					$package[]	=	$pack;
					$i++;
				}else{
					$not_found_pack[]	=	$pack;
				}
			}
				
			$not_found_pack	=	array_unique(array_filter($not_found_pack));
			$num = count($package);
			$this->assign('num', $num);
			$this->assign('not_found_pack', $not_found_pack);
			$j_info = base64_encode(json_encode($info));
			$this->assign('aid', $aid);
			$this->assign('j_info', $j_info);
			$this->assign('info', $info);
			$this->assign('atp', $atp);
			$this->display('task_soft_import_view');
		}else {
			$this->assign('aid', $aid);
			$this->assign('atp', $atp);
			$this->display();
		}
	}
	
	//导入数据库
	function task_soft_import_up()
	{
		$model		=	D('Sj.SignTaskSoft');
		$aid		=	$_POST['aid'];
		$atp		=	$_POST['atp'];
		$info		=	json_decode(base64_decode($_POST['info']),true);
		$key_arr	=	explode(',',trim($_POST['id'],','));
		$id_str		=	'';
		$repeat_pack	=	array();
		$import_count	=	0;
		$time			=	time();
		foreach( $info as $k=>$v ) {
			if(!in_array($k, $key_arr)) {
				continue;
			}
			$condtion = array(
					'package'	=>	$v['package'],
					'status'	=>	array('in','1,2'),
			);
			if( $atp == 1 ) {
				$condtion['mid'] = $aid;
			}else {
				$condtion['did'] = $aid;
			}
			//检查是否重复添加
			$row = $model->table('qd_task_soft')->where($condtion)->find();
			if( !empty($row) ) {
				$repeat_pack[] = $v['package'];
				continue;
			}
			$data	=	array(
					'softname'	=>	$v['softname'],
					'package'	=>	$v['package'],
					'status'	=>	1,
					'create_tm'	=>	$time,
			);
			if( $atp == 1 ) {
				$data['mid'] = $aid;
			}else {
				$data['did'] = $aid;
			}
			$res = $model->table('qd_task_soft')->add($data);
			if($res){
				$import_count++;//导入成功的软件
				$id_str .= $res.',';
			}
		}
		if($id_str){
			$this->writelog("在任务软件库中添加了id为{$id_str}的软件", 'qd_task_soft', $id_str,__ACTION__,'','add');
		}
		$count = count($key_arr);//选中的软件
		if(count($repeat_pack)) {
			$import_count_failure = $count - $import_count;//冲突的软件
			$str='';
			if($import_count) {
				$str="成功导入软件：{$import_count}个  ";
			}
			$str.="软件冲突：{$import_count_failure}个  ";
			foreach($repeat_pack as $k=>$v ) {
				$str .= $v.',';
			}
			echo json_encode(array('code'=>2,'msg'=>$str));
		}else {
			echo json_encode(array('code'=>1,'msg'=>"成功导入任务软件：{$count}个"));
		}
	}
	
	//软件库配置详情
	function task_soft_detail()
	{
		$aid = $_GET['aid'];
		$atp = $_GET['atp'];//活动类型 0为签到日期任务软件 1为补签任务软件
		if( !$aid ) {
			$this->error('参数有误');
		}
		$model	=	D('Sj.SignTaskSoft');
		$where	=	array(
				'status'=>	array('in','1,2'),
		);
		if( $atp == 1 ) {
			$where['mid'] = $aid;
		}else {
			$where['did'] = $aid;
		}
		$count	=	$model->getcount($where);
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 10, $param);
		$show	=	$Page->show();
		$order	=	'id desc';
		$list	=	$model->table('qd_task_soft') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order($order) -> select();
		$this->assign('aid', $aid);
		$this->assign('atp', $atp);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}
	
}