<?php

error_reporting(0);
if(isset($_GET['notfound']) || !isset($_GET['source']) ) {
    header('Content-Type: text/html');
    print("wrong usage , please supply a valid source e.g.  https://your.server/path/to/script/?source=https://mysite.lan/feed ");
    http_response_code(400);
}

header('Content-Type: text/xml; charset=utf-8');

include 'rss-filter.php';
$rf = new RssFilter();
echo $rf->init($_GET['source']);
