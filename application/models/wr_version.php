<?php

class wr_version extends CI_Model {
	function version()
	{
		$version = new stdClass();
		$version->majorVersion = 1.0;
		$version->minorVersion = 0.0;
		return $version;
	}
}		
?>
