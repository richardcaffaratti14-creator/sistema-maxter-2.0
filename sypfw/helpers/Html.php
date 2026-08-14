<?php

class Html {

	public static function array2Options($array, $selected = '') {
		$rtn = '';
		foreach ($array as $it) {
			$rtn .= '<option value="' . $it . '"' . ($selected == $it ? ' selected="selected"' : '') . '>' . $it . '</option>';
		}
		return $rtn;
	}

	public static function assocArray2Option($array, $label, $value) {
		$rtn = '';
		foreach ($array as $it) {
			$rtn .= '<option value="' . $it[$value] . '">' . $it[$label] . '</option>';
		}
		return $rtn;
	}
	
	public static function stdClass2Option($array, $label, $value) {
		$rtn = '';
		foreach ($array as $it) {
			$rtn .= '<option value="' . $it->$value . '">' . $it->$label . '</option>';
		}
		return $rtn;
	}

	public static function generatePaginator( $totalPages, $currentPage, $extra_params = '' ) {
		if ($totalPages > 1) {
			echo '<div class="pagination">';

			if ($currentPage == 0)
				echo '<span class="previous-off">&lt;&lt; ' . l_Previous . '</span>';
			else
				echo '<a href="'.App::getActionUrl().'?page=' . ($currentPage - 1) . $extra_params. '">&lt;&lt; ' . l_Previous . '</a>';

			for ($i = 0, $m = $totalPages; $i < $m; $i++) {
				if ($currentPage == $i)
					echo '<span class="active">' . ($i + 1) . '</span>';
				else
					echo '<a href="'.App::getActionUrl().'?page=' . $i .$extra_params. '">' . ($i + 1) . '</a>';
			}

			if ($currentPage == ($totalPages - 1))
				echo '<span class="next-off">' . l_Next . ' &gt;&gt;</span>';
			else
				echo '<a href="'.App::getActionUrl().'?page=' . ($currentPage + 1) .$extra_params. '">' . l_Next . ' &gt;&gt;</a>';

			echo '</div>';
		}
	}

}