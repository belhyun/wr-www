<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class api extends CI_Controller
{
	function index()
	{
		$this->load->view('api/index');	
	}
}
?>
