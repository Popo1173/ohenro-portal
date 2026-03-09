<?php

//------------------------------------------------------------------------------
//データベースオープン関数
//引数 : なし
//戻り値 : データベースハンドル
function db_open()
{
	if (DB_FLAG == "1") {
		// データベース接続
		$str = "";
		if (DB_HOST) {
			$str .= " host=" . DB_HOST;
		}
		if (DB_PORT) {
			$str .= " port=" . DB_PORT;
		}
		if (DB_NAME) {
			$str .= " dbname=" . DB_NAME;
		}
		if (DB_USER) {
			$str .= " user=" . DB_USER;
		}
		if (DB_PASS) {
			$str .= " password=" . DB_PASS;
		}
		$dbh = pg_connect($str);

		//DBの接続に失敗した場合はエラー表示をおこない処理中断
		if ($dbh == False) {
		    system_error("can not connect db\b");
		}
	}
	elseif (DB_FLAG == "2") {
		//データベース接続
		if (DB_PORT) {
			$dbh = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
		}
		else {
			$dbh = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		}

		//DBの接続に失敗した場合はエラー表示をおこない処理中断
		if ($dbh == False) {
			system_error("can not connect db\n");
		}

		//データベース選択
		if (!(mysqli_select_db($dbh, DB_NAME)))
		{
			system_error("Failed connect");
		}

		//文字コードセット
		if (DB_CHAR) {
			mysqli_set_charset($dbh, DB_CHAR);
		}
	}
	elseif (DB_FLAG == "3") {
		//データベース接続
		$dbh = sqlite_open(DB_NAME, 0666, $sqliteerror);

		//DBの接続に失敗した場合はエラー表示をおこない処理中断
		if ($dbh == False) {
			system_error("can not connect db\n" . $sqliteerror);
		}
	}
	elseif (DB_FLAG == "4") {
		try {
			//データベース接続
			$dbh = new SQLite3(DB_NAME);
		} catch (Exception $e) {
			//DBの接続に失敗した場合はエラー表示をおこない処理中断
			system_error("can not connect db\n" . $e->getTraceAsString());
		}
	}

	//データベースハンドルを返す
	return $dbh;
}

//------------------------------------------------------------------------------
//データベースクローズ関数
//引数 : データベースハンドル
//戻り値 : なし
function db_close($dbh)
{
	if (DB_FLAG == "1") {
		//データベースクローズ
		pg_close($dbh);
	}
	elseif (DB_FLAG == "2") {
		//テーブルロック解除
		$sql = "unlock tables";
		trans_exec($dbh, $sql);

		//データベースクローズ
		mysqli_close($dbh);
	}
	elseif (DB_FLAG == "3") {
		//データベースクローズ
		sqlite_close($dbh);
	}
	elseif (DB_FLAG == "4") {
		//データベースクローズ
		$dbh->close();
	}
}

//------------------------------------------------------------------------------
//トランザクションの開始処理を行う
//引数 : データベースハンドル
//戻り値 : なし
function start_trans($dbh)
{
	if (DB_FLAG == "1") {
		//自動トランザクション解除
		$sql = "set AUTOCOMMIT off";
		if (!(pg_query($dbh, $sql)))
		{
			system_error("SQL Failed " . $sql);
		}

		//トランザクション開始
		$sql = "begin";
		if (!(pg_query($dbh, $sql)))
		{
			system_error("SQL Failed " . $sql);
		}
	}
	elseif (DB_FLAG == "2") {
		//自動トランザクション解除
		mysqli_autocommit($dbh, false);

		//トランザクション開始
		$sql = "begin";
		if (!(mysqli_query($dbh, $sql)))
		{
			system_error("SQL Failed " . $sql);
		}
	}
}

//------------------------------------------------------------------------------
//トランザクションの完了処理を行う
//引数 : データベースハンドル
//戻り値 : なし
function end_trans($dbh)
{
	if (DB_FLAG == "1") {
		//トランザクションの完了
		$sql = "commit";
		if (!(pg_query($dbh, $sql)))
		{
			//ロールバック
			rollback_trans($dbh);
			system_error("SQL Failed " . $sql);
		}
	}
	elseif (DB_FLAG == "2") {
		//トランザクションの完了
		$sql = "commit";
		if (!(mysqli_query($dbh, $sql)))
		{
			//ロールバック
			rollback_trans($dbh);
			system_error("SQL Failed " . $sql);
		}
	}
}

//------------------------------------------------------------------------------
//ロールバック処理を行う
//引数 : データベースハンドル
//戻り値 : なし
function rollback_trans($dbh)
{
	if (DB_FLAG == "1") {
		//ロールバック
	    $sql = "rollback";
	    if (!(pg_query($sql, $dbh)))
	    {
			system_error("SQL Failed " . $sql);
		}
	}
	elseif (DB_FLAG == "2") {
		//ロールバック
		$sql = "rollback";
		if (!(mysqli_query($dbh, $sql)))
		{
			system_error("SQL Failed " . $sql);
		}
	}
}

//------------------------------------------------------------------------------
//データベースからテーブル抽出を行う
//引数 : データベースハンドル、SQLクエリー
//戻り値 : 取得結果の配列
function get_array($dbh, $sql)
{
	if (DB_FLAG == "1") {
		//SQL実行
		if (!($rs = pg_query($dbh, $sql)))
		{
			system_error("SQL Failed " . $sql);
		}

		//結果の取得
		$item = array();
		for ($i = 0; $arr = pg_fetch_array($rs); $i++) {
			reset($arr);
			$item[$i] = $arr;
		}
	}
	elseif (DB_FLAG == "2") {
		//SQL実行
		if (!($rs = mysqli_query($dbh, $sql)))
		{
			system_error("SQL Failed " . $sql);
		}

		//結果の取得
		$item = array();
		for ($i = 0; $arr = mysqli_fetch_array($rs); $i++) {
			reset($arr);
			$item[$i] = $arr;
		}
	}
	elseif (DB_FLAG == "3") {
		//SQL実行
		if (!($rs = sqlite_query($dbh, $sql, SQLITE_BOTH, $sqliteerror)))
		{
			system_error("SQL Failed " . $sqliteerror . "\n" . $sql);
		}

		//結果の取得
		$item = array();
		for ($i = 0 ; $i < sqlite_num_rows($rs) ; $i++){
			$item[$i] = sqlite_fetch_array($rs, SQLITE_ASSOC);
		}
	}
	elseif (DB_FLAG == "4") {
		//SQL実行
		if (!($rs = $dbh->query($sql)))
		{
			system_error("SQL Failed " . $sql);
		}

		//結果の取得
		$item = array();
		$i = 0;
		while ($row = $rs->fetchArray()) {
			$item[$i++] = $row;
		}
	}

	if(sizeof($item) > 0){
		return $item;
	} else {
		return null;
	}
}

//------------------------------------------------------------------------------
// 説明：DB更新時クエリーをトランザクション付きで行う
// 引数：データベースコネクション、SQLクエリー文字列、トランザクション管理フラグ
// 戻値：エラーの場合false、成功の場合は、true
function trans_exec($dbh, $sql, $trans = false)
{
	if ($trans) {
		// トランザクション開始
		$trans_flg = start_trans($dbh);
	}

	if (DB_FLAG == "1") {
		$trans_flg = pg_query($dbh, $sql);
	}
	elseif (DB_FLAG == "2") {
		$trans_flg = mysqli_query($dbh, $sql);
	}
	elseif (DB_FLAG == "3") {
		$trans_flg = sqlite_exec($dbh, $sql, $sqliteerror);
	}
	elseif (DB_FLAG == "4") {
		$trans_flg = $dbh->query($sql);
	}

	if (!$trans_flg) {
		// DB更新失敗の場合
		// ロールバック
		if ($trans)
		{
			rollback_trans($dbh);
		}
		system_error("SQL Failed " . $sql . "\n" . $sqliteerror);
	}

	if ($trans) {
		// コミット
		$trans_flg = end_trans($dbh);
	}
	return true;
}

//------------------------------------------------------------------------------
//システムエラー発生時の処理
//引数 : エラーコード
//戻り値 : なし
function system_error($err_code)
{
	print("エラー : " . $err_code);
	die;
}

//------------------------------------------------------------------------------
//stripslashesを再帰的に実行
//stripslashesは、'"\などのエスケープを戻す
//渡す値に配列がある場合はエラーになるので、この関数を呼ぶ
//引数 : 変換する値
//戻り値 : 変換後の値
if (!function_exists("stripslashes_deep")) {
function stripslashes_deep($value)
{
	$value = is_array($value) ?
	array_map('stripslashes_deep', $value) :
	stripslashes($value);

	return $value;
}
}

//------------------------------------------------------------------------------
//アクセスログをファイル出力する
//引数：コメント
//戻り値：成功 true、失敗 false
function print_access_log($comment = "") {
	//ディレクトリ作成
	if (!is_dir(LOG_PATH)) {
		mkdir(LOG_PATH);
	}

	$filename = LOG_PATH . "access_" . date("Ym") . ".log";

	$buf = array(
			$_SERVER["SCRIPT_NAME"],
			$comment,
			$_SERVER["REQUEST_METHOD"],
			$_SERVER["QUERY_STRING"],
			$_SERVER["REMOTE_ADDR"],
			$_SERVER["HTTP_USER_AGENT"],
			$_SERVER["HTTP_REFERER"],
			date("Y/m/d H:i:s")
		);

	$data = join(",", $buf) . "\n";

	if (!is_file($filename)) {
		// 古いデータを削除
		$tmp_date = mktime(0, 0, 0, date("m"), 1, date("Y") - 1);
		$old_file = LOG_PATH . "access_" . date("Ym", $tmp_date) . ".log";
		if (is_file($old_file)) {
			unlink($old_file);
		}

		$buf = array(
				"画面",
				"コメント",
				"リクエスト",
				"クエリー",
				"IPアドレス",
				"エージェント",
				"リファラ",
				"アクセス日時"
			);

		$data = convert_str(join(",", $buf), CHAR_SET) . "\n" . $data;
	}

	if (!$handle = fopen($filename, 'a+')) {
		return false;
	}

	flock($handle, LOCK_EX);

	if (!fwrite($handle, $data)) {
		return false;
	}

	flock($handle, LOCK_UN);
	
	fclose($handle);
	return true;
}

//------------------------------------------------------------------------------
//ログファイルに書き込むを行う(バッチ用)
//引数：ログファイルに書き込むメッセージ
//戻り値：成功 true、失敗 false
function print_batch_log($id, $message = "") {
	//ディレクトリ作成
	if (!is_dir(BATCH_PATH)) {
		mkdir(BATCH_PATH);
	}

	$filename = BATCH_PATH . "batch_" . date("Ym") . ".log";
	$buf = date("Y/m/d H:i:s", time()) . " " . $id . " " . $message . "\n";

	if (!is_file($filename)) {
		// 古いデータを削除
		$tmp_date = mktime(0, 0, 0, date("m")-6, 1, date("Y"));
		$old_file = BATCH_PATH . "batch_" . date("Ym", $tmp_date) . ".log";
		if (is_file($old_file)) {
			unlink($old_file);
		}
	}

	if (!$handle = fopen($filename, 'a+')) {
		print "Cannot open file ($filename)\n";
		return false;
	}

	flock($handle, LOCK_EX);

	if (!fwrite($handle, $buf)) {
		print "Cannot write to file ($filename)\n";
		return false;
	}

	flock($handle, LOCK_UN);
	
	fclose($handle);
	return true;
}


//------------------------------------------------------------------------------
//二重起動防止用ロックファイルのチェック
//ロックされていた場合は1秒毎にリトライし、それでも解除されない場合はfalseを返す
//引数：画面ID、リトライ回数
//戻り値：起動可 true、二重起動 false
function check_double($id, $max_cnt = 10) {
	$filename = BATCH_PATH . $id . ".tmp";

	for ($i = 0; $i < $max_cnt; $i++) {
		if (!file_exists($filename)) {
			return true;
		}
		sleep(1);
	}
	return false;
}

//------------------------------------------------------------------------------
//ロックファイルの作成
//引数：画面ID
//戻り値：成功 true、失敗 false
function lock_file($id) {
	//ディレクトリ作成
	if (!is_dir(BATCH_PATH)) {
		mkdir(BATCH_PATH);
	}

	$filename = BATCH_PATH . $id . ".tmp";

	if (!$handle = fopen($filename, 'w+')) {
		 print "Cannot open file ($filename)\n";
		 return false;
	}

	if (!fwrite($handle, $id)) {
		print "Cannot write to file ($filename)\n";
		return false;
	}
	return true;
}

//------------------------------------------------------------------------------
//ロックファイルの削除
//引数：画面ID
//戻り値：なし
function release_file($id) {
	$filename = BATCH_PATH . $id . ".tmp";

	if (file_exists($filename)) {
		unlink($filename);
	}
}

//------------------------------------------------------------------------------
//PHP 5.4以降対策
if (!function_exists("session_is_registered")) {
	function session_is_registered($str) {
		if (isset($_SESSION[$str])) {
			return true;
		}
		return false;
	}
}

//------------------------------------------------------------------------------
//PHP 5.4以降対策
if (!function_exists("session_unregister")) {
	function session_unregister($str) {
		unset($_SESSION[$str]);
	}
}

//------------------------------------------------------------------------------
//PHP 6以降対策
if (!function_exists("ereg")) {
	function ereg($str1, $str2) {
		$str1 = replace_str("/", "\\/", $str1);
		return preg_match("/" . $str1 . "/", $str2);
	}
}

//------------------------------------------------------------------------------
//PHP 6以降対策
if (!function_exists("eregi")) {
	function eregi($str1, $str2) {
		$str1 = replace_str("/", "\\/", $str1);
		return preg_match("/" . $str1 . "/i", $str2);
	}
}

//------------------------------------------------------------------------------
//PHP 6以降対策
if (!function_exists("ereg_replace")) {
	function ereg_replace($str1, $str2, $str3) {
		$str1 = replace_str("/", "\\/", $str1);
		return preg_replace("/" . $str1 . "/", $str2, $str3);
	}
}

//------------------------------------------------------------------------------
//PHP 6以降対策
if (!function_exists("eregi_replace")) {
	function eregi_replace($str1, $str2, $str3) {
		$str1 = replace_str("/", "\\/", $str1);
		return preg_replace("/" . $str1 . "/i", $str2, $str3);
	}
}

//------------------------------------------------------------------------------
//PHP 7以降対策
function count_ary($ary) {
	if (is_array($ary)) {
		return count($ary);
	}
	return 0;
}

//------------------------------------------------------------------------------
//PHP 7以降対策
function get_number($num) {
	if ($num) {
		return number_format($num);
	}
	return 0;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function replace_str($target, $replace, $str) {
	if ($str) {
		return str_replace($target, $replace, $str);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function convert_str($str, $encoding, $current = "auto") {
	if ($str) {
		return mb_convert_encoding($str, $encoding, $current);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function mb_string_length($str) {
	if ($str !== null) {
		return mb_strlen($str);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function string_length($str) {
	if ($str !== null) {
		return strlen($str);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function mb_str_pos($str, $search) {
	if ($str) {
		return mb_strpos($str, $search);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function str_pos($str, $search) {
	if ($str) {
		return strpos($str, $search);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function mb_str_rpos($str, $search) {
	if ($str) {
		return mb_strrpos($str, $search);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function str_rpos($str, $search) {
	if ($str) {
		return strrpos($str, $search);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function mb_sub_str($str, $start, $length = null) {
	if ($str) {
		return mb_substr($str, $start, $length);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function sub_str($str, $start, $length = null) {
	if ($str) {
		return substr($str, $start, $length);
	}
	return $str;
}

//------------------------------------------------------------------------------
//PHP 8以降対策
function number_round($number, $round) {
	if ($number) {
		return round($number, $round);
	}
	return $number;
}

//------------------------------------------------------------------------------
//implodeのワーニング対策
//引数、戻り値はimplodeと同じ
function array_to_string($splt, $ary) {
	if (!$ary) {
		return "";
	}
	if (is_array($ary)) {
		return implode($splt, $ary);
	}
	return $ary;
}

//------------------------------------------------------------------------------
//explode同等
//引数、戻り値はexplodeと同じ
function string_to_array($splt, $str) {
	if (!$str) {
		return null;
	}
	return explode($splt, $str);
}

//------------------------------------------------------------------------------
//mktimeのエラー対策
//引数、戻り値はmktimeと同じ
function make_time($hour, $min, $sec, $month, $day, $year) {
	$h = intval($hour);
	if (!is_int($h)) {
		$h = 0;
	}
	$m = intval($min);
	if (!is_int($m)) {
		$m = 0;
	}
	$s = intval($sec);
	if (!is_int($s)) {
		$s = 0;
	}
	$mon = intval($month);
	if (!is_int($mon)) {
		$mon = 0;
	}
	$d = intval($day);
	if (!is_int($d)) {
		$d = 0;
	}
	$y = intval($year);
	if (!is_int($y)) {
		$y = 0;
	}

	return mktime($h, $m, $s, $mon, $d, $y);
}


//------------------------------------------------------------------------------
//デバッグ用に変数、配列の内容を出力して終了する(文字化け対策済み)
//引数：表示する変数または配列
//戻り値：なし
function c($str) {
	header('Content-Type: text/html; charset=' . CHAR_SET);
	print_r($str);
	exit;
}

//------------------------------------------------------------------------------
//変数、配列の内容を出力
//引数：表示する変数または配列
//戻り値：なし
function d() {
	echo '<pre style="background:#fff;color:#333;border:1px solid #ccc;margin:2px;padding:4px;font-family:monospace;font-size:12px">';
	foreach (func_get_args() as $v) var_dump($v);
	echo '</pre>';
}

//------------------------------------------------------------------------------
//変数、HTMLエスケープ
//引数：エスケープする文字列
//戻り値：エスケープ後の文字列
function h($str) {
	if(is_array($str)) {
		return array_map( "h",$str );
	}
	else {
		return htmlspecialchars($str, ENT_COMPAT | ENT_HTML401, CHAR_SET);
	}
}

//------------------------------------------------------------------------------
//変数、SQLエスケープ
//引数：エスケープする文字列
//戻り値：エスケープ後の文字列
function e($str) {
	if(is_array($str)) {
		return array_map( "e" , $str );
	}
	else {
		return mysql_real_escape_string( $str );
	}
}

//------------------------------------------------------------------------------
//ハッシュ暗号化
//引数：暗号化する文字列
//戻り値：暗号化後の文字列
function hash_encode($str) {
	return md5(PASS_KEY . md5($str . PASS_KEY));
}

//------------------------------------------------------------------------------
//端末チェック 携帯アクセス時は自動ジャンプ
//引数 : 携帯用URL
//戻り値 : なし
function check_pc($url)
{
	$agent = $_SERVER['HTTP_USER_AGENT']; 
	if (preg_match("/^DoCoMo/", $agent) ||
		preg_match("/^J-PHONE|^Vodafone|^SoftBank/", $agent) ||
		preg_match("/^UP.Browser|^KDDI/", $agent)) {
		header("Location: " . $url);
		exit();
	}
}

//------------------------------------------------------------------------------
//端末チェック 携帯以外のアクセス時は自動ジャンプ
//引数 : PC用URL
//戻り値 : なし
function check_mobile($url)
{
	$agent = $_SERVER['HTTP_USER_AGENT']; 
	if (preg_match("/^DoCoMo/", $agent) ||
		preg_match("/^J-PHONE|^Vodafone|^SoftBank/", $agent) ||
		preg_match("/^UP.Browser|^KDDI/", $agent)) {
	}
	else {
		header("Location: " . $url);
		exit();
	}
}

//------------------------------------------------------------------------------
//スマートフォン判定
//引数 : なし
//戻り値 : なし
function is_sp()
{
	//スマートフォン判定
	$ua = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/iPhone|iPod|Android.*Mobile|Windows.*Phone/", $ua)) {
		return true;
	}

	return false;
}

//------------------------------------------------------------------------------
//配列の文字列を全てSQLエスケープ
//引数 : 変換する配列、シングルクォーテーション付加フラグ、シングルクォーテーション変換フラグ、変換後文字コード
//戻り値 : 変換後の配列
function escape_sqls($ary, $flag = true, $chg_flag = true, $code = "")
{
	$res = array();
	foreach ($ary as $key => $value) {
		$res[$key] = escape_sql($value, $flag, $chg_flag, $code);
	}
	return $res;
}

//------------------------------------------------------------------------------
// パスワード暗号化
// 引数: 暗号化対象文字列
// 戻り値: 暗号化後文字列
function encode_pass($str) {
	if (!$str) {
		return "";
	}

	$len = string_length($str);
	$enc = "";
	$key = PASS_KEY;

	for($i = 0; $i < $len; $i++){
		$asciin = ord($str[$i]);
		$enc .= chr($asciin ^ ord($key[$i]));
	}
	$enc = base64_encode($enc);

	return $enc;
}

//------------------------------------------------------------------------------
// パスワード複号化
// 引数: 複号化対象文字列
// 戻り値: 複号化後文字列
function decode_pass($str) {
	if (!$str) {
		return "";
	}

	$enc = base64_decode($str);
	$plaintext = "";
	$len = string_length($enc);
	$key = PASS_KEY;

	for($i = 0; $i < $len; $i++){
		$asciin = ord($enc[$i]);
		$plaintext .= chr($asciin ^ ord($key[$i]));
	}

	return trim($plaintext);
}

/*=========================================================================
  d_jconv($string,$to_code);
  d_jconv($string,$to_code,$from_code);
  -------------
  文字コード変換

  $to_code : 変換後コード
  $from_code : 変換前コード

          "e" : EUC
          "j" : JIS
          "s" : SJIS
          "u" : UTF-8 (unicode)
          "u7" : UTF-7 (unicode for Mail Subject)
          "u16" : UTF-16 (unicode for PDF)
          "ucs2" : UCS-2 (unicode for PDF JavaScript)
===========================================================================*/
function d_jconv($str,$code,$input_code=""){
   // EUCの「長瀬」という文字列がUTF-8と認識されてしまう可能性がある為，
   // 認識オーダーのUTF-8とEUC-JPを交換する
   mb_detect_order(array("ASCII", "JIS","EUC-JP","SJIS","UTF-8"));

   if($input_code){
     switch($input_code){
       case "e":
         $orig_encoding = "EUC-JP";
         break;
       case "j":
         $orig_encoding = "JIS";
         break;
       case "s":
         $orig_encoding = "SJIS";
         break;
       case "u":
         $orig_encoding = "UTF-8";
         break;
       case "u7":
         $orig_encoding = "UTF-7";
         break;
       case "u16":
         $orig_encoding = "UTF-16";
         break;
       case "ucs2":
         $orig_encoding = "UCS-2";
         break;
     }
   }

   if(!$orig_encoding && is_string($str)){
     if(function_exists("mb_detect_encoding")){
       $orig_encoding = mb_detect_encoding($str);
     } else {
       $orig_encoding = i18n_discover_encoding($str);
     }
   }

   if(function_exists("convert_str")){
     if($code == "e" && $orig_encoding!="EUC-JP"){
       return @convert_str($str,"EUC-JP",$orig_encoding);
     } else if($code == "j" && $orig_encoding!="JIS"){
       return @convert_str($str,"JIS",$orig_encoding);
     } else if($code == "s" && $orig_encoding!="SJIS"){
       return @convert_str($str,"SJIS",$orig_encoding);
     } else if($code == "u" && $orig_encoding!="UTF-8"){
       return @convert_str($str,"UTF-8",$orig_encoding);
     } else if($code == "u16" && $orig_encoding!="UTF-16"){
       return @convert_str($str,"UTF-16",$orig_encoding);
     } else if($code == "u7" && $orig_encoding!="UTF-7"){
       return @convert_str($str,"UTF-7",$orig_encoding);
     } else if($code == "ucs2" && $orig_encoding!="UCS-2"){
       return @convert_str($str,"UCS-2",$orig_encoding);
     } else {
       return $str;
     }
   } else {
     if($code == "e" && $orig_encoding!="EUC-JP"){
       return @i18_convert($str,"EUC-JP",$orig_encoding);
     } else if($code == "j" && $orig_encoding!="JIS"){
       return @i18n_convert($str,"JIS",$orig_encoding);
     } else if($code == "s" && $orig_encoding!="SJIS"){
       return @i18n_convert($str,"SJIS",$orig_encoding);
     } else if($code == "u" && $orig_encoding!="UTF-8"){
       return @i18n_convert($str,"UTF-8",$orig_encoding);
     } else if($code == "u7" && $orig_encoding!="UTF-7"){
       return @i18n_convert($str,"UTF-7",$orig_encoding);
     } else if($code == "u16" && $orig_encoding!="UTF-16"){
       return @i18n_convert($str,"UTF-16",$orig_encoding);
     } else if($code == "ucs2" && $orig_encoding!="UCS-2"){
       return @i18n_convert($str,"UCS-2",$orig_encoding);
     } else {
       return $str;
     }
   }
}

//------------------------------------------------------------------------------
//配列文字コード変換
//引数 : 変換前配列
//戻り値 : 変換後配列
function d_jconvs($ary,$code,$input_code=""){
	$ret = $ary;
	if (is_array($ret)) {
		foreach ($ret as $key => $value) {
			$ret[$key] = d_jconvs($value, $code, $input_code);
		}
	}
	else {
		$ret = d_jconv($ret, $code, $input_code);
	}
	return $ret;
}

//------------------------------------------------------------------------------
//mb_convert_kanaの配列対応
//引数 : 変換前配列、オプション、エンコード
//戻り値 : 変換後配列
function mb_convert_change($array, $option, $encoding = ""){
	if (!$encoding) {
		$encoding = CHAR_SET;
	}
	if (is_array($array)) {
		foreach($array as $i => $key) {
			if (is_array($key)) {
				$array[$i] = mb_convert_change($array[$i], $option, $encoding);
			}
			else{
				$array[$i] = mb_convert_kana($key, $option, $encoding);
			}
		}
	}
	else {
		$array = mb_convert_kana($array, $option, $encoding);
	}
	return $array;
};

//------------------------------------------------------------------------------
//機種依存文字変換
//引数 : 変換前文字列
//戻り値 : 変換後文字列
function escape_string($str)
{
	if (!$str || !is_string($str)) {
		return $str;
	}
	$pattern     = array( '㎜', '㎝', '㎞', '㎎', '㎏', '㏄', '㎡', '№' , '㏍'  , '℡',
			'㍉'  , '㌔'  , '㌢'    , '㍍'      , '㌘'    , '㌧'  , '㌃'    , '㌶'        ,
			'㍑'      , '㍗'    , '㌍'      , '㌦'  , '㌣'    , '㌫'       , '㍊'         , '㌻'    ,
			'①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨', '⑩',
			'⑪', '⑫', '⑬', '⑭', '⑮', '⑯', '⑰', '⑱', '⑲', '⑳',
			'㊤', '㊥', '㊦', '㊧', '㊨', '㈱'  , '㈲'  , '㈹',
			'㍾'  , '㍽'  ,  '㍼'  ,  '㍻'  ,
			'Ⅰ', 'Ⅱ',  'Ⅲ' ,  'Ⅳ',  'Ⅴ',  'Ⅵ',  'Ⅶ' , 'Ⅷ'  , 'Ⅸ', 'Ⅹ',
			'ⅰ', 'ⅱ', 'ⅲ', 'ⅳ', 'ⅴ', 'ⅵ', 'ⅶ', 'ⅷ', 'ⅸ', 'ⅹ',
			'∑', '⊿', '∮', '･',
			'ｱ', 'ｲ', 'ｳ', 'ｴ', 'ｵ', 'ｶ', 'ｷ', 'ｸ', 'ｹ', 'ｺ', 'ｻ', 'ｼ', 'ｽ', 'ｾ', 'ｿ', 'ﾀ', 'ﾁ', 'ﾂ', 'ﾃ', 'ﾄ',
			'ﾅ', 'ﾆ', 'ﾇ', 'ﾈ', 'ﾉ', 'ﾊ', 'ﾋ', 'ﾌ', 'ﾍ', 'ﾎ', 'ﾏ', 'ﾐ', 'ﾑ', 'ﾒ', 'ﾓ', 'ﾔ', 'ﾕ', 'ﾖ', 'ﾗ', 'ﾘ',
			'ﾙ', 'ﾚ', 'ﾛ', 'ﾜ', 'ｦ', 'ﾝ', 'ｧ', 'ｨ', 'ｩ', 'ｪ', 'ｫ', 'ｯ', 'ｬ', 'ｭ', 'ｮ', 'ﾞ', 'ﾟ', 'ｰ', '､', '｡',
			'｢', '｣',
			);

	$replacement = array( 'mm', 'cm', 'km', 'mg', 'kg', 'cc', 'm^2', 'No.', 'K.K.', 'TEL',
			'ミリ', 'キロ', 'センチ', 'メートル', 'グラム', 'トン', 'アール', 'ヘクタール', 
			'リットル', 'ワット', 'カロリー', 'ドル', 'セント', 'パーセント', 'ミリバール', 'ページ',
			'(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)',
			'(11)', '(12)', '(13)', '(14)', '(15)', '(16)', '(17)', '(18)', '(19)', '(20)',
			'上', '中', '下', '左', '右', '(株)', '(有)', '(代)',
			'明治', '大正',  '昭和',  '平成',
			'I' , 'II',  'III',  'IV',  'V' ,  'VI',  'VII', 'VIII', 'IX', 'X',
			'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x',
			'Σ', 'Δ', '∫', '・',
			'ア', 'イ', 'ウ', 'エ', 'オ', 'カ', 'キ', 'ク', 'ケ', 'コ', 'サ', 'シ', 'ス', 'セ', 'ソ', 'タ', 'チ', 'ツ', 'テ', 'ト',
			'ナ', 'ニ', 'ヌ', 'ネ', 'ノ', 'ハ', 'ヒ', 'フ', 'ヘ', 'ホ', 'マ', 'ミ', 'ム', 'メ', 'モ', 'ヤ', 'ユ', 'ヨ', 'ラ', 'リ',
			'ル', 'レ', 'ロ', 'ワ', 'ヲ', 'ン', 'ァ', 'ィ', 'ゥ', 'ェ', 'ォ', 'ッ', 'ャ', 'ュ', 'ョ', '゛', '゜', 'ー', '、', '。',
			'「', '」',
			);

	for ($i = 0; $i < count_ary($pattern); $i++) {
		$str = mb_ereg_replace($pattern[$i], $replacement[$i], $str);
	}

	$pattern     = array( '纊', '褜', '鍈', '銈', '蓜', '俉', '炻', '昱', '棈', '鋹', '曻', '彅', '丨', '仡', '仼', '伀', '伃', '伹', '佖', '侒',
			'侊', '侚', '侔', '俍', '偀', '倢', '俿', '倞', '偆', '偰', '偂', '傔', '僴', '僘', '兊', '兤', '冝', '冾', '凬', '刕',
			'劜', '劦', '勀', '勛', '匀', '匇', '匤', '卲', '厓', '厲', '叝', '﨎', '咜', '咊', '咩', '哿', '喆', '坙', '坥', '垬',
			'埈', '埇', '﨏', '塚', '增', '墲', '夋', '奓', '奛', '奝', '奣', '妤', '妺', '孖', '寀', '甯', '寘', '寬', '尞', '岦',
			'岺', '峵', '崧', '嵓', '﨑', '嵂', '嵭', '嶸', '嶹', '巐', '弡', '弴', '彧', '德', '忞', '恝', '悅', '悊', '惞', '惕',
			'愠', '惲', '愑', '愷', '愰', '憘', '戓', '抦', '揵', '摠', '撝', '擎', '敎', '昀', '昕', '昻', '昉', '昮', '昞', '昤',
			'晥', '晗', '晙', '晴', '晳', '暙', '暠', '暲', '暿', '曺', '朎', '朗', '杦', '枻', '桒', '柀', '栁', '桄', '棏', '﨓',
			'楨', '﨔', '榘', '槢', '樰', '橫', '橆', '橳', '橾', '櫢', '櫤', '毖', '氿', '汜', '沆', '汯', '泚', '洄', '涇', '浯',
			'涖', '涬', '淏', '淸', '淲', '淼', '渹', '湜', '渧', '渼', '溿', '澈', '澵', '濵', '瀅', '瀇', '瀨', '炅', '炫', '焏',
			'焄', '煜', '煆', '煇', '凞', '燁', '燾', '犱', '犾', '猤', '猪', '獷', '玽', '珉', '珖', '珣', '珒', '琇', '珵', '琦',
			'琪', '琩', '琮', '瑢', '璉', '璟', '甁', '畯', '皂', '皜', '皞', '皛', '皦', '益', '睆', '劯', '砡', '硎', '硤', '硺',
			'礰', '礼', '神', '祥', '禔', '福', '禛', '竑', '竧', '靖', '竫', '箞', '精', '絈', '絜', '綷', '綠', '緖', '繒', '罇',
			'羡', '羽', '茁', '荢', '荿', '菇', '菶', '葈', '蒴', '蕓', '蕙', '蕫', '﨟', '薰', '蘒', '﨡', '蠇', '裵', '訒', '訷',
			'詹', '誧', '誾', '諟', '諸', '諶', '譓', '譿', '賰', '賴', '贒', '赶', '﨣', '軏', '﨤', '逸', '遧', '郞', '都', '鄕',
			'鄧', '釚', '釗', '釞', '釭', '釮', '釤', '釥', '鈆', '鈐', '鈊', '鈺', '鉀', '鈼', '鉎', '鉙', '鉑', '鈹', '鉧', '銧',
			'鉷', '鉸', '鋧', '鋗', '鋙', '鋐', '﨧', '鋕', '鋠', '鋓', '錥', '錡', '鋻', '﨨', '錞', '鋿', '錝', '錂', '鍰', '鍗',
			'鎤', '鏆', '鏞', '鏸', '鐱', '鑅', '鑈', '閒', '隆', '﨩', '隝', '隯', '霳', '霻', '靃', '靍', '靏', '靑', '靕', '顗',
			'顥', '飯', '飼', '餧', '館', '馞', '驎', '髙', '髜', '魵', '魲', '鮏', '鮱', '鮻', '鰀', '鵰', '鵫', '鶴', '鸙', '黑',
			);

	for ($i = 0; $i < count_ary($pattern); $i++) {
		$str = mb_ereg_replace($pattern[$i], '??', $str);
	}

	return( $str );
}

//------------------------------------------------------------------------------
//文字コード化け対応メール送信
//引数 : 送信先メールアドレス、件名、本文、ヘッダ、sendmailパラメータ、送信元メールアドレス、変換前文字コード
//戻り値 : true:成功、false:失敗
function send_mail($to, $subject, $body, $headers = "", $parameters = "", $from = "", $encode = "auto") {
	$subject = mb_encode_mimeheader($subject, "ISO-2022-JP", $encode);
	$body = convert_str($body, "ISO-2022-JP", $encode);

	if ($from) {
		$headers .= "From: " . $from . "\n";
	}
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/plain; charset=iso-2022-jp\n";
	$headers .= "Content-Transfer-Encoding: 7bit\n";
	$headers .= "X-Mailer: PHP/" . phpversion();

	return mail($to, $subject, $body, $headers, $parameters);
}

//------------------------------------------------------------------------------
//SQLクエリー文字列変換
//引数 : 文字列、シングルクォーテーション付加フラグ、シングルクォーテーション変換フラグ、変換後文字コード
//戻り値 : 変換後の文字列
function escape_sql($str, $flag = true, $chg_flag = true, $code = "")
{
	$ret = $str;
	if ($code) {
		$ret = convert_str($ret, $code, "auto");
	}
	if ($chg_flag) {
		$ret = replace_str("'", "''", $ret);
		$ret = replace_str("\\", "\\\\", $ret);
	}
	if ($flag) {
		if (!$ret && $ret != "0") {
			return 'null';
		}
		$ret = "'" . $ret . "'";
	}
	return $ret;
}

//------------------------------------------------------------------------------
//HTML POST時のデータ配列を変換する ($data["item"][$i] → $data[$i]["item"])
//引数 : 変換前の配列
//戻り値 : 変換後の配列
function change_post_array($ary)
{
	$data = $ary;
	$res = array();
	foreach ($data as $key => $value) {
		if (!is_array($data[$key])) {
			continue;
		}
		for ($i = 0; $i < count_ary($data[$key]); $i++) {
			if ($data[$key][$i]) {
				$res[$i][$key] = $data[$key][$i];
			}
		}
	}
	return $res;
}

//------------------------------------------------------------------------------
//HTML POST時のデータ配列を変換する ($data["item" . $i] → $data["item"][$i])
//引数 : 変換前の配列
//戻り値 : 変換後の配列
function change_post_array2($ary, $key_name)
{
	$data = $ary;
	$i = 0;
	while ($data[$key_name . $i]) {
		$data[$key_name][$i] = $data[$key_name . $i];
		$i++;
	}
	return $data;
}

//------------------------------------------------------------------------------
//RSSリーダー
//引数 : RSSパス、行数
//戻り値 : 配列(site_title, site_link, title, link, desc)
function get_rss($rss_url, $rss_row = 10)
{
	require_once CLASS_PATH . "RSS.php";
	$rdf = $rss_url;
	$code = CHAR_SET;

	$r = new XML_RSS($rdf);
	$r->parse();
	if (!$ch = $r->getChannelInfo()) return null;

	$site_title = convert_str($ch['title'], $code, "UTF-8,EUC-JP,SJIS");
	$site_link = $ch['link'];

	$num = ($rss_row) ? $rss_row : count_ary($r->getItems());
	for ($i = 0; $i < $num; $i++) {
		$val = $r->getItems();
		if (!is_array($val[$i])) {
			continue;
		}
		foreach ($val[$i] as $key => $value) {
			if (is_array($value)) {
				$value = array_to_string("", $value);
			}
			$res[$i][$key] = convert_str(strip_tags($value), $code, "UTF-8,EUC-JP,SJIS");
		}
		$res[$i]["site_title"] = $site_title;
		$res[$i]["site_link"] = $site_link;
	}
	return $res;
}

//------------------------------------------------------------------------------
//RSSリーダー(livedoor天気用)
//引数 : RSSパス、行数
//戻り値 : 配列(site_title, site_link, title, link, desc)
function get_rss2($rss_url, $rss_row = 10)
{
	require_once CLASS_PATH . "RSS.php";
	$rdf = $rss_url;
	$code = CHAR_SET;

	$r = new XML_RSS($rdf);
	$r->parse();
	if (!$ch = $r->getChannelInfo()) return null;

	$site_title = convert_str($ch['title'], $code, "UTF-8,EUC-JP,SJIS");
	$site_link = $ch['link'];

	$num = ($rss_row) ? $rss_row : count_ary($r->getItems());
	for ($i = 0; $i < $num; $i++) {
		$val = $r->getItems();
		if (!is_array($val[$i])) {
			continue;
		}
		foreach ($val[$i] as $key => $value) {
			$res[$i][$key] = convert_str(strip_tags($value), $code, "UTF-8,EUC-JP,SJIS");
		}
		$res[$i]["title1"] = $res[$i]["title"];

		$val = $r->getImages();
		if (!is_array($val[$i])) {
			continue;
		}
		foreach ($val[$i+1] as $key => $value) {
			$res[$i][$key] = convert_str(strip_tags($value), $code, "UTF-8,EUC-JP,SJIS");
		}

		$res[$i]["site_title"] = $site_title;
		$res[$i]["site_link"] = $site_link;
	}
	return $res;
}

//------------------------------------------------------------------------------
//xmlを配列に変換
//引数 : Simple xml形式オブジェクト
//戻り値 : 配列
function xml2array($xmlobj) {
	$arr = array();
	if (is_object($xmlobj)) {
		$xmlobj = get_object_vars($xmlobj);
	} else {
		$xmlobj = $xmlobj;
	}

	foreach ($xmlobj as $key => $val) {
		if (is_object($xmlobj[$key])) {
			$arr[$key] = xml2array($val);
		} else if (is_array($val)) {
			foreach($val as $k => $v) {
				if (is_object($v) || is_array($v)) {
					$arr[$key][$k] = xml2array($v);
				} else {
					$arr[$key][$k] = $v;
				}
			}
		} else {
			$arr[$key] = $val;
		}

	}
	return $arr;
}

//------------------------------------------------------------------------------
//配列の1つを削除
//引数 : 処理前の配列、削除する配列のインデックス(0～)
//戻り値 : 処理後の配列
function delete_array($ary, $index) {
	$res = array();

	if (!is_array($ary)) {
		return $ary;
	}

	for ($i = 0, $cnt = 0; $i <= max(array_keys($ary)); $i++) {
		if ($i == $index) {
			continue;
		}
		$res[$cnt++] = $ary[$i];
	}

	return $res;
}

//------------------------------------------------------------------------------
// HTMLの文字エスケープ
// htmlspecialcharsを呼び、空の場合は&nbsp;を出力
// 引数：エスケープする文字列、改行変換フラグ、&nbsp;変換フラグ、変換後文字コード
// 戻り値：エスケープ後の文字列
function escape_html($str, $cr_flag = false, $sp_flag = false, $code = "", $url_flag = false)
{
	$ret = $str;

	//SQLエスケープを戻す
	$ret = replace_str("\\\"", "\"", $ret);

	if ($code) {
		$ret = convert_str($ret, $code, "auto");
		$c = $code;
	}
	else {
		$c = CHAR_SET;
	}
	if (is_string($ret)) {
		$ret = htmlspecialchars($ret, ENT_COMPAT | ENT_HTML401, $c);
	}

	//改行エスケープ
	if ($cr_flag) {
		$ret = replace_str("\r\n", "<br>", $ret);
		$ret = replace_str("\r", "<br>", $ret);
		$ret = replace_str("\n", "<br>", $ret);
	}

	if ($url_flag && $ret) {
		$ret = preg_replace("/(https?|ftp|news)(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$ret);
		$ret = preg_replace("/([#-9A-~]+)(@[#-9A-~]+)/","<a href=\"mailto:\\1\\2\">\\1\\2</a>",$ret);
	}

	if ($ret == "") {
		if ($sp_flag) {
			return "&nbsp;";
		}
		return "";
	}
	return $ret;
}

//------------------------------------------------------------------------------
//文字列の中のHTMLタグの許可されていないものをエスケープする
//引数 : 変換する文字列、許可するタグ 例 array('a','br','font');、改行変換フラグ、変換後文字コード
//戻り値 : エスケープ後の文字列
function escape_tag($str, $ary, $flag = false, $code = "")
{
	$ret = $str;
	if ($code) {
		$ret = convert_str($ret, $code, "auto");
	}

	//一旦変換する
	$ret = replace_str("&", "&amp;", $ret);
//	$ret = replace_str("\"", "&quot;", $ret);
//	$ret = replace_str("'", "&#039;", $ret);
	$ret = replace_str("<", "&lt;", $ret);

	//SQLエスケープを戻す
	$ret = replace_str("\\\"", "\"", $ret);

	//改行エスケープ
	if ($flag) {
		$ret = replace_str("\r\n", "<br>", $ret);
		$ret = replace_str("\r", "<br>", $ret);
		$ret = replace_str("\n", "<br>", $ret);
	}

	for ($i = 0; $i < count_ary($ary); $i++) {
		//許可するタグは"<"に戻す
		$ret = my_ireplace("&lt;" . $ary[$i], "<" . $ary[$i], $ret);
		$ret = my_ireplace("&lt;\/" . $ary[$i], "</" . $ary[$i], $ret);
	}

	return $ret;
}

//------------------------------------------------------------------------------
//str_ireplaceと同様の機能(PHP5に対応していないため)
//引数 : 置換する文字列、置換後の文字列、変換対象の文字列
//戻り値 : 変換後の文字列
function my_ireplace($needle, $str, $haystack) {
	return preg_replace("/$needle/i", $str, $haystack);
}
//------------------------------------------------------------------------------
//文字列の中のHTMLタグの許可されていないものをエスケープし、英単語に改行を入れる
//引数 : 変換する文字列、許可するタグ 例 array('a','br','font');、改行する位置(文字数)(改行しない場合は空白を指定)、改行変換フラグ、変換後文字コード
//戻り値 : エスケープ後の文字列
function escape_tag2($str, $ary, $max = 0, $cr_flag = false, $code = "")
{
	$str = escape_tag($str, $ary, false, $code);

	$rs = "";
	if ($max != 0) {
		$tag_flag = false;
		for ($i = 0; $i < mb_string_length($str); ) {
			//1行の最大文字数分を取得
			$strtmp = mb_sub_str($str, $i, $max);

			//タグ内は無視
			if ($tag_flag) {
				$tag2 = mb_str_pos($strtmp, ">");
				if ($tag2 || mb_sub_str($strtmp, 0, 1) == ">") {
					$tag_flag = false;
					$rs .= mb_sub_str($str, $i, $tag2 + 1);
					$i += $tag2 + 1;
				}
				else {
					$rs .= $strtmp;
					$i += $max;
				}
				continue;
			}

			$tag = mb_str_pos($strtmp, "<");
			if ($tag || mb_sub_str($strtmp, 0, 1) == "<") {
				$tag_flag = true;
				$rs .= mb_sub_str($str, $i, $tag + 1);
				$i += $tag + 1;
				continue;
			}

			//$max文字数以内の自動改行コードを検索
			$pos[0] = mb_str_rpos($strtmp, "\n");
			$pos[1] = mb_str_rpos($strtmp, " ");
			$pos[2] = check_multibyte($strtmp);

			if (max($pos) > 0) {
				//自動改行コードがあった場合は改行位置を次の検索位置とする
				$rs .= mb_sub_str($str, $i, max($pos) + 1);
				$i += max($pos) + 1;
			}
			elseif ($i + $max > mb_string_length($str)) {
				//全角文字がない場合で、文字の最後まで検索していればループを抜ける
				$rs .= $strtmp;
				break;
			}
			else {
				//全角文字列がない場合で、まだ文字がある場合は改行を挿入
				$rs .= $strtmp;
				$rs .= "\n";
				$i += $max;
			}
		}
	}
	else {
		//改行位置の指定がない場合はそのまま
		$rs = $str;
	}

	//改行エスケープ
	if ($cr_flag) {
		$rs = replace_str("\r\n", "<br>", $rs);
		$rs = replace_str("\r", "<br>", $rs);
		$rs = replace_str("\n", "<br>", $rs);
	}

	return $rs;
}

//------------------------------------------------------------------------------
// JavaScriptの文字エスケープ
// HTMLエンコードを行い、シングルクォーテーションを\'に変換する
// 引数：エスケープする文字列、変換後文字コード
// 戻り値：エスケープ後の文字列
function escape_js($str, $code = "")
{
	$ret = $str;
	if ($code) {
		$ret = convert_str($ret, $code, "auto");
		$c = $code;
	}
	else {
		$c = CHAR_SET;
	}
	$ret = htmlspecialchars($ret, ENT_COMPAT | ENT_HTML401, $c);

	$ret = replace_str("'", "\\'", $ret);
	$ret = replace_str("\r", "\\r", $ret);
	$ret = replace_str("\n", "\\n", $ret);
	$ret = replace_str("\r\n", "\\r\\n", $ret);

	return $ret;
}

//------------------------------------------------------------------------------
//HTML表示用文字列に変換する
//引数 : 変換する文字列、改行する位置(文字数)(改行しない場合は空白を指定)、改行変換フラグ、HTMLエスケープフラグ、変換後文字コード
//戻り値 : 変換後の文字列
function change_html($str, $max, $cr_flag = false, $escape_flag = true, $code = "") {
	//空文字の場合は空文字を返す
	if ($str == "") {
		return "";
	}

	if ($code) {
		$str = convert_str($str, $code, "auto");
		$c = $code;
	}
	else {
		$c = CHAR_SET;
	}

	$rs = "";
	if ($max != 0) {
		for ($i = 0; $i < mb_string_length($str); ) {
			//1行の最大文字数分を取得
			$strtmp = mb_sub_str($str, $i, $max);

			//$max文字数以内の自動改行コードを検索
			$pos[0] = mb_str_rpos($strtmp, "\n");
			$pos[1] = mb_str_rpos($strtmp, " ");
			$pos[2] = check_multibyte($strtmp);

			if (max($pos) > 0) {
				//自動改行コードがあった場合は改行位置を次の検索位置とする
				$rs .= mb_sub_str($str, $i, max($pos) + 1);
				$i += max($pos) + 1;
			}
			elseif ($i + $max > mb_string_length($str)) {
				//全角文字がない場合で、文字の最後まで検索していればループを抜ける
				$rs .= $strtmp;
				break;
			}
			else {
				//全角文字列がない場合で、まだ文字がある場合は改行を挿入
				$rs .= $strtmp;
				$rs .= "\n";
				$i += $max;
			}
		}
	}
	else {
		//改行位置の指定がない場合はそのまま
		$rs = $str;
	}

	if ($escape_flag) {
		//HTML表示用文字列に変換する
		$rs = htmlspecialchars($rs, ENT_COMPAT | ENT_HTML401, $c);
	}

	//改行エスケープ
	if ($cr_flag) {
		$rs = replace_str("\r\n", "<br>", $rs);
		$rs = replace_str("\r", "<br>", $rs);
		$rs = replace_str("\n", "<br>", $rs);
	}

	return $rs;
}

//------------------------------------------------------------------------------
//スマートフォン用にHTML表示用文字列を調整する
//引数 : 変換する文字列、文字カットフラグ、改行変換フラグ、HTMLエスケープフラグ、変換後文字コード
//戻り値 : 変換後の文字列
function sp_list_html($str, $cut_flag = true, $cr_flag = false, $escape_flag = true, $code = "") {
	//空文字の場合は空文字を返す
	if ($str == "") {
		return "";
	}
	$rs = $str;

	$rs = strip_tags($rs);
	$rs = replace_str(" ", "", $rs);
	$rs = replace_str("\r\n", " ", $rs);
	$rs = replace_str("\n", " ", $rs);
	$rs = replace_str("\r", " ", $rs);
	$rs = change_html($rs, 20, $cr_flag, $escape_flag, $code);

	if (string_length($rs) > 44) {
		$rs = cut_string($rs, 43) . "...";
	}

	return $rs;
}

//------------------------------------------------------------------------------
//セッションの特殊文字エスケープを解除
//引数 : 変換する連想配列
//戻り値 : 変換後の連想配列
function escape_sess($Hash)
{
	if (!is_array($Hash)) {
		return null;
	}
	foreach ( $Hash as $key => $value )
	{
		$value = replace_str("\\\"", "\"", $value);
		$value = replace_str("\\'", "'", $value);
		$ret[$key] = replace_str("\\\\", "\\", $value);
	}
	return $ret;
}

//------------------------------------------------------------------------------
//特殊文字エスケープを解除
//引数 : 変換する連想配列
//戻り値 : 変換後の連想配列
function clear_escape($data)
{
	if (!is_array($data)) {
		return null;
	}

	foreach ($data as $key => $value) {
		if (is_array($value)) {
			array_map('clear_escape', $value);
		}
		else {
			$value = replace_str("\\\"", "\"", $value);
			$value = replace_str("\\'", "'", $value);
			$ret[$key] = replace_str("\\\\", "\\", $value);
		}
	}
	return $ret;
}

//------------------------------------------------------------------------------
//第何週目取得関数
//引数 : 年、月、日
//戻り値 : その月の第何週目か
function get_week_count($year, $month, $day) {
	$first = date("w", mktime(0, 0, 0, $month, 1, $year));
	if ($day % 7 == 1) {
		return floor($day / 7) + 1;
	}
	if ($day % 7 == 0) {
		$tmp_week = 7 + $first;
		$week = floor($day / 7);
	}
	else {
		$tmp_week = ($day % 7) + $first;
		$week = floor($day / 7) + 1;
	}
	if ($tmp_week > 7) {
		$week++;
	}
	return $week;
}

//------------------------------------------------------------------------------
//週取得関数
//引数 : 年、月
//戻り値 : その月が第何週目まであるかを取得
function get_week_max($year, $month) {
	$tmp_day = date("t", mktime(0, 0, 0, $month, 1, $year));
	return get_week_count($year, $month, $tmp_day);
}

//------------------------------------------------------------------------------
//ファイルサイズのフォーマット文字列作成
//引数 : ファイルサイズ
//戻り値 : フォーマット後のファイルサイズ文字列
function format_file_size($str)
{
	$size = $str;
	if (string_length($size) > 12) {
		$size = round($size / 1000000000000, 1) . "TB";
	}
	elseif (string_length($size) > 9) {
		$size = round($size / 1000000000, 1) . "GB";
	}
	elseif (string_length($size) > 6) {
		$size = round($size / 1000000, 1) . "MB";
	}
	elseif (string_length($size) > 3) {
		$size = round($size / 1000, 1) . "KB";
	}
	else {
		$size = $size . "B";
	}
	return $size;
}

//------------------------------------------------------------------------------
//文字列の切り取り
//引数 : 文字列、切り取り位置(バイト)
//戻り値 : 変換後の文字列
function cut_string($str, $cut)
{
	return mb_strcut($str, 0, $cut, CHAR_SET);

	if (0) {
		//空の場合
		if ($str == "") {
			return $str;
		}

		for ($i = 0, $cnt = 0; $i < string_length($str) && $i < $cut; $i++, $cnt++) {
			$chr = sub_str($str, $i, 1);
			if (!(preg_match('/^[\x20-\x7F]$/', $chr))) {
				if ($i + 1 >= $cut) {
					break;
				}
				$i++;
				$cnt++;
			}
		}

		return sub_str($str, 0, $cnt);
	}
}

//------------------------------------------------------------------------------
//全角を後ろから検索し、位置を返す
//引数 : チェックする文字列
//戻り値 : 文字列の一番最後の全角の位置
function check_multibyte($strchk)
{
	//空の場合は0を返す
	if ($strchk == "")
	{
		return 0;
	}

	$result = 0;
	for ($i = mb_string_length($strchk) - 1; $i >= 0; $i--)
	{
		//後ろから1文字ずつ取得
		$chr = mb_sub_str($strchk, $i, 1);
		if (!(preg_match('/^[\x20-\x7F]$/', $chr)))
		{
			//日本語の場合はその位置を返す
			$result = $i;
			break;
		}
	}
	return $result;
}

//------------------------------------------------------------------------------
//半角数字チェック
//引数 : チェック文字列、文字数チェック
//戻り値 : true:半角数字のみ、false:半角数字以外あり
function check_number($str, $length = 0)
{
	$ret = $str;
	$res = false;

	if (preg_match("/[^0-9]/", $ret))
	{
		//数字以外の文字があった場合
	}
	else
	{
		//数字のみの場合
		if ($length) {
			//文字数チェック
			if (string_length($str) == $length) {
				$res = true;
			}
		}
		else {
			$res = true;
		}
	}

	return $res;
}

//------------------------------------------------------------------------------
//電話番号チェック(半角数字、ハイフンチェック)
//引数 : チェック文字列、桁数チェックフラグ
//戻り値 : true:半角数字,-のみ、false:半角数字,-以外あり
function check_tel($str, $len_flag = false)
{
	if ($len_flag) {
		$ret = replace_str("-", "", $str);

		if (!check_number($ret))
		{
			//数字以外の文字があった場合
			return false;
		}

		//桁数をチェック
		if (string_length($ret) == 10 || string_length($ret) == 11)
		{
			return true;
		}
		return false;
	}
	else
	{
		$ret = $str;

		if (preg_match("/[^0-9,-]+$/", $ret))
		{
			//数字以外の文字があった場合
			return false;
		}
		else
		{
			//数字のみの場合
			return true;
		}
	}
}

//------------------------------------------------------------------------------
//郵便番号チェック(XXX-XXXXまたはXXXXXXX)
//引数 : チェック文字列
//戻り値 : true:郵便番号形式、false:郵便場号以外
function check_postcode($str)
{
	$ret = $str;

	if (preg_match("/^\d{3}\-\d{4}$/", $ret) || preg_match("/^\d{7}$/", $ret))
	{
		//郵便番号形式
		return true;
	}
	else
	{
		//郵便場号形式以外の場合
		return false;
	}
}

//------------------------------------------------------------------------------
//半角英数字チェック
//引数 : チェック文字列、空白許可フラグ
//戻り値 : true:半角英数字のみ、false:半角英数字以外あり
function check_alpha($str, $flag = false)
{
	if (string_length($str) == 0)
	{
		//空文字の場合
		return false;
	}

	for ($i = 0; $i < string_length($str); $i++)
	{
		//1文字ずつ取得
		$a = ord(sub_str($str, $i, 1));
		if ($flag)
		{
			if (($a < ord("0") or $a > ord("9")) and ($a < ord("a") or $a > ord("z")) and ($a < ord("A") or $a > ord("Z")) and
				($a <> ord("@")) and ($a <> ord(".")) and ($a <> ord("_")) and ($a <> ord("-")) and ($a <> ord(" ")))
			{
				//半角英数字以外の文字があった場合
				return false;
			}
		}
		else
		{
			if (($a < ord("0") or $a > ord("9")) and ($a < ord("a") or $a > ord("z")) and ($a < ord("A") or $a > ord("Z")) and
				($a <> ord("@")) and ($a <> ord(".")) and ($a <> ord("_")) and ($a <> ord("-")))
			{
				//半角英数字以外の文字があった場合
				return false;
			}
		}
	}

	return true;
}

//------------------------------------------------------------------------------
//記号込みのパスワードチェック
//許可する記号は!#$%&()=~|-^\,./<>?_;:+*[]{}@`
//引数 : チェック文字列
//戻り値 : true:半角英数字記号のみ、false:半角英数字記号以外あり
function check_password($password) {
    // 許可文字のみで構成されているかチェック
    return preg_match('/^[A-Za-z0-9!#$%&()=~|\-\^\\\\,\.\/<>?_;\:\+\*\[\]\{\}@`]+$/', $password) === 1;
}

//------------------------------------------------------------------------------
//メールアドレスチェック
//引数 : チェック文字列
//戻り値 : true:正常、false:正しくない
function check_email($str)
{
	$regex = $strict ? '/^([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i' : '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=??-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i';
	if (preg_match($regex, trim($str))) {
		return true;
	}
	else {
		return false;
	}
}

//------------------------------------------------------------------------------
//HTMLラジオチェック出力
//引数 : Value1、Value2
//戻り値 : 一致した場合 checked、一致しない場合はなし
function check_radio($value1, $value2)
{
	$ret = "";
	if ($value1 === $value2) {
		$ret = " checked";
	}
	return $ret;
}

//------------------------------------------------------------------------------
//HTMLチェックボックスチェック出力
//引数 : チェックボックスValue、Value2
//戻り値 : 一致した場合 checked、一致しない場合はなし
function check_box($ary, $value2)
{
	$ret = "";
	if (!is_array($ary)) {
		if ($ary === $value2) {
			$ret = " checked";
		}
		return $ret;
	}

	for ($i = 0; $i < count_ary($ary); $i++) {
		if ($ary[$i] === $value2) {
			$ret = " checked";
			return $ret;
		}
	}

	return $ret;
}

//------------------------------------------------------------------------------
//HTMLプルダウンメニューセレクト出力
//引数 : Value1、Value2
//戻り値 : 一致した場合 selected、一致しない場合はなし
function check_select($value1, $value2)
{
	$ret = "";
	if ($value1 === $value2) {
		$ret = " selected";
	}
	return $ret;
}

//------------------------------------------------------------------------------
// フルパスからファイル名を取得
// 引数: パス文字列、拡張子付加フラグ
// 戻り値: ファイル名
function get_file_name($str_path, $ext_flag = true)
{
	$str = $str_path;
	if (!$ext_flag) {
		$pos = str_rpos($str, ".");
		if ($pos !== false)
		{
			$str = sub_str($str, 0, $pos);
		}
	}
	$pos = str_rpos($str, "\\");
	if ($pos !== false)
	{
		return sub_str($str, $pos+1);
	}
	$pos = str_rpos($str, "/");
	if ($pos !== false)
	{
		return sub_str($str, $pos+1);
	}
	return $str;
}

//------------------------------------------------------------------------------
// フルパスから拡張子名を取得
// 引数: パス文字列
// 戻り値: 拡張子名
function get_file_ex($str_path)
{
	$pos = str_rpos($str_path, ".");
	if ($pos !== false)
	{
		return sub_str($str_path, $pos+1);
	}
	return "";
}

//------------------------------------------------------------------------------
// URLからモードを取得(例:admin_list.htmlからlistを抽出)
// 引数: パス文字列
// 戻り値: モード名
function get_file_mode($str_path)
{
	$str = get_file_name($str_path, false);
	$pos = str_rpos($str, "_");
	if ($pos !== false)
	{
		return sub_str($str, $pos+1);
	}
	return $str;
}

//------------------------------------------------------------------------------
// 生年月日から年齢を取得
// 引数: 年号、年、月、日
// 戻り値: 年齢
function calc_age($nengo,$year,$month,$day)
{
	$YEAR_LIST = array(0,1867,1911,1925,1988);

	$date_array = getdate(time());
	$yyyy = $date_array["year"];
	$mm   = $date_array["mon"];
	$dd   = $date_array["mday"];

	$age  = $yyyy - $year - $YEAR_LIST[$nengo];
	if($month > $mm) $age -= 1;
	if($month == $mm && $day > $dd) $age -= 1;

	return $age;
}

//------------------------------------------------------------------------------
// ファイル容量単位取得
// 引数: ファイルサイズ(バイト)、小数点以下の文字数
// 戻り値: ファイルサイズ文字列(TB、GB、MB、B)
function get_file_unit($size, $num = 1) {
	if (string_length($size) > 12) {
		$res = round($size / 1000000000000, $num) . "TB";
	}
	elseif (string_length($size) > 9) {
		$res = round($size / 1000000000, $num) . "GB";
	}
	elseif (string_length($size) > 6) {
		$res = round($size / 1000000, $num) . "MB";
	}
	elseif (string_length($size) > 3) {
		$res = round($size / 1000, $num) . "KB";
	}
	else {
		$res = $size . "B";
	}
	return $res;
}

//------------------------------------------------------------------------------
// ディレクトリ階層以下のコピー
// 引数: コピー元ディレクトリ、コピー先ディレクトリ
// 戻り値: 結果
function dir_copy($dir_name, $new_dir)
{
	if (!is_dir($new_dir)) {
		mkdir($new_dir);
	}

	if (is_dir($dir_name)) {
		if ($dh = opendir($dir_name)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == "." || $file == "..") {
					continue;
				}
				if (is_dir($dir_name . "/" . $file)) {
					dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
				}
				else {
					copy($dir_name . "/" . $file, $new_dir . "/" . $file);
				}
			}
			closedir($dh);
		}
	}
	return true;
}

//------------------------------------------------------------------------------
// ディレクトリ階層以下の容量計算
// 引数: ディレクトリ
// 戻り値: サイズ
function dir_size($dir_name)
{
	$size = 0;
	if (is_dir($dir_name)) {
		if ($dh = opendir($dir_name)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == "." || $file == "..") {
					continue;
				}
				if (is_dir($dir_name . "/" . $file)) {
					$size += dir_size($dir_name . "/" . $file);
				}
				else {
					$size += filesize($dir_name . "/" . $file);
				}
			}
			closedir($dh);
		}
	}
	return $size;
}

//------------------------------------------------------------------------------
// ディレクトリ階層以下のファイル数計算
// 引数: ディレクトリ
// 戻り値: ファイル数
function file_count($dir_name)
{
	$count = 0;
	if (is_dir($dir_name)) {
		if ($dh = opendir($dir_name)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == "." || $file == "..") {
					continue;
				}
				if (is_dir($dir_name . "/" . $file)) {
					$count += file_count($dir_name . "/" . $file);
				}
				else {
					$count++;
				}
			}
			closedir($dh);
		}
	}
	return $count;
}

//------------------------------------------------------------------------------
// ディレクトリ階層以下のファイル、ディレクトリ削除
// 引数: ディレクトリ、ディレクトリ削除フラグ
// 戻り値: なし
function dir_delete($dir, $del_dir_flag = true)
{
	$dir_name = $dir;
	if (sub_str($dir_name, -1) == "/") {
		$dir_name = sub_str($dir_name, 0, -1);
	}
	if (is_dir($dir_name)) {
		if ($dh = opendir($dir_name)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == "." || $file == "..") {
					continue;
				}
				if (is_dir($dir_name . "/" . $file)) {
					dir_delete($dir_name . "/" . $file);
				}
				else {
					unlink($dir_name . "/" . $file);
				}
			}
			closedir($dh);
		}
		if ($del_dir_flag) {
			rmdir($dir_name);
		}
	}

	return true;
}

//------------------------------------------------------------------------------
// テンポラリディレクトリの古いファイルを削除
// 引数: ディレクトリ名、年、月、日
// 戻り値: なし
function delete_tmpfile($dir_name, $year = 1, $month = 0, $day = 0)
{
	$tmp_time = mktime(date("H"), date("i"), date("s"), date("n") - $month, date("j") - $day, date("Y") - $year);
	if (is_dir($dir_name)) {
		if ($dh = opendir($dir_name)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == "." || $file == "..") {
					continue;
				}
				$filename = $dir_name . "/" . $file;
				if (is_file($filename)) {
					if ($tmp_time > filemtime($filename)) {
						unlink($filename);
					}
				}
			}
			closedir($dh);
		}
	}
}

//------------------------------------------------------------------------------
// 自動パスワード発行
// 引数: パスワード文字数、文字フラグ(0:英数字、1:数字のみ、2:大文字数字)
// 戻り値: パスワード
function auto_password($length = 8, $num_flag = false)
{
	if ($num_flag == 2) {
		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
	}
	elseif ($num_flag) {
		$str = "1234567890";
	}
	else {
		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
	}
	$str = str_shuffle($str);
	return sub_str($str, 0, $length);
}

//------------------------------------------------------------------------------
// 自動パスワード発行2
// 引数: パスワード文字数、数字フラグ、英字フラグ、記号フラグ
// 戻り値: パスワード
function auto_password2($length = 8, $num_flag = true, $en_flag = true, $mark_flag = false)
{
	$str = "";
	if (!$length) {
		return "";
	}
	if ($num_flag) {
		$str .= "1234567890";
	}
	if ($en_flag) {
		$str .= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	}
	if ($mark_flag) {
		$str .= "!#$%&()=~|-^\,./<>?_;:+*[]{}@`";
	}

	$res = "";
	for ($i = 0; $i < $length; $i++) {
		$res .= sub_str($str, rand(0, string_length($str)-1), 1);
	}

	return $res;
}

//------------------------------------------------------------------------------
// XML読み込み
// 引数: XMLファイルパス、データキー名
// 戻り値: 配列
function readDatabase($filename, $key_name) {
	// read the XML database of aminoacids
	$data = implode("", file($filename));
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $tags);
	xml_parser_free($parser);

	// loop through the structures
	foreach ($tags as $key=>$val) {
		if ($key == $key_name) {
			$molranges = $val;
			// each contiguous pair of array entries are the 
			// lower and upper range for each molecule definition
			for ($i=0; $i < count_ary($molranges); $i+=2) {
				$offset = $molranges[$i] + 1;
				$len = $molranges[$i + 1] - $offset;
				$tdb[] = parseMol(array_slice($values, $offset, $len));
			}
		} else {
			continue;
		}
	}
	return $tdb;
}

//------------------------------------------------------------------------------
// XML解析
// 引数: XMLデータ項目
// 戻り値: 配列
function parseMol($mvalues) {
	for ($i=0; $i < count_ary($mvalues); $i++) {
		$mol[$mvalues[$i]["tag"]] = $mvalues[$i]["value"];
	}
	return $mol;
}

//------------------------------------------------------------------------------
// ファイルアップロード処理
// 引数: パラメータ名、セッション名、テンポラリディレクトリパス
// 戻り値: 成功時 null、失敗時 エラーメッセージ
function upload_file($param_name, $update_session_name, $tmp_dir) {
	if (!is_uploaded_file($_FILES[$param_name]["tmp_name"])) {
		return null;
	}

	$file_to = session_id() . date("YmdHis") . $param_name;
	$_SESSION[$update_session_name][$param_name . "_tmp"] = $file_to;
	$_SESSION[$update_session_name][$param_name . "_name"] = $_FILES[$param_name]["name"];
	if (!copy($_FILES[$param_name]["tmp_name"], $tmp_dir . $file_to)) {
		return "copy error.";
	}
	return null;
}

//------------------------------------------------------------------------------
// 複数ファイルアップロード処理
// 引数: パラメータ名、セッション名、テンポラリディレクトリパス、リクエストデータ
// 戻り値: 成功時 null、失敗時 エラーメッセージ
function upload_files($param_name, $update_session_name, $tmp_dir, $request_data) {
	for ($i = 0; $i < count_ary($_FILES[$param_name]["tmp_name"]); $i++) {
		if (!is_uploaded_file($_FILES[$param_name]["tmp_name"][$i])) {
			$_SESSION[$update_session_name][$param_name . "_tmp"][$i] = "";
			$_SESSION[$update_session_name][$param_name . "_name"][$i] = $request_data[$param_name . "_name"][$i];
			continue;
		}

		$file_to = session_id() . date("YmdHis") . $param_name . $i;
		$_SESSION[$update_session_name][$param_name . "_tmp"][$i] = $file_to;
		$_SESSION[$update_session_name][$param_name . "_name"][$i] = $_FILES[$param_name]["name"][$i];
		if (!copy($_FILES[$param_name]["tmp_name"][$i], $tmp_dir . $file_to)) {
			return "copy error.";
		}
	}
	return null;
}

//------------------------------------------------------------------------------
// ファイルアップロード完了処理
// 引数: コピー元ファイルパス、コピー先ファイルパス
// 戻り値: 成功時 null、失敗時 エラーメッセージ
function complete_upload($from, $to, $tmp_dir = "") {
	if (!is_file($from)) {
		return "temporary file is not found.";
	}
	if (!get_file_name($to)) {
		return "file name is null.";
	}
	if (!copy($from, $to)) {
		return "copy error.";
	}

	//一時ファイルを削除
	unlink($from);

	//古いゴミファイルを削除
	if ($tmp_dir) {
		delete_tmpfile($tmp_dir, 0, 0, 3);
	}
	return null;
}

//------------------------------------------------------------------------------
// ファイル削除処理
// 引数: ファイルパス
// 戻り値: なし
function delete_file($file_name) {
	if (is_file($file_name)) {
		unlink($file_name);
	}
}

//------------------------------------------------------------------------------
//画像リサイズ処理(縦横比固定)
//引数 : 元ファイル名(フルパス)、コピー先ファイル名(フルパス)、リサイズのピクセル
//戻り値 : 成功 画像タイプ、失敗 false
function resize_image($file_from, $file_to, $resize = 0)
{
	// アップされた画像の大きさを取得（戻り値：配列（0:width 1:height））
	$upImgSize = getimagesize($file_from);
	$resize_flag = true;

	//アップされた画像を元に新規画像を作成
	switch ($upImgSize[2]) {
	case "1":
		$upNewTmpImg  = imagecreatefromgif($file_from);
		break;
	case "2":
		$upNewTmpImg  = imagecreatefromjpeg($file_from);
		break;
	case "3":
		$upNewTmpImg  = imagecreatefrompng($file_from);
		break;
	default:
		$resize_flag = false;
		break;
	}

	if ($resize == 0) {
		$resize_flag = false;
	}

	if ($resize_flag) {
		// アップされた画像をリサイズして保存
		//TrueColorイメージを新規に作成する（引数１：サイズ変更後の横サイズ　／引数２：サイズ変更後の縦サイズ）
		$img_width = $upImgSize[0];
		$img_height = $upImgSize[1];

		if ($img_width > $resize) {
			$img_width = $resize;
			$img_height = $upImgSize[1] * $resize / $upImgSize[0];
		}
		if ($img_height > $resize) {
			$img_width = $upImgSize[0] * $resize / $upImgSize[1];
			$img_height = $resize;
		}
		if ($upImgSize[2] == "1") {
			$newImage = ImageCreate($img_width, $img_height);
		}
		else {
			$newImage = ImageCreateTrueColor($img_width, $img_height);
		}

		/*
		* 再サンプリングを行いイメージの一部をコピー、伸縮する
		* ※ImageCopyResampled()の引数一覧（順番に）
		*　コピー先、コピー元、コピー先のＸ座標値、コピー先のＹ座標値、
		*　コピー元のＸ座標値、コピー元のＹ座標値、
		*　コピー先の横サイズ、コピー先の縦サイズ、
		*　コピー元の横サイズ、コピー先の縦サイズ */
		$icr_result = ImageCopyResampled($newImage,$upNewTmpImg,0,0,0,0,$img_width,$img_height,$upImgSize[0],$upImgSize[1]);

		// 画像をファイルに出力する
		switch ($upImgSize[2]) {
		case "1":
			if($icr_result)$ij_result = imagegif($newImage, $file_to);
			break;
		case "2":
			if($icr_result)$ij_result = imagejpeg($newImage, $file_to);
			break;
		case "3":
			if($icr_result)$ij_result = imagepng($newImage, $file_to);
			break;
		}

		// 作成し終わった画像テンポラリーファイルを破棄する
		ImageDestroy($upNewTmpImg);
		imageDestroy($newImage);

		// 処理に失敗：強制終了）
		if(!$icr_result||!$ij_result) {
			return false;
		}
	}
	else {
		//リサイズなし
		if (!copy($file_from, $file_to)) {
			return false;
		}
	}

	return $upImgSize[2];
}

//------------------------------------------------------------------------------
//画像リサイズ処理(縦横比固定)
//引数 : 元ファイル名(フルパス)、コピー先ファイル名(フルパス)、リサイズのピクセル幅、リサイズのピクセル高さ、
//       強制リサイズ → falseの場合は指定サイズを超えた場合のみリサイズ、trueの場合は、強制リサイズ
//戻り値 : 成功 画像タイプ、失敗 false
function resize_image2($file_from, $file_to, $width = 0, $height = 0, $resize = false)
{
	// アップされた画像の大きさを取得（戻り値：配列（0:width 1:height））
	$upImgSize = getimagesize($file_from);
	$resize_flag = true;

	//アップされた画像を元に新規画像を作成
	switch ($upImgSize[2]) {
	case "1":
		$upNewTmpImg  = imagecreatefromgif($file_from);
		break;
	case "2":
		$upNewTmpImg  = imagecreatefromjpeg($file_from);
		break;
	case "3":
		$upNewTmpImg  = imagecreatefrompng($file_from);
		break;
	default:
		$resize_flag = false;
		break;
	}

	if ($width == 0 && $height == 0) {
		$resize_flag = false;
	}

	if ($resize_flag) {
		// アップされた画像をリサイズして保存
		//TrueColorイメージを新規に作成する（引数１：サイズ変更後の横サイズ　／引数２：サイズ変更後の縦サイズ）
		$img_width = $upImgSize[0];
		$img_height = $upImgSize[1];

		if (($resize || $img_width > $width) && $width) {
			$img_width = $width;
			$img_height = $upImgSize[1] * $width / $upImgSize[0];
		}
		if (($resize || $img_height > $height) && $height) {
			$img_width = $upImgSize[0] * $height / $upImgSize[1];
			$img_height = $height;
		}
		if ($upImgSize[2] == "1") {
			$newImage = ImageCreate($img_width, $img_height);
		}
		else {
			$newImage = ImageCreateTrueColor($img_width, $img_height);
		}

		/*
		* 再サンプリングを行いイメージの一部をコピー、伸縮する
		* ※ImageCopyResampled()の引数一覧（順番に）
		*　コピー先、コピー元、コピー先のＸ座標値、コピー先のＹ座標値、
		*　コピー元のＸ座標値、コピー元のＹ座標値、
		*　コピー先の横サイズ、コピー先の縦サイズ、
		*　コピー元の横サイズ、コピー先の縦サイズ */
		$icr_result = ImageCopyResampled($newImage,$upNewTmpImg,0,0,0,0,$img_width,$img_height,$upImgSize[0],$upImgSize[1]);

		// 画像をファイルに出力する
		switch ($upImgSize[2]) {
		case "1":
			if($icr_result)$ij_result = imagegif($newImage, $file_to);
			break;
		case "2":
			if($icr_result)$ij_result = imagejpeg($newImage, $file_to);
			break;
		case "3":
			if($icr_result)$ij_result = imagepng($newImage, $file_to);
			break;
		}

		// 作成し終わった画像テンポラリーファイルを破棄する
		ImageDestroy($upNewTmpImg);
		imageDestroy($newImage);

		// 処理に失敗：強制終了）
		if(!$icr_result||!$ij_result) {
			return false;
		}
	}
	else {
		//リサイズなし
		if (!copy($file_from, $file_to)) {
			return false;
		}
	}

	return $upImgSize[2];
}

//------------------------------------------------------------------------------
//画像回転処理
//引数 : 元ファイル名(フルパス)、回転度数
//戻り値 : 成功 画像タイプ、失敗 false
function rotate_image($file_name, $degrees = 0)
{
	// アップされた画像の大きさを取得（戻り値：配列（0:width 1:height））
	$upImgSize = getimagesize($file_name);

	//アップされた画像を元に新規画像を作成
	switch ($upImgSize[2]) {
	case "1":
		$upNewTmpImg  = imagecreatefromgif($file_name);
		break;
	case "2":
		$upNewTmpImg  = imagecreatefromjpeg($file_name);
		break;
	case "3":
		$upNewTmpImg  = imagecreatefrompng($file_name);
		break;
	}

	ini_set('memory_limit', '128M');

	if ($degrees) {
		$newImage = imagerotate($upNewTmpImg, -$degrees, 0);

		// 画像をファイルに出力する
		switch ($upImgSize[2]) {
		case "1":
			imagegif($newImage, $file_name);
			break;
		case "2":
			imagejpeg($newImage, $file_name);
			break;
		case "3":
			imagepng($newImage, $file_name);
			break;
		}

		// 作成し終わった画像テンポラリーファイルを破棄する
		ImageDestroy($upNewTmpImg);
		imageDestroy($newImage);
	}

	return $upImgSize[2];
}

?>
