<?php

class RSS {
  private $rss;

  function __construct(string $title, string $description = '', string $link = '') {
    $this->rss = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss version="2.0"/>');
    $this->rss->channel->title = $title;
    $this->rss->channel->description = $description;
    $this->rss->channel->link = $link;
  }

  function addItem(string $title, string $url, string $body = '', $date = null): SimpleXMLElement {
    $item = $this->rss->channel->addChild('item');
    $item->title = $title;
    $item->guid = $url;
    $item->guid['isPermaLink'] = 'true';
    $item->description = $body;
    $item->pubDate = is_int($date) ? date(DATE_RSS, $date) : $date;
    return $item;
  }

  function toString(): string {
    return $this->rss->asXML();
  }

  function __toString(): string {
    return $this->rss->asXML();
  }
}
