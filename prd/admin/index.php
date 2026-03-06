<?php
//------------------------------------------------------------------------------
// 管理ログイン画面
// 管理ログイン画面を表示する
//------------------------------------------------------------------------------

//設定ファイル呼び出し
require_once(dirname(__FILE__). '/../conf.php');

//タイトル
$sub_title_name = "ログイン画面";

$next_page = "";
if (isset($_GET["url"]) && $_GET["url"]) {
	foreach ($_GET as $key => $value) {
		if ($key == "url") {
			continue;
		}

		if ($next_page) {
			$next_page .= "&";
		}
		$next_page .= $key . "=" . $value;
	}
	$next_page = $_GET["url"] . "?" . $next_page;
}

//ログインチェック
if ($_SESSION["login"]["login_admin_flag"]) {
	//ログイン済みの場合は、トップページへ
	if ($next_page) {
		//指定画面へ
		header("Location: " . $next_page);
		exit;
	}

	//メニュー画面を表示
	header("Location: top" . EXT);
	exit;
}

if (isset($_GET["timeout"]) && $_GET["timeout"]) {
	$error_message = "ログインの有効期限が切れました。<br>\n再度ログインしなおしてください。";
}
else {
	$error_message = $_SESSION["login"]["error_message"];
}

?>
