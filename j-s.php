<?php

namespace x\minify {
    function j_s(?string $content, callable $step = null): ?string {
        if ("" === ($content = \trim($content ?? ""))) {
            return null;
        }
        return "" !== $content ? $content : null;
    }
}