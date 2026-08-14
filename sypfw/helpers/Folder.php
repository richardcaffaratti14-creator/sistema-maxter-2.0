<?php

$_global_sidebar_folder_ix = 1;
//$jsonobj = str_replace(PATH_ORIGINALS_ROOT, '', $path);
//$jsonobj = explode("/", $jsonobj);
//$_global_this_path = array();
//$_global_basepath = PATH_ORIGINALS_ROOT;
//foreach ($jsonobj as $tmp_folder) {
//	$_global_this_path[ base64_encode($_global_basepath . "/" . $tmp_folder) ] = utf8_encode($tmp_folder);
//	$_global_basepath .= "/" . $tmp_folder;
//}

class Folder {

    public static function createTree($path, $link, $baseAttrs = '', $selected = '', $parent_selected = false, $_is_first_folder = true) {
		
		if ($_is_first_folder) {
			if (file_exists('_folder_cache'.base64_encode(PATH_ORIGINALS_ROOT).'.txt'))
				if (isset($_GET['_clear_cache']))
					unlink('_folder_cache'.base64_encode(PATH_ORIGINALS_ROOT).'.txt');
				else
					return file_get_contents('_folder_cache'.base64_encode(PATH_ORIGINALS_ROOT).'.txt');
		}
		
		global $_global_sidebar_folder_ix, $_global_this_path, $_global_basepath;
		$_global_sidebar_folder_ix++;
		
		$folders = glob($path . '*', GLOB_ONLYDIR);
		
		if (count($folders) > 0) {
			$sub_selected = substr($selected, 0, strlen($path)) == $path;
			$rtn = '<ul ' . $baseAttrs . ' id="_ft_' . ($_global_sidebar_folder_ix - 1) . '" ' . (($_global_sidebar_folder_ix > 1) && !$sub_selected && !$parent_selected ? 'style="display:none"' : "") . '>';

			foreach ($folders as $f) {
				$sub_selected = substr($selected, 0, strlen($f . "/")) == $f . "/";

				$folder_name_bc = str_replace('/',' \\ ', str_replace(PATH_ORIGINALS_ROOT, '', $f));
				
				$folder_name = Folder::lastName($f);

				$first_pic = 'images/firstpic/' . self::afterFotos($f);

//				$_global_this_path[ base64_encode($f) ] = utf8_encode($folder_name);
				

//				if ($f == $selected) {
//					$rtn .= '<li class="selected"><a href="javascript:void(0)" class="_expand_folder _expanded" data-id="' . $_global_sidebar_folder_ix . '"></a>'
//						. '<a class="fname" rel="' . $first_pic . '" href="' . $link . base64_encode($f) . '">' . utf8_encode($folder_name) . '</a>';
//				} else {
//					$rtn .= '<li><a href="javascript:void(0)" '
//						. 'class="_expand_folder ' . ($sub_selected ? "_expanded" : "") . '" data-id="' . $_global_sidebar_folder_ix . '"></a>'
//						. '<a class="fname"  rel="' . $first_pic . '" href="' . $link . base64_encode($f) . '">' . utf8_encode($folder_name) . '</a>';
//				}
				if ($f == $selected) {
					$rtn .= '<li id="_li_ft_'.$_global_sidebar_folder_ix.'" class="selected">'
						. '<a href="javascript:_load_folder_pictures(\''.base64_encode($f).'\', ' . $_global_sidebar_folder_ix . ', \''.addslashes(utf8_encode($folder_name_bc)).'\')" class="_expand_folder _expanded" data-id="' . $_global_sidebar_folder_ix . '"></a>'
						. '<a class="fname sidebarfolderlink" rel="' . $first_pic . '" href="javascript:_load_folder_pictures(\''.base64_encode($f).'\', ' . $_global_sidebar_folder_ix . ', \''.addslashes(utf8_encode($folder_name_bc)).'\')">' . utf8_encode($folder_name) . '</a>' . "\n";
				} else {
					$rtn .= '<li id="_li_ft_'.$_global_sidebar_folder_ix.'">'
						. '<a href="javascript:_load_folder_pictures(\''.base64_encode($f).'\', ' . $_global_sidebar_folder_ix . ', \''.addslashes(utf8_encode($folder_name_bc)).'\')" ' . 'class="_expand_folder ' . ($sub_selected ? "_expanded" : "") . '" data-id="' . $_global_sidebar_folder_ix . '"></a>'
						. '<a class="fname sidebarfolderlink" rel="' . $first_pic . '" href="javascript:_load_folder_pictures(\''.base64_encode($f).'\', ' . $_global_sidebar_folder_ix . ', \''.addslashes(utf8_encode($folder_name_bc)).'\')">' . utf8_encode($folder_name) . '</a>' . "\n" ;
				}
				
				$rtn .= Folder::createTree($f . "/", $link, '', $selected, $f == $selected, false);
				$rtn .= "</li>";
			}
			$rtn .= '</ul>';
		}
		
		if ($_is_first_folder) {
			file_put_contents('_folder_cache'.base64_encode(PATH_ORIGINALS_ROOT).'.txt', $rtn);
		}
		
		return $rtn;
    }

    public static function lastName($path) {
		$t = explode('/', $path);
		$count = count($t);
		if ($t[$count - 1] != '') {
			return $t[$count - 1];
		} else {
			$t[$count - 2];
		}
    }

    public static function afterFotos($path) {
		$t = explode('fotos/', $path);
		return $t[1];
    }

}
