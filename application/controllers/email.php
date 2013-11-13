<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class email extends base_controller 
{
	function index_post()
	{
		$email = $this->input->post('email');
		$this->wr_email->setData(array('email'=>$email));
		$this->wr_email->insert();
	}
}
