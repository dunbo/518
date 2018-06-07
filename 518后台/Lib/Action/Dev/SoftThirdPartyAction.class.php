<?php
/**
 * 第三方域名软件下载管理
 */
class SoftThirdPartyAction extends CommonAction{
	function soft_list()
	{
		$softname	=	!empty($_REQUEST['softname'])?trim($_REQUEST['softname']):"";
		$package	=	!empty($_REQUEST['package'])?trim($_REQUEST['package']):"";
		$order		=	$_REQUEST['order']?$_REQUEST['order']:0;
		$model = M('');
		$where = 'A.download_type = 2';
		if(isset($package) && !empty($package)) {
           $where .= " and A.package = '{$package}' ";
        }
		if(isset($softname) && !empty($softname)) {
            $where .= " and B.softname like '%{$softname}%' ";
        }
        $where .= " and B.status = 1 and B.hide=1 and B.channel_id ='' ";
		$sql		=	"select A.*,B.softname from sj_soft_expand as A left join sj_soft as B on A.package = B.package where {$where}";
        $sql_count	=	"select count(*) as count from sj_soft_expand as A left join sj_soft as B on A.package = B.package where {$where}";
		$count	=	$model->query($sql_count);
		$count	=	$count[0]['count'];
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 10, $param);
		$show	=	$Page->show();
		if($order == 1){
			$order		=	0;
			$orderby	=	' order by A.add_tm asc ';
		}else{
			$order		=	1;
			$orderby	=	' order by A.add_tm desc ';
		}
		$limit = " limit ".$Page->firstRow . ',' . $Page->listRows;
		$sql .= $orderby.$limit;
		$list	=	$model->query($sql);
		
		$this->assign('page', $show);
		$this->assign('order', $order);
		$this->assign('package', $package);
		$this->assign('softname', $softname);
		$this->assign('list', $list);
		$this->display();	
	}
	
	//输入包名智能验证包名是否存在
	public function pub_soft_val()
	{
		$package	=	trim($_GET['package']);
		$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if( $pkg_data ) {
			exit( json_encode(array('code'=>1,'msg'=>'','softname'=>$pkg_data['softname'])) );
		}else {
			exit( json_encode(array('code'=>0,'msg'=>'包名不存在')) );
		}
	}
	
	//添加第三方软件管理
	public function soft_add(){
		if($_POST){
			$model	=	M('');
			$table	=	"sj_soft_expand";
			$ret	=	$this -> soft_validate($table);
			if($ret['code'] == 1) {
				$config	=	$ret['config'];
				$data	=	$ret['data'];
				$model -> table($table) -> add($data);
				$this -> writelog("已添加包名为{$data['package']}的第三方软件管理中",$table,$data['package'],__ACTION__ ,'','add');
				$this -> success("操作成功");
			}else{
				$this -> assign('jumpUrl', 'javascript:history.back(-1);');
				$this -> error($ret['msg']);
			}
		}else{
			$this -> display();
		}
	}
	
	//任务软件系列操作
	function soft_delete()
	{
		$package = !empty($_GET['package']) ? trim($_GET['package']) : 0;
		if(!$package){
			$this->error('参数有误');
		}
		$model	=	M('');
		$table	=	'sj_soft_expand';
		$where	=	array('package' => $package, 'download_type' => 2);
		$result	=	$model->table($table)->where($where)->delete();
		if( $result ) {
			$this -> writelog("已删除包名为{$package}",$table,$package,__ACTION__ ,'','del');
			$this->success('操作成功');
		}else {
			$this->error('操作失败');
		}
	}
	
	function soft_import()
	{
		if($_FILES) {
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
			$this->assign('j_info', $j_info);
			$this->assign('info', $info);
			$this->display('soft_import_view');
		}else {
			$this->display();
		}
	}
	
	//导入数据库
	function soft_import_up()
	{
		$model		=	M('');
		$info		=	json_decode(base64_decode($_POST['info']),true);
		$key_arr	=	explode(',',trim($_POST['id'],','));
		$package_str	=	'';
		$repeat_pack	=	array();
		$import_count	=	0;
		$time			=	time();
		foreach( $info as $k=>$v ) {
			if(!in_array($k, $key_arr)) {
				continue;
			}
			$condtion = array(
					'package'			=>	$v['package'],
					' download_type'	=>	2,
			);
			//检查是否重复添加
			$row = $model->table('sj_soft_expand')->where($condtion)->find();
			if( !empty($row) ) {
				$repeat_pack[] = $v['package'];
				continue;
			}
			$data	=	array(
					'package'		=>	$v['package'],
					'download_type'	=>	2,
					'update_tm'		=>	$time,
					'add_tm'		=>	$time,
			);
			$model->table('sj_soft_expand')->add($data);
			$import_count++;//导入成功的软件
			$package_str .= $v['package'];
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
			$this->writelog("在第三方软件管理中添加了包名为{$package_str}的软件", 'sj_soft_expand', $package_str,__ACTION__,'','add');
			echo json_encode(array('code'=>1,'msg'=>"成功导入任务软件：{$count}个"));
		}
	}
	
	/**
	 * 任务软件验证
	 */
	public function soft_validate( $table )
	{
		$package	=	!empty($_POST['package']) ? trim($_POST['package']) : '';
		if( !$package ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	'包名不能为空'
			);
			return $return;
		}
		$pkg_data = M('')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if( !$pkg_data ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	'包名不存在'
			);
			return $return;
		}
		$data = array(
				'package'		=>	$package,
				'download_type'	=>	2,
				'add_tm'		=>	time(),
				'update_tm'		=>	time(),
		);
		$condtion = array(
				'package'	=>	$package,
		);
		//检查是否重复添加
		$row = M('')->table('sj_soft_expand')->where($condtion)->find();
		if( !empty($row) ) {
			$return = array(
					'code'	=>	0,
					'msg'	=>	'该软件已经添加过了'
			);
			return $return;
		}
		$return = array(
				'code'		=>	1,
				'data'		=>	$data,
		);
		return $return;
	}
	
	function batch_del_soft(){
		$package = trim($_GET['package']);
		if(!$package) {
			$this->error('参数有误');
		}
		$package_arr = explode(',', $package);
		$model = M('');
		foreach ($package_arr as $v){
			$ret = $model->table('sj_soft_expand')->where("package='{$v}' and download_type = 2 ")->delete();
		}
		if($ret){
			$this -> writelog("第三方软件管理删除表名为{$package}的软件",'sj_soft_expand',$package,__ACTION__ ,"","del");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}

}