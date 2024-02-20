<?php

foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'f' . DIRECTORY_SEPARATOR . '*.php', GLOB_NOSORT) as $v) {
    require $v;
}