<?php

class TagManageAction extends CommonAction {
    private $true_table='new_tag';
    private $true_table_cat_tag='new_tag_cat_tag';
    private $true_table_new_cat_tag='sj_new_tag_cat_tag';
    private $true_table_tag_category='sj_new_tag_category';

    //yss
    public function tag_del(){
        $tag_id = trim($_POST['tag_id']);
        
        if(isset($tag_id) && !empty($tag_id)){
            if(strlen($tag_id)>=1){
                 $model = M($this->true_table);
                 // $res = $model->delete($tag_id);
                 $res = $model->where(array('tag_id'=>array('in',explode(',', $tag_id)),'soft_num'=>array('eq',0)))->save(array('status'=>0,'update_tm'=>time()));
                 if ($res) {
                    $this->writelog("标签表删除了tag_id为{$tag_id}的记录",'sj_new_tag',$tag_id,__ACTION__ ,"","del");
                    $get_c_id=$model->table($this->true_table_new_cat_tag)->where(array('tag_id'=>array('in',explode(',', $tag_id)),'status'=>1))->select();
                    
                    foreach($get_c_id as $v){
                        $re=$model->table($this->true_table_new_cat_tag)->where(array('tag_id'=>$v['tag_id'],'status'=>1))->save(array('status'=>0));
                        if($re){
                            $model->table($this->true_table_tag_category)->where(array('id'=>$v['c_id']))->save(array('tag_num'=>array('exp',"`tag_num`-1")));
                        }
                        
                    }
                    
                    $result = array ('success' => true, 'msg' =>'删除成功！');
                    echo json_encode ( $result );
                    exit ();
                 } else {
                    $result = array ('success' => false, 'msg' =>'删除失败！');
                    echo json_encode ( $result );
                    exit ();
                 }
            }else{
                $result = array ('success' => false, 'msg' =>'请选择要删除的标签！');
                echo json_encode ( $result );
                exit ();
            }
        }
    }
	public function tag_list(){ 
		
        $abc = trim($_GET['abc']); 
        $f_count = trim($_GET['f_count']); 
        $t_count = trim($_GET['t_count']); 
		$error_list = trim($_GET['error_list']); 
		
		$model = M($this->true_table);

		import('@.ORG.Page2');// 导入分页类
		$where = array();
		$where['status'] = 1;
		if($abc){
			$where['abc'] = $abc;
		}			
		$count = $model->where($where)->count();
		$count2 = $model->where("status=1")->count();
		// 实例化分页类 传入总记录数和每页显示的记录数
		$Page       = new Page($count,1000);
		// 分页显示输出
		$show       = $Page->show();
		 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$res = $model->where($where)->order('abc asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		//echo $model->getlastsql();
		$abc_arr = array('1','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		//统计个个列表总数
		$abc_count = $this -> pub_getcount($abc_arr);
		$this->assign('abc_arr',$abc_arr);
		$this->assign('abc_count',$abc_count);
		$this->assign('count',$count2);			
        $this->assign('abc',$abc);// 赋值数据集
        $this->assign('error_list',$error_list);// 赋值数据集
        $filename="/tmp/{$_SESSION['admin']['admin_id']}_error_list.log";
        if($t_count || $f_count){
            if(file_exists($filename)){
                $this->assign('show_div',1);// 赋值数据集
            }
        }else{
            $this->assign('show_div',0);// 赋值数据集
           
            if(file_exists($filename)){
                unlink($filename);
            }
            
        }
        $this->assign('t_count',$t_count);// 赋值数据集
        $this->assign('f_count',$f_count);// 赋值数据集
		$this->assign('list',$res);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this ->display();
    		
	}	

	//个个列表的总数据
	public function pub_getcount($abc_arr){
		$model = M($this->true_table);
        // unset($abc_arr[0]);
		// if(!S($this->true_table.$type)){
			$abc_count = array();
			foreach($abc_arr as $k => $v){
				$abc_count[$v['abc']] =  $model->where("abc = '{$v['abc']}' and status=1")->count();
				//写入缓存
				// S($this->true_table.$type,$abc_count,300);
			}
            // $abc_count[1] =  $model->where(array('status'=>1,'abc'=>array('not in',$abc_arr)))->count();
		// }else{
		// 	//读取缓存
		// 	$abc_count = S($this->true_table.$type);
		// }

		return $abc_count;
	}

    
    public function tag_add(){
        if(trim($_GET['batch'])==1){
            $this->import_csv();
            return;
        }
        if(trim($_POST['error_list'])){
            $this->down_csv();
            return;
        }
        $model = M($this->true_table);
        $tag_name=trim($_POST['tag_name']);
        $edit_tag_name=trim($_POST['edit_tag_name']);
        $tag_id=trim($_POST['tag_id']);
        if(!$tag_id){
            $re=$model->where(array('status'=>1,'tag_name'=>$tag_name))->find();
        }else{
            $res=$model->where(array('status'=>1,'tag_name'=>$edit_tag_name,'tag_id'=>array('neq',$tag_id)))->find();
        }
        
        if($re){
            $this->assign("jumpUrl","/index.php/Dev/TagManage/tag_list");
            $this -> success("添加成功");
        }else if($res){
            // $this -> error("编辑失败,标签名称已存在");
            $result = array ('success' => false, 'msg' =>"编辑失败,标签名称已存在");
            echo json_encode ( $result );
            exit ();
        }else{
            $data=array();
           
            if($tag_id){
                $data['tag_name']=$edit_tag_name;
                $data['update_tm']=time();
                $data['tag_id']=$tag_id;
                $data['abc']=$this-> getFirstCharter($edit_tag_name);
                $re=$model->save($data);
               
            }else{
                $data['tag_name']=$tag_name;
                $data['create_tm']=time();
                $data['abc']=$this-> getFirstCharter($tag_name);
                $re=$model->add($data);
                
            }
            
            // echo $model->getlastsql();
            if($re){
                if($tag_id){
                      $tag_data=$model->where(array('status'=>1,'tag_id'=>$tag_id))->find();
                      $this->writelog("标签表编辑了tag_id为{$tag_id}的记录,新标签名称为{$edit_tag_name},旧标签名称为{$tag_data['tag_name']}",'sj_'.$this->true_table,$tag_id,__ACTION__ ,"","edit");
                      $result = array ('success' => true, 'msg' =>"编辑成功");
                      echo json_encode ( $result );
                      exit ();
                }else{
                     $this->writelog("标签表新增了tag_id为{$re}的记录,标签名称为{$tag_name}",'sj_'.$this->true_table,$re,__ACTION__ ,"","add");
                     $this->assign("jumpUrl","/index.php/Dev/TagManage/tag_list");
                     $this -> success("添加成功");
                }
               
               
            }else{
                
                 if($tag_id){
                      $result = array ('success' => false, 'msg' =>"编辑失败");
                      echo json_encode ( $result );
                      exit ();
                }else{
                     $this -> error("添加失败");
                }
            }

        }
    }
    function getFirstCharter($str)
    {

        $abc = is_numeric(substr(Pinyin($str),0,1))?'1': substr(Pinyin($str),0,1);
        if(!in_array(strtoupper($abc), array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'))){
            return 1;
        }
        return strtoupper($abc);
    }
    //yss
    function down_csv(){
        
        $filename="{$_SESSION['admin']['admin_id']}_error_list.log";
        
        $lists=file_get_contents("/tmp/$filename");
        $lists=json_decode($lists,true);

        $filename='标签批量导入_'.date('Y_m_d_H:i:s').'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" .$filename );
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何信息');
        } else {
            if ($category == "nature") {
                $str = iconv('utf-8', 'gb2312', '标签名称')  . "," . iconv('utf-8', 'gb2312', '失败原因')."\r\n";
            }
            foreach ($lists as $key => $val) {
                
                    $str.= iconv('utf-8', 'gb2312', $val['tag_name']). "," . iconv('utf-8', 'gb2312',$val['error'])."\r\n";
               
            }
        }
        echo $str;
        exit;
    }

	public function batch_add($list,$error_list){
       
		$model = M($this->true_table);
		foreach($list as $k=>$v){
            $is_re=$model->where(array('status'=>1,'tag_name'=>$v['tag_name']))->find();
            if(!$is_re){
                $data=array();
                $data['tag_name']=$v['tag_name'];
                $data['abc']=$v['abc'];
                $data['create_tm']=time();
                
                $re=$model->add($data);
            }
        }
        $t_count=count($list);
        $f_count=count($error_list);
        if($f_count){
            
            $filename="{$_SESSION['admin']['admin_id']}_error_list.log";
            file_put_contents("/tmp/$filename", json_encode($error_list));
        }else{
            $error_list='';
            $this->assign("jumpUrl","/index.php/Dev/TagManage/tag_list");
            $this -> success("添加成功");
            return;
        }

        header("Location: /index.php/Dev/TagManage/tag_list/t_count/{$t_count}/f_count/{$f_count}/error_list/1/1/1"); 
        
        
	}

    public function import_csv(){
        
        $files_type1 = strtolower(substr(strrchr($_FILES ['batch_add_file']['name'], '.' ),1));

        if ($files_type1 == 'csv' && is_uploaded_file ($_FILES["batch_add_file"]["tmp_name"])) {
                $file = $_FILES ['batch_add_file']['tmp_name'];
                $list = array();
                $error_list = array();
                $contents = file_get_contents($file);
                $encoding = mb_detect_encoding($contents, array('GB2312','GBK','UTF-16','UCS-2','UTF-8','BIG5','ASCII'));
                $fp = fopen($file,"r");
                $arr_data=array();
                while(!feof($fp)){
                      $line = iconv($encoding,'utf-8',trim(fgets($fp)));
                      
                      $line=explode(',',$line);
                      $line=$line[0];
                      
                      $abc =$this->getFirstCharter($line);
                      if(!empty($line) && !in_array($line, $arr_data)){
                        $arr_data[]=$line;
                        if(mb_strlen($line,'utf8')>10){
                            $error_list[]=array('error'=>"10个字符以内",'tag_name'=>$line);
                        }else if(!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_-]+$/u",$line)){
                            $error_list[]=array('error'=>"标签格式：文字，英文字母，数字，特殊符号仅支持“-”“_”",'tag_name'=>$line);
                        }else{
                            $list[] = array('abc'=>strtoupper($abc),'tag_name'=>$line);
                        }
                      }
                } 
                
                fclose($fp);
                $this->batch_add($list,$error_list);
                
        }else{
            $this->error('批量导入文件必须为csv格式');
        }
    }

    //二级分类下标签管理功能
    public function cat_tag_list(){
        $c_id=$_GET['c_id'];
        $order=$_GET['order'];
        $model = M('');
        
        // var_dump($_GET['c_id']);
        if(!$c_id){
            $this->error('二级分类必传');
        }   
        $tag_category=$model->table($this->true_table_tag_category)->where(array('id'=>$c_id))->find();
        // echo $model->getlastsql();die;
        if(!$tag_category){
            $this->error('二级分类不存在');
        }  
        $where = array();
        $where['a.status'] = 1;
        $where['a.c_id'] = $c_id;      

        
        if($order==1){
            $res = $model->table("$this->true_table_new_cat_tag as a")->join('sj_new_tag b ON b.tag_id= a.tag_id')->where($where)->order('b.soft_num desc')->select();
            $this->assign('order',2);
        }else{
            $res = $model->table("$this->true_table_new_cat_tag as a")->join('sj_new_tag b ON b.tag_id= a.tag_id')->where($where)->order('b.soft_num asc')->select();
            $this->assign('order',1);
        }
        // echo $model->getlastsql();
        // var_dump($res);die;
        $this->assign('list',$res);
        $this->assign('category_name',$tag_category['name']);
        $this->assign('c_id',$tag_category['id']);
        $this ->display();
    }
	
    public function cancel_cat_tag_link(){
        $id = trim($_POST['id'])?trim($_POST['id']):trim($_GET['id']);
        $c_id =trim($_POST['c_id'])?trim($_POST['c_id']):trim($_GET['c_id']);
        $model = M($this->true_table_cat_tag);
        
        
        if(isset($id) && !empty($id)){
            if(strlen($id)>=1){
                
                 $id_arr=explode(',', $id);
                 $res = $model->where(array('id'=>array('in',$id_arr)))->save(array('update_tm'=>time(),'status'=>0));
                 if ($res) {
                    $this->writelog("标签 分类 关联表删除了id为{$id}的记录",$this->true_table_new_cat_tag,$id,__ACTION__ ,"","del");
                    $n=count($id_arr);
                    $model->table($this->true_table_tag_category)->where(array('id'=>$c_id))->save(array('tag_num'=>array('exp',"`tag_num`-{$n}")));
                    // echo $model->getlastsql();die;
                    if($_GET['id']){
                        $this->assign("jumpUrl","/index.php/Dev/TagManage/cat_tag_list/c_id/{$c_id}");
                        $this -> success("删除成功！");
                    }else{
                        $result = array ('success' => true, 'msg' =>'删除成功！');
                        echo json_encode ( $result );
                        exit ();
                    }
                    
                 } else {
                    if($_GET['id']){
                        $this->assign("jumpUrl","/index.php/Dev/TagManage/cat_tag_list/c_id/{$c_id}");
                        $this -> error("删除失败！");
                    }else{
                        $result = array ('success' => false, 'msg' =>'删除失败！');
                        echo json_encode ( $result );
                        exit ();
                    }
                    
                 }
            }else{
                $result = array ('success' => false, 'msg' =>'请选择要删除的标签！');
                echo json_encode ( $result );
                exit ();
            }
        }
    }
    public function cat_tag_add(){
       
        $model = M($this->true_table_cat_tag);
        $weight=trim($_POST['weight']);
        
        $tag_name=trim($_POST['tag_name']);
        $t=time();
        $id=trim($_POST['id']);
        $c_id=trim($_POST['c_id'])?trim($_POST['c_id']):trim($_GET['c_id']);
        if($_GET['add']==1){
            $this->assign('c_id',$c_id);
            $this->assign('from',$_GET['from']);

            $this->display();
        }
        $data=array();
        if(!preg_match("/^[0-9]+$/u",$weight)){
            if($id){
                  $result = array ('success' => false, 'msg' =>"权重必须为正整数");
                  echo json_encode ( $result );
                  exit ();
            }else{
                 $this->error("权重必须为正整数");
            }
            
        }
        if($id){
            $data['weight']=$weight;
            $data['update_tm']=$t;
            $data['id']=$id;
            $re=$model->save($data);
           
        }else{
            
            $tag_name_arr=explode(',', $tag_name);
            $tag_name_arr=array_filter($tag_name_arr); 
            if(count($tag_name_arr)<1){
                $this -> error("标签名称不能为空,多个标签名称需用','分隔");
            }
            $tag_id=array();
            foreach($tag_name_arr as $v){
                if(!empty($v)){
                    if(mb_strlen($v,'utf8')>10){
                        $this->error("10个字符以内");
                    }else if(!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_-]+$/u",$v)){
                        $this->error("标签格式：文字，英文字母，数字，特殊符号仅支持“-”“_”");
                    }
                }
                
            }
            foreach($tag_name_arr as $v){

               $tag_data=$model-> table('sj_new_tag')->where(array('tag_name'=>$v,'status'=>1))->find();
               if($tag_data){
                    $cat_tag_data=$model-> table($this->true_table_new_cat_tag)->where(array('tag_id'=>$tag_data['tag_id'],'c_id'=>$c_id,'status'=>1))->find();
                    if(!$cat_tag_data){
                        $tag_id[]=$tag_data['tag_id'];
                    }
                    
               }else{

                    $tag_id[]=$model-> table('sj_new_tag')->add(array('create_tm'=>$t,'tag_name'=>$v,'abc'=>$this-> getFirstCharter(trim($v))));
               }
            }
            foreach($tag_id as $v){
                $data=array();
                $data['weight']=$weight;
                $data['c_id']=$c_id;
                $data['tag_id']=$v;
                $data['create_tm']=$t;
                $c_tag_id=$model-> table($this->true_table_new_cat_tag)->add($data);
                $this->writelog("二级分类{$c_id}与标签tag_id为{$v}的标签关联",$this->true_table_new_cat_tag,$c_tag_id,__ACTION__ ,"","add");
            }
            
            $n=count($tag_id);
            $re=$model->table($this->true_table_tag_category)->where(array('id'=>$c_id))->save(array('tag_num'=>array('exp',"`tag_num`+{$n}")));
            // $re=$model->add($data);
            
        }
        
        
        if($re){
            if($id){
                  $result = array ('success' => true, 'msg' =>"编辑成功");
                  echo json_encode ( $result );
                  exit ();
            }else{
                if($_POST['from']=='tag_category'){
                    $this->assign("jumpUrl","/index.php/Dev/TagPackage/tag_category");
                    $this -> success("添加成功");
                }else{
                    $this->assign("jumpUrl","/index.php/Dev/TagManage/cat_tag_list/c_id/$c_id");
                    $this -> success("添加成功");
                }
                 
            }
           
           
        }else{
            
             if($id){
                  $result = array ('success' => false, 'msg' =>"编辑失败");
                  echo json_encode ( $result );
                  exit ();
            }else{
                 $this -> error("添加失败");
            }
        }

       
    }
}