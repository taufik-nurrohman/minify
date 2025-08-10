<?php

namespace x\minify {
    function j_s_o_n(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        if ('""' === $from || '[]' === $from || 'false' === $from || 'null' === $from || 'true' === $from || '{}' === $from || \is_numeric($from)) {
            return $from;
        }
        $count = \strlen($from);
        $i = 0;
        $to = "";
        while ($i < $count) {
            $c = $from[$i];
            if (' ' === $c || "\n" === $c || "\r" === $c || "\t" === $c) {
                $i++;
                continue;
            }
            if ('"' === $c) {
                $i++;
                $to .= $c;
                while ($i < $count) {
                    if ('"' === ($c = $from[$i])) {
                        $j = $i - 1;
                        $x = 0;
                        while ($j >= 0 && "\\" === $from[$j]) {
                            $j--;
                            $x++;
                        }
                        if (0 === $x % 2) {
                            $i++;
                            $to .= $c;
                            break;
                        }
                    }
                    $i++;
                    $to .= $c;
                }
                continue;
            }
            $i++;
            $to .= $c;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}