<?php
/*-----------------------------------------------------------------------------
件名：CSV情報クラス
画面：CSV関連
機能：CSV管理
-----------------------------------------------------------------------------*/

class class_csv  {
	var $data;
	var $list;
	var $max;
	var $error_message;

	function class_csv() {
		$this->__construct();
	}

	function __construct() {
	}

	//CSVデータ取得
	function get_list($file, $convert = "") {
		$data = $request_data;

		$csv_data = array();
		mb_language("Japanese");

		$f = fopen($file, "r");

//なぜかlocalhost:8000ではうまく読み込めない
//		while($tmp = fgetcsv($f)){
		while ($line = fgets($f)) {
			$tmp = string_to_array(",", $line);
			if ($convert) {
				$tmp = mb_convert_change($tmp, $convert, "SJIS");
			}
			array_push($csv_data, $tmp);
		}
		fclose($f);

		$this->list = $csv_data;
		$this->max = count_ary($csv_data);
		return true;
	}

	//連想配列キーをセットする
	function set_keys($keys) {
		if (!is_array($keys)) {
			return false;
		}

		$list = $this->list;
		$csv_data = array();

		for ($row = 0; $row < count_ary($list); $row++) {
			foreach($keys as $key => $value){
				if ($list[$row][$key]) {
					$csv_data[$row][$value] = trim($list[$row][$key]);
				}
			}
		}

		$this->list = $csv_data;
	}

	//言語コードをセットする
	function set_lang($request_data) {
		$data = $request_data;
		$csv_data = $this->list;

		for ($row = 0; $row < count_ary($csv_data); $row++) {
			$csv_data[$row]["lang_code"] = $data["lang_code"];
			$csv_data[$row]["mode_flag"] = $data["mode_flag"];
		}

		$this->list = $csv_data;
	}

}
?>