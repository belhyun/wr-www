<?php

class wr_notice extends CI_Model {
	
	var $room_id = '';
	var $user_id = '';
	var $content = '';
	var $page = '';
	
	function write()
	{
		$this->wr_room->setData(array('id'=>$this->room_id));
		$this->db->where('id',$this->room_id);
		$ado = $this->db->get('wr_room');
		$result = $ado->result();
		if(!$result)
		{
			throw new Exception('room_not_exists');
		}
		if(empty($result->owner_id) || $result->owner_id != $this->user_id)
		{
			throw new Exception('not_room_manager');
		}

		$this->db->where('room_id', $this->room_id);
		$this->db->where('user_id', $this->user_id);
		$ado = $this->db->get('wr_notice');
		$data = array(
				'room_id' => $this->room_id,
				'user_id' => $this->user_id,
				'content' => $this->content,
				'reg_date' => date('YmdHis')
				);		

		if($ado->num_rows() > 0)
		{
			$this->db->update('wr_notice', $data);
			return $this->db->affected_rows();
		}
		else
		{
			$this->db->insert('wr_notice',$data);
			return $this->db->insert_id();
		}
	}

	function select()
	{
		$this->db->where('room_id',$this->room_id);
		$ado  = $this->db->get('wr_notice');
		return reset($ado->result());
	}
}

?>
