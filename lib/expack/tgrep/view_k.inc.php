<?php
/**
 * rep2expack - tGrep �������ʂ̃����_�����O for Mobile
 */
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>tGrep<?php if (strlen($htm['query']) > 0) { echo ' - ', $htm['query']; } ?></title>
    <?php echo $htm['mobile_css'], $_conf['extra_headers_ht']; ?>
</head>
<body>

<h1 id="top" name="top">�گ�����ٌ���</h1>

<!-- Search Form -->
<form action="<?php echo $htm['php_self']; ?>" method="get">
<input name="Q" <?php echo $htm['search_attr']; ?>>
<input type="submit" value="����">
<?php echo $_conf['detect_hint_input_ht'], $_conf['k_input_ht']; ?>
</form>
<hr>

<?php if (!$query) { ?>
<?php
if ($_conf['expack.tgrep.quicksearch']) {
    require_once P2EX_LIB_DIR . '/tgrep/menu_quick.inc.php';
    echo "<hr>\n";
}
if ($_conf['expack.tgrep.recent_num'] > 0) {
    require_once P2EX_LIB_DIR . '/tgrep/menu_recent.inc.php';
    echo "<hr>\n";
}
?>
<!-- HowTo -->
<h4>�d�l</h4>
<ul>
<li>��ܰ�ނͽ�߰���؂��3�܂Ŏw��ł�����ׂĂ��܂ނ��̂����o����܂��</li>
<li>2�ڈȍ~�̷�ܰ�ނœ���&quot;-&quot;������Ƥ������܂܂Ȃ����̂����o����܂��</li>
<li>&quot; �܂��� &#39; �ň͂܂ꂽ�����ͽ�߰��������Ă��Ă���̷�ܰ�ނƂ��Ĉ����܂��</li>
<li>��ܰ�ނ̑S�p���p��啶���������͖�������܂��</li>
<li>�ް��ް��̍X�V�p�x��1���Ԃ�1��Ťڽ���������������͍X�V���_�ł̒l�ł��</li>
</uL>
<?php } ?>
<?php if ($errors) { ?>
<!-- Errors -->
<h4>�װ</h4>
<ul><?php foreach ($errors as $error) { ?><li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li><?php } ?></ul>
<?php } ?>

<?php if (!$errors && $profile) { ?>
<!-- Result and Filter -->
<p>
<?php if ($htm['category'] && isset($profile['categories'][$htm['category']])) { ?>
<b><?php echo htmlspecialchars($profile['categories'][$htm['category']]->name, ENT_QUOTES); ?></b>����<b><?php echo $htm['query']; ?></b>������:<?php echo $htm['hits']; ?>hit!(all:<?php echo $htm['allhits']; ?>)
<?php } else { ?>
<b><?php echo $htm['query']; ?></b>�Ō���:<?php echo $htm['hits']; ?>hit!
<?php } ?>
</p>
<form action="<?php echo $htm['php_self']; ?>" method="get">
<input type="hidden" name="Q" value="<?php echo $htm['query']; ?>">
<select name="C">
<option value="">�ú�؂�I��</option>
<?php foreach ($profile['categories'] as $c) { ?><option value="<?php echo $c->id; ?>"<?php if ($c->id == $htm['category']) { echo ' selected'; } ?>><?php echo mb_convert_kana(htmlspecialchars($c->name, ENT_QUOTES), 'rnsk'); ?> (<?php echo $c->hits; ?>)</option><?php } ?>
</select>
<input type="submit" value="�i��">
</form>
<hr>
<?php } ?>

<?php if ($threads) { ?>
<!-- ThreadList and Pager -->
<div><a href="#bottom" align="right" title="����"<?php echo $_conf['k_accesskey_at']['bottom']; ?>><?php echo $_conf['k_accesskey_st']['bottom']; ?>��</a></div>
<?php
foreach ($threads as $o => $t) {
    $new = '';
    $turl = sprintf('%s?host=%s&amp;bbs=%s&amp;key=%d', $_conf['read_php'], $t->host, $t->bbs, $t->tkey);
    $burl = sprintf('%s?host=%s&amp;bbs=%s&amp;itaj_en=%s&amp;word=%s', $_conf['subject_php'], $t->host, $t->bbs, UrlSafeBase64::encode($t->ita), $htm['query_en']);
    $aThread = new Thread;
    $aThread->setThreadPathInfo($t->host, $t->bbs, $t->tkey);
    if ($aThread->getThreadInfoFromIdx() && $aThread->isKitoku()) {
        $rnum = max($t->resnum, $aThread->readnum);
        $nnum = max(0, $rnum - $aThread->readnum);
    } else {
        $rnum = $t->resnum;
        $nnum = '';
    }
    if (!empty($_conf['mobile.save_packet'])) {
        $ttitle = mb_convert_kana($t->title, 'rnsk');
        $itaj = mb_convert_kana($t->ita, 'rnsk');
    } else {
        $ttitle = $t->title;
        $itaj = $t->ita;
    }
?>
<p><?php echo $o; ?>.<a href="<?php echo $turl; ?>"><?php echo $ttitle; ?></a><br>
<small><?php echo date('y/m/d ', $t->tkey); ?><a href="<?php echo $burl; ?>"><?php echo $itaj; ?>(<?php echo $profile['boards'][$t->bid]->hits; ?>)</a></small></p>
<?php } ?>
<div><a href="#top" align="right" title="���"<?php echo $_conf['k_accesskey_at']['above']; ?>><?php echo $_conf['k_accesskey_st']['above']; ?>��</a></div>
<?php if ($htm['pager']) { ?>
<hr>
<div><?php echo $htm['pager']; ?></div>
<?php } ?>
<?php } ?>
<hr>
<p id="bottom" name="bottom">
<?php echo $_conf['k_to_index_ht'], ' '; ?>
<?php if ($query) { ?>
<a href="tgrepc.php"<?php echo $_conf['k_accesskey_at'][5]; ?>><?php echo $_conf['k_accesskey_st'][5]; ?>tGrep</a>
<?php if ($_conf['expack.tgrep.quicksearch']) { ?>
<a href="tgrepctl.php?file=quick&amp;query=<?php echo $htm['query_en']; ?>"<?php echo $_conf['k_accesskey_at'][9]; ?>><?php echo $_conf['k_accesskey_st'][9], $htm['query']; ?>���ꔭ�����ɒǉ�</a>
<?php } ?>
<?php } ?>
</p>
</body>
</html>
<?php

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
