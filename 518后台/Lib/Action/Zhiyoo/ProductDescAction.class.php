<?php 

class ProductDescAction extends CommonAction{
    
    
    function listShow(){
        $editrank = $_GET['editrank']?$_GET['editrank']:0;
        $upid = $_GET['upid'] ? intval($_GET['upid']):0;
        $map = array('status'=>1,'upid'=>$upid);
        $model = D('Zhiyoo.ProductDescription');
        $res = $model -> where($map)->order(array('rank'=>'asc'))->select();
        $this->assign('list',$res);
        $this->assign('upid',$upid);
        $this->assign('editrank',$editrank);
        $this->display();
    }
    
    
    function add(){
        $upid = $_REQUEST['upid'] ? intval($_REQUEST['upid']) : 0;
        $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
        $list = array();
        $model = D('Zhiyoo.ProductDescription');
        if($id > 0){
            $list = $model->where(array('id'=>$id,'status'=>1))->find();
            if(!$list) $this->error('文案不存在或已删除');
            $upid = $list['upid'];
        }
        if($_POST['addsumbit']){
            if(!$_POST['name']) $this->error('展示名称不能为空');
            if(!$_POST['instruction']) $this->error('位置说明不能为空');
            if(!$_POST['content']&&$upid>0) $this->error('内容详情不能为空');
            $data = array(
                'name'=>$_POST['name'],
                'instruction'=>$_POST['instruction'],
                'content'=>$_POST['content']?$_POST['content']:'',
                'upid'=>$upid,
            );
           if(!$id){               
                $res = $model->add($data);
                if($res){
                    $this -> writelog('众测文案添加成功，id为'.$res,$model->getTableName(),$res,__ACTION__ ,"","add");
                    $this->success('添加成功');
                }   
           }else{
               $res = $model->where(array('id'=>$id))->save($data);
                $this -> writelog('众测文案编辑成功，id为'.$id,$model->getTableName(),$id,__ACTION__ ,"","edit");
                $this->success('编辑成功');
               
           }
            
            $this->error('提交失败');
            
        }else{
            $this->assign('list',$list);
            $this->assign('upid',$upid);
            $this->assign('id',$id);
            $this->display();
        }
        
    }
    
    function editRank(){
        $upid = $_REQUEST['upid'] ? intval($_REQUEST['upid']) : 0;
        $model = D('Zhiyoo.ProductDescription');
        $ids = '';
        foreach($_POST['rank'] as $id=>$val){
            $val = (int) $val;
            $model->save(array('rank'=>$val,'id'=>$id));
            $ids .= $id.',';
        }
        $ids = substr($ids,0,-1);
          $this -> writelog('众测文案编辑优先级成功，id为'.$ids,$model->getTableName(),$ids,__ACTION__ ,"","edit");
        $this->assign('jumpUrl',"__URL__/listShow/upid/$upid/");
        $this->success('优先级编辑成功');
    }
    
    function listDel(){
        $model = D('Zhiyoo.ProductDescription');
        if($_GET['id']>0){
            $id = $_GET['id'];
        }else{
            $this->error("删除失败，文案不存在");
        }
        $this -> writelog('众测文案删除成功，id为'.$id,$model->getTableName(),$id,__ACTION__ ,"","delete");
        $model->save(array('status'=>0,'id'=>$id));
        $model->save(array('status'=>0,'upid'=>$id));
        $this->success('删除文案成功');
    }
    
    
    
    
    
    
    
    
    
    
    
    
}