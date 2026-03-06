<?php
/*-----------------------------------------------------------------------------
件名：ログイン
画面：ログイン画面
機能：ログイン処理
-----------------------------------------------------------------------------*/

//設定ファイル呼び出し
require_once(dirname(__FILE__). '/../conf.php');
require_once(CLASS_PATH . 'class_admin.php');

$error_message = "";
$_SESSION["login"]["error_message"] = "";

//ログインクラス取得
$obj_data = new class_admin();

if (!$obj_data->check_login($request_data, $dbh)) {
	$error_message .= "ログインに失敗しました。<br>\nIDとパスワードをお確かめの上、再度ログイン処理を実行してください。<br>\n";
}

if ($error_message) {
	//エラー処理
	$_SESSION["login"]["error_message"] = $error_message;
	header("Location: ./index" . EXT . "?" . $_POST["query"]);
	exit;
}

//クッキー設定
if ($_POST["use_cookie"]) {
	setcookie("login_id", $_POST["login_id"], time() + 60*60*24*7);
	setcookie("password", $_POST["password"], time() + 60*60*24*7);
	setcookie("use_cookie", $_POST["use_cookie"], time() + 60*60*24*7);
}
else {
	setcookie("login_id", $_POST["login_id"], time() - 3600);
	setcookie("password", $_POST["password"], time() - 3600);
	setcookie("use_cookie", $_POST["use_cookie"], time() - 3600);
}

setcookie("admin_id", $_SESSION["login_info"]["admin_id"], time() + 60*60*6);
setcookie("session_id", session_id(), time() + 60*60*6);

if (isset($_POST["url"]) && $_POST["url"]) {
	//指定URLを表示
	header("Location: " . $_POST["url"]);
}
else {
	//メニュー画面を表示
	header("Location: top" . EXT);
}

?>
