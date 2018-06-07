<?php
/**
 * 安智网产品管理平台 推荐管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络技术有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.5.16
 * ----------------------------------------------------------------------------
*/
class AdvertisementAction extends CommonAction {
	private $lists;             //列表
	private $conf_db;           //配置表
	private $conf_list;         //配置列表
	private $hashs;             //默认hashs
	private $map;               //条件
	private $soft_db;           //软件表
	private $soft_list;         //软件列表
	private $ad_zone_db;        //广告位
	private $ad_zone_list;      //广告位列表
	private $softid;            //软件id
	private $sid;               //临时ID
	private $returnurl;         //返回地址
	private $order;             //排序
	private $ad_db;             //广告实体表
	private $ad_list;           //广告实体列表
	private $feature_db;        //推荐位表
	private $feature_list;      //推荐位列表
	private $feature_soft_db;   //推荐软件表
	private $feature_soft_list; //推荐软件列表
	public function index() {
		exit;
	}
	//推荐管理_推荐位列表
	public function zone_list() {
		$this->ad_zone_db=M('ad_zone');
		import("@.ORG.Page");
		$util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : '1';
		$my_markets = isset($_GET['market_category']) ? $_GET['market_category'] : 1;
		$map = array(
			'pid' => $pid,
			'market_category' => $my_markets,
			'status' => 1
		);

		$count= $this->ad_zone_db->where($map)->count();
		$Page=new Page($count,15);
		$this->ad_zone_list=$this->ad_zone_db->where($map)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();

		//dump($this->soft_lack_list);
		$Page->setConfig('header','篇记录');
		$Page->setConfig('first','<<');
		$Page->setConfig('last','>>');
		$show =$Page->show();
		$this->assign ("page", $show );
		$market_category = array(1=>'首页',2=>'应用',3=>'游戏');
		$this -> assign('market_category',$market_category);
		
		$this -> assign('my_markets',$my_markets);
		$this->assign('zonelist',$this->ad_zone_list);
		$this->assign ("pid", $pid );
		$this->assign('product_list',$util->getProducts($pid));
		//dump($this->ad_zone_list);
		$this->display();

	}
	//推荐管理_推荐位添加_显示
	public function zone_add() {
		$util = D("Sj.Util");
		$pid = isset($_GET['pid']) ? $_GET['pid'] : '1';
		if($pid == 1){
			$market_category = isset($_GET['market_category']) ? $_GET['market_category'] : '1';
		}
		$this->assign('product_list',$util->getProducts($pid));
		$this->assign('pid',$pid);
		$this->assign('market_category',$market_category);
		$this->display();
	}
	//推荐管理_推荐位添加_提交
	public function zone_addit() {

		$this->ad_zone_list['aid']=$_POST['aid'];
		$this->ad_zone_list['pid']=$_POST['pid'];
		$this->ad_zone_list['market_category']=$_POST['market_category'];
		$this->ad_zone_list['adzone_name']=preg_replace('/[\s]+/','',$_POST['adzone_name']);
		$this->ad_zone_list['adzone_type']=preg_replace('/[\s]+/','',$_POST['adzone_type']);
		$this->ad_zone_list['note']=preg_replace('/[\s]+/','',$_POST['note']);
		$this->ad_zone_list['status']=1;
		$this->ad_zone_list['upload_time']=time();
		$this->ad_zone_list['last_refresh']=time();
		$this->ad_zone_list['orderid']=$_POST['orderid'];
		$this->ad_zone_list['admin_id'] = $_SESSION['admin']['admin_id'];

		if(empty($this->ad_zone_list['aid']) || empty($this->ad_zone_list['adzone_name']) ) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error("ID和名称内容为必选，请用心填写！");
		}


		$this->map['aid']=$this->ad_zone_list['aid'];

		$this->ad_zone_db=M('ad_zone');
		$count=$this->ad_zone_db->where($this->map)->count();
		if(!empty($count)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_add');
			$this->error("添加失败！，标识不可重复");
		}else
		{
			if($id=$this->ad_zone_db->add($this->ad_zone_list)) {
				$this->writelog('图片广告位配置：添加了ID为['.$id.'], 标识ID为['.$this->ad_zone_list['aid'].']的推荐位', 'sj_ad_zone', $id,__ACTION__ ,"","add");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
				$this->success("添加成功！");
			}else
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_add');
				$this->error("添加失败！");
			}
		}

	}
	//推荐管理_推荐位编辑_显示
	public function zone_edit() {
		$util = D("Sj.Util");

		$this->sid=$_GET['id'];

		if(empty($this->sid)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error("非法操作失败,如频繁出现，请联系管理员！");
		}
		$this->ad_zone_db=M('ad_zone');
		$this->map['id']=$this->sid;
		$this->ad_zone_list=$this->ad_zone_db->where($this->map)->select();

		$this->assign('product_list',$util->getProducts($this->ad_zone_list[0]['pid']));

		$this->assign('zonelist',$this->ad_zone_list[0]);
		$this->display();

	}
	//推荐管理_推荐位编辑_提交
	public function zone_editit() {

		$this->sid=$_POST['id'];

		if(empty($this->sid)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error("非法操作失败,如频繁出现，请联系管理员！");
		}

		$this->ad_zone_list['aid']=$_POST['aid'];
		$this->ad_zone_list['pid']=$_POST['pid'];
		$this->ad_zone_list['adzone_name']=preg_replace('/[\s]+/','',$_POST['adzone_name']);
		$this->ad_zone_list['adzone_type']=preg_replace('/[\s]+/','',$_POST['adzone_type']);
		$this->ad_zone_list['note']=preg_replace('/[\s]+/','',$_POST['note']);
		$this->ad_zone_list['last_refresh']=time();
		$this->ad_zone_list['orderid']=$_POST['orderid'];
		$this->ad_zone_list['upload_time']=time();
		$this->ad_zone_list['admin_id'] = $_SESSION['admin']['admin_id'];

		$this->map['aid']=$this->ad_zone_list['aid'];

		$this->ad_zone_db=M('ad_zone');
		$this->lists=$this->ad_zone_db->where($this->map)->field('id,aid')->select();

		if($this->lists[0]['id']!=$this->sid && !empty($this->lists)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error("编辑失败！标识不可重复");
		}else
		{
			$this->map='';
			$this->map['id']=$this->sid;
			$log = $this -> logcheck(array('id' =>$_POST['id']),'sj_ad_zone',$this->ad_zone_list,$this->ad_zone_db);
			if(false!==$this->ad_zone_db->where($this->map)->save($this->ad_zone_list)) {
				$this->writelog('图片广告位配置：编辑了ID为['.$this->sid.']的推荐位'.$log, 'sj_ad_zone', $this->sid,__ACTION__ ,"","edit");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
				$this->success("编辑成功！");
			}else
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
				$this->error("编辑失败！");
			}
		}

	}

	//推荐管理_推荐位停用启用
	public function zone_status() {

		$this->sid=$_GET['id'];
		$this->ad_zone_db=M('ad_zone');
		$this->map['last_refresh']=time();
		if($_GET['state']) {
			$this->map['status']=1;
			$this->writelog('图片广告位配置-启用ID为['.$this->sid.']的推荐位', 'sj_ad_zone', $this->sid,__ACTION__ ,"","edit");
		}else
		{
			$this->writelog('图片广告位配置-停用ID为['.$this->sid.']的推荐位', 'sj_ad_zone', $this->sid,__ACTION__ ,"","edit");
			$this->map['status']=0;
		}
		if(false!==$this->ad_zone_db->where(array('id'=>$this->sid))->save($this->map)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->success("操作成功");
		}else
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error('操作失败');
		}
	}
	//推荐管理_列表推荐软件列表
	public function zone_list_soft() {


		$this->sid=$_GET['id'];
		$match = $_GET['match'];
		$this->ad_zone_db=M('ad_zone');
		$this->map['adzone_type']=1;
		$this->map['status']=1;

		$this->ad_zone_list=$this->ad_zone_db->where($this->map)->field('id,aid,adzone_name')->select();

		$this->assign('zonelist',$this->ad_zone_list);



		if(empty($this->sid)) {
			$this->sid=$this->ad_zone_list[0]['id'];
		}

		$this->ad_db=M('ad');
		$soft_db = D('soft');
		$nowtime = time();

		for($i=0;$i<count($this->ad_zone_list);$i++) {
			if($match == 1){
				$map_ex = "`status` = 1 and `zid` = ".$this->ad_zone_list[$i]['id']." and endtime <= ".$nowtime;//过期软件 where
				$ad_softs    = $this->ad_db->where($map_ex)->order('begintime,endtime asc')->select(); //过期软件 list
			}else{
				$ad_softs = array();
				$map='';
				$map = "`status` = 1 and `zid` = ".$this->ad_zone_list[$i]['id']." and begintime <= ".$nowtime." and endtime >".$nowtime;   //正在推广  where
				$map_fut = "`status` = 1 and `zid` = ".$this->ad_zone_list[$i]['id']." and begintime > ".$nowtime;//即将推广 where
				$ad_soft_promotion = $this->ad_db->where($map)->order('begintime,endtime asc')->select();    //正在推广 或 即将推广的 软件 list
				if(count($ad_soft_promotion) > 0) $ad_softs = $ad_soft_promotion;
				$ad_soft_fut       = $this->ad_db->where($map_fut)->order('begintime,endtime asc')->select(); //即将推广的软件 list
				if(count($ad_soft_fut) > 0) $ad_softs = array_merge($ad_softs,$ad_soft_fut);
			}
			foreach($ad_softs as &$v){
				$map_soft = " package='{$v['package']}' and `status`=1 and `hide`=1 ";
				$softname = $soft_db->where($map_soft)->getField("softname");
				$softname = $softname?$softname:"已无此软件";
				$v['package_name'] = $v['package']."({$softname})";
			}

			$this->ad_list[$this->ad_zone_list[$i]['id']] = $ad_softs;

		}

		$this->assign('adlist',$this->ad_list);
		$this->assign('nowtime',$nowtime);
		$this->assign('thepid',$this->sid);
		$this->assign('WEBMARKETURL',__MARKETURL__);

		//dump($this->ad_zone_list);

		//dump($this->ad_list);

		$this->display();

	}


	//推荐管理_图片推荐软件列表
	//Edit By 黄文强 2012/10/11
	public function zone_list_img() {

		$this->sid=$_GET['id'];

		$pid = isset($_GET['pid']) ? $_GET['pid'] : '1';
		$my_markets = isset($_GET['market_category']) ? $_GET['market_category'] : 1;
		$this->ad_zone_db=M('ad_zone');
		$this->map['adzone_type']=2;
		$this->map['status']=1;
		$this->map['pid']=$pid;
		$this->map['market_category'] = $my_markets;
		
		//$market_category = isset($_GET['market_category']) ? $_GET['market_category'] : 1;
		$this->map['market_category']=$my_markets;
	
	
		$market_category = array(1=>'首页',2=>'应用',3=>'游戏');
		$this->ad_zone_list=$this->ad_zone_db->where($this->map)->field('id,aid,adzone_name,pid,market_category')->select();
	
		if($pid == 1){
			$this -> assign('my_markets',$my_markets);
			$this ->assign('market_category',$market_category);
		}
		$this->assign('zonelist',$this->ad_zone_list);

		if(empty($this->sid)) {
			$this->sid=$this->ad_zone_list[0]['id'];
		}
		$this->ad_db=M('ad');
		//$this->ad_list=$this->ad_db->where('zid='.$this->sid.' and status = 1')->select();

		$nowtime = time();
		$map = "`status` = 1 and `zid` = ".$this->sid." and begintime <= ".$nowtime." and endtime >".$nowtime;   //正在推广  where
		$map_ex = "`status` = 1 and `zid` = ".$this->sid." and endtime <= ".$nowtime;//过期软件 where
		$map_fut = "`status` = 1 and `zid` = ".$this->sid." and begintime > ".$nowtime;//即将推广 where
		$ad_softs = array();
		$ad_soft_promotion = $this->ad_db->where($map)->order('begintime,endtime asc')->select();    //正在推广 或 即将推广的 软件 list
		if(count($ad_soft_promotion) > 0) $ad_softs = $ad_soft_promotion;
		$ad_soft_expire    = $this->ad_db->where($map_ex)->order('begintime,endtime asc')->select(); //过期软件 list
		if(count($ad_soft_expire) > 0) $ad_softs = array_merge($ad_softs, $ad_soft_expire);
		$ad_soft_fut       = $this->ad_db->where($map_fut)->order('begintime,endtime asc')->select(); //即将推广的软件 list
		if(count($ad_soft_fut) > 0) $ad_softs = array_merge($ad_softs, $ad_soft_fut);
		$this -> ad_list = $ad_softs;

		$this->conf_db = M('feature');
		$this->conf_list=$this->conf_db->field('feature_id,name')->select();

		for($i=0;$i<count($this->ad_list);$i++) {
			for($j=0;$j<count($this->conf_list);$j++) {
				if($this->conf_list[$j]['feature_id']==$this->ad_list[$i]['featureid']) {
					$this->ad_list[$i]['feature']=$this->conf_list[$j]['name'];
				}
			}
		}


		$this->assign('adlist',$this->ad_list);
		$this -> assign('nowtime' , $nowtime);
		$this->assign('thepid',$this->sid);
		$this -> assign('pid',$pid);
		$util = D("Sj.Util");
		$this->assign('product_list',$util->getProducts($pid));
		$this->display();
	}
	//推荐管理_广告添加_显示
	//Edit By 黄文强 2012/10/10
	public function zone_ad_add() {

		$this->returnurl=$_GET['returnurl'];
		if(empty($this->returnurl)) {
			$this->returnurl='zone_list_img';
		}
		$this->ad_zone_db=M('ad_zone');
		if(!$_GET['pid']){
			$pid = 1;
		}else{
			$pid = $_GET['pid'];
		}
		if(!$_GET['market_category']){
			$market_category = 1;
		}else{
			$market_category = $_GET['market_category'];
		}
		if (isset($_GET['zone_id'])){
			$map = array(
				'id' => $_GET['zone_id']
			);
			$ad_zone = $this->ad_zone_db->where($map)->find();
			$this->assign('ad_zone', $ad_zone);
			$pid = $ad_zone['pid'];
		}

		$this->map['status']=1;
		$this->map['pid']=$pid;
		$this->map['market_category'] = $market_category;
		$this->ad_zone_list=$this->ad_zone_db->where($this->map)->field('id,aid,adzone_name')->select();

		$this->maps['status']=1;
		$this->maps['pid']=$pid;
		$this->conf_db = M('feature');
		$this->conf_list=$this->conf_db->where($this->maps)->field('feature_id,name')->select();
		$this->assign('conflist',$this->conf_list);
	
		$activity = D('Sj.Activity');
		$activity_list = $activity->where(array('status' => 1))->field('id,name')->select();
		$this->assign('activitylist', $activity_list);

		$this->assign('zonelist',$this->ad_zone_list);
		$this->assign('returnurl',$this->returnurl);

		$channel_model = M('channel');
		$channel_list = $channel_model->field("cid,chname")->where(array('status' => 1))->select();
		$this->assign('channel_list', $channel_list);
		$this -> assign("market_category",$market_category);
		$this -> assign("pid",$pid);
		$this->display();
	}
	//推荐管理_广告添加_提交
	//Edit By 黄文强 2012/10/10
	public function ad_upload() {
		$model = new Model();
		$channel = M('channel');
		$this->returnurl = $_POST['returnurl'];
		if(empty($this->returnurl)) {
			$this->returnurl='zone_list_img';
		}
		$market_category = $_POST['market_category'];
		$this->ad_list['adname']    =   preg_replace('/[\s]+/','',$_POST['adname']);
		$this->ad_list['zid']       =   $_POST['zid'];
		$this->ad_list['ad_type']   =   $_POST['ad_type'];
		$this->ad_list['orderid']   =   $_POST['orderid'];
		$this->ad_list['note']      =  preg_replace('/[\s]+/','',$_POST['note']);
		$this->ad_list['begintime'] =  strtotime($_POST['begintime']);
		$endtime = $_POST['endtime'];
		$this->ad_list['endtime']   =  strtotime($endtime);
		//var_dump($_POST);
		$this->ad_list['featureid'] = 0;
		$this->ad_list['package']   = '';
		$this->ad_list['href']      = '';
		$this->ad_list['activityid'] = '';
		$this->ad_list['beid'] = trim($_POST['beid']);
	//var_dump($_POST);exit;
		if($this -> ad_list['beid']){
			$beid_where['_string'] = "beid = {$this -> ad_list['beid']} and end > ".time()." and status = 1";
			$beid_result = $model -> table('sj_push_be_detail') -> where($beid_where) -> select();
		
			if(!$beid_result){
				$this -> error("填写的行为id不存在，请检查");
			}
		}
		switch ($this->ad_list['ad_type'])
		{
			case 1:
				$this->ad_list['featureid']   =   $_POST['featureid'];
			
				if(!$this -> ad_list['featureid']){
					$this -> error("请选择专题");
				}
				if($this->ad_list['featureid']){
					$feature_have_result = $model -> table('sj_feature') -> where(array('feature_id' => $this->ad_list['featureid'],'status' => 1)) -> select();
					if(!$feature_have_result){
						$this -> error("专题id不存在");
					}
				}
				break;
			case 2:
				$this->ad_list['package']   =  preg_replace('/[\s]+/','',$_POST['package']);
				$package = $this->ad_list['package'];
				break;
			case 3:
				$this->ad_list['href'] = $_POST['href'];
				$this->ad_list['page_title'] = $_POST['page_title'];
				if(!$_POST['page_title']){
					$this -> error("请填写网页标题");
				}
				$this->ad_list['open_type'] = $_POST['open_type'];
				break;
			case 4:
				$this->ad_list['activityid'] = $_POST['activityid'];
				
				if(!$this -> ad_list['activityid']){
					$this -> error("请选择活动");
				}
				if($this->ad_list['activityid']){
					$activity_have_result = $model -> table('sj_activity') -> where(array('id' => $this->ad_list['activityid'],'status' => 1)) -> select();
					if(!$activity_have_result){
						$this -> error("活动id不存在");
					}
				}
				break;
		}
		if($this->ad_list['endtime'] < $this->ad_list['begintime']) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
			$this->error("对不起，结束时间不得小于开始时间！");
		}

		$this->ad_list['upload_time'] =  time();
		$this->ad_list['last_refresh'] =  time();
		$this->ad_list['status'] =  1;
		$this->ad_list['admin_id'] = $_SESSION['admin']['admin_id'];

		if(empty($this->ad_list['adname']) ||  empty($this->ad_list['zid']) || empty($this->ad_list['ad_type']) || empty($this->ad_list['begintime']) || empty($this->ad_list['endtime']))
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
			$this->error("对不起，广告名称、广告位、广告类型以及起始时间为必选项，请用心填写！");
		}

		if ($this->ad_list['ad_type'] == 3 && (!isset($this->ad_list['href']) || !preg_match('/^http:\/\//is', $this->ad_list['href'])))
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
			$this->error("网址格式错误");
		}
		if ($this->ad_list['ad_type'] == 2)
		{
			$softlist = M("soft");
			$flag = $softlist->where(array('status' => 1,'hide'=>1, 'package' => $this->ad_list['package']))->find();
			if (empty($flag))
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
				$this->error("{$package} 包名不存在市场软件库中，请确认包名的正确性");
			}
		}

		$this->ad_db =  M('ad');
		$sql = "select A.* from sj_ad as A left join sj_ad_zone B on A.zid=B.id where A.status=1 and B.pid=1 and A.adname='{$this->ad_list['adname']}'";
		$flag = $this->ad_db->query($sql);
		if (!empty($flag))
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
			$this->error("广告名称已存在");
		}

		$path = date("Ym/d/");
		$config = array(
			'multi_config' => array(),
		);
		
		if($_POST['zid'] != 30){
			if($_POST['pid'] != 5 && $_POST['pid'] != 4){
				if(!empty($_FILES['image']['size'])){
					$config['multi_config']['image'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width' => 225,
						'img_p_height' => 125,
						'img_p_size' => 1024 * 10,
					);
				} else {
					$this->error("请选择上传文件1！");
				}
			}
			
			if($_POST['pid'] == 4){
				$new_width = 360;
				$new_height = 160;
				$new_size = 30;
			}else{
				$new_width = 480;
				$new_height = 181;
				$new_size = 35;
			}
			
			if(!empty($_FILES['imagev4']['size'])){
				$config['multi_config']['imagev4'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'  => $new_width,
					'img_p_height' => $new_height,
					'img_p_size'   => 1024 * 35,
				);
			} else {
				$this->error("请选择上传文件！");
			}
		}else{
			$imgcnt = 0;
			if(!empty($_FILES['image']['size'])){
				$config['multi_config']['image'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 168,
					'img_p_height' => 75,
					'img_p_size' => 1024 * 10,
				);
				$imgcnt = 1;
			}
			if($_POST['pid'] == 4){
				$new_width = 360;
				$new_height = 160;
				$new_size = 30;
			}else{
				$new_width = 168;
				$new_height = 168;
				$new_size = 35;
			}
			if(!empty($_FILES['imagev4']['size'])){
				$config['multi_config']['imagev4'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'  => $new_width,
					'img_p_height' => $new_height,
					'img_p_size'   => 1024 * $new_size,
				);
				$imgcnt++;
			}
			if($imgcnt > 1){
				$this -> error('推荐位只允许上传一种图片请确认要上传的图片！');
			}
			if($imgcnt == 0){
				$this -> error('请至少上传一张图片');
			}

		}

		if (!empty($config['multi_config'])) {
			$this->lists = $this->_uploadapk(0, $config);

			foreach($this->lists['image'] as $val) {
				if ($val['post_name'] == 'image') {
					$this->ad_list['imageurl']   =   $val['url'];
					if($_POST['zid'] == 30)  $this->ad_list['imgurlv4'] = '';
				}
				if ($val['post_name'] == 'imagev4') {
					$this->ad_list['imgurlv4']    =   $val['url'];
					if($_POST['zid'] == 30)  $this->ad_list['imageurl'] = '';
				}
			}
		}

		$this->ad_zone_db = M('ad_zone');
		$this->map['id'] = $this->ad_list['zid'];
		//$this->ad_list['aid'] =$this->ad_zone_db->where($this->map)->getField('aid');
		$zone_info =$this->ad_zone_db->where($this->map)->find();
		$this->ad_list['aid'] = $zone_info['aid'];
		if (isset($_POST['cid'])) {
			$cids = array();
			foreach ($_POST['cid'] as $cid) {
				if ($cid >= 0)
					$cids[] = $cid;
			}
			$cids = array_unique($cids);
			if (count($cids) > 0) {
				$s = implode(',', $cids);
				$s = ",{$s},";
				$this->ad_list['channel_id'] = $s;
			}
			foreach($cids as $k=>$val){
				if($val!=0){
					$c_where['status']=1;
					$c_where['cid']=$val;
					$ch_chname=$channel->where($c_where)->getfield("chname");
					$zh_chname .=$ch_chname."|";
				}

			}
		}
		$this->ad_db->ping();
		$pid = $_POST['pid'];
		$market_category = $_POST['market_category'];
		if($zone_id = $this->ad_db->add($this->ad_list)) {
			$msg = "向广告位[{$zone_info['adzone_name']}]中添加了名称为[{$this->ad_list['adname']}]的广告, 广告id[{$zone_id}] \n";
			if(empty($zh_chname)){
				$msg .="可见渠道为全部可见 \n";
			}else{
				$msg .="可见渠道为[{$zh_chname}] \n";
			}
			$msg .= "开始时间{$_POST['begintime']}, 结束时间{$endtime}\n";
			$msg .= "软件包名为{$package}\n ";
			$this->writelog($msg, 'sj_ad', $zone_id,__ACTION__ ,"","add");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Advertisement/{$this->returnurl}/id/{$zone_info['id']}/pid/{$pid}/market_category/{$market_category}");
			$this->success("添加广告成功！");
		}else
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Advertisement/{$this->returnurl}/id/{$zone_info['id']}/pid/{$pid}/market_category/{$market_category}");
			$this->error("添加广告失败！");
		}
	}

	//推荐管理_广告编辑_显示
	//Edit By 黄文强 2012/10/10
	public function zone_ad_edit() {
		$channel_model = M('channel');
                	            
		$this->returnurl=$_GET['returnurl'];
		if(empty($this->returnurl)) {
			$this->returnurl='zone_list_img';
		}
	
		$this->sid=$_GET['id'];
		if(empty($this->sid)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error("非法操作失败,如频繁出现，请联系管理员！");
		}
		$this->ad_db = M('ad');
		$this->ad_list = $this->ad_db->where(array('id' => $this->sid))->find();

		$map = array(
			'id' => $this->ad_list['zid']
		);

		$this->ad_zone_db=M('ad_zone');
		$ad_zone = $this->ad_zone_db->where($map)->find();
		$this->map['status']=1;
		$this->map['pid']=$ad_zone['pid'];

		$this->ad_zone_list=$this->ad_zone_db->where($this->map)->field('id,aid,adzone_name')->select();
		$this->assign('zonelist',$this->ad_zone_list);

		$this->conf_db = M('feature');
		$this->conf_list=$this->conf_db->where($this->map)->field('feature_id,name')->select();
		$this->assign('conflist',$this->conf_list);

		$activity = D('Sj.Activity');
		$activity_list = $activity->where(array('status' => 1))->field('id,name')->select();
		$this->assign('activitylist', $activity_list);

		if (strlen($this->ad_list['channel_id']) > 0) {
			$channel_selected = explode(',', $this->ad_list['channel_id']);
			$channel_selected_ret = array();
			foreach ($channel_selected as $cs) {
				if (empty($cs)) continue;
				$channel_selected_ret[] = array('cid' => $cs);
			}
			foreach($channel_selected_ret as $k=>$val){
				$channel_selected_ret[$k]['cid']=$val['cid'];
				$chname=$channel_model->where(array('status' => 1,'cid'=>$val['cid']))->getfield("chname");
				$channel_selected_ret[$k]['chname']=$chname;
			}
			$this->assign('channel_selected', $channel_selected_ret);
		}
		$channel_list = $channel_model->field("cid,chname")->where(array('status' => 1))->select();

		$this->assign('channel_list', $channel_list);
		$this -> assign("pid",$ad_zone['pid']);
		$this->assign('returnurl',$this->returnurl);
		$this->assign('adlist',$this->ad_list);

		$this->display();
	}


	//广告实体编辑
	//Edit By 黄文强 2012/10/10
	public function ad_editit() {
		$this->ad_zone_db = M('ad_zone');
		$this->ad_db = M('ad');
		$channel=M('channel');
		$model = new Model();
		$this->sid = $_POST['id'];
		if(empty($this->sid)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error("非法操作失败,如频繁出现，请联系管理员！");
		}
		
		$old_ad = $this->ad_db->where(array('id' => $this->sid))->find();
		$old_ad['channel_id'] = substr($old_ad['channel_id'], 1, -1);
		$old_cids=explode(",",$old_ad['channel_id']);
		if(count($old_cids)>0){
			$old_zh_chname = '';
			foreach($old_cids as $old_k=>$old_val){
				if($old_val!=0){
					$old_where['status']=1;
					$old_where['cid']=$old_val;
					$old_ch_chname=$channel->where($old_where)->getfield("chname");
					$old_zh_chname .=$old_ch_chname."|";
				}

			}
		}else{
			$old_zh_chname="全部可见";
		}
		$this->returnurl = $_POST['returnurl'];
		if(empty($this->returnurl)) {
			$this->returnurl='zone_list_img';
		}
		
		$this->ad_list['id']           = $_POST['id'];
		$this->ad_list['adname']       =   preg_replace('/[\s]+/','',$_POST['adname']);
		$this->ad_list['zid']          =   $_POST['zid'];
		$this->ad_list['ad_type']      =   $_POST['ad_type'];
		//$this->ad_list['content']      =  preg_replace('/[\s]+/','',$_POST['content']);
		$this->ad_list['orderid']      =   $_POST['orderid'];
		$this->ad_list['note']         =  preg_replace('/[\s]+/','',$_POST['note']);
		$this->ad_list['begintime']    =  strtotime($_POST['begintime']);
		$endtime                       = $_POST['endtime'];
		$this->ad_list['endtime']      =  strtotime($endtime);
		$this->ad_list['last_refresh'] =  time();
		$this->ad_list['featureid']    = 0;
		$this->ad_list['package']      = '';
		$this->ad_list['href']         = '';
		$this->ad_list['activityid'] = '';
		$this->ad_list['beid'] = trim($_POST['beid']);

		if($this -> ad_list['beid']){
			$beid_where['_string'] = "beid = {$_POST['beid']} and end > ".time()." and status = 1";
			$beid_result = $model -> table('sj_push_be_detail') -> where($beid_where) -> select();
		
			if(!$beid_result){
				$this -> error("填写的行为id不存在，请检查");
			}
		}
		
		switch ($this->ad_list['ad_type'])
		{
			case 1:
				$this->ad_list['featureid']   =   $_POST['featureid'];
				if(!$this -> ad_list['featureid']){
					$this -> error("请选择专题");
				}
				if($this->ad_list['featureid']){
					$feature_have_result = $model -> table('sj_feature') -> where(array('feature_id' => $this->ad_list['featureid'],'status' => 1)) -> select();
					if(!$feature_have_result){
						$this -> error("专题id不存在");
					}
				}
				break;
			case 2:
				$this->ad_list['package']   =  preg_replace('/[\s]+/','',$_POST['package']);
				$package = $this->ad_list['package'];
				break;
			case 3:
				$this->ad_list['href'] = $_POST['href'];
				$this->ad_list['page_title'] = $_POST['page_title'];
				$this->ad_list['open_type'] = $_POST['open_type'];
				if(!$_POST['page_title']){
					$this -> error("请填写网页标题");
				}
				break;
			case 4:
				$this->ad_list['activityid'] = $_POST['activityid'];
				if(!$this -> ad_list['activityid']){
					$this -> error("请选择活动");
				}
				if($this->ad_list['activityid']){
					$activity_have_result = $model -> table('sj_activity') -> where(array('id' => $this->ad_list['activityid'],'status' => 1)) -> select();
					if(!$activity_have_result){
						$this -> error("活动id不存在");
					}
				}
				break;
		}
		//var_dump($_POST);
		if ($this->ad_list['ad_type'] == 2)
		{
			$softlist = M("soft");
			$flag = $softlist->where(array('status' => 1, 'hide'=>1,'package' => $this->ad_list['package']))->select();
			if (empty($flag))
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
				$this->error("{$package}包名不存在市场软件库中，请确认包名的正确性");
			}
		}
		if ($this->ad_list['ad_type'] == 3 && (!isset($this->ad_list['href']) || !preg_match('/^http:\/\//is', $this->ad_list['href'])))
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
			$this->error("网址格式错误");
		}
		if(empty($this->ad_list['adname']) ||  empty($this->ad_list['zid']) || empty($this->ad_list['ad_type']) || empty($this->ad_list['begintime']) || empty($this->ad_list['endtime']))
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
			$this->error("请用心填写！");
		}

		$this->ad_db =  M('ad');
		$sql = "select A.* from sj_ad as A left join sj_ad_zone B on A.zid=B.id where A.status=1 and B.pid=1 and A.adname='{$this->ad_list['adname']}' and A.id<>{$this->ad_list['id']}";
		$flag = $this->ad_db->query($sql);
		if (count($flag) > 0 )
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/'.$this->returnurl);
			$this->error("广告名称已存在");
		}

		//附件上传
		$path = date("Ym/d/");
		$config = array(
			'multi_config' => array(),
		);
		if($_POST['zid'] != 30){
			if($_POST['pid'] != 5){
				if(!empty($_FILES['image']['size'])){
					$config['multi_config']['image'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width' => 225,
						'img_p_height' => 125,
						'img_p_size' => 1024 * 10,
					);
				}
			}
			if($_POST['pid'] == 4){
				$new_width = 360;
				$new_height = 160;
				$new_size = 30;
			}else{
				$new_width = 480;
				$new_height = 181;
				$new_size = 35;
			}
			
			if(!empty($_FILES['imagev4']['size'])){
				$config['multi_config']['imagev4'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'  => $new_width,
					'img_p_height' => $new_height,
					'img_p_size'   => 1024 * $new_size,
				);
			}
		}else{
			$imgcnt = 0;
			if(!empty($_FILES['image']['size'])){
				$config['multi_config']['image'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 168,
					'img_p_height' => 75,
					'img_p_size' => 1024 * 10,
				);
				$imgcnt = 1;
			}
			
			if($_POST['pid'] == 4){
				$new_width = 360;
				$new_height = 160;
				$new_size = 30;
			}else{
				$new_width = 168;
				$new_height = 168;
				$new_size = 35;
			}
			if(!empty($_FILES['imagev4']['size'])){
				$config['multi_config']['imagev4'] = array(
					'savepath' => UPLOAD_PATH. '/img/'. $path,
					'saveRule' => 'getmsec',
					'img_p_width'  => $new_width,
					'img_p_height' => $new_height,
					'img_p_size'   => 1024 * $new_size,
				);
				$imgcnt++;
			}
			if($imgcnt > 1){
				$this -> error('推荐位只允许上传一种图片请确认要上传的图片！');
			}
//			if($imgcnt == 0){
//				$this -> error('请至少上传一张图片');
//			}

		}
	
		if (!empty($config['multi_config'])) {
			$this->lists = $this->_uploadapk(0, $config);

			foreach($this->lists['image'] as $val) {
				if ($val['post_name'] == 'image') {
					$this->ad_list['imageurl']   =   $val['url'];
				}
				if ($val['post_name'] == 'imagev4') {
					$this->ad_list['imgurlv4']    =   $val['url'];
				}
			}
		}
		if($_POST['zid'] == 30){
			if(!empty($this->ad_list['imageurl'])) $this->ad_list['imgurlv4'] = '';
			if(!empty($this->ad_list['imgurlv4'])) $this->ad_list['imageurl'] = '';
		}

		$this->map['id'] = $this->ad_list['zid'];
		$zone_info = $this->ad_zone_db->where($this->map)->find();
		$this->ad_list['aid'] = $zone_info['aid'];

		$configModel = D('Sj.Config');
		$column_desc = $configModel->getAdColumnDesc();

		if (isset($_POST['cid'])) {
			$cids = array();
			foreach ($_POST['cid'] as $cid) {
				if ($cid >= 0)
					$cids[] = $cid;
			}
			$cids = array_unique($cids);
			if (count($cids) > 0) {
				$s = implode(',', $cids);
				$s = ",{$s},";
				$this->ad_list['channel_id'] = $s;
			} else {
				$this->ad_list['channel_id'] = null;
			}
			foreach($cids as $k=>$val){
				if($val!=0){
					$c_where['status']=1;
					$c_where['cid']=$val;
					$ch_chname=$channel->where($c_where)->getfield("chname");
					$zh_chname .=$ch_chname."|";
				}

			}
		}else{
			$zh_chname="全部可见";
		}
	
		if(false!==$this->ad_db->where(array('id'=>$this->sid))->save($this->ad_list)) {
			$where = array('id' => $old_ad['zid']);
			$old_zone_info =$this->ad_zone_db->where($where)->find();
			$msg = "市场软件运营推荐-市场首页轮播图:编辑了广告位[{$old_zone_info['adzone_name']}]中ID为[{$this->sid}]的广告,广告名[{$old_ad['adname']}]\n";
			foreach ($this->ad_list as $key => $val) {
				if (isset($column_desc[$key]) && $this->ad_list[$key] != $old_ad[$key]) {
					$desc = $column_desc[$key];
					if ($key == 'zid') {
						$msg .= "将{$desc} 从'{$old_zone_info['adzone_name']}'修改成 '{$zone_info['adzone_name']}'\n";
					}elseif($key=='channel_id'){
						$this->ad_list['channel_id'] = substr($this->ad_list['channel_id'],1,-1);
						if($this->ad_list['channel_id']!=$old_ad['channel_id']){
							$msg .= "将{$desc} 从'{$old_zh_chname}'修改为 '{$zh_chname}'\n";
						}

					}elseif($key == 'begintime' || $key == 'endtime') {
						$old = date('Y-m-d H:i:s', $old_ad[$key]);
						$new = date('Y-m-d H:i:s', $this->ad_list[$key]);
						$msg .= "将{$desc} 从'{$old}'修改成 '{$new}'\n";
					} else {
						$msg .= "将{$desc} 从'{$old_ad[$key]}'修改成 '{$this->ad_list[$key]}'\n";
					}
				}
			}
			if(!empty($this->ad_list['package'])){
				$msg .= "软件包名为{$this->ad_list['package']}";
			}

			$this->writelog($msg, 'sj_ad', $this->sid,__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Advertisement/{$this->returnurl}/id/{$zone_info['id']}/pid/{$_POST['pid']}/market_category/{$zone_info['market_category']}");
			$this->success("编辑广告成功！");
		}else
		{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Advertisement/{$this->returnurl}/id/{$zone_info['id']}/pid/{$_POST['pid']}/market_category/{$zone_info['market_category']}");
			$this->error("编辑广告失败！");
		}

	}


	// 推荐管理_专题列表
	public function feature_list() {
		$pid = isset($_GET['pid']) ? $_GET['pid'] : '1';
		$util = D("Sj.Util");
		$feature_db    = M('feature');
		$feature_soft_db = M('feature_soft');
		$map['status'] = 1;
		//$map['pid'] = $pid;
		$map['pid'] = array('like','%,'.$pid.',%');
			// 如要分页操作只需把以下注释去掉即可
			import("@.ORG.Page");
			$count = $feature_db->where($map)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$this->assign('page', $show);
			$feature_list = $feature_db->where($map)->field("feature_id,name")->limit($Page->firstRow.','.$Page->listRows)->select();
//		$feature_list  = $feature_db->where($map)->field("feature_id,name")->select();	// 不分页的
		foreach ($feature_list as &$featurelist){
			$featurelist['n'] = $feature_soft_db->where("feature_id={$featurelist['feature_id']} AND `status`=1 AND start_tm <= ".time()." AND end_tm >= ".time())->count();
		}
		/*echo '<pre>';
		print_r($feature_list);
		echo '</pre>';*/
		$this->assign('product_list',$util->getProducts($pid));
		$this->assign('featurelist',$feature_list);
		$this->display();
	}
	// 推荐管理_专题软件列表
	function feature_soft_list(){
		$model = new Model();
		$featureid = $_GET['id'];
		$where = array(
			'A.status' => 1,
			'A.feature_id' => $featureid,
			//'B.feature_id' => $featureid,
		);
		//按时间搜索
		$time = time();
		if(!isset($_GET['select_time'])) $select_time = 2;
		if($_GET['select_time'] == 2  || $_GET['select_time'] == ""){
			$where['A.start_tm'] = array('elt',$time); 
			$where['A.end_tm'] = array('egt',$time); 
		}elseif($_GET['select_time'] == 3){
			$where['A.start_tm'] = array('gt',$time); 
		}elseif($_GET['select_time'] == 4){
			$where['A.end_tm'] = array('lt',$time); 
		}
		$order_str = "B.rank asc,A.pos";

		import('@.ORG.Page');
		$count = $model->table('sj_feature_soft A')->join("sj_feature_graphic B ON B.id = A.feature_graphic_id")->where($where)->field('A.id')->count();
		$Page = new Page($count, 15);
		$show = $Page->show();
		
		$feature_list = $model->table('sj_feature_soft A')->join("sj_feature_graphic B ON B.id = A.feature_graphic_id")->where($where)->field('A.*,B.rank as rank_b,B.status as status_b')->limit($Page->firstRow.','.$Page->listRows)->order($order_str)->select();
		$pkg = array();
		foreach($feature_list as $k => $v){
			$pkg[] = $v['package'];
		}
		$where = array(
			'package' => array('in',$pkg),
			'status' => 1,
			'hide' => 1,
		);
		$soft_info = get_table_data($where,"sj_soft","package","softname,package");
		$section = array('','一','二','三','四','五','六','七','八','九','十');
		//获取合作样式列表
		$util = D("Sj.Util"); 
		foreach($feature_list as $k => $v){
			$feature_list[$k]['softname']=$soft_info[$v['package']]['softname'];
			if($v['status_b'] == 1){
				$feature_list[$k]['feature_graphic'] = "图文第".$section[$v['rank_b']]."段";
			}
			$feature_list[$k]['rank_b'] = $v['rank_b'] ? $v['rank_b'] : 0;
			$typelist = $util->getHomeExtentSoftTypeList($v['type']);
			foreach($typelist as $key => $val){
				if($val[1] == true)
				{
					$feature_list[$k]['types'] = $val[0];
				}
			}
		}
		$res = $model->table('sj_feature')->where("feature_id=$featureid")->field('name')->find();
		$this->assign('featurename', $res['name']);
		$this->assign('featureid', $featureid);
		$this->assign('page', $show);
		$this -> assign("time",$select_time ? $select_time : $_GET['select_time'] );
		$this->assign('feature_soft_list', $feature_list);	
		$this->display();
	}
	//推荐管理_专题软件增加_显示
	public function feature_soft_add() {
		$feature_id = (int)$_GET['featureid'];
		$feature_db = M('feature');
		$map['status'] = 1;
		$feature_soft_db = M('feature_soft');
		$feature_soft_list = $feature_soft_db->where(array('feature_id'=>$feature_id,'status' => 1))->order('rank asc')->select();
//		$feature_list = $feature_db->where($map)->field('feature_id,name')->select();
//		$this->assign('featurelist',$feature_list);
		$this->assign('count',count($feature_soft_list)+1);
		$this->assign('feature_id',$feature_id);
		$where = array('feature_id' => $feature_id,'status'=>1);
		$feature = $feature_db->table('sj_feature_graphic')->where($where)->order('rank asc')->field('id,rank')->select();
		$section = array('','一','二','三','四','五','六','七','八','九','十');
		foreach($feature as $k => $v){
			$feature[$k]['title'] = "图文第".$section[$v['rank']]."段";
		}
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);
		
        $this->assign('feature', $feature);		
		$this->display();
	}
	//推荐管理_专题软件增加_提交
	function feature_soft_upload() {
        // tpl（网页）里的名称和数据库字段对应数组
        $column_convert_arr = array(
            'feature_id' => 'feature_id',
            'package' => 'package',
            'special' => 'special',
            'remark' => 'remark',
            'rank' => 'rank',
            'begintime' => 'start_tm',
            'endtime' => 'end_tm',
			'type' =>'type',
			'recommend_soft_name' => 'recommend_soft_name',
			'recommend_reason' =>'recommend_reason',
			'recommend_person' =>'recommend_person',
        );
        // $check_column_arr数组存放_POST/_GET对应数据库字段的值（因为logic_check里的变量名跟数据库字段名一样）
        $check_column_arr = array();
        foreach($column_convert_arr as $key=>$value) {
            if (array_key_exists($key, $_POST)) {
                $check_column_arr[$value] = $_POST[$key];
            }
        }
        // trim一下
        foreach($check_column_arr as $key=>$value) {
            $check_column_arr[$key] = trim($value);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
        $error_msg = $this->logic_check($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            // $this->assign('jumpUrl', '');
            $this->error($msg);
        }
    
		$feature_soft_db = M('feature_soft');
		$data['feature_id']  = (int)$_POST['feature_id'];
		$data['package']     = trim((string)$_POST['package']);
		$data['status']      = 1;
		$data['special']     = trim((string)$_POST['special']);
		$data['remark']      = trim((string)$_POST['remark']);
		$data['pos']        = (int)$_POST['rank'];
		$data['recommend_soft_name']= trim((string)$_POST['recommend_soft_name']);
		$data['recommend_reason']= trim((string)$_POST['recommend_reason']);
		$data['recommend_person']= trim((string)$_POST['recommend_person']);
		$where = array('id'=>$_POST['feature_graphic_id']);
		$graphic = $feature_soft_db->table('sj_feature_graphic')->where($where)->field('rank')->find();
		$data['rank']        = ($graphic['rank']*100)+(int)$_POST['rank'];
		$data['start_tm'] = trim(strtotime($_POST['begintime']));
		$data['end_tm'] = trim(strtotime($_POST['endtime']));
		$data['feature_graphic_id']        = (int)$_POST['feature_graphic_id'];
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		if(isset($_POST['type'])){
				$data['type'] = $_POST['type'];
		}else{
			$data['type'] = 0;
		}
		$model = new Model();
		$time = time();
		if($data['package']){
			//屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm'],array('0'=>1));
			if($shield_error){
			    $this -> error($shield_error);
			}
		}
		
		if($id = $feature_soft_db->add($data)) 
		{
			$feature_result = $model -> table('sj_feature') -> where(array('feature_id' => $_POST['feature_id'])) -> save(array('last_refresh' => $time));
			$where_rank = ' `status` = 1 ';
			$where_rank .= ' AND `id` ='.$id;
			if(isset($data['feature_id'])){
			   $where_rank .= ' AND `feature_id` = '.(int)$data['feature_id'];
			}
			//更新排序
			$map = array(
				'rank' => $data['rank'],
				'upload_time' => $time,
				'last_refresh' => $time,
			);
		    $result = $model -> table('sj_feature_soft') -> where($where_rank) -> save($map);
			//$this->_updateRankInfo('sj_feature_soft','rank',$id,$where_rank,$data['rank']);
			$this->writelog("推荐管理-专题软件列表-添加了ID为[{$id}]包名为{$data['package']}的专题软件", 'sj_feature_soft', $id,__ACTION__ ,"","add");
			//$_POST['from'] == 1 来自远程softAction 调
			if($_POST['from'] != 1)	$this->ajaxReturn(1, "添加成功！", 1);
		}
		else
		{
			if($_POST['from'] != 1)	$this->ajaxReturn(0, "添加失败！", 0);
		}

	}
	//推荐管理_专题软件编辑_显示
	public function feature_soft_edit() {
		$id = $_GET['id'];
		$feature_id = $_GET['feature_id'];
		if(empty($id)) {
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Advertisement/zone_list');
			$this->error("非法操作失败,如频繁出现，请联系管理员！");
		}
		$feature_soft_db = M('feature_soft');
		$feature_soft_list = $feature_soft_db->where(array('id' =>$id))->order('rank')->select();
		$soft_db = M('soft');
		$package = '';
		for($i=0;$i<count($feature_soft_list);$i++) {
			$package.=',"'.$feature_soft_list[$i]['package'].'"';
		}
		if($package[0]==',')
		{
			$package=substr($package,1);
		}
		$map = '';
		$map['package'] = array('in',$package);
		$soft_list= $soft_db->field('softname,package')->where($map)->group('package')->select();

		for($i=0;$i<count($feature_soft_list);$i++) {
			for($j=0;$j<count($soft_list);$j++) {
				if(strcasecmp($soft_list[$j]['package'],$feature_soft_list[$i]['package'])==0) {
					$this->feature_soft_list[$i]['softname'] = $soft_list[$j]['softname'];
				}
			}
		}
		$condition['feature_id'] =  $feature_soft_list[0]['feature_id'];
		$condition['status']     = 1;
		$count = count($feature_soft_db -> where($condition) -> select());
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($feature_soft_list[0]['type']);
		$this->assign('typelist',$typelist);
		
		$this->assign('count',$count);
		$this->assign('feature_id',$feature_id );
		$this->assign('featuresoftlist',$feature_soft_list[0]);
		$where = array('feature_id' => $feature_id,'status'=>1);
		$feature = $feature_soft_db->table('sj_feature_graphic')->where($where)->order('rank asc')->field('id,rank')->select();
		$section = array('','一','二','三','四','五','六','七','八','九','十');
		foreach($feature as $k => $v){
			$feature[$k]['title'] = "图文第".$section[$v['rank']]."段";
		}
        $this->assign('feature', $feature);			
		$this->display();
	}

	//推荐管理_专题软件编辑_提交
	public function feature_soft_editit() {
        // 将_POST或_GET传进来的参数统一转成与表里字段一样的名称
        $column_convert_arr = array(
            'id' => 'id',
            'feature_id' => 'feature_id',
            'package' => 'package',
            'special' => 'special',
            'remark' => 'remark',
            'rank' => 'rank',
            'begintime' => 'start_tm',
            'endtime' => 'end_tm',
			'type' => 'type',
			'recommend_soft_name' => 'recommend_soft_name',
			'recommend_reason' =>'recommend_reason',
			'recommend_person' =>'recommend_person',
        );
        $check_column_arr = array();
        foreach($column_convert_arr as $key=>$value) {
            if (array_key_exists($key, $_POST)) {
                $check_column_arr[$value] = $_POST[$key];
            }
        }
        foreach($check_column_arr as $key=>$value) {
            $check_column_arr[$key] = trim($value);
        }
        // 调用通用的检查函数
        $content_arr = array();
        $content_arr[0] = $check_column_arr;
        $error_msg = $this->logic_check($content_arr);
        $qualified_flag = true;
        foreach($error_msg as $key=>$value) {
            if ($value['flag'] == 1)
                $qualified_flag = false;
        }
        if (!$qualified_flag) {
            $msg = $error_msg[0]['msg'];
            // 业务逻辑：设置返回的跳转页面
            $this->error($msg);
        }
		$feature_soft_db = M('feature_soft');
		$id = $data['id'] = (int)trim($_POST['id']);
		

		$data['package']    = (string)trim($_POST['package']);
		
		$data['pos']       = (int)trim($_POST['rank']);
		$where = array('id'=>$_POST['feature_graphic_id']);
		$graphic = $feature_soft_db->table('sj_feature_graphic')->where($where)->field('rank')->find();
		$data['rank']        = ($graphic['rank']*100)+(int)$_POST['rank'];
		$data['feature_id'] = (int)trim($_POST['feature_id']);
		$data['special']    = (string)trim($_POST['special']);
		$data['remark']     = (string)trim($_POST['remark']);
		$sur_rank           = (int)trim($_POST['currentrank']);
		$data['start_tm']   = (int)strtotime(trim($_POST['begintime']));
		$data['end_tm']     = (int)strtotime(trim($_POST['endtime']));
		$data['recommend_soft_name']= trim((string)$_POST['recommend_soft_name']);
		$data['recommend_reason']= trim((string)$_POST['recommend_reason']);
		$data['recommend_person']= trim((string)$_POST['recommend_person']);
		if(isset($_POST['type'])){
			$data['type'] = $_POST['type'];
		}else{
			$data['type'] = 0;
		}
		$data['feature_graphic_id']     = (int)trim($_POST['feature_graphic_id']);
		$data['last_refresh']     = time();
		

		$old_feature = $feature_soft_db->where(array("status"=>1,"id"=>$data['id']))->find();
		if($data['package']){
			 //屏蔽软件上排期时报警需求 新增  yuesai
			$AdSearch = D("Sj.AdSearch");
			$shield_error=$AdSearch->check_shield($data['package'],$data['start_tm'],$data['end_tm'],array('0'=>1));
			if($shield_error){
			    $this -> error($shield_error);
			}
		}
       
		$log = $this -> logcheck(array('id' =>$_POST['id']),'sj_feature_soft',$data,$feature_soft_db);
		if(false===$feature_soft_db->where(array('id'=>$id))->save($data)) 
		{
			//$_POST['from'] == 1 来自远程softAction 调
			if($_POST['from'] != 1)	 $this->ajaReturn(0, "编辑专题软件失败！", 0);
		}

		$configModel = D('Sj.Config');
			$column_desc = $configModel->getFeatureSoftColumnDesc();
			$msg = "推荐管理-专题软件列表-编辑了id为[{$id}]包名为{$data['package']}".$log;
			foreach ($data as $key => $val) {
				if (isset($column_desc[$key]) && $data[$key] != $old_feature[$key]) {
					$desc = $column_desc[$key];
					$msg .= "将{$desc} 从'{$old_feature[$key]}'修改成 '{$data[$key]}'\n";
				}
			}
		$this->writelog($msg,"sj_feature_soft",$id,__ACTION__ ,"","edit");
		if($_POST['from'] != 1)	 $this->ajaxReturn(1, "编辑专题软件成功！", 1);
	}

	//推荐管理_专题软件删除
	public function feature_soft_del() {
		$id  = (int)$_GET['id'];
		$feature_id = (int)$_GET['feature_id'];
		if(empty($id)) {
 			$this->ajaxReturn(0,"非法操作失败,如频繁出现，请联系管理员！",0);
		}

		$feature_soft_db = M('feature_soft');
		$result= $feature_soft_db->where(array("status"=>1,"id"=>$id))->find();
		$data['status']   = 0 ;
		$data['last_refresh'] = time();
		$map['id']          = $id;
		$map['feature_id']  = $feature_id;


		if(false!==$feature_soft_db->where($map)->save($data)) {
			$feature_soft = $feature_soft_db->where('status = 1 AND feature_id ='.$feature_id)->order('rank ASC')-> select();
			$count = count($feature_soft);
			for($i = 1;$i <= $count; $i++){
				$sql = 'UPDATE __TABLE__ SET rank ='.$i.' WHERE feature_id='.$feature_id.' AND id='.$feature_soft[$i-1]['id'];
				$feature_soft_db ->query($sql);
			}
			$this->writelog('推荐管理-专题软件列表-删除了ID为['.$id.']包名为['.$result['package'].']的专题软件','sj_feature_soft',$id,__ACTION__ ,"","del");
//			$this->success("删除成功！");
 			$this->ajaxReturn(1,"删除成功！",1);
		} else {
			$this->ajaxReturn(0,"删除失败！",0);
		}
	}
	function feature_soft_change_rank(){
		$id = intval(trim($_POST['id']));
		$data['feature_id'] = $featureid = intval(trim($_POST['fid']));
		$data['pos'] = $rank = intval(trim($_POST['rank']));
		$oldrank = intval(trim($_POST['oldrank']));
		if (!$id || !$featureid || !$rank){
			$this->ajaxReturn(1, '未知错误,更改失败!', 1);
			exit();
		}
		$where_rank = ' `status` = 1 ';
		$where_rank .= ' AND `id` ='.$id;
		if(isset($data['feature_id'])){
		   $where_rank .= ' AND `feature_id` = '.$data['feature_id'];
		}
		if($rank >= 100){
			$this->ajaxReturn(0, '不可超过99', 0);
			exit();		
		}
		//更新排序
		$model=	new Model();
		$map = array(
			'pos' => $rank,
			'rank' => ($_POST['rank_b']*100)+$rank,
		);
		$result = $model -> table('sj_feature_soft') -> where($where_rank) -> save($map);
		if($result)
		{
		 $this->writelog('推荐管理-专题软件列表-编辑了id为'.$id.',的rank排序由'.$oldrank.'更改为'.$rank,'sj_feature_soft',$id,__ACTION__ ,"","edit");
		 $this->ajaxReturn(1, '更改位置成功!', 1);
		}
		else
		{
			$this->ajaxReturn(0, '更改位置失败!', 0);
		}
	}

	//推荐管理_列表推荐软件_删除
	public function zone_ad_delete() {
	  $this -> returnurl = $_GET['returnurl'];
	  $this -> sid       = $_GET['id'];
	  $this -> ad_db     = M('ad');
	  $pid = $_GET['pid'];
	  $update['status']  = 0;
	  $update['endtime'] = time();
	  $update['last_refresh'] = time();
	  $map['id']         = $this -> sid;
	  $ad_soft = $this -> ad_db -> where($map) -> select();
	  $affect = $this -> ad_db -> where($map) -> save($update);
	  if($affect > 0){
			$this->ad_zone_db=M('ad_zone');
			$map = array('id' => $ad_soft[0]['zid']);
			$zone_info =$this->ad_zone_db->where($map)->find();
			
			$this->ad_list['aid'] = $zone_info['aid'];
		  	$log = "市场软件运营推荐-市场首页轮播图:从广告位{$zone_info['adzone_name']}中删除了ID为[{$ad_soft[0]['id']}],名为[{$ad_soft[0]['adname']}]";
		  	if(!empty($ad_soft[0]['package'])){
				$log .= ",包名为[{$ad_soft[0]['package']}]的广告软件";
			}
			$this->writelog($log, 'sj_zd',$ad_soft[0]['id'],__ACTION__ ,"","del");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Advertisement/{$this->returnurl}/id/{$zone_info['id']}/pid/{$zone_info['pid']}/market_category/{$zone_info['market_category']}");
			$this->success($ad_soft[0]['adname']."的广告软件删除成功！");
	  }else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Advertisement/{$this -> returnurl}/id/{$zone_info['id']}/pid/{$zone_info['pid']}/market_category/{$zone_info['market_category']}");
			$this->error("删除失败！");
	  }
	}
	public function search_ad_soft() {

		$this->sid=$_GET['id'];
		$this->ad_zone_db=M('ad_zone');
		$this->map['adzone_type']=1;
		$this->map['status']=1;
		$package = $_GET['package'] ? $_GET['package'] : '';
		$begintime = $_GET['begintime'] ? strtotime($_GET['begintime']) : "";
		$endtime = $_GET['endtime'] ? strtotime($_GET['endtime']) : "";
		if($begintime > $endtime){
			$this -> error("现在时间段 结束时间 小于 开始时间");
		}
		if($_GET['begintime'] == $_GET['endtime']){
			   $endtime = strtotime($_GET['endtime']);
		}
		$this->ad_zone_list=$this->ad_zone_db->where($this->map)->field('id,aid,adzone_name')->select();
		$this->assign('zonelist',$this->ad_zone_list);
		if(empty($this->sid)) {
			$this->sid=$this->ad_zone_list[0]['id'];
		}

		$this->ad_db=M('ad');
		$soft_db = D('soft');
		$nowtime = time();

		for($i=0;$i<count($this->ad_zone_list);$i++) {
				$ad_softs = array();
				$map='';
				if($package) $map .="`package` like '%".$package."%' and ";
				$map .= "`status` = 1 and `zid` = ".$this->ad_zone_list[$i]['id']." and begintime <= ".$endtime."   and  endtime >=".$begintime." ";
				$ad_softs = $this->ad_db->where($map)->order('begintime,endtime asc')->select();
				foreach($ad_softs as &$v){
					$map_soft = " package='{$v['package']}' and `status`=1 and `hide`=1 ";
					$softname = $soft_db->where($map_soft)->getField("softname");
					$softname = $softname?$softname:"已无此软件";
					$v['package_name'] = $v['package']."({$softname})";
				}
			   $this->ad_list[$this->ad_zone_list[$i]['id']] = $ad_softs;
		}
		$this -> assign('adlist',$this->ad_list);
		$this -> assign('nowtime',$nowtime);
		$this -> assign("package",$package);
		$this -> assign("begintime",$_GET['begintime']);
		$this -> assign("endtime",$_GET['endtime']);
		$this -> assign("WEBMARKETURL" , __MARKETURL__);
		$this -> assign('thepid',$this->sid);
		$this -> display("zone_list_soft");
	}

	public function search_ad_soft_list() {
		$this->sid=$_GET['id'];
		$this->ad_zone_db=M('ad_zone');
		$this->map['adzone_type']=1;
		$this->map['status']=1;
		$package = $_GET['package'] ? $_GET['package'] : "";
		$begintime = $_GET['begintime'] ? strtotime($_GET['begintime']) : ((strtotime(date("Y-m-d H:i:s")))-86400*7);
		$endtime = $_GET['endtime'] ? strtotime($_GET['endtime']) : (strtotime(date("Y-m-d H:i:s")));
		$this->assign("time",array(date("Y-m-d H:i:s",$begintime),date("Y-m-d H:i:s",$endtime)));
		if($begintime > $endtime){
			$this -> error("现在时间段 结束时间 小于 开始时间");
		}
		$this->ad_zone_list=$this->ad_zone_db->where($this->map)->field('id,aid,adzone_name,status')->select();
		$this->assign('zonelist',$this->ad_zone_list);
		if(empty($this->sid)) {
			$this->sid=$this->ad_zone_list[0]['id'];
		}
		$this->ad_db=M('ad');
		$soft_db = D('soft');
		$nowtime = time();

		for($i=0;$i<count($this->ad_zone_list);$i++) {
				$ad_softs = array();
				$map='';
				if($package) $map .="`package` like '%".$package."%' and ";
				$map .= "`zid` = ".$this->ad_zone_list[$i]['id']." and begintime <= ".$endtime."   and  endtime >=".$begintime." ";
				$ad_softs = $this->ad_db->where($map)->order('begintime,endtime asc')->select();
				foreach($ad_softs as &$v){
					$map_soft = " package='{$v['package']}' and `status`=1 and `hide`=1 ";
					$softname = $soft_db->where($map_soft)->getField("softname");
					$softname = $softname?$softname:"已无此软件";
					$v['package_name'] = $v['package']."({$softname})";
				}
			   $this->ad_list[$this->ad_zone_list[$i]['id']] = $ad_softs;
		}
		$this -> assign('adlist',$this->ad_list);
		$this -> assign('nowtime',$nowtime);
		$this -> assign("package",$package);
		$this -> assign("WEBMARKETURL",__MARKETURL__);
		$this -> assign("begintime",$_GET['begintime']);
		$this -> assign("endtime",$_GET['endtime']);
		$this -> assign('thepid',$this->sid);
		$this -> display();
	}

	//推荐管理_专题页软件管理_位置处理
	public function feature_soft_rank_do(){
		$id         = (int)$_GET['id'];
		$rank       = (int)$_GET['rank'];
		$feature_id = (int)$_GET['feature_id'];
		$lr         = isset($_GET['lr']) ? (int)$_GET['lr'] : 20;
		$p          = isset($_GET['p'])  ? (int)$_GET['p']  : 1;

		$where      = ' `status` = 1 AND `feature_id` = '.$feature_id;

		//更新排序
		$param = $this->_updateRankInfo('sj_feature_soft','rank',$id,$where,$rank,$lr,$p);
		$this->writelog('推荐管理_专题页软件管理_位置处理编辑了为'.$id.',的rank排序', 'sj_feature_soft',$id,__ACTION__ ,"","edit");
		exit(json_encode($param));
	}

	//响应专题类别的软件个数
	public function feature_rank_response(){

		$id  = (int)$_GET['feature_id'];
		$this -> feature_soft_db = M('feature_soft');

		$map ['feature_id'] = $id;
		$map ['status']     = 1;
		$this -> feature_soft_list = $this->feature_soft_db->where($map)->order('rank asc')->select();
		$count = count($this->feature_soft_list) + 1 ;

		$rank = '';
		for($i = 1;$i <= $count;$i++){
			$rank .= "<option value=\"".$i."\"";
			if($count == $i){
				$rank .= " selected=\"selected\"";
			}
			$rank .= ">$i</option>";
		}
		echo $rank;
		exit;
	}

	//批量更新主题软件的排序位置
	public function feature_batch_rank(){
		$this -> feature_soft_db = M('feature_soft');
		$ids   = (string)$_GET['id'];
		$ranks = (string)$_GET['rank'];
		$feature_id = (int)$_GET['feature_id'];
		$ids   = substr($ids,0,strlen($ids)-1);
		$ranks = substr($ranks,0,strlen($ranks)-1);

		$feature_soft = array();
		$allids   = explode(",",$ids);
		$allranks = explode(",", $ranks);

		$feature_soft = array_combine($allids,$allranks);
		foreach($feature_soft as $id => $rank){
			$this->feature_soft_db -> query("UPDATE __TABLE__ SET rank = ".$rank." WHERE status = 1 AND feature_id = ".$feature_id." AND id = " .$id);
		}

		$this->writelog('主题软件的排序位置批量更新了feature_id为'.$feature_id.'的排序',__TABLE__,$feature_id,__ACTION__ ,"","edit");
		$this->assign('jumpUrl','/index.php/Sj/Advertisement/feature_soft_list/feature_id/'.$feature_id);
		$this->success('批量更新成功');
	}
    
    // 初始单条错误信息，初始化信息：flag为0，msg为空
    function init_error_msg(&$error_msg, $key) {
        if (!isset($error_msg))
            $error_msg = array();
        $error_msg[$key] = array('flag' => 0,'msg' => '');
    }
    
    // 添加错误信息
    function append_error_msg(&$error_msg, $key, $flag, $msg) {
        if (!isset($error_msg[$key])) {
            $this->init_error_msg($error_msg, $key);
        }
        $error_msg[$key]['flag'] |= $flag;
        $error_msg[$key]['msg'] .= $msg;
    }
    
    // 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加单条软件一致
    // 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
    // 2，将某些列的字符串转成数字，如是、否转化成1，0......
    function handwriting_convert_and_check(&$content_arr) {
        // 初始化错误信息数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：将表里字段名称和模版里列的名称一一对应
        $correct_title_arr = array(
            'feature_id' => '专题类别ID',
            'name' => '专题类别名称',
			'feature_graphic_rank'=> '所属段落',
            'package'  =>   '软件包名',
            'start_tm'  =>   '开始时间(yyyy/MM/dd)',
            'end_tm'  =>   '结束时间(yyyy/MM/dd)',
            'special'  =>   '是否特殊条件',
            'remark' => '软件简介',
            'rank' => '排序',
			'type' => '合作形式',
			'recommend_soft_name' => '软件包名(推荐)',
			'recommend_reason' =>'推荐理由(必填)',
			'recommend_person' =>'推荐人群(必填)',
        );
        // trim一下所有的数据
        foreach($content_arr as $key=>$record) {
            foreach($record as $r_key=>$r_record) {
                $content_arr[$key][$r_key] = trim($r_record);
            }
        }
        // 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($correct_title_arr as $key => $value) {
            $new_key[] = $key;
        }
        foreach($content_arr as $key=>$record) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($record[$new_key_key])) {
                    $new_content_arr[$key][$new_key_value] = $record[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
        /* 现在批量导入支持所有专题，不只是装机必备
        // 业务逻辑：为了和页面共用logic_check(),现给每一行分配装机必备的id：31
        foreach($content_arr as $key=>$record) {
            $content_arr[$key]['feature_id'] = C("ZJBB_FEATURE_ID");
        }
        */
        // 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息
        $expected_words = array();
        // 当输入为空不允许时，将其值设为false以作区别
        $expected_words['special'] = array("" => 0, "是" => 1, "否" => 0);
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->get_cooperation();
		$expected_words['type'] =$typelist;
		//所属段落换成对应的数字
		$expected_words['feature_graphic_rank'] = array("" => 0,"图文第一段" =>1,"图文第二段" =>2,"图文第三段" =>3,"图文第四段" =>4,"图文第五段" =>5,"图文第六段" =>6,"图文第七段" =>7,"图文第八段" =>8,"图文第九段" =>9,"图文第十段" =>10,);
		
        foreach($content_arr as $key=>$record) {
            // 开始检查每列内容是否为预期内容
            foreach($record as $r_key=>$r_value) {
                if (array_key_exists($r_key, $expected_words)) {
                    if (!array_key_exists($r_value, $expected_words[$r_key])) {
                        $column = $correct_title_arr[$r_key];
                        $this->append_error_msg($error_msg, $key, 1, "{$column}列内容填写有误;");
                    } else {
                        $tmp = $expected_words[$r_key][$r_value];
                        // 如果是false不处理（在后台的logic_check()里会统一进行非空检查），即还是为空，否则替换成相应的数字
                        if ($tmp !== false)
                            $content_arr[$key][$r_key] = $tmp;
                    }
                }
                // 自动填充批量导入的时间
                if ($r_key == 'start_tm' || $r_key == 'end_tm') {
                    if ($r_key == 'start_tm') {
                        $type = 0;
                        $hint = '开始';
                    } else {
                        $type = 1;
                        $hint = '结束';
                    }
                    if (!preg_match('/^T/', $content_arr[$key][$r_key])) {
                        $this->append_error_msg($error_msg, $key, 1, "{$hint}时间需以T开头;");
                    } else {
                        $content_arr[$key][$r_key] = preg_replace('/^T/', '', $content_arr[$key][$r_key]);
                    }
                    $ret = $this->auto_convert_time($content_arr[$key][$r_key], $type);
                    if ($ret) {
                        $content_arr[$key][$r_key] = $ret;
                    }// else转换错误，保持原始值，后面的logic_check会校验原始格式
                }
            }
        }
        return $error_msg;
    }
    
    // 统一的逻辑检查：检查添加软件数据是否合法
    function logic_check(&$content_arr) {
        // 初始化错误数组
        $error_msg = array();
        foreach($content_arr as $key => $value) {
            $this->init_error_msg($error_msg, $key);
        }
        // 业务逻辑：
        $feature_soft_model = M('feature_soft');
        $feature_model = M('feature');
        $soft_model = M("soft");//软件大表
        // 业务逻辑：以下为各项具体检查
        foreach($content_arr as $key=>$record) {
            // 检查是不是编辑，如果是编辑，给record增加extent_id字段并分配其在表里的值
            if (isset($record['id'])) {
                $where = array('id' => array('EQ', $record['id']));
                $find = $feature_soft_model->where($where)->find();
                $content_arr[$key]['feature_id'] = $find['feature_id'];
                $record['feature_id'] = $find['feature_id'];
            }
            // 检查专题id是否有效
            if (isset($record['feature_id']) && $record['feature_id'] != "") {
                $where = array(
                    'feature_id' => array('EQ', $record['feature_id']),
                    //'status' => array('EQ', 1),//停用的专题也就是status=0的专题也可以添加数据，产品文档要求的
                );
                $find = $feature_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "专题id不存在;");
                } else {
                    $content_arr[$key]['bk_feature_id'] = $record['feature_id'];
                    // 如果传了专题类别名称，还需要检查与feature_id是否匹配
                    if (isset($record['name'])) {
                        if ($find['name'] != $record['name']) {
                            $this->append_error_msg($error_msg, $key, 1, "专题类别ID与专题类别名称不对应;");
                        }
                    }
					//如果传了所属段落，还要检查feature_id是否有段落匹配
					if($record['feature_graphic_rank']!=0)
					{
						$graphic_where = array(
							'feature_id' => array('EQ', $record['feature_id']),
							'rank' => $record['feature_graphic_rank'],
							'status' =>1,
							//'status' => array('EQ', 1),//停用的专题也就是status=0的专题也可以添加数据，产品文档要求的
						);
						$feature_graphic = $feature_model->table('sj_feature_graphic')->where($graphic_where)->select();
						if(!$feature_graphic)
						{
							$section = array('','一','二','三','四','五','六','七','八','九','十');
							$this->append_error_msg($error_msg, $key, 1, "图文第".$section[$record['feature_graphic_rank']]."段落不存在;");
						}
						else
						{
							$content_arr[$key]['bk_feature_graphic_id'] = $feature_graphic[0]['id'];
						}
					} else {
						$content_arr[$key]['bk_feature_graphic_id'] = 0;
					}
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "专题id不能为空;");
            }
            // 检查包名是否在sj_soft表里
            if (isset($record['package']) && $record['package'] != "") {
                $where = array(
                    'package' => $record['package'],
                    'status' => 1,
                    //'hide' => array('in', array(1, 1024)),
                );
                $find = $soft_model->where($where)->find();
                if (!$find) {
                    $this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
            }
            // 检查开始时间
            if (isset($record['start_tm']) && $record['start_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['start_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                } else {
                    $time = strtotime($record['start_tm']);
                    if ($time) {
                        $content_arr[$key]['bk_start_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "开始时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "开始时间不能为空;");
            }
            // 检查结束时间
            if (isset($record['end_tm']) && $record['end_tm'] != "") {
                if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}\s+\d{1,2}:\d{1,2}:\d{1,2}$/", $record['end_tm'])) {
                    $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                } else {
                    $time = strtotime($record['end_tm']);
                    if ($time) {
                        $content_arr[$key]['bk_end_time'] = $time;
                    } else {
                        $this->append_error_msg($error_msg, $key, 1, "结束时间日期格式不对;");
                    }
                }
            } else {
                $this->append_error_msg($error_msg, $key, 1, "结束时间不能为空;");
            }
            // 检查开始时间是否小于结束时间
            if (isset($content_arr[$key]['bk_start_time']) && isset($content_arr[$key]['bk_end_time'])) {
                if ($content_arr[$key]['bk_start_time'] > $content_arr[$key]['bk_end_time']) {
                    $this->append_error_msg($error_msg, $key, 1, "开始时间需小于结束时间;");
                }
            }
            // 检查简介长度
            if (isset($record['remark']) && $record['remark'] != "") {
                if (mb_strlen($record['remark'], 'utf-8') > 20 || mb_strlen($record['remark'], 'utf-8') < 10){
                    $this->append_error_msg($error_msg, $key, 1, "软件简介限制10-20个不为空的字;");
                }
            }
			//专题改版  新增加推荐理由和推荐人群和软件推荐包名
			 // 检查推荐理由
            if (isset($record['recommend_reason']) && $record['recommend_reason'] != "") {
                if (mb_strlen($record['recommend_reason'], 'utf-8') > 20 || mb_strlen($record['recommend_reason'], 'utf-8') < 10){
                    $this->append_error_msg($error_msg, $key, 1, "软件推荐理由限制10-20个不为空的字;");
                }
            }
			else
			{
				$this->append_error_msg($error_msg, $key, 1, "推荐理由不能为空;");
			}
			 // 检查推荐人群
            if (isset($record['recommend_person']) && $record['recommend_person'] != "") {
                if (mb_strlen($record['recommend_person'], 'utf-8') > 20 || mb_strlen($record['recommend_person'], 'utf-8') < 10){
                    $this->append_error_msg($error_msg, $key, 1, "软件推荐人群限制10-20个不为空的字;");
                }
            }
			else
			{
				$this->append_error_msg($error_msg, $key, 1, "推荐人群不能为空;");
			}
            // 检查排序是否为正整数
            if (!isset($record['rank']) || $record['rank'] == '') {
                $this->append_error_msg($error_msg, $key, 1, "排序不能为空;");
            } else {
                if (!preg_match("/^[1-9]\d*$/", $record['rank'])) {
                    $this->append_error_msg($error_msg, $key, 1, "排序格式不对，需为正整数;");
                }
            }
        }
        
        // 检查行与行之间的数据是否冲突（主要检查相同包名的区间是否有冲突）
        foreach($content_arr as $key1=>$record1) {
            // 如果填写时间的不完善，则不比较
            if (!isset($record1['bk_start_time']) || !isset($record1['bk_end_time']))
                continue;
            foreach($content_arr as $key2=>$record2) {
                // 比较过的不比较
                if ($key1 >= $key2)
                    continue;
                // 包名不相同
                if ($record1['package'] != $record2['package'])
                    continue;
                // 如果专题id不相等，则不比较
                if ($record1['feature_id'] != $record2['feature_id'])
                    continue;
                // 如果填写时间的不完善，则不比较
                if (!isset($record2['bk_start_time']) || !isset($record2['bk_end_time']))
                    continue;
                // 时间是否交叉
                if ($record1['bk_start_time'] <= $record2['bk_end_time'] && $record2['bk_start_time'] <= $record1['bk_end_time']) {
                    $k1 = $key1 + 1; $k2 = $key2 + 1;
                    $this->append_error_msg($error_msg, $key1, 1, "投放时间区间与第{$k2}行有重叠;");
                    $this->append_error_msg($error_msg, $key2, 1, "投放时间区间与第{$k1}行有重叠;");
                }
            }
        }
        
        // 检查每一行数据是否与数据库的存储内容相冲突
        foreach($content_arr as $key=>$record) {
            if (!isset($record['bk_feature_id']))
                continue;
            // 业务逻辑：如果填写时间的不完善，则不比较
            if (!isset($record['bk_start_time']) || !isset($record['bk_end_time']))
                continue;
            $start_time = $record['bk_start_time'];
            $end_time = $record['bk_end_time'];
            $where = array(
                'package' => array('EQ', $record['package']),
                'status' => array('NEQ', 0),
                'feature_id' => array('EQ', $record['feature_id']),
                'start_tm' => array('ELT', $end_time),
                'end_tm' => array('EGT', $start_time),
            );
            if (isset($record['id'])) {
                $where['id'] = array('NEQ', $record['id']);
            }
            $db_records = $feature_soft_model->where($where)->select();
            // 有冲突的后台记录
            foreach($db_records as $db_key=>$db_record) {
                $start_at_str = date('Y-m-d H:i:s',$db_record['start_tm']);
                $end_at_str = date('Y-m-d H:i:s',$db_record['end_tm']);
                $status_paused_hint = "";
                if ($db_record['status'] == 2) {
                    $status_paused_hint = "，该记录处于已停用状态，请前往批量明细列表中操作";
                }
                $this->append_error_msg($error_msg, $key, 1, "投放区间与ID为【{$db_record['id']}】，包名为【{$db_record['package']}】的记录有重叠（该投放时间从【{$start_at_str}】到【{$end_at_str}】{$status_paused_hint}）;");
            }
        }
        return $error_msg;
    }
    
    // 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        // $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
            }
        }
        return $content_arr;
    }
    
    function import_array_convert_and_check(&$content_arr) {
        // 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
        $error_msg1 = $this->handwriting_convert_and_check($content_arr);
        // 文件转换数据后的检查（区间是否有效、排期是否冲突等）
        $error_msg2 = $this->logic_check($content_arr);
        // 将$error_msg2合并到$error_msg1里并返回$error_msg1
        //屏蔽软件上排期时报警需求 新增  yuesai
		$AdSearch = D("Sj.AdSearch");
        $error_msg3 = $AdSearch->logic_check_shield($content_arr,'start_tm','end_tm','',array('0'=>1));
        foreach($error_msg2 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        foreach($error_msg3 as $key=>$value) {
            if (!array_key_exists($key, $error_msg1)) {
                $this->init_error_msg($error_msg1, $key);
            }
            $this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
        }
        return $error_msg1;
    }
    // 下载批量导入模版
    function down_moban() {
        $file_dir = C("ADLIST_PATH") . "zhuanti_import_moban.csv";
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode('专题批量导入模版') . ".csv");
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } else {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
    }
    
    // 批量导入访问的页面节点（只是批量导入装机必备的软件）
    function import_softs() {
        if ($_GET['down_moban']) {
            $this->down_moban();
        } else if ($_FILES) {
            $err = $_FILES["upload_file"]["error"];
            if ($err) {
                $this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
            }
            $file_name = $_FILES['upload_file']['name'];
            $tmp_arr = explode(".", $file_name);
            $name_suffix = array_pop($tmp_arr);
            if (strtoupper($name_suffix) != "CSV") {
                $this->ajaxReturn("",'请上传CSV格式文件！', -2);
            }
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $content_arr = $this->import_file_to_array($tmp_name);
            if ($content_arr == -1) {
                $this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
            } else if (empty($content_arr)) {
                $this->ajaxReturn("",'文件数据内容不能为空！', -4);
            }
            // 返回检查结果的错误信息，如果记录的flag为1表示有错误
            $error_msg = $this->import_array_convert_and_check($content_arr);
            $flag = true;
            foreach($error_msg as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            if (!$flag) {
                $this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
            }
            // 判断后台有没有人正在导入
            $lock_name = 'sj_feature_soft_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $fail_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            $flag = true;
            foreach($fail_arr as $key=>$value) {
                if ($value['flag'] == 1)
                    $flag = false;
            }
            // save the import file for backups
            $save_dir = IMPORT_FILE_UPLOAD_PATH;
            $this->mkDirs($save_dir);
            $save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
            $save_file_name = $save_dir . $save_name;
            move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
            $this->writelog("专题：批量导入了{$save_file_name}。");
            if ($flag) {
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($fail_arr,'存在部分导入失败记录！', -6);
            }
        } else {
            $this->display("import_softs");
        }
    }
    
    // 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
    function process_import_array($content_arr) {
        $fail_arr = array();
        $model = M('feature_soft');
        foreach($content_arr as $key => $record) {
            $map = array();
            // 设置默认值
			$map['status'] = 1;
            // 赋值，以下为必填的值
			$map['feature_id'] = $record['feature_id'];
			$map['package'] = $record['package'];
			$map['start_tm'] = strtotime($record['start_tm']);
			$map['end_tm'] = strtotime($record['end_tm']);
			$map['admin_id'] = $_SESSION['admin']['admin_id'];
            // 以下为非必须的值
			//填写了所属段落需要进行保存图文id 修改feature的rank也有所不同
			$map['feature_graphic_id'] = $record['bk_feature_graphic_id'];
			if($record['feature_graphic_rank']!=0)
			{
				/*$where_graphic = array(
					'feature_id' =>$record['feature_id'],
					'rank' =>$record['feature_graphic_rank'],
				);
				$find_id = $model->table('sj_feature_graphic')->where($where_graphic)->find();
				$map['feature_graphic_id'] = $find_id['id'];*/
				if(isset($record['rank']))
				{
					$map['rank'] = ($record['feature_graphic_rank']*100)+(int)$record['rank'];
					$map['pos'] = $record['rank'];
				}
				else
				{
					$map['rank'] = ($record['feature_graphic_rank']*100);
				}
			}
			else
			{
				if (isset($record['rank']))
				{
					$map['pos'] = $record['rank'];
					$map['rank'] = $record['rank'];
				}
			}
            if (isset($record['special']))
                $map['special'] = $record['special'];
            if (isset($record['remark']))
                $map['remark'] = $record['remark'];
			//专题改版 新增加推荐包名，推荐理由和推荐人群
			if (isset($record['recommend_soft_name']))
                $map['recommend_soft_name'] = $record['recommend_soft_name'];
			if (isset($record['recommend_reason']))
                $map['recommend_reason'] = $record['recommend_reason'];
			if (isset($record['recommend_person']))
                $map['recommend_person '] = $record['recommend_person'];
			$map['type'] = isset($record['type']) ? $record['type'] : 0;

            
            // 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog("推荐管理-专题软件列表-添加了ID为[{$id}]包名为{$map['package']}的专题软件",'sj_feature_soft',$id,__ACTION__ ,"","add");
                //$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_soft/extent_id/'.$record['extent_id']);
				//$this->success('添加成功');
                $fail_arr[$key]['flag'] = 0;
                $fail_arr[$key]['msg'] = "添加成功";
			} else {
				//$this->error('添加失败');
                // 未知原因添加失败
                $fail_arr[$key]['flag'] = 1;
                $fail_arr[$key]['msg'] = "添加失败";
			}
        }
        return $fail_arr;
    }

}

