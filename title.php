<?php
/**
 * rep2 - タイトルページ
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ユーザ認証

//=========================================================
// 変数
//=========================================================

if (!empty($GLOBALS['pref_dir_realpath_failed_msg'])) {
    P2Util::pushInfoHtml('<p>' . $GLOBALS['pref_dir_realpath_failed_msg'] . '</p>');
}

$p2web_url_r = P2Util::throughIme($_conf['p2web_url']);
$expack_url_r = P2Util::throughIme($_conf['expack.web_url']);
$expack_dl_url_r = P2Util::throughIme($_conf['expack.download_url']);
$expack_hist_url_r = P2Util::throughIme($_conf['expack.history_url']);

// {{{ データ保存ディレクトリのパーミッションの注意を喚起する

P2Util::checkDirWritable($_conf['dat_dir']);
$checked_dirs[] = $_conf['dat_dir']; // チェック済みのディレクトリを格納する配列に

// まだチェックしていなければ
if (!in_array($_conf['idx_dir'], $checked_dirs)) {
    P2Util::checkDirWritable($_conf['idx_dir']);
    $checked_dirs[] = $_conf['idx_dir'];
}
if (!in_array($_conf['pref_dir'], $checked_dirs)) {
    P2Util::checkDirWritable($_conf['pref_dir']);
    $checked_dirs[] = $_conf['pref_dir'];
}

// }}}

//=========================================================
// 前処理
//=========================================================
// ●ID 2ch オートログイン
if ($array = P2Util::readIdPw2ch()) {
    list($login2chID, $login2chPW, $autoLogin2ch) = $array;
    if ($autoLogin2ch) {
        require_once P2_LIB_DIR . '/login2ch.inc.php';
        login2ch();
    }
}

//=========================================================
// プリント設定
//=========================================================
// 最新版チェック
$newversion_found = '';
if (!empty($_conf['updatan_haahaa'])) {
    $newversion_found = checkUpdatan();
}

// ログインユーザ情報
$htm['auth_user'] = "<p>ログインユーザ: {$_login->user_u} - " . date("Y/m/d (D) G:i") . "</p>\n";

// （携帯）ログイン用URL
$base_url = rtrim(dirname(P2Util::getMyUrl()), '/');
$url_b = $base_url . '?user=' . rawurlencode($_login->user_u) . '&b=';
$url_b_ht = htmlspecialchars($url_b, ENT_QUOTES);

// 携帯用ビューを開くブックマークレット
$bookmarklet = <<<JS
(function (u, w, v, x, y) {
    var t;
    if (typeof window.outerHeight === 'number') {
        t = y + window.outerHeight;
        if (v < t){
            v = t;
        }
    }
    t = window.open(u, '', 'width=' + w + ',height=' + v + ',' +
        'scrollbars=yes,resizable=yes,toolbar=no,menubar=no,status=no'
    );
    if (t) {
        t.resizeTo(w, v);
        t.focus();
        return false;
    } else {
        return true;
    }
})
JS;
$bookmarklet = preg_replace('/\\b(var|return|typeof) +/', '$1{%space%}', $bookmarklet);
$bookmarklet = preg_replace('/\\s+/', '', $bookmarklet);
$bookmarklet = str_replace('{%space%}', ' ', $bookmarklet);

$bookmarklet_k = $bookmarklet . "('{$url_b}k',240,320,20,-100)";
$bookmarklet_i = $bookmarklet . "('{$url_b}i',320,480,20,-100)";
$bookmarklet_k_ht = htmlspecialchars($bookmarklet_k, ENT_QUOTES);
$bookmarklet_i_ht = htmlspecialchars($bookmarklet_i, ENT_QUOTES);
$bookmarklet_k_en = rawurlencode($bookmarklet_k);
$bookmarklet_i_en = rawurlencode($bookmarklet_i);

$htm['ktai_url'] = <<<EOT
<table border="0" cellspacing="0" cellpadding="1">
    <tbody>
        <tr>
            <th>携帯用URL:</th>
            <td><a href="{$url_b_ht}k" target="_blank" onclick="return {$bookmarklet_k_ht};">{$url_b_ht}k</a></td>
            <td>[<a href="javascript:{$bookmarklet_k_en};">bookmarklet</a>]</td>
        </tr>
        <tr>
            <th>iPhone用URL:</th>
            <td><a href="{$url_b_ht}i" target="_blank" onclick="return {$bookmarklet_i_ht};">{$url_b_ht}i</a></td>
            <td>[<a href="javascript:{$bookmarklet_i_en};">bookmarklet</a>]</td>
        </tr>
    </tbody>
</table>
EOT;

// 前回のログイン情報
$htm['log'] = '';
$htm['last_login'] = '';
if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
    if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
        $htm['log'] = array_map('htmlspecialchars', $log);
        $htm['last_login'] = <<<EOT
<br>
<table border="0" cellspacing="0" cellpadding="1">
    <caption>前回のログイン情報 - {$htm['log']['date']}</caption>
    <tbody>
        <tr><th>ユーザ:</th><td>{$htm['log']['user']}</td></tr>
        <tr><th>IP:</th><td>{$htm['log']['ip']}</td></tr>
        <tr><th>HOST:</th><td>{$htm['log']['host']}</td></tr>
        <tr><th>UA:</th><td>{$htm['log']['ua']}</td></tr>
        <tr><th>REFERER:</th><td>{$htm['log']['referer']}</td></tr>
    </tbody>
</table>
EOT;
    }
}

//=========================================================
// HTMLプリント
//=========================================================
$ptitle = 'rep2 - title';

echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>{$ptitle}</title>
    <base target="read">
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=title&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>
<body>\n
EOP;

// 情報メッセージ表示
P2Util::printInfoHtml();

echo <<<EOP
<br>
<div class="container">
    {$newversion_found}
    <p>rep2-expack rev.{$_conf['p2expack']}; extends rep2-{$_conf['p2version']}; modified by {$_conf['p2modifier']}<br>
    <a href="{$expack_url_r}"{$_conf['ext_win_target_at']}>{$_conf['expack.web_url']}</a><br>
    <a href="{$p2web_url_r}"{$_conf['ext_win_target_at']}>{$_conf['p2web_url']}</a></p>
    <ul>
        <li><a href="viewtxt.php?file=doc/README.txt">README.txt</a></li>
        <li><a href="viewtxt.php?file=doc/README-EX.txt">README-EX.txt</a></li>
        <li><a href="img/how_to_use.png">ごく簡単な操作法</a></li>
        <li><a href="{$expack_hist_url_r}"{$_conf['ext_win_target_at']}>拡張パック 更新記録</a></li>
        <!-- <li><a href="viewtxt.php?file=doc/ChangeLog.txt">ChangeLog（rep2 更新記録）</a></li> -->
    </ul>
    {$htm['auth_user']}
    {$htm['ktai_url']}
    {$htm['last_login']}
</div>
</body>
</html>
EOP;

//==================================================
// 関数
//==================================================
// {{{ checkUpdatan()

/**
 * オンライン上のrep2-expack最新版をチェックする
 *
 * @return string HTML
 */
function checkUpdatan()
{
    global $_conf, $p2web_url_r, $expack_url_r, $expack_dl_url_r, $expack_hist_url_r;

    $no_p2status_dl_flag  = false;

    $ver_txt_url = $_conf['expack.web_url'] . 'version.txt';
    $cachefile = P2Util::cacheFileForDL($ver_txt_url);
    FileCtl::mkdirFor($cachefile);

    if (file_exists($cachefile)) {
        // キャッシュの更新が指定時間以内なら
        if (filemtime($cachefile) > time() - $_conf['p2status_dl_interval'] * 86400) {
            $no_p2status_dl_flag = true;
        }
    }

    if (empty($no_p2status_dl_flag)) {
        P2Util::fileDownload($ver_txt_url, $cachefile);
    }

    $ver_txt = FileCtl::file_read_lines($cachefile, FILE_IGNORE_NEW_LINES);
    $update_ver = $ver_txt[0];
    $kita = 'ｷﾀ━━━━（ﾟ∀ﾟ）━━━━!!!!!!';
    //$kita = 'ｷﾀ*･ﾟﾟ･*:.｡..｡.:*･ﾟ(ﾟ∀ﾟ)ﾟ･*:.｡. .｡.:*･ﾟﾟ･*!!!!!';

    $newversion_found_html = '';
    if ($update_ver && version_compare($update_ver, $_conf['p2expack'], '>')) {
        $newversion_found_html = <<<EOP
<div class="kakomi">
    {$kita}<br>
    オンライン上に 拡張パック の最新バージョンを見つけますた。<br>
    rep2-expack rev.{$update_ver} → <a href="{$expack_dl_url_r}"{$_conf['ext_win_target_at']}>ダウンロード</a> / <a href="{$expack_hist_url_r}"{$_conf['ext_win_target_at']}>更新記録</a>
</div>
<hr class="invisible">
EOP;
    }
    return $newversion_found_html;
}

// }}}

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
