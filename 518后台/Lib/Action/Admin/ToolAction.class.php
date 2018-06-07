<?php
class ToolAction extends CommonAction {
	function get_config()
	{
		return array(
            1 => array('TMS任务缓存', '/cache/cache_task.php'),
            2 => array('配置缓存', '/cache/cache_config.php'),
            3 => array('OTA缓存', '/cache/cache_market_update.php'),
            4 => array('通用广告SDK缓存', '/sdk/cache_sdk_ad.php'),
            5 => array('活动缓存', '/cache/cache_ad.php activity'),
            6 => array('礼包缓存', '/cache/gift/cache_game_gift_list.php'),
            7 => array('弹窗广告缓存', '/cache/cache_ad.php mixad.pop_ad'),
            8 => array('轮播缓存', '/cache/cache_ad.php mixad.slider_new'),
            9 => array('灵活运营/文字快捷入口缓存', '/cache/cache_ad.php mixad.normal_banner'),
            10 => array('文字链缓存', '/cache/cache_ad.php mixad.text_tip'),
            11 => array('返回运营缓存', '/cache/cache_ad.php mixad.back_ad'),
            12 => array('频道运营缓存', '/cache/cache_category_extent.php'),
            13 => array('闪屏缓存', '/cache/cache_ad.php mixad.splash'),
            14 => array('悬浮窗/动画广告缓存', '/cache/cache_ad.php mixad.animation'),
            15 => array('定制推送', '/cache/cache_ad.php mixad.req_custom_push'),
            16 => array('新服/开测列表缓存', '/cache/gift/cache_game_newservice_list.php'),
            17 => array('软件详情页面相关缓存（新服/开测/活动/礼包/标签）', '/cache/cache_package_info.php'),
            18 => array('外部推广通用跳转', '/cache/cache_ad.php mixad.web_jump'),
            19 => array('桌面预下载', '/cache/cache_ad.php market.launcher_icon'),
            20 => array('软件屏蔽缓存', '/cache/cache_areas_filter.php'),
            21 => array('专题列表缓存', '/cache/cache_topics.php'),
            22 => array('静默下载', '/cache/cache_ad.php mixad.silent_install'),
            23 => array('合作内容缓存', '/cache/cache_ad.php coop_info.all_coop_data'),
            24 => array('推送&被动预下载', '/cache/cache_ad.php mixad.req_push'),
            25 => array('市场首页缓存', '/cache_extent_v1.php'),
            26 => array('动态TAB缓存', '/cache/cache_ad.php mixad.MainPageTab'),
 			27 => array('搜索热词缓存','/cache/search_key_hot_keywords.php'),
			28 => array('649市场首页下拉刷新缓存', '/cache_extent_v2.php 105'),
			29 => array('649版块帖子缓存','/cache/cache_ad.php bbs_banner.bbs_banner_data')
		);
	}
	
    function index(){
		$config = $this->get_config();
		
		$model = new Model();
		$where = array();
		$total = $model->table('sj_task_client_log')->count();

		$limit = 50;
		import('@.ORG.Page2');
		$Page = new Page($total, $limit);
		$Page->rollPage = 10;
        $Page->setConfig('header','条记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
		
		$list = $model->table('sj_task_client_log')->limit($Page->firstRow.','.$Page->listRows)->field('*')->order('id desc')->select();
		$refresh = false;
		foreach ($list as $val) {
			if ($val['execution_status'] == 0 || $val['execution_status'] == 3) {
				$refresh = true;
				break;
			}
		}
		$this->assign('refresh', $refresh);
		$this->assign('list', $list);
		$this->assign('page', $Page->show());
		$this->assign('total', $total);
		
		$this->assign('config', $config);
		$this->display('index');
    }
	
	function pub_notify()
	{
		$config = $this->get_config();
		$model = new Model();
		if (!empty($_GET) && isset($_GET['id']) && isset($config[$_GET['id']])) {
			list($name, $path) = $config[$_GET['id']];
			
			$now = time();
			$data = array(
				'fromip' => $_SERVER['REMOTE_ADDR'],
				'actionexp'=> "{$_SESSION['admin']['admin_user_name']}, 刷新了[{$name}]",
				'log_time' => $now,
				'update_time' => $now,
			);
			$log_id = $model->table('sj_task_client_log')->add($data);
			$task_client = get_task_client();
			$task_val = array(
				"type"=> $type,
				"atime"=> $now,
				"file"=> $path,
				"log_id" =>$log_id,
			);
			$job_handle = $task_client->doBackground("ucenter_callback", json_encode($task_val));
			
			$where = array(
				'id' =>$log_id
			);
			$data = array(
				'job_handle' => $job_handle  
			);
			$model->table('sj_task_client_log')->where($where)->save($data);
		}
	}
}
?>
