<?php

//------------------------------------------------------------------------------
//URLから言語コードを取得
function get_lang_code($request_data = null) {
	global $lang_ary;
	$data = $request_data;
/*
	if (isset($_SESSION["lang_code"]) && $_SESSION["lang_code"]) {
		return $_SESSION["lang_code"];
	}

	if (is_login()) {
		return $_SESSION["login_info_user"]["lang_code"];
	}
*/
	if (isset($data["lang_code"])) {
		return $data["lang_code"];
	}

	$lang = null;
	if (isset($data["lang"])) {
		$lang = $data["lang"];
	}
	else {
		// 1. 現在のURLのパス部分を取得（例: /ja/dir2/index.html）
		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		// 2. 「/」で分解する
		$segments = explode('/', trim($path, '/'));

		// 3. 最初の要素を取得
		$lang = $segments[0];
/*
		// 1. ホスト名を取得 (ja.ohenro.online)
		$host = parse_url($_SERVER["SERVER_NAME"], PHP_URL_HOST);

		// 2. ホスト名をドットで分割
		$parts = string_to_array('.', $host);

		// 3. サブドメインを抽出 (要素が3つ以上なら、最初の要素がサブドメイン)
		if (count_ary($parts) >= 3) {
		    $lang = $parts[0];
		}
*/
	}

	$lang_code = null;
	if (string_length($lang)) {
//		$lang_code = array_search($lang, $lang_ary);
		foreach($lang_ary as $key => $val) {
			if (strtolower($val) == strtolower($lang)) {
				$lang_code = $key;
			}
		}

		if ($lang_code !== null) {
			if ($data["en_flag"]) {
				$lang_code = get_en_code($lang_code);
			}
			return $lang_code;
		}
	}

	return 0;
}

//------------------------------------------------------------------------------
//スペイン語、フランス語、イタリア語、ドイツ語の場合は英語を取得
function get_en_code($lang_code) {
	global $lang_ary, $lang_string;

	if (!array_key_exists($lang_code, $lang_string)) {
		return array_search('en', $lang_ary);
	}

	return $lang_code;
}

//------------------------------------------------------------------------------
//各言語のメッセージをCSVから取得
//引数の連想配列にlang_codeとmessage_num(CSV内のメッセージコード)を格納して渡す
function get_lang_message($request_data = null) {
	$data = $request_data;
	$csv_data = get_csv_message();
	$data["lang_code"] = get_en_code($data["lang_code"]);

	for ($i = 0; $i < count_ary($csv_data); $i++) {
		if ($data["message_num"] == $csv_data[$i]["message_num"]) {
			return $csv_data[$i][$data["lang_code"]];
		}
	}
	return null;
}

//------------------------------------------------------------------------------
//メッセージCSVを取得
function get_csv_message() {
	global $message_keys;

	//言語毎のCSVファイル名
	$file = CSV_SYS_ROOT . "message.csv";

	if (!file_exists($file)) {
		return null;
	}

	//CSVクラス
	require_once(CLASS_PATH . 'class_csv.php');
	$obj_csv = new class_csv();

	$obj_csv->get_list($file);
	$obj_csv->set_keys($message_keys);
//print_r($obj_csv->list);exit;
	return $obj_csv->list;
}

//------------------------------------------------------------------------------
//動画CSVを取得
function get_csv_movie($lang_code) {
	global $lang_ary, $keys;

	//言語毎のCSVファイル名
	$file = CSV_FILE_PATH . $lang_ary[$lang_code] . ".csv";

	if (!file_exists($file)) {
		return null;
	}

	//CSVクラス
	require_once(CLASS_PATH . 'class_csv.php');
	$obj_csv = new class_csv();

	$obj_csv->get_list($file);
	$obj_csv->set_keys($keys);

	return $obj_csv->list;
}


//------------------------------------------------------------------------------
//ログインフラグチェック
//ログイン状態かどうかを確認する
//引数 : 管理画面フラグ
//戻り値 : ログイン情報
function is_login($admin_flag = false) {
//	if (session_is_registered("login_info")) {
	if ($admin_flag) {
		if ($_SESSION["login"]["login_admin_flag"]) {
			return true;
		}
		return false;
	}
	else {
		global $request_data;
		if ($_SESSION["login"]["login_admin_flag"] && $request_data["preview"]) {
			return true;
		}
		if ($_SESSION["login"]["login_flag"]) {
			return true;
		}
		return false;
	}
}

//------------------------------------------------------------------------------
//管理画面ログイン状態チェック
//ログイン状態でない場合は、トップへジャンプする
//引数 : 管理画面フラグ
//戻り値 : ログイン情報
function check_login_info($flag = 1) {
	if (session_is_registered("login_info")) {
		//セッション更新
		$_SESSION["login_info"] = $_SESSION["login_info"];

		return true;
	}

	// セッションクリア
	$_SESSION["login"]["login_admin_flag"] = false;

	// ログイン画面へ
	$url = HTTP_ROOT . "admin/index" . EXT . "?timeout=1";
	if (('POST' == $_SERVER['REQUEST_METHOD']) && (!empty($_POST))) {
	}
	else {
		$url .= "&url=" . $_SERVER["SCRIPT_NAME"];
	}
	header("Location: " . $url);
	exit;
}

//------------------------------------------------------------------------------
//ユーザー画面ログイン状態チェック
//ログイン状態でない場合は、ログイン画面へジャンプする
//引数 : なし
//戻り値 : ログイン情報
function check_login_user() {
	global $lang_ary;

	//ログインありの場合
	if ($_SESSION["login"]["login_flag"]) {
		//セッション更新
		$_SESSION["login_info_user"] = $_SESSION["login_info_user"];

		return true;
	}

	// セッションクリア
	$_SESSION["login"]["login_flag"] = false;

	// ログイン画面へ
	$url = HTTP_ROOT . $lang_ary[get_lang_code()] . "/login/index" . EXT . "?timeout=1";
	if (('POST' == $_SERVER['REQUEST_METHOD']) && (!empty($_POST))) {
	}
	else {
		$url .= "&url=" . $_SERVER["SCRIPT_NAME"];
	}

	header("Location: " . $url);
	exit;
}

//------------------------------------------------------------------------------
//ページ切り替えHTML出力
//引数 : 最大件数、1ページ件数、現在ページ、ファイル名、パラメータ
//戻り値 : なし
function page_link($max, $num, $page, $file = "", $param = "")
{
	global $obj_smarty;
	if ($max <= $num) {
		return;
	}

	$get_data = $_GET;
	$query = $param;
	$hidden = "";
	foreach ($get_data as $key => $value) {
		if ($key != "page") {
			$hidden .= '<input type="hidden" name="' . $key . '" value="' . $value . '">' . "\n";
			if (!$param) {
				$query .= '&' . $key . '=' . $value;
			}
		}
	}

	$filename = $file;
	if (!$file) {
		$filename = $_SERVER["SCRIPT_NAME"];
	}
?>
<!--page_link_start//-->	
<table width="100%" height="25" border="0" cellpadding="0" cellspacing="0">
  <form name="page_change" action="<?=$filename?>" method="get">
  <?=$hidden?>
      <tr>
        <td align="right"><?php
	if ($page != 0) {
?><a href="<?=$filename?>?page=<?=$page-1?><?=$query?>" class="link_b">&lt;&lt;前のページ</a><?php
	}
	else {
		print "&lt;&lt;前のページ";
	}
?><img src="<?=IMG_PATH?>img_<?=$color_ary[$color_template]?>/cmn/0.gif" width="5" height="1">
		<select name="page" size="1" onChange="this.form.submit();">
<?php
	for ($i = 0; $i < $max / $num; $i++) {
		if ($i == $page) {
?>
          <option value="<?=$i?>" selected><?=$i+1?></option>
<?php
		}
		else {
?>
          <option value="<?=$i?>"><?=$i+1?></option>
<?php
		}
	}
?>
        </select>
		/<?=$i?>
		<img src="<?=IMG_PATH?>img_<?=$color_ary[$color_template]?>/cmn/0.gif" width="5" height="1"><?php
	if ($page * $num < $max - $num) {
?><a href="<?=$filename?>?page=<?=$page+1?><?=$query?>" class="link_b">次のページ&gt;&gt;</a><?php
	}
	else {
		print "次のページ&gt;&gt;";
	}
?></td>
      </tr>
  </form>
</table>
<!--//page_link_end-->
<?php
}

//------------------------------------------------------------------------------
//デバッグ表示、出力
//引数 : タイトル、メッセージ
//戻り値 : なし
function display_debug($message, $code = "") {
	$msg = $message;
	if ($code) {
		$msg = $code . ":" . $msg;
	}
	if (DEV_FLAG) {
		d($msg);
	}
	print_access_log($msg);
}

//------------------------------------------------------------------------------
//メッセージ画面表示
//引数 : タイトル、メッセージ
//戻り値 : なし
function display_message($msg, $title = "エラー", $mode_flag = "") {
	global $obj_smarty;
	$obj_smarty->assign("sub_title", $title);
	$obj_smarty->assign("message", $msg);
	$obj_smarty->display("message.tpl");

	exit;
}

//------------------------------------------------------------------------------
//エラー処理
//引数 : エラーメッセージ、エラーコード
//戻り値 : なし
function display_error($message, $error = "") {
	global $obj_smarty;

	if (!DEV_FLAG) {
		$data["title"] = "メンテナンス中";
		$data["detail"] = "ただいま、メンテナンス中です。<br>\n恐れ入りますが、しばらく経った後に再度アクセスしてください。";
	}
	else {
		$data["title"] = "エラー";
		$data["detail"] = $message;
		$data["error"] = $error;
	}

	$obj_smarty->assign("sub_title", $data["title"]);
	$obj_smarty->assign("data", $data);
	$obj_smarty->display("error.tpl");
	exit;
}

//------------------------------------------------------------------------------
//エラー処理
//PEARでCatchしたエラーを処理
//引数 : エラーオブジェクト
//戻り値 : なし
function error_handle($error) {
	global $obj_smarty;
	
	$data["title"] = "メンテナンス中";
	$data["detail"] = "ただいま、メンテナンス中です。<br>\n恐れ入りますが、しばらく経った後に再度アクセスしてください。";

	print_r($error);
	exit;
}

?>
