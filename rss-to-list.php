<?php

$rss = simplexml_load_file($argv[1]);

$items = [];
foreach ($rss->channel->item as $item) {
  $items[] = [
    (string)$item->title,
    strtotime((string)$item->pubDate),
    (string)$item->guid,
    $item->enclosure['url']
  ]; 
}


usort($items, function ($a, $b) {
  return ($a[1] < $b[1]) ? 1 : -1;
});

foreach ($items as list($title, $date, $url, $img)) {
  $date = date("Y", $date);
  echo "- $date <a href='$url'>$title</a>\n";
}
