<?php
if (!defined('BASEPATH')) exit ('No direct script access allowed');

class MY_Loader extends CI_Loader 
{
	function MY_Loader()
	{
		parent::CI_Loader();
		$CI =& get_instance();
		if($CI->config->item('enable_compress')){
			$this->_ci_view_path = FCPATH.'service/application/views/';
		}else{
			$this->_ci_view_path = APPPATH.'views/';
		}
	}
}