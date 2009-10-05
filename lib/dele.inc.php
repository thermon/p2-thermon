<?php
/**
 * rep2 - �X���b�h�f�[�^�ADAT���폜���邽�߂̊֐��S
 */

require_once P2_LIB_DIR . '/FileCtl.php';
require_once P2_LIB_DIR . '/setfav.inc.php';
require_once P2_LIB_DIR . '/setpalace.inc.php';

// {{{ deleteLogs()

/**
 * ���w�肵���z��keys�̃��O�iidx, (dat, srd)�j���폜���āA
 * ���łɗ���������O���B���C�ɃX���A�a��������O���B
 *
 * ���[�U�����O���폜���鎞�́A�ʏ킱�̊֐����Ă΂��
 *
 * @public
 * @param array $keys �폜�Ώۂ�key���i�[�����z��
 * @return int ���s�������0, �폜�ł�����1, �폜�Ώۂ��Ȃ����2��Ԃ��B
 */
function deleteLogs($host, $bbs, $keys)
{
    // �w��key�̃��O���폜�i�Ώۂ���̎��j
    if (is_string($keys)) {
        $akey = $keys;
        offRecent($host, $bbs, $akey);
        offResHist($host, $bbs, $akey);
        setFav($host, $bbs, $akey, 0);
        setPal($host, $bbs, $akey, 0);
        $r = deleteThisKey($host, $bbs, $akey);

    // �w��key�z��̃��O���폜
    } elseif (is_array($keys)) {
        $rs = array();
        foreach ($keys as $akey) {
            offRecent($host, $bbs, $akey);
            offResHist($host, $bbs, $akey);
            setFav($host, $bbs, $akey, 0);
            setPal($host, $bbs, $akey, 0);
            $rs[] = deleteThisKey($host, $bbs, $akey);
        }
        if (array_search(0, $rs) !== false) {
            $r = 0;
        } elseif (array_search(1, $rs) !== false) {
            $r = 1;
        } elseif (array_search(2, $rs) !== false) {
            $r = 2;
        } else {
            $r = 0;
        }
    }
    return $r;
}

// }}}
// {{{ deleteThisKey()

/**
 * ���w�肵���L�[�̃X���b�h���O�iidx (,dat)�j���폜����
 *
 * �ʏ�́A���̊֐��𒼐ڌĂяo�����Ƃ͂Ȃ��BdeleteLogs() ����Ăяo�����B
 *
 * @see deleteLogs()
 * @return int ���s�������0, �폜�ł�����1, �폜�Ώۂ��Ȃ����2��Ԃ��B
 */
function deleteThisKey($host, $bbs, $key)
{
    global $_conf;

    $anidx = P2Util::idxDirOfHostBbs($host, $bbs) . $key . '.idx';
    $adat = P2Util::datDirOfHostBbs($host, $bbs) . $key . '.dat';

    // File�̍폜����
    // idx�i�l�p�ݒ�j
    if (file_exists($anidx)) {
        if (unlink($anidx)) {
            $deleted_flag = true;
        } else {
            $failed_flag = true;
        }
    }

    // dat�̍폜����
    if (file_exists($adat)) {
        if (unlink($adat)) {
            $deleted_flag = true;
        } else {
            $failed_flag = true;
        }
    }

    // ���s�������
    if (!empty($failed_flag)) {
        return 0;
    // �폜�ł�����
    } elseif (!empty($deleted_flag)) {
        return 1;
    // �폜�Ώۂ��Ȃ����
    } else {
        return 2;
    }
}

// }}}
// {{{ checkRecent()

/**
 * ���w�肵���L�[���ŋߓǂ񂾃X���ɓ����Ă邩�ǂ������`�F�b�N����
 *
 * @public
 */
function checkRecent($host, $bbs, $key)
{
    global $_conf;

    if ($lines = FileCtl::file_read_lines($_conf['recent_idx'], FILE_IGNORE_NEW_LINES)) {
        foreach ($lines as $l) {
            $lar = explode('<>', $l);
            // ��������
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                return true;
            }
        }
    }
    return false;
}

// }}}
// {{{ checkResHist()

/**
 * ���w�肵���L�[���������ݗ����ɓ����Ă邩�ǂ������`�F�b�N����
 *
 * @public
 */
function checkResHist($host, $bbs, $key)
{
    global $_conf;

    if ($lines = FileCtl::file_read_lines($_conf['res_hist_idx'], FILE_IGNORE_NEW_LINES)) {
        foreach ($lines as $l) {
            $lar = explode('<>', $l);
            // ��������
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                return true;
            }
        }
    }
    return false;
}

// }}}
// {{{ offRecent()

/**
 * ���w�肵���L�[�̗����i�ŋߓǂ񂾃X���j���폜����
 *
 * @public
 */
function offRecent($host, $bbs, $key)
{
    global $_conf;

    $neolines = array();

    $lock = new P2Lock($_conf['recent_idx'], false);

    // {{{ ����΍폜

    if ($lines = FileCtl::file_read_lines($_conf['recent_idx'], FILE_IGNORE_NEW_LINES)) {
        foreach ($lines as $l) {
            $lar = explode('<>', $l);
            // �폜�i�X�L�b�v�j
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                $done = true;
                continue;
            }
            $neolines[] = $l;
        }
    }

    // }}}
    // {{{ ��������

    if (is_array($neolines)) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (FileCtl::file_write_contents($_conf['recent_idx'], $cont) === false) {
            p2die('cannot write file.');
        }
    }

    // }}}

    if (!empty($done)) {
        return 1;
    } else {
        return 2;
    }
}

// }}}
// {{{ offResHist()

/**
 * ���w�肵���L�[�̏������ݗ������폜����
 *
 * @public
 */
function offResHist($host, $bbs, $key)
{
    global $_conf;

    $neolines = array();

    $lock = new P2Lock($_conf['res_hist_idx'], false);

    // {{{ ����΍폜

    if ($lines = FileCtl::file_read_lines($_conf['res_hist_idx'], FILE_IGNORE_NEW_LINES)) {
        foreach ($lines as $l) {
            $lar = explode('<>', $l);
            // �폜�i�X�L�b�v�j
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                $done = true;
                continue;
            }
            $neolines[] = $l;
        }
    }

    // }}}
    // {{{ ��������

    if (is_array($neolines)) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (FileCtl::file_write_contents($_conf['res_hist_idx'], $cont) === false) {
            p2die('cannot write file.');
        }
    }

    // }}}

    if (!empty($done)) {
        return 1;
    } else {
        return 2;
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
