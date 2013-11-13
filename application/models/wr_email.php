<?php

class wr_email extends CI_Model {
	
	var $email = '';

	function insert()
	{
		$data = array('email' => $this->email);
		$this->db->insert('wr_email',$data);
	}
}
