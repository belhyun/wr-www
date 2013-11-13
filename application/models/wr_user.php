<?php

class wr_user extends CI_Model {

	var $id = '';
	var $pwd = '';
	var $name = '';
	var $stu_msg = '';
	var $star_cnt = '';
	var $acc_token = '';
	var $gcm_id = '';
	var $wr_email = '';
	var $reg_date = '';
	var $upd_date = '';
	var $image = '';    
	var $cat_id = '';
	var $manager_notice_yn = '';
	var $room_time_yn = '';
	var $re_rly_notice_yn = '';
	var $sys_notice_yn = '';
	var $evernote_yn = '';

	function __construct()
	{
		parent::__construct();
	}
	
	function update_alarm()
	{
		$data = array(
				'manager_notice_yn'=>$this->manager_notice_yn,
				'room_time_yn'=>$this->room_time_yn,
				're_rly_notice_yn'=>$this->re_rly_notice_yn,
				'sys_notice_yn'=>$this->sys_notice_yn,
				'evernote_yn' => $this->evernote_yn
				);
		 $this->db->where('id',$this->id);
		 $this->db->update('wr_user',$data);
		 return true;
	}

	function logout()
	{
		$data = array(
			'acc_token' => ''
		);
		$this->db->where('id',$this->id);
		$this->db->update('wr_user',$data);
	}
	
	function isValidSession()
	{
		$this->db->where('acc_token',$this->acc_token);
		$this->db->where('id',$this->id);
		$ado = $this->db->get('wr_user');
		if($ado->num_rows() > 0)
		{
			return true;						
		}
		
		return false;
	}

	function isLogged($accToken, $userId)
	{
		$this->db->where('acc_token',$accToken);
		$this->db->where('id',$userId);
		$ado = $this->db->get('wr_user');
		if($ado->num_rows() > 0)
		{
			return true;						
		}
		
		return false;
	}

	function isDupl()
	{
		$this->db->where('email',$this->wr_email);
		if($this->db->get('wr_user')->num_rows() > 0)
		{
			return true;			
		}
		return false;
	}

	function isDuplNick($name)
	{
		$this->db->where('name',$this->name);
		if($this->db->get('wr_user')->num_rows() > 0)
		{
			return true;			
		}
		return false;
	}

	function login()
	{
		//$this->db->select('*, GROUP_CONCAT(t1.cat_id) as cat_list');
		//$this->db->where('t2.email',$this->wr_email);
		//$this->db->group_by('t2.id');
		//$ado = $this->db->get('wr_user_int_category AS t1 LEFT JOIN wr_user AS t2 ON t2.id = t1.user_id');
		$this->db->where('wr_user.email',$this->wr_email);
		$ado = $this->db->get('wr_user');
		$result = reset($ado->result());
		if($ado->num_rows() == 0)
		{
			throw new Exception('user_not_exists');
		}
		if($result->email != $this->wr_email || $result->pwd != $this->pwd)
		{
			throw new Exception('incorrect_information');
		}
		$token = sha1("wr_"+time());
		$data = array(
			'acc_token' => $token,
			'gcm_id' => $this->gcm_id
		);
		$result->acc_token = $data['acc_token'];
		$this->db->where('email',$this->wr_email);
		$this->db->update('wr_user',$data);
		return $result;
	}
	
	function join()
	{
		$this->db->where('email',$this->wr_email);
		$query = $this->db->get('wr_user');
		if($query->num_rows() > 0)
		{
			throw new Exception(wr_http_message::DUPLICATE_500, wr_http_message::ERROR_500);
		}
				
		$data = array(
			'name' => $this->name,
			'email' => $this->wr_email,
			'pwd' => $this->pwd,
			'acc_token' => uniqid("wr_"+time(),true)
		);

		$sql = 'CALL sp_join_user(?,?,?,?,?)';
		$params = array(
				'wr_name' => $this->name,
				'wr_email' => $this->wr_email,
				'wr_pwd' => $this->pwd,
				'wr_acc_token' => 	uniqid("wr_"+time(),true),
				'wr_cat_id' => $this->cat_id
		);
		$this->db->query($sql, $params);
		$this->db->select('MAX(id) as id');
		$this->db->from('wr_user');
		$ado = $this->db->get();
		$result = $ado->result();
		$data['user_id'] = $result[0]->id;
		return $data;
	}

	function update()
	{
		$image = $this->save_image('profile_'.$this->id,'image');
		log_message('debug','>>>>>image'.$image);
		if(!$image)
		{
			$this->db->select('image');
			$this->db->from('wr_user');
			$this->db->where('id',$this->id);
			$ado = $this->db->get();
			$result = reset($ado->result());
			$image = $result->image;
		}
		$data = array(
				'name' => $this->name,
				'email' => $this->wr_email,
				'stu_msg' => $this->stu_msg,
				'upd_date' => date('Ymd'),
				'int_cat' => $this->cat_id,
				'image' => $image
				);
		if(!$this->stu_msg || $this->stu_msg == null)
		{
			unset($data['stu_msg']);
		}
		$this->db->where('id',$this->id);
		$this->db->update('wr_user',$data);
		return true;
	}
	
	function updateAlarm()
	{
		$data = array(
				'manager_notice_yn' => $this->manager_notice_yn,
				'room_time_yn' => $this->room_time_yn,
				're_rly_notice_yn' => $this->re_rly_notice_yn,
				'upd_date' => date('Ymd'),
				'sys_notice_yn' => $this->sys_notice_yn
		);
		$this->db->where('id',$this->id);
		$this->db->update('wr_user',$data);
		return $data;
	}

	public function getUserInfo()
	{
		$this->db->where('id',$this->id);
		$ado = $this->db->get('wr_user');
		$result = $ado->result();
		if(empty($result))
		{
			return false;
		}
		return $result;
	}

	function unRegister()
	{
		$this->db->where('id',$this->id);
		$ado = $this->db->get('wr_user');
		if($ado->num_rows() == 0)
		{
			throw new Exception('user_not_exists');
		}
		$this->db->where('id',$this->id);
		$this->db->from('wr_user');
		$this->db->delete();
		return true;
	}

	function getAccToken($userId)
	{
		$this->db->where('id',$userId);
		$ado = $this->db->get('wr_user');
		if($ado->num_rows() == 0)
		{
			return false;
		}
		$result = reset($ado->result());
		return $result != null ? $result->acc_token : false;
	}
}
?>
