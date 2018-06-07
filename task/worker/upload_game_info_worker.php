<?php

require_once(dirname(__FILE__).'/../init.php');
require_once(dirname(__FILE__).'/../../tools/functions.php');

load_helper('utiltool');

define('UPLOAD_PATH', '/data/att/m.goapk.com');
define('IMAGE_FOLDER', '/image/');
define('HOST_TAG', '<!--{ANZHI_IMAGE_HOST}-->');

//task_work
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("upload_game_info", "upload_game_info_func");
while($worker->work());
function upload_game_info_func($job) {
    $game_info_arr = json_decode($job->workload(), true);
    if(!$game_info_arr || !is_array($game_info_arr) || empty($game_info_arr)) {
		return False;
	}
    foreach ($game_info_arr as $game_info) {
        var_dump($game_info);
        $ret = process_game_info($game_info);
        if (!$ret)
            continue;
        db_insert($game_info);
    }
    
}

function process_game_info(&$game_info) {
    // 处理资讯正文
    // 先将原始值生成md5，看库里是否已经存在过，如果存在过，则过滤掉，防止重复提交
    if ($game_info['module_content']) {
        $game_info['module_md5'] = md5($game_info['module_content']);
        $option = array(
            'where' => array(
                'module_md5' => $game_info['module_md5']
            ),
            'table' => 'caiji_game_info',
        );
        $model = new GoModel();
        $find = $model->findOne($option);
        if ($find) {
            $h = date("H");
            $log_content = date("Y/m/d H:i:s") . "\t" . '正文内容重复提交，正文内容为：' . $game_info['module_content'] . "\t" . '正文md5值为：' . $game_info['module_md5'];
            permanentlog('wanke_worker_error_log_'.$h, $log_content);
            return false;
        }
    }
    
    // 处理资讯图片，根据url下载图片
    $img_url = $game_info['img_url'];
    $ret = downloadImage($img_url);
    if ($ret) {
        // 压缩图片
        $file_name = $ret['file_name'];
        $ret = image_strip_size($file_name, $file_name, 30720);
        var_dump($ret);
        $game_info['news_pic'] = str_replace(UPLOAD_PATH, '', $file_name);
    } else {
        $game_info['news_pic'] = '';
    }
    
    $img_reg = '/<img.+?src=[\'|"](.+?)[\'|"]/';
    $ret = preg_match_all($img_reg, $game_info['module_content'], $matches);
    var_dump($matches);
    
    if ($ret) {
        $img_url_arr = $matches[1];
        foreach ($img_url_arr as $img_url) {
            $ret = downloadImage($img_url);
            if (!$ret)
                continue;
            // 压缩图片
            $img_local_name = $ret['file_name'];
            $ret = image_strip_size($img_local_name, $img_local_name, 30720);
            // 数据入库时，上传路径不替换成固定的host，而是换成约定好的标签字符串，方便域名替换
            $img_local_name = str_replace(UPLOAD_PATH, HOST_TAG.'/', $img_local_name);
            $game_info['module_content'] = str_replace($img_url, $img_local_name, $game_info['module_content']);
        }
    }
    remove_external_url($game_info['module_content']);
    return true;
}

function db_insert($game_info) {
    var_dump($game_info);
    $data = array();
    if (isset($game_info['news_name'])) {
        $data['news_name'] = $game_info['news_name'];
    }
    if (isset($game_info['news_pic'])) {
        $data['news_pic'] = $game_info['news_pic'];
    }
    if (isset($game_info['news_content'])) {
        $data['news_content'] = $game_info['news_content'];
    }
    if (isset($game_info['pkg'])) {
        $data['package'] = $game_info['pkg'];
    }
    if (isset($game_info['module_content'])) {
        $data['module_content'] = htmlspecialchars($game_info['module_content']);
    }
    if (isset($game_info['module_md5'])) {
        $data['module_md5'] = $game_info['module_md5'];
    }
    if (isset($game_info['info_type'])) {
        $data['info_type'] = $game_info['info_type'];
        
    }
    if (isset($game_info['news_date'])) {
        $data['news_date'] = $game_info['news_date'];
    }
    if (isset($game_info['author'])) {
        $data['author'] = $game_info['author'];
    }
    
    $data['website_name'] = '玩客';
    $data['status'] = 1;
    $data['create_tm'] = $data['update_tm'] = time();
    $data['__user_table'] = 'caiji_game_info';
    
    
    $model = new GoModel();
    $ret = $model->insert($data);
    if (!$ret) {
        // 记录失败日志
        $h = date("H");
        $log_content = date("Y/m/d H:i:s") . "\t" . '入库失败，getLatestSql：' . $model->getLatestSql();
        permanentlog('wanke_worker_error_log_'.$h, $log_content);
    }
    return $ret;
}


// from internet
function downloadImage($url) {
    if (!$url) {
        var_dump("url is empty!");
        return false;
    }
    //服务器返回的头信息
    $responseHeaders = array();
    //原始图片名
    $originalfilename = '';
    //图片的后缀名
    $ext = '';
    $ch = curl_init($url);
    //设置curl_exec返回的值包含Http头
    curl_setopt($ch, CURLOPT_HEADER, 1);
    //设置curl_exec返回的值包含Http内容
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
    //设置抓取跳转（http 301，302）后的页面
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    //设置最多的HTTP重定向的数量
    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);

    //服务器返回的数据（包括http头信息和内容）
    $html = curl_exec($ch);
    //获取此次抓取的相关信息
    $httpinfo = curl_getinfo($ch);
    $last_url = $httpinfo['url'];
    $error_code = curl_error($ch);
    curl_close($ch);
    if ($html !== false) {
        //分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2+跳转次数 个子串
        $httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
        //倒数第二段是服务器最后一次response的http头
        $header = $httpArr[count($httpArr) - 2];
        //倒数第一段是服务器最后一次response的内容
        $body = $httpArr[count($httpArr) - 1];
        $header.="\r\n";

        //获取最后一次response的header信息
        preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
        if (!empty($matches) && count($matches) == 3 && !empty($matches[1])) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                if (array_key_exists($i, $matches[2])) {
                    $responseHeaders[$matches[1][$i]] = $matches[2][$i];
                }
            }
        }
        //获取图片后缀名
        $ret = parse_url($last_url);
        $request_path = $ret['path'];
        if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $request_path, $matches)) {
            $originalfilename = $matches[0];
            $ext = $matches[1];
        } else {
            if (array_key_exists('Content-Type', $responseHeaders)) {
                if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
                    $ext = $extmatches[1];
                }
            }
        }
        //保存文件
        if (!empty($ext)) {
            // add: 在指定目录下生成随机本地名
            $file_dir = UPLOAD_PATH . IMAGE_FOLDER . date("Ym/d/");
            var_dump($file_dir);
            if (!is_dir($file_dir)) {
                $oldumask = umask(0);
                $ret = __mkdir($file_dir, 0777);
                umask($oldumask); 
                if ($ret === false) {
                    var_dump("make dir fail");
                    return false;
                }
            }
            for ($i = 0; $i < 100; $i++) {
                $file_name = $file_dir .time().rand(1000,9999).rand(1000,9999).".{$ext}";
                if (!file_exists($file_name))
                    break;
            }
            if ($i >= 100) {
                return false;
            }
            //echo $file_name;die;
            //如果目录不存在，则先要创建目录
            //CFiles::createDirectory(dirname($file_name));
            $local_file = fopen($file_name, 'w');
            if (false !== $local_file) {
                if (false !== fwrite($local_file, $body)) {
                    fclose($local_file);
                    $sizeinfo = getimagesize($file_name);
                    return array('file_name' => realpath($file_name), 'width' => $sizeinfo[0], 'height' => $sizeinfo[1], 'orginalfilename' => $originalfilename, 'filename' => pathinfo($file_name, PATHINFO_BASENAME));
                }
            }
        }
    }
    return false;
}

// 去掉外链
function remove_external_url(&$txt) {
    //$re = '/(?:(?:https?|ftp):\/\/)?(?:(?:(?:www\.)?[a-zA-Z0-9_-]+(?:\.[a-zA-Z0-9_-]+)?\.(?:[a-zA-Z]+))|(?:(?:[0-1]?[0-9]?[0-9]|2[0-5][0-5])\.(?:[0-1]?[0-9]?[0-9]|2[0-5][0-5])\.(?:[0-1]?[0-9]?[0-9]|2[0-5][0-5])\.(?:[0-1]?[0-9]?[0-9]|2[0-5][0-5]))(?:\:\d{0,4})?)(?:\/[\w- .\/?%&=]*)?/i';
    $re = "/((http:\/\/)|(https:\/\/))([\w\d-]+\.)+[\w-]+(\/[\x{4e00}-\x{9fa5}\d\w-.\/?%&=]*)?/iu";
    $attachment_host = str_replace('/', '\/', HOST_TAG);
    $re_internal = '/'. $attachment_host .'/';
    preg_match_all($re, $txt, $matches);
    $matches = $matches[0];
    foreach($matches as $match) {
        if (!preg_match($re_internal, $match)) {
            write_access_log("removing external url: " . json_encode($match));
            $txt = str_replace($match, "javascript:void(0)", $txt);
        }
    }
}