<?php
#智盟广告栏目数据导出脚本
error_reporting(E_ERROR);
ini_set('display_errors', 'on');
include dirname(realpath(__FILE__)). '/../load_gophp.php';

$model = new GoModel();

//获取内容外显数据
$table = 'sj_soft_content_explicit';
$table1 = 'sj_soft_extra';
$table2 = 'cont_column_content_video';

$data = '';
//从$table获取数据
$result = $model -> query("select id,cont_column from $table where status=1 and cont_column!='0' and cont_column!=''");
while ($row = $model -> fetch($result)) {
	$id = $row['id'];
	$column = $row['cont_column'];
	$column_ids = explode(',',trim($column,','));
	foreach ($column_ids as $column_id) {
		$data .= '(2,'.$column_id.','.$id.'),';
	}
}
//从$table1获取数据
$res = $model -> query("select id,cont_column from $table1 where status=1 and cont_column!='0' and cont_column!=''");
while ($video = $model -> fetch($res)) {
	$video_id = $video['id'];
	$column = $video['cont_column'];
	$video_ids = explode(',',trim($column,','));
	foreach ($video_ids as $column_id) {
		$data .= '(3,'.$column_id.','.$video_id.'),';
	}
}
$data = trim($data,',');

//把数据导入table2
$model -> query("insert into $table2 (column_type,columnid,con_videoid) values $data");

die;

