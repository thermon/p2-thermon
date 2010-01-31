<?php
/**
 * rep2 �X���b�h�T�u�W�F�N�g�\���֐�
 * for subject.php
 */

// {{{ sb_print()

/**
 * sb_print - �X���b�h�ꗗ��\������ (<tr>�`</tr>)
 */
function sb_print($aThreadList)
{
    global $_conf, $sb_view, $p2_setting, $STYLE;

    //$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sb_print()');

    if (!$aThreadList->threads) {
        echo '<tbody><tr><td>�@�Y���T�u�W�F�N�g�͂Ȃ�������</td></tr></tbody>';
        //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_print()');
        return;
    }

    // �ϐ� ================================================

    // >>1 �\�� (spmode�͏���)
    $only_one_bool = false;
    if (!$aThreadList->spmode && ($_conf['sb_show_one'] == 1 || ($_conf['sb_show_one'] == 2 &&
        (strpos($aThreadList->bbs, 'news') !== false || $aThreadList->bbs == 'bizplus')
    ))) {
        $only_one_bool = true;
    }

    // �`�F�b�N�{�b�N�X
    if ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
        $checkbox_bool = true;
    } else {
        $checkbox_bool = false;
    }

    // ��
    if ($aThreadList->spmode && $aThreadList->spmode != 'taborn' && $aThreadList->spmode != 'soko') {
        $ita_name_bool = true;
    } else {
        $ita_name_bool = false;
    }

    $norefresh_q = '&amp;norefresh=true';

    $td = array('edit' => '', 'offrec' => '', 'unum' => '', 'rescount' => '',
                'one' => '', 'checkbox' => '', 'ita' => '', 'spd' => '',
                'ikioi' => '', 'birth' => '', 'fav' => '');

    // td�� css�N���X
    $class_t  = ' class="t"';   // ��{
    $class_te = ' class="te"';  // ���ёւ�
    $class_tu = ' class="tu"';  // �V�����X��
    $class_tn = ' class="tn"';  // ���X��
    $class_tc = ' class="tc"';  // �`�F�b�N�{�b�N�X
    $class_to = ' class="to"';  // �I�[�_�[�ԍ�
    $class_tl = ' class="tl"';  // �^�C�g��
    $class_ts = ' class="ts"';  // ���΂₳
    $class_ti = ' class="ti"';  // ����

    // �\�[�g ==================================================

    // ���݂̃\�[�g�`����class�w���CSS�J���[�����O
    $class_sort_midoku  = '';   // �V��
    $class_sort_res     = '';   // ���X
    $class_sort_no      = '';   // No.
    $class_sort_title   = '';   // �^�C�g��
    $class_sort_ita     = '';   // ��
    $class_sort_spd     = '';   // ���΂₳
    $class_sort_ikioi   = '';   // ����
    $class_sort_bd      = '';   // Birthday
    $class_sort_fav     = '';   // ���C�ɓ���
    if (empty($_REQUEST['rsort'])) {
        ${'class_sort_' . $GLOBALS['now_sort']} = ' class="now_sort"';
    } else {
        ${'class_sort_' . $GLOBALS['now_sort']} = ' class="now_sort rsort"';
    }

    // �t���\�[�g�p�N�G��
    $rsortq_midoku  = '';   // �V��
    $rsortq_res     = '';   // ���X
    $rsortq_no      = '';   // No.
    $rsortq_title   = '';   // �^�C�g��
    $rsortq_ita     = '';   // ��
    $rsortq_spd     = '';   // ���΂₳
    $rsortq_ikioi   = '';   // ����
    $rsortq_bd      = '';   // Birthday
    $rsortq_fav     = '';   // ���C�ɓ���
    if (empty($_REQUEST['rsort'])) {
        ${'rsortq_' . $GLOBALS['now_sort']} = '&amp;rsort=1';
    }

    $sortq_spmode = '';
    $sortq_host = '';
    $sortq_ita = '';
    // spmode��
    if ($aThreadList->spmode) {
        $sortq_spmode = "&amp;spmode={$aThreadList->spmode}";
    }
    // spmode�łȂ��A�܂��́Aspmode�����ځ[�� or dat�q�ɂȂ�
    if (!$aThreadList->spmode || $aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko') {
        $sortq_host = "&amp;host={$aThreadList->host}";
        $sortq_ita = "&amp;bbs={$aThreadList->bbs}";
    }

    $sortq_common = $sortq_spmode . $sortq_host . $sortq_ita;

    if (!empty($_REQUEST['find_cont']) && strlen($GLOBALS['word_fm']) > 0) {
        $word_q = '&amp;word=' . rawurlencode($GLOBALS['word']) . '&amp;method=' . rawurlencode($GLOBALS['sb_filter']['method']);
    } else {
        $word_q = '';
    }

    //=====================================================
    // �e�[�u���w�b�_
    //=====================================================
    echo "<thead>\n<tr class=\"tableheader\">\n";

    // ����
    if ($sb_view == 'edit') {
        echo "<th{$class_te}>&nbsp;</th>\n";
    }
    // �����̉���
    if ($aThreadList->spmode == 'recent') {
        echo "<th{$class_t}>&nbsp;</th>\n";
    }
    // �V��
    if ($sb_view != 'edit') {
        echo <<<EOP
<th{$class_tu} id="sb_th_midoku"><a{$class_sort_midoku} href="{$_conf['subject_php']}?sort=midoku{$sortq_common}{$rsortq_midoku}{$norefresh_q}" target="_self">�V��</a></th>\n
EOP;
    }
    // ���X��
    if ($sb_view != 'edit') {
        echo <<<EOP
<th{$class_tn} id="sb_th_res"><a{$class_sort_res} href="{$_conf['subject_php']}?sort=res{$sortq_common}{$rsortq_res}{$norefresh_q}" target="_self">���X</a></th>\n
EOP;
    }
    // >>1
    if ($only_one_bool) {
        echo "<th{$class_t}>&nbsp;</th>\n";
    }
    // �`�F�b�N�{�b�N�X
    if ($checkbox_bool) {
        echo <<<EOP
<th{$class_tc}><input id="allbox" name="allbox" type="checkbox" onclick="checkAll();" title="���ׂĂ̍��ڂ�I���A�܂��͑I������"></th>\n
EOP;
    }
    // No.
    $title = empty($aThreadList->spmode) ? ' title="2ch�W���̕��я��ԍ�"' : '';
    echo <<<EOP
<th{$class_to} id="sb_th_no"><a{$class_sort_no} href="{$_conf['subject_php']}?sort=no{$sortq_common}{$rsortq_no}{$norefresh_q}" target="_self"{$title}>No.</a></th>\n
EOP;
    // �^�C�g��
    echo <<<EOP
<th{$class_tl} id="sb_th_title"><a{$class_sort_title} href="{$_conf['subject_php']}?sort=title{$sortq_common}{$rsortq_title}{$norefresh_q}" target="_self">�^�C�g��</a></th>\n
EOP;
    // ��
    if ($ita_name_bool) {
        echo <<<EOP
<th{$class_t} id="sb_th_ita"><a{$class_sort_ita} href="{$_conf['subject_php']}?sort=ita{$sortq_common}{$rsortq_ita}{$norefresh_q}" target="_self">��</a></th>\n
EOP;
    }
    // ���΂₳
    if ($_conf['sb_show_spd']) {
        echo <<<EOP
<th{$class_ts} id="sb_th_spd"><a{$class_sort_spd} href="{$_conf['subject_php']}?sort=spd{$sortq_common}{$rsortq_spd}{$norefresh_q}" target="_self">���΂₳</a></th>\n
EOP;
    }
    // ����
    if ($_conf['sb_show_ikioi']) {
        echo <<<EOP
<th{$class_ti} id="sb_th_ikioi"><a{$class_sort_ikioi} href="{$_conf['subject_php']}?sort=ikioi{$sortq_common}{$rsortq_ikioi}{$norefresh_q}" target="_self">����</a></th>\n
EOP;
    }
    // Birthday
    echo <<<EOP
<th{$class_t} id="sb_th_bd"><a{$class_sort_bd} href="{$_conf['subject_php']}?sort=bd{$sortq_common}{$rsortq_bd}{$norefresh_q}" target="_self">Birthday</a></th>\n
EOP;
    // ���C�ɓ���
    if ($_conf['sb_show_fav'] && $aThreadList->spmode != 'taborn') {
        echo <<<EOP
<th{$class_t} id="sb_th_fav"><a{$class_sort_fav} href="{$_conf['subject_php']}?sort=fav{$sortq_common}{$rsortq_fav}{$norefresh_q}" target="_self" title="���C�ɃX��">��</a></th>\n
EOP;
    }

    echo "</tr>\n</thead>\n";

    //=====================================================
    //�e�[�u���{�f�B
    //=====================================================

    echo "<tbody>\n";

    //spmode������΃N�G���[�ǉ�
    if ($aThreadList->spmode) {
        $spmode_q = "&amp;spmode={$aThreadList->spmode}";
    } else {
        $spmode_q = '';
    }

    $i = 0;
    foreach ($aThreadList->threads as $aThread) {
        $i++;
        $midoku_ari = false;
        $anum_ht = ''; // #r1

        $host_bbs_key_q = "host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}";

        if ($aThreadList->spmode != 'taborn') {
            if (!$aThread->torder) { $aThread->torder = $i; }
        }

        // tr�� css�N���X
        if ($i % 2) {
            $row_class = 'r1 r_odd';
        } else {
            $row_class = 'r2 r_even';
        }

        //�V�����X�� =============================================
        $unum_ht_c = '&nbsp;';
        // �����ς�
        if ($aThread->isKitoku()) {
            $row_class .= ' r_read'; // read�͉ߋ�����

            // $ttitle_en_q �͐ߌ��ȗ�
            $delelog_js = "return wrapDeleLog('{$host_bbs_key_q}',this);";
            $title_at = ' title="�N���b�N����ƃ��O�폜"';

            $anum_ht = sprintf('#r%d', min($aThread->rescount, $aThread->rescount - $aThread->nunum + 1 - $_conf['respointer']));

            // subject.txt�ɂȂ���
            if (!$aThread->isonline) {
                $row_class .= ' r_offline';
                // JavaScript�ł̊m�F�_�C�A���O����
                $unum_ht_c = <<<EOP
<a class="un_n" href="{$_conf['subject_php']}?{$host_bbs_key_q}{$spmode_q}&amp;dele=true" target="_self" onclick="if (!window.confirm('���O���폜���܂����H')) {return false;} {$delelog_js}"{$title_at}>-</a>
EOP;

            // �V������
            } elseif ($aThread->unum > 0) {
                $row_class .= ' r_new';
                $midoku_ari = true;
                $unum_ht_c = <<<EOP
<a id="un{$i}" class="un_a" href="{$_conf['subject_php']}?{$host_bbs_key_q}{$spmode_q}&amp;dele=true" target="_self" onclick="{$delelog_js}"{$title_at}>{$aThread->unum}</a>
EOP;

            // subject.txt�ɂ͂��邪�A�V���Ȃ�
            } else {
                $unum_ht_c = <<<EOP
<a class="un" href="{$_conf['subject_php']}?{$host_bbs_key_q}{$spmode_q}&amp;dele=true" target="_self" onclick="{$delelog_js}"{$title_at}>{$aThread->unum}</a>
EOP;
            }
        }

        $td['unum'] = "<td{$class_tu}>{$unum_ht_c}</td>\n";

        // �����X�� =============================================
        $td['rescount'] = "<td{$class_tn}>{$aThread->rescount}</td>\n";

        // �� ============================================
        if ($ita_name_bool) {
            $ita_name_ht = htmlspecialchars($aThread->itaj ? $aThread->itaj : $aThread->bbs, ENT_QUOTES);
            $td['ita'] = <<<EOP
<td{$class_t}><a href="{$_conf['subject_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}" target="_self">{$ita_name_ht}</a></td>\n
EOP;
        }


        // ���C�ɓ��� ========================================
        if ($_conf['sb_show_fav']) {
            if ($aThreadList->spmode != 'taborn') {

                $favmark = (!empty($aThread->fav)) ? '��' : '+';
                $favdo = (!empty($aThread->fav)) ? 0 : 1;
                $favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
                $favdo_q = '&amp;setfav='.$favdo;

                // $ttitle_en_q ���t���������������A�ߖ�̂��ߏȗ�����
                $td['fav'] = <<<EOP
<td{$class_t}><a class="fav" href="info.php?{$host_bbs_key_q}{$favdo_q}" target="info" onclick="return wrapSetFavJs('{$host_bbs_key_q}','{$favdo}',this);" title="{$favtitle}">{$favmark}</a></td>\n
EOP;
            }
        }

        // torder(info) =================================================
        // ���C�ɃX��
        if ($aThread->fav) {
            $torder_st = "<b>{$aThread->torder}</b>";
        } else {
            $torder_st = $aThread->torder;
        }
        $torder_ht = <<<EOP
<a id="to{$i}" class="info" href="info.php?{$host_bbs_key_q}" target="_self" onclick="return wrapOpenSubWin(this.href.toString())">{$torder_st}</a>
EOP;

        // title =================================================
        $rescount_q = '&amp;rescount=' . $aThread->rescount;

        // dat�q�� or �a���Ȃ�
        if ($aThreadList->spmode == 'soko' || $aThreadList->spmode == 'palace') {
            $rescount_q = '';
            $offline_q = '&amp;offline=true';
            $anum_ht = '';
        // subject.txt �ɂȂ��ꍇ
        } elseif (!$aThread->isonline) {
            $offline_q = '&amp;offline=true';
        } else {
            $offline_q = '';
        }

        // �^�C�g�����擾�Ȃ�
        $ttitle_ht = $aThread->ttitle_ht;
        if (strlen($ttitle_ht) == 0) {
            $ttitle_ht = "http://{$aThread->host}/test/read.cgi/{$aThread->bbs}/{$aThread->key}/";
        }

        if ($aThread->similarity) {
            $ttitle_ht .= sprintf(' <var>(%0.1f)</var>', $aThread->similarity * 100);
        }

        // ���X��
        $moto_thre_ht = '';
        if ($_conf['sb_show_motothre']) {
            if (!$aThread->isKitoku()) {
                $moto_thre_ht = '<a class="thre_title moto_thre" href="'
                              . htmlspecialchars($aThread->getMotoThread(false, ''), ENT_QUOTES)
                              . '"'
                              . ' onmouseover="showMotoLsPopUp(event, this, this.nextSibling.innerText)"'
                              . ' onmouseout="hideMotoLsPopUp()">�E</a>';
            }
        }

        // �V�K�X��
        if ($aThread->new) {
            $row_class .= ' r_brand_new';
            $classtitle_q = ' class="thre_title_new"';
        } else {
            $classtitle_q = ' class="thre_title"';
        }

        // �X�������N
        if ($word_q) {
            $rescount_q = '';
            $offline_q = '&amp;offline=true';
            $anum_ht = '';
        }
        $thre_url = "{$_conf['read_php']}?{$host_bbs_key_q}{$rescount_q}{$offline_q}{$word_q}{$anum_ht}";

        $chUnColor_js = ($midoku_ari) ? "chUnColor('{$i}');" : '';
        $change_color = " onclick=\"chTtColor('{$i}');{$chUnColor_js}\"";

        // �I�����[>>1
        if ($only_one_bool) {
            $td['one'] = <<<EOP
<td{$class_t}><a href="{$_conf['read_php']}?{$host_bbs_key_q}&amp;one=true">&gt;&gt;1</a></td>\n
EOP;
        }

        // �`�F�b�N�{�b�N�X
        if ($checkbox_bool) {
            $checked_ht = '';
            if ($aThreadList->spmode == 'taborn') {
                if (!$aThread->isonline) { // or ($aThread->rescount >= 1000)
                    $checked_ht = ' checked';
                }
            }
            $td['checkbox'] = <<<EOP
<td{$class_tc}><input name="checkedkeys[]" type="checkbox" value="{$aThread->key}"{$checked_ht}></td>\n
EOP;
        }

        // ����
        if ($sb_view == 'edit') {
            $td['unum'] = '';
            $td['rescount'] = '';
            $sb_view_q = '&amp;sb_view=edit';
            if ($aThreadList->spmode == 'fav') {
                $setkey = 'setfav';
            } elseif ($aThreadList->spmode == 'palace') {
                $setkey = 'setpal';
            }
            $narabikae_a = "{$_conf['subject_php']}?{$host_bbs_key_q}{$spmode_q}{$sb_view_q}";

            $td['edit'] = <<<EOP
<td{$class_te}>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=top" target="_self">��</a>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=up" target="_self">��</a>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=down" target="_self">��</a>
    <a class="te" href="{$narabikae_a}&amp;{$setkey}=bottom" target="_self">��</a>
</td>\n
EOP;
        }

        // �ŋߓǂ񂾃X���̉���
        if ($aThreadList->spmode == 'recent') {
            $td['offrec'] = <<<EOP
<td{$class_tc}><a href="info.php?{$host_bbs_key_q}&amp;offrec=true" target="_self" onclick="return offrec_ajax(this);">�~</a></td>\n
EOP;
        }

        // ���΂₳�i�� ����/���X �� ���X�Ԋu�j
        if ($_conf['sb_show_spd']) {
            if ($spd_st = $aThread->getTimePerRes()) {
                $td['spd'] = "<td{$class_ts}>{$spd_st}</td>\n";
            }
        }

        // ����
        if ($_conf['sb_show_ikioi']) {
            if ($aThread->dayres > 0) {
                // 0.0 �ƂȂ�Ȃ��悤�ɏ����_��2�ʂŐ؂�グ
                $dayres = ceil($aThread->dayres * 10) / 10;
                $dayres_st = sprintf("%01.1f", $dayres);
            } else {
                $dayres_st = '-';
            }
            $td['ikioi'] = "<td{$class_ti}>{$dayres_st}</td>\n";
        }

        // Birthday
        $birthday = date('y/m/d', $aThread->key); // (y/m/d H:i)
        $td['birth'] = "<td{$class_t}>{$birthday}</td>\n";

        //====================================================================================
        // �X���b�h�ꗗ table �{�f�B HTML�v�����g <tr></tr>
        //====================================================================================

        // �{�f�B
        echo <<<EOR
<tr class="{$row_class}">
{$td['edit']}{$td['offrec']}{$td['unum']}{$td['rescount']}{$td['one']}{$td['checkbox']}<td{$class_to}>{$torder_ht}</td>
<td{$class_tl}>{$moto_thre_ht}<a id="tt{$i}" href="{$thre_url}" title="{$aThread->ttitle_hd}"{$classtitle_q}{$change_color}>{$ttitle_ht}</a></td>
{$td['ita']}{$td['spd']}{$td['ikioi']}{$td['birth']}{$td['fav']}</tr>\n
EOR;

    }

    echo "</tbody>\n";

    //$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_print()');
    return true;
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
