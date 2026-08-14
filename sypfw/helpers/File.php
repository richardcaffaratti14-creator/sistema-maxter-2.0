<?php

class FilePaginatedResult {

	public $files_qty;
	public $pages_qty;
	public $current_page;
	public $files;

}

class File {

	public static function docopy($src, $dst) {
//		file_put_contents('__bench.txt', "$src -> $dst\n", FILE_APPEND);

		
//		$_start = microtime(true);
		
		//traditional copy -----------------------------------------------------------------------------
		$_ret = @copy($src, $dst);
		
//		$_end = microtime(true) - $_start;
//		file_put_contents('__bench.txt', "COPY $_end \n\n", FILE_APPEND);
		
		
		//chunked copy ---------------------------------------------------------------------------------
//		$_start = microtime(true);
//		
//		# 1 meg at a time, you can adjust this.
//		$buffer_size = 1048576; 
//		$ret = 0;
//		$fin = fopen($src, "rb");
//		$fout = fopen($dst, "w");
//		while(!feof($fin)) {
//			$ret += fwrite($fout, fread($fin, $buffer_size));
//		}
//		fclose($fin);
//		fclose($fout);
//		$_ret = $ret; # return number of bytes written		
//		
//		$_end = microtime(true) - $_start;
//		file_put_contents('__bench.txt', "Chunked $_end \n\n", FILE_APPEND);
		
		
		//stream copy ---------------------------------------------------------------------------------
//		$_start = microtime(true);
//		
//		$fsrc = fopen($src,'r');
//        $fdest = fopen($dst,'w+');
//        $len = stream_copy_to_stream($fsrc,$fdest);
//        fclose($fsrc);
//        fclose($fdest);
//        $_ret = $len; 		
//		
//		$_end = microtime(true) - $_start;
//		file_put_contents('__bench.txt', "Stream $_end \n\n", FILE_APPEND);
		
		return $_ret;
	}
	
	
	public static function mkdirs($dir, $mode = 0777, $recursive = true) {
		if (is_null($dir) || $dir === "") {
			return FALSE;
		}
		if (is_dir($dir) || $dir === "/") {
			return TRUE;
		}
		if (File::mkdirs(dirname($dir), $mode, $recursive)) {
			return mkdir($dir, $mode);
		}
		return FALSE;
	}

	public static function getNextAvailableFileName($file_name) {

		$fullfilename = $file_name;
		$filename = basename($file_name);
		$ext = substr($filename, strrpos($filename, '.') + 1);
		$name = substr($filename, 0, strrpos($filename, '.'));
		$filepath = str_replace($filename, '', $file_name);
		$c = 0;
		while (file_exists($fullfilename)) {
			$c++;
			$fullfilename = $filepath . $name . '-' . $c . '.' . $ext;
		}
		return $fullfilename;
	}

	public static function delete($file_path) {
		if (is_file($file_path)) {
			return unlink($file_path);
		} else {
			return FALSE;
		}
	}

	public static function upload($post_name, $path, $override = false) {
		$file_name = $_FILES[$post_name]['name'];

		if (!$override) {
			$new_file_path = File::getNextAvailableFileName($path . $file_name);
		} else {
			$new_file_path = $path . $file_name;
		}

		if (move_uploaded_file($_FILES[$post_name]['tmp_name'], $new_file_path))
			return basename($new_file_path);
		else
			return false;
	}

	public static function getFilesForm($path) {
		return glob($path);
	}

	public static function getExtension($file) {
		return pathinfo($file, PATHINFO_EXTENSION);
	}

	public static function getPaginatedFilesForm($path, $current_page, $page_size = 14 , $filter = '') {
		//$tmp = glob($path."*.{jpg,JPG,jpeg,JPEG,mp4,MP4}", GLOB_BRACE);
		if (!empty($filter)) $filter .= "*";
		$tmp = glob($path . "*{$filter}.[jJ][pP][gG]");
		$tmp1 = glob($path . "*{$filter}.[jJ][pP][eE][gG]");
		$tmp2 = glob($path . "*{$filter}.[mM][pP][4]");
		$tmp = array_merge($tmp, $tmp1, $tmp2);
		sort($tmp, SORT_REGULAR);

		$rtn = new FilePaginatedResult();
		$rtn->current_page = $current_page;
		$rtn->files_qty = count($tmp);
		$rtn->pages_qty = ceil($rtn->files_qty / $page_size);
		$from = $current_page * $page_size;
		$to = $from + $page_size;
		$to = $to > $rtn->files_qty ? $rtn->files_qty : $to;
		for ($i = $from; $i < $to; $i++) {
			$rtn->files[] = $tmp[$i];
		}
		return $rtn;
	}

	public static function getNextPrevFile($current) {
		
		if (strpos($current, '/') === 0){
			$current = substr($current, 1);
		}
		/* */
		
		//$current = str_replace('//', '', $current);
		
		$dirname = dirname($current);
		$dirname = $dirname == '.' ? '' : $dirname;
		
		$path = utf8_decode(PATH_ORIGINALS_ROOT . $dirname) . '/';
		$path = str_replace('/\\', '', $path);
		$path = str_replace('//', '/', $path);
		$all = File::getPaginatedFilesForm($path, 0, 999999999);
		$prev = '';
		
		$idx = -1;
		$c = 0;

		//Dump::d($all->files);
		
		if (count($all->files) > 0) {
			foreach ($all->files as $f) {
				//Dump::dlp($f , utf8_decode(PATH_ORIGINALS_ROOT . $current));
				if ($f == utf8_decode(PATH_ORIGINALS_ROOT . $current)) {
					$idx = $c;
				}
				$c++;
			}
		}

		//Dump::dlp("---", $current, $path, $dirname);
		
		if ($idx != -1){
			$rtn = array();
			if ($idx-1 < 0){
				//	no hay anterior devuelvo el ultimo
				$rtn['prev'] = $all->files[count($all->files)-1];
			} else {
				$rtn['prev'] = $all->files[$idx-1];
			}
			
			if ($idx + 1 > count($all->files)-1){
				//	no hay siguiente devuelvo el primero
				$rtn['next'] = $all->files[0];
			} else {
				$rtn['next'] = $all->files[$idx+1];
			}
			
			
			return $rtn;
			//Dump::dlp($all->files[$idx-1], $all->files[$idx+1]);
		} else {
			return FALSE;
		}
		
	}

}

?>
