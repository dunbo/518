<?php

class HelptextAction extends CommonAction{

	function help_list(){
		$model = new Model();
		if($_GET['type'] == 2){
			$where['_string'] = "status = 0";
		}else{
			$_GET['type'] = 1;
			$where['_string'] = "status = 1";
		}
		!isset($_GET['pid']) ? $_GET['pid'] =1 : $_GET['pid'];
		!isset($_GET['show_pid_page']) ? $_GET['show_pid_page'] =0 : $_GET['show_pid_page'];
		$this->check_where($where, 'pid');
		$this->check_where($where, 'show_pid_page');
		$count = $model -> table('sj_helptext') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 10, $param);
		$result = $model -> table('sj_helptext') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank') -> select();
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 15;
		}
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('type',$_GET['type']);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign('result',$result);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);	
		$this->assign('show_pid_page', $_GET['show_pid_page']);			
		$this -> display();
	}
	
	function add_help_show(){
		$model = new Model();
		$count = $model -> table('sj_helptext') -> where(array('status' => 1)) -> count();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('count',$count);
		$this -> assign('rank',$rank);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);				
		$this -> display();
	}
	
	function add_help_do(){
		$model = new Model();
		$title = $_POST['title'];
		if(!$title){
			$this -> error("请填写标题");
		}
		if($this -> strlen_az($title) > 200){
			$this -> error("标题字数限制在100字以内");
		}
		$content = $_POST['content'];
		if(!$content){
			$this -> error("请填写内容");
		}
	
		if($this -> strlen_az($content) > 1000){
			$this -> error("内容字数限制在500字以内");
		}
		$rank = $_POST['rank'];
		$rank_where['_string'] = "rank >= {$rank} and status = 1 and pid ={$_POST['pid']}";
		$rank_result = $model -> table('sj_helptext') -> where($rank_where) -> select();
		foreach($rank_result as $key => $val){
			$change_result = $model -> table('sj_helptext') -> where(array('id' => $val['id'])) -> save(array('rank' => $val['rank'] + 1));
		}
		$data = array(
			'title' => $title,
			'content' => $content,
			'rank' => $rank,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'pid' => $_POST['pid'],
			'show_pid_page' => $_POST['show_pid_page'] ? $_POST['show_pid_page'] : 0,
		);
		$result = $model -> table('sj_helptext') -> add($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已添加id为{$result}的帮助说明",'sj_helptext',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Sj/Helptext/help_list/p/{$p}/lr/{$lr}");
			$this -> success('添加成功');
		}else{
			$this -> error('添加失败');
		}
	}



	function edit_help_show(){
		$model = new Model();
		$id = $_GET['id'];
		$count = $model -> table('sj_helptext') -> where(array('status' => 1)) -> count();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$result = $model -> table('sj_helptext') -> where(array('id' => $id)) -> find();
		$p = $_GET['p'];
		$lr = $_GET['lr'];
		$this -> assign('p',$p);
		$this -> assign('lr',$lr);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);			
		$this -> display();
	}
	
	function edit_help_do(){
		$model = new Model();
		$id = $_POST['id'];
		$title = $_POST['title'];
		$content = $_POST['content'];
		if(!$title){
			$this -> error("请填写标题");
		}
		if($this -> strlen_az($title) > 200){
			$this -> error("标题字数限制在100字以内");
		}

		if(!$content){
			$this -> error("请填写内容");
		}
	
		if($this -> strlen_az($content) > 1000){
			$this -> error("内容字数限制在500字以内");
		}
		$rank = $_POST['rank'];
		$my_result = $model -> table('sj_helptext') -> where(array('id' => $id)) -> find();
		if($rank != $my_result['rank']){
			if($rank > $my_result['rank']){
				$need_where['_string'] = "rank > {$my_result['rank']} and rank <= {$rank} and status = 1";
				$need_result = $model -> table('sj_helptext') -> where($need_where) -> select();			
				foreach($need_result as $key => $val){
					$update_data = array(
						'rank' => $val['rank'] - 1
					);
					$update_result = $model -> table('sj_helptext') -> where(array('id' => $val['id'])) -> save($update_data);
				}
			}else{
				$need_where['_string'] = "rank < {$my_result['rank']} and rank >= {$rank} and status = 1";
				$need_result = $model -> table('sj_helptext') -> where($need_where) -> select();
				foreach($need_result as $key => $val){
					$update_data = array(
						'rank' => $val['rank'] + 1
					);
					$update_result = $model -> table('sj_helptext') -> where(array('id' => $val['id'])) -> save($update_data);
				}
			}
		}
		
		$data = array(
			'title' => $title,
			'content' => $content,
			'rank' => $rank,
			'update_tm' => time(),
		);
		$log_result = $this -> logcheck(array('id' => $id),'sj_helptext',$data,$model);
		$result = $model -> table('sj_helptext') -> where(array('id' => $id)) -> save($data);
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		if($result){
			$this -> writelog("已编辑id为{$id}的帮助说明,".$log_result,'sj_helptext',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl',"/index.php/Sj/Helptext/help_list/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
		
	}

	function del_help(){
		$model = new Model();
		$id = $_GET['id'];
		$been_result = $model -> table('sj_helptext') -> where(array('id' => $id)) -> find();
		$been_where['_string'] = "rank > {$been_result['rank']} and status = 1 and pid={$been_result['pid']}";
		$been_rank = $model -> table('sj_helptext') -> where($been_where) -> select();
		foreach($been_rank as $key => $val){
			$update_result = $model -> table('sj_helptext') -> where(array('id' => $val['id'])) -> save(array('rank' => $val['rank'] - 1));
		}
		
		$result = $model -> table('sj_helptext') -> where(array('id' => $id)) -> save(array('status' => 0));
		$p = $_GET['p'];
		$lr = $_GET['lr'];

		if($result){
			$this -> writelog("已删除id为{$id}的帮助说明",'sj_helptext',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl',"/index.php/Sj/Helptext/help_list/p/{$p}/lr/{$lr}");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	
	}


//计算字段长度
	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}






}