<?php

class wr_http_message extends CI_Model
{
	const ERROR_400 = 400;
	const ERROR_500 = 500;
	const SUCCESS_200 = 200;
	const NOT_FOUND_404 = 404;
	const ERROR_401 = 401;
	const ERROR_304 = 304;
	const ERROR_403 = 403;
	const DUPLICATE_500 = 'already_user_exists';
	const USER_NOT_EXISTS_500 = 'user_not_exists';
	const INCORRECT_500 = 'incorrect_user_info';
	
	function __construct()
	{
		parent::__construct();
	}
	
	public static function get400($msg = 'bad_request')	
	{
		return array('resultCode'=>0, 'resultMsg'=>$msg);
	}
	
	public static function get500($msg='internal_server_error')	
	{
		return array('resultCode'=>0, 'resultMsg'=>$msg);
	}

	public static function get200($msg='success',$resultCode = 1)
	{
		return array('resultCode'=>$resultCode, 'resultMsg'=>$msg);
	}

	public static function get401($msg='unauthorized')
	{
		return array('resultCode'=>0, 'resultMsg'=>$msg);
	}

	public static function get304()
	{
		return array('resultCode'=>0, 'resultMsg'=>'not_modified');
	}
	public static function get404($msg='not_found') 
	{ 
		return array('resultCode'=>0, 'resultMsg'=>$msg); 
	}

	public static function get403($msg='not_acceptable')
	{
		return array('resultCode'=>0, 'resultMsg'=>$msg);
	}

}
?>
