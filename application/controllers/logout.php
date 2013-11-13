<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');

class logout extends base_controller 
{
	function index_post()
	{
		$id = $this->input->post('user_id');
	
		if($id == null)
		{
			$this->response(wr_http_message::get400('bad_request'), 400);
		}
				
		$this->wr_user->setData(array('id'=>$id));
		try{
			$token = $this->wr_user->logout();
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
		$this->response(wr_http_message::get200('success'), wr_http_message::SUCCESS_200);
	}
}

?>
