<?php

class wr_room_join_user extends CI_Model {
	
	var $id = '';	
	var $room_id = '';
	var $user_id = '';
	var $reg_date = '';

	function join()
	{
		$this->db->where('id',$this->room_id);
		$ado = $this->db->get('wr_room');
		if($ado->num_rows() == 0)
		{
			throw new Exception('room_not_exist');
		}
		$data =
			array('room_id'=>$this->room_id,
					'join_user_id'=>$this->user_id,
					'reg_date'=>date('YmdHis'));
		
		$this->db->insert('wr_room_join_user',$data);

		return true;
	}


	function un_join()
	{
		$this->db->where('room_id',$this->room_id);
		$this->db->where('join_user_id',$this->user_id);
		$this->db->from('wr_room_join_user');
		$this->db->delete();
		return true;
	}


	function isJoinRoom()
	{
		$this->db->where('room_id',$this->room_id);
		$this->db->where('join_user_id',$this->user_id);
		$ado = $this->db->get('wr_room_join_user');
		if($ado->num_rows() == 0)
		{
			return false;
		}
		return true;
	}

	function getJoinCnt()
	{
		$today = date('Y:m:d 00:00:00',strtotime($this->reg_date));
		$tomorrow = date('Y:m:d 00:00:00',strtotime($this->reg_date)+60*60*24);
		//$this->db->where('wr_room_join_user.reg_date >=',$today);
		//$this->db->where('wr_room_join_user.reg_date <=',$tomorrow);
		$this->db->where('room_id',$this->room_id);
		$ado = $this->db->get('wr_room_join_user');
		return (string)$ado->num_rows();
	}

	function getAllJoinCnt()
	{
		$this->db->where('room_id',$this->room_id);
		$ado = $this->db->get('wr_room_join_user');
		return $ado->num_rows();
	}
function getJoinUsers() {
		$this->db->select('wr_user.id,wr_user.gcm_id');
		$this->db->from('wr_room_join_user LEFT JOIN wr_user ON
				wr_room_join_user.join_user_id = wr_user.id');
		$this->db->where('room_id',$this->room_id);
		$this->db->where('wr_user.id !=',$this->user_id);
		$ado = $this->db->get();
		if($ado->num_rows() == 0) return false;
		return $ado->result();
	}
}

?>
