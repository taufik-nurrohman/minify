<?php

// <https://www.w3.org/TR/CSS22/syndata.html#tokenization>
// <https://www.w3.org/TR/css-syntax-3#token-diagrams>
// <https://www.w3.org/TR/selectors-4>

namespace x\minify {
    function c_s_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $p = '!"\'()+,-/:;=>[]^{|}~'; // !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
        $s = '"(?>\\\\"|[^"])*"|\'(?>\\\\\'|[^\'])*\'';
        $to = "";
        while (false !== ($chop = \strpbrk($from, '/"\'[' . '()+,:>{}~'))) {
            if ("" !== ($v = \substr($from, 0, \strlen($from) - \strlen($chop)))) {
                $from = \substr($from, \strlen($v));
                $to .= \preg_replace('/\s+/', ' ', $v);
            }
            if ('/' === $chop[0]) {
                // `/* … */`
                if ('*' === $chop[1] && \preg_match('/^\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', $chop, $m)) {
                    $from = \ltrim(\substr($from, \strlen($m[0])));
                    // `/*! … */` or `/** … */`
                    if (false !== \strpos('!*', $m[0][2])) {
                        $to .= '/*' . \trim(\substr($m[0], 3, -2)) . '*/';
                    } else if ("" !== $to && false === \strpos($p, \substr($to, -1))) {
                        $to .= ' ';
                    }
                    continue;
                }
                $from = \substr($from, 1);
                $to .= '/';
                continue;
            }
            if ('""' === \substr($chop, 0, 2)) {
                $from = \substr($from, 2);
                $to .= '""';
                continue;
            }
            if ("''" === \substr($chop, 0, 2)) {
                $from = \substr($from, 2);
                $to .= "''";
                continue;
            }
            // `" … "` or `' … '`
            if (false !== \strpos('"\'', $chop[0]) && \preg_match('/^(?>' . $s . ')/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $to .= $m[0];
                continue;
            }
            if (false !== \strpos('+,>~', $chop[0])) {
                $from = \ltrim(\substr($from, 1));
                $to = \rtrim($to) . $chop[0];
                continue;
            }
            // `[ … ]`
            if ('[' === $chop[0] && \preg_match('/^\[(?>' . $s . '|[^]])+\]/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                if ("" !== $to && false !== \strpos(" \n\r\t", \substr($to, -1))) {
                    $to = \rtrim($to) . ' ';
                }
                // Minify attribute selector(s)
                $to .= '[';
                foreach (\preg_split('/(' . $s . '|[$*=^|~]|\s+)/', \trim(\substr($m[0], 1, -1)), -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
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
            if (false !== \strpos('():{}', $chop[0])) {
                if ('(' === $chop[0]) {
                    $from = \ltrim(\substr($from, 1));
                    if (false !== \strpos(" \n\r\t", \substr($to, -1))) {
                        $to = \rtrim($to) . ' ';
                    }
                    $to .= '(';
                    continue;
                }
                if (')' === $chop[0]) {
                    if (false !== \strpos(" \n\r\t", $from[0])) {
                        $from = ' ' . \ltrim($from);
                    }
                    $to = \rtrim($to) . ')';
                    continue;
                }
                if (':' === $chop[0] && \preg_match('/^::?[a-z-][a-z\d-]*(?=[(+>[{~\s])/', $chop, $m)) {
                    $from = \ltrim(\substr($from, \strlen($m[0])));
                    if (false !== \strpos(" \n\r\t", \substr($to, -1))) {
                        $to = \rtrim($to) . ' ';
                    }
                    $to .= $m[0];
                    continue;
                }
                $from = \ltrim(\substr($from, 1));
                $to = \rtrim($to);
                if ('}' === $chop[0]) {
                    $to = \rtrim($to, ';'); // Drop last semi-colon(s)
                }
                $to = \rtrim($to) . $chop[0];
                continue;
            }
            $from = \substr($from, \strlen($chop));
            $to .= \preg_replace('/\s+/', ' ', $chop);
        }
        if ("" !== $from) {
            $to .= \preg_replace('/\s+/', ' ', $from);
        }
        return "" !== $to ? $to : null;
    }
}