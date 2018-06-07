<?php
class SchoolAction extends CommonAction {
	public function School_List() { //校园列表
		if($_POST['add']=='add')
		{
			$this->doAdd_School($_POST['name']);
		}
		if($_POST['updata'])
		{
			$this->doSchool_List($_POST['name'],$_POST['updata']);
		}
		if($_POST['del'])
		{
			$this->doSchool_del($_POST['del']);
		}
		$model = M ( 'school_name' );
		import ( "@.ORG.Page" ); //导入分页类
		$count = $model->where ( '`status` = 1' )->count (); //计算总数
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $model->where ( '`status` = 1' )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
		$page = $p->show ();
		$this->assign ( "page", $page );
		$this->assign ( 'new', $new );
		$this->display ();
	}
	public function doAdd_School($str) { //添加学校
		$model = M ( 'school_name' );
		$school_name = $str;
		if(! empty ( $school_name ))
		{
			if (! empty ( $school_name )) {
				$new = $model->where ( "`schoolname` = '$school_name'" )->SELECT ();
				if ($new[0]['status']==NULL) {
					$data = array ('schoolname' => $_POST ['name'], 'time' => time (), 'ip' => $this->getRealIp (), 'status' => 1 );
					$list = $model->add ( $data );
					$this->writelog("添加名为".$school_name."学校",'sj_school_name',$list,__ACTION__ ,"","add");//
					//$this->success ( iconv('GKB','UTF-8','添加成功') );
					echo "<script LANGUAGE='javascript'> alert('添加成功！')</script>";
				} else if($new[0]['status'] == 1){
					//$this->error ( '学校名称被占用' );
					echo "<script LANGUAGE='javascript'> alert('学校名称被占用！')</script>";
				} else if($new[0]['status'] == 0){
					$model->delete($new[0]['id']);
					$data = array ('schoolname' => $_POST ['name'], 'time' => time (), 'ip' => $this->getRealIp (), 'status' => 1 );
					$list=$model->add($data);
					$this->writelog("添加名为".$school_name."学校",'sj_school_name',$list,__ACTION__ ,"","add");
					//$this->success ( '添加成功' );
					echo "<script LANGUAGE='javascript'> alert('添加成功！')</script>";
				}
			} else {
				//$this->error ( '没有添加学校名称' );
				echo "<script LANGUAGE='javascript'> alert('没有添加学校名称！')</script>";
			}
		}
		//$this->display ();
	}
	public function doSchool_List($str,$id) { //编辑学校内容
		$model = M ( 'school_name' );
		$id = $id;
		$school_name = $str;
		if (empty ( $school_name )) {
			$list = $model->where ( "`id` = '$id'" )->SELECT ();
		} else {
			$new = $model->where ( "`id` <> '$id' and  `schoolname` = '$school_name' " )->SELECT ();
			if(empty($new))
			{
				$data = array ('schoolname' => $school_name );
				$log_result = $this->logcheck(array('id'=>$id),'sj_school_name',$data,$model);
				$new1 = $model->where ( "`id` = '$id'" )-> save ($data);
				if(!empty($new1))
				{
					$this->writelog("学校管理列表_id为{$id}.{$log_result}",'sj_school_name',$id,__ACTION__ ,"","edit");
					//$this->success ( '修改成功' );
					echo "<script LANGUAGE='javascript'> alert('修改成功!')</script>"  ;
				}else{
					//$this->error ( '修改失败' );
					echo "<script LANGUAGE='javascript'> alert('修改失败！')</script>" ;
				}
				$list = $new;
			} else {
				//$this->error( '学校名称已被占用' );
				echo "<script LANGUAGE='javascript'> alert('学校名称已被占用！')</script>" ;
				//$list = array('id'=>$id,'schoolname'=>$school);
			}
		}
		//$this->assign ('list' , $list );
		//$this->display ();
	}
	public function doSchool_del($id) { //删除学校内容
		$model = M ( 'school_name' );
		//$id = $_GET ['doSchool_del'];
		$data = array ('status' => '0' );
		$new = $model->where ( "id = " . $id )->save ( $data );
		IF (!empty ( $new )) {
			$this->writelog("将id为".$id.'的学校删除','sj_school_name',$id,__ACTION__ ,"","del");
			echo "<script LANGUAGE='javascript'> alert('删除成功！')</script>" ;
			echo "<script LANGUAGE='javascript'> javascript:self.close();</script> ";
		} else {
			echo "<script LANGUAGE='javascript'> alert('删除失败！')</script>" ;
		}
	}
	public function Edit_School() { //活动图片列表
		if($_POST['add']=='add')
		{
			$this->doEdit_School_list($_POST['name']);
		}
		if($_POST['updata'])
		{
			$array = array($_POST['updata1'],$_POST['updata']);
			$this->doEdit_School($array);
		}
		if($_POST['del'])
		{
			$array = array($_POST['del1'],$_POST['del']);
			$this->doDel_School($array);
		}
		$model = M ( 'school_name' );
		$model1 = M ( 'school_image' );
		$Form = M ( 'Form' );
		import ( "@.ORG.Page" ); //导入分页类
		$count = $model->where ( '`status` = 1' )->count (); //计算总数
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $model->where ( '`status` = 1' )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
		$page = $p->show ();
		foreach ( $new as $key => $value ) {
			$list = $model1->where ( 'uid=' . $new [$key] ['id'] . ' and `status` = 1' )->limit ( 4 )->SELECT ();
			$i = count ( $list );
			if (empty ( $list )) {
				$new [$key] ['pic'] = array (array ('id' => '', 'uid' => $new [$key] ['id'], 'url' => '', 's_url' => '', 'type' => '1', 'status' => '1' ), array ('id' => '', 'uid' => $new [$key] ['id'], 'url' => '', 's_url' => '', 'type' => '1', 'status' => '1' ), array ('id' => '', 'uid' => $new [$key] ['id'], 'url' => '', 's_url' => '', 'type' => '1', 'status' => '1' ), array ('id' => '', 'uid' => $new [$key] ['id'], 'url' => '', 's_url' => '', 'type' => '1', 'status' => '1' ) );
			} else {
				if ($i == 4)
					$new [$key] ['pic'] = $list;
				else {
					for($k = $i; $k < 4; $k ++) {
						$list [] = array ('id' => '', 'uid' => $new [$key] ['id'], 'url' => '', 's_url' => '', 'type' => '1', 'status' => '1' );
					}
					$new [$key] ['pic'] = $list;
				}
			}
		}
		$this->assign ( "page", $page );
		$this->assign ( 'url', IMGATT_HOST );
		$this->assign ( 'new', $new );
		$this->display ();
	}
	public function doEdit_School_list($str,$file) { //学校添加图片
		$model = M ( 'school_name' );
		$model1 = M ( 'school_image' );
		$school_name = $str;
		if(! empty ( $_POST ))
		{
				if (! empty ( $school_name )) {
					$new = $model->where ( "`schoolname` = '$school_name'" )->SELECT ();
					if ($new[0]['status']==NULL) {
						$data = array ('schoolname' => $_POST ['name'], 'time' => time (), 'ip' => $this->getRealIp (), 'status' => 1 );
						$list = $model->add ( $data );
						$this->writelog("添加名为".$school_name."学校",'sj_school_name',$list,__ACTION__ ,"","add");
						$ret = $this->_upload($list[id]);
						//$this->success ( '添加成功' );
						echo "<script LANGUAGE='javascript'> alert('添加成功！')</script>" ;
					} else if($new[0]['status'] == 1){
						$ret = $this->_upload($new[0]['id']);
						//$this->success ( '添加成功' );
						echo "<script LANGUAGE='javascript'> alert('添加成功！')</script>" ;
					} else if($new[0]['status'] == 0){
						$model->delete($new[0]['id']);
						$data = array ('schoolname' => $_POST ['name'], 'time' => time (), 'ip' => $this->getRealIp (), 'status' => 1 );
						$id = $model->add($data);
						$this->writelog("添加名为".$school_name."学校",'sj_school_name',$id,__ACTION__ ,"","add");
						$ret = $this->_upload($id);
						//$this->success ( iconv('GKB','UTF-8','添加成功') );
						echo "<script LANGUAGE='javascript'> alert('添加成功！')</script>" ;
				}
			} else {
				//$this->error ( iconv('GKB','UTF-8','没有添加学校名称') );
				echo "<script LANGUAGE='javascript'> alert('没有添加学校名称！')</script>" ;
			}
		}
		//$this->display();
	}
	public function doEdit_School($array) { //修改图片
		$model = M ( 'school_image' );
		$data = array ('status' => '0' );
		//$str = $_GET [doEdit_School];
		//$id = explode ( ".", $str );
		$id = $array;
		if (! empty ( $_FILES )) {
			//如果有文件上传 上传附件
				$this->_upload( $id [1] ,$id[0]);
		}
		// $this->writelog("ID为".$id[1]."的学校，修改照片。照片id为".$id[0]);
		//$this->display ();
	}
	public function doDel_School($array) {	//删除照片
		$model = M ( 'school_image' );
		$data = array ('status' => '0' );
		//$id = explode ( '.', $_GET [doDel_School] );
		$id = $array;
		if ($id [0] == 0) {
			echo "<script LANGUAGE='javascript'> alert('没有可删除图片！')</script>" ;
			echo "<script LANGUAGE='javascript'> javascript:self.close();</script> ";
		} else {
			//print_r($id);
			$list = $model->where ( "id = " . $id [0] . " and uid = " . $id [1] )->save ( $data );
			//$list = $model->where ( "id = 21 and uid = " )->save ( $data );
			if ($list !== false) {
				$this->writelog("id为".$id [1]."的学校删除照片id为".$id[0],'sj_school_image',$id[0],__ACTION__ ,"","del");
				echo "<script LANGUAGE='javascript'> alert('删除成功！')</script>" ;
				echo "<script LANGUAGE='javascript'> javascript:self.close();</script> ";
			} else {
				echo "<script LANGUAGE='javascript'> alert('删除失败！')</script>" ;
				echo "<script LANGUAGE='javascript'> javascript:self.close();</script> ";
			}
		}
	}
	public function User_List() {	//注册列表
		$Form = M ( 'school_user' );
		import ( "@.ORG.Page" ); //导入分页类
		$count = $Form->count (); //计算总数
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $Form->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
		$page = $p->show ();
		$time[1] = date("Y-m-d",time());
		$time[0] = date("Y-m-d",(time()-(7*86400)));
		$this->assign ('time',$time);
		$this->assign ( "page", $page );
		$this->assign ( "new", $new );
		$this->display ();
	
	}
	public function doAdd_User() {			//注册会员导出CSV 
		Header ( "Content-type:   application/octet-stream " );
		Header ( "Content-Disposition:   attachment;   filename= list.csv" );
		$time = strtotime ( escape_string($_POST [date0]));
		$time1 = strtotime (escape_string($_POST [date1]))+86400;
		$model = M ( 'school_user' );
		if (empty ( $time ) && empty ( $time1 )) {
			$new = $model->SELECT ();
		} else if (empty ( $time1 )) {
			$new = $model->order('id DESC')->where ( " time >= $time " )->findAll ();
		} else if (empty ( $time )) {
			$new = $model->order('id DESC')->where ( " time <= $time1" )->findAll ();
		} else {
			$new = $model->order('id DESC')->where ( " time >= $time and time  <= $time1 " )->findAll ();
		}
		
		echo iconv('UTF-8','GBK',"序号,日期,姓名,学校,专业,社团,手机,邮箱,理由\r\n");
		
		foreach ( $new as $key => $value ) {
			echo $value ['id'] . ',' . date ( "Y/m/d", $value ['time'] ) . ',' . iconv ( "UTF-8", "GB2312//IGNORE", $value ['name'] ) . ',' . iconv ( "UTF-8", "GB2312//IGNORE", $value ['school'] ) . ',' . iconv ( "UTF-8", "GB2312//IGNORE", $value ['professional'] ) . ',' . iconv ( "UTF-8", "GB2312//IGNORE", $value ['community'] ) . ',' . iconv ( "UTF-8", "GB2312//IGNORE", $value ['Mobile'] ) . ',' . iconv ( "UTF-8", "GB2312//IGNORE", $value ['Email'] ) . ',' . iconv ( "UTF-8", "GB2312//IGNORE", $value ['reason'] ) . "\r\n";
		}
	}
	public function csv_School()
	{
		header('content-type:text/html;charset=utf-8');
		$pu_model =  D('School.School');
		$channel = M ('school_data');
		$cid = escape_string($_POST[id]);
		$time = escape_string($_POST['time']);
		$time1 = escape_string($_POST['time1']);
		$new = $channel->where('cid='. $cid ." and `time`>= $time  and `time` <= $time1")->SELECT();
		//print_r($new);
		//exit();
		$new1 = $pu_model->where('cid='. $cid ." and `submit_tm`>= $time  and `submit_tm` <= $time1 and `status` = 1")->SELECT();
		$i=0;
		foreach($new as $key => $value)
		{
			if($new1)
			{
				foreach($new1 as $key1 => $value1)
				{
					if(date('Y-m-d',$value['time']) == date('Y-m-d',$value1['submit_tm']))
					{
						$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => $value1['counts'] ) ;
					}else{
						if(empty($list[$i]['num']))
						{
							$value1['counts'] = 0;
							$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => $value1['counts'] ) ;
						}
					}
				}
			}
			else
			{
				$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => 0 ) ;
			}
			$i++;
		}
		Header ( "Content-type:   application/octet-stream " );
		Header ( "Content-Disposition:   attachment;   filename= list.csv" );
		echo iconv ( "UTF-8", "GB2312//IGNORE",$new[0]['checkname'])."\r\n";
		echo iconv ( "UTF-8", "GB2312//IGNORE","日期").",".iconv ( "UTF-8", "GB2312//IGNORE","web下载量").",".iconv ( "UTF-8", "GB2312//IGNORE","wap下载量").",".iconv ( "UTF-8", "GB2312//IGNORE","激活量")."\r\n";
		foreach($list as $key => $value)
		{
			echo date("Y/m/d",$value['time']).','.$value['web'].','.$value['wap'].','.$value['num']."\r\n";
			$sumweb +=  $value['web'];
			$sumwap +=  $value['wap'];
			$sumnum +=  $value['num'];
		}
		echo iconv ( "UTF-8", "GB2312//IGNORE","总量").",".$sumweb.",".$sumwap.",".$sumnum;
	}
	public function data_School()	{	//
		$Form = M ( 'school_channel' );
		$pu_model =  D('School.School');
		$channel = M ('school_data');
		$time = time()-(7*86400);
		$ptime = date("m/d/Y",$time);
		$ptime1 = date("m/d/Y",time());
		import ( "@.ORG.Page" ); //导入分页类
		if(empty($_GET)&&empty($_POST))
		{
			$count = $Form->where("`cid` = 442")->count (); //计算总数
			$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
			$p = new Page($count,$getpage);
			$this->assign("getpage",$getpage);
			$new = $Form->where("`cid` = 442")->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
			foreach($new as $key => $value)
			{
				$new[$key]['web'] = $channel->where("cid=" .$value['cid'])->sum('web');
				$new[$key]['wap'] = $channel->where("cid=" .$value['cid'])->sum('wap');
				$new[$key]['num'] = $pu_model->where("cid=" .$value['cid'])->sum('counts');	
			}
			$page = $p->show ();
			$this->assign('ptime',$ptime);
			$this->assign('ptime1',$ptime1);
			$this->assign ( "page", $page );
			$this->assign ( "new", $new );
			$this->display ();
		}else{
			if($_POST['check']=="查看")
			{
				$time = strtotime(escape_string($_POST['date0']));
				$time1 = strtotime(escape_string($_POST['date1']));
				$count = $Form->count (); //计算总数//->where ( "`time` >= {$time} and `time` <= {$time1}" )
				$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
				$p = new Page($count,$getpage);
				$this->assign("getpage",$getpage);
				$new = $Form->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
				foreach($new as $key => $value)
				{
					$new[$key]['web'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('web');
					$new[$key]['wap'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('wap');
					$new[$key]['num'] = $pu_model->where("cid=" .$value['cid']." and `submit_tm` >= {$time} and `submit_tm` <= {$time1}")->sum('counts');	
				}
				$page = $p->show ();
				$ptime = date("m/d/Y",$time );
				$ptime1 = date("m/d/Y",$time1);
				$this->assign('ptime',$ptime);
				$this->assign('ptime1',$ptime1);
				$this->assign ( "page", $page );
				$this->assign ( "new", $new );
				$this->display ();
			} else if(strstr($_GET[data_School],"daochu")){
				header('content-type:text/html;charset=utf-8');
				Header ( "Content-type:   application/octet-stream " );
				$arr = $_GET[data_School];
				$arr = explode('.',$arr);
				$pr = explode("-",$arr[1]);
				$time = strtotime($pr[2].'-'.$pr[0].'-'.$pr[1]);
				$pr1 = explode("-",$arr[2]);
				$time1 = strtotime($pr1[2].'-'.$pr1[0].'-'.$pr1[1])+86400;
				
				Header ( "Content-Disposition:   attachment;   filename= list.csv" );
				echo iconv('UTF-8','GBK',"序号 ,渠道名称,WEB下载量,WAP下载量,激活数量\r\n");
				$count = $Form->count (); //计算总数
				$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
				$p = new Page($count,$getpage);
				$this->assign("getpage",$getpage);
				$new = $Form->where("`cid` = 442")->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
				foreach($new as $key => $value)
				{
					$new[$key]['web'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('web');
					$new[$key]['wap'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('wap');
					$new[$key]['num'] = $pu_model->where("cid=" .$value['cid']." and `submit_tm` >= {$time} and `submit_tm` <= {$time1}")->sum('counts');	
				}
				foreach($new as $key => $value)
				{
					//echo iconv('UTF-8','GBK',$value[id].','.$value['checkname'].','.$value['web'].','.$value['wap'].',',$value['num'])."\r\n";
					echo $value[id].','.iconv('utf-8','gb2312',$value['checkname']).','.$value['web'].','.$value['wap'].',',$value['num']."\r\n";
				}
			} else if ($_POST) {
				foreach($_POST as $key => $value)
				{
					if($value == "查看明细")
					{
						$arr = explode("_",$key);
						break;
					}
				}
				$cid = $arr[1];
				$pr = explode("/",$_POST[date0]);
				$time = strtotime($pr[2].'-'.$pr[0].'-'.$pr[1]);
				$pr1 = explode("/",$_POST[date1]);
				$time1 = strtotime($pr1[2].'-'.$pr1[0].'-'.$pr1[1])+86400;
				$new = $channel->where('cid='. $cid ." and `time`>= $time  and `time` <= $time1")->SELECT();
				//print_r($new);
				$new1 = $pu_model->where('cid='. $cid ." and `submit_tm`>= $time  and `submit_tm` <= $time1 and `status`=1")->SELECT();
				$i = 0;
				foreach($new as $key => $value)
				{
					if($new1)
					{
						foreach($new1 as $key1 => $value1)
						{
							if(date('Y-m-d',$value['time']) == date('Y-m-d',$value1['submit_tm']))
							{
								$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => $value1['counts'] ) ;
							}else{
								if(empty($list[$i]['num']) )
								{
									$value1['counts'] = 0;
									$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => $value1['counts'] ) ;
								}
							}
						}
					}
					else
					{
						$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => 0 ) ;
					}
					$i++;
				}
				//print_r($list);
				$this->assign ('time',array($time,$time1));
				$this->assign('new',$new[0]);
				$this->assign('list',$list);
				$this->display ('check_School');
			}
		}
		
	}
	//校园渠道统计_new
	public function data_School_new()	{	//
		$Form = M ( 'school_channel' );
		$activateDB =  D('Sj.activation');
		$activateDB -> getConnection();
		$channel = M ('school_data');
		$time = time()-(7*86400);
		$ptime = date("m/d/Y",$time);
		$ptime1 = date("m/d/Y",time());
		import ( "@.ORG.Page" ); //导入分页类
		if(empty($_GET)&&empty($_POST))
		{
			$count = $Form->where("`cid` = 442")->count (); //计算总数
			$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
			$p = new Page($count,$getpage);
			$this->assign("getpage",$getpage);
			$new = $Form->where("`cid` = 442")->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
			foreach($new as $key => $value)
			{
				$new[$key]['web'] = $channel->where("cid=" .$value['cid'])->sum('web');
				$new[$key]['wap'] = $channel->where("cid=" .$value['cid'])->sum('wap');
				$new[$key]['num'] = $activateDB -> table('activation_state') ->where("cid=" .$value['cid'])->sum('counts');	
			}
			$page = $p->show ();
			$this->assign('ptime',$ptime);
			$this->assign('ptime1',$ptime1);
			$this->assign ( "page", $page );
			$this->assign ( "new", $new );
			$this->display ();
		}else{
			if($_POST['check']=="查看")
			{
				$time = strtotime(escape_string($_POST['date0']));
				$time1 = strtotime(escape_string($_POST['date1']));
				$count = $Form->count (); //计算总数//->where ( "`time` >= {$time} and `time` <= {$time1}" )
				$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
				$p = new Page($count,$getpage);
				$this->assign("getpage",$getpage);
				$new = $Form->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
				foreach($new as $key => $value)
				{
					$new[$key]['web'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('web');
					$new[$key]['wap'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('wap');
					$new[$key]['num'] = $activateDB -> table('activation_state') -> where("cid=" .$value['cid']." and `submit_tm` >= {$time} and `submit_tm` <= {$time1}")->sum('counts');	
				}
				$page = $p->show ();
				$ptime = date("m/d/Y",$time );
				$ptime1 = date("m/d/Y",$time1);
				$this->assign('ptime',$ptime);
				$this->assign('ptime1',$ptime1);
				$this->assign ( "page", $page );
				$this->assign ( "new", $new );
				$this->display ();
			} else if(strstr($_GET[data_School],"daochu")){
				header('content-type:text/html;charset=utf-8');
				Header ( "Content-type:   application/octet-stream " );
				$arr = $_GET[data_School];
				$arr = explode('.',$arr);
				$pr = explode("-",$arr[1]);
				$time = strtotime($pr[2].'-'.$pr[0].'-'.$pr[1]);
				$pr1 = explode("-",$arr[2]);
				$time1 = strtotime($pr1[2].'-'.$pr1[0].'-'.$pr1[1])+86400;
				
				Header ( "Content-Disposition:   attachment;   filename= list.csv" );
				echo iconv('UTF-8','GBK',"序号 ,渠道名称,WEB下载量,WAP下载量,激活数量\r\n");
				$count = $Form->count (); //计算总数
				$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
				$p = new Page($count,$getpage);
				$this->assign("getpage",$getpage);
				$new = $Form->where("`cid` = 442")->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll ();
				foreach($new as $key => $value)
				{
					$new[$key]['web'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('web');
					$new[$key]['wap'] = $channel->where("cid=" .$value['cid']." and `time` >= {$time} and `time` <= {$time1}")->sum('wap');
					$new[$key]['num'] = $activateDB -> table('activation_state') -> where("cid=" .$value['cid']." and `submit_tm` >= {$time} and `submit_tm` <= {$time1}")->sum('counts');	
				}
				foreach($new as $key => $value)
				{
					//echo iconv('UTF-8','GBK',$value[id].','.$value['checkname'].','.$value['web'].','.$value['wap'].',',$value['num'])."\r\n";
					echo $value[id].','.iconv('utf-8','gb2312',$value['checkname']).','.$value['web'].','.$value['wap'].',',$value['num']."\r\n";
				}
			} else if ($_POST) {
				foreach($_POST as $key => $value)
				{
					if($value == "查看明细")
					{
						$arr = explode("_",$key);
						break;
					}
				}
				$cid = $arr[1];
				$pr = explode("/",$_POST[date0]);
				$time = strtotime($pr[2].'-'.$pr[0].'-'.$pr[1]);
				$pr1 = explode("/",$_POST[date1]);
				$time1 = strtotime($pr1[2].'-'.$pr1[0].'-'.$pr1[1])+86400;
				$new = $channel->where('cid='. $cid ." and `time`>= $time  and `time` <= $time1")->SELECT();
				//print_r($new);
				$new1 = $activateDB -> table('activation_state') ->where('cid='. $cid ." and `submit_tm`>= $time  and `submit_tm` <= $time1 and `status`=1")->SELECT();
				$i = 0;
				foreach($new as $key => $value)
				{
					if($new1)
					{
						foreach($new1 as $key1 => $value1)
						{
							if(date('Y-m-d',$value['time']) == date('Y-m-d',$value1['submit_tm']))
							{
								$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => $value1['counts'] ) ;
							}else{
								if(empty($list[$i]['num']) )
								{
									$value1['counts'] = 0;
									$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => $value1['counts'] ) ;
								}
							}
						}
					}
					else
					{
						$list[$i] = array('time'=>$value['time'],'web'=> $value['web'] ,'wap' => $value['wap'] , 'num' => 0 ) ;
					}
					$i++;
				}
				//print_r($list);
				$this->assign ('time',array($time,$time1));
				$this->assign('new',$new[0]);
				$this->assign('list',$list);
				$this->display ('check_School');
			}
		}
		
	}
	public function user_reg_list ()
	{
		$reg = M("school_reg");
		import ( "@.ORG.Page" ); //导入分页类
		
		if($_GET['submit']) {
			Header('content-type:text/html;charset=utf-8');
			Header ( "Content-type:   application/octet-stream " );
			Header ( "Content-Disposition:   attachment;   filename= list.csv" );
			$time = array($_GET['date0'],$_GET['date1']);
			$ret = $reg->where("`created_at` >= '".strtotime($_GET['date0'])."' and  `created_at` < '" .(strtotime($_GET['date1'])+86400). "'" )->findAll();
			echo iconv("UTF-8","GBK","姓名,性别,学校(学校全称),所属专业,入学年份,毕业年份,QQ号,手机号,报名附言,注册时间\r\n");
			foreach($ret as $key => $value)
			{
				echo iconv("UTF-8","GBK","{$value['name']},".($value['sex']==1?"男":"女").",{$value['school']},{$value['professional']},{$value['Entrance_year']},{$value['Graduation_year']},{$value['qq']},{$value['mobile']},{$value['text']},".date('Y-m-d',$value['created_at'])."\r\n");
			}
			exit();
		}
		
		$count = $reg->count ();
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $reg->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'id desc' )->findAll();
		$page = $p->show ();
		if(!$time)
		{
			$time = array(date("Y-m-d",time()-7*86400),date("Y-m-d",time()));
		}
		$this->assign('time',$time);
		$this->assign("new",$new);
		$this->assign("page",$page);
		$this->display();
	}
	
	public function Channels_management ()
	{
		import ( "@.ORG.Page" ); //导入分页类
		$type=0;
		$channel_model = M('school_channel');
		if($_GET['daochu'] == 1) {
			$time['date0'] = escape_string($_GET['date0']);
			$time['date1'] = escape_string($_GET['date1']);
			$time1 = array($_GET['date0'],$_GET['date1']);
			Header('content-type:text/html;charset=utf-8');
			Header ( "Content-type:   application/octet-stream " );
			Header ( "Content-Disposition:   attachment;   filename= list.csv" );
			echo iconv("UTF-8","GBK","序号,渠道名称,下载量,首次激活数量,最终激活数量\r\n");
			$new = $channel_model->where("`cid` <> '442'")->order('id DESC')->findAll();
			foreach($new as $key => $value) {
				$new[$key]['first_num'] = $channel_model->table('pu_channel_first_state')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
				$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->count();
				$new[$key]['num']['c'] = $channel_model->table('sj_school_data')->where("`time` >=  ".strtotime($time['date0'])." and `time` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->sum('web');
				echo iconv("UTF-8","GBK",$new[$key]['id'].",".$new[$key]['checkname'].",".$new[$key]['num']['c'].','.$new[$key]['first_num'].','.$new[$key]['last_num']."\r\n");
			} 
			exit();
		}elseif($_GET['daochu'] == 2) {
			$time['date0'] = escape_string($_GET['date0']);
			$time['date1'] = escape_string($_GET['date1']);
			$time1 = array($_GET['date0'],$_GET['date1']);
			$type=1;
		}
		if($type == 0) {
			$time['date0'] = date("Y-m-d",time()-7*86400);
			$time['date1'] = date("Y-m-d",time());
			$time1 = array(date("Y-m-d",time()-7*86400),date("Y-m-d",time()));
		}
		
		if(!empty($_GET['soso'])) {
			$zh_soso=escape_string($_GET['soso']);
			$channel_ret = $channel_model->table('sj_channel')->where("`chname` like '%{$zh_soso}%'")->findAll();
			if($channel_ret != NULL) {
				$this->assign("list",$channel_ret);
				$this->assign('type',1);
			}
		}
		if(!empty($_POST['cid'])) {
			$inret = $channel_model->table('sj_channel')->where(array('cid'=>$_POST['cid']))->find();
			$id = $channel_model->table('sj_school_channel')->data(array('cid'=>$inret['cid'],'checkname'=>$inret['chname'],'checkkey' =>$inret['chl']))->add();
			if($id > 0)
			{
				$this->writelog("校园添加渠道id为".$id,'sj_school_channel',$id,__ACTION__ ,"","add");
				$this->success ( '添加成功' );
			}
		}
		
		$count = $channel_model->where("`cid` <> '442'")->count();
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $channel_model->limit($p->firstRow.','.$p->listRows)->where("`cid` <> '442'")->order('id DESC')->findAll();
		foreach($new as $key => $value) {
			$new[$key]['first_num'] = $channel_model->table('pu_channel_first_state')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
			$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->count();
			$res = $channel_model->table('sj_school_data')->where("`time` >=  ".strtotime($time['date0'])." and `time` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->field('sum(web) as web, sum(wap) as wap')->find();
			$new[$key]['num']['web'] = $res['web'];
			$new[$key]['num']['wap'] = $res['wap'];
		}
		$page = $p->show ();
		$this->assign('new',$new);
		$this->assign('time',$time1);
		$this->assign("page",$page);
		$this->display();
	}
	
	public function Channels_management_new ()
	{
		import ( "@.ORG.Page" ); //导入分页类
		$type=0;
		$channel_model = M('school_channel');
		$activateDB = D('Sj.activation');
		$activateDB -> getConnection();
		if($_GET['daochu'] == 1) {
			$time['date0'] = escape_string($_GET['date0']);
			$time['date1'] = escape_string($_GET['date1']);
			$time1 = array($_GET['date0'],$_GET['date1']);
			Header('content-type:text/html;charset=utf-8');
			Header ( "Content-type:   application/octet-stream " );
			Header ( "Content-Disposition:   attachment;   filename= list.csv" );
			echo iconv("UTF-8","GBK","序号,渠道名称,下载量,首次激活数量,最终激活数量\r\n");
			$new = $channel_model->where("`cid` <> '442'")->order('id DESC')->findAll();
			foreach($new as $key => $value) {
				$new[$key]['first_num'] = $activateDB ->table('activation_state')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
				$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->count();
				$new[$key]['num']['c'] = $channel_model->table('sj_school_data')->where("`time` >=  ".strtotime($time['date0'])." and `time` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->sum('web');
				echo iconv("UTF-8","GBK",$new[$key]['id'].",".$new[$key]['checkname'].",".$new[$key]['num']['c'].','.$new[$key]['first_num'].','.$new[$key]['last_num']."\r\n");
			} 
			exit();
		}elseif($_GET['daochu'] == 2) {
			$time['date0'] = escape_string($_GET['date0']);
			$time['date1'] = escape_string($_GET['date1']);
			$time1 = array($_GET['date0'],$_GET['date1']);
			$type=1;
		}
		if($type == 0) {
			$time['date0'] = date("Y-m-d",time()-7*86400);
			$time['date1'] = date("Y-m-d",time());
			$time1 = array(date("Y-m-d",time()-7*86400),date("Y-m-d",time()));
		}
		
		if(!empty($_GET['soso'])) {
			$zh_soso=escape_string($_GET['soso']);
			$channel_ret = $channel_model->table('sj_channel')->where("`chname` like '%{$zh_soso}%'")->findAll();
			if($channel_ret != NULL) {
				$this->assign("list",$channel_ret);
				$this->assign('type',1);
			}
		}
		if(!empty($_POST['cid'])) {
			$inret = $channel_model->table('sj_channel')->where(array('cid'=>$_POST['cid']))->find();
			$id = $channel_model->table('sj_school_channel')->data(array('cid'=>$inret['cid'],'checkname'=>$inret['chname'],'checkkey' =>$inret['chl']))->add();
			if($id > 0)
			{
				$this->writelog("校园添加渠道id为".$id,'sj_school_channel',$id,__ACTION__ ,"","add");
				$this->success ( '添加成功' );
			}
		}
		
		$count = $channel_model->where("`cid` <> '442'")->count();
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $channel_model->limit($p->firstRow.','.$p->listRows)->where("`cid` <> '442'")->order('id DESC')->findAll();
		foreach($new as $key => $value) {
			$new[$key]['first_num'] = $activateDB->table('activation_state')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
			$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".strtotime($time['date0'])." and `submit_tm` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->count();
			$res = $channel_model->table('sj_school_data')->where("`time` >=  ".strtotime($time['date0'])." and `time` < ".(strtotime($time['date1'])+86400)." and  `cid` = '".$value['cid']."'")->field('sum(web) as web, sum(wap) as wap')->find();
			$new[$key]['num']['web'] = $res['web'];
			$new[$key]['num']['wap'] = $res['wap'];
		}
		$page = $p->show ();
		$this->assign('new',$new);
		$this->assign('time',$time1);
		$this->assign("page",$page);
		$this->display();
	}
	public function Channels_management_one() {
		import ( "@.ORG.Page" ); //导入分页类
		$channel_model = M('school_data');
		if($_POST['check1'])
		{
			Header('content-type:text/html;charset=utf-8');
			Header ( "Content-type:   application/octet-stream " );
			Header ( "Content-Disposition:   attachment;   filename= list.csv" );
			$new = $channel_model->field("* , (`web`+`wap`) as c")->where("`cid` = '".trim($_GET['id'])."'")->order('id DESC')->findAll();
			echo iconv("UTF-8",'GBK',"序号,渠道名称,下载量,有效激活数量,渠道安装数量,日期\r\n");
			foreach($new as $key => $value) {
				$time = $value['time']; 
				$new[$key]['ptime'] = date('Y-m-d',$value['time']);
				$new[$key]['first_num'] = $channel_model->table('pu_channel_first_state')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
				$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."'")->count();
				echo iconv("UTF-8","GBK",$new[$key]['id'].",".$new[$key]['checkname'].",".$new[$key]['c'].",".$new[$key]['first_num'].",".$new[$key]['last_num'].",".$new[$key]['ptime']."\r\n");
			}
			exit();
		}
		$count = $channel_model->where("`cid` = '".trim($_GET['id'])."'")->count();
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $channel_model->field("* , (`web`+`wap`) as c")->limit($p->firstRow.','.$p->listRows)->where("`cid` = '".trim($_GET['id'])."'")->order('time DESC')->findAll();
		foreach($new as $key => $value) {
			$time = $value['time']; 
			$new[$key]['ptime'] = date('Y-m-d',$value['time']);
			$new[$key]['first_num'] = $channel_model->table('pu_channel_first_state')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
			$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."'")->count();
		}
		$page = $p->show ();
		$this->assign('page',$page);
		$this->assign('new',$new);
		$this->display();
	}
	public function Channels_management_one_new() {
		import ( "@.ORG.Page" ); //导入分页类
		$channel_model = M('school_data');
		$activateDB = D('Sj.activation');
		$activateDB -> getConnection();
		if($_POST['check1'])
		{
			Header('content-type:text/html;charset=utf-8');
			Header ( "Content-type:   application/octet-stream " );
			Header ( "Content-Disposition:   attachment;   filename= list.csv" );
			$new = $channel_model->field("* , (`web`+`wap`) as c")->where("`cid` = '".trim($_GET['id'])."'")->order('id DESC')->findAll();
			echo iconv("UTF-8",'GBK',"序号,渠道名称,下载量,有效激活数量,渠道安装数量,日期\r\n");
			foreach($new as $key => $value) {
				$time = $value['time']; 
				$new[$key]['ptime'] = date('Y-m-d',$value['time']);
				$new[$key]['first_num'] = $activateDB->table('activation_state')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
				$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."'")->count();
				echo iconv("UTF-8","GBK",$new[$key]['id'].",".$new[$key]['checkname'].",".$new[$key]['c'].",".$new[$key]['first_num'].",".$new[$key]['last_num'].",".$new[$key]['ptime']."\r\n");
			}
			exit();
		}
		$count = $channel_model->where("`cid` = '".trim($_GET['id'])."'")->count();
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$new = $channel_model->field("* , (`web`+`wap`) as c")->limit($p->firstRow.','.$p->listRows)->where("`cid` = '".trim($_GET['id'])."'")->order('time DESC')->findAll();
		foreach($new as $key => $value) {
			$time = $value['time']; 
			$new[$key]['ptime'] = date('Y-m-d',$value['time']);
			$new[$key]['first_num'] = $activateDB->table('activation_state')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."' and  `status` = '1' ")->sum('counts');
			$new[$key]['last_num'] = $channel_model->table('pu_channel_last')->where("`submit_tm` >= ".$time." and `submit_tm` < ".($time+86400)." and  `cid` = '".$value['cid']."'")->count();
		}
		$page = $p->show ();
		$this->assign('page',$page);
		$this->assign('new',$new);
		$this->display();
	}
	
	public function add_Channels() {
		$channel_model = M('channel');
		if(!empty($_GET['soso'])) {
			$zh_soso=escape_string($_GET['soso']);
			$channel_ret = $channel_model->where("`chname` like '%{$zh_soso}%'")->findAll();
			if($channel_ret != NULL) {
				$this->assign("list",$channel_ret);
				$this->assign('type',1);
			}
		}
		if(!empty($_GET['sosopu'])) {
			$zh_sosopu=escape_string($_GET['sosopu']);
			$channel_ret1 = $channel_model->table('pu_popularparter')->where("`pu_name` like '%{$zh_sosopu}%'")->findAll();
			if($channel_ret1 != NULL) {
				$this->assign("list1",$channel_ret1);
				$this->assign('type',1);
			}
		}
		$channel = M('school_channel');
		if(!empty($_POST['cid'])&&!empty($_POST['pid'])) {
			$inret = $channel_model->where(array('cid'=>$_POST['cid']))->find();
			$puid = $channel->where(array('puid'=>$_POST['pid']))->find();
			if(is_array($puid)){
				$this->error("推广渠道已经存在");
				exit();
			}else{
				$data = array('cid'=>$inret['cid'],'checkname'=>$inret['chname'],'puid' => $_POST['pid'] , 'checkkey'=>$inret['chl']);
				$id = $channel->data($data)->add();
				if($id > 0) {
					$this->writelog("校园添加渠道id为".$id,'sj_school_channel',$id,__ACTION__ ,"","add");
					$this->success ( '添加成功' );
				}
			}
		}
		import ( "@.ORG.Page" );
		$where = 'id <> 442 and puid <> 0' ;
		if(!empty($_GET['soid'])) {
			$zh_soid=escape_string($_GET['soid']);
			$where .= " and `cid` = '{$zh_soid}'";
		}
		if(!empty($_GET['soname'])){
			$zh_soname=escape_string($_GET['soname']);
			$where .= " and `checkname` like '%{$zh_soname}%'";
		}
		if(!empty($_GET['sopuid'])) {
			$zh_sopuid=escape_string($_GET['sopuid']);
			$where .= " and `puid` = '{$zh_sopuid}'";
		}
		if(!empty($_GET['sopuname'])) {
			$zh_sopuname=escape_string($_GET['sopuname']);
			$populist = $channel_model->table('pu_popularparter')->where("`pu_name` like '%{$zh_sopuname}%'")->findAll();
			$puid = "";
			foreach($populist  as $key=>$value) {
				$puid .= ",'".$value['id']."'";
			}
			$where .= " and `puid` in (".substr($puid,1).")";
		}
		$count = $channel_model->table('sj_school_channel')->where($where)->count();
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 15;
		$p = new Page($count,$getpage);
		$this->assign("getpage",$getpage);
		$ret = $channel_model->table('sj_school_channel')->limit($p->firstRow.','.$p->listRows)->where($where)->order('id desc')->findAll();
		foreach($ret as $key => $value) {
			$ret[$key]['pu_name'] = $channel_model->table('pu_popularparter')->where("id = {$value['puid']}")->field("pu_name")->find();
		}
		$page = $p->show ();
		$this->assign('page',$page);
		$this->assign("ret",$ret);;
		$this->display();
	}
	
	protected function getRealIp() {
		if (! empty ( $_SERVER ['HTTP_CLIENT_IP'] )){ // check ip from share internet
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )){ // to check ip is pass from proxy{
			$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER ['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	protected function _upload($id,$imgid = 0) {
		import ( "@.ORG.UploadFile" );
		$upload = new UploadFile ( );
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置上传文件类型
		$upload->allowExts = explode ( ',', 'jpg,gif,png,jpeg' );
		//设置附件上传目录
		///data/att/m.goapk.com/image/201110/09
		$upload->savePath = '/data/att/m.goapk.com/image/' . date ( "Ym/d", time () ) . '/';
		//$upload->savePath = 'D:/APMServ5.2.6/APMServ5.2.6/www/htdocs/wwwroot/newadmin.goapk.com/img/Uploads/';
		//设置需要生成缩略图，仅对图像文件有效
		$upload->thumb = true;
		// 设置引用图片类库包路径
		$upload->imageClassPath = '@.ORG.Image';
		//设置需要生成缩略图的文件后缀
		$upload->thumbPrefix = 'm_,s_'; //生产2张缩略图
		//设置缩略图最大宽度
		$upload->thumbMaxWidth = '570,181';
		//设置缩略图最大高度
		$upload->thumbMaxHeight = '390,113';
		//设置上传文件规则
		$upload->saveRule = uniqid;
		//删除原图
		$upload->thumbRemoveOrigin = true;
		if (! $upload->upload ()) {
			//捕获上传异常
			$this->error ( $upload->getErrorMsg () );
		} else {
			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo ();
			foreach ( $uploadList as $key => $value ) {
				$data [] ['image'] = $uploadList [$key] ['savename'];
				$data [] ['create_time'] = time ();
			}
		}
		$model = M ( 'school_image' );
		//保存当前数据对象
		foreach ( $data as $key => $value ) {
			if (! empty ( $data [$key] ['image'] )) {
				if($imgid == 0) {
					$list = $model->add ( array ('uid' => $id, 'url' => '/image/' . date ( 'Ym/d', time () ) . '/m_' . $data [$key] ['image'], 's_url' => '/image/' . date ( 'Ym/d', time () ) . '/s_' . $data [$key] ['image'], 'type' => '1', 'status' => '1' ) );
					//$list = $model->add ( array ('uid' => $id, 'url' => '/img/Uploads/m_' . $data [$key] ['image'], 's_url' => '/img/Uploads/s_' . $data [$key] ['image'], 'type' => '1', 'status' => '1' ) );
				} else {
					$data=array('uid' => $id, 'url' => '/image/' . date ( 'Ym/d', time () ) . '/m_' . $data [$key] ['image'], 's_url' => '/image/' . date ( 'Ym/d', time () ) . '/s_' . $data [$key] ['image'], 'type' => '1', 'status' => '1');
					$log_result = $this->logcheck(array('id'=>$imgid),'sj_school_image',$data,$model);
					$list = $model->where("`id` = $imgid")->save($data);
					//$list = $model->where("`id` = $imgid")->save(array('uid' => $id, 'url' => '/img/Uploads/m_' . $data [$key] ['image'], 's_url' => '/img/Uploads/s_' . $data [$key] ['image'], 'type' => '1', 'status' => '1'));
				}
			}
		}
		if ($list !== false) {
			if($imgid == 0){
				$this->writelog("id为{$id}的学校添加照片,照片id为{$list}",'sj_school_image',$list,__ACTION__ ,"","add");
			}else{
				$this->writelog("id为{$id}的学校修改照片,照片id为{$imgid}.{$log_result}",'sj_school_image',$imgid,__ACTION__ ,"","edit");
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>