<?php
//------------------------------------------------------------------------------
// メール本文作成クラス
//------------------------------------------------------------------------------

class class_mail {
	var $subject;
	var $subject_mobile;
	var $body;
	var $mobile;
	var $mobile_list = array(
	'docomo.ne.jp',
	'ezweb.ne.jp',
	'softbank.ne.jp',
	'vodafone.ne.jp',
	'pdx.ne.jp',
	);

	function set_subject($str) {
		$this->subject = $str;
		return true;
	}

	function set_subject_mobile($str) {
		$this->subject_mobile = $str;
		return true;
	}

	function set_mail($filename, $mail_root = "", $encode_from = "") {
		// ファイル存在確認
		$mail_path = $mail_root;
		if (!$mail_path) {
			$mail_path = MAIL_ROOT;
		}
		if (!is_file($mail_path . $filename)) {
			return false;
		}

		// ファイルオープン
		$fh = fopen($mail_path . $filename, 'r');
		if (!$fh) {
			return false;
		}

		// ファイル読み込み
		$mail_body;
		while (!feof($fh)) {
			$mail_body .= fgets($fh);
		}

		// ファイルクローズ
		fclose($fh);

		$data = $_POST;
		foreach ($data as $key => $value) {
			if ($encode_from) {
				$s = $value;
				$s = mb_convert_kana($s, "KV", $encode_from);
				$s = mb_convert_encoding($s, "UTF-8", $encode_from);
				$mail_body = str_replace("#" . $key . "#", $s, $mail_body);
			}
			else {
				$mail_body = str_replace("#" . $key . "#", $value, $mail_body);
			}
		}

		$this->body = $mail_body;
		return true;
	}

	function set_mail_mobile($filename, $encode_from = "") {
		// ファイル存在確認
		if (!is_file(MAIL_ROOT . $filename)) {
			return false;
		}

		// ファイルオープン
		$fh = fopen(MAIL_ROOT . $filename, 'r');
		if (!$fh) {
			return false;
		}

		// ファイル読み込み
		$mail_body;
		while (!feof($fh)) {
			$mail_body .= fgets($fh);
		}

		// ファイルクローズ
		fclose($fh);

		$data = $_POST;
		foreach ($data as $key => $value) {
			if ($encode_from) {
				$s = $value;
				$s = mb_convert_kana($s, "KV", $encode_from);
				$s = mb_convert_encoding($s, "UTF-8", $encode_from);
				$mail_body = str_replace("#" . $key . "#", $s, $mail_body);
			}
			else {
				$mail_body = str_replace("#" . $key . "#", $value, $mail_body);
			}
		}

		$this->mobile = $mail_body;
		return true;
	}

	function set_body($format, $str, $encode_from = "") {
		if ($encode_from) {
			$s = $str;
			$s = mb_convert_kana($s, "KV", $encode_from);
			$s = mb_convert_encoding($s, "UTF-8", $encode_from);
			$this->body = str_replace("#" . $format . "#", $s, $this->body);
		}
		else {
			$this->body = str_replace("#" . $format . "#", $str, $this->body);
		}
		if ($this->mobile) {
			if ($encode_from) {
				$s = $str;
				$s = mb_convert_kana($s, "KV", $encode_from);
				$s = mb_convert_encoding($s, "UTF-8", $encode_from);
				$this->mobile = str_replace("#" . $format . "#", $s, $this->mobile);
			}
			else {
				$this->mobile = str_replace("#" . $format . "#", $str, $this->mobile);
			}
		}
		return true;
	}

	function set_data($ary, $encode_from = "") {
		foreach ($ary as $key => $value) {
			if ($encode_from) {
				$s = $value;
				$s = mb_convert_kana($s, "KV", $encode_from);
				$s = mb_convert_encoding($s, "UTF-8", $encode_from);
				$this->body = str_replace("#" . $key . "#", $s, $this->body);
			}
			else {
				$this->body = str_replace("#" . $key . "#", $value, $this->body);
			}
			if ($this->mobile) {
				if ($encode_from) {
					$s = $value;
					$s = mb_convert_kana($s, "KV", $encode_from);
					$s = mb_convert_encoding($s, "UTF-8", $encode_from);
					$this->mobile = str_replace("#" . $key . "#", $s, $this->mobile);
				}
				else {
					$this->mobile = str_replace("#" . $key . "#", $value, $this->mobile);
				}
			}
		}
	}

	function send_mail($to, $from, $file = array()) {
		global $lang_ary;

		$sub = $this->subject;
		$sub_mobile = $this->subject_mobile;
		if (!$sub_mobile) {
			$sub_mobile = $sub;
		}

		$header = "";
		$body = "";
		$mobile = "";

		if (count_ary($file)) {
			//添付がある場合
			$header .= "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";

			$body .= "--__BOUNDARY__\n";
			$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
			$body .= $this->body . "\n";
			$body .= "--__BOUNDARY__\n";

			$mobile .= "--__BOUNDARY__\n";
			$mobile .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
			$mobile .= $this->mobile . "\n";
			$mobile .= "--__BOUNDARY__\n";

			for ($i = 0; $i < count_ary($file); $i++) {
				$file_name = get_file_name($file[$i]);
				$body .= "Content-Type: application/octet-stream; name=\"" . $file_name . "\"\n";
				$body .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\n";
				$body .= "Content-Transfer-Encoding: base64\n\n";
				$body .= chunk_split(base64_encode(file_get_contents($file[$i])));
				$body .= "--__BOUNDARY__\n";

				$mobile .= "Content-Type: application/octet-stream; name=\"" .$file_name . "\"\n";
				$mobile .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\n";
				$mobile .= "Content-Transfer-Encoding: base64\n\n";
				$mobile .= chunk_split(base64_encode(file_get_contents($file[$i])));
				$mobile .= "--__BOUNDARY__\n";
			}
		}
		else {
			$body = $this->body;
			$mobile = $this->mobile;
			$header .= "MIME-Version: 1.0\n";
			$header .= "Content-type: text/plain; charset=UTF-8\n";
			$header .= "Content-Transfer-Encoding: 8bit\n";
		}

		//登録解除ヘッダー
		$lang = $lang_ary[get_lang_code()];
		$url = URL_STRING . $lang . "/login/index.html?url=/" . $lang . "/signup/form/index.html";
		$header .= "List-Unsubscribe-Post: List-Unsubscribe=One-Click\n";
		$header .= "List-Unsubscribe: <" . $url . ">\n";

		$header .= "From: " . $from;

		if (!$this->mobile && DEV_FLAG) {
			$log_string = "To: ". $to . "\nSubject: " . $sub . "\n" . $header . "\n\n" . $body;
			print_access_log($log_string);
		}
		if ($sub) {
			$sub = "=?UTF-8?B?" . base64_encode($sub) . "?=";
		}

		if ($this->mobile && DEV_FLAG) {
			$log_string = "To: ". $to . "\nSubject: " . $sub_mobile . "\n" . $header . "\n\n" . $mobile;
			print_access_log($log_string);
		}
		if ($sub_mobile) {
			$sub_mobile = "=?ISO-2022-JP?B?" . base64_encode($sub_mobile) . "?=";
		}

		if ($this->mobile) {
			$send_to = $to;
			$send_to = str_replace(" ", "", $send_to);
			$send_to = str_replace("'", "", $send_to);
			$send_to = str_replace('"', '', $send_to);

			$s = "";
			if (strpos($send_to, ",") !== false) {
				$s = ",";
			}
			elseif (strpos($send_to, ";") !== false) {
				$s = ";";
			}
			if ($s) {
				$list = explode($s, $send_to);
				for ($i = 0; $i < count_ary($list); $i++) {
					$mobile_flag = false;
					for ($j = 0; $j < count_ary($this->mobile_list); $j++) {
						if (strpos(strtolower($list[$i]), $this->mobile_list[$j]) !== false) {
							$mobile_flag = true;
							break;
						}
					}
					if ($mobile_flag) {
						if (!DEV_FLAG && !mail($list[$i], $sub_mobile, $mobile, $header, "-f " . WEB_MAIL)) {
							return false;
						}
					}
					else {
						if (!DEV_FLAG && !mail($list[$i], $sub, $body, $header, "-f " . WEB_MAIL)) {
							return false;
						}
					}
				}
			}
			else {
				$mobile_flag = false;
				for ($j = 0; $j < count_ary($this->mobile_list); $j++) {
					if (strpos(strtolower($send_to), $this->mobile_list[$j]) !== false) {
						$mobile_flag = true;
						break;
					}
				}
				if ($mobile_flag) {
					if (!DEV_FLAG && !mail($send_to, $sub_mobile, $mobile, $header, "-f " . WEB_MAIL)) {
						return false;
					}
				}
				else {
					if (!DEV_FLAG && !mail($send_to, $sub, $body, $header, "-f " . WEB_MAIL)) {
						return false;
					}
				}
			}
		}
		else {
			if (!DEV_FLAG && !mail($to, $sub, $body, $header, "-f " . WEB_MAIL)) {
				return false;
			}
		}

		return true;
	}

}
?>
