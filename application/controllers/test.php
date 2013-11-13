<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class test extends base_controller 
{
	function index_get()
	{
		$this->s3->putObject('test','witherest-image','test.txt','public-read');
	}				
}

?>
