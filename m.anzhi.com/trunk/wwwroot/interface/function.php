<?php 
define('API_ROOT',dirname(dirname(dirname(__FILE__))).DS.'api.anzhi.com');
function getAPI($module_action, $parameters){
    list($module, $action) = explode('.', $module_action);
	include_once API_ROOT.DS.'Action.class.php';
    $action_file = API_ROOT . DS. strtolower($module). DS. $action. '.php';
    if (!file_exists($action_file)) {
        return False;
    }
    include_once $action_file;
    if (!class_exists($action)) {
        return False;
    }
    $actionClass = new $action($parameters);
    return $actionClass->getData();
}
