<?php
if (!function_exists("array_column")) {
  function array_column(array &$rows, $column_key, $index_key = null) {
    $data = array();
    if (empty($index_key)) {
      foreach ($rows as $row) {
        $data[] = $row[$column_key];
      }
    } else {
      foreach ($rows as $row) {
        $data[$row[$index_key]] = $row[$column_key];
      }
    }
    return $data;
  }
}

/**
 * 视频管理
 */

class VideoManageAction extends CommonAction {
    const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";
    private $true_table='soft_extra';      
    //视频列表
    public function video_list() {
        $model = M($this->true_table);
        $where = array();
        if (isset($_GET['s_package'])) {
            $where['package'] = trim($_GET['s_package']);
            $this->assign('package', trim($_GET['s_package']));
        }
        if(isset($_GET['check_status'])){
            $where['check_status'] = $_GET['check_status'];
        }else{
            $where['check_status'] = 0;
            $_GET['check_status'] = 0;
        }
        if (isset($_GET['s_softname'])) {
        	$softdata = $model->table('sj_soft')->where(array('softname'=>trim($_GET['s_softname']),'status'=>1,'hide'=>array('in',array(1,1024))))->field('package,softname')->select();
            // echo $model->getlastsql();
            $package_arr=array();
            foreach($softdata as $v){
                $package_arr[]=$v['package'];
            }
            $where['package'] = array('in',$package_arr);
            $this->assign('softname', trim($_GET['s_softname']));
        }
        if (isset($_GET['s_video_title'])) {
            $where['video_title'] = array('like',"%".trim($_GET['s_video_title']."%"));
            $this->assign('video_title', trim($_GET['s_video_title']));
        }
        if (isset($_GET['s_video_num'])) {
            $where['video_num'] = trim($_GET['s_video_num']);
            $this->assign('video_num', trim($_GET['s_video_num']));
        }
        
        $where['status'] = 1;
        //添加内容选项
        if(in_array($_GET['check_status'],array(0,1,2))){
            //搜索条件
            if($_GET['content_level']) $where["cont_level"] = $_GET['content_level'];
            if($_GET['content_nature']) $where["cont_quality"] = $_GET['content_nature'];
            if($_GET['cont_src']) $where["cont_src"] = $_GET['cont_src'];
            //用户倾向
            if($_GET['user_tend']) $where["user_tend"] = $_GET['user_tend'];
            if($_GET['s_content_column']){
                $where["cont_column"] = array('like','%,'.$_GET['s_content_column'].',%');
                $this->assign('s_content_column',$_GET['s_content_column']);
            }
            if(!is_null($_GET['s_status_tag'])){
                $where["tagstatus"] = $_GET['s_status_tag'];
                $this->assign('s_status_tag',$_GET['s_status_tag']);
            } 

            $cont_model = D('Sj.ContAttribute');
            $column_model = D('Sj.ContColumn');
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

        $count = $model->where($where)->count();
        import("@.ORG.Page2");
        $pg=$_GET['p']?$_GET['p']:1;
        $this->assign('pg', $pg);
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $this->assign('total', $count);
        $order = 'add_tm desc';
        $o_param = preg_replace('/order=[^&]{1,}/','',$param);
        $this->assign('o_param',$o_param);
        if(isset($_GET['order'])){

            if($_GET['order']=='a'){
              $order = 'add_tm asc';
            }else{
              $order = 'add_tm desc';
            }
            $this->assign('order',$_GET['order']);
        }

        $list = $model->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order($order)->select();
        $package=array();
        foreach($list as $k=>$v){
          $package[]=$v['package'];
        }
        
        $softdata=get_table_data(array('package'=>array('in',$package),'status'=>1,'hide'=>1),"sj_soft","package","*");
        foreach($list as $k=>$v){
          $list[$k]['softname']=$softdata[$v['package']]['softname'];
          if($v['video_num']==1){
            $list[$k]['source']='游戏联运';
          }else if($v['video_num']==2){
            $list[$k]['source']='开发者';
          }else if($v['video_num']==3){
            $list[$k]['source']='视频';
          }else if($v['video_num']==4) {
          	$list[$k]['source']='当乐网';
          }else if($v['video_num']==5) {
          	$list[$k]['source']='百家号';
          }else if($v['video_num']==6) {
          	$list[$k]['source']='超好玩';
          }else if($v['video_num']==7) {
            $list[$k]['source']='内容合作平台';
          }

          //添加内容选项
          if(in_array($_GET['check_status'],array(0,1,2))){
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
        $this->assign('check_status',$_GET['check_status']);
        $this->assign('list', $list);
        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('page', $Page->show());
        $this->display();
    }
    //视频删除
    public function video_del() {
        $model = M($this->true_table);

        $data = array(
            'update_tm' => time(),
            'status' => 0,
            'id' => $_GET['id'],
        );
        $res = $model->save($data);

        //调用接口通知java端
        $post_data = array();
        $post_data['id'] = $_GET['id'];
        $post_data['contentType'] = 2; # 1 内容外显 , 2 视频管理
        $post_data['optType'] = 2; # 1 添加\更新 , 2 删除
        //获取包名
        $pack_result = $model->table('sj_soft_extra')->where(array('id'=>$_GET['id']))->find();
        $post_data['packageName'] = $pack_result['package'];
        $post_data['showStyle'] = 3;
        $notic_return = curl_update_content_package($post_data);
        $return_source = json_decode($notic_return,true);
        $this->writelog("视频管理接口更新数据 code:".$return_source['code']."MSG:".$return_source['msg']."，视频id为".$post_data['id']."，包名为".$post_data['packageName']."，内容类型为".$post_data['contentType']."。", "",$post_data['id'],__ACTION__ ,'','del');
        
        

        if ($res) {
			$this->writelog("删除了此条视频，其中id为{$_GET['id']}。", 'sj_soft_extra', $_GET['id'], __ACTION__,"","del");
            $this->success("操作成功");
        }else{
            $this->success("操作失败");
        }
    }
    
    
    public function video_edit(){
        $model = M($this->true_table);
        if($_GET['id']||$_GET['add']){
            if($_GET['id']){
                $video_data=$model->where(array('id'=>$_GET['id']))->find();
                $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
                $soft_data=$model->table('sj_soft')->where(array('package'=>$video_data['package'],'status'=>1,'hide'=>array('in',array(1,1024))))->find();
                $video_data['softname']=$soft_data['softname'];
                $this->assign('video_data', $video_data);
            }
            #内容属性
            $cont_model = D('Sj.ContAttribute');
            $cont_column_num = explode(',',trim($video_data['cont_column'],','));
            //内容标签
            $tag_result = M('') -> table('cont_tags_content')->where(array('vid'=>$_GET['id']))->select();
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
            list($content_xz,$content_zl,$con_source,$user_tend,$content_lm,$content_tag) = $cont_model -> show_conattibute($video_data['cont_quality'],$video_data['cont_level'],$video_data['cont_src'],$video_data['user_tend'],$cont_column_num,$second_tag,$three_rank);
            $this->assign('user_tend',$user_tend);
            $this->assign('content_tag',$content_tag);
            $this->assign('content_zl', $content_zl);
            $this->assign('content_xz', $content_xz);
            $this->assign('content_lm', $content_lm);
            $this->assign('con_source',$con_source);

            if($_GET['show_video']==1){
                $this->display('video_show');
            }else{
                $this->display();
            }
            
        }else if($_POST){
            $data=array();
            $task_data=array();
            $data['id']=trim($_POST['id']);
            // $data['status']=1;
            // $data['video_type']=1;
            $data['video_title']=trim($_POST['video_title']);
            if($data['id']){
                $video_data=$model->where(array('id'=>$data['id']))->find();
                $video_data_old=array('video_url'=>$video_data['video_url'],'video_pic'=>$video_data['video_pic'],'video_pic_30'=>$video_data['video_pic_30'],'video_pic_60'=>$video_data['video_pic_60']);
            	if(!$_FILES['video_url']['tmp_name']&&!$video_data['video_url']){
            		$this->error("请上传200M以内MP4格式视频");
            	}
            }else{
            	$data['video_num'] = 3;
                $data['status']=1;
                $data['video_type']=1;
            	$data['add_tm']=time();
            	if(!$_FILES['video_url']['tmp_name']){
            		$this->error("请上传200M以内MP4格式视频");
            	}

            }

            //v6.4.9增加视频下载引导
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

            $data['update_tm']=time();
        	$data['package']=trim($_POST['package']);
            $task_data['package']=trim($_POST['package']);

            $soft_data=$model->table('sj_soft')->where(array('package'=>$data['package'],'status'=>1,'hide'=>1))->find();
            $data['softid']=$soft_data['softid'];
            $task_data['softid']=$soft_data['softid'];
            // `video_pic` varchar(255) DEFAULT '' COMMENT '视频图片',
            // `video_url` varchar(255) NOT NULL DEFAULT '' COMMENT '视频路径',
            // `video_md5` varchar(255) NOT NULL DEFAULT '' COMMENT '视频md5',
            // `video_filesize` varchar(255) NOT NULL DEFAULT '' COMMENT '视频大小',

            // `video_pic_30` varchar(255) NOT NULL DEFAULT '' COMMENT '图片大小30k 宽度320',
            // `video_pic_60` varchar(255) NOT NULL DEFAULT '' COMMENT '图片大小60k 宽度480',
            // `screen_mode` tinyint(1) DEFAULT '0' COMMENT '1横屏 2竖屏',
            // `video_h263_url` varchar(255) DEFAULT '' COMMENT '263编码视频路径',
            
            
            if($video_data_old){
            	$up_file=$this->upload_pic($video_data_old);
            }else{
            	$up_file=$this->upload_pic();
            }
            

            if(count($up_file)){
                // $file_path_arr=$this->upload_pic_do($up_file);
                // if($file_path_arr){
            	if($up_file['video_url']){
					$data['inject_status'] = 0;
            		$task_data['url'] = $up_file['video_url'];
            	}
                $data=array_merge($data,$up_file);
                // }
            }
            #内容来源
            $data['cont_src'] = $_POST['con_source'];
            #用户倾向
            $data['user_tend'] = $_POST['user_tend'];
            $data['cont_level'] = $_POST['content_level'] ? $_POST['content_level'] : 0; #内容质量
            $data['cont_quality'] = $_POST['content_nature'] ? $_POST['content_nature'] : 0; #内容性质
            $data['cont_column'] = $_POST['content_column'] ? ','.$_POST['content_column'] : '';#内容栏目
            $data['tagstatus'] = $_POST['content_tags'] ? 1 : 0;
            $cont_tags = $_POST['content_tags'];
            $cont_column = $_POST['content_column'];

            //添加编辑成功还需要调work
            if($data['id']){
                $log = $this -> logcheck(array('id' => $data['id']),'sj_soft_extra',$data,$model);
                $re = $model->save($data);
                if($re){
                    //编辑已通过的视频，更新content_platform_content内容统计表的title
                    if($video_data['check_status']==1 && $video_data['video_num']==7){
                        $CP = D('Dev.ContentPlatform');
                        $cp_res = $CP->sync_content('edit', array(
                            'type' => 2,
                            'content_id' => $data['id'],
                            'title' => $data['video_title'],
                        ));
                        if($cp_res){
                            $this->writelog("更新了此条视频到内容合作平台内容表中，视频id为{$data['id']},标题为{$data['video_title']}", 'content_platform_content', $cp_res, __ACTION__,"","edit");
                        }
                    }
                }

                //调用接口通知java端
                $post_data = array();
                $post_data['id'] = $data['id'];
                $post_data['contentType'] = 2; # 1 内容外显 , 2 视频管理
                $post_data['optType'] = 1; # 1 添加\更新 , 2 删除
                $post_data['packageName'] = $data['package'];
                $post_data['showStyle'] = 3;
                $notic_return = curl_update_content_package($post_data);
                $return_source = json_decode($notic_return,true);
                $this->writelog("视频管理接口更新数据 code:".$return_source['code']."MSG:".$return_source['msg']."，视频id为".$post_data['id']."，包名为".$post_data['packageName']."，内容类型为".$post_data['contentType']."。", "",$post_data['id'],__ACTION__ ,'','edit');
                
                //添加内容栏目映射数据
                if (!empty($cont_column)) {
                    $column_type = 3;//2：内容外显 3： 视频管理
                    $cont_column_ids = explode(',',trim($cont_column,','));
                    //先删除关联表中的对应的内容序号id
                    $column_res = M('')->table('cont_column_content_video')->where(array('column_type'=>$column_type,'con_videoid'=>$data['id']))->delete();
                    if($column_res){
                        $this->writelog('视频管理删除智盟关联数据，来源id为'.$column_type.'，视频id为'.$data['id'].'。', 'cont_column_content_video',$data['id'],__ACTION__ ,'','delete');
                    }
                    //添加新的关联数据
                    foreach ($cont_column_ids as $cont_column_key=>$cont_column_value) {
                        $column_data = array();
                        $column_data['column_type'] = $column_type;
                        $column_data['con_videoid'] = $data['id'];
                        $column_data['columnid'] = $cont_column_value;
                        $column_result = M('')->table('cont_column_content_video')->add($column_data);
                        if($column_result){
                            $this->writelog('视频管理添加智盟关联数据，其中来源id为'.$column_type.'，栏目id为'.$cont_column_value.'，视频id为'.$data['id'].'。', 'cont_column_content_video',$data['id'],__ACTION__ ,'','add');
                        } 
                    }
                }
                //添加内容标签映射数据
                $tag_data = array();
                $tag_data['pkgname'] = $data['package'];
                $tag_data['vid'] = $data['id'];
                $tag_data['tags'] = $cont_tags;
                //先删除原有映射内容
                $tag_res = M('')->table('cont_tags_content')->where(array('vid'=>$data['id']))->delete();
                if($tag_res){
                    $this->writelog('内容标签映射表删除关联数据，vid为'.$data['id'].'。', 'cont_tags_content',$data['id'],__ACTION__ ,'','delete');
                }
                if($cont_tags){//添加新数据
                    $cont_model = D('Sj.ContAttribute');
                    $tag_result = $cont_model->add_cont_attrribute($tag_data);
                    if($tag_result){
                        $this->writelog('内容标签映射表添加关联数据，vid为'.$data['id'].'。', 'cont_tags_content',$data['id'],__ACTION__ ,'','add');
                    }
                }


                if($re){
                	$task_data['id']=$data['id'];
                	$this->create_h263_url($task_data);
                    $this->writelog('修改了视频id为'.$data["id"].'。'.$log."来源为{$data['cont_src']}", 'sj_soft_extra',$data["id"],__ACTION__ ,'','edit');
                    $check_status = isset($video_data['check_status'])?$video_data['check_status']:0;
                    $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/VideoManage/video_list/check_status/'.$check_status);
                    $this->success('编辑成功');
                }else{
                    $this->error('编辑失败');
                }
            }else{

                $re=$model->add($data);
                //调用接口通知java端
                $post_data = array();
                $post_data['id'] = mysql_insert_id();
                $post_data['contentType'] = 2; # 1 内容外显 , 2 视频管理
                $post_data['optType'] = 1; # 1 添加\更新 , 2 删除
                $post_data['packageName'] = $data['package'];
                $post_data['showStyle'] = 3;
                $notic_return = curl_update_content_package($post_data);
                $return_source = json_decode($notic_return,true);
                $this->writelog("视频管理接口更新数据 code:".$return_source['code']."MSG:".$return_source['msg']."，视频id为".$post_data['id']."，包名为".$post_data['packageName']."，内容类型为".$post_data['contentType']."。", "",$post_data['id'],__ACTION__ ,'','add');
                
                //添加内容栏目映射数据
                if (!empty($cont_column)) {
                    $column_type = 3;//2：内容外显 3： 视频管理
                    $cont_column_ids = explode(',',trim($cont_column,','));
                    //先删除关联表中的对应的内容序号id
                    $column_res = M('')->table('cont_column_content_video')->where(array('column_type'=>$column_type,'con_videoid'=>$post_data['id']))->delete();
                    if($column_res){
                        $this->writelog('视频管理删除智盟关联数据，来源id为'.$column_type.'，视频id为'.$post_data['id'].'。', 'cont_column_content_video',$post_data['id'],__ACTION__ ,'','delete');
                    }
                    //添加新的关联数据
                    foreach ($cont_column_ids as $cont_column_key=>$cont_column_value) {
                        $column_data = array();
                        $column_data['column_type'] = $column_type;
                        $column_data['con_videoid'] = $post_data['id'];
                        $column_data['columnid'] = $cont_column_value;
                        $column_result = M('')->table('cont_column_content_video')->add($column_data);
                        if($column_result){
                            $this->writelog('视频管理添加智盟关联数据，其中来源id为'.$column_type.'，栏目id为'.$cont_column_value.'，视频id为'.$post_data['id'].'。', 'cont_column_content_video',$post_data['id'],__ACTION__ ,'','add');
                        } 
                    }
                }
                //添加内容标签映射数据
                $tag_data = array();
                $tag_data['pkgname'] = $data['package'];
                $tag_data['vid'] = $post_data['id'];
                $tag_data['tags'] = $cont_tags;
                //先删除原有映射内容
                $tag_res = M('')->table('cont_tags_content')->where(array('vid'=>$tag_data['vid']))->delete();
                if($tag_res){
                    $this->writelog('内容标签映射表删除关联数据，vid为'.$tag_data['vid'].'。', 'cont_tags_content',$tag_data['vid'],__ACTION__ ,'','delete');
                }
                if($cont_tags){
                    //添加新数据
                    $cont_model = D('Sj.ContAttribute');
                    $tag_result = $cont_model->add_cont_attrribute($tag_data);
                    if($tag_result){
                        $this->writelog('内容标签映射表添加关联数据，vid为'.$tag_data['vid'].'。', 'cont_tags_content',$tag_data['vid'],__ACTION__ ,'','add');
                    }
                }
                
                if($re){
                	$task_data['id']=$re;
                	$this->create_h263_url($task_data);
                    $this->writelog('添加了一条视频，其中id为'.$re.",内容性质为{$data['cont_quality']},质量为{$data['cont_level']},来源为{$data['cont_src']},栏目为{$data['cont_column']}", 'sj_soft_extra',$re,__ACTION__ ,'','add');
                    $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/VideoManage/video_list/check_status/0');
                    $this->success('添加成功');
                }else{
                    $this->error('添加失败');
                }
            }
            
            
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
        
        // var_dump($video_data);die;
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
        $model = M();
        $where = array(
            'package' => $package,
            'status' => 1,
            'hide' => array('in', '1,1024'),
        );
        $find = $model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
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
        #用于 内容生产邮件系统
        if($_GET['cont']){
            $this-> video_cont();
            die;
        }

        $resid = $id = $_POST['id'] ? $_POST['id'] : $_GET['id']; #$resid 用于 内容生产邮件系统
        $cont_level = $_POST['content_level'] ? $_POST['content_level'] : $_GET['content_level']; #用于 内容生产邮件系统
        $cont_nature = $_POST['content_nature'] ? $_POST['content_nature'] : $_GET['content_nature']; #用于 内容生产邮件系统
        $contlevelMod = D('Sj.ContLevel'); #用于 内容生产邮件系统
        $cont_column = $_POST['content_column'] ? ','.$_POST['content_column'] : '';#内容栏目
        #内容来源
        $cont_src =  $_POST['con_source'] ? $_POST['con_source'] : 0 ;
        $user_tend =  $_POST['user_tend'] ? $_POST['user_tend'] : 0 ;
        #内容标签
        $cont_tags = $_POST['content_tags'];
        $tagstatus = empty($_POST['content_tags']) ? 0 : 1;

       
        $model = M('');
        $id = str_replace(",","','",$id);
        if(!$id){
            $this->error('缺少参数');
        }

        $where = array('id'=>array('exp', " in ('{$id}')"));
        $check_status = $_POST['check_status'] ? $_POST['check_status'] : $_GET['check_status'];
        $sysc = $_POST['sysc'] ? $_POST['sysc'] : $_GET['sysc'];

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

		#用于 内容生产邮件系统 
        if($check_status == 1){
            if(empty($cont_level)) $this->error('请选择内容质量');
            if(empty($cont_nature)) $this->error('请选择内容性质');
            // if(empty($cont_column)) $this->error('请选择内容栏目');
        }
        //如果为撤销操作不修改内容质量，性质，栏目值
        if($check_status != 1){
            $res = $model->table('sj_soft_extra')->where($where)->save(array('check_status'=>$check_status,'update_tm'=>time(),'tagstatus'=>$tagstatus));
            if($check_status == 0 && $res){
                $list = $model->table('sj_soft_extra')->where(array_merge($where,array('video_num'=>7)))->field('video_num,video_title,id,dev_id')->select();
                foreach ($list as $k => $v) {
                    //审核通过视频时，在content_platform_content内容统计表修改状态
                    $CP = D('Dev.ContentPlatform');
                    $cp_res = $CP->sync_content('edit', array(
                        'type' => 2,
                        'content_id' => $v['id'],
                        'status' => 2,
                    ));
                    if($cp_res){
                        $this->writelog("更新了此条视频到内容合作平台内容表中，视频id为{$v['id']}", 'content_platform_content', $cp_res, __ACTION__,"","edit");
                    }
                }
            }
        }else{
            $res = $model->table('sj_soft_extra')->where($where)->save(array('check_status'=>$check_status,'update_tm'=>time(),'cont_level'=>$cont_level,'cont_quality'=>$cont_nature,'cont_column'=>$cont_column,'cont_src' => $cont_src,'user_tend' => $user_tend,'tagstatus'=>$tagstatus));
            if($res){
                $list = $model->table('sj_soft_extra')->where(array_merge($where,array('video_num'=>7)))->field('video_num,video_title,id,dev_id')->select();
                foreach ($list as $k => $v) {
                    //审核通过视频时，在content_platform_content内容统计表修改状态
                    $CP = D('Dev.ContentPlatform');
                    $cp_res = $CP->sync_content('edit', array(
                        'type' => 2,
                        'content_id' => $v['id'],
                        'pass_tm' => time(),
                        'status' => 1,
                    ));
                    if($cp_res){
                        $this->writelog("更新了此条视频到内容合作平台内容表中，视频id为{$v['id']}", 'content_platform_content', $cp_res, __ACTION__,"","edit");
                    }
                }
            }
        }
        
        //如果栏目，添加栏目与视频关联数据
        if (!empty($cont_column)) {
            $column_type = 3;//2：内容外显 3： 视频管理
            $cont_column_ids = explode(',',trim($cont_column,','));
            $video_column_list = explode(',',$resid);
            foreach($video_column_list as $video_column_id){
                //先删除关联表中的对应的视频id
                $column_res = $model->table('cont_column_content_video')->where(array('column_type'=>$column_type,'con_videoid'=>$video_column_id))->delete();
                if($column_res){
                    $this->writelog('视频管理删除智盟关联数据，来源id为'.$column_type.'，视频id为'.$video_column_id.'。', 'cont_column_content_video',$video_column_id,__ACTION__ ,'','delete');
                }
                //添加新的关联数据
                foreach ($cont_column_ids as $cont_column_key=>$cont_column_value) {
                    $column_data = array();
                    $column_data['column_type'] = $column_type;
                    $column_data['con_videoid'] = $video_column_id;
                    $column_data['columnid'] = $cont_column_value;
                    $column_result = $model->table('cont_column_content_video')->add($column_data);
                    if($column_result){
                        $this->writelog('视频管理添加智盟关联数据，其中来源id为'.$column_type.'，栏目id为'.$cont_column_value.'，视频id为'.$video_column_id.'。', 'cont_column_content_video',$video_column_id,__ACTION__ ,'','add');
                    } 
                }
            } 
        }
        //如果内容标签不为空，添加到内容标签映射表中
        $video_tag_list = explode(',',$resid);
        foreach($video_tag_list as $video_tag_id){
            //获取包名
            $tag_data = array();
            $tag_pkg = $model->table('sj_soft_extra')->where(array('id'=>$video_tag_id))->find();
            $tag_data['pkgname'] = $tag_pkg['package'];
            $tag_data['vid'] = $video_tag_id;
            $tag_data['tags'] = $cont_tags;
            //先删除原有映射内容
            $tag_res = M('')->table('cont_tags_content')->where(array('vid'=>$video_tag_id))->delete();
            if($tag_res){
                $this->writelog('内容标签映射表删除关联数据，vid为'.$video_tag_id.'。', 'cont_tags_content',$video_tag_id,__ACTION__ ,'','delete');
            }
            //添加新数据
            if($cont_tags){
                $cont_model = D('Sj.ContAttribute');
                $tag_result = $cont_model->add_cont_attrribute($tag_data);
                if($tag_result){
                    $this->writelog('内容标签映射表添加关联数据，vid为'.$video_tag_id.'。', 'cont_tags_content',$video_tag_id,__ACTION__ ,'','add');
                }
            }
        } 
        

        //同步到资源库
        if($res && $sysc) {
        	$extra_info = $model->table('sj_soft_extra')->where($where)->find();
        	$pkg_data	=	M('')->field('softname')->table('sj_soft')->where(array('package'=>$extra_info['package'],'status'=>1,'hide'=>1))->find();
        	$option['status']			=	1;
        	$option['admin_id']			=	$_SESSION['admin']['admin_id'];
        	$option['title']			=	$extra_info['video_title'];
        	$option['package_name']		=	$pkg_data['softname'];
        	$option['package_643']		=	$extra_info['package'];
        	$option['parameter_field']	=	json_encode(array('video_id'=>$extra_info['id']));
        	if($extra_info['video_num']	==	2) {
        		$option['is_dev'] 	=	1;
        	}else {
        		$option['video_url']	=	$extra_info['video_h263_url'];
        		$option['video_pic']	=	$extra_info['video_pic'];
        		$option['is_dev']		=	0;
        	}
            //内容质量，性质，栏目
            if($cont_level) $option['cont_level'] = $cont_level;
            if($cont_nature) $option['cont_quality'] = $cont_nature;
            if($cont_column) $option['cont_column'] = $cont_column;
            if($cont_src) $option['cont_src'] = $cont_src;
            if($user_tend) $option['user_tend'] = $user_tend;
            $option['tagstatus'] = $tagstatus;

        	$option['update_at']	=	time();
        	//获取区间
        	$sql_extent = "SELECT extent_id FROM sj_flexible_extent where belong_page_type='fixed_resource_channel' and `status`=1 and extent_type = 29 ORDER BY extent_type asc";
        	$list_extent = M('')->query($sql_extent);
        	$option['extent_id'] = $list_extent[0]['extent_id'];

        	$sql = "SELECT b.* FROM sj_flexible_extent as a INNER JOIN sj_flexible_extent_soft as b on a.extent_id = b.extent_id WHERE a.belong_page_type= 'fixed_resource_channel' and a.status=1 and b.resource_type= 29 and b.status = 1 and b.package_643 ='{$extra_info['package']}' ";
        	$dan_tu =  M('')->query($sql);
        	if(isset($dan_tu[0]['id'])){
                $cont_Levelids_arr = $dan_tu[0]['id'];#用于 内容生产邮件系统
        		$ret = M('')->table('sj_flexible_extent_soft')->where(array('id'=>$dan_tu[0]['id']))->save($option);
        		if($ret){
        			$this->writelog('内容外显数据同步到灵活运营资源库单软件(视频)类型，其中资源库id为'.$dan_tu[0]['id'].'，视屏id为'.$id.'。', 'sj_flexible_extent_soft',$dan_tu[0]['id'],__ACTION__ ,'','edit');
        		}
        	}else{
        		$option['create_at']		=	time();
        		$option['resource_type']	=	29;
        		$affect = M('')->table('sj_flexible_extent_soft')->add($option);
        		if($affect){
                    $cont_Levelids_arr = $affect;#用于 内容生产邮件系统
        			$this->writelog('内容外显数据同步到灵活运营资源库单软件(视频)类型，其中资源库id为'.$affect.'，视频id为'.$id.'。', 'sj_flexible_extent_soft',$affect,__ACTION__ ,'','add');
        		}
        	}
			#用于 内容生产邮件系统 统计用
			if($sysc && $cont_level && $cont_nature && $cont_Levelids_arr){
				$cont_type = 2;
				$conlevelfrom = 3;
                $bgcard_id = $option['extent_id'];
				$contlevelMod -> addData($cont_Levelids_arr,$cont_level,$cont_nature,$cont_type,$conlevelfrom,$resid,$bgcard_id);
			}
            //内容标签
            if($cont_Levelids_arr){
                $tag_sysc = array();
                $tag_sysc['pkgname'] = $tag_pkg['package'];
                $tag_sysc['tags'] = $cont_tags;
                $tag_sysc['eid'] = $cont_Levelids_arr;
                //先删除原有映射内容
                $tag_res = M('')->table('cont_tags_content')->where(array('eid'=>$cont_Levelids_arr))->delete();
                if($tag_res){
                    $this->writelog('内容标签映射表删除关联数据，eid为'.$cont_Levelids_arr.'。', 'cont_tags_content',$cont_Levelids_arr,__ACTION__ ,'','delete');
                }
                //添加新数据
                if($cont_tags){
                    $tag_result = $cont_model->add_cont_attrribute($tag_sysc);
                    if($tag_result){
                        $this->writelog('内容标签映射表添加关联数据，eid为'.$cont_Levelids_arr.'。', 'cont_tags_content',$cont_Levelids_arr,__ACTION__ ,'','add');
                    }
                }
            }

        }

        #用于 内容生产邮件系统 统计用
        if($check_status == 1 && $sysc != 1){
            if($resid && $cont_level && $cont_nature){
                $cont_Levelids_arr = 0;
                $cont_type = 2;
                $conlevelfrom = 3;
                $resid = explode(",",$resid);
                $bgcard_id = '';              
                $contlevelMod -> addData($cont_Levelids_arr,$cont_level,$cont_nature,$cont_type,$conlevelfrom,$resid,$bgcard_id);
            }
        }
        
        if($check_status >= 0){
            //调用接口通知java端
            $post_data = array();
            $notic_ids = explode(',',$resid);
            $post_data['contentType'] = 2; # 1 内容外显 , 2 视频管理
            if($check_status == 1) $post_data['optType'] = 1; # 1 添加\更新 , 2 删除
            if($check_status == 2 || $check_status == 0) $post_data['optType'] = 2; # 1 添加\更新 , 2 删除
            foreach ($notic_ids as $notic_value) {
                //获取包名
                $pack_result = $model->table('sj_soft_extra')->where(array('id'=>$notic_value))->find();
                $post_data['packageName'] = $pack_result['package'];
                $post_data['showStyle'] = 3;
                $post_data['id'] = $notic_value;
                $notic_return = curl_update_content_package($post_data);
                $return_source = json_decode($notic_return,true);
                $do_caozuo = $post_data['optType']==1 ? 'edit' : 'del'; 
                $this->writelog("视频管理接口更新数据 code:".$return_source['code']."MSG:".$return_source['msg']."，视频id为".$post_data['id']."，包名为".$post_data['packageName']."，内容类型为".$post_data['contentType']."。", "",$post_data['id'],__ACTION__ ,'',$do_caozuo);
                
            }
        }
        $status_intro = array(0=>'撤销',1=>'通过',2=>'驳回');
        $now_status = $status_intro[$check_status];
        if($check_status == 1) $now_msg = ",内容性质为{$cont_nature},质量为{$cont_level},来源为{$cont_src},用户倾向为{$user_tend}栏目为{$cont_column}.";
        if($res){
            $this->writelog("{$now_status}了视频，ID为{$id}{$now_msg}", 'sj_soft_extra', $id, __ACTION__,"","edit");
            $this->success("{$now_status}成功");
        }else{
            $this->error("{$now_status}失败");
        }
    }
    
    public function video_cont() {
        $id = $_GET['id'];
        #获取栏目列表
        $cont_model = D('Sj.ContAttribute');
        $id_s = explode(',',$id);
        if(count($id_s) < 2){ //如果为批量通过则不匹配选中值
            $cont_result = M('') -> table('sj_soft_extra') -> where(array('id'=>$id)) -> find();
            $this->assign('video_data', $cont_result);
            $cont_column_num = explode(',',trim($cont_result['cont_column'],','));
            //内容标签
            $tag_result = M('') -> table('cont_tags_content')->where(array('vid'=>$id))->select();
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
        }
        #获取内容属性
        list($content_xz,$content_zl,$con_source,$user_tend,$content_lm,$content_tag) = $cont_model -> show_conattibute($cont_result['cont_quality'],$cont_result['cont_level'],$cont_result['cont_src'],$cont_result['user_tend'],$cont_column_num,$second_tag,$three_rank);
        $this->assign('user_tend',$user_tend);
        $this->assign('content_tag',$content_tag);
        $this->assign('con_source',$con_source);
        $this->assign('content_zl', $content_zl);
        $this->assign('content_xz', $content_xz);
        $this->assign('content_lm', $content_lm);
        
        $check_status = $_GET['check_status'];
        $sysc = $_GET['sysc'] ? $_GET['sysc'] : 0;
        $this->assign('sysc', $sysc);
        $this->assign('check_status', $check_status);
        $this->assign('id', $id);
        $this->display('video_cont');
    }
    
}
