<?php
class WatersAction extends CommonAction {
    public $upload_url = "/data/att/m.goapk.com/waters/";
    public $lists=array();
   public function add_waters_list() {
       $wid = trim($_GET['wid']);
       $package = trim($_GET['package']);
       if($wid==0 || empty($package)){
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersSoft');
           $this->error('请检查您要输入的信息是否合理！');
       }
       $soft = M("soft");
       $wlist = M("waters_list");
       $wmap['package'] = $package;
       $wmap['wid']=  $wid;
       $smap['package'] = $package;
       $smap['hide'] = 1;
       $smap['status'] = 1;
       $softinfo = $soft -> where($smap) -> select();
       $wlistinfo = $wlist -> where($wmap) -> select();
       if(count($softinfo) > 0){
             if(empty($wlistinfo)){
                  $data['wid'] = $wid;
                  $data['package'] = $package;
                  $data['status'] = 1;
                  $data['create_time'] = time();
                  $affect = 0;
                  $affect = $wlist -> add($data);
                  if($affect>0){
                  	$this->writelog("添加wid为".$wid."包名为".$package."的分类",'sj_waters_list',$affect,__ACTION__ ,"","add");
                    $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersSoft');
                    $this->success('添加成功');
                  }
             }else{
                   if($wlistinfo[0]['status'] ==0){
                      $data['status'] = 1;
                      $data['create_time'] = time();
                      $map['wid'] = $wid;
                      $map['package'] = $package;
                      $affect = $wlist -> where($map) -> save($data);
                      if($affect > 0){
                      	$this->writelog("添加wid为".$wid."包名为".$package."的分类",'sj_waters_list',"wid:{$wid}",__ACTION__ ,"","edit");
                        $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersSoft');
                        $this->success('添加成功');
                      }
                   }
                   $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersSoft');
                   $this->error('添加失败,软件已存在');
             }
       }else{
       $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersSoft');
       $this->error('软件不存在');
       }
    }
	//新版本
	 public function watersSoft()
	 {
		$map['_string'] = "A.status = 1 AND B.status = 1 AND C.status = 1 AND D.package_status = 1 AND A.wid = B.wid AND B.package = C.package AND C.softid = D.softid";
		//获取搜索条件
	   if(isset($_GET['package']) && $_GET['package'] != "")
	   {
			$package = trim($_GET['package']);
			$where['B.package'] = array('like','%'.$package.'%');
			$where['C.softname'] = array('like','%'.$package.'%');
			$where['_logic'] = "OR";
			$map['_complex'] = $where;
	   }
	   if(isset($_GET['wid']) && $_GET['wid'] != "")
	   {
			$wid = trim($_GET['wid']);
			$map['A.wid'] = $wid;
	   }
		$w1 = M('waters_list');
		$w = M('waters');
		$wlist1 = $w -> where('status = 1') -> select();
		$count = $w1->table("sj_waters A,sj_waters_list B,sj_soft C,sj_soft_file D")->where($map)->count();
		import("@.ORG.Page");
		$p=new Page($count,15);
        $wllist = $w1->table("sj_waters A,sj_waters_list B,sj_soft C,sj_soft_file D")->where($map) 
					->field("B.id, C.softid, A.w_name,	D.iconurl,	C.softname,	B.package,	C.score,	D.filesize,	B.create_time")
					->order("B.create_time desc") -> limit($p->firstRow.','.$p->listRows)-> select();
		$p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        $this->assign( "page", $page );
        $this->assign( "wid", $wid );
        $this->assign( "package", $package );
        $this -> assign("wlist" , $wlist1);
        $this->assign ( "list", $wllist );
		$this->url_suffix = $this->get_url_suffix(array('wid','package','p','lr'));
        $this ->display("watersSoft");
	 }
	/**旧版本
	*下面这个已经没用了
	*/
   public function watersSoft_bak_by_liujian() {
       $wl = M('waters_list');
       $w = M('waters');
       $soft = M("soft");
       $sfile = M("soft_file");
       import("@.ORG.Page");
       if(isset($_GET['selectwid'])){
		$selectwid = $_GET['selectwid'];
		$map['wid'] = $selectwid;
       }
	   //获取搜索条件
	   if(isset($_GET['package']) && $_GET['package'] != "")
	   {
			$soft_map['package'] = array('like','%'.$_GET['package'].'%');
			$soft_map['softname'] = array('like','%'.$_GET['package'].'%');
			$soft_map['_logic_'] = "OR";
	   }
	   if(isset($_GET['wid']) && $_GET['wid'] != "")
	   {
			$map['wid'] = $_GET['wid'];
	   }
       $map['status'] = 1;
		
		$count=$wl->where($map)->count();
		echo $count;
		$p=new Page($count,15);
        $wllist = $wl  ->where($map) -> limit($p->firstRow.','.$p->listRows)-> select();
		
        $wlist = $w -> where('status = 1') -> getField("wid,w_name");
        $wlist1 = $w -> where('status = 1') -> select();
        foreach($wllist as $idx => $info){
            $package[] = $info['package'];
        }
        //获取软件信息
        $map['package'] = array('in',$package);
        $map['status'] = 1;
        $map['hide']   = 1;
        $softinfo = $soft -> where($map) -> select();
        foreach($softinfo as $idx => $sinfo){
             $softfile = $sfile -> where("softid = ".$sinfo['softid']." and package_status = 1") -> select();
			 if(isset($softfile))
			 {
				 $softinfos[$sinfo['package']]['filesize'] = $softfile[0]['filesize'];
				 $softinfos[$sinfo['package']]['iconurl'] = $softfile[0]['iconurl'];
				 $softinfos[$sinfo['package']]            = array_merge($sinfo, $softinfos[$sinfo['package']]);
			 }
        }
		$wllist_tmp = $wllist;
		$wllist = NULL;
        foreach($wllist_tmp as $idx => $info){
			if(isset($softinfos[$info['package']]['softid']) && $softinfos[$info['package']]['softid'] != NULL && isset($wlist[$info['wid']]))
			{
				 $wllist[$idx]  = $wllist_tmp[$idx];
				 $wllist[$idx]['w_name'] = $wlist[$info['wid']];
				 $wllist[$idx]['softname'] = $softinfos[$info['package']]['softname'];
				 $wllist[$idx]['costs'] = $softinfos[$info['package']]['costs'];
				 $wllist[$idx]['iconurl'] = $softinfos[$info['package']]['iconurl'];
				 $wllist[$idx]['filesize'] = $softinfos[$info['package']]['filesize'];
				 $wllist[$idx]['softid'] = $softinfos[$info['package']]['softid'];
				 $wllist[$idx]['score'] = $softinfos[$info['package']]['score'];	
			}
        }
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        $this->assign( "page", $page );
        $this -> assign("wlist" , $wlist1);
        $this->assign ( "list", $wllist );
        $this ->display("watersSoft");
   }
   public function waterList() {
        $w = M("waters");
        import("@.ORG.Page");
		$count=$w->where('status=1')->count();
		$p=new Page($count,15);
        $wlist = $w  ->where('status = 1') -> limit($p->firstRow.','.$p->listRows)-> select();
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        $this->assign( "page", $page );
        $this->assign ( "list", $wlist );
        $this ->display("waterList");
   }
   public function add_waters() {
       $w = M("waters");
       $w_name = trim($_GET['w_name']);
       $data['w_name'] = $w_name;
       $data['status'] = 1;
	   $data['switching'] = $_GET['special'];//开启，关闭
       $data['create_time'] = time();
       //$affect = 0;
       $result = array();
       $result = $w -> where(" status=1 and w_name='{$w_name}'") -> select();
	   //echo $w -> getlastsql($affect);exit;
	   if(empty($w_name)){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/waterList');
			$this->error('集合名不能为空');
	   }
       if(!$result){
           $affect = $w ->add($data);
           if($affect){
           	    $this->writelog("添加了名字为：".$w_name."的应用集合",'sj_waters',$affect,__ACTION__ ,"","add");
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/waterList');
                $this->success('添加成功');
           }
       }else{
                $this->error('添加失败，集合名已存在');
       }
   }
   public function delWatersSoft() {
        $wlist = M('waters_list');
        $id =$_GET['id'];
        $data['status'] = 0;
        $package = $wlist -> where(array('id'=>$id)) -> find();
		$url_suffix = $this->get_url_suffix(array('wid','package','p','lr'));
        $affect = $wlist -> where(array('id'=>$id)) -> save($data);
		
        if($affect > 0){
          $this->writelog("软件集合-集合软件更新-删除了表waters_list，id为".$id."包名为".$package['package'].'的数据','sj_waters_list',$id,__ACTION__ ,"","del");
          $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersSoft'.$url_suffix);
          $this->success('删除成功');
        }
   }
   public function delWaterList() {
       $id = $_GET['id'];
       $w = M('waters');
       $map['wid'] = $id;
       $data['status'] = 0;
       $affect = $w ->where($map) ->save($data);
       if($affect > 0){
       	$this->writelog("删除了wid为：".$id."的软件集合",'sj_waters',$id,__ACTION__ ,"","del");
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/waterList');
          $this->success('删除成功');
       }
   }
   public function editWaterList() {
       $w = M('waters');
       $id = $_GET['id'];
       $map['wid'] = $id;
       $result = $w -> where($map) -> select();
       $this -> assign('wid',$result[0]['wid']);
       $this -> assign('w_name',$result[0]['w_name']);
       $this -> assign('switching',$result[0]['switching']);
       $this -> display("editWaterList");
   }
   public function doEditWater() {
       $w = M('waters');
       $wid = $_GET['wid'];
       $w_name = trim($_GET['w_name']);
       $data['w_name'] = $w_name;
	   $data['switching']    = (string)$_GET['special'];
       $data['create_time'] = time();
	   if(empty($w_name)){
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/waterList');
        $this->error('集合名不能为空');
	   }
	   $affect1 = $w -> where("status=1 and wid != {$wid} and w_name='{$w_name}'")->select();
	   //echo $w-> getlastsql();
	   if($affect1){
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/waterList');
        $this->error('集合名已存在');	
	   }else{
                $log = $this->logcheck(array('wid'=>$wid),'sj_waters',$data,$w);
		$affect = $w -> where("wid={$wid} and status=1") -> save($data);
			if($affect > 0){
			  $this->writelog("修改了wid为".$wid."的软件集合名,".$log,'sj_waters',$wid,__ACTION__ ,"","edit");
			  $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/waterList');
			  $this->success('编辑成功');
			}
	   }
   }
   public function watersMsg() {
       $wm = M("waters_note");
       $w = M("waters");
        import("@.ORG.Page");
		$count=$wm->where('status=1')->count();
		$p=new Page($count,15);
        $type = array('文本','apkID');
        $wlist = $w  ->where('status = 1') -> getField('wid,w_name');
        $wmlist = $wm  ->where('status = 1') -> limit($p->firstRow.','.$p->listRows)-> select();
        foreach($wmlist as $idx => $info){
           $wmlist[$idx]['w_name'] = $wlist[$info['wid']];
           $wmlist[$idx]['type'] = $type[$info['type']];
        }
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        $this->assign( "page", $page );
        $this->assign ( "list", $wmlist );
       $this -> display("watersMsg");
   }
    public function addWatersMsg() {
       $w = M("waters");
       $wlist = $w -> where("status = 1") -> select();
       $this -> assign("wlist" ,$wlist);
       $this -> display("addWatersMsg");
   }
   public function addMsg() {
       $wm = M("waters_note");
       $wid = $_GET['wid'];
       $type = $_GET['type'];
       $message = $_GET['message'];
       $map['wid'] = $wid;
       $map['type'] = $type;
       $result = $wm -> where($map) -> select();
       if(!empty($result)){
            $this->error('添加失败，已存在');
       }
       $data['wid'] = $wid;
       $data['type'] = $type;
       $data['message'] = $message;
       $data['create_time'] = time();
       $data['status'] = 1;
        $affect = $wm -> add($data);
       if($affect > 0){
       	  $this->writelog('添加'.$affect.'的公告，公告内容为'.$data['message'],'sj_waters_note',$affect,__ACTION__ ,"","add");
          $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersMsg');
          $this->success('添加成功');
       }
   }

   public function delMsg() {
       $wm = M("waters_note");
       $id = $_GET['id'];
       $data['status'] = 0;
       $affect = $wm -> where(array("id"=>$id)) -> save($data);
       if($affect > 0){
       	  $this->writelog('软件集合-集合软件公告-id为'.$id."的公告被删除",'sj_waters_note',$id,__ACTION__ ,"","del");
          $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersMsg');
          $this->success('删除成功');
       }else{
          $this->error('删除失败');
       }
    }

   public function editMsg() {
       $wm = M("waters_note");
       $w = M("waters");
       $id = $_GET['id'];
       $typelist = array(array('value'=>0,'name'=>'文本'),array('value'=>1,'name'=>'apkID'));
       $wlist = $w  ->where('status = 1') -> select();
       $result = $wm ->where(array("id"=>$id)) -> select();
       $this -> assign('id',$result[0]['id']);
       $this -> assign('message', $result[0]['message']);
       $this -> assign('type',$result[0]['type']);
       $this -> assign('wid',$result[0]['wid']);
       $this -> assign('wlist',$wlist);
       $this -> assign('typelist',$typelist);
       $this -> display('editMsg');
   }
    public function doEditMsg() {
       $wm = M("waters_note");
       $map['id'] = $_GET['id'];
       $data['message'] = $_GET['message'];
       $data['type'] = $_GET['type'];
       $data['create_time'] = time();
       $log = $this->logcheck(array('id'=>$map['id']),'sj_waters_note',$data,$wm);
       $affect = $wm -> where($map) -> save($data); 
       if($affect > 0){
          $this->writelog('软件集合-集合软件公告-ID为'.$map['id'].$log,'sj_waters_note',$map['id'],__ACTION__ ,"","edit");
       	  //$this->writelog('ID为'.$map['id']."的公告被修改，内容为".$data['message']);
          $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersMsg');
          $this->success('编辑成功');
       }else{
          $this->error('编辑失败');
       }
   }

    public function watersUpdate() {
        $ws = M("waters_soft");
        $w  = M("waters");
        $map['status'] = 1;
        $wmap['status'] = 1;
         import("@.ORG.Page");
		$p=new Page($count,15);
		$count=$ws->where('status=1')->count();
        $wslist = $ws -> where($map) -> limit($p->firstRow.','.$p->listRows) -> select();
        $wlist       = $w -> where($wmap) -> getField("wid,w_name");
        $wlist1       = $w -> where($wmap) -> select();
        foreach($wslist as $idx => $info){
             $wslist[$idx]['w_name'] = $wlist[$info['wid']];
        }
        $p->setConfig('header','篇记录');
        $p->setConfig('prev',"上一页");
        $p->setConfig('next','下一页');
        $p->setConfig('first','首页');
        $p->setConfig('last','末页');
        $page = $p->show ();
        $this->assign( "page", $page );
        $this ->assign("wslist",$wslist);
        $this ->assign("wlist",$wlist1);
        $this -> display("watersUpdate");
    }
	public function watersUpdata_insert() {
		$map['version_code'] = $_POST['version_code'];
		$map['version_name'] = $_POST['version_name'];
		$map['force_update'] = $_POST['force_update'];
		$map['description'] = $_POST['description'];
        $map['apkname'] = $_POST['apkname'];

        if(empty($map['version_code']) || empty($map['version_name'])|| empty($map['apkname'])) {
            $this->error('对不起，软件名,版本号及版本名称必须填写');
        }
        if(strlen($_POST['version_name'])>30) {
            $this->error('对不起，版本名称不得长于30个字符，10个汉字');
        }
        if(strlen($_POST['description'])>1500) {
            $this->error('对不起，描述信息不得长于1500个字符，500个汉字');
        }
       if(!empty($_FILES['apk']['size'])) {
        //APK上传
        	$path = date("Ym/d/");
        	$config = array(
        		'savePath' => UPLOAD_PATH. '/apk/'. $path,
        		//'saveRule' => substr($_FILES['apk']['name'], 0, strrpos($_FILES['apk']['name'], '.')),
        		'saveRule' => 'packagename',
        		'subPath' => '/',
        	);
            $this->lists=$this->_uploadapk(false, $config);
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
            $this->error('对不起，APK必须上传');
        }

		$Form = M("waters_soft"); // 实例化User对象
		$map['iconurl'] = $this->lists['apk'][0]["iconurl_db"];
		$map['package'] = $this->lists['apk'][0]["packagename"];
		$map['apkurl'] = $this->lists['apk'][0]["apkurl_db"];
		$map['apksize'] = $this->lists['apk'][0]["size"];
        $map['wid'] = $_POST['wid'];
        $map['status'] = 1;
		$map['submit_tm']=time();
		$map['last_refresh']=time();
        $data['status'] = 0;
        $exists = array();
        $exists = $Form -> where(array("wid" => $_POST['wid'],"status" => 1)) ->select();

        if(!empty($exists)){
            $affect = 0;
            $affect = $Form -> where(array("wid" => $_POST['wid'],"status" => 1)) -> save($data);
            if($affect == 0){
               $this->error("升级失败");
            }
        }

		$Form -> create();
		$idthumb=$Form->add($map); // 写入用户数据到数据库

        $this->writelog('上传了ID为['.$idthumb.']的市场更新','sj_waters_soft',$idthumb,__ACTION__ ,"","add");
		$this->success("数据保存成功！");
	}
    //市场更新管理_编辑_显示
	public function watersUpdate_edit() {
		if(!empty($_GET['id'])) {
            $map='';
            $map['status']=1;
            $map['id']=$_GET['id'];
            $Form = M("waters_soft");
            $w= M("waters");
			$vo = $Form->where($map)->select();
            $wlist = $w -> where("status = 1") -> select();
            $this->assign('wlist',$wlist);
            $this->assign('vo',$vo[0]);
            $this->display('watersUpdate_update');
	}
    }
    //市场更新管理_编辑_提交
	public function watersUpdate_update_do() {
			$Form	=	M("waters_soft");
			$_POST['last_refresh']=time();
            if(empty($_POST['version_code']) || empty($_POST['version_name'])) {
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
                $this->error('对不起，版本号及版本名称必须填写');
            }
            if(strlen($_POST['version_name'])>30) {
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
                $this->error('对不起，版本名称不得长于30个字符，10个汉字');
            }
            if(strlen($_POST['description'])>1500) {
                $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Soft/softadd');
                $this->error('对不起，版本名称不得长于1500个字符，500个汉字');
            }
            $map['id'] = $_POST['id'];
            $data = array('version_code'=>$_POST['version_code'],'id'=>$_POST['id'],'version_name'=>$_POST['version_name'],'force_update'=>$_POST['force_update'],'description'=>$_POST['description'],'last_refresh'=>$_POST['last_refresh']);
            $log = $this->logcheck(array('id'=>$_POST['id']),'sj_waters_soft',$data,$Form
              );
			     $list=$Form->where($map)->save($data);

			if($list!==false){

               //$this->writelog('编辑了ID为['.$_POST['id'].']的市场更新');
        $this->writelog('软件集合-集合软件更新-编辑了ID为['.$_POST['id'].']的市场更新'.$log,'sj_waters_soft',$_POST['id'],__ACTION__ ,"","edit");
			   $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersUpdate');
			   $this->success('数据更新成功');
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersUpdate');
				$this->error('数据更新失败');
			}
		}

    //市场更新管理_市场更新删除
	public function watersUpdate_del() {
		if(!empty($_GET['id'])) {
			$ws	=	M("waters_soft");
            $data['status'] = 0;
            $map['id'] = (int)$_GET['id'];
			$result	=	$ws -> where($map) -> save($data);
			if(false != $result)
			{
                $this->writelog('删除了ID为['.$_GET['id'].']的市场更新','sj_waters_soft',$_GET['id'],__ACTION__ ,"","del");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersUpdate');
				$this->success('删除成功');
			}else
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/watersUpdate');
				$this->error('删除失败');
			}
		}else{
			$this->error('删除项不存在！');
		}
	}
	/**
	*集合渠道推广管理
	*@功能： 1推广渠道包
			 2列表显示渠道名,集合名称,添加时间,状态,推广链接,功能操作
			 3操作 增,删,改
			 4渠道新增默认为关闭(不能使用)
	*/
	public	function water_chl_add(){
		$model =  new Model();
		$db_table = 'sj_waters_chl_popular'; //渠道推广表
		$wid = $_POST['wid'];
		$chids = $_POST['cid'];
		$url = trim($_POST['popular_url']);
		if(!$chids || !$url) $this -> error('渠道或链接不能为空');
		$last_refresh = $create_tm = time();
		$chidcount = count($chids);
		$i = 0;
		$existscount = 0;
		foreach($chids as $cid){
			$data = array(
				'wid' => $wid,
				'chid' => $cid,
				'last_refresh' => $last_refresh,
				'create_tm' => $create_tm,
				'status' => 0,
				'popular_link' => $url,
 			);
			$exists = 0;
			$exists = $model -> table($db_table) -> where('wid ='.$wid.' and chid ='.$cid) -> count();
			if($exists > 0){
				$existscount++;
				continue;
			}
			$affect = $model -> table($db_table) -> add($data);
			if($affect){
                $this->writelog('添加了 wid '.$wid.'的 渠道id:'.$cid.' 的推广','sj_waters_chl_popular',$affect,__ACTION__ ,"","add");
				$i++;
			}	
		}
		if($chidcount == $i){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/water_chl_list/wid/'.$wid);
			$this -> success('添加渠道添加成功');
		}elseif($i!=0){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/water_chl_list/wid/'.$wid);
			$this -> success('添加渠道添加成功了'.$i."个 ，给别渠道重复");			
		}else{
			$this -> error('添加失败');
		}
		
	}
	
	public function water_chl_add_html(){
		$model = new Model();
		$wid = $_GET['wid'];
		$where = array(
			'wid' => $wid,
			'status' => 1,
 		);
		$list = $model -> table('sj_waters') -> where($where) -> limit(1) -> select();
		$this -> assign('wid',$list[0]['wid']);
		$this -> assign('w_name',$list[0]['w_name']);
		$this -> display('water_chl_add_html');
	}
	
	public function water_chl_list(){
		$wid = $_GET['wid'];
		$model = new Model();
		$db_table = 'sj_waters_chl_popular';
		$where = array(
			'wid' => $wid,
		);
		$list = $model -> table($db_table) -> where($where) -> order('last_refresh desc') -> select();
		foreach($list as $idx => $info){
			$chname  = $model -> table('sj_channel') -> where('cid ='.$info['chid']." and status = 1") -> getField('chname');
			$list[$idx]['chname'] = $chname;
			$wname = $model -> table('sj_waters') -> where('wid='.$info['wid']) -> getField('w_name');
			$list[$idx]['w_name'] = $wname;
		}
		$this -> assign('wid',$wid);
		$this -> assign('list',$list);
		$this -> display('water_chl_list');
 	}
	public function water_chl_edit(){
		$wid = $_GET['wid'];
		$id = $_GET['id'];
		$model = new Model();
		$db_table = 'sj_waters_chl_popular';
		$where = array(
			'wid' => $wid,
			'id' => $id,
		);
		$list = $model -> table($db_table) -> where($where) -> order('last_refresh desc') -> select();
		$where = array(
			'wid' => $wid,
		);
		$w_name = $model -> table('sj_waters') -> where($where) -> getField('w_name'); 
		$chname = $model -> table('sj_channel') -> where('cid ='.$list[0]['chid']) -> getField('chname');
		$this -> assign('list',$list[0]);
		$this -> assign('w_name',$w_name);
		$this -> assign('chname',$chname);
		$this -> display('water_chl_edit');		
	}
	public function water_chl_edit_do(){
		$id = $_POST['id'];
		$wid = $_POST['wid'];
		$where = array(
			'id' => $id,
		);
		$data = array();
		if($_POST['chid']) $data['chid'] = $_POST['chid'];
		$data['last_refresh'] = time();
		if($_POST['status']) $data['status'] = $_POST['status'];
		if($_POST['url']) $data['popular_link'] = trim($_POST['url']);
		$model = new Model();
		$db_table = 'sj_waters_chl_popular';
		$affect = $model -> table($db_table) -> where($where) -> save($data);
		if($affect){
			$this -> writelog('编辑了 wid '.$wid.'的 内容 '.json_encode($data),'sj_waters_chl_popular',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/water_chl_list/wid/'.$wid);
			$this -> success('软件集合渠道编辑成功！');
		}else{
			$this -> error('编辑失败');
		}
	}

	public function water_chl_modify(){
		$id = $_GET['id'];
		$wid = $_GET['wid'];
		$status = $_GET['status'];
		$model = new Model();
		$where = array(
			'id' => $id,
		);
		$exists = $model -> table('sj_waters_chl_popular') -> where($where) -> count();
		if($exists){
			$data = array(
				'last_refresh' => time(),
				'status' => $status,
 			);
			$affect = $model -> table('sj_waters_chl_popular') -> where($where) -> save($data);
			if($affect){
				$this -> writelog('修改了 id '.$id.'的 status 为'.$status,'sj_waters_chl_popular',$id,__ACTION__ ,"","edit");
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Waters/water_chl_list/wid/'.$wid);
				$this -> success('修改成功');
			}
		}else{
			$this -> error('不存在');
		}
	}
}
?>
