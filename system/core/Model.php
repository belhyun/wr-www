<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Model {
	/**
    * @var CI_Config
    */
    private $config;
    /**
    * @var CI_DB_active_record
    */
    private $db;
    /**
    * @var CI_Email
    */
		private $email;
    /**
    * @var CI_Form_validation
    */
    private $form_validation;
    /**
     * @var CI_Input
    */
    private $input;
    /**
    * @var CI_Loader
    */
    private $load;
    /**
    * @var CI_Router
    */
    private $router;
    /**
    * @var CI_Session
    */
    private $session;
    /**
    * @var CI_Table
    */
    private $table;
    /**
    * @var CI_Unit_test
    */
    private $unit;
    /**
    * @var CI_URI
    */
    private $uri;
    /**
    * @var CI_Pagination
    */
    private $pagination;
	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		log_message('debug', "Model Class Initialized");
	}

	/**
	 * __get
	 *
	 * Allows models to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param	string
	 * @access private
	 */
	function __get($key)
	{
		$CI =& get_instance();
		return $CI->$key;
	}
	
	function isLogged($token)
	{
		$this->db->select('acc_token');
		$this->db->where('id',$this->id);
		$ado = $this->db->get('wr_user');
		if($ado->result() == $token)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function save_image($id,$attr='image')
	{
		if(empty($_FILES[$attr]['name']))
		{
			return false;
		}

		//log_message('debug','image name'.$id);
		$ext = end(explode(".", $_FILES[$attr]['name']));
		$dir = getcwd().'/assets/images/'.$id.'/';
		if(!is_dir($dir))
		{
			mkdir($dir,0777);
		}
		if(empty($_FILES))
		{
			return false;
		}
		$config['overwrite'] = true;
		$config['upload_path'] = $dir;
		$config['allowed_types'] = 'gif|jpg|png|doc|txt';
		//$config['max_size']  = 1024 * 8;
		//$config['remove_spaces'] = true;
		$config['max_width'] = 800;
		$config['max_height'] = 600;
		$config['file_name'] = 'original';
		$this->upload->initialize($config);
		if($this->upload->do_upload($attr))
		{
			$config = array();
			$config['overwrite'] = true;
			$config['image_library'] = 'gd2';
			$config['source_image'] = $dir.'original'.'.'.$ext;
			$config['maintain_ratio'] = true;
			$config['new_image'] = $dir.'small.'.$ext;
			$config['width'] = 160;
			$config['height'] = 160;
			$this->image_lib->initialize($config);
			$this->image_lib->resize();
		}
		return '/assets/images/'."{$id}/original.{$ext}";
	}

	function setData($data)
	{
		$thisClass = new ReflectionClass(get_called_class());
		$props = $thisClass->getProperties();
		foreach($props as $prop)
		{
			$k = $prop->name;
			if(array_key_exists($k, $data))
			{
				$this->$k = $data[$k];
			}
		}
	}
}
// END Model Class

/* End of file Model.php */
/* Location: ./system/core/Model.php */
