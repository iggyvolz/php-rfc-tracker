<?php

declare(strict_types=1);

namespace iggyvolz\rfctracker;

use DateTime;
use DOMXPath;
use DOMElement;

class Vote
{
    public string $title = "";
    /**
     * @var list<string>
     */
    public array $choices = [];
    /**
     * @var array<string, int>
     */
    public array $votes = [];
    public static function get(string $title, DOMElement $form, DOMXPath $x): self
    {
        $self = new self();
        $self->init($title, $form, $x);
        return $self;
    }
    private function init(string $title, DOMElement $form, DOMXPath $x): void
    {
        $this->title = $title;
        $choicesElements = $x->query(".//tr[@class='row1']/td", $form);
        foreach ($choicesElements as $elem) {
            $this->choices[] = $elem->textContent;
        }
        $voteElements = $x->query(".//tr", $form);
        foreach ($voteElements as $elem) {
            $voterName = $x->query(".//a", $elem)[0];
            if (!$voterName) {
                continue;
            }
            $voterName = $voterName->textContent;
            $vote = null;
            $tds = $x->query("./td", $elem);
            foreach ($tds as $i => $subelement) {
                if (!($subelement instanceof DOMElement)) {
                    continue;
                }
                if ($subelement->getAttribute("style") === "background-color:#AFA") {
                    $vote = intval($i) - 1;
                }
            }
            if (!is_null($vote)) {
                $this->votes[$voterName] = $vote;
            }
        }
    }
}
