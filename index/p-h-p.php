<?php

namespace x\minify {
    function p_h_p(?string $from): ?string {
        if ("" === ($from = \trim($from ?? ""))) {
            return null;
        }
        $prev = $to = "";
        foreach (\token_get_all($from) as $v) {
            if (\is_array($v)) {
                // echo \token_name($v[0]) . '<br/>' . \htmlspecialchars(\json_encode($v[1])) . '<br/><br/>';
                if (\T_CLOSE_TAG === $v[0]) {
                    // <https://www.php.net/manual/en/language.basic-syntax.instruction-separation.php>
                    $to = \trim(\trim($to, ';')) . $v[1];
                    continue;
                }
                if (\T_COMMENT === $v[0]) {
                    if (0 === \strpos($v[1], '/*') && false !== \strpos('!*', $v[1][2])) {
                        // TODO
                    }
                    continue;
                }
                if (\T_CONSTANT_ENCAPSED_STRING === $v[0]) {
                    $to = \trim($to) . $v[1];
                    continue;
                }
                if (\T_ECHO === $v[0] || \T_PRINT === $v[0]) {
                    if ('<?' . 'php ' === \substr($to, -6)) {
                        $to = \substr($to, 0, -4) . '='; // Replace `<?php echo` with `<?=`
                        continue;
                    }
                    $to .= 'echo '; // Replace `print` with `echo`
                    continue;
                }
                if (\T_END_HEREDOC === $v[0]) {
                    $to .= 'S';
                    continue;
                }
                if (\T_OPEN_TAG === $v[0]) {
                    $to .= $prev = \trim($v[1]) . ' ';
                    continue;
                }
                if (\T_START_HEREDOC === $v[0]) {
                    if ("'" === $v[1][3]) {
                        // TODO
                    }
                    $to .= "<<<S\n";
                    continue;
                }
                if (\T_WHITESPACE === $v[0]) {
                    $to .= $prev = false !== \strpos(' "/!#%&()*+,-.:;<=>?@[\]^`{|}~' . "'", \substr($to, -1)) ? "" : ' ';
                    continue;
                }
                $to .= $prev = $v[1];
                continue;
            }
            $to = \trim($to) . $v;
        }
        return "" !== ($to = \trim($to)) ? $to : null;
    }
}