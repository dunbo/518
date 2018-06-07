<?php
/*
 * created by 黄文强
 * at 2013/02/27
 */
function generateSN($pid)
{
	if (empty($pid))
		return false;
	$model = new GoModel();
	$flag = false;
	$count = 0;
	while (!$flag && $count < 5)
	{
		$ts = microtime();
		$random = rand(0, 9999999999);
		$origin = "SN_" . $ts . "_" . $random;
		$sn = md5($origin);
		$option = array(
			'__user_table' => 'sj_sn',
			'sn' => $sn,
			'pid' => $pid,
			'create_at' => time()
		);
		$info = $model->insert($option);
		if ($info)
		{
			$flag = true;
			return $sn;
		}
		else
		{
			$count++;
		}
	}
}
?>
