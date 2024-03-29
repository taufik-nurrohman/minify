<?php

error_reporting(E_ALL | E_STRICT);

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('html_errors', 1);

define('D', DIRECTORY_SEPARATOR);
define('P', "\u{001A}");
define('PATH', __DIR__);

require __DIR__ . D . 'index.php';

$of = basename($_GET['of'] ?? 'h-t-m-l');

$files = glob(__DIR__ . D . 'test' . D . $of . D . '*.max', GLOB_NOSORT);
usort($files, static function ($a, $b) {
    $a = dirname($a) . D . basename($a, '.max');
    $b = dirname($b) . D . basename($b, '.max');
    return strnatcmp($a, $b);
});

$out = '<!DOCTYPE html>';
$out .= '<html dir="ltr">';
$out .= '<head>';
$out .= '<meta charset="utf-8">';
$out .= '<title>';
$out .= 'Minify ' . strtoupper(strtr($of, ['-' => ""]));
$out .= '</title>';
$out .= '<style>';
$out .= <<<CSS

.char-space,
.char-tab {
  opacity: 0.5;
  position: relative;
}

.char-space::before {
  bottom: 0;
  content: '·';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}

.char-tab::before {
  bottom: 0;
  content: '→';
  left: 0;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
}

CSS;
$out .= '</style>';
$out .= '</head>';
$out .= '<body>';

$out .= '<form method="get">';
$out .= '<fieldset>';
$out .= '<legend>';
$out .= 'Tests';
$out .= '</legend>';
foreach (glob(__DIR__ . D . 'test' . D . '*', GLOB_ONLYDIR) as $v) {
    $out .= ' ';
    $out .= '<button' . ($of === ($n = basename($v)) ? ' disabled' : "") . ' name="of" type="submit" value="' . htmlspecialchars($n) . '">';
    $out .= htmlspecialchars(strtoupper(strtr($n, ['-' => ""])));
    $out .= '</button>';
}
$out .= '</fieldset>';
$out .= '</form>';

$error_count = 0;
foreach ($files as $v) {
    $raw = file_get_contents($v);
    $out .= '<h1 id="' . ($n = basename($v, '.max')) . '"><a aria-hidden="true" href="#' . $n . '">&sect;</a> ' . strtr($v, [PATH . D => '.' . D]) . '</h1>';
    $out .= '<div style="display:flex;gap:1em;margin:1em 0 0;">';
    $out .= '<pre style="background:#ccc;border:1px solid rgba(0,0,0,.25);color:#000;flex:1;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $out .= strtr(htmlspecialchars($raw), [
        "\t" => '<span class="char-tab">' . "\t" . '</span>',
        ' ' => '<span class="char-space"> </span>'
    ]);
    $out .= '</pre>';
    $out .= '<div style="flex:1;min-width:0;">';
    $a = $b = "";
    $a .= '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;margin:0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
    $start = microtime(true);
    $content = call_user_func("x\\minify\\" . \strtr($of, '-', '_'), $raw) ?? "";
    $end = microtime(true);
    $a .= strtr(htmlspecialchars($content), [
        "\t" => '<span class="char-tab">' . "\t" . '</span>',
        ' ' => '<span class="char-space"> </span>'
    ]);
    $a .= '</pre>';
    if (is_file($f = dirname($v) . D . pathinfo($v, PATHINFO_FILENAME) . '.min')) {
        $test = file_get_contents($f);
        if ($error = $content !== $test) {
            $b .= '<pre style="background:#cff;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;margin:1em 0 0;padding:.5em;tab-size:4;white-space:pre-wrap;word-wrap:break-word;">';
            $b .= strtr(htmlspecialchars($test), [
                "\t" => '<span class="char-tab">' . "\t" . '</span>',
                ' ' => '<span class="char-space"> </span>'
            ]);
            $b .= '</pre>';
        }
    } else {
        $error = false; // No test file to compare
    }
    $out .= ($error ? strtr($a, [':#cfc;' => ':#fcc;']) : $a) . $b . '</div>';
    $out .= '</div>';
    $time = round(($end - $start) * 1000, 2);
    if ($error) {
        $error_count += 1;
    }
    $slow = $time >= 1;
    $out .= '<p style="color:#' . ($error || $slow ? '800' : '080') . ';">Parsed in ' . $time . ' ms.</p>';
}

$out .= '</body>';
$out .= '</html>';

if ($error_count) {
    $out = strtr($out, ['</title>' => ' (' . $error_count . ')</title>']);
}

echo $out;