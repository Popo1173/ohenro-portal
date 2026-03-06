<?php
/*-----------------------------------------------------------------------------
件名：情報クラス
画面：宿、タクシー、旅館インタビュー情報関連
機能：宿、タクシー、旅館インタビュー情報管理
-----------------------------------------------------------------------------*/

class class_info  {
	var $data;
	var $list;
	var $max;
	var $field_ary;
	var $error_message;
	var $no_img;

	function class_info() {
		$this->__construct();
	}

	function __construct() {
		//データベースのフィールド
		$this->field_ary = array(
			"lang_code",
			"mode_flag",
			"info_num",
			"info_name",
			"pref",
			"address",
			"tel",
			"access",
			"comment",
			"url",
			"status",
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

		$sql_search = "";

		if ($data["id"]) {
			$sql_search .= "
		and
			i.info_id = " . escape_sql($data["id"]);
		}
		if ($data["info_id"]) {
			$sql_search .= "
		and
			i.info_id = " . escape_sql($data["info_id"]);
		}
		if ($data["info_num"]) {
			$sql_search .= "
		and
			i.info_num = " . escape_sql($data["info_num"]);
		}
		if (isset($data["lang_code"]) && string_length($data["lang_code"])) {
			$sql_search .= "
		and
			i.lang_code = " . escape_sql($data["lang_code"]);
		}
		if ($data["mode_flag"]) {
			$sql_search .= "
		and
			i.mode_flag = " . escape_sql($data["mode_flag"]);
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			i." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			i.info_id,
			" . $sql_field . "
			update_date as up_date
		from
			" . TBL_HEAD . "info i
		where
			i.info_id is not null " . $sql_search . "
		";

		$item = get_array($dbh, $sql);
		$this->data = $item[0];

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
				if ($value == "lang_code") {
					continue;
				}
				else {
					if ($i) {
						$sql_field .= ", ";
					}
					$sql_field .= "ifnull(i." . $value . ", ''), ' '";
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
			i.lang_code = " . escape_sql($data["lang_code"]);
		}
		if ($data["mode_flag"]) {
			$sql_search .= "
		and
			i.mode_flag = " . escape_sql($data["mode_flag"]);
		}

		if ($data["order_id"] === "desc") {
			$data["order"] = "
		order by
			i.lang_code,
			i.info_num desc";
		}
		else {
			$data["order"] = "
		order by
			i.lang_code,
			i.info_num";
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			i." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			i.info_id,
			" . $sql_field . "
			update_date as up_date
		from
			" . TBL_HEAD . "info i
		where
			i.info_id is not null " . $sql_search . $data["search"] . "
		" . $data["order"] . "
		" . $data["limit"];

		$item = get_array($dbh, $sql);
		$this->list = $item;

		//最大件数取得
		$sql = "
		select
			count(*) as cnt
		from
			" . TBL_HEAD . "info i
		where
			i.info_id is not null " . $sql_search . $data["search"] . "
		";

		$item = get_array($dbh, $sql);
		$this->max = $item[0]["cnt"];

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//画像パス取得
	function get_image_path($id) {
		$file_root = INFO_IMAGE_ROOT . "interview/" . sprintf('%02d', $id) . IMAGE_FILE_EXT;
		$file_name = INFO_IMAGE_PATH . "interview/" . sprintf('%02d', $id) . IMAGE_FILE_EXT;
		if (!is_file($file_root)) {
			$file_name = $this->no_img;
		}
		return $file_name;
	}

	//メンバー変数から画像パス取得
	function get_image_file_name() {
		$data = $this->data;
		$code = $data["info_id"];
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
		global $info_ary;
		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$data = $request_data;

		if (!isset($data["info_num"]) || !$data["info_num"]) {
			$this->error_message .= $info_ary[$data["mode_flag"]] . "IDを入力してください。<br>\n";
		}
		elseif (!check_number($data["info_num"])) {
			$this->error_message .= "IDは半角数値で入力してください。<br>\n";
		}
		elseif (!isset($data["csv_flag"]) || !$data["csv_flag"]) {
			//重複チェック
			$search = array();
			$search["lang_code"] = $data["lang_code"];
			$search["mode_flag"] = $data["mode_flag"];
			$search["info_num"] = $data["info_num"];
			$this->get_data($search, $dbh);
			$item = $this->data;
			if (count_ary($item)) {
				if (!isset($data["info_id"])) {
					$this->error_message .= $info_ary[$data["mode_flag"]] . "IDがすでに登録されています。<br>\n";
				}
				elseif ($item["info_id"] !== $data["info_id"]) {
					$this->error_message .= $info_ary[$data["mode_flag"]] . "IDがすでに登録されています。<br>\n";
				}
			}
		}

		if (!isset($data["info_name"]) || !$data["info_name"]) {
			$this->error_message .= $info_ary[$data["mode_flag"]] . "名称を入力してください。<br>\n";
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

		if (!isset($data["info_id"]) || !$data["info_id"]) {
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
			insert into " . TBL_HEAD . "info (
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
				" . TBL_HEAD . "info
			set
				" . $sql_field . $sql_pass . "
				update_date = now()
			where
				info_id = " . escape_sql($data["info_id"]) . "
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
			$search["mode_flag"] = $data[$h]["mode_flag"];
			$search["info_num"] = $data[$h]["info_num"];
			$this->get_data($search, $dbh);

			$info = $this->data;
			if (count_ary($info)) {
				$data[$h]["info_id"] = $info["info_id"];
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
			" . TBL_HEAD . "info
		where
			info_id = " . escape_sql($data["id"]) . "
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
		$code = $data["info_num"];

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
		$code = $data["info_num"];

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
		$code = $data["info_num"];

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
		$code = $data["info_num"];

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
		$code = $data["info_num"];

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