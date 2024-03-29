<?php

namespace x\minify {
    function p_h_p(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $from = \strtr($from, ["\r" => ""]);
        $to = $from;
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}