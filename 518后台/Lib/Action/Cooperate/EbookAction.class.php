<?php
class EbookAction extends Action {

    //合作电子书管理
    function ebook_list(){
        $model = D('Cooperate.Ebook');
        import('@.ORG.Page2');
        $where = array();
        if(isset($_GET['bk_name'])){
            $this->assign('bk_name',$_GET['bk_name']);
            $where['a.bk_name'] = array('exp'," like '%{$_GET['bk_name']}%'");
        }
        if(isset($_GET['bk_class_a_name'])){
            $this->assign('bk_class_a_name',$_GET['bk_class_a_name']);
            $where['a.bk_class_a_name'] = array('exp'," like '%{$_GET['bk_class_a_name']}%'");
        }
        if(isset($_GET['ch_id'])){
            $this->assign('ch_id',$_GET['ch_id']);
            $where['b.ch_id'] = $_GET['ch_id'];
        }
        if(isset($_GET['ch_name'])){
            $this->assign('ch_name',$_GET['ch_name']);
            $where['b.ch_name'] = array('exp'," like '%{$_GET['ch_name']}%'");
        }
        if(isset($_GET['begintime'])){
            $this->assign('begintime',$_GET['begintime']);
            $where['b.ch_update'] = array('exp'," > '{$_GET['begintime']}'");
        }
        if(isset($_GET['endtime'])){
            $this->assign('endtime',$_GET['endtime']);
            $where['b.ch_update'] = array('exp'," < '{$_GET['begintime']}'");
        }

        $total = $model->table('coop_ebook_chapter as b')->join('coop_ebook as a on a.bk_id = b.bk_id')->where($where)->count();
        $Page = new Page($total,10);
        $field = 'a.bk_name,a.bk_class_a_name,b.*';
        $res = $model->table('coop_ebook_chapter as b')->join('coop_ebook as a on a.bk_id = b.bk_id')->limit($Page->firstRow.','.$Page->listRows)->where($where)->field($field)->order('b.ch_update desc')->select();
//        echo $model->getLastSql();
//        var_dump($res);
        $this->assign('res',$res);
        $this -> assign('page', $Page->show());
        $this -> assign('total', $total);
        $this->display();
    }

    //查看章节内容
    function get_book_content(){
        $id = $_GET['id'];
        if(!$id){
            $this->error('参数错误');
        }else{
            $model = D('Cooperate.Ebook');
            $where = array('b.id'=>$id);
            $con = $model->table('coop_ebook_chapter as b')->join('coop_ebook as a on b.bk_id = a.bk_id')->join('coop_ebook_content as c on b.bk_id = c.bk_id and b.ch_id = c.ch_id')->field('a.bk_name,a.bk_class_a_name,b.*,c.content')->where($where)->find();
//            echo $model->getLastSql();
//            var_dump($con);
            $this->assign('con',$con);
        }
        $this->display();
    }
}

?>