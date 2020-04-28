<?php

declare(strict_types=1);

namespace iggyvolz\rfctracker;

use DOMNode;
use DOMXPath;
use DOMElement;

class Rfc
{
    public string $name = "";
    public string $description = "";
    public ?string $version = null;
    public ?string $date = null;
    public ?string $author = null;
    public ?string $status = null;
    public string $link = "";
    /**
     * @var array<string, Vote>
     */
    public array $votes = [];
    public static function get(string $link, DOMNode $entry, DOMXPath $x): self
    {
        $self = new self();
        $self->init($link, $entry, $x);
        return $self;
    }
    public function init(string $link, DOMNode $entry, DOMXPath $x): void
    {
        $this->link = $link;
        // Get description
        foreach ($x->query(".//text()", $entry) as $node) {
            if (!($node->parentNode instanceof DOMElement) || $node->parentNode->tagName === "a") {
                continue;
            }
            $this->description .= $node->textContent;
        }
        // Remove metadata such as created date
        foreach (
            [
                "Created",
                "Discussion",
                "Put under discussion",
                "Accepted",
                "Announced",
                "Published",
                "Revived",
                "Vote",
                "Postponed",
                "Other",
                "Re-opened",
                "Re-created",
                "Updated"
            ] as $metadatastring
        ) {
            $this->description = preg_replace(
                "/\\(" . preg_quote($metadatastring, "/") . "[^\\)]+\\)/",
                "",
                $this->description
            );
        }
        // One of them missed an open paren
        $this->description = preg_replace("/\\Created:[^\\)]+\\)/", "", $this->description);
        // Trim spaces and periods from string
        $this->description = trim($this->description, " \t\n\r\0\x0B.");
        // Get name
        $this->name = $x->query(".//a", $entry)[0]->textContent;

        // Get contents from RFC page
        $x = Utilities::getURL("https://wiki.php.net/rfc/$link");
        foreach ($x->query("//li") as $li) {
            $text = trim($li->textContent);
            if (strpos($text, "Version: ") === 0) {
                $this->version = substr($text, 9);
            }
            if (strpos($text, "Date: ") === 0) {
                $this->date = substr($text, 6);
            }
            if (strpos($text, "Author: ") === 0) {
                $this->author = substr($text, 8);
            }
            if (strpos($text, "Status: ") === 0) {
                $this->status = substr($text, 8);
            }
        }

        // Get vote elements
        foreach ($x->query("//form[@name='doodle__form']") as $form) {
            if (!($form instanceof DOMElement)) {
                continue;
            }
            // Get vote title
            $title = trim($x->query(".//th", $form)[0]->textContent);
            $this->votes[$title] = Vote::get($title, $form, $x);
        }
    }
}
