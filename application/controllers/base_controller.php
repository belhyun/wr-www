<?php defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
class base_controller extends REST_Controller{
	protected $accToken = '';

	function __construct()
	{
		parent::__construct();
		$headers = apache_request_headers();
		if(!empty($headers['acc_token']))
		{
			$this->accToken = $headers['acc_token'];
		}
	}
	function isLogged($acc_token=null,$id=null)
	{
		if($acc_token == null || $id == null)
		{
			return false;
		}
		if($this->wr_user->isLogged($acc_token,$id))
		{
			return true;
		}
		return false;
	}

	function response($result)
	{
		$requestType = null;
		if($this->input->post('request_type') != null)
		{
			$requestType = $this->input->post('request_type');
		}
		else if($this->input->get('request_type') != null)
		{
			$requestType = $this->input->get('request_type');
		}
		if($requestType != null)
			$result['requestType'] = $requestType;
		parent::response($result);
	}
}
