<?php

namespace x\minify\x {
    function json(?string $value): ?string {
        if ("" === ($value = $value ?? "")) {
            return null;
        }
        if ('""' === $value || '[]' === $value || 'false' === $value || 'null' === $value || 'true' === $value || '{}' === $value) {
            return $value;
        }
        if (\is_numeric($value)) {
            return \trim($value);
        }
        $i = 0;
        $limit = \strlen($value);
        $s = "";
        while ($i < $limit) {
            $c = $value[$i += \strspn($value, json\c1, $i)];
            if (json\c2 === $c) {
                $start = $i++;
                while ($i < $limit) {
                    $i += \strcspn($value, json\c4, $i);
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

namespace x\minify\x\json {
    const c1 = " \n\r\t";
    const c2 = '"';
    const c3 = "\\";
    const c4 = c2 . c3;
}