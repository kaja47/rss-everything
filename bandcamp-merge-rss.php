<?php

$res = new SimpleXMLElement('<rss version="2.0"/>');
$res->channel->title = $argv[1];

foreach (array_slice($argv, 2) as $f) {
  $rss = simplexml_load_file($f);

  foreach ($rss->channel->item as $item) {
    $i = $res->channel->addChild('item');
    $i->title               = $rss->channel->title.": ".$item->title;
    $i->pubDate             = $item->pubDate;
    $i->guid                = $item->guid;
    $i->guid['isPermaLink'] = $item->guid['isPermaLink'];
    $i->description         = $item->description;
  }
}

echo $res->asXML();
