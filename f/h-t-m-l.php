<?php

namespace x\minify {
    function h_t_m_l(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $to = "";
        while (false !== ($chop = \strpbrk($from, '<&'))) {
            if ("" !== ($v = \substr($from, 0, \strlen($from) - \strlen($chop)))) {
                $from = \substr($from, \strlen($v));
                if ('>' === \substr($to, -1) && \preg_match('/<[a-z\d][a-z\d:-]*(?>"[^"]*"|\'[^\']*\'|[^\/>])*>$/', $to)) {
                    if (' ' === $v && '</' === \substr($from, 0, 2)) {
                        $to .= $v;
                        continue;
                    }
                    $v = \ltrim($v);
                }
                if ('</' === \substr($from, 0, 2)) {
                    $v = \rtrim($v);
                }
                $to .= \preg_replace(['/^\s{2,}|\s{2,}$/', '/\s+/'], ["", ' '], $v);
            }
            // `<…`
            if ('<' === $chop[0]) {
                // <https://html.spec.whatwg.org/multipage/syntax.html#comments>
                // `<!--…`
                if (0 === \strpos($chop, '<!--') && false !== ($n = \strpos($chop, '-->'))) {
                    $from = \substr($from, \strlen($chop = \substr($chop, 0, $n + 3)));
                    // <https://en.wikipedia.org/wiki/Conditional_comment>
                    if ('<![endif]-->' === \substr($chop, -12)) {
                        $to .= \substr($chop, 0, $n = \strpos($chop, '>') + 1) . h_t_m_l(\substr($chop, $n, -12)) . \substr($chop, -12);
                    }
                    if (' ' !== \strpos($to, -1) && ' ' !== $from[0]) {
                        continue;
                    }
                    $from = \ltrim($from);
                    $to = \rtrim($to) . ' ';
                    continue;
                }
                // <https://html.spec.whatwg.org/multipage/syntax.html#cdata-sections>
                // `<![CDATA[…`
                if (0 === \strpos($chop, '<![CDATA[') && false !== ($n = \strpos($chop, ']]>'))) {
                    $from = \substr($from, \strlen($chop = \substr($chop, 0, $n + 3)));
                    $to .= $chop;
                    continue;
                }
                if (\preg_match('/^<(?>"[^"]*"|\'[\']*\'|[^>])+>/', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $m[0] = h_t_m_l\e($m[0]);
                    // `<!DOCTYPE…`
                    if ('!' === $m[0][1]) {
                        $to .= $m[0];
                        continue;
                    }
                    if (0 === \strpos($m[0], '</')) {}
                    $to .= $m[0];
                    continue;
                }
                $from = \substr($from, 1);
                $to .= '<';
                continue;
            }
            if ('&' === $chop[0]) {
                if (\preg_match('/^&(?>#x[a-f\d]{1,6}|#\d{1,7}|[a-z][a-z\d]{1,31});/i', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $to .= $m[0];
                    continue;
                }
                $from = \substr($from, 1);
                $to .= '&';
                continue;
            }
            $from = \substr($from, \strlen($chop));
            $to .= $chop;
        }
        if ("" !== $from) {
            $to .= \preg_replace(['/^\s{2,}/', '/\s+/'], ["", ' '], $from);
        }
        return "" !== $to ? $to : null;
    }
}

namespace x\minify\h_t_m_l {
    function e(string $from): string {
        $to = "";
        foreach (\preg_split('/("[^"]*"|\'[^\']*\'|[\/<=>])/', $from, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if ("" === ($v = \trim($v))) {
                $to .= ' ';
                continue;
            }
            $to .= $v;
        }
        return $to;
    }
}