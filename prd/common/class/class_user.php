<?php
/*-----------------------------------------------------------------------------
件名：会員情報クラス
画面：お遍路サイト会員関連
機能：お遍路サイト会員管理
-----------------------------------------------------------------------------*/

class class_user  {
	var $class_name;
	var $field_ary;
	var $personal_ary;
	var $class_ary;
	var $error_code;
	var $data;
	var $list;
	var $max;
	var $error_message;
	var $movie_list;
	var $movie_max;
	var $round_list;
	var $round_max;

	function class_user() {
		$this->__construct();
	}

	function __construct() {
		$this->class_name = "user";
		$this->field_ary = array(
			//"lang_code",
			"family_name",
			"first_name",
			"family_kana",
			"first_kana",
			"email",
			//"password",
			"country",
			"age",
			"mail_flag",
		);

		$this->personal_ary = array(
			"family_name",
			"first_name",
			"family_kana",
			"first_kana",
			"email",
			//"password",
			"country",
			"age",
		);

		$this->error_code = " is-error-message";
	}

	//詳細データ取得
	function get_data($request_data = "", $db_handle = null) {
		global $lang_ary;
		$data = $request_data;

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$sql_relation = "";
		$sql_search = "";

		if (isset($data[$this->class_name . "_id"]) && $data[$this->class_name . "_id"]) {
			$sql_search .= "
		and
			u." . $this->class_name . "_id = " . escape_sql($data[$this->class_name . "_id"]);
		}
		if (isset($data["id"]) && $data["id"]) {
			$sql_search .= "
		and
			u." . $this->class_name . "_id = " . escape_sql($data["id"]);
		}
		if (isset($data["s"]) && $data["s"]) {
			$sql_search .= "
		and
			u.security_code = " . escape_sql($data["s"]);
		}
		if ((isset($data["email"]) && $data["email"]) || (isset($data["check_login"]) && $data["check_login"])) {
			$sql_search .= "
		and
			u.email = " . escape_sql(encode_pass($data["email"]));
		}
		if (isset($data["temp_id"]) && $data["temp_id"]) {
			$sql_search .= "
		and
			u.temp_id = " . escape_sql($data["temp_id"]);
		}
		if (isset($data["round_id"]) && $data["round_id"]) {
			$sql_search .= "
		and
			r.round_id = " . escape_sql($data["round_id"]);
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			u." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			u.lang_code,
			u." . $this->class_name . "_id," . $sql_field . "
			u.password,
			u.mail_flag,
			count(r.round_id) as ohenro_num,
			u.temp_id,
			u.security_code,
			date_format(u.insert_date, '%Y/%c/%e') as insert_date,
			date_format(u.update_date, '%Y/%c/%e') as update_date
		from
			" . TBL_HEAD . $this->class_name . " u
		left join
			" . TBL_HEAD . "user_round r
		on
			u.user_id = r.user_id
		where
			u." . $this->class_name . "_id is not null " . $sql_search . "
		group by
			u.user_id
		";

		$item = get_array($dbh, $sql);
		$data = $item[0];

		//個人データの復号化
		for ($i = 0; $i < count_ary($this->personal_ary); $i++) {
			$data[$this->personal_ary[$i]] = decode_pass($data[$this->personal_ary[$i]]);
		}

		//アンケート回答取得
		$data = $this->get_user_ans($data, $dbh);

		//姓名
		$data["user_name"] = $data["first_name"] . " " . $data["family_name"];
		if ($data["lang_code"] == array_search('ja', $lang_ary) || 
			$data["lang_code"] == array_search('zh-CN', $lang_ary) ||
			$data["lang_code"] == array_search('zh-TW', $lang_ary)) {
			$data["user_name"] = $data["family_name"] . " " . $data["first_name"];
		}
		if (string_length($data["temp_id"])) {
//			$data["family_name"] = "(仮登録)";
		}

		$this->data = $data;

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

		$sql_search = $data["search"];

		//検索
		if (isset($data["lang_code"]) && string_length($data["lang_code"])) {
			$sql_search .= "
			and
				u.lang_code = " . escape_sql($data["lang_code"]);
		}
		if (isset($data["family_name"]) && $data["family_name"]) {
			$sql_search .= "
			and
				u.family_name like '%" . escape_sql(encode_pass($data["family_name"]), false) . "%'";
		}
		if (isset($data["first_name"]) && $data["first_name"]) {
			$sql_search .= "
			and
				u.first_name like '%" . escape_sql(encode_pass($data["first_name"]), false) . "%'";
		}
		if (isset($data["country"]) && $data["country"]) {
			$sql_search .= "
			and
				u.country like '%" . escape_sql(encode_pass($data["country"]), false) . "%'";
		}
		if (isset($data["age"]) && $data["age"]) {
			$sql_search .= "
			and
				u.age like '%" . escape_sql(encode_pass($data["age"]), false) . "%'";
		}
		if (isset($data["email"]) && $data["email"]) {
			$sql_search .= "
			and
				u.email like '%" . escape_sql(encode_pass($data["email"]), false) . "%'";
		}
		if (isset($data["mail_flag"]) && string_length($data["mail_flag"])) {
			$sql_search .= "
			and
				u.mail_flag = " . escape_sql($data["mail_flag"]);
		}

		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			$sql_field .= "
			u." . $this->field_ary[$i] . ",";
		}

		//データ取得
		$sql = "
		select
			u.lang_code,
			u." . $this->class_name . "_id," . $sql_field . "
			u.mail_flag,
			u.temp_id,
			u.insert_date,
			u.update_date
		from
			" . TBL_HEAD . $this->class_name . " u
		where
			u." . $this->class_name . "_id is not null " . $sql_search . "
		order by
			u.insert_date desc
		" . $data["limit"];

		$item = get_array($dbh, $sql);

		//個人データの復号化
		for ($h = 0; $h < count_ary($item); $h++) {
			for ($i = 0; $i < count_ary($this->personal_ary); $i++) {
				$item[$h][$this->personal_ary[$i]] = decode_pass($item[$h][$this->personal_ary[$i]]);
				if (string_length($item[$h]["temp_id"])) {
					$item[$h]["family_name"] = "(仮登録)";
				}
			}
		}

		$this->list = $item;

		//最大件数取得
		$sql = "
		select
			count(*) as cnt
		from
			" . TBL_HEAD . $this->class_name . " u
		where
			u." . $this->class_name . "_id is not null " . $sql_search . "
		";

		$item = get_array($dbh, $sql);

		$this->max = $item[0]["cnt"];

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//ログイン認証
	function check_login($request_data, $db_handle) {
		$data = $request_data;

		if (!$data["email"] || !$data["password"]) {
			return false;
		}

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$search = array();
		$search["email"] = $data["email"];
		$search["check_login"] = "1";
		$this->get_data($search, $dbh);
		$login = $this->data;

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		if (!isset($login)) {
			return false;
		}

		//パスワード暗号化
		$encoded_pass = encode_pass($data["password"]);

		if ($login["password"] != $encoded_pass && $login["temp_pass"] != $encoded_pass) {
			return false;
		}

		//ログイン処理
		$_SESSION["login"]["login_flag"] = true;
		$_SESSION["login_info_user"] = array();
		$_SESSION["login_info_user"] = $login;
		$this->login = $login;

		//認証成功
		return true;
	}

	//エラーチェック
	function check_error($request_data, $db_handle = null) {
		global $lang_ary, $user_keys, $user_header;

		$data = $request_data;
		$message = array();
		$message["lang_code"] = $data["lang_code"];

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$this->check_mail($data, $dbh);
		$this->check_pass($data, $dbh);
		$this->check_name($data, $dbh);
/*
		$this->class_ary["country"] = "";
		if (!isset($data["country"]) || !$data["country"]) {
			$this->class_ary["country"] = $this->error_code;
			//$this->class_ary["country_error"] = "「居住国」を選択してください。";
			$message["message_num"] = "E03003";
			$this->class_ary["country_error"] = get_lang_message($message);
			$this->error_message .= $this->class_ary["country_error"] . "<br>\n";
		}
*/
		$this->class_ary["age"] = "";
		if (!isset($data["age"]) || !$data["age"]) {
			$this->class_ary["age"] = $this->error_code;
			//$this->class_ary["age_error"] = "「年齢層」を選択してください。";
			$message["message_num"] = "E03004";
			$this->class_ary["age_error"] = get_lang_message($message);
			$this->error_message .= $this->class_ary["age_error"] . "<br>\n";
		}

		if ($data["temp_id"]) {
			$this->check_agree($data, $dbh);
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

	//姓名チェック
	function check_name($request_data, $dbh) {
		$data = $request_data;
		$message = array();
		$message["lang_code"] = $data["lang_code"];

		$this->class_ary["family_name"] = "";
		if (!isset($data["family_name"]) || !$data["family_name"]) {
			$this->class_ary["family_name"] = $this->error_code;
			//$this->error_message .= "「姓」を入力してください。";
			$message["message_num"] = "E03001";
			$this->class_ary["family_name_error"] .= get_lang_message($message);
			$this->error_message .= $this->class_ary["family_name_error"] . "<br>\n";
		}
		$this->class_ary["first_name"] = "";
		if (!isset($data["first_name"]) || !$data["first_name"]) {
			$this->class_ary["first_name"] = $this->error_code;
			//$this->error_message .= "「名」を入力してください。";
			$message["message_num"] = "E03002";
			$this->class_ary["first_name_error"] .= get_lang_message($message);
			$this->error_message .= $this->class_ary["first_name_error"] . "<br>\n";
		}

		if ($data["lang_code"] == array_search('ja', $lang_ary)) {
			$this->class_ary["family_kana"] = "";
			if (!isset($data["family_kana"]) || !$data["family_kana"]) {
				$this->class_ary["family_kana"] = $this->error_code;
				$this->class_ary["family_kana_error"] .= "「セイ」を入力してください。";
				$this->error_message .= $this->class_ary["family_kana_error"] . "<br>\n";
			}
			$this->class_ary["first_kana"] = "";
			if (!isset($data["first_kana"]) || !$data["first_kana"]) {
				$this->class_ary["first_kana"] = $this->error_code;
				$this->class_ary["first_kana_error"] .= "「メイ」を入力してください。";
				$this->error_message .= $this->class_ary["first_kana_error"] . "<br>\n";
			}
		}

		if ($this->error_message) {
			return false;
		}

		return true;
	}

	//メールアドレスチェック
	function check_mail($request_data, $dbh) {
		global $lang_ary, $user_keys, $user_header;
		$data = $request_data;
		$message = array();
		$message["lang_code"] = $data["lang_code"];

		if ($data["user_id"]) {
			$this->get_data($data, $dbh);
		}

		$this->class_ary["email"] = "";
		if (!isset($data["email"]) || !$data["email"]) {
			$this->class_ary["email"] = $this->error_code;
			$message["message_num"] = "E02001";
			$this->class_ary["email_error"] = get_lang_message($message);
			//$this->class_ary["email_error"] = "「メールアドレス」を入力してください。";
			$this->error_message .= $this->class_ary["email_error"] . "<br>\n";
		}
		elseif (!check_email($data["email"])) {
			$this->class_ary["email"] = $this->error_code;
			$message["message_num"] = "E02002";
			$this->class_ary["email_error"] = get_lang_message($message);
			//$this->class_ary["email_error"] = "「メールアドレス」を正しく入力してください。";
			$this->error_message .= $this->class_ary["email_error"] . "<br>\n";
		}
		elseif (isset($data["double_email_flag"]) && ($data["email"] != $data["email2"])) {
			$this->class_ary["email2"] = " is-invalid";
			$this->error_message .= "「メールアドレス」が一致しません。<br>\n";
		}
		else {
			$this->class_ary["email2"] = "";
		}

		if (!$this->error_message && isset($data["double_check_flag"])) {
			//重複チェック
			$sql2 = "";
			if ($data["user_id"]) {
				$sql2 .= "
				and
					user_id <> " . escape_sql($data["user_id"]);
			}

			$sql = "
				select
					user_id
				from
					" . TBL_HEAD . "user
				where
					email = " . escape_sql(encode_pass($data["email"])) . $sql2 . "
			";

			$item = get_array($dbh, $sql);

			if (count_ary($item)) {
				$this->class_ary["email"] = $this->error_code;
				$message["message_num"] = "E02003";
				$this->class_ary["email_error"] = get_lang_message($message);
				//$this->class_ary["email_error"] = "そのメールアドレスはすでに登録されております。別のメールアドレスを指定してください。";
				$this->error_message .= $this->class_ary["email_error"] . "<br>\n";
			}
		}

		if ($this->error_message) {
			return false;
		}

		return true;
	}

	//パスワードチェック
	function check_pass($request_data, $dbh) {
		$data = $request_data;
		$message = array();
		$message["lang_code"] = $data["lang_code"];

		$check_pass_flag = true;
		if ($data["temp_id"]) {
		}
		elseif ($data["user_id"] && !$data["password"]) {
			$check_pass_flag = false;
		}

		if ($check_pass_flag) {
			$this->class_ary["password"] = "";
			$this->class_ary["password2"] = "";
			if (!isset($data["password"]) || !$data["password"]) {
				$this->class_ary["password"] = $this->error_code;
				$message["message_num"] = "E03005";
				$this->class_ary["password_error"] = get_lang_message($message);
				//$this->class_ary["password_error"] = "「パスワード」を入力してください。";
				$this->error_message .= $this->class_ary["password_error"] . "<br>\n";
			}
			elseif (!check_alpha($data["password"])) {
				$this->class_ary["password"] = $this->error_code;
				$message["message_num"] = "E03006";
				$this->class_ary["password_error"] = get_lang_message($message);
				//$this->class_ary["password_error"] = "「パスワード」は半角英数字で入力してください。";
				$this->error_message .= $this->class_ary["password_error"] . "<br>\n";
			}
			elseif (string_length($data["password"]) < 8 || 20 < string_length($data["password"]) ) {
				$this->class_ary["password"] = $this->error_code;
				$message["message_num"] = "E03007";
				$this->class_ary["password_error"] = get_lang_message($message);
				//$this->class_ary["password_error"] = "「パスワード」は8文字以上20文字以下で入力してください。";
				$this->error_message .= $this->class_ary["password_error"] . "<br>\n";
			}
			elseif (isset($data["double_pass_flag"]) && ($data["password"] != $data["password2"])) {
				$this->class_ary["password2"] = $this->error_code;
				$message["message_num"] = "E04001";
				$this->class_ary["password2_error"] = get_lang_message($message);
				//$this->class_ary["password2_error"] .= "「パスワード」が一致しません。";
				$this->error_message .= $this->class_ary["password2_error"] . "<br>\n";
			}
		}

		if ($this->error_message) {
			return false;
		}
		return true;
	}

	//利用規約同意チェック
	function check_agree($request_data, $dbh) {
		$data = $request_data;
		$message = array();
		$message["lang_code"] = $data["lang_code"];

		$this->class_ary["agree"] = "";
		if (!isset($data["agree"]) || !$data["agree"]) {
			$this->class_ary["agree"] = $this->error_code;
			$message["message_num"] = "E05002";
			$this->class_ary["agree_error"] .= get_lang_message($message);
			//$this->error_message .= "「利用規約／個人情報保護方針」に同意されましたらチェックをしてください。";
			$this->error_message .= $this->class_ary["agree_error"] . "<br>\n";
		}

		if ($this->error_message) {
			return false;
		}
		return true;
	}

	//データ更新
	function set_data($request_data, $dbh) {
		global $lang_ary;

		$data = $request_data;
		if (!isset($data["lang_code"])) {
			$data["lang_code"] = 0;
		}

		$message = array();
		$message["lang_code"] = get_en_code($data["lang_code"]);

		//メール作成クラス
		require_once(CLASS_PATH . "class_mail.php");

		// メールクラス生成
		$obj_mail = new class_mail();

		if (isset($data["admin_flag"]) && !$data["user_id"]) {
			//登録
			$sql_field = "";
			$sql_field2 = "";
			for ($i = 0; $i < count_ary($this->field_ary); $i++) {
				$sql_field .= $this->field_ary[$i] . ",
				";
				//個人情報は暗号化して登録する
				if (in_array($this->field_ary[$i], $this->personal_ary)) {
					$sql_field2 .= escape_sql(encode_pass($data[$this->field_ary[$i]])) . ",
				";
				}
				else {
					$sql_field2 .= escape_sql($data[$this->field_ary[$i]]) . ",
				";
				}
			}

			//データ登録
			$sql = "
			insert into " . TBL_HEAD . $this->class_name . " (
				" . $sql_field . "
				lang_code,
				password,
				insert_date,
				update_date
			) values (
				" . $sql_field2 . "
				" . escape_sql($data["lang_code"]) . ",
				" . escape_sql(encode_pass($data["password"])) . ",
				now(),
				now()
			)";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);

			//インサートしたIDを取得
			$sql = "
			select
				last_insert_id() as next_id
			from
				" . TBL_HEAD . $this->class_name . "
			";

			$item = get_array($dbh, $sql);
			$next_id = $item[0]["next_id"];
			$code = md5($next_id);
			$code = md5($code . time());

			//セキュリティコード更新
			$sql = "
			update 
				" . TBL_HEAD . "user
			set
				security_code = " . escape_sql($code) . "
			where
				user_id = " . escape_sql($next_id) . "
			";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);

			return true;
		}

		//会員情報変更
		$sql_field = "";
		for ($i = 0; $i < count_ary($this->field_ary); $i++) {
			//個人情報は暗号化して登録する
			if (in_array($this->field_ary[$i], $this->personal_ary)) {
				$sql_field .= $this->field_ary[$i] . " = " . escape_sql(encode_pass($data[$this->field_ary[$i]])) . ",
			";
			}
			else {
				$sql_field .= $this->field_ary[$i] . " = " . escape_sql($data[$this->field_ary[$i]]) . ",
			";
			}
		}

		if (isset($data["admin_flag"])) {
			$sql_field .= "lang_code = " . escape_sql($data["lang_code"]) . ",
			";
		}
		if (isset($data["password"]) && $data["password"]) {
			$sql_field .= "password = " . escape_sql(encode_pass($data["password"])) . ",
			";
		}

		$this->get_data($data, $dbh);
		$user = $this->data;

		//データ更新
		$sql = "
		update 
			" . TBL_HEAD . $this->class_name . "
		set
			" . $sql_field . "
			temp_id = null,
			update_date = now()
		where
			" . $this->class_name . "_id = " . escape_sql($data[$this->class_name . "_id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		if ($data["temp_id"]) {
/*
			//アンケート回答データを一旦削除
			$sql = "
			delete from
				" . TBL_HEAD . "user_ans
			where
				user_id = " . escape_sql($data["user_id"]) . "
			";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
*/
			//アンケート登録
			for ($i = 0; $i < ENQ_NUM; $i++) {
				$key = "q" . sprintf("%d", $i+1);
				if (!isset($data[$key]) || !string_length($data[$key])) {
					continue;
				}

				$multi_flag = 0;
				if ($data["enq"][$key]["value"]) {
					$multi_flag = $data["enq"][$key]["multi_flag"];
					$data[$key] = $data["enq"][$key]["value"];
				}
				elseif (is_array($data[$key])) {
					$multi_flag = 1;
					$data[$key] = array_to_string(",", $data[$key]);
				}
/*
				elseif ($data[$key]["multi_flag"]) {
					$multi_flag = $data[$key]["multi_flag"];
				}
*/
				//アンケート回答登録
				$sql = "
				insert into " . TBL_HEAD . "user_ans (
					user_id,
					question,
					multi_flag,
					ans_text,
					ans_date
				) values (
					" . escape_sql($data["user_id"]) . ",
					" . escape_sql($key) . ",
					" . escape_sql($multi_flag) . ",
					" . escape_sql($data[$key]) . ",
					now()
				)";

				//テーブル更新
				$rs = trans_exec($dbh, $sql);

				$other = $key . "_other";
				if (isset($data[$other]) && $data[$other]) {
					//その他テキストボックス
					$key = $other;

					//アンケート回答登録
					$sql = "
					insert into " . TBL_HEAD . "user_ans (
						user_id,
						question,
						multi_flag,
						ans_text,
						ans_date
					) values (
						" . escape_sql($data["user_id"]) . ",
						" . escape_sql($key) . ",
						" . escape_sql($multi_flag) . ",
						" . escape_sql($data[$key]) . ",
						now()
					)";

					//テーブル更新
					$rs = trans_exec($dbh, $sql);

				}
			}
		}

		if (!isset($data["admin_flag"]) && $data["temp_id"]) {
			//メール送信
			// 件名
			//$obj_mail->set_subject("ご登録ありがとうございます");
			$message["message_num"] = "S01002";
			$subject = get_lang_message($message);
			$obj_mail->set_subject($subject);

			// メール本文
			if (!$obj_mail->set_mail("register.txt", MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/")) {
				$this->error_message .= MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/register.txt ファイルオープンに失敗しました。\n";
				print_access_log($this->error_message);
				return false;
			}

			$ary = array();
			$ary["url"] = URL_STRING . $lang_ary[$data["lang_code"]] . "/my-page/index" . EXT;
			$obj_mail->set_data($ary);

			// ユーザメール送信
			if ($data["email"] && !$obj_mail->send_mail($data["email"], WEB_MAIL)) {
				$this->error_message .= "会員本登録 : メール送信に失敗しました。\n";
				print_access_log($this->error_message);
				return false;
			}
		}
		elseif (!isset($data["admin_flag"])) {
			//メール送信
			// 件名
			//$obj_mail->set_subject("ご登録変更");
			$message["message_num"] = "S01003";
			$subject = get_lang_message($message);
			$obj_mail->set_subject($subject);

			// メール本文
			if (!$obj_mail->set_mail("change.txt", MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/")) {
				$this->error_message .= MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/change.txt ファイルオープンに失敗しました。\n";
				print_access_log($this->error_message);
				return false;
			}

			$ary = array();
			$ary["url"] = URL_STRING . $lang_ary[$data["lang_code"]] . "/my-page/index" . EXT;
			$obj_mail->set_data($ary);

			// ユーザメール送信
			if ($data["email"] && !$obj_mail->send_mail($data["email"], WEB_MAIL)) {
				$this->error_message .= "会員情報変更 : メール送信に失敗しました。\n";
				print_access_log($this->error_message);
				return false;
			}
		}

		return true;
	}

	//識別ID取得
	function get_temp_id($dbh) {
		$temp_id = "";
		while (1) {
			$temp_id = auto_password(32, 2);

			//一時データが重複していないかをチェック
			$search = array();
			$search["temp_id"] = $temp_id;

			$res = $this->get_data($search, $dbh);
			if (!count_ary($res)) {
				break;
			}
		}

		return $temp_id;
	}

	//会員メールアドレス登録
	function set_mail_data($request_data, $dbh) {
		global $lang_ary;
		$data = $request_data;

		//仮ID取得
		$temp_id = $this->get_temp_id($dbh);

		//データ登録
		$sql = "
		insert into " . TBL_HEAD . "user (
			lang_code,
			email,
			temp_id,
			mail_flag,
			insert_date,
			update_date
		) values (
			" . escape_sql($data["lang_code"]) . ",
			" . escape_sql(encode_pass($data["email"])) . ",
			" . escape_sql($temp_id) . ",
			0,
			now(),
			now()
		)";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		//インサートしたIDを取得
		$sql = "
		select
			last_insert_id() as next_id
		from
			" . TBL_HEAD . "user
		";

		$item = get_array($dbh, $sql);
		$next_id = $item[0]["next_id"];
		$code = md5($next_id);
		$code = md5($code . time());

		//セキュリティコード更新
		$sql = "
		update 
			" . TBL_HEAD . "user
		set
			security_code = " . escape_sql($code) . "
		where
			user_id = " . escape_sql($next_id) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		//メール作成クラス
		require_once(CLASS_PATH . "class_mail.php");

		// メールクラス生成
		$obj_mail = new class_mail();

		// 件名
		//$obj_mail->set_subject("仮登録完了");
		$message = array();
		$message["lang_code"] = get_en_code($data["lang_code"]);
		$message["message_num"] = "S01001";
		$subject = get_lang_message($message);
		$obj_mail->set_subject($subject);

		// メール本文
		if (!$obj_mail->set_mail("user_mail.txt", MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/")) {
			$this->error_message .= MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/user_mail.txt ファイルオープンに失敗しました。\n";
			print_access_log($this->error_message);
			return false;
		}

		$ary = array();
		$ary["url"] = URL_STRING . $lang_ary[$data["lang_code"]] . "/signup/form/index" . EXT . "?temp_id=" . $temp_id;
		$obj_mail->set_data($ary);

		// ユーザメール送信
		if ($data["email"] && !$obj_mail->send_mail($data["email"], WEB_MAIL)) {
			$this->error_message .= "仮登録 : メール送信に失敗しました。\n";
			print_access_log($this->error_message);
			return false;
		}

		if ($this->error_message) {
			return false;
		}

		return true;
	}

	//パスワード再設定メール送信
	function send_password($request_data, $dbh) {
		global $lang_ary;
		$data = $request_data;

		if (!$this->check_mail($data, $dbh)) {
			return false;
		}

		$this->get_data($data, $dbh);
		$user = $this->data;

		if (!count_ary($user)) {
			$this->error_message .= "パスワード再設定 : ユーザー取得エラー。<br>\n";
			print_access_log($this->error_message);
			return false;
		}

		//メール作成クラス
		require_once(CLASS_PATH . "class_mail.php");

		// メールクラス生成
		$obj_mail = new class_mail();

		// 件名
		//$obj_mail->set_subject("パスワード再設定");
		$message = array();
		$message["lang_code"] = get_en_code($data["lang_code"]);
		$message["message_num"] = "S01004";
		$subject = get_lang_message($message);
		$obj_mail->set_subject($subject);

		// メール本文
		if (!$obj_mail->set_mail("password.txt", MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/")) {
			$this->error_message .= MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/password.txt ファイルオープンに失敗しました。\n";
			print_access_log($this->error_message);
			return false;
		}

		$ary = array();
		$ary["url"] = URL_STRING . $lang_ary[$data["lang_code"]] . "/password-reset/new/index" . EXT . "?s=" . $user["security_code"];
		$obj_mail->set_data($ary);

		// ユーザメール送信
		if ($user["email"] && !$obj_mail->send_mail($user["email"], WEB_MAIL)) {
			$this->error_message .= "パスワード再設定 : メール送信に失敗しました。\n";
			print_access_log($this->error_message);
			return false;
		}

		return true;
	}

	//パスワード再設定
	function reset_password($request_data, $dbh) {
		$data = $request_data;

		$this->check_pass($data, $dbh);

		if ($this->error_message) {
			return false;
		}

		//データ更新
		$sql = "
		update 
			" . TBL_HEAD . "user
		set
			password = " . escape_sql(encode_pass($data["password"])) . ",
			update_date = now()
		where
			user_id = " . escape_sql($data["user_id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		return true;
	}

	//メールアドレス登録後の有効期限切れを削除
	function delete_tmp_user($dbh) {
		//データ削除
		$sql = "
		delete from
			" . TBL_HEAD . "user
		where
			char_length(temp_id) > 0
		and
			insert_date < " . escape_sql(date("Y-m-d H:i:00", mktime(date("H")-24, date("i"), 0, date("m"), date("d"), date("Y"))));

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		return true;
	}

	//データ削除
	function delete_data($request_data, $dbh) {
		global $lang_ary;
		$data = $request_data;

		if (!$data["user_id"]) {
			$this->error = "id is null.";
			return false;
		}

		//アンケート回答削除
		$sql = "
		delete from
			" . TBL_HEAD . "user_ans
		where
			" . $this->class_name ."_id = " . escape_sql($data[$this->class_name ."_id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		//視聴履歴、お気に入り削除
		$sql = "
		delete from
			" . TBL_HEAD . "movie_user
		where
			" . $this->class_name ."_id = " . escape_sql($data[$this->class_name ."_id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		//お遍路周回情報削除
		$sql = "
		delete from
			" . TBL_HEAD . "user_round
		where
			" . $this->class_name ."_id = " . escape_sql($data[$this->class_name ."_id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		//ユーザーデータ削除
		$sql = "
		delete from
			" . TBL_HEAD . $this->class_name . "
		where
			" . $this->class_name ."_id = " . escape_sql($data[$this->class_name ."_id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		if (!isset($data["admin_flag"])) {
			//メール作成クラス
			require_once(CLASS_PATH . "class_mail.php");

			// メールクラス生成
			$obj_mail = new class_mail();

			// 件名
			//$obj_mail->set_subject("会員情報を削除いたしました");
			$message = array();
			$message["lang_code"] = get_en_code($data["lang_code"]);
			$message["message_num"] = "S01005";
			$subject = get_lang_message($message);
			$obj_mail->set_subject($subject);

			// メール本文
			if (!$obj_mail->set_mail("cancel.txt", MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/")) {
				$this->error_message .= MAIL_ROOT . $lang_ary[$message["lang_code"]] . "/cancel.txt ファイルオープンに失敗しました。\n";
				print_access_log($this->error_message);
				return false;
			}

			// ユーザメール送信
			if ($data["email"] && !$obj_mail->send_mail($data["email"], WEB_MAIL)) {
				$this->error_message .= "会員退会 : メール送信に失敗しました。\n";
				print_access_log($this->error_message);
				return false;
			}
		}

		return true;
	}

	//ステータス変更
	function change_status($request_data, $dbh) {
		$data = $request_data;

		$sql_field = "";
		if (isset($data["lang_code"]) && string_length($data["lang_code"])) {
			$sql_field .= "lang_code = " . escape_sql($data["lang_code"]) . ",
			";
		}
		if (isset($data["mail_flag"]) && string_length($data["mail_flag"])) {
			$sql_field .= "mail_flag = " . escape_sql($data["mail_flag"]) . ",
			";
		}

		//データ更新
		$sql = "
		update 
			" . TBL_HEAD . $this->class_name . "
		set
			" . $sql_field . "
			update_date = now()
		where
			" . $this->class_name . "_id = " . escape_sql($data[$this->class_name . "_id"]) . "
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		return true;
	}

	//アンケート回答取得
	function get_user_ans($request_data = "", $db_handle = null) {
		$data = $request_data;

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$sql = "
		select
			a.question,
			a.multi_flag,
			a.ans_text,
			a.ans_date
		from
			" . TBL_HEAD . "user_ans a
		where
			a.user_id = " . escape_sql($data["user_id"]) . "
		order by
			a.question
		";

		$item = get_array($dbh, $sql);

		$tmp_ary = array();
		$str = "";
		for ($i = 0; $i < count_ary($item); $i++) {
			if ($str) {
				$str .= "、";
			}
			$str .= $item[$i]["question"] . ":" . $item[$i]["ans_text"];
			$tmp_ary[$i] = $str;
		}

		$data["answer_ary"] = $item;
		$data["answer_log"] .= $str;

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return $data;
	}

	//賞状表示情報取得
	function get_award($request_data = "", $db_handle = null) {
		if (!$data["user_id"] || !$data["round_id"]) {
			return false;
		}

		//データ取得
		$sql = "
		select
			u.family_name,
			u.first_name,
			date_format(r.insert_date, '%Y年%c月%e日') as round_date
		from
			" . TBL_HEAD . $this->class_name . " u
		inner join
			" . TBL_HEAD . "user_round r
		on
			u.user_id = r.user_id
		where
			u." . $this->class_name . "_id is not null " . $sql_search . "
		and
			r.round_id = " . escape_sql($data["round_id"]) . "
		";

		$item = get_array($dbh, $sql);
		return $item[0];
	}

	//お遍路達成日一覧取得
	function get_round_list($request_data = "", $db_handle = null) {
		$data = $request_data;

		if (!$data["user_id"]) {
			return false;
		}

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		//データ取得
		$sql = "
		select
			r.round_id,
			r.insert_date
		from
			" . TBL_HEAD . "user_round r
		where
			r.round_id is not null " . $sql_search . "
		and
			r.user_id = " . escape_sql($data["user_id"]) . "
		order by
			r.insert_date
		" . $data["limit"];

		$item = get_array($dbh, $sql);
		$this->round_list = $item;

		//最大件数取得
		$sql = "
		select
			count(*) as cnt
		from
			" . TBL_HEAD . "user_round r
		where
			r.round_id is not null " . $sql_search . "
		and
			r.user_id = " . escape_sql($data["user_id"]) . "
		";

		$item = get_array($dbh, $sql);
		$this->round_max = $item[0]["cnt"];

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//視聴履歴、お気に入り一覧取得
	function get_movie_list($request_data = "", $db_handle = null) {
		$data = $request_data;

		if (!$data["user_id"]) {
			return false;
		}

		$dbh = $db_handle;
		if (!$db_handle) {
			//データベースオープン
			$dbh = db_open();
		}

		$sql_search = $data["search"];
		if (!$data["mode_flag"]) {
			$data["mode_flag"] = 1;
		}

		if (isset($data["pref"]) && $data["pref"]) {
			$sql_search .= "
		and
			t.pref = " . escape_sql($data["pref"]);
		}

		if ($data["mode_flag"] == 1) {
			//データ取得
			$sql = "
			select
				r.relation_id,
				r.movie_num,
				t.temple_id,
				t.lang_code,
				t.temple_num,
				t.mountain,
				t.mount_kana,
				t.infirmary,
				t.infir_kana,
				t.temple_name,
				t.temple_kana,
				t.pref,
				t.movie_detail1,
				t.movie_detail2,
				t.movie_category
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
				r.mode_flag = " . escape_sql($data["mode_flag"]) . "
			where
				t.temple_id is not null " . $sql_search . "
			and
				t.lang_code = " . escape_sql($data["lang_code"]) . "
			order by
				t.temple_num
			" . $data["limit"];

			$item = get_array($dbh, $sql);
			$this->movie_list = $item;

			//最大件数取得
			$sql = "
			select
				count(*) as cnt
			from
				" . TBL_HEAD . "temple t
			inner join
				" . TBL_HEAD . "movie_user r
			on
				t.temple_num = r.temple_num
			where
				t.temple_id is not null " . $sql_search . "
			and
				r.user_id = " . escape_sql($data["user_id"]) . "
			and
				r.movie_num = 1
			and
				r.mode_flag = " . escape_sql($data["mode_flag"]) . "
			and
				t.lang_code = " . escape_sql($data["lang_code"]) . "
			";

			$item = get_array($dbh, $sql);
			$this->movie_max = $item[0]["cnt"];
		}
		else {
			//データ取得
			$sql = "
			select
				r.relation_id,
				r.movie_num,
				t.temple_id,
				t.lang_code,
				t.temple_num,
				t.mountain,
				t.mount_kana,
				t.infirmary,
				t.infir_kana,
				t.temple_name,
				t.temple_kana,
				t.pref,
				t.movie_detail1,
				t.movie_detail2,
				t.movie_category
			from
				" . TBL_HEAD . "temple t
			inner join
				" . TBL_HEAD . "movie_user r
			on
				t.temple_num = r.temple_num
			where
				t.temple_id is not null " . $sql_search . "
			and
				r.user_id = " . escape_sql($data["user_id"]) . "
			and
				r.movie_num = 1
			and
				r.mode_flag = " . escape_sql($data["mode_flag"]) . "
			and
				t.lang_code = " . escape_sql($data["lang_code"]) . "
			order by
				t.temple_num
			" . $data["limit"];

			$item = get_array($dbh, $sql);
			$this->movie_list = $item;

			//最大件数取得
			$sql = "
			select
				count(*) as cnt
			from
				" . TBL_HEAD . "temple t
			inner join
				" . TBL_HEAD . "movie_user r
			on
				t.temple_num = r.temple_num
			where
				t.temple_id is not null " . $sql_search . "
			and
				r.user_id = " . escape_sql($data["user_id"]) . "
			and
				r.movie_num = 1
			and
				r.mode_flag = " . escape_sql($data["mode_flag"]) . "
			and
				t.lang_code = " . escape_sql($data["lang_code"]) . "
			";

			$item = get_array($dbh, $sql);
			$this->movie_max = $item[0]["cnt"];
		}

		if (!$db_handle) {
			//データベースクローズ
			db_close($dbh);
		}

		return true;
	}

	//視聴履歴、お気に入り登録
	function set_movie_data($request_data, $dbh) {
		$data = $request_data;

		if (!$data["user_id"] || !$data["temple_num"] || !$data["movie_num"] || !$data["mode_flag"]) {
			return false;
		}

		//重複確認
		$sql = "
		select
			relation_id
		from
			" . TBL_HEAD . "movie_user
		where
			user_id = " . escape_sql($data["user_id"]) . "
		and
			temple_num = " . escape_sql($data["temple_num"]) . "
		and
			movie_num = " . escape_sql($data["movie_num"]) . "
		and
			mode_flag = " . escape_sql($data["mode_flag"]) . "
		";

		$item = get_array($dbh, $sql);

		if (!count_ary($item)) {
			//重複がなければデータ登録
			$sql = "
			insert into " . TBL_HEAD . "movie_user (
				lang_code,
				user_id,
				temple_num,
				movie_num,
				mode_flag,
				insert_date,
				update_date
			) values (
				" . escape_sql($data["lang_code"]) . ",
				" . escape_sql($data["user_id"]) . ",
				" . escape_sql($data["temple_num"]) . ",
				" . escape_sql($data["movie_num"]) . ",
				" . escape_sql($data["mode_flag"]) . ",
				now(),
				now()
			)";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
		}

		if ($data["mode_flag"] == 2) {
			return true;
		}

		//お遍路全制覇時の処理
		$sql = "
		select
			count(*) as cnt
		from
			" . TBL_HEAD . "movie_user
		where
			user_id = " . escape_sql($data["user_id"]) . "
		and
			movie_num = 1
		and
			mode_flag = 1
		";

		$item = get_array($dbh, $sql);

		if ($item[0]["cnt"] >= 88) {
			//データ更新
			$sql = "
			update 
				" . TBL_HEAD . "user
			set
				ohenro_num = ohenro_num + 1
			where
				user_id = " . escape_sql($data["user_id"]) . "
			";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);

			//データ更新
			$sql = "
			insert into " . TBL_HEAD . "user_round (
				user_id,
				insert_date
			) values (
				" . escape_sql($data["user_id"]) . ",
				now()
			)";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);

			//視聴履歴をリセット
			$sql = "
			delete from
				" . TBL_HEAD . "movie_user
			where
				user_id = " . escape_sql($data["user_id"]) . "
			and
				movie_num = 1
			and
				mode_flag = 1
			";

			//テーブル更新
			$rs = trans_exec($dbh, $sql);
		}

		return true;
	}

	//お気に入り削除
	function delete_favorite($request_data, $dbh) {
		$data = $request_data;

		if (!$data["user_id"] || !$data["temple_num"] || !$data["movie_num"]) {
			return false;
		}

		//削除処理
		$sql = "
		delete from
			" . TBL_HEAD . "movie_user
		where
			user_id = " . escape_sql($data["user_id"]) . "
		and
			temple_num = " . escape_sql($data["temple_num"]) . "
		and
			movie_num = " . escape_sql($data["movie_num"]) . "
		and
			mode_flag = 2
		";

		//テーブル更新
		$rs = trans_exec($dbh, $sql);

		return true;
	}

}
?>