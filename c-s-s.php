<?php

// <https://www.w3.org/TR/css-syntax-3#token-diagrams>

namespace x\minify {
    function c_s_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $to = "";
        while (false !== ($chop = \strpbrk($from, '/[{'))) {
            if ("" !== ($v = \substr($from, 0, \strlen($from) - \strlen($chop)))) {
                $from = \substr($from, \strlen($v));
                $to .= \trim($v);
            }
            // `/* … */`
            if (0 === \strpos($chop, '/*') && \preg_match('/^\/\*[^*]*\*+([^/*][^*]*\*+)*\//', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                // `/*! … */` or `/** … */`
                if (false !== \strpos('!*', $m[0][2])) {
                    $to .= '/*' . \trim(\substr($m[0], 3, -2)) . '*/';
                }
                continue;
            }
            if ('[' === $chop[0]) {}
            if ('{' === $chop[0]) {}
            $from = \substr($from, \strlen($chop));
            $to .= $chop;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== $to ? $to : null;
    }
}