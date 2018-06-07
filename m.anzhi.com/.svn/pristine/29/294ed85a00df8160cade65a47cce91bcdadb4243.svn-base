<?php
/**
 * @Author:      czy
 * @DateTime:    2015-10-29 14:45:30
 * @Description: 魔秀桌面列表接口
 * 				 数据类型分为增量
 */
ini_set("display_errors", 1);
error_reporting(1);

class mxlist{
	public $params;
	public $type_id;
	public $pre_url;
	public function __construct($param){
		$this->params = $param;
		$this->pre_url = getIconHost();
	}
	public function getData(){
		$ip_addr = array('106.2.223.26'=>1,'218.241.82.226'=>1,'101.251.251.197'=>1,'101.251.251.213'=>1);
		$ip = onlineip();
		if (!isset($ip_addr[$ip])) {
			exit('ip is error');
		}
		
		$return = array();
		$offset = $this->params['start'] ? (int) $this->params['start'] : 0;
		$limit = $this->params['limit'] ? (int) $this->params['limit'] : 30;
		if ($limit>30) $limit = 30;
		$debug = $this->params['debug'];
		$debug = 0;
		$key = "mx:list:{$offset}:{$limit}";
		$_SESSION['MODEL_CID'];
		$memcache = GoCache::getCacheAdapter('memcached');
		$return_cache  = $memcache->get($key);
		
		if($return_cache && empty($debug)){
			$return_cache = json_decode($return_cache,1);
			$list = $return_cache['list'];
			$total = $return_cache['total'];
			$return['TOTAL'] = $total;
			$return['DATA'] = $list;
			$return['KEY'] = 'mxlist';
			return json_encode($return);
		}
		
		$re = $this->getSoftConfig($offset, $limit);

		$memcache->set($key,json_encode($re),3600);
		$return['TOTAL'] = $re['total'];
		$return['DATA'] = $re['list'];
		$return['KEY'] = 'mxlist';
		return json_encode($return);
	}
	
	public function getSoftConfig($offset, $limit) {
		$config_soft = array(
			'com.storm.smart','cn.opda.a.phonoalbumshoushou','cn.kuwo.player','com.moji.mjweather','cn.kuwo.tingshu','com.baidu.homework','com.sogou.activity.src','com.letv.android.client','com.sogou.novel','com.duokan.reader','com.sina.news','com.baidu.video','com.dianxinos.dxhome','com.miui.mihome2','com.letv.letvshop','com.nd.android.pandareader','com.tencent.mtt','com.UCMobile','fm.qingting.qtradio','me.chunyu.ChunyuDoctor','com.slanissue.apps.mobile.erge','com.baidu.mbaby','com.tencent.mm','com.tencent.mobileqq','com.taobao.taobao','com.tencent.qqlive','com.qiyi.video','com.eg.android.AlipayGphone','com.baidu.searchbox','com.ss.android.article.news','com.youku.phone','com.sina.weibo','com.baidu.BaiduMap','com.miui.video','com.tencent.qqpimsecure','com.tencent.news','com.tencent.qqmusic','com.sankuai.meituan','flipboard.cn','com.google.android.inputmethod.pinyin','com.dianping.v1','com.tencent.qqlite','com.baidu.browser.apps','com.kugou.android','com.qzone','com.sohu.newsclient','com.meitu.meiyancamera','com.smile.gifmaker','com.shoujiduoduo.ringtone','com.besttone.hall','com.immomo.momo','com.autonavi.minimap','com.tencent.androidqqmail','com.tudou.android','im.yixin','com.sohu.sohuvideo','com.tencent.qqpim','com.pplive.androidphone','com.baidu.input','cn.wps.moffice_eng','com.kingroot.kinguser','tv.pps.mobile','com.cootek.smartdialer','com.corp21cn.mail189','com.chinatelecom.pim','com.chaozh.iReaderFree','com.baidu.netdisk','com.snda.wifilocating','com.jingdong.app.mall','com.akazam.android.wlandialer','com.hunantv.imgo.activity','ctrip.android.view','com.tmall.wireless','com.shuqi.controller','com.Qunar','com.achievo.vipshop','com.erdo.android.FJDXCartoon','com.netease.newsreader.activity','com.sdu.didi.psnger','com.fenbi.android.solar','com.duowan.mobile','com.duowan.groundhog.mctools','com.wuba','com.sg.sledog','com.chaozh.iReaderFree15','com.tuan800.tao800','com.youan.universal','com.lenovo.anyshare','com.tencent.karaoke','com.autonavi.xmgd.navigator','com.google.android.marvin.talkback','com.tencent.token','com.wochacha','com.qq.reader','com.sds.android.ttpod','com.evernote','com.tongcheng.android','com.baidu.tieba','cn.goapk.market','com.sohu.inputmethod.sogoupad','com.hexin.plat.android','com.huluxia.gametools','com.cleanmaster.security_cn','com.ss.android.essay.joke','com.yy.yymeet','com.sohu.inputmethod.sogou','com.netease.cloudmusic','com.youdao.dict','com.ijinshan.kbatterydoctor','com.lenovo.safecenter','com.cleanmaster.mguard_cn','com.ximalaya.ting.android','com.besttone.elocal','com.google.android.youtube','com.zhiyoo','com.lenovo.launcher','viva.reader','com.meitu.meipaimv','telecom.mdesk','com.youloft.calendar','com.xunlei.downloadprovider','com.funshion.video.mobile','com.mt.mtxx.mtxx','com.mogujie','com.tadu.android','com.handsgo.jiakao.android','com.tripadvisor.tripadvisor.daodao','com.ting.mp3.android','com.chinamworld.main','com.baidu.input_huawei','com.flightmanager.view','com.ifeng.news2','com.androidesk','com.changba','bubei.tingshu','com.sinovatech.unicom.ui','sogou.mobile.explorer','com.android.chrome','com.tencent.android.qqdownloader','com.google.android.apps.plus','com.baidu.hao123','vStudio.Android.Camera360','com.yx','com.lingduo.acorn','com.qidian.QDReader','com.gaoxin.abilly.activity','cmb.pb','com.tencent.ttpic','com.ubercab','com.cubic.autohome','com.chinamworld.bocmbci','com.ganji.android','com.rrh.jdb','com.dewmobile.kuaiya','com.mapbar.android.mapbarmap','com.tencent.reading','com.syezon.wifi','com.sskj.flashlight','com.xfplay.play','com.yixia.xiaokaxiu','com.wififreekey.wifi','com.lenovo.leos.cloud.sync','com.vlocker.locker','com.duomi.android','com.nd.android.pandahome2','sina.mobile.tianqitong','com.kingroot.master','com.ushaqi.zhuishushenqi','com.peopleClients.views','com.iflytek.inputmethod','com.myzaker.ZAKER_Phone','com.suning.mobile.ebuy','com.baidu.searchbox_tianyi','com.huawei.hidisk','com.tencent.qt.qtl','cn.eclicks.wzsearch','com.iflytek.cmcc','com.lenovo.calendar','com.ijinshan.browser_fast','com.tripadvisor.tripadvisor','com.wenba.bangbang','com.cinema2345','dopool.player','com.jxedt','me.ele','com.oupeng.browser','com.cootek.smartinputv5','com.ws.wifi','com.estrongs.android.pop','com.alibaba.mobileim','com.whatsapp','com.yipiao','com.xiaomi.channel','com.renren.mobile.android','com.jm.android.jumei','com.uc.browser.hd','com.xiaobanlong.main','cn.cj.pe','com.tencent.qqpinyin','com.lenovo.browser','com.kingsoft','com.google.android.voicesearch','com.nuomi','com.imusic.iting','com.android.dazhihui','com.qihoo360.mobilesafe','com.iooly.android.lockscreen','com.zhiqupk.root','com.xiaomi.shop','cmccwm.mobilemusic','com.huawei.remoteassistant','com.yoloho.dayima','com.lltskb.lltskb','com.yiche.price','com.xinmei365.font','com.android.cheyooh','com.lenovo.FileBrowser','com.tencent.qqmusicpad','com.iflytek.inputmethod.pad','com.android.comicsisland.activity','com.hf','com.tuniu.app.ui','com.oupeng.mini.android','com.icoolme.android.weather','com.cmbchina.ccd.pluto.cmbActivity','com.zte.heartyservice','com.bbgz.android.app','com.sankuai.meituan.takeoutnew','com.meitu.makeup','com.gionee.client','com.mymoney','com.tianqi2345','com.jingdong.pdj','com.speedsoftware.rootexplorer','com.tencent.map','com.baidu.lbs.waimai','com.MobileTicket','com.zte.backup.mmi','com.android.coolwind','com.yybackup','com.ludashi.benchmark','com.tencent.WBlog','com.calendar.UI','my.beautyCamera','com.baidu.iknow','com.wifi.mianfeid','com.androidesk.livewallpaper','com.chinamobile.cmccwifi','com.baidu.baidutranslate','com.andorid.flashinglight','com.tencent.lightalk','cn.lextel.dg','com.shoujiduoduo.wallpaper','com.meitu.wheecam','com.ijinshan.duba','com.wifi.wnys','com.wantu.activity','com.viewer.wifipass','com.mgyun.shua.su','com.qiyi.tv','com.icbc','com.wifi.net.cn','com.lbe.security','com.fenbi.android.gaozhong','com.meilishuo','qsbk.app','com.duoduo.child.story','com.samsung.swift.app.kiesair','com.husor.mizhe','com.quvideo.xiaoying','cn.andouya','com.codoon.gps','com.nice.main','com.sogou.map.android.maps','com.kuaikan.comic','com.sankuai.movie','cld.navi.mainframe','com.netease.mail','com.lingan.seeyou','com.peopledailychina.activity','com.jiongji.andriod.card','com.haodou.recipe','com.autohome.mycar','com.babytree.apps.pregnancy','com.taobao.trip','cn.amazon.mShop.android','com.cubic.choosecar','com.gtgj.view','air.tv.douyu.android','com.dp.android.elong','cn.cntv','com.xtuone.android.syllabus','com.daohang2345','com.ifeng.newvideo','com.netease.my.anzhi','com.netease.dhxy.anzhi','com.skymoons.hqg.anzhi','com.supercell.clashofclans.anzhi','sh.lilith.dgame.anzhi','com.pokercity.bydrqp.anzhi','com.crisisfire.android.anzhi','com.youzu.snsgz.anzhi','com.supercell.boombeach.anzhi','com.netease.ldxy.anzhi','net.crimoon.pm.anzhi','com.snailgame.panda.anzhi','com.babletime.fknsango_anzhi','com.hcg.cok.anzhi','com.tianmashikong.qmqj.anzhi','com.ourpalm.buliangren.anzhi','com.cyou.cx.mtlbb.anzhi','com.wanmei.mini.dod.anzhi','com.regin.gcld.anzhi','com.taiqi.sl.fsn.anzhi','com.youlong.zlcq.anzhi','cc.thedream.qinsmoon.anzhi','com.snailgame.gyc.anzhi','com.lk.yxzj.anzhi','com.ledo.kof97ol.anzhi','com.ttxw.anzhi','com.SmartSpace.TheSoulOfSwordFury.Android.anzhi','com.youkia.death.anzhi','com.mztgame.dzz.anzhi','com.GF.PalaceM2_cn_cn.hwy_android_anzhi.anzhi','com.cmge.xianjian.anzhi','com.linekong.cjad.anzhi'
		);
		
		$key = "mx:alllist:softid";
		$memcache = GoCache::getCacheAdapter('memcached');
		$enabled_id = $memcache->get($key);
		$enabled_id = json_decode($enabled_id, true);
		$model = load_model('softlist');
		if (empty($enabled_id)) {
			$p_ids = $model->getPkg2Id($config_soft);
			$enabled_id = array();
			foreach ($p_ids as $recommend_id) {
				if(!is_array($recommend_id)){
					$enabled_id[] = $recommend_id;
				} elseif(count($recommend_id)==1){
					$enabled_id[] = $recommend_id[0];
				} else{
					$enabled_id_flip = array_flip($recommend_id);
					$dev = load_model('dev');
					$softid = $dev->filterSoftId($enabled_id_flip, $filter_option);
					$softid = array_pop($softid);
					$enabled_id[] = $softid;
				}
			}
			$memcache->set($key,json_encode($enabled_id),600);
		}
		
		$used_id = array_slice($enabled_id, $offset, $limit, true);
		$app = $model->getsoftinfos($used_id, getFilterOption());
		$app = $this->formatData($app, $used_id);
		
		$re['list'] = $app;
		$re['total'] = count($enabled_id);
		
		return $re;
	}
	
	/**
	 * 格式化数据
	 * @param  [array]  $app     []
	 * @param  [array]  $softids []
	 * @return [array]           []
	 */
	public function formatData($app, $softids){
		$r = array();
		
		$pre_url = getIconHost();
		foreach ($softids as $key=>$row) {
			$i = $key+1;
			
			$_d['softid'] 		= (int)$app[$row]['softid'];
			$_d['package']		= $app[$row]['package'];
			$_d['name']			= ($app[$row]['name'])?$app[$row]['name']:$app[$row]['softname'];
			$_d['icon'] 		= $pre_url.$app[$row]['iconurl_125'];
			$_d['type']			= ($app[$row]['parentid'] == 2) ? 'game' : 'soft';
			$_d['rank']			= $i;
			$_d['download'] 	= "anzhimarket://details?id={$_d['package']}&s1=e14e68f63577&s2=1";
			$_d['wapurl']		= 'http://m.anzhi.com/mxzm_'.$_d['softid'].'.html';
			$r[] = $_d;
		}
		return $r;
	}
	
	function echomicrotime($str=''){
	    if($_COOKIE['test']){
	        $key = $_SESSION['t_key']?$_SESSION['t_key']:1;
	        echo $str .'local->'.$key.' use :'. microtime()."<br />";
	        $_SESSION['t_key'] = $key + 1;
	    }
	    	
	}
}
