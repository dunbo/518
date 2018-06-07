<?php
/**
 * 安智网产品管理平台 产品相关管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:高硕 2011.5.16
 * ----------------------------------------------------------------------------
*/
class ProductsAction extends CommonAction {
	private $lists;
    //皮肤管理_皮肤查看列表
	function skin(){
		$Form = M("market_skin");
		import("@.ORG.Page");
		$count=$Form->where('status=1')->count();
		$p=new Page($count,15);
		$list=$Form->where(array('status' => 1,'skin_version'=>1))->limit($p->firstRow.','.$p->listRows)->order('id desc')->select();
	
		$i=0;
		$pic_model = M("pic_category");
		foreach($list as $v){
			$list[$i]['upload_tm']=date("Y-m-d",$v['upload_tm']);
			$i++;
		}
		
		foreach($list as $v){
			$where['id'] = $v['pic_id'];
			$pic_name = $pic_model -> where($where) -> select();
			$v['pic_name'] = $pic_name[0]['pic_name'];
			$list_go[] = $v;
		}


			$p->setConfig('header','篇记录');
			$p->setConfig('prev',"上一页");
			$p->setConfig('next','下一页');
			$p->setConfig('first','首页');
			$p->setConfig('last','末页');
			$page = $p->show ();

			$this->assign( "page", $page );
			$this->assign ( "list", $list_go );
			$this->display('skin');
	}
    //皮肤管理_上传皮肤_提交
	public function skin_insert() {
        if(!empty($_FILES['apk']['size'])) {
            //APK上传
            $path = date('Ym/d/');
        	$config = array(
        		'savePath' => UPLOAD_PATH. '/apk/'. $path,
        		'saveRule' => 'packagename'
        	);
            $this->lists=$this->_uploadapk(false, $config);
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，APK必须上传');
        }

        $soft_db=M('soft');
        $map['softname']='皮肤预留';
        $map['status']=0;
        $map['hide']=0;
        $softid=$soft_db->add($map);
        if(empty($softid)) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，出了一点小问题');
        }
        $soft_db->where('softid='.$softid)->delete();

		$Form = M("market_skin"); // 实例化User对象


		$_POST['id'] = $softid;
		$_POST['version_code'] = $this->lists[apk][0]["versionCode"];
		$_POST['iconurl'] = $this->lists[apk][0]["iconurl_db"];
		$_POST['package'] = $this->lists[apk][0]["packagename"];
		$_POST['apkurl'] = $this->lists[apk][0]["apkurl_db"];
		$_POST['apksize'] = $this->lists[apk][0]["size"];
		$_POST['version'] = $this->lists[apk][0]["versionName"];
		$_POST['status'] = 1;
		$_POST['skin_version'] = 1;
		$_POST['upload_tm']=time();
		$_POST['last_refresh']=time();
		$Form -> create();
		$ret=$Form->add(); // 写入用户数据到数据库
        $this->writelog('上传了ID为'.$softid.'包名为'.$_POST['package'].'的皮肤','sj_market_skin',$ret,__ACTION__ ,"","add");
		$this->success("数据保存成功！");
	}
    //皮肤管理_上传皮肤_显示
	function skin_add(){
		$Fo = M("pu_config");
		$fw=$Fo->query('select * from pu_config where status=1 and config_type="firmware"');
		$pic_model = M("pic_category");
		$where['status'] = 1;
		$pic_list = $pic_model -> where($where) -> select();
		$this -> assign('pic_category',$pic_list);
		$this->assign('fw',$fw);
		$this->display('skin_add');
	}
    //皮肤管理_皮肤编辑_提交
	public function skin_update() {
		$Form = D("market_skin");
		if(!empty($_FILES['apk']['size'])) {
            //APK上传
            $path = date('Ym/d/');
        	$config = array(
        		'savePath' => UPLOAD_PATH. '/apk/'. $path,
        		'saveRule' => 'packagename'
        	);
        	
            $this->lists=$this->_uploadapk(false, $config);
			$_POST['version_code'] = $this->lists[apk][0]["versionCode"];
			$_POST['iconurl'] = $this->lists[apk][0]["iconurl_db"];
			$_POST['package'] = $this->lists[apk][0]["packagename"];
			$_POST['apkurl'] = $this->lists[apk][0]["apkurl_db"];
			$_POST['apksize'] = $this->lists[apk][0]["size"];
			$_POST['version'] = $this->lists[apk][0]["versionName"];
        }
		$_POST['status'] = 1;
		$_POST['last_refresh']=time();
		$Form->create();
		$where['id'] = $_POST['id'];
		$log = $this->logcheck(array('id'=>$_POST['id']),'sj_market_skin',$Form->create(),$Form);
		$list=$Form->where($where) -> save();
		if($list!==false){
            //$this->writelog('编辑了ID为'.$_POST['id'].'包名为'.$_POST['package'].'的皮肤');
            $this->writelog("皮肤管理_皮肤查看列表编辑了ID为'".$_POST['id']."'".$log,'sj_market_skin',$_POST['id'],__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/skin/');
			$this->success('数据更新成功');
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/skin/');
			$this->error('数据更新失败');
		}
	}
	// 皮肤管理_皮肤编辑_显示
	public function skin_edit() {
		if(!empty($_GET['id'])) {
			$Form = M("market_skin");
			$vo = $Form->where(array("id"=>$_GET['id']))->select();
			$pic_model = M("pic_category");
			$where['status'] = 1;
			$pic_category = $pic_model -> where($where) -> select();
			if($vo) {
				$this->assign('pic_category',$pic_category);
				$this->assign('vo',$vo);
				$this->display('skin_edit');
			}else{
				exit('编辑项不存在！');
			}
		}else{
			exit('编辑项不存在！');
		}
	}
    //皮肤管理_皮肤删除
	public function skin_del() {
		if(!empty($_GET['id'])) {
			$Form	=	M("market_skin");
            $thumb='';
            $thumb['status']=0;
			$result	=	$Form->where(array('id'=>(int)$_GET['id']))->save($thumb);
			if(false != $result)
			{
                $this->writelog('删除了ID为'.$_GET['id'].'的皮肤','sj_market_skin',$_GET['id'],__ACTION__ ,"","del");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/skin/');
				$this->success('删除成功');
			}else
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/skin/');
				$this->error('删除失败');
			}
		}else{
			$this->error('删除项不存在！');
		}
	}

//===================================================================================================
	//市场更新管理_市场查看列表
	function marketupdate(){
	//上传显示页
		$infowhere = 'status=1';
		
		if($_SESSION['market_soso']) {
			if(strstr('通用更新渠道',$_SESSION['market_soso']))
			{
				$this->assign("tongyong",'1');
			}else{
				$infowhere .= " and chname like '%{$_SESSION['market_soso']}%'";
			}
			$this->assign("soso",$_SESSION['market_soso']);
			$this->assign("so",'1');
		}
		
		if($_SESSION['market_note'])
			$this->assign("note1",$_SESSION['market_note']);
		if($_SESSION['market_version_code'])
			$this->assign("version_code1",$_SESSION['market_version_code']);
		if($_SESSION['market_version_name'])
			$this->assign("version_name1",$_SESSION['market_version_name']);
		if($_SESSION['market_target_version_code'])
			$this->assign('target_version_code1',$_SESSION['market_target_version_code']);		
		
		if($_POST)
		{
			if($_POST['soso']) {
				if(strstr('通用更新渠道',$_POST['soso']))
				{
					$this->assign("tongyong",'1');
				}else{
					$infowhere .= " and chname like '%{$_POST['soso']}%'";
				}
				$this->assign("soso",$_POST['soso']);
			}
			if($_POST['note1'])
				$this->assign("note1",$_POST['note1']);
			if($_POST['version_code1'])
				$this->assign("version_code1",$_POST['version_code1']);
			if($_POST['version_name1'])
				$this->assign("version_name1",$_POST['version_name1']);
			if($_POST['target_version_code1'])
				$this->assign('target_version_code1',$_POST['target_version_code1']);		
			$this->assign("so",'1');
			
		}
		$Form = M("channel");
		
		$info = $Form->where($infowhere)->select();
		$i =0 ;
		foreach($info as $v)
		{
			if($v['only_auth'] == 1) $info[$i]['only_auth'] = '显示';
			else $info[$i]['only_auth'] = '不显示';
			$i ++;
		}
		$Fo = M("pu_config");
		$firmware=$Fo->query('select * from pu_config  where status=1 and config_type="firmware"');
		//print_R($firmware);
		$len = count($firmware);
		for ($i=1;$i<=$len;$i++){
			if($i%3 == 0){
				$firmware[$i-1]['configcontent'] .= '<br />';
			}
		}
		$this->assign("firmware",$firmware);
		if(count($info) == 0){
			$this->error('无相关渠道');
		}
		$this->assign("info",$info);
	    //列表显示页
		$Form = M("market");
		import("@.ORG.Page");
		$form = M("channel");
		$where = "status=1";
		
		
		$platform = '';
		if($_GET['chlsoso']=="chlsoso"){
			if($_GET['platform']){
				$platform = $_GET['platform'] ;
				$zh_platform=escape_string($platform);
				$where .= " and platform = '{$zh_platform}'";
				$this->assign("platform", $platform);
			}

			if($_GET['version_code']){
				$zh_version_code=escape_string($_GET['version_code']);
				$where .= " and version_code = {$zh_version_code}";
				$this->assign("version_code",$_GET['version_code']);
			}
			if($_GET['target_version_code']){
				$zh_target_version_code=escape_string($_GET['target_version_code']);
				$where .= " and target_version_code like '%{$zh_target_version_code}%'";
				$this->assign("target_version_code",$_GET['target_version_code']);
			}
			if($_GET['version_name']){
				$zh_version_name=escape_string($_GET['version_name']);
				$where .= " and version_name like '%{$zh_version_name}%'";
				$this->assign("version_name",$_GET['version_name']);
			}
			
			if($_GET['force_update']!=2){
				if($_GET['force_update'] == 1){
					$where .= " and force_update = 1";
					$this->assign("force_update","1");
				}
				else if($_GET['force_update'] == 0){
					$where .= " and force_update <> 1";
					$this->assign("force_update","2");
				}
			}
			if($_GET['chl']){
				if($_GET['chl'] == "通用" ){
					$where .= " and cid=0";
				}else{
					$chl = escape_string($_GET['chl']);
					$ret = $form->where(array("chname"=> array("like","%".$chl."%")))->findAll();
					$cid .= "";
					foreach($ret as $key => $value){
						$cid .= ",".$value['cid'];
					}
					$where .= " and cid in (".substr($cid,1).")";
				}
			}
			if($_GET['wifi_load']){
				$wifi_load=escape_string($_GET['wifi_load']);
				$where .= " and wifi_load = {$wifi_load}";
				$this->assign("wifi_load",$_GET['wifi_load']);
			}
		}
		$count=$Form->where($where)->count();
		$Page=new Page($count,15);
		$list=$Form->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();

		for($i=0;$i<count($list);$i++){
			$list[$i]['cname']=$form->where('cid="'.$list[$i]['cid'].'"')->getField('chname');

			if(empty($list[$i]['cname']))
			{
				$list[$i]['cname']='通用';
			}
		}
		$j =0 ;
		foreach($list as $v)
		{	if($v['force_update'] == 1) $list[$j]['force_update'] = '更新';
			else $list[$j]['force_update'] = '不更新';
			$list[$j]['last_refresh'] = date("Y-m-d H:i:s",$list[$j]['last_refresh']);
			$j ++;
		}
		$Page->setConfig('header','篇记录');
		$Page->setConfig('prev',"上一页");
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','末页');
        $show =$Page->show();
        $this->assign ("page", $show );
		$this->assign("apkurl",IMGATT_HOST);
		$this->assign("list",$list);

		$util = D('Sj.Util');
		$this->assign('product_list',$util->getProducts($platform));
		$this->display('marketupdate');
	}
    //市场更新管理_上传市场_提交
    //Edit By Jinshan 2011/5/24
	public function marketupdata_insert() {


		$map['version_code'] = $_POST['version_code'];
		$map['version_name'] = $_POST['version_name'];
		$map['force_update'] = $_POST['force_update'];

		if($_POST['cid']){
			$map['cid'] = $_POST['cid'];
		}else{
			$map['cid'] = 0;
		}
		$map['note'] = $_POST['note'];
		$map['platform'] = $_POST['platform'];

		$map['wifi_load'] = $_POST['wifi_load'];
		$_SESSION['market_note'] = $_POST['note'];
		$_SESSION['market_version_code'] = $_POST['version_code'];
		$_SESSION['market_version_name'] = $_POST['version_name'];
		$_SESSION['market_soso'] = $_POST['sosoi'];

        if(empty($map['version_code']) || empty($map['version_name'])) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，版本号及版本名称必须填写');
        }
        if(strlen($_POST['version_name'])>30) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，版本名称不得长于30个字符，10个汉字');
        }
        if(strlen($_POST['note'])>3000) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，版本名称不得长于3000个字符，500个汉字');
        }

		
       if(!empty($_FILES['apk']['size'])) {
        //APK上传
        	$path = date("Ym/d/");
        	$config = array(
        		'savePath' => UPLOAD_PATH. '/apk/'. $path,
        	);
            $this->lists=$this->_uploadapk(false, $config);
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，APK必须上传');
        }
        if (isset($_POST['target_version_code'])) {
            $tvc = trim($_POST['target_version_code']);
            for ($i = 0; $i < strlen($tvc); $i++) {
                $c = ord($tvc[$i]);
                if (($c < 48 || $c > 57) && ($c != 44)) {
                    $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
                    $this->error('针对版本只允许输入数字和逗号。');
                }
            }
            $arr = array();
            $temp = explode(',', $tvc);
            foreach ($temp as $c) {
                if (strlen($c) > 0)
                    $arr[] = $c;
            }
            if (!empty($arr)) {
                $map['target_version_code'] = ','. implode(',', $arr). ',';
            }
        }
        $market_update_info = $_POST['did'];
		$Form = M("market"); // 实例化User对象
		$map['iconurl'] = $this->lists[apk][0]["iconurl_db"];
		
		$map['apkurl'] = $this->lists[apk][0]["apkurl_db"];
		$map['apksize'] = $this->lists[apk][0]["size"];
		//$icon_original = $this->lists[apk][0]["packagename"]."_".time()."_0".substr($this->lists[apk][0]["iconurl_db"], strrpos($this->lists[apk][0]["iconurl_db"], '.'));
		//copy('/data/att/m.goapk.com/'.$this->lists[apk][0][icon_original],'/data/att/m.goapk.com/icon/'.$path.$icon_original);
		$map['icon_original'] = $this->lists[apk][0][apk_icon_db];
        $map['status'] = 1;
		$map['submit_tm']=time();
		$map['last_refresh']=time();
		$map['firmware']=$_POST['firmware'];
        sort($map['firmware']);
		$map['firmware']=','.implode(',', $_POST['firmware']).',';
		$map['exclude_did']=','.implode(',', $_POST['did']).',';
		$Form -> create();
		$idthumb=$Form->add($map); // 写入用户数据到数据库
		if (!$idthumb) {//如果插入失败删除掉文件
		    unlink(UPLOAD_PATH.'/'.$map['apkurl']);
		    $this->error("数据保存失败！");
		}
		//渠道版本同步通知
		$ucenter = C('ucenter');
		$data_array = array(
			'data'=>array(
				'serviceId' =>$ucenter['client_serviceId'],
				'syncDataType' => 2,
				//'standAlone' => 1,
				'cid' => $_POST['cid'],
			),
		);	
		request_task(C('ucenter_api'),$data_array);			
		$task_client = get_task_client();
		$task_client->doBackground("market_incremental_update",json_encode(array('id'=>$idthumb,'oid'=>$market_update_info)));
		include_once SERVER_ROOT. '/tools/functions.php';
		go_make_links(UPLOAD_PATH.$this->lists[apk][0]["apkurl_db"]);
		go_make_links(UPLOAD_PATH.$this->lists[apk][0]["iconurl_db"]);
        $this->writelog('上传了ID为['.$idthumb.']包名为['.$map['package'].']的市场更新','sj_market',$idthumb,__ACTION__ ,"","add");
		$this->success("数据保存成功！");
	}
    //市场更新管理_编辑_显示
	public function marketupdate_edit() {
		if(!empty($_GET['id'])) {
            $Form = M("channel");
            $info = $Form->where('status=1')->select();
            $i =0 ;
            foreach($info as $v)
            {
                if($v['only_auth'] == 1) $info[$i]['only_auth'] = '显示';
                else $info[$i]['only_auth'] = '不显示';
                $i ++;
            }
		    $this->assign("info",$info);

			$Fo = M("pu_config");
			$fw=$Fo->query('select * from pu_config where status=1 and config_type="firmware"');


            $map='';
            $map['status']=1;
            $map['id']=$_GET['id'];
            $Form = M("market");

			$vo = $Form->where($map)->select();

			$vv=explode(',',$vo[0]['firmware']);
			for($i=0;$i<count($fw);$i++)
			{
				for($j=0;$j<count($vv);$j++)
				{
					if($fw[$i]['configname']==$vv[$j])
					{
						$fw[$i]['type']=1;
					}

				}
			}

			$len = count($fw);
			for ($i=1;$i<=$len;$i++){
				if($i%3 == 0){
					$fw[$i-1]['configcontent'] .= '<br />';
				}
			}

			$this->assign('fw',$fw);
			if($vo) {
				if($vo[0]['cid']==0){
					$vo[0]['chname']="通用";
				}else{
				    $vo[0]['chname']=$Fo->table("sj_channel")->where(array("status"=>1,"cid"=>$vo[0]['cid']))->getfield("chname");
				}
				$this->assign('vo',$vo[0]);
				$this->display('marketupdate_update');
			}else{
			    exit('编辑项不存在！');
			}
		}else{
		    exit('编辑项不存在！');
		}
	}
	//市场更新管理_编辑_提交
	public function marketupdate_update() {
	    $Form	=	D("market");
	    $_POST['last_refresh']=time();

	    if(empty($_POST['version_code']) || empty($_POST['version_name'])) {
	        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
	        $this->error('对不起，版本号及版本名称必须填写');
	    }
	    if(strlen($_POST['version_name'])>30) {
	        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
	        $this->error('对不起，版本名称不得长于30个字符，10个汉字');
	    }
	    if(strlen($_POST['note'])>1500) {
	        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
	        $this->error('对不起，版本名称不得长于1500个字符，500个汉字');
	    }
	    sort($_POST['firmware']);
	    $_POST['firmware']=','.implode(',', $_POST['firmware']).',';

	    if (isset($_POST['target_version_code'])) {
	        $tvc = trim($_POST['target_version_code']);
	        for ($i = 0; $i < strlen($tvc); $i++) {
	            $c = ord($tvc[$i]);
	            if (($c < 48 || $c > 57) && ($c != 44)) {
	                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
	                $this->error('针对版本只允许输入数字和逗号。');
	            }
	        }
	        $arr = array();
	        $temp = explode(',', $tvc);
	        foreach ($temp as $c) {
	            if (strlen($c) > 0)
	            $arr[] = $c;
	        }
	        $_POST['target_version_code'] = !empty($arr) ? (','. implode(',', $arr). ',') : null;
	    } else {
	        $_POST['target_version_code'] = '';
	    }
	 	$data['version_code'] = $_POST['version_code'];
		$data['version_name'] = $_POST['version_name'];
		$data['note'] = $_POST['note'];//描述
		//固件版本
		$data['firmware'] = $_POST['firmware'];
		//升级针对的版本号
		$data['target_version_code'] = $_POST['target_version_code'];
		$data['last_refresh'] = $_POST['last_refresh'];
		$data['force_update'] = $_POST['force_update']; 
        $data['cid'] = $_POST['cid'];

	   	$Form->create();
	    $market_update_info = $_POST['did'];
	    $task_client = get_task_client();
	    $task_client->doBackground("market_incremental_update",json_encode(array('id'=>$_POST['id'],'oid'=>$market_update_info)));
	    $log = $this->logcheck(array('id'=>$_POST['id']),'sj_market',$data,$Form);
	    $list=$Form->where("id={$_POST['id']}")-> data($data)->save();
	    if($list!==false){
	       //$this->writelog('编辑了ID为['.$_POST['id'].']的市场更新');
	       $this->writelog($log,'sj_market',$_POST['id'],__ACTION__ ,"","edit");
		   $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/marketupdate');
		   $this->success('数据更新成功');
		}else{
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/marketupdate');
			$this->error('数据更新失败');
		}
	}

    //市场更新管理_市场更新删除
	public function marketupdate_del() {
		if(!empty($_GET['id'])) {
			$Form	=	M("market");
			$data = array(
			    'status' => 0
			);
			$result	=	$Form->where(array('id'=>(int)$_GET['id'],'status'=>1))->data($data)->save();
			if(false != $result)
			{
                $this->writelog('市场更新管理-市场查看列表删除了ID为['.$_GET['id'].']的市场更新','sj_market',$_GET['id'],__ACTION__ ,"","del");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/marketupdate');
				$this->success('删除成功');
			}else
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Products/marketupdate');
				$this->error('删除失败');
			}
		}else{
			$this->error('删除项不存在！');
		}
	}
	
	public function Start_interface()
	{
		$Form=M('splash');
    import("@.ORG.Page"); //导入分页类
    if($_GET['oldid'])
    {
    	$data = array('time_duration' => time()-1); 
    	$Form->where(array("id" =>$_GET['oldid']))->save($data);
    	$this->writelog("删除了id为{$_GET['oldid']}的数据",'sj_splash',$_GET['oldid'],__ACTION__ ,"","del");
    }
    if($_GET['delid'])
    {
    	$data = array('status' => 0); 
    	$Form->where(array("id" =>$_GET['delid']))->save($data);
    	$this->writelog("删除了id为{$_GET['delid']}的启动界面",'sj_splash',$_GET['delid'],__ACTION__ ,"","del");
    }
    $count = $Form->where("status = 1")->count();    //计算总数
    $p = new Page ( $count, 20 );
    $list=$Form->limit($p->firstRow.','.$p->listRows)->where("status = 1")->order('created_at desc')->findAll();
    $page = $p->show ();
    foreach($list as $key => $value)
    {
    	if(($value['time_started'] <= time())&&($value['time_duration'] >= time())){
    		$list[$key]['type'] = 1; //当前显示
    	}elseif(($value['time_started'] > time())&&($value['time_duration'] >= time())){
    		$list[$key]['type'] = 2;
    	}elseif(($value['time_started'] < time())&&($value['time_duration'] < time())){
    		$list[$key]['type'] = 0;
    	}else{
    		$list[$key]['type'] = 0;
    	}
		if (!empty($value['channel_id'])) {
			$value['channel_id'] = preg_replace('/^(,)/', '', $value['channel_id']);
			$value['channel_id'] = preg_replace('/(,)$/', '', $value['channel_id']);
			
			$ids = explode(',', $value['channel_id']);
			$where = array(
				'cid' => array('in', $ids)
			);
			$chs = $Form->table('sj_channel')->where($where)->field('chname')->findAll();
			$list[$key]['chname'] = '';
			foreach($chs as $item){
				$list[$key]['chname'] .= $item['chname']. ',';
			}
			
			$list[$key]['chname'] = preg_replace('/(,)$/', '', $list[$key]['chname']);
		}
		
		
    }
    $this->assign ( "page", $page );
    $this->assign ( "list", $list );
		$this->display();
	}
	
	public function Start_interface_old()
	{
		$model = M("channel");
		if($_POST['soso'])
		{
			$where = array('chname'=> array('LIKE',"%".$_POST['soso']."%"));
			$ret = $model->where($where)->findAll();
			if($ret){
				$this->assign('select','1');
				$this->assign('ret',$ret);
			}
		}
		$Form=M('splash_channel');
		if($_POST['cid'])
		{
			$cret = $model->where(array("cid"=>$_POST['cid']))->find();
			$data = array('chl' => $cret['chl'] ,'chname' => $cret['chname'] , 'status'=>1 ,'time' => time() , 'cid' => $_POST['cid'] );
			$ret=$Form->add($data);
			$this->writelog('添加了名为['.$cret['chname'].']的黑名单','sj_splash_channel',$ret,__ACTION__ ,"","add");
			$this->success('添加成功');
		}
		if($_GET['dcid'])
		{
			$data = array('status' => 0);
			$Form->where(array("id" => $_GET['dcid']))->save($data);
			$this->writelog('删除了id为['.$_GET['dcid'].']的黑名单','sj_splash_channel',$_GET['dcid'],__ACTION__ ,"","del");
			$this->success('删除成功');
		}
    import("@.ORG.Page"); //导入分页类
    $count = $Form->where("status = 1")->count();    //计算总数
    $p = new Page ( $count, 20 );
    $list=$Form->limit($p->firstRow.','.$p->listRows)->where("status = 1")->order('time desc')->findAll();
    $page = $p->show ();
    $this->assign ( "page", $page );
    $this->assign ( "list", $list );
		$this->display();
	}
	
	public function Start_interface_update()
	{
		$model = M('splash');
		$a = array(1=>'安智市场',2=>'安卓游戏',3=>'安智市场HD');
		$splash_categorys = new Model();
		$splash_category_where['_string'] = "pid in (1,6,7) and status = 1";
		$splash_result = $splash_categorys -> table('pu_product') -> where($splash_category_where) -> select();
		$this -> assign('splash_categorys',$splash_result);
		if($_GET['id'])
		{
			$ret = $model->where(array("id" =>$_GET['id']))->find();
			//print_R($ret);
			$ret[url]=IMGATT_HOST.$ret['splash_url'];
			$this->assign('ret',$ret);
			if($_POST)
			{
				
				$name = $_POST['name'];
				$file=$_POST['textpic'];
				$arrtime1 = explode('/',$_POST['time1']);
				$str = "{$arrtime1[2]}/{$arrtime1[0]}/{$arrtime1[1]} {$_POST['h1']}:{$_POST['m1']}:00" ;
				$time1=strtotime("{$arrtime1[2]}/{$arrtime1[0]}/{$arrtime1[1]} {$_POST['h1']}:{$_POST['m1']}:00");
				$arrtime2 = explode('/',$_POST['time2']);
				$time2=strtotime("{$arrtime2[2]}/{$arrtime2[0]}/{$arrtime2[1]} {$_POST['h2']}:{$_POST['m2']}:00");
				if(($model->where("(time_duration > $time1 and time_started < $time1 and status = 1 and id != {$_GET['id']}) or (time_duration > $time2 and time_started < $time2 and status = 1  and id != {$_GET['id']}) or (time_started > $time1 and time_started < $time2 and status = 1  and id != {$_GET['id']}) or (time_duration > $time1 and time_duration < $time2 and status = 1  and id != {$_GET['id']})")->find()?false:true)){
					$data=array('status'=>1,'created_at'=> time(),'time_started' => $time,'time_started'=> $time1,'time_duration'=> $time2,'splash_url'=> $file);
					$model->where(array("id"=>$_GET['id']))->save($data);
					$this->writelog('修改了名为['.$_POST['name'].']的主题','sj_splash',$_GET['id'],__ACTION__ ,"","edit");
					$this->success('修改成功');
				}else{
					$this->error('此时间段，已经有活动页面');
				}
			}
		}else{
			if($_POST)
			{
			
				$name = $_POST['name'];
				$splash_category = $_POST['splash_category'];
				$file=$_POST['textpic'];
				$arrtime1 = explode('/',$_POST['time1']);
				$str = "{$arrtime1[2]}/{$arrtime1[0]}/{$arrtime1[1]} {$_POST['h1']}:{$_POST['m1']}:00" ;
				$time1=strtotime("{$arrtime1[2]}/{$arrtime1[0]}/{$arrtime1[1]} {$_POST['h1']}:{$_POST['m1']}:00");
				$arrtime2 = explode('/',$_POST['time2']);
				$time2=strtotime("{$arrtime2[2]}/{$arrtime2[0]}/{$arrtime2[1]} {$_POST['h2']}:{$_POST['m2']}:00");
				if(($model->where("(time_duration > $time1 and time_started < $time1 and status = 1) or (time_duration > $time2 and time_started < $time2 and status = 1) or (time_started > $time1 and time_started < $time2 and status = 1) or (time_duration > $time1 and time_duration < $time2 and status = 1)")->find()?false:true)){
					$data=array('status'=>1,'created_at'=> time(),'time_started' => $time,'time_started'=> $time1,'time_duration'=> $time2,'splash_url'=> $file,'splash_name'=> $name,'splash_category' => $splash_category);
					isset($_POST['cid']) && $data['channel_id'] = ','. implode(',', $_POST['cid']). ',';
					$ret=$model->add($data);
					$this->writelog('添加了名为['.$_POST['name'].']的主题','sj_splash',$ret,__ACTION__ ,"","add");
					$this->success('添加成功');
				}else{
					$this->error('此时间段，已经有活动页面');
				}
			}
			$this->assign('time',date('m/d/Y',time()));
		}
		$this->display();
	}
	
	public function Start_interface_updatepic()
	{
		$targetFolder = '/data/att/m.goapk.com/image/' . date ( "Ym/d", time () )."/"; // Relative to the root
		if (!empty($_FILES)) {
			$tempFile = $_FILES['pic']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			$dirname = 'data/att/m.goapk.com/image/' . date ( "Ym/d", time () );
		 	$dirarr = explode("/",$dirname);
		 	$dir="/";
		 	for ($i=0;$i<count($dirarr);$i++){
		 		$dir .= $dirarr[$i]."/";
		 		if (!is_dir($dir)){ 
		  		mkdir($dir, 0755, true);
			  }
		  }
		  $targetFile = $targetFolder . $_FILES['pic']['name'];
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['pic']['name']);
			$targetFile = $targetFolder . substr(MD5($fileParts['filename']),0,6).'_'.time().'.'.$fileParts['extension'];
			$urldir = "/image/" . date ( "Ym/d", time () )."/".substr(MD5($fileParts['filename']),0,6).'_'.time().'.'.$fileParts['extension'];
			if (in_array($fileParts['extension'],$fileTypes)) {
				if(move_uploaded_file($tempFile,$targetFile))
				{
					$return = $urldir ;
					exit($return);
				}else{
					exit('1');
				}
			} else {
				exit('1');
			}
		}
	}
	function Start_interface_lode()
	{
		$model = M('splash');
		$ret = $model->where(array("id"=>$_GET['id']))->find();
   	if(($ret['time_started'] <= time())&&($ret['time_duration'] >= time())){
   		$ret['type'] = 1; //当前显示
   	}elseif(($ret['time_started'] > time())&&($ret['time_duration'] >= time())){
   		$ret['type'] = 2;
   	}elseif(($ret['time_started'] < time())&&($ret['time_duration'] < time())){
   		$ret['type'] = 0;
   	}else{
   		$ret['type'] = 0;
   	}
		$ret[url]=IMGATT_HOST.$ret['splash_url'];
		$this->assign('ret',$ret);
		$this->display();
	}

	//安智市场-web_市场更新管理_推送行为管理页面
	function push(){
		$push_behavior = M('push_behavior');
		if($_GET['action']=='add') {
		} else if($_GET['action']=='edit') {
		} else if($_GET['action']=='oper') {
		} else {
			import('@.ORG.Page');
			$limit = 30;

			$params = array();
			if($_GET['so_beid'] && is_numeric($_GET['so_beid'])) {
				$params['beid'] = $_GET['so_beid'];
			}
			if($_GET['so_col'] && is_numeric($_GET['so_col'])) {
				$params['col'] = $_GET['so_col'];
			}
			if($_GET['so_value']) {
				$params['value'] = $_GET['so_value'];
			}
			if($_GET['so_status']==1 || $_GET['so_status']==2) {
				$params['status'] = $_GET['so_status'];
			}

			$total = $push_behavior->where($params)->count();
			$page = new Page($total, $limit, http_build_query($_REQUEST));

			$list = $push_behavior->where($params)->order("beid desc")->limit($page->firstRow.','.$page->listRows)->select();
			if($list) {
				foreach($list as $key=>$val) {
					if($val['status']==1) {	//启用
						$list[$key]['status'] = '启用';
					} else if($val['status']==2) {	//停用
						$list[$key]['status'] = '停用';
					}
				}
			}

			$page -> setConfig('header', '篇记录');
			$page -> setConfig('first', '<<');
			$page -> setConfig('last', '>>');
			$this -> assign('page', $page->show());
			if(!$_GET['p']){
				$_GET['p'] = 1;
			}
			$this -> assign('p',$_GET['p']);
			$this->assign('list', $list);
			$this->display();
		}
	}

	//安智市场-web_市场更新管理_推送行为管理页面_添加
	function push_add() {
		$push_behavior = M('push_behavior');

		$tmp = $push_behavior->where("col='{$_POST['behavior_col']}' AND value='{$_POST['behavior_value']}'")->limit(0,1)->select();
		if($tmp) {
			$this->error('添加失败，相同行为规则和行为条件的记录已存在！');
			exit;
		}
		$ret=$push_behavior->add(array('col'=>$_POST['behavior_col'],'value'=>$_POST['behavior_value'],'status'=>1));
		$this->writelog('市场更新管理-推送行为排期管理页面添加了新的推送行为ID为'.$ret,'sj_push_behavior',$ret,__ACTION__ ,"","add");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Products/push");
		$this->success("恭喜您，添加推送行为成功！");
	}

	//安智市场-web_市场更新管理_推送行为管理页面_启用停用
	function push_oper() {
		$push_behavior = M('push_behavior');

		$arr = array();
		$id_arr = array();
		$id_arr = explode(',',$_POST['beid']);
		$p = $_POST['p'];
		if($id_arr) {
			foreach($id_arr as $k=>$v) {
				if(!$v || !is_numeric($v)) unset($id_arr[$k]);
			}
		}
		if(!$id_arr) {
			$arr['succ'] = 0;
			$arr['msg'] = '请选中要操作的记录！';

			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Products/push/p/{$p}");
			$this->error("操作失败，请选中要操作的记录！");
		} else {
			$id_str = implode(',',$id_arr);
			$push_behavior->query("update sj_push_behavior set status='{$_POST['sta']}' where beid in ($id_str)");
			$this->writelog('市场更新管理-推送行为排期管理页面修改ID为'.$id_str.'状态为'.$_POST['sta']."\n".'有效:1无效:2'.'推送排期','sj_push_behavior',$id_str,__ACTION__ ,"","edit");
			$arr['succ'] = 1;
			$arr['beid'] = $id_arr;
			$arr['status'] = $_POST['sta'];

			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Products/push/p/{$p}");
			$this->success("恭喜您，操作成功！");
		}
		//exit(json_encode($arr));
	}

	//安智市场-web_市场更新管理_推送行为管理页面_启用停用
	function push_edit() {
		$push_behavior = M('push_behavior');
		$p = $_POST['edit_p'];
		$tmp = $push_behavior->where("col='{$_POST['edit_col']}' AND value='{$_POST['edit_value']}' AND beid!='{$_POST['edit_beid']}'")->limit(0,1)->select();
		if($tmp) {
			$this->error('编辑失败，相同行为规则和行为条件的记录已存在！');
			exit;
		}
		$status = $_POST['edit_status'] == '启用' ? 1 : 2;
		$data = array('beid'=>$_POST['edit_beid'],'col'=>$_POST['edit_col'],'value'=>$_POST['edit_value'],'status'=>$status);
		$log = $this->logcheck(array('beid'=>$_POST['edit_beid']),'sj_push_behavior',$data,$push_behavior);
		$push_behavior->query("update sj_push_behavior set col='{$_POST['edit_col']}',value='{$_POST['edit_value']}',status='{$status}' where beid='{$_POST['edit_beid']}'");
		$this->writelog("编辑了beid为'".$_POST['edit_beid']."'".$log,'sj_push_behavior',$_POST['edit_beid'],__ACTION__ ,"","add");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Products/push/p/".$p);

		$this->success("恭喜您，编辑推送行为成功！");
	}

	//安智市场-web_市场更新管理_推送行为排期管理页面
	function pushwait(){
		date_default_timezone_set('Asia/Shanghai');
		$push_detail = M('push_be_detail');
		if($_GET['action']=='add') {
		} else if($_GET['action']=='edit') {
		} else if($_GET['action']=='oper') {
		} else {
			import('@.ORG.Page');
			$limit = 30;

			$params = array();
			$_sql = '';
			if($_GET['so_id'] && is_numeric($_GET['so_id'])) {
				$params['id'] = $_GET['so_id'];
				$_sql = "id='{$_GET['so_id']}'";
			}
			if($_GET['so_beid'] && is_numeric($_GET['so_beid'])) {
				$params['beid'] = $_GET['so_beid'];
				$_sql .= ($_sql ? ' AND ' : '')."beid='{$_GET['so_beid']}'";
			}
			if($_GET['so_package']) {
				$params['package'] = $_GET['so_package'];
				$_sql .= ($_sql ? ' AND ' : '')."package='{$_GET['so_package']}'";
			}
			if($_GET['begintime'] && $_GET['endtime']) {
				$params['start'] = strtotime($_GET['begintime'].' 00:00:00');
				$params['end'] = strtotime($_GET['endtime'].' 23:59:59');
				$_sql .= ($_sql ? ' AND ' : '')."(start between {$params['start']} and {$params['end']} OR end between {$params['start']} and {$params['end']})";
			}
			if(isset($_GET['so_status']) && is_numeric($_GET['so_status']) && ($_GET['so_status']==0 || $_GET['so_status']==1)) {
				$params['status'] = $_GET['so_status'];
				$_sql .= ($_sql ? ' AND ' : '')."status='{$_GET['so_status']}'";
			}

			$total = $push_detail->where($_sql)->count();
			$page = new Page($total, $limit, http_build_query($_REQUEST));

			$list = $push_detail->where($_sql)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
			if($list) {
				foreach($list as $key=>$val) {
					if($val['status']==1) {	//启用
						$list[$key]['status'] = '启用';
					} else if($val['status']==0) {	//停用
						$list[$key]['status'] = '停用';
					}
					$list[$key]['start'] = date('Y-n-j',$val['start']);
					$list[$key]['end'] = date('Y-n-j',$val['end']);
				}
			}

			//行为管理
			$push_behavior = M('push_behavior');
			$behavior = $push_behavior->where("status=1")->order("beid desc")->select();

			$page -> setConfig('header', '篇记录');
			$page -> setConfig('first', '<<');
			$page -> setConfig('last', '>>');
			$this -> assign('page', $page->show());
			$this -> assign('behavior',$behavior);
			if(!$_GET['p']){
				$_GET['p'] = 1;
			}
			$this -> assign('p',$_GET['p']);
			$this->assign('list', $list);
			$this->display();
		}
	}

	//安智市场-web_市场更新管理_推送行为排期管理页面_启用停用
	function pushwait_oper() {
		$push_detail = M('push_be_detail');
		$arr = array();
		$id_arr = array();
		$id_arr = explode(',',$_POST['id']);
		$p = $_POST['p'];
		
		if($id_arr) {
			foreach($id_arr as $k=>$v) {
				if(!$v || !is_numeric($v)) unset($id_arr[$k]);
			}
		}
		if(!$id_arr) {
			$arr['succ'] = 0;
			$arr['msg'] = '请选中要操作的记录！';
		} else {
			$id_str = implode(',',$id_arr);
			$package = $push_detail -> where(array("id" =>array('in',$id_str))) -> select();
			$packagestr = array();
			foreach($package as $v){
				$packagestr[] = $v['package'];
			}
			$push_detail->query("update sj_push_be_detail set status='{$_POST['sta']}' where id in ($id_str)");
			$this->writelog('市场更新管理-推送行为排期管理页面修改ID为'.$id_str.'包名为'.implode(',',$packagestr).'状态为'.$_POST['sta']."\n".'停用:0启用:1'.'推送排期','sj_push_be_detail',$id_str,__ACTION__ ,"","edit");
			$arr['succ'] = 1;
			$arr['id'] = $id_arr;
			$arr['status'] = $_POST['sta'];
		}
		//exit(json_encode($arr));
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Products/pushwait/p/{$p}");
		$this->success("恭喜您，操作成功！");
	}

	//安智市场-web_市场更新管理_推送行为排期管理页面_添加
	function pushwait_add() {
		$push_detail = M('push_be_detail');
		$model = new Model();
		$start = strtotime($_POST['add_start'].' 00:00:00');
		$end = strtotime($_POST['add_end'].' 23:59:59');
		$package = trim($_POST['add_package']);
		
		if ($package) {
		    $tmp = $push_detail->table('sj_soft')->where("package='{$package}' and hide = 1 and status = 1")->select();
		    if (empty($tmp)) {
		        $this->error('添加失败，包名不存在！');
			    exit;
		    }
		} else {
		    $this->error('添加失败，包名为空！');
			exit;
		}


		$tmp = $push_detail->where("beid='{$_POST['add_beid']}' AND package='{$package}'")->select();
		$has = FALSE;
		if($tmp) {
			foreach($tmp as $val) {
				if(($start>=$val['start'] && $start<=$val['end']) || ($end>=$val['start'] && $end<=$val['end'])) {
					$has = TRUE;
					break;
				}
			}
			if($has) {
				$this->error('添加失败，相同包名与推送行为的排期出现时间重叠！');
				exit;
			}
		}
		$ret=$push_detail->add(array('beid'=>$_POST['add_beid'],'package'=>$package,'start'=>$start,'end'=>$end,'status'=>$_POST['add_status']));
		$this->writelog("添加了id为{$ret}",'sj_push_be_detail',$ret,__ACTION__ ,"","add");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Products/pushwait");
		$this->success("恭喜您，添加推送行为排期成功！");
	}

	//安智市场-web_市场更新管理_推送行为排期管理页面_添加
	function pushwait_edit() {
		$push_detail = M('push_be_detail');
		$model = new Model();
		$start = strtotime($_POST['edit_start'].' 00:00:00');
		$end = strtotime($_POST['edit_end'].' 23:59:59');
		$p = $_POST['edit_p'];
		$package = trim($_POST['edit_package']);
		
		if ($package) {
		    $tmp = $push_detail->table('sj_soft')->where("package='{$package}' and hide = 1 and status = 1")->select();
		    if (empty($tmp)) {
		        $this->error('编辑失败，包名不存在！');
			    exit;
		    }
		} else {
		    $this->error('编辑失败，包名为空！');
			exit;
		}


		$tmp = $push_detail->where("beid='{$_POST['edit_beid']}' AND package='{$package}' AND id!='{$_POST['edit_id']}'")->select();
		$has = FALSE;
		if($tmp) {
			foreach($tmp as $val) {
				if(($start>=$val['start'] && $start<=$val['end']) || ($end>=$val['start'] && $end<=$val['end'])) {
					$has = TRUE;
					break;
				}
			}
			if($has) {
				$this->error('编辑失败，相同包名与推送行为的排期出现时间重叠！');
				exit;
			}
		}
		$status = $_POST['edit_status'] == '停用' ? 0 : 1;
		$data = array('start'=>$start,'end'=>$end,'beid'=>$_POST['edit_beid'],'package'=>$package,'status'=>$status);
		$log = $this->logcheck(array('id'=>$_POST['edit_id']),'sj_push_be_detail',$data,$push_detail);
		$push_detail->query("update sj_push_be_detail set beid='{$_POST['edit_beid']}',package='{$package}',start='{$start}',end='{$end}',status='{$status}' where id='{$_POST['edit_id']}'");
		$this->writelog("市场更新管理-推送行为排期管理页面编辑推送行为排期行为ID为'".$_POST['edit_id']."'".$log,'sj_push_be_detail',$_POST['edit_beid'],__ACTION__ ,"","edit");
		//$this->writelog('编辑推送行为排期行为ID为'.$_POST['edit_id'].'包名'.$_POST['edit_package'].'状态为'.$status.'停用是0启用是1');
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Products/pushwait/p/".$p);
		$this->success("恭喜您，编辑推送行为排期成功！");
	}
	
	function new_skin(){
		$model = new Model();
		$result = $model -> table('sj_market_skin') -> where(array('skin_version' => 2,'status' => 1)) -> order('rank') -> select();
		$count = count($result);
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}

		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_new_skin_show(){
		$this -> display();
	}
	
	function add_new_skin(){
		$model = new Model();
		$name = $_POST['name'];
		$iconurl = $_FILES['iconurl'];
		$theme_url = $_FILES['theme_url'];
		
		if(empty($name)){
			$this -> error('请填写主题名称');
		}
		if(!$iconurl['size']){
			$this -> error('请上传皮肤');
		}
		if(!$theme_url['size']){
			$this -> error('请上传主题封面');
		}
		$path = date("Ym/d/");
		if($iconurl['size'] && $theme_url['size']){
			$config['multi_config']['iconurl'] = array(
				'savepath' => UPLOAD_PATH . '/apk/' . $path,
				'saveRule' => 'packagename'
			);
			$config['multi_config']['theme_url'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_width'=> 446, 
				'img_p_height'=> 160,
			);
			$list = $this->_uploadapk(0, $config);
			foreach($list['image'] as $key => $val){
				if($val['post_name'] == 'theme_url'){
					$data['theme_url'] = $val['url'];
				}
			}
			
			$data['package'] = $list['apk'][0]['packagename'];
			$data['md5_file'] = md5_file(UPLOAD_PATH.$list['apk'][0]['apkurl_db']);
			$data['iconurl'] = $list['apk'][0]["iconurl_db"];
			$data['apkurl'] = $list['apk'][0]["apkurl_db"];
			$data['apksize'] = $list['apk'][0]["size"];
			$data['version'] = $list['apk'][0]["versionName"];
			$data['version_code'] = $list['apk'][0]["versionCode"];
		}


		$data['name'] = $name;
		$data['skin_version'] = 2;
		$data['rank'] = 1;
		$data['status'] = 1;
		$data['last_refresh'] = time();
		$data['upload_tm'] = time();
	
		
		$been_result = $model -> table('sj_market_skin') -> where(array('skin_version' => 2,'status' => 1)) -> select();
		foreach($been_result as $key => $val){
			$update_where['id'] = $val['id'];
			$update_data = array(
				'rank' => $val['rank'] + 1,
			);
			$update_result = $model -> table('sj_market_skin') -> where($update_where) -> save($update_data);
		}
		$map['softname']='皮肤预留';
        $map['status']=0;
        $map['hide']=0;
		$map['package'] = '皮肤预留';
        $softid=$model -> table('sj_soft') -> add($map);
	
		if(empty($softid)) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，出了一点小问题');
        }
        $model -> table('sj_soft') ->where('softid='.$softid)->delete();
		$data['id'] = $softid;
		$result = $model -> table('sj_market_skin') -> add($data);

		if($result){
			$this -> writelog("已添加id为{$result}的V4.5的主题配置",'sj_market_skin',$result,__ACTION__ ,"","add");
			$this->assign('jumpUrl', '/index.php/Sj/Products/new_skin');
			$this->success('添加成功');
		}else{
			$this -> error('添加失败');
		}
	}
	
	function edit_new_skin_show(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_market_skin') -> where(array('id' => $id)) -> select();
		$count = count($result);
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
	
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_new_skin(){
		$model = new Model();
		$name = $_POST['name'];
		$id = $_POST['id'];
		$iconurl = $_FILES['iconurl'];
		$theme_url = $_FILES['theme_url'];
		if(empty($name)){
			$this -> error('请填写主题名称');
		}
		$path = date("Ym/d/");
		if($iconurl['size'] || $theme_url['size']){
			$config['multi_config']['iconurl'] = array(
				'savepath' => UPLOAD_PATH . '/apk/' . $path,
				'saveRule' => 'packagename'
			);
			$config['multi_config']['theme_url'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_width'=> 446, 
				'img_p_height'=> 160,
			);
			$list = $this->_uploadapk(0, $config);
			foreach($list['image'] as $key => $val){
				if($val['post_name'] == 'theme_url'){
					$data['theme_url'] = $val['url'];
				}
			}
			
			$data['package'] = $list['apk'][0]['packagename'];
			$data['md5_file'] = md5_file(UPLOAD_PATH.$list['apk'][0]['apkurl_db']);
			$data['iconurl'] = $list['apk'][0]["iconurl_db"];
			$data['apkurl'] = $list['apk'][0]["apkurl_db"];
			$data['apksize'] = $list['apk'][0]["size"];
			$data['version'] = $list['apk'][0]["versionName"];
			$data['version_code'] = $list['apk'][0]["versionCode"];
		}


		$data['name'] = $name;
		$data['last_refresh'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'sj_market_skin',$data,$model);
		$result = $model -> table('sj_market_skin') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$result}的V4.5的主题配置".$log_result,'sj_market_skin',$id,__ACTION__ ,"","edit");
			$this->assign('jumpUrl', '/index.php/Sj/Products/new_skin');
			$this->success('编辑成功');
		}else{
			$this -> error('编辑失败');
		}
	
	}
	
	function change_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$change = $_GET['change'];
		$my_result = $model -> table('sj_market_skin') -> where(array('id' => $id)) -> select();
		if($change > $my_result[0]['rank']){
			$need_where['_string'] = "rank > {$my_result[0]['rank']} and rank <= {$change} and skin_version = 2 and status = 1";
			$need_result = $model -> table('sj_market_skin') -> where($need_where) -> select();
	
			foreach($need_result as $key => $val){
				$update_where['_string'] = "id = {$val['id']}";
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table('sj_market_skin') -> where($update_where) -> save($update_data);
			}
			$result = $model -> table('sj_market_skin') -> where(array('id' => $id)) -> save(array('rank' => $change));
		}else{
			$need_where['_string'] = "rank < {$my_result[0]['rank']} and rank >= {$change} and skin_version = 2 and status = 1";
			$need_result = $model -> table('sj_market_skin') -> where($need_where) -> select();
			foreach($need_result as $key => $val){
				$update_where['_string'] = "id = {$val['id']}";
				$update_data = array(
					'rank' => $val['rank'] + 1
				);
				$update_result = $model -> table('sj_market_skin') -> where($update_where) -> save($update_data);
			}
			$result = $model -> table('sj_market_skin') -> where(array('id' => $id)) -> save(array('rank' => $change));
		}
		
		if($result){
			$this -> writelog("已修改id为{$id}的主题配置的排序为{$change}",'sj_market_skin',$id,__ACTION__ ,"","edit");
			echo 1;
			return json_encode(1);
		}

	}
	
	function delete_new_skin(){
		$model = new Model();
		$id = $_GET['id'];
		$need_result = $model -> table('sj_market_skin') -> where(array('id' => $id)) -> select();
		$update_where['_string'] = "rank > {$need_result[0]['rank']} and status = 1 and skin_version = 2";
		$update_result = $model -> table('sj_market_skin') -> where($update_where) -> select();
		foreach($update_result as $key => $val){
			$some_where['id'] = $val['id'];
			$some_data = array(
				'rank' => $val['rank'] - 1
			);
			$some_result = $model -> table('sj_market_skin') -> where($some_where) -> save($some_data);
		}
		$result = $model -> table('sj_market_skin') -> where(array('id' => $id)) -> save(array('status' => 0));

		if($result){
			$this -> writelog("已删除id为{$id}的主题配置",'sj_market_skin',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Products/new_skin');
			$this -> success("删除成功");
		}
	}
}
