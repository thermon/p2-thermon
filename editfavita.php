<?php
/**
 * rep2 - ���C�ɓ���ҏW
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/StrCtl.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// ����ȑO�u����
//================================================================

// ���C�ɔ̒ǉ��E�폜�A���ёւ�
if (isset($_GET['setfavita']) or isset($_POST['setfavita']) or isset($_POST['submit_setfavita'])) {
    require_once P2_LIB_DIR . '/setfavita.inc.php';
    setFavIta();
}
// ���C�ɔ̃z�X�g�𓯊�
if (isset($_GET['syncfavita']) or isset($_POST['syncfavita'])) {
    require_once P2_LIB_DIR . '/BbsMap.php';
    BbsMap::syncBrd($_conf['favita_brd']);
}


// �v�����g�p�ϐ� ======================================================

// ���C�ɔǉ��t�H�[��
$add_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['SCRIPT_NAME']}" accept-charset="{$_conf['accept_charset']}" target="_self">
    <p>
        ��URL: <input type="text" id="url" name="url" value="http://" size="48">
        ��: <input type="text" id="itaj" name="itaj" value="" size="16">
        <input type="hidden" id="setfavita" name="setfavita" value="1">
        <input type="submit" name="submit" value="�V�K�ǉ�">
    </p>
    {$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
</form>\n
EOFORM;

// ���C�ɔ����t�H�[��
$sync_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
    <p>
        {$_conf['k_input_ht']}
        <input type="hidden" id="syncfavita" name="syncfavita" value="1">
        <input type="submit" name="submit" value="���X�g�ƃz�X�g�𓯊�����">�i�̃z�X�g�ړ]�ɑΉ����܂��j
    </p>
</form>\n
EOFORM;

// ���C�ɔؑփt�H�[��
if ($_conf['expack.misc.multi_favs']) {
    $switch_favita_form_ht = FavSetManager::makeFavSetSwitchForm('m_favita_set', '���C�ɔ�', NULL, NULL, !$_conf['ktai']);
} else {
    $switch_favita_form_ht = '';
}

//================================================================
// �w�b�_
//================================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    {$_conf['extra_headers_ht']}
    <title>p2 - ���C�ɔ̕��ёւ�</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=editfavita&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <script type="text/javascript" src="js/yui/YAHOO.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/log.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/event.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/dom.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/dragdrop.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/ygDDOnTop.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/ygDDSwap.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/ygDDMy.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/ygDDMy2.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/ygDDList.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/yui/ygDDPlayer.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onload="top.document.title=self.document.title;"';
echo "</head><body{$body_at}>\n";

echo $_info_msg_ht;
$_info_msg_ht = '';

//================================================================
// ���C������HTML�\��
//================================================================

//================================================================
// ���C�ɔ�
//================================================================

// favita�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['favita_brd'], $_conf['favita_perm']);
// favita�ǂݍ���
$lines = FileCtl::file_read_lines($_conf['favita_brd'], FILE_IGNORE_NEW_LINES);
$okini_itas = array();

$i = 0;
if (is_array($lines)) {
    foreach ($lines as $l) {
        if (preg_match("/^\t?(.+?)\t(.+?)\t(.+?)\$/", $l, $matches)) {
            $id = "li{$i}";
            $okini_itas[$id]['itaj']       = $itaj = rtrim($matches[3]);
            $okini_itas[$id]['itaj_en']    = $itaj_en = base64_encode($itaj);
            $okini_itas[$id]['host']       = $host = $matches[1];
            $okini_itas[$id]['bbs']        = $bbs = $matches[2];
            $okini_itas[$id]['itaj_view']  = htmlspecialchars($itaj);
            $okini_itas[$id]['itaj_ht']    = "&amp;itaj_en=" . $itaj_en;
            $okini_itas[$id]['value']      = StrCtl::toJavaScript("{$host}@{$bbs}@{$itaj_en}");

            $i++;
        }
    }
}

// PC�p
if (!$_conf['ktai'] and !empty($lines)) {
?>
<script type="text/javascript">
//<![CDATA[
    // var gLogger = new ygLogger("test_noimpl.php");
    var dd = []
    var gVarObj = new Object();

    function dragDropInit() {
        var i = 0;
        var id = '';
        for (j = 0; j < <?php echo count($lines); ?>; ++j) {
            id = "li" + j;
            dd[i++] = new ygDDList(id);
            //gVarObj[id] = '<?php echo $host . "@" . $bbs . "@" . $itaj_en; ?>';
        }
        <?php
        foreach ($okini_itas as $k => $v) {
            echo "gVarObj['{$k}'] = '{$v['value']}';";
        }
        ?>

        dd[i++] = new ygDDListBoundary("hidden1");

        YAHOO.util.DDM.mode = 0; // 0:Point, :Intersect
    }

    YAHOO.util.Event.addListener(window, "load", dragDropInit);
    // YAHOO.util.DDM.useCache = false;


function makeOptionList()
{
    var values = [];
    var elem = document.getElementById('italist');
    var childs = elem.childNodes;
    for (var i = 0; i < childs.length; i++) {
        if (childs[i].tagName == 'LI' && childs[i].style.visibility != 'hidden' && childs[i].style.display != 'none') {
            values[i] = gVarObj[childs[i].id];
            //alert(values[i]);
        }
    }

    var val = "";
    for (var j = 0; j < values.length; j++) {
        if (val > "") {
            val += ",";
        }
        if (values[j] > "") {
            val += values[j];
        }
    }
    //alert(val);

    return val;
}

function submitApply()
{
    document.form['list'].value = makeOptionList();
    //alert(document.form['list'].value);
    //document.form.submit();
}
//]]>
</script>
<?php
}


// iPhone�p
if ($_conf['iphone'] && file_exists('./iui/iui.js')) {
    $onclick = '';
    $m_php = 'menu_i.php?nt=' . time();

// PC�p
} elseif (!$_conf['ktai']) {
    $onclick = " onclick=\"if (parent.menu) { parent.menu.location.href='{$_conf['menu_php']}?nr=1'; }\"";
    $m_php = $_SERVER['SCRIPT_NAME'];

// �g�їp
} else {
    $onclick = '';
    $m_php = 'menu_k.php?view=favita&amp;nr=1' . $_conf['k_at_a'] . '&amp;nt=' . time();
}

echo <<<EOP
<div><b>���C�ɔ̕ҏW</b> [<a href="{$m_php}"{$onclick}>���j���[���X�V</a>] {$switch_favita_form_ht}</div>
EOP;

echo $add_favita_form_ht;
echo '<hr>';

// PC�iNetFront�����O�j
if (!$_conf['ktai'] && $_conf['favita_order_dnd'] && !P2Util::isNetFront()) {

    if ($lines) {
        $script_enable_html .= <<<EOP
���C�ɔ̕��ёւ��i�h���b�O�A���h�h���b�v�j
<div class="itas">
<form id="form" name="form" method="post" action="{$_SERVER['SCRIPT_NAME']}" accept-charset="{$_conf['accept_charset']}" target="_self">

<table border="0">
<tr>
<td class="italist" id="ddrange">

<ul id="italist"><li id="hidden6" class="sortList" style="visibility:hidden;">Hidden</li>
EOP;
        if (is_array($okini_itas)) {
            foreach ($okini_itas as $k => $v) {
                $script_enable_html .= '<li id="' . $k . '" class="sortList">' . $v['itaj_view'] . '</li>';
            }
        }
    }

    $script_enable_html .= <<<EOP
<li id="hidden1" style="visibility:hidden;">Hidden</li></ul>

</td>
</tr>
</table>

<input type="hidden" name="list">

<input type="submit" value="���ɖ߂�">
<input type="submit" name="submit_setfavita" value="�ύX��K�p����" onclick="submitApply();">

</div>
</form>
EOP;
    $regex = array('/"/', '/\n/');
    $replace = array('\"', null);
    $out = preg_replace($regex, $replace, $script_enable_html);

    echo <<<EOP
<script type="text/javascript">
<!--
document.write("{$out}");
//-->
</script>
EOP;

}

//================================================================
// NOSCRIPT����HTML�\��
//================================================================
if ($lines) {
    // PC�iNetFront�����O�j
    if (!$_conf['ktai'] && $_conf['favita_order_dnd'] && !P2Util::isNetFront()) {
        echo '<noscript>';
    }
    echo '���C�ɔ̕��ёւ�';
    echo '<table>';
    foreach ($lines as $l) {
        if (preg_match('/^\t?(.+?)\t(.+?)\t(.+?)$/', rtrim($l), $matches)) {
            $itaj       = rtrim($matches[3]);
            $itaj_en    = rawurlencode(base64_encode($itaj));
            $host       = $matches[1];
            $bbs        = $matches[2];
            $itaj_view  = htmlspecialchars($itaj, ENT_QUOTES);
            $itaj_q     = '&amp;itaj_en='.$itaj_en;
            echo <<<EOP
            <tr>
            <td><a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}">{$itaj_view}</a></td>
            <td>[ <a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=top{$_conf['k_at_a']}" title="��ԏ�Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=up{$_conf['k_at_a']}" title="���Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=down{$_conf['k_at_a']}" title="����Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=bottom{$_conf['k_at_a']}" title="��ԉ��Ɉړ�">��</a> ]</td>
            <td>[<a href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}&amp;setfavita=0{$_conf['k_at_a']}">�폜</a>]</td>
            </tr>
EOP;
        }
    }

    echo "</table>";
    // PC�iNetFront�����O�j
    if (!$_conf['ktai'] && $_conf['favita_order_dnd'] && !P2Util::isNetFront()) {
        echo '</noscript>';
    }
}

// PC
if (!$_conf['ktai']) {
    echo '<hr>';
    echo $sync_favita_form_ht;
}

//================================================================
// �t�b�^HTML�\��
//================================================================
if ($_conf['ktai']) {
    echo "<hr><div class=\"center\">{$_conf['k_to_index_ht']}</div>";
}

echo '</body></html>';

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
