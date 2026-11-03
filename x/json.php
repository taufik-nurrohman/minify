<?php

namespace x\minify\x {
    function json(?string $value): ?string {
        return json\s(json\a($value ?? ""));
    }
}

namespace x\minify\x\json {
    const c1 = " \n\r\t";
    const c2 = '"';
    const c3 = "\\";
    const c4 = c2 . c3;
    function a(string $s) {
        $r = [];
        if ("" === $s) {
            return $r;
        }
        $i = 0;
        $limit = \strlen($s);
        while ($i < $limit) {
            $c = $s[$i += \strspn($s, c1, $i)];
            if (c2 === $c) {
                $start = $i++;
                while ($i < $limit) {
                    $i += \strcspn($s, c4, $i);
                    if ($c === ($s[$i] ?? "")) {
                        break;
                    }
                    $i += 2;
                }
                $r[] = \substr($s, $start, ++$i - $start);
                continue;
            }
            $r[] = $c;
            ++$i;
        }
        return $r;
    }
    function s(array $a) {
        return $a ? \implode("", $a) : null;
    }
}