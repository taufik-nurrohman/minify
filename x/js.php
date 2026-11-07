<?php

namespace x\minify\x {
    function js(?string $value): ?string {
        return js\s(js\a($value ?? ""));
    }
}

namespace x\minify\x\js {
    const c1 = " \n\r\t";
    const c2 = '"' . "'`";
    const c3 = '0123456789';
    const c4 = "\\";
    const c5 = c4 . c2;
    const c6 = c4 . '$`';
    const c7 = '`"\'/!#%&()*+,-:;<=>?@[\]^`{|}~'; // But `$`, `.`, and `_`
    const m1 = '~\G[\p{ID_Start}_$][\p{ID_Continue}_$\x{200c}\x{200d}]*$~u';
    const m2 = '~\G/(?>\\\\[\s\S]|\[(?>\\\\[\s\S]|[^\]\\\\])*\]|[^/\\\\[\n\r])+/[dgimsuvy]*(?=\s*(?>[(),.;\\[\\]{}\n\r]|$))~';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $i = 0;
        $limit = \strlen($s);
        $stack = [];
        while ($i < $limit) {
            if ($stack && 0 === $stack[$k = \array_key_last($stack)]) {
                $start = $i;
                while ($i < $limit) {
                    $c = $s[$i += \strcspn($s, c6, $i)] ?? 0;
                    // <https://tc39.es/ecma262#prod-Template>
                    if ('`' === $c) {
                        if ("" !== ($str = \substr($s, $start, $i - $start))) {
                            $r[] = [2, $str];
                        }
                        $r[] = $c;
                        ++$i;
                        \array_pop($stack);
                        continue 2;
                    }
                    // <https://tc39.es/ecma262#prod-TemplateHead>
                    if ('$' === $c && '{' === ($s[$i + 1] ?? 0)) {
                        $r[] = [2, \substr($s, $start, $i - $start)];
                        $r[] = \substr($s, $i, 2);
                        $i += 2;
                        $stack[$k] = 1;
                        continue 2;
                    }
                    $i += c4 === $c ? 2 : 1;
                }
                // Broken string
                $r[] = [2, \substr($s, $start)];
                continue;
            }
            $c = $s[$i += \strspn($s, c1, $i)] ?? 0;
            if ('/' === $c) {
                // <https://tc39.es/ecma262#prod-MultiLineComment>
                if ('*' === ($s[$i + 1] ?? 0)) {
                    if (false !== ($end = \strpos($s, '*/', $i + 2))) {
                        $r[] = [0, \substr($s, $i, ($end += 2) - $i)];
                        $i = $end;
                        continue;
                    }
                    // Broken comment
                    $r[] = [0, \substr($s, $i)];
                    break;
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
                $stack[] = 0;
                ++$i;
                continue;
            }
            if ($stack && $stack[$k = \array_key_last($stack)]) {
                if ('{' === $c) {
                    ++$stack[$k];
                } else if ('}' === $c) {
                    $r[] = $c;
                    // <https://tc39.es/ecma262#prod-TemplateTail>
                    if (0 === --$stack[$k]) {
                        $r[] = [2, \substr($s, ++$i, $eat = \strcspn($s, '`', $i))];
                        $i += $eat;
                        continue;
                    }
                }
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
            // <https://tc39.es/ecma262#prod-StringLiteral>
            if (false !== \strpos(c2, $c)) {
                $start = $i++;
                while ($i < $limit) {
                    $i += \strcspn($s, c5, $i);
                    if ($c === ($s[$i] ?? 0)) {
                        break;
                    }
                    $i += 2;
                }
                $r[] = [2, \substr($s, $start, ++$i - $start)];
                continue;
            }
            // Punctuation but `$`, `.`, and `_`
            if (false !== \strpos(c7, $c)) {
                $r[] = $c;
                ++$i;
                continue;
            }
            $c = \substr($s, $i, $n = \strcspn($s, c1 . c7, $i));
            // <https://tc39.es/ecma262#prod-SignedInteger>
            if (false !== \strpos('+-', $s[$i + $n] ?? "\0")) {
                // <https://tc39.es/ecma262#prod-ExponentIndicator>
                if (false !== \strpos('Ee', $c[-1]) && \is_numeric(\strtr(\substr($c, 0, -1), ['_' => ""]))) {
                    $c .= \substr($s, $i + $n, $m = \strcspn($s, c1 . c7, $i + $n + 1) + 1);
                    $n += $m;
                }
            }
            $i += $n;
            if ('...' === $c) {
                $r[] = $c;
                continue;
            }
            if ("" !== $c) {
                if ('.' === $c[0] && false !== \strpos(c3, $c[1] ?? "\0")) {
                    $r[] = [1, $c];
                    continue;
                }
                if (false !== \strpos(c3, $c[0])) {
                    $r[] = [1, $c];
                    continue;
                }
                $r[] = $c;
            }
        }
        return $r;
    }
    // <https://tc39.es/ecma262#sec-literals-numeric-literals>
    function n(string $n) {
        if ('_' === $n[-1]) {
            return $n;
        }
        $f = '0' === ($n = \strtolower(\strtr($n, ['_' => ""])))[0];
        // <https://tc39.es/ecma262#prod-LegacyOctalIntegerLiteral>
        if ($f && \strspn($n, '01234567') === \strlen($n) && "" !== \trim($n, '0')) {
            return $n;
        }
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
    function n2t(array $a, int $k) {
        $r = $a[++$k] ?? 0;
        while (\is_array($r) && 0 === $r[0]) {
            $r = $a[++$k] ?? 0;
        }
        return $r;
    }
    function p2v(array $a, int $k) {
        $r = $a[--$k] ?? 0;
        while (\is_array($r) && 0 === $r[0]) {
            $r = $a[--$k] ?? 0;
        }
        return $r;
    }
    function s(array $a) {
        if (!$a) {
            return null;
        }
        $s = "";
        foreach ($a as $k => $v) {
            if (\is_array($v)) {
                if (0 === $v[0]) {
                    $keep = 0;
                    if (false !== \strpos('!*', $v[1][2]) || false !== \strpos($v[1], '@cc_on') || false !== \strpos($v[1], '@license')) {
                        $keep = 1;
                        if (0 === \strpos($v[1], '//')) {
                            $c = \ltrim(\substr($v[1], 2));
                            if ("" !== $c) {
                                $s .= '//' . $c;
                            }
                        } else {
                            $c = \trim(\ltrim(\substr($v[1], 2, -2), '!*'));
                            if (false !== \strpos($c, "\n")) {
                                $c = "\n" . $c . "\n";
                            }
                            if ("" !== $c) {
                                $s .= '/*' . $c . ('*/' === \substr($v[1], -2) ? '*/' : "");
                            }
                        }
                    }
                    continue;
                }
                if (1 === $v[0]) {
                    if ('1' === ($n = n($v[1])) && "" !== $s && '=' === $s[-1] && false !== \strpos('+-', $s[-2])) {
                        $s = \substr($s, 0, -1) . $s[-2];
                        continue;
                    }
                    if ('.' !== $n[0] && "" !== $s && false === \strpos(c7, $s[-1])) {
                        $s .= ' ';
                    }
                    $s .= $n;
                    continue;
                }
                if (2 === $v[0]) {
                    // `,'asdf':` → `,asdf:`
                    // `{'asdf':` → `{asdf:`
                    if ("" !== $s && false !== \strpos(',{', $s[-1]) && "" !== ($key = \substr($v[1], 1, -1))) {
                        if (':' === n2t($a, $k) && \preg_match(m1, $key)) {
                            $s .= $key;
                            continue;
                        }
                    }
                }
                $s .= "''" === $v[1] ? '""' : $v[1];
                continue;
            }
            // `,)` → `)`
            // `,]` → `]`
            // `,}` → `}`
            if (',' === $v) {
                if (\is_string($p = n2t($a, $k)) && false !== \strpos(')]}', $p[0])) {
                    continue;
                }
            }
            if ('...' === $v) {
                $s .= $v;
                continue;
            }
            // `;}` → `}`
            if (';' === $v) {
                if (\is_string($p = n2t($a, $k)) && false !== \strpos('}', $p[0])) {
                    continue;
                }
            }
            if ("" !== $s) {
                // `+ ++`
                // `++ +`
                // `- --`
                // `-- -`
                if (false !== \strpos('+-', $v[0]) && $v[0] === $s[-1]) {
                    $s .= ' ' . $v;
                    continue;
                }
                // `asdf asdf`
                if (false === \strpos(c7 . '.', $v[0]) && false === \strpos(c7 . '.', $s[-1])) {
                    $s .= ' ';
                }
            }
            if (']' === $v) {
                if (\is_string($p = p2v($a, $k - 2)) && \in_array($p, [
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
                if (\is_array($p = p2v($a, $k)) && 2 === $p[0] && "" !== ($key = \substr($p[1], 1, -1))) {
                    // `asdf['1234']` → `asdf[1234]`
                    if (\strspn($key, c3) === \strlen($key)) {
                        $s = \substr($s, 0, -(\strlen($key) + 2)) . $key . $v;
                        continue;
                    }
                    // `asdf['asdf']` → `asdf.asdf`
                    if (\preg_match(m1, $key)) {
                        if ('[' === ($a[$k - 2] ?? 0)) {
                            $c7 = \strtr(c7, [')' => "", ']' => "", '}' => ""]);
                            if ("" !== ($c = \substr($s, -(\strlen($key) + 4), 1)) && false === \strpos($c7, $c)) {
                                $s = \substr($s, 0, -(\strlen($key) + 3)) . '.' . $key;
                                continue;
                            }
                        }
                    }
                }
            }
            $c7 = \strtr(c7, [':' => ""]);
            if ("" === $s || false === \strpos(',.{', $s[-1]) || \is_string($p = n2t($a, $k)) && false !== \strpos($c7, $p[0])) {
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
        }
        return $s;
    }
}