<?php
App::setLayout('lay_blank.php');

$im = imagecreatefromjpeg(STATIC_PATH . 'test/01.jpg');

$contraste = Http::getOverPost('c');
$contraste = $contraste == '' ? -0.9 : $contraste;

$brillo = Http::getOverPost('b');


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

$contrast_value = $contrast_values[$contraste];

//$contrast_value = $val;

if ($im && imagefilter($im, IMG_FILTER_BRIGHTNESS, $brillo)) {
	echo 'Image contrast changed. ' . $contrast_value.'<br />';

	if ($im && imagefilter($im, IMG_FILTER_CONTRAST, $contrast_value)) {
		echo 'Image bright changed. ' . $brillo;
		imagejpeg($im, STATIC_PATH . 'test/01_b.jpg', 100);
		//imagepng($im, 'sean.png');
		imagedestroy($im);
	}
	
} else {
	echo 'Image brightness change failed.';
}
echo '<br />';
foreach ($contrast_values as $key => $value) {
	echo '<a href="http://localhost/maxter_www/deb_contrast?val=' . $key . '">' . $key . '</a> | ';
}
?>

<img src="static/test/01_b.jpg" />