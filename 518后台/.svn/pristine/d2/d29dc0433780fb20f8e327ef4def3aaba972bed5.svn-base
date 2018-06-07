<?php
/**
 * 安智网产品管理平台 信息管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.4.25
 * ----------------------------------------------------------------------------
*/
class MessageAction extends CommonAction {
		//error_reporting(E_ALL);
		//ini_set('display_errors', "on");
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
    public function soft_claim() {
		$_sql = '';
		if(!empty($_GET['package'])) {
			$_sql.=" and A.package like '%{$_GET['package']}%'";
		}
		if(!empty($_GET['softname'])) {
			$_sql.=" and A.softname like '%{$_GET['softname']}%'";
		}
		if(!empty($_GET['dev_name'])) {
			$_sql.=" and B.dev_name like '%{$_GET['dev_name']}%'";
		}

        $this->soft_claim_db=M('soft_claim');
        import("@.ORG.Page");
        $this->map['status']=0;
        $count= $this->soft_claim_db->table('sj_soft_claim A,pu_developer B')->where("A.status=0 and A.dev_id=B.dev_id{$_sql}")->count('A.id');
        $Page=new Page($count,15);
        $this->soft_claim_list=$this->soft_claim_db->table('sj_soft_claim A,pu_developer B')->where("A.status=0 and A.dev_id=B.dev_id{$_sql}")->field('A.id,A.package,A.softname,A.dev_id,A.content,A.note,A.status,A.upload_time,B.dev_name')->order('A.upload_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();


        //dump($this->soft_lack_list);
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );

		//过滤
		$this->gpcFilter($this->soft_claim_list);

        $this->assign('claimlist',$this->soft_claim_list);
        //dump($this->soft_claim_list);
        $this->display();

    }
    //信息管理__缺乏软件列表
    public function soft_lacklist() {
    	$type = isset($_GET['type']) ? $_GET['type'] : ''; //默认是缺乏软件
    	$status = isset($_GET['status']) ? $_GET['status'] : 1;
    	$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

    	$order = isset($_GET['order']) ? $_GET['order'] : 'd_d';

    	$kmap = array(
    		't' => 'last_refresh',
    		'd' => 'numbers',
    	);
    	list($k,$s) = explode('_', $order);

        $this->soft_lack_db=M('soft_lack');
        import("@.ORG.Page");
        $where = "status = {$status}";

    	if (!empty($keyword)) {
			$db = Db::getInstance();
			$keyword = $db->escape_string(trim($keyword));
    		$where .= " AND (package like '%{$keyword}%' OR version_name like '%{$keyword}%' OR user_version_name like '%{$keyword}%' OR softname like '%{$keyword}%')";
    	}
		if (!empty($type)) {
    		$where .= " AND type= {$type}";
    	}
        $count= $this->soft_lack_db->where($where)->count();
    	if (isset($kmap[$k])){
    		$sort = $s=='d' ? 'DESC' : 'ASC';
    		$this->soft_lack_db->order("{$kmap[$k]} {$sort}");
    		$this->assign ("order", $order );
    	}
        $Page=new Page($count,15);
        $this->soft_lack_list=$this->soft_lack_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

		//过滤
		$this->gpcFilter($this->soft_lack_list);

        //dump($this->soft_lack_list);
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("type", $type );
        $this->assign ("status", $status );
        $this->assign ("keyword", $keyword );
        $this->assign('lacklist',$this->soft_lack_list);
        //$html = $this->fetch();
        $this->display();
    	header("Cache-control: no-store");
    	header("pragma:no-cache");
		//exit($html);
    }


	//信息管理__举报信息列表
    public function feedback_list() {
        $type='';
        $map='';
        $actioname='';
        $tmpname='';

        $type=$_GET['type'];
        if(empty($type)) {
            $type='self';
        }
		$channel_db = M('channel');
        switch($type) {
        case 'self':
            $actionname='反馈信息列表';
            $tmpname='feedback_list';
            $otherAct="已删除的举报信息";
            $map='status=1 and softid=0 and feedbacktype=9';
            $otherurl = "__URL__/feedback_list/type/unshow";
            break;
        case 'soft':
            $actionname='软件举报列表';
            $tmpname='feedback_soft_list';
            $otherAct="已删除的软件举报";
            $map='status=1 and softid!=0';
            $post = 'soft';
            $otherurl = "__URL__/feedback_list/type/unshow_soft";
            break;
        case 'unshow':
            $actionname="已删除的举报信息";
            $tmpname = 'feedback_list';
            $otherAct= '举报信息列表';
            $map='status=0 and softid=0 and feedbacktype=9';
            $otherurl = "__URL__/feedback_list";
            break;
        case 'unshow_soft':
            $actionname="已删除的软件举报";
            $tmpname = 'feedback_soft_list';
            $otherAct= '软件反馈列表';
            $map='status=0 and softid!=0';
            $post = 'unshow_soft';
            $otherurl = "__URL__/feedback_list/type/soft";
            break;
         default :
            $actionname='反馈信息列表';
            $tmpname='feedback_list';
            $otherAct="已删除的反馈信息";
            $map='status=1 and softid=0 and feedbacktype=9';
            $otherurl = "__URL__/feedback_list/type/unshow";
            break;
        }

        if(empty($_REQUEST['p'])) {
            $this->map = $map;
            $this->order = 'feedbackid desc';
            if(!empty($_POST['softid'])) {
                $this->map .= ' and softid = "'.preg_replace('/[\s]+/','',$_POST['softid']).'"';
                $this->assign('softid',$_POST['softid']);
            }
            if(!empty($_GET['softid'])) {
                $this->map .= ' and softid = "'.preg_replace('/[\s]+/','',$_GET['softid']).'"';
                $this->assign('softid',$_GET['softid']);
            }

            if (empty($_REQUEST['fromdate'])) {
                $_REQUEST['fromdate'] = date('Y-m-d', time() - 86400 *7);
            }

            if (empty($_REQUEST['todate'])) {
                $_REQUEST['todate'] = date('Y-m-d', time());
            }

            if(!empty($_REQUEST['fromdate'])) {
				$_SESSION['fromdate'] = $_REQUEST['fromdate'];
				$this->assign("fromdate",$_REQUEST['fromdate']);
                $this->map .= ' and submit_tm > '.strtotime($_SESSION['fromdate']).'';
            }else{
				$_SESSION['fromdate']='';
			}
			//todate
            if(!empty($_REQUEST['todate'])) {
				$_SESSION['todate'] = $_REQUEST['todate'];
				$this->assign("todate",$_REQUEST['todate']);
                $this->map .= ' and submit_tm <'.strtotime($_SESSION['todate'].' 23:59:59').'';
            }else{
				$_SESSION['todate']='';
			}
			//content
            if(!empty($_REQUEST['content'])) {
                $this->map.=' and content like "%'.preg_replace('/[\s]+/','',$_REQUEST['content']).'%"';
                $this->assign('content', $_REQUEST['content']);
            }
			//custom
			if(!empty($_REQUEST['custom'])){
				$this->assign("custom",$_REQUEST['custom']);
				$this->map .=' and custom like "%'.$_REQUEST['custom'].'%"';
			}
			//imei
			if(!empty($_REQUEST['imei'])){
				$this->map .= " and imei = '".$_REQUEST['imei']."'";	
				$this->assign("imei",$_REQUEST['imei']);
			}
			//ipmsg
			if(!empty($_REQUEST['ipmsg'])){
				$this->map .= " and ipmsg = '".$_REQUEST['ipmsg']."'";	
				$this->assign("ipmsg",$_REQUEST['ipmsg']);
			}
			
			$device = D("Sj.Device");
			//dname
			if(!empty($_REQUEST['dname'])){
				$did = $device -> where("dname = '".$_REQUEST['dname']." ' " ) ->getField('did');
				$this->map .= " and did = '".$did."'";	
				$this->assign("dname",$_REQUEST['dname']);
			}
			//chname
			if(!empty($_REQUEST['chname'])){
				$channel_array = $channel_db -> where("chname like '%".$_REQUEST['chname']."%'") -> select();
				$cid_array = array();
				foreach($channel_array as $info){
					$cid_array[] = $info['cid'];
				}
				$this -> map .=" and cid in (".implode(',',$cid_array).")";
				$this -> assign('chname',$_REQUEST['chname']);
			}
			//version_code
			if(!empty($_REQUEST['version_code'])){
				$this->map .= " and version_code = '".$_REQUEST['version_code']."'";	
				$this->assign("version_code",$_REQUEST['version_code']);
			}
			if(isset($_REQUEST['pid'])) {
                $this->assign('pid',$_REQUEST['pid']);
                if($_REQUEST['pid'] && is_numeric($_REQUEST['pid'])) {
                   $this->map .= " and pid='{$_REQUEST['pid']}'";
                }
            }
            $_SESSION['admin']['feedback_list']['where']=$this->map;
            $_SESSION['admin']['feedback_list']['order']=$this->order;
			//pid
			#if(isset($_REQUEST['pid'])) {
			#	$this->assign('pid',$_REQUEST['pid']);
			#	if($_REQUEST['pid'] && is_numeric($_REQUEST['pid'])) {
			#		$this->map .= " and pid='{$_REQUEST['pid']}'";
			#	}
			#}
        }else{
            $this->map = $_SESSION['admin']['feedback_list']['where'];
            $this->order = $_SESSION['admin']['feedback_list']['order'];
        }
        //  $type==self && !empty($_REQUEST['version_code']  不明白什么意思？
        $this->feedback_db=M('feedback');
        $soft_db = M('soft');
        $device = D("Sj.Device");
		
        import("@.ORG.Page");
        $count= $this->feedback_db->where($this->map)->count();
		//echo $count;
        $Page=new Page($count,50);
        $this->feedback_list=$this->feedback_db->where($this->map)->field('feedbackid,softid,userid,feedbacktype,content,submit_tm,ipmsg,imei,version_code,cid,did,contact,custom,backtype,pid,jbori,firmware')->order($this->order)->limit($Page->firstRow.','.$Page->listRows)->select();

        if($type=='soft' || $type=='unshow_soft') {
            $this->conf_db = D('Sj.Config');
            $this->config_list = $this->conf_db->where('config_type="feedbacktype" and status=1')->getField('configname,configcontent');
            //dump($this->config_list);
        }else{
			$this->conf_db = D('Sj.Config');
            $this->config_list = $this->conf_db->where('config_type="backtype" and status=1')->getField('configcontent');
			//echo $this->config_list;
			$zh_config_list=explode("|",$this->config_list);
			//print_r($zh_config_list);exit;
		}
        for($i=0;$i<count($this->feedback_list);$i++) {
			if($type=='soft' || $type=='unshow_soft'){
				$this->feedback_list[$i]['feedbacktype']=$this->config_list[$this->feedback_list[$i]['feedbacktype']];
			}else{
				//echo $this->feedback_list[$i]['backtype']."</br>";
				$this->feedback_list[$i]['backtype']=$zh_config_list[($this->feedback_list[$i]['backtype']-1)];
			}
            $softname = $soft_db -> where('softid = '.$this->feedback_list[$i]['softid']) ->getField('softname');
            $this -> feedback_list[$i]['softname'] = $softname;
            //机型
            $dname = $device -> where('did = '.  $this -> feedback_list[$i]['did']) ->getField('dname');
            $this -> feedback_list[$i]['dname'] = $dname;
			$cid = $this->feedback_list[$i]['cid'];
            $chname = $channel_db -> where('cid ='.$cid) -> getField('chname');
			$this -> feedback_list[$i]['chname'] = $chname;
            //是否显示回复操作
	    	if($type=='self') {
	            $feedbackid = $this -> feedback_list[$i]['feedbackid'];
	            $thread_db = D('Sj.Thread');
	            $reaback_flag = $thread_db->checkHaveThread($feedbackid,'feedback'); 
	        }
			//pid 来自
			if($this->feedback_list[$i]['pid'] == 1) {
				$this->feedback_list[$i]['pid_str'] = '手机';
			} else if($this->feedback_list[$i]['pid'] == 5) {
				$this->feedback_list[$i]['pid_str'] = '游戏客户端';
			} else {
				$this->feedback_list[$i]['pid_str'] = '未知';
			}
			//内容过滤
			$this->feedback_list[$i]['content'] = $this->strFilter($this->feedback_list[$i]['content']);
			//举报来源
			$this->feedback_list[$i]['jbori'] = $this->feedback_list[$i]['jbori'] == 1 ? '市场举报' : '一键举报';
        }
        foreach($this->feedback_list as $key => $value)
        {
        	$thread_db = D('Sj.Thread');
        	$fbtype = $thread_db->checkHaveThreadlist($value['feedbackid'],'2');
        	if($fbtype == true)
        	{
        		$this->feedback_list[$key]['flag'] = 1;
        	}else{
        		$this->feedback_list[$key]['flag'] = 0;
        	}
        }

		//过滤
		$this->gpcFilter($post);
		$this->gpcFilter($this->feedback_list);

		//print_r($this->feedback_list);
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("actionname", $actionname );
        $this->assign ('otherAct',$otherAct);
        $this->assign ('otherurl',$otherurl);
        $this->assign ('soft',$post);
        $this->assign ('type',$type);
		$this->assign ("from_value", $_SESSION['fromdate']);
		$this->assign ("to_value", $_SESSION['todate']);
		$this->assign ("zh_content", $_SESSION['content']);
        $this->assign('feedbacklist',$this->feedback_list);
        $this->display($tmpname);
    }
	public function set_message(){
		$this->assign('admin_id',trim($_GET['admin_id']));
		$this->assign('id',trim($_GET['id']));
		$this->display();
	}
	public function set_one_message(){
		$this->assign('id',trim($_GET['id']));
		$this->assign('admin_id',trim($_GET['admin_id']));
		$this->display();
	}
    //信息管理__软件评论列表
    public function message_soft() {

        $soft_device_obj = M('device_user');
        $device_obj        = D('Sj.Device');
        if(empty($_GET['p'])) {
			if($_GET['status']!=null){
				//$this->assign("status",$_GET['status']);
				$_SESSION['zh_status']=$_GET['status'];
				//$this->assign("zh_status",$_SESSION['zh_status']);
				$this->map='status="'.preg_replace('/[\s]+/','',$_SESSION['zh_status']).'"';
			}else{
				$_SESSION['zh_status']=1;
				//$this->assign("zh_status",$_SESSION['zh_status']);
				//$this->assign("status",1);
				$this->map="status={$_SESSION['zh_status']}";
			}
            /*if(!empty($_POST['softid'])) {
            		$this->assign("getsoftid",$_POST['softid']);
                $this->map.=' and softid="'.preg_replace('/[\s]+/','',$_POST['softid']).'"';
            }*/
            if(!empty($_GET['softid'])) {
				$_SESSION['zh_softid']=$_GET['softid'];
            	 //$this->assign("getsoftid",$_SESSION['zh_softid']);
               $this->map.=' and softid="'.preg_replace('/[\s]+/','',$_SESSION['zh_softid']).'"';
            }else{
				$_SESSION['zh_softid']='';
			}
        	if(!empty($_GET['package'])) {
				$_SESSION['zh_package']=$_GET['package'];
        			 //$this->assign("getpackage",$_SESSION['zh_package']);
               $this->map.=' and package="'.preg_replace('/[\s]+/','',$_SESSION['zh_package']).'"';
            }else{
				$_SESSION['zh_package']='';
			}
			if(!empty($_GET['custom'])){
				$_SESSION['custom']=$_GET['custom'];
				$this->map.=' and custom like "%'.$_SESSION['custom'].'%"';
			}
            if(!empty($_GET['content'])) {
				$_SESSION['zh_content']=$_GET['content'];
            	//$this->assign("getcontent",$_SESSION['zh_content']);
                $this->map.=' and content like "%'.preg_replace('/[\s]+/','',$_SESSION['zh_content']).'%"';
            }else{
				$_SESSION['zh_content']='';
			}
			if(!empty($_GET['fromdate'])) {
				$_SESSION['fromdate']=$_GET['fromdate'];
            	//$this->assign("getcontent",$_SESSION['zh_content']);
                $this->map.=' and create_time >= "'.preg_replace('/[\s]+/','',strtotime($_SESSION['fromdate'])).'"';
            }else{
				$_SESSION['fromdate']='';
			}
			if(!empty($_GET['todate'])) {
				$_SESSION['todate']=$_GET['todate'];
            	//$this->assign("getcontent",$_SESSION['zh_content']);
                $this->map.=' and create_time <= "'.preg_replace('/[\s]+/','',strtotime($_SESSION['todate'])).'"';
            }else{
				$_SESSION['todate']='';
			}
			//pid
			if(!empty($_GET['pid']) && is_numeric($_GET['pid'])) {
				$_SESSION['pid'] = $_GET['pid'];
				$this->map .= " and pid='{$_GET['pid']}'";
			} else {
				$_SESSION['pid'] = 0;
			}
			//echo $this->map;exit;
            $_SESSION['admin']['message_soft']['where']=$this->map;

        }else
        {
            $this->map=$_SESSION['admin']['message_soft']['where'];
        }
        $this->soft_comment_db=M('soft_comment');
        $soft_db = M('soft');
        import("@.ORG.Page");
        $count= $this->soft_comment_db->where($this->map)->count();
        $Page=new Page($count,15);
		//echo aaaaaaaaaaaaa.$_SESSION['zh_status'].bbbbbbbbbbbbbb;
		//$zh_status=explode("=",$this->map);
		//$this->assign("status",$zh_status[1]);
        $this->soft_comment_list=$this->soft_comment_db->where($this->map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
	
       /*市场版本、机型、固件版本信息
           imei, did
       */
        foreach($this->soft_comment_list as $idx => $comment){
          $softname = $soft_db -> where('softid = '.$comment['softid'])->getField('softname');
          $where['imei'] = $comment['imei'];
          $user_device = $soft_device_obj -> where($where) -> select();
          $c_did = $comment['did']?$comment['did']:$user_device[0]['did'];
          $dname         = $device_obj -> where('did = '.$c_did )->getField('dname');
          $this -> soft_comment_list[$idx]['softname'] = $softname;
          $this -> soft_comment_list[$idx]['firmware'] =  $user_device[0]['firmware'];
          $this -> soft_comment_list[$idx]['version_code'] =  $comment['version_code']?$comment['version_code']:$user_device[0]['version_code'];
          $this -> soft_comment_list[$idx]['dname']    = $dname;
		  
			if($comment['pid'] == 1) {
				$this->soft_comment_list[$idx]['pid_str'] = '手机';
			} else if($comment['pid'] == 5) {
				$this->soft_comment_list[$idx]['pid_str'] = '游戏客户端';
			} else {
				$this->soft_comment_list[$idx]['pid_str'] = '未知';
			}

			//内容过滤
			$this->soft_comment_list[$idx]['content'] = $this->strFilter($comment['content']);
        }
        

        foreach($this->soft_comment_list as $key => $value)
        {
        	$thread_db = D('Sj.Thread');
        	$fbtype = $thread_db->checkHaveThreadlist($value['id'],'1');
        	if($fbtype == true)
        	{
        		$this->soft_comment_list[$key]['flag'] = 1;
        	}else{
        		$this->soft_comment_list[$key]['flag'] = 0;
        	}
        }
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );

		//过滤
		$this->gpcFilter($this->soft_comment_list);
		$this->gpcFilter($_SESSION['zh_package']);
		$this->gpcFilter($_SESSION['zh_content']);
		$this->gpcFilter($_SESSION['custom']);

        $this->assign('commentlist',$this->soft_comment_list);
		$this->assign("zh_status",$_SESSION['zh_status']);
		$this->assign("getsoftid",$_SESSION['zh_softid']);
		$this->assign("getpackage",$_SESSION['zh_package']);
		$this->assign("getcontent",$_SESSION['zh_content']);
		$this->assign("custom",$_SESSION['custom']);
		$this->assign("from_value",$_SESSION['fromdate']);
		$this->assign("to_value",$_SESSION['todate']);
		$this->assign("pid",$_SESSION['pid']);
        //dump($this->soft_comment_list);
        $this->display();

    }

    
    public function message_unshow() {
        $this->sid=$_GET['id'];

        if(empty($this->sid)) {
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if($this->sid[0]==',')
        {
            $this->sid=substr($this->sid,1);
        }
		if($_GET['zh_one_msg'] == "未通过" || $_GET['zh_one_msg'] == ''){
        	$this->error("请填写具体驳回信息！！！");
        }
        $this->soft_comment_db=M('soft_comment');
        $map='';
        $map['status']='0';
		$map['denymsg']=$_GET['zh_one_msg'];
		$map['update_user_id']=$_SESSION['admin']['admin_id'];
		$map['update_time']=time();
        $this->map='';
        $this->map['id']=array('in',$this->sid);

        //dump($this->soft_list);
        if(false!==$this->soft_comment_db->where($this->map)->save($map)) {

            $this->writelog('信息管理__软件评论列表-删除了ID为['.$this->sid.']的评论','sj_soft_comment',$this->sid,__ACTION__ ,"","del");
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->success("取消显示成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->error("取消显示失败！");
        }
    }
	
	 
	 
	 
	
	//张辉---信息管理__通过评论显示
    public function message_pass() {

        $this->sid=$_GET['id'];

        if(empty($this->sid)) {
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if($this->sid[0]==',')
        {
            $this->sid=substr($this->sid,1);
        }

        $this->soft_comment_db=M('soft_comment');
        $map='';
        $map['status']='1';
		$map['update_user_id']=$_SESSION['admin']['admin_id'];
		$map['update_time']=time();
		
        $this->map='';
        $this->map['id']=array('in',$this->sid);

        //dump($this->soft_list);
        if(false!==$this->soft_comment_db->where($this->map)->save($map)) {
			$sid_array=explode(",",$this->sid);
			foreach($sid_array as $l=>$k){
				 $this->writelog('信息管理__软件评论列表审核通过了ID为['.$k.']的评论','sj_soft_comment',$k,__ACTION__ ,"","edit");
			}
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->success("审核通过成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->error("审核通过失败！");
        }
    }
 //张辉---信息管理__通过评论显示结束
 
 
 
 	
	//张辉---信息管理__未通过审核
    public function message_nopass() {

        $this->sid=$_GET['id'];
		//echo "jhfjhfjhf".$_GET['id']."dfdfff";
        if(empty($this->sid)) {
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
		if($_GET['zh_msg'] == "未通过"){
        	$this->error("请填写具体驳回信息！！！");
        }
        if($this->sid[0]==',')
        {
            $this->sid=substr($this->sid,1);
        }

        $this->soft_comment_db=M('soft_comment');
        $map='';
        $map['status']='0';
		$map['denymsg']=$_GET['zh_msg'];
		$map['update_user_id']=$_SESSION['admin']['admin_id'];
		$map['update_time']=time();
		
        $this->map='';
        $this->map['id']=array('in',$this->sid);
		
        //dump($this->soft_list);
		//var_dump($this->map);exit;
		//echo $this->soft_comment_db->where($this->map)->save($map);exit;
        if(false!==$this->soft_comment_db->where($this->map)->save($map)) {
			$sid_array=explode(",",$this->sid);
			foreach($sid_array as $l=>$k){
				$this->writelog('未通过ID为['.$k.']的评论','sj_soft_comment',$k,__ACTION__ ,"","edit");
			}
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->success("未通过处理成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/message_soft');
            $this->error("未通过处理失败！");
        }
    }
 //张辉---信息管理__未通过结束
 
 
 
 
 
 
 
 
 

    //信息管理__删除反馈及举报
    public function feedback_unshow() {

        $this->sid=$_GET['id'];


        $type='';
        if($_GET['type']) {
            $type='feedback_list/type/soft';
        }else
        {
            $type='feedback_list';
        }
        if(empty($this->sid)) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/'.$type);
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if($this->sid[0]==',')
        {
            $this->sid=substr($this->sid,1);
        }

        $this->feedback_db=M('feedback');
        $map='';
        $map['status']='0';

        $this->map='';
        $this->map['feedbackid']=array('in',$this->sid);

        //dump($this->soft_list);

        if(false!==$this->feedback_db->where($this->map)->save($map)) {
			$sids=explode(",",$this->sid);
			foreach($sids as $k=>$val){
				$this->writelog('信息管理-软件反馈-删除了ID为['.$val.']的反馈、举报','sj_feedback',$sids[$k],__ACTION__ ,"","del");
			}
            //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/'.$type);
            $this->success("删除成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/'.$type);
            $this->error("删除失败！");
        }
    }


    //信息管理__取消缺乏列表显示
    public function lacklist_unshow() {
        $this->sid = $_GET['id'];
        $status = $_GET['status'];

        if(empty($this->sid)) {
        	$this->sid = $_POST['id'];
        	$status = $_POST['status'];
        }
        if(empty($this->sid)) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_lacklist');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }
        if($this->sid[0]==',')
        {
            $this->sid=substr($this->sid,1);
        }
        $this->soft_lack_db=M('soft_lack');
        $map='';
        $map['status'] = $status;

        $this->map= array();
        $this->map['id']=array('in',$this->sid);
        //dump($this->soft_list);
        if(false!==$this->soft_lack_db->where($this->map)->save($map)) {
			if(is_array($this->sid)){
				$packages = '';
				$id = '';
				foreach($this->sid as $v){
					$package = $this->soft_lack_db->where(array('id'=>$v))->select();
					$packages .=$package[0]['package'].",";
					$id .=$v.",";
				}
                $this->writelog('信息管理-缺乏软件管理-批量删除了ID为['.$id.']软件包名为['.$packages.']的缺乏显示软件','sj_soft_lack',$id,__ACTION__ ,"","del");
			}else{
				$package = $this->soft_lack_db->where($this->map)->select();
				$this->writelog('信息管理-缺乏软件管理-删除了ID为['.$this->sid.']软件包名为['.$package[0]['package'].']的缺乏显示软件','sj_soft_lack',$this->sid,__ACTION__ ,"","del");
			}
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_lacklist');
            $this->success("取消显示成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_lacklist');
            $this->error("取消显示失败！");
        }
    }
    //信息管理__认领不通过
    public function deny_claim() {
		$sid_arr = explode(',',$_GET['id']);
		if($sid_arr) {
			foreach($sid_arr as $key=>$val) {
				if(!is_numeric($val)) unset($sid_arr[$key]);
			}
		}

        if(!$sid_arr) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_claim');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }

		$sid_str = implode(',',$sid_arr);

        $this->soft_claim_db=M('soft_claim');
        $this->soft_db=M('soft');

        $arr=$this->soft_claim_db->where("id IN ({$sid_str})")->select();
		$package_arr = array();
		if($arr) {
			foreach($arr as $val) {
				$package_arr[] = $val['package'];
			}
		}
		if($package_arr) {
			$package_str = "'".implode("','",$package_arr)."'";
		}

        $thumbmap='';
        $thumbmap['dev_id']=0;
        $thumbmap['claim_status']=0;
        $this->soft_db->where("package IN ({$package_str})")->save($thumbmap);

        $map='';
        $map['status']='2';

        //dump($this->soft_list);
        if(false!==$this->soft_claim_db->where("id IN ({$sid_str})")->save($map)) {
	
            $this->writelog('拒绝了ID为'. $sid_str .'包名为'.$package_str.'的软件认领请求',"",$package_str,'sj_soft_claim',$sid_str,__ACTION__ ,"","edit");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_claim');
            $this->success("拒绝成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_claim');
            $this->error("拒绝失败！");
        }
    }
    //信息管理__认领通过
    public function permit_claim() {

        $id_arr = explode(',',$_GET['id']);
		if($id_arr) {
			foreach($id_arr as $key=>$val) {
				if(!is_numeric($val)) unset($id_arr[$key]);
			}
		}
        if(!$id_arr) {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_claim');
            $this->error("非法操作失败,如频繁出现，请联系管理员！");
        }

		$id_str = implode(',',$id_arr);
        $this->soft_claim_db=M('soft_claim');

        $this->soft_db=M('soft');
		$soft_note_db=M('soft_note');

		$log_id_arr = array();
		$package = '';
		foreach($id_arr as $id) {
			$claim_result=$this->soft_claim_db->where(array('id' => $id))->find();
			$package=$claim_result['package'];
			$dev_id=$claim_result['dev_id'];
			if(empty($dev_id)){
				continue;
			}
			$thumbmap='';
			$thumbmap['claim_status']=2;
			$thumbmap['dev_id']=$dev_id;
			$this->soft_db->where('package="'.$package.'"')->save($thumbmap);
			//$auth = array('auth'=>1);
			//$soft_note_db->where('package="'.$package.'"')->save($auth);

			$map='';
			$map['status']='1';

			$this->map='';
			$this->map['id']=array('in',$id);

			if(false!==$this->soft_claim_db->where($this->map)->save($map)) {
				$log_id_arr[] = $id;
			}
		}
		if($log_id_arr) {
			$this->writelog("通过了ID为[". implode(',',$log_id_arr) ."]软件包名为[".$package."]的软件认领请求","",$package,'sj_soft_claim',implode(',',$log_id_arr),__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_claim');
			$this->success("通过成功！");
		} else {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Message/soft_claim');
            $this->error("通过失败！");
		}
    }
    
	//异常管理
	function exceptionList()
	{
		//$group_only = $_REQUEST['group_only']?$_REQUEST['group_only']:'';
		
		import("@.ORG.Page");
		$model = D('Sj.MarketLog');
		$master_model = new Model();

		$this->assign('myget',$_GET);
		$where = '1 ';
		
 		if($_GET['type'] == 1){
			$zh_type=escape_string($_GET['type']);
			$where = $where . " and type = '".$zh_type."'";
		}elseif($_GET['type'] == 0){
			$zh_type=escape_string($_GET['type']);
			$where = $where . " and type = '".$zh_type."'";
		}
		!isset($_GET['pid']) ? $_GET['pid'] = 1 : $_GET['pid'];
		$this->assign("pid",$_GET['pid']);
        $where = $where . " and pid = '".$_GET['pid']."'";
 		if($_GET['dname']){
			$zh_dname=escape_string($_GET['dname']);
			$this->assign("dname",$_GET['dname']);
			$wheres['dname'] = array("like","%{$zh_dname}%");
			$dname = $master_model->table('pu_device')->where($wheres)-> select();
			foreach($dname as $n => $m){
				$did.= '"'.$m['did'].'",';
				$dids = substr($did,0,-1);
			}
			$where = $where . " and device in({$dids})";
		}
		if($_GET['market_imei']){
			$zh_market_imei=escape_string($_GET['market_imei']);
			$this->assign("market_imei",$_GET['market_imei']);
			$where = $where . " and imei = '".$zh_market_imei."'";
		}
		if($_GET['rom_firmware']){
			$zh_rom_firmware=escape_string($_GET['rom_firmware']);
			$this->assign("rom_firmware",$_GET['rom_firmware']);
			$where = $where . " and rom_firmware = '".$zh_rom_firmware."'";
		} 
		if($_GET['cid']){
			$cid_array['cid']=escape_string($_GET['cid']);
			$channel = $master_model->table('sj_channel')->where(array('cid'=>$cid_array['cid'],'status'=>1))->select();
			$chname = $channel[0]['chname'];
			$this->assign("chname",$chname);
			$this->assign("cid_array",$cid_array);
			$where = $where . " and channel like '".$cid_array['cid']."'";
		}
		if($_GET['market_version']){
			$zh_market_version=escape_string($_GET['market_version']);
			$where = $where . " and market_version = '".$zh_market_version."'";
			$this->assign('market_version', $_GET['market_version']);
		}
		if($_GET['time1']){
			$this->assign('time1', $_GET['time1']);
			$where =$where . " and submit_day >= '".strtotime($_GET['time1'])."'";
		}
		if($_GET['time2']){
			$this->assign('time2', $_GET['time2']);
			$where =$where . " and submit_day <= '".strtotime($_GET['time2']."23:59:59")."'";
		}
		if(strtotime($_GET['time1']) > strtotime($_GET['time2'])){
			$this ->error('开始时间不能大于结束时间');
		}
		if($_GET['group_only'] == 1){
			$count = $model->table('sj_market_exception')->field('DISTINCT(ex_md5)')->where($where)-> count();
			//$count = count($total);
		}else {
			$count = $model->table('sj_market_exception')->field('DISTINCT(id)')->where($where)-> count();
			//$count = count($total);
		}
		$Page = new Page($count,15);
		
		if($_POST['group_id'] == 1){
			$where_result = $model -> table('sj_market_exception') -> where(array('id' => $_GET['id'])) -> field('ex_md5') -> select();
			$data = array('type' => 1 , 'type_message' => $_POST['content']);
			$model->where(array("ex_md5"=>$where_result[0]['ex_md5']))->data($data)->save();
		    $this->writelog('信息管理-客户端异常管理-添加了ID为['.$_GET['id'].']的评论回复','sj_market_exception',$_GET['id'],__ACTION__ ,"","add");
			$this->success("添加成功！");
		}else{
			if($_POST){
				$data = array('type' => 1 , 'type_message' => $_POST['content']);
				$model->where(array("id"=>$_GET['id']))->data($data)->save();
				$this->writelog('信息管理-客户端异常管理-添加了ID为['.$_GET['id'].']的评论回复','sj_market_exception',$_GET['id'],__ACTION__ ,"","add");
				$this->success("添加成功！");
			}
		}
		
		if($_GET['group_only'] == 1){
			$exception = $model->table('sj_market_exception')->group('ex_md5')->where($where)->order('submit_day desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			 $list = array();
			foreach($exception as $k => $v){
				$dev = $master_model->table('pu_device')->where(array('did'=>$v['device'],'status'=>1))->select();
				$list[$k]['id'] = $v['id'];
				$list[$k]['imei'] = $v['imei'];
				$list[$k]['market_version'] = $v['market_version'];
				$list[$k]['rom_firmware'] = $v['rom_firmware'];
				$list[$k]['rom_version'] = $v['rom_version'];
				$list[$k]['submit_day'] = $v['submit_day'];
				$list[$k]['exception'] = $v['exception'];
				$list[$k]['type'] = $v['type'];
				$list[$k]['dname'] = $dev[0]['dname'];
				$channel = $master_model->table('sj_channel')->where(array('cid'=>$v['channel'],'status'=>1))->select();
				$list[$k]['chname'] = $channel[0]['chname']; 
			}
		}else{
			$exception = $model->table('sj_market_exception')->where($where)->order('submit_day desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$list = array();
			foreach($exception as $k => $v){
				$dev = $master_model->table('pu_device')->where(array('did'=>$v['device'],'status'=>1))->select();
				$list[$k]['id'] = $v['id'];
				$list[$k]['imei'] = $v['imei'];
				$list[$k]['market_version'] = $v['market_version'];
				$list[$k]['rom_firmware'] = $v['rom_firmware'];
				$list[$k]['rom_version'] = $v['rom_version'];
				$list[$k]['submit_day'] = $v['submit_day'];
				$list[$k]['exception'] = $v['exception'];
				$list[$k]['type'] = $v['type'];
				$list[$k]['dname'] = $dev[0]['dname'];
				$channel = $master_model->table('sj_channel')->where(array('cid'=>$v['channel'],'status'=>1))->select();
				$list[$k]['chname'] = $channel[0]['chname'];			
			}
		}
		//过滤
		$this->gpcFilter($list);
		$this->gpcFilter($group_only);
		$this->gpcFilter($keyword);
		
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );
		$this->assign('exceptionList', $list);
		$this->assign("group_only",$group_only);
		$this->assign ("keyword", $keyword );
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);			
		$this->display();
	}
	
	function showException()
	{
		$id = $_GET['id'];
		$model = D('Sj.MarketLog');
		$map = array(
			'id' => $id
		);
		$res = $model->table('sj_market_exception')->where($map)->find();
		$exception = '<pre>';
		$exception .= $res['exception'];
		$exception .= '</pre>';
		
		echo $exception;
	}
	
	function topSearchAppList()
	{
		import("@.ORG.Page");
		$model = M('soft_top_search');
		$count = $model->count('id');
		$Page = new Page($count,15);
		$list = $model->field('*')
			->order('upload_time desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','<<');
        $Page->setConfig('last','>>');
        $show =$Page->show();
        $this->assign ("page", $show );

		//过滤
		$this->gpcFilter($list);

		$this->assign('topSearchAppList', $list);
		$this->display();
	}
	function editTopSearchApp()
	{
		import("@.ORG.Input");
		$model = M('soft_top_search');
		if (!empty($_POST)) {
			$where = array(
				'id' => $_POST['id']
			);
			$map = array(
				'keywords' => Input::getVar($_POST['keywords']),
				'package' => Input::getVar($_POST['package']),
				'upload_time' => time(),
			);
			$log = $this -> logcheck(array('id' => $_POST['id']),'sj_soft_top_search',$map,$model);

			if($model->where($where)->save($map)){
			//$this->writelog('编辑了ID为['.$_POST['id'].']包名为['.$_POST['package'].']的关键词');
			$this->writelog("信息管理-关键词管理-编辑了ID为'".$_POST['id']."'的信息内容为:".$log,'sj_soft_top_search',$_POST['id'],__ACTION__ ,"","edit");	
			$this->display();
			}else{
				$this -> error('修改失败');
			}
		} else {
			$where = array(
				'id' => $_GET['id']
			);
			
			$app = $model->where($where)->find();
			$this->assign('top', $app);
			$this->display();
		}
	}
	function addTopSearchApp()
	{
		import("@.ORG.Input");
		$model = M('soft_top_search');
		if (!empty($_POST)) {
			$map = array(
				'keywords' => Input::getVar($_POST['keywords']),
				'package' => Input::getVar($_POST['package']),
				'upload_time' => time()
			);
			$ret=$model->add($map);
			$this->writelog('信息管理-关键词管理-添加了package为['.$_POST['package'].']的关键词','sj_soft_top_search',$ret,__ACTION__ ,"","add");
			$this->display();
		} else {
			$this->display();
				
		}
	}
	function delTopSearchApp()
	{
		$model = M('soft_top_search');
		$package =  $model-> where(array("id" => $_GET['id']))->find();
		$model->where(array("id" => $_GET['id']))->delete();
		$this->writelog('信息管理-关键词管理-删除了ID为['.$_GET['id'].']包名为['.$package['package'].']的关键词','sj_soft_top_search',$_GET['id'],__ACTION__ ,"","del");
		$this->success('删除成功');
	}
	
	/**
	 * 
	 * 客服回复页面 
	 */
	function feedback_reback(){
		$model = M("feedback");
		$ret = $model->where(array("feedbackid" =>$_GET['id']))->find();
		$modelpost = M("post");
		$modelthread = M("thread");
		$ret1 = $modelthread->table('pu_user')->where(array("userid" => $_GET['id']))->find();
  	    $data = array(
	  		'rid' => $_GET['id'] , 
	  		'type' => 2 , 
	  		'imsi' => $ret['imsi'] ,
	  		'mac' => $ret['mac'] , 
	  		'userid' => $ret['userid'] ,
	  		'username' => $ret1['user_name'],
	  		'imei' => $ret['imei'] , 
	  		'did' => $ret['did'] , 
	  		'cid' => $ret['cid'] ,
	  		'ip' => $ret['ipmsg'] ,
	  		'dateline' => $ret['submit_tm'] ,
	  		'admin_status' => 1 , 
	  		're_status' => 1,
	  		're_dateline'=> $ret['submit_tm'] , 
	  		'thread' => $ret['content'] , 
	  		'all_post' => 1 ,
	  		'new_post' => 1 , 
	  		'last_post_time' => time() , 
	  		'last_user_post_time' => $ret['submit_tm'],
	  		'vcode' => $ret['version_code'],
  	        'pid' => $ret['pid'],
	  	);	
  	    $retitd = $modelthread->add($data);
		$postdata = array('tid' => $retitd , 'contents' => $_POST['content'] , 'dateline' => time() , 'status' => '1' , 'user_type' => '1' , 'system_userid' => $_SESSION['admin']['admin_id']);
 		$ret=$modelpost->add($postdata);
 		$this->writelog('信息管理-软件反馈-添加了ID为['.$_GET['id'].']的举报回复','sj_thread',$ret,__ACTION__ ,"","add");
 		$this->success('回复成功');
 	}
 	
 	function fcmessagelist()
 	{
 		$Form=M('thread');
    	import("@.ORG.Page");
 
 		//普通方式实现分页
 		//$where = array ("mai_user_id" => array ("LIKE", $userids ) ); 
 		$mainurl = "";
 		$type = 0;
 		if($_GET['thread']){
 			
    	$where = array("admin_status" => 1 );
    	if($_GET['contents']){
    		echo $where = $where + array('thread' => array("LIKE" , "%".$_GET['contents']."%" ));
    		$this->assign("contents",$_GET['contents']);
    		$mainurl .= "/contents/".$_GET['contents'];
 		}
 		if($_GET['imei']){
    		$where = $where + array('imei' => array("LIKE" , "%".$_GET['imei']."%" ));
    		$this->assign("imei",$_GET['imei']);
    		$mainurl .= "/imei/".$_GET['imei'];
    	}
    	if($_GET['vcode']){
    		$where = $where + array('vcode' => array("LIKE" , "%".$_GET['vcode']."%" ));
    		$this->assign("vcode",$_GET['vcode']);
    		$mainurl .= "/imei/".$_GET['imei'];
 		}
 		if($_GET['new'] == 0){
 			$where = $where + array('new_post' => "0");
 			$this->assign("new",$_GET['new']);
 			$mainurl .= "/new/".$_GET['new'];
 		}elseif($_GET['new'] == 1){
 			$where = $where + array('new_post' => array("neq" , "0" ));
 			$this->assign("new",$_GET['new']);
 			$mainurl .= "/new/".$_GET['new'];
 		}
    	$count = $Form->where($where)->count();    //计算总数
    	
    	$p = new Page ( $count, 10);
		$list=$Form->limit($p->firstRow.','.$p->listRows)->where($where)->order('last_user_post_time desc')->findAll();
    	$page = $p->show ();
 		}else{
    	//导入分页类
    		$count = $Form->where("admin_status = 1")->count();    //计算总数
    		$p = new Page ( $count, 10 );
    		$list=$Form->limit($p->firstRow.','.$p->listRows)->where("admin_status = 1")->order('last_user_post_time desc')->findAll();
   			$page = $p->show ();
  		}
 		foreach($list as $key => $value){
 			if($value['did']){
    			$didname = $Form->table("pu_device")->where("did = {$value['did']}")->find();
    			$list[$key]['didname'] = $didname['dname'];
 			}
 			$didname1 = $Form->table("sj_post")->where("tid = {$value['tid']}")->order('dateline desc')->find();
    		if($didname1['contents'] != NULL)
    			$list[$key]['thread1'] = $didname1['contents'];
    	}
 		if($_GET['p']){
 			$mainurl .= "/p/".$_GET['p'];
 		}

		//过滤
		$this->gpcFilter($list);

  		$this->assign('mainurl',$mainurl);
    	$this->assign ( "page", $page );
    	$this->assign ( "list", $list );
 		$this->display();
 	}
	
	function fcmessagelistdel()
 	{
 		if($_GET['postid'])
 		{
 			$Form=M('post');
			$data = array('tid' => $_GET['postid'] , 'contents' => $_POST['contents'] , 'dateline'=>time() , 'status' => 1  , 'user_type' => '1' ,'system_userid' => $_SESSION['admin']['admin_id']);
 			$re=$Form->add($data);
 			$ret = $Form->table('sj_thread')->where(array("tid" => $_GET['postid']))->find();
 			$Form1=M('thread');
 			$data1 = array('last_post_time' => time() , 'new_post' => ($ret['new_post']+1) , 'all_post' => ($ret['all_post']+1));
 			$Form1->where("tid = {$_GET['postid']}")->data($data1)->save();
 			$this->writelog('信息管理-站内信管理系统-添加了TID为['.$_GET['postid'].']的回复','sj_post',$re,__ACTION__ ,"","add");
 			$this->success('回复成功'); 
 		}else{
 			if($_GET['id']){
 				$Form=M('thread');
 				$data = array('admin_status'=>'0');
 				$Form->data($data)->where(array("tid"=>$_GET['id']))->save();
 				$this->writelog('信息管理-站内信管理系统-删除了TID为['.$_GET['id'].']的回复','sj_thread',$_GET['id'],__ACTION__ ,"","del");
			}elseif($_GET['pid']){
				$Form=M('post');
				$Form2 = M('thread');
                $ret = $Form2->table('sj_thread')->where(array("tid" => $_GET['sid']))->find();
				$all_post = $ret['all_post']>0?($ret['all_post']-1):0;
                $new_post = $ret['new_post']>0?($ret['new_post']-1):0;
                $data1 = array('new_post'=>$new_post,'all_post'=>$all_post);
                $t = $Form2->where("tid = {$_GET['sid']}")->data($data1)->save();
 				$data = array('status'=>'0');
 				$Form->data($data)->where(array("pid"=>$_GET['pid']))->save();
 				$this->writelog('信息管理-站内信管理系统-删除了PID为['.$_GET['pid'].']的回复','sj_post',$t,__ACTION__ ,"","del");
			}
			$this->success('删除成功');
		}
		
 	}
 	
 	function fcmessagelistedit()
 	{
 		$_GET['imei'];
 		$_GET['contents'];
 		$_GET['vcode'];
 		$_GET['p'];
 		$mainurl = "";
 		if($_GET['imei']){
 			$mainurl .= "/imei/".$_GET['imei'];
 		}
 		if($_GET['contents']){
 			$mainurl .= "/contents/".$_GET['contents'];
 		}
 		if($_GET['vcode']){
 			$mainurl .= "/vcode/".$_GET['vcode'];
 		}
 		if($_GET['p']){
 			$mainurl .= "/p/".$_GET['p'];
 		}
 		$this->assign("mainurl",$mainurl);
 		$Form=M('post');
	    import("@.ORG.Page"); //导入分页类
    	$count = $Form->where(array("status" => 1 , "tid" => $_GET['id']))->count();    //计算总数
    	$p = new Page ( $count, 50 );
    	$list=$Form->limit($p->firstRow.','.$p->listRows)->where(array("status" => 1 ,"tid" =>$_GET['id']))->order('dateline desc')->findAll();
    	$title = $Form->table("sj_thread")->where(array("admin_status" => 1 ,"tid"=>$_GET['id']))->find();
    	//print_R($title);
    	$this->assign("t",$title['thread']);
    	$page = $p->show ();
    	$this->assign	('tid',$_GET['id']);
    	$this->assign ( "page", $page );
    	$this->assign ( "list", $list );
 		$this->display();
 	}
 	
 	function messagesofulist()
 	{
 		$Form=M('thread');
 		$ret = $Form->table("sj_soft_comment")->where(array("id"=>$_GET['id']))->find();
 		$data = array(
  					'rid' => $_GET['id'] , 
  					'type' => 1 , 
  					'imsi' => $ret['imsi'] ,
  					'mac' => $ret['mac'] , 
  					'userid' => $ret['userid'] ,
  					'username' => $ret['user_name'],
  					'imei' => $ret['imei'] , 
  					'did' => $ret['did'] , 
  					'cid' => $ret['cid'] ,
  					'ip' => $ret['ipmsg'] ,
  					'dateline' => $ret['create_time'] ,
  					'admin_status' => 1 , 
  					're_status' => 1,
  					're_dateline'=> $ret['create_time'] , 
  					'thread' => $ret['content'] , 
  					'all_post' => 1 ,
  					'new_post' => 1 , 
  					'last_post_time' => time() , 
  					'last_user_post_time' => $ret['create_time'] ,
  					'vcode' =>  $ret['version_code']
  					);	
  	$retitd = $Form->add($data);
		$postdata = array('tid' => $retitd , 'contents' => $_POST['content'] , 'dateline' => time() , 'status' => '1' , 'user_type' => '1' , 'system_userid' => $_SESSION['admin']['admin_id']);
 		$postForm = M('post');
 		$ret=$postForm->add($postdata);
 		$this->writelog('软件评论列表-添加了ID为['.$_GET['id'].']的评论回复','sj_soft_comment',$ret,__ACTION__ ,"","add");
 		$this->success('回复成功');
 		
 	}
	 //by 张辉 导出数据
    public function exportExcel() {
	    
		ini_set('memory_limit','1024M');
        set_time_limit(0);
        $feedbacklist = array();
		$this->feedback_db=M('feedback');
		$soft_db = M('soft');
        $device = D("Sj.Device");
		$this->map=$_SESSION['admin']['feedback_list']['where'];
		$this->order=$_SESSION['admin']['feedback_list']['order'];	
		
		
		if($this->map=="status=1 and softid=0"){
				$from_time=time()-30*86400;
				$to_time=time();
				$this->map="status=1 and softid=0 and submit_tm  >='".$from_time."' and submit_tm <= '".$to_time."'";
		}
		
		$conf_db = D('Sj.Config');
	    $config_list = $conf_db->where('config_type="backtype" and status=1')->getField('configcontent');
		//echo $this->config_list;
		$zh_config_list=explode("|",$config_list);
		
		$feedbacklist=$this->feedback_db->where($this->map)->order($this->order)->select();
        foreach($feedbacklist as $idx => $info){
			//$softname = $soft_db -> where('softid = '.$info['softid']) ->getField('softname');
            //$feedbacklist[$idx]['softname'] = $softname;
            //机型
            $dname = $device -> where('did = '.$info['did']) ->getField('dname');
            $feedbacklist[$idx]['dname'] = $dname;
            
            //反馈类型
            $feedbacklist[$idx]['backtype_name']=$zh_config_list[($feedbacklist[$idx]['backtype']-1)];
        }
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="feedbackinfo.csv"');
		header('Cache-Control: max-age=0');
		$fp = fopen('php://output', 'a');
		$head = array('ID', '用户ID','反馈类型','反馈内容','反馈时间','反馈IP','IMEI','version_code','机型','联系方式');
		foreach ($head as $i => $v) {
			// CSV的Excel支持GBK编码，一定要转换，否则乱码
			$head[$i] = iconv('utf-8', 'gbk', $v);
		}
		  
		 //将数据通过fputcsv写到文件句柄
		fputcsv($fp, $head);
		// 计数器
		$cnt = 0;
		// 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
		$limit = 100000;
        /*first line*/
        //至于读数据应该不用我说了 赋值些变量的问题??
        //表 title
		//echo count($softlist);exit
		foreach($feedbacklist as $idx => $info){
			$cnt ++;
			if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
				ob_flush();
				flush();
				$cnt = 0;
			}
            /*if((intval($abi) != 0) && ((intval($abi) & 8) == 0)){
            	$abi = "否";
            }else{
            	$abi = "是";
            }*/
			$list=array();
			$list['feedbackid']=iconv('utf-8', 'gbk',$info['feedbackid'])."\t";
			$list['userid']=iconv('utf-8', 'gbk',$info['userid'])."\t";
			$list['backtype_name']=iconv('utf-8', 'gbk',$info['backtype_name'])."\t";
			$list['content']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['content']))."\t";
			$list['submit_tm']=date('Y-m-d H:i:s',(int)$info['submit_tm'])."\t";
			$list['ipmsg']=iconv('utf-8', 'gbk',$info['ipmsg'])."\t";
			$list['imei']=iconv('utf-8', 'gbk',$info['imei'])."\t";
			$list['version_code']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['version_code']))."\t";
			$list['dname']=iconv('utf-8', 'gbk',$info['dname'])."\t";
			$list['contact']=iconv('utf-8', 'gbk',$info['contact'])."\t";
			fputcsv($fp, $list);
        }
        
    }
	//by 张辉 导出数据
    public function message_exportExcel() {
		ini_set('memory_limit','1024M');
        set_time_limit(0);
        $feedbacklist = array();
		$soft_comment_db=M('soft_comment');
		$soft_db = M('soft');
		$soft_device_obj = M('device_user');
        $device_obj        = D('Sj.Device');
		
		 $this->map=$_SESSION['admin']['message_soft']['where'];
			 if($this->map=="status=1"){
				$from_time=time()-30*86400;
				$to_time=time();
				$this->map="status=1 and create_time >='".$from_time."' and create_time <= '".$to_time."'";
			 }
		 //echo $this->map;exit;
		//$this->order=$_SESSION['admin']['feedback_list']['order'];
		$soft_comment_list=$soft_comment_db->where($this->map)->order('create_time desc')->select();
		//$this->success("fffff");
         foreach($soft_comment_list as $idx => $comment){
          $softname = $soft_db -> where('softid = '.$comment['softid'])->getField('softname');
          $where['imei'] = $comment['imei'];
          $user_device = $soft_device_obj -> where($where) -> select();
          $c_did = $comment['did']?$comment['did']:$user_device[0]['did'];
          $dname         = $device_obj -> where('did = '.$c_did )->getField('dname');
          $soft_comment_list[$idx]['softname'] = $softname;
          $soft_comment_list[$idx]['firmware'] =  $user_device[0]['firmware'];
          $soft_comment_list[$idx]['version_code'] =  $comment['version_code']?$comment['version_code']:$user_device[0]['version_code'];
          $soft_comment_list[$idx]['dname']    = $dname;
        }
       header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="messageinfo.csv"');
		header('Cache-Control: max-age=0');
		$fp = fopen('php://output', 'a');
		$head = array('软件ID', '软件名','用户ID','用户名','IMEI','机型','市场版本号','固件适配号','内容','积分','发现时间','IP');
		foreach ($head as $i => $v) {
			// CSV的Excel支持GBK编码，一定要转换，否则乱码
			$head[$i] = iconv('utf-8', 'gbk', $v);
		}
		  
		 //将数据通过fputcsv写到文件句柄
		fputcsv($fp, $head);
		// 计数器
		$cnt = 0;
		// 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
		$limit = 100000;
        /*first line*/
        //至于读数据应该不用我说了 赋值些变量的问题??
        //表 title
		//echo count($softlist);exit;
		
		foreach($soft_comment_list as $idx => $info){
			$cnt ++;
			if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
				ob_flush();
				flush();
				$cnt = 0;
			}
            /*if((intval($abi) != 0) && ((intval($abi) & 8) == 0)){
            	$abi = "否";
            }else{
            	$abi = "是";
            }*/
			$list=array();
			$list['softid']=iconv('utf-8', 'gbk',$info['softid'])."\t";
			$list['softname']=iconv('utf-8', 'gbk',$info['softname'])."\t";
			$list['userid']=iconv('utf-8', 'gbk',$info['userid'])."\t";
			$list['user_name']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['user_name']))."\t";
			$list['imei']=iconv('utf-8', 'gbk',$info['imei'])."\t";
			$list['dname']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['dname']))."\t";
			$list['version_code']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['version_code']))."\t";
			$list['firmware']=iconv('utf-8', 'gbk',$info['firmware'])."\t";
			$list['content']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['content']))."\t";
			$list['score']=iconv('utf-8', 'gbk',$info['score'])."\t";
			$list['create_time']=date('Y-m-d H:i:s',(int)$info['create_time'])."\t";
			$list['ipmsg']=iconv('utf-8', 'gbk',$info['ipmsg'])."\t";
			fputcsv($fp, $list);
        }
        
    }
    
    // 贴士信息管理列表By haoxian
 	function tips_info() {
 		$status = isset($_GET['status']) ? $_GET['status'] : 1;
 		$tipsinfo = M("tipsinfo");
        $sel = M('product');
        $pid = $_GET['pid'];
        $sel_list = $sel ->table('pu_product')->where('status = 1')->findAll();
        $pid = $pid=='' ? 1: $pid;
 		$total = $tipsinfo->where("status=$status and pid = $pid")->count();
		import("@.ORG.Page");
		$Page       = new Page($total,15);
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');		
		$show = $Page->show();
        $tips_info = $tipsinfo->table('pu_product')->join('sj_tipsinfo on sj_tipsinfo.pid = pu_product.pid')->where("sj_tipsinfo.status = $status and sj_tipsinfo.pid = $pid")->order('sj_tipsinfo.id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		//过滤
		$this->gpcFilter($tips_info);
		$this->assign('status', $status);
		$this->assign('tipsinfo',$tips_info);
		$this->assign('page',$show);
        $this->assign ("sel_list", $sel_list);
        $this->assign ('pid',$pid);
 		$this->display();
 	}
 	// 贴士信息管理 停用、启用By haoxian
 	function tips_info_operate(){
 		$tipsinfo = M("tipsinfo");
 		$id = $_GET['id'];
 		$operating = $_GET['operating'];
 		$data['status'] = $operating;
 		$title = $operating ? '启用' : '停用';
 		if ($tipsinfo->where("id=$id")->save($data)){
 			$this->writelog($title."了ID为[".$id."]的贴士信息",'sj_tipsinfo',$id,__ACTION__ ,"","edit");
 			$this->ajaxReturn(1,1,1);
 		}
 	}
 	// 贴士信息管理 编辑、添加By haoxian
 	function tips_info_edit(){
 		$tipsinfo = M("tipsinfo");
        $sel = M('product');
        $sel_list = $sel ->table('pu_product')->where('status = 1')->findAll();
        $this->assign ( "sel_list", $sel_list );
        $p_id = substr($_GET['pid'],0,1);
        $this->assign ( "p_id", $p_id );
 	 	if (isset($_POST) && !empty($_POST) && !is_null($_POST)){
			$id = trim($_POST['id']);
 			$data['tipsContent'] = trim($_POST['tipsContent']);
 			$data['showProbability'] = trim($_POST['showProbability']);
 			$data['updateTime'] = time();
            $data['pid'] = $_POST['pid'];
 		    
 			if (is_null($data['tipsContent']) || empty($data['tipsContent']) || $data['tipsContent'] == ''){
 				$this->ajaxReturn(0,'贴士信息不能为空',0);
 			}
 			if (mb_strlen($data['tipsContent'], 'utf-8') > 15){
 				$this->ajaxReturn(0,'贴士信息字数过长',0);
 			}
 			if (ceil($data['showProbability']) != $data['showProbability']){
 				$this->ajaxReturn(0,'显示概率不合法',0);
 			}
 			if ($data['showProbability'] > 100 || $data['showProbability'] < 1){
 				$this->ajaxReturn(0,'显示概率不合法',0);
 			}
 			if ($data['pid']==''){
                $this->ajaxReturn(0,'选择平台不能为空',0);
            }
 			$flag = 0;
 			if ($id){
				$log = $this -> logcheck(array('id' => $id),'sj_tipsinfo',$data,$tipsinfo);
 				if ($tipsinfo->where("id=$id")->save($data)){
                    $this->writelog("信息管理-贴士信息管理-编辑了ID为[$id]的贴士信息内容为:".$log,'sj_tipsinfo',$id,__ACTION__ ,"","edit");
                     $this -> success("编辑成功");
 					$flag = 1;
 				}
 			} else {
	 			$res = $tipsinfo->where("tipsContent='{$data['tipsContent']}'")->field('id')->select();
	 			if ($res[0]['id']){
	 				$this->ajaxReturn(0,'贴士内容重复',0);
	 			} else {
	 				$data['status'] = 1;
	 				if ($id = $tipsinfo->add($data)){
                        $this->writelog("信息管理-贴士信息管理-添加了ID为[$id]的贴士信息内容为:{$data['tipsContent']}",'sj_tipsinfo',$id,__ACTION__ ,"","add");
                        $this -> success("添加成功");
	 					$flag = 1;
	 				}
	 			}			
 			}
 			$title = empty($id) ? '添加' : '编辑';
 			if ($flag){
 				$this->ajaxReturn(1,"$title".'成功',1);
 			} else {
 				$this->ajaxReturn(0,"$title".'失败',0);
 			}
 		} else {
	 		$id = isset($_GET['id']) && !empty($_GET['id']) ? $_GET['id'] : '';
	 		$title = empty($id) ? '添加' : '编辑';
	 		if ($id){
	 			$res = $tipsinfo->where("id=$id")->select();
	 		}
	 		$this->assign('tipsinfo', $res[0]);
	 		$this->assign('title', $title);
			$this->display();			
 		}
 	}
	//V6.0头像昵称 2015-5-19 added by shitingting
	function comment_nickname()
	{
		$model = M("comment_nickname");
		$where=array(
			'status' => 1,
		);
		import('@.ORG.Page2');	
		$param = http_build_query($_GET);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 80;
		$count=$model->where($where) ->count();
		$Page = new Page($count,$limit,$param);	
		$list=$model->where($where)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$show_upload_info=file_get_contents("/tmp/test.txt");
		
		$this->assign("uplod_info",$show_upload_info);
		$this->assign("list",$list);
		$this -> assign('page', $Page->show());	
		$this->display();
		unlink("/tmp/test.txt");
	}
	function nickname_add()
	{
		$model = M("comment_nickname");
		$filename=trim($_FILES['upload_file']['tmp_name']);
		$err = $_FILES["upload_file"]["error"];
		$file_name_csv=$_FILES['upload_file']['name'];
		$tmp_arr = explode(".", $file_name_csv);
		$name_suffix = array_pop($tmp_arr);
		if(!$filename)
		{
			$error1=-1;
		}
		else
		{
			if (strtoupper($name_suffix) != "CSV") 
			{
				$error2=-2;
			}
			else
			{
				$handle=fopen($filename,'r');
				$out = array (); 
				while (!feof($handle)) 
				{
					$nickname=trim(fgets($handle));
					$nickname=iconv('GB2312', 'UTF-8', $nickname);
					if($nickname)
					{ 
						$have=$model->where(array('nickname' => $nickname, 'status'=>1))->find();
						if($have)
						{
							$have_name[]= $have['nickname'];
						}
						else
						{
							$map['nickname']=$nickname;
							$map['create_at']=time();
							$map['update_at']=time();
							$map['status']=1;
							$result[]=$model->add($map);
						}
					}
				}
				fclose($filename);
				$repeat_count=count($have_name);
				if($have_name)
				{
					$repeat_names=implode(",",$have_name);
				}
				if($result)
				{
					$len_result=count($result);
					$data="本次上传成功：{$len_result}个，重复：{$repeat_count}个，重复昵称:{$repeat_names}";
					file_put_contents("/tmp/test.txt",$data);
					$ids=implode(",",$result);
					$success=1;
					$this->writelog('添加了ID为['.$ids.']的昵称','sj_comment_nickname',$ids,__ACTION__ ,"","add");
				}
			}
		}
		
		
		echo '{"nickname_count":"' . $len_result . '","nickname_repeat_count":"' . $repeat_count .'","repeat_nickname":"' . $repeat_names . '","error1":"'.$error1.'","error2":"'.$error2.'","success":"'.$success.'"}';
	}
	function del_comment_nickname()
	{
		$model = M("comment_nickname");
		$where = array('status'=>1,'id'=>array('in',$_GET['id']));
		$map = array('status'=>0,'update_at'=>time());
		$ret = $model ->where($where)->save($map);
		if($ret)
		{
			$this->writelog('删除了ID为['.$_GET['id'].']的昵称','sj_comment_nickname',$_GET['id'],__ACTION__ ,"","del");
			$this->success('操作成功');
		}
		else
		{
			$this -> error('操作失败');
		}
	}
}
?>
