<?php

namespace x\minify {
    function j_s_o_n(?string $from, callable $step = null): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $to = "";
        while (false !== ($chop = \strpbrk($from, '",:[]{}'))) {
            if ("" !== ($v = \substr($from, 0, \strlen($from) - \strlen($chop)))) {
                $to .= $step ? \call_user_func($step, $v, $to) : $v;
                $from = \substr($from, \strlen($v));
            }
            if ('"' === $chop[0] && \preg_match('/^"(?>\\"|[^"])*"/', $chop, $m)) {
                $to .= $step ? \call_user_func($step, $m[0], $to) : $m[0];
                $from = \substr($from, \strlen($m[0]));
                continue;
            }
            if ("" === ($v = \trim($chop))) {
                continue;
            }
            $to .= $step ? \call_user_func($step, $v, $to) : $v;
            $from = \substr($from, \strlen($chop));
        }
        if ("" !== $from) {
            $to .= $step ? \call_user_func($step, $from, $to);
        }
        return "" !== $to ? $to : null;
    }
}
