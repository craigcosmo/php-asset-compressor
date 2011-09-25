<?php

class Optimize extends CI_Controller
{
	function index()
	{
		$this->asset->compress_css('css/','service/');
		$this->asset->compress_js('js/','service/');
		$this->asset->compress_img('img/', 'service/img/sprite.png', 'service/css/css.css');
	}

}
