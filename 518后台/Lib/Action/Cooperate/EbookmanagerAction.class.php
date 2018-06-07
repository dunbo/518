<?php
class EbookmanagerAction extends CommonAction {
    //逐浪电子书管理
    function ebook_manager_list(){
        $model = D('Cooperate.Ebook');
        import('@.ORG.Page2');
        $where = array();
        if(isset($_GET['bk_name'])){
            $this->assign('bk_name',$_GET['bk_name']);
            $where['bk_name'] = array('exp'," like '%{$_GET['bk_name']}%'");
        }
        if(isset($_GET['bk_author'])){
            $this->assign('bk_author',$_GET['bk_author']);
            $where['bk_author'] = array('exp'," like '%{$_GET['bk_author']}%'");
        }
        if(isset($_GET['bk_id'])){
            $this->assign('bk_id',$_GET['bk_id']);
            $where['bk_id'] = $_GET['bk_id'];
        }
        if(isset($_GET['book_vip_type'])&&$_GET['book_vip_type']!=0){
            $this->assign('book_vip_type',$_GET['book_vip_type']);
            $where['book_vip_type'] = $_GET['book_vip_type'];
        }
        if(isset($_GET['begintime'])){
            $this->assign('begintime',$_GET['begintime']);
            $where['bk_last_ch_update'] = array('exp'," > '{$_GET['begintime']}'");
        }
        if(isset($_GET['bk_last_ch_update'])){
            $this->assign('endtime',$_GET['endtime']);
            $where['bk_last_ch_update'] = array('exp'," < '{$_GET['endtime']}'");
        }
        if(isset($_GET['bk_class_a'])){
            $this->assign('bk_class_a',$_GET['bk_class_a']);
            $where['bk_class_a'] = $_GET['bk_class_a'];
        }
        if(isset($_GET['status'])){
            $this->assign('status',$_GET['status']);
            $where['status'] = $_GET['status'];
        }else{
             $this->assign('status',1);
            $where['status'] = 1;
        }
        $total = $model->table('coop_ebook')->where($where)->count();
        // var_dump($total);
        $Page = new Page($total,10);
        $field = '*';
        if(isset($_GET['order'])){
            $str=$_GET['order'].'  ';
            $str.=($_GET['sta']==1)?'desc':'asc';
            $this->assign('sta',$_GET['sta']);
            $this->assign('order',$_GET['order']);
        }
        $res = $model->table('coop_ebook')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order($str)->field($field)->select();
        foreach($res as $k=>$v){
            if($v['bk_last_ch_update']){
                $res[$k]['bk_last_ch_update2']=date("Y-m-d H:i:s", $v['bk_last_ch_update']);
            }
            if($v['bk_cre_date']){
                $res[$k]['bk_cre_date2']=date("Y-m-d H:i:s", $v['bk_cre_date']);
            }
        }
        $category = $model->table('coop_ebook_category')->select();
        $this->assign('category',$category);
        // echo $model->getLastSql();
        // echo "<pre>";var_dump($res);die;
        $this->assign('res',$res);
        $this -> assign('page', $Page->show());
        $this -> assign('total', $total);
        $this->display();
    }
    //审核状态
    function news_release() {
      $passed = $_GET['passed'];
      $model = D('Cooperate.Ebook');
      if($_GET['biaoshi']==1){
        $id_arr = explode(',',$_GET['ids']);
        $id=$_GET['ids'];
      }else{
        $id = $_GET['id'];
        $id_arr=array($id);
      }
      $data['status'] = $passed;
      $result = $model->table('coop_ebook')->where(array('id' => array('in',$id_arr)))->save($data);
      // echo $model->getLastSql();die;
      if($passed==1){
        $str='通过';
      }else{
        $str='撤销';
      }
      if ($result) {
        $this->writelog("电子书_已{$str}id为{$id}的电子书",'coop_ebook',$id,__ACTION__ ,'','edit');
        echo 1;
      }else{
        echo 2;
      }
    }
    function batch_set_vip(){
        $model = D('Cooperate.Ebook');
        if($_GET){
            $ids=explode(',', $_GET['bk_ids']);
            $this -> assign('count', count($ids));
            $this -> assign('bk_ids', $_GET['bk_ids']);
            $this->display();
        }
        if($_POST){
            $ids=explode(',', $_POST['bk_ids']);
            $result = $model->table('coop_ebook')->where(array('id' => array('in',$ids)))->save(array('book_vip_type'=>$_POST['book_vip_type']));
            if ($result) {
                $this->writelog("电子书_id为{$_POST['bk_ids']}的电子书批量设置全书为{$_POST['book_vip_type']}，1为全书免费，2为全书vip",'coop_ebook',$_POST['bk_ids'],__ACTION__ ,'','edit');
                echo 1;
             }else{
                echo 2;
             }
        }
    }
    function set_vip(){
        $model = D('Cooperate.Ebook');
        if($_GET){
            $res = $model->table('coop_ebook')->where(array('id'=>$_GET['id']))->find();
            $count = $model->table('coop_ebook_chapter')->where(array('bk_id'=>$res['bk_id']))->count();
            $this -> assign('count', $count);
            $this -> assign('res', $res);
            $this -> assign('id', $_GET['id']);
            $this->display();
        }
        if($_POST){
            $id= $_POST['id'];
            $anzhi_bk_name= trim($_POST['anzhi_bk_name']);
            $book_vip_type= $_POST['book_vip_type'];
            $bk_vip_ch_id_start= trim($_POST['bk_vip_ch_id_start']);
            if(!(is_numeric($bk_vip_ch_id_start)&&$bk_vip_ch_id_start==(int)$bk_vip_ch_id_start) || $bk_vip_ch_id_start <0){
                echo 3;
                return;
            }
            $bk_vip_ch_id_start_old= trim($_POST['bk_vip_ch_id_start_old']);
            $anzhi_bk_name_old= trim($_POST['anzhi_bk_name_old']);
            $result = $model->table('coop_ebook')->where(array('id' => $id))->save(array('book_vip_type'=>$_POST['book_vip_type'],'bk_vip_ch_id_start'=>$bk_vip_ch_id_start,'anzhi_bk_name'=>$anzhi_bk_name));
            if ($result) {
                $this->writelog("电子书_id为{$_POST['bk_ids']}的电子书批量设置全书为{$_POST['book_vip_type']}，1为全书免费，2为全书vip.书籍安智名称由{$anzhi_bk_name_old}变为{$anzhi_bk_name}，书籍vip开始章节由{$bk_vip_ch_id_start_old}变为为：{$bk_vip_ch_id_start}",'coop_ebook',$_POST['bk_ids'],__ACTION__ ,'','edit');
                echo 1;
             }else{
                echo 2;
             }
        }
    }
    //合作电子书管理
    function chapter_list(){
        $model = D('Cooperate.Ebook');
        import('@.ORG.Page2');
        $where = array();
        if(isset($_GET['bk_id'])){
            $this->assign('bk_id',$_GET['bk_id']);
            $where['a.bk_id'] = $_GET['bk_id'];
        }
        
        $total = $model->table('coop_ebook_chapter as b')->join('coop_ebook as a on a.bk_id = b.bk_id')->where($where)->count();
        $Page = new Page($total,10);
        $field = 'a.bk_name,a.bk_class_a_name,b.*';
        if($_GET['order']){
            $str = ($_GET['order']==1)?' b.ch_id desc,':' b.ch_id asc,';
            $this -> assign('sta', $_GET['order']);
        }
        $res = $model->table('coop_ebook_chapter as b')->join('coop_ebook as a on a.bk_id = b.bk_id')->limit($Page->firstRow.','.$Page->listRows)->where($where)->field($field)->order("$str b.ch_update desc")->select();
        // echo $model->getLastSql();
        // var_dump($res);
        $this->assign('res',$res);
        $this -> assign('page', $Page->show());
        $this -> assign('total', $total);
        $this->display();
    }
    //合作电子书标签内容管理
    function label_content_manager(){
        $model = D('Cooperate.Ebook');
        import('@.ORG.Page2');
        $where = array();
        if(isset($_GET['bk_name'])){
            $this->assign('bk_name',$_GET['bk_name']);
            $where['bk_name'] = array('exp'," like '%{$_GET['bk_name']}%'");
        }
        if(isset($_GET['bk_author'])){
            $this->assign('bk_author',$_GET['bk_author']);
            $where['bk_author'] = array('exp'," like '%{$_GET['bk_author']}%'");
        }
        if(isset($_GET['bk_id'])){
            $this->assign('bk_id',$_GET['bk_id']);
            $where['bk_id'] = $_GET['bk_id'];
        }
        if(isset($_GET['book_vip_type'])&&$_GET['book_vip_type']!=0){
            $this->assign('book_vip_type',$_GET['book_vip_type']);
            $where['book_vip_type'] = $_GET['book_vip_type'];
        }
        if(isset($_GET['begintime'])){
            $this->assign('begintime',$_GET['begintime']);
            $where['bk_last_ch_update'] = array('exp'," > '{$_GET['begintime']}'");
        }
        if(isset($_GET['bk_last_ch_update'])){
            $this->assign('endtime',$_GET['endtime']);
            $where['bk_last_ch_update'] = array('exp'," < '{$_GET['endtime']}'");
        }
        if(isset($_GET['bk_class_a'])){
            $this->assign('bk_class_a',$_GET['bk_class_a']);
            $where['bk_class_a'] = $_GET['bk_class_a'];
        }
        if(isset($_GET['status'])){
            $this->assign('status',$_GET['status']);
            $where['status'] = $_GET['status'];
        }else{
             $this->assign('status',1);
            $where['status'] = 1;
        }
        $total = $model->table('coop_ebook')->where($where)->count();
        // var_dump($total);
        $Page = new Page($total,10);
        $field = '*';
        if(isset($_GET['order'])){
            $str=$_GET['order'].'  ';
            $str.=($_GET['sta']==1)?'desc':'asc';
            $this->assign('sta',$_GET['sta']);
            $this->assign('order',$_GET['order']);
        }
        $res = $model->table('coop_ebook')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order($str)->field($field)->select();
        foreach($res as $k=>$v){
            if($v['bk_last_ch_update']){
                $res[$k]['bk_last_ch_update2']=date("Y-m-d H:i:s", $v['bk_last_ch_update']);
            }
            if($v['bk_cre_date']){
                $res[$k]['bk_cre_date2']=date("Y-m-d H:i:s", $v['bk_cre_date']);
            }
        }
        $category = $model->table('coop_ebook_category')->select();
        $this->assign('category',$category);
        // echo $model->getLastSql();
        // echo "<pre>";var_dump($res);die;
        $this->assign('res',$res);
        $this -> assign('page', $Page->show());
        $this -> assign('total', $total);
        $this->display();
    }
    
}

?>