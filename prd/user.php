<?php
//------------------------------------------------------------------------------
// お遍路サイト会員処理
//------------------------------------------------------------------------------

//設定ファイル呼び出し
include_once(dirname(__FILE__) . "/conf.php");

require_once(CLASS_PATH . 'class_user.php');

//クラスオブジェクト生成
$obj_data = new class_user();

$error_flag = false;
$error_message = "";

//ルートへのパス
$document_path = "./";

//メニューID
$menu_id = "user";

//セッション名
$update_session_name = $menu_id . "_up";

//モードの取得
$path = $_SERVER["SCRIPT_NAME"];
$mode = get_file_name($_SERVER["SCRIPT_NAME"], false);
$md = $request_data["md"];
$mode_flag = $request_data["mode_flag"];

$lang_code = get_lang_code($request_data);
$request_data["lang_code"] = $lang_code;

//データベースオープン
$dbh = db_open();

//トランザクション開始
start_trans($dbh);

//有効期限切れユーザー削除
$obj_data->delete_tmp_user($dbh);

//トランザクション終了
end_trans($dbh);

//データベースクローズ
db_close($dbh);


if ($post_send_flag && isset($request_data["md"]) && $request_data["md"] == "del") {
	//退会処理

	//セッションクリア
	session_unregister($update_session_name);

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	if (!$obj_data->check_mail($request_data, $dbh)) {
		$error_flag = true;
	}
	elseif (!$obj_data->check_login($request_data, $dbh)) {
		$error_flag = true;
		$message = array();
		$message["lang_code"] = $lang_code;
		$message["message_num"] = "E01001";
		$obj_data->class_ary["password"] .= $obj_data->error_code;
		$obj_data->class_ary["password_error"] .= get_lang_message($message);
	}

	if ($error_flag) {
		//トランザクション終了
		end_trans($dbh);

		//データベースクローズ
		db_close($dbh);

		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/my-page/account-delete/index" . EXT . "?md=chk");
		exit;
	}

	$search = array();
	$search["lang_code"] = $lang_code;
	$search["user_id"] = $_SESSION["login_info_user"]["user_id"];
	$search["email"] = $_SESSION["login_info_user"]["email"];
	$obj_data->delete_data($search, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);


	//ログアウト処理
	$_SESSION["login"] = array();
	$_SESSION["login_info_user"] = null;
	$_SESSION["error_message"] = null;

	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/my-page/account-delete/complete/index" . EXT);
	exit;
}
elseif ($post_send_flag && isset($request_data["md"]) && $request_data["md"] == "select_language") {
	//言語選択時
	$data = array();
	$data["lang_code"] = get_lang_code($request_data);

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	//ログインユーザーの言語切替
	if (is_login()) {
		$data["user_id"] = $_SESSION["login_info_user"]["user_id"];
		$obj_data->change_status($data, $dbh);
	}

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

}
elseif ($post_send_flag && isset($request_data["md"]) && $request_data["md"] == "mail_up") {
	//会員メールアドレス送信

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	//エラーチェック
	$request_data["double_check_flag"] = true;
//	if (!$obj_data->check_mail($request_data, $dbh) || !$obj_data->check_agree($request_data, $dbh)) {
	if (!$obj_data->check_mail($request_data, $dbh)) {
		//エラー処理

		//トランザクション終了
		end_trans($dbh);

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
		$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/signup/index" . EXT . "?md=chk");
		exit;
	}

	$obj_data->set_mail_data($request_data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	$_SESSION["error_message"] = null;
	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/signup/complete/index" . EXT);
	exit;
}
elseif ($post_send_flag && isset($request_data["md"]) && ($request_data["md"] == "movie_end" || $request_data["md"] == "movie_favorite")) {
	//動画視聴履歴、お気に入り追加
	$data = $request_data;
	if (!isset($data["temple_num"])) {
		exit;
	}

	if (!is_login()) {
		//ログインしていない場合は何もしない
		exit;
	}

	$data["lang_code"] = $lang_code;
	$data["user_id"] = $_SESSION["login_info_user"]["user_id"];
	if ($request_data["md"] == "movie_end") {
		$data["mode_flag"] = 1;
	}
	else {
		$data["mode_flag"] = 2;
	}

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	$obj_data->set_movie_data($data, $dbh);

	//言語コードを更新
	$obj_data->change_status($data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);
}
elseif ($post_send_flag && isset($request_data["md"]) && ($request_data["md"] == "delete_favorite")) {
	//お気に入り解除
	$data = $request_data;
	if (!isset($data["temple_num"])) {
		exit;
	}

	if (!is_login()) {
		//ログインしていない場合は何もしない
		exit;
	}

	$data["user_id"] = $_SESSION["login_info_user"]["user_id"];

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	$obj_data->delete_favorite($data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

}
elseif ($post_send_flag && isset($request_data["md"]) && $request_data["md"] == "mail_flag") {
	//メルマガ購読変更処理

	//セッションクリア
	session_unregister($update_session_name);

	$data = $request_data;
	$data["user_id"] = $_SESSION["login_info_user"]["user_id"];

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	$obj_data->change_status($data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/my-page/subscribe-edit/complete/index" . EXT);
	exit;
}
elseif (isset($request_data["md"]) && $request_data["md"] == "up") {
	//更新処理

	//セッションクリア
	session_unregister($update_session_name);
	$_SESSION["error_message"] = null;

	$error_flag = false;

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	//エラーチェック
	$request_data["double_check_flag"] = true;
	if (!$obj_data->check_error($request_data, $dbh)) {
		$error_flag = true;
	}

	if ($error_flag) {
		//エラー処理

		//トランザクション終了
		end_trans($dbh);

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
		$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

		if ($request_data["temp_id"]) {
			header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/signup/form/index" . EXT . "?md=chk");
		}
		else {
			header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/my-page/profile-edit/index" . EXT . "?md=chk");
		}
		exit;
	}

	if (!$request_data["comp_flag"]) {
		//トランザクション終了
		end_trans($dbh);

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;

		//確認画面へ
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/signup/form/confirm/index" . EXT);
		exit;
	}

	$obj_data->set_data($request_data, $dbh);

	if ($request_data["temp_id"]) {
		//ログイン処理
		$search = array();
		$search["email"] = $request_data["email"];
		$search["password"] = $request_data["password"];
		$obj_data->check_login($search, $dbh);
	}

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	if ($request_data["temp_id"]) {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/signup/form/complete/index" . EXT);
	}
	else {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/my-page/profile-edit/complete/index" . EXT);
	}
	exit;
}
elseif (isset($request_data["md"]) && $request_data["md"] == "reset") {
	//パスワード再設定メール送信

	//セッションクリア
	session_unregister($update_session_name);

	//データベースオープン
	$dbh = db_open();

	if (!$obj_data->send_password($request_data, $dbh)) {
		//エラー処理

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
		$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/password-reset/index" . EXT . "?md=chk");
		exit;
	}

	//データベースクローズ
	db_close($dbh);

	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/password-reset/mail-complete/index" . EXT);
	exit;
}
elseif (isset($request_data["md"]) && $request_data["md"] == "password") {
	//パスワード再設定

	//セッションクリア
	session_unregister($update_session_name);
	$_SESSION["error_message"] = null;

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	$request_data["double_pass_flag"] = true;
	if (!$obj_data->reset_password($request_data, $dbh)) {
		//エラー処理

		//トランザクション終了
		end_trans($dbh);

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
		$_SESSION[$update_session_name]["class_ary"] = $obj_data->class_ary;

		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/password-reset/new/index" . EXT . "?md=chk");
		exit;
	}

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/password-reset/complete/index" . EXT);
	exit;
}
//elseif (isset($request_data["md"]) && $request_data["md"] == "del") {
elseif (strpos($path, "my-page/account-delete/") !== false) {
	//退会確認画面

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		$class_ary = $data["class_ary"];
	}
	else {
		//詳細データ取得
		$obj_data->get_data($_SESSION["login_info_user"]);
		$data = $obj_data->data;
	}

	$hidden .= '    <input type="hidden" name="md" value="del">
	<input type="hidden" name="lang_code" value="' . $lang_code . '">
	<input type="hidden" name="user_id" value="' . $data["user_id"] . '">
';
}
elseif (strpos($path, "my-page/subscribe-edit/") !== false) {
	//メルマガ購読変更画面

	//詳細データ取得
	$obj_data->get_data($_SESSION["login_info_user"]);
	$data = $obj_data->data;
}
elseif (strpos($path, "signup/form/confirm") !== false) {
	//確認画面
	if (!session_is_registered($update_session_name)) {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/login/index" . EXT);
		exit;
	}
	else {
		$data = $_SESSION[$update_session_name];
	}

	$form = '<form name="form_q" action="' . $_SERVER["SCRIPT_NAME"] . '" method="post">';
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
elseif (strpos($path, "signup/form/") !== false || strpos($path, "my-page/profile-edit/") !== false) {
	//本登録、変更
	$hidden = "";
	$disp_email = "";
	$disp_pass = "";
	$hidden_email = "";
	$error_flag = false;

	//データベースオープン
	$dbh = db_open();

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		$class_ary = $data["class_ary"];
	}
	elseif ($request_data["temp_id"]) {
		$search = array();
		$search["temp_id"] = $request_data["temp_id"];
		$obj_data->get_data($search, $dbh);
		$data = $obj_data->data;

		if (!$data) {
			$error_flag = true;
		}
		else {
			$data["age"] = "18～29";
			$data["email2"] = $data["email"];
			$data["password"] = "";
			$data["password2"] = "";
			$data["temp_id"] = $request_data["temp_id"];
			$data["mail_flag"] = "1";
			$param = EXT . "?temp_id=" . $request_data["temp_id"];
		}
	}
	elseif ($request_data["s"] || $_SESSION["login_info_user"]["user_id"]) {
		if (strpos($path, "profile-edit/") === false) {
			$error_flag = true;
		}
		else {
			$search = array();
			$search["user_id"] = $_SESSION["login_info_user"]["user_id"];
			if (!$search["user_id"]) {
				$search = $request_data;
			}
			$obj_data->get_data($search, $dbh);
			$data = $obj_data->data;

			$data["email2"] = $data["email"];
			$data["password"] = "";
			$data["password2"] = "";
		}
	}

	//データベースクローズ
	db_close($dbh);

	if (!$data) {
		$error_flag = true;
	}

	if ($error_flag) {
		$_SESSION["error_message"] = "アクセスエラーです。";
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/login/index" . EXT);
		exit;
	}

	$hidden .= '    <input type="hidden" name="md" value="up">
	<input type="hidden" name="lang_code" value="' . $lang_code . '">
	<input type="hidden" name="user_id" value="' . $data["user_id"] . '">
';
	if ($data["temp_id"]) {
		$hidden .= '<input type="hidden" name="temp_id" value="' . escape_html($data["temp_id"]) . '">
';
		$disp_email = ' style="display:none;" ';
		$hidden_email = '    <input type="hidden" name="email" value="' . escape_html($data["email"]) . '">
	<input type="hidden" name="email2" value="' . escape_html($data["email"]) . '">
';
	}
	else {
		$hidden .= '<input type="hidden" name="comp_flag" value="1">
';
	}

	$javascript = "";

}
elseif (strpos($path, "signup") !== false) {
	//メールアドレス認証
	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		$class_ary = $data["class_ary"];
	}

	$hidden = '    <input type="hidden" name="md" value="mail_up">
	<input type="hidden" name="lang_code" value="' . $lang_code . '">
';

}
elseif (strpos($path, "login") !== false) {
	//ログイン画面
	if ($request_data["md"] == "chk") {
		$data = $_SESSION["user_login"];
		$class_ary = $data["class_ary"];
	}

//	if ($_SESSION["login_info_user"]["user_id"]) {
	if ($_SESSION["login"]["login_flag"]) {
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/my-page/index" . EXT);
		exit;
	}

	$hidden = '    <input type="hidden" name="e" value="' . $event["url_param"] . '">
	<input type="hidden" name="lang_code" value="' . $lang_code . '">
	<input type="hidden" name="url" value="' . $request_data["url"] . '">';

}
elseif (strpos($path, "password-reset/new/") !== false) {
	//パスワード再設定
	$error_flag = false;

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		$class_ary = $data["class_ary"];
	}
	elseif (!$request_data["s"]) {
		$error_flag = true;
	}
	else {
		//詳細データ取得
		$obj_data->get_data($request_data);
		$user = $obj_data->data;

		if (!count_ary($user)) {
			$error_flag = true;
		}
	}

	if ($error_flag) {
		$_SESSION["error_message"] = "アクセスエラーです。";
		header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/login/index" . EXT);
		exit;
	}

	$hidden = '    <input type="hidden" name="md" value="password">
	<input type="hidden" name="lang_code" value="' . $lang_code . '">
	<input type="hidden" name="s" value="' . $request_data["s"] . '">
';

}
elseif (strpos($path, "password-reset/") !== false) {
	//パスワード再設定メールアドレス入力
	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		$class_ary = $data["class_ary"];
	}

	$hidden = '    <input type="hidden" name="md" value="reset">
	<input type="hidden" name="lang_code" value="' . $lang_code . '">
';
}
elseif (strpos($path, "my-page/favorite/") !== false || strpos($path, "my-page/history/") !== false) {
	//視聴履歴一覧、お気に入り一覧

	//データ取得
	$search = array();
	$search["user_id"] = $_SESSION["login_info_user"]["user_id"];
	$search["pref"] = $request_data["pref"];

	//データベースオープン
	$dbh = db_open();

	if (strpos($path, "history/") !== false) {
		//視聴履歴
		$search["mode_flag"] = 1;

		//札所一覧取得
		require_once(CLASS_PATH . 'class_temple.php');
		$obj_temple = new class_temple();

		$obj_temple->get_list($search, $dbh);
		$list = $obj_temple->list;

		//周回取得
		if ($data["ohenro_num"]) {
			$obj_data->get_round_list($data, $dbh);
			$round_list = $obj_data->round_list;
			$round_max = $obj_data->round_max;
		}

	}
	else {
		//お気に入り
		$search["mode_flag"] = 2;

		$obj_data->get_movie_list($search, $dbh);
		$list = $obj_data->movie_list;

		$num1 = $page * $num + 1;
		$max = $obj_data->movie_max;
		$num2 = min($page*$num+count_ary($list), ($page+1)*$num, $max);
	}

	//データベースクローズ
	db_close($dbh);

}
//elseif ($mode == "award") {
elseif (strpos($path, "my-page/award/") !== false) {
	//賞状表示

	//詳細データ取得
	$obj_data->get_data($request_data);
	$data = $obj_data->data;
}
elseif (strpos($path, "my-page/") !== false) {
	//詳細

	//セッションクリア
	session_unregister($update_session_name);

	//お知らせ取得
	require_once(CLASS_PATH . 'class_notice.php');
	$obj_notice = new class_notice();

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	//詳細データ取得
	$data = check_login_user($request_data, $obj_data, $dbh);

	//絞り込み
	$search = $request_data;
	$search["mypage_flag"] = 1;
	$search["limit"] = "limit 5 ";

	//一覧データ取得
	$obj_notice->get_list($search, $dbh);
	$notice = $obj_notice->list;

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	if (!count_ary($data)) {
		header("Location: " . HTTP_ROOT . "/logout.php");
		exit;
	}

	//会員情報
	$data["mypage_flag"] = true;

}
else {
	header("Location: " . HTTP_ROOT . $lang_ary[$lang_code] . "/login/index" . EXT);
	exit;
}

?>
