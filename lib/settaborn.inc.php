<?php
/**
 * rep2 - �X���b�h���ځ[��̊֐�
 */

require_once P2_LIB_DIR . '/FileCtl.php';

// {{{ settaborn()

/**
 * �X���b�h���ځ[����I���I�t����
 *
 * $set �́A0(����), 1(�ǉ�), 2(�g�O��)
 */
function settaborn($host, $bbs, $key, $set)
{
    global $_conf, $title_msg, $info_msg;

    //==================================================================
    // key.idx �ǂݍ���
    //==================================================================

    // idxfile�̃p�X�����߂�
    $idx_host_bbs_dir_s = P2Util::idxDirOfHostBbs($host, $bbs);
    $idxfile = $idx_host_bbs_dir_s . $key . '.idx';

    // �f�[�^������Ȃ�ǂݍ���
    if ($lines = FileCtl::file_read_lines($idxfile, FILE_IGNORE_NEW_LINES)) {
        $data = explode('<>', $lines[0]);
    } else {
        $data = array_fill(0, 12, '');
    }

    //==================================================================
    // p2_threads_aborn.idx�ɏ�������
    //==================================================================

    // p2_threads_aborn.idx �̃p�X�擾
    $taborn_idx = $idx_host_bbs_dir_s . 'p2_threads_aborn.idx';

    // p2_threads_aborn.idx ���Ȃ���ΐ���
    FileCtl::make_datafile($taborn_idx, $_conf['p2_perm']);

    // p2_threads_aborn.idx �ǂݍ���;
    $taborn_lines= FileCtl::file_read_lines($taborn_idx, FILE_IGNORE_NEW_LINES);

    $neolines = array();

    if ($taborn_lines) {
        foreach ($taborn_lines as $l) {
            $lar = explode('<>', $l);
            if ($lar[1] == $key) {
                $aborn_attayo = true; // ���ɂ��ځ[�񒆂ł���
                if ($set == 0 or $set == 2) {
                    $title_msg_pre = "+ ���ځ[�� �������܂���";
                    $info_msg_pre = "+ ���ځ[�� �������܂���";
                }
                continue;
            }
            if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
            $neolines[] = $l;
        }
    }

    // �V�K�f�[�^�ǉ�
    if ($set == 1 or !$aborn_attayo && $set == 2) {
        $newdata = "$data[0]<>{$key}<><><><><><><><>";
        $neolines ? array_unshift($neolines, $newdata) : $neolines = array($newdata);
        $title_msg_pre = "�� ���ځ[�� ���܂���";
        $info_msg_pre = "�� ���ځ[�� ���܂���";
    }

    // ��������
    $cont = '';
    if (!empty($neolines)) {
        foreach ($neolines as $l) {
            $cont .= $l."\n";
        }
    }
    if (FileCtl::file_write_contents($taborn_idx, $cont) === false) {
        p2die('cannot write file.');
    }

    $GLOBALS['title_msg'] = $title_msg_pre;
    $GLOBALS['info_msg'] = $info_msg_pre;

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
