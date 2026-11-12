<?php

namespace x\minify\x {
    function xml(?string $value): ?string {
        return xml\s(xml\a($value ?? ""));
    }
}

namespace x\minify\x\xml {
    const c1 = " \n\r\t";
    const c2 = '"' . "'";
    const c3 = c1 . c2 . '=>';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $i = 0;
        $limit = \strlen($s);
        while ($i < $limit) {
            $c = $s[$i += \strspn($s, c1, $i)];
            if ('<' === $c) {
                if ('!' === ($s[$i + 1] ?? 0)) {
                    // <https://www.w3.org/TR/xml#NT-Comment>
                    if (0 === \substr_compare($s, '--', $i + 2, 2)) {
                        if (false !== ($end = \strpos($s, '-->', $i + 4))) {
                            $r[] = [0, \substr($s, $i, ($end += 3) - $i)];
                            $i = $end;
                            continue;
                        }
                        // Broken
                        $r[] = [0, \substr($s, $i)];
                        break;
                    }
                    // <https://www.w3.org/TR/xml#NT-CDSect>
                    if (0 === \substr_compare($s, '[CDATA[', $i + 2, 7)) {
                        if (false !== ($end = \strpos($s, ']]>', $i + 7))) {
                            $r[] = [3, \substr($s, $i, ($end += 3) - $i)];
                            $i = $end;
                            continue;
                        }
                        // Broken
                        $r[] = [0, \substr($s, $i)];
                        break;
                    }
                    $r[] = $c;
                    ++$i;
                    continue;
                }
                // <https://www.w3.org/TR/xml#NT-PI>
                if ('?' === ($s[$i + 1] ?? 0)) {
                    if (false !== ($end = \strpos($s, '?>', $i + 1))) {
                        $r[] = [2, \substr($s, $i, ($end += 2) - $i)];
                        $i = $end;
                        continue;
                    }
                    // Broken
                    $r[] = [2, \substr($s, $i)];
                    break;
                }
                // <https://www.w3.org/TR/xml/#NT-ETag>
                // <https://www.w3.org/TR/xml/#NT-EmptyElemTag>
                // <https://www.w3.org/TR/xml/#NT-STag>
                if (false !== ($end = \strpos($s, '>', $i + 2))) {
                    $r[] = [1, \substr($s, $i, ($end += 1) - $i)];
                    $i = $end;
                    continue;
                }
                // Broken
                $r[] = [0, \substr($s, $i)];
                break;
            }
            if ($eat = \strcspn($s, '<', $i)) {
                $r[] = \substr($s, $i, $eat);
                $i += $eat;
                continue;
            }
            $r[] = $c;
            ++$i;
        }
        return $r;
    }
    function c(string $s) {
        $i = 0;
        $limit = \strlen($s);
        $r = [];
        while ($i < $limit) {
            $i += \strspn($s, c1, $i);
            if ($i >= $limit) {
                break;
            }
            $r[] = \substr($s, $i, $n = \strcspn($s, c1, $i));
            $i += $n;
        }
        return \implode(' ', $r);
    }
    function e(string $s, $w = "") {
        $i = 0;
        $limit = \strlen($s);
        $r = "";
        while ($i < $limit) {
            $c = $s[$i];
            // <https://www.w3.org/TR/xml#NT-Reference>
            if ('&' === $c && ';' === ($s[$i + ($end = \strcspn($s, c1, $i + 1))] ?? 0)) {
                $e = \substr($s, $i, ++$end);
                $i += $end;
                $r .= $e !== ($f = \html_entity_decode($e, \ENT_HTML5 | \ENT_QUOTES, 'UTF-8')) && false === \strpos('&<>' . $w, $f) ? $f : $e;
                continue;
            }
            if ($eat = \strcspn($s, '&<', $i)) {
                $r .= \substr($s, $i, $eat);
                $i += $eat;
                continue;
            }
            $r .= $c;
            ++$i;
        }
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
                    continue;
                }
                if (1 === $v[0]) {
                    $t = t($v[1]);
                    // <https://www.w3.org/TR/xml#NT-ETag>
                    if ('/' === $t[0][0]) {
                        $s .= '<' . $t[0];
                        if (\is_array($p = p2v($a, $k)) && 1 === $p[0] && '/' !== $p[1][1]) {
                            if (\substr($t[0], 1) === ($o = t($p[1]))[0] && 0 === $o[2]) {
                                $s = \substr($s, 0, -(\strlen($t[0]) + 2)) . '/>';
                                continue;
                            }
                        }
                        $s .= '>';
                        continue;
                    }
                    // <https://www.w3.org/TR/xml#NT-EmptyElemTag>
                    // <https://www.w3.org/TR/xml#NT-STag>
                    $s .= '<' . $t[0];
                    // <https://www.w3.org/TR/xml#NT-Attribute>
                    if ($t[1]) {
                        foreach ($t[1] as $kk => $vv) {
                            $s .= ' ' . $kk . '=' . '"' . e($vv, '"') . '"';
                        }
                    }
                    $s .= ($t[2] ? '/' : "") . '>';
                    continue;
                }
                // <https://www.w3.org/TR/xml#NT-PI>
                if (2 === $v[0]) {
                    $t = t($v[1]);
                    $s .= '<' . $t[0];
                    if ($t[1]) {
                        foreach ($t[1] as $kk => $vv) {
                            $s .= ' ' . $kk . '=' . '"' . e($vv, '"') . '"';
                        }
                    }
                    $s .= '?>';
                    continue;
                }
                $s .= $v[1];
                continue;
            }
            $s .= e(c($v));
        }
        return $s;
    }
    function t(string $s) {
        $i = 1; // Skip `<`
        $limit = \strlen($s);
        $r = [null, [], 0];
        if ($eat = \strcspn($s, c3, $i)) {
            $r[0] = \substr($s, $i, $eat);
            $i += $eat;
        }
        while ($i < $limit) {
            if ('>' === ($c = $s[$i += \strspn($s, c1, $i)])) {
                break;
            }
            if ($eat = \strcspn($s, c3, $i)) {
                $key = \substr($s, $i, $eat);
                $i += $eat + \strspn($s, c1, $i + $eat);
                if ('=' === ($s[$i] ?? 0)) {
                    ++$i;
                    $i += \strspn($s, c1, $i);
                    $c = $s[$i] ?? 0;
                    if (false !== \strpos(c2, $c)) {
                        $start = ++$i;
                        $i += $eat = \strcspn($s, $c, $i);
                        $value = \substr($s, $start, $eat);
                        ++$i;
                    } else {
                        $start = $i;
                        $i += $eat = \strcspn($s, c3, $i);
                        $value = \substr($s, $start, $eat);
                    }
                    $r[1][$key] = $value;
                    continue;
                }
                if ('?' === $key && '>' === $s[$i]) {
                    break;
                }
                if ('/' === $key && '>' === $s[$i += \strspn($s, c1, $i)]) {
                    $r[2] = 1;
                    break;
                }
                continue;
            }
            ++$i;
        }
        return $r;
    }
}