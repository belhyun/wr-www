<?php

class wr_room extends CI_Model {
	
	var $id = '';
	var $name = '';
	var $image = '';
	var $join_cnt = '';
	var $manager_id = '';
	var $checked_cnt = '';
	var $max_cnt = '';
	var $category_id = '';
	var $content = '';
	var $period_type = '';
	var $alarm_yn = '';
	var $alarm_title = '';
	var $public_yn = '';
	var $reg_date = '';
	var $start_date = '';
	var $end_date = '';
	var $user_id = '';
	var $alarm_time = '';

	function __construct()
	{
		parent::__construct();
	}

	function create()
	{
		/*
		if(filesize($_FILES[$attr]['image']) > 1024*8)
		{
			return false;
		}
		*/
		$data = array(
				'name' => $this->name,
				'manager_id' => $this->manager_id,
				'max_cnt' => $this->max_cnt,
				'reg_date' => date('ymd'),
				'period_type' => $this->period_type,
				'alarm_yn' => $this->alarm_yn,
				'public_yn' => $this->public_yn,
				'start_date' => $this->start_date,
				'end_date' => $this->end_date,
				'content' => $this->content,
				'category_id' => $this->category_id,
				'alarm_yn' => $this->alarm_yn,
				'alarm_time' => $this->alarm_time
				);
		$this->db->insert('wr_room', $data);
		$id = $this->db->insert_id();
		if(!($image = $this->save_image('room_'.$id,'image')))
		{
			$image = '';
			return $id;
		}
		$this->db->set('image',$image);
		$this->db->where('id',$id);
		$this->db->update('wr_room', $data);
		return $id;
	}


	function delete()
	{
		$this->db->where('id',$this->id);
		$ado = $this->db->get('wr_room');
		if($ado->num_rows() == 0)
		{
			throw new Exception('room_not_exists');
		}
		$this->db->where('id', $this->id);
		$this->db->where('manager_id', $this->user_id);
		$this->db->from('wr_room');
		$this->db->delete();
		return true;
	}

	
	function update()
	{
		$this->db->where('id',$this->id);
		$ado = $this->db->get('wr_room');
		$result = $ado->result();
		$image = $this->save_image('room_'.$this->id,'image');
		if(!$image)
		{
			$this->db->select('image');
			$this->db->from('wr_room');
			$this->db->where('id',$this->id);
			$ado = $this->db->get();
			$result = reset($ado->result());
			if($result)
				$image = $result->image;
			else
				$image = '';
		}
		$data = array(
				'name' => $this->name,
				'manager_id' => $this->manager_id,
				'max_cnt' => $this->max_cnt,
				'reg_date' => date('ymd'),
				'period_type' => $this->period_type,
				'alarm_yn' => $this->alarm_yn,
				'public_yn' => $this->public_yn,
				'start_date' => $this->start_date,
				'end_date' => $this->end_date,
				'content' => $this->content,
				'category_id' => $this->category_id,
				'alarm_time' => $this->alarm_time,
				'alarm_yn' => $this->alarm_yn
				);
		if(!$image)
		{
			$data['image'] = $image;
		}
		$this->db->where('id',$this->id);
		$id = $this->db->update('wr_room', $data);
		return true;
	}
	function select()
	{
		$this->db->where('id',$this->id);
		return reset($this->db->get('wr_room')->result());
	}

	function getRoomInfo()
	{
		$this->db->where('id',$this->id);
		$result = reset($this->db->get('wr_room')->result());
		$stdObj = new stdClass();
		$stdObj->roomId= $result->id;
		$stdObj->roomTitle = $result->name;
		$stdObj->roomPurpose = $result->content;
		$stdObj->roomImagePath = $result->image;
		$stdObj->category = $result->category_id;
		$stdObj->startDate = $result->start_date;
		$stdObj->endDate = $result->end_date;
		$stdObj->periodType = $result->period_type;
		$stdObj->alarmLevel = $result->alarm_yn;
		$stdObj->alarmTime = $result->alarm_time;
		$stdObj->publicLevel = $result->public_yn;
		$stdObj->maxMemberCount = $result->max_cnt;

		return $stdObj;
	}

	function update_checked_cnt($isCancel = false)
	{
		$roomInfo = $this->select();
		if(!$roomInfo) return false;
		if($roomInfo->checked_cnt == 0 && $isCancel)
		{
			throw new Exception('checked_cnt_is_zero');
		}
		$sdate = strtotime($roomInfo->start_date);
		$edate = strtotime($roomInfo->end_date);
		$now = strtotime(date('YmdHis'));
		if($now >= $sdate && $now <= $edate)
		{
			$this->db->where('user_id',$this->user_id);
			$this->db->where('room_id',$this->id);
			$this->db->where('CURDATE() = reg_date');
			$ado = $this->db->get('wr_room_chk_user');
			if($ado->num_rows() == 0)
			{
				$this->db->where('id',$this->id);
				if($isCancel)
				{
					$this->db->set('checked_cnt','checked_cnt-1',false);
				}
				else
				{
					$this->db->set('checked_cnt','checked_cnt+1',false);
				}
				$this->db->update('wr_room');
				return $roomInfo;
			}
		}
		return $roomInfo;
	}

	function chkedMemByDate()
	{
		$today = date('Y:m:d 00:00:00',strtotime($this->reg_date));
		$tomorrow = date('Y:m:d 00:00:00',strtotime($this->reg_date)+60*60*24);
		$this->db->select('wr_room_chk_user.user_id AS checkedMemberId,wr_user.image AS checkedUserProfileImage');
		$this->db->from('wr_room_chk_user LEFT JOIN wr_user ON wr_room_chk_user.user_id = wr_user.id');
		$this->db->where('wr_room_chk_user.reg_date >=',$today);
		$this->db->where('wr_room_chk_user.reg_date <=',$tomorrow);
		$this->db->where('wr_room_chk_user.room_id',$this->id);
		$ado = $this->db->get();
		if($ado->num_rows() > 0)
		{
			return $ado->result();
		}
		return array();
	}
	
	function check_room_with()
	{
		$this->db->select('room.id as roomId, 
		room.name as roomTitle,
		room.content as roomPurpose,
		room.start_date as startDate,
		room.end_date as endDate,
		room.max_cnt as maxMemberCount,
		room.join_cnt as curMemberCount,
		room.manager_id as roomOwner,
		room.image as roomImagePath,
		room.public_yn as publicLevel,
		room.period_type as periodType,
		room.alarm_time as alarmTime,
		room.alarm_yn as alarmLevel');
		
		$this->db->from('wr_room as room');
		$this->db->where('room.id',$this->id);
		$ado = $this->db->get();
		$result = $ado->result();
		if($ado->num_rows() == 0) 
		{	
			return new stdClass();
		}
		else
		{
			$roomInfo = reset($result);
			$this->wr_room_chk_user->setData(array('room_id'=>$this->id));
			$this->wr_room_join_user->setData(array('room_id'=>$this->id,'user_id'=>$this->user_id,'reg_date'=>date('Y:m:d')));
			$roomInfo->curMemberCount = $this->wr_room_join_user->getJoinCnt();
			if(!$this->user_id)
			{
				$roomInfo->isJoined = false;
			}
			else
			{
				$roomInfo->isJoined = $this->wr_room_join_user->isJoinRoom();
			}

			if(($chkCnt = $this->wr_room_chk_user->getTodayChkCnt()) == 0)
			{
				$roomInfo->achievementRate = '0%';
			}
			else
			{
				if($roomInfo->curMemberCount== 0) $roomInfo->achievementRate = '0%';
				else
				{
					$roomInfo->achievementRate =
						((round($chkCnt/$roomInfo->curMemberCount,2))*100).'%';
				}
			}
			$today = date('Y:m:d 00:00:00');
			$tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));	
			$tomorrow = date('Y:m:d 00:00:00',$tomorrow);
			$this->db->select('wr_user.id AS checkedMemberId, wr_user.image AS checkedUserProfileImage');
			$this->db->from('wr_user LEFT JOIN wr_room_chk_user AS chk_user ON
					chk_user.user_id = wr_user.id');
			$this->db->where('chk_user.room_id',$this->id);
			$this->db->where('chk_user.reg_date >=',$today);
			$this->db->where('chk_user.reg_date <=',$tomorrow);
			$this->db->distinct();
			$roomInfo->checkedMembers = $this->db->get()->result();

			return $roomInfo;
		}
	}

	function getMadeByMeRoom()
	{
		$today = date('Y-m-d');
		$this->db->where('manager_id',$this->user_id);
		$this->db->where('end_date > ',$today);
		$ado = $this->db->get('wr_room');
		return $ado->result();
	}

	function getJoinRoom()
	{
		$today = date('Y-m-d');
		$roomList = array();
		$myCreateRoom = $this->getMadeByMeRoom();
		$createdRoomCnt = strval(count($myCreateRoom));
		$this->wr_user->setData(array('id'=>$this->user_id));
		$userInfo = reset($this->wr_user->getUserInfo());
		if(!$userInfo) 
			throw new Exception('user_not_exists');
		$this->db->select('wr_room.*');
		$this->db->from('wr_room 
				LEFT JOIN wr_room_join_user 
				ON wr_room_join_user.room_id = wr_room.id');
		$this->db->where('wr_room_join_user.join_user_id',(int)$this->user_id);
		$this->db->where('wr_room.end_date > ',$today);
		$this->db->where('wr_room.manager_id != wr_room_join_user.join_user_id');
		$ado = $this->db->get();
		$result = $ado->result();
		if($ado->num_rows() > 0 && $result[0]->id != null)
		{
			$stdObj = new StdClass();
			$stdObj->createRoomCount = $createdRoomCnt;
			$stdObj->joinRoomCount = (string)count($result);
			$stdObj->userStarCount = $userInfo->star_cnt;

			foreach($result as $list)
			{
				$this->wr_room_join_user->setData(array('room_id'=>$list->id));
				$room = new stdClass();
				$room->roomId = $list->id;
				$room->roomTitle = $list->name;
				if($list->image == '')
					$room->roomImagePath = null;
				$room->roomImagePath = $list->image;
				$room->roomPurpose = $list->content;
				$room->startDate = $list->start_date;
				$room->endDate = $list->end_date;
				$this->wr_room_chk_user->setData(array('room_id'=>$list->id));
				$room->checkedMemberCount = $this->wr_room_chk_user->getTodayChkCnt();
				$room->maxMemberCount = $list->max_cnt;
				$room->curMemberCount = $this->wr_room_join_user->getAllJoinCnt();
				$room->alarmTime = $list->alarm_time;
				$room->alarmLevel = $list->alarm_yn;
				if($list->manager_id == $this->user_id)
				{
					$room->roomOwner = $this->user_id;
				}
				$room->checked = $this->isTodayChecked($list->id);
				$roomList[] = $room;
			}
		}
		else
		{
			$stdObj = new stdClass();
			$stdObj->roomList = array();
			$stdObj->joinRoomCount = (string)count($result);
			$stdObj->userStarCount = $userInfo->star_cnt;
			$stdObj->createRoomCount = $createdRoomCnt;
		}
		if(!empty($myCreateRoom))
		{
			foreach($myCreateRoom as $list)
			{
				$this->wr_room_join_user->setData(array('room_id'=>$list->id));
				$room = new stdClass();
				$room->roomId = $list->id;
				$room->roomTitle = $list->name;
				$room->roomImagePath = $list->image;
				$room->startDate = $list->start_date;
				$room->endDate = $list->end_date;
				$this->wr_room_chk_user->setData(array('room_id'=>$list->id));
				$room->checkedMemberCount = $this->wr_room_chk_user->getTodayChkCnt();
				//$room->checkedMemberCount = $list->checked_cnt;
				$room->maxMemberCount = $list->max_cnt;
				$room->curMemberCount = $this->wr_room_join_user->getAllJoinCnt();
				$room->roomPurpose = $list->content;
				$room->alarmTime = $list->alarm_time;
				$room->alarmLevel = $list->alarm_yn;

				if($list->manager_id == $this->user_id)
				{
					$room->roomOwner = $this->user_id;
				}
				$room->checked = $this->isTodayChecked($list->id);
				$roomList[] = $room;
			}
		}
		$stdObj->roomList = $roomList;
		return $stdObj;
	}

	function isTodayChecked($roomId)
	{
		$this->db->where('room_id', $roomId);
		$this->db->where('user_id', $this->user_id);
		$this->db->where('reg_date >=',date('Y-m-d 00:00:00'));
		$ado  =$this->db->get('wr_room_chk_user');
		if($ado->num_rows() > 0)
		{
			return true;
		}

		return false;
	}

	function updateJoinCnt()
	{
		$this->db->where('id',$this->id);
		$this->db->set('join_cnt','join_cnt+1', false);
		$this->db->update('wr_room');
	}

	function getRoomManagerInfo()
	{
		$this->db->select('wr_room.content as roomManager, wr_user.name as
				roomManagerName, wr_room.manager_id as roomManagerId, wr_user.image as
				roomManageImagePath');
		$this->db->from('wr_room left join wr_user on wr_room.manager_id =
				wr_user.id');
		$this->db->where('wr_room.id',$this->id);

		$ado = $this->db->get();

		return current($ado->result());
	}

	function isRoomExist()
	{
		$this->db->where('id',$this->id);
		$ado = $this->db->get('wr_room');
		return $ado->num_rows();
	}
}
?>
