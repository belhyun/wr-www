<?php

class wr_room_chk_user extends CI_Model {
	
	var $id = '';	
	var $room_id = '';
	var $user_id = '';
	var $reg_date = '';
	
	function __construct()
	{
		parent::__construct();
	}
	
	function insert()
	{
		if(!($this->wr_room->select()))
		{
			throw new Exception('check_room_not_exists');
		}
		$this->wr_room_join_user->setData(array('room_id'=>$this->room_id));
		$this->db->where('room_id',$this->room_id);
		$ado = $this->db->get('wr_room_join_user');
		if($ado->num_rows() == 0)
		{
			throw new Exception('not_join_user');
		}
		if($this->isTodayCheck())
		{
			throw new Exception('already_today_check');
			return false;
		}
		$data = array(
				'room_id' => $this->room_id,
				'user_id' => $this->user_id,
				'reg_date' => date('YmdHis')
		);
		$this->db->insert('wr_room_chk_user',$data);
		return true;
	}
	
	function delete()
	{
		$this->db->where('room_id', $this->room_id);
		$this->db->where('user_id', $this->user_id);
		$ado = $this->db->get('wr_room_chk_user');
		if($ado->num_rows() == 0) 
		{
			throw new Exception('check_data_not_exists');
		}
		$this->db->where('room_id', $this->room_id);
		$this->db->where('user_id', $this->user_id);
		$this->db->delete('wr_room_chk_user');
		return true;
	}

	function isTodayCheck()
	{
		$this->db->where('room_id', $this->room_id);
		$this->db->where('user_id', $this->user_id);
		$this->db->where('reg_date >=',date('Y-m-d 00:00:00'));
		$ado  =$this->db->get('wr_room_chk_user');
		if($ado->num_rows() > 0)
		{
			return true;
		}
		return false;
	}

	function getTodayChkCnt()
	{
		$this->db->where('room_id', $this->room_id);
		$this->db->where('reg_date >=', date('Y-m-d 00:00:00'));
		$ado = $this->db->get('wr_room_chk_user');
		return $ado->num_rows();
	}

	function getCheckedMemberCnt()
	{
		$this->db->from('wr_room_chk_user LEFT JOIN wr_room ON wr_room.id = wr_room_chk_user.room_id');
		$this->db->where('wr_room.end_date >',date('Y-m-d 00:00:00'));
		$this->db->where('room_id',$this->room_id);
		$ado = $this->db->get();
		return (string)$ado->num_rows();
	}
}

?>
