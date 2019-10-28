<?php

require_once("rss.php");

$res = new RSS($argv[1]);

foreach (array_slice($argv, 2) as $f) {
  $rss = simplexml_load_file($f);

  foreach ($rss->channel->item as $item) {
    $title       = (string)$rss->channel->title.": ".$item->title;
    $guid        = (string)$item->guid;
    $description = (string)$item->description;
    $pubDate     = (string)$item->pubDate;
    $res->addItem($title, $guid, $description, $pubDate);
  }
}

echo $res->toString();
