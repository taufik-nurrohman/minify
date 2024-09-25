<?php

namespace x\minify {
    function p_h_p(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $count = \count($tokens = \token_get_all($from));
        $to = "";
        foreach ($tokens as $k => $v) {
            if (\is_array($v)) {
                echo \token_name($v[0]) . '<br/>' . \htmlspecialchars(\json_encode($v[1])) . '<br/><br/>';
                if (\T_CLOSE_TAG === $v[0]) {
                    if ($k === $count - 1) {
                        $to = \trim($to, ';') . ';';
                        continue;
                    }
                    // <https://www.php.net/manual/en/language.basic-syntax.instruction-separation.php>
                    $to = \trim(\trim($to, ';')) . $v[1];
                    continue;
                }
                if (\T_COMMENT === $v[0] || \T_DOC_COMMENT === $v[0]) {
                    if (0 === \strpos($v[1], '/*') && false !== \strpos('!*', $v[1][2])) {
                        if (false !== \strpos($v[1], "\n")) {
                            $to .= '/*' . \substr($v[1], 3);
                        } else {
                            $to .= '/*' . \trim(\substr($v[1], 3, -2)) . '*/';
                        }
                    }
                    continue;
                }
                if (\T_CONSTANT_ENCAPSED_STRING === $v[0]) {
                    $to = \trim($to) . $v[1];
                    continue;
                }
                if (\T_DNUMBER === $v[0] || \T_LNUMBER === $v[0]) {
                    // <https://wiki.php.net/rfc/numeric_literal_separator>
                    $test = \strtr($v[1], ['_' => ""]);
                    if (\T_DNUMBER === $v[0]) {
                        $test = \rtrim(\trim($test, '0'), '.');
                    } else {
                        $test = \ltrim($test, '0');
                    }
                    $to .= "" === $test ? '0' : $test;
                    continue;
                }
                if (\T_ECHO === $v[0] || \T_PRINT === $v[0]) {
                    if ('<?' . 'php ' === \substr($to, -6)) {
                        // Replace `<?php echo` with `<?=`
                        $to = \substr($to, 0, -4) . '=';
                        continue;
                    }
                    // Replace `print` with `echo`
                    $to .= 'echo ';
                    continue;
                }
                if (\T_END_HEREDOC === $v[0]) {
                    $to .= 'S';
                    continue;
                }
                if (\T_OPEN_TAG === $v[0]) {
                    $to .= \trim($v[1]) . ' ';
                    continue;
                }
                if (\T_START_HEREDOC === $v[0]) {
                    if ("'" === $v[1][3]) {
                        $to .= "<<<'S'\n";
                        continue;
                    }
                    $to .= "<<<S\n";
                    continue;
                }
                if (\T_STRING === $v[0]) {
                    $test = \strtolower($v[1]);
                    if (false !== \strpos(',false,null,true,', ',' . $test . ',')) {
                        $to .= $test;
                        continue;
                    }
                    $to .= $v[1];
                    continue;
                }
                if (\T_WHITESPACE === $v[0]) {
                    $to .= false !== \strpos(' "/!#%&()*+,-.:;<=>?@[\]^`{|}~' . "'", \substr($to, -1)) ? "" : ' ';
                    continue;
                }
                $to .= $v[1];
                continue;
            }
            echo \htmlspecialchars(\json_encode($v)) . '<br/><br/>';
            if (false !== \strpos(')]', $v)) {
                $to = \trim(\trim($to, ',')) . $v;
                continue;
            }
            $to = \trim($to) . $v;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}