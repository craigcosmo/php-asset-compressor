<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Asset {
		
//	private $CI;
	//$CI =& get_instance();
	//$CI->config->load('asset');
	

//	function _mirror($dir, $dest)
//	{
//		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
//		foreach ( $iterator as $path )
//		{
			// check if dir
//			if($path->isDir() && !file_exists($dest.$path->getPathname()))
//			{
//				mkdir($dest.$path->getPathname(), 0755, true);
//			}// now we might have (css/demo/main.css), need to check if css/demo exist
//			elseif( !file_exists(dirname($dest.$path->getPathname())) )
//			{
//				mkdir(dirname($dest.$path->getPathname()), 0755, true);
//				$this->_write_file($dest.$path->getPathname(),'');
//			}
//			else
//			{
//				$this->_write_file($dest.$path->getPathname(),'');
//			}
//		}
//	}
	function _create_folder($dest)
	{
		if(!file_exists($dest)){
			mkdir($dest, 0755, true);
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
	function compress_css($dir, $dest, $exclusion=null)
	{
		$CI =& get_instance();
		$CI->load->library('cssmin');
		//create destination folder is not exsited
		$this->_create_folder($dest);


		foreach($this->_get_all_path($dir) as $single_path)
		{
			$this->_create_folder($dest.$single_path);
			$this->_versionize($dest.$single_path);
			//echo $dest.$single_path.'<br>';
			$files = glob($single_path."*.css");// get css from orignal folder
			
			$css = "";
			
			foreach($files as $file)
			{
				$css .= file_get_contents($file);
			}
			$css = $CI->cssmin->minify($css);
			$css = trim($css);
			$this->_write_file($dest.$single_path.basename($single_path).'.css', $css);
			// write compressed css to new folder
			//demo/register.css
			//demo/from.css
			//=>demo/demo.css
		}
		
	}
	function compress_js($dir, $dest, $exclusion=null)
	{	
		$CI =& get_instance();
		$CI->load->library('jsmin');

		$this->_create_folder($dest);

		foreach($this->_get_all_path($dir) as $single_path)
		{
			$this->_create_folder($dest.$single_path);
			$this->_versionize($dest.$single_path);
		
			$files = glob($single_path."*.js");
			
			$js = "";
			
			foreach($files as $file)
			{
				$js .= file_get_contents($file);
			}
			$js = $CI->jsmin->minify(trim($js));

			$this->_write_file($dest.$single_path.basename($single_path).'.js', $js);
		}
		
	}
	function compress_html($dir,$dest,$exclusion=null)
	{
		$CI =& get_instance();
		$CI->load->library('jsmin');
		$CI->load->library('cssmin');
		
		$this->_create_folder($dest);
		
		foreach($this->_get_all_path($dir) as $single_path)
		{
			$this->_create_folder($dest.$single_path);

			$files = glob($single_path."*.php"); 

			foreach($files as $file)
			{
				$str = file_get_contents($file);
				$str = $CI->jsmin->minify($str);
//				$str = $CI->htmlmin->minify($str);
				// remove html comment
				$str = preg_replace('/<!--[^\[](.*?)-->/', '', $str);
				// remove newline
				$str = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $str);
				// remove spaces
				$str= str_replace(array("  ", "   "), " ", $str);
				
				$this->_write_file($dest.$single_path.basename($file), $str);	
			}
		}
	}
	function compress_html2($dir,$dest,$exclusion=null)
		{
			$CI =& get_instance();
			
			$CI->load->library('jsmin');
			
			$this->_create_folder($dest);
			
			foreach($this->_get_all_path($dir) as $single_path)
			{
				$this->_create_folder($dest.$single_path);
	
				$files = glob($single_path."*.php"); 
	
				foreach($files as $file)
				{
					$str = file_get_contents($file);
					$str = $CI->jsmin->minify($str);
					// remove html comment
					$str = preg_replace('/<!--[^\[](.*?)-->/', '', $str);
					// remove newline
					$str = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $str);
					// remove spaces
					$str= str_replace(array("  ", "   "), " ", $str);
					
					$this->_write_file($dest.$single_path.basename($file), $str);	
				}
			}
		}
	
	function compress_img($img_dir, $img_dest, $css_dest, $exclusion=null)
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

	function css_link($normal_dir, $compress_dir, $use_compress=false)
	{
		$CI =& get_instance();
		$output='';

		$v = $this->_get_version($compress_dir);
		if($CI->config->item('enable_compress') && file_exists($compress_dir) ){
			$files = glob($compress_dir.'*.css');		
		}elseif ($use_compress ==true) {
			$files = glob($compress_dir.'*.css');
		}else{
			$files = glob($normal_dir."*.css");
		}
		
		foreach($files as $file) {
			$output .= '<link type="text/css" rel="stylesheet" href="/'.$file.$v.'" />';
		}
		return $output;
	}
	function js_link($normal_dir, $compress_dir, $use_compress=false)
	{
		$CI =& get_instance();
		$output='';

		$v = $this->_get_version($compress_dir);
		if($CI->config->item('enable_compress') && file_exists($compress_dir) ){
			$files = glob($compress_dir.'*.js');		
		}elseif($use_compress==true) {
			$files = glob($compress_dir.'*.js');
		}else{
			$files = glob($normal_dir."*.js");
		}
		
		foreach($files as $file) {
			$output .= '<script type="text/javascript" src="/'.$file.$v.'"></script>';
		}
		return $output;
	}
	function _versionize($dest)
	{
		$version_file = $dest.'version';
		
		if(!file_exists($version_file)){
			$file = json_encode(array('v'=>1, 'time'=>time()));
			$this->_write_file($version_file, $file);
			
		}else{
			$file = file_get_contents($version_file);	
			$file = json_decode($file, true);
			
			
			// each version made has to be at least 20 seconds away
			if(time() - $file['time'] > 20){
				$file['v']=$file['v']+1;
				$file['time']=time();
				$file = json_encode($file);
				$this->_write_file($version_file, $file);
			}
		}
	}
	function _get_version($location)
	{
		if(file_exists($location.'version')){
			$file = file_get_contents($location.'version');
			$file = json_decode($file, true);
			return '?v='.$file['v'];
		}else{
			return '?v=0';
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
	function _report($text,$result)
	{
		
	}
}

?>