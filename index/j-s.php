<?php

namespace x\minify {
    function j_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $c1 = '$_ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $c2 = '0123456789';
        $c3 = '`"\'/!#%&()*+,-.:;<=>?@[\]^`{|}~'; // Punctuation(s) but `$` and `_`
        $c4 = " \n\r\t";
        $to = "";
        while (false !== ($chop = \strpbrk($from, $c1 . $c2 . $c3 . $c4))) {
            if ("" !== ($v = \strstr($from, $c = $chop[0], true))) {
                $from = $chop;
                $to .= $v;
            }
            if (false !== \strpos($c1, $c) && \preg_match('/^[a-z$_][\w$]*\b(?!\$)/i', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                if ('false' === $m[0]) {
                    $to .= '!1';
                } else if ('true' === $m[0]) {
                    $to .= '!0';
                } else {
                    $to .= $m[0];
                }
                continue;
            }
            if (false !== \strpos($c2, $c)) {
                // `0b0`, `0o0`, `0x0`, `0b0n`, `0o0n`, `0x0n`
                if (\preg_match('/^0(b[01]+(_[01]+)*|o[0-7]+(_[0-7]+)?|x[a-f\d]+(_[a-f\d]+)*)n?\b/i', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $to .= \strtr($m[0], ['_' => ""]);
                    continue;
                }
                // `0`, `0n`, `0e0`, `0e+0`, `0e-0`, `0.0`, `0.0e0`, `0.0e+0, `0.0e-0`
                if (\preg_match('/^\d+(_\d+)*(n|(\.\d+(_\d+)*)?(e[+-]?\d+(_\d+)*)?)\b/i', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $v = \strtr($m[0], ['_' => ""]);
                    if (false !== \strpos($v, '.')) {
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
                if ("" !== $from . $to && false === \strpos($c3, $from[0]) && false === \strpos($c3, \substr($to, -1))) {
                    $to .= ' ';
                }
                continue;
            }
            if (
                // ``…``
                '`' === $c && \preg_match('/^`[^`\\\\]*(?>\\\\.[^`\\\\]*)*`/', $chop, $m) ||
                // `"…"`
                '"' === $c && \preg_match('/^"[^"\\\\]*(?>\\\\.[^"\\\\]*)*"/', $chop, $m) ||
                // `'…'`
                "'" === $c && \preg_match("/^'[^'\\\\]*(?>\\\\.[^'\\\\]*)*'/", $chop, $m)
            ) {
                $from = \substr($from, \strlen($m[0]));
                if ('`' === $c && false !== \strpos($m[0], '${')) {
                    // `${…}`
                    $to .= \preg_replace_callback('/\$(\{[^}\\\\]*(?>\\\\.[^}\\\\]*)*\})/', static function ($m) {
                        return j_s($m[0]);
                    }, $m[0]);
                    continue;
                }
                $to .= $m[0];
                continue;
            }
            if ('/' === $c) {
                $test = $chop[1] ?? 0;
                // `/*…*/`
                if ('*' === $test && \preg_match('/^\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    // `/*!…*/` or `/**…*/`
                    if (false !== \strpos('!*', $m[0][2])) {
                        if (false !== \strpos($m[0], "\n")) {
                            $to .= $m[0];
                        } else {
                            $to .= '/*' . \trim(\substr($m[0], 3, -2)) . '*/';
                        }
                    }
                    continue;
                }
                // `//…`
                if ('/' === $test) {
                    $from = \substr($from, \strpos($chop . "\n", "\n") + 1);
                    continue;
                }
                // `/…/i`
                if (\preg_match('/^\/[^\/\\\\]*(?>\\\\.[^\/\\\\]*)*\/[gimsuy]?\b/', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $to .= $m[0];
                    continue;
                }
                $from = \substr($from, 1);
                $to .= $c;
                continue;
            }
            $from = \substr($from, 1);
            if (false !== \strpos(')]', $c)) {
                $to = \rtrim($to, ','); // `(a,b,[a,b,c,],)` to `(a,b,[a,b,c])`
            } else if ('}' === $c) {
                $to = \rtrim($to, ';'); // `{a;b;c;}` to `{a;b;c}`
            }
            $to .= $c;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}