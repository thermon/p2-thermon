<?php
/**
 * ImageCache2 設定ファイル
 */

// {{{ 全般

// キャッシュ保存ディレクトリのパス
$_conf['expack.ic2.general.cachedir'] = "./cache";

// DSN (DBに接続するためのデータソース名)
// @link http://jp.pear.php.net/manual/ja/package.database.db.intro-dsn.php
// 例)
//  MySQL:       "mysql://username:password@localhost:3306/database"
//  PostgreSQL:  "pgsql://username:password@localhost:5432/database"
//  SQLite2:     "sqlite:///./cache/imgcache.sqlite"
// 注1: username,password,databaseは実際のものと読み替える。
// 注2: MySQL,PosrgreSQLでは予めデータベースを作っておく。
$_conf['expack.ic2.general.dsn'] = "";

// DBで使うテーブル名
$_conf['expack.ic2.general.table'] = "imgcache";

// 削除済み＆再ダウンロードしない画像リストのテーブル名
$_conf['expack.ic2.general.blacklist_table'] = "ic2_blacklist";

// エラーを記録するテーブル名
$_conf['expack.ic2.general.error_table'] = "ic2_errors";

// エラーを記録する最大の行数
$_conf['expack.ic2.general.error_log_num'] = 100;

// 画像のURLが貼られたスレッドのタイトルを自動で記録する (off:0;on:1)
$_conf['expack.ic2.general.automemo'] = 1;

// 画像を処理するプログラム (gd | imagick | ImageMagick)
// gd, imagick は PHP の拡張モジュールを利用、ImageMagick は外部コマンドを利用
// ImageMagickのバージョンを自動判定するようになったので
// 明示的に"ImageMagick6"を指定しなくてもよい
$_conf['expack.ic2.general.driver'] = "gd";

// JPEG to JPEG 変換に Epeg エクステンションを使う (off:0;on:1)
// http://page2.xrea.jp/index.php#php_epeg
$_conf['expack.ic2.general.epeg'] = 0;

// JPEG の品質がこの値より小さいとき Epeg エクステンションを使う
$_conf['expack.ic2.general.epeg_quality_limit'] = 90;

// ImageMagickのパス（convertがある“ディレクトリ”のパス）
// httpdの環境変数でパスが通っているなら空のままでよい
// パスを明示的に指定する場合は、スペースがあるとサムネイルが作成できないので注意
$_conf['expack.ic2.general.magick'] = "";

// 透過画像をサムネイル化する際の背景色 (ImageMagick(6)では無効、16進6桁で指定)
$_conf['expack.ic2.general.bgcolor'] = "#FFFFFF";

// 携帯でもサムネイルをインライン表示する (off:0;on:1)
// このときの大きさはPCと同じ
$_conf['expack.ic2.general.inline'] = 1;

// 携帯用の画像を表示するときLocation ヘッダを使ってリダイレクトする (off:0;on:1)
// offならPHPで適切なContent-Typeヘッダと画像を出力する
$_conf['expack.ic2.general.redirect'] = 1;

// }}}
// {{{ 一覧

// ページタイトル
$_conf['expack.ic2.viewer.title'] = "ImageCache2::Viewer";

// Lightbox Plus で画像を表示 (off:0;on:1)
// @link http://serennz.sakura.ne.jp/toybox/lightbox/?ja
$_conf['expack.ic2.viewer.lightbox'] = 0;
$_conf['expack.ic2.viewer.lightbox'] = 1; //customized

// オリジナル画像が見つからないレコードを自動で消去する (off:0;on:1)
$_conf['expack.ic2.viewer.delete_src_not_exists'] = 0;

// 表示用に調整した画像情報をキャッシュ (off:0;on:1)
$_conf['expack.ic2.viewer.cache'] = 0;

// キャッシュの有効期限（秒）
// 1時間=3600
// 1日=86400
// 1週間=604800
// 手動でクリアするまでずっと=-1
$_conf['expack.ic2.viewer.cache_lifetime'] = 3600;

// 重複画像を最初にヒットする1枚だけ表示 (off:0;on:1)
// サブクエリに対応していないMySQL 4.1未満で有効にするとエラーが出る
$_conf['expack.ic2.viewer.unique'] = 0;

// Exif情報を表示 (off:0;on:1)
$_conf['expack.ic2.viewer.exif'] = 0;

// --以下の設定ははデフォルト値で、ツールバーで変更できる--

// 1ページ当たりの列数
$_conf['expack.ic2.viewer.cols'] = 8;

// 1ページ当たりの行数
$_conf['expack.ic2.viewer.rows'] = 5;

// 1ページ当たりの画像数（携帯用）
$_conf['expack.ic2.viewer.inum'] = 10;

// しきい値 (-1 ~ 5)
$_conf['expack.ic2.viewer.threshold'] = 0;

// 並び替え基準 (time | uri | date_uri | name | size | width | height | pixels)
$_conf['expack.ic2.viewer.order'] = "time";

// 並び替え方向 (ASC | DESC)
$_conf['expack.ic2.viewer.sort'] = "DESC";

// 検索フィールド (uri | name | memo)
$_conf['expack.ic2.viewer.field'] = "memo";

// }}}
// {{{ 管理

// ページタイトル
$_conf['expack.ic2.manager.title'] = "ImageCache2::Manager";

// メモ記入欄の1行当たりの半角文字数
$_conf['expack.ic2.manager.cols'] = 40;

// メモ記入欄の行数
$_conf['expack.ic2.manager.rows'] = 5;

// }}}
// {{{ ダウンロード

// ページタイトル
$_conf['expack.ic2.getter.title'] = "ImageCache2::Getter";

// サーバに接続する際にタイムアウトするまでの時間（秒）
$_conf['expack.ic2.getter.conn_timeout'] = 5;

// ダウンロードがタイムアウトするまでの時間（秒）
$_conf['expack.ic2.getter.read_timeout'] = 60;

// エラーログにある画像はダウンロードを試みない (no:0;yes:1)
$_conf['expack.ic2.getter.checkerror'] = 1;

// デフォルトでURL+.htmlの偽リファラを送る (no:0;yes:1)
$_conf['expack.ic2.getter.sendreferer'] = 0;

// sendreferer = 0 のとき、例外的にリファラを送るホスト（カンマ区切り）
$_conf['expack.ic2.getter.refhosts'] = "";

// sendreferer = 1 のとき、例外的にリファラを送らないホスト（カンマ区切り）
$_conf['expack.ic2.getter.norefhosts'] = "";

// 強制あぼーんのホスト（カンマ区切り）
$_conf['expack.ic2.getter.reject_hosts'] = "rotten.com,shinrei.net";

// 強制あぼーんURLの正規表現
$_conf['expack.ic2.getter.reject_regex'] = "";

// ウィルススキャンをする (no:0;clamscan:1;clamdscan:2)
// （Clam AntiVirusを利用）
// ImageCache2や手動スキャンにしかClamAVを使わないなら1でclamscanの方が無難と思われる
$_conf['expack.ic2.getter.virusscan'] = 0;

// ClamAVのパス（clam(d)scanがある“ディレクトリ”のパス）
// httpdの環境変数でパスが通っているなら空のままでよい
// パスを明示的に指定する場合は、スペースがあるとウィルススキャンできないので注意
$_conf['expack.ic2.getter.clamav'] = "";

// }}}
// {{{ プロキシ

// 画像のダウンロードにプロキシを使う (no:0;yes:1)
$_conf['expack.ic2.proxy.enabled'] = 0;

// ホスト
$_conf['expack.ic2.proxy.host'] = "";

// ポート
$_conf['expack.ic2.proxy.port'] = "";

// ユーザ名
$_conf['expack.ic2.proxy.user'] = "";

// パスワード
$_conf['expack.ic2.proxy.pass'] = "";

// }}}
// {{{ ソース

// 保存用サブディレクトリ名
$_conf['expack.ic2.source.name'] = "src";

// キャッシュする最大データサイズ（これを越えると禁止リスト行き、0は無制限）
$_conf['expack.ic2.source.maxsize'] = 10000000;

// キャッシュする最大の幅（上に同じく）
$_conf['expack.ic2.source.maxwidth'] = 4000;

// キャッシュする最大の高さ（〃）
$_conf['expack.ic2.source.maxheight'] = 4000;

// }}}
// {{{ サムネイル

// 設定名（＝保存用サブディレクトリ名）
$_conf['expack.ic2.thumb1.name'] = 6464;

// サムネイルの最大幅（正の整数）
$_conf['expack.ic2.thumb1.width'] = 64;

// サムネイルの最大高さ（正の整数）
$_conf['expack.ic2.thumb1.height'] = 64;

// サムネイルのJPEG品質（正の整数、1~100以外にするとPNG）
$_conf['expack.ic2.thumb1.quality'] = 80;

// }}}
// {{{ 携帯フルスクリーン

// 設定名
$_conf['expack.ic2.thumb2.name'] = "qvga_v";

// サムネイルの最大幅
$_conf['expack.ic2.thumb2.width'] = 240;

// サムネイルの最大高さ
$_conf['expack.ic2.thumb2.height'] = 320;

// サムネイルのJPEG品質
$_conf['expack.ic2.thumb2.quality'] = 80;

// }}}
// {{{ 中間イメージ

// 設定名
$_conf['expack.ic2.thumb3.name'] = "vga";

// サムネイルの最大幅
$_conf['expack.ic2.thumb3.width'] = 640;

// サムネイルの最大高さ
$_conf['expack.ic2.thumb3.height'] = 480;

// サムネイルのJPEG品質
$_conf['expack.ic2.thumb3.quality'] = 80;

// }}}
// {{{ 生成した画像をキャッシュしない動的生成のプリセット値

// "設定名" => arrray(width, height, quality) の連想配列
$_conf['expack.ic2.dynamic.presets'] = array(
    //"WQVGA待受" => array(240, 400, 90),
    //"iPhone待受" => array(320, 480, 0),
);

// }}}
@include_once P2_CONF_DIR  . '/conf_ic2.inc.private.php';
/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
