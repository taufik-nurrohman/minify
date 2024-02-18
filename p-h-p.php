<?php

namespace x\minify {
    function p_h_p(?string $content, callable $step = null): ?string {
        if ("" === ($content = \trim($content ?? ""))) {
            return null;
        }
        return "" !== $content ? $content : null;
    }
}