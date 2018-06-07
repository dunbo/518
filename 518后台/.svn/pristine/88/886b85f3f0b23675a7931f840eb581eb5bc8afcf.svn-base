<?php
class MessageAction extends CommonAction {
    //合作软件
    public function cooper_soft(){
        $model = M('');
        $whitelist = $this->get_whitelist_soft();
        $package = $whitelist['package'];
        $this->assign('whitelist',$whitelist['abc_list']);
		
        $search = $model->table('sj_coopersoft_search')->where(array('admin_id'=>$_SESSION['admin']['admin_id']))->order('add_tm desc')->limit(3)->select();
        $this->assign('search',$search);
        $this->assign('sub_level',$_GET['sub_level']);
        //保存筛选条件及处理条件
        if(!empty($_GET['sub_pack'])){
            $res = $model->table('sj_coopersoft_search')->data(array('admin_id'=>$_SESSION['admin']['admin_id'],'package'=>$_GET['sub_pack'],'add_tm'=>time()))->add();
            $sub_pack = explode(',',$_GET['sub_pack']);
            $this->assign('sub_pack',$_GET['sub_pack']);
            $this->assign('sub_id',$_GET['sub_id']);
            array_pop($sub_pack);
        }else{
            $sub_pack = $package;
        }
        
        $package_str = '';
        foreach($sub_pack as $key=>$val){
            $package_str .= $val.",";
        }
        $package_str = substr($package_str,0,-1);
        //评测级别
        if(!empty($_GET['sub_level'])){
            $level_str = '';
            $level = explode(',', $_GET['sub_level']);
            
            array_pop($level);
            $level_arr = array('1'=>'A','2'=>'B','3'=>'C','4'=>'D','5'=>'S','6'=>'B-','7'=>'B+','8'=>'A+','9'=>'A-');
            foreach($level as $key=>$val){
                if($val!='10'){
                    $level[$key] = $level_arr[$val];
                    
                }else{
                    $is_null = true;
                    unset($level[$key]);
                }
            }
            
            foreach($level as $key=>$val){
                $level_str .= "'".$val."',";
            }
            $level_str = substr($level_str,0,-1);

        }
        if(!$level_str){
            $level_str = 1;
        }
		$package_str = ''; //测试用的 上线必须去掉
        $this->message_soft($package_str,$from='cooper_soft',$level_str,$is_null);
        
    }
    
    //获取白名单软件
    public function get_whitelist_soft(){
        $model = M('soft_whitelist');
        $list = $model->field('id,softname,package')->where('status = 1')->select();
        foreach($list as $key=>$val){
            $first_char = strtoupper(substr(Pinyin(trim($val['softname'])),0,1));
            $list[$key]['abc'] = $first_char;
        }
        $abc_arr = array('other','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $abc_list = array();
        $package = array();
        foreach($list as $key=>$val){
            if(in_array($val['abc'], $abc_arr)){
                $abc_list[$val['abc']][] = $list[$key];
            }else{
                $abc_list['other'][] = $list[$key];
            }
            $package[] = $val['package'];
        }
        return array('abc_list'=>$abc_list,'package'=>$package);
    }
    
    
    public function message_soft($package_str,$from='message_soft',$level='',$is_null=false) {
		$comment_model = D('Dev.Comment');
		$modelpost = M("post");
		$model = D("Dev.Message");
		$param = $_GET;
		if(!$param){
			$_POST['type'] = 1;
			$_POST['content'] = '';
			$this->pub_set_content();
		}
		unset($param['is_replace'],$param['processed'],$param['from_processed'],$param['all_post'],$param['__hash__'],$param['p'],$param['lr']);
		$param = http_build_query($param);
		$this->assign('param',$param);
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		if(C('MESSAGE_CONFIG') != 1){
			//切换到从库查询
			list($total,$Page,$comment_list) = $this -> old_message_soft($package_str, $from, $level='',$is_null=false);
		}else{
			//使用接口查询
			//默认起止时间
			if (empty($_GET['begintime']) && empty($_GET['endtime'])) {
				$day = strtotime(date("Y-m-d"));
				$_GET['begintime'] = strtotime(date('Y-m-d H:i:s', $day-7*86400));
				$_GET['endtime'] = strtotime(date('Y-m-d H:i:s', $day+86399));
			}else{
				$_GET['begintime'] = strtotime($_GET['begintime']);
				$_GET['endtime'] = strtotime($_GET['endtime']);
			}		
			$w_arr = array(
				'softid','package','imei','ipmsg','user_name','content','pid','beginscore','endscore','hot','admin_name','denymsg','choose_type','feature_name','everybody_say'
			);
			$is_two  = 0;
			foreach($w_arr as $v){
				if(isset($_GET[$v])){
					$is_two  = 1;
				}
			}
			if($is_two ==0 && ($_GET['endtime']-$_GET['begintime']) > 90*86400  ){
				$this -> error('时间区间超过三个月请多加一个条件搜索！');
			}
			if(!isset($_GET['processed']) && !$_GET['all_post']){
				$_GET['processed'] =   0 ;
			}			
			foreach($_GET as $k => $v){
				if($k == 'begintime' || $k == 'endtime'){
					$v = date('Y-m-d H:i:s',$v);
				}
				$this->assign($k,$v);		
			}
			$_GET['status'] = $from == 'del_soft_list' ? 0 : 1;
			if($from != 'del_soft_list'){
				$_GET['admin_show'] = 1;
			}
			if($package_str){
				$_GET['packages'] = $package_str;
			}
			$_GET['page'] = $_GET['p'] ? $_GET['p'] : 1;
			$_GET['limit'] = 10;
			//分页
			import("@.ORG.Page2");
			$res = $model->get_show_data($_GET);
			$total = $res['total'];
			$where = array('id'=>array('in',$res['data']));
			$comment_list = $comment_model->table('sj_soft_comment')->where($where)->order('create_time desc')->select();
			$Page=new Page($total, $_GET['limit']);		
		}
		$did = array();
		$softidkey = array();
		$where = array();
		$package = array();
		$userid = array();
		$comment_id = array();
		foreach ($comment_list as $key => $value){                                                        
			if($value['did']) $did[] = $value['did'];
			if($value['comment_type'] ==1)
			{
				$softidkey[$value['softid']] = 1;
			}
			elseif($value['comment_type'] ==2)
			{
				$featureidkey[$value['softid']] = 1;
			}
			if($value['package']) $package[] = $value['package']; 
			$userid[] = $value['userid'];     
			$comment_id[] = $value['id'];	
		}
		if($did){
			$where = array(	'did' => array('in',$did));
			$dinfo = get_table_data($where,"pu_device","did","did,dname");
			$this->assign('dinfo', $dinfo);
		}
		if($from == 'cooper_soft' && $package){
			$where = array(
				'package' => array('in',$package)
			);
			$test_level = get_table_data($where,"yx_product","soft_id","soft_id,reviewlevel","soft_id asc");			
		}
		$file_info = array();
		$softinfo = array();
		if (!empty($softidkey)) {
			$where = array(
				'softid' => array('in',array_keys($softidkey))
			);
			$file_info = get_table_data($where,"sj_soft_file","softid","softid,iconurl","softid asc");	
			$softinfo = get_table_data($where,"sj_soft","softid","softname,softid,version, version_code,hide","softid asc");	
			foreach ($softinfo as $k => $v) {
				$softinfo[$k]['iconurl'] = $file_info[$k]['iconurl'];
			}
		}
		if(!empty($featureidkey))
		{
			//专题名称
			$where_feature=array(
				'feature_id' => array('in',array_keys($featureidkey)),
			);
			$featureinfo = get_table_data($where_feature,"sj_feature","feature_id","feature_id,name");
		}
		if($comment_id){
			$where = array(
				'comment_id' => array('in',$comment_id)
			);			
			$postres_again = get_table_data($where,"sj_post","comment_id","comment_id,dateline, contents,user_type,system_userid,dev_id","pid asc");	
		}
		 $adminid = array();
		 $dev_id = array();
		 $replace_id = array();
		//回复--读取状态--处理状态
		foreach ($comment_list as $key => $value) {			
			$comment_list[$key]['again_reply'] = $postres_again[$value['id']]['contents'];
			$comment_list[$key]['again_reply_tm'] = $postres_again[$value['id']]['dateline'];
			$comment_list[$key]['user_type'] = $postres_again[$value['id']]['user_type'];
			$comment_list[$key]['dev_id'] = $postres_again[$value['id']]['dev_id'];
			$dev_id[] = $postres_again[$value['id']]['dev_id'];
			$replace_id[]=$value['id'];
			if($from != 'del_soft_list' && $postres_again[$value['id']]['system_userid']){
				$value['update_user_id'] = $postres_again[$value['id']]['system_userid'];
			}
			$comment_list[$key]['reviewlevel'] = $test_level[$value['softid']]['reviewlevel'];
            $comment_list[$key]['update_user_id'] = $value['update_user_id'];
			if($value['update_user_id']) $adminid[] = $value['update_user_id'];
		}
		$this->assign('softinfo', $softinfo);	
		$this->assign('featureinfo', $featureinfo);		
		$devid_arr = array_unique(array_merge($userid,$dev_id));
		//开发者账号
		if($devid_arr){
			$where = array(	'userid' => array('in',$devid_arr)	);
			$dev = get_table_data($where,"pu_user","userid","userid,user_name");
			$this->assign('dev', $dev);
		}
		if($replace_id){
			$where = array(	'c_p_id' => array('in',$replace_id),'type'=>array('exp','&2=2'),'c_type'=>1	);
			$replace_data = get_table_data($where,"sj_soft_comment_extent","c_p_id","id,c_p_id");
			$this->assign('replace_data', $replace_data);
		}

		//后台用户
		if($adminid){
			$username = array();
			$where = array(	'admin_user_id' => array('in',$adminid)	);
			$username = get_table_data($where,"sj_admin_users","admin_user_id","admin_user_id,admin_user_name");
			$this->assign('username',$username);	
		}	
		$this->assign('commentlist',$comment_list);	
		$this->assign('page',$Page->show());
		$this->assign('total',$total);
		//来源	
		if(!S('product')){
			$util = D("Sj.Util");
			$product = $util -> getProducts(array('pid'=>$_GET['pid']));
			S('product',$product,24*3600);
		}else{
			$product = S('product');
		}
		$this->assign('product', $product);	
		//热门配置
		if(!S('configcontent')){
			$conf = $comment_model->table('pu_config')->where(" `config_type` = 'hot_comments_config'")->field("configcontent")->find();
			$conf = json_decode($conf['configcontent'],true);
			S('configcontent',$conf,12*3600);
		}else{
			$conf = S('configcontent');
		}
		$this->assign('conf', $conf);
		$_POST['type'] = 1;
		if($this->pub_get_content(1)!=''){
			$this->assign('replace_button',1);
		}
		if($from=="cooper_soft"){
			$this->assign('from','cooper');
		}else if($package_str&&$from=='message_soft'){
			$this->assign('from','own');
		}else{
			$this->assign('from','message');
		}
		
        $this->display($from);

    }

	public function show_replace_button(){
		if(!empty($_GET['contents'])||!empty($_GET['content'])){
			$replace_button = 1;
			$this->assign('replace_button',$replace_button);
		}
	}
	public function old_message_soft($package_str,$from='message_soft',$level='',$is_null=false,$rids=''){
		// if($_GET['is_replace']){
		// 	unset($_GET['processed']);
		// 	unset($_GET['all_post']);
		// }else{
		// 	unset($_GET['is_replace']);
		// }
		//$comment_model = M('soft_comment');
		$comment_model = D('Dev.Comment');
		$message_model = D('Dev.Message');
		$modelpost = M("post");
		$modelthread = M("thread");
		$where = array(
			'status' => $from == 'del_soft_list' ? 0 : 1,
			'content' => array('exp'," !='' "),
			'admin_show' => 1,
		);
		$sesson_type = 'soft_comment';
		$referer = explode('/', $_SERVER['HTTP_REFERER']);
		if(in_array('Comment_reply',$referer) || $_GET['type3'] == 'delete' ){
		    $from_referer = 1; unset($_GET['type3']);
		}
 		if($from_referer){	
		    $this->getGetValfromSession($sesson_type);
		} 
		$this->setGetValfromSession($sesson_type);
		
		$this->assign('history', $_GET['history']);

		if($package_str){
			$where['package'] = array('in',$package_str);
		}else{
			//评论信息列表踢除自有产品评论的数据
			$package_arr = array('cn.goapk.market','anzhi.pad','com.azyx','com.anzhi.weixin');
			$where['package'] = array('not in',$package_arr);
		}
		$this->check_where($where, 'softid');
		$this->check_where($where, 'imei');
		if(isset($_GET['package']) && in_array(trim($_GET['package']),$package_arr)){
			$where['package'] = '';
			$this->assign('package', $_GET['package']);
		}else{
			$this->check_where($where, 'package');
		}
		$this->check_where($where, 'from_processed','isset');
		unset($_GET['from_processed']);
		$this->check_where($where, 'ipmsg');
		$this->check_where($where, 'denymsg', 'isset', 'like');
		$this->check_where($where, 'pid');
		$_POST['type'] = 1;
		$content = $this->pub_get_content(1);
		$content = str_replace('%','\%',addslashes($content));
		$where['content'] = array('like', "%{$content}%");
		//$this->check_where($where, 'content', 'isset', 'like');

		$this->check_where($where, 'user_name', 'isset', 'like');
		
		$conf = $comment_model->table('pu_config')->where(" `config_type` = 'hot_comments_config'")->field("configcontent")->find();
		$conf = json_decode($conf['configcontent'],true);
		$this->assign('conf', $conf);
		if(!empty($_GET['time'])){
    			$_GET['begintime'] =  date('Y-m-d 00:00:00',$_GET['time']);
				$_GET['endtime'] =    date('Y-m-d 23:59:59',$_GET['time']);
				
		}else{
			$day = strtotime(date("Y-m-d",time()));
    		if (empty($_GET['begintime']) && empty($_GET['endtime'])) {
	    		$_GET['begintime'] = date('Y-m-d H:i:s', $day-7*86400);
	    		$_GET['endtime'] = date('Y-m-d H:i:s', $day+86399);
	    	}
    	}
		if($from == 'del_soft_list'){
			$tm_order = 'update_time';
		}else{
			$tm_order = 'create_time';
		}
    	$this->check_range_where($where, 'begintime', 'endtime', $tm_order, true);
		if(isset($_GET['hot']) && $_GET['hot'] == 1){
			if(empty($_GET['package']) && empty($_GET['softid'])){
				$this->error('搜索热门评论必须填写包名或softid');
			}	
			$this->assign('hot', $_GET['hot']);
			$time = time();
			//XX天内发布的评论
			$hot_day = $time-$conf['hot_day']*86400;
			//回复数大于等于XX条			//赞数量大于等于XX个			//人工设置的有效天数
			$man_hot_day = $time-$conf['man_hot_day']*86400;			
			$where['_string'] = "create_time >={$hot_day} and reply_count >={$conf['reply_num']} and  like_count >= {$conf['praise_num']} or (apply_rules_time >={$man_hot_day} AND hot=1)";
		}else if(isset($_GET['hot']) && $_GET['hot'] == 0){
			if(empty($_GET['package']) && empty($_GET['softid'])){
				$this->error('搜索热门评论必须填写包名或softid');
			}
			$this->assign('hot', $_GET['hot']);
			$where['_string'] = "reply_count <={$conf['reply_num']} and hot=0 ";			
		}		
    	$this->check_range_where($where, 'beginscore', 'endscore', 'score');
		if(isset($_GET['all_post']) && $_GET['all_post'] == 1){
			$where['all_post'] = array('gt',2);
			$this->assign('all_post', $_GET['all_post']);			
		}
    	if(empty($_GET['processed'])) { 
			$where['processed'] = '0';
			$_GET['processed'] = 0;			
		}else{
			$where['processed'] = $_GET['processed'] ? $_GET['processed'] : 0;
		}


		//评论内容重复字符和长度相关查询
		list($c_p_id_arr,$comment_extent_data)=$message_model->get_comment_extent_id($_GET,1);
		if(is_array($c_p_id_arr)){
			$where['id'] = array('IN', $c_p_id_arr);
		}
		
			
		
		if($_GET['is_replace']){
			$this->assign('is_replace',$_GET['is_replace']);
			if($comment_extent_data){
				$this->assign('comment_extent_data',$comment_extent_data);
			}
		}

		if($_GET['word_num']){
			$this->assign('word_num',$_GET['word_num']);
		}
		if($_GET['repeat_num']){
			$this->assign('repeat_num',$_GET['repeat_num']);
		}


		if($_GET['admin_name']){
			$username = array();
			$where_username = array('admin_user_name' => $_GET['admin_name']);
			$subquery = $comment_model->table('sj_admin_users')->field('admin_user_id')->where($where_username)->buildSql();
			$where['update_user_id'] = array('IN', $subquery);			
			$this->assign('admin_name',$_GET['admin_name']);	
		}			
		//V6.2新增
		if(!empty($_GET['feature_name']))
		{
			$where_feature = array(
				'status' => 1,
			);
			$where_feature['name'] = array('like', '%'.escape_string($_GET['feature_name']).'%');
			$subquery = $comment_model->table('sj_feature')->field('distinct feature_id')->where($where_feature)->buildSql();
			$where['softid'] = array('IN', $subquery);
			$this->assign('feature_name', $_GET['feature_name']);
		}
		if(!empty($_GET['choose_type'])){
			$where['comment_type']= $_GET['choose_type'];
			$this->assign('choose_type', $_GET['choose_type']);
		}
		if(!empty($_GET['everybody_say'])){
			if($_GET['everybody_say']==1)
			{
				$where['_string']=" everybody_said_id != 0 ";
			}
			elseif($_GET['everybody_say']==2)
			{
				$where['_string']=" everybody_said_id = 0 ";
			}
			$this->assign('everybody_say', $_GET['everybody_say']);
		}
		
		$this->assign('processed', $_GET['processed']);
		if($_GET['all_post'] == 1){
			$where['processed'] = 0;
		}	
		if($_GET['is_replace']){
			unset($where['processed']);
			$this->assign('processed',3);
		}
        $cache_key = 'comment_'. md5(json_encode($where));
        $get_total = false;
        if (!isset($_SESSION['admin'][$cache_key])) {
            $get_total = true;
        } elseif(time() > $_SESSION['admin'][$cache_key]['add_time'] + 3600) {
            $get_total = true;
        } else {
            $total = $_SESSION['admin'][$cache_key]['total'];
        }
		if($_GET['softid'] || $_GET['comment_id'] || $_GET['imei']){
			$table = "sj_soft_comment as a";
			$table2 = "sj_soft_comment";
		}else if($_GET['package']){
			$table = "sj_soft_comment as a  force index (package_create_time)";
			$table2 = "sj_soft_comment  force index (package_create_time)";
		}else{
			$table = "sj_soft_comment as a  force index (".$tm_order.")";
			$table2 = "sj_soft_comment  force index (".$tm_order.")";
		}		
        if ($get_total) {
            if(!empty($level)){
                foreach($where as $key=>$val){
					if($key == '_string') continue;
                    $where_l['a.'.$key] = $where[$key];
                }
                 if(!empty($level)){
                        if($is_null){
                            if($level == 1){
                                $where_l['b.reviewlevel'] = array('exp','is null');
                            }else{
                                $where_l['b.reviewlevel'] = array(array('exp','in ('.$level.')'),array('exp','is null'),'or');
                            }
                            
                        }else{
                            if($level != 1){
                                $where_l['b.reviewlevel'] = array('in',$level);
                            }
                            
                        }
                    }     						
                 $total = $comment_model->table($table)->join('yx_product as b on a.package = b.package')->where($where_l)->count();
            }else{				
                $total = $comment_model->table($table2)->where($where)->count();
            }

            $_SESSION['admin'][$cache_key]['total'] = $total;
            $_SESSION['admin'][$cache_key]['add_time'] = time();
        }
        import("@.ORG.Page2");
        $Page=new Page($total, 10);
		
		if (!empty($_GET['softname'])) {
			$where_soft = array(
				'status' => 1,
				'hide' => 1,
			);
			$where_soft['softname'] = array('like', '%'.escape_string($_GET['softname']).'%');
			$subquery = $comment_model->table('sj_soft')->field('distinct package')->where($where_soft)->buildSql();
			$where['package'] = array('IN', $subquery);
			$this->assign('softname', $_GET['softname']);
		}
        if(!empty($_GET['comment_ip'])){
				$where['ipmsg']= $_GET['comment_ip'];
		}
		if(!empty($_GET['comment_id'])){
			unset($where);
			$where['id'] = $_GET['comment_id'];
		}		
        if ($from == 'del_soft_list' ) {
            unset($where['processed']);
        }
		if(!empty($level)){
			foreach($where as $key=>$val){
				if($key == '_string') continue;
				$where1['a.'.$key] = $where[$key];
			}
			if(!empty($level)){
				if($is_null){
					if($level == 1){
						$where1['b.reviewlevel'] = array('exp','is null');
					}else{
						$where1['b.reviewlevel'] = array(array('exp','in ('.$level.')'),array('exp','is null'),'or');
					}
				}else{
					if($level != 1){
						$where1['b.reviewlevel'] = array('in',$level);
					}
				}
			}                 
			$comment_list = $comment_model->table($table)
							->join('yx_product as b on a.package = b.package')
							->where($where1)->limit($Page->firstRow.','.$Page->listRows)->order($tm_order.' desc')	->field('a.*')->select();
		}else{
			$comment_list = $comment_model->table($table2)->where($where)
							->limit($Page->firstRow.','.$Page->listRows)
							->order($tm_order.' desc')
							->select();
		}
		return array($total,$Page,$comment_list);	
	}
	
	public function own_soft(){
		$package_str = "cn.goapk.market,anzhi.pad,com.azyx,com.anzhi.weixin";
		$this -> message_soft($package_str);
	}

    public function message_soft_unshow() {
		$comment_model = D('Dev.Comment');
		$where = array(
			'status' => 1,
			'content' => array('exp',"!=''"),
			'admin_show' => 0,
		);
		$this->assign('history', $_GET['history']);

		$this->check_where($where, 'softid');
		$this->check_where($where, 'imei');
		$this->check_where($where, 'package');
		$this->check_where($where, 'ipmsg');;
		$this->check_where($where, 'pid');
    	$this->check_where($where, 'content', 'isset', 'like');
    	$this->check_where($where, 'user_name', 'isset', 'like');
    	if (empty($_GET['begintime']) && empty($_GET['endtime'])) {
			$day = strtotime(date("Y-m-d",time()));
    		$_GET['begintime'] = date('Y-m-d H:i:s', $day-90*86400);
    		$_GET['endtime'] = date('Y-m-d H:i:s', $day+86399);
    	}
    	$this->check_range_where($where, 'begintime', 'endtime', 'create_time', true);
    	$this->check_range_where($where, 'beginscore', 'endscore', 'score');



        $cache_key = 'comment_'. md5(json_encode($where));
        $get_total = false;
        if (!isset($_SESSION['admin'][$cache_key])) {
            $get_total = true;
        } elseif(time() > $_SESSION['admin'][$cache_key]['add_time'] + 3600) {
            $get_total = true;
        } else {
            $total = $_SESSION['admin'][$cache_key]['total'];
        }

        if ($get_total) {
            $total = $comment_model->table('sj_soft_comment force index (create_time)')->where($where)->count();
            $_SESSION['admin'][$cache_key]['total'] = $total;
            $_SESSION['admin'][$cache_key]['add_time'] = time();
        }
        import("@.ORG.Page2");
        $Page=new Page($total, 20);
		if (!empty($_GET['softname'])) {
			$where_soft = array(
				'status' => 1,
				'hide' => 1,
			);
			$where_soft['softname'] = array('like', '%'.escape_string($_GET['softname']).'%');
			$subquery = $comment_model->table('sj_soft')->field('distinct package')->where($where_soft)->buildSql();
			$where['package'] = array('IN', $subquery);
			$this->assign('softname', $_GET['softname']);
		}

		$comment_list = $comment_model->table('sj_soft_comment force index (create_time)')->where($where)
				->limit($Page->firstRow.','.$Page->listRows)
				->order('update_time desc')
				->select();
				//echo $comment_model->getlastsql();
				
		$dkey = array();
		$softidkey = array();
		$where = array();
		foreach ($comment_list as $key => $value) {
			$dkey[$value['did']] = 1;
			$softidkey[$value['softid']] = 1;
			$ip = $value['ip_msg'];
			if($ip){
					$comment_list[$key]['ip_num']= $comment_model->table('sj_soft_comment')->where("`status` = 1  AND  `content` != ''  AND  `admin_show` = 0 and ip_msg='{$ip}'")->count();
			}
		}
		$dinfo = array();
		if (!empty($dkey)) {
			$where['did'] = array('IN', array_keys($dkey));
			$res = $comment_model->table('pu_device')->where($where)->field('did,dname')->select();
			foreach ($res as $value) {
				$dinfo[$value['did']] = $value['dname'];
			}
		}
		$this->assign('dinfo', $dinfo);

		$where = array();
		$softinfo = array();
		if (!empty($dkey)) {
			$where['softid'] = array('IN', array_keys($softidkey));
			//$where['package_status'] = 1;
			$res = $comment_model->table('sj_soft_file')->where($where)->field('softid, iconurl')->select();
			foreach ($res as $value) {
				$softinfo[$value['softid']]['iconurl'] = $value['iconurl'];
			}

			$res = $comment_model->table('sj_soft')->where($where)->field('softname,softid, version, version_code,hide')->select();
			foreach ($res as $value) {
				$softinfo[$value['softid']]['softname'] = $value['softname'];
				$softinfo[$value['softid']]['version'] = $value['version'];
				$softinfo[$value['softid']]['version_code'] = $value['version_code'];
				$softinfo[$value['softid']]['hide'] = $value['hide'];
			}
		}
		$this->assign('softinfo', $softinfo);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);			
	   	$param = http_build_query($_GET);
		$this->assign('param',$param);
		$this->assign('commentlist',$comment_list);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');		
		$this->assign('page',$Page->show());
		$this->assign('total',$total);

        $this->display();

    }
    public function showoff()
    {
		$comment_model = D('Dev.Comment');
    	$data = array(
    		'admin_show' => 0,
			'update_time' => time()
    	);
    	$id = '';
    	if (isset($_GET['id'])) {
	    	$where = array(
	    		'id' => $_GET['id']
	    	);
	    	$id = $_GET['id'];
    	} else if (isset($_GET['ids'])) {
	    	$where = array(
	    		'id' => array('IN', explode(',', $_GET['ids']))
	    	);
	    	$id = $_GET['ids'];
    	}
    	$comment_model->table('sj_soft_comment')->where($where)->save($data);
		if (isset($_GET['id'])) {
			$this->writelog('对id['.$id.']的评论进行忽略', 'sj_soft_comment', $_GET['id'],__ACTION__ ,"","edit");
		} else if (isset($_GET['ids'])) {
			$ids = explode(',', $_GET['ids']);
			foreach ($ids as $id) {
				$this->writelog('对id['.$id.']的评论进行忽略', 'sj_soft_comment', $id,__ACTION__ ,"","edit");
			}
		}
    	$this->success('忽略成功');
    }

    public function delete()
    {
		if (!empty($_POST)) {
			$comment_model = D('Dev.Comment');
			$data = array(
				'status' => 0,
				'update_time' => time(),
			);
			$id = '';
			if (!empty($_POST['id'])) {
				$where = array(
					'id' => $_POST['id']
				);
				$id = $_POST['id'];
				$res = $comment_model->table('sj_soft_comment')->where($where)->find();  
				if($res['status']  == 0){
					exit(json_encode(array('code'=>'0','msg'=>"此信息已经被删除，请不要重复提交。")));
				}
			} else if (!empty($_POST['ids'])) {
				$where = array(
					'id' => array('IN', explode(',', $_POST['ids']))
				);
				$id = $_POST['ids'];
                $res = $comment_model->table('sj_soft_comment')->where($where)->field('processed')->find();  
			}
			if (empty($_POST['rid'])){
				$data['rid'] = '';
			} else{
				$data['rid'] = implode(',', $_POST['rid']);	
			} 
			// $reject = '';
			// if (!empty($_POST['reject']) || $_POST['reject_reason'] != "请输入屏蔽原因："){ 
				// foreach($_POST['reject'] as $val){
					// if($val){
						// $reject .= trim($val);
					// }
				// }
				// if($_POST['reject_reason'] != "请输入屏蔽原因："){
					// $reject1 = "\n".$_POST['reject_reason'];
				// }
				// $data['denymsg'] = $reject;
			// } 
			if (!empty($_POST['content'])){ 
				$reject = $_POST['content'];
				$data['denymsg'] = $reject;
			}
			
			$data['update_user_id'] = $_SESSION['admin']['admin_id'];
			$comment_model->table('sj_soft_comment')->where($where)->save($data);
			if (!empty($_POST['id'])) {
                if($res['processed']==1){
                    $this->writelog('对id['.$_POST['id'].']的评论进行删除，原因:'.$reject, 'sj_soft_comment', $id,__ACTION__,'processed','del');
                }else{
                    $this->writelog('对id['.$_POST['id'].']的评论进行删除，原因:'.$reject, 'sj_soft_comment', $id,__ACTION__,'','del');
                }
				
			} else if (!empty($_POST['ids'])) {
				$ids = explode(',', $_POST['ids']);
				foreach ($ids as $id) {
                    if($res['processed']==1){
                        $this->writelog('对id['.$id.']的评论进行删除，原因:'.$reject, 'sj_soft_comment', $id,__ACTION__,'processed','del');
                    }else{
                        $this->writelog('对id['.$id.']的评论进行删除，原因:'.$reject, 'sj_soft_comment', $id,__ACTION__,'','del');
                    }
					
				}
			}
			
			foreach ($_SESSION['admin'] as $key) {
				if (preg_match('/^comment_[a-z0-9]{32}$/', $key)) {
					unset($_SESSION['admin'][$key]);
				}
			}
			exit(json_encode(array('code'=>'1','msg'=>"删除成功",'return_url'=>$_SERVER['HTTP_REFERER'])));
		} else {
			$model = new Model();
			$where = array(
				'status' => 1,
				'reason_type' => 7,
			);
			$reasons = $model->table('dev_reason')->where($where)->order('pos')->select();
			foreach($reasons as &$val){
				if($val['content2']){
					$val['content2'] = explode('<br />', $val['content2']);
				}
			}
			$this->assign('reasons', $reasons);
			$this->assign('id', $_GET['id']);
			$this->assign('ids', $_GET['ids']);
			$this->assign('callback', $_GET['callback']);
			$this->assign('act', 'delete');
			$this->display();
		}
    }

    public function addCommentBlock()
    {
    	$model = new Model();
    	$where = array(
    		'status' => 1,
    		'reason_type' => 6,
    	);
    	$reasons = $model->table('dev_reason')->where($where)->order('pos')->select();
    	foreach($reasons as &$val){
    	    if($val['content2']){
    	        $val['content2'] = explode('<br />', $val['content2']);
    	    }
    	}
    	$this->assign('reasons', $reasons);
    	$this->assign('ip', $_GET['ip']);
    	$this->assign('imei', $_GET['imei']);
		if($_GET['user'] == 'GOAPKGFUSER_@'){
			$_GET['user'] = "安智网友";
		}
    	$this->assign('user', $_GET['user']);
    	$this->display();
    }


	public function listBadFilter()
	{
		$filterDesc = array(
			1 => 'IP',
			2 => 'IMEI',
			3 => '用户名',
		);
		$model = M('admin_filter');
		$where = array();

		if (isset($_GET['type'])) {
			switch ($_GET['type']) {
				case '1':			
				    $where['begintime']  = array('exp',' <= '.time().' and endtime >= '.time());	
				    $where['limit_time'] = 0;
					$where['_logic'] = 'or';					
					break;				
				case '2':
					$where['endtime'] = array('elt', time());
					$where['limit_time'] = 1;
					break;
				case '3':
					$where['begintime'] = array('egt', time());
					$where['limit_time'] = 1;
					break;
			}
			$this->assign('type', $_GET['type']);
		}

		$this->check_where($where, 'source_type');
		$this->check_where($where, 'source_value');
		$this->check_range_where($where, 'fromdate', 'todate', 'addtime', true);
		if(!$where['_logic']){
			$where['_logic'] = 'and';
		}
		$count = $model
			->table('sj_bad_filter')
			->where($where)
			->count();
		$this->assign('total', $count);
		import("@.ORG.Page2");
        $Page=new Page($count,15);
       
		$black_list = $model
			->table('sj_bad_filter')
			->where($where)
			->limit($Page->firstRow.','.$Page->listRows)
			->order('addtime desc')->select();
		//echo $model->getlastsql();
    	$where = array(
    		'status' => 1,
    		'reason_type' => 6,
    	);
    	$res = $model->table('dev_reason')->where($where )->select();
    	$reasons = array();
    	foreach ($res as $key => $value) {
    		# code...
    		$reasons[$value['id']] = $value['content'];
    	}
    	$now = time();
    	foreach ($black_list as $key => $value) {
    		# code...
    		if (!empty($value['rid'])) {
    			$rids = explode(',', $value['rid']);
    			$str = '';
    			foreach ($rids as $v) {
    				# code...
    				$str .= "\n". $reasons[$v];
    			}
    			$black_list[$key]['reason'] .= $str;
    		}

    		if (!empty($value['begintime']) && !empty($value['endtime'])) {
    			if ($value['begintime'] > $now) {
    				$black_list[$key]['time_status'] = '未开始';
    			} elseif ($value['endtime'] < $now) {
    				$black_list[$key]['time_status'] = '过期';
    			} else {
    				$black_list[$key]['time_status'] = '正常';
    			}
    		}
    		if($value['limit_time'] == 0){
    		    $black_list[$key]['time_status'] = '正常';
    		}
    	}
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');			
    	$this->assign('page', $Page->show());
		$this->assign('black_list', $black_list);	
		$this->assign('filterDesc', $filterDesc);
		$this->display();
	}
	public function addBadFilter()
	{
		$model = M('bad_filter');
		if (!empty($_POST)) {
			$source_type =  $_POST['source_type'];
			$ip = $_POST['ip_add'];
			$imei = $_POST['imei_add'];
			$user = $_POST['user_add'];
			foreach($source_type as $val){
				$where['source_type'] = $val;
				if($val == 1){
					$where['source_value'] = $ip;
				}elseif($val == 2){
					$where['source_value'] = $imei;
				}elseif($val == 3){
					$where['source_value'] = $user;
				}
				$tm = time();
				$where['begintime'] = array('exp',"<={$tm}");
				$where['endtime'] = array('exp',">={$tm}");
				$ret = $model->where($where)->find();
				if($ret){
					$this -> error('已经有该记录了请不要重复添加');
				}
				$map = array();
				$map['source_type'] = $val;
				if($val == 1){
					if(!empty($ip)){
						$map['source_value'] = $ip;
					}else{
						$this->error('请填写正确的ip值');
					}
				}elseif($val == 2){
					if(empty($imei)){
						$this->error('请填写正确的imei值');
					}
					$map['source_value'] = $imei;
				}elseif($val == 3){
					if(empty($user)){
						$this->error('请填写正确的用户名');
					}
					$map['source_value'] = $user;
				}
				$map['limit_time'] = $_POST['limit_time'];
				$map['addtime'] = time();
				if ($map['limit_time'] == 1) {
					$map['begintime'] = strtotime($_POST['begintime_pop'] . ' 00:00:00');
					$map['endtime'] = strtotime($_POST['endtime_pop'] . ' 23:59:59');
				}
				$map['reason'] = $_POST['reason'];
				
				if (empty($_POST['rid'])){
					$map['rid'] = '';
				} else{
					$map['rid'] = implode(',', $_POST['rid']);	
				}
				
				$id = $model->add($map);
				$this->writelog('添加id['.$id.']的评论屏蔽', 'sj_bad_filter', $id,__ACTION__,'','add');
			}
			$this->success('成功');
		} else {
			$this->display();
		}
	}
	public function editBadFilter()
	{
		$model = M('bad_filter');
		if (!empty($_POST)) {
			$map = array();
			$map['source_type'] = $_POST['source_type'];
			$map['source_value'] = $_POST['source_value'];
			$map['limit_time'] = $_POST['limit_time'];
			$map['addtime'] = time();
			if ($map['limit_time'] == 1) {
				$map['begintime'] = strtotime($_POST['begintime_pop'] . ' 00:00:00');
				$map['endtime'] = strtotime($_POST['endtime_pop'] . ' 23:59:59');
			}

			$where = array(
				'id' => $_POST['id']
			);
			$map['rid'] = '';
			!empty($_POST['reason']) && $map['reason'] = $_POST['reason'];
			!empty($_POST['rid']) && $map['rid'] = implode(',', $_POST['rid']);

			$model->where($where)->save($map);
			//echo $model->getlastsql();exit;
			$this->writelog('修改id['.$_POST['id'].']的评论屏蔽', 'sj_bad_filter', $_POST['id'],__ACTION__,'','edit');
			$this->success('修改成功');
		} else {
			$filter = $model->where(array('id' => $_GET['id']))->find();

	    	$where = array(
	    		'status' => 1,
	    		'reason_type' => 6,
	    	);
	    	$reasons = $model->table('dev_reason')->where($where )->select();
	    	$this->assign('reasons', $reasons);
	    	$this->assign('rid', explode(',', $filter['rid']));
			$this->assign('filter', $filter);
			$this->display();
		}
	}

	public function delBadFilter()
	{
		$model = M('bad_filter');
		$map = array(
			'id' => $_GET['id'],
		);
		$rst = $model->where($map)->find();
		if(!$rst){
			$this -> error('此记录已经被删除，请不要重复提交');
		}
		$model->where($map)->delete();
		$id_arr = explode(',',$_GET['id']);
		foreach($id_arr as $val){
			$this->writelog($val.'的评论屏蔽被删除', 'sj_bad_filter', $val,__ACTION__,'','del');
		}
		$this->success('删除成功');
	}

    public function feedback_list() {
    	$feedback_db=M('feedback');

    	$where = array(
    		'info_type' => 1
    	);
    	if(in_array($_SESSION['admin']['admin_id'],array(69,68))){
    		$_GET['sectiontypeid'] = 2;
    		$this->assign('is_Specified_user',1); 
    	}

    	$get_num = count($_GET);
        $type=$_GET['type'];
        if(empty($type)) {
            $type='self';
            $where['status'] = 1;
            $tpl = 'feedback_list';
            $processed = $_GET['processed']?$_GET['processed']:0;
			$where['processed'] = $processed;	
        } else {
        	$type = 'unshow';
        	$where['status'] = 0;
        	$tpl = 'feedback_list_unshow';
        	$processed = 3;
        }
		$this->assign('processed', $processed);		
		$this->assign('type', $type);		
		$sesson_type = 'soft_msg'.$type.$processed;
		$referer = explode('/', $_SERVER['HTTP_REFERER']);
		if(in_array('feedback_reback_show',$referer) || (!isset($_GET['search'])&&$get_num!=1)){
		    $from_referer = 1;
		}
		if($from_referer){
		    $this->getGetValfromSession($sesson_type);
		}
		$this->setGetValfromSession($sesson_type);
        !$_GET['sectiontypeid']&&$_GET['sectiontypeid']=0;
		if($_GET['sectiontypeid']==0){
			$where['sectiontypeid'] = array('in', array(-1,0));
		}else if($_GET['sectiontypeid'] !=0 ){
			$this->check_where($where, 'sectiontypeid');
		}
		if($_GET['sectiontypeid'] == 100){
			unset($where['sectiontypeid']);
		}
		!isset($_GET['pid']) ? $_GET['pid'] = 1 : $_GET['pid'];
        $this->check_where($where, 'contact');
    	$this->check_where($where, 'version_code');
    	$this->check_where($where, 'ipmsg');
    	$this->check_where($where, 'imei');
    	$this->check_where($where, 'pid');
    	$this->check_where($where, 'backtype');
    	$modelpost = M("post");
    	$modelthread = M("thread");
		if(isset($_GET['all_post']) && $_GET['all_post'] == 1){
			$str = 'all_post >= 2 and type = 2';
			$rid_data = $modelthread->where($str)->field('rid')->findAll();
			$ids = array();
			if ($rid_data) {
				foreach ($rid_data as $value) {
					$ids[] = $value['rid'];
				}
			}
			$where['feedbackid'] = array('in', $ids);			
			$this->assign('all_post', $_GET['all_post']);
		}
    	
    	$where_t = array(
    		'status' => 1
    	);

    	$this->check_other_table_where($where, $where_t, 'dname', 'did', 'pu_device', 'isset');
    	$this->check_other_table_where($where, $where_t, 'chname', 'cid', 'sj_channel', 'isset');
		// $join_fild = array(
			// '0'=>'backtype',
			// '1'=>'id'
		// );
    	// $this->check_other_table_where($where, $where_t, 'question',$join_fild , 'sj_feedback_question', 'isset');
    	$this->check_where($where, 'content', 'isset', 'like');
		if(($_GET['processed'] == 1 && isset($_GET['processed'])) || $_GET['type'] == 'unshow'){
			$order = "update_at";
		}elseif($_GET['processed'] == 0){
			$order = "update_at";
		}else{
			$order = "submit_tm";
		}
		if(empty($_GET['fromdate']) && empty($_GET['todate'])){
			$_GET['fromdate'] = date("Y-m-d H:i:s",time()-14*86400);
			$_GET['todate'] = date("Y-m-d 23:59:59",time());
		}
    	$this->check_range_where($where, 'fromdate', 'todate', $order, true);
		$admin_users = M("admin_users");		
		if (!empty($_GET['admin_name'])) {
			$where_admin = array('admin_state'=>1);
			$where_admin['admin_user_name'] = array('like','%'.escape_string($_GET['admin_name']).'%');
			$admin_user_id = $admin_users->where($where_admin)->field('admin_user_id')->buildSql();
			$where['admin_id'] = array('IN', $admin_user_id);
			$this->assign('admin_name', $_GET['admin_name']);
		}
        $conf_db = D('Sj.Config');
        $config_list = $conf_db->where('config_type="backtype" and status=1')->getField('configcontent');
        $config_list = explode("|",$config_list);
		
		$backtype = array();
		foreach($config_list as $k =>$v){
			$backtype[$k+1] = $v; 
		}
        $this->assign('feedbacktype', $backtype);
		$question_type = get_table_data(array('status'=>1),"sj_feedback_question","id","id,question");
		$this->assign('question_type', $question_type);
        import("@.ORG.Page2");
		
        $count= $feedback_db->table('sj_feedback feedback force index (update_at)')->where($where)->count();
		//echo $count;
		$param = http_build_query($_GET);	
		$Page = new Page($count,15,$param);	
        $this->assign('total', $count);
		$model = new Model();
        $feedbacklist = $feedback_db->table('sj_feedback feedback force index (update_at)')
						->where($where)
        				->limit($Page->firstRow.','.$Page->listRows)
        				->order("{$order} desc")
        				->select();
		//echo 	$feedback_db->getLastSql();			
        $dkey = array();
        $ckey = array();
		$adminid = array();
		$softid = array();
        foreach ($feedbacklist as $key => $value) {
        	# code...
			if($value['did']) $dkey[] = $value['did'];
        	$ckey[$value['cid']] = 1;
        	$feedbacklist[$key]['reply'] = '';
        	$parm2 = array('rid' => $value['feedbackid']);
      	
        	$res = $modelthread->where($parm2)->field('tid,new_post')->find();
        	
        	if ($res) {
        		$postres = $modelpost->where(array('tid' => $res['tid'], 'user_type' => 1))->field('dateline, contents')->order('dateline desc')->find();
        		$feedbacklist[$key]['reply'] = $postres['contents'];
        		$feedbacklist[$key]['reply_tm'] = $postres['dateline'];
        		$feedbacklist[$key]['new_post'] = $res['new_post']; 
				$postre_user = $modelpost->where(array('tid' => $res['tid'], 'user_type' => 0))->field('dateline, contents')->order('dateline desc')->find();
				$feedbacklist[$key]['user_content'] = $postre_user['contents'];				
				$feedbacklist[$key]['user_content_tm'] = $postre_user['dateline'];				
        	}
			if($value['admin_id']) $adminid[] = $value['admin_id'];
			if($value['softid']) $softid[] = $value['softid'];
        }
		$softid = array_unique($softid);
		if($dkey){
			$dinfo = $feedback_db->table('pu_device')->where(array('did' => array('in', array_unique($dkey))))
					->field('did,dname')
					->select();
			$dmap = array();
			foreach ($dinfo as $value) {
				# code...
				$dmap[$value['did']] = $value['dname'];
			}
		}
		if($ckey){
			$cinfo = $feedback_db->table('sj_channel')->where(array('cid' => array('in', array_keys($ckey) ) ) )
					->field('cid,chname')
					->select();
			$cmap = array();
			foreach ($cinfo as $value) {
				# code...
				$cmap[$value['cid']] = $value['chname'];
			}
        }
        $this->gpcFilter($feedbacklist);
        $this->assign('feedbacklist', $feedbacklist);
		if($adminid){
			$admin_user_name = $admin_users->where(array('admin_user_id'=>array('in',$adminid)))->field('admin_user_name,admin_user_id')->select();
			$username = array();
			foreach($admin_user_name as $k=>$v){
				$username[$v['admin_user_id']] = $v['admin_user_name'];
			}
		}
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);		
		$this->assign('username', $username);		
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
        $this->assign('cmap', $cmap);
        $this->assign('dmap', $dmap);
        $this->assign('page', $Page->show());
		$config = C('sectiontype');
		$config[0] = '待分配';
		$config[-1] = '待分配';
        $this->assign('config', $config);
        $this->assign('param', $param);
		//软件名称
		$where = array(
			'softid' => array('in',$softid),
		);
		$softlist = get_table_data($where,"sj_soft","softid","softid,softname");
		$this->assign('softlist', $softlist);
		//固件配置
		$where = array(
			'config_type' => 'firmware',
			'status' => 1
		);
		$firmware_conf = get_table_data($where,"pu_config","configname","configname,configcontent");
		$this->assign('firmware_conf', $firmware_conf);
    	$this->display($tpl);
    }
    
    
    public function exportExcel() { 
		$Export = D("Dev.Export");
		//分页		
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 100;
		$data = $Export->get_feedback($_GET,$limit,$p);
		exit(json_encode($data));
    }

    public function soft_feedback_statistics_list() {
        $type='';
        $map='';
        $actioname='';
        $tmpname='';
        $feedback_db=M('feedback');

		$where = array(
			'A.status' => 1,
			'A.info_type' => 2,			
			'B.status' => 1,
			'B.hide' => 1,
		);

		if (isset($_GET['content'])) {
			$where['A.content'] = array('like', '%'.escape_string($_GET['content']).'%');
			$this->assign('content', $_GET['content']);
		}
		if (!empty($_GET['jbori'])) {
			$where['A.jbori'] = $_GET['jbori'];
			$this->assign('jbori', $_GET['jbori']);
		}
		if (!empty($_GET['feedbacktype'])) {
			$where['A.feedbacktype'] = $_GET['feedbacktype'];
			$this->assign('feedbacktype', $_GET['feedbacktype']);
		}
		if (!empty($_GET['fromdate']) && !empty($_GET['todate'])) {
			$where['A.addtime'] = array(
				array('egt', strtotime($_GET['fromdate'])),
				array('elt', strtotime($_GET['todate'])),
			);
			$this->assign('fromdate', $_GET['fromdate']);
			$this->assign('todate', $_GET['todate']);
		} else if (!empty($_GET['fromdate'])) {
			$where['A.addtime'] = array('egt', strtotime($_GET['fromdate']));
			$this->assign('fromdate', $_GET['fromdate']);
		} else if (!empty($_GET['todate'])) {
			$where['A.addtime'] = array('elt', strtotime($_GET['todate']));
			$this->assign('todate', $_GET['todate']);
		}

		if (isset($_GET['softid'])) {
			$where['B.softid'] = $_GET['softid'];
			$this->assign('softid', $_GET['softid']);
		}
		if (isset($_GET['package'])) {
			$where['B.package'] = $_GET['package'];
			$this->assign('package', $_GET['package']);
		}
		if (isset($_GET['softname'])) {
			$where['B.softname'] = array('like', '%'.escape_string($_GET['softname']).'%');
			$this->assign('softname', $_GET['softname']);
		}
		if (isset($_GET['question_type'])) {
			$where['A.report_type'] = array('like', '%,'.escape_string($_GET['question_type']).',%');
			$this->assign('question_type', $_GET['question_type']);
		}		
		$download_field = '(B.total_downloaded+B.total_downloaded_add-B.total_downloaded_detain)';
		if ($_GET['startdownload']!='' && $_GET['enddownload']!='') {
			$where[$download_field] = array(
				array('egt', $_GET['startdownload']),
				array('elt', $_GET['enddownload'] ),
			);
			$this->assign('startdownload', $_GET['startdownload']);
			$this->assign('enddownload', $_GET['enddownload']);
		} else if ($_GET['startdownload']!='') {
			$where[$download_field] = array('egt', $_GET['startdownload']);
			$this->assign('startdownload', $_GET['startdownload']);
		} else if ($_GET['enddownload']!='') {
			$where[$download_field] = array('elt', $_GET['enddownload']);
			$this->assign('enddownload', $_GET['enddownload']);
		}

		if ($_GET['dev_name']!=''|| $_GET['email']!='' || $_GET['dev_type']!= '' || isset($_GET['dev_type'])) {
			$where_t = array(
				'status' => 0,
			);
			if ($_GET['dev_name']!='') {
				$where_t['dev_name'] = array('like', '%'.escape_string($_GET['dev_name']).'%');
				$this->assign('dev_name', $_GET['dev_name']);
			}
			if ($_GET['email']!='') {
				$where_t['email'] = $_GET['email'];
				$this->assign('email', $_GET['email']);
			}
			if ($_GET['dev_type']!='' || isset($_GET['dev_type'])) {
			    $where_t['type'] = $_GET['dev_type'];
			    $this->assign('dev_type', $_GET['dev_type']);
			}
	        $squery = $feedback_db->table('pu_developer')
	        			->where($where_t)->field('dev_id')->buildSql();
	        $where_t = array();
	        $where_t['B.dev_id'] = array('IN', $squery);
			if ($_GET['dev_name']!='') {
				$where_t['_string'] = "B.dev_id='' AND B.dev_name like '%".escape_string($_GET['dev_name'])."%'";
	        	$where_t['_logic'] = 'or';
			}
			$where['_complex'] = $where_t;
		}

		$order = isset($_GET['order']) ? $_GET['order'] :  'a';
		$this->assign('order', $order);
		if ($order == 'd') {
			$order_str = $download_field. ' desc';
		} else {
			$order_str = $download_field. ' asc';
		}


        
        $soft_db = M('soft');
        $device = D("Sj.Device");
		
        import("@.ORG.Page2");
        $sub_query = $feedback_db->table('sj_feedback A')
        			->where($where)->join('sj_soft B on A.softid=B.softid')
        			->field('A.softid')
        			->group('A.softid')->buildSql();

        $count = $feedback_db->table("({$sub_query}) T")->count();

		$this->assign('total', $count);
        $Page=new Page($count,15);
        $feedback_list = $feedback_db->table('sj_feedback A')
        				->where($where)->join('sj_soft B on A.softid=B.softid')
        				->group('A.softid')->limit($Page->firstRow.','.$Page->listRows)
        				->order($order_str)
        				->select();
		$devid_key = array();
		$softidkey = array();
		$where = array();
		$package = array();
		foreach ($feedback_list as $key => $value) {
			$devid_key[$value['dev_id']] = 1;
			$softidkey[$value['softid']] = 1;
			$package[] = $value['package'];
		}
        $conf_db = D('Sj.Config');
        $feedbacktype_list = $conf_db->where('config_type="feedbacktype" and status=1')->getField('configname,configcontent');

		$this->assign('feedbacktype_list', $feedbacktype_list);
		//新举报类型
		$report_question_type = get_table_data(array('status'=>1),"sj_report_question","id","id,question");
		$this->assign ("report_question_type", $report_question_type );
		
		$dev_info = array();
		if (!empty($devid_key)) {
			$where['dev_id'] = array('IN', array_keys($devid_key));
			$res = $feedback_db->table('pu_developer')->where($where)->field('dev_id, dev_name, email, type')->select();
			foreach ($res as $value) {
				$dev_info[$value['dev_id']] = $value;
			}
		}
		$this->assign('dev_info', $dev_info);

		$where = array();
		$softinfo = array();
		$feedbacktype_info = array();
		if (!empty($softidkey)) {
			$where['softid'] = array('IN', array_keys($softidkey));
			$where['package_status'] = 1;
			$res = $feedback_db->table('sj_soft_file')->where($where)->field('softid, iconurl,apk_name')->select();
			//软件图标
			// $softmodel = D('Dev.Softlist');
			// $iconlist = $softmodel -> new_icon_list('',$package);
			foreach ($res as $value) {
				// if($iconlist[1][$value['apk_name']]['iconurl']){
					// $iconurl = $iconlist[1][$value['apk_name']]['iconurl'];
				// }else{
					$iconurl = $value['iconurl'];
				//}
				$softinfo[$value['softid']] = $iconurl;
			}			
			$t_where = array();
			foreach ($softidkey as $key => $value) {
				# code...
				$t_where = array(
					'softid' => $key,
					'status' => 1
				);
				$feedbacktype_info[$key] = array();
				$res = $feedback_db->where($t_where)->field('count(*) as num, feedbacktype')->group('feedbacktype')->select();
				$t = 0;
				foreach ($res as $val) {
					# code...
					$feedbacktype_info[$key][] = array($feedbacktype_list[$val['feedbacktype']], $val['num'], $val['feedbacktype']);
					$t += $val['num'];
				}
				$feedbacktype_info[$key][] = array('合计', $t, '');
			}
		}

		$this->assign('softinfo', $softinfo);
		$this->assign('feedbacktype_info', $feedbacktype_info);
        $feedback_source = array(
        	'1' => '市场举报',
        	'2' => '一键举报'
        );
        $this->assign('feedback_source', $feedback_source);
		//过滤
		$this->gpcFilter($post);
		$this->gpcFilter($feedback_list);

		$param = $_GET;
		$param['order'] = ($order == 'd') ? 'a' : 'd';
		$query = http_build_query($param);
		$this->assign ("query", $query );
		//下架原因
		$reason_list = $feedback_db -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 5 ))->order('pos asc,id desc')->select();
		foreach($reason_list as &$val){
		    if($val['content2']){
		        $val['content2'] = explode('<br />', $val['content2']);
		    }
		}
		$this->assign ("reason_list", $reason_list );
		//print_r($this->feedback_list);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("actionname", $actionname );
        $this->assign ('otherAct',$otherAct);
        $this->assign ('otherurl',$otherurl);
        $this->assign ('soft',$post);
		$this->assign ("from_value", $_SESSION['fromdate']);
		$this->assign ("to_value", $_SESSION['todate']);
		$this->assign ("zh_content", $_SESSION['content']);
        $this->assign('feedbacklist',$feedback_list);
        $this->display();

    }
	public function soft_feedback_list() {
		$model = new Model();
		$where = array();
		$where['s.status'] = 1;
		$where['s.hide'] = 1;
		$where['f.status'] = 1;
		$where['f.info_type'] = 2;	
		
		if($_GET){
			if (isset($_GET['softid'])) {
				$where['f.softid'] = $_GET['softid'];
				$this->assign('softid', $_GET['softid']);
			}
			if (isset($_GET['softname'])) {	
				$where['s.softname'] = array('like', '%'.escape_string($_GET['softname']).'%');
				$this->assign('softname', $_GET['softname']);
			}
			if (isset($_GET['package'])) {
				$where['s.package'] = array('eq',trim($_GET['package']));
				$this->assign('package', $_GET['package']);
			}
			if (!empty($_GET['feedbacktype'])) {
				$where['f.feedbacktype'] = $_GET['feedbacktype'];
				$this->assign('feedbacktype', $_GET['feedbacktype']);
			}
			if (!empty($_GET['jbori'])) {
				$where['f.jbori'] = $_GET['jbori'];
				$this->assign('jbori', $_GET['jbori']);
			}		
			if (isset($_GET['content'])) {
				$where['f.content'] = array('like', '%'.escape_string($_GET['content']).'%');
				$this->assign('content', $_GET['content']);
			}	
			if(!empty($_GET['begintime']) && !empty($_GET['endtime'])){
				$begintime = strtotime($_GET['begintime']);
				$endtime = strtotime($_GET['endtime']);
				$where['f.submit_tm'] = array(array("egt",$begintime),array("elt",$endtime));
				$this->assign('begintime', $_GET['begintime']);
				$this->assign('endtime', $_GET['endtime']);
			}	
			if (isset($_GET['question_type'])) {
				$where['f.report_type'] = array('like', '%,'.escape_string($_GET['question_type']).',%');
				$this->assign('question_type', $_GET['question_type']);
			}	
		}
		if(empty($_GET['begintime']) && empty($_GET['endtime'])){
			$start = date("Y-m-d 00:00:00",time()-30*86400);
			$end = date("Y-m-d 23:59:59",time());
			$begintime = strtotime($start);
			$endtime = strtotime($end);
			$where['f.submit_tm'] = array(array("egt",$begintime),array("elt",$endtime));
			$_GET['begintime'] = $start;
			$_GET['endtime'] = $end;
			$this->assign('begintime', $start);
			$this->assign('endtime', $end);
		}
		//分页
		import("@.ORG.Page2");
		$count = $model->table('sj_soft s')->join("sj_feedback f ON f.softid = s.softid")->where($where)->count();	
        $Page=new Page($count,15);
		$feedback_list = $model->table('sj_soft s')->join("sj_feedback f ON f.softid = s.softid")->where($where)->field('f.*,s.softname,s.package,s.version,s.language')->limit($Page->firstRow.','.$Page->listRows)->order('f.submit_tm desc')->select();
		//echo $model->getlastsql();
		$softid = array();
		foreach($feedback_list as $key => $val){
			$softid[] = $val['softid']; 
		}
		$softid = array_unique($softid);
		if($softid){
			//图标icon
			$file_db = M('soft_file');
			$map1 = array();
			$map1['softid'] = array('in', $softid);
			$soft_file = $file_db ->where($map1)->field('softid,iconurl')->select();
			$filelist = array();
			foreach($soft_file as $key => $val){
				$filelist[$val['softid']] = $val;
			}
		}
		//举报类型
		$conf_db = D('Sj.Config');
        $feedbacktype_list = $conf_db->where('config_type="feedbacktype" and status=1')->getField('configname,configcontent');
		//新举报类型
		$report_question_type = get_table_data(array('status'=>1),"sj_report_question","id","id,question");
		$this->assign ("report_question_type", $report_question_type );
		foreach($feedback_list as $key => $val){
			$feedback_list[$key]['softname'] = $val['softname'];
			$feedback_list[$key]['package'] = $val['package'];
			$feedback_list[$key]['version'] = $val['version'];
			$feedback_list[$key]['language'] = $val['language'];
			$feedback_list[$key]['iconurl'] = $filelist[$val['softid']]['iconurl'];
			$feedback_list[$key]['configname'] = $feedbacktype_list[$val['feedbacktype']];
			if($val['report_type']){
				$report_type = explode(',',$val['report_type']);
				$str = '';
				foreach($report_type as $v){
					if($v){
						$str .= $report_question_type[$v]['question'];
					}
				}
			}
			$feedback_list[$key]['question_type'] = $str;			
		}
		$feedback_source = array(
        	'1' => '市场举报',
        	'2' => '一键举报'
        );
		$param = http_build_query($_GET);				
        $this->assign('param', $param);
        $this->assign('feedback_source', $feedback_source);
		$this->assign('feedbacklist',$feedback_list);
		$this->assign('feedbacktype_list',$feedbacktype_list);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
        $show =$Page->show();
        $this->assign ("page", $show );
		$this->assign('total', $count);
		$this->display();
	}
    public function soft_feedback_unshow_list() {
        $type='';
        $map='';
        $actioname='';
        $tmpname='';
        $feedback_db=M('feedback');

		$where = array(
			'A.status' => 0,
			'B.status' => 1,
			'B.hide' => 1,
			'A.softid' => array('gt', 0),
		);

		if (isset($_GET['content'])) {
			$where['A.content'] = array('like', '%'.escape_string($_GET['content']).'%');
			$this->assign('content', $_GET['content']);
		}
		if (!empty($_GET['jbori'])) {
			$where['A.jbori'] = $_GET['jbori'];
			$this->assign('jbori', $_GET['jbori']);
		}
		if (!empty($_GET['feedbacktype'])) {
			$where['A.feedbacktype'] = $_GET['feedbacktype'];
			$this->assign('feedbacktype', $_GET['feedbacktype']);
		}
		if (!empty($_GET['fromdate']) && !empty($_GET['todate'])) {
			$where['A.addtime'] = array(
				array('egt', strtotime($_GET['fromdate'])),
				array('elt', strtotime($_GET['todate'])),
			);
			$this->assign('fromdate', $_GET['fromdate']);
			$this->assign('todate', $_GET['todate']);
		} else if (!empty($_GET['fromdate'])) {
			$where['A.addtime'] = array('egt', strtotime($_GET['fromdate']));
			$this->assign('fromdate', $_GET['fromdate']);
		} else if (!empty($_GET['todate'])) {
			$where['A.addtime'] = array('elt', strtotime($_GET['todate']));
			$this->assign('todate', $_GET['todate']);
		}

		if (isset($_GET['softid'])) {
			$where['B.softid'] = $_GET['softid'];
			$this->assign('softid', $_GET['softid']);
		}
		if (isset($_GET['package'])) {
			$where['B.package'] = $_GET['package'];
			$this->assign('package', $_GET['package']);
		}
		if (isset($_GET['softname'])) {
			$where['B.softname'] = array('like', '%'.escape_string($_GET['softname']).'%');
			$this->assign('softname', $_GET['softname']);
		}
		$download_field = '(B.total_downloaded+B.total_downloaded_add-B.total_downloaded_detain)';
		if ($_GET['startdownload']!='' && $_GET['enddownload']!='') {
			$where[$download_field] = array(
				array('egt', $_GET['startdownload']),
				array('elt', $_GET['enddownload'] ),
			);
			$this->assign('startdownload', $_GET['startdownload']);
			$this->assign('enddownload', $_GET['enddownload']);
		} else if ($_GET['startdownload']!='') {
			$where[$download_field] = array('egt', $_GET['startdownload']);
			$this->assign('startdownload', $_GET['startdownload']);
		} else if ($_GET['enddownload']!='') {
			$where[$download_field] = array('elt', $_GET['enddownload']);
			$this->assign('enddownload', $_GET['enddownload']);
		}
		if ($_GET['dev_name']!=''|| $_GET['email']!='') {
			$where_t = array(
				'status' => 0,
			);
			if ($_GET['dev_name']!='') {
				$where_t['dev_name'] = array('like', '%'.escape_string($_GET['dev_name']).'%');
				$this->assign('dev_name', $_GET['dev_name']);
			}
			if ($_GET['email']!='') {
				$where_t['email'] = $_GET['email'];
				$this->assign('email', $_GET['email']);
			}
	        $squery = $feedback_db->table('pu_developer')
	        			->where($where_t)->field('dev_id')->buildSql();
	        $where_t = array();
	        $where_t['B.dev_id'] = array('IN', $squery);
			if ($_GET['dev_name']!='') {
				$where_t['B.dev_name'] = array('like', '%'.escape_string($_GET['dev_name']).'%');
	        	$where_t['_logic'] = 'or';
			}
			$where['_complex'] = $where_t;
		}



        
        $soft_db = M('soft');
        $device = D("Sj.Device");
		
        import("@.ORG.Page2");
        $sub_query = $feedback_db->table('sj_feedback A')
        			->where($where)->join('sj_soft B on A.softid=B.softid')
        			->field('A.softid')
        			->group('A.softid')->buildSql();

        $count = $feedback_db->table("({$sub_query}) T")->count();

        $count = $feedback_db->table('sj_feedback A')
        			->where($where)->join('sj_soft B on A.softid=B.softid')
        			->field('A.softid')
        			->count();

		$this->assign('total', $count);
        $Page=new Page($count,15);
        $feedback_list = $feedback_db->table('sj_feedback A')
        				->where($where)->join('sj_soft B on A.softid=B.softid')
        				->limit($Page->firstRow.','.$Page->listRows)
        				->select();

		$devid_key = array();
		$softidkey = array();
		$where = array();
		$package = array();
		foreach ($feedback_list as $key => $value) {
			$devid_key[$value['dev_id']] = 1;
			$softidkey[$value['softid']] = 1;
			$package[] = $value['package']; 
		}
        $conf_db = D('Sj.Config');
        $feedbacktype_list = $conf_db->where('config_type="feedbacktype" and status=1')->getField('configname,configcontent');

		$this->assign('feedbacktype_list', $feedbacktype_list);
		$dev_info = array();
		if (!empty($devid_key)) {
			$where['dev_id'] = array('IN', array_keys($devid_key));
			$res = $feedback_db->table('pu_developer')->where($where)->field('dev_id, dev_name')->select();
			foreach ($res as $value) {
				$dev_info[$value['dev_id']] = $value['dev_name'];
			}
		}
		$this->assign('dinfo', $dinfo);

		$where = array();
		$softinfo = array();
		$feedbacktype_info = array();
		if (!empty($softidkey)) {
			$where['softid'] = array('IN', array_keys($softidkey));
			$where['package_status'] = 1;
			$res = $feedback_db->table('sj_soft_file')->where($where)->field('softid, iconurl,apk_name')->select();
			//软件图标
			// $softmodel = D('Dev.Softlist');
			// $iconlist = $softmodel -> new_icon_list('',$package);
			foreach ($res as $value) {
				// if($iconlist[1][$value['apk_name']]['iconurl']){
					// $iconurl = $iconlist[1][$value['apk_name']]['iconurl'];
				// }else{
					$iconurl = $value['iconurl'];
				//}
				$softinfo[$value['softid']] = $iconurl;
			}

		}

		$this->assign('softinfo', $softinfo);
        $feedback_source = array(
        	'1' => '市场举报',
        	'2' => '一键举报'
        );
        $this->assign('feedback_source', $feedback_source);
		//过滤
		$this->gpcFilter($post);
		$this->gpcFilter($feedback_list);

		$param = $_GET;
		$param['order'] = ($order == 'd') ? 'a' : 'd';
		$query = http_build_query($param);
		$this->assign ("query", $query );

		//print_r($this->feedback_list);
		$Page->rollPage = 10;
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
        $show =$Page->show();
        $this->assign ("page", $show );
        $this->assign ("actionname", $actionname );
        $this->assign ('otherAct',$otherAct);
        $this->assign ('otherurl',$otherurl);
        $this->assign ('soft',$post);
		$this->assign ("from_value", $_SESSION['fromdate']);
		$this->assign ("to_value", $_SESSION['todate']);
		$this->assign ("zh_content", $_SESSION['content']);
        $this->assign('feedbacklist',$feedback_list);
        $this->display();
    }

    function show_soft_feedback()
    {
    	$softid = $_GET['softid'];
    	$this->assign('softid', $_GET['softid']);
    	$feedback_db=M('feedback');
    	$softinfo = $feedback_db->table('sj_soft')->where(array('softid' => $softid)) -> find();
    	$this->assign('softinfo', $softinfo);

    	$where = array(
    		'softid' => $softid, 
    		'status' => 1,
    	);
		if (isset($_GET['content'])) {
			$where['content'] = array('like', '%'.escape_string($_GET['content']).'%');
			$this->assign('content', $_GET['content']);
		}
		if (!empty($_GET['jbori'])) {
			$where['jbori'] = $_GET['jbori'];
			$this->assign('jbori', $_GET['jbori']);
		}
		if (!empty($_GET['feedbacktype'])) {
			$where['feedbacktype'] = $_GET['feedbacktype'];
			$this->assign('feedbacktype', $_GET['feedbacktype']);
		}
    	$count = $feedback_db->where($where)->count();
    	import("@.ORG.Page2");
        $Page=new Page($count,15);
    	$feedbacklist = $feedback_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        $conf_db = D('Sj.Config');
        $feedbacktype_list = $conf_db->where('config_type="feedbacktype" and status=1')->getField('configname,configcontent');

        $feedback_source = array(
        	'1' => '市场举报',
        	'2' => '一键举报'
        );

		$this->assign('total', $count);

        $show =$Page->show();
        $this->assign ("page", $show );
    	$this->assign('feedbacklist', $feedbacklist);
    	$this->assign('feedbacktype_list', $feedbacktype_list);
    	$this->assign('feedback_source', $feedback_source);
    	$this->display();
    }
    public function deleteFeedback()
    {
    	$feedback_model = M('feedback');
		$time = time();
    	$data = array(
    		'status' => 0,
    		'update_at' => $time,
			'admin_id'=> $_SESSION['admin']['admin_id']
    	);
    	$id = '';
		$type = $_GET['type'];
		if($type == "feedback"){
			$str = "反馈";
		}elseif($type == "report"){
			$str = "举报";
		}
    	if (isset($_GET['id'])) {
	    	$where = array(
	    		'feedbackid' => $_GET['id']
	    	);
	    	$id = $_GET['id'];
			$res = $feedback_model->where($where)->find();
			if($res['status'] == 0){
				$this -> error('此信息已经被删除！请不要重复提交。');
			}
    	} else if (isset($_GET['ids'])) {
	    	$where = array(
	    		'feedbackid' => array('IN', explode(',', $_GET['ids']))
	    	);
            $res = $feedback_model->where($where)->field('processed')->find();
	    	$id = $_GET['ids'];
    	}
    	$physical = ($_GET['physical']) ? $_GET['physical'] : 0;
    	if (empty($physical)) {
    		$feedback_model->where($where)->save($data);
			if($_GET['ids']){
				if($res['processed']==1){
					$this->writelog('对id['.$id.']的'.$str.'进行删除', 'sj_feedback', $id,__ACTION__,'processed','del');
				}else{
					$this->writelog('对id['.$id.']的'.$str.'进行删除', 'sj_feedback', $id,__ACTION__,'','del');
				}
			}else{
                if($res['processed']==1){
                    $this->writelog('对id['.$id.']的'.$str.'进行删除', 'sj_feedback', $id,__ACTION__,$type.'processed','del');
                }else{
                    $this->writelog('对id['.$id.']的'.$str.'进行删除', 'sj_feedback', $id,__ACTION__,$type,'del');
                }
				
			}
    	} elseif(!empty($id)) {
    		$feedback_model->where($where)->delete();
			if($_GET['ids']){
				foreach(explode(',', $_GET['ids']) as $val){
					$this->writelog('对id['.$val.']的'.$str.'进行物理删除', 'sj_feedback', $val,__ACTION__,$type,'del');
				}
			}else{
				$this->writelog('对id['.$id.']的'.$str.'进行物理删除', 'sj_feedback', $id,__ACTION__,$type,'del');
			}
    	}
		$map = array('status' => 0,'last_refresh'=>$time);
		$feedback_model->table('sj_thread')->where("rid='{$id}'")->save($map);
    	if($_GET['ids'] || $type == "report"){
    	    $this->success('删除成功');
     	}else{
    	    exit(json_encode(array('code'=>1,'msg'=>array($_GET['id']))));
    	}
    	
    }

    public function deleteSoftFeedback()
    {
    	$feedback_model = M('feedback');
    	$data = array(
    		'status' => 0,
    		'update_at' => time()
    	);
    	$id = '';
    	if (isset($_GET['softid'])) {
	    	$where = array(
	    		'softid' => $_GET['softid']
	    	);
	    	$softid = $_GET['softid'];
    	} else if (isset($_GET['softids'])) {
	    	$where = array(
	    		'softid' => array('IN', explode(',', $_GET['softids']))
	    	);
	    	$softid = $_GET['softids'];
    	}
    	$feedback_model->where($where)->save($data);
		if (isset($_GET['softid'])) {
			$this->writelog('对id['.$softid.']的举报进行删除', 'sj_feedback', $softid,__ACTION__ ,"","del");
		} else if (isset($_GET['softids'])) {
			$ids = explode(',', $_GET['softids']);
			foreach ($ids as $softid) {
				$this->writelog('对id['.$softid.']的举报进行删除', 'sj_feedback', $softid,__ACTION__ ,"","del");
			}
		}
    	if($_GET['softids']){
    	    $this->success('删除成功');
    	}else{
    	    exit(json_encode(array('code'=>1,'msg'=>array($softid))));
    	}		
    }
    function feedback_reback_show(){
        $id = $_GET['id'];
        $model = M("feedback");
        $modelpost = M("post");
        $modelthread = M("thread");
		$admin_users = M("admin_users");
        $res = $modelthread->where(array('type'=>2,'rid'=>$id))->field('tid')->find();
        if($res['tid']){
            $data = $modelpost->where(array('tid'=>$res['tid'],'status'=>1))
            ->order('dateline asc')
            ->select();
        }
        $feedback = $model->where(array('feedbackid'=>$id))->find();
		$adminid = array();
		foreach($data as $v){
			$adminid[] = $v['system_userid'];
		}
		//操作人
		$where = array(
			'admin_user_id' => array('in',$adminid),
		);
		$username_arr = get_table_data($where,"sj_admin_users","admin_user_id","admin_user_name,admin_user_id");		

		//回复内容
		$reason_list = $model -> table("dev_reason") -> where(array("status" => 1,"reason_type" => 8,"pid"=>$_GET['pid'] ))->order('pos asc,id desc')->select();
		foreach($reason_list as &$val){
		    if($val['content2']){
		        $val['content2'] = explode('<br />', $val['content2']);
		    }
		}	
		$this -> assign('reason_list',$reason_list);		
        $this->assign('feedback',$feedback);
		$this->gpcFilter($data);
        $this->assign('username_arr',$username_arr);
        $this->assign('data',$data);
        $this->assign('processed',$_GET['processed']);
		if($_GET['sectiontypeid'] == 2 ){ 
			//白名单软件
			// 远程调用Dev.FeedbackLog控制器的process_whitelist_soft操作方法
			$white_soft_arr = R("Dev.FeedbackLog","process_whitelist_soft"); 
			$this->assign('abc_list',$white_soft_arr['abc_list']);
			$this->assign('all_soft',$white_soft_arr['all_soft']);
			$this->assign('soft',$white_soft_arr['soft']);
		}
		//渠道类型
		$where = array(
			//'status' => 1,
			'type' => 1,
			'c_name' => '518后台',
		);
		$subQuery = $model->table('sj_feedback_config')->where($where)->field('id')->buildSql(); 	
		$where = array(
			//'status' => 1,
			'type' => 2,
			'parent_id' => array('in',$subQuery),
		);
		$feed_type = get_table_data($where,"sj_feedback_config","id","id,c_name,parent_id,status");	
        $this->assign('feed_type',$feed_type);		
        $this->display();
         
    }
	function feedback_reback(){
	    header("Content-type: text/html; charset=utf-8");
		$model = M("feedback");
		$ret = $model->where(array("feedbackid" =>$_GET['id']))->find();
		$modelpost = M("post");
		$modelthread = M("thread");
		$ret1 = $modelthread->table('pu_user')->where(array("userid" => $ret['userid']))->find();
		$res = $modelthread->where(array('rid' => $_GET['id']))->find();
		if($_POST['sectiontypeid'] == 2 && $_POST['record']==1){
			//添加游戏运营本次处理记录
			if(empty($_POST['softname'])){
				exit(json_encode(array('code'=>'0','msg'=>'软件名称不能为空')));
			}	
			if(empty($_POST['feedback_type'])){
				exit(json_encode(array('code'=>'0','msg'=>'请选择问题类型')));
			}	
			$pkg = $_POST['pkg'];
			$feedback_type = $_POST['feedback_type'];
		}
		if (!$res) {
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
		} else {
			$retitd = $res['tid'];
 			$data1 = array('last_post_time' => time() , 'new_post' => ($res['new_post']+1) , 'all_post' => ($res['all_post']+1));
 			$modelthread->where("tid = {$res['tid']}")->data($data1)->save();
		}
		if($_POST['recommend_soft']==1)
		{
		 $package_arr=explode(",",$_POST['package_all']);
		 $package_a=array_filter($package_arr);
		 $package_last=implode(",",$package_a); 
		}
		else
		{
		 $package_last="";
		}
		if($_POST['img_path']){
			$tmp_dir = '/tmp/'.$_POST['img_path'];
			$newfile = UPLOAD_PATH. "/img/".date('Ym/d').'/';
			if(!is_dir($newfile)) {
				if(!mkdir($newfile,0777,true)) {
					//创建thumb_ori目录{$dir_thumb_ori}失败
					permanentlog($field.".log",date('Y-m-d H:i:s')."---".$dir."目录创建失败");
				}
			}
			$path =  $newfile.$_POST['img_path'];
			if (copy($tmp_dir,$path)) {
				$str = "[img]".str_replace(UPLOAD_PATH,'',$path)."[/img]";	
			}else{
				permanentlog("feedback_img.log",date('Y-m-d H:i:s')."---".$tmp_dir."=>".$path."copy失败");
			}
		}
		$postdata = array(
			'tid' => $retitd ,
			'contents' => $_POST['content'].$str ,
			'recommend_package'=>$package_last,
			'dateline' => time() ,
			'status' => '1' ,
			'user_type' => '1' ,
			'system_userid' => $_SESSION['admin']['admin_id']
		);
		if($_POST['sectiontypeid'] == 2 && $_POST['record']==1){
			$postdata['processing_type'] = $feedback_type;
		}
 		$modelpost->add($postdata);

 		$model->where(array("feedbackid" =>$_GET['id']))->save(array('processed' => 1,'update_at'=>time(),'admin_id'=> $_SESSION['admin']['admin_id']));
		if($_POST['sectiontypeid'] == 2 && $_POST['record']==1){
			if(!$pkg && $_POST['softname'] != '非合作游戏'){
				$where = array(
					'softname' => $_POST['softname'],
					'status'=>1,
					'hide' =>1
				);
				$subQuery = $modelthread->table('sj_soft')->where($where)->field('package')->buildSql(); 	
				$where = array(
					'package' => array('in',$subQuery),
					'status'=>1,
				);
				$whitelist = $modelthread->table('sj_soft_whitelist')->where($where)->field('package')->find();
				$pkg = $whitelist['package'];
			}
			$log_model = D('Dev.Log');
			$log_model -> update_records($pkg,$feedback_type,$_POST['pid']);
		}
		if($ret['processed']==1){
			$this->writelog('添加了ID为['.$_GET['id'].']的反馈回复','sj_feedback',$_GET['id'],__ACTION__,'processed','add');
		}else{
			$this->writelog('添加了ID为['.$_GET['id'].']的反馈回复','sj_feedback',$_GET['id'],__ACTION__,'','add');
		}
 		
 		exit(json_encode(array('code'=>'1','msg'=>'回复成功')));
 	}
	//软件反馈批量回复--上传文件的
	function feedback_reback_all_file(){
		//导入文件
		if(!empty($_FILES)){
			if (!empty($_FILES['csv']['tmp_name'])) {
				$array = array('csv');
				$ytypes = $_FILES['csv']['name'];
				$info = pathinfo($ytypes);
				$type =  $info['extension'];//获取文件件扩展名
				if(!in_array($type,$array)){
					$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
					$this->error('上传格式错误');
				}
				
				if (!empty($_FILES['csv']['tmp_name'])){		
					$data = file_get_contents($_FILES['csv']['tmp_name']);
					//判断是否是utf-8编辑
					if(mb_check_encoding($data,"utf-8") != true){
						$data = iconv("gbk","utf-8", $data);
					}
					$data = str_replace("\r\n","\n",$data);	
					$data_arr = explode("\n", $data);
				}
			}
			$file_data = array_unique($data_arr);
		}
		$model = M("feedback");
		$modelpost = M("post");
		$modelthread = M("thread");		
		$error ='';
		foreach($file_data as $val){
			if(!$val) continue;
			list($id,$content) = explode(',',$val);
			if(!is_numeric($id)){
				$error .= "ID为【{$id}】格式错误\n";
				continue;
			}	
			$ret = $model->where(array("feedbackid" =>$id))->find();
			if(!$ret){
				$error .= "ID为【{$id}】记录无效\n";
				continue;
			}
			$ret1 = $modelthread->table('pu_user')->where(array("userid" => $ret['userid']))->field('user_name')->find();
			$res = $modelthread->where(array('rid' => $id))->find();
			if (!$res) {
				$map = array(
					'rid' => $id , 
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
				$retitd = $modelthread->add($map);
			} else {
				$retitd = $res['tid'];
			}
			$postdata = array('tid' => $retitd , 'contents' => $content , 'dateline' => time() , 'status' => '1' , 'user_type' => '1' , 'system_userid' => $_SESSION['admin']['admin_id']);
			$modelpost->add($postdata);
			$model->where(array("feedbackid" =>$id))->save(array('processed' => 1,'update_at'=>time(),'admin_id'=> $_SESSION['admin']['admin_id']));
		}
		$this->assign("jumpUrl","/index.php/Dev/Message/feedback_list/processed/1");
		if($error){
			$this -> error($error);
		}else{
			$this -> success('成功');
		}
	}

	
 	function check_where(&$where , $column, $check_func = 'isset', $where_type = 'eq',$assign = true) {
 		$has_rule = false;
 		$where_key = empty($column_alias) ? $column : $column_alias;
 		$dot_pos = stripos('.', $column);
 		$key = ($dot_pos===false) ? $column : substr($column, $dot_pos);

 		switch ($check_func) {
 			case 'isset':
 				$has_rule = isset($_GET[$key]);
 				break;
 			case 'noempty':
 				$has_rule = !empty($_GET[$key]);
 				break; 			
 			default:
 				$has_rule = isset($_GET[$key]);
 				break;
 		}
 		if ($has_rule) {
 			if ($where_type == 'eq') {
				$where[$where_key] = $_GET[$key];
 			} else if ($where_type == 'like') {
 				$where[$where_key] = array('like', '%'.escape_string($_GET[$key]).'%');
 			}
			$assign && $this->assign($key, $_GET[$key]);
		}
		return $has_rule;
 	}

 	function check_other_table_where(&$where, $other_where, $column, $join_field, $table, $check_func = 'isset', $where_type = 'eq', $assign = true,$column_alias=null)
 	{
		$model = new Model();
		$has_rule = false;
		if (is_array($join_field)) {
			$inner_key = $join_field[0];
			$join_key = $join_field[1];
		} else {
			$inner_key = $join_key = $join_field;
		}
		if ($this->check_where($other_where, $column, $check_func, $where_type, $assign)) {
 			$dot_pos = stripos('.', $join_key);
 			$field = ($dot_pos===false) ? $join_key : substr($join_key, $dot_pos);

			$res = $model->table($table)->where($other_where)->field($field)->select();
			$ids = array();
			if ($res) {
				foreach ($res as $value) {
					$ids[] = $value[$field];
				}
			}
			$where[$inner_key] = array('in', $ids);
		}
		return $has_rule;
 	}

 	function check_range_where(& $where, $start, $end, $column, $is_time = false, $assign = true)
 	{
 		$has_rule = false;

		if (!empty($_GET[$start]) && !empty($_GET[$end])) {
			$where[$column] = array(
				array('egt', $is_time ? strtotime($_GET[$start]) : $_GET[$start]),
				array('elt', $is_time ? strtotime($_GET[$end]) : $_GET[$end]),
			);
			$assign && $this->assign($start, $_GET[$start]);
			$assign && $this->assign($end, $_GET[$end]);

		} elseif (!empty($_GET[$start])) {
			$where[$column] = array('egt', $is_time ? strtotime($_GET[$start]) : $_GET[$start]);
			$assign && $this->assign($start, $_GET[$start]);
		} elseif (!empty($_GET[$end])) {
			$where[$column] = array('elt', $is_time ? strtotime($_GET[$end]) : $_GET[$end]);
			$assign && $this->assign($end, $_GET[$end]);
		}
		return $has_rule;
 	}
		
	//评论回复页面
	function Comment_reply(){
		$model = D("Dev.Message");
		list($res,$data,$admin_info,$dev_arr) = $model -> get_comment_reply();
        $this->assign('comment',$res);
        $this->assign('data',$data);
		$this->assign('username_arr',$admin_info);
		$this->assign('dev_arr',$dev_arr);			
		//回复配置内容	
		$reasons = $model -> get_reason(10);
		$this->assign('reason_list', $reasons);				
        $this->display();
	}
	//评论回复提交
	function Comment_reply_do(){

	    header("Content-type: text/html; charset=utf-8");
		$comment_model = D('Dev.Comment');
		$ret = $comment_model->table('sj_soft_comment')->where(array("id" =>$_GET['id']))->find();
		$modelpost = M("post");
		$modelthread = M("thread");
		$ret1 = $modelthread->table('pu_user')->where(array("userid" => $ret['userid']))->find();
		$res = $modelthread->where(array('rid' => $_GET['id'], 'type' => 1))->find();
		$time = time();
		if (!$res) {
	  	    $data = array(
		  		'rid' => $_GET['id'] , 
		  		'type' => 1 , 
		  		'imsi' => $ret['imsi'] ,
		  		'mac' => $ret['mac'] , 
		  		'userid' => $ret['userid'] ,
		  		'username' => $ret1['user_name'],
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
		  		'last_post_time' => $time, 
		  		'last_user_post_time' => $ret['create_time'],
		  		'vcode' => $ret['version_code'],
	  	        'pid' => $ret['pid'],
		  	);	
	  	    $retitd = $modelthread->add($data);
		} else {
			$retitd = $res['tid'];
 			$data1 = array('last_post_time' => $time , 'new_post' => ($res['new_post']+1) , 'all_post' => ($res['all_post']+1));
 			$modelthread->where("tid = {$res['tid']}")->data($data1)->save();			
		}
		$where = array(
			'comment_id' => $_GET['id'], 
			'status' => 1,
			'conver_id' => array('gt',0)
		);
		$conver = $modelpost->where($where)->field('conver_id')->order('pid desc')->find();
		if($conver){
			$conver_id = $conver['conver_id'];
		}else{
			$data = array('status' => 2,'update_tm'=>$time);
			$conver_id = $modelthread -> table('sj_conversation')->add($data);		
		}
		$postdata = array(
			'tid' => $retitd ,
			'comment_id'=>$_GET['id'], 
			'contents' => $_POST['content'] ,
			'dateline' => $time, 
			'update_time' => $time , 			
			'status' => '1' , 
			'user_type' => '1' , 
			'system_userid' => $_SESSION['admin']['admin_id'],
			'user_name' => $_SESSION['admin']['admin_user_name'],
			'before_id' =>  0,
			'reply_userid' => $ret['userid'],
			'reply_imei' => $ret['imei'] ? $ret['imei'] : '',
			'reply_device' => $ret['device'] ? $ret['device'] : '',
			'reply_vname' => $ret['vname'] ? $ret['vname'] : '',
			'reply_user_name' => $ret['nick_name'] ? $ret['nick_name'] : $ret['user_name'],
			'reply_avatar' => $ret['nick_name'] ? IMGATT_HOST . $ret['avatar'] : $ret['avatar'],
			'reply_content' => '',
			'package' => $ret['package'] ? $ret['package'] : '',
			'softid' => $ret['softid'] ? $ret['softid'] : 0 ,
			'hide' => 1,
			'conver_id' => $conver_id,
		);	
		if ($ret['version_code'] >= 6000) {
			$postdata['hide'] = 0;
		}
 		$pid = $modelpost->add($postdata);
		$data = array(
			'processed' => 1,
			'update_time'=>$time,
			'from_processed'=>0,
			'update_user_id'=> $_SESSION['admin']['admin_id'],
			'all_post' => array('exp',"`all_post`+1")
		);
		if ($ret['version_code'] >= 6000) {
			set_redis_comment($ret['userid'],$ret['imei'], $pid, $postdata['comment_id'],$ret['mac']);
			$data['reply_count'] = array('exp',"`reply_count`+1");
		}
		//调用回复字符处理worker
		$send_data = array(
			'id' => $pid,
			'type' => 2
		);
		$task_client = get_task_client();
		$task_client->doBackground("comment_deal", json_encode($send_data));

 		$comment_model->table('sj_soft_comment')->where(array("id" =>$_GET['id']))->save($data);
        if($ret['processed']==1){
           $this->writelog('添加了ID为['.$_GET['id'].']的评论回复','sj_soft_comment',$_GET['id'],__ACTION__,'processed','add');
        }else{
           $this->writelog('添加了ID为['.$_GET['id'].']的评论回复','sj_soft_comment',$_GET['id'],__ACTION__,'','add');
        }
 		
		exit(json_encode(array('code'=>'1','msg'=>'回复成功')));	
 	}
	//已删除列表
    public function del_soft_list() {
		$this -> message_soft('','del_soft_list');
    }
	//软件举报信息导出	
	function soft_feedback_export(){
		$Export = D("Dev.Export");
		//分页		
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 100;
		$data = $Export->get_soft_feedback($_GET,$limit,$p);
		exit(json_encode($data));
	}
	//软件评论导出	
	function soft_message_export(){
		$Export = D("Dev.Export");
		//分页		
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 100;
		$data = $Export->get_soft_message($_GET,$limit,$p);
		exit(json_encode($data));
	}
	//得到get值从session
	public function getGetValfromSession($session_type){
		foreach($_SESSION[$session_type] as $k=>$v){
			$_GET[$k]	=	isset($_GET[$k])?$_GET[$k]:$_SESSION[$session_type][$k];
		}
	}
	//set session值从get
	public function setGetValfromSession($session_type){
		unset($_SESSION[$session_type]);
		foreach($_GET as $k=>$v){
			if($v){
				$_SESSION[$session_type][$k]	=	$_GET[$k];
			}
		}		
	}
	//分配部门
	public function department(){
		$config = C('sectiontype');
		$config_msg = C('sectiontype_msg');
		if($_POST){
			$model = new Model();
			$feedbackid = explode(',',$_POST['id']);
			$sectiontypeid = $_POST['sectiontypeid'];

			$where = array(
				'feedbackid' => array('in',$feedbackid)
			);
			$res = $model -> table('sj_feedback')-> where($where)->field('processed')->find();
			$data = array(
				'sectiontypeid' => $sectiontypeid,
				'update_at'=>time(),
				'admin_id' => $_SESSION['admin']['admin_id']
			);
			if($sectiontypeid == 0){
				$data['status'] = 0;
			}
			$ret = $model -> table('sj_feedback')-> where($where)->save($data); 
			if($ret){
				if($sectiontypeid == 0){
                   if($res['processed']==1){
                       $this->writelog("删除了feedbackid为{$_POST['id']}的软件反馈",'sj_feedback',$_POST['id'],__ACTION__,'processed','del');
                   }else{
                       $this->writelog("删除了feedbackid为{$_POST['id']}的软件反馈",'sj_feedback',$_POST['id'],__ACTION__,'','del');
                   }
					
				}else{
					foreach($feedbackid as $v){
						if($res['processed']==1){
							$this->writelog("把feedbackid为{$v}分配到{$config[$sectiontypeid]}部门",'sj_feedback',$v,__ACTION__,'processed','edit');
						}else{
							$this->writelog("把feedbackid为{$v}分配到{$config[$sectiontypeid]}部门",'sj_feedback',$v,__ACTION__,'','edit');
						}
						
					}
				}
				exit(json_encode(array('code'=>1,'msg'=>'分配成功')));
			}else{
				exit(json_encode(array('code'=>0,'msg'=>'分配失败')));
			}				
		}else{
			$this->assign('department', $config);
			$this->assign('sectiontypeid', $_GET['sectiontypeid']);
			$this->assign('department_msg', $config_msg);
			$this->assign('id', $_GET['id']);			
			$this -> display();
		}
	}
	//软件反馈部门统计
	public function pub_department_total(){
		$model = new Model();
		$processed = $_GET['processed'] ? $_GET['processed'] : 0;
		$where = array();
		$where['status'] = 1;
		$where['softid'] = 0;
		$where['info_type'] = 1;	
    	$this->check_where($where, 'version_code');
    	$this->check_where($where, 'ipmsg');
    	$this->check_where($where, 'imei');
        $this->check_where($where, 'contact');
    	$this->check_where($where, 'pid', 'noempty');
    	$this->check_where($where, 'processed');
    	$this->check_where($where, 'backtype');
    	$where_t = array(
    		'status' => 1
    	);		
    	$this->check_other_table_where($where, $where_t, 'dname', 'did', 'pu_device', 'isset');
    	$this->check_other_table_where($where, $where_t, 'chname', 'cid', 'sj_channel', 'isset');
    	$this->check_where($where, 'content', 'isset', 'like');	
    	$order = "update_at";
		/*if(($_GET['processed'] == 1 && isset($_GET['processed']))){
			$order = "update_at";
		}elseif($_GET['processed'] == 0){
			$order = "update_at";
		} */
    	$this->check_range_where($where, 'fromdate', 'todate', $order, true);	
		if(isset($_GET['all_post']) && $_GET['all_post'] == 1){
			$str = 'all_post >= 2 and type = 2';
			$rid_data = $model->table('sj_thread')->where($str)->field('rid')->findAll();
			$ids = array();
			if ($rid_data) {
				foreach ($rid_data as $value) {
					$ids[] = $value['rid'];
				}
			}
			if($ids){
				$where['feedbackid'] = array('in', $ids);	
			}			
		}		
		$where['processed'] = $processed;
		$department_total = $model ->table('sj_feedback feedback force index (update_at)')-> where($where)->group('sectiontypeid')->field('count(*) as counts,sectiontypeid')->select();
		//$all = $model -> table('sj_feedback')-> where($where)->count();	
		//再次回复总数
		if($processed == 0){
			$str = 'all_post >= 2 and type = 2';
			$rid_data = $model->table('sj_thread')->where($str)->field('rid')->findAll();
			$ids = array();
			if ($rid_data) {
				foreach ($rid_data as $value) {
					$ids[] = $value['rid'];
				}
			}
			if($ids){
				$where['feedbackid'] = array('in', $ids);	
			}		
			$count_reply= $model -> table('sj_feedback feedback force index (update_at)')->where($where)->count();		
		}		
		$result = array();
		$all = 0;
		foreach($department_total as $k => $v){
			$result[$v['sectiontypeid']] = $v['counts'] ;
			$all += $v['counts'] ;
		}
		unset($department_total);	
		$result['all'] = $all;	
		//待分配
		$result[0] = $result[0]+$result[-1];
		exit(json_encode(array('result'=>$result,'count_reply'=>$count_reply)));
	}
	//软件反馈--已处理操作
	function processed_do(){
		$config = C('sectiontype');
		if($_POST){
			
			$model = new Model();
			$feedbackid = explode(',',$_POST['id']);
			$sectiontypeid = $_POST['sectiontypeid'];
			$where = array(
				'feedbackid' => array('in',$feedbackid)
			);
			$data = array(
				'sectiontypeid' => $sectiontypeid,
				'update_at'=>time(),
				'processed'=>1,
				'admin_id' => $_SESSION['admin']['admin_id']
			);
			if($sectiontypeid == 0){
				$data['status'] = 0;
			}
			$ret = $model -> table('sj_feedback')-> where($where)->save($data); 
			if($ret){
				if($sectiontypeid == 0){
					$this->writelog("删除了feedbackid为{$_POST['id']}的软件反馈",'sj_feedback',$_POST['id'],__ACTION__,'','del');
				}else{
					foreach($feedbackid as $v){
						$this->writelog("把feedbackid为{$v}分配到{$config[$sectiontypeid]}部门并且到已处理列表",'sj_feedback',$v,__ACTION__,'','edit');
					}
				}
				exit(json_encode(array('code'=>1,'msg'=>'操作成功')));
			}else{
				exit(json_encode(array('code'=>0,'msg'=>'操作失败')));
			}	
		}else{
			$config_msg = C('sectiontype_msg');		
			$this->assign('department', $config);
			$this->assign('department_msg', $config_msg);
			$this->assign('sectiontypeid', $_GET['sectiontypeid']);
			$this->assign('id', $_GET['id']);			
			$this -> display();
		}
	}
	//软件反馈批量回复
	function feedback_reback_all(){
		$model = new Model();
		$id = $_GET['id'];
		if($_POST){
			$content = $_POST['content'];
			$id_arr =  explode(',',$_POST['id']);
			foreach($id_arr as $id){
				$ret = $model->table('sj_feedback')->where(array("feedbackid" =>$id))->find();

				$ret1 = $model->table('pu_user')->where(array("userid" => $ret['userid']))->field('user_name')->find();
				$res = $model->table('sj_thread')->where(array('rid' => $id))->field('tid')->find();
				if (!$res) {
					$map = array(
						'rid' => $id , 
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
					$retitd = $model->table('sj_thread')->add($map);
				} else {
					$retitd = $res['tid'];
				}
				$postdata = array('tid' => $retitd , 'contents' => $content , 'dateline' => time() , 'status' => '1' , 'user_type' => '1' , 'system_userid' => $_SESSION['admin']['admin_id']);
				$model->table('sj_post')->add($postdata);
				$model->table('sj_feedback')->where(array("feedbackid" =>$id))->save(array('processed' => 1,'update_at'=>time(),'admin_id'=> $_SESSION['admin']['admin_id']));
               if($ret['processed']==1){
                   $this->writelog('添加了ID为['.$id.']的批量反馈回复','sj_feedback',$id,__ACTION__,'processed','edit');
               }else{
                   $this->writelog('添加了ID为['.$id.']的批量反馈回复','sj_feedback',$id,__ACTION__,'','edit');
               }
				
			}
			exit(json_encode(array('code'=>'1','msg'=>'回复成功')));			
		}else{
			$reason_list = $model->table('dev_reason')->where("status=1 and reason_type=8")->order("pos asc,id desc")->select();
			foreach($reason_list as &$val){
				if($val['content2']){
					$val['content2'] = explode('<br />', $val['content2']);
				}
			}	
			$this -> assign('reason_list',$reason_list);	
			$this -> assign('id',$id);	
			$this -> assign('processed',$_GET['processed']);	
			$this -> display('batch_recovery');
		}
	}
	//热门配置
	function update_hot(){
		$model = new Model();
		if($_GET['more']==1){
			$this->update_hot_more();
		}else{
			$where = array(
				'id' => $_GET['id'],
			);
			$map = array('hot'=>$_GET['hot'],'apply_rules_time'=>time());
			$ret = $model -> table('sj_soft_comment') -> where($where)->save($map);
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if(!$ret){
				$this -> error('操作失败');
			}else{
				if($_GET['hot'] == 1){
					$log = "置为热门";
				}else{
					$log = "取消热门";
				}
				$this->writelog('信息管理--软件评论ID为'.$_GET['id']."设置成【{$log}】",'sj_soft_comment',$_GET['id'],__ACTION__ ,"","edit");
				$this -> success('操作成功');
			}
		}
	}

	//批量置为热门
	public function update_hot_more(){
		if(empty($_GET['ids'])){
			$this->error('请选择评论');
		}
		$ids = explode(',',$_GET['ids']);
		$ids_str = implode("','",$ids);
		$where = array(
			'id' => array('exp'," in ('{$ids_str}')"),
		);
		$model = new Model();
		$map = array('hot'=>1,'apply_rules_time'=>time());
		$ret = $model -> table('sj_soft_comment') -> where($where)->save($map);
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
		if(!$ret){
			$this -> error('操作失败');
		}else{
			$this->writelog('信息管理--软件评论ID为'.$_GET['ids']."批量设置成【置为热门】",'sj_soft_comment',$_GET['ids'],__ACTION__ ,"","edit");
			$this -> success('操作成功');
		}
	}

	//信息管理--软件评论--评论回复列表
	function reply_list(){
		$model = D("Dev.Message");
		$where = array(
			'reply_type' => 1,
		);
		if(!isset($_GET['search'])){
			$_POST['type'] = 2;
			$_POST['content'] = '';
			$this->pub_set_content();
		}
		if($_GET['is_replace']){
			$_GET['processed']=-5;
			$this->assign('is_replace',$_GET['is_replace']);
		}
		$processed  = $_GET['processed'] ? $_GET['processed'] : 0;
		
		$where['processed'] = $processed;
		$this -> assign('processed',$processed);
		if(isset($_GET['package'])){
			$this -> assign('package',$_GET['package']);
		}
		if(isset($_GET['feature_name'])){
			$this -> assign('feature_name',$_GET['feature_name']);
		}
		if(isset($_GET['choose_type'])){
			$this -> assign('choose_type',$_GET['choose_type']);
		}
		if(isset($_GET['everybody_say'])){
			$this -> assign('everybody_say',$_GET['everybody_say']);
		}
		$day = strtotime(date("Y-m-d",time()));
		if (empty($_GET['begintime']) && empty($_GET['endtime'])) {
			$_GET['begintime'] = date('Y-m-d H:i:s', $day-7*86400);
			$_GET['endtime'] = date('Y-m-d H:i:s', $day+86399);
		}	
		$this->check_where($where, 'softid');
		$this->check_where($where, 'softid');
		$this->check_where($where, 'comment_id');
		$this->check_where($where, 'imei');
		$this->check_where($where, 'reply_ip');
		$this->check_where($where, 'reply_pid');
		//$this->check_where($where, 'contents', 'isset', 'like');
		$_POST['type'] = 2;
		$content = $this->pub_get_content(1);
		$content = str_replace('%','\%',addslashes($content));
		$where['contents'] = array('like', "%{$content}%");


		$this->check_where($where, 'user_name', 'isset', 'like');	
		$this->check_range_where($where, 'begintime', 'endtime', 'dateline', true);	

		$res = $model ->get_reply_list($where);
		

		if($_GET['word_num']){
			$this->assign('word_num',$_GET['word_num']);
		}
		if($_GET['repeat_num']){
			$this->assign('repeat_num',$_GET['repeat_num']);
		}

		//var_dump($res);
		$this -> assign('list',$res['list']);
		$this -> assign('soft_info',$res['soft_info']);
		$this -> assign('feature_info',$res['feature_info']);
		$this -> assign('comment_info',$res['comment_info']);
		$this -> assign('dinfo',$res['dinfo']);
		$this -> assign('admin_info',$res['admin_info']);
		$this -> assign('admin_reply',$res['admin_reply']);
		$this -> assign('reason',$res['reason']);
		$this -> assign('total',$res['total']);
		$_POST['type'] = 2;
		if($this->pub_get_content(1)!=''){
			$this->assign('replace_button',1);
		}
		// var_dump($res['comment_extent_data']);
		$this -> assign('comment_extent_data',$res['comment_extent_data']);
		$this -> assign('replace_data',$res['replace_data']);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);		
		$this -> assign('page', $res['page']->show());		
		$this->display();
	}
	//信息管理--软件评论--评论回复列表--回复删除
    public function reply_del(){
		$model = D("Dev.Message");
		if (!empty($_POST)) {
			$ret = $model -> del_reply_post();	
			if($ret){
				$pid = $_POST['id'] ? $_POST['id'] : $_POST['ids'];
				$this->writelog('对pid['.$pid.']的评论回复进行删除', 'sj_post',$pid,__ACTION__ ,"","del");
			}			
			exit(json_encode(array('code'=>'1','msg'=>"删除成功",'return_url'=>$_SERVER['HTTP_REFERER'])));
		} else {
			$reasons = $model -> get_reason(7);
			$this->assign('reasons', $reasons);
			$this->assign('id', $_GET['id']);
			$this->assign('processed', $_GET['processed']);
			$this->assign('comment_id', $_GET['comment_id']);
			$this->assign('ids', $_GET['ids']);
			$this->assign('act', 'reply_del');
			$this->display('delete');
		}
    }
	//回复信息查看
	public function pub_reply_contents(){
		$model = D("Dev.Message");
		if($_GET['processed'] > 0){
			list($list,$admin_info,$dev_arr) = $model -> get_reply();
			foreach($list as $k=>$v){
				if($v['user_type'] == 1){
					echo "安智（{$admin_info[$v['system_userid']]['admin_user_name']}）回复：<br/>";
				}else if($v['user_type'] == 2){
					echo "开发者客服（{$dev_arr[$v['dev_id']]['user_name']}）回复：<br/>";
				}else{
					echo "软件评论回复：<br/>";
				}
				echo date('Y-m-d H:i:s',$v['dateline'])."<br/>";
				echo $v['contents']."<br/>";
			}	
		}else{
			$where = array('pid' => $_GET['id'],'reply_type' => 1);
			$reply = $model->table('sj_post') -> where($where) ->field('pid,dateline,contents')-> find();
			echo $reply ? date("Y-m-d H:i:s",$reply['dateline']) ."<br/>". $reply['contents'] ."<br/>" : '';
		}
		
	}
	//评论回复列表--回复
	function reply_post(){
		$model = D("Dev.Message");
		if($_POST){
			$ret = $model -> reply_post();
			if($ret){
			   $this->writelog('软件评论回复-回复了ID为['.$_POST['id'].']的评论回复','sj_soft_comment',$_POST['id'],__ACTION__,'processed','edit');
				exit(json_encode(array('code'=>'1','msg'=>'回复成功')));	
			}else{
				exit(json_encode(array('code'=>'0','msg'=>'回复失败')));	
			}			
		}else{
			list($list,$admin_info,$dev_arr) = $model -> get_reply();
			$this->assign('list', $list);	
			$this->assign('admin_info', $admin_info);	
			$this->assign('dev_arr', $dev_arr);	
			//回复配置内容	
			$reasons = $model -> get_reason(10);
			$this->assign('reason_list', $reasons);			
			$this->assign('REFERER', $_SERVER['HTTP_REFERER']);			
			$this->display();			
		}
	}
	//安智市场-手机--信息管理--头像昵称管理--头像
	function comment_pictures(){
		$model = D("Dev.Message");
		if(!empty($_POST) ){
			$res = $model -> upload_img();
			if($res['code'] == 1){
				$this->writelog('添加了ID为['.$res['id'].']的头像','sj_comment_pictures',$res['id'],__ACTION__ ,"","add");
			}
			exit(json_encode($res));
		}else{
			list($list,$Page) = $model -> get_comment_pictures();	
			$this->assign('list', $list);
			$this -> assign('page', $Page->show());				
			$this->display();			
		}
	} 
	//安智市场-手机--信息管理--头像昵称管理--删除头像
	function del_comment_pictures(){
		$model = new Model();
		$where = array('status'=>1,'id'=>array('in',$_GET['id']));
		$map = array('status'=>0,'update_tm'=>time());
		$ret = $model -> table('sj_comment_pictures')->where($where)->save($map);
		if($ret){
			$this->writelog('删除了ID为['.$_GET['id'].']的头像','sj_comment_pictures',$_GET['id'],__ACTION__ ,"","del");
			$this->success('操作成功');
		}else{
			$this -> error('操作失败');
		}
	}
	//软件反馈--文件上传
	function pub_uploadfile_to_tmp(){
		$model = D("Dev.Uploadfile");
		$model -> uploadfile_to_tmp();
	}

	public function replace_comment(){
		$ids = explode(',',$_GET['ids']);
		if($_POST){
			$p_ids = explode(',',$_POST['ids']);
			$c_type = $_POST['c_type']?$_POST['c_type']:1;
			if(!$p_ids){
				exit(json_encode(array('code'=>0,'msg'=>'未选择评论')));
			}
			$model = M('');
			$where_id = implode("','",$p_ids);
			$_POST['type'] = $c_type;
			$content = $this->pub_get_content(1);
			$content = addslashes($content);
			$rcontent = addslashes($_POST['rcontent']);
			$len = mb_strlen($_POST['rcontent'],'UTF8');
			if($len > 100){
				exit(json_encode(array('code'=>0,'msg'=>"替换内容最多100字，现{$len}字")));
			}
			$table = 'sj_soft_comment';
			$id = 'id';
			$content_field = 'content';
			if($c_type == 2){
				$table = 'sj_post';
				$id = 'pid';
				$content_field = 'contents';
			}
			$comment = get_table_data(array("{$id}"=>array('exp'," in('{$where_id}')")),"{$table}",$id,"{$id},{$content_field}");
			$sql = "update {$table} set {$content_field} = REPLACE({$content_field},'{$content}','{$rcontent}') where {$id} in('{$where_id}')";
			$model->query($sql);
			$admin_id = $_SESSION['admin']['admin_id'];
			$task_client = get_task_client();
			foreach($comment as $k=>$v){
				$comment_extent =  $model->table('sj_soft_comment_extent')->where("c_p_id = '{$v[$id]}'  and c_type = '{$c_type}'")->field('id,c_p_id')->select();
				$data = array(
					'content' => $v[$content_field],
					'admin_id' => $admin_id,
				);
				$data['update_tm'] =  time();
				if($comment_extent){
					$data['type'] = array('exp'," type | 2");
					$model->table('sj_soft_comment_extent')->where("c_p_id = '{$v[$id]}'  and c_type = '{$c_type}'")->save($data);
				}else{
					$data['c_p_id'] = $v[$id];
					$data['type'] = 2;
					$data['c_type'] = $c_type;
					$data['create_tm'] = time();
					$model->table('sj_soft_comment_extent')->add($data);
				}

				//调用回复字符处理worker
				$send_data = array(
					'id' => $v[$id],
					'type' => $c_type
				);
				$task_client->doBackground("comment_deal", json_encode($send_data));
			}
			exit(json_encode(array('code'=>200,'msg'=>'成功')));
		}
		$this->assign('count',count($ids));
		$this->assign('ids',$_GET['ids']);
		$_POST['type'] = $_GET['content_type'];
		$content = $this->pub_get_content(1);
		$this->assign('content',$content);
		$this->assign('content_type',$_GET['content_type']);
		$this->assign('c_type',$_GET['c_type']);
		$this->display();
	}

	public function pub_set_content(){
		$type = $_POST['type'];
		$admin_id = $_SESSION['admin']['admin_id'];
		if($type==1){
			$name = 'dev_message_content';
		}else{
			$name = 'dev_reply_content';
		}
		$content = $_POST['content'];
		$file = "/tmp/{$name}_{$admin_id}.log";
		file_put_contents($file, $content);
	}

	public function pub_get_content($get_type=''){
		$type = $_POST['type'];
		$admin_id = $_SESSION['admin']['admin_id'];
		if($type==1){
			$name = 'dev_message_content';
		}else{
			$name = 'dev_reply_content';
		}
		$file = "/tmp/{$name}_{$admin_id}.log";
		$content = file_get_contents($file);
		if($get_type == 1){
			return $content;
		}else{
			echo json_encode(array('content'=>$content));
		}
	}
}
?>
