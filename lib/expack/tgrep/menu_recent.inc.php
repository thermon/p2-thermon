<?php
/**
 * rep2xpack - tGrep �����������j���[
 */

if ($_conf['iphone']) {
    tgrep_print_recent_list_i();
} elseif ($_conf['ktai']) {
    tgrep_print_recent_list_k();
} else {
    tgrep_print_recent_list();
}

// {{{ tgrep_read_recent_list()

/**
 * ����������ǂݍ���
 */
function tgrep_read_recent_list()
{
    global $_conf;

    $list = FileCtl::file_read_lines($_conf['expack.tgrep.recent_file'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($list)) {
        return array();
    }
    return $list;
}

// }}}
// {{{ tgrep_print_recent_list()

/**
 * PC�p�\��
 */
function tgrep_print_recent_list()
{
    global $_conf;

    $tgrep_recent_list = tgrep_read_recent_list();

    if (!defined('TGREP_SMARTLIST_PRINT_ONLY_LINKS')) {
        echo '<div class="menu_cate">' . "\n";
        echo '<b><a class="menu_cate" href="#" onclick="return showHide(\'c_tgrep_recent\');" target="_self">�X����������</a></b>' . "\n";
        echo '[<a href="#" onclick="return tGrepUpdateList(\'recent\',\'c_tgrep_recent\');" target="_self">�X</a>]' . "\n";
        echo '[<a href="#" onclick="return tGrepClearList(\'recent\',\'c_tgrep_recent\');" target="_self">��</a>]' . "\n";
        echo '<div class="itas" id="c_tgrep_recent">' . "\n";
    }
    if ($tgrep_recent_list) {
        foreach ($tgrep_recent_list as $tgrep_recent_query) {
            $tgrep_recent_query_en =rawurlencode($tgrep_recent_query);
            $tgrep_recent_query_ht = htmlspecialchars($tgrep_recent_query, ENT_QUOTES);
            echo '�@<a href="tgrepc.php?Q=' . $tgrep_recent_query_en . '">' . $tgrep_recent_query_ht . '</a><br>' . "\n";
        }
    } else {
        echo '�i�Ȃ��j' . "\n";
    }
    if (!defined('TGREP_SMARTLIST_PRINT_ONLY_LINKS')) {
        echo "</div>\n</div>\n";
    }
}

// }}}
// {{{ tgrep_print_recent_list_k()

/**
 * �g�їp�\��
 */
function tgrep_print_recent_list_k()
{
    global $_conf;

    $tgrep_recent_list = tgrep_read_recent_list();

    echo '<h4>��������</h4>' . "\n";
    if ($tgrep_recent_list) {
        echo '<ul>' . "\n";
        foreach ($tgrep_recent_list as $tgrep_recent_query) {
            $tgrep_recent_query_en = rawurlencode($tgrep_recent_query);
            $tgrep_recent_query_ht = htmlspecialchars($tgrep_recent_query, ENT_QUOTES);
            echo '<li><a href="tgrepc.php?Q=' . $tgrep_recent_query_en . '">' . $tgrep_recent_query_ht . '</a></li>' . "\n";
        }
        echo '</ul>' . "\n";
        echo '<p><a href="tgrepctl.php?file=recent&amp;clear=all">����������ر</a></p>' . "\n";
    } else {
        echo '<p>�i�Ȃ��j</p>' . "\n";
    }
}

// }}}
// {{{ tgrep_print_recent_list_i()

/**
 * iPhone�p�\��
 */
function tgrep_print_recent_list_i()
{
    global $_conf;

    $tgrep_recent_list = tgrep_read_recent_list();

    if ($tgrep_recent_list) {
        foreach ($tgrep_recent_list as $tgrep_recent_query) {
            $tgrep_recent_query_en = rawurlencode($tgrep_recent_query);
            $tgrep_recent_query_ht = htmlspecialchars($tgrep_recent_query, ENT_QUOTES);
            echo '<li><a href="tgrepc.php?iq=' . $tgrep_recent_query_en . '">' . $tgrep_recent_query_ht . '</a></li>' . "\n";
        }
    } else {
        echo '<li class="weight-n">�i�Ȃ��j</li>' . "\n";
    }
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
