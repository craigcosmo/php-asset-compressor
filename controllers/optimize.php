<?php

class Optimize extends Controller
{
	function index()
	{
		$this->asset->version();
		$this->asset->css();
		$this->asset->js();
		$this->asset->html();
		$this->asset->img();
	}
}
