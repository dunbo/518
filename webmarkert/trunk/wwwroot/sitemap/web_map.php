<?php
/** 网站导航类 */ 
class web_map {
	
	/** 模板 */
	public $tpl;

	/** model */
	public $model;

	/** 应用分类id */
	private $app_cid = 1;

	/** 游戏分类id */
	private $game_cid = 2;

	/** 应用/游戏 */
	private $soft = array();

	/** 应用/游戏id */
	private $softid = array();

	/** 应用软件每页数 */
	private $pre_page = 200;

	private $show_page = 35;
	
	public $softlist_logic;
	
	/** 当前页数 */
	private $cur_page = null;
	
	private $ca;

	public function __construct() {
		
		$this->model = new GoModel();

		$this->cur_page = $_GET['page'];

	}
		
	/** 输出导航 */
	public function showMap() {
	
		global $map_pre;

		$this->tpl->out['map_pre'] = $map_pre;

		$this->showPage();

		/** 如果当前页数不为数值，输出各分类 */
		if (!is_numeric($this->cur_page)) {
			
			$this->showIndex();	

			$this->showApp();

			$this->showGame();

			$this->showTheme();

			$this->showHotApp();

			$this->showHotGame();

			$this->tpl->display('web_map_category.html');


		} else {
				
			$this->tpl->out['everSoft'] = $this->everSoft();

			$this->tpl->display('web_map_soft.html');

		}
	
	}
	
	/** 
	 * 首页输出
	 */
	private function showIndex() {

		global $map_config;

		
		$this->tpl->out['map_config'] = $map_config;
		
	}
	
	/**
	 * 应用输出
	 */
	private function showApp() {

		$this->soft = array();
		$this->ca = $this->app_cid;

		$this->categoryTag();
		
		$this->tpl->out['soft_app'] = $this->soft;

	}
	
	/**
	 * 游戏输出
	 */
	private function showGame() {

		$this->soft = array();
		$this->ca = $this->game_cid;

		$this->categoryTag();

		$this->tpl->out['soft_game'] = $this->soft;

	}
	
	/**
	 * 专题输出
	 */
	private function showTheme() {
			
		$params = array(
			'LIST_INDEX_START' => 0,
			'LIST_INDEX_SIZE' => 50,
			'VR' => 1
		);

		$result = gomarket_action('soft.GoGetSoftSubject', $params);

		foreach($result['DATA'] as $key => $val){
			if($val[1] > 1000000){
				$val[1] = intval($val[1] / 1000000);
			}
			$feature_list[] = $val;
		}
		
		$this->tpl->out['feature_list'] = $feature_list;

	}

	/**
	 * 热门应用
	 */
	private function showHotApp() {
	
		$this->ca = $this->app_cid;
		
		$this->HOTcategory();

	    $this->tpl->out['app_list'] = $this->soft;
		
	}
	
	/**
	 * 热门游戏
	 */
	private function showHotGame() {
		
		$this->ca = $this->game_cid;
	
		$this->HOTcategory();
	
	    $this->tpl->out['game_list'] = $this->soft;
	}
	/**
	 * 当页软件应用列表
	 */
	private function everSoft() {
		$params = array(
			'LIST_INDEX_START' => ($_GET['page'] - 1) * $this->pre_page,
			'LIST_INDEX_SIZE' => $this->pre_page,
			'GET_COUNT' => 1,
			'VR' => 1
		);

		$result = gomarket_action('soft.GoGetHomeHot', $params);
		$info = array();
		foreach ($result['DATA'] as $val) {
			$info[] = array(
				'softid' => $val[0],
				'softname' => $val[2],
				'package' => $val[7],
			);
		}
		return $info;
	}
	
	/**
	 * 分页输出
	 */
	private function showPage() {
		$params = array(
			'LIST_INDEX_START' => 0,
			'LIST_INDEX_SIZE' => $this->pre_page,
			'GET_COUNT' => 1,
			'VR' => 1
		);

		$result = gomarket_action('soft.GoGetHomeHot', $params);
		
		$softAllCount = $result['COUNT'];

		$page = pagination_arr($this->cur_page, $softAllCount, $this->pre_page,
			$this->show_page, $page_url_str = 'page', $request_uri = '');

		$this->tpl->out['page'] = $page;
	}

	/**
	 * 应用/游戏分类
	 */
	private function category() {

		$result = gomarket_action('soft.GoGetSoftCategoryList', array('TYPE' => $this->ca == 1 ? 0 : 1, 'VR' => 1));
		$this->soft = $result['DATA'];
		
	}

	//应用/游戏分类标签
	private function categoryTag(){
		$result = gomarket_action('v53.GoGetCategoryTag', array('CATE_TYPE' => $this->ca == 1 ? 0 : 1,'VR'=>14));
		$new_res = array();
        //过滤掉没有标签的分类和电子书分类
        foreach($result['CATEGORY_GROUP'] as $val){
            if(!empty($val[3]) && $val[0]!=3){
                $new_res[] = $val;
            }
        }
		$this->soft = $new_res;
	}
	
	/**
	 * HOT 应用/游戏
	 */
	private function HOTcategory() {
	
		$parameters = array(
			'LIST_INDEX_START' => 0,
			'VR' => 1,
			'ORDER' => 1,
			'ID' => $this->ca
		);
		
		$results = gomarket_action('soft.GoGetCategoryAllSoftList',$parameters);
		$this->soft = scorehtml($results['DATA']);
		
	}

	
}
