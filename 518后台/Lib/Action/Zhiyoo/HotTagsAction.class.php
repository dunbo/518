<?php 

Class HotTagsAction extends CommonAction{
	private $model;
	private $table = 'zy_hot_tags';
	private $table_c = 'zy_column_conf';
	
	public function _initialize() {
        parent::_initialize();
        $this->model = D('Zhiyoo.HotTags');
    }
	
	public function index(){
		$order = $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';
		$result = $this->model->where('status>=1') ->order('status asc,rank '.$order)-> select();
		foreach($result as $k => $v){
            $res = $this->model->table($this->table_c) ->where(array('cid'=>$v['cid']))->find();
            $result[$k]['cname'] = $res['name'];
            $result[$k]['cstatus'] = $res['status'];
        }
		
		$order = $_GET['order'] == 'DESC' ? 'ASC' : 'DESC';
		$this -> assign('order',$order);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	public function choosecolumn(){
        $bbs_modle = D('Zhiyoo.Zhiyoo');
		//获取筛选规则名称 2015-09-07 11:40
		$filter_tabal = 'zy_colimn_filter_rule';
	
		$filter_data = $bbs_modle -> table($filter_tabal)->where('status=1')->select();
	
		$filterdata = array();
		foreach ($filter_data as $fval){
			$filterdata[$fval['id']] = $fval['description'];
		}
		$this->assign('filterdata',$filterdata);
		
		$tags = $bbs_modle->gettags();
		$result = $bbs_modle ->table('zy_column_conf')->where(array('status'=>1,'type'=>0,'platform'=>1))->order($order_sql) ->select();
		foreach($result as $k=>$val){
		
			$colruleinfo = explode('_',$result[$k]['rule']);
			$threadtadid = $tduniquetag = $unsettag =array();
			//取出每个栏目的tagid,并且记录下有子标签的父标签tagid
			foreach($colruleinfo as $tagid){
				$tduniquetag[$tagid] = $threadtadid[$tagid] = $tagid;
				if($tags[$tagid]['parentid']>0) $unsettag[] = $tags[$tagid]['parentid'];
			
			}
			//删除有子标签的父标签的tagid
			foreach($unsettag as $delid){unset($tduniquetag[$delid]);}
			
			foreach($tduniquetag as $tag){
				if($tags[$tag]['status'] == -1) continue;
				$columnrule[$result[$k]['cid']][$tags[$tag]['group']]['name'] = $tags[$tag]['groupname'];	
				$columnrule[$result[$k]['cid']][$tags[$tag]['group']]['tag_arr'][] = $tag;
			}

		}
		
		$this->assign('result',$result);
		$this->assign('ruleinfo',$columnrule);
		$this->assign('tags',$tags);
		$this -> display();
	}
	
	public function edit(){
        if(!empty($_GET['htid'])){
            $result = $this->model->where(array('htid'=>$_GET['htid']))->find();
            $where = array('status'=>1,'cid'=>$result['cid']);
            $res = $this->model->table($this->table_c) ->where($where)->find();
            $result['cid'] = $res['cid'];
            $result['cname'] = $res['name'];
            
            $this -> assign('result',$result);
        }else{
            $count = $this->model->where('status=1')-> count();
            if($count >= 8 )$this -> error("最多只能添加8个标签");
        }
		$this -> display();
	}
	
	public function doedit(){
        if($_GET['action'] == 'tags'){
            $r = false;
            $name = trim($_POST['name']);
            empty($name) && $r = true;
            is_numeric($_POST['rank']) ? $rank = $_POST['rank'] : $r = true;
            is_numeric($_POST['cid']) ? $cid = $_POST['cid'] : $r = true;
            if($r)$this -> error("表单信息错误");
            
            $data = array(
                'name' => $name,
                'rank' => $rank,
                'cid' => $cid,
            );
            if(empty($_GET['htid'])){
                $where = array('status'=>1,'name'=>$name);
                $res = $this->model->where($where)->select();
                if(empty($res)) $id = $this->model->add($data);
                else $this->error("添加失败！标签名称已存在！");
                
            }
            else {
                $id = $_GET['htid'];
                $where = array('status'=>1,'name'=>$name,'htid'=>array('neq',$id));
                $res = $this->model->where($where)->select();
                $where = array('htid'=>$id);
                if(empty($res)) $this->model->where($where)->save($data);
                else $this->error("编辑失败！标签名称已存在！");
            }
            if($id === false){
                $this->error("编辑失败！");
            }else{
                $this -> writelog("智友-热门标签管理 已编辑id：{$id}","zy_hot_tags",$id,__ACTION__ ,"","edit");
                $this->assign('jumpUrl',"/index.php/Zhiyoo/HotTags/index");
                $this->success("编辑成功！");
            }
        }
        if($_GET['action'] == 'rank'){
            foreach($_POST['rank'] as $key => $val){
                $this->model->where (array('htid'=>$key))->save(array('rank'=>intval($val)));
                $this -> writelog("智友-热门标签管理 已编辑id：{$key}优先级{$val}","zy_hot_tags",$key,__ACTION__ ,"","edit");
            }
			
            $this->assign('jumpUrl',"/index.php/Zhiyoo/HotTags/index");
			$this->success("编辑成功！");
        }
	}
	
	public function del(){
		$result = $this->model ->where(array('htid'=>$_GET['htid']))->save(array('status'=>-1));
		if($result !== false){
			$this -> writelog("智友-热门标签管理 已删除id{$_GET['htid']}","zy_hot_tags",$_GET['htid'],__ACTION__ ,"","del");
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
	}
	
	public function status(){
        if($_GET['status'] == '1'){//正常变停用
            $result = $this->model ->where(array('htid'=>$_GET['htid']))->save(array('status'=>2));
            // $result = $this->model ->table('zy_column_conf')->where('cid='.$_GET['cid'])->save(array('status'=>2));
        }else{
            $result = $this->model ->table('zy_column_conf')->where(array('cid'=>$_GET['cid']))->select();
            if($result[0]['status'] != '1'){
                $this->error("链接栏目未启用！状态更改失败！");
            }
            $result = $this->model ->where(array('htid'=>$_GET['htid']))->save(array('status'=>1));
        }
		
		if($result !== false){
			$this -> writelog("智友-热门标签管理 已更改状态id{$_GET['htid']}为".($_GET['status'] == '1' ? 2 : 1),"zy_hot_tags",$_GET['htid'],__ACTION__ ,"","edit");
			$this->success("状态更改成功！");
		}else{
			$this->error("状态更改失败！");
		}
	}
}