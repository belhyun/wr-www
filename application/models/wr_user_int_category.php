<?php

class wr_user_int_category extends CI_Model {

	var $id = '';
	var $userId = '';
	var $regDate = '';
	var $catId = '';

	function __construct()
	{
		parent::__construct();
	}
	
	function insert()
	{
		$this->db->where('cat_id',$this->catId);
		$this->db->where('user_id',$this->userId);
		$query = $this->db->get('wr_user_int_category');
		if($query->num_rows() > 0)
		{
			throw new Exception(wr_http_message::DUPLICATE_500, wr_http_message::ERROR_500);
		}
		
		$data = array(
			'user_id' => $this->userId,
			'cat_id' => $this->catId,
			'reg_date' => date('ymdhis')
		);
		$this->db->insert('wr_user_int_category', $data);
		
		return true;
	}

	function update()
	{
		$data = array(
			'user_id' => $this->userId,
			'cat_id' => $this->catId,
			'reg_date' => date('ymdhis')
		);
		$this->db->where('user_id',$this->userId);
		$this->db->update('wr_user_int_category', $data);
		return true;
	}
}

?>
