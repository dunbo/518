<?php

class SearchKeyClient{

   private $_timeout;
   public $_error;
   public $_port;
   public $_host;
   public $socket;
   public $_warning;
   public $_connerror;
   public $_path;
   
  
   public function SearchKeyClient(){
     
	    $this->_timeout     = 1;
		$this->_host		= "localhost";
		$this->_port		= 1111;
		$this->_path		= false;
		$this->socket		= false;
		$this->_error		= ""; // per-reply fields (for single-query case)
		$this->_warning		= "";
		$this->_connerror	= false;
	
   }
  
  
   public function _Connect(){
       
	    if ( $this->socket!==false )
		{    
		    
			 return $this->socket;
		}
		
		
		
		$errno = 0;
		$errstr = "";
		$this->_connerror = false;
		
		if ( $this->_path )
		{
			$host = $this->_path;
			$port = 0;
		}
		else
		{
			$host = $this->_host;
			$port = $this->_port;
		}
		
		
	   
	    $this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_set_option($this->socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>20, "usec"=>0 ) );
        socket_set_option($this->socket,SOL_SOCKET,SO_SNDTIMEO,array("sec"=>20, "usec"=>0 ) );
        socket_set_option($this->socket,SOL_SOCKET,SO_KEEPALIVE,10);
	    $result =  socket_connect($this->socket,$host,$port);
	   
	  
     
	   if ($result<0)
		{
			if ( $this->_path )
				$location = $this->_path;
			else
				$location = "{$this->_host}:{$this->_port}";
			
			$errstr = trim ( $errstr );
			$this->_error = "connection to $location failed (errno=$errno, msg=$errstr)";
			$this->_connerror = true;
			return false;
		}
		
   }
   
   public function SetServer ( $host, $port = 0 )
	{
		assert ( is_string($host) );
		
		if ( $host[0] == '/')
		{
			$this->_path = 'unix://' . $host;
			return;
		}
		
		if ( substr ( $host, 0, 7 )=="unix://" )
		{
			$this->_path = $host;
			return;
		}
				
		assert ( is_int($port) );
		$this->_host = $host;
		$this->_port = $port;
		$this->_path = '';

	}
	
	public function SetConnectTimeout ( $timeout )
	{
		  assert ( is_numeric($timeout) );
		  $this->_timeout = $timeout;
	}

   
    public function _Send ($data, $length = 1024 )
	{     
	
	     
	      
		  $reg = pack("nnN",0,1,strlen($data));
		  
		  socket_write($this->socket, $reg.$data) or die("Write failed\n"); // 数据传送 向服务器发送消息 
		  
		  $header  =  socket_read($this->socket,8);
		  
		  if(strlen($header)==8 ){
             list($status, $ver, $len) = array_values(unpack("n2a/Nb",$header));     
			 $left = $len;
			 $response = ""; 
			
			 while($left>0){
					
					$buf = socket_read($this->socket,1024);
		            
					if(!empty($buf)){
						
					   $response .= $buf;
					 
					   $left -=strlen($buf);    
		
					}else{
					  
						 break;
					
					}           
		 
			 }

            
            $this->Close();
		     
			 if($response){
		         return json_decode($response,true);
		     }
			 
			 
		}
		
		
		$this->Close();

		return false;
	}

	 
	
	public function _MBPop ()
	{
		if ( $this->_mbenc )
			mb_internal_encoding ( $this->_mbenc );
	}
	
	public function query($query,$filter=array()){
		   
           
		 $this->_Connect();
		 if($this->socket){
            
            if(!is_array($filter)){
                 $filter = array();
            }

            $filter['q'] = $query;
           
            $str = json_encode($filter);
           
	        return $this->_Send($str);
		 }
		return false; 
	}
	
	public function Close()
	{
		if ( $this->socket !== false ){
			
			socket_close($this->socket);
			$this->socket = false;
		}
			 
	}
	
	public function __destruct()
	{
		if ( $this->socket !== false ){

			socket_close($this->socket);
			$this->socket = false;
		}
	}


}