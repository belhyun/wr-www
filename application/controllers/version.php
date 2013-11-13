<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class version extends base_controller 
{
	function index_get()
	{
		$msgAry = wr_http_message::get200();
		$msgAry['version'] = $this->wr_version->version();
		$this->response($msgAry,200);	
	}				
}

?>
