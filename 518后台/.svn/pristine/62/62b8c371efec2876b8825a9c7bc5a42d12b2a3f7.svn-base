<?php 
Class ActiveConfPcAction extends CommonAction{
	private $bbsmodel;
	private $model;
	private $img_host_out = array(
            'http://img1.anzhi.com',
            'http://img2.anzhi.com',
            'http://img3.anzhi.com',
            'http://img4.anzhi.com',
            'http://img5.anzhi.com'
        );
	
	public function _initialize() {
        parent::_initialize();
		$this->bbsmodel = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.ActiveConfPc');
	}
	
	function active_list(){
		$rank = $_GET['rank'] ? $_GET['rank']: 'asc';
		$order_url = $_GET['rank'] == 'desc' ? 'asc' : 'desc';
		$type = $_GET['type'] ? $_GET['type'] : 1;
		$order_sql = !$_GET['rank']?  'rank asc': "rank {$rank}";
		
		$count = $this -> model -> table('zy_active_conf_pc') -> where("type={$type} and status >0") -> count();
        
        import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$show = $Page->show();
		$res = $this -> model -> table('zy_active_conf_pc') -> where("type={$type} and status >0 ") -> order($order_sql)-> limit($Page->firstRow . ',' . $Page->listRows)->select();
		$showadd = 1;
		if($type == 1 && $count >= 3) $showadd = 0; 
		if($type == 2 && $count >= 1) $showadd = 0; 
		if($type == 6 && $count >= 4) $showadd = 0; 
		if($type == 7 && $count >= 6) $showadd = 0; 
		if($type == 8 && $count >= 3) $showadd = 0; 
		if($_GET['edit_rank']){
			$this ->assign('editrank',$_GET['edit_rank']);
			$showadd = 0;
		}
		foreach($res as $key=>$val){
			$jumptxt = '';
			if($val['operation_type'] == 1 && $val['cid']){
				$name = $this -> model->table('zy_column_conf')->field('name')->where("cid={$val['cid']} and status >=1 ")-> find();
				$jumptxt = !empty($name['name']) ? "栏目：".$name['name'] : '';
			}elseif($val['operation_type'] == 2 && $val['pid']){
				$name = $this -> model->table('zy_column_conf')->field('name')->where("cid={$val['pid']} and status >=1 ")-> find();
				$jumptxt = !empty($name['name']) ? "专题：".$name['name'] : '';
			}elseif($val['operation_type'] == 3){
				$name = $this -> bbsmodel->table('x15_forum_forum')->field('name')->where(array('fid'=>$val['fid']))-> find();
				$jumptxt = "版块：".$name['name'];
				
			}elseif($val['operation_type'] == 4){
				$name = $this -> bbsmodel->table('x15_forum_thread')->field('subject')->where(array('tid'=>$val['tid']))-> find();
				$jumptxt = "主题：".$name['subject'];
				
			}elseif($val['operation_type'] == 5){
				$jumptxt = "内部链接地址：".$val['url'];
				
			}elseif($val['operation_type'] == 6){
				$jumptxt = "外部链接地址：".$val['url'];
				
			}elseif($val['operation_type'] == 7){
				$jumptxt = '外部广告代码';
			}elseif($val['operation_type'] == 8){
				$r = $this -> model->table('zy_tags')->field(array('group','tagname','parentid'))->where(array('tagid'=>$val['tagid']))-> find();
                $gid = $r['group'];
                $tname = $r['tagname'];
                $gname = $this -> model->table('zy_tagsgroup')->where(array('groupid'=>$gid))->getField('groupname');
				$jumptxt = $gname."：";
                if($r['parentid']){
                    $pn = $this -> model->table('zy_tags')->where(array('tagid'=>$r['parentid']))-> getField('tagname');
                    $jumptxt .= $pn.' > ';
                }
                $jumptxt .= $tname;
			}
			$res[$key]['jumptxt'] = $jumptxt;
		}
        
		$title = $this -> model -> table('zy_active_conf_pc_type') -> where("type={$type} and status >0 ") ->find();
		
        $this -> assign("title", $title['sname'] ? $title['sname'] : $title['name']);
        $this -> assign("page", $show);
		$this -> assign('actlist',$res);
		$this -> assign('order_url',$order_url);
		$this -> assign('showadd',$showadd);
		$this -> assign('ltype',$type);
		if(in_array($type,array(3,4,5))) {
			$this -> display('portal_list');
		}else{
            $this -> display();
        }
	}
	
	function add(){
		$type = $_GET['type'] ? $_GET['type'] : 1;
		$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=1') ->select();
		$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform=1') ->select();
		$this -> assign('jump',$jump);
		$this -> assign('column',$column);
		$this -> assign('pref',$pref);
		$this -> assign('ltype',$type);
		$this -> display('edit');		
	}
	
	function edit(){
		$type = $_GET['type'] ? $_GET['type'] : 1;
		$id = $_GET['id'];
		if(!id){
			$this -> error('id不能为空');
		}
		$info = $this -> model -> table('zy_active_conf_pc') -> where("id ={$id}")->find();
		$info['cat'] = $info['operation_type'];
        
		$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=1') ->select();
		$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform =1') ->select();
		if($info['fid']){
			$fname = $this -> bbsmodel -> table('x15_forum_forum')->field('name')-> where(array('fid'=>$info['fid']))->find();
		}
		if($info['tid']){
			$title = $this -> bbsmodel -> table('x15_forum_thread')->field('subject')-> where(array('tid'=>$info['tid']))->find();
		}
		if($info['tagid']){
            $r = $this -> model->table('zy_tags')->field(array('group','tagname','parentid'))->where(array('tagid'=>$info['tagid']))-> find();
            $gname = $this -> model->table('zy_tagsgroup')->where(array('groupid'=>$r['group']))->getField('groupname');
            $tagtext = $gname."：";
            if($r['parentid']){
                $pn = $this -> model->table('zy_tags')->where(array('tagid'=>$r['parentid']))-> getField('tagname');
                $info['tagstr'] = $r['parentid'].'_'.$info['tagid'];
                $tagtext .= $pn.' > ';
            }
            $tagtext .= $r['tagname'];
		}
		$this -> assign('tagtext',$tagtext);
		$this -> assign('column',$column);
		$this -> assign('pref',$pref);
		$this -> assign('ltype',$type);
		$this -> assign('info',$info);
		$this -> assign('id',$id);
		$this -> assign('fname',$fname['name']);
		$this -> assign('title',$title['subject']);
		$this -> display();		
	}
	
	function changestatus(){
		$type = $_GET['type'] ? $_GET['type'] : 1;
		$status = $_GET['status'] == 1 ? 2 : 1;
		$txt = $status == 2 ? '停用' : '启用';
		$id = $_GET['id'];
		if(!$id){
			$this -> error('id不能为空');
		}
		$data['status'] = $status;
		$data['id'] = $id;
		$res = $this -> model -> table('zy_active_conf_pc') -> save($data);
		if($res){
			$this -> writelog("智友内容管理-PC运营位配置 已{$txt}id为{$res}的运营位","zy_active_conf_pc",$res,__ACTION__ ,"","edit");
			$this -> success("{$txt}成功");
		}
	}
	
	
	function add_submit(){
		$type = $_GET['type'];
		$id = $_GET['id'];
		$text = $id ? '编辑' :'添加' ;
		$optype = $_POST['cat'] > 0 ? $_POST['cat'] : $_POST['op_type'];
		$subject = trim($_POST['subject']) ? trim($_POST['subject']) : '';
		$datedir = '/zhiyoo/';
		$savepath = UPLOAD_PATH.$datedir;
        
		if($_FILES['img']['size']>0){
            $config = array(
                'tmp_file_dir' => '/tmp/',
                'width' => 2000,
                'filesize' => 100000,
                'real_width' => 2000,
            );
			$imgpath = $this -> _upload($_FILES['img'],$savepath,$config);
			$data['pic_path'] = $datedir.$imgpath;
		}elseif(in_array($type,array(1,2))&&in_array($optype,array(4,5,6))){
            $this ->error("{$text}失败，必须上传图片");
        }
		
		$data['type'] = $type;
		$data['operation_type'] = $optype;
		
		if(in_array($type,array(3,4))&&!$subject){
			$this ->error("{$text}失败，该运营位必须填写文案");
		}
        $subject  &&  $data['subject'] = trim($subject);
		
		$data['tid'] = $data['cid'] = $data['fid'] = $data['pid'] = 0;
		$data['url'] = '';
		if($optype == 1 && $_POST['cat'] == 1){
			if(empty($_POST['cid'])){
				$this ->error("{$text}失败，请选择栏目");
			}
			$data['cid'] = $_POST['cid'];
		}
		if($optype == 2){
			if(empty($_POST['pid'])){
				$this ->error("{$text}失败，请选择专题");
			}
			$data['pid'] = $_POST['pid'];
		}
		if($optype == 3){
			if(empty($_POST['fid'])){
				$this ->error("{$text}失败，请选择板块");
			}
			$data['fid'] = $_POST['fid'];
		}
		if($optype == 4){
			if(empty($_POST['tid'])){
				$this ->error("{$text}失败，请填写tid");
			}
			$data['tid'] = $_POST['tid'];
		}
		if($optype == 5){
			if(empty($_POST['innerurl'])){
				$this ->error("{$text}失败，请填写内置跳转链接");
			}
			$data['url'] = trim($_POST['innerurl']);
		}
		if($optype == 6){
			if(empty($_POST['outerurl'])){
				$this ->error("{$text}失败，请填写外部跳转链接");
			}
			$data['url'] = trim($_POST['outerurl']);		
		}
		if($optype == 7){
			if(empty($_POST['outcode'])){
				$this ->error("{$text}失败，请填写外部广告代码");
			}
			$data['outcode'] = trim($_POST['outcode']);		
		}
		if($optype == 8){
			if(empty($_POST['tagid'])){
				$this ->error("{$text}失败，选择标签");
			}
			$data['tagid'] = trim($_POST['tagid']);		
		}
		
		if($id){
			$data['id'] = $id;
			$result = $this-> model -> table('zy_active_conf_pc') -> save($data);	
		}else{
			$data['rank'] = 999; 
			$result = $this-> model -> table('zy_active_conf_pc') -> add($data);	
		}
		
			
		if($result !== false){
			if($data['id']){
				$etype = 'edit';
			}else{
				$etype = 'add';
			}
			$this -> writelog("智友内容管理-PC运营位配置 {$text}id为{$result}的运营位","zy_active_conf_pc",$result,__ACTION__ ,"",$etype);
			if(in_array($type,array(3,4,5,6,7,8))) {
                $this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConfPc/portal_list/type/{$type}");
            }else{
                $this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConfPc/active_list/type/{$type}");
            }
			$this -> success("{$text}成功");
		}else{
			$this -> error("{$text}失败");
		}
	}
	
	function getthread(){
		$tid = $_GET['tid'];
		$subject = $this -> bbsmodel -> table('x15_forum_thread') -> field('subject') -> where("tid={$tid}") ->find();
		if($subject){			
			echo $subject['subject'];
		}else{
			echo 0;
		}
	}
	
	function editsubject(){
		$type = $_GET['type'];
		if(!$type){
			$this ->error('修改失败');
		}
		$subject = $this -> model -> table('zy_active_conf_pc_type') ->where("type={$type}") ->getField('sname');
		$this ->assign('subject',$subject);
		$this ->assign('type',$type);
		$this -> display();
	}
	
	function editsubject_sub(){
		$type = $_GET['type'];
		$subject = $_POST['subject'];
		if(!$type||!$subject){
			$this ->error('修改失败');
		}
		
		$res = $this -> model -> table('zy_active_conf_pc_type')->where(array('type' => $type)) ->save(array('sname' => $subject));
		if($res !== false){
			$this->writelog("智友内容管理-PC运营位配置 修改id为{$id}的文案","zy_active_conf_pc_type",$id,__ACTION__ ,"","edit");
			$this->success('文案修改成功');
		}else{
			$this ->error('文案修改失败');
		}
		
		$this -> display();
		
		
	}
	
	function editrank(){
		$type = $_GET['type'];
		foreach($_POST['order'] as $id =>$rank){
			$this->model->table('zy_active_conf_pc')->where('id='.$id)->save(array('rank'=>$rank));	
			$this -> writelog("智友内容管理-PC运营位配置 已编辑id为{$id}运营位的优先级为".$rank,"zy_active_conf_pc",$id,__ACTION__ ,"","edit");
		}
		
		$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConfPc/active_list/type/{$type}");
		$this -> success("编辑优先级成功");
		
		
	}
	
	function del(){
		$id = $_GET['id'];
		$type = $_GET['type'];
		if(empty($id)){
			$this->error('id为空，删除失败');
		}
		$data['id'] = $id;
		$data['status'] = 0;
		$res = $this -> model -> table('zy_active_conf_pc') -> save($data);
		if($res){			
			$this -> writelog("智友内容管理-PC运营位配置 已删除id为{$id}的运营位","zy_active_conf_pc",$id,__ACTION__ ,"","del");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function portal_list(){
		!$_GET['type'] && $_GET['type'] = 3;
		$this->active_list();
	}
	
	private function decodeUnicode($str)
	{
		return  preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
			create_function(
				'$matches',
				'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
			),
			$str);
	}
	
	protected function _upload($file,$savepath,$config){
		include dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}
    
    public function delimg(){
        $id = intval($_GET['id']);
        $result = $this-> model -> table('zy_active_conf_pc')->where(array('id'=>$id)) -> save(array('pic_path'=>''));	
		if($result){
			$this -> writelog("智友内容管理-PC运营位配置 删除运营位ID为{$id}的图片","zy_active_conf_pc",$id,__ACTION__ ,"","del");
			echo json_encode(array('su'=>1));
		}else{
			echo json_encode(array('su'=>0));
		}
    }
	
	function phone_tag(){
		$chkfid = $_GET['id'] ? $this-> model -> table('zy_active_conf_pc') -> where(array('id'=>$_GET['id']))->getField('tagid'): 0;
		$grouplist = $this-> model -> table('zy_tagsgroup') -> where(array('groupid'=>array('in',array(1,2))))->select(); //论坛分区数据
		$forumlist = array();
		$subforumlist = array();
		foreach($grouplist as $index =>  $ginfo){
			$forums = $this ->model -> table('zy_tags') -> where(array('status'=>1,'group'=>$ginfo['groupid']))->select(); //论坛板块数据
			$sublist[$ginfo['groupid']] = $forums;
		}
		$this -> assign('id',$_GET['id']);
		$this -> assign('chk',$chk);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('sublist',$sublist);
		$this -> display();
	}
    
    public function tag_submit(){
        $id = intval($_GET['id']);
        $data = array('tagid'=>$_POST['subtags'],'type'=>5,'operation_type'=>8);
		if($id){
			$data['id'] = $id;
			$result = $this-> model -> table('zy_active_conf_pc') -> save($data);	
		}else{
			$data['rank'] = 999; 
			$result = $this-> model -> table('zy_active_conf_pc') -> add($data);	
		}
		if($result !== false){
			$this -> writelog("智友内容管理-PC运营位配置 运营位ID为{$id}的tagid为{$_POST['subtags']}","zy_active_conf_pc",$id,__ACTION__ ,"","del");
			$this -> success("操作成功");
		}else{
			$this -> error("操作失败");
		}
    }
	
	function tag_list(){
		//获取所有的一级分类
		$result = $this-> model ->table('zy_tagsgroup')->field(array('groupid','groupname'))->where('status = 1')->select();
		foreach($result as $val){
			$cat[$val['groupid']] = $val['groupname'];
		}
		$result = $this-> model ->table('zy_tags')->field(array('tagid','tagname','group','parentid'))->where('status = 1')->select();
		foreach($result as $val){
			$taginfo[$val['group']][] = $val;
			$tagpinfo[$val['parentid']][] = $val;
			$taglist[$val['tagid']] = $val;
		}
		
		$id = $_GET['id']?$_GET['id']:0;
		$this->assign('tid' ,$id);
		if($_GET['tags']) {
			$tags = explode('_',$_GET['tags']);
			$this->assign('tags' ,$tags);
		}elseif($id){
			$res = $this-> model -> table('zy_idtotagid')->field('tagid')->where('id='.$id)->select();
			foreach($res as $val){
				$tags[]=$val['tagid'];
			}
			$this->assign('tags' ,$tags);
		}
		$this->assign('taginfo',$taginfo);
		$this->assign('tagpinfo',$tagpinfo);
		$this->assign('tagpinfo_json',json_encode($tagpinfo));
		$this->assign('taglist',$taglist);
		$this->assign('taglist_json',json_encode($taglist));
		$this->assign('grouplist_json',json_encode($cat));
		$this->assign('cat',$cat);
		$this->display();
	}
}