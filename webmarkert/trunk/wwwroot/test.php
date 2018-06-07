<?php
phpinfo();
 
//edit by honking
?>

homeFeature
homenewapp
homenewgrame
HomeHotApp
HomeHotGrame 

select ss.softid softid,ss.package,ss.softname,ssf.iconurl iconurl,sc.name cname from sj_soft as ss left join sj_soft_file as ssf on ss.softid = ssf.softid left join sj_category as sc on ss.category_id = concat(',',sc.category_id,',') where ss.package in ('com.baidu.input','com.baidu.padinput','com.tencent.qq','com.tencent.mobileqq','com.tencent.mm','cn.wps.moffice_i18n_hd','cn.wps.moffice','cn.wps.moffice_eng','com.qiyi.video','net.nlnnnpn.noknlnkns','com.musicqiyi.mvideo','com.qiyi.video.pad','com.xqiyi.xvideo','com.ecebege.fekegeleeegei','com.baidu.BaiduMap','com.sina.weibotab','com.sina.weibog3','com.sina.weibo','com.baidu.searchbox','com.doujiao.nightlife.activity','com.qunar.hotel','com.Qunar','com.xiaobai.mobile.safe','com.hao.shoujimanager','com.tencent.qqpimsecure','com.anyisheng.doctoran','com.dyj.pad','com.dayingjia.stock.activity','cn.goapk.market','shaft.android','com.tencent.qqgame') and ss.status = 1 and ss.hide = 1 and ss.softname like "%ร๗ึ้%" limit 0,20 


select ss.softid softid,ss.package,ss.softname,ssf.iconurl iconurl,sc.name cname from sj_soft as ss left join sj_soft_file as ssf on ss.softid = ssf.softid left join sj_category as sc on ss.category_id = concat(',',sc.category_id,',') where ss.package in ('com.baidu.input','com.baidu.padinput','com.tencent.qq','com.tencent.mobileqq','com.tencent.mm','cn.wps.moffice_i18n_hd','cn.wps.moffice','cn.wps.moffice_eng','com.qiyi.video','net.nlnnnpn.noknlnkns','com.musicqiyi.mvideo','com.qiyi.video.pad','com.xqiyi.xvideo','com.ecebege.fekegeleeegei','com.baidu.BaiduMap','com.sina.weibotab','com.sina.weibog3','com.sina.weibo','com.baidu.searchbox','com.doujiao.nightlife.activity','com.qunar.hotel','com.Qunar','com.xiaobai.mobile.safe','com.hao.shoujimanager','com.tencent.qqpimsecure','com.anyisheng.doctoran','com.dyj.pad','com.dayingjia.stock.activity','cn.goapk.market','shaft.android','com.tencent.qqgame') and ss.status = 1 and ss.hide = 1 limit 0,20 




