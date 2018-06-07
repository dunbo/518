<?php

class SoftPiracyWarningAction extends CommonAction {
	/*
	 *  盗版校验软件列表
	 *  create by 黄文强 at 2013/01/31
	 */
	public function ShowPiracy()
	{
		import('@.ORG.Page');
		$cond = array();
		$where = '';
		if (!empty($_GET['seach_val']))
		{  
		    $show_type = $_GET['seach_type'];
		    $seach_val = $_GET['seach_val'];
			if($show_type==1){
				$seach_type = 'p.softname';
			    $where = "$seach_type like '%{$seach_val}%' and ";
			}elseif($show_type==2){
			    $seach_type = 'p.package';
			    $where = "$seach_type = '{$seach_val}' and ";
			}elseif($show_type==3){
				$seach_type = 'u.dev_name';
				$where = "$seach_type liek '%{$seach_val}%' and ";
			}elseif($show_type==4){
				$seach_type = 'u.email';
				$where = "$seach_type = '".trim($seach_val)."' and ";
			}
			
			$this->assign('seach_val', $seach_val);
			$this->assign('show_type', $show_type);
		}
		if(!empty($_GET['dev_type'])){
		    if( $_GET['dev_type'] == 2 ){
		        $where .= " u.type = 0 and ";
		    }elseif($_GET['dev_type'] == 1){
		        $where .= " u.type = 1 and ";
		    }
		    $this->assign('dev_type', $_GET['dev_type']);
		}
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
		$param = http_build_query($_REQUEST);

		$model = new Model();
		$where .= $where.'p.status=1  ORDER BY  p.update_at  DESC  ';
		$table = 'sj_piracy_soft p  left join pu_developer u on u.dev_id=p.dev_id';
		$total = $model->table($table)->where($where)->count();
		$page = new Page($total, $limit);
		$piracyList = $model->table($table)->where($where)->field('p.*,u.type,u.dev_name,u.email')->limit($page->firstRow . ',' . $page->listRows)->select();
		$package = array();
		foreach($piracyList as $v){
			$package[] = $v['package'];
		}
		$where = array(
			'package' => array('in',$package),
			'status'=>1,
			'hide'=>1
		);
		$softinfo = get_table_data($where,"sj_soft","package","softname,package");
		$this->assign('softinfo', $softinfo);
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this->assign('page', $page->show());
		$this->assign('count', $total);
		$this->assign('piracyList', $piracyList);
		$this->display('ShowPiracy');
	}
	
	public function DeletePiracy()
	{
		$time = time();
		$flag = true;
		if (!isset($_GET['id']))
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->error('ID不能为空');
		}
		$id = json_decode($_GET['id']);
		if (!$id)
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->error('ID格式错误');
		}
		$model = new Model();
		$error = '';
		foreach ($id as $v)
		{
			if(!$v) continue;
			$ret = $model->table('sj_piracy_soft')->where("id = {$v}")->field('status,from_type,package')->find();
			if ($ret['status'] != 0 && $ret['from_type'] ==1)
			{
				$ret = $model->table('sj_piracy_soft')->where("id = $v")->save(array('status' => 0, 'update_at' => $time));
				if (!$ret)
					$flag = false;
				else
					$this->writelog('删除了ID为' . $v . '盗版白名单软件','sj_piracy_soft',$v,__ACTION__ ,"","del");
			}else if($ret['from_type'] == 2){
				$error .= $ret['package'].",";
				$flag = false;
			}
		}
		if ($flag == false)
		{	
			if($error){
				$this->error('包名为'.$error.'官方自动加入不可删除');
			}else{
				$this->error('删除失败');
			}
		}
		else
		{
			//$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
			$this->success('删除成功');
		}
	}
	public function GetSoftname(){
	       $package = trim($_POST['package']);
	       if(empty($package) || $package=='获取失败'){
	       	  $result = array ('success' => false,'msg'=>'请输入正确包名');
			  echo json_encode ( $result );
			  exit ();
	       }
	       $model = new Model();
	       $is_package = $model->table('sj_piracy_soft')->where("package = '{$package}' and status=1")->select();
	       //$res = $model->table('sj_soft')->field('dev_id,softname,dev_name,package')->where("package = '{$package}' and claim_status=2 and status=1 and hide=1")->select();
	       if($is_package){
	       	  $result = array ('success' => false,'msg'=>'该包名已被添加过');
			  echo json_encode ( $result );
			  exit ();
	       }
	       $res = $model->table('sj_soft')->join('pu_developer ON sj_soft.dev_id = pu_developer.dev_id')->field('sj_soft.dev_id,sj_soft.softname,pu_developer.dev_name,sj_soft.package')->where("sj_soft.package = '{$package}' and sj_soft.claim_status=2 and sj_soft.status=1 and sj_soft.hide=1 ")->find();
	        
	       //echo $model->getLastSql();
			if($res){
				$result = array ('success' => true, 'msg'=>'获取成功！','softname' =>$res['softname'],'dev_id' =>$res['dev_id'],'dev_name' =>$res['dev_name']);
				echo json_encode ( $result );
				exit ();
			}else{
				$result = array ('success' => false,'msg'=>'该包名不存在于已上架列表');
				echo json_encode ( $result );
				exit ();
			}  
	}
	public function EditPiracy(){
		$model = new Model();
		$sub_type = trim($_POST['sub_type']);
		$res = false;
		//$show_str = '';
		$time = time();
		if($sub_type == 'add'){
				if (empty($_POST['softname'])){
					$this->error('软件名称不能为空');
				}
				if (empty($_POST['package'])){
					$this->error('包名不能为空');
				}
				if (empty($_POST['dev_id']) || empty($_POST['dev_name'])){
					//$this->error('包名不存在已上架软件列表');
				    $this->error('添加失败');
				}
			for ($i = 0 ;$i<count($_POST['package']); $i++){
				 	$softname = trim($_POST['softname'][$i]);
					$package =  trim($_POST['package'][$i]);
					$dev_id  =  trim($_POST['dev_id'][$i]);
					$dev_name  =  trim($_POST['dev_name'][$i]);
					//echo $softname.'---'.$package.'---'.$dev_id.'<br>';
				if (empty($softname)){
					$this->error('软件名称不能为空');
				}
				if (empty($package)){
					$this->error('包名不能为空');
				}
				if (empty($dev_id) || empty($dev_name)){
					//$this->error('包名不存在已上架软件列表');
				    $this->error('添加失败');
				}
				$is_package  = $model->table('sj_piracy_soft')->where("package='{$package}' and status=1")->select();
				$is_softname = $model->table('sj_piracy_soft')->where("softname='{$softname}' and status=1")->select();
				if($is_package){
					$this->error('包名已存在该列表 '.$package);
				}
				if($is_softname){
					$this->error('软件名 已存在该列表'.$softname);
				}
				$data = array(
					'softname'=>"{$softname}",
					'dev_name'=>"{$dev_name}",
					'package' =>"{$package}",
					'dev_id'  =>"{$dev_id}",
					'create_at'=>"{$time}",
					'update_at'=>"{$time}"
					);
				$res_add = $model->table('sj_piracy_soft')->add($data);
				//$show_str.= '软件名：'.$softname.'&nbsp&nbsp包名：'.$package.'<br>';
			}
		}elseif($sub_type == 'edit'){
			
			
			    $softname = trim($_POST['softname'][0]);
				$package =  trim($_POST['package'][0]);
				$edit_id =  trim($_POST['edit_id']);
			    if(empty($package)){
					$this->error('请填写包名');
				}
				if(empty($softname)){
					$this->error('请填写软件名');
				}
				if(empty($edit_id)){
					$this->error('无法获取开发者ID');
				}
				$data = array(
							'softname'=>"{$softname}",
							'package' =>"{$package}",
							'update_at'=>"{$time}"
						 );
				$log_result = $this->logcheck(array('id'=>$edit_id),'sj_piracy_soft',$data,$model);
				$res_edit = $model->table('sj_piracy_soft')->where("id={$edit_id}")->save($data);
				//$show_str.= '软件名：'.$softname.'&nbsp&nbsp包名：'.$package.'<br>';
		}else{
			$this->error('非法操作，请联系管理员');
		}
		if($res_add){
		    $this->writelog('添加了ID为' . $res_add . '盗版白名单软件','sj_piracy_soft',$res_add,__ACTION__ ,"","add");
		    $this->success('添加成功！');
		}elseif($res_edit){
			$this->writelog('编辑了ID为' . $_POST['edit_id'] . '盗版白名单软件.'.$log_result,'sj_piracy_soft',$_POST['edit_id'],__ACTION__ ,"","edit");
		   	$this->success('编辑成功！');
		}else{
			$this->error('非法操作，请联系管理员');
		}
	}
}