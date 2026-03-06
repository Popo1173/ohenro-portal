<?php
/*-----------------------------------------------------------------------------
件名：お知らせクラス
画面：お知らせ情報関連
機能：お知らせ情報管理
-----------------------------------------------------------------------------*/

class class_notice {
	var $data;
	var $list;
	var $max;
	var $no_img;
	var $field_ary;
	var $error_message;

	function class_notice() {
		$this->__construct();
	}

	function __construct() {
		//データベースのフィールド
		$this->field_ary = array(
			"lang_code",
			"top_flag",
			"mypage_flag",
			"title",
			"detail",
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
			n.notice_id = " . escape_sql($data["id"]);
		}
		if ($data["notice_id"]) {
			$sql_search .= "
		and
			n.notice_id = " . escape_sql($data["notice_id"]);
		}
		if (isset($data["lang_code"]) && string_length($data["lang_code"])) {
			$sql_search .= "
		and
			n.lang_code = " . escape_sql($data["lang_code"]);
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			n." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			n.notice_id,
			" . $sql_field . "
			update_date as up_date
		from
			" . TBL_HEAD . "notice n
		where
			n.notice_id is not null " . $sql_search . "
		";

		$item = get_array($dbh, $sql);

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		if (!count_ary($item)) {
			return false;
		}

		$this->data = $item[0];
		$this->get_image_file_name();

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
					$sql_field .= "ifnull(n." . $value . ", ''), ' '";
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
			n.lang_code = " . escape_sql($data["lang_code"]);
		}
		if (isset($data["top_flag"]) && $data["top_flag"]) {
			$sql_search .= "
		and
			n.top_flag = " . escape_sql($data["top_flag"]);
		}
		if (isset($data["mypage_flag"]) && $data["mypage_flag"]) {
			$sql_search .= "
		and
			n.mypage_flag = " . escape_sql($data["mypage_flag"]);
		}

		if ($data["order_id"] === "desc") {
			$data["order"] = "
		order by
			n.lang_code,
			n.insert_date desc";
		}
		else {
			$data["order"] = "
		order by
			n.lang_code,
			n.insert_date";
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			n." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			n.notice_id,
			" . $sql_field . "
			date_format(n.update_date, '%Y/%m/%d') as up_date
		from
			" . TBL_HEAD . "notice n
		where
			n.notice_id is not null " . $sql_search . $data["search"] . "
		" . $data["order"] . "
		" . $data["limit"];

		$item = get_array($dbh, $sql);
		$this->list = $item;

		//最大件数取得
		$sql = "
		select
			count(*) as cnt
		from
			" . TBL_HEAD . "notice n
		where
			n.notice_id is not null " . $sql_search . $data["search"] . "
		";

		$item = get_array($dbh, $sql);
		$this->max = $item[0]["cnt"];

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//表示画面文字列取得
	function get_disp_str($request_data) {
		$data = $request_data;
		if (!$data) {
			$data = $this->data;
		}

		$str = "";
		if ($data["top_flag"]) {
			$str .= "トップページ<br>";
		}
		if ($data["mypage_flag"]) {
			$str .= "マイページ<br>";
		}

		return $str;
	}

	//画像パス取得
	function get_image_path($id) {
		$file_root = IMAGE_FILE_ROOT . $id . IMAGE_FILE_EXT;
		$file_name = IMAGE_FILE_PATH . $id . IMAGE_FILE_EXT;
		if (!is_file($file_root)) {
			$file_name = $this->no_img;
		}
		return $file_name;
	}

	//メンバー変数から画像パス取得
	function get_image_file_name() {
		$data = $this->data;
		$code = $data["notice_id"];
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
		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$data = $request_data;

		if (!isset($data["title"]) || !$data["title"]) {
			$this->error_message .= "タイトルを入力してください。<br>\n";
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

		if (!isset($data["notice_id"]) || !$data["notice_id"]) {
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
			insert into " . TBL_HEAD . "notice (
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
				" . TBL_HEAD . "notice
			set
				" . $sql_field . $sql_pass . "
				update_date = now()
			where
				notice_id = " . escape_sql($data["notice_id"]) . "
			";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
		}

		//画像
		$this->set_file($data);

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
			" . TBL_HEAD . "notice
		where
			notice_id = " . escape_sql($data["id"]) . "
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
		$code = $data["notice_id"];

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
		$code = $data["notice_id"];

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
		$code = $data["notice_id"];

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
		$code = $data["notice_id"];

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
		$code = $data["notice_id"];

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