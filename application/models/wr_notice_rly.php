<?php

class wr_notice_rly extends CI_Model {
	var $room_id = '';
	var $writer_id = '';
	var $notice_id = '';
	var $content = '';
	
	function setData($data)
	{
		foreach(get_object_vars($this) as $k=>$v)
		{
			if(array_key_exists($k, $data))
			{
				$this->$k = $data[$k];
			}
		}
	}
	
	function write_rly()
	{
		$data = array(
			'room_id' => $this->room_id,
			'notice_id' => $this->notice_id,
			'content' => $this->content,
			'writer_id' => $this->writer_id,
			'reg_date' => date('YmdHis')
		);				
		$this->db->insert('wr_notice_rly',$data);
		return true;
	}	

	function getRly()
	{
		$ary = array();
		$this->db->select('rly.*, user.name, user.image');
		$this->db->from('wr_notice_rly AS rly LEFT JOIN wr_user AS user ON
				rly.writer_id = user.id');
		$this->db->where('rly.notice_id',$this->notice_id);
		$ado = $this->db->get('wr_notice_rly');
		$result = $ado->result();
		if($ado->num_rows() == 0)
		{
			return $ary;
		}
		foreach($result as $value)
		{
			$stdObj = new stdClass();
			$stdObj->writerName = $value->name;
			$stdObj->writeTime = date('Y-m-d-H:i',strtotime($value->reg_date));
			$stdObj->message = $value->content;
			$stdObj->isReply = false;
			$stdObj->writerImagePath = $value->image;
			$ary[] = $stdObj;
		}
		return $ary;
	}
}
?>
