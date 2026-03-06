<?php
/*-----------------------------------------------------------------------------
件名：ユーザー管理画面
画面：ユーザー管理画面
機能：ユーザー管理画面表示
-----------------------------------------------------------------------------*/

require_once(dirname(__FILE__). '/../../conf.php');
require_once(CLASS_PATH . 'class_user.php');

//ログインチェック
check_login_info(1);

//ルートへのパス
$document_path = "../";

//タイトル
$sub_title = "会員";

//メニューID
$menu_id = "user";

//データの件数/1ページ
$num = 50;

//セッション名
$update_session_name = $menu_id . "_up";

//モードの取得
$mode = get_file_mode($_SERVER["SCRIPT_NAME"]);
$md = $request_data["md"];
$mode_flag = $request_data["mode_flag"];
$sub_title_name = $sub_title;

//クラスオブジェクト生成
$obj_data = new class_user();

if ($post_send_flag && isset($request_data["mode"]) && $request_data["mode"] == "del") {
	//削除

	//セッションクリア
	session_unregister($update_session_name);

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	$request_data["admin_flag"] = true;
	$obj_data->delete_data($request_data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	header("Location: " . $document_path . "comp.html?md=" . $menu_id . "_del");
}
elseif ($post_send_flag && isset($request_data["mode"]) && $request_data["mode"] == "download") {
	//CSVダウンロード

	$file_name = "user" . date("Ymd") . ".csv";
	$data = $request_data;
	$csv_data = "";

	$obj_data->get_list($data);
	$list = $obj_data->list;

	for ($i = 0; $i < count_ary($user_header); $i++) {
		if ($i) {
			$csv_data .= ",";
		}
		$csv_data .= $user_header[$i];
	}
	$csv_data .= "\n";

	//データ作成
	if (count_ary($list) > 0) {
		for ($i = 0; $i < count_ary($list); $i++) {
			for ($j = 0; $j < count_ary($user_keys); $j++) {
				if ($j) {
					$csv_data .= ",";
				}
				$csv_data .= $list[$i][$user_keys[$j]];
			}
			$csv_data .= "\n";
		}
	}
	else {
		$csv_data .= "該当するデータがありませんでした。";
	}

	// ファイルダウンロードのためのヘッダー出力
	header("Content-type: application/octet-stream");
	header("Content-Description: " . $file_name);
	header("Content-Disposition: attachment; filename=\"" . $file_name . "\"");

	print $csv_data;
	exit;
}
elseif ($post_send_flag && $mode == "up") {
	//更新処理

	//セッションクリア
	session_unregister($update_session_name);

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	$error_flag = false;
	$request_data["admin_flag"] = true;
	$request_data["double_check_flag"] = true;

	//エラーチェック
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

		header("Location: " . $_SERVER["SCRIPT_NAME"] . "?md=chk");
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
		header("Location: " . $menu_id . "_detail.html?md=conf");
		exit;
	}

	$obj_data->set_data($request_data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	header("Location: " . $document_path . "comp.html?md=" . $menu_id . "_up");
	exit;
}
elseif (isset($request_data["md"]) && $request_data["md"] == "conf") {
	//確認画面
	if (!session_is_registered($update_session_name)) {
		header("Location: " . $menu_id . "_list.html");
		exit;
	}

	$data = $_SESSION[$update_session_name];
	$sub_title_name = $sub_title;

	if (!$data["mail_flag"]) {
		$data["mail_flag"] = 0;
	}

	if (isset($_SESSION[$update_session_name]["id"]) && $_SESSION[$update_session_name]["id"]) {
		$sub_title_name .= "変更確認";
		$message = "この" . $sub_title . "を変更いたします。<br>\nよろしければ確定ボタンをクリックしてください。";
	}
	else {
		$sub_title_name .= "登録確認";
		$message = "この" . $sub_title . "を登録いたします。<br>\nよろしければ確定ボタンをクリックしてください。";
	}
}
elseif (isset($request_data["md"]) && $request_data["md"] == "del") {
	//確認画面
	$sub_title_name .= "削除確認";
	$message = "この" . $sub_title . "を削除いたします。<br>\nよろしければ削除ボタンをクリックしてください。";

	//詳細データ取得
	$obj_data->get_data($request_data);
	$data = $obj_data->data;
}
elseif ($mode == "up") {
	//登録、変更

	//データベースオープン
	$dbh = db_open();

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		//$data[$menu_id . "_id"] = $data["id"];
	}
	elseif ($request_data["id"]) {
		$obj_data->get_data($request_data, $dbh);
		$data = $obj_data->data;
	}
	else {
		$data["mail_flag"] = "1";
	}

	//データベースクローズ
	db_close($dbh);

	$sub_title_name = $sub_title;

	if ($data["id"]) {
		$sub_title_name .= "更新";
	}
	else {
		$sub_title_name .= "登録";
	}
}
elseif ($mode == "detail") {
	//詳細
	$sub_title_name .= "詳細";

	//詳細データ取得
	$obj_data->get_data($request_data);
	$data = $obj_data->data;
}
else {
	//一覧
	$sub_title_name .= "一覧";

	$page = 0;
	if (isset($request_data["page"]) && $request_data["page"]) {
		$page = $request_data["page"];
	}

	$data = $request_data;
	$data["search"] = "";
	$data["limit"] = " limit " . ($page * $num) . ", " . $num;

	//データベースオープン
	$dbh = db_open();

	$obj_data->get_list($data, $dbh);
	$list = $obj_data->list;

	//データベースクローズ
	db_close($dbh);

	$num1 = $page * $num + 1;
	$max = $obj_data->max;
	$num2 = min($page*$num+count_ary($list), ($page+1)*$num, $max);
}

?>
