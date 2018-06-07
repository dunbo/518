<?php
require_once("SSDB.php");
file_put_contents("/tmp/ssdb.log","2343"."\r\n",FILE_APPEND);
class GoSsdb
{
	protected  static  $ssdb;
        public function __construct($host,$port)
        {	
              try{
                $this->ssdb = new SimpleSSDB($host, $port);
                }catch(Exception $e){
                      die(__LINE__ . ' ' . $e->getMessage());
               }
        }
	
	function get($key)
        {
                if(empty($key))return false;
                $result = $this->ssdb->get($key);
                if($result)
                {
                        return $result;
                }
                return false;
        }

        function set($key,$val)
        {
                if(empty($key)) return false;
                $result = $this->ssdb->set($key,$val);
                if($result)
                {
                        return true;
                }
                return false;
        }
}
?>
