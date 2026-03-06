<?php
/*-----------------------------------------------------------------------------
件名：宿、タクシー、旅館インタビュー情報管理
画面：管理画面情報一覧、CSVアップロード、編集
機能：管理画面情報管理
-----------------------------------------------------------------------------*/

require_once(dirname(__FILE__). '/../../conf.php');
require_once(CLASS_PATH . 'class_info.php');

//ルートへのパス
$document_path = "../";

//ログインチェック
check_login_info(1);

//タイトル
$sub_title = $info_ary[$request_data["mode_flag"]];
$sub_title_name = $sub_title;

//メニューID
$menu_id = "info";

//データの件数/1ページ
$num = 100;

//セッション名
$update_session_name = $menu_id . "_up";

//モードの取得
$mode = get_file_mode($_SERVER["SCRIPT_NAME"]);
$md = $request_data["md"];

//クラスオブジェクト生成
$obj_data = new class_info();

if ($post_send_flag && isset($request_data["mode"]) && $request_data["mode"] == "del") {
	//削除

	//セッションクリア
	session_unregister($update_session_name);

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	$obj_data->delete_data($request_data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	header("Location: ../comp.html?md=" . $info_str[$request_data["mode_flag"]] . "_del&mode_flag=" . $request_data["mode_flag"]);
}
elseif ($post_send_flag && isset($request_data["mode"]) && $request_data["mode"] == "image_del1") {
	//画像削除

	//詳細データ取得
	$obj_data->get_data($request_data);
	$data = $obj_data->data;

	if (strpos($request_data["num"], ",") !== false) {
		$ary = explode(",", $request_data["num"]);
		for ($i = 0; $i < count_ary($ary); $i++) {
			if (!$ary[$i]) {
				continue;
			}
			$data["num"] = $ary[$i];
			$obj_data->delete_image($data);
		}
	}
	else {
		$data["num"] = $request_data["num"];
		$obj_data->delete_image($data);
	}

	header("Location: info_detail.html?" . $request_data["query"]);
}
elseif ($post_send_flag && isset($request_data["mode"]) && $request_data["mode"] == "image_del") {
	//画像削除

	//詳細データ取得
	$obj_data->get_data($request_data);
	$data = $obj_data->data;

	$obj_data->delete_file($data);

	header("Location: info_list.html?" . $request_data["query"]);
}
elseif ($post_send_flag && isset($request_data["mode"]) && $request_data["mode"] == "download") {
	//CSVダウンロード
	$data = $request_data;
	$mode_flag = $data["mode_flag"];
	$file_name = $info_str[$mode_flag] . "_" . $lang_ary[$data["lang_code"]] . "_" . date("Ymd") . ".csv";
	$csv_data = "";

	$search = array();
	$search["lang_code"] = $data["lang_code"];
	$search["mode_flag"] = $mode_flag;
	$obj_data->get_list($search);
	$list = $obj_data->list;

	//ヘッダー作成
	for ($i = 0; $i < count_ary($info_header[$mode_flag]); $i++) {
		if ($i) {
			$csv_data .= ",";
		}
		$csv_data .= $info_header[$mode_flag][$i];
	}
	$csv_data .= "\n";

	//データ作成
	if (count_ary($list) > 0) {
		for ($i = 0; $i < count_ary($list); $i++) {
			for ($j = 0; $j < count_ary($info_keys[$mode_flag]); $j++) {
				if ($j) {
					$csv_data .= ",";
				}
				$csv_data .= "\"" . $list[$i][$info_keys[$mode_flag][$j]] . "\"";
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
elseif ($post_send_flag && isset($request_data["mode"]) && $request_data["mode"] == "upload") {
	//CSVアップロード
	$mode_flag = $request_data["mode_flag"];
	$error_flag = false;

	//セッションクリア
	session_unregister($update_session_name);

	//CSVクラス
	require_once(CLASS_PATH . 'class_csv.php');
	$obj_csv = new class_csv();

	//CSVファイルオープン
	$file = $_FILES["user_csv"]["tmp_name"];
	if (!$file) {
		$error_flag = true;
		$_SESSION[$update_session_name]["error_message"] = "CSVファイルを選択してください。<br>\n";
	}

	if (!$error_flag) {
		$obj_csv->get_list($file);
		$obj_csv->set_keys($info_keys[$mode_flag]);
		$obj_csv->set_lang($request_data);
		$csv_data = $obj_csv->list;
		if (!$csv_data) {
			$error_flag = true;
			$_SESSION[$update_session_name]["error_message"] = "データが見つかりませんでした。<br>\n";
		}
	}

	//エラーチェック
	if (!$error_flag && !$csv_data) {
		$error_flag = true;
		$_SESSION[$update_session_name]["error_message"] = "データが見つかりませんでした。<br>\n";
	}
	if (!$error_flag && !$obj_data->check_upload_error($csv_data)) {
		$error_flag = true;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;
	}

	if ($error_flag) {
		//エラー処理
		header("Location: " . $_SERVER["SCRIPT_NAME"] . "?md=chk");
		exit;
	}

	//データベースオープン
	$dbh = db_open();

	//トランザクション開始
	start_trans($dbh);

	if (!$obj_data->upload_data($csv_data, $dbh)) {
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

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);


	header("Location: " . $document_path . "comp" . EXT . "?md=" . $info_str[$request_data["mode_flag"]] . "_upload&mode_flag=" . $request_data["mode_flag"]);
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

	//エラーチェック
	if (!$obj_data->check_error($request_data, $dbh)) {
		//エラー処理

		//トランザクション終了
		end_trans($dbh);

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;
		$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;

		if ($obj_data->error_num == 1) {
			$_SESSION[$update_session_name]["info_num"] = "";
		}

		header("Location: " . $_SERVER["SCRIPT_NAME"] . "?md=chk");
		exit;
	}
/*
	if (is_uploaded_file($_FILES["image_file"]["tmp_name"])) {
		$request_data["image_up_flag"] = true;
	}
	for ($i = 1; $i <= 10; $i++) {
		if (is_uploaded_file($_FILES["image_file" . $i]["tmp_name"])) {
			$request_data["image_up_flag" . $i] = true;
		}
	}
*/
	if (!$request_data["comp_flag"]) {
		//トランザクション終了
		end_trans($dbh);

		//データベースクローズ
		db_close($dbh);

		//セッション保持
		$_SESSION[$update_session_name] = $request_data;

		if (!$obj_data->set_tmp_file($request_data)) {
			//エラー処理
			$_SESSION[$update_session_name]["error_message"] = $obj_data->error_message;

			header("Location: " . $_SERVER["SCRIPT_NAME"] . "?md=chk");
			exit;
		}

		if (is_uploaded_file($_FILES["image_file"]["tmp_name"])) {
			$_SESSION[$update_session_name]["image_file"] = $file_name;
		}
		elseif (isset($request_data["old_image_file"]) && $request_data["old_image_file"]) {
			$_SESSION[$update_session_name]["image_file"] = $request_data["old_image_file"];
		}
		for ($i = 1; $i <= 10; $i++) {
			if (is_uploaded_file($_FILES["image_file" . $i]["tmp_name"])) {
				$_SESSION[$update_session_name]["image_file" . $i] = $file_name;
			}
			elseif (isset($request_data["old_image_file" . $i]) && $request_data["old_image_file" . $i]) {
				$_SESSION[$update_session_name]["image_file" . $i] = $request_data["old_image_file" . $i];
			}
		}

		//確認画面へ
		header("Location: info_detail.html?md=conf");
		exit;
	}

	$obj_data->set_data($request_data, $dbh);

	//トランザクション終了
	end_trans($dbh);

	//データベースクローズ
	db_close($dbh);

	if (isset($request_data["id"]) && $request_data["id"]) {
		header("Location: ../comp.html?md=" . $info_str[$request_data["mode_flag"]] . "_chg&mode_flag=" . $request_data["mode_flag"]);
	}
	else {
		header("Location: ../comp.html?md=" . $info_str[$request_data["mode_flag"]] . "_up&mode_flag=" . $request_data["mode_flag"]);
	}
	exit;
}
elseif ($mode == "delete") {
	$sub_title_name .= $sub_title . "一括削除";
	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
	}
}
elseif (isset($request_data["md"]) && $request_data["md"] == "conf") {
	//確認画面
	if (!session_is_registered($update_session_name)) {
		header("Location: " . $menu_id . "_list.html");
		exit;
	}

	$data = escape_sess($_SESSION[$update_session_name]);

	if (!$data["stock"]) {
		$data["stock"] = "0";
	}

	//データベースオープン
	$dbh = db_open();

	$obj_data->data = $data;
	$obj_data->get_image_file_name();
	$data = $obj_data->data;

	//データベースクローズ
	db_close($dbh);

	$sub_flag = false;
	for ($i = 1; $i <= 10; $i++) {
		if ($data["image_flag" . $i]) {
			$sub_flag = true;
			break;
		}
	}

	if (isset($data["info_id"]) && $data["info_id"]) {
		$sub_title_name .= "変更確認";
		$message = "この" . $sub_title . "を変更いたします。<br>\nよろしければ確定ボタンをクリックしてください。";
	}
	else {
		$sub_title_name .= "登録確認";
		$message = "この" . $sub_title . "を登録いたします。<br>\nよろしければ確定ボタンをクリックしてください。";
		$data["up_date"] = date("Y/m/d h:m");
	}
}
elseif (isset($request_data["md"]) && $request_data["md"] == "del") {
	//確認画面
	$sub_title_name .= "削除確認";
	$message = "この" . $sub_title . "を削除いたします。<br>\nよろしければ削除ボタンをクリックしてください。";

	//詳細データ取得
	$obj_data->get_data($request_data);
	$data = $obj_data->data;

	$sub_flag = false;
	for ($i = 1; $i <= 10; $i++) {
		if ($data["image_flag" . $i]) {
			$sub_flag = true;
			break;
		}
	}
}
elseif ($mode == "download") {
	//CSVダウンロード
	$sub_title_name = $sub_title . "CSVダウンロード";

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
	}
	else {
		$data = $request_data;
	}
}
elseif ($mode == "upload") {
	//CSVアップロード
	$sub_title_name = $sub_title . "CSVアップロード";

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
	}
	else {
		$data = $request_data;
	}
}
elseif ($mode == "up") {
	//登録、変更

	//データベースオープン
	$dbh = db_open();

	if ($request_data["md"] == "chk") {
		$data = $_SESSION[$update_session_name];
		//$data[$menu_id . "_id"] = $data["id"];

		$obj_data->data = $data;
		$obj_data->get_image_file_name();
		$data = $obj_data->data;
	}
	elseif (isset($request_data["id"])) {
		$obj_data->get_data($request_data, $dbh);
		$data = $obj_data->data;
	}
	else {
		$data = $request_data;
		$data["status"] = 1;
	}

	//データベースクローズ
	db_close($dbh);

	$sub_title_name = $sub_title;

	if ($data["info_id"]) {
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

	$sub_flag = false;
	for ($i = 1; $i <= 10; $i++) {
		if ($data["image_flag" . $i]) {
			$sub_flag = true;
			break;
		}
	}
}
elseif ($mode == "list") {
	//一覧
	$sub_title_name .= "一覧";

	$page = 0;
	if (isset($request_data["page"]) && $request_data["page"]) {
		$page = $request_data["page"];
	}

	//絞り込み
	$data = $request_data;
	$data["search"] = "";
	$data["limit"] = "limit " . ($page * $num) . ", " . $num;

	//データベースオープン
	$dbh = db_open();

	//一覧データ取得
	$obj_data->get_list($data, $dbh);

	//データベースクローズ
	db_close($dbh);

	$num1 = $page * $num + 1;
	$max = $obj_data->max;
	$num2 = min($page*$num+count_ary($obj_data->list), ($page+1)*$num, $max);

	$list = $obj_data->list;
}
?>
