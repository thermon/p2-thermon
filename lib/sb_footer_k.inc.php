<?php
/**
 * rep2 - �T�u�W�F�N�g - �g�уt�b�^�\��
 * for subject.php
 */

//=================================================
//�t�b�^�v�����g
//=================================================
$mae_ht = "";
$tugi_ht = "";
$bbs_q = "&amp;bbs=".$aThreadList->bbs;

if (!empty($GLOBALS['wakati_words'])) {
    $word_at = '&amp;method=similar&amp;word=' . rawurlencode($GLOBALS['wakati_word']);
    $word_input_ht = '<input type="hidden" name="method" value="similar">';
    $word_input_ht .= '<input type="hidden" name="word" value="' . htmlspecialchars($GLOBALS['wakati_word'], ENT_QUOTES) . '">';
} elseif ($word) {
    $word_at = '&amp;word=' . rawurldecode($word);
    $word_input_ht = '<input type="hidden" name="word" value="' . htmlspecialchars($word, ENT_QUOTES) . '">';
    if (isset($sb_filter['method']) && $sb_filter['method'] == 'or') {
        $word_at .= '&amp;method=or';
        $word_input_ht = '<input type="hidden" name="method" value="or">';
    }
} else {
    $word_at = '';
    $word_input_ht = '';
}

if ($aThreadList->spmode == "fav" && $sb_view == "shinchaku") {
    $allfav_ht = <<<EOP
<div class=\"pager\"><a href="{$_conf['subject_php']}?spmode=fav{$norefresh_q}{$_conf['k_at_a']}">�S�Ă̂��C�ɽڂ�\��</a></div>
EOP;
} else {
    $allfav_ht = '';
}

// �y�[�W�^�C�g������HTML�ݒ� ====================================
if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}"{$_conf['k_accesskey_at']['up']}">{$_conf['k_accesskey_st']['up']}<b>{$aThreadList->itaj}</b></a> (���ݒ�)
EOP;
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}"{$_conf['k_accesskey_at']['up']}">{$_conf['k_accesskey_st']['up']}<b>{$aThreadList->itaj}</b></a> (dat�q��)
EOP;
} elseif (!empty($ptitle_url)) {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}" class="nobutton"><b>{$ptitle_hd}</b></a>
EOP;
} else {
    $ptitle_ht = <<<EOP
<b>{$ptitle_hd}</b>
EOP;
}

// {{{ �i�r

if (!empty($_REQUEST['sb_view'])) {
    $sb_view_at = "&amp;sb_view=" . rawurlencode($_REQUEST['sb_view']);
    $sb_view_input_ht = '<input type="hidden" name="sb_view" value="' . htmlspecialchars($_REQUEST['sb_view'], ENT_QUOTES) . '">';
} else {
    $sb_view_at = '';
    $sb_view_input_ht = '';
}

if (!empty($_REQUEST['rsort'])) {
    $sb_view_at .= '&amp;rsort=1';
    $sb_view_input_ht .= '<input type="hidden" name="rsort" value="1">';
}

if ($aThreadList->spmode == 'merge_favita' && $_conf['expack.misc.multi_favs']) {
    $sb_view_at .= $_conf['m_favita_set_at_a'];
    $sb_view_input_ht .= $_conf['m_favita_set_input_ht'];
}

if ($disp_navi['from'] > 1) {
    $mae_ht = <<<EOP
<a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;from={$disp_navi['mae_from']}{$sb_view_at}{$word_at}{$_conf['k_at_a']}"{$_conf['k_accesskey_at']['prev']}>{$_conf['k_accesskey_st']['prev']}�O</a>
EOP;
} else {
    $mae_ht = '';
}

if ($disp_navi['tugi_from'] <= $sb_disp_all_num) {
    $tugi_ht = <<<EOP
<a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;from={$disp_navi['tugi_from']}{$sb_view_at}{$word_at}{$_conf['k_at_a']}"{$_conf['k_accesskey_at']['next']}>{$_conf['k_accesskey_st']['next']}��</a>
EOP;
} else {
    $tugi_ht = '';
}

if ($disp_navi['from'] == $disp_navi['end']) {
    $sb_range_on = $disp_navi['from'];
} else {
    $sb_range_on = "{$disp_navi['from']}-{$disp_navi['end']}";
}

if (!$disp_navi['all_once']) {
    if ($_conf['mobile.sb_disp_range'] < 1) {
        $k_sb_navi_select_from_ht = '<option value="1">$_conf[&#39;mobile.sb_disp_range&#39;] �̒l���s���ł�</option>';
    } else {
        if ($disp_navi['offset'] % $_conf['mobile.sb_disp_range']) {
            $k_sb_navi_select_from_ht = "<option value=\"{$disp_navi['from']}\" selected>{$sb_range_on}</option>";
        } else {
            $k_sb_navi_select_from_ht = '';
        }

        /*$k_sb_navi_select_optgroup = $_conf['mobile.sb_disp_range'] * 5;
        if ($k_sb_navi_select_optgroup >= $sb_disp_all_num) {
            $k_sb_navi_select_optgroup = 0;
        }*/

        for ($i = 0; $i < $sb_disp_all_num; $i += $_conf['mobile.sb_disp_range']) {
            $j = $i + 1;
            $k = $i + $_conf['mobile.sb_disp_range'];
            if ($k > $sb_disp_all_num) {
                $k = $sb_disp_all_num;
            }

            /*if ($k_sb_navi_select_optgroup && $i % $k_sb_navi_select_optgroup == 0) {
                if ($i) {
                    $k_sb_navi_select_from_ht .= '</optgroup>';
                }
                $k_sb_navi_select_from_ht .= "<optgroup label=\"{$j}-\">";
            }*/

            $l = ($j == $k) ? "$j" : "{$j}-{$k}";

            if ($j == $disp_navi['from']) {
                $k_sb_navi_select_from_ht .= "<option value=\"{$j}\" selected>{$l}</option>";
            } else {
                $k_sb_navi_select_from_ht .= "<option value=\"{$j}\">{$l}</option>";
            }
        }

        /*if ($k_sb_navi_select_optgroup) {
            $k_sb_navi_select_from_ht .= '</optgroup>';
        }*/
    }

    if ($_conf['iphone']) {
        $k_sb_navi_ht = <<<EOP
<div class="pager"><select onchange="location.href = '{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;from=' + this.options[this.selectedIndex].value + '{$sb_view_at}{$word_at}{$_conf['k_at_a']}';">{$k_sb_navi_select_from_ht}</select>/{$sb_disp_all_num} {$mae_ht} {$tugi_ht}</div>
EOP;
    } else {
        $k_sb_navi_ht = <<<EOP
<div>{$sb_range_on}/{$sb_disp_all_num} {$mae_ht} {$tugi_ht}</div>
<form method="get" action="{$_conf['subject_php']}">
<input type="hidden" name="host" value="{$aThreadList->host}">
<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
<input type="hidden" name="norefresh" value="1">
<select name="from">{$k_sb_navi_select_from_ht}</select><input type="submit" value="GO">
{$sb_view_input_ht}{$word_input_ht}{$_conf['k_input_ht']}
</form>
EOP;
    }
} else {
    $k_sb_navi_ht = '';
}

// }}}
// {{{ dat�q��

// �X�y�V�������[�h�łȂ���΁A�܂��͂��ځ[�񃊃X�g�Ȃ�
if (!$aThreadList->spmode or $aThreadList->spmode == "taborn") {
    $dat_soko_ht = <<<EOP
 <a href="{$_conf['subject_php']}?host={$aThreadList->host}{$bbs_q}{$norefresh_q}&amp;spmode=soko{$_conf['k_at_a']}">dat�q��</a>
EOP;
} else {
    $dat_soko_ht = '';
}

// }}}
// {{{ ���ځ[�񒆂̃X���b�h

if ($ta_num) {
    $taborn_link_ht = <<<EOP
 <a href="{$_conf['subject_php']}?host={$aThreadList->host}{$bbs_q}{$norefresh_q}&amp;spmode=taborn{$_conf['k_at_a']}">���ݒ�({$ta_num})</a>
EOP;
} else {
    $taborn_link_ht = '';
}

// }}}
// {{{ �V�K�X���b�h�쐬

if (!$aThreadList->spmode) {
    $buildnewthread_ht = <<<EOP
 <a href="post_form.php?host={$aThreadList->host}{$bbs_q}&amp;newthread=1{$_conf['k_at_a']}">�ڗ���</a>
EOP;
} else {
    $buildnewthread_ht = '';
}

// }}}
// {{{ ���C�ɃX���Z�b�g�ؑ�

if ($aThreadList->spmode == 'fav' && $_conf['expack.misc.multi_favs']) {
    $switchfavlist_ht = '<div>' . FavSetManager::makeFavSetSwitchForm('m_favlist_set', '���C�ɃX��', NULL, NULL, FALSE, array('spmode' => 'fav')) . '</div>';
} else {
    $switchfavlist_ht = '';
}

// }}}
// {{{ �\�[�g�ύX �i�V�� ���X No. �^�C�g�� �� ���΂₳ ���� Birthday ���j

if ($_conf['iphone']) {
    $sorts = array('midoku' => '�V��', 'res' => '���X', 'no' => 'No.', 'title' => '�^�C�g��');
} else {
    $sorts = array('midoku' => '�V��', 'res' => 'ڽ', 'no' => 'No.', 'title' => '����');
}

if ($aThreadList->spmode and $aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') {
    $sorts['ita'] = '��';
}
if ($_conf['sb_show_spd']) {
    $sorts['spd'] = '���΂₳';
}
if ($_conf['sb_show_ikioi']) {
    $sorts['ikioi'] = '����';
}
$sorts['bd'] = 'Birthday';
if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') {
    $sorts['fav'] = '��';
}

$htm['change_sort'] = "<form method=\"get\" action=\"{$_conf['subject_php']}\">";
$htm['change_sort'] .= $_conf['k_input_ht'];
$htm['change_sort'] .= '<input type="hidden" name="norefresh" value="1">';
// spmode��
if ($aThreadList->spmode) {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"spmode\" value=\"{$aThreadList->spmode}\">";
}
// spmode�łȂ��A�܂��́Aspmode�����ځ[�� or dat�q�ɂȂ�
if (!$aThreadList->spmode || $aThreadList->spmode == "taborn" || $aThreadList->spmode == "soko") {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"host\" value=\"{$aThreadList->host}\">";
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"bbs\" value=\"{$aThreadList->bbs}\">";
}

$htm['change_sort'] .= '<select name="sort">';
foreach ($sorts as $k => $v) {
    if ($GLOBALS['now_sort'] == $k) {
        $selected = ' selected';
    } else {
        $selected = '';
    }
    $htm['change_sort'] .= "<option value=\"{$k}\"{$selected}>{$v}</option>";
}
$htm['change_sort'] .= '</select>';

if (!empty($_REQUEST['sb_view'])) {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"sb_view\" value=\"" . htmlspecialchars($_REQUEST['sb_view']) . "\">";
}
if ($_conf['iphone']) {
    // iPhone (2.0.1) ��Safari�ł�label�v�f�������Ȃ� (�^�O�ň͂ށAfor�����Ƃ���) �̂�onclick�ő�p����
    $htm['change_sort'] .= ' <input type="checkbox" name="rsort" value="1">';
    $htm['change_sort'] .= '<span onclick="iutil.checkPrev(this);">�t��</span>';
} else {
    $htm['change_sort'] .= ' <label><input type="checkbox" name="rsort" value="1">�t��</label>';
}
$htm['change_sort'] .= ' <input type="submit" value="���ёւ�"></form>';

// }}}

// HTML�v�����g ==============================================
if (!$_conf['iphone']) {
    echo '<hr>';
}
echo $k_sb_navi_ht;
include P2_LIB_DIR . '/sb_toolbar_k.inc.php';
echo $allfav_ht;
echo $switchfavlist_ht;
echo '<div class="pager">';
echo $dat_soko_ht;
echo $taborn_link_ht;
echo $buildnewthread_ht;
echo '</div>';
echo $htm['change_sort'];
echo '<hr>';
echo "<div class=\"center\">{$_conf['k_to_index_ht']}</div>";

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
