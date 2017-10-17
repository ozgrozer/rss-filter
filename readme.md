# rss-filter

A simple PHP class for filtering RSS feeds.

## Usage

1] Create your `words.php` file with the words that you don't want to see in the RSS feeds.

```php
<?php

$words = [
  'banned',
  'politic',
  'violence'
];
```

2] Create your `index.php` file like this.

```php
<?php

error_reporting(0);
header('Content-Type: text/xml; charset=utf-8');

include 'rss-filter.php';
$rf = new RssFilter();
echo $rf->init($_GET['source']);
```

3] Now it's time to reproduce the RSS feed.

Instead of using the original URL: https://www.wired.com/feed/rss<br>
You can use like that: http://localhost/rss-filter/?source=https://www.wired.com/feed/rss
