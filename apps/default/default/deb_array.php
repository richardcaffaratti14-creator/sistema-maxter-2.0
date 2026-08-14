<?php

$a[1] = 'uno';
$a[5] = '2';

Dump::d($a);

$a = array_values($a);

Dump::d($a);
