<?php
/*-----------------------------------------------------------------------------
件名：札所情報クラス
画面：札所関連
機能：札所管理
-----------------------------------------------------------------------------*/

class class_temple  {
	var $data;
	var $list;
	var $max;
	var $field_ary;
	var $error_message;
	var $no_img;

	function class_temple() {
		$this->__construct();
	}

	function __construct() {
		//データベースのフィールド
		$this->field_ary = array(
			"lang_code",
			"temple_num",
			"pref",
			"temple_name",
			"temple_kana",
			"mountain",
			"mount_kana",
			"infirmary",
			"infir_kana",
			"address",
			"tel",
			"access",
			"detail",
			"shuuha",
			"honzon",
			"kaiki",
			"founding",
			"shingon",
			"next_temple",
			"car",
			"walk",
			"next_comment",
			"ohenro_item",
			"inn",
			"taxi",
			"interview",
			"comment",
			"shukubou",
			"language",
			"food",
			"Johannes",
			"movie_url1",
			"movie_url2",
			"image_url1",
			"image_url2",
			"movie_detail1",
			"movie_detail2",
			"movie_category",
		);

		$this->no_img = CMN_PATH . "noimg.jpg";
	}

	//詳細データ取得
	function get_data($request_data, $db_handle = null) {
		$data = $request_data;

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$sql_relation = "";
		$sql_search = "";

		if ($data["id"]) {
			$sql_search .= "
		and
			t.temple_id = " . escape_sql($data["id"]);
		}
		if ($data["temple_id"]) {
			$sql_search .= "
		and
			t.temple_id = " . escape_sql($data["temple_id"]);
		}
		if ($data["temple_num"]) {
			$sql_search .= "
		and
			t.temple_num = " . escape_sql($data["temple_num"]);
		}
		if (string_length($data["lang_code"])) {
			$sql_search .= "
		and
			t.lang_code = " . escape_sql($data["lang_code"]);
		}
		if ($data["user_id"] && $data["movie_num"]) {
			$sql_relation .= "
		and
			r.user_id = " . escape_sql($data["user_id"]) . "
		and
			r.movie_num = " . escape_sql($data["movie_num"]);
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			t." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			t.temple_id,
			r.relation_id,
			" . $sql_field . "
			t.update_date as up_date
		from
			" . TBL_HEAD . "temple t
		left join
			" . TBL_HEAD . "movie_user r
		on
			t.temple_num = r.temple_num ". $sql_relation . "
		where
			t.temple_id is not null " . $sql_search . "
		";

		$item = get_array($dbh, $sql);
		$data = $item[0];
/*
		//サブ画像があるかどうかのフラグ
		$data["sub_flag"] = false;
		for ($i = 1; $i <= 10; $i++) {
			if ($data["image_flag" . $i]) {
				$data["sub_flag"] = true;
				break;
			}
		}
*/
		//Google Map用に日本語の住所取得
		$data["map_address"] = $data["infirmary"] . $data["temple_name"] . "+" . $data["pref"] . $data["address"];
		if ($data["lang_code"] > 0) {
			$search = array();
			$search["lang_code"] = 0;
			$search["temple_num"] = $data["temple_num"];
			$this->get_data($search, $dbh);
			$data["map_address"] = $this->data["map_address"];
		}

		require_once(CLASS_PATH . 'class_info.php');
		$obj_data = new class_info();

		//宿情報取得
		$tmp = string_to_array(";", $data["inn"]);
		for ($i = 0; $i < count_ary($tmp); $i++) {
			$search = array();
			$search["lang_code"] = $data["lang_code"];
			$search["info_num"] = $tmp[$i];
			$search["mode_flag"] = 1;
			$obj_data->get_data($search, $dbh);

			$data["inn_info"][$i] = $obj_data->data;
		}

		//タクシー情報取得
		$tmp = string_to_array(";", $data["taxi"]);
		for ($i = 0; $i < count_ary($tmp); $i++) {
			$search = array();
			$search["lang_code"] = $data["lang_code"];
			$search["info_num"] = $tmp[$i];
			$search["mode_flag"] = 2;
			$obj_data->get_data($search, $dbh);

			$data["taxi_info"][$i] = $obj_data->data;
		}

		//旅館インタビュー情報取得
		$tmp = string_to_array(";", $data["interview"]);
		for ($i = 0; $i < count_ary($tmp); $i++) {
			$search = array();
			$search["lang_code"] = $data["lang_code"];
			$search["info_num"] = $tmp[$i];
			$search["mode_flag"] = 3;
			$obj_data->get_data($search, $dbh);

			$data["interview_info"][$i] = $obj_data->data;
		}

		$this->data = $data;

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		if (!count_ary($item)) {
			return false;
		}

		return true;
	}

	//一覧データ取得
	function get_list($request_data, $db_handle = null) {
		$data = $request_data;

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$sql_search = $data["search"];
		if ($data["free_word"]) {
			$word = $data["free_word"];
			$word = str_replace("　", " ", $word);
			$word_ary = explode(" ", $word);

			$sql_field = "concat(";
			$i = 0;
			foreach ($this->field_ary as $value) {
				if ($value == "temple_num" || $value == "lang_code") {
					continue;
				}
				else {
					if ($i) {
						$sql_field .= ", ";
					}
					$sql_field .= "ifnull(t." . $value . ", ''), ' '";
				}
				$i++;
			}
			$sql_field .= ") ";

			foreach ($word_ary as $value) {
				$sql_search .= "
		and
			" . $sql_field . " collate utf8_general_ci like '%" . escape_sql($value, false) . "%' ";
			}
		}

		if (isset($data["lang_code"]) && string_length($data["lang_code"])) {
			$sql_search .= "
		and
			t.lang_code = " . escape_sql($data["lang_code"]);
		}
		if (isset($data["pref"]) && $data["pref"]) {
			if ($data["lang_code"] > 0) {
				//日本語以外は、日本語の県名で抽出した後で札所Noで検索する
				$sql_search .= "
		and
			t.temple_num in (
				select
					temple_num
				from
					" . TBL_HEAD . "temple
				where
					lang_code = 0
				and
					pref = " . escape_sql($data["pref"]) . "
			)";
			}
			else {
				$sql_search .= "
		and
			t.pref = " . escape_sql($data["pref"]);
			}
		}

		if ($data["order_id"] === "desc") {
			$data["order"] = "
		order by
			t.lang_code,
			t.temple_num desc";
		}
		else {
			$data["order"] = "
		order by
			t.lang_code,
			t.temple_num";
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			t." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			t.temple_id,
			r.relation_id,
			" . $sql_field . "
			t.update_date as up_date
		from
			" . TBL_HEAD . "temple t
		left join
			" . TBL_HEAD . "movie_user r
		on
			t.temple_num = r.temple_num
		and
			r.user_id = " . escape_sql($data["user_id"]) . "
		and
			r.movie_num = 1
		and
			r.mode_flag = 2
		where
			t.temple_id is not null " . $sql_search . "
		" . $data["order"] . "
		" . $data["limit"];

		$item = get_array($dbh, $sql);
		$this->list = $item;

		//最大件数取得
		$sql = "
		select
			count(*) as cnt
		from
			" . TBL_HEAD . "temple t
		where
			t.temple_id is not null " . $sql_search . "
		";

		$item = get_array($dbh, $sql);
		$this->max = $item[0]["cnt"];

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//動画URL取得
	function get_movie_url($request_data, $movie_num = 1) {
		global $lang_ary;
		$data = $request_data;

		$url = HTTP_ROOT . $lang_ary[$data["lang_code"]] . "/temples/movie/index.html?lang=" . $lang_ary[$data["lang_code"]];
		$url .= "&temple=" . $data["temple_num"] . "&num=" . $movie_num;

		return $url;
	}

	//動画URLからIDを取得
	function get_movie_id($url) {
		// 正規表現パターン: embed/ の後ろにある11文字（英数、ハイフン、アンダースコア）をキャプチャ
		$pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';

		if (preg_match($pattern, $url, $matches)) {
		    return $matches[1]; // インデックス1に動画IDが入る
		}

		return null; // 見つからなかった場合
	}

	//Google Map URL取得
	function get_map_path($request_data) {
		global $lang_ary;
		$data = $request_data;
		$lang_code = get_lang_code();

		$url = "https://www.google.com/maps?output=embed";
		$url .= "&q=" . $data["pref"] . $data["address"];
		$url .= "&hl=" . $lang_ary[$lang_code];

		return $url;
	}

	//サムネイル画像パス取得
	function get_image_path($id) {
		$file_root = TEMPLE_IMAGE_ROOT . sprintf('%02d', $id) . IMAGE_FILE_EXT;
		$file_name = TEMPLE_IMAGE_PATH . sprintf('%02d', $id) . IMAGE_FILE_EXT;
		if (!is_file($file_root)) {
			$file_name = $this->no_img;
		}
		return $file_name;
	}

	//Lサイズ画像パス取得
	function get_lsize_path($id) {
		$file_root = TEMPLE_LSIZE_ROOT . sprintf('%02d', $id) . IMAGE_FILE_EXT;
		$file_name = TEMPLE_LSIZE_PATH . sprintf('%02d', $id) . IMAGE_FILE_EXT;
		if (!is_file($file_root)) {
			$file_name = $this->no_img;
		}
		return $file_name;
	}

	//メンバー変数から画像パス取得
	function get_image_file_name() {
		$data = $this->data;
		$code = $data["temple_id"];
		$img = $code . IMAGE_FILE_EXT;
		$tmp_img = $code . IMAGE_FILE_EXT;
		$img_path = $code;

		if ($data["image_up_flag"]) {
			$this->data["image_file"] = $tmp_img;
			$this->data["image_file_path"] = TMP_FILE_PATH . $tmp_img;
		}
		elseif (is_file(IMAGE_FILE_ROOT . $img)) {
			$this->data["image_flag"] = true;
			$this->data["image_file"] = $img;
			$this->data["image_file_path"] = IMAGE_FILE_PATH . $img;
			$this->data["image_file_tmp"] = TMP_FILE_PATH . $tmp_img;
			$this->data["image_path"] = IMAGE_FILE_PATH . $img_path;
			$this->data["image_ext"] = IMAGE_FILE_EXT;
		}
		elseif (is_file(IMAGE_FILE_ROOT . $img_old)) {
			$this->data["image_flag"] = true;
			$this->data["image_file_tmp"] = TMP_FILE_PATH . $tmp_img;
			$this->data["image_path"] = IMAGE_FILE_PATH . $img_path;
			$this->data["image_ext"] = IMAGE_FILE_EXT;
		}
		else {
			$this->data["image_flag"] = false;
			$this->data["image_file"] = "";
			$this->data["image_file_path"] = $this->no_img;
		}

		//画像追加
		for ($i = 1; $i <= 10; $i++) {
			$img = $code . "_" . $i . IMAGE_FILE_EXT;
			$tmp_img = $code . "_" . $i . IMAGE_FILE_EXT;

			if ($data["image_up_flag" . $i]) {
				if (is_file(TMP_FILE_ROOT . $tmp_img)) {
					$this->data["image_flag" . $i] = true;
					$this->data["image_file" . $i] = $tmp_img;
					$this->data["image_file_path" . $i] = TMP_FILE_PATH . $tmp_img;
				}
				elseif (is_file(IMAGE_FILE_ROOT . $img)) {
					$this->data["image_flag" . $i] = true;
					$this->data["image_file" . $i] = $img;
					$this->data["image_file_path" . $i] = IMAGE_FILE_PATH . $img;
				}
			}
			elseif (is_file(IMAGE_FILE_ROOT . $img)) {
				$this->data["image_flag" . $i] = true;
				$this->data["image_file" . $i] = $img;
				$this->data["image_file_path" . $i] = IMAGE_FILE_PATH . $img;
				$this->data["image_file_tmp" . $i] = TMP_FILE_PATH . $tmp_img;
			}
			elseif (is_file(IMAGE_FILE_ROOT . $img_old)) {
				$this->data["image_flag" . $i] = true;
				$this->data["image_file_tmp" . $i] = TMP_FILE_PATH . $tmp_img;
			}
			else {
				$this->data["image_flag" . $i] = false;
				$this->data["image_file" . $i] = "";
				$this->data["image_file_path" . $i] = $this->no_img;
			}
		}
	}

	//エラーチェック
	function check_error($request_data, $db_handle = null) {
		global $info_ary, $info_str;
		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$data = $request_data;

		if (!isset($data["temple_num"]) || !$data["temple_num"]) {
			$this->error_message .= "札所Noを入力してください。<br>\n";
		}
		elseif (!check_number($data["temple_num"])) {
			$this->error_message .= "札所Noは半角数値で入力してください。<br>\n";
		}
		elseif ($data["temple_num"] < 0 || 88 < $data["temple_num"]) {
			$this->error_message .= "札所Noは1から88までで入力してください。<br>\n";
		}
		elseif (!isset($data["csv_flag"]) || !$data["csv_flag"]) {
			//重複チェック
			$search = array();
			$search["lang_code"] = $data["lang_code"];
			$search["temple_num"] = $data["temple_num"];
			$this->get_data($search, $dbh);
			$item = $this->data;
			if (count_ary($item)) {
				if (!isset($data["temple_id"])) {
					$this->error_message .= "札所Noがすでに登録されています。<br>\n";
				}
				elseif ($item["temple_id"] !== $data["temple_id"]) {
					$this->error_message .= "札所Noがすでに登録されています。<br>\n";
				}
			}
		}

		if (!isset($data["temple_name"]) || !$data["temple_name"]) {
			$this->error_message .= "寺院名を入力してください。<br>\n";
		}

		for ($i = 1; $i < count_ary($info_str); $i++) {
			if ($data[$info_str[$i]] && !preg_match("/^[0-9;]+$/", $data[$info_str[$i]])) {
				$this->error_message .= $info_ary[$i] . "は数値と;(セミコロン)のみで入力してください。<br>\n";
			}
		}

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		if ($this->error_message) {
			return false;
		}
		return true;
	}

	//データ更新
	function set_data($request_data, $dbh) {
		$data = $request_data;

		if (!isset($data["temple_id"]) || !$data["temple_id"]) {
			//登録
			$sql_field = "";
			$sql_field2 = "";
			for ($i = 0; $i < count_ary($this->field_ary); $i++) {
				$sql_field .= $this->field_ary[$i] . ",
				";
				$sql_field2 .= escape_sql($data[$this->field_ary[$i]]) . ",
				";
			}

			//データ登録
			$sql = "
			insert into " . TBL_HEAD . "temple (
				" . $sql_field . "
				insert_date,
				update_date
			) values (
				" . $sql_field2 . "
				now(),
				now()
			)";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
		}
		else {
			$sql_field = "";
			for ($i = 0; $i < count_ary($this->field_ary); $i++) {
				$sql_field .= $this->field_ary[$i] . " = " . escape_sql($data[$this->field_ary[$i]]) . ",
				";
			}

			//データ更新
			$sql = "
			update 
				" . TBL_HEAD . "temple
			set
				" . $sql_field . $sql_pass . "
				update_date = now()
			where
				temple_id = " . escape_sql($data["temple_id"]) . "
			";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
		}

		return true;
	}

	//CSVエラーチェック
	function check_upload_error($csv_data) {
		$list = $csv_data;

		for ($i = 1; $i < count_ary($list); $i++) {
			$list[$i]["csv_flag"] = true;

			if (!$this->check_error($list[$i])) {
				$this->error_message = $i . "行目 : <br>\n" . $this->error_message;
				return false;
			}
		}

		return true;
	}

	//CSVアップロード処理
	function upload_data($csv_data, $dbh) {
		$data = $csv_data;
		$error_message = "";

		for ($h = 1; $h < count_ary($data); $h++) {
			//重複しているIDを取得
			$search = array();
			$search["lang_code"] = $data[$h]["lang_code"];
			$search["temple_num"] = $data[$h]["temple_num"];
			$this->get_data($search, $dbh);
			$temple = $this->data;

			if (count_ary($temple)) {
				$data[$h]["temple_id"] = $temple["temple_id"];
			}
			$this->set_data($data[$h], $dbh);
		}

		return true;
	}

	//データ削除
	function delete_data($request_data, $dbh) {
		$data = $request_data;
		if (!$data["id"]) {
			$this->error = "id is null.";
			return false;
		}

		//データ削除
		$sql = "
		delete from
			" . TBL_HEAD . "temple
		where
			temple_id = " . escape_sql($data["id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		return true;
	}


	//画像エラーチェック
	function check_file_error($request_data) {
		$data = $request_data;

		$file_name = $_FILES["file_name"]["name"];

		if (!$file_name) {
			$this->error_message .= "ファイルを選択してください。<br>\n";
		}
		elseif ($file_name && $_FILES["file_name"]["size"] == 0) {
			$this->error_message .= "ファイルが開けませんでした。<br>\n";
		}

		if ($this->error_message) {
			return false;
		}
		return true;
	}

	//一時画像保存
	function set_tmp_file($request_data) {
		$data = $request_data;
		$code = $data["temple_num"];

		// 保存先のファイル名
		$file_directory = TMP_FILE_ROOT;
		$tmp_file = $file_directory . $code . IMAGE_FILE_EXT;

		if (!$this->error_message && is_uploaded_file($_FILES["image_file"]["tmp_name"])) {
			if (is_file($tmp_file)) {
				unlink($tmp_file);
			}

			if (!copy($_FILES["image_file"]["tmp_name"], $tmp_file)) {
				$this->error_message .= $tmp_file . "ファイルのアップロードに失敗しました。<br>\n";
			}
		}

		//画像追加
		for ($i = 1; $i <= 10; $i++) {
			if (!$this->error_message && isset($_FILES["image_file" . $i]["tmp_name"]) && is_uploaded_file($_FILES["image_file" . $i]["tmp_name"])) {
				$tmp_file = $file_directory . $code . "_" . $i . IMAGE_FILE_EXT;
				if (is_file($tmp_file)) {
					unlink($tmp_file);
				}

				if (!copy($_FILES["image_file" . $i]["tmp_name"], $tmp_file)) {
					$this->error_message .= "ファイルのアップロードに失敗しました。<br>\n";
				}
			}
		}

		if ($this->error_message) {
			return false;
		}

		return true;
	}

	//画像保存
	function set_file($request_data) {
		$data = $request_data;
		$code = $data["temple_num"];

		// 保存先のファイル名
		$file_directory = IMAGE_FILE_ROOT;
		$file = $file_directory . $code . IMAGE_FILE_EXT;
		$tmp_file = TMP_FILE_ROOT . $code . IMAGE_FILE_EXT;

		if (is_file($tmp_file)) {
			if (is_file($file)) {
				unlink($file);
			}

			if (!copy($tmp_file, $file)) {
				$this->error_message .= "ファイルのアップロードに失敗しました。<br>\n";
			}
		}

		//画像追加
		for ($i = 1; $i <= 10; $i++) {
			$file = $file_directory . $code . "_" . $i . IMAGE_FILE_EXT;
			$tmp_file = TMP_FILE_ROOT . $code . "_" . $i . IMAGE_FILE_EXT;

			if (is_file($tmp_file)) {
				if (is_file($file)) {
					unlink($file);
				}

				if (!copy($tmp_file, $file)) {
					$this->error_message .= "ファイルのアップロードに失敗しました。<br>\n";
				}
			}
		}

		if ($this->error_message) {
			return false;
		}

		$this->delete_tmp_file($data);
		return true;
	}

	//画像一時ファイル削除
	function delete_tmp_file($request_data) {
		$data = $request_data;
		$code = $data["temple_num"];

		// 保存先のファイル名
		$file_directory = TMP_FILE_ROOT;
		$tmp_file = $file_directory . $code . IMAGE_FILE_EXT;

		if (is_file($tmp_file)) {
			unlink($tmp_file);
		}
		else {
			$this->error_message = "ファイルが見つかりませんでした。";
		}

		//画像追加
		for ($i = 1; $i <= 10; $i++) {
			$tmp_file = $file_directory . $code . "_" . $i . IMAGE_FILE_EXT;

			if (is_file($tmp_file)) {
				unlink($tmp_file);
			}
			else {
				$this->error_message = "ファイルが見つかりませんでした。";
			}
		}

		if ($this->error_message) {
			return false;
		}

		return true;
	}

	//全画像削除
	function delete_file($request_data) {
		$data = $request_data;
		$code = $data["temple_num"];

		// 保存先のファイル名
		$file_directory = IMAGE_FILE_ROOT;
		$tmp_file = $file_directory . $code . IMAGE_FILE_EXT;
		if (is_file($tmp_file)) {
			unlink($tmp_file);
		}
		else {
			$this->error_message .= "ファイルが見つかりませんでした。";
		}

		//画像追加
		for ($i = 1; $i <= 10; $i++) {
			$tmp_file = $file_directory . $code . "_" . $i . IMAGE_FILE_EXT;
			if (is_file($tmp_file)) {
				unlink($tmp_file);
			}
			else {
				$this->error_message .= "ファイルが見つかりませんでした。";
			}
		}

		if ($this->error_message) {
			return false;
		}

		return true;
	}

	//画像を1つ削除
	function delete_image($request_data) {
		$data = $request_data;
		$code = $data["temple_num"];

		// 保存先のファイル名
		$file_directory = IMAGE_FILE_ROOT;

		//画像判定
		$tmp_file = $file_directory . $code . IMAGE_FILE_EXT;

		if (is_file($tmp_file)) {
			unlink($tmp_file);
		}

		if ($this->error_message) {
			return false;
		}

		return true;
	}

}
?>