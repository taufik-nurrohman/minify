<?php

namespace x\minify\x {
function json(?string $value): ?string {
    if ("" === ($value = $value ?? "")) {
        return null;
    }
    $i = 0;
    $limit = \strlen($value);
    $s = "";
    $w = " \n\r\t";
    while ($i < $limit) {
        $c = $value[$i += \strspn($value, $w, $i)];
        if ('"' === $c) {
            $start = $i++;
            while ($i < $limit) {
                $i += \strcspn($value, "\"\\", $i);
                if ('"' === ($value[$i] ?? "")) {
                    break;
                }
                $i += 2;
            }
            $s .= \substr($value, $start, $i - $start + 1);
            ++$i;
            continue;
        }
        $s .= $c;
        ++$i;
    }
    return "" !== $s ? $s : null;
}
}