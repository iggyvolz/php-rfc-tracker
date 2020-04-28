<?php

declare(strict_types=1);

namespace iggyvolz\rfctracker;

use DOMElement;

class RfcTracker
{
    /**
     * @var array<string, Category>
     */
    public array $categories = [];
    public static function run(): void
    {
        echo self::get()->__toString();
    }
    private static function get(): self
    {
        $self = new self();
        $self->init();
        return $self;
    }
    private function init(): void
    {
        $x = Utilities::getURL("https://wiki.php.net/rfc");
        $h2s = $x->query("//h2");
        foreach ($h2s as $h2) {
            $name = $h2->textContent;
            $item = $h2;
            $rfclist = null;
            $subcategories = [];
            $subcategoryName = null;
            while (true) {
                $item = $item->nextSibling;
                if (is_null($item)) {
                    // Ran out of siblings
                    break;
                }
                if (!($item instanceof DOMElement)) {
                    // Likely a text node, skip
                    continue;
                }
                if ($item->tagName === "h2") {
                    // Ran out of siblings
                    break;
                }
                if ($item->tagName === "h3") {
                    // Child category
                    $subcategoryName = $item->textContent;
                }
                if ($item->tagName === "div") {
                    if (is_null($subcategoryName)) {
                        $rfclist = $item;
                    } else {
                        $subcategories[$subcategoryName] = $item;
                    }
                }
            }
            $this->categories[$name] = Category::get($name, $rfclist, $subcategories, $x);
        }
    }
    /**
     * @psalm-suppress InvalidToString
     * -> https://github.com/vimeo/psalm/issues/2996
     */
    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }
}
