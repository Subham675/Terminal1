<?php
$html = file_get_contents('app/views/home.php');
preg_match('/<style>(.*?)<\/style>/s', $html, $m);
$lines = explode("\n", $m[1]);
$open = 0;
$stack = [];
foreach ($lines as $idx => $line) {
    $lineNum = $idx + 58;
    $clean = preg_replace('/\/\*.*?\*\//', '', $line);
    $chars = str_split($clean);
    foreach ($chars as $c) {
        if ($c === '{') {
            $stack[] = [$lineNum, trim($line)];
            $open++;
        } elseif ($c === '}') {
            array_pop($stack);
            $open--;
        }
    }
}
echo "Total brace balance: $open\n";
if (!empty($stack)) {
    echo "Remaining unclosed blocks:\n";
    foreach ($stack as $s) {
        echo "Line {$s[0]}: {$s[1]}\n";
    }
} else {
    echo "ALL CSS BRACES PERFECTLY BALANCED!\n";
}
