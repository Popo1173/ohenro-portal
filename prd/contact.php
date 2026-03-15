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
$path = $_SERVER["SCRIPT_NAME"];
$md = $request_data["md"];

$lang_code = get_lang_code();

if ($post_send_flag && isset($request_data["md"]) && $request_data["md"] == "up") {
	//お問い合わせ送信
	$data = $request_data;

	//データベースオープン
	$dbh = db_open();

	//エラーチェック
	$error_flag = false;
	$data["lang_code"] = $lang_code;
	if (!$obj_data->check_mail($data, $dbh)) {
		$error_flag = true;
	}
	if (!$obj_data->check_name($data, $dbh)) {
		$error_flag = true;
	}
	if (!$obj_data->check_agree($data, $dbh)) {
		$error_flag = true;
	}
	if (!isset($data["detail"]) || !$data["detail"]) {
		//$obj_data->error_message .= "「お問い合わせ内容」を入力してください。";
		$message = array();
		$message["lang_code"] = $lang_code;
		$message["message_num"] = "E05001";
		$obj_data->class_ary["detail"] = $obj_data->error_code;
		$obj_data->class_ary["detail_error"] .= get_lang_message($message);
	}

	if ($error_flag) {
		//エラー処理

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
		$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/index" . EXT . "?md=chk");
		exit;
	}

	//データベースクローズ
	db_close($dbh);

	if (!$request_data["comp_flag"]) {
		//セッション保持
		$_SESSION[$update_session_name] = $request_data;

		//確認画面へ
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/confirm/index" . EXT);
		exit;
	}

	//メール作成クラス
	require_once(CLASS_PATH . "class_mail.php");

	// メールクラス生成
	$obj_mail = new class_mail();

	// 件名
	//$obj_mail->set_subject("お問い合わせありがとうございます");
	$message = array();
	$message["lang_code"] = get_en_code($lang_code);
	$message["message_num"] = "S01007";
	$subject = get_lang_message($message);
	$obj_mail->set_subject($subject);

	// メール本文
	if (!$obj_mail->set_mail("contact.txt", MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/")) {
		print_access_log(MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/contact.txt ファイルオープンに失敗しました。\n");
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/index" . EXT . "?md=chk");
		exit;
	}

	// ユーザメール送信
	if ($request_data["email"] && !$obj_mail->send_mail($request_data["email"], WEB_MAIL)) {
		print_access_log("お問い合わせユーザー宛 : メール送信に失敗しました。\n");
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/index" . EXT . "?md=chk");
		exit;
	}

	//管理者宛メール送信
	$obj_admin = new class_mail();
	$obj_admin->set_subject("お問い合わせがありました");

	// メール本文
	if (!$obj_admin->set_mail("contact_admin.txt", MAIL_ROOT)) {
		print_access_log(MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/contact_admin.txt ファイルオープンに失敗しました。\n");
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/index" . EXT . "?md=chk");
		exit;
	}

	// 管理者メール送信
	if (!$obj_admin->send_mail(WEB_MAIL2, $request_data["email"])) {
		print_access_log("お問い合わせ管理者宛 : メール送信に失敗しました。\n");
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/index" . EXT . "?md=chk");
		exit;
	}

	$_SESSION["error_message"] = null;
	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/complete/index" . EXT);
	exit;
}
elseif (strpos($path, "contact-us/confirm") !== false) {
	//確認画面
	if (!session_is_registered($update_session_name)) {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/contact-us/index" . EXT);
		exit;
	}
	else {
		$data = $_SESSION[$update_session_name];
	}

	$hidden = '    <input type="hidden" name="comp_flag" value="1">' . "\n";
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			for ($i = 0; $i < count_ary($value); $i++) {
				if (is_array($value[$i])) {
					foreach ($value[$i] as $key2 => $value2) {
						$hidden .= '    <input type="hidden" name="' . $key2 . '[]" value="' . escape_html($value2) . '">' . "\n";
					}
				}
				else {
					$hidden .= '    <input type="hidden" name="' . $key . '[]" value="' . escape_html($value[$i]) . '">' . "\n";
				}
			}
		}
		else {
			$hidden .= '    <input type="hidden" name="' . $key . '" value="' . escape_html($value) . '">' . "\n";
		}
	}

	//会員情報
	$data["conf_flag"] = true;

}
elseif (strpos($path, "contact-us/") !== false) {
	//登録、変更

	//データベースオープン
	$dbh = db_open();

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		$class_ary = $data["class_ary"];
	}
	elseif (is_login()) {
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
	<input type="hidden" name="lang_code" value="' . $lang_code . '">
	<input type="hidden" name="user_id" value="' . $data["user_id"] . '">' . "\n";

	$javascript = "";

}

?>
