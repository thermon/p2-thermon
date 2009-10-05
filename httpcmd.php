<?php
/**
 * rep2 - Ajax
 * cmd �������ŃR�}���h����
 * �Ԃ�l�́A�e�L�X�g�ŕԂ�
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// {{{ HTTP�w�b�_

P2Util::header_nocache();
header('Content-Type: text/plain; charset=Shift_JIS');

// }}}

$r_msg = '';

// �R�}���h�擾 (�w�肳��Ă��Ȃ���΁A�����Ԃ����ɏI��)
if (!isset($_REQUEST['cmd'])) {
    exit;
} else {
    $cmd = $_REQUEST['cmd'];
}

$host = isset($_REQUEST['host']) ? $_REQUEST['host'] : null;
$bbs  = isset($_REQUEST['bbs'])  ? $_REQUEST['bbs']  : null;
$key  = isset($_REQUEST['key'])  ? $_REQUEST['key']  : null;

switch ($cmd) {
// {{{ ���O�폜

case 'delelog':
    if (isset($host) && isset($bbs) && isset($key)) {
        if (!function_exists('deleteLogs')) {
            include P2_LIB_DIR . '/dele.inc.php';
        }
        $r = deleteLogs($host, $bbs, array($key));
        if (empty($r)) {
            $r_msg = '0'; // ���s
        } elseif ($r == 1) {
            $r_msg = '1'; // ����
        } elseif ($r == 2) {
            $r_msg = '2'; // �Ȃ�
        }
    }
    break;

// }}}
// {{{ �����폜

case 'offrec':
    if (isset($host) && isset($bbs) && isset($key)) {
        if (!function_exists('offRecent')) {
            include P2_LIB_DIR . '/dele.inc.php';
        }
        $r1 = offRecent($host, $bbs, $key);
        $r2 = offResHist($host, $bbs, $key);
        if (empty($r1) || empty($r2)) {
            $r_msg = '0'; // ���s
        } elseif ($r1 == 1 || $r2 == 1) {
            $r_msg = '1'; // ����
        } elseif ($r1 == 2 && $r2 == 2) {
            $r_msg = '2'; // �Ȃ�
        }
    }
    break;

// }}}
// {{{ ���C�ɔ�

case 'setfavita':
    if (isset($host) && isset($bbs) && isset($_REQUEST['setfavita'])) {
        if (!function_exists('setFavItaByHostBbs')) {
            include P2_LIB_DIR . '/setfavita.inc.php';
        }
        if (isset($_REQUEST['itaj_en'])) {
            $itaj = base64_decode($_REQUEST['itaj_en']);
        } elseif (isset($_REQUEST['itaj'])) {
            $itaj = $_REQUEST['itaj'];
        } else {
            $itaj = null;
        }
        if (isset($_REQUEST['setnum'])) {
            $r = setFavItaByHostBbs($host, $bbs, $_REQUEST['setfavita'], $itaj, $_REQUEST['setnum']);
        } else {
            $r = setFavItaByHostBbs($host, $bbs, $_REQUEST['setfavita'], $itaj);
        }
        if (empty($r)) {
            $r_msg = '0'; // ���s
        } elseif ($r == 1) {
            $r_msg = '1'; // ����
        }
    }
    break;

// }}}
// {{{ ���C�ɃX��

case 'setfav':
    if (isset($host) && isset($bbs) && isset($key) && isset($_REQUEST['setfav'])) {
        if (!function_exists('setFav')) {
            include P2_LIB_DIR . '/setfav.inc.php';
        }
        if (isset($_REQUEST['ttitle_en'])) {
            $ttitle = base64_decode($_REQUEST['ttitle_en']);
        } elseif (isset($_REQUEST['ttitle'])) {
            $ttitle = $_REQUEST['ttitle'];
        } else {
            $ttitle = null;
        }
        if (isset($_REQUEST['setnum'])) {
            $r = setFav($host, $bbs, $key, $_REQUEST['setfav'], $ttitle, $_REQUEST['setnum']);
        } else {
            $r = setFav($host, $bbs, $key, $_REQUEST['setfav'], $ttitle);
        }
        if (empty($r)) {
            $r_msg = '0'; // ���s
        } elseif ($r == 1) {
            $r_msg = '1'; // ����
        }
    }
    break;

// }}}
// {{{ �a������

case 'setpal':
    if (isset($host) && isset($bbs) && isset($key) && isset($_REQUEST['setpal'])) {
        if (!function_exists('setPal')) {
            include P2_LIB_DIR . '/setpalace.inc.php';
        }
        if (isset($_REQUEST['ttitle_en'])) {
            $ttitle = base64_decode($_REQUEST['ttitle_en']);
        } elseif (isset($_REQUEST['ttitle'])) {
            $ttitle = $_REQUEST['ttitle'];
        } else {
            $ttitle = null;
        }
        $r = setPal($host, $bbs, $key, $_REQUEST['setpal'], $ttitle);
        if (empty($r)) {
            $r_msg = '0'; // ���s
        } elseif ($r == 1) {
            $r_msg = '1'; // ����
        }
    }
    break;

// }}}
// {{{ �X���b�h���ځ[��

case 'taborn':
    if (isset($host) && isset($bbs) && isset($key) && isset($_REQUEST['taborn'])) {
        require_once P2_LIB_DIR . '/settaborn.inc.php';
        $r = settaborn($host, $bbs, $key, $_REQUEST['taborn']);
        if (empty($r)) {
            $r_msg = '0'; // ���s
        } elseif ($r == 1) {
            $r_msg = '1'; // ����
        }
    }
    break;

// }}}
// {{{ ImageCaceh2 ON/OFF

case 'ic2':
    if (isset($_REQUEST['switch'])) {
        require_once P2EX_LIB_DIR . '/ic2/Switch.php';
        $switch = (bool)$_REQUEST['switch'];
        if (IC2_Switch::set($switch, !empty($_REQUEST['mobile']))) {
            if ($switch) {
                $r_msg = '1'; // ON�ɂ���
            } else {
                $r_msg = '2'; // OFF�ɂ���
            }
        } else {
            $r_msg = '0'; // ���s
        }
    }
    break;

// }}}
}
// {{{ ���ʏo��

echo $r_msg;

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
