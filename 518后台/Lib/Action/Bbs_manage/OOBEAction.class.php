<?php

Class OOBEAction extends CommonAction{

	function focus_pic_list(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$pic_title = $_GET['pic_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if($pic_title){
			$where_go .= " and pic_title like '%{$pic_title}%'";
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($start_tm && $end_tm){
			$where_go .= " and create_tm >= ".strtotime($start_tm)." and create_tm <= ".strtotime($end_tm);
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $bbs_model -> table('bbs_OOBE_focus') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_OOBE_focus') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank,create_tm DESC') -> select();
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
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('pic_title',$pic_title);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('result',$result);
		$this -> display();
	}

	function add_focus_show(){
		$this -> display();
	}
	
	function add_focus_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$pic_title = $_POST['pic_title'];
		if(mb_strlen($pic_title,'utf8') < 1 || mb_strlen($pic_title,'utf8') > 20){
			$this -> error("请输入1-20字以内的图片标题");
		}
		$my_pic = $_FILES['my_pic'];
		$rank = $_POST['rank'];
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		$pic_link = $_POST['pic_link'];
		if(mb_strlen($pic_link,'utf8') < 1 || mb_strlen($pic_link,'utf8') > 255){
			$this -> error("请输入1-255字以内的轮播图地址");
		}
		if ($my_pic['size']) {
			$halve_wd = getimagesize($my_pic['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
            if ($width_go_ha != 459 || $height_go_ha != 286) {
                $this->error("图标大小不符合条件");
            }

            if ($my_pic['type'] != 'image/png' && $my_pic['type'] != 'image/jpg'  && $my_pic['type'] != 'image/jpeg') {
                $this->error("图标格式错误");
            }
			$config['multi_config']['my_pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['pic_url'] = $bbs_pic_url;
		}else{
			$this -> error("请上传焦点图片");
		}
		$data['pic_title'] = $pic_title;
		$data['rank'] = $rank;
		$data['pic_link'] = $pic_link;
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$result = $bbs_model -> table('bbs_OOBE_focus') -> add($data);
		if($result){
			$this -> writelog("已添加id为{$result}的焦点图", 'bbs_OOBE_focus', $result,__ACTION__ ,"","add");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/OOBE/focus_pic_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_focus_show(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$pic_title = $_GET['pic_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$lr = $_GET['lr'];
		$p = $_GET['p'];
		$result = $bbs_model -> table('bbs_OOBE_focus') -> where(array('id' => $id)) -> find();
		$this -> assign("result",$result);
		$this -> assign("pic_title",$pic_title);
		$this -> assign("start_tm",$start_tm);
		$this -> assign("end_tm",$end_tm);
		$this -> assign("lr",$lr);
		$this -> assign("p",$p);
		$this -> display();
	}
	
	function edit_focus_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_POST['id'];
		$pic_titles = $_POST['pic_titles'];
		if($pic_titles){
			$where_go .= "/pic_title/{$pic_titles}";
		}
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if($start_tm && $end_tm){
			$where_go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
		$lr = $_POST['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_POST['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$pic_title = $_POST['pic_title'];
		if(mb_strlen($pic_title,'utf8') < 1 || mb_strlen($pic_title,'utf8') > 20){
			$this -> error("请输入1-20字以内的图片标题");
		}
		$my_pic = $_FILES['my_pic'];
		$rank = $_POST['rank'];
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		$pic_link = $_POST['pic_link'];
		if(mb_strlen($pic_link,'utf8') < 1 || mb_strlen($pic_link,'utf8') > 255){
			$this -> error("请输入1-255字以内的轮播图地址");
		}
		if ($my_pic['size']) {
			$halve_wd = getimagesize($my_pic['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
            if ($width_go_ha != 459 || $height_go_ha != 286) {
                $this->error("图标大小不符合条件");
            }

            if ($my_pic['type'] != 'image/png' && $my_pic['type'] != 'image/jpg'  && $my_pic['type'] != 'image/jpeg') {
                $this->error("图标格式错误");
            }
			$config['multi_config']['my_pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['pic_url'] = $bbs_pic_url;
		}
		$data['pic_title'] = $pic_title;
		$data['rank'] = $rank;
		$data['pic_link'] = $pic_link;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_OOBE_focus',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_OOBE_focus') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的焦点图".$log_result, 'bbs_OOBE_focus', $id,__ACTION__ ,"","edit");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/OOBE/focus_pic_list".$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_focus_pic(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$pic_title = $_GET['pic_title'];
		if($pic_title){
			$where_go .= "/pic_title/{$pic_title}";
		}
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if($start_tm && $end_tm){
			$where_go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
		$lr = $_GET['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_GET['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$result = $bbs_model -> table('bbs_OOBE_focus') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的焦点图", 'bbs_OOBE_focus', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/OOBE/focus_pic_list".$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function change_rank(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data['rank'] = $rank;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_OOBE_focus',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_OOBE_focus') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的焦点图".$log_result, 'bbs_OOBE_focus', $id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
 
 	function article_list(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$article_title = $_GET['article_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if($article_title){
			$where_go .= " and article_title like '%{$article_title}%'";
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($start_tm && $end_tm){
			$where_go .= " and create_tm >= ".strtotime($start_tm)." and create_tm <= ".strtotime($end_tm);
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $bbs_model -> table('bbs_OOBE_article') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_OOBE_article') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank,create_tm DESC') -> select();
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
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('article_title',$article_title);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('result',$result);
		$this -> display();
	}

	function add_article_show(){
		$this -> display();
	}
	
	function add_article_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$article_title = $_POST['article_title'];
		if(mb_strlen($article_title,'utf8') < 1 || mb_strlen($article_title,'utf8') > 15){
			$this -> error("请输入1-15字以内的文章标题");
		}
		$article_pic = $_FILES['article_pic'];
		$rank = $_POST['rank'];
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		$article_link = $_POST['article_link'];
		if(mb_strlen($article_link,'utf8') < 1 || mb_strlen($article_link,'utf8') > 255){
			$this -> error("请输入1-255字以内的帖子地址");
		}
		if ($article_pic['size']) {
			$halve_wd = getimagesize($article_pic['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
            if ($width_go_ha != 147 || $height_go_ha != 105) {
                $this->error("图标大小不符合条件");
            }

            if ($article_pic['type'] != 'image/png' && $article_pic['type'] != 'image/jpg' && $article_pic['type'] != 'image/jpeg') {
                $this->error("图标格式错误");
            }
			$config['multi_config']['article_pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['article_pic'] = $bbs_pic_url;
		}else{
			$this -> error("请上传文章图片");
		}
		$data['article_title'] = $article_title;
		$data['rank'] = $rank;
		$data['article_link'] = $article_link;
		$data['create_tm'] = time();
		$data['update_tm'] = time();
		$data['status'] = 1;
		$result = $bbs_model -> table('bbs_OOBE_article') -> add($data);
		if($result){
			$this -> writelog("已添加id为{$result}的文章", 'bbs_OOBE_article', $result,__ACTION__ ,"","add");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/OOBE/article_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_article_show(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$article_title = $_GET['article_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$lr = $_GET['lr'];
		$p = $_GET['p'];
		$result = $bbs_model -> table('bbs_OOBE_article') -> where(array('id' => $id)) -> find();
		$this -> assign("result",$result);
		$this -> assign("article_title",$article_title);
		$this -> assign("start_tm",$start_tm);
		$this -> assign("end_tm",$end_tm);
		$this -> assign("lr",$lr);
		$this -> assign("p",$p);
		$this -> display();
	}
	
	function edit_article_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_POST['id'];
		$article_titles = $_POST['article_titles'];
		if($article_titles){
			$where_go .= "/pic_title/{$article_titles}";
		}
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if($start_tm && $end_tm){
			$where_go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
		$lr = $_POST['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_POST['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$article_title = $_POST['article_title'];
		if(mb_strlen($article_title,'utf8') < 1 || mb_strlen($article_title,'utf8') > 15){
			$this -> error("请输入1-15字以内的图片标题");
		}
		$article_pic = $_FILES['article_pic'];
		$rank = $_POST['rank'];
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		$article_link = $_POST['article_link'];
		if(mb_strlen($article_link,'utf8') < 1 || mb_strlen($article_link,'utf8') > 255){
			$this -> error("请输入1-255字以内的轮播图地址");
		}
		if ($article_pic['size']) {
			$halve_wd = getimagesize($article_pic['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
            if ($width_go_ha != 147 || $height_go_ha != 105) {
                $this->error("图标大小不符合条件");
            }

            if ($article_pic['type'] != 'image/png' && $article_pic['type'] != 'image/jpg' && $article_pic['type'] != 'image/jpeg') {
                $this->error("图标格式错误");
            }
			$config['multi_config']['article_pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['article_pic'] = $bbs_pic_url;
		}
		$data['article_title'] = $article_title;
		$data['rank'] = $rank;
		$data['article_link'] = $article_link;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_OOBE_article',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_OOBE_article') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的文章".$log_result, 'bbs_OOBE_article', $result,__ACTION__ ,"","edit");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/OOBE/article_list".$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_article(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$article_title = $_GET['article_title'];
		if($article_title){
			$where_go .= "/article_title/{$article_title}";
		}
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if($start_tm && $end_tm){
			$where_go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
		$lr = $_GET['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_GET['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$result = $bbs_model -> table('bbs_OOBE_article') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的文章", 'bbs_OOBE_article', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/OOBE/article_list".$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function change_rank_article(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data['rank'] = $rank;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_OOBE_article',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_OOBE_article') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的文章".$log_result, 'bbs_OOBE_article', $id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
 
	











}