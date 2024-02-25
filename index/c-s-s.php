<?php

// !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
// <https://www.w3.org/TR/CSS22/syndata.html#tokenization>
// <https://www.w3.org/TR/css-syntax-3#token-diagrams>
// <https://www.w3.org/TR/selectors-4>

namespace x\minify {
    function c_s_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $from = \strtr($from, ["\r" => ""]);
        $p = '!"\'()+,-/:;=>[]^{|}~';
        // <https://stackoverflow.com/a/5696141>
        $s = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"' . '|' . "'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'";
        $to = "";
        while (false !== ($chop = \strpbrk($from, '/"\'[' . '!()+,:;>{}~'))) {
            if ("" !== ($v = \substr($from, 0, \strlen($from) - \strlen($chop)))) {
                $from = \substr($from, \strlen($v));
                if (\strlen($to) > 1 && ':' === \substr($to, -1) && false === \strpos(" \n\t", \substr($to, -2, 1))) {
                    // <https://www.w3.org/TR/css-values-4#numeric-types>
                    $v = \preg_replace_callback('/(^|[\s\S])(-?(?:\d*[.])?\d+)(%|Hz|Q|cap|ch|cm|deg|dpcm|dpi|dppx|em|ex|grad|ic|in|kHz|lh|mm|ms|pc|pt|px|rad|rcap|rch|rem|rex|ric|rlh|s|turn|vb|vh|vi|vmax|vmin|vw)\b/', static function ($m) {
                        if ("" !== $m[1] && false !== \stripos('0123456789abcdefghijklmnopqrstuvwxyz', $m[1])) {
                            return $m[0];
                        }
                        return 0 === ((int) $m[2]) && false === \strpos(',%,deg,', ',' . $m[3] . ',') ? $m[1] . '0' : $m[0];
                    }, $v);
                }
                $to .= \preg_replace('/\s+/', ' ', $v);
            }
            if ('/' === $chop[0]) {
                // `/*…*/`
                if ('*' === $chop[1] && \preg_match('/^\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', $chop, $m)) {
                    $from = \ltrim(\substr($from, \strlen($m[0])));
                    // `/*!…*/` or `/**…*/`
                    if (false !== \strpos('!*', $m[0][2])) {
                        $to .= '/*' . \trim(\substr($m[0], 3, -2)) . '*/';
                    } else if ("" !== $to && false === \strpos($p, \substr($to, -1))) {
                        $to .= ' ';
                    }
                    continue;
                }
                $from = \ltrim(\substr($from, 1));
                $to = \rtrim($to) . '/';
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
            // `"…"` or `'…'`
            if (false !== \strpos('"\'', $chop[0]) && \preg_match('/^(?>' . $s . ')/s', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $to .= $m[0];
                continue;
            }
            if (false !== \strpos('+,>~', $chop[0])) {
                $from = \ltrim(\substr($from, 1));
                $to = \rtrim($to) . $chop[0];
                continue;
            }
            // `[…]`
            if ('[' === $chop[0] && \preg_match('/^\[(?>' . $s . '|[^]])+\]/s', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                if ("" !== $to && false !== \strpos(" \n\t", \substr($to, -1))) {
                    $to = \rtrim($to) . ' ';
                }
                // Minify attribute selector(s)
                $to .= '[';
                foreach (\preg_split('/(' . $s . '|[$*=^|~]|\s+)/s', \trim(\substr($m[0], 1, -1)), -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
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
            if (false !== \strpos('!():;{}', $chop[0])) {
                if ('(' === $chop[0]) {
                    if ('calc' === \substr($to, -4) && false !== \strpos($p . ' ', \substr($to, -5, 1)) && \preg_match('/\((?>[^()]+|(?R))*\)/', $chop, $m)) {
                        $from = \substr($from, \strlen($m[0]));
                        $to .= '(' . \trim(\preg_replace(['/\s+/', '/\s*([()*,\/])\s*/'], [' ', '$1'], \substr($m[0], 1, -1))) . ')';
                        continue;
                    }
                    if ('format' === \substr($to, -6) && false !== \strpos($p . ' ', \substr($to, -7, 1)) && \preg_match('/^\(\s*(' . $s . ')\s*\)/', $chop, $m)) {
                        $from = \substr($from, \strlen($m[0]));
                        $v = \substr($type = \trim(\substr($m[0], 1, -1)), 1, -1);
                        // <https://drafts.csswg.org/css-fonts#font-face-src-parsing>
                        if (false !== \strpos(',collection,embedded-opentype,opentype,svg,truetype,woff,woff2,', ',' . $v . ',')) {
                            $type = \substr($type, 1, -1);
                        } else if (false !== \strpos(',woff-variations,woff2-variations,truetype-variations,opentype-variations,', ',' . $v . ',')) {
                            $type = \strtr(\substr($type, 1, -1), ['-' => ') tech(']);
                        }
                        $to .= '(' . $type . ')';
                        continue;
                    }
                    if ('tech' === \substr($to, -4) && false !== \strpos($p . ' ', \substr($to, -5, 1)) && \preg_match('/^\(\s*(' . $s . ')\s*\)/', $chop, $m)) {
                        $from = \substr($from, \strlen($m[0]));
                        $v = \substr($type = \trim(\substr($m[0], 1, -1)), 1, -1);
                        // <https://drafts.csswg.org/css-fonts#font-face-src-parsing>
                        if (false !== \strpos(',color-COLRv0,color-COLRv1,color-SVG,color-sbix,color-CBDT,features-opentype,features-aat,features-graphite,incremental,palettes,variations,', ',' . $v . ',')) {
                            $type = \substr($type, 1, -1);
                        }
                        $to .= '(' . $type . ')';
                        continue;
                    }
                    if ('url' === \substr($to, -3) && false !== \strpos($p . ' ', \substr($to, -4, 1)) && \preg_match('/^\(\s*(' . $s . ')\s*\)/', $chop, $m)) {
                        $from = \substr($from, \strlen($m[0]));
                        $v = \substr($link = \trim(\substr($m[0], 1, -1)), 1, -1);
                        if (\strcspn($v, '"\'()') === \strlen($v)) {
                            $link = \substr($link, 1, -1);
                        }
                        $to .= '(' . $link . ')';
                        continue;
                    }
                    $from = \ltrim(\substr($from, 1));
                    if (false !== \strpos(" \n\t", \substr($to, -1))) {
                        $to = \rtrim($to) . ' ';
                    }
                    if (\strlen($to) > 1 && false !== \strpos($p, \substr($to, -2, 1))) {
                        $to = \substr($to, 0, -1);
                    }
                    $to .= '(';
                    continue;
                }
                if (')' === $chop[0]) {
                    $from = \substr($from, 1);
                    if (false !== \strpos(" \n\t", $from[0])) {
                        $from = ' ' . \ltrim($from);
                    }
                    if (\strlen($from) > 1 && false !== \strpos($p, $from[1])) {
                        $from = \substr($from, 1);
                    }
                    $to = \rtrim($to) . ')';
                    continue;
                }
                if (':' === $chop[0] && \strlen($chop) > 1 && false === \strpos(" \n\t", $chop[1])) {
                    $from = \ltrim(\substr($from, 1));
                    if (false !== \strpos(" \n\t", \substr($to, -1))) {
                        $to = \rtrim($to) . ' ';
                    }
                    $to .= ':';
                    continue;
                }
                if (';' === $chop[0]) {
                    $from = \ltrim(\substr($from, 1));
                    $to = \rtrim($to) . ';';
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
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}