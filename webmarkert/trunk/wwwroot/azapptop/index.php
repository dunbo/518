<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
define('ROOT',dirname(__FILE__).'/');
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
include_once GO_APP_ROOT. DS. '..'.DS.'..'. DS. 'GoPHP'. DS. 'Startup.php';
date_default_timezone_set('Asia/Shanghai');
define('APP_NAME', 'www');
$model = new GoModel();

if($_SERVER['SERVER_ADDR']=='192.168.0.99') {
	define('IMGATT_HOST','http://192.168.0.99/testdata');
} elseif($_SERVER['SERVER_ADDR']=='118.26.203.23'){
	define('IMGATT_HOST','http://118.26.203.23/cmcc');
}else {
	define('IMGATT_HOST', 'http://img1.anzhi.com');
	//define('IMGATT_HOST','http://192.168.0.99/testdata');
}
$cache_time = 1800;

$year = isset($_GET['year'])? $_GET['year']:2014;

    $option = array(
            'table' => 'sl_year_list',
            'where' => array(
                'year'=>$year,
                'status'=>1,
            ),
            'order' => 'year',
            'cache_time' => $cache_time,
        );
$year_result=$model->findone($option);

    $option = array(
            'table' => 'sl_year_channel',
            'where' => array(
                'year_id'=>$year_result['id'],
                'status'=>1,
            ),
            'cache_time' => $cache_time,
        );
    $year_channel_result=$model->findall($option);
foreach($year_channel_result as $v)
{
	$channel_arr[] = $v['channel_id'];
}

foreach($channel_arr as $k => $v){

    $option = array(
            'table' => 'sl_channel_list',
            'where' => array(
                'id'=>$v,
            ),
            'cache_time' => $cache_time,
        );
    $channel_result=$model->findone($option);
    $channel[] = $channel_result;
}

    $option = array(
            'table' => 'sj_year_soft',
            'where' => array(
                'year'=>$year_result['id'],
            ),
            'order' => 'pos ASC,id ASC',
            'cache_time' => $cache_time,
        );
    $rs=$model->findall($option);
       foreach($rs as $v){
           if($v['package'])
           {
		$year_soft[$v['channel']][] = $v;
		$package[] = $v['package'];
           }
       }

//软件对应的icon,下载量,星星评级
//下载量,评分
        $package_str = "'".implode("','",$package)."'";

        $option = array(
            'field' => 'softid,package,total_downloaded,score',
            'table' => 'sj_soft',
            'where' => array(
                'package'=>array('exp','in ('.$package_str.')'),
                'status'=>1,
                'hide'=>1,
                'channel_id'=>'',
            ),
            'cache_time' => $cache_time,
        );
    $rs=$model->findall($option);        
        foreach($rs as $v)
        {
	    $soft[$v['package']] = $v;
        }

        //icon图标

        $option = array(
            'field' => 'apk_name,iconurl',
            'table' => 'sj_soft_file',
            'where' => array(
                'apk_name'=>array('exp','in ('.$package_str.')'),
            ),
            'cache_time' => $cache_time,
        );
    $rs=$model->findall($option);        
        foreach($rs as $v)
        {
	    $soft_file[$v['apk_name']] = $v;
        }


//软件信息汇总
if($year_soft) {
	foreach($year_soft as $key=>$val) {
		foreach($val as $k=>$v) {
			$year_soft[$key][$k]['href'] = "http://www.anzhi.com/soft_{$soft[$v['package']]['softid']}.html";
			$year_soft[$key][$k]['icon'] = IMGATT_HOST.$soft_file[$v['package']]['iconurl'];
			$year_soft[$key][$k]['stars'] = $soft[$v['package']]['score'];
			$year_soft[$key][$k]['str_downloaded'] = num_format2($soft[$v['package']]['total_downloaded'],2);

		}
	}
}

$yid = $year_result['id'];

//现场图片
    $option = array(
            'table' => 'sj_year_scenephoto',
            'where' => array(
                'year'=>$yid,
            ),
            'order' => 'id ASC',
            //'cache_time' => $cache_time,
        );
    $rs=$model->findall($option);        
        foreach($rs as $v)
        {
            $v['photo'] = IMGATT_HOST.$v['photo'];
            $scenephoto[] = $v;
        }

        //合作媒体
        $option = array(
            'table' => 'sj_year_mediapartners',
            'where' => array(
                'year'=>$yid,
            ),
            'order' => 'id ASC',
            'cache_time' => $cache_time,
        );
    $rs=$model->findall($option);        
        foreach($rs as $v)
        {
            $v['pic'] = IMGATT_HOST.$v['pic'];
            $mediapartners[] = $v;
        }

if($year==2014)
{
    require_once(ROOT.'tpl/2014.html');
}
/*else if($year ==2015)
{
    require_once(ROOT.'tpl/2015.html');
}*/

function num_format2($num, $type)
{
	if ($num === '') return $num;
	$num = intval($num);
	switch ($type) {
		case 1 :
			return $num;
		break;
		
		case 2:
			if ($num <= 100) {
				return '小于100次';
			} elseif ($num < 1000) {
				$n = floor($num / 100); 
				return "大于{$n}00次";
			} elseif ($num < 10000) {
				$n = floor($num / 1000); 
				return "大于{$n}千次";
			} elseif ($num < 100000) {
				$n = floor($num / 10000); 
				return "大于{$n}万次";
			} elseif ($num < 1000000) {
				$n = floor($num / 100000); 
				return "大于{$n}0万次";
			} elseif ($num < 10000000) {
				$n = floor($num / 1000000); 
				return "大于{$n}00万次";
			} else {
				return "大于1000万次";
			}
		break;
		default :
			return $num;
		break;
	}
}
