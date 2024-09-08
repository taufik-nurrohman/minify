<?php

namespace x\minify {
    function j_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $c1 = '0123456789';
        $c2 = '`"\'/' . '!#%&()*+,-.:;<=>?@[\]^`{|}~'; // Punctuation(s) but `$` and `_`
        $c3 = " \n\r\t";
        $to = "";
        while (false !== ($chop = \strpbrk($from, $c1 . 'ft' . $c2 . $c3))) {
            if ("" !== ($v = \strstr($from, $c = $chop[0], true))) {
                $from = $chop;
                $to .= $v;
            }
            if ($n = \strspn($chop, $c3)) {
                $from = \substr($from, $n);
                if ("" !== $from && "" !== $to && false === \strpos($c2, $from[0]) && false === \strpos($c2, \substr($to, -1))) {
                    $to .= ' ';
                }
                continue;
            }
            if ('f' === $c && \preg_match('/^false\b/', $chop)) {
                $from = \substr($from, 5);
                $to .= '!1';
                continue;
            }
            if ('t' === $c && \preg_match('/^true\b/', $chop)) {
                $from = \substr($from, 4);
                $to .= '!0';
                continue;
            }
            if (false !== \strpos($c1, $c) && \preg_match('/^(\d+(_\d+)*)*\.\d+(_\d+)*\b/', $chop, $m)) {
                // TODO
            }
            if (
                '`' === $c && \preg_match('/^`[^`\\\\]*(?>\\\\.[^`\\\\]*)*`/', $chop, $m) ||
                '"' === $c && \preg_match('/^"[^"\\\\]*(?>\\\\.[^"\\\\]*)*"/', $chop, $m) ||
                "'" === $c && \preg_match('/^\'[^\'\\\\]*(?>\\\\.[^\'\\\\]*)*\'/', $chop, $m)
            ) {
                $from = \substr($from, \strlen($m[0]));
                if ('`' === $c && false !== \strpos($m[0], '${')) {
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
            if (false !== \strpos($c2, $c)) {
                $from = \substr($from, 1);
                if (false !== \strpos(')]', $c)) {
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