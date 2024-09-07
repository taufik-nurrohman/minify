<?php

namespace x\minify {
    function j_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $r = '`"\'/' . '!#$%&()*+,-.:;<=>?@[\]^_`{|}~';
        $to = "";
        while (false !== ($chop = \strpbrk($from, $r . " \n\r\t"))) {
            if ("" !== ($v = \strstr($from, $c = $chop[0], true))) {
                $from = $chop;
                if ('.' === $c && ('0' === $v || \strspn('0', $v) === \strlen($v))) {
                    // Remove `0.` prefix
                } else if ('.' === \substr($to, -1) && false !== \strpos($r . '0123456789', \substr($to, -2, 1))) {
                    if ("" !== ($v = \rtrim($v, '0'))) {
                        $to .= $v;
                    } else {
                        $to = \substr($to, 0, -1) . '0';
                    }
                } else {
                    $to .= $v;
                }
            }
            if ($n = \strspn($chop, " \n\r\t")) {
                $from = \substr($from, $n);
                if ("" !== $from && "" !== $to && false === \strpos($r, $from[0]) && false === \strpos($r, \substr($to, -1))) {
                    $to .= ' ';
                }
                continue;
            }
            if (
                '`' === $c && \preg_match('/^`[^`\\\\]*(?>\\\\.[^`\\\\]*)*`/', $chop, $m) ||
                '"' === $c && \preg_match('/^"[^"\\\\]*(?>\\\\.[^"\\\\]*)*"/', $chop, $m) ||
                "'" === $c && \preg_match('/^\'[^\'\\\\]*(?>\\\\.[^\'\\\\]*)*\'/', $chop, $m)
            ) {
                $from = \substr($from, \strlen($m[0]));
                if ('`' === $c && false !== \strpos($m[0], '${')) {
                    $to .= \preg_replace_callback('/\$(\{[^{}\\\\]*(?>\\\\.[^{}\\\\]*)*\})/', static function ($m) {
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
                    $from = \substr($from, \strpos($chop, "\n") + 1);
                    continue;
                }
                // Look like a regular expression <https://javascript.info/regexp-introduction#flags>
                if (\preg_match('/^\/[^\/\\\\]*(?>\\\\.[^\/\\\\]*)*\/[gimsuy]?\b/', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $to .= $m[0];
                    continue;
                }
                $from = \substr($from, 1);
                $to .= $chop[0];
                continue;
            }
            if (false !== \strpos($r, $c)) {
                $from = \substr($from, 1);
                if (false !== \strpos('$_', $c) && \preg_match('/\w/', \substr($to, -1))) {
                    // Fix case for `var $asdf` or `var _asdf`
                    $to .= ' ';
                } else if (false !== \strpos(')]', $c)) {
                    $to = \rtrim($to, ',');
                } else if ('}' === $c) {
                    $to = \rtrim($to, ';');
                }
                $to .= $c;
                continue;
            }
            $from = "";
            $to .= $chop;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}