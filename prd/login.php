<?php
/*-----------------------------------------------------------------------------
件名：ログイン
画面：ログイン画面
機能：ログイン処理
-----------------------------------------------------------------------------*/

//設定ファイル呼び出し
require_once(dirname(__FILE__). '/./conf.php');
require_once(CLASS_PATH . 'class_user.php');

$error_message = "";
$lang_code = get_lang_code($request_data);
$lang_dir = $lang_ary[$lang_code];

//ログインクラス取得
$obj_data = new class_user();

$update_session_name = "user_login";
$error_flag = false;
$request_data["lang_code"] = $lang_code;

//データベースオープン
$dbh = db_open();

if (!$obj_data->check_mail($request_data, $dbh)) {
	$error_flag = true;
}
elseif (!$obj_data->check_login($request_data, $dbh)) {
	$error_flag = true;
	//$error_message .= "ログインに失敗しました。<br>\nメールアドレスとパスワードをお確かめの上、再度ログイン処理を実行してください。<br>\n";
	$message = array();
	$message["lang_code"] = $lang_code;
	$message["message_num"] = "E01001";
	$obj_data->class_ary["password"] .= $obj_data->error_code;
	$obj_data->class_ary["password_error"] .= get_lang_message($message);
}

//データベースクローズ
db_close($dbh);


if ($error_flag) {
	//エラー処理
	$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
	$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

	header("Location: " . HTTP_ROOT . $lang_dir . "/login/index" . EXT . "?md=chk");
	exit;
}


if (isset($request_data["url"]) && $request_data["url"]) {
	//指定URLを表示
	header("Location: " . $request_data["url"]);
//	header("Location: " . HTTP_ROOT . $lang_dir . "/my-page/index" . EXT);
}
else {
	//トップ画面を表示
	header("Location: " . HTTP_ROOT . $lang_dir . "/my-page/index" . EXT);
}

?>
