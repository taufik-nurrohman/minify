<?php

namespace x\minify {
    function x_m_l(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $c1 = " \n\r\t";
        $to = "";
        while (false !== ($chop = \strpbrk($from, '<&' . $c1))) {
            if ("" !== ($v = \strstr($from, $c = $chop[0], true))) {
                $from = $chop;
                $to .= $v;
            }
            // `<…`
            if ('<' === $c) {
                if (false !== \strpos($c1, $chop[1] ?? $c)) {
                    $from = \substr($from, 1);
                    $to .= '<';
                    continue;
                }
                // <https://html.spec.whatwg.org/multipage/syntax.html#comments>
                // `<!--…`
                if (0 === \strpos($chop, '<!--') && false !== ($n = \strpos($chop, '-->'))) {
                    $from = \substr($from, \strlen(\substr($chop, 0, $n + 3)));
                    continue;
                }
                // <https://html.spec.whatwg.org/multipage/syntax.html#cdata-sections>
                // `<![CDATA[…`
                if (0 === \strpos($chop, '<![CDATA[') && false !== ($n = \strpos($chop, ']]>'))) {
                    $from = \substr($from, \strlen($v = \substr($chop, 0, $n + 3)));
                    $to .= $v;
                    continue;
                }
                if (\preg_match('/^<(?>"[^"]*"|\'[^\']*\'|[^>])+>/', $chop, $m)) {
                    $from = \trim(\substr($from, \strlen($m[0])));
                    $to = \trim($to);
                    foreach (\preg_split('/("[^"]*"|\'[^\']*\'|\s+)/', $m[0], -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
                        $to .= "" === ($v = \trim($v)) ? ' ' : $v;
                    }
                    if ('/>' === \substr($to, -2)) {
                        $to = \trim(\substr($to, 0, -2)) . '/>';
                    } else if ('?>' === \substr($to, -2)) {
                        $to = \trim(\substr($to, 0, -2)) . '?>';
                    } else {
                        $to = \trim(\substr($to, 0, -1)) . '>';
                    }
                    continue;
                }
                $from = \substr($from, 1);
                $to .= '<';
                continue;
            }
            if ('&' === $c && \strpos($chop, ';') > 1 && \preg_match('/^&(?>#x[a-f\d]{1,6}|#\d{1,7}|[a-z][a-z\d]{1,31});/i', $chop, $m)) {
                $from = \substr($from, \strlen($m[0]));
                $v = \html_entity_decode($m[0], \ENT_HTML5 | \ENT_QUOTES, 'UTF-8');
                if (false !== \strpos('&<>', $v)) {
                    $to .= $m[0];
                    continue;
                }
                $to .= $v;
                continue;
            }
            if ($n = \strspn($chop, $c1)) {
                $from = \substr($from, $n);
                $to .= ' ';
                continue;
            }
            $from = "";
            $to .= $chop;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}