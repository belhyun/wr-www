<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');
class login extends base_controller 
{
	function index_post()
	{
		$id = $this->input->post('user_id');
		$pwd = $this->input->post('user_pwd');
		$gcmId = $this->input->post('gcm_id');
		if($id == null || $pwd == null)
		{
			$this->response(wr_http_message::get400('bad_request'), 400);
		}
		$this->wr_user->setData(array('wr_email'=>$id,'pwd'=>$pwd,'gcm_id'=>$gcmId));
		try{
			$result = $this->wr_user->login();
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
		$msgAry = wr_http_message::get200();
		$msgAry['token'] = $result->acc_token;
		$user = new stdClass();
		$user->userIndex = $result->id;
		$user->nickName = $result->name;
		$user->myCategoris = $result->int_cat;
		$user->purpose = $result->stu_msg;
		$user->isRoomTimeNotice = (bool)$result->room_time_yn;
		$user->isRoomManagerNotice = (bool)$result->manager_notice_yn;
		$user->isReplyNotice = (bool)$result->re_rly_notice_yn; 
		$user->isWitherestNotice = (bool)$result->sys_notice_yn;
		$user->profileImagePath = $result->image;
		$user->isEvernote = (bool)$result->evernote_yn;
		$msgAry['user'] = $user; 
		$this->response($msgAry, wr_http_message::SUCCESS_200);
	}
}
?>
