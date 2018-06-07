<?php
class InformationAction extends CommonAction {
    /**
		* 动态信息展示
	*/
	function information_list(){
		import("@.ORG.Page2");
		$model = new Model();
		if(isset($_GET['p'])){
			$p =  $_GET['p'];
		}else{
			$p = 1;
		}
		if(isset($_GET['lr'])){
			$lr =  $_GET['lr'];
		}else{
			$lr = 10;
		}
		$info_type = isset($_GET['info_type']) ? $_GET['info_type'] : 1;
		$where['status'] = $info_type ;
		if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
			$this -> assign("start_date",$_GET['start_date']);
			$this -> assign("end_date",$_GET['end_date']);
			$start_tm = strtotime($_GET['start_date']);
			$end_tm = strtotime($_GET['end_date']);
			$where['_string'] = "create_tm >= {$start_tm}  and create_tm <= {$end_tm}";
		}
		if(!empty($_GET['title'])){
			$where['title'] = array("like","%{$_GET['title']}%");
			$this -> assign("title",$_GET['title']);
		}
		$count = $model -> table("dev_information") -> where($where)->count();
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$list = $model -> table("dev_information") -> where($where) ->order('pos asc')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		foreach($list as $k => $v){
			$list[$k]['show_tm'] = date("Y-m-d H:i",$v['update_tm']);
			//排序
			$tmp = "<select rel='{$v['id']}' name='rank' class='extent_rank'>";
			for($i=1;$i<=$count;$i++) {
				$select = $i==$v['pos'] ? ' selected' : '';
				$tmp .= "<option value='{$i}'{$select}>{$i}</option>";
			}
			$tmp .= "</select>";
			$list[$k]['pos_str'] = $tmp;
		}
		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign("count", $count);
		$this->assign("p", $p);
		$this->assign("lr", $lr);
		$this -> assign("list",$list);

		$this -> assign("info_type",$info_type);
		$this->display();
	}
	/**
		* 动态信息添加
	*/
	function information_add(){

		if(is_numeric($_GET['id'])) {
			$model = new Model();
			$detail = $model->Table('dev_information')->where("id={$_GET['id']}")->select();

			$detail[0]['content'] = str_replace('IMG_HOST',IMGATT_HOST,$detail[0]['content']);
                                                                  $this->assign("color",$detail[0]['color']);
			$this->assign("editor_id", $_GET['id']);
			$this->assign("detail", $detail[0]);
		} else {
			unset($_GET['id']);
		}

		$this->display();
	}

	//动态信息添加_提交
	function information_add_submit() {

		if(empty($_POST['editor_preurl'])) $_POST['editor_preurl'] = '/index.php/Dev/Information/information_list/';

		$_POST['editor_title'] = trim($_POST['editor_title']);
		$_POST['editor_content'] = trim(strip_tags($_POST['editor_content'],'<a><strong><em><u><span><p><br><img>'));

		//对内容是否为<p>&nbsp;</p>的检查
		$tmp = trim(strip_tags($_POST['editor_content']));
		/* if($tmp=='&nbsp;') {
			$this->assign('jumpUrl',$_POST['editor_preurl']);
			$this->error('请填写正文！');
		} */

		if(!$_POST['editor_title']) {
			$this->assign('jumpUrl',$_POST['editor_preurl']);
			$this->error('请填写标题！');
		}
		/* if(!$_POST['editor_content']) {
			$this->assign('jumpUrl',$_POST['editor_preurl']);
			$this->error('请填写正文！');
		} */

		//文章中图片处理,开始
		preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$_POST['editor_content'],$matches);
		if($matches[1]) {	//有需要上传的新图片
			$pre_path = $_SERVER['DOCUMENT_ROOT'];		//web根目录
			//图片宽度不超过600px检查
			foreach($matches[1] as $key => $val) {
				unset($width,$height,$type,$attr);
				list($width,$height,$type,$attr) = getimagesize($pre_path.$val);
				if($width>600) {
					$this->assign('jumpUrl',$_POST['editor_preurl']);
					$this->error('有图片宽度超过限定的600px，请检查！');
					exit;
				}
			}

			//上传图片
			$files = array();
			$files_name = array();
			foreach($matches[1] as $key => $val) {
				$upload_model = D("Dev.Uploadfile");
				$files_name[$key] = str_replace('.','',microtime(true)).'_'.$upload_model -> rand_code(8);
			}
			foreach($matches[1] as $key => $val) {
				$files[$files_name[$key]] = '@'.$pre_path.$val;
			}
			$arr = InformationAction::dev_upload($files);
			if($arr['info']['http_code']!=200) {
				$this->assign('jumpUrl',$_POST['editor_preurl']);
				$this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
			}
			//删除public下图片
			foreach($matches[1] as $key => $val) {
				unlink($pre_path.$val);
			}

			$new_arr = array();
			if($arr['ret']) {
				foreach($arr['ret'] as $key=>$val) {
					unset($k,$new_k);
					$k = array_search($key,$files_name);
					$new_k = $matches[1][$k];
					$new_arr[$new_k] = 'IMG_HOST'.$val;
				}
				//文章内容中图片路径替换
				$_POST['editor_content'] = strtr($_POST['editor_content'],$new_arr);
			}
		}
		//文章中图片处理,结束

		//插入数据库
		$model = new Model();
		$data = array();
		$editor_id = intval($_POST['editor_id']);
		$do = '';
		if($editor_id) {
			$data['title'] = $_POST['editor_title'];
			$data['content'] = $_POST['editor_content'];
			$data['update_tm'] = time();
            $data['color'] = $_POST['color'];
            $log_result = $this->logcheck(array('id'=>$editor_id),'dev_information',$data,$model->table('dev_information'));
			$res = $model->table('dev_information')->where("id='{$editor_id}'")->save($data);
			$do = '编辑';

			$log = "编辑了id为{$editor_id}的动态信息！{$log_result}";
		} else {
			$data['title'] = $_POST['editor_title'];
			$data['content'] = $_POST['editor_content'];
			$data['status'] = 1;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['color'] = $_POST['color'];			
			$res = $model->table('dev_information')->add($data);
			if($res){
				//添加成功后排序
				$table       = 'dev_information';
				$field       = 'pos';
				$where       = "status=1";
				$extent_id   = $res;
				$target_rank = 1;

				$where_rank = array(
					'status' => 1,
				);

				//更新排序
				$param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
			}
			$do = '添加';
			$log = "添加了id为{$res}的动态信息！";
		}

		if($res) {
			if($do=='添加'){
				$type = 'add';
			}else{
				$type = 'edit';
			}
			$this->writelog($log,"dev_information",$res,__ACTION__ ,"",$type);
			$this->assign('jumpUrl',$_POST['editor_preurl']);
			$this->success("{$do}文章成功！");
		} else {
			$this->assign('jumpUrl',$_POST['editor_preurl']);
			$this->error("数据{$do}失败，请重试！");
		}
	}

	//动态信息添加_图片处理,代码来源:/dev.goapk.com/common.php
	//上传图片
	public static function dev_upload($files) {
		$vals = array(
			'do' => 'save',
			'static_data' => '/data/att/m.goapk.com',
		);		
		$upload_model = D("Dev.Uploadfile");
		return $upload_model -> _http_post(array_merge($vals,$files));
	}


	//动态信息编辑_获取编辑内容
	function information_edit_getcont() {
		$id = intval($_GET['id']);

		$model = new Model();
		$one = $model->Table('dev_information')->where("id={$id}")->select();
		exit(json_encode($one[0]));
	}

	/**
		* 动态信息编辑
	*/
	function information_edit(){
		$this->display();
	}
	/**
		* 动态信息删除
	*/
	function information_del(){
		$referer = $_POST['preurl'] ? $_POST['preurl'] : $_SERVER['HTTP_REFERER'];
		$info_id = $_GET['info_id'];
		if(strpos($info_id,',')!==FALSE) {
			$where = "id IN (".mysql_escape_string($info_id).")";
		} else {
			$where = "id='{$info_id}'";
		}
		$info_list = array();
		$info_list['status'] = 2;		//删除
		$info_list['update_tm'] = time();

		$model = new Model();
		$result = $model->table("dev_information")->where($where)-> save($info_list);
		if(empty($result)){
			$this->error('删除动态信息失败');
		}else{
			$this->writelog("删除了id为{$info_id}的动态信息","dev_information",$info_id,__ACTION__ ,"","del");
			$this->assign("jumpUrl", $referer);
			$this->success('删除动态信息成功');
		}
	}
	/**
		* 停用动态信息
	*/
	function information_stop(){
		$model = new Model();
		$info_id = $_GET['info_id'];
		$referer = $_POST['preurl'];
		if(strpos($info_id,',')!==FALSE) {
			$where = "id IN (".mysql_escape_string($info_id).")";
		} else {
			$where = "id='{$info_id}'";
		}
		$pos_list = $model-> table("dev_information")-> where(array("status" =>1,"id" => $info_id))->field('pos')-> find();
		$wheres['pos'] = array('gt',$pos_list['pos']);
		$wheres['status'] = 1;
		$list = $model->table("dev_information")->where($wheres)-> select();
		foreach($list as $v){
			$sql = "update dev_information set pos = pos-1 where id={$v['id']}";
			$model -> query($sql);
		}	
		$info_list['status'] = 0;
		$info_list['update_tm'] = time();
		$result = $model -> table("dev_information") -> where($where) -> save($info_list);
		if(empty($result)){
			$this->error('停用动态信息失败');
		}else{
			$this->writelog("停用了id为{$info_id}的动态信息成功","dev_information",$info_id,__ACTION__ ,"","edit");
			$this->assign("jumpUrl", $referer);
			$this->success('停用动态信息成功');
		}
	}
	/**
		* 动态信息启用
	*/
	function information_start(){
		$model = new Model();
		$info_id = $_GET['info_id'];
		$referer = $_POST['preurl'];
		if(strpos($info_id,',')!==FALSE) {
			$where = "id IN (".mysql_escape_string($info_id).")";
		} else {
			$where = "id='{$info_id}'";
		}
		$info_list['status'] = 1;
		$info_list['update_tm'] = time();
		$result = $model -> table("dev_information") -> where($where) -> save($info_list);
		if(empty($result)){
			$this->error('启用动态信息失败');
		}else{
			//启用成功后排序
			$table       = 'dev_information';
			$field       = 'pos';
			$where       = "status=1";
			$extent_id   = $info_id;
			$target_rank = 1;

			$where_rank = array(
				'status' => 1,
			);

			//更新排序
			$param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);

			$this->writelog("启用了id为{$info_id}的动态信息成功","dev_information",$info_id,__ACTION__ ,"","edit");
			$this->assign("jumpUrl", $referer);
			$this->success('启用动态信息成功');
		}
	}
		//排序
	function reason_sequence() {
	    if(isset($_GET)){
			$table       = 'dev_information';
			$field       = 'pos';
			$where       = "status=1";
			$extent_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['pos'];

			$where_rank = array(
				'status' => 1,
			);

			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
			exit(json_encode($param));
		}
	}
}
?>