<?php

$appname = $args[1];

include '../vnbb.php';

mkdir('./'.$appname);

mkdir("./$appname/tmp");
mkdir("./$appname/log");
mkdir("./$appname/upoad");
mkdir("./$appname/conf");


mkdir("./$appname/conf");
copy('./conf.default.php', "./$appname/conf/conf.php");

