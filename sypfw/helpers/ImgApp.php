<?php
/**
 * MUST BE FIXED FOR MULTI APPLICATION SITES!!!!
 * 
 * In this clas will set all the configurations for the "images" application
 * the configurations will be stored in session THIS 
 */
class ImgApp{
	
	private static $_site_code = 'images_app';

	public static function setPath($path){
		SessionManager::setValue('IMAGESAPP_PATH', $path, self::$_site_code);
	}
	
	public static function setCachePath($path){
		SessionManager::setValue('IMAGESAPP_CACHE', $path, self::$_site_code);
	}
	
	public static function setQuality($quality){
		SessionManager::setValue('IMAGESAPP_QUALITY', $quality, self::$_site_code);
	}
	
	/**
	 *
	 * @param boolean $bool allow anlarge image if it is smaller than the size
	 */
	public static function allowEnlarge($bool){
		SessionManager::setValue('ALLOW_ANLARGE', $bool, self::$_site_code);
	}
	
}