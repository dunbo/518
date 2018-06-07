<?php
/**
 * 采集视频管理
 */
class VideoCollectManageAction extends CommonAction {
    const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";
    private $colle_table='colle_contentlist';
    private $sj_colle_table='sj_colle_contentlist';
    private $pic_path_abs = "/thirdall";
    private $video_path_abs = "/thirdall";
    
//     public function _initialize() {
//     	echo "测试环境暂时不能用";die;
//     }
    //视频列表
    public function video_list() {
    	$s_package		=	trim($_GET['s_package']);
    	$s_softname		=	trim($_GET['s_softname']);
    	$video_name		=	trim($_GET['s_video_name']);
    	$video_source	=	trim($_GET['video_source']);
    	$check_status	=	$_GET['check_status']?intval($_GET['check_status']):0;
    	$order			=	$_GET['order']?intval($_GET['order']):0;
    	$colle_table	=	$this->colle_table;
    	$sj_colle_table	=	$this->sj_colle_table;
    	
    	$model = D('Dev.VideoCollectManage');
    	//$model = M('');
    	$where = " 1=1 ";
    	if($check_status) {
    		$where .= " and b.status = {$check_status}";
    	}else {
    		$where .= " and (ISNULL(b.status) or b.status = 0)";
    	}
    	if($s_package) {
    		$where .= " and b.package = '{$s_package}'";
    	}
    	if($s_softname) {
    		$where .= " and b.softname like '%{$s_softname}%'";
    	}
    	if($video_source) {
    		$where .= " and a.taskname = '{$video_source}'";
    	}
    	if($video_name) {
    		$where .= " and a.title like '%{$video_name}%'";
    	}
    	
        //内容属性，标签搜索
        if(in_array($check_status,array(0,1,2))){
            //搜索条件
            if($_GET['content_level']) $where .= " and b.cont_level = '{$_GET['content_level']}'";
            if($_GET['content_nature']) $where .= " and b.cont_quality = '{$_GET['content_nature']}'";
            if($_GET['cont_src']) $where .= " and b.cont_src = '{$_GET['cont_src']}'";
            //用户倾向 $_GET['user_tend']
            if($_GET['user_tend']) $where .= " and b.user_tend = '{$_GET['user_tend']}'";
            if($_GET['s_content_column']){
                $where .= " and b.cont_column like '%,{$_GET['s_content_column']},%'";
                $this->assign('s_content_column',$_GET['s_content_column']);
            }
            if(!is_null($_GET['s_status_tag'])){
                $where .= " and b.tagstatus = '{$_GET['s_status_tag']}'";
                $this->assign('s_status_tag',$_GET['s_status_tag']);
            } 

            $column_model = D('Sj.ContColumn');
            $cont_model = D('Sj.ContAttribute');
            $column_list = $column_model->getall_cont_column();
            $content_xz = content_nature_selecttag($_GET['content_nature']);
            $content_zl = content_level_selecttag($_GET['content_level']);
            #内容来源
            $config = array(
                'key'=>'CONTENT_SOURCE',
                'type'=>'select',
                'tag_id'=>'cont_src',
                'tag_name'=>'cont_src',
                'tag_tip'=>'全部',
                'default'=> isset($_GET['cont_src']) ? $_GET['cont_src'] : 0,

            );
            #用户倾向
            $config_user = array(
                'key'=>'USER_TEND',
                'type'=>'select',
                'tag_id'=>'user_tend',
                'tag_name'=>'user_tend',
                'tag_tip'=>'全部',
                'default'=> isset($_GET['user_tend']) ? $_GET['user_tend'] : 0,
            );
            $user_tend = $cont_model -> get_user_tend($config_user);
            $con_source = content_html_unit($config);
            $this->assign('user_tend',$user_tend);
            $this->assign('con_source',$con_source);
            $this->assign('content_zl', $content_zl);
            $this->assign('content_xz', $content_xz);
            $this->assign('column_list', $column_list);

            $content_level = C('CONTENT_LEVEL');
            $content_quality = C('CONTENT_NATURE');
            $content_source = C('CONTENT_SOURCE');
            $user_tend = C('USER_TEND');
        }

        // $sql = "select a.sid,a.source_createtime,a.taskname,a.title,a.summary,a.video_pic,a.video_local_path, b.package,b.softname,b.status,b.video_pic as video_pic_2,b.title as title_2,b.summary as summary_2 from {$colle_table} as a left join {$sj_colle_table} as b on a.sid = b.sid where ";
    	$sql = "select a.sid,a.source_createtime,a.taskname,a.title,a.summary,a.video_pic,a.video_local_path, b.package,b.softname,b.status,b.video_pic as video_pic_2,b.title as title_2,b.summary as summary_2,b.cont_level,b.cont_quality,b.cont_column,b.cont_src,b.user_tend,b.tagstatus from {$colle_table} as a left join {$sj_colle_table} as b on a.sid = b.sid where ";  
    	
    	$sql_count = "select count(*) as count from {$colle_table} as a left join {$sj_colle_table} as b on a.sid = b.sid where ";
    	$sql_count = $sql_count.$where;
    	
        $count = $model->query($sql_count);
        $count = $count[0]['count'];
        import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		
        $o_param = preg_replace('/order=[^&]{1,}/','',$param);
        $on_order = $order?0:1;
        $o_param = $o_param."&order=".$on_order;
        $this->assign('o_param', $o_param);
        if($order){
        	$orderby = " order by a.source_createtime asc ";
        }else{
        	$orderby = " order by a.source_createtime desc ";
        }
		$limit	=	" limit ".$Page->firstRow.','.$Page->listRows;
		$sql	=	$sql.$where.$orderby.$limit;	
        $list	=	$model->query($sql);
        foreach ($list as $k => $v){
        	if($v['summary_2']) {
        		$str_num =  strlen($v['summary_2']);
        		if($str_num > 90) {
        			$list[$k]['summary_substr'] = mb_substr($v['summary_2'],0,30,"utf-8")."...";
        			$list[$k]['summary'] = $v['summary_2'];
        		}else{
        			$list[$k]['summary_substr'] = $v['summary_2'];
        			$list[$k]['summary'] = $v['summary_2'];
        		}
        	}else {
        		$str_num =  strlen($v['summary']);
        		if($str_num > 90) {
        			$list[$k]['summary_substr'] = mb_substr($v['summary'],0,30,"utf-8")."...";
        		}else {
        			$list[$k]['summary_substr'] = $v['summary'];
        		}
        	}

            //添加内容选项
            if(in_array($check_status,array(0,1,2))){
                //展示内容选项
                $list[$k]['content_select'] = '';
                if($list[$k]['cont_level']) $list[$k]['content_select'] .= '<li style="list-style:none;">内容质量：'.$content_level[$list[$k]['cont_level']]."</li>";
                if($list[$k]['cont_quality']) $list[$k]['content_select'] .= '<li style="list-style:none;">内容性质：'.$content_quality[$list[$k]['cont_quality']];
                if($list[$k]['cont_src']) $list[$k]['content_select'] .= '<li style="list-style:none;">内容来源：'.$content_source[$list[$k]['cont_src']];
                //用户倾向
                if($list[$k]['user_tend']) $list[$k]['content_select'] .= '<li style="list-style:none;">用户倾向：'.$user_tend[$list[$k]['user_tend']];
                if($list[$k]['cont_column']){
                    $cont_column_num = explode(',',trim($list[$k]['cont_column'],','));
                    $column_select = '';
                    foreach ($column_list as $column_value) {
                        if(in_array($column_value['cont_id'],$cont_column_num)){
                            $column_select .= $column_value['name'].',';
                        }
                    }
                    $list[$k]['content_select'] .= '<li style="list-style:none;">内容栏目：'.$column_select;
                }
                if($list[$k]['tagstatus'] == 1) $list[$k]['content_select'] .= '<li style="list-style:none;">标签状态：是';
            }

        }
        //print_r($list);die;
        $this->assign('page', $show);
        $this->assign('total', $count);
        $this->assign('order', $order);
        $this->assign('s_package', $s_package);
        $this->assign('s_softname', $s_softname);
        $this->assign('video_name', $video_name);
        $this->assign('video_source', $video_source);
        $this->assign('check_status', $check_status);
        $this->assign('list', $list);
        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('page', $Page->show());
        $this->display();
    }
    
    public function video_edit() {
    	$sid			=	$_REQUEST['sid'];
    	$colle_table	=	$this->colle_table;
    	$sj_colle_table	=	$this->sj_colle_table;
    	$model = D('Dev.VideoCollectManage');
    	// $column_model = D('Sj.ContColumn');
    	// $column_list = $column_model->getall_cont_column();
    	if($_GET['show_video']==1){
    		$video_data=$model->table($colle_table)->where(array('sid'=>$sid))->find();
    		$this->assign('video_data', $video_data);
    		$this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
    		$this->display('video_show');
    	}
    	if($_POST) {
    		$sid			=	$_POST['sid'];
    		$video_title	=	trim($_POST['video_title']);
    		$package		=	trim($_POST['package']);
    		$video_summary	=	trim($_POST['video_summary']);
            $download_guide =   $_POST['download_guide'] ? $_POST['download_guide'] : 0;
            $dg_type        =   $_POST['dg_type'] ? $_POST['dg_type'] : 0;
            $dg_screen      =   $_POST['dg_screen'] ? $_POST['dg_screen'] : 0;
            $dg_time_m      =   $_POST['dg_time_m'] ? $_POST['dg_time_m'] : 0;
            $dg_time_s      =   $_POST['dg_time_s'] ? $_POST['dg_time_s'] : 0;
            $dg_long        =   $_POST['dg_long'] ? $_POST['dg_long'] : 0;
            $dg_position    =   $_POST['dg_position'] ? $_POST['dg_position'] : 0;
    		$cont_level		=	$_POST['content_level'] ? $_POST['content_level'] : 0;
    		$cont_nature	=	$_POST['content_nature'] ? $_POST['content_nature'] : 0;
    		$cont_column	=	$_POST['cont_column_str'] ? ','.$_POST['cont_column_str'] : '';
            $con_source     =   $_POST['con_source'] ? $_POST['con_source'] : 0;
    		$user_tend		=	$_POST['user_tend'] ? $_POST['user_tend'] : 0;
            $cont_tags = $_POST['content_tags'];
    		$tagstatus = $_POST['content_tags'] ? 1 : 0;

    		if( strlen($video_title) > 60 ) {
    			$this->error("视频名称不能超过20个汉字");
    		}
    		if(!$package) {
    			$this->error("包名不能为空");
    		}
    		$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
			if( !$pkg_data ) {
				$this->error("包名不存在");
			}
    		
            $data['download_guide'] = $download_guide;
            if ($download_guide==1) {
                if(empty($dg_type)) $this->error('请选择下载引导类型');
                $data['dg_type'] = $dg_type;

                if(empty($dg_screen)) $this->error('请选择下载引导全屏/非全屏展示');
                $data['dg_screen'] = $dg_screen;
                
                //if(empty($dg_time_m)) $this->error('请输入显示时间分钟数');
                if (!empty($dg_time_m) && !preg_match("/^\d*$/", $dg_time_m)) {
                    $this->error('显示时间分钟数请输入大于等于0的的整数');
                }
                $data['dg_time_m'] = $dg_time_m;
                
                if(empty($dg_time_m) && empty($dg_time_s)){
                    $this->error('0秒为无效时间，请重新输入');
                }
                if (!empty($dg_time_s) && !preg_match("/^\d*$/", $dg_time_s)) {
                    $this->error('显示时间秒数请输入大于等于0的的整数');
                }
                if ($dg_time_s>59) {
                    $dg_time_s = 59;
                }
                $data['dg_time_s'] = $dg_time_s;
                
                if(empty($dg_long)) $this->error('请输入显示时长秒数');
                if (!preg_match("/^[1-9]\d*$/", $dg_long)) {
                    $this->error('显示时长秒数请输入大于0的的整数');
                }
                $data['dg_long'] = $dg_long;
                
                if(empty($dg_position)) $this->error('请选择下载引导显示位置');
                $data['dg_position'] = $dg_position;
            }

			if(empty($cont_level)) $this->error('请选择内容质量');
			if(empty($cont_nature)) $this->error('请选择内容性质');
			//if(empty($cont_column)) $this->error('请选择内容栏目');
			
			$data['package']	=	$package;
			$data['softname']	=	$pkg_data['softname'];

			$data['cont_level']		=	$cont_level;
			$data['cont_quality']	=	$cont_nature;
			$data['cont_column']	=	$cont_column;
            $data['cont_src']       =   $con_source;
			$data['user_tend']		=	$user_tend;
            $data['tagstatus']      =   $tagstatus;
    		$width = 1256; $height = 706;
    		$date	=	date("Ym/d/");
    		if($_FILES['video_pic']['tmp_name']) {
    			$pic_path = getimagesize($_FILES['video_pic']['tmp_name']);
    			if($pic_path[0] != $width || $pic_path[1] != $height){
    				$this->error("分辨率图标大小不符合条件");
    			}
    			if( !in_array($_FILES['video_pic']['type'], array('image/png','image/jpg','image/jpeg')) ) {
    				$this->error("请添加图片格式为：jpg，png的弹窗图片");
    			}
    			$up_file=$this->upload_pic();
    			$data['video_pic'] = $up_file['video_pic'];
    			$data['video_pic_30'] = $up_file['video_pic_30'];
    			$data['video_pic_60'] = $up_file['video_pic_60'];
    		}
    		$data['title']		=	$video_title;
    		$data['summary']	=	$video_summary;
    		
    		$colle		=	$model->table($colle_table)->where(array('sid'=>$sid))->find();
    		$sj_colle	=	$model->table($sj_colle_table)->where(array('sid'=>$sid))->find();
    		
    		if($sj_colle) {
    			$data['update_tm'] = time();
    			$ret = $model->table($sj_colle_table)->where(array('sid'=>$sid))->save($data);
                $this->writelog("编辑了ID为{$sid}的采集视频数据",$sj_colle_table,$sid,__ACTION__,'','edit');
                $cvid = $sid;
    		}else {
    			if(!$data['video_pic']) {
    				$data['video_pic'] = $this->pic_path_abs.$colle['video_pic'];
    			}
    			$data['sid']		=	$sid;
    			$data['taskname']	=	$colle['taskname'];
    			$data['video_local_path'] = $this->video_path_abs.$colle['video_local_path'];
    			$data['status']		=	0;
    			$data['update_tm']	=	time();
    			$data['add_tm']		=	time();
    			if(!$data['video_pic_30'] || !$data['video_pic_60']){
	    			if( C('is_test') == 1 ) {
	    				$suf = '/data/caiji/thirdall';
	    			}else{
	    				$suf = '/data/att/m.goapk.com/thirdall';
	    			}
	    			$file = array ( 'video_pic' => "@{$suf}{$colle['video_pic']}");
	    			$file_res	=	$this->upload_pic_do($file,'video_pic');
	    			if(!$file_res){
	    				$this->error('采集资源未推送到518服务器上');
	    			}
	    			$up_file	=	$this->upload_pic_do($file_res,'video_backstage_handle');
	    			$data['video_pic']	  = $up_file['video_pic'];
	    			$data['video_pic_30'] = $up_file['video_pic_30'];
	    			$data['video_pic_60'] = $up_file['video_pic_60'];
    			}
    			$ret = $model->table($sj_colle_table)->add($data);
                $this->writelog("添加了ID为{$ret}的采集视频数据",$sj_colle_table,$ret,__ACTION__,'','add');
                if($ret) $cvid = $ret;
    		}
            //添加内容标签映射数据
            if($cvid){
                $tag_data = array();
                $tag_data['pkgname'] = $data['package'];
                $tag_data['cvid'] = $cvid;
                $tag_data['tags'] = $cont_tags;
                //先删除原有映射内容
                $tag_res = M('')->table('cont_tags_content')->where(array('cvid'=>$tag_data['cvid']))->delete();
                if($tag_res){
                    $this->writelog('内容标签映射表删除关联数据，cvid为'.$tag_data['cvid'].'。', 'cont_tags_content',$tag_data['cvid'],__ACTION__ ,'','delete');
                }
                if($cont_tags){//添加新数据
                    $cont_model = D('Sj.ContAttribute');
                    $tag_result = $cont_model->add_cont_attrribute($tag_data);
                    if($tag_result){
                        $this->writelog('内容标签映射表添加关联数据，cvid为'.$tag_data['cvid'].'。', 'cont_tags_content',$tag_data['cvid'],__ACTION__ ,'','add');
                    }
                } 
            }

    		if($ret){
    			$this->success('操作成功');
    		}else{
    			$this->error('操作失败');
    		}
    	}else {
    		$sj_colle	=	$model->table($sj_colle_table)->where(array('sid'=>$sid))->find();
    		
    		if($sj_colle) {
    			$info = $sj_colle;
    		}else{
    			$info = $model->table($colle_table)->where(array('sid'=>$sid))->find();
    			$info['video_pic'] = $this->pic_path_abs.$info['video_pic'];
    		}
            //获取此条内容性质,质量，栏目
            // ===================================
            $cont_model = D('Sj.ContAttribute');   
            //内容标签
            $tag_result = M('') -> table('cont_tags_content')->where(array('cvid'=>$sid))->select();
            $second_tag = $three_rank = array();
            foreach ($tag_result as $tag_key => $tag_value) {
                array_push($second_tag,$tag_value['second_tagid']);
                if($tag_value['three_tagid']) array_push($second_tag,$tag_value['three_tagid']);
                if($tag_value['tagidlevel']){
                    $thlrank = $tag_value['three_tagid'].'_'.$tag_value['tagidlevel'];
                    array_push($three_rank, $thlrank);
                }  
            }
            $second_tag = array_unique($second_tag);
            $cont_column_num = explode(',', $sj_colle['cont_column']);
            $sj_colle['cont_src'] = $sj_colle['cont_src'] ? $sj_colle['cont_src'] : 2;
            list($content_xz,$content_zl,$con_source,$user_tend,$content_lm,$content_tag) = $cont_model -> show_conattibute($sj_colle['cont_quality'],$sj_colle['cont_level'],$sj_colle['cont_src'],$sj_colle['user_tend'],$cont_column_num,$second_tag,$three_rank);
            $this->assign('user_tend', $user_tend);
            $this->assign('con_source', $con_source);
            $this->assign('content_zl', $content_zl);
            $this->assign('content_xz', $content_xz);
            $this->assign('content_tag',$content_tag);
            $this->assign('content_lm',$content_lm);
            // ===================================
    		$this->assign('info', $info);
    		$this->display();
    	}
    }
    
    public function pub_check_package() {
        $package = $_GET['package'];
        $find = $this->package_search($package);
        if ($find) {
            $this->ajaxReturn(1, $find, 1);
        } else {
            $this->ajaxReturn(0, '', 0);
        }
    }
    
    private function package_search($package) {
        if (!$package)
            return 0;
        $where = array(
            'package' => $package,
            'status' => 1,
            'hide' => array('in', '1,1024'),
        );
        $find = M("")->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
        if ($find)
            return $find['softname'];
        return 0;
    }
    
	function create_h263_url($task_data){
		$task_client = get_task_client();
		if($task_data['url']){
			$task_client->doBackground('video_change_format', json_encode($task_data));
		}
	}

	function change_status(){
		$sid_str		=	$_REQUEST['sid'];
		$sid_arr		=	explode(',', $sid_str);
		$check_status	=	$_REQUEST['check_status'];
		$sysc			=	$_REQUEST['sysc'];
		$package		=	trim($_REQUEST['package']);
		$model = D('Dev.VideoCollectManage');
		$colle_table	=	$this->colle_table;
		$sj_colle_table	=	$this->sj_colle_table;
		//驳回
		if($check_status == 2){
			foreach ($sid_arr as $v) {
				$sj_colle	=	$model->table($sj_colle_table)->where(array('sid'=>$v))->find();
				if( empty($sj_colle) ){
					$colle	=	$model->table($colle_table)->where(array('sid'=>$v))->find();
					$map = array(
							'sid'				=>	$colle['sid'],
							'taskname'			=>	$colle['taskname'],
							'softname'			=>	'',
							'package'			=>	'',
							'title'				=>	$colle['title'],
							'summary'			=>	$colle['summary'],
							'video_local_path'	=>	$this->video_path_abs.$colle['video_local_path'],
							'video_pic'			=>	$this->pic_path_abs.$colle['video_pic'],
							'status'			=>	2,
							'update_tm'			=>	time(),
							'add_tm'			=>	time(),
					);
					$res = $model->table($sj_colle_table)->add($map);
				}else {
					$map = array(
						'status'	=>	2,
						'update_tm'	=>	time(),
					);
					$res = $model->table($sj_colle_table)->where(array('sid'=>$v))->save($map);
				}
			}
		}
		//撤销
		if($check_status == 0) {
			$where = array('sid'=>array('in', $sid_str));
			$res = $model->table($sj_colle_table)->where($where)->save(array('status'=>$check_status,'update_tm'=>time()));
		}
		//通过
		if($check_status == 1) {
			$list	=	$model->table($sj_colle_table)->where(array('sid'=>array('in', $sid_str)))->select();
			if(count($sid_arr) == 1) {
				if($_POST) {
					if(!$package) {
						$this->error('包名不能为空');
					}
					$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
					if(!$pkg_data) {
						$this->error('包名不存在');
					}
					$cont_level		=	$_POST['content_level'] ? $_POST['content_level'] : 0;
					$cont_nature	=	$_POST['content_nature'] ? $_POST['content_nature'] : 0;
					$cont_column	=	$_POST['cont_column_str'] ? ','.$_POST['cont_column_str'] : '';
                    $con_source     =   $_POST['con_source'] ? $_POST['con_source'] : 0;
					$user_tend		=	$_POST['user_tend'] ? $_POST['user_tend'] : 0;
                    $cont_tags = $_POST['content_tags'];//内容标签
                    $tagstatus = empty($_POST['content_tags']) ? 0 : 1;//内容标签状态

                    $download_guide =   $_POST['download_guide'] ? $_POST['download_guide'] : 0;
                    $dg_type        =   $_POST['dg_type'] ? $_POST['dg_type'] : 0;
                    $dg_screen      =   $_POST['dg_screen'] ? $_POST['dg_screen'] : 0;
                    $dg_time_m      =   $_POST['dg_time_m'] ? $_POST['dg_time_m'] : 0;
                    $dg_time_s      =   $_POST['dg_time_s'] ? $_POST['dg_time_s'] : 0;
                    $dg_long        =   $_POST['dg_long'] ? $_POST['dg_long'] : 0;
                    $dg_position    =   $_POST['dg_position'] ? $_POST['dg_position'] : 0;

                    $data['download_guide'] = $download_guide;
                    if ($download_guide==1) {
                        if(empty($dg_type)) $this->error('请选择下载引导类型');
                        $data['dg_type'] = $dg_type;

                        if(empty($dg_screen)) $this->error('请选择下载引导全屏/非全屏展示');
                        $data['dg_screen'] = $dg_screen;
                        
                        //if(empty($dg_time_m)) $this->error('请输入显示时间分钟数');
                        if (!empty($dg_time_m) && !preg_match("/^\d*$/", $dg_time_m)) {
                            $this->error('显示时间分钟数请输入大于等于0的的整数');
                        }
                        $data['dg_time_m'] = $dg_time_m;
                        
                        if(empty($dg_time_m) && empty($dg_time_s)){
                            $this->error('0秒为无效时间，请重新输入');
                        }
                        if (!empty($dg_time_s) && !preg_match("/^\d*$/", $dg_time_s)) {
                            $this->error('显示时间秒数请输入大于等于0的的整数');
                        }
                        if ($dg_time_s>59) {
                            $dg_time_s = 59;
                        }
                        $data['dg_time_s'] = $dg_time_s;
                        
                        if(empty($dg_long)) $this->error('请输入显示时长秒数');
                        if (!preg_match("/^[1-9]\d*$/", $dg_long)) {
                            $this->error('显示时长秒数请输入大于0的的整数');
                        }
                        $data['dg_long'] = $dg_long;
                        
                        if(empty($dg_position)) $this->error('请选择下载引导显示位置');
                        $data['dg_position'] = $dg_position;
                    }
					
					if(empty($cont_level)) $this->error('请选择内容质量');
					if(empty($cont_nature)) $this->error('请选择内容性质');
					//if(empty($cont_column)) $this->error('请选择内容栏目');
						
					$data['cont_level']		=	$cont_level;
					$data['cont_quality']	=	$cont_nature;
					$data['cont_column']	=	$cont_column;
                    $data['cont_src']       =   $con_source;
					$data['user_tend']		=	$user_tend;
                    $data['tagstatus']      =   $tagstatus;
					
					$colle	=	$model->table($colle_table)->where(array('sid'=>$sid_str))->find();
					if( !empty($list) ) {
						$data['softname']	=	$pkg_data['softname'];
						$data['package']	=	$package;
						$data['update_tm']	=	time();
						$model->table($sj_colle_table)->where(array('sid'=>$sid_str))->save($data);
                        $cvid = $sid_str; //内容标签映射表中cvid
					}else {
						if( C('is_test') == 1 ) {
							$suf = '/data/caiji/thirdall';
						}else{
							$suf = '/data/att/m.goapk.com/thirdall';
						}
						$file = array ( 'video_pic' => "@{$suf}{$colle['video_pic']}");
						$file_res	=	$this->upload_pic_do($file,'video_pic');
						if(!$file_res){
							$this->error('采集资源未推送到518服务器上');
						}
						$up_file	=	$this->upload_pic_do($file_res,'video_backstage_handle');
						$data['video_pic']	  = $up_file['video_pic'];
    					$data['video_pic_30'] = $up_file['video_pic_30'];
    					$data['video_pic_60'] = $up_file['video_pic_60'];
						
						$data['sid']		=	$colle['sid'];
						$data['taskname']	=	$colle['taskname'];
						$data['softname']	=	$pkg_data['softname'];
						$data['package']	=	$package;
						$data['title']		=	$colle['title'];
						$data['summary']	=	$colle['summary'];
						$data['video_local_path']	=	$this->video_path_abs.$colle['video_local_path'];
						$data['video_pic']	=	$this->pic_path_abs.$colle['video_pic'];
						$data['status']		=	0;
						$data['update_tm']	=	time();
						$data['add_tm']		=	time();
                        $cvid_result = $model->table($sj_colle_table)->add($data);
						if($cvid_result) $cvid = $cvid_result;//内容标签映射表中cvid
					}
                    //如果内容标签不为空，添加到内容标签映射表中
                    if($cvid){
                        $tag_data = array();
                        $tag_data['pkgname'] = $package;
                        $tag_data['cvid'] = $cvid;
                        $tag_data['tags'] = $cont_tags;
                        //先删除原有映射内容
                        $tag_res = M('')->table('cont_tags_content')->where(array('cvid'=>$tag_data['cvid']))->delete();
                        if($tag_res){
                            $this->writelog('内容标签映射表删除关联数据，cvid为'.$tag_data['cvid'].'。', 'cont_tags_content',$tag_data['cvid'],__ACTION__ ,'','delete');
                        }
                        if($cont_tags){//添加新数据
                            $cont_model = D('Sj.ContAttribute');
                            $tag_result = $cont_model->add_cont_attrribute($tag_data);
                            if($tag_result){
                                $this->writelog('内容标签映射表添加关联数据，vid为'.$tag_data['cvid'].'。', 'cont_tags_content',$tag_data['cvid'],__ACTION__ ,'','add');
                            } 
                        } 
                    }

				}else {
					if(empty($list)|| !$list[0]['package']){
                        $sj_colle   =   $model->table($sj_colle_table)->where(array('sid'=>$sid_str))->find();
                        //获取此条内容性质,质量，栏目
                        $cont_model = D('Sj.ContAttribute');
                        $cont_column_num = explode(',', $sj_colle['cont_column']);
                        //内容标签
                        $tag_result = M('') -> table('cont_tags_content')->where(array('cvid'=>$sid_str))->select();
                        $second_tag = $three_rank = array();
                        foreach ($tag_result as $tag_key => $tag_value) {
                            array_push($second_tag,$tag_value['second_tagid']);
                            if($tag_value['three_tagid']) array_push($second_tag,$tag_value['three_tagid']);
                            if($tag_value['tagidlevel']){
                                $thlrank = $tag_value['three_tagid'].'_'.$tag_value['tagidlevel'];
                                array_push($three_rank, $thlrank);
                            }  
                        }
                        $second_tag = array_unique($second_tag);
                        #获取内容属性
                        $sj_colle['cont_src'] = $sj_colle['cont_src'] ? $sj_colle['cont_src'] : 2;
                        list($content_xz,$content_zl,$con_source,$user_tend,$content_lm,$content_tag) = $cont_model -> show_conattibute($sj_colle['cont_quality'],$sj_colle['cont_level'],$sj_colle['cont_src'],$sj_colle['user_tend'],$cont_column_num,$second_tag,$three_rank);
                        $this->assign('user_tend',$user_tend);
                        $this->assign('content_tag',$content_tag);
                        $this->assign('con_source',$con_source);
                        $this->assign('content_zl', $content_zl);
                        $this->assign('content_xz', $content_xz);
                        $this->assign('content_lm', $content_lm);
                        // ==================================
                        
						
						$this->assign('sysc', $sysc);
						$this->assign('sid', $sid_str);
						$this->assign('check_status', $check_status);
						$this->display('video_submit');die;
					}
				}
			}else {
				if(count($sid_arr) != count($list)) {
					$this->error("有未填写包名的视屏不能批量通过或通过并同步操作!");
				}
			}
			
			foreach ($sid_arr as $val) {
				$sj_colle	=	$model->table($sj_colle_table)->where(array('sid'=>$val))->find();
				$pkg_data	=	M('')->field('softid,softname,package')->table('sj_soft')->where(array('package'=>$sj_colle['package'],'status'=>1,'hide'=>1))->find();
				//先添加到视屏库
				$data_video = array(
					'package'		=>	$sj_colle['package'],
					'softid'		=>	$pkg_data['softid'],
					'video_title'	=>	$sj_colle['title'],
					'video_pic'		=>	$sj_colle['video_pic'],
					'video_pic_30'	=>	$sj_colle['video_pic_30'],
					'video_pic_60'	=>	$sj_colle['video_pic_60'],
					'video_url'		=>	$sj_colle['video_local_path'],
					'add_tm'		=>	time(),
					'update_tm'		=>	time(),
					'status'		=>	1,
					'check_status'	=>	1,
					'cont_level'	=>	$sj_colle['cont_level'],
					'cont_quality'	=>	$sj_colle['cont_quality'],
					'cont_column'	=>	$sj_colle['cont_column'],
                    'cont_src'      =>  $sj_colle['cont_src'],
					'user_tend'		=>	$sj_colle['user_tend'],
                    'tagstatus'     =>  $sj_colle['tagstatus'],
                    'download_guide'=>  $sj_colle['download_guide'],
                    'dg_type'       =>  $sj_colle['dg_type'],
                    'dg_screen'     =>  $sj_colle['dg_screen'],
                    'dg_time_m'     =>  $sj_colle['dg_time_m'],
                    'dg_time_s'     =>  $sj_colle['dg_time_s'],
                    'dg_long'       =>  $sj_colle['dg_long'],
                    'dg_position'   =>  $sj_colle['dg_position'],
				);
				if($sj_colle['taskname'] =='dangleVideo' ){
					//4当乐网
					$data_video['video_num'] = 4;
				}elseif($sj_colle['taskname'] == 'baijiahaoVideo' ){
					//5百家号
					$data_video['video_num'] = 5;
				}elseif($sj_colle['taskname'] == 'com18touchVideo' ){
					//6超好玩
					$data_video['video_num'] = 6;
				}else{
					$this->error("不支持该视屏源");
				}
					
				$video_id = M("")->table('sj_soft_extra')->add($data_video);
				//如果栏目，添加栏目与视频关联数据
				if (!empty($sj_colle['cont_column'])) {
					$column_type = 3;//2：内容外显 3： 视频管理
					$cont_column_ids = explode(',',trim($sj_colle['cont_column'],','));
					$video_column_list = explode(',',$_GET['id']);
					//先删除关联表中的对应的视频id
					$column_res = M('')->table('cont_column_content_video')->where(array('column_type'=>$column_type,'con_videoid'=>$video_id))->delete();
					if($column_res){
						$this->writelog('视频管理删除智盟关联数据，来源id为'.$column_type.'，视频id为'.$video_id.'。', 'cont_column_content_video',$video_id,__ACTION__ ,'','delete');
					}
					//添加新的关联数据
					foreach ($cont_column_ids as $cont_column_key=>$cont_column_value) {
						$column_data = array();
						$column_data['column_type'] = $column_type;
						$column_data['con_videoid'] = $video_id;
						$column_data['columnid'] = $cont_column_value;
						$column_result = M('')->table('cont_column_content_video')->add($column_data);
						if($column_result){
							$this->writelog('视频管理添加智盟关联数据，其中来源id为'.$column_type.'，栏目id为'.$cont_column_value.'，视频id为'.$video_id.'。', 'cont_column_content_video',$video_id,__ACTION__ ,'','add');
						}
					}
				}
				
				if(!$video_id) {
					$this->error("同步到视频库失败");
				}
                //通过时为视屏管理中数据添加标签
                if($sj_colle['tagstatus'] == 1){
                    //查询采集视频的此id映射关联数据
                    $ctag_result = M('')->table('cont_tags_content')->where(array('cvid'=>$val,'status'=>1))->select();
                    $tag_str = '';
                    foreach($ctag_result as $ctag_value){
                        $tag_s = '';
                        if($ctag_value['second_tagid']) $tag_s = $ctag_value['first_tagid'].'_'.$ctag_value['second_tagid'];
                        if($ctag_value['three_tagid']) $tag_s = $ctag_value['first_tagid'].'_'.$ctag_value['second_tagid'].'_'.$ctag_value['three_tagid'];
                        if($ctag_value['tagidlevel']) $tag_s = $ctag_value['first_tagid'].'_'.$ctag_value['second_tagid'].'_'.$ctag_value['three_tagid'].'_'.$ctag_value['tagidlevel'];
                        if(!empty($tag_s)) $tag_str .= $tag_s.',';
                    }
                    //内容标签
                    $tag_data = array();
                    $tag_data['pkgname'] = $sj_colle['package'];
                    $tag_data['vid'] = $video_id;
                    $tag_data['tags'] = $tag_str;
                    //先删除原有映射内容
                    $tag_res = M('')->table('cont_tags_content')->where(array('vid'=>$tag_data['vid']))->delete();
                    if($tag_res){
                        $this->writelog('内容标签映射表删除关联数据，cid为'.$tag_data['cid'].'。', 'cont_tags_content',$tag_data['cid'],__ACTION__ ,'','delete');
                    }
                    //添加新数据
                    $cont_model = D('Sj.ContAttribute');
                    $tag_result = $cont_model->add_cont_attrribute($tag_data);
                    if($tag_result){
                        $this->writelog('内容标签映射表添加关联数据，cid为'.$tag_data['cid'].'。', 'cont_tags_content',$tag_data['cid'],__ACTION__ ,'','add');
                    }
                }

				//视屏转码
				$task_data['id']=$video_id;
				$task_data['package'] = $pkg_data['package'];
				$task_data['softid'] = $pkg_data['softid'];
				$task_data['video_num'] = $data_video['video_num'];
				$task_data['collect_video']= 1;
				$task_data['url'] = $sj_colle['video_local_path'];
				$this->create_h263_url($task_data);
				
				//同步到资源库
				if( $sysc ) {
					$option['status']			=	1;
					$option['admin_id']			=	$_SESSION['admin']['admin_id'];
					$option['title']			=	$sj_colle['title'];
					$option['package_name']		=	$sj_colle['softname'];
					$option['package_643']		=	$sj_colle['package'];
					$option['parameter_field']	=	json_encode(array('video_id'=>$video_id));
					$option['video_url']		=	$sj_colle['video_local_path'];
					$option['video_pic']		=	$sj_colle['video_pic'];
					$option['is_dev']			=	0;
					$option['update_at']		=	time();
                    $option['cont_level']		=	$sj_colle['cont_level'];
                    $option['cont_quality']		=	$sj_colle['cont_quality'];
                    $option['cont_column']		=	$sj_colle['cont_column'];
                    $option['cont_src']         =   $sj_colle['cont_src'];
                    $option['user_tend']        =   $sj_colle['user_tend'];//用户倾向
                    $option['tagstatus']        =   $sj_colle['tagstatus'];//修改标签状态
					//获取区间
					$sql_extent = "SELECT extent_id FROM sj_flexible_extent where belong_page_type='fixed_resource_channel' and `status`=1 and extent_type = 29 ORDER BY extent_type asc";
					$list_extent = M('')->query($sql_extent);
					$option['extent_id'] = $list_extent[0]['extent_id'];
						
					$sql = "SELECT b.* FROM sj_flexible_extent as a INNER JOIN sj_flexible_extent_soft as b on a.extent_id = b.extent_id WHERE a.belong_page_type= 'fixed_resource_channel' and a.status=1 and b.resource_type= 29 and b.status = 1 and b.package_643 ='{$sj_colle['package']}' ";
					$dan_tu =  M('')->query($sql);
					if(isset($dan_tu[0]['id'])){
						$cont_Levelids_arr = $dan_tu[0]['id'];#用于 内容生产邮件系统
						$ret = M('')->table('sj_flexible_extent_soft')->where(array('id'=>$dan_tu[0]['id']))->save($option);
						if($ret){
							$this->writelog('内容外显数据同步到灵活运营资源库单软件(视频)类型，其中资源库id为'.$dan_tu[0]['id'].'，视屏id为'.$video_id.'。', 'sj_flexible_extent_soft',$dan_tu[0]['id'],__ACTION__ ,'','edit');
						}
					}else{
						$option['create_at']		=	time();
						$option['resource_type']	=	29;
						$affect = M('')->table('sj_flexible_extent_soft')->add($option);
						if($affect){
							$cont_Levelids_arr = $affect;#用于 内容生产邮件系统
							$this->writelog('内容外显数据同步到灵活运营资源库单软件(视频)类型，其中资源库id为'.$affect.'，视频id为'.$video_id.'。', 'sj_flexible_extent_soft',$affect,__ACTION__ ,'','add');
						}
					}

                    //如果内容标签不为空，添加到内容标签映射表中
                    if($cont_Levelids_arr){
                        $tag_data = array();
                        $tag_data['pkgname'] = $sj_colle['package'];
                        $tag_data['eid'] = $cont_Levelids_arr;
                        $tag_data['tags'] = $_POST['content_tags'];
                        //先删除原有映射内容
                        $tag_res = M('')->table('cont_tags_content')->where(array('eid'=>$tag_data['eid']))->delete();
                        if($tag_res){
                            $this->writelog('内容标签映射表删除关联数据，eid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','delete');
                        }
                        //添加新数据
                        if($_POST['content_tags']){
                            $cont_model = D('Sj.ContAttribute');
                            $tag_result = $cont_model->add_cont_attrribute($tag_data);
                            if($tag_result){
                                $this->writelog('内容标签映射表添加关联数据，eid为'.$tag_data['eid'].'。', 'cont_tags_content',$tag_data['eid'],__ACTION__ ,'','add');
                            }
                        } 
                    }
				}
			}
			$where = array('sid'=>array('in', $sid_str));
			$res = $model->table($sj_colle_table)->where($where)->save(array('status'=>$check_status,'update_tm'=>time()));
		}

		$status_intro = array(0=>'撤销',1=>'通过',2=>'驳回');
		$now_status = $status_intro[$check_status];
		if($res){
			$this->writelog("{$now_status}了视频，ID为{$sid_str}", 'sj_colle_contentlist', $sid_str, __ACTION__,"","edit");
			$this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/VideoCollectManage/video_list/check_status/'.$check_status);
			$this->success("{$now_status}成功");
		}else{
			$this->error("{$now_status}失败");
		}
	}
	
	public function upload_pic($data){
		$fileicon=array();
		if(!$_FILES){
			return;
		}
		foreach($_FILES as $key=>$val) {
			$ext = strtolower(pathinfo($val['name'],PATHINFO_EXTENSION));
			if($key=="video_pic"){
				if(!$_FILES['video_pic']['tmp_name']){
					continue;
				}
				if(!in_array($ext,array('png','jpg'))) {
					$this->error('视频默认图：1256×706px，格式jpg、png');
				}else if(getimagesize($val['tmp_name'])===FALSE) {
					$this->error('视频默认图：1256×706px，格式jpg、png');
				}
				$fileicon[$key] = '@'.$val['tmp_name'];
			}else if($key=="video_url"){
				if(!$_FILES['video_url']['tmp_name']){
					continue;
				}
				if($val['size'] > 200*1024*1024) {	//1M
					$this->error("请上传200M以内MP4格式视频");
				} else if(!in_array($ext,array('mp4'))) {
					$this->error('请上传200M以内MP4格式视频');
				}
				// else if(getimagesize($val['tmp_name'])===FALSE) {
				// 	$this->error('请上传1G以内MP4格式视频3');
				// }
				$fileicon['video'] = '@'.$val['tmp_name'];
			}
				
		}
		if($fileicon['video_pic']){
			$image_file = getimagesize(substr($fileicon['video_pic'],1));
			if($image_file[0] != 1256 || $image_file[1] != 706){
				$this->error("视频默认图：尺寸1256*706。支持JPG,PNG");
			}
		}
		$file_path=array();
		foreach($fileicon as $k=>$v){
			if(!$v){
				continue;
			}
			$file_path=array_merge($file_path,$this->upload_pic_do(array($k=>$v),$k));
		}
		// var_dump('调试');
		// var_dump($file_path);die;
		if($file_path){
			if(!$file_path['video_pic']){
				$file_path['video_pic'] = $data['video_pic'];
				$file_path['video_pic_30'] = $data['video_pic_30'];
				$file_path['video_pic_60'] = $data['video_pic_60'];
			}
			if(!$file_path['video_url']){
				$file_path['video_url'] = $data['video_url'];
			}
			$video_data=$this->upload_pic_do($file_path,'video_backstage_handle');
		}
	
		return $video_data;
	}
	
	
   public function upload_pic_do($file,$k){
        if ($file) {
            $file['static_data'] = '/data/att/m.goapk.com';
            if($k=='video'){
            	$file['do'] = 'video';
            }else if($k=='video_pic'){
            	$file['do'] = 'video_pic';
            }else if($k=='video_backstage_handle'){
            	$file['do'] = 'video_backstage_handle';
            }
            
        }
        $upload_model = D("Dev.Uploadfile");
        $upload = $upload_model->_http_post($file);
        if ($upload['info']['http_code'] != 200) {
            $this->error("和图片服务器通讯失败，请重试！({$upload['info']['errno']}:{$upload['info']['error']})");
        }
        $pic_arr=array();
        // var_dump($upload['ret']['ret']);
        if ($upload['ret']['code']==1) {
            foreach ($upload['ret']['ret'] as $key => $val) {
    			if($key=='video'){
    				$val = str_replace('/data/att/m.goapk.com','',$val);
            		$pic_arr['video_url'] = $val;
            	}else{
            		$pic_arr[$key] = $val;
            	}
            }
        }else{
        	$this->error("{$upload['ret']['msg']}");
        }
        
        return $pic_arr;
    }
	
    public  function pub_resize(){
    	$file = array ( 'video_pic' => '@/data/caiji/thirdall/img/201803/22/1521655812385.jpg');
    	$res = $this->upload_pic_do($file,'video_pic');
    	$res = $this->upload_pic_do($res,'video_backstage_handle');
    	print_r($res);die;
    }
	
}
