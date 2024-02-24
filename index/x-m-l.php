<?php

namespace x\minify {
    function x_m_l(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $from = \strtr($from, ["\r" => ""]);
        $to = $from;
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}