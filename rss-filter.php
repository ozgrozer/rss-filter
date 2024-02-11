<?php

class RssFilter {
  function stristrArray($haystack, $needle) {
    if (!is_array($needle)) {
      $needle = [$needle];
    }
    foreach ($needle as $searchstring) {
      $found = stristr($haystack, $searchstring);
      if ($found) {
        return $found;
      }
    }
    return false;
  }

  function getAttribute($string, $attribute = '') {
    if ($string->length === 0) {
      $result = '';
    } else {
      if ($attribute) {
        $result = $string->item(0)->getAttribute($attribute);
      } else {
        if ($string->item(0)->childNodes->item(1)) {
          $result = $string->item(0)->childNodes->item(1)->nodeValue;
        } elseif ($string->item(0)->childNodes->item(0)) {
          $result = $string->item(0)->childNodes->item(0)->nodeValue;
        } else {
          $result = '';
        }
      }
    }
    return $result;
  }

  function init($source) {
    include 'words.php';

    $xmlDoc = new DOMDocument();
    $source = trim(file_get_contents($source));
    $xmlDoc->loadXML($source);

    if ($xmlDoc->getElementsByTagName('feed')->length) {
      $type = 'atom';
    } elseif ($xmlDoc->getElementsByTagName('rss')->length) {
      $type = 'rss';
    } else {
      $type = '';
    }

    if ($type === 'atom') {
      $rssTitle = $this->getAttribute($xmlDoc->getElementsByTagName('title'));
      $rssLink = $this->getAttribute($xmlDoc->getElementsByTagName('link'), 'href');
      $rssDescription = '';
      $items = $xmlDoc->getElementsByTagName('entry');
      $countItems = ($xmlDoc->getElementsByTagName('entry')->length);
      $combineItems = '';

      for ($i = 0; $i < $countItems; $i++) {
        $item = $items->item($i);
        $title = htmlspecialchars($this->getAttribute($item->getElementsByTagName('title')));
        $link = htmlspecialchars($this->getAttribute($item->getElementsByTagName('link'), 'href'));
        $description = htmlspecialchars($this->getAttribute($item->getElementsByTagName('content')));
        $pubDate = $this->getAttribute($item->getElementsByTagName('published'));
        $updated = $this->getAttribute($item->getElementsByTagName('updated'));
        $published = $pubDate ? $pubDate : $updated;

        if (
          ($this->stristrArray($title, $goodwords) || ($this->stristrArray($description, $badwords))) && 
          (!$this->stristrArray($title, $badwords) && !($this->stristrArray($description, $badwords)))
          ) {
          $combineItems .= '<item>
            <title>' . $title . '</title>
            <link>' . $link . '</link>
            <description>' . $description . '</description>
            <pubDate>' . $published . '</pubDate>
          </item>';
        }
      }
    } elseif ($type === 'rss') {
      $channel = $xmlDoc->getElementsByTagName('channel')->item(0);
      $rssTitle = $this->getAttribute($channel->getElementsByTagName('title'));
      $rssLink = $this->getAttribute($channel->getElementsByTagName('link'));
      $rssDescription = $this->getAttribute($channel->getElementsByTagName('description'));
      $items = $xmlDoc->getElementsByTagName('item');
      $countItems = ($xmlDoc->getElementsByTagName('item')->length);
      $combineItems = '';

      for ($i = 0; $i < $countItems; $i++) {
        $item = $items->item($i);
        $title = htmlspecialchars($this->getAttribute($item->getElementsByTagName('title')));
        $link = htmlspecialchars($this->getAttribute($item->getElementsByTagName('link')));
        $description1 = htmlspecialchars($this->getAttribute($item->getElementsByTagName('description')));
        $description2 = htmlspecialchars($this->getAttribute($item->getElementsByTagName('encoded')));
        $description = $description1 ? $description1 : $description2;
        $pubDate = $this->getAttribute($item->getElementsByTagName('pubDate'));

        $enclosure = ['url' => '', 'type' => '', 'length' => ''];
        $imageAttributes = $item->getElementsByTagName('enclosure')[0]->attributes;
        foreach ($imageAttributes as $key => $imageAttribute) {
          $enclosure[$imageAttribute->nodeName] = htmlspecialchars($imageAttribute->nodeValue);
        }


        if (
          ($this->stristrArray($title, $goodwords) || ($this->stristrArray($description, $badwords))) && 
          (!$this->stristrArray($title, $badwords) && !($this->stristrArray($description, $badwords)))
          ) {
          $combineItems .= '<item>
            <title>' . $title . '</title>
            <link>' . $link . '</link>
            <description>' . $description . '</description>
            <pubDate>' . $pubDate . '</pubDate>
            <enclosure url="' . $enclosure['url'] . '" type="' . $enclosure['type'] . '" length="' . $enclosure['length'] . '" />
          </item>';
        }
      }
    }

    if ($type) {
      $result = '<?xml version="1.0" encoding="UTF-8"?>
      <rss version="2.0">
      <channel>
        <title>' . htmlspecialchars($rssTitle) . '</title>
        <link>' . htmlspecialchars($rssLink) . '</link>
        <description>' . htmlspecialchars($rssDescription) . '</description>
        ' . $combineItems . '
      </channel>
      </rss>';
    } else {
      $result = 'undefined type';
    }

    return $result;
  }
}
