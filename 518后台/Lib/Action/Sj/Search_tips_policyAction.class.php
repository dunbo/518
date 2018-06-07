<?php
    class Search_tips_policyAction extends CommonAction {

        //软件列表
        public function keyword_list(){
            $model = M('search_tips_policy');		
          
            //print_r($list);exit;
            import("@.ORG.Page");
            $param = http_build_query($_GET);
            $limit = 10;
            $count_total = $model -> where(array('status' => 1))->count();
            $page  = new Page($count_total, $limit, $param);
            $list = $model->table('sj_search_tips_policy')->where(array('status' => 1))->field('id,search_keywords,content,type')->order('type desc,create_tm desc')->limit($page->firstRow . ',' . $page->listRows)->select();
            $this->assign('list', $list);
            $page->setConfig('header', '篇记录');
            $page->setConfig('first', '<<');
            $page->setConfig('last', '>>');
            $this->assign("page", $page->show());

            $this->display();
        }
       //添加软件
        public function add_keyword() {
            if(isset($_POST['submit']))
            {
               // var_dump($_POST);exit;
                    $model = M('search_tips_policy');
                    $data = array(
                            'type' => 1,
                            'status' => 1,
                            'create_tm' => strtotime("now"),
                            'update_tm' => strtotime("now"),
                            'content' => $_POST['content'],
                            'search_keywords' => $_POST['search_keywords'],
							'admin_id'=>$_SESSION['admin']['admin_id'],
                            );
                            //var_dump($data);exit;
                    $have_been = $model -> where(array('search_keywords' => $data['search_keywords'])) -> select();
                    if($have_been){
                            $this -> error("该搜索热词已经存在");
                    }

                    $res = $model->add($data);
                    $this->writelog("在政策性搜索提示中添加了关键词{$_POST['search_keywords']}前端显示文案为{$_POST['content']}", 'sj_search_tips_policy',$res,__ACTION__ ,"","add");
            }
            // 显示表单
            $this->redirect("keyword_list");
        }
        //编辑软件
        public function edit_keyword() {
        	$model = M('search_tips_policy');
        	
        	if (!empty($_POST)) {
        		$model = M('search_tips_policy');
        		$id = $_REQUEST['id'];
        		$data = array(
					'update_tm' => strtotime("now"),
					'content' => $_POST['content'],
					'search_keywords' => $_POST['search_keywords'],
					'admin_id'=>$_SESSION['admin']['admin_id'],
					);
                        $have_where['_string'] = "search_keywords = '{$data['search_keywords']}' and id != {$id}";
                        $have_been = $model -> where($have_where) -> select();

                        if($have_been){
                            // $this->redirect("/index.php/Sj/Search_tips_policy/edit_keyword/id/".$id);
                            
                                $this->error('该搜索热词已经存在');
                        }
                        $result = $model->where(array('id' => $id))->select();
                        $conten111 = $result[0]['content'];
                        $search_keywords111 = $result[0]['search_keywords'];
        		$res = $model->where(array('id' => $id))->save($data);
                        $this->writelog("政策性搜索提示:关键词由[{$result[0]['search_keywords']}]编辑为[{$_POST['search_keywords']}],前端显示文案由[".$conten111."]编辑为[".$_POST['content']."]", "sj_search_tips_policy",$id,__ACTION__ ,"","edit");
                        $this->redirect("keyword_list");
        	}
                if ($_REQUEST['id']) {
        		$thisid = $_REQUEST['id'];
        		$result = $model->where(array('id' => $thisid))->select();
                       // var_dump($result[0]['search_keywords']);z
        		$this->assign('val',$result);
                        $this->display();
        	}
        	
        }
        //删除软件
        public function del() {
            if ($_GET) {
                 $id = $_GET['id'];
                 $model = M();
                 $data = array(
                         'status' => 0,
						 'update_tm' => time(),
                         );
                 $ret = $model->table('sj_search_tips_policy')->where(array('id'=>$id))->save($data);
                 if ($ret) {
                     $this->writelog("在政策性搜索提示中删除了id为[$id]的关键词", 'sj_search_tips_policy', $id,__ACTION__ ,"","del");
                     $this->success("删除成功！");
                 } else {
                     $this->error("删除失败！");
                 }
             }
        }
        
        // 改变状态
        public function change_type() {
        	if ($_GET) {
                    $id = $_GET['id'];
                    $type = $_GET['type'];
                    $model = M();
                    if($type ==1){
                        $data = array(
                            'type' => 0,
                        );
                    }else{
                         $data = array(
                            'type' => 1,
                        );
                    }

                    $ret = $model->table('sj_search_tips_policy')->where(array('id'=>$id))->save($data);
                    if ($ret) {
                         $this->writelog("在政策性搜索提示中更改了id为[$id]的关键词的状态", 'sj_search_tips_policy', $id,__ACTION__ ,"","edit");
                         $this->success("修改状态成功！");
                    } else {
                         $this->error("修改状态失败！");
                    }
            }
 
        }  
    }
