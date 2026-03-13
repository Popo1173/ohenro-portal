<?php
//------------------------------------------------------------------------------
// デフォルトセット
// 開始時に実行
//------------------------------------------------------------------------------

// 共通ファイルインクルード
set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_PATH);
include_once(CMN_ROOT . "cmn_funcs.php");
include_once(CMN_ROOT . "common.php");

//アンケート設問数
define("ENQ_NUM", 7);

//文字コード
define("HTTP_CHAR", "UTF-8");
define("CHAR_SET", "UTF-8");
define("DB_CHAR", "UTF8");

//ファイル拡張子
define("FILE_EXT", ".html");
define("EXT", ".html");
define("IMAGE_FILE_EXT", ".jpg");

//動画URL用CSVファイル
define("CSV_FILE_PATH", SVR_ROOT . "csv/");
//メールフォーマットディレクトリ
define("MAIL_ROOT" , CMN_ROOT . "mail/");

//ファイルパス
define("CSV_SYS_ROOT", CMN_ROOT . "csv/");
define("TMP_FILE_ROOT", SVR_ROOT . "admin/tmp/");
define("TMP_FILE_PATH", HTTP_ROOT . "admin/tmp/");
define("PDF_FILE_ROOT", SVR_ROOT . "pdf/");
define("PDF_FILE_PATH", HTTP_ROOT . "pdf/");
define("IMAGE_FILE_ROOT", CMN_ROOT . "images/notice/");
define("IMAGE_FILE_PATH", CMN_PATH . "images/notice/");
define("TEMPLE_IMAGE_ROOT", CMN_ROOT . "images/temple/");
define("TEMPLE_IMAGE_PATH", CMN_PATH . "images/temple/");
define("TEMPLE_LSIZE_ROOT", CMN_ROOT . "images/temple-l/");
define("TEMPLE_LSIZE_PATH", CMN_PATH . "images/temple-l/");
define("INFO_IMAGE_ROOT", CMN_ROOT . "images/");
define("INFO_IMAGE_PATH", CMN_PATH . "images/");


//言語定義
$lang_ary = array(
	"0"=>"ja",
	"1"=>"en",
	"2"=>"zh-CN",
	"3"=>"zh-TW",
	"4"=>"es",
	"5"=>"fr",
	"6"=>"it",
	"7"=>"de",
);

$lang_string = array(
	"0"=>"日本語",
	"1"=>"英語",
	"2"=>"中国語 (簡体)",
	"3"=>"中国語 (繁體)",
	//"4"=>"スペイン語",
	//"5"=>"フランス語",
	//"6"=>"イタリア語",
	//"7"=>"ドイツ語",
);

//動画CSV列定義
$keys = array(
	"0"=>"pref",
	"1"=>"temple",
	"2"=>"url1",
	"3"=>"url2",
);
//札所以外の動画は、89行目以降に管理ID,タイトル,URL,(空白)とする

//メッセージCSV列定義(lang_codeをvalueとする)
$message_keys = array(
	"0"=>"message_num",
	"4"=>"0",
	"5"=>"1",
	"6"=>"2",
	"7"=>"3",
);

//札所CSV列定義
$temple_keys = array(
	"temple_num",
	"mountain",
	"mount_kana",
	"infirmary",
	"infir_kana",
	"temple_name",
	"temple_kana",
	"pref",
	"address",
	"tel",
	//"access",
	"detail",
	"shuuha",
	"kaiki",
	"honzon",
	"founding",
	"shingon",
	"next_temple",
	"walk",
	"car",
	"next_comment",
	"ohenro_item",
	//"comment",
	"inn",
	"shukubou",
	"taxi",
	//"language",
	//"food",
	"Johannes",
	"interview",
);

//札所CSVヘッダ
$temple_header = array(
	"札所No",
	"山",
	"山（カナ）",
	"院",
	"院（カナ）",
	"寺院名",
	"寺院名（カナ）",
	"県",
	"所在",
	"電話",
	//"アクセス",
	"概要",
	"宗派",
	"開基",
	"本尊",
	"創建",
	"真言",
	"次の札所まで（単位km）",
	"徒歩（分）",
	"車（分）",
	"次の札所説明",
	"遍路用品",
	//"情報",
	"周辺宿泊",
	"宿坊",
	"タクシー",
	//"言語対応",
	//"食",
	"ヨハネスさんコメント",
	"旅館オーナーインタビュー",
);


//宿CSV列定義
$inn_keys = array(
	"info_num",
	"info_name",
	//"pref",
	//"address",
	"tel",
	//"access",
	"comment",
	"status",
);

//宿CSVヘッダ
$inn_header = array(
	"ID",
	"名称",
	//"県",
	//"住所",
	"電話番号",
	//"アクセス",
	"備考",
	"ステータス",
);

//タクシーCSV列定義
$taxi_keys = array(
	"info_num",
	"info_name",
	//"pref",
	//"address",
	"tel",
	//"access",
	//"comment",
	"status",
);

//タクシーCSVヘッダ
$taxi_header = array(
	"ID",
	"名称",
	//"県",
	//"住所",
	"電話番号",
	//"アクセス",
	//"備考",
	"営業ステータス",
);

//旅館インタビューCSV列定義
$interview_keys = array(
	"info_num",
	"info_name",
	//"pref",
	//"address",
	//"tel",
	//"access",
	"comment",
	"url",
);

//旅館インタビューCSVヘッダ
$interview_header = array(
	"ID",
	"名称",
	//"県",
	//"住所",
	//"電話番号",
	//"アクセス",
	"リード",
	"リンク先",
);

//情報モード
$info_ary = array(
	"",
	"旅館",
	"タクシー",
	"旅館インタビュー",
);
//情報モード
$info_str = array(
	"",
	"inn",
	"taxi",
	"interview",
);

//情報CSVキー配列
$info_keys = array(
	"",
	$inn_keys,
	$taxi_keys,
	$interview_keys,
);

//情報CSVヘッダー
$info_header = array(
	"",
	$inn_header,
	$taxi_header,
	$interview_header,
);


//会員CSV列定義
$user_keys = array(
	"lang_code",
	"family_name",
	"first_name",
	"family_kana",
	"first_kana",
	"email",
	//"country",
	"age",
);

//会員CSVヘッダ
$user_header = array(
	"言語",
	"姓",
	"名",
	"セイ",
	"メイ",
	"メールアドレス",
	//"居住国",
	"年齢層",
);

//メルマガ購読フラグ
$mail_read_ary = array(
	"購読しない",
	"購読する",
);

//メールマガジンステータス
$mail_status_ary = array(
	"",
	"即時送信",
	"送信予約",
	"送信済み",
);

//公開ステータス
$status_ary = array("0", "1");
$display_ary = array(
	"非公開",
	"公開する",
);

//都道府県(四国のみ)
$shikoku_ary = array("徳島県", "高知県", "愛媛県", "香川県");
$shikoku_tab = array(
	"tokushima"=>"徳島県",
	"kochi"=>"高知県",
	"ehime"=>"愛媛県",
	"kagawa"=>"香川県",
);


//暗号化キー
define("PASS_KEY", "UMSFDijnP5aPRAgn");

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

mb_language("Japanese");
date_default_timezone_set('Asia/Tokyo');

//セッション管理開始
if (isset($_SERVER["SERVER_NAME"])) {
	session_start();
	if (!isset($_SESSION["initiated"])) {
		$_SESSION["initiated"] = true;
		session_regenerate_id();
	}
}

set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_PATH);
date_default_timezone_set('Asia/Tokyo');

//現在のURL
$current_url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

//表示日付
$ary_year = array();
for ($i = 0; $i < 5; $i++) {
	$tmp = date("Y") - 1 + $i;
	$ary_year[$tmp] = $tmp;
}

$ary_month = array();
for ($i = 1; $i <= 12; $i++) {
	$tmp = sprintf("%02d", $i);
	$ary_month[$tmp] = $tmp;
}

$ary_day = array();
for ($i = 1; $i <= 31; $i++) {
	$tmp = sprintf("%02d", $i);
	$ary_day[$tmp] = $tmp;
}

//曜日
$ary_week = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
$ary_week_j = array("日", "月", "火", "水", "木", "金", "土");

//時間
$ary_hour = array();
for ($i = 1; $i <= 23; $i++) {
	$tmp = sprintf("%02d", $i);
	$ary_hour[$tmp] = $tmp;
}

$ary_min = array();
for ($i = 0; $i < 60; $i += 5) {
	$tmp = sprintf("%02d", $i);
	$ary_min[$tmp] = $tmp;
}

//更新
$mode_ary = array("登録", "変更", "削除");

//都道府県
$pref_ary = array("北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県","新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県","滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県","徳島県","香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県", "その他");

//許可するタグ
$tag_ary = array("strong", "em", "u", "a", "span", "img", "br", "p", "b", "i", "u", "font", "div", "strike", "sub", "sup", "li", "ol", "hr");

$need_string = ' <span class="font_orangetxt">※</span>';

$accesslog_string = "
<script type=\"text/javascript\">
document.write(\"<img src='" . CMN_PATH . "accesslog.php?\");
document.write(\"referer=\"+document.referrer+\"&\");
document.write(\"width=\"+screen.width+\"&\");
document.write(\"height=\"+screen.height+\"&\");
document.write(\"color=\"+screen.colorDepth+\"' \");
document.write(\"width='0' \");
document.write(\"height='0' \");
document.write(\"border='0' \");
document.write(\"alt=''>\");
</script>
";

$request_data = array();
$post_send_flag = false;
if ('POST' == $_SERVER['REQUEST_METHOD']) {
	$request_data = $_POST;
	$post_send_flag = true;
}
elseif ('GET' == $_SERVER['REQUEST_METHOD']) {
	$request_data = $_GET;
}

?>
