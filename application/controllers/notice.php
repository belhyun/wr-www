<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');
require_once(getcwd().'/application/libraries/GoogleCloudMsg.php');

class notice extends base_controller 
{
	function write_post()
	{
		$content = $this->input->post('content');
		$roomId = $this->input->post('room_id');
		$userId= $this->input->post('user_id');
		$gcm = new wr_gcm();

		if($roomId == null || $content == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		
		if(!$this->isLogged($this->accToken,$userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
		
		try{
			$this->wr_notice->setData(array('room_id'=>$roomId,
			'content'=>$content, 'user_id'=>$userId));
			
			if(($noticeId = $this->wr_notice->write()))
			{
				$this->wr_room_join_user->setData(array('room_id'=>$roomId,
							'user_id'=>$userId));
				$joinUsers = $this->wr_room_join_user->getJoinUsers();
				if($joinUsers)
				{
					if($noticeId)
					{
						$data = array('pushCode'=>wr_gcm_code::NOTICE_WRITE,
								'messageId'=>$noticeId);
						$reqIds = array();
						foreach($joinUsers as $user)
						{
							if(!empty($user->gcm_id))
								array_push($reqIds,$user->gcm_id);
						}
						$gcm->send($data,$reqIds);
					}
				}
				$this->response(wr_http_message::get200(), wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
	}	
}

?>
