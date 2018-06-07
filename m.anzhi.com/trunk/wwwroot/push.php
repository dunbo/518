<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

$push_id = $_GET['pushid'];

//$filter_option

$push_model = load_model('push');


$launch_data_arr = $push_model->getAdListCached('REQ_PUSH',-1,$vr, $filter_option);

$json = json_encode($launch_data_arr[$push_id]);
?>

<script>
if (typeof(window['AnzhiActivitys']) != 'undefined') {
    window.AnzhiActivitys.launch('<?php echo $json ?>', 0);
    window.AnzhiActivitys.exitPage();
}
</script>