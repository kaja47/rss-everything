<?php

require_once("rss.php");

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

$baseName = $baseUrl;
$baseName = preg_replace('~^https://~', '', $baseName);
$baseName = preg_replace('~\.bandcamp\.com/.*$~', '', $baseName);
$baseName = preg_replace('~/.*$~', '', $baseName);
$baseName = trim($baseName, '/');

$cacheFile = "bc/$baseName";
if (!file_exists('bc')) {
  mkdir("bc");
}

if (file_exists($cacheFile) && filemtime($cacheFile) > time() - (7 * 24 * 3600)) {
  echo file_get_contents($cacheFile);
  return;
}


$items = [];

$xpath = loadFile($baseUrl);
$albums = $xpath->query('//div[@id="discography"]//ul/li');
$feedTitle = $xpath->query('//meta[@property="og:site_name"]/@content')->item(0)->textContent;

foreach ($albums as $a) {
  $title = $xpath->query('./div[@class="trackTitle"]/a', $a)->item(0)->textContent;
  $url   = $xpath->query('./div[@class="trackTitle"]/a/@href', $a)->item(0)->textContent;
  $date  = $xpath->query('./div[@class="trackYear secondaryText"]', $a)->item(0)->textContent;
  $img   = $xpath->query('./div/a[starts-with(@class, "thumbthumb")]/img/@src', $a)->item(0)->textContent;

  $url = $baseUrl.$url;
  $date = date(DATE_RSS, strtotime($date));
  $items[] = [$title, $date, $url, $img];
}


if (empty($items)) {
  $albums = $xpath->query('//li[starts-with(@class, "music-grid-item square")]');

  foreach ($albums as $a) {
    $title  = $xpath->query('./a/p/text()', $a)->item(0)->textContent;
    $artist = $xpath->query('./a/p/span[@class="artist-override"]', $a);
    $url    = $xpath->query('./a/@href', $a)->item(0)->textContent;
    $img    = $xpath->query('./a/div[@class="art"]/img/@src', $a)->item(0)->textContent;

    $title = trim($title);
    if (strpos($url, 'https') !== 0) {
      $url = $baseUrl.$url;
    }

    $xp = loadFile($url);
    $dateTxt = $xp->query('//meta[@itemprop="datePublished"]/@content')->item(0)->textContent;
    $date = date(DATE_RSS, strtotime($dateTxt));

    $items[] = [$title, $date, $url, $img];
  }
}


$rss = new RSS($feedTitle);
foreach ($items as list($title, $date, $url, $img)) {
  $rss->addItem($title, $url, "<img src='$img'/>", $date);
}

$rss = $rss->toString();
file_put_contents($cacheFile, $rss);
echo $rss;
