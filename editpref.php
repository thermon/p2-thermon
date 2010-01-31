<?php
/**
 * rep2 - �ݒ�Ǘ�
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// {{{ �z�X�g�̓����p�ݒ�

$synctitles = array(
    'favita'    => '���C�ɔ�',
    'fav'       => '���C�ɃX��',
    'recent'    => '�ŋߓǂ񂾃X��',
    'res_hist'  => '�������ݗ���',
    'palace'    => '�X���̓a��'
);

// }}}
// {{{ �ݒ�ύX����

// �z�X�g�̓���
if (isset($_POST['sync'])) {
    $sync_boards = array();
    $sync_indexes = array();

    switch ($_POST['sync']) {
    case 'favita':
        if ($_conf['expack.misc.multi_favs']) {
            $sync_boards[] = $_conf['orig_favita_brd'];
            for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
                $sync_boards[] = sprintf('%s/p2_favita%d.brd', $_conf['pref_dir'], $i);
            }
        } else {
            $sync_boards[] = $_conf['favita_brd'];
        }
        break;
    case 'fav':
        if ($_conf['expack.misc.multi_favs']) {
            $sync_indexes[] = $_conf['orig_favlist_idx'];
            for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
                $sync_indexes[] = sprintf('%s/p2_favlist%d.idx', $_conf['pref_dir'], $i);
            }
        } else {
            $sync_indexes[] = $_conf['favlist_idx'];
        }
        break;
    case 'recent':
        $sync_indexes[] = $_conf['recent_idx'];
        break;
    case 'res_hist':
        $sync_indexes[] = $_conf['res_hist_idx'];
        break;
    case 'palace':
        $sync_indexes[] = $_conf['palace_idx'];
        break;
    case 'all':
        if ($_conf['expack.misc.multi_favs']) {
            $sync_boards[] = $_conf['orig_favita_brd'];
            $sync_indexes[] = $_conf['orig_favlist_idx'];
            for ($i = 1; $i <= $_conf['expack.misc.favset_num']; $i++) {
                $sync_boards[] = sprintf('%s/p2_favita%d.brd', $_conf['pref_dir'], $i);
                $sync_indexes[] = sprintf('%s/p2_favlist%d.idx', $_conf['pref_dir'], $i);
            }
        } else {
            $sync_boards[] = $_conf['favita_brd'];
            $sync_indexes[] = $_conf['favlist_idx'];
        }
        $sync_indexes[] = $_conf['recent_idx'];
        $sync_indexes[] = $_conf['res_hist_idx'];
        $sync_indexes[] = $_conf['palace_idx'];
        break;
    }

    foreach ($sync_boards as $brd) {
        if (file_exists($brd)) {
            BbsMap::syncBrd($brd);
        }
    }

    foreach ($sync_indexes as $idx) {
        if (file_exists($idx)) {
            BbsMap::syncIdx($idx);
        }
    }

// ���C�ɓ���Z�b�g�ύX������΁A�ݒ�t�@�C��������������
} elseif ($_conf['expack.misc.multi_favs'] && isset($_POST['favsetlist'])) {
    updateFavSetList();
}

// }}}
// {{{ �����o���p�ϐ�

$ptitle = '�ݒ�Ǘ�';

if ($_conf['ktai']) {
    $status_st      = '�ð��';
    $autho_user_st  = '�F��հ��';
    $client_host_st = '�[��ν�';
    $client_ip_st   = '�[��IP���ڽ';
    $browser_ua_st  = '��׳��UA';
    $p2error_st     = 'rep2 �װ';
} else {
    $status_st      = '�X�e�[�^�X';
    $autho_user_st  = '�F�؃��[�U';
    $client_host_st = '�[���z�X�g';
    $client_ip_st   = '�[��IP�A�h���X';
    $browser_ua_st  = '�u���E�UUA';
    $p2error_st     = 'rep2 �G���[';
}

$autho_user_ht = '';

// }}}

//=========================================================
// HTML�v�����g
//=========================================================
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
    <title>{$ptitle}</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" type="text/css" href="css.php?css=style&amp;skin={$skin_en}">
    <link rel="stylesheet" type="text/css" href="css.php?css=editpref&amp;skin={$skin_en}">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">\n
    <script type="text/javascript" src="js/basic.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/changeskin.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/respopup.js?{$_conf['p2_version_id']}"></script>
    <script type="text/javascript" src="js/editpref.js?{$_conf['p2_version_id']}"></script>\n
EOP;
    $body_at = ' onload="window.top.document.title=window.self.document.title;"';
} elseif (!$_conf['iphone']) {
    $body_at = $_conf['k_colors'];
} else {
    $body_at = '';
}

echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

if (!$_conf['ktai']) {
//<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
    echo "<p id=\"pan_menu\">{$ptitle}</p>\n";
}


echo $_info_msg_ht;
$_info_msg_ht = '';

// �ݒ�v�����g
$aborn_thread_txt   = 'p2_aborn_thread.txt';
$aborn_res_txt      = 'p2_aborn_res.txt';
$aborn_name_txt     = 'p2_aborn_name.txt';
$aborn_mail_txt     = 'p2_aborn_mail.txt';
$aborn_msg_txt      = 'p2_aborn_msg.txt';
$aborn_id_txt       = 'p2_aborn_id.txt';
$ng_name_txt        = 'p2_ng_name.txt';
$ng_mail_txt        = 'p2_ng_mail.txt';
$ng_msg_txt         = 'p2_ng_msg.txt';
$ng_id_txt          = 'p2_ng_id.txt';
// +live �n�C���C�g���[�h�p
$highlight_name_txt = 'p2_highlight_name.txt';
$highlight_mail_txt = 'p2_highlight_mail.txt';
$highlight_msg_txt  = 'p2_highlight_msg.txt';
$highlight_id_txt   = 'p2_highlight_id.txt';
// +Wiki
$aborn_be_txt       = 'p2_aborn_be.txt';
$ng_be_txt          = 'p2_ng_be.txt';
// �A���J�[
$anchor_ignore_word     = 'p2_anchor_ignore.txt';
$anchor_prefix_single   = 'p2_anchor_prefix_single.txt';
$anchor_prefix_double   = 'p2_anchor_prefix_double.txt';
$anchor_prefix_option   = 'p2_anchor_prefix_option.txt';
$anchor_delimiter       = 'p2_anchor_delimiter.txt';
$anchor_range_delimiter = 'p2_anchor_range_delimiter.txt';
$anchor_num_option      = 'p2_anchor_num_option.txt';
$anchor_ranges_option   = 'p2_anchor_ranges_option.txt';

echo '<div>';
echo <<<EOP
<a href="edit_conf_user.php{$_conf['k_at_q']}" class="button">���[�U�ݒ�ҏW</a>
EOP;
if (!$_conf['ktai'] && $_conf['expack.skin.enabled']) {
    $skin_options = array('conf_user_style' => '�W��');
    foreach (array('./skin', './user_skin') as $skin_dir) {
        if (is_dir($skin_dir)) {
            foreach (glob("{$skin_dir}/*.php") as $skin_file) {
                $_name = basename($skin_file, '.php');
                if (!array_key_exists($_name, $skin_options) &&
                    is_file($skin_file) &&
                    preg_match('/^[0-9A-Za-z_\\-]+$/', $_name))
                {
                    $skin_options[$_name] = $_name;
                }
            }
        }
    }
    uksort($skin_options, 'compareSkinNames');
    $skin_options_ht = '';
    foreach ($skin_options as $_name => $_title) {
        $skin_options_ht .= sprintf('<option value="%s"%s>%s</option>',
                                    htmlspecialchars($_name, ENT_QUOTES),
                                    ($_name == $skin_name) ? ' selected' : '',
                                    htmlspecialchars($_title, ENT_QUOTES));
    }
    echo <<<EOP
 �b <a href="edit_user_font.php">�t�H���g�ݒ�ҏW</a>
 �b �X�L��:<form class="inline-form" method="get" action="{$_SERVER['SCRIPT_NAME']}"
 onsubmit="changeSkinAll(this.skin.options[this.skin.selectedIndex].value, '{$_conf['p2_version_id']}'); return false;">
<select name="skin">{$skin_options_ht}</select><input type="submit" value="�ύX">
</form>
EOP;
}
echo '</div>';

// PC�p�\��
if (!$_conf['ktai']) {

    echo "<table id=\"editpref\">\n";

    // {{{ PC - NG���[�h�ҏW
    echo "<tr><td>\n\n";

    echo <<<EOP
<fieldset>
<legend><a href="http://akid.s17.xrea.com/p2puki/pukiwiki.php?%5B%5BNG%A5%EF%A1%BC%A5%C9%A4%CE%C0%DF%C4%EA%CA%FD%CB%A1%5D%5D" target="read">NG���[�h</a>�ҏW</legend>
EOP;
    printEditFileForm($ng_name_txt, '���O');
    printEditFileForm($ng_mail_txt, '���[��');
    printEditFileForm($ng_msg_txt, '�{��');
    printEditFileForm($ng_id_txt, 'ID');
    // +Wiki
    printEditFileForm($ng_be_txt, 'BE');
    echo <<<EOP
</fieldset>\n\n
EOP;

    echo "</td>";

    // }}}
    // {{{ PC - ���ځ[�񃏁[�h�ҏW

    echo "<td>\n\n";

    echo <<<EOP
<fieldset>
<legend>���ځ[�񃏁[�h�ҏW</legend>\n
EOP;
    printEditFileForm($aborn_res_txt, '���X');
    printEditFileForm($aborn_name_txt, '���O');
    printEditFileForm($aborn_mail_txt, '���[��');
    printEditFileForm($aborn_msg_txt, '�{��');
    printEditFileForm($aborn_id_txt, 'ID');
    printEditFileForm($aborn_thread_txt, '�X���b�h');
    // +Wiki
    printEditFileForm($aborn_be_txt, 'BE');
    echo <<<EOP
</fieldset>\n
EOP;

    echo "</td></tr>";

    // }}}
	// {{{ PC - +live �n�C���C�g���[�h�ҏW

	echo "<td>\n\n";

	echo <<<EOP
<fieldset>
<legend>�n�C���C�g���[�h�ҏW</legend>\n
EOP;
	printEditFileForm($highlight_name_txt, '���O');
	printEditFileForm($highlight_mail_txt, '���[��');
	printEditFileForm($highlight_msg_txt, '�{��');
	printEditFileForm($highlight_id_txt, 'ID');
	echo <<<EOP
</fieldset>\n
EOP;

	echo "</td></tr>";

	// }}}
	// {{{ PC - �A���J�[�ҏW

echo "<td colspan=\"2\">\n\n";
echo <<<EOP
<fieldset>
<legend>�A���J�[�̐��K�\���ݒ�</legend>
EOP;
    printEditFileForm($anchor_prefix_single,	"�V���O�����p�q�i&gt;�j");
    printEditFileForm($anchor_prefix_double,	"�_�u�����p�q�i&gt;&gt;�j");
    printEditFileForm($anchor_prefix_option,	"���p�q�I�v�V����");
    printEditFileForm($anchor_delimiter,		"�ԍ��̋�؂蕶���i�C�j");
    printEditFileForm($anchor_range_delimiter,	"�ԍ��͈͎̔w�蕶���i�|�j");
    printEditFileForm($anchor_num_option,		"���X�ԍ��I�v�V����");
    printEditFileForm($anchor_ranges_option,	"�A���J�[�I�v�V����");
    printEditFileForm($anchor_ignore_word,		"�A���J�[�ł��邱�Ƃ�ے肷��㑱����");
echo <<<EOP
</fieldset>\n
EOP;
echo "</td></tr>\n\n";

	// }}}
    // {{{ PC - �z�X�g�̓��� HTML�̃Z�b�g

    echo <<<EOP
<tr><td colspan="2">
<fieldset>
<legend>�z�X�g�̓��� �i2ch�̔ړ]�ɑΉ����܂��j</legend>
EOP;
    echo getSyncFavoritesFormHt('all', '���ׂ�');
    foreach ($synctitles as $syncmode => $syncname) {
        echo getSyncFavoritesFormHt($syncmode, $syncname);
    }
    echo <<<EOP
</fieldset>
</td></tr>\n
EOP;

    // }}}
    // {{{ PC - �Z�b�g�؂�ւ��E���̕ύX

    if ($_conf['expack.misc.multi_favs']) {
        echo "<tr><td colspan=\"2\">\n\n";

        echo <<<EOP
<form action="editpref.php" method="post" accept-charset="{$_conf['accept_charset']}" target="_self" style="margin:0">
    <input type="hidden" name="favsetlist" value="1">
    <fieldset>
        <legend>�Z�b�g�؂�ւ��E���̕ύX�i�Z�b�g������ɂ���ƃf�t�H���g�̖��O�ɖ߂�܂��j</legend>
        <table>
            <tr>\n
EOP;
        echo "<td>\n";
        echo getFavSetListFormHt('m_favlist_set', '���C�ɃX��');
        echo "</td><td>\n";
        echo getFavSetListFormHt('m_favita_set', '���C�ɔ�');
        echo "</td><td>\n";
        echo getFavSetListFormHt('m_rss_set', 'RSS');
        echo "</td>\n";
        echo <<<EOP
            </tr>
        </table>
        <div>
            <input type="submit" value="�ύX">
        </div>
    </fieldset>
    {$_conf['detect_hint_input_ht']}{$_conf['k_input_ht']}
</form>\n\n
EOP;

        echo "</td></tr>\n\n";
    }

    // }}}
}

// �g�їp�\��
if ($_conf['ktai']) {
    echo <<<EOP
<hr>
<p>����/NGܰ�ޕҏW</p>
<form method="GET" action="edit_aborn_word.php">
{$_conf['k_input_ht']}
<select name="file">
<option value="{$aborn_thread_txt}">����:��</option>
<option value="{$aborn_name_txt}">����:���O</option>
<option value="{$aborn_mail_txt}">����:Ұ�</option>
<option value="{$aborn_msg_txt}">����:�{��</option>
<option value="{$aborn_id_txt}">����:ID</option>
<option value="{$aborn_be_txt}">����:BE</option>
<option value="{$ng_name_txt}">NG:���O</option>
<option value="{$ng_mail_txt}">NG:Ұ�</option>
<option value="{$ng_msg_txt}">NG:�{��</option>
<option value="{$ng_id_txt}">NG:ID</option>
<option value="{$ng_id_txt}">NG:BE</option>
</select>
<input type="submit" value="�ҏW">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="file" value="{$aborn_res_txt}">
<input type="submit" value="����ڽ�ҏW">
</form>
<hr>
EOP;

    echo "<p>�A���J�[�F���̐ݒ�</p>\n";
	echo <<<EOP
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_prefix_single}">
<input type="submit" value="�V���O�����p�q(&gt;)">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_prefix_double}">
<input type="submit" value="�_�u�����p�q(&gt;&gt;)">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_prefix_option}">
<input type="submit" value="���p�q�I�v�V����">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_delimiter}">
<input type="submit" value="�ԍ��̋�؂蕶��(,)">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_range_delimiter}">
<input type="submit" value="�ԍ��͈͎̔w�蕶��(-)">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_num_option}">
<input type="submit" value="���X�ԍ��I�v�V����">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_ranges_option}">
<input type="submit" value="�A���J�[�I�v�V����">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$anchor_ignore_word}">
<input type="submit" value="�A���J�[�ł��邱�Ƃ�ے肷��㑱����">
</form>
EOP;

    echo "<p>νĂ̓����i2ch�̔ړ]�ɑΉ����܂��j</p>\n";
    echo getSyncFavoritesFormHt('all', '���ׂ�');
    foreach ($synctitles as $syncmode => $syncname) {
        echo getSyncFavoritesFormHt($syncmode, $syncname);
    }

    // {{{ �g�� - �Z�b�g�؂�ւ�

    if ($_conf['expack.misc.multi_favs']) {
        echo <<<EOP
<hr>
<p>���C�ɽڥ���C�ɔ¥RSS�̾�Ă�I��</p>
<form action="editpref.php" method="post" accept-charset="{$_conf['accept_charset']}" target="_self">
{$_conf['k_input_ht']}
EOP;
        echo getFavSetListFormHtK('m_favlist_set', '���C�ɽ�'), '<br>';
        echo getFavSetListFormHtK('m_favita_set', '���C�ɔ�'), '<br>';
        echo getFavSetListFormHtK('m_rss_set', 'RSS'), '<br>';
        echo <<<EOP
<input type="submit" value="�ύX">
</form>
EOP;
    }

    include_once P2_LIB_DIR . '/wiki/editpref.inc.php';

}

// {{{ �V���܂Ƃߓǂ݂̃L���b�V���\��

$matome_cache_list = MatomeCacheList::getList();
if ($matome_cache_list) {
    $ckeyprefixlen = strlen(MatomeCacheList::getKeyPrefix());
    $i = 0;

    if ($_conf['ktai']) {
        echo "<hr>\n<p>�V���܂Ƃߓǂ݃L���b�V��</p>\n";
    } else {
        echo "<tr><td colspan=\"2\">\n";
        echo "<fieldset>\n<legend>�V���܂Ƃߓǂ݃L���b�V��</legend>\n";
        echo "<ul id=\"matome_cache\">\n";
    }

    foreach ($matome_cache_list as $cache_key => $cache_info) {
        // PC�E�g�ы��ʕϐ�
        $i++;
        $ckey = substr($cache_key, $ckeyprefixlen);
        $clink = 'read_new_cache.php?ckey=' . rawurlencode($ckey) . $_conf['k_at_a'];
        if ($cache_info['title']) {
            $ctitle = $cache_info['title'];
        } else {
            $ctitle = $ckey;
        }
        $cnumthreads = count($cache_info['threads']);

        if ($_conf['ktai']) {
            // �L���b�V���ւ̃����N�A�g�їp
            if ($_conf['iphone']) {
                $cdate = date('y/m/d H:i', $cache_info['time']);
                echo "<p>{$cdate} [<a href=\"{$clink}\">{$ctitle}</a>]</p>";
            } else {
                $cdate = date('y/m/d G:i', $cache_info['time']);
                echo "<p>{$cdate}<br>[<a href=\"{$clink}\">{$ctitle}</a>]</p>";
            }
        } else {
            // �L���b�V���ւ̃����N�APC�p
            $cid = 'matome_cache_meta' . $i;
            $cdate = date('Y/m/d H:i:s', $cache_info['time']);
            if ($cnumthreads) {
                $cpopup_at = " onmouseover=\"showCacheMetaData('{$cid}', event)\"";
                $cpopup_at .= " onmouseout=\"hideCacheMetaData('{$cid}')\"";
            } else {
                $cpopup_at = '';
            }
            echo "<li><span class=\"matome_cache_date\">{$cdate}</span> ";
            echo "[<a href=\"{$clink}\" target=\"read\"{$cpopup_at}>{$ctitle}</a>]";
            if ($cnumthreads) {
                echo "<div class=\"popup_element\" id=\"{$cid}\"{$cpopup_at}>";
            }
        }

        // �܂Ƃߓǂ݂Ɋ܂܂��e�X���b�h�ւ̃����N
        if ($cnumthreads && (!$_conf['ktai'] || $_conf['iphone'])) {
            if ($_conf['iphone']) {
                $ctarget_at = '';
            } else {
                $ctarget_at = ' target="read"';
            }

            echo '<ul>';
            foreach ($cache_info['threads'] as $cthread) {
                $cthreadlink = $_conf['read_php']
                             . '?host=' . rawurlencode($cthread['host'])
                             . '&amp;bbs=' . rawurlencode($cthread['bbs'])
                             . '&amp;key=' . rawurlencode($cthread['key'])
                             . '&amp;ls=' . rawurlencode($cthread['ls'])
                             . '&amp;offline=true' . $_conf['k_at_a'];
                echo "<li><a href=\"{$cthreadlink}\"{$ctarget_at}>{$cthread['title']}</a></li>";
            }
            echo '</ul>';
        }

        // ���^�O�APC�p
        if (!$_conf['ktai']) {
            if ($cnumthreads) {
                echo '</div>';
            }
            echo "</li>\n";
        }
    }

    // ���^�O�APC�p
    if (!$_conf['ktai']) {
        echo "</ul>\n";
        echo "</fieldset>\n";
        echo "</td></tr>\n";
    }
}

// }}}

if ($_conf['ktai']) {
    echo "<hr><div class=\"center\">{$_conf['k_to_index_ht']}</div>";
} else {
    echo "</table>\n";
}

echo '</body></html>';

exit;

//==============================================================================
// �֐�
//==============================================================================
// {{{ printEditFileForm()

/**
 * �ݒ�t�@�C���ҏW�E�C���h�E���J���t�H�[��HTML���v�����g����
 *
 * @param   string  $filename       �ҏW����t�@�C����
 * @param   string  $submit_value   submit�{�^���̒l
 * @return  void
 */
function printEditFileForm($filename, $submit_value)
{
    global $_conf;

    $path = $_conf['pref_dir'] . '/' . $filename;

    if ((file_exists($path) && is_writable($path)) ||
        (!file_exists($path) && is_writable(dirname($path)))
    ) {
        $onsubmit = '';
        $disabled = '';
    } else {
        $onsubmit = ' onsubmit="return false;"';
        $disabled = ' disabled';
    }

    $rows = 36; // 18
    $cols = 92; // 90

    if ($filename == 'p2_aborn_thread.txt' ||
        preg_match('/^p2_(aborn|ng|highlight)_(name|mail|id|msg)\\.txt$/', $filename))
    {
        $edit_php = 'edit_aborn_word.php';
        $target = '_self';
    } else {
        $edit_php = 'editfile.php';
        $target = 'editfile';
    }
    $filename_ht = htmlspecialchars($filename, ENT_QUOTES);

    $ht = <<<EOFORM
<form action="{$edit_php}" method="GET" target="{$target}" class="inline-form"{$onsubmit}>
    {$_conf['k_input_ht']}
    <input type="hidden" name="file" value="{$filename_ht}">
    <input type="hidden" name="encode" value="Shift_JIS">
    <input type="hidden" name="rows" value="{$rows}">
    <input type="hidden" name="cols" value="{$cols}">
    <input type="submit" value="{$submit_value}"{$disabled}>
</form>\n
EOFORM;

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    echo $ht;
}

// }}}
// {{{ getSyncFavoritesFormHt()

/**
 * �z�X�g�̓����p�t�H�[����HTML���擾����
 *
 * @param   string  $path_value     ��������t�@�C���̃p�X
 * @param   string  $submit_value   submit�{�^���̒l
 * @return  string
 */
function getSyncFavoritesFormHt($path_value, $submit_value)
{
    global $_conf;

    $ht = <<<EOFORM
<form action="editpref.php" method="POST" target="_self" class="inline-form">
    {$_conf['k_input_ht']}
    <input type="hidden" name="sync" value="{$path_value}">
    <input type="submit" value="{$submit_value}">
</form>\n
EOFORM;

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    return $ht;
}

// }}}
// {{{ getFavSetListFormHt()

/**
 * ���C�ɓ���Z�b�g�؂�ւ��E�Z�b�g���ύX�p�t�H�[����HTML���擾����iPC�p�j
 *
 * @param   string  $set_name   ���������p�Z�b�g��
 * @param   string  $set_title  HTML�\���p�Z�b�g��
 * @return  string
 */
function getFavSetListFormHt($set_name, $set_title)
{
    global $_conf;

    if (!($titles = FavSetManager::getFavSetTitles($set_name))) {
        $titles = array();
    }

    $radio_checked = array_fill(0, $_conf['expack.misc.favset_num'] + 1, '');
    $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
    $radio_checked[$i] = ' checked';
    $ht = <<<EOFORM
<fieldset>
    <legend>{$set_title}</legend>\n
EOFORM;
    for ($j = 0; $j <= $_conf['expack.misc.favset_num']; $j++) {
        if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
            $titles[$j] = ($j == 0) ? $set_title : $set_title . $j;
        }
        $ht .= <<<EOFORM
    <input type="radio" name="{$set_name}" value="{$j}"{$radio_checked[$j]}>
    <input type="text" name="{$set_name}_titles[{$j}]" size="18" value="{$titles[$j]}">
    <br>\n
EOFORM;
    }
    $ht .= <<<EOFORM
</fieldset>\n
EOFORM;

    return $ht;
}

// }}}
// {{{ getFavSetListFormHtK()

/**
 * ���C�ɓ���Z�b�g�؂�ւ��p�t�H�[����HTML���擾����i�g�їp�j
 *
 * @param   string  $set_name   ���������p�Z�b�g��
 * @param   string  $set_title  HTML�\���p�Z�b�g��
 * @return  string
 */
function getFavSetListFormHtK($set_name, $set_title)
{
    global $_conf;

    if (!($titles = FavSetManager::getFavSetTitles($set_name))) {
        $titles = array();
    }

    $selected = array_fill(0, $_conf['expack.misc.favset_num'] + 1, '');
    $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
    $selected[$i] = ' selected';
    $ht = "<select name=\"{$set_name}\">";
    for ($j = 0; $j <= $_conf['expack.misc.favset_num']; $j++) {
        if ($j == 0) {
            if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
                $titles[$j] = $set_title;
            }
            $titles[$j] .= ' (��̫��)';
        } else {
            if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
                $titles[$j] = $set_title . $j;
            }
        }
        if (!empty($_conf['mobile.save_packet'])) {
            $titles[$j] = mb_convert_kana($titles[$j], 'rnsk');
        }
        $ht .= "<option value=\"{$j}\"{$selected[$j]}>{$titles[$j]}</option>";
    }
    $ht .= "</select>\n";

    return $ht;
}

// }}}
// {{{ updateFavSetList()

/**
 * ���C�ɓ���Z�b�g���X�g���X�V����
 *
 * @return  boolean �X�V�ɐ���������TRUE, ���s������FALSE
 */
function updateFavSetList()
{
    global $_conf, $_info_msg_ht;

    if (file_exists($_conf['expack.misc.favset_file'])) {
        $setlist_titles = FavSetManager::getFavSetTitles();
    } else {
        FileCtl::make_datafile($_conf['expack.misc.favset_file']);
    }
    if (empty($setlist_titles)) {
        $setlist_titles = array();
    }

    $setlist_names = array('m_favlist_set', 'm_favita_set', 'm_rss_set');
    foreach ($setlist_names as $setlist_name) {
        if (isset($_POST["{$setlist_name}_titles"]) && is_array($_POST["{$setlist_name}_titles"])) {
            $setlist_titles[$setlist_name] = array();
            for ($i = 0; $i <= $_conf['expack.misc.favset_num']; $i++) {
                if (!isset($_POST["{$setlist_name}_titles"][$i])) {
                    $setlist_titles[$setlist_name][$i] = '';
                    continue;
                }
                $newname = trim($_POST["{$setlist_name}_titles"][$i]);
                $newname = preg_replace('/\r\n\t/', ' ', $newname);
                $newname = htmlspecialchars($newname, ENT_QUOTES);
                $setlist_titles[$setlist_name][$i] = $newname;
            }
        }
    }

    $newdata = serialize($setlist_titles);
    if (FileCtl::file_write_contents($_conf['expack.misc.favset_file'], $newdata) === FALSE) {
        $_info_msg_ht .= "<p>p2 error: {$_conf['expack.misc.favset_file']} �ɂ��C�ɓ���Z�b�g�ݒ���������߂܂���ł����B";
        return FALSE;
    }

    return TRUE;
}

// }}}
// {{{ compareSkinNames()

/**
 * �X�L���̃��X�g���\�[�g���邽�߂̃R�[���o�b�N�֐�
 *
 * @param string $a
 * @param string $b
 * @return int
 */
function compareSkinNames($a, $b)
{
    if ($a == 'conf_user_style') {
        return -1;
    }
    if ($b == 'conf_user_style') {
        return 1;
    }
    return strcmp($a, $b);
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
