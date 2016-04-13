<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Asset {
		
	function compress_css($dir, $dest, $exclusion=null)
	{
		$CI =& get_instance();
		$CI->load->library('cssmin');
		//create destination folder is not exsited
		$this->_create_folder($dest);

		foreach ($this->_count($dir) as $filename) {
			$css = file_get_contents($filename);
			// $css = $CI->cssmin->minify($css);
			// $css = preg_replace('!\s+!', ' ', $css);
			// $css = str_replace(array("\r\n", "\r", "\n", "\t"), "", $css);
			// $css = trim($css);

			$css = $this->compress($css);

			$newpath = str_replace($dir, $dest, $filename);
			$new_directory = dirname($newpath);
			$this->_create_folder($new_directory);
			$this->_write_file($newpath, $css);
		}
		echo 'done compressing css';	
	}

	function compress_js($dir, $dest, $exclusion=null)
	{	
		$CI =& get_instance();
		$CI->load->library('jsmin');
		//create destination folder is not exsited
		$this->_create_folder($dest);

		foreach ($this->_count($dir) as $filename) {
			$css = file_get_contents($filename);
			// $css = $CI->jsmin->minify($css);
			// // $css = preg_replace('!\s+!', ' ', $css);
			// // $css = str_replace(array("\r\n", "\r", "\n", "\t"), "", $css);
			// $css = trim($css);
			$css = $this->compress($css);

			$newpath = str_replace($dir, $dest, $filename);
			$new_directory = dirname($newpath);
			$this->_create_folder($new_directory);
			$this->_write_file($newpath, $css);
		}
		
		echo 'done compressing js';
	}
	function compress_html($dir,$dest,$exclusion=null)
	{
		$CI =& get_instance();
		
		$this->_create_folder($dest);
		
		foreach($this->_count($dir) as $filename)
		{
			$html = file_get_contents($filename);

			// $html = $this->minify_html($html);
			
			// remove html comment
			// $html = preg_replace('/<!--[^\[](.*?)-->/', '', $html);


			// link http://stackoverflow.com/questions/19676024/using-regular-expression-remove-html-comments-from-content
			$html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

			// refer this site for more info about this regex
			// http://stackoverflow.com/questions/19509863/how-to-remove-js-comments-using-php

			//remove js and css comment
			// $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
			// $html = preg_replace($pattern, '', $html);

			// remove mutiline js css comment
			// refer this source http://stackoverflow.com/questions/643113/regex-to-strip-comments-and-multi-line-comments-and-empty-lines
			$html = preg_replace('!/\*.*?\*/!s', '', $html);
			// $html = preg_replace('/\n\s*\n/', "\n", $html);

			// remove single line comment
			$html = preg_replace('#^\s*//.+$#m', "", $html);
			$html = preg_replace('~^\h*//\h*$~m', '', $html);


			// remove inline comment
			$html = preg_replace('/\s+\/\/[^\n]+/m', '', $html);
			$html = preg_replace('/(?<=;)\s+\/\/[^\n]+/m', '', $html);

			// remove empty single line
			// refer this link http://stackoverflow.com/questions/34689674/php-regex-remove-inline-comment-only/34689766?noredirect=1#comment57127722_34689766
			$html = preg_replace('/(?<=;)\s+\/\/[^\n]+/m', '', $html);
			

			// remove single line comment
			// $html = preg_replace("/(?<!\:)\/\/(.*)\\n/", '', $html);
			// $html = preg_replace('#^\s*//.+$#m', "", $html);
			// $html = preg_replace('~^\h*//\h*$~m', '', $html);


			


			// $html = $this->stripPhpComments($html);

			// $html = $this->just_strip($html);

			// remove newline
			$html = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $html);

			// remove spaces
			$html =  preg_replace('!\s+!', ' ', $html);

			// $html= str_replace(array("  ", "   ","    ","     "), " ", $html);

			$html = trim($html);



			$newpath = str_replace($dir, $dest, $filename);
			$new_directory = dirname($newpath);
			$this->_create_folder($new_directory);
			$this->_write_file($newpath, $html);

		}
		echo 'done compressing html';
	}
	
	function _count($dir){
		$di = new RecursiveDirectoryIterator($dir);
		$paths = array();
		foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
			if(is_file($filename) && preg_match('/^\./',basename($filename)) == 0){
				$paths[] = $filename;
			}
		}
		return $paths;
	}
	function _create_folder($dest)
	{
		if(!file_exists($dest)){
			mkdir($dest, 0755, true);
		}
	}
	function _write_file($path, $data, $mode = 'wb')
	{
		if ( ! $fp = @fopen($path, $mode))
		{
			return FALSE;
		}
		
		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);	

		return TRUE;
	}

	
	function stripPhpComments($code)
	{
		//http://stackoverflow.com/questions/34689674/php-regex-remove-inline-comment-only?noredirect=1#comment57125782_34689674
	    $tokens = token_get_all($code);
	    $strippedCode = '';

	    while($token = array_shift($tokens)) {        
	        if((is_array($token) && token_name($token[0]) !== 'T_COMMENT') 
	            || !is_array($token)) 
	        {
	            $strippedCode .= is_array($token) ? $token[1] : $token;
	        }
	    }
	    return $strippedCode;        
	}

	function just_strip($source){
		foreach(token_get_all($source) as $token)
		{
		    if(is_array($token))
		    {
		        if($token[0] != T_COMMENT || substr($token[1] != '//', 0, 3))
		            return $token[1];
		    }
		    else
		        return $token;
		}
	}
	


	function compress($html)
	{

		// remove html comment
		// $html = preg_replace('/<!--[^\[](.*?)-->/', '', $html);

		// link http://stackoverflow.com/questions/19676024/using-regular-expression-remove-html-comments-from-content
		$html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

		// refer this site for more info about this regex
		// http://stackoverflow.com/questions/19509863/how-to-remove-js-comments-using-php

		//remove js and css comment
		// $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
		// $html = preg_replace($pattern, '', $html);

		// remove mutiline js css comment
		// refer this source http://stackoverflow.com/questions/643113/regex-to-strip-comments-and-multi-line-comments-and-empty-lines
		$html = preg_replace('!/\*.*?\*/!s', '', $html);
		// $html = preg_replace('/\n\s*\n/', "\n", $html);

		// remove single line comment
		$html = preg_replace('#^\s*//.+$#m', "", $html);
		$html = preg_replace('~^\h*//\h*$~m', '', $html);


		// remove inline comment
		$html = preg_replace('/\s+\/\/[^\n]+/m', '', $html);
		$html = preg_replace('/(?<=;)\s+\/\/[^\n]+/m', '', $html);

		// remove empty single line
		// refer this link http://stackoverflow.com/questions/34689674/php-regex-remove-inline-comment-only/34689766?noredirect=1#comment57127722_34689766
		$html = preg_replace('/(?<=;)\s+\/\/[^\n]+/m', '', $html);
		

		// remove single line comment
		// $html = preg_replace("/(?<!\:)\/\/(.*)\\n/", '', $html);
		// $html = preg_replace('#^\s*//.+$#m', "", $html);
		// $html = preg_replace('~^\h*//\h*$~m', '', $html);

		// $html = $this->stripPhpComments($html);

		// $html = $this->just_strip($html);

		// remove newlines
		$html = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $html);

		// remove spaces
		// $html= str_replace(array("  ", "   ","    ","     "), " ", $html);
		$html =  preg_replace('!\s+!', ' ', $html);


		$html = trim($html);

		echo 'done compressing html';
	}

	function create_sprite($img_dir, $img_dest, $css_dest, $exclusion=null)
	{
		$this->_create_folder(dirname($img_dest));
		$this->_versionize(dirname($img_dest).'/');
		$css = file_get_contents($css_dest);
		$v=$this->_get_version(dirname($img_dest));
		

		$imgs = array();
		 //extract urls from the css and put into var $imgs
		$re = '/url\(\s*[\'"]?(\S*\.(?:jpe?g|gif|png))[\'"]?\s*\)[^;}]*?no-repeat/i';
		if (preg_match_all($re, $css, $matches)) {
			$imgs = $matches[1];
			print_r($imgs);
			$canvasWidth= 0;
			$canvasHeight = 0;
			
			//remove dubplicate url, if duplicated our sprite will have duplicated images
			$imgs = array_unique($imgs);
			//reset array key
			$imgs = array_merge($imgs);

			// loop thorugh urls, get the images size to determine our canvas size
			foreach($imgs as $val)
			{
				$info = getimagesize($img_dir.basename($val));
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
				$tmp = imagecreatefrompng($img_dir.basename($val));
				$w=imagesx($tmp);
				$h=imagesy($tmp);
				imagecopy($img, $tmp, 0, $pos, 0, 0, $w, $h);
				$pos += $h;
				imagedestroy($tmp);
			}
  			print_r($array);
			// create our final sprite image.
			imagepng($img, $img_dest);		
			//get the compressed css file
			$css = file_get_contents($css_dest);
			//replace normal urls with sprite.png and add positions 
			foreach($array as $key=>$val){
				$css = str_replace($key,'url(/'.$img_dest.$v.') no-repeat '.$val['x'].' '.$val['y'].'px',$css);
			}
			// finally write the compressed css back to service folder
			$this->_write_file($css_dest, $css);
//			echo $css;
		}
	}
	function combine_file($dir, $dest, $final_file_name){
		$CI =& get_instance();

		//create destination folder is not exsited
		$this->_create_folder($dest);


		foreach($this->_get_all_path($dir) as $single_path)
		{
			$this->_create_folder($dest.$single_path);
			//echo $dest.$single_path.'<br>';
			$files = glob($single_path."*.css");// get css from orignal folder
			
			$content = "";
			
			foreach($files as $file)
			{
				$content .= file_get_contents($content);
			}

			$content = trim($content);
			$this->_write_file($dest.$single_path.$final_file_name, $content);
			// write compressed file to new folder
			//demo/register.css
			//demo/from.css
			//=>demo/demo.css
		}
	}

	function _get_all_path($dir)
	{
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
		$paths = array();
		foreach ( $iterator as $path )
		{
			if($path->isDir())
			{
				$paths[] = $path->getPathname().'/';
			}
		}
		
		array_push($paths, $dir);
		return $paths;
	}


}

?>