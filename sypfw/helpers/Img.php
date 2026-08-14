<?php

class Img {

	public static function crop($img_path, $img_size) {
		//	f1/f2/algo.jpg		--> f1/f2/algo_100x100.jpg
		
		//Dump::dlp("@@", basename($img_path),"@@");
		
		if (strpos($img_path, '.'))
			$ini = substr($img_path, 0, strrpos($img_path, '.'));
		else 
			$ini = $img_path;
		$mid = "_" . $img_size;
		if (strpos($img_path, '.'))
			$end = substr($img_path, strrpos($img_path, '.'));
		else
			$end = '';
		return $ini . $mid . $end;
	}
	/*
	public static function deleteCacheFor($img_path) {

		$img_path = str_replace('static/', '', $img_path);
		//	fix: need to make this config dynamic
		//$cache_path = STATIC_PATH.'img_cache/';
		$cache_path = PATH_THUMBS;

		//	$cache_path . uploads/tapas_empresalud_ng/
		$search_path = $cache_path . substr($img_path, 0, strrpos($img_path, '/')) . '/';
		//Dump::dlp("SEARCH PATH",$search_path);

		//	img name = tapa_empresalud_ng_diciembre_2011.jpg
		$base = basename($img_path);
		$name = substr($base, 0, strrpos($base, '.'));
		$ext = substr($base, strrpos($base, '.'));

		$search_files = glob($search_path . '*' . $ext);

		foreach ($search_files as $file) {
			$sb = basename($file);
			if (preg_match('/' . $name . '.+' . $ext . '/', $sb)) {
				//Dump::dlp($img_path, $file, "MATCH");
				File::delete($file);
			} else {
				//Dump::dlp($img_path, $file, "DONT MATCH");
			}
		}
	}
	
	public static function deleteImgAndCache($img_path){
		Img::deleteCacheFor($img_path);
		File::delete($img_path);
	}
	 */

}
