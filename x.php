<?php

$folder = __DIR__ . DIRECTORY_SEPARATOR . 'x' . DIRECTORY_SEPARATOR;

foreach (['css', 'html', 'js', 'json', 'php', 'xml'] as $x) {
    require __DIR__ . DIRECTORY_SEPARATOR . 'x' . DIRECTORY_SEPARATOR . $x . '.php';
}