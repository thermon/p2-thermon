<?php
/**
 * ImageCache2 - �G���[���O�E�u���b�N���X�g�{��
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

define('P2_OUTPUT_XHTML', 1);

require_once './conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    p2die('ImageCache2�͖����ł��B', 'conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B');
}

// }}}
// {{{ ������

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB/DataObject.php';
require_once 'HTML/Template/Flexy.php';
require_once P2EX_LIB_DIR . '/ic2/findexec.inc.php';
require_once P2EX_LIB_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIB_DIR . '/ic2/DataObject/Common.php';

// }}}
// {{{ �ݒ�Ə���

// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

if (!isset($_REQUEST['table'])) {
    p2die('ImageCache2 - �s���ȃN�G��');
}

$mode = $_REQUEST['table'];
switch ($mode) {
    case 'errlog':
        require_once P2EX_LIB_DIR . '/ic2/DataObject/Errors.php';
        $table = new IC2_DataObject_Errors;
        $table->orderBy('occured ASC');
        $title = '�G���[���O';
        break;
    case 'blacklist':
        require_once P2EX_LIB_DIR . '/ic2/DataObject/BlackList.php';
        $table = new IC2_DataObject_BlackList;
        $table->orderBy('uri ASC');
        $title = '�u���b�N���X�g';
        break;
    default:
        p2die('ImageCache2 - �s���ȃN�G��');
}


$db = $table->getDatabaseConnection();
if (isset($_POST['clean'])) {
    $sql = 'DELETE FROM ' . $db->quoteIdentifier($table->__table);
    $result = $db->query($sql);
    if (DB::isError($result)) {
        p2die($result->getMessage());
    }
} elseif (isset($_POST['delete']) && isset($_POST['target']) && is_array($_POST['target'])) {
    foreach ($_POST['target'] as $target) {
        $delete = clone $table;
        $delete->uri = $target;
        $delete->delete();
    }
}

// }}}
// {{{ �o��

$_flexy_options = array(
    'locale' => 'ja',
    'charset' => 'cp932',
    'compileDir' => $_conf['compile_dir'] . DIRECTORY_SEPARATOR . 'ic2',
    'templateDir' => P2EX_LIB_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
);

$flexy = new HTML_Template_Flexy($_flexy_options);

$flexy->setData('php_self', $_SERVER['SCRIPT_NAME']);
$flexy->setData('skin', $skin_en);
$flexy->setData('title', $title);
$flexy->setData('mode', $mode);
$flexy->setData('reload_js', $_SERVER['SCRIPT_NAME'] . '?nt=' . time() . '&table=' . $mode);
$flexy->setData('info_msg', $_info_msg_ht);
$flexy->setData('pc', !$_conf['ktai']);
$flexy->setData('iphone', $_conf['iphone']);
$flexy->setData('doctype', $_conf['doctype']);
$flexy->setData('extra_headers',   $_conf['extra_headers_ht']);
$flexy->setData('extra_headers_x', $_conf['extra_headers_xht']);

if ($table->find()) {
    switch ($mode) {
        case 'errlog':
            $flexy->setData('data_renderer_errlog', TRUE);
            $flexy->setData('data', ic2dumptable_errlog($table));
            break;
        case 'blacklist':
            $flexy->setData('data_renderer_blacklist', TRUE);
            $flexy->setData('data', ic2dumptable_blacklist($table));
            break;
    }
}

P2Util::header_nocache();
$flexy->compile('ic2vt.tpl.html');
$flexy->output();

// }}}
// {{{ �֐�
// {{{ ic2dumptable_errlog()

/**
 * �G���[���O���擾����
 */
function ic2dumptable_errlog($dbdo)
{
    $data = array();
    while ($dbdo->fetch()) {
        $obj = new stdClass;
        $obj->uri = $dbdo->uri;
        $obj->date = date('Y-m-d (D) H:i:s', $dbdo->occured);
        $obj->code = $dbdo->errcode;
        $obj->message = mb_convert_encoding($dbdo->errmsg, 'CP932', 'UTF-8');
        $data[] = $obj;
    }
    return $data;
}

// }}}
// {{{ ic2dumptable_blacklist()

/**
 * �u���b�N���X�g���擾����
 */
function ic2dumptable_blacklist($dbdo)
{
    $data = array();
    while ($dbdo->fetch()) {
        $obj = new stdClass;
        $obj->uri = $dbdo->uri;
        switch ($dbdo->type) {
            case '0':
                $obj->type = '���������ς�';
                break;
            case '1':
                $obj->type = '���ځ[��';
                break;
            case '2':
                $obj->type = '�E�B���X����';
                break;
            default:
                $type = '???';
        }
        $obj->size = $dbdo->size;
        $obj->md5 = $dbdo->md5;
        $data[] = $obj;
    }
    return $data;
}

// }}}
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
