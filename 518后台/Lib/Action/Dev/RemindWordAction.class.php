<?php
error_reporting(E_ALL);
ini_set('display_error','');
class RemindWordAction extends CommonAction {
    
    //添加提醒词
    public function addremind(){
        if($_POST){
            if($_POST['type_1'] == '填写监测词语，如多个请用分号分隔。'){
                $_POST['type_1'] = '';
            }
            if($_POST['type_2'] == '填写监测词语，如多个请用分号分隔。'){
                $_POST['type_2'] = '';
            }
            if($_POST['type_3'] == '填写监测词语，如多个请用分号分隔。'){
                $_POST['type_3'] = '';
            }
	        $_POST['type_1'] = str_replace('；',';',trim($_POST['type_1']));
	        $_POST['type_2'] = str_replace('；',';',trim($_POST['type_2']));
	        $_POST['type_3'] = str_replace('；',';',trim($_POST['type_3']));
	
	        for($i=0;$i<5;$i++){
	            if($_POST['user'][$i] && $_POST['email'][$i]){
	                $str .= $_POST['user'][$i].':'.$_POST['email'][$i].'|';
	            }
	        }
	        $data['usermail'] = $str;
	        $data['mailnum']  = $_POST['mailnum'];
	        $data['maxnum']   = $_POST['maxnum'];
	        $data['type']     = 1;
	        $type_1 = explode(';', $_POST['type_1']);
	        foreach ($type_1 as $v){
	             if($data['word'] = trim($v)) $this->updateremind($data);
	        }
	        $type_2 = explode(';', $_POST['type_2']);
	        $data['type']     = 2;
	        foreach ($type_2 as $v){
	             if($data['word'] = trim($v)) $this->updateremind($data);
	        }
	        $type_3 = explode(';', $_POST['type_3']);
	        $data['type']     = 3;
	        foreach ($type_3 as $v){
	            if($data['word'] = trim($v)) $this->updateremind($data);
	        }
	        $this->success('添加成功');
        }else{
            $this->display('addremind');
        }
        
    }
    public function updateremind($data){
        $model  = new Model();
        $word = $data['word'];
        $type = $data['type'];
        
        $result = $model->table('sj_soft_remindword')->where("type={$type} and word='{$word}' and status=0")->select();
        
		if($result){
			$log_result = $this->logcheck("type={$type} and word='{$word}'  and status=0",'sj_soft_remindword',$data,$model->table('sj_soft_remindword'));
		    $model->table('sj_soft_remindword')->where("type={$type} and word='{$word}'  and status=0")->save($data);
		    $this->writelog("信息提醒词编辑了word为{$word}的记录。{$log_result}",'sj_soft_remindword',"word:{$word}",__ACTION__ ,"","edit");
		}else{
		    $data['create_time'] = time();
		    $ret=$model->table('sj_soft_remindword')->add($data);
		    $this->writelog("信息提醒词添加id为{$ret}的记录",'sj_soft_remindword',$ret,__ACTION__ ,"","add");
		}
    }
    
    //编辑提醒词
    public function editremind(){
		$model  = new Model();
		if($_POST){
		    for($i=0;$i<5;$i++){
		        if($_POST['user'][$i] && $_POST['email'][$i]){
		            $str .= $_POST['user'][$i].':'.$_POST['email'][$i].'|';
		        }
		    }
		    $data['usermail'] = $str;
		    $data['type']     = $_POST['type'];
		    $data['word']     = $_POST['word'];
		    $data['mailnum']  = $_POST['mailnum'];
		    $data['maxnum']   = $_POST['maxnum'];		    
		    $this->updateremind($data);
		    $this->success('编辑成功！');
		    
		}else{
			$id = intval($_GET['id']);
			
			$result = $model->table('sj_soft_remindword')->where("id = {$id}")->select();
			$data   = $result[0];
			$data['usermail'] = explode('|', $data['usermail']);
			foreach ($data['usermail'] as $v){
			  if($v){
			      $v = explode(':', $v);
			      $usermail[] = $v;			      
			  }
			}
			$this->assign('usermail_num',count($usermail));
			$this->assign('usermail',$usermail);
			$this->assign('data',$data);			
			$this->display('editremind'); 
		}		
    }

    //删除提醒词
    public function delremind(){
		$model  = new Model();
		$data = array();
		$id = $_GET['id'];
		if($_GET['type'] == 'clearnum'){		   
		   $data['num'] = 0;	
		   $data['sendnum'] = 0;
		}elseif($_GET['type'] == 'delword'){		   
		   $data['status'] = 1;           
        }		
		$id_arr =  explode(',',$id);
		$where['id'] = array('exp',"in ({$id})");
	    $result = $model->table('sj_soft_remindword')->where($where)->save($data); 

	   	if(1||$result){
	   		$this->writelog("信息提醒词删除id为{$id}的记录",'sj_soft_remindword',$id,__ACTION__ ,"","del");
			exit(json_encode(array('code'=>1,'msg'=>'操作成功！')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'操作失败请重试！')));
		}
    }
    
    //列表
    public function listremind(){

        $model  = new Model();
        import('@.ORG.Page2');
        if($_GET){
            $word = trim($_GET['word']);
            if($word){
                $where['word'] = array("exp","like '%{$word}%'") ;
                $this -> assign('word',$word);
            }
            $user = trim($_GET['user']);
            if($user){
                $where['usermail'] = array("exp","like '%{$user}%'") ;
                $this -> assign('user',$user);
            }
            $email = trim($_GET['email']);
            if($email){
                $where['usermail'] = array("exp","like '%{$email}%'") ;
                $this -> assign('email',$email);
            }
            if($_GET['type']){
                $where['type'] = $_GET['type'] ;
                $this -> assign('type',$_GET['type']);
            }
        }
        $where['status'] = 0;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $total = $model->table('sj_soft_remindword')->where($where)->count();
        $page = new Page($total, $limit);
        $remindlists = $model->table('sj_soft_remindword')->where($where)->order('id  desc')->limit($page->firstRow.','.$page->listRows)->select();
        if($user || $email){
            foreach ($remindlists as $v){
                if($user){
                    if(strstr($v['usermail'],$user)){
                        $v['usermail']=preg_replace("/{$user}/i", "<font color=yellow><b>{$user}</b></font>",$v['usermail']);
                    }  
                }
                if($email){
                    if(strstr($v['usermail'],$email)){
                        $v['usermail']=preg_replace("/{$email}/i", "<font color=yellow><b>{$email}</b></font>",$v['usermail']);
                    }
                }
                $remindlist[] = $v;
            }
        }
        $remindlist = $remindlist ? $remindlist :$remindlists;
        $this->assign('remindlists',$remindlist);
		$param = http_build_query($_GET);
		$this -> assign('param',$_GET);
        $page->setConfig('header','篇记录');
        $page->setConfig('first','首页');
        $page->setConfig('last','尾页');			
		$this -> assign('page', $page->show());
		$this -> assign('count', $total);
		$param = http_build_query($_GET);
		$this -> assign('param',$param);
        $this->display('listremind');        
    }

	public function checkuser(){
		$model  = new Model();
		$result = $model->table('sj_admin_users')->where("admin_state=1 and admin_user_name='".trim($_POST['user'])."'")->select();
		if($result){
			echo 1;
		}else{
			echo 0;
		}
		exit;
	}
}