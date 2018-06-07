<?php
class CaijiModel extends AdvModel {

    protected $connect_id = 24;

    public function __construct(){
            parent::__construct();
            $cj_Connect = C('DB_CAIJI');

            $this -> addConnect($cj_Connect, $this->connect_id);
            $this -> switchConnect($this->connect_id);
    }

    //系统扫描配置
    function getscanlist(){
        $ret = $this->table('cj_scan_config')->select();
        return $ret;
    }

    //根据ID获取系统扫描
    function getscaninfo($id){
        $ret = $this->table('cj_scan_config')->where("id = $id")->find();
        return $ret;
    }

    //根据ID改变配置的状态
    function updatestatus($id,$status){
        $data = array();
        $data['status']= $status;
        $data['update_tm']=time();
        $data['os_username']=$_SESSION['admin']['admin_user_name'];
        return $ret = $this->table('cj_scan_config')->where("id = $id")->save($data);
    }

    //根据ID改变待采集信息的状态
    function updatestatus_standby($id,$status){
        $data = array();
        $data['status']= $status;
        $data['update_tm']=time();
        $data['package_from']=2;
        return $ret = $this->table('cj_standby_fetch')->where("id = $id")->save($data);
    }

    //根据ID改变待采集信息的状态
    function deletestandby($id){
        $ret = $this->table('cj_standby_fetch')->where("id = $id")->delete();
        return $ret;
    }    

    //根据ID改变配置的备注
    function updatedesc($id,$desc){
        $data = array();
        $data['desc']= $desc;
        $data['update_tm']=time();
        $data['os_username']=$_SESSION['admin']['admin_user_name'];
        return $ret = $this->table('cj_scan_config')->where("id = $id")->save($data);
    }
    

    //修改系统扫描配置
    function savescan($id,$download,$dev_name){
        $data = array();
        $data['download']=$download;
        $data['dev_name']=$dev_name;
        $data['update_tm']=time();
        $data['os_username']=$_SESSION['admin']['admin_user_name'];
        $ret = $this->table('cj_scan_config')->where("id = $id")->save($data);
        return $ret;
    }


    //修改系统扫描配置 新增第三方
    function savescan_new($post){
        $id = $post['id'];
        $data = array();
        $data['category_key']=$post['category_key'];
        $data['score']=$post['score'];
        $data['download']=$post['download'];
        $dev_name = $post['dev_name'];
        if(strlen($dev_name)>0)
        {
            if(strpos($dev_name,',')!==false)
            {
                $tmparr = explode(',',$dev_name);
                $str = '';
                foreach($tmparr as $v)
                {
                    if(!empty($v))
                    {
                        $str.= ','.$v;
                    }
                }
                $str = $str.',';
            }else
            {
                $str= ','.$dev_name.',';
            }
        }

        $data['dev_name']=$str;
        $data['update_tm']=time();
        $data['os_username']=$_SESSION['admin']['admin_user_name'];
        $ret = $this->table('cj_scan_config')->where("id = $id")->save($data);
        return $ret;
    }

    //修改待采集
    function savestandby($post,$cname,$total_downloaded,$pkgname =null){
        //判断 只能有一条 人工添加的数据
        if(!empty($pkgname))
        {
            $package = $pkgname;
            $model = M();
            $rets = $model->table('sj_soft')->field('softname')->where("package = '$package' and status=1 and hide=1")->find();
            if($rets==null){
                $res = $model->table('sj_soft_tmp')->where("package = '$package' and status=2")->find();
                $softname = $res['softname'];
            }else{
                $softname = $rets['softname'];
            }
            $type = 2;
        }else{
            $softname = $post['softname'];
            $package = $post['package'];
            $type = $post['ptype'];
        }
        $id = $post['pid'];
        //supwater
        $ret = $this->table('cj_standby_fetch')->where("package = '$package' and status=1 and package_from=2")->find();
        if($ret===NULL||$ret['id']==$post['pid']) //要除了自己
        {
            if($type==1) //修改
            {
                $data = array();
                $data['package']=$post['package'];
                $data['softname']=$softname;
                $data['examine_type']=$post['examine'];
                $data['desc']=$post['desc'];
                $data['az_category']=$cname;
                $data['az_downloaded']=$total_downloaded;
                $data['package_from']=2;
                $data['os_username']=$_SESSION['admin']['admin_user_name'];
                $this->table('cj_standby_fetch')->where("package='$package' and id!=$id")->delete();
                $ret = $this->table('cj_standby_fetch')->where("id = $id")->save($data);
                return $ret;
            }else if($type==2)//新增
            {
                $data = array();
                $data['package']=$package;
                $data['softname']=$softname;
                $data['examine_type']=$post['examine'];
                $data['desc']=$post['desc'];
                $data['az_category']=$cname;
                $data['az_downloaded']=$total_downloaded;
                $data['package_from']=2;
                $data['create_tm']=time();
                $data['os_username']=$_SESSION['admin']['admin_user_name'];
                $this->table('cj_standby_fetch')->where("package='$package'")->delete();
                $ret = $this->table('cj_standby_fetch')->add($data);
                return $ret;
            }
        }else{
            return -1;
        }
    }



    function get_standby_info($id)
    {
        $ret = $this->table('cj_standby_fetch')->where("id = $id")->find();
        return $ret;
    }

    //获取待采集列表
    function get_standby_fetch($get,$type=1){

        if($get['az_category']==1||$get['az_category']==2)
        {
            if($get['az_category']==1)
            {
                $where['az_category'] = array('eq','应用');
            }else if($get['az_category']==2)
            {
                $where['az_category'] = array('eq','游戏');
            }
        }

        if($get['examine_type']==1||$get['examine_type']==2||$get['examine_type']==3)
        {
            if($get['examine_type']!=-1)
            {
                $where['examine_type'] = array('eq',$get['examine_type']);
            }
        }

        if(isset($get['dev_type']))
        {
            if($get['dev_type']==0||$get['dev_type']==1||$get['dev_type']==2||$get['dev_type']==3)
            {
                if($get['dev_type']==3)
                {
                    $where['dev_type'] = array('exp','is null');
                }else
                {
                    $where['dev_type'] = array('eq',$get['dev_type']);
                }
            }
        }

        if($get['package_from']==1||$get['package_from']==2)
        {
            if($get['package_from']!=-1)
            {
                $where['package_from'] = array('eq',$get['package_from']);
            }
        }

        if(isset($get['status']))
        {
            if($get['status']!=-1)
            {
                $where['status'] = array('eq',$get['status']);
            }
        }

        if(strlen($get['softname'])>0)
        {
            $where['softname'] = array('like', '%'.$get['softname'].'%');
        }

        if(strlen($get['package'])>0)
        {
            //$where['package'] = array('like', '%'.$get['package'].'%');
            $where['package'] = array('eq',$get['package']);
        }

        if(strlen($get['add_begintime'])>0)
        {
            $where['create_tm']  = array('between',''.strtotime($get['add_begintime']).','.strtotime($get['add_endtime']).'');
        }

        $down_str = $get['down_str']*10000;
        $down_end = $get['down_end']*10000;
        if(strlen($get['down_str'])>0)
        {
            if(strlen($get['down_end'])>0)
            {
                $where['az_downloaded']  = array('between',''.$down_str.','.$down_end);
            }else
            {
                $where['az_downloaded']  = array('egt',$down_str);
            }
        }else if(strlen($get['down_end'])>0)
        {
            $where['az_downloaded']  = array('elt',$down_end);
        }

        $order = 'create_tm desc';

        if($type==2)
        {
            $rs = $this->table('cj_standby_fetch')->field('*')->where($where)->order($order)->select();
            $res = array(
                'list'=>$rs,
            );
        }else
        {
            import("@.ORG.Page");
            $count = $this->table('cj_standby_fetch')->where($where)->count();
            $page = new Page($count, 10);
            $rs = $this->table('cj_standby_fetch')->field('*')->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
            //echo $this->getlastsql();
            $page->setConfig('header','条记录');
            $page->setConfig('first','<<');
            $page->setConfig('last','>>');
            $show =$page->show();    
            $res = array(
                'list'=>$rs,
                'page'=>$show,
                'count'=>$count,
            );
        }
        return $res;
    }


    //获取合作软件列表
    function getcooplist($get,$type=1){

        if($get['az_category']==1||$get['az_category']==2)
        {
            if($get['az_category']==1)
            {
                $where['az_category'] = array('eq','应用');
            }else if($get['az_category']==2)
            {
                $where['az_category'] = array('eq','游戏');
            }
        }

        if($get['sync_from']==1||$get['sync_from']==2||$get['sync_from']==3||$get['sync_from']==4)
        {
            if($get['sync_from']==1)
            {
                $where['sync_from'] = array('eq','刷量白名单');
            }else if($get['sync_from']==2)
            {
                $where['sync_from'] = array('eq','运营白名单');
            }else if($get['sync_from']==3)
            {
                $where['sync_from'] = array('eq','闪屏-通过');
            }else if($get['sync_from']==4)
            {
                $where['sync_from'] = array('eq','商务白名单');
            }
        }

        if($get['status']==1||$get['status']==2||$get['status']==3)
        {
            if($get['status']==1)
            {
                $where['status'] = array('eq',$get['status']);
            }else if($get['status']==2)
            {
                $where['status'] = array('eq',$get['status']);
            }else if($get['status']==3)
            {
                $where['status'] = array('eq',$get['status']);
            }
        }        

        if(strlen($get['softname'])>0)
        {
            $where['softname'] = array('like', '%'.$get['softname'].'%');
        }

        if(strlen($get['package'])>0)
        {
            //$where['package'] = array('like', '%'.$get['package'].'%');
            $where['package'] = array('eq',$get['package']);
        }

        if(strlen($get['dev_name'])>0)
        {
            $where['dev_name'] = array('like', '%'.$get['dev_name'].'%');
        }

        if(strlen($get['cj_begintime'])>0)
        {
            $where['fetch_tm']  = array('between',''.strtotime($get['cj_begintime']).','.strtotime($get['cj_endtime']).'');
        }

        if(strlen($get['add_begintime'])>0)
        {
            $where['create_tm']  = array('between',''.strtotime($get['add_begintime']).','.strtotime($get['add_endtime']).'');
        }

        $down_str = $get['down_str']*10000;
        $down_end = $get['down_end']*10000;
        if(strlen($get['down_str'])>0)
        {
            if(strlen($get['down_end'])>0)
            {
                $where['az_downloaded']  = array('between',''.$down_str.','.$down_end);
            }else
            {
                $where['az_downloaded']  = array('egt',$down_str);
            }
        }else if(strlen($get['down_end'])>0)
        {
            $where['az_downloaded']  = array('elt',$down_end);
        }
        $where['sync_from']  = array('neq','商务广告');
        $order = 'status desc,fetch_tm desc';

        if($type==2)
        {
            $rs = $this->table('cj_soft_coop')->field('*')->where($where)->order($order)->select();
            $res = array(
                'list'=>$rs,
            );
        }else
        {
            import("@.ORG.Page");
            $count = $this->table('cj_soft_coop')->where($where)->count();
            $page = new Page($count, 10);
            $rs = $this->table('cj_soft_coop')->field('*')->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
            //echo $this->getlastsql();
            $page->setConfig('header','条记录');
            $page->setConfig('first','<<');
            $page->setConfig('last','>>');
            $show =$page->show();    
            $res = array(
                'list'=>$rs,
                'page'=>$show,
                'count'=>$count,
            );
        }
        return $res;
    }

    //获取邮件配置列表
    function getmailist(){
        $ret = $this->table('cj_email_config')->select();
        return $ret;
    }
	
	//检查添加站点的分类是否重复  
	//added by shitingting 2015/3/23
	function check_category($arr,$appfrom,$id=0)
	{
		$category_name="";
		$where=array(
		'website_id'=>$appfrom,
		'status'=>1,
		);
		if($id)
		{
			$where['_string'] = "id !=".$id;
		}
		$find=$this->table('cj_add_category_config')->field('category_name')->where($where)->select();
		foreach($find as $va)
		{
			$va['category_name']=substr($va['category_name'],0,-1);
			$category_name .=$va['category_name'];
		}
		$category=explode(',', $category_name);
		$have=array_intersect($arr,$category);
		return $have;
	}

    //根据ID获取邮件配置
    function getmailinfo($id){
        $ret = $this->table('cj_email_config')->where("id = $id")->find();
        return $ret;
    }

    //修改邮件
    function savemail($post){
        $id = $post['id'];
        $data = array();
        $data['send_mails']=$post['send_mails'];
        $data['cc_mails']=$post['cc_mails'];
        $data['update_tm']=time();
        $data['os_username']=$_SESSION['admin']['admin_user_name'];
        $ret = $this->table('cj_email_config')->where("id = $id")->save($data);
        return $ret;
    }

    //根据ID改变配置的状态
    function updatestatus_mail($id,$status){
        $data = array();
        $data['status']= $status;
        $data['update_tm']=time();
        $data['os_username']=$_SESSION['admin']['admin_user_name'];
        return $ret = $this->table('cj_email_config')->where("id = $id")->save($data);
    }
	function save_cj_update($pkg,$msg,$table){
		$where = array(
			'package' => $pkg,
			'status' => 1,
		);
		$map = array(
			'status' => 3,
			'ignore_tm' => time(),
			'ignore_contents' => $msg,
		);	
		$this->table($table)->where($where)->save($map);
	}
	//获取不同来源的md5
	function get_md5($appfrom,$package,$version_code,$table)
	{
		$where=array(
			'package'=>$package,
			'version_code' => $version_code,
			'appfrom' => $appfrom,
			'status' => 1,
		);
		$apk_md5 = $this->table($table)->field('apk_md5')->where($where)->find();
		return $apk_md5;
	}
	//获取数据
	function get_data($where,$params,$table){
		//下载量和抓取时间排序 
 		if(!empty($params['orderby'])){
			$orderby = '';
			if ($params['orderby'] == 'download') {
				$orderby = 'az_downloaded';
			} elseif ($params['orderby'] == 'download_count') {
				$orderby = 'download_count';
			} elseif ($params['orderby'] == 'time') {
				$orderby = 'create_time';
			}
			 elseif ($params['orderby'] == 'website_update_time') {
				$orderby = 'website_update_time';
			}
			 elseif ($params['orderby'] == 'bi_query_num') {
				$orderby = 'bi_query_num';
			}
		}else{
			if($where['status'] == 2){
				$orderby = 'review_time';
			}else if($where['status'] == 3){
				$orderby = 'ignore_tm';
			}else{
				$orderby = 'create_time';
			}
		}	
		$order  = !empty($params['order']) ? $params['order'] : 'd';
		if ($order == 'd') {
			$order_str = $orderby.' desc';
		} elseif ($order == 'a') {
			$order_str = $orderby.' asc';
		}
		if($where['status'] == 1){
			$total = $this->table($table)->where($where)->field("count(DISTINCT package) as total")->find();	
		}else{
			$totals = $this->table($table)->where($where)->count();
			$total['total'] = $totals;
		}
		import('@.ORG.Page2');
		//分页		
		$limit = isset($params['limit']) ? $params['limit'] : 10;	
		$param = http_build_query($params);
		$Page = new Page($total['total'],$limit,$param);
		$Page->rollPage = 10;
        $Page->setConfig('header','条记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		if($where['status'] == 1){
			if($table == 'cj_soft_update' ){
				$field = 'id,package,softname,version_name,version_code,is_ad,is_safe,is_office,az_category,icon_path,icon_md5,apk_path,download_count,az_downloaded,appfrom,create_time,status,examine_type,detail_url,sign,is_officesign';
			}else{
				$field = 'id,package,softname,category_name,version_name,version_code,is_ad,is_safe,is_office,icon_path,icon_md5,apk_path,download_count,az_downloaded,appfrom,create_time,detail_url,website_update_time,is_md5_same,soft_type,bi_query,bi_query_num';
			}
			$subQuery = $this->table($table)->where($where)->order('create_time desc')->field($field)->buildSql(); 			
			$list = $this->table("({$subQuery}) A")->group('package')->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->field('*,count(*) as toal')->select();
			//echo $this->getlastsql();
		}else{
			$list = $this->table($table)->where($where)->order($order_str)->limit($Page->firstRow.','.$Page->listRows)->select();
			//echo $this->getlastsql();
		}
		return array($list,$total['total'],$Page);
	}
	//获取采集表的软件信息
	function get_soft_info(){
		if($_GET['type'] == 'add'){
			$table = 'cj_soft_add';
		}else{
			$table = 'cj_soft_update';
		}
		$id = explode(',',$_GET['id']);
		$where = array(
			'id'=>array('in',$id)
		);
		$list = $this->table($table)->where($where)->select();
		$softname = array();
		$package = array();
		$appfrom = array();
		foreach($list as $v){
			$softname[] = $v['softname']; 
			$package[] = $v['package']; 
			$appfrom[] = $v['appfrom']; 
		}
		return array($softname,$package,array_unique($appfrom));
        }

	//采集忽略提交
	function update_ignored_do($id){
		$msg = $_POST['msg'];

		$appfrom = explode(',',$_POST['appfrom']);

		if($_POST['type'] == 'add'){
			$table = 'cj_soft_add';
		}else{
			$table = 'cj_soft_update';
		}

		$where = array(
			'id'=>array('in',$id),
			'status' => 1,
		);

		$list = $this->table($table)->where($where)->field('softname,package,download_count')->select();
                if($_POST['is_black']==1){
                    $this->insert_black_list($list,$msg);
                }

		$pkg = array();
		foreach($list as $v){
			$pkg[] = $v['package'];
		}
		unset($list);

		$where = array(
			'package'=>array('in',$pkg),
			'appfrom'=>array('in',$appfrom),
			'status' => 1,
		);		
		$map = array(
			'ignore_contents' => $msg,
			'ignore_tm' => time(),
			'status' => 3
		);
		$ret = $this->table($table)->where($where)->save($map);
		if($ret){
			return 1;
		}else{
			return 0;
		}
	}


        //忽略写入黑名单
        function insert_black_list($list,$msg)
        {
            $data = array();
            foreach($list as $v)
            {
                $data['softname'] = $v['softname'];
                $data['package'] = $v['package'];
                $data['download_count'] = $v['download_count'];
                $data['create_tm'] = time();
                $data['type'] = 1;
                $data['desc'] = $msg;
                $this->table('cj_black_list')->add($data);
            }
        }

	//
	function get_from_soft($v){
		if($_POST['type'] == 'add'){
			$table = 'cj_soft_add';
		}else{
			$table = 'cj_soft_update';
		}
		$where = array(
			'package' => $v['package'],
			'id' => array('exp',"!={$v['id']}"),
			'status' => 1
		);
		//$table = "(select  U.*, C.`priority` from {$table} U LEFT join cj_update_website_config C on U.appfrom_id = C.id WHERE (U.status = 1) ORDER BY C.`priority` ASC ) A";
		$subQuery = $this->table($table)->where($where)->order('create_time desc')->buildSql();
		if($_POST['type'] == 'add')
		{
			//新增  增加网站更新时间的字段
			$list = $this->table("({$subQuery}) A")->field('A.id,A.softname,A.apk_path,A.appfrom,A.create_time,A.icon_path,A.download_count,A.detail_url,A.website_update_time')->select();
		}
		else
		{
			$list = $this->table("({$subQuery}) A")->field('A.id,A.softname,A.apk_path,A.appfrom,A.create_time,A.icon_path,A.download_count,A.detail_url')->select();
		}
		foreach($list as $k => $val){
			$list[$k]['id'] = $val['id'];
			$list[$k]['icon_path'] =  "<img src='".CAIJI_ATTACHMENT_HOST . $val['icon_path']."' width='48' height='48'/>";
			$list[$k]['apk_path'] = "<a href='".CAIJI_ATTACHMENT_HOST . $val['apk_path']."' ><b>立即下载</b></a></a>";
			
			$list[$k]['download_count'] = number_format($val['download_count']);
			$list[$k]['appfrom'] = $val['appfrom'];
			//新增  增加网站更新时间
			if($_POST['type'] == 'add')
			{
				$list[$k]['website_update_tm'] = $val['website_update_time'] ? date("Y-m-d",$val['website_update_time']) : '';
			}
			$list[$k]['create_time'] = $val['create_time'] ? date("Y-m-d H:i:s",$val['create_time']) : '';
			$list[$k]['softname'] = $val['softname'];
			$list[$k]['package'] = $v['package'];
			$list[$k]['dev_name'] = $v['dev_name'] ? $v['dev_name'] : '';
			$list[$k]['dev_id'] = $v['dev_id'] ? $v['dev_id'] : '';
			$list[$k]['email'] = $v['email'] ? $v['email'] : '';
			$list[$k]['version_code'] = $v['version_code'];
			$list[$k]['version_name'] = $v['version_name'];
			$list[$k]['az_version_code'] = $v['az_version_code'];
			$list[$k]['az_category'] = $v['az_category'];
			$list[$k]['az_version'] = $v['az_version'];
			$list[$k]['az_downloaded'] = $v['az_downloaded'];
                        $list[$k]['detail_url'] = $val['detail_url'];
			if($v['is_office'] == 1){
				$list[$k]['is_office'] = "官方&nbsp;|";
			}else{
				$list[$k]['is_office'] = "";
			}
			if($v['is_safe'] == 1){
				$list[$k]['is_safe'] = "安全&nbsp;|";
			}else if($v['is_safe'] == 2){
				$list[$k]['is_safe'] = "不安全&nbsp;|";
			}else{
				$list[$k]['is_safe'] = "";
			}
			if($v['is_ad'] == 1){
				$list[$k]['is_ad'] = "无广告&nbsp;|";
			}else if($v['is_ad'] == 2){
				$list[$k]['is_ad'] = "有广告&nbsp;";
			}else{
				$list[$k]['is_ad'] = "";
			}
			if($v['az_office'] == 1){
				$list[$k]['az_office'] = "官方&nbsp;";
			}else{
				$list[$k]['az_office'] = "";
			}
			if($v['az_safe'] == 1){
				$list[$k]['az_safe'] = "安全&nbsp;|";
			}else if($v['az_safe'] == 2){
				$list[$k]['az_safe'] = "不安全&nbsp;|";
			}else{
				$list[$k]['az_safe'] = "";
			}
			if($v['az_ad'] == 1){
				$list[$k]['az_ad'] = "有广告&nbsp;|";
			}else if($v['az_ad'] == 2){
				$list[$k]['az_ad'] = "无广告&nbsp;|";
			}else{
				$list[$k]['az_ad'] = "";
			}	
		}
		return $list;
	}
	function get_exp($where){
		if($_GET['type'] == 'add'){
			$table = 'cj_soft_add';
		}else{
			$table = 'cj_soft_update';
		}		
		if($_GET['status'] == 2){
			$order_str = "review_time desc";
		}else{
			$order_str = "ignore_tm desc";
		}
		//分页		
		import('@.ORG.Page2');	
		$total = $_GET['count'];
		$p = isset($_GET['pp']) ? $_GET['pp'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 1000;	
		$totalPages = ceil($total/$limit);
		if($p == 1){
			$firstRow = 0;
		}else{
			$firstRow = ($p-1) * $limit;
		}
		$list = $this->table($table)->where($where)->order($order_str)->limit($firstRow.','.$limit)->select();
		if (!isset($_GET['fid'])) {
			$fid = uniqid();
		} else {
			$fid = $_GET['fid'];
		}
		mkdir('/tmp/export/', 0755, true);
		$file = '/tmp/export/'. session_id(). '_'.$fid.'export'. ".csv";
		$file = fopen($file, 'a');
		if($p ==1){
			fwrite($file,chr(0xEF).chr(0xBB).chr(0xBF)); 
			if($_GET['status'] ==3){
				$heade = array('软件名称','包名','下载量','版本','软件分类','来源网站','忽略时间','忽略原因');		
			}else if($_GET['status'] ==2){
				$heade = array('软件名称','包名','下载量','版本','软件分类','来源网站','审核流程','入库时间','备注');
			}
			fputcsv($file, $heade);
		}	
		foreach($list as $k => $v){
			$put_arr = array();
			$put_arr['softname'] = $v['softname'] ? $v['softname'] : "\t";
			$put_arr['package'] = $v['package'] ? $v['package'] : "\t";
			if($_GET['type'] == 'add'){
				$put_arr['download_count'] = $v['download_count'] ? number_format($v['download_count']) : "0";
			}else{
				$put_arr['az_downloaded'] = $v['az_downloaded'] ? number_format($v['az_downloaded']) : "0";
			}
			$version_code = $v['version_code'] ? $v['version_code'] : "";
			$version_name = $v['version_name'] ? $v['version_name'] : "";
			$put_arr['version'] = $version_code."(".$version_name.")\t";
			if($_GET['type'] == 'add'){
				$put_arr['category_name'] = $v['category_name'] ? $v['category_name'] : "\t";
			}else{
				$put_arr['az_category'] = $v['az_category'] ? $v['az_category'] : "\t";
			}
			$put_arr['appfrom'] = $v['appfrom'] ? $v['appfrom'] : "\t";
			if($_GET['status'] ==3){
				$put_arr['ignore_tm'] = $v['ignore_tm'] ? date("Y-m-d H:i:s",$v['ignore_tm']) : "\t";
				$put_arr['ignore_contents'] = $v['ignore_contents'] ? str_replace("<br />","",$v['ignore_contents']) : "\t";
			}else{
				if($v['examine_type'] == 1){
					$examine_type = "普通审核";
				}else if($v['examine_type'] == 2){
					$examine_type = "快速审核";
				}else{
					$examine_type = "免审";
				}
				$put_arr['examine_type'] = $examine_type;
				$put_arr['review_time'] = $v['review_time'] ? date("Y-m-d H:i:s",$v['review_time']) : "\t";
				$put_arr['remark'] = $v['remark'] ? $v['remark'] : "\t";
			}
			fputcsv($file, $put_arr);				
		}
		fclose($file);	
		$next_page = $p + 1;
		if ($p != $totalPages) {
			$par = $_GET;
			unset($par['pp'],$par['fid'],$par['button'],$par['__hash__']);
			$param = http_build_query($par);
			$needle = array('=','&');
			$param = str_replace($needle,'/',$param);
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Caiji/Collection/collection_export/pp/{$next_page}/fid/{$fid}/{$param}",
			);
		} else {	
			if($_GET['begintime'] && $_GET['endtime']){
				$name = $_GET['begintime']."~".$_GET['endtime'];
			}else{
				$name = date("Y-m-d H:i",time());
			}
			if($_GET['status'] == 2){
				$name_str = "采集后台_已入库_".$name;
			}else{
				$name_str = "采集后台_已忽略_".$name;
			}
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Dev/User/pub_getfile/fid/{$fid}/name/{$name_str}",
			);	
		}
		return $data;		
	}
	function get_update_website_config($table,$field='*'){
		$ret = $this->table($table)->field($field)->order('priority asc')->group('website_name')->select();
		$ret[] = array('website_name'=>'搜索失败');
		$ret[] = array('website_name'=>'搜索缺乏');
		$ret[] = array('website_name'=>'taptap');
		if($table=='cj_update_website_config'){
			$ret[] = array('website_name'=>'百度api');
		}
        return $ret;
	}
    
    // 每个网站的配置
    function getAddConfig($id = 0) {
        $where = array(
            'status' => 1,
        );
        if ($id) {
            $where['id'] = $id;
        }
        $list = $this->table('cj_add_package_website_config')->where($where)->select();
        $model = M();
        foreach ($list as $key => $record) {
            // 将用户id转成用户名称
            $edit_limit_os_id = $record['edit_limit_os_id'];
            $find = $model->table('sj_admin_users')->where(array('admin_user_id'=>$edit_limit_os_id))->find();
            $list[$key]['edit_limit_os_name'] = $find['admin_user_name'];
			//网站更新时间为Y-m-d
			$list[$key]['website_up_tm'] = $record['website_update_time_limit'] ? date('Y-m-d',$record['website_update_time_limit']) : "";
            // 将category_limit前后trim掉,再展示
            $list[$key]['category_limit'] = trim($list[$key]['category_limit'], ',');
        }
        return $list;
    }
    
	//新增网站分类的配置
	function getAddCategoryConfig($id=0)
	{
		$where = array(
            'status' => 1,
        );
		if ($id) 
		{
            $where['id'] = $id;
        }
        $list = $this->table('cj_add_category_config')->where($where)->select();
        $model = M();
        foreach ($list as $key => $record) {
            // 将用户id转成用户名称
            $edit_limit_os_id = $record['edit_limit_os_id'];
            $find = $model->table('sj_admin_users')->where(array('admin_user_id'=>$edit_limit_os_id))->find();
            $list[$key]['edit_limit_os_name'] = $find['admin_user_name'];
			
			//网站更新时间为Y-m-d
			$list[$key]['website_up_tm'] = $record['website_update_time_limit'] ? date('Y-m-d',$record['website_update_time_limit']) : "";
            // 将category_name前后trim掉,再展示
            $list[$key]['category_name'] = trim($list[$key]['category_name'], ',');
			//根据网站来源id读取名字
			$website_name = $this ->table('cj_add_package_website_config')->where(array('id'=>$record['website_id']))->field("website_name")->find();
			$list[$key]['website_name']=$website_name['website_name'];
        }
        return $list;
	}
    // 搜索失败配置
    function getSearchFailAddConfig() {
        $where = array(
            'config_code' => 'SEARCH_FAIL_ADD_CONFIG',
            'status' => 1,
        );
        $find = $this->table('cj_scan_add_config')->where($where)->find();
        $config_content = json_decode($find['config_content'], true);
        $find['download_limit'] = $config_content['download_limit'];
        $find['category_limit'] = $config_content['category_limit'];
        // 将用户id转成用户名称
        $model = M();
        $ret = $model->table('sj_admin_users')->where(array('admin_user_id'=>$find['os_id']))->find();
        $find['os_name'] = $ret['admin_user_name'];
        // 将category_limit前后trim掉,再展示
        $find['category_limit'] = trim($find['category_limit'], ',');
        return $find;
    }
    
    // 包名限制
    function getPackageNameConfigList($config_code) {
        $where = array(
            'config_code' => $config_code,
            'status' => 1,
        );
        $find = $this->table('cj_scan_add_config')->where($where)->find();
        $find['config_content'] = trim($find['config_content'], ',');
        // 将用户id转成用户名称
        $model = M();
        $ret = $model->table('sj_admin_users')->where(array('admin_user_id'=>$find['os_id']))->find();
        $find['os_name'] = $ret['admin_user_name'];
        return $find;
    }

    function getPackageNameIncludeExcludeListCount($where) {
        $count = $this->table('cj_packagename_include_exclude')->where($where)->count();
        return $count;
    }

    function getPackageNameIncludeExcludeList($where, $start, $limit) {
        $list = $this->table('cj_packagename_include_exclude')->where($where)->limit("{$start},{$limit}")->select();
        return $list;
    }
    
    function saveAddConfig($where, $data) {
        $ret = $this->table('cj_add_package_website_config')->where($where)->save($data);
        return $ret;
    }
    
    function saveSearchFailAddConfig($where, $data) {
        $ret = $this->table('cj_scan_add_config')->where($where)->save($data);
        return $ret;
    }
    
    function savePackageNameConfig($where, $data) {
        $ret = $this->table('cj_scan_add_config')->where($where)->save($data);
        return $ret;
    }
    
    function addIncludeExcludeKeyword($data) {
        $ret = $this->table('cj_packagename_include_exclude')->add($data);
        return $ret;
    }

    function editIncludeExcludeKeyword($where, $data) {
        $ret = $this->table('cj_packagename_include_exclude')->where($where)->save($data);
        return $ret;
    }

    function getIncludeExcludeKeyword($id) {
        $find = $this->table('cj_packagename_include_exclude')->where(array('id'=>$id))->find();
        return $find;
    }

    //黑名单
    function get_black_list($get,$type=1){

        if(isset($get['zhuangtai'])&&$get['zhuangtai']!=-1)
        {
            $where['type'] = array('eq',$get['zhuangtai']);
        }

        if(strlen($get['ssoftname'])>0)
        {
            $where['softname'] = array('like', '%'.$get['ssoftname'].'%');
        }

        if(strlen($get['ppackage'])>0)
        {
            //$where['package'] = array('like', '%'.$get['package'].'%');
            $where['package'] = array('eq',$get['ppackage']);
        }

        $down_str = $get['down_str']*10000;
        $down_end = $get['down_end']*10000;
        if(strlen($get['down_str'])>0)
        {
            if(strlen($get['down_end'])>0)
            {
                $where['download_count']  = array('between',''.$down_str.','.$down_end);
            }else
            {
                $where['download_count']  = array('egt',$down_str);
            }
        }else if(strlen($get['down_end'])>0)
        {
            $where['download_count']  = array('elt',$down_end);
        }

        $order = 'create_tm desc';

        if($type==2)
        {
            $rs = $this->table('cj_black_list')->field('*')->where($where)->order($order)->select();
            $res = array(
                'list'=>$rs,
            );
        }else
        {
            import("@.ORG.Page");
            $count = $this->table('cj_black_list')->where($where)->count();
            $page = new Page($count, 10);
            $rs = $this->table('cj_black_list')->field('*')->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
            //echo $this->getlastsql();
            $page->setConfig('header','条记录');
            $page->setConfig('first','<<');
            $page->setConfig('last','>>');
            $show =$page->show();    
            $res = array(
                'list'=>$rs,
                'page'=>$show,
                'count'=>$count,
            );
        }
        return $res;
    }

    function getblackinfo($id) {
        $find = $this->table('cj_black_list')->where(array('id'=>$id))->find();
        return $find;
    }

    function save_black($id,$data){
        return $this->table('cj_black_list')->where('id = '.$id)->save($data);
    }

    function add_black($data){
        return $this->table('cj_black_list')->add($data);
    }    

    function delblack($id){
        return $this->table('cj_black_list')->where('id='.$id)->delete();
    }

    //批量导入操作DB
    function importadd($data){
        $model = new model();
        $ret='';
        foreach($data as $v)
        {
            $data['package'] = iconv('gbk','utf-8',$v[1]);
            //$res = $model->table('sj_soft')->field('total_downloaded')->where('status=1 and hide=1 and package="'.$v[0].'"')->find();
            //$data['download_count'] = $res['total_downloaded'];//根据包名查一个下载量
            $data['softname'] = iconv('gbk','utf-8',$v[0]);
            $data['desc'] = iconv('gbk','utf-8',$v[2]);
            $data['create_tm'] = time();
            $ret.=',';
            $ret.=$this->table('cj_black_list')->add($data);
        }
    }

    //获取采集软件的黑名单
    function get_cj_black_list($pkg){
            return $res = $this->table('cj_black_list')->where(array('package'=>$pkg))->find();
    }

    //获取待采集列表的人工添加
    function get_standby_infobyrg($pkg){
            return $res = $this->table('cj_standby_fetch')->where(array('package'=>$pkg,'status'=>1,'package_from'=>2))->find();
    }    

    //检查包名是否存在于合作软件
    function get_fromcoop($pkg){
            return $res = $this->table('cj_soft_coop')->where('package="'.$pkg.'" and sync_from !="闪屏-通过"')->find();
    }
	//验证是否是忽略数据
	function check_is_ignore($table,$pkg,$code){
		$where = array(
			'package' => $pkg,
			'version_code' => $code,
			'status' => 3
		);
		$ret = $this->table($table)->where($where)->field('package')->find();
		if($ret){
			return 1;
		}else{
			return 0;	
		}
	}
	//获取采集截图
	function get_cj_thumb($softid){
		$where = array(
			'softid' => $softid,
			'status' => 1
		);	
		$thumb_list = $this->table('cj_soft_add_thumb')->where($where)->field('screenshot_path,rank')->order('rank asc')->select();
		return $thumb_list;
	}

    //全局搜索根据软件名称和包名 获取包名范围
    function globalsearchlike($softname)
    {
        $data = array();
        $rs_add = $this->table('cj_soft_add')->field('package')->where('softname like "%'.$softname.'%" and status in (1,2,3)')->select();
        if(!is_array($rs_add)){
            $rs_add= array();
        }

        $rs_update = $this->table('cj_soft_update')->field('package')->where('softname like "%'.$softname.'%" and status in (1,2,3)')->select();
        if(!is_array($rs_update)){
            $rs_update = array();
        }
        $rs = array_merge($rs_add,$rs_update);

        $rs_black = $this->table('cj_black_list')->field('package')->where('softname like "%'.$softname.'%"')->select();
        if(!is_array($rs_black)){
            $rs_black = array();
        }
        $rs = array_merge($rs,$rs_black);

        $rs_coop = $this->table('cj_soft_coop')->field('package')->where('softname like "%'.$softname.'%"')->select();
        if(!is_array($rs_coop)){
            $rs_coop = array();
        }
        $rs = array_merge($rs,$rs_coop);

        $rs_fetch = $this->table('cj_standby_fetch')->field('package')->where('softname like "%'.$softname.'%"')->select();
        if(!is_array($rs_fetch)){
            $rs_fetch = array();
        }
        $rs = array_merge($rs,$rs_fetch);
        foreach($rs as $k=>$v)
        {
            $data[$k] = $v['package'];
        }
        return array_unique($data);
    }

    function globalsearch($package)
    {
        //$package = 'com.dotalk.snsfree';
        //$package = 'com.tencent.obadkiller';
        $data = array();
        $rs_add = $this->table('cj_soft_add')->where('package="'.$package.'" and status in (1,2,3)')->select();

        $rs_update = $this->table('cj_soft_update')->where('package="'.$package.'" and status in (1,2,3)')->select();

        $rs_black = $this->table('cj_black_list')->where('package="'.$package.'"')->find();

        $rs_coop = $this->table('cj_soft_coop')->where('package="'.$package.'"')->find();

        $rs_fetch = $this->table('cj_standby_fetch')->where('package="'.$package.'" and status=1')->find();

        if(!empty($rs_add))
        {
            foreach($rs_add as $v)
            {
                if($data[$package]['version_code']==$v['version_code'])
                {
                    $data[$package]['appfrom'] = $data[$package]['appfrom'].':'.$v['appfrom'];
                    $data[$package]['add_status'] = $data[$package]['add_status'].$v['status'];
                }else if($data[$package]['version_code']>$v['version_code'])
                {
                    continue;
                }else if($data[$package]['version_code']<$v['version_code'])
                {
                    unset($data[$package]);
                    $data[$package]['version_code'] = $v['version_code'];
                    $data[$package]['appfrom'] = $v['appfrom'];
                    $data[$package]['add_status'] = $v['status'];
                }

                $data[$package]['softname'] = $v['softname'];
                $data[$package]['version_name'] = $v['version_name'];
                $data[$package]['icon_path'] = $v['icon_path'];
            }
        }

        if(!empty($rs_update))
        {
            foreach($rs_update as $v)
            {
                if($data[$package]['version_code']==$v['version_code'])
                {
                    $data[$package]['appfrom'] = $data[$package]['appfrom'].':'.$v['appfrom'];
                    $data[$package]['update_status'] = $data[$package]['update_status'].$v['status'];
                }else if($data[$package]['version_code']>$v['version_code'])
                {
                    continue;
                }else if($data[$package]['version_code']<$v['version_code'])
                {
                    unset($data[$package]);
                    $data[$package]['version_code'] = $v['version_code'];
                    $data[$package]['appfrom'] = $v['appfrom'];
                    $data[$package]['update_status'] = $v['status'];
                }

                $data[$package]['softname'] = $v['softname'];
                $data[$package]['version_name'] = $v['version_name'];
                $data[$package]['az_category'] = $v['az_category'];
                $data[$package]['examine_type'] = $v['examine_type'];
                $data[$package]['icon_path'] = $v['icon_path'];
            }
        }

        if(!empty($rs_fetch))
        {
            $data[$package]['softname'] = $rs_fetch['softname'];
            $data[$package]['fetch_status']  = 1;
        }
        if(!empty($rs_black))
        {
            $data[$package]['softname'] = $rs_black['softname'];
            $data[$package]['black_status']  = 1;
        }   
        if(!empty($rs_coop))
        {
            $data[$package]['softname'] = $rs_coop['softname'];
            $data[$package]['coop_status']  = 1;
        }
        //print_r($data);
        //exit;
        
        return $data;
    }        
}
