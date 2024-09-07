<?php

namespace x\minify {
    function j_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $r = '`"\'/' . '!#$%&()*+,-.:;<=>?@[\]^_`{|}~ ' . "\n\r\t";
        $to = "";
        while (false !== ($chop = \strpbrk($from, $r))) {
            if ("" !== ($v = \strstr($from, $chop[0], true))) {
                $from = $chop;
                $to .= j_s\n($v);
            }
            if (false !== \strpos(" \n\r\t", $chop[0])) {
                // TODO
                // echo '<pre style="border:1px solid">';
                // echo json_encode($chop);
                // echo '</pre>';
                $from = \substr($from, 1);
                continue;
            }
            if (
                '`' === $chop[0] && \preg_match('/^`[^`\\\\]*(?>\\\\.[^`\\\\]*)*`/', $chop, $m) ||
                '"' === $chop[0] && \preg_match('/^"[^"\\\\]*(?>\\\\.[^"\\\\]*)*"/', $chop, $m) ||
                "'" === $chop[0] && \preg_match('/^\'[^\'\\\\]*(?>\\\\.[^\'\\\\]*)*\'/', $chop, $m)
            ) {
                $from = \substr($from, \strlen($m[0]));
                if ('`' === $chop[0] && false !== \strpos($m[0], '${')) {
                    $m[0] = \preg_replace_callback('/\$(\{[^{}\\\\]*(?>\\\\.[^{}\\\\]*)*\})/', static function ($m) {
                        return j_s($m[0]);
                    }, $m[0]);
                }
                $to .= $m[0];
                continue;
            }
            if ('/' === $chop[0]) {
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
                    $from = \substr($from, \strlen(\strstr($chop, "\n", true)));
                    continue;
                }
                // Look like a regular expression <https://javascript.info/regexp-introduction#flags>
                if (\preg_match('/^\/[^\/\\\\]*(?>\\\\.[^\/\\\\]*)*\/[gimsuy]?\b/', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $to .= $m[0];
                    continue;
                }
                if (false !== \strpos($r, $chop[0])) {
                    $from = \ltrim(\substr($from, 1));
                    $to .= $chop[0];
                    continue;
                }
                $from = \substr($from, 1);
                $to .= $chop[0];
                continue;
            }
            if (false !== \strpos($r, $chop[0])) {
                $from = \ltrim(\substr($from, 1));
                if (false !== \strpos('$_', $chop[0]) && \preg_match('/\w/', \substr($to, -1))) {
                    // Fix case for `var $asdf` or `var _asdf`
                    $to .= ' ';
                } else if (false !== \strpos(')]', $chop[0])) {
                    $to = \rtrim($to, ',');
                } else if ('}' === $chop[0]) {
                    $to = \rtrim($to, ';');
                }
                $to .= $chop[0];
                continue;
            }
            $from = "";
            $to .= j_s\n($chop);
        }
        if ("" !== $from) {
            $to .= j_s\n($from);
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}

namespace x\minify\j_s {
    function n(string $from): string {
        return \trim(\preg_replace('/\s+/', ' ', $from));
    }
}