<?php

namespace x\minify\x {
    function js(?string $value): ?string {
        return js\s(js\a($value ?? ""));
    }
}

namespace x\minify\x\js {
    const c1 = " \n\r\t";
    const c2 = '"' . "'`";
    const c3 = "\\";
    const c4 = c2 . c3;
    const c5 = '`"\'/!#%&()*+,-:;<=>?@[\]^`{|}~'; // Punctuation(s) but `$`, `.`, and `_`
    const c6 = '0123456789';
    const m1 = '~\G([' . c2 . '])([\p{ID_Start}_$][\p{ID_Continue}_$\x{200c}\x{200d}]*)\1$~u';
    const m2 = '~\G/(?>\\\\[\s\S]|\[(?>\\\\[\s\S]|[^\]\\\\])*\]|[^/\\\\[\n\r])+/[dgimsuvy]*(?=\s*(?>[(),.;\\[\\]{}\n\r]|$))~';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $i = 0;
        $limit = \strlen($s);
        $t = [];
        while ($i < $limit) {
            if ($t && 0 === $t[$k = \array_key_last($t)]) {
                $start = $i;
                while ($i < $limit) {
                    $c = $s[$i += \strcspn($s, c3 . '$`', $i)] ?? 0;
                    if ('`' === $c) {
                        $r[] = [2, \substr($s, $start, $i - $start)];
                        $r[] = $c;
                        ++$i;
                        \array_pop($t);
                        continue 2;
                    }
                    if ('$' === $c && '{' === ($s[$i + 1] ?? 0)) {
                        $r[] = [2, \substr($s, $start, $i - $start + 2)];
                        $i += 2;
                        $t[$k] = 1;
                        continue 2;
                    }
                    $i += c3 === $c ? 2 : 1;
                }
                $r[] = [2, \substr($s, $start)];
                continue;
            }
            $c = $s[$i += \strspn($s, c1, $i)] ?? 0;
            if ('/' === $c) {
                // <https://tc39.es/ecma262#prod-MultiLineComment>
                if ('*' === ($s[$i + 1] ?? 0) && false !== ($end = \strpos($s, '*/', $i + 2))) {
                    $r[] = [0, \substr($s, $i, ($end += 2) - $i)];
                    $i = $end;
                    continue;
                }
                // <https://tc39.es/ecma262#prod-SingleLineComment>
                if ('/' === ($s[$i + 1] ?? 0) && ($end = \strcspn($s, "\n\r", $i))) {
                    $r[] = [0, \substr($s, $i, $end) . ($end + $i < $limit ? "\n" : "")];
                    $i += $end;
                    continue;
                }
                // <https://tc39.es/ecma262#prod-RegularExpressionLiteral>
                if (false !== \strpos($s, '/', $i + 1) && \preg_match(m2, $s, $m, \PREG_OFFSET_CAPTURE, $i)) {
                    $r[] = [3, $m[0][0]];
                    $i = $m[0][1] + \strlen($m[0][0]);
                    continue;
                }
                // `/=`
                if ('=' === ($s[$i + 1] ?? 0)) {
                    $r[] = $c . $s[++$i];
                    continue;
                }
                $r[] = $c;
                ++$i;
                continue;
            }
            if ('`' === $c) {
                $r[] = $c;
                $t[] = 0;
                ++$i;
                continue;
            }
            if ($t && $t[$k = \array_key_last($t)]) {
                if ('{' === $c) {
                    ++$t[$k];
                } else if ('}' === $c) {
                    if (0 === --$t[$k]) {
                        $r[] = '}';
                        ++$i;
                        continue;
                    }
                }
            }
            if (false !== \strpos(c2, $c)) {
                $start = $i++;
                while ($i < $limit) {
                    $i += \strcspn($s, c4, $i);
                    if ($c === ($s[$i] ?? 0)) {
                        break;
                    }
                    $i += 2;
                }
                $r[] = [2, \substr($s, $start, ++$i - $start)];
                continue;
            }
            // `!=`, `!==`
            if ('!' === $c && '=' === ($s[$i + 1] ?? 0)) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '=', $i));
                $i += $end;
                continue;
            }
            // `%=`
            if ('%' === $c && '=' === ($s[$i + 1] ?? 0)) {
                $r[] = $c . $s[++$i];
                ++$i;
                continue;
            }
            // `&&`, `&=`, `&&=`
            if ('&' === $c && false !== \strpos('&=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '&=', $i));
                $i += $end;
                continue;
            }
            // `**`, `*=`, `**=`
            if ('*' === $c && false !== \strpos('*=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '*=', $i));
                $i += $end;
                continue;
            }
            // `++`, `+=`
            if ('+' === $c && false !== \strpos('+=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . $s[++$i];
                ++$i;
                continue;
            }
            // `--`, `-=`
            if ('-' === $c && false !== \strpos('-=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . $s[++$i];
                ++$i;
                continue;
            }
            // `<<`, `<=`, `<<=`
            if ('<' === $c && false !== \strpos('<=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '<=', $i));
                $i += $end;
                continue;
            }
            // `=>`
            if ('=' === $c && '>' === ($s[$i + 1] ?? 0)) {
                $r[] = $c . $s[++$i];
                ++$i;
                continue;
            }
            // `==`, `===`
            if ('=' === $c && '=' === ($s[$i + 1] ?? 0)) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '=', $i));
                $i += $end;
                continue;
            }
            // `>>`, `>>>`, `>=`, `>>=`, `>>>=`
            if ('>' === $c && false !== \strpos('>=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '>=', $i));
                $i += $end;
                continue;
            }
            // `??`, `??=`
            if ('?' === $c && false !== \strpos('?=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '?=', $i));
                $i += $end;
                continue;
            }
            // `||`, `|=`, `||=`
            if ('|' === $c && false !== \strpos('|=', $s[$i + 1] ?? "\0")) {
                $r[] = $c . \substr($s, ++$i, $end = \strspn($s, '|=', $i));
                $i += $end;
                continue;
            }
            if (false !== \strpos(c5, $c)) {
                $r[] = $c;
                ++$i;
                continue;
            }
            $c = \substr($s, $i, $n = \strcspn($s, c1 . c5, $i));
            // <https://tc39.es/ecma262#prod-SignedInteger>
            if (false !== \strpos('+-', $s[$i + $n] ?? "\0")) {
                // <https://tc39.es/ecma262#prod-ExponentIndicator>
                if (false !== \strpos('Ee', \substr($c, -1)) && \is_numeric(\strtr(\substr($c, 0, -1), ['_' => ""]))) {
                    $c .= \substr($s, $i + $n, $m = \strcspn($s, c1 . c5, $i + $n + 1) + 1);
                    $n += $m;
                }
            }
            $i += $n;
            if ('...' === $c) {
                $r[] = $c;
                continue;
            }
            if ("" !== $c) {
                if ('.' === $c[0] && false !== \strpos(c6, $c[1] ?? "\0")) {
                    $r[] = [1, $c];
                    continue;
                }
                if (false !== \strpos(c6, $c[0])) {
                    $r[] = [1, $c];
                    continue;
                }
                $r[] = $c;
            }
        }
        return $r;
    }
    function s(array $a) {
        if (!$a) {
            return null;
        }
        $s = "";
        foreach ($a as $k => $v) {
            $next = $a[$k + 1] ?? 0;
            $prev = $a[$k - 1] ?? 0;
            if (\is_array($v)) {
                if (0 === $v[0]) {
                    $keep = 0;
                    if (false !== \strpos('!*', $v[1][2]) || false !== \strpos($v[1], '@cc_on') || false !== \strpos($v[1], '@license')) {
                        $keep = 1;
                        if (0 === \strpos($v[1], '//')) {
                            $v[1] = '//' . \ltrim(\substr($v[1], 2));
                        } else {
                            if ("" !== \trim($v[1]) && false === \strpos($v[1], "\n")) {
                                $v[1] = '/*' . \trim(\ltrim(\substr($v[1], 2, -2), '!*')) . '*/';
                            } else {
                                $v[1] = "/*\n" . \trim(\ltrim(\substr($v[1], 2, -2), '!*')) . "\n*/";
                            }
                        }
                        $s .= $v[1];
                    }
                    if (\is_string($next) && ($w = \substr($s, -1))) {
                        if ("" !== $w && false !== \strpos('+-', $w) && $w === $next[0]) {
                            $s .= ' ';
                        } else if (!$keep && false === \strpos(c5, $w) && false === \strpos(c5, $next[0])) {
                            $s .= ' ';
                        }
                    }
                    continue;
                }
                if (1 === $v[0]) {
                    $n = n($v[1]);
                    if ('.' !== ($n = n($v[1]))[0] && "" !== $s && false === \strpos(c5, \substr($s, -1))) {
                        $s .= ' ';
                    }
                    $s .= $n;
                    if (\is_string($next) && false === \strpos(c5, $next[0])) {
                        $s .= ' ';
                    }
                    continue;
                }
                if (2 === $v[0]) {
                    if (':' === $next && false !== \strpos(',{', \substr($s, -1))) {
                        if (\preg_match(m1, $v[1], $m)) {
                            $s .= $m[2];
                            continue;
                        }
                    }
                }
                $s .= $v[1];
                continue;
            }
            // Remove trailing `,`
            if (',' === $v) {
                $n = $k;
                while (\is_array($next) && 0 === $next[0]) {
                    $next = $a[++$n] ?? 0;
                }
                if (\is_string($next) && false !== \strpos(')]}', $next[0])) {
                    continue;
                }
            }
            if ('...' === $v) {
                $s .= $v;
                continue;
            }
            // Remove trailing `;`
            if (';' === $v) {
                $n = $k;
                while (\is_array($next) && 0 === $next[0]) {
                    $next = $a[++$n] ?? 0;
                }
                if (\is_string($next) && false !== \strpos('}', $next[0])) {
                    continue;
                }
            }
            if (']' === $v) {
                if (\is_string($a[$k - 3] ?? 0) && \in_array($a[$k - 3], [
                    'case',
                    'else',
                    'in',
                    'instanceof',
                    'of',
                    'return',
                    'typeof'
                ], true)) {
                    $s .= $v;
                    continue;
                }
                if (\is_array($prev) && 2 === $prev[0] && \preg_match(m1, $prev[1], $m)) {
                    if ('[' === ($a[$k - 2] ?? 0)) {
                        $c5 = \strtr(c5, [')' => "", ']' => "", '}' => ""]);
                        if ("" !== ($c = \substr($s, -(\strlen($prev[1]) + 2), 1)) && false === \strpos($c5, $c)) {
                            $s = \substr($s, 0, -(\strlen($prev[1]) + 1)) . '.' . \substr($prev[1], 1, -1);
                            continue;
                        }
                    }
                }
            }
            if ("" !== $s && false === \strpos(',.{', \substr($s, -1))) {
                if ('false' === $v) {
                    $s = \rtrim($s) . '!1';
                } else if ('true' === $v) {
                    $s = \rtrim($s) . '!0';
                } else if ('undefined' === $v) {
                    $s .= 'void 0';
                } else {
                    $s .= $v;
                }
            } else {
                $s .= $v;
            }
            if (\is_string($next) && false !== \strpos('+-', $v[0]) && $v[0] === $next[0]) {
                $s .= ' ';
            } else if (false === \strpos(c5 . '.', \substr($v, -1)) && \is_string($next) && false === \strpos(c5 . '.', $next[0] ?? "\0")) {
                $s .= ' ';
            }
        }
        return $s;
    }
    // <https://tc39.es/ecma262#sec-literals-numeric-literals>
    function n(string $n) {
        if ('_' === \substr($n, -1)) {
            return $n;
        }
        $f = '0' === ($n = \strtolower(\strtr($n, ['_' => ""])))[0];
        $n = false === \strpos($n, '.') || false !== \strpos($n, 'e') ? \ltrim($n, '0') : \trim($n, '0');
        if ("" === $n || '.' === $n) {
            return '0';
        }
        // <https://tc39.es/ecma262#prod-NonDecimalIntegerLiteral>
        if ($f && $n && false !== \strpos('bnox', $n[0])) {
            $n = '0' . $n;
        }
        return \rtrim($n, '.');
    }
}