<?php

// データベース名
define("DB_NAME", "ohenro");

// データベースユーザ名
define("DB_USER", "test");

// データベースパスワード
define("DB_PASS", "test");

// データベースホスト名
define("DB_HOST", "localhost");

// データベースポート番号
define("DB_PORT", "");

// テーブル名
define("TBL_HEAD", "ohe_");

// データベースフラグ PostgreSQL:1, MySQL:2
define("DB_FLAG", "2");


// ドキュメントルート
$http_root = "/hello/shikoku/";
if ($_SERVER["SERVER_PORT"] == "8000") {
	$http_root  = "/";
}
//define("HTTP_ROOT", "http://" . $_SERVER["SERVER_NAME"] . $http_root);
define("HTTP_ROOT", $http_root);
//define("SVR_ROOT", $_SERVER["DOCUMENT_ROOT"] . $http_root);
define("SVR_ROOT", dirname(__FILE__) . "/");
//define("URL_STRING", "http://" . $_SERVER["SERVER_NAME"] . $http_root);
if ($_SERVER["SERVER_PORT"] == "8000") {
	define("URL_STRING", "http://localhost:8000" . $http_root);
}
else {
	define("URL_STRING", "http://127.0.0.1" . $http_root);
}

// 共通ファイルディレクトリパス
define("CMN_ROOT", SVR_ROOT . "common/");
define("CMN_PATH", HTTP_ROOT . "common/");

// クラスパス
define("CLASS_PATH", CMN_ROOT . "class/");

// 画像ディレクトリパス
define("IMG_PATH", HTTP_ROOT . "image/");
define("IMG_ROOT", SVR_ROOT . "image/");

// データ保存用ディレクトリパス
define("DATA_PATH", SVR_ROOT . "data/data/");

// WEBログディレクトリパス
define("LOG_PATH", SVR_ROOT . "data/log/");

// バッチログディレクトリパス
define("BATCH_PATH", SVR_ROOT . "data/log/");

// 管理者メールアドレス
define("WEB_MAIL", "info@advancedsearch.co.jp");
define("WEB_MAIL2", "info@advancedsearch.co.jp");

//本番／開発フラグ
define("DEV_FLAG", "1");

//テスト環境フラグ
define("TEST_FLAG", "1");

//デバッグする時は1にする
$debug_mode = 0;

//管理画面タイトル
define("ADMIN_TITLE", "お遍路さん管理画面");

// デフォルトセット
include_once(CMN_ROOT . "init.php");

?>
