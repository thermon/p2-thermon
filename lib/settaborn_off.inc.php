<?php
/**
 * rep2 - �X���b�h���ځ[�񕡐��ꊇ��������
 */

require_once P2_LIB_DIR . '/FileCtl.php';

// {{{ settaborn_off()

/**
 * ���X���b�h���ځ[��𕡐��ꊇ��������
 */
function settaborn_off($host, $bbs, $taborn_off_keys)
{
    if (!$taborn_off_keys) {
        return;
    }

    // p2_threads_aborn.idx �̃p�X�擾
    $taborn_idx = P2Util::idxDirOfHostBbs($host, $bbs) . 'p2_threads_aborn.idx';

    // p2_threads_aborn.idx ���Ȃ����
    if (!file_exists($taborn_idx)) {
        p2die('���ځ[�񃊃X�g��������܂���ł����B');
    }

    // p2_threads_aborn.idx �ǂݍ���
    $taborn_lines = FileCtl::file_read_lines($taborn_idx, FILE_IGNORE_NEW_LINES);

    // �w��key���폜
    foreach ($taborn_off_keys as $val) {

        $neolines = array();

        if ($taborn_lines) {
            foreach ($taborn_lines as $line) {
                $lar = explode('<>', $line);
                if ($lar[1] == $val) { // key����
                    // echo "key:{$val} �̃X���b�h�����ځ[��������܂����B<br>";
                    $kaijo_attayo = true;
                    continue;
                }
                if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
                $neolines[] = $line;
            }
        }

        $taborn_lines = $neolines;
    }

    // ��������
    if (file_exists($taborn_idx)) {
        copy($taborn_idx, $taborn_idx.'.bak'); // �O�̂��߃o�b�N�A�b�v
    }

    $cont = '';
    if (is_array($taborn_lines)) {
        foreach ($taborn_lines as $l) {
            $cont .= $l."\n";
        }
    }
    if (FileCtl::file_write_contents($taborn_idx, $cont) === false) {
        p2die('cannot write file.');
    }

    /*
    if (!$kaijo_attayo) {
        // echo "�w�肳�ꂽ�X���b�h�͊��ɂ��ځ[�񃊃X�g�ɍڂ��Ă��Ȃ��悤�ł��B";
    } else {
        // echo "���ځ[������A�������܂����B";
    }
    */
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
