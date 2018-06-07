<?php
/**
 * 安智网产品管理平台 市场综合管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:李朋 2011/5/19
 * ----------------------------------------------------------------------------
 */
class MarketAction extends CommonAction{
     public $safe_status = array('认证中', '已认证', '警告', '危险', '未认证');  //认证状态

     //市场综合管理_ajax修改认证状态
     function checkAjax(){
         $softid = $_POST['softid'];
         $safe = $_POST['safe'];
		 
         $SDB = D("soft");
         $SFDB = D("soft_file");
         $swhere['softid'] = $softid;
         $data['safe'] = $safe;
		 $log_result = $this->logcheck(array('softid'=>$softid),'sj_soft',$data,$SDB);
         $affect1 = $SDB -> where($swhere) -> save($data);
         $affect2 = $SFDB -> where($swhere) -> save($data);
         $package = $SDB -> where($swhere) -> select();
			
         if($affect1 && $affect2){
         	 $this->writelog('市场综合管理-认证软件列表将softid为'.$swhere['softid'].'包名为'.$package[0]['package'].','.$log_result,'sj_soft',$softid,__ACTION__ ,"","edit");
             $this -> ajaxReturn($softid, $this -> safe_status[$safe]."成功" , 1);
             }else{
             $this -> ajaxReturn('',$this -> safe_status[$safe]."失败", 0);
             }
         }

     //市场综合管理_ajax修改机型显示状态
     function checkDeviceAjax(){
         $device = D("Sj.Device");
         $did = $_POST['did'];
         $status = $_POST['show_soft_rule'];
         $where['did'] = $did;
         $data['show_soft_rule'] = $status;
         $title = intval($status) == 1 ? "显示" : "不显示";
		 $log_result = $this->logcheck(array('did'=>$did),'pu_device',$data,$device);
         $affect = $device -> where($where) -> save($data);

         if($affect){
         	$this->writelog('市场综合管理修改机型显示状态将did为'.$did.'设置为'.$title,'pu_device',$did,__ACTION__ ,"","edit");
             $this -> ajaxReturn($affect, '已经修改成' . $title , 1);
             }else{
             $this -> ajaxReturn('', "failed", 0);
             }
         }

     //市场综合管理_ajax修改修改厂商显示状态
     function checkFactoryAjax() {
      $factory = D("Sj.Manufacturer");
      $mid = $_POST['mid'];
      $status = $_POST['status'];
      $where['mid'] = $mid;
      $data['status'] = $status;
      $title = $status ? "显示" : "不显示";
      $affect = $factory -> where($where) -> save($data);
         if($affect){
         	 $this->writelog('市场综合管理修改厂商显示状态将mid为'.$did.'设置为'.$title,'pu_manufacturer',$mid,__ACTION__ ,"","edit");
             $this -> ajaxReturn($affect, '已经修改成' . $title , 1);
             }else{
             $this -> ajaxReturn('', "failed", 0);
             }
     }
     //市场综合管理_认证软件列表
     function approve(){                 //
         $SDB = D("soft");
         // 认证中 0 已认证 1  警告 2 危险 3 未认证 4
        $safe = array(
            array('val' => 1, 'comment' => '认证'),
             array('val' => 2, 'comment' => '警告'),
             array('val' => 3, 'comment' => '危险'),
             array('val' => 4, 'comment' => '未认证')
            );
        import("@.ORG.Page");
        $searchkey = trim($_REQUEST['searchkey']);
        if($searchkey){
		 $zh_where['softname']=array('like',"%".$searchkey."%");
		 $zh_where['package']=array('like',"%".$searchkey."%");
		 $zh_where['_logic']='or';
         $resultlist = $SDB -> where($zh_where) -> select();
         $softcount = count($resultlist);
         $page = new Page($softcount, 20);
         $softlist = $SDB ->where($zh_where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('softid desc') -> select();
        }else{
         $softcount = $SDB -> count();
         $page = new Page($softcount, 20);
         $softlist = $SDB -> limit($page -> firstRow . ',' . $page -> listRows) -> order('softid desc') -> select();
        }

         $page -> setConfig('header', '篇记录');
         $page -> setConfig('prev', '上一页');
         $page -> setConfig('next', '下一页');
         $page -> setConfig('first', '首页');
         $page -> setConfig('last', '末页');
         $p = $page -> show();
         if($searchkey) $this -> assign('searchkey',$searchkey);
         $this -> assign('list', $softlist);
         $this -> assign('start', $page -> firstRow);
         $this -> assign('page', $p);
         $this -> assign('safe', $safe);
         $this -> display('approve');
         }

     //市场综合管理_软件认证列表_批量修改认证状态
     function modifysafe(){
         $start = $_POST['start'];
         $status = $_POST['status'];
         $softid = $_POST['softid'];
         if(trim($_REQUEST['searchkey'])){
           $searchkey = trim($_REQUEST['searchkey']);
           $search = "searchkey/".$searchkey."/";
         }
         $SDB = D("soft");
         $SFDB = D("soft_file");
         $in = "('" . implode("','", $softid) . "')";
         $affect = $SDB -> where("softid in " . $in) -> save(array('safe' => $status));
         $package = $SDB -> where("softid in " . $in) -> select();
		 $packagestr = array();
		 foreach ($package as $v){
			$packagestr[] = $v['package'];
		 }
         $affect1 = $SFDB -> where("softid in " . $in) -> save(array('safe' => $status));
         if(!$affect && !$affect1){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/approve');
             $this -> error("你修改的状态就是软件的当前状态,修改失败！");
             }else{
             $p = $start / 20 +1;
             $this->writelog('市场综合管理-认证软件列表批量修改认证状态softid为'.$in.'软件包名为'.implode(',',$packagestr).'设置为'.$status,'sj_soft_file,sj_soft',$in,__ACTION__ ,"","edit");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/approve/'.$search.'p/' . $p . '/?&p=' . $p);
             $this -> success("已经修改成功");
             }
         }
     //市场综合管理_机型管理_机型管理列表
    function machine_zh(){
         $Form = D("Sj.Device");   //机型表
         $factory_mobile = D("Sj.Manufacturer"); //厂商表
         $device_alias = D("Sj.Devicealias"); //机型别名表
         $device_user_DB = D("device_user"); //手机用户表
         import("@.ORG.Page");
         $searchkey  = $_REQUEST['searchkey'];
         $where = '';
		 $dids = array();
        if(trim($searchkey)){
           $where = " where dname like '%".escape_string($_REQUEST['searchkey'])."%' ";
           $device_list = $Form -> query("select did,dname,mid,status,show_soft_rule from __TABLE__".$where);
			//获取 device 的信息
			foreach($device_list as $info){
				$dids[] = $info['did'];
			}
			
        }

         if(count($dids) > 0)  $wherebydids = "('".implode("','",$dids)."')";
         $where = $wherebydids ? " where did in ".$wherebydids : '';
		 if(!isset($_REQUEST['count'])){
			 $device_user = $device_user_DB -> query("SELECT COUNT(*) as cnt FROM (SELECT did FROM __TABLE__ GROUP BY did) AS c ".$where);
			 $count = $device_user[0]['cnt'];
		 }else{
			 $count = $_REQUEST['count'];		   
		 }
         $p = new Page($count, 10);
         $device_user_desc = $device_user_DB -> query("select count(did) ducount,did from  __TABLE__ ".$where." group by did order by ducount desc limit " . $p -> firstRow . ',' . $p -> listRows); //分页显示 user
         $factory_list = $factory_mobile -> select();
         $devicealiases = $device_alias -> select();
		 $devaliases_str = '';
         foreach($devicealiases as $info){
             $devaliases[$info['did']] = $info;
			 $devaliases_str .= $info['alias'].",";
             }
         foreach($factory_list as $info){
             $faclist[$info['mid']] = $info; //获取厂商信息
             }
		//获取 device 的信息
		 $did_str = '';
		 foreach($device_user_desc as $id => $info){
		     $did_str .= $info['did'].',';
		 }
		 if(!empty($devaliases_str)) $did_str .= $devaliases_str;
		 if(!empty($did_str)) $did_where = " where did in (".substr($did_str,0,-1).")";
         $device_list_all = $Form -> query("select did,dname,mid,status,show_soft_rule from __TABLE__ ".$did_where);
         foreach($device_list_all as $id => $value){
             $delist[$value['did']] = $value; //整理device信息
         }	 
         // 信息综合
        foreach($device_user_desc as $info){
             $mid = $delist[$info['did']]['mid'];
             $alias = $devaliases[$info['did']] ? $delist[$devaliases[$info['did']]['alias']]['dname'] : 0;
             $list[] = array(
                'did' => $info['did'],
                 'dname' => $delist[$info['did']]['dname'] ? $delist[$info['did']]['dname'] : '未知机型',
                 'mname' => $faclist[$mid]['mname'],
                 'mid' => $mid,
                 'sum_stat' => $info['ducount'],
                 'show_soft_rule' => $delist[$info['did']]['show_soft_rule'],
                 'dev_adapter' => $alias,
                );
             }
         $p -> setConfig('header', '篇记录');
         $p -> setConfig('prev', '上一页');
         $p -> setConfig('next', '下一页');
         $p -> setConfig('first', '首页');
         $p -> setConfig('last', '末页');
         $page = $p -> show();
         if($searchkey) $this -> assign("searchkey",$searchkey);
         $this -> assign("page", $page);
         $this -> assign("list", $list);
		 $this -> assign("count",$count);
         $this -> display('machine_zh');
         }
	     //by张辉机型管理—添加机型显示开始
    function machine(){
    	 //ini_set("memory_limit", "1024M");
         $Form = D("Sj.Device");   //机型表
         $factory_mobile = D("Sj.Manufacturer"); //厂商表
         $device_alias = D("Sj.Devicealias"); //机型别名表
         //$device_user_DB = D("device_user"); //手机用户表
         import("@.ORG.Page");
         $searchkey  = $_REQUEST['searchkey'];
         $where = ' where status=1 ';
		 $dids = array();
        if(trim($searchkey)){
            $where .= " and  dname like '%".escape_string($_REQUEST['searchkey'])."%' ";
        }else{
            $where=" where status=1";
        }
        $res = $Form -> query("select count(*) as total from __TABLE__".$where);
		 $count=$res[0]['total'];
         $p = new Page($count, 10);
         $device_list = $Form -> query("select * from  __TABLE__ ".$where." order by did desc limit " . $p -> firstRow . ',' . $p -> listRows); //分页显示 device
        $dids = array();
        foreach($device_list as $info){ 
            $dids[]=$info['did'];
        }
		 
		 $factory_list = $factory_mobile ->field("mid,mname") ->select();
		 foreach($factory_list as $k=>$info){
			$value[$info['mid']]['mname']=$info['mname'];
		 }
		 $devicealiases = $device_alias -> select();
		 foreach($devicealiases as $k=>$de_info){
			$zh_device[$de_info['did']]['alias']=$de_info['alias'];
		 }
		 $factory_list_alias = $Form->where(array("status"=>1, 'did' => array('in', $dids))) ->field("did,dname")->select();
		 foreach($factory_list_alias as $k=>$fa_info){
			
			$zh_factory[$fa_info['did']]['dname']=$fa_info['dname'];
		 }
        foreach($device_list as $info){ 
             $list[] = array(
                'did' => $info['did'],
                 'dname' => $info['dname'] ? $info['dname'] : '未知机型',
                 'mname' => $value[$info['mid']]['mname'],
                 'mid' => $info['mid'],
                 'show_soft_rule' =>$info['show_soft_rule'],
                 'dev_adapter' => $zh_factory[$zh_device[$info['did']]['alias']]['dname'],
                );
             }
         $p -> setConfig('header', '篇记录');
         $p -> setConfig('prev', '上一页');
         $p -> setConfig('next', '下一页');
         $p -> setConfig('first', '首页');
         $p -> setConfig('last', '末页');
         $page = $p -> show();
         if($searchkey) $this -> assign("searchkey",$searchkey);
         $this -> assign("page", $page);
         $this -> assign("list", $list);
		 $this -> assign("count",$count);
         $this -> display('machine');
         }
	 //by张辉机型管理—添加机型显示结束
     //市场综合管理_机型管理_添加机型_执行
     function addDevice(){
         $device_alias = D("Sj.Devicealias");
         $device = D("Sj.Device");
         $data['dname'] = $_POST['dname'];
         $data['status'] = $_POST['status'];
         $data['mid'] = $_POST['mid'];
         $data['note'] = $_POST['note'];
         $devicealias['alias'] = $_POST['alias'];
         $data['device_type'] = 1;

         $result = $device -> where(array("dname" => $data['dname'])) -> select();
         if(count($result) > 0){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/addDeviceform');
             $this -> error("该机型已存在");
             }

         $addid = $device -> add($data);
         if($devicealias['alias'] != 'select'){
             $affect = $device_alias -> add(array('did' => $addid, 'alias' => $devicealias['alias']));
             }
         if($addid || $affect){
         	 $this->writelog('市场综合管理添加机型did为'.$addid,'pu_device_alias',$addid,__ACTION__ ,"","add");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/addDeviceform');
             $this -> success("机型添加成功");
             }
         }
	
     //市场综合管理_机型管理_添加机型_显示
     function addDeviceform(){
         $Form = D("Sj.Device");
         $factory_mobile = D("Sj.Manufacturer");
         $factorylist = $factory_mobile -> select();
         foreach($factorylist as $info){
             $faclist[$info['mid']] = $info;
             }
         $devicelist = $Form -> select();
         foreach($devicelist as $info){
             $devlist[$info['did']] = $info;
             }
         $this -> assign('devlist', $devlist);
         $this -> assign('faclist', $faclist);
         $this -> display();
         }
    /**
     * 显示字段：
	 * 软件名称 softname
     * 软件版本 version
     * 软件版本号  version_code
     * ICON缩略图  iconurl
	 * 软件包名 package
     * 软件类型 category_id
     * 软件适配情况
     * 管理员进入后，显示相关机型所有已上架软件，显示其相关信息以及状态。
     * 每页15条记录，可翻页
     * 每条记录前，有一个复选框，分页底部 有 批量 已通过 1，批量 未通过 2，批量未检查 三个按钮，可对软件进行批量管理。
     */
     //市场综合管理_机型适配软件列表
     function adapterSoftList(){
         $soft = D("soft");
         $softfile = D("soft_file");
         $catagory = D("category");
         $device = D("Sj.Device");
         $device_adapter = D("device_adapter");
         import("@.ORG.Page");
         $did = $_REQUEST['did'];
         $searchkey  = $_REQUEST['searchkey'];
         $where = trim($searchkey) ? " and (softname like '%".$searchkey."%' or package like '%".$searchkey."%')" : "";
         $deviceinfo = $device -> where(array("did" => $did)) -> select();
         $deviceadapter = $device_adapter -> where(array("did" => $did)) -> select();
         foreach($deviceadapter as $info){
             $adapter_did_soft[$info['package']] = $info;
             }
         $count = $soft -> where("hide=1".$where) -> count();
         $sql = "select count(*) as sum from sj_device_adapter where package in (select package from sj_soft where hide=1 {$where}) and status = 1 and did='{$did}'";
         $r = $soft -> query($sql);
         $pass = $r[0]['sum'];

         $sql = "select count(*) as sum from sj_device_adapter where package in (select package from sj_soft where hide=1 {$where}) and status = 2 and did='{$did}'";
         $r = $soft -> query($sql);
         $unpass = $r[0]['sum'];
         $uncheck = $count - $pass - $unpass;
         $p = new Page($count, 15);
         $list = $soft -> where("hide=1".$where) -> limit($p -> firstRow . ',' . $p -> listRows) -> order('upload_tm desc') -> select();
         $category_list = $catagory -> select();
         foreach($category_list as $info){
             $catalist[$info['category_id']] = $info;
             }
         foreach($list as $id => $info){
             $softid = $info['softid'];
             $sf = $softfile -> where("softid = " . $softid) -> select();
             $result[$id] = array_merge(
                $info,
                 $sf[0],
                 array('category' => $catalist[$info['category_id']]['name'])
                );
             $adpterstatus = $adapter_did_soft[$info['package']] ? $adapter_did_soft[$info['package']] : array('status' => 0);
             $result[$id] = array_merge($result[$id], $adpterstatus);
             }
         $p -> setConfig('header', '篇记录');
         $p -> setConfig('prev', '上一页');
         $p -> setConfig('next', '下一页');
         $p -> setConfig('first', '首页');
         $p -> setConfig('last', '末页');
         $page = $p -> show ();
         if(trim($searchkey)) $this->assign('searchkey',trim($searchkey));
         $this -> assign("page", $page);
         $this -> assign ("list", $result);
         $this -> assign('p', $p -> firstRow);
         $this -> assign("device", $deviceinfo[0]);
         $this -> assign('did', $did);
         $this -> assign('uncheck', $uncheck);
         $this -> assign('pass', $pass);
         $this -> assign('unpass', $unpass);
         $this -> display('adapterSoftList');
         }

     //市场综合管理_机型适配软件
     function adaptersoft(){
         $device = M("device_adapter");
         $adapter = $_POST['adapter'];
         $did = $_POST['did'];
         $status = $_POST['_do'];
         $note  =  $_POST['note'];
         $p = $_REQUEST['currpage'] ? $_REQUEST['currpage'] / 15 + 1 : 0;
         $search = $_REQUEST['searchkey'] ? "/searchkey/".$_REQUEST['searchkey'] : "";
         if ((is_numeric($did)>0) && is_numeric($status)){
	         foreach($adapter as $ad){
	         	$result = $device -> where(array("did" => $did , "package" => $ad)) -> select();
	         	$data = array();
	         	if(count($result) > 0){
	         		if($result[0]['status'] == $status){
	         			continue;
	         		} elseif($status != 3) {
	         			$data['status'] = $status;
	         			$data['note'] = ($data['status'] == 2) ? $note : '';
	         			$data['last_refresh'] = time();
	         			$device -> where(array("did" => $did , "package" => $ad)) -> save($data);
	         		} else {
	         			$device -> where(array("did" => $did , "package" => $ad)) -> delete();
	         		}
	         	}else{
	         		if ($status == 3) continue;
	         		$data['did'] = $did;
	         		$data['package'] = $ad;
	         		$data['upload_time'] = time();
	         		$data['last_refresh'] = $data['upload_time'];
	         		$data['status'] = $status;
	         		$data['note'] = ($status == 2) ? $note : '';
	         		$device -> add($data);
	         	}
	         }
         } else {
         	$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/adapterSoftList/did/' . $did .$search. '/p/' . $p . "/?&p=" . $p);
         	$this -> error("未知错误!");         	
         }
        // 机型适配软件
         $this->writelog('市场综合管理添加机型did为'.$did."的匹配软件". implode(',', $adapter), 'sj_device_adapter',$did,__ACTION__ ,"","add");
         $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/adapterSoftList/did/' . $did .$search. '/p/' . $p . "/?&p=" . $p);
         $this -> success("软件适配成功");
         }
     //市场综合管理_厂商适配软件列表
     function MadapterSoftList(){
         $soft = D("soft");
         $softfile = D("soft_file");
         $catagory = D("category");
         $factory = D("Sj.Manufacturer");
         $factory_adapter = D("manufacturer_adapter");
         import("@.ORG.Page");
         $mid = $_REQUEST['mid'];
     	 if (!is_numeric($mid) || $mid<=0){
         	$this -> error("没有厂商!");
         	return ;
         }
         $searchkey  = $_REQUEST['searchkey'];
         $where = trim($searchkey) ? " and softname like '%".$searchkey."%' or package like '%".$searchkey."%'" : "";
         $factoryinfo = $factory -> where(array("mid" => $mid)) -> select();
         $factoryadapter = $factory_adapter -> where(array("mid" => $mid)) -> select();
         foreach($factoryadapter as $info){
             $adapter_did_soft[$info['package']] = $info;
             }
         $count = $soft -> where("hide=1".$where) -> count();
         $p = new Page($count, 15);
         $list = $soft -> where("hide=1".$where) -> limit($p -> firstRow . ',' . $p -> listRows) -> order('softid asc') -> select();
         $category_list = $catagory -> select();
         foreach($category_list as $info){
             $catalist[$info['category_id']] = $info;
             }
         foreach($list as $id => $info){
             $softid = $info['softid'];
             $sf = $softfile -> where("softid = " . $softid) -> select();

            if($info['category_id'][0]==',')
            {
                $info['category_id']=substr($info['category_id'],1);
            }

            $tnum=strlen($info['category_id']);
            $tnum--;
            if($info['category_id'][$tnum]==',')
            {
                $info['category_id']=substr($info['category_id'],0,-1);
            }

            $result[$id] = array_merge(
                $info,
                 $sf[0],
                 array('category' => $catalist[$info['category_id']]['name'])
                );
             $adpterstatus = $adapter_did_soft[$info['package']] ? $adapter_did_soft[$info['package']] : array('status' => 0);
             $result[$id] = array_merge($result[$id], $adpterstatus);
             }
         $p -> setConfig('header', '篇记录');
         $p -> setConfig('prev', '上一页');
         $p -> setConfig('next', '下一页');
         $p -> setConfig('first', '首页');
         $p -> setConfig('last', '末页');
         $page = $p -> show ();
         if(trim($searchkey)) $this->assign('searchkey',trim($searchkey));
         $this -> assign("page", $page);
         $this -> assign ("list", $result);
         $this -> assign('p', $p -> firstRow);
         $this -> assign("factory", $factoryinfo[0]);
         $this -> assign('mid', $mid);
         $this -> display('MadapterSoftList');
         }
     //市场综合管理_厂商适配软件
    function Madaptersoft(){
         $factory = M("manufacturer_adapter");
         $adapter = $_POST['adapter'];
         $mid = $_POST['mid'];
         $status = $_POST['_do'];
         $p = $_REQUEST['currpage'] ? $_REQUEST['currpage'] / 15 + 1 : 0;
         $search = $_REQUEST['searchkey'] ? "/searchkey/".$_REQUEST['searchkey'] : "";
         if (is_numeric($mid) && is_numeric($status)){
	         foreach($adapter as $ad){
	         	$result = $factory -> where(array("mid" => $mid , "package" => $ad)) -> select();
	         	$data = array();
	         	if(count($result) > 0){
	         		if($result[0]['status'] == $status){
	         			continue;
	         		} elseif($status != 3) {
	         			$data['status'] = $status;
	         			$data['last_refresh'] = time();
	         			$factory -> where(array("mid" => $mid, "package" => $ad)) -> save($data);
	         		} else {
	         			$factory -> where(array("mid" => $mid ,"package" => $ad)) -> delete();
	         		}
	         	} else {
	         		if ($status == 3) continue;
	         		$data['mid'] = $mid;
	         		$data['package'] = $ad;
	         		$data['upload_time'] = time();
	         		$data['last_refresh'] = $data['upload_time'];
	         		$data['status'] = $status;
	         		$factory -> add($data);
	         	}
	         }
         } else {
         	$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/MadapterSoftList/mid/' . $mid . $search.'/p/' . $p . "/?&p=" . $p);
         	$this -> error("未知错误!");
         }
         $this->writelog('市场综合管理厂商适配软件mid为'.$mid."适配软件包名为". implode(',', $adapter)."\t".'状态status字段修改为'.$status,'sj_manufacturer_adapter',$mid,__ACTION__ ,"","edit");
         $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/MadapterSoftList/mid/' . $mid . $search.'/p/' . $p . "/?&p=" . $p);
         $this -> success("软件适配成功");
        }
      //市场综合管理_厂商适配管理_厂商列表
     function manufacturers() {
         import("@.ORG.Page");
         $factory = D("Sj.Manufacturer");
         $factory_list = $factory -> select();
         $count = count($factory_list);
         $p = new Page($count, 10);
         $factory_list = $factory -> limit($p -> firstRow . ',' . $p -> listRows) -> order("mid desc")-> select();
         $p -> setConfig('header', '篇记录');
         $p -> setConfig('prev', '上一页');
         $p -> setConfig('next', '下一页');
         $p -> setConfig('first', '首页');
         $p -> setConfig('last', '末页');
         $page = $p -> show ();
         $this -> assign("page", $page);
         $this -> assign('list',$factory_list);
         $this -> display();
     }
     //市场综合管理_厂商适配管理_添加厂商_显示
     function addFactoryform() {
         $this -> display();
     }
     //市场综合管理_厂商适配管理_添加厂商_执行
     function addFactory() {
         $data['mname'] = $_POST['mname'];
         $data['note']  = $_POST['note'];
         $data['status'] = $_POST['status'];
         $factory = D("Sj.Manufacturer");
         $result = $factory -> where(array("mname" => $data['mname'])) -> select();
         if(count($result) > 0){
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/addFactoryform');
             $this -> error("该厂商已存在");
         }

         $affect = $factory -> add($data);
         if($affect){
         	 $this->writelog('市场综合管理添加厂商:'.$_POST['mname'],'sj_manufacturer_adapter',$affect,__ACTION__ ,"","add");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/addFactoryform');
             $this -> success("该厂商已添加");
         }
     }
     //市场综合管理_厂商适配管理_按厂商分组展示机型
     function groupFdevice() {
         $data['mid'] = $_GET['mid'];
         import("@.ORG.Page");
         $device = D("Sj.Device");
         $factory = D("Sj.Manufacturer");
         $factory_info = $factory -> where($data) -> select();
         $delist = $device -> where($data) -> select();
         $count = count($delist);
         $p = new Page($count, 10);
         $device_list = $device -> where($data) -> limit($p -> firstRow . ',' . $p -> listRows) -> order("mid desc")-> select();
         foreach($device_list as $info){
              $devices[] = array_merge($info,array('mname' => $factory_info[0]['mname']));
         }
         $p -> setConfig('header', '篇记录');
         $p -> setConfig('prev', '上一页');
         $p -> setConfig('next', '下一页');
         $p -> setConfig('first', '首页');
         $p -> setConfig('last', '末页');
         $page = $p -> show ();
         $this -> assign('list',$devices);
         $this -> assign("page", $page);
         $this -> display("groupFdevice");
     }
     //市场综合管理_厂商适配管理_修改厂商_显示
     function modifyFactoryForm() {
        $factory = D("Sj.Manufacturer");
        $mid     = $_GET['mid'];
        $factoryinfo = $factory -> where(array("mid" => $mid)) -> select();
        $this -> assign('info',$factoryinfo[0]);
        $this -> display();
     }
     //市场综合管理_厂商适配管理_修改厂商信息_执行
     function modifyFactory() {
        $factory = D("Sj.Manufacturer");
        $where['mid'] = $_POST['mid'];
        $data['mname'] = $_POST['mname'];
        $data['status'] = $_POST['status'];
        $data['note'] = $_POST['note'];

		$log = $this -> logcheck(array('mid' => $_POST['mid']),'pu_manufacturer',$data,$factory);

        $affect = $factory -> where($where) -> save($data);
         if($affect){
         	 $this->writelog('市场综合管理_厂商适配管理修改了ID为['.$_POST['mid'].']的厂商'.$log,'sj_manufacturer_adapter',$affect,__ACTION__ ,"","edit");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/manufacturers');
             $this -> success("该厂商已修改");
         }else{
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/addFactoryform');
             $this -> error("该厂商修改失败");
         }
     } 
     //厂商适配管理_删除厂商
     function deleteFactory() {
         $factory = D("Sj.Manufacturer");
         $factory_adapter = D("manufacturer_adapter");
         $mid= $_GET['mid'];
         $affect = $factory->where(array("mid" => $mid))-> delete();
         $affect1 = $factory_adapter->where(array("mid" =>$mid))-> delete();
         if($affect || $affect1){

             $this->writelog('市场综合管理_厂商适配管理删除了ID为['.$mid.']的厂商','sj_manufacturer_adapter',$mid,__ACTION__ ,"","del");
             $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Market/manufacturers');
             $this -> success("该厂商已删除");
         }
     }
	 //市场综合管理_推荐文案管理
	function recommanage() {
		date_default_timezone_set('Asia/Shanghai');
		$recommanage = M('recommanage');
		if($_GET['action'] == 'addRecom') {
			$ret=$recommanage->add(array('recom_cont'=>$_POST['recom_cont'],'recom_type'=>$_POST['recom_type'],'postdate'=>time()));
			$this->writelog('市场综合管理_推荐文案管理添加推荐信息:'.$_POST['recom_cont'],'sj_recommanage',$ret,__ACTION__ ,"","add");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Market/recommanage/recom_type/{$_POST['recom_type']}");
            $this -> success("恭喜您，推荐信息添加成功！");
		} else if($_GET['action'] == 'edit') {
			$data = array('recom_cont'=>$_POST['recom_cont'],'recom_type'=>$_POST['recom_type'],'postdate'=>time());
			$log = $this -> logcheck(array('id' =>$_GET['id']),'sj_recommanage',$data,$recommanage);
			$recommanage->where("id={$_GET['id']}")->save(array('recom_cont'=>$_POST['recom_cont'],'recom_type'=>$_POST['recom_type'],'postdate'=>time()));
			$this->writelog("市场综合管理_推荐文案管理编辑了ID为'".$_GET['id']."'的信息内容为:".$log,'sj_recommanage',$_GET['id'],__ACTION__ ,"","edit");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Market/recommanage/recom_type/{$_POST['recom_type']}");
            $this -> success("恭喜您，推荐信息编辑成功！");
		} else if($_GET['action'] == 'delete') {
			$recommanage->query("delete from sj_recommanage where id='{$_GET['id']}'");
			$this->writelog("市场综合管理_推荐文案管理删除了ID为[{$_GET['id']}]的文案信息",'sj_recommanage',$_GET['id'],__ACTION__ ,"","del");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Market/recommanage/recom_type/{$_GET['recom_type']}");
            $this -> success("恭喜您，推荐信息删除成功！");
		} else {
			$_arr = $arr = array();
			$_arr = $recommanage->order('id desc')->select();
			if($_arr) {
				foreach($_arr as $val) {
					$val['postdate'] = date('Y-m-d H:i:s',$val['postdate']);
					$arr[$val['recom_type']][] = $val;
				}
			}
			$this -> assign('recom1',$arr[1]);
			$this -> assign('recom2',$arr[2]);
			$this -> assign('recom3',$arr[3]);
			$this -> assign('recom4',$arr[4]);
			$this -> display();
		}
	}
	//市场综合管理_推荐文案管理_配置页面
	function recomconf() {
		$recomconf = M('recomconf');
		if($_GET['action'] == 'addVal') {
			$ret=$recomconf->add(array('name'=>$_POST['valName'],'val'=>$_POST['valVal'],'explain'=>$_POST['valExplain'],'rule'=>$_POST['valRule'],'postdate'=>time()));
			$this->writelog('市场综合管理_推荐文案管理_配置页面添加变量:'.$_POST['valName'],'sj_recomconf',$ret,__ACTION__ ,"","add");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Market/recomconf/");
            $this -> success("恭喜您，添加变量成功！");
		} else if($_GET['action'] == 'edit') {
            $log = $this -> logcheck(array('id' =>$_GET['id']),'sj_recomconf',$_POST,$recomconf);
			$recomconf->where("id='{$_GET['id']}'")->save(array('name'=>$_POST['valName'],'val'=>$_POST['valVal'],'explain'=>$_POST['valExplain'],'rule'=>$_POST['valRule'],'postdate'=>time()));
			$this->writelog("市场综合管理_推荐文案管理_配置页面编辑了ID为[{$_GET['id']}]的信息内容为：".$log,'sj_recomconf',$_GET['id'],__ACTION__ ,"","edit");
            $this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Market/recomconf/");
            $this -> success("恭喜您，编辑变量成功！");
		} else if($_GET['action'] == 'delete') {
			$recomconf->query("delete from sj_recomconf where id='{$_GET['id']}'");
			$this->writelog("市场综合管理_推荐文案管理_配置页面删除了ID为[{$_GET['id']}]的信息",'sj_recomconf',$_GET['id'],__ACTION__ ,"","del");
			$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Market/recomconf/");
            $this -> success("恭喜您，删除变量成功！");
		} else {
			$arr = $recomconf->order('id desc')->select();
			$this->assign('arr',$arr);
			$this->display();
		}
	}
	
	//2013-2-27 适配机型通过/不通过列表 庄超滨
	function passSoftList(){
         $soft = D("soft");
         $softfile = D("soft_file");
         $catagory = D("category");
         $device = D("Sj.Device");
         $device_adapter = D("device_adapter");
         import("@.ORG.Page");
         $did = $_REQUEST['did'];
         $deviceinfo = $device -> where(array("did" => $did)) -> select();	
         $count = $soft -> where("hide=1") -> count();//统计软件总数
         $sql = "select count(*) as sum from sj_device_adapter where package in (select package from sj_soft where hide=1 {$where}) and status = 1 and did='{$did}'";
         $r = $soft -> query($sql);
         $pass = $r[0]['sum'];//统计通过的数据
         $sql = "select count(*) as sum from sj_device_adapter where package in (select package from sj_soft where hide=1 {$where}) and status = 2 and did='{$did}'";
         $r = $soft -> query($sql);
         $unpass = $r[0]['sum'];//统计不通过的数据
         $uncheck = $count - $pass - $unpass;	//统计未检查数据	 
		if($_GET['stat'] == 1){
			$p = new Page($pass, 15);
		}else if($_GET['stat'] == 2){
			$p = new Page($unpass, 15);
		}else{
			$p = new Page($count, 15);
		}
		 if(!empty($_GET['stat'])){
			$deviceadapter = $device_adapter -> where(array("did" => $did,"status" => $_GET['stat'] )) -> limit($p -> firstRow . ',' . $p -> listRows) -> select();
		 }
		 $result = array();
		 foreach($deviceadapter as $k => $v){
			$list = $soft -> where(array("hide"=>"1","package"=>$v['package'])) -> select();
			$softids = $list[0]['softid'];
			$softid = substr("$softids",1,-1);
			$category_ids = $list[0]['category_id'];
			$category_id = substr("$category_ids",1,-1);
			//查询获取到软件图标
			$sf = $softfile -> where("softid = " . $softid) ->field('iconurl,softid')  -> select();
			//查询获取软件分类
			$category_list = $catagory -> where(array("category_id" => "$category_id")) -> field('category_id , name') -> select(); 
			//整合数据
 			$result[$k]['iconurl'] = $sf[0]['iconurl']; 	
			$result[$k]['softname'] = $list[0]['softname']; 	
			$result[$k]['status'] = $v['status']; 	
			$result[$k]['package'] = $list[0]['package']; 	
			$result[$k]['version'] = $list[0]['version']; 	
			$result[$k]['version_code'] = $list[0]['version_code']; 	
			$result[$k]['category_name'] = $category_list[0]['name']; 	 
		 }
         $p -> setConfig('header', '篇记录');
         $p -> setConfig('prev', '上一页');
         $p -> setConfig('next', '下一页');
         $p -> setConfig('first', '首页');
         $p -> setConfig('last', '末页');
         $page = $p -> show ();
		 $this -> assign('p', $p -> firstRow);
		 $this -> assign("page", $page);
         $this -> assign ("list", $result);
         $this -> assign("device", $deviceinfo[0]);
         $this -> assign('did', $did);
         $this -> assign('uncheck', $uncheck);
         $this -> assign('pass', $pass);
         $this -> assign('unpass', $unpass);
         $this -> display('passSoftList');
         }

}
?>
