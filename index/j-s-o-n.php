<?php

namespace x\minify {
    function j_s_o_n(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        if ('""' === $from || '[]' === $from || 'false' === $from || 'null' === $from || 'true' === $from || '{}' === $from || \is_numeric($from)) {
            return $from;
        }
        $to = "";
        while (false !== ($chop = \strpbrk($from, '",:[]{}'))) {
            if ("" !== ($v = \substr($from, 0, \strlen($from) - \strlen($chop)))) {
                $from = \substr($from, \strlen($v));
                $to .= \trim($v);
            }
            if (false !== \strpos(',:[]{}', $chop[0])) {
                $from = \substr($from, 1);
                $to .= $chop[0];
                continue;
            }
            if ('""' === \substr($chop, 0, 2)) {
                $from = \substr($from, 2);
                $to .= '""';
                continue;
            }
            if ('"' === $chop[0] && \preg_match('/^"[^"\\\\]*(?>\\\\.[^"\\\\]*)*"/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $to .= $m[0];
                continue;
            }
            $from = \substr($from, \strlen($chop));
            $to .= $chop;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}