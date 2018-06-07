<?php

//专题页内容管理
class FeatureManageAction extends CommonAction {
    
	private $feature_tab_db;
	private $feature_tab_list;
	private $feature_db;
	private $feature_list;
	private $config_db;
	private $config_list;
	
	//选项列表
    public function feature_tab_list() {
	    import('@.ORG.Page');
        $param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
		    $this->assign('lr',(int)$_GET['lr']);
		}else{
		    $this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign('p',(int)$_GET['p']);
		}else{
		    $this->assign('p', 1);
		}
		if(isset($_GET['status'])){
		    $this -> assign('status',(int)$_GET['status']);
			$where = array('status' => (int)$_GET['status']);
		}else{
		    $this -> assign('status', 1);
			$where = array('status' => 1);
		}
        $this -> feature_tab_db = M('market_feature_tab');
		$count_total = $this -> feature_tab_db -> where($where) -> count();
		$page  = new Page($count_total, $limit, $param);
        $this -> feature_tab_list  = $this -> feature_tab_db -> where($where) 
		      -> limit($page -> firstRow . ',' . $page -> listRows) 
		      -> order('rank asc')
			  -> select();
		$this -> assign('tab_list',$this -> feature_tab_list);
		$this -> assign('count',$count_total);
		$this -> config_db = new Model();
		$condition = array('status'=>1,'config_type' => 'WEBMARKET_FEATURE_TAB_NUM');
		$this -> config_list = $this -> config_db ->table('pu_config') -> where($condition) 
					-> field('configcontent') -> select();
		$sum   = (int)$this->config_list[0]['configcontent'];
        $this -> assign('sum',$sum);
		$page -> setConfig('header', '篇记录');
        $page -> setConfig('first', '<<');
        $page -> setConfig('last', '>>');
        $this -> assign('page', $page->show());
        $html = $this->fetch();
		header('Cache-control: no-store');
		header('pragma:no-cache');
		exit($html);
    }
	
	//选项添加
    public function feature_tab_add() {
        $this -> feature_db    = M('feature');
		$where = array('status' => 1);
        $this -> feature_list  = $this->feature_db->where($where)->field('feature_id,name')->select();
		$this -> assign('featurelist',$this->feature_list);
		$this -> feature_tab_db = M('market_feature_tab');
		$condition = array('status' => 1);
		$this -> assign('count',$this -> feature_tab_db -> where($condition) -> count() + 1);
        $this -> display();
    }
	
	//选项添加执行
    public function feature_tab_add_do() {
        $this->feature_tab_db = M('market_feature_tab');
		isset($_POST['feature_id']) && $map['feature_id'] = (int)$_POST['feature_id'];
		isset($_POST['tab_name']) && $map['tab_name'] = (string)$_POST['tab_name'];
		isset($_POST['tab_desc']) && $map['tab_desc'] = (string)$_POST['tab_desc'];
		isset($_POST['rank']) && $map['rank']         = (int)$_POST['rank'];
		if(empty($map['feature_id'])){
		    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_add');
			$this -> error('请选择专题');
		}
		if(empty($map['tab_name'])){
		    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_add');
			$this -> error('请输入选项卡名称');
		}
		if(empty($map['tab_desc'])){
		    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_add');
			$this -> error('请输入选项卡描述');
		}
		$this -> config_db = new Model();
		$condition = array('status'=>1,'config_type' => 'WEBMARKET_FEATURE_TAB_NUM');
		$this -> config_list = $this -> config_db ->table('pu_config') -> where($condition) 
					-> field('configcontent') -> select();
		$sum   = (int)$this->config_list[0]['configcontent'];
		$where = array('status' => 1);
		$count = $this->feature_tab_db -> where($where)->count();
		if( $count >= $sum){
			$this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_list');
			$this -> error('选项卡数量大于预定数量'.$this->config_list[0]['configcontent']);
		}else{
			$map['status']       = 1;
			$map['update_time']  = time();
			if ($tab_id = $this -> feature_tab_db -> add($map)) {
				//更新排序
				$this -> _updateRankInfo('sj_market_feature_tab','rank',$tab_id,$where,$map['rank']);
				$this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_list');
				$this -> writelog('专题页信息管理-专题页选项卡列表添加了tab_id为'.$tab_id.'的选项', 'sj_market_feature_tab', $tab_id);
				$this -> success('添加成功');
			}else{
				$this -> assign('jumpUrl','/index.php/Webmarket/FeatureManage/feature_tab_add');
				$this -> error('添加失败');
			}
		}
    }
	
	//选项启停用
    public function feature_tab_del() {
	    $this -> feature_tab_db = M('market_feature_tab');
	    //启用时判断数量
	    if((int)$_GET['status'] == 1){
			$this -> config_db = new Model();
			$condition = array('status'=>1,'config_type' => 'WEBMARKET_FEATURE_TAB_NUM');
			$this -> config_list = $this -> config_db ->table('pu_config') -> where($condition) 
						-> field('configcontent') -> select();
			$sum  = (int)$this->config_list[0]['configcontent'];
			$map  = array( '`status`' => 1);
			$count = $this->feature_tab_db->where($map)->count();
		    if( $count >= $sum){
			    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_list');
				$this -> error('选项卡数量大于预定数量'.$this -> config_list[0]['configcontent'].',请停用某个选项卡');
			}
		}
		$tab_id = (int)$_GET['tab_id'];
		$where  = array('tab_id' => $tab_id);
		$data['status']      = (int)$_GET['status'];
		$data['rank']        = rand(1000, 1999);
		$data['update_time'] = time();
		$log = $this->logcheck(array('tab_id' => $tab_id),'sj_market_feature_tab',$data,$this -> feature_tab_db);
		$this -> feature_tab_db -> where($where) -> save($data);
		//停用选项卡
		if($data['status'] == 0){
		    $this -> feature_tab_list = $this -> feature_tab_db -> where($map)->order('rank asc')->select();
			foreach($this -> feature_tab_list as $rank => $feature_tab){
				$this -> feature_tab_db -> query("UPDATE __TABLE__ SET rank = ".($rank + 1)." WHERE `status` = 1 AND `tab_id` = " .$feature_tab['tab_id']);
			}
		}
		//启用选项卡
		if($data['status'] == 1){
			$this -> feature_tab_list = $this -> feature_tab_db -> where($map)->order('rank asc')->select();
			foreach($this -> feature_tab_list as $rank => $feature_tab){
				$this -> feature_tab_db -> query("UPDATE __TABLE__ SET rank = ".($rank + 1)." WHERE `status` = 1 AND `tab_id` = " .$feature_tab['tab_id']);
			}
		}
		$this -> assign('jumpUrl', "/index.php/Webmarket/FeatureManage/feature_tab_list/status/".$data['status']);
		$this -> writelog('专题页信息管理-专题页选项卡列表修改了tab_id为'.$tab_id.'的选项'.$log);
		$this -> success('操作成功');
    }
	
	//选项编辑
	public function feature_tab_edit(){
	    $tab_id = (int)$_GET['tab_id'];
	    $where  =  array('tab_id' => $tab_id,'status' => 1);
		$this -> feature_tab_db = M('market_feature_tab');
		$this -> feature_tab_list = $this -> feature_tab_db -> where($where) -> select();
		$this -> feature_db    = M('feature');
		$map   = array( 'status' => 1 );
        $this -> feature_list  = $this -> feature_db -> where($map) -> field('feature_id,name') -> select();
		$this -> assign('featurelist',$this -> feature_list);
		$this -> assign('tabinfo',$this -> feature_tab_list[0]);
		$this -> assign('count',$this -> feature_tab_db -> where($map) ->count());
        $this -> display();
	}
	
	//选项编辑执行
	public function feature_tab_edit_do(){
	    $this -> feature_tab_db = M('market_feature_tab');
		$tab_id =  (int)$_POST['tab_id'];
		$where  = array( 'tab_id' => $tab_id);
		isset($_POST['feature_id']) && $map['feature_id'] = (int)$_POST['feature_id'];
		isset($_POST['tab_name']) && $map['tab_name'] = (string)$_POST['tab_name'];
		isset($_POST['tab_desc']) && $map['tab_desc'] = (string)$_POST['tab_desc'];
		isset($_POST['rank']) && $map['rank']         = (int)$_POST['rank'];
		if(empty($map['feature_id'])){
		    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_add');
			$this -> error('请选择专题');
		}
		if(empty($map['tab_name'])){
		    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_add');
			$this -> error('请输入选项卡名称');
		}
		if(empty($map['tab_desc'])){
		    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_add');
			$this -> error('请输入选项卡描述');
		}
		$map['update_time'] = time();
		$map['status']      = 1;
		$log = $this -> logcheck(array('tab_id' => $tab_id),'sj_market_feature_tab',$map,$this->feature_tab_db);
		if ($this->feature_tab_db -> where($where)->save($map)) {
			$where_rank = '`status` = 1';
			//更新排序
			$this->_updateRankInfo('sj_market_feature_tab','rank',$tab_id,$where_rank,$map['rank']);
			$this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_list');
			$this -> writelog('专题页信息管理-专题页选项卡列表编辑了tab_id为'.$tab_id.'的选项'.$log);
			$this -> success('编辑成功');
		}else{
		    $this -> assign('jumpUrl', '/index.php/Webmarket/FeatureManage/feature_tab_edit');
			$this -> error('编辑失败');
		}
	}
	
	//更新选项排序
	public function feature_tab_edit_rank(){
	    $tab_id     = (int)$_GET['tab_id'];
		$rank       = (int)$_GET['rank'];
		$lr         = isset($_GET['lr']) ? (int)$_GET['lr'] : 10;
		$p          = isset($_GET['p'])  ? (int)$_GET['p']  : 1;
		$where      = ' `status` = 1 ';
	    //更新排序
		$param = $this->_updateRankInfo('sj_market_feature_tab','rank',$tab_id,$where,$rank,$lr,$p);
		$this -> feature_tab_db = M('market_feature_tab');
		$condition = array( 'tab_id' => $tab_id);
		$map   = array( 'status' => 1 , 'update_time' => time());
		$this -> feature_tab_db -> where($condition)->save($map);
		$this -> writelog('专题页信息管理-专题页选项卡列表更新了为'.$tab_id.',的rank排序','sj_market_feature_tab', $tab_id);
		exit(json_encode($param));   
	}
}