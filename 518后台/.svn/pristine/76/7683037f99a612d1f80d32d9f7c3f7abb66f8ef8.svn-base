<?php 

Class TdkAction extends CommonAction{
	private $model;
	private $slavemodel;
    
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.Tdk');
        $this->slavemodel = D('Zhiyoo.Tdk');
    }
    
	public function taglist(){
		$ordertype = 'status desc';
		if(isset($_GET['idorder'])) $ordertype = $_GET['idorder'] == 'desc' ? 'tagid desc' : 'tagid asc';
		if(isset($_GET['rankorder'])) $ordertype = $_GET['rankorder'] == 'desc' ? 'rank desc' : 'rank asc';
		
		$firstgroup = $this->slavemodel->table("zy_tagsgroup")->where('status=1')->order('groupid asc')->select();//读取一级目录，按照优先级升序排列
		foreach($firstgroup as $result1){
			$result[$result1['groupid']]['name'] = $result1['groupname'];//分类名称
			$result[$result1['groupid']]['groupid'] = $result1['groupid'];
            $wheresql = array(
                        'status' => array('neq',-1),
                        'parentid' => 0,
                        'group' => $result1['groupid']
                        );
			// $count = $this->model -> table("zy_tags")->where($wheresql)->count();//读取目录下标签总数
			
			$tags = $this->slavemodel->table("zy_tags")->where($wheresql)->order($ordertype)->select();//读取目录下标签
			foreach($tags as $key => $result3){//读取一级标签内容总数
                // $wheresql = array('status' => array('neq',-1),'parentid' => $result3['tagid']);
				// $downcount = $this->model -> table('zy_tags')-> where($wheresql)->count();
				// $tags[$key]['downcount'] = $downcount;//子标签个数计数
                // $ext = $this->model->where(array('type'=> 1,'id'=>$result3['tagid']))->find();
                $tagids[] = $result3['tagid'];
			}
            
			$result[$result1['groupid']]['tags'] = $tags;//标签列表
            $result[$result1['groupid']]['count'] = count($tags);//标签总数
		}
        $ext = $this->slavemodel->table('zy_tdk')->where(array('type'=> 1,'id'=>array('in',$tagids)))->select();
        foreach($ext as $val){
            $tagTdk[$val['id']] = $val;
        }
		
		$this -> assign('idorder',$_GET['idorder'] == 'desc' ? 'asc' : 'desc');
		$this -> assign('rankorder',$_GET['rankorder'] == 'desc' ? 'asc' : 'desc');
		
		$this -> assign('result',$result);
		$this -> assign('tagTdk',$tagTdk);
		$this -> display();
	}
    
	public function columnlist(){
		$zymodel = D('Zhiyoo.Zhiyoo');
		$order_sql = 'status asc,rank asc';
		if(isset($_GET['idorder'])) $order_sql = $_GET['idorder'] == 'desc' ? 'cid desc' : 'cid asc';
		if(isset($_GET['rankorder'])) $order_sql = $_GET['rankorder'] == 'desc' ? 'rank desc' : 'rank asc';
		
		$group = $zymodel->gettaggroup();
		$tags = $zymodel->gettags();
		$where = array('platform'=>1,'status'=>array('gt',0));
		$where['type'] = $_GET['type'] == 1 ?  1 : 0;//0栏目 1专题
		$result = $this->model ->table('zy_column_conf')->where($where)->order($order_sql) ->select();
		foreach($result as $key => $val){
			$colruleinfo = explode('_',$val['rule']);
			$threadtagid = $tduniquetag = $unsettag =array();
			//取出每个栏目的tagid
			foreach($colruleinfo as $tagid){
				if($tags[$tagid]['status'] == -1) continue;
				if($tags[$tagid]['parentid']>0) $threadtagid[$tags[$tagid]['group']][] = $tags[$tags[$tagid]['parentid']]['tagname'].'-'.$tags[$tagid]['tagname'];
				else $threadtagid[$tags[$tagid]['group']][] = $tags[$tagid]['tagname'];
			}
			$result[$key]['tag'] = '';
			foreach($threadtagid as $groupid => $tag){
				$result[$key]['tag'] .= $group[$groupid]['groupname'].':'.implode(',',$tag).'<br/>';
			}
			$ext = $this->model->where(array('type'=> 2,'id'=>$val['cid']))->find();
			if(!empty($ext)){
				$result[$key] = array_merge($result[$key],$ext);
			}
		}
		
		$this -> assign('idorder',$_GET['idorder'] == 'desc' ? 'asc' : 'desc');
		$this -> assign('rankorder',$_GET['rankorder'] == 'desc' ? 'asc' : 'desc');
		
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit(){
		if($_GET['tagid']){
			$tagid = $_GET['tagid'];
			$res = $this->model->where(array('type'=>1,'id'=>$tagid))->find();
		}elseif($_GET['cid']){
			$cid = $_GET['cid'];
			$res = $this->model->where(array('type'=>2,'id'=>$cid))->find();
		}
		$this -> assign('result',$res);
		$this -> display();
	}
	
	function doedit(){
		$data = array('title' => trim($_POST['title']),
						'description' => trim($_POST['description']),
						'keywords' => trim($_POST['keywords']));
		if($_GET['tagid']){
			$tagid = $_GET['tagid'];
			$res = $this->model->where(array('type'=>1,'id'=>$tagid))->find();
			$data['type'] = 1;
			$data['id'] = $tagid;
			if($res) $res = $this->model->save($data);
			else $res = $this->model->add($data);
			$this -> writelog("智友内容管理-众测后台-SEO-TDK配置 已修改标签id为{$tagid}的TDK","zy_tdk",$tagid,__ACTION__ ,"","edit");
		}elseif($_GET['cid']){
			$cid = $_GET['cid'];
			$res = $this->model->where(array('type'=>2,'id'=>$cid))->find();
			$data['type'] = 2;
			$data['id'] = $cid;
			if($res) $res = $this->model->save($data);
			else $res = $this->model->add($data);
			$this -> writelog("智友内容管理-众测后台-SEO-TDK配置 已修改栏目id为{$cid}的TDK","zy_tdk",$cid,__ACTION__ ,"","edit");
		}
		
		
		// $this->assign("jumpUrl", "/index.php/Zhiyoo/Tdk/taglist");
		$this->success("修改成功！");
	}
	
	function secondtaglist(){
		$tagid = $_GET['tagid'];
		$ordertype = 'status desc';
		if(isset($_GET['idorder'])) $ordertype = $_GET['idorder'] == 'desc' ? 'tagid desc' : 'tagid asc';
		if(isset($_GET['rankorder'])) $ordertype = $_GET['rankorder'] == 'desc' ? 'rank desc' : 'rank asc';
		
        $result = $this->model ->table('zy_tags')-> where(array('tagid' => $tagid))->find();//一级标签
		$parent['tagname'] = $result['tagname'];
		$wheresql = array(
					'status' => array('neq',-1),
					'parentid' => $tagid
					);
		// $count = $this->model -> table("zy_tags")->where($wheresql)->count();//读取目录下标签总数
		// $parent['count'] = $count;//标签总数
		$result = $this->model -> table("zy_tags")->where($wheresql)->order($ordertype)->select();//读取目录下标签
        if(!$result){
            $this->error('当前标签下没有子标签,请配置后再来查看');
        }
        $parent['count'] = count($result);//标签总数           
		foreach($result as $key => $val){
			$ext = $this->model->where(array('type'=> 1,'id'=>$val['tagid']))->find();
			if(!empty($ext)){
				$result[$key] = array_merge($result[$key],$ext);
			}
		}
		
		$this -> assign('idorder',$_GET['idorder'] == 'desc' ? 'asc' : 'desc');
		$this -> assign('rankorder',$_GET['rankorder'] == 'desc' ? 'asc' : 'desc');
		
		$this -> assign('parent',$parent);
		$this -> assign('result',$result);
		$this -> display();
	}
}