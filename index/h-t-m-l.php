<?php

namespace x\minify {
    function h_t_m_l(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $c1 = '<&';
        $c2 = " \n\r\t";
        $r1 = '"[^"]+"';
        $r2 = "'[^']+'";
        $r3 = $r1 . '|' . $r2;
        $r4 = '<(?>' . $r3 . '|[^>])++>';
        $to = "";
        while (false !== ($chop = \strpbrk($from, $c1 . $c2))) {
            if ("" !== ($v = \strstr($from, $c = $chop[0], true))) {
                $from = $chop;
                $to .= $v;
            }
            // <https://www.w3.org/TR/xml#dt-stag>
            // `<…`
            if ('<' === $c && isset($chop[1]) && false === \strpos($c2, $chop[1])) {
                // <https://www.w3.org/TR/xml#d0e1149>
                // `<!--…`
                if (0 === \strpos($chop, '<!--') && false !== ($n = \strpos($chop, '-->'))) {
                    $from = \substr($from, \strlen(\substr($chop, 0, $n + 3)));
                    continue;
                }
                // <https://www.w3.org/TR/xml#d0e1271>
                // `<![CDATA[…`
                if (0 === \strpos($chop, '<![CDATA[') && false !== ($n = \strpos($chop, ']]>'))) {
                    $from = \substr($from, \strlen($v = \substr($chop, 0, $n + 3)));
                    $to .= $v;
                    continue;
                }
                if (\preg_match('/^' . $r4 . '/', $chop, $m)) {
                    $from = \substr($from, \strlen($m[0]));
                    foreach (\preg_split('/(' . $r3 . '|\s+)/', $m[0], -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
                        if (false !== \strpos('"\'', $v[0]) && false !== \strpos($v, '&')) {
                            $v = \preg_replace_callback('/&(?>#x[a-f\d]{1,6}|#\d{1,7}|[a-z][a-z\d]{1,31});/i', static function ($m) use ($v) {
                                $test = \html_entity_decode($m[0], \ENT_HTML5 | \ENT_QUOTES, 'UTF-8');
                                if (false !== \strpos('&<>' . $v[0], $test)) {
                                    return $m[0];
                                }
                                return $test;
                            }, $v);
                        }
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
            }
            // <https://www.w3.org/TR/xml#dt-charref>
            // <https://www.w3.org/TR/xml#dt-entref>
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
            if ($n = \strspn($chop, $c2)) {
                $r = \substr($from, 0, $n);
                $from = \substr($from, $n);
                // `</asdf>  asdf`
                if ('>' === \substr($to, -1) && '/' === \substr(\strrchr($to, '<'), 1, 1)) {
                    if (' ' === $r && '<' !== ($from[0] ?? 0)) { // TODO
                        $to .= $r;
                    }
                    continue;
                }
                // `<asdf> `
                if ('<' === ($to[0] ?? 0) && '/' !== ($to[1] ?? 0) && '>' === \substr($to, -1)) {
                    if (' ' === $r && (
                        // `<asdf/> `
                        '/>' === \substr($to, -2) ||
                        // `<asdf> </asdf>`
                        '</' === \substr($from, 0, 2) && \substr(\strtok(\strrchr($to, '<'), $c2 . '>'), 1) === \substr(\strtok($from, $c2 . '>'), 2)
                    )) {
                        $to .= $r;
                    }
                    continue;
                }
                // ` </asdf>`
                if ('</' === \substr($from, 0, 2) && \strpos($from, '>') > 2) {
                    if (' ' === $r && '>' === \substr($to, -1) && \substr(\strtok(\strrchr($to, '<'), $c2 . '>'), 1) === \substr(\strtok($from, $c2 . '>'), 2)) {
                        $to .= $r;
                    }
                    continue;
                }
                // `<asdf>…`
                if ('>' === \substr($to, -1) && '/' !== \substr(\strrchr($to, '<'), 1, 1)) {
                    continue; // Always remove space(s) after open tag
                }
                // `…<asdf>`
                if ('<' === ($from[0] ?? 0) && '/' !== ($from[1] ?? 0) && \strpos($from, '>') > 2) {
                    if (' ' !== $r) {
                        continue; // Remove space(s) before open tag if it is not a space
                    }
                }
                // `…`
                $to .= ' ';
                continue;
            }
            $from = \substr($from, 1);
            $to .= $c;
        }
        if ("" !== $from) {
            $to .= $from;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}