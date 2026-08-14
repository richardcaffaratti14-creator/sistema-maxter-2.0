<?php
App::setLayout('lay_blank.php');

Presu::init();

$id = Presu::getID();

MaxterHlp::d("--@".$id."@--");
MaxterHlp::d($_SESSION['maxter']);