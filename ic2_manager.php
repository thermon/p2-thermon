<?php
/**
 * ImageCache2 - �����e�i���X
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
require_once 'HTML/Template/Flexy.php';
require_once P2EX_LIB_DIR . '/ic2/bootstrap.php';

// �ݒ�ǂݍ���
$ini = ic2_loadconfig();
if ($ini['Viewer']['cache'] && file_exists($_conf['iv2_cache_db_path'])) {
    $viewer_cache_exists = true;
} else {
    $viewer_cache_exists = false;
}

// �f�[�^�x�[�X�ɐڑ�
$db = DB::connect($ini['General']['dsn']);
if (DB::isError($db)) {
    p2die($result->getMessage());
}

// �e���v���[�g�G���W��������
$_flexy_options = array(
    'locale' => 'ja',
    'charset' => 'cp932',
    'compileDir' => $_conf['compile_dir'] . DIRECTORY_SEPARATOR . 'ic2',
    'templateDir' => P2EX_LIB_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
);

$flexy = new HTML_Template_Flexy($_flexy_options);

// }}}
// {{{ �f�[�^�x�[�X����E�t�@�C���폜

if (isset($_POST['action'])) {
    switch ($_POST['action']) {

        // �摜���폜����
        case 'dropZero':
        case 'dropAborn':
            if ($_POST['action'] == 'dropZero') {
                // �����N=0 �̉摜���폜����
                $where = $db->quoteIdentifier('rank') . ' = 0';
                if (isset($_POST['dropZeroLimit'])) {
                    // �擾�������Ԃ�����
                    switch ($_POST['dropZeroSelectTime']) {
                        case '24hours': $expires = 86400; break;
                        case 'aday':    $expires = 86400; break;
                        case 'aweek':   $expires = 86400 * 7; break;
                        case 'amonth':  $expires = 86400 * 31; break;
                        case 'ayear':   $expires = 86400 * 365; break;
                        default: $expires = NULL;
                    }
                    if ($expires !== NULL) {
                        $operator = ($_POST['dropZeroSelectType'] == 'within') ? '>' : '<';
                        $where .= sprintf(' AND %s %s %d',
                            $db->quoteIdentifier('time'),
                            $operator,
                            time() - $expires);
                    }
                }
                // �u���b�N���X�g�ɓo�^����
                $to_blacklist = !empty($_POST['dropZeroToBlackList']);
            } else {
                // ���ځ[��摜���폜���A�u���b�N���X�g�ɓo�^����
                $where = $db->quoteIdentifier('rank') . ' < 0';
                $to_blacklist = TRUE;
            }

            $sql = sprintf('SELECT %s FROM %s WHERE %s;',
                $db->quoteIdentifier('id'),
                $db->quoteIdentifier($ini['General']['table']),
                $where);
            $result = $db->getAll($sql, NULL, DB_FETCHMODE_ORDERED | DB_FETCHMODE_FLIPPED);
            if (DB::isError($result)) {
                P2Util::pushInfoHtml($result->getMessage());
                break;
            }
            $target = $result[0];
            $removed_files = IC2_DatabaseManager::remove($target, $to_blacklist);
            $flexy->setData('toBlackList', $to_blacklist);
            break;

        // PC�p�ȊO�̍쐬�ς݃T���l�C������������
        case 'clearThumb':
            $thumb_dir2 = $ini['General']['cachedir'] . '/' . $ini['Thumb2']['name'];
            $thumb_dir3 = $ini['General']['cachedir'] . '/' . $ini['Thumb3']['name'];
            $result_files2 = P2Util::garbageCollection($thumb_dir2, -1, '', '', TRUE);
            $result_files3 = P2Util::garbageCollection($thumb_dir3, -1, '', '', TRUE);
            $removed_files = array_merge($result_files2['successed'], $result_files3['successed']);
            $failed_files = array_merge($result_files2['failed'], $result_files3['failed']);
            if (!empty($failed_files)) {
                $info_msg_ht = '<p>�ȉ��̃t�@�C�����폜�ł��܂���ł����B</p>';
                $info_msg_ht .= '<ul><li>';
                $info_msg_ht .= implode('</li><li>', array_map('htmlspecialchars', $failed_files));
                $info_msg_ht .= '</li></ul>';
                P2Util::pushInfoHtml($info_msg_ht);
            }
            break;

        // �ꗗ�\���p�̃f�[�^�L���b�V������������
        case 'clearCache':
            // �ꗗ�\���p�f�[�^�L���b�V�����N���A
            if ($viewer_cache_exists) {
                $kvs = P2KeyValueStore::getStore($_conf['iv2_cache_db_path'],
                                                 P2KeyValueStore::CODEC_SERIALIZING);
                if ($kvs->clear() === false) {
                    P2Util::pushInfoHtml('<p>�ꗗ�\���p�̃f�[�^�L���b�V���������ł��܂���ł����B</p>');
                } else {
                    P2Util::pushInfoHtml('<p>�ꗗ�\���p�̃f�[�^�L���b�V�����������܂����B</p>');
                }
            }

            // �R���p�C���ς݃e���v���[�g���폜
            $result_files = P2Util::garbageCollection($flexy->options['compileDir'], -1, '', '', TRUE);
            $removed_files = $result_files['successed'];
            if (!empty($result_files['failed'])) {
                $info_msg_ht = '<p>�ȉ��̃R���p�C���ς݃e���v���[�g���폜�ł��܂���ł����B</p>';
                $info_msg_ht .= '<ul><li>';
                $info_msg_ht .= implode('</li><li>', array_map('htmlspecialchars', $result_files['failed']));
                $info_msg_ht .= '</li></ul>';
                P2Util::pushInfoHtml($info_msg_ht);
            }
            break;

        // �G���[���O����������
        case 'clearErrorLog':
            $result = $db->query('DELETE FROM ' . $db->quoteIdentifier($ini['General']['error_table']));
            if (DB::isError($result)) {
                P2Util::pushInfoHtml($result->getMessage());
            } else {
                P2Util::pushInfoHtml('<p>�G���[���O���������܂����B</p>');
            }
            break;

        // �u���b�N���X�g����������
        case 'clearBlackList':
            $result = $db->query('DELETE FROM ' . $db->quoteIdentifier($ini['General']['blacklist_table']));
            if (DB::isError($result)) {
                P2Util::pushInfoHtml($result->getMessage());
            } else {
                P2Util::pushInfoHtml('<p>�u���b�N���X�g���������܂����B</p>');
            }
            break;

        // �f�[�^�x�[�X���œK������
        case 'optimizeDB':
            // SQLite2 �̉摜�L���b�V���f�[�^�x�[�X��VACUUM
            if ($db->dsn['phptype'] == 'sqlite') {
                $result = $db->query('VACUUM');
                if (DB::isError($result)) {
                    P2Util::pushInfoHtml($result->getMessage());
                } else {
                    P2Util::pushInfoHtml('<p>�摜�f�[�^�x�[�X���œK�����܂����B</p>');
                }
            }

            // SQLite3 �̈ꗗ�\���p�f�[�^�L���b�V����VACUUM,REINDX
            if ($viewer_cache_exists) {
                $kvs = P2KeyValueStore::getStore($_conf['iv2_cache_db_path'],
                                                 P2KeyValueStore::CODEC_SERIALIZING);
                $kvs->optimize();
                unset($kvs);
                P2Util::pushInfoHtml('<p>�ꗗ�\���p�̃f�[�^�L���b�V�����œK�����܂����B</p>');
            }
            break;

        // ����`�̃��N�G�X�g
        default:
            P2Util::pushInfoHtml('<p>����`�̃��N�G�X�g�ł��B</p>');
    }

    if (isset($removed_files)) {
        $flexy->setData('removedFiles', $removed_files);
    }
}

// }}}
// {{{ �o��

$flexy->setData('skin', $skin_en);
$flexy->setData('php_self', $_SERVER['SCRIPT_NAME']);
$flexy->setData('info_msg', P2Util::getInfoHtml());
$flexy->setData('pc', !$_conf['ktai']);
$flexy->setData('iphone', $_conf['iphone']);
$flexy->setData('doctype', $_conf['doctype']);
$flexy->setData('extra_headers',   $_conf['extra_headers_ht']);
$flexy->setData('extra_headers_x', $_conf['extra_headers_xht']);
if ($db->dsn['phptype'] == 'sqlite' || $viewer_cache_exists) {
    $flexy->setData('enable_optimize_db', true);
} else {
    $flexy->setData('enable_optimize_db', false);
}

P2Util::header_nocache();
$flexy->compile('ic2mng.tpl.html');
$flexy->output();

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
