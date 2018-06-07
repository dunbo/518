<?php 

Class TagAction extends CommonAction{
	private $model_te;
	private $img_url = IMGATT_HOST;
    
	public function _initialize() {
        parent::_initialize();
        $this->model_te = D('Zhiyoo.TagsExt');
    }
    
    //老的标签列表，废弃不用
	function taglist(){
		$model = D('Zhiyoo.Zhiyoo');
		$sql = $_GET['idorder'] == 'desc' ? 'status desc,tagid desc' : 'status desc,tagid asc';
		if(isset($_GET['nameorder'])) $sql = $_GET['nameorder'] == 'asc' ? 'tagname asc' : 'tagname desc';
		if(isset($_GET['storder'])) $sql = $_GET['storder'] == 'desc' ? 'status desc , tagid ASC' : 'status asc , tagid ASC';
		if(isset($_GET['rank'])) $sql = $_GET['rank'] == 'desc' ? 'rank desc' : 'rank asc';
        
        $groupsql = array('status'=>1);
		if(isset($_GET['groupid'])) $groupsql['groupid'] = $_GET['groupid'];
		
		$firstgroup = $model ->table("zy_tagsgroup")->where($groupsql)->order('groupid asc')->select();//读取一级目录，按照优先级升序排列
        if(empty($firstgroup)) $this->error("分组错误");
		foreach($firstgroup as $result1){
			$result[$result1['groupid']]['name'] = $result1['groupname'];//分类名称
			$result[$result1['groupid']]['groupid'] = $result1['groupid'];
            $wheresql = array(
                        'status' => array('neq',-1),
                        'parentid' => 0,
                        'group' => $result1['groupid']
                        );
			$count = $model -> table("zy_tags")->where($wheresql)->count();//读取目录下标签总数
			//var_dump($tags);
			$result[$result1['groupid']]['count'] = $count;//标签总数
			$tags = $model -> table("zy_tags")->where($wheresql)->order($sql)->select();//读取目录下标签
			foreach($tags as $key => $result3){//读取一级标签内容总数
				//内容个数计数 采集池+线上内容（包括复制内容）
				$tagres = $model ->table("zy_idtotagid")->where(array('tagid'=>$result3['tagid']))->field('id')->select();
				$tagarr = array();
				foreach($tagres as $val){
					$tagarr[] = $val['id'];
				}
				$where_sql = array('c.id'=>array('in',$tagarr),'c.status'=>1);	
				$pcount = $model -> table('zy_collect c') ->join("left join zy_collect_ext e on c.id=e.colid")-> where($where_sql)->count();
                $wheresql = array('status' => array('neq',-1),'parentid' => $result3['tagid']);
				$downcount = $model -> table('zy_tags')-> where($wheresql)->count();
				$tags[$key]['pcount'] = $pcount;//内容个数计数
				$tags[$key]['downcount'] = $downcount;//子标签个数计数
                $ext = $this->model_te->where(array('status'=> 1,'tagid'=>$result3['tagid']))->find();
                if(empty($ext['img_path'])){
                    $tags[$key]['img_path'] = '';
                }else{
                    $tags[$key]['img_path'] = $this->img_url.$ext['img_path'];
                }
			}
            if(isset($_GET['corder'])){
                $pcount = $tagid = $tagname = $parentid = $group = $status = $downcount = $t_rank = array();
                foreach ($tags as $key => $row) 
                {
                    $tagid[$key] = $row['tagid'];
                    $tagname[$key] = $row['tagname'];
                    $parentid[$key] = $row['parentid'];
                    $group[$key] = $row['group'];
                    $status[$key] = $row['status'];
                    $pcount[$key] = $row['pcount'];
                    $downcount[$key] = $row['downcount'];
                    $img_path[$key] = $row['img_path'];
                    $t_rank[$key] = $row['rank'];
                }
                if($_GET['corder'] == 'asc'){
                    array_multisort($pcount, SORT_ASC, SORT_REGULAR, $tagid, $tagname, $parentid, $group, $status,$downcount,$t_rank);
                }else{
                    array_multisort($pcount, SORT_DESC, SORT_REGULAR, $tagid, $tagname, $parentid, $group, $status,$downcount,$t_rank);
                }
                $tags = array();
                $count = count($pcount);
                for($i=0;$i<$count;$i++){
                    $tags[$i]['tagid'] = $tagid[$i];
                    $tags[$i]['tagname'] = $tagname[$i];
                    $tags[$i]['parentid'] = $parentid[$i];
                    $tags[$i]['group'] = $group[$i];
                    $tags[$i]['status'] = $status[$i];
                    $tags[$i]['pcount'] = $pcount[$i];
                    $tags[$i]['downcount'] = $downcount[$i];
                    $tags[$i]['img_path'] = $img_path[$i];
                    $tags[$i]['rank'] = $t_rank[$i];
                }
            }
                
			$count = $pcount = $tagid = $tagname = $parentid = $group = $status = $downcount = null;
			$result[$result1['groupid']]['tags'] = $tags;//标签列表
		}
		
		$this -> assign('idorder',$_GET['idorder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('nameorder',$_GET['nameorder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('storder',$_GET['storder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('corder',$_GET['corder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('rankorder',$_GET['rank'] == 'asc' ? 'desc' : 'asc');
		
		$this -> assign('list',$result);
		$this -> display();
	}
	
	function edit(){
		$action = $_GET['action'];
		$tid = $_GET['tagid'];
		$model = D('Zhiyoo.Zhiyoo');
		if($action == 'edit'){
			$ntag = $model -> table("zy_tags")->where(array('tagid'=>$tid))->find();//当前标签
            $where = array('status'=>1,'group'=>$ntag['group']);
            $ntag['parentid'] != 0 && $where['parentid'] = $ntag['parentid'];
			$ngroup = $model -> table("zy_tags")->where($where)->select();//当前标签所在分类
			$nosectags = $model -> query("select * from zy_tags where status=1 and parentid=0 and `group`='{$ntag['group']}' and tagid not in (select parentid from zy_tags where status!=-1 and parentid!=0 and `group`='{$ntag['group']}')");//当前分类下所有没有子标签的标签
			$ischild = $model -> table("zy_tags")->where(array('status'=>array('neq',-1),'parentid'=>$tid))->find();//当前标签是否有子标签
			$group = $model ->table("zy_tagsgroup")->where('status=1')->order('groupid ASC')->select();
			foreach($group as $key => $val){
				$groupid = $val['groupid'];
				$tag = $model -> table("zy_tags")->where(array('status'=>1,'group'=>$groupid))->order('tagid ASC')->select();
				$group[$key]['tag']=$tag;
			}//var_dump($ischild);
            $ext = $this->model_te->where(array('status'=>1,'tagid'=>$tid))->find();
            if(empty($ext['img_path'])){
                $ntag['img_path'] = '';
            }else{
                $ntag['img_path'] = $this->img_url.$ext['img_path'];
            }
			$this -> assign('ntag',$ntag);
			$this -> assign('ischild',$ischild);
			$this -> assign('nosectags',$nosectags);
			$this -> assign('ngroup',$ngroup);
			$this -> assign('list',$group);
			$this -> display();
		}elseif($action == 'del'){
			$result = $model ->table("zy_tags")->where(array('parentid'=>$tid))->select();//查看是否有子标签
            $alltid = array();
			if(!empty($result)){
				foreach($result as $val){
					$alltid[] = $val['tagid'];
				}
			}
            $alltid[] = $tid;
			foreach($alltid as $tid){//遍历执行ID删除
                $result = $model-> table("zy_tags")->where(array('tagid'=>$tid))->save(array('status'=>-1));//更新标签状态
                $result = $this->model_te->where(array('status'=>1,'tagid'=>$tid))->save(array('status'=>-1));//更新EXT状态
                $id = $model ->table("zy_idtotagid")->where(array('tagid'=>$tid))->getField('id');//查询所属标签内容
                $idstr  = array();
                foreach($id as $val){
                    $idstr[] = $val['id'];
                }
                //素材标签操作
                $ct_rule=$model ->table("zy_id_tagstr")->where(array('id'=>array('in',$idstr)))->select();//查询已打上该标签内容的所有内容
                foreach($ct_rule as $val){
                    $array = explode('_',$val['tagstr']);//分解得到内容打上的标签
                    $key = array_search($tid,$array);//返回需要删除的tagid键名
                    unset($array[$key]);
                    $newrule = implode('_',$array);
                    $model->table("zy_id_tagstr")->where(array('id'=>$val['id']))->save(array('tagstr'=>$newrule));
                }
                //栏目标签 操作
                $this -> del_column_tag($tid);
                
                $model ->table("zy_column_content")->where(array('id'=>array('in',$idstr)))->delete();
                $model ->table("zy_idtotagid")->where(array('tagid'=>$tid))->delete();//删除此标签所有关联内容信息
                $this -> writelog("智友内容管理-内容标签/栏目-标签管理 已删除标签id为{$tid}的标签","zy_idtotagid",$tid,__ACTION__ ,"","del");
			}
			$this->success("删除成功！");
		}elseif($action == 'status'){
			$status = $_GET['status'];
			if($status == '0'){
				$result = $model ->table("zy_tags")->where(array('tagid'=>$tid))->save(array('status'=>0));
				$result = $model ->table("zy_tags")->where(array('status' => array('neq',-1),'parentid'=>$tid))->save(array('status'=>0));
			}
			else{
				$result = $model ->table("zy_tags")->where(array('tagid'=>$tid))->save(array('status'=>1));
			}
			
			$this -> writelog("智友内容管理-内容标签/栏目-标签管理 已修改标签id为{$tid}的标签状态为".$status,"zy_idtotagid",$tid,__ACTION__ ,"","del");
			$this->success("修改成功！");
		}
		elseif($action == 'renamegroup'){
			$groupid = $_GET['groupid'];
			$group = $model->table("zy_tagsgroup")->where(array('groupid'=>$groupid))->find();
			$this->assign("name", $group['groupname']);
			$this -> display('renamegroup');
		}
	}
	
	function doedit(){
		$model = D('Zhiyoo.Zhiyoo');
		$action = $_GET['action'];
		if($action =='tag'){
			$tagid = $_POST['tagid'];
			$newtagid = $_POST['tag'];
			$group = $_POST['group'];
			//重命名
			$name = trim($_POST['name']);
            $result = $model ->table("zy_tags")->where(array('tagname'=>$name,'tagid' => array('neq',$tagid),'status' => array('neq',-1)))->find();
			if(!empty($result)){//是否已存在标签
				// $this->assign("jumpUrl", "/index.php/Zhiyoo/Tag/taglist");
				$this->error("已存在标签，不能重复添加！");
			}
			else{
				$result = $model ->table("zy_tags")->where(array('tagid'=>$tagid))->save(array('tagname'=>$name));
				// 2016-03-18
				$retdata = $model ->table("zy_tags")->where(array('tagid' => $tagid,'status' => array('neq',-1)))->find();
				$imggroupid = $retdata['group'];
				$imgparentid = $retdata['parentid'];
				if ($imggroupid==1 && $imgparentid==0) {
					$imgwidth = 166;
				}elseif($imggroupid==1 && $imgparentid!=0){
					$imgwidth = 64;
				}elseif($imggroupid==57 && $imgparentid==0) {
					$imgwidth = 166;
				}else{
					$imgwidth = 50;
				}
				//2016-03-18 结束
                if($_FILES['icon']['size']>0){
                    $config = array(
                        'tmp_file_dir' => '/tmp/',
                        'width' => $imgwidth,
                        'filesize' => 100000,
                        'real_width' => $imgwidth,
                    );
                    $datedir = '/tagicon/';
                    $savepath = UPLOAD_PATH.$datedir;
                    $imgpath = $this -> _upload($_FILES['icon'],$savepath,$config);
                    $data = array('img_path'=>$datedir.$imgpath);
                    //查询是否已存icon
                    $res = $this->model_te->where(array('status' =>1,'tagid' => $tagid))->select();
                    if(empty($res)){
                        $data['tagid']=$tagid;
                        $result = $this->model_te->add($data);
                    }else{
                        $result = $this->model_te->where(array('status' =>1,'tagid' => $tagid))->save($data);
                    }
                }
			}
			if(isset($_POST['tag']) && isset($_POST['tagid']) && $newtagid !== $tagid){
				//移动标签内容
				$id = $model -> table("zy_idtotagid")-> where(array('tagid'=>$tagid))->select();//获取所属标签所有内容
                $idstr  = array();
                foreach($id as $val){
                    $idstr[] = $val['id'];
                }
                //素材标签操作
                $ct_rule=$model ->table("zy_id_tagstr")->where(array('id'=>array('in',$idstr)))->select();//查询已打上该标签内容的所有内容
				foreach($ct_rule as $val){
					$array = explode('_',$val['tagstr']);//分解得到内容打上的标签
					$key = array_search($tagid,$array);//返回需要更改的tagid键名
					$array[$key]=$newtagid;//替换标签
					$array = array_unique($array);//合并重复标签
					sort($array,SORT_NUMERIC);
					$newrule = implode('_',$array);
					$model -> table("zy_id_tagstr")->where(array('id'=>$val['id']))->save(array('tagstr'=>$newrule));
				}
				//栏目标签操作
				$model -> table("zy_column_content")->where(array('id'=>array('in',$idstr),'source'=>0))->delete();
				$id = $model ->table("zy_idtotagid")-> where(array('tagid'=>$tagid))->select();//原标签对应的id
				$idarray  = array();
				foreach($id as $val){
					$idarray[] = $val['id'];
				}
				$newid = $model ->table("zy_idtotagid")-> where(array('tagid'=>$newtagid))->select();//新标签对应的id
				$newidarray  = array();
				foreach($newid as $val){
					$newidarray[] = $val['id'];
				}
				$id = array_intersect($idarray,$newidarray);//返回两标签共有内容
				$model ->table("zy_idtotagid")->where(array('tagid'=>$tagid,'id'=>array('in',$id)))->delete();//删除重复关联内容信息
				$model ->table("zy_idtotagid")->where(array('tagid'=>$tagid))->save(array('tagid'=>$newtagid));//更新此标签所有关联内容对应的标签信息
			}
			$this -> writelog("智友内容管理-内容标签/栏目-标签管理 已修改标签id为{$tagid}的标签到".$newtagid,"zy_idtotagid",$tagid,__ACTION__ ,"","edit");
		}
		elseif($action =='renamegroup'){
			$groupid = $_POST['groupid'];
			$name = trim($_POST['name']);
			$result = $model ->table("zy_tagsgroup")->where(array('groupid'=>$groupid))->save(array('groupname'=>$name));
			$this -> writelog("智友内容管理-内容标签/栏目-标签管理 已重命名标签分类id为{$groupid}的标签分类为".$name,"zy_tagsgroup",$groupid,__ACTION__ ,"","edit");
		}elseif($action =='delimg'){
			$tagid = $_GET['tagid'];
            $result = $this->model_te->where(array('status'=> 1,'tagid'=>$tagid))->save(array('status'=>-1));
            if($result) {
                $this -> writelog("智友内容管理-内容标签/栏目-标签管理 已删除标签id为{$tagid}的标签图片","zy_tags_ext",$tagid,__ACTION__ ,"","del");
                echo json_encode(array('su'=>1));
            }else{
                echo json_encode(array('su'=>0));
            }
            exit;
		}
		
		
		// $this->assign("jumpUrl", "/index.php/Zhiyoo/Tag/taglist");
		$this->success("修改成功！");
	}
	
	function addtag(){
		$model = D('Zhiyoo.Zhiyoo');
		if($_GET['action'] == 'do'){
			$groupid = $_POST['group'];
			if(empty($groupid)){
				// $this->assign("jumpUrl", "/index.php/Zhiyoo/Tag/taglist");
				$this->error("请选择标签分类");
			}
			$tagid = is_numeric($_POST['tag']) ? $_POST['tag'] : 0;
			$name = trim($_POST['name']);
            $result = $model ->table("zy_tags")->where(array('status' => array('neq',-1),'tagname'=>$name))->find();
			if(!empty($result)){//是否已存在标签
				// $this->assign("jumpUrl", "/index.php/Zhiyoo/Tag/taglist");
				$this->error("已存在标签，不能重复添加！");
			}
			else{
				$data = array('tagname'=>$name,
                              'parentid'=>$tagid,
                              'group'=>$groupid
                );
                $result = $model -> table("zy_tags")->add($data);
                if($groupid==1){
                	$imgwidth = 166;
                }else{
                	$imgwidth = 50;
                }
                if($_FILES['icon']['size']>0){
                    $config = array(
                        'tmp_file_dir' => '/tmp/',
                        'width' => $imgwidth,
                        'filesize' => 100000,
                        'real_width' => $imgwidth,
                    );
                    $datedir = '/tagicon/';
                    $savepath = UPLOAD_PATH.$datedir;
                    $imgpath = $this -> _upload($_FILES['icon'],$savepath,$config);
                    $data = array('tagid'=>$result,
                                  'img_path'=>$datedir.$imgpath,
                    );
                    $result = $this->model_te->add($data);
                }
				if($result !== false)
				{
					$this -> writelog("智友内容管理-内容标签/栏目-标签管理 已添加标签名为{$name}的标签","zy_tags_ext",$result,__ACTION__ ,"","add");
					// $this->assign("jumpUrl", "/index.php/Zhiyoo/Tag/taglist");
					$this->success("添加成功！");
				}
			}
			exit;
			
		}
		if($_GET['action'] == 'sec'){
			$tagid = is_numeric($_GET['tagid']) ? $_GET['tagid'] : 0;
			$group = $model -> table("zy_tags t")->join('LEFT JOIN zy_tagsgroup g ON t.`group`=g.groupid')-> WHERE(array('t.tagid'=>$tagid))->find();
			$this -> assign('group',$group);
			$this -> display('addtagsec');
			exit;
		}
		$group = $model -> table('zy_tagsgroup')-> where('status=1')->order("groupid ASC")->select();//读取一级目录，按照优先级升序排列
		foreach($group as $key => $val){
            $wheresql = array('status' => 1,'parentid' => 0,'group' => $val['groupid']);
			$tag = $model ->table('zy_tags')-> where($wheresql)->order("tagid ASC")->select();
			$group[$key]['tag']=$tag;
		}
		$this -> assign('list',$group);
		$this -> display();
	}
	
	function secondtaglist(){
		$model = D('Zhiyoo.Zhiyoo');
		$tagid = $_GET['tagid'];
		
		if($_GET['type']){
			if($_GET['type'] == 'id'){
				$sql = $_GET['order'] == 'desc' ? 'tagid desc' : 'tagid asc';
			}
			elseif($_GET['type'] == 'name'){
				$sql = $_GET['order'] == 'desc' ? 'tagname desc' : 'tagname asc';
			}
			elseif($_GET['type'] == 'status'){
				$sql = $_GET['order'] == 'desc' ? 'status desc' : 'status asc';
			}elseif($_GET['type'] == 'rank'){
				$sql = $_GET['order'] == 'desc' ? 'rank desc' : 'rank asc';
			}
		}
		else $sql = 'status desc, tagid asc';
        $result = $model ->table('zy_tags')-> where(array('status' => array('neq',-1),'parentid' => $tagid))->count();//内容个数
        $val['count'] = $result;
        $result = $model ->table('zy_tags')-> where(array('tagid' => $tagid))->find();//一级标签
        $val['tag'] = $result;
        $tags = $model ->table('zy_tags')-> where(array('status' => array('neq',-1),'parentid' => $tagid))->order($sql)->select();
			
        foreach($tags as $key => $result3){//读取标签内容总数
            //内容个数计数 采集池+线上内容（包括复制内容）		
            $tagres = $model->table('zy_idtotagid')-> where(array('tagid' => $result3['tagid']))->select();
            $tagarr = array();
            foreach($tagres as $val1){
                $tagarr[] = $val1['id'];
            }
            $where_sql = array('c.id' => array('in',$tagarr),'c.status' => 1);	
            $pcount = $model -> table('zy_collect c') ->join("left join zy_collect_ext e on c.id=e.colid")-> where($where_sql)->count();
            
            $tags[$key]['pcount'] = $pcount;
            $ext = $this->model_te->where(array('status' =>1,'tagid' => $result3['tagid']))->find();
            if(empty($ext['img_path'])){
                $tags[$key]['img_path'] = '';
            }else{
                $tags[$key]['img_path'] = $this->img_url.$ext['img_path'];
            }
        }
		if($_GET['type'] == 'count'){
            $tagid = $tagname = $parentid = $group = $status = $pcount = $img_path = array();
            foreach ($tags as $key => $row) 
            {
                $tagid[$key] = $row['tagid'];
                $tagname[$key] = $row['tagname'];
                $parentid[$key] = $row['parentid'];
                $group[$key] = $row['group'];
                $status[$key] = $row['status'];
                $pcount[$key] = $row['pcount'];
                $img_path[$key] = $row['img_path'];
                $t_rank[$key] = $row['rank'];
            }
			if($_GET['order'] == 'desc'){
                array_multisort($pcount, SORT_DESC, SORT_REGULAR, $tagid, $tagname, $parentid, $group, $status);
			}else{
                array_multisort($pcount, SORT_ASC, SORT_REGULAR, $tagid, $tagname, $parentid, $group, $status);
			}
            $tags = array();
			$count = count($pcount);
			for($i=0;$i<$count;$i++){
				$tags[$i]['tagid'] = $tagid[$i];
				$tags[$i]['tagname'] = $tagname[$i];
				$tags[$i]['parentid'] = $parentid[$i];
				$tags[$i]['group'] = $group[$i];
				$tags[$i]['status'] = $status[$i];
				$tags[$i]['pcount'] = $pcount[$i];
				$tags[$i]['img_path'] = $img_path[$i];
				$tags[$i]['rank'] = $t_rank[$i];
			}
        }
        $order = $_GET['order'] =='desc' ? 'asc' : 'desc';
        $this -> assign('order',$order);
        $this -> assign('val',$val);
        $this -> assign('list',$tags);
        $this -> display();
		
	}
	
	private function del_column_tag($tagid){
		$model = D('Zhiyoo.Zhiyoo');
		//读取该标签组的所有标签
		$result = $model ->table('zy_tags')->where(array('tagid' => $tagid))->find();
		$parentid = $result['parentid'];
		$allid = array();
		$allid[] = $parentid;
		if($parentid){
			$result = $model ->table('zy_tags')->where(array('parentid' => $parentid,'status' => 1))->select();
			foreach($result as $val){
				$allid[] = $val['tagid'];
			}
		}
		//读取所有栏目
		$column_conf = $model -> table('zy_column_conf')->select();;
		foreach($column_conf as $val1){
			$array = explode('_',$val1['rule']);
			$key = array_search($tagid,$array);//返回需要删除的tagid键名
			if($key === false) continue;
			unset($array[$key]);
			$tagcount = array_intersect($array,$allid);//栏目管理里的标签，主标签下是否只存在一个子标签
			if(count($tagcount) ===1){//子标签被删除后=1,即只存在一个主标签
				$key = array_search($parentid,$array);
				unset($array[$key]);//删除主标签
			}//删除子标签
			$newrule = implode('_',$array);
			$model ->table('zy_column_conf')->where(array('cid' => $val1['cid']))->save(array('rule' => $newrule));
		}
	}
	
	public function result_list(){
		// define('BBSLUNTAN_HOST','http://forum.anzhi.com/forum.php?mod=post&action=edit');
		$address = $_SERVER['SERVER_ADDR'] == '192.168.0.99' ? 'forum.anzhi.com' : 'bbs.zhiyoo.com';
		!defined('BBSLUNTAN_HOST') && define('BBSLUNTAN_HOST','http://bbs.zhiyoo.com/forum.php?mod=post&action=edit');
		$order_sql = !$_GET['type']?  'c.addtime desc': "{$_GET['type']} {$_GET['order']},c.addtime desc";
		$th[$_GET['type']] = $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$where_sql = array();
		$where_sql['c.status'] = 1;
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		
		$tagid = $_GET['tagid'];
		$taglist = $bbs_modle->gettags();
		if($tagid) {
			//搜索符合条件的标签
			$tagres = $bbs_modle->table('zy_idtotagid')->where(array('tagid' => $tagid))->select();				
			foreach($tagres as $val){
				$tagarr[] = $val['id'];
			}
			$where_sql['c.id'] =  array('in',$tagarr);	
		}
		
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $bbs_modle -> table('zy_collect c') ->join("left join zy_collect_ext e on c.id=e.colid")-> where($where_sql)->count();
		
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		$result = $bbs_modle -> table('zy_collect c') ->join("left join zy_collect_ext e on c.id=e.colid")-> where($where_sql)-> limit($Page->firstRow . ',' .$Page->listRows)->order($order_sql)->field("c.id,c.tid,c.pid,c.title,c.img_name,c.img_path,c.fid,c.views,c.replies,c.addtime,c.substatus,c.source,e.advid,e.ext_title,e.img_path1,e.img_path2,e.description,e.platform,e.position")-> select();
		
		$presult = $bbs_modle->table('zy_schedule_platform')->where('status=1')->select();
		foreach($presult as $val){
			$platform[$val['platform']] = $val['platformname'];
		}
		$presult = $bbs_modle->table('zy_schedule_position')->where('status=1')->select();
		foreach($presult as $val){
			$position[$val['position']] = $val['positionname'];
		}
		
		foreach($result as $key => $val){
			
			if(!empty($val['advid'])){
				$schtime = $bbs_modle->table('zy_schedule')->where('status=0 and id='.$val['advid'])->field('starttime,endtime')->find();
				$result[$key]['starttime'] = $schtime['starttime'];
				$result[$key]['endtime'] = $schtime['endtime'];
			}
			
			$result[$key]['platformname'] = isset($platform[$val['platform']]) ? $platform[$val['platform']] : '' ;
			$result[$key]['positionname'] = isset($position[$val['position']]) ? $position[$val['position']] : '' ;
			
			if($val['img_path'] != '') {
				if($val['source']==0) $result[$key]['img_path'] = C('bbs_attach_url').$val['img_path'];
			}
			if($result[$key]['img_path1'])$result[$key]['img_path1'] = IMGATT_HOST.'/'.$val['img_path1'];
			if($result[$key]['img_path2'])$result[$key]['img_path2'] = IMGATT_HOST.'/'.$val['img_path2'];
			$result[$key]['advid'] = $val['advid'] > 0 ? $val['advid'] : '';//剔除未排期内容

			$thread_tags = $bbs_modle -> table('zy_idtotagid') -> where("id={$val['id']}") ->select();
			$threadtadid = $tduniquetag = $unsettag = array();
			//取出每个主题的tagid,并且记录下有子标签的父标签tagid
			foreach($thread_tags as $tagidlist){
				$tduniquetag[$tagidlist['tagid']] = $threadtadid[$tagidlist['tagid']] = $tagidlist['tagid'];
				if($taglist[$tagidlist['tagid']]['parentid']>0) $unsettag[] = $taglist[$tagidlist['tagid']]['parentid'];
			}
			//删除有子标签的父标签的tagid，并且把全部的该主题全部的tagid保存成url
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			$tag_result[$val['id']] = array();
			foreach($tduniquetag as $tag){
				$tag_result[$val['id']][$taglist[$tag]['group']]['name'] = $taglist[$tag]['groupname'];	
				$tag_result[$val['id']][$taglist[$tag]['group']]['tag_arr'][] = $tag;
				$tag_result[$val['id']][$taglist[$tag]['group']]['tag_url'] = implode('_',$threadtadid).'_';
			}
			//如果主题没有标签，修改主题的打标签状态为未打标签
			if(empty($thread_tags)) {
				$bbs_modle->table('zy_collect')->where("id={$val['id']}")->save(array('tagstatus'=>0));
				$result[$key]['tagstatus'] = '0';
				}
		} 
		//保留标签功能
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$nowtime = time();
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('taglist',$taglist);
		$this -> assign('nowtime',$nowtime);
		$this -> assign('srcurl',$srcurl);
		$this -> assign('tag_result',$tag_result);
		$this->assign('bbs_model',$bbs_modle);
		$this->assign('address',$address);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign(array('result'=>$result,'th'=>$th));
		$this -> display();
	}
	
	
	protected function _upload($file,$savepath,$config){
		include dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}
	function edit_rank(){
		$bbs_modle = D('Zhiyoo.Zhiyoo');
		if (isset($_POST['level'])){
			$tagsname = (isset($_POST['tagsname'])) ? addslashes(trim($_POST['tagsname'])) : '';
			$ids = '';
			foreach ($_POST['level'] as $k=>$v){
				$v = abs(intval($v));
				$ids .= $k.',';
				$p_ret = $bbs_modle -> query("UPDATE zy_tags SET rank='$v' where  tagid='$k'");
			}
			$jsonarr = '标签分类管理的'.$tagsname.'优先级 tagid:rank'.json_encode($_REQUEST['level']);
			$this -> writelog($jsonarr,"zy_tags",$ids,__ACTION__ ,"","edit");
			$this->success("编辑优先级成功！");
		}
	}
    
    //新的标签分组列表
    function tagCate(){
		$model = D('Zhiyoo.ZhiyooSlave');
        $sql = 'status desc , tagid ASC' ;
        $groupsql = array('status'=>1);
		$firstgroup = $model ->table("zy_tagsgroup")->where($groupsql)->order('groupid asc') -> select();//读取一级目录，按照优先级升序排列
        if(empty($firstgroup)) $this->error("分组错误");
		foreach($firstgroup as $result1){
			$result[$result1['groupid']]['name'] = $result1['groupname'];//分类名称
			$result[$result1['groupid']]['groupid'] = $result1['groupid'];
            $wheresql = array(
                        'status' => array('neq',-1),
                        'parentid' => 0,
                        'group' => $result1['groupid']
                        );
			$tags = $model -> table("zy_tags")->where($wheresql)->order($sql)->select();//读取目录下标签
			$result[$result1['groupid']]['count'] = count($tags);//标签总数
			$result[$result1['groupid']]['tags'] = $tags;//标签列表
		}
		
		$this -> assign('list',$result);
		$this -> display();
        
        
        
    }
    
    //查看每个标签分组的详情
    function showDetail(){
		$model = D('Zhiyoo.ZhiyooSlave');
		$sql = $_GET['idorder'] == 'desc' ? 'status desc,tagid desc' : 'status desc,tagid asc';
		if(isset($_GET['nameorder'])) $sql = $_GET['nameorder'] == 'asc' ? 'tagname asc' : 'tagname desc';
		if(isset($_GET['storder'])) $sql = $_GET['storder'] == 'desc' ? 'status desc , tagid ASC' : 'status asc , tagid ASC';
		if(isset($_GET['rank'])) $sql = $_GET['rank'] == 'desc' ? 'rank desc' : 'rank asc';
        
        if(!$_GET['groupid']){
            $this->error('请选择正确的分组');
        }
        $groupsql['status'] = 1;
		$groupsql['groupid'] = $_GET['groupid'];
		
		$result1 = $model ->table("zy_tagsgroup")->where($groupsql)->find();//读取一级目录，按照优先级升序排列
        if(empty($result1)) $this->error("分组错误");
        $result[$result1['groupid']]['name'] = $result1['groupname'];//分类名称
        $result[$result1['groupid']]['groupid'] = $result1['groupid'];
        $wheresql = array(
                    'status' => array('neq',-1),
                    'parentid' => 0,
                    'group' => $result1['groupid']
                    );
     
        $tags = $model -> table("zy_tags")->where($wheresql)->order($sql)->select();//读取目录下标签
        foreach($tags as $key => $result3){//读取一级标签内容总数
            //内容个数计数 采集池+线上内容（包括复制内容）
            $tagres = $model ->table("zy_idtotagid")->where(array('tagid'=>$result3['tagid']))->field('id')->select();
            $tagarr = array();
            foreach($tagres as $val){
                $tagarr[] = $val['id'];
            }
            $where_sql = array('c.id'=>array('in',$tagarr),'c.status'=>1);	
            $pcount = $model -> table('zy_collect c') ->join("left join zy_collect_ext e on c.id=e.colid")-> where($where_sql)->count();
            $tags[$key]['pcount'] = $pcount;//内容个数计数
            
            $wheresql = array('status' => array('neq',-1),'parentid' => $result3['tagid']);
            $downcount = $model -> table('zy_tags')-> where($wheresql)->find();
            $tags[$key]['downcount'] = empty($downcount) ? 0 : 1;//是否存在子标签
            $ext = $this->model_te->where(array('status'=> 1,'tagid'=>$result3['tagid']))->find();
            if(empty($ext['img_path'])){
                $tags[$key]['img_path'] = '';
            }else{
                $tags[$key]['img_path'] = $this->img_url.$ext['img_path'];
            }
        
        }
        $pcount = array();
        if(isset($_GET['corder'])){
            foreach ($tags as $key => $row) 
            {
                $tagid[$key] = $row['tagid'];
                $tagname[$key] = $row['tagname'];
                $parentid[$key] = $row['parentid'];
                $group[$key] = $row['group'];
                $status[$key] = $row['status'];
                $pcount[$key] = $row['pcount'];
                $downcount[$key] = $row['downcount'];
                $img_path[$key] = $row['img_path'];
                $t_rank[$key] = $row['rank'];
            }
            if($_GET['corder'] == 'asc'){
                array_multisort($pcount, SORT_ASC, SORT_REGULAR, $tagid, $tagname, $parentid, $group, $status,$downcount,$t_rank);
            }else{
                array_multisort($pcount, SORT_DESC, SORT_REGULAR, $tagid, $tagname, $parentid, $group, $status,$downcount,$t_rank);
            }
            
            $tags = array();
            $count = count($pcount);
            for($i=0;$i<$count;$i++){
                $tags[$i]['tagid'] = $tagid[$i];
                $tags[$i]['tagname'] = $tagname[$i];
                $tags[$i]['parentid'] = $parentid[$i];
                $tags[$i]['group'] = $group[$i];
                $tags[$i]['status'] = $status[$i];
                $tags[$i]['pcount'] = $pcount[$i];
                $tags[$i]['downcount'] = $downcount[$i];
                $tags[$i]['img_path'] = $img_path[$i];
                $tags[$i]['rank'] = $t_rank[$i];
            }
          
        }
        $result[$result1['groupid']]['tags'] = $tags;//标签列表
        $result[$result1['groupid']]['count'] = count($tags);//标签总数
		
		$this -> assign('idorder',$_GET['idorder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('nameorder',$_GET['nameorder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('storder',$_GET['storder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('corder',$_GET['corder'] == 'asc' ? 'desc' : 'asc');
		$this -> assign('rankorder',$_GET['rank'] == 'asc' ? 'desc' : 'asc');
		
		$this -> assign('list',$result);
		$this -> display();
        
        
    }
}