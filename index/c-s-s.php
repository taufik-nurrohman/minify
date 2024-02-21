<?php

// <https://www.w3.org/TR/CSS22/syndata.html#tokenization>
// <https://www.w3.org/TR/css-syntax-3#token-diagrams>
// <https://www.w3.org/TR/selectors-4>

namespace x\minify {
    function c_s_s(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $to = "";
        while (false !== ($chop = \strpbrk($from, '/"\'[{+,>~'))) {
            if ("" !== ($v = \substr($from, 0, \strlen($from) - \strlen($chop)))) {
                $from = \substr($from, \strlen($v));
                $to .= $v;
            }
            // `/* … */`
            if (0 === \strpos($chop, '/*') && \preg_match('/^\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', $chop, $m)) {
                $from = \ltrim(\substr($from, \strlen($m[0])));
                // `/*! … */` or `/** … */`
                if (false !== \strpos('!*', $m[0][2])) {
                    $to .= '/*' . \trim(\substr($m[0], 3, -2)) . '*/';
                // !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
                } else if ("" !== $to && false === \strpos('!"\'()+,-/:;=>[]^{|}~', \substr($to, -1))) {
                    $to .= ' ';
                }
                continue;
            }
            if ('""' === \substr($chop, 0, 2)) {
                $from = \substr($from, 2);
                $to .= '""';
                continue;
            }
            if ("''" === \substr($chop, 0, 2)) {
                $from = \substr($from, 2);
                $to .= "''";
                continue;
            }
            if (false !== \strpos('"\'', $chop[0]) && \preg_match('/^(?>' . c_s_s\q . ')/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $to .= $m[0];
                continue;
            }
            if (false !== \strpos('+,>~', $chop[0])) {
                $from = \ltrim(\substr($from, 1));
                $to = \rtrim($to) . $chop[0];
                continue;
            }
            if ('[' === $chop[0] && \preg_match('/^\[(?>' . c_s_s\q . '|[^]])+\]/', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                if ("" !== $to && false !== \strpos(" \n\r\t", \substr($to, -1))) {
                    $to = \rtrim($to) . ' ';
                }
                $to .= c_s_s\a($m[0]);
                continue;
            }
            if ('{' === $chop[0] && \preg_match('/^\{(?>' . c_s_s\q . '|[^{}]|(?R))*\}/', $chop, $m)) {
                $from = \ltrim(\substr($from, \strlen($m[0])));
                $to = \rtrim($to) . c_s_s\r($m[0]);
                continue;
            }
            $from = \substr($from, \strlen($chop));
            $to .= $chop;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== $to ? $to : null;
    }
}

namespace x\minify\c_s_s {
    \define(__NAMESPACE__ . "\\q", '"(?>\\\\"|[^"])*"|\'(?>\\\\\'|[^\'])*\'');
    // Minify attribute(s)
    function a(string $to): string {
        $from = \substr($to, 1, -1);
        $to = "";
        foreach (\preg_split('/(' . q . '|[$*=^|~]|\s+)/', $from, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if ("" === ($v = \trim($v))) {
                continue;
            }
            // <https://mothereff.in/unquoted-attributes>
            if (false !== \strpos('"\'', $v[0]) && "" !== ($test = \substr($v, 1, -1))) {
                if ('-' === $test || 0 === \strpos($test, '--') || \is_numeric($test[0]) || ('-' === $test[0] && \is_numeric($test[1]))) {
                    $to .= $v;
                    continue;
                }
                if (!\preg_match('/^[\w-]+$/', $test)) {
                    $to .= $v;
                    continue;
                }
                $to .= $test;
                continue;
            }
            if (false === \strpos('"$\'*=^|~', \substr($to, -1)) && false === \strpos('"$\'*=^|~', $v[0])) {
                $to .= ' ';
            }
            $to .= $v;
        }
        return '[' . $to . ']';
    }
    // Minify rule(s)
    function r(string $to): string {
        $to = \substr($to, 1, -1);
        return '{' . \trim($to) . '}';
    }
    // Minify selector(s)
    function s(string $to): string {

    }
}