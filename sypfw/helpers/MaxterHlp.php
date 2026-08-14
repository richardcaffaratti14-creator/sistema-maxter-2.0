<?php

class MaxterHlp {
    
    public static function d($txt) {
    echo '<pre>' . print_r($txt, true) . '</pre>';
}

    public static function fn($number) {
	return number_format($number, 2, ',', '.');
    }

    /**
     * 	returns if the $filename has the $sufix
     * 
     * @param string $filename
     * @param string $sufix
     */
    public static function isFileSufix($filename, $sufix) {
	$parts = pathinfo($filename);
	$media_filename_noext = $parts['filename'];
	$len = strlen($sufix);
	$file_sufix = substr($media_filename_noext, ($len * (-1)));
	
	if (strtolower($file_sufix) == strtolower($sufix)) {
	    return TRUE;
	}
	return FALSE;
    }

}
