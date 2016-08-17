<?php
require_once('Michelf/Markdown.inc.php');
use \Michelf\Markdown;

function defaultTransform($md) {
    $html = Markdown::defaultTransform($md);
    return $html;
}