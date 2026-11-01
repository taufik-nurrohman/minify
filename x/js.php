<?php

namespace x\minify\x {
    function js(?string $value): ?string {
        if ("" === ($value = $value ?? "")) {
            return null;
        }
        $d = $i = 0;
        $limit = \strlen($value);
        $s = "";
        while ($i < $limit) {
            $c = $value[$i += \strspn($value, js\c1, $i)] ?? 0;
            if ('/' === $c) {
                $next = $value[$i + 1] ?? 0;
                // <https://tc39.es/ecma262#prod-MultiLineComment>
                if ('*' === $next) {
                    if (false === ($e = \strpos($value, '@cc_on', $i + 2))) {
                        $e = \strpos($value, '@license', $i + 2);
                    }
                    $end = \strpos($value, '*/', $i + 2);
                    $end = false !== $end ? $end + 2 : $limit;
                    if (false !== \strpos('!*', $value[$i + 2] ?? "\0") || false !== $e && $e < $end - 2) {
                        $str = \substr($value, $i + 3, $end - $i - 5);
                        if (\strcspn($str, "\n\r") === \strlen($str)) {
                            $str = \trim($str);
                        }
                        $s .= '/*' . $str . '*/';
                    }
                    $i = $end;
                    $last = \substr($s, -1);
                    if (false !== \strpos('+-', $last) && $last === ($value[$i] ?? 0)) {
                        $s .= ' ';
                    } else {
                        $i += \strspn($value, js\c1, $i);
                        if (false === \strpos(js\c5, $last) && false === \strpos(js\c5, $value[$i] ?? "\0")) {
                            $s .= ' ';
                        }
                    }
                    continue;
                }
                // <https://tc39.es/ecma262#prod-SingleLineComment>
                if ('/' === $next) {
                    if (false === ($e = \strpos($value, '@cc_on', $i + 2))) {
                        $e = \strpos($value, '@license', $i + 2);
                    }
                    $end = \strcspn($value, "\n\r", $i);
                    if (false !== $e && $e < $i + $end) {
                        $s .= '//' . \trim(\substr($value, $i + 2, $end - 2));
                        $end += \strspn($value, js\c1, $end + $i);
                        if ($end + $i < $limit) {
                            $s .= "\n";
                        }
                    }
                    $i += $end;
                    continue;
                }
                $last = \substr($s, -1);
                // Number(s) before the `/` automatically invalidates the potential regular expression token
                if ("" !== $last && false !== \strpos(js\c6, $last)) {
                    $s .= $c;
                    ++$i;
                    continue;
                }
                // <https://tc39.es/ecma262#prod-RegularExpressionLiteral>
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
                // Not a regular expression token
                $s .= $c;
                $i = $start + 1;
                continue;
            }
            if ('`' === $c && !$d) {
                $start = $i++;
                while ($i < $limit) {
                    $c = $value[$i += \strcspn($value, "\\$`", $i)] ?? 0;
                    if ('`' === $c) {
                        break;
                    }
                    if ('$' === $c && '{' === ($value[$i + 1] ?? 0)) {
                        ++$d;
                        ++$i;
                        break;
                    }
                    $i += 2;
                }
                $str = \substr($value, $start, ++$i - $start);
                $s .= $str;
                continue;
            }
            if ($d) {
                if ('{' === $c) {
                    ++$d;
                } else if ('}' === $c) {
                    $s .= $c;
                    ++$i;
                    if (0 === --$d) {
                        $start = $i;
                        while ($i < $limit) {
                            $c = $value[$i += \strcspn($value, "\\`", $i)] ?? 0;
                            if ('`' === $c) {
                                break;
                            }
                        }
                        $str = \substr($value, $start, ++$i - $start);
                        $s .= $str;
                    }
                    continue;
                }
            }
            if (false !== \strpos(js\c2, $c)) {
                $start = $i++;
                while ($i < $limit) {
                    $i += \strcspn($value, js\c4, $i);
                    if ($c === ($value[$i] ?? 0)) {
                        break;
                    }
                    $i += 2;
                }
                $str = \substr($value, $start, $i - $start + 1);
                if ("'" === $str[0] && false === \strpos($str, '"') && false !== \strpos($str, js\c3 . "'")) {
                    $str = '"' . \strtr(\substr($str, 1, -1), [js\c3 . "'" => "'"]) . '"';
                } else if ('"' === $str[0] && false === \strpos($str, "'") && false !== \strpos($str, js\c3 . '"')) {
                    $str = "'" . \strtr(\substr($str, 1, -1), [js\c3 . '"' => '"']) . "'";
                }
                $s .= $str;
                ++$i;
                continue;
            }
            // Remove trailing `,`
            if (',' === $c) {
                $n = \strspn($value, js\c1, $i + 1) + 1;
                if ($i + $n < $limit && false !== \strpos(')]}', $value[$i + $n])) {
                    ++$i;
                    continue;
                }
            }
            // Remove trailing `;`
            if (';' === $c) {
                $n = \strspn($value, js\c1, $i + 1) + 1;
                if ($i + $n < $limit && false !== \strpos('}', $value[$i + $n])) {
                    ++$i;
                    continue;
                }
            }
            if ('.' === $c && ($n = \strspn($value, js\c6 . '_', $i + 1))) {
                ++$n;
                if ('n' === ($value[$i + $n] ?? 0)) {
                    ++$n;
                }
                $s .= js\n(\substr($value, $i, $n));
                $i += $n;
                continue;
            }
            if (false !== \strpos(js\c5, $c)) {
                $last = \substr($s .= $c, -1);
                ++$i;
                if ($last === ($value[$i + 1] ?? 0) && false !== \strpos('+-', $last) && false !== \strpos(js\c1, $value[$i] ?? "\0")) {
                    $s .= ' ';
                }
                continue;
            }
            $n = \strcspn($value, js\c1 . js\c5, $i);
            if ('.' === ($value[$i + $n] ?? 0)) {
                $n += \strspn($value, js\c6 . '_', $i + $n + 1) + 1;
            }
            $c = \substr($value, $i, $n);
            // <https://tc39.es/ecma262#prod-BooleanLiteral>
            if ('false' === $c) {
                $s = \rtrim($s) . '!1';
            } else if ('true' === $c) {
                $s = \rtrim($s) . '!0';
            } else if ("" !== $c) {
                $c = false !== \strpos(js\c6 . '.', $c[0]) ? js\n($c) : $c;
                $s = '.' === $c[0] ? \rtrim($s) . $c : $s . $c;
            }
            if ($n = \strspn($value, js\c1, $i += $n)) {
                $i += $n;
                if ($i < $limit && false === \strpos(js\c5, $value[$i])) {
                    $s .= ' ';
                }
            }
        }
        return "" !== $s ? $s : null;
    }
}

namespace x\minify\x\js {
    const c1 = " \n\r\t";
    const c2 = '"' . "'`";
    const c3 = "\\";
    const c4 = c2 . c3;
    const c5 = '`"\'/!#%&()*+,-.:;<=>?@[\]^`{|}~'; // Punctuation(s) but `$` and `_`
    const c6 = '0123456789';
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