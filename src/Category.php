<?php

declare(strict_types=1);

namespace iggyvolz\rfctracker;

use DOMNode;
use DOMXPath;
use DOMElement;

class Category
{
    public string $name = "";
    /**
     * @var array<string, Category>
     */
    public array $subcategories = [];
    /**
     * @var array<string, ?Rfc>
     */
    public array $rfcs = [];
    /**
     * @param array<string, DOMNode> $subcategories
     */
    public static function get(string $name, ?DOMNode $rfclist, array $subcategories, DOMXPath $x): self
    {
        $self = new self();
        $self->init($name, $rfclist, $subcategories, $x);
        return $self;
    }
    /**
     * @param array<string, DOMNode> $subcategories
     */
    private function init(string $name, ?DOMNode $rfclist, array $subcategories, DOMXPath $x): void
    {
        $this->name = $name;
        foreach ($subcategories as $subname => $subnode) {
            $this->subcategories[$subname] = self::get($subname, $subnode, [], $x);
        }
        $rfcs = is_null($rfclist) ? [] : $x->query("./ul/li/div", $rfclist);
        foreach ($rfcs as $rfc) {
            $link = $x->query(".//a", $rfc)[0];
            if (!($link instanceof DOMElement)) {
                continue;
            }
            $name = substr($link->getAttribute("href"), 5);
            if ($name[0] === "/") {
                continue; // External URL, not an RFC
            }
            $this->rfcs[$name] = Rfc::get($name, $rfc, $x);
        }
    }
}
