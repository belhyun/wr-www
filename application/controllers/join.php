<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class join extends base_controller 
{
	function index_post()
	{
		$name = $this->input->post('user_name');
		$email = $this->input->post('user_id');
		$pwd = $this->input->post('user_pwd');
		$intCat = $this->input->post('int_cat');

		if($pwd == null || $name == null || $email == null || $intCat == null)
		{
			$this->response(wr_http_message::get400('bad_request'), 400);
		}

		if(!$this->isLogged())
		{
			$this->wr_user->setData(array('name'=>$name,'wr_email'=>$email,'pwd'=>$pwd,'cat_id'=>$intCat));
			try{
				if($user = $this->wr_user->join()){
				}
			}catch(Exception $ex){
				$this->response(wr_http_message::get200($ex->getMessage(),0),
						wr_http_message::SUCCESS_200);
			}
			$msgAry = wr_http_message::get200('Success');
			$msgAry['acc_token'] = $user['acc_token'];
			$msgAry['user_id'] = $user['user_id'];
			$this->response($msgAry, wr_http_message::SUCCESS_200);
		}
		else
		{
			$this->response(wr_http_message::get401('already_authorized'), wr_http_message::ERROR_401);
		}
	}
}
