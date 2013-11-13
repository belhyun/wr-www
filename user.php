<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('/BaseController.php');

class User extends BaseController 
{
	function index_get()
	{
		$this->load->model('WrUser');
		$this->load->model('WrUserIntCategory');
		$this->load->model('WrHttpMessage');
		
		$name = $this->input->get('userName');
		$email = $this->input->get('userId');
		$pwd = $this->input->get('userPw');
		$intCat = $this->input->get('interestCategory');
		
		foreach(get_defined_vars()as $k => $v)
		{
			if(empty($v))
			{
				$this->response(WrHttpMessage::get400(), 400);
			}
		}
		
		if(!$this->isLogged(null,$email))
		{
			$this->WrUser->setData(array('name'=>$name,'email'=>$email,'pwd'=>$pwd));
			try{
				$this->WrUserIntCategory->setData(array('catId'=>$intCat));
				$this->WrUser->insert();
			}catch(Exception $ex){
				if($ex->getCode() == WrHttpMessage::ERROR_500)
				{
					$this->response(WrHttpMessage::get500($ex->getMessage()), WrHttpMessage::ERROR_500);
				}
			}
			$this->response(WrHttpMessage::get200(), WrHttpMessage::SUCCESS_200);
		}
		else
		{
			$this->response(WrHttpMessage::get401('Already Authorized'), WrHttpMessage::ERROR_401);
		}
	}
	
	function check_get()
	{
		$this->load->model('WrUser');
		$this->load->model('WrHttpMessage');
		$id = $this->input->get('id');
		if(empty($id))
		{
			$this->response(WrHttpMessage::get400(), 400);
		}
		$this->WrUser->setData(array('id'=>$id));
		try{
			$msgAry = WrHttpMessage::get200();
			if($this->WrUser->isDupl($id))
			{
				$msgAry['isDuplication'] = true;
				$this->response($msgAry, WrHttpMessage::SUCCESS_200);
			}
			else
			{
				$msgAry['isDuplication'] = false;
				$this->response($msgAry, WrHttpMessage::SUCCESS_200);
			}
		}catch(Exception $ex){
			if($ex->getCode() == WrHttpMessage::ERROR_500)
			{
				$this->response(WrHttpMessage::get500($ex->getMessage()), WrHttpMessage::ERROR_500);
			}
		}
	}
}