<?php
/**
 * 安智网产品管理平台 信息管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.4.25
 * ----------------------------------------------------------------------------
*/
class SoftbankAction extends CommonAction {
    private $lists;             //列表
    private $conf_db;           //配置表
    private $config_list;       //配置列表
    private $hashs;             //默认hashs
    private $map;               //条件
    private $soft_db;           //软件表
    private $soft_list;         //软件列表
    private $soft_comment_db;      //软件附属表
    private $soft_comment_list;    //软件附属列表
    private $feedback_db;       //软件反馈表
    private $feedback_list;     //软件反馈列表
    private $soft_claim_db;      //软件认领表
    private $soft_claim_list;    //软件认领列表
    private $soft_lack_db;      //缺乏列表
    private $soft_lack_list;    //缺乏软件
    private $softid;            //软件id
    private $sid;               //临时ID
    private $returnurl;         //返回地址
    private $order;             //排序

	public function index() {
        exit;
	}
    //信息管理__软件认领列表
  public function recommend()
  {
  	$model = M('soft');
  	if($_POST['sosoid']&&$_POST['sosoid']!="ID"){
  		if( is_numeric($_POST['sosoid']) ){
  			$ret = $model->where(array("softid" =>$_POST['sosoid'],"status" => 1 , "hide" => 1))->findAll();
  			$this->assign('select',1);
  			$this->assign('ret',$ret);
  		}else{
  			$this->error('用ID搜索，请输入纯数字。');
  		}
  	}elseif($_POST['soso']&&$_POST['sosoid']!="包名"){
  		if(mb_strlen($_POST['soso'])>=2)
  		{
  			$ret = $model->where(array('package'=>array('LIKE','%'.$_POST['soso'].'%') , 'status' => 1 ,  'hide' => 1))->findAll();
  			$this->assign('select',1);
  			$this->assign('ret',$ret);
  		}else{
  			$this->error('包名，请输入2个字以上搜索');
  		}
  	}
  	$modelbank = M('soft_candidate');
  	if($_GET['delsoftid'])
  	{
  		$data = array('status' => 0 ,'updated_at' => time());
		$package = $modelbank->where(array("id" =>$_GET['delsoftid']))->find();
  		$modelbank->where(array("id" =>$_GET['delsoftid']))->data($data)->save();
  		$this->writelog('软件库管理_推荐位软件库_删除了id为['.$_GET['delsoftid'].']包名为['.$package['package'].']的推荐软件','sj_soft_candidate',$_GET['delsoftid'],__ACTION__ ,"","del");
  		$this->success('删除成功');
  	}
  	if($_POST['softid'])
  	{
  		$ret = $model->where(array("softid" =>$_POST['softid']))->find();
  		$data = array( 'package' => $ret['package'] ,  'status' => 1 , 'created_at' => time());
  		if(($modelbank->where(array("package" => $ret['package'],"status" => 1))->find()?false:true)){
  			$ret=$modelbank->data($data)->add();
  			$this->writelog('添加了包名为['.$ret['package'].']的推荐','sj_soft_candidate',$ret,__ACTION__ ,"","add");
  			$this->success('添加成功');
  		}else{
  			$this->error('推广中已经有此包名的软件');
  		}
  	}elseif($_POST['srch_id'] || $_POST['srch_name'] || $_POST['srch_pkg']){
            $where_arr = array();
            if($_POST['srch_id']){
                 $where_arr[] = " softid = ".$_POST['srch_id'];
             }
            if($_POST['srch_name']){
                $where_arr[] = " softname like '%".$_POST['srch_name']."%'";
            }
            if($_POST['srch_pkg']){
                $where_arr[] = " package like '%".$_POST['srch_pkg']."%'";
            }
           $where_arr[]= " hide = 1";
           $where_arr[] = " status = 1 ";
           $where_str = implode(" and ",$where_arr);
           $result = $model -> where($where_str) -> select();
            foreach($result as $info){
               $pkg_arr[] = $info['package'];
            }
           if(count($pkg_arr) == 1) $pkg_str = " = '".$pkg_arr[0]."'";
           else if(count($pkg_arr) > 1) $pkg_str = " in ('".implode("','",$pkg_arr)."')";
           $where_str = " and `package`".$pkg_str;
           $this -> assign("srch_name",$_POST["srch_name"]);
           $this -> assign("srch_id",$_POST["srch_id"]);
           $this -> assign("srch_pkg",$_POST["srch_pkg"]);
    }
  	import("@.ORG.Page"); //导入分页类
    $where = "status = 1 ".$where_str;
  	$count = $modelbank->where($where)->count();    //计算总数
    $p = new Page ( $count, 15 );
    $list = $modelbank->limit($p->firstRow.','.$p->listRows)->where($where)->order('id desc')->findAll();
    $page = $p->show ();
    foreach($list as $key => $value)
    {
    	$softret = $model->where("package = '{$value['package']}' and status = 1 and hide = 1" )->find();
    	$list[$key]['softname'] = $softret['softname'];
    	$list[$key]['soft_id'] = $softret['softid'];
    }
    $this->assign ( "page", $page );
    $this->assign ( "list", $list );
  	$this->display();
  }

  public function recommend_new()
  {
  	$model = M('soft');
	$srch_type = isset($_REQUEST['srch_type']) ? $_REQUEST['srch_type'] : 'n';
  	if($_POST['sosoid']&&$_POST['sosoid']!="ID"){
  		if( is_numeric($_POST['sosoid']) ){
  			$ret = $model->where(array("softid" =>$_POST['sosoid'],"status" => 1,"hide" =>1))->findAll();
  			$this->assign('select',1);
  			$this->assign('ret',$ret);
  		}else{
  			$this->error('用ID搜索，请输入纯数字。');
  		}
  	}elseif($_POST['soso']&&$_POST['sosoid']!="包名"){
  		if(mb_strlen($_POST['soso'])>=2)
  		{
  			//$ret = $model->where(array('package'=>array('LIKE','%'.$_POST['soso'].'%') , 'status' => 1 ,  'hide' => 1))->findAll();
			$ret = $model->where(array('package'=>$_POST['soso'] , 'status' => 1 ,  'hide' => 1))->findAll();
  			$this->assign('select',1);
  			$this->assign('ret',$ret);
  		}else{
  			$this->error('包名，请输入2个字以上搜索');
  		}
  	}
  	$modelbank = M('soft_new_candidate');
  	if($_GET['delsoftid'])
  	{
		$package = $modelbank->where(array("id" =>$_GET['delsoftid']))->find();
		$data = array('status' => 0 ,'updated_at' => time());
  		$modelbank->where(array("id" =>$_GET['delsoftid']))->data($data)->save();
  		$this->writelog('删除了id为['.$_GET['delsoftid'].']包名为['.$package['package'].']的最新推荐软件','soft_new_candidate', $_GET['delsoftid'],__ACTION__ ,"","del");
  		$this->success('删除成功');
  	}
	
  	if($_GET['editid'])
  	{
	  	$data = array( 
			'updated_at' => time(), 
			'begintime'=>strtotime($_POST['begintime'].' 00:00:00'),
			'endtime'=>strtotime($_POST['endtime'].' 23:59:59'),
		);
		$package = $modelbank->where(array("id" =>$_GET['editid']))->find();
  		$modelbank->where(array("id" => $_GET['editid']))->data($data)->save();
  		$this->writelog('编辑了id为['.$_GET['editid'].']包名为['.$package['package'].']的最新推荐软件','soft_new_candidate', $_GET['editid'],__ACTION__ ,"","edit");
  		$this->success('编辑成功');
  	}
	
  	if($_POST['softid'])
  	{
		if (empty($_POST['begintime'])) {
			$this->error('请输入开始时间');
		} elseif (empty($_POST['begintime'])) {
			$this->error('请输入开始时间');
		} elseif(strtotime($_POST['begintime'].' 00:00:00') > strtotime($_POST['begintime'].' 23:59:59')) {
			$this->error('开始时间大于结束时间，请重新输入');
		}
	
	
  		$ret = $model->where(array("softid" =>$_POST['softid']))->find();
  		$data = array( 
			'package' => $ret['package'] ,  
			'status' => 1 , 
			'created_at' => time(), 
			'begintime'=>strtotime($_POST['begintime'].' 00:00:00'),
			'endtime'=>strtotime($_POST['endtime'].' 23:59:59'),
		);
  		if(($modelbank->where(array("package" =>$ret['package'],"status" => 1))->find()?false:true)){
  			$id = $modelbank->data($data)->add();
  			$this->writelog('添加了包名为['.$ret['package'].']的最新推荐软件', 'soft_new_candidate', $id,__ACTION__ ,"","add");
  			$this->success('添加成功');
  		}else{
  			$this->error('最新推荐软件中已经有此包名的软件');
  		}
  	}elseif($_POST['srch_id'] || $_POST['srch_name'] || $_POST['srch_pkg'] || $srch_type){
            $where_arr = array();
            if($_POST['srch_id']){
                 $where_arr[] = " softid = ".$_POST['srch_id'];
             }
            if($_POST['srch_name']){
                $where_arr[] = " softname like '%".$_POST['srch_name']."%'";
            }
            if($_POST['srch_pkg']){
                $where_arr[] = " package like '%".$_POST['srch_pkg']."%'";
            }
			$now = time();

			if (!empty($where_arr)){
				$where_arr[]= " hide = 1";
				$where_arr[] = " status = 1 ";
				$where_str = implode(" and ",$where_arr);
				$result = $model -> where($where_str) -> select();
				foreach($result as $info){
					$pkg_arr[] = $info['package'];
				}
				if(count($pkg_arr) == 1) {
					$pkg_str = " = '".$pkg_arr[0]."'";
				} else if(count($pkg_arr) > 1) {
					$pkg_str = " in ('".implode("','",$pkg_arr)."')";
				}
			}
		   
			switch($srch_type) {
				case 'e':
					$where_str = " endtime<{$now}";
				break;

				case 'f':
					$where_str = " begintime>{$now}";
				break;	

				case 'n':
				default:
					$where_str = " begintime<={$now} and endtime>={$now}";
				break;
			}
		   if ($pkg_arr) $where_str .= " and `package`".$pkg_str;

           $this -> assign("srch_name",$_POST["srch_name"]);
           $this -> assign("srch_id",$_POST["srch_id"]);
           $this -> assign("srch_pkg",$_POST["srch_pkg"]);
           $this -> assign("srch_type",$srch_type);
    }
  	import("@.ORG.Page"); //导入分页类
    $where = "status = 1 and ".$where_str;

  	$count = $modelbank->where($where)->count();    //计算总数
    $p = new Page ( $count, 15 );
    $list = $modelbank->limit($p->firstRow.','.$p->listRows)->where($where)->order('id desc')->findAll();
    $page = $p->show ();
    foreach($list as $key => $value)
    {
    	$softret = $model->where("package = '{$value['package']}' and status = 1 and hide = 1" )->find();
    	$list[$key]['softname'] = $softret['softname'];
    	$list[$key]['soft_id'] = $softret['softid'];
    }
    $this->assign ( "page", $page );
    $this->assign ( "list", $list );
  	$this->display();
  }
  
  public function soft_old()
  {
  	$model = M('soft');
  	if($_POST['sosoid']&&$_POST['sosoid']!="ID"){
  		if( is_numeric($_POST['sosoid']) ){
  			$ret = $model->where(array("softid" =>$_POST['sosoid'],"status" => 1, "hide" => 1))->findAll();
  			$this->assign('select',1);
  			$this->assign('ret',$ret);
  		}else{
  			$this->error('用ID搜索，请输入纯数字。');
  		}
  	}elseif($_POST['soso']&&$_POST['sosoid']!="包名"){
  		if(mb_strlen($_POST['soso'])>=2)
  		{
  			$ret = $model->where(array('package'=>array('LIKE','%'.$_POST['soso'].'%') , 'status' => 1 ,  'hide' => 1))->findAll();
  			$this->assign('select',1);
  			$this->assign('ret',$ret);
  		}else{
  			$this->error('包名，请输入2个字以上搜索');
  		}
  	}
  	$modelbank = M('soft_deprecated');
  	if($_GET['delsoftid'])
  	{
  		$data = array('status' => 0 ,'updated_at' => time());
  		$package = $modelbank->where(array("id" => $_GET['delsoftid']))->find();
  		$modelbank->where(array("id" => $_GET['delsoftid']))->data($data)->save();
  		$this->writelog('软件库管理_陈旧软件提示库_删除了id为['.$_GET['delsoftid'].']包名为['.$package['package'].']的陈旧软件','sj_soft_deprecated',$_GET['delsoftid'],__ACTION__ ,"","del");
  		$this->success('删除成功');
  	}
  	if($_POST['softid'])
  	{
  		$ret = $model->where(array("softid" => $_POST['softid']))->find();
  		$data = array( 'package' => $ret['package'] , 'status' => 1 , 'created_at' => time());
  		if(($modelbank->where("package = '{$ret['package']}' and status = 1")->find()?false:true)){
  			$softid = $modelbank->data($data)->add();
  			$this->writelog('添加了id为['.$softid.']包名为['.$ret['package'].']的陈旧软件','soft_soft_deprecated',$softid,__ACTION__ ,"","del");
  			$this->success('添加成功');
  		}else{
  			$this->error('陈旧软件中已经有此包名的软件');
  		}
  	}
  	import("@.ORG.Page"); //导入分页类
  	$count = $modelbank->where("status = 1")->count();    //计算总数
    $p = new Page ( $count, 5 );
    $list = $modelbank->limit($p->firstRow.','.$p->listRows)->where("status = 1")->order('id desc')->findAll();
    $page = $p->show ();
    foreach($list as $key => $value)
    {
    	$softret = $model->where("package = '{$value['package']}'" )->find();
    	$list[$key]['softname'] = $softret['softname'];
    	$list[$key]['soft_id'] = $softret['softid'];
    }
    $this->assign ( "page", $page );
    $this->assign ( "list", $list );
  	$this->display();
  }

}
?>
