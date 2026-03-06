<?php
/*-----------------------------------------------------------------------------
件名：ログアウト
画面：全画面
機能：ログアウト処理をしてログイン画面に戻る
-----------------------------------------------------------------------------*/

//設定ファイル呼び出し
require_once(dirname(__FILE__). '/./conf.php');

// セッションクリア
$_SESSION["login"] = array();
$_SESSION["login_info_user"] = null;
$_SESSION["error_message"] = null;
//session_destroy();

//クッキークリア
//setcookie("session_id", "", time() - 1000);

// ログイン画面へ
header("Location: " . HTTP_ROOT . $lang_ary[get_lang_code()] . "/login/index" . EXT);

?>
