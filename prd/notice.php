<?php
/*-----------------------------------------------------------------------------
// お遍路サイトお知らせ表示処理
-----------------------------------------------------------------------------*/

require_once(dirname(__FILE__). '/conf.php');
require_once(CLASS_PATH . 'class_notice.php');

//ルートへのパス
$document_path = "./";

//タイトル
$sub_title = "お知らせ";
$sub_title_name = $sub_title;

//メニューID
$menu_id = "notice";

//データの件数/1ページ
$num = 100;

//セッション名
$update_session_name = $menu_id . "_up";

//モードの取得
$mode = get_file_mode($_SERVER["SCRIPT_NAME"]);
$md = $request_data["md"];

//クラスオブジェクト生成
$obj_data = new class_notice();


if (strpos($path, "news/detail") !== false) {
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
elseif (strpos($path, "news") !== false) {
	//一覧
	$sub_title_name .= "一覧";

	$page = 0;
	if (isset($request_data["page"]) && $request_data["page"]) {
		$page = $request_data["page"];
	}

	//絞り込み
	$data = $request_data;
	$data["top_flag"] = 1;
	$data["limit"] = "limit " . ($page * $num) . ", " . $num;

	//データベースオープン
	$dbh = db_open();

	//一覧データ取得
	$obj_data->get_list($data, $dbh);
	$list = $obj_data->list;

	//データベースクローズ
	db_close($dbh);

	$num1 = $page * $num + 1;
	$max = $obj_data->max;
	$num2 = min($page*$num+count_ary($list), ($page+1)*$num, $max);

}
?>
