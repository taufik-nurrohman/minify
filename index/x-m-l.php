<?php

namespace x\minify {
    function x_m_l(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $to = "";
        while (false !== ($chop = \strpbrk($from, '<&'))) {
            if ("" !== ($v = \strstr($from, $chop[0], true))) {
                $from = $chop;
                $to .= x_m_l\n($v, $to);
            }
            // `<…`
            if ('<' === $chop[0]) {
                if (\strlen($chop) > 1 && false !== \strpos(" \n\t", $chop[1])) {
                    $from = \substr($from, 1);
                    $to .= '<';
                    continue;
                }
                // <https://html.spec.whatwg.org/multipage/syntax.html#comments>
                // `<!--…`
                if (0 === \strpos($chop, '<!--') && false !== ($n = \strpos($chop, '-->'))) {
                    $from = \substr($from, \strlen($chop = \substr($chop, 0, $n + 3)));
                    $to = \rtrim($to);
                    continue;
                }
                // <https://html.spec.whatwg.org/multipage/syntax.html#cdata-sections>
                // `<![CDATA[…`
                if (0 === \strpos($chop, '<![CDATA[') && false !== ($n = \strpos($chop, ']]>'))) {
                    $from = \ltrim(\substr($from, \strlen($chop = \substr($chop, 0, $n + 3))));
                    $to = \rtrim($to) . $chop;
                    continue;
                }
                if (\preg_match('/^<(?>"[^"]*"|\'[^\']*\'|[^>])+>/', $chop, $m)) {
                    $from = \substr($from, $b = \strlen($m[0]));
                    if (false !== \strpos('!?', $m[0][1])) {
                        $from = \ltrim($from);
                        $to = \rtrim($to) . x_m_l\e($m[0]);
                        continue;
                    }
                    $to .= x_m_l\e($m[0]);
                    continue;
                }
                $from = \substr($from, 1);
                $to .= '<';
                continue;
            }
            if ('&' === $chop[0]) {
                if (\strpos($chop, ';') > 1 && \preg_match('/^&(?>#x[a-f\d]{1,6}|#\d{1,7}|[a-z][a-z\d]{1,31});/i', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    $v = \html_entity_decode($m[0], \ENT_HTML5 | \ENT_QUOTES, 'UTF-8');
                    if (false !== \strpos('&<>', $v)) {
                        $to .= $m[0];
                        continue;
                    }
                    $to .= $v;
                    continue;
                }
                $from = \substr($from, 1);
                $to .= '&';
                continue;
            }
            $from = "";
            $to .= x_m_l\n($chop);
        }
        if ("" !== $from) {
            $to .= x_m_l\n($from);
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}

namespace x\minify\x_m_l {
    function e(string $from): string {
        $to = "";
        foreach (\preg_split('/("[^"]*"|\'[^\']*\'|[^!\/<=>?\s]+)/', $from, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if ("" === ($v = \trim($v))) {
                $to .= ' ';
                continue;
            }
            $to .= $v;
        }
        if ('/>' === \substr($to, -2)) {
            return \rtrim(\substr($to, 0, -2)) . '/>';
        }
        if ('>' === \substr($to, -1)) {
            return \rtrim(\substr($to, 0, -1)) . '>';
        }
        return $to;
    }
    function n(string $from): string {
        return \trim(\preg_replace('/\s+/', ' ', $from));
    }
}