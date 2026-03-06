<?php
/*-----------------------------------------------------------------------------
件名：担当者情報クラス
画面：担当者関連
機能：担当者管理
-----------------------------------------------------------------------------*/

class class_admin  {
	var $data;
	var $list;
	var $max;
	var $error_message;

	function class_user() {
		$this->__construct();
	}

	function __construct() {
		$this->field_ary = array(
			"login_id",
			"admin_name",
		);
	}

	//詳細データ取得
	function get_data($request_data = "", $db_handle = null) {
		$data = $request_data;

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$sql_search = "";
		if (isset($data["admin_id"]) && $data["admin_id"]) {
			$sql_search .= "
		and
			a.admin_id = " . escape_sql($data["admin_id"]);
		}
		if (isset($data["login_id"]) && $data["login_id"]) {
			$sql_search .= "
		and
			a.login_id = " . escape_sql($data["login_id"]);
		}
		if (isset($data["id"]) && $data["id"]) {
			$sql_search .= "
		and
			a.admin_id = " . escape_sql($data["id"]);
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			a." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			a.admin_id," . $sql_field . "
			a.password,
			date_format(a.insert_date, '%Y/%c/%e') as insert_date,
			date_format(a.update_date, '%Y/%c/%e') as update_date
		from
			" . TBL_HEAD . "admin a
		where
			a.admin_id is not null " . $sql_search . "
		";

		$item = get_array($dbh, $sql);
		$this->data = $item[0];
		$this->data["password"] = decode_pass($this->data["password"]);

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//一覧データ取得
	function get_list($request_data = "", $db_handle = null) {
		$data = $request_data;

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		if (isset($data["search"])) $search = $data["search"];
		if (isset($data["limit"])) $limit = $data["limit"];

		//データ取得
		$sql = "
		select
			a.admin_id,
			a.login_id,
			a.admin_level,
		from
			" . TBL_HEAD . "admin a
		where
			a.admin_id is not null " . $search . "
		order by
			a.admin_id
		" . $limit;

		$item = get_array($dbh, $sql);
		$this->list = $item;

		//最大件数取得
		$sql = "
		select
			count(*) as cnt
		from
			" . TBL_HEAD . "admin
		where
			admin_id is not null " . $search . "
		";

		$item = get_array($dbh, $sql);
		$this->max = $item[0]["cnt"];

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//ログイン処理
	function check_login($request_data, $flag = '') {
		$data = $request_data;

		if (!$data["login_id"] || !$data["password"]) {
			return false;
		}

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		//ログイン認証
		$this->get_data($data, $dbh);
		$login = $this->data;

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		if (!isset($login)) {
			return false;
		}

		//パスワード暗号化
		$encoded_pass = $data["password"];

		if ($login["password"] != $encoded_pass) {
			return false;
		}

		//ログイン処理
		$_SESSION["login"]["login_admin_flag"] = true;
		$_SESSION["login_info"] = $login;
		$this->login = $login;

		//認証成功
		return true;
	}

	//エラーチェック
	function check_error($request_data, $db_handle = null) {
		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$data = $request_data;

		if (!isset($data["login_id"]) || !$data["login_id"]) {
			$this->error_message .= "ログインIDを入力してください。<br>\n";
		}
		elseif (!check_alpha($data["login_id"])) {
			$this->error_message .= "ログインIDは半角英数字で入力してください。<br>\n";
		}
		else {
			//重複チェック
			$sql2 = "";
			if ($data["id"]) {
				$sql2 .= "
				and
					admin_id <> " . escape_sql($data["id"]);
			}

			$sql = "
				select
					count(*) as cnt
				from
					" . TBL_HEAD . "admin
				where
					login_id = " . escape_sql($data["login_id"]) . $sql2 . "
			";

			$item = get_array($dbh, $sql);
			if ($item[0]["cnt"] > 0) {
				$this->error_message .= "ログインIDが他の方と重複しております。別のログインIDを指定してください。<br>\n";
			}
		}
		if (!$data["id"] && (!isset($data["password"]) || !$data["password"])) {
			$this->error_message .= "パスワードを入力してください。<br>\n";
		}
		elseif (!preg_match("/([0-9].*[a-zA-Z]|[a-zA-Z].*[0-9])/", $data["password"])) {
			$this->error_message .= "パスワードを半角数字と半角英数字を混在させて入力してください。<br>\n";
		}
		elseif (string_length($data["password"]) <= 6) {
			$this->error_message .= "パスワードは6文字以上で入力してください。<br>\n";
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
		$data["encode_pass"] = encode_pass($data["password"]);
//		$data["encode_pass"] = $data["password"];

		if (!isset($data["id"]) || !$data["id"]) {
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
			insert into " . TBL_HEAD . "admin (
				" . $sql_field . "
				password,
				insert_date,
				update_date
			) values (
				" . $sql_field2 . "
				" . escape_sql($data["encode_pass"]) . ",
				now(),
				now()
			)";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
		}
		else {
			$sql_pass = "";
			if ($data["password"]) {
				$sql_pass .= "password = " . escape_sql($data["encode_pass"]) . ",
				";
			}

			$sql_field = "";
			for ($i = 0; $i < count_ary($this->field_ary); $i++) {
				$sql_field .= $this->field_ary[$i] . " = " . escape_sql($data[$this->field_ary[$i]]) . ",
				";
			}

			//データ更新
			$sql = "
			update 
				" . TBL_HEAD . "admin
			set
				" . $sql_field . $sql_pass . "
				update_date = now()
			where
				admin_id = " . escape_sql($data["id"]) . "
			";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
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

		//データ更新
		$sql = "
		delete from
			" . TBL_HEAD . "admin
		where
			admin_id = " . escape_sql($data["id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		return true;
	}


}
?>