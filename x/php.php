<?php

namespace x\minify\x {
    function php(?string $value): ?string {
        return php\s(php\a($value ?? ""));
    }
}

namespace x\minify\x\php {
    const c1 = ' !"#$%&()*+,-./:;<=>?@[\]^_`{|}~' . "'";
    const c2 = '"' . "'";
    const m1 = '~[\w\x80-\xff)}\]]\[([' . c2 . '])([+-]?\d+)\1$~';
    const m2 = '~->\{([' . c2 . '])([a-zA-Z_\x80-\xff][\w\x80-\xff]*)\1$~';
    const m3 = '~([' . c2 . '])([+-]?\d+)\1$~';
    function a(string $s) {
        return "" !== $s ? \token_get_all($s) : [];
    }
    function s(array $a) {
        if (!$a) {
            return null;
        }
        $in_array = $is_array = 0;
        $limit = \count($a);
        $s = "";
        foreach ($a as $k => $v) {
            $next = $a[$k + 1] ?? 0;
            $prev = $a[$k - 1] ?? 0;
            if (\is_array($v)) {
                $t = $v[0];
                // Can be `array $asdf` or `array (`
                if (\T_ARRAY === $t) {
                    $i = $k + 1;
                    while (isset($a[$i])) {
                        if (\is_array($a[$i]) && \T_WHITESPACE !== $a[$i][0]) {
                            break;
                        }
                        if (\is_string($a[$i])) {
                            if ('(' === $a[$i]) {
                                $is_array += 1;
                            }
                            break;
                        }
                        ++$i;
                    }
                    if (!$is_array) {
                        $s .= $v[1];
                    }
                    continue;
                }
                if (\T_AS === $t && false === \strpos(c1, $s[-1])) {
                    $s .= ' ' . $v[1];
                    continue;
                }
                echo json_encode([token_name($t),htmlspecialchars($v[1])]);
                echo '<br>';
                if (\T_CLOSE_TAG === $t) {
                    if ($k === $limit - 1) {
                        break;
                    }
                    if (';' === $s[-1]) {
                        $s = \rtrim($s, ';');
                    }
                    $s .= $v[1];
                    continue;
                }
                if (\T_COMMENT === $t || \T_DOC_COMMENT === $t) {
                    if ('/*' === \substr($v[1], 0, 2)) {
                        if (false !== \strpos('!*', $v[1][2]) || false !== \strpos($v[1], '@license')) {
                            $c = \trim(\ltrim(\substr($v[1], 2, -2), '!*'));
                            if (false !== \strpos($c, "\n")) {
                                $c = "\n" . $c . "\n";
                            }
                            if ("" !== $c) {
                                $s .= '/*' . $c . '*/';
                            }
                        }
                    }
                    continue;
                }
                if (\T_CONSTANT_ENCAPSED_STRING === $t) {
                    if ('!!' === \substr($s, -2)) {
                        $s = \substr($s, 0, -2) . '!' . (\substr($v[1], 1, -1) ? '0' : '1');
                        continue;
                    }
                    if ('"".' === \substr($s, -3)) {
                        $s = \substr($s, 0, -3) . $v[1];
                        continue;
                    }
                    if ('(float)' === \substr($s, -7) && \is_numeric($c = \substr($v[1], 1, -1))) {
                        $c = (string) ((float) $c);
                        $s = \substr($s, 0, -7) . $c . (false === \strpos($c, '.') ? '.0' : "");
                        continue;
                    }
                    if ('(int)' === \substr($s, -5) && \is_numeric($c = \substr($v[1], 1, -1))) {
                        $c = (string) (((int) $c));
                        $s = \substr($s, 0, -5) . $c;
                        continue;
                    }
                    $s .= "''" === $v[1] ? '""' : $v[1];
                    continue;
                }
                if (\T_ECHO === $t || \T_PRINT === $t) {
                    if ('<?php ' === \substr($s, -6)) {
                        $s = \substr($s, 0, -4) . '=';
                        continue;
                    }
                    $s .= 'echo';
                    continue;
                }
                if (\T_ENCAPSED_AND_WHITESPACE === $t) {
                    $s .= \strtr($v[1], ["S\n" => "\\x53\n", "S\r" => "\\x53\r"]);
                    continue;
                }
                if (\T_DNUMBER === $t || \T_LNUMBER === $t) {
                    $v[1] = \strtolower(\strtr($v[1], ['_' => ""]));
                    $v[1] = false === \strpos($v[1], '.') ? \ltrim($v[1], '0') : \trim($v[1], '0');
                    if ("" === $v[1]) {
                        $v[1] = '0';
                    } else if ('.' === $v[1]) {
                        $v[1] = '0.0';
                    } else if ('.' === $v[1][-1]) {
                        $v[1] .= '0';
                    }
                    if ('!!' === \substr($s, -2)) {
                        $c = \T_DNUMBER ? (float) $v[1] : (int) $v[1];
                        $s = \substr($s, 0, -2) . '!' . ($c ? '0' : '1');
                        continue;
                    }
                    if ('"".' === \substr($s, -3)) {
                        $s = \substr($s, 0, -3) . "'" . $v[1] . "'";
                        continue;
                    }
                    if ('(float)' === \substr($s, -7)) {
                        $s = \substr($s, 0, -7) . $v[1] . (\T_LNUMBER === $t ? '.0' : "");
                        continue;
                    }
                    if ('(int)' === \substr($s, -5) && \T_LNUMBER === $t) {
                        $s = \substr($s, 0, -5) . $v[1];
                        continue;
                    }
                    if (false === \strpos(c1, $s[-1]) && '.' !== $v[1][0]) {
                        $s .= ' ';
                    }
                    $s .= $v[1];
                    continue;
                }
                if (\T_DEC === $t && '-' === $s[-1]) {
                    $s .= ' ' . $v[1];
                    continue;
                }
                // `'1234'=>` → `1234=>`
                if (\T_DOUBLE_ARROW === $t) {
                    if (false !== \strpos(c2, $s[-1]) && \preg_match(m3, $s, $m)) {
                        $s = \substr($s, 0, -(\strlen($m[0]))) . $m[2] . $v[1];
                        continue;
                    }
                }
                if (\T_END_HEREDOC === $t) {
                    $s .= 'S';
                    continue;
                }
                if (\T_INC === $t && '+' === $s[-1]) {
                    $s .= ' ' . $v[1];
                    continue;
                }
                if (\T_OPEN_TAG === $t) {
                    $s .= \trim($v[1]) . ' ';
                    continue;
                }
                if (\T_START_HEREDOC === $t) {
                    $s .= '<<<' . ("'" === $v[1][3] ? "'S'" : 'S') . "\n";
                    continue;
                }
                if (\T_STRING === $t) {
                    $c = \strtolower($v[1]);
                    if ('false' === $c) {
                        if ('!!' === \substr($s, -2)) {
                            $s = \substr($s, 0, -1) . '1';
                            continue;
                        }
                        if ('"".' === \substr($s, -3)) {
                            $s = \substr($s, 0, -1);
                            continue;
                        }
                        if ('(int)' === \substr($s, -5)) {
                            $s = \substr($s, 0, -5) . '0';
                            continue;
                        }
                        $s .= '!1';
                        continue;
                    }
                    if ('null' === $c) {
                        if ('!!' === \substr($s, -2)) {
                            $s = \substr($s, 0, -1) . '1';
                            continue;
                        }
                        if ('"".' === \substr($s, -3)) {
                            $s = \substr($s, 0, -1);
                            continue;
                        }
                        if ('(int)' === \substr($s, -5)) {
                            $s = \substr($s, 0, -5) . '0';
                            continue;
                        }
                        if (false === \strpos(c1, $s[-1])) {
                            $s .= ' ';
                        }
                        $s .= $c;
                        continue;
                    }
                    if ('true' === $c) {
                        if ('!!' === \substr($s, -2)) {
                            $s = \substr($s, 0, -1) . '0';
                            continue;
                        }
                        if ('"".' === \substr($s, -3)) {
                            $s = \substr($s, 0, -3) . "'1'";
                            continue;
                        }
                        if ('(int)' === \substr($s, -5)) {
                            $s = \substr($s, 0, -5) . '1';
                            continue;
                        }
                        $s .= '!0';
                        continue;
                    }
                    if (false === \strpos(c1, $s[-1])) {
                        $s .= ' ';
                    }
                    $s .= $v[1];
                    continue;
                }
                if (\T_WHITESPACE === $t) {
                    continue;
                }
                if ('_CAST' === \substr(\token_name($t), -5)) {
                    $c = match ($c = \strtolower(\trim(\substr($v[1], 1, -1)))) {
                        'boolean' => 'bool',
                        'double' => 'float',
                        'integer' => 'int',
                        'real' => 'float',
                        default => $c
                    };
                    if ('bool' === $c) {
                        $c = '!!';
                    } else if ('string' === $c) {
                        $c = '"".';
                    } else {
                        $c = '(' . $c . ')';
                    }
                    $s .= $c;
                    continue;
                }
                $s .= $v[1];
                continue;
            }
            if ('(' === $v && $is_array) {
                $s .= '[';
                ++$in_array;
                continue;
            }
            if (')' === $v && $is_array) {
                if ($in_array === $is_array) {
                    $s = \rtrim($s, ',');
                    $s .= ']';
                    --$in_array;
                    --$is_array;
                    continue;
                }
                --$in_array;
            }
            if (']' === $v) {
                // `)['1234']` → `)[1234]`
                // `]['1234']` → `][1234]`
                // `}['1234']` → `}[1234]`
                // `asdf['1234']` → `asdf[1234]`
                if (false !== \strpos(c2, $s[-1]) && \preg_match(m1, $s, $m)) {
                    $s = \substr($s, 0, -(\strlen($m[0]) - 2)) . $m[2] . $v;
                    continue;
                }
                if ('!![' === \substr($s, -3)) {
                    $s = \substr($s, 0, -3) . '!1';
                    continue;
                }
                $s = \rtrim($s, ',') . $v;
                continue;
            }
            // `->{'asdf'}` → `->asdf`
            if ('}' === $v) {
                if (false !== \strpos(c2, $s[-1]) && \preg_match(m2, $s, $m)) {
                    $s = \substr($s, 0, -(\strlen($m[0]) - 2)) . $m[2];
                    continue;
                }
            }
            if (false !== \strpos('+-', $v) && $v === $s[-1]) {
                $s .= ' ' . $v;
                continue;
            }
            if (false !== \strpos(')]}', $v) && ',' === $s[-1]) {
                $s = \rtrim($s, ',') . $v;
                continue;
            }
            $s .= $v;
        }
        return $s;
    }
}