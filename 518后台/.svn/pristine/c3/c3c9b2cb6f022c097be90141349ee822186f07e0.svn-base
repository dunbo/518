<?php
/**
  * Desc:   市场配置管理
  * @author Sun Tao<suntao@anzhi.com>
  * @final  2014-04-28
*/
class ConfigAction extends CommonAction{

    /**
      * Desc:   市场提醒设置
      * @author Sun Tao<suntao@anzhi.com>
    */
    function MarketRemind(){
        $model = M('pu_config');
        $res = $model->table('pu_config')->where("config_type='MARKET_UPDATETIME'")->find();
        //$rs = unserialize($res['configcontent']);
        $rs = json_decode($res['configcontent'],true);
        $old = $res['configcontent'];

        $newrs['denglu'] = $rs['denglu']/3600;
        $newrs['qiehuan'] = $rs['qiehuan']/3600;
        $newrs['zi'] = $rs['zi']/3600;

        $this->assign('rs',$newrs);

        if($this->isAjax())
        {
            $denglu = (int)$_POST['denglu'];
            $qiehuan = (int)$_POST['qiehuan'];
            $zi = (int)$_POST['zi'];

            $tmpdata = array(
                'denglu'=>$denglu*3600,
                'qiehuan'=>$qiehuan*3600,
                'zi'=>$zi*3600
            );
            //$data['configcontent']=serialize($tmpdata);
            $data['configcontent']=json_encode($tmpdata);
            $model->table('pu_config')->where("config_type='MARKET_UPDATETIME'")->save($data);
            $this->writelog("市场配置-市场提醒设置，修改了配置,从".$old."改成了".$data['configcontent'], 'pu_config','MARKET_UPDATETIME',__ACTION__ ,"","edit");
            echo 1;exit(0);
        }


        $this->display();
    }
    /**
      * Desc:   第三方合作管理
      * @author Sun Tao<suntao@anzhi.com>
     	功能开关集合 added by shitingting
    */
    function ThirdParty_show()
    {
        $model = M('pu_config');
        //第三方
        $rs = $model->table('pu_config')->where("config_type='THIRD_PARTY'")->find();
        $old = $rs['configcontent'];
		$this->assign('rs',json_decode($rs['configcontent'],true));
        
        //市场自有 V5.5分享软件直接下载
        $rs = $model->table('pu_config')->where("config_type='ANZHI_OWN'")->find();
        $old2 = $rs['configcontent'];
		$this->assign('res',json_decode($rs['configcontent'],true));
		
		//显示4.3版本配置页面
		//$d = $model->table('pu_config')->where("config_type='4.2VER_DOWNLOAD'")->find();
		$p = $model ->table('pu_config') -> where("config_type='4.2VER_DISPLAY'")->select();
		$f = $model ->table('pu_config') -> where("config_type='VER_FLOW'")->select();
		$this->assign('fow', $f[0]);         //零流量更新
		$this->assign('dis', $p[0]);		// 4.3版本日排行显示控制
		//$this->assign('dow', $d[0]);		// 4.3版本【更新/下载】 页面推送信息是否下载
		
		//4.4配置页
		$category = M('category');
		$where44 = array(
				'config_type' => 'ireader_display',
				'status' => 1,
			);
		$ireader_display = $model->table('pu_config')->where($where44)->find();
		$this->assign('ireader_display', $ireader_display['configcontent']);
		$double_row_switch = $model->table('pu_config')->where("config_type='double_row_switch'")->find();
		$this->assign('double_row_switch', $double_row_switch['configcontent']);
		$ireader_searchable = $category->where("category_id=3")->find();
		$this->assign('ireader_searchable', $ireader_searchable['searchable']);
		
		//对战平台插件
		$rs = $model->table('pu_config')->where("config_type='PK_Platform'")->find();
		$this->assign('fighting',json_decode($rs['configcontent'],true));
		
		//桌面LOGO小红点
		$red = $model->table('pu_config')->where("config_type='LOGO_RED'")->find();
		$this->assign('logo_red',json_decode($red['configcontent'],true));
		//对战插件显示和静默下载
		$fighting_show = $model->table('pu_config')->where("config_type='fighting_show'")->find();
		$this->assign('fighting_show',json_decode($fighting_show['configcontent'],true));
		
		$fighting_sli_down = $model->table('pu_config')->where("config_type='fighting_sli_down'")->find();
		$this->assign('fighting_sli_down',json_decode($fighting_sli_down['configcontent'],true));
		//5.7及以上手机清理 卸载残留开关提醒
		$upload_cleanup = $model->table('pu_config')->where("config_type='UNLOAD_CLEANUP'")->find();
		$this->assign('upload_cleanup',json_decode($upload_cleanup['configcontent'],true));
		//5.7及以上手机清理 手机清理百度版
		$cleanup_baidu = $model->table('pu_config')->where("config_type='CLEANUP_BAIDU'")->find();
		$this->assign('cleanup_baidu',json_decode($cleanup_baidu['configcontent'],true));
		
		//5.7及以上手机清理 桌面深度清理跳转目标
		$deep_cleanup_jump = $model->table('pu_config')->where("config_type='DEEP_CLEANUP_JUMP'")->find();
		$this->assign('deep_cleanup_jump',json_decode($deep_cleanup_jump['configcontent'],true));
		
		//6.0及以上首页双列显示
		$double_row_switch_6 = $model->table('pu_config')->where("config_type='double_row_switch_6'")->find();
		$this->assign('double_row_switch_6',json_decode($double_row_switch_6['configcontent'],true));
		
		//全面体验是否显示
		$all_exp_show = $model->table('pu_config')->where("config_type='all_exp_show'")->find();
		$this->assign('all_exp_show',json_decode($all_exp_show['configcontent'],true));
		
		//兑吧合作  v6.2
		$exchange_coop = $model->table('pu_config')->where("config_type='exchange_coop'")->find();
		$this->assign('exchange_coop',json_decode($exchange_coop['configcontent'],true));
		
		//推广合作标识 v6.3
		$spread_coop_logo = $model->table('pu_config')->where("config_type='spread_coop_logo'")->find();
		$this->assign('spread_coop_logo',json_decode($spread_coop_logo['configcontent'],true));
		//外部内容合作
		$content_coop_switch = $model->table('pu_config')->where("config_type='content_coop_switch'")->find();
		$this->assign('content_coop_switch',json_decode($content_coop_switch['configcontent'],true));
		//内容合作频道桌面快捷方式是否创建开关
		$content_coop_channel = $model->table('pu_config')->where("config_type='content_coop_channel'")->find();
		$this->assign('content_coop_channel',json_decode($content_coop_channel['configcontent'],true));
		
		//红包助手
		$red_package_switch = $model->table('pu_config')->where("config_type='red_package_switch'")->find();
		$this->assign('red_package_switch',json_decode($red_package_switch['configcontent'],true));
		//红包助手-桌面快捷方式是否创建开关
		$red_package_desktop = $model->table('pu_config')->where("config_type='red_package_desktop'")->find();
		$this->assign('red_package_desktop',json_decode($red_package_desktop['configcontent'],true));

		// //今日头条-闪屏合作
		// $headline_splash_screen_coop = $model->table('pu_config')->where("config_type='headline_splash_screen_coop'")->find();
		// $this->assign('headline_splash_screen_coop',json_decode($headline_splash_screen_coop['configcontent'],true));
		//手机清理-腾讯手机管家(6.4.1+)
		$tencent_phone_steward_switch = $model->table('pu_config')->where("config_type='tencent_phone_steward_switch'")->find();
		$this->assign('tencent_phone_steward_switch',json_decode($tencent_phone_steward_switch['configcontent'],true));
		//手机清理-手机管家-桌面深度清理跳转：
		$phone_steward_desktop_jump = $model->table('pu_config')->where("config_type='phone_steward_desktop_jump'")->find();
		$this->assign('phone_steward_desktop_jump',json_decode($phone_steward_desktop_jump['configcontent'],true));
		//手机清理-手机管家-软件残留
		$phone_steward_software_residual = $model->table('pu_config')->where("config_type='phone_steward_software_residual'")->find();
		$this->assign('phone_steward_software_residual',json_decode($phone_steward_software_residual['configcontent'],true));
		// //手机清理-手机管家-系统内存不足
		// $phone_steward_system_memory_insufficient = $model->table('pu_config')->where("config_type='phone_steward_system_memory_insufficient'")->find();
		// $this->assign('phone_steward_system_memory_insufficient',json_decode($phone_steward_system_memory_insufficient['configcontent'],true));

		//手机清理-手机管家-系统内存不足-提醒阈值
		$phone_steward_remind_threshold = $model->table('pu_config')->where("config_type='phone_steward_remind_threshold'")->find();
		$this->assign('phone_steward_remind_threshold',json_decode($phone_steward_remind_threshold['configcontent'],true));

		//手机清理-手机管家-系统内存不足-提醒阈值
		$phone_steward_day_popup_num = $model->table('pu_config')->where("config_type='phone_steward_day_popup_num'")->find();
		$this->assign('phone_steward_day_popup_num',json_decode($phone_steward_day_popup_num['configcontent'],true));

		//对外预约 分享页面id 
		$share_page = $model->table('pu_config')->where("config_type='share_page_id'")->find();
		$this->assign('share_page',json_decode($share_page['configcontent'],true));
		
		//v6.4.5 智友推荐
		$zhiyoo_recommend = $model->table('pu_config')->where("config_type='zhiyoo_recommend'")->field('conf_id,configcontent')->find();

		$this->assign('zhiyoo_recommend',json_decode($zhiyoo_recommend['configcontent'],true));

		//软件更新方式
		$SOFT_UPDATE_WAY = $model->table('pu_config')->where("config_type='SOFT_UPDATE_WAY'")->field('conf_id,configcontent')->find();

		$this->assign('SOFT_UPDATE_WAY',json_decode($SOFT_UPDATE_WAY['configcontent'],true));

		//快速入口(648滑动)时间间隔
		$fast_entrance_slide_interval=$model->table('pu_config')->where("config_type='fast_entrance_slide_interval'")->field('conf_id,configcontent')->find();
		$this->assign('fast_entrance_slide_interval',json_decode($fast_entrance_slide_interval['configcontent'],true));

		//轮播图样式
		$slideshow_style=$model->table('pu_config')->where("config_type='slideshow_style'")->field('conf_id,configcontent')->find();
		$this->assign('slideshow_style',json_decode($slideshow_style['configcontent'],true));

		//详情页优先展示专区（6.4.9+）
		$detail_first_show=$model->table('pu_config')->where("config_type='detail_first_show'")->field('conf_id,configcontent')->find();
		$this->assign('detail_first_show', $detail_first_show['configcontent']);

		//详情页专区只展示论坛内容（6.4.9+）
		$detail_forum_show=$model->table('pu_config')->where("config_type='detail_forum_show'")->field('conf_id,configcontent')->find();
		$this->assign('detail_forum_show', $detail_forum_show['configcontent']);
		$this->display();
	}
    
     function ThirdParty_do()
    {
	     $model = M('pu_config');
		 $category = M('category');
	    //第三方
		$tdc=$_POST['tdc'] ? $_POST['tdc'] : 0;
		$voice = $_POST['voice'] ? $_POST['voice'] : 0;
		$clear = $_POST['clear'] ? $_POST['clear'] : 0;
		
		$tmpdata = array(
			'tdc'=>$tdc,
			'voice'=>$voice,
			'clear'=>$clear,
		);
		$data['configcontent']=json_encode($tmpdata);
		$data['uptime']=time();
		$result_three=$model->table('pu_config')->where("config_type='THIRD_PARTY'")->save($data);
		
	    //市场自有 V5.5分享软件直接下载
	    $skin = $_POST['skin'] ? $_POST['skin'] : 0;
		$share_soft = $_POST['share_soft'] ? $_POST['share_soft'] : 0;
		$tmpdata1 = array(
                    'skin'=>$skin,
					'share_soft'=>$share_soft,
                );
		$data1['configcontent']=json_encode($tmpdata1);
		$data1['uptime']=time();
		$result_ziyou=$model->table('pu_config')->where("config_type='ANZHI_OWN'")->save($data1);
		
		//4.3版本配置页面
		$flow=$_POST['flow'] ? $_POST['flow'] : 0;
		$dsort=$_POST['dsort'] ? $_POST['dsort'] : 0;
		$data2['configcontent']=$flow;
		$data2['uptime']=time();
		$data3['configcontent']=$dsort;
		$data3['uptime']=time();
		$result_flow=$model->table('pu_config')->where("config_type='VER_FLOW'")->save($data2);
		$result_display=$model->table('pu_config')->where("config_type='4.2VER_DISPLAY'")->save($data3);
		
		//4.4配置页
        $ireader_display = $_POST['ireader_display'] ? $_POST['ireader_display'] : 0;
		$ireader_searchable = $_POST['ireader_searchable'] ? $_POST['ireader_searchable'] : 0;
		$double_row_switch = $_POST['double_row_switch'] ? $_POST['double_row_switch'] : 0;	
		$where44 = array(
			'config_type' => 'ireader_display',
			'status' => 1,
		);
		$ret1 = $model->table('pu_config')->where($where44)->save(array('configcontent' => $ireader_display,'uptime'=>time()));
		$ret2 = $model->table('pu_config')->where("config_type='double_row_switch'")->save(array('configcontent' => $double_row_switch,'uptime'=>time()));
		if ($ret2 !== false)
				{
					$ireader_category = array(3);
					while (!empty($ireader_category))
					{
						$cid = array_pop($ireader_category);
						$where = array(
							'category_id' => $cid,
						);
						$ret3 = $category->where($where)->save(array('searchable' => $ireader_searchable));
						if ($ret === false)
						{
							$flag = false;
						}
						$where = array(
							'parentid' => $cid,
						);
						$to_push = $category->where($where)->select();
						foreach ($to_push as $v)
						{
							array_push($ireader_category, $v['category_id']);
						}
					}
				}
		
		//桌面LOGO小红点
		$logo_red = $_POST['logo_red'] ? $_POST['logo_red'] : 0;
		$where_red = array(
		    'config_type' => 'LOGO_RED',
			'status' => 1,
		);
		$red = $model->table('pu_config')->where($where_red)->save(array('configcontent' => $logo_red,'uptime'=>time()));
		
		//对战插件显示和静默下载
		$fighting_show = $_POST['fighting_show'] ? $_POST['fighting_show'] : 0;
		$fighting_sli_down = $_POST['fighting_sli_down'] ? $_POST['fighting_sli_down'] : 0;
		
		$where_show = array(
		    'config_type' => 'fighting_show',
			'status' => 1,
		);
		$fighting_show_result = $model->table('pu_config')->where($where_show)->save(array('configcontent' => $fighting_show,'uptime'=>time()));
				$where_sli_down = array(
		    'config_type' => 'fighting_sli_down',
			'status' => 1,
		);
		$fighting_sli_down_result = $model->table('pu_config')->where($where_sli_down)->save(array('configcontent' => $fighting_sli_down,'uptime'=>time()));
		
		//5.7及以上手机清理 卸载残留开关提醒
		$upload_cleanup = $_POST['upload_cleanup'] ? $_POST['upload_cleanup'] : 0;
		$where_cleanup = array(
		    'config_type' => 'UNLOAD_CLEANUP',
			'status' => 1,
		);
		$cleanup_result = $model->table('pu_config')->where($where_cleanup)->save(array('configcontent' => $upload_cleanup,'uptime'=>time()));
		
		//5.7及以上手机清理 手机清理百度版
		$cleanup_baidu = $_POST['cleanup_baidu'] ? $_POST['cleanup_baidu'] : 0;
		$where_baidu = array(
		    'config_type' => 'CLEANUP_BAIDU',
			'status' => 1,
		);
		$cleanup_baidu_result = $model->table('pu_config')->where($where_baidu)->save(array('configcontent' => $cleanup_baidu,'uptime'=>time()));
		
		//5.7及以上手机清理 桌面深度清理跳转目标
		$deep_cleanup_jump = $_POST['deep_cleanup_jump'] ? $_POST['deep_cleanup_jump'] : 0;
		$where_jump = array(
		    'config_type' => 'DEEP_CLEANUP_JUMP',
			'status' => 1,
		);
		$cleanup_jump_result = $model->table('pu_config')->where($where_jump)->save(array('configcontent' => $deep_cleanup_jump,'uptime'=>time()));
		
		//6.0及以上首页双列显示开关
		$double_row_switch_6 = $_POST['double_row_switch_6'] ? $_POST['double_row_switch_6'] : 0;
		$where_double_6 = array(
		    'config_type' => 'double_row_switch_6',
			'status' => 1,
		);
		$double_row_switch_6_result = $model->table('pu_config')->where($where_double_6)->save(array('configcontent' => $double_row_switch_6,'uptime'=>time()));
		
		//全面体验是否显示
		$all_exp_show = $_POST['all_exp_show'] ? $_POST['all_exp_show'] : 0;
		$where_all_exp_show = array(
		    'config_type' => 'all_exp_show',
			'status' => 1,
		);
		$all_exp_show_result = $model->table('pu_config')->where($where_all_exp_show)->save(array('configcontent' => $all_exp_show,'uptime'=>time()));
		
		//兑吧合作
		$exchange_coop = $_POST['exchange_coop'] ? $_POST['exchange_coop'] : 0;
		$where_exchange_coop = array(
		    'config_type' => 'exchange_coop',
			'status' => 1,
		);
		$exchange_coop_result = $model->table('pu_config')->where($where_exchange_coop)->save(array('configcontent' => $exchange_coop,'uptime'=>time()));
		
		//6.3推广合作标识
		$spread_coop_logo = $_POST['spread_coop_logo'] ? $_POST['spread_coop_logo'] : 0;
		$where_spread_coop_logo = array(
		    'config_type' => 'spread_coop_logo',
			'status' => 1,
		);
		$spread_coop_logo_result = $model->table('pu_config')->where($where_spread_coop_logo)->save(array('configcontent' => $spread_coop_logo,'uptime'=>time()));		
		//外部内容合作
		$content_coop_switch = $_POST['content_coop_switch'] ? $_POST['content_coop_switch'] : 0;
		$where_content_coop_switch = array(
		    'config_type' => 'content_coop_switch',
			'status' => 1,
		);
		$content_coop_switch_result = $model->table('pu_config')->where($where_content_coop_switch)->save(array('configcontent' => $content_coop_switch,'uptime'=>time()));
		
		//内容合作频道
		$content_coop_channel = $_POST['content_coop_channel'] ? $_POST['content_coop_channel'] : 0;
		$where_content_coop_channel = array(
		    'config_type' => 'content_coop_channel',
			'status' => 1,
		);
		$content_coop_channel_result = $model->table('pu_config')->where($where_content_coop_channel)->save(array('configcontent' => $content_coop_channel,'uptime'=>time()));
		
		//红包助手
		$red_package_switch = $_POST['red_package_switch'];
		$where_red_package_switch = array(
		    'config_type' => 'red_package_switch',
			'status' => 1,
		);
		$red_package_switch_result = $model->table('pu_config')->where($where_red_package_switch)->save(array('configcontent' => $red_package_switch,'uptime'=>time()));
		
		//红包助手-桌面快捷方式是否创建开关
		$red_package_desktop = $_POST['red_package_desktop'] ? $_POST['red_package_desktop'] : 0;
		$where_red_package_desktop = array(
		    'config_type' => 'red_package_desktop',
			'status' => 1,
		);
		$red_package_desktop_result = $model->table('pu_config')->where($where_red_package_desktop)->save(array('configcontent' => $red_package_desktop,'uptime'=>time()));
		
		// //今日头条-闪屏合作
		// $headline_splash_screen_coop = $_POST['headline_splash_screen_coop'] ? $_POST['headline_splash_screen_coop'] : 0;
		// $where_headline_splash_screen_coop = array(
		//     'config_type' => 'headline_splash_screen_coop',
		// 	'status' => 1,
		// );
		// $headline_splash_screen_coop_result = $model->table('pu_config')->where($where_headline_splash_screen_coop)->save(array('configcontent' => $headline_splash_screen_coop,'uptime'=>time()));
		//手机清理-腾讯手机管家(6.4.1+)
		$tencent_phone_steward_switch = $_POST['tencent_phone_steward_switch'] ? $_POST['tencent_phone_steward_switch'] : 0;
		$where_tencent_phone_steward_switch = array(
		    'config_type' => 'tencent_phone_steward_switch',
			'status' => 1,
		);
		$tencent_phone_steward_switch_result = $model->table('pu_config')->where($where_tencent_phone_steward_switch)->save(array('configcontent' => $tencent_phone_steward_switch,'uptime'=>time()));

		//手机清理-手机管家-桌面深度清理跳转
		$phone_steward_desktop_jump = $_POST['phone_steward_desktop_jump'] ? $_POST['phone_steward_desktop_jump'] : 0;
		$where_phone_steward_desktop_jump = array(
		    'config_type' => 'phone_steward_desktop_jump',
			'status' => 1,
		);
		$phone_steward_desktop_jump_result = $model->table('pu_config')->where($where_phone_steward_desktop_jump)->save(array('configcontent' => $phone_steward_desktop_jump,'uptime'=>time()));
		
		//手机清理-手机管家-软件残留：
		$phone_steward_software_residual = $_POST['phone_steward_software_residual'] ? $_POST['phone_steward_software_residual'] : 0;
		$where_phone_steward_software_residual = array(
		    'config_type' => 'phone_steward_software_residual',
			'status' => 1,
		);
		$phone_steward_software_residual_result = $model->table('pu_config')->where($where_phone_steward_software_residual)->save(array('configcontent' => $phone_steward_software_residual,'uptime'=>time()));
		// //手机清理-手机管家-系统内存不足：
		// $phone_steward_system_memory_insufficient = $_POST['phone_steward_system_memory_insufficient'] ? $_POST['phone_steward_system_memory_insufficient'] : 0;
		// $where_phone_steward_system_memory_insufficient = array(
		//     'config_type' => 'phone_steward_system_memory_insufficient',
		// 	'status' => 1,
		// );

		// $phone_steward_system_memory_insufficient_result = $model->table('pu_config')->where($where_phone_steward_system_memory_insufficient)->save(array('configcontent' => $phone_steward_system_memory_insufficient,'uptime'=>time()));

		//手机清理-手机管家-系统内存不足-提醒阈值
		$phone_steward_remind_threshold = $_POST['phone_steward_remind_threshold'] ? $_POST['phone_steward_remind_threshold'] : 0;
		if(!(is_numeric($phone_steward_remind_threshold)&&$phone_steward_remind_threshold==(int)$phone_steward_remind_threshold) || $phone_steward_remind_threshold <0 || $phone_steward_remind_threshold >100){
			$this->error("手机清理-手机管家-系统内存不足-提醒阈值请填写小于等于100的正整数");
		}
		$where_phone_steward_remind_threshold = array(
		    'config_type' => 'phone_steward_remind_threshold',
			'status' => 1,
		);

		$phone_steward_remind_threshold_result = $model->table('pu_config')->where($where_phone_steward_remind_threshold)->save(array('configcontent' => $phone_steward_remind_threshold,'uptime'=>time()));
		
		// 手机清理-手机管家-系统内存不足-日弹窗次数
		$phone_steward_day_popup_num = $_POST['phone_steward_day_popup_num'] ? $_POST['phone_steward_day_popup_num'] : 0;
		if(!(is_numeric($phone_steward_day_popup_num)&&$phone_steward_day_popup_num==(int)$phone_steward_day_popup_num) || $phone_steward_day_popup_num <0){
			$this->error("手机清理-手机管家-系统内存不足-日弹窗次数请填写正整数");
		}
		$where_phone_steward_day_popup_num = array(
		    'config_type' => 'phone_steward_day_popup_num',
			'status' => 1,
		);

		$phone_steward_day_popup_num_result = $model->table('pu_config')->where($where_phone_steward_day_popup_num)->save(array('configcontent' => $phone_steward_day_popup_num,'uptime'=>time()));
		//对外预约 分享页面id 
		$share_page_id = $_POST['share_page_id'] ? $_POST['share_page_id'] : 0;
		//根据页面id获取分享的标题、分享文案、分享图片地址
		$page_where=array(
			'ap_id' => $share_page_id,
		);
		$activity_result = $model->table('sj_activity_page')-> where($page_where)->find();
		
		$save_share=array(
			'share_page_id' => $share_page_id,
			'share_url' => $activity_result['ap_link']."?ap_id=".$activity_result['ap_id'],
			'share_title' => $activity_result['title'],
			'share_text' => $activity_result['share_text'],
			'share_icon' => IMGATT_HOST_CDN.$activity_result['rule_pic'],
		);
		$where_share_page = array(
		    'config_type' => 'share_page_id',
			'status' => 1,
		);
		$save_array=array(
			'configcontent' => json_encode($save_share),
			'uptime' => time(),
		);
		$share_page_result = $model->table('pu_config')->where($where_share_page)->save($save_array);
		
		//v6.4.5 智友推荐
		$where = array(
			'config_type' => 'zhiyoo_recommend',
			'status' => 1,
		);
		$zhiyoo_recommend = $_POST['zhiyoo_recommend']?$_POST['zhiyoo_recommend']:0;
		$save = array(
			'configcontent' => $zhiyoo_recommend,
			'uptime' => time(),
		);
		$zhiyoo_res = $model->table('pu_config')->where($where)->save($save);

		//软件更新方式开关
		$where = array(
			'config_type' => 'SOFT_UPDATE_WAY',
			'status' => 1,
		);
		$SOFT_UPDATE_WAY = $_POST['SOFT_UPDATE_WAY']?$_POST['SOFT_UPDATE_WAY']:0;
		$save = array(
			'configcontent' => $SOFT_UPDATE_WAY,
			'uptime' => time(),
		);
		$SOFT_UPDATE_WAY_res = $model->table('pu_config')->where($where)->save($save);

		//快速入口(648滑动)时间间隔
		$where = array(
			'config_type' => 'fast_entrance_slide_interval',
			'status' => 1,
		);
		$fast_entrance_slide_interval = $_POST['fast_entrance_slide_interval']?$_POST['fast_entrance_slide_interval']:0;
		$save = array(
			'configcontent' => $fast_entrance_slide_interval,
			'uptime' => time(),
		);
		$fast_entrance_slide_interval_res = $model->table('pu_config')->where($where)->save($save);

		//轮播图样式
		$where = array(
			'config_type' => 'slideshow_style',
			'status' => 1,
		);
		$slideshow_style = $_POST['slideshow_style']?$_POST['slideshow_style']:0;
		$save = array(
			'configcontent' => $slideshow_style,
			'uptime' => time(),
		);
		$slideshow_style_res = $model->table('pu_config')->where($where)->save($save);

		//详情页优先展示专区（6.4.9+）
		$where = array(
			'config_type' => 'detail_first_show',
			'status' => 1,
		);
		$detail_first_show = $_POST['detail_first_show']?$_POST['detail_first_show']:0;
		$save = array(
			'configcontent' => $detail_first_show,
			'uptime' => time(),
		);
		$detail_first_show_res = $model->table('pu_config')->where($where)->save($save);

		//详情页专区只展示论坛内容（6.4.9+）
		$where = array(
			'config_type' => 'detail_forum_show',
			'status' => 1,
		);
		$detail_forum_show = $_POST['detail_forum_show']?$_POST['detail_forum_show']:0;
		$save = array(
			'configcontent' => $detail_forum_show,
			'uptime' => time(),
		);
		$detail_forum_show_res = $model->table('pu_config')->where($where)->save($save);
		
		if($result_three!==false&&$result_ziyou!==false&&$result_display!==false&&$result_flow!==false&&$ret1!==false&&$ret2!==false&&$ret3!==false&&$ret_fighting!==false&&$red!==false&&$fighting_show_result!==false&&$fighting_sli_down_result!==false&&$cleanup_result!==false&&$cleanup_baidu_result!==false&&$cleanup_jump_result!==false&&$double_row_switch_6_result!==false&&$all_exp_show_result!==false&&$exchange_coop_result!==false&&$spread_coop_logo_result!==false&&$content_coop_switch_result!==false&&$content_coop_channel_result!==false&&$red_package_switch_result!==false&&$red_package_desktop_result!==false&&$share_page_result!==false&&$tencent_phone_steward_switch_result!==false&&$phone_steward_desktop_jump_result!==false&&$phone_steward_software_residual_result!==false&&$phone_steward_day_popup_num_result!==false&&$phone_steward_remind_threshold_result!==false&&$zhiyoo_res!==false&&$SOFT_UPDATE_WAY_res!==false&&$fast_entrance_slide_interval_res!==false&&$slideshow_style_res!==false&&$detail_first_show_res!==false&&$detail_forum_show_res!==false)
		{
			$this->writelog("市场配置-市场功能开关-第三方合作管理，修改了配置,改成了".$data['configcontent'].",市场自由功能管理,改成了".$data1['configcontent'].",4.3版本的配置修改为零流量更新改为".$data2['configcontent'].",日排行显示改为".$data3['configcontent'].",4.4版本配置电子书显示改为".$ireader_display .",电子书搜索改为".$ireader_searchable.",首页双页显示改为".$double_row_switch.",桌面LOGO小红点修改为".$logo_red.",对战插件显示修改为".$fighting_show.",对战插件静默下载修改为".$fighting_sli_down.",卸载残留开关提醒修改为".$upload_cleanup.",手机清理百度版修改为".$cleanup_baidu.",桌面深度清理跳转目标".$deep_cleanup_jump.",6.0及以上首页双列显示".$double_row_switch_6.",全面体验是否显示".$all_exp_show.",兑吧合作开关".$exchange_coop.",推广合作标识".$spread_coop_logo.",外部内容合作".$content_coop_switch.",内容合作频道".$content_coop_channel.",红包助手".$red_package_switch_result.",红包助手-桌面快捷方式是否创建开关".$red_package_desktop_result.",分享页面id为".$share_page_id.",手机清理-腾讯手机管家(6.4.1+)".$tencent_phone_steward_switch_result.",手机清理-手机管家-桌面深度清理跳转".$phone_steward_desktop_jump_result.",手机清理-手机管家-软件残留".$phone_steward_software_residual_result.",手机清理-手机管家-系统内存不足-提醒阈值为".$phone_steward_remind_threshold.",手机清理-手机管家-系统内存不足-日弹窗次数为".$phone_steward_day_popup_num."智友推荐（6.4.5+）:{$zhiyoo_recommend},软件更新方式：{$SOFT_UPDATE_WAY},快速入口(648滑动)时间间隔：{$fast_entrance_slide_interval_res},轮播图样式：{$slideshow_style},详情页优先展示专区（6.4.9+）：{$detail_first_show},详情页专区只展示论坛内容（6.4.9+）：{$detail_forum_show_res}", 'pu_config','',__ACTION__ ,"","edit");
			$this->success('配置修改成功');
		}
		else
		{
			$this->error('配置修改失败');
		}
	}
    function RegisterWay() {
        $model = M('pu_config');
        $rs = $model->table('pu_config')->where("config_type='REGISTER_WAY'")->find();
        $register_way = $rs['configcontent'];
        
        $this->assign('register_way', $register_way);
        $this->display();
    }
    
    function editRegisterWay() {
        $register_way = trim($_GET['register_way']);
        
        $model = M('pu_config');
        $where = array(
            'config_type' => 'REGISTER_WAY',
        );
        $data = array(
            'configcontent' => $register_way,
        );
        $log = $this->logcheck($where, 'pu_config', $data, $model);
        $rs = $model->table('pu_config')->where($where)->save($data);
        if ($rs) {
            $this->writelog("安智市场-手机_市场配置_注册配置：编辑了注册配置，{$log}", 'pu_config',"REGISTER_WAY",__ACTION__ ,"","add");
            $this->success("编辑成功！");
        } else {
            $this->error("编辑失败！");
        }
    }
	//2015-04-28 热门评论配置
	function hot_comments_config(){
		$model = D('Sj.Config');
		$res = $model -> get_config('hot_comments_config');
		if($_POST){
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if($_POST['hot_num'] > 100){
				$this->error("可填写0-100的整数!");
			}
			unset($_POST['__hash__']);
			$ret = $model -> save_config('hot_comments_config','热门评论配置',$res);						
			if($ret){
				$configcontent = json_encode($_POST);
				$log  = "编辑前{$res['configcontent']},编辑后：{$configcontent}";
				$this->writelog("安智市场-手机_市场配置_注册配置：编辑了热门评论配置，{$log}", 'pu_config',"hot_comments_config",__ACTION__ ,"","edit");
				$this->success("操作成功！");
			}else{
				$this->error("操作失败！");
			}
		}else{
			$this->assign('res', json_decode($res['configcontent'],true));
			$this -> display();
		}	
	}
	//2015-04-28 市场配置_市场功能开关__高低分分辨率设置
	function high_resolution_config(){
		$model = D('Sj.Config');
		$res = $model -> get_config('high_resolution_config');
		if($_POST){
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);
			if(!$_POST['width'] || !$_POST['height']){
				$this->error("请填写正确分辨率！");
			}
			unset($_POST['__hash__']);
			$ret = $model -> save_config('high_resolution_config','高低分分辨率设置',$res);			
			if($ret){
				$configcontent = json_encode($_POST);
				$log  = "编辑前{$res['configcontent']},编辑后：{$configcontent}";
				$this->writelog("安智市场-市场配置_市场功能开关__分辨率设置：{$log}", 'pu_config',"high_resolution_config",__ACTION__ ,"","edit");
				$this->success("操作成功！");
			}else{
				$this->error("操作失败！");
			}
		}else{
			$this->assign('res', json_decode($res['configcontent'],true));
			$this -> display();
		}	
	}
	//2015-5-5 运营合作-活动管理-活动分区-活动设置
	function active_config(){
		$model = D('Sj.Config');
		$res = $model -> get_config('active_config');
		if($_POST){
			$this->assign('jumpUrl','/index.php/Sj/Activity/showActivityList');
			if(!isset($_POST['active_num']) || $_POST['active_num'] == ''){
				$this->error("请填写正确天数！");
			}				
			unset($_POST['__hash__']);
			$ret = $model -> save_config('active_config','活动设置',$res);
			if($ret){
				$configcontent = json_encode($_POST);
				$log  = "编辑前{$res['configcontent']},编辑后：{$configcontent}";
				$this->writelog("运营合作-活动管理-活动分区-活动设置：{$log}", 'pu_config',"active_config",__ACTION__ ,"","edit");
				$this->success("操作成功！");
			}else{
				$this->error("操作失败！");
			}
		}else{
			$this->assign('res', json_decode($res['configcontent'],true));
			$this -> display();
		}	
	}
	//2015-5-5 运营合作-活动管理-活动分区-活动设置
	function subscriber_config(){
		$model = D('Sj.Config');
		$res = $model -> get_config('subscriber_config');
		if($_POST){
			$this->assign('jumpUrl','/index.php/Sj/GameSubscribe/showGameorderList');
			if(!isset($_POST['subscriber_num']) || $_POST['subscriber_num'] == ''){
				echo 3;
				return;
			}else if($_POST['subscriber_num']==0){
				echo 3;
				return;
			}			
			unset($_POST['__hash__']);
			$ret = $model -> save_config('subscriber_config','预约设置',$res);
			if($ret){
				$configcontent = json_encode($_POST);
				$log  = "编辑前{$res['configcontent']},编辑后：{$configcontent}";
				$this->writelog("运营合作-游戏预约-游戏预约管理-预约设置：{$log}", 'pu_config',"subscriber_config",__ACTION__ ,"","edit");
				echo 1;
			}else{
				echo 2;
			}
		}else{
			$this->assign('res', json_decode($res['configcontent'],true));
			$this -> display();
		}	
	}
	//2015-5-13 运营位管理-市场专题管理-专题配置-专题设置
	function special_config(){
		$model = D('Sj.Config');
		$res = $model -> get_config('special_config');
		if($_POST){
			$this->assign('jumpUrl','/index.php/Sj/Systemmanage/feature');
			if(!isset($_POST['special_num']) || $_POST['special_num'] == ''){
				$this->error("请填写正确天数！");
			}	
			unset($_POST['__hash__']);
			$ret = $model -> save_config('special_config','专题设置',$res);
			if($ret){
				$configcontent = json_encode($_POST);
				$log  = "编辑前{$res['configcontent']},编辑后：{$configcontent}";
				$this->writelog("运营位管理-市场专题管理-专题配置-专题设置：{$log}", 'pu_config',"special_config",__ACTION__ ,"","edit");
				$this->success("操作成功！");
			}else{
				$this->error("操作失败！");
			}
		}else{
			$this->assign('res', json_decode($res['configcontent'],true));
			$this -> display();
		}			
	} 
	//2015-5-21 安智市场-手机——市场配置——Chrome下载地址替换  added by shitingting
	function chrome_download_replace()
	{
		$model= M("chrome_download");
		$where=array(
			'status' => 1,
		);
		// 分页
        import("@.ORG.Page");
        $limit = 10;
        $count = $model->where($where)->count();
        $page  = new Page($count, $limit);
        // 当前页数据
        $list = $model->where($where)->order("id asc")->limit($page->firstRow . ',' . $page->listRows)->select();
        // 处理list 根据包名获取软件名和地址
        foreach ($list as $key => $value) 
		{
			if(mb_strlen($value['be_replaced_url'])>50)
			{
				$list[$key]['be_short_url']=mb_substr($value['be_replaced_url'],0,50,'utf-8')."...";
			}
			else
			{
				$list[$key]['be_short_url']=$value['be_replaced_url'];
			}
			if($value['replaced_package'])
			{
				$soft_info=PublicAction::get_soft_info($value['replaced_package']);
				$arr=json_decode($soft_info,true);
				$list[$key]['soft_name']=$arr['soft_name'];
				$list[$key]['soft_url']=$arr['soft_url'];
			}
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show());
        $this->display('Sj::Config::Chrome_download::chrome_download_replace');
	}
	function add_chrome_replace()
	{
		$model= M("chrome_download");
		if($_POST)
		{
			$be_replaced_url=trim($_POST['be_replaced_url']);
			$replaced_package=trim($_POST['replaced_package']);
			$start_tm=strtotime($_POST['start_tm']);
			$end_tm=strtotime($_POST['end_tm']);
			if(!$be_replaced_url)
			{
				$this->error("请填写待替换地址");
			}
			if(!$replaced_package)
			{
				$this->error("请填写替换后包名");
			}
			if(!$start_tm)
			{
				$this->error("请填写有效时间的开始时间");
			}
			if(!$end_tm)
			{
				$this->error("请填写有效时间的结束时间");
			}
			if($start_tm > $end_tm)
			{
				$this->error("开始时间不能大于结束时间");
			}
			$where=array(
				'be_replaced_url'=>$be_replaced_url,
				'start_tm'=>array('elt',$end_tm),
				'end_tm'=>array('egt',$start_tm),
				'status'=>1,
			);
			$have_url=$model->where($where)->find();
			if($have_url)
			{
				$this->error("待替换地址在该有效时间内已经有了替换包，请重填写");
			}
			$map['be_replaced_url'] = $be_replaced_url;
			$map['replaced_package'] = $replaced_package;
			$map['start_tm'] = $start_tm;
			$map['end_tm'] = $end_tm;
			$map['create_tm'] = time();
			$map['update_tm'] = time();
			$map['status'] = 1;
			$ret = $model->add($map);
            if ($ret) 
			{
                $this->writelog("安智市场-手机-市场配置-Chrome下载地址替换：添加了id为{$ret}的内容", 'sj_chrome_download',$ret,__ACTION__ ,"","add");
                $this->success("添加成功！");
				$this->assign('jumpUrl','/index.php/Sj/Config/chrome_download_replace');
            } 
			else 
			{
                $this->error("添加失败！");
            }
		}
		else
		{
			$this->display('Sj::Config::Chrome_download::add_chrome_replace');
		}
	}
	function edit_chrome_replace()
	{
		$model= M("chrome_download");
		if($_POST)
		{
			$id = $_POST['id'];
            $map = array();
			$where=array(
				'id'=>$id,
			);
			$be_replaced_url=trim($_POST['be_replaced_url']);
			$replaced_package=trim($_POST['replaced_package']);
			$start_tm=strtotime($_POST['start_tm']);
			$end_tm=strtotime($_POST['end_tm']);
			if(!$be_replaced_url)
			{
				$this->error("请填写待替换地址");
			}
			if(!$replaced_package)
			{
				$this->error("请填写替换后包名");
			}
			if(!$start_tm)
			{
				$this->error("请填写有效时间的开始时间");
			}
			if(!$end_tm)
			{
				$this->error("请填写有效时间的结束时间");
			}
			if($start_tm > $end_tm)
			{
				$this->error("开始时间不能大于结束时间");
			}
			$where_have=array(
				'be_replaced_url'=>$be_replaced_url,
				'start_tm'=>array('elt',$end_tm),
				'end_tm'=>array('egt',$start_tm),
				'status'=>1,
				'id'=>array('neq',$id),
			);
			$have_url=$model->where($where_have)->find();
			if($have_url)
			{
				$this->error("待替换地址在该有效时间内已经有了替换包，请重填写");
			}
			$map['be_replaced_url'] = $be_replaced_url;
			$map['replaced_package'] = $replaced_package;
			$map['start_tm'] = $start_tm;
			$map['end_tm'] = $end_tm;
			$map['update_tm'] = time();
			$log = $this->logcheck($where, 'sj_chrome_download', $map, $model);
			$ret = $model->where($where)->save($map);
			if ($ret) 
			{
				$this->writelog("安智市场-手机-市场配置-Chrome下载地址替换：编辑了id为{$id}的内容，{$log}", 'sj_chrome_download',$id,__ACTION__ ,"","edit");
				$this->success("编辑成功！");
			} 
			else 
			{
				$this->error("编辑失败！");
			}
		}
		else
		{
			$where['id']=$_GET['id'];
			$where['status']=1;
			$result=$model->where($where)->find();
			if($result['replaced_package'])
			{
				$soft_info=PublicAction::get_soft_info($result['replaced_package']);
				$arr=json_decode($soft_info,true);
				$result['soft_name']=$arr['soft_name'];
				$result['soft_url']=$arr['soft_url'];
			}
            $this->assign('list', $result);
            $this->display('Sj::Config::Chrome_download::edit_chrome_replace');
		}
	}
	function del_chrome_replace()
	{
		$model= M("chrome_download");
        $id = $_GET['id'];
        $where = array(
            'id' => $id,
            'status' => 1,
        );
        $map = array('status' => 0, 'update_tm' => time());
        $ret = $model->where($where)->save($map);
        if ($ret) 
		{
            $this->writelog("安智市场-手机-市场配置-Chrome下载地址替换：删除了id为{$id}的内容", 'sj_chrome_download',$id,__ACTION__ ,"","del");
            $this->success("删除成功");
        } 
		else 
		{
            $this->error("删除失败");
        }
	}
}
