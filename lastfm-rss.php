<?php

if ($argc < 2) {
  echo "url missing\n";
  return;
}

$baseUrl = $argv[1];

function loadFile($file) {
  $dom = new DOMDocument();
  @ $dom->loadHTMLFile($file);
  return new DOMXPath($dom);
} 


preg_match('~https://www.last.fm/music/([^/]+)/.*~', $baseUrl, $m);
$baseName = $m[1];

$cacheFile = "lf/$baseName";
if (!file_exists('lf')) {
  mkdir("lf");
}

if (file_exists($cacheFile) && filemtime($cacheFile) > time() - (7 * 24 * 3600)) {
  echo file_get_contents($cacheFile);
  return;
}


$items = [];

$xpath = loadFile($baseUrl);
$albums = $xpath->query('//section[@id="artist-albums-section"]/ol/li'); 
$feedTitle = $xpath->query('//h1')->item(0)->textContent;

foreach ($albums as $a) {
  $title = $xpath->query('./div/h3', $a)->item(0)->textContent;
  $img   = $xpath->query('./div/div[starts-with(@class, "media-item")]//img/@src', $a)->item(0)->textContent;
  $url   = $xpath->query('./div/a/@href', $a)->item(0)->textContent;
  $info  = $xpath->query('./div/p[last()]', $a)->item(0)->textContent;

  if (preg_match('~ 1 track~', $info)) continue;

  $title = trim($title);
  $date  = date(DATE_RSS, strtotime(trim(strstr($info, '·', true))));
  $url   = "https://www.last.fm".$url;

  $items[] = [$title, $date, $url, $img];
}


$rss = new SimpleXMLElement('<rss version="2.0"/>');
$rss->channel->title = $feedTitle;

foreach ($items as list($title, $date, $url, $img)) {
  $item = $rss->channel->addChild('item');
  $item->title   = $title;
  $item->pubDate = $date;
  $item->guid    = $url;
  $item->guid['isPermaLink'] = 'true';
  $item->description = "<img src='$img'/>";
}

$rss = $rss->asXML();
file_put_contents($cacheFile, $rss);
echo $rss;
