<?php

namespace x\minify\x {
    function css(?string $value): ?string {
        return css\s(css\a($value ?? ""));
    }
    function css2(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $c1 = '-_ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $c2 = '0123456789';
        $c3 = '"\'/[!#()+,:;<>{}~';
        $c4 = " \n\r\t";
        $r1 = '"[^"\\\\]*(?>\\\\.[^"\\\\]*)*"';
        $r2 = "'[^'\\\\]*(?>\\\\.[^'\\\\]*)*'";
        $r3 = $r1 . '|' . $r2;
        $to = "";
        while (false !== ($chop = \strpbrk($from, $c1 . $c2 . $c3 . $c4))) {
            if ("" !== ($v = \strstr($from, $c = $chop[0], true))) {
                $from = $chop;
                $to .= $v;
            }
            // <https://www.w3.org/TR/css-color-4#the-hsl-notation>
            if ('h' === $c && (0 === \strpos($chop, 'hsl(') || 0 === \strpos($chop, 'hsla(')) && \preg_match('/^hsla?\(\s*(none|\d*\.?\d+(?:deg)?)\s*[,\s]\s*(none|\d*\.?\d+%?)\s*[,\s]\s*(none|\d*\.?\d+%?)\s*(?:[,\/]\s*(none|\d*\.?\d+%?)\s*)?\)/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $h = 'none' === $m[1] ? 0 : (float) ('deg' === \substr($m[1], -3) ? \substr($m[1], 0, -3) : $m[1]);
                $s = 'none' === $m[2] ? 0 : (float) ('%' === \substr($m[2], -1) ? \substr($m[2], 0, -1) : $m[2]);
                $l = 'none' === $m[3] ? 0 : (float) ('%' === \substr($m[3], -1) ? \substr($m[3], 0, -1) : $m[3]);
                $a = isset($m[4]) ? ('none' === $m[4] ? 0 : (float) ('%' === \substr($m[4], -1) ? \substr($m[4], 0, -1) : $m[4])) : 1;
                // <https://www.w3.org/TR/css-color-4#hsl-to-rgb>
                $h = $h % 360;
                if ($h < 0) {
                    $h += 360;
                }
                $s /= 100;
                $l /= 100;
                $f = static function ($n) use ($h, $l, $s) {
                    $a = $s * \min($l, 1 - $l);
                    $k = ($n + $h / 30) % 12;
                    return $l - $a * \max(-1, \min($k - 3, 9 - $k, 1));
                };
                $to .= css(\sprintf('#%02x%02x%02x%02x', $f(0) * 255, $f(8) * 255, $f(4) * 255, $a * 255));
                continue;
            }
            // <https://www.w3.org/TR/css-color-4#rgb-functions>
            if ('r' === $c && (0 === \strpos($chop, 'rgb(') || 0 === \strpos($chop, 'rgba(')) && \preg_match('/^rgba?\(\s*(none|\d*\.?\d+%?)\s*[,\s]\s*(none|\d*\.?\d+%?)\s*[,\s]\s*(none|\d*\.?\d+%?)\s*(?:[,\/]\s*(none|\d*\.?\d+%?)\s*)?\)/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $r = 'none' === $m[1] ? 0 : ('%' === \substr($m[1], -1) ? (255 * (((float) \substr($m[1], 0, -1)) / 100)) : (float) $m[1]);
                $g = 'none' === $m[2] ? 0 : ('%' === \substr($m[2], -1) ? (255 * (((float) \substr($m[2], 0, -1)) / 100)) : (float) $m[2]);
                $b = 'none' === $m[3] ? 0 : ('%' === \substr($m[3], -1) ? (255 * (((float) \substr($m[3], 0, -1)) / 100)) : (float) $m[3]);
                $a = (isset($m[4]) ? ('none' === $m[4] ? 0 : ('%' === \substr($m[4], -1) ? (1 * (((float) \substr($m[4], 0, -1)) / 100)) : (float) $m[4])) : 1) * 255;
                $x = \sprintf('#%02x%02x%02x%02x', $r < 0 ? 0 : ($r > 255 ? 255 : $r), $g < 0 ? 0 : ($g > 255 ? 255 : $g), $b < 0 ? 0 : ($b > 255 ? 255 : $b), $a < 0 ? 0 : ($a > 255 ? 255 : $a));
                $to .= css($x);
                continue;
            }
            // <https://www.w3.org/TR/css-values-4#numeric-types>
            if (false !== \strpos($c1, $c) && '0' === \substr($to, -1) && \preg_match('/[*+,\/\s:-]0$/', $to) && \preg_match('/^(?>Hz|Q|cap|ch|cm|dpcm|dpi|dppx|em|ex|grad|ic|in|kHz|lh|mm|ms|pc|pt|px|rad|rcap|rch|rem|rex|ric|rlh|s|turn|vb|vh|vi|vmax|vmin|vw)\b/', $chop, $m)) {
                // Remove unit after zero value except `%` and `deg`
                $from = \substr($from, \strlen($m[0]));
                continue;
            }
            // <https://www.w3.org/TR/css-syntax-3#ident-token-diagram>
            if (false !== \strpos("\\" . $c1, $c) && \preg_match('/^(?>\\\\[a-f\d]+\s+|\\\\.|[a-z_-])(?>\\\\[a-f\d]+\s+|\\\\.|[a-z\d_-])*/i', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                // <https://www.w3.org/TR/css-values-4#calc-syntax>
                if (0 === \strpos($from, '(') && false !== \strpos(',abs,acos,asin,atan,atan2,calc,clamp,cos,exp,hypot,log,max,min,mod,pow,rem,round,sign,sin,sqrt,tan,', ',' . $m[0] . ',') && \preg_match('/^\([^;}]+\)/', $from, $n)) {
                    $from = \substr($from, \strlen($n[0]));
                    // Remove space(s) around `*` and `/`
                    $to .= $m[0] . \preg_replace('/\s*([*\/])\s*/', '$1', css($n[0]));
                    continue;
                }
                $to .= \preg_replace('/\s+/', ' ', $m[0]);
                continue;
            }
            // <https://www.w3.org/TR/css-syntax-3#number-token-diagram>
            if (false !== \strpos($c2, $c)) {
                if (\preg_match('/^\d+(\.\d+)?(e[+-]?\d+)?\b/i', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    if (false !== \strpos($v = $m[0], '.') || '.' === \substr($to, -1)) {
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
                if ("" !== ($a = $from[0] ?? "") && "" !== ($b = \substr($to, -1))) {
                    if (\strlen($from) > 1 && ('[' === $a || ':' === $a && isset($from[1]) && false === \strpos($c4, $from[1]))) {
                        if (false === \strpos('+,>}', $b)) {
                            $to .= ' '; // Case of `asdf :asdf` and `asdf [asdf]`
                        }
                    } else if (
                        // Case of `@asdf "asdf"` and `"asdf" asdf` or `@asdf (asdf)` and `(asdf) asdf`
                        false !== \strpos('"\'(', $a) && false === \strpos($c3, $b) ||
                        false !== \strpos('"\')', $b) && false === \strpos($c3, $a)
                    ) {
                        $to .= ' ';
                    } else if ("" !== $a . $b && false === \strpos($c3, $a) && false === \strpos($c3, $b)) {
                        $to .= ' ';
                    }
                }
                continue;
            }
            if (false !== \strpos('"\'', $c) && \preg_match('/^(?>' . $r3 . ')/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                if ('format(' === \substr($to, -7)) {
                    $test = \trim(\substr($m[0], 1, -1));
                    // <https://drafts.csswg.org/css-fonts#font-face-src-parsing>
                    if (false !== \strpos(',collection,embedded-opentype,opentype,svg,truetype,woff,woff2,', ',' . $test . ',')) {
                        $to .= $test;
                    } else if (false !== \strpos(',woff-variations,woff2-variations,truetype-variations,opentype-variations,', ',' . $test . ',')) {
                        $to .= \strtr($test, ['-' => ') tech(']);
                    } else {
                        $to .= $m[0];
                    }
                    continue;
                }
                if ('tech(' === \substr($to, -5)) {
                    $test = \trim(\substr($m[0], 1, -1));
                    // <https://drafts.csswg.org/css-fonts#font-face-src-parsing>
                    if (false !== \strpos(',color-COLRv0,color-COLRv1,color-SVG,color-sbix,color-CBDT,features-opentype,features-aat,features-graphite,incremental,palettes,variations,', ',' . $test . ',')) {
                        $to .= $test;
                    } else {
                        $to .= $m[0];
                    }
                    continue;
                }
                // <https://www.w3.org/TR/css-syntax-3#consume-a-url-token>
                // <https://www.w3.org/TR/css-syntax-3#url-token-diagram>
                if ('url(' === \substr($to, -4) && \strcspn(\substr($m[0], 1, -1), $c4 . '"\'()\\\\') === \strlen($m[0]) - 2) {
                    $to .= \substr($m[0], 1, -1);
                    continue;
                }
                $to .= $m[0];
                continue;
            }
            // `#…`
            if ('#' === $c && \preg_match('/^#([a-f\d]{1,2}){3,4}\b/i', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $n = \strlen($v = \strtolower(\substr($m[0], 1)));
                if (4 === $n && 'f' === \substr($v, -1)) {
                    $v = \substr($v, 0, -1);
                    $n -= 1;
                } else if (8 === $n && 'ff' === \substr($v, -2)) {
                    $v = \substr($v, 0, -2);
                    $n -= 2;
                }
                if (6 === $n && $v[0] === $v[1] && $v[2] === $v[3] && $v[4] === $v[5]) {
                    $v = $v[0] . $v[2] . $v[4];
                } else if (8 === $n && $v[0] === $v[1] && $v[2] === $v[3] && $v[4] === $v[5] && $v[6] === $v[7]) {
                    $v = $v[0] . $v[2] . $v[4] . $v[6];
                }
                $to .= '#' . $v;
                continue;
            }
            // `/*…*/`
            if ('/' === $c && '*' === ($chop[1] ?? 0) && \preg_match('/^\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                // `/*!…*/` or `/**…*/`
                if (false !== \strpos('!*', $m[0][2])) {
                    if (false !== \strpos($m[0], "\n")) {
                        $to .= '/*' . \substr($m[0], 3);
                    } else {
                        $to .= '/*' . \trim(\substr($m[0], 3, -2)) . '*/';
                    }
                // Case of `asdf/*asdf*/asdf{asdf:asdf}`
                } else if ("" !== $to && "" !== ($x = \substr($to, -1)) && false === \strpos($c3, $x)) {
                    $to .= ' ';
                }
                continue;
            }
            // `[…]`
            if ('[' === $c && \preg_match('/^\[(?>' . $r3 . '|[^]]+)+\]/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                if ("" !== $to && "" !== ($x = \substr($to, -1)) && false !== \strpos($c4, $x)) {
                    $to = \trim($to) . ' ';
                }
                $to .= '[';
                foreach (\preg_split('/((?>' . $r3 . '|[$*=^|~]|\s+))/', \trim(\substr($m[0], 1, -1)), -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
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
                    if ("" !== ($x = \substr($to, -1)) && false === \strpos('"$\'*=[]^|~', $x) && false === \strpos('"$\'*=[]^|~', $v[0])) {
                        $to .= ' '; // Case of `[asdf=asdf i]` or `[asdf="asdf"i]`
                    }
                    $to .= $v;
                }
                $to .= ']';
                continue;
            }
            $from = \substr($from, 1);
            if (false !== \strpos(')]', $c)) {
                $to = \trim($to, ',');
            } else if ('}' === $c) {
                $to = \trim($to, ';');
                if ('{' === \substr($to, -1)) {
                    $to = \trim(\preg_replace('/[^{}]+(?>' . $r3 . '|[^{}]+)*\{$/', "", $to)); // Drop empty selector(s)
                    continue;
                }
            }
            $to .= $c;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}

namespace x\minify\x\css {
    const c1 = " \f\n\r\t\v";
    const c2 = "'" . '"';
    const c3 = "\\";
    const c4 = c1 . c2 . '(),/:;[]{}';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $i = 0;
        $limit = \strlen($s);
        while ($i < $limit) {
            $c = $s[$i += \strspn($s, c1, $i)];
            // <https://www.w3.org/TR/css-syntax-3#consume-comment>
            if ('/' === $c && '*' === ($s[$i + 1] ?? 0)) {
                if (false !== ($at = \strpos($s, '*/', $i + 2))) {
                    $r[] = [0, \substr($s, $i, ($at += 2) - $i)];
                    $i = $at;
                    continue;
                }
                // Broken
                $r[] = [0, \substr($s, $i)];
                break;
            }
            // Maybe a pseudo-class selector
            if (':' === $c && false === \strpos(c1, $s[$i + 1] ?? "\0")) {
                if ($at = \strcspn($s, c1 . ',{', $i + 1)) {
                    $c .= \substr($s, ++$i, $at);
                    if ('(' === ($s[$i += $at] ?? 0)) {
                        $c .= '(';
                        ++$i;
                    }
                    $r[] = $c;
                    continue;
                }
                $r[] = $c;
                ++$i;
                continue;
            }
            // <https://www.w3.org/TR/css-syntax-3#consume-string-token>
            if (false !== \strpos(c2, $c)) {
                $start = $i++;
                while ($i < $limit) {
                    if ($c === ($s[$i += \strcspn($s, c3 . $c, $i)] ?? 0)) {
                        break;
                    }
                    $i += 2;
                }
                $r[] = [2, \substr($s, $start, ++$i - $start)];
                continue;
            }
            if ($at = \strcspn($s, c4, $i)) {
                $c = \substr($s, $i, $at);
                $i += $at;
                if ($i >= $limit) {
                    break;
                }
                // Maybe a declaration or a pseudo-class selector at the start of nested selector syntax
                if (':' === $s[$i]) {
                    // Skip past the comment and string token, and check to see if `;` or `}` token exists before the
                    // next `{`. In that case, the current position must be a declaration syntax.
                    $n = $i + 1;
                    while ($n < $limit) {
                        if ('/' === $s[$n += \strspn($s, c1, $n)] && '*' === ($s[$n + 1] ?? 0)) {
                            if (false === ($at = \strpos($s, '*/', $n + 2))) {
                                $n = $limit;
                                break;
                            }
                            $n = $at + 2;
                            continue;
                        }
                        if (false !== \strpos(c2, $s[$n] ?? 0)) {
                            $q = $s[$n++];
                            while ($n < $limit) {
                                if ($q === ($s[$n += \strcspn($s, c3 . $q, $n)] ?? 0)) {
                                    ++$n;
                                    break;
                                }
                                $n += 2;
                            }
                            continue;
                        }
                        break;
                    }
                    // Must be a declaration syntax
                    if (false !== \strpos(';}', $s[$n + \strcspn($s, ';}{', $n)] ?? 0)) {
                        $r[] = $c;
                        $c = $s[$i++];
                    // Must be a nested selector syntax
                    } else {
                        $c .= \substr($s, $i, $at = \strcspn($s, c1 . ',{', $i));
                        if ('(' === ($s[$i += $at] ?? 0)) {
                            $c .= '(';
                            ++$i;
                        }
                    }
                // `asdf(` or `asdf[`
                } else if (false !== \strpos('([', $s[$i])) {
                    $c .= $s[$i++];
                }
                $r[] = $c;
                continue;
            }
            $r[] = $c;
            ++$i;
        }
        return $r;
    }
    function n2t(array $a, int $k, int &$at = 0) {
        $r = $a[++$k] ?? 0;
        while (\is_array($r) && 0 === $r[0]) {
            $r = $a[++$k] ?? 0;
        }
        $at = $k;
        return $r;
    }
    function s(array $a) {
        if (!$a) {
            return null;
        }
        $calc = 0;
        $s = "";
        foreach ($a as $k => $v) {
            if (\is_array($v)) {
                if (0 === $v[0]) {
                    if (false === \strpos('!*', $v[1][2] ?? "\0") && false === \strpos($v[1], '@cc_on') && false === \strpos($v[1], '@license')) {
                        continue;
                    }
                    if ('/' === $v[1][1]) {
                        $c = \ltrim(\substr($v[1], 2));
                        if ("" !== $c) {
                            $s .= '//' . $c . (isset($a[$k + 1]) ? "\n" : "");
                        }
                        continue;
                    }
                    $c = '/*' . \strtr(\substr($v[1], 3), ["\r\n" => "\n", "\r" => "\n"]);
                    if (false === \strpos($c, "\n")) {
                        $at = '*/' === \substr($c, -2);
                        if ("" !== ($c = \trim(\substr($c, 2, $at ? -2 : null)))) {
                            $s .= '/*' . $c . ($at ? '*/' : "");
                        }
                    } else {
                        $s .= $c;
                    }
                    continue;
                }
                if (2 === $v[0]) {
                    if ($s && '=' === $s[-1]) {
                        $q = 0;
                        $w = v($v[1]);
                        // <https://mothereff.in/unquoted-attributes>
                        if ("" === $w || '-' === $w || 0 === \strpos($w, '--') || '-' === $w[0] && \strspn($w, '0123456789', 1) || \strspn($w, '0123456789')) {
                            $q = 1;
                        } else if (\strcspn($w, c3 . c4) !== \strlen($w)) {
                            $q = 1;
                        } else if (\preg_match('~^[-_\x{00a0}-\x{10ffff}]+$~u', $w)) {
                            $q = 0;
                        }
                        if (!$q) {
                            $s .= $w;
                            continue;
                        }
                    }
                }
                $s .= $v[1];
                continue;
            }
            $calc = $calc || 'calc(' === $v;
            if (';' === $v) {
                $calc = 0;
                if (\is_string($p = n2t($a, $k)) && '}' === $p) {
                    continue;
                }
                $s .= $v;
                continue;
            }
            if (':' === $v) {
                $s .= $v;
                continue;
            }
            if (false !== \strpos('$*^|~', $v[0]) && '=' === ($v[1] ?? 0)) {
                $s .= $v;
                continue;
            }
            $t = c2 . '{;<>,}~/])=!' . ($calc ? '(*' : '+');
            if (false === \strpos($t, $v[0]) && $s && false === \strpos($t . '([:', $s[-1])) {
                $s .= ' ';
            }
            if ('}' === $v) {
                $calc = 0;
                if ($s && '{' === $s[-1]) {
                    // echo json_encode('empty selector');
                    // echo '<br>';
                }
            }
            $s .= $v;
        }
        return $s;
    }
    function v(string $s) {
        if (\strlen($s) > 1 && ("'" === $s[0] && "'" === $s[-1] || '"' === $s[0] && '"' === $s[-1])) {
            return \substr($s, 1, -1);
        }
        return $s;
    }
}