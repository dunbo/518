<?php 
//require_once '/data/www/m.anzhi.com/trunk/wwwroot/init.php';
require_once(dirname(realpath(__FILE__)).'/../init.php');
 $version_code = $_GET['verc'];
 if (empty($version_code))
    exit;
 $model = new GoModel();
   $option = array(
		'where' => array(
		   'verc' =>$_GET['verc'] ,
		 ),
		'table' => 'sj_aboutslideshow'
	);
 $result = $model -> findOne($option);
	if($result['img1']!=null)
	{
	  $img1=getImageHost().$result['img1'];
	}
	if($result['img2']!=null)
	{
	  $img2=getImageHost().$result['img2'];
	}
	if($result['img3']!=null)
	{
	  $img3=getImageHost().$result['img3'];
	}
	if($result['img4']!=null)
	{
	  $img4=getImageHost().$result['img4'];
	}
	if($result['img5']!=null)
	{
	  $img5=getImageHost().$result['img5'];
	}
	$img=array($img1,$img2,$img3,$img4,$img5);
	if($img!=null)
	{
	  echo json_encode($img);
	}
?>

