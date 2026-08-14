<?php

App::setLayout('lay_blank.php');

$im = imagecreatefromjpeg(STATIC_PATH.'test/01.jpg');

$val = Http::getOverPost('val');
$val = $val == '' ? -0.9 : $val;

if($im && imagefilter($im, IMG_FILTER_BRIGHTNESS, $val))
{
    echo 'Image brightness changed. '. $val;

	imagejpeg($im, STATIC_PATH.'test/01_b.jpg', 100);
    //imagepng($im, 'sean.png');
    imagedestroy($im);
}
else
{
    echo 'Image brightness change failed.';
}

?>
<img src="static/test/01_b.jpg" />