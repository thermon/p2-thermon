<?php
/**
 * rep2expack - �X���b�h�\���v���t�B���^
 *
 * SPM����̃��X�t�B���^�����O�Ŏg�p
 */

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

/**
 * �ϐ��̐ݒ�
 */
$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];
$rc   = $_GET['rescount'];
$ttitle_en = $_GET['ttitle_en'];
$resnum = $_GET['resnum'];
$field  = $_GET['field'];
if (isset($_GET['word'])) {
    unset($_GET['word']);
}
$res_filter = array();
$res_filter['field'] = $field;
$itaj = P2Util::getItaName($host, $bbs);
if (!$itaj) { $itaj = $bbs; }
$ttitle_name = UrlSafeBase64::decode($ttitle_en);
$popup_filter = 1;

/**
 * �Ώۃ��X�̏���
 */
$aThread = new ThreadRead;
$aThread->setThreadPathInfo($host, $bbs, $key);
$aThread->readDat($aThread->keydat);

if (isset($aThread->datlines[$resnum - 1])) {
    $ares = $aThread->datlines[$resnum - 1];
    $resar = $aThread->explodeDatLine($ares);
    $name = $resar[0];
    $mail = $resar[1];
    $date_id = $resar[2];
    $msg = $resar[3];

    $aShowThread = new ShowThreadPc($aThread);
    if ($field == 'rres') {
        $_REQUEST['field']  = 'msg';
        $_REQUEST['method'] = 'regex';
        $word = ShowThread::getAnchorRegex(
            '%prefix%(.+%delimiter%)?' . $resnum . '(?!\\d|%range_delimiter%)'
        );
    } else {
        $word = $aShowThread->getFilterTarget($ares, $resnum, $name, $mail, $date_id, $msg);
    }
    if (strlen($word) == 0) {
        unset($word);
    } else {
        if ($field == 'date') {
            $date_part = explode(' ', trim($word));
            $word = $date_part[0];
        }
        $_REQUEST['word'] = $_GET['word'] = $word;
    }

    unset($ares, $resar, $name, $mail, $date_id, $msg, $aShowThread);
}

// read.php�ɏ�����n��
include P2_BASE_DIR . '/read.php';

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
