<?php
/**
 * 安智网产品管理平台 (手机)系统管理之控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.5.20
 * ----------------------------------------------------------------------------
*/
class SystemmanageAction extends CommonAction {
    private $typeid;        //类型id
    private $uid;           //用户id
    private $map;           //存储条件
    private $users_db;      //用户表
    private $lists;         //常用列表
    private $notallowword;  //敏感词
    private $user_log_db;   //用户日志表
    private $user_log_list; //用户日志列表
    private $conf_db;        //配置表
    private $conf_list;      //配置列表
    private $templists;       //temp表中里列表
    private $pid;            //临时ID

	private $image_width_high = 684;
    private $image_height_high = 231;
	private $image_width_low = 444;
    private $image_height_low = 150;
	
	private $web_width = 300;
    private $web_height = 175;
	private $fresh_width = 480;
    private $fresh_height = 180;
	//专题改版 图片大小
	private $header_single_img_width = 480;
	private $header_single_img_height = 180;
	private $header_multiple_img_width = 220;
	private $header_multiple_img_height = 180;
	private $top_end_img_width = 460;
	//v6.3大家说图片
	private $everybody_say_pic_width = 480;
	private $everybody_say_pic_height = 180;
	
//------------------------------------网站分辨率管理--------------------------------------
    //手机系统管理_分辨率管理_显示
    public function resolution() {
        $this->conf_db = M('resolution');
        $this->map['status']='1';
        $this->conf_list=$this->conf_db->where($this->map)->select();
        $this->assign('conflist',$this->conf_list);
        $this->display();
    }
    //手机系统管理_分辨率管理_删除
    public function resolutiondel() {
        $this->pid=$_GET['id'];
        $this->conf_db = M('resolution');
        $this->map['resolutionid']=$this->pid;
        $this->conf_list['status']=0;
        if(false!==$this->conf_db->where($this->map)->save($this->conf_list))
        {
            $this->writelog('手机系统管理-分辨率显示，删除了id为'.$this->pid.'的分辨率','sj_resolution',$this->pid,__ACTION__ ,"","del");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/resolution');
            $this->success("删除分辨率项成功！");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/resolution');
     	   $this->error("删除分辨率项失败,发生错误！");
        }
    }
    //手机系统管理_分辨率管理_增加_显示
    public function resolutionadd() {
        $this->display();
    }
    //手机系统管理_分辨率管理_增加_执行
    public function resolution_add() {
        $this->conf_db = M('resolution');
        $this->conf_list['length']=$_POST['length'];
        $this->conf_list['width']=trim($_POST['width']);
        $this->conf_list['note']=trim($_POST['note']);
        $this->conf_list['status']=1;
        $this->conf_list['upload_time']=time();
        $this->conf_list['last_refresh']=time();
        if(empty($this->conf_list['length'])||empty($this->conf_list['width']) ||empty($this->conf_list['note'])) {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/resolution');
     	   $this->error("对不起，宽、高、及综合都不可为空！");
        }
		$res = $this->conf_db->add($this->conf_list);
        if(false!==$res) {
            $this->writelog('手机系统管理-分辨率显示，增加了综合为['.$this->conf_list['note'].']的分辨率','sj_resolution',$res,__ACTION__ ,"","add");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/resolution');
            $this->success("添加分辨率项成功！");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/resolution');
     	   $this->error("添加分辨率项失败,发生错误！");
        }
    }
    //手机系统管理_分辨率管理_编辑_显示
    public function resolutionedit() {
        $this->pid=$_GET['id'];
        $this->conf_db = M('resolution');
        $this->map['resolutionid']=$this->pid;
        $this->conf_list=$this->conf_db->where($this->map)->select();
        $this->assign('conflist',$this->conf_list[0]);
        $this->display();
    }
     //手机系统管理_分辨率管理_编辑_执行
    public function resolution_edit() {

        $this->pid=$_POST['resolutionid'];
        $this->conf_db = M('resolution');
        $this->map['resolutionid']=$this->pid;

        $this->conf_list['length']=$_POST['length'];
        $this->conf_list['width']=$_POST['width'];
        $this->conf_list['note']=$_POST['note'];
        $this->conf_list['last_refresh']=time();
        $log = $this->logcheck(array('resolutionid'=>$_POST['resolutionid']),'sj_resolution',$this->conf_list,$this->conf_db);
        $this->lists= $this->conf_db->where($this->map)->save($this->conf_list);
        if(false!==$this->lists) {
            $this->writelog('手机系统管理-分辨率管理_显示，编辑了id为'.$this->pid.'的分辨率'.$log,'sj_resolution',$this->pid,__ACTION__ ,"","edit");
            //$this->writelog('编辑了id为'.$this->pid.',的分辨率');
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/resolution');
            $this->success("修改分辨率项成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/resolution');
            $this->error('修改分辨率项失败！');
        }
    }
//------------------------------------网站软件分类管理--------------------------------------
   //手机系统管理_软件分类列表
    public function category() {

        $pid=$_GET['id'];
		$model = new Model();
        $conf_db = M('category');
		$soft_page_place = M('soft_page_place');
		$redis = new redis();
		$redis->connect(C('REDIS_HOST'),C('REDIS_PORT')); 
        $this->map['status']=1;
        $this->map['parentid']=0;
       /*  $conf_list=$conf_db->where($this->map)->field('category_id,name,orderid')->order('orderid')->select();
		//print_r($conf_list);
        $this->assign('conflist',$conf_list);

        if(empty($pid)) {
            $pid=$conf_list[0]['category_id'];
        }
        $lists=$conf_db->where('status=1 and parentid='.$pid)->order('orderid')->select();
		//print_r($lists);
		foreach($lists as $key=>$val){
			$lists[$key]['category_id']=$val['category_id'];
			//echo $val['category_id'];
			$list_three=$conf_db->where('status=1 and parentid='.$lists[$key]['category_id'])->order('orderid')->select();
			//print_r($list_three);
			foreach($list_three as $k=>$info){
				//echo $info['category_id'];
				$three[$lists[$key]['category_id']][$k]['category_id']=$info['category_id'];
				$three[$lists[$key]['category_id']][$k]['name']=$info['name'];
				$three[$lists[$key]['category_id']][$k]['parentid']=$info['parentid'];
				$three[$lists[$key]['category_id']][$k]['orderid']=$info['orderid'];
				$three[$lists[$key]['category_id']][$k]['typical']=$info['typical'];
			}
		}
		
		$this->assign('lists',$lists);
        $this->assign('three',$three); */
        $conf_list=$conf_db->where($this->map)->field('category_id,name,orderid')->order('orderid')->select();
		if(empty($pid)) {
            $pid=$conf_list[0]['category_id'];
        }
		foreach($conf_list as $m=>$n){
			
			$lists=$conf_db->where('status=1 and parentid='.$n['category_id'])->order('orderid')->select();
			$conf_list[$m][$n['category_id']]=$lists;
			 foreach($lists as $key=>$val){
				$lists[$key]['category_id']=$val['category_id'];
				$list_three=$conf_db->where('status !=0 and parentid='.$lists[$key]['category_id'])->order('orderid')->select();
				foreach($list_three as $k=>$info){
                                        $model = D('Sj.Tags');
                                        $tagsname = $model->getTagsname($info['tag_ids']);

					$three[$lists[$key]['category_id']][$k]['category_id']=$info['category_id'];
					$three[$lists[$key]['category_id']][$k]['name']=$info['name'];
					$zh_time=time();
					$where['status']=1;
					$where['start_tm']=array('elt',$zh_time);
					$where['stop_tm']=array('egt',$zh_time);
					$where['soft_type']="top_".$info['category_id']."_hot";
					$where['pos']=1;
					$soft_name=$soft_page_place->where($where)->getField("soft_name");
					if(empty($soft_name)){
						$soft=M("soft");
						$key_soft="SOFTLIST_CATEGORY_SOFTID_".$info['category_id']."_HOT";
						$soft_repalce=$redis->get($key_soft);
						$soft_array=json_decode($soft_repalce,true);
						$soft_array=array_slice($soft_array,0,1);
						$package=$soft_array[0];
						$where_soft['status']=1;
						$where_soft['package']=$package;
						$soft_name=$soft->where($where_soft)->getField("softname");
					}
						//echo $redis->get('SOFTLIST_CATEGORY_SOFTID_');
					$three[$lists[$key]['category_id']][$k]['soft_name']=$soft_name;
					$three[$lists[$key]['category_id']][$k]['parentid']=$info['parentid'];
					$point_name_result = $model -> table('sj_category') -> where(array('category_id' => $info['category_point'])) -> select();
					
					$three[$lists[$key]['category_id']][$k]['parent_name'] = $point_name_result[0]['name'];
					$three[$lists[$key]['category_id']][$k]['orderid']=$info['orderid'];
					$three[$lists[$key]['category_id']][$k]['status']=$info['status'];
					$three[$lists[$key]['category_id']][$k]['typical']=$info['typical'];
					$three[$lists[$key]['category_id']][$k]['tags_name']=$tagsname;
				}
			}
		}

        
        //suntao 根据标签ID 查 名字和荐
        $this->assign('conflist',$conf_list);
		$this->assign('three',$three);
        $this->assign('thepid',$pid);

        //dump($this->ad_list);

        $this->display();
    }
	
	
	public function add_category_show(){
		$model = new Model();
		$category_id = $_GET['category_id'];
		$thepid = $_GET['thepid'];
		$category_list_1 = $model -> table('sj_category') -> where(array('parentid' => 1,'status' => 1)) -> select();
		foreach($category_list_1 as $key => $val){
			$my_category_1 = $model -> table('sj_category') -> where(array('parentid' => $val['category_id'],'status' => 1)) -> select();
			if($my_category_1){
				$vs = array();
				foreach($my_category_1 as $k => $v){
					$vs[$k]['category_id'] = $v['category_id'];
					$vs[$k]['name'] = $v['name'];
				}
				$category_need_1[$key] = $vs;
			}
		}
		foreach($category_need_1 as $key => $val){
			foreach($val as $k => $v){
				$category_arr_1[] = $v;
			}
		}
		$category_list_2 = $model -> table('sj_category') -> where(array('parentid' => 2,'status' => 1)) -> select();
		foreach($category_list_2 as $key => $val){
			$my_category_2 = $model -> table('sj_category') -> where(array('parentid' => $val['category_id'],'status' => 1)) -> select();
			if($my_category_2){
				$vs = array();
				foreach($my_category_2 as $k => $v){
					$vs[$k]['category_id'] = $v['category_id'];
					$vs[$k]['name'] = $v['name'];
				}
				$category_need_2[$key] = $vs;
			}
		}
		foreach($category_need_2 as $key => $val){
			foreach($val as $k => $v){
				$category_arr_2[] = $v;
			}
		}
		$category_list_3 = $model -> table('sj_category') -> where(array('parentid' => 3,'status' => 1)) -> select();
		foreach($category_list_3 as $key => $val){
			$my_category_3 = $model -> table('sj_category') -> where(array('parentid' => $val['category_id'],'status' => 1)) -> select();
			if($my_category_3){
				$vs = array();
				foreach($my_category_3 as $k => $v){
					$vs[$k]['category_id'] = $v['category_id'];
					$vs[$k]['name'] = $v['name'];
				}
				$category_need_3[$key] = $vs;
			}
		}
		foreach($category_need_3 as $key => $val){
			foreach($val as $k => $v){
				$category_arr_3[] = $v;
			}
		}
	
		$parentid=$_GET['id'];
		$this -> assign('category_list_1',$category_arr_1);
		$this -> assign('category_list_2',$category_arr_2);
		$this -> assign('category_list_3',$category_arr_3);
		$this -> assign("category_id",$category_id);
		$this -> assign("thepid",$thepid);
		$this->assign("parentid",$parentid);
		$this -> display();
	}
	
    //手机系统管理_软件分类管理_增加_显示
    public function categoryadd() {

		$parentid=$_GET['id'];
        $this->conf_db = M('category');
        /* $this->map['status']=1;
        $this->map['parentid']=0;
        $this->conf_list=$this->conf_db->where($this->map)->field('category_id,name,orderid')->order('orderid')->select();
		$this->assign('conflist',$this->conf_list); */
		$this->assign("parentid",$parentid);
        $this->display();
    }
    //手机系统管理_软件分类管理_增加_执行
    public function category_add() {

        $this->conf_db = M('category');
        $this->conf_list['parentid']=$_POST['parentid'];
        $this->conf_list['orderid']=trim($_POST['orderid']);
        $this->conf_list['name']=trim($_POST['name']);
		$this->conf_list['status'] = trim($_POST['category_type']);
        if(empty($this->conf_list['name'])) {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$this->conf_list['parentid']);
     	   $this->error("添加软件分类项失败,名称不得为空！");
        }
        $this->conf_list['note']=trim($_POST['note']);
        $this->conf_list['status']=1;
        $this->conf_list['upload_time']=time();
        $this->conf_list['last_refresh']=time();
		$res = $this->conf_db->add($this->conf_list);
        if(false!== $res) {
            $this->writelog(' 软件类别配置：增加了名称为['.$this->conf_list['name'].']的软件分类','sj_category',$res,__ACTION__ ,"","add");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$this->conf_list['parentid']);
            $this->success("添加软件分类项成功！");
        }else{
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category');
     	   $this->error("添加软件分类项失败,发生错误！");
        }
    }

    //手机系统管理_软件分类管理_编辑_显示  suntao
    public function categoryedit() {
        $tagmodel = D('Sj.Tags');

        if($this->isAjax())
        {
            $tags = trim($_POST['tags']);

            $cid = $_POST['cid'];
            $rr = $tagmodel->table(' sj_category')->field('status,category_point')->where("category_id=$cid")->find();
            if($rr['status']==2)
            {
                $cid = $rr['category_point'];
            }

            if(strlen($tags)>0)
            {
                $tags_noj = str_replace('(荐)','',$tags);

                $tagarr2 = explode(',',$tags_noj);
                foreach($tagarr2 as $v)
                {
                    if(strlen($v)>12)
                    {
                        echo 3;
                        exit(0);
                    }
                }

                $tagarr2 = array_filter($tagarr2);
                $newarr2 = array_unique($tagarr2);

                $tagarr = explode(',',$tags);
                $tagarr = array_filter($tagarr);
                $newarr = array_unique($tagarr);

                if($newarr2 != $tagarr2)
                {
                    $this->ajaxReturn(2,'有重复的',1);
                }else{
                    //检查是否在分类下有软件
                    $rs = $tagmodel->is_has_soft($cid,$newarr);
                    $this->ajaxReturn($rs,'检查完毕',1);
                }
            }else{
                echo 1;exit(0);
            }
        }

        $this->pid=$_GET['id'];
		$parentid=$_GET['parentid'];
	
		$keys=$_GET['key'];
	
		$model = new Model();
        $this->conf_db = M('category');
        $this->map['category_id']=$this->pid;
	
        $this->conf_list=$this->conf_db->where($this->map)->select();
		$result = $model -> table('sj_soft_file') -> where(array('apk_name' => $this->conf_list[0]['apply_pkg'])) -> order('softid DESC') -> select();
		foreach($this -> conf_list as $vo => $val){
			$val['iconurl_72'] = $result[0]['iconurl_72'];
			$this -> conf_list[$vo] = $val;
		}
		$parent_result = $model -> table('sj_category') -> where(array('category_id' => $this -> conf_list[0]['category_point'])) -> select();
		if($this -> conf_list[0]['category_point'] < 4){
			$this -> conf_list[0]['category_point_first'] = $this -> conf_list[0]['category_point'];
		}else{
			$my_parent_result = $model -> table('sj_category') -> where(array('category_id' => $this -> conf_list[0]['category_point'])) -> select();
			$my_grand_result = $model -> table('sj_category') -> where(array('category_id' => $my_parent_result[0]['parentid'])) -> select();
			$this -> conf_list[0]['category_point_first'] = $my_grand_result[0]['parentid'];
		}
	
		$category_list_1 = $model -> table('sj_category') -> where(array('parentid' => 1,'status' => 1)) -> select();
		foreach($category_list_1 as $key => $val){
			$my_category_1 = $model -> table('sj_category') -> where(array('parentid' => $val['category_id'],'status' => 1)) -> select();
			if($my_category_1){
				$vs = array();
				foreach($my_category_1 as $k => $v){
					$vs[$k]['category_id'] = $v['category_id'];
					$vs[$k]['name'] = $v['name'];
				}
				$category_need_1[$key] = $vs;
			}
		}
		foreach($category_need_1 as $key => $val){
			foreach($val as $k => $v){
				$category_arr_1[] = $v;
			}
		}
		$category_list_2 = $model -> table('sj_category') -> where(array('parentid' => 2,'status' => 1)) -> select();
		foreach($category_list_2 as $key => $val){
			$my_category_2 = $model -> table('sj_category') -> where(array('parentid' => $val['category_id'],'status' => 1)) -> select();
			if($my_category_2){
				$vs = array();
				foreach($my_category_2 as $k => $v){
					$vs[$k]['category_id'] = $v['category_id'];
					$vs[$k]['name'] = $v['name'];
				}
				$category_need_2[$key] = $vs;
			}
		}
		foreach($category_need_2 as $key => $val){
			foreach($val as $k => $v){
				$category_arr_2[] = $v;
			}
		}
		$category_list_3 = $model -> table('sj_category') -> where(array('parentid' => 3,'status' => 1)) -> select();
		foreach($category_list_3 as $key => $val){
			$my_category_3 = $model -> table('sj_category') -> where(array('parentid' => $val['category_id'],'status' => 1)) -> select();
			if($my_category_3){
				$vs = array();
				foreach($my_category_3 as $k => $v){
					$vs[$k]['category_id'] = $v['category_id'];
					$vs[$k]['name'] = $v['name'];
				}
				$category_need_3[$key] = $vs;
			}
		}
		foreach($category_need_3 as $key => $val){
			foreach($val as $k => $v){
				$category_arr_3[] = $v;
			}
		}
		//$parentid=$_GET['id'];
		$this -> assign('category_list_1',$category_arr_1);
		$this -> assign('category_list_2',$category_arr_2);
		$this -> assign('category_list_3',$category_arr_3);
		
		
		//$this -> assign('parent_id',$parent_result[0]['parentid']);
        $tagname = $tagmodel->getTagsname($this->conf_list[0]['tag_ids']);
        $this->assign('conflist',$this->conf_list[0]);
		$this->assign('tagname',$tagname);
		$this->assign('parentid',$parentid);
		$this->assign('keys',$keys);
        $this->display();
    }
     //手机系统管理_软件分类管理_编辑_执行
    public function category_edit() {
        $this->pid=$_POST['categoryid'];
		$parentid=$_POST['parentid'];
		$model = new Model();
        $this->conf_db = M('category');
        $this->map['category_id']=$this->pid;
        $this->conf_list['tag_ids']=trim($_POST['newtid']);
        $this->conf_list['orderid']=trim($_POST['orderid']);
        $this->conf_list['name']=trim($_POST['name']);
		if(!$this->conf_list['name']){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
			$this -> error("请输入分类名称");
		}else{
			$have_where['_string'] = "name = '{$this->conf_list['name']}' and status != 0 and category_id != {$this->pid}";
			$have_result = $model -> table('sj_category') -> where($have_where) -> select();
		
			if($have_result){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
				$this -> error("分类名称已存在");
			}
		}
        
        $this->conf_list['last_refresh']=time();
		
		if($_POST['select_second_category'] == 1){
			$this->conf_list['category_point'] = trim($_POST['category_point_1']);
		}elseif($_POST['select_second_category'] == 2){
			$this->conf_list['category_point'] = trim($_POST['category_point_2']);
		}elseif($_POST['select_second_category'] == 3){
			$this->conf_list['category_point'] = trim($_POST['category_point_3']);
		}
		if(!(int)($this->conf_list['category_point'])){
			$this->conf_list['category_point'] = $_POST['select_second_category'];
		}
		
		$been_have = $this -> conf_db -> where(array('category_id' => $this -> map['category_id'])) -> select();
		if($been_have[0]['status'] == 2){
			$where_have = "category_point = {$this->conf_list['category_point']} and status != 0 and category_id != {$this->map['category_id']}";
			$have_been = $this->conf_db -> where($where_have) -> select();
		
			foreach($have_been as $key => $val){
				$check_result = $this->conf_db -> where(array('category_id' => $val['parentid'])) -> select();
			
				if($check_result[0]['parentid'] == $pid){
					$this -> error("该一级分类下已存在该分类指向");
				}
			}
		}
		$iconurl = $_FILES['iconurl'];
		$category_pic_h = $_FILES['category_pic_h'];
		$category_pic_m = $_FILES['category_pic_m'];
        $this->conf_list['apply_pkg'] = trim($_POST['apply_pkg']);
		
		if($parentid == 2){
			if(!$_POST['apply_pkg']){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
				$this -> error("应用包名不能为空");
			}
			
			$been_result = $model -> table('sj_soft') -> where(array('package' => $_POST['apply_pkg'],'hide' => 1,'status' => 1)) -> select();
			if(!$been_result){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
				$this -> error("应用包名不存在");
			}
		}
		if($_POST['typical'] != "注意;以分号分隔每个名称;最多输入10个名称"){
			$this->conf_list['typical']=trim($_POST['typical']);
			$typical[] = explode(";", $this->conf_list['typical']);
			$n = count($typical[0]);
			if ($n > 10){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
				$this->error('最多输入10个名称');
			}
			foreach ($typical[0] as $t){
				if (mb_strlen($t, 'utf-8') > 10){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
					$this->error('每个名称最多输入10个字符');
				}
			}
		}
		if(!empty($_FILES['iconurl']['size']) || !empty($_FILES['category_pic_h']['size']) || !empty($_FILES['category_pic_m']['size'])) {
			$path = date('Ym/d/', time());
            $config = array(
            	'multi_config' => array(
            		'iconurl' => array(
            			'savepath' => UPLOAD_PATH. '/icon/'. $path,
            			'saveRule' => 'getmsec',
						//'img_p_width'=> 40, //图片常规压缩宽度
						//'img_p_height'=> 40, //图片常规压缩高度 
            		),
					'category_pic_h' => array(
            			'savepath' => UPLOAD_PATH. '/icon/'. $path,
            			'saveRule' => 'getmsec',
						'img_p_width'=> 218, //图片常规压缩宽度
						'img_p_height'=> 142, //图片常规压缩高度
            		),
					'category_pic_m' => array(
            			'savepath' => UPLOAD_PATH. '/icon/'. $path,
            			'saveRule' => 'getmsec',
						'img_p_width'=> 145, //图片常规压缩宽度
						'img_p_height'=> 94, //图片常规压缩高度
            		),
            	),
				//'img_p_size' =>  1024*5,
            );
				$upload=$this->_uploadapk(0, $config);
				foreach($upload['image'] as $key => $val){
					if($val['post_name'] == 'iconurl'){
						$this->conf_list['big_iconurl']   =   $val['url_original'];
						//圆角图片会压缩成平角  舍弃
						//$this->conf_list['iconurl']   =   $val['url'];
						
						//图标压缩小图标
						$db_url = str_replace('.','_small.',$val['url_original']);
						$path_small = UPLOAD_PATH.$db_url;
						copy(UPLOAD_PATH.$val['url_original'],$path_small);
						go_resize_icon($path_small,10240,40,40);
						$this->conf_list['iconurl'] = $db_url;
					}
					if($val['post_name'] == 'category_pic_h'){
						$this->conf_list['category_pic_h']   =   $val['url'];
					}
					if($val['post_name'] == 'category_pic_m'){
						$this->conf_list['category_pic_m']   =   $val['url'];
					}
				}
			}

        $log = $this->logcheck(array('category_id'=>$this->pid),'sj_category',$this->conf_list,$this->conf_db);
        $this->lists= $this->conf_db->where($this->map)->save($this->conf_list);

        if(false!==$this->lists) {
            $this->writelog('软件类别配置:编辑了id为'.$this->pid.',的软件分类'.$log,'sj_category',$this->pid,__ACTION__ ,"","edit");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
            $this->success("修改软件分类项成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
            $this->error('修改软件分类项失败！');
        }
    }
    //手机系统管理_软件分类管理_删除
    public function categorydel() {
        $this->pid=$_GET['id'];
        $this->conf_db = M('category');
        $this->map['category_id']=$this->pid;
		$parentid = $_GET['parentid'];
		$this->map['_logic'] = 'or';
        $this->conf_list['status']=0;
        if(false!==$this->conf_db->where($this->map)->save($this->conf_list))
        {
            $this->writelog('软件类别配置：删除了id为'.$this->pid.'的软件分类','sj_category',$this->pid,__ACTION__ ,"","del");
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$_GET['parentid']);
            $this->success("删除软件分类项成功！");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category');
     	   $this->error("删除软件分类失败,发生错误！");
        }
    }

//------------------------------------网站专题类别管理--------------------------------------
    //手机系统管理_专题类别管理_显示
    public function feature( ) {
		if (trim($_GET['search_name'])){
			$map['name'] =trim($_GET['search_name']);
			$this->assign('name',$map['name']);
			$map['name'] =array('like','%'.$map['name'].'%');
		}
		if (trim($_GET['search_feature_type_name'])){
			$type['feature_type_name'] =trim($_GET['search_feature_type_name']);
		}
		if (trim($_GET['feature_type_name'])){
			$type['feature_type_name'] =trim($_GET['feature_type_name']);
		}
		if($type['feature_type_name']){
			$this->assign('feature_type_name',$type['feature_type_name']);
			$type['feature_type_name'] =array('like','%'.$type['feature_type_name'].'%');
			$datas=M('feature_type')->where($type)->field('id')->select();
			$ids="";
			foreach($datas as $data){
				$ids .=$data['id'].",";
			}
			$map['type']=array('in',rtrim($ids,','));
		}
		if (trim($_GET['name'])){
			$map['name'] =trim($_GET['name']);
			$this->assign('name',$map['name']);
			$map['name'] =array('like','%'.$map['name'].'%');
		}
		if(isset($_GET['order'])){
			$order=$_GET['order']?$_GET['order']:1;
		}
        if(isset($_GET['order_id'])){
            $order_id=$_GET['order_id']?$_GET['order_id']:1;
        }else{
             $order_id=1;
             $this->assign('order_id',$order_id);
        }
        $this->conf_db = M('feature');
		$feature_soft_db = M('feature_soft');
		$model = new Model();
        $util = D("Sj.Util");
        $pid = isset($_GET['pid']) ? $_GET['pid'] : '1';

        /*
        $map = array(
            'pid' => $pid,
        );
         */
        $map['pid'] = array('like','%,'.$pid.',%');
        $this->assign ("pid", $pid );
        $this->assign('product_list',$util->getProducts($pid));

        import("@.ORG.Page");
        $param = http_build_query($_GET);
		$limit = 20;
		if(isset($_GET['lr'])){
		    $this->assign("lr",(int)$_GET['lr']);
		}else{
		    $this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign("p",(int)$_GET['p']);
		}else{
		    $this->assign("p", 1);
		}
		
		$this->assign("status", isset($_GET['status']) ? (int)$_GET['status']: 1);
		
        $map['status'] = isset($_GET['status']) ? (int)$_GET['status']: 1;
		$count = $this->conf_db->where($map)->count();
		$page = new Page($count, $limit, $param);
		if($order==1){
            $this->conf_list = $this->conf_db->where($map)->order('feature_id asc')->limit($page->firstRow . ',' . $page->listRows)->select();
        }else if($order==2){
            $this->conf_list = $this->conf_db->where($map)->order('feature_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        }else if($order_id==2){
            $this->conf_list = $this->conf_db->where($map)->order('orderid desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        }else if($order_id==1){
			
            $this->conf_list = $this->conf_db->where($map)->order('orderid asc,last_refresh desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		    $this->assign('order_id',2); 
        }
		foreach($this -> conf_list as $key => $val)
		{
			$my_channel = explode(',',$val['channel_id']);
			$channel_str = '';
			foreach($my_channel as $k => $v){
				if($v){
					$chname_result = $model -> table('sj_channel') -> where(array('cid' => $v)) -> find();
					if($chname_result){
						$channel_str .= $chname_result['chname'].',';
					}
				}
			}
			$chname_str = substr($channel_str,0,-1);
			if(strlen($chname_str) > 50){
				$my_chname = substr($chname_str,0,50);
				$val['chname_str'] = $my_chname.'...';
				$val['chname_type'] = 2;
			}elseif(strlen($chname_str) < 50 && strlen($chname_str) > 0){
				$val['chname_str'] = $chname_str;
				$val['chname_type'] = 1;
			}else{
				$val['chname_str'] = '——';
			}
			if($val['orderid'] == 999999){
				$val['orderid'] = '';
			}
			$val['n'] = $feature_soft_db->where("feature_id={$val['feature_id']} AND `status`=1 AND start_tm <= ".time()." AND end_tm >= ".time())->count();
			$model=M(feature_type);
            $feature_type_list = $model->table('sj_feature_type')->where("status = 1 AND id ={$val['type']}")->select();
 		    $val['type_real']=$feature_type_list[0]['feature_type_name'];
			//页面类型
			if($val['feature_page_type']==1)
			{
				$val['feature_page_type_show']="H5";
				//预览功能
				if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
				{
					$val['pre_url'] = 'http://118.26.203.23/lottery/feature_new.php?id='.$val['feature_id'];
				}
				elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
				{
					$val['pre_url'] = 'http://9.m.anzhi.com/lottery/feature_new.php?id='.$val['feature_id'];
				}
				else 
				{
					$val['pre_url'] = 'http://fx.anzhi.com/lottery/feature_new.php?id='.$val['feature_id'];
				}
			}
			else
			{
				$val['feature_page_type_show']="原生";
			}
			$this -> conf_list[$key] = $val;
		}
		if($order==1){
			$this->assign('order',2);
		}else{
			$this->assign('order',1);
		}
        if($order_id==2){
            $this->assign('order_id',1);
        }
		$this->assign('count',$count);
		$this->assign('zero',0);
		$this->assign('conflist',$this->conf_list);
		$page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign("page", $page->show());
        $html = $this->fetch();
		header("Cache-control: no-store");
		header("pragma:no-cache");
		exit($html);
    }
    
    // 专题分类列表
    public function feature_type_list() {
        $model = M();
        $where =array(
            'status' => 1
        );
        $list = $model->table('sj_feature_type')->where($where)->select();
        
        // 查找该专题分类下有多少个专题
        foreach ($list as $key => $record) {
            $where = array(
                'type' => $record['id'],
                'status' => 1,
            );
            $count = $model->table('sj_feature')->where($where)->count();
            $list[$key]['feature_count'] = $count;
        }
        
        $this->assign('feature_type_list', $list);
        $this->display();
    }
    
    public function add_feature_type() {
        if ($_POST) {
            $model = M();
            $data = array();
            $feature_type_name = trim($_POST['feature_type_name']);
            if (!$feature_type_name) {
                $this->error("专题分类名称不能为空");
            }
            if (mb_strlen($feature_type_name, 'utf-8') > 10) {
                $this->error("专题分类名称不能超出10个字");
            }
            // 检查冲突
            $conflict_where = array(
                'status' => 1,
                'feature_type_name' => $feature_type_name,
            );
            $conflict_find = $model->table('sj_feature_type')->where($conflict_where)->find();
            if ($conflict_find) {
                $this->error("专题分类名称与id为{$conflict_find['id']}的记录重复！");
            }
            $data['feature_type_name'] = $feature_type_name;
            $data['create_time'] = $data['update_time'] = time();
            // 添加
            $ret = $model->table('sj_feature_type')->add($data);
            if ($ret) {
                $this->writelog("市场专题管理_专题配置_管理专题分类：新增了id为{$ret}的专题分类",'sj_feature_type',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        } else {
            $this->display();
        }
    }
    
    public function edit_feature_type() {
        if ($_POST) {
            $model = M();
            $id = $_POST['id'];
            $data = array();
            $feature_type_name = trim($_POST['feature_type_name']);
			$soft_count = trim($_POST['soft_count']);
			
            if (!$feature_type_name) {
                $this->error("专题分类名称不能为空");
            }
            if (mb_strlen($feature_type_name, 'utf-8') > 10) {
                $this->error("专题分类名称不能超出10个字");
            }
            // 检查冲突
            $conflict_where = array(
                'status' => 1,
                'feature_type_name' => $feature_type_name,
                'id' => array('neq', $id),
            );
            $conflict_find = $model->table('sj_feature_type')->where($conflict_where)->find();
            if ($conflict_find) {
                $this->error("专题分类名称与id为{$conflict_find['id']}的记录重复！");
            }
			//V6.0安智汉化 增加显示软件数 
			if($feature_type_name=="安智汉化")
			{
				if($soft_count!="")
				{
					if (!preg_match('/^\d+$/', $soft_count)) 
					{
						$this->error("安智汉化软件数必须是大于等于3的正整数");
					}
					elseif($soft_count<3)
					{
						$this->error("安智汉化软件数必须是大于等于3的正整数");
					}
					else
					{
						$data['soft_count']=$soft_count;
					}
				}
				else
				{
					$this->error("安智汉化软件数必填");
				}
			}
			
            $data['feature_type_name'] = $feature_type_name;
            $data['update_time'] = time();
            // 添加
            $where = array(
                'id' => $id,
            );
            $log = $this->logcheck($where, 'sj_feature_type', $data, $model);
            $ret = $model->table('sj_feature_type')->where($where)->save($data);
            if ($ret) {
                $this->writelog("市场专题管理_专题配置_管理专题分类：编辑了id为{$id}的专题分类，{$log}",'sj_feature_type',$id,__ACTION__ ,"","edit");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else {
            $model = M();
            $id = $_GET['id'];
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $find = $model->table('sj_feature_type')->where($where)->find();
			if($find['soft_count']==0)
			{
				$soft_count="";
			}
			else
			{
				$soft_count=$find['soft_count'];
			}
			$this->assign('soft_count',$soft_count);
            $this->assign('find', $find);
            $this->display();
        }
    }
    
    public function delete_feature_type() {
        $id = $_GET['id'];
        if ($id == 1 || $id == 2 || $id == 3) {
            $this->ajaxReturn(0, "不能删除此专题！", -1);
        }
        $model = M();
        // 查找该专题分类下还有没有专题，有的话不允许删除
        $where = array(
            'type' => $id,
            'status' => 1,
        );
        $count = $model->table('sj_feature')->where($where)->count();
        if ($count) {
            $this->ajaxReturn(0, "该专题分类下仍有专题，请先将专题移走后再删除", 1);
        }
        // 可以删除专题
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $data = array(
            'update_time' => time(),
            'status' => 0,
        );
        $ret = $model->table('sj_feature_type')->where($where)->save($data);
        if ($ret) {
            $this->writelog("市场专题管理_专题配置_管理专题分类：删除了id为{$id}的专题分类",'sj_feature_type',$id,__ACTION__ ,"","del");
            $this->ajaxReturn(0, "删除成功！", 0);
        } else {
            $this->ajaxReturn(0, "删除失败！", -1);
        }
    }
    
    // 一个专题分类下的专题全部移动至另一个专题分类
    public function move_features() {
        $from_feature_type_id = $_GET['from_feature_type_id'];
        $to_feature_type_id = $_GET['to_feature_type_id'];
        // 将sj_feature里type为$from_feature_type_id的更改为$to_feature_type_id
        $model = M();
        $where = array(
            'type' => $from_feature_type_id,
            'status' => 1,
        );
        $data = array(
            'last_refresh' => time(),
            'type' => $to_feature_type_id,
        );
        $ret = $model->table('sj_feature')->where($where)->save($data);
        if ($ret) {
            $this->writelog("市场专题管理_专题配置_管理专题分类：id为{$from_feature_type_id}的专题分类下的专题全部移动到id为{$to_feature_type_id}的专题分类下",'sj_feature',$from_feature_type_id,__ACTION__ ,"","edit");
            $this->ajaxReturn(0, "移动成功！", 0);
        } else {
            $this->ajaxReturn(0, "移动失败！", -1);
        }
    }
	
	//手机系统管理_专题类别管理_更新专题类别排序
    public function feature_update_rank() {
		$id        = (int)$_GET['feature_id'];
		$orderid   = (int)$_GET['orderid'];
		$lr        = isset($_GET['lr']) ? (int)$_GET['lr'] : 20;
		$p         = isset($_GET['p'])  ? (int)$_GET['p']  : 1;
		$pid	   = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
		if($pid < 1){
			$this -> error('非法操作,没有获取产品');
		}
		$where     = ' `status` = 1 and pid = '.$pid;
		$param     = $this->_updateRankInfo('sj_feature','orderid',$id,$where,$orderid,$lr,$p);
        $this -> writelog('编辑了feature_id为'.$id.',的rank排序',"sj_feature",$id,__ACTION__,"feature_config",'edit');
		exit(json_encode($param));
    }
	
	//手机系统管理_专题类别管理_批量更新排序
    public function feature_batch_rank() {
        if(isset($_GET)){
		    $model = M('feature');
			$ids   = (string)$_GET['id'];
			$ranks = (string)$_GET['orderid'];
			$ids   = substr($ids,0,strlen($ids)-1);
			$ranks = substr($ranks,0,strlen($ranks)-1);
			
			$extent_list = array();
			$allids   = explode(",",$ids);
			$allranks = explode(",", $ranks);
			
			$extent_list = array_combine($allids,$allranks);
			foreach($extent_list as $id => $rank){
				$model -> query("UPDATE __TABLE__ set orderid = ".$rank." WHERE status = 1 AND feature_id = " .$id);
			}
			
			$this->writelog('批量更新了feature_id为'.$ids.'的排序',__TABLE__,$ids,__ACTION__,"",'edit');
			$this->assign('jumpUrl','/index.php/Sj/Systemmanage/feature');
			$this->success('批量更新成功');
		}
    }
	
    //手机系统管理_专题类别管理_增加_显示
    public function featureadd() {
        $util = D("Sj.Util");
        $pid = isset($_GET['pid']) ? $_GET['pid'] : '1';
        $map = array(
            'pid' => $pid,
        );
        $this->assign ("pid", $pid );
        $this->assign('product_list',$util->getProducts($pid));

	    $this->conf_db = M('feature');
		$count = count($this->conf_db->where('status = 1')->select()) + 1;
		$this->assign('count',$count);
	   
        $channel_model = M('channel');
        $channel_list = $channel_model->field("cid,chname")->where(array('status' => 1))->select();
        $this->assign('channel_list', $channel_list);
		
        $operator_list = $channel_model->table('pu_operating')->field("oid,mname")->select();
        $this->assign('operator_list', $operator_list);
        
        $feature_type_list = $channel_model->table('sj_feature_type')->where(array('status' => 1))->select();
        $this->assign('feature_type_list', $feature_type_list);
		
		//注释新增作者
		/*$author_list = $channel_model->table('sj_author')->where(array('status' => 1))->select();
        $this->assign('author_list', $author_list);*/
		
		//各个图片大小
		$this->assign('image_width_high', $this->image_width_high);
		$this->assign('image_height_high', $this->image_height_high);
		$this->assign('image_width_low', $this->image_width_low);
		$this->assign('image_height_low', $this->image_height_low);
		
		$this->assign('web_width', $this->web_width);
		$this->assign('web_height', $this->web_height);
		$this->assign('fresh_width', $this->fresh_width);
		$this->assign('fresh_height', $this->fresh_height);
		
		//专题改版
		$this->assign('header_single_img_width', $this->header_single_img_width);
		$this->assign('header_multiple_img_width', $this->header_multiple_img_width);
		$this->assign('top_end_img_width', $this->top_end_img_width);
		
		//V6.3大家说图片
		$this->assign('everybody_say_pic_width', $this->everybody_say_pic_width);
		$this->assign('everybody_say_pic_height', $this->everybody_say_pic_height);
		
		$this->getAbiList();
        $this->display();
    }
	public function getAbiList($match_abi = 0)
	{
		$abilist = array(
			'1' => array('ABI_ARMEABI (普通机型)', $match_abi & 1),
			'2' => array('ABI_ARMEABI_V7A (普通机型)',$match_abi & 2),
			'4' => array('ABI_X86 (pc)',$match_abi & 4),
			'8' => array('ABI_MIPS (mips机型，例如君正)',$match_abi & 8),
		);
		
		$this->assign('abilist', $abilist);
	}
    //手机系统管理_专题类别管理_增加_执行
    public function feature_add() {
		$model = new Model();
		$time = time();
		$abilist = array(
			'1' => array('ABI_ARMEABI (普通机型)', $match_abi & 1),
			'2' => array('ABI_ARMEABI_V7A (普通机型)',$match_abi & 2),
			'4' => array('ABI_X86 (pc)',$match_abi & 4),
			'8' => array('ABI_MIPS (mips机型，例如君正)',$match_abi & 8),
		);
		$this->conf_list['match_abi'] = 0;

		if (!empty($_POST['match_abi'])) {
			foreach ($_POST['match_abi'] as $v) {
				$zh_abi_name .=$abilist[$v][0]."|";
				$this->conf_list['match_abi'] = ($this->conf_list['match_abi'] | $v);
			}
		}else{
			$zh_abi_name="全部过滤";
		}
		if($_POST['name']==''){
			$this->error('专题类别名称不能为空');
		}else if(mb_strlen($_POST['name'],'utf8')>15){
			$this->error('专题类别名称不能大于15个字符');
		}else if(mb_strlen($_POST['title'],'utf8')>15){
			$this->error('专题类别标题不能大于15');
		}else if($_POST['remark']==''|| $_POST['remark']=='请输入专题详情简介'){
			$this->error('专题详情简介不能为空');
		}
                if($_POST['pid']==''){
			$this->error('请勾选产品类型');
		}
		//v6.3上线时间
		$this->conf_list['public_time'] = strtotime($_POST['public_time']);
		if (!$this->conf_list['public_time']) {
			$this->error("上线时间不能为空");
		}
		//专题改版
		//0表示原生页面  1表示H5页面
		$feature_page_type = $_POST['feature_page_type'];
		if($feature_page_type==1)
		{
			foreach($_POST['main_key'] as $key =>$val)
			{
				if(empty($_POST['paragraph_detail'][$val]))
				{
					$this->error('请填写段落详情');
				}	
			}
			$this->conf_list['feature_page_bg'] = trim($_POST['feature_bg_color']);
			$this->conf_list['feature_comment_share_label_color'] = trim($_POST['feature_comment_share_label_color']);
			$this->conf_list['feature_comment_share_text_color'] = trim($_POST['feature_comment_share_text_color']);
		}
		else
		{
			foreach($_POST['editor_content'] as $k => $v){
				if(empty($v)) $this->error('请填写图文');
			}	
		}	
		if($_POST['note']=='' || $_POST['note'] =="请填写专题类别备注"){
			$note == "";
		}else{
			$note = $_POST['note'];
		}

		if (!empty($_POST['description'])) {
			$this->conf_list['description'] = $_POST['description'];
		}

		if($_POST['pid'] == 4){
			$webicon_width = 525;
			$webicon_height = 225;
			$webicon_size = 30;
		}else{
			$webicon_width = 170;
			$webicon_height = 100;
			$webicon_size = 10;
		}
		$this->initval_check();
			if(empty($_FILES['webicon']['size']) || empty($_FILES['fresh_hoticon']['size'])) {
				$this->error("请选择图片！！！");
				//if($_POST['pid'] == 5){
				if(in_array('5',$_POST['pid'])){
					if(empty($_FILES['ordinary_icon_olgame']['size'])){
						$this->error("请选择图片！！！");
					}
				}
				
			}else{
				if(!empty($_FILES['fresh_hoticon']['size'])){
					$pic_tmp = getimagesize($_FILES['fresh_hoticon']['tmp_name']);					
					if($pic_tmp[0] != $this->fresh_width || $pic_tmp[1] != $this->fresh_height){
						$this -> error("请上传宽度为{$this->fresh_width},高度为{$this->fresh_height}的图片");
					}
				}
				if(!empty($_FILES['webicon']['size'])){
					$pic_webicon = getimagesize($_FILES['webicon']['tmp_name']);					
					if($pic_webicon[0] != $this->web_width || $pic_webicon[1] != $this->web_height){
						$this -> error("请上传宽度为{$this->web_width},高度为{$this->web_height}的图片");
					}
				}
				$image_604_204 = getimagesize($_FILES['icon_604_204']['tmp_name']);
				if($image_604_204[0] != $this->image_width_high || $image_604_204[1] != $this->image_height_high){
					$this -> error("专题图片(新版高分):请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}的图片");
				}
				$image_444_150 = getimagesize($_FILES['icon_444_150']['tmp_name']);
				if($image_444_150[0] != $this->image_width_low || $image_444_150[1] != $this->image_height_low){
					$this -> error("专题图片(新版低分):请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
				}
				$everybody_say_pic = getimagesize($_FILES['everybody_say_pic']['tmp_name']);
				if($everybody_say_pic[0] != $this->everybody_say_pic_width || $everybody_say_pic[1] != $this->everybody_say_pic_height){
					$this -> error("专题图片(大家说):请上传宽度为{$this->everybody_say_pic_width},高度为{$this->everybody_say_pic_height}的图片");
				}
				
				include_once SERVER_ROOT. '/tools/functions.php';
				//上传图片 尝鲜
				$folder = "/img/" . date("Ym/d/");
				$this->mkDirs(UPLOAD_PATH . $folder);
				
				$fresh_hoticon_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['fresh_hoticon']['name'],$match_fresh_hoticon_img);
				$fresh_hoticon_img = $match_fresh_hoticon_img[0];
				$fresh_hoticon_img_path = $folder . time() . '_fresh_hoticon_img_'. rand(1000,9999) . $fresh_hoticon_img;
				$fresh_hoticon_img_path_last = UPLOAD_PATH . $fresh_hoticon_img_path;
				$ret = move_uploaded_file($_FILES['fresh_hoticon']['tmp_name'], $fresh_hoticon_img_path_last);
				$this->conf_list['fresh_hoticon']=$fresh_hoticon_img_path;
				//网页
				$webicon_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['webicon']['name'],$match_webicon_img);
				$webicon_img = $match_webicon_img[0];
				$webicon_img_path = $folder . time() . '_webicon_img_'. rand(1000,9999) . $webicon_img;
				$webicon_img_path_last = UPLOAD_PATH . $webicon_img_path;
				//$ret = move_uploaded_file($_FILES['webicon']['tmp_name'], $webicon_img_path_last);
				image_strip_size($_FILES['webicon']['tmp_name'],$webicon_img_path_last,15*1024);
				$this->conf_list['webicon']=$webicon_img_path;
			
				//高分
				$feature_high_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['icon_604_204']['name'],$match_feature_high_img);
				$feature_high_img = $match_feature_high_img[0];
				$feature_high_img_path = $folder . time() . '_feature_high_img_'. rand(1000,9999) . $feature_high_img;
				$feature_high_img_path_80 = $folder . time() . '_feature_high_img_80_'. rand(1000,9999) . $feature_high_img;
				$feature_high_img_path_last = UPLOAD_PATH . $feature_high_img_path;
				$feature_high_img_path_last_80 = UPLOAD_PATH . $feature_high_img_path_80;
				$ret = move_uploaded_file($_FILES['icon_604_204']['tmp_name'], $feature_high_img_path_last);
				image_strip_size($feature_high_img_path_last,$feature_high_img_path_last_80,80*1024);
				$this->conf_list['high_image_url']= $feature_high_img_path;
				$this->conf_list['high_image_url_80']= $feature_high_img_path_80;
				
				//低分
				$feature_low_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['icon_444_150']['name'],$match_feature_low_img);
				$feature_low_img = $match_feature_low_img[0];
				$feature_low_img_path = $folder . time() . '_feature_low_img_'. rand(1000,9999) . $feature_low_img;
				$feature_low_img_path_40 = $folder . time() . '_feature_low_img_40_'. rand(1000,9999) . $feature_low_img;
				$feature_low_img_path_last = UPLOAD_PATH . $feature_low_img_path;
				$feature_low_img_path_last_40 = UPLOAD_PATH . $feature_low_img_path_40;
				$ret = move_uploaded_file($_FILES['icon_444_150']['tmp_name'], $feature_low_img_path_last);
				image_strip_size($feature_low_img_path_last,$feature_low_img_path_last_40,40*1024);
				$this->conf_list['low_image_url']= $feature_low_img_path;
				$this->conf_list['low_image_url_40']= $feature_low_img_path_40;
				
				//大家说图片
				$everybody_say_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['everybody_say_pic']['name'],$match_everybody_say_img);
				$everybody_say_img = $match_everybody_say_img[0];
				$everybody_say_img_path = $folder . time() . '_everybody_say_img_'. rand(1000,9999) . $everybody_say_img;
				$everybody_say_img_path_last = UPLOAD_PATH . $everybody_say_img_path;
				$ret = move_uploaded_file($_FILES['everybody_say_pic']['tmp_name'], $everybody_say_img_path_last);
				$this->conf_list['everybody_say_pic']= $everybody_say_img_path;
				
				
				/*$path = date('Ym/d/', time());
				$config = array(
							'multi_config' => array(
								'fresh_hoticon' => array(
									'savepath' => UPLOAD_PATH. '/img/'. $path,
									'saveRule' => 'getmsec',
								),
								'webicon' => array(
									'savepath' => UPLOAD_PATH. '/img/'. $path,
									'saveRule' => 'getmsec',
									'img_p_size' =>  1024*15,
									'img_p_width' =>  $this->web_width,
									'img_p_height' => $this->web_height,
								),
								'icon_604_204' => array(
									'savepath' => UPLOAD_PATH. '/img/'. $path,
									'saveRule' => 'getmsec',
									'img_p_size' =>  1024*80,
								),
								'icon_444_150' => array(
									'savepath' => UPLOAD_PATH. '/img/'. $path,
									'saveRule' => 'getmsec',
									'img_p_size' =>  1024*40,
								),
							),
					);
				$lists=$this->_uploadapk(0, $config);
				foreach($lists['image'] as $val) {
					if ($val['post_name'] == 'fresh_hoticon') {
						$this->conf_list['fresh_hoticon']= $val['url'];
					}
					if ($val['post_name'] == 'webicon') {
						$this->conf_list['webicon']= $val['url'];
					}
					if ($val['post_name'] == 'icon_604_204') {
						$this->conf_list['high_image_url']= $val['url_original'];
						$this->conf_list['high_image_url_80']= $val['url'];
					}
					if ($val['post_name'] == 'icon_444_150') {
						$this->conf_list['low_image_url']= $val['url_original'];
						$this->conf_list['low_image_url_40']= $val['url'];
					}
				}*/
			}
		//专题改版新增加字段
		$this->conf_list['feature_page_type']=trim($feature_page_type);
		
        $this->conf_list['name']=trim($_POST['name']);
        $this->conf_list['note']=trim($note);
        $this->conf_list['status']=1;
        $this->conf_list['upload_time']=time();
        $this->conf_list['last_refresh']=time();
		if($_POST['orderid']){
			$this->conf_list['orderid']=$_POST['orderid'];
		}else{
			$this->conf_list['orderid']=999999;
		}
        $this->conf_list['channel_id'] = '';
		//$this->conf_list['pid'] = $_POST['pid'];
        $pidstr = ','.implode(',',$_POST['pid']).',';
		$this->conf_list['pid'] = $pidstr;
		$this->conf_list['type'] = $_POST['type'];
		$this->conf_list['oid'] = ','. $_POST['oid']. ',';
		if(empty($_POST['oid'])||$_POST['oid']<=0){
			$zh_oid_name="全部可见";
		}else{
			$zh_oid_name=$model->table("pu_operating")->where(array("oid"=>$_POST['oid']))->getfield("mname");
		}
		$this->conf_list['remark']=trim($_POST['remark']);
        if ($_POST['cid']) {
			if($_POST['cid'][0] != 0 && count($_POST['cid']) >= 1){
				$cids = array();
				foreach ($_POST['cid'] as $cid) {
					if ($cid >= 0)
						$cids[] = $cid;
				}
				$cids = array_unique($cids);
				if (count($cids) > 0) {
					$s = implode(',', $cids);
					$s = ",{$s},";
					$this->conf_list['channel_id'] = $s;
				}
				foreach($cids as $k=>$val){
					if($val!=0){
						$c_where['status']=1;
						$c_where['cid']=$val;
						$ch_chname=$model->table('sj_channel')->where($c_where)->getfield("chname");
						$zh_chname .=$ch_chname."|";
					}
				
				}
			}else{
				$this->conf_list['channel_id'] = '';
			}
        }else{
			$this -> conf_list['channel_id'] = '';
			$zh_chname="全部可见";
		} 

        if(empty($this->conf_list['name'])) {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/feature');
     	   $this->error("对不起，新增专题类别不可为空！");
        }
		$id = $model->table('sj_feature')->add($this->conf_list);
        $last_id = mysql_insert_id();
        
		$data['status'] = 1;
		$data['feature_id'] = $last_id;
		$data['title'] = trim($_POST['title']);
		if($_POST['text']=='请输入WEB上的专题详情简介'){
			$data['text'] = "";
		}else{
			$data['text'] = trim($_POST['text']);
		}
		
		$ins = $model->table('sj_webmarket_feature_text')->add($data);
        if($id) {
			if($feature_page_type==1)
			{
				foreach($_POST['main_key'] as $key =>$val)
				{
					$res = $this -> pub_add_feature_graphic_new($_POST,$_FILES,$val,$id);
					$res['feature_id']=$id;
					$res['add_tm'] = $time;
					$res['update_tm'] = $time;
					$res['rank']= $key+1;
					$model -> table('sj_feature_graphic')->add($res);
				}	
				//增加分享的跳转
				$share_where=array(
                    'content_type' => 3,
                    'feature_id' => $id,
                    'page_flag' => '0x00150000',
                    'status'=>1,
				);
				$is_have_share = $model->table("sj_common_jump")->where($share_where)->find();
				if(!$is_have_share)
				{
					$new_data = array(
                        'content_type' => 3,
                        'feature_id' => $id,
                        'page_flag' => '0x00150000',
						'create_at' => time(),
						'update_at' => time(),
						'status' => 1,
					);
					$share_ret =  $model->table('sj_common_jump')->add($new_data);
				}
			}
			else
			{
				foreach($_POST['editor_content'] as $k => $v){
					$res = $this -> pub_add_feature_graphic($v,$id);
					if($res['code'] == 1){
						$data = array(
							'feature_id' => $id,
							'content' => $res['msg'],
							'add_tm' => $time,
							'update_tm' => $time,
							'rank' => $k+1,
						);
						$model -> table('sj_feature_graphic')->add($data);
					}else{
						$this->error($res['msg']);
					}
				}			
			}
			
			if ($this->conf_list['is_hot'] == 1) {
				$sql = "update sj_feature set is_hot=0 where feature_id <> {$id} and channel_id <> {$this->conf_list['channel_id']}";
				$model->query($sql);
			}
			$msg="专题配置：增加了名称ID为[{$id}]标题为[{$this->conf_list['name']}] \n";
			$msg .="可见渠道为[{$zh_chname}] \n";
			$msg .="运营商为[{$zh_oid_name}] \n";
			$msg .="cpu过滤机型为[{$zh_abi_name}]";
			$msg .="的专题类别";
            $this->writelog($msg, 'sj_feature',$id,__ACTION__,'','add');
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Systemmanage/feature");
            $this->success("添加专题类别项成功！");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Systemmanage/feature");
     	   $this->error("添加专题类别项失败,发生错误！");
        }
    }
    //手机系统管理_专题类别管理_编辑_显示
    public function featureedit() {			
        $this->pid=$_GET['id'];
        $this->conf_db = M('feature');
        $this->map['feature_id']=$this->pid;
        $this->conf_list=$this->conf_db->where($this->map)->select();
		$fea_id = $_GET['id'];
		$model = M('webmarket_feature_text');
		$text_list = $model->where("feature_id = $fea_id")->find();
        
		$this->conf_list[0]['title'] = $text_list['title'];
		$this->conf_list[0]['text'] = $text_list['text'];
		$this->assign('conflist',$this->conf_list[0]);
		$count = count($this->conf_db->where('status = 1')->select());
		$this->assign('count',$count);
        $channel_model = M('channel');
		
        $operator_list = $channel_model->table('pu_operating')->field("oid,mname")->select();
        $this->assign('operator_list', $operator_list);
		
        if (strlen($this->conf_list[0]['oid']) > 0) {
            $operator_selected = explode(',', $this->conf_list[0]['oid']);
            $operator_selected_ret = array();
            foreach ($operator_selected as $cs) {
				if (empty($cs)) continue;       	
                $operator_selected_ret[] = $cs;
            }
            $this->assign('operator_selected', $operator_selected_ret);
        }
		
        if (strlen($this->conf_list[0]['channel_id']) > 0) {
			$cookstr = substr($this->conf_list[0]['channel_id'], 1, -1);
			$chl = $channel_model->field("`cid`,`chname`")->where(' `cid` in (' . $cookstr . ')')->select();
            $this->assign('channel_selected', $chl);
        }
        if($this->conf_list[0]['text']==""){
        	$check =1;
        }else{
        	$check =2;
        }
        $this->assign('check',$check);
		$util = D("Sj.Util");
        $pidarr = array_filter(explode(',',$this->conf_list[0]['pid']));
		$this->assign('product_list',$util->getProducts($pidarr));
		$this->getAbiList($this->conf_list[0]['match_abi']);
        $feature_type_list = $channel_model->table('sj_feature_type')->where(array('status' => 1))->select();
        $this->assign('feature_type_list', $feature_type_list);
		$where = array('feature_id' => $this->conf_list[0]['feature_id'],'status'=>1);
		$feature = $this->conf_db->table('sj_feature_graphic')->where($where)->order('rank asc')->select();
		$where = array(	'from_id' => $this->conf_list[0]['feature_id']	);
		$graphic_image  = get_table_data($where,"sj_graphic_image","id","*");	
        $this->assign('graphic_image', $graphic_image);		
		$section = array('','一','二','三','四','五','六','七','八','九','十');
		$pattern = "#\[img\]([\d]+)\[/img\]#" ;
		foreach($feature as $k => $v){
			$feature[$k]['title'] = "图文第".$section[$v['rank']]."段";
			preg_match_all($pattern, $v['content'],$matches);
			$new_k = array();
			foreach($matches[1] as $vv){
				$new_k['[img]'.$vv.'[/img]'] = "<img src='". IMGATT_HOST . $graphic_image[$vv]['imgurl']."'/>";
			}
			$content = strtr($v['content'],$new_k);
			$feature[$k]['content'] =  $content;
			$header_content_arr = json_decode($v['header_content'],true);
			//段落头图内容展示
			if($header_content_arr)
			{
				$header_count=count($header_content_arr)/2;
				$new_header_arr=array();
				for($i=1;$i<=$header_count;$i++)
				{
					$new_header_arr[$i]['img']=$header_content_arr['img'.$i];
					$new_header_arr[$i]['common_jump_id']=$header_content_arr['common_jump_id'.$i];
					$common_where=array(
						'id'=>$header_content_arr['common_jump_id'.$i],
						'status'=>1,
					);
					$common_detail=$model->table('sj_common_jump')->where($common_where)->find();
					$new_header_arr[$i]['common_jump_detail']=$common_detail;
				}
				if($v['header_type']==1)
				{
					$feature[$k]['header_content_arr1'] =  $new_header_arr;
				}
				elseif($v['header_type']==2)
				{
					$feature[$k]['header_content_arr2'] =  $new_header_arr;
				}
				elseif($v['header_type']==3)
				{
					$feature[$k]['header_content_arr3'] =  $new_header_arr;
				}
				elseif($v['header_type']==4)
				{
					$feature[$k]['header_content_arr4'] =  $new_header_arr;
				}
			}
			//段落推荐图片展示
			$recommend_content_arr = json_decode($v['recommend_content'],true);
			if($recommend_content_arr)
			{
				$recommend_count=count($recommend_content_arr)/2;
				$new_recommend_arr=array();
				for($i=1;$i<=$recommend_count;$i++)
				{
					$new_recommend_arr[$i]['img']=$recommend_content_arr['recommend_img'.$i];
					$new_recommend_arr[$i]['common_jump_id']=$recommend_content_arr['recommend_common_jump_id'.$i];
					$recommend_common_where=array(
						'id'=>$recommend_content_arr['recommend_common_jump_id'.$i],
						'status'=>1,
					);
					$recommend_common_detail=$model->table('sj_common_jump')->where($recommend_common_where)->find();
					$new_recommend_arr[$i]['common_jump_detail']=$recommend_common_detail;
				}
				if($v['recommend_type']==1)
				{
					$feature[$k]['recommend_content_arr1'] =  $new_recommend_arr;
				}
				elseif($v['recommend_type']==2)
				{
					$feature[$k]['recommend_content_arr2'] =  $new_recommend_arr;
				}
				elseif($v['recommend_type']==3)
				{
					$feature[$k]['recommend_content_arr3'] =  $new_recommend_arr;
				}
				elseif($v['recommend_type']==4)
				{
					$feature[$k]['recommend_content_arr4'] =  $new_recommend_arr;
				}
			}
			//段落标题展示
			$title_content_arr = json_decode($v['title_content'],true);
			if($title_content_arr)
			{
				$title_type=$v['title_type'];
				$new_title_content_arr=array();
				if($title_type==1)//文本
				{
					$feature[$k]['title_content'] =  $title_content_arr['title_content'];
					$feature[$k]['title_font_size'] =  $title_content_arr['font_size'];
					$feature[$k]['title_font_color'] =  $title_content_arr['font_color'];
					$feature[$k]['title_img'] ="";
				}
				else//图片
				{
					$feature[$k]['title_img'] =  $title_content_arr['img'];
					$feature[$k]['title_content']="";
				}
				$title_common_where=array(
						'id'=>$title_content_arr['common_jump_id'],
						'status'=>1,
					);
				$title_common_detail=$model->table('sj_common_jump')->where($title_common_where)->find();
				$feature[$k]['title_common_jump_id'] =  $title_content_arr['common_jump_id'];
				$feature[$k]['title_common_jump_detail']=$title_common_detail;
			}
			$title_bg_content = $v['title_bg_content'];
			if($title_bg_content)
			{
				$feature[$k]['title_bg_content']=$title_bg_content;
			}
			//段落背景
			$bg_content = $v['bg_content'];
			if($bg_content)
			{
				$feature[$k]['bg_content']=$bg_content;
			}
			//段落标签
			$label_type=$v['label_type'];
			if($label_type==4)//文本
			{
				$label_content_arr = json_decode($v['label_content'],true);
				$feature[$k]['label_type_text'] =  $label_content_arr['label_type_text'];
				$feature[$k]['label_type_text_size'] =  $label_content_arr['label_type_text_size'];
				$feature[$k]['label_type_text_color'] =  $label_content_arr['label_type_text_color'];
				$feature[$k]['label_type_img']="";
				$feature[$k]['label_pre_words'] ="";
			}
			elseif($label_type==5)//图片
			{
				$feature[$k]['label_type_img'] = $v['label_content'];
				$feature[$k]['label_type_text'] ="";
				$feature[$k]['label_pre_words'] ="";
			}
			else
			{
				$feature[$k]['label_pre_words'] = $v['label_content'];
				$feature[$k]['label_type_text'] ="";
				$feature[$k]['label_type_img'] = "";
			}
			
			$label_one_download_content_arr = json_decode($v['label_one_download_content'],true);
			if($label_one_download_content_arr)
			{
				$is_one_download=$v['is_one_download'];
				if($is_one_download==1)//有
				{
					$feature[$k]['label_one_download_frame_color'] =  $label_one_download_content_arr['one_download_frame_color'];
					$feature[$k]['label_one_download_button_color'] =  $label_one_download_content_arr['one_download_button_color'];
					$feature[$k]['label_one_download_font_color'] =  $label_one_download_content_arr['one_download_font_color'];
				}
			}
			//段落软件
			//下载按钮
			$soft_download_content=$v['soft_download_content'];
			$soft_download_content_arr = json_decode($v['soft_download_content'],true);
			$feature[$k]['soft_download_frame_color'] =  $soft_download_content_arr['download_frame_color'];
			$feature[$k]['soft_download_button_color'] =  $soft_download_content_arr['download_button_color'];
			$feature[$k]['soft_download_font_color'] =  $soft_download_content_arr['download_font_color'];
			//软件标签内容配置
			$feature[$k]['soft_label_pre_words'] =  $v['soft_label_pre_words'];
			//软件推荐信息 
			$soft_recommend_info_content_arr = json_decode($v['soft_recommend_info_content'],true);
			$feature[$k]['soft_recommend_info'] =  $soft_recommend_info_content_arr['soft_recommend_info'];
			$feature[$k]['soft_recommend_info_color'] =  $soft_recommend_info_content_arr['soft_recommend_info_color'];
			//软件推荐人群 
			$soft_recommend_people_content_arr = json_decode($v['soft_recommend_people_content'],true);
			$feature[$k]['soft_recommend_people'] =  $soft_recommend_people_content_arr['soft_recommend_people'];
			$feature[$k]['soft_recommend_people_color'] =  $soft_recommend_people_content_arr['soft_recommend_people_color'];
			//软件标题 
			$soft_title_content_arr = json_decode($v['soft_title_content'],true);
			$feature[$k]['soft_title'] =  $soft_title_content_arr['soft_title'];
			$feature[$k]['soft_title_size'] =  $soft_title_content_arr['soft_title_size'];
			$feature[$k]['soft_title_color'] =  $soft_title_content_arr['soft_title_color'];
			//软件摘要 
			$soft_abstract_content_arr = json_decode($v['soft_abstract_content'],true);
			$feature[$k]['soft_abstract'] =  $soft_abstract_content_arr['soft_abstract'];
			$feature[$k]['soft_abstract_size'] =  $soft_abstract_content_arr['soft_abstract_size'];
			$feature[$k]['soft_abstract_color'] =  $soft_abstract_content_arr['soft_abstract_color'];
			//软件背景 特殊样式1 是三个背景 特殊样式2是一个个背景
			if($v['soft_type']==4)//特殊样式1 
			{
				$soft_all_bg_arr = json_decode($v['soft_all_bg'],true);
				$feature[$k]['soft1_bg_color'] =  $soft_all_bg_arr['soft1_bg_color'];
				$feature[$k]['soft2_bg_color'] =  $soft_all_bg_arr['soft2_bg_color'];
				$feature[$k]['soft3_bg_color'] =  $soft_all_bg_arr['soft3_bg_color'];
			}
			
		}
		//各个图片大小
		$this->assign('image_width_high', $this->image_width_high);
		$this->assign('image_height_high', $this->image_height_high);
		$this->assign('image_width_low', $this->image_width_low);
		$this->assign('image_height_low', $this->image_height_low);
		
		$this->assign('web_width', $this->web_width);
		$this->assign('web_height', $this->web_height);
		$this->assign('fresh_width', $this->fresh_width);
		$this->assign('fresh_height', $this->fresh_height);
		
		//专题改版
		$this->assign('header_single_img_width', $this->header_single_img_width);
		$this->assign('header_multiple_img_width', $this->header_multiple_img_width);
		$this->assign('top_end_img_width', $this->top_end_img_width);
		
		//V6.3大家说图片
		$this->assign('everybody_say_pic_width', $this->everybody_say_pic_width);
		$this->assign('everybody_say_pic_height', $this->everybody_say_pic_height);
		
        $this->assign('feature', $feature);
		//var_dump($feature);exit;
        $this->display();
    }


     //手机系统管理_专题类别管理_编辑_执行
    public function feature_edit() {	
		$time = time();
		$abilist = array(
			'1' => array('ABI_ARMEABI (普通机型)', $match_abi & 1),
			'2' => array('ABI_ARMEABI_V7A (普通机型)',$match_abi & 2),
			'4' => array('ABI_X86 (pc)',$match_abi & 4),
			'8' => array('ABI_MIPS (mips机型，例如君正)',$match_abi & 8),
		);
		$path = date('Ym/d/', $time);
        $this->pid = (int)$_POST['featureid'];
        $this->conf_db = M('feature');
		$channel=M("channel");
        $this->map['feature_id'] = $this->pid;

        $old_feature = $this->conf_db->where($this->map)->find();
		//print_r($old_feature);
        $old_feature['channel_id'] = substr($old_feature['channel_id'], 1, -1);
		$old_cids=explode(",",$old_feature['channel_id']);
		if(count($old_cids)>0){
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
		if(empty($old_feature['oid'])||$old_feature['oid']<=0){
			$old_oid_name="全部可见";
		}else{
			$old_feature['oid'] = substr($old_feature['oid'], 1, -1);
			$old_oid_name=$channel->table("pu_operating")->where(array("oid"=>$old_feature['oid']))->getfield("mname");
		}
		if(empty($old_feature['match_abi'])){
			$old_abi_name="全部过滤";
		}else{
			$abi_list = array(1,2,4,8);
			$abi = array();
			foreach($abi_list as $v) {
				if ($v & $old_feature['match_abi']) {
					$old_abi_name .=$abilist[$v][0]."|";
				}
			}
		}

		if($_POST['name']==''){
			$this->error('专题类别名称不能为空');
		}else if(mb_strlen($_POST['name'],'utf8')>15){
			$this->error('专题类别名称不能大于15个字符');
		}else if(mb_strlen($_POST['title'],'utf8')>15){
			$this->error('专题类别标题不能大于15');
		}else if($_POST['remark']==''|| $_POST['remark']=='请输入专题详情简介'){
			$this->error('专题详情简介不能为空');
                }
                if($_POST['pid']==''){
			$this->error('请勾选产品类型');
		}
		//v6.3上线时间
		$this->conf_list['public_time'] = strtotime($_POST['public_time']);
		if (!$this->conf_list['public_time']) {
			$this->error("上线时间不能为空");
		}
		
        

		//专题改版
		//0表示原生页面  1表示H5页面
		$feature_page_type = $_POST['feature_page_type'];
		if($feature_page_type==1)
		{
			foreach($_POST['main_key'] as $key =>$val)
			{
				if(empty($_POST['paragraph_detail'][$val]))
				{
					$this->error('请填写段落详情');
				}	
			}
			$this->conf_list['feature_page_bg'] = trim($_POST['feature_bg_color']);
			$this->conf_list['feature_comment_share_label_color'] = trim($_POST['feature_comment_share_label_color']);
			$this->conf_list['feature_comment_share_text_color'] = trim($_POST['feature_comment_share_text_color']);
		}
		else
		{
			foreach($_POST['editor_content'] as $k => $v){
				if(empty($v)) $this->error('请填写图文');
			}	
		}	
		
		if($_POST['note']=='' || $_POST['note'] =="请填写专题类别备注"){
			$note == "";
		}else{
			$note = $_POST['note'];
		}
        $this->conf_list['name']=trim($_POST['name']);
        $this->conf_list['note']=trim($note);
		if($_POST['orderid']){
			$this->conf_list['orderid']=trim($_POST['orderid']);
		}else{
			$this->conf_list['orderid']= 999999;
		}
        //$this->conf_list['orderid']=$_POST['orderid'];
        $this->conf_list['last_refresh']=$time;
        $this->conf_list['channel_id'] = '';
                $pidstr = ','.implode(',',$_POST['pid']).',';
		$this->conf_list['pid'] = $pidstr;
		//$this->conf_list['pid'] = $_POST['pid'];
		$this->conf_list['oid'] = ','. $_POST['oid']. ',';
		$this->conf_list['type'] = $_POST['type'];
		if(empty($_POST['oid'])){
			$new_oid_name="全部可见";
		}else{
			$new_oid_name=$channel->table("pu_operating")->where(array("oid"=>$_POST['oid']))->getfield("mname");
		}
		$this->conf_list['remark']=trim($_POST['remark']);
		
		$this->conf_list['match_abi'] = 0;
		if (!empty($_POST['match_abi']) && $_POST['match_abi']>0) {
			foreach ($_POST['match_abi'] as $v) {
				$new_abi_name .=$abilist[$v][0]."|";
				$this->conf_list['match_abi'] = ($this->conf_list['match_abi'] | $v);
			}
		}else{
			$new_abi_name .="全部过滤";
		}
		if (!empty($_POST['description'])) {
			$this->conf_list['description'] = $_POST['description'];
		}

		$config = array(
			'multi_config' => array(),
		);
		if($_POST['pid'] == 4){
			$webicon_width = 525;
			$webicon_height = 225;
			$webicon_size = 30;
		}else{
			$webicon_width = 170;
			$webicon_height = 100;
			$webicon_size = 15;
		}

		//尝鲜图片
		if($_FILES['fresh_hoticon']['size']){
			$pic_tmp = getimagesize($_FILES['fresh_hoticon']['tmp_name']);
			if($pic_tmp[0] != $this->fresh_width || $pic_tmp[1] != $this->fresh_height){
				$this -> error("请上传宽度为{$this->fresh_width },高度为{$this->fresh_height }的图片");
			}
		}
	
		include_once SERVER_ROOT. '/tools/functions.php';
		$folder = "/img/" . date("Ym/d/");
		$this->mkDirs(UPLOAD_PATH . $folder);
		if ($_FILES['fresh_hoticon']['size']) {
			//上传图片 尝鲜
			$fresh_hoticon_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['fresh_hoticon']['name'],$match_fresh_hoticon_img);
			$fresh_hoticon_img = $match_fresh_hoticon_img[0];
			$fresh_hoticon_img_path = $folder . time() . '_fresh_hoticon_img_'. rand(1000,9999) . $fresh_hoticon_img;
			$fresh_hoticon_img_path_last = UPLOAD_PATH . $fresh_hoticon_img_path;
			$ret = move_uploaded_file($_FILES['fresh_hoticon']['tmp_name'], $fresh_hoticon_img_path_last);
			$this->conf_list['fresh_hoticon']=$fresh_hoticon_img_path;
				
			/*$config['multi_config']['fresh_hoticon'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',	
			);*/
		}
		if(empty($old_feature['high_image_url']) && !$_FILES['icon_604_204']['tmp_name']){
			$this -> error("专题图片(新版高分):请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}的图片");
		}
		if(empty($old_feature['low_image_url']) && !$_FILES['icon_444_150']['tmp_name']){
			$this -> error("专题图片(新版低分):请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
		}
		if(empty($old_feature['everybody_say_pic']) && !$_FILES['everybody_say_pic']['tmp_name']){
			$this -> error("专题图片(大家说):请上传宽度为{$this->everybody_say_pic_width},高度为{$this->everybody_say_pic_height}的图片");
		}
		
		if ($_FILES['icon_604_204']['tmp_name']) {
			$image_604_204 = getimagesize($_FILES['icon_604_204']['tmp_name']);
			if($image_604_204[0] != $this->image_width_high || $image_604_204[1] != $this->image_height_high){
				$this -> error("专题图片(新版高分):请上传宽度为{$this->image_width_high},高度为{$this->image_height_high}的图片");
			}	
			//高分
			$feature_high_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['icon_604_204']['name'],$match_feature_high_img);
			$feature_high_img = $match_feature_high_img[0];
			$feature_high_img_path = $folder . time() . '_feature_high_img_'. rand(1000,9999) . $feature_high_img;
			$feature_high_img_path_80 = $folder . time() . '_feature_high_img_80_'. rand(1000,9999) . $feature_high_img;
			$feature_high_img_path_last = UPLOAD_PATH . $feature_high_img_path;
			$feature_high_img_path_last_80 = UPLOAD_PATH . $feature_high_img_path_80;
			$ret = move_uploaded_file($_FILES['icon_604_204']['tmp_name'], $feature_high_img_path_last);
			image_strip_size($feature_high_img_path_last,$feature_high_img_path_last_80,80*1024);
			$this->conf_list['high_image_url']= $feature_high_img_path;
			$this->conf_list['high_image_url_80']= $feature_high_img_path_80;
				
			/*$config['multi_config']['icon_604_204'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',	
				'img_p_size' =>  1024*80,
			);*/
		}
		if ($_FILES['icon_444_150']['tmp_name']) {
			$image_444_150 = getimagesize($_FILES['icon_444_150']['tmp_name']);
			if($image_444_150[0] != $this->image_width_low || $image_444_150[1] != $this->image_height_low){
				$this -> error("专题图片(新版低分):请上传宽度为{$this->image_width_low},高度为{$this->image_height_low}的图片");
			}		
			//低分
			$feature_low_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['icon_444_150']['name'],$match_feature_low_img);
			$feature_low_img = $match_feature_low_img[0];
			$feature_low_img_path = $folder . time() . '_feature_low_img_'. rand(1000,9999) . $feature_low_img;
			$feature_low_img_path_40 = $folder . time() . '_feature_low_img_40_'. rand(1000,9999) . $feature_low_img;
			$feature_low_img_path_last = UPLOAD_PATH . $feature_low_img_path;
			$feature_low_img_path_last_40 = UPLOAD_PATH . $feature_low_img_path_40;
			$ret = move_uploaded_file($_FILES['icon_444_150']['tmp_name'], $feature_low_img_path_last);
			image_strip_size($feature_low_img_path_last,$feature_low_img_path_last_40,40*1024);
			$this->conf_list['low_image_url']= $feature_low_img_path;
			$this->conf_list['low_image_url_40']= $feature_low_img_path_40;
		
			/*$config['multi_config']['icon_444_150'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',	
				'img_p_size' =>  1024*40,
			);*/
		}
		//大家说图片
		if ($_FILES['everybody_say_pic']['tmp_name']) {
			$everybody_say_pic = getimagesize($_FILES['everybody_say_pic']['tmp_name']);
			if($everybody_say_pic[0] != $this->everybody_say_pic_width || $everybody_say_pic[1] != $this->everybody_say_pic_height){
				$this -> error("专题图片(大家说):请上传宽度为{$this->everybody_say_pic_width},高度为{$this->everybody_say_pic_height}的图片");
			}		
			$everybody_say_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['everybody_say_pic']['name'],$match_everybody_say_img);
			$everybody_say_img = $match_everybody_say_img[0];
			$everybody_say_img_path = $folder . time() . '_everybody_say_img_'. rand(1000,9999) . $everybody_say_img;
			$everybody_say_img_path_last = UPLOAD_PATH . $everybody_say_img_path;
			$ret = move_uploaded_file($_FILES['everybody_say_pic']['tmp_name'], $everybody_say_img_path_last);
			$this->conf_list['everybody_say_pic']= $everybody_say_img_path;
		}
		$this->initval_check();
		if ($_FILES['webicon']['size']) {
			//网页
			$webicon_img = preg_match("/\.(jpg|png|gif)$/", $_FILES['webicon']['name'],$match_webicon_img);
			$webicon_img = $match_webicon_img[0];
			$webicon_img_path = $folder . time() . '_webicon_img_'. rand(1000,9999) . $webicon_img;
			$webicon_img_path_last = UPLOAD_PATH . $webicon_img_path;
			//$ret = move_uploaded_file($_FILES['webicon']['tmp_name'], $webicon_img_path_last);
			image_strip_size($_FILES['webicon']['tmp_name'],$webicon_img_path_last,15*1024);
			$this->conf_list['webicon']=$webicon_img_path;
				
			/*$config['multi_config']['webicon'] = array(
				'savepath' => UPLOAD_PATH. '/img/'. $path,
				'saveRule' => 'getmsec',
				'img_p_size' =>  1024*15,
				'img_p_width' =>  $this->web_width,
				'img_p_height' => $this->web_height,	
			);*/
		}

		/*if(!empty($config['multi_config'])){
			$lists=$this->_uploadapk(0, $config);
			foreach($lists['image'] as $val) {
				if ($val['post_name'] == 'fresh_hoticon') {
					$this->conf_list['fresh_hoticon']=$val['url'];
				}
				if ($val['post_name'] == 'icon_604_204') {
					$this->conf_list['high_image_url']=$val['url_original'];
					$this->conf_list['high_image_url_80']=$val['url'];
				}
				if ($val['post_name'] == 'icon_444_150') {
					$this->conf_list['low_image_url']=$val['url_original'];
					$this->conf_list['low_image_url_40']=$val['url'];
				}
				if ($val['post_name'] == 'webicon') {
					$this->conf_list['webicon']= $val['url'];
				}
			}
		}*/
		
	
        if (isset($_POST['cid'])) {
			if($_POST['cid'][0] != 0 && count($_POST['cid']) >= 1){
				$cids = array();
				foreach ($_POST['cid'] as $cid) {
					if ($cid >= 0)
						$cids[] = $cid;
				}
				$cids = array_unique($cids);
				if (count($cids) > 0) {
					$s = implode(',', $cids);
					$s = ",{$s},";            	
					$this->conf_list['channel_id'] = $s;
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
				$this->conf_list['channel_id'] = '';
			}
        }else{
			$zh_chname="全部可见";
		}

		//图文详情
		$model = new Model();
		if($feature_page_type==1)//新版图文  编辑内容  需做出修改
		{
			$this->conf_list['feature_page_type'] =1;
			$this->conf_list['feature_page_bg'] = trim($_POST['feature_bg_color']);
			$this->conf_list['feature_comment_share_label_color'] = trim($_POST['feature_comment_share_label_color']);
			$this->conf_list['feature_comment_share_text_color'] = trim($_POST['feature_comment_share_text_color']);
			foreach($_POST['main_key'] as $key =>$val)
			{
				$res = $this -> pub_add_feature_graphic_new($_POST,$_FILES,$val,$_POST['featureid']);
				if(!empty($_POST['feature_graphic_arrid'][$val])){//有就修改
					$where = array(
						'id' => $_POST['feature_graphic_arrid'][$val],
						'feature_id' => $_POST['featureid'],				
					);
					$res['update_tm'] = $time;
					$res['rank']= $key+1;
					$model -> table('sj_feature_graphic')->where($where)->save($res);
				}else{//没有就添加
					$res['feature_id']=$_POST['featureid'];
					$res['add_tm'] = $time;
					$res['update_tm'] = $time;
					$res['rank']= $key+1;
					//检查该专题改段落是否已经存在 解决的问题是由原生页面编辑成H5页面
					$is_have_where=array(
						'feature_id' => $_POST['featureid'],
						'rank' => $key+1,
						'status'=>1,
					);
					$is_have=$model -> table('sj_feature_graphic')->where($is_have_where)->find();
					if($is_have)//如果有先删除该专题该段落的数据
					{
						$del_where=array(
							'id'=>$is_have['id'],
						);
						$del_data = array(
						'update_tm' => $time,
						'status' => 0,
						);
						$model -> table('sj_feature_graphic')->where($del_where)->save($del_data);
						$model -> table('sj_feature_graphic')->add($res);
						//$model -> table('sj_feature_graphic')->where($del_where)->save($res);
					}
					else
					{
						$model -> table('sj_feature_graphic')->add($res);
					}					
				}
			}	
			//增加分享的跳转
			$share_where=array(
                'content_type' => 3,
                'feature_id' => $_POST['featureid'],
                'page_flag' => '0x00150000',
                'status' => 1,
			);
			$is_have_share = $model->table("sj_common_jump")->where($share_where)->find();
			if(!$is_have_share)
			{
				$new_data = array(
                    'content_type' => 3,
                    'feature_id' => $_POST['featureid'],
                    'page_flag' => '0x00150000',
					'create_at' => time(),
					'update_at' => time(),
					'status' => 1,
				);
				$share_ret =  $model->table('sj_common_jump')->add($new_data);
			}
		}
		else//旧版图文详情
		{
			$this->conf_list['feature_page_type'] =0;
			$this->conf_list['feature_page_bg'] = "";
			$this->conf_list['feature_comment_share_label_color'] = "";
			$this->conf_list['feature_comment_share_text_color'] = "";
			foreach($_POST['editor_content'] as $k => $v){
				$res = $this -> pub_add_feature_graphic($v,$_POST['featureid']);
				if($res['code'] == 1){
					if(!empty($_POST['feature_graphic_arrid'][$k])){
						$where = array(
							'id' => $_POST['feature_graphic_arrid'][$k],
							'feature_id' => $_POST['featureid'],				
						);
						$data = array(
							'content' => $res['msg'],
							'update_tm' => $time,
							'rank' => $k+1
						);
						$model -> table('sj_feature_graphic')->where($where)->save($data);
					}else{
						$data = array(
							'feature_id' => $_POST['featureid'],	
							'content' => $res['msg'],
							'add_tm' => $time,
							'update_tm' => $time,
							'rank' => $k+1,
						);		
						//检查该专题改段落是否已经存在 解决的问题是由H5页面编辑成原生页面
						$is_have_where=array(
							'feature_id' => $_POST['featureid'],
							'rank' => $k+1,
							'status'=>1,
						);
						$is_have=$model -> table('sj_feature_graphic')->where($is_have_where)->find();
						if($is_have)//如果有先删除该专题该段落的数据
						{
							$del_where=array(
								'id'=>$is_have['id'],
							);
							$del_data = array(
							'update_tm' => $time,
							'status' => 0,
							);
							$model -> table('sj_feature_graphic')->where($del_where)->save($del_data);
							$model -> table('sj_feature_graphic')->add($data);	
							//$model -> table('sj_feature_graphic')->where($del_where)->save($data);
						}
						else
						{
							$model -> table('sj_feature_graphic')->add($data);	
						}				
					}
				}else{
					$this->error($res['msg']);
				}
			}		
		}
		if($_POST['feature_graphic_strid']){
			$where = array(
				'id'=>array('in',substr($_POST['feature_graphic_strid'],0,-1))
			);
			$map = array(
				'status'=>0,
				'update_tm'=>$time
			);
			$model -> table('sj_feature_graphic')->where($where)->save($map);
			$where = array(
				'feature_graphic'=>array('in',substr($_POST['feature_graphic_strid'],0,-1))
			);
			$where = array(
				'feature_graphic_id'=>array('in',substr($_POST['feature_graphic_strid'],0,-1))
			);
			$map = array(
				'feature_graphic_id'=>0,
				'feature_id'=>$_POST['featureid'],
				'upload_time' => $time
			);
			$model -> table('sj_feature_soft')->where($where)->save($map);
		}
		$log1 = $this->logcheck(array('feature_id'=>$_POST['featureid']),'sj_feature',$this->conf_list,$this->conf_db);
        $this->writelog("专题配置-编辑了id为'".$_POST['featureid']."'".$log1, 'sj_feature',$_POST['featureid'],__ACTION__,'','edit');
        $list= $this->conf_db->where($this->map)->save($this->conf_list);

        $model = M('webmarket_feature_text');
        $data = array();
		$feature_id = $_POST['featureid'];
		$data['title'] = trim($_POST['title']);
		if($_POST['text']=='请输入WEB上的专题详情简介'){
			$data['text'] = "";
		}else{
			$data['text'] = trim($_POST['text']);
		}
		$res = $model->where("feature_id = $feature_id")->save($data);
		if($res==0){
			$data['feature_id'] = $_POST['featureid'];
			$data['status'] = 1;
			$model->data($data)->add();
		}
        if(false!==$list) {
		
			if ($this->conf_list['is_hot'] == 1) {
				$id = escape_string($_POST['featureid']);
				$sql = "update sj_feature set is_hot=0 where feature_id <> {$id} and channel_id = '{$this->conf_list['channel_id']}'";
				$this->conf_db->query($sql);
			}
			

            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Systemmanage/feature");
            $this->success("修改专题类别项成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Systemmanage/feature");
            $this->error('修改专题类别项失败！');
        }
    }
    //手机系统管理_专题类别管理_开启\关闭
    public function featuredel() {
        $this->pid=(int)$_GET['id'];
        $this->conf_db = M('feature');
		$p  = isset($_GET['p'])  ? (int)$_GET['p']  : 0;
		$lr = isset($_GET['lr']) ? (int)$_GET['lr'] : 20;
        $old_feature = $this->conf_db->where(array('feature_id' => $this->pid))->find();
        if($_GET['state']) {
            $this->map['status'] = 1;
            $this->writelog("启用ID为[{$this->pid}],名为[{$old_feature['name']}]的专题类别", 'sj_feature',$this->pid,__ACTION__ ,'','edit');
        }
		else
        {
		    $this->map['status']  = 0;
            $this->writelog(" 专题配置-停用ID为[{$this->pid}],名为[{$old_feature['name']}]的专题类别", 'sj_feature',$this->pid,__ACTION__ ,0,'edit');
        }

        if(false!==$this->conf_db->where(array('feature_id' => $this->pid))->save($this->map))
        {   
		    $feature_list = $this->conf_db->where('status = 1')->order('orderid asc')->select();
			
            $this->assign('jumpUrl',"/index.php/".GROUP_NAME."/Systemmanage/feature");
            $this->success("操作成功！");
        }else
        {
          $this->assign('jumpUrl',"/index.php/".GROUP_NAME."/Systemmanage/feature");
     	   $this->error("操作失败,发生错误！");
        }
    }
//-------------------------test

//------------------------------------网站渠道分类管理--------------------------------------
   //手机系统管理_渠道分类列表
    public function channel_category() {
        $this->conf_db = M('channel_category');
        $this->map['status']=1;
        $this->conf_list=$this->conf_db->where($this->map)->select();
        $this->assign('conflist',$this->conf_list);
        $this->display();
    }
    //手机系统管理_渠道分类管理_增加_显示
    public function channel_categoryadd() {
		if (!empty($_POST)) {
			$this->conf_db = M('channel_category');
			$this->conf_list['name']=trim($_POST['name']);
			if(empty($this->conf_list['name'])) {
			   //$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category');
			   $this->error("添加渠道分类项失败,名称不得为空！");
			}
			$this->conf_list['note']=trim($_POST['note']);
			$this->conf_list['status']=1;
			$this->conf_list['upload_time']=time();
			$this->conf_list['last_refresh']=time();

			if($id=$this->conf_db->add($this->conf_list)) {
				$this->writelog('增加了名称为['.$this->conf_list['name'].']的渠道分类', 'sj_channel_category',$id,__ACTION__ ,'','add');
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/channel_category');
				$this->success("添加渠道分类项成功！");
			}else
			{
			   $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/channel_category');
			   $this->error("添加渠道分类项失败,发生错误！");
			}
		} else {
			$this->display();
		}
    }
    //手机系统管理_渠道分类管理_编辑_显示
    public function channel_categoryedit() {
		$this->conf_db = M('channel_category');
		if (!empty($_POST)) {
			$this->map['category_id']=$_POST['categoryid'];
			$this->conf_list['name']=trim($_POST['name']);
			$this->conf_list['note']=trim($_POST['note']);
			$this->conf_list['status']=1;
			$this->conf_list['last_refresh']=time();

            $log = $this->logcheck(array('category_id'=>$_POST['categoryid']),'sj_channel_category',$this->conf_list,$this->conf_db);
			$this->lists= $this->conf_db->where($this->map)->save($this->conf_list);

			if(false!==$list) {
				//$this->writelog('编辑了id为'.$_POST['categoryid'].',的渠道分类', 'channel_category', $_POST['categoryid']);
				$this->writelog('编辑了id为'.$_POST['categoryid'].',的渠道分类,'.$log, 'sj_channel_category', $_POST['categoryid'],__ACTION__ ,'','edit');
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/channel_category');
				$this->success("修改渠道分类项成功！");
			}else
			{
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/channel_categoryedit/id/'.$_POST['categoryid']);
				$this->error('修改渠道分类项失败！');
			}
		
		} else {
			$this->map['category_id']=$_GET['id'];
			$this->conf_list=$this->conf_db->where($this->map)->find();
			$this->assign('conflist',$this->conf_list);

			$this->display();
		}
    }

    //手机系统管理_渠道分类管理_删除
    public function channel_categorydel() {
		$model = new Model();
        $this->map['category_id']=$_GET['id'];
        $this->conf_list['status']=0;
        if(false!==$model->table('sj_channel_category')->where($this->map)->save($this->conf_list))
        {	
			//把所属类型改成《未分类》
			$data = array('category_id' => 0);
			$model->table('sj_channel')->where($this->map)->save($data);
            $this->writelog('删除了id为'.$_GET['id'].'的软件分类', 'sj_channel_category', $_GET['id'],__ACTION__ ,'','del');
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/channel_category');
            $this->success("删除渠道分类项成功！");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/channel_category');
     	   $this->error("删除渠道分类失败,发生错误！");
        }
    }



    public function tomail() {
        vendor("PHPMailer.class#phpmailer");
        $this->smtp_mail('honking@spam.la','1231','123','332020572@qq.com');
    }

    protected function smtp_mail ( $sendto_email, $subject=null, $body=null,$sendto_name=null) {

        $mail = new PHPMailer(true);        //新建一个邮件发送类对象
        $mail->IsSMTP();                // send via SMTP

        $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
        $mail->Host       = "smtp.gmail.com";      // SMTP 邮件服务器地址,这里需要替换为发送邮件的邮箱所在的邮件服务器地址
        $mail->Port       = 465;                   // SMTP端口
        $mail->Username   = "buggoapk@gmail.com";  // SMTP服务器上此邮箱的用户名,有的只需要@前面的部分,有的需要全名。请替换为正确的邮箱用户名
        $mail->Password   = "liumang-841101";            // SMTP服务器上该邮箱的密码,请替换为正确的密码
        $mail->AddAddress($sendto_email,$sendto_name);  // 收件人邮箱和姓名
        //$mail->SetFrom('name@yourdomain.com', '领军人物');
        $mail->From = "lingjunrenwu@gmail.com";    // SMTP服务器上发送此邮件的邮箱,请替换为正确的邮箱,$mail->Username 的值是对应的。
        $mail->FromName =  "领军人物";  // 真实发件人的姓名等信息,这里根据需要填写
        $mail->CharSet = "utf-8";            // 这里指定字符集！
        $mail->Encoding = "base64";
        //$mail->AddReplyTo('lingjunrenwu@gmail.com',"系统管理员");//这一项根据需要而设 回复

        $mail->IsHTML(true);  // send as HTML
        // 邮件主题
        $mail->Subject = $subject;
        // 邮件内容
        $mail->Body = '<html><head><meta http-equiv="Content-Language" content="zh-cn"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'.$body.'</body></html>';
        $mail->AltBody ="text/html";
        if(!$mail->Send())
        {

            $this->assign( 'jumpUrl',SITE_URL.'/index.php/Message/recoverpasswd');
            $this->error('对不起,邮件发送错误。请稍后再试！');
        }
        else {

            $this->assign( 'jumpUrl',SITE_URL.'/index.php/Message/recoverpasswd');
            $this->success('邮件已成功发送至您的邮箱,请您在30分钟内修改密码,谢谢！');
        }
    }
	//三级分类添加  supwater
	function  add_category(){
		$pid = $_GET['p'];
		//echo $_POST['category_id'];
		$category=M("category");
		$model = new Model();
		$data['status']=$_POST['category_type'];
		$data['name']=$_POST['name'];
		if(!$data['name']){
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$pid);
			$this -> error("请输入分类名称");
		}else{
			$have_where['_string'] = "name = '{$data['name']}' and status != 0";
			$have_result = $model -> table('sj_category') -> where($have_where) -> select();
		
			if($have_result){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$pid);
				$this -> error("分类名称已存在");
			}
		}
		$data['parentid']=trim($_POST['parentid']);
		$data['orderid']=trim($_POST['orderid']);
		$this->conf_list['orderid']=trim($_POST['orderid']);
		$data['apply_pkg'] = trim($_POST['apply_pkg']);
		
		//$soft_result = $model -> table('sj_soft_file') -> where(array('apk_name' => $data['apply_pkg'])) -> select();
		$iconurl = $_FILES['iconurl'];
		$category_pic_h = $_FILES['category_pic_h'];
		$category_pic_m = $_FILES['category_pic_m'];
		//$data['category_point_first'] = $_POST['select_second_category'];
		if($_POST['select_second_category'] == 1){
			$data['category_point'] = trim($_POST['category_point_1']);
		}elseif($_POST['select_second_category'] == 2){
			$data['category_point'] = trim($_POST['category_point_2']);
		}elseif($_POST['select_second_category'] == 3){
			$data['category_point'] = trim($_POST['category_point_3']);
		}
		if(!(int)($data['category_point'])){
			$data['category_point'] = $_POST['select_second_category'];
		}
		if($data['status'] == 2){
			$where_have = "category_point = {$data['category_point']} and status != 0";
			$have_been = $category -> where($where_have) -> select();
		
			foreach($have_been as $key => $val){
				$check_result = $category -> where(array('category_id' => $val['parentid'])) -> select();
			
				if($check_result[0]['parentid'] == $pid){
					$this -> error("该一级分类下已存在该分类指向");
				}
			}
		}
		//$data['soft_icon'] = $soft_result[0]['iconurl_72'];
		$data['upload_time']=time();
		$data['last_refresh']=time();
		
		if($_POST['typical'] != '注意;以分号分隔每个名称;最多输入10个名称'){
			$data['typical']=trim($_POST['typical']);

			$typical[] = explode(";", $data['typical']);
			$n = count($typical[0]);
			if ($n > 10){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$pid);
				$this->error('最多输入10个名称');
			}
			foreach ($typical[0] as $t){
				if (mb_strlen($t, 'utf-8') > 10){
					$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$pid);
					$this->error('每个典型应用最多输入10个字符');
				}
			}
		}
		if($pid == 2){
			if(!$_POST['apply_pkg']){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id'.$pid);
				$this -> error("应用包名不能为空");
			}
			$been_result = $model -> table('sj_soft') -> where(array('package' => $_POST['apply_pkg'],'hide' => 1,'status' => 1)) -> select();
			if(!$been_result){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Systemmanage/category/id/{$pid}");
				$this -> error("应用包名不存在");
			}
			if(!$_FILES['category_pic_h']['size'] || !$_FILES['category_pic_m']['size']){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/p'.$pid);
				$this -> error("请上传图片");
			}
		}else{
			if(!$_FILES['iconurl']['size']){
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/p'.$pid);
				$this -> error("请上传图片");
			}
		}
		if(!empty($_FILES['iconurl']['size']) || !empty($_FILES['category_pic_h']['size']) || !empty($_FILES['category_pic_m']['size']) ) {
			$path = date('Ym/d/', time());
            $config = array(
            	'multi_config' => array(
            		'iconurl' => array(
            			'savepath' => UPLOAD_PATH. '/icon/'. $path,
            			'saveRule' => 'getmsec',
						'img_p_size' =>  1024*5,

            		),
					'category_pic_h' => array(
						'savepath' => UPLOAD_PATH. '/icon/'. $path,
            			'saveRule' => 'getmsec',
						'img_p_width'=> 218, //图片常规压缩宽度
						'img_p_height'=> 142, //图片常规压缩高度
					),
					'category_pic_m' => array(
						'savepath' => UPLOAD_PATH. '/icon/'. $path,
            			'saveRule' => 'getmsec',
						'img_p_width'=> 145, //图片常规压缩宽度
						'img_p_height'=> 94, //图片常规压缩高度
					),
            	),
				
            );
			$upload=$this->_uploadapk(0, $config);
	
			foreach($upload['image'] as $key => $val){
				if($val['post_name'] == 'iconurl'){
					$data['iconurl']   =   $val['url'];
				}
				if($val['post_name'] == 'category_pic_h'){
					$data['category_pic_h']   =   $val['url'];
				}
				if($val['post_name'] == 'category_pic_m'){
					$data['category_pic_m']   =   $val['url'];
				}
			}
			
		}


		$result = $category->add($data);
		
		if(false!==$result) {
            $this->writelog('增加了名称为['.$data['name'].']的软件分类', 'sj_category', $result,__ACTION__ ,'','add');
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$pid);
            $this->success("添加软件分类项成功！");
        }else
        {
           $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$pid);
     	   $this->error("添加软件分类项失败,发生错误！");
        }
	}
	function edit_category(){
		$category=M("category");
		$parentid=$_POST['id'];
		$data['parentid']=$_POST['category_id'];
		if(($data['parentid']=="0")||($data['parentid']=="")){
			$this->error("修改类别不能为空");
		}
		$old_parentid=$_POST['parentid'];
		$zh_categoryid=$_POST['zh_categoryid'];
        $data['last_refresh']=time();
		$where['category_id']=$zh_categoryid;
        $lists= $category->where($where)->save($data);
        if(false!==$lists) {
            $this->writelog('软件类别配置：编辑了id为'.$zh_categoryid.',的所属父分类为old_parentid为'.$old_parentid.'到新父分类parentid为'.$data['parentid'],'sj_category', $zh_categoryid,__ACTION__ ,'','edit');
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
            $this->success("修改软件分类项成功！");
        }else
        {
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category/id/'.$parentid);
            $this->error('修改软件分类项失败！');
        }
	}
	
	function category_icon()
	{
		$model = M("market_skin");
		$For = M("pic_show");
		$where = array(
			'status' => 1,
			'parentid' => array('in',array(1,2))
		);
		$top = $model->table('sj_category')->where($where)->field('category_id')->select();
		$category_list = array();
		$in = array();
		foreach ($top as $val) {
			$where = array(
				'parentid' => $val['category_id']
			);
			$sub = $model->table('sj_category')->where($where)->field('category_id, name')->select();
			foreach ($sub as $row) {
				$category_list[] = array($row['category_id'], $row['name']);
				$in[] = $row['category_id'];
			}
		}
		$skin_list = array();
		$pic_model = M("pic_category");
		$where_go['status'] = 1;
		$result = $pic_model -> where($where_go) -> select(); 
	
		$pic_all = array();
		foreach($result as $key => $val){
			$pic_list[$val['id']] = $val;
			$pic_all[$val['id']]['pic_id'] = $val['id'];
			$pic_all[$val['id']]['pic_name'] = $val['pic_name'];
			$where_category['status'] = 1;
			$where_category['pic_id'] = $val['id'];
			$skin_list = $For -> where($where_category) -> select();
			$category_all[$val['id']] = $skin_list;
		}
		//把category_id当做数组$category_all 第二层的key
		foreach($category_all as $key => $val){
			$name=$pic_model->where(array("status"=>1,"id"=>$key))->getfield("pic_name");
			foreach($val as $k => $v){
				$val2[$v['category_id']] = $v;
				$val=$val2;
			}
			$val['name']=$name;
			$category_all[$key]=$val;
		}

		$this -> assign("category_all",$category_all);
		$this->assign ( "category_list",  $category_list);
		$this->assign ( "pic_list", $pic_list );
		$this->display();
	}
	//添加类别图片
	function add_category_pic(){
		$model = M("pic_category");
		$category_name = isset($_GET['category_name']) ? $_GET['category_name'] : '';
		if($category_name){
			$model_category = M("pic_category");
			$data['status'] = 1;
			$data['pic_name'] = $category_name;
			$category_pic = $model_category -> add($data);
		}
		
		if($category_pic){
			$this->writelog('增加了名称为['.$category_name.']的类别图标','sj_pic_category', $category_pic,__ACTION__ ,'','add');
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category_icon');
            $this->success("添加类别图标成功！");
		}
		$this -> display("add_skin");
	}
	
	
	function category_icon_del()
	{
		$model = M("market_skin");
		$pic_id = $_GET['pic_id'];

		$where['status'] = 1;
		$category_skin = $model -> where($where) -> select();
		foreach($category_skin as $key => $val){
			$pic_all[] = $val['pic_id'];
		}

		if(!in_array($pic_id,$pic_all)){
			$where_go['id'] = $pic_id;
			$data['status'] = 0;
			$model_pic = M("pic_category");
		 $model_pic -> where($where_go) -> save($data);
		 
		 if($model_pic){
			$this->writelog('删除了ID为['.$pic_id.']的类别图标','sj_pic_category', $pic_id,__ACTION__ ,'','del');
            $this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Systemmanage/category_icon');
            $this->success("删除类别图标成功！");
		 }
		}else{
			$this -> error("此类别图标正在使用中");
		}
		
	}
	
	function category_icon_add()
	{
		file_put_contents('/tmp/honking.log', var_export($_POST, true));
		if(!empty($_FILES['iconurl']['size'])) {
			$path = date('Ym/d/', time());
            $config = array(
            	'multi_config' => array(
            		'iconurl' => array(
            			'savepath' => UPLOAD_PATH. '/icon/'. $path,
            			'saveRule' => 'time'
            		),
            	),
				//'img_p_size' =>  1024*5,
				//'img_p_width' =>  89,
				//'img_p_height' => 89,	
            );
			$upload=$this->_uploadapk(0, $config);
			
			$model = M('pic_show');
			$map = array(
				'category_id' => $_POST['category_id'],
				'pic_id' => $_POST['pic_id'],
				'iconurl' => $upload['image'][0]['url'],
				'create_at' => time(),
				'update_at' => time(),
				'status' => 1
			);
			$category_skin_id = 0;
			if (empty($_POST['category_skin_id'])) {
				$category_skin_id = $model->add($map);
				$this->writelog("增加分类id {$_POST['category_id']} 下, 皮肤id为 {$_POST['skin_id']} 的图标",'sj_pic_show', $category_skin_id,__ACTION__ ,'','add');
			} else {
				$where = array(
					'id' => $_POST['category_skin_id']
				);
				$category_skin_id = $_POST['category_skin_id'];
				$model->where($where)->save($map);
				$this->writelog("修改分类id {$_POST['category_id']} 下, 皮肤id为 {$_POST['skin_id']} 的图标",'sj_pic_show', $_POST['category_id'],__ACTION__ ,'','edit');
			}
			exit(IMGATT_HOST. $upload['image'][0]['url']. '?random='. time() . ','. $category_skin_id);
		}
	}

	/**
	* Desc:   标签管理
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-04-28
	*/
	function tags_list()
	{
		$model = D('Sj.Tags');
		$res = $model->getaglist($_GET);
		$this->assign('list',$res['list']);
		$this->assign('size',$res['count']);
		$this->assign('page',$res['page']);
		$order = isset($_GET['order'])?$_GET['order']:1;
		$this->assign('order',$order);
		$this->display();
	}

	/**
	* Desc:   添加标签
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-04-28
	*/
	function add_tag()
	{
		$model = D('Sj.Tags');
		if($this->isPost())
		{
			$tag_name = $_POST['tag_name'];
			$tag_name_old = $_POST['tag_name_old'];
			$tag_id = $_POST['tag_id'];
			if($tag_id) //修改
			{
				$rs = $model->editag($tag_id,$tag_name);
				if($rs>0){//修改成功
					$this->writelog('标签管理-标签列表，编辑了名称为"'.$tag_name_old.'"的标签新名称为"'.$tag_name.'"','sj_tag', $tag_id,__ACTION__ ,'','edit');
					echo 4;exit(0);
				}else if($rs==-1){
					echo 1;exit(0);
				}else{ //修改失败
					echo 5;exit(0);
				}
			
			}else{//新增
				$rs = $model->addtag($tag_name);
				if($rs==-1){//标签名已存在
					echo 1;exit(0);
				}else if($rs>0){//添加成功
					$this->writelog('标签管理-标签列表，新增了名称为"'.$tag_name.'"的标签','sj_tag', $rs,__ACTION__ ,'','add');
					echo 2;exit(0);
				}else{ //添加失败
					echo 3;exit(0);
				}
			}
		}

		$tag_id = $_GET['tag_id'];
		$rett = $model->table('sj_tag')->field('tag_name')->where("status=1 and tag_id = $tag_id")->find();
		$tag_name = $rett['tag_name'];
		if($tag_id){
			$this->assign('title','修改');
			$this->assign('tag_name',$tag_name);
		}else{
			$this->assign('title','新增');
		}

		$this->display();
	}


	/**
	* Desc:   有标签软件管理 supwater
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-04-28
	*/
	function tags_softlist()
	{
		if($this->isAjax())
		{
			$model = D('Sj.Tags');
			$softids = $_POST['softids'];
			$softidarr = explode(',',$softids);

			$rearr = array();
			foreach($softidarr as $k=>$v)
			{
				$key = 'tag_'.$v;
				$rs = $model->getsoftinfo($v);
				$rearr[$k]['key'] = $key;
				$rearr[$k]['value'] = $rs['tags'];
			}
			$this->ajaxReturn($rearr,'获取成功',1);
		}


		//软件类型
		$soft_tmp = D("Dev.Softaudit");
		$cname = $soft_tmp ->return_category();
	$this -> assign('cname',$cname);

		//软件类别条件
		if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
		}

		$model = D('Sj.Tags');
		$this->assign('order',$_GET['order']);

		if($_GET){
			$res = $model->getagsoftlist($_GET);
		}

		$this->assign('list',$res['list']);

		$softids = '';
		foreach($res['list'] as $v)
		{
			$softids = $softids.$v['softid'].',';
		}
		$softids = substr($softids,0,-1);
		$this->assign('softids',$softids);


		$this->assign('page',$res['page']);
		$this->assign('count',$res['count']);
		$this->display();
	}

	/**
	* Desc:   无标签软件管理
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-04-28
	*/
	function softlist()
	{
		$model = D('Sj.Tags');
		$res = $model->getaglist();
		$this->assign('list',$res['list']);
		$this->assign('page',$res['page']);
		$this->display();
	}

	/**
	* Desc:   删除标签
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-04
	*/
	function deletetag()//supwater
	{
		$model = D('Sj.Tags');
		$res = $model->deltag($_POST['tag_id']);
		if($res!=false){
		$this->writelog('标签管理-标签列表，删除了ID为"'.$_POST['tag_id'].'"的标签','sj_tag', $_POST['tag_id'],__ACTION__ ,'','del');
			echo 1;exit(0);
		}
	}

	/**
	* Desc:   从软件进入编辑标签页面
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-04
	*/
	function edit_soft_tag()
	{
		$softid = $_GET['softid'];
		$model = D('Sj.Tags');
		$tmpar = $model->table('sj_soft')->field('category_id')->where("softid=$softid")->find();
		$cid = str_replace(',','',$tmpar['category_id']);
		$tmparr = $model->table('sj_category')->field('name')->where("category_id=$cid")->find();
		$this->assign('cname',$tmparr['name']);


		$rs = $model->getsoftinfo($softid);
		$this->assign('info',$rs);

		if($this->isPost())
		{
			$tags = $_POST['tags'];
			$old_tags = $_POST['old_tags'];
			$package= $_POST['package'];
			$res_num = $this->diff_tag($package,$old_tags,$tags);
			if($res_num==1)
			{
				if(empty($old_tags))
				{
					$this->writelog('标签管理-无标签软件，包名为"'.$package.'"的软件,添加了"'.$tags.'"的标签','sj_tag',$package,__ACTION__ ,'','add');
				}else{
					$this->writelog('标签管理-有标签软件，包名为"'.$package.'"的软件,标签从"'.$old_tags.'"修改成了"'.$tags.'"','sj_tag',$package,__ACTION__ ,'','edit');
				}
			}
			echo $res_num;exit(0);
		}

		$this->display();
	}
	
	/**
	* Desc:   对比新旧tags得到新增和删除的标签
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-05
	*/
	function diff_tag($package,$old_tags,$new_tags)
	{
		$model = D('Sj.Tags');
		//$old_tags = "android,app,f,tencentgame,w,weixin,交友,开讯,微信,微信下载,聊天,腾讯,腾讯新闻,讯风,讯风输入法";
		//$new_tags = "android,app,f,tencentgame,w,weixin,交友,开讯,微信,微信下载,聊天,讯风输入法,st,讯风,腾讯微博,ts,ttt";
		$oldarr = array_filter(explode(',',$old_tags));
		$newarr = array_filter(explode(',',$new_tags));
		$newarr = array_unique($newarr);
		$del = array_diff($oldarr,$newarr);//要删的
		$add = array_diff($newarr,$oldarr);//新增的
		if(count($del)==0&&count($add)==0){
			//未做更改;
			return 1;
		}

		if(count($del)!=0)
		{
			$del_str_tags = '"'.implode('","',$del).'"';
			$rs = $model->del_soft_tag($package,$del_str_tags);
			if($rs ==false){  //删除异常
				return 2;
			}
		}

		if(count($add)!=0)
		{
			$rs = $model->add_soft_tag($package,$add);
			if($rs ==false){   //新增异常
				return 3;
			}
		}

		$res = $model->update_soft_note($package,$newarr); 
		if($res==false){ //更新note失败
			//return 4;   //supwater  上线后打开
		}
		return 1;
	}

   /**
	* Desc:   获取常用标签
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-05
	*/
	function getCommontag()
	{
		//$tags = '360安全卫士,360手机卫士,psv,dashui,psp,手机卫士11,,';
		$model = D('Sj.Tags');

		$rs = $model->getsoftinfo($softid);

		if($this->isPost())
		{
			$cid = $_POST['category_id'];
			$ret = $model->getTagsnamebycid($cid);
			if($ret==-1){
				echo 1;exit(0);
			}else
			{
				//$commontag_arr = array('psv','psp','ps3','3ds','xbox360');
				$commontag_arr = $ret;
				$tags = $_POST['tags'];
				$tagsarr = array_filter(explode(',',$tags));
				$res = array_unique(array_merge($tagsarr,$commontag_arr));
				$newtags = implode(',',$res);
				$this->ajaxReturn($newtags,"合并成功",1);
			}
		}
	}

   /**
	* Desc:   获取该分类下包含软件最多的tag前10
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-05
	*/
	function getSoftTop()
	{
		$memcache_obj = new Memcache;
		$memcache_obj->connect(C(MC_HOST), C(MC_PORT));

		$prefix = 'tagcid_';
		
		if($this->isPost())
		{
			$model = D('Sj.Tags');
			$cid = $_POST['category_id'];
			$cid = str_replace(',','',$cid);
			$mc_key = $prefix.$cid;

			$rs = $memcache_obj->get($mc_key);
			$commontag_arr = unserialize($rs);
			$commontag_arr = array_slice($commontag_arr,0,10);

			if(empty($commontag_arr)){
				echo 1;exit(0);
			}
			$tags = $_POST['tags'];
			$tagsarr = array_filter(explode(',',$tags));
			$res = array_unique(array_merge($tagsarr,$commontag_arr));
			$newtags = implode(',',$res);
			$this->ajaxReturn($newtags,"合并成功",1);
		}
	}

	/**
	* Desc:   无标签软件管理
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-06
	*/
	function notags_softlist()
	{
		//软件类型
		$soft_tmp = D("Dev.Softaudit");
		$cname = $soft_tmp ->return_category();
	$this -> assign('cname',$cname);

		//软件类别条件
		if(!empty($_GET['cateid'])){
				$cateids = explode(',',$_GET['cateid']);
				$cateid = array_flip($cateids);
				$this -> assign('cateid',$cateid);
				$this -> assign("init_cateid",$_GET['cateid']);
		}

		$model = D('Sj.Tags');
		$this->assign('order',$_GET['order']);

		if($_GET){
			$res = $model->getnotagsoftlist($_GET);
		}

		$this->assign('list',$res['list']);
		$this->assign('page',$res['page']);
		$this->assign('count',$res['count']);
		$this->display();
	}


	/**
	* Desc:   设置标签数以及从缓存获取标签逻辑处理
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-07
	*/
	function set_tag_num()
	{
		$model = M('pu_config');
		$res = $model->table('pu_config')->where("config_type='TAG_NUM'")->find();
			
		$memcache_obj = new Memcache;
		$memcache_obj->connect(C(MC_HOST), C(MC_PORT));

		if($this->isPost())
		{
			$setnum = $_POST['setnum'];
			$data['configcontent'] = $setnum;
			$model->table('pu_config')->where("config_type='TAG_NUM'")->save($data);

			$tags = $_POST['tags'];

			$prefix = 'tagcid_';
			$cid = $_POST['categoryid'];
			$cid = str_replace(',','',$cid);

			$rr = $model->table(' sj_category')->field('status,category_point')->where("category_id=$cid")->find();
			if($rr['status']==2)
			{
				$cid = $rr['category_point'];
			}


			$mc_key = $prefix.$cid;

			$rs = $memcache_obj->get($mc_key);
			$commontag_arr = unserialize($rs);
			$commontag_arr = array_slice($commontag_arr,0,$setnum);

			if(empty($commontag_arr)){ //未获取到缓存
				echo 1;exit(0);
			}

			$tagsarr = array_filter(explode(',',$tags));//有荐的

			$del_value =array();
			foreach($tagsarr  as $v){
				if(strpos($v,'(荐)')>0)
				{
					$del_value[]=str_replace('(荐)','',$v);
				}
			}

			foreach($commontag_arr as $key => $vv){
				if(in_array($vv,$del_value))
				{
					unset($commontag_arr[$key]);
				}
			}

			$res = array_unique(array_merge($tagsarr,$commontag_arr));
			$newtags = implode(',',$res);

			$this->ajaxReturn($newtags,'获取成功',1);
		}

		$this->assign('tag_num',$res['configcontent']);
		$this->display();
	}

	/**
	* Desc:   设置标签数以及获取带来下载最多的标签
	* @author Sun Tao<suntao@anzhi.com>
	* @final  2014-05-08
	*/
	function set_tag_num_down()
	{
		$model = M('pu_config');
		$res = $model->table('pu_config')->where("config_type='TAG_NUM'")->find();
			
		$memcache_obj = new Memcache;
		$memcache_obj->connect(C(MC_HOST), C(MC_PORT));

		if($this->isPost())
		{
			$setnum = $_POST['setnum'];
			$data['configcontent'] = $setnum;
			$model->table('pu_config')->where("config_type='TAG_NUM'")->save($data);

			$tags = $_POST['tags'];

			$prefix = 'tagcid_down_';
			$cid = $_POST['categoryid'];
			$cid = str_replace(',','',$cid);

			$rr = $model->table(' sj_category')->field('status,category_point')->where("category_id=$cid")->find();
			if($rr['status']==2)
			{
				$cid = $rr['category_point'];
			}



			$mc_key = $prefix.$cid;

			$rs = $memcache_obj->get($mc_key);
			$commontag_arr = unserialize($rs);
			$commontag_arr = array_slice($commontag_arr,0,$setnum);

			if(empty($commontag_arr)){ //未获取到缓存
				echo 1;exit(0);
			}

			$tagsarr = array_filter(explode(',',$tags));//有荐的

			$del_value =array();
			foreach($tagsarr  as $v){
				if(strpos($v,'(荐)')>0)
				{
					$del_value[]=str_replace('(荐)','',$v);
				}
			}

			foreach($commontag_arr as $key => $vv){
				if(in_array($vv,$del_value))
				{
					unset($commontag_arr[$key]);
				}
			}

			$res = array_unique(array_merge($tagsarr,$commontag_arr));
			$newtags = implode(',',$res);

			$this->ajaxReturn($newtags,'获取成功',1);
		}

		$this->assign('tag_num',$res['configcontent']);
		$this->display();
	}
	
	//修改专题排序
	function change_orders(){
		$model = new Model();
		$featureid = $_GET['featureid'];
		$rank = $_GET['rank'];
		if(!$rank){
			$rank = 999999;
		}
		$log_result = $this->logcheck(array('feature_id' => $featureid),'sj_feature',array('orderid' => $rank),$model);
		$result = $model -> table('sj_feature') -> where(array('feature_id' => $featureid)) -> save(array('orderid' => $rank));
		if($result){
			$this -> writelog("已修改id为{$featureid}的排序,".$log_result,'sj_feature',$featureid,__ACTION__ ,'','edit');
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function show_channel(){
		$model = new Model();
		$featureid = $_GET['featureid'];
		$result = $model -> table('sj_feature') -> where(array('feature_id' => $featureid)) -> find();
		$my_channel = explode(',',$result['channel_id']);
		foreach($my_channel as $key => $val){
			$chname_result = $model -> table('sj_channel') -> where(array('cid' => $val)) -> find();
			if($chname_result){
				$chname_str .= $chname_result['chname'].',';
			}
			$channel_str = substr($chname_str,0,-1);
		}
		$this -> assign('channel_str',$channel_str);
		$this -> display();
	}
	//市场运营_软件类别配置__编辑__开发者热门标签
	function hot_tag(){
		$model = D('Sj.Tags');
		$c_id =  $_GET['cat_id'];
		if($_POST){
			if(trim($_POST['tag_name']) ==''){
				exit(json_encode(array('code'=>'0','msg'=>'标签名不能为空')));	
			}
			$res = $model -> add_hot_tag();
			if($res['code'] == 1){
				$this -> writelog("添加了标签为{$_POST['tag_name']}的标签id为{$res['id']}的开发者热门标签",'sj_feature',$res['id'],__ACTION__ ,'','add');
				exit(json_encode(array('code'=>'1','msg'=>$res['msg'])));	
			}else{
				exit(json_encode(array('code'=>'0','msg'=>$res['msg'])));	
			}
		}else{
			$list = $model -> get_hot_tag($c_id);
			if($_GET['from'] == 1){
				//add_new_confirm.html ajax调用返回
				exit(json_encode(array('list'=>$list)));	
			}else{
				$this -> assign('list1',$list['0-9 其他']);
				unset($list['0-9 其他']);
				$this -> assign('list',$list);
				$this -> assign('cat_name',$_GET['cat_name']);
				$this -> display();
			}
		}
	}
	//市场运营_软件类别配置__编辑__开发者热门标签__删除标签
	function del_hot_tag(){
		$model = new Model();
		$id_arr = explode(',',$_POST['id']);
		$result = $model -> table('sj_hot_tag') -> where(array('id' => array('in',$id_arr))) -> save(array('status'=>0));
		if($result){
			$this -> writelog("删除了标签id为{$_POST['id']}的开发者热门标签",'sj_hot_tag',$_POST['id'],__ACTION__ ,'','del');
			exit(json_encode(array('code'=>'1','msg'=>'操作成功')));	
		}else{
			exit(json_encode(array('code'=>'0','msg'=>'操作失败')));	
		}
	}
	
	//批量导入标签
	function import_tags(){
		if ($_FILES) {
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
			//判断内容是否有重复
			$tmp_arr = $content_arr;
			foreach($content_arr as $k=>$v){
				unset($tmp_arr[$k]);
				foreach($tmp_arr as $tmp_k=>$tmp_v){
					if($v[0]==$tmp_v[0]){
						$error_msg = '第'.($k+2).'行和第'.($tmp_k+2).'的内容重复';
						$this->ajaxReturn("",$error_msg, -4);
					}
				}
				$tmp_arr[$k] = $v;
			}


            // 判断后台有没有人正在导入
            $lock_name = 'sj_systemmanage_tag_importing';
            $import_lock = S($lock_name);
            if ($import_lock) {
                $this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
            }
            // 上锁，设置60秒内有效
            S($lock_name, 1, 60, 'File');
            // 返回导入结果，如果记录的flag为0表示添加失败
            $result_arr = $this->process_import_array($content_arr);
            // 导入后解锁
            S($lock_name, NULL);
            // 返回数据给页面
            if(empty($result_arr)){
                $this->ajaxReturn("",'导入成功！', 0);
            } else {
                $this->ajaxReturn($result_arr['msg'],'存在部分导入失败记录！', -5);
            }
        } else {
            $this->display("import_tags");
        }	
	}
	//处理导入标签并入库
	function process_import_array($content_arr) {
		$model = new Model();
		$result_arr = $all_tag = $all_pack = $package_tag = array();
		foreach($content_arr as $k=>$v){
			$v[1] = str_replace('，',',',$v[1]);
			$tags = explode(',',$v[1]);
			foreach($tags as $t_k=>$t_v){
				$all_tag[] = $t_v;
			}
			$all_pack[] = $v[0];
			$package_tag[$v[0]] = $tags;
		}
		$all_tag = array_unique($all_tag);

		//已存在标签
		$has_tag_info = $model->table('sj_tag')->where(array('tag_name'=>array('in',$all_tag),'status'=>1))->field('tag_id,tag_name')->select();
		$has_tag = array();
		$nothas_tag = array();
		foreach($has_tag_info as $has_key=>$has_val){
			$has_tag[$has_val['tag_name']] = $has_val;
		}

		//新标签
		foreach($all_tag as $all_v){
			if(!array_key_exists($all_v,$has_tag)){
				$nothas_tag[$all_v]['tag_name'] = $all_v;  
			}
		}

		//插入新标签
		foreach($nothas_tag as $nothas_key=>$nothas_val){
			$res = $model->table('sj_tag')->add(array('tag_name'=>$nothas_val['tag_name'],'addtime'=>time()));
			if($res){
				$this->writelog('标签管理-标签列表，新增了名称为"'.$nothas_val['tag_name'].'"的标签','sj_tag',$res,__ACTION__ ,'','add');
				$nothas_tag[$nothas_key]['tag_id'] = $res;
			}else{
				$result_arr['msg'][] = $nothas_val['tag_name'].'在添加到标签表时出现错误';
			}
			
		}
		//处理完的带id的所有标签
		$all_process_tag = array_merge($has_tag,$nothas_tag);
		//将标签名换成标签id
		foreach($package_tag as $p_key=>$p_val){
			foreach($p_val as $me_key=>$me_val){
				$p_val[$me_key] = $all_process_tag[$me_val]['tag_id'];
			}
			$package_tag[$p_key] = $p_val;
		}
		$need_insert = array();
		//转化最后需要插入的标签
		$package_has_tag = $model->table('sj_tag_package')->where(array('package'=>array('in',$all_pack)))->select();
		foreach($package_tag as $package_key=>$package_val){
			foreach($package_val as $p_k=>$p_v){
				foreach($package_has_tag as $pa_k=>$pa_v){
					if($package_key==$pa_v['package']){
						if($p_v==$pa_v['tag_id']){
							unset($package_val[$p_k]);
						}
					}
				}
			}
			$package_tag[$package_key] = $package_val;
		}
		foreach($package_tag as $last_key=>$last_val){
			foreach($last_val as $insert_k=>$insert_v){
				$id = $model->table('sj_tag_package')->add(array('package'=>$last_key,'tag_id'=>$insert_v));
				if($id){
					$this->writelog('包名为"'.$last_key.'"的软件,添加了'.$insert_v.'的标签','sj_tag_package',$id,__ACTION__ ,'','add');
				}else{
					$error_tag = $model->table('sj_tag')->where(array('tag_id'=>$insert_v))->field('tag_name')->find();
					$result_arr['msg'][] = '包名为'.$last_key.'的软件标签'.$error_tag['tag_name'].'添加时出现错误';
				}
			}
			
		}
		return $result_arr;

	}
	//读取csv
	function import_file_to_array($file) {
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
	//注释新增作者
	//增加作者管理  added by shitingting
	/*public function author_manage_list() 
	{
        $model = M();
        $where =array(
            'status' => 1
        );
        $list = $model->table('sj_author')->where($where)->select(); 
		
		 // 查找该作者下有多少个专题
		foreach ($list as $key => $author)
		{
			$where = array(
				'author_id' => $author['id'],
				'status' => 1,
			);
			$count = $model->table('sj_feature')->where($where)->count();
			$list[$key]['feature_count'] = $count;
		}		
        $this->assign('author_list', $list);
        $this->display();
    }
    
    public function add_author() 
	{
        if ($_POST) 
		{
            $model = M();
            $data = array();
            $author_name = trim($_POST['author_name']);
            if (!$author_name) 
			{
                $this->error("作者名称不能为空");
            }
            if (mb_strlen($feature_type_name, 'utf-8') > 10) 
			{
                $this->error("作者名称不能超出10个字");
            }
			// 取得图片后缀
			$suffix = preg_match("/\.(jpg|png)$/", $_FILES['header_images']['name'], $matches);
			if ($matches) 
			{
				$suffix = $matches[0];
			} 
			else 
			{
				$this->error('上传图片格式错误！');
			}
			//判断图片的长和宽
			list($width_new, $height_new, $type_new, $attr_new)=getimagesize($_FILES['header_images']['tmp_name']);
			$path = date("Ym/d/");
			if(!empty($_FILES['header_images']['size']))
			{
			   if($width_new==80&&$height_new==80)
				{
					$config['multi_config']['header_images'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => 80,
						'img_p_height' => 80,
						'img_p_size'   => 1024 * 35,
						'img_s_size'   => 1024 * 90,     
					);
				}
				else
				{
				 $this -> error("作者头像仅限上传80*80尺寸的JPG/PNG图片");
				}
			}
			else
			{
				$this -> error("请上传头像");
			}
			if (!empty($config['multi_config'])) 
			{
			   $list = $this->_uploadapk(0, $config);
			   foreach($list['image'] as $val) 
			    {
					if ($val['post_name'] == 'header_images') 
					{
						$image = $val['url'];
						$data['author_image'] = $image;
					}
			    }
			}
            // 检查冲突
            $conflict_where = array(
                'status' => 1,
                'author_name' => $author_name,
            );
            $conflict_find = $model->table('sj_author')->where($conflict_where)->find();
            if ($conflict_find) 
			{
                $this->error("作者名称与id为{$conflict_find['id']}的作者名字重复！");
            }
			$data['status']=1;
            $data['author_name'] = $author_name;
            $data['create_tm'] = $data['update_tm'] = time();
            // 添加
            $ret = $model->table('sj_author')->add($data);
            if ($ret) {
                $this->writelog("市场专题管理_专题配置_作者管理：新增了id为{$ret}的作者");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Systemmanage/author_manage_list");
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
        }
		else 
		{
            $this->display();
        }
    }
    
    public function edit_author() {
        if ($_POST) 
		{
            $model = M();
            $id = $_POST['id'];
            $data = array();
            $author_name = trim($_POST['author_name']);
            if (!$author_name) 
			{
                $this->error("作者名称不能为空");
            }
            if (mb_strlen($feature_type_name, 'utf-8') > 10) 
			{
                $this->error("作者名称不能超出10个字");
            }
            // 检查冲突
            $conflict_where = array(
                'status' => 1,
                'author_name' => $author_name,
                'id' => array('neq', $id),
            );
            $conflict_find = $model->table('sj_author')->where($conflict_where)->find();
            if ($conflict_find) 
			{
                $this->error("作者名称与id为{$conflict_find['id']}的作者名称重复！");
            }
            $data['author_name'] = $author_name;
            $data['update_tm'] = time();
			
			// 取得图片后缀
			if(!empty($_FILES['header_images']['size']))
			{
				$suffix = preg_match("/\.(jpg|png)$/", $_FILES['header_images']['name'], $matches);
				if ($matches) 
				{
					$suffix = $matches[0];
				} 
				else 
				{
					$this->error('上传图片格式错误！');
				}
				//判断图片的长和宽
				list($width_new, $height_new, $type_new, $attr_new)=getimagesize($_FILES['header_images']['tmp_name']);
				$path = date("Ym/d/");
			    if($width_new==80&&$height_new==80)
				{
					$config['multi_config']['header_images'] = array(
						'savepath' => UPLOAD_PATH. '/img/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width'  => 80,
						'img_p_height' => 80,
						'img_p_size'   => 1024 * 35,
						'img_s_size'   => 1024 * 90,     
					);
				}
				else
				{
				 $this -> error("作者头像仅限上传80*80尺寸的JPG/PNG图片");
				}

				if (!empty($config['multi_config'])) 
				{
				   $list = $this->_uploadapk(0, $config);
				   foreach($list['image'] as $val) 
					{
						if ($val['post_name'] == 'header_images') 
						{
							$image = $val['url'];
							$data['author_image'] = $image;
						}
					}
				}
			}
            // 添加
            $where = array(
                'id' => $id,
            );
            $log = $this->logcheck($where, 'sj_author', $data, $model);
            $ret = $model->table('sj_author')->where($where)->save($data);
            if ($ret) 
			{
                $this->writelog("市场专题管理_专题配置_管理作者：编辑了id为{$id}的作者，{$log}");
				$this->assign('jumpUrl','/index.php/'.GROUP_NAME."/Systemmanage/author_manage_list");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } 
		else 
		{
            $model = M();
            $id = $_GET['id'];
            $where = array(
                'id' => $id,
                'status' => 1,
            );
            $find = $model->table('sj_author')->where($where)->find();
            $this->assign('find', $find);
            $this->display();
        }
    }
    
    public function delete_author() 
	{
        $id = $_GET['id'];
        $model = M();
        // 查找该作者下还有没有专题，有的话不允许删除
        $where = array(
            'author_id' => $id,
            'status' => 1,
        );
        $count = $model->table('sj_feature')->where($where)->count();
        if ($count) {
            $this->ajaxReturn(0, "该作者下仍有专题，请先将专题移走后再删除", 1);
        }
        // 可以删除专题
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $data = array(
            'update_tm' => time(),
            'status' => 0,
        );
        $ret = $model->table('sj_author')->where($where)->save($data);
        if ($ret) {
            $this->writelog("市场专题管理_专题配置_管理作者：删除了id为{$id}的作者");
            $this->ajaxReturn(0, "删除成功！", 0);
        } else {
            $this->ajaxReturn(0, "删除失败！", -1);
        }
    }
    
    // 一个作者的专题全部移动至另一个作者
    public function move_to_author() 
	{
        $from_author_id = $_GET['from_author_id'];
        $to_author_id = $_GET['to_author_id'];
        // 将sj_feature里author_id为$from_author_id的更改为$to_author_id
        $model = M();
        $where = array(
            'author_id' => $from_author_id,
            'status' => 1,
        );
        $data = array(
            'last_refresh' => time(),
            'author_id' => $to_author_id,
        );
        $ret = $model->table('sj_feature')->where($where)->save($data);
        if ($ret) {
            $this->writelog("市场专题管理_专题配置_管理作者：id为{$from_author_id}的作者下的专题全部移动到id为{$to_author_id}的作者下");
            $this->ajaxReturn(0, "移动成功！", 0);
        } else {
            $this->ajaxReturn(0, "移动失败！", -1);
        }
    }*/
	//2015-05-15 @庄超滨 专题配置-新增专题--添加图文详情
	public static function pub_add_feature_graphic($v,$feature_id){
		static $upload_model;
		if(!$upload_model){
			$upload_model = D("Dev.Uploadfile");
		}		
		//如果get_magic_quotes_gpc()是打开的
		if(get_magic_quotes_gpc()){
			$v=stripslashes($v);//将字符串进行处理
		}		
		$v = preg_replace('#<img(.*?)src=[\'"](.*?)(/img/.*?)[\'"][^>]*>#ie',"getImgId('\\3',$feature_id)",$v);
		//文章中图片处理,开始
		preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$v,$matches);
		if($matches[1]) {	//有需要上传的新图片
			$pre_path = $_SERVER['DOCUMENT_ROOT'];		//web根目录
			//图片宽度不超过600px检查				
			foreach($matches[1] as $key => $val) {
				unset($width,$height,$type,$attr);
				list($width,$height,$type,$attr) = getimagesize($pre_path.$val);
				if($width>600) {
					return array('code' => 0,'msg' =>'有图片宽度超过限定的600px，请检查！' );
				}
			
			}

			//上传图片
			$files = array();
			$files_name = array();
			foreach($matches[1] as $key => $val) {
				$files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
			}
			foreach($matches[1] as $key => $val) {
				$files[$files_name[$key]] = '@'.$pre_path.$val;
			}
			$vals = array(
				'do' => 'save',
				'static_data' => '/data/att/m.goapk.com',
			);
			$arr =  $upload_model -> _http_post(array_merge($vals,$files));
			if($arr['info']['http_code']!=200) {
				return array('code' => 0,'msg' =>"和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
			}
			//删除public下图片
			foreach($matches[1] as $key => $val) {
				unlink($pre_path.$val);
			}
			$new_arr = array();
			if($arr['ret']) {
				$day = date('Ym/d');
				$dir = UPLOAD_PATH.'/img/'.$day.'/';
				if (!is_dir($dir)) {
					mkdir($dir, 0777, true);
				}
				include_once SERVER_ROOT. '/tools/functions.php';
				foreach($arr['ret'] as $key=>$val) {
					list($msec,$sec) = explode(' ',microtime());
					$msec = substr($msec,2);
					$img_get =  getimagesize(UPLOAD_PATH.$val);
					$path = "anzhi_feature_".$feature_id."_".$msec.".jpg";
					$path_80 = "anzhi_feature_".$feature_id."_".$msec."_80.jpg";
					$path_40 = "anzhi_feature_".$feature_id."_".$msec."_40.jpg";
					copy(UPLOAD_PATH.$val, $dir.$path);
					$ret_80 =  copy(UPLOAD_PATH.$val, $dir.$path_80);
					if(!$ret_80){
						$log = date('Y-m-d H:i:s')."---".UPLOAD_PATH.$val . "=>".$dir.$path_80."copy失败";
						permanentlog('anzhi_feature_80.log', $log);
					}
					$ret_40 = copy(UPLOAD_PATH.$val, $dir.$path_40);
					if(!$ret_40){
						$log = date('Y-m-d H:i:s')."---".UPLOAD_PATH.$val . "copy=>".$dir.$path_40."失败";
						permanentlog('anzhi_feature_40.log', $log);
					}					
					image_strip_size(UPLOAD_PATH.$val, $dir.$path_80, 1024*80); 					
					image_strip_size(UPLOAD_PATH.$val, $dir.$path_40, 1024*40); 					
					unlink(UPLOAD_PATH.$val);
					$val = '/img/'.$day.'/'.$path ;
					$val_80 = '/img/'.$day.'/'.$path_80 ;
					$val_40 = '/img/'.$day.'/'.$path_40 ;
					unset($k,$new_k);
					$k = array_search($key,$files_name);
					$new_k = $matches[1][$k];
					$map = array(
						'imgurl' => $val,
						'imgurl_80' => $val_80,
						'imgurl_40' => $val_40,
						'add_tm' => time(),
						'from_id' => $feature_id,
						'img_width' => $img_get[0],
						'img_height' => $img_get[1],
					);
					$img_id = $upload_model -> table('sj_graphic_image')->add($map);
					$new_arr[$new_k] = "[img]".$img_id."[/img]";
					//$new_arr[$new_k] = IMGATT_HOST.$val;
				}
				//文章内容中图片路径替换
				$editor_content = strtr($v,$new_arr);
				$pattern = "#<img(.*?)src=['\"]+\[img\]([\d\s]+)\[/img\]['\"]+[^>]*>#";
				$replacement = "[img]\\2[/img]";
				$editor_content =  preg_replace($pattern, $replacement, $editor_content);
			}

		}else{
			$editor_content = $v;
		}
		return array('code' => 1,'msg' =>$editor_content);
	}
	//新版 图文
	public function pub_add_feature_graphic_new($data,$file,$section,$feature_id)
	{
		//var_dump($file);exit;
		$model=new Model();
		$map=array();
		$map['status']=1;
		$new_data=array();
		//段落头图
		$paragraph_header=$data['paragraph_header'][$section];
		$new_data['is_header']=$paragraph_header;
		if($paragraph_header==1)
		{
			$header_type=$data['header_type'][$section];
			$new_data['header_type']=$header_type;
			$param_arr=array();
			if($header_type==1)
			{
				//var_dump($file['fixed_img']);exit;
				//图片 以及图片大小的比对
				$fixed_img=$file['fixed_img']['size'][$section];
				if($fixed_img)
				{
					$header_fixed_img = getimagesize($file['fixed_img']['tmp_name'][$section]);
					if($header_fixed_img[0] != $this->header_single_img_width)
					{
						$this -> error("专题段落头图:请上传宽度为{$this->header_single_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$fixed_img_suffix = preg_match("/\.(jpg|png|gif)$/", $file['fixed_img']['name'][$section],$match_fixed_img_suffix);
					$fixed_img_suffix = $match_fixed_img_suffix[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$header_fixed_img_path = $folder . time() . '_header_fixed_img_'. rand(1000,9999) . $fixed_img_suffix;
					$header_fixed_img_path_last = UPLOAD_PATH . $header_fixed_img_path;
					$ret = move_uploaded_file($file['fixed_img']['tmp_name'][$section], $header_fixed_img_path_last);
					$img = $header_fixed_img_path;
				}
				else
				{
					$fixed_img_hidden=$data['fixed_img_hidden'][$section];
					if($fixed_img_hidden)//编辑 图片已经存在
					{
						$img=$fixed_img_hidden;
					}
					else
					{
						$img="";
					}
				}
				//推荐内容处理 合并 
				$content_key = 'header_fix1_'.$section;
				if($data['content_type'][$content_key])
				{
					$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$content_key);
					if($rcontent!==true)
					{
						$this -> error($rcontent);
					}
					else
					{
						if($data['fixed_img_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$jump_where = array(
								'id' => $data['fixed_img_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($jump_where)->save($map);
							$common_jump_id=$data['fixed_img_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$common_jump_id = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$common_jump_id ="";
				}
				//保存图片和推荐内容id的json
				$param_arr=array(
					'img1' =>$img,
					'common_jump_id1'=>$common_jump_id,
				);
			}elseif($header_type==2)
			{
				//图片 以及图片大小的比对
				$fixed_img1=$file['fixed_img1']['size'][$section];
				$fixed_img2=$file['fixed_img2']['size'][$section];
				if($fixed_img1)
				{
					$header_fixed_img1 = getimagesize($file['fixed_img1']['tmp_name'][$section]);
					if($header_fixed_img1[0] != $this->header_multiple_img_width)
					{
						$this -> error("专题段落头图:请上传宽度为{$this->header_multiple_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$fixed_img1_suffix = preg_match("/\.(jpg|png|gif)$/", $file['fixed_img1']['name'][$section],$match_fixed_img1_suffix);
					$fixed_img1_suffix = $match_fixed_img1_suffix[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$header_fixed_img1_path = $folder . time() . '_header_fixed_img1_'. rand(1000,9999) . $fixed_img1_suffix;
					$header_fixed_img1_path_last = UPLOAD_PATH . $header_fixed_img1_path;
					$ret = move_uploaded_file($file['fixed_img1']['tmp_name'][$section], $header_fixed_img1_path_last);
					$img1 = $header_fixed_img1_path;
				}
				else
				{
					$fixed_img1_hidden=$data['fixed_img1_hidden'][$section];
					if($fixed_img1_hidden)//编辑 图片已经存在
					{
						$img1=$fixed_img1_hidden;
					}
					else
					{
						$img1="";
					}
				}
				//推荐内容处理 合并 
				$content_key = 'header_fix2_'.$section.'_1';
				if($data['content_type'][$content_key])
				{
					$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$content_key);
					if($rcontent!==true)
					{
						$this -> error($rcontent);
					}
					else
					{
						if($data['fixed_img1_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$jump_where1 = array(
								'id' => $data['fixed_img1_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($jump_where1)->save($map);
							$common_jump_id1=$data['fixed_img1_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$common_jump_id1 = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$common_jump_id1 ="";
				}
				if($fixed_img2)
				{
					$header_fixed_img2 = getimagesize($file['fixed_img2']['tmp_name'][$section]);
					if($header_fixed_img2[0] != $this->header_multiple_img_width)
					{
						$this -> error("专题段落头图:请上传宽度为{$this->header_multiple_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$fixed_img2_suffix = preg_match("/\.(jpg|png|gif)$/", $file['fixed_img2']['name'][$section],$match_fixed_img2_suffix);
					$fixed_img2_suffix = $match_fixed_img2_suffix[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$header_fixed_img2_path = $folder . time() . '_header_fixed_img2_'. rand(1000,9999) . $fixed_img2_suffix;
					$header_fixed_img2_path_last = UPLOAD_PATH . $header_fixed_img2_path;
					$ret = move_uploaded_file($file['fixed_img2']['tmp_name'][$section], $header_fixed_img2_path_last);
					$img2 = $header_fixed_img2_path;
				}
				else
				{
					$fixed_img2_hidden=$data['fixed_img2_hidden'][$section];
					if($fixed_img2_hidden)//编辑 图片已经存在
					{
						$img2=$fixed_img2_hidden;
					}
					else
					{
						$img2="";
					}
				}
				//推荐内容处理 合并 
				$content_key = 'header_fix2_'.$section.'_2';
				if($data['content_type'][$content_key])
				{
					$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$content_key);
					if($rcontent!==true)
					{
						$this -> error($rcontent);
					}
					else
					{
						if($data['fixed_img2_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$jump_where2 = array(
								'id' => $data['fixed_img2_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($jump_where2)->save($map);
							$common_jump_id2=$data['fixed_img2_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$common_jump_id2 = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$common_jump_id2 ="";
				}
				
				//保存图片和推荐内容id的json
				$param_arr=array(
					'img1' =>$img1,
					'common_jump_id1'=>$common_jump_id1,
					'img2' =>$img2,
					'common_jump_id2'=>$common_jump_id2,
				);
			}elseif($header_type==3)
			{
				//图片 以及图片大小的比对 scroll_img_hidden[header_scroll_section_1_1]
				//$count1=0;
				//$count2=0;
				$i=0;
				foreach($file['scroll_img']['size'] as $key=>$val)
				{
					$pos = strpos($key,'header_scroll_'.$section.'_');
					if($pos==0&&$pos!==false)//说明是同一段的图片
					{
						if(!$val&&!$data['scroll_img_hidden'][$key])
						{
							continue;
						}
						else
						{
							//$count1++;
							$i++;
							$scroll_img="scroll_img".$i;
							$common_jump_id = "common_jump_id".$i;
							
							//$scroll_pic_key = 'header_scroll_'.$section.'_'.$i;
							$scroll_pic_key = $key;
							$img=$file['scroll_img']['size'][$scroll_pic_key];
							if($img)
							{
								$header_scroll_img = getimagesize($file['scroll_img']['tmp_name'][$scroll_pic_key]);
								if($header_scroll_img[0] != $this->header_single_img_width)
								{
									$this -> error("专题段落头图:请上传宽度为{$this->header_single_img_width}的图片");
								}
								
								include_once SERVER_ROOT. '/tools/functions.php';
								//上传图片
								$scroll_img_suffix = preg_match("/\.(jpg|png|gif)$/", $file['scroll_img']['name'][$scroll_pic_key],$match_scroll_img);
								$scroll_img_suffix = $match_scroll_img[0];
								$folder = "/img/" . date("Ym/d/");
								$this->mkDirs(UPLOAD_PATH . $folder);
								$scroll_img_path = $folder . time() . '_scroll_img_'. rand(1000,9999) . $scroll_img_suffix;
								$scroll_img_path_last = UPLOAD_PATH . $scroll_img_path;
								$ret = move_uploaded_file($file['scroll_img']['tmp_name'][$scroll_pic_key], $scroll_img_path_last);
								$$scroll_img = $scroll_img_path;
							}
							else
							{
								$scroll_img_hidden=$data['scroll_img_hidden'][$scroll_pic_key];
								if($scroll_img_hidden)//编辑 图片已经存在
								{
									$$scroll_img=$scroll_img_hidden;
								}
								else
								{
									$$scroll_img='';
								}
							}
							//推荐内容处理 合并 content_type[header_scroll_section_1_2]
							//$content_key = 'header_scroll_'.$section.'_'.$i;
							$content_key = $key;
							if($data['content_type'][$content_key])
							{
								$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$content_key);
								if($rcontent!==true)
								{
									$this -> error($rcontent);
								}
								else
								{
									if($data['scroll_img_jump_id'][$content_key])//说明是编辑推荐内容
									{
										$map['update_at']=time();
										$jump_where = array(
											'id' => $data['scroll_img_jump_id'][$content_key],			
										);
										$model -> table('sj_common_jump')->where($jump_where)->save($map);
										$$common_jump_id=$data['scroll_img_jump_id'][$content_key];
									}
									else  //添加
									{
										//保存数据
										$map['create_at']=time();
										$map['update_at']=time();
										$$common_jump_id = $model -> table('sj_common_jump')->add($map);
									}	
								}
							}
							else
							{
								$$common_jump_id ="";
							}
							$param_arr['img'.$i]=$$scroll_img;
							$param_arr['common_jump_id'.$i]=$$common_jump_id;
						}							
					}
				}
			}elseif($header_type==4)
			{
				//图片 以及图片大小的比对
				//$count1=0;
				//$count2=0;
				$i=0;
				foreach($file['scroll_img_multiple']['size'] as $key=>$val)
				{
					$pos = strpos($key,'header_scroll_multiple_'.$section.'_');
					if($pos==0&&$pos!==false)//说明是同一段的图片
					{
						if(!$val&&!$data['scroll_img_multiple_hidden'][$key])
						{
							continue;
						}
						else
						{
							//$count1++;
							$i++;
							$scroll_img="scroll_multiple_img".$i;
							$common_jump_id = "common_jump_id".$i;
							
							//$scroll_pic_key = 'header_scroll_'.$section.'_'.$i;
							$scroll_pic_key=$key;
							$img=$file['scroll_img_multiple']['size'][$scroll_pic_key];
							if($img)
							{
								$header_scroll_img = getimagesize($file['scroll_img_multiple']['tmp_name'][$scroll_pic_key]);
								
								if($header_scroll_img[0] != $this->header_multiple_img_width)
								{
									$this -> error("专题段落头图:请上传宽度为{$this->header_multiple_img_width}的图片");
								}
								
								include_once SERVER_ROOT. '/tools/functions.php';
								//上传图片
								$scroll_img_suffix = preg_match("/\.(jpg|png|gif)$/", $file['scroll_img_multiple']['name'][$scroll_pic_key],$match_scroll_img);
								$scroll_img_suffix = $match_scroll_img[0];
								$folder = "/img/" . date("Ym/d/");
								$this->mkDirs(UPLOAD_PATH . $folder);
								$scroll_img_path = $folder . time() . '_scroll_img_'. rand(1000,9999) . $scroll_img_suffix;
								$scroll_img_path_last = UPLOAD_PATH . $scroll_img_path;
								$ret = move_uploaded_file($file['scroll_img_multiple']['tmp_name'][$scroll_pic_key], $scroll_img_path_last);
								$$scroll_img = $scroll_img_path;
							}
							else
							{
								$scroll_img_multiple_hidden=$data['scroll_img_multiple_hidden'][$scroll_pic_key];
								if($scroll_img_multiple_hidden)//编辑 图片已经存在
								{
									$$scroll_img=$scroll_img_multiple_hidden;
								}
								else
								{
									$$scroll_img= '';
								}
							}
							//推荐内容处理 合并 content_type[header_scroll_section_1_2]
							//$content_key = 'header_scroll_multiple_'.$section.'_'.$i;
							$content_key = $key;
							if($data['content_type'][$content_key])
							{
								$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$content_key);
								if($rcontent!==true)
								{
									$this -> error($rcontent);
								}
								else
								{
									if($data['scroll_img_multiple_jump_id'][$content_key])//说明是编辑推荐内容
									{
										$map['update_at']=time();
										$jump_where = array(
											'id' => $data['scroll_img_multiple_jump_id'][$content_key],			
										);
										$model -> table('sj_common_jump')->where($jump_where)->save($map);
										$$common_jump_id=$data['scroll_img_multiple_jump_id'][$content_key];
									}
									else  //添加
									{
										//保存数据
										$map['create_at']=time();
										$map['update_at']=time();
										$$common_jump_id = $model -> table('sj_common_jump')->add($map);
									}	
								}
							}
							else
							{
								$$common_jump_id ="";
							}
							$param_arr['img'.$i]=$$scroll_img;
							$param_arr['common_jump_id'.$i]=$$common_jump_id;
						}
					}
				}
			}
			$new_data['header_content'] = json_encode($param_arr);
		}
		//段落标题
		$paragraph_title=$data['paragraph_title'][$section];
		$new_data['is_title']=$paragraph_title;
		if($paragraph_title==1)
		{
			$title_type=$data['title_type'][$section];
			$new_data['title_type']=$title_type;
			$alignment_type=$data['alignment_type'][$section];
			$new_data['title_alignment_type']=$alignment_type;
			if($title_type==1)
			{
				//文本
				$title_content=$data['title_content'][$section];
				$font_size=$data['font_size'][$section];
				$font_color=$data['font_color'][$section];
				//推荐内容处理 合并 
				$title_content_key = 'title_'.$section;
				if($data['content_type'][$title_content_key])
				{
					$title_rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$title_content_key);
					if($title_rcontent!==true)
					{
						$this -> error($title_rcontent);
					}
					else
					{
						if($data['title_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$jump_where = array(
								'id' => $data['title_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($jump_where)->save($map);
							$title_common_jump_id=$data['title_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$title_common_jump_id = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$title_common_jump_id ="";
				}
				//保存图片和推荐内容id的json
				$title_param_arr=array(
					'title_content' =>$title_content,
					'font_size' =>$font_size,
					'font_color' =>$font_color,
					'common_jump_id'=>$title_common_jump_id,
				);
			}elseif($title_type==2)
			{
				//图片 
				$title_image=$file['title_image']['size'][$section];
				if($title_image)
				{
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$title_img = preg_match("/\.(jpg|png|gif)$/", $file['title_image']['name'][$section],$match_title_img);
					$title_img = $match_title_img[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$title_img_path = $folder . time() . '_title_img_'. rand(1000,9999) . $title_img;
					$title_img_path_last = UPLOAD_PATH . $title_img_path;
					$ret = move_uploaded_file($file['title_image']['tmp_name'][$section], $title_img_path_last);
					$title_image_new = $title_img_path;
				}
				else
				{
					$title_image_hidden=$data['title_image_hidden'][$section];
					if($title_image_hidden)
					{
						$title_image_new=$title_image_hidden;
					}
					else{
						$title_image_new="";
					}
				}
				//推荐内容处理 合并 
				$title_content_key = 'title_'.$section;
				if($data['content_type'][$title_content_key])
				{
					$title_rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$title_content_key);
					if($title_rcontent!==true)
					{
						$this -> error($title_rcontent);
					}
					else
					{
						if($data['title_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$jump_where = array(
								'id' => $data['title_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($jump_where)->save($map);
							$title_common_jump_id=$data['title_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$title_common_jump_id = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$title_common_jump_id ="";
				}
				
				//保存图片和推荐内容id的json
				$title_param_arr=array(
					'img' =>$title_image_new,
					'common_jump_id'=>$title_common_jump_id,
				);
			}
			$new_data['title_content'] = json_encode($title_param_arr);
			//段落标题背景
			$paragraph_title_bg=$data['paragraph_title_bg'][$section];
			$new_data['title_bg_type']=$paragraph_title_bg;
			if($paragraph_title_bg==2)//纯色  颜色
			{
				$title_bg_color=$data['title_bg_color'][$section];
				$new_data['title_bg_content']=$title_bg_color;
			}
			else if($paragraph_title_bg==3)//图片
			{
				//图片 
				$title_bg_imgs=$file['title_bg_imgs']['size'][$section];
				if($title_bg_imgs)
				{
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$title_bg_img = preg_match("/\.(jpg|png|gif)$/", $file['title_bg_imgs']['name'][$section],$match_title_bg_img);
					$title_bg_img = $match_title_bg_img[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$title_bg_img_path = $folder . time() . '_title_bg_img_'. rand(1000,9999) . $title_bg_img;
					$title_bg_img_path_last = UPLOAD_PATH . $title_bg_img_path;
					$ret = move_uploaded_file($file['title_bg_imgs']['tmp_name'][$section], $title_bg_img_path_last);
					$title_bg_imgs_new = $title_bg_img_path;
				}
				else
				{
					$title_bg_hidden=$data['title_bg_hidden'][$section];
					if($title_bg_hidden)
					{
						$title_bg_imgs_new=$title_bg_hidden;
					}
					else{
						$title_bg_imgs_new="";
					}
				}
				$new_data['title_bg_content']=$title_bg_imgs_new;
			}
		}
		//段落背景
		$paragraph_bg=$data['paragraph_bg'][$section];
		$new_data['is_bg']=$paragraph_bg;
		if($paragraph_bg==1)
		{
			$bg_type=$data['bg_type'][$section];
			$new_data['bg_type']=$bg_type;
			if($bg_type==1)
			{
				//纯色 颜色
				$bg_color=$data['bg_color'][$section];
				$new_data['bg_content']=$bg_color;
			}elseif($bg_type==2)
			{
				//图片 
				$bg_imgs=$file['bg_imgs']['size'][$section];
				if($bg_imgs)
				{
					$bg_imgs_detail = getimagesize($file['bg_imgs']['tmp_name'][$section]);
					if($bg_imgs_detail[0] != $this->top_end_img_width)
					{
						$this -> error("专题段落背景:请上传宽度为{$this->top_end_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$bg_img = preg_match("/\.(jpg|png|gif)$/", $file['bg_imgs']['name'][$section],$match_bg_img);
					$bg_img = $match_bg_img[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$bg_img_path = $folder . time() . '_bg_img_'. rand(1000,9999) . $bg_img;
					$bg_img_path_last = UPLOAD_PATH . $bg_img_path;
					$ret = move_uploaded_file($file['bg_imgs']['tmp_name'][$section], $bg_img_path_last);
					$bg_imgs_new = $bg_img_path;
				}
				else
				{
					$bg_content_hidden=$data['bg_content_hidden'][$section];
					if($bg_content_hidden)
					{
						$bg_imgs_new=$bg_content_hidden;
					}
					else{
						$bg_imgs_new="";
					}
				}
				$new_data['bg_content']=$bg_imgs_new;
			}
		}
		//段落顶部
		$paragraph_top=$data['paragraph_top'][$section];
		$new_data['is_top']=$paragraph_top;
		if($paragraph_top==1)
		{
			$top_type=$data['top_type'][$section];
			$new_data['top_type']=$top_type;
			if($top_type==4)//自定义图片
			{
				//图片 
				$top_custom_imgs=$file['top_custom_imgs']['size'][$section];
				if($top_custom_imgs)
				{
					$top_custom_imgs_detail = getimagesize($file['top_custom_imgs']['tmp_name'][$section]);
					if($top_custom_imgs_detail[0] != $this->top_end_img_width)
					{
						$this -> error("专题段落顶部:请上传宽度为{$this->top_end_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$top_custom_img = preg_match("/\.(jpg|png|gif)$/", $file['top_custom_imgs']['name'][$section],$match_top_custom_img);
					$top_custom_img = $match_top_custom_img[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$top_custom_img_path = $folder . time() . '_top_custom_img_'. rand(1000,9999) . $top_custom_img;
					$top_custom_img_path_last = UPLOAD_PATH . $top_custom_img_path;
					$ret = move_uploaded_file($file['top_custom_imgs']['tmp_name'][$section], $top_custom_img_path_last);
					$top_custom_imgs_new = $top_custom_img_path;
				}
				else
				{
					$top_content_hidden=$data['top_content_hidden'][$section];
					if($top_content_hidden)
					{
						$top_custom_imgs_new=$top_content_hidden;
					}
					else{
						$top_custom_imgs_new="";
					}
				}
				$new_data['top_content']=$top_custom_imgs_new;
			}
		}
		//段落标签
		$paragraph_label=$data['paragraph_label'][$section];
		$new_data['is_label']=$paragraph_label;
		if($paragraph_label==1)
		{
			$label_type=$data['label_type'][$section];
			$new_data['label_type']=$label_type;
			if($label_type==4)//纯文本
			{
				$label_type_text=$data['label_type_text'][$section];
				$label_type_text_size=$data['label_type_text_size'][$section];
				$label_type_text_color=$data['label_type_text_color'][$section];
				$label_content_arr=array(
					'label_type_text'=>$label_type_text,
					'label_type_text_size'=>$label_type_text_size,
					'label_type_text_color'=>$label_type_text_color,
				);
				$new_data['label_content'] = json_encode($label_content_arr);
			}else if($label_type==5)//图片
			{ 
				$label_type_custom_imgs=$file['label_type_custom_imgs']['size'][$section];
				if($label_type_custom_imgs)
				{
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$label_type_custom_img = preg_match("/\.(jpg|png|gif)$/", $file['label_type_custom_imgs']['name'][$section],$match_label_type_custom_img);
					$label_type_custom_img = $match_label_type_custom_img[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$label_type_custom_img_path = $folder . time() . '_label_type_custom_img_'. rand(1000,9999) . $label_type_custom_img;
					$label_type_custom_img_path_last = UPLOAD_PATH . $label_type_custom_img_path;
					$ret = move_uploaded_file($file['label_type_custom_imgs']['tmp_name'][$section], $label_type_custom_img_path_last);
					$label_type_custom_imgs_new = $label_type_custom_img_path;
				}
				else
				{
					$label_type_img_hidden=$data['label_type_img_hidden'][$section];
					if($label_type_img_hidden)
					{
						$label_type_custom_imgs_new=$label_type_img_hidden;
					}
					else{
						$label_type_custom_imgs_new="";
					}
				}
				$new_data['label_content'] = $label_type_custom_imgs_new;
			}
			else//预定义图片添加配置文字内容
			{
				$new_data['label_content'] = $data['label_pre_words'][$section];
			}
			$is_one_download=$data['is_one_download'][$section];
			$new_data['is_one_download']=$is_one_download;
			if($is_one_download==1)
			{
				$one_download_frame_color=$data['one_download_frame_color'][$section];
				$one_download_button_color=$data['one_download_button_color'][$section];
				$one_download_font_color=$data['one_download_font_color'][$section];
				$one_download_arr=array(
					'one_download_frame_color'=>$one_download_frame_color,
					'one_download_button_color'=>$one_download_button_color,
					'one_download_font_color'=>$one_download_font_color,
				);
				$new_data['label_one_download_content']=json_encode($one_download_arr);
			}
			$label_bg=$data['label_bg'][$section];
			$new_data['label_bg_type']=$label_bg;
			if($label_bg==1)//纯色
			{
				$label_bg_color=$data['label_bg_color'][$section];
				$new_data['label_bg_content']=$label_bg_color;
			}
			else if($label_bg==2)//图片
			{
				$label_bg_imgs=$file['label_bg_imgs']['size'][$section];
				if($label_bg_imgs)
				{
					$label_bg_imgs_detail = getimagesize($file['label_bg_imgs']['tmp_name'][$section]);
					if($label_bg_imgs_detail[0] != $this->top_end_img_width)
					{
						$this -> error("专题段落标签背景:请上传宽度为{$this->top_end_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$label_bg_img = preg_match("/\.(jpg|png|gif)$/", $file['label_bg_imgs']['name'][$section],$match_label_bg_img);
					$label_bg_img = $match_label_bg_img[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$label_bg_img_path = $folder . time() . '_label_bg_img_'. rand(1000,9999) . $label_bg_img;
					$label_bg_img_path_last = UPLOAD_PATH . $label_bg_img_path;
					$ret = move_uploaded_file($file['label_bg_imgs']['tmp_name'][$section], $label_bg_img_path_last);
					$label_bg_imgs_new = $label_bg_img_path;
				}
				else
				{
					$label_bg_content_hidden=$data['label_bg_content_hidden'][$section];
					if($label_bg_content_hidden)
					{
						$label_bg_imgs_new=$label_bg_content_hidden;
					}
					else{
						$label_bg_imgs_new="";
					}
				}
				$new_data['label_bg_content'] = $label_bg_imgs_new;
			}
		}
		//段落详情
		$paragraph_detail=$data['paragraph_detail'][$section];
		$res = $this -> pub_add_feature_graphic($paragraph_detail,$feature_id);
		if($res['code'] == 1){
			$new_data['content']=$res['msg'];
		}else{
			$this->error($res['msg']);
		}
		//段落软件
		$paragraph_soft=$data['paragraph_soft'][$section];
		$new_data['is_soft']=$paragraph_soft;
		if($paragraph_soft==1)
		{
			$soft_type=$data['soft_type'][$section];
			$new_data['soft_type']=$soft_type;
			//下载按钮  通用
			$download_frame_color=$data['download_frame_color'][$section];
			$download_button_color=$data['download_button_color'][$section];
			$download_font_color=$data['download_font_color'][$section];
			$soft_download_arr=array(
				'download_frame_color'=>$download_frame_color,
				'download_button_color'=>$download_button_color,
				'download_font_color'=>$download_font_color,
			);
			$new_data['soft_download_content']=json_encode($soft_download_arr);
				
			if($soft_type==1)//单排样式1
			{
				//软件信息样色配置
				$soft_info_color=$data['soft_info_color'][$section];
				$new_data['soft_info_color']=$soft_info_color;
			}else if($soft_type==2)//单排样式2
			{
				//推荐信息
				$soft_recommend_info=$data['soft_recommend_info'][$section];
				$soft_recommend_info_color=$data['soft_recommend_info_color'][$section];
				$soft_recommend_info_arr=array(
					'soft_recommend_info'=>$soft_recommend_info,
					'soft_recommend_info_color'=>$soft_recommend_info_color,
				);
				$new_data['soft_recommend_info_content']=json_encode($soft_recommend_info_arr);
				//推荐人群
				$soft_recommend_people=$data['soft_recommend_people'][$section];
				$soft_recommend_people_color=$data['soft_recommend_people_color'][$section];
				$soft_recommend_people_arr=array(
					'soft_recommend_people'=>$soft_recommend_people,
					'soft_recommend_people_color'=>$soft_recommend_people_color,
				);
				$new_data['soft_recommend_people_content']=json_encode($soft_recommend_people_arr);
				//软件标签样式
				$soft_label_type=$data['soft_label_type'][$section];
				$new_data['soft_label_type']=$soft_label_type;
				//标签显示的配置内容
				$new_data['soft_label_pre_words']=$data['soft_label_pre_words'][$section];
			}else if($soft_type==3)//双排样式
			{
				//软件信息样色配置
				$soft_info_color=$data['soft_info_color'][$section];
				$new_data['soft_info_color']=$soft_info_color;
				//推荐信息
				$soft_recommend_info=$data['soft_recommend_info'][$section];
				$soft_recommend_info_color=$data['soft_recommend_info_color'][$section];
				$soft_recommend_info_arr=array(
					'soft_recommend_info'=>$soft_recommend_info,
					'soft_recommend_info_color'=>$soft_recommend_info_color,
				);
				$new_data['soft_recommend_info_content']=json_encode($soft_recommend_info_arr);
			}else if($soft_type==4)//特殊样式1
			{
				//软件1-3背景
				$soft1_bg_color=$data['soft1_bg_color'][$section];
				$soft2_bg_color=$data['soft2_bg_color'][$section];
				$soft3_bg_color=$data['soft3_bg_color'][$section];
				$soft_bg_color_arr=array(
					'soft1_bg_color'=>$soft1_bg_color,
					'soft2_bg_color'=>$soft2_bg_color,
					'soft3_bg_color'=>$soft3_bg_color,
				);
				$new_data['soft_all_bg']=json_encode($soft_bg_color_arr);	
			}else if($soft_type==5)//特殊样式2
			{ 
				//推荐信息
				$soft_recommend_info=$data['soft_recommend_info'][$section];
				$soft_recommend_info_color=$data['soft_recommend_info_color'][$section];
				$soft_recommend_info_arr=array(
					'soft_recommend_info'=>$soft_recommend_info,
					'soft_recommend_info_color'=>$soft_recommend_info_color,
				);
				$new_data['soft_recommend_info_content']=json_encode($soft_recommend_info_arr);
				//软件背景
				$soft_special2_bg_color=$data['soft_special2_bg_color'][$section];
				$new_data['soft_all_bg']=$soft_special2_bg_color;
			}
			//单排样式1、单排样式2、双排样式都有分割线 
			if($soft_type==1||$soft_type==2||$soft_type==3)
			{
				//分割线
				$soft_split_line_type=$data['soft_split_line_type'][$section];
				$new_data['soft_split_line_type']=$soft_split_line_type;
				if($soft_split_line_type==3)
				{
					$soft_split_line_custom_imgs=$file['soft_split_line_custom_imgs']['size'][$section];
					if($soft_split_line_custom_imgs)
					{
						include_once SERVER_ROOT. '/tools/functions.php';
						//上传图片
						$soft_split_line_custom_img = preg_match("/\.(jpg|png|gif)$/", $file['soft_split_line_custom_imgs']['name'][$section],$match_soft_split_line_custom_img);
						$soft_split_line_custom_img = $match_soft_split_line_custom_img[0];
						$folder = "/img/" . date("Ym/d/");
						$this->mkDirs(UPLOAD_PATH . $folder);
						$soft_split_line_custom_img_path = $folder . time() . '_soft_split_line_custom_img_'. rand(1000,9999) . $soft_split_line_custom_img;
						$soft_split_line_custom_img_path_last = UPLOAD_PATH . $soft_split_line_custom_img_path;
						$ret = move_uploaded_file($file['soft_split_line_custom_imgs']['tmp_name'][$section], $soft_split_line_custom_img_path_last);
						$soft_split_line_custom_imgs_new = $soft_split_line_custom_img_path;
					}
					else
					{
						$soft_split_line_custom_pic_hidden=$data['soft_split_line_custom_pic_hidden'][$section];
						if($soft_split_line_custom_pic_hidden)
						{
							$soft_split_line_custom_imgs_new=$soft_split_line_custom_pic_hidden;
						}
						else{
							$soft_split_line_custom_imgs_new="";
						}
					}
					$new_data['soft_split_line_custom_pic'] = $soft_split_line_custom_imgs_new;
				}
			}
			//特殊样式1、特殊样式2都有标题、摘要、推荐信息背景和一键下载图片
			if($soft_type==4||$soft_type==5)
			{
				//软件标题
				$soft_title=$data['soft_title'][$section];
				$soft_title_size=$data['soft_title_size'][$section];
				$soft_title_color=$data['soft_title_color'][$section];
				$soft_title_info_arr=array(
					'soft_title'=>$soft_title,
					'soft_title_size'=>$soft_title_size,
					'soft_title_color'=>$soft_title_color,
				);
				$new_data['soft_title_content']=json_encode($soft_title_info_arr);
				//软件摘要
				$soft_abstract=$data['soft_abstract'][$section];
				$soft_abstract_size=$data['soft_abstract_size'][$section];
				$soft_abstract_color=$data['soft_abstract_color'][$section];
				$soft_abstract_info_arr=array(
					'soft_abstract'=>$soft_abstract,
					'soft_abstract_size'=>$soft_abstract_size,
					'soft_abstract_color'=>$soft_abstract_color,
				);
				$new_data['soft_abstract_content']=json_encode($soft_abstract_info_arr);
				//推荐信息背景颜色配置
				$soft_recomment_bg_color=$data['soft_recomment_bg_color'][$section];
				$new_data['soft_recommend_info_bg']=$soft_recomment_bg_color;
				//一键下载图片上传
				$soft_specila_one_download_pic=$file['soft_specila_one_download_pic']['size'][$section];
				if($soft_specila_one_download_pic)
				{
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$soft_specila_one_download_pic = preg_match("/\.(jpg|png|gif)$/", $file['soft_specila_one_download_pic']['name'][$section],$match_soft_specila_one_download_pic);
					$soft_specila_one_download_pic = $match_soft_specila_one_download_pic[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$soft_specila_one_download_pic_path = $folder . time() . '_soft_specila_one_download_pic_'. rand(1000,9999) . $soft_specila_one_download_pic;
					$soft_specila_one_download_pic_path_last = UPLOAD_PATH . $soft_specila_one_download_pic_path;
					$ret = move_uploaded_file($file['soft_specila_one_download_pic']['tmp_name'][$section], $soft_specila_one_download_pic_path_last);
					$soft_specila_one_download_pic_new = $soft_specila_one_download_pic_path;
				}
				else
				{
					$soft_specila_one_download_pic_hidden=$data['soft_specila_one_download_pic_hidden'][$section];
					if($soft_specila_one_download_pic_hidden)
					{
						$soft_specila_one_download_pic_new=$soft_specila_one_download_pic_hidden;
					}
					else{
						$soft_specila_one_download_pic_new="";
					}
				}
				$new_data['soft_specila_one_download_pic '] = $soft_specila_one_download_pic_new;
			}
		}
		//段落推荐
		$paragraph_recommend=$data['paragraph_recommend'][$section];
		$new_data['is_recommend ']=$paragraph_recommend;
		if($paragraph_recommend==1)
		{
			$recommend_type=$data['recommend_type'][$section];
			$new_data['recommend_type']=$recommend_type;
			$recommend_param_arr=array();
			if($recommend_type==1)
			{
				//图片 以及图片大小的比对
				$recommend_fixed_img=$file['recommend_fixed_img']['size'][$section];
				if($recommend_fixed_img)
				{
					$recommend_fixed_img_detail = getimagesize($file['recommend_fixed_img']['tmp_name'][$section]);
					if($recommend_fixed_img_detail[0] != $this->header_single_img_width)
					{
						$this -> error("专题段落推荐:请上传宽度为{$this->header_single_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$recommend_fixed_img_suffix = preg_match("/\.(jpg|png|gif)$/", $file['recommend_fixed_img']['name'][$section],$match_recommend_fixed_img_suffix);
					$recommend_fixed_img_suffix = $match_recommend_fixed_img_suffix[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$recommend_fixed_img_path = $folder . time() . '_recommend_fixed_img_'. rand(1000,9999) . $recommend_fixed_img_suffix;
					$recommend_fixed_img_path_last = UPLOAD_PATH . $recommend_fixed_img_path;
					$ret = move_uploaded_file($file['recommend_fixed_img']['tmp_name'][$section], $recommend_fixed_img_path_last);
					$recommend_img = $recommend_fixed_img_path;
				}
				else
				{
					$recommend_fixed_img_hidden=$data['recommend_fixed_img_hidden'][$section];
					if($recommend_fixed_img_hidden)//编辑 图片已经存在
					{
						$recommend_img=$recommend_fixed_img_hidden;
					}
					else
					{
						$recommend_img="";
					}
				}
				//推荐内容处理 合并 recommend_fix1_section_1]
				$recommend_content_key = 'recommend_fix1_'.$section;
				if($data['content_type'][$recommend_content_key])
				{
					$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$recommend_content_key);
					if($rcontent!==true)
					{
						$this -> error($rcontent);
					}
					else
					{
						if($data['recommend_fixed_img_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$recommend_jump_where = array(
								'id' => $data['recommend_fixed_img_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($recommend_jump_where)->save($map);
							$recommend_common_jump_id=$data['recommend_fixed_img_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$recommend_common_jump_id = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$recommend_common_jump_id ="";
				}
				//保存图片和推荐内容id的json
				$recommend_param_arr=array(
					'recommend_img1' =>$recommend_img,
					'recommend_common_jump_id1'=>$recommend_common_jump_id,
				);
			}elseif($recommend_type==2)
			{
				//图片 以及图片大小的比对
				$recommend_fixed_img1=$file['recommend_fixed_img1']['size'][$section];
				$recommend_fixed_img2=$file['recommend_fixed_img2']['size'][$section];
				if($recommend_fixed_img1)
				{
					$recommend_fixed_img1_detail = getimagesize($file['recommend_fixed_img1']['tmp_name'][$section]);
					if($recommend_fixed_img1_detail[0] != $this->header_multiple_img_width)
					{
						$this -> error("专题段落推荐:请上传宽度为{$this->header_multiple_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$recommend_fixed_img1_suffix = preg_match("/\.(jpg|png|gif)$/", $file['recommend_fixed_img1']['name'][$section],$match_recommend_fixed_img1_suffix);
					$recommend_fixed_img1_suffix = $match_recommend_fixed_img1_suffix[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$recommend_fixed_img1_path = $folder . time() . '_recommend_fixed_img1_'. rand(1000,9999) . $recommend_fixed_img1_suffix;
					$recommend_fixed_img1_path_last = UPLOAD_PATH . $recommend_fixed_img1_path;
					$ret = move_uploaded_file($file['recommend_fixed_img1']['tmp_name'][$section], $recommend_fixed_img1_path_last);
					$recommend_img1 = $recommend_fixed_img1_path;
				}
				else
				{
					$recommend_fixed_img1_hidden=$data['recommend_fixed_img1_hidden'][$section];
					if($recommend_fixed_img1_hidden)//编辑 图片已经存在
					{
						$recommend_img1=$recommend_fixed_img1_hidden;
					}
					else
					{
						$recommend_img1="";
					}
				}
				//推荐内容处理 合并 
				$recommend_content_key1 = 'recommend_fix2_'.$section.'_1';
				if($data['content_type'][$recommend_content_key1])
				{
					$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$recommend_content_key1);
					if($rcontent!==true)
					{
						$this -> error($rcontent);
					}
					else
					{
						if($data['recommend_fixed_img1_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$recommend_jump_where1 = array(
								'id' => $data['recommend_fixed_img1_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($recommend_jump_where1)->save($map);
							$recommend_common_jump_id1=$data['recommend_fixed_img1_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$recommend_common_jump_id1 = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$recommend_common_jump_id1 ="";
				}
				if($recommend_fixed_img2)
				{
					$recommend_fixed_img2_detail = getimagesize($file['recommend_fixed_img2']['tmp_name'][$section]);
					if($recommend_fixed_img2_detail[0] != $this->header_multiple_img_width)
					{
						$this -> error("专题段落推荐:请上传宽度为{$this->header_multiple_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$recommend_fixed_img2_suffix = preg_match("/\.(jpg|png|gif)$/", $file['recommend_fixed_img2']['name'][$section],$match_recommend_fixed_img2_suffix);
					$recommend_fixed_img2_suffix = $match_recommend_fixed_img2_suffix[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$recommend_fixed_img2_path = $folder . time() . '_recommend_fixed_img2_'. rand(1000,9999) . $recommend_fixed_img2_suffix;
					$recommend_fixed_img2_path_last = UPLOAD_PATH . $recommend_fixed_img2_path;
					$ret = move_uploaded_file($file['recommend_fixed_img2']['tmp_name'][$section], $recommend_fixed_img2_path_last);
					$recommend_img2 = $recommend_fixed_img2_path;
				}
				else
				{
					$recommend_fixed_img2_hidden=$data['recommend_fixed_img2_hidden'][$section];
					if($recommend_fixed_img2_hidden)//编辑 图片已经存在
					{
						$recommend_img2=$recommend_fixed_img2_hidden;
					}
					else
					{
						$recommend_img2="";
					}
				}
				//推荐内容处理 合并 
				$recommend_content_key2 = 'recommend_fix2_'.$section.'_2';
				if($data['content_type'][$recommend_content_key2])
				{
					$rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$recommend_content_key2);
					if($rcontent!==true)
					{
						$this -> error($rcontent);
					}
					else
					{
						if($data['recommend_fixed_img2_jump_id'][$section])//说明是编辑推荐内容
						{
							$map['update_at']=time();
							$recommend_jump_where2 = array(
								'id' => $data['recommend_fixed_img2_jump_id'][$section],			
							);
							$model -> table('sj_common_jump')->where($recommend_jump_where2)->save($map);
							$recommend_common_jump_id2=$data['recommend_fixed_img2_jump_id'][$section];
						}
						else  //添加
						{
							//保存数据
							$map['create_at']=time();
							$map['update_at']=time();
							$recommend_common_jump_id2 = $model -> table('sj_common_jump')->add($map);
						}	
					}
				}
				else
				{
					$recommend_common_jump_id2 ="";
				}
				
				//保存图片和推荐内容id的json
				$recommend_param_arr=array(
					'recommend_img1' =>$recommend_img1,
					'recommend_common_jump_id1'=>$recommend_common_jump_id1,
					'recommend_img2' =>$recommend_img2,
					'recommend_common_jump_id2'=>$recommend_common_jump_id2,
				);
			}elseif($recommend_type==3)
			{
				//图片 以及图片大小的比对
				//$recommend_count1=0;
				//$recommend_count2=0;
				$j=0;
				foreach($file['recommend_scroll_img']['size'] as $key=>$val)
				{
					$recommend_pos = strpos($key,'recommend_scroll_'.$section.'_');
					if($recommend_pos==0&&$recommend_pos!==false)//说明是同一段的图片
					{
						if(!$val&&!$data['recommend_scroll_img_hidden'][$key])
						{
							continue;
						}
						else
						{
							//$recommend_count1++;
							$j++;
							$recommend_scroll_img="scroll_img".$j;
							$recommend_common_jump_id = "common_jump_id".$j;
					
							//$recommend_scroll_pic_key = 'recommend_scroll_'.$section.'_'.$j;
							$recommend_scroll_pic_key = $key;
							$recommend_img=$file['recommend_scroll_img']['size'][$recommend_scroll_pic_key];
							if($recommend_img)
							{
								$recommend_scroll_img_detail= getimagesize($file['recommend_scroll_img']['tmp_name'][$recommend_scroll_pic_key]);
								
								if($recommend_scroll_img_detail[0] != $this->header_single_img_width)
								{
									$this -> error("专题段落推荐:请上传宽度为{$this->header_single_img_width}的图片");
								}
								
								include_once SERVER_ROOT. '/tools/functions.php';
								//上传图片
								$recommend_scroll_img_suffix = preg_match("/\.(jpg|png|gif)$/", $file['recommend_scroll_img']['name'][$recommend_scroll_pic_key],$match_recommend_scroll_img_suffix);
								$recommend_scroll_img_suffix = $match_recommend_scroll_img_suffix[0];
								$folder = "/img/" . date("Ym/d/");
								$this->mkDirs(UPLOAD_PATH . $folder);
								$recommend_scroll_img_path = $folder . time() . '_recommend_scroll_img_'. rand(1000,9999) . $recommend_scroll_img_suffix;
								$recommend_scroll_img_path_last = UPLOAD_PATH . $recommend_scroll_img_path;
								$ret = move_uploaded_file($file['recommend_scroll_img']['tmp_name'][$recommend_scroll_pic_key], $recommend_scroll_img_path_last);
								$$recommend_scroll_img = $recommend_scroll_img_path;
							}
							else
							{
								$recommend_scroll_img_hidden=$data['recommend_scroll_img_hidden'][$recommend_scroll_pic_key];
								if($recommend_scroll_img_hidden)//编辑 图片已经存在
								{
									$$recommend_scroll_img=$recommend_scroll_img_hidden;
								}
								else
								{
									$$recommend_scroll_img= '';
								}
							}
							//推荐内容处理 合并 content_type[recommend_scroll_section_1_1]
							//$recomend_content_key = 'recommend_scroll_'.$section.'_'.$j;
							$recomend_content_key = $key;
							if($data['content_type'][$recomend_content_key])
							{
								$recommend_rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$recomend_content_key);
								if($recommend_rcontent!==true)
								{
									$this -> error($recommend_rcontent);
								}
								else
								{
									if($data['recommend_scroll_img_jump_id'][$recomend_content_key])//说明是编辑推荐内容
									{
										$map['update_at']=time();
										$recommend_jump_where = array(
											'id' => $data['recommend_scroll_img_jump_id'][$recomend_content_key],			
										);
										$model -> table('sj_common_jump')->where($recommend_jump_where)->save($map);
										$$recommend_common_jump_id=$data['recommend_scroll_img_jump_id'][$recomend_content_key];
									}
									else  //添加
									{
										//保存数据
										$map['create_at']=time();
										$map['update_at']=time();
										$$recommend_common_jump_id = $model -> table('sj_common_jump')->add($map);
									}	
								}
							}
							else
							{
								$$recommend_common_jump_id ="";
							}
							$recommend_param_arr['recommend_img'.$j]=$$recommend_scroll_img;
							$recommend_param_arr['recommend_common_jump_id'.$j]=$$recommend_common_jump_id;
						}
					}
				}
			
			}elseif($recommend_type==4)
			{
				//图片 以及图片大小的比对
				//$recommend_count1=0;
				//$recommend_count2=0;
				$j=0;
				foreach($file['recommend_scroll_img_multiple']['size'] as $key=>$val)
				{
					$recommend_pos = strpos($key,'recommend_scroll_multiple_'.$section.'_');
					if($recommend_pos==0&&$recommend_pos!==false)//说明是同一段的图片
					{
						if(!$val&&!$data['recommend_scroll_img_multiple_hidden'][$key])
						{
							continue;
						}
						else
						{
							//$recommend_count1++;
							$j++;
							$recommend_scroll_img="scroll_img".$j;
							$recommend_common_jump_id = "common_jump_id".$j;
							
							//$recommend_scroll_pic_key = 'recommend_scroll_'.$section.'_'.$j;
							$recommend_scroll_pic_key = $key;
							$recommend_img=$file['recommend_scroll_img_multiple']['size'][$recommend_scroll_pic_key];
							if($recommend_img)
							{
								$recommend_scroll_img_detail= getimagesize($file['recommend_scroll_img_multiple']['tmp_name'][$recommend_scroll_pic_key]);
								
								if($recommend_scroll_img_detail[0] != $this->header_multiple_img_width)
								{
									$this -> error("专题段落推荐:请上传宽度为{$this->header_multiple_img_width}的图片");
								}
								
								include_once SERVER_ROOT. '/tools/functions.php';
								//上传图片
								$recommend_scroll_img_suffix = preg_match("/\.(jpg|png|gif)$/", $file['recommend_scroll_img_multiple']['name'][$recommend_scroll_pic_key],$match_recommend_scroll_img_suffix);
								$recommend_scroll_img_suffix = $match_recommend_scroll_img_suffix[0];
								$folder = "/img/" . date("Ym/d/");
								$this->mkDirs(UPLOAD_PATH . $folder);
								$recommend_scroll_img_path = $folder . time() . '_recommend_scroll_img_'. rand(1000,9999) . $recommend_scroll_img_suffix;
								$recommend_scroll_img_path_last = UPLOAD_PATH . $recommend_scroll_img_path;
								$ret = move_uploaded_file($file['recommend_scroll_img_multiple']['tmp_name'][$recommend_scroll_pic_key], $recommend_scroll_img_path_last);
								$$recommend_scroll_img = $recommend_scroll_img_path;
							}
							else
							{
								$recommend_scroll_img_multiple_hidden=$data['recommend_scroll_img_multiple_hidden'][$recommend_scroll_pic_key];
								if($recommend_scroll_img_multiple_hidden)//编辑 图片已经存在
								{
									$$recommend_scroll_img=$recommend_scroll_img_multiple_hidden;
								}
								else
								{
									$$recommend_scroll_img= '';
								}
							}
							//推荐内容处理 合并 content_type[recommend_scroll_section_1_1]
							//$recomend_content_key = 'recommend_scroll_multiple_'.$section.'_'.$j;
							$recomend_content_key = $key;
							if($data['content_type'][$recomend_content_key])
							{
								$recommend_rcontent=ContentTypeModel::saveRecommendContent_new($data,'',$map,$recomend_content_key);
								if($recommend_rcontent!==true)
								{
									$this -> error($recommend_rcontent);
								}
								else
								{
									if($data['recommend_scroll_img_multiple_jump_id'][$recomend_content_key])//说明是编辑推荐内容
									{
										$map['update_at']=time();
										$recommend_jump_where = array(
											'id' => $data['recommend_scroll_img_multiple_jump_id'][$recomend_content_key],			
										);
										$model -> table('sj_common_jump')->where($recommend_jump_where)->save($map);
										$$recommend_common_jump_id=$data['recommend_scroll_img_multiple_jump_id'][$recomend_content_key];
									}
									else  //添加
									{
										//保存数据
										$map['create_at']=time();
										$map['update_at']=time();
										$$recommend_common_jump_id = $model -> table('sj_common_jump')->add($map);
									}	
								}
							}
							else
							{
								$$recommend_common_jump_id ="";
							}
							$recommend_param_arr['recommend_img'.$j]=$$recommend_scroll_img;
							$recommend_param_arr['recommend_common_jump_id'.$j]=$$recommend_common_jump_id;
						}
					}
				}
			}
			$new_data['recommend_content'] = json_encode($recommend_param_arr);
		}
		//段落结尾
		$paragraph_ending=$data['paragraph_ending'][$section];
		$new_data['is_ending']=$paragraph_ending;
		if($paragraph_ending==1)
		{
			$ending_type=$data['ending_type'][$section];
			$new_data['ending_type']=$ending_type;
			if($ending_type==4)//自定义图片
			{
				//图片 
				$ending_custom_imgs=$file['ending_custom_imgs']['size'][$section];
				if($ending_custom_imgs)
				{
					$ending_custom_imgs_detail = getimagesize($file['ending_custom_imgs']['tmp_name'][$section]);
					if($ending_custom_imgs_detail[0] != $this->top_end_img_width)
					{
						$this -> error("专题段落结尾:请上传宽度为{$this->top_end_img_width}的图片");
					}
					include_once SERVER_ROOT. '/tools/functions.php';
					//上传图片
					$ending_custom_img = preg_match("/\.(jpg|png|gif)$/", $file['ending_custom_imgs']['name'][$section],$match_ending_custom_img);
					$ending_custom_img = $match_ending_custom_img[0];
					$folder = "/img/" . date("Ym/d/");
					$this->mkDirs(UPLOAD_PATH . $folder);
					$ending_custom_img_path = $folder . time() . '_ending_custom_img_'. rand(1000,9999) . $ending_custom_img;
					$ending_custom_img_path_last = UPLOAD_PATH . $ending_custom_img_path;
					$ret = move_uploaded_file($file['ending_custom_imgs']['tmp_name'][$section], $ending_custom_img_path_last);
					$ending_custom_imgs_new = $ending_custom_img_path;
				}
				else
				{
					$ending_content_hidden=$data['ending_content_hidden'][$section];
					if($ending_content_hidden)
					{
						$ending_custom_imgs_new=$ending_content_hidden;
					}
					else{
						$ending_custom_imgs_new="";
					}
				}
				$new_data['ending_content']=$ending_custom_imgs_new;
			}
		}
		return $new_data;
	}

	function initval_check(){
		//点赞数都是大于0的整数
		$matches="/^[1-9]\d*$/";
		if($_POST['init_val']){
			if(!preg_match($matches,$_POST['init_val'])){
				$this->error("点赞数的初始值为正整数");
			}else{
				$this->conf_list['init_val'] = $_POST['init_val'];
			}
		}else{
			$this->error("点赞数的初始值不能为空");
		}

		if($_POST['random_start']){
			if(!preg_match($matches,$_POST['random_start'])){
				$this->error("点赞数的随机开始值为正整数");
			}else{
				$this->conf_list['random_start'] = $_POST['random_start'];
			}
		}else{
			$this->error("点赞数的随机开始值不能为空");
		}

		if($_POST['random_end']){
			if(!preg_match($matches,$_POST['random_end'])){
				$this->error("点赞数的随机结束值为正整数");
			}else{
				$this->conf_list['random_end'] = $_POST['random_end'];
			}
		}else{
			$this->error("点赞数的随机结束值不能为空");
		}
	}
}
?>

