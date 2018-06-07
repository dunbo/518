<?php
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';

$model = new GoModel();

if (isset($_POST['formula'])) {
	$formula = trim($_POST['formula']);
	$formula = str_replace('（', '(', $formula);
	$formula = str_replace('）', ')', $formula);
	$formula = str_replace('安装', '`install_cnt`', $formula);
	$formula = str_replace('下载', '`download_cnt`', $formula);
	$formula = str_replace('卸载', '`uninstall_cnt`', $formula);
	$formula = str_replace('浏览量', '`view_cnt`', $formula);
	$formula = str_replace('评分', '`score`', $formula);
	$formula = preg_replace('/(\d+)\%/', '0.\1', $formula);
	$sql = "update `tmp_soft_statics` set `order_num`={$formula};";
	$model->query($sql);
	exit($sql);
}





$option = array(
	'where' => array(
		'submit_day' => strtotime($_POST['date'])
	),
	'order' => $_POST['order']. ' desc',
	'table' => 'tmp_soft_statics',
	'limit' => 50
);

$res = $model->findAll($option);
?>

<table border="1">
	<tr>
		<td>排名</td>
		<td>包名</td>
		<td>下载数</td>
		<td>浏览数</td>
		<td>安装数</td>
		<td>卸载数</td>
		<td>评分</td>
		<td>计算值</td>
	</tr>
<?php $i=1;?>
<?php foreach($res as $row): ?>
	<tr>
		<td><?php echo $i++;?></td>
		<td><?php echo $row['package']?></td>
		<td><?php echo $row['download_cnt']?></td>
		<td><?php echo $row['view_cnt']?></td>
		<td><?php echo $row['install_cnt']?></td>
		<td><?php echo $row['uninstall_cnt']?></td>
		<td><?php echo $row['score']?></td>
		<td><?php echo $row[$_POST['order']]?></td>
	</tr>
<?php endforeach;?>
</table>