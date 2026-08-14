<?php
App::setLayout('lay_blank.php');


/*$pt = PhpThumb::getInstance();
$pt->registerPlugin('GdReflectionLib', 'gd');
*/
$thumb = PhpThumbFactory::create('static/img_cache/fotos/20090331-132119_andyh.cgsociety.org_160x170.jpg');  
$thumb->resize(100, 100);  
//$thumb->createReflection(40, 40, 80, true, '#a4a4a4');
$thumb->bc(100, 0.6);
//$thumb->test();
$thumb->show();

/*
$thumb = PhpThumbFactory::create('static/02.png');  
$thumb->resize(200, 200);
$thumb->show();  
 * 
 */