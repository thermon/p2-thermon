<?php
/**
 * rep2 - �T�u�W�F�N�g - �c�[���o�[�\��
 * for subject.php
 */

//===========================================================
// HTML�\���p�ϐ�
//===========================================================
/* ���HTML�\���p�ϐ��� sb_header.inc.php �ɂĐݒ� */

if (!isset($new_matome_i)) {
    $new_matome_i = 0;
    $sb_tool_i = 0;
}

// {{{ �V���܂Ƃߓǂ�
$new_matome_i++;

// �q�ɂłȂ����
if ($aThreadList->spmode != "soko") {
    if ($shinchaku_attayo) {
        $shinchaku_num_ht = " (<span id=\"smynum{$new_matome_i}\" class=\"matome_num\">{$shinchaku_num}</span>)";
    } else {
        $shinchaku_num_ht = "";
    }
    $shinchaku_matome_ht =<<<EOP
<a id="smy{$new_matome_i}" class="matome" href="{$_conf['read_new_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;nt={$newtime}" onclick="chNewAllColor();">�V���܂Ƃߓǂ�{$shinchaku_num_ht}</a>
EOP;
}
// }}}

$sb_tool_i++;
if ($sb_tool_i == 1) {
    $sb_tool_anchor = <<<EOP
<a class="toolanchor" href="#sbtoolbar2" target="_self">��</a>
EOP;
} elseif ($sb_tool_i == 2) {
    $sb_tool_anchor = <<<EOP
<a class="toolanchor" href="#sbtoolbar1" target="_self">��</a>
EOP;
}

//===========================================================
// HTML�v�����g
//===========================================================
echo <<<EOP
<table id="sbtoolbar{$sb_tool_i}" class="toolbar" cellspacing="0">
    <tr>
        <td class="toolbar-title">{$ptitle_ht}</td>
        <td class="toolbar-update">
            <form class="toolbar" method="GET" action="subject.php" accept-charset="{$_conf['accept_charset']}" target="_self">
                {$sb_form_hidden_ht}
                <input type="submit" name="submit_refresh" value="�X�V">
                {$sb_disp_num_ht}
            </form>
        </td>
        <td class="toolbar-filter">{$filter_form_ht}</td>
        <td class="toolbar-edit">{$edit_ht}</td>
        <td class="toolbar-anchor">
            {$shinchaku_matome_ht}
            <span class="time">{$reloaded_time}</span>
            {$sb_tool_anchor}
        </td>
    </tr>
</table>\n
EOP;

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
