<?php
// router.php
$url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$path = __DIR__ . $url;

// ファイルが存在し、かつそれがHTMLファイルの場合
if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'html') {
    // PHPとしてincludeする
    include $path;
    return true;
}
elseif (substr($url, -1) === '/') {
    include $path . "index.html";
    return true;
}

// それ以外は通常通りファイルを返す
return false;
?>
