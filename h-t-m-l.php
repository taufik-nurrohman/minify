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
                $to .= $v;
            }
            // `< …`
            if ('<' === $chop[0]) {
                // `<!-- …`
                if (0 === \strpos($chop, '<!--')) {
                    // <https://spec.commonmark.org/0.31.2#html-comment>
                    // `<!-->`
                    if (0 === \strpos($chop, '<!-->')) {
                        $from = \substr($from, 5);
                        continue;
                    }
                    // `<!--->`
                    if (0 === \strpos($chop, '<!--->')) {
                        $from = \substr($from, 6);
                        continue;
                    }
                    if (\preg_match('/^<!--[\s\S]*?-->/', $chop, $m)) {
                        $from = \substr($from, \strlen($m[0]));
                        // <https://learn.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/compatibility/ms537512(v=vs.85)>
                        if ('<![endif]-->' === \substr($m[0], -12)) {
                            $to .= $m[0];
                        }
                    }
                    continue;
                }
                if (0 === \strpos($chop, '<![CDATA[')) {}
                if (0 === \strpos($chop, '<!')) {}
                if (\preg_match('/^<(?>"[^"]*"|\'[\']*\'|[^>])+>/', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
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
            $to .= $from;
        }
        return "" !== $to ? $to : null;
    }
}