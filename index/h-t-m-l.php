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
                if ('>' === \substr($to, -1) && \preg_match('/<(?>"[^"]*"|\'[^\']*\'|[^\/>])+>$/', $to, $m)) {
                    $n = \substr(\strtok($m[0], " \n\r\t>"), 1);
                    if (false !== \strpos(',br,hr,wbr,', ',' . $n . ',')) {
                        $v = \rtrim($v);
                    }
                    if ('/' !== $n[0]) {
                        if (' ' === $v && '</' === \substr($from, 0, 2)) {
                            $to .= $v;
                            continue;
                        }
                        if (false === \strpos(',img,input,', ',' . $n . ',')) {
                            $v = \ltrim($v);
                        }
                    }
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
                    $n = \substr(\strtok($m[0], " \n\r\t>"), 1);
                    // `<!DOCTYPE…`
                    if ('!' === $n[0]) {
                        $to .= $m[0];
                        continue;
                    }
                    if (false !== \strpos(',pre,script,style,textarea,', ',' . $n . ',')) {
                        $from = \substr($from, ($e = \strpos($chop, '</' . $n . '>')) + 1);
                        $to .= \substr($chop, 0, $e) . '</' . $n . '>';
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
    // A number of attribute(s) are boolean attribute(s). The presence of a boolean attribute on an element represents
    // the `true` value, and the absence of the attribute represents the `false` value. If the attribute is present, its
    // value must either be the empty string or a value that is an ASCII case-insensitive match for the attribute’s
    // canonical name, with no leading or trailing white-space. The values “true” and “false” are not allowed on boolean
    // attribute(s). To represent a `false` value, the attribute has to be omitted altogether.
    //
    // <https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attributes>
    \define(__NAMESPACE__ . "\\a", 'allow(?:fullscreen|paymentrequest)|async|auto(?:focus|play)|checked|controls|def(?:ault|er)|disabled|formnovalidate|hidden|ismap|itemscope|loop|multiple|muted|no(?:module|validate)|open|playsinline|re(?:adonly|quired|versed)|selected|truespeed');
    function a(string $to): string {
        return \preg_replace('/\b(' . a . ')=(?>""|\'\'|"\1"|\'\1\'|\1)?(?=[\/>\s])/', '$1', $to);
    }
    function e(string $from): string {
        $to = "";
        foreach (\preg_split('/("[^"]*"|\'[^\']*\'|[^\/<=>\s]+)/', $from, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if ("" === ($v = \trim($v))) {
                $to .= ' ';
                continue;
            }
            $to .= $v;
        }
        if (false !== \strpos($to, '=')) {
            $to = a($to);
        }
        if (0 === \strpos($to, '<script ')) {
            $to = a\script($to);
        }
        if (0 === \strpos($to, '<style ')) {
            $to = a\style($to);
        }
        if ('/>' === \substr($to, -2)) {
            return \rtrim(\substr($to, 0, -2)) . '/>';
        }
        if ('>' === \substr($to, -1)) {
            return \rtrim(\substr($to, 0, -1)) . '>';
        }
        return $to;
    }
}

namespace x\minify\h_t_m_l\a {
    function script(string $to): string {
        return $to;
    }
    function style(string $to): string {
        return $to;
    }
}