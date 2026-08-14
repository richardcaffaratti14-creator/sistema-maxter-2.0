<?php

$filename = "D:/maxter_fotos/fotos/IMG_0133.JPG";
$filename2 = "D:/maxter_fotos/fotos/IMG_0132__.JPG";

$exif = exif_read_data($filename);
$ort = $exif['Orientation'];

$image = WideImage::load($filename);

// GD doesn't support EXIF, so all information is removed.
$image = $image->exifOrient($ort);
$image->output('jpg');