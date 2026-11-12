<?php

foreach (['css', 'html', 'js', 'json', 'php', 'xml'] as $x) {
    if (!function_exists("x\\minify\\x\\" . $x)) {
        require __DIR__ . DIRECTORY_SEPARATOR . 'x' . DIRECTORY_SEPARATOR . $x . '.php';
    }
}