<?php

if (!defined('IMG_HOST'))
    define('IMG_HOST', '/data/att/m.goapk.com');
if (!defined('IMG_URL'))
    define('IMG_URL', IMGATT_HOST);

class OnlinegameAction extends CommonAction {

    const HOST_TAG = "<!--{ANZHI_IMAGE_HOST}-->";
	
    private $becomes = array('&', '"', "'", '<', '>');
    private $active_module_url = "/tmp/azyx/activities";         //活动礼包模板临时文件夹
    private $active_module_true_url = "/data/www/wwwroot/new-wwwroot/azyx-html/activities";   //活动礼包模板存储文件夹
    private $news_module_url = "/tmp/azyx/news";     //新闻资讯模板临时文件夹
    private $news_module_true_url = "/data/www/wwwroot/new-wwwroot/azyx-html/news";   //新闻资讯模板存储文件夹
    private $header = "<!DOCTYPE html>
						<html>
						<head>
						<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
						<meta name='viewport' content='width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no'>
						<META HTTP-EQUIV='Cache-Control' CONTENT='max-age=5'>
						<meta content='telephone=no' name='format-detection'>
						<meta name='keywords' content='Android,安卓,安卓网,安卓市场,安卓游戏,电子市场,安卓软件,Android游戏,安卓手机,游戏汉化,手机游戏,Android软件,软件下载,最新汉化软件,安卓游戏下载,游戏汉化' />
						<meta name='description' content='安智市场,Android,安卓,安卓网,安卓市场,电子市场,安卓游戏,应用商店-国内最专业的Android安卓电子市场，提供海量安卓软件、Android手机游戏、安卓最新汉化软件、汉化游戏资源及最新APK汉化、汉化破解APP、APK免费下载，致力于为用户打造最贴心的Android安卓手机应用商店' />
						<title>安智活动2</title>";

    //网游名称/icon配置
    function game_icon_list() {
        $model = new Model();
        $result = $model->table('sj_olgame_icon')->where(array('status' => 1))->select();
        $news_result = $model->table('pu_config')->where("config_type = 'game_news_switch' and status = 1")->select();

        $news_switch_change = $_GET['news_switch'];
        if (isset($_GET['news_switch'])) {
            $where['_string'] = "config_type = 'game_news_switch' and status= 1";
            $data['configcontent'] = $news_switch_change;
            $news_change_result = $model->table('pu_config')->where($where)->save($data);
            if ($news_switch_change == 1) {
                $change = '开';
            } else {
                $change = '关';
            }
            $this->writelog("已修改安卓游戏新闻资讯开关为{$news_switch_change}",'pu_config',"config_type = 'game_news_switch' and status= 1", __ACTION__ ,"","edit");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_icon_list');
            $this->success("修改成功");
        }
        $this->assign("news_switch", $news_result[0]['configcontent']);
        $this->assign("result", $result);
        $this->display();
    }

    //编辑网游配置显示
    function game_icon_edit() {
        $model = new Model();
        $id = $_GET['id'];
        $result = $model->table('sj_olgame_icon')->where(array('id' => $id, 'status' => 1))->select();
        $this->assign("result", $result);
        $this->display();
    }

    //编辑网游配置提交
    function game_icon_edit_do() {
        $model = new Model();
        $id = $_POST['id'];
        $game_name = $_POST['game_name'];
        if (!$game_name) {
            $this->error("导航名称不能为空");
        }
        if (strlen($game_name) > 9) {
            $this->error("导航名称格式错误");
        }
        $high_icon = $_FILES['high_icon'];
        $halve_icon = $_FILES['halve_icon'];


        //判断高分图标高宽
        if ($high_icon['size']) {
            $high_wd = getimagesize($high_icon['tmp_name']);
            $widhig_hg = $high_wd[3];
            $wh_hg = explode(' ', $widhig_hg);
            $wh1_hg = $wh_hg[0];
            $widths_hg = explode('=', $wh1_hg);
            $width1_hg = substr($widths_hg[1], 0, -1);
            $width_go_hg = substr($width1_hg, 1);
            $hi1_hg = $wh_hg[1];
            $heights_hg = explode('=', $hi1_hg);
            $height1_hg = substr($heights_hg[1], 0, -1);
            $height_go_hg = substr($height1_hg, 1);
            if ($width_go_hg != 46 || $height_go_hg != 40) {
                $this->error("高分辨率图标大小不符合条件");
            }

            if ($high_icon['type'] != 'image/png' && $high_icon['type'] != 'image/x-png') {
                $this->error("高分辨率图标格式错误");
            }

            if ($high_icon['size'] > 35 * 1024) {
                $this->error("高分辨率图标尺寸不符合条件");
            }
        }


        //判断中分图标高宽
        if ($halve_icon['size']) {
            $halve_wd = getimagesize($halve_icon['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
            if ($width_go_ha != 30 || $height_go_ha != 27) {
                $this->error("中分辨率图标大小不符合条件");
            }

            if ($halve_icon['type'] != 'image/png') {
                $this->error("中分辨率图标格式错误");
            }

            if ($halve_icon['size'] > 35 * 1024) {
                $this->error("中分辨率图标尺寸不符");
            }
        }


        if ($high_icon['size'] || $halve_icon['size']) {
            $path = date("Ym/d/", time());
            $config = array(
                'multi_config' => array(
                    'high_icon' => array(
                        'savepath' => UPLOAD_PATH . '/img/' . $path,
                        'saveRule' => 'getmsec'
                    ),
                    'halve_icon' => array(
                        'savepath' => UPLOAD_PATH . '/img/' . $path,
                        'saveRule' => 'getmsec'
                    ),
                ),
            );
            $list = $this->_uploadapk(0, $config);

            foreach ($list['image'] as $key => $val) {
                if ($val['post_name'] == 'high_icon') {
                    $data['high_icon'] = $val['url'];
                }
                if ($val['post_name'] == 'halve_icon') {
                    $data['halve_icon'] = $val['url'];
                }
            }
        }

        $been_result = $model->table('sj_olgame_icon')->where(array('id' => $id))->select();
        $data['game_name'] = $game_name;
        $data['update_tm'] = time();
        $log_result = $this->logcheck(array('id' => $id), 'sj_olgame_icon', $data, $model);
        $result = $model->table('sj_olgame_icon')->where(array('id' => $id))->save($data);
        $data_all['game_name'] = $game_name;
        $all_result = $model->table('sj_olgame_icon')->where(array('game_name' => $been_result[0]['game_name']))->save($data_all);
        if ($result) {
            $this->writelog("网游_网游名称/icon配置_已编辑id为{$id}的网游配置" . $log_result,'sj_olgame_icon',"{$id}", __ACTION__ ,"","edit");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_icon_list');
            $this->success("编辑成功");
        }
    }

    //活动礼包
    function active_gift_list() {

        $active_model = D('sendNum.sendNum');

        //刷新已过期活动礼包
        $now_where['_string'] = "a.status = 1 and o.status = 1 and o.rank != 0 and a.end_tm < " . $time  . " and a.active_from = 2";
        $now_result = $active_model->table('sendNum.sendnum_active a left join sendNum.olgame_active o on o.active_id=a.id')->field('a.*')->where($now_where)->select();
        foreach ($now_result as $key => $val) {
            $rank_where['_string'] = "status = 1 and active_id = {$val['id']}";
            $rank_result = $active_model->table('olgame_active')->where($rank_where)->select();

            if ($rank_result[0]['rank'] != 0) {
                $other_where['_string'] = "status = 1 and rank > {$rank_result[0]['rank']}";
                $other_result = $active_model->table('olgame_active')->where($other_where)->select();

                foreach ($other_result as $key => $val) {
                    $change_where['rank'] = $val['rank'];
                    $change_data = array(
                        'rank' => $val['rank'] - 1,
                    );
                    $change_result = $active_model->update_active_content($change_where, $change_data);
                }
                $myself_where['id'] = $rank_result[0]['id'];
                $myself_data = array(
                    'rank' => 0,
                    'selection' => 0,
                );
                $myself_result = $active_model->update_active_content($myself_where, $myself_data);
            }
        }


        //刷新开启网游精选状态的已过期活动礼包的网游精选状态为关闭
        $last_result = $active_model->table('olgame_active')->where(array('selection' => 1, 'status' => 1))->select();
        $last_result_active = $active_model->table('sendnum_active')->where(array('id' => $last_result[0]['active_id']))->select();
        if ($last_result_active[0]['end_tm'] < time()) {
            $selection_where['active_id'] = $last_result_active[0]['id'];
            $data = array(
                'selection' => 0,
            );
            $selection_result = $active_model->update_active_content($selection_where, $data);
        }



        $no_where['_string'] = "status = 1 and active_from = 2 and end_tm >" . time() . "";
        $no_result = $active_model->table('sendnum_active')->where($no_where)->select();
        foreach ($no_result as $key => $val) {
            $no_id_str .= $val['id'] . ',';
        }
        import("@.ORG.Page");
        $no_id = substr($no_id_str, 0, -1);
        $where['_string'] = "status = 1 and active_id in ({$no_id})";
        $count = $active_model->table('olgame_active')->where($where)->order('rank')->count();

        $Page = new Page($count, 20, $limit);
        $result = $active_model->table('olgame_active')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('rank')->select();
        if (!$_GET['p']) {
            $_GET['p'] = 1;
        }
        foreach ($result as $key => $val) {
            $active_where['id'] = $val['active_id'];
            $active_result = $active_model->table('sendnum_active')->where($active_where)->select();
            $val['num'] = $key + 1 + (($_GET['p'] - 1) * $_GET['lr']);
            $val['cut_tm'] = $active_result[0]['end_tm'];
            $val['start_tm'] = $active_result[0]['start_tm'];
            $val['all_num'] = $active_result[0]['num_cnt'];
            $else_result = $active_model->table('sendnum_number')->where(array('active_id' => $val['active_id'], 'status' => 0))->select();
            $val['surplus_num'] = count($else_result);
            $val['limit_num'] = $active_result[0]['conf_cnt'];
            $val['release_tm'] = $val['start_tm'];
            $val['active_name'] = $active_result[0]['active_name'];
            $val['active_status'] = $active_result[0]['status'];
            $result[$key] = $val;
        }

        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('page', $show);

        $this->assign("count", $count);
        $this->assign("result", $result);
        $this->display();
    }

    //迭代7活动礼包_发布中
    public function active_gift_list1() {
        $active_model = D('sendNum.sendNum');

        $qu_array = array(
			"1" => "论坛",
            "2" => "安卓游戏",
            "4" => "安智市场",
            "8" => "SDK",
			"16"=> "微信"
        );
		$time = time();
        //刷新已过期活动礼包
		if(!empty($_GET['gift_category'])){
			$now_where['_string'] = "a.gift_category = {$_GET['gift_category']} and a.status = 1 and o.status = 1 and o.rank != 0 and end_tm < " . $time ;
			$now_result = $active_model->table('sendNum.sendnum_active a left join sendNum.olgame_active o on o.active_id=a.id')->field('a.*')->where($now_where)->select();		
			foreach ($now_result as $key => $val) {
				$rank_where['_string'] = "status = 1 and active_id = {$val['id']}";
				$rank_result = $active_model->table('sendNum.olgame_active')->where($rank_where)->select();
				if ($rank_result[0]['rank'] != 0) {
					$other_where['_string'] = "gift_category = {$_GET['gift_category']} and status = 1 and rank > {$rank_result[0]['rank']}";
					$other_result = $active_model->table('sendNum.olgame_active')->where($other_where)->select();
					foreach ($other_result as $key => $val) {
						$change_where['id'] = $val['id'];
						$change_data = array(
							'rank' => $val['rank'] - 1,
						);
						$change_result = $active_model->update_active_content($change_where, $change_data);
					}

					$myself_where['id'] = $rank_result[0]['id'];

					$myself_data = array(
						'rank' => 0,
						'selection' => 0,
					);
					$myself_result = $active_model->update_active_content($myself_where, $myself_data);
				}
			}
		}
        //刷新开启网游精选状态的已过期活动礼包的网游精选状态为关闭
        $last_result = $active_model->table('sendNum.olgame_active')->where(array('selection' => 1, 'status' => 1))->select();
		if($last_result){
			$last_result_active = $active_model->table('sendNum.sendnum_active')->where(array('id' => $last_result[0]['active_id']))->select();
			if ($last_result_active[0]['end_tm'] < $time) {
				$selection_where['active_id'] = $last_result_active[0]['id'];
				$data = array(
					'selection' => 0,
				);
				$selection_result = $active_model->update_active_content($selection_where, $data);
			}
		}
        
		$where = array(
			'b.end_tm' => array('egt',$time),
			'a.status' => 1
		);
        import("@.ORG.Page");

        //搜索条件
        if ($_GET['active_id'] && !empty($_GET['active_id'])) {
            $this->assign('id', $_GET['active_id']);
            $where['a.active_id'] = $_GET['active_id'];
        }
        if ($_GET['softname'] && !empty($_GET['softname'])) {
            $this->assign('softname', $_GET['softname']);
            $model = new Model();
            $subQuery = $model->table('sj_soft')->field('package')->where('softname like "%' . $_GET['softname'] . '%" and status=1 and hide=1')->group('package')->select();
            $pkg = array();
            foreach ($subQuery as $v) {
                if ($v['package'])
                    $pkg[] = $v['package'];
            }
            if ($pkg)
                $pkg = array_unique($pkg);
            $where['a.apply_pkg'] = array('in',$pkg);
        }
        if ($_GET['apply_pkg'] && !empty($_GET['apply_pkg'])) {
            $this->assign('apply_pkg', $_GET['apply_pkg']);
            $where['a.apply_pkg'] = $_GET['apply_pkg'];
        }
        if ($_GET['start_tm'] && !empty($_GET['start_tm'])) {
            $this->assign('start_tm', $_GET['start_tm']);
            $start_tm = strtotime($_GET['start_tm']);
            $where['b.start_tm']= array('egt',$start_tm); 
        }
        if ($_GET['end_tm'] && !empty($_GET['end_tm'])) {
            $this->assign('end_tm', $_GET['end_tm']);
            $end_tm = strtotime($_GET['end_tm']);
            $where['b.end_tm'] = array('elt',$end_tm); 
        }
        if ($_GET['active_from'] && !empty($_GET['active_from'])) {
            $this->assign('active_from', $_GET['active_from']);
            $from = array(
				"论坛" => "1,3,5,9,15,17",
                "安卓游戏" => "2,3,6,10,15,18",
                "安智市场" => "4,5,6,12,15,20",
                "SDK" => "8,9,10,12,15,24",
				"微信" => "16,17,18,20,24",
            );
            $where['b.active_from'] = array('in',$from[$_GET['active_from']]); 
        }else{
            $where['b.active_from']  = array('exp'," not in('32','64','128')");
        }

        if ($_GET['type'] && !empty($_GET['type'])) {
            $this->assign('gift_types', $_GET['type']);
            $where['b.gift_type'] =  $_GET['type'] ;
        }

        if ($_GET['gift_category'] && !empty($_GET['gift_category'])) {
            $this->assign('gift_category', $_GET['gift_category']);
            $where['b.gift_category'] = $_GET['gift_category'];
        }

        $res = $active_model->table('sendNum.olgame_active a')->join("sendNum.sendnum_active b on a.active_id = b.id")->where($where)->group('b.active_name')->field('a.id')->select();

        $count = count($res);

        $Page = new Page($count, 20, $limit);
        $allcategory = $this->get_catgory_str();
        foreach ($allcategory as $key => $val) {
            $tmpcate = explode(',', $val);
            foreach ($tmpcate as $tmpkey => $tmpval) {
                $tmpcate[$tmpkey] = "," . $tmpval . ",";
            }
            $allcategory[$key] = implode('","', $tmpcate);
        }
        $type = array('1' => '应用', '2' => '游戏', '3' => '电子书');
        $result = $active_model->table('sendNum.olgame_active a')->field('a.id as rank_id,a.active_id,a.apply_pkg,a.usable,a.module_content,a.rank,a.status,a.gift_url,a.create_tm,a.update_tm,a.rerelease_tm,a.be_active_name,a.be_apply_pkg,a.be_usable,a.be_cut_tm,a.be_limit_num,a.intro,a.selection,a.sim_content,a.detail,a.usage,a.exchange_start,a.exchange_end,a.active_type,b.*')->join("sendNum.sendnum_active b on a.active_id = b.id")->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('a.rank asc')->group('a.active_id')->select();

        if (!$_GET['p']) {
            $_GET['p'] = 1;
        }
        if (!$_GET['lr']) {
            $_GET['lr'] = 20;
        }
        $this->assign('img_host', IMG_URL);
        $this->assign('p', $_GET['p']);
        $this->assign('lr', $_GET['lr']);
        $package = array();
        foreach ($result as $v) {
            if ($v['apply_pkg'])
                $package[] = $v['apply_pkg'];
        }
        if ($package) {
            $where = array(
                'package' => array('in', $package),
                'status' => 1,
				'hide' => 1,
            );
			if ($_GET['softname'] && !empty($_GET['softname'])) {
				$where['softname'] = array('like',"%{$_GET['softname']}%");
			}			
            $soft_info = get_table_data($where, "sj_soft", "package", "package,softid,softname,category_id,total_downloaded","softid asc");
			$sdk = get_table_data($where, "sj_soft_whitelist", "package", "package");
            $where = array(
                'apk_name' => array('in', $package),
                'package_status' => array('exp', "> 0"),
            );
            $file_icon_arr = get_table_data($where, "sj_soft_file", "softid", "id,softid,iconurl");
        }
        foreach ($result as $key => $val) {
            $cate = explode(',', $soft_info[$val['apply_pkg']]['category_id']);
            $result[$key]['category_id'] = $cate[1];
            foreach ($allcategory as $allkey => $allval) {
                $catekey = strpos($allval, $result[$key]['category_id']);
                if ($catekey) {
                    $val['type'] = $type[$allkey];
                }
            }
            $active_where['sendnum_active.id'] = $val['active_id'];
            $active_where['sendnum_active.status'] = 1;

            //是否介入sdk
            if ($sdk[$val['apply_pkg']]) {
                $val['sdk_status'] = 1;
            }

            $active_result = $active_model->table('sendnum_active')->join("sendnum_gift_type on sendnum_active.gift_type = sendnum_gift_type.id")->where($active_where)->select();

            if ($active_result[0]['active_from'] == 4 || $active_result[0]['active_from'] == 8 || $active_result[0]['active_from'] == 12) {
                $val['start_button'] = 0;
            } else {
                $val['start_button'] = 1;
            }
            $val['num'] = $key + 1 + (($_GET['p'] - 1) * $_GET['lr']);
            $val['cut_tm'] = $active_result[0]['end_tm'];
            $val['start_tm'] = $active_result[0]['start_tm'];
            $val['all_num'] = $active_result[0]['num_cnt'];
            $else_result = $active_model->table('sendNum.sendnum_number')->where(array('active_id' => $val['active_id'], 'status' => 0))->select();
            $val['surplus_num'] = $active_result[0]['num_cnt'] - $active_result[0]['used_cnt'];
            $val['limit_num'] = $active_result[0]['conf_cnt'];
            $val['release_tm'] = $val['start_tm'];
            $val['active_name'] = $active_result[0]['active_name'];
            $val['active_status'] = $active_result[0]['status'];
            $val['gift_type_name'] = $active_result[0]['gift_type'];

            $active_from = '';
            foreach ($qu_array as $k => $v) {
                if (($active_result[0]['active_from'] & $k) == true) {
                    $active_from .= $v . ',';
                }
            }
            if ($val['gift_category'] == 2) {
                $val['active_from'] = substr($active_from, 0, -1);
            } else {
                $val['active_from'] = '安智市场';
            }
            $val['softname'] = $soft_info[$val['apply_pkg']]['softname'];
            $val['total_downloaded'] = $soft_info[$val['apply_pkg']]['total_downloaded'];
            $val['softid'] = $soft_info[$val['apply_pkg']]['softid'];
            $val['iconurl'] = $file_icon_arr[$val['softid']]['iconurl'];
            $result[$key] = $val;
        }

        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();

        $this->assign('page', $show);
        $gift_type = $active_model->table('sendnum_gift_type')->where(array('status' => 1))->select();
		$gift_type_1 =  array();
		$gift_type_2 =  array();
		foreach( $gift_type as $v){
			if($v['gift_category'] == 1) $gift_type_1[] = $v; 
			if($v['gift_category'] == 2) $gift_type_2[] = $v; 
		}		
        $this->assign('gift_type', $gift_type);
        $this->assign('gift_type_1', $gift_type_1);
        $this->assign('gift_type_2', $gift_type_2);
        $this->assign("count", $count);
        $this->assign("result", $result);
        $this->assign("from", 'list');
        $this->display();
    }

    //开启/关闭网游精选
    function p_market_change_selection() {
        $model = new Model();
        $active_model = D('sendNum.sendNum');
        $id = $_GET['id'];
        $have_been = $active_model->table('olgame_active')->where(array('active_id' => $id))->select();

        $one_been = $active_model->table('olgame_active')->where(array('selection' => 1, 'status' => 1))->select();
        $where['active_id'] = $id;
        $time_result = $active_model->table('sendnum_active')->where(array('id' => $have_been[0]['active_id']))->select();
        if ($have_been[0]['selection'] == 1) {
            $data = array(
                'selection' => 0
            );
            $log = "关闭";
        } else {


            if ($time_result[0]['end_tm'] < time()) {
                $this->error("已过期市场礼包不能成为精选网游");
            }
            if ($one_been) {
                $this->error("只能开启一个市场礼包成为精选网游");
            }
            if (!$have_been[0]['intro']) {
                $this->error("您没有填写简介内容，请先填写内容后在开启");
            }
            $data = array(
                'selection' => 1
            );
            $log = "开启";
        }
        $result = $active_model->update_active_content($where, $data);

        if ($result) {
            $this->writelog("网游_网游名称/icon配置_已{$log}id为{$id}的精选网游配置",'olgame_active',"{$id}", __ACTION__ ,"","edit");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Onlinegame/active_gift_list1/gift_category/{$time_result[0]['gift_category']}");
            $this->success("编辑成功");
        } else {
            $this->error("编辑失败");
        }
    }

    //迭代7活动礼包取消
    function p_market_del() {
        $emailmodel = D("Dev.Sendemail");
        $config_txt = C('_config_txt_');
        $active_model = D('sendNum.sendNum');
		$model = new Model();		
		$time = time();
        $id = $_GET['del_id'];
        $reason = $_GET['del_reason'];
        $been_result = $active_model->table('sendnum_active')->where(array('id' => $id))->find();
        $change_result = $active_model->table('olgame_active')->where(array('active_id' => $id))->find();
        $pkg_result = $active_model->table('newgomarket.sj_soft')->where(array('package' => $change_result['apply_pkg']))->field('softid,softname')->find();
        $result_active = $active_model->active_save(array('id' => $id), array('status'=>0));
		if(!$result_active)  $this->error("操作失败");
		$data = array(
			'status' =>0,
			'rank' =>0,
			'update_tm' => $time,
		);
        $result_content = $active_model->update_active_content(array('active_id' => $id), $data);
		if(!$result_content)  $this->error("操作失败2");
		$where = array(
			'sendnumactive_id' => $id
		);
		$data = array(
			'status' => 5,
			'rand_status' => 7,
			'reject_reason' => $reason, 
			'update_tm' => $time
		);
        $active_model->table('sendnum_tmp')->where($where)->save($data);
		if (!empty($been_result['dev_id'])) {
			//前台提醒
			$tm = date("Y-m-d H:i:s", $time);
			$search = array("softname", "giftname", "tm", "msg");
			$replace = array($pkg_result['softname'], $been_result['active_name'], $tm, $reason);
			$msg = str_replace($search, $replace, $config_txt['gift_reset']);
			$emailmodel->dev_remind_add($been_result['dev_id'], $msg);
			//发送邮件提醒
			$dever = $model->table('pu_developer')->where("dev_id={$been_result[0]['dev_id']}")->field('dev_id,email,dev_name')->find();
			$subject = $config_txt['gift_reset_subject'];
			$search2 = array("devname", "softname", "giftname", "tm", "msg");
			$replace2 = array($dever['dev_name'], $pkg_result['softname'], $been_result['active_name'], $tm, $reason);
			$email_cont = str_replace($search2, $replace2, $config_txt['gift_reset_txt']);

			$emailmodel->realsend($dever['email'], $dever['dev_name'], $subject, $email_cont);
		}
		$where = array(
			'status' => 1,
			'rank' => array('exp'," > {$change_result['rank']} and rank >0")
		);
		$data = array(
			'rank' => array("exp","`rank`-1"),
		);
		$result = $active_model->update_active_content($where, $data);
		//
		$fileicon = $model->table('sj_soft_file')->where(array('apk_name' => $change_result['apply_pkg']))->field('iconurl')->find();
		$send_data = array(
			'serviceId' => $been_result['active_from'],       //业务线id  ,礼包的使用业务线
			'giftName' => $been_result['active_name'],//"奖品名称"
			'giftType' => '2',//奖品类型：1:积分  2：礼包  3：话费
			'giftSoftName' => $pkg_result['softname'],//软件名称
			'giftSoftPname' => $change_result['apply_pkg'],//软件包名
			'giftTotal' =>$been_result['num_cnt'],//礼包总数
			'giftEvSum' =>  $change_result['be_limit_num'],//明日发放限制数量
			'prImage' => 'http://img3.anzhi.com'.$fileicon['iconurl'],//奖品图片
			'prShelvesDate' => $been_result['start_tm'],   //有效期--开始时间--使用时间
			'prUnderDate' => $been_result['end_tm'] ,   //有效期--结束时间
			'prSdate' => $change_result['exchange_start'] ,  //上架时间
			'prEdate' =>  $change_result['exchange_end'], //下架时间
			'prSortno'  => 0,//排序号
			'remark' => $change_result['detail'], //奖品说明
			'giftUse' => '2', //奖品用途   0:抽奖  1:兑换   2:领取
			'useRange' => $change_result['usable'],//使用范围
			'useWay' => $change_result['usage'] ,//使用方法
			'createTime' => $change_result['create_tm'],
			'updateTime' => $time,
			'delStatus' => 1 ,//0:整除  1:删除。
			'ref_id' => $id,
			'fromServiceId' => '007',//数据来源业务线
			'oper_type' => 2,//0:新增、1：修改、2：删除
		);
		$active_model -> send_gift_work($send_data);	
		$this->writelog("网游_礼包设置_已取消id为{$id}的礼包",'sendnum_active',"{$id}", __ACTION__ ,"","del");
		$this->assign("jumpUrl", "/index.php/Sj/Onlinegame/active_gift_list1/gift_category/{$been_result['gift_category']}");
		$this->success("取消成功");
    }

    //迭代7市场礼包编辑
    function p_market_edit_submit() {
        $active_model = D('sendNum.sendNum');
        $model = new Model();
        $active_name = trim($_POST['active_name']);
        $id = $_POST['id'];
        $usable = $_POST['usable'];
        $detail = trim($_POST['detail']);
        $usage = trim($_POST['usage']);
        $exchange_start = strtotime($_POST['exchange_start']);
        $exchange_end = strtotime($_POST['exchange_end']);
        $cut_tm = strtotime($_POST['cut_tm']);
        $start_tm = strtotime($_POST['start_tm']);
        $limit_num = $_POST['limit_num'];
        $sur_num = $_POST['sur_num'];
        $p = $_POST['p'];
        $lr = $_POST['lr'];
		$active_from = 0;
		if ((!empty($_POST['pt_market_bo'])&& $_POST['pt_market_bo'] == 1)||$_POST['pt_market']=='on') {
			$market_num = intval($_POST['sc_num']);
			$active_from += 4;

			if ($market_num == 0 || $market_num == '') {
				$this->error("发放平台激活码数量不能为0");
			}
			if ($market_num < $limit_num) {
				$this->error("每日发放限制不能大于平台发布数量");
			}
			if ($market_num > $_POST['all_num']) {
				$this->error("平台发布数量不能大于总数量");
			}
		} else {
			$market_num = 0;
		}

		if ((!empty($_POST['pt_game_bo'])&& $_POST['pt_game_bo'] == 1)||$_POST['pt_game']=='on') {
			$game_num = intval($_POST['game_num']);
			$active_from += 2;

			if ($game_num == 0 || $game_num == '') {
				$this->error("发放平台激活码数量不能为0");
			}
			if ($game_num < $limit_num) {
				$this->error("每日发放限制不能大于平台发布数量");
			}
			if ($game_num > $_POST['all_num']) {
				$this->error("平台发布数量不能大于总数量");
			}
		} else {
			$game_num = 0;
		}

		if ((!empty($_POST['pt_sdk_bo'])&& $_POST['pt_sdk_bo'] == 1)||$_POST['pt_sdk']=='on') {
			$sdk_num = intval($_POST['sdk_num']);
			$active_from += 8;

			if ($sdk_num == 0 || $sdk_num == '') {
				$this->error("发放平台激活码数量不能为0");
			}
			if ($sdk_num < $limit_num) {
				$this->error("每日发放限制不能大于平台发布数量");
			}
			if ($sdk_num > $_POST['all_num']) {
				$this->error("平台发布数量不能大于总数量");
			}
		} else {
			$sdk_num = 0;
		}
		if ((!empty($_POST['pt_bbs_bo']) && $_POST['pt_bbs_bo'] == 1)||$_POST['pt_bbs']=='on') {
			$bbs_num = intval($_POST['bbs_num']);
			$active_from += 1;
			if ($bbs_num == 0 || $bbs_num == '') {
				$this->error("发放平台激活码数量不能为0");
			}
			if ($bbs_num < $limit_num) {
				$this->error("每日发放限制不能大于平台发布数量");
			}
			if ($bbs_num > $_POST['all_num']) {
				$this->error("平台发布数量不能大于总数量");
			}
		} else {
			$bbs_num = 0;
		}
		if ((!empty($_POST['pt_weixin_bo']) && $_POST['pt_weixin_bo'] == 1)||$_POST['pt_weixin']=='on') {
			$weixin_num = intval($_POST['weixin_num']);
			$active_from += 16;
			if ($weixin_num == 0 || $weixin_num == '') {
				$this->error("发放平台激活码数量不能为0");
			}
			if ($weixin_num < $limit_num) {
				$this->error("每日发放限制不能大于平台发布数量");
			}
			if ($weixin_num > $_POST['all_num']) {
				$this->error("平台发布数量不能大于总数量");
			}
		} else {
			$weixin_num = 0;
		}
		if ($market_num + $game_num + $sdk_num + $bbs_num + $weixin_num != $_POST['all_num']) {
			$this->error("平台发放总数量应等于上传激活码总数量");
		}
		// if($limit_num < 10 && $limit_num != 0){
			// $this->error("每日发放限制不能小于10个");
		// }
        if (!$active_name) {
            $this->error("标题不能为空");
        }

        $gift_type = $_POST['gift_type'];
        if ($gift_type == 0) {
            $this->error("请选择礼包类型");
        }

        if (mb_strlen($active_name, 'utf-8') > 12) {
            $this->error("请输入12个字以内的名称");
        }
        if (!$usable) {
            $this->error("使用范围不能为空");
        }
        if (strlen($usable) > 90) {
            $this->error("请输入30个字以内的使用范围");
        }
        if ($detail == '') {
            $this->error("礼包详情不能为空");
        }
        if ($usage == '') {
            $this->error("使用方法不能为空");
        }
        if ($exchange_start == '') {
            $this->error("使用开始时间不能为空");
        }
        if ($exchange_end == '') {
            $this->error("使用结束时间不能为空");
        }
        if ($exchange_start > $exchange_end) {
            $this->error("使用开始时间不能大于使用结束时间");
        }
        if ($exchange_start < $start_tm) {
            $this->error("使用开始时间不能在领取开始时间之前");
        }

        $apply_pkg = trim($_POST['apply_pkg']);
        $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->select();
        if (!$pkg_result) {
            $this->error("应用包名不存在");
        }
        $intro = trim($_POST['intro']);

        if (strlen($intro) > 150) {
            $this->error("简介不能大于50个汉字");
        }

        $been_num = $active_model->table("sendnum_number_$id")->where(array('active_id' => $id, 'status' => 0))->select();


        $pre_path = $_SERVER['DOCUMENT_ROOT'];

        foreach ($matches[1] as $key => $val) {
            $upload_model = D("Dev.Uploadfile");
            $files_name[$key] = str_replace('.', '', microtime(true)) . '_' . $upload_model->rand_code(8);
        }
        foreach ($matches[1] as $key => $val) {
            $files[$files_name[$key]] = '@' . $pre_path . $val;
        }
        $gift_category_result = $active_model->table('sendnum_active')->where(array('id' => $id))->find();

        if ($gift_category_result['gift_category'] == 2) {
            $bbs_score = $_POST['bbs_score'];
            $bbs_pic = $_FILES['bbs_pic'];
            if ($gift_category_result['bbs_conf_cnt'] != 0) {
                if ($bbs_score < 0 || !is_numeric($bbs_score)) {
                    $this->error("请填写正确的论坛领取所需金币");
                }
            }
            $path = date('Ym/d');
            if ($bbs_pic['size']) {
                $config['multi_config']['bbs_pic'] = array(
                    'savepath' => UPLOAD_PATH . '/img/' . $path,
                    'saveRule' => 'getmsec',
                );
                $list = $this->_uploadapk(0, $config);
                $bbs_pic_url = $list['image'][0]['url'];
                $data_active['bbs_pic'] = $bbs_pic_url;
            }

            $data_active['bbs_score'] = $bbs_score;
            $game_prefix = $_POST['game_prefix'];
            $data_active['game_prefix'] = $game_prefix;
			$same_package_result = $active_model -> table('olgame_active') -> where(array('gift_category' => 2,'apply_pkg' => $apply_pkg)) -> select();
			foreach($same_package_result as $key => $val){
				$prefix_result = $active_model -> table('sendnum_active') -> where(array('id' => $val['active_id'])) -> save(array('game_prefix' => $game_prefix));
			}
            $game_type = trim($_POST['game_type']);
            if ($game_type) {
                $game_category_result = $model->table('sj_category')->where(array('name' => $game_type, 'status' => 1))->select();
                $category_result = $model->table('sj_category')->where(array('parentid' => 2, 'status' => 1))->select();
                foreach ($category_result as $k => $v) {
                    $category_arr[] = $v['category_id'];
                }

                if (!in_array($game_category_result[0]['parentid'], $category_arr) && $game_category_result[0]['parentid'] != 2) {
                    $this->error("该游戏分类不存在");
                }
                $game_category = $game_category_result[0]['category_id'];
                $data_active['game_category'] = $game_category;
            }
        }

        $been_result = $active_model->table('olgame_active')->where(array('active_id' => $id))->find();
        $been_code = $active_model->table('sendnum_active')->where(array('id' => $id))->find();
        //数据写入
        $data_active['active_name'] = trim($active_name);
		$data_active['end_tm'] = $cut_tm;
		$data_active['start_tm'] = $start_tm;
        $data_active['gift_type'] = $_POST['gift_type'];
        $data_active['conf_cnt'] = $limit_num;
		$data_active['active_from'] = $active_from;
		$data_active['market_conf_cnt'] = $_POST['sc_num'];
		$data_active['game_conf_cnt'] = $_POST['game_num'];
		$data_active['sdk_conf_cnt'] = $_POST['sdk_num'];
		$data_active['bbs_conf_cnt'] = $_POST['bbs_num'];
		$data_active['weixin_conf_cnt'] = $_POST['weixin_num'];
        $where_active['_string'] = "id = {$id} and status != 0";
        // $have_result = $active_model->table('sendNum.sendnum_active')->where("active_name = '{$active_name}' and status != 0 and id != {$id}")->select();

        // if ($have_result) {
            // $this->error("对不起，标题不能相同");
        // }
        $log_result = $this->logcheck(array('id' => $id), 'sendnum_active', $data_active, $active_model);
        $active_result = $active_model->active_save($where_active, $data_active);
		$time = time();
        $data['be_limit_num'] = $limit_num;
        $data['be_active_name'] = $been_code['active_name'];
        $data['be_apply_pkg'] = $been_result['apply_pkg'];
        $data['be_usable'] = $been_result['usable'];
        $data['be_cut_tm'] = $been_code['end_tm'];
        $data['apply_pkg'] = $apply_pkg;
        $data['usable'] = $usable;
        $data['intro'] = htmlspecialchars($intro);
        $data['detail'] = $detail;
        $data['usage'] = $usage;
        $data['exchange_start'] = $exchange_start;
        $data['exchange_end'] = $exchange_end;
        $data['update_tm'] = $time;

        $where_content['_string'] = "active_id = {$id} and status != 0";
        $log_result_content = $this->logcheck(array('active_id' => $id), 'olgame_active', $data, $active_model);
        $content_result = $active_model->update_active_content($where_content, $data);
		//同步tmp表
		$map = array(
			'be_limit_num' => $limit_num,
			'gift_type' => $_POST['gift_type'],
			'start_tm' => $start_tm,
			'end_tm' => $cut_tm,
			'bbs_score' => $bbs_score,
			'game_prefix' => $game_prefix,
			'exchange_start' => $exchange_start,
			'exchange_end' => $exchange_end,
			'active_name' => $active_name,
		);
		$active_model -> table('sendnum_tmp') -> where(array('sendnumactive_id' => $id)) -> save($map);
		
        $static_file = $this->active_module_url;
        $static_file_true = $this->active_module_true_url;
        $js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
        preg_match("/href=\"(.*)\"/Ui", $module_content, $my_a);
        $package = $my_a[1];
        $module_contents = str_replace($package, "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)", $module_content);
        //生成静态页面
        ob_start();
        echo $this->header;
        echo $js_a;
        echo $module_contents;
        $temp = ob_get_contents();
        ob_end_clean();
        if (!file_exists($static_file)) {
            @mkdir(rtrim($static_file, '/'), 0777, true) or die("创建目录失败");
        }
        $fp = fopen($static_file . "/market_gift_preview_{$id}.html", 'w');
        $create_result = fwrite($fp, $temp);
        fclose($fp);
        if (!file_exists($static_file_true)) {
            @mkdir(rtrim($static_file_true, '/'), 0777, true) or die("创建目录失败");
        }
        if (!$create_result) {
            $this->error("写入文件错误");
        }
        $from = $been_code['status'];

        if ($active_result || $content_result) {

			//
      $fileicon = $model->table('sj_soft_file')->where(array('apk_name' => $apply_pkg, 'package_status'=>1))->field('iconurl_125')->order('id desc')->find();
			$send_data = array(
				'serviceId' => $been_code['active_from'],       //业务线id  ,礼包的使用业务线
				'giftName' => $active_name,//"奖品名称"
				'giftType' => '2',//奖品类型：1:积分  2：礼包  3：话费
				'giftSoftName' => $pkg_result[0]['softname'],//软件名称
				'giftSoftPname' => $apply_pkg,//软件包名
				'giftTotal' => $sur_num,//礼包总数
				'giftEvSum' => $limit_num,//明日发放限制数量
				'prImage' => 'http://img3.anzhi.com'.$fileicon['iconurl_125'],//奖品图片
				'prShelvesDate' => $start_tm,   //有效期--开始时间--使用时间
				'prUnderDate' => $cut_tm ,   //有效期--结束时间
				'prSdate' =>  $exchange_start,  //上架时间
				'prEdate' =>  $exchange_end, //下架时间			
				'prSortno'  => 1,//排序号
				'remark' => $detail, //奖品说明
				'giftUse' => '2', //奖品用途   0:抽奖  1:兑换   2:领取
				'useRange' => $usable,//使用范围
				'useWay' => $usage ,//使用方法
				'createTime' => $active['add_tm'],
				'updateTime' => $time,
				'delStatus' => 0 ,//0:整除  1:删除。
				'ref_id' => $id,
				'fromServiceId' => '007',//数据来源业务线
				'oper_type' => 1,//0:新增、1：修改、2：删除
			);
			$active_model -> send_gift_work($send_data);
            $push_data = array(
                'active_name' => $active_name,
                'intro' => $intro,
                'active_id' =>$id,
                'start_tm' => $start_tm,
                'cut_tm' => $cut_tm,
                'exchange_start' => $exchange_start,
                'exchange_end' => $exchange_end,
                'icon_url' => 'http://img3.anzhi.com'.$fileicon['iconurl_125']
            );
            push_gift_msg($push_data);

            $this->writelog("网游_市场礼包_已编辑id为{$id}的市场礼包" . $log_result . $log_result_content,'sendnum_active',"{$id}", __ACTION__ ,"","edit");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Onlinegame/active_gift_list1/gift_category/{$gift_category_result[0]['gift_category']}");
            $this->success("编辑成功");
        } else {
            $this->error("编辑失败");
        }
    }
	
	//计算每个平台已发出数量
	function get_used_num($id){
		$table = 'sendnum_number_'.$id;
		$active_model = D('sendNum.sendNum');
		$where = array(
			'status' => array('in',array(1,3))
		);
		$used_num = $active_model->table($table)->where($where)->field('from')->select();
		$from_num = array();
		foreach($used_num as $k=>$v){
			if(!isset($from_num[$v['from']])) $from_num[$v['from']] = 0;
			$from_num[$v['from']] ++;
		}
		
		return $from_num;
	}
    //迭代7市场礼包编辑显示
    function p_market_edit_show() {
        $id = $_GET['id'];
        $active_model = D('sendNum.sendNum');
        $model = new Model();
        $p = $_GET['p'];
        $lr = $_GET['lr'];
        $this->assign('p', $_GET['p']);
        $this->assign('lr', $_GET['lr']);
        $result = $active_model->table('olgame_active')->where("active_id = {$id} and status != 0")->select();
        //$active_result = $active_model -> table('sendnum_active') -> where("id => {$id} and status != 0") -> select();
        $sql = "select a.*,b.id as gift_id,b.gift_type from sendNum.sendnum_active as a left join sendNum.sendnum_gift_type as b on a.gift_type = b.id where a.`status` != 0 and a.id = {$id}";

        $active_result = $active_model->query($sql);
		//计算每个平台已发出数量
		$activ_from_num = $this->get_used_num($id);
		//var_dump($activ_from_num);
        foreach ($result as $key => $val) {
            $active_where['id'] = $val['active_id'];
            //	$active_result = $active_model -> table('sendnum_active') -> where($active_where) -> select();
            $val['cut_tm'] = $active_result[0]['end_tm'];
            $val['start_tm'] = $active_result[0]['start_tm'];
            $val['all_num'] = $active_result[0]['num_cnt'];
            $val['used_cnt'] = $active_result[0]['used_cnt'];
            $else_result = $active_model->table('sendnum_number')->where(array('active_id' => $val['active_id'], 'status' => 1))->select();
            $val['surplus_num'] = $active_result[0]['num_cnt'] - $active_result[0]['used_cnt'];
            $val['limit_num'] = $active_result[0]['conf_cnt'];
            $val['active_name'] = $active_result[0]['active_name'];
            $val['sim_content'] = $result[0]['sim_content'];
            $val['sel_type'] = $active_result[0]['gift_id'];
            if ($active_result[0]['market_conf_cnt'] == 0) {
                $val['market_conf_cnt'] == '';
            } else {
                $val['market_conf_cnt'] = $active_result[0]['market_conf_cnt'];
            }

            if ($active_result[0]['game_conf_cnt'] == 0) {
                $val['game_conf_cnt'] == '';
            } else {
                $val['game_conf_cnt'] = $active_result[0]['game_conf_cnt'];
            }

            if ($active_result[0]['sdk_conf_cnt'] == 0) {
                $val['sdk_conf_cnt'] == '';
            } else {
                $val['sdk_conf_cnt'] = $active_result[0]['sdk_conf_cnt'];
            }

            if ($active_result[0]['bbs_conf_cnt'] == 0) {
                $val['bbs_conf_cnt'] == '';
            } else {
                $val['bbs_conf_cnt'] = $active_result[0]['bbs_conf_cnt'];
            }
			if ($active_result[0]['weixin_conf_cnt'] == 0) {
                $val['weixin_conf_cnt'] == '';
            } else {
                $val['weixin_conf_cnt'] = $active_result[0]['weixin_conf_cnt'];
            }
			
            $val['gift_category'] = $active_result[0]['gift_category'];
            $val['soft_name'] = $active_result[0]['soft_name'];
            $val['bbs_score'] = $active_result[0]['bbs_score'];
            $val['game_prefix'] = $active_result[0]['game_prefix'];
            $val['bbs_pic'] = $active_result[0]['bbs_pic'];
            $val['active_from'] = $active_result[0]['active_from'];
			$val['active_from_num'] = $activ_from_num;
            $game_category = $active_result[0]['game_category'];
            $game_category_result = $model->table('sj_category')->where(array('category_id' => $game_category))->find();
            $val['game_category'] = $game_category_result['name'];
            $result[$key] = $val;
        }
        $result_one = $active_model->table("sendNum.sendnum_gift_type")->where(array('gift_category' => $active_result[0]['gift_category'], 'status' => 1))->select();
        $this->assign('result_one', $result_one);
        $this->assign("result", $result);
        $this->display();
    }

    function p_edit_rank($flash) {
        $model = D('sendNum.sendNum');

		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$result = $model -> table('olgame_active') -> where(array('id' => $id)) -> save(array('rank' => $rank));
		if($result){
			$this -> writelog('更新了安卓游戏_礼包设置id为'.$id.'的排序为'."{$rank}",'olgame_active',"{$id}", __ACTION__ ,"","edit");
			echo json_encode($param);
		}

    }

    //迭代7活动礼包_待审核
    public function verify_gift_list() {
        $where = "a.status = 2";
        if ($_GET['id'] && !empty($_GET['id'])) {
            $this->assign('id', $_GET['id']);
            $where .= ' and a.id = "' . $_GET['id'] . '"';
        }
        if ($_GET['softname'] && !empty($_GET['softname'])) {
            $this->assign('softname', $_GET['softname']);
            $model = new Model();
			$subQuery = $model->table('sj_soft')->field('package')->where('softname like "%' . $_GET['softname'] . '%"')->buildSql(); 
            $where .= " and a.apply_pkg in ({$subQuery})";
        }
        if ($_GET['apply_pkg'] && !empty($_GET['apply_pkg'])) {
            $this->assign('apply_pkg', $_GET['apply_pkg']);
            $where .= ' and a.apply_pkg = "' . $_GET['apply_pkg'] . '"';
        }
        if ($_GET['start_tm'] && !empty($_GET['start_tm'])) {
            $this->assign('start_tm', $_GET['start_tm']);
            $start_tm = strtotime($_GET['start_tm']);
            $where .= ' and a.start_tm >= "' . $start_tm . '"';
        }
        if ($_GET['end_tm'] && !empty($_GET['end_tm'])) {
            $this->assign('end_tm', $_GET['end_tm']);
            $end_tm = strtotime($_GET['end_tm']);
            $where .= ' and a.end_tm <= "' . $end_tm . '"';
        }
        if ($_GET['substart_tm'] && !empty($_GET['substart_tm'])) {
            $this->assign('substart_tm', $_GET['substart_tm']);
            $add_tm = strtotime($_GET['substart_tm']);
            $where .= ' and a.add_tm >= "' . $add_tm . '"';
        }
        if ($_GET['subend_tm'] && !empty($_GET['subend_tm'])) {
            $this->assign('subend_tm', $_GET['subend_tm']);
            $add_tm = strtotime($_GET['subend_tm']);
            $where .= ' and a.add_tm <= "' . $add_tm . '"';
        }
        if ($_GET['type'] && !empty($_GET['type'])) {
            $this->assign('gift_types', $_GET['type']);
            $where .= ' and a.gift_type = "' . $_GET['type'] . '"';
        }

        if ($_GET['gift_category'] && !empty($_GET['gift_category'])) {
            $this->assign('gift_category', $_GET['gift_category']);
			$where .= ' and a.gift_category = "' . $_GET['gift_category'] . '"';
        } 		
        $allcategory = $this->get_catgory_str();
        foreach ($allcategory as $key => $val) {
            $tmpcate = explode(',', $val);
            foreach ($tmpcate as $tmpkey => $tmpval) {
                $tmpcate[$tmpkey] = "," . $tmpval . ",";
            }
            $allcategory[$key] = implode('","', $tmpcate);
        }

        $active_model = D('sendNum.sendNum');

        $qu_array = array(
            "1" => "安智论坛", 
            "2" => "安卓游戏",
            "4" => "安智市场",            
            "8" => "SDK",
			"16"=> "微信平台"
        );

        $count = $active_model->table('sendnum_tmp a')->where($where)->count();
//        echo $active_model->getLastSql();
        $result = $active_model->table('sendnum_tmp a')->join("sendnum_gift_type b on a.gift_type = b.id")->where($where)->order('add_tm desc')->field('a.*,b.gift_type')->select();

        $sdk_model = M('');
        $type = array('1' => '应用', '2' => '游戏', '3' => '电子书');
        $package = array();
        foreach ($result as $v) {
            if ($v['apply_pkg'])
                $package[] = $v['apply_pkg'];
        }
        if ($package) {
            $where = array(
                'package' => array('in', $package),
                'status' => 1,
            );
            $soft_info = get_table_data($where, "sj_soft", "package", "package,softid,softname,category_id,total_downloaded");
            $where = array(
                'apk_name' => array('in', $package),
                'package_status' => array('exp', "> 0"),
            );
            $file_icon_arr = get_table_data($where, "sj_soft_file", "apk_name", "id,apk_name,iconurl");
        }
        foreach ($result as $key => $val) {
            $cate = explode(',', $val['category_id']);
            $result[$key]['category_id'] = $cate[1];
            foreach ($allcategory as $allkey => $allval) {
                $catekey = strpos($allval, $result[$key]['category_id']);
                if ($catekey) {
                    $val['type'] = $type[$allkey];
                }
            }

            $where = array(
                'package' => $val['apply_pkg']
            );
            $sdk = $sdk_model->table('sj_soft_whitelist')->where($where)->field('is_sdk')->find();
            //是否介入sdk
            if ($sdk) {
                $val['sdk_status'] = $sdk['is_sdk'];
            }
			$str = '';
			foreach($qu_array as $k => $v){
				if(($val['active_from'] & $k) == $k) $str .= $v.",";
			}
            $val['active_from'] = substr($str,0,-1);
            $val['softname'] = $soft_info[$val['apply_pkg']]['softname'];
            $val['total_downloaded'] = $soft_info[$val['apply_pkg']]['total_downloaded'];
            $val['softid'] = $soft_info[$val['apply_pkg']]['softid'];
            $val['iconurl'] = $file_icon_arr[$val['apply_pkg']]['iconurl'];
            $result[$key] = $val;
        }
        import("@.ORG.Page");
        $Page = new Page($count, 20, $limit);
        $this->assign('img_host', IMG_URL);
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('count', $count);
        $this->assign('page', $show);
        $this->assign("result", $result);
        $this->assign("from", 'verify');
        $gift_type = $active_model->table('sendnum_gift_type')->where(array('status' => 1))->select();
		$gift_type_1 =  array();
		$gift_type_2 =  array();
		foreach( $gift_type as $v){
			if($v['gift_category'] == 1) $gift_type_1[] = $v; 
			if($v['gift_category'] == 2) $gift_type_2[] = $v; 
		}		
        $this->assign('gift_type', $gift_type);
        $this->assign('gift_type_1', $gift_type_1);
        $this->assign('gift_type_2', $gift_type_2);
        $this->display();
    }

    //迭代7活动礼包_已过期
    public function active_gift_list_last1() {
        $where = "a.status = 1 and end_tm < '" . time() . "'";
        if ($_GET['id'] && !empty($_GET['id'])) {
            $this->assign('id', $_GET['id']);
            $where .= ' and a.id = "' . $_GET['id'] . '"';
        }
        if ($_GET['softname'] && !empty($_GET['softname'])) {
            $this->assign('softname', $_GET['softname']);
            $model = new Model();
            $subQuery = $model->table('sj_soft')->field('package')->where('softname like "%' . $_GET['softname'] . '%"')->select();
            $str = '';
            foreach ($subQuery as $v) {
                if ($v['package'])
                    $str .= "'" . $v['package'] . "',";
            }
            if ($str)
                $str = substr($str, 0, -1);
            $where .= " and a.apply_pkg in ({$str})";
        }
        if ($_GET['apply_pkg'] && !empty($_GET['apply_pkg'])) {
            $this->assign('apply_pkg', $_GET['apply_pkg']);
            $where .= ' and a.apply_pkg = "' . $_GET['apply_pkg'] . '"';
        }
        if ($_GET['start_tm'] && !empty($_GET['start_tm'])) {
            $this->assign('start_tm', $_GET['start_tm']);
            $start_tm = strtotime($_GET['start_tm']);
            $where .= ' and a.start_tm >= "' . $start_tm . '"';
        }
        if ($_GET['end_tm'] && !empty($_GET['end_tm'])) {
            $this->assign('end_tm', $_GET['end_tm']);
            $end_tm = strtotime($_GET['end_tm']);
            $where .= ' and a.end_tm <= "' . $end_tm . '"';
        }
        if ($_GET['substart_tm'] && !empty($_GET['substart_tm'])) {
            $this->assign('substart_tm', $_GET['substart_tm']);
            $add_tm = strtotime($_GET['substart_tm']);
            $where .= ' and a.add_tm >= "' . $add_tm . '"';
        }
        if ($_GET['subend_tm'] && !empty($_GET['subend_tm'])) {
            $this->assign('subend_tm', $_GET['subend_tm']);
            $add_tm = strtotime($_GET['subend_tm']);
            $where .= ' and a.add_tm <= "' . $add_tm . '"';
        }
        if ($_GET['type'] && !empty($_GET['type'])) {
            $this->assign('gift_types', $_GET['type']);
            $where .= ' and a.gift_type = "' .  $_GET['type'] . '"';
        }
		if ($_GET['gift_category'] && !empty($_GET['gift_category'])) {
            $this->assign('gift_category', $_GET['gift_category']);
			$where .= ' and a.gift_category = "' . $_GET['gift_category'] . '"';
        } 	
        $active_model = D('sendNum.sendNum');
        $qu_array = array(
            "1" => "安智论坛", 
            "2" => "安卓游戏",
            "4" => "安智市场",            
            "8" => "SDK",
            "16" => "微信平台",
        );
        $where .= " and a.active_from not in('32','64','128')";
        $count = $active_model->table('sendnum_tmp a')->where($where)->count();
        import("@.ORG.Page");
        $Page = new Page($count, 20);
        $result = $active_model->table('sendnum_tmp a')->join("sendnum_gift_type b on a.gift_type = b.id")->field('a.*,b.gift_type')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $active_model->getLastSql();

        $allcategory = $this->get_catgory_str();
        foreach ($allcategory as $key => $val) {
            $tmpcate = explode(',', $val);
            foreach ($tmpcate as $tmpkey => $tmpval) {
                $tmpcate[$tmpkey] = "," . $tmpval . ",";
            }
            $allcategory[$key] = implode('","', $tmpcate);
        }

        $type = array('1' => '应用', '2' => '游戏', '3' => '电子书');
        $package = array();
        $active_arr = array();
        foreach ($result as $v) {
            if ($v['apply_pkg'])
                $package[] = $v['apply_pkg'];
                $active_arr[] = $v['active_name'];
        }
 
       // $active_data = get_table_data(array('active_name'=>array('in',$active_arr)), "sendNum.sendnum_active", "package,num_cnt,used_cnt");
        $active_data = $active_model->table('sendnum_active')->where(array('active_name'=>array('in',$active_arr)))->field('active_name,num_cnt,used_cnt')->group('active_name')->select();

        $num_arr = array();
        foreach($active_data as $key=>$val){
            $num_arr[$val['active_name']]['num_cnt'] = $val['num_cnt'];
            $num_arr[$val['active_name']]['used_cnt'] = $val['used_cnt'];
        }

        if ($package) {
            $where = array(
                'package' => array('in', $package),
                'status' => 1,
            );
            $soft_info = get_table_data($where, "sj_soft", "package", "package,softid,softname,category_id,total_downloaded");
            $where = array(
                'apk_name' => array('in', $package),
                'package_status' => array('exp', "> 0"),
            );
            $file_icon_arr = get_table_data($where, "sj_soft_file", "apk_name", "id,apk_name,iconurl");
        }

        foreach ($result as $key => $val) {
            $cate = explode(',', $soft_info[$val['apply_pkg']]['category_id']);
            $result[$key]['category_id'] = $cate[1];
            $val['surplus_num'] = $num_arr[$val['active_name']]['num_cnt']-$num_arr[$val['active_name']]['used_cnt'];
            foreach ($allcategory as $allkey => $allval) {
                $catekey = strpos($allval, $result[$key]['category_id']);
                if ($catekey) {
                    $val['type'] = $type[$allkey];
                }
            }
            //是否介入sdk

            $where = array(
                'package' => $val['apply_pkg']
            );
            $sdk = $active_model->table('newgomarket.sj_soft_whitelist')->where($where)->field('id')->find();
            //是否介入sdk
            if ($sdk) {
                $val['sdk_status'] = 1;
            }

			$str = '';
			foreach($qu_array as $k => $v){
				if(($val['active_from'] & $k) == $k) $str .= $v.",";
			}
            $val['active_from'] = substr($str,0,-1);
			
            $val['softname'] = $soft_info[$val['apply_pkg']]['softname'];
            $val['total_downloaded'] = $soft_info[$val['apply_pkg']]['total_downloaded'];
            $val['softid'] = $soft_info[$val['apply_pkg']]['softid'];
            $val['iconurl'] = $file_icon_arr[$val['apply_pkg']]['iconurl'];
            $result[$key] = $val;
        }

        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('count', $count);
        $this->assign('img_host', IMG_URL);
        $this->assign('page', $show);
        $this->assign("result", $result);
        $gift_type = $active_model->table('sendnum_gift_type')->where(array('status' => 1))->select();
		$gift_type_1 =  array();
		$gift_type_2 =  array();
		foreach( $gift_type as $v){
			if($v['gift_category'] == 1) $gift_type_1[] = $v; 
			if($v['gift_category'] == 2) $gift_type_2[] = $v; 
		}		
        $this->assign('gift_type', $gift_type);
        $this->assign('gift_type_1', $gift_type_1);
        $this->assign('gift_type_2', $gift_type_2);
        $this->assign("from", 'last');
        $this->display();
    }

    //迭代7删除过期礼包
    public function p_del_lastgift() {
        $id = $_GET['id'];
        if (!empty($id)) {
			$time  = time();
            $active_model = D('sendNum.sendNum');
            $where = 'id in (' . $id . ')';
            $tmp_list = $active_model->table('sendnum_tmp')->where($where)->field('sendnumactive_id')->field('sendnumactive_id,apply_pkg')->select();
            $active_id = array();
            $pkg = array();
            foreach ($tmp_list as $key => $val) {
				if(!$val['sendnumactive_id']) continue;
				$active_id[] = $val['sendnumactive_id']; 
				$pkg[] = $val['apply_pkg']; 
            }
            $where = array(
				'id' => array('in',$active_id)
			);
            $been_result = $active_model->table('sendnum_active')->where($where)->field('id,active_name,status,active_from')->select();
			$data = array(
				'status' => 0,
				'update_tm' => $time,
			);
            $result_active = $active_model->table('sendnum_active')->where($where)->save($data);
            $whereid = array(
                'active_id' => array('in',$active_id)
            );
			$data = array(
				'status' => 0,
				'rank' => 0,
				'update_tm' => $time,
			);
            $result_content = $active_model->table('olgame_active')->where($whereid)->save($data);
			$data = array(
				'status' => 5,
				'update_tm' => $time,
			);
            $result_tmp = $active_model->table('sendnum_tmp')->where('id = "' . $id . '"')->save($data);
            if ($result_active && $result_tmp) {		
				$where = array('active_id'=>array('in',$active_id));
				$change_result  = get_table_data($where,"sendNum.olgame_active","active_id","*");		
				$where = array('package'=>array('in',$pkg),'status'=>1,'hide'=>1);
				$soft_arr  = get_table_data($where,"sj_soft","package","package,softname");
				
				$where = array('apk_name'=>array('in',$pkg),'package_status'=>1);
				$fileicon  = get_table_data($where,"sj_soft_file","apk_name","apk_name,iconurl");

				foreach ($been_result as $key => $val) {
					$where = array(
						'status' => 1,
						'rank' => array('exp'," > {$change_result[$val['id']]['rank']} and rank >0")
					);
					$data = array(
						'rank' => array("exp","`rank`-1"),
					);
					$result = $active_model->update_active_content($where, $data);					
					//
					$send_data = array(
						'serviceId' => $val['active_from'],       //业务线id  ,礼包的使用业务线
						'giftName' => $val['active_name'],//"奖品名称"
						'giftType' => '2',//奖品类型：1:积分  2：礼包  3：话费
						'giftSoftName' => $soft_arr[$change_result[$val['id']]['apply_pkg']]['softname'],//软件名称
						'giftSoftPname' => $change_result[$val['id']]['apply_pkg'],//软件包名
						'giftTotal' => $val['num_cnt'],//礼包总数
						'giftEvSum' => $change_result[$val['id']]['be_limit_num'],//每日发放限制数量
						'prImage' => 'http://img3.anzhi.com'.$fileicon[$change_result[$val['id']]['apply_pkg']]['iconurl'],//奖品图片
						'prShelvesDate' => $val['start_tm'],   //有效期--开始时间--使用时间
						'prUnderDate' => $val['end_tm'] ,   //有效期--结束时间
						'prSdate' => $change_result[$val['id']]['exchange_start'] ,  //上架时间
						'prEdate' =>  $change_result[$val['id']]['exchange_end'], //下架时间			
						'prSortno'  => $change_result[$val['id']]['rank'],//排序号
						'remark' => $change_result[$val['id']]['detail'], //奖品说明
						'giftUse' => '2', //奖品用途   0:抽奖  1:兑换   2:领取
						'useRange' => $change_result[$val['id']]['be_usable'],//使用范围
						'useWay' => $change_result[$val['id']]['usage'] ,//使用方法
						'createTime' => $change_result[$val['id']]['create_tm'],
						'updateTime' => $change_result[$val['id']]['update_tm'],
						'delStatus' => 0 ,//0:整除  1:删除。
						'ref_id' => $val['id'],
						'fromServiceId' => '007',//数据来源业务线
						'oper_type' => 0,//0:新增、1：修改、2：删除
					);
					$active_model -> send_gift_work($send_data);
					update_soft_status(array('gift_status' => 0), $change_result[$val['id']]['apply_pkg']);
				}
                $idstr = implode(',',$active_id);
                $this->writelog("网游_礼包设置_已删除id为{$idstr}的礼包",'olgame_active',"{$idstr}", __ACTION__ ,"","del");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Onlinegame/active_gift_list_last1");
                $this->success("删除成功");
            } else {
                $this->success("删除失败");
            }
        }
    }

    //迭代7撤销驳回礼包
    public function p_reset_rejectgift() {
        $id = $_GET['id'];
        if (!empty($id)) {
            $where = 'id in(' . $id . ')';
            $active_model = D('sendNum.sendNum');
            $data['update_tm'] = time();
            $data['status'] = 2;
            $res = $active_model->table('sendNum.sendnum_tmp')->where($where)->save($data);
            if ($res) {
                $this->success("撤销成功");
            } else {
                $this->error("撤销失败");
            }
        }
    }

    //迭代7活动礼包_已驳回
    public function reject_gift_list() {
        $where = "a.status = 4 ";
        if ($_GET['id'] && !empty($_GET['id'])) {
            $this->assign('id', $_GET['id']);
            $where .= ' and a.id = "' . $_GET['id'] . '"';
        }
        if ($_GET['softname'] && !empty($_GET['softname'])) {
            $this->assign('softname', $_GET['softname']);
            $model = new Model();
            $subQuery = $model->table('sj_soft')->field('package')->where('softname like "%' . $_GET['softname'] . '%"')->select();
            $str = '';
            foreach ($subQuery as $v) {
                if ($v['package'])
                    $str .= "'" . $v['package'] . "',";
            }
            if ($str)
                $str = substr($str, 0, -1);
            $where .= " and a.apply_pkg in ({$str})";
        }
        if ($_GET['apply_pkg'] && !empty($_GET['apply_pkg'])) {
            $this->assign('apply_pkg', $_GET['apply_pkg']);
            $where .= ' and a.apply_pkg = "' . $_GET['apply_pkg'] . '"';
        }
        if ($_GET['start_tm'] && !empty($_GET['start_tm'])) {
            $this->assign('start_tm', $_GET['start_tm']);
            $start_tm = strtotime($_GET['start_tm']);
            $where .= ' and a.update_tm >= "' . $start_tm . '"';
        }
        if ($_GET['end_tm'] && !empty($_GET['end_tm'])) {
            $this->assign('end_tm', $_GET['end_tm']);
            $end_tm = strtotime($_GET['end_tm']);
            $where .= ' and a.update_tm <= "' . $end_tm . '"';
        }
        if ($_GET['type'] && !empty($_GET['type'])) {
            $this->assign('gift_types', $_GET['type']);
            $where .= ' and a.gift_type = "' . $_GET['type'] . '"';
        }
        if ($_GET['gift_category'] && !empty($_GET['gift_category'])) {
            $this->assign('gift_category', $_GET['gift_category']);
			$where .= ' and a.gift_category = "' . $_GET['gift_category'] . '"';
        } 			
        $active_model = D('sendNum.sendNum');
        $where .= " and a.active_from not in('32','64','128')";
        $count = $active_model->table('sendnum_tmp a')->where($where)->count();
//        echo $active_model->getLastSql();
        $result = $active_model->table('sendnum_tmp a')->join("sendnum_gift_type b on a.gift_type = b.id")->field('a.*,b.gift_type')->where($where)->group('a.active_name')->select();
//        echo $active_model->getLastSql();
        $allcategory = $this->get_catgory_str();
        foreach ($allcategory as $key => $val) {
            $tmpcate = explode(',', $val);
            foreach ($tmpcate as $tmpkey => $tmpval) {
                $tmpcate[$tmpkey] = "," . $tmpval . ",";
            }
            $allcategory[$key] = implode('","', $tmpcate);
        }

        $type = array('1' => '应用', '2' => '游戏', '3' => '电子书');
        $package = array();
        foreach ($result as $v) {
            if ($v['apply_pkg'])
                $package[] = $v['apply_pkg'];
        }
        if ($package) {
            $where = array(
                'package' => array('in', $package),
                'status' => 1,
            );
            $soft_info = get_table_data($where, "sj_soft", "package", "package,softid,softname,category_id,total_downloaded");
            $where = array(
                'apk_name' => array('in', $package),
                'package_status' => array('exp', "> 0"),
            );
            $file_icon_arr = get_table_data($where, "sj_soft_file", "apk_name", "id,apk_name,iconurl");
        }
		
        foreach ($result as $key => $val) {
            $cate = explode(',', $soft_info[$val['apply_pkg']]['category_id']);
            $result[$key]['category_id'] = $cate[1];
            foreach ($allcategory as $allkey => $allval) {
                $catekey = strpos($allval, $result[$key]['category_id']);
                if ($catekey) {
                    $val['type'] = $type[$allkey];
                }
            }
            //是否介入sdk

            $where = array(
                'package' => $val['apply_pkg']
            );
            $sdk = $active_model->table('newgomarket.sj_soft_whitelist')->where($where)->field('id')->find();
            //是否介入sdk
            if ($sdk) {
                $val['sdk_status'] = 1;
            }
			$str = '';
			foreach($qu_array as $k => $v){
				if(($val['active_from'] & $k) == $k) $str .= $v.",";
			}
            $val['active_from'] = substr($str,0,-1);
            $val['softname'] = $soft_info[$val['apply_pkg']]['softname'];
            $val['total_downloaded'] = $soft_info[$val['apply_pkg']]['total_downloaded'];
            $val['softid'] = $soft_info[$val['apply_pkg']]['softid'];
            $val['iconurl'] = $file_icon_arr[$val['apply_pkg']]['iconurl'];
            $result[$key] = $val;
        }

        import("@.ORG.Page");
        $Page = new Page($count, 20, $limit);
        $this->assign('count', $count);
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('img_host', IMG_URL);
        $this->assign('page', $show);
        $this->assign("result", $result);
        $gift_type = $active_model->table('sendnum_gift_type')->where(array('status' => 1))->select();
		$gift_type_1 =  array();
		$gift_type_2 =  array();
		foreach( $gift_type as $v){
			if($v['gift_category'] == 1) $gift_type_1[] = $v; 
			if($v['gift_category'] == 2) $gift_type_2[] = $v; 
		}		
        $this->assign('gift_type', $gift_type);
        $this->assign('gift_type_1', $gift_type_1);
        $this->assign('gift_type_2', $gift_type_2);
        $this->assign("from", 'reject');
        $this->display();
    }

    //迭代7活动礼包_已删除
    public function del_gift_list() {
        $where = "a.status = 5 ";
        if ($_GET['id'] && !empty($_GET['id'])) {
            $this->assign('id', $_GET['id']);
            $where .= ' and a.id = "' . $_GET['id'] . '"';
        }
        if ($_GET['softname'] && !empty($_GET['softname'])) {
            $this->assign('softname', $_GET['softname']);
            $model = new Model();
            $subQuery = $model->table('sj_soft')->field('package')->where('softname like "%' . $_GET['softname'] . '%"')->select();
            $str = '';
            foreach ($subQuery as $v) {
                if ($v['package'])
                    $str .= "'" . $v['package'] . "',";
            }
            if ($str)
                $str = substr($str, 0, -1);
            $where .= " and a.apply_pkg in ({$str})";
        }
        if ($_GET['apply_pkg'] && !empty($_GET['apply_pkg'])) {
            $this->assign('apply_pkg', $_GET['apply_pkg']);
            $where .= ' and a.apply_pkg = "' . $_GET['apply_pkg'] . '"';
        }
        if ($_GET['start_tm'] && !empty($_GET['start_tm'])) {
            $this->assign('start_tm', $_GET['start_tm']);
            $start_tm = strtotime($_GET['start_tm']);
            $where .= ' and a.update_tm >= "' . $start_tm . '"';
        }
        if ($_GET['end_tm'] && !empty($_GET['end_tm'])) {
            $this->assign('end_tm', $_GET['end_tm']);
            $end_tm = strtotime($_GET['end_tm']);
            $where .= ' and a.update_tm <= "' . $end_tm . '"';
        }
        if ($_GET['type'] && !empty($_GET['type'])) {
            $this->assign('gift_types', $_GET['type']);
            $where .= ' and a.gift_type = "' . $_GET['type'] . '"';
        }
        if ($_GET['gift_category'] && !empty($_GET['gift_category'])) {
            $this->assign('gift_category', $_GET['gift_category']);
			$where .= ' and a.gift_category = "' . $_GET['gift_category'] . '"';
        } 	
        $active_model = D('sendNum.sendNum');
        $qu_array = array(
            "1" => "安智论坛", 
            "2" => "安卓游戏",
            "4" => "安智市场",            
            "8" => "SDK",
			"16"=> "微信平台"
        );
        $where .= " and a.active_from not in('32','64','128')";
        $count = $active_model->table('sendnum_tmp a')->where($where)->count();
//        echo $active_model->getLastSql();
        $result = $active_model->table('sendnum_tmp a')->join("sendnum_gift_type b on a.gift_type = b.id")->field('a.*,b.gift_type')->where($where)->group('a.active_name')->order('update_tm desc')->select();

//        echo $active_model->getLastSql();
        $sdk_model = M('');
        $allcategory = $this->get_catgory_str();
        foreach ($allcategory as $key => $val) {
            $tmpcate = explode(',', $val);
            foreach ($tmpcate as $tmpkey => $tmpval) {
                $tmpcate[$tmpkey] = "," . $tmpval . ",";
            }
            $allcategory[$key] = implode('","', $tmpcate);
        }

        $type = array('1' => '应用', '2' => '游戏', '3' => '电子书');
        $package = array();
        foreach ($result as $v) {
            if ($v['apply_pkg'])
                $package[] = $v['apply_pkg'];
        }
        if ($package) {
            $where = array(
                'package' => array('in', $package),
                'status' => 1,
            );
            $soft_info = get_table_data($where, "sj_soft", "package", "package,softid,softname,category_id,total_downloaded");
            $where = array(
                'apk_name' => array('in', $package),
                'package_status' => array('exp', "> 0"),
            );
            $file_icon_arr = get_table_data($where, "sj_soft_file", "apk_name", "id,apk_name,iconurl");
            $where = array(
                'package' => array('in', $package),
                'status' => 2,
            );
            $sdk = get_table_data($where, "sj_soft_tmp", "package", "package,sdk_status");
        }
        foreach ($result as $key => $val) {
            $cate = explode(',', $soft_info[$val['apply_pkg']]['category_id']);
            $result[$key]['category_id'] = $cate[1];
            foreach ($allcategory as $allkey => $allval) {
                $catekey = strpos($allval, $result[$key]['category_id']);
                if ($catekey) {
                    $val['type'] = $type[$allkey];
                }
            }
            //是否介入sdk
            $val['sdk_status'] = $sdk[$val['apply_pkg']]['sdk_status'];
			$str = '';
			foreach($qu_array as $k => $v){
				if(($val['active_from'] & $k) == $k) $str .= $v.",";
			}
            $val['active_from'] = substr($str,0,-1);
            $val['softname'] = $soft_info[$val['apply_pkg']]['softname'];
            $val['total_downloaded'] = $soft_info[$val['apply_pkg']]['total_downloaded'];
            $val['softid'] = $soft_info[$val['apply_pkg']]['softid'];
            $val['iconurl'] = $file_icon_arr[$val['apply_pkg']]['iconurl'];
            $result[$key] = $val;
        }

        import("@.ORG.Page");
        $Page = new Page($count, 20, $limit);
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('img_host', IMG_URL);
        $this->assign('count', $count);
        $this->assign('page', $show);
        $this->assign("result", $result);
        $gift_type = $active_model->table('sendnum_gift_type')->where(array('status' => 1))->select();
		$gift_type_1 =  array();
		$gift_type_2 =  array();
		foreach( $gift_type as $v){
			if($v['gift_category'] == 1) $gift_type_1[] = $v; 
			if($v['gift_category'] == 2) $gift_type_2[] = $v; 
		}		
        $this->assign('gift_type', $gift_type);
        $this->assign('gift_type_1', $gift_type_1);
        $this->assign('gift_type_2', $gift_type_2);
        $this->assign("from", 'del');
        $this->display();
    }

    //活动礼包_已过期
    function active_gift_list_last() {
        $active_model = D('sendNum.sendNum');

        $no_where['_string'] = "status = 1 and active_from = 2 and end_tm <" . time() . "";
        $no_result = $active_model->table('sendnum_active')->where($no_where)->select();
        foreach ($no_result as $key => $val) {
            $no_id_str .= $val['id'] . ',';
        }
        import("@.ORG.Page");
        $no_id = substr($no_id_str, 0, -1);
        $where['_string'] = "status = 1 and active_id in ({$no_id})";
        $count = $active_model->table('olgame_active')->where($where)->order('create_tm DESC')->count();
        $Page = new Page($count, 20, $limit);
        $result = $active_model->table('olgame_active')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('rank')->select();
        if (!$_GET['p']) {
            $_GET['p'] = 1;
        }
        foreach ($result as $key => $val) {
            $active_where['id'] = $val['active_id'];
            $active_result = $active_model->table('sendnum_active')->where($active_where)->select();
            $val['num'] = $key + 1 + (($_GET['p'] - 1) * $_GET['lr']);
            $val['cut_tm'] = $active_result[0]['end_tm'];
            $val['start_tm'] = $active_result[0]['start_tm'];
            $val['all_num'] = $active_result[0]['num_cnt'];
            $else_result = $active_model->table('sendnum_number')->where(array('active_id' => $val['active_id'], 'status' => 0))->select();
            $val['surplus_num'] = count($else_result);
            $val['limit_num'] = $active_result[0]['conf_cnt'];
            $val['release_tm'] = $val['start_tm'];
            $val['active_name'] = $active_result[0]['active_name'];
            $val['active_status'] = $active_result[0]['status'];
            $result[$key] = $val;
        }

        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign('page', $show);
        $count = count($result);
        $this->assign("count", $count);
        $this->assign("result", $result);
        $this->display();
    }

    //活动礼包添加显示
    function active_add_show() {
        $this->display();
    }

    //迭代7活动礼包_添加显示
    public function active_add_show1() {
        $active_model = D('sendNum.sendNum');
        $model = new Model();
        if ($this->isAjax()) {
            $keyword = $_GET['query'];

            $key_result = $model->query("select name from sj_category where parentid = 2 and status=1 and name like '%{$keyword}%'");

            $data = array(
                'query' => $keyword,
                'suggestions' => array(),
            );
            foreach ($key_result as $v) {
                $data['suggestions'][] = $v['name'];
            }
            exit(json_encode($data));
        }


        //刷新已过期活动礼包
		$this->refresh_gift_rank();
        
        // $time = time();
        // $sql = "select * from sendNum.sendnum_active as a inner join sendNum.olgame_active as b on a.id = b.active_id where a.status = 1 and b.status = 1 and a.end_tm >{$time} and b.rank not in (0) order by b.rank limit 1";
		// var_dump($_GET);
        // $first_one = $active_model->query($sql);
        // $l_count = count($first_one);
        // if ($l_count != 0) {
            // $flash = 'flash';
            // $this->p_edit_rank($flash);
        // }
        $result_1 = $active_model->table("sendNum.sendnum_gift_type")->where(array('gift_category' => 1, 'status' => 1))->select();	
        $result_2 = $active_model->table("sendNum.sendnum_gift_type")->where(array('gift_category' => 2, 'status' => 1))->select();
        $this->assign('result_1', $result_1);
        $this->assign('result_2', $result_2);
        $rand = rand(1, 9999);
        $this->assign('rand', $rand);
        $this->display();
    }

	//刷新过期礼包
	function refresh_gift_rank(){
		$active_model = D('sendNum.sendNum');
		$now_where['_string'] = "status = 1 and end_tm < " . time() . "";
        $now_result = $active_model->table('sendNum.sendnum_active')->field('id')->where($now_where)->select();
		$id_arr = array();
        foreach ($now_result as $key => $val) {
			$id_arr[] = $val['id'];           
        }
		$rank_where = array(
			'status' => 1,
			'active_id' => array('in',$id_arr)
		);
		$rank_result = $active_model->table('sendNum.olgame_active')->field('id,rank')->where($rank_where)->select();
		foreach($rank_result as $key=>$val){
			if ($val['rank'] != 0) {
                $other_where['_string'] = "status = 1 and rank > {$val['rank']}";
                $other_result = $active_model->table('sendNum.olgame_active')->field('id,rank')->where($other_where)->select();

                foreach ($other_result as $key => $val) {
                    $change_where['rank'] = $val['rank'];
                    $change_data = array(
                        'rank' => $val['rank'] - 1,
                    );
                    $change_result = $active_model->update_active_content($change_where, $change_data);
                 }
                $myself_where['id'] = $val['id'];
                $myself_data = array(
                    'rank' => 0,
                    'selection' => 0,
                );
                $myself_result = $active_model->update_active_content($myself_where, $myself_data);
            }
		}
	}
	
    //开启/关闭网游精选
    function change_selection() {
        $model = new Model();
        $active_model = D('sendNum.sendNum');
        $id = $_GET['id'];
        $have_been = $active_model->table('olgame_active')->where(array('id' => $id))->select();
        $one_been = $active_model->table('olgame_active')->where(array('selection' => 1, 'status' => 1))->select();
        $where['id'] = $id;
        if ($have_been[0]['selection'] == 1) {
            $data = array(
                'selection' => 0
            );
            $log = "关闭";
        } else {
            $time_result = $active_model->table('sendnum_active')->where(array('id' => $have_been[0]['active_id']))->select();
            if ($time_result[0]['end_tm'] < time()) {
                $this->error("已过期活动礼包不能成为精选网游");
            }
            if ($one_been) {
                $this->error("只能开启一个活动礼包成为精选网游");
            }
            if (!$have_been[0]['intro']) {
                $this->error("您没有填写简介内容，请先填写内容后在开启");
            }
            $data = array(
                'selection' => 1
            );
            $log = "开启";
        }
        $result = $active_model->update_active_content($where, $data);
        if ($result) {
            $this->writelog("网游_网游名称/icon配置_已{$log}id为{$id}的精选网游配置",'olgame_active',"{$id}", __ACTION__ ,"","edit");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list/from/2');
            $this->success("编辑成功");
        }
    }

    //迭代7活动礼包添加提交
    public function active_add_submit1() {

        $model = new Model();
		$active_model = D('sendNum.sendNum');
        $limit_num = $_POST['limit_num'];
        $active_from = 0;
        $gift_category = $_POST['gift_category'];
        if (!$gift_category) {
            $this->error("请选择礼包分类");
        }
        $active_name = trim($_POST['active_name']);
        $time = time();
        if (!$active_name) {
            $this->error("标题名称不能为空");
        }
        if (mb_strlen($active_name, 'utf-8') > 12) {
            $this->error("请输入12个字以内的名称");
        }
        if ($gift_category == 1) {
            $gift_type = $_POST['gift_type_1'];
        } else if($gift_category == 2){
            $gift_type = $_POST['gift_type_2'];
        }
        if ($gift_type == 0) {
            $this->error("请选择礼包类型");
        }
        $usable = $_POST['usable'];
        if (!$usable) {
            $this->error("使用范围不能为空");
        }
        if (strlen($usable) > 90) {
            $this->error("请输入30个字以内的使用范围");
        }
        $apply_pkg = trim($_POST['apply_pkg']);
        $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->order('softid DESC') -> limit(0,1)->select();
        if (!$pkg_result) {
            $this->error("应用包名不存在");
        }

		$soft_category = substr($pkg_result[0]['category_id'],1,-1);
		$parent_result = $model -> table('sj_category') -> where(array('category_id' => $soft_category)) -> find();
		$grand_result = $model -> table('sj_category') -> where(array('category_id' => $parent_result['parentid'])) -> find();
		
		if($gift_category == 1){
			if($parent_result['parentid'] != 1 && $grand_result['parentid'] != 1){
				$this -> error('选择应用礼包后添加的软件应为应用软件');
			}
		}elseif($gift_category == 2){
			if($parent_result['parentid'] != 2 && $grand_result['parentid'] != 2){
				$this -> error('选择游戏礼包后添加的软件应为游戏软件');
			}
		}
        $soft_name = trim($_POST['soft_name']);
        $cut_tm = strtotime($_POST['cut_tm']);
        $start_tm = strtotime($_POST['start_tm']);
        if ($start_tm == '') {
            $this->error("开始时间不能为空");
        }
        if ($cut_tm == '') {
            $this->error("结束时间不能为空");
        }
        if ($cut_tm < $start_tm) {
            $this->error("开始时间不能大于结束时间");
        }
        $intro = trim($_POST['intro']);
        if (strlen($intro) > 150) {
            $this->error("简介不能超过50个汉字");
        }

        if ($limit_num == '') {
            $this->error("每日发放限制不能为空");
        }


        if ($_POST['usage'] == '') {
            $this->error("使用方法不能为空");
        }

        if ($_POST['detail'] == '') {
            $this->error("礼包详情不能为空");
        }
        if ($_POST['exchange_start'] == '') {
            $this->error("兑换开始时间不能为空");
        }
        if ($_POST['exchange_end'] == '') {
            $this->error("兑换结束时间不能为空");
        }
        if ($_POST['exchange_end'] < $_POST['exchange_start']) {
            $this->error("兑换结束时间不能在兑换开始时间之前");
        }

        if (strtotime($_POST['exchange_start']) < $start_tm) {
            $this->error("兑换时间不能在开始时间之前");
        }

        //上传文件获取隐藏域中的内容
        $new_file = $_POST['new_file'];
        if ($new_file == '') {
            $this->error("请上传激活码文件");
        }

        if (!file_exists($new_file)) {
            $this->error("csv文件不存在");
        }


        $new_file_name = $new_file;
        $shili = fopen($new_file_name, "r");  //打开文本

        while (!feof($shili)) {      //判断是否到了文件最后的函数
            $shi = fgets($shili, 1024);    //读取其中的数据

            $a = explode(',', $shi);

            if ($a[1]) {
                $this->error("激活码文件格式错误");
            }
            $str .= $shi . ",";
        }

        $str_arr = str_replace("\r\n", '', $str);

        $str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);

        $code_arrs = explode(',', $str_arrs);

        foreach ($code_arrs as $key => $val) {
            if (trim($val) != "") {
                $code_arr[$key] = $val;
            }
        }

        $all_num = count($code_arr);

        if ($all_num > 9999) {
            $this->error("激活码总数不能超过9999个");
        }

        if ($all_num < 1) {
            $this->error("激活码不能为空");
        }



        if ($limit_num > $all_num) {
            $this->error("每日限制数不能大于激活码总数");
        }

        if ($limit_num) {
            $limit_num = intval(strval($limit_num));
            if ($limit_num <= 0 || !is_int($limit_num)) {
                $this->error("请输入正确格式的限制数");
            }
        }


        foreach ($code_arr as $key => $val) {
            if (strlen($val) > 25) {
                $this->error("激活码长度不能大于25位");
            }
        }

        $code_arr_unique = array_unique($code_arr);
        if (preg_match("/[\x80-\xff]./", $str_arrs)) {
            $this->error("激活码文件中不可有中文");
        }
        if (count($code_arr) > count($code_arr_unique)) {
            $repeat_arr = array_diff_assoc($code_arr, $code_arr_unique);
            foreach ($repeat_arr as $key => $val) {
                $repeat_str .= $val . ',';
                $this->error("激活码文件中含有重复数据:{$val}");
            }
        }

        if ($gift_category == 2) {
            if (!empty($_POST['pt_market']) && $_POST['pt_market'] == 'on') {
                $market_num = intval($_POST['sc_num']);
                $active_from += 4;

                if ($market_num == 0 || $market_num == '') {
                    $this->error("发放平台激活码数量不能为0");
                }
                if ($market_num < $limit_num) {
                    $this->error("每日发放限制不能大于平台发布数量");
                }
            } else {
                $market_num = 0;
            }
            $data_active['market_conf_cnt'] = $market_num;
            $data_tmp['market_conf_cnt'] = $market_num;

            if (!empty($_POST['pt_game']) && $_POST['pt_game'] == 'on') {
                $game_num = intval($_POST['game_num']);
                $active_from += 2;

                if ($game_num == 0 || $game_num == '') {
                    $this->error("发放平台激活码数量不能为0");
                }
                if ($game_num < $limit_num) {
                    $this->error("每日发放限制不能大于平台发布数量");
                }
            } else {
                $game_num = 0;
            }
            $data_active['game_conf_cnt'] = $game_num;
            $data_tmp['game_conf_cnt'] = $game_num;
            if (!empty($_POST['pt_sdk']) && $_POST['pt_sdk'] == 'on') {
                $sdk_num = intval($_POST['sdk_num']);
                $active_from += 8;

                if ($sdk_num == 0 || $sdk_num == '') {
                    $this->error("发放平台激活码数量不能为0");
                }
                if ($sdk_num < $limit_num) {
                    $this->error("每日发放限制不能大于平台发布数量");
                }
            } else {
                $sdk_num = 0;
            }
            $data_active['sdk_conf_cnt'] = $sdk_num;
            $data_tmp['sdk_conf_cnt'] = $sdk_num;
            
            if (!empty($_POST['pt_bbs']) && $_POST['pt_bbs'] == 'on') {
                $bbs_num = intval($_POST['bbs_num']);
                $active_from += 1;

                if ($bbs_num == 0 || $bbs_num == '') {
                    $this->error("发放平台激活码数量不能为0");
                }
                if ($bbs_num < $limit_num) {
                    $this->error("每日发放限制不能大于平台发布数量");
                }
            } else {
                $bbs_num = 0;
            }
            $data_active['bbs_conf_cnt'] = $bbs_num;
            $data_tmp['bbs_conf_cnt'] = $bbs_num;
            
            if (!empty($_POST['pt_weixin']) && $_POST['pt_weixin'] == 'on') {
                $weixin_num = intval($_POST['weixin_num']);
                $active_from += 16;
            
                if ($weixin_num == 0 || $weixin_num == '') {
                    $this->error("发放平台激活码数量不能为0");
                }
                if ($weixin_num < $limit_num) {
                    $this->error("每日发放限制不能大于平台发布数量");
                }
            } else {
                $weixin_num = 0;
            }
            $data_active['weixin_conf_cnt'] = $weixin_num;
            $data_tmp['weixin_conf_cnt'] = $weixin_num;
            
            $count_num = $market_num + $game_num + $sdk_num + $bbs_num + $weixin_num;

            if ($count_num > $all_num) {
                $this->error("当前发布的礼包数量大于总数，请重新填写");
            } else if ($count_num < $all_num) {
                $this->error("当前发布的礼包数量小于总数，请重新填写");
            }

            $bbs_score = $_POST['bbs_score'];
            $bbs_pic = $_FILES['bbs_pic'];
            if ($_POST['pt_bbs'] == 'on') {
                if ($bbs_score < 0 || !is_numeric($bbs_score)) {
                    $this->error("请填写正确的论坛领取所需金币");
                }
                $path = date('Ym/d');
                if ($bbs_pic['size']) {
                    $config['multi_config']['bbs_pic'] = array(
                        'savepath' => UPLOAD_PATH . '/img/' . $path,
                        'saveRule' => 'getmsec',
                    );
                    $list = $this->_uploadapk(0, $config);
                    $bbs_pic_url = $list['image'][0]['url'];
                    $data_active['bbs_pic'] = $bbs_pic_url;
                } else {
                    $this->error("选择发放平台为论坛后请上传论坛礼包图片");
                }
            }
            $data_active['bbs_score'] = $bbs_score;
			//游戏名称首字母
			$pinyin = Pinyin($pkg_result[0]['softname']);
			$game_prefix = strtoupper(substr($pinyin,0,1));
            $data_active['game_prefix'] = $game_prefix;
			$same_package_result = $active_model -> table('olgame_active') -> where(array('gift_category' => 2,'apply_pkg' => $apply_pkg)) -> select();
			foreach($same_package_result as $key => $val){
				$prefix_result = $active_model -> table('sendnum_active') -> where(array('id' => $val['active_id'])) -> save(array('game_prefix' => $game_prefix));
			}
			
			
            $game_type = trim($_POST['game_type']);
            if ($_POST['pt_bbs'] == 'on') {
                if (!$game_type) {
                    $this->error("选择论坛发放必须填写游戏分类");
                }
            }
            if ($game_type) {
                $game_category_result = $model->table('sj_category')->where(array('name' => $game_type, 'status' => 1))->select();
                $category_result = $model->table('sj_category')->where(array('parentid' => 2, 'status' => 1))->select();
                foreach ($category_result as $k => $v) {
                    $category_arr[] = $v['category_id'];
                }

                if (!in_array($game_category_result[0]['parentid'], $category_arr) && $game_category_result[0]['parentid'] != 2) {
                    $this->error("该游戏分类不存在");
                }
                $game_category = $game_category_result[0]['category_id'];
                $data_active['game_category'] = $game_category;
            }
			
			
			
        }

        //数据写入
        $data_tmp['apply_pkg'] = $apply_pkg;
        $data_tmp['active_name'] = $active_name;
        $data_tmp['gift_type'] = $gift_type;
        $data_tmp['start_tm'] = strtotime($_POST['start_tm']);
        $data_tmp['end_tm'] = strtotime($_POST['cut_tm']);
        $data_tmp['be_limit_num'] = $limit_num;
        $data_tmp['num_cnt'] = $all_num;
        $data_tmp['used_cnt'] = 0;
        $data_tmp['detail'] = trim($_POST['detail']);
        $data_tmp['exchange_start'] = strtotime($_POST['exchange_start']);
        $data_tmp['exchange_end'] = strtotime($_POST['exchange_end']);
        $data_tmp['usable'] = $usable;
        $data_tmp['usage'] = trim($_POST['usage']);
        $data_tmp['status'] = 1;
		if($active_from){
			$data_tmp['active_from'] = $active_from;
		}else{
			$data_tmp['active_from'] = 4;
		}
        if ($gift_category == 1) {
            $data_tmp['market_conf_cnt'] = $all_num;
        }
        $data_tmp['add_tm'] = time();
        $data_tmp['update_tm'] = time();
        $data_tmp['creater'] = $_SESSION['admin']['admin_user_name'];
        $data_tmp['creater_id'] = $_SESSION['admin']['admin_id'];
        $data_tmp['intro'] = htmlspecialchars($_POST['intro']);
        $data_active['active_name'] = $active_name;
        $data_active['soft_name'] = trim($soft_name);
        $data_active['gift_category'] = trim($gift_category);
        $data_active['active_type'] = 1;
        $data_active['conf_cnt'] = $limit_num;
        $data_active['status'] = 1;
        $data_active['end_tm'] = strtotime($_POST['cut_tm']);
        $data_active['start_tm'] = strtotime($_POST['start_tm']);
        $data_active['num_cnt'] = $all_num;
        $data_active['used_cnt'] = 0;
        $data_active['creater'] = $_SESSION['admin']['admin_user_name'];
        $data_active['creater_id'] = $_SESSION['admin']['admin_id'];
        $data_active['add_tm'] = time();
        $data_active['update_tm'] = time();		
		if($active_from){
			$data_active['active_from'] = $active_from;
		}else{
			$data_active['active_from'] = 4;
		}

        if ($gift_category == 1) {
            $data_active['market_conf_cnt'] = $all_num;
        }
        $data_active['gift_type'] = $gift_type;

        $data['apply_pkg'] = $apply_pkg;
        $data['gift_category'] = $gift_category;
        $data['usable'] = $usable;
        $data['module_content'] = htmlspecialchars($_POST['editor_content']);
        $data['create_tm'] = time();
        $data['update_tm'] = time();
        $data['status'] = 1;
        $data['active_type'] = $active_from;
        
        if (strtotime($_POST['start_tm']) < $time && strtotime($_POST['cut_tm']) < $time) {
            $data['rank'] = 0;
        } else {
            $data['rank'] = 1;
        }
        $data['intro'] = htmlspecialchars($_POST['intro']);

        $data['be_limit_num'] = $limit_num;
        $data['be_active_name'] = $active_name;
        $data['be_apply_pkg'] = $apply_pkg;
        $data['be_usable'] = $usable;
        //$data['sim_content'] = $_POST['sim_content'];
        $data['be_cut_tm'] = strtotime(date('Y-m-d H:i:s', strtotime($_POST['cut_tm'])));
        $data['exchange_start'] = strtotime($_POST['exchange_start']);
        $data['exchange_end'] = strtotime($_POST['exchange_end']);
        $data['usage'] = trim($_POST['usage']);
        $data['detail'] = trim($_POST['detail']);
        // $have_result = $active_model->table('sendNum.sendnum_active')->where("active_name = '{$active_name}' and status != 0")->select();
        // if ($have_result) {
            // $this->error("对不起，标题不能相同");
        // }
        $result = $active_model->add_active($data_active);
		$data_tmp['sendnumactive_id'] = $result;
		$data_tmp['rand_status'] = 3;
        $res = $active_model->table('sendnum_tmp')->data($data_tmp)->add();
        if (!$res) {
            $this->error("入库待审核表失败");
        }
        $data['gift_url'] = "/market_gift_preview_{$result}.html";
        if ($result && $res) {
            $data['active_id'] = $result;
            $active_result = $active_model->add_active_content($data);
            $query = "select a.id,a.rank from olgame_active as a left join sendnum_active as b on a.active_id = b.id where b.status = 1 and a.rank != 0 and a.active_id != {$result}";
            $have_been = $active_model->query($query);
            if ($data['rank'] != 0) {

                foreach ($have_been as $key => $val) {
                    $rank_result = $active_model->table('olgame_active')->where(array('id' => $val['id']))->save(array('rank' => $val['rank'] + 1));
                }
            }
			$active_model->create_sendnum_number($result);
			$code_arr = array_chunk($code_arr,500);
			
			//激活码入口
			foreach ($code_arr as $key => $val) {
				if ($val) {
					$sql_str = '';
					foreach($val as $v){
						$sql_str .= ",({$result},'{$v}',0)";
					}
					$sql_str =  substr($sql_str,1);
					$active_model->add_market_num($sql_str, $result);
				}
			}
        }
		/*
        $static_file = $this->active_module_url;
        $static_file_true = $this->active_module_true_url;
        //$a = "<a href='javascript:window.AnzhiActivitys.downloadForActivity('{$pkg_result}')' class='down_btn' title='安智市场下载'>开始下载</a>";
        $js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
        preg_match("/href=\"(.*)\"/Ui", $module_content, $my_a);
        $package = $my_a[1];
        $module_contents = str_replace($package, "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)", $module_content);
        //生成静态页面
        ob_start();
        echo $this->header;
        echo $js_a;
        echo $module_contents;
        $temp = ob_get_contents();
        ob_end_clean();
        if (!file_exists($static_file)) {
            @mkdir(rtrim($static_file, '/'), 0777, true) or die("创建目录失败");
        }
        $fp = fopen($static_file . "/market_gift_preview_{$result}.html", 'w');
        $create_result = fwrite($fp, $temp);
        fclose($fp);
        if (!file_exists($static_file_true)) {
            @mkdir(rtrim($static_file_true, '/'), 0777, true) or die("创建目录失败");
        }
        if ($create_result) {
            if (file_exists($static_file_true . "/market_gift_preview_{$result}.html")) {
                unlink($static_file_true . "/market_gift_preview_{$result}.html");
            }
            if (!@copy($static_file . "/market_gift_preview_{$result}.html", $static_file_true . "/market_gift_preview_{$result}.html")) {
                $this->error("移动文件错误");
            }  //删除以前的文件并把新的html移动到指定位置
        } else {
            $this->error("写入文件错误");
        }
		*/

        if ($result) {

			//
      $fileicon = $model->table('sj_soft_file')->where(array('apk_name' => $apply_pkg, 'package_status'=>1))->field('iconurl_125')->order('id desc')->find();
			$send_data = array(
				'serviceId' => $active_from,       //业务线id  ,礼包的使用业务线
				'giftName' => $active_name,//"奖品名称"
				'giftType' => '2',//奖品类型：1:积分  2：礼包  3：话费
				'giftSoftName' => $pkg_result[0]['softname'],//软件名称
				'giftSoftPname' => $apply_pkg,//软件包名
				'giftTotal' => $all_num,//礼包总数
				'giftEvSum' => $limit_num,//明日发放限制数量
				'prImage' => 'http://img3.anzhi.com'.$fileicon['iconurl'],//奖品图片
				'prShelvesDate' => $data_active['start_tm'],   //有效期--开始时间--使用时间
				'prUnderDate' => $data_active['end_tm'],   //有效期--结束时间
				'prSdate' =>  $data['exchange_start'] ,  //上架时间
				'prEdate' =>  $data['exchange_end'], //下架时间				
				'prSortno'  => $data['rank'],//排序号
				'remark' => $_POST['detail'], //奖品说明
				'giftUse' => '2', //奖品用途   0:抽奖  1:兑换   2:领取
				'useRange' => $usable,//使用范围
				'useWay' => $_POST['usage'] ,//使用方法
				'createTime' => $data_active['add_tm'],
				'updateTime' => $data_active['update_tm'],
				'delStatus' => 0 ,//0:整除  1:删除。
				'ref_id' => $result,
				'fromServiceId' => '007',//数据来源业务线
				'oper_type' => 0,//0:新增、1：修改、2：删除
			);
			$active_model -> send_gift_work($send_data);
            //礼包推送
            $push_data = array(
                'active_name' => $active_name,
                'intro' => $data['intro'],
                'active_id' =>$result,
                'start_tm' => $data_active['start_tm'],
                'cut_tm' => $data_active['end_tm'],
                'exchange_start' => $data['exchange_start'],
                'exchange_end' => $data['exchange_end'],
                'icon_url' => 'http://img3.anzhi.com'.$fileicon['iconurl_125']
            );
            push_gift_msg($push_data);

            $this->writelog("已添加id为{$result}的标题为{$active_name}的礼包",'olgame_active',"{$result}", __ACTION__ ,"","add");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Onlinegame/active_gift_list1/gift_category/{$gift_category}");
            $this->success("添加成功");
        }
    }

    //迭代7审核礼包
    public function p_verify_gift() {
        if (!empty($_GET['id']) && $_GET['id']) {
            $active_model = D('sendNum.sendNum');
            $result = $active_model->table('sendnum_tmp')->where('id = "' . $_GET['id'] . '"')->select();
            $gift_type = $active_model->table('sendnum_gift_type')->where('status = 1')->select();
            $sdk_model = M('');
            $sdk = $sdk_model->table('sj_soft_whitelist')->where('package = "' . $result[0]['apply_pkg'] . '"')->field('is_sdk')->find();
            $result[0]['sdk_status'] = $sdk['is_sdk'];
            $result[0]['start_tm'] = date("Y-m-d H:i:s", $result[0]['start_tm']);
            $result[0]['end_tm'] = date("Y-m-d H:i:s", $result[0]['end_tm']);
            $result[0]['exchange_start'] = date("Y-m-d H:i:s", $result[0]['exchange_start']);
            $result[0]['exchange_end'] = date("Y-m-d H:i:s", $result[0]['exchange_end']);
            $result[0]['down_path'] = IMG_URL . $result[0]['up_file_path'];
            $path = explode('/', $result[0]['up_file_path']);
            $result[0]['name'] = $path[count($path) - 1];
			$this->assign('img_host', IMG_URL);
            $this->assign('result', $result);
            $this->assign('gift_type', $gift_type);
        }
        $this->display();
    }
 
    //迭代7审核礼包（通过，驳回）
    public function p_change_gift() {
        $path = P_LOG_DIR . '/gift';
        $time = time();
        $name = P_LOG_DIR . '/gift/gift' . $_POST['id'] . "_" . $time . '.txt';
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                die("创建目录失败1");
            }
            //@mkdir(rtrim($path, '/'), 0777, true) or die("创建目录失败");
        }
        $of = fopen($name, 'w'); //创建并打开dir.txt
        fclose($of);

        $id = empty($_POST['id']) ? "" : $_POST['id'];
        $active_model = D('sendNum.sendNum');
        if (!empty($id)) {
            $emailmodel = D("Dev.Sendemail");
            $config_txt = C('_config_txt_');
            $gift_model = M('');
            $model = M('');
            $change_status = empty($_POST['status']) ? "" : $_POST['status'];
            if (!empty($change_status) && $change_status == 1) {
                //审核通过
                $limit_num = $_POST['limit_num'];

                $active_from = 0;
                if (!empty($_POST['pt_market']) && $_POST['pt_market'] == 'on') {
                    $market_num = intval($_POST['sc_num']);
                    $active_from += 4;

                    if ($market_num == 0 || $market_num == '') {
                        $this->error("发放平台激活码数量不能为0");
                    }
                    if ($market_num < $limit_num) {
                        $this->error("每日发放限制不能大于平台发布数量1");
                    }
                    if ($market_num > $_POST['num_cnt']) {
                        $this->error("平台发布数量不能大于总数量");
                    }
                } else {
                    $market_num = 0;
                }

                if (!empty($_POST['pt_game']) && $_POST['pt_game'] == 'on') {
                    $game_num = intval($_POST['game_num']);
                    $active_from += 2;

                    if ($game_num == 0 || $game_num == '') {
                        $this->error("发放平台激活码数量不能为0");
                    }
                    if ($game_num < $limit_num) {
                        $this->error("每日发放限制不能大于平台发布数量2");
                    }
                    if ($game_num > $_POST['num_cnt']) {
                        $this->error("平台发布数量不能大于总数量");
                    }
                } else {
                    $game_num = 0;
                }

                if (!empty($_POST['pt_sdk']) && $_POST['pt_sdk'] == 'on') {
                    $sdk_num = intval($_POST['sdk_num']);
                    $active_from += 8;

                    if ($sdk_num == 0 || $sdk_num == '') {
                        $this->error("发放平台激活码数量不能为0");
                    }
                    if ($sdk_num < $limit_num) {
                        $this->error("每日发放限制不能大于平台发布数量3");
                    }
                    if ($sdk_num > $_POST['num_cnt']) {
                        $this->error("平台发布数量不能大于总数量");
                    }
                } else {
                    $sdk_num = 0;
                }
				if (!empty($_POST['pt_bbs']) && $_POST['pt_bbs'] == 'on') {
                    $bbs_num = intval($_POST['bbs_num']);
                    $active_from += 1;

                    if ($bbs_num == 0 || $bbs_num == '') {
                        $this->error("发放平台激活码数量不能为0");
                    }
                    if ($bbs_num < $limit_num) {
                        $this->error("每日发放限制不能大于平台发布数量4");
                    }
                    if ($bbs_num > $_POST['num_cnt']) {
                        $this->error("平台发布数量不能大于总数量");
                    }
                } else {
                    $bbs_num = 0;
                }
                if (!empty($_POST['pt_weixin']) && $_POST['pt_weixin'] == 'on') {
                    $weixin_num = intval($_POST['weixin_num']);
                    $active_from += 16;

                    if ($weixin_num == 0 || $weixin_num == '') {
                        $this->error("发放平台激活码数量不能为0");
                    }
                    if ($weixin_num < $limit_num) {
                        $this->error("每日发放限制不能大于平台发布数量4");
                    }
                    if ($weixin_num > $_POST['num_cnt']) {
                        $this->error("平台发布数量不能大于总数量");
                    }
                } else {
                    $weixin_num = 0;
                }
                if ($market_num + $game_num + $sdk_num + $bbs_num + $weixin_num != $_POST['num_cnt']) {
                    $this->error("平台发放总数量应等于上传激活码总数量");
                }
                $active_name = trim($_POST['active_name']);
                $time = time();
                if (!$active_name) {
                    $this->error("礼包名称不能为空");
                }
                if (mb_strlen($active_name, 'utf-8') > 12) {
                    $this->error("请输入12个字以内的名称");
                }
                $gift_type = $_POST['gift_type'];
                if ($gift_type == 0) {
                    $this->error("请选择礼包类型");
                }
                $usable = $_POST['usable'];
                if (!$usable) {
                    $this->error("使用范围不能为空");
                }
                if (mb_strlen($usable, 'utf-8') > 30) {
                    $this->error("请输入30个字以内的使用范围");
                }
                $apply_pkg = trim($_POST['apply_pkg']);

                $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->field('softid,softname')->find();
                if ($pkg_result) {
                    file_put_contents($name, "存在此包名\r", FILE_APPEND);
                } else {
                    file_put_contents($name, "不存在此包名\r", FILE_APPEND);
                }

                if (!$pkg_result) {
                    $this->error("应用包名不存在");
                }
                $intro = trim($_POST['intro']);
                if (strlen($intro) > 150) {
                    $this->error("简介不能超过50个汉字");
                }

                if ($limit_num == '') {
                    $this->error("每日发放限制不能为空");
                }


                if ($_POST['usage'] == '') {
                    $this->error("使用方法不能为空");
                }

                if ($_POST['detail'] == '') {
                    $this->error("礼包详情不能为空");
                }
				if ($_POST['exchange_start'] == '') {
                    $this->error("兑换开始时间不能为空");
                }
				
                if ($_POST['exchange_end'] == '') {
                    $this->error("兑换结束时间不能为空");
                }
				$active = $_POST;
				$end_tm = strtotime($active['cut_tm']);
				$start_tm = strtotime($active['start_tm']);
				$exchange_start = strtotime($active['exchange_start']);
				$exchange_end = strtotime($active['exchange_end']);
				
                if ($end_tm < $start_tm) {
                    $this->error("领取结束时间不能在领取开始时间之前");
                }
                if ($exchange_end < $exchange_start) {
                    $this->error("兑换结束时间不能在兑换开始时间之前");
                }

                if ($exchange_start < $start_tm) {
                    $this->error("兑换时间不能在领取开始时间之前");
                }

                //数据写入
                $data_active['active_name'] = $active['active_name'];
                $data_active['active_type'] = 1;
                $data_active['conf_cnt'] = $active['limit_num'];
                $data_active['status'] = 1;
                $data_active['add_tm'] = $active['add_tm'];
                $data_active['end_tm'] = $end_tm;
                $data_active['start_tm'] = $start_tm;
                $data_active['num_cnt'] = $active['num_cnt'];
                $data_active['used_cnt'] = 0;
                $data_active['creater'] = $active['creater'];
                $data_active['creater_id'] = $active['creater_id'];
                $data_active['active_from'] = $active_from;
                $data_active['market_conf_cnt'] = $active['sc_num'];
                $data_active['game_conf_cnt'] = $active['game_num'];
                $data_active['sdk_conf_cnt'] = $active['sdk_num'];
                $data_active['bbs_conf_cnt'] = $active['bbs_num'];
                $data_active['weixin_conf_cnt'] = $active['weixin_num'];
				$data_active['bbs_pic'] = $active['bbs_pic'];
                $data_active['gift_type'] = $active['gift_type'];
                $data_active['dev_id'] = $active['dev_id'];
                $data_active['from'] = 1;
                $data_active['game_prefix'] = $active['game_prefix'];
                $data_active['soft_name'] = $pkg_result['softname'];
                $data['apply_pkg'] = $active['apply_pkg'];
                $data['usable'] = $active['usable'];
                //$data['module_content'] = htmlspecialchars($_POST['editor_content']);
                $data['create_tm'] = $active['add_tm'];
                $data['update_tm'] = $active['update_tm'];
                $data['status'] = 1;

                $time = time();

                if ($data_active['start_tm'] < $time && $data_active['end_tm'] < $time) {
                    $data['rank'] = 0;
                } else {
                    $data['rank'] = 1;
                }
                $data['intro'] = $active['intro'];
                $data['be_limit_num'] = $active['limit_num'];
                $data['be_active_name'] = $active['active_name'];
                $data['be_apply_pkg'] = $active['apply_pkg'];
                $data['be_usable'] = $active['usable'];
                //$data['sim_content'] = $_POST['sim_content'];
                $data['be_cut_tm'] = $active['end_tm'];
                $data['exchange_start'] = $exchange_start;
                $data['exchange_end'] = $exchange_end;
                $data['usage'] = $active['usage'];
                $data['detail'] = $active['detail'];
                // $have_result = $active_model->table('sendNum.sendnum_active')->where("active_name = '{$active['active_name']}' and status != 0")->select();
                // if ($have_result) {
                    // $this->error("对不起，礼包名称不能相同");
                // }
                $new_file = IMG_HOST . $active['up_file_path'];
                if (!file_exists($new_file)) {
                    file_put_contents($name, "csv文件不存在\r", FILE_APPEND);
                    $this->error("csv文件不存在");
                }

                $result = $active_model->add_active($data_active);
                $data['gift_url'] = "/market_gift_preview_{$result}.html";
                if ($result) {
                    file_put_contents($name, "add_active成功，成功id为" . $result . "\r", FILE_APPEND);
                    //上传文件获取隐藏域中的内容
                    $new_file_name = $new_file;
                    $shili = fopen($new_file_name, "r");  //打开文本
                    while (!feof($shili)) {      //判断是否到了文件最后的函数
                        $shi = fgets($shili, 1024);    //读取其中的数据
                        $a = explode(',', $shi);
                        if ($a[1]) {
                            $active_model->table('sendnum_active')->where(array('id' => $result))->delete();
                            file_put_contents($name, "激活码文件格式错误，回滚删除sendnum_active的id为" . $result . "\r", FILE_APPEND);
                            $this->error("激活码文件格式错误");
                        }
                        $str .= $shi . ",";
                    }
                    $str_arr = str_replace("\r\n", '', $str);
                    $str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);
                    $code_arrs = explode(',', $str_arrs);
                    foreach ($code_arrs as $key => $val) {
                        if (trim($val) != "") {
                            $code_arr[$key] = $val;
                        }
                    }
					$active_model->create_sendnum_number($result);
					$code_arr = array_chunk($code_arr,500);
                    //激活码入口
                    foreach ($code_arr as $key => $val) {
                        if ($val) {
							$sql_str = '';
							foreach($val as $v){
								$sql_str .= ",({$result},'{$v}',0)";
							}
							$sql_str =  substr($sql_str,1);
							$active_model->add_market_num($sql_str, $result);
                        }
                    }
                    $data['active_id'] = $result;
                    $active_result = $active_model->add_active_content($data);
                    if ($active_result) {
                        file_put_contents($name, "add_active_content成功，成功id为" . $active_result . "\r", FILE_APPEND);
                    } else {
                        $active_model->table('sendnum_active')->where(array('id' => $result))->delete();
                        file_put_contents($name, "add_active_content失败，回滚删除sendnum_active的id为" . $result . "\r", FILE_APPEND);
                        $this->error("数据插入错误,请重试!");
                    }
                    $query = "select a.id,a.rank from olgame_active as a left join sendnum_active as b on a.active_id = b.id where b.status = 1 and a.rank != 0 and a.active_id != {$result}";
                    $have_been = $active_model->query($query);
                    if ($data['rank'] != 0) {
                        foreach ($have_been as $key => $val) {
                            $rank_result = $active_model->table('olgame_active')->where(array('id' => $val['id']))->save(array('rank' => $val['rank'] + 1));
                        }
                    }
					$map = array(
						'status' => 1,
						'rand_status'=>3,
						'be_limit_num'=>$active['limit_num'],
						'sendnumactive_id' => $result,
						'update_tm'=>$time,
						'market_conf_cnt' => $active['sc_num'],
						'game_conf_cnt' => $active['game_num'],
						'sdk_conf_cnt' => $active['sdk_num'],
						'bbs_conf_cnt' => $active['bbs_num'],
						'weixin_conf_cnt' => $active['weixin_num'],
						'active_from' => $active_from,
						'start_tm' => $start_tm,
						'end_tm' => $end_tm,
						'exchange_start' => $exchange_start,
						'exchange_end' => $exchange_end,
					);
                    $num_tmp = $active_model->table('sendnum_tmp')->where('id = "' . $id . '"')->data($map)->save();

                    if ($num_tmp) {
                        file_put_contents($name, "sendnum_tmp更新成功，成功id为" . $result . "\r", FILE_APPEND);
                    } else {
                        $sql = $active_model->getLastsql();
                        $active_model->table('sendnum_active')->where(array('id' => $result))->delete();
                        $active_model->table('olgame_active')->where(array('active_id' => $result))->delete();
                        file_put_contents($name, "sendnum_tmp更新失败\r" . $sql . "\r，并回滚删除sendnum_active（id为" . $result . "）和olgame_active（active_id为" . $result . "）", FILE_APPEND);
                        $this->error("数据插入错误,请重试!");
                    }
                } else {
                    file_put_contents($name, "add_active失败/r", FILE_APPEND);
                    $this->error("数据插入错误,请重试!");
                }

                $static_file = $this->active_module_url;
                $static_file_true = $this->active_module_true_url;
                //$a = "<a href='javascript:window.AnzhiActivitys.downloadForActivity('{$pkg_result}')' class='down_btn' title='安智市场下载'>开始下载</a>";
                $js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
                preg_match("/href=\"(.*)\"/Ui", $module_content, $my_a);
                $package = $my_a[1];
                $module_contents = str_replace($package, "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)", $module_content);
                //生成静态页面
                ob_start();
                echo $this->header;
                echo $js_a;
                echo $module_contents;
                $temp = ob_get_contents();
                ob_end_clean();
                if (!is_dir($static_file)) {
                    file_put_contents($name, "创建临时目录失败/r", FILE_APPEND);
                    @mkdir(rtrim($static_file, '/'), 0777, true) or die("创建目录失败2");
                }
                $fp = fopen($static_file . "/market_gift_preview_{$result}.html", 'w');
                $create_result = fwrite($fp, $temp);
                fclose($fp);
                if (!is_dir($static_file_true)) {
                    file_put_contents($name, "创建线上目录失败/r", FILE_APPEND);
                    @mkdir(rtrim($static_file_true, '/'), 0777, true) or die("创建目录失败3");
                }
                if ($create_result) {
                    if (file_exists($static_file_true . "/market_gift_preview_{$result}.html")) {
                        unlink($static_file_true . "/market_gift_preview_{$result}.html");
                    }
                    if (!@copy($static_file . "/market_gift_preview_{$result}.html", $static_file_true . "/market_gift_preview_{$result}.html")) {
                        $this->error("移动文件错误");
                    }  //删除以前的文件并把新的html移动到指定位置

                    //前台提醒
                    $tm = date("Y-m-d H:i:s", time());
                    $search = array("softname", "giftname", "tm");
                    $replace = array($pkg_result['softname'], $active['active_name'], $tm);
                    $msg = str_replace($search, $replace, $config_txt['gift_pass']);
                    $emailmodel->dev_remind_add($active['dev_id'], $msg);
                    //发送邮件提醒
                    $model = new Model();
                    $dever = $model->table('pu_developer')->where("dev_id={$active['dev_id']}")->field('dev_id,email,dev_name')->find();
                    $subject = $config_txt['gift_pass_subject'];
                    $search2 = array("devname", "softname", "giftname", "tm");
                    $replace2 = array($dever['dev_name'], $pkg_result['softname'], $active['active_name'], $tm);
                    $email_cont = str_replace($search2, $replace2, $config_txt['gift_pass_txt']);
                    $emailmodel->realsend($dever['email'], $dever['dev_name'], $subject, $email_cont);
                    $this->writelog("通过了礼包名为" . $active['active_name'] . "的礼包",'sendnum_tmp',"{$id}", __ACTION__ ,"","edit");
                    $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/verify_gift_list');
					if($data_active['start_tm'] > $time){
						$gift_status = "3";//预定中
					}else if($data_active['start_tm'] <= $time && $data_active['end_tm'] >= $time){
						$gift_status = "4";//领取中
					}else if($data_active['end_tm'] < $time ){
						$gift_status = "6";//已过期
					}
					//
          $fileicon = $model->table('sj_soft_file')->where(array('apk_name' => $apply_pkg, 'package_status'=>1))->field('iconurl_125')->order('id desc')->find();
					$send_data = array(
						'serviceId' => $active_from,       //业务线id  ,礼包的使用业务线
						'giftName' => $active['active_name'],//"奖品名称"
						'giftType' => '2',//奖品类型：1:积分  2：礼包  3：话费
						'giftSoftName' => $pkg_result['softname'],//软件名称
						'giftSoftPname' => $apply_pkg,//软件包名
						'giftTotal' => $active['num_cnt'],//礼包总数
						'giftEvSum' => $active['limit_num'],//明日发放限制数量
						'prImage' => 'http://img3.anzhi.com'.$fileicon['iconurl'],//奖品图片
						'prShelvesDate' => $start_tm,   //有效期--开始时间--使用时间
						'prUnderDate' =>  $end_tm,   //有效期--结束时间
						'prSdate' => $exchange_start ,  //上架时间
						'prEdate' =>  $exchange_end, //下架时间						
						'prSortno'  => $data['rank'],//排序号
						'remark' => $active['detail'], //奖品说明
						'giftUse' => '2', //奖品用途   0:抽奖  1:兑换   2:领取
						'useRange' => $active['usable'],//使用范围
						'useWay' => $active['usage'] ,//使用方法
						'createTime' => $active['add_tm'],
						'updateTime' => $active['update_tm'],
						'delStatus' => 0 ,//0:整除  1:删除。
						'ref_id' => $result,
						'fromServiceId' => '007',//数据来源业务线
						'oper_type' => 0,//0:新增、1：修改、2：删除
					);
					$active_model -> send_gift_work($send_data);
                    //礼包推送
                    $push_data = array(
                        'active_name' => $active['active_name'],
                        'intro' => $active['intro'],
                        'active_id' =>$result,
                        'start_tm' => $start_tm,
                        'cut_tm' => $end_tm,
                        'exchange_start' => $exchange_start,
                        'exchange_end' => $exchange_end,
                        'icon_url' => 'http://img3.anzhi.com'.$fileicon['iconurl_125']
                    );
                    push_gift_msg($push_data);

					update_soft_status(array('gift_status' => $gift_status), $apply_pkg);
                    $this->success("审核成功");
                } else {
                    $this->error("写入文件错误");
                }
            } else if (!empty($change_status) && $change_status == 4) {
                //审核驳回
                $apply_pkg = trim($_POST['apply_pkg']);
                $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->field('softid,softname')->find();
                $data['status'] = 4;
                $data['rand_status'] = 1;
                $data['update_tm'] = time();
                $data['reject_reason'] = empty($_POST['reject_reason']) ? "" : $_POST['reject_reason'];
                $active_model->table('sendnum_tmp')->where('id = "' . $id . '"')->save($data);
                //前台提醒
                $tm = date("Y-m-d H:i:s", time());
                $search = array("softname", "giftname", "tm", "msg");
                $replace = array($pkg_result['softname'], $_POST['active_name'], $tm, $data['reject_reason']);
                $msg = str_replace($search, $replace, $config_txt['gift_reject']);
                $emailmodel->dev_remind_add($_POST['dev_id'], $msg);
                //发送邮件提醒
                $model = new Model();
                $dever = $model->table('pu_developer')->where("dev_id={$_POST['dev_id']}")->field('dev_id,email,dev_name')->find();
                $subject = $config_txt['gift_reject_subject'];
                $search2 = array("devname", "softname", "giftname", "tm", "msg");
                $replace2 = array($dever['dev_name'], $pkg_result['softname'], $_POST['active_name'], $tm, $data['reject_reason']);
                $email_cont = str_replace($search2, $replace2, $config_txt['gift_reject_txt']);
                $emailmodel->realsend($dever['email'], $dever['dev_name'], $subject, $email_cont);
                $this->writelog("驳回了礼包名为" . $_POST['active_name'] . "的礼包",'sendnum_tmp',"{$id}", __ACTION__ ,"","edit");
                update_soft_status(array('gift_status' => 1), $apply_pkg);
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/verify_gift_list');
                $this->success("驳回成功");
            } else {
                $this->error("审核失败");
            }
        }
    }

    public function file_num() {
        $code_file = $_FILES;
        if ($code_file['activation_code']['name'] == '') {
            echo '{"err":"1","error_con":"请上传激活码文件"}';
            return false;
        }
        if ($code_file['activation_code']['size'] == 0) {
            echo '{"err":"2","error_con":"激活码文件不能为空"}';
            return false;
        }
        $file_type = explode('.', $code_file['activation_code']['name']);

        if ($file_type[1] != 'csv') {
            echo '{"err":"3","error_con":"请上传csv格式文件"}';
            return false;
        }

        $text = $_FILES['activation_code']['tmp_name'];
		
        $time = md5(time() + rand(1, 999));
        $file_str = "/tmp/$time.csv";
        $move = move_uploaded_file($text, $file_str);
        $read_name = $file_str;

        $shili = fopen($read_name, "r");
        while (!feof($shili)) {
            $shi = fgets($shili, 1024);

            $a = explode(',', $shi);

            if ($a[1]) {
				echo '{"err":"4","error_con":"激活码文件格式错误"}';
				return false;
            }
            $str .= $shi . ",";
        }

        $str_arr = str_replace("\r\n", '', $str);

        $str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);

        $code_arrs = explode(',', $str_arrs);

        foreach ($code_arrs as $key => $val) {
            if (trim($val) != "") {
                $code_arr[$key] = $val;
            }
        }

        $count = count($code_arr);

        echo '{"out_count":"' . $count . '","new_file":"' . $file_str . '","new_file_name":"' . $time . '.csv"}';
    }
	//v6.2 新增上传到某个地方并且可以下载激活码的代码不需要了
	/*public function file_new_server_num() {
        $code_file = $_FILES;

        if ($code_file['activation_code']['name'] == '') {
            echo '{"err":"1","error_con":"请上传激活码文件"}';
            return false;
        }
        if ($code_file['activation_code']['size'] == 0) {
            echo '{"err":"2","error_con":"激活码文件不能为空"}';
            return false;
        }
        $file_type = explode('.', $code_file['activation_code']['name']);

        if ($file_type[1] != 'csv') {
            echo '{"err":"3","error_con":"请上传csv格式文件"}';
            return false;
        }

        $text = $_FILES['activation_code']['tmp_name'];
		$time = md5(time() + rand(1, 999));
		
		//存放在某一个地方
		$path=date("/Ym/d/",time());
		$save_dir = C("NEW_SERVER_CSV").$path;
		$this->mkDirs($save_dir);
		$save_name = $time . '_' . $_FILES['activation_code']['name'];
		$save_file_name = $save_dir . $save_name;
		$db_save=$path.$save_name;
		
        //$file_str = "/tmp/$time.csv";
        $move = move_uploaded_file($text, $save_file_name);
       //$read_name = $file_str;
	    $read_name = $save_file_name;
        $shili = fopen($read_name, "r");
        while (!feof($shili)) {
            $shi = fgets($shili, 1024);

            $a = explode(',', $shi);

            if ($a[1]) {
				echo '{"err":"4","error_con":"激活码文件格式错误"}';
				return false;
            }
            $str .= $shi . ",";
        }

        $str_arr = str_replace("\r\n", '', $str);

        $str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);

        $code_arrs = explode(',', $str_arrs);

        foreach ($code_arrs as $key => $val) {
            if (trim($val) != "") {
                $code_arr[$key] = $val;
            }
        }

        $count = count($code_arr);

        echo '{"out_count":"' . $count . '","new_file":"' . $db_save . '","new_file_name":"' . $time . '.csv"}';
    }
	public function down_load_csv()
	{
		$file_path = base64_decode($_GET['file_path']);
		$file_dir = C("NEW_SERVER_CSV").$file_path;
		$file_name_arr = explode('/',$file_path);
		$count = count($file_name_arr);
		$file_name = $file_name_arr[$count-1];
		 if (file_exists($file_dir)) 
		{
            $file = fopen($file_dir,"r");
			
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . urlencode($file_name ));
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit(0);
        } 
		else 
		{
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        }
	}*/
	
    public function claim_upload($files, $files_type) {
        $vals = array(
            'do' => 'claim',
            'static_data' => '/data/att/m.goapk.com',
        );
        return _http_post(array_merge($vals, $files, $files_type));
    }

    //活动礼包添加提交
    function active_add_submit() {
        $model = new Model();
        $active_name = $_POST['active_name'];

        if (!$active_name) {
            $this->error("活动名称不能为空");
        }
        if (strlen($active_name) > 30) {
            $this->error("请输入30个字以内的名称");
        }
        $usable = $_POST['usable'];
        if (!$usable) {
            $this->error("使用范围不能为空");
        }
        if (strlen($usable) > 30) {
            $this->error("请输入30个字以内的使用范围");
        }
        $apply_pkg = $_POST['apply_pkg'];
        $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->select();
        if (!$pkg_result) {
            $this->error("应用包名不存在");
        }
        $cut_tm = strtotime($_POST['cut_tm']);
        $start_tm = strtotime($_POST['start_tm']);

        if ($cut_tm < $start_tm) {
            $this->error("开始时间不能大于结束时间");
        }
        $intro = trim($_POST['intro']);
        if (strlen($intro) > 150) {
            $this->error("简介不能超过50个汉字");
        }
        $limit_num = $_POST['limit_num'];
        if ($limit_num == '') {
            $this->error("每日发放限制不能为空");
        }
        $code_file = $_FILES['activation_code'];
        //var_dump($code_file);
        if (!$code_file) {
            $this->error("请上传激活码文件");
        }
        if ($code_file['size'] == 0) {
            $this->error("激活码文件不能为空");
        }
        $file_type = explode('.', $code_file['name']);

        if ($file_type[1] != 'csv') {
            $this->error("请上传csv格式文件");
        }
        if ($file_type[1] == 'csv') {
            $file_handle = fopen($code_file['tmp_name'], "r");
            $shili = fopen($code_file['tmp_name'], "r");  //打开文本

            while (!feof($shili)) {      //判断是否到了文件最后的函数
                $shi = fgets($shili, 1024);    //读取其中的数据
                $a = explode(',', $shi);
                if ($a[1]) {
                    $this->error("激活码文件格式错误");
                }
                $str .= $shi . ",";
            }
        }


        $str_arr = str_replace("\r\n", '', $str);

        $str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);
        $code_arrs = explode(',', $str_arrs);
        foreach ($code_arrs as $key => $val) {
            if ($val) {
                $code_arr[$key] = $val;
            }
        }


        $all_num = count($code_arr);
        if ($limit_num > $all_num) {
            $this->error("每日限制数不能大于激活码总数");
        }
        if ($limit_num) {
            $limit_num = intval(strval($limit_num));
            if ($limit_num <= 0 || !is_int($limit_num)) {
                $this->error("请输入正确格式的限制数");
            }
        }


        foreach ($code_arr as $key => $val) {

            if (strlen($val) > 25) {
                $this->error("激活码长度不能大于25位");
            }
        }
        $code_arr_unique = array_unique($code_arr);
        if (preg_match("/[\x80-\xff]./", $str_arrs)) {
            $this->error("激活码文件中不可有中文");
        }
        if (count($code_arr) > count($code_arr_unique)) {
            $repeat_arr = array_diff_assoc($code_arr, $code_arr_unique);
            foreach ($repeat_arr as $key => $val) {
                $repeat_str .= $val . ',';
                $this->error("激活码文件中含有重复数据:{$val}");
            }
        }

        $module_content = $_POST['editor_content'];
        if (empty($module_content) || $module_content == "<p>
	&nbsp;
</p>") {
            $this->error("编辑内容不能为空");
        }
        $_POST['editor_content'] = stripcslashes($_POST['editor_content']);
        preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $_POST['editor_content'], $matches);

        //preg_match_all("/<a.+?href=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u",$_POST['editor_content'],$matches_a);

        $pre_path = $_SERVER['DOCUMENT_ROOT'];

        foreach ($matches[1] as $key => $val) {
            $files_name[$key] = str_replace('.', '', microtime(true)) . '_' . OnlinegameAction::rand_code(8);
        }
        foreach ($matches[1] as $key => $val) {
            $files[$files_name[$key]] = curl_file_create($pre_path . $val);
        }
        $arr = OnlinegameAction::dev_upload($files);
        $nurl = IMGATT_HOST;
        if ($arr['ret']) {
            foreach ($arr['ret'] as $key => $val) {
                unset($k, $new_k);
                $k = array_search($key, $files_name);
                $new_k = $matches[1][$k];
                $new_arr[$new_k] = $nurl . $val;
            }
            //文章内容中图片路径替换
            $_POST['editor_content'] = strtr($_POST['editor_content'], $new_arr);
        }

        //数据写入
        $data_active['active_name'] = $active_name;

        $data_active['active_type'] = 1;
        $data_active['conf_cnt'] = $limit_num;
        $data_active['status'] = 1;
        $data_active['end_tm'] = strtotime($_POST['cut_tm']);
        $data_active['start_tm'] = strtotime($_POST['start_tm']);
        $data_active['num_cnt'] = $all_num;
        $data_active['used_cnt'] = 0;
        $data_active['creater'] = $_SESSION['admin']['admin_user_name'];
        $data_active['creater_id'] = $_SESSION['admin']['admin_id'];
        $data_active['active_from'] = 2;
        $data['apply_pkg'] = $apply_pkg;
        $data['usable'] = $usable;
        $data['module_content'] = htmlspecialchars($_POST['editor_content']);
        $data['create_tm'] = time();
        $data['update_tm'] = time();
        $data['status'] = 1;
        $active_model = D('sendNum.sendNum');
        $data['rank'] = 1;
        $data['intro'] = htmlspecialchars($_POST['intro']);
        $data['be_limit_num'] = $limit_num;
        $data['be_active_name'] = $active_name;
        $data['be_apply_pkg'] = $apply_pkg;
        $data['be_usable'] = $usable;
        $data['be_cut_tm'] = strtotime(date('Y-m-d H:i:s', strtotime($_POST['cut_tm'])));
        $data['active_type'] = 2;
        // $have_result = $active_model->table('sendnum_active')->where("active_name = '{$active_name}' and status != 0")->select();

        // if ($have_result) {
            // $this->error("对不起，活动名称不能相同");
        // }
        $result = $active_model->add_active($data_active);
        $data['gift_url'] = "/olgame_gift_preview_{$result}.html";
        if ($result) {
            $data['active_id'] = $result;
            $active_result = $active_model->add_active_content($data);
            /* $been_where['_string'] = "status = 1 and rank != 0 and active_id != {$result}";
              $have_been = $active_model -> table('olgame_active') -> where($been_where) -> select(); */

            $query = "select a.id,a.rank from olgame_active as a left join sendnum_active as b on a.active_id = b.id where b.active_from = 2 and b.status = 1 and a.rank != 0 and a.active_id != {$result}";
            $have_been = $active_model->query($query);

            foreach ($have_been as $key => $val) {
                $rank_result = $active_model->table('olgame_active')->where(array('id' => $val['id']))->save(array('rank' => $val['rank'] + 1));
            }

            //激活码入口
            foreach ($code_arr as $key => $val) {
                if ($val) {
                    $data_code['active_id'] = $result;
                    $data_code['active_num'] = $val;
                    $data_code['status'] = 0;
                    $code_result = $active_model->add_active_num($data_code);
                }
            }
        }

        $static_file = $this->active_module_url;
        $static_file_true = $this->active_module_true_url;
        //$a = "<a href='javascript:window.AnzhiActivitys.downloadForActivity('{$pkg_result}')' class='down_btn' title='安智市场下载'>开始下载</a>";
        $js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
        preg_match("/href=\"(.*)\"/Ui", $module_content, $my_a);
        $package = $my_a[1];
        $module_contents = str_replace($package, "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)", $module_content);
        //生成静态页面
        ob_start();
        echo $this->header;
        echo $js_a;
        echo $module_contents;
        $temp = ob_get_contents();
        ob_end_clean();
        if (!file_exists($static_file)) {
            @mkdir(rtrim($static_file, '/'), 0777, true) or die("创建目录失败");
        }
        $fp = fopen($static_file . "/olgame_gift_preview_{$result}.html", 'w');
        $create_result = fwrite($fp, $temp);
        fclose($fp);
        if (!file_exists($static_file_true)) {
            @mkdir(rtrim($static_file_true, '/'), 0777, true) or die("创建目录失败");
        }
        if ($create_result) {
            if (file_exists($static_file_true . "/olgame_gift_preview_{$result}.html")) {
                unlink($static_file_true . "/olgame_gift_preview_{$result}.html");
            }
            if (!@copy($static_file . "/olgame_gift_preview_{$result}.html", $static_file_true . "/olgame_gift_preview_{$result}.html")) {
                $this->error("移动文件错误");
            }  //删除以前的文件并把新的html移动到指定位置
        } else {
            $this->error("写入文件错误");
        }

        if ($result) {
            $this->writelog("已添加id为{$result}的活动礼包",'sendnum_active',"{$result}", __ACTION__ ,"","add");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list/from/2');
            $this->success("添加成功");
        }
    }

    //图片处理,代码来源:/dev.goapk.com/common.php
    //上传图片
    public static function dev_upload($files) {
        $vals = array(
            'do' => 'save',
            'static_data' => '/data/att/m.goapk.com',
        );
        return OnlinegameAction::_http_post(array_merge($vals, $files));
    }

    //摘自tools/ClsFactory.php中http_post函数
    public static function _http_post($vals) {
        $pro_env = C('PRO_ENV');
        if($pro_env == 1){
            //线上
            $host = '192.168.1.18';
            $host_dam = 'Host: dummy.goapk.com';
        }else if($pro_env == 2){
            $host = 'dummy.goapk.com';
            $host_dam = 'Host: dummy.goapk.com';
        }else if($pro_env == 3||$pro_env == 4){
            $host = '192.168.0.99';
            $host_dam = 'Host: 9.dummy.goapk.com';
        }
        $res = curl_init();
        curl_setopt($res, CURLOPT_URL, "http://${host}/service_dev.php");
        curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
        $result = curl_exec($res);

        $info = curl_getinfo($res);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);

        return array('ret' => json_decode($result, true), 'info' => $info, 'errno' => $errno, 'error' => $error);
    }

    public static function rand_code($num) {
        $str = '';
        for ($i = 0; $i < $num; $i++) {
            $str .= mt_rand(0, 9);
        }
        return $str;
    }

    //活动礼包编辑显示
    function active_edit_show() {
        $id = $_GET['id'];
        $active_model = D('sendNum.sendNum');
        $result = $active_model->table('olgame_active')->where("active_id = {$id} and status != 0")->select();
        $active_result = $active_model->table('sendnum_active')->where("id => {$id} and status != 0")->select();
        foreach ($result as $key => $val) {
            $active_where['id'] = $val['active_id'];
            $active_result = $active_model->table('sendnum_active')->where($active_where)->select();
            $val['cut_tm'] = $active_result[0]['end_tm'];
            $val['start_tm'] = $active_result[0]['start_tm'];
            $val['all_num'] = $active_result[0]['num_cnt'];
            $else_result = $active_model->table('sendnum_number')->where(array('active_id' => $val['active_id'], 'status' => 1))->select();
            $val['surplus_num'] = count($else_result);
            $val['limit_num'] = $active_result[0]['conf_cnt'];
            $val['active_name'] = $active_result[0]['active_name'];

            $result[$key] = $val;
        }

        $this->assign("result", $result);
        $this->display();
    }

    //活动礼包编辑提交
    function active_edit_submit() {
        $active_model = D('sendNum.sendNum');
        $model = new Model();
        $active_name = $_POST['active_name'];
        $id = $_POST['id'];
        $usable = $_POST['usable'];
        if (!$active_name) {
            $this->error("活动名称不能为空");
        }
        if (strlen($active_name) > 30) {
            $this->error("请输入30个字以内的名称");
        }
        if (!$usable) {
            $this->error("使用范围不能为空");
        }
        if (strlen($usable) > 30) {
            $this->error("请输入30个字以内的使用范围");
        }

        $apply_pkg = $_POST['apply_pkg'];
        $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->select();
        if (!$pkg_result) {
            $this->error("应用包名不存在");
        }
        $cut_tm = strtotime($_POST['cut_tm']);
        $start_tm = strtotime($_POST['start_tm']);
        if ($cut_tm < $start_tm) {
            $this->error("截止时间不可小于开始时间");
        }
        $intro = trim($_POST['intro']);

        if (strlen($intro) > 150) {
            $this->error("简介不能大于50个汉字");
        }
        $limit_num = $_POST['limit_num'];
        if ($limit_num) {
            $limit_num = intval(strval($limit_num));
            if ($limit_num <= 0 || !is_int($limit_num)) {
                $this->error("请输入正确格式的限制数");
            }
        }
        $been_num = $active_model->table('sendnum_number')->where(array('active_id' => $id, 'status' => 0))->select();
        if ($limit_num > count($been_num)) {
            $this->error("每日限制数不能大于剩余激活码总数");
        }
        $module_content = $_POST['editor_content'];

        if (empty($module_content) || $module_content == "<p>
	&nbsp;
</p>") {
            $this->error("编辑内容不能为空");
        }

        $_POST['editor_content'] = stripcslashes($_POST['editor_content']);
        preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $_POST['editor_content'], $matches);

        $pre_path = $_SERVER['DOCUMENT_ROOT'];

        foreach ($matches[1] as $key => $val) {
            $files_name[$key] = str_replace('.', '', microtime(true)) . '_' . OnlinegameAction::rand_code(8);
        }
        foreach ($matches[1] as $key => $val) {
            $files[$files_name[$key]] = curl_file_create($pre_path . $val);
        }
        $arr = OnlinegameAction::dev_upload($files);
        $nurl = IMGATT_HOST;
        if ($arr['ret']) {
            foreach ($arr['ret'] as $key => $val) {
                unset($k, $new_k);
                $k = array_search($key, $files_name);
                $new_k = $matches[1][$k];
                $new_arr[$new_k] = $nurl . $val;
            }
            //文章内容中图片路径替换
            $_POST['editor_content'] = strtr($_POST['editor_content'], $new_arr);
        }
        $been_result = $active_model->table('olgame_active')->where(array('active_id' => $id))->select();
        $been_code = $active_model->table('sendnum_active')->where(array('id' => $id))->select();
        //数据写入
        $data_active['active_name'] = $active_name;
        $data_active['conf_cnt'] = $limit_num;
        $data_active['end_tm'] = strtotime($_POST['cut_tm']);
        $data_active['start_tm'] = strtotime($_POST['start_tm']);
        if ($data_active['end_tm'] > time() && $been_code[0]['end_tm'] < time()) {
            $where_my['_string'] = "rank > 0 and status = 1";
            $my_result = $active_model->table('olgame_active')->where($where_my)->select();
            foreach ($my_result as $key => $val) {
                $where_self['id'] = $val['id'];
                $data_self['rank'] = $val['rank'] + 1;
                $self_result = $active_model->update_active_content($where_self, $data_self);
            }
            $data['rank'] = 1;
        }
        //$data_active['day_surplus_num'] = $limit_num - ($been_code[0]['limit_num'] - $been_code[0]['day_surplus_num']);
        $where_active['_string'] = "id = {$id} and status != 0";
        // $have_result = $active_model->table('sendnum_active')->where("active_name = '{$active_name}' and status != 0 and id != {$id}")->select();

        // if ($have_result) {
            // $this->error("对不起，活动名称不能相同");
        // }
        $log_result = $this->logcheck(array('id' => $id), 'sendnum_active', $data_active, $active_model);
        $active_result = $active_model->active_save($where_active, $data_active);

        $data['be_limit_num'] = $been_code[0]['conf_cnt'];
        $data['be_active_name'] = $been_code[0]['active_name'];
        $data['be_apply_pkg'] = $been_result[0]['apply_pkg'];
        $data['be_usable'] = $been_result[0]['usable'];
        $data['be_cut_tm'] = $been_code[0]['end_tm'];
        $data['apply_pkg'] = $apply_pkg;
        $data['usable'] = $usable;
        $data['intro'] = htmlspecialchars($intro);
        $data['module_content'] = htmlspecialchars($_POST['editor_content']);
        $data['update_tm'] = time();
        $where_content['_string'] = "active_id = {$id} and status != 0";
        $log_result_content = $this->logcheck(array('active_id' => $id), 'olgame_active', $data, $active_model);
        $content_result = $active_model->update_active_content($where_content, $data);

        $static_file = $this->active_module_url;
        $static_file_true = $this->active_module_true_url;
        $js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
        preg_match("/href=\"(.*)\"/Ui", $module_content, $my_a);
        $package = $my_a[1];
        $module_contents = str_replace($package, "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)", $module_content);
        //生成静态页面
        ob_start();
        echo $this->header;
        echo $js_a;
        echo $module_contents;
        $temp = ob_get_contents();
        ob_end_clean();
        if (!file_exists($static_file)) {
            @mkdir(rtrim($static_file, '/'), 0777, true) or die("创建目录失败");
        }
        $fp = fopen($static_file . "/olgame_gift_preview_{$id}.html", 'w');
        $create_result = fwrite($fp, $temp);
        fclose($fp);
        if (!file_exists($static_file_true)) {
            @mkdir(rtrim($static_file_true, '/'), 0777, true) or die("创建目录失败");
        }
        if (!$create_result) {
            $this->error("写入文件错误");
        }
        $been_result = $active_model->table('sendnum_active')->where("id = {$id}")->select();
        $from = $been_result[0]['status'];

        if ($active_result || $content_result) {
            $this->writelog("网游_活动礼包_已编辑id为{$id}的活动礼包" . $log_result . $log_result_content,'sendnum_active',"{$id}", __ACTION__ ,"","edit");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Onlinegame/active_gift_list/from/{$from}");
            $this->success("编辑成功");
        } else {
            $this->error("编辑失败");
        }
    }

    //活动礼包删除
    function active_del() {
        $active_model = D('sendNum.sendNum');
        $id = $_GET['id'];
        $data_active['status'] = 0;
        $data['status'] = 0;
        $data['rank'] = 0;
        $data['update_tm'] = time();
        $where['id'] = $id;
        $where_content['active_id'] = $id;
        $been_result = $active_model->table('sendnum_active')->where(array('id' => $id))->select();
        $change_result = $active_model->table('olgame_active')->where(array('active_id' => $id))->select();
        if ($been_result[0]['status'] == 1) {
            $all_result = $active_model->table('olgame_active')->where("status = 1 and rank > {$change_result[0]['rank']}")->select();
            foreach ($all_result as $key => $val) {
                $where_change['id'] = $val['id'];
                $data_change['rank'] = $val['rank'] - 1;
                $result = $active_model->update_active_content($where_change, $data_change);
            }
        }
        $from = $been_result[0]['status'];
        $result_active = $active_model->active_save($where, $data_active);
        $result_content = $active_model->update_active_content($where_content, $data);

        if ($result_active && $result_content) {
            $this->writelog("网游_活动礼包_已删除id为{$id}的活动礼包",'sendnum_active',"{$id}", __ACTION__ ,"","del");
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . "/Onlinegame/active_gift_list/from/{$from}");
            $this->success("删除成功");
        }
    }

    //活动礼包位置上移/下移
    function gift_rank_change() {
        $active_model = D('sendNum.sendNum');
        $change = $_GET['change'];
        $id = $_GET['id'];
        $rank_result = $active_model->table('olgame_active')->where(array('active_id' => $id, 'active_type' => 2))->select();
        if ($change == 'up') {
            $where['rank'] = $rank_result[0]['rank'] - 1;
            $where['active_type'] = 2;
            $data['rank'] = $rank_result[0]['rank'];
            $than_result = $active_model->update_active_content($where, $data);
            $where_self['active_id'] = $id;
            $where_self['active_type'] = 2;
            $data_self['rank'] = $rank_result[0]['rank'] - 1;
            $self_result = $active_model->update_active_content($where_self, $data_self);
            $rank_now = $rank_result[0]['rank'] - 1;
            if ($than_result && $self_result) {
                $this->writelog("已修改id为{$id}的活动礼包位置为{$rank_now}",'olgame_active',"{$id}", __ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list');
                $this->success("操作成功");
            }
        } elseif ($change == 'down') {
            $where['rank'] = $rank_result[0]['rank'] + 1;
            $where['active_type'] = 2;
            $data['rank'] = $rank_result[0]['rank'];
            $than_result = $active_model->update_active_content($where, $data);
            $where_self['active_id'] = $id;
            $where_self['active_type'] = 2;
            $data_self['rank'] = $rank_result[0]['rank'] + 1;
            $self_result = $active_model->update_active_content($where_self, $data_self);
            $rank_now = $rank_result[0]['rank'] + 1;
            if ($than_result && $self_result) {
                $this->writelog("已修改id为{$id}的活动礼包位置为{$rank_now}",'olgame_active',"{$id}", __ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list');
                $this->success("操作成功");
            }
        }
    }

    //活动礼包预览
    function gift_profile() {
        $id = $_GET['id'];
        $active_model = D('sendNum.sendNum');
        $result = $active_model->table('olgame_active')->where(array('active_id' => $id))->select();
        $content = $result[0]['module_content'];
        $content = htmlspecialchars_decode($content);
        $this->assign("profile", $content);
        $this->display("");
    }

    //活动礼包发布/重新发布/取消发布
    function active_release() {
        $active_model = D('sendNum.sendNum');
        $id = $_GET['id'];
        $been_result = $active_model->table('olgame_active')->where(array('active_id' => $id))->select();
        $been_active = $active_model->table('sendnum_active')->where(array('id' => $id))->select();
        if ($_GET['cancel']) {  //取消发布
            $data['status'] = 2;
            $rank_result = $active_model->table('olgame_active')->where("rank > {$been_result[0]['rank']} and status = 1")->select();
            foreach ($rank_result as $key => $val) {
                $data_rank['rank'] = $val['rank'] - 1;
                $where_rank['active_id'] = $val['active_id'];
                $change_result = $active_model->update_active_content($where_rank, $data_rank);
            }
            $where['id'] = $id;
            $result = $active_model->active_save($where, $data);
            if ($result) {
                $this->writelog("已取消id为{$id}的活动礼包的发布",'sendnum_active',"{$id}", __ACTION__ ,"","del");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list/from/2');
                $this->success("取消成功");
            }
        } else {
            if ($been_active[0]['status'] == 2) {  //发布
                $data_active['status'] = 1;
                $data_active['start_tm'] = time();
                $been_result = $active_model->table('olgame_active')->where(array('active_id' => $id))->select();
                $rank_result = $active_model->table('sendnum_active')->where("status =1 and active_from = 2 and end_tm > " . time() . "")->select();
                foreach ($rank_result as $key => $val) {
                    $change_result = $active_model->table('olgame_active')->where(array("active_id" => $val['id']))->select();
                    $where_rank['active_id'] = $val['id'];
                    $data_rank['rank'] = $change_result[0]['rank'] + 1;
                    $the_result = $active_model->update_active_content($where_rank, $data_rank);
                }
                $data['rank'] = 1;
                $js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
                preg_match("/href=\"(.*)\"/Ui", htmlspecialchars_decode($been_result[0]['module_content']), $my_a);
                $package = $my_a[1];
                $module_contents = str_replace($package, "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)", $been_result[0]['module_content']);

                //生成静态页面
                ob_start();
                echo $this->header;
                echo $js_a;
                echo '<style>body { font-size: 18px; line-height:32px}</style>';
                echo '<body>';
                echo htmlspecialchars_decode($module_contents);
                echo '</body>';
                $temp = ob_get_contents();
                ob_end_clean();
                $static_file_true = $this->active_module_true_url;
                $static_file = $this->active_module_url;
                if (!file_exists($static_file)) {
                    @mkdir(rtrim($static_file, '/'), 0777, true) or die("创建目录失败");
                }
                $fp = fopen($static_file . "/olgame_gift_preview_{$id}.html", 'w');
                $create_result = fwrite($fp, $temp);
                fclose($fp);
                if (!file_exists($static_file_true)) {
                    @mkdir(rtrim($static_file_true, '/'), 0777, true) or die("创建目录失败");
                }
                if ($create_result) {
                    if (file_exists($static_file_true . "/olgame_gift_preview_{$id}.html")) {
                        unlink($static_file_true . "/olgame_gift_preview_{$id}.html");
                    }
                    if (!@copy($static_file . "/olgame_gift_preview_{$id}.html", $static_file_true . "/olgame_gift_preview_{$id}.html")) {
                        $this->error("移动文件错误");
                    }  //删除以前的文件并把新的html移动到指定位置
                } else {
                    $this->error("写入文件错误");
                }
                $where['_string'] = "active_id = {$id} and status != 0";
                $data['gift_url'] = "/olgame_gift_preview_{$id}.html";
                $data_active['start_tm'] = time();
                $where_active['_string'] = "id = {$id} and status !=0 ";
                $result = $active_model->update_active_content($where, $data);

                $active_result = $active_model->active_save($where_active, $data_active);

                if ($result || $active_result) {
                    $this->writelog("已发布id为{$id}的活动礼包,活动页面为{$data['gift_url']}",'sendnum_active',"{$id}", __ACTION__ ,"","edit");
                    $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list');
                    $this->success("发布成功");
                } else {
                    $this->error("发布失败");
                }
            } elseif ($been_active[0]['status'] == 1) { // 重新发布
                $been_result = $active_model->table('olgame_active')->where(array('active_id' => $id))->select();
                $been_code = $active_model->table('sendnum_active')->where(array('id' => $id))->select();
                $data['rerelease_tm'] = time();
                $data['be_limit_num'] = $been_code[0]['conf_cnt'];
                $data['be_active_name'] = $been_code[0]['active_name'];
                $data['be_apply_pkg'] = $been_result[0]['apply_pkg'];
                $data['be_usable'] = $been_result[0]['usable'];
                $data['be_cut_tm'] = $been_code[0]['end_tm'];
                $where['active_id'] = $id;
                $my_result = $active_model->update_active_content($where, $data);

                $static_file = $this->active_module_url;
                $static_file_true = $this->active_module_true_url;
                $js_a = "<script>function onDownloadCreated(id) {alert('软件 ' + {$pkg_result} + ' 的下载任务已经创建');}</script>";
                preg_match("/href=\"(.*)\"/Ui", htmlspecialchars_decode($been_result[0]['module_content']), $my_a);
                $package = $my_a[1];
                $module_contents = str_replace($package, "javascript:window.AnzhiActivitys.downloadForActivity('{$package}',1)", $been_result[0]['module_content']);
                //生成静态页面
                ob_start();
                echo $this->header;
                echo $js_a;
                echo '<style>body { font-size: 18px; line-height:32px}</style>';
                echo '<body>';
                echo htmlspecialchars_decode($module_contents);
                echo '</body>';
                $temp = ob_get_contents();
                ob_end_clean();
                if (!file_exists($static_file)) {
                    @mkdir(rtrim($static_file, '/'), 0777, true) or die("创建目录失败");
                }
                $fp = fopen($static_file . "/olgame_gift_preview_{$id}.html", 'w');
                $create_result = fwrite($fp, $temp);
                fclose($fp);
                if (!file_exists($static_file_true)) {
                    @mkdir(rtrim($static_file_true, '/'), 0777, true) or die("创建目录失败");
                }
                if ($create_result) {
                    if (file_exists($static_file_true . "/olgame_gift_preview_{$id}.html")) {
                        unlink($static_file_true . "/olgame_gift_preview_{$id}.html");
                    }
                    if (!@copy($static_file . "/olgame_gift_preview_{$id}.html", $static_file_true . "/olgame_gift_preview_{$id}.html")) {
                        $this->error("移动文件错误");
                    }  //删除以前的文件并把新的html移动到指定位置
                    $this->writelog("已重新发布id为{$id}的活动礼包",'sendnum_active',"{$id}", __ACTION__ ,"","edit");
                    $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list');
                    $this->success("重新发布成功");
                } else {
                    $this->error("写入文件错误");
                }
            } else {
                $this->error("操作失败");
            }
        }
    }

    //活动礼包排序置顶
    function gift_stick() {
        $active_model = D('sendNum.sendNum');
        $id = $_GET['id'];
        $result_rank = $active_model->table('olgame_active')->where(array('active_id' => $id, 'active_type' => 2))->select();
        if ($result_rank[0]['rank'] == 1) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list');
            $this->success("置顶成功");
        } else {
            $where['_string'] = "status = 1 and rank < {$result_rank[0]['rank']} and rank > 0 and active_type = 2";
            $all_result = $active_model->table('olgame_active')->where($where)->select();
            foreach ($all_result as $key => $val) {
                $where_change['_string'] = "id = {$val['id']} and active_type = 2";
                $data_change['rank'] = $val['rank'] + 1;
                $change_result = $active_model->update_active_content($where_change, $data_change);
            }
            $my_where['_string'] = "active_id = {$id} and status = 1 and active_type = 2";
            $my_data['rank'] = 1;
            $my_result = $active_model->update_active_content($my_where, $my_data);
            if ($my_result) {
                $this->writelog("已置顶id为{$id}的活动礼包",'sendnum_active',"{$id}", __ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/active_gift_list');
                $this->success("置顶成功");
            }
        }
    }

    //新闻资讯列表
    function game_news_list() {
        $model = new Model();
        //$result = $model -> table('sj_olgame_news') -> where("status != 0") -> select();
        $count_result = $model->table('sj_olgame_news')->where(array('status' => 2))->order('rank')->select();
        $no_result = $model->table('sj_olgame_news')->where(array('status' => 1))->order('create_tm DESC')->select();
        foreach ($no_result as $key => $val) {
            $val['num'] = $key + 1;
            $no_result[$key] = $val;
        }
        $from = $_GET['from'];
        $count = count($count_result);
        $this->assign("from", $from);
        $this->assign("count", $count);
        $this->assign("been_result", $count_result);
        $this->assign("no_result", $no_result);
        $this->display();
    }

    // 新：未发布资讯列表
    function game_news_unreleased_list() {
        $model = new Model();
        $where = array(
            'status' => 1,
        );
        // trim一下
        foreach ($_GET as $key => $value) {
            $_GET[$key] = trim($value);
        }
        // 记录页数参数
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }

        // 搜索软件名称
        if ($_GET['search_softname'] != '') {
            $where['softname'] = array('like', '%' . $_GET['search_softname'] . '%');
        }
        // 搜索资讯标题
        if ($_GET['search_news_name'] != '') {
            $where['news_name'] = array('like', '%' . $_GET['search_news_name'] . '%');
        }
        // 搜索资讯类型
        if ($_GET['search_info_type'] != '' && $_GET['search_info_type'] != -1) {
            $where['info_type'] = $_GET['search_info_type'];
        }
		// 搜索软件包名
        if ($_GET['search_pckname'] != '') {
            $where['apply_pkg'] = array('eq', $_GET['search_pckname']);
        }
		// 搜索采集站点
        if ($_GET['search_website'] != '') {
            $where['website_name'] = array('like', '%' . $_GET['search_website'] . '%');
        }
		
        $count = $model->table('sj_olgame_news')->where($where)->order('create_tm DESC')->count();
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show(); //分页显示输出
        $list = $model->field('id, news_name, news_pic, softname, website_name, author, catch_tm, create_tm, info_type,apply_pkg')->table('sj_olgame_news')->where($where)->order('create_tm DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
        // 搜索内容
        $this->assign('search_softname', $_GET['search_softname']);
        $this->assign('search_news_name', $_GET['search_news_name']);
        $this->assign('search_info_type', $_GET['search_info_type']);
		$this->assign('search_pckname', $_GET['search_pckname']);
        $this->assign('search_website', $_GET['search_website']);

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('apkurl', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('function_name', __FUNCTION__);
        $this->assign('url_param', $url_param);
        $this->display();
    }

    // 新：已发布资讯列表
    function game_news_released_list() {
        $model = new Model();
        $where = array(
            'status' => 2,
        );
		//判断是否是推荐列表
		if($_GET['recommend'] == 1){
			$where['recommend_status'] =1;
		};
		//print_r($where);
        // trim一下
        foreach ($_GET as $key => $value) {
            $_GET[$key] = trim($value);
        }
        // 记录页数参数
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
        // 搜索软件名称
        if ($_GET['search_softname'] != '') {
            $where['softname'] = array('like', '%' . $_GET['search_softname'] . '%');
        }
        // 搜索资讯标题
        if ($_GET['search_news_name'] != '') {
            $where['news_name'] = array('like', '%' . $_GET['search_news_name'] . '%');
        }
        // 搜索资讯类型
        if ($_GET['search_info_type'] != '' && $_GET['search_info_type'] != -1) {
            $where['info_type'] = $_GET['search_info_type'];
        }
		// 搜索软件包名
        if ($_GET['search_pckname'] != '') {
            $where['apply_pkg'] = array('eq', $_GET['search_pckname']);
        }
		// 搜索采集站点
        if ($_GET['search_website'] != '') {
            $where['website_name'] = array('like', '%' . $_GET['search_website'] . '%');
        }
        $count = $model->table('sj_olgame_news')->where($where)->order('create_tm DESC')->count();
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show(); //分页显示输出
		
		//判断是否是推荐列表,如果是按照recommend_rank排序
		if($_GET['recommend'] == 1){
			$list = $model->field('id, news_name, news_pic, softname, website_name, author, catch_tm, create_tm, info_type, rank, update_tm, release_tm,apply_pkg,recommend_status,recommend_rank, recommend_update_time')->table('sj_olgame_news')->where($where)->order('recommend_rank asc,recommend_update_time desc')->limit($page->firstRow . ',' . $page->listRows)->select();
			//echo $model->getLastSql();exit;
		}else{
			$list = $model->field('id, news_name, news_pic, softname, website_name, author, catch_tm, create_tm, info_type, rank, update_tm, release_tm,apply_pkg,recommend_status,recommend_rank,recommend_update_time')->table('sj_olgame_news')->where($where)->order('rank asc')->limit($page->firstRow . ',' . $page->listRows)->select();
		};
        
        // 搜索内容
        $this->assign('search_softname', $_GET['search_softname']);
        $this->assign('search_news_name', $_GET['search_news_name']);
        $this->assign('search_info_type', $_GET['search_info_type']);
		$this->assign('search_pckname', $_GET['search_pckname']);
        $this->assign('search_website', $_GET['search_website']);

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('apkurl', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('function_name', __FUNCTION__);
        $this->assign('url_param', $url_param);
        $this->display();
    }

    //添加资讯显示
    function news_add_show() {
        $this->assign('function_name', $_GET['from']);
        // 记录页数参数，方便跳回第几页
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($key == 'id' || $key == 'from')
                continue;
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
        $this->assign('url_param', $url_param);
        $this->display();
    }

    //添加资讯提交
    function news_add_submit() {
        $model = new Model();
        $news_name = $_POST['news_name'];
        if (!$news_name) {
            $this->error("资讯名称不能为空");
        }
        $author = $_POST['author'];
        $apply_pkg = $_POST['apply_pkg'];
        $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->select();
        if (!$pkg_result) {
            $this->error("应用包名不存在");
        }
        $news_pic = $_FILES['news_pic'];

        if ($news_pic['size']) {
            $high_wd = getimagesize($news_pic['tmp_name']);
            $widhig_hg = $high_wd[3];
            $wh_hg = explode(' ', $widhig_hg);
            $wh1_hg = $wh_hg[0];
            $widths_hg = explode('=', $wh1_hg);
            $width1_hg = substr($widths_hg[1], 0, -1);
            $width_go_hg = substr($width1_hg, 1);
            $hi1_hg = $wh_hg[1];
            $heights_hg = explode('=', $hi1_hg);
            $height1_hg = substr($heights_hg[1], 0, -1);
            $height_go_hg = substr($height1_hg, 1);
            if ($width_go_hg != 130 || $height_go_hg != 80) {
                //$this -> error("默认图片大小不符合条件");
            }
            preg_match("/\.(?:png|jpg|jpeg)$/i", $news_pic['name'], $matches);
            if (!$matches) {
                $this->error("上传图片类型错误！");
            }

            if ($news_pic['size'] > 35 * 1024) {
                $this->error("默认图片尺寸大于35K");
            }
            if (!$news_pic['size']) {
                $this->error("图片不能为空");
            }
            $config = array(
                'multi_config' => array(
                    'news_pic' => array(
                        'savepath' => UPLOAD_PATH . '/img/' . $path,
                        'saveRule' => 'getmsec'
                    ),
                ),
            );
            $list = $this->_uploadapk(0, $config);
            $news_url = $list['image'][0]['url'];
            $data['news_pic'] = $news_url;
            /* 这些字段目前没有用到
              $data['be_news_pic'] = $news_url;
             */
        } else {
            $this->error("资讯默认图片不能为空");
        }
        $news_content = $_POST['news_content'];
        if (!$news_content) {
            $this->error("简介内容不能为空");
        }
        if (mb_strlen($news_content, 'utf-8') > 40) {
            $this->error("简介内容不能大于40个字");
        }
        $module_content = $_POST['editor_content'];
        if (empty($module_content) || $module_content == "<p>
	&nbsp;
</p>") {
            $this->error("编辑内容不能为空");
        }
        $_POST['editor_content'] = stripcslashes($_POST['editor_content']);
        preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $_POST['editor_content'], $matches);
        $pre_path = $_SERVER['DOCUMENT_ROOT'];

        foreach ($matches[1] as $key => $val) {
            $files_name[$key] = str_replace('.', '', microtime(true)) . '_' . OnlinegameAction::rand_code(8);
        }
        foreach ($matches[1] as $key => $val) {
            $files[$files_name[$key]] = curl_file_create($pre_path . $val);
        }
        $arr = OnlinegameAction::dev_upload($files);
        if ($arr['ret']) {
            foreach ($arr['ret'] as $key => $val) {
                unset($k, $new_k);
                $k = array_search($key, $files_name);
                $new_k = $matches[1][$k];
                $new_arr[$new_k] = self::HOST_TAG . $val;
            }
            //文章内容中图片路径替换
            $_POST['editor_content'] = strtr($_POST['editor_content'], $new_arr);
        }
        $data['news_name'] = $news_name;
        $data['apply_pkg'] = $apply_pkg;
        $data['author'] = $author;
        $data['module_content'] = htmlspecialchars($_POST['editor_content']);
        $data['news_content'] = htmlspecialchars($news_content);
        $data['create_tm'] = time();
        $data['update_tm'] = time();
        $data['status'] = 1;
        /////////// new added data
        $data['softname'] = $_POST['softname'];
        $data['info_type'] = $_POST['info_type'];
        ///////////

        $result = $model->table('sj_olgame_news')->add($data);
        if ($result) {
            $this->writelog("已添加id为{$result}的新闻资讯",'sj_olgame_news',"{$result}", __ACTION__ ,"","add");
            //$this -> assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_news_list/from/1');
            $from = $_POST['from'];
            $this->assign('jumpUrl', "/index.php/Sj/Onlinegame/{$from}?{$_POST['url_param']}");
            $this->success("添加成功");
        }
    }

    //新闻资讯预览
    function news_profile() {
        $id = $_GET['id'];
        $model = new Model();
        $result = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
        $content = $result[0]['module_content'];
        $content = htmlspecialchars_decode($content);
        // 展示需要将图片host替换上去
        $content = str_replace(self::HOST_TAG, GAMEINFO_ATTACHMENT_HOST, $content);
        $this->assign("profile", $content);
        $this->display("");
    }

    //编辑资讯显示
    function news_edit_show() {
        $model = new Model();
        $id = $_GET['id'];
        $result = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
        $module_content = $result[0]['module_content'];
        // 展示需要将图片host换上去
        $module_content = htmlspecialchars_decode($module_content);
        $module_content = str_replace(self::HOST_TAG, GAMEINFO_ATTACHMENT_HOST, $module_content);
        $result[0]['module_content'] = $module_content;

        $this->assign("result", $result);
        $this->assign('attachment_host', GAMEINFO_ATTACHMENT_HOST);
        $this->assign('function_name', $_GET['from']);
        // 记录页数参数，方便跳回第几页
        $url_param = "";
        foreach ($_GET as $key => $value) {
            if ($key == 'id' || $key == 'from')
                continue;
            if ($url_param != '')
                $url_param .= "&";
            if ($value != '')
                $url_param .= "{$key}={$value}";
        }
        $this->assign('url_param', $url_param);
        $this->display();
    }

    //编辑资讯提交
    function news_edit_submit() {
        $model = new Model();
        $news_name = $_POST['news_name'];
        if (!$news_name) {
            $this->error("资讯名称不能为空");
        }

        $author = $_POST['author'];
        $apply_pkg = $_POST['apply_pkg'];
        $id = $_POST['id'];
        $pkg_result = $model->table('sj_soft')->where(array('package' => $apply_pkg))->select();
        if (!$pkg_result) {
            $this->error("应用包名不存在");
        }
        $news_pic = $_FILES['news_pic'];
        if ($news_pic['size']) {
            $high_wd = getimagesize($news_pic['tmp_name']);
            $widhig_hg = $high_wd[3];
            $wh_hg = explode(' ', $widhig_hg);
            $wh1_hg = $wh_hg[0];
            $widths_hg = explode('=', $wh1_hg);
            $width1_hg = substr($widths_hg[1], 0, -1);
            $width_go_hg = substr($width1_hg, 1);
            $hi1_hg = $wh_hg[1];
            $heights_hg = explode('=', $hi1_hg);
            $height1_hg = substr($heights_hg[1], 0, -1);
            $height_go_hg = substr($height1_hg, 1);
            if ($width_go_hg != 130 || $height_go_hg != 80) {
                //$this -> error("默认图片大小不符合条件");
            }
            preg_match("/\.(?:png|jpg|jpeg)$/i", $news_pic['name'], $matches);
            if (!$matches) {
                $this->error("上传图片类型错误！");
            }
            if ($news_pic['size'] > 35 * 1024) {
                $this->error("默认图片尺寸大于35K");
            }
            if (!$news_pic['name']) {
                $this->error("图片不能为空");
            }
            $config = array(
                'multi_config' => array(
                    'news_pic' => array(
                        'savepath' => UPLOAD_PATH . '/img/' . $path,
                        'saveRule' => 'getmsec'
                    ),
                ),
            );
            $list = $this->_uploadapk(0, $config);
            $news_url = $list['image'][0]['url'];
            $data['news_pic'] = $news_url;
        }
        $news_content = $_POST['news_content'];
        if (!$news_content) {
            $this->error("简介内容不能为空");
        }
        /*
          if(strlen($news_content) > 180){
          $this -> error("简介内容不能大于60个字");
          }
         */
        if (mb_strlen($news_content, 'utf-8') > 40) {
            $this->error("简介内容不能大于40个字");
        }
        $module_content = $_POST['editor_content'];
        if (empty($module_content) || $module_content == "<p>
	&nbsp;
</p>") {
            $this->error("编辑内容不能为空");
        }
        $_POST['editor_content'] = stripcslashes($_POST['editor_content']);
        // 1，将与自己域名相关的图片域名换回约定的标签字符串
        $_POST['editor_content'] = str_replace(GAMEINFO_ATTACHMENT_HOST, self::HOST_TAG, $_POST['editor_content']);
        // 2，将富文本里的图片发送到服务器并路径内容写成约定标签
        preg_match_all("/<img.+?src=\"(\/Public\/js\/kindeditor.*?)\".+?\/>/u", $_POST['editor_content'], $matches);

        $pre_path = $_SERVER['DOCUMENT_ROOT'];

        foreach ($matches[1] as $key => $val) {
            $files_name[$key] = str_replace('.', '', microtime(true)) . '_' . OnlinegameAction::rand_code(8);
        }
        foreach ($matches[1] as $key => $val) {
            $files[$files_name[$key]] = curl_file_create($pre_path . $val);
        }
        $arr = OnlinegameAction::dev_upload($files);
        if ($arr['ret']) {
            foreach ($arr['ret'] as $key => $val) {
                unset($k, $new_k);
                $k = array_search($key, $files_name);
                $new_k = $matches[1][$k];
                $new_arr[$new_k] = self::HOST_TAG . $val;
            }
            //文章内容中图片路径替换
            $_POST['editor_content'] = strtr($_POST['editor_content'], $new_arr);
        }

        $been_result = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
        $from = $been_result[0]['status'];
        $data['news_name'] = $news_name;
        $data['apply_pkg'] = $apply_pkg;
        $data['author'] = $author;
        $data['module_content'] = htmlspecialchars($_POST['editor_content']);
        $data['news_content'] = htmlspecialchars($news_content);
        $data['update_tm'] = time();
        /////////// new added data
        $data['softname'] = $_POST['softname'];
        $data['info_type'] = $_POST['info_type'];
        ///////////
        $data_tmp = array();
        foreach ($data as $key => $value) {
            $data_tmp[$key] = $value;
        }
        unset($data_tmp['module_content']);
        $where = array(
            'id' => $id
        );
        $log_result = $this->logcheck($where, 'sj_olgame_news', $data_tmp, $model);
        $find = $model->table('sj_olgame_news')->where($where)->find();
        if ($find['module_content'] != $data['module_content'])
            $log_result .= "，module_content字段也被编辑";
        $result = $model->table('sj_olgame_news')->where($where)->save($data);

        if ($result) {
            $this->writelog("网游_新闻资讯_已编辑id为{$id}的新闻资讯：" . $log_result,'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
            $from = $_POST['from'];
            $this->assign('jumpUrl', "/index.php/Sj/Onlinegame/{$from}?{$_POST['url_param']}");
            $this->success("编辑成功");
        } else {
            $this->error("编辑失败");
        }
    }

    //新闻资讯发布/取消发布
    function news_release() {
        $model = new Model();
		$first=substr($_GET['id'],0,1);
		if($first==",")
		{
			$nid = substr($_GET['id'],1);
		}
		else
		{
			$nid = $_GET['id'];
		}
		$id_arr = explode(',',$nid);
		foreach($id_arr as $id)
		{
			$flag=false;
			$been_result = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
			if ($_GET['cancel']) {  //取消发布
				$data['status'] = 1;
				$result = $model->table('sj_olgame_news')->where(array('id' => $id))->save($data);
				if ($result) {
					$rank_sql = "update sj_olgame_news set rank = rank-1 where rank >{$been_result[0]['rank']} and `status`=2";
					$model->query($rank_sql);
					$this->writelog("网游_新闻资讯_已取消id为{$id}的新闻资讯的发布",'sj_olgame_news',"{$id}", __ACTION__ ,"","del");
				}
			} 
			else 
			{
				// 检查数据不能为空
				$non_empty_column = array('news_name', 'news_pic', 'author', 'apply_pkg', 'news_content', 'module_content');
				foreach ($non_empty_column as $column) 
				{
					if (empty($been_result[0][$column])) 
					{
						if($first==",")//判断批量发布有问题
						{
							$error_id[]=$id;
							$flag=true;
							break;
						}
						else//单条发布内容为空
						{
							$this->error("存在空的内容，不能发布！");
						}
					}
				}
				if($flag==true)
				{
					continue;
				}
				if ($been_result[0]['status'] == 1) {  //发布
					$data['status'] = 2;
					$data['release_tm'] = time();
					$rank_sql = "update sj_olgame_news set rank = rank+1 where `status`=2";
					$model->query($rank_sql);
					$result = $model->table('sj_olgame_news')->where(array('id' => $id))->save($data);
					if ($result) {
						$this->writelog("已发布id为{$id}的新闻资讯",'sj_olgame_news',"{$result}", __ACTION__ ,"","edit");
					} 
				} elseif ($been_result[0]['status'] == 2) { // 重新发布
					$been_result = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
					$data['release_tm'] = time();
					$result = $model->table('sj_olgame_news')->where(array('id' => $id))->save($data);
					if ($result) {
						$this->writelog("已重新发布id为{$id}的新闻资讯",'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
					}  
				} else {
					$this->error("操作错误");
				}
			}
		}
		if($result)
		{
			if($error_id)
			{
				$error_id=implode(',',$error_id);
				$this->success("除了id为【{$error_id}存在空的内容不能发布】其他都操作成功");	
			}
			else
			{
				$this->success("操作成功");
			}
		}
		else
		{
			$this->error("操作失败");
		}
    }

    // 根据包名查找软件名
    public function search_softname($package) {
        $package = $_POST['package'];
        $model = M();
        $where = array(
            'package' => $package,
            'status' => 1,
            'hide' => array('in', '0,1,1024'),
        );
        $find = $model->table('sj_soft')->where($where)->order('version_code')->order('version_code desc')->find();
        if ($find) {
            $this->ajaxReturn(1, $find, 1);
        } else
            $this->ajaxReturn(0, '', 0);
    }

    // 通过输入数字来变更排序
    public function change_rank() {
        $model = M();
        $where = array(
            'status' => 2,
        );
		if($_GET['recommend'] == 1){
				$where['recommend_status']=1;
		}
        $count = $model->table('sj_olgame_news')->where($where)->order('create_tm DESC')->count();
		//print_r($_GET);
		//print_r($_POST);exit;
        if ($_POST) {
			$field = 'rank';//默认要修改的字段
			$log = '新闻资讯配置全部列表排序';//日志记录信息
			//判断是否是推荐列表
			if($_POST['recommend'] == 1){
				$field = 'recommend_rank';
				$log = '新闻资讯配置推荐列表排序';//日志记录信息
			};
            $id = $_POST['id_of_change_rank'];
            $new_rank = $_POST['new_rank'];
            if ($new_rank > $count || $new_rank < 1) {
                $this->error('输入的排序位置有误！');
            }
            $where = array(
                'id' => $id,
                'status' => 2,
            );
            $find = $model->table('sj_olgame_news')->where($where)->find();
            if (!$find)
                $this->error('未找到此记录！');
            // 保存变更前该记录的排序
            $old_rank = $find[$field];
            // 如果old_rank和new_rank相等，直接返回
            if ($old_rank == $new_rank){
				$data_rank['recommend_update_time'] = time();
				$ret = $model->table('sj_olgame_news')->where($where)->save($data_rank);
				$this->writelog("{$log},修改id如下：{$id},把{$old_rank}改为{$new_rank}",'sj_olgame_news',"{$result}", __ACTION__ ,"","add");
                $this->success('变更排序成功！');
			}
            // 变更该记录排序
			$data_rank[$field] = $new_rank;
			$data_rank['recommend_update_time'] = time();
            $ret = $model->table('sj_olgame_news')->where($where)->save($data_rank);
			//echo $model->getLastSql();exit;
            if (!$ret)
                $this->error('变更排序失败！请重新再试');
            if($_POST['recommend'] != 1){
				// 更新其他受影响的记录
				if ($old_rank < $new_rank) {
					$where = array(
						'id' => array('neq', $id),
						$field => array(array('gt', $old_rank), array('elt', $new_rank), 'and'),
						'status' => 2,
					);
					$records = $model->table('sj_olgame_news')->where($where)->select();
					foreach ($records as $key => $record) {
						$model->table('sj_olgame_news')->where(array('id' => $record['id']))->save(array($field => $record[$field] - 1));
					}
					$this->writelog("{$log},修改id如下：{$id},把{$old_rank}改为{$new_rank}",'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
					$this->success('变更排序成功！');
				} else {
					$where = array(
						'id' => array('neq', $id),
						$field => array(array('lt', $old_rank), array('egt', $new_rank), 'and'),
						'status' => 2,
					);
					$records = $model->table('sj_olgame_news')->where($where)->select();
					foreach ($records as $key => $record) {
						$model->table('sj_olgame_news')->where(array('id' => $record['id']))->save(array($field => $record[$field] + 1));
					}
					$this->writelog("{$log},修改id如下：{$id},把{$old_rank}改为{$new_rank}",'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
					$this->success('变更排序成功！');
				}
			}else{
					$this->writelog("{$log},修改id如下：{$id},把{$old_rank}改为{$new_rank}",'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
					$this->success('变更排序成功！');
			}
        } else if ($_GET) {
            $id = $_GET['id'];
            $this->assign('id', $id);
            $this->assign('count', $count);
            $this->display();
        }
    }

    //新闻资讯置顶/取消置顶
    function news_rank_stick() {
        $model = new Model();
        $id = $_GET['id'];
        $result_rank = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
        if ($result_rank[0]['rank'] == 1) {
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_news_list');
            $this->success("置顶成功");
        } else {
            $where['_string'] = "status = 2 and rank < {$result_rank[0]['rank']}";
            $all_result = $model->table('sj_olgame_news')->where($where)->select();
            foreach ($all_result as $key => $val) {
                $where_change['_string'] = "id = {$val['id']}";
                $data_change['rank'] = $val['rank'] + 1;
                $change_result = $model->table('sj_olgame_news')->where($where_change)->save($data_change);
            }
            $my_where['_string'] = "id = {$id} and status = 2";
            $my_data['rank'] = 1;
            $my_result = $model->table('sj_olgame_news')->where($my_where)->save($my_data);
            if ($my_result) {
                $this->writelog("已置顶id为{$id}的新闻资讯",'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_news_list');
                $this->success("置顶成功");
            }
        }
    }

    //新闻资讯上移/下移
    function news_rank_change() {
        $model = new Model();
        $change = $_GET['change'];
        $id = $_GET['id'];
        $rank_result = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
        if ($change == 'up') {
            $than_result = $model->table('sj_olgame_news')->where(array('rank' => $rank_result[0]['rank'] - 1, 'status' => 2))->save(array('rank' => $rank_result[0]['rank']));
            $self_result = $model->table('sj_olgame_news')->where(array('id' => $id))->save(array('rank' => $rank_result[0]['rank'] - 1));
            $rank_now = $rank_result[0]['rank'] + 1;
            if ($than_result && $self_result) {
                $this->writelog("已修改id为{$id}的新闻资讯位置为{$rank_now}",'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_news_list');
                $this->success("操作成功");
            }
        } elseif ($change == 'down') {
            $less_result = $model->table('sj_olgame_news')->where(array('rank' => $rank_result[0]['rank'] + 1, 'status' => 2))->save(array('rank' => $rank_result[0]['rank']));
            $self_result = $model->table('sj_olgame_news')->where(array('id' => $id))->save(array('rank' => $rank_result[0]['rank'] + 1));
            $rank_now = $rank_result[0]['rank'] - 1;
            if ($less_result && $self_result) {
                $this->writelog("已修改id为{$id}的新闻资讯位置为{$rank_now}",'sj_olgame_news',"{$id}", __ACTION__ ,"","edit");
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Onlinegame/game_news_list');
                $this->success("操作成功");
            }
        }
    }

    //新闻资讯删除
    function game_news_del() {
        $model = new Model();
		$first=substr($_GET['id'],0,1);
		if($first==",")
		{
			$nid = substr($_GET['id'],1);
		}
		else
		{
			$nid = $_GET['id'];
		}
		$id_arr = explode(',',$nid);
		foreach($id_arr as $id)
		{
			$data['status'] = 0;
			$data['update_tm'] = time();
			$been_result = $model->table('sj_olgame_news')->where(array('id' => $id))->select();
			if ($been_result[0]['status'] == 2) {
				$rank_result = $model->table('sj_olgame_news')->where("status = 2 and rank > {$been_result[0]['rank']}")->select();
				foreach ($rank_result as $key => $val) {
					$data_change['rank'] = $val['rank'] - 1;
					$change_result = $model->table('sj_olgame_news')->where("status = 2 and id = {$val['id']}")->save($data_change);
				}
				$result = $model->table('sj_olgame_news')->where(array('id' => $id))->save($data);
				$from = 2;
			} else {
				$result = $model->table('sj_olgame_news')->where(array('id' => $id))->save($data);
				$from = 1;
			}
			if ($result) {
				$this->writelog("网游_新闻资讯_已删除id为{$id}的新闻资讯",'sj_olgame_news',"{$id}", __ACTION__ ,"","del");
				//$this->success("删除成功");
			}
		}
		if($result)
		{
			$this->success("删除成功");
		}
		else
		{
			$this->success("删除失败");
		}
    }
	
	//新闻资讯推荐
    function game_news_recommend() {
		$model = M();
		$id = explode(',',$_GET['id']);
		$data['recommend_rank'] = 999;
		$data['recommend_status'] = 1;
		$data['recommend_update_time']   = time();
		$result = $model->table('sj_olgame_news')->where(array('id' => array('in',$id)))->save($data);
		//echo $model->getLastSql();exit;
		if($result)
		{
			$this->writelog("SDK攻略推荐：id为：{$_GET['id']}",'sj_olgame_news',"{$_GET['id']}", __ACTION__ ,"","edit");
			$this->success("推荐成功");
		}
		else
		{
			$this->writelog("SDK攻略推荐：id为：{$_GET['id']}失败",'sj_olgame_news',"{$_GET['id']}", __ACTION__ ,"","edit");
			$this->success("推荐失败");
		}
    }
    
	//新闻资讯取消推荐
	function game_news_recommend_remove(){
		$model = M();
		$id = explode(',',$_GET['id']);
		$data['recommend_status'] = 0;
		$data['recommend_rank'] = 0;
		$data['recommend_update_time']   = time();
		$result = $model->table('sj_olgame_news')->where(array('id' => array('in',$id)))->save($data);
		if($result)
		{
			$this->writelog("SDK攻略推荐：取消id为：{$_GET['id']}",'sj_olgame_news',"{$_GET['id']}", __ACTION__ ,"","del");
			$this->success("取消成功");
		}
		else
		{
			$this->writelog("SDK攻略推荐：取消id为：{$_GET['id']}失败",'sj_olgame_news',"{$_GET['id']}", __ACTION__ ,"","del");
			$this->success("取消推荐失败");
		}
	}
	//新服列表
    public function new_server() {
        $model = new Model();
        $time = time();
        $timelast = strtotime(date('Y-m-d H:i:s', $time));
        $time = $timelast;
        import("@.ORG.Page");
        $count = $model->table('sj_new_server')->where(" begin >= " . $time . " and status=1")->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //$this->assign('page', $show);
        //$arr = $model ->table('sj_new_server') ->where(" begin > ".$time)->order('sort')->limit($Page->firstRow.','.$Page->listRows)->select();
        //不分页

        $arr = $model->table('sj_new_server')->where(" begin >= " . $time . " and status=1")->order('sort desc')->findALL();

        $cl = $model->table('sj_new_server')->where(" begin >= " . $time . " and status=1")->order('sort desc')->find();
        $dl = $model->table('sj_new_server')->where(" begin >= " . $time . " and status=1")->order('sort')->find();
        $this->assign('min', $cl['sort']);
        $this->assign('max', $dl['sort']);
        $this->assign('count', $count);

        foreach ($arr as $key => $val) {
            $arr[$key]['pid'] = $key + 1;
        }

        $this->assign('value', $arr);
        $this->display();
    }

    //添加新服
    public function add_new_server() {
        if ($_POST) {
            $model = new Model();
			      $active_model = D('sendNum.sendNum');
            $time = time();
            $count = $model->table('sj_new_server')->where(" begin > " . $time)->count();

            $data['pack_name'] = $_POST['pack_name'];
            $data['begin'] = strtotime($_POST['begin']);
            $data['new_server_name'] = trim($_POST['new_server_name']);
            $data['comment'] = trim($_POST['comment']);
            $data['sort'] = $time;
            $data['status'] = 1;
            $data['add_tm'] = $time;
            $data['update_tm'] = $time;
            $data['pass_tm'] = $time;
			      $data['server_type'] = $_POST['server_type'];
            //$data['xin_str'] = $_POST['xin_str'];
			
      			if($_POST['server_type']==2){
      				if(empty($_POST['del_type'])) $this->error("请选择内测类型");
      				$data['del_type'] = $_POST['del_type'];
      				//V6.2内测 新增上传激活码、使用方法和结束时间
      				if($_POST['end'])
      				{
      					$data['end'] = strtotime($_POST['end']);
      					if($data['begin'] > $data['end'])
      					{
      						$this->error("开服时间不能大于结束时间");
      					}
      				}
      				else
      				{
      					$this->error("结束时间不能为空");
      				}
      				if($_POST['use_method'])
      				{
      					if(mb_strlen($_POST['use_method'],'utf-8')>50||mb_strlen($_POST['use_method'],'utf-8')<2)
      					{
      						$this->error("可输入2~50个字符");
      					}
      					$data['method'] = $_POST['use_method'];
      				}
      				else
      				{
      					$this->error("使用方法不能为空");
      				}
      				if($_POST['new_file'])
      				{
      					$data['active_codes_count'] =$_POST['out_count'];
      					//没必要上传文件路径了
      					//$data['up_file_path'] = $_POST['new_file'];
      					 //上传文件获取隐藏域中的内容
      					$new_file = $_POST['new_file'];
      					//$new_file = C("NEW_SERVER_CSV").$new_file;
      					
      					/*if ($new_file == '') {
      						$this->error("请上传激活码文件");
      					}*/
      					if (!file_exists($new_file)) 
      					{
      						$this->error("csv文件不存在");
      					}

      					$new_file_name = $new_file;
      					$shili = fopen($new_file_name, "r");  //打开文本

      					while (!feof($shili)) {      //判断是否到了文件最后的函数
      						$shi = fgets($shili, 1024);    //读取其中的数据
      						$a = explode(',', $shi);
      						if ($a[1]) {
      							$this->error("激活码文件格式错误");
      						}
      						$str .= $shi . ",";
      					}
      					$str_arr = str_replace("\r\n", '', $str);
      					$str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);
      					$code_arrs = explode(',', $str_arrs);
      					foreach ($code_arrs as $key => $val) {
      						if (trim($val) != "") {
      							$code_arr[$key] = $val;
      						}
      					}
      					$all_num = count($code_arr);
      					if($all_num)
      					{
      						$data['active_codes_release_begin'] = strtotime($_POST['active_codes_release_begin']);
      						$data['active_codes_release_end'] = strtotime($_POST['active_codes_release_end']);
      						$data['active_codes_effective_begin'] = strtotime($_POST['active_codes_effective_begin']);
      						$data['active_codes_effective_end'] = strtotime($_POST['active_codes_effective_end']);
      						if(!$data['active_codes_release_begin'])
      						{
      							$this->error("激活码发放开始时间不能为空");
      						}
      						if(!$data['active_codes_release_end'])
      						{
      							$this->error("激活码发放结束时间不能为空");
      						}
      						if(!$data['active_codes_effective_begin'])
      						{
      							$this->error("激活码有效开始时间不能为空");
      						}
      						if(!$data['active_codes_effective_end'])
      						{
      							$this->error("激活码有效结束时间不能为空");
      						}
      						if($data['active_codes_release_begin'] > $data['active_codes_release_end'])
      						{
      							$this->error("激活码发放开始时间不能大于结束时间");
      						}
      						if($data['active_codes_effective_begin'] > $data['active_codes_effective_end'])
      						{
      							$this->error("激活码有效开始时间不能大于结束时间");
      						}
      						//当前时间<=发放开始<=内测开始<=有效开始   发放结束<=内测结束<=有效结束
      						if($data['active_codes_release_begin'] < time())
      						{
      							$this->error("激活码发放开始时间不能小于当前时间间");
      						}
      						if($data['active_codes_release_begin']>$data['begin'])
      						{
      							$this->error("激活码发放开始时间不能大于内测服开始时间");
      						}
      						if($data['active_codes_release_begin']>$data['active_codes_effective_begin'])
      						{
      							$this->error("激活码发放开始时间不能大于激活码有效开始时间");
      						}
      						if($data['begin']>$data['active_codes_effective_begin'])
      						{
      							$this->error("内测服开始时间不能大于激活码有效开始时间");
      						}
      						if($data['active_codes_release_end']>$data['end'])
      						{
      							$this->error("激活码发放结束时间不能大于内测服结束时间");
      						}
      						if($data['active_codes_release_end']>$data['active_codes_effective_end'])
      						{
      							$this->error("激活码发放结束时间不能大于激活码有效结束时间");
      						}
      						if($data['end']>$data['active_codes_effective_end'])
      						{
      							$this->error("内测服结束时间不能大于激活码有效结束时间");
      						}
      					}
      					if ($all_num > 9999) {
      						$this->error("激活码总数不能超过9999个");
      					}
      					if ($all_num < 1) {
      						$this->error("激活码不能少于1个");
      					}
      					foreach ($code_arr as $key => $val) {
      						if (strlen($val) > 25) {
      							$this->error("激活码长度不能大于25位");
      						}
      					}
      					$code_arr_unique = array_unique($code_arr);
      					if (preg_match("/[\x80-\xff]./", $str_arrs)) {
      						$this->error("激活码文件中不可有中文");
      					}
      					if (count($code_arr) > count($code_arr_unique)) 
      					{
      						$repeat_arr = array_diff_assoc($code_arr, $code_arr_unique);
      						foreach ($repeat_arr as $key => $val) {
      							$repeat_str .= $val . ',';
      							$this->error("激活码文件中含有重复数据:{$val}");
      						}
      					}
      				}
      			}
      			if($data['end'])
      			{
      				$search_where = array(
      					'pack_name'=>$data['pack_name'],
      					//'begin' =>  array('elt',$data['end']),
      					//'end' =>  array('egt',$data['begin']),
      					'end' =>  array('egt',time()),
      					'status' =>  array('exp',"!=0")
      				);
      			}
      			else
      			{
      				$b_time = explode(' ',$_POST['begin']);
      				$search_where = array(
      					'pack_name'=>$data['pack_name'],
      					'FROM_UNIXTIME(begin)' =>  array('exp','like "'.$b_time[0].'%"'),
      					'status' =>  array('exp',"!=0")
      				);
      			}
			
      			$begin_allow = $model->table('sj_new_server')->where($search_where)->field('id')->find();
      			if($begin_allow){
                    echo $model->getLastSql();
                    var_dump($begin_allow);
      				//所选日期已有新服或内测服，请重新选择开服时间
      				$this->error("您还有内测服或新服未结束，暂不可以添加新内测服或新服");
      			}
            if ($data['pack_name'] == '') {
                $this->error("包名不能为空");
            } else if ($data['begin'] == '') {
                $this->error("开服时间不能为空");
            } else if ($data['begin'] < time()) {
                $this->error("开服时间不能小于当前时间");
            } else if ($data['new_server_name'] == '') {
                $this->error("服务器名称不能为空");
            } else {
        				$where = array(
        					'package'=>$data['pack_name'],
        					'hide' => 1,
        					'status' =>1,
        				);				
                $res = $model->table('sj_soft')->where($where)->field('package')->find();
                if (empty($res)) {
                    $this->error("软件包名不存在");
                } else {
                    $new_servername = trim($_POST['new_server_name']);
                    $pack_name = $_POST['pack_name'];
                    $sel = $model->table('sj_new_server')->data($data)->add();
                    if($sel){
                        if($data['active_codes_count'])
                        {
                            $active_model->create_newserver_activecode($sel);
                            $code_arr = array_chunk($code_arr,500);
                            //激活码入口
                            foreach ($code_arr as $key => $val) {
                                if ($val) {
                                    $sql_str = '';
                                    $crete_time=time();
                                    foreach($val as $v){
                                        $sql_str .= ",('{$crete_time}','{$v}',0)";
                                    }
                                    $sql_str =  substr($sql_str,1);
                                    $active_model->add_new_server_num($sql_str, $sel);
                                }
                            }
                        }
                        $this->writelog("网游-新服-添加新服名称为{$new_servername},包名为{$pack_name}",'sj_new_server',"{$sel}", __ACTION__ ,"","add");
                        $this->success("成功");
                    }else{
                        $this->error("添加失败");
                    }

                }
            }
        } else {
            $this->display();
        }
    }
    //批量添加新服
    public function batch_add_new_server() {
		$model = new Model();
		$time = time();
        if (!empty($_FILES['upload_file']['tmp_name'])) {
			$array = array('csv');
			$ytypes = $_FILES['upload_file']['name'];
			$info = pathinfo($ytypes);
			$type =  $info['extension'];//获取文件件扩展名
			$error = '';
			if(!in_array($type,$array)){
				$error .= "上传格式错误<br>";
			}			
			$server_type_arr = array('新服','内测服');
			$del_type_arr = array('删档内测','不删档内测');
			if($error == ''){		
				//把文件中的数据取出转字符编码					
				$data = file_get_contents($_FILES['upload_file']['tmp_name']);
				//判断是否是utf-8
				if(mb_check_encoding($data,"utf-8") != true){
					$data = iconv("gbk","utf-8", $data);
				}	
				$data = str_replace("\r\n","\n",$data);	
				$data_arr = explode("\n", $data);
				
				$package = array();	
				$list_arr = array();
				foreach($data_arr as $k=>$v){
					if($k == 0 || empty($v)) continue;
					$pkg = '';
					$server_name= '';
					$server_type = '';
					$del_type = '';
					$comment = '';
					$begin = '';
					list($pkg,$server_name,$server_type,$del_type,$comment,$begin) = explode(',',$v);
					if($pkg == ''){
						$error .= "包名不可为空<br>";
						continue;
					}	
					if($server_name == ''){
						$error .= "服务器名称不可为空<br>";
						continue;	
					}	
					if($server_type == ''){
						$error .= "服务器名为".$server_name."的服务器类型不可为空<br>";
						continue;
					}else{
						if(!in_array($server_type,$server_type_arr)){
							$error .= "服务器名为".$server_name."的服务器类型不正确<br>";
							continue;
						}
						if($server_type == '内测服'){
							if($del_type == ''){
								$error .= "服务器名为".$server_name."的内测类型不可为空<br>";
								continue;
							}
							if(!in_array($del_type,$del_type_arr)){
								$error .= "服务器名为".$server_name."的内测类型不正确<br>";
								continue;
							}
						}else{
							if($del_type != ''){
								$error .= "服务器名为".$server_name."的内测类型应为空<br>";
								continue;
							}
						}
						
					}
					
					$begin = str_replace('T','',$begin);	
					$tm = strtotime($begin);
					if($tm < $time){
						$error .= "开服时间{$begin}不能小于当前时间<br>";
						continue;
					}
					$b_time = date('Y-m-d',$tm);
					$search_where = array(
						'pack_name'=>$pkg,
						'FROM_UNIXTIME(begin)' =>  array('exp','like "'.$b_time.'%"'),
						'status' =>  array('exp',"!=0")
					);
					$begin_allow = $model->table('sj_new_server')->where($search_where)->field('id')->find();
					if($begin_allow){
						$error .= "包名{$pkg}所选日期{$begin}已有新服或内测服，请重新选择开服时间<br>";
						continue;
					}
					$package[] = $pkg;
					$list_arr[$server_name] = array(
						'new_server_name' => $server_name,
						'comment' => $comment,
						'begin' => $tm,
						'package' => $pkg,
						'server_type'=>$server_type,
						'del_type'=>$del_type
					);
				}
				unset($data_arr);
				if($package){
					$where = array(
						'package'=>array('in',$package),
						'hide' => 1,
						'status' =>1,
					);
					$soft = get_table_data($where,"sj_soft","package","package");
					foreach($list_arr as $key => $val){
						if(empty($soft[$val['package']])) $error .= "软件包名{$val['package']}不存在<br> ";
					}
				}
			}	
			if($error == ''){
				//整理入库sql
				$sql_header = " INSERT INTO `sj_new_server` (`pack_name`,`begin`,`new_server_name`,`comment`,`sort`,`status`,`add_tm`,`update_tm`,`pass_tm`,`server_type`,`del_type`) VALUES ";
				$sql_str = "";
				$log = "网游-新服-批量添加";
				$data_server_type = array('新服'=>1,'内测服'=>2);
				$data_del_type = array('删档内测'=>1,'不删档内测'=>2);
				foreach($list_arr as $key => $val){
					$sql_str .= ",('{$val['package']}','{$val['begin']}','{$val['new_server_name']}','{$val['comment']}','{$time}','1','{$time}','{$time}','{$time}','{$data_server_type[$val['server_type']]}','{$data_del_type[$val['del_type']]}')";
					$log .= "新服名称为{$val['new_server_name']},包名为{$val['package']}";
				}
				$sql_str =  substr($sql_str,1);
				$model->query($sql_header . $sql_str);
				$this->writelog($log,'sj_new_server',"", __ACTION__ ,"","add");
				exit(json_encode(array('code'=>'1')));	
			}else{
				exit(json_encode(array('code'=>'0','error'=>$error)));	
			}
        } else {
            $this->display();
        }
    }

    //显示编辑
    public function update_server_show() {
        $User = new Model();
        $id = $_GET['id'];
        $result = $User->table('sj_new_server')->where("id = $id")->find();
        $this->assign('value', $result);
        $this->display();
    }

    //编辑新服
    public function update_new_server() {
        $User = new Model();
		$active_model = D('sendNum.sendNum');
        $data['pack_name'] = $_POST['pack_name'];
        $data['begin'] = strtotime($_POST['begin']);
        $data['new_server_name'] = trim($_POST['new_server_name']);
        $data['comment'] = trim($_POST['comment']);
		$data['server_type'] = $_POST['server_type'];
		$data['del_type'] = $_POST['del_type'];
        $id = $_POST['id'];
		$old_new_count = $_POST['active_codes_count'];
		
		if($_POST['server_type']==2)
		{
			if(empty($_POST['del_type'])) $this->error("请选择内测类型");
			//V6.2内测 新增上传激活码、使用方法和结束时间
			if($_POST['end'])
			{
        $old_data = $User->table('sj_new_server')->where(array('id'=>$id))->find();
        $data['begin']=$old_data['begin'];
				$data['end'] = strtotime($_POST['end']);
				if($data['begin'] > $data['end'])
				{
					$this->error("开服时间不能大于结束时间");
				}

        
        if($old_data['end'] > $data['end'])
        {
          $this->error("修改后的结束时间必须大于当前结束时间");
        }
			}
			else
			{
				$this->error("结束时间不能为空");
			}
			if($_POST['use_method'])
			{
				if(mb_strlen($_POST['use_method'],'utf-8')>50||mb_strlen($_POST['use_method'],'utf-8')<2)
				{
					$this->error("可输入2~50个字符");
				}
				$data['method'] = $_POST['use_method'];
			}
			else
			{
				$this->error("使用方法不能为空");
			}
			if($_POST['new_file'])
			{
				$data['active_codes_count'] =$_POST['out_count']+$_POST['active_codes_count'];
				//$data['up_file_path'] = $_POST['new_file'];
				
				 //上传文件获取隐藏域中的内容
				$new_file = $_POST['new_file'];
				//$new_file = C("NEW_SERVER_CSV").$new_file;
			
				if (!file_exists($new_file)) 
				{
					$this->error("csv文件不存在");
				}

				$new_file_name = $new_file;
				$shili = fopen($new_file_name, "r");  //打开文本

				while (!feof($shili)) {      //判断是否到了文件最后的函数
					$shi = fgets($shili, 1024);    //读取其中的数据
					$a = explode(',', $shi);
					if ($a[1]) {
						$this->error("激活码文件格式错误");
					}
					$str .= $shi . ",";
				}

				$str_arr = str_replace("\r\n", '', $str);
				$str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);
				$code_arrs = explode(',', $str_arrs);
				foreach ($code_arrs as $key => $val) {
					if (trim($val) != "") {
						$code_arr[$key] = $val;
					}
				}
				$all_num = count($code_arr);
				//编辑追加的时候查看之前的有几个
				$old_new_count += $all_num;
				if ($old_new_count > 9999) {
					$this->error("激活码总数不能超过9999个");
				}
				if ($old_new_count < 1) {
					$this->error("激活码不能少于1个");
				}
				foreach ($code_arr as $key => $val) {
					if (strlen($val) > 25) {
						$this->error("激活码长度不能大于25位");
					}
				}
				$code_arr_unique = array_unique($code_arr);
				if (preg_match("/[\x80-\xff]./", $str_arrs)) {
					$this->error("激活码文件中不可有中文");
				}
				if (count($code_arr) > count($code_arr_unique)) 
				{
					$repeat_arr = array_diff_assoc($code_arr, $code_arr_unique);
					foreach ($repeat_arr as $key => $val) {
						$repeat_str .= $val . ',';
						$this->error("激活码文件中含有重复数据:{$val}");
					}
				}
			}
		}
    // else{
    //   $data['end'] = '';
    // }
		if($old_new_count!=0 && $_POST['server_type']==2)
		{
			$data['active_codes_release_begin'] = strtotime($_POST['active_codes_release_begin']);
			$data['active_codes_release_end'] = strtotime($_POST['active_codes_release_end']);
			$data['active_codes_effective_begin'] = strtotime($_POST['active_codes_effective_begin']);
			$data['active_codes_effective_end'] = strtotime($_POST['active_codes_effective_end']);
			if(!$data['active_codes_release_begin'])
			{
				$this->error("激活码发放开始时间不能为空");
			}
			if(!$data['active_codes_release_end'])
			{
				$this->error("激活码发放结束时间不能为空");
			}
			if(!$data['active_codes_effective_begin'])
			{
				$this->error("激活码有效开始时间不能为空");
			}
			if(!$data['active_codes_effective_end'])
			{
				$this->error("激活码有效结束时间不能为空");
			}
			if($data['active_codes_release_begin'] > $data['active_codes_release_end'])
			{
				$this->error("激活码发放开始时间不能大于结束时间");
			}
			if($data['active_codes_effective_begin'] > $data['active_codes_effective_end'])
			{
				$this->error("激活码有效开始时间不能大于结束时间");
			}
			//当前时间<=发放开始<=内测开始<=有效开始   发放结束<=内测结束<=有效结束
			if($data['active_codes_release_begin'] < time())
			{
				$this->error("激活码发放开始时间不能小于当前时间");
			}
			if($data['active_codes_release_begin']>$data['begin'])
			{
				$this->error("激活码发放开始时间不能大于内测服开始时间");
			}
			if($data['active_codes_release_begin']>$data['active_codes_effective_begin'])
			{
				$this->error("激活码发放开始时间不能大于激活码有效开始时间");
			}
			if($data['begin']>$data['active_codes_effective_begin'])
			{
				$this->error("内测服开始时间不能大于激活码有效开始时间");
			}
			if($data['active_codes_release_end']>$data['end'])
			{
				$this->error("激活码发放结束时间不能大于内测服结束时间");
			}
			if($data['active_codes_release_end']>$data['active_codes_effective_end'])
			{
				$this->error("激活码发放结束时间不能大于激活码有效结束时间");
			}
			if($data['end']>$data['active_codes_effective_end'])
			{
				$this->error("内测服结束时间不能大于激活码有效结束时间");
			}
		}
		
		if($data['end'])
		{
			$search_where = array(
				'id' => array('exp',"!={$id}"),
				'pack_name'=>$data['pack_name'],
				//'begin' =>  array('elt',$data['end']),
				//'end' =>  array('egt',$data['begin']),
				'end' =>  array('egt',time()),
				'status' =>  array('exp',"!=0")
			);
		}
		else
		{
			$b_time = explode(' ',$_POST['begin']);
			$search_where = array(
				'id' => array('exp',"!={$id}"),
				'pack_name'=>$data['pack_name'],
				'FROM_UNIXTIME(begin)' =>  array('exp','like "'.$b_time[0].'%"'),
				'status' =>  array('exp',"!=0")
			);
		}
			
		/*$b_time = explode(' ',$_POST['begin']);
		$search_where = array(
			'id' => array('exp',"!={$id}"),
			'pack_name'=>$data['pack_name'],
			'FROM_UNIXTIME(begin)' =>  array('exp','like "'.$b_time[0].'%"'),
			'status' =>  array('exp',"!=0")
		);*/
		$begin_allow = $User->table('sj_new_server')->where($search_where)->field('id')->find();
		if($begin_allow){
			//$this->error("所选日期已有新服或内测服，请重新选择开服时间");
			$this->error("您还有内测服或新服未结束，暂不可以添加新内测服或新服");
		}
        //$data['xin_str'] = $_POST['xin_str'];
        if ($data['pack_name'] == '') {
            $this->error("包名不能为空");
        } else if ($data['begin'] < time()) {
            $this->error("开服时间不能小于当前时间");
        } else if ($data['begin'] == '') {

            $this->error("开服时间不能为空");
        } else if ($data['new_server_name'] == '') {

            $this->error("服务器名称不能为空");
        } else {

            $sel = $User->table('sj_soft')->where(array('package' => $data['pack_name']))->select();

            if (empty($sel)) {
                $this->error("软件包名不存在");
            } else {
                $check_com = $User->table("sj_new_server")->where("id = $id and stats = 1")->find();
                if ($check_com != '') {
                    if ($data['comment'] == '') {
                        $this->error("网游精选配置状态开启之后简介不能为空");
                    }
                }
                $log = $this->logcheck(array('id' => $id), 'sj_new_server', $data, $User);
                $sel = $User->table('sj_new_server')->where("id = $id")->save($data);
				if($_POST['out_count'])
				{
					if(empty($_POST['active_codes_count']))
					{
						$active_model->create_newserver_activecode($id);
					}
					$code_arr = array_chunk($code_arr,500);
					//激活码入口
					foreach ($code_arr as $key => $val) {
						if ($val) {
							$sql_str = '';
							$crete_time=time();
							foreach($val as $v){
								$sql_str .= ",('{$crete_time}','{$v}',0)";
							}
							$sql_str =  substr($sql_str,1);
							$active_model->add_new_server_num($sql_str, $id);
						}
					}
				}
                $this->writelog("网游-新服-ID为$id" . $log,'sj_new_server',"{$id}", __ACTION__ ,"","edit");

                $this->success("成功");
            }
        }
    }

    //取消新服
    public function del_new_server() {
        if ($_GET['type'] == 1) {
            $this->assign('type', 1);
            $this->assign('id', $_GET['id']);
            $this->assign('from', $_GET['from']);
            $this->display('del_new_server');
        } else {
            $model = new Model();
            $emailmodel = D("Dev.Sendemail");
            $config_txt = C('_config_txt_');
            if ($_POST['type'] == 1) {
                $msg = $_POST['del_reason'];
                if (!$msg)
                    $this->error("取消原因不能为空");
                $id_arr = explode(',', $_POST['id']);
            }else {
                $id_arr = $_GET['id'];
            }
            $where = array(
                'id' => array('in', $id_arr),
            );
            $data = array(
                'reject_reason' => $msg,
                'update_tm' => time(),
                'status' => 4,
                'da_order' => 1
            );
            $list = $model->table('sj_new_server')->where($where)->field('pack_name,new_server_name,dev_id')->select();
            $res = $model->table('sj_new_server')->where($where)->save($data);
            if ($res) {
                $table = 'sj_new_server';
                $field = 'pos';
                $where = "status=1";
                $extent_id = $res;
                $target_rank = 1;

                $where_rank = array(
                    'status' => 1,
                    'begin' => array('exp', '>= UNIX_TIMESTAMP(CURDATE())'),
                    'server_type' => 1
                );

                //更新排序
                $param = $this->_updateRankInfo($table, $field, $extent_id, $where_rank, $target_rank);
                
                //发送提醒 ----邮件
                $tm = date("Y-m-d H:i:s", time());
                $package = array();
                $dev_id = array();
                foreach ($list as $v) {
                    $package[] = $v['pack_name'];
                    $dev_id[] = $v['dev_id'];
                    update_soft_status(array('server_status' => 4), $v['pack_name']);
                }
                if ($package) {
                    $where = array('package' => array('in', $package));
                    //获取软件名称
                    $soft_info = get_table_data($where, "sj_soft", "package", "package,softname");
                }
                if ($dev_id) {
                    //开发者信息
                    $where = array('dev_id' => array('in', $dev_id));
                    $dev_list = get_table_data($where, "pu_developer", "dev_id", "dev_id,email,dev_name");
                }
                foreach ($list as $k => $v) {
                    if ($v['dev_id']) {
                        //提醒
                        $search = array("softname", "tm", "new_server_name", "msg");
                        $replace = array($soft_info[$v['pack_name']]['softname'], $tm, $v['new_server_name'], $msg);
                        $msg2 = str_replace($search, $replace, $config_txt['new_server_cancel']);
                        $emailmodel->dev_remind_add($v['dev_id'], $msg2);
                        //发送邮件提醒
                        $subject = $config_txt['new_server_pass_subject'];
                        $search2 = array("devname", "softname", "tm", "new_server_name", "msg");
                        $replace2 = array($dev_list[$v['dev_id']]['dev_name'], $soft_info[$v['pack_name']]['softname'], $tm, $v['new_server_name'], $msg);
                        $email_cont = str_replace($search2, $replace2, $config_txt['new_server_cancel_txt']);
                        $emailmodel->realsend($dev_list[$v['dev_id']]['email'], $dev_list[$v['dev_id']]['dev_name'], $subject, $email_cont);
                        /////提醒结束	
                    }
                    $this->writelog("取消了新服名称为{$v['new_server_name']}包名为{$v['pack_name']}。取消原因：{$msg}", 'sj_new_server', $v['pack_name'], __ACTION__,"","del");
                }
            }
            $this->success("取消成功");
        }
    }

    //过期新服删除
    public function new_server_expired_del() {
        $model = new Model();
        $msg = $_POST['del_reason'];
        if (!$msg)
            $this->error("删除原因不能为空");
        $id_arr = explode(',', $_POST['id']);
        $where = array(
            'id' => array('in', $id_arr),
        );
        $data = array(
            'reject_reason' => $msg,
            'update_tm' => time(),
            'status' => 0,
            'da_order' => 0
        );
        $list = $model->table('sj_new_server')->where($where)->field('pack_name,new_server_name,dev_id')->select();
        $res = $model->table('sj_new_server')->where($where)->save($data);
        if ($res) {
            $last_status =$model->table('sj_new_server')->where(array('pack_name'=>$res['pack_name'],'status'=>array('exp',' !=0 ')))->field('status')->order('update_tm desc')->find();
            update_soft_status(array('server_status'=>$last_status['status']), $res['pack_name']);
            foreach ($list as $k => $v) {
                $this->writelog("删除了新服名称为{$v['new_server_name']}包名为{$v['pack_name']}。删除原因：{$msg}", 'sj_new_server', $v['pack_name'], __ACTION__,"","del");
            }
            $this->success("删除成功");
        }
    }

    //开启新服
    public function start_server() {
        $User = new Model();
        $id = $_GET['id'];
        $stats = $_GET['stats'];
        $time = time();
        if ($stats == 0) {
            $count = $User->table('sj_new_server')->where("stats = 1 and begin > $time")->count();
            if ($count >= 1) {
                $this->error('信息开启只能有一条，若要开启，请先关闭已开启的信息');
            } else {
                $content = $User->table('sj_new_server')->where("id=$id")->find();
                if ($content['comment'] != '') {
                    $log = $this->logcheck(array('id' => $id), 'sj_new_server', array('stats' => 1), $User);
                    $User->table('sj_new_server')->where("id = $id")->setField('stats', 1);
                    $this->writelog("新服-ID为$id" . $log, 'sj_new_server', $id, __ACTION__,'','edit');
                    $this->success("成功");
                } else {
                    $this->error('您没有填写简介内容，请先填写内容后在开启');
                }
            }
        } else if ($stats == 1) {

            $log = $this->logcheck(array('id' => $id), 'sj_new_server', array('stats' => '0'), $User);
            $User->table('sj_new_server')->where("id = $id")->setField('stats', '0');
            $this->writelog("网游-新服-ID为$id" . $log, 'sj_new_server', $id, __ACTION__,'','edit');
            $this->success("成功");
        }
    }

    //新服置顶
    public function server_top() {
        $User = new Model();
        $sort = $_GET['sort'];
        $id = $_GET['id'];
        $result = $User->table('sj_new_server')->order('sort desc')->limit(1)->find();
        $first_sort = $result['sort'];
        $first_id = $result['id'];
        $save1 = $User->table('sj_new_server')->where("id =$id")->setField('sort', $first_sort + 1);

        if ($save1 = true) {
            $this->success("置顶成功");
        } else {
            $this->error("置顶失败");
        }
    }

    //新服上移
    public function server_up() {
        $User = new Model();
        $type = $_GET['type'];
        $sort = $_GET['sort'];
        $id = $_GET['id'];
        $time = time();
        if ($type == 'up') {
            //根据时间戳进行上移
            $result = $User->table('sj_new_server')->where("sort > $sort and begin > $time")->order('sort')->find();
            $User->table('sj_new_server')->where("id = $id")->setField('sort', $result['sort']);
            $User->table('sj_new_server')->where("id ='" . $result['id'] . "'")->setField('sort', $sort);
            $this->success("上移成功");
        } else if ($type == 'down') {
            //根据时间戳进行下移
            $result = $User->table('sj_new_server')->where("sort < $sort and begin > $time")->order('sort desc')->find();
            $User->table('sj_new_server')->where("id = $id")->setField('sort', $result['sort']);
            $User->table('sj_new_server')->where("id ='" . $result['id'] . "'")->setField('sort', $sort);
            $this->success("下移成功");
        }
    }

    function text() {
        $model = new Model();
        sleep(700);
        $result = $model->table('sj_soft')->where(array('status' => 1))->limit(1)->select();
        $this->display();
    }

    //分类
    function get_catgory_str() {
        $model = new model();
        $appcategory = $model->table('sj_category')->where('parentid = 1 and status =1')->field('category_id')->select();
        $appstr = '';
        foreach ($appcategory as $appkey => $appval) {
            $appstr .= $appval['category_id'] . ",";
            $sonapp = $model->table('sj_category')->where('parentid = "' . $appval['category_id'] . '" and status =1')->field('category_id')->select();
            foreach ($sonapp as $sonkey => $sonval) {
                $appstr .= $sonval['category_id'] . ',';
            }
        }
        $appstr = substr($appstr, 0, -1);
        $gamecategory = $model->table('sj_category')->where('parentid = 2 and status =1')->field('category_id')->select();
        $gamestr = '';
        foreach ($gamecategory as $gamekey => $gameval) {
            $gamestr .= $gameval['category_id'] . ",";
            $songame = $model->table('sj_category')->where('parentid = "' . $gameval['category_id'] . '" and status =1')->field('category_id')->select();
            foreach ($songame as $sonkey => $sonval) {
                $gamestr .= $sonval['category_id'] . ',';
            }
        }
        $gamestr = substr($gamestr, 0, -1);
        //电子书
        $bookcategory = $model->table('sj_category')->where('parentid = 3 and status =1')->field('category_id')->select();
        $bookstr = '';
        foreach ($bookcategory as $bookkey => $bookval) {
            $bookstr .= $bookval['category_id'] . ",";
            $sonbook = $model->table('sj_category')->where('parentid = "' . $bookval['category_id'] . '" and status =1')->field('category_id')->select();
            foreach ($sonbook as $sonkey => $sonval) {
                $bookstr .= $sonval['category_id'] . ',';
            }
        }
        $bookstr = substr($bookstr, 0, -1);
        $cateidarr = array('1' => $appstr, '2' => $gamestr, '3' => $bookstr);
        return $cateidarr;
    }

    //新服列表
    function audit_new_server() {
        $model = new Model();
        //刷新排序
        $table = 'sj_new_server';
        $field = 'pos';
        $where = "status=1";
        $extent_id = 1;
        $target_rank = 1;

        $where_rank = array(
            'status' => 1,
            'begin' => array('exp', '>= UNIX_TIMESTAMP(CURDATE())'),
            'server_type' => 1
        );
        $param = $this->_updateRankInfo($table, $field, $extent_id, $where_rank, $target_rank);
        //排序结束

        $status = isset($_GET['status']) ? $_GET['status'] : 2;
        $where = array();
        if ($status == 100) {
            $where['status'] = 1;
            $where['begin'] = array('exp', '< UNIX_TIMESTAMP(CURDATE())');
        } else if ($status == 1) {
            $where['status'] = 1;
            $where['begin'] = array('exp', '>= UNIX_TIMESTAMP(CURDATE())');
        } else {
            $where['status'] = $status;
        }
        //搜索
        if ($_GET) {
            $url_param = '';
            if (isset($_GET['softid']) || isset($_GET['softname'])) {
                $wheres = array('status' => 1);
                if (isset($_GET['softid'])) {
                    $wheres['softid'] = $_GET['softid'];
                    $url_param .= "/softid/{$_GET['softid']}";
                    $this->assign('softid', $_GET['softid']);
                }
                if (isset($_GET['softname'])) {
                    $wheres['softname'] = array('like', "%{$_GET['softname']}%");
                    $url_param .= "/softname/{$_GET['softname']}";
                    $this->assign('softname', $_GET['softname']);
                }
                $res = $model->table('sj_soft')->where($wheres)->field('package')->select();
                $package = array();
                foreach ($res as $v) {
                    $package[] = $v['package'];
                }
                $where['pack_name'] = array('in', $package);
            }
            $this->check_where($where, 'pack_name');
            $this->check_range_where($where, 'begintime', 'endtime', 'begin', true);
            $this->check_range_where($where, 'start_tm', 'end_tm', 'add_tm', true);
            $this->check_range_where($where, 'update_tm_start', 'update_tm_end', 'update_tm', true);
            $this->check_range_where($where, 'pass_tm_start', 'pass_tm_end', 'pass_tm', true);
        }
		if(isset($_GET['server_type'])){
			$where['server_type'] = $_GET['server_type'];
            $url_param .= "/server_type/{$_GET['server_type']}";
			$this->assign('server_type', $_GET['server_type']);
		}
        if(isset($_GET['pack_name'])){
            $url_param .= "/pack_name/{$_GET['pack_name']}";
        }
        if(isset($_GET['begintime'])){
            $url_param .= "/begintime/{$_GET['begintime']}";
        }
        if(isset($_GET['endtime'])){
            $url_param .= "/endtime/{$_GET['endtime']}";
        }
        if(isset($_GET['pass_tm_start'])){
            $url_param .= "/pass_tm_start/{$_GET['pass_tm_start']}";
        }
        if(isset($_GET['pass_tm_end'])){
            $url_param .= "/pass_tm_end/{$_GET['pass_tm_end']}";
        }
        if(isset($_GET['pack_name'])){
            $url_param .= "/pack_name/{$_GET['pack_name']}";
        }
        $this->assign('url_param',$url_param);
        $count = $model->table('sj_new_server')->where($where)->count();
        import("@.ORG.Page2");
        $param = http_build_query($_GET);
        $Page = new Page($count, 15, $param);
        $this->assign('total', $count);
        if ($status == 1) {
            $order = 'pos asc';
        } else if ($status == 100) {
            $order = 'begin desc';
        } else if ($status == 3) {
            $order = 'update_tm desc';
        } else {
            $order = 'add_tm desc';
        }
        $server_list = $model->table('sj_new_server')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order($order)->findALL();
        //echo $model->getlastsql();
        $package = array();
        foreach ($server_list as $k => $v) {
            $package[] = $v['pack_name'];
        }
        $package = array_unique($package);
        if ($package) {
            $where = array(
                'status' => 1,
                'hide' => 1,
                'package' => array('in', $package),
            );
            $soft_list = get_table_data($where, "sj_soft", "package", "softname,softid,package,category_id,total_downloaded");
            $sid = array();
            $categoryids = '';
            foreach ($soft_list as $k => $v) {
                if ($v['softid'])
                    $sid[] = $v['softid'];
                if (!empty($v['category_id'])) {
                    $categoryids .= substr("{$v['category_id']}", 1);
                }
            }
            //sj_soft_file表数据	
            if ($sid) {
                $where = array(
                    'softid' => array('in', $sid),
                    'package_status' => array('exp', "> 0"),
                );
                $file_icon_arr = get_table_data($where, "sj_soft_file", "softid", "id,softid,iconurl,anzhi_sdk");
            }

            //类别名称
            $category_all = array();
            if ($categoryids) {
                $where = array(
                    'status' => 1,
                    'category_id' => array('in', substr($categoryids, 0, -1)),
                );
                $category_all = get_table_data($where, "sj_category", "category_id", "category_id,name,status");
                foreach ($soft_list as $k => $v) {
                    if (!empty($v['category_id'])) {
                        $categoryid = substr("{$v['category_id']}", 1, -1);
                    }
                    $soft_list[$k]['category_name'] = $category_all[$categoryid]['name'];
                    $soft_list[$k]['iconurl'] = $file_icon_arr[$v['softid']]['iconurl'];
                    $soft_list[$k]['anzhi_sdk'] = $file_icon_arr[$v['softid']]['anzhi_sdk'];
                }
            }
        }
        //整理数据
        $list = array();
		$count_num = $model->table('sj_new_server')->where(array('server_type'=>1,'status'=>1,'begin'=>array('exp','>= UNIX_TIMESTAMP(CURDATE())')))->order('pos desc')->find();
		
		$count_num = $count_num['pos'];
        foreach ($server_list as $k => $v) {
            $list[$k]['softid'] = $soft_list[$v['pack_name']]['softid'];
            $list[$k]['softname'] = $soft_list[$v['pack_name']]['softname'];
            $list[$k]['iconurl'] = $soft_list[$v['pack_name']]['iconurl'];
            $list[$k]['category_name'] = $soft_list[$v['pack_name']]['category_name'];
            $list[$k]['anzhi_sdk'] = $soft_list[$v['pack_name']]['anzhi_sdk'];
            $list[$k]['total_downloaded'] = $soft_list[$v['pack_name']]['total_downloaded'];
            $list[$k]['begin'] = $v['begin'] ? date("Y-m-d H:i:s", $v['begin']) : '';
			$list[$k]['end'] = $v['end'] ? date("Y-m-d H:i:s", $v['end']) : '';
			$list[$k]['method'] = $v['method'];
			//文件不显示
			/*if($v['up_file_path'])
			{
				$file_path_arr = explode('/',$v['up_file_path']);
				$count_arr = count($file_path_arr);
				$list[$k]['up_file'] = $file_path_arr[$count_arr-1];
				$list[$k]['up_file_path'] = $v['up_file_path'];
			}*/

            $list[$k]['add_tm'] = $v['add_tm'] ? date("Y-m-d H:i:s", $v['add_tm']) : '';
            $list[$k]['update_tm'] = $v['update_tm'] ? date("Y-m-d H:i:s", $v['update_tm']) : '';
            $list[$k]['pass_tm'] = $v['pass_tm'] ? date("Y-m-d H:i:s", $v['pass_tm']) : '';
            $list[$k]['new_server_name'] = $v['new_server_name'];
            $list[$k]['comment'] = $v['comment'];
            $list[$k]['id'] = $v['id'];
            $list[$k]['package'] = $v['pack_name'];
            $list[$k]['reject_reason'] = $v['reject_reason'];
            $list[$k]['stats'] = $v['stats'];
			$list[$k]['server_type'] = $v['server_type'];
			$list[$k]['del_type'] = $v['del_type'];
            if($v['server_type']==1){
                //排序
                $tmp = "<select rel='{$v['id']}' old_pos='{$v['pos']}' name='rank' class='extent_rank'>";
                for ($i = 1; $i <= $count_num; $i++) {
                    $select = $i == $v['pos'] ? ' selected' : '';
                    $tmp .= "<option value='{$i}'{$select}>{$i}</option>";
                }
                $tmp .= "</select>";
                $list[$k]['pos'] = $tmp;
            }else{
                $tmp = "<input type='text' name='i_rank' value='{$v['pos']}' style='width:25px' class='i_extent_rank' rel='{$v['id']}' old_pos='{$v['pos']}'>";
                $list[$k]['pos'] = $tmp;
            }

        }
        $this->assign('list', $list);
        unset($server_list);
        $Page->rollPage = 10;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $this->assign('cmap', $cmap);
        $this->assign('dmap', $dmap);
        $this->assign('page', $Page->show());
        if ($status == 1) {//发布中
            $tpl = 'audit_new_server';
        } else if ($status == 2 || $status == 100) {//待审核--过期列表
            $tpl = 'audit_new_server_2';
        } else if ($status == 3 || $status == 4) {//驳回--取消列表
            $tpl = 'audit_new_server_3';
        }
        $this->assign('status', $status);
        $this->display($tpl);
    }

    //驳回新服
    function reject_new_server() {
        $model = new Model();
        $emailmodel = D("Dev.Sendemail");
        $config_txt = C('_config_txt_');
        $id_arr = explode(',', $_GET['id']);

        $msg = $_POST['msg'];
        if (!$msg) {
            exit(json_encode(array('code' => 0, 'msg' => '请填写原因')));
        }
        $where = array(
            'id' => array('in', $id_arr),
            'status' => 2
        );
        $data = array(
            'reject_reason' => $msg,
            'update_tm' => time(),
            'status' => 3,
            'da_order' => 4
        );
        $list = $model->table('sj_new_server')->where($where)->field('pack_name,new_server_name,dev_id')->select();

        $ret = $model->table('sj_new_server')->where($where)->save($data);

        if ($ret) {

            $tm = date("Y-m-d H:i:s", time());
            $package = array();
            $dev_id = array();
            foreach ($list as $v) {
                $package[] = $v['pack_name'];
                $dev_id[] = $v['dev_id'];
                update_soft_status(array('server_status' => 3), $v['pack_name']);
            }
            if ($package) {
                $where = array('package' => array('in', $package));
                //获取软件名称
                $soft_info = get_table_data($where, "sj_soft", "package", "package,softname");
            }
            if ($dev_id) {
                //开发者信息
                $where = array('dev_id' => array('in', $dev_id));
                $dev_list = get_table_data($where, "pu_developer", "dev_id", "dev_id,email,dev_name");
            }
            foreach ($list as $k => $v) {
                if ($v['dev_id']) {
                    //提醒
                    $search = array("softname", "tm", "new_server_name", "msg");
                    $replace = array($soft_info[$v['pack_name']]['softname'], $tm, $v['new_server_name'], $msg);
                    $msg2 = str_replace($search, $replace, $config_txt['new_server_reject']);
                    $emailmodel->dev_remind_add($v['dev_id'], $msg2);
                    //发送邮件提醒
                    $subject = $config_txt['new_server_pass_subject'];
                    $search2 = array("devname", "softname", "tm", "new_server_name", "msg");
                    $replace2 = array($dev_list[$v['dev_id']]['dev_name'], $soft_info[$v['pack_name']]['softname'], $tm, $v['new_server_name'], $msg);
                    $email_cont = str_replace($search2, $replace2, $config_txt['new_server_reject_txt']);
                    $emailmodel->realsend($dev_list[$v['dev_id']]['email'], $dev_list[$v['dev_id']]['dev_name'], $subject, $email_cont);
                    /////提醒结束	
                }
                $this->writelog("驳回了新服名称为{$v['new_server_name']}包名为{$v['pack_name']}。驳回原因：{$msg}", 'sj_new_server', $v['pack_name'], __ACTION__,'','edit');
            }
            exit(json_encode(array('code' => 1, 'msg' => $id_arr)));
        } else {
            exit(json_encode(array('code' => 0, 'msg' => '驳回失败')));
        }
    }

    //通过新服
    function pass_new_server() {
        $model = new Model();
        $emailmodel = D("Dev.Sendemail");
		$active_model = D('sendNum.sendNum');
        $config_txt = C('_config_txt_');
        $id_arr = explode(',', $_POST['id']);
        $where = array(
            'id' => array('in', $id_arr),
            'status' => 2
        );
        $data = array(
            'update_tm' => time(),
            'pass_tm' => time(),
            'status' => 1,
            'da_order' => 2
        );
        $list = $model->table('sj_new_server')->where($where)->field('id,pack_name,new_server_name,dev_id,up_file_path')->select();
        $ret = $model->table('sj_new_server')->where($where)->save($data);
        if ($ret) {
            $tm = date("Y-m-d H:i:s", time());
            $package = array();
            $dev_id = array();
            foreach ($list as $v) {
                $package[] = $v['pack_name'];
                $dev_id[] = $v['dev_id'];
            }
            if ($package) {
                //获取软件名称
                $where = array('package' => array('in', $package));
                $soft_info = get_table_data($where, "sj_soft", "package", "package,softname");
            }
            if ($dev_id) {
                //开发者信息
                $where = array('dev_id' => array('in', $dev_id));
                $dev_list = get_table_data($where, "pu_developer", "dev_id", "dev_id,email,dev_name");
            }
			
            foreach ($list as $k => $v) {
				//V6.2激活码添加 添加到表里面
				if($v['up_file_path'])
				{
					$file_path = UPLOAD_PATH.$v['up_file_path'];
					$new_id=$v['id'];
					$shili = fopen($file_path, "r");  //打开文本
					while (!feof($shili)) {      //判断是否到了文件最后的函数
						$shi = fgets($shili, 1024);    //读取其中的数据
						$str .= $shi . ",";
					}
					$str_arr = str_replace("\r\n", '', $str);
					$str_arrs = substr($str_arr, 0, strlen($str_arr) - 1);
					$code_arrs = explode(',', $str_arrs);
					foreach ($code_arrs as $key => $val) {
						if (trim($val) != "") {
							$code_arr[$key] = $val;
						}
					}
					if($code_arr)
					{
						$active_model->create_newserver_activecode($new_id);
						$code_arr = array_chunk($code_arr,500);
						//激活码入口
						foreach ($code_arr as $key => $val) {
							if ($val) {
								$sql_str = '';
								$crete_time=time();
								foreach($val as $v){
									$sql_str .= ",('{$crete_time}','{$v}',0)";
								}
								$sql_str =  substr($sql_str,1);
								$active_model->add_new_server_num($sql_str, $new_id);
							}
						}
					}
				}	
                update_soft_status(array('server_status' => 1), $v['pack_name']);
                if ($v['dev_id']) {
                    //提醒
                    $search = array("softname", "tm", "new_server_name");
                    $replace = array($soft_info[$v['pack_name']]['softname'], $tm, $v['new_server_name']);
                    $msg = str_replace($search, $replace, $config_txt['new_server_pass']);
                    $emailmodel->dev_remind_add($v['dev_id'], $msg);
                    //发送邮件提醒
                    $subject = $config_txt['new_server_pass_subject'];
                    $search2 = array("devname", "softname", "tm", "new_server_name");
                    $replace2 = array($dev_list[$v['dev_id']]['dev_name'], $soft_info[$v['pack_name']]['softname'], $tm, $v['new_server_name']);
                    $email_cont = str_replace($search2, $replace2, $config_txt['new_server_pass_txt']);
                    $emailmodel->realsend($dev_list[$v['dev_id']]['email'], $dev_list[$v['dev_id']]['dev_name'], $subject, $email_cont);
                    /////提醒结束
                }
                $this->writelog("通过了新服名称为{$v['new_server_name']}包名为{$v['pack_name']}。", 'sj_new_server', $v['pack_name'], __ACTION__,'','edit');
            }
            if ($ret) {
                $table = 'sj_new_server';
                $field = 'pos';
                $where = "status=1";
                $extent_id = $ret;
                $target_rank = 1;

                $where_rank = array(
                    'status' => 1,
                    'begin' => array('exp', '>= UNIX_TIMESTAMP(CURDATE())'),
                    'server_type' => 1
                );

                //更新排序
                $param = $this->_updateRankInfo($table, $field, $extent_id, $where_rank, $target_rank);
            }
            exit(json_encode(array('code' => 1, 'msg' => $id_arr)));
        } else {
            exit(json_encode(array('code' => 0, 'msg' => '通过失败')));
        }
    }

    //撤销新服
    function revoke_new_server() {
        $model = new Model();
        $id_arr = explode(',', $_POST['id']);
        $where = array(
            'id' => array('in', $id_arr),
            'status' => 3
        );
        $data = array(
            'update_tm' => time(),
            'status' => 2,
            'da_order' => 3
        );
        $list = $model->table('sj_new_server')->where($where)->field('pack_name,new_server_name')->select();
        $ret = $model->table('sj_new_server')->where($where)->save($data);
        if ($ret) {
            foreach ($list as $k => $v) {
                update_soft_status(array('server_status' => 2), $v['pack_name']);
                $this->writelog("撤销了新服名称为{$v['new_server_name']}包名为{$v['pack_name']}。", 'sj_new_server', $v['pack_name'], __ACTION__,'','edit');
            }
            exit(json_encode(array('code' => 1, 'msg' => $id_arr)));
        } else {
            exit(json_encode(array('code' => 0, 'msg' => '撤销失败')));
        }
    }

    //排序
    public function new_server_sequence() {
        if (isset($_GET)) {
            $table = 'sj_new_server';
            $field = 'pos';
            $where = "status=1";
            $extent_id = (int) $_GET['id'];
            $target_rank = (int) $_GET['pos'];
            $old_pos = $_GET['old_pos'] == 0 ? 1 : $_GET['old_pos'];

            if($_GET['type']==2){
                $model = M('');
                $where_rank = array(
                    'id' => $extent_id
                );
                $data = array('pos'=>$target_rank,'update_tm'=>time());
                $res = $model->table('sj_new_server')->where($where_rank)->save($data);
                $param = $res;
            }else{
                $where_rank = array(
                    'status' => 1,
                    'begin' => array('exp', '>= UNIX_TIMESTAMP(CURDATE())'),
                    'server_type' => 1
                );
                //更新排序
                $param = $this->_updateRankInfo($table, $field, $extent_id, $where_rank, $target_rank);
            }
            $this->writelog("新服排序：把id为{$extent_id}从【" . $old_pos . "】调整到【{$target_rank}】", 'sj_new_server', $extent_id, __ACTION__,'','edit');
            exit(json_encode($param));
        }
    }

    public function get_softname() {
        $model = new Model();
        $package = trim($_GET['package']);
        $softname_result = $model->table('sj_soft')->where(array('package' => $package, 'hide' => array('in', array(1,1024)), 'status' => 1))->field('softname,category_id')->order('softid DESC')->limit(1)->select();
        $my_category = substr($softname_result[0]['category_id'], 1, -1);

        $category_result = $model->table('sj_category')->where(array('category_id' => $my_category, 'status' => 1))->select();

        $data = array(
            'soft_name' => $softname_result[0]['softname'],
            'category_name' => $category_result[0]['name']
        );
        if ($softname_result) {
            echo json_encode($data);
            return json_encode($data);
        } else {
            echo 2;
            return 2;
        }
    }
	//礼包追加文件 
	function gift_reseed(){
		$active_model = D('sendNum.sendNum');
		if($_POST){
			//如果没有上传激活码就是编辑分发平台个数
			if(empty($_POST['file_path'])){
				$res = $active_model-> update_platform_num();
				if($res['code'] == 1){
					$this->writelog("修改了礼包id为".$_POST['active_id']."的活动礼包分发平台个数：安智市场：{$_POST['market_conf_cnt']} 安卓游戏：{$_POST['game_conf_cnt']} SDK:{$_POST['sdk_conf_cnt']} 论坛 :{$_POST['bbs_conf_cnt']}",'sendnum_active', $_POST['active_id'], __ACTION__,'','edit');
				}
			}else{
				$res = $active_model-> post_gift_reseed();
				if($res['error'] == ''){
					$this->writelog("追加了礼包id为".$_POST['active_id']."的活动礼包",'sendnum_active', $_POST['active_id'], __ACTION__,'','edit');
				}
			}
		}else{		
			$res = $active_model->get_gift_code();
		}
		exit(json_encode($res));		
	}
	//新服追加文件 
	function new_server_reseed(){
		$active_model = D('sendNum.sendNum');
		$res = $active_model->get_new_server_code();
		exit(json_encode($res));		
	}



  // 新闻推荐通过输入数字来变更排序
    public function recommend_change_rank() {
        $model = M();
        $count=1000;
        if($_POST){
            $field = 'recommend_rank';
            $id = $_POST['id_of_change_rank'];
            $new_rank = $_POST['new_rank'];
            if ($new_rank > $count || $new_rank < 1) {
                $this->error('输入的排序位置有误！');
            }
            $where = array(
                'id' => $id,
                'status' => 2,
            );
            $find = $model->table('sj_olgame_news')->where($where)->find();
            if (!$find)
                $this->error('未找到此记录！');
            $old_rank = $find[$field];
            // 如果old_rank和new_rank相等，直接返回
            if ($old_rank == $new_rank){
                 $this->error('变更排序失败！当前排序值与原排序值相等');         
            }
            $data_rank[$field] = $new_rank;
            $data_rank['recommend_update_time'] = time();
            $ret = $model->table('sj_olgame_news')->where($where)->save($data_rank);
            if (!$ret){
                $this->error('变更排序失败！请重新再试');
            }else{
                $this->writelog("修改id如下：{$id},把{$old_rank}改为{$new_rank}",'sj_olgame_news', $id, __ACTION__,'','edit');
                $this->success('变更排序成功！');
            }
                
        }
        if ($_GET) {
            $id = $_GET['id'];
            $this->assign('id', $id);
            $this->assign('count', $count);
            $this->display("change_rank");
        }
    }
	//提取礼包--显示
	function p_export_gift(){
		$active_model = D('sendNum.sendNum');
		$active_id = $_GET['id'];
		$where = array(
			'active_id' => $active_id,
		);
		$list = $active_model->table('sendnum_number_export')->where($where)->order('add_tm asc')->select();
		$this->assign('active_name', $_GET['active_name']);
		$this->assign('list', $list);	
		//礼包总量
		$num = $active_model->table('sendnum_number_'.$active_id)->count();
		$this->assign('num', $num);	
		$surplus_num = $active_model -> get_gift_surplus($active_id);
		$this->assign('surplus_num', $surplus_num);	
		//礼包详情
		$sendnum_list = $active_model -> get_active_data($active_id);
		$this->assign('sendnum_list', $sendnum_list);	
		$bbs_res_num = $sendnum_list['bbs_conf_cnt']-$sendnum_list['cnt1'];
		$this->assign('bbs_res_num', $bbs_res_num);		
		$gm_res_num = $sendnum_list['game_conf_cnt']-$sendnum_list['cnt2'];
		$this->assign('gm_res_num', $gm_res_num);
		$az_res_num = $sendnum_list['market_conf_cnt']-$sendnum_list['cnt4'];		
		$this->assign('az_res_num', $az_res_num);
		$sdk_res_num = $sendnum_list['sdk_conf_cnt']-$sendnum_list['cnt8'];
		$this->assign('sdk_res_num', $sdk_res_num);	
		$weixin_res_num = $sendnum_list['weixin_conf_cnt']-$sendnum_list['cnt16'];		
		$this->assign('weixin_res_num', $weixin_res_num);	
		$this->display();
	}
	//提取礼包--提交
	function p_export_gift_do(){
		if($_GET['down_file']){
			//下载详情
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' ."sendNum_".$_GET['active_id']."_".$_GET['id']. '.csv"');
			header('Cache-Control: max-age=0');
			$file = C('ACTIVITY_CSV').base64_decode($_GET['file_path']);
			readfile($file);exit;
			// if (!file_exists($file)) {
				// exit;
			// }
			// $fp = fopen($file, 'r');
			// $out_fp = fopen('php://output', 'a');
			// while (!feof($fp)) {
				// fputs($out_fp, fgets($fp));
			// }
			// fclose($fp);
			// fclose($out_fp);
			// exit;
		}
		$active_model = D('sendNum.sendNum');		
		$active_id = $_POST['active_id'];
		$surplus_num = $active_model -> get_gift_surplus($active_id);
		$bbs_num = $_POST['bbs_num'] && $_POST['bbs_num'] != 'undefined' ? $_POST['bbs_num'] : 0;
		$gm_num = $_POST['gm_num'] && $_POST['gm_num'] != 'undefined' ? $_POST['gm_num'] : 0;
		$az_num = $_POST['az_num'] && $_POST['az_num'] != 'undefined' ? $_POST['az_num'] : 0;
		$sdk_num = $_POST['sdk_num'] && $_POST['sdk_num'] != 'undefined' ? $_POST['sdk_num'] : 0;
		$weixin_num = $_POST['weixin_num'] && $_POST['weixin_num'] != 'undefined' ? $_POST['weixin_num'] : 0;
		if(($bbs_num+$gm_num+$az_num+$sdk_num+$weixin_num)>$surplus_num){
			exit(json_encode(array('code'=>0,'msg'=>"激活码超出提取数量")));
		}
		if(($bbs_num+$gm_num+$az_num+$sdk_num+$weixin_num)==0){
			exit(json_encode(array('code'=>0,'msg'=>"请填写正确数量")));
		}
		$data = array(
			1 => array(
				'num' => $bbs_num,
				'msg' => '论坛',
			),
			2 => array(
				'num' => $gm_num,
				'msg' => '安卓游戏',
			),
			4 => array(
				'num' => $az_num,
				'msg' => '市场礼包',
			),
			8 => array(
				'num' => $sdk_num,
				'msg' => 'sdk礼包',
			),
			16 => array(
				'num' => $weixin_num,
				'msg' => '微信',
			)
		);
		$res = $active_model -> export_gift_file($active_id,$data);
		if($res['id']){
			$this->writelog("礼包管理【提取礼包】提取的active_id:".$active_id."提取id为".$res,'sendnum_number_export', $res, __ACTION__,'','add');
			$arr = array(
				'code'=>1,
				'msg'=>"提取成功",
				'file_path' => $res['file_path'],
				'id' => $res['id']
			);
			exit(json_encode($arr));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>"提取失败")));
		}				
	}
}
