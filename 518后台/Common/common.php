<?php
// 获取客户端IP地址
function get_client_ip(){
   return $_SERVER['REMOTE_ADDR'];
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr")){
            if ($suffix && strlen($str)>$length)
                return mb_substr($str, $start, $length, $charset)."...";
        else
                 return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
            if ($suffix && strlen($str)>$length)
                return iconv_substr($str,$start,$length,$charset)."...";
        else
                return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}



function toDate($time,$format='Y年m月d日 H:i:s')
{
	if( empty($time)) {
		return '';
	}
    $format = str_replace('#',':',$format);
	return date($format,$time);
}

function showTags($tags)
{
	$tags = explode(' ',$tags);
    $str = '';
    foreach($tags as $key=>$val) {
    	$tag =  trim($val);
        $str  .= ' <a href="'.__URL__.'/tag/name/'.urlencode($tag).'">'.$tag.'</a>  ';
    }
    return $str;
}
function firendlyTime($time)
{
    if(empty($time)) {
    	return '';
    }
	import('@.ORG.Date');  //日期时间操作类目录与1.5不一样
	$date	=	new Date(intval($time));
    return $date->timeDiff(time(),2);
}
function autourl($message){
      $message= preg_replace( array(
     "/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+)/i",
     "/(?<=[^\]a-z0-9\/\-_.~?=:.])([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/i"
      ), array(
      "[url]\\1\\3[/url]",
      "[email]\\0[/email]"
      ), ' '.$message);
      return $message;
     }


    function getCategoryName($id)
    {
        if(isset($_SESSION['categoryList'])) {
        	$list  = $_SESSION['categoryList'];
            return $list[$id];
        }
    	$dao = D("Category");
        $cateList  = $dao->getField("id,title");
        $_SESSION['categoryList']=$cateList;
        return $cateList[$id];
    }
    function getAbstract($content,$id)
    {
        if(false !== $pos=strpos($content,'[separator]')) {
            $content  =  substr($content,0,$pos).'  <P> <a href="'.__URL__.'/'.$id.'"><B>阅读文章全部内容… </B></a> ';
         }
         return $content;
    }


function getTitleSize($count)
{
    $size = (ceil($count/10)+11).'px';
    return $size;
}

function getBlogTitle($id)
{
	$dao = D("Blog");
    $blog   =  $dao->getById($id);
    if($blog) {
    	return $blog['title'];
    }else {
    	return '';
    }
}






function rcolor() {
    $rand = rand(0,255);
    return sprintf("%02X","$rand");
}
function rand_color()
{
	return '#'.rcolor().rcolor().rcolor();
}


function byte_format($input, $dec=0)
{
  $prefix_arr = array("B", "K", "M", "G", "T");
  $value = round($input, $dec);
  $i=0;
  while ($value>1024)
  {
     $value /= 1024;
     $i++;
  }
  $return_str = round($value, $dec).$prefix_arr[$i];
  return $return_str;
}
/**
 +----------------------------------------------------------
 * UBB 解析
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function ubb($Text) {
      $Text=trim($Text);
      //$Text=htmlspecialchars($Text);
      //$Text=ereg_replace("\n","<br>",$Text);
      $Text=preg_replace("/\\t/is","  ",$Text);
      $Text=preg_replace("/\[hr\]/is","<hr>",$Text);
      $Text=preg_replace("/\[separator\]/is","<br/>",$Text);
      $Text=preg_replace("/\[h1\](.+?)\[\/h1\]/is","<h1>\\1</h1>",$Text);
      $Text=preg_replace("/\[h2\](.+?)\[\/h2\]/is","<h2>\\1</h2>",$Text);
      $Text=preg_replace("/\[h3\](.+?)\[\/h3\]/is","<h3>\\1</h3>",$Text);
      $Text=preg_replace("/\[h4\](.+?)\[\/h4\]/is","<h4>\\1</h4>",$Text);
      $Text=preg_replace("/\[h5\](.+?)\[\/h5\]/is","<h5>\\1</h5>",$Text);
      $Text=preg_replace("/\[h6\](.+?)\[\/h6\]/is","<h6>\\1</h6>",$Text);
      $Text=preg_replace("/\[center\](.+?)\[\/center\]/is","<center>\\1</center>",$Text);
      //$Text=preg_replace("/\[url=([^\[]*)\](.+?)\[\/url\]/is","<a href=\\1 target='_blank'>\\2</a>",$Text);
      $Text=preg_replace("/\[url\](.+?)\[\/url\]/is","<a href=\"\\1\" target='_blank'>\\1</a>",$Text);
      $Text=preg_replace("/\[url=(http:\/\/.+?)\](.+?)\[\/url\]/is","<a href='\\1' target='_blank'>\\2</a>",$Text);
      $Text=preg_replace("/\[url=(.+?)\](.+?)\[\/url\]/is","<a href=\\1>\\2</a>",$Text);
      $Text=preg_replace("/\[img\](.+?)\[\/img\]/is","<img src=\\1>",$Text);
      $Text=preg_replace("/\[img\s(.+?)\](.+?)\[\/img\]/is","<img \\1 src=\\2>",$Text);
      $Text=preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/is","<font color=\\1>\\2</font>",$Text);
      $Text=preg_replace("/\[colorTxt\](.+?)\[\/colorTxt\]/eis","color_txt('\\1')",$Text);
      $Text=preg_replace("/\[style=(.+?)\](.+?)\[\/style\]/is","<div class='\\1'>\\2</div>",$Text);
      $Text=preg_replace("/\[size=(.+?)\](.+?)\[\/size\]/is","<font size=\\1>\\2</font>",$Text);
      $Text=preg_replace("/\[sup\](.+?)\[\/sup\]/is","<sup>\\1</sup>",$Text);
      $Text=preg_replace("/\[sub\](.+?)\[\/sub\]/is","<sub>\\1</sub>",$Text);
      $Text=preg_replace("/\[pre\](.+?)\[\/pre\]/is","<pre>\\1</pre>",$Text);
      $Text=preg_replace("/\[emot\](.+?)\[\/emot\]/eis","emot('\\1')",$Text);
      $Text=preg_replace("/\[email\](.+?)\[\/email\]/is","<a href='mailto:\\1'>\\1</a>",$Text);
      $Text=preg_replace("/\[i\](.+?)\[\/i\]/is","<i>\\1</i>",$Text);
      $Text=preg_replace("/\[u\](.+?)\[\/u\]/is","<u>\\1</u>",$Text);
      $Text=preg_replace("/\[b\](.+?)\[\/b\]/is","<b>\\1</b>",$Text);
      $Text=preg_replace("/\[quote\](.+?)\[\/quote\]/is","<blockquote>引用:<div style='border:1px solid silver;background:#EFFFDF;color:#393939;padding:5px' >\\1</div></blockquote>", $Text);
      $Text=preg_replace("/\[code\](.+?)\[\/code\]/eis","highlight_code('\\1')", $Text);
      $Text=preg_replace("/\[php\](.+?)\[\/php\]/eis","highlight_code('\\1')", $Text);
      $Text=preg_replace("/\[sig\](.+?)\[\/sig\]/is","<div style='text-align: left; color: darkgreen; margin-left: 5%'><br><br>--------------------------<br>\\1<br>--------------------------</div>", $Text);
      return $Text;
}

/**
 +----------------------------------------------------------
 * 代码加亮
 +----------------------------------------------------------
 * @param String  $str 要高亮显示的字符串 或者 文件名
 * @param Boolean $show 是否输出
 +----------------------------------------------------------
 * @return String
 +----------------------------------------------------------
 */
function highlight_code($str,$show=false)
{
    if(file_exists($str)) {
        $str    =   file_get_contents($str);
    }
    $str  =  stripslashes(trim($str));
    $str = str_replace(array('&lt;', '&gt;'), array('<', '>'), $str);
    $str = str_replace(array('&lt;?php', '?&gt;',  '\\'), array('phptagopen', 'phptagclose', 'backslashtmp'), $str);
    $str = '<?php //tempstart'."\n".$str.'//tempend ?>'; // <?
    $str = highlight_string($str, TRUE);
    if (abs(phpversion()) < 5)
    {
        $str = str_replace(array('<font ', '</font>'), array('<span ', '</span>'), $str);
        $str = preg_replace('#color="(.*?)"#', 'style="color: \\1"', $str);
    }
    // Remove our artificially added PHP
    $str = preg_replace("#\<code\>.+?//tempstart\<br />\</span\>#is", "<code>\n", $str);
    $str = preg_replace("#\<code\>.+?//tempstart\<br />#is", "<code>\n", $str);
    $str = preg_replace("#//tempend.+#is", "</span>\n</code>", $str);
    // Replace our markers back to PHP tags.
    $str = str_replace(array('phptagopen', 'phptagclose', 'backslashtmp'), array('&lt;?php', '?&gt;', '\\'), $str); //<?
    $line   =   explode("<br />", rtrim(ltrim($str,'<code>'),'</code>'));
    $result =   '<div class="code"><ol>';
    foreach($line as $key=>$val) {
        $result .=  '<li>'.$val.'</li>';
    }
    $result .=  '</ol></div>';
    $result = str_replace("\n", "", $result);
    if( $show!== false) {
        echo($result);
    }else {
        return $result;
    }
}

function color_txt($str)
{
    if(function_exists('iconv_strlen')) {
    	$len  = iconv_strlen($str);
    }else if(function_exists('mb_strlen')) {
    	$len = mb_strlen($str);
    }
    $colorTxt = '';
    for($i=0; $i<$len; $i++) {
               $colorTxt .=  '<span style="color:'.rand_color().'">'.msubstr($str,$i,1,'utf-8','').'</span>';
     }

    return $colorTxt;
}


/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function rand_string($len=6,$type='',$addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    if($type!=4) {
        $chars   =   str_shuffle($chars);
        $str     =   substr($chars,0,$len);
    }else{
        // 中文随机字
        for($i=0;$i<$len;$i++){
          $str.= msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1);
        }
    }
    return $str;
}

/**
 +----------------------------------------------------------
 * 获取登录验证码 默认为4位数字
 +----------------------------------------------------------
 * @param string $fmode 文件名
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function build_verify ($length=4,$mode=1) {
    return rand_string($length,$mode);
}
/**
 +----------------------------------------------------------
 * Python 读取APK文件信息
 +----------------------------------------------------------
 * @param string $fmode 文件名
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */

function get_apk_info($path) {
	if (!file_exists($path)) {
		exitOnError("{$path} not found", "/");
	}
	include_once SERVER_ROOT. '/tools/functions.php';
    //$cmd = APKINFO_CMD . " \"${path}\"";
    //$result = shell_exec($cmd);
    //$result = json_decode($result, true);
    $result = go_apk_info($path);
    if (!$result) {
        $filename = "/tmp/" . time() . ".apk";
        //copy($apkfiles['tmp_name'], $filename);
        copy($path, $filename);
        addlog('error.info', "get_apk_info failed: $cmd => $filename");
        exitOnError("get_apk_info failed: $cmd, apk is moved to tmp dir: $filename", "/");
    }
    return $result;
}

function get_sdk_plugin_jar_version_code($path) {
    if (!file_exists($path)) {
        exitOnError("{$path} not found", "/");
    }
    include_once SERVER_ROOT. '/tools/functions.php';
    $jar_support_ver = go_sdk_plugin_jar_version_code($path);
    return $jar_support_ver;
}

//填写日志
if(!function_exists('addlog')):
 function addlog($name, $txt, $print = false) {
        $txt = mb_substr($txt, 0, 2048);
        $host = array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : 'unknown';
        if (strstr($host, ":")) {
            $host = substr($host, 0, strrpos($host, ":"));
        }
        $path = P_LOG_DIR . "/" . $host . "/" . date('Y-m-d', time()) . "/";
        $client = 'user:null';
        if (isset($_SESSION)) {
            if (array_key_exists('USER_NAME', $_SESSION)) $client = json_encode($_SESSION);
        } else if ($GLOBALS["GO_ENV"] == GO_ENV_CLI) {
            $client = "daemon";
        }
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                file_put_contents("/tmp/error.info", "make dir failed.");
                return false;
            }
        }
        if ($GLOBALS['GO_ENV'] == GO_ENV_CLI or $print) echo $txt;
        $backtrace = debug_backtrace();
        $trace = "";
        foreach ($backtrace as $idx => $info) {
            $trace .= str_replace(SERVER_ROOT,'',$info['file']) . ":" . $info['line'];
            if ($idx >= 9) break;
            $trace .= " - ";
        }
        $log = $_SERVER['REMOTE_ADDR'] . " - " . date("Y-m-d H:i:s") . " - $txt --- $trace - {$client}\n";
        file_put_contents($path . $name, $log, FILE_APPEND);
    }
endif;
    //错误信息
    function exitOnError($msg, $url = "") {
        $msg = strip_tags($msg);
        $msg = addcslashes($msg, '"');
        $jump = "";
        if ($url) $jump = "window.self.location='{$url}';";
        echo "<script>alert(\"{$msg}\"); $jump </script>";
        exit;
    }

	function getoidname($id){
		C('DB_PREFIX','');
		$objcon = M('pu_operating');
		$name = $objcon -> where("oid = $id") -> getField('mname');
		return $name;
	}
	function getmidname($id){
		C('DB_PREFIX','');
		$objcon = M('pu_manufacturer ');
		$name = $objcon -> where("mid = $id") -> getField('mname');
		return $name;
	}
	function getdidname($id){
		C('DB_PREFIX','');
		$objcon = M('pu_device');
		$name = $objcon -> where("did = $id") -> getField('dname');
		return $name;
	}

     /**
	 * 切割函数
	 * Enter description here ...
	 * @param $fn 源文件路径
	 * @param $out_dir 输出文件路径
	 * @param $bs 块大小
	 * @param $dir_name_num 截取的名字长度
     * 2011/6/8 从stdafx.php复制
	 */
	function splitfile($fn, $out_dir, $bs = 524288, $dir_name_num = 2) {
	    if (!is_file($fn)) {
	        return false;
	    }
		include_once SERVER_ROOT. '/tools/functions.php';
	    $file = realpath($fn);
	    $dir = dirname($file);
	    $name = basename($fn);
	    $dir1 = substr($name,0,$dir_name_num);
	    $fs = filesize($fn);
//	    if ($fs > $bs)
//	        return 1;
	    $n = 0;
	    //$out_dir = "${out_dir}/${dir1}";
	    for ($i = 0; $i < $fs; $i += $bs) {
	        $out = sprintf("${out_dir}/${name}.%04d", $n);
//	        echo "${file} => ${out}\n";
	        if ($fs <= $bs){
	        	$cmd = "mkdir -p ${out_dir} && cp ${file} ${out}";
	        }else{
	        	$cmd = "mkdir -p ${out_dir} && dd if=\"${file}\" of=\"${out}\" bs=${bs} skip=${n} count=1 2>&1  >> /tmp/splitfile.log";
	        }
	        shell_exec($cmd);
			go_make_links($out);
	        $n += 1;
	    }
	    return $n;
	}

    /**
     +----------------------------------------------------------
     * 自定义文件规则
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    function packagename($filename)
    {
        $tmp_name = $filename['tmp_name'];
        $apkinfo= get_apk_info($tmp_name);
        $package_name = $apkinfo['packagename'];
        $GLOBALS['upload_package_name'] = $package_name;
        list($msec, $sec) = explode(' ', microtime());
        $msec = substr($msec, 2);
        $rename = $package_name. '_'. $msec. '_'. $filename['key'];
        
        return $rename;
    }
    
    function thumbname($filename)
    {
    	//截图压缩
        //shell_exec("convert -flaten {$filename['tmp_name']} {$filename['tmp_name']}.{$filename['extension']}' && mv {$filename['tmp_name']}.{$filename['extension']} {$filename['tmp_name']}");
        $package_name = $GLOBALS['upload_package_name'];
        list($msec, $sec) = explode(' ', microtime());
        $msec = substr($msec, 2);
        $rename = $package_name. '_'. $msec. '_'. $filename['key'];
        
        return $rename;
    }

    //记录日志
    function permanentlog($name, $log) {
        $host = array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : 'unknown';
        if (strstr($host, ":")) {
            $host = substr($host, 0, strrpos($host, ":"));
        }
		
        if($host == "117.79.157.114" || $host == "117.79.157.126"){
        	$host = "gomarket.goapk.com";
        }

        $host = strtolower($host);
        $path = P_LOG_DIR . "/" . $host . "/" . date("Y-m-d", time()) . "/";
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                file_put_contents("/tmp/error.info", "cannot add permanent_log");
                return false;
            }
        }
        file_put_contents($path . $name, $log . "\n", FILE_APPEND);
    }

function sendNotification($data)
{
	$service_port = NOTIFICATION_PORT;
	$address = NOTIFICATION_HOST;

	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	$result = socket_connect($socket, $address, $service_port);
	if (!$result) {
		return false;
	}
	
	$put = json_encode($data). "\n";
	
	socket_write($socket, $put, strlen($put));
	socket_close($socket);
}

function get_task_client($server = '')
{
	if($server){
		$task_server = C($server .'_task_server');//服务器
		$task_port = C($server .'_task_port');//端口
	}else{
		$task_server = C('task_server');//服务器
		$task_port = C('task_port');//端口
	}
    static $task_client = array();
	$key = "{$task_server}:{$task_port}";
    if (!$task_client[$key]) {
        $task_client[$key] = new GearmanClient();
        $task_client[$key]->addServer($task_server, $task_port);
    }
    return $task_client[$key];
}

/*
 * 加密，可逆
 * 可接受任何字符
 * 安全度非常高
 */
function encrypt($txt, $key = 'goapk')
{
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
	$ikey ="-x6g6ZWm2G9dfsaevEE_FWUKL.pOq3kRIxsZ6rm-";
	$nh1 = rand(0,64);
	$nh2 = rand(0,64);
	$nh3 = rand(0,64);
	$ch1 = $chars{$nh1};
	$ch2 = $chars{$nh2};
	$ch3 = $chars{$nh3};
	$nhnum = $nh1 + $nh2 + $nh3;
	$knum = 0;$i = 0;
	while(isset($key{$i})) $knum +=ord($key{$i++});
	$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
	$txt = base64_encode($txt);
	$txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
	$tmp = '';
	$j=0;$k = 0;
	$tlen = strlen($txt);
	$klen = strlen($mdKey);
	for ($i=0; $i<$tlen; $i++) {
		$k = $k == $klen ? 0 : $k;
		$j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
		$tmp .= $chars{$j};
	}
	$tmplen = strlen($tmp);
	$tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
	$tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
	$tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
	return $tmp;
}

/*
 * 解密
 *
 */
function decrypt($txt, $key = 'goapk')
{
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
	$ikey ="-x6g6ZWm2G9dfsaevEE_FWUKL.pOq3kRIxsZ6rm-";
	$knum = 0;$i = 0;
	$tlen = strlen($txt);
	while(isset($key{$i})) $knum +=ord($key{$i++});
	$ch1 = $txt{$knum % $tlen};
	$nh1 = strpos($chars,$ch1);
	$txt = substr_replace($txt,'',$knum % $tlen--,1);
	$ch2 = $txt{$nh1 % $tlen};
	$nh2 = strpos($chars,$ch2);
	$txt = substr_replace($txt,'',$nh1 % $tlen--,1);
	$ch3 = $txt{$nh2 % $tlen};
	$nh3 = strpos($chars,$ch3);
	$txt = substr_replace($txt,'',$nh2 % $tlen--,1);
	$nhnum = $nh1 + $nh2 + $nh3;
	$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
	$tmp = '';
	$j=0; $k = 0;
	$tlen = strlen($txt);
	$klen = strlen($mdKey);
	for ($i=0; $i<$tlen; $i++) {
		$k = $k == $klen ? 0 : $k;
		$j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
		while ($j<0) $j+=64;
		$tmp .= $chars{$j};
	}
	$tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
	return base64_decode($tmp);
}

function escape_string($string)
{
	$string = DB::getInstance()->escape_string($string);
	return $string;
}
//扩展防注入
function escape_array($array)
{
	foreach($array as $k=>$v){
		$new_array[]=escape_string($v);
	}
	return $new_array;
}
function getmsec()
{
	list($msec, $sec) = explode(' ', microtime());
	$msec = substr($msec, 2);
	return $msec;
}
//提醒词过滤
function checkword($str,$softObj){	
	if(!empty($str)){
		$configtype='soft_remind_words';
		$result = $softObj->table("pu_config")->where(array("config_type"=>$configtype,"status" => 1))->find();
		$wordarray = array();

		$wordarray = explode('|',$result['configcontent']);
	    foreach($wordarray as $v2){
			if(strstr($str,$v2)){
				$str=preg_replace("/{$v2}/i", "<font color=red><b>{$v2}</b></font>",$str);	 
			} 
		}
	}
	return $str;
}
//提醒此过滤——描述
function checkword_intro($str,$softObj){	
	if(!empty($str)){
		$configtype='soft_remind_words';
		$result = $softObj->table("pu_config")->where(array("config_type"=>$configtype,"status" => 1))->find();
		$wordarray = array();
		$wordarray = explode('|',$result['configcontent']);
	    foreach($wordarray as $v2){
			if(strstr($str,$v2)){
				$m=preg_match_all('/[\x{4e00}-\x{9fa5}]{0,2}'.$v2.'[\x{4e00}-\x{9fa5}]{0,2}/u', $str, $match); 
				foreach($match[0] as $m=>$n){
					$all_str .=preg_replace("/{$v2}/i", "<font color=red><b>{$v2}</b></font>",$n)."</br>";	 
				}
				
			} 
		}
	}
	return $all_str;
}
//盗版提醒
function getPiracyWarning($softname,$package_str,$md5_icon)
{	
	$model = new Model();	
	$ret = S('dev_piracy_soft_data');
	if(!$ret){
		//盗版提醒
		//$ret = $model->table('sj_piracy_soft p inner join sj_soft_fileicon i on p.package=i.apk_name')->where("p.status=1")->field('p.dev_id,p.softname,p.package,i.md5_icon')->select();
		$sql  =  "SELECT * FROM (
					SELECT p.dev_id, p.softname,p.package,i.md5_icon,i.softid FROM sj_piracy_soft p 
					  LEFT JOIN sj_soft_fileicon i ON p.package = i.apk_name WHERE p.status = 1 ORDER BY i.softid DESC
					) t GROUP BY t.package";
		$ret = $model->query($sql);
		//echo $model->getLastSql();
		S('dev_piracy_soft_data',$ret,10);
	}
	//echo '<pre>'; var_dump($ret);
	if (!empty($ret)){
		$return = array();	
		foreach ($ret as $k => $v)
		{
		    if (strtolower($v['package']) == strtolower($package_str) ) {
		        return false;
		    }
		    $str = '';
			if(!strstr($softname,$v['softname'])){
				continue;
			}
		    // if($softname == trim($v['softname'])){
		        // $result['softname'] = 1;
		        // $str .=  "<br>名称相同";
		    // }
			similar_text(iconv('utf-8', 'gbk', $softname), iconv('utf-8', 'gbk', $v['softname']), $per);
			//if($per >= 50){
				$per = floor($per);
				$str .=  "<br>名称相似【$per%】";
			//}


		    if($md5_icon && $md5_icon == trim($v['md5_icon'])){
		        $result['icon'] = 1;
		        $str .=  "<br/>icon相同";
		    }
		    if($str){
		        $return[$k]['softname'] = $v['softname'] .$str;
		        $return[$k]['apk_package'] = $v['package'] ;
		        $trim_package = trim($v['package']);
		        $pkg2icon = S('dev_piracy_soft_icon');
		        if (!isset($pkg2icon[$trim_package])) {
		            $icon_arr = $model->table('sj_soft_file')->where("package_status=1 and apk_name = '{$trim_package}'")->field('iconurl')->order('id desc')->find();
		            $pkg2icon[$trim_package] = $icon_arr['iconurl'];
		            S('dev_piracy_soft_icon',$pkg2icon,60);
		        }
		        $return[$k]['apk_icon'] = $pkg2icon[$trim_package];
		        
		    }		
		}		
	}

	if (empty($return)) return false;
	else   return $return;
}
//iccon相似度提醒
function get_icon_similarity($vals) {
    $pro_env = C('PRO_ENV');
    if($pro_env == 1){
        //线上(连接到附件服务器的接口)
        $conf['dummy'] = array(
            'host' => '192.168.1.55',
            'host_dam' => 'Host: dummy.goapk.com',
        );
    }else if($pro_env == 2){
        $conf['dummy'] = array(
            'host' => 'dummy.goapk.com',
            'host_dam' => 'Host: dummy.goapk.com',
        );
    }else if($pro_env == 3||$pro_env == 4){
        $conf['dummy'] = array(
            'host' => '192.168.0.99',
            'host_dam' => 'Host: 9.dummy.goapk.com',
        );
    }

	$host = $conf['dummy']['host'];
	$host_dam = $conf['dummy']['host_dam'];

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, "http://${host}/call.php?m=image&a=compare");
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host_dam));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);

	return $result;
}

//运营提醒
function getOperationWarning($softname,$dev_id ,$package_str ){	
	//静态变量
	static $ret;
	static $whitelist;
	$percent = 50;
	$model = new Model();
	if(!$ret){
		//运营提醒
		$ret_w = $model->table('sj_soft_whitelist')->where("status=1")->field('softname,package')->select();
		$package = array();
		$whitelist = array();
		foreach($ret_w as $v){
			$package[] = $v['package'];
			$whitelist[$v['package']] = $v['softname'];
		}
		//判断是否在上架状态
		$where = array();
		$where['status'] = 1;		
		$where['hide'] = 1;		
		$where['package'] = array('in',$package);		
		$ret = $model->table('sj_soft')->where($where)->field('package,softid')->select();
	}
	if (empty($ret)){
		return false;
	}else{
		$return = array();
		$softid_arr = array();
		foreach ($ret as $k => $v){	
			$softid_arr[] = $v['softid'];
		}
		$where_f = array();
		$where_f['package_status'] = 1;
		$where_f['softid'] = array('in',$softid_arr);
		$icon = $model->table('sj_soft_file')->where($where_f)->field('iconurl,softid')->select();
		$icon_arr = array();
		foreach($icon as $k => $v){
			$icon_arr[$v['softid']] = $v['iconurl'] ;
		}
		foreach ($ret as $k => $v){	
			if (strtolower($v['package']) == strtolower($package_str) ) {
				return false;
			}
			if(!strstr($softname,$whitelist[$v['package']])){
				continue;
			}
			similar_text(iconv('utf-8', 'gbk', $softname), iconv('utf-8', 'gbk', $whitelist[$v['package']]), $per);
			$per = floor($per);
			
			$return[$k]['softname'] = $whitelist[$v['package']] . " 【$per%】";
			$return[$k]['apk_package'] = $v['package'] ;
			// $icon_arr = $model->table('sj_soft_file')->where("package_status=1 and softid = '{$v['softid']}'")->field('iconurl')->find();
			// $return[$k]['apk_icon'] = $icon_arr['iconurl'];		
			$return[$k]['apk_icon'] = $icon_arr[$v['softid']];
		}
		if (empty($return)){
			return false;
		}else{
			return $return;
		}
	}
}
/*
 * 汉字转拼音函数
 * */
function Pinyin($String,$Source='UTF-8',$Type='File'){
	if ($Source == 'UTF-8') {
		$String = mb_convert_encoding($String, 'GBK', 'UTF-8');
	}

	$TablePath=empty($TablePath)?SITE_PATH.'/Common/':$TablePath;
	$TableFile=$TablePath.'GBK2PY.Table';
	$MapTable=($Type=='MEM'?file_get_contents($TableFile):$MapTable=fopen($TableFile,'rb'));
	if(!file_exists($TableFile)){
		return false;
	}
	$StringLenth=strlen($String);
	$ReturnStr='';
	for($Foo=0;$Foo<$StringLenth;$Foo++){
		$Char=ord(substr($String,$Foo,1));
		if($Char>127){
			$Str=substr($String,$Foo,2);
			$High=ord($Str[0])-129;
			$Low=ord($Str[1])-64;
			$Addr=($High<<8)+$Low-($High*64);
			if($Addr<0){
				$ReturnStr.='_';
			}else{
				$MapAddr=$Addr*8;
				if($Type=='MEM'){
					$MapStr='';
					for($Tmp=0;$Tmp<8;$Tmp++){
						$MapStr.=$MapTable[($MapAddr+$Tmp)];
					}
					$BinStr=unpack('a8py',$MapStr);
				}else{
					fseek($MapTable,$MapAddr,SEEK_SET);
					$BinStr=unpack('a8py',fread($MapTable,8));
				}
				$Foo++;
				$BinStr = preg_replace('/\d$/', '', $BinStr);
				$ReturnStr.=$BinStr['py'];
			}
		}else{
			$ReturnStr.=$String[$Foo];
		}
	}
	$Type=='MEM'?null:fclose($MapTable);
	return $ReturnStr;
}

function update_data_log($softid,$type,$soft_obj) {
	$admin_data_log_sql = "SELECT  ss.softid appid,ss.softname title,ss.dev_name developer,ss.package package,ss.category_id category,ss.version version,ss.version_code versioncode,ssf.min_firmware minsdkversion,ss.upload_tm releasedate,ss.intro description,ss.score hotlevel,ss.tags keyword,ssf.url apkurl,ss.total_downloaded downloadnumber,ssf.filesize packagesize,ssf.iconurl smallmaplink FROM sj_soft AS ss LEFT JOIN sj_soft_file AS ssf on ss.softid = ssf.softid  where ss.softid = ";
	$soft_db = $soft_obj;
	$sql = $admin_data_log_sql . $softid;
	$result = $soft_db->query($sql);
	$soft_modify = $result[0];
	$date = time();
	if($soft_modify){
		$soft_modify['type'] = $type;
		$soft_modify['date'] = $date;
		$h = date("H", $date);
		permanentlog("data_modify_{$h}.log",json_encode($soft_modify));
	}
	//file_put_contents("{$dir}/data_modify.log", json_encode($soft_modify) . "\n", FILE_APPEND);
}

function showDatePicker($start_name, $end_name, $start_value, $end_value, $tpl = 1)
{
$html1 = "<span><input type='text' name='{$start_name}' id='{$start_name}' value='{$start_value}' style='cursor: hand; width: 150px;' class='Wdate' onClick='WdatePicker({startDate:\"%y-%M-%d 00:00:00\",dateFmt:\"yyyy-MM-dd HH:mm:ss\"})' /></span>~
<span><input type='text' name='{$end_name}' id='{$end_name}' value='{$end_value}' style='cursor: hand; width: 150px;' class='Wdate' onClick='WdatePicker({startDate:\"%y-%M-%d 23:59:59\",dateFmt:\"yyyy-MM-dd HH:mm:ss\"})' /></span>
";
$html2 = '';
	$var = 'html'. $tpl;
	echo $$var;
}

function SendMail($address,$title,$message)
{
    vendor('PHPMailer.class#phpmailer');

    $mail=new PHPMailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();

    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet='UTF-8';

    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);

    // 设置邮件正文
    $mail->Body=$message;

    // 设置邮件头的From字段。
    $mail->From=C('MAIL_ADDRESS');

    // 设置发件人名字
    $mail->FromName='admin';

    // 设置邮件标题
    $mail->Subject=$title;

    // 设置SMTP服务器。
    $mail->Host=C('MAIL_SMTP');

    // 设置为"需要验证"
    $mail->SMTPAuth=true;
    $mail->ishtml(true);
    // 设置用户名和密码。
    $mail->Username=C('MAIL_LOGINNAME');
    $mail->Password=C('MAIL_PASSWORD');

    // 发送邮件。
    return($mail->Send());
}
//山寨提醒
function get_copycat_num($name,$md5_icon,$package_str){
	static $note_arr;
	$model = new Model();	
	$name = trim($name);
	$md5_icon = trim($md5_icon);
	if (!$note_arr) {
		$note_w =  array();
		$note_w['shield'] = 1;
		$note_w['shield_start'] = array("elt",time());
		$note_w['shield_end'] = array("egt",time());
		$notelist = $model->table('sj_soft_note')->where($note_w)->field('package')->select();
		foreach($notelist as $val){
			$note_arr[$val['package']] = 1;
		}
	}
	$where=array(       
		'softname' => $name,
		'status' =>1,
		'hide'=>1,
		'channel_id' =>''
	);
	$softlist = $model->table('sj_soft')->where($where)->field('softid,package')->select();
	$fileicon = $model->table('sj_soft_fileicon')->where("md5_icon='{$md5_icon}' and package_status =1")->field('softid')->select();
	$fileicon_arr = array();
	foreach($fileicon as $key=>$val){
		$fileicon_arr[$val['softid']] = 1;
	}
	$softid = array();
	$count=0;
	foreach($softlist as $k=>$v){
		if($v['package'] == $package_str || isset($note_arr[$v['package']])){
			continue;
		}
		if(isset($fileicon_arr[$v['softid']])){
			$softid[] = $v['softid'];
			$count++;
		}else{
			continue;
		}
	}
    $where = array('softname'=>$name,'record_type'=>array('exp',' <= 3'),'status'=>2);
	$soft_tmp = $model->table('sj_soft_tmp')->where($where)->field('id,package')->select();
	$fileicon_tmp = $model->table('sj_soft_fileicon_tmp')->where("md5_icon='{$md5_icon}' and package_status=1")->field('tmp_id')->select();
	$fileicon_tmp_arr = array();
	foreach($fileicon_tmp as $key =>$val){
		$fileicon_tmp_arr[$val['tmp_id']] = $val['tmp_id'];
	}
	$tmp_id = array();
	$count_tmp = 0;
	foreach($soft_tmp as $v){
		if($v['package'] == $package_str || isset($note_arr[$v['package']])){
			continue;
		}
		if(isset($fileicon_tmp_arr[$v['id']])){
			$tmp_id[] = $v['id'];
			$count_tmp++;
		}else{
			continue;	
		}
	}
	$counts = $count+$count_tmp;
	return array($counts,$softid,$tmp_id);
}

function get_table_data($where,$table,$key,$field = '*',$order = ''){
	$model = new Model();	
	if($order){
		$list = $model -> table($table)->where($where)->field($field)->order($order)->select();
	}else{
		$list = $model -> table($table)->where($where)->field($field)->select();
	}
	$return = array();
	foreach((array)$list as $k => $v){
		$return[$v[$key]] = $v;
	}
	return $return;
}


function setCookieAdmin(){
    $admin_id = $_SESSION['admin']['admin_id'];
    $loginip = $_SESSION['admin']['loginip'];
    import("@.ORG.3des");
    $rep = new Crypt3Des();
    $secret_map = C('secret_map');
    $salt = array_rand($secret_map);
    $rep->key = $secret_map[$salt];
    $arr = array(
        $admin_id, 
        $loginip, 
        time(),
        md5($_SERVER['HTTP_USER_AGENT'])
    );
    $str = implode(',', $arr);
    $secret = $rep->encrypt($str);
//    setcookie("AZEUI",$secret. '|'. $salt ,7200, NULL,NULL,NULL,TRUE);
    cookie('AZEUI', $secret. '|'. $salt, 10800);
}

function checkCookieAdmin(){
    if(!empty($_COOKIE['AZEUI'])){
        list($secret, $salt) = explode('|', $_COOKIE['AZEUI']);
        import("@.ORG.3des");
        $rep = new Crypt3Des();
        $secret_map = C('secret_map');
        $rep->key = $secret_map[$salt];
        $str = $rep->decrypt($secret);
        if ($str) {
            list($admin_id, $loginip, $logintime, $agent) = explode(',', $str);
            $loginip = trim($loginip);
            if ($loginip != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            
            if ($agent != md5($_SERVER['HTTP_USER_AGENT'])) {
                return false;
            }
            
            if (time() - $logintime > 10800) {
                return false;
            }
            
            $adminusers = M('admin_users');
            $admin = $adminusers->where(array('admin_user_id'=>$admin_id))->field('admin_user_name,admin_group,sessionid')->find();
            
            $allow_multi_session = C('allow_multi_session');
            if(!$allow_multi_session && $admin['sessionid']!=session_id()) {
                return false;
            }
            
            $_SESSION['admin'] = $admin;
            $_SESSION['admin']['admin_id'] = $admin_id;
            $_SESSION['admin']['loginip'] = $loginip;
            $_SESSION['admin']['ua']=$_SERVER['HTTP_USER_AGENT'];

            $role=M('');
            //TODO 特殊权限，用户权限都保存在这个表
            $viplevellist=$role->table('sj_admin_role A,sj_admin_node B')->where('A.admin_id="'.$admin_id.'" AND A.node_id=B.node_id')->field('B.nodename')->select();
            $theviplevelarray = array();
            foreach($viplevellist as $key=>$value)
            {
                $theviplevelarray[$key]=$value['nodename'];
            }
        
            $_SESSION['admin']['popedom'] = $theviplevelarray;
            return true;
        }
    }
    return false;
}

//取出cookie中的帐号信息
function getCookieAdmin(){          
    if(!defined('INC')){            
        return false;
    }

    $session = decrypt($_COOKIE['info']); 
    $arr = explode(',', $session);
    return $arr;  
}

function microtime_float()
{
    /*
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
    */
    return gettimeofday(true);
}

//生成随机码
function rand_code_md5() {
    return md5(rand(1, 100000) . microtime());
}

function _http_post_email($vals) {
    if (preg_match('/^192\.168\.0/i', $_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] == '10.0.2.15' || $_SERVER['SERVER_ADDR'] == '114.247.222.131') {
        //$url = 'http://192.168.0.74:92/service.php';
        //$host = 'Host: localhost';
        //$url = 'http://118.26.203.22/service.php';42.62.4.183
        $url = 'http://124.243.198.93/service.php';
        $host = 'Host: mail.goapk.com';
    } else {
        $url = 'http://192.168.1.143/service.php';
        $host = 'Host: mail.goapk.com';
    }

    $url .= '?key=f3778b2d59c276233de4f73b2ebf46ea';

    $res = curl_init();
    curl_setopt($res, CURLOPT_URL, $url);
    curl_setopt($res, CURLOPT_TIMEOUT, 5);
    curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
    curl_setopt($res, CURLOPT_POST, true);
    curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
    $result = curl_exec($res);
    $http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
    curl_close($res);

    $log_file = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'e:/email.log' : LOG . date('Y-m-d') . '/email.log';
    if (!is_dir(dirname($log_file)))
        mkdir(dirname($log_file), 0777, true);
    file_put_contents($log_file, "post|{$url}|{$host}|{$vals['email']}|{$vals['subject']}|{$vals['content']}|{$http_code}|" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

    return array(
        'ret' => $result,
        'http_code' => $http_code,
    );
}
//更新sj_soft_status 表状态
//$data = array('field'=>$value,'field2'=>$value2);
function update_soft_status($data,$pkg){
	$model = new Model();
	if($data){
		foreach($data as $key=>$v){
			$map[$key] = $v;
		}
		$where = array(
		    'package' =>$pkg
		);	
		$res = $model->table('sj_soft_status')->where($where)->save($map);
		if(!$res){
			permanentlog("soft_status_error.log",date('Y-m-d H:i:s')."---".$model->getlastsql());
			if($map['soft_status'] && $map['soft_status']  > 0){
			    $res = getSoftStatusByPackage($pkg);
			}		    
		}else{
			permanentlog("soft_status_success.log",date('Y-m-d H:i:s')."---".$model->getlastsql());
		}
		return $res;
	}else{
	    return false;
	}

}
function getSoftStatusByPackage($package){
    $model = new Model();
    $data = array();
    $soft_status = 0 ;
    $time = 0;
    $where = array(
            'status' => array('exp','in (2,3)'),
            'package' => $package,
            'dev_id' => array('exp','>0')
    );
    $res_tmp = $model->table('sj_soft_tmp')->where($where)->field('dev_id,record_type,sdk_status,status,softname,softid,pass_time,pass_status,review_time,last_refresh')->find();    
    if($res_tmp){
        $record_type = $res_tmp['record_type'];
        $status = $res_tmp['status'];
        $sdk_status = $res_tmp['sdk_status'];
        if($record_type == 5){
            $soft_status = 71;
        }elseif($record_type == 6){
            $soft_status = 72;
        }elseif($record_type == 7){
            $soft_status = 73;
        }elseif($status == 2 && !empty($res_tmp['pass_time']) && $res_tmp['pass_status'] == 1){
            $soft_status = 40;
        }elseif($status == 2 && $sdk_status == 2){
            $soft_status = 22;
        }elseif($status == 3 && $sdk_status == 2){
            $soft_status = 21;
        }elseif($status == 2 && $record_type == 1){
            $soft_status = 31;
        }elseif($status == 2 && $record_type == 2){
            $soft_status = 32;
        }elseif($status == 2 && $record_type == 3){
            $soft_status = 33;
        }elseif($status == 2 && $record_type == 4){
            $soft_status = 34;
        }elseif($status == 3 && $record_type == 1){
            $soft_status = 11;
        }elseif($status == 3 && $record_type == 2){
            $soft_status = 12;
        }elseif($status == 3 && $record_type == 3){
            $soft_status = 13;
        }elseif($status == 3 && $record_type == 4){
            $soft_status = 14;
        }
        $data['package'] = $package;
        $data['dev_id'] = $res_tmp['dev_id'];
        $data['softid'] = $res_tmp['softid'];
        $data['softname'] = mysql_real_escape_string($res_tmp['softname']);
        $data['soft_status'] = $soft_status;
        $data['update_tm'] = $res_tmp['last_refresh'];
        $data['create_tm'] = $res_tmp['last_refresh'];
        
    }else{
        
	    $where = array(
	            'hide' => array('exp','in (1,3)'),
	            'status'=> 1,
	            'package' => $package,
	            'dev_id' => array('exp','>0')
	    );
        $res = $model->table('sj_soft')->where($where)->order('softid  desc')->field('dev_id,package,version,hide,softname,softid,review_time,last_refresh')->select();
        
		foreach($res as $v){
            $time = $v['last_refresh'];
            $data['package'] = $v['package'];
            $data['dev_id'] = $v['dev_id'];
            $data['softid'] = $v['softid'];            
            $data['version'] = $v['version'];
            $data['softname'] = mysql_real_escape_string($v['softname']);
            $data['update_tm'] = $time;
            $data['create_tm'] = $time;
            if($v['hide'] == 1){
                $soft_status = 50; break;
            }elseif($v['hide'] == 3){
                unset($data['version']);
                $soft_status = 60;
            }
        }
        $data['soft_status'] = $soft_status;
    }
    
    if($soft_status != 0){   
        $soft_status_db = M('soft_status');
        $res = $soft_status_db->add($data);
        if(!$res){
        	foreach($data as $key=>$v){
				$map[$key] = $v;
			}
			$where = array(
				'package' =>$package
			);	
			$map['del'] = 0;
			$res = $model->table('sj_soft_status')->where($where)->save($map);
        }
    }

}
function httpGetInfo($url, $vals,$log_name) {
        $res = curl_init();
        curl_setopt($res, CURLOPT_URL, $url);
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
        $result = curl_exec($res);
        $http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);
		if($log_name){
			$log = "{$http_code}|{$errno}|{$error}\n" . print_r($vals, true) . "\n" . print_r($result, true) . "\n\n";
			permanentlog($log_name, $log);
		}else{
			if ($http_code != 200)
				file_put_contents('user_statistics.log', "{$http_code}|{$errno}|{$error}\n" . print_r($vals, true) . "\n" . print_r($result, true) . "\n\n", '_http_post');
		}
        return $result;
}

function packZipDown($zipname,$filelist){  
	header('X-Accel-Chareset: utf-8');
	Header("X-Archive-Files: zip");
	Header("Content-type: application/octet-stream");
	Header("Content-Disposition: attachment; filename={$zipname}.zip");
	if(is_array($filelist) && count($filelist)){
		foreach ($filelist as $path){
			$size = filesize($path);
			$stack = explode('/',$path);
			$fruit = array_pop($stack);
			printf("%s %d %s  %s\n",'-',$size,str_replace(UPLOAD_PATH,'/apkfile',$path),$fruit); 
		}                  
	}
}

function getDevidBySign($sign){
    $where = array(
            's.hide' => 1,
            's.status'=> 1,
            'f.sign'=> $sign,
            
    );
    $res = $model->table('sj_soft_file f')->join("sj_soft s on s.softid = f.softid")->where($where)->field('s.dev_id,s.softid')->select();
    $devids = array();
    if($res){
    	foreach($res as $row){
    		$devids[$row['dev_id']] += 1;
    	}
    }
    return $devids;
	
}



function request_task($api,$data_array,$extra=array(),$need_crypt=true,$need_info=true,$method='POST'){
	
	import('@.ORG.GoDes');  //GoDes
	if(empty($data_array['data'])||empty($api)||!is_array($data_array['data'])){
		return false;
	}
	$start = microtime_float();
	$app = defined('APP_NAME') ? 'www' : 'gomarket';
	$ucenter = C('ucenter');
	
	if(empty($ucenter)){
		return false;
	}
	$day = date('Y-m-d');
	file_put_contents("/tmp/ucenter_task_request_{$day}.log", time()."\t".session_id()."\t".json_encode($data_array)."\n", FILE_APPEND);
    $api_version = isset($extra['version'])&&!empty($extra['version']) ? $extra['version'] : $ucenter[$api['prefix'].'_version'] ;
	$url = $ucenter[$api['prefix'].'_uri'].$api_version.$api['apiname'];
	$des = new GoDes($ucenter['task_privatekey']);
	$temp_data = $des->encrypt(json_encode($data_array['data']));
	$data = base64_encode($temp_data);
	$request_data = array('data'=>$data);
	$real_serviceId = $api['passthrough'] === true ? $ucenter['client_serviceId'] : ($api['passthrough'] === false ? $ucenter['serviceId'] : 0);
	if(empty($real_serviceId)){
		return false;
	}
	if($api['prefix']=='task'){
		$request_data['clientInfo'] = array('serviceId'=>$real_serviceId);
	}
	$request_data['serviceId'] = $ucenter['serviceId'];
	$request_data['serviceVersion'] = $ucenter['serviceVersion'];
	$request_data['serviceType'] = 0;	//	0 移动端， 1 Web端
	if(isset($api['sid'])&&!empty($api['sid'])){
		$request_data['sid'] = $api['sid'];
	}
	if(!empty($data_array['device'])){
		$request_data['device'] = $data_array['device'];
	}
    $request_header = isset($extra['header']['appchannel']) && !empty($extra['header']['appchannel']) ? array('appchannel'=>$extra['header']['appchannel']) : (isset($ucenter['appchannel']) && !empty($ucenter['appchannel']) ? array('appchannel'=>$ucenter['appchannel']) : array());
	if(!empty($data_array['header'])){
		$request_header = array_merge($request_header,$data_array['header']);
	}
	$request_data['header'] = $request_header;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	$code = $info['http_code'];
	$msg = date('Y-m-d H:i:s');
	if(is_array($request_data)){	//	为记日志处理
		$request_data = json_encode($request_data);
	}
	if ($code == 200) {
		if (empty($result)) {
			$new_msg = $msg.$request_data." return empty \n";
			file_put_contents("/tmp/ucenter_task_error_{$day}.log", $new_msg, FILE_APPEND);
		}
		$temp_data = json_decode($result, true);
		if($temp_data['code']==200){
			$respone_data = base64_decode($temp_data['data']);
			$decrypt_privatekey = $api['passthrough'] === true ? $ucenter['task_client_privatekey'] : $ucenter['task_privatekey'];
			$des = new GoDes($decrypt_privatekey);
			$ret = json_decode($des->decrypt($respone_data),true);
		}
		else{
			$ret = $temp_data;
		}
		$end = microtime_float();
		$s = $end - $start;
		if ($s > 0.5) {	//	此常量待定
			$rheader = explode("\r\n", $rheader);
			$ss = end($rheader);
			$new_msg = $msg.$request_data." spend {$s} {$ss}\n";
			file_put_contents("/tmp/ucenter_task_slow_{$day}.log", $new_msg, FILE_APPEND);
		}
		return $ret;
	}
	else {
		$msg .= $request_data." service abnormal \n";
		file_put_contents("/tmp/ucenter_task_error_{$day}.log", $msg, FILE_APPEND);
		return false;
	}
	return false;
}

function getImgId($key,$id){
	$where = array(	'from_id' => $id);
	$img_list  = get_table_data($where,"sj_graphic_image","imgurl","id,imgurl");
	return "[img]".$img_list[$key]['id']."[/img]";   
	
} 

    
//修改回调地址接口
function updata_app_message($data) {

	import('@.ORG.GoDes');
	$key = 'eeUu5p6XElQbYGM26iCIOmo2';
	$appkey = '142605894293bjc9VR9P3Xqv7jFTgh';
	$des = new GoDes($key);
	$data_des = $des->encrypt(json_encode($data));
	$data_des = base64_encode($data_des);
	$request_data = array('data'=>$data_des,'appKey'=>$appkey);

	$res = curl_init();
    $pro_env = C('PRO_ENV');
    if($pro_env == 1){
        //线上
        $host = 'http://192.168.3.136:8089/php/dev/app_info_notify';
    }else if($pro_env == 2){
        //518test
        $host =  'http://192.168.3.91:8089/php/dev/app_info_notify';
    }else if($pro_env == 3||$pro_env == 4){
        //99 或本地
        $host = 'http://192.168.0.114:8080/mplatform/php/dev/app_info_notify';
    }
	curl_setopt($res, CURLOPT_URL, $host);
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $request_data);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);
	$dir = P_LOG_DIR . "/admin.goapk.com/" . date("Y-m-d", $date);
	if (!is_dir($dir))
		mkdir($dir, 0755);
	$date = date('Y-m-d H:i:s',time());
	file_put_contents($dir .'/updata_app_message.log', "{$http_code}|{$errno}|{$error}|{$host}". "\n" . "{$date}\n" . print_r($data, true) . "\n" . print_r($request_data, true) . "\n" . print_r($result, true) . "\n\n", FILE_APPEND);
	return $result;
}

function set_redis_comment($uid,$imei,$pid,$comment_id,$mac){
    $redis = new Redis();
    $redis->connect(C('COMMENT_REDIS_HOST'),C('COMMENT_REDIS_PORT'));
    $redis->incr("message_v6:imei:{$imei}");

    $user_key = 'message_v6:notify:imei:'.$imei;
    $redis->hset($user_key, $pid, $comment_id);

    $data = array(
      'object_id' => $pid,
      'object_type' => 'post',
      'msg' => $comment_id,
      'imei' => $imei,
      'mac' => $mac,
      'status' => 1,
      'create_at' => time()
    );
    if($uid && $uid != 13176){
        $redis->incr("message_v6:uid:{$uid}");
        $user_key = 'message_v6:notify:uid:'.$uid;
        $redis->hset($user_key, $pid, $comment_id);
        $data['userid'] = $uid;
    }
    $model = new Model();
    $model->table('sj_notify')->add($data);
}

function update_testin_data($data){
    
    $url = 'http://anzhiapi.testin.cn/settings/';
    //$url = 'http://anzhiapi.testin.cn/mission2/settings/';
    if(empty($data['type'])||empty($data['id'])){
    	return false;
    }
    if($data['type'] == 'del'){
        $vals['ID'] = $data['id'];
        $vals['TYPE'] = 'DEL';        
    }elseif($data['type'] == 'set'){
        $vals['ID'] = $data['id'].'_channel';        
        $vals['TYPE'] = 'SET';
        $vals['order'] = $data['rank'];
    }else{
        return false;
    }
    
    return httpGetInfo($url, $vals,'testin_update.log');    
	
}

//取得推送调用
function push_soft_update_msg($data){
    $push = D("Sj.Push");
    $model = new Model();
    $package = $data['package'];
    $result = $model->table("sj_update_push")->where(array("soft_package"=>$package,"status" => 1))->find();
    permanentlog("test.log",date("Y-m-d H:i:s").print_r($model->getLastSql(),true).'\n\n'.var_export($result,true));
    if($result){
        $vals['package_name'] = $data['package'];
        $vals['app_name'] = $data['softname'];
        $vals['sid'] = (int)$data['softid'];
        $vals['version_name'] = $data['version'];
        $vals['version_code'] = $data['version_code'];
        $vals['size'] = $data['filesize'];
        $vals['icon_url'] = $data['iconurl'];
        $vals['item_update_time'] = $data['review_time'];
        $vals['update_content'] = $data['update_content'];
        $vals['start_time'] = $data['start_time'];
        $vals['end_time'] = $data['end_time'];
        permanentlog("test.log",date("Y-m-d H:i:s").print_r($vals,true));
        $push->addPckPush($vals);           
    }	
}

/**
 * +----------------------------------------------------------
 * 新版获取appkey
 * date 2015-10-13 
 * +----------------------------------------------------------
 */
function getAppInfoNew($data){
	//var_dump($data);
		import('@.ORG.GoDes');
		$app = C('app_key');
		$serviceId = $app['serviceId'];
		$key = $app['key'];
		$url = $app['host'].$app['create'];
		$post_data = array(
			'pid' => $data['dev_id'], //开发者id
			'appName' => $data['softname'], //软件名称
			'contractAppName' => $data['softname'], //合同中应用名称
			'description' =>$data['jianjie'], //简介
			'appStatus'=>1, //应用状态（有效）
			'payStatus'=>0, //支付状态（正常）
			'packageName'=> $data['package'], //包名
			'isJoinUcenter'=>1, //是否接入用户体系
			'category' => $data['p_leixing']
		);
		if($data['p_fenlei']=='网游'||$data['p_fenlei']=='棋牌'){
			if($data['p_fenlei']=='网游'||$data['p_fenlei']=='棋牌'){
				$post_data['gameType'] = 1; //网游
			}
			$post_data['appType'] = 2; //用户中心版
		}else{
			$post_data['gameType'] = 0; //单机
			$post_data['appType'] = 1; //支付SDK
			$post_data['isJoinUcenter'] = 0; //单机默认不接入账号
		}
		if(isset($data['is_accept_account'])) $post_data['isJoinUcenter'] = $data['is_accept_account'];
		permanentlog("create_apppkey.log",date("Y-m-d H:i:s")."{$url}\n".print_r($post_data,true));
		
		$des = new GoDes($key);
		$data_des = $des->encrypt(json_encode($post_data));
		$data_des = base64_encode($data_des);
		//var_dump($data_des);
		$request_data = array('data'=>$data_des,'serviceId'=>$serviceId);
		//var_dump($request_data);
		$o = "";
		foreach ( $request_data as $k => $v ) 
		{ 
			$o.= "$k=" . urlencode( $v ). "&" ;
		}
		$post_data=substr($o,0,-1);
		$result = httpGetInfo($url,$post_data,"create_apppkey.log");
		
        return $result;
}

/**
 * +----------------------------------------------------------
 * 新版修改appkey
 * date 2015-10-13 
 * +----------------------------------------------------------
 */
function modifyAppNew($data) {
	import('@.ORG.GoDes');
	$app = C('app_key');
	$serviceId = $app['serviceId'];
	$key = $app['key'];
	$url = $app['host'].$app['modify'];
	$post_data = array(
		'appKey' => $data['appKey']
	);
	if(isset($data['appSecret'])) $post_data['appSecret'] = $data['appSecret'];
	if(isset($data['dev_id'])) $post_data['pid'] = $data['dev_id'];
	if(isset($data['softname'])){ 
		$post_data['appName'] = trim($data['softname']);
	} 
	if(isset($data['contractAppName'])) $post_data['contractAppName'] = $data['contractAppName'];
	if(isset($data['status'])) $post_data['appStatus'] = $data['status'];
	if(isset($data['pay_url'])) $post_data['payCallback'] = htmlspecialchars_decode($data['pay_url']);
	if(isset($data['usernotice_url'])) $post_data['notifyUrl'] = htmlspecialchars_decode($data['usernotice_url']);
	if(isset($data['packageName'])) $post_data['packageName'] = $data['packageName'];
	if(isset($data['p_fenlei'])){
		if($data['p_fenlei']=='网游'||$data['p_fenlei']=='棋牌'){
			$post_data['gameType'] = 1; //网游，棋牌也按网游传参
			$post_data['appType'] = 2; //用户中心版
		}else{
			$post_data['gameType'] = 0; //单机
			$post_data['appType'] = 1; //支付SDK
		}		
	}
	if(isset($data['p_leixing'])) $post_data['category'] = $data['p_leixing'];
	if(isset($data['isOnline'])) $post_data['isOnline'] = $data['isOnline'];	
	if(isset($data['onlineTimeStr'])) $post_data['onlineTimeStr'] = $data['onlineTimeStr'];
	if(isset($data['isJoinUcenter'])) $post_data['isJoinUcenter'] = $data['isJoinUcenter'];
	if(isset($data['appType'])) $post_data['appType'] = $data['appType'];
	permanentlog("modifyapp.log",date("Y-m-d H:i:s").$url."\n".print_r($post_data,true));
	//var_dump($post_data);
	$des = new GoDes($key);
	$data_des = $des->encrypt(json_encode($post_data));
	$data_des = base64_encode($data_des);
	//var_dump($data_des);
	$request_data = array('data'=>$data_des,'serviceId'=>$serviceId);
	//var_dump($request_data);
	$o = "";
	foreach ( $request_data as $k => $v ) 
	{ 
		$o.= "$k=" . urlencode( $v ). "&" ;
	}
	$post_data=substr($o,0,-1);
	//var_dump($post_data);
	$result = httpGetInfo($url, $post_data,'modifyapp.log');
	//var_dump($result);
	return $result;
}

//更改app开发者id
function modifyAppPid($data){
	import('@.ORG.GoDes');
	$app = C('app_key');
	$serviceId = $app['serviceId'];
	$key = $app['key'];
	$url = $app['host'].$app['modify_pid'];
	$post_data = array(
		'appKey' => $data['appKey'],
		'pid' => $data['pid']
	);
	
	permanentlog("modifyapp.log",date("Y-m-d H:i:s").$url."\n".print_r($post_data,true));
	$des = new GoDes($key);
	$data_des = $des->encrypt(json_encode($post_data));
	$data_des = base64_encode($data_des);
	$request_data = array('data'=>$data_des,'serviceId'=>$serviceId);
	$o = "";
	foreach ( $request_data as $k => $v ) 
	{ 
		$o.= "$k=" . urlencode( $v ). "&" ;
	}
	$post_data=substr($o,0,-1);
	$result = httpGetInfo($url, $post_data,'modifyapp.log');
	return $result;
}

//判断游戏是否是联运游戏
function isSdkGame($package){
	$model = new Model();
	$game = $model->table('sj_soft_whitelist')->where("status = 1 and package = '{$package}'")->field('id,is_accept_account,fen_lei')->find();
	if($game){
		return $game;
	}else{
		return false;
	}
}

//获取联运游戏appkey
function getAppKey($package,$need_other=''){
	$model = new Model();
	if($need_other==1){
		$filed = 'app_id,app_secret,pay_url,usernotice_url';
	}else{
		$field = 'app_id';
	}
    $appkey = $model->table('sj_sdk_info')->where("package = '{$package}'")->order('id desc')->field($field)->find();
	if($need_other==1){
		return $appkey;
	}else{
		return $appkey['app_id'];
	}	
}

//获取联运软件合同名称
function getAppContractName($package){
	$model = new Model();
	$contractname = $model->table('yx_contract')->where("package = '{$package}'")->order('id desc')->field('softname,contract_name')->find();
	return $contractname;
}
//反序列化函数
function mb_unserialize($res) {
    $res = str_replace("\r", "", $res);
    $res = preg_replace_callback('!s:\d+:"(.*?)";!s', 'serstr', $res );
    return unserialize($res);
}

function serstr($m){
    return 's:'.strlen($m[1]).':"'.$m[1].'";';
}

function mb_unserialize1($res,$to = 'utf-8',$from = 'gbk') {
    $res = mb_convert_encoding($res,$from,$to);//转成原始编码
    $res = str_replace("\r", "", $res);
    $res = unserialize($res);
    $res = dmb_convert_encoding($res,$to,$from);
    return $res;
}

function dmb_convert_encoding($string,$to = 'utf-8',$from = 'gbk') {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = dmb_convert_encoding($val, $to,$from);
        }
    } else {
        $string = mb_convert_encoding($string,$to,$from);
    }
    return $string;
}

//图片打水印
function watermark($dst_path,$src_path,$where = 'br',$save_path){
    //创建图片
    $dst = imagecreatefromstring(file_get_contents($dst_path));
    $src = imagecreatefromstring(file_get_contents($src_path));
    //获取水印图片的宽高
    list($src_w, $src_h) = getimagesize($src_path);
    list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path);
    
    if($dst_w - 20 <= $src_w || $dst_h - 20 <= $src_h)return false;
    if(!$save_path)$save_path = $dst_path;
    switch($where){
        case 'tc': //顶部居中
            $dst_x = ($dst_w - $src_w)/2;
            $dst_y = 10;
            break;
        case 'tl': //顶部居左
            $dst_x = 10;
            $dst_y = 10;
            break;
        case 'tr': //顶部居右
            $dst_x = $dst_w - $src_w - 10;
            $dst_y = 10;
            break;
        case 'cc': //居中
            $dst_x = ($dst_w - $src_w)/2;
            $dst_y = ($dst_h - $src_h)/2;
            break;
        case 'bc': //底部居中
            $dst_x = ($dst_w - $src_w)/2;
            $dst_y = $dst_h - $src_h - 10;
            break;
        case 'bl': //底部居左
            $dst_x = 10;
            $dst_y = $dst_h - $src_h - 10;
            break;
        default: //底部居右
            $dst_x = $dst_w - $src_w - 10;
            $dst_y = $dst_h - $src_h - 10;
    }
    //将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
    //imagecopymerge($dst, $src, 10, 10, 0, 0, $src_w, $src_h, 50);
    //如果水印图片本身带透明色，则使用imagecopy方法
    imagecopy($dst, $src, $dst_x, $dst_y, 0, 0, $src_w, $src_h);
    //输出图片
    switch ($dst_type) {
        case 1://GIF
            header('Content-Type: image/gif');
            imagegif($dst,$save_path);
            break;
        case 2://JPG
            header('Content-Type: image/jpeg');
            imagejpeg($dst,$save_path);
            break;
        case 3://PNG
            header('Content-Type: image/png');
            imagepng($dst,$save_path);
            break;
        default:
            break;
    }
    imagedestroy($dst);
    imagedestroy($src);
    return $save_path;
}

function check_sdk_ver($file) {
    $r = array();

    $cmd = "/data/www/wwwroot/config/gnu/aapt  d xmltree ".$file." AndroidManifest.xml|grep ANZHIUSERCENTE_VERSIONS -A1|grep -v ANZHIUSERCENTE_VERSIONS";
    $ver = trim(shell_exec($cmd));
    if(preg_match('/Raw: "([^"]+)"/',$ver,$matches)){
        $r[] = $matches[1];
        return $r;
    }
    if(strpos($ver,'type 0x10')){
        $version_str = substr($ver,strrpos($ver,')')+1);
        $r[] =  hexdec($version_str);
        return $r;
    }
    if(strpos($ver,'type 0x4')){
        $version_str = substr($ver,strrpos($ver,')')+1);
        $r[] = round(hexToDecFloat($version_str),1);
        return $r;
    }

    $tmp_dir = '/tmp';
    $classes = 'classes.dex';
    $cmd = "unzip -jo -d {$tmp_dir} \"{$file}\" {$classes} 2>/dev/null";
    shell_exec($cmd);
    $vers = array(
        //'1.0',
        '3.1.1',
        '3.1.2',
        '3.1.3',
    );
    $i = 0;
    foreach ($vers as $ver) {
        $pver = preg_quote($ver);
        $cmd = "strings {$tmp_dir}/{$classes}|grep '^{$pver}\$'";
        $line = trim(shell_exec($cmd));
        if ($ver == $line) {
            $r[] = $ver;
            $i++;
        }
    }
    return $r;
}

function hexToDecFloat($strHex) {
    $v = hexdec($strHex);
    $x = ($v & ((1 << 23) - 1)) + (1 << 23) * ($v >> 31 | 1);
    $exp = ($v >> 23 & 0xFF) - 127;
    return $x * pow(2, $exp - 23);
}


//礼包推送调用
function push_gift_msg($data){
    permanentlog("push_gift.log",date("Y-m-d H:i:s").print_r($data,true));
    $push = D("Sj.Push");
    $model = D('sendNum.sendNum');
    $vals['title'] = $data['active_name'];
    $vals['intro'] = $data['intro'];
    $vals['item_objid'] = $data['active_id'];
    $vals['start_time'] = $data['start_tm'];
    $vals['item_url'] = empty($data['icon_url'])?'':$data['icon_url'];
    //兑换时间大于3天按三天计算，小于三天按兑换结束时间
    if($data['cut_tm']-$data['start_tm']>259200){
        $vals['end_time'] =  (int)$data['start_tm']+(86400*3);
    }else{
        $vals['end_time'] = (int)$data['cut_tm']-300;
    }
    $vals['push_type'] = 5;
    $push_id = $model->table('sendNum.sendnum_active')->where(array("id"=>$data['active_id']))->field('push_id')->find();
        permanentlog("push_gift.log", $model->getLastSql());

    if(!empty($push_id['push_id'])){
        //编辑推送
        $vals['pro_status'] = 0;
        $push->editSdkPush($push_id['push_id'],$vals);
        permanentlog("push_gift.log",date("Y-m-d H:i:s").print_r($vals,true));
    }else{
        //添加推送
        $push_id = $push->addSdkPush($vals);
        $model->table('sendNum.sendnum_active')->where(array("id"=>$data['active_id']))->save(array('push_id'=>$push_id));
        permanentlog("push_gift.log", $model->getLastSql());
        permanentlog("push_gift.log",date("Y-m-d H:i:s").print_r($vals,true).'推送id为:'.$push_id);
    }

}

function getDataFromBi($params,$model_id='40')
{
    $url = 'http://172.16.1.73:9080';
    //$url = 'http://103.227.81.66:89';
    $res = curl_init();
    curl_setopt($res, CURLOPT_URL, "{$url}/public/index.php?_c=Analysis&_a=index&model_id={$model_id}&show_type=json");
    curl_setopt($res, CURLOPT_POST, true);
    curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($res, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($res);
    return $result;
}

function get_rank(&$last_rank){
    //var_dump($db_rank);
    $rank = 1;
    if(count($last_rank)>0){
        while(true){
            if(in_array($rank,$last_rank) || $rank==2){
                $rank += 1;
            }else{

                break;
            }
        }
    }
    return $rank;
}

function is_time_cross($beginTime1 = '', $endTime1 = '', $beginTime2 = '', $endTime2 = '')
{
    $status = $beginTime2 - $beginTime1;
    if ($status > 0)
    {
        $status2 = $beginTime2 - $endTime1;
        if ($status2 >= 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    else
    {
        $status2 = $endTime2 - $beginTime1;
        if ($status2 >= 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function m_number_format($data){
    return number_format($data, 2);
}

function getAnzhiFile($file_path)
{
	//$file = 'http://118.26.224.18/push_file'. $file_path;
	$file = '/data/att/518/push_file'.$file_path;
	$str = file_get_contents($file);
	$str = str_replace("\r\n", "\n", $str);
	$arr = explode("\n", $str);
	$result = array();
	foreach ($arr as $val) {
		$val = trim($val);
		if (empty($val)) continue;
		
		$encoding = mb_detect_encoding ( $val ,  "UTF-8,GBK" );
		if ($encoding != 'UTF-8') {
			$val = mb_convert_encoding( $val, 'utf-8', $encoding);
		}
		$result[] = $val;
	}
	return $result;
}

function relevance_softname($package,$data){
    $data['appKey'] = getAppKey($package);
    if(!$data['appKey']) return true;
    $res = json_decode(modifyAppNew($data), true);
    if(!$res['code']=='success'){
        return false;
    }else{
        return true;
    }
}

//保留小数点后几位，不进位;
function figure_float($num,$precision=2){
    $res = bcadd($num,0,$precision);
    return $res;
}
/*
计算生成红包队列

$money 总金额
$num 总红包数
$min 最小金额
$coef 最大金额系数
$singlemax 单个最大金额
*/

function create_queue($money,$num,$min,$coef,$singlemax){
    if($money<0){
        return false;
    }
    if($num<4){
        return false;
    }
    if($min<0.01){
        return false;
    }
    if($min>$singlemax){
        return false;
    }
    //红包平均值，保留两位小数
    $average = figure_float($money/$num,2);
    if($average < $min) {
        return false;
    }
    //在平均值和最大系数值之间去最小的
	// $min_flag = min($average,2.8); 
    //系数必须大于等于1,小于$min_flag
    if($coef > 2.8 || $coef <1){
        return false;
    }
    if($average == $min){
        return array_fill(0,$num,$min);
    }
    $pack = array();
    $sum = 0;
    
    for($i=0;$i<$num;$i++){
        $max = figure_float(($money-$sum)/($num-$i),2);
        //金额随机上界限
        $maxrand = floor($max*$coef*100);
        //金额随机下界限
        $minrand = $min * 100;
        
        if($maxrand < $minrand) {
            break;
        }
        $rand = mt_rand($minrand,$maxrand)/100;
        //如果红包的随机金额大于单个红包最大额，红包金额等于单个红包最大额
        if($rand>$singlemax){
            $rand = $singlemax;
        }
        
        $sum = bcadd($sum,$rand,2);
        //如果红包金额大于了总金额 断掉
        if($sum > $money){
            $sum = bcsub($sum,$rand,2);
            break;
        }
        $pack[$i] = $rand;
    }
    
    $restnum = $num - count($pack);
    $resmon = bcsub($money,$sum,2);
    //如果生成红包总数小于需要发放的红包数，用最低金额补足剩余的红包数量
    
    if($restnum>0 && $resmon > 0){
        $maxnum = floor($resmon/$min);
        //当剩余的前小于红包
        $restnum = min($maxnum,$restnum);
        if( $restnum > 0 ){
            $newpack = array_fill(0,$restnum,$min);    
            $pack = array_merge($pack,$newpack);
        }
        
    }
    
    shuffle($pack);
   
    return $pack;
    
}

/*
计算生成红包队列 以分为单位

$money 总金额
$num 总红包数
$min 最小金额
$coef 最大金额系数
$singlemax 单个最大金额
*/


function create_queue_intval($money,$num,$min,$coef,$singlemax){
    if($money < 0){
        return false;
    }
    if($num < 4){
        return false;
    }
    if($min < 1){
        return false;
    }
    if($min > $singlemax){
        return false;
    }
    //红包平均值，保留两位小数
    $average = floor($money / $num);
    if($average < $min) {
        return false;
    }
    //在平均值和最大系数值之间去最小的
	// $min_flag = min($average,2.8); 
    //系数必须大于等于1,小于$min_flag
    if($coef > 2.8 || $coef < 1){
        return false;
    }
    if($average == $min){
        return array_fill(0,$num,$min);
    }
    $pack = array();
    $sum = 0;
    
    for($i=0;$i<$num;$i++){
        //金额随机上界限
        $maxrand = floor((($money-$sum)/($num-$i))*$coef);
        //金额随机下界限
        $minrand = $min;
        
        if($maxrand < $minrand) {
            break;
        }
        $rand = mt_rand($minrand,$maxrand);
        //如果红包的随机金额大于单个红包最大额，红包金额等于单个红包最大额
        if($rand > $singlemax){
            $rand = $singlemax;
        }
        
        $sum = $sum + $rand;
        //如果红包金额大于了总金额 断掉
        if($sum > $money){
            $sum = $sum - $rand;
            break;
        }
        $pack[$i] = $rand;
    }
    
    $restnum = $num - count($pack);
    $resmon = $money - $sum;
    //如果生成红包总数小于需要发放的红包数，用最低金额补足剩余的红包数量
    
    if($restnum > 0 && $resmon > 0){
        $maxnum = floor($resmon/$min);
        //当剩余的前小于红包
        $restnum = min($maxnum,$restnum);
        if($restnum > 0){
            $newpack = array_fill(0,$restnum,$min);    
            $pack = array_merge($pack,$newpack);
        }
        
    }
    
    shuffle($pack);
   
    return $pack;
    
}

function process_image($required=true, $filename, $image_width=0, $image_height=0, $image_name='图片', $expression='jpg|png',$is_tmpname=false)
{
    $tmp_name = $_FILES[$filename]['tmp_name'];
    $name = $_FILES[$filename]['name'];
    if($is_tmpname){
        $tmp_name = $filename['tmp_name'];
        $name = $filename['name'];
    }
    if($required&&!$tmp_name){
        $msg = "请上传{$image_name}！";
        return array('code'=>0,'msg'=>$msg);
    }
    if($tmp_name){
        // 取得图片后缀
        $name = strtolower($name);
        $suffix = preg_match("/\.({$expression})$/", $name,$matches);
        if ($matches) {
            $suffix = $matches[0];
        } else {
            return array('code'=>0,'msg'=>"{$image_name}格式应为{$expression}！");
        }
        // 判断图片长和宽
        $img_info_arr = getimagesize($tmp_name);
        if (!$img_info_arr) {
            return array('code'=>0,'msg'=>"上传{$image_name}出错！");
        }
        $width = $img_info_arr[0];
        $height = $img_info_arr[1];
        if($image_width!=0&&$image_height!=0){
            if ($width!=$image_width || $height!=$image_height){
                return array('code'=>0,'msg'=>"{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
            }
        }
        $folder = "/img/".date("Ym/d/");
        $adir = explode('/',UPLOAD_PATH . $folder);
        $dirlist = '';
        $rootdir = array_shift($adir);
        if(($rootdir!='.'||$rootdir!='..')&&!file_exists($rootdir)){
            @mkdir($rootdir);
        }
        foreach($adir as $key=>$val){
            $dirlist .= "/".$val;
            $dirpath = $rootdir.$dirlist;
            if(!file_exists($dirpath)){
                @mkdir($dirpath);
                @chmod($dirpath,0777);
            }
        }
        $relative_path = $folder . time() . '_' . rand(1000,9999) . $suffix;
        $img_path = UPLOAD_PATH . $relative_path;
        move_uploaded_file($tmp_name, $img_path);
        return array('code'=>1,'msg'=>$relative_path);
    }else{
        return array('code'=>1);
    }

}
#返回内容质量
function content_level_selecttag($level_val='',$type='',$src_flag=0){    
    $list = C('CONTENT_LEVEL');
    $type = $type ? $type : 'select';
    $tags = '';
    $tag_obj_id = " id ='cont_level'";
    if($src_flag) $tag_obj_id = '';
    if($type == 'select'){
        $tags .= "<select {$tag_obj_id} name='content_level'>";
        $tags .= '<option value="0" selected>全部</option>';
        foreach($list as $k => $val){
            if($level_val == $k){
                $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
            }else{
                $tags .= '<option value="'.$k.'">'.$val.'</option>';
            }
        }
        $tags .= "</select>";

    }elseif($type == 'radio'){
        foreach($list as $k => $val){
            if($level_val == $k){
                $tags .= '<input name="content_level" checked type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
            }else{
                $tags .= '<input name="content_level" type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
    }
    
    return $tags;
}
#返回内容性质
function content_nature_selecttag($nature_val='',$type='',$src_flag=0){ 
    $list = C('CONTENT_NATURE');
    $type = $type ? $type : 'select';
    $tags = '';
    $tag_obj_id = " id='cont_nature'";
    if($src_flag) $tag_obj_id = '';
    if($type == 'select'){
        $tags .= "<select {$tag_obj_id} name='content_nature'>";
        $tags .= '<option value="0" selected>全部</option>';
        foreach($list as $k => $val){
            if($nature_val == $k){
                $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
            }else{
                $tags .= '<option value="'.$k.'">'.$val.'</option>';
            }
        }
        $tags .= "</select>";
    }elseif($type == 'radio'){
        foreach($list as $k => $val){
            if($nature_val == $k){
                $tags .= '<input name="content_nature" checked type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
            }else{
                $tags .= '<input name="content_nature" type="radio" value="'.$k.'"/>'.$val.'&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
    }
      
    return $tags;
}
#返回软件类型
function content_soft_type_selecttag($softid='',$src_flag=0){
  $list = C('CONTENT_SOFT_TYPE');
  $tags = '';
  $tag_obj_id = " id ='soft_type'";
  if($src_flag) $tag_obj_id = '';
  $tags .= "<select {$tag_obj_id} name='soft_type'>";
  foreach($list as $k => $val){
    if($softid == $k){
        $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
    }else{
        $tags .= '<option value="'.$k.'">'.$val.'</option>';
    }
  }
  $tags .= "</select>";
  return $tags;   
}
#返回内容栏目
function content_column_selecttag($checked=array()){
    $model = D('Sj.ContColumn');
    $column_list = $model->getall_cont_column();
    $arr = $tag = $tags = $sct = '';
    $arr .='<li style="list-style: none">已选择栏目名称：';
    $tag .= '<li style="list-style: none">';
    foreach ($column_list as $column_value) {
        if(in_array($column_value['cont_id'],$checked)){
            $column_select .= $column_value['name'].',';
        }
        $tag .= '<input type="checkbox" onclick="checklist(this)" title="'.$column_value['name'].'" name="cont_column[]" value="'.$column_value['cont_id'].'"'; 
        if(in_array($column_value['cont_id'],$checked)) $tag .= 'checked ';
        $tag .= '>'.$column_value['name'].'&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    $arr .= '<span style="color:blue;" id="selectlist">'.$column_select.'</span></li>';
    $tag .= '</li>';
    $sct .= '<script>';
    $sct .= 'function checklist(obj){';
    $sct .= 'var column_select = ""; ';
    $sct .= 'var obj_column = document.getElementsByName("cont_column[]");';
    $sct .= 'for(k in obj_column){';
    $sct .= 'if(obj_column[k].checked) column_select += obj_column[k].title+",";}';
    $sct .= '$("#selectlist").text(column_select);}';
    $sct .= '</script>';
    $tags = $arr.$tag.$sct;
    return $tags;
}

#返回资源
function content_html_unit($config){ 

  $list = C($config['key']);
  if(empty($list)){
      return '';
  }
  $tags = '';
  if($config['type'] == "select"){
    $tags .= "<select id='{$config['tag_id']}' name='{$config['tag_name']}'>";
    $tags .= $config['tag_tip'] ? '<option value="0" selected>'.$config['tag_tip'].'</option>' : '';
    foreach($list as $k => $val){
      if($config['default'] == $k){
          $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
      }else{
          $tags .= '<option value="'.$k.'">'.$val.'</option>';
      }
    }
    $tags .= "</select>";
  }elseif($config['type'] == "radio"){
    foreach($list as $k => $val){
      if($config['default'] == $k){
          $tags .= "<input name='{$config['tag_name']}' checked type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
      }else{
          $tags .= "<input name='{$config['tag_name']}' type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
      }
    }
  }
  return $tags;
}
#用户倾向
function user_tendency($config_user){ 

  $list = C($config_user['key']);
  if(empty($list)){
      return '';
  }
  $tags = '';
  if($config_user['type'] == "select"){
    $tags .= "<select id='{$config_user['tag_id']}' name='{$config_user['tag_name']}'>";
    $tags .= $config_user['tag_tip'] ? '<option value="0" selected>'.$config_user['tag_tip'].'</option>' : '';
    foreach($list as $k => $val){
      if($config_user['default'] == $k){
          $tags .= '<option selected="selected" value="'.$k.'">'.$val.'</option>';
      }else{
          $tags .= '<option value="'.$k.'">'.$val.'</option>';
      }
    }
    $tags .= "</select>";
  }elseif($config_user['type'] == "radio"){
    foreach($list as $k => $val){
      if($config_user['default'] == $k){
          $tags .= "<input name='{$config_user['tag_name']}' checked type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
      }else{
          $tags .= "<input name='{$config_user['tag_name']}' type='radio' value='".$k."'/>".$val."&nbsp;&nbsp;&nbsp;&nbsp;";
      }
    }
  }
  return $tags;
}
/*编辑栏目通知java端调用接口
 *Int     contentType  内容类型枚举:1 图文,2 视频
 *long    id           图片/视屏id
 *Int     optType      1 创建/更新,2 删除
 *String  packageName  包名
 *Integer showStyle    1 单图片,2 多图片，3 视频内容
*/
function curl_update_content_package($post_data) {
    sleep(2);
    $data['data'] = json_encode($post_data);
    $url = "http://feedadmin.web.lan.anzhi.com/admin/phpDataUpdate";
    // $header = array("host: dev.feedadmin.web.lan.anzhi.com");   //测试域名
    // $header = array("host: feedadmin.web.lan.anzhi.com");    //线上域名
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    file_put_contents('/tmp/java_api_bessy.log','request : '.json_encode($post_data).' resq:'.json_encode($result).' http code :'.$http_code.' '.date('Y-m-d H:i:s')."\n",FILE_APPEND);
    curl_close($ch);
    return $result;
}

if (!function_exists('curl_file_create')) {
  function curl_file_create($file)
  {
    return '@'. $file;
  }
}
