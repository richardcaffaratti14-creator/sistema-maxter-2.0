<?php

error_reporting(E_ALL ^ E_NOTICE);

Page::addJS('jquery-ui-1.8.21.custom.min.js');
Page::addCSS('maxter-theme/jquery-ui-1.8.21.custom.css');

Page::addCSS("jcrop/jquery.Jcrop.min.css");
Page::addJS("jquery.Jcrop.min.js");

//Page::addJS("imgpreview.full.jquery.js");
Page::addJS("imgpreview.full.jquery.js");

Page::addJS('mediaelement-and-player.min.js');
Page::addCSS('mediaelement/mediaelementplayer.css');

Page::addJS("jquery.form.js");

Page::addJS("pixastic.custom.js");

$q = Http::getOverPost('q');

$process_order = Http::getOverPost('process_order');

Presu::init();
PhotoBook::init();
