<?php
/**
 * rep2 - iPhone/iPod Touch��p���j���[ (�viui)
 *
 * @link http://code.google.com/p/iui/
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/menu_iphone.inc.php';

$_login->authorize(); //���[�U�F��

if ($_conf['view_forced_by_query']) {
    output_add_rewrite_var('b', $_conf['b']);
}

// {{{ ���X�g (Ajax)

if (isset($_GET['cateid'])) {
    menu_iphone_ajax('menu_iphone_show_board_menu', (int)$_GET['cateid']);
    exit;
}

// }}}
// {{{ ���� (Ajax)

if (isset($_POST['word'])) {
    $word = menu_iphone_unicode_urldecode($_POST['word']);
    if (substr_count($word, '.') == strlen($word)) {
        $word = '';
    }

    if (strlen($word) > 0 && p2_set_filtering_word($word, 'and') !== null) {
        menu_iphone_ajax('menu_iphone_show_matched_boards', $word);
    } else {
        header('Content-Type: application/xml; charset=UTF-8');
        echo mb_convert_encoding('<div class="panel">�����ȃL�[���[�h�ł��B</div>', 'UTF-8', 'CP932');
    }
    exit;
}

// }}}
// {{{ HTML�o��
// {{{ �w�b�_
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=Shift_JIS" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes" />
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
    <title>rep2</title>
    <link rel="stylesheet" type="text/css" href="iui/iui.css?<?php echo $_conf['p2_version_id']; ?>" />
    <link rel="stylesheet" type="text/css" href="css/menu_i.css?<?php echo $_conf['p2_version_id']; ?>" />
    <link rel="apple-touch-icon" type="image/png" href="img/touch-icon/p2-serif.png" />
    <script type="text/javascript" src="iui/iui.js?<?php echo $_conf['p2_version_id']; ?>"></script>
    <script type="text/javascript" src="js/json2.js?<?php echo $_conf['p2_version_id']; ?>"></script>
    <script type="text/javascript" src="js/iphone.js?<?php echo $_conf['p2_version_id']; ?>"></script>
    <script type="text/javascript" src="js/menu_i.js?<?php echo $_conf['p2_version_id']; ?>"></script>
<?php
// {{{ �w��T�u���j���[�֎����ňړ�
// $hashes�̎擾���������Ȃ̂ŕ���B
/*
if (isset($hashes) && is_array($hashes) && count($hashes)) {
    $js = '';
    $last = array_pop($hashes);
    while (($hash = array_shift($hashes)) !== null) {
        $hash = trim($hash);
        if ($hash === '') {
            continue;
        }
        $js .= "'" . StrCtl::toJavaScript($hash) . "',";
    }
    $hash = trim($last);
    if ($hash !== '') {
        $js .= "'" . StrCtl::toJavaScript($hash) . "'";
    } else {
        $js .= "'_'";
    }
?>
    <script type="text/javascript">
    // <![CDATA[
    window.addEventListener('load', function(event) {
        window.removeEventListener(event.type, arguments.callee, false);

        window.setTimeout(function(subMenus, contextNode, delayMsec) {
            var id, anchor, child, evt;

            if (!subMenus.length || !contextNode) {
                return;
            }

            id = subMenus.shift();
            anchor = document.evaluate('./li/a[@href="#' + id + '"]',
                                       contextNode,
                                       null,
                                       XPathResult.FIRST_ORDERED_NODE_TYPE,
                                       null).singleNodeValue;
            child = document.getElementById(id);

            if (anchor && child) {
                evt = document.createEvent('MouseEvents');
                evt.initMouseEvent('click', true, true, window,
                                   0, 0, 0, 0, 0,
                                   false, false, false, false, 0, null);
                anchor.dispatchEvent(evt);

                if (subMenus.length) {
                    contextNode = child;
                    window.setTimeout(arguments.callee, delayMsec,
                                      subMenus, child, delayMsec);
                }
            }
        }, 200, [<?php echo $js; ?>], document.getElementById('top'), 200);
    });
    // ]]>
    </script>
<?php
}*/
// }}}
?>
</head>
<body>

<?php
// }}}
// {{{ �c�[���o�[
?>

<div class="toolbar">
    <h1 id="pageTitle"></h1>
    <a id="backButton" class="button" style="z-index:2" href="#"></a>
    <a class="button leftButton" href="#boardSearch">��</a>
    <a class="button" href="#threadSearch">��</a>
</div>

<?php
// }}}
// {{{ �g�b�v���j���[
?>

<ul id="top" title="rep2" selected="true">
<?php if (P2Util::hasInfoHtml()) { ?>
    <li><a href="#info_msg" class="color-r">�G���[</a></li>
<?php } ?>
    <li class="group">���X�g</li>
<?php if ($_conf['expack.misc.multi_favs']) { ?>
    <li><a href="#fav">���C�ɃX��</a></li>
<?php } else { ?>
    <?php /* <li><a href="subject.php?spmode=fav&amp;sb_view=shinchaku" target="_self">���C�ɃX���̐V��</a></li> */ ?>
    <li><a href="subject.php?spmode=fav" target="_self">���C�ɃX��</a></li>
<?php } ?>
    <li><a href="#favita">���C�ɔ�</a></li>
    <li><a href="menu_i.php?cateid=0">���X�g</a></li>
    <li><a href="subject.php?spmode=palace&amp;norefresh=1" target="_self">�X���̓a��</a></li>

    <li class="group">����</li>
    <?php /* <li><a href="subject.php?spmode=recent&amp;sb_view=shinchaku" target="_self">�ŋߓǂ񂾃X���̐V��</a></li> */ ?>
    <li><a href="subject.php?spmode=recent" target="_self">�ŋߓǂ񂾃X��</a></li>
<?php if ($_conf['res_hist_rec_num']) { ?>
    <li><a href="subject.php?spmode=res_hist" target="_self">�������ݗ���</a></li>
<?php if ($_conf['res_write_rec']) { ?>
    <li><a href="read_res_hist.php" target="_self">�������ݗ����̓��e</a></li>
<?php } } ?>

    <li class="group">expack</li>
<?php if ($_conf['expack.rss.enabled']) { ?>
    <li><a href="#rss">RSS</a></li>
<?php } ?>
<?php if ($_conf['expack.ic2.enabled'] == 2 || $_conf['expack.ic2.enabled'] == 3) { ?>
    <li><a href="iv2.php?reset_filter=1" target="_self">�摜�L���b�V���ꗗ</a></li>
<?php } ?>
    <li><a href="#tgrep">�X���b�h����</a></li>

    <li class="group">�Ǘ�</li>
    <li><a href="editpref.php" target="_self">�ݒ�Ǘ�</a></li>
    <li><a href="setting.php" target="_self">���O�C���Ǘ�</a></li>
    <li><a href="#login_info">���O�C�����</a></li>
</ul>

<?php
// }}}
// {{{ �G���[

if (P2Util::hasInfoHtml()) { 
    echo '<div id="info_msg" class="panel" title="�G���[">';
    P2Util::printInfoHtml();
    echo '</div>';
}

// }}}
// {{{ �T�u���j���[

if ($_conf['expack.misc.multi_favs']) {
    // {{{ ���C�ɃX��

    $favlist = FavSetManager::getFavSetTitles('m_favlist_set');
    if (!$favlist) {
        $favlist = array();
    }
    $fav_elems = '';
    $fav_new_elems = '';
    $fav_elem_prefix = '';

    for ($no = 0; $no <= $_conf['expack.misc.favset_num']; $no++) {
        if (isset($favlist[$no]) && strlen($favlist[$no]) > 0) {
            $name = $favlist[$no];
        } else {
            $favlist[$no] = $name = ($no ? "���C�ɃX��{$no}" : '���C�ɃX��');
        }
        $fav_url = "subject.php?spmode=fav&amp;m_favlist_set={$no}";
        $fav_elems .= "<li><a href=\"{$fav_url}\" target=\"_self\">{$name}</a></li>";
        //$fav_new_elems .= "<li><a href=\"{$fav_url}&amp;sb_view=shinchaku\" target=\"_self\">{$name}</a></li>";
    }

    echo '<ul id="fav" title="���C�ɃX��">';
    //echo '<li class="group">�V��</li>';
    //echo $fav_new_elems;
    //echo '<li class="group">�S��</li>';
    echo $fav_elems;
    echo "</ul>\n";

    // }}}
    // {{{ ���C�ɔ�

    $favita = FavSetManager::getFavSetTitles('m_favita_set');
    if (!$favita) {
        $favita = array();
    }

    echo '<ul id="favita" title="���C�ɔ�">';

    for ($no = 0; $no <= $_conf['expack.misc.favset_num']; $no++) {
        if (isset($favita[$no]) && strlen($favita[$no]) > 0) {
            $name = $favita[$no];
        } else {
            $favita[$no] = $name = ($no ? "���C�ɔ�{$no}" : '���C�ɔ�');
        }
        echo "<li><a href=\"#favita{$no}\">{$name}</a></li>";
    }

    echo "</ul>\n";

    $orig_favita_brd = $_conf['favita_brd'];

    foreach ($favita as $no => $name) {
        $_conf['favita_brd'] = $_conf['pref_dir'] . DIRECTORY_SEPARATOR
            . ($no ? "p2_favita{$no}.brd" : 'p2_favita.brd');
        menu_iphone_show_favorite_boards($name, $no);
    }

    $_conf['favita_brd'] = $orig_favita_brd;

    // }}}
    // {{{ RSS

    if ($_conf['expack.rss.enabled']) { 
        $rss = FavSetManager::getFavSetTitles('m_rss_set');
        if (!$rss) {
            $rss = array();
        }

        echo '<ul id="rss" title="RSS">';

        for ($no = 0; $no <= $_conf['expack.misc.favset_num']; $no++) {
            if (isset($rss[$no]) && strlen($rss[$no]) > 0) {
                $name = $rss[$no];
            } else {
                $rss[$no] = $name = ($no ? "RSS{$no}" : 'RSS');
            }
            echo "<li><a href=\"#rss{$no}\">{$name}</a></li>";
        }

        echo "</ul>\n";

        $orig_rss_setting_path = $_conf['expack.rss.setting_path'];

        foreach ($rss as $no => $name) {
            $_conf['expack.rss.setting_path'] = $_conf['pref_dir'] . DIRECTORY_SEPARATOR
                    . ($no ? "p2_rss{$no}.txt" : 'p2_rss.txt');
            menu_iphone_show_feed_list($name, $no);
        }

        $_conf['expack.rss.setting_pat'] = $orig_rss_setting_path;
    }

    // }}}
} else {
    menu_iphone_show_favorite_boards('���C�ɔ�');

    if ($_conf['expack.rss.enabled']) { 
        menu_iphone_show_feed_list('RSS');
    }
}

// }}}
// {{{ ���O�C�����
?>

<div id="login_info" class="panel" title="���O�C�����">
<h2>�F�؃��[�U</h2>
<p><strong><?php echo $_login->user_u; ?></strong> - <?php echo date('Y/m/d (D) G:i:s'); ?></p>
<?php if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) { ?>
<h2>�O��̃��O�C��</h2>
<pre style="word-wrap:break-word;word-break:break-all"><?php
if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
    $log_hd = array_map('htmlspecialchars', $log);
    echo <<<EOP
<strong>DATE:</strong> {$log_hd['date']}
<strong>USER:</strong> {$log_hd['user']}
<strong>  IP:</strong> {$log_hd['ip']}
<strong>HOST:</strong> {$log_hd['host']}
<strong>  UA:</strong> {$log_hd['ua']}
<strong>REFERER:</strong> {$log_hd['referer']}
EOP;
}
?></pre>
<?php } ?>
</div>

<?php
// }}}
// {{{ �X���b�h����
?>

<ul id="tgrep" title="�X���b�h����">
    <li><a href="#tgrep_info">�X���b�h�����ɂ���</a></li>
    <li class="group">�N�C�b�N�T�[�`</li>
    <?php require_once P2EX_LIB_DIR . '/tgrep/menu_quick.inc.php'; ?>
    <li class="group">��������</li>
    <?php require_once P2EX_LIB_DIR . '/tgrep/menu_recent.inc.php'; ?>
</ul>

<?php
// }}}
// {{{ �X���b�h�����ɂ���
?>

<div id="tgrep_info" class="panel" title="tGrep�ɂ���">
<ul>
    <li>rep2 �@�\�g���p�b�N�̃X���b�h������ tGrep (<a href="http://page2.xrea.jp/tgrep/" target="_blank">http://page2.xrea.jp/tgrep/</a>) �𗘗p���Ă��܂��B</li>
    <li>iPhone�ł̓��j���[�E��́u�ځv�{�^�����^�b�v���Č����_�C�A���O���猟�����܂��B</li>
    <li>�L�[���[�h�̓X�y�[�X��؂��3�܂Ŏw��ł��A���ׂĂ��܂ނ��̂����o����܂��B</li>
    <li>2�ڈȍ~�̃L�[���[�h�œ��� - (���p�}�C�i�X) ������ƁA������܂܂Ȃ����̂����o����܂��B</li>
    <li>&quot; �܂��� &#39; �ň͂܂ꂽ�����͈�̃L�[���[�h�Ƃ��Ĉ����܂��B</li>
    <li>�L�[���[�h�̑S�p���p�A�啶���������͖�������܂��B</li>
    <li>�f�[�^�x�[�X�̍X�V��3���Ԃ�1��ŁA���X���Ȃǂ͍X�V���_�ł̒l�ł��B</li>
</ul>
</div>

<?php
// }}}
// {{{ �����_�C�A���O
?>

<form id="boardSearch" class="dialog"
  method="post" action="menu_i.php"
  accept-charset="<?php echo $_conf['accept_charset']; ?>">
<fieldset>
    <h1>����</h1>
    <a class="button leftButton" type="cancel">���</a>
    <a class="button blueButton" type="submit">����</a>
    <label>word:</label>
    <input type="text" name="word" autocorrect="off" autocapitalize="off" />
</fieldset>
</form>

<?php
// }}}
// {{{ �X���b�h�����_�C�A���O
?>

<form id="threadSearch" class="dialog"
  method="post" action="tgrepc.php"
  accept-charset="<?php echo $_conf['accept_charset']; ?>">
<fieldset>
    <h1>�X���b�h����</h1>
    <a class="button leftButton" type="cancel">���</a>
    <a class="button blueButton" type="submit">����</a>
    <label>word:</label>
    <input type="text" name="iq" autocorrect="off" autocapitalize="off" />
</fieldset>
</form>

<?php
// }}}
?>

</body>
</html>
<?php
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
