<?php

declare(strict_types=1);

namespace iggyvolz\rfctracker;

use DOMXPath;
use DOMDocument;

class Utilities
{
    private function __construct()
    {
    }
    private static function getFromURL(string $url): string
    {
        $key = hash("sha256", $url);
        $cachefile = __DIR__ . "/../cache/$key";
        if (file_exists($cachefile)) {
            return file_get_contents($cachefile);
        }
        $conts = file_get_contents($url);
        file_put_contents($cachefile, $conts);
        return $conts;
    }
    public static function getURL(string $url): DOMXPath
    {
        $conts = self::getFromURL($url);
        $dom = new DOMDocument();
        $old = libxml_use_internal_errors(true);
        $dom->loadHTML($conts);
        libxml_use_internal_errors($old);
        return new DOMXPath($dom);
    }
}
