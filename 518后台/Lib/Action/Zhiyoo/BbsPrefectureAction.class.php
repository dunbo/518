<?php
class BbsPrefectureAction extends CommonAction
{
	private $bbsdiscuzdb;
	private $model;
	private $bbstabal = 'x15_forum_forum';
	private $zhiyootabale = "zy_bbs_plate";
	private $zhiyooarea = "zy_bbs_plate_area";
	
	public function _initialize() {
        parent::_initialize();
		$this->bbsdiscuzdb = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.Zhiyoo');
	}
	function prefecture_list(){
		$order = 'ASC';$orderBy = 'status DESC';
		$where = $class = $filterurl = '';
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		
		if (isset($_REQUEST['class']) and $_REQUEST['class']>0) {
			$class = intval($_REQUEST['class']);
			$where = $where." class=$class and ";
		}
		
		$orderBy = $orderBy." , modifytime $order";
		
		$fids = "fid,fup,type,name,status";
		$fidstr = "b_fid, b_fup, b_type, b_name, b_status, createdate";
		/*
		$maxplat = $this->model->table($this->zhiyootabale)->where("b_fup=0 and b_fid<10000")->max('b_fid');
		if ($maxplat==null) {
			$maxplat = 0;
		}
		$sqlvalue = '';
		$bbsnews = $this->bbsdiscuzdb->table($this->bbstabal)->where("fup=0 and type='group' and fid>$maxplat")->field($fids)->order('fid asc')->limit(100)->select();
		
		if($bbsnews!==null){
			foreach ($bbsnews as $k=>$v){
				$createdate = date('Y-m-d H:i:s');
				$sqlvalue .= "('{$v['fid']}','{$v['fup']}','{$v['type']}','{$v['name']}','{$v['status']}','{$createdate}'),";
			}
			$sqlvalue = substr($sqlvalue, 0,-1);
			$result = $this->model->query("INSERT INTO $this->zhiyootabale ($fidstr) VALUES ".$sqlvalue);
		}*/
		$where = $where." b_fup=0 and status!='-1'";
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->model -> table($this->zhiyootabale) -> where($where)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 30;
		$Page = new Page($count,$prepage , $param);
		$z_fids = "id,class,b_fid, b_fup, b_type, b_name, b_status,b_level,b_class,name,area_num,icon,status,createdate";
// 		$data = $this->model->query("select $z_fids from $this->zhiyootabale where $where ");
		$data = $this->model->table($this->zhiyootabale)->where($where)->field($z_fids)->order($orderBy)->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($data as $k=>$v){
			$b_fid = $v['b_fid'];
			if ($b_fid!=0) {
				$platcount = $this->bbsdiscuzdb->table($this->bbstabal)->where("fup='{$b_fid}'")->count();
				if ($v['area_num']<$platcount) {
					$ret = $this->model->query("UPDATE $this->zhiyootabale SET `area_num` = '$platcount' WHERE `b_fid` =$b_fid");
				}else {
					$platcount = $v['area_num'];
				}
			}else{
				$platcount = 0;
			}
			if ($v['b_class']==999999) {
				$data[$k]['b_class'] = '';
			}
			$data[$k]['plat'] = $platcount;
		}
		
		$pageList = $Page->show();
		if(isset($_GET['lr']) and $_GET['lr']){
			$lr = $_GET['lr'];
			$filterurl .= '/lr/'.$lr;
		}else{
			$lr = 30;
		}
		if(isset($_GET['p']) and $_GET['p']){
			$p = $_GET['p'];
			$filterurl .= '/p/'.$p;
		}else{
			$p = 1;
		}

		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		
		$this->assign('pageList',$pageList);
		($order=='ASC')?$order = 'DESC':$order='ASC';
		$this->assign('order',$order);
		$this->assign('classid',$class);
		$this -> assign('prefecturelist',$data);
		$this -> display();
	}
	
	function del_prefecture(){
		$fid = intval($_REQUEST['fid']);
		if ($fid<1) {
			$this -> writelog('论坛版块—删除b_fid为'.$fid.'专区失败',"zy_advdata",$fid,__ACTION__ ,"","del");
			$this->success("参数有误，删除失败！");
		}
		$ret = $this->model->query("UPDATE $this->zhiyootabale SET `status` = '-1' WHERE `b_fid` =$fid");
		$ret = $this->model->query("UPDATE $this->zhiyootabale SET `status` = '-1' WHERE `b_fup` =$fid");
		$this -> writelog('论坛版块—删除b_fid为'.$fid.'的专区同时删除专区下版块',$this->zhiyootabale,$fid,__ACTION__ ,"","del");
		$this->success("删除成功！");
	}
	function edit_prefecture(){
		$fid = intval($_REQUEST['fid']);
		$act = addslashes(trim($_REQUEST['action']));
		
		if ($fid>0 and $act=='edit') {
			$dataone = $this->model->table($this->zhiyootabale)->field('class,b_fid,b_fup,b_name,name,icon,buyershowtid')->where(array('b_fid'=>$fid))->find();
			$this -> assign('editprefecture',$dataone);
			$this -> display();
		}
		if ($fid>0 and $act=='status'){
			$status = abs(intval($_REQUEST['status']));
			$ret = $this->model->query("UPDATE $this->zhiyootabale SET `status` = '$status' WHERE `b_fid` =$fid");
			if ($ret===false) {
				$this -> writelog('论坛版块—状态编辑b_fid为'.$fid.'失败，状态status为'.$status,$this->zhiyootabale,$fid,__ACTION__ ,"","edit");
				$this->success("状态编辑失败！");
			}
			$strstatus = $status ? '使用' : '停用';
			$this -> writelog('论坛版块—编辑ID为'.$fid.'的状态为'.$strstatus,$this->zhiyootabale,$fid,__ACTION__ ,"","edit");
			$this->success("状态编辑成功！");
		}
		if($_POST and $act=='editprefecture'){
			$img_path ='';
			
			if($_FILES['p_logo']['size']>0||$_FILES['p_logo']['size']>0){
				$imginfo = $this->_upload();
				if($imginfo['image'][0]['url']) $img_path = $imginfo['image'][0]['url'];
			}
			
			($img_path!='')?$setimg = ",`icon`='$img_path'":$setimg = '';
			$classnamearr = array('0'=>'默认','1'=>'普通专区','2'=>'手机专区','3'=>'游戏专区');
			$classid = (isset($_POST['classname']))?intval($_POST['classname']):1;
// 			$classid = intval($_POST['classname']);
			$fids = intval($_POST['fids']);
			$buyershowtid = intval($_POST['buyershowtid']);
			$names = addslashes(trim($_POST['names']));

			$dataone = $this->model->table($this->zhiyootabale)->field('id,class,b_fid,b_fup,b_type,b_name,name,icon,buyershowtid')->where("b_fid=$fids")->find();
			if (($names=='' or $names == $dataone['name']) and $classid==$dataone['class'] and $buyershowtid==$dataone['buyershowtid'] and $img_path=='') {
				$this->success("没有更改任何项！");
				exit;
			}
			if($buyershowtid){
				$res = $this->bbsdiscuzdb->table('x15_forum_thread')->field('fid')->where(array('tid'=>$buyershowtid))->find();
				if(!$res || $res['fid'] != $fids) $this->error("买家秀TID不属于当前板块");
				
			}
			$ret = $this->model->query("UPDATE $this->zhiyootabale SET `class` = $classid,`name`='$names',`buyershowtid`='$buyershowtid' $setimg WHERE `b_fid` =$fids");
			if ($ret===false) {
				$this->success("编辑失败！");
			}
			$datanames = ($dataone['name']) ? $dataone['name'] : '空';
			$strnames = ($names!='' and $names != $dataone['name'])?' 显示名称由'.$datanames.'改为'.$names : '';
			$strnames .= $buyershowtid!=$dataone['buyershowtid']?', 买家秀由'.$dataone['buyershowtid'].'改为'.$buyershowtid:'';
			if ($dataone['b_type']=='forum') {
				$str = '版块';
				$classstr = '';
			}elseif($dataone['b_type']=='sub'){
				$str = '子版块';
				$classstr = '';
			}else{
				$str = '专区';
				$classstr = ' 分类为'.$classnamearr[$classid];
			}
			if($img_path!=''){
				$classstr .= ' LOGO：'.$img_path;
			}
			$this -> writelog('论坛版块—编辑'.$str.'ID为'.$fids.'的内容  '.$classstr.$strnames,$this->zhiyootabale,$fids,__ACTION__ ,"","edit");
			$this->success("编辑成功！");
		}
		if ($_POST and $act=='prioitu_edit') {
			if (isset($_POST['level'])){
				$vstr = '';
				foreach ($_POST['level'] as $k=>$v){
					$v = abs(intval($v));
					if ($v=='') {
						$vstr = "优先级为空或0的不做处理。";
						continue;
					}
					$p_ret = $this->model -> query("UPDATE $this->zhiyootabale SET b_level='$v' where id='$k'");
				}
				$jsonarr = '论坛版块—编辑优先级id:level'.json_encode($_REQUEST['level']);
				$this -> writelog($jsonarr,$this->zhiyootabale,$k,__ACTION__ ,"","edit");
				$this->success("编辑优先级成功！".$vstr);
			}
		}
		
		if ($_POST and $act=='prioitu_edit_class') {
			if (isset($_POST['level'])){
				$vstr = '';
				foreach ($_POST['level'] as $k=>$v){
					$v = abs(intval($v));
					if ($v=='') {
						$vstr = "优先级为空或0的不做处理。";
						continue;
					}
					$p_ret = $this->model -> query("UPDATE $this->zhiyootabale SET b_class='$v' where id='$k'");
				}
				$jsonarr = '论坛版块—编辑专区优先级id:b_class'.json_encode($_REQUEST['level']);
				$this -> writelog($jsonarr,$this->zhiyootabale,$k,__ACTION__ ,"","edit");
				$this->success("编辑优先级成功！".$vstr);
			}
		}
		if ($_POST and $act=='area_edit') {
			if (isset($_POST['level'])){
				$vstr = '';
				foreach ($_POST['level'] as $k=>$v){
					$v = abs(intval($v));
					if ($v=='') {
						$vstr = "优先级为空或0的不做处理。";
						continue;
					}
					$p_ret = $this->model -> query("UPDATE $this->zhiyootabale SET b_plat='$v' where id='$k'");
				}
				$jsonarr = '论坛版块—编辑专区优先级id:b_plat'.json_encode($_REQUEST['level']);
				$this -> writelog($jsonarr,$this->zhiyootabale,$k,__ACTION__ ,"","edit");
				$this->success("编辑优先级成功！".$vstr);
			}
		}
		if ($_POST and $act=='all_area_edit') {
			if (isset($_POST['level'])){
				$vstr = '';
				foreach ($_POST['level'] as $k=>$v){
					$v = abs(intval($v));
					if ($v=='') {
						$vstr = "优先级为空或0的不做处理。";
						continue;
					}
					$p_ret = $this->model -> query("UPDATE $this->zhiyootabale SET b_order='$v' where id='$k'");
				}
				$jsonarr = '论坛版块—编辑专区优先级id:b_order'.json_encode($_REQUEST['level']);
				$this -> writelog($jsonarr,$this->zhiyootabale,$k,__ACTION__ ,"","edit");
				$this->success("编辑优先级成功！".$vstr);
			}
		}
	}
	public function area_list(){
		$fid = intval($_REQUEST['fid']);
		$issub = intval($_REQUEST['issub']);
		$fupid = $this->model->table($this->zhiyootabale)->field('b_fup')->where('b_fid='.$fid)->find();
		if ($fid<1) {
			$this -> writelog('论坛版块—查看b_fid为'.$fid.'专区失败','b_fup',$fid,__ACTION__ ,"","edit");
			$this->success("参数有误");
		}
		$order = 'ASC';$orderBy = 'status DESC';
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$orderBy = $orderBy." , modifytime $order";
		
		$fids = "fid,fup,type,name,status";
		$fidstr = "b_fid, b_fup, b_type, b_name, b_status, createdate";
		/*
		$maxplat = $this->model->table($this->zhiyootabale)->where("b_fup=$fid")->max('b_fid');
		if ($maxplat==null) {
			$maxplat = 0;
		}
		
		$sqlvalue = '';
		$bbsnews = $this->bbsdiscuzdb->table($this->bbstabal)->where("fup=$fid and fid>$maxplat")->field($fids)->order('fid asc')->limit(50)->select();
		
		if($bbsnews!==null){
			foreach ($bbsnews as $k=>$v){
				$createdate = date('Y-m-d H:i:s');
				$sqlvalue .= "('{$v['fid']}','{$v['fup']}','{$v['type']}','{$v['name']}','{$v['status']}','{$createdate}'),";
			}
			$sqlvalue = substr($sqlvalue, 0,-1);
 			$result = $this->model->query("INSERT INTO $this->zhiyootabale ($fidstr) VALUES ".$sqlvalue);
		}*/
		
		$where = "b_fup=$fid and status!='-1'";
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->model -> table($this->zhiyootabale) -> where($where)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 30;
		$Page = new Page($count,$prepage , $param);
		$z_fids = "id,b_fid, b_fup, b_type, b_name, b_status,b_plat,name,area_num,icon,status,createdate,buyershowtid,allowpost";
		$data = $this->model->table($this->zhiyootabale)->where($where)->field($z_fids)->order($orderBy)->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($data as $k=>$v){
			$b_fid = $v['b_fid'];
			$platcount = $this->bbsdiscuzdb->table($this->bbstabal)->field('threads,posts')->where("fid='{$b_fid}'")->find();
			$numcount = 0;
			if ($platcount!==false) {
				$numcount = intval($platcount['threads'])+intval($platcount['posts']);
				if ($v['area_num']<$numcount) {
					$ret = $this->model->query("UPDATE $this->zhiyootabale SET `area_num` = '$numcount' WHERE `b_fid` =$b_fid");
				}else {
					$numcount = $v['area_num'];
				}
			}
			if ($v['b_plat']==999999) {
				$data[$k]['b_plat'] = '';
			}
			$data[$k]['area'] = $numcount;
			if(!$issub){
				$data[$k]['sub_count'] = $this->model->table($this->zhiyootabale)->field('b_fid')->where("b_fup='{$b_fid}' and b_type='sub'")->count();
			}
		}
		$filterurl = '';
		$pageList = $Page->show();
		if(isset($_GET['lr']) and $_GET['lr']){
			$lr = $_GET['lr'];
			$filterurl .= '/lr/'.$lr;
		}else{
			$lr = 30;
		}
		if(isset($_GET['p']) and $_GET['p']){
			$p = $_GET['p'];
			$filterurl .= '/p/'.$p;
		}else{
			$p = 1;
		}
		$fidname = $this->model->table($this->zhiyootabale)->field('b_name,name')->where("b_fid='{$fid}'")->find();
		$this->assign('fidname',$fidname);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this->assign('fid',$fid);
		$this->assign('pageList',$pageList);
		($order=='ASC')?$order = 'DESC':$order='ASC';
		$this->assign('order',$order);
		$this -> assign('prefecturelist',$data);
		$this -> assign('issub',$issub);
		$this -> assign('fupid',$fupid['b_fup']);
		$this -> display();
	}
	
	public function all_area_list(){
		$order = 'ASC';$orderBy = 'id ASC';
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$orderBy = $orderBy." , modifytime $order";
		/*
		$maxplat = $this->model->table($this->zhiyootabale)->where("b_fup!=0")->max('b_fid');
		
		if ($maxplat==null) {
			$maxplat = 0;
		}
		$fids = "fid,fup,type,name,status";
		$fidstr = "b_fid, b_fup, b_type, b_name, b_status, createdate";
		$sqlvalue = '';
		$bbsnews = $this->bbsdiscuzdb->table($this->bbstabal)->where("type='forum' and fup!=0 and fid>$maxplat")->field($fids)->order('fid asc')->limit(100)->select();
		
		if($bbsnews!==null){
			foreach ($bbsnews as $k=>$v){
				$createdate = date('Y-m-d H:i:s');
				$sqlvalue .= "('{$v['fid']}','{$v['fup']}','{$v['type']}','{$v['name']}','{$v['status']}','{$createdate}'),";
			}
			$sqlvalue = substr($sqlvalue, 0,-1);
			$result = $this->model->query("INSERT INTO $this->zhiyootabale ($fidstr) VALUES ".$sqlvalue);
		}*/
	
		$where = "b_fup!=0 and status!='-1'";
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->model -> table($this->zhiyootabale) -> where($where)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		$z_fids = "id,b_fid, b_fup, b_type, b_name, b_status,b_order,b_plat,name,icon,status,createdate";
		$data = $this->model->table($this->zhiyootabale)->where($where)->field($z_fids)->order($orderBy)->limit($Page->firstRow . ',' . $Page->listRows)->select();
	
		foreach ($data as $k=>$v){
			$b_fid = $v['b_fid'];
			$platcount = $this->bbsdiscuzdb->table($this->bbstabal)->field('threads,posts')->where("fid='{$b_fid}'")->find();
			$numcount = 0;
			if ($platcount!==false) {
				$numcount = intval($platcount['threads'])+intval($platcount['posts']);
			}
			$data[$k]['area'] = $numcount;
		}
		$filterurl = '';
		$pageList = $Page->show();
		if(isset($_GET['lr']) and $_GET['lr']){
			$lr = $_GET['lr'];
			$filterurl .= '/lr/'.$lr;
		}else{
			$lr = 20;
		}
		if(isset($_GET['p']) and $_GET['p']){
			$p = $_GET['p'];
			$filterurl .= '/p/'.$p;
		}else{
			$p = 1;
		}
	
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this->assign('pageList',$pageList);
		($order=='ASC')?$order = 'DESC':$order='ASC';
		$this->assign('order',$order);
		$this -> assign('prefecturelist',$data);
		$this -> display();
	}
	
	function all_area_essence(){
		if ($_POST and isset($_POST['action']) and $_POST['action']=='area_essence') {
			$img_path ='';

			if($_FILES['p_logo']['size']>0||$_FILES['p_logo']['size']>0){
				$imginfo = $this->_upload();
				if($imginfo['image'][0]['url']) $img_path = $imginfo['image'][0]['url'];
			}
			if($img_path!=''){
				$setimg = ",'$img_path'";
				$setimgs =  ",icon = '$img_path'";
				$icon = ',icon';
			}else{
				$setimg = $icon = $setimgs = '';
			}
				
			$names = addslashes(trim($_POST['names']));
			$createdate = date('Y-m-d H:i:s');
			if (isset($_POST['isstatus']) and $_POST['isstatus']=='edit_area_essence') {
				$id = intval($_POST['id']);
				$dataone = $this->model->table($this->zhiyooarea)->field('id,class,b_fid,b_plat,name,icon')->where("id=$id")->find();
				if (($names=='' or $names == $dataone['name']) and $img_path=='') {
					$this->success("没有更改任何项！");
					exit;
				}
				$str = "修改";
				$ret = $this->model->query("UPDATE $this->zhiyooarea SET `name`='$names' $setimgs WHERE `id` =$id");
				if($ret!==false and ($img_path!='' or $names!='')){
					$strnames = '';
					if($names!='' and $names!=$dataone['name']){
						$datanames = ($dataone['name']) ? $dataone['name'] : '空';
						$strnames .= '显示名称由'.$datanames.'更改为'.$names .' ';
					}
					if($img_path!=''){
						$strnames .= 'LOGO：'.$img_path;
					}
					$this -> writelog('论坛版块—修改ID为'.$id.'精华热帖'.$strnames,$this->zhiyooarea,$id,__ACTION__ ,"","edit");
				}
			}else {
				$id = '';
				$str = "添加";
				$ret = $this->model->query("INSERT INTO $this->zhiyooarea (class,name $icon ,createdate) VALUES (1,'$names ' $setimg,'$createdate')");
			}
			
			if ($ret===false) {
				$this -> writelog('论坛版块—'.$str.'精华热帖失败',$this->zhiyooarea,$ret,__ACTION__ ,"","add");
				$this->success("$str失败！");
			}
			
			$this->success("$str成功！");
		}
		
		$order = 'ASC';$orderBy = 'status DESC';
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$orderBy = $orderBy." , modifytime $order";
		
		$where = " class=1 AND status!='-1' ";
		$z_fids = 'id,class,b_fid,b_name,b_plat,name,icon,status';
		$data = $this->model->table($this->zhiyooarea)->where($where)->field($z_fids)->order($orderBy)->select();
		$this->assign('areadata',$data);
		($order=='ASC')?$order = 'DESC':$order='ASC';
		$this->assign('order',$order);
		$this -> display();
	}
	function del_area(){//删除
		if (isset($_REQUEST['action']) and isset($_REQUEST['id'])) {
			$act = addslashes(trim($_REQUEST['action']));
			$id = intval($_REQUEST['id']);
		}else {
			$act = $id = '';
		}
		
		if ($id<1 and $act !='del') {
			$this -> writelog('论坛版块—删除id为'.$id.'精华热帖或热门论坛');
			$this->success("参数有误，删除失败！");
		}
		$ret = $this->model->query("UPDATE $this->zhiyooarea SET `status` = '-1' WHERE `id` =$id");
		$this -> writelog('论坛版块—删除id为'.$id.'热门论坛',$this->zhiyooarea,$id,__ACTION__ ,"","del");
		$this->success("删除成功！");
	}
	
	function add_area_essence(){
		$act = $id = '';
		if (isset($_REQUEST['action'])){
			$act = addslashes(trim($_REQUEST['action']));
		}
		if (isset($_REQUEST['id'])) {
			$id = intval($_REQUEST['id']);
		}
		if ($id>0 and $act=='edit') {
			$dataone = $this->model->table($this->zhiyooarea)->field('id,class,b_plat,name,icon')->where("id=$id")->find();
			$this -> assign('editarea',$dataone);
			$this -> assign('isstatus','edit_area_essence');
			$this -> assign('id',$id);
		}
		if ( $act=='add') {
			$count = $this->model->table($this->zhiyooarea)->where("class=1 and status!='-1'")->count();
			if ($count>=5) {
				exit("<h2>目前精华热帖最多可以添加5个版块！<h2>");
			}
			$dataone = array('id'=>'','class'=>'','b_plat'=>'','name'=>'','icon'=>'');
			$this -> assign('editarea',$dataone);
			$this -> assign('isstatus','add_area_essence');
		}

		$this -> display();
	}
	function all_area_newhot(){
		if ($_POST and isset($_POST['action']) and $_POST['action']=='area_top') {
			$b_fid = intval($_POST['b_fid']);
			$names = addslashes(trim($_POST['names']));
			$fidcount = $this->model -> table($this->zhiyooarea) -> where("b_fid=$b_fid and status!=-1 and type=1")->count();
			$namecount = $this->model -> table($this->zhiyooarea) -> where("name='$names' and status!=-1 and type=1")->count();
			
			$b_name = $this->model->table($this->zhiyootabale)->where("b_fid=$b_fid")->getField("b_name");
			$img_path ='';

			if($_FILES['p_logo']['size']>0||$_FILES['p_logo']['size']>0){
				$imginfo = $this->_upload();
				if($imginfo['image'][0]['url']) $img_path = $imginfo['image'][0]['url'];
			}
			if($img_path!=''){
				$setimgs = ",icon='$img_path'";
				$setimg =  ",'$img_path'";
				$icon = ',icon';
			}else{
				$setimg = $icon = $setimgs = '';
			}

			
			$createdate = date('Y-m-d H:i:s');
			if (isset($_POST['isstatus']) and $_POST['isstatus']=='edit_area_top') {
				$id = intval($_POST['id']);
				$dataone = $this->model->table($this->zhiyooarea)->field('id,class,b_fid,b_plat,name,icon')->where("id=$id and type=1")->find();
				if (($names=='' or $names == $dataone['name']) and $b_fid==$dataone['b_fid'] and $img_path=='') {
					$this->success("没有更改任何项！");
					exit;
				}
				if ($b_fid!=$dataone['b_fid'] and $fidcount) {
					$this->success("版块名称重复，请重新选择版块名称！");
				}
				if ($names!='' and $names != $dataone['name'] and $namecount) {
					$this->success("显示名称重复，请重新填写显示名称！");
				}
				$str = "修改";
				$ret = $this->model->query("UPDATE $this->zhiyooarea SET `b_fid`='$b_fid',`b_name`='$b_name',`name`='$names' $setimgs WHERE `id` =$id and type=1");
				if($ret!==false){
					$strnames = '';
					if($names!='' and $names!=$dataone['name']){
						$datanames = ($dataone['name']) ? $dataone['name'] : '空';
						$strnames .= '显示名称由'.$datanames.'更改为'.$names .' ';
					}
					if($img_path!=''){
						$strnames .= 'LOGO：'.$img_path;
					}
					if($strnames!=''){
						$this -> writelog('论坛版块—修改新热板块'.$strnames,$this->zhiyooarea,$id,__ACTION__ ,"","edit");
					}
				}
			}else{
				$id='';
				$str = "添加";
				if ($fidcount) {
					$this->success("版块名称重复，请重新选择版块名称！");
				}
				if ($names!='' and $namecount) {
					$this->success("显示名称重复，请重新填写显示名称！");
				}
				$ret = $this->model->query("INSERT INTO $this->zhiyooarea (class,b_fid,b_name,name $icon ,createdate, type) VALUES (2,'$b_fid','$b_name','$names' $setimg,'$createdate',1)");
				if($ret!==false and $names!=''){
					$this -> writelog('论坛版块—添加新热板块name'.$names,$this->zhiyooarea,$ret,__ACTION__ ,"","add");
				}
			}
			if ($ret===false) {
				$this -> writelog('论坛版块—'.$str.'新热板块失败',$this->zhiyooarea,$ret,__ACTION__ ,"","edit");
				$this->success("$str失败！");
			}
			
			$this->success("$str成功！");
		}
		$order = 'ASC';$orderBy = 'status DESC';
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$orderBy = $orderBy." , modifytime $order";
		$where = "class=2 and status!='-1' and type=1 ";
		$z_fids = 'id,class,b_fid,b_name,b_plat,name,icon,status';
		$data = $this->model->table($this->zhiyooarea)->where($where)->field($z_fids)->order($orderBy)->select();
		$orderbyarr = array();
		foreach ($data as $k=>$v){
			$b_fid = $v['b_fid'];
			$platcount = $this->bbsdiscuzdb->table($this->bbstabal)->field('threads,posts')->where("fid='{$b_fid}'")->find();
			$numcount = 0;
			if ($platcount!==false) {
				$numcount = intval($platcount['threads'])+intval($platcount['posts']);
			}
			$data[$k]['area'] = $numcount;
			$orderbyarr[$k] = $numcount;
		}
		if (isset($_GET['ordernum']) and $_GET['ordernum']!='') {
			$orderArr = array();
			$i=0;
			($_GET['ordernum']=='DESC')?arsort($orderbyarr):asort($orderbyarr);
			foreach ($orderbyarr as $k=>$v){
				$orderArr[$i] = $data[$k];
				$i++;
			}
			$data = $orderArr;
			$order = $_GET['ordernum'];
		}
		$this->assign('areadata',$data);
		($order=='ASC')?$order = 'DESC':$order='ASC';
		$this->assign('order',$order);
		$this -> display();
	}
	function all_area_top(){
		if ($_POST and isset($_POST['action']) and $_POST['action']=='area_top') {
			$b_fid = intval($_POST['b_fid']);
			$names = addslashes(trim($_POST['names']));
			$fidcount = $this->model -> table($this->zhiyooarea) -> where("b_fid=$b_fid and status!=-1 and type=0 ")->count();
			$namecount = $this->model -> table($this->zhiyooarea) -> where("name='$names' and status!=-1 and type=0 ")->count();
			
			$b_nameaone = $this->model->table($this->zhiyootabale)->where("b_fid=$b_fid")->field("b_name")->find();
			$b_name = $b_nameaone['b_name'];
			$img_path ='';

			if($_FILES['p_logo']['size']>0||$_FILES['p_logo']['size']>0){
				$imginfo = $this->_upload();
				if($imginfo['image'][0]['url']) $img_path = $imginfo['image'][0]['url'];
			}
			if($img_path!=''){
				$setimgs = ",icon='$img_path'";
				$setimg =  ",'$img_path'";
				$icon = ',icon';
			}else{
				$setimg = $icon = $setimgs = '';
			}

			
			$createdate = date('Y-m-d H:i:s');
			if (isset($_POST['isstatus']) and $_POST['isstatus']=='edit_area_top') {
				$id = intval($_POST['id']);
				$dataone = $this->model->table($this->zhiyooarea)->field('id,class,b_fid,b_plat,name,icon')->where("id=$id")->find();
				if (($names=='' or $names == $dataone['name']) and $b_fid==$dataone['b_fid'] and $img_path=='') {
					$this->success("没有更改任何项！");
					exit;
				}
				if ($b_fid!=$dataone['b_fid'] and $fidcount) {
					$this->success("版块名称重复，请重新选择版块名称！");
				}
				if ($names!='' and $names != trim($dataone['name']) and $namecount) {
					$this->success("显示名称重复，请重新填写显示名称！");
				}
				$str = "修改";
				$ret = $this->model->query("UPDATE $this->zhiyooarea SET `b_fid`='$b_fid',`b_name`='$b_name',`name`='$names' $setimgs WHERE `id` =$id and type=0 ");
				if($ret!==false){
					$strnames = '';
					if($names!='' and $names!=$dataone['name']){
						$datanames = ($dataone['name']) ? $dataone['name'] : '空';
						$strnames .= '显示名称由'.$datanames.'更改为'.$names .' ';
					}
					if($img_path!=''){
						$strnames .= 'LOGO：'.$img_path;
					}
					if($strnames!=''){
						$this -> writelog('论坛版块—修改热门论坛'.$strnames,$this->zhiyooarea,$id,__ACTION__ ,"","edit");
					}
				}
			}else{
				$id='';
				$str = "添加";
				if ($fidcount) {
					$this->success("版块名称重复，请重新选择版块名称！");
				}
				if ($names!='' and $namecount) {
					$this->success("显示名称重复，请重新填写显示名称！");
				}
				$ret = $this->model->execute("INSERT INTO $this->zhiyooarea (class,b_fid,b_name,name $icon ,createdate,type) VALUES (2,'$b_fid','$b_name','$names' $setimg,'$createdate',0)");
				$id = $this->model->getLastInsID();
				if($ret!==false and $names!=''){
					$this -> writelog('论坛版块—添加热门论坛name'.$names,$this->zhiyooarea,$id,__ACTION__ ,"","add");
				}
			}
			if ($ret===false) {
				$this -> writelog('论坛版块—'.$str.'热门论坛失败',$this->zhiyooarea,'',__ACTION__ ,"","add");
				$this->success("$str失败！");
			}
			
			$this->success("$str成功！");
		}
		$order = 'ASC';$orderBy = 'status DESC';
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$orderBy = $orderBy." , modifytime $order";
		$where = "class=2 and status!='-1' and type=0 ";
		$z_fids = 'id,class,b_fid,b_name,b_plat,name,icon,status';
		$data = $this->model->table($this->zhiyooarea)->where($where)->field($z_fids)->order($orderBy)->select();
		$orderbyarr = array();
		foreach ($data as $k=>$v){
			$b_fid = $v['b_fid'];
			$platcount = $this->bbsdiscuzdb->table($this->bbstabal)->field('threads,posts')->where("fid='{$b_fid}'")->find();
			$numcount = 0;
			if ($platcount!==false) {
				$numcount = intval($platcount['threads'])+intval($platcount['posts']);
			}
			$data[$k]['area'] = $numcount;
			$orderbyarr[$k] = $numcount;
		}
		if (isset($_GET['ordernum']) and $_GET['ordernum']!='') {
			$orderArr = array();
			$i=0;
			($_GET['ordernum']=='DESC')?arsort($orderbyarr):asort($orderbyarr);
			foreach ($orderbyarr as $k=>$v){
				$orderArr[$i] = $data[$k];
				$i++;
			}
			$data = $orderArr;
			$order = $_GET['ordernum'];
		}
		$this->assign('areadata',$data);
		($order=='ASC')?$order = 'DESC':$order='ASC';
		$this->assign('order',$order);
		$this -> display();
	}
	function add_area_top(){
		$act = $id = '';
		if (isset($_REQUEST['action'])){
			$act = addslashes(trim($_REQUEST['action']));
		}
		if (isset($_REQUEST['id'])) {
			$id = intval($_REQUEST['id']);
		}
		if ($id>0 and $act=='edit') {
			$dataone = $this->model->table($this->zhiyooarea)->field('id,class,b_fid,b_plat,name,icon')->where("id=$id and type=0")->find();
			$this -> assign('editarea',$dataone);
			$this -> assign('isstatus','edit_area_top');
			$this -> assign('id',$id);
		}
		if ( $act=='add') {
			$count = $this->model->table($this->zhiyooarea)->where("class=2 and status!='-1' and type=0")->count();
			if ($count>=9) {
				exit("<h2>目前热门论坛最多可以添加9个版块！<h2>");
			}
			$dataone = array('id'=>'','class'=>'','b_fid'=>'','b_plat'=>'','name'=>'','icon'=>'');
			$this -> assign('editarea',$dataone);
			$this -> assign('isstatus','add_area_top');
		}
		$dataplat = $this->model->table($this->zhiyootabale)->field('id,b_fid,b_name')->where("b_fup!=0 and b_status =1 ")->select();
		$this -> assign('dataplat',$dataplat);
		$this -> display();
	}
	function add_area_newhot(){
		$act = $id = '';
		if (isset($_REQUEST['action'])){
			$act = addslashes(trim($_REQUEST['action']));
		}
		if (isset($_REQUEST['id'])) {
			$id = intval($_REQUEST['id']);
		}
		if ($id>0 and $act=='edit') {
			$dataone = $this->model->table($this->zhiyooarea)->field('id,class,b_fid,b_plat,name,icon')->where("id=$id and type=1")->find();
			$this -> assign('editarea',$dataone);
			$this -> assign('isstatus','edit_area_top');
			$this -> assign('id',$id);
		}
		if ( $act=='add') {
			$count = $this->model->table($this->zhiyooarea)->where("class=2 and status!='-1' and type=1")->count();
			if ($count>=10) {
				exit("<h2>目前新热版块最多可以添加10个版块！<h2>");
			}
			$dataone = array('id'=>'','class'=>'','b_fid'=>'','b_plat'=>'','name'=>'','icon'=>'');
			$this -> assign('editarea',$dataone);
			$this -> assign('isstatus','add_area_top');
		}
		$dataplat = $this->model->table($this->zhiyootabale)->field('id,b_fid,b_name')->where("b_fup!=0 and b_status =1 ")->select();
		$this -> assign('dataplat',$dataplat);
		$this -> display();
	}
	function edit_treat_area(){
		if (isset($_REQUEST['action'])) {
			$act = addslashes(trim($_REQUEST['action']));
			$id = intval($_REQUEST['id']);
		}else{
			$act = $id ='';
		}
		if ($_POST and $act=='area_edit_b_plat') {
			if (isset($_POST['level'])){
				$id_str = '';
				foreach ($_POST['level'] as $k=>$v){
					$v = abs(intval($v));
					$p_ret = $this->model -> query("UPDATE $this->zhiyooarea SET b_plat='$v' where id='$k'");
					$id_str .= $k.',';
				}
				$jsonarr = '论坛版块—精华热帖或热门论坛编辑专区优先级id:b_plat'.json_encode($_REQUEST['level']);
				$this -> writelog($jsonarr,$this->zhiyooarea,$id_str,__ACTION__ ,"","edit");
				$this->success("编辑优先级成功！");
			}
		}
		if ($id>0 and $act=='status'){
			$status = abs(intval($_REQUEST['status']));
			$ret = $this->model->query("UPDATE $this->zhiyooarea SET `status` = '$status' WHERE `id` =$id");
			if ($ret===false) {
				$this -> writelog('论坛版块—精华热帖或热门论坛状态编辑id为'.$id.'失败，状态status为'.$status,$this->zhiyooarea,$id,__ACTION__ ,"","edit");
				$this->success("状态编辑失败！");
			}
			$strstatus = $status ? '使用' : '停用';
			$this -> writelog('论坛版块—精华热帖或热门论坛编辑ID为'.$id.'状态为'.$strstatus,$this->zhiyooarea,$id,__ACTION__ ,"","edit");
			$this->success("状态编辑成功！");
		}
	}
	
	public function changestatus_post(){
		$id = intval($_GET['id']);
		$allowpost = intval($_GET['allowpost']);
		$res = $this->model->table($this->zhiyootabale)->where(array('id'=>$id))->save(array('allowpost'=>$allowpost));
		$status = $allowpost == 1?'允许':'禁止';
		if($res !== false){
			$this -> writelog("论坛版块—编辑ID为{$id}的发帖状态改变为{$status}",$this->zhiyooarea,$id,__ACTION__ ,"","edit");
			$this -> success("状态改变成功");
		}else{
			$this -> error("状态改变失败");
		}
	}

	protected function _upload(){
		$img_dir = UPLOAD_PATH ."/img/". date('Ym/d/', time());		
		$config['multi_config']['p_logo'] = array(
			'savepath' => $img_dir,
			'saveRule' => 'getmsec',
		);
		$maxsize = 1024*20;
		if($_FILES['p_logo']['size'] > $maxsize){
			$config['multi_config']['p_logo']['img_p_size'] = $maxsize ;
			$config['multi_config']['p_logo']['img_p_width'] = 70;
		}else{
			$config['multi_config']['p_logo']['enable_resize'] = false;
		}
		$list = $this->_uploadapk(0, $config);
		if(!$list){
			$this -> error("生成图片失败");
		}
		return $list;	
	}
	
	
	protected function _uploadold($savepath,$width='200',$height='200',$Prefix='s_',$unlik='0') {
		import ( "@.ORG.UploadFile" );
		$upload = new UploadFile ( );
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置上传文件类型
		$upload->allowExts = explode ( ',', 'jpg,gif,png,jpeg' );
		//设置附件上传目录
		$upload->savePath = $savepath;
		//设置需要生成缩略图，仅对图像文件有效
		$upload->thumb = true;
		// 设置引用图片类库包路径
		$upload->imageClassPath = '@.ORG.Image';
		//设置需要生成缩略图的文件后缀
		$upload->thumbPrefix = $Prefix; //生成缩略图
		//设置缩略图最大宽度
		$upload->thumbMaxWidth = $width;
		//设置缩略图最大高度
		$upload->thumbMaxHeight = $height;
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
		}
		foreach($uploadList as $val){
			if ($unlik==1) {
				unlink($val['savepath'].$Prefix.$val['savename']);
			}
			$list[$val['post_name']]['name']=$val['savename'];
		}
		return $list;
	}
	

	function delimg(){
		$model = D('Zhiyoo.Zhiyoo');
        $fid = $_GET['fid'];
        $result = $this->model->table($this->zhiyootabale)->where(array('b_fid'=>$fid))->save(array('icon'=>''));
        if($result) {
            $this -> writelog("智友内容管理-论坛版块-专区列表 已删除b_fid为{$fid}的图片",$this->zhiyooarea,$fid,__ACTION__ ,"","del");
            echo json_encode(array('su'=>1));
        }else{
            echo json_encode(array('su'=>0));
        }
    }
}