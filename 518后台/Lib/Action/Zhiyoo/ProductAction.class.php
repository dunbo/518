<?php
/**
 * 众测产品相关管理
 * @author renhj
 * 2015-11-11 10:00
 */
class ProductAction extends CommonAction
{
	private $model;
	private $bbsmodel;
	private $productmodel;
	private $producmastertmodel;
	private $productpermodel;
	private $producttidmodel;
	private $zhiyoo_product_apply = "zy_product_apply";		//产品申请表
	private $zhiyoo_product_apply_count = "zy_product_apply_count";	//产品申请数量
	private $zy_credit_log = 'zy_product_sq_credit_log';	//用户资源扣除表
	private $zhiyoo_test_content = "zy_test_content";		//智友众测报告表
	private $zhiyoo_test_topnav = 'zy_test_topnav_conf';	//顶部导航栏目配置表
	private $zhiyoo_prodct_type = 'zy_product_type';		//产品分类表（获取gid）
	private $bbs_forum = 'x15_forum_forum';					//版块表
	private $bbstabal = 'x15_forum_thread';					//主题表
	private $bbs_threadclass = "x15_forum_threadclass";		//主题分类表
	private $bbs_option = 'x15_forum_typeoptionvar';		//分类信息项目数据表
	private $bbs_forum_typeoption = "x15_forum_typeoption";	//分类信息项目数据表
	private $bbs_member_count = "x15_common_member_count";	//用户统计表(金币、智豆)
	private $zy_notify_log = 'zy_apply_notify_log';	//发送通知表
	private $zyactivity = 'zy_product_act';	//产品活动表
	private $sharetext = 'zy_product_share_text';	//产品分享文案表
	private $slavemodel ;	//zhiyoo从库
	
	private $bbsfid = 1554;
	private $bbsgid = 1553;
	private $bbs_product_name = '机型介绍';
	private $typeoptionarr = array('p_count'=>'数量','p_condition'=>'申请条件','c_count'=>'申请条件数量',
			'p_price'=>'参考价格','is_p_url'=>'是否展示购买链接','p_url'=>'购买链接','p_spread_img'=>'推广头图',
			'p_img'=>'列表图片','p_supplier'=>'供应商名称','is_supplier_url'=>'是否展示供应商链接','supplier_url'=>'供应商链接','supplier_logo'=>'供应商logo',
			'is_bbs_url'=>'是否展示论坛地址','bbs_url'=>'论坛地址','is_buy'=>'我要购买配置','buy_name'=>'购买产品名称','buy_img'=>'购买产品图片','buy_price'=>'购买产品价格','buy_url'=>'购买地址',
			'is_p_propaganda'=>'是否展示宣传广告图','p_propaganda'=>'宣传广告图','release_time'=>'发布时间','application_time'=>'申请时间','experience_time'=>'体验时间','end_time'=>'结束时间'
	);
	
	
	public function _initialize() {
        parent::_initialize();
		$this->model = D('Zhiyoo.Zhiyoo');
		$this->bbsmodel = D('Zhiyoo.bbs');
		$this->productmodel = D('Zhiyoo.Product_slave');
		$this->producmastertmodel = D('Zhiyoo.Product_master');
		$this->productpermodel = D('Zhiyoo.ProductPer');
		$this->producttidmodel = D('Zhiyoo.ProductTid');
		$this->slavemodel = D('Zhiyoo.ZhiyooSlave');
	}
	/*
	 * 产品管理
	 */
	public function productList(){
		$edit_rank = $_GET['edit_rank'] ? 1 :0;
		$selectwhere = '';
		//获取typeid
		$fidthreadclass = $this->bbsmodel->table($this->bbs_threadclass)->field('typeid,fid')->where("name='{$this->bbs_product_name}'")->select();
		$typeidarr = $typeidfidarr = array();
		foreach ($fidthreadclass as $typeidval){
			$typeidarr[] = $typeidval['typeid'];
			$typeidfidarr[] = $typeidval['fid'];
		}
		$p_class = $subject = $p_status = $selectwhere_fid = '';
		
		$typeids = implode(',', $typeidarr);
		$selectwhere = " typeid in ($typeids) and displayorder>=0 and sortid>0";
		if($typeidfidarr) $selectwhere_fid = "fid in (".implode(',', $typeidfidarr).") "; 
		
		// 分类查询
		if (isset($_REQUEST['p_class']) and trim($_REQUEST['p_class'])!='') {
			$p_class = intval(trim($_REQUEST['p_class']));
			$forum_fid_arr = $this->forum_list_fid($p_class);
			$resultarr = array_intersect($forum_fid_arr, $typeidfidarr);
			$resultfid = implode(',', $resultarr);
			if ($resultarr) {
				$selectwhere_fid =" fid in ($resultfid) ";
			}else {
				$selectwhere_fid =" fid = 0 ";
			}
			$_GET['p_class'] = $p_class;
		}
		
		$selectwhere .= " and ".$selectwhere_fid;
		
		
		// 产品名称查询
// 		if ($_POST and trim($_POST['subject'])!='') {
// 			$subject = addslashes(trim($_POST['subject']));
// 			$selectwhere .= " and subject like '%{$subject}%' ";
// 		}
		if (isset($_REQUEST['subject']) and trim($_REQUEST['subject'])!='') {
			$subject = addslashes(trim($_REQUEST['subject']));
			$selectwhere .= " and subject like '%{$subject}%' ";
			$_GET['subject'] = $subject;
		}
		
		// 状态值查询
		if (isset($_REQUEST['p_status']) and trim($_REQUEST['p_status'])!='') {
			$p_status = intval(trim($_REQUEST['p_status']));
			$_GET['p_status'] = $p_status;
			$retfield = $this->statusTime($p_status);
			$pWhere = "identifier='$retfield'";
			$pfield = "optionid";
				
			$p_optionret = $this->forum_typeoption($pWhere,$pfield);
			$p_optionid = $p_optionret[0]['optionid'];
				
			$retWhere = '';
			$time = date('Y-m-d H:i:s',time());
			$retWhere = ($p_status==5) ?  " and value<'$time' " :  " and value>='$time' ";
			//判读当前状态值
				
			$p_optionfield = "sortid,tid,fid,optionid,value";
			$p_optionvar = $this->bbsmodel->table($this->bbs_option)->where("optionid=$p_optionid $retWhere")->field($p_optionfield)->order("tid DESC")->select();
		
// 			echo $this->bbsmodel->getLastSql();
			$threadtid = '';
			$p_optionarr = $diffarr = array();
			foreach ($p_optionvar as $pval){
				$p_optionarr[] = $pval['tid'];
			}
			if ($p_status>1 and $p_status<5) { //筛选状态值（大于1小于5的三个状态值对比查询）
				$difference = intval($p_status-1);
				$retdiff = $this->statusTime($difference);
				$diffWhere = "identifier='$retdiff'";
					
				$diff_optionret = $this->forum_typeoption($diffWhere,$pfield);
				$diff_optionid = $diff_optionret[0]['optionid'];
				$diff_optionvar = $this->bbsmodel->table($this->bbs_option)->where("optionid=$diff_optionid $retWhere")->field($p_optionfield)->order("tid DESC")->select();
				foreach ($diff_optionvar as $dval){
					$diffarr[] = $dval['tid'];
				}
				$optioninter = array_intersect($p_optionarr,$diffarr);
				$optiondiff = array_diff($p_optionarr,$optioninter);
				$threadtid = implode(',',$optiondiff);
		
			}else {
				$threadtid = implode(',',$p_optionarr);
			}
			$selectwhere .= " and tid in ($threadtid)";
		}
		
		
		//获取分类信息
		$threadtypelist = $this->product_type();
		
		// 获取分类信息设置项
		$Where = "identifier in ('p_count','release_time','application_time','experience_time','end_time')";
		$field = "optionid,classid,title,identifier";
		// 获取分类信息设置项
		$typeoptionret = $this->forum_typeoption($Where,$field);
		
		$typeoptionid = $typeidarr = array();
		$optionid = '';
		foreach ($typeoptionret as $typekey => $typeval){
			$typeoptionid[$typeval['optionid']]['optionid'] = $typeval['optionid'];
			$typeoptionid[$typeval['optionid']]['title'] = $typeval['title'];
			$typeoptionid[$typeval['optionid']]['identifier'] = $typeval['identifier'];
			$typeoptionid[$typeval['optionid']][$typeval['identifier']] = $typeval['optionid'];
			$typeidarr[$typeval['identifier']] = $typeval['optionid'];
			$optionid .= $typeval['optionid'].',';
		}
		$optionid = substr($optionid,0,-1);
		
		
		// 分页
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->bbsmodel-> table($this->bbstabal)->where($selectwhere)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		
		//查询产品列表
		$fieldstr = 'tid,fid,typeid,sortid,subject,dateline';
		$optionfield = 'optionid,value';
        //查询出所有通过筛选条件的tid
		$threadinfo = $this->bbsmodel->table($this->bbstabal)->where($selectwhere)->field($fieldstr)->order("tid DESC")->select();
        foreach($threadinfo as $key => $tval){
            $tidlist[$tval['tid']] = $key;
        }
        $tidmap['tid'] = array('in',array_keys($tidlist));
        
        //查找产品的优先级值
        $orders = $this->slavemodel->table('zy_product_tid')->where($tidmap)->select();
        foreach($orders as $val){
            $productOrder[$val['tid']] =  $val['displayorder']<9999?$val['displayorder']:'';
        }
        //插入新的产品进入zy_product_tid表
        $newtid = array_diff(array_keys($tidlist),array_keys($productOrder));
        if(!empty($newtid)){
            $values = '('.implode('),(',$newtid).')';
            $this->producttidmodel->query('INSERT INTO zy_product_tid(`tid`) VALUES'.$values);
        }

        //分页排序
        // $threadlist = $this->producttidmodel->where($tidmap)->order(array('displayorder'=>'asc','tid'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $threadlist = $this->slavemodel->table('zy_product_tid')->where($tidmap)->order(array('displayorder'=>'asc','tid'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($threadlist as $key => $tval){
            $threadlist[$key] =  $threadinfo[$tidlist[$tval['tid']]];
            $threadlist[$key]['displayorder'] = isset($productOrder[$tval['tid']]) ? $productOrder[$tval['tid']] : '';
			$applycount = $this->productmodel->table($this->zhiyoo_product_apply)->where(array('tid'=>$tval['tid']))->count();
			$threadlist[$key]['count'] = $applycount;
			$testcount = $this->productmodel->table($this->zhiyoo_test_content)->where(array('productid'=>$tval['tid']))->count();
			$threadlist[$key]['test_count'] = $testcount;
			
			//获取申请人数阀值系数
			$m_num = $showtotal = '';
			if ($applycount>18) {
				$modulus = $this->productmodel->table($this->zhiyoo_product_apply_count)->field('applycount,modulus')->where(array('tid'=>$tval['tid']))->find();
				$m_num = ($modulus && isset($modulus['modulus']) && $modulus['modulus']) ? $modulus['modulus'] : 9;
				$m_applycount = ($modulus && isset($modulus['applycount']) && $modulus['applycount']) ? $modulus['applycount'] : $applycount;
				$showtotal = $m_applycount*$m_num + intval(substr($tval['tid'],-2)) + intval(substr($tval['dateline'], -1));
			}
			$threadlist[$key]['modulus'] = $m_num;
			$threadlist[$key]['showtotal'] = $showtotal;
			
			
			//获取配置信息
			$optionvar = $this->bbsmodel->table($this->bbs_option)->where("tid={$tval['tid']} and optionid in ($optionid)")->field($optionfield)->select();
			$opidarr = array();
			foreach ($optionvar as $opval){
				$opidarr[$opval['optionid']] = $opval['value'];
			}

			$p_count = $release_time = $application_time = $experience_time = $end_time = $statusstr = '';
			if ($optionvar) {
				$p_count = $opidarr[$typeidarr['p_count']];
				$release_time = $opidarr[$typeidarr['release_time']];
				$application_time = $opidarr[$typeidarr['application_time']];
				$experience_time = $opidarr[$typeidarr['experience_time']];
				$end_time = $opidarr[$typeidarr['end_time']];
				
				$statusstr = $this->statusValue($release_time,$application_time,$experience_time,$end_time);
			}
			
			$threadlist[$key]['p_count'] = $p_count;
			$threadlist[$key]['release_time'] = $release_time;
			$threadlist[$key]['application_time'] = $application_time;
			$threadlist[$key]['experience_time'] = $experience_time;
			$threadlist[$key]['end_time'] = $end_time; 
			$threadlist[$key]['status'] = $statusstr;
			$valfid = $tval['fid'];
			$forumtypename = $this->forum_type_name($valfid,$threadtypelist['type']);
			$threadlist[$key]['typename'] = $forumtypename;
            
		}
		
		// 分页
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
		$this -> assign('p_class',$p_class);
		$this -> assign('subject',$subject);
		$this -> assign('p_status',$p_status);
		
		$this -> assign('threadtype',$threadtypelist['list']);
		$this -> assign('threadlist',$threadlist);
        $this->assign('edit_rank',$edit_rank);
		$this -> display();
	}
	/*
	 * 产品申请管理
	 */
	public function applyList(){
		
		$tid = intval($_REQUEST['tid']);
		$pnum = intval($_REQUEST['pnum']);	//产品数
		if ($tid<1) {
			$this -> writelog('查看产品申请失败tid为'.$tid.'。',$this->bbstabal,$tid,__ACTION__ ,"","view");
			$this->error("参数有误！");
		}
		$Wheresub = "tid=$tid";
		$Where = $Wheresub;
		
		$username = $addr_ip = $tel = $begintime = $endtime = $p_status = '';
		//用户名
// 		if ($_POST and trim($_POST['username'])!='') {
// 			$username = addslashes(trim($_POST['username']));
// 			$Where .= " and username like '%{$username}%' ";
// 		}
		if (isset($_REQUEST['username']) and trim($_REQUEST['username'])!='') {
			$username = addslashes(trim($_REQUEST['username']));
			$Where .= " and username like '%{$username}%' ";
			$_GET['username'] = $username;
		}
		// 提交申请IP
		if (isset($_REQUEST['addr_ip']) and trim($_REQUEST['addr_ip'])!='') {
			$addr_ip = addslashes(trim($_REQUEST['addr_ip']));
			$Where .= " and addr_ip like '%{$addr_ip}%' ";
			$_GET['addr_ip'] = $addr_ip;
		}
		// 手机号
		if (isset($_REQUEST['tel']) and trim($_REQUEST['tel'])!='') {
			$tel = trim($_REQUEST['tel']);
			$Where .= " and tel like '%{$tel}%' ";
			$_GET['tel'] = $tel;
		}
		// 提交开始时间
		if (isset($_REQUEST['begintime']) and trim($_REQUEST['begintime'])!='') {
			$begintime = strtotime(trim($_REQUEST['begintime']));
			$Where .= " and createdate>='$begintime' ";
			$_GET['begintime'] = $begintime;
		}
		// 提交结束时间
		if (isset($_REQUEST['endtime']) and trim($_REQUEST['endtime'])!='') {
			$endtime = strtotime(trim($_REQUEST['endtime']));
			$Where .= " and createdate<='$endtime' ";
			$_GET['endtime'] = $endtime;
		}
		// 状态值
		if (isset($_REQUEST['p_status']) and trim($_REQUEST['p_status'])!='') {
			$p_status = intval(trim($_REQUEST['p_status']));
			$Where .= " and status='$p_status' ";
			$_GET['p_status'] = $p_status;
		}
		
		
		$fieldstr = "subject,author";
		$threadfind = $this->bbsmodel->table($this->bbstabal)->where($Wheresub)->field($fieldstr)->find();
		
		if ($_GET['export'] == 1) {
			$exportfield = array(
					array('field'=>'id','title'=>'ID','type'=>'int'),
					array('field'=>'fid','title'=>'FID','type'=>'int'),
					array('field'=>'tid','title'=>'TID','type'=>'int'),
					array('field'=>'uid','title'=>'用户ID','type'=>'int'),
					array('field'=>'username','title'=>'用户名','type'=>'varchar'),
					array('field'=>'names','title'=>'姓名','type'=>'varchar'),
					array('field'=>'tel','title'=>'手机号','type'=>'char'),
					array('field'=>'qq','title'=>'QQ','type'=>'char'),
					array('field'=>'address','title'=>'地址','type'=>'varchar'),
					array('field'=>'content','title'=>'申请描述','type'=>'text'),
					array('field'=>'status','title'=>'状态2:通过1:核准|0:申请|-1:驳回','type'=>'int'),
					array('field'=>'addr_ip','title'=>'IP','type'=>'char'),
					array('field'=>'createdate','title'=>'创建时间','type'=>'dateint'),
					array('field'=>'modifytime','title'=>'最后修改时间','type'=>'dateint'),
					
			);

			$exportlist = $this->productmodel->table($this->zhiyoo_product_apply)->where($Wheresub)->order('id DESC')->select();
			$this->exportCsv($exportlist,$exportfield,$threadfind['subject']."_申请列表_".date('Y-m-d-h-i').".csv");
		}
		//申请通过人数
		$pt_num = $this->productmodel-> table($this->zhiyoo_product_apply)->where(array('tid'=>$tid,'status'=>2))->count();
		// 分页
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->productmodel-> table($this->zhiyoo_product_apply)->where($Where)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		
		$applylist = $this->productmodel->table($this->zhiyoo_product_apply)->where($Where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
// 		echo $this->model->getLastSql();
		$fieldmember = "extcredits2,extcredits4";	// extcredits2是金币 	extcredits4是智豆
		foreach ($applylist as $appkey=>$appval){
			$memberWhere = array('uid'=>$appval['uid']);
			$fieldmemberdata = $this->bbsmodel->table($this->bbs_member_count)->where($memberWhere)->field($fieldmember)->find();
			$applylist[$appkey]['Gold'] = $fieldmemberdata['extcredits2'];
			$applylist[$appkey]['Chibeans'] = $fieldmemberdata['extcredits4'];
			//获取评测数
			$themeCount = $this->themeCount($appval['uid']);
			$applylist[$appkey]['themecount'] = $themeCount;
		}

		// 分页
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
		
		$this->assign('username',$username);
		$this->assign('addr_ip',$addr_ip);
		$this->assign('tel',$tel);
		$this->assign('begintime',date('Y-m-d H:i:s',$begintime));
		$this->assign('endtime',date('Y-m-d H:i:s',$endtime));
		$this->assign('p_status',$p_status);
		
		$this->assign('tid',$tid);
		$this -> assign('applylist',$applylist);
		$this -> assign('threadfind',$threadfind);
		$this->assign('pnum',$pnum);
		$this->assign('pt_num',$pt_num);
		
		$this -> display();
	}
	/*
	 * 产品众测报告管理
	 */
	public function testContent(){
		$tid = intval($_REQUEST['tid']);
		if ($tid<1) {
			$this -> writelog('查看产品众测报告失败tid为'.$tid.'。');
			$this->error("参数有误！");
		}
		$statusarr = array(array('id'=>'1','title'=>'已通过'),array('id'=>'-1','title'=>'已驳回'),array('id'=>'-2','title'=>'待审核'),array('id'=>'-3','title'=>'审核忽略'));
		$atatusname = array();
		$isstatus = false;
		foreach ($statusarr as $staval){
			$atatusname[$staval['id']] = $staval['title'];
		}
		$order = 'DESC';$orderBy = 'tid DESC';
		$where = $class = $filterurl = '';
		if (isset($_REQUEST['field']) and isset($_REQUEST['order'])) {
			$field = addslashes(trim($_REQUEST['field']));
			$order = addslashes(trim($_REQUEST['order']));
			$orderBy = "$field $order";
		}
		$Wheresub = "tid=$tid";
		$wherejoin = '';
		$Where = "productid=".$tid;
		
		$username = $addr_ip = $tel = $begintime = $endtime = $p_status = '';
		//用户名
// 		if ($_POST and trim($_POST['username'])!='') {
// 			$username = addslashes(trim($_POST['username']));
// 			$Where .= " and username like '%{$username}%' ";
// 		}
		if (isset($_REQUEST['username']) and trim($_REQUEST['username'])!='') {
			$username = addslashes(trim($_REQUEST['username']));
			$Where .= " and username like '%{$username}%' ";
			$_GET['username'] = $username;
		}
		//IP地址
		if (isset($_REQUEST['addr_ip']) and trim($_REQUEST['addr_ip'])!='') {
			$addr_ip = addslashes(trim($_REQUEST['addr_ip']));
			$Where .= " and ip like '%{$addr_ip}%' ";
			$_GET['addr_ip'] = $addr_ip;
		}
		// 手机号
		if (isset($_REQUEST['tel']) and trim($_REQUEST['tel'])!='') {
			$tel = trim($_REQUEST['tel']);
			$sonWhere = " tid=$tid and status=2 ";
			$sonlist = $this->productmodel->field('tid')->table($this->zhiyoo_product_apply)->where($sonWhere." and tel like '%{$tel}%' ")->order('tid DESC')->select();
			$stid = array();
			foreach ($sonlist as $sval){
				$stid[] = $sval['tid'];
			}
			$sontid = implode(',',$stid);
			
			$Where .= " and productid in ('$sontid')";
			$_GET['tel'] = $tel;
		}
		// 开始时间
		if (isset($_REQUEST['begintime']) and trim($_REQUEST['begintime'])!='') {
			$begintime = strtotime(trim($_REQUEST['begintime']));
			$Where .= " and dateline>='$begintime' ";
			$_GET['begintime'] = $begintime;
		}
		// 结束时间
		if (isset($_REQUEST['endtime']) and trim($_REQUEST['endtime'])!='') {
			$endtime = strtotime(trim($_REQUEST['endtime']));
			$Where .= " and dateline<='$endtime' ";
			$_GET['endtime'] = $endtime;
		}
		// 状态值
		if (isset($_REQUEST['p_status']) and trim($_REQUEST['p_status'])!='') {
			$p_status = intval(trim($_REQUEST['p_status']));
			if ($p_status==1) {
				$WhereStatus = " displayorder>='0' ";
			}else {
				$WhereStatus = " displayorder='$p_status' ";
			}
			$_GET['p_status'] = $p_status;
			$isstatus = true;
		}
		
		$fieldstr = "subject,author";
		$threadfind = $this->bbsmodel->table($this->bbstabal)->where($Wheresub)->field($fieldstr)->find();
		
		
		$testlists = $this->productmodel->table($this->zhiyoo_test_content)->where($Where)->order($orderBy)->select();
		
		//获取众测报告tid
		$teststatus = $testtidarr = $teststidarr = array();
		foreach ($testlists as $tkey=>$tval){
			$testtidarr[] = $tval['tid'];
		}
		$testtid = implode(',', $testtidarr);
		$sWhere = "tid in ($testtid)";
		if ($isstatus) {
			$sWhere = $sWhere." and $WhereStatus ";
		}
		$testsarr = $this->bbsmodel->table($this->bbstabal)->where($sWhere)->field('tid,displayorder')->select();
		foreach ($testsarr as $taval){
			$teststidarr[] = $taval['tid'];
			$teststatus[$taval['tid']] = $taval['displayorder'];
		}
		if ($isstatus) {
			$testtidstr = implode(',', $teststidarr);
			$Where .= " and tid in ($testtidstr)";
			$testlist = $this->productmodel->table($this->zhiyoo_test_content)->where($Where)->order($orderBy)->select();
		}else{
			$testlist = $testlists;
		}
		
		$fieldmember = "extcredits2,extcredits4";	// extcredits2是金币 	extcredits4是智豆
// 		$onceWhere = " tid=$tid and status=2 ";
		$onceWhere = array('tid'=>$tid,'status'=>'2');
		$oncefield = "tel,qq";
		foreach ($testlist as $testkey=>$testval){
// 			$onceWhere = "uid=".$testval['uid']." and ".$onceWhere;
			$onceWhere['uid'] = $testval['uid'];
			$oncedata = $this->productmodel->table($this->zhiyoo_product_apply)->where($onceWhere)->field($oncefield)->find();
// 			echo $this->model->getLastSql();
			$testlist[$testkey]['tel'] = $oncedata['tel'];
			$testlist[$testkey]['qq'] = $oncedata['qq'];
			$memberWhere = array('uid'=>$testval['uid']);
			$fieldmemberdata = $this->bbsmodel->table($this->bbs_member_count)->where($memberWhere)->field($fieldmember)->find();
			$testlist[$testkey]['Gold'] = $fieldmemberdata['extcredits2'];
			$testlist[$testkey]['Chibeans'] = $fieldmemberdata['extcredits4'];
			
			$statusdisplayorder  = $teststatus[$testval['tid']];
			if ($statusdisplayorder>=0) {
				$statusdisplayorder = 1;
			}
			$testlist[$testkey]['statusname'] = $atatusname[$statusdisplayorder];
			//获取评测数
			$themeCount = $this->themeCount($testval['uid']);
			$testlist[$testkey]['themecount'] = $themeCount;
		}

		if ($_GET['export'] == 1) {
			$exportfield = array(
					array('field'=>'fid','title'=>'FID','type'=>'int'),
					array('field'=>'tid','title'=>'TID','type'=>'int'),
					array('field'=>'uid','title'=>'用户ID','type'=>'int'),
					array('field'=>'username','title'=>'用户名','type'=>'varchar'),
					array('field'=>'title','title'=>'报告名称','type'=>'varchar'),
					array('field'=>'tel','title'=>'手机号','type'=>'char'),
					array('field'=>'qq','title'=>'QQ','type'=>'char'),
					array('field'=>'Gold','title'=>'金币','type'=>'int'),
					array('field'=>'Chibeans','title'=>'智豆','type'=>'int'),
					array('field'=>'status','title'=>'状态2:通过1:核准|0:待审核|-1:驳回','type'=>'int'),
					array('field'=>'ip','title'=>'IP','type'=>'char'),
					array('field'=>'dateline','title'=>'创建时间','type'=>'dateint')
		
			);
				
			$this->exportCsv($testlist,$exportfield,$threadfind['subject']."_众测报告列表_".date('y-m-d-h-i').".csv");
		}
		($order=='ASC')?$order = 'DESC':$order='ASC';
		$this->assign('order',$order);
		$this->assign('tid',$tid);
		
		$this->assign('username',$username);
		$this->assign('addr_ip',$addr_ip);
		$this->assign('tel',$tel);
		$this->assign('begintime',date('Y-m-d H:i:s',$begintime));
		$this->assign('endtime',date('Y-m-d H:i:s',$endtime));
		$this->assign('p_status',$p_status);
		
		$this -> assign('statusarr',$statusarr);
		$this -> assign('applylist',$testlist);
		$this -> assign('threadfind',$threadfind);
		$this -> display();
	}
	/*
	 * 产品评测管理
	 */
	public function themeList(){
		$id = intval($_REQUEST['id']);
		$mark = addslashes(trim($_REQUEST['mark']));
		
		$_GET['id'] = $id;
		$where = $selecttable = $site = '';
				
		if ($mark=='apply') {
			$where = "id=$id";
			$selecttable = $this->zhiyoo_product_apply;
			$site = '申请管理';
		}else{
			$where = "tid=$id";
			$selecttable = $this->zhiyoo_test_content;
			$site = '众测报告管理';
		}
		$_GET['mark'] = $mark;
		if ($id<1) {
			$this -> writelog('查看产品'.$site.'评测失败ID为'.$id.'。');
			$this->error("参数有误！");
		}
		$retuid = $this->productmodel->table($selecttable)->field('uid')->where($where)->find();
		if (!$retuid or !isset($retuid['uid']) or $retuid['uid']<1) {
			$this -> writelog('查看产品'.$site.'评测失败，没有查找到相应用户。');
			$this->error("参数有误！");
		}
		$uid = $retuid['uid'];
		$threadtypelist = $this->themeType();
		
		$fields = "tid,fid,typeid,sortid,subject,dateline,views,replies,displayorder";
		$themeWhere = "authorid=".$uid;
		
		$subject = $typeid = '';
		// 标题
		if (isset($_REQUEST['subject'])  and trim($_REQUEST['subject'])!='') {
			$subject = addslashes(trim($_REQUEST['subject']));
			$themeWhere .= " and subject like '%{$subject}%' ";
			$_GET['subject'] = $subject;
		}
		
		// 分类查询
		if (isset($_REQUEST['p_class']) and trim($_REQUEST['p_class'])!='') {
			$typeid = trim($_REQUEST['p_class']);
			if ($typeid==1) {
				$testarr = $this->productmodel->table($this->zhiyoo_test_content)->field('fid,typeid')->where("uid=$uid")->select();
				$arrtest = array();
				foreach ($testarr as $aval){
					$arrtest[] = '(fid='.$aval['fid'].' and typeid='.$aval['typeid'].')';
				}
				$arrtest = array_keys(array_count_values($arrtest));
				$seltWhere = implode(' or ', $arrtest);
			}else{
				$seltWhere = 'fid='. str_replace(',',' and typeid=',$typeid);
			}
			if ($seltWhere != '') {
				$themeWhere .=" and ($seltWhere) ";
			}
			$_GET['p_class'] = $typeid;
		}else {
		
			$themtype = $this->slavemodel->table($this->zhiyoo_test_topnav)->field('id,ref_typeid,ref_fid')->where('status=1 and poststatus=1')->select();
			$tharr = array();
			foreach ($themtype as $ttval){
				$tharr[] = '(fid='.$ttval['ref_fid'].' and typeid='.$ttval['ref_typeid'].')';
			}
			$selWheret = implode(' or ', $tharr);
			$testarr = $this->productmodel->table($this->zhiyoo_test_content)->field('fid,typeid')->where("uid=$uid")->select();
			$arrtest = array();
			foreach ($testarr as $aval){
				$arrtest[] = '(fid='.$aval['fid'].' and typeid='.$aval['typeid'].')';
			}
			$arrtest = array_keys(array_count_values($arrtest));
			$seltWhere = implode(' or ', $arrtest);
			if ($seltWhere!='' && $selWheret!=''){
				$themeWhere = '('.$selWheret.' or '.$seltWhere .') and '.$themeWhere;
			}elseif ($seltWhere!=''){
				$themeWhere = '('.$seltWhere .') and '.$themeWhere;
			}elseif ($selWheret!=''){
				$themeWhere = '('.$selWheret .') and '.$themeWhere;
			}
			
		}
		
		// 分页
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->bbsmodel-> table($this->bbstabal)->where($themeWhere)->count();
		
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		
		$themelist = $this->bbsmodel->table($this->bbstabal)->field($fields)->where($themeWhere)->order('tid DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
	
		foreach ($themelist as $themekey=>$themeval){
			$fid_typeid = $themeval['fid'].','.$themeval['typeid'];
			$retsetid = array_key_exists($fid_typeid,$threadtypelist['type']);
			$typeidname = $retsetid ? $threadtypelist['type'][$fid_typeid] : '众测报告';
			
			$themelist[$themekey]['typename'] = $typeidname;
		}
	
		// 分页
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
		
		$this -> assign('subject',$subject);
		$this -> assign('typeid',$typeid);
		
		$this -> assign('id',$id);
		$this -> assign('mark',$mark);
		$this -> assign('site',$site);
		$this -> assign('uid',$uid);
		$this -> assign('threadtype',$threadtypelist['list']);
		$this -> assign('themelist',$themelist);
		$this -> display();
	}
	
	/*
	 * 众测报告编辑优先级
	 */
	public function batchTreat(){
		if (!isset($_POST['action']) and $_POST['action']!='batchTreat') {
			$this -> writelog('众测报告编辑优先级参数有误。');
			$this->error("参数有误！");
		}

		if (!isset($_POST['level']) or !count($_POST['level'])) {
			$this -> writelog('众测报告编辑优先级参数有误，没有要处理项。');
			$this->error("参数有误，请勾选要处理项！");
		}
		foreach ($_POST['level'] as $k=>$v){
			$v = abs(intval($v));
			$p_ret = $this->producmastertmodel -> query("UPDATE $this->zhiyoo_test_content SET rank='$v' where tid='$k'");
			if($p_ret!==false){
				$this -> writelog('众测报告编辑优先级成功，tid为'.$k.'优先级改为：'.$v);
			}else{
				$this -> writelog('众测报告编辑优先级失败，tid为'.$k.'优先级改为：'.$v);
				$this->error("参数有误！");
			}
		}
		$this->success("编辑优先级成功！");

	}
	/*
	 * 产品申请批量审核
	 */
	public function batchCheck(){
        if(empty($_GET['tid'])) $this->error('产品tid提交错误');
        if($_POST['dotype'] == '批量审核'){
            if (!isset($_POST['action']) or $_POST['action']!='batchCheck') {
                $this -> writelog('产品申请批量审核参数有误。');
                $this->error("参数有误！");
            }
            if (!isset($_POST['checkname']) or !count($_POST['checkname'])) {
                $this -> writelog('产品申请批量审核参数有误，没有勾选要处理项。');
                $this->error("参数有误，请勾选要处理项！");
            }
            $ids = implode(',', $_POST['checkname']);
            $modifytime = time();
            $ret = $this->producmastertmodel->query("UPDATE $this->zhiyoo_product_apply SET status='1',modifytime=$modifytime WHERE `id` in ($ids)");
            
            if($ret!==false){
                $this -> writelog('产品申请批量审核成功，产品tid为'.$_GET['tid'].'，申请id为'.$ids,$this->zhiyoo_product_apply,$ids,__ACTION__ ,"","edit");
                
                $logtxt = date('H:i:s').' 操作者id:'. $_SESSION['admin']['admin_id'].' 产品申请批量审核成功，产品tid为'.$_GET['tid'].'，申请id为'.$ids;
                file_put_contents('/tmp/apply_change.log.'.date('Y-m-d'),$logtxt."\n",FILE_APPEND);
                
                $this->success("批量审核成功！");
            }else{
                $this -> writelog('产品申请批量审核参数有误，修改失败，产品tid为'.$_GET['tid'].',id为'.$ids,$this->zhiyoo_product_apply,$ids,__ACTION__ ,"","edit");
                $this->error("参数有误！");
            }
        }
        if($_POST['dotype'] == '批量驳回'){
           if (!isset($_POST['action']) or $_POST['action']!='batchCheck') {
                $this->error("参数有误！");
            }
            if (!isset($_POST['checkname']) or !count($_POST['checkname'])) {
                $this->error("参数有误，请勾选要处理项！");
            }
            $reject=",reject=''";
            $modifytime = time();
            foreach($_POST['checkname'] as $id){
                $finddata = $this->productmodel->table($this->zhiyoo_product_apply)->where(array('id'=>$id))->field("tid,uid")->find();
                $p_uid = $finddata['uid'];
                $p_tid = $finddata['tid'];
                
                $status = -1;
                $ret = $this->producmastertmodel->query("UPDATE $this->zhiyoo_product_apply SET status='$status' ,modifytime=$modifytime $reject  WHERE `id` =$id");
                if($ret!==false){
                    
                    $logtxt =  date('H:i:s').' 操作者id:'. $_SESSION['admin']['admin_id'].'  产品申请批量驳回成功,产品tid为'.$_GET['tid'].'，id为'.$id.',状态status为'.$status;
                    file_put_contents('/tmp/apply_change.log.'.date('Y-m-d'),$logtxt."\n",FILE_APPEND);
                    
                    $strstatus =  '-1';
                    //返回金币或智豆接口
                    $ares = $this->producmastertmodel->query("UPDATE $this->zy_credit_log SET status='$strstatus'  WHERE `a_id`=$id and `uid` =$p_uid and `tid`=$p_tid");
                    
                    //通过或者驳回后需要发送通知
                    $logdata = array(
                        'a_id' => $id,
                        'uid' => $p_uid,
                        'tid' => $p_tid,
                        'dateline' => time(),
                        'status' => $strstatus,
                    );
                    $this->producmastertmodel->table($this->zy_notify_log) ->add($logdata);
                    
                    $this -> writelog('产品申请批量驳回成功,产品tid为'.$_GET['tid'].'，id为'.$id.',状态status为'.$status,$this->zhiyoo_product_apply,$id,__ACTION__ ,"","edit");
                    
                    
                    
                    
                }
                
            }
            $this->success("批量驳回成功！");
        }
		
	}
	/*
	 * 调取tid的地址或申请描述
	 * 查看产品申请地址或申请描述
	 * 查看产品申请驳回原因
	 */
	public function viewContent(){
		$id = intval($_REQUEST['id']);
		if ($id<1) {
			$this -> writelog('查看产品申请地址或申请描述参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$Where = "id=$id";
		$field = trim($_REQUEST['field']);
		$fields = $field;
		if ($fields == 'address') {
			$field = "address,pasturl,pasturl2,pasturl3";
		}
		$content = $this->productmodel->table($this->zhiyoo_product_apply)->where($Where)->field("$field")->find();
		if ($fields == 'address') {
// 			$contentstr = $content['address']."\n \n".$content['pasturl']."\n".$content['pasturl2']."\n".$content['pasturl3']."\n";
			$contentstr = $content;
		}else{
			$contentstr = $content[$field];
		}
		$this -> assign('fields',$fields);
		$this -> assign('content',$contentstr);
		$this -> display();
	}
	
	/*
	 * 修改产品申请状态
	 */
	public function updateStart(){
		$id = intval($_REQUEST['id']);
		$tid = intval($_REQUEST['tid']);
        
		$status = intval($_REQUEST['status']);
		if ($id<1) {
			$this -> writelog('修改产品申请状态参数有误id为'.$id);
			$this->error("参数有误！");
		}
		$reject = '';
		switch ($status){
			case 1:
				$retstr = "审核" ;
				break;
			case 2:
				$retstr = "通过" ;
				break;
			case -1:
				$retstr = "驳回" ;
				$reject =  (isset($_REQUEST['reject']) && trim($_REQUEST['reject'])!='') ? " , reject='".addslashes(trim($_REQUEST['reject']))."'" : '';
				break;
		}
		$modifytime = time();
		$finddata = $this->productmodel->table($this->zhiyoo_product_apply)->where(array('id'=>$id))->field("tid,uid")->find();
		$p_uid = $finddata['uid'];
		$p_tid = $finddata['tid'];
		if($tid != $p_tid){
            $this->error('产品tid提交错误');
        }
        
		$ret = $this->producmastertmodel->query("UPDATE $this->zhiyoo_product_apply SET status='$status' ,modifytime=$modifytime $reject  WHERE `id` =$id");
		if($ret!==false){
			if ($status=='-1' || $status == 2) {
				$strstatus = ($status ==2) ? '1' :'-1';
				//返回金币或智豆接口
				$ares = $this->producmastertmodel->query("UPDATE $this->zy_credit_log SET status='$strstatus'  WHERE `a_id`=$id and `uid` =$p_uid and `tid`=$p_tid");
				
				//通过或者驳回后需要发送通知
				$logdata = array(
					'a_id' => $id,
					'uid' => $p_uid,
					'tid' => $p_tid,
					'uid' => $p_uid,
					'dateline' => time(),
					'status' => $strstatus,
				);
				$this->producmastertmodel->table($this->zy_notify_log) ->add($logdata);
				
				
				$this -> writelog('产品申请'.$retstr.'添加通知成功，申请id为'.$id.',uid为'.$p_uid.',tid为'.$p_tid.',状态status为'.$status,$this->zy_notify_log,$id,__ACTION__ ,"","edit");
			
			}
			$this -> writelog('产品申请'.$retstr.'操作成功，产品tid为'.$p_tid.',申请id为'.$id.'状态status为'.$status,$this->zhiyoo_product_apply,$id,__ACTION__ ,"","edit");
            
            $logtxt = date('H:i:s').'操作者id:'. $_SESSION['admin']['admin_id'].' 产品申请'.$retstr.'操作成功，产品tid为'.$p_tid.',申请id为'.$id.'状态status为'.$status;
            file_put_contents('/tmp/apply_change.log.'.date('Y-m-d'),$logtxt."\n",FILE_APPEND);
			$this->success("{$retstr}成功！");
		}else{
			$this -> writelog('修改产品申请状态参数有误id为'.$id,$this->zhiyoo_product_apply,$id,__ACTION__ ,"","edit");
			$this->error("参数有误！");
		}
	}
	
	/*
	 * 暂无应用
	 * 修改产品众测报告状态  
	*/
	public function updateTestStart(){
		$tid = intval($_REQUEST['tid']);
		$status = intval($_REQUEST['status']);
		if ($tid<1) {
			$this -> writelog('修改产品众测报告状态参数有误tid为'.$tid);
			$this->error("参数有误！");
		}
		switch ($status){
			case 1:
				$retstr = "审核" ;
				break;
			case 2:
				$retstr = "通过" ;
				break;
			case -1:
				$retstr = "驳回" ;
				break;
		}
		$modifytime = time();
		$ret = $this->productmastermodel->query("UPDATE $this->zhiyoo_test_content SET status='$status',modifytime=$modifytime WHERE `tid` =$tid");

		if($ret!==false){
			if ($status=='1') {
				//修改论坛帖子tid通过接口
			}
			$this -> writelog('产品众测报告'.$retstr.'成功tid为'.$tid.'状态status为'.$status,$this->zhiyoo_test_content,$tid,__ACTION__ ,"","edit");
			$this->success("{$retstr}成功！");
		}else{
			$this -> writelog('修改产品众测报告状态参数有误tid为'.$tid,$this->zhiyoo_test_content,$tid,__ACTION__ ,"","edit");
			$this->error("参数有误！");
		}
	}

	
	/*
	 * 获取该版块主题分类表
	 */
	public function forum_threadtype(){
		$threadlist = $this->bbsmodel->table($this->bbs_threadclass)->where('fid='.$this->bbsfid)->order('displayorder asc')->select();
        
// 		$threadlist = $this->bbsmodel->getForumThreadtypeByFid($this->bbsfid);
		$threadarr = array();
		foreach ($threadlist as $val){
			$threadarr[$val['typeid']]['typeid'] =  $val['typeid'];
			$threadarr[$val['typeid']]['name'] =  $val['name'];
		}
		return $threadarr;
	}
	
	/*
	 * 获取产品分类
	 */
	public function product_type(){
		$producttype = $this->slavemodel->table($this->zhiyoo_prodct_type)->where("status=1")->field("`id`,`gid`,`name`,`rename`")->select();
		$ptypelistarr = array();
		foreach ($producttype as $ptkey=>$ptval){
			$ptypename = trim($ptval['rename']);
			$ptypegid = intval($ptval['gid']);
			if ($ptypename=='') {
				$ptypename = trim($ptval['name']);
			}
			if ($ptypegid==0) {
				$ptypegid = $ptval['id'].'_0';
			}
			$ptypelistarr['list'][$ptkey]['id'] = $ptval['id'];
			$ptypelistarr['list'][$ptkey]['name'] = $ptypename;
			$ptypelistarr['type'][$ptypegid] = $ptypename;
		}

		return $ptypelistarr;
	}
	/*
	 * 返回分类名称
	 */
	public function forum_type_name($fid,$threadtypearr){
		$typename = '';
		$forumfid = $this->forum_forum_gid($fid);
// 		$threadtypelist = $this->product_type();
		$retfid = array_key_exists($forumfid,$threadtypearr);
		if ($retfid) {
			$typename = $threadtypearr[$forumfid];
		}else {
			$producttypefind = $this->slavemodel->table($this->zhiyoo_prodct_type)->where("status=1 and `special` like '%{$forumfid}%'")->field("`id`,`gid`,`name`,`rename`")->find();
			if ($producttypefind and isset($producttypefind['id'])) {
				$forumfid = $producttypefind['id'].'_0';
				$typename = $threadtypearr[$forumfid];
			}
		}
		return $typename;
	}
	
	/*
	 * 返回指定fid的gid（fup）
	 */
	public function forum_forum_gid($fid){
		$forumfidarr = $this->bbsmodel->table($this->bbs_forum)->where("fid='{$fid}'")->field('fid,fup,type')->find();
		$forumfup = intval($forumfidarr['fup']);
		if($forumfup!=0){
			$forumfid = $this->forum_forum_gid($forumfup);
		}else {
			$forumfid = intval($forumfidarr['fid']);
		}
		return $forumfid;
	}
	
	/*
	 * 返回当前分类下整体fid（fid，subfid）
	 */
	public function forum_list_fid($forumtypeoid){
		$fidarr = $retfidarr = array();
		$pypefind = $this->slavemodel->table($this->zhiyoo_prodct_type)->where("id=$forumtypeoid")->field("`gid`,`special`")->find();
		if (!$pypefind['gid']) {
			$fidarr = explode(',', $pypefind['special']);
		}else $fidarr[] = $pypefind['gid'];
		foreach ($fidarr as $fidval){
			$bbsfidarr = $this->bbsmodel->table($this->bbs_forum)->field('fid')->where("fup=$fidval")->select();
			foreach ($bbsfidarr as $bfval){
				$bbsfarr = $this->bbsmodel->table($this->bbs_forum)->field('fid')->where("fup={$bfval['fid']}")->select();
				$retfidarr[] = $bfval['fid'];
				foreach ($bbsfarr as $bbfval){
					$retfidarr[] = $bbfval['fid'];
				} 
			}
		}
		return $retfidarr;
	}
	/*
	 * 获取分类信息设置项
	 */
	public function forum_typeoption($Where,$field="*"){
		$typeoptionret = $this->bbsmodel->table($this->bbs_forum_typeoption)->where($Where)->field($field)->select();
// 		echo $this->bbsmodel->getLastSql();
		return $typeoptionret;
	}
	/*
	 * 返回评测数量
	 */
	public function themeCount($uid){
		$themtype = $this->slavemodel->table($this->zhiyoo_test_topnav)->field('id,ref_typeid,ref_fid')->where('status=1 and poststatus=1')->select();
		$tharr = array();
		foreach ($themtype as $ttval){
			$tharr[] = '(fid='.$ttval['ref_fid'].' and typeid='.$ttval['ref_typeid'].')';
		}
		$selWhere = implode(' or ', $tharr);
// 		$testcount = $this->model->table($this->zhiyoo_test_content)->where("uid=$uid")->count();
		$testarr = $this->productmodel->table($this->zhiyoo_test_content)->field('fid,typeid')->where("uid=$uid")->select();
		$arrtest = array();
		foreach ($testarr as $aval){
			$arrtest[] = '(fid='.$aval['fid'].' and typeid='.$aval['typeid'].')';
		}
		$arrtest = array_keys(array_count_values($arrtest));
		$seltWhere = implode(' or ', $arrtest);
		if ($seltWhere!='') {
			$seltWhere = ' or '.$seltWhere;
		}
		$selWhere = $selWhere.$seltWhere;
		$threadcount = $this->bbsmodel->table($this->bbstabal)->where("($selWhere) and authorid=$uid")->count();
// 		echo $this->bbsmodel->getLastSql()."<br />";
// 		$count = intval($testcount) + intval($threadcount);
		return intval($threadcount);
	}
	/*
	 * 返回评测分类
	 */
	public function themeType(){
		$themtypes = $this->slavemodel->table($this->zhiyoo_test_topnav)->field('id,navname,ref_typeid,ref_fid')->where('status=1 and poststatus=1')->select();
		$retthemetype = array();
		foreach ($themtypes as $k=>$v){
			$keytype = $v['ref_fid'].','.$v['ref_typeid'];
			$retthemetype['list'][$k]['typeid'] = $keytype;
			$retthemetype['list'][$k]['navname'] = $v['navname'];
			$retthemetype['type'][$keytype] = $v['navname'];
		}
		$retthemetype['list'][] = array('typeid'=>'1','navname'=>'众测报告');
		return $retthemetype;
	}
	/*
	 * 返回状态值
	 */
	public function statusValue($v1='',$v2='',$v3='',$v4=''){
		$retstatus= '已结束';
		$time = date('Y-m-d H:i:s',time());
		if($v1>$time){
			$retstatus = '待上线';
		}else if ($time>$v1 and $time<=$v2) {
			$retstatus = '即将上线';
		}else if($time>$v2 and $time<=$v3){
			$retstatus = '申请中';
		}else if($time>$v3 and $time<=$v4){
			$retstatus = '体验中';
		}
		return $retstatus;
	}
	/*
	 * 返回相应字段
	 */
	public function statusTime($status){
		switch($status){
			case 1:
				$retfield = "release_time" ;
				break;
			case 2:
				$retfield = "application_time" ;
				break;
			case 3:
				$retfield = "experience_time" ;
				break;
			case 4:
				$retfield = "end_time" ;
				break;
			case 5:
				$retfield = "end_time" ;
				break;
			default:
				$retfield = "end_time" ;
		}
		return $retfield;
	}
	/*
	 * 返回gid下的版块fid
	 */
	public function returnFid(){
		$selfid = $this->bbsmodel->table($this->bbs_forum)->field('fid')->where("fup=".$this->bbsgid." and type='forum'")->select();
		$retfid = '';
		$retarr = array();
		foreach ($selfid as $val){
			$retarr[] = $val['fid'];
		}
		$retfid = implode(',', $retarr);
		return $retfid;
	}
	/*
	 * 产品相关导出csv
	 */
	public function exportCsv($client_lists,$exportfield,$filename) {
	
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=" . $filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');

		$str = "";
		if (empty($client_lists)) {
			$str.=iconv('utf-8', 'gb2312', '没有任何节点信息');
		} else {
			foreach ($exportfield as $fkey=>$fval){
				$str .= iconv('utf-8', 'gb2312', "{$fval['title']}").",";
			}
			$str .=  "\r\n";
// 			$str = "" . iconv('utf-8', 'gb2312', '客户名称') . "," . iconv('utf-8', 'gb2312', '联系人') . "," . iconv('utf-8', 'gb2312', '联系方式') . "," . iconv('utf-8', 'gb2312', '协议数量') . "," . iconv('utf-8', 'gb2312', '合同数量') . "," . iconv('utf-8', 'gb2312', '创建时间') . "\r\n";
			foreach ($client_lists as $key => $val) {
				foreach ($exportfield as $efkey=>$efval){
					$field = $efval['field'];
					$content = addslashes(trim($val[$field]));
					
					if ($efval['type']=='varchar') {
						$str .= iconv('utf-8', 'gb2312', "{$content}").",";
					}elseif($efval['type'] == 'dateint'){
						$str .= date('Y-m-d H:i:s',$content).",";
					}elseif($efval['type']=='text'){
						$content=str_replace("\r\n"," || ",$content);
						$str .= iconv('utf-8', 'gb2312', "{$content}").",";
					}else{
						$str .= $content.",";
					}
				}
				$str .=  "\r\n";
// 				$str.= "" . iconv('utf-8', 'gb2312', $val['client_name']) . "," . iconv('utf-8', 'gb2312', $val['contact_name']) . "," . iconv('utf-8', 'gb2312', $val['contact_phone']) . "," . iconv('utf-8', 'gb2312', $val['agreement_num']) . "," . iconv('utf-8', 'gb2312', $val['contract_num']) . "," . date("Y-m-d H:i:s", $val['create_tm']) . "\r\n";
			}
		}
		echo $str;
		exit;
	}
	/*
	 * 众测产品申请人数管理阈值系数
	 * 2016-02-17
	 */
	public function applyModulus(){
		$tid = intval($_REQUEST['tid']);
		if (isset($_POST['action']) && trim($_POST['action'])=='applyModulus' && isset($_POST['modulus'])) {
			$modnum = intval($_POST['modulus']) > 0 ? intval($_POST['modulus']) : 9;
			$ret = $this->producmastertmodel->query("UPDATE $this->zhiyoo_product_apply_count SET `modulus`='$modnum' WHERE `tid` = '$tid'");
			
			if($ret!==false){
				$this -> writelog('众测产品申请人数管理阈值系数tid为'.$tid.'状态阈值系数改为'.$modnum,$this->zhiyoo_product_apply_count,$tid,__ACTION__ ,"","edit");
				$this->success("阀值系数更改成功！");
			}else{
				$this -> writelog('修改众测产品申请人数管理阈值系数失败tid为'.$tid,$this->zhiyoo_product_apply_count,$tid,__ACTION__ ,"","edit");
				$this->error("参数有误！");
			}
			
		}
		$modulus = $this->productmodel->table($this->zhiyoo_product_apply_count)->field('modulus')->where(array('tid'=>$tid))->find();
		$m_num = ($modulus && isset($modulus['modulus']) && $modulus['modulus']) ? $modulus['modulus'] : 9;
		$this -> assign('modulus',$m_num);
		$this -> assign('tid',$tid);
		$this -> display();
	}
	/*
	 * 众测产品申请驳回
	 * 2016-03-10
	 */
	function applyReject(){
		$id = intval($_REQUEST['id']);
		$tid = intval($_REQUEST['tid']);
		$this -> assign('id',$id);
		$this -> assign('tid',$tid);
		$this -> display();
	}
	
	public function productPer(){
		$rank = $_GET['rank'];
		$selectwhere = array();
		
		//获取typeid
		$fidthreadclass = $this->bbsmodel->table($this->bbs_threadclass)->field('typeid,fid')->where(array('name'=>$this->bbs_product_name))->select();
		$typeidarr = $typeidfidarr = array();
		foreach ($fidthreadclass as $typeidval){
			$typeidarr[] = $typeidval['typeid'];
			$typeidfidarr[] = $typeidval['fid'];
		}
		
		$selectwhere['typeid'] = array('in',$typeidarr);
		$selectwhere['displayorder'] = array('egt',0);
		$selectwhere['sortid'] = array('gt',0);
		if($typeidfidarr) $selectwhere['fid'] = array('in',$typeidfidarr);
		//获取所有TID
		$threadlist = $this->bbsmodel->table($this->bbstabal)->where($selectwhere)->field('tid')->select();
		
		// 获取分类信息设置项-申请时间对应ID
		$typeval = $this->bbsmodel->table($this->bbs_forum_typeoption)->where("identifier ='application_time'")->field("optionid")->find();
		$optionid = $typeval['optionid'];
		
		//查询产品列表
		$tid2applytime = array();
		foreach ($threadlist as $key => $tval){
			//增量插入TIDS表
			//$result = $this->producttidmodel->where(array('tid'=>$tval['tid']))->find();
			$result = $this->slavemodel->table('zy_product_tid')->where(array('tid'=>$tval['tid']))->find();
			if(empty($result)){
				$this->producttidmodel->add(array('tid'=>$tval['tid']));
			}
			//获取配置信息
			$optionvar = $this->bbsmodel->table($this->bbs_option)->where(array('tid'=>$tval['tid'],'optionid'=>$optionid))->field('value')->find();
			$tid2applytime[$tval['tid']] = strtotime($optionvar['value']);
		}
		
		$result = $this->slavemodel->table('zy_product_per')->where('status >= 0')->order('dateline asc')->select();
		$i = 1;//期数计数
		$reslist = array();
		foreach ($result as $key => $val){
			$count = 0;
			foreach ($tid2applytime as $tid => $time){
				if($time <= $val['dateline']) {
					unset($tid2applytime[$tid]);
					$count++;
				}
			}
			$val['count'] = $count;
			$val['daily'] = '第 '.$i.' 期大众评测';
			$rank == 'asc' ? array_push($reslist,$val) : array_unshift($reslist,$val);
			$i++;
		}
		$last = array();
		$last['count'] = count($tid2applytime);
		$last['daily'] = '第 '.$i.' 期大众评测';
		$rank == 'asc' ? array_push($reslist,$last) : array_unshift($reslist,$last);
		
		$this -> assign('reslist',$reslist);
		$rank = $rank == 'asc' ? 'desc' : 'asc';
		$this -> assign('rank',$rank);
		$this -> display();
	}
	
	public function adddateline(){
		$id = intval($_GET['id']);
		$this -> assign('id',$id);
		$this -> display();
	}
	
	public function deldateline(){
		$id = intval($_GET['id']);
		$res = $this->productpermodel->where(array('id'=>$id))->save(array('status'=>-1));
		if($res !== false){
			$this -> writelog("智友内容管理-产品管理-产品分期管理 删除id为{$id}的时间点",'zy_product_per',$id,__ACTION__ ,"","del");
			$this -> success("时间点删除成功");
		}else{
			$this -> error('时间点删除失败');
		}
	}
	
	public function editdateline(){
		$id = intval($_GET['id']);
		if(!$id){
			$this -> error('ID错误');
		}
		$timep = $this->productpermodel->datelinebyid($id);
		$timep = date("Y-m-d H:i:s",$timep);
		$this -> assign('id',$id);
		$this -> assign('timep',$timep);
		$this -> display('adddateline');
	}
	
	public function doadddateline(){
		$id = intval($_GET['id']);
		$dateline = strtotime($_POST['timep']);
		if(!$dateline)$this -> error('时间点格式错误');
		$text = '添加';
		if($id){
			$timep = $this->productpermodel->datelinebyid($id);
			if($dateline == $timep){//时间点未修改
				$this -> success("时间点未修改");
			}
			$text = '编辑';
		}
		//重复的时间点检测
		$result = $this->slavemodel->table('zy_product_per')->where(array('dateline'=>$dateline,'status'=>1))->find();
		if(!empty($result)){
			$this -> error('不能添加重复的时间点');
		}
		//前后时间点检测是否有产品
		//获取前后时间点
		if($id){
			$predateline = $this->slavemodel->table('zy_product_per')->where(array('id'=>array('neq',$id),'dateline'=>array('elt',$dateline),'status'=>1))->max('dateline');
			$nextdateline = $this->slavemodel->table('zy_product_per')->where(array('id'=>array('neq',$id),'dateline'=>array('gt',$dateline),'status'=>1))->min('dateline');
			
		}else{
			$predateline = $this->slavemodel->table('zy_product_per')->where(array('dateline'=>array('elt',$dateline),'status'=>1))->max('dateline');
			$nextdateline = $this->slavemodel->table('zy_product_per')->where(array('dateline'=>array('gt',$dateline),'status'=>1))->min('dateline');
		}
		
		if(empty($predateline)) $predateline = 0;
		$pretidbool = $nextbool = false;//时间点前后是否有产品
		
		//获取所有TID
		//$threadlist = $this->producttidmodel->select();
		$threadlist = $this->slavemodel->table('zy_product_tid')->select();
		
		// 获取分类信息设置项-申请时间对应ID
		$typeval = $this->bbsmodel->table($this->bbs_forum_typeoption)->where("identifier ='application_time'")->field("optionid")->find();
		$optionid = $typeval['optionid'];
		
		$nexttidstatus = $pretidstatus = 0;//至少保留一项为启用状态
		//查询产品列表
		foreach ($threadlist as $key => $tval){
			//获取配置信息
			$optionvar = $this->bbsmodel->table($this->bbs_option)->where(array('tid'=>$tval['tid'],'optionid'=>$optionid))->field('value')->find();
			$applytime = strtotime($optionvar['value']);
			if(empty($nextdateline)){//最后一个时间点
				if($applytime > $dateline){
					$nextbool = true;
					$nexttidstatus = +$tval['status'];
				}
			}else{
				if($applytime > $dateline && $applytime <= $nextdateline){
					$nextbool = true;
					$nexttidstatus += $tval['status'];
				}
			}
			if($applytime <= $dateline && $applytime > $predateline){
				$pretidbool = true;
				$pretidstatus += $tval['status'];
			}
			// if($pretidbool && $nextbool) break; //符合条件直接跳出
		}
		if(!$pretidbool) $this -> error("时间点与之前的时间点之间没有产品，{$text}失败！");
		if(!$nextbool) $this -> error("时间点与之后的时间点之间没有产品，{$text}失败！");
		if(!$pretidstatus) $this -> error("时间点与之前的时间点之间没有启用的产品，{$text}失败！");
		if(!$nexttidstatus) $this -> error("时间点与之后的时间点之间没有启用的产品，{$text}失败！");

		if($id){
			$res = $this->productpermodel->where(array('id'=>$id))->save(array('dateline'=>$dateline));
		}else{
			$res = $this->productpermodel->add(array('dateline'=>$dateline));
		}

		if($res !== false){
			$this -> writelog("智友内容管理-产品管理-产品分期管理 {$text}id为{$id}的时间点",'zy_product_per',$id,__ACTION__ ,"","edit");
			$this -> success("时间点{$text}成功");
		}else{
			$this -> error("时间点{$text}失败");
		}
	}
	
	/*
	 * 产品分期管理列表
	 */
	public function listfromdate(){
		$id = intval($_GET['id']);
		//获取分类信息
		$threadtypelist = $this->product_type();
		
		// 获取分类信息设置项
		$Where = "identifier in ('p_count','release_time','application_time','experience_time','end_time')";
		$field = "optionid,classid,title,identifier";
		// 获取分类信息设置项
		$typeoptionret = $this->forum_typeoption($Where,$field);
		
		$typeoptionid = $typeidarr = array();
		$optionid = '';
		foreach ($typeoptionret as $typekey => $typeval){
			$typeoptionid[$typeval['optionid']]['optionid'] = $typeval['optionid'];
			$typeoptionid[$typeval['optionid']]['title'] = $typeval['title'];
			$typeoptionid[$typeval['optionid']]['identifier'] = $typeval['identifier'];
			$typeoptionid[$typeval['optionid']][$typeval['identifier']] = $typeval['optionid'];
			$typeidarr[$typeval['identifier']] = $typeval['optionid'];
			$optionid .= $typeval['optionid'].',';
		}
		$optionid = substr($optionid,0,-1);
		
		//获取所有TID
		//$tids = $this->producttidmodel->select();
		$tids = $this->slavemodel->table('zy_product_tid')->select();
		$tidarr = array();
		if($id){//有时间点的
			$dateline = $this->productpermodel->datelinebyid($id);
			$predateline = $this->slavemodel->table('zy_product_per')->where(array('dateline'=>array('lt',$dateline),'status'=>1))->max('dateline');
			if(empty($predateline)) $predateline = 0;
			
			foreach ($tids as $tid){
				$optionvar = $this->bbsmodel->table($this->bbs_option)->where(array('tid'=>$tid['tid'],'optionid'=>$typeidarr['application_time']))->field('value')->find();
				$applytime = strtotime($optionvar['value']);
				if($applytime <= $dateline && $applytime > $predateline){
					$tidarr[] = $tid['tid'];
					$tidstatus[$tid['tid']] = $tid['status'];
				}
			}
		}else{//最后一期
			$dateline = $this->slavemodel->table('zy_product_per')->where(array('status'=>1))->max('dateline');
			foreach ($tids as $tid){
				$optionvar = $this->bbsmodel->table($this->bbs_option)->where(array('tid'=>$tid['tid'],'optionid'=>$typeidarr['application_time']))->field('value')->find();
				$applytime = strtotime($optionvar['value']);
				if($applytime > $dateline){
					$tidarr[] = $tid['tid'];
					$tidstatus[$tid['tid']] = $tid['status'];
				}
			}
		}
		if(empty($tidarr)) $this -> error("获取产品列表失败");
		$selectwhere = array('tid'=>array('in',$tidarr));
		
		//至少保留一项为启用状态
		$disable = array_sum($tidstatus);
		// 分页
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $this->bbsmodel-> table($this->bbstabal)->where($selectwhere)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		
		//查询产品列表
		$fieldstr = 'tid,fid,typeid,sortid,subject,dateline';
		$optionfield = 'optionid,value';
		$threadlist = $this->bbsmodel->table($this->bbstabal)->where($selectwhere)->field($fieldstr)->order("tid DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($threadlist as $key => $tval){
			$applycount = $this->productmodel->table($this->zhiyoo_product_apply)->where(array('tid'=>$tval['tid']))->count();
			$threadlist[$key]['count'] = $applycount;
			$testcount = $this->productmodel->table($this->zhiyoo_test_content)->where(array('productid'=>$tval['tid']))->count();
			$threadlist[$key]['test_count'] = $testcount;
			
			//获取申请人数阀值系数
			$m_num = $showtotal = '';
			if ($applycount>18) {
				$modulus = $this->productmodel->table($this->zhiyoo_product_apply_count)->field('applycount,modulus')->where(array('tid'=>$tval['tid']))->find();
				$m_num = ($modulus && isset($modulus['modulus']) && $modulus['modulus']) ? $modulus['modulus'] : 9;
				$m_applycount = ($modulus && isset($modulus['applycount']) && $modulus['applycount']) ? $modulus['applycount'] : $applycount;
				$showtotal = $m_applycount*$m_num + intval(substr($tval['tid'],-2)) + intval(substr($tval['dateline'], -1));
			}
			$threadlist[$key]['modulus'] = $m_num;
			$threadlist[$key]['showtotal'] = $showtotal;
			
			
			//获取配置信息
			$optionvar = $this->bbsmodel->table($this->bbs_option)->where(array('tid'=>$tval['tid'],'optionid'=>array('in',$optionid)))->field($optionfield)->select();
			$opidarr = array();
			foreach ($optionvar as $opval){
				$opidarr[$opval['optionid']] = $opval['value'];
			}

			$p_count = $release_time = $application_time = $experience_time = $end_time = $statusstr = '';
			if ($optionvar) {
				$p_count = $opidarr[$typeidarr['p_count']];
				$release_time = $opidarr[$typeidarr['release_time']];
				$application_time = $opidarr[$typeidarr['application_time']];
				$experience_time = $opidarr[$typeidarr['experience_time']];
				$end_time = $opidarr[$typeidarr['end_time']];
				
				$statusstr = $this->statusValue($release_time,$application_time,$experience_time,$end_time);
			}
			
			$threadlist[$key]['p_count'] = $p_count;
			$threadlist[$key]['release_time'] = $release_time;
			$threadlist[$key]['application_time'] = $application_time;
			$threadlist[$key]['experience_time'] = $experience_time;
			$threadlist[$key]['end_time'] = $end_time; 
			$threadlist[$key]['status'] = $statusstr;
			$valfid = $tval['fid'];
			$forumtypename = $this->forum_type_name($valfid,$threadtypelist['type']);
			$threadlist[$key]['typename'] = $forumtypename;
		}
		
		// 分页
		$pageList = $Page->show();
		
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$this->assign('pageList',$pageList);
		
		$this -> assign('threadtype',$threadtypelist['list']);
		$this -> assign('threadlist',$threadlist);
		$this -> assign('tidstatus',$tidstatus);
		$this -> assign('disable',$disable);
		$this -> display();
	}
	
	/*
	 * 产品分期管理列表-改变产品显示状态
	 */
	public function changestatus_ptid(){
		$tid = intval($_GET['tid']);
		$status = intval($_GET['status']);
		
		$res = $this->producttidmodel->status($tid,$status);
		
		if($res !== false){
			$this -> writelog("智友内容管理-产品管理-产品分期管理 tid为{$tid}的状态改变为{$status}",'zy_product_per',$tid,__ACTION__ ,"","edit");
			$this -> success("状态改变成功");
		}else{
			$this -> error("状态改变失败");
		}
	}
	
	/*
	 * 添加申请用户
	 */
	public function applyAdd(){
		$tid = intval($_GET['tid']);
		
		$this -> assign('tid',$tid);
		$this -> display();
	}
	
	public function checkUser(){
		$data = array('ok'=>0);
		$username = trim($_POST['username']);
		$res = $this->bbsmodel->table('x15_common_member')->where(array('username'=>$username))->field('uid')->find();
		$uid = $res['uid'];
		if($uid)$data = array('ok'=>1,'uid'=>$uid);
		echo json_encode($data);
	}
	
	public function doapplyAdd(){
		$tid = intval($_GET['tid']);
		$bbs_common_filed = array('1'=>'extcredits2','2'=>'extcredits4');
		$username = trim($_POST['username']);
		if(empty($username)) $this -> error("用户名不存在！");
		$res = $this->bbsmodel->table('x15_common_member')->where(array('username'=>$username))->field('uid')->find();
		if(empty($res)) $this -> error("用户名不存在！");
		$names = trim($_POST['names']);
		$nameslen = mb_strlen($names,'UTF8');
		if($nameslen<2 || $nameslen>10) $this -> error("名字字数不得少于2字，最多10字！");
		$address = trim($_POST['address']);
		$addresslen = mb_strlen($address,'UTF8');
		if($addresslen<5 || $addresslen>100) $this -> error("联系地址字数不得少于5字，最多100字！");
		$tel = trim($_POST['tel']);
		if(!preg_match("/^1[1-9][0-9]{9}$/i",$tel)) $this -> error("手机号格式错误！");
		$qq = trim($_POST['qq']);
		if(!preg_match("/^[1-9][0-9]{4,12}$/i",$qq)) $this -> error("QQ号格式错误！");
		
		$res = $this->productmodel->table($this->zhiyoo_product_apply_count)->where(array('tel'=>$tel,'tid'=>$tid,'status'=>array('egt',0)))->count();
		if($res){
			$this -> error("该手机号已申请过该产品！");
		}
		
		$res = $this->bbsmodel->table('x15_forum_typeoption')->where(array('identifier'=>$this->statusTime(2)))->field('optionid')->find();
		$opid_time = $res['optionid'];
		$res = $this->bbsmodel->table('x15_forum_typeoptionvar')->where(array('tid'=>$tid,'optionid'=>$opid_time))->field('value')->find();
		$opid_stime =strtotime( $res['value']);
		$res = $this->bbsmodel->table('x15_forum_typeoption')->where(array('identifier'=>$this->statusTime(3)))->field('optionid')->find();
		$opid_time = $res['optionid'];
		$res = $this->bbsmodel->table('x15_forum_typeoptionvar')->where(array('tid'=>$tid,'optionid'=>$opid_time))->field('value')->find();
		$opid_etime =strtotime( $res['value']);
		if(time()<$opid_stime || time()>$opid_etime){
			$this -> error("该产品状态已不在申请中，请查看其它产品！");
		}
		
		$res = $this->bbsmodel->table('x15_common_member')->where(array('username'=>$username))->field('uid')->find();
		$uid = $res['uid'];
		$data = array('tid'=>$tid,'uid'=>$uid,'username'=>$username,'names'=>$names,'address'=>$address,'tel'=>$tel,'qq'=>$qq);
		//获取所需
		$res = $this->bbsmodel->table('x15_forum_typeoption')->where(array('identifier'=>'p_condition'))->field('optionid')->find();
		$opid_con = $res['optionid'];
		$res = $this->bbsmodel->table('x15_forum_typeoptionvar')->where(array('tid'=>$tid,'optionid'=>$opid_con))->field('value')->find();
		$data['type'] = $condition = $res['value'];
		$res = $this->bbsmodel->table('x15_forum_typeoption')->where(array('identifier'=>'c_count'))->field('optionid')->find();
		$opid_cc = $res['optionid'];
		$res = $this->bbsmodel->table('x15_forum_typeoptionvar')->where(array('tid'=>$tid,'optionid'=>$opid_cc))->field('value')->find();
		$data['credit'] = $count = $res['value'];
		
		
		$res = $this->bbsmodel->table('x15_common_member_count')->where(array('uid'=>$uid))->field($bbs_common_filed[$condition])->find();
		$user_count = $res[$bbs_common_filed[$condition]];
		if($user_count < $count){
			$this -> error("用户金币/智豆不足！");
		}
		
		$res = $this->bbsmodel->table('x15_forum_thread')->where(array('tid'=>$tid))->field('fid,posttableid')->find();
		$data['fid'] = $fid = $res['fid'];
		$data['gid'] = $this->getforumgid($fid);
		$res = $this->bbsmodel->table('x15_forum_threadclass')->where(array('fid' => $fid,'name'=>$this->bbs_product_name))->field('typeid')->find();
		$data['ptypeid'] = $res['typeid'];
		
		$data['email'] = trim($_POST['email']);
		$data['content'] = trim($_POST['content']);
		$data['pasturl'] = trim($_POST['pasturl']);
		$data['pasturl2'] = trim($_POST['pasturl2']);
		$data['pasturl3'] = trim($_POST['pasturl3']);
		
		//验证信息
		$salt = 'EW22di9dREW9Q#';
		$data['tm'] = time();
		$data['auth'] = md5($data['tm'].$salt);
		
		$ch = curl_init();
		curl_setopt_array($ch,array(
			CURLOPT_URL => 'http://pingce.zhiyoo.com/api/product_apply_add.php',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 10
		));
		$res = curl_exec($ch);
		curl_close($ch);
		$res = json_decode($res,1);
		if($res && $res['CODE'] == 200 && $res['DATA'] > 0){
			$this -> writelog("智友内容管理-产品管理-添加申请用户 ，众测产品tid为".$tid."，添加id为{$res['DATA']}的申请信息",'x15_forum_threadclass',$res['DATA'],__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Zhiyoo/Product/applyList/tid/{$tid}/pnum/{$_GET['pnum']}/applyAdd/1/");
			$this -> success("添加成功");
			
		}else{
			$this -> error("添加失败！");
		}
	}
	
	private function getforumgid($fid){
		$res = $this->bbsmodel->table('x15_forum_forum')->where(array('fid'=>$fid))->field('fup')->find();
		if($res['fup'] == 0) {
			return $fid;
		}else{
			return $this->getforumgid($res['fup']);
		}
	}
	
	function product_act(){
		$map['identifier'] = array('in',array('experience_time','application_time'));
		$options = $this->bbsmodel->table($this->bbs_forum_typeoption)->where($map)->field('optionid,identifier')->select();
		foreach($options as $op){
			 $opval[$op['identifier']] = $op['optionid'];
			
		}
		$map = array();
		$map['optionid'] = array('in',array_values($opval));
		$options = $this->bbsmodel->table($this->bbs_option)->where($map)->field('optionid,value,tid')->select();
		foreach($options as $op){
			if($opval['experience_time'] == $op['optionid']){				
				$thread[$op['tid']]['experience_time'] = strtotime($op['value']);
			}else{
				$thread[$op['tid']]['application_time'] = strtotime($op['value']);
			}
		}
		$now = time();
		foreach($thread as $tid =>$optime){
			if($optime['application_time'] < $now && $optime['experience_time'] >= $now){
				$tinfo = $this->bbsmodel->table($this->bbstabal)->where("tid={$tid} and displayorder>=0")->field('subject,fid')->find();
				if(empty($tinfo)) continue;
				$applications[$tid]['subject'] = $tinfo['subject'];
				$applications[$tid]['fid'] = $tinfo['fid'];
				$applications[$tid]['application_time'] = date('Y-m-d H:i:s',$optime['application_time']);
				$applications[$tid]['experience_time'] = date('Y-m-d H:i:s',$optime['experience_time']);
				$zyinfo = $this->slavemodel->table($this->zyactivity)->where('tid='.$tid)->find();
				$applications[$tid]['starttime'] = $zyinfo['starttime'] ?date('Y-m-d H:i:s',$zyinfo['starttime']) :'';
				$applications[$tid]['endtime'] = $zyinfo['endtime']?date('Y-m-d H:i:s',$zyinfo['endtime']):'';
				$applications[$tid]['status'] = $zyinfo['status'] ? $zyinfo['status'] : 0;
				$applications[$tid]['tid'] = $tid;
			}
			
		}
		$this->assign('threads',$applications);
		$this->display();
		
	}
	
	function change_act_status(){
		$status = $_GET['status'] == 0 ? 1 : 0;
		$tid = $_GET['tid'];
		$res = $this->slavemodel->table($this->zyactivity)->where("tid={$tid}")->find();
		if(!$res){
			$this->error('请先配置活动时间');
		}else{
			$this->model->table($this->zyactivity)->where("tid={$tid}")->save(array('status'=>$status));
			$this->writelog("智友内容管理-产品管理-活动配置 修改了众测产品tid为{$tid}的活动状态为{$status}",$this->zyactivity,$tid,__ACTION__ ,"","edit");
			$this->success('活动状态修改成功');
		}
		
	}
	
	function change_act_time(){
		
		$tid = $_GET['tid'];
		$res = $this->slavemodel->table($this->zyactivity)->where("tid={$tid}")->find();
		$starttime = $endtime = '';
		if($res){
			$starttime = date("Y-m-d H:i:s",$res['starttime']);
			$endtime = date("Y-m-d H:i:s",$res['endtime']);
		}
		if($_POST['actsubmit']){
			$start = strtotime($_POST['start']);
			$end = strtotime($_POST['end']);
			if(!$start || !$end){
				$this->error('请配置正确的时间');
			}
			if($start>$end){
				$this->error('开始时间必须小于结束时间');
			}
			
			$map['identifier'] = array('in',array('experience_time','application_time'));
			$options = $this->bbsmodel->table($this->bbs_forum_typeoption)->where($map)->field('optionid,identifier')->select();
			foreach($options as $op){
				 $opval[$op['identifier']] = $op['optionid'];
				 $opkeyval[$op['optionid']] = $op['identifier'];
			}
			$map = array();
			$map['optionid'] = array('in',array_keys($opkeyval));
			$map['tid'] = $tid;
			$info = $this->bbsmodel->table($this->bbs_option)->field('value,optionid')->where($map)->select();
			foreach($info as $val){
				$thread[$opkeyval[$val['optionid']]] = strtotime($val['value']);
			}
			if($start<$thread['application_time'] || $start>$thread['experience_time']){
				$this->error('开始时间大于产品申请时间，小于产品体验时间');
			}
			
			
			$data['starttime'] = $start;
			$data['endtime'] = $end;
			if($res){
				$this->model->table($this->zyactivity)->where('tid='.$tid)->save($data);	
			}else{
				$data['tid'] = $tid;
				$data['status'] = 0;
				$this->model->table($this->zyactivity)->add($data);
			}
				// echo $this->model->getLastSql();
				// die;
			$this->writelog("智友内容管理-产品管理-活动配置 修改了众测产品{$tid}的活动开始结束时间配置",$this->zyactivity,$tid,__ACTION__ ,"","edit");
			$this->success("活动配置成功");
		
		}else{			
			$this->assign('tid',$tid);
			$this->assign('end',$endtime);
			$this->assign('start',$starttime);
			$this->display();
		}
	}
	
	function product_text(){
		$share = $this->slavemodel->table($this->sharetext)->select();
		foreach($share as $val){
			$list[$val['id']] = $val;
		}
		$this->assign('share',$list);
		$this->display();	
	}
	
	function text_conf(){
		$id = $_GET['id'];
		$share = $this->slavemodel->table($this->sharetext)->where('id='.$id)->find();
		if($_POST['formsubmit']){
			$text = trim($_POST['text']);
			if(!$text){
				$this->error('没有配置分享文字');
			}else{
				$data['share_text'] = $text;
				$data['id'] = $id;
				if($share){
					$this->model->table($this->sharetext)->save($data);
				}else{
					$this->model->table($this->sharetext)->add($data);
				}
				$this->writelog("智友内容管理-产品管理-活动配置 修改了{$id}的分享文案配置",$this->sharetext,$id,__ACTION__ ,"","edit");
				$this->success('分享文案配置成功');
			}
			
		}else{
			$text = '';
			if($share){
				$text = $share['share_text'];
			}
			$this->assign('text',$text);
			$this->assign('id',$id);
			$this->display();
			
		}
		
		
	}
    
    public function editRank(){
        foreach($_POST['rank'] as $key =>$val){
            if($val!==''&&$val>=0){            
                $data['tid']=$key;
                $data['displayorder'] = $val>9999 ? 9999:$val;
                $logtid .= $key.',';
                $this->producttidmodel->save($data);
            }
        }
        $logtid = substr($logtid,0,-1);
        $this -> writelog('编辑众测产品排序,tid为'.$logtid,$this->zhiyoo_test_content,$logtid,__ACTION__ ,"","edit");      
        $this->assign("jumpUrl","__URL__/productList/"); 
        $this->success(" 编辑成功 " ); 
    }
    
     public function delReport(){
        $reportid =(int) $_GET['reportid'];
        if($reportid<=0) $this->error('指定的报告不存在，删除失败');
        
        $model = D("Zhiyoo.TestContent");
        $res = $model-> where(array('tid'=>$reportid))->delete();
        if($res){
            $this -> writelog('众测报告删除，tid为'.$reportid,$this->zhiyoo_test_content,$reportid,__ACTION__ ,"","delete");           
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }
    
    public function addReport(){
        $productid = $_REQUEST['productid'] ;
        if($_POST['addsubmit']){
            $tid = (int)$_POST['addtid'];
            $rank = (int)$_POST['addrank'];
            $rank =  $rank ? $rank : 1;
            if($tid<0) $this->error('请输入整数tid');
            $model = D('Zhiyoo.TestContent');
            $exist = $this->productmodel->table($this->zhiyoo_test_content)->where(array('tid'=>$tid))->find();
            if($exist) $this->error('该主题已经添加为众测报告');
            $thread = $this->bbsmodel->table($this->bbstabal)->where(array('tid'=>$tid))->find();
            if(!$thread) $this->error('tid不存在');
            if($thread['displayorder']<0) $this->error('该主题正在审核或已经删除，无法添加');
            $data['tid'] = $tid;
            $data['typeid'] = $thread['typeid'];
            $data['rank'] = $rank;
            $data['fid'] = $thread['typeid'];
            $data['productid'] = $_POST['productid'];
            $data['uid'] = $thread['authorid'];
            $data['username'] = $thread['author'];
            $data['ip'] = $_SERVER['SERVER_ADDR'];
            $data['dateline'] = $thread['dateline'];
            $data['posttableid'] = $thread['posttableid'];
            $data['title'] = $thread['subject'];
            $data['status'] = $thread['status'];
            $data['modifytime'] = $thread['dateline'];
            $data['gid'] = $this->get_report_gid($thread['fid']);
            $model->add($data);
            $this -> writelog('众测报告已经成功添加，众测产品tid为'.$data['productid'].'，众测报告tid为'.$tid,$this->zhiyoo_test_content,$tid,__ACTION__ ,"","add");
            $this->success('添加成功');
        }else{            
            $this->assign('productid',$productid);
            $this->display();
        }
    }
    
    //获取专区的gid
    private function get_report_gid($fid){
        $res = $this->bbsmodel->table($this->bbs_forum)->where(array('fid'=>$fid))->field(array('fup'))->find();
        $resfup = $this->bbsmodel->table($this->bbs_forum)->where(array('fid'=>$res['fup']))->field(array('fup'))->find();
        if($resfup['fup'] == 0){
            return $res['fup'];
        }else{
            return $resfup['fup'];
        }
        
    }
    
    public function awardShow(){
        $editrank = intval($_GET['editrank'])? 1:0;
        $tid = $_REQUEST['tid'];
        $listmodel = D('Zhiyoo.AwardList');
        $usermodel = D('Zhiyoo.AwardUser');
        $bbsmodel = D('Zhiyoo.bbs');
        $product = $bbsmodel->table('x15_forum_thread')->field(array('subject'=>'title'))->where(array('tid'=>$tid))->find();
        //如果众测产品已经过期，则该产品奖励不能编辑
        $op = $bbsmodel->table('x15_forum_typeoption')->field(array('optionid'))->where(array('identifier'=>'end_time'))->find();
        $endtime = $bbsmodel->table('x15_forum_typeoptionvar')->field(array('value','tid'))->where(array('optionid'=>$op['optionid'],'tid'=>$tid))->find();
        $end = 0;
        if(strtotime($endtime['value'])<time()){
            $end = 1;
        }
        
        $awardlist = $listmodel->where(array('tid'=>$tid,'status'=>1))->order(array('rank'))->select();        
        foreach($awardlist as $key => $val){
            $users = $usermodel->where(array('aid'=>$val['id']))->order(array('rank'))->select();
            
            foreach($users as $u){                
                $awardlist[$key]['users'][$u['uid']] =$u['username'];
            }
            
        }
        $this->assign('list',$awardlist);
        $this->assign('product',$product);
        $this->assign('tid',$tid);
        $this->assign('editrank',$editrank);
        $this->assign('end',$end);
        $this->display();
    }
    
    public function awardAdd(){
        $tid = $_REQUEST['tid'];
        $productmodel = D('Zhiyoo.Product_slave');
        $applys = $productmodel->table('zy_product_apply')->where(array('tid'=>$tid,'status'=>2))->field(array('uid','username'))->group('username')->select();
                

        $listmodel = D('Zhiyoo.AwardList');
        $usermodel = D('Zhiyoo.AwardUser');
        $paward_res = $listmodel->where(array('tid'=>$tid,'status'=>1))->select();
        foreach($paward_res as $val){
            $paward[$val['id']] = $val;
        }
        $ids = array_keys($paward);
        $users = $usermodel->where(array('aid'=>array('in',$ids)))->order(array('rank'))->select();
        $userlists = $awarduser = array();
        foreach($users as $val){
            $userlists[$val['aid']][$val['uid']] = $val;
            $awarduser[] = $val['uid'];
        }
        
        if($id = $_REQUEST['id']){
            $awardlist = $paward[$id];
            $awardlist['users'] = $userlists[$id];
            
        }
        if($_POST['addsubmit']){
            
            $data['name'] = trim($_POST['name']);
            $data['award'] = trim($_POST['award']);
            $data['info'] = trim($_POST['info']);
            if(!$data['name']){
                $this->error('请填写奖品名称');
            }
            if(!$data['award']){
                $this->error('请填写奖品信息');
            }
            if(!$data['info']){
                $this->error('请填写通知信息');
            }
            if(empty($_POST['rank'])){
                $this->error('请填写获奖者名单');
            }
            $data['tid'] = $tid;
            if($id){
                $aid = $data['id'] = $id;
                $listmodel->save($data);
            }else{
                $aid = $listmodel->add($data);
               
            }
            if($aid){
                // var_dump($_POST['rank']);
                if($id){
                    $usermodel->where(array('aid'=>$aid))->delete();
                }
                foreach($_POST['rank'] as $key=>$val){
                    $udata = $productmodel->table('zy_product_apply')->where(array('uid'=>$key))->field(array('uid','username'))->group('username')->find();
                    $udata['aid'] = $aid;
                    $udata['rank'] = $val;
                    $usermodel->add($udata);
                }
                $table = $listmodel->getTableName()."\n".$usermodel->getTableName();
                if($id){
                    $this -> writelog('众测奖品编辑成功，众测产品tid为'.$tid.'，id为'.$id,$table,$id,__ACTION__ ,"","edit");
                    $this->success('编辑成功');
                }else{
                    $this -> writelog('众测奖品添加成功，众测产品tid为'.$tid.'，id为'.$aid,$table,$aid,__ACTION__ ,"","add");
                    $this->sendInfo($tid,$aid,1);
                    $this->success('添加成功');
                }
            }else{
                $this->error('添加失败');
                
            }
            
            
        }else{
            $this->assign('list',$awardlist);
            $this->assign('applys',$applys);
            $this->assign('tid',$tid);
            $this->assign('id',$id);
            $this->assign('awarduser',$awarduser);
            $this->display();
        }
        
    }
    
    public function awardDel(){
        $id = (int)$_GET['id'];
        $tid = (int)$_GET['tid'];
        if($id){
            $listmodel = D('Zhiyoo.AwardList');
            $listmodel->save(array('id'=>$id,'status'=>0));
            $this -> writelog('众测奖品删除成功，众测产品tid为'.$tid.'，id为'.$id,$listmodel->getTableName(),$id,__ACTION__ ,"","delete");
            $this->sendInfo($tid,$id,0);
            $this->success('删除成功');
            
        }
        $this->error('删除失败，奖项不存在');
        
    }
    
    public function awardRank(){
        $listmodel = D('Zhiyoo.AwardList');
        $ranks = $ids = '';
        $tid = $_POST['tid'];
        foreach($_POST['rank'] as $key => $val){
            $data['id']=$key;
            $data['rank'] = (int)$val;
            $listmodel->save($data);
            $ids .= $key.',';
            $ranks .= $val.',';
        }
        
        $ids = substr($ids,0,-1);
        $ranks = substr($ranks,0,-1);
        $this -> writelog('众测奖品编辑排序成功，众测产品tid为'.$tid.'，id为'.$ids.'排序为'.$ranks,$listmodel->getTableName(),$id,__ACTION__ ,"","delete");
        $this->assign('jumpUrl',"/index.php/Zhiyoo/Product/awardShow/tid/{$tid}/");
        $this->success('编辑排序成功');
    }
    
    
    
    protected function sendInfo($tid,$aid,$status){
        $res = $this->producmastertmodel->table('zy_award_notice')->where(array('aid'=>$aid))->find();
        $data['tid']=$tid;
        $data['aid']=$aid;
        $data['status']=$status;
        if($res){
            $this->producmastertmodel->table('zy_award_notice')->where(array('aid'=>$data['aid']))->save(array('status'=>$data['status']));
            // echo $this->producmastertmodel->getLastSql();die;
        }else{
            $this->producmastertmodel->table('zy_award_notice')->add($data);
        }
        
        
    }
}


