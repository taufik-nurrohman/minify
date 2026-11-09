<?php

namespace x\minify\x {
    function php(?string $value): ?string {
        return php\s(php\a($value ?? ""));
    }
}

namespace x\minify\x\php {
    const c1 = '!"#$%&()*+,-./:;<=>?@[\]^_`{|}~' . "' \n\r\t";
    const c2 = '"' . "'";
    const m1 = '~[\w\x80-\xff)}\]]\[([' . c2 . '])([+-]?\d+)\1$~';
    const m2 = '~->\{([' . c2 . '])([a-zA-Z_\x80-\xff][\w\x80-\xff]*)\1$~';
    const m3 = '~([' . c2 . '])([+-]?\d+)\1$~';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        foreach (\token_get_all($s) as $v) {
            if (\is_array($v) && \T_WHITESPACE === $v[0]) {
                continue;
            }
            $r[] = $v;
        }
        return $r;
    }
    function n2t(array $a, int $k, int &$at = 0) {
        $r = $a[++$k] ?? 0;
        while (\is_array($r) && \in_array($r[0], [\T_COMMENT, \T_DOC_COMMENT], true)) {
            $r = $a[++$k] ?? 0;
        }
        $at = $k;
        return $r;
    }
    function p2v(array $a, int $k, int &$at = 0) {
        $r = $a[--$k] ?? 0;
        while (\is_array($r) && \in_array($r[0], [\T_COMMENT, \T_DOC_COMMENT], true)) {
            $r = $a[--$k] ?? 0;
        }
        $at = $k;
        return $r;
    }
    function s(array $a) {
        if (!$a) {
            return null;
        }
        $array = false;
        $s = "";
        $stack = [];
        foreach ($a as $k => $v) {
            if (\is_array($v)) {
                $t = $v[0];
                // Can be `array $asdf` or `array (`
                if (\T_ARRAY === $t) {
                    if (!$array = '(' === n2t($a, $k)) {
                        $s .= $v[1];
                    }
                    continue;
                }
                if (\T_CLOSE_TAG === $t) {
                    if ($k === \count($a) - 1) {
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
                            $c = \rtrim(\trim(\ltrim(\substr($v[1], 2, -2), '!*'), "\n\r"));
                            $c = false !== \strpos($c, "\n") ? "\n" . $c . "\n" : \trim($c);
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
                    $v[1] = false === \strpos($v[1], '.') ? $v[1] : \trim($v[1], '0');
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
                if (false === \strpos(c1, $v[1][0]) && false === \strpos(c1, $s[-1])) {
                    $s .= ' ';
                }
                $s .= $v[1];
                continue;
            }
            if ('(' === $v) {
                $stack[] = $array;
                $s .= $array ? '[' : $v;
                $array = false;
                continue;
            }
            if (')' === $v && $stack) {
                $s = \rtrim($s, ',');
                $s .= \array_pop($stack) ? ']' : $v;
                continue;
            }
            // `,)` → `)`
            // `,]` → `]`
            if (',' === $v && \is_string($p = n2t($a, $k)) && false !== \strpos(')]', $p[0])) {
                continue;
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