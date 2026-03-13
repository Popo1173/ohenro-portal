<?php
/*-----------------------------------------------------------------------------
件名：完了画面
画面：管理画面
機能：管理画面完了画面表示
-----------------------------------------------------------------------------*/

require_once(dirname(__FILE__). '/../conf.php');

//ログインチェック
check_login_info();

//モードの取得
$mode = "";
if ($request_data["menu_id"]) {
	$mode = $request_data["menu_id"];
}
elseif ($request_data["md"]) {
	$str = $request_data["md"];
	$pos = strrpos($str, "_");
	if ($pos !== false) {
		$mode = substr($str, 0, $pos);
	}
}

$mode_array = array(
	"notice"=>"お知らせ",
	"temple"=>"札所",
	"inn"=>$info_ary[1],
	"taxi"=>$info_ary[2],
	"interview"=>$info_ary[3],
	"admin"=>"アカウント",
	"user"=>"会員",
	"mail"=>"メールマガジン",
	"template"=>"メールテンプレート",
	"log"=>"ログ",
	"movie"=>"動画",
);

$folder_array = array(
	"info"=>"temple",
	"inn"=>"temple",
	"taxi"=>"temple",
	"interview"=>"temple",
	"template"=>"user",
	"mail"=>"user",
);

if (isset($request_data["md"]) && $request_data["md"] == $mode . "_up") {
	//タイトル
	$sub_title_name = $mode_array[$mode] . "更新完了";
	$title_name = $mode_array[$mode];
	$mode_name = "更新";
	$button_name = $mode_array[$mode] . "一覧へ";

	if ($request_data["mode"] === "draft") {
		$message_string = $title_name . "の下書きを保存いたしました。<br />";
	}

	if ($request_data["mode_flag"]) {
		$next_page_url = "./" . $folder_array[$mode] . "/info_list" . EXT . "?mode_flag=" . $request_data["mode_flag"];
	}
	elseif ($folder_array[$mode]) {
		$next_page_url = "./" . $folder_array[$mode] . "/" . $mode . "_list" . EXT;
	}
	else {
		$next_page_url = "./" . $mode . "/" . $mode . "_list" . EXT;
	}
}
elseif (isset($request_data["md"]) && $request_data["md"] == $mode . "_del") {
	//タイトル
	$sub_title_name = $mode_array[$mode] . "削除完了";
	$title_name = $mode_array[$mode];
	$mode_name = "削除";
	$button_name = $mode_array[$mode] . "一覧へ";

	if ($request_data["mode_flag"]) {
		$next_page_url = "./" . $folder_array[$mode] . "/info_list" . EXT . "?mode_flag=" . $request_data["mode_flag"];
	}
	elseif ($folder_array[$mode]) {
		$next_page_url = "./" . $folder_array[$mode] . "/" . $mode . "_list" . EXT;
	}
	else {
		$next_page_url = "./" . $mode . "/" . $mode . "_list" . EXT;
	}
	if ($request_data["menu_id"] == "log") {
		$button_name = "戻る";
		$next_page_url = "./top" . EXT;
	}
}
elseif (isset($request_data["md"]) && $request_data["md"] == $mode . "_upload") {
	//タイトル
	$sub_title_name = $mode_array[$mode] . "アップロード完了";
	$title_name = $mode_array[$mode];
	$message_string = $title_name . "のアップロードが完了いたしました。<br />";
	$mode_name = "アップロード";
	$button_name = $mode_array[$mode] . "一覧";

	if ($request_data["mode_flag"]) {
		$next_page_url = "./" . $folder_array[$mode] . "/info_list" . EXT . "?mode_flag=" . $request_data["mode_flag"];
	}
	elseif ($folder_array[$mode]) {
		$next_page_url = "./" . $folder_array[$mode] . "/" . $mode . "_list" . EXT;
	}
	else {
		$next_page_url = "./" . $mode . "/" . $mode . "_list" . EXT;
	}
}

?>
