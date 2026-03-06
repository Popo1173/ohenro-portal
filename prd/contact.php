<?php
/*-----------------------------------------------------------------------------
// お遍路サイトお問い合わせ処理
-----------------------------------------------------------------------------*/

require_once(dirname(__FILE__). '/conf.php');

require_once(CLASS_PATH . 'class_user.php');

//クラスオブジェクト生成
$obj_data = new class_user();

//ルートへのパス
$document_path = "./";

//タイトル
$sub_title = "お問い合わせ";
$sub_title_name = $sub_title;

//メニューID
$menu_id = "contact";

//セッション名
$update_session_name = $menu_id . "_up";

//モードの取得
$mode = get_file_mode($_SERVER["SCRIPT_NAME"]);
$md = $request_data["md"];

$lang_code = get_lang_code();

if ($post_send_flag && isset($request_data["md"]) && $request_data["md"] == "up") {
	//お問い合わせ送信

	//データベースオープン
	$dbh = db_open();

	//エラーチェック
	$request_data["lang_code"] = $lang_code;
	if (!$obj_data->check_error($request_data, $dbh)) {
		//エラー処理

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
		$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact/index" . EXT . "?md=chk");
		exit;
	}

	//データベースクローズ
	db_close($dbh);

	if (!$request_data["comp_flag"]) {
		//セッション保持
		$_SESSION[$update_session_name] = $request_data;

		//確認画面へ
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact/confirm/index" . EXT);
		exit;
	}

	//メール作成クラス
	require_once(CLASS_PATH . "class_mail.php");

	// メールクラス生成
	$obj_mail = new class_mail();

	// 件名
	//$obj_mail->set_subject("お問い合わせありがとうございます");
	$message = array();
	$message["lang_code"] = $lang_code;
	$message["message_num"] = "S01007";
	$subject = get_lang_message($message);
	$obj_mail->set_subject($subject);

	// メール本文
	if (!$obj_mail->set_mail("contact.txt", CMN_ROOT . "mail/" . $lang_ary[$message["lang_code"]] . "/")) {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact/index" . EXT . "?md=chk");
		exit;
	}

	// ユーザメール送信
	if ($request_data["email"] && !$obj_mail->send_mail($request_data["email"], WEB_MAIL)) {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact/index" . EXT . "?md=chk");
		exit;
	}

	$_SESSION["error_message"] = null;
	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact/complete/index" . EXT);
	exit;
}
elseif (strpos($path, "contact/confirm") !== false) {
	//確認画面
	if (!session_is_registered($update_session_name)) {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact/index" . EXT);
		exit;
	}
	else {
		$data = $_SESSION[$update_session_name];
	}

	$hidden = '    <input type="hidden" name="comp_flag" value="1">
';
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			for ($i = 0; $i < count_ary($value); $i++) {
				if (is_array($value[$i])) {
					foreach ($value[$i] as $key2 => $value2) {
						$hidden .= '    <input type="hidden" name="' . $key2 . '[]" value="' . escape_html($value2) . '">
';
					}
				}
				else {
					$hidden .= '    <input type="hidden" name="' . $key . '[]" value="' . escape_html($value[$i]) . '">
';
				}
			}
		}
		else {
			$hidden .= '    <input type="hidden" name="' . $key . '" value="' . escape_html($value) . '">
';
		}
	}

	//会員情報
	$data["conf_flag"] = true;

}
elseif (strpos($path, "contact/form") !== false) {
	//登録、変更

	//データベースオープン
	$dbh = db_open();

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		$class_ary = $data["class_ary"];
	}
	elseif ($_SESSION["login_info_user"]["user_id"]) {
		$search = array();
		$search["user_id"] = $_SESSION["login_info_user"]["user_id"];
		if (!$search["user_id"]) {
			$search = $request_data;
		}
		$obj_data->get_data($search, $dbh);
		$data = $obj_data->data;
	}

	//データベースクローズ
	db_close($dbh);

	$hidden .= '    <input type="hidden" name="md" value="up">
	<input type="hidden" name="user_id" value="' . $data["user_id"] . '">
';

	$javascript = "";

}

?>
