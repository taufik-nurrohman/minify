<?php

namespace x\minify {
    function c_s_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $c1 = '-_ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $c2 = '0123456789';
        $c3 = '"\'/[!#()+,:;<>{}~';
        $c4 = " \n\r\t";
        $r1 = '"[^"\\\\]*(?>\\\\.[^"\\\\]*)*"';
        $r2 = "'[^'\\\\]*(?>\\\\.[^'\\\\]*)*'";
        $to = "";
        while (false !== ($chop = \strpbrk($from, $c1 . $c2 . $c3 . $c4))) {
            if ("" !== ($v = \strstr($from, $c = $chop[0], true))) {
                $from = $chop;
                $to .= $v;
            }
            // <https://www.w3.org/TR/css-syntax-3#ident-token-diagram>
            // TODO: Capture escape sequence
            if (false !== \strpos($c1, $c) && \preg_match('/^[a-z_-][a-z\d_-]*/i', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $to .= $m[0];
                continue;
            }
            // <https://www.w3.org/TR/css-syntax-3#number-token-diagram>
            if (false !== \strpos($c2, $c)) {
                if (\preg_match('/^\d+(\.\d+)?(e[+-]?\d+)?\b/i', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    if (false !== \strpos($v = $m[0], '.')) {
                        $v = \rtrim(\trim($v, '0'), '.');
                    } else {
                        $v = \ltrim($v, '0');
                    }
                    $to .= "" === $v ? '0' : $v;
                    continue;
                }
            }
            if ($n = \strspn($chop, $c4)) {
                $from = \substr($from, $n);
                if (
                    false !== \strpos('"\'(', $from[0]) && false === \strpos($c3, \substr($to, -1)) ||
                    false !== \strpos('"\')', \substr($to, -1)) && false === \strpos($c3, $from[0])
                ) {
                    $to .= ' ';
                } else if ("" !== $from . $to && false === \strpos($c3, $from[0]) && false === \strpos($c3, \substr($to, -1))) {
                    $to .= ' ';
                }
                continue;
            }
            if (false !== \strpos('"\'', $c) && \preg_match('/^(?>' . $r1 . '|' . $r2 . ')/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $to .= $m[0];
                continue;
            }
            // `/*…*/`
            if ('/' === $c && '*' === ($chop[1] ?? 0) && \preg_match('/^\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                // `/*!…*/` or `/**…*/`
                if (false !== \strpos('!*', $m[0][2])) {
                    if (false !== \strpos($m[0], "\n")) {
                        $to .= $m[0];
                    } else {
                        $to .= '/*' . \trim(\substr($m[0], 3, -2)) . '*/';
                    }
                } else if ("" !== $to && false === \strpos($c3, \substr($to, -1))) {
                    $to .= ' ';
                }
                continue;
            }
            // `[…]`
            if ('[' === $c && \preg_match('/^\[(?>' . $r1 . '|' . $r2 . '|[^]]+)+\]/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                if ("" !== $to && false !== \strpos($c4, \substr($to, -1))) {
                    $to = \rtrim($to) . ' ';
                }
                // Minify attribute selector(s)
                $to .= '[';
                foreach (\preg_split('/((?>' . $r1 . '|' . $r2 . '|[$*=^|~]|\s+))/', \trim(\substr($m[0], 1, -1)), -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
                    if ("" === ($v = \trim($v))) {
                        continue;
                    }
                    // <https://mothereff.in/unquoted-attributes>
                    if (false !== \strpos('"\'', $v[0]) && "" !== ($test = \substr($v, 1, -1))) {
                        if ('-' === $test || 0 === \strpos($test, '--') || \is_numeric($test[0]) || ('-' === $test[0] && \is_numeric($test[1]))) {
                            $to .= $v;
                            continue;
                        }
                        if (!\preg_match('/^[\w-]+$/', $test)) {
                            $to .= $v;
                            continue;
                        }
                        $to .= $test;
                        continue;
                    }
                    if (false === \strpos('"$\'*=[]^|~', \substr($to, -1)) && false === \strpos('"$\'*=[]^|~', $v[0])) {
                        $to .= ' ';
                    }
                    $to .= $v;
                }
                $to .= ']';
                continue;
            }
            $from = \substr($from, 1);
            if (false !== \strpos(')]', $c)) {
                $to = \rtrim($to, ',');
            } else if ('}' === $c) {
                $to = \rtrim($to, ';');
            }
            $to .= $c;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}