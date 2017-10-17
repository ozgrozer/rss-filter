<?php

error_reporting(0);
header('Content-Type: text/xml; charset=utf-8');

include 'rss-filter.php';
$rf = new RssFilter();
echo $rf->init($_GET['source']);
