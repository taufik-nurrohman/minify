<?php

namespace x\minify\x {
    function js(?string $value): ?string {
        return js\s(js\a($value ?? ""));
    }
}

namespace x\minify\x\js {
    const a1 = ['case' => 1, 'else' => 1, 'in' => 1, 'of' => 1, 'return' => 1, 'throw' => 1, 'yield' => 1];
    const a2 = ['case' => 1, 'delete' => 1, 'return' => 1, 'throw' => 1, 'typeof' => 1, 'void' => 1, 'yield' => 1];
    const c1 = " \n\r\t";
    const c2 = '"' . "'`";
    const c3 = '0123456789';
    const c4 = "\\";
    const c5 = c4 . c2;
    const c6 = c4 . '$`';
    const c7 = c4 . '!#%&*+-<=>?@^|~';
    const c8 = c2 . c7 . '(),/:;[]{}'; // But `$`, `.`, and `_`
    const m1 = '~\G[\p{ID_Start}_$][\p{ID_Continue}_$\x{200c}\x{200d}]*$~u';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $i = 0;
        $limit = \strlen($s);
        $pat = 1;
        $stack = [];
        while ($i < $limit) {
            if ($stack && 0 === $stack[$k = \array_key_last($stack)]) {
                $start = $i;
                while ($i < $limit) {
                    $c = $s[$i += \strcspn($s, c6, $i)] ?? 0;
                    // <https://tc39.es/ecma262#prod-Template>
                    if ('`' === $c) {
                        if ("" !== ($str = \substr($s, $start, $i - $start))) {
                            $r[] = [2, $str, false];
                        }
                        $r[] = $c;
                        ++$i;
                        \array_pop($stack);
                        continue 2;
                    }
                    // <https://tc39.es/ecma262#prod-TemplateHead>
                    if ('$' === $c && '{' === ($s[$i + 1] ?? 0)) {
                        $r[] = [2, \substr($s, $start, $i - $start), false];
                        $r[] = $c . '{';
                        $i += 2;
                        $stack[$k] = 1;
                        continue 2;
                    }
                    $i += c4 === $c ? 2 : 1;
                }
                // Broken string
                $r[] = [2, \substr($s, $start), false];
                continue;
            }
            $c = $s[$i += \strspn($s, c1, $i)] ?? 0;
            // <https://tc39.es/ecma262#prod-annexB-SingleLineHTMLCloseComment>
            if ('-' === $c && 0 === \substr_compare($s, '-->', $i, 3) && ($end = \strcspn($s, "\n\r", $i))) {
                $r[] = [0, \substr($s, $i, $end)];
                $i += $end;
                continue;
            }
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
                    $r[] = [0, \substr($s, $i, $end)];
                    $i += $end;
                    continue;
                }
                // <https://tc39.es/ecma262#prod-RegularExpressionLiteral>
                if ($pat && false !== \strpos($s, '/', $i + 2)) {
                    $n = $i + 1;
                    $pat = 0;
                    $x = $z = 0;
                    while ($n < $limit) {
                        $c1 = $s[$n];
                        if ($x) {
                            $x = 0;
                        } else if (c4 === $c1) {
                            $x = 1;
                        } else if ('[' === $c1) {
                            $z = 1;
                        } else if (']' === $c1) {
                            $z = 0;
                        } else if ('/' === $c1 && !$z) {
                            $n += \strspn($s, 'dgimsuvy', ++$n);
                            $r[] = [3, \substr($s, $i, $n - $i)];
                            $i = $n;
                            continue 2;
                        } else if ("\n" === $c1 || "\r" === $c1) {
                            break;
                        }
                        ++$n;
                    }
                }
                $r[] = $c . \substr($s, ++$i, $eat = \strspn($s, c7, $i));
                $i += $eat;
                continue;
            }
            // <https://tc39.es/ecma262#prod-annexB-SingleLineHTMLOpenComment>
            if ('<' === $c && 0 === \substr_compare($s, '<!--', $i, 4) && ($end = \strcspn($s, "\n\r", $i))) {
                $r[] = [0, \substr($s, $i, $end)];
                $i += $end;
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
                        $start = ++$i;
                        while ($i < $limit) {
                            if ('`' === $s[$i += \strcspn($s, c4 . '`', $i)]) {
                                break;
                            }
                            $i += 2;
                        }
                        $r[] = [2, \substr($s, $start, $i - $start), false];
                        continue;
                    }
                }
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
                $r[] = [2, \substr($s, $start, ++$i - $start), true];
                continue;
            }
            // Punctuation but `$`, `.`, and `_`
            if (false !== \strpos(c8, $c)) {
                $pat = false !== \strpos('!%&(*+,-:;<=>?[^{|~', $c);
                $r[] = $c . \substr($s, ++$i, $eat = \strspn($s, c7, $i));
                $i += $eat;
                continue;
            }
            $c = \substr($s, $i, $n = \strcspn($s, c1 . c8, $i));
            // <https://tc39.es/ecma262#prod-SignedInteger>
            if (false !== \strpos('+-', $s[$i + $n] ?? "\0")) {
                // <https://tc39.es/ecma262#prod-ExponentIndicator>
                if (false !== \strpos('Ee', $c[-1]) && \is_numeric(\strtr(\substr($c, 0, -1), ['_' => ""]))) {
                    $c .= \substr($s, $i + $n, $m = \strcspn($s, c1 . c8, $i + $n + 1) + 1);
                    $n += $m;
                }
            }
            $i += $n;
            if ("" !== $c) {
                if (false !== \strpos(c3, $c[0]) || '.' === $c[0] && false !== \strpos(c3, $c[1] ?? "\0")) {
                    $pat = 0;
                    $r[] = [1, $c];
                    continue;
                }
                $pat = isset(a2[$c]);
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
    function n2t(array $a, int $k, int &$at = 0) {
        $r = $a[++$k] ?? 0;
        while (\is_array($r) && 0 === $r[0]) {
            $r = $a[++$k] ?? 0;
        }
        $at = $k;
        return $r;
    }
    function p2v(array $a, int $k, int &$at = 0) {
        $r = $a[--$k] ?? 0;
        while (\is_array($r) && 0 === $r[0]) {
            $r = $a[--$k] ?? 0;
        }
        $at = $k;
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
                    if (false === \strpos('!*', $v[1][2] ?? "\n") && false === \strpos($v[1], '@cc_on') && false === \strpos($v[1], '@license')) {
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
                        $end = '*/' === \substr($c, -2);
                        if ("" !== ($c = \trim(\substr($c, 2, $end ? -2 : null)))) {
                            $s .= '/*' . $c . ($end ? '*/' : "");
                        }
                    } else {
                        $s .= $c;
                    }
                    continue;
                }
                if (1 === $v[0]) {
                    // `+=1` → `++`
                    // `-=1` → `--`
                    if ('1' === ($n = n($v[1])) && "" !== $s && '=' === $s[-1] && false !== \strpos('+-', $s[-2])) {
                        $s = \substr($s, 0, -1) . $s[-2];
                        continue;
                    }
                    if ('.' !== $n[0] && "" !== $s && false === \strpos(c8, $s[-1])) {
                        $s .= ' ';
                    }
                    $s .= $n;
                    continue;
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
            // `,'asdf':` → `,asdf:`
            // `{'asdf':` → `{asdf:`
            if (':' === $v) {
                if (\is_array($p = p2v($a, $at = $k, $at)) && 2 === $p[0] && $p[2] && "" !== ($key = \substr($p[1], 1, -1))) {
                    if (\is_string($p = p2v($a, $at)) && false !== \strpos(',{', $p[0])) {
                        if (false === \strpos(c1 . c3 . c8 . '.', $key[0]) && \preg_match(m1, $key)) {
                            $s = \substr($s, 0, -(\strlen($key) + 2)) . $key . $v;
                            continue;
                        }
                    }
                }
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
                if (false === \strpos(c8 . '.', $v[0]) && false === \strpos(c1 . c8 . '.', $s[-1])) {
                    $s .= ' ';
                }
            }
            if (']' === $v) {
                if (\is_array($p = p2v($a, $at = $k, $at)) && 2 === $p[0] && $p[2] && "" !== ($key = \substr($p[1], 1, -1))) {
                    if ('[' !== ($p = p2v($a, $at, $at))) {
                        $s .= $v;
                        continue;
                    }
                    if (0 === $at || \is_string($p = p2v($a, $at)) && false !== \strpos(c8, $p[0]) && false === \strpos(')]}', $p[0]) || isset(a1[$p])) {
                        $s .= $v;
                        continue;
                    }
                    // `asdf['1234']` → `asdf[1234]`
                    if (\strspn($key, c3) === \strlen($key)) {
                        $s = \substr($s, 0, -(\strlen($key) + 2)) . $key . $v;
                        continue;
                    }
                    // `asdf['asdf']` → `asdf.asdf`
                    if (false === \strpos(c1 . c3 . c8 . '.', $key[0]) && \preg_match(m1, $key)) {
                        $s = \substr($s, 0, -(\strlen($key) + 3)) . '.' . $key;
                        continue;
                    }
                }
            }
            if ("" === $s || false === \strpos(',.{', $s[-1]) || \is_string($p = n2t($a, $k)) && false !== \strpos(c8, $p[0]) && ':' !== $p[0]) {
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