<?php
/**
 * rep2expack - tGrep �������ʂ̃����_�����O for PC
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
    <?php echo $_conf['extra_headers_xht']; ?>
    <title>tGrep<?php if (strlen($htm['query']) > 0) { echo ' - ', $htm['query']; } ?></title>
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;<?php echo $htm['skin_q']; ?>" />
    <link rel="stylesheet" type="text/css" href="css.php?css=subject&amp;<?php echo $htm['skin_q']; ?>" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <style type="text/css">
    /* <![CDATA[ */
    div.tgrep_message {
        margin:0; padding:0; line-height:120%;
        <?php echo $htm['message_background'], $htm['message_border'], $htm['message_color']; ?>
    }
    div.tgrep_message h4 {
        margin:1em;
    }
    div.tgrep_message ul {
        margin:1em; padding:0 0 0 2em;
    }
    div.tgrep_result {
        margin:0; padding:2px; line-height:100%; white-space:nowrap;
        <?php echo $htm['message_background'], $htm['message_color']; ?>
    }
    tr.tablefooter td {
        padding:2px; text-align:center;
        border-top:<?php echo $STYLE['sb_th_bgcolor']; ?> solid 1px;
        <?php echo $htm['message_background'], $htm['message_color']; ?>
    }
    /* ]]> */
    </style>
    <script type="text/javascript">
    //<![CDATA[
    function setWinTitle() {
        if (top != self) {top.document.title=self.document.title;}
    }
    function sf() {
        <?php if (strlen($htm['query']) == 0) { echo 'document.getElementById("Q").focus()'; } ?>
    }
    //]]>
    </script>
</head>
<body onload="sf();setWinTitle();gIsPageLoaded=true;">

<!-- Toolbar1 -->
<table id="sbtoolbar1" class="toolbar" cellspacing="0">
<tr>
    <td align="left" valign="middle"><span class="itatitle" id="top"><a class="aitatitle" href="<?php echo $htm['tgrep_url']; ?>" target="_blank"><b>tGrep for rep2</b></a></span></td>
    <td align="left" valign="middle">
        <form id="searchForm" name="searchForm" action="<?php echo $htm['php_self']; ?>" method="get" accept-charset="<?php echo $_conf['accept_charset']; ?>">
        <input id="Q" name="Q" <?php echo $htm['search_attr']; ?> />
        <input type="submit" value="����" />
        <?php echo $_conf['detect_hint_input_xht'], $_conf['k_input_ht']; ?>
        </form>
    </td>
    <td align="right" valign="middle"><?php if ($threads) { ?><a class="toolanchor" href="#sbtoolbar2" target="_self">��</a><?php } else { ?>�@<?php } ?></td>
</tr>
</table>

<?php if (!$query) { ?>
<!-- HowTo -->
<div class="tgrep_message">
<h4>�X���^�C�����ɂ���</h4>
<ul>
    <li>�L�[���[�h�̓X�y�[�X��؂��3�܂Ŏw��ł��A���ׂĂ��܂ނ��̂����o����܂��B</li>
    <li>2�ڈȍ~�̃L�[���[�h�œ���&quot;-&quot;������ƁA������܂܂Ȃ����̂����o����܂��B</li>
    <li>&quot; �܂��� &#39; �ň͂܂ꂽ�����̓X�y�[�X�������Ă��Ă���̃L�[���[�h�Ƃ��Ĉ����܂��B</li>
    <li>�L�[���[�h�̑S�p���p�A�啶���������͖�������܂��B</li>
    <li>�f�[�^�x�[�X�̍X�V�p�x��3���Ԃ�1��ŁA���X���E�����E�������͍X�V���_�ł̒l�ł��B</li>
</uL>
</div>
<?php } ?>
<?php if ($errors) { ?>
<!-- Errors -->
<div class="tgrep_message">
<h4>�G���[</h4>
<ul><?php foreach ($errors as $error) { ?><li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li><?php } ?></ul>
</div>
<?php } ?>

<?php if (!$errors && $profile) { ?>
<!-- Result and Filter -->
<div class="tgrep_result">
<?php if ($htm['category'] && isset($profile['categories'][$htm['category']])) { ?>
<b><?php echo htmlspecialchars($profile['categories'][$htm['category']]->name, ENT_QUOTES); ?></b> ���� <b><?php echo $htm['query']; ?></b> ������: <?php echo $htm['hits']; ?> hit! (all: <?php echo $htm['allhits']; ?>)
<?php } else { ?>
<b><?php echo $htm['query']; ?></b> �Ō���: <?php echo $htm['hits']; ?> hit!
<?php } ?>
<input id="h_php_self" type="hidden" value="<?php echo $htm['php_self']; ?>" />
<input id="h_query_en" type="hidden" value="<?php echo $htm['query_en']; ?>" />
<input id="h_read_php" type="hidden" value="<?php echo $_conf['read_php']; ?>" />
<input id="h_subject_php" type="hidden" value="<?php echo $_conf['subject_php']; ?>" />
| �J�e�S���ōi�荞��:
<select onchange="location.href=document.getElementById('h_php_self').value+'?Q='+document.getElementById('h_query_en').value+this.options[this.selectedIndex].value">
<option value="">-</option>
<?php foreach ($profile['categories'] as $c) { ?><option value="&amp;C=<?php echo $c->id; ?>"<?php if ($c->id == $htm['category']) { echo ' selected="selected"'; } ?>><?php echo htmlspecialchars($c->name, ENT_QUOTES); ?> (<?php echo $c->hits; ?>)</option><?php } ?>
</select>
| �ōi�荞��:
<select onchange="location.href=document.getElementById('h_subject_php').value+'?word='+document.getElementById('h_query_en').value+this.options[this.selectedIndex].value">
<option value="">-</option>
<?php $m = ($htm['category'] && isset($profile['categories'][$htm['category']])) ? $profile['categories'][$htm['category']]->member : null; ?>
<?php foreach ($profile['boards'] as $n => $b) { if (!$m || in_array($n, $m)) { ?><option value="<?php printf('&amp;host=%s&amp;bbs=%s&amp;itaj_en=%s', $b->host, $b->bbs, rawurlencode(base64_encode($b->name))); ?>"><?php echo htmlspecialchars($b->name, ENT_QUOTES); ?> (<?php echo $b->hits; ?>)</option><?php } } ?>
</select>
</div>
<?php } ?>

<?php if ($threads) { ?>
<!-- ThreadList and Pager -->
<table class="threadlist" cellspacing="0">
<thead>
<tr class="tableheader">
    <th class="ti">�V��</th>
    <th class="ti">���X</th>
    <th class="ti">No.</th>
    <th class="tl">�^�C�g��</th>
    <th class="t">��</th>
    <th class="t">Birthday</th>
    <th class="ti">����</th>
    <th class="ti">������</th>
</tr>
</thead>
<tbody>
<?php
$R = true;
require_once P2_LIB_DIR . '/Thread.php';
foreach ($threads as $o => $t) {
    $new = '';
    $turl = sprintf('%s?host=%s&amp;bbs=%s&amp;key=%d', $_conf['read_php'], $t->host, $t->bbs, $t->tkey);
    $burl = sprintf('%s?host=%s&amp;bbs=%s&amp;itaj_en=%s&amp;word=%s', $_conf['subject_php'], $t->host, $t->bbs, rawurlencode(base64_encode($t->ita)), $htm['query_en']);
    if (P2Util::isHostMachiBbs($t->host)) {
        $ourl = sprintf('http://%s/bbs/read.pl?BBS=%s&KEY=%s', $t->host, $t->bbs, $t->tkey);
    } else {
        $ourl = sprintf('http://%s/test/read.cgi/%s/%s/', $t->host, $t->bbs, $t->tkey);
    }
    $ourl = P2Util::throughIme($ourl);
    $aThread = new Thread;
    $aThread->setThreadPathInfo($t->host, $t->bbs, $t->tkey);
    if ($aThread->getThreadInfoFromIdx() && $aThread->isKitoku()) {
        $rnum = max($t->resnum, $aThread->readnum);
        $nnum = max(0, $rnum - $aThread->readnum);
    } else {
        $rnum = $t->resnum;
        $nnum = '';
    }
?>
<tr class="<?php echo $R ? 'r1' : 'r2'; $R = !$R; ?>">
    <td class="ti"><?php echo $nnum; ?></td>
    <td class="ti"><?php echo $rnum; ?></td>
    <td class="ti"><?php echo $o; ?></td>
    <td class="tl"><a href="<?php echo $ourl; ?>" target="read">�E</a> <a href="<?php echo $turl; ?>" target="read"><?php echo $t->title; ?></a></td>
    <td class="t"><a href="<?php echo $burl; ?>"><?php echo $t->ita; ?></a></td>
    <td class="t"><?php echo date('y/m/d', $t->tkey); ?></td>
    <td class="ti"><?php echo round($t->dayres, 2); ?></td>
    <td class="ti"><?php echo round($t->dratio * 100); ?>%</td>
</tr>
<?php } ?>
</tbody>
<?php if ($htm['pager']) { ?>
<tfoot>
<tr class="tablefooter">
    <td colspan="8"><?php echo $htm['pager']; ?></td>
</tr>
</tfoot>
<?php } ?>
</table>
<?php } ?>

<?php if ($threads) { ?>
<!-- Toolbar2 -->
<table id="sbtoolbar2" class="toolbar" cellspacing="0">
<tr>
    <td align="left" valign="middle"><span class="itatitle" id="bottom"><a class="aitatitle" href="<?php echo $htm['tgrep_url']; ?>" target="_blank"><b>tGrep for rep2</b></a></span></td>
    <td align="left" valign="middle">
        <form id="searchForm2" name="searchForm2" action="<?php echo $htm['php_self']; ?>" method="get" accept-charset="<?php echo $_conf['accept_charset']; ?>">
        <input id="Q2" name="Q" <?php echo $htm['search_attr']; ?> />
        <input type="submit" value="����" />
        <?php echo $_conf['detect_hint_input_xht'], $_conf['k_input_ht']; ?>
        </form>
    </td>
    <td align="right" valign="middle"><a class="toolanchor" href="#sbtoolbar1" target="_self">��</a></td>
</tr>
</table>
<?php } ?>

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
