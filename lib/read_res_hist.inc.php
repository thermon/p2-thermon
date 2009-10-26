<?php
/**
 * rep2 - �������ݗ��� �̂��߂̊֐��Q
 */

require_once P2_LIB_DIR . '/DataPhp.php';

// {{{ deleMsg()

/**
 * �`�F�b�N�����������݋L�����폜����
 */
function deleMsg($checked_hists)
{
    global $_conf;

    $lock = new P2Lock($_conf['res_hist_dat'], false);

    // �ǂݍ����
    $reslines = FileCtl::file_read_lines($_conf['res_hist_dat'], FILE_IGNORE_NEW_LINES);
    if ($reslines === false) {
        p2die("{$_conf['res_hist_dat']} ���J���܂���ł���");
    }

    // �t�@�C���̉��ɋL�^����Ă�����̂��V�����̂ŋt���ɂ���
    $reslines = array_reverse($reslines);

    $neolines = array();

    // �`�F�b�N���Đ�����
    if ($reslines) {
        $n = 1;
        foreach ($reslines as $ares) {
            $rar = explode("<>", $ares);

            // �ԍ��Ɠ��t����v���邩���`�F�b�N����
            if (checkMsgID($checked_hists, $n, $rar[2])) {
                $rmnums[] = $n; // �폜����ԍ���o�^
            }

            $n++;
        }
        $neolines = rmLine($rmnums, $reslines);

        $_info_msg_ht .= "<p>p2 info: " . count($rmnums) . "���̃��X�L�����폜���܂���</p>";
    }

    if (is_array($neolines)) {
        // �s����߂�
        $neolines = array_reverse($neolines);

        $cont = "";
        if ($neolines) {
            $cont = implode("\n", $neolines) . "\n";
        }

        // ��������
        if (FileCtl::file_write_contents($_conf['res_hist_dat'], $cont) === false) {
            p2die('cannot write file.');
        }
    }
}

// }}}
// {{{ checkMsgID()

/**
 * �ԍ��Ɠ��t����v���邩���`�F�b�N����
 *
 * @return boolean
 */
function checkMsgID($checked_hists, $order, $date)
{
    if ($checked_hists) {
        foreach ($checked_hists as $v) {
            $vary = explode(",,,,", $v);    // ",,,," �͊O�����痈��ϐ��ŁA����ȃf���~�^
            if (($vary[0] == $order) and ($vary[1] == $date)) {
                return true;
            }
        }
    }
    return false;
}

// }}}
// {{{ rmLine()

/**
 * �w�肵���ԍ��i�z��w��j���s���X�g����폜����
 */
function rmLine($order_list, $lines)
{
    if ($lines) {
        $neolines = array();
        $i = 0;
        foreach ($lines as $l) {
            $i++;
            if (checkOrder($order_list, $i)) { continue; } // �폜����
            $neolines[] = $l;
        }
        return $neolines;
    }
    return false;
}

// }}}
// {{{ checkOrder()

/**
 * �ԍ��Ɣz����r
 */
function checkOrder($order_list, $order)
{
    if ($order_list) {
        foreach ($order_list as $n) {
            if ($n == $order) {
                return true;
            }
        }
    }
    return false;
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
