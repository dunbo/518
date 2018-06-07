<?php
/*
 * 流量签到活动管理
 */
class SignFlowAction extends CommonAction {

	private $activity;
	private $link_pre;
	private $link_path;
	private $link_log_url;

	public function __construct() {
		parent::__construct();
		$this->activity = D('sendNum.Activity');			
		$this->link_pre = 'http://fx.anzhi.com/activity/activity_page/';
		$this->link_path = ACTIVITY_PAGE;
	}

	public function produceList() {
		$model = D('sendNum.Activity');	
		$where = array(
			'activate_type' => 16,
			'status' => 1
		);
		$this->check_where($where, 'ap_name', 'isset', 'like');
		$this->check_where($where, 'ap_id', 'isset');
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		list($resetVal,$total,$Page) = $model -> get_activity_page($where,$limit);
		$resetVal = $this->resetVal($resetVal);
		$produceList = $resetVal;
		
		if(EVN == '9test' || EVN == 'prod'){
			$my_host = 'http://fx.anzhi.com';
		}elseif(EVN == '518test'){
			$my_host = ACTIVITY_URL;
		}
		
		$this -> assign('lr',$_GET['lr']?$_GET['lr']:20);
		$this -> assign('my_host',$my_host);
		$this -> assign('page', $Page->show());
		$this->assign('jsonDATA', json_encode($resetVal));
		$this->assign('produceList', $produceList);
		$this->display('produceList');
	}

	private function resetVal($produceList) {	
		$pkg = array();
		foreach ($produceList as $key => $val) {	
			if ($val['ap_link']) {
				$produceList[$key]['reset_ap_link'] = $this->link_pre.$val['ap_link'];	
			} else {
				$produceList[$key]['reset_ap_link'] = '未生成';	
			}
			$produceList[$key]['reset_ap_ctm'] = date('Y-m-d<br/>H:i', $produceList[$key]['ap_ctm']);
			/* if ($val['ap_type'] == 2) {
				$produceList[$key]['reset_ap_type'] = '活动页面';
			} */
			if(count(json_decode($val['ap_rule'],true)) < 10){
			    $produceList[$key]['reset_ap_type'] = '活动结束';
			}else {
			    $produceList[$key]['reset_ap_type'] = '活动页面';
			}
			if($val['activate_type'] == 1 && $val['ap_package']){
				$pkg[] = $val['ap_package'];
			}
		}	
		if($pkg){
			$where = array(
				'package' =>  array('in',$pkg),
			);
			$softinfo = get_table_data($where,"sj_soft","package","package,softname");
			foreach ($produceList as $key => $val) {	
				$produceList[$key]['softname'] = $softinfo[$val['ap_package']]['softname'];
			}
		}	
		return $produceList;
	}

	public function addTpl() {
		$this->display('addTpl');
	}
    
	
	public function addTplEnd() {
	    $this->display();
	}
	
	//分类列表
	public function soft_category_list(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> order('rank') -> select();
	    for($i=1;$i<=count($result);$i++){
	        $rank[] = $i;
	    }
	     
	    foreach($result as $key => $val){
	        $my_soft_count = $model -> table('sj_actives_soft') -> where(array('category_id' => $val['id'],'status' => 1)) -> count();
	        $val['soft_count'] = $my_soft_count;
	        $result[$key] = $val;
	    }
	     
	    $p = $_GET['p']?$_GET['p']:1;
	    $lr = $_GET['lr'];
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('id',$id);
	    $this -> assign('rank',$rank);
	    $this -> assign('result',$result);
	    $this -> display();
	}
    //软件列表
	public function soft_list(){
	    $model = new Model();
	    $category_id = $_GET['category_id'];
	    $my_category = $model -> table('sj_actives_category') -> where(array('id' => $category_id)) -> select();
	    $result = $model -> table('sj_actives_soft') -> where(array('category_id' => $category_id,'status' => 1)) -> order('rank') -> select();
	    for($i=1;$i<=count($result);$i++){
	        $rank[] = $i;
	    }
	    $p = $_GET['p']?$_GET['p']:1;
	    $lr = $_GET['lr'];
	    $this -> assign('id',$my_category[0]['active_id']);
	    $this -> assign('category_id',$category_id);
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('rank',$rank);
	    $this -> assign('result',$result);
	    $this -> display();
	}
	
	//接受分类表单提交
	public function add_category_do(){
	    $model = new Model();
	    $category_name = $_POST['category_name'];
	    $pic_url = $_FILES['pic_url'];
	    $rank = $_POST['rank'];
	    $active_id = $_POST['active_id'];
	    if($this -> strlen_az($category_name) > 20 || !$category_name){
	        $this -> error("请填写10字以内的分类名称");
	    }
	    $have_been = $model -> table('sj_actives_category') -> where(array('category_name' => $category_name,'active_id' => $active_id,'status' => 1)) -> select();
	    if($have_been){
	        $this -> error("该活动已存在此分类名称");
	    }
	    if($pic_url){
	        $path=date("Ym/d/",time());
	        $config = array(
	            'multi_config' => array(
	                'pic_url' => array(
	                    'savepath' => UPLOAD_PATH. '/img/'. $path,
	                    'saveRule' => 'getmsec',
	                    'img_p_width' => 102,
	                    'img_p_height' => 35,
	                    'img_p_size' => 1024*30,
	                ),
	            ));
	        $lists=$this->_uploadapk(0, $config);
	        $my_pic = $lists['image'][0]['url'];
	    }else{
	        $this -> error("请上传分类图片");
	    }
	    $rank_where['_string'] = "active_id = {$active_id} and rank >= {$rank} and status = 1";
	    $rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
	    if($rank_result){
	        foreach($rank_result as $key => $val){
	            $rank_data = array(
	                'rank' => $val['rank'] + 1
	            );
	            $rank_where['_string'] = "id = {$val['id']}";
	            $change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
	        }
	    }
	    $data = array(
	        'category_name' => $category_name,
	        'rank' => $rank,
	        'pic_url' => $my_pic,
	        'create_tm' => time(),
	        'update_tm' => time(),
	        'active_id' => $active_id,
	        'price'=>$_POST['price']!=''?$_POST['price']:0, //流量默认配置
	        'status' => 1
	    );
	    $result = $model -> table('sj_actives_category') -> add($data);
	    $p = $_POST['p'];
	    $lr = $_POST['lr'];
	    if($result){
	        $this -> writelog("已添加活动id为{$active_id}的分类，id为{$result}",'sj_actives_category',$result,__ACTION__ ,'','add');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/SignFlow/soft_category_list/id/{$active_id}/p/{$p}/lr/{$lr}");
	        $this -> success("添加成功");
	    }else{
	        $this -> error("添加失败");
	    }
	}
	
	//打开分类表单
	public function add_category_show(){
	    $model = new Model();
	    $active_id = $_GET['active_id'];
	    $rank_result = $model -> table('sj_actives_category') -> where(array('active_id' => $active_id,'status' => 1)) -> count();
	    if($rank_result>=2){  //限制分组添加
	        $url ="/index.php/Sendnum/SignFlow/soft_category_list/id/{$active_id}";
	       echo '<script language="javascript">alert("规则限制,最多2组!不要贪心哦");window.location.href="'.$url.'";</script>';return;
	    }
	    for($i=1;$i<=$rank_result;$i++){
	        $rank[] = $i;
	    }
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    $this -> assign('active_id',$active_id);
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('rank',$rank);
	    $this -> display();
	}
	
	//配置文案展示
	public function config_text_show(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> select();
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    $this -> assign('id',$id);
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('result',$result);
	    $this -> display();
	}
	
	//配置文件提交
	function config_comment_do(){
	    $model = new Model();
	    $id = $_POST['id'];
	    $winning_comment = $_POST['winning_comment'];
	    if($this -> strlen_az($winning_comment) > 24 || !$winning_comment){
	        $this -> error("请填写12个字以内的获奖机会文案");
	    }
	    $button_comment = $_POST['button_comment'];
	    if($this -> strlen_az($button_comment) > 30 || !$button_comment){
	        $this -> error("请填写15个字以内的主页面按钮名称");
	    }
	    $download_comment = $_POST['download_comment'];
	    if($this -> strlen_az($download_comment) > 100 || !$download_comment){
	        $this -> error("请填写50个字以内的下载后文案");
	    }
	    $data = array(
	        'winning_comment' => $winning_comment,
	        'button_comment' => $button_comment,
	        'download_comment' => $download_comment,
	        'ap_utm' => time()
	    );
	    $log_result = $this -> logcheck(array('ap_id' => $id),'sj_activity_page',$data,$model);
	    $result = $model -> table('sj_activity_page') -> where(array('ap_id' => $id)) -> save($data);
	    $p = $_POST['p'];
	    $lr = $_POST['lr'];
	    if($result){
	        $this -> writelog("已编辑id为{$id}的活动的文案配置".$log_result,'sj_actives_category',$id,__ACTION__ ,'','edit');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/SignFlow/soft_category_list/id/{$id}/p/{$p}/lr/{$lr}");
	        $this -> success("编辑成功");
	    }else{
	        $this -> error("编辑失败");
	    }
	}
	
	function activate_category_list(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> order('rank') -> select();
	    for($i=1;$i<=count($result);$i++){
	        $rank[] = $i;
	    }
	    foreach($result as $key => $val){
	        $my_soft_count = $model -> table('sj_actives_soft') -> where(array('category_id' => $val['id'],'status' => 1)) -> count();
	        $val['soft_count'] = $my_soft_count;
	        $result[$key] = $val;
	    }
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('id',$id);
	    $this -> assign('rank',$rank);
	    $this -> assign('result',$result);
	    $this -> display();
	}
	
	
	function add_edit_soft(){	    
	    $model = new Model();
	    if(!$_POST){ 
	        $id = $_GET['id'];
	        $p = $_GET['p'];
	        $lr = $_GET['lr'];
	        
	        //查活动表
	        $category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> select();
	        var_dump($category_result);
	        //根据活动id去关联查软件
	        $soft_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $category_result[0]['id'],'status' => 1)) -> order('rank') -> select();
	         
	        //$rank = count($soft_result); //下一个添加的排序
	        for($i=1;$i<=count($soft_result);$i++){
	            $rank[] = $i;
	        }
	         
	        $this -> assign("soft_result",$soft_result);
	        $this -> assign('id',$id);
	        $this -> assign("p",$p);
	        $this -> assign("lr",$lr);
	        $this -> assign("rank",$rank);
	        $this -> assign("referer_url",base64_decode($_GET['referer_url']));
	        
	        $this -> display(); 
	   }

	    $id = $_POST['id'];
	    $p = $_POST['p'];
	    $lr = $_POST['lr'];
	    $category_result = $model -> table('sj_actives_category') -> where(array('active_id' => $id,'status' => 1)) -> select();

	    //未查到记录添加
	    if(!$category_result){
	        $data['active_id'] = $id;
	        $data['category_name'] = '签到流量活动';
	        $data['status'] = 1;
	        $data['rank'] = 16;
	        $data['create_tm'] = time();
	        $data['update_tm'] = time();
	        $add_category = $model -> table('sj_actives_category') -> add($data);
	        if($add_category){
	            $category_id = $add_category;
	        }else{
	            $this -> error("添加分类失败");
	        }
	    }else{
	        $category_id = $category_result[0]['id'];
	    }
	    
	    
	    $soft_name = trim($_POST['soft_name']);
	    $package = trim($_POST['package']);
	    $rank = $_POST['rank'];
	    $recomment = $_POST['recomment'];
	    $award_recomment = $_POST['award_recomment'];
	    if($this -> strlen_az($soft_name) > 20 || !$soft_name){
	        $this -> error("请填写10字以内的软件名称");
	    }
	    $active_have_been_where['_string'] = "soft_name = '{$soft_name}' and category_id = {$category_id} and status = 1";
	    $active_have_been = $model -> table('sj_actives_soft') -> where($active_have_been_where) -> select();
	    
	    if($active_have_been){
	        $this -> error("该活动已存在此软件名称");
	    }
	    $my_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
	    if(!$my_package){
	        $this -> error("该软件包名不存在");
	    }
	    $active_have_been_where_package['_string'] = "package = '{$package}' and category_id = {$category_id} and status = 1";
	    $active_have_been_package = $model -> table('sj_actives_soft') -> where($active_have_been_where_package) -> select();
	    if($active_have_been_package){
	        $this -> error("该活动已存在此软件包名");
	    }
	    
	    $rank_where['_string'] = "category_id = {$category_id} and rank >= {$rank} and status = 1";
	    $rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
	    if($rank_result){
	        foreach($rank_result as $key => $val){
	            $rank_data = array(
	                'rank' => $val['rank'] + 1
	            );
	            $change_result = $model -> table('sj_actives_soft') -> where(array('id' => $val['id'])) -> save($rank_data);
	        }
	    }
	    $data = array(
	        'soft_name' => $soft_name,
	        'package' => $package,
	        'page_id' => $id,
	        'rank' => $rank,
	        'recomment' => $recomment,
	        'award_recomment' => $award_recomment,
	        'create_tm' => time(),
	        'update_tm' => time(),
	        'status' => 1,
	        'category_id' => $category_id
	    );
	    $result = $model -> table('sj_actives_soft') -> add($data);
	    $p = $_POST['p'];
	    $lr = $_POST['lr'];
	    if($result){
	        $this -> writelog("已添加页面id为{$result}的软件", 'sj_actives_soft',$result,__ACTION__ ,'','add');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/CoActivity/soft_list/id/{$id}/p/{$p}/lr/{$lr}");
	        $this -> success("添加成功");
	    }else{
	        $this -> error("添加失败");
	    }
	}
	
	function del_soft(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
	    $category_result = $model -> table('sj_actives_category') -> where(array('id' => $my_result[0]['category_id'],'status' => 1)) -> select();
	    $rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and status = 1";
	    $rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
	    foreach($rank_result as $key => $val){
	        $rank_data = array(
	            'rank' => $val['rank'] - 1
	        );
	        $rank_result = $model -> table('sj_actives_soft') -> where(array('id' => $val['id'])) -> save($rank_data);
	    }
	    $result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save(array('status' => 0));
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    if($result){
	        $this -> writelog("已删除id为{$id}活动分类软件", 'sj_actives_soft',$id,__ACTION__ ,'','del');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/SignFlow/soft_list/category_id/{$my_result[0]['category_id']}/p/{$p}/lr/{$lr}");

	        $this -> success("删除成功");
	    }else{
	        $this -> error("删除失败");
	    }
	
	}
	
	function del_category(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $my_result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
	    $where_rank['_string'] = "rank > {$my_result[0]['rank']} and active_id = {$my_result[0]['active_id']} and status = 1";
	    $rank_result = $model -> table('sj_actives_category') -> where($where_rank) -> select();
	
	    foreach($rank_result as $key => $val){
	        $rank_data = array(
	            'rank' => $val['rank'] - 1
	        );
	        $rank_result = $model -> table('sj_actives_category') -> where(array('id' => $val['id'])) -> save($rank_data);
	    }
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    $result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> save(array('status' => 0));
	    if($result){
	        $soft_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $id)) -> save(array('status' => 0));
	        $this -> writelog("已删除id为{$id}的活动分类",'sj_actives_category',$id,__ACTION__ ,'','del');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/SignFlow/soft_category_list/id/{$my_result[0]['active_id']}/p/{$p}/lr/{$lr}");
	        $this -> success("删除成功");
	    }else{
	        $this -> error("编辑失败");
	    }
	}
	
	public function edit_category_show(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
	    $count = $model -> table('sj_actives_category') -> where(array('active_id' => $result[0]['active_id'],'status' => 1)) -> count();
	    for($i=1;$i<=$count;$i++){
	        $rank[] = $i;
	    }
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    
	    $this -> assign('rank',$rank);
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('result',$result);
	    $this -> display();
	}
	
	public function edit_category_do(){
	    $model = new Model();
	    $id = $_POST['id'];
	    $category_name = $_POST['category_name'];
	    $pic_url = $_FILES['pic_url'];
	    $rank = $_POST['rank'];
	    if($this -> strlen_az($category_name) > 20 || !$category_name){
	        $this -> error("请填写10字以内的分类名称");
	    }
	    $where_have['_string'] = "category_name = {$category_name} and active_id = {$active_id} and status = 1 and id != {$id}";
	    $have_been = $model -> table('sj_actives_category') -> where(array('category_name' => $category_name,'active_id' => $active_id,'status' => 1)) -> select();
	    if($have_been){
	        $this -> error("该活动已存在此分类名称");
	    }
	    if($_FILES['pic_url']['size']){
	        $path=date("Ym/d/",time());
	        $config = array(
	            'multi_config' => array(
	                'pic_url' => array(
	                    'savepath' => UPLOAD_PATH. '/img/'. $path,
	                    'saveRule' => 'getmsec',
	                    'img_p_width' => 102,
	                    'img_p_height' => 35,
	                    'img_p_size' => 1024*30,
	                ),
	            ));
	        $lists=$this->_uploadapk(0, $config);
	        $my_pic = $lists['image'][0]['url'];
	    }
	    $have_been = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
	    if($rank != $have_been[0]['rank']){
	        if($rank < $have_been[0]['rank']){
	            $rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank >= {$rank}  and rank < {$have_been[0]['rank']} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] + 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }elseif($rank > $have_been[0]['rank']){
	            $rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank > {$have_been[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] - 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }
	    }
	    if($_FILES['pic_url']['size']){
	        $data = array(
	            'category_name' => $category_name,
	            'rank' => $rank,
	            'pic_url' => $my_pic,
	            'update_tm' => time(),
	            'price' => $_POST['price']
	        );
	    }else{
	        $data = array(
	            'category_name' => $category_name,
	            'rank' => $rank,
	            'update_tm' => time(),
	            'price' => $_POST['price']
	        );
	    }
	    $log_result = $this -> logcheck(array('id' => $id),'sj_actives_category',$data,$model);
	    $result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> save($data);
	    $p = $_POST['p'];
	    $lr = $_POST['lr'];
	    if($result){
	        $this -> writelog("已编辑活动id为{$have_been[0]['active_id']}的分类，id为{$id}".$log_result,'sj_actives_category',$id,__ACTION__ ,'','edit');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/SignFlow/soft_category_list/id/{$have_been[0]['active_id']}/p/{$p}/lr/{$lr}");
	        $this -> success("编辑成功");
	    }else{
	        $this -> error("编辑失败");
	    }
	
	}
	
	
	public function editTpl() {
	    $activityOne = $this->resetVal($this->activity->getActivityOne($_GET['id']));
	    $list = json_decode($activityOne[0]['ap_rule'],true);
	    $tableImg = json_decode($activityOne[0]['ap_award'],true);
	    
	    if(!$_POST){
	        $this->assign('list', $list);
	        $this->assign('tableImg',$tableImg );
	        
	        if(count($list) > 10){
	            $this->display('addTpl');
	        }else{
	            $this->display('addTplEnd');
	        }
	    }else{
	        $imgUrl = $list = $listsImg = $nullImg = array();
	        $model = D('sendNum.Activity');
	        $config = $model->pic_config();
	        
	        if(!empty($config)){
	            $list =  $this->_uploadapk($type=false, $config);
	            foreach ($list['image'] as $key => $val) {
	                $listsImg[$val['post_name']] = $val['url_original'];
	            }
	        }
	        
	        //加入未修改图片信息
	        foreach ($tableImg as $key => $img){
	            if(!$listsImg[$key]){
	                $listsImg[$key] = $img;
	            }
	        }
	        if (!$listsImg['whole_banner_bgimg']) {
	            $this->error('编辑失败，活动banner图片不能为空');
	        }
	        
	        $jsondImg = json_encode($listsImg);
	        $jsondate = json_encode($_POST);
	         
	        $Data = array(
	            'ap_name' => trim($_POST['name']),
	            'ap_imgurl'=>$listsImg['whole_banner_bgimg'], //获取列表插图
	            'activate_type' => 16,
	            'ap_utm' => time(),
	            'ap_rule' => $jsondate,
	            'ap_award'=>$jsondImg
	        );
	        $where = "ap_id=".$_GET['id'];
	        $this->assign('jumpUrl', '/index.php/Sendnum/SignFlow/produceList');
	        $log_result = $this->logcheck(array('ap_id'=>$_GET['id']),'sj_activity_page',$Data,$this->activity);
	        $this->activity->activityEdit($where, $Data);
	        $this->writelog("修改活动页面id:".$_GET['id'].$log_result, 'sj_activity_page',$_GET['id'],__ACTION__ ,'','edit');
	        $this->success('编辑成功');
	    }
	}
	
	//分类顺序调整
	function change_category_rank(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $rank = $_GET['rank'];
	    $have_been = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> select();
	    if($rank != $have_been[0]['rank']){
	        if($rank < $have_been[0]['rank']){
	            $rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank >= {$rank}  and rank < {$have_been[0]['rank']} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] + 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }elseif($rank > $have_been[0]['rank']){
	            $rank_where['_string'] = "active_id = {$have_been[0]['active_id']} and rank > {$have_been[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_category') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] - 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_category') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }
	    }
	    $log_result = $this -> logcheck(array('id' => $id),'sj_actives_category',array('rank' => $rank),$model);
	    $my_result = $model -> table('sj_actives_category') -> where(array('id' => $id)) -> save(array('rank' => $rank));
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    if($my_result){
	        $this -> writelog("已编辑id为{$id}的活动分类".$log_result,'sj_actives_category',$id,__ACTION__ ,'','edit');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/Activate/activate_category_list/id/{$have_been[0]['active_id']}/p/{$p}/lr/{$lr}");
	        $this -> success("编辑成功");
	    }else{
	        $this -> error("编辑失败");
	    }
	}

	//软件的添加 表单
	public function add_soft_show(){
	    $model = new Model();
	    $category_id = $_GET['category_id'];
	    $result = $model -> table('sj_actives_soft') -> where(array('category_id' => $category_id,'status' => 1)) -> count();
	    for($i=1;$i<=$result;$i++){
	        $rank[] = $i;
	    }
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('category_id',$category_id);
	    $this -> assign('rank',$rank);
	    $this -> display();
	}
	
	//软件的修改 表单展示
	public function edit_soft_show(){	    
	    $model = new Model();
	    $id = $_GET['id'];
	    $result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
	    $rank_result = $model -> table('sj_actives_soft') -> where(array('category_id' => $result[0]['category_id'],'status' => 1)) -> count();
	    for($i=1;$i<=$rank_result;$i++){
	        $rank[] = $i;
	    }
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    $this -> assign('p',$p);
	    $this -> assign('lr',$lr);
	    $this -> assign('rank',$rank);
	    $this -> assign('result',$result);
	    $this -> display();
	}
	//软件的修改 表单修改提交
	public function edit_soft_do(){
	    $model = new Model();
	    $id = $_POST['id'];
	    $soft_name = trim($_POST['soft_name']);
	    $package = trim($_POST['package']);
	    $rank = $_POST['rank'];
	    $recomment = $_POST['recomment'];
	    $award_recomment = $_POST['award_recomment'];
	    $my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
	
	    //该活动所拥有的分类
	    $my_activate = $model -> table('sj_actives_category') -> where(array('id' => $my_result[0]['category_id'],'status' => 1)) -> select();
	    $the_category_id = $model -> table('sj_actives_category') -> where(array('active_id' => $my_activate[0]['active_id'],'status' => 1)) -> select();
	
	    foreach($the_category_id as $key => $val){
	        $category_id_str_go .= $val['id'].',';
	    }
	    $category_id_str = substr($category_id_str_go,0,-1);
	    $active_have_been_where['_string'] = "soft_name = '{$soft_name}' and category_id in ({$category_id_str}) and status = 1 and id != {$id}";
	    $active_have_been = $model -> table('sj_actives_soft') -> where($active_have_been_where) -> select();
	
	    if($active_have_been){
	        $this -> error("该活动已存在此软件名称");
	    }
	
	    $where_have['_string'] = "soft_name = {$soft_name} and category_id = {$my_result[0]['category_id']} and status = 1 and id != {$id}";
	    $have_been = $model -> table('sj_actives_soft') -> where($where_have) -> select();
	    if($have_been){
	        $this -> error("该软件名在此分类中已存在");
	    }
	    if($this -> strlen_az($soft_name) > 20 || !$soft_name){
	        $this -> error("请填写10字以内的软件名称");
	    }
	    $have_been = $model -> table('sj_actives_soft') -> where(array('soft_name' => $soft_name,'category_id' => $category_id,'status' => 1)) -> select();
	    if($have_been){
	        $this -> error("该软件名称在此分类中已存在");
	    }
	    $my_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
	    if(!$my_package){
	        $this -> error("该软件包名不存在");
	    }
	
	    $active_have_been_where_package['_string'] = "package = '{$package}' and category_id in ({$category_id_str}) and status = 1 and id != {$id}";
	    $active_have_been_package = $model -> table('sj_actives_soft') -> where($active_have_been_where_package) -> select();
	    if($active_have_been_package){
	        $this -> error("该活动已存在此软件包名");
	    }
	
	    $where_have_package['_string'] = "package = {$package} and category_id = {$category_id} and status = 1 and id != {$id}";
	    $have_been_package = $model -> table('sj_actives_soft') -> where($where_have_package) -> select();
	    if($have_been_package){
	        $this -> error("该软件包名在此分类中已存在");
	    }
	    if($this -> strlen_az($recomment) > 30){
	        $this -> error("请填写15字以内的一句话推荐");
	    }
	    if($this -> strlen_az($award_recomment) > 200){
	        $this -> error("请填写100字以内的奖品介绍");
	    }
	    if($rank != $my_result[0]['rank']){
	        if($rank < $my_result[0]['rank']){
	            $rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank >= {$rank}  and rank < {$my_result[0]['rank']} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] + 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }elseif($rank > $my_result[0]['rank']){
	            $rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] - 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }
	    }
	
	    $data = array(
	        'soft_name' => $soft_name,
	        'package' => $package,
	        'rank' => $rank,
	        'recomment' => $recomment,
	        'award_recomment' => $award_recomment,
	        'update_tm' => time(),
	    );
	    $log_result = $this -> logcheck(array('id' => $id),'sj_actives_soft',$data,$model);
	    $result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save($data);
	    $p = $_POST['p'];
	    $lr = $_POST['lr'];
	    if($result){
	        $this -> writelog("已编辑id为{$id}的活动分类软件".$log_result,'sj_actives_soft',$id,__ACTION__ ,'','edit');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/SignFlow/soft_list/category_id/{$my_result[0]['category_id']}/p/{$p}/lr/{$lr}");
	        $this -> success("编辑成功");
	    }else{
	        $this -> error("编辑失败");
	    }
	}   

	public function add_soft_do(){
	    $model = new Model();
	    $category_id = $_POST['category_id'];
	    $soft_name = trim($_POST['soft_name']);
	    $package = trim($_POST['package']);
	    $rank = $_POST['rank'];
	    $recomment = $_POST['recomment'];
	    $award_recomment = $_POST['award_recomment'];
	    if($this -> strlen_az($soft_name) > 20 || !$soft_name){
	        $this -> error("请填写10字以内的软件名称");
	    }
	    //该活动所拥有的分类
	    $my_activate = $model -> table('sj_actives_category') -> where(array('id' => $category_id,'status' => 1)) -> select();
	    $the_category_id = $model -> table('sj_actives_category') -> where(array('active_id' => $my_activate[0]['active_id'],'status' => 1)) -> select();
	
	    foreach($the_category_id as $key => $val){
	        $category_id_str_go .= $val['id'].',';
	    }
	    $category_id_str = substr($category_id_str_go,0,-1);
	    $active_have_been_where['_string'] = "soft_name = '{$soft_name}' and category_id in ({$category_id_str}) and status = 1";
	    $active_have_been = $model -> table('sj_actives_soft') -> where($active_have_been_where) -> select();
	
	    if($active_have_been){
	        $this -> error("该活动已存在此软件名称");
	    }
	
	    $have_been = $model -> table('sj_actives_soft') -> where(array('soft_name' => $soft_name,'category_id' => $category_id,'status' => 1)) -> select();
	
	    if($have_been){
	        $this -> error("该软件名称在此分类中已存在");
	    }
	    $my_package = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> select();
	    if(!$my_package){
	        $this -> error("该软件包名不存在");
	    }
	
	    $active_have_been_where_package['_string'] = "package = '{$package}' and category_id in ({$category_id_str}) and status = 1";
	    $active_have_been_package = $model -> table('sj_actives_soft') -> where($active_have_been_where_package) -> select();
	    if($active_have_been_package){
	        $this -> error("该活动已存在此软件包名");
	    }
	
	    $have_been_package = $model -> table('sj_actives_soft') -> where(array('package' => $package,'category_id' => $category_id,'status' => 1)) -> select();
	    if($have_been_package){
	        $this -> error("该软件包名在此分类中已存在");
	    }
	    if($this -> strlen_az($recomment) > 30){
	        $this -> error("请填写15字以内的一句话推荐");
	    }
	    if($this -> strlen_az($award_recomment) > 200){
	        $this -> error("请填写100字以内的奖品介绍");
	    }
	    $rank_where['_string'] = "category_id = {$category_id} and rank >= {$rank} and status = 1";
	    $rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
	    if($rank_result){
	        foreach($rank_result as $key => $val){
	            $rank_data = array(
	                'rank' => $val['rank'] + 1
	            );
	            $change_result = $model -> table('sj_actives_soft') -> where(array('id' => $val['id'])) -> save($rank_data);
	        }
	    }
	    $data = array(
	        'soft_name' => $soft_name,
	        'package' => $package,
	        'page_id' => $my_activate[0]['active_id'],
	        'rank' => $rank,
	        'recomment' => $recomment,
	        'award_recomment' => $award_recomment,
	        'create_tm' => time(),
	        'update_tm' => time(),
	        'status' => 1,
	        'category_id' => $category_id
	    );
	    $result = $model -> table('sj_actives_soft') -> add($data);
	    $p = $_POST['p'];
	    $lr = $_POST['lr'];
	
	    if($result){
	        $this -> writelog("已添加分类id为{$category_id}的软件，id为{$result}",'sj_actives_soft',$result,__ACTION__ ,'','add');
	        $this -> assign('jumpUrl',"/index.php/Sendnum/SignFlow/soft_list/category_id/{$category_id}/p/{$p}/lr/{$lr}");
	        $this -> success("添加成功");
	    }else{
	        $this -> error("添加失败");
	    }
	}	
	//软件的添加 结束
	public function editTplEnd(){
	    if (!$_POST){ $this->error('系统发生错误!非密文提交');   }
	    $activityOne = $this->resetVal($this->activity->getActivityOne($_GET['id']));
	    $list = json_decode($activityOne[0]['ap_rule'],true);
	    $tableImg = json_decode($activityOne[0]['ap_award'],true);
        $imgUrl = $list = $listsImg = $nullImg = array();
        $model = D('sendNum.Activity');
        $config = $model->pic_config();
         
        if(!empty($config)){
            $list =  $this->_uploadapk($type=false, $config);
            foreach ($list['image'] as $key => $val) {
                $listsImg[$val['post_name']] = $val['url_original'];
            }
        }
         
        //加入未修改图片信息
        foreach ($tableImg as $key => $img){
            if(!$listsImg[$key]){
                $listsImg[$key] = $img;
            }
        }
        if(!$listsImg['whole_bgimg'])
        {
            $this->error('添加失败，活动banner图片不能为空');
        }
        
        $jsondImg = json_encode($listsImg);
        $jsondate = json_encode($_POST);
    
        $Data = array(
            'ap_name' => trim($_POST['name']),
            //'ap_imgurl'=>$listsImg['whole_bgimg'], //获取列表插图
            'ap_imgurl'=>$listsImg['whole_bgimg'], //获取列表插图
            'activate_type' => 16,
            'ap_utm' => time(),
            'ap_rule' => $jsondate,
            'ap_award'=>$jsondImg
        );
        $where = "ap_id=".$_GET['id'];
        $this->assign('jumpUrl', '/index.php/Sendnum/SignFlow/produceList');
        $log_result = $this->logcheck(array('ap_id'=>$_GET['id']),'sj_activity_page',$Data,$this->activity);
        $this->activity->activityEdit($where, $Data);
        $this->writelog("修改活动页面id:".$_GET['id'].$log_result, 'sj_activity_page',$_GET['id'],__ACTION__ ,'','edit');
        $this->success('编辑成功');
	}
	public function activityAdd() {
		$jump_url = $_SERVER['HTTP_REFERER'];
	    $this->assign('jumpUrl', $jump_url);
		if (!$_POST){ $this->error('系统发生错误!非密文提交');   }
		$model = D('sendNum.Activity');	
		/*
		if (!$_FILES['whole_bgimg']['name']) {
			$this->error('添加失败，活动背景图片不能为空');
		}*/
		if (!$_FILES['whole_banner_bgimg']['name']) {
		    $this->error('添加失败，活动banner图片不能为空');
		}
		
		if (mb_strlen($_POST['name'], 'utf-8') > 30) {
			$this->error('操作失败，活动名称不能超过30个字');
		}
		
		if(!$_POST['whole_bgcolor'] || !$_POST['whole_button_bgcolor'] || !$_POST['whole_button_tcolor'] || !$_POST['whole_text_color']){
		    $this->error('操作失败，缺少 * 号标记元素');
		}
		if(!$_POST['whole_sign_button'] && !$_POST['whole_dow_button']){
		    $this->error('至少选择一项活动类型');
		}
		 
        
		$config = $model->pic_config();
		$imgUrl = $list = $listsImg = $nullImg = array();
		$list =  $this->_uploadapk($type=false, $config);
		
		foreach ($list['image'] as $key => $val) {
		    $listsImg[$val['post_name']] = $val['url_original'];
		}
		/*//加入空的图片信息
		foreach ($_FILES as $img){
		    if(!$img['name']){ $listsIm[$img['name']] = ''; }
		}*/
		
		$jsondImg = json_encode($listsImg);
		$jsondate = json_encode($_POST);
		
		$insertData = array(
			'ap_name' => trim($_POST['name']),	
		    'ap_imgurl'=>$listsImg['whole_banner_bgimg'], //获取列表插图
		    'activate_type' => 16,
			'ap_type' => $_POST['type'],
			'ap_ctm' => time(),
			'ap_utm' => time(),
			'ap_rule' => $jsondate,
		    'ap_award'=>$jsondImg
		);
		
		$result = $this->activity->activityAdd($insertData);
		
		if($result){
		    $ap_link = '/activity_'.$result.'.html';
			$this->activity->updatePage('ap_id='.$result, array('ap_link' => $ap_link));
		}else{
		    $this->assign('postDate', $_POST);
		    $this->assign('list', $_POST);
		    $sql = $model->getLastSql();
		    $this->error('系统发生错误!添加失败!请返回重试');
		}
		$this->assign('jumpUrl', '/index.php/Sendnum/SignFlow/produceList');
		$this -> writelog("添加活动页面，内容为".json_encode($insertData), 'sj_activity_page',$result,__ACTION__ ,'','add');
		$this->success('添加成功');
	}

	public function change_soft_rank(){
	    $model = new Model();
	    $id = $_GET['id'];
	    $rank = $_GET['rank'];
	    $my_result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> select();
	    if($rank != $my_result[0]['rank']){
	        if($rank < $my_result[0]['rank']){
	            $rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank >= {$rank}  and rank < {$my_result[0]['rank']} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] + 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }elseif($rank > $my_result[0]['rank']){
	            $rank_where['_string'] = "category_id = {$my_result[0]['category_id']} and rank > {$my_result[0]['rank']} and rank <= {$rank} and status = 1 and id != {$id}";
	            $rank_result = $model -> table('sj_actives_soft') -> where($rank_where) -> select();
	            if($rank_result){
	                foreach($rank_result as $key => $val){
	                    $rank_data = array(
	                        'rank' => $val['rank'] - 1
	                    );
	                    $rank_where['_string'] = "id = {$val['id']}";
	                    $change_result = $model -> table('sj_actives_soft') -> where($rank_where) -> save($rank_data);
	                }
	            }
	        }
	    }
	    $p = $_GET['p'];
	    $lr = $_GET['lr'];
	    $log_result = $this -> logcheck(array('id' => $id),'sj_actives_soft',array('rank' => $rank),$model);
	    $result = $model -> table('sj_actives_soft') -> where(array('id' => $id)) -> save(array('rank' => $rank));
	    if($result){
	        $this -> writelog("已编辑id为{$id}的活动分类软件".$log_result,'sj_actives_soft',$id,__ACTION__ ,'','edit');
	        $this -> assign("jumpUrl","/index.php/Sendnum/Activate/soft_list/category_id/{$my_result[0]['category_id']}/p/{$p}/lr/{$lr}");
	        $this -> success("编辑成功");
	    }else{
	        $this -> error("编辑失败");
	    }
	}
	
	public function activityAddEnd() {
	    $jump_url = $_SERVER['HTTP_REFERER'];
	    $this->assign('jumpUrl', $jump_url);
	    if (!$_POST){ $this->error('系统发生错误!非密文提交');   }

	    $config = $this->activity->pic_config();
	    
	    $imgUrl = $list = $listsImg = $nullImg = array();
	    $list =  $this->_uploadapk($type=false, $config);
	    
	    foreach ($list['image'] as $key => $val) {
	        $listsImg[$val['post_name']] = $val['url_original'];
	    }
	    
	    if(!$listsImg['whole_bgimg'])
	    {
	        $this->error('添加失败，活动banner图片不能为空');
	    }
	    
	    $jsondImg = json_encode($listsImg);
	    $jsondate = json_encode($_POST);
	    
	    $insertData = array(
	        'ap_name' => trim($_POST['name']),
	        'ap_imgurl'=>$listsImg['whole_bgimg']?$listsImg['whole_bgimg']:$listsImg['whole_end_pageimg'], //获取列表插图
	        'activate_type' => 16,
	        'ap_type' => $_POST['type'],
	        'ap_ctm' => time(),
	        'ap_utm' => time(),
	        'ap_rule' => $jsondate,
	        'ap_award'=>$jsondImg
	    );
	    
	    $result = $this->activity->activityAdd($insertData);
	    
	    if($result){
	        $ap_link = '/activity_'.$result.'.html';
	        $this->activity->updatePage('ap_id='.$result, array('ap_link' => $ap_link));
	    }else{
	        $this->error('系统发生错误!添加失败!请返回重试');
	    }
	    $this->assign('jumpUrl', '/index.php/Sendnum/SignFlow/produceList');
	    $this -> writelog("添加活动结束页面，内容为".json_encode($insertData), 'sj_activity_page',$result,__ACTION__ ,'','add');
	    $this->success('添加成功');
	}

	private function noticeLine($str) {
		$arr = explode(PHP_EOL, $str);	
		foreach ($arr as $val) {
			if (mb_strlen(trim($val), 'utf-8') > 15) {
				return false;
			}
		}
		return true;
	}

	public function checkFormat() {
		$package = $_GET['package'] ? $_GET['package'] : $_POST['package'];
		$result = $this->activity->checkPackage($package);
		echo $result;
	}

	public function activitydel() {
		$where = 'ap_id='.$_GET['id'];
		$this->activity->activityDel($where);
		$this->writelog('删除活动页面id:'.$_GET['id'], 'sj_activity_page',$_GET['id'],__ACTION__ ,'','del');
		header("location:/index.php/Sendnum/SignFlow/produceList");
	}


	private function uploadFile() {
		$img = $_FILES['img'];	
		if ($img['name']) {
			$fileExt = substr($img['name'], strrpos($img['name'], '.'));
			$cdndir = '/data/att/m.goapk.com/img/';
			$datedir = date('Ym',time()).'/'.date('d', time()).'/';
			$filename = md5(time().$img['name']).$fileExt;
			$type = $img['type'];
			if (!is_dir($cdndir.$datedir)) {
				mkdir($cdndir.$datedir, 0755, true);	
			}
			copy($img['tmp_name'], $cdndir.$datedir.$filename);
			return $datedir.$filename;
		} else {
			return false;	
		}
	}


} 
