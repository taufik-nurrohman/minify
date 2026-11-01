<?php

namespace x\minify\x {
    function json(?string $value): ?string {
        if ("" === ($value = $value ?? "")) {
            return null;
        }
        if ('""' === $value || '[]' === $value || 'false' === $value || 'null' === $value || 'true' === $value || '{}' === $value || \is_numeric($value)) {
            return $value;
        }
        $i = 0;
        $limit = \strlen($value);
        $s = "";
        $c1 = " \n\r\t";
        $c2 = '"';
        $c3 = "\\";
        $c4 = $c2 . $c3;
        while ($i < $limit) {
            $c = $value[$i += \strspn($value, $c1, $i)];
            if ($c2 === $c) {
                $start = $i++;
                while ($i < $limit) {
                    $i += \strcspn($value, $c4, $i);
                    if ($c === ($value[$i] ?? "")) {
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