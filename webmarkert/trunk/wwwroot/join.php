<?php
	include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');
	$tplObj->out['type'] = 'link';
	if ($_POST){
		$model = new GoModel();
		//添加记录
		$data = array(
			'link_name' => $_POST['link_name'],
			'link_url' => $_POST['link_url'],
			'qq' => $_POST['link_qq'],
			'email' => $_POST['link_email'],
			'create_tm' => time(),
			'state' => 2,
			'__user_table' => 'sj_frendly_link'
		);
		if ($model->insert($data)){
			echo "<script type='text/javascript'>alert('提交成功！'); window.location.href='/'</script>";
			//header('Location: http://newweb.anzhi.com/index.php');
		} else {
			echo "<script type='text/javascript'> alert('提交失败！'); window.location.reload(); </script>";
		}
		return ;
	} else {
		// 友情链接
		$model = new GoModel();
		$option = array(
			'table' => 'sj_frendly_link',
			'where' => array(
				'state' => 1,
				'status' => 1,
			),
			'order' => 'rank',
			//'field' => ''
		);
		$link = $model->findAll($option);
		//krsort($link);
		$tplObj->out['links'] = $link;		
	}
	$tplObj->display('join.html');