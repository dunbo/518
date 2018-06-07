<?php
class FeedbackFilterAction extends CommonAction {
    public function filterlist(){
        import("@.ORG.Page2");
        $model = M("feedback_filter");        
        $admin_users = M("admin_users");
		$where = array('status' => 0);
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$this->check_where($where, 'feedbackword', 'isset', 'like');
		$this->check_where($where, 'sectiontypeid');
		$this->check_where($where, 'backtype');
		$this->check_range_where($where, 'start_time', 'end_time', 'update_time', true);
		!isset($_GET['pid']) ? $_GET['pid'] =1 : $_GET['pid'];
		$this->check_where($where, 'pid');		
        $count1 = $model->where('status=0')->count();
        $param = http_build_query($_GET);
        $count = $model->where($where)->count();
        $Page = new Page($count, $limit, $param);
        $list = $model  -> where($where) ->order('rank asc')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
        $feedbacktype = C('feedbacktype');   
        $question_type = get_table_data(array('status'=>1),"sj_feedback_question","id","id,question");
        $this->assign('question_type', $question_type);
        $this->assign('feedbacktype',$feedbacktype);
        //var_dump($list);
        foreach($list as $key=>$value){
            $str = null;
            $filter = null;
            if($value['filter']) $filter = '且包含';
            else   $filter = '或者包含';

            if(empty($feedbacktype[$value['backtype']])&&!empty($value['backtype'])){
                $feedback = $model->table('sj_feedback_question')->where(array('id'=>$value['backtype']))->field('question')->find();
                $value['backtype'] && $str = '反馈主题：'.$feedback['question'].'<br />'.$filter.':<br />';
            }else{
                $value['backtype'] && $str = '反馈主题：'.$feedbacktype[$value['backtype']].'<br />'.$filter.':<br />';
            }
            
            $feedbackword = explode('||',$value['feedbackword']);

            foreach($feedbackword as $v){
                if($v){
                    $str .= '('.$v.')或者';
                }
            }
            $str = mb_substr($str, 0, -2, 'utf-8');
            $value['feedbackword']&&$str.='<br />';
            
            if($value['wordlenght']!=0){
                $str .= $filter.':<br /> 英文及特殊字符的长度大于'.$value['wordlenght'].'字';
            }
            
            $list[$key]['filterword'] = $str;  
            if($value['admin_id']) $adminid[] = $value['admin_id'];
        }
        
        if($adminid){
            $admin_user_name = $admin_users->where(array('admin_user_id'=>array('in',$adminid)))->field('admin_user_name,admin_user_id')->select();
            $username = array();
            foreach($admin_user_name as $k=>$v){
                $username[$v['admin_user_id']] = $v['admin_user_name'];
            }
        }
        $this->assign('username',$username);
        $show = $Page->show();
        $this->assign("page", $show);
        $this->assign('num',$count1+1);
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('sectiontype',C('sectiontype'));
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);				
        $this->display();
    }
    
    public function addfilter() {
        
        if(!empty($_POST)){
            $filter = M("feedback_filter");
			$data = array();
            $data['filter_name'] = trim($_POST['filter_name']);
            $data['rank'] = intval($_POST['rank']);
            $data['sectiontypeid'] = $_POST['sectiontypeid']?intval($_POST['sectiontypeid']):'';
            $data['filter'] = intval($_POST['filter']);
            $data['backtype'] = intval($_POST['backtype']);           
            $data['wordlenght'] = intval($_POST['wordlenght']);
            $data['pid'] = intval($_POST['pid']);
            $data['admin_id'] = $_SESSION['admin']['admin_id'];
            $data['update_time'] = time();
            $word = '';
            if(!empty($_POST['feedbackword'])){
                foreach($_POST['feedbackword'] as $feedbackword){
                    if($feedbackword) $word .= $feedbackword.'||';                    
                }
                $word = str_replace('；', ';', $word);
                $word = substr($word,0,-2);
                
            }
            if($word&&$word!=""){
                $data['feedbackword'] = $word;
            }
            $where = array('status'=>0,
                    'rank'=>array('exp',">={$data['rank']}"));
            $filter->where($where)->save(array('rank'=>array('exp','rank+1'))); 
            $result = $filter->add($data);
            //var_dump($result);
//            echo $filter->getlastsql(); exit;
            if($result){
                $this->writelog('添加了ID为[' . $result . ']的软件反馈分部门规则', 'sj_feedback_filter', $result,__ACTION__ ,"","add");
                $this->success('添加成功');
            }else{
                $this->success('添加失败');
            }
            
        }
    }
    
    public function editfilter() {
        if(!empty($_POST)){
            $filter = M("feedback_filter");
            $data['filter_name'] = $_POST['filter_name']?trim($_POST['filter_name']):'';
            $data['sectiontypeid'] = $_POST['editsectiontypeid']?intval($_POST['editsectiontypeid']):'';
            $data['filter'] = intval($_POST['editfilter']);
            $data['backtype'] = intval($_POST['editbacktype']);           
            $data['wordlenght'] = intval($_POST['editwordlenght']);
            $data['admin_id'] = $_SESSION['admin']['admin_id'];
            $data['update_time'] = time();
			$data['pid'] = intval($_POST['exitpid']);
            $word = '';
            if(!empty($_POST['editfeedbackword'])){
                foreach($_POST['editfeedbackword'] as $feedbackword){
                    if($feedbackword) $word .= $feedbackword.'||';                    
                }
                $word = str_replace('；', ';', $word);
                if($word){
                $word = substr($word,0,-2);
            }
            }
            $data['feedbackword'] = $word;
            $where = "id = ".$_POST['filterid'];
            $log_result = $this->logcheck($where,'sj_feedback_filter',$data,$filter);
            $result = $filter->where($where)->save($data);
            $rs = $this->changerank($_POST['filterid'], $_POST['editrank'],2);

            if($result){
                $this->writelog('编辑了ID为[' . $_POST['filterid'] . ']的软件反馈分部门规则.'.$log_result, 'sj_feedback_filter', $_POST['filterid'],__ACTION__ ,"","edit");
                $this->success('更新成功');
            }else{
                $this->success('更新失败');
            }
            
        }        
    }

    
    public function del_filter(){
        $filter = M("feedback_filter");
        if(isset($_GET['id'])&&$_GET['id']!=''){
            $where = "id = ".$_GET['id'];
            $data['status'] = 1;
            $result = $filter->where($where)->save($data);
            $row = $filter->where("id={$_GET['id']}")->field('id,rank,pid')->find();
            $where = array(
				'status'=>0,
                'rank'=>array('exp',">{$row['rank']}"),
				'pid' => $row['pid']
			);
            $filter->where($where)->save(array('rank'=>array('exp','rank-1')));           
            $this->writelog('删除了ID为[' . $_GET['id'] . ']的软件反馈分部门规则', 'feedback_filter', $_GET['id'],__ACTION__ ,"","del");
            $this->success('删除成功');            
        }else{
            $this->success('获取id失败');
        }
    }
    public function  filter_edit(){
        //echo $_POST['id'];
        $model = M("feedback_filter");   
        $where = 'status=0 and id = "'.$_POST['id'].'"';
        $list = $model  -> where($where) -> find();
        echo json_encode($list);
    }
    public function changerank($id,$rank,$tag='1'){
        if($tag == '1'){
             $id = intval($_GET['id']);
             $rank = intval($_GET['rank']);          
        }

        $filter = M("feedback_filter");
        $data = $filter->where("id={$id}")->field('id,rank')->find();
        if($data['rank']>$rank){//向上移动
            $where = array('status'=>0,'rank'=>array('exp',">={$rank} and rank <{$data['rank']}"));

            $filter->where($where)->save(array('rank'=>array('exp','rank+1')));
        }elseif($data['rank']<$rank){//向下移动
            $where = array('status'=>0,'rank'=>array('exp',"<={$rank} and rank >={$data['rank']}"));

            $filter->where($where)->save(array('rank'=>array('exp','rank-1')));
        }
        $log_result = $this->logcheck(array('id'=>$id),'sj_feedback_filter',array('rank'=>$rank),$filter);
        $result = $filter->where("id={$id}")->save(array('rank'=>$rank));
        if($result && $log_result){
            $this->writelog("编辑ID为[ {$id} ]的软件反馈分部门规则排序为{$log_result}", 'sj_feedback_filter', $id,__ACTION__ ,"","edit");
        }
        if($tag==2){
            return $result;
        }else{
            if($result) echo  1;
            else echo 0;                   
        }        
    }


}