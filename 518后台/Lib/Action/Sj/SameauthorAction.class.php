<?php

class SameauthorAction extends CommonAction{
	
	function soft_list(){
		$model = new Model();
		if($_GET['search_author']){
			$author_where['_string'] = "dev_name like '%{$_GET['search_author']}%' and status = 0";
			$count = $model -> table('pu_developer') -> where($author_where) -> count();
			import("@.ORG.Page");
			$p=new Page($count,10);
			$author_result = $model -> table('pu_developer') -> where($author_where) -> field('dev_name,dev_id') -> limit($p->firstRow.','.$p->listRows) -> select();
			$p->setConfig('header','篇记录');
			$p->setConfig('prev',"上一页");
			$p->setConfig('next','下一页');
			$p->setConfig('first','首页');
			$p->setConfig('last','末页');
			$page = $p->show ();
			if(!$_GET['p']){
				$_GET['p'] = 1;
			}
			$mp = $_GET['p'];
			if(!$_GET['lr']){
				$_GET['lr'] = 10;
			}
			$lr = $_GET['lr'];
		
			$this -> assign('lr',$lr);
			$this -> assign('p',$mp);
			$this->assign( "page", $page );
			$this -> assign('all_author',$author_result);
			$this -> assign('search_author',$_GET['search_author']);
		}
		
		if($_GET['dev_id']){
			$have_been = $model -> table('sj_sameauthor_soft') -> where(array('dev_id' => $_GET['dev_id'])) -> order('rank') -> select();
	
			if($have_been){
				foreach($have_been as $key => $val){
					$my_soft = $model -> table('sj_soft') -> where(array('package' => $val['package'],'status' => 1,'hide' => 1)) -> field('softid,softname,package,dev_name') ->  limit(1) -> order('softid DESC') -> select();
					$vals['softname'] = $my_soft[0]['softname'];
					$vals['package'] = $my_soft[0]['package'];
					$vals['rank'] = $val['rank'];
					$soft_result[$key] = $vals;
				}
				
				foreach($soft_result as $key => $val){
					$mob_down_result = $model -> table('sj_soft_statistics') -> where(array('package' => $val['package'])) -> select();
					$mob_down = $mob_down_result[0]['mob_dl_cnt'] + $mob_down_result[0]['mob_up_cnt'];
					$val['mob_down'] = $mob_down;
					$soft_result[$key] = $val;
				}
				
			}else{
				$soft_result = $model -> table('sj_soft') -> where(array('dev_id' => $_GET['dev_id'],'status' => 1,'hide' => 1)) -> field('softid,softname,package,dev_name') -> limit(1) -> order('softid DESC') -> select();
				foreach($soft_result as $key => $val){
					$mob_down_result = $model -> table('sj_soft_statistics') -> where(array('package' => $val['package'])) -> select();
					$mob_down = $mob_down_result[0]['mob_dl_cnt'] + $mob_down_result[0]['mob_up_cnt'];
					$val['mob_down'] = $mob_down;
					$soft_result[$key] = $val;
				}
				foreach($soft_result as $key => $val){
					$need_rank[] = $val['mob_down'];
				}
				array_multisort($need_rank, $soft_result);
				$soft_result = array_reverse($soft_result);
				foreach($soft_result as $key => $val){
					$val['rank'] = $key + 1;
					$soft_result[$key] = $val;
				}
			}
			
	
			$author_result = $model -> table('pu_developer') -> where(array('dev_id' => $_GET['dev_id'])) -> field('dev_name,dev_id') -> select();
			$this -> assign('soft_result',$soft_result);
			$this -> assign('dev_id',$_GET['dev_id']);
			$this -> assign('my_dev',$author_result);
		}
		$this -> display();
	}


	function change_rank(){
		$model = new Model();
		$search_author = $_POST['search_author'];
		$dev_id = $_POST['dev_id'];
		$package = $_POST['package'];
		$rank = $_POST['rank'];
		$p = $_POST['p'];
		$lr = $_POST['lr'];
		foreach($rank as $key => $val){
			if(!$val){
				$this -> assign('jumpUrl',"/index.php/Sj/Sameauthor/soft_list/dev_id/{$dev_id}/search_author/{$search_author}/p/{$p}/lr/{$lr}");
				$this -> error("请填写排序");
			}
		}
		$have_been = $model -> table('sj_sameauthor_soft') -> where(array('dev_id' => $dev_id)) -> select();

		if($have_been){
			$id_str = '';
			foreach($package as $key => $val){
				$where['package'] = $val;
				$data['rank'] = $rank[$key];
	
				$data['update_tm'] = time();
				$data['admin_id'] = $_SESSION['admin']['admin_id'];
				$id = $model->table('sj_sameauthor_soft')->where(array('package' =>$val))->field('id')->find();
				$log_go = $this -> logcheck(array('package' =>$val),'sj_sameauthor_soft',$data,$model);
				$log_result .= $log_go.',';
				$id_str = $id['id'].',';
				$update_result = $model -> table('sj_sameauthor_soft') -> where($where) -> save($data);

				$update_result_go[] = $update_result;
			}
			$id_str = substr($id_str,0,-1);
		}else{
			foreach($package as $key => $val){
				$data['package'] = $val;
				$data['dev_id'] = $dev_id;
				$data['rank'] = $rank[$key];
				
				$data['update_tm'] = time();
				$data['create_tm'] = time();
				$data['admin_id'] = $_SESSION['admin']['admin_id'];
				$add_result = $model -> table('sj_sameauthor_soft') -> add($data);
				$add_result_go[] = $add_result;
			}
		}

		if($update_result_go || $add_result_go){
			if($update_result){
				$this -> writelog("已编辑相同作者".$log_result,'sj_sameauthor_soft',$id_str,__ACTION__ ,"","edit");
			}
			if($add_result){
				$this -> writelog("已添加dev_id为{$dev_id}的相同作者软件排序",'sj_sameauthor_soft',$add_result,__ACTION__ ,"","add");
			}
			$this -> assign('jumpUrl',"/index.php/Sj/Sameauthor/soft_list/dev_id/{$dev_id}/search_author/{$search_author}/p/{$p}/lr/{$lr}");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}







}