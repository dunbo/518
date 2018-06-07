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


class ContentExplicitAction extends CommonAction {
    const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";

    //内容外显列表
    public function explicit_list() {
        $model = D('Sj.ContentExplicit');
        $where = array();
        if (isset($_GET['s_softid']) || isset($_GET['s_package'])) {
            $wheres = array('status' => 1);
            if (isset($_GET['s_softid'])) {
                $wheres['softid'] = trim($_GET['s_softid']);

                $this->assign('softid', $_GET['s_softid']);
            }
            if (isset($_GET['s_package'])) {
                $wheres['package'] = trim($_GET['s_package']);
                $this->assign('package', $_GET['s_package']);
            }
            $res = $model->table('sj_soft')->where($wheres)->field('package')->select();
            $package = array();
            foreach ($res as $v) {
                $package[] = $v['package'];
            }
            $where['package'] = array('in', $package);
        }
        if($_GET['s_show_style'] && $s_show_style = trim($_GET['s_show_style'])){
            $where['show_style'] = array('eq', $s_show_style);
            $this->assign("show_style", $s_show_style);
        }
        if($_GET['s_template_select'] && $s_template_select = trim($_GET['s_template_select'])){
            $where['template_select'] = array('eq', $s_template_select);
            $this->assign("template_select", $s_template_select);
        }
        if($_GET['s_content_type'] && $s_content_type = trim($_GET['s_content_type'])){
            $where['content_type'] = array('eq', $s_content_type);
            $this->assign("content_type", $s_content_type);
        }

        if($_GET['s_content_from'] && $s_content_from = trim($_GET['s_content_from'])){
            $where['content_from'] = array('eq', $s_content_from);
            $this->assign("content_from", $s_content_from);
        }
        
        if($_GET['begintime'] && $begintime = strtotime(trim($_GET['begintime']))){
            $where["update_tm"] = array('egt', $begintime);
            $this->assign("begintime", $_GET['begintime']);
        }
        if($_GET['endtime'] && $endtime = strtotime(trim($_GET['endtime']))){
            $where["update_tm"] = array('elt', $endtime);
            $this->assign("endtime", $_GET['endtime']);
        }
        if($begintime && $endtime){
            $where["update_tm"] = array('exp', ">=$begintime and update_tm<=$endtime");
        }
        $where['status'] = 1;
        $passed=$_GET['passed']?$_GET['passed']:1;
        $t=time();
        
     
        $where['passed'] = $passed; 
     
            
           // if($passed==2){
           //    $where['end_tm'] = array('exp',"> {$t}"); 
           // }
        if(in_array($passed,array(1,2,3))){//添加内容选项
            //搜索条件
            if($_GET['content_level']) $where["cont_level"] = $_GET['content_level'];
            if($_GET['content_nature']) $where["cont_quality"] = $_GET['content_nature'];
            if($_GET['cont_src']) $where["cont_src"] = $_GET['cont_src'];
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
        
        $this->assign('passed', $passed);

        $count = $model->where($where)->count();
        import("@.ORG.Page2");
        $pg=$_GET['p']?$_GET['p']:1;
        $this->assign('pg', $pg);
        $param = http_build_query($_GET);
        $Page = new Page($count, 10, $param);
        $this->assign('total', $count);
        if($passed==3){
            $list = $model->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('update_tm desc')->select();
        }else{
            $list = $model->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('update_tm desc')->select();
        }
        
       
        $dev_id=array();
        $package=array();

        
        foreach($list as $k=>$v){
          
          $list[$k]['create_tm']=date('Y-m-d H:i:s',$v['create_tm']);
          $list[$k]['update_tm']=date('Y-m-d H:i:s',$v['update_tm']);
          $list[$k]['start_tm']=date('Y-m-d H:i:s',$v['start_tm']);
          $list[$k]['end_tm']=date('Y-m-d H:i:s',$v['end_tm']);
          $dev_id[]=$v['dev_id'];
          $package[]=$v['package'];
          if(in_array($passed,array(1,2,3))){
            //展示内容选项
            $list[$k]['content_select'] = '';
            if($list[$k]['cont_level']) $list[$k]['content_select'] .= '<li style="list-style:none;">内容质量：'.$content_level[$list[$k]['cont_level']]."</li>";
            if($list[$k]['cont_quality']) $list[$k]['content_select'] .= '<li style="list-style:none;">内容性质：'.$content_quality[$list[$k]['cont_quality']];
            if($list[$k]['cont_src']) $list[$k]['content_select'] .= '<li style="list-style:none;">内容来源：'.$content_source[$list[$k]['cont_src']];
            //用户倾向 user_tend
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

        $dev_list=get_table_data(array('dev_id'=>array('in',$dev_id)),"pu_developer","dev_id","*");
        
        $softdata = $model->table('sj_soft_tmp')->where(array('package'=>array('in',$package),'status'=>array('in',array(1,2)),'record_type'=>array('in',array(1,3))))->order('id desc')->select();
        $soft_list=array();
        $category=array();
        $softid=array();
        foreach($softdata as $v){
          $category[]=trim($v['category_id'],',');

          
          if(!$soft_list[$v['package']]){
            $soft_list[$v['package']]=$v;
            $softid[]=$v['id'];
          }
          
          
        }
        $category_list=array();
        // $category_list=get_table_data(array('category_id'=>array('in',$category)),"sj_category","category_id","category_id,name");
        foreach($category as $v){
            $category_list[$v]=$this->get_category($v);
        }

        $icon_list = get_table_data(array('tmp_id' => array('in',$softid)),"sj_soft_file_tmp","tmp_id","tmp_id,iconurl");
        $draft = $_GET['draft'] ? 1 : 0;
        $this->assign('icon_list', $icon_list);

        $this->assign('list', $list);
        $this->assign('dev_list', $dev_list);
        $this->assign('soft_list', $soft_list);
        $this->assign('category_list', $category_list);
        $this->assign('draft', $draft);
        
        
     

        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);

        $this->assign('page', $Page->show());
        $this->display();
    }
    //内容外显 过期删除 驳回 撤销 通过
    public function explicit_handle() {
        #用于 内容生产邮件系统
        if($_GET['cont']){
            $this -> explicit_cont();
            die;
        }

        $model = D('Sj.ContentExplicit');
        $msg = $_POST['reject_reason'];
        $url_source = $_POST['url_source']?$_POST['url_source']:$_GET['url_source'];
        $passed = $_POST['passed']?$_POST['passed']:$_GET['passed'];

        $contlevelMod = D('Sj.ContLevel');#用于 内容生产邮件系统
        $cont_level = $_POST['content_level'] ? $_POST['content_level'] : $_GET['content_level']; #用于 内容生产邮件系统
        $cont_nature = $_POST['content_nature'] ? $_POST['content_nature'] : $_GET['content_nature']; #用于 内容生产邮件系统
        $cont_column = $_POST['content_column'] ? ','.$_POST['content_column'] : ''; #内容栏目
        $cont_src = $_POST['con_source'] ?  $_POST['con_source'] : '0'; #内容来源
        $user_tend = $_POST['user_tend'] ?  $_POST['user_tend'] : '0'; #用户倾向
        $tagstatus = empty($_POST['content_tags']) ? 0 : 1;
        $cont_tags = $_POST['content_tags'];
        $exp_id = $_POST['id'] ? $_POST['id'] : $_GET['id'];

        if(($passed==5 && !$cont_level) || ($passed==2 && !$cont_level)) $this->error('请选择内容质量'); 
        if(($passed==5 && !$cont_nature) || ($passed==2 && !$cont_nature)) $this->error('请选择内容性质'); 
        // if(($passed==5 && !$cont_column) || ($passed==2 && !$cont_column)) $this->error('请选择内容栏目'); 
        #用于 内容生产邮件系统
       
        //passed=4  为删除
        if($_GET['bs']==1 && $passed!=5){
             $this->assign('passed', $passed);
             $this->assign('id', $_GET['id']);
             $this->assign('url_source', $url_source);
             $this->display();
			 exit;
        }else{
            if (!$msg && $passed==3){
              $this->error("驳回原因不能为空");
            }
            
        }
        $id_arr = explode(',', $_POST['id']?$_POST['id']:$_GET['id']);
        $where = array(
            'id' => array('in', $id_arr),
        );
        $data = array(
            'update_tm' => time(),
            'passed' =>$passed,
        );
        if($passed==5){//通过并同步操作
            $data['passed'] = 2;
            $explicit_data  = $model->where(array('id'=>$exp_id))->find();
            $option['title'] = $explicit_data['title'];
            $option['package_name'] = $explicit_data['softname'];
            $option['package_643'] = $explicit_data['package'];
            $option['is_dev'] = 1;
            $rcontent=ContentTypeModel::saveRecommendContent(array('content_type'=>9,'used_id'=>$exp_id),'',$option);
            if($rcontent !==true){
                $this -> error($rcontent);
            }
            // 创建时间和更新时间
            $option['update_at'] = time();
            //内容质量，性质，栏目
            if($cont_level) $option['cont_level'] = $cont_level;
            if($cont_nature) $option['cont_quality'] = $cont_nature;
            if($cont_column) $option['cont_column'] = $cont_column;
            if($cont_src) $option['cont_src'] = $cont_src;
            if($user_tend) $option['user_tend'] = $user_tend;
            $option['tagstatus'] = $tagstatus;
            //获取区间
            $sql_extent = "SELECT extent_id FROM sj_flexible_extent where belong_page_type='fixed_resource_channel' and `status`=1 and extent_type in(24,26) ORDER BY extent_type asc";
            $list_extent = M('')->query($sql_extent);
            
            $show_style = $_POST['show_style'] ? trim($_POST['show_style']) : trim($_GET['show_style']);
            
            if($show_style==1 ){
            	$option['extent_id'] = $list_extent[0]['extent_id'];
            	$sql = "SELECT b.* FROM sj_flexible_extent as a INNER JOIN sj_flexible_extent_soft as b on a.extent_id = b.extent_id WHERE a.belong_page_type= 'fixed_resource_channel' and a.status=1 and b.resource_type= 24 and b.status = 1 and b.package_643 ='{$explicit_data['package']}' and b.content_type=9";
            	$dan_tu =  M('')->query($sql);
                if(isset($dan_tu[0]['id'])){
                    $cont_Levelids_arr = $dan_tu[0]['id'];#用于 内容生产邮件系统
            		$ret = M('')->table('sj_flexible_extent_soft')->where(array('id'=>$dan_tu[0]['id']))->save($option);
            		if($ret){
            			$this->writelog('内容外显数据同步到灵活运营资源库单软件(列表单图)类型，其中资源库id为'.$dan_tu[0]['id'].'，内容外显id为'.$exp_id.'。', 'sj_flexible_extent_soft',$dan_tu[0]['id'],__ACTION__ ,'','edit');
            		}
            	}else{
            		$option['create_at'] = time();
            		$option['resource_type'] = 24;
            		$affect = M('')->table('sj_flexible_extent_soft')->add($option);
            		if($affect){
                        $cont_Levelids_arr = $affect;#用于 内容生产邮件系统
            			$this->writelog('内容外显数据同步到灵活运营资源库单软件(列表单图)类型，其中资源库id为'.$affect.'，内容外显id为'.$$exp_id.'。', 'sj_flexible_extent_soft',$affect,__ACTION__ ,'','add');
            		}
            	}
            }
            if($show_style==2){
            	$option['extent_id'] = $list_extent[1]['extent_id'];
            	$sql_2 = "SELECT b.* FROM sj_flexible_extent as a INNER JOIN sj_flexible_extent_soft as b on a.extent_id = b.extent_id WHERE a.belong_page_type= 'fixed_resource_channel' and b.resource_type= 26 and a.status=1 and b.package_643 ='{$explicit_data['package']}' and b.status = 1 and b.content_type=9";
            	$duo_tu =  M('')->query($sql_2);
                if(isset($duo_tu[0]['id'])){
                    $cont_Levelids_arr = $duo_tu[0]['id'];#用于 内容生产邮件系统
            		$ret = M('')->table('sj_flexible_extent_soft')->where(array('id'=>$duo_tu[0]['id']))->save($option);
            		if($ret){
            			$this->writelog('内容外显数据同步到灵活运营资源库单软件(三图)类型，其中资源库id为'.$duo_tu[0]['id'].'，内容外显id为'.$exp_id.'。', 'sj_flexible_extent_soft',$duo_tu[0]['id'],__ACTION__ ,'','edit');
            		}
            	}else{
            		$option['create_at'] = time();
            		$option['resource_type'] = 26;
            		$affect = M('')->table('sj_flexible_extent_soft')->add($option);
            		if($affect){
                        $cont_Levelids_arr = $affect;#用于 内容生产邮件系统
            			$this->writelog('内容外显数据同步到灵活运营资源库单软件(三图)类型，其中资源库id为'.$affect.'，内容外显id为'.$exp_id.'。', 'sj_flexible_extent_soft',$affect,__ACTION__ ,'','add');
            		}
            	}
            }
            #用于 内容生产邮件系统 统计用
            if($cont_level && $cont_nature && $cont_Levelids_arr){
                $resid = $exp_id;
                if($show_style == 1){
                    $cont_type = 3;
                }else if($show_style == 2){
                    $cont_type = 4;
                }
                $conlevelfrom = 2;
                $bgcard_id = $option['extent_id'];
                $contlevelMod -> addData($cont_Levelids_arr,$cont_level,$cont_nature,$cont_type,$conlevelfrom,$resid,$bgcard_id);
            }
            //内容标签添加
            if($cont_Levelids_arr){
                $cont_model = D('Sj.ContAttribute');
                $tag_sysc = array();
                $tag_sysc['pkgname'] = $option['package_643'];
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
               
        if($passed==3){
            $data['reject_reason'] =$msg;
        }else if($passed==4){
            $data['status'] =0;
            unset($data['passed']);
        }
        
        if($passed==2 || $passed==5){
            $data['cont_level'] = $cont_level;
            $data['cont_quality'] = $cont_nature;
            $data['cont_column'] = $cont_column;
            $data['cont_src'] = $cont_src;
            $data['user_tend'] = $user_tend;
            $data['tagstatus'] = $tagstatus;
            $data['pass_tm'] = time();
            //如果有栏目，添加栏目与内容关联数据
            if (!empty($cont_column)) {
                $column_type = 2;//2：内容外显 3： 视频管理
                $cont_column_ids = explode(',',trim($cont_column,','));
                if($exp_id){ 
                    $content_column_id = $exp_id;
                    $column_res = $model->table('cont_column_content_video')->where(array('column_type'=>$column_type,'con_videoid'=>$content_column_id))->delete();
                    if($column_res){
                        $this->writelog('内容外显删除智盟关联数据，来源id为'.$column_type.'，内容id为'.$content_column_id.'。', 'cont_column_content_video',$content_column_id,__ACTION__ ,'','del');
                    }
                    //添加新的关联数据
                    foreach ($cont_column_ids as $cont_column_key=>$cont_column_value) {
                        $column_data = array();
                        $column_data['column_type'] = $column_type;
                        $column_data['con_videoid'] = $content_column_id;
                        $column_data['columnid'] = $cont_column_value;
                        $column_result = $model->table('cont_column_content_video')->add($column_data);
                        if($column_result){
                            $this->writelog('内容外显添加智盟关联数据，其中来源id为'.$column_type.'，栏目id为'.$cont_column_value.'，内容id为'.$content_column_id.'。', 'cont_column_content_video',$content_column_id,__ACTION__ ,'','add');
                        }
                    }
                }
                
            }
            //如果内容标签不为空，添加到内容标签映射表中
            $video_tag_id = $exp_id;
            $tag_data = array();
            $tag_pkg = $model->where(array('id'=>$exp_id))->find();
            $tag_data['pkgname'] = $tag_pkg['package'];
            $tag_data['cid'] = $exp_id;
            $tag_data['tags'] = $cont_tags;
            //先删除原有映射内容
            $tag_res = M('')->table('cont_tags_content')->where(array('cid'=>$exp_id))->delete();
            if($tag_res){
                $this->writelog('内容标签映射表删除关联数据，cid为'.$tag_data['cid'].'。', 'cont_tags_content',$tag_data['cid'],__ACTION__ ,'','delete');
            }
            //添加新数据
            if($cont_tags){
                $cont_model = D('Sj.ContAttribute');
                $tag_result = $cont_model->add_cont_attrribute($tag_data);
                if($tag_result){
                    $this->writelog('内容标签映射表添加关联数据，cid为'.$tag_data['cid'].'。', 'cont_tags_content',$tag_data['cid'],__ACTION__ ,'','add');
                }
            }

        }
        
        $list = $model->where($where)->field('package,title,id,show_style,dev_id,content_type,content_from,title2')->select();
        $res = $model->where($where)->save($data);
        //调用接口通知java端
        if($passed){
            $post_data = array();
            if($passed == 1 || $passed == 3 || $passed == 4) $post_data['optType'] = 2; # 1 添加\更新 , 2 删除
            if($passed == 2 || $passed == 5 ) $post_data['optType'] = 1; # 1 添加\更新 , 2 删除
            $post_data['contentType'] = 1; # 1 内容外显 , 2 视频管理=
            foreach ($list as $list_k => $list_v) {
                $post_data['id'] = $list_v['id'];
                $post_data['packageName'] = $list_v['package'];
                $post_data['showStyle'] = $list_v['show_style'];
                $notic_return = curl_update_content_package($post_data);
                $return_source = json_decode($notic_return,true);
                $do_caozuo = $post_data['optType']==1 ? 'edit' : 'del'; 
                $this->writelog("内容外显接口更新数据 code:".$return_source['code']."MSG:".$return_source['msg']."，内容id为".$post_data['id']."，包名为".$post_data['packageName']."，内容类型为".$post_data['contentType']."。", "",$post_data['id'],__ACTION__ ,'',$do_caozuo);
                
            }
        }
        
        if ($res) {
            foreach ($list as $k => $v) {
                if($passed==2 || $passed==5){
                    if($v['content_type']==1 && $v['content_from']==2){
                        //通过内容外显时，在content_platform_content内容统计表增加记录
                        $CP = D('Dev.ContentPlatform');
                        $cp_res = $CP->sync_content('edit', array(
                            'type' => 1,
                            'content_id' => $v['id'],
                            'pass_tm' => time(),
                            'status' => 1,
                        ));
                        if($cp_res){
                            $this->writelog("更新了此条内容外显到内容合作平台内容表中，其中内容外显id为{$v['id']}", 'content_platform_content', $cp_res, __ACTION__,"","edit");
                        }
                    }
                    #用于 内容生产邮件系统 统计用
                    if($cont_level && $cont_nature){
                        $show_style = $v['show_style'];
                        $rasid = $v['id'];
                        if($show_style == 1){
                            $cont_type = 3;
                        }else if($show_style == 2){
                            $cont_type = 4;
                        }
                        $conlevelfrom = 2;
                        $cont_Levelids_arr = $bgcard_id = '';
                        $contlevelMod -> addData($cont_Levelids_arr,$cont_level,$cont_nature,$cont_type,$conlevelfrom,$rasid,$bgcard_id);
                    }
                    $this->writelog("通过了此条内容外显到审核中，其中id为{$v['id']},标题名称为{$v['title']},包名为{$v['package']},内容性质为{$v['cont_quality']},质量为{$v['cont_level']},来源为{$v['cont_src']},栏目为{$v['cont_column']}。", 'sj_soft_content_explicit', $v['id'], __ACTION__,"","edit");
                }else if($passed==3){
                    if($v['content_type']==1 && $v['content_from']==2){
                        //通过内容外显时，在content_platform_content内容统计表增加记录
                        $CP = D('Dev.ContentPlatform');
                        $cp_res = $CP->sync_content('edit', array(
                            'type' => 1,
                            'content_id' => $v['id'],
                            'status' => 3,
                        ));
                        if($cp_res){
                            $this->writelog("更新了此条内容外显到内容合作平台内容表中，其中内容外显id为{$v['id']}", 'content_platform_content', $cp_res, __ACTION__,"","edit");
                        }
                    }
                    $this->writelog("驳回了此条内容外显，其中id为{$v['id']},标题名称为{$v['title']},包名为{$v['package']}。驳回原因：{$msg}", 'sj_soft_content_explicit', $v['id'], __ACTION__,"","edit");
                }else if($passed==1){
                    $this->writelog("撤销了此条内容外显到审核中，其中id为{$v['id']},标题名称为{$v['title']},包名为{$v['package']}。", 'sj_soft_content_explicit', $v['id'], __ACTION__,"","edit");
                }else if($passed==4){
                    $this->writelog("删除了此条内容外显到审核中，其中id为{$v['id']},标题名称为{$v['title']},包名为{$v['package']}。", 'sj_soft_content_explicit', $v['id'], __ACTION__,"","del");
                }
                
            }
            if($url_source){
                $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/ContentExplicit/explicit_list/passed/1&1/1');
            }
            $this->success("操作成功");
        }else{
            $this->success("操作失败");
        }
    }
    public function get_category($category_id){
        $category_data=get_table_data(array('category_id'=>$category_id),"sj_category","category_id","*");
        if($category_data[$category_id]['parentid']==0){
          return $category_data[$category_id]['name'];
        }else{
          return $this->get_category($category_data[$category_id]['parentid']);
        }
    }
    public function explicit_edit(){
        $model = D('Sj.ContentExplicit');
        if($_GET['id']||$_GET['add']){
            if($_GET['id']){
                $explicit_data=$model->where(array('id'=>$_GET['id']))->find();
                $explicit_data['start_tm']=$explicit_data['start_tm']?date('Y-m-d H:i:s',$explicit_data['start_tm']):'';
                $explicit_data['end_tm']=$explicit_data['end_tm']?date('Y-m-d H:i:s',$explicit_data['end_tm']):'';
                $explicit_data['az_style_content']= $explicit_data['template_select'] == 3 ?  $explicit_data['az_style_content'] :json_decode($explicit_data['az_style_content'],true) ;
                $explicit_data['explicit_pic']=json_decode($explicit_data['explicit_pic'],true);
                $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
                $this->assign('explicit_data', $explicit_data);
                $this->assign('az_style_content', $explicit_data['az_style_content']);
                $this->assign('passed', $_GET['passed']);
                $soft_data=$model->table('sj_soft')->where(array('package'=>$explicit_data['package'],'status'=>1,'hide'=>1))->find();
                $category_id=trim($soft_data['category_id'],",");
                $this->assign('category_name', $this->get_category($category_id));

                #查询置顶视频信息
                $top_video = $model->table('sj_soft_content_exp_video')->where(array('contentid'=>$_GET['id']))->field('id')->find();
                $this->assign('top_video', $top_video['id']);
            }

            //获取内容属性，标签
            $cont_model = D('Sj.ContAttribute');
            $cont_column_num = explode(',',trim($explicit_data['cont_column'],','));  
            //内容标签
            $tag_result = M('') -> table('cont_tags_content')->where(array('cid'=>$_GET['id']))->select();
            
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
            list($content_xz,$content_zl,$con_source,$user_tend,$content_lm,$content_tag) = $cont_model -> show_conattibute($explicit_data['cont_quality'],$explicit_data['cont_level'],$explicit_data['cont_src'],$explicit_data['user_tend'],$cont_column_num,$second_tag,$three_rank);
            $this->assign('content_tag',$content_tag);
            $this->assign('content_xz', $content_xz);
            $this->assign('content_zl', $content_zl);
            $this->assign('content_lm', $content_lm);
            $this->assign('con_source',$con_source);  
            $this->assign('user_tend',$user_tend);  
            
            include(SITE_PATH.'/Public/js/editorv1/php/serv_config.php');
            $this->assign('editor_config',$CONFIG);
            $this->display();
        }else if($_POST){
            $data=array();
            $data['id']=trim($_POST['id']);
            if($data['id']){
                $explicit_data_before=$model->where(array('id'=>$data['id']))->find();
                $az_style_content_before=json_decode($explicit_data_before['az_style_content'],true);
                $explicit_pic_before=json_decode($explicit_data_before['explicit_pic'],true);
                $data['passed']=$explicit_data_before['passed'];
            }else{
                $az_style_content_before=array();
                $explicit_pic_before=array();
                $data['package']=trim($_POST['package']);
                $soft_data=$model->table('sj_soft')->where(array('package'=>$data['package']))->find();
                $data['softname']=$soft_data['softname'];
                $data['dev_id']=$soft_data['dev_id'];
                $data['passed']=1;
               

            }
            if($data['passed'] == 2 && $_POST['draft']){
                $this->error('发布中的内容不能保存草稿箱');
            }

            if($_POST['start_tm']){
                $data['start_tm']=strtotime(trim($_POST['start_tm']));
            }
            if($_POST['end_tm']){
                $data['end_tm']=strtotime(trim($_POST['end_tm']));
            }
            
            // $where=array();
            // $where["start_tm"] = array('elt', $data['end_tm']);
            // $where["end_tm"] = array('egt', $data['start_tm']);
            // $where['id']=array('neq',$data['id']);
            // $where['status']=1;
            // $where['package']=$explicit_data_before['package'];
            
            // $re_data=$model->where($where)->find();
            // if($re_data){
            //     $this->error("包名为{$explicit_data_before['package']}的内容外显与id为{$re_data['id']}时间交叉。");
            // }
            $data['show_style']=trim($_POST['show_style']);
            
            $az_model_num_sub=$_POST['az_model_num_sub'];
            $data['template_select']=trim($_POST['template_select']);

            $up_file=array();
            if($az_model_num_sub>0 && $data['template_select']==1){
                for($az_num=1;$az_num<($az_model_num_sub+1);$az_num++){

                    $az_style_content_before['az_style_'.$az_num]['article']=trim($_POST['article'.$az_num]);
                    $az_style_content_before['az_style_'.$az_num]['deputy_show']=trim($_POST['deputy_show'.$az_num]);
                    $az_style_content_before['az_style_'.$az_num]['pic_pattern']=trim($_POST['pic_pattern'.$az_num]);

                    if(trim($_POST['deputy_show'.$az_num])==1){
                        $pic_pattern_image=$this->upload_pic($_FILES['pic_pattern_image'.$az_num],'pic_pattern_image'.$az_num,$az_num,trim($_POST['pic_pattern'.$az_num]));
                        $up_file=array_merge($up_file,$pic_pattern_image);
                        if(count($az_style_content_before['az_style_'.$az_num]['pic_pattern_image'])){
                            foreach($az_style_content_before['az_style_'.$az_num]['pic_pattern_image'] as $kkk=>$vvv){
                                  $len=strlen($kkk);
                                  if(substr($kkk,$len-1,1)>$az_style_content_before['az_style_'.$az_num]['pic_pattern']-1){
                                      unset($az_style_content_before['az_style_'.$az_num]['pic_pattern_image'][$kkk]);
                                  }
                                  
                            }
                        }
                    }else{
                        unset($az_style_content_before['az_style_'.$az_num]['pic_pattern']);
                        unset($az_style_content_before['az_style_'.$az_num]['pic_pattern_image']);
                    }
                    

                }
            }

            
            if($data['show_style']==1 || $data['show_style']==2){
                $show_style_img_num=($data['show_style']==1)?1:4;
                $explicit_pic=$this->upload_pic($_FILES['explicit_pic'],'explicit_pic',0,$show_style_img_num);
                $up_file=array_merge($up_file,$explicit_pic);
                if($explicit_pic_before){
                    foreach($explicit_pic_before as $kk=>$vv){

                        if(substr($kk,3,1)>$show_style_img_num-1){
                            unset($explicit_pic_before['pic'.substr($kk,3,1)]);
                        }
                    }
                }
            }

            if(count($up_file)){
                $file_path_arr=$this->upload_pic_do($up_file);
            }
            
            
                
            
            
            foreach($file_path_arr as $k=>$v){
                 if(substr($k,0,3)=='pic'){   
                    $explicit_pic_before[$k]=$v;
                 }else{
                    if($data['template_select']==1){
                        $az_num=(substr($k,5,1)=='p')?substr($k,4,1):substr($k,4,2);
                        if($az_num && $az_style_content_before['az_style_'.$az_num]['deputy_show']==1){
                            $az_style_content_before['az_style_'.$az_num]['pic_pattern_image'][$k]=$v;
                        }
                    }
                    
                 }
                 
            }

            $data['explicit_pic']=json_encode($explicit_pic_before);
            if($data['template_select']==1){
                $data['az_style_content']=json_encode($az_style_content_before);
                $data['h_five_link_url']='';
            }elseif($data['template_select']==3){
                $data['az_style_content'] = $_POST['editorValue'];
                $data['h_five_link_url']='';
                //替换掉编辑器中的more_content_ul list-paddingleft-2样式
                $data['az_style_content'] = str_replace('more_content_ul list-paddingleft-2', 'more_content_ul', $data['az_style_content']);
            }else{
                $data['h_five_link_url']=trim($_POST['h_five_link_url']);
                $data['az_style_content']='';
            }
            
            $data['content_type']=trim($_POST['content_type']);
            $data['title']=trim($_POST['title']);
            $data['title2']=trim($_POST['title2']);
            
            $data['bottom_download_pink']=trim($_POST['bottom_download_pink']);
            // echo "<pre>";var_dump($data);die;

            $data['update_tm']=time();
            #保存草稿过来的数据，不需要所有的数据都填写，如果是没有填写，
            #是null值导致无法保存到数据库，在这个地方null值置为空字符串
            $exturl = '';
            #保存草稿
            if($_POST['draft'] == 1 ){
                foreach ($data as $key => $value) {
                    $data[$key] = $data[$key] ? $data[$key] : '';
                }
                $exturl = '/draft/1/';
                $_POST['passed'] = $data['passed'] = 4;
            }elseif($data['passed'] == 4){
                #草稿提交到审核
                $data['passed'] = 1;
                $_POST['passed'] = 1;
                
            }

            #内容来源 
            $data['cont_src'] = $_POST['con_source']  ? $_POST['con_source'] : 0;
            #用户倾向
            $data['user_tend'] = $_POST['user_tend']  ? $_POST['user_tend'] : 0;
            $data['cont_level'] = $_POST['content_level'] ? $_POST['content_level'] : 0; #内容质量
            $data['cont_quality'] = $_POST['content_nature'] ? $_POST['content_nature'] : 0; #内容性质
            $data['cont_column'] = $_POST['content_column'] ? ','.$_POST['content_column'] : '';#内容栏目
            $data['tagstatus'] = $_POST['content_tags'] ? 1 : 0;
            $cont_tags = $_POST['content_tags'];
            $cont_column = $_POST['content_column'];

            #只有安智样式和富媒体样式支持顶部视频
            $need_top_video = 0;
            if($data['template_select']==1 || $data['template_select']==3){
                $need_top_video = 1;
            }
            if($data['id']){
                $log = $this -> logcheck(array('id' => $data['id']),'sj_soft_content_explicit',$data,$model);
                $re = $model->save($data);
                if($re){
                    //编辑已通过的内容外显，更新content_platform_content内容统计表的title
                    if($explicit_data_before['passed']==2 && $explicit_data_before['content_from']==2 && $explicit_data_before['content_type']==1){
                        $CP = D('Dev.ContentPlatform');
                        $cp_res = $CP->sync_content('edit', array(
                            'type' => 1,
                            'content_id' => $data['id'],
                            'title' => $data['title'],
                        ));
                        if($cp_res){
                            $this->writelog("更新了此条内容外显到内容合作平台内容表中，其中内容外显id为{$data['id']},标题为{$data['title']},内容性质为{$data['cont_quality']},质量为{$data['cont_level']},来源为{$data['cont_src']},栏目为{$data['cont_column']}", 'content_platform_content', $cp_res, __ACTION__,"","edit");
                        }
                    }
                }
                #添加置顶视频信息
                if($need_top_video && $_POST['softvideoid']){
                    $vdata['id'] = $_POST['softvideoid'];
                    $vdata['contentid'] = $data['id'];
                    $model->table('sj_soft_content_exp_video')->save($vdata);
                }

                //调用接口通知java端
                $post_data = array();
                $post_data['id'] = $data['id'];
                $post_data['contentType'] = 1; # 1 内容外显 , 2 视频管理
                $post_data['optType'] = 1; # 1 添加\更新 , 2 删除
                $post_data['packageName'] = $explicit_data_before['package'];
                $post_data['showStyle'] = $data['show_style'];
                $notic_return = curl_update_content_package($post_data);
                $return_source = json_decode($notic_return,true);
                $this->writelog("内容外显接口更新数据 code:".$return_source['code']."MSG:".$return_source['msg']."，内容id为".$post_data['id']."，包名为".$post_data['packageName']."，内容类型为".$post_data['contentType']."。", "",$post_data['id'],__ACTION__ ,'','edit');
                
                //添加内容栏目映射数据
                if (!empty($cont_column)) {
                    $column_type = 2;//2：内容外显 3： 视频管理
                    $cont_column_ids = explode(',',trim($cont_column,','));
                    //先删除关联表中的对应的内容序号id
                    $column_res = M('')->table('cont_column_content_video')->where(array('column_type'=>$column_type,'con_videoid'=>$data['id']))->delete();
                    if($column_res){
                        $this->writelog('内容外显删除智盟关联数据，来源id为'.$column_type.'，内容id为'.$data['id'].'。', 'cont_column_content_video',$data['id'],__ACTION__ ,'','delete');
                    }
                    //添加新的关联数据
                    foreach ($cont_column_ids as $cont_column_key=>$cont_column_value) {
                        $column_data = array();
                        $column_data['column_type'] = $column_type;
                        $column_data['con_videoid'] = $data['id'];
                        $column_data['columnid'] = $cont_column_value;
                        $column_result = M('')->table('cont_column_content_video')->add($column_data);
                        if($column_result){
                            $this->writelog('内容外显添加智盟关联数据，其中来源id为'.$column_type.'，栏目id为'.$cont_column_value.'，内容id为'.$data['id'].'。', 'cont_column_content_video',$data['id'],__ACTION__ ,'','add');
                        } 
                    }
                }
                //添加内容标签映射数据
                $tag_data = array();
                $tag_data['pkgname'] = $explicit_data_before['package'];
                $tag_data['cid'] = $data['id'];
                $tag_data['tags'] = $cont_tags;
                //先删除原有映射内容
                $tag_res = M('')->table('cont_tags_content')->where(array('cid'=>$data['id']))->delete();
                if($tag_res){
                    $this->writelog('内容标签映射表删除关联数据，cid为'.$data['id'].'。', 'cont_tags_content',$data['id'],__ACTION__ ,'','delete');
                }
                //添加新数据
                if($cont_tags){
                    $cont_model = D('Sj.ContAttribute');
                    $tag_result = $cont_model->add_cont_attrribute($tag_data);
                    if($tag_result){
                        $this->writelog('内容标签映射表添加关联数据，cid为'.$data['id'].'。', 'cont_tags_content',$data['id'],__ACTION__ ,'','add');
                    }
                }

                if($re){
                    $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/ContentExplicit/explicit_list/passed/'.$_POST['passed'].$exturl);
                    $this->writelog('修改了内容外显id为'.$data["id"].'。'.$log, 'sj_soft_content_explicit',$data["id"],__ACTION__ ,'','edit');
                    $this->success('编辑成功');
                }else{
                    $this->error('编辑失败');
                }
            }else{

                $data['create_tm']=time();
                $data['cont_src'] = $_POST['con_source'];
                $data['user_tend'] = $_POST['user_tend'];
                $re=$model->add($data);

                //调用接口通知java端
                $post_data = array();
                $post_data['id'] = mysql_insert_id();
                $post_data['contentType'] = 1; # 1 内容外显 , 2 视频管理
                $post_data['optType'] = 1; # 1 添加\更新 , 2 删除
                $post_data['packageName'] = $data['package'];
                $post_data['showStyle'] = $data['show_style'];
                $notic_return = curl_update_content_package($post_data);
                $return_source = json_decode($notic_return,true);
                $this->writelog("内容外显接口更新数据 code:".$return_source['code']."MSG:".$return_source['msg']."，内容id为".$post_data['id']."，包名为".$post_data['packageName']."，内容类型为".$post_data['contentType']."。", "",$post_data['id'],__ACTION__ ,'','add');
                
                //添加内容栏目映射数据
                if (!empty($cont_column)) {
                    $column_type = 2;//2：内容外显 3： 视频管理
                    $cont_column_ids = explode(',',trim($cont_column,','));
                    //先删除关联表中的对应的内容序号id
                    $column_res = M('')->table('cont_column_content_video')->where(array('column_type'=>$column_type,'con_videoid'=>$post_data['id']))->delete();
                    if($column_res){
                        $this->writelog('内容外显删除智盟关联数据，来源id为'.$column_type.'，内容id为'.$post_data['id'].'。', 'cont_column_content_video',$post_data['id'],__ACTION__ ,'','delete');
                    }
                    //添加新的关联数据
                    foreach ($cont_column_ids as $cont_column_key=>$cont_column_value) {
                        $column_data = array();
                        $column_data['column_type'] = $column_type;
                        $column_data['con_videoid'] = $post_data['id'];
                        $column_data['columnid'] = $cont_column_value;
                        $column_result = M('')->table('cont_column_content_video')->add($column_data);
                        if($column_result){
                            $this->writelog('内容外显添加智盟关联数据，其中来源id为'.$column_type.'，栏目id为'.$cont_column_value.'，内容id为'.$post_data['id'].'。', 'cont_column_content_video',$post_data['id'],__ACTION__ ,'','add');
                        } 
                    }
                }
                //添加内容标签映射数据
                $tag_data = array();
                $tag_data['pkgname'] = $data['package'];
                $tag_data['cid'] = $post_data['id'];
                $tag_data['tags'] = $cont_tags;
                //先删除原有映射内容
                $tag_res = M('')->table('cont_tags_content')->where(array('cid'=>$tag_data['cid']))->delete();
                if($tag_res){
                    $this->writelog('内容标签映射表删除关联数据，cid为'.$tag_data['cid'].'。', 'cont_tags_content',$tag_data['cid'],__ACTION__ ,'','delete');
                }
                //添加新数据
                if($cont_tags){
                    $cont_model = D('Sj.ContAttribute');
                    $tag_result = $cont_model->add_cont_attrribute($tag_data);
                    if($tag_result){
                        $this->writelog('内容标签映射表添加关联数据，cid为'.$tag_data['cid'].'。', 'cont_tags_content',$tag_data['cid'],__ACTION__ ,'','add');
                    }
                }

                if($re){
                    $msg = '添加成功';
                    $exturl = '';
                    if($data['passed'] == 4){
                        $exturl = 'draft/1/';
                        $tips = '添加了一条内容外显到草稿箱，其中id为'.$re.'。';
                        $msg = '添加草稿成功';

                    }else{
                        $tips = '添加了一条内容外显，其中id为'.$re.",内容性质为{$data['cont_quality']},质量为{$data['cont_level']},来源为{$data['cont_src']},栏目为{$data['cont_column']}";
                    }
                    $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/ContentExplicit/explicit_list/passed/'.$_POST['passed'].'/'.$exturl);
                    $this->writelog($tips, 'sj_soft_content_explicit',$re,__ACTION__ ,'','add');
                    #添加置顶视频信息
                    if($need_top_video && $_POST['softvideoid']){
                        $vdata['id'] = $_POST['softvideoid'];
                        $vdata['contentid'] = $re;
                        $model->table('sj_soft_content_exp_video')->save($vdata);
                    }


                    $this->success($msg);
                }else{
                    $this->error('添加失败');
                }
            }
            
            
        }
    }
    public function upload_pic($file_arr,$source_key,$text_n=0,$num){
        // parent::error('1111');
        $file = array();
        // var_dump($num);
        // echo "<pre>";var_dump($file_arr);
        // var_dump($source_key);
        if ($file_arr) {
            $i=0;
            foreach ($file_arr as $key => $val) {
                if($source_key=='explicit_pic'){
                    if($key=='size'){
                        foreach($val as $s_k=>$s_v){
                            if(($s_v>512000) && $s_k<$num){
                              // var_dump(123);die;
                                $pic_n=$s_k+1;
                                $this->error("外显图片第{$pic_n}张上传失败");
                            }else if($s_k>=$num){
                                unset($file_arr['tmp_name'][$s_k]);
                                continue;
                            }else if(!$s_v){
                                unset($file_arr['tmp_name'][$s_k]);
                                continue;
                            }
                        }
                    }
                }
                // else if($text_n){
                //     if($key=='size'){
                //         foreach($val as $s_k=>$s_v){
                //             if(($s_v>102400) && $s_k<$num){
                //                 $pic_n=$s_k+1;
                //                 $this->error("文章正文{$text_n}的第{$pic_n}张图片上传失败");
                //             }else if($s_k>=$num){
                //                 unset($file_arr['tmp_name'][$s_k]);
                //                 continue;
                //             }else if(!$s_v){
                //                 unset($file_arr['tmp_name'][$s_k]);
                //                 continue;
                //             }
                //         }
                //     }
                // }
                if($key=='error'){
                    foreach($val as $s_k=>$e_v){
                        // var_dump($e_v);
                        // var_dump($num);
                        // var_dump($s_k);
                        if($e_v==0&&$s_k<$num){
                            $i++;
                        }else{
                            // var_dump($file_arr['tmp_name'][$s_k]);
                            unset($file_arr['tmp_name'][$s_k]);
                            // var_dump($file_arr['tmp_name']);die;
                            continue;
                        }
                    }
                }
                
            }
            foreach ($file_arr as $key => $val) {
                if($key=='tmp_name'){

                    foreach($val as $e_k=>$e_v){
                        if($e_k<$num){
                            if($text_n){
                                $pic="text{$text_n}pic".$e_k;
                            }else{
                                $pic='pic'.$e_k;
                            }
                            
                            $file[$pic] = '@' . $e_v;
                            $image_file = getimagesize(substr($file[$pic],1));
                            $file[$pic] = curl_file_create($e_v);
                            if($source_key=='explicit_pic'){
                                $pic_n=($e_k+1);
                                if($_POST['show_style']==1){
                                  $true_w=464;
                                  $true_h=274;
                                }else if($_POST['show_style']==2){
                                  if($pic_n<2){
                                    $true_w=464;
                                    $true_h=274;
                                  }else{
                                    $true_w=160;
                                    $true_h=160;
                                  }
                                  
                                }
                                if($image_file[0] != $true_w || $image_file[1] != $true_h){
                                    $this->error("外显图片第{$pic_n}张上传失败,图片尺寸：{$true_w}×{$true_h}，小于500KB");
                                }
                            }
                            // else if($text_n){
                            //     $pic_n=($e_k+1);
                            //     if(trim($_POST['pic_pattern'.$text_n])==1){
                            //       $true_w=640;
                            //       $true_h=270;
                            //     }else if(trim($_POST['pic_pattern'.$text_n])==2){
                            //       $true_w=280;
                            //       $true_h=450;
                            //     }else if(trim($_POST['pic_pattern'.$text_n])==3){
                            //       if($pic_n<2){
                            //         $true_w=280;
                            //         $true_h=450;
                            //       }else{
                            //         $true_w=280;
                            //         $true_h=215;
                            //       }
                            //     }
                            //     if($image_file[0] != $true_w || $image_file[1] != $true_h){
                            //       $this->error("文章正文{$text_n}的图片第{$pic_n}张上传失败,图片尺寸：{$true_w}×{$true_h}，小于100KB");
                            //     }
                            // }
                        }
                        
                        
                    }
                }
            }
        }
        // var_dump($file);die;
        return $file;
        
    }
    public function upload_pic_do($file){
        if ($file) {
            $file['static_data'] = '/data/att/m.goapk.com';
            $file['do'] = 'save';
        }
        $upload_model = D("Dev.Uploadfile");
        $upload = $upload_model->_http_post($file);
        // echo "<pre>";var_dump($upload);die;
        if ($upload['info']['http_code'] != 200) {
            $this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
        }
        $pic_arr=array();
        if ($upload['ret']) {
            foreach ($upload['ret'] as $key => $val) {
                if ($val == 'failed') {
                    continue;
                } else {
                    $pic_arr[$key] = $val;
                }
            }
        }
        if(count($pic_arr)!=(count($file)-2)){
            // if($source_key=='explicit_pic'){
            //   $this->error("外显图片上传失败");
            // }
            $this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
        }
        
        return $pic_arr;
    }
    public function preview_content_explicit(){
        $model = D('Sj.ContentExplicit');
        if($_GET['id']){
            $data = $model->where(array('id'=>trim($_GET['id'])))->field('*')->find();
            $data['az_style_content']=$data['template_select'] == 3? $data['az_style_content']:json_decode($data['az_style_content'],true);
            $data['explicit_pic']=json_decode($data['explicit_pic'],true);
            $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
            $data['create_tm']=date('Y-m-d',$data['create_tm']);
             if($data['template_select'] == 3){

                $data['az_style_content'] = preg_replace_callback('/<img([^>]+)(src=[\'"][^\'"]+[\'"])([^>]*)>/',array($this,"replace_content"), $data['az_style_content']);
            }
            $this->assign('data', $data);
            $soft_data_note = $model->table('sj_soft_note')->where(array('package'=>$data['package']))->field('*')->find();
            $this->assign('in_short', $soft_data_note['in_short']);
            // $soft_data = $model->table('sj_soft')->where(array('package'=>$data['package'],'status'=>1,'hide'=>1))->field('*')->find();
            $soft_data = $model->table('sj_soft')->where(array('package'=>$data['package']))->field('*')->find();

            $soft_data_file = $model->table('sj_soft_file')->where(array('softid'=>$soft_data['softid']))->field('*')->find();
            $this->assign('iconurl_72', $soft_data_file['iconurl_72']);
            #查找顶部视频的相关信息
            if($data['template_select'] == 1 ||$data['template_select'] == 3 ){
                $where = array('contentid'=>$data['id']);
                $this->assign('img_url',GAMEINFO_ATTACHMENT_HOST);
                $video_data = $model->table('sj_soft_content_exp_video')->where($where)->find();
                $video_cover = '/static/default_black.jpg';
                $this->assign('video_cover',  $video_cover);
                if ($video_data){
                    if($video_data['type']>=2 && !$video_data['coverpath']){
                        $video_data['coverpath'] = $video_cover;
                    }
                    $this->assign('video_data',$video_data);
                    
                }
            }
            


            $this->display();
        }else{
            $this->error("id不能为空");
        }
        
    }
    // 远程请求检查根据包名查找软件名
    public function pub_check_package() {
        $package = $_POST['package'];
        // var_dump($package);die;
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
            // 'hide' => 1,
        );
        $find = $model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
        if ($find)
            return $find['softname'];
        return 0;            
    }

    //删除草稿想内容
    public function draft_delete(){
        $model = D('Sj.ContentExplicit');
        if(!$_GET['id']){
            $this->error('草稿不存在');
        }
        $data=$model->where(array('id'=>$_GET['id'],'passed'=>4))->find();
        // echo $model->getLastSql();die;

        if(empty($data)){
            $this->error('删除的草稿不存在');
        }
        $change['status'] = 0;
        $change['id'] = $_GET['id'];
        $res = $model->save($change);
        if($res){
            $this->writelog("删除了此条草稿箱的内容，其中id为{$data['id']},标题名称为{$data['title']}", 'sj_soft_content_explicit', $data['id'], __ACTION__,"","del");
            $this->success('草稿删除成功');
        }else{
            $this->error('草稿删除失败');
        }

    }

    public function explicit_cont(){

        $id = $_GET['id'];
        //获取此条内容性质,质量，栏目
        $cont_result = M('') -> table('sj_soft_content_explicit') -> where(array('id'=>$id)) -> find();
        $cont_column_num = explode(',',trim($cont_result['cont_column'],','));
        //内容标签
        $tag_result = M('') -> table('cont_tags_content')->where(array('cid'=>$id))->select();
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
        $cont_model = D('Sj.ContAttribute');
        list($content_xz,$content_zl,$con_source,$user_tend,$content_lm,$content_tag) = $cont_model -> show_conattibute($cont_result['cont_quality'],$cont_result['cont_level'],$cont_result['cont_src'],$cont_result['user_tend'],$cont_column_num,$second_tag,$three_rank);
        $this->assign('user_tend',$user_tend);
        $this->assign('content_tag',$content_tag);
        $this->assign('con_source',$con_source);
        $this->assign('content_zl', $content_zl);
        $this->assign('content_xz', $content_xz);
        $this->assign('content_lm', $content_lm);
    
        $typeid = $_GET['typeid'];
        $pkg = $_GET['pkg'];
        $this->assign('typeid', $typeid);
        $this->assign('pkg', $pkg);
        $this->assign('id', $id);
        $this->display('explicit_cont');
    }

    public function replace_content($matches){
        if(strpos($matches[0], 'dataoriginal')!==false){
            return '<img'.$matches[1].$matches[3].'>';
        }else{
            return $matches[0];
        }
    }

    public function video_edit(){
        $model = D('Sj.ContentExpVideo');
        if($_GET['id']||$_GET['add']){
            if($_GET['id']){
                $video_data=$model->where(array('id'=>$_GET['id']))->find();
                $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
                $this->assign('video_data', $video_data);
                if($video_data['type'] == 3){
                    // $soft_data=$model->table('sj_soft')->where(array('package'=>$video_data['package'],'status'=>1,'hide'=>array('in',array(1,1024))))->find();
                    // $video_data['softname']=$soft_data['softname'];
                    $video_info = $model->table('sj_soft_extra')->field('id,package,video_pic,video_url,video_title')->where(array('check_status'=>'1','status' => 1,'package'=>$video_data['pkgname']))->select();
                    $this->assign('video_info', $video_info);
                }
            }
            $this->display();
        }else if($_POST){
            if(!$_POST['videotype']){
                $this->error('请选择视频类型');
            }
            $data['type']=$_POST['videotype'];
            $video_path = trim($_POST['videourl']);
            if($_POST['videotype'] == 1 ){
                if(!$video_path){
                    $this->error('视频链接不能为空');
                }
                if(substr($video_path,0,4) != 'http'){
                    $this->error('请填写合法的视频链接');
                }
                if(strpos($video_path, 'youku') === 'false'){
                    $this->error('外链视频只支持优酷');
                }
                $data['videopath'] = $video_path;
            }
            if($_POST['videotype'] == 2){
                
                if($_FILES['videopath']['tmp_name']){
                    $ext = strtolower(pathinfo($_FILES['videopath']['name'],PATHINFO_EXTENSION));
                    if($_FILES['videopath']['size'] > 100*1024*1024) {  //1M
                        $this->error("请上传100M以内MP4格式视频");
                    } else if(!in_array($ext,array('mp4'))) {
                        $this->error('请上传100M以内MP4格式视频');
                    }
                }

                if($_FILES['coverpath']['tmp_name']){
                    $img_ext = strtolower(pathinfo($_FILES['coverpath']['name'],PATHINFO_EXTENSION));
                    if(!in_array($img_ext,array('png','jpg'))) {
                        $this->error('图片格式只支持jpg,png');
                    }
                }

                if(!$_POST['id'] && !$_FILES['videopath']['tmp_name']){
                    $this->error('请上传100M以内MP4格式视频');
                }
                if($_FILES['coverpath']['tmp_name']){
                    // $file_arr = array('coverpath'=>'@'.$_FILES['coverpath']['tmp_name']);
                    // $up_file_img=$this->upload_pic_do($file_arr);
                    // $data['coverpath'] = $up_file_img['coverpath'];
                    $extra['do'] = 'image' ;
                    $res = $model->new_http_post($_FILES['coverpath'],$extra);
                    // var_dump($res,1);die;
                    $data['coverpath'] = $res['ret']['ret']['upfile'];
                }
                if($_FILES['videopath']['tmp_name']){
                    $extra['do'] = 'video' ;
                    $res = $model->new_http_post($_FILES['videopath'],$extra);
                    $data['videopath'] = $res['ret']['ret']['upfile'];
                    // var_dump($res);die;
                 }
                
            }
            if($_POST['videotype'] == 3){
                if(!$_POST['videoselect']){
                    $this->error('请选择正确的视频');
                }
                $video_info = $model->table('sj_soft_extra')->field('id,package,video_pic,video_url,video_title')->where(array('check_status'=>'1','status' => 1,'id'=>$_POST['videoselect']))->find();
                $data['coverpath'] = $video_info['video_pic'];
                $data['pkgname'] = $video_info['package'];
                $data['videopath'] = $video_info['video_url'];
                $soft_info = $model->table('sj_soft')->field('softname')->where(array('package'=>$video_info['package'],'status'=>1,'hide'=>1))->find();
                // echo $model->getLastSql();
                $data['softname'] = $soft_info['softname'];
                $data['videolibid'] = $_POST['videoselect'];
            }

            if($_POST['id']){
                $data['id']=intval($_POST['id']);
                $re=$model->save($data);
                // echo $model->getLastSql();
                $this->writelog('修改了视频id为'.$data["id"].'。'.$log, 'sj_soft_content_exp_video',$data["id"],__ACTION__ ,'','edit');
                // $check_status = isset($video_data['check_status'])?$video_data['check_status']:0;
                // $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/VideoManage/video_list/check_status/'.$check_status);
                $this->ajaxReturn($data['id'],'编辑成功',1);
                
            }else{
                $re=$model->add($data);
                // echo $model->getLastSql();
                if($re){
                    $task_data['id']=$re;
                    $this->writelog('添加了一条视频，其中id为'.$re.'。', 'sj_soft_content_exp_video',$re,__ACTION__ ,'','add');
                    // $this->assign("jumpUrl",'/index.php/'.GROUP_NAME.'/VideoManage/video_list/check_status/0');
                    // $this->success('添加成功');
                    $this->ajaxReturn($re,'添加成功',1);
                }else{
                    $this->ajaxReturn($re,'添加失败',1);
                }
            }
            
            
        }
    }

    public function get_video_lib(){
        $pkg = trim($_POST['package']);
        $video_model = M('soft_extra');
        $soft_info = $video_model->table('sj_soft')->field('softname')->where(array('package'=>$pkg,'status'=>1,'hide'=>1))->find();
        if($soft_info){
            $video_info = $video_model->field('id,video_pic,video_url,video_title')->where(array('check_status'=>'1','status' => 1,'package'=>$pkg))->select();
            // echo $video_model->getLastSql();
            if($video_info){
                foreach ($video_info as $key => $value) {
                    $video_info[$key]['video_pic'] = GAMEINFO_ATTACHMENT_HOST.$value['video_pic'];
                    $video_info[$key]['video_url'] = GAMEINFO_ATTACHMENT_HOST.$value['video_url'];
                    # code...
                }
                $soft_info['video_info'] = $video_info;
          
                $this->ajaxReturn($soft_info,'查询成功',1);
            }else{
                $this->ajaxReturn($soft_info,'软件视频不存在',0);
            }

        }else{
            $this->ajaxReturn($soft_info,'该包名不存在',0);
        }
        
        

    }

    public function pub_get_video_lib(){
        
        $video_list = array();
        $pkg = $_POST['PACKAGE_NAME'];
        $video_model = M('soft_extra');
        $soft_info = $video_model->table('sj_soft')->field('softname')->where(array('package'=>$pkg,'status'=>1,'hide'=>1))->find();
        if($soft_info){
            $video_list['SOFTNAME'] = $soft_info['softname'];
            $video_info = $video_model->field('id,video_pic,video_url,video_title')->where(array('check_status'=>'1','status' => 1,'package'=>$pkg))->select();
            // echo $video_model->getLastSql();
            if($video_info){
                $video_title = array();
                foreach ($video_info as $key => $value) {
                    $video_title[$key]['video_pic'] = $value['video_pic'];
                    $video_title[$key]['video_url'] = $value['video_url'];
                    $video_title[$key]['video_title'] = $value['video_title'];
                }
                $video_list['CODE'] = '200';
                $video_list['DATA'] = $video_title;
                
                echo (json_encode($video_list));
                //var_dump($video_list);
            }else{
                $video_list['CODE'] = '2000';//软件视频不存在
                echo (json_encode($video_list));
            }

        }else{
            $video_list['CODE'] = '3000'; //软件不存在
            echo (json_encode($video_list));
        }
        die;
    }

}