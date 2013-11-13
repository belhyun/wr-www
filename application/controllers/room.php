<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('base_controller.php');
require_once(getcwd().'/application/libraries/GoogleCloudMsg.php');

class room extends base_controller 
{
	function create_post()
	{
		$catId = $this->input->post('category_id');
		$title = $this->input->post('check_room_title');
		$isPublic = $this->input->post('publish_level');
		$sDate = $this->input->post('start_date');
		$eDate = $this->input->post('end_date');
		$periodType = $this->input->post('period_type');
		$id = $this->input->post('user_id');
		$maxCnt = $this->input->post('max_member'); 
		$content = $this->input->post('content');
		$alarmYn = $this->input->post('alarm_yn');
		$alarmTime = $this->input->post('alarm_time');

		$this->wr_room->setData(array('name'=>$title,'public_yn'=>$isPublic
					,'start_date'=>date($sDate),'end_date'=>date($eDate),'period_type'=>$periodType,
					'category_id'=>$catId,'manager_id'=>$id,'max_cnt'=>$maxCnt,'content'=>$content,'alarm_yn'=>$alarmYn,'alarm_time'=>$alarmTime));
		if(!$this->isLogged($this->accToken,$id))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
				
		try{
			$roomId = $this->wr_room->create();
			$this->wr_room_join_user->setData(array('room_id'=>$roomId,'user_id'=>$id));
			$this->wr_room_join_user->join();
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}
		$msgAry = wr_http_message::get200();
		$msgAry['roomId'] = $roomId; 
		$this->response($msgAry, wr_http_message::SUCCESS_200);
	}

	function get_post()
	{
		$roomId = $this->input->post('room_id');
		$userId = $this->input->post('user_id');
		if($roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken,$userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}

		try{
			$this->wr_room->setData(array('id'=>$roomId));
			$result = $this->wr_room->getRoomInfo();
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}
		$msgAry = wr_http_message::get200();
		$msgAry['roomInfo'] = $result; 
		$this->response($msgAry, wr_http_message::SUCCESS_200);
	}

	function update_post()
	{
		$catId = $this->input->post('category_id');
		$title = $this->input->post('check_room_title');
		$isPublic = $this->input->post('publish_level');
		$sDate = $this->input->post('start_date');
		$eDate = $this->input->post('end_date');
		$periodType = $this->input->post('period_type');
		$id = $this->input->post('user_id');
		$maxCnt = $this->input->post('max_member'); 
		$content = $this->input->post('content');
		$room_id = $this->input->post('room_id');
		$alarmTime = $this->input->post('alarm_time');
		$alarmYn = $this->input->post('alarm_yn');
		$gcm = new wr_gcm();

		$this->wr_room->setData(array('name'=>$title,'public_yn'=>$isPublic
		,'start_date'=>date($sDate),'end_date'=>date($eDate),'period_type'=>$periodType,
		'category_id'=>$catId,'manager_id'=>$id,'max_cnt'=>$maxCnt,'content'=>$content,'id'=>$room_id,'alarm_time'=>$alarmTime,'alarm_yn'=>$alarmYn));
		$this->wr_room_join_user->setData(array('room_id'=>$room_id,
							'user_id'=>$id));
				
		if(!$this->isLogged($this->accToken,$id))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
				
		try{
			if($this->wr_room->update())
			{
				$joinUsers = $this->wr_room_join_user->getJoinUsers();
				if($joinUsers)
				{
					$roomInfo = $this->wr_room->getRoomInfo();
					$data =
						array('pushCode'=>wr_gcm_code::ROOM_ALARM_CHANGE,'roomId'=>$roomInfo->roomId,'roomName'=>$roomInfo->roomTitle,'roomPurpose'=>$roomInfo->roomPurpose,'alarmLevel'=>$roomInfo->alarmLevel,'alarmTime'=>$roomInfo->alarmTime);
					$reqIds = array();
					foreach($joinUsers as $user)
					{
						if(!empty($user->gcm_id))
							array_push($reqIds,$user->gcm_id);
					}
					$gcm->send($data,$reqIds);
				}
				$msgAry = wr_http_message::get200();
				$this->response(wr_http_message::get200(), wr_http_message::SUCCESS_200);
			}
			else
			{
				$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
	}

	function check_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');

		if($userId == null || $roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken,$userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}

		try{
			$this->wr_room_chk_user->setData(array('user_id'=>$userId,'room_id'=>$roomId));
			$this->wr_room->setData(array('id'=>$roomId,'user_id'=>$userId));
			$this->wr_room_chk_user->insert();
			//$roomInfo = $this->wr_room->update_checked_cnt();
			$msgAry = wr_http_message::get200();
			/*
				 if(!$roomInfo)
				 {
				 $this->response(wr_http_message::get400('not_exist_room'),
				 wr_http_message::ERROR_400);
				 }
			 */
			$msgAry['checkedMemberCount'] =$this->wr_room_chk_user->getCheckedMemberCnt();
			/*
				 if (getTemporaryCredentials()) {
			// We obtained temporary credentials, now redirect the user to evernote.com to authorize access
			var_dump(getAuthorizationUrl());
			}
			 */
			$this->response($msgAry, wr_http_message::SUCCESS_200);				
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0), wr_http_message::SUCCESS_200);				
		}
	}
	
	function check_cancel_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');

		if($userId == null || $roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken,$userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}

		try{
			$this->wr_room_chk_user->setData(array('user_id'=>$userId,'room_id'=>$roomId));
			$this->wr_room->setData(array('id'=>$roomId,'user_id'=>$userId));
			if($this->wr_room_chk_user->delete())
			{
				$checkCnt = $this->wr_room_chk_user->getCheckedMemberCnt();
				$msgAry = wr_http_message::get200();
				if($checkCnt-1 < 0)
				{
					$checkCnt = 0;
				}
				$msgAry['checkedMemberCount'] = $checkCnt;
				$this->response($msgAry, wr_http_message::SUCCESS_200);				
			}
			else
			{
				$this->response(wr_http_message::get400('invalid_request'),
						wr_http_message::ERROR_400);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),wr_http_message::SUCCESS_200);
		}
	}

	function with_list_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');
		//$sDate = $this->input->post('s_date');
		//$eDate = $this->input->post('e_date');

		if($roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
		/*
		if(empty($sDate))
		{
			$sDate = date('Ymd');
		}
		*/
		try{
			$this->wr_room->setData(array('user_id'=>$userId,'id'=>$roomId
						/*'start_date'=>$sDate,'end_date'=>$eDate*/));
			$roomWith = $this->wr_room->check_room_with();
			$msgAry = wr_http_message::get200();
			$msgAry['checkRoomWith'] = $roomWith;
			$this->response($msgAry, wr_http_message::SUCCESS_200);
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage(),0),
					wr_http_message::SUCCESS_200);
		}
	}

	function chked_mem_by_date_post()
	{
		$roomId = $this->input->post('room_id');
		$date = $this->input->post('date');

		if($roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		try{
			$this->wr_room->setData(array('id'=>$roomId,
						'reg_date'=>$date));
			$this->wr_room_join_user->setData(array('room_id'=>$roomId,'reg_date'=>$date));
			$memList = $this->wr_room->chkedMemByDate();
			$msgAry = wr_http_message::get200();
			$subResult = array();
			$subResult['checkedMembers'] = $memList;
			$joinCnt = $this->wr_room_join_user->getJoinCnt();
			if(!$joinCnt)
			{
				$joinCnt = 0;
			}
			$subResult['curMemberCount'] = (string)$joinCnt;
			$subResult['maxMemberCount'] = $this->wr_room->select()->max_cnt;
			$msgAry['checkRoomWith'] = $subResult;
			$this->response($msgAry, wr_http_message::SUCCESS_200);
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}
	}
		
	function my_room_post()
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
			$this->wr_room->setData(array('user_id'=>$userId));
			$msgAry = wr_http_message::get200();
			$result = $this->wr_room->getJoinRoom();
			if(count($result->roomList) > 0)
			{
				$msgAry['createRoomCount'] = $result->createRoomCount;
				$msgAry['joinRoomCount'] = $result->joinRoomCount;
				$msgAry['userStarCount'] = $result->userStarCount;
				$msgAry['roomList'] = $result->roomList;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
			else
			{
				$msgAry['createRoomCount'] = $result->createRoomCount;
				$msgAry['joinRoomCount'] = $result->joinRoomCount;
				$msgAry['userStarCount'] = $result->userStarCount;
				$msgAry['roomList'] = $result->roomList;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get200($ex->getMessage()), wr_http_message::SUCCESS_200);
		}
	}

	function room_delete_post()
	{
		$userId = $this->input->post('user_id');
		$roomId = $this->input->post('room_id');

		if($userId == null || $roomId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}
	
		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}
		try{
			$this->wr_room->setData(array('id'=>$roomId,'user_id'=>$userId));
			if($this->wr_room->delete())
			{
				$msgAry = wr_http_message::get200();
				$msgAry['roomId'] = $roomId;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}catch(Exception $ex){
			$this->response(wr_http_message::get500($ex->getMessage()), wr_http_message::ERROR_500);
		}
	}

	function article_rly_post()
	{
		$userId = $this->input->post('user_id');
		$noticeId = $this->input->post('notice_id');

		if($userId == null || $noticeId == null)
		{
			$this->response(wr_http_message::get400(), 400);
		}

		if(!$this->isLogged($this->accToken, $userId))
		{
			$this->response(wr_http_message::get401("not_valid_access_token"), wr_http_message::ERROR_401);
		}

		try{
			$this->wr_notice_rly->setData(array('user_id'=>$userId,'notice_id'=>$noticeId));
			if($rlyList = $this->wr_notice_rly->getRly())
			{
				$msgAry = wr_http_message::get200();
				$msgAry['messageList'] = $rlyList;
				$this->response($msgAry, wr_http_message::SUCCESS_200);
			}
		}catch(Exception $ex){
			$this->response(wr_http_message::get500(), wr_http_message::ERROR_500);
		}

	}
}

?>
