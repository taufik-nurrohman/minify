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
    const c4 = c1 . '/>?';
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $i = 0;
        $limit = \strlen($s);
        while ($i < $limit) {
            $c = $s[$i];
            if ($at = \strspn($s, c1, $i)) {
                if ('<' === $s[$i + $at]) {
                    $i += $at;
                    continue;
                }
            }
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
            if ($at = \strcspn($s, '<', $i)) {
                $r[] = \substr($s, $i, $at);
                $i += $at;
                continue;
            }
            $r[] = $c;
            ++$i;
        }
        return $r;
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
            if ($at = \strcspn($s, '&<', $i)) {
                $r .= \substr($s, $i, $at);
                $i += $at;
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
        $w = 0;
        foreach ($a as $k => $v) {
            if (\is_array($v)) {
                $w = 0;
                if (0 === $v[0]) {
                    continue;
                }
                if (1 === $v[0]) {
                    $t = t($v[1]);
                    // <https://www.w3.org/TR/xml#NT-ETag>
                    if ('/' === $t[0][0]) {
                        $s .= '<' . $t[0];
                        $w = 0;
                        if (\is_array($p = p2v($a, $k)) && 1 === $p[0] && '/' !== $p[1][1]) {
                            // `<asdf asdf="asdf"></asdf>` → `<asdf asdf="asdf"/>`
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
                        foreach ($t[1] as $m) {
                            // <https://www.w3.org/TR/SVG2/text.html#TextLayoutPreMultiline>
                            // <https://www.w3.org/TR/xml#sec-white-space>
                            if ('white-space' === $m[0] || 'xml:space' === $m[0]) {
                                $w = [$m[0], $m[1] ? \trim($m[1], c2) : 0];
                            }
                            $s .= ' ' . $m[0] . ($m[1] ? '=' . e($m[1], '"' === $m[1][0] ? '"' : "'") : "");
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
                        foreach ($t[1] as $m) {
                            $s .= ' ' . $m[0] . ($m[1] ? '=' . e($m[1], '"' === $m[1][0] ? '"' : "'") : "");
                        }
                    }
                    $s .= '?>';
                    continue;
                }
                $s .= $v[1];
                continue;
            }
            $s .= e(w($v, $w));
        }
        return $s;
    }
    function t(string $s) {
        $i = 1; // Move past `<`
        $limit = \strlen($s);
        $r = ["", [], 0];
        if (false !== \strpos('/?', $s[$i])) {
            $r[0] .= $s[$i++];
        }
        if ($at = \strcspn($s, c4, $i)) {
            $r[0] .= \substr($s, $i, $at);
            $i += $at;
        }
        while ($i < $limit) {
            if ('>' === ($c = $s[$i += \strspn($s, c1, $i)])) {
                break;
            }
            if ($at = \strcspn($s, c3, $i)) {
                $key = \substr($s, $i, $at);
                $i += $at;
                if ('=' === $s[$i += \strspn($s, c1, $i)]) {
                    ++$i; // Move past `=`
                    $i += \strspn($s, c1, $i);
                    if (false !== \strpos(c2, $c = $s[$i])) {
                        $value = \substr($s, $i, $at = \strcspn($s, $c, $i + 1) + 2);
                    } else {
                        // Broken (value with no surrounding `"` or `'`)
                        $value = \substr($s, $i, $at = \strcspn($s, c3, $i + 1) + 1);
                    }
                    $i += $at;
                    $r[1][] = [$key, $value];
                    continue;
                }
                // Broken (no value)
                if ($at = \strcspn($s, c4, $i)) {
                    $r[1][] = [\substr($s, $i, $at), null];
                    continue;
                }
                if ('?' === $r[0][0] && '?' === $key && '>' === $s[$i]) {
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
    function w(string $s, $w = 0) {
        if ($w && ('white-space' === $w[0] && 'pre' === $w[1] || 'xml:space' === $w[0] && 'preserve' === $w[1])) {
            return $s;
        }
        $i = 0;
        $limit = \strlen($s);
        $r = [];
        $x = $w && 'white-space' === $w[0] && 'pre-line' === $w[1] ? " \t" : c1;
        while ($i < $limit) {
            $i += \strspn($s, $x, $i);
            if ($i >= $limit) {
                break;
            }
            $r[] = \substr($s, $i, $n = \strcspn($s, $x, $i));
            $i += $n;
        }
        $r = \implode(' ', $r);
        return $w && 'white-space' === $w[0] && 'pre-line' === $w[1] ? \strtr($r, ["\n " => "\n"]) : $r;
    }
}