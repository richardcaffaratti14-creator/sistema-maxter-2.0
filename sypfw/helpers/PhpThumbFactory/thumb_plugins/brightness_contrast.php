<?php

class BrightnessContrast {

	protected $parentInstance;

	public function bc($brightness, $contrast, &$that) {
		$contrast_values = array(
			"0" => 0,
			"0.1" => -1,
			"0.2" => -10,
			"0.3" => -15,
			"0.4" => -20,
			"0.5" => -25,
			"0.6" => -30,
			"0.7" => -32,
			"0.8" => -35,
			"0.9" => -42,
			"1" => -45,
			"-0.1" => 1,
			"-0.2" => 10,
			"-0.3" => 15,
			"-0.4" => 20,
			"-0.5" => 25,
			"-0.6" => 35,
			"-0.7" => 45,
			"-0.8" => 55,
			"-0.9" => 70,
			"-1" => 95,
		);

		$brightness = $brightness == '' ? 0 : $brightness;
		$contrast = $contrast == '' ? 0 : $contrast;
		
		if (imagefilter($that->getWorkingImage(), IMG_FILTER_BRIGHTNESS, $brightness)) {
			if (imagefilter($that->getWorkingImage(), IMG_FILTER_CONTRAST, $contrast_values[$contrast])) {
				
			} else {
				die("error: contrast cannot be applied");
			}
		} else {
			die("error: brightness cannot be applied");
		}
		return $that;
	}

}

$pt = PhpThumb::getInstance();
$pt->registerPlugin('BrightnessContrast', 'gd');
