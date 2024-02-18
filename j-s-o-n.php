<?php

namespace x\minify {
    function j_s_o_n(?string $content, callable $step = null): ?string {
        if ("" === ($content = \trim($content ?? ""))) {
            return null;
        }
        $chops = [];
        while (false !== ($chop = \strpbrk($content, '",:[]{}'))) {
            if ("" !== ($v = \substr($content, 0, \strlen($content) - \strlen($chop)))) {
                $chops[] = $v;
                $content = \substr($content, \strlen($v));
            }
            if ('"' === $chop[0] && \preg_match('/^"(?>\\"|[^"])*"/', $chop, $m)) {
                $chops[] = $m[0];
                $content = \substr($content, \strlen($m[0]));
                continue;
            }
            if ("" === ($chop = \trim($chop))) {
                continue;
            }
            $chops[] = $chop;
            $content = \substr($content, \strlen($chop));
        }
        if ("" !== $content) {
            $chops[] = $content;
        }
        return "" !== ($content = \implode("", $chops)) ? $content : null;
    }
}