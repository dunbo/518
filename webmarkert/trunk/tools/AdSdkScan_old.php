<?php

if (!function_exists('test_apk_for_addon')):

    $aapt = '/data/www/wwwroot/new-wwwroot/config/gnu/aapt';
    if (!is_file($aapt)) {
        $aapt = '/data/www/wwwroot/config/gnu/aapt';
        if (!is_file($aapt))
            $aapt = 'aapt';
    }

	define('ADDON_WANPU', 1);
	define('ADDON_YOUMENG', 2);
	define('ADDON_YOUMI', 4);
	define('ADDON_YOUMI_APPOFFER', 8);
	define('ADDON_JUZI', 16);
	define('ADDON_DIANJOY', 32);
	define('ADDON_ZHIDIAN', 64);
	define('ADDON_MOGO', 128);
	define('ADDON_ADMOB', 256);
	define('ADDON_BAIDU_MOBAD', 512);
	define('ADDON_WINAD', 1024);
	define('ADDON_ADCHINA', 2048);
	define('ADDON_WQ', 4096);
	define('ADDON_WY_AD', 8192);
	define('ADDON_WY_OFFER', 16384);
	define('ADDON_WOOBOO', 32768);
	define('ADDON_SUIZONG', 65536);
	define('ADDON_LMMOB', 131072);
	define('ADDON_TENCENT', 262144);
	define('ADDON_CASEE', 524288);
	define('ADDON_FTAD', 1048576);
	define('ADDON_DOMBO', 2097152);
	define('ADDON_BAIDU_STAT', 4194304);
	define('ADDON_ADWO', 8388608);
	define('ADDON_MOBISAGE', 16777216);
	define('ADDON_SOMA', 33554432);
	define('ADDON_LSENSE', 67108864);
	define('ADDON_APPMEDIA_AD', 134217728);
	define('ADDON_APPMEDIA_SHELF', 268435456);
	define('ADDON_ADUU', 536870912);
	define('ADDON_ADTOUCH', 1073741824);
	define('ADDON_DYD', 2147483648);
	define('ADDON_ANZHI', 4294967296);
	define('ADDON_ANZHI_COVER', 8589934592);

	function test_apk_for_addon_invoke(&$ctx, $prefix, $ln, $mask) {
		$ret = 0;
		$suffix = array();
		$max = 34;
		$n = 1;
		for ($i = 0; $i < $max; $i++) {
			$suffix[] = $n;
			$n = $n << 1;
		}
		foreach ($suffix as $s) {
			if (($s & $mask) == 0)
				continue;
			$func = "${prefix}_${s}";
			if (function_exists($func)) {
				$ret |= $func($ctx, $ln);
			}
		}
		return $ret;
	}

	function test_apk_manifest_callback_4(&$ctx, $ln) {
		if (strstr($ln, 'net.youmi.android.AdActivity'))
			return ADDON_YOUMI;
		return 0;
	}

	function test_apk_manifest_callback_8(&$ctx, $ln) {
		if (strstr($ln, 'net.youmi.android.appoffers.AppOffersActivity'))
			return ADDON_YOUMI_APPOFFER;
		return 0;
	}

	function test_apk_manifest_callback_16(&$ctx, $ln) {
		if (!isset($ctx['juzi_flags']))
			$ctx['juzi_flags'] = 0;
		if (strstr($ln,'com.juzi.virtualgoods.main.TheAdVirtualGoods'))
			$ctx['juzi_flags'] += 1;
		if (strstr($ln,'com.juzi.tool.DownLing'))
			$ctx['juzi_flags'] += 1;
		if (strstr($ln,'com.juzi.ad.WebActivity'))
			$ctx['juzi_flags'] += 1;
		if ($ctx['juzi_flags'] == 3)
			return ADDON_JUZI;
		return 0;
	}

	function test_apk_manifest_callback_32(&$ctx, $ln) {
		if (strstr($ln, 'com.dianjoy.DianjoyOfferActivity'))
			return ADDON_DIANJOY;
		if (strstr($ln, 'com.dianjoy.DianjoyOfferHelpService'))
			return ADDON_DIANJOY;
		return 0;
	}

	function test_apk_manifest_callback_1024(&$ctx, $ln) {
		if (strstr($ln, 'com.winad.android.banner.util.VideoPlayerActivity'))
			return ADDON_WINAD;
		if (strstr($ln, 'com.winad.android.banner.push.PushContentActivity'))
			return ADDON_WINAD;
		if (strstr($ln, 'com.winad.android.banner.push.BootReceiver'))
			return ADDON_WINAD;
		if (strstr($ln, 'com.winad.android.banner.push.MyService'))
			return ADDON_WINAD;
		return 0;
	}

	function test_apk_manifest_callback_2048(&$ctx, $ln) {
		if (strstr($ln, 'com.adchina.android.ads.views.NomalVideoPlayActivity'))
			return ADDON_ADCHINA;
		return 0;
	}

	function test_apk_manifest_callback_16384(&$ctx, $ln) {
		if (strstr($ln, 'com.wiyun.offer.OfferList'))
			return ADDON_WY_OFFER;
		return 0;
	}

	function test_apk_manifest_callback_32768(&$ctx, $ln) {
		if (strstr($ln, 'com.wooboo.adlib_android.AdActivity'))
			return ADDON_WOOBOO;
		if (strstr($ln, 'com.wooboo.adlib_android.FullActivity'))
			return ADDON_WOOBOO;
		return 0;
	}

	function test_apk_manifest_callback_65536(&$ctx, $ln) {
		if (strstr($ln, 'com.suizong.mobplate.ads.AdActivity'))
			return ADDON_SUIZONG;
		return 0;
	}

	function test_apk_manifest_callback_131072(&$ctx, $ln) {
		if (strstr($ln, 'com.lmmob.ad.sdk.LmMobAdWebView'))
			return ADDON_LMMOB;
		if (strstr($ln, 'com.lmmob.ad.sdk.LmMobFullImageActivity'))
			return ADDON_LMMOB;
		return 0;
	}

	function test_apk_manifest_callback_1048576(&$ctx, $ln) {
		if (strstr($ln, 'com.fractalist.sdk.base.sys.FtService'))
			return ADDON_FTAD;
		if (strstr($ln, 'com.fractalist.sdk.base.sys.FtActivity'))
			return ADDON_FTAD;
		return 0;
	}

	function test_apk_manifest_callback_2097152(&$ctx, $ln) {
		if (strstr($ln, 'com.domob.android.ads.DomobActivity'))
			return ADDON_DOMOB;
		return 0;
	}

	function test_apk_manifest_callback_8388608(&$ctx, $ln) {
		if (strstr($ln, 'com.adwo.adsdk.AdwoAdBrowserActivity'))
			return ADDON_ADWO;
		return 0;
	}

	function test_apk_manifest_callback_67108864(&$ctx, $ln) {
		if (strstr($ln, 'com.l.adlib_android.AdBrowseActivity'))
			return ADDON_LSENSE;
		return 0;
	}

	function test_apk_manifest_callback_134217728(&$ctx, $ln) {
		if (strstr($ln, 'cn.appmedia.ad.AdActivity'))
			return ADDON_APPMEDIA_AD;
		return 0;
	}

	function test_apk_manifest_callback_268435456(&$ctx, $ln) {
		if (strstr($ln, 'cn.appmedia.adshelf.activity.ApkList'))
			return ADDON_APPMEDIA_SHELF;
		if (strstr($ln, 'cn.appmedia.adshelf.download.DownloadService'))
			return ADDON_APPMEDIA_SHELF;
		return 0;
	}

	function test_apk_manifest_callback_536870912(&$ctx, $ln) {
		if (strstr($ln, 'cn.aduu.adsdk.AdSpotActivity'))
			return ADDON_ADUU;
		return 0;
	}

	function test_apk_manifest_callback_2147483648(&$ctx, $ln) {
		if (strstr($ln, 'com.daoyoudao.ad.CAdA') || strstr($ln, 'com.daoyoudao.ad.CAdS') || strstr($ln, 'com.daoyoudao.ad.CAdR')
		|| strstr($ln, 'com.daoyoudao.ad.CAdRB') || strstr($ln, 'com.daoyoudao.dankeAd.DankeService')
		|| strstr($ln, 'com.daoyoudao.dankeAd.DankeActivity') || strstr($ln, 'com.dyds.ad'))
			return ADDON_DYD;
		return 0;
	}

	function test_apk_manifest_callback_4294967296(&$ctx, $ln) {
		if (strstr($ln, 'com.anzhi.anzhipostersdk.service.SystemService')
		|| strstr($ln, 'com.anzhi.anzhipostersdk.service.DownloadService')
		|| strstr($ln, 'com.anzhi.anzhipostersdk.receiver.ProcessAlarmReceiver')
		|| strstr($ln, 'com.anzhi.anzhipostersdk.WebActivity')) {
			return ADDON_ANZHI;
		}
		return 0;
	}

	function test_apk_manifest_callback_8589934592(&$ctx, $ln) {
		if (strstr($ln, 'com.anzhi.ad.coverscreen.SA')
		|| strstr($ln, 'com.anzhi.ad.coverscreen.WA')
		|| strstr($ln, 'com.anzhi.ad.coverscreen.SR')) {
			return ADDON_ANZHI_COVER;
		}
		return 0;
	}

	function test_apk_manifest_meta_data_callback_1(&$ctx, $ln) {
		if ($ln == 'WAPS' || $ln == 'WAPS_ID' || $ln == 'WAPS_PID')
			return ADDON_WANPU;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_2(&$ctx, $ln) {
		//if ($ln == 'UMENG_APPKEY' || $ln == 'UMENG_CHANNEL')
		//    return ADDON_YOUMENG;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_64(&$ctx, $ln) {
		if ($ln == 'com.view.AdView.pid')
			return ADDON_ZHIDIAN;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_128(&$ctx, $ln) {
		if ($ln == 'ADMOGO_KEY')
			return ADDON_MOGO;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_256(&$ctx, $ln) {
		if ($ln == 'ADMOB_PUBLISHER_ID')
			return ADDON_ADMOB;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_512(&$ctx, $ln) {
		if ($ln == 'BaiduMobAd_APP_ID' || $ln == 'BaiduMobAd_APP_SEC')
			return ADDON_BAIDU_MOBAD;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_1024(&$ctx, $ln) {
		if ($ln == 'PUBLISHER_ID_BANNER' || $ln == 'TESTMODE_BANNER')
			return ADDON_WINAD;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_32768(&$ctx, $ln) {
		if ($ln == 'Market_ID' || $ln == 'Wooboo_PID')
			return ADDON_WOOBOO;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_524288(&$ctx, $ln) {
		if ($ln == 'cn.casee.adsdk.cid' || $ln == 'cn.casee.adsdk.appid' || $ln == 'cn.casee.adsdk.istesting')
			return ADDON_CASEE;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_2097152(&$ctx, $ln) {
		if ($ln == 'DOMOB_PID')
			return ADDON_DOMOB;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_4194304(&$ctx, $ln) {
		$test = array('BaiduMobAd_CHANNEL', 'BaiduMarket', 'BaiduMobAd_STAT_ID', 'BaiduMobAd_EXCEPTION_LOG', 'BaiduMobAd_SEND_STRATEGY', 'BaiduMobAd_TIME_INTERVAL', 'BaiduMobAd_ONLY_WIFI');
		//if (in_array($ln, $test))
		//    return ADDON_BAIDU_STAT;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_8388608(&$ctx, $ln) {
		if ($ln == 'Adwo_PID')
			return ADDON_ADWO;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_16777216(&$ctx, $ln) {
		if (strncmp($ln, 'mobiSage', 8) == 0)
			return ADDON_MOBISAGE;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_67108864(&$ctx, $ln) {
		if (strncmp($ln, 'l_channel', 9) == 0)
			return ADDON_LSENSE;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_1073741824(&$ctx, $ln) {
		if ($ln == 'appsec')
			return ADDON_ADTOUCH;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_2147483648(&$ctx, $ln) {
		if ($ln == 'DYD_APPID' || $ln == 'DYD_CHANNELID')
			return ADDON_DYD;
		return 0;
	}

	function test_apk_manifest_meta_data_callback_4294967296(&$ctx, $ln) {
		if ($ln == 'AnZhi')
			return ADDON_ANZHI;
		return 0;
	}

	function test_apk_uses_library_callback_32(&$ctx, $ln) {
		if (strncmp($ln, "dianjoy", 7) == 0)
			return ADDON_DIANJOY;
		return 0;
	}

	function test_apk_uses_library_callback_1024(&$ctx, $ln) {
		if (strncmp($ln, "winAdBanner", 11) == 0)
			return ADDON_WINAD;
		return 0;
	}

	function test_apk_uses_library_callback_2048(&$ctx, $ln) {
		if (strncmp($ln, "adchina", 7) == 0)
			return ADDON_ADCHINA;
		return 0;
	}

	function test_apk_uses_library_callback_4096(&$ctx, $ln) {
		if (strncmp($ln, "WQAd", 4) == 0)
			return ADDON_WQ;
		return 0;
	}

	function test_apk_uses_library_callback_8192(&$ctx, $ln) {
		if (strncmp($ln, "WiAd.jar", 8) == 0)
			return ADDON_WY_AD;
		return 0;
	}

	function test_apk_uses_library_callback_16384(&$ctx, $ln) {
		if (strncmp($ln, "WiOffer.jar", 11) == 0)
			return ADDON_WY_OFFER;
		return 0;
	}

	function test_apk_uses_library_callback_131072(&$ctx, $ln) {
		if (preg_match("/LmMob.+\.jar/", $ln))
			return ADDON_LMMOB;
		return 0;
	}

	function test_apk_uses_library_callback_262144(&$ctx, $ln) {
		if (preg_match("/tencent_mobwin.+\.jar/", $ln))
			return ADDON_TENCENT;
		return 0;
	}

	function test_apk_uses_library_callback_524288(&$ctx, $ln) {
		if (preg_match("/casee-ad.+\.jar/", $ln))
			return ADDON_CASEE;
		return 0;
	}

	function test_apk_uses_library_callback_1048576(&$ctx, $ln) {
		if (preg_match("/ftad_.+\.jar/", $ln))
			return ADDON_FTADD;
		return 0;
	}

	function test_apk_uses_library_callback_2097152(&$ctx, $ln) {
		if (preg_match("/domob_.+\.jar/", $ln))
			return ADDON_DOMOB;
		return 0;
	}

	function test_apk_uses_library_callback_8388608(&$ctx, $ln) {
		if (preg_match("/adwo_.+\.jar/", $ln))
			return ADDON_ADWO;
		return 0;
	}

	function test_apk_uses_library_callback_16777216(&$ctx, $ln) {
		if (preg_match("/Mobisage_.+\.jar/", $ln))
			return ADDON_MOBISAGE;
		return 0;
	}

	function test_apk_uses_library_callback_33554432(&$ctx, $ln) {
		if (preg_match("/SOMA_.+\.jar/", $ln))
			return ADDON_SOMA;
		return 0;
	}

	function test_apk_uses_library_callback_67108864(&$ctx, $ln) {
		if (preg_match("/l_android_adsdk.+\.jar/", $ln))
			return ADDON_LSENSE;
		return 0;
	}

	function test_apk_uses_library_callback_134217728(&$ctx, $ln) {
		if (preg_match("/AppMediaAd.+\.jar/", $ln))
			return ADDON_APPMEDIA_AD;
		return 0;
	}

	function test_apk_uses_library_callback_268435456(&$ctx, $ln) {
		if (preg_match("/AppShelf.+\.jar/", $ln))
			return ADDON_APPMEDIA_SHELF;
		return 0;
	}

	function test_apk_uses_library_callback_536870912(&$ctx, $ln) {
		if (preg_match("/aduu.+\.jar/", $ln))
			return ADDON_ADUU;
		return 0;
	}

	function test_apk_uses_library_callback_1073741824(&$ctx, $ln) {
		if (preg_match("/adtouch.+\.jar/", $ln))
			return ADDON_ADTOUCH;
		return 0;
	}

	function test_apk_uses_library_callback_4294967296(&$ctx, $ln) {
		if (strncmp($ln, "anzhi_adk", 9) == 0)
			return ADDON_ANZHI;
		return 0;
	}

	function test_apk_file_list_callback_32(&$ctx, $ln) {
		if (preg_match("/^res\/layout[^\/]*\/dianjoy.*\.xml/", $ln))
			return ADDON_DIANJOY;
		return 0;
	}

	function test_apk_file_list_callback_4096(&$ctx, $ln) {
		if (preg_match("/^res\/raw\/wqapp.*\.xml/", $ln))
			return ADDON_WQ;
		return 0;
	}

	function test_apk_file_list_callback_8192(&$ctx, $ln) {
		if (preg_match("/^res\/layout\/wy_ad_.*\.xml/", $ln))
			return ADDON_WY_AD;
		return 0;
	}

	function test_apk_file_list_callback_16384(&$ctx, $ln) {
		if (preg_match("/^res\/layout\/wy_offer.*\.xml/", $ln))
			return ADDON_WY_OFFER;
		return 0;
	}

	function test_apk_for_3rdparty($file, $mask = -1) {
		global $aapt;
		if (!is_file($file))
			return 0;
		# TODO: do NOT hardcode this path
		$ctx = array();
		$ret = 0;
		$cmd = "${aapt} d xmltree \"${file}\" AndroidManifest.xml 2>/dev/null";
		$result = shell_exec($cmd);
		if ($result) {
			$tag_begin = false;
			$tag_prefix = '';
			$lines = explode("\n", $result);
			for ($i = 0; $i < count($lines); $i++) {
				$ln = $lines[$i];
				$ret |= test_apk_for_addon_invoke($ctx, 'test_apk_manifest_callback', $ln, ((~$ret) & $mask));
				if ($tag_begin) {
					$tag_length = strlen($tag_prefix);
					$tag_regexp = '/\s{'. $tag_length. '}[A-Z]:\s(.+)/';
					if (!preg_match($tag_regexp, $ln, $matches)) {
						$tag_begin = false;
						continue;
					}
					$nv = $matches[1];
					#echo "$nv\n";
					if (preg_match('/android:name.+"([^"]+)"/', $ln, $m)) {
						$ret |= test_apk_for_addon_invoke($ctx, 'test_apk_manifest_meta_data_callback', $m[1], ((~$ret) & $mask));
					}
					if ($ret == 0 && preg_match('/android:value.+"([^"]+)"/', $ln, $m)) {
						$ret |= test_apk_for_addon_invoke($ctx, 'test_apk_manifest_meta_data_callback', $m[1], ((~$ret) & $mask));
					}
					continue;
				}
				if (!$tag_begin && preg_match('/(\s*+)E:\smeta-data/', $ln, $matches)) {
					$tag_begin = true;
					$tag_prefix = $matches[1]. '  ';
					continue;
				}
			}
		}
		# check uses-library
		$cmd = "${aapt} d badging '${file}' 2>/dev/null";
		$result = shell_exec($cmd);
		if ($result) {
			$lines = explode("\n", $result);
			foreach ($lines as $ln) {
				if (preg_match("/uses-library:'([^']*?)'/", $ln, $m)) {
					$ret |= test_apk_for_addon_invoke($ctx, 'test_apk_uses_library_callback', $m[1], ((~$ret) & $mask));
				}
				else if (preg_match("/uses-library-not-required:'([^']*?)'/", $ln, $m)) {
					$ret |= test_apk_for_addon_invoke($ctx, 'test_apk_uses_library_callback', $m[1], ((~$ret) & $mask));
				}
			}
		}
		# check files
		$cmd = "(unzip -l '${file}' | awk '{print $4}') 2>/dev/null";
		$result = shell_exec($cmd);
		if ($result) {
			$lines = explode("\n", $result);
			foreach ($lines as $ln) {
				$ret |= test_apk_for_addon_invoke($ctx, 'test_apk_file_list_callback', $ln, ((~$ret) & $mask));
			}
		}
		return $ret;
	}

	function test_apk_for_wanpu($file) {
		return (test_apk_for_3rdparty($file) & ADDON_WANPU) == ADDON_WANPU;
	}

	function test_apk_for_addon($file) {
		$msg = array(
			0 => '',
			1 => '万普',
			2 => '友盟',
			4 => '有米',
			8 => '有米积分墙',
			16 => '橘子',
			32 => '点乐',
			64 => '指点',
			128 => '芒果',
			256 => '谷歌',
			512 => '百度',
			1024 => '赢告',
			2048 => '易传媒',
			4096 => '帷千',
			8192 => '微云广告',
			16384 => '微云推广',
			32768 => '哇棒',
			65536 => '随踪',
			131072 => '力美',
			262144 => '聚赢',
			524288 => '架势无线',
			1048576 => '飞云',
			2097152 => '多盟',
			4194304 => '百度统计',
			8388608 => '安沃',
			16777216 => '艾德思奇',
			33554432 => 'Smaato',
			67108864 => 'Lsence',
			134217728 => '乐享（广告）',
			268435456 => '乐享（货架）',
			536870912 => 'ADUU',
			1073741824 => 'AdTouch',
			2147483648 => '道友道',
			4294967296 => '安智',
			8589934592 => '安智插屏',
		);
		$val = '';
		$ret = test_apk_for_3rdparty($file);
		foreach ($msg as $i => $s) {
			if (($ret & $i) != 0) {
				if (strlen($val) > 0)
					$val .= '|';
				$val .= $s;
			}
		}
		return $val;
	}

endif;

?>