<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');
class User extends base_controller 
{
	function check_post()
	{
		$id = $this->input->post('user_id');
		if($id == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		$this->wr_user->setData(array('wr_email'=>$id));
		try{
			$msgAry = wr_http_message::get200();
			if($this->wr_user->isDupl())
			{
				$msgAry['isDuplication'] = true;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
			else
			{
				$msgAry['isDuplication'] = false;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
	}

	function test_post()
	{
		log_message('debug','test');
	}

	function update_post()
	{
		$name = $this->input->post('name');
		$stuMsg = $this->input->post('stu_msg');
		$intCat = $this->input->post('int_cat');
		$email = $this->input->post('email');
		$userId = $this->input->post('user_id');
		if($name == null || $intCat == null ||
				$email == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken,$userId))
		{
			$this->response(wr_http_message::get401('not_authorized'), wr_http_message::ERROR_401);
		}
		try{
			$this->wr_user->setData(array('id'=>$userId,
						'name'=>$name,'wr_email'=>$email,'stu_msg'=>$stuMsg,'cat_id'=>$intCat));
			//$this->wr_user_int_category->setData(array('userId'=>$userId,'catId'=>$intCat));
			$this->wr_user->update();
			//$this->wr_user_int_category->update();
			$this->response(wr_http_message::get200(), wr_http_message::SUCCESS_200);
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
	}

	function update_alarm_post()
	{
		$alarm = $this->input->post('alarm');
		$userId = $this->input->post('user_id');

		if($alarm == null || $userId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		
		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
		try{
			$list = explode('|',$alarm);
			$this->wr_user->setData(array('id'=>$userId,
						'manager_notice_yn'=>$list[0],
						'room_time_yn'=>$list[1],
						're_rly_notice_yn'=>$list[2],
						'sys_notice_yn'=>$list[3],
						'evernote_yn' => $list[4]));
			if($this->wr_user->update_alarm())
			{
				$msgAry = wr_http_message::get200();
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}
	}

	function join_room_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');

		if($userId == null || $roomId== null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
		try{
			$this->wr_room_join_user->setData(array('user_id'=>$userId,'room_id'=>$roomId));
			$this->wr_room->setData(array('id'=>$roomId));
			if($this->wr_room_join_user->join())
			{
				//$this->wr_room->updateJoinCnt();
				$msgAry = wr_http_message::get200();
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
	}

	function un_join_room_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');

		if($userId == null || $roomId== null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
		try{
			$this->wr_room_join_user->setData(array('user_id'=>$userId,'room_id'=>$roomId));
			$this->wr_room->setData(array('id'=>$roomId));
			if($this->wr_room->isRoomExist() != 0 && $this->wr_room_join_user->un_join())
			{
				//$this->wr_room->updateJoinCnt();
				$msgAry = wr_http_message::get200();
				$msgAry['roomId'] = $roomId;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
			else
			{
				throw new Exception('room_not_exist');
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}

	}

	function nick_dupl_chk_post()
	{
		$name = $this->input->post('nick');
		if($name == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		$this->wr_user->setData(array('name'=>$name));
		try{
			$msgAry = wr_http_message::get200();
			if($this->wr_user->isDuplNick($name))
			{
				$msgAry['isDuplication'] = true;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
			else
			{
				$msgAry['isDuplication'] = false;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			if($ex->getCode() == wr_http_message::ERROR_500)
			{
				$this->response(wr_http_message::get500($ex->getMessage()), wr_http_message::ERROR_500);
			}
		}
	}

	function un_register_post()
	{
		$userId = $this->input->post('user_id');

		if($userId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
		try{
			$this->wr_user->setData(array('id'=>$userId));
			if($this->wr_user->unRegister())
			{
				$msgAry = wr_http_message::get200();
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}


	}
}
