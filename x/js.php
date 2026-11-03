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
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $d = $i = 0;
        $limit = \strlen($s);
        while ($i < $limit) {
            $c = $s[$i += \strspn($s, c1, $i)] ?? 0;
            if ('/' === $c) {
                // <https://tc39.es/ecma262#prod-MultiLineComment>
                if ('*' === ($s[$i + 1] ?? 0) && false !== ($end = \strpos($s, '*/', $i + 2))) {
                    $r[] = [false, \substr($s, $i, ($end += 2) - $i)];
                    $i = $end;
                    continue;
                }
                // <https://tc39.es/ecma262#prod-SingleLineComment>
                if ('/' === ($s[$i + 1] ?? 0) && ($end = \strcspn($s, "\n\r", $i))) {
                    $r[] = [false, \substr($s, $i, $end) . ($end + $i < $limit ? "\n" : "")];
                    $i += $end;
                    continue;
                }
                // Number(s) before the `/` automatically invalidates the potential regular expression token
                // if (false !== ($end = \end($r)) && false !== \strpos(c6, $end)) {
                //     $r[] = $c;
                //     ++$i;
                //     continue;
                // }
                // <https://tc39.es/ecma262#prod-RegularExpressionLiteral>
                /*
                $g = 0;
                $start = $i++;
                while ($i < $limit) {
                    $c1 = $value[$i];
                    if (js\c3 === $c1) {
                        $i += 2;
                        continue;
                    }
                    // <https://tc39.es/ecma262#prod-RegularExpressionNonTerminator>
                    if ("\n" === $c1 || "\r" === $c1) {
                        break;
                    }
                    if ('[' === $c1) {
                        $g = 1;
                    } else if (']' === $c1) {
                        $g = 0;
                    } else if ('/' === $c1 && !$g) {
                        ++$i;
                        break;
                    }
                    ++$i;
                }
                // <https://tc39.es/ecma262#prod-RegularExpressionFlags>
                $i += \strspn($value, 'dgimsuvy', $i);
                $i += \strspn($value, js\c1, $i);
                // It must be the last non-white-space character or it must be followed by one of `),.;]}`
                if ($i === $limit || false !== \strpos('),.;]}', $value[$i] ?? "\0")) {
                    $str = \rtrim(\substr($value, $start, $i - $start));
                    // Unclosed potential regular expression token
                    if ('/' !== \substr(\rtrim($str, 'dgimsuvy'), -1)) {
                        $s .= $c;
                        $i = $start + 1;
                        continue;
                    }
                    $s .= $str;
                    continue;
                }
                */
                // `/=`
                if ('=' === ($s[$i + 1] ?? 0)) {
                    $r[] = $c . $s[++$i];
                    continue;
                }
                // Not a regular expression token
                $r[] = $c;
                ++$i;
                continue;
            }
            if ('`' === $c && !$d) {
                $start = $i++;
                while ($i < $limit) {
                    $c = $s[$i += \strcspn($s, "\\$`", $i)] ?? 0;
                    if ('`' === $c) {
                        break;
                    }
                    if ('$' === $c && '{' === ($s[$i + 1] ?? 0)) {
                        ++$d;
                        ++$i;
                        break;
                    }
                    $i += 2;
                }
                $r[] = \substr($s, $start, ++$i - $start);
                continue;
            }
            if ($d) {
                if ('{' === $c) {
                    ++$d;
                } else if ('}' === $c) {
                    $r[] = $c;
                    ++$i;
                    if (0 === --$d) {
                        $start = $i;
                        while ($i < $limit) {
                            $c = $s[$i += \strcspn($s, "\\`", $i)] ?? 0;
                            if ('`' === $c) {
                                break;
                            }
                        }
                        $r[] = \substr($s, $start, ++$i - $start);
                    }
                    continue;
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
                // if ("'" === $w[0] && false === \strpos($w, '"') && false !== \strpos($w, c3 . "'")) {
                //     $w = '"' . \strtr(\substr($w, 1, -1), [c3 . "'" => "'"]) . '"';
                // } else if ('"' === $w[0] && false === \strpos($w, "'") && false !== \strpos($w, c3 . '"')) {
                //     $w = "'" . \strtr(\substr($w, 1, -1), [c3 . '"' => '"']) . "'";
                // }
                $r[] = [1, \substr($s, $start, ++$i - $start)];
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
            // Remove trailing `,`
            if (',' === $c) {
                $n = \strspn($s, c1, $i + 1) + 1;
                if ($i + $n < $limit && false !== \strpos(')]}', $s[$i + $n])) {
                    ++$i;
                    continue;
                }
            }
            // Remove trailing `;`
            if (';' === $c) {
                $n = \strspn($s, c1, $i + 1) + 1;
                if ($i + $n < $limit && false !== \strpos('}', $s[$i + $n])) {
                    ++$i;
                    continue;
                }
            }
            if (false !== \strpos(c5, $c)) {
                $r[] = $c;
                ++$i;
                continue;
            }
            $c = \substr($s, $i, $n = \strcspn($s, c1 . c5, $i));
            $r[] = "" !== $c && false !== \strpos(c6 . '.', $c[0]) ? [0, $c] : $c;
            $i += $n;
        }
        // echo '<pre style="border:1px solid;white-space:pre-wrap;word-wrap:break-word;">';
        // echo htmlspecialchars(json_encode($r),\JSON_PRETTY_PRINT);
        // echo '</pre>';
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
                    $n = n($v[1]);
                    if ('.' !== ($n[0] ?? 0) && \is_string($a[$k - 1] ?? 0) && false === \strpos(c5, \substr($a[$k - 1], -1))) {
                        $s .= ' ';
                    }
                    $s .= $n;
                    continue;
                }
                if (1 === $v[0]) {
                    if (':' === ($a[$k + 1] ?? 0) && false !== \strpos(',{', \substr($s, -1))) {
                        if (\preg_match('/^[\p{ID_Start}_$][\p{ID_Continue}_$\x{200c}\x{200d}]*$/u', \substr($v[1], 1, -1))) {
                            $s .= \substr($v[1], 1, -1);
                            continue;
                        }
                    }
                    $s .= $v[1];
                    continue;
                }
                if (false === $v[0]) {
                    if (false !== \strpos('!*', $v[1][2]) || false !== \strpos($v[1], '@cc_on') || false !== \strpos($v[1], '@license')) {
                        if (0 === \strpos($v[1], '//')) {
                            $v[1] = '//' . \ltrim(\substr($v[1], 2));
                        } else {
                            if ("" !== \trim($v[1]) && false === \strpos($v[1], "\n")) {
                                $v[1] = '/*' . \trim(\substr($v[1], 3, -2)) . '*/';
                            } else {
                                $v[1] = '/*' . \substr($v[1], 3, -2) . '*/';
                            }
                        }
                        $s .= $v[1];
                    }
                    if (\is_string($a[$k - 1] ?? 0)) {
                        if (\is_string($a[$k + 1] ?? 0) && ('++' === $a[$k - 1] || '--' === $a[$k - 1]) && $a[$k - 1] === $a[$k + 1]) {
                            $s .= ' ';
                        } else if (false === \strpos(c5, \substr($a[$k - 1], -1)) && \is_string($a[$k + 1] ?? 0) && false === \strpos(c5, $a[$k + 1][0] ?? "\0")) {
                            $s .= ' ';
                        }
                    }
                    continue;
                }
                $s .= $v[1];
                continue;
            }
            if ('false' === $v) {
                $s = \rtrim($s) . '!1';
            } else if ('true' === $v) {
                $s = \rtrim($s) . '!0';
            } else {
                $s .= $v;
            }
            if (\is_string($a[$k + 1] ?? 0) && ('++' === $v || '--' === $v) && $v === $a[$k + 1]) {
                $s .= ' ';
            } else if (false === \strpos(c5, \substr($v, -1)) && \is_string($a[$k + 1] ?? 0) && false === \strpos(c5, $a[$k + 1][0] ?? "\0")) {
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
        $f = '0' === ($n = \strtolower($n))[0];
        $n = false === \strpos($n, '.') ? \ltrim($n, '0') : \trim($n, '0');
        if ("" === $n || '.' === $n) {
            return '0';
        }
        // <https://tc39.es/ecma262#prod-NonDecimalIntegerLiteral>
        if ($f && $n && false !== \strpos('box', $n[0])) {
            $n = '0' . $n;
        }
        return \strtr(\rtrim($n, '.'), ['_' => ""]);
    }
}