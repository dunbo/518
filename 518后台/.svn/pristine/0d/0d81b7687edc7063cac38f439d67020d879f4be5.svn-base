<?php
ini_set('memory_limit', '128M');
class marketPushAction extends CommonAction {
	
	//弹窗广告增加高低分图片 
    private $big_pic_width=468;
    private $big_pic_height=566;
	private $big_pic_width_high=684;
    private $big_pic_height_high=872;
	private $big_pic_width_low=444;
    private $big_pic_height_low=566;
	
    private $small_pic_width_high=466;
    private $small_pic_height_high=333;
	private $small_pic_width_low=350;
    private $small_pic_height_low=250;
	
	private $btn_pic_width_high=466;
    private $btn_pic_height_high=200;
	private $btn_pic_width_low=350;
    private $btn_pic_height_low=150;
	
	private $btn_pic_des_limit=16;
	private $btn_pic_name_limit=6;
	
	//推送添加单行多行高低分图片
	private $single_low_width=480;
	private $single_low_height=88;
	private $single_high_width=720;
	private $single_high_height=132;
	private $multiple_low_width=480;
	private $multiple_low_height=250;
	private $multiple_high_width=720;
	private $multiple_high_height=375;
	
	//V6.4新增加 信息图标图片
	private $info_icon_width=60;
	private $info_icon_height=60;
	
	//V6.5新增加   桌面红包弹窗、高低分图片
	private $desk_red_high_width=106;
	private $desk_red_high_height=106;
	private $desk_red_low_width=60;
	private $desk_red_low_height=60;
	private $desk_red_pop_width=80;
	private $desk_red_pop_height=80;
	
	private $desk_red_des_title_limit=20;//20个汉字 40个字符
	private $desk_red_des_subtitle_limit=14;
	private $red_result_pop_des_limit=20;
	
	public function _initialize() {
		#屏蔽该ip绕过权限检查
        //预约推送脚本限制
        if (!isset($_POST['USEAPI']) && $_SERVER['REMOTE_ADDR'] != '192.168.1.13') {
            parent::_initialize();
        }
    }
	
    function market_push_list(){
        $sj_market_push=M("market_push");
        $channel=M("channel");
        //echo $_GET['zh_type']."</br>";
        import("@.ORG.Page");
        $where="status=1 and push_type = 1";

        if($_GET['zh_type']){
            if($_GET['zh_type']==1){
                $where .= " and start_tm <= ".time()." and end_tm >= ".time()."";

                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==2){
                $where .= " and end_tm < ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==3){
                $where .= " and start_tm > ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }
        }

        if(!empty($_GET['id'])){
            $where.=" and id=".$_GET['id'];
            $this->assign("so_id",$_GET['id']);
        }
        if(!empty($_GET['info_type'])){
            $where.=" and info_type=".$_GET['info_type'];
            $this->assign("so_info_type",$_GET['info_type']);

        }
        if(!empty($_GET['fromdate'])&& !empty($_GET['todate'])){
            $where.=" and start_tm <=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['todate'])))." and end_tm>=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['fromdate'])));
            $this->assign("so_start_tm",$_GET['fromdate']);
            $this->assign("so_end_tm",$_GET['todate']);
        }
        //$market_push_list=$sj_market_push->where($where)->select();
        $_SESSION['admin']['soft_list']['where'] = $where;
         $util = D("Sj.Util");
        //////////////////////// 应该处理的GET参数
        // 添加平台默认为市场
        $pid = 1;//默认为1
        if ($_GET['pid']) {
            $pid = $_GET['pid'];
        }
        $this->assign('pid', $pid);
        $product_list = $util->getProducts($pid);
        $this->assign('product_list',$product_list);
        $where .= " and pid={$pid}";

        $count = $sj_market_push->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $market_push_list=$sj_market_push->where($where)->order("start_tm desc,id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //$market_push_list=$sj_market_push->where("status=1")->select();
        $mongo = false;
        $collection = $mongodb = false;
        $mongo = false;
        $collection = $mongodb = false;
        //if (class_exists('mongo')) {
        //	$conn_str = '192.168.1.30:27017';
        //	$mongo = new Mongo($conn_str);
        //	$mongodb = $mongo->selectDB('push');
        //	$collection = $mongodb->receipt;
        //}
        foreach ($market_push_list as $k => $v) {
            //echo $v['channel_id'];

            $market_push_list[$k]['push_count']= 0;
            if ($collection) {
                $querys = array(
                    'push_id' => intval($v['id'])
                );
                $market_push_list[$k]['push_count'] = $collection->find($querys)->count();
            }


            $cid_str = substr($v['channel_id'], 1, -1);
            $array = explode(',', $cid_str);
            $cname = $channel->where("cid in ({$cid_str})")->findAll();
			if (in_array("0",$array))
			{
				$market_push_list[$k]['chname'] .= "<p>通用</p>";
			}
            foreach ($cname as $k1 => $v1) 
			{
			 if($market_push_list[$k]['chname']=="")
			  {
			    if($k1==0)
				{
				    if(mb_strlen($v1['chname'],'utf-8')>10)
					{
					 $short_chname=mb_substr($v1['chname'],0,10,'utf-8');
					 $market_push_list[$k]['chname'].="<p>{$short_chname}</p>";
					}
					else
					{
					   $market_push_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
					}
				}
			    if($k1>=1)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
			  else
			  {
			    if($k1>=0)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
            }
            $did_str = substr($v['device_did'], 1, -1);
            $dname = $channel->table("pu_device")->where("did in ({$did_str})")->findAll();
            foreach ($dname as $k2 => $v2) 
			{
			    if($k2==0)
				 {
				    if(mb_strlen($v2['dname'],'utf-8')>10)
					{
					 $short_dname=mb_substr($v2['dname'],0,10,'utf-8');
					 $market_push_list[$k]['dname'].="<p>{$short_dname}</p>";
					}
					else
					{
					$market_push_list[$k]['dname'] .= "<p>{$v2['dname']}</p>";
					}
				 }
			    if($k2>=1)
					{
					  $short_dname=mb_substr($v2['dname'],0,6,'utf-8');
					  $market_push_list[$k]['dname'] .= "<p>{$short_dname}...</p>";
					  break;
					}
                
            }
            if($v['info_type']==2){
                $feature_db=M('feature');
                $map['status']=1;
                if(!empty($v['feature_id'])){
                    $map['feature_id']=$v['feature_id'];
                    $feature_name=$feature_db->where($map)->getfield('name');
                    $market_push_list[$k]['feature_name']=$feature_name;
                }
            }
			if(mb_strlen($v['push_area'],'utf8')>10)
			{ 
			 $short_area=mb_substr($v['push_area'],0,10,'utf-8');
			 $market_push_list[$k]['push_area'] =$short_area."...";
			}
        }
		//获取合作样式列表
		$util = D("Sj.Util"); 
        foreach($market_push_list as $key => $val){
            if($val['info_type'] == 5){
                $val['info_content'] = htmlspecialchars_decode($val['info_content']);
            }
            $market_push_list[$key] = $val;
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($val['co_type']);
			foreach($typelist as $k => $v){
				if($v[1] == true)
				{
					$market_push_list[$key]['co_types'] = $v[0];
				}
			}
			//概率显示
			if($val['push_odds'] == 100)
			{
				$market_push_list[$key]['push_odds_last'] = "100%";
			}
			elseif($val['push_odds'] == 0)
			{
				$market_push_list[$key]['push_odds_last'] = "随机";
			}
			else
			{
				$market_push_list[$key]['push_odds_last'] = $val['push_odds'].'%';
			}
        }

        $push_type = $_GET['push_type'];
        $this -> assign("push_type",$push_type);
        if ($_GET['p'])
            $this->assign('p', $_GET['p']);
        else

        $this->assign('p', '1');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("push_list",$market_push_list);
        $this->display();
    }


    function market_cpm_list(){
        $sj_market_push=M("market_push");
        $channel=M("channel");
        //echo $_GET['zh_type']."</br>";
        import("@.ORG.Page");

        $where="status=1 and push_type = 2";


        $util = D("Sj.Util");
        //////////////////////// 应该处理的GET参数
        // 添加平台默认为市场
        $pid = 1;//默认为1
        if ($_GET['pid']) {
            $pid = $_GET['pid'];
        }
        $this->assign('pid', $pid);
        $product_list = $util->getProducts($pid);
        $this->assign('product_list',$product_list);
        $where .= " and pid={$pid}";
        
        if($_GET['zh_type']){
            if($_GET['zh_type']==1){
                $where .= " and start_tm <= ".time()." and end_tm >= ".time()."";

                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==2){
                $where .= " and end_tm < ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==3){
                $where .= " and start_tm > ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }
        }

        if(!empty($_GET['id'])){
            $where.=" and id=".$_GET['id'];
            $this->assign("so_id",$_GET['id']);
        }
        if(!empty($_GET['notice_type'])){
            $where.=" and notice_type=".$_GET['notice_type'];
            $this->assign("so_info_type",$_GET['notice_type']);
        }
        if(!empty($_GET['fromdate'])&& !empty($_GET['todate'])){
            $where.=" and start_tm <=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['todate'])))." and end_tm>=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['fromdate'])));
            $this->assign("so_start_tm",$_GET['fromdate']);
            $this->assign("so_end_tm",$_GET['todate']);
        }
        //$market_push_list=$sj_market_push->where($where)->select();
        $_SESSION['admin']['soft_list']['where'] = $where;

        $count = $sj_market_push->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $market_push_list=$sj_market_push->where($where)->order("start_tm desc,id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //$market_push_list=$sj_market_push->where("status=1")->select();
        $mongo = false;
        $collection = $mongodb = false;
        $mongo = false;
        $collection = $mongodb = false;
        //if (class_exists('mongo')) {
        //	$conn_str = '192.168.1.30:27017';
        //	$mongo = new Mongo($conn_str);
        //	$mongodb = $mongo->selectDB('push');
        //	$collection = $mongodb->receipt;
        //}
		//获取合作样式列表
		$util = D("Sj.Util"); 
        foreach ($market_push_list as $k => $v) {
            //echo $v['channel_id'];

            $market_push_list[$k]['push_count']= 0;
            if ($collection) {
                $querys = array(
                    'push_id' => intval($v['id'])
                );
                $market_push_list[$k]['push_count'] = $collection->find($querys)->count();
            }


            $cid_str = substr($v['channel_id'], 1, -1);
            $array = explode(',', $cid_str);
            $cname = $channel->where("cid in ({$cid_str})")->findAll();
			if (in_array(0,$array))
            {
				$market_push_list[$k]['chname'] .= "<p>通用</p>";
            }
            foreach ($cname as $k1 => $v1) 
			{
			  if($market_push_list[$k]['chname']=="")
			  {
			    if($k1==0)
				{
				    if(mb_strlen($v1['chname'],'utf-8')>10)
					{
					 $short_chname=mb_substr($v1['chname'],0,10,'utf-8');
					 $market_push_list[$k]['chname'].="<p>{$short_chname}</p>";
					}
					else
					{
					 $market_push_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
					}
				}
			    if($k1>=1)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
			  else
			  {
			    if($k1>=0)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
            }
            $did_str = substr($v['device_did'], 1, -1);
            $dname = $channel->table("pu_device")->where("did in ({$did_str})")->findAll();
            foreach ($dname as $k2 => $v2) 
			{
			    if($k2==0)
				 {
				    if(mb_strlen($v2['dname'],'utf-8')>10)
					{
					 $short_dname=mb_substr($v2['dname'],0,10,'utf-8');
					 $market_push_list[$k]['dname'].="<p>{$short_dname}</p>";
					}
					else
					{
					$market_push_list[$k]['dname'] .= "<p>{$v2['dname']}</p>";
					}
				 }
				if($k2>=1)
				{
				  $short_dname=mb_substr($v2['dname'],0,6,'utf-8');
				  $market_push_list[$k]['dname'] .= "<p>{$short_dname}...</p>";
				  break;
				}   
            }
            if($v['info_type']==2){
                $feature_db=M('feature');
                $map['status']=1;
                if(!empty($v['feature_id'])){
                    $map['feature_id']=$v['feature_id'];
                    $feature_name=$feature_db->where($map)->getfield('name');
                    $market_push_list[$k]['feature_name']=$feature_name;
                }
            }
			if(mb_strlen($v['push_area'],'utf8')>10)
			{ 
			 $short_area=mb_substr($v['push_area'],0,10,'utf-8');
			 $market_push_list[$k]['push_area'] =$short_area."...";
			}
			//显示覆盖人数  取csv和填写的最小值
			if($v['cover_num']==0&&$v['pre_dl_count']==0)
			{
			 $market_push_list[$k]['last_cover']="全部";
			}
			else
			{
			   if($v['cover_num']==0&&$v['pre_dl_count']!=0)
			   {
			    $market_push_list[$k]['last_cover']=$v['pre_dl_count'];
			   }
			   if($v['cover_num']!=0&&$v['pre_dl_count']==0)
			   {
			    $market_push_list[$k]['last_cover']=$v['cover_num'];
			   }
			   if($v['cover_num']!=0&&$v['pre_dl_count']!=0)
			   {
					if($v['cover_num']<=$v['pre_dl_count'])
					{
					  $market_push_list[$k]['last_cover']=$v['cover_num'];
					}
					else
					{
					  $market_push_list[$k]['last_cover']=$v['pre_dl_count'];
					}
			    }
			}
			//V6.0显示弹窗类型
			if($v['popup_type']==1)
			{
				$market_push_list[$k]['pop_style']="大图弹窗";
			}
			elseif($v['popup_type']==2)
			{
				$market_push_list[$k]['pop_style']="小图弹窗";
			}elseif($v['popup_type']==3) {
				$market_push_list[$k]['pop_style']="按钮弹窗";
			}elseif($v['popup_type']==4){
				$market_push_list[$k]['pop_style']="大图弹窗（下侧关闭）";
			}
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($v['co_type']);
			foreach($typelist as $key => $val){
				if($val[1] == true)
				{
					$market_push_list[$k]['co_types'] = $val[0];
				}
			}
        }

        $this -> assign('push_type',2);
        if ($_GET['p'])
            $this->assign('p', $_GET['p']);
        else
        $this->assign('p', '1');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("push_list",$market_push_list);
        $this->display();
    }
	//被动预下载  added by shitingting
	function market_pre_dl_list(){
        $sj_market_push=M("market_push");
        $channel=M("channel");
        
        import("@.ORG.Page");
        $where="status=1 and push_type = 3";
        if($_GET['zh_type'])
		{
            if($_GET['zh_type']==1)
			{
                $where .= " and start_tm <= ".time()." and end_tm >= ".time()."";

                $this->assign("zh_type",$_GET['zh_type']);
            }
			elseif($_GET['zh_type']==2)
			{
                $where .= " and end_tm < ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }
			elseif($_GET['zh_type']==3){
                $where .= " and start_tm > ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }
        }

        if(!empty($_GET['id'])){
            $where.=" and id=".$_GET['id'];
            $this->assign("so_id",$_GET['id']);
        }
		$soft_package=trim($_GET['soft_package']);
		$soft_name=trim($_GET['soft_name']);
		
        if(!empty($soft_package))
		{
            $where.=" and soft_package = '{$soft_package}'";
            $this->assign("so_soft_package",$soft_package);
        }
		 if(!empty($soft_name))
		 {
            $where.=" and soft_name = '{$soft_name}'";
            $this->assign("so_soft_name",$soft_name);
        }
        if(!empty($_GET['fromdate'])&& !empty($_GET['todate'])){
            $where.=" and start_tm <=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['todate'])))." and end_tm>=".strtotime(date('Y-m-d H:i:s',strtotime($_GET['fromdate'])));
            $this->assign("so_start_tm",$_GET['fromdate']);
            $this->assign("so_end_tm",$_GET['todate']);
        }
        $market_push_list=$sj_market_push->where($where)->select();
        $_SESSION['admin']['soft_list']['where'] = $where;

        $count = $sj_market_push->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
        $market_push_list=$sj_market_push->where($where)->order("start_tm desc,id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //$market_push_list=$sj_market_push->where("status=1")->select();
        $mongo = false;
        $collection = $mongodb = false;
        $mongo = false;
        $collection = $mongodb = false;
        
		//获取合作样式列表
		$util = D("Sj.Util"); 
		foreach($list as $key => $val) {
			$package[] = $val['package'];
			$package_result[$val['package']] = $val;
			
		}
        foreach ($market_push_list as $k => $v) 
		{
            $market_push_list[$k]['push_count']= 0;
            if ($collection) {
                $querys = array(
                    'push_id' => intval($v['id'])
                );
                $market_push_list[$k]['push_count'] = $collection->find($querys)->count();
            }


            $cid_str = substr($v['channel_id'], 1, -1);
            $array = explode(',', $cid_str);
            $cname = $channel->where("cid in ({$cid_str})")->findAll();
			if (in_array(0,$array))
            {
				$market_push_list[$k]['chname'] .= "<p>通用</p>";
            }
            foreach ($cname as $k1 => $v1) 
			{
			  if($market_push_list[$k]['chname']=="")
			  {
			    if($k1==0)
				{
					if(mb_strlen($v1['chname'],'utf-8')>10)
					{
					 $short_chname=mb_substr($v1['chname'],0,10,'utf-8');
					 $market_push_list[$k]['chname'].="<p>{$short_chname}</p>";
					}
					else
					{
					 $market_push_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
					 }
				}
			    if($k1>=1)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
			  else
			  {
			    if($k1>=0)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
               
            }
            $did_str = substr($v['device_did'], 1, -1);
            $dname = $channel->table("pu_device")->where("did in ({$did_str})")->findAll();
            foreach ($dname as $k2 => $v2) 
			{
			     if($k2==0)
				 {
				    if(mb_strlen($v2['dname'],'utf-8')>10)
					{
					 $short_dname=mb_substr($v2['dname'],0,10,'utf-8');
					 $market_push_list[$k]['dname'].="<p>{$short_dname}</p>";
					}
					else
					{
					 $market_push_list[$k]['dname'] .= "<p>{$v2['dname']}</p>";
					}
				 }
				 if($k2>=1)
					{
					  $short_dname=mb_substr($v2['dname'],0,6,'utf-8');
					  $market_push_list[$k]['dname'] .= "<p>{$short_dname}...</p>";
					  break;
					}
            }
            if($v['info_type']==2)
			{
                $feature_db=M('feature');
                $map['status']=1;
                if(!empty($v['feature_id'])){
                    $map['feature_id']=$v['feature_id'];
                    $feature_name=$feature_db->where($map)->getfield('name');
                    $market_push_list[$k]['feature_name']=$feature_name;
                }
            }
			if(mb_strlen($v['push_area'],'utf8')>10)
			{ 
			 $short_area=mb_substr($v['push_area'],0,10,'utf-8');
			 $market_push_list[$k]['push_area'] =$short_area."...";
			}
			//显示覆盖人数  取csv和填写的最小值
			if($v['cover_num']==0&&$v['pre_dl_count']==0)
			{
			 $market_push_list[$k]['last_cover']="全部";
			}
			else
			{
			   if($v['cover_num']==0&&$v['pre_dl_count']!=0)
			   {
			    $market_push_list[$k]['last_cover']=$v['pre_dl_count'];
			   }
			   if($v['cover_num']!=0&&$v['pre_dl_count']==0)
			   {
			    $market_push_list[$k]['last_cover']=$v['cover_num'];
			   }
			   if($v['cover_num']!=0&&$v['pre_dl_count']!=0)
			   {
					if($v['cover_num']<=$v['pre_dl_count'])
					{
					  $market_push_list[$k]['last_cover']=$v['cover_num'];
					}
					else
					{
					  $market_push_list[$k]['last_cover']=$v['pre_dl_count'];
					}
			    }
			}
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($v['co_type']);
			foreach($typelist as $key => $val){
				if($val[1] == true)
				{
					$market_push_list[$k]['co_types'] = $val[0];
				}
			}
        }
        $this -> assign('push_type',3);
        if ($_GET['p'])
            $this->assign('p', $_GET['p']);
        else
        $this->assign('p', '1');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("push_list",$market_push_list);
        $this->display();
    }
	
	//V6.5桌面红包
	function market_desk_red_list(){
        $sj_market_push=M("market_push");
        $channel=M("channel");
        import("@.ORG.Page");
        $where="status=1 and push_type = 5";
        if($_GET['zh_type']){
            if($_GET['zh_type']==1){
                $where .= " and start_tm <= ".time()." and end_tm >= ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==2){
                $where .= " and end_tm < ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }elseif($_GET['zh_type']==3){
                $where .= " and start_tm > ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }
        }
        if(!empty($_GET['id'])){
            $where.=" and id=".$_GET['id'];
            $this->assign("so_id",$_GET['id']);
        }
		
        if(!empty($_GET['red_type'])){
			$red_task_where = '%"red_type":"'.$_GET['red_type'].'"%';
            $where.=" and desk_red_text like '".$red_task_where."'";
            $this->assign("red_type",$_GET['red_type']);
        }
        if( !empty($_GET['fromdate']) ) {
        	$where .=" and start_tm >=".strtotime($_GET['fromdate']);
        	$this->assign("so_start_tm",$_GET['fromdate']);
        }
        if( !empty($_GET['todate']) ) {
        	$where .= " and end_tm<=".strtotime($_GET['todate']);
        	$this->assign("so_end_tm",$_GET['todate']);
        }
        $_SESSION['admin']['soft_list']['where'] = $where;
        $count = $sj_market_push->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $market_push_list=$sj_market_push->where($where)->order("start_tm desc,id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //$market_push_list=$sj_market_push->where("status=1")->select();
        $mongo = false;
        $collection = $mongodb = false;
        $mongo = false;
        $collection = $mongodb = false;
        //if (class_exists('mongo')) {
        //	$conn_str = '192.168.1.30:27017';
        //	$mongo = new Mongo($conn_str);
        //	$mongodb = $mongo->selectDB('push');
        //	$collection = $mongodb->receipt;
        //}
		//获取合作样式列表
		$util = D("Sj.Util"); 
        foreach ($market_push_list as $k => $v) {
            //echo $v['channel_id'];
            $market_push_list[$k]['push_count']= 0;
            if ($collection) {
                $querys = array(
                    'push_id' => intval($v['id'])
                );
                $market_push_list[$k]['push_count'] = $collection->find($querys)->count();
            }
            $cid_str = substr($v['channel_id'], 1, -1);
            $array = explode(',', $cid_str);
            $cname = $channel->where("cid in ({$cid_str})")->findAll();
			if (in_array(0,$array))
            {
				$market_push_list[$k]['chname'] .= "<p>通用</p>";
            }
            foreach ($cname as $k1 => $v1) 
			{
			  if($market_push_list[$k]['chname']=="")
			  {
			    if($k1==0)
				{
				    if(mb_strlen($v1['chname'],'utf-8')>10)
					{
					 $short_chname=mb_substr($v1['chname'],0,10,'utf-8');
					 $market_push_list[$k]['chname'].="<p>{$short_chname}</p>";
					}
					else
					{
					 $market_push_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
					}
				}
			    if($k1>=1)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
			  else
			  {
			    if($k1>=0)
				{
				  $short=mb_substr($v1['chname'],0,6,'utf-8');
				  $market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  break;
				}
			  }
            }
            $did_str = substr($v['device_did'], 1, -1);
            $dname = $channel->table("pu_device")->where("did in ({$did_str})")->findAll();
            foreach ($dname as $k2 => $v2) 
			{
			    if($k2==0)
				 {
				    if(mb_strlen($v2['dname'],'utf-8')>10)
					{
					 $short_dname=mb_substr($v2['dname'],0,10,'utf-8');
					 $market_push_list[$k]['dname'].="<p>{$short_dname}</p>";
					}
					else
					{
					$market_push_list[$k]['dname'] .= "<p>{$v2['dname']}</p>";
					}
				 }
				if($k2>=1)
				{
				  $short_dname=mb_substr($v2['dname'],0,6,'utf-8');
				  $market_push_list[$k]['dname'] .= "<p>{$short_dname}...</p>";
				  break;
				}   
            }
            if($v['info_type']==2){
                $feature_db=M('feature');
                $map['status']=1;
                if(!empty($v['feature_id'])){
                    $map['feature_id']=$v['feature_id'];
                    $feature_name=$feature_db->where($map)->getfield('name');
                    $market_push_list[$k]['feature_name']=$feature_name;
                }
            }
			if(mb_strlen($v['push_area'],'utf8')>10)
			{ 
			 $short_area=mb_substr($v['push_area'],0,10,'utf-8');
			 $market_push_list[$k]['push_area'] =$short_area."...";
			}
			//显示覆盖人数  取csv和填写的最小值
			if($v['cover_num']==0&&$v['pre_dl_count']==0)
			{
			 $market_push_list[$k]['last_cover']="全部";
			}
			else
			{
			   if($v['cover_num']==0&&$v['pre_dl_count']!=0)
			   {
			    $market_push_list[$k]['last_cover']=$v['pre_dl_count'];
			   }
			   if($v['cover_num']!=0&&$v['pre_dl_count']==0)
			   {
			    $market_push_list[$k]['last_cover']=$v['cover_num'];
			   }
			   if($v['cover_num']!=0&&$v['pre_dl_count']!=0)
			   {
					if($v['cover_num']<=$v['pre_dl_count'])
					{
					  $market_push_list[$k]['last_cover']=$v['cover_num'];
					}
					else
					{
					  $market_push_list[$k]['last_cover']=$v['pre_dl_count'];
					}
			    }
			}
			//V6.5显示获取红包信息
			$red_arr = json_decode($v['desk_red_text'],true);
			
			$market_push_list[$k]['red_type']=$red_arr['red_type'];
			
			if( $red_arr['red_id'] ) {
				$redPack_info = D('Sj.RedActivity')->get_red_package_info($red_arr['red_id']);
				$market_push_list[$k]['totalnum']=$redPack_info[0]['totalnum'];
				$market_push_list[$k]['totalmon']=$redPack_info[0]['totalmon'];
				$market_push_list[$k]['getmon']=$redPack_info[0]['getmon'];
				$market_push_list[$k]['getnum']=$redPack_info[0]['getnum'];
				$market_push_list[$k]['restmon']=$redPack_info[0]['restmon'];
				$market_push_list[$k]['restnum']=$redPack_info[0]['restnum'];
			}
			
			$market_push_list[$k]['cpm_pic']=$red_arr['desk_red_pop'];
			$market_push_list[$k]['high_image_url']=$red_arr['desk_red_high'];
			$market_push_list[$k]['low_image_url']=$red_arr['desk_red_low'];
			$market_push_list[$k]['red_soft_pkg']=$red_arr['red_soft_pkg'];
			//根据包名获取软件名称
			$red_soft_name=$sj_market_push->table('sj_soft')->where(array('package'=>$red_arr['red_soft_pkg']))->getfield('softname');
			$market_push_list[$k]['red_soft_name']=$red_soft_name;
			
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($v['co_type']);
			foreach($typelist as $key => $val){
				if($val[1] == true)
				{
					$market_push_list[$k]['co_types'] = $val[0];
				}
			}
        }
        $this -> assign('push_type',5);
        if ($_GET['p'])
            $this->assign('p', $_GET['p']);
        else
        $this->assign('p', '1');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("push_list",$market_push_list);
        $this->display();
    }

    //v6.4.9预约闹钟列表
    function market_pre_clock_list(){
    	$sj_market_push = M("market_push");
        $channel = M("channel");
        
        import("@.ORG.Page");
        $where = "status=1 and push_type = 6";
        if ($_GET['zh_type']) {
            if ($_GET['zh_type']==1) {
                $where .= " and daily_start_tm <= ".time()." and end_tm >= ".time()."";
                $this->assign("zh_type", $_GET['zh_type']);
            } else if ($_GET['zh_type']==2) {
                $where .= " and end_tm < ".time()."";
                $this->assign("zh_type", $_GET['zh_type']);
            } else if ($_GET['zh_type']==3) {
                $where .= " and daily_start_tm > ".time()."";
                $this->assign("zh_type",$_GET['zh_type']);
            }
        }

        if (!empty($_GET['id'])) {
            $where .= " and id=".$_GET['id'];
            $this->assign("so_id", $_GET['id']);
        }
		$soft_package = trim($_GET['soft_package']);
		$soft_name = trim($_GET['soft_name']);
		
        if (!empty($soft_package)) {
            $where .= " and info_title = '{$soft_package}'";
            $this->assign("so_soft_package", $soft_package);
        }
		 if (!empty($soft_name)) {
            $where .= " and btn_name = '{$soft_name}'";
            $this->assign("so_soft_name", $soft_name);
        }
        if (!empty($_GET['fromdate']) && !empty($_GET['todate'])) {
            $where .= " and daily_start_tm >=".strtotime($_GET['fromdate'])." and end_tm<=".strtotime($_GET['todate']);
            $this->assign("so_start_tm", $_GET['fromdate']);
            $this->assign("so_end_tm", $_GET['todate']);
        }

        $_SESSION['admin']['soft_list']['where'] = $where;

        $count = $sj_market_push->where($where)->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
        $market_push_list = $sj_market_push->where($where)->order("daily_start_tm desc, id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        $mongo = false;
        $collection = $mongodb = false;
        
		//获取合作样式列表
		$util = D("Sj.Util"); 
		foreach ($list as $key => $val) {
			$package[] = $val['package'];
			$package_result[$val['package']] = $val;
			
		}
        foreach ($market_push_list as $k => $v) {
            $market_push_list[$k]['push_count']= 0;
            if ($collection) {
                $querys = array(
                    'push_id' => intval($v['id'])
                );
                $market_push_list[$k]['push_count'] = $collection->find($querys)->count();
            }

            $cid_str = substr($v['channel_id'], 1, -1);
            $array = explode(',', $cid_str);
            $cname = $channel->where("cid in ({$cid_str})")->findAll();
			if (in_array(0,$array)) {
				$market_push_list[$k]['chname'] .= "<p>通用</p>";
            }
            foreach ($cname as $k1 => $v1) {
				if ($market_push_list[$k]['chname']=="") {
					if ($k1==0) {
						if (mb_strlen($v1['chname'], 'utf-8')>10) {
					 		$short_chname = mb_substr($v1['chname'], 0, 10, 'utf-8');
					 		$market_push_list[$k]['chname'] .= "<p>{$short_chname}</p>";
						} else {
					 		$market_push_list[$k]['chname'] .= "<p>{$v1['chname']}</p>";
						}
					}
					if ($k1>=1)	{
				  		$short = mb_substr($v1['chname'], 0, 6, 'utf-8');
				  		$market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  		break;
					}
				} else {
					if ($k1>=0) {
				  		$short = mb_substr($v1['chname'], 0, 6, 'utf-8');
				  		$market_push_list[$k]['chname'] .= "<p>{$short}...</p>";
				  		break;
					}
				}
            }
            $did_str = substr($v['device_did'], 1, -1);
            $dname = $channel->table("pu_device")->where("did in ({$did_str})")->findAll();
            foreach ($dname as $k2 => $v2) {
			    if ($k2==0) {
				    if (mb_strlen($v2['dname'], 'utf-8')>10) {
					 	$short_dname = mb_substr($v2['dname'], 0, 10, 'utf-8');
					 	$market_push_list[$k]['dname'] .= "<p>{$short_dname}</p>";
					} else {
					 	$market_push_list[$k]['dname'] .= "<p>{$v2['dname']}</p>";
					}
				}
				if ($k2>=1) {
					$short_dname = mb_substr($v2['dname'], 0, 6, 'utf-8');
					$market_push_list[$k]['dname'] .= "<p>{$short_dname}...</p>";
					break;
				}
            }
            if ($v['info_type']==2)	{
                $feature_db = M('feature');
                $map['status']=1;
                if (!empty($v['feature_id'])) {
                    $map['feature_id'] = $v['feature_id'];
                    $feature_name = $feature_db->where($map)->getfield('name');
                    $market_push_list[$k]['feature_name']=$feature_name;
                }
            }
			if (mb_strlen($v['push_area'], 'utf8')>10) { 
			 	$short_area = mb_substr($v['push_area'], 0, 10, 'utf-8');
			 	$market_push_list[$k]['push_area'] = $short_area."...";
			}
			//显示覆盖人数  取csv和填写的最小值
			if ($v['cover_num']==0 && $v['pre_dl_count']==0) {
			 	$market_push_list[$k]['last_cover'] = "全部";
			} else {
			   	if ($v['cover_num']==0 && $v['pre_dl_count']!=0) {
			    	$market_push_list[$k]['last_cover'] = $v['pre_dl_count'];
			   	}
			   	if ($v['cover_num']!=0 && $v['pre_dl_count']==0) {
			    	$market_push_list[$k]['last_cover'] = $v['cover_num'];
			   	}
			   	if ($v['cover_num']!=0 && $v['pre_dl_count']!=0) {
					if ($v['cover_num']<=$v['pre_dl_count']) {
					  	$market_push_list[$k]['last_cover'] = $v['cover_num'];
					} else {
					  	$market_push_list[$k]['last_cover'] = $v['pre_dl_count'];
					}
			    }
			}
			//合作形式
			$typelist = $util->getHomeExtentSoftTypeList($v['co_type']);
			foreach ($typelist as $key => $val) {
				if ($val[1] == true) {
					$market_push_list[$k]['co_types'] = $val[0];
				}
			}
        }
        $this -> assign('push_type', 6);
        if ($_GET['p']) {
            $this->assign('p', $_GET['p']);
        } else {
        	$this->assign('p', '1');
        }
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign("push_list", $market_push_list);
        $this->display();
    }

    function market_push_add(){

    /**
        *获取专题类别列表
    **/
        $feature_db=M('feature');
        $map['status']=1;
        $feature_list=$feature_db->where($map)->field('feature_id,name')->select();
        $this->assign('featurelist',$feature_list);
    /**
        *获取活动列表
    **/
        $activity = D('Sj.Activity');
        $activity_list = $activity->where(array('status' => 1))->field('id,name')->select();
        $this -> assign("push_type",$_GET['push_type']);
        $this->assign('activitylist', $activity_list);

    /**
        *获取机型列表
    **/
        $device_db=M();
        $map['status']=1;
        //$device_list=$device_db->table("pu_device")->where($map)->field('did,dname')->select();
        //$this->assign('devicelist',$device_list);


        $util = D('Sj.Util');
        //////////////////////// 应该处理的GET参数
        // 添加平台默认为市场
        $pid = 1;//默认为1
        $this->assign('pid', $pid);
        $product_list = $util->getProducts($pid);
        $this->assign('product_list',$product_list);

        //print_r($util->getMarketVersion());die;
        $this->assign('abilist', $util->getAbiList());

        $this->assign('firmwarelist', $util->getFirmwareList());
        $this->assign('version_list', $util->getMarketVersion());
        $this->assign('operator_list', $util->getOperators());
	
		//弹窗广告 按钮描述和按钮名称字数限制、图片大小限制
		$this->assign('btn_pic_des_limit', $this->btn_pic_des_limit);
		$this->assign('btn_pic_name_limit', $this->btn_pic_name_limit);
		
		$this->assign('big_pic_width',$this->big_pic_width);
		$this->assign('big_pic_height',$this->big_pic_height);
		$this->assign('big_pic_width_high',$this->big_pic_width_high);
		$this->assign('big_pic_height_high',$this->big_pic_height_high);
		$this->assign('big_pic_width_low',$this->big_pic_width_low);
		$this->assign('big_pic_height_low',$this->big_pic_height_low);
		
		$this->assign('small_pic_width_high',$this->small_pic_width_high);
		$this->assign('small_pic_height_high',$this->small_pic_height_high);
		$this->assign('small_pic_width_low',$this->small_pic_width_low);
		$this->assign('small_pic_height_low',$this->small_pic_height_low);
		
		$this->assign('btn_pic_width_high',$this->btn_pic_width_high);
		$this->assign('btn_pic_height_high',$this->btn_pic_height_high);
		$this->assign('btn_pic_width_low',$this->btn_pic_width_low);
		$this->assign('btn_pic_height_low',$this->btn_pic_height_low);
		
		//PUSH大图
		$this->assign('single_low_width',$this->single_low_width);
		$this->assign('single_low_height',$this->single_low_height);
		$this->assign('single_high_width',$this->single_high_width);
		$this->assign('single_high_height',$this->single_high_height);
		
		$this->assign('multiple_low_width',$this->multiple_low_width);
		$this->assign('multiple_low_height',$this->multiple_low_height);
		$this->assign('multiple_high_width',$this->multiple_high_width);
		$this->assign('multiple_high_height',$this->multiple_high_height);
		
		//V6.4的图片
		$this->assign('info_icon_width',$this->info_icon_width);
		$this->assign('info_icon_height',$this->info_icon_height);
		
		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList();
		$this->assign('typelist',$typelist);
		
        $this->display();
    }
    function market_push_addto()
	{
        $model = new Model();
        $sj_market_push = M("market_push");
		$push_model = D('Webmarket.MarketPush');
		
        //预约自动推送条件限制
        if($_POST['activity_id'] && $_POST['is_subscripe']){
            if (is_array($_POST['activity_id'])) {
                $activity_id = $_POST['activity_id'][0];
            } else {
                $activity_id = $_POST['activity_id'];
            }
            $where = array('activity_id' => $activity_id,'push_type' => $_POST['push_type'], 'is_subscripe' => 1);
            $result = $push_model -> table('sj_market_push') -> where($where) -> find();
            if($result) {
                if ($_POST['push_type'] == 1) {
                    $data = array(
                        'start_tm' => strtotime($_POST['fromdate']),
                        'end_tm' => strtotime($_POST['todate']),
                        'update_tm' => time(),
                    );
                    $push_model -> table('sj_market_push') -> where(array('id'=>$result['id'])) -> save($data);
                }
                echo '该活动已经存在';
                exit;
            }
            $info_list['is_subscripe'] = 1;
        }        
		
        $info_list['push_type'] = $_POST['push_type'];
		//上传覆盖人数和排除人数
		/*if(($info_list['push_type']==1&&$_POST['need_limit']==1)||($info_list['push_type']==1&&$_POST['need_limit']==2)||($info_list['push_type']==2)||($info_list['push_type']==3))*/
		if($_POST['need_limit'] !==0)
		{
			$file_result = $push_model-> save_file_csv($_POST,$_FILES,$info_list);
			if($file_result !== true)
			{	
				$this -> error($file_result);
			}
		}
		if($info_list['push_type'] == 1)
		{
			$push_num = count($_POST['info_type']);
			//多条PUSH概率之和必须等于100 如果不填则都不填
			if($push_num>1)
			{
				$sum = array_sum($_POST['odds']);
				if($sum !== 0)
				{
					$all_num = count($_POST['odds']);
					$write_num = count(array_filter($_POST['odds']));
					if($all_num !== $write_num)
					{
						$this->error("所有push概率都填写或者都不填写");
					}
					if($sum !== 100)
					{
						$this->error("所有push概率加一起不是100");
					}
				}
				$info_list['is_multiple_push'] = 1;
			}
			else
			{
				$info_list['is_multiple_push'] = 0;
				$info_list['push_odds'] = 100;
			}
			//PUSH内容处理
			$info = array();
			$diff_other = time().mt_rand(100000,999999);
			for($i=0;$i<$push_num;$i++)
			{
				$push_content_result = $push_model-> save_push_content($_POST,$i,'',$info_list);
				if($push_content_result !== true)
				{	
					$this -> error($push_content_result);
				}
				
				//6.0PUSH大图
				if($_FILES['push_single_low']['size'][$i]&&$_FILES['push_single_high']['size'][$i]&&$_FILES['push_multiple_low']['size'][$i]&&$_FILES['push_multiple_high']['size'][$i])
				{
					$rcontent=$this->upload_push_images($_FILES,$i,$info_list);
					if($rcontent!==true)
					{
						$this -> error($rcontent);
					}
				}
				elseif(!$_FILES['push_single_low']['size'][$i]&&!$_FILES['push_single_high']['size'][$i]&&!$_FILES['push_multiple_low']['size'][$i]&&!$_FILES['push_multiple_high']['size'][$i])
				{	
					//V6.4增加的图标图片非必填
					if($_FILES['push_info_icon']['size'][$i])
					{
						$rcontent=$this->upload_push_images($_FILES,$i,$info_list);
						if($rcontent!==true)
						{
							$this -> error($rcontent);
						}
					}
				}
				else
				{
					$this->error("单行多行的高低分图片都上传或者都不上传！");	
				}
				//PUSH设置
				$push_set_result = $push_model-> save_push_set($_POST,$info_list);
				if($push_set_result !== true)
				{	
					$this -> error($push_set_result);
				}
				
				//PUSH类型相对应的内容判断
				if($info_list['info_type'] == 1 || $info_list['info_type'] == 5 )
				{
					if($info_list['notice_type'] == 1 && $info_list['notice_type'] != 5){
						$package_result = $push_model->content_type_deal(1,$info_list);
						if($package_result !== true)
						{	
							$this -> error($package_result);
						}
					}elseif($info_list['notice_type'] == 2){
						$feature_result = $push_model->content_type_deal(2,$info_list);
						if($feature_result !== true)
						{	
							$this -> error($feature_result);
						}
					}elseif($info_list['notice_type'] == 3){
						$active_result = $push_model->content_type_deal(3,$info_list);
						if($active_result !== true)
						{	
							$this -> error($active_result);
						}
					}elseif($info_list['notice_type'] == 4){
						$web_result = $push_model->content_type_deal(4,$info_list);
						if($web_result !== true)
						{	
							$this -> error($web_result);
						}
					}
				}elseif($info_list['info_type'] == 2)
				{
					$feature_result = $push_model->content_type_deal(2,$info_list);
					if($feature_result !== true)
					{	
						$this -> error($feature_result);
					}
				}elseif($info_list['info_type'] == 3){
					$package_result = $push_model->content_type_deal(1,$info_list);
					if($package_result !== true)
					{	
						$this -> error($package_result);
					}
				}elseif($info_list['info_type'] == 6){
					$active_result = $push_model->content_type_deal(3,$info_list);
					if($active_result !== true)
					{	
						$this -> error($active_result);
					}
				}elseif($info_list['info_type'] == 8){
					$web_result = $push_model->content_type_deal(4,$info_list);
					if($web_result !== true)
					{	
						$this -> error($web_result);
					}
				}//v6.0增加市场页面//v6.3前面已经判断过
				/*elseif($info_list['info_type'] == 10)
				{
					$page_result = $push_model->content_type_deal(5,$info_list);
					if($page_result !== true)
					{	
						$this -> error($page_result);
					}
				}*/
				//辨别多条push为一组
				$info_list['which_push'] = $diff_other;
				
				//调用model的函数  公用保存数据函数
				$save_result = $push_model-> common_save($_POST,'',$info_list);
				if($save_result !== true)
				{
					$this -> error($save_result);
				}
				
				//删除为了方便判断类型内容增加的 page1和page4
				//unset($info_list['page_name1']);
				//unset($info_list['page_name4']);
				$info[$i] = $info_list;
			}
        }else if($info_list['push_type'] == 2){
			//弹窗内容和设置
			$pop_set_result = $push_model-> save_pop_content($_POST,'',$info_list);

			if($pop_set_result !== true)
			{	
				$this -> error($pop_set_result);
			}
			
			//弹窗图片处理
			$pop_pic=$this->upload_pop_images($_FILES,$_POST,'',$info_list);
			if($pop_pic!==true)
			{
				$this -> error($pop_pic);
			}
			//弹窗类型内容处理
			if($info_list['notice_type'] == 1){
				$package_result = $push_model->content_type_deal(1,$info_list);
				if($package_result !== true)
				{	
					$this -> error($package_result);
				}
            }elseif($info_list['notice_type'] == 2){
				$feature_result = $push_model->content_type_deal(2,$info_list);
				if($feature_result !== true)
				{	
					$this -> error($feature_result);
				}
            }elseif($info_list['notice_type'] == 3){
				$active_result = $push_model->content_type_deal(3,$info_list);
				if($active_result !== true)
				{	
					$this -> error($active_result);
				}
            }elseif($info_list['notice_type'] == 4){
				$web_result = $push_model->content_type_deal(4,$info_list);
				if($web_result !== true)
				{	
					$this -> error($web_result);
				}
            }/*elseif($info_list['notice_type'] == 5)//V6.0添加跳转页面
			{
				$page_result = $push_model->content_type_deal(5,$info_list);
				if($page_result !== true)
				{	
					$this -> error($page_result);
				}
            }*/
			
			//删除为了方便判断类型内容增加的 page1和page4
			//unset($info_list['page_name1']);
			//unset($info_list['page_name4']);
			//PUSH设置
			$push_set_result = $push_model-> save_push_set($_POST,$info_list);
			if($push_set_result !== true)
			{
				$this -> error($push_set_result);
			}
        }
		else if($info_list['push_type'] == 3)
		{
			//预下载内容和设置
			$pre_set_result = $push_model-> save_pre_content($_POST,$info_list);
			if($pre_set_result !== true)
			{	
				$this -> error($pre_set_result);
			}
		}
		else if($info_list['push_type'] == 5)
		{
			//桌面红包内容
			$desk_red_result = $push_model-> save_desk_red_content($_FILES,$_POST,'',$info_list);
			if($desk_red_result !== true)
			{	
				//$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_add/push_type/5');
				$this -> error($desk_red_result);
			}
			//桌面红包图片处理
			/*$pop_pic=$this->upload_desk_red_images($_FILES,$_POST,'',$info_list);
			if($pop_pic!==true)
			{
				$this -> error($pop_pic);
			}*/
			//桌面红包页面类型内容处理
			if($info_list['notice_type'] == 1){
				$package_result = $push_model->content_type_deal(1,$info_list);
				if($package_result !== true)
				{	
					$this -> error($package_result);
				}
            }elseif($info_list['notice_type'] == 2){
				$feature_result = $push_model->content_type_deal(2,$info_list);
				if($feature_result !== true)
				{	
					$this -> error($feature_result);
				}
            }elseif($info_list['notice_type'] == 3){
				$active_result = $push_model->content_type_deal(3,$info_list);
				if($active_result !== true)
				{	
					$this -> error($active_result);
				}
            }elseif($info_list['notice_type'] == 4){
				$web_result = $push_model->content_type_deal(4,$info_list);
				if($web_result !== true)
				{	
					$this -> error($web_result);
				}
            }/*elseif($info_list['notice_type'] == 5)//V6.0添加跳转页面
			{
				$page_result = $push_model->content_type_deal(5,$info_list);
				if($page_result !== true)
				{	
					$this -> error($page_result);
				}
            }*/
			
			//删除为了方便判断类型内容增加的 page1和page4
			//unset($info_list['page_name1']);
			//unset($info_list['page_name4']);
		}else if($info_list['push_type'] == 6){
			//弹窗内容和设置
			$pop_set_result = $push_model-> save_clock_content($_POST,'',$info_list);

			if($pop_set_result !== true)
			{	
				$this -> error($pop_set_result);
			}
			
			//图片处理
			$pop_pic=$this->upload_clock_images($_FILES,$_POST,'',$info_list);
			if($pop_pic!==true)
			{
				$this -> error($pop_pic);
			}
			//预约闹钟类型内容处理
			if($info_list['notice_type'] == 1){
				$package_result = $push_model->content_type_deal(1,$info_list);
				if($package_result !== true)
				{	
					$this -> error($package_result);
				}
            }elseif($info_list['notice_type'] == 2){
				$feature_result = $push_model->content_type_deal(2,$info_list);
				if($feature_result !== true)
				{	
					$this -> error($feature_result);
				}
            }elseif($info_list['notice_type'] == 3){
				$active_result = $push_model->content_type_deal(3,$info_list);
				if($active_result !== true)
				{	
					$this -> error($active_result);
				}
            }elseif($info_list['notice_type'] == 4){
				$web_result = $push_model->content_type_deal(4,$info_list);
				if($web_result !== true)
				{	
					$this -> error($web_result);
				}
            }/*elseif($info_list['notice_type'] == 5)//V6.0添加跳转页面
			{
				$page_result = $push_model->content_type_deal(5,$info_list);
				if($page_result !== true)
				{	
					$this -> error($page_result);
				}
            }*/
			
			//删除为了方便判断类型内容增加的 page1和page4
			//unset($info_list['page_name1']);
			//unset($info_list['page_name4']);
        }

		//调用model的函数  公用保存数据函数  弹窗和预下载使用
		$save_result = $push_model-> common_save($_POST,'',$info_list);
		if($save_result !== true)
		{
			$this -> error($save_result);
		}

		/*
		print_r(strtotime( '1970-01-01 '.$info_list['daily_start_tm']));
		print_r(strtotime( '1970-01-01 '.$info_list['daily_end_tm']));
		print_r($info_list);
		print_r($info);
		exit;
		*/
		//push的时候单独添加数据 都添加完成才跳转
		if($info_list['push_type'] == 1)
		{
			foreach($info as $key => $val)
			{
                if($val['soft_package'] && empty($_POST['is_subscripe'])){
                    //屏蔽软件上排期时报警需求 新增  yuesai
                    $AdSearch = D("Sj.AdSearch");
                    $shield_error=$AdSearch->check_shield($val['soft_package'],$val['start_tm'],$val['end_tm']);
                    if($shield_error){
                        $this -> error($shield_error);
                    }
                }
				$id = $this->link_push($val);
				if(!$id)
				{
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_list');
					$this->error("添加推送失败,发生错误！");
				}

				$val['id'] = $id;
				$val['pid'] = $_POST['pid'] ?  $_POST['pid'] :1;

				$id = $sj_market_push->add($val);
				if($id)
				{
					$getui = D('Getui');
					$getui->push_relate($id,$info_list);
					$this->writelog('增加了名称ID为['.$id.']标题为['.$val['info_title'].']的推送信息', 'sj_market_push',$id,__ACTION__ ,"","add");
				}
				else
				{
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_list');
					$this->error("添加推送失败,发生错误！");
				}
			}
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_list/push_type/1');
			$this->success("添加推送成功！");
		}
		else  
		{
            if($info_list['soft_package']&& empty($_POST['is_subscripe'])){
                //屏蔽软件上排期时报警需求 新增  yuesai
                $AdSearch = D("Sj.AdSearch");
                $shield_error=$AdSearch->check_shield($info_list['soft_package'],$info_list['start_tm'],$info_list['end_tm']);
                if($shield_error){
                    $this -> error($shield_error);
                }
            }
			
			$id = $this->link_push($info_list);
			if(!$id)
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_list');
				$this->error("添加推送失败,发生错误！");
			}
			$info_list['id'] = $id;

            if($_POST['activity_id'] && $_POST['is_subscripe']){
                $info_list['is_subscripe'] = 1;
                $info_list['activity_id'] = is_array($_POST['activity_id']) ? $_POST['activity_id'][0] : $_POST['activity_id'];
            }  
            //添加平台
          	if($_POST['push_type'] == 2 ||$_POST['push_type'] == 1 ||$_POST['push_type'] == 6){
           		$info_list['pid'] = $_POST['pid']?$_POST['pid']:1;
			}else{
				$info_list['pid'] = 1;
			}

			$id = $sj_market_push->add($info_list);
			if($id) {

				if($info_list['push_type'] == 2) {
					$this->writelog('增加了名称ID为['.$id.']弹窗名称为['.$info_list['cpm_name'].']的推送信息', 'sj_market_push', $id,__ACTION__ ,"","add");
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_cpm_list/push_type/2');
					$this->success("添加弹窗广告成功！");
				}else if($info_list['push_type'] == 3) {
					$this->writelog('增加了名称ID为['.$id.']的推送信息', 'sj_market_push', $id,__ACTION__ ,"","add");
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_pre_dl_list/push_type/3');
					$this->success("添加被动预下载成功！");
				}else if($info_list['push_type'] == 5) {
					//红包是直接发放类型
					$red_info = json_decode($info_list['desk_red_text'], true);
					$bind_ext_data	=	array('name'=>'桌面红包');
					if( $red_info['red_id'] ) {
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $id, $red_info['red_id'],'', $red_info['task_id'],$bind_ext_data);
						if( $bind_info['STATUS'] == 1 ) {
							//更改sj_red_packet_conf中红包id为$_post['red_id']的状态
							$desk_status = array(
								'desktop' => 2, //2是push推送
							);
							$red_conf_update = $model->table("sj_red_packet_conf")->where(array('id'=>$red_info['red_id']))->save($desk_status);
							$this->writelog("修改ID".$red_info['red_id']."为PUSH推送的桌面红包",'sj_red_packet_conf',$red_info['red_id'],__ACTION__ ,"","edit");
						}else {
							$msg_error = $bind_info['MSG'];
							
							$red_info['red_type']		=	1;
							$red_info['red_soft_pkg']	=	'';
							$red_info['red_soft_name']	=	'';
							$red_info['red_id']			=	'';
							$red_info['task_id']		=	'';
							$red_info['red_num']		=	'';
							$red_info['totalmon']		=	'';
							$red_info['red_task_content1']	=	'';
							$red_info['red_task_content2']	=	'';
							$desk_data_json = array(
								'status'		=>	0,
								'desk_red_text'	=>	json_encode($red_info)
							);
							$sj_market_push->where(array('id' => $id))->save($desk_data_json);
							$this->writelog('增加了名称ID为['.$id.']的推送信息', 'sj_market_push', $id,__ACTION__ ,"","add");
							$this->error($msg_error);
						}
					}
					$this->writelog('增加了名称ID为['.$id.']的推送信息', 'sj_market_push', $id,__ACTION__ ,"","add");
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_desk_red_list/push_type/5');
					$this->success("添加桌面红包成功！");
				}else if($info_list['push_type'] == 6) {
					$this->writelog('增加了名称ID为['.$id.']的推送信息', 'sj_market_push', $id,__ACTION__ ,"","add");
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_pre_clock_list/push_type/6');
					$this->success("添加预约闹钟成功！");
				}
			}else{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_list');
				$this->error("添加推送失败,发生错误！");
			}
		} 
    }
    
	private function link_push($data, $id = 0) {
		$Push = D("Sj.Push");
	
		//var_dump($data);exit;

		$init_date = '1970-01-01 ';

		$new_data = array(
			'start_time' => $data['start_tm'],
			'end_time' => $data['end_tm']
		);

		if(!empty($data['start_tm']) && !empty($data['end_tm'])) {
			$new_data['start_time'] = $data['start_tm'];
			$new_data['end_time']	= $data['end_tm'];
		}
		
		if(isset($data['status'])) {
			$new_data['status'] = $data['status'] == 0 ? 0 : 1;
		}
		
		if(!empty($data['daily_start_tm'])) {
			$new_data['item_show_time_start'] = (strtotime( $init_date . $data['daily_start_tm']) + (60*60*8));
		}
		if(!empty($data['daily_end_tm'])) {
			$new_data['item_show_time_end'] = (strtotime( $init_date . $data['daily_end_tm']) + (60*60*8));
		}

		$channel = 1;
		
		//区分"什么值得买"
		if (!empty($_POST['pid']) && $_POST['pid'] == 20) {
			$channel = 20;
		}

		$new_data['channel'] = $channel;
		$new_id = 0;

		if($id) {
			$new_id = $Push->editMarketOldPush($id,$new_data);
		} else {
			$new_id = $Push->addMarketOldPush($new_data);
		}
		return $new_id;
	}

    function package_check(){
        $model = new Model();
        $package = $_GET['soft_package'];
        $result = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
        if($result){
            echo json_encode($result[0]['softname']);
            return json_encode($result[0]['softname']);
        }else{
            echo json_encode(1);
            return json_encode(1);
        }
    }


    function market_push_del(){
        $sj_market_push=M("market_push");
        $id = intval($_GET['id']);
		$my_result = $sj_market_push -> where(array('id' => $id)) -> select();
		if($my_result[0]['push_type'] == 5){
			$red_info = json_decode($my_result[0]['desk_red_text'], true);
			if( $red_info['red_id'] ) {
				//解除绑定
				$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $id, $red_info['red_id'], $red_info['red_id']);
				if( $bind_info['STATUS'] == 1) {
					$desk_status = array(
							'desktop' => 0, //是否是桌面红包 0，不是， 1，定制 2，push
					);
					$model = new model();
					$model->table("sj_red_packet_conf")->where(array('id'=>$red_info['red_id']))->save($desk_status);
				}else {
					$this->error($bind_info['MSG']);
				}
			}
		}
		$this->link_push(array('status' => 0),$id);
		$affect = $sj_market_push -> query("update __TABLE__ set status = 0 where id = " .$id);
        $this->writelog('删除了ID为['.$id.']的推送信息', 'sj_market_push', $id,__ACTION__ ,"","del");
        if($my_result[0]['push_type'] == 1){
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_push_list/push_type/1/p/{$_GET['p']}");
        }else if($my_result[0]['push_type'] == 2){
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_cpm_list/push_type/2/p/{$_GET['p']}");
        }else if($my_result[0]['push_type'] == 3){
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_pre_dl_list/push_type/3/p/{$_GET['p']}");
        }else if($my_result[0]['push_type'] == 5){
        	$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_desk_red_list/push_type/5/p/{$_GET['p']}");
        }else if($my_result[0]['push_type'] == 6){
        	$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_pre_clock_list/push_type/6/p/{$_GET['p']}");
        }
        $this->success('删除成功');
    }
    function market_push_edit(){
        $sj_market_push=M("market_push");
    /**
        *获取专题类别列表
    **/
        $feature_db=M('feature');
        $map['status']=1;
        $feature_list=$feature_db->where($map)->field('feature_id,name')->select();
        $this->assign('featurelist',$feature_list);


    /**
        *获取活动列表
    **/
        $activity = D('Sj.Activity');
        $activity_list = $activity->where(array('status' => 1))->field('id,name')->select();
        $this->assign('activitylist', $activity_list);

    /**
        *获取机型列表
    **/
        $device_db=M();
        $channel_model = M('channel');
        $map['status']=1;
        //$device_list=$device_db->table("pu_device")->where($map)->field('did,dname')->select();
        //$this->assign('devicelist',$device_list);
        $where['id']=$_GET['id'];
        $where['status']=1;
		
		//查找到记录
        $sj_market_push_one=$sj_market_push->where($where)->find();
		
		//echo date('Y-m-d H:i:s', $sj_market_push_one['start_tm']);die;
		//渠道
        $cookstr = substr($sj_market_push_one['channel_id'], 1, -1);
        $array = explode(',', $cookstr);
        $area_list=explode(';',$sj_market_push_one['push_area']);
        $chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
        if (in_array("0",$array)&&$chl!=NULL)
		{
		  $tong = array("cid"=> "0" ,"chname"=> "通用");
		  array_unshift($chl, $tong);
		}
        if (in_array("0",$array)&&$chl==NULL)
		{
		  $chl[0]['cid']="0";
		  $chl[0]['chname']="通用";
		}
		

        if (strlen($sj_market_push_one['channel_id']) > 0) {
            $device_selected = explode(',', $sj_market_push_one['device_did']);
            $device_selected_ret = array();
            foreach ($device_selected as $ds) {
                if (empty($ds)) continue;
                $device_name = $device_db->table("pu_device")->where(array('did' => $ds))->field('did,dname')->select();
                $device_selected_ret[] = array('did' => $ds,'dname' => $device_name[0]['dname']);
            }
            $this->assign('device_selected', $device_selected_ret);

        }

        $util = D('Sj.Util');
        $this->assign('abilist', $util->getAbiList($sj_market_push_one['abi']));
        $this->assign('firmwarelist', $util->getFirmwareList(explode(',', $sj_market_push_one['firmware'])));
        $this->assign('version_list', $util->getMarketVersion(explode(',', $sj_market_push_one['version_code'])));
        $this->assign('operator_list', $util->getOperators(explode(',', $sj_market_push_one['oid'])));
		$this->assign('choose_version_list', $util->getMarketVersion(explode(',', $sj_market_push_one['choose_version_code'])));
		
		
		//PUSH为多条显示
		//var_dump($sj_market_push_one['is_multiple_push']);exit;
		if($sj_market_push_one['push_type'] == 1)
		{
			if($sj_market_push_one['which_push'])
			{
				$other_where =array(
					'which_push' => $sj_market_push_one['which_push'],
					'status' =>1,
				);
			}
			else
			{
				$other_where =array(
					'id' => $_GET['id'],
					'status' =>1,
				);
			}
			$all_push = $sj_market_push -> where($other_where) ->select();
			foreach($all_push as $key => $val)
			{

				//V6.0 英文页面标识转换汉字显示 
				if($val['market_page_name'])
				{
					$page_type = $val['market_page_name'];
				
					$page_name = ContentTypeModel::convertPageType2PageName($page_type);
					if (!$page_name)
					{
						$all_push[$key]['market_push_page_names']="";
						//$market_push_page_names="";
					}
					else
					{
						$all_push[$key]['market_push_page_names']=$page_name;
						//$market_push_page_names=$page_name;
					}
				}
                if($all_push[$key]['info_title']){
                    $info_title=preg_replace('#<font color="([^"]+)">#','<span style="color:\1;">', $all_push[$key]['info_title']);
                    $info_title= preg_replace('#</font>#', '</span>', $info_title);
                    $info_title= preg_replace('#<font>#', '<span>', $info_title);
                    // var_dump($info_title);die;
                    // $info_title=preg_replace('#<font color="([^"]+)">([^<]+)</font>#','<span style="color:\1;">\2</span>', $all_push[$key]['info_title']);
                    $all_push[$key]['info_title']=$info_title;
                }
                if($all_push[$key]['info_content']){
                    $info_content=preg_replace('#<font color="([^"]+)">#','<span style="color:\1;">', $all_push[$key]['info_content']);
                    $info_content= preg_replace('#</font>#', '</span>', $info_content);
                    $info_content= preg_replace('#<font>#', '<span>', $info_content);
                    // $info_content=preg_replace('#<font color="([^"]+)">([^<]+)</font>#','<span style="color:\1;">\2</span>', $all_push[$key]['info_content']);
                    $all_push[$key]['info_content']=$info_content;
                }
				//概率显示
				if($val['push_odds'] ==0)
				{
					$all_push[$key]['push_odds_show'] ="";
				}
				else
				{
					$all_push[$key]['push_odds_show'] =$val['push_odds'];
				}
				//合作形式
				$util = D("Sj.Util");
				$all_push[$key]['co_type'] = $util->getHomeExtentSoftTypeList($val['co_type']);
				if(!empty($all_push[$key]['parameter_field'])){
					$info = json_decode($all_push[$key]['parameter_field'],true);
					$all_push[$key]['is_sync_accout'] = $info['website_is_sync_accout'];
					$all_push[$key]['is_actionbar'] = $info['website_is_actionbar'];
					$all_push[$key]['screen_show'] = $info['website_screen_show'];
					$all_push[$key]['is_h5'] = $info['website_is_h5'];
				}
				//商务活跃  合作渠道
				if($val['push_info_type']==6)
				{
					$co_cookstr = substr($val['business_co_channel_id'], 1, -1);
					$co_array = explode(',', $co_cookstr);
					$co_chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $co_cookstr . ')')->select();
					if (in_array("0",$co_array)&&$co_chl!=NULL)
					{
					  $co_tong = array("cid"=> "0" ,"chname"=> "通用");
					  array_unshift($co_chl, $co_tong);
					}
					if (in_array("0",$co_array)&&$co_chl==NULL)
					{
					  $co_chl[0]['cid']="0";
					  $co_chl[0]['chname']="通用";
					}
					$all_push[$key]['co_chl'] =$co_chl;
				}
			}
		}
		
		//PUSH设置和弹窗显示
		if(!$sj_market_push_one['activation_day_end'])
		{
		//	$sj_market_push_one['activation_day_end']='';
		}
		$sj_market_push_one['daily_start_tms'] = date('Y-m-d H:i:s',$sj_market_push_one['daily_start_tm']);
        $sj_market_push_one['start_tms'] = date('Y-m-d H:i:s',$sj_market_push_one['start_tm']);
        $sj_market_push_one['end_tms'] = date('Y-m-d H:i:s',$sj_market_push_one['end_tm']);
		if($sj_market_push_one['activation_date_start'])
		{
			$sj_market_push_one['activation_start_tms'] = date('Y-m-d H:i:s',$sj_market_push_one['activation_date_start']);
		}
		else
		{
			$sj_market_push_one['activation_start_tms'] = '';
		}
		if($sj_market_push_one['activation_date_end'])
		{
			$sj_market_push_one['activation_end_tms'] = date('Y-m-d H:i:s',$sj_market_push_one['activation_date_end']);
		}
        else
		{
			$sj_market_push_one['activation_end_tms'] = '';
		}
		
		//V6.0 英文页面标识转换汉字显示 下面的push内容可能是多条显示
		if($sj_market_push_one['market_page_name'])
		{
			$page_type = $sj_market_push_one['market_page_name'];
		
			$page_name = ContentTypeModel::convertPageType2PageName($page_type);
			if (!$page_name)
			{
				$market_push_page_names="";
			}
			else
			{
				$market_push_page_names=$page_name;
			}
		}
		if(!empty($sj_market_push_one['parameter_field'])){
			$info = json_decode($sj_market_push_one['parameter_field'],true);
			$sj_market_push_one['is_sync_accout'] = $info['website_is_sync_accout'];
			$sj_market_push_one['is_actionbar'] = $info['website_is_actionbar'];
			$sj_market_push_one['screen_show'] = $info['website_screen_show'];
			$sj_market_push_one['is_h5'] = $info['website_is_h5'];
		}
		
		//V6.5红包id和任务软件展示
		if($sj_market_push_one['desk_red_text']&&$sj_market_push_one['push_type']==5)
		{
			$red_detail_arr = json_decode($sj_market_push_one['desk_red_text'],true);
			$this->assign('red_detail_arr',$red_detail_arr);
		}

		//合作形式
		$util = D("Sj.Util");
		$typelist = $util->getHomeExtentSoftTypeList($sj_market_push_one['co_type']);
		$this->assign('typelist',$typelist);
			
        $this->assign("thepid",$sj_market_push_one['feature_id']);
        $this->assign('chl_list', $chl);
        $this -> assign("push_type",$sj_market_push_one['push_type']);
        $this->assign("push_list",$sj_market_push_one);
		//PUSH显示
		$this->assign("all_push_list",$all_push);
		$this -> assign("push_count",count($all_push));
		$this -> assign("last_id",$_GET['id']);
		
        //var_dump ($area_list);exit;
        $this->assign("push_area",$area_list);
        $this -> assign("p",$_GET['p']);
		
		$this -> assign("market_push_page_names",$market_push_page_names);
		
		//PUSH大图
		$this->assign('single_low_width',$this->single_low_width);
		$this->assign('single_low_height',$this->single_low_height);
		$this->assign('single_high_width',$this->single_high_width);
		$this->assign('single_high_height',$this->single_high_height);
		
		$this->assign('multiple_low_width',$this->multiple_low_width);
		$this->assign('multiple_low_height',$this->multiple_low_height);
		$this->assign('multiple_high_width',$this->multiple_high_width);
		$this->assign('multiple_high_height',$this->multiple_high_height);
		
		//V6.4的图片
		$this->assign('info_icon_width',$this->info_icon_width);
		$this->assign('info_icon_height',$this->info_icon_height);
		
		//弹窗广告 按钮描述和按钮名称字数限制、图片大小限制
		$this->assign('btn_pic_des_limit', $this->btn_pic_des_limit);
		$this->assign('btn_pic_name_limit', $this->btn_pic_name_limit);
		
		$this->assign('big_pic_width',$this->big_pic_width);
		$this->assign('big_pic_height',$this->big_pic_height);
		$this->assign('big_pic_width_high',$this->big_pic_width_high);
		$this->assign('big_pic_height_high',$this->big_pic_height_high);
		$this->assign('big_pic_width_low',$this->big_pic_width_low);
		$this->assign('big_pic_height_low',$this->big_pic_height_low);
		
		$this->assign('small_pic_width_high',$this->small_pic_width_high);
		$this->assign('small_pic_height_high',$this->small_pic_height_high);
		$this->assign('small_pic_width_low',$this->small_pic_width_low);
		$this->assign('small_pic_height_low',$this->small_pic_height_low);
		
		$this->assign('btn_pic_width_high',$this->btn_pic_width_high);
		$this->assign('btn_pic_height_high',$this->btn_pic_height_high);
		$this->assign('btn_pic_width_low',$this->btn_pic_width_low);
		$this->assign('btn_pic_height_low',$this->btn_pic_height_low);
		
		//V6.5桌面红包图片
		$this->assign('id',$_GET['id']);

		$util = D('Sj.Util');
        //////////////////////// 应该处理的GET参数
        // 添加平台默认为市场
        $pid = $sj_market_push_one['pid'];
        $this->assign('pid', $pid);
        $product_list = $util->getProducts($pid);
        $this->assign('product_list',$product_list);
		
        $this->display();
    }
    function market_push_editto()
	{
        $sj_market_push=M("market_push");
        $model = new Model();
        $channel_model = M('channel');
		$push_model = D('Webmarket.MarketPush');
		//传来的有push内容 PUSH多条可以全删 
		if($_POST['id'])
		{
			if(is_array($_POST['id']))
			{
				$id=$_POST['id'][0];
			}
			else
			{
				$id=$_POST['id'];
			}
		}
		else
		{ //多条push 全删的情况
			$id=$_POST['last_id'];
		}
		
		$where['id']=$id;
		$where['status']=1;
		//判断是否为 push、弹窗、预下载
		$info_list['push_type']=$sj_market_push->where($where)->getfield("push_type");
		
		//上传覆盖人数 排除人数
		/*if(($info_list['push_type'] == 1&&$_POST['need_limit']==1)||($info_list['push_type'] == 1&&$_POST['need_limit']==2)||($info_list['push_type'] == 2&&$_POST['need_limit']==1)||($info_list['push_type'] == 2&&$_POST['need_limit']==2)||($info_list['push_type'] == 3&&$_POST['need_limit']==1)||($info_list['push_type'] == 3&&$_POST['need_limit']==2))*/
		if($_POST['need_limit']!==0)
		{
			$file_result = $push_model-> save_edit_file_csv($_POST,$_FILES,$info_list);
			if($file_result !== true)
			{	
				$this -> error($file_result);
			}
		}
		
        if($info_list['push_type'] == 1)
		{	
			//查找一组的Push
			$sj_market_push_one=$sj_market_push->where(array('id' => $id))->find();
			
			if($sj_market_push_one['which_push'])//查找所有多条
			{
				$other_where =array(
					'which_push' => $sj_market_push_one['which_push'],
					'status' =>1,
				);
				$all_push = $sj_market_push -> where($other_where) ->select();
				$all_id =array();
				foreach($all_push as $key => $val)
				{
					$all_id[] = $val['id'];
				}
			}
		
			
			if($_POST['id'])
			{	
				if($all_id)
				{
					//表示删除的id数组
					$del_id = array_diff($all_id,$_POST['id']);
				}
				$push_num = count($_POST['id']);
				//多条PUSH概率之和必须等于100 如果不填则都不填
				$sum = array_sum($_POST['odds']);
				if($sum !== 0)
				{
					$all_num = count($_POST['odds']);
					$write_num = count(array_filter($_POST['odds']));
					if($all_num !== $write_num)
					{
						$this->error("所有push概率都填写或者都不填写");
					}
					if($sum !== 100)
					{
						$this->error("所有push概率加一起不是100");
					}
				}
				if($push_num>1)
				{
					$info_list['is_multiple_push'] = 1;
				}
				else
				{
					//如果是单条
					$info_list['is_multiple_push'] = 0;
					$info_list['push_odds'] = 100;
				}
				//PUSH内容处理
				$info = array();
				for($i=0;$i<$push_num;$i++)
				{
					$push_id=$_POST['id'][$i];
					$push_where=array(
						'status' => 1,
						'id' =>$push_id,
					);
					//信息类型
					$info_list['info_type']=$sj_market_push->where($push_where)->getfield("info_type");
					//图片
					$result_image=$sj_market_push->where($push_where)->find();
				
					$push_content_result = $push_model-> save_push_content($_POST,$i,$push_id,$info_list);
					if($push_content_result !== true)
					{	
						$this -> error($push_content_result);
					}
					
					//6.0PUSH大图
					if($_FILES['push_single_low']['size'][$i]&&$_FILES['push_single_high']['size'][$i]&&$_FILES['push_multiple_low']['size'][$i]&&$_FILES['push_multiple_high']['size'][$i])
					{
						$rcontent=$this->upload_push_images($_FILES,$i,$info_list);
						if($rcontent!==true)
						{
							$this -> error($rcontent);
						}
					}
					elseif(!$_FILES['push_single_low']['size'][$i]&&!$_FILES['push_single_high']['size'][$i]&&!$_FILES['push_multiple_low']['size'][$i]&&!$_FILES['push_multiple_high']['size'][$i])
					{	
						//V6.4增加的图标图片非必填
						if($_FILES['push_info_icon']['size'][$i])
						{
							$rcontent=$this->upload_push_images($_FILES,$i,$info_list);
							if($rcontent!==true)
							{
								$this -> error($rcontent);
							}
						}
					}
					else
					{
						if($result_image['push_single_low'])
						{
							if($_FILES['push_single_low']['size'][$i]||$_FILES['push_single_high']['size'][$i]||$_FILES['push_multiple_low']['size'][$i]||$_FILES['push_multiple_low']['size'][$i])
							{
								$rcontent=$this->upload_push_images($_FILES,$i,$info_list);
								if($rcontent!==true)
								{
									$this -> error($rcontent);
								}
							}
						}
						else
						{
							$this->error("单行多行的高低分图片都上传或者都不上传！");	
						}
					}
					
					//类型内容处理
					if($info_list['info_type'] == 1 || $info_list['info_type'] == 5 || $info_list['info_type'] == 3)
					{
						if($info_list['notice_type'] == 1 && $info_list['notice_type'] != 5){
							
							$package_result = $push_model->content_type_deal(1,$info_list);
							if($package_result !== true)
							{	
								$this -> error($package_result);
							}
						}elseif($info_list['notice_type'] == 2){
							$feature_result = $push_model->content_type_deal(2,$info_list);
							if($feature_result !== true)
							{	
								$this -> error($feature_result);
							}
						}elseif($info_list['notice_type'] == 3){
							$active_result = $push_model->content_type_deal(3,$info_list);
							if($active_result !== true)
							{	
								$this -> error($active_result);
							}
						}elseif($info_list['notice_type'] == 4){
							$web_result = $push_model->content_type_deal(4,$info_list);
							if($web_result !== true)
							{	
								$this -> error($web_result);
							}
						}
					}elseif($info_list['info_type'] == 2){
						$feature_result = $push_model->content_type_deal(2,$info_list);
						if($feature_result !== true)
						{	
							$this -> error($feature_result);
						}
					}elseif($info_list['info_type'] == 3){
						$package_result = $push_model->content_type_deal(1,$info_list);
						if($package_result !== true)
						{	
							$this -> error($package_result);
						}
					}elseif($info_list['info_type'] == 6){
						$active_result = $push_model->content_type_deal(3,$info_list);
						if($active_result !== true)
						{	
							$this -> error($active_result);
						}
					}elseif($info_list['info_type'] == 8){
						$web_result = $push_model->content_type_deal(4,$info_list);
						if($web_result !== true)
						{	
							$this -> error($web_result);
						}
					}/*elseif($info_list['info_type'] == 10)//V6.0添加跳转页面
					{
						$page_result = $push_model->content_type_deal(5,$info_list);
						if($page_result !== true)
						{	
							$this -> error($page_result);
						}
					}*/
					
					//PUSH设置
					$push_set_result = $push_model-> save_push_set($_POST,$info_list);
					if($push_set_result !== true)
					{	
						$this -> error($push_set_result);
					}	
					//删除为了方便判断类型内容增加的 page1和page4
					//unset($info_list['page_name1']);
					//unset($info_list['page_name4']);
					
					//调用model的函数  公用保存数据函数  弹窗和预下载使用
					$save_result = $push_model-> common_save($_POST,$push_id,$info_list);
					if($save_result !== true)
					{
						$this -> error($save_result);
					}
					//编辑的时候把id填进去  这样对应ID修改
					$info_list['id'] = $push_id;
					$info[$i] = $info_list;
				}
			}else {
				$del_id = $all_id;
			}
        }else if($info_list['push_type'] == 2) {
			//弹窗内容和设置
			$pop_set_result = $push_model-> save_pop_content($_POST,$id,$info_list);
			if($pop_set_result !== true)
			{	
				$this -> error($pop_set_result);
			}
			//弹窗图片处理
			$pop_pic=$this->upload_pop_images($_FILES,$_POST,$id,$info_list);
			if($pop_pic!==true)
			{
				$this -> error($pop_pic);
			}
			//类型内容处理
			if($info_list['notice_type'] == 1)
			{
				//包名判断
				$package_result = $push_model->content_type_deal(1,$info_list);
				if($package_result !== true)
				{	
					$this -> error($package_result);
				}
            }elseif($info_list['notice_type'] == 2){
				$feature_result = $push_model->content_type_deal(2,$info_list);
				if($feature_result !== true)
				{	
					$this -> error($feature_result);
				}
            }elseif($info_list['notice_type'] == 3){
				$active_result = $push_model->content_type_deal(3,$info_list);
				if($active_result !== true)
				{	
					$this -> error($active_result);
				}
				
            }elseif($info_list['notice_type'] == 4){
				$web_result = $push_model->content_type_deal(4,$info_list);
				if($web_result !== true)
				{	
					$this -> error($web_result);
				}
            }/*elseif($info_list['notice_type'] == 5)//V6.0添加跳转页面
			{
				$page_result = $push_model->content_type_deal(5,$info_list);
				if($page_result !== true)
				{	
					$this -> error($page_result);
				} 
            }*/
			//删除为了方便判断类型内容增加的 page1和page4
			//unset($info_list['page_name1']);
			//unset($info_list['page_name4']);
			//PUSH设置
			$push_set_result = $push_model-> save_push_set($_POST,$info_list);
			if($push_set_result !== true)
			{
				$this -> error($push_set_result);
			}
		}elseif($info_list['push_type'] == 3) {
			//预下载内容和设置
			$pre_set_result = $push_model-> save_pre_content($_POST,$info_list);
			if($pre_set_result !== true)
			{	
				$this -> error($pre_set_result);
			}
		}elseif($info_list['push_type'] == 5) {
			//桌面红包内容
			$desk_red_result = $push_model-> save_desk_red_content($_FILES,$_POST,$id,$info_list);
			if($desk_red_result !== true)
			{	
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_edit/id/'.$id);
				$this -> error($desk_red_result);
			}
			//桌面红包图片处理
			/*$pop_pic=$this->upload_desk_red_images($_FILES,$_POST,$id,$info_list);
			if($pop_pic!==true)
			{
				$this -> error($pop_pic);
			}*/
			//桌面红包页面类型内容处理
			if($info_list['notice_type'] == 1){
				$package_result = $push_model->content_type_deal(1,$info_list);
				if($package_result !== true)
				{	
					$this -> error($package_result);
				}
            }elseif($info_list['notice_type'] == 2){
				$feature_result = $push_model->content_type_deal(2,$info_list);
				if($feature_result !== true)
				{	
					$this -> error($feature_result);
				}
            }elseif($info_list['notice_type'] == 3){
				$active_result = $push_model->content_type_deal(3,$info_list);
				if($active_result !== true)
				{	
					$this -> error($active_result);
				}
            }elseif($info_list['notice_type'] == 4){
				$web_result = $push_model->content_type_deal(4,$info_list);
				if($web_result !== true)
				{	
					$this -> error($web_result);
				}
            }/*elseif($info_list['notice_type'] == 5)
			{
				$page_result = $push_model->content_type_deal(5,$info_list);
				if($page_result !== true)
				{	
					$this -> error($page_result);
				}
            }*/
			//删除为了方便判断类型内容增加的 page1和page4
			//unset($info_list['page_name1']);
			//unset($info_list['page_name4']);
		}else if($info_list['push_type'] == 6){
			//弹窗内容和设置
			$pop_set_result = $push_model-> save_clock_content($_POST,$id,$info_list);

			if($pop_set_result !== true)
			{	
				$this -> error($pop_set_result);
			}
			
			//图片处理
			$pop_pic=$this->upload_clock_images($_FILES,$_POST,$id,$info_list);
			if($pop_pic!==true)
			{
				$this -> error($pop_pic);
			}
			//预约闹钟类型内容处理
			if($info_list['notice_type'] == 1){
				$package_result = $push_model->content_type_deal(1,$info_list);
				if($package_result !== true)
				{	
					$this -> error($package_result);
				}
            }elseif($info_list['notice_type'] == 2){
				$feature_result = $push_model->content_type_deal(2,$info_list);
				if($feature_result !== true)
				{	
					$this -> error($feature_result);
				}
            }elseif($info_list['notice_type'] == 3){
				$active_result = $push_model->content_type_deal(3,$info_list);
				if($active_result !== true)
				{	
					$this -> error($active_result);
				}
            }elseif($info_list['notice_type'] == 4){
				$web_result = $push_model->content_type_deal(4,$info_list);
				if($web_result !== true)
				{	
					$this -> error($web_result);
				}
            }/*elseif($info_list['notice_type'] == 5)//V6.0添加跳转页面
			{
				$page_result = $push_model->content_type_deal(5,$info_list);
				if($page_result !== true)
				{	
					$this -> error($page_result);
				}
            }*/
			
			//删除为了方便判断类型内容增加的 page1和page4
			//unset($info_list['page_name1']);
			//unset($info_list['page_name4']);
        }
		
		//调用model的函数  公用保存数据函数  弹窗和预下载使用
		$save_result = $push_model-> common_save($_POST,$id,$info_list);
		if($save_result !== true)
		{
			$this -> error($save_result);
		}
		
		//push的时候单独编辑数据 都添加完成才跳转
		if($info_list['push_type'] == 1)
		{
			if(count($del_id))//说明编辑的时候有删除
			{
				foreach($del_id as $val)//一个一个删除
				{
					$affect = $sj_market_push -> query("update __TABLE__ set status = 0 where id = " .$val);
					$this->link_push(array('status' => 0), $val);
					$this->writelog('删除了ID为['.$val.']的推送信息', 'sj_market_push', $val,__ACTION__ ,"","del");
				}
			}
			//修改信息
			if($info)
			{
				foreach($info as $key => $val)
				{
					$push_id = $val['id'];
					unset($val['id']);
					
					$log = $this->logcheck(array('id'=>$push_id),'sj_market_push',$info[$key],$sj_market_push);
					$where_push =array('id' => $push_id);
                    if($val['soft_package'] && empty($_POST['is_subscripe'])){
                        //屏蔽软件上排期时报警需求 新增  yuesai
                        $AdSearch = D("Sj.AdSearch");
                        $shield_error=$AdSearch->check_shield($val['soft_package'],$val['start_tm'],$val['end_tm']);
                        if($shield_error){
                            $this -> error($shield_error);
                        }
                    }
					$val['pid'] = $_POST['pid']?$_POST['pid']:1;
					$list= $sj_market_push->where($where_push)->save($val);
					if($list !== false)
					{
						$getui = D('Getui');
						$getui->push_relate($push_id,$val);
						$this->link_push($val, $push_id);
						$this->writelog("市场推送信息ID为$push_id".$log,'sj_market_push',$push_id,__ACTION__ ,"","edit");
					}else {
						$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_list');
						$this->error("编辑推送失败,发生错误！");
					}
				}
			}
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_push_list/push_type/1/p/{$_POST['p']}");
			$this->success("编辑推送成功！");
		}else {
			$log = $this->logcheck(array('id'=>$id),'sj_market_push',$info_list,$sj_market_push);
            if($info_list['soft_package'] && empty($_POST['is_subscripe'])){
                //屏蔽软件上排期时报警需求 新增  yuesai
                $AdSearch = D("Sj.AdSearch");
                $shield_error=$AdSearch->check_shield($info_list['soft_package'],$info_list['start_tm'],$info_list['end_tm']);
                if($shield_error){
                    $this -> error($shield_error);
                }
            }
			if( $info_list['push_type'] == 5 ) {
				//编辑桌面红包时 处理红包绑定与解绑
				$bind_data = $info_list['bind_data'];
				if( !empty($bind_data) ) {
					if( $bind_data['bind_red_type'] == 1 ) {
						if( $bind_data['red_id'] ) {
								//解除绑定
								$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['red_id'], $bind_data['red_id']);
								if( $bind_info['STATUS'] == 1 ) {
									$desk_status = array(
										'desktop' => 0, //是否是桌面红包 0，不是， 1，定制 2，push
									);
									$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['red_id']))->save($desk_status);
								}else {
									$this->error($bind_info['MSG']);
								}
						}
						//绑定红包
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['new_red_id'], '', '',$bind_data['note']);
						if( $bind_info['STATUS'] == 1 ) {
							$red_conf_find2 = $model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->find();
							if($bind_data['table'] == 'sj_market_push') {
								$desk = 2;
							}elseif($bind_data['table'] == 'sj_custom_push') {
								$desk = 1;
							}else {
								$desk = 0;
							}
							$desk_status = array(
								'desktop' => $desk, //是否是桌面红包 0，不是， 1，定制 2，push
							);
							$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->save($desk_status);
						}else {
							$this->error($bind_info['MSG']);
						}
					}elseif( $bind_data['bind_red_type'] == 2 ) {
						if( $bind_data['red_id'] ) {
							//解除绑定
							$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['red_id'], $bind_data['red_id']);
							if( $bind_info['STATUS'] == 1) {
								$desk_status = array(
									'desktop' => 0, //是否是桌面红包 0，不是， 1，定制 2，push
								);
							$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['red_id']))->save($desk_status);
							}else {
								$this->error($bind_info['MSG']);
							}
						}
						//绑定新的任务红包
						$bind_info = D('Sj.RedActivity')->bind_red_pagckage(1, $bind_data['push_id'], $bind_data['new_red_id'],'',$bind_data['task_id'],$bind_data['note']);
						if( $bind_info['STATUS'] == 1 ) {
						//更改sj_red_packet_conf中红包id为$_post['red_id']的desktop状态
							$red_conf_find2 = $model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->find();
							if($bind_data['table'] == 'sj_market_push') {
								$desk = 2;
							}elseif($bind_data['table'] == 'sj_custom_push') {
								$desk = 1;
							}else {
								$desk = 0;
							}
							$desk_status = array(
								'desktop' => $desk, //是否是桌面红包 0，不是， 1，定制 2，push
							);
							$model->table("sj_red_packet_conf")->where(array('id'=>$bind_data['new_red_id']))->save($desk_status);
						}else {
							$this->error($bind_info['MSG']);
						}
					}
				}
			}
			if(($info_list['push_type'] == 2 || $info_list['push_type'] == 1 || $info_list['push_type'] == 6) && $_POST['pid']){
				$info_list['pid'] = $_POST['pid'];
			}

			if($list= $sj_market_push->where($where)->save($info_list)) 
			{
				// echo $sj_market_push->getLastSql();die;
				$this->link_push($info_list, $where['id']);
				$this->writelog("市场推送信息ID为$id".$log,'sj_market_push',$id,__ACTION__ ,"","edit");
				if($info_list['push_type'] == 2){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_cpm_list/push_type/2/p/{$_POST['p']}");
					$this->success("编辑弹窗广告成功！");
				}else if($info_list['push_type'] == 3){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_pre_dl_list/push_type/3/p/{$_POST['p']}");
					$this->success("编辑被动预下载成功！");
				}else if($info_list['push_type'] == 5){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_desk_red_list/push_type/5/p/{$_POST['p']}");
					$this->success("编辑桌面红包成功！");
				}else if($info_list['push_type'] == 6){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/marketPush/market_pre_clock_list/push_type/6/p/{$_POST['p']}");
					$this->success("编辑预约闹钟成功！");
				}
			}else {
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/marketPush/market_push_list');
				$this->error("编辑推送失败,发生错误！");
			}
		}   
    }
    
    function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr);
        return $arr;
    }

    function check_beid($beid,$feature_id,$start_tm,$end_tm){
        $model = new Model();
        $beid_where['_string'] = "beid = {$beid} and status = 1 and start  <= {$end_tm} and end >= {$start_tm}";
        $beid_result = $model -> table('sj_push_be_detail') -> where($beid_where) -> select();
        $feature_result = $model -> table('sj_feature_soft') -> where(array('feature_id' => $feature_id,'status' => 1)) -> select();
        foreach($feature_result as $key => $val){
            $feature_soft_arr[] = $val['package'];
        }
        foreach($beid_result as $key => $val){
            if(!in_array($val['package'],$feature_soft_arr)){
                $i = 0;
            }else{
                $i = 1;
            }
            $check_arr[] = $i;
        }

        $count = array_sum($check_arr);
        if($count > 0){
            return true;
        }else{
            return false;
        }
    }
	//计算csv中的个数 并返回
	function csv_count()
	{
		if($_FILES['upload_file'])
		{
			$filename=$_FILES['upload_file']['tmp_name'];
			$err = $_FILES["upload_file"]["error"];
			$file_name_csv=$_FILES['upload_file']['name'];
			$tmp_arr = explode(".", $file_name_csv);
			$name_suffix = array_pop($tmp_arr);
		}
		if($_FILES['paichu_upload_file'])
		{
			$filename=$_FILES['paichu_upload_file']['tmp_name'];
			$file_name_csv=$_FILES['paichu_upload_file']['name'];
			$tmp_arr = explode(".", $file_name_csv);
			$name_suffix = array_pop($tmp_arr);
		}
		if(empty($filename))
		{
			$error1=-1;
			echo '{"error1":"'.$error1.'"}';
			return;
		}
		if (strtoupper($name_suffix) != "CSV") 
		{
			$error2=-2;
			echo '{"error2":"'.$error2.'"}';
			return;
		}
		
		$handle=fopen($filename,'r');
		$out = array (); 
		$out_unique = array (); 
		$n = 0; 
		$reapt = 0;
		$i = 0;
		while (!feof($handle)) 
		{
			$mm = fgets($handle);
			if(!$mm) continue;
			if($i == 0) {
				if( trim($mm) != "IMEI"){
					$error2 = -3;
					break;
				}
			}
			if( strrpos($mm, 'E+') ) {
				$reapt ++;
			}
			//$out[$n]=fgets($handle);
			//$out[$n]=str_replace(array("\n","\r"),"",$out[$n]);//去掉换行符
// 			if(!empty($out[$n]))
// 			{
// 				if($n>0 && !isset($out_unique[$out[$n]])){
// 					$out_unique[$out[$n]]=$out[$n];
// 					$uniq_count ++;
// 				}
// 				$n++;
// 			}
			if($i!=0)
			$n++;
			$i++;
		}
		fclose($handle);
		if($error2 == -3) {
			echo '{"error2":"'.$error2.'"}';
			return;
		}
		if( $reapt > 10 ) {
			$error2 = -4;
			echo '{"error2":"'.$error2.'"}';
			return;
		}
// 		if($_FILES['upload_file'])
// 		{
// 			if(trim($out[0])!="device_Id")
// 			{
// 				$error2=-3;
// 				echo '{"error2":"'.$error2.'"}';
// 				return;
// 			}
// 		}
// 		if($_FILES['paichu_upload_file'])
// 		{
// 			if(trim($out[0])!="IMEI")
// 			{
// 				$error2=-3;
// 				echo '{"error2":"'.$error2.'"}';
// 				return;
// 			}
// 		}
		
		//上传csv不去重，去重太慢
		//$un_data = array_unique($out);  
		//$len_result=count($un_data)-2;
		// $len_result=$n-1;
		//$len_result=count($out_unique);
		// save the import file for backups
		$path=date("/Ym/d/",time());
		$save_dir = C("MARKET_PUSH_CSV").$path;
		$this->mkDirs($save_dir);
		$save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
		$save_file_name = $save_dir . $save_name;
		$db_save=$path.$save_name;
		if($_FILES['upload_file'])
		{
			move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
		}
		if($_FILES['paichu_upload_file'])
		{
			move_uploaded_file($_FILES['paichu_upload_file']['tmp_name'], $save_file_name);
		}
		
		echo '{"out_count":"' . $n . '","csv_url":"' . $db_save . '"}';
	}
	//Push添加图片
	function upload_push_images($files,$i,&$info_list)
	{
		if($files['push_single_low']['tmp_name'][$i])
		{
			$info_list['push_single_low'] = $files['push_single_low'];
			$push_single_low = getimagesize($info_list['push_single_low']['tmp_name'][$i]);
			$single_low_width = $push_single_low[0];
			$single_low_height = $push_single_low[1];
			//检查图片尺寸			
			if ($single_low_width != $this->single_low_width || $single_low_height != $this->single_low_height) 
			{
				return "请添加尺寸为{$this->single_low_width}*{$this->single_low_height}的单行低分图片";
			}
			if ($info_list['push_single_low']['type'][$i] != 'image/png' && $info_list['push_single_low']['type'][$i] != 'image/jpg'&&$info_list['push_single_low']['type'][$i] != 'image/jpeg') 
			{
				return "请添加图片格式为：jpg，png的单行低分图片";
			}
		}		
		if($files['push_single_high']['tmp_name'][$i])
		{
			$info_list['push_single_high'] = $files['push_single_high'];
			$push_single_high = getimagesize($info_list['push_single_high']['tmp_name'][$i]);
			$single_high_width = $push_single_high[0];
			$single_high_height = $push_single_high[1];
			//检查图片尺寸			
			if ($single_high_width != $this->single_high_width || $single_high_height != $this->single_high_height) 
			{
				return "请添加尺寸为{$this->single_high_width}*{$this->single_high_height}的单行高分图片";
			}
			if ($info_list['push_single_high']['type'][$i] != 'image/png' && $info_list['push_single_high']['type'][$i] != 'image/jpg'&&$info_list['push_single_high']['type'][$i] != 'image/jpeg') 
			{
				return "请添加图片格式为：jpg，png的单行高分图片";
			}	
		}
		if($files['push_multiple_low']['tmp_name'][$i])
		{
			$info_list['push_multiple_low'] = $files['push_multiple_low'];
			$push_multiple_low = getimagesize($info_list['push_multiple_low']['tmp_name'][$i]);
			$multiple_low_width = $push_multiple_low[0];
			$multiple_low_height = $push_multiple_low[1];
			//检查图片尺寸			
			if ($multiple_low_width != $this->multiple_low_width || $multiple_low_height != $this->multiple_low_height) 
			{
				return "请添加尺寸为{$this->multiple_low_width}*{$this->multiple_low_height}的多行低分图片";
			}
			if ($info_list['push_multiple_low']['type'][$i] != 'image/png' && $info_list['push_multiple_low']['type'][$i] != 'image/jpg'&&$info_list['push_multiple_low']['type'][$i] != 'image/jpeg') 
			{
				return "请添加图片格式为：jpg，png的多行低分图片";
			}	
		}
		if($files['push_multiple_high']['tmp_name'][$i])
		{
			$info_list['push_multiple_high'] = $files['push_multiple_high'];
			$push_multiple_high = getimagesize($info_list['push_multiple_high']['tmp_name'][$i]);
			$multiple_high_width = $push_multiple_high[0];
			$multiple_high_height = $push_multiple_high[1];
			//检查图片尺寸			
			if ($multiple_high_width != $this->multiple_high_width || $multiple_high_height != $this->multiple_high_height) 
			{
				return "请添加尺寸为{$this->multiple_high_width}*{$this->multiple_high_height}的多行高分图片";
			}
			if ($info_list['push_multiple_high']['type'][$i] != 'image/png' && $info_list['push_multiple_high']['type'][$i] != 'image/jpg'&&$info_list['push_multiple_high']['type'][$i] != 'image/jpeg') 
			{
				return "请添加图片格式为：jpg，png的多行高分图片";
			}	
		}
		//V6.4新增加  信息图标图片  非必填
		if($files['push_info_icon']['tmp_name'][$i])
		{
			$info_list['push_info_icon'] = $files['push_info_icon'];
			$push_info_icon = getimagesize($info_list['push_info_icon']['tmp_name'][$i]);
			$push_info_icon_width = $push_info_icon[0];
			$push_info_icon_height = $push_info_icon[1];
			//检查图片尺寸			
			if ($push_info_icon_width != $this->info_icon_width || $push_info_icon_height != $this->info_icon_height) 
			{
				return "请添加尺寸为{$this->info_icon_width}*{$this->info_icon_height}的信息图标图片";
			}
			if ($info_list['push_info_icon']['type'][$i] != 'image/png' && $info_list['push_info_icon']['type'][$i] != 'image/jpg'&&$info_list['push_info_icon']['type'][$i] != 'image/jpeg') 
			{
				return "请添加图片格式为：jpg，png的信息图标图片";
			}	
		}
		include_once SERVER_ROOT. '/tools/functions.php';
		//上传图片
		// 将图片存储起来
		$single_low = preg_match("/\.(jpg|png)$/", $info_list['push_single_low']['name'][$i],$match_single_low);
		$single_high = preg_match("/\.(jpg|png)$/", $info_list['push_single_high']['name'][$i],$match_single_high);
		$multiple_low = preg_match("/\.(jpg|png)$/", $info_list['push_multiple_low']['name'][$i],$match_multiple_low);
		$multiple_high = preg_match("/\.(jpg|png)$/", $info_list['push_multiple_high']['name'][$i],$match_multiple_high);
		
		//信息图标图片
		$info_icon_pic = preg_match("/\.(jpg|png)$/", $info_list['push_info_icon']['name'][$i],$match_info_icon);
		
		$single_low = $match_single_low[0];
		$single_high = $match_single_high[0];
		$multiple_low = $match_multiple_low[0];
		$multiple_high = $match_multiple_high[0];
		$info_icon_pic = $match_info_icon[0];
		 
		$folder = "/img/" . date("Ym/d/");
		$this->mkDirs(UPLOAD_PATH . $folder);
		$single_low_path = $folder . time() . '_single_low_'. rand(1000,9999) . $single_low;
		$single_low_path_40 = $folder . time() . '_single_low_40_' . rand(1000,9999) . $single_low;
		$single_high_path = $folder . time() .'_single_high_'. rand(1000,9999) . $single_high;
		$single_high_path_80 = $folder . time() .'_single_high_80_'. rand(1000,9999) . $single_high;
		$multiple_low_path = $folder . time() .'_multiple_low_'. rand(1000,9999) . $multiple_low;
		$multiple_low_path_40 = $folder . time() .'_multiple_low_40_'. rand(1000,9999) . $multiple_low;
		$multiple_high_path = $folder . time() .'_multiple_high_'. rand(1000,9999) . $multiple_high;
		$multiple_high_path_80 = $folder . time() .'_multiple_high_80_'. rand(1000,9999) . $multiple_high;
		$info_icon_pic_path = $folder . time() .'_info_icon_pic_'. rand(1000,9999) . $info_icon_pic;
	
		$img_single_low_path = UPLOAD_PATH . $single_low_path;
		$img_single_low_path_40 = UPLOAD_PATH . $single_low_path_40;
		$img_single_high_path = UPLOAD_PATH . $single_high_path;
		$img_single_high_path_80 = UPLOAD_PATH . $single_high_path_80;
		$img_multiple_low_path = UPLOAD_PATH . $multiple_low_path;
		$img_multiple_low_path_40 = UPLOAD_PATH . $multiple_low_path_40;
		$img_multiple_high_path = UPLOAD_PATH . $multiple_high_path;
		$img_multiple_high_path_80 = UPLOAD_PATH . $multiple_high_path_80;
		$img_info_icon_pic_path = UPLOAD_PATH . $info_icon_pic_path;
		
		if($info_list['push_single_low']['tmp_name'][$i])
		{
			$ret = move_uploaded_file($info_list['push_single_low']['tmp_name'][$i], $img_single_low_path);
			$info_list['push_single_low'] = $single_low_path;
			$single_low_40=image_strip_size($img_single_low_path,$img_single_low_path_40,40*1024);
			if($single_low_40)
			{
				$info_list['push_single_low_40'] = $single_low_path_40;
			}	
		}
		if($info_list['push_single_high']['tmp_name'][$i])
		{
			$ret = move_uploaded_file($info_list['push_single_high']['tmp_name'][$i], $img_single_high_path);
			$info_list['push_single_high'] = $single_high_path;
			$single_high_80=image_strip_size($img_single_high_path,$img_single_high_path_80,80*1024);
			if($single_high_80)
			{
				$info_list['push_single_high_80'] = $single_high_path_80;
			}	
		}
		if($info_list['push_multiple_low']['tmp_name'][$i])
		{
			$ret = move_uploaded_file($info_list['push_multiple_low']['tmp_name'][$i], $img_multiple_low_path);
			$info_list['push_multiple_low'] = $multiple_low_path;
			$multiple_low_40=image_strip_size($img_multiple_low_path,$img_multiple_low_path_40,40*1024);
			if($multiple_low_40)
			{
				$info_list['push_multiple_low_40'] = $multiple_low_path_40;
			}	
		}
		if($info_list['push_multiple_high']['tmp_name'][$i])
		{
			$ret = move_uploaded_file($info_list['push_multiple_high']['tmp_name'][$i], $img_multiple_high_path);
			$info_list['push_multiple_high'] = $multiple_high_path;
			$multiple_high_80=image_strip_size($img_multiple_high_path,$img_multiple_high_path_80,80*1024);
			if($multiple_high_80)
			{
				$info_list['push_multiple_high_80'] = $multiple_high_path_80;
			}	
		}
		if($info_list['push_info_icon']['tmp_name'][$i])
		{
			$ret = move_uploaded_file($info_list['push_info_icon']['tmp_name'][$i], $img_info_icon_pic_path);
			$info_list['push_info_icon'] = $info_icon_pic_path;
		}
		return true;
	}
	//弹窗图片处理
	//V6.0弹窗广告 大图、小图高低分、按钮弹窗高低分 
	function upload_pop_images($files,$data,$id,&$info_list)
	{
		$model = new model();
		$pop_type=$data['pop_type'];
		$info_list['popup_type']=$pop_type;
		$btn_des=$data['btn_des'];
		$btn_name=$data['btn_name'];
		if($id)
		{
			if($pop_type==3)
			{
				if($btn_des)
				{
					if(mb_strlen($btn_des,'utf-8')>$this->btn_pic_des_limit)
					{
						$this->error("描述限制在{$this->btn_pic_des_limit}字以内");
					}
				}
				$info_list['btn_des'] = $btn_des;
				if($btn_name)
				{
					if(mb_strlen($btn_name,'utf-8')>$this->btn_pic_name_limit)
					{
						$this->error("按钮名称在{$this->btn_pic_name_limit}字以内");
					}
					else
					{
						$info_list['btn_name'] = $btn_name;
					}
				}
				else
				{
					$this->error("按钮名称不能为空");
				}
			}
			if(($_FILES['cpm_pic_big']['size']||$_FILES['cpm_pic_big_high']['size']||$_FILES['cpm_pic_big_low']['size'])||($_FILES['cpm_pic_small_high']['size']||$_FILES['cpm_pic_small_low']['size'])||($_FILES['cpm_pic_btn_high']['size']||$_FILES['cpm_pic_btn_low']['size']))
			{
				if($pop_type==1)
				{
					$info_list['cpm_pic'] = $_FILES['cpm_pic_big'];
					$info_list['high_image_url'] = $_FILES['cpm_pic_big_high'];
					$info_list['low_image_url'] = $_FILES['cpm_pic_big_low'];
					
					//检查图片尺寸
					$img_info_arr = getimagesize($info_list['cpm_pic']['tmp_name']);
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr&&!$img_info_arr_high&&!$img_info_arr_low) 
					{
						$this->error("上传图片出错");
					}
					if($_FILES['cpm_pic_big']['size'])
					{
						$width = $img_info_arr[0];
						$height = $img_info_arr[1];
						if ($width != $this->big_pic_width || $height != $this->big_pic_height) 
						{
							$this->error("请添加尺寸为{$this->big_pic_width}*{$this->big_pic_height}的弹窗图片");
						}
						//检查图片格式
						if ($info_list['cpm_pic']['type'] != 'image/png' && $info_list['cpm_pic']['type'] != 'image/jpg'&&$info_list['cpm_pic']['type'] != 'image/jpeg') 
						{
							$this->error("请添加图片格式为：jpg，png的弹窗图片");
						}
					}						
					if($_FILES['cpm_pic_big_high']['size'])
					{
						$width_high = $img_info_arr_high[0];
						$height_high = $img_info_arr_high[1];
						if ($width_high != $this->big_pic_width_high || $height_high != $this->big_pic_height_high) 
						{
							$this->error("请添加尺寸为{$this->big_pic_width_high}*{$this->big_pic_height_high}的高分图片");
						}
					}
					if($_FILES['cpm_pic_big_low']['size'])
					{
						$width_low = $img_info_arr_low[0];
						$height_low = $img_info_arr_low[1];
						
						if ($width_low != $this->big_pic_width_low || $height_low != $this->big_pic_height_low) 
						{
							$this->error("请添加尺寸为{$this->big_pic_width_low}*{$this->big_pic_height_low}的低分图片");
						}
					}							
				}
				else if($pop_type==2)
				{
					$info_list['cpm_pic'] = "";
					$info_list['high_image_url'] = $_FILES['cpm_pic_small_high'];
					$info_list['low_image_url'] = $_FILES['cpm_pic_small_low'];
					//检查图片尺寸
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr_high&&!$img_info_arr_low) 
					{
						$this->error("上传图片出错");
					}
					if($_FILES['cpm_pic_small_high']['size'])
					{
						$width_high = $img_info_arr_high[0];
						$height_high = $img_info_arr_high[1];
						if ($width_high != $this->small_pic_width_high || $height_high != $this->small_pic_height_high) 
						{
							$this->error("请添加尺寸为{$this->small_pic_width_high}*{$this->small_pic_height_high}的高分图片");
						}
					}
					if($_FILES['cpm_pic_small_low']['size'])
					{
						$width_low = $img_info_arr_low[0];
						$height_low = $img_info_arr_low[1];
						if ($width_low != $this->small_pic_width_low || $height_low != $this->small_pic_height_low) 
						{
							$this->error("请添加尺寸为{$this->small_pic_width_low}*{$this->small_pic_height_low}的低分图片");
						}
					}			
				}
				else if($pop_type==3)
				{
					$info_list['cpm_pic'] = "";
					$info_list['high_image_url'] = $_FILES['cpm_pic_btn_high'];
					$info_list['low_image_url'] = $_FILES['cpm_pic_btn_low'];
					if($btn_des)
					{
						if(mb_strlen($btn_des,'utf-8')>20)
						{
							$this->error("描述限制在20字以内");
						}
					}
					$info_list['btn_des'] = $btn_des;
					if($btn_name)
					{
						if(mb_strlen($btn_name,'utf-8')>6)
						{
							$this->error("按钮名称在6字以内");
						}
						else
						{
							$info_list['btn_name'] = $btn_name;
						}
					}
					else
					{
						$this->error("按钮名称不能为空");
					}
					
					//检查图片尺寸
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr_high&&!$img_info_arr_low) 
					{
						$this->error("上传图片出错");
					}
					if($_FILES['cpm_pic_btn_high']['size'])
					{
						$width_high= $img_info_arr_high[0];
						$height_high = $img_info_arr_high[1];
						if ($width_high != $this->btn_pic_width_high || $height_high != $this->btn_pic_height_high) 
						{
							$this->error("请添加尺寸为{$this->btn_pic_width_high}*{$this->btn_pic_height_high}的高分图片");
						}
					}
					if($_FILES['cpm_pic_btn_low']['size'])
					{
						$width_low= $img_info_arr_low[0];
						$height_low = $img_info_arr_low[1];
						if ($width_low != $this->btn_pic_width_low || $height_low != $this->btn_pic_height_low) 
						{
							$this->error("请添加尺寸为{$this->btn_pic_width_low}*{$this->btn_pic_height_low}的低分图片");
						}
					}	
				}else if($pop_type==4) {
					$info_list['cpm_pic'] = $_FILES['cpm_pic_big'];
					$info_list['high_image_url'] = $_FILES['cpm_pic_big_high'];
					$info_list['low_image_url'] = $_FILES['cpm_pic_big_low'];
					
					//检查图片尺寸
					$img_info_arr = getimagesize($info_list['cpm_pic']['tmp_name']);
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr&&!$img_info_arr_high&&!$img_info_arr_low) 
					{
						$this->error("上传图片出错");
					}
					if($_FILES['cpm_pic_big']['size'])
					{
						$width = $img_info_arr[0];
						$height = $img_info_arr[1];
						if ($width != $this->big_pic_width || $height != $this->big_pic_height) 
						{
							$this->error("请添加尺寸为{$this->big_pic_width}*{$this->big_pic_height}的弹窗图片");
						}
						//检查图片格式
						if ($info_list['cpm_pic']['type'] != 'image/png' && $info_list['cpm_pic']['type'] != 'image/jpg'&&$info_list['cpm_pic']['type'] != 'image/jpeg') 
						{
							$this->error("请添加图片格式为：jpg，png的弹窗图片");
						}
					}						
					if($_FILES['cpm_pic_big_high']['size'])
					{
						$width_high = $img_info_arr_high[0];
						$height_high = $img_info_arr_high[1];
						if ($width_high != $this->big_pic_width_high || $height_high != $this->big_pic_height_high) 
						{
							$this->error("请添加尺寸为{$this->big_pic_width_high}*{$this->big_pic_height_high}的高分图片");
						}
					}
					if($_FILES['cpm_pic_big_low']['size'])
					{
						$width_low = $img_info_arr_low[0];
						$height_low = $img_info_arr_low[1];
						
						if ($width_low != $this->big_pic_width_low || $height_low != $this->big_pic_height_low) 
						{
							$this->error("请添加尺寸为{$this->big_pic_width_low}*{$this->big_pic_height_low}的低分图片");
						}
					}		
				}
				if($info_list['high_image_url']['size'])
				{
					if ($info_list['high_image_url']['type'] != 'image/png' && $info_list['high_image_url']['type'] != 'image/jpg'&&$info_list['high_image_url']['type'] != 'image/jpeg') 
					{
						$this->error("请添加图片格式为：jpg，png的高分图片");
					}
				}
				if($info_list['low_image_url']['size'])
				{
					if ($info_list['low_image_url']['type'] != 'image/png' && $info_list['low_image_url']['type'] != 'image/jpg'&&$info_list['low_image_url']['type'] != 'image/jpeg') 
					{
						$this->error("请添加图片格式为：jpg，png的低分图片");
					}
				}
			}	
		}
		else
		{
			if(($files['cpm_pic_big']['size']&&$files['cpm_pic_big_high']['size']&&$files['cpm_pic_big_low']['size'])||($files['cpm_pic_small_high']['size']&&$files['cpm_pic_small_low']['size'])||($files['cpm_pic_btn_high']['size']&&$files['cpm_pic_btn_low']['size']))
			{
				if($pop_type==1)
				{
					$info_list['cpm_pic'] = $files['cpm_pic_big'];
					$info_list['high_image_url'] = $files['cpm_pic_big_high'];
					$info_list['low_image_url'] = $files['cpm_pic_big_low'];
					//检查图片尺寸
					$img_info_arr = getimagesize($info_list['cpm_pic']['tmp_name']);
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr||!$img_info_arr_high||!$img_info_arr_low) 
					{
						return "上传图片出错";
					}
										
					$width = $img_info_arr[0];
					$height = $img_info_arr[1];
					$width_high = $img_info_arr_high[0];
					$height_high = $img_info_arr_high[1];
					$width_low = $img_info_arr_low[0];
					$height_low = $img_info_arr_low[1];
					
					if ($width != $this->big_pic_width || $height != $this->big_pic_height) 
					{
						return "请添加尺寸为{$this->big_pic_width}*{$this->big_pic_height}的弹窗图片";
					}
					if ($width_high != $this->big_pic_width_high || $height_high != $this->big_pic_height_high) 
					{
						return "请添加尺寸为{$this->big_pic_width_high}*{$this->big_pic_height_high}的高分图片";
					}
					if ($width_low != $this->big_pic_width_low || $height != $this->big_pic_height_low) 
					{
						return "请添加尺寸为{$this->big_pic_width_low}*{$this->big_pic_height_low}的低分图片";
					}
					//检查图片格式
					if ($info_list['cpm_pic']['type'] != 'image/png' && $info_list['cpm_pic']['type'] != 'image/jpg'&&$info_list['cpm_pic']['type'] != 'image/jpeg') 
					{
						return "请添加图片格式为：jpg，png的弹窗图片";
					}
				}
				else if($pop_type==2)
				{
					$info_list['cpm_pic'] = "";
					$info_list['high_image_url'] = $files['cpm_pic_small_high'];
					$info_list['low_image_url'] = $files['cpm_pic_small_low'];
					//检查图片尺寸
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr_high||!$img_info_arr_low) 
					{
						return "上传图片出错";
					}
					$width_high = $img_info_arr_high[0];
					$height_high = $img_info_arr_high[1];
					$width_low = $img_info_arr_low[0];
					$height_low = $img_info_arr_low[1];
					if ($width_high != $this->small_pic_width_high || $height_high != $this->small_pic_height_high) 
					{
						return "请添加尺寸为{$this->small_pic_width_high}*{$this->small_pic_height_high}的高分图片";
					}
					if ($width_low != $this->small_pic_width_low || $height_low != $this->small_pic_height_low) 
					{
						return "请添加尺寸为{$this->small_pic_width_low}*{$this->small_pic_height_low}的低分图片";
					}
				}
				else if($pop_type==3)
				{
					$info_list['cpm_pic'] = "";
					$info_list['high_image_url'] = $files['cpm_pic_btn_high'];
					$info_list['low_image_url'] = $files['cpm_pic_btn_low'];
					if($btn_des)
					{
						if(mb_strlen($btn_des,'utf-8')>$this->btn_pic_des_limit)
						{
							return "描述限制在{$this->btn_pic_des_limit}字以内";
						}
						else
						{
							$info_list['btn_des'] = $btn_des;
						}
					}
					if($btn_name)
					{
						if(mb_strlen($btn_name,'utf-8')>$this->btn_pic_name_limit)
						{
							return "按钮名称在{$this->btn_pic_name_limit}字以内";
						}
						else
						{
							$info_list['btn_name'] = $btn_name;
						}
					}
					else
					{
						return "按钮名称不能为空";
					}
					
					//检查图片尺寸
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr_high||!$img_info_arr_low) 
					{
						return "上传图片出错";
					}
					$width_high= $img_info_arr_high[0];
					$height_high = $img_info_arr_high[1];
					$width_low= $img_info_arr_low[0];
					$height_low = $img_info_arr_low[1];
					if ($width_high != $this->btn_pic_width_high || $height_high != $this->btn_pic_height_high) 
					{
						return "请添加尺寸为{$this->btn_pic_width_high}*{$this->btn_pic_height_high}的高分图片";
					}
					if ($width_low != $this->btn_pic_width_low || $height_low != $this->btn_pic_height_low) 
					{
						return "请添加尺寸为{$this->btn_pic_width_low}*{$this->btn_pic_height_low}的低分图片";
					}
				}else if($pop_type == 4) {
					$info_list['cpm_pic'] = $files['cpm_pic_big'];
					$info_list['high_image_url'] = $files['cpm_pic_big_high'];
					$info_list['low_image_url'] = $files['cpm_pic_big_low'];
					//检查图片尺寸
					$img_info_arr = getimagesize($info_list['cpm_pic']['tmp_name']);
					$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
					$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
					if (!$img_info_arr||!$img_info_arr_high||!$img_info_arr_low) 
					{
						return "上传图片出错";
					}
										
					$width = $img_info_arr[0];
					$height = $img_info_arr[1];
					$width_high = $img_info_arr_high[0];
					$height_high = $img_info_arr_high[1];
					$width_low = $img_info_arr_low[0];
					$height_low = $img_info_arr_low[1];
					
					if ($width != $this->big_pic_width || $height != $this->big_pic_height) 
					{
						return "请添加尺寸为{$this->big_pic_width}*{$this->big_pic_height}的弹窗图片";
					}
					if ($width_high != $this->big_pic_width_high || $height_high != $this->big_pic_height_high) 
					{
						return "请添加尺寸为{$this->big_pic_width_high}*{$this->big_pic_height_high}的高分图片";
					}
					if ($width_low != $this->big_pic_width_low || $height != $this->big_pic_height_low) 
					{
						return "请添加尺寸为{$this->big_pic_width_low}*{$this->big_pic_height_low}的低分图片";
					}
					//检查图片格式
					if ($info_list['cpm_pic']['type'] != 'image/png' && $info_list['cpm_pic']['type'] != 'image/jpg'&&$info_list['cpm_pic']['type'] != 'image/jpeg') 
					{
						return "请添加图片格式为：jpg，png的弹窗图片";
					}
				}
				if ($info_list['high_image_url']['type'] != 'image/png' && $info_list['high_image_url']['type'] != 'image/jpg'&&$info_list['high_image_url']['type'] != 'image/jpeg') 
				{
					return "请添加图片格式为：jpg，png的高分图片";
				}
				if ($info_list['low_image_url']['type'] != 'image/png' && $info_list['low_image_url']['type'] != 'image/jpg'&&$info_list['low_image_url']['type'] != 'image/jpeg') 
				{
					return "请添加图片格式为：jpg，png的低分图片";
				}
			}
			else
			{
				return "请上传全部图片";
			}
		}
		
		include_once SERVER_ROOT. '/tools/functions.php';
		//上传图片
		// 将图片存储起来
		$suffix = preg_match("/\.(jpg|png)$/", $info_list['cpm_pic']['name'],$matches);
		$suffix_high = preg_match("/\.(jpg|png)$/", $info_list['high_image_url']['name'],$matches_high);
		$suffix_low = preg_match("/\.(jpg|png)$/", $info_list['low_image_url']['name'],$matches_low);
		if($matches)
		{
			$suffix = $matches[0];
		}
		if ($matches_high) {
			$suffix_high = $matches_high[0];
		} 
		if ($matches_high) {
			$suffix_low = $matches_low[0];
		} 
		$folder = "/img/" . date("Ym/d/");
		$this->mkDirs(UPLOAD_PATH . $folder);
		$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
		$relative_path_high = $folder . time() .'high'. '_' . rand(1000,9999) . $suffix_high;
		$relative_path_high_80 = $folder . time() .'high_80'. '_' . rand(1000,9999) . $suffix_high;
		$relative_path_low = $folder . time() .'low'. '_' . rand(1000,9999) . $suffix_low;
		$relative_path_low_40 = $folder . time() .'low_40'. '_' . rand(1000,9999) . $suffix_low;
		$img_path = UPLOAD_PATH . $relative_path;
		$img_path_high = UPLOAD_PATH . $relative_path_high;
		$img_path_high_80 = UPLOAD_PATH . $relative_path_high_80;
		$img_path_low = UPLOAD_PATH . $relative_path_low;
		$img_path_low_40 = UPLOAD_PATH . $relative_path_low_40;
		if($id)
		{
			$have_been = $model -> table('sj_market_push') -> where(array('id' => $id)) -> select();
		}
		if($info_list['cpm_pic']['tmp_name'])
		{
			$ret = move_uploaded_file($info_list['cpm_pic']['tmp_name'], $img_path);
			$info_list['cpm_pic'] = $relative_path;
		}
		else
		{
			if($id)
			{
				$info_list['cpm_pic'] = $have_been[0]['cpm_pic'];
			}
		}
		if($info_list['high_image_url']['tmp_name'])
		{
			$ret = move_uploaded_file($info_list['high_image_url']['tmp_name'], $img_path_high);
			$info_list['high_image_url'] = $relative_path_high;
			$high_80=image_strip_size($img_path_high,$img_path_high_80,80*1024);
			if($high_80)
			{
				$info_list['high_image_url_80'] = $relative_path_high_80;
			}	
		}
		else
		{
			if($id)
			{
				$info_list['high_image_url'] = $have_been[0]['high_image_url'];
			}
		}
		if($info_list['low_image_url']['tmp_name'])
		{
			$ret = move_uploaded_file($info_list['low_image_url']['tmp_name'], $img_path_low);
			$info_list['low_image_url'] = $relative_path_low;
			$low_40=image_strip_size($img_path_low,$img_path_low_40,40*1024);
			if($low_40)
			{
				$info_list['low_image_url_40'] = $relative_path_low_40;
			}
		} 
		else
		{
			if($id)
			{
				$info_list['low_image_url'] = $have_been[0]['low_image_url'];
			}
		}
		return true;
	}

	//预约闹钟图片处理
	private function upload_clock_images($files,$data,$id,&$info_list)
	{
		$clock_des = trim($data['clock_des']);
		if ($clock_des == '') {
			return '请输入预约闹钟提示语';
		}
		$info_list['btn_des'] = $clock_des;
		$clock_package = trim($data['clock_package']);
		if ($clock_package == '') {
			return '请输入预约游戏包名';
		}
		/*$softinfo = M('')->table('sj_soft')->where(array('status'=>1,'hide'=>1,'package'=>$clock_package))->find();
		if(!$softinfo){
			return '预约游戏包名无效';
		}*/
/*
SELECT id,activity_page_id FROM  sj_activity WHERE STATUS=1 AND activity_page_id=(
	SELECT page_id FROM `sj_actives_soft` WHERE page_id IN 
		(SELECT activity_page_id FROM `sj_activity` WHERE activity_type_bank=6 AND activity_page_id!=0 AND STATUS=1) 
	AND STATUS=1 AND package!='cn.goapk.market' AND package='mgyly6.anzhi' LIMIT 1
)
*/
		//所有有效预约活动的页面id
		$yx_product = M('')->table('yx_product')->where(array('package'=>$clock_package,'del'=>0))->find();
		if(!$yx_product){
			return '预约包名无效';
		}
		//关联预约游戏包名的页面id
		$sj_game_subscriber = M('')->table('sj_game_subscriber')->where(array('status'=>1,'yx_id'=>$yx_product['soft_id']))->find();
		if(!$sj_game_subscriber){
			return '此预约游戏包名没有关联到任何预约活动';
		}
		//符合条件的页面id所对应的活动id
		$info_list['cpm_pic'] = $sj_game_subscriber['activity_id'];
		$info_list['info_title'] = $clock_package;
		$info_list['btn_name'] = $softinfo['softname'];
		if (!$files['clock_img']['tmp_name'] && !$id) {
			return '请上传预约闹钟背景图';
		}
		if($files['clock_img']['tmp_name']){
			$clock_img_ets = pathinfo($files['clock_img']['name'], PATHINFO_EXTENSION);
			if(!in_array($clock_img_ets, array('png','jpg','jpeg'))){
				return '预约闹钟背景图格式不对';
			}
			$clock_img_info = getimagesize($files['clock_img']['tmp_name']);
			if(!$clock_img_info){
				return '上传预约闹钟背景图出错';
			}
			if($clock_img_info[0]!=444 || $clock_img_info[1]!=150){
				return '预约闹钟背景图尺寸为444*150';
			}
		}
		if (!$files['clock_logo']['tmp_name'] && !$id) {
			return '请上传预约闹钟logo图';
		}
		if($files['clock_logo']['tmp_name']){
			$clock_logo_ets = pathinfo($files['clock_logo']['name'], PATHINFO_EXTENSION);
			if(!in_array($clock_logo_ets, array('png','jpg','jpeg'))){
				return '预约闹钟logo图格式不对';
			}
			$clock_logo_info = getimagesize($files['clock_logo']['tmp_name']);
			if(!$clock_logo_info){
				return '上传预约闹钟logo图出错';
			}
			if($clock_logo_info[0]!=74 || $clock_logo_info[1]!=74){
				return '预约闹钟logo图尺寸为74*74';
			}
		}
		$config = array();
		foreach($_FILES as $k => $v){
			if($v['tmp_name']){
				$config['multi_config'][$k] = array(
					'savepath' => UPLOAD_PATH. '/img/'. date("Ym/d/"),
					'saveRule' => 'getmsec',
					'img_p_size' => 1024 * 200,
				);			
			}			
		}
		if(!empty($config['multi_config'])){
			$return = $this->_uploadapk(0, $config);
			foreach($return['image'] as $val) {
				if ($val['post_name']=='clock_img') {
					$info_list['high_image_url'] = $val['url'];
				}
				if ($val['post_name']=='clock_logo') {
					$info_list['low_image_url'] = $val['url'];
				}
			}
		}
		return true;
	}

	//桌面红包图片处理
	//V6.5弹窗、高低分 
	function upload_desk_red_images($files,$data,$id,&$info_list)
	{
		$model = new model();
		if($files['desk_red_pop']['size']||$files['desk_red_high']['size']||$files['desk_red_low']['size'])
		{
			$info_list['cpm_pic'] = $files['desk_red_pop'];
			$info_list['high_image_url'] = $files['desk_red_high'];
			$info_list['low_image_url'] = $files['desk_red_low'];
			
			//检查图片尺寸
			$img_info_arr = getimagesize($info_list['cpm_pic']['tmp_name']);
			$img_info_arr_high = getimagesize($info_list['high_image_url']['tmp_name']);
			$img_info_arr_low = getimagesize($info_list['low_image_url']['tmp_name']);
			if (!$img_info_arr&&!$img_info_arr_high&&!$img_info_arr_low) 
			{
				$this->error("上传图片出错");
			}
			if($_FILES['desk_red_pop']['size'])
			{
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				if ($width != $this->desk_red_pop_width || $height != $this->desk_red_pop_height) 
				{
					$this->error("请添加尺寸为{$this->desk_red_pop_width}*{$this->desk_red_pop_height}的弹窗图片");
				}
				//检查图片格式
				if ($info_list['cpm_pic']['type'] != 'image/png' && $info_list['cpm_pic']['type'] != 'image/jpg'&&$info_list['cpm_pic']['type'] != 'image/jpeg') 
				{
					$this->error("请添加图片格式为：jpg，png的弹窗图片");
				}
			}						
			if($_FILES['desk_red_high']['size'])
			{
				$width_high = $img_info_arr_high[0];
				$height_high = $img_info_arr_high[1];
				if ($width_high != $this->desk_red_high_width || $height_high != $this->desk_red_high_height) 
				{
					$this->error("请添加尺寸为{$this->desk_red_high_width}*{$this->desk_red_high_height}的高分图片");
				}
			}
			if($_FILES['desk_red_low']['size'])
			{
				$width_low = $img_info_arr_low[0];
				$height_low = $img_info_arr_low[1];
				
				if ($width_low != $this->desk_red_low_width || $height_low != $this->desk_red_low_height) 
				{
					$this->error("请添加尺寸为{$this->desk_red_low_width}*{$this->desk_red_low_height}的低分图片");
				}
			}							
		
			if($info_list['high_image_url']['size'])
			{
				if ($info_list['high_image_url']['type'] != 'image/png' && $info_list['high_image_url']['type'] != 'image/jpg'&&$info_list['high_image_url']['type'] != 'image/jpeg') 
				{
					$this->error("请添加图片格式为：jpg，png的高分图片");
				}
			}
			if($info_list['low_image_url']['size'])
			{
				if ($info_list['low_image_url']['type'] != 'image/png' && $info_list['low_image_url']['type'] != 'image/jpg'&&$info_list['low_image_url']['type'] != 'image/jpeg') 
				{
					$this->error("请添加图片格式为：jpg，png的低分图片");
				}
			}
		}
		else
		{
			if(!$id)
			{
				return "请上传全部图片";
			}
		}
		
		include_once SERVER_ROOT. '/tools/functions.php';
		//上传图片
		// 将图片存储起来
		$suffix = preg_match("/\.(jpg|png)$/", $info_list['cpm_pic']['name'],$matches);
		$suffix_high = preg_match("/\.(jpg|png)$/", $info_list['high_image_url']['name'],$matches_high);
		$suffix_low = preg_match("/\.(jpg|png)$/", $info_list['low_image_url']['name'],$matches_low);
		if($matches)
		{
			$suffix = $matches[0];
		}
		if ($matches_high) {
			$suffix_high = $matches_high[0];
		} 
		if ($matches_high) {
			$suffix_low = $matches_low[0];
		} 
		$folder = "/img/" . date("Ym/d/");
		$this->mkDirs(UPLOAD_PATH . $folder);
		$relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
		$relative_path_high = $folder . time() .'high'. '_' . rand(1000,9999) . $suffix_high;
		$relative_path_high_80 = $folder . time() .'high_80'. '_' . rand(1000,9999) . $suffix_high;
		$relative_path_low = $folder . time() .'low'. '_' . rand(1000,9999) . $suffix_low;
		$relative_path_low_40 = $folder . time() .'low_40'. '_' . rand(1000,9999) . $suffix_low;
		$img_path = UPLOAD_PATH . $relative_path;
		$img_path_high = UPLOAD_PATH . $relative_path_high;
		$img_path_high_80 = UPLOAD_PATH . $relative_path_high_80;
		$img_path_low = UPLOAD_PATH . $relative_path_low;
		$img_path_low_40 = UPLOAD_PATH . $relative_path_low_40;
		if($id)
		{
			$have_been = $model -> table('sj_market_push') -> where(array('id' => $id)) -> select();
		}
		if($info_list['cpm_pic']['tmp_name'])
		{
			$ret = move_uploaded_file($info_list['cpm_pic']['tmp_name'], $img_path);
			$info_list['cpm_pic'] = $relative_path;
		}
		else
		{
			if($id)
			{
				$info_list['cpm_pic'] = $have_been[0]['cpm_pic'];
			}
		}
		
		if($info_list['high_image_url']['tmp_name'])
		{
			$ret = move_uploaded_file($info_list['high_image_url']['tmp_name'], $img_path_high);
			$info_list['high_image_url'] = $relative_path_high;
			$high_80=image_strip_size($img_path_high,$img_path_high_80,80*1024);
			if($high_80)
			{
				$info_list['high_image_url_80'] = $relative_path_high_80;
			}	
		}
		else
		{
			if($id)
			{
				$info_list['high_image_url'] = $have_been[0]['high_image_url'];
			}
		}
		if($info_list['low_image_url']['tmp_name'])
		{
			$ret = move_uploaded_file($info_list['low_image_url']['tmp_name'], $img_path_low);
			$info_list['low_image_url'] = $relative_path_low;
			$low_40=image_strip_size($img_path_low,$img_path_low_40,40*1024);
			if($low_40)
			{
				$info_list['low_image_url_40'] = $relative_path_low_40;
			}
		} 
		else
		{
			if($id)
			{
				$info_list['low_image_url'] = $have_been[0]['low_image_url'];
			}
		}
		return true;
	}
}
?>
