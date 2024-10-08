<?php

namespace x\minify {
    function h_t_m_l(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $from = \strtr($from, ["\r" => ""]);
        $to = "";
        while (false !== ($chop = \strpbrk($from, '<&'))) {
            if ("" !== ($v = \strstr($from, $chop[0], true))) {
                $from = $chop;
                $to .= h_t_m_l\n($v);
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
                    if ("" !== $from && false !== \strpos(" \n\t", $from[0])) {
                        $from = ' ' . \ltrim($from);
                    } else {
                        $from = \ltrim($from);
                    }
                    if (false !== \strpos(" \n\t", \substr($to, -1))) {
                        $to = \rtrim($to) . ' ';
                    } else {
                        $to = \rtrim($to);
                    }
                    // <https://en.wikipedia.org/wiki/Conditional_comment>
                    if ('<![endif]-->' === \substr($chop, -12)) {
                        $to .= \substr($chop, 0, $n = \strpos($chop, '>') + 1) . h_t_m_l(\substr($chop, $n, -12)) . \substr($chop, -12);
                    }
                    if ("" !== $from && ' ' === $from[0]) {
                        $to = \rtrim($to);
                    }
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
                        $to = \rtrim($to) . h_t_m_l\e($m[0]);
                        continue;
                    }
                    $n = \substr(\strtok($m[0], " \n\t>"), 1);
                    // `<pre>…</pre>` or `<script>…</script>` or `<style>…</style>` or `<textarea>…</textarea>`
                    if (false !== \strpos(',pre,script,style,textarea,', ',' . $n . ',')) {
                        $from = \ltrim(\substr($from, ($e = \strpos($chop, '</' . $n . '>')) + 1));
                        $value = \substr($chop, $b, $e - $b);
                        if ('script' === $n && \function_exists($f = __NAMESPACE__ . "\\j_s")) {
                            $value = \trim($value);
                            if (0 === \strpos($value, '<![CDATA[') && ']]>' === \substr($value, -3)) {
                                $value = \trim(\substr($value, 9, -3));
                            }
                            $value = \call_user_func($f, $value);
                        } else if ('style' === $n && \function_exists($f = __NAMESPACE__ . "\\c_s_s")) {
                            $value = \trim($value);
                            if (0 === \strpos($value, '<![CDATA[') && ']]>' === \substr($value, -3)) {
                                $value = \trim(\substr($value, 9, -3));
                            }
                            $value = \call_user_func($f, $value);
                        }
                        $to .= h_t_m_l\e($m[0]) . $value . '</' . $n . '>';
                        continue;
                    }
                    // `</asdf>`
                    if ('/' === $m[0][1]) {
                        if (\strlen($from) > 1 && false !== \strpos(" \n\t", $from[1])) {
                            $from = \ltrim($from);
                        }
                        if (0 === \strpos($from, "\n<") && '/' !== $from[3] && false === \strpos($from, '<![endif]-->')) {
                            $from = \substr($from, 1);
                        }
                        $to = \rtrim($to);
                    // `<asdf/>`
                    } else if ('/' === \substr($m[0], -2, 1)) {
                        // `<br/>` or `<hr/>` or `<wbr/>`
                        if (false !== \strpos(',br,hr,wbr,', ',' . \trim($n, '/') . ',')) {
                            $from = \ltrim($from);
                            $to = \rtrim($to);
                        // `<asdf/>`
                        } else {
                            if (\strlen($from) > 1 && false !== \strpos(" \n\t", \substr($from, 1, 1))) {
                                $from = \ltrim($from);
                            }
                            if (\strlen($to) > 1 && false !== \strpos(" \n\t", \substr($to, -2, 1))) {
                                $to = \rtrim($to);
                            }
                        }
                    // `<asdf>`
                    } else {
                        // `<br>` or `<hr>` or `<wbr>`
                        if (false !== \strpos(',br,hr,wbr,', ',' . $n . ',')) {
                            $from = \ltrim($from);
                            $to = \rtrim($to);
                        // `<img>` or `<input>`
                        } else if (false !== \strpos(',img,input,', ',' . $n . ',')) {
                            if (\strlen($from) > 1 && false !== \strpos(" \n\t", \substr($from, 1, 1))) {
                                $from = \ltrim($from);
                            }
                            if (\strlen($to) > 1 && false !== \strpos(" \n\t", \substr($to, -2, 1))) {
                                $to = \rtrim($to);
                            }
                        // `<asdf>`
                        } else {
                            if (0 === \strpos($from, ' </' . $n . '>')) {
                                $from = \substr($from, 3 + \strlen($n) + 1);
                                $to .= h_t_m_l\e($m[0]) . ' </' . $n . '>';
                                continue;
                            }
                            $from = \ltrim($from);
                            if (\strlen($to) > 1 && false !== \strpos(" \n\t", \substr($to, -2, 1))) {
                                $to = \rtrim($to);
                            }
                        }
                    }
                    $to .= h_t_m_l\e($m[0]);
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
            $to .= h_t_m_l\n($chop);
        }
        if ("" !== $from) {
            $to .= h_t_m_l\n($from);
        }
        return "" !== ($to = \trim($to)) ? $to : null;
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
    function a(string $to): string {
        static $of = [];
        if (!$of) {
            foreach ([
                'allowfullscreen',
                'allowpaymentrequest',
                'async',
                'autofocus',
                'autoplay',
                'checked',
                'controls',
                'default',
                'defer',
                'disabled',
                'formnovalidate',
                'hidden',
                'ismap',
                'itemscope',
                'loop',
                'multiple',
                'muted',
                'nomodule',
                'novalidate',
                'open',
                'playsinline',
                'readonly',
                'required',
                'reversed',
                'selected',
                'truespeed'
            ] as $v) {
                $of[' ' . $v . "='" . $v . "'"] = ' ' . $v;
                $of[' ' . $v . "=''"] = ' ' . $v;
                $of[' ' . $v . '= '] = ' ' . $v . ' ';
                $of[' ' . $v . '=""'] = ' ' . $v;
                $of[' ' . $v . '="' . $v . '"'] = ' ' . $v;
                $of[' ' . $v . '=' . $v . ' '] = ' ' . $v . ' ';
                $of[' ' . $v . '=' . $v . '/'] = ' ' . $v . '/';
                $of[' ' . $v . '=' . $v . '>'] = ' ' . $v . '>';
                $of[' ' . $v . '=/'] = ' ' . $v . '/';
                $of[' ' . $v . '=>'] = ' ' . $v . '>';
            }
        }
        return \strtr($to, $of);
    }
    function e(string $from): string {
        $to = "";
        foreach (\preg_split('/("[^"]*"|\'[^\']*\'|[^!\/<=>?\s]+)/', $from, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if ("" === ($v = \trim($v))) {
                $to .= ' ';
                continue;
            }
            if ('"' === $v[0] && '"' === \substr($v, -1) || "'" === $v[0] && "'" === \substr($v, -1)) {
                $test = \substr($v, 1, -1);
                if (' class=' === \substr($to, -7)) {
                    $v = $v[0] . \trim(\preg_replace('/\s+/', ' ', $test)) . $v[0];
                } else if (' style=' === \substr($to, -7) && \function_exists($f = \substr(__NAMESPACE__, 0, -7) . 'c_s_s')) {
                    $v = $v[0] . \substr(\call_user_func($f, 'x{' . $test . '}'), 2, -1) . $v[0];
                } else if (false !== \strpos($to, ' on') && \preg_match('/ on[a-z\d]+=$/', $to) && \function_exists($f = \substr(__NAMESPACE__, 0, -7) . 'j_s')) {
                    $v = $v[0] . \call_user_func($f, $test) . $v[0];
                }
                if (false !== ($n = \strpos($v, '&')) && \strpos($v, ';') > $n + 1) {
                    $v = \preg_replace_callback('/&(?>#x[a-f\d]{1,6}|#\d{1,7}|[a-z][a-z\d]{1,31});/i', static function ($m) use ($v) {
                        $s = \html_entity_decode($m[0], \ENT_HTML5 | \ENT_QUOTES, 'UTF-8');
                        if ($s === $v[0]) {
                            return $m[0];
                        }
                        return $s;
                    }, $v);
                }
            }
            $to .= $v;
        }
        if (false !== \strpos($to, '=')) {
            $to = a($to);
            if (0 === \strpos($to, '<link ')) {
                $to = a\link($to);
            } else if (0 === \strpos($to, '<script ')) {
                $to = a\script($to);
            } else if (0 === \strpos($to, '<style ')) {
                $to = a\style($to);
            }
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
        if (\strlen($from) > 1 && false !== \strpos(" \n\t", $from[0]) && false !== \strpos(" \n\t", $from[1])) {
            return '  ' . \preg_replace('/\s+/', ' ', \ltrim($from));
        }
        if (\strlen($from) > 1 && false !== \strpos(" \n\t", \substr($from, -1)) && false !== \strpos(" \n\t", \substr($from, -2, 1))) {
            return \preg_replace('/\s+/', ' ', \rtrim($from)) . '  ';
        }
        return \preg_replace('/\s+/', ' ', $from);
    }
}

namespace x\minify\h_t_m_l\a {
    function link(string $to): string {
        static $of = [];
        if (!$of) {
            foreach ([
                'type' => 'text/css'
            ] as $k => $v) {
                $of[' ' . $k . "='" . $v . "'"] = "";
                $of[' ' . $k . '="' . $v . '"'] = "";
                $of[' ' . $k . '=' . $v . ' '] = ' ';
                $of[' ' . $k . '=' . $v . '>'] = '>';
            }
        }
        return \strtr($to, $of);
    }
    function script(string $to): string {
        static $of = [];
        if (!$of) {
            // <https://crockford.com/javascript/script.html>
            // <https://crockford.com/javascript/style1.html>
            foreach ([
                'language' => 'javascript',
                'type' => [
                    'application/ecmascript',
                    'application/javascript',
                    'application/x-javascript',
                    'text/javascript'
                ]
            ] as $k => $v) {
                if (\is_array($v)) {
                    foreach ($v as $vv) {
                        $of[' ' . $k . "='" . $vv . "'"] = "";
                        $of[' ' . $k . '="' . $vv . '"'] = "";
                        $of[' ' . $k . '=' . $vv . ' '] = ' ';
                        $of[' ' . $k . '=' . $vv . '>'] = '>';
                    }
                    continue;
                }
                $of[' ' . $k . "='" . $v . "'"] = "";
                $of[' ' . $k . '="' . $v . '"'] = "";
                $of[' ' . $k . '=' . $v . ' '] = ' ';
                $of[' ' . $k . '=' . $v . '>'] = '>';
            }
        }
        if (false !== \strpos($to, ' event=') || false !== \strpos($to, ' for=')) {
            $to = \preg_replace('/ (event|for)=(?>"[^"]*"|\'[^\']*\'|[^\/>\s]+)/', "", $to);
        }
        return \strtr($to, $of);
    }
    function style(string $to): string {
        static $of = [];
        if (!$of) {
            foreach ([
                'type' => 'text/css'
            ] as $k => $v) {
                $of[' ' . $k . "='" . $v . "'"] = "";
                $of[' ' . $k . '="' . $v . '"'] = "";
                $of[' ' . $k . '=' . $v . ' '] = ' ';
                $of[' ' . $k . '=' . $v . '>'] = '>';
            }
        }
        return \strtr($to, $of);
    }
}