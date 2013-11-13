<?php

class wr_category extends CI_Model {
	var $id = '';
	var $name = '';
	var $reg_date = '';
	var $category_id = '';
	var $page = '';
	var $order_id = '';
	
	function __construct()
	{
		parent::__construct();
	}
	
	function getList()
	{
		/*
		if($this->redis->get('WR_CATEGORY_LIST') != null)
		{
			return unserialize($this->redis->get('WR_CATEGORY_LIST'));
		}
		*/
		$query = $this->db->get('wr_category');
		$result = array();
		if($query->num_rows() == 0)
		{
			return $result;
		}
		foreach ($query->result() as $row)
		{
			$stdObj = new stdClass();
			$stdObj->categoryId = $row->id;
			$stdObj->categoryName = $row->name;
			$stdObj->categoryDescription = $row->desc;
			array_push($result, $stdObj);
		}
		/*
		if($this->redis->get('WR_CATEGORY_LIST') == null)
		{
			$this->redis->set('WR_CATEGORY_LIST',serialize($result),86400);
		}
		*/
		return $result;
	}
	
	function recordCount()
	{
		$today = date('Y-m-d');  
		$this->db->where('category_id',$this->category_id);
		$this->db->where('start_date <= ',$today);
		$this->db->where('end_date >= ',$today);
		return $this->db->count_all_results('wr_room');
	}
	
	function getRoomList()
	{
		$this->page = $this->page*10 - 10;
		$today = date('Y-m-d');  
		$this->db->limit(10,$this->page);
		$this->db->where('category_id',$this->category_id);
		$this->db->where('start_date <= ',$today);
		$this->db->where('end_date >= ',$today);
		if($this->order_id == 1)
		{
			$this->db->order_by('id desc');
		}
		else if($this->order_id == 2)
		{
			$this->db->order_by('join_cnt desc');
			$this->db->order_by('checked_cnt desc');
			$this->db->order_by('id desc');
		}
		$ado = $this->db->get('wr_room');
		if($ado->num_rows() == 0)
		{
			return array();
		}
		$result = $ado->result();
		rsort($result);
		$ary = array();
		foreach($result as $obj)
		{
			$stdObj = new stdClass();
			$this->wr_room_join_user->setData(array('room_id'=>$obj->id));
			$stdObj->roomId = $obj->id;
			$stdObj->roomImagePath = $obj->image;
			$stdObj->roomTitle = $obj->name;
			$stdObj->roomPurpose = $obj->content;
			$stdObj->startDate = $obj->start_date;
			$stdObj->endDate = $obj->end_date;
			$stdObj->maxMemberCount = $obj->max_cnt;
			$stdObj->curMemberCount = $this->wr_room_join_user->getAllJoinCnt();
			$ary[] = $stdObj;
		}
		return $ary;
	}

	function getRoomCnt()
	{
		$today = date('Y-m-d');  
		$this->db->select('category_id AS categoryId , COUNT(*) AS roomCount');
		$this->db->from('wr_room');
		$this->db->where('start_date <= ',$today);
		$this->db->where('end_date >= ',$today);
		$this->db->group_by('wr_room.category_id');
		$ado = $this->db->get();
		if($ado->num_rows() == 0)
		{
			$result = array();
			$query = $this->db->get('wr_category');
			foreach ($query->result() as $row)
			{
				$stdObj = new stdClass();
				$stdObj->categoryId = $row->id;
				$stdObj->roomCount = (string)0;
				array_push($result, $stdObj);
			}
			return $result;
		}
		$categoryCnt = $ado->result();
		$this->db->select('id as categoryId');
		$this->db->from('wr_category');
		$ado = $this->db->get();
		$categoryList = $ado->result();
		$result = array();
		function filter($item,$key,$categoryCnt)
		{
			$item->roomCount = (string)0;
			foreach($categoryCnt as $ele)
			{
				if($ele->categoryId == $item->categoryId)
				{
					$item->roomCount = $ele->roomCount;
				}
			}
		}
		array_walk($categoryList,'filter',$categoryCnt);
		return $categoryList;
	}
}

?>
