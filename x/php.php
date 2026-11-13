<?php

namespace x\minify\x {
    function php(?string $value): ?string {
        return php\s(php\a($value ?? ""));
    }
}

namespace x\minify\x\php {
    const c111 = " \f\n\r\t\v";
    const c1 = '!"#$%&()*+,-./:;<=>?@[\]^_`{|}~' . "' \n\r\t";
    const c2 = '"' . "'";
    const m1 = '~[\w\x80-\xff)}\]]\[([' . c2 . '])([+-]?\d+)\1$~';
    const m2 = '~->\{([' . c2 . '])([a-zA-Z_\x80-\xff][\w\x80-\xff]*)\1$~';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        foreach (\token_get_all($s) as $v) {
            if (\is_array($v)) {
                $t = $v[0];
                if (\T_CLOSE_TAG === $t) {
                    $r[] = [4, \trim($v[1]) . (false !== \strpos(c111, $v[1][-1]) ? "\n" : ""), false];
                    continue;
                }
                if (\T_COMMENT === $t || \T_DOC_COMMENT === $t) {
                    $r[] = [0, $v[1]];
                    continue;
                }
                if (\T_CONSTANT_ENCAPSED_STRING === $t || \T_ENCAPSED_AND_WHITESPACE === $t) {
                    $r[] = [2, $v[1], \T_ENCAPSED_AND_WHITESPACE === $t];
                    continue;
                }
                if (\T_DNUMBER === $t || \T_LNUMBER === $t) {
                    $r[] = [1, $v[1], \T_LNUMBER === $t];
                    continue;
                }
                if (\T_END_HEREDOC === $t || \T_START_HEREDOC === $t) {
                    $r[] = [5, $v[1], \T_START_HEREDOC === $t];
                    continue;
                }
                if (\T_OPEN_TAG === $t) {
                    $r[] = [4, \trim($v[1]) . ' ', true];
                    continue;
                }
                if (\T_OPEN_TAG_WITH_ECHO === $t) {
                    $r[] = [4, $v[1], true];
                    continue;
                }
                if (\T_WHITESPACE === $t) {
                    continue;
                }
                if ('_CAST' === \substr(\token_name($t), -5)) {
                    $r[] = [6, '(' . \trim(\substr($v[1], 1, -1)) . ')'];
                    continue;
                }
                $r[] = $v[1];
                continue;
            }
            $r[] = $v;
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
        $array = false;
        $s = "";
        $stack = [];
        foreach ($a as $k => $v) {
            if (\is_array($v)) {
                if (0 === $v[0]) {
                    if (false === \strpos('!*', $v[1][2] ?? "\0") && false === \strpos($v[1], '@license')) {
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
                        if ("" !== ($c = \trim(\substr($c, 2, -2)))) {
                            $s .= '/*' . $c . '*/';
                        }
                    } else {
                        $s .= $c;
                    }
                    continue;
                }
                if (1 === $v[0]) {
                    $n = \strtolower(\strtr($v[1], ['_' => ""]));
                    $n = false === \strpos($n, '.') ? $n : \trim($n, '0');
                    if ("" === $n) {
                        $n = '0';
                    } else if ('.' === $n) {
                        $n = '0.0';
                    } else if ('.' === $n[-1]) {
                        $n .= '0';
                    }
                    if ('!!' === \substr($s, -2)) {
                        $s = \substr($s, 0, -2) . '!' . (($v[2] ? (int) $n : (float) $n) ? '0' : '1');
                        continue;
                    }
                    if ('"".' === \substr($s, -3)) {
                        $s = \substr($s, 0, -3) . "'" . $n . "'";
                        continue;
                    }
                    if ('(float)' === \substr($s, -7)) {
                        $s = \substr($s, 0, -7) . $n . ($v[2] ? '.0' : "");
                        continue;
                    }
                    if ('(int)' === \substr($s, -5) && $v[2]) {
                        $s = \substr($s, 0, -5) . $n;
                        continue;
                    }
                    if (false === \strpos(c1, $s[-1]) && '.' !== $n[0]) {
                        $s .= ' ';
                    }
                    $s .= $n;
                    continue;
                }
                if (2 === $v[0]) {
                    if ($v[2]) {
                        $s .= \strtr($v[1], ["S\n" => "\\x53\n", "S\r" => "\\x53\r"]);
                        continue;
                    }
                    if ('!!' === \substr($s, -2)) {
                        $s = \substr($s, 0, -2) . '!' . (\substr($v[1], 1, -1) ? '0' : '1');
                        continue;
                    }
                    if ('"".' === \substr($s, -3)) {
                        $s = \substr($s, 0, -3) . $v[1];
                        continue;
                    }
                    $n = \strtr(\substr($v[1], 1, -1), ['_' => ""]);
                    if ('(float)' === \substr($s, -7) && \is_numeric($n)) {
                        $n = (string) ((float) $n);
                        $s = \substr($s, 0, -7) . $n . (false === \strpos($n, '.') ? '.0' : "");
                        continue;
                    }
                    if ('(int)' === \substr($s, -5) && \is_numeric($n)) {
                        $n = (string) (((int) $n));
                        $s = \substr($s, 0, -5) . $n;
                        continue;
                    }
                    $s .= "''" === $v[1] ? '""' : $v[1];
                    continue;
                }
                // if (3 === $v[0]) {}
                if (4 === $v[0]) {
                    if (!$v[2] && $k === \count($a) - 1) {
                        break;
                    }
                    if ($s && ';' === $s[-1]) {
                        $s = \rtrim($s, ';');
                    }
                    $s .= \strtolower($v[1]);
                    continue;
                }
                if (5 === $v[0]) {
                    $s .= $v[2] ? '<<<' . ("'" === $v[1][3] ? "'S'" : 'S') . "\n" : 'S';
                    continue;
                }
                if (6 === $v[0]) {
                    $c = match ($c = \strtolower($v[1])) {
                        '(boolean)' => '(bool)',
                        '(double)' => '(float)',
                        '(integer)' => '(int)',
                        '(real)' => '(float)',
                        default => $c
                    };
                    if ('(bool)' === $c) {
                        $c = '!!';
                    } else if ('(string)' === $c) {
                        $c = '"".';
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
            if (')' === $v) {
                if ('(object)[](' === \substr($s, -11)) {
                    $s = \substr($s, 0, -1);
                    \array_pop($stack);
                    continue;
                }
                if ($stack) {
                    $s = \rtrim($s, ',');
                    $s .= \array_pop($stack) ? ']' : $v;
                    continue;
                }
            }
            // `,)` → `)`
            // `,]` → `]`
            if (',' === $v && \is_string($p = n2t($a, $k)) && false !== \strpos(')]', $p[0])) {
                continue;
            }
            if ('=>' === $v) {
                if (\is_array($p = p2v($a, $k)) && 2 === $p[0] && !$p[2] && \strspn($p[1], '0123456789', 1) === \strlen($p[1]) - 2) {
                    $key = \substr($p[1], 1, -1);
                    $s = \substr($s, 0, -\strlen($p[1])) . ("" !== $key ? $key : '""') . $v;
                    continue;
                }
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
            if (false !== \strpos('+-', $v[0]) && $v[0] === $s[-1]) {
                $s .= ' ' . $v;
                continue;
            }
            $w = \strtolower($v);
            // Can be `array $asdf` or `array (`
            if ('array' === $w) {
                if (!$array = '(' === n2t($a, $k)) {
                    $s .= $w;
                }
                continue;
            }
            if (false === \strpos(c1, $v[0]) && false === \strpos(c1, $s[-1])) {
                $s .= ' ';
            }
            if ('echo' === $w || 'print' === $w) {
                if (\is_array($p = p2v($a, $k)) && 4 === $p[0] && $p[2]) {
                    $s = \substr($s, 0, -(\strlen($p[1]) - 2)) . '=';
                    continue;
                }
                $s .= 'echo';
                continue;
            }
            if ('false' === $w) {
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
            if ('null' === $w) {
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
                $s .= $w;
                continue;
            }
            if ('true' === $w) {
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
            if ("\\stdclass" === $w && 'new' === \substr($s, -3)) {
                $s = \substr($s, 0, -3) . '(object)[]';
                continue;
            }
            if ('stdclass' === $w && 'new ' === \substr($s, -4)) {
                $s = \substr($s, 0, -4) . '(object)[]';
                continue;
            }
            $s .= $v;
        }
        return $s;
    }
}