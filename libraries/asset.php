<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Asset {
	
	var $service_folder = 'service/';
	var $img_folder = 'img/';
	var $css_folder = 'css/';
	var $js_folder = 'js/';
	var $html_folder = 'application/views';
	var $main_css = 'main.css';
	var $main_js = 'main.js';
	var $sprite = 'sprite.png';
	var $version = 'version.js';
	
	function css()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->load->library('cssmin');
		
		$cssfiles = glob("css/*.css");
		$css = "";
		foreach($cssfiles as $cssfile) {
		$css .= file_get_contents($cssfile);
		}
		$css = $CI->cssmin->minify($css);
		$css = trim($css);
		write_file($this->service_folder.$this->main_css, $css);

	}
	function js()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->load->library('jsmin');
				
		$jsfiles = glob("js/*.js");
		$js = "";
		foreach($jsfiles as $jsfile) {
			$js .= file_get_contents($jsfile);
		}
		$js = $CI->jsmin->minify($js);
		$js = trim($js);
		write_file($this->service_folder.$this->main_js, $js);	
	
	}
	function html(){
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->load->library('jsmin');
		$htmlfiles = glob('application/views/*.php');
		foreach($htmlfiles as $htmlfile) {			
			$str = file_get_contents($htmlfile);
			// minify js in html also remove js comment
			$str=$CI->jsmin->minify($str);
			// remove html comment
			$str = preg_replace('/<!--[^\[](.*?)-->/', '', $str);
			// remove minor space and newline
			$str = str_replace(array("\r\n", "\r", "\n", "\t"), "", $str);
			$str= str_replace(array("  ", "   "), " ", $str);
			
			write_file($this->service_folder.basename($htmlfile), $str);	
		}
	}
	function img()
	{
		$CI =& get_instance();
		$CI->load->helper('file');	
		//loop through all standard css files
		$cssfiles = glob("css/*.css");
		$css = "";
		foreach($cssfiles as $cssfile) {
			$css .= file_get_contents($cssfile);
		}

		$imgs = array();
		// extract urls from the css and put into var $imgs
		$re = '/url\(\s*[\'"]?(\S*\.(?:jpe?g|gif|png))[\'"]?\s*\)[^;}]*?no-repeat/i';
		if (preg_match_all($re, $css, $matches)) {
			$imgs = $matches[1];
			//print_r($imgs);
			$canvasWidth= 0;
			$canvasHeight = 0;
			
			//remove dubplicate url, if duplicated our sprite will have duplicated images
			$imgs = array_unique($imgs);
			//reset array key
			$imgs = array_merge($imgs);
			
			// loop thorught urls, get the images size to determine our canvas size
			foreach($imgs as $val)
			{
				$info = getimagesize($this->img_folder.basename($val));
				$width=$info[0];
				$height=$info[1];
				$canvasHeight +=$height;
				if($canvasWidth < $width){
					$canvasWidth = $width;
				}
			}
 
			// create our canvas
			$img = imagecreatetruecolor($canvasWidth, $canvasHeight);
			$background = imagecolorallocatealpha($img, 255, 255, 255, 127);
			imagefill($img, 0, 0, $background);
			imagealphablending($img, false);
			imagesavealpha($img, true);
 
			// start placing images to the canvas from top down.
			$pos = 0;
			$array = array();
						
			foreach($imgs as $key=>$val){
				
				$array['url('.$val.') no-repeat'] = array('x'=>0,'y'=>'-'.$pos);
				$tmp = imagecreatefrompng($this->img_folder.basename($val));
				$w=imagesx($tmp);
				$h=imagesy($tmp);
				imagecopy($img, $tmp, 0, $pos, 0, 0, $w, $h);
				$pos += $h;
				imagedestroy($tmp);
			}
  			print_r($array);

			// create our final sprite image.
			imagepng($img, $this->service_folder.'sprite.png');
			
			//get the compressed css file
			$css = file_get_contents($this->service_folder.$this->main_css);
			//replace normal urls with sprite.png and add positions 
			foreach($array as $key=>$val){
				$css = str_replace($key,'url(/service/sprite.png) no-repeat '.$val['x'].' '.$val['y'].'px',$css);
			}
			// finally write the compressed css back to server folder
			write_file($this->service_folder.$this->main_css, $css);
			//echo $css;
		}
	}
	function css_link()
	{
		$CI =& get_instance();
		if($CI->config->item('enable_compress')){
			$v = (file_exists($this->service_folder.$this->version)) ? $this->_get_version($this->service_folder.$this->version) :'';
			$v = '?v='.$v;
			$output = '<link type="text/css" rel="stylesheet" href="/'.$this->service_folder.$this->main_css.$v.'" />';
			return $output;
		}else{
			$cssfiles = glob("css/*.css");
			$output='';
			foreach($cssfiles as $cssfile) {
				$output.='<link type="text/css" rel="stylesheet" href="/'.$cssfile.'" />';
			}
			return $output;
		}
	}
	function js_link()
	{
		$CI =& get_instance();
		if($CI->config->item('enable_compress')){
			$v = (file_exists($this->service_folder.$this->version)) ? $this->_get_version($this->service_folder.$this->version) :'';
			$v = '?v='.$v;
			$output='<script src="/'.$this->service_folder.$this->main_js.$v.'"></script>';
			return $output;
		}else{
			$jsfiles = glob("js/*.js");
			$output='';
			foreach($jsfiles as $jsfile) {
				$output.='<script src="/'.$jsfile.'"></script>';
			}
			return $output;
		}
	}
	function version()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		if(!file_exists($this->service_folder.$this->version)){
			$file = json_encode(array('v'=>1));
			write_file($this->service_folder.$this->version, $file);
		}else{
			$file = file_get_contents($this->service_folder.$this->version);	
			$file = json_decode($file, true);
			$file['v']=$file['v']+1;
			// print_r( $file['v']);
			$file = json_encode($file);
			write_file($this->service_folder.$this->version, $file);
		}
	}
	function _get_version($location)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$file = file_get_contents($location);	
		$file = json_decode($file, true);
		return $file['v'];
	}
	
}

?>