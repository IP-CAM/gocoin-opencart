<?php
	$arr= array();
	$arr=explode('catalog', getcwd()); //getcwd() /var/www/opencart/catalog/controller/payment
	include($arr[0].'config.php');
	include(DIR_SYSTEM.'database/mysql.php');
	include(DIR_SYSTEM.'library/gocoinlib/src/GoCoin.php');
	function showtoken(){
		$obj = new  DBMySQL(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

	    $data = $obj->query("SELECT  `key` ,`value` FROM  `".DB_PREFIX."setting` WHERE  `group` =  'gocoin'");
	    $result1 = $data->rows;
	    if(!empty($result1))
	    {
	    	  
			$clientid='';
			$secretkey='';
	    	foreach ($result1 as $result) {

		    	 
		    		if($result['key']=='gocoin_gocoinsecretkey'){
		     			  $secretkey=$result['value'];
		    		}
		    		if($result['key']=='gocoin_gocoinmerchant'){
		   			  $clientid=$result['value'];
		    		}
		    	 
		    	  
	    		
	    	}
	    	  if(!empty($clientid)&& !empty($secretkey))
			        {
			            try{
			                $code =isset($_REQUEST['code']) && !empty($_REQUEST['code'])?$_REQUEST['code']:'';
			                $token= GoCoin::requestAccessToken($clientid, $secretkey, $code, null);
			                $msg = "<b>Copy this Access Token into your Opencart GoCoin configuration:</b><br> ".$token;
			                die($msg);
			            }catch (Exception $e)
			            {
			                die( $e->getMessage());
			            }
			            
			        }
			        else
			        {
			           die("Please provide and save the Merchant ID and Secret Key before getting Token");
			        }
 
	    }
	    else
	    {
	        die("Please provide and save the Merchant ID and Secret Key before getting Token");
	    } 
    }
showtoken();
?>