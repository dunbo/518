<?php
include dirname(__FILE__).'/../newgomarket.goapk.com/init.php';
$apk = $_GET["apk"];
$dltime = time();
$hour = date('H');
$ip = onlineip();

if ($apk == "market"){
     $markets = $softObj -> getLatestMarkets();
     foreach ($markets as $idx => $info){
         $crawl_uri = load_config('full_static_host') . $info["apkurl"];
         $headers = get_headers($crawl_uri);
         $status = substr($headers[0], 9, 3);
         if ($status == 200){
             permanentlog('popularize_' . $hour . '.json', json_encode(array(
                        'action' => "WAP",
                         'dl_time' => $dltime,
                         'type' => 'market',
                         'apk' => substr($info["apkurl"], 1),
                         'ip' => $ip,
                         'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '',
                        )));
             toLocation(load_config('full_static_host') . $info["apkurl"]);
             exit;
             }

         }
     }else{
     $realuri = "http://m.goapk.com/" . $apk . ".apk";
     $headers = get_headers($realuri);
     $status = substr($headers[0], 9, 3);
     if ($status == 200){
         permanentlog('popularize_' . $hour . '.json', json_encode(array(
                    'action' => "WAP",
                     'dl_time' => $dltime,
                     'type' => "popularize",
                     'apk' => $apk . ".apk",
                     'ip' => $ip,
                     'referer' => $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '',
                    )));
         toLocation("http://m.goapk.com/" . $apk . ".apk");
         exit;
         }
     }
toLocation("http://m.goapk.com");
