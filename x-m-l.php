<?php

namespace x\minify {
    function x_m_l(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        return "" !== $to ? $to : null;
    }
}