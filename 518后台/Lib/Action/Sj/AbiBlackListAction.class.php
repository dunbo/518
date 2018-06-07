<?php
//ABI黑名单列表
class AbiBlackListAction extends CommonAction {
    public function abiList(){
    	$type =0;
    	if($type==1){
    		$type = " and sj_abi.type=1 ";
    		$tpls = "Sj:Abilist:whitelist";
    		
    	}else{
    		$type = " and sj_abi.type=0 ";
    		$tpls = "Sj:Abilist:blacklist";
    	}
    	import('@.ORG.Page');
    	$model = new Model();
    	$where = "";
    	$where.= "sj_abi.status=1 {$type} and sj_soft.hide=1 and sj_soft.status=1 and sj_soft.claim_status=2";
    	if (!empty($_GET['package'])){
   			$package = $_GET['package'];
		    $where.="  and sj_abi.package = '{$package}'";
			$this->assign('package', $package);
		}
    	if (!empty($_GET['softname'])){
   			$softname = $_GET['softname'];
		    $where.="  and sj_soft.softname like '%{$softname}%'";
		    $this->assign('softname', $softname);
		}
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
		
		$total = $model->table('sj_abi')->where($where)->count();		
		$page = new Page($total, $limit);
		/*$soft = $model->table('sj_soft')->field('package,dev_name,softname')->where('hide=1 and status=1 and claim_status=2')->select();
		$soft_arr = array();
		foreach ($soft as $val){
			$soft_arr[$val['package']][] = $val;
		}*/
		$list = $model->table('sj_abi')->join('sj_soft on sj_abi.package = sj_soft.package')->field('sj_abi.id,sj_abi.package,sj_abi.abi,sj_abi.add_time,sj_soft.softname')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('sj_abi.add_time desc')->select();
		foreach ($list as $key=>$val){
			//$list[$key]['dev_name']  = $soft_arr[$val['package']][0]['dev_name'];
			//$list[$key]['softname']  = $soft_arr[$val['package']][0]['softname'];
			$list[$key]['abi'] = $this->isAbi($val['abi']);
		}
		
	 
		//echo $model->getlastsql();
		//var_dump($list);
		$page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign('page', $page->show());
		$this->assign('list', $list);
		$this->display($tpls);
    }
    public function isAbi($type){
   		 switch ($type){
			case 1:
			return 'ABI_ARMEABI(普通机型)';
			break;
			case 2:
			return 'ABI_ARMEABI_V7A(普通机型)';
			break;
			case 4:
			return 'ABI_X86(pc)';
			break;
			case 8:
			return 'ABI_MIPS(mips机型)';
			break;
		}
    }
    public function oper(){
    	$model = new Model();
		$sub_type = trim($_POST['sub_type']);
		$type = 0;
		if($sub_type=='edit'){ //编辑数据
			$res_edit = '';
			
		}else{ //添加数据操作
			$package = trim($_POST['package']);
			$abi = trim($_POST['abi']);
			$is_package  = $model->table('sj_abi')->where("package='{$package}' and status=1")->select();
			if($is_package){
				$this->error('包名已存在该列表[ '.$package.']');
			}
			$data = array(
					'package' =>"{$package}",
					'abi'     =>"{$abi}",
			        'type'    =>$type,
					'status'  =>1,
					'add_time'=>time(),
					);
			$res_add = $model->table('sj_abi')->add($data);
		}
   		if($res_add){
		    $this->success('添加成功！');
		}elseif($res_edit){
		   	$this->success('编辑成功！');
		}else{
			$this->error('非法操作，请联系管理员');
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
	       $res = $model->table('sj_soft')->join('pu_developer ON sj_soft.dev_id = pu_developer.dev_id')->field('sj_soft.dev_id,sj_soft.softname,pu_developer.dev_name,pu_developer.email,sj_soft.package,sj_soft.softid,pu_developer.type,sj_soft.abi')->where("sj_soft.package = '{$package}'  and sj_soft.status=1 and sj_soft.hide=1 ")->find();
	       if($res){
				$result = array ('success' => true, 'msg'=>'获取成功！','softname' =>$res['softname'],'dev_id' =>$res['dev_id'],'dev_name'=>$res['dev_name'],'softid'=>$res['softid'],'email'=>$res['email'],'type'=>$res['type'],'abi'=>$res['abi']);
				echo json_encode ( $result );
				exit ();
			}else{
				$result = array ('success' => false,'msg'=>'获取失败');
				echo json_encode ( $result );
				exit ();
			}  
	}
	public function abi_del(){
		$flag = true;
		$type = $_GET['type'];
		if (!isset($_GET['id'])){
			$this->error('ID不能为空');
		}
		$id = json_decode($_GET['id']);
		if (!$id){
			$this->error('ID格式错误');
		}
		$model = new Model();
		foreach ($id as $v){
			$ret = $model->table('sj_abi')->where("id = $v and type=$type")->field('status')->select();
			if ($ret[0]['status'] != 0){
				$ret = $model->table('sj_abi')->where("id = $v and type=$type")->save(array('status' => 0));
				if (!$ret)
					$flag = false;
				else
					$this->writelog('删除了ID为' . $v . 'ABI黑名单');
			}
		}
		if ($flag == false){
			$this->error('删除失败');
		}
		else{
			$this->success('删除成功');
		}
	
	}
}
?>
