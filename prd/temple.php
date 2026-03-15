<?php
/*-----------------------------------------------------------------------------
// お遍路サイト札所表示処理
-----------------------------------------------------------------------------*/

require_once(dirname(__FILE__). '/conf.php');
require_once(CLASS_PATH . 'class_temple.php');

//ルートへのパス
$document_path = "./";

//タイトル
$sub_title = "札所";
$sub_title_name = $sub_title;

//メニューID
$menu_id = "temple";

//データの件数/1ページ
$num = 100;

//セッション名
$update_session_name = $menu_id . "_up";

//モードの取得
$path = $_SERVER["SCRIPT_NAME"];
$md = $request_data["md"];

//クラスオブジェクト生成
$obj_data = new class_temple();

//言語コードの取得
$lang_code = get_lang_code();
$request_data["lang"] = $lang_code;

//動画CSV読み込み
//require_once(dirname(__FILE__). '/./csv.html');
$csv_data = get_csv_movie($lang_code);

if (strpos($path, "temples/temple") !== false) {
	//詳細
	$sub_title_name .= "詳細";

	//詳細データ取得
	$data = $request_data;
	$data["lang_code"] = $lang_code;
	if (is_login()) {
		$data["user_id"] = $_SESSION["login_info_user"]["user_id"];
		$data["movie_num"] = 1;
	}
	$obj_data->get_data($data);
	$data = $obj_data->data;

	require_once(CLASS_PATH . 'class_info.php');
	$obj_info = new class_info();

	$taxi = "";
	$inn = "";
	$interview = $data["interview_info"];

	$item = $data["taxi_info"];
	for ($i = 0; $i < count_ary($item); $i++) {
		if ($i > 0) {
			$taxi .= "、";
		}
		$taxi .= escape_html($item[$i]["info_name"]) . "：" . escape_html($item[$i]["tel"]);
	}

	$item = $data["inn_info"];
	for ($i = 0; $i < count_ary($item); $i++) {
		if ($i > 0) {
			$inn .= "、";
		}
		$inn .= escape_html($item[$i]["info_name"]) . "：" . escape_html($item[$i]["tel"]);
	}

	//動画用URL
	$movie_url = $obj_data->get_movie_url($data, 1);
	$movie_url2 = "";

	//住職ムービーが存在するかのフラグ
	if ($csv_data[$data["temple_num"]-1]["url2"]) {
		$movie_url2 = $obj_data->get_movie_url($data, 2);
	}

	//次の札所情報取得
	if ($data["temple_num"] < 88) {
		$search = array();
		$search["lang_code"] = $lang_code;
		$search["temple_num"] = $data["temple_num"] + 1;
		$obj_data->get_data($search);
		$next = $obj_data->data;
	}

}
elseif (strpos($path, "temples/movie") !== false) {
	//動画画面
	if (isset($request_data["temple"])) {
		//札所動画画面

		$temple = $request_data["temple"];
		$search = $request_data;
		$search["lang_code"] = $lang_code;
		$search["temple_num"] = $temple;
		$obj_data->get_data($search);
		$data = $obj_data->data;

		$num = $request_data["num"];
		if ($num != 1 && $num != 2) {
			$num = 1;
		}

		//動画情報を取得
		$pref  = $csv_data[$temple-1]["pref"];
		$title = $csv_data[$temple-1]["temple"];
		$url = $csv_data[$temple-1]["url" . $num];
		$movie_id = $obj_data->get_movie_id($url);

		$js_string = "<script>
    set_movie_id(" . $lang_code . ", " . $temple . ", " . $num . ");
</script>";
	}
	else {
		//その他の動画画面
		$movie_key = "";
		$movie_id = "";
		$title = "";

		if (isset($request_data["mid"])) {
			//管理IDからURLを取得する
			$movie_key = $request_data["mid"];
			for ($i = 88; $i < count_ary($csv_data); $i++) {
				if ($csv_data[$i]["pref"] == $movie_key) {
					$movie_id = $obj_data->get_movie_id($csv_data[$i]["url1"]);
					$title = $csv_data[$i]["temple"];
					break;
				}
			}
		}

		if (!$movie_id && isset($request_data["url"])) {
			$movie_id = $obj_data->get_movie_id($request_data["url"]);
		}

		$js_string = "<script>
    set_movie_id(" . $lang_code . ");
</script>";
	}

	//会員かどうかで読み込むファイルを変える
	$file_user = "user";
	if (is_login()) {
		$file_user = "member";
	}

}
elseif (strpos($path, "temples/") !== false) {
	//一覧
	$sub_title_name .= "一覧";

	$page = 0;
	if (isset($request_data["page"]) && $request_data["page"]) {
		$page = $request_data["page"];
	}

	//デフォルト表示県
	if (!$request_data["tab"]) {
		$request_data["tab"] = "tokushima";
	}

	//絞り込み
	$data = $request_data;
	$data["pref"] = $shikoku_tab[$request_data["tab"]];

	//スペイン語、フランス語、イタリア語、ドイツ語の場合は英語を取得
	$search = array();
	$search["en_flag"] = true;
	$data["lang_code"] = get_lang_code($search);

	if (is_login()) {
		$data["user_id"] = $_SESSION["login_info_user"]["user_id"];
	}
	$data["limit"] = "limit " . ($page * $num) . ", " . $num;

	//一覧データ取得
	$obj_data->get_list($data);
	$list = $obj_data->list;

	$num1 = $page * $num + 1;
	$max = $obj_data->max;
	$num2 = min($page*$num+count_ary($obj_data->list), ($page+1)*$num, $max);

	//動画用URL
	for ($i = 0; $i < count_ary($list); $i++) {
		$list[$i]["movie2_flag"] = false;
		if ($csv_data[$list[$i]["temple_num"]-1]["url2"]) {
			//住職ムービーが存在するかのフラグ
			$list[$i]["movie2_flag"] = true;
			$list[$i]["movie_url2"] = $obj_data->get_movie_url($list[$i], 2);
		}
		$list[$i]["movie_url1"] = $obj_data->get_movie_url($list[$i], 1);
	}

}
else {
}

?>
