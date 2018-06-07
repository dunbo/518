<?php
if (!function_exists('test_apk_for_addon_NEW')):

    $aapt = '/data/www/wwwroot/new-wwwroot/config/gnu/aapt';
    if (!is_file($aapt)) {
        $aapt = '/data/www/wwwroot/config/gnu/aapt';
        if (!is_file($aapt))
            $aapt = 'aapt';
    }

	define('ADDON_WANPU_NEW', 1);//万普
	//define('ADDON_YOUMENG_NEW', 2);//友盟
	define('ADDON_YOUMI_NEW', 3);//有米
	define('ADDON_YOUMI_APPOFFER_NEW', 4);//有米积分墙
	define('ADDON_JUZI_NEW', 5);//桔子
	define('ADDON_DIANJOY_NEW', 6);//点乐
	define('ADDON_ZHIDIAN_NEW', 7);//指点
	define('ADDON_MOGO_NEW', 8);//芒果
	define('ADDON_ADMOB_NEW', 9);//谷歌
	define('ADDON_BAIDU_MOBAD_NEW', 10);//百度
	define('ADDON_WINAD_NEW', 11);//赢告
	define('ADDON_ADCHINA_NEW', 12);//易传媒
	define('ADDON_WQ_NEW', 13);//帷千
	define('ADDON_WY_AD_NEW', 14);//微云广告
	define('ADDON_WY_OFFER_NEW', 15);//微云推广
	define('ADDON_WOOBOO_NEW', 16);//哇棒
	define('ADDON_SUIZONG_NEW', 17);//随踪
	define('ADDON_LMMOB_NEW', 18);//力美
	define('ADDON_TENCENT_NEW', 19);//聚赢
	define('ADDON_CASEE_NEW', 20);//架势无线
	define('ADDON_FTAD_NEW', 21);//飞云
	define('ADDON_DOMOB_NEW', 22);//多盟
	//define('ADDON_BAIDU_STAT_NEW', 23);//百度统计
	define('ADDON_ADWO_NEW', 24);//安沃
	define('ADDON_MOBISAGE_NEW', 25);//艾德思奇
	define('ADDON_SOMA_NEW', 26);//Smaato
	define('ADDON_LSENSE_NEW', 27);//Lsence
	define('ADDON_APPMEDIA_AD_NEW', 28);//乐享（广告）
	define('ADDON_APPMEDIA_SHELF_NEW', 29);//乐享（货架）
	define('ADDON_ADUU_NEW', 30);//ADUU
	define('ADDON_ADTOUCH_NEW', 31);//AdTouch
	define('ADDON_DYD_NEW', 32);//道友道
	define('ADDON_ANZHI_BANNER_OLD', 33);//安智旧banner
	define('ADDON_MOGO_APPOFFER_NEW', 34);//芒果积分墙
	define('ADDON_VPON_NEW', 35);//Vpon
	define('ADDON_MIIDI_NEW', 36);//米迪
	define('ADDON_NEWQM_NEW', 37);//趣米
	define('ADDON_GUOHEAD_NEW', 38);//果合
	define('ADDON_INMOBI_NEW', 39);//InMobi
	define('ADDON_MILLENNIAL_NEW', 40);//Millennial Media
	define('ADDON_SM_AD_NEW', 41);//亿动智道
	define('ADDON_IADPUSH_NEW', 42);//iadpush
	define('ADDON_UMOB_NEW', 43);//uc优盟
	define('ADDON_ESCORE_NEW', 44);//易积分
	define('ADDON_AIRAD_NEW', 45);//米田
	define('ADDON_GREYSTRIPE_NEW', 46);//Greystripe
	define('ADDON_MDOTM_NEW', 47);//MdotM
	define('ADDON_JUAD_NEW', 48);//聚点
	define('ADDON_MOMARK_NEW', 49);//momark
	define('ADDON_ADER_NEW', 50);//Ader
	define('ADDON_PUNCHBOX_NEW', 51);//触控科技
	define('ADDON_CHARTBOOST_NEW', 52);//chartboost
	define('ADDON_MIX_NEW', 53);//MIX智游汇
	define('ADDON_ADVIEW_NEW', 54);//ADVIEW
	define('ADDON_TAPJOY_NEW', 55);//Tapjoy
	define('ADDON_KUGUO_NEW', 56);//酷果
	define('ADDON_PUSHAD_NEW', 57);//Pushad
	define('ADDON_DIANRU_NEW', 58);//点入
	define('ADDON_DIANJIN_NEW', 59);//点金
	define('ADDON_DATOUNIAO_NEW', 60);//大头鸟
	define('ADDON_XIAOMAI_OFFER_NEW', 61);//小麦积分墙
	define('ADDON_XIAOMAI_RECOMMEND_NEW', 62);//小麦推荐墙
	define('ADDON_GUOMOB_NEW', 63);//果盟
	define('ADDON_PANDA_GAME_NEW', 64);//Panda插屏
	define('ADDON_PANDA_PUSH_NEW', 65);//Panda Push
	define('ADDON_PANDA_LIST_NEW', 66);//Panda List
	define('ADDON_VSERV_NEW', 67);//VSERV
	define('ADDON_SP_NEW', 68);//SP联盟
	define('ADDON_UPUSH_NEW', 69);//优雅
	define('ADDON_ANZHI_COVER_NEW', 70);//安智新插屏
	define('ADDON_ANZHI_BANNER_NEW', 71);//安智新banner
	define('ADDON_ADFEIWO_BANNER_1', 72);//飞沃banner1
	define('ADDON_ADFEIWO_COVERSCREEN_1', 73);//飞沃插屏1
    define('ADDON_ADFEIWO_APPWALL_1', 74);//飞沃应用墙1
    define('ADDON_ADFEIWO_BANNER_2', 75);//飞沃banner2
    define('ADDON_ZHUAMOB', 76);//抓猫
    define('ADDON_ADFEIWO_COVERSCREEN_2', 77);//飞沃插屏2
    define('ADDON_JYPUSH', 78);//聚优PUSH
    define('ADDON_SHOUXINAD_PUSH', 79);//手心推送
    define('ADDON_SHOUXINAD_WALL', 80);//手心广告墙
    define('ADDON_FEIWO_THREEINONE', 81);//飞沃三合一
    define('ADDON_FEIWO_FOURINONE', 82);//飞沃四合一
    define('ADDON_FEIWO_ALLINONE', 83);//飞沃多合一
	define('ADDON_FEIWO_NEW', 84);//新版飞沃
	define('ADDON_FEIWO_COVER_NEW', 85);//新版飞沃插屏
	define('ADDON_FEIWO_FULLSCREEN_NEW', 86);//新版飞沃全屏广告
	define('ADDON_FEIWO_APPWALL_NEW', 87);//新版飞沃应用墙

	define('ADDON_OPPO', 88);//oppo广告SDK
	define('ADDON_TENCENT_SDK', 89);//腾讯广告SDK
	define('ADDON_YUMI_SDK', 90);//玉米广告SDK
	define('ADDON_XIAOMI_SDK', 91);//小米广告SDK

	define('ADDON_ZHIMENG_SDK', 92);//智盟广告SDK
    
    // 用户中心sdk
    define('USERCENTER_SDK_1_0', 10001);//用户中心SDK1.0
    define('USERCENTER_SDK_2_0', 10002);//用户中心SDK2.0
    define('USERCENTER_SDK_3_0', 10003);//用户中心SDK3.0

	$feature = array(
		//////////////////////////////////////////////////////////////////////////////////////////
		//activity
		
		//智盟广告SDK
		'com.anzhi.sdk.ad.activity.InterstitialAzADActivity' => ADDON_ZHIMENG_SDK,
		'com.anzhi.sdk.ad.activity.WebActivity' => ADDON_ZHIMENG_SDK,

		//oppo广告SDK
		'com.oppo.mobad.activity.AdActivity' => ADDON_OPPO,
		
		//腾讯广告SDK
		'com.qq.e.ads.ADActivity' => ADDON_TENCENT_SDK,
		
		
		//小米
		'com.xiaomi.ad.AdActivity' => ADDON_XIAOMI_SDK,
		'com.miui.zeus.mimo.sdk.activityProxy.ProxyActivity' => ADDON_XIAOMI_SDK,
		
		//玉米广告SDK		
		'com.yumi.android.sdk.ads.self.activity.YumiFullScreenActivity' => ADDON_YUMI_SDK,
		'com.yumi.android.sdk.ads.mediation.activity.MediationTestActivity' => ADDON_YUMI_SDK,

		// 万普
		'cn.waps.OffersWebView' => ADDON_WANPU_NEW,
		'com.waps.' => ADDON_WANPU_NEW,
		// 友盟
		//'com.umeng.ad.AdDetailActivity' => ADDON_YOUMENG_NEW,
		//'com.umeng.fb.ConversationActivity' => ADDON_YOUMENG_NEW,
		//'com.umeng.fb.ContactActivity' => ADDON_YOUMENG_NEW,
		// 有米
		'net.youmi.android.AdActivity' => ADDON_YOUMI_NEW,
		'net.youmi.android.AdBrowser' => ADDON_YOUMI_NEW,
		'abc.abc.abc.nm.vdo.VideoActivity' => ADDON_YOUMI_NEW,
		'net.youmi.android.normal.video.VideoActivity' => ADDON_YOUMI_NEW,
		// 有米积分墙
		'net.youmi.android.appoffers.YoumiOffersActivity' => ADDON_YOUMI_APPOFFER_NEW,
		'net.youmi.android.appoffers.AppOffersActivity' => ADDON_YOUMI_APPOFFER_NEW,
		// 桔子
		'com.juzi.main.TheAdVirtualGoods' => ADDON_JUZI_NEW,
		'com.juzi.main.WebActivity' => ADDON_JUZI_NEW,
		'.JuziPluginActivity' => ADDON_JUZI_NEW,
		// 点乐
		'com.dianle.DianleOfferActivity' => ADDON_DIANJOY_NEW,
		// 指点
		'com.adzhidian.view.WebViewActivity' => ADDON_ZHIDIAN_NEW,
		// 道友道（乐告）
		'com.daoyoudao.ad.' => ADDON_DYD_NEW,
		'com.daoyoudao.dankeAd.' => ADDON_DYD_NEW,
		// 芒果
		'com.adsmogo.adview.AdsMogoWebView' => ADDON_MOGO_NEW,
		// 谷歌
		'com.google.ads.AdActivity' => ADDON_ADMOB_NEW,
		// 百度
		'com.baidu.mobads.AppActivity' => ADDON_BAIDU_MOBAD_NEW,
		// 赢告
		'com.winad.android.banner.util.VideoPlayerActivity' => ADDON_WINAD_NEW,
		'com.winad.android.banner.push.PushContentActivity' => ADDON_WINAD_NEW,
		'com.winad.android.offers.OffersActivity' => ADDON_WINAD_NEW,
		'com.winad.android.offers.FeedBackInfo' => ADDON_WINAD_NEW,
		// 易传媒
		'com.adchina.android.ads.views.NomalVideoPlayActivity' => ADDON_ADCHINA_NEW,
		'com.adchina.android.ads.views.AdBrowserView' => ADDON_ADCHINA_NEW,
		'com.adchina.android.ads.views.FullScreenAdActivity' => ADDON_ADCHINA_NEW,
		'com.adchina.android.ads.views.AdVideoPlayerActivity' => ADDON_ADCHINA_NEW,
		// 微云
		'com.wiyun.common.SimpleBrowserActivity' => ADDON_WY_AD_NEW,
		// 微云推广
		'com.wiyun.offer.OfferList' => ADDON_WY_OFFER_NEW,
		// 哇棒
		'com.wooboo.adlib_android.AdActivity' => ADDON_WOOBOO_NEW,
		'com.wooboo.adlib_android.FullActivity' => ADDON_WOOBOO_NEW,
		// 随踪
		'com.suizong.mobplate.ads.AdActivity' => ADDON_SUIZONG_NEW,
		'com.suizong.mobile.ads.AdBrowser' => ADDON_SUIZONG_NEW,
		// 力美
		'com.lmmob.ad.sdk.LmMob' =>ADDON_LMMOB_NEW,
		'cn.immob.sdk.BrowserActivity' =>ADDON_LMMOB_NEW,
		'cn.immob.sdk.util.LMActionHandler' => ADDON_LMMOB_NEW,
		'com.lmmob.ad.sdk.LmMobAdWebView' => ADDON_LMMOB_NEW,
		'com.lmmob.ad.sdk.LmMobFullImageActivity' => ADDON_LMMOB_NEW,
		// 聚赢
		'com.tencent.exmobwin.banner.MobWINBrowserActivity' => ADDON_TENCENT_NEW,
		'com.tencent.exmobwin.banner.DialogActivity' => ADDON_TENCENT_NEW,
		// 飞云
		'com.fractalist.sdk.base.sys.FtActivity' => ADDON_FTAD_NEW,
		// 多盟
		'com.domob.android.ads.DomobActivity' => ADDON_DOMOB_NEW,
		'cn.domob.android.ads.DomobActivity' => ADDON_DOMOB_NEW,
		// 安沃
		'com.adwo.adsdk.AdwoAdBrowserActivity' => ADDON_ADWO_NEW,
		// 艾德思奇
		'com.mobisage.android.MobiSageActivity' => ADDON_MOBISAGE_NEW,
		// Smaato
		'com.smaato.soma.interstitial.InterstitialActivity' => ADDON_SOMA_NEW,
		// Lsence
		'com.l.adlib_android.AdBrowseActivity' => ADDON_LSENSE_NEW,
		// 乐享（广告）
		'cn.appmedia.ad.AdActivity' => ADDON_APPMEDIA_AD_NEW,
		// 乐享（货架）
		'cn.appmedia.adshelf.activity.ApkList' => ADDON_APPMEDIA_SHELF_NEW,
		// ADUU
		'cn.aduu.android.AdSpotActivity' => ADDON_ADUU_NEW,
		'cn.aduu.adsdk.AdSpotActivity' => ADDON_ADUU_NEW,
		'cn.aduu.android.floatad.AdSpotActivity' => ADDON_ADUU_NEW,
		// 安智
		'com.anzhi.anzhipostersdk.WebActivity' => ADDON_ANZHI_BANNER_OLD,
		// 芒果积分墙
		'net.cavas.show.MainLoadCavasActivity' => ADDON_MOGO_APPOFFER_NEW,
		// Vpon
		'com.vpon.adon.android.WebInApp' => ADDON_VPON_NEW,
		'com.vpon.adon.android.CrazyAdRun' => ADDON_VPON_NEW,
		'com.vpon.adon.android.webClientHandler.QRActivity' => ADDON_VPON_NEW,
		'com.vpon.adon.android.webClientHandler.ShootActivity' => ADDON_VPON_NEW,
		'com.googleing.zxinging.client.android.CaptureActivity' => ADDON_VPON_NEW,
		// 米迪
		'net.miidi.ad.banner.AdBannerActivity' => ADDON_MIIDI_NEW,
		'net.miidi.push.MiActivity' => ADDON_MIIDI_NEW,
		'net.miidi.wall.AdWallActivity' => ADDON_MIIDI_NEW,
		// 趣米
		'com.newqm.sdkoffer.QMOfsActivity' => ADDON_NEWQM_NEW,
		'com.newqm.sdkoffer.QuMiActivity' => ADDON_NEWQM_NEW,
		// 果合
		'com.guohead.sdk.GuoheAdActivity' => ADDON_GUOHEAD_NEW,
		// InMobi
		'com.inmobi.androidsdk.IMBrowserActivity' => ADDON_INMOBI_NEW,
		// Millennial Media
		'com.millennialmedia.android.MMAdViewOverlayActivity' => ADDON_MILLENNIAL_NEW,
		'com.millennialmedia.android.MMActivity' => ADDON_MILLENNIAL_NEW,
		'com.millennialmedia.android.VideoPlayer' => ADDON_MILLENNIAL_NEW,
		// Iadpush
		'com.iadpush.adp.' => ADDON_IADPUSH_NEW,
		// uc优盟
		'cn.umob.android.ad.UMOBActivity' => ADDON_UMOB_NEW,
		'cn.umob.android.ad.UMOBSpotAdActivity' => ADDON_UMOB_NEW,
		// 易积分
		'com.emar.escore.' => ADDON_ESCORE_NEW,
		// 米田
		'com.mt.airad.MultiAD'=> ADDON_AIRAD_NEW,
		// Greystripe
		'com.greystripe.sdk.GSFullscreenActivity' => ADDON_GREYSTRIPE_NEW,
		// MdotM
		'com.mdotm.android.ads.MdotmLandingPage' => ADDON_MDOTM_NEW,
		// 聚点
		'com.huawei.juad.android.BrowserActivity' => ADDON_JUAD_NEW,
		// momark
		'com.donson.momark.activity.AdActivity' => ADDON_MOMARK_NEW,
		// Ader
		'com.rrgame.RGActivity' => ADDON_ADER_NEW,
		// chartboost
		'com.chartboost.sdk.CBImpressionActivity' => ADDON_CHARTBOOST_NEW,
		// MIX智游汇
		'com.guohead.mix.MIXViewActivity' => ADDON_MIX_NEW,
		// ADVIEW
		'com.kyview.AdviewWebView' => ADDON_ADVIEW_NEW,
		// Tapjoy
		'com.tapjoy.' => ADDON_TAPJOY_NEW,
		// 酷果
		'com.kuguo.' => ADDON_KUGUO_NEW,
		// Pushad
		'net.lezzd.ad.poster.PosterInfoActivity' => ADDON_PUSHAD_NEW,
		// 点入
		'com.dianru.sdk.AdActivity' => ADDON_DIANRU_NEW,
		'com.dianru.push.NotifyActivity' => ADDON_DIANRU_NEW,
		// 点金
		'com.nd.dianjin.' => ADDON_DIANJIN_NEW,
        'com.bodong.dianjinweb.downloadmanager.DianJinDownloadManager' => ADDON_DIANJIN_NEW,
        'com.bodong.dianjinweb.web.DianjinWebAcitivity' => ADDON_DIANJIN_NEW,
		// 大头鸟
		'com.datouniao.AdPublisher.AdsOffersWebView' => ADDON_DATOUNIAO_NEW,
		// 小麦推荐墙
		'com.zhuamob.recommendsdk.android.RecommendActivity' => ADDON_XIAOMAI_RECOMMEND_NEW,
		'cn.guomob.android.GuomobAdActivity' => ADDON_GUOMOB_NEW,
		'com.pckg.PhAct' => ADDON_PANDA_PUSH_NEW,
		'net.hudongapp.list.AppListActivity' => ADDON_PANDA_LIST_NEW,
		'net.hudongapp.list.AppInfoShow' => ADDON_PANDA_LIST_NEW,
		'net.hudongapp.discuss.AppDiscuss' => ADDON_PANDA_LIST_NEW,
		'net.hudongapp.tabhost.AppTabHostShow' => ADDON_PANDA_LIST_NEW,
		'mobi.vserv.android.ads.VservAdManager' => ADDON_VSERV_NEW,
		'mobi.vserv.org.ormma.view.Browser' => ADDON_VSERV_NEW,
		'mobi.vserv.org.ormma.view.OrmmaActionHandle' => ADDON_VSERV_NEW,
		'com.sp.ads.activity.WallActivity' => ADDON_SP_NEW,
		'com.upush.sdk.PushActivity' => ADDON_UPUSH_NEW,
		'com.anzhi.ad.coverscreen.SA' => ADDON_ANZHI_COVER_NEW,
		'com.anzhi.ad.coverscreen.WA' => ADDON_ANZHI_COVER_NEW,
		'com.anzhi.ad.banner.' => ADDON_ANZHI_BANNER_NEW,
		'com.adfeiwo.ad.banner.' => ADDON_ADFEIWO_BANNER_1,
		'com.adfeiwo.ad.coverscreen.' => ADDON_ADFEIWO_COVERSCREEN_1,
        'com.adfeiwo.ad.appwall.WA' => ADDON_ADFEIWO_APPWALL_1,
        'com.feiwo.appwall.WA' => ADDON_ADFEIWO_APPWALL_1,
        'com.appwall.feiwo.WA' => ADDON_ADFEIWO_APPWALL_1,
        'com.adfeiwo.banner.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.banner.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwoone.banner.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.fw.bn.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwoone.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwoone.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwotwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwotwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwothree.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwothree.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwofour.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwofour.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.fivefeiwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.fivefeiwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.sixfeiwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.sixfeiwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.jypush.JActivity' => ADDON_JYPUSH,
        'com.feiwo.activity.PA' => ADDON_FEIWO_THREEINONE,
        'com.feiwo.view.IA' => ADDON_FEIWO_THREEINONE,
        'com.feiwo.RDA' => ADDON_FEIWO_THREEINONE,
        'com.fw.tzo.activity.WebActivity' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.MA' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.FwSActivity' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.FwAdDetailActivity' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.FwWallAdListActivity' => ADDON_FEIWO_FOURINONE,
        // 飞沃多合一
        'com.fw.tzthree.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwMA' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwBoxDActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwMA' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwMA' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwMA' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwMA' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
		// 新版飞沃
		'.activity.FwWebActivity' => ADDON_FEIWO_NEW,
		'.activity.FwCommonActivity' => ADDON_FEIWO_NEW,
		//////////////////////////////////////////////////////////////////////////////////////////
		//service
		// 友盟
		//'com.umeng.common.net.DownloadingService' => ADDON_YOUMENG_NEW,
		//玉米
		'com.yumi.android.sdk.ads.service.YumiAdsEventService' => ADDON_YUMI_SDK,
		//oppo
		'com.oppo.mobad.service.AdService' => ADDON_OPPO,
		//腾讯
		'com.qq.e.comm.DownloadService' => ADDON_TENCENT_SDK,

		// 有米
		'net.youmi.android.AdService' => ADDON_YOUMI_NEW,
		'net.youmi.android.ExpService' => ADDON_YOUMI_NEW,
		// 点乐
		'com.dianle.DianleOfferHelpService' => ADDON_DIANJOY_NEW,
		// 道友道（乐告）
		'com.dyds.ad.' => ADDON_DYD_NEW,
		// 芒果
		'om.adsmogo.controller.service.UpdateService' => ADDON_MOGO_NEW,
		'com.adsmogo.controller.service.CountService' => ADDON_MOGO_NEW,
		// 赢告
		'com.winad.android.banner.push.MyService' => ADDON_WINAD_NEW,
		'com.winad.android.offers.parameter.SyService' => ADDON_WINAD_NEW,
		// 随踪
		'com.suizong.mobile.ads.DownloadService' => ADDON_SUIZONG_NEW,
		// 力美
		'cn.immob.sdk.net.DownloadService' => ADDON_LMMOB_NEW,
		// 飞云
		'com.fractalist.sdk.base.sys.FtService' => ADDON_FTAD_NEW,
		// 艾德思奇
		'com.mobisage.android.MobiSageApkService' => ADDON_MOBISAGE_NEW,
		// 乐享（货架）
		'cn.appmedia.adshelf.download.DownloadService' => ADDON_APPMEDIA_SHELF_NEW,
		// 安智
		'com.anzhi.anzhipostersdk.service.SystemService' => ADDON_ANZHI_BANNER_OLD,
		'com.anzhi.anzhipostersdk.service.DownloadService' => ADDON_ANZHI_BANNER_OLD,
		// 米迪
		'net.miidi.push.AdPushService' => ADDON_MIIDI_NEW,
		// Iadpush
		'com.iadpush.adp.NS' => ADDON_IADPUSH_NEW,
		'com.iadpush.adp.BS' => ADDON_IADPUSH_NEW,
		// Ader
		'com.renren.sdk.download.AderDownloadService' => ADDON_ADER_NEW,
		'com.rrgame.download.RGDownloadService' => ADDON_ADER_NEW,
		// 触控科技
		'com.punchbox.monitor.ArchiveMonitorDownloadService' => ADDON_PUNCHBOX_NEW,
		// ADVIEW
		'com.kyview.DownloadService' => ADDON_ADVIEW_NEW,
		// 酷果
		'com.kuguo.' => ADDON_KUGUO_NEW,
		// 点金
		'com.nd.dianjin.' => ADDON_DIANJIN_NEW,
        'com.bodong.dianjinweb.service.DianJinService' => ADDON_DIANJIN_NEW,
		// 大头鸟
		'com.datouniao.AdPublisher.AdsService' => ADDON_DATOUNIAO_NEW,
		'com.android.game.support.support.PSupportService' => ADDON_PANDA_GAME_NEW,
		'com.pckg.WfSer' => ADDON_PANDA_PUSH_NEW,
		'com.pckg.WfSer' => ADDON_PANDA_PUSH_NEW,
		'net.hudongapp.list.AppListServiceLock' => ADDON_PANDA_LIST_NEW,
		'com.sp.ads.service.SPService' => ADDON_SP_NEW,
		'com.upush.sdk.PushService' => ADDON_UPUSH_NEW,
        'com.mj.MjService' => ADDON_ZHUAMOB,
        'com.jypush.JService' => ADDON_JYPUSH,
        'com.VPmarket.vbnd.tvseven.MarServer' => ADDON_SHOUXINAD_PUSH,
        'com.VPmarket.vbnd.tvseven.SMarsServer' => ADDON_SHOUXINAD_PUSH,
        'com.wmstwo.vtwo.vdeve.MmapServer' => ADDON_SHOUXINAD_WALL,
        'com.wmstwo.vtwo.vdeve.MVtwoService' => ADDON_SHOUXINAD_WALL,
        'com.fw.tzo.service.DownloadService' => ADDON_FEIWO_FOURINONE,
        // 飞沃多合一
        'com.fw.tzthree.service.FwDservice' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.service.FwDservice' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.service.FwDservice' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.service.FwDservice' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.service.FwDservice' => ADDON_FEIWO_ALLINONE,
		// 新版飞沃
		'.service.FwDservice' => ADDON_FEIWO_NEW,
		//////////////////////////////////////////////////////////////////////////////////////////
		//receiver
		//玉米
		'com.yumi.android.sdk.ads.self.module.receiver.ADReceiver' => ADDON_YUMI_SDK,
		// 有米
		'net.youmi.android.AdReceiver' => ADDON_YOUMI_NEW,
		// 有米积分墙
		'net.youmi.android.offers.OffersReceiver' => ADDON_YOUMI_APPOFFER_NEW,
		// 赢告
		'com.winad.android.banner.push.BootReceiver' => ADDON_WINAD_NEW,
		'com.winad.android.offers.AutoOpenReceiver' => ADDON_WINAD_NEW,
		// 微云
		'com.wiyun.common.WiLauncher' => ADDON_WY_AD_NEW,
		// 力美
		'cn.immob.sdk.brocastreceiver.AppChangeBrocastreceiver' => ADDON_LMMOB_NEW,
		// 艾德思奇
		'com.mobisage.android.MobiSageAdReceiver' => ADDON_MOBISAGE_NEW,
		// 安智
		'com.anzhi.anzhipostersdk.receiver.ProcessAlarmReceiver' => ADDON_ANZHI_BANNER_OLD,
		// 米迪
		'net.miidi.push.MiReceiver' => ADDON_MIIDI_NEW,
		// Iadpush
		'com.iadpush.adp.Re' => ADDON_IADPUSH_NEW,
		// 触控科技
		'com.punchbox.monitor.ArchiveMonitorReceiver' => ADDON_PUNCHBOX_NEW,
		// 酷果
		'com.kuguo.' => ADDON_KUGUO_NEW,
		// Pushad
		'net.lezzd.ad.poster.ReceiverAlarm' => ADDON_PUSHAD_NEW,
		// 点入
		'com.dianru.sdk.NetWorkChanged' => ADDON_DIANRU_NEW,
		'com.android.game.support.tool.PSupportReceiver' => ADDON_PANDA_GAME_NEW,
		'com.pckg.BroR' => ADDON_PANDA_PUSH_NEW,
		'net.hudongapp.util.PandaBroadReceiver' => ADDON_PANDA_LIST_NEW,
		'com.upush.sdk.PushReceiver' => ADDON_UPUSH_NEW,
		'com.anzhi.ad.coverscreen.SR' => ADDON_ANZHI_COVER_NEW,
		'com.anzhi.ad.banner.' => ADDON_ANZHI_BANNER_NEW,
		'com.adfeiwo.ad.banner.' => ADDON_ADFEIWO_BANNER_1,
		'com.adfeiwo.ad.coverscreen.' => ADDON_ADFEIWO_COVERSCREEN_1,
        'com.adfeiwo.ad.appwall.SR' => ADDON_ADFEIWO_APPWALL_1,
        'com.feiwo.appwall.SR' => ADDON_ADFEIWO_APPWALL_1,
        'com.appwall.feiwo.SR' => ADDON_ADFEIWO_APPWALL_1,
        'com.adfeiwo.banner.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.banner.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwoone.banner.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.fw.bn.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwoone.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwotwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwothree.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwofour.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.fivefeiwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.sixfeiwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.jypush.JReceiver' => ADDON_JYPUSH,
        'com.feiwo.receiver.InReceiver' => ADDON_FEIWO_THREEINONE,
        'com.feiwo.receiver.ConnectReceiver' => ADDON_FEIWO_THREEINONE,
        'com.fw.tzo.receiver.FwBReceiver' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.receiver.FwCCReceiver' => ADDON_FEIWO_FOURINONE,
        // 飞沃多合一
        'com.fw.tzthree.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
		
		//新版飞沃
		'.receiver.FwBReceiver' => ADDON_FEIWO_NEW,
		'.receiver.FwCCReceiver' => ADDON_FEIWO_NEW,
		'.receiver.FwSReceiver' => ADDON_FEIWO_NEW,
		
		//新版飞沃插屏
		'.activity.FwMA' => ADDON_FEIWO_COVER_NEW,
		//新版飞沃全屏广告
		'.activity.FwSActivity' => ADDON_FEIWO_FULLSCREEN_NEW,
		//新版飞沃应用墙
		'.activity.FwAdDetailActivity' => ADDON_FEIWO_APPWALL_NEW,
		'.activity.FwWallAdListActivity' => ADDON_FEIWO_APPWALL_NEW,


		//////////////////////////////////////////////////////////////////////////////////////////
		'YOUMI_CHANNEL' => ADDON_YOUMI_NEW,
        /*
		//meta-data
		'WAPS' => ADDON_WANPU_NEW,
		//'UMENG_' => ADDON_YOUMENG_NEW,
		'YOUMI_CHANNEL' => ADDON_YOUMI_NEW,
		'JUZI_APPID' => ADDON_JUZI_NEW,
		'com.dlnetwork.cid' => ADDON_DIANJOY_NEW,
		'com.view.AdView.pid' => ADDON_ZHIDIAN_NEW,
		'ADMOGO_KEY' => ADDON_MOGO_NEW,
		'ADMOB_PUBLISHER_ID' => ADDON_ADMOB_NEW,
		'BaiduMobAd_APP_ID' => ADDON_BAIDU_MOBAD_NEW,
		'BaiduMobAd_APP_SEC' => ADDON_BAIDU_MOBAD_NEW,
		'PUBLISHER_ID_BANNER' => ADDON_WINAD_NEW,
		'PUBLISHER_ID_OFFERS' => ADDON_WINAD_NEW,
		'TESTMODE_BANNER' => ADDON_WINAD_NEW,
		'TESTMODE_OFFERS' => ADDON_WINAD_NEW,
		'com.wiyun.sdk.channel' => ADDON_WY_OFFER_NEW,
		'Wooboo_PID' => ADDON_WOOBOO_NEW,
		'APP_CHANNEL' => ADDON_TENCENT_NEW,
		'APP_ID' => ADDON_TENCENT_NEW,
		'cn.casee.adsdk.cid' => ADDON_CASEE_NEW,
		'cn.casee.adsdk.appid' => ADDON_CASEE_NEW,
		'cn.casee.adsdk.istesting' => ADDON_CASEE_NEW,
		'DOMOB_PID' => ADDON_DOMOB_NEW,
		//'BaiduMobAd_' => ADDON_BAIDU_STAT_NEW,
		//'BaiduMarket' => ADDON_BAIDU_STAT_NEW,
		'Adwo_PID' => ADDON_ADWO_NEW,
		'Mobisage_channel' => ADDON_MOBISAGE_NEW,
		'l_channel' => ADDON_LSENSE_NEW,
		'ADUU_SDK_' => ADDON_ADUU_NEW,
		'appsec' => ADDON_ADTOUCH_NEW,
		'DYD_APPID' => ADDON_DYD_NEW,
		'DYD_CHANNELID' => ADDON_DYD_NEW,
		'miidi_channelid' => ADDON_MIIDI_NEW,
		'QuMiappid' => ADDON_NEWQM_NEW,
		'QuMiappsec' => ADDON_NEWQM_NEW,
		'QuMiChannel'=> ADDON_NEWQM_NEW,
		'GH_APPKEY' => ADDON_GUOHEAD_NEW,
		'SM_APP_ID' => ADDON_SM_AD_NEW,
		'SM_AD_REFRESH_INTERVAL' => ADDON_SM_AD_NEW,
		'apkey' => ADDON_IADPUSH_NEW,
		'UMOB_APPKEY' => ADDON_UMOB_NEW,
		'UMOB_CHID' => ADDON_UMOB_NEW,
		'AdView_CHANNEL' => ADDON_ADVIEW_NEW,
		'ADVIEW_SDK_KEY' => ADDON_ADVIEW_NEW,
		'cooId' => ADDON_KUGUO_NEW,
		'channelId' => ADDON_KUGUO_NEW,
		'net.lezzd.ad' => ADDON_PUSHAD_NEW,
		'com.dianru.sdk.keycode' => ADDON_DIANRU_NEW,
		'dianjin_channel' => ADDON_DIANJIN_NEW,
		'DTN_APP_ID' => ADDON_DATOUNIAO_NEW,
		'DTN_SECRET_KEY' => ADDON_DATOUNIAO_NEW,
		'DTN_PLACE_ID' => ADDON_DATOUNIAO_NEW,
		'OFFER_SDK_CHANNELID' => ADDON_XIAOMAI_OFFER_NEW,
		'ZHUAMOB_OFFER_SDK_CHANNELID' => ADDON_XIAOMAI_RECOMMEND_NEW,
		'PANDA_APP_ID' => ADDON_PANDA_GAME_NEW,
		'ZY_MARKET_ID' => ADDON_PANDA_GAME_NEW,
		'application_id' => ADDON_UPUSH_NEW,
		'channel_id' => ADDON_UPUSH_NEW,
        'ZHUAMOB_APPKEY' => ADDON_ZHUAMOB,
        */
	);

	$file_list_feature = array(
		"/^res\/layout[^\/]*\/dianjoy.*\.xml/" => ADDON_DIANJOY_NEW,
		"/^res\/raw\/wqapp.*\.xml/" => ADDON_WQ_NEW,
		"/^res\/layout\/wy_ad_.*\.xml/" => ADDON_WY_AD_NEW,
		"/^res\/layout\/wy_offer.*\.xml/" => ADDON_WY_OFFER_NEW,
	);

	# 扫描代码的方式
	$classes_dex = array(
		"com/anzhi/sdk/ad/main/AdBaseView" => ADDON_ZHIMENG_SDK,
	);
	
	// 飞沃动态特征（根据sdk的jar包名最后一段产生）
	function addFeiWoDynamicFeature($last_name, &$feature) {
		if (empty($last_name)) {
			return false;
		}
		$captical_last_name = ucwords($last_name);
		// 飞沃动态特征
		$feiwo_dynamic_feature_arr = array(
			".{$last_name}.activity.{$captical_last_name}Activity" => ADDON_FEIWO_NEW,
			".{$last_name}.service.{$captical_last_name}Service" => ADDON_FEIWO_NEW,
			".{$last_name}.receiver.{$captical_last_name}Receiver" => ADDON_FEIWO_NEW,
		);
		// 给feature加特征
		foreach ($feiwo_dynamic_feature_arr as $dynamic_feature => $ad_code) {
			$feature[$dynamic_feature] = $ad_code;
		}
		return true;
	}
	
	function getFeiWoJarCustomName($file) {
		$result = array();
		$cmd = "(unzip -l '${file}' | awk '{print $4}') 2>/dev/null";
		$content = shell_exec($cmd);
		if (!$content) {
			return null;
		}
		$lines = explode("\n", $content);
		
		$last_name_reg = '/^assets\/([^.]+)_b$/';
		$last_name = '';
		foreach ($lines as $line) {
			if (empty($line)) {
				continue;
			}
			if (preg_match($last_name_reg, $line, $matches)) {
				$last_name = $matches[1];
				break;
			}
		}
		return $last_name;
	}

	function test_apk_for_asrm($file, $feature) {
		global $aapt;
		$cmd = "${aapt} d xmltree \"${file}\" AndroidManifest.xml 2>/dev/null";
		$content = shell_exec($cmd);
		$result = array();
        
		if (!$content) {
			return null;
		}
		///////////动态加飞沃特征
		$last_name = getFeiWoJarCustomName($file);
		if (!empty($last_name)) {
			addFeiWoDynamicFeature($last_name, $feature);
		}
		///////////////////////////////////////////
		$lines = explode("\n", $content);
		$count = count($lines);
		for($i = 0; $i < $count; $i++) {
			if (!strstr($lines[$i], 'android:name'))
				continue;
			foreach($feature as $key => $value) {
				if (strstr($lines[$i], $key)) {
					$result[$value] = null;
				}
			}
		}
		return $result;
	}
    
    // 检查文件，只要包含其中一个指定文件就认为有这个sdk
	function test_apk_for_file_list($file, $file_list_feature) {
		$result = array();
		$cmd = "(unzip -l '${file}' | awk '{print $4}') 2>/dev/null";
		$content = shell_exec($cmd);
		if (!$content) {
			return null;
		}
		$lines = explode("\n", $content);
		$count = count($lines);
		for($i = 0; $i < $count; $i++) {
			foreach($file_list_feature as $key => $value) {
				if(preg_match($key, $lines[$i])) {
					$result[$value] = null;
				}
			}
		}
		return $result;
	}

	//检查文件class_dex，代码扫描方式
	function test_apk_for_classes_dex($file,$classes_dex){
		$result = array();
		$cmd = "unzip -l '${file}' | awk '{print $4}' | grep '^classes[0-9]*.dex$'";
    	$r = trim(shell_exec($cmd));
    	if (!$r) return null;
    	$lines = explode("\n", $r);
    	foreach ($lines as $k => $f) {
    		foreach ($classes_dex as $key => $value) {
				$cmd = "unzip -p {$file} '{$f}' | strings | grep '{$key}'";
				$content = shell_exec($cmd);
				if (!$content) {
					continue;
				}
				$result[$value] = null;
			}
    	}
		
		return $result;
	}

	function test_apk_for_addon_NEW($file) {
		if (!is_file($file))
			return -1;
		$result = array();
		$result_num = array();
        global $feature;
		$result_asr = test_apk_for_asrm($file, $feature);
		if ($result_asr) {
			$result += $result_asr;
		}
        global $file_list_feature;
		$result_file_list = test_apk_for_file_list($file, $file_list_feature);
		if ($result_file_list)
			$result += $result_file_list;
		global $classes_dex;
		$result_classes_dex = test_apk_for_classes_dex($file,$classes_dex);
		if ($result_classes_dex)
			$result += $result_classes_dex;

		foreach($result as $key => $value) {
			$result_num[] = $key;
		}
		return $result_num;
	}
    
    //////////////////////////////////////////////////////////////////////////////////
    // 用户中心SDK
    //////////////////////////////////////////////////////////////////////////////////
    // 用户中心不同版本SDK对应特征（指定特征必须全部包含）
    $usercenter_feature = array(
        USERCENTER_SDK_1_0 => array(
            'com.anzhi.usercenter.LoginActivity',
            'com.anzhi.usercenter.InfoManagerActivity',
            'com.anzhi.usercenter.UpdateBindTelTimClearActivity',
            'com.anzhi.usercenter.RegisterActivity',
            'com.anzhi.usercenter.UpdateBindEmailTimClearActivity',
            'com.anzhi.usercenter.UpdateBindTelTimActivity',
            'com.anzhi.usercenter.ChargePwdSettingClearActivity',
            'com.anzhi.usercenter.AccountSafeActivity',
            'com.anzhi.usercenter.ResetPWDActivity',
            'com.anzhi.usercenter.UpdateMsnActivity',
            'com.anzhi.usercenter.StarChooserActivity',
            'com.anzhi.usercenter.UpdateBindEmailTimActivity',
            'com.anzhi.usercenter.UpdateNiChengActivity',
            'com.anzhi.usercenter.ChargeMainActivity',
            'com.anzhi.usercenter.PayWebViewActivity',
            'com.anzhi.usercenter.BaseWebViewActivity',
            'com.anzhi.usercenter.UCenterActivity',
            'com.anzhi.usercenter.InitActivity',
            'com.anzhi.usercenter.ChargePwdSettingActivity',
            'com.anzhi.usercenter.GameOldAccountLoginActivity',
        ),
        USERCENTER_SDK_2_0 => array(
            'com.anzhi.usercenter.sdk.LogoActivity',
            'com.anzhi.usercenter.sdk.LoginActivity',
            'com.anzhi.usercenter.sdk.UserCenterMainActivity',
            'com.anzhi.usercenter.sdk.UserDetailActivity',
            'com.anzhi.usercenter.sdk.RegisterActivity',
            'com.anzhi.usercenter.sdk.UpdateStarActivity',
            'com.anzhi.usercenter.sdk.UpdateQQActivity',
            'com.anzhi.usercenter.sdk.UpdateNickActivity',
            'com.anzhi.usercenter.sdk.AnzhiCurrencyActivity',
            'com.anzhi.usercenter.sdk.PwdSettingActivity',
            'com.anzhi.usercenter.sdk.PwdSettingClearActivity',
            'com.anzhi.usercenter.sdk.AccountSafeActivity',
            'com.anzhi.usercenter.sdk.BindTelActivity',
            'com.anzhi.usercenter.sdk.UnbindTelActivity',
            'com.anzhi.usercenter.sdk.CurrencyChargeWebViewActivity',
            'com.anzhi.usercenter.sdk.ChargeHistoryWebViewActivity',
            'com.anzhi.usercenter.sdk.ConsumHistoryWebViewActivity',
            'com.anzhi.usercenter.sdk.GameChargeWebViewActivity',
            'com.anzhi.usercenter.sdk.OrderRecodeWebViewActivity',
            'com.anzhi.usercenter.sdk.FeedbackWebViewActivity',
            'com.anzhi.usercenter.sdk.OpenLoginforQQActivity',
            'com.anzhi.usercenter.sdk.OpenLoginforWeiboActivity',
            'com.anzhi.usercenter.sdk.BindEmailActivity',
            'com.anzhi.usercenter.sdk.UnbindEmailActivity',
            'com.anzhi.usercenter.sdk.FindPwdActivity',
            'com.anzhi.usercenter.sdk.LoadingActivity',
            'com.anzhi.usercenter.sdk.UcenterOfficialLoginActivity',
        ),
        USERCENTER_SDK_3_0 => array(
            'com.anzhi.usercenter.sdk.LogoActivity',
            'com.anzhi.usercenter.sdk.LoginActivity',
            'com.anzhi.usercenter.sdk.UserCenterMainActivity',
            'com.anzhi.usercenter.sdk.UserDetailActivity',
            'com.anzhi.usercenter.sdk.RegisterActivity',
            'com.anzhi.usercenter.sdk.UpdateStarActivity',
            'com.anzhi.usercenter.sdk.UpdateQQActivity',
            'com.anzhi.usercenter.sdk.UpdateNickActivity',
            'com.anzhi.usercenter.sdk.AnzhiCurrencyActivity',
            'com.anzhi.usercenter.sdk.PwdSettingActivity',
            'com.anzhi.usercenter.sdk.PwdSettingClearActivity',
            'com.anzhi.usercenter.sdk.AccountSafeActivity',
            'com.anzhi.usercenter.sdk.BindTelActivity',
            'com.anzhi.usercenter.sdk.UnbindTelActivity',
            'com.anzhi.usercenter.sdk.CurrencyChargeWebViewActivity',
            'com.anzhi.usercenter.sdk.ChargeHistoryWebViewActivity',
            'com.anzhi.usercenter.sdk.ConsumHistoryWebViewActivity',
            'com.anzhi.usercenter.sdk.GameChargeWebViewActivity',
            'com.anzhi.usercenter.sdk.OrderRecodeWebViewActivity',
            'com.anzhi.usercenter.sdk.FeedbackWebViewActivity',
            'com.anzhi.usercenter.sdk.OpenLoginforQQActivity',
            'com.anzhi.usercenter.sdk.OpenLoginforWeiboActivity',
            'com.anzhi.usercenter.sdk.BindEmailActivity',
            'com.anzhi.usercenter.sdk.UnbindEmailActivity',
            'com.anzhi.usercenter.sdk.FindPwdActivity',
            'com.anzhi.usercenter.sdk.LoadingActivity',
            'com.anzhi.usercenter.sdk.UcenterOfficialLoginActivity',
            'com.anzhi.usercenter.sdk.AnzhiMessageActivity',
            'com.anzhi.usercenter.sdk.ModifyPwdActivity',
            'com.anzhi.usercenter.sdk.ResetPwdActivity',
            'com.anzhi.usercenter.sdk.FindAccountFailActivity',
            'com.anzhi.usercenter.sdk.NoticeDetailActivity',
            'com.anzhi.usercenter.sdk.AnzhiGameBbsActivity',
            'com.anzhi.usercenter.sdk.GameGiftActivity',
            'com.anzhi.usercenter.sdk.GiftBagDetailActivity',
            'com.anzhi.usercenter.sdk.GiftbagRecordActivity',
        ),
    );
    
    // 用户中心SDK文件特征：必须包含以下文件才能算是SDK2.0、3.0
    $usercenter_file_list_feature = array(
        USERCENTER_SDK_2_0 => array(
             "/^assets\/upomp_tbow_config\.xml$/",
             "/^assets\/ucenter$/",
             "/^assets\/anChina_Province_city_zone$/",             
        ),
        USERCENTER_SDK_3_0 => array(
             "/^assets\/upomp_tbow_config\.xml$/",
             "/^assets\/ucenter$/",
             "/^assets\/anChina_Province_city_zone$/",             
        ),
    );
    
    // 用户中心：检查manifest的asrm
    function test_usercenter_apk_for_asrm($file, $feature) {
		global $aapt;
		$cmd = "${aapt} d xmltree \"${file}\" AndroidManifest.xml 2>/dev/null";
		$content = shell_exec($cmd);
		$result = array();
        
		if (!$content) {
			return null;
		}
		$lines = explode("\n", $content);
		$count = count($lines);
        foreach ($feature as $addon_key => $feature_arr) {
            $flag = true;
            foreach ($feature_arr as $single_feature) {
                for ($i = 0; $i < $count; $i++) {
                    if (!strstr($lines[$i], 'android:name'))
                        continue;
                    if (strstr($lines[$i], $single_feature)) {
                        break;
                    }
                }
                if ($i >= $count) {
                    $flag = false;
                    break;
                }
            }
            if ($flag)
                $result[$addon_key] = null;
        }
        
		return $result;
	}
    
    // 用户中心：检查文件，必须包含指定所有文件，否则认为不包含这个sdk，返回结果是不符合条件的sdk记录
    function test_usercenter_apk_file_list($file, $file_list_feature) {
        $result = array();
		$cmd = "(unzip -l '${file}' | awk '{print $4}') 2>/dev/null";
		$content = shell_exec($cmd);
		if (!$content) {
			return null;
		}
		$lines = explode("\n", $content);
		$count = count($lines);
        foreach ($file_list_feature as $addon_key => $file_feature_arr) {
            $flag = true;
            foreach ($file_feature_arr as $file_feature) {
                for($i = 0; $i < $count; $i++) {
                    if (preg_match($file_feature, $lines[$i])) {
                        // 检测到了第N个指定文件，break掉去检测下一个指定文件
                        break;
                    }
                }
                if ($i >= $count) {
                    // 到结尾也没有检测到第N个指定文件，break掉检测下一个sdk的指定文件
                    $flag = false;
                    break;
                }
            }
            if (!$flag)
                $result[$addon_key] = null;
        }
        return $result;
    }
    
    // 检查用户中心的sdk
    function test_usercenter_apk_for_addon_NEW($file) {
        if (!is_file($file))
			return -1;
		$result = array();
		$result_num = array();
        global $usercenter_feature;
		$result_asr = test_usercenter_apk_for_asrm($file, $usercenter_feature);
		if ($result_asr) {
			$result += $result_asr;
		}
        // 处理一下，因为sdk3.0会包含2.0的特征，所以如果检查结果包含3.0，需把2.0去掉
        if (array_key_exists(USERCENTER_SDK_2_0, $result) && array_key_exists(USERCENTER_SDK_3_0, $result))
            unset($result[USERCENTER_SDK_2_0]);
        
        // 检查文件
        global $usercenter_file_list_feature;
        // 得到不符合条件的sdk数组
        $result_file_list = test_usercenter_apk_file_list($file, $usercenter_file_list_feature);
        // 如果是SDK2.0或SDK3.0，需包含指定文件，否则unset掉
        foreach ($result as $key => $value) {
            if (array_key_exists($key, $result_file_list)) {
                unset($result[$key]);
            }
        }
        // 把结果转换一下
        foreach($result as $key => $value) {
			$result_num[] = $key;
		}
		return $result_num;
    }
    
	function get_addon_map()
	{
		$adlist = array(
			1 => '万普',
			2 => '友盟',
			3 => '有米',
			4 => '有米积分墙',
			5 => '桔子',
			6 => '点乐',
			7 => '指点',
			8 => '芒果',
			9 => '谷歌',
			10 => '百度',
			11 => '赢告',
			12 => '易传媒',
			13 => '帷千',
			14 => '微云广告',
			15 => '微云推广',
			16 => '哇棒',
			17 => '随踪',
			18 => '力美',
			19 => '聚赢',
			20 => '架势无线',
			21 => '飞云',
			22 => '多盟',
			23 => '百度统计',
			24 => '安沃',
			25 => '艾德思奇',
			26 => 'Smaato',
			27 => 'Lsence',
			28 => '乐享（广告）',
			29 => '乐享（货架）',
			30 => 'ADUU',
			31 => 'AdTouch',
			32 => '道友道',
			34 => '芒果积分墙',
			35 => 'Vpon',
			36 => '米迪',
			37 => '趣米',
			38 => '果合',
			39 => 'InMobi',
			40 => 'Millennial Media',
			41 => '亿动智道',
			42 => 'iadpush',
			43 => 'uc优盟',
			44 => '易积分',
			45 => '米田',
			46 => 'Greystripe',
			47 => 'MdotM',
			48 => '聚点',
			49 => 'momark',
			50 => 'Ader',
			51 => '触控科技',
			52 => 'chartboost',
			53 => 'MIX智游汇',
			54 => 'ADVIEW',
			55 => 'Tapjoy',
			56 => '酷果',
			57 => 'Pushad',
			58 => '点入',
			59 => '点金',
			60 => '大头鸟',
			61 => '小麦积分墙',
			62 => '小麦推荐墙',
			63 => '果盟',
			64 => 'Panda插屏',
			65 => 'Panda Push',
			66 => 'Panda List',
			67 => 'VSERV',
			68 => 'SP联盟',
			69 => '优雅',
			33 => '安智旧banner',
			70 => '安智新插屏',
			71 => '安智新banner',
			72 => '飞沃banner1',
			73 => '飞沃插屏1',
            74 => '飞沃应用墙1',
            75 => '飞沃banner2',
            76 => '抓猫',
            77 => '飞沃插屏2',
            78 => '聚优推送',
            79 => '手心推送',
            80 => '手心应用墙',
            81 => '飞沃三合一',
            82 => '飞沃四合一',
            83 => '飞沃多合一',
			84 => '飞沃新版',
			85 => '飞沃新版插屏',
			86 => '飞沃新版全屏',
			87 => '飞沃新版应用墙',
			88 => 'oppo广告SDK',
			89 => '腾讯广告SDK',
			90 => '玉米广告SDK',
			91 => '小米广告SDK',
			92 => '智盟广告SDK',

            10001 => 'SDK1.0',
            10002 => 'SDK2.0',
            10003 => 'SDK3.0',
		);
		return $adlist;
	}
	
	function get_addon_name($data) {
		$adlist = get_addon_map();
		if (is_array($data))
		{
			$result = array();
			foreach ($data as $v)
			{
				if (isset($adlist[$v]))
				{
					$result[$v] = $adlist[$v];
				}
			}
			return $result;
		}
		else
		{
			if (isset($adlist[$data]))
				return $adlist[$data];
			else
				return false;
		}
	}
    
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////以下功能测试中: 转为标准xml格式后再判断///////////////////////////////////////////
    $activity_feature = array(
        //activity
		// 万普
		'cn.waps.OffersWebView' => ADDON_WANPU_NEW,
		'com.waps.' => ADDON_WANPU_NEW,
		// 友盟
		//'com.umeng.ad.AdDetailActivity' => ADDON_YOUMENG_NEW,
		//'com.umeng.fb.ConversationActivity' => ADDON_YOUMENG_NEW,
		//'com.umeng.fb.ContactActivity' => ADDON_YOUMENG_NEW,
		// 有米
		'net.youmi.android.AdActivity' => ADDON_YOUMI_NEW,
		'net.youmi.android.AdBrowser' => ADDON_YOUMI_NEW,
		// 有米积分墙
		'net.youmi.android.appoffers.YoumiOffersActivity' => ADDON_YOUMI_APPOFFER_NEW,
		'net.youmi.android.appoffers.AppOffersActivity' => ADDON_YOUMI_APPOFFER_NEW,
		// 桔子
		'com.juzi.main.TheAdVirtualGoods' => ADDON_JUZI_NEW,
		'com.juzi.main.WebActivity' => ADDON_JUZI_NEW,
		'.JuziPluginActivity' => ADDON_JUZI_NEW,
		// 点乐
		'com.dianle.DianleOfferActivity' => ADDON_DIANJOY_NEW,
		// 指点
		'com.adzhidian.view.WebViewActivity' => ADDON_ZHIDIAN_NEW,
		// 道友道（乐告）
		'com.daoyoudao.ad.' => ADDON_DYD_NEW,
		'com.daoyoudao.dankeAd.' => ADDON_DYD_NEW,
		// 芒果
		'com.adsmogo.adview.AdsMogoWebView' => ADDON_MOGO_NEW,
		// 谷歌
		'com.google.ads.AdActivity' => ADDON_ADMOB_NEW,
		// 百度
		'com.baidu.mobads.AppActivity' => ADDON_BAIDU_MOBAD_NEW,
		// 赢告
		'com.winad.android.banner.util.VideoPlayerActivity' => ADDON_WINAD_NEW,
		'com.winad.android.banner.push.PushContentActivity' => ADDON_WINAD_NEW,
		'com.winad.android.offers.OffersActivity' => ADDON_WINAD_NEW,
		'com.winad.android.offers.FeedBackInfo' => ADDON_WINAD_NEW,
		// 易传媒
		'com.adchina.android.ads.views.NomalVideoPlayActivity' => ADDON_ADCHINA_NEW,
		'com.adchina.android.ads.views.AdBrowserView' => ADDON_ADCHINA_NEW,
		'com.adchina.android.ads.views.FullScreenAdActivity' => ADDON_ADCHINA_NEW,
		'com.adchina.android.ads.views.AdVideoPlayerActivity' => ADDON_ADCHINA_NEW,
		// 微云
		'com.wiyun.common.SimpleBrowserActivity' => ADDON_WY_AD_NEW,
		// 微云推广
		'com.wiyun.offer.OfferList' => ADDON_WY_OFFER_NEW,
		// 哇棒
		'com.wooboo.adlib_android.AdActivity' => ADDON_WOOBOO_NEW,
		'com.wooboo.adlib_android.FullActivity' => ADDON_WOOBOO_NEW,
		// 随踪
		'com.suizong.mobplate.ads.AdActivity' => ADDON_SUIZONG_NEW,
		'com.suizong.mobile.ads.AdBrowser' => ADDON_SUIZONG_NEW,
		// 力美
		'com.lmmob.ad.sdk.LmMob' =>ADDON_LMMOB_NEW,
		'cn.immob.sdk.BrowserActivity' =>ADDON_LMMOB_NEW,
		'cn.immob.sdk.util.LMActionHandler' => ADDON_LMMOB_NEW,
		'com.lmmob.ad.sdk.LmMobAdWebView' => ADDON_LMMOB_NEW,
		'com.lmmob.ad.sdk.LmMobFullImageActivity' => ADDON_LMMOB_NEW,
		// 聚赢
		'com.tencent.exmobwin.banner.MobWINBrowserActivity' => ADDON_TENCENT_NEW,
		'com.tencent.exmobwin.banner.DialogActivity' => ADDON_TENCENT_NEW,
		// 飞云
		'com.fractalist.sdk.base.sys.FtActivity' => ADDON_FTAD_NEW,
		// 多盟
		'com.domob.android.ads.DomobActivity' => ADDON_DOMOB_NEW,
		'cn.domob.android.ads.DomobActivity' => ADDON_DOMOB_NEW,
		// 安沃
		'com.adwo.adsdk.AdwoAdBrowserActivity' => ADDON_ADWO_NEW,
		// 艾德思奇
		'com.mobisage.android.MobiSageActivity' => ADDON_MOBISAGE_NEW,
		// Smaato
		'com.smaato.soma.interstitial.InterstitialActivity' => ADDON_SOMA_NEW,
		// Lsence
		'com.l.adlib_android.AdBrowseActivity' => ADDON_LSENSE_NEW,
		// 乐享（广告）
		'cn.appmedia.ad.AdActivity' => ADDON_APPMEDIA_AD_NEW,
		// 乐享（货架）
		'cn.appmedia.adshelf.activity.ApkList' => ADDON_APPMEDIA_SHELF_NEW,
		// ADUU
		'cn.aduu.android.AdSpotActivity' => ADDON_ADUU_NEW,
		'cn.aduu.adsdk.AdSpotActivity' => ADDON_ADUU_NEW,
		'cn.aduu.android.floatad.AdSpotActivity' => ADDON_ADUU_NEW,
		// 安智
		'com.anzhi.anzhipostersdk.WebActivity' => ADDON_ANZHI_BANNER_OLD,
		// 芒果积分墙
		'net.cavas.show.MainLoadCavasActivity' => ADDON_MOGO_APPOFFER_NEW,
		// Vpon
		'com.vpon.adon.android.WebInApp' => ADDON_VPON_NEW,
		'com.vpon.adon.android.CrazyAdRun' => ADDON_VPON_NEW,
		'com.vpon.adon.android.webClientHandler.QRActivity' => ADDON_VPON_NEW,
		'com.vpon.adon.android.webClientHandler.ShootActivity' => ADDON_VPON_NEW,
		'com.googleing.zxinging.client.android.CaptureActivity' => ADDON_VPON_NEW,
		// 米迪
		'net.miidi.ad.banner.AdBannerActivity' => ADDON_MIIDI_NEW,
		'net.miidi.push.MiActivity' => ADDON_MIIDI_NEW,
		'net.miidi.wall.AdWallActivity' => ADDON_MIIDI_NEW,
		// 趣米
		'com.newqm.sdkoffer.QMOfsActivity' => ADDON_NEWQM_NEW,
		'com.newqm.sdkoffer.QuMiActivity' => ADDON_NEWQM_NEW,
		// 果合
		'com.guohead.sdk.GuoheAdActivity' => ADDON_GUOHEAD_NEW,
		// InMobi
		'com.inmobi.androidsdk.IMBrowserActivity' => ADDON_INMOBI_NEW,
		// Millennial Media
		'com.millennialmedia.android.MMAdViewOverlayActivity' => ADDON_MILLENNIAL_NEW,
		'com.millennialmedia.android.MMActivity' => ADDON_MILLENNIAL_NEW,
		'com.millennialmedia.android.VideoPlayer' => ADDON_MILLENNIAL_NEW,
		// Iadpush
		'com.iadpush.adp.' => ADDON_IADPUSH_NEW,
		// uc优盟
		'cn.umob.android.ad.UMOBActivity' => ADDON_UMOB_NEW,
		'cn.umob.android.ad.UMOBSpotAdActivity' => ADDON_UMOB_NEW,
		// 易积分
		'com.emar.escore.' => ADDON_ESCORE_NEW,
		// 米田
		'com.mt.airad.MultiAD'=> ADDON_AIRAD_NEW,
		// Greystripe
		'com.greystripe.sdk.GSFullscreenActivity' => ADDON_GREYSTRIPE_NEW,
		// MdotM
		'com.mdotm.android.ads.MdotmLandingPage' => ADDON_MDOTM_NEW,
		// 聚点
		'com.huawei.juad.android.BrowserActivity' => ADDON_JUAD_NEW,
		// momark
		'com.donson.momark.activity.AdActivity' => ADDON_MOMARK_NEW,
		// Ader
		'com.rrgame.RGActivity' => ADDON_ADER_NEW,
		// chartboost
		'com.chartboost.sdk.CBImpressionActivity' => ADDON_CHARTBOOST_NEW,
		// MIX智游汇
		'com.guohead.mix.MIXViewActivity' => ADDON_MIX_NEW,
		// ADVIEW
		'com.kyview.AdviewWebView' => ADDON_ADVIEW_NEW,
		// Tapjoy
		'com.tapjoy.' => ADDON_TAPJOY_NEW,
		// 酷果
		'com.kuguo.' => ADDON_KUGUO_NEW,
		// Pushad
		'net.lezzd.ad.poster.PosterInfoActivity' => ADDON_PUSHAD_NEW,
		// 点入
		'com.dianru.sdk.AdActivity' => ADDON_DIANRU_NEW,
		'com.dianru.push.NotifyActivity' => ADDON_DIANRU_NEW,
		// 点金
		'com.nd.dianjin.' => ADDON_DIANJIN_NEW,
        'com.bodong.dianjinweb.downloadmanager.DianJinDownloadManager' => ADDON_DIANJIN_NEW,
        'com.bodong.dianjinweb.web.DianjinWebAcitivity' => ADDON_DIANJIN_NEW,
		// 大头鸟
		'com.datouniao.AdPublisher.AdsOffersWebView' => ADDON_DATOUNIAO_NEW,
		// 小麦推荐墙
		'com.zhuamob.recommendsdk.android.RecommendActivity' => ADDON_XIAOMAI_RECOMMEND_NEW,
		'cn.guomob.android.GuomobAdActivity' => ADDON_GUOMOB_NEW,
		'com.pckg.PhAct' => ADDON_PANDA_PUSH_NEW,
		'net.hudongapp.list.AppListActivity' => ADDON_PANDA_LIST_NEW,
		'net.hudongapp.list.AppInfoShow' => ADDON_PANDA_LIST_NEW,
		'net.hudongapp.discuss.AppDiscuss' => ADDON_PANDA_LIST_NEW,
		'net.hudongapp.tabhost.AppTabHostShow' => ADDON_PANDA_LIST_NEW,
		'mobi.vserv.android.ads.VservAdManager' => ADDON_VSERV_NEW,
		'mobi.vserv.org.ormma.view.Browser' => ADDON_VSERV_NEW,
		'mobi.vserv.org.ormma.view.OrmmaActionHandle' => ADDON_VSERV_NEW,
		'com.sp.ads.activity.WallActivity' => ADDON_SP_NEW,
		'com.upush.sdk.PushActivity' => ADDON_UPUSH_NEW,
		'com.anzhi.ad.coverscreen.SA' => ADDON_ANZHI_COVER_NEW,
		'com.anzhi.ad.coverscreen.WA' => ADDON_ANZHI_COVER_NEW,
		'com.anzhi.ad.banner.' => ADDON_ANZHI_BANNER_NEW,
		'com.adfeiwo.ad.banner.' => ADDON_ADFEIWO_BANNER_1,
		'com.adfeiwo.ad.coverscreen.' => ADDON_ADFEIWO_COVERSCREEN_1,
        'com.adfeiwo.ad.appwall.WA' => ADDON_ADFEIWO_APPWALL_1,
        'com.feiwo.appwall.WA' => ADDON_ADFEIWO_APPWALL_1,
        'com.appwall.feiwo.WA' => ADDON_ADFEIWO_APPWALL_1,
        'com.adfeiwo.banner.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.banner.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwoone.banner.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.fw.bn.WebViewActivity' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwoone.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwoone.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwotwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwotwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwothree.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwothree.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwofour.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwofour.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.fivefeiwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.fivefeiwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.sixfeiwo.coverscreen.SA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.sixfeiwo.coverscreen.WA' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.jypush.JActivity' => ADDON_JYPUSH,
        'com.feiwo.activity.PA' => ADDON_FEIWO_THREEINONE,
        'com.feiwo.view.IA' => ADDON_FEIWO_THREEINONE,
        'com.feiwo.RDA' => ADDON_FEIWO_THREEINONE,
        'com.fw.tzo.activity.WebActivity' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.MA' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.FwSActivity' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.FwAdDetailActivity' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.activity.FwWallAdListActivity' => ADDON_FEIWO_FOURINONE,
        // 飞沃多合一
        'com.fw.tzthree.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwMA' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.activity.FwBoxDActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwMA' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwMA' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwMA' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwWebActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwMA' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwSActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwAdDetailActivity' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.activity.FwWallAdListActivity' => ADDON_FEIWO_ALLINONE,
		
		//新版飞沃
		'.activity.FwWebActivity' => ADDON_FEIWO_NEW,
		'.activity.FwCommonActivity' => ADDON_FEIWO_NEW,
		//新版飞沃插屏
		'.activity.FwMA' => ADDON_FEIWO_COVER_NEW,
		//新版飞沃全屏广告
		'.activity.FwSActivity' => ADDON_FEIWO_FULLSCREEN_NEW,
		//新版飞沃应用墙
		'.activity.FwAdDetailActivity' => ADDON_FEIWO_APPWALL_NEW,
		'.activity.FwWallAdListActivity' => ADDON_FEIWO_APPWALL_NEW,

    );
    
    $service_feature = array(
        //service
		// 友盟
		//'com.umeng.common.net.DownloadingService' => ADDON_YOUMENG_NEW,
		// 有米
		'net.youmi.android.AdService' => ADDON_YOUMI_NEW,
		// 点乐
		'com.dianle.DianleOfferHelpService' => ADDON_DIANJOY_NEW,
		// 道友道（乐告）
		'com.dyds.ad.' => ADDON_DYD_NEW,
		// 芒果
		'om.adsmogo.controller.service.UpdateService' => ADDON_MOGO_NEW,
		'com.adsmogo.controller.service.CountService' => ADDON_MOGO_NEW,
		// 赢告
		'com.winad.android.banner.push.MyService' => ADDON_WINAD_NEW,
		'com.winad.android.offers.parameter.SyService' => ADDON_WINAD_NEW,
		// 随踪
		'com.suizong.mobile.ads.DownloadService' => ADDON_SUIZONG_NEW,
		// 力美
		'cn.immob.sdk.net.DownloadService' => ADDON_LMMOB_NEW,
		// 飞云
		'com.fractalist.sdk.base.sys.FtService' => ADDON_FTAD_NEW,
		// 艾德思奇
		'com.mobisage.android.MobiSageApkService' => ADDON_MOBISAGE_NEW,
		// 乐享（货架）
		'cn.appmedia.adshelf.download.DownloadService' => ADDON_APPMEDIA_SHELF_NEW,
		// 安智
		'com.anzhi.anzhipostersdk.service.SystemService' => ADDON_ANZHI_BANNER_OLD,
		'com.anzhi.anzhipostersdk.service.DownloadService' => ADDON_ANZHI_BANNER_OLD,
		// 米迪
		'net.miidi.push.AdPushService' => ADDON_MIIDI_NEW,
		// Iadpush
		'com.iadpush.adp.NS' => ADDON_IADPUSH_NEW,
		'com.iadpush.adp.BS' => ADDON_IADPUSH_NEW,
		// Ader
		'com.renren.sdk.download.AderDownloadService' => ADDON_ADER_NEW,
		'com.rrgame.download.RGDownloadService' => ADDON_ADER_NEW,
		// 触控科技
		'com.punchbox.monitor.ArchiveMonitorDownloadService' => ADDON_PUNCHBOX_NEW,
		// ADVIEW
		'com.kyview.DownloadService' => ADDON_ADVIEW_NEW,
		// 酷果
		'com.kuguo.' => ADDON_KUGUO_NEW,
		// 点金
		'com.nd.dianjin.' => ADDON_DIANJIN_NEW,
        'com.bodong.dianjinweb.service.DianJinService' => ADDON_DIANJIN_NEW,
		// 大头鸟
		'com.datouniao.AdPublisher.AdsService' => ADDON_DATOUNIAO_NEW,
		'com.android.game.support.support.PSupportService' => ADDON_PANDA_GAME_NEW,
		'com.pckg.WfSer' => ADDON_PANDA_PUSH_NEW,
		'com.pckg.WfSer' => ADDON_PANDA_PUSH_NEW,
		'net.hudongapp.list.AppListServiceLock' => ADDON_PANDA_LIST_NEW,
		'com.sp.ads.service.SPService' => ADDON_SP_NEW,
		'com.upush.sdk.PushService' => ADDON_UPUSH_NEW,
        'com.mj.MjService' => ADDON_ZHUAMOB,
        'com.jypush.JService' => ADDON_JYPUSH,
        'com.VPmarket.vbnd.tvseven.MarServer' => ADDON_SHOUXINAD_PUSH,
        'com.VPmarket.vbnd.tvseven.SMarsServer' => ADDON_SHOUXINAD_PUSH,
        'com.wmstwo.vtwo.vdeve.MmapServer' => ADDON_SHOUXINAD_WALL,
        'com.wmstwo.vtwo.vdeve.MVtwoService' => ADDON_SHOUXINAD_WALL,
        // 飞沃四合一
        'com.fw.tzo.service.DownloadService' => ADDON_FEIWO_FOURINONE,
        // 飞沃多合一
        'com.fw.tzthree.service.FwDservice' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.service.FwDservice' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.service.FwDservice' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.service.FwDservice' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.service.FwDservice' => ADDON_FEIWO_ALLINONE,
		//新版飞沃
		'.service.FwDservice' => ADDON_FEIWO_NEW,
    );
    
    $receiver_feature = array(
        //receiver
		// 有米
		'net.youmi.android.AdReceiver' => ADDON_YOUMI_NEW,
		// 有米积分墙
		'net.youmi.android.offers.OffersReceiver' => ADDON_YOUMI_APPOFFER_NEW,
		// 赢告
		'com.winad.android.banner.push.BootReceiver' => ADDON_WINAD_NEW,
		'com.winad.android.offers.AutoOpenReceiver' => ADDON_WINAD_NEW,
		// 微云
		'com.wiyun.common.WiLauncher' => ADDON_WY_AD_NEW,
		// 力美
		'cn.immob.sdk.brocastreceiver.AppChangeBrocastreceiver' => ADDON_LMMOB_NEW,
		// 艾德思奇
		'com.mobisage.android.MobiSageAdReceiver' => ADDON_MOBISAGE_NEW,
		// 安智
		'com.anzhi.anzhipostersdk.receiver.ProcessAlarmReceiver' => ADDON_ANZHI_BANNER_OLD,
		// 米迪
		'net.miidi.push.MiReceiver' => ADDON_MIIDI_NEW,
		// Iadpush
		'com.iadpush.adp.Re' => ADDON_IADPUSH_NEW,
		// 触控科技
		'com.punchbox.monitor.ArchiveMonitorReceiver' => ADDON_PUNCHBOX_NEW,
		// 酷果
		'com.kuguo.' => ADDON_KUGUO_NEW,
		// Pushad
		'net.lezzd.ad.poster.ReceiverAlarm' => ADDON_PUSHAD_NEW,
		// 点入
		'com.dianru.sdk.NetWorkChanged' => ADDON_DIANRU_NEW,
		'com.android.game.support.tool.PSupportReceiver' => ADDON_PANDA_GAME_NEW,
		'com.pckg.BroR' => ADDON_PANDA_PUSH_NEW,
		'net.hudongapp.util.PandaBroadReceiver' => ADDON_PANDA_LIST_NEW,
		'com.upush.sdk.PushReceiver' => ADDON_UPUSH_NEW,
		'com.anzhi.ad.coverscreen.SR' => ADDON_ANZHI_COVER_NEW,
		'com.anzhi.ad.banner.' => ADDON_ANZHI_BANNER_NEW,
		'com.adfeiwo.ad.banner.' => ADDON_ADFEIWO_BANNER_1,
		'com.adfeiwo.ad.coverscreen.' => ADDON_ADFEIWO_COVERSCREEN_1,
        'com.adfeiwo.ad.appwall.SR' => ADDON_ADFEIWO_APPWALL_1,
        'com.feiwo.appwall.SR' => ADDON_ADFEIWO_APPWALL_1,
        'com.appwall.feiwo.SR' => ADDON_ADFEIWO_APPWALL_1,
        'com.adfeiwo.banner.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.banner.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwoone.banner.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.fw.bn.AdReceiver' => ADDON_ADFEIWO_BANNER_2,
        'com.feiwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwoone.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwotwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwothree.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.feiwofour.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.fivefeiwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.sixfeiwo.coverscreen.SR' => ADDON_ADFEIWO_COVERSCREEN_2,
        'com.jypush.JReceiver' => ADDON_JYPUSH,
        'com.feiwo.receiver.InReceiver' => ADDON_FEIWO_THREEINONE,
        'com.feiwo.receiver.ConnectReceiver' => ADDON_FEIWO_THREEINONE,
        'com.fw.tzo.receiver.FwBReceiver' => ADDON_FEIWO_FOURINONE,
        'com.fw.tzo.receiver.FwCCReceiver' => ADDON_FEIWO_FOURINONE,
        // 飞沃多合一
        'com.fw.tzthree.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzthree.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfour.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
        'com.fw.tzfive.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fw.toth.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.receiver.FwBReceiver' => ADDON_FEIWO_ALLINONE,
		'com.fa.fb.receiver.FwCCReceiver' => ADDON_FEIWO_ALLINONE,
		
		//新版飞沃
		'.receiver.FwBReceiver' => ADDON_FEIWO_NEW,
		'.receiver.FwCCReceiver' => ADDON_FEIWO_NEW,
		'.receiver.FwSReceiver' => ADDON_FEIWO_NEW,

    );
    /*
    $meta_data_feature = array(
        //meta-data
		'WAPS' => ADDON_WANPU_NEW,
		//'UMENG_' => ADDON_YOUMENG_NEW,
		'YOUMI_CHANNEL' => ADDON_YOUMI_NEW,
		'JUZI_APPID' => ADDON_JUZI_NEW,
		'com.dlnetwork.cid' => ADDON_DIANJOY_NEW,
		'com.view.AdView.pid' => ADDON_ZHIDIAN_NEW,
		'ADMOGO_KEY' => ADDON_MOGO_NEW,
		'ADMOB_PUBLISHER_ID' => ADDON_ADMOB_NEW,
		'BaiduMobAd_APP_ID' => ADDON_BAIDU_MOBAD_NEW,
		'BaiduMobAd_APP_SEC' => ADDON_BAIDU_MOBAD_NEW,
		'PUBLISHER_ID_BANNER' => ADDON_WINAD_NEW,
		'PUBLISHER_ID_OFFERS' => ADDON_WINAD_NEW,
		'TESTMODE_BANNER' => ADDON_WINAD_NEW,
		'TESTMODE_OFFERS' => ADDON_WINAD_NEW,
		'com.wiyun.sdk.channel' => ADDON_WY_OFFER_NEW,
		'Wooboo_PID' => ADDON_WOOBOO_NEW,
		'APP_CHANNEL' => ADDON_TENCENT_NEW,
		'APP_ID' => ADDON_TENCENT_NEW,
		'cn.casee.adsdk.cid' => ADDON_CASEE_NEW,
		'cn.casee.adsdk.appid' => ADDON_CASEE_NEW,
		'cn.casee.adsdk.istesting' => ADDON_CASEE_NEW,
		'DOMOB_PID' => ADDON_DOMOB_NEW,
		//'BaiduMobAd_' => ADDON_BAIDU_STAT_NEW,
		//'BaiduMarket' => ADDON_BAIDU_STAT_NEW,
		'Adwo_PID' => ADDON_ADWO_NEW,
		'Mobisage_channel' => ADDON_MOBISAGE_NEW,
		'l_channel' => ADDON_LSENSE_NEW,
		'ADUU_SDK_' => ADDON_ADUU_NEW,
		'appsec' => ADDON_ADTOUCH_NEW,
		'DYD_APPID' => ADDON_DYD_NEW,
		'DYD_CHANNELID' => ADDON_DYD_NEW,
		'miidi_channelid' => ADDON_MIIDI_NEW,
		'QuMiappid' => ADDON_NEWQM_NEW,
		'QuMiappsec' => ADDON_NEWQM_NEW,
		'QuMiChannel'=> ADDON_NEWQM_NEW,
		'GH_APPKEY' => ADDON_GUOHEAD_NEW,
		'SM_APP_ID' => ADDON_SM_AD_NEW,
		'SM_AD_REFRESH_INTERVAL' => ADDON_SM_AD_NEW,
		'apkey' => ADDON_IADPUSH_NEW,
		'UMOB_APPKEY' => ADDON_UMOB_NEW,
		'UMOB_CHID' => ADDON_UMOB_NEW,
		'AdView_CHANNEL' => ADDON_ADVIEW_NEW,
		'ADVIEW_SDK_KEY' => ADDON_ADVIEW_NEW,
		'cooId' => ADDON_KUGUO_NEW,
		'channelId' => ADDON_KUGUO_NEW,
		'net.lezzd.ad' => ADDON_PUSHAD_NEW,
		'com.dianru.sdk.keycode' => ADDON_DIANRU_NEW,
		'dianjin_channel' => ADDON_DIANJIN_NEW,
		'DTN_APP_ID' => ADDON_DATOUNIAO_NEW,
		'DTN_SECRET_KEY' => ADDON_DATOUNIAO_NEW,
		'DTN_PLACE_ID' => ADDON_DATOUNIAO_NEW,
		'OFFER_SDK_CHANNELID' => ADDON_XIAOMAI_OFFER_NEW,
		'ZHUAMOB_OFFER_SDK_CHANNELID' => ADDON_XIAOMAI_RECOMMEND_NEW,
		'PANDA_APP_ID' => ADDON_PANDA_GAME_NEW,
		'ZY_MARKET_ID' => ADDON_PANDA_GAME_NEW,
		'application_id' => ADDON_UPUSH_NEW,
		'channel_id' => ADDON_UPUSH_NEW,
        'ZHUAMOB_APPKEY' => ADDON_ZHUAMOB,
    );
    */
    
    function aapt_xmltree_to_standard_xml($src) {
        $standard_xml_str = "";
        specialXMLCharacterTransfer($src);
        $src = str_replace("\r\n", "\n", $src);
        $arr = explode("\n", $src);
        
        $last = 0;
        $unclose_arr = array();
        if (count($arr) < 2) {
            return -1;
        }
        
        $namespace_key = $manifest_key = -1;
        // 找到N: android=xxx格式的一行
        foreach($arr as $key => $line) {
            if (preg_match("/^(?:\s)*N: android=([\s\S]+)/", $line, $matches)) {
                $namespace = $matches[1];
                $namespace_key = $key;
                break;
            }
        }
        // 找到E: manifest格式的一行
        foreach($arr as $key => $line) {
            if (preg_match('/^((?:\s)*)E: manifest/', $line, $matches)) {
                $space_manifest = strlen($matches[1]) + 2;
                $manifest_key = $key;
                break;
            }
        }
        if ($namespace_key === -1 || $manifest_key === -1)
            return -1;
        if ($namespace_key >= $manifest_key)
            return -1;
        // 把namespace_key以下，manifest_key以上的元素上移
        for($i = $namespace_key+1; $i <= $manifest_key; $i++) {
            $arr[$i-1] = $arr[$i];
        }
        $arr[$manifest_key] = "";
        for($i = 0; $i < $space_manifest; $i++) {
            $arr[$manifest_key] .= " ";
        }
        $arr[$manifest_key] .= "A: xmlns:android=\"{$namespace}\"";
        
        foreach($arr as $key => $line) {
            // 判断是元素还是属性
            if (preg_match("/^((?:\s)*)E: ([\w-]+)/", $line, $matches)) {
                // 是元素
                $space_num = strlen($matches[1]);
                $biaoqian = $matches[2];
                if ($last != 0) {
                    // 小闭合
                    $standard_xml_str .= '>';
                }
                if ($space_num < $last) {
                    // 大闭合               
                    while(!empty($unclose_arr)){
                        $i = count($unclose_arr)-1;
                        $tmp = $unclose_arr[$i];
                        $tmp_space_num = $tmp['space_num'];
                        $tmp_biaoqian = $tmp['biaoqian'];
                        if ($tmp_space_num < $space_num)
                            break;
                        $standard_xml_str .= '</' . $tmp_biaoqian . '>';
                        array_pop($unclose_arr);
                    }
                }
                // 新建前当标签，但不闭合，由后面情况决定是否闭合
                $standard_xml_str .= '<' . $biaoqian;
                
                $unit = array('biaoqian' => $biaoqian, 'space_num' => $space_num);
                $unclose_arr[] = $unit;
                
                // 保存空格数
                $last = $space_num;
            //} else if (preg_match("/^((?:\s)*)A: ([\w:]+)(?:\([\s\S]*\))?=(?:\([\s\S]*\))?([^(]+)(?:\([\s\S]*\))?/", $line, $matches)) {
            } else if (preg_match('/^(\s*)A: ([\w:]+)(?:\(0x\w+\))?=("[^"]*"|@0x\w+|\(type 0x\w+\)0x\w+|[\s\S]+?)(?:\([\s\S]+\))?/', $line, $matches)) {
                // 是属性
                $space_num = strlen($matches[1]);
                $biaoqian = $matches[2];
                $value = $matches[3];
                // 如果属性值不是字符串（即值非"XXX"格式，而是0x1之类的，则给其两边加上双引号，否则会导致xml解析类无法解析）
                if (preg_match('/^([^\"]+)/', $value, $matches2)) {
                    $value = '"' . $matches2[1] . '"';
                }
                $standard_xml_str .= " {$biaoqian}={$value}";
                
                // 保存空格数
                $last = $space_num;
            }
        }
        // 对unclose_arr数组里剩余的标签全部闭合
        if ($last != 0) {
            // 小闭合
            $standard_xml_str .= '>';
        }
        while(!empty($unclose_arr)){
            $i = count($unclose_arr)-1;
            $tmp = $unclose_arr[$i];
            $tmp_space_num = $tmp['space_num'];
            $tmp_biaoqian = $tmp['biaoqian'];
            $standard_xml_str .= '</' . $tmp_biaoqian . '>';
            array_pop($unclose_arr);
        }
        // 返回标准xml字符串
        return $standard_xml_str;
    }
    
    // 将域名空间换成普通字符串，即将冒号:换成下划线_，并返回数组
    function parseNamespaceXml($xmlstr) {
        $xmlstr = preg_replace('/\sxmlns="(.*?)"/', ' _xmlns="${1}"', $xmlstr);
        $xmlstr = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${2}_${3}', $xmlstr);
        $xmlstr = preg_replace('/(\w+):(\w+)="(.*?)"/', '${1}_${2}="${3}"', $xmlstr);
        
        $doc = new DOMDocument();
        $ret = @$doc->loadXML($xmlstr);
        if (!$ret)
            return false;
        return $doc;
    }
    
    function specialXMLCharacterTransfer(&$xmlstr) {
        $str_arr = str_split($xmlstr);
        $trans_arr = array(
            '<' => '&lt;',
            '>' => '&gt;',
            '&' => '&amp;',
            //'\'' => '&apos;',
            //'"' => '&quot;',
        );
        $xmlstr = strtr($xmlstr, $trans_arr);
    }
    
    function test_apk_for_asrm2($file) {
        global $aapt;
        $cmd = "${aapt} d xmltree \"${file}\" AndroidManifest.xml 2>/dev/null";
        $manifest_str = shell_exec($cmd);
        if (!$manifest_str) {
			return null;
		}
        $standard_xml_str = aapt_xmltree_to_standard_xml($manifest_str);
        if ($standard_xml_str === -1) {
            return -1;
        }
        $dom = parseNamespaceXml($standard_xml_str);
        if (!$dom) {
            file_put_contents("/tmp/AdSdkScan.error.log", "parseNamespaceXml() error! file: {$file}\n", FILE_APPEND);
            return -1;
        }
        $result = array();
        $test_element_arr = array(
            "activity"  => "activity",
            "service"   => "service",
            "receiver"  => "receiver",
            // "meta-data" => "meta_data",
        );
        foreach($test_element_arr as $test_element_key => $test_element_value) {
            $element_dom = $dom->getElementsByTagName($test_element_key);
            foreach($element_dom as $element){ 
                $android_name = $element->getAttribute('android_name');
                $element_feature = $test_element_value . "_feature";
                global ${$element_feature};
                foreach(${$element_feature} as $key => $value) {
                    if (strstr($android_name, $key)) {
                        $result[$value] = null;
                    }
                }
            }
        }
        return $result;
    }
    
    function test_apk_for_addon_NEW2($file) {
		if (!is_file($file))
			return -1;
		$result = array();
		$result_num = array();
		$result_asr = test_apk_for_asrm2($file);
        if ($result_asr === -1) {
            // 用test_apk_for_asrm
            global $feature;
            $result_asr = test_apk_for_asrm($file, $feature);
        }
        if ($result_asr) {
			$result += $result_asr;
		}
        global $file_list_feature;
		$result_file_list = test_apk_for_file_list($file, $file_list_feature);
		if ($result_file_list)
			$result += $result_file_list;
		foreach($result as $key => $value) {
			$result_num[] = $key;
		}
		return $result_num;
	}
    ////////////////////////////////////以上：new: 转为标准xml格式后再判断///////////////////////////////////////////
endif;
?>
