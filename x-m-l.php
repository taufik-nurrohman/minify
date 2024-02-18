<?php

namespace x\minify {
    function x_m_l(?string $content, callable $step = null): ?string {
        if ("" === ($content = \trim($content ?? ""))) {
            return null;
        }
        return "" !== $content ? $content : null;
    }
}