<?php
/**
 * ImageCache2 - �摜�L���b�V���ꗗ
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

define('P2_SESSION_CLOSE_AFTER_AUTHENTICATION', 0);
define('P2_OUTPUT_XHTML', 1);

require_once './conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    p2die('ImageCache2�͖����ł��B', 'conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B');
}

if ($_conf['iphone']) {
    $_conf['extra_headers_ht'] .= <<<EOP
\n<link rel="stylesheet" type="text/css" href="css/ic2_iphone.css?{$_conf['p2_version_id']}">
<link rel="stylesheet" type="text/css" href="css/iv2_iphone.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/json2.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/ic2_iphone.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/iv2_iphone.js?{$_conf['p2_version_id']}"></script>\n
EOP;
    $_conf['extra_headers_xht'] .= <<<EOP
\n<link rel="stylesheet" type="text/css" href="css/ic2_iphone.css?{$_conf['p2_version_id']}" />
<link rel="stylesheet" type="text/css" href="css/iv2_iphone.css?{$_conf['p2_version_id']}" />
<script type="text/javascript" src="js/json2.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/ic2_iphone.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/iv2_iphone.js?{$_conf['p2_version_id']}"></script>\n
EOP;
}

// �r���[����p�̉B���v�f

if ($_conf['view_forced_by_query']) {
    output_add_rewrite_var('b', $_conf['b']);
}

// }}}
// {{{ ������

// ���C�u�����ǂݍ���
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';
require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';
require_once P2EX_LIB_DIR . '/ic2/bootstrap.php';
require_once P2EX_LIB_DIR . '/ic2/QuickForm/Rules.php';

// }}}
// {{{ config

// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

// Exif�\�����L�����H
$show_exif = ($ini['Viewer']['exif'] && extension_loaded('exif'));

$_default_mode = (int)$_conf['expack.ic2.viewer_default_mode'];
if ($_default_mode < 0 || $_default_mode > 3) {
    $_default_mode = 0;
}

// �t�H�[���̃f�t�H���g�l
$_defaults = array(
    'page'  => 1,
    'cols'  => $ini['Viewer']['cols'],
    'rows'  => $ini['Viewer']['rows'],
    'inum'  => $ini['Viewer']['inum'],
    'order' => $ini['Viewer']['order'],
    'sort'  => $ini['Viewer']['sort'],
    'field' => $ini['Viewer']['field'],
    'key'   => '',
    'threshold' => $ini['Viewer']['threshold'],
    'compare' => '>=',
    'mode' => $_default_mode,
);

// �t�H�[���̌Œ�l
$_constants = array(
    'start'   => '<<',
    'prev'    => '<',
    'next'    => '>',
    'end'     => '>>',
    'jump'    => 'Go',
    'search'  => '����',
    'cngmode' => '�ύX',
    '_hint'   => $_conf['detect_hint'],
);

// 臒l��r���@
$_compare = array(
    '>=' => '&gt;=',
    '='  => '=',
    '<=' => '&lt;=',
);

// 臒l
$_threshold = array(
    '-1' => '-1',
    '0' => '0',
    '1' => '1',
    '2' => '2',
    '3' => '3',
    '4' => '4',
    '5' => '5',
);

// �\�[�g�
$_order = array(
    'time' => '�擾����',
    'uri'  => 'URL',
    'date_uri' => '���t+URL',
    'date_uri2' => '���t+URL(2)',
    'name' => '�t�@�C����',
    'size' => '�t�@�C���T�C�Y',
    'width' => '����',
    'height' => '����',
    'pixels' => '�s�N�Z����',
    'id' => 'ID',
);

// �\�[�g����
$_sort = array(
    'ASC'  => '����',
    'DESC' => '�~��',
);

// �����t�B�[���h
$_field = array(
    'uri'   => 'URL',
    'name'  => '�t�@�C����',
    'memo'  => '����',
);

// ���[�h
$_mode = array(
    '3' => '��Ȳق���',
    '0' => '�ꗗ',
    '1' => '�ꊇ�ύX',
    '2' => '�ʊǗ�',
);

// �g�їp�ɕϊ��i�t�H�[�����p�P�b�g�ߖ�̑ΏۊO�Ƃ��邽�߁j
if ($_conf['ktai']) {
    foreach ($_order as $_k => $_v) {
        $_order[$_k] = mb_convert_kana($_v, 'ask');
    }
    foreach ($_field as $_k => $_v) {
        $_field[$_k] = mb_convert_kana($_v, 'ask');
    }
}

// }}}
// {{{ prepare (DB & Cache)

// DB_DataObject���p������DAO
$icdb = new IC2_DataObject_Images;
$db = $icdb->getDatabaseConnection();
$db_class = strtolower(get_class($db));

// �T���l�C���쐬�N���X
$thumb = new IC2_Thumbnailer(IC2_Thumbnailer::SIZE_DEFAULT);

if ($ini['Viewer']['cache']) {
    $kvs = P2KeyValueStore::getStore($_conf['iv2_cache_db_path'],
                                     P2KeyValueStore::CODEC_SERIALIZING);
    $cache_lifetime = (int)$ini['Viewer']['cache_lifetime'];
    if (array_key_exists('cache_clean', $_REQUEST)) {
        $cache_clear = $_REQUEST['cache_clean'];
    } else {
        $cache_clear = false;
    }
    $optimize_db = false;

    if ($cache_clear == 'all') {
        $kvs->clear();
        $optimize_db = true;
    } elseif ($cache_clear == 'gc') {
        $kvs->gc($cache_lifetime);
        $optimize_db = true;
    }

    if ($optimize_db) {
        // �L���b�V����VACUUM,REINDEX
        $kvs->optimize();

        // SQLite�Ȃ�VACUUM�����s
        if ($db_class == 'db_sqlite') {
            $result = $db->query('VACUUM');
            if (DB::isError($result)) {
                p2die($result->getMessage());
            }
        }
    }

    $cache = new P2KeyValueStore_FunctionCache($kvs, $cache_lifetime);
    $imageInfo_getExtraInfo = $cache->createProxy('IC2_ImageInfo::getExtraInfo');
    $imageInfo_getExifData = $cache->createProxy('IC2_ImageInfo::getExifData');
    $editForm_imgManager = $cache->createProxy('IC2_EditForm::imgManager');

    $use_cache = true;
} else {
    $use_cache = false;
}

// }}}
// {{{ prepare (Form & Template)

// �y�[�W�J�ڗp�t�H�[����ݒ�
// �y�[�W�J�ڂ�GET�ōs�����A�摜���̍X�V��POST�ōs���̂łǂ���ł��󂯓����悤�ɂ���
// �i�����_�����O�O�� $qf->updateAttributes(array('method' => 'get')); �Ƃ���j
$_attribures = array('accept-charset' => 'UTF-8,Shift_JIS');
$_method = ($_SERVER['REQUEST_METHOD'] == 'GET') ? 'get' : 'post';
$qf = new HTML_QuickForm('go', $_method, $_SERVER['SCRIPT_NAME'], '_self', $_attribures);
$qf->registerRule('numberInRange',  null, 'IC2_QuickForm_Rule_NumberInRange');
$qf->registerRule('inArray',        null, 'IC2_QuickForm_Rule_InArray');
$qf->registerRule('arrayKeyExists', null, 'IC2_QuickForm_ArrayKeyExists');
$qf->setDefaults($_defaults);
$qf->setConstants($_constants);
$qfe = array();

// �t�H�[���v�f�̒�`

// �y�[�W�ړ��̂��߂�submit�v�f
$qfe['start'] = $qf->addElement('button', 'start');
$qfe['prev']  = $qf->addElement('button', 'prev');
$qfe['next']  = $qf->addElement('button', 'next');
$qfe['end']   = $qf->addElement('button', 'end');
$qfe['jump']  = $qf->addElement('button', 'jump');

// �\�����@�Ȃǂ��w�肷��input�v�f
$qfe['page']      = $qf->addElement('text', 'page', '�y�[�W�ԍ����w��', array('size' => 3));
$qfe['cols']      = $qf->addElement('text', 'cols', '��', array('size' => 3, 'maxsize' => 2));
$qfe['rows']      = $qf->addElement('text', 'rows', '�c', array('size' => 3, 'maxsize' => 2));
$qfe['order']     = $qf->addElement('select', 'order', '���я�', $_order);
$qfe['sort']      = $qf->addElement('select', 'sort', '����', $_sort);
$qfe['field']     = $qf->addElement('select', 'field', '�t�B�[���h', $_field);
$qfe['key']       = $qf->addElement('text', 'key', '�L�[���[�h', array('size' => 20));
$qfe['compare']   = $qf->addElement('select', 'compare', '��r���@', $_compare);
$qfe['threshold'] = $qf->addElement('select', 'threshold', '�������l', $_threshold);

// �����R�[�h����̃q���g�ɂ���B���v�f
$qfe['_hint'] = $qf->addElement('hidden', '_hint');

// ���������s����submit�v�f
$qfe['search'] = $qf->addElement('submit', 'search');

// ���[�h�ύX������select�v�f
$qfe['mode'] = $qf->addElement('select', 'mode', '���[�h', $_mode);

// ���[�h�ύX���m�肷��submit�v�f
$qfe['cngmode'] = $qf->addElement('submit', 'cngmode');

// �t�H�[���̃��[��
$qf->addRule('cols', '1 to 20',  'numberInRange', array('min' => 1, 'max' => 20),  'client', true);
$qf->addRule('rows', '1 to 100', 'numberInRange', array('min' => 1, 'max' => 100), 'client', true);
$qf->addRule('order', 'invalid order.', 'arrayKeyExists', $_order);
$qf->addRule('sort',  'invalid sort.',  'arrayKeyExists', $_sort);
$qf->addRule('field', 'invalid field.', 'arrayKeyExists', $_field);
$qf->addRule('threshold', '-1 to 5', 'numberInRange', array('min' => -1, 'max' => 5));
$qf->addRule('compare', 'invalid compare.', 'arrayKeyExists', $_compare);
$qf->addRule('mode', 'invalid mode.', 'arrayKeyExists', $_mode);

// Flexy
$_flexy_options = array(
    'locale' => 'ja',
    'charset' => 'cp932',
    'compileDir' => $_conf['compile_dir'] . DIRECTORY_SEPARATOR . 'iv2',
    'templateDir' => P2EX_LIB_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
    'plugins' => array('P2Util' => P2_LIB_DIR . '/P2Util.php')
);

if (!is_dir($_conf['compile_dir'])) {
    FileCtl::mkdir_r($_conf['compile_dir']);
}

$flexy = new HTML_Template_Flexy($_flexy_options);

$flexy->setData('php_self', $_SERVER['SCRIPT_NAME']);
$flexy->setData('base_dir', dirname($_SERVER['SCRIPT_NAME']));
$flexy->setData('p2vid', P2_VERSION_ID);
$flexy->setData('_hint', $_conf['detect_hint']);
if ($_conf['iphone']) {
    $flexy->setData('top_url', 'index.php');
} elseif ($_conf['ktai']) {
    $flexy->setData('k_color', array(
        'c_bgcolor' => !empty($_conf['mobile.background_color']) ? $_conf['mobile.background_color'] : '#ffffff',
        'c_text'    => !empty($_conf['mobile.text_color'])  ? $_conf['mobile.text_color']  : '#000000',
        'c_link'    => !empty($_conf['mobile.link_color'])  ? $_conf['mobile.link_color']  : '#0000ff',
        'c_vlink'   => !empty($_conf['mobile.vlink_color']) ? $_conf['mobile.vlink_color'] : '#9900ff',
    ));
    $flexy->setData('top_url', dirname($_SERVER['SCRIPT_NAME']) . '/index.php');
    $flexy->setData('accesskey', $_conf['accesskey']);
} else {
    $flexy->setData('skin', str_replace('&amp;', '&', $skin_en));
}
$flexy->setData('pc', !$_conf['ktai']);
$flexy->setData('iphone', $_conf['iphone']);
$flexy->setData('doctype', $_conf['doctype']);
$flexy->setData('extra_headers',   $_conf['extra_headers_ht']);
$flexy->setData('extra_headers_x', $_conf['extra_headers_xht']);

// }}}
// {{{ validate

// ����
$qf->validate();
$sv = $qf->getSubmitValues();
$page      = IC2_ParameterUtility::getValidValue('page',   $_defaults['page'], 'intval');
$cols      = IC2_ParameterUtility::getValidValue('cols',   $_defaults['cols'], 'intval');
$rows      = IC2_ParameterUtility::getValidValue('rows',   $_defaults['rows'], 'intval');
$order     = IC2_ParameterUtility::getValidValue('order',  $_defaults['order']);
$sort      = IC2_ParameterUtility::getValidValue('sort',   $_defaults['sort'] );
$field     = IC2_ParameterUtility::getValidValue('field',  $_defaults['field']);
$key       = IC2_ParameterUtility::getValidValue('key',    $_defaults['key']);
$threshold = IC2_ParameterUtility::getValidValue('threshold', $_defaults['threshold'], 'intval');
$compare   = IC2_ParameterUtility::getValidValue('compare',   $_defaults['compare']);
$mode      = IC2_ParameterUtility::getValidValue('mode',      $_defaults['mode'], 'intval');

// �g�їp�ɒ���
if ($_conf['ktai']) {
    $lightbox = false;
    $mode = 1;
    $inum = (int) $ini['Viewer']['inum'];
    $overwritable_params = array('order', 'sort', 'field', 'key', 'threshold', 'compare');

    // �G������ǂݍ���
    require_once P2_LIB_DIR . '/emoji.inc.php';
    $emj = p2_get_emoji();
    $flexy->setData('e', $emj);
    $flexy->setData('ak', $_conf['k_accesskey_at']);
    $flexy->setData('as', $_conf['k_accesskey_st']);

    // �t�B���^�����O�p�t�H�[����\��
    if (!empty($_GET['show_iv2_kfilter'])) {
        !defined('P2_NO_SAVE_PACKET') && define('P2_NO_SAVE_PACKET', true);
        $r = new HTML_QuickForm_Renderer_ObjectFlexy($flexy);
        $qfe['key']->removeAttribute('size');
        $qf->updateAttributes(array('method' => 'get'));
        $qf->accept($r);
        $qfObj = $r->toObject();
        $flexy->setData('page', $page);
        $flexy->setData('move', $qfObj);
        P2Util::header_nocache();
        $flexy->compile('iv2if.tpl.html');
        $flexy->output();
        exit;
    }
    // �t�B���^�����Z�b�g
    elseif (!empty($_GET['reset_filter'])) {
        unset($_SESSION['iv2i_filter']);
        session_write_close();
    }
    // �t�B���^��ݒ�
    elseif (!empty($_GET['session_no_close'])) {
        foreach ($overwritable_params as $ow_key) {
            if (isset($$ow_key)) {
                $_SESSION['iv2i_filter'][$ow_key] = $$ow_key;
            }
        }
        session_write_close();
    }
    // �t�B���^�����O�p�ϐ����X�V
    elseif (!empty($_SESSION['iv2i_filter'])) {
        foreach ($overwritable_params as $ow_key) {
            if (isset($_SESSION['iv2i_filter'][$ow_key])) {
                $$ow_key = $_SESSION['iv2i_filter'][$ow_key];
            }
        }
    }
} else {
    //$lightbox = ($mode == 0 || $mode == 3) ? $ini['Viewer']['lightbox'] : false;
    $lightbox = $ini['Viewer']['lightbox'];
}

// }}}
// {{{ query

$removed_files = array();

// 臒l�Ńt�B���^�����O
if (!($threshold == -1 && $compate == '>=')) {
    $icdb->whereAddQuoted('rank', $compare, $threshold);
}

// �L�[���[�h����������Ƃ�
if ($key !== '') {
    $keys = explode(' ', $icdb->uniform($key, 'CP932'));
    foreach ($keys as $k) {
        $operator = 'LIKE';
        $wildcard = '%';
        $not = false;
        if ($k[0] == '-' && strlen($k) > 1) {
            $not = true;
            $k = substr($k, 1);
        }
        if (strpos($k, '%') !== false || strpos($k, '_') !== false) {
            // SQLite2��LIKE���Z�q�̉E�ӂŃo�b�N�X���b�V���ɂ��G�X�P�[�v��
            // ESCAPE�ŃG�X�P�[�v�������w�肷�邱�Ƃ��ł��Ȃ��̂�GLOB���Z�q���g��
            if ($db_class == 'db_sqlite') {
                if (strpos($k, '*') !== false || strpos($k, '?') !== false) {
                    p2die('ImageCache2 Warning', '�u%�܂���_�v�Ɓu*�܂���?�v�����݂���L�[���[�h�͎g���܂���B');
                } else {
                    $operator = 'GLOB';
                    $wildcard = '*';
                }
            } else {
                $k = preg_replace('/[%_]/', '\\\\$0', $k);
            }
        }
        $expr = $wildcard . $k . $wildcard;
        if ($not) {
            $operator = 'NOT ' . $operator;
        }
        $icdb->whereAddQuoted($field, $operator, $expr);
    }
    $qfe['key']->setValue($key);
}

// �d���摜���X�L�b�v����Ƃ�
$_find_duplicated = 0; // �����I�p�����[�^�A�o�^���R�[�h��������ȏ�̉摜�݂̂𒊏o
if ($ini['Viewer']['unique'] || $_find_duplicated > 1) {
    $subq = 'SELECT ' . (($sort == 'ASC') ? 'MIN' : 'MAX') . '(id) FROM ';
    $subq .= $db->quoteIdentifier($ini['General']['table']);
    if (isset($keys)) {
        // �T�u�N�G�����Ńt�B���^�����O����̂Őe�N�G����WHERE����p�N���Ă��ă��Z�b�g
        $subq .= $icdb->_query['condition'];
        $icdb->whereAdd();
    }
    // md5�����ŃO���[�v�����Ă��\���Ƃ͎v�����ǁA�ꉞ�B
    $subq .= ' GROUP BY size, md5, mime';
    if ($_find_duplicated > 1) {
        $subq .= sprintf(' HAVING COUNT(*) > %d', $_find_duplicated - 1);
    }
    // echo '<!--', mb_convert_encoding($subq, 'CP932', 'UTF-8'), '-->';
    $icdb->whereAdd("id IN ($subq)");
}

// �f�[�^�x�[�X���X�V����Ƃ�
if (isset($_POST['edit_submit']) && !empty($_POST['change'])) {

    $target = array_unique(array_map('intval', $_POST['change']));

    switch ($mode) {

    // �ꊇ�Ńp�����[�^�ύX
    case 1:
        // �����N��ύX
        $newrank = IC2_ParameterUtility::intoRange($_POST['setrank'], -1, 5);
        IC2_DatabaseManager::setRank($target, $newrank);
        // ������ǉ�
        if (!empty($_POST['addmemo'])) {
            $newmemo = get_magic_quotes_gpc() ? stripslashes($_POST['addmemo']) : $_POST['addmemo'];
            $newmemo = $icdb->uniform($newmemo, 'CP932');
            if ($newmemo !== '') {
                 IC2_DatabaseManager::addMemo($target, $newmemo);
            }
        }
        break;

    // �ʂɃp�����[�^�ύX
    case 2:
        // �X�V�p�̃f�[�^���܂Ƃ߂�
        $updated = array();
        $removed = array();
        $to_blacklist = false;
        $no_blacklist = false;

        foreach ($target as $id) {
            if (!empty($_POST['img'][$id]['remove'])) {
                if (!empty($_POST['img'][$id]['black'])) {
                    $to_blacklist = true;
                    $removed[$id] = true;
                } else {
                    $no_blacklist = true;
                    $removed[$id] = false;
                }
            } else {
                $newmemo = get_magic_quotes_gpc() ? stripslashes($_POST['img'][$id]['memo']) : $_POST['img'][$id]['memo'];
                $data = array(
                    'rank' => intval($_POST['img'][$id]['rank']),
                    'memo' => $icdb->uniform($newmemo, 'CP932')
                );
                if (0 < $id && -1 <= $data['rank'] && $data['rank'] <= 5) {
                    $updated[$id] = $data;
                }
            }
        }

        // �����X�V
        if (count($updated) > 0) {
            IC2_DatabaseManager::update($updated);
        }

        // �폜�i���u���b�N���X�g����j
        if (count($removed) > 0) {
            foreach ($removed as $id => $to_blacklist) {
                $removed_files = array_merge($removed_files,
                                             IC2_DatabaseManager::remove(array($id), $to_blacklist));
            }
            if ($to_blacklist) {
                if ($no_blacklist) {
                    $flexy->setData('toBlackListAll', false);
                    $flexy->setData('toBlackListPartial', true);
                } else {
                    $flexy->setData('toBlackListAll', true);
                    $flexy->setData('toBlackListPartial', false);
                }
            } else {
                $flexy->setData('toBlackListAll', false);
                $flexy->setData('toBlackListPartial', false);
            }
        }
        break;

    } // endswitch

// �ꊇ�ŉ摜���폜����Ƃ�
} elseif ($mode == 1 && isset($_POST['edit_remove']) && !empty($_POST['change'])) {
    $target = array_unique(array_map('intval', $_POST['change']));
    $to_blacklist = !empty($_POST['edit_toblack']);
    $removed_files = array_merge($removed_files,
                                 IC2_DatabaseManager::remove($target, $to_blacklist));
    $flexy->setData('toBlackList', $to_blacklist);
}

// }}}
// {{{ build

// �����R�[�h���𐔂���
//$db->setFetchMode(DB_FETCHMODE_ORDERED);
//$all = (int)$icdb->count('*', true);
//$db->setFetchMode(DB_FETCHMODE_ASSOC);
$sql = sprintf('SELECT COUNT(*) FROM %s %s', $db->quoteIdentifier($ini['General']['table']), $icdb->_query['condition']);
$all = $db->getOne($sql);
if (DB::isError($all)) {
    p2die($all->getMessage());
}

// �}�b�`���郌�R�[�h���Ȃ�������G���[��\���A���R�[�h������Ε\���p�I�u�W�F�N�g�ɒl����
if ($all == 0) {

    // ���R�[�h�Ȃ�
    $flexy->setData('nomatch', true);
    $flexy->setData('reset', $_SERVER['SCRIPT_NAME']);
    if ($_conf['ktai']) {
        $flexy->setData('kfilter', !empty($_SESSION['iv2i_filter']));
    }
    $qfe['start']->updateAttributes('disabled');
    $qfe['prev']->updateAttributes('disabled');
    $qfe['next']->updateAttributes('disabled');
    $qfe['end']->updateAttributes('disabled');
    $qfe['page']->updateAttributes('disabled');
    $qfe['jump']->updateAttributes('disabled');

} else {

    // ���R�[�h����
    $flexy->setData('nomatch', false);

    // �\���͈͂�ݒ�
    $ipp = $_conf['ktai'] ? $inum : $cols * $rows; // images per page
    $last_page = ceil($all / $ipp);

    // �y�[�W�J�ڗp�p�����[�^������
    if (isset($sv['search']) || isset($sv['cngmode'])) {
        $page = 1;
    } elseif (isset($sv['page'])) {
        $page = max(1, min((int)$sv['page'], $last_page));
    } else {
        $page = 1;
    }
    $prev_page = max(1, $page - 1);
    $next_page = min($page + 1, $last_page);

    // �y�[�W�J�ڗp�����N�iiPhone�j�𐶐�
    if ($_conf['iphone']) {
        $pg_base = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);
        $pager = '';
        if ($page != 1) {
            $pager .= sprintf('<a href="%s?page=%d">%s</a> ', $pg_base,          1, $emj['lt2']);
            $pager .= sprintf('<a href="%s?page=%d">%s</a> ', $pg_base, $prev_page, $emj['lt1']);
        }
        $pager .= sprintf('%d/%d', $page, $last_page);
        if ($page != $last_page) {
            $pager .= sprintf(' <a href="%s?page=%d">%s</a>', $pg_base, $next_page, $emj['rt1']);
            $pager .= sprintf(' <a href="%s?page=%d">%s</a>', $pg_base, $last_page, $emj['rt2']);
        }
        $flexy->setData('pager', $pager);

    // �y�[�W�J�ڗp�����N�i�g�сj�𐶐�
    } elseif ($_conf['ktai']) {
        $pg_base = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);
        $pg_pos = sprintf('%d/%d', $page, $last_page);
        $pager1 = '';
        $pager2 = '';
        if ($page != 1) {
            $pager1 .= sprintf('<a href="%s?page=%d"%s>%s%s</a> ',
                               $pg_base,
                               1,
                               $_conf['k_accesskey_at'][1],
                               $_conf['k_accesskey_st'][1],
                               $emj['lt2']
                               );
            $pager1 .= sprintf('<a href="%s?page=%d"%s>%s%s</a> ',
                               $pg_base,
                               $prev_page,
                               $_conf['k_accesskey_at'][4],
                               $_conf['k_accesskey_st'][4],
                               $emj['lt1']
                               );
            $pager2 .= sprintf('<a href="%s?page=%d">%s</a> ', $pg_base,          1, $emj['lt2']);
            $pager2 .= sprintf('<a href="%s?page=%d">%s</a> ', $pg_base, $prev_page, $emj['lt1']);
        }
        $pager1 .= $pg_pos;
        $pager2 .= $pg_pos;
        if ($page != $last_page) {
            $pager1 .= sprintf(' <a href="%s?page=%d">%s</a>', $pg_base, $next_page, $emj['rt1']);
            $pager1 .= sprintf(' <a href="%s?page=%d">%s</a>', $pg_base, $last_page, $emj['rt2']);
            $pager2 .= sprintf(' <a href="%s?page=%d"%s>%s%s</a>',
                               $pg_base,
                               $next_page,
                               $_conf['k_accesskey_at'][6],
                               $_conf['k_accesskey_st'][6],
                               $emj['rt1']
                               );
            $pager2 .= sprintf(' <a href="%s?page=%d"%s>%s%s</a>',
                               $pg_base,
                               $last_page,
                               $_conf['k_accesskey_at'][9],
                               $_conf['k_accesskey_st'][9],
                               $emj['rt2']
                               );
        }
        $flexy->setData('pager1', $pager1);
        $flexy->setData('pager2', $pager2);

    // �y�[�W�J�ڗp�t�H�[���iPC�j�𐶐�
    } else {
        $mf_hiddens = array(
            '_hint' => $_conf['detect_hint'], 'mode' => $mode,
            'page' => $page, 'cols' => $cols, 'rows' => $rows,
            'order' => $order, 'sort' => $sort,
            'field' => $field, 'key' => $key,
            'compare' => $compare, 'threshold' => $threshold
        );
        $pager_q = $mf_hiddens;
        mb_convert_variables('UTF-8', 'CP932', $pager_q);

        // �y�[�W�ԍ����X�V
        $qfe['page']->setValue($page);
        $qf->addRule('page', "1 to {$last_page}", 'numberInRange', array('min' => 1, 'max' => $last_page), 'client', true);

        // �ꎞ�I�Ƀp�����[�^��؂蕶���� & �ɂ��Č��݂̃y�[�W��URL�𐶐�
        $pager_separator = ini_get('arg_separator.output');
        ini_set('arg_separator.output', '&');
        $flexy->setData('current_page', $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($pager_q));
        ini_set('arg_separator.output', $pager_separator);
        unset($pager_q, $pager_separator);

        // �y�[�W����ړ��{�^���̑������X�V
        if ($page == 1) {
            $qfe['start']->updateAttributes('disabled');
            $qfe['prev']->updateAttributes('disabled');
        } else {
            $qfe['start']->updateAttributes(array('onclick' => "pageJump(1)"));
            $qfe['prev']->updateAttributes(array('onclick' => "pageJump({$prev_page})"));
        }

        // �y�[�W�O���ړ��{�^���̑������X�V
        if ($page == $last_page) {
            $qfe['next']->updateAttributes('disabled');
            $qfe['end']->updateAttributes('disabled');
        } else {
            $qfe['next']->updateAttributes(array('onclick' => "pageJump({$next_page})"));
            $qfe['end']->updateAttributes(array('onclick' => "pageJump({$last_page})"));
        }

        // �y�[�W�w��ړ��p�{�^���̑������X�V
        if ($last_page == 1) {
            $qfe['jump']->updateAttributes('disabled');
        } else {
            $qfe['jump']->updateAttributes(array('onclick' => "if(validate_go(this.form))pageJump(this.form.page.value)"));
        }
    }

    // �ҏW���[�h�p�t�H�[���𐶐�
    if ($mode == 1 || $mode == 2) {
        $flexy->setData('editFormHeader', IC2_EditForm::header((isset($mf_hiddens) ? $mf_hiddens : array()), $mode));
        if ($mode == 1) {
            $flexy->setData('editFormCheckAllOn', IC2_EditForm::checkAllOn());
            $flexy->setData('editFormCheckAllOff', IC2_EditForm::checkAllOff());
            $flexy->setData('editFormCheckAllReverse', IC2_EditForm::checkAllReverse());
            $flexy->setData('editFormSelect', IC2_EditForm::selectRank($_threshold));
            $flexy->setData('editFormText', IC2_EditForm::textMemo());
            $flexy->setData('editFormSubmit', IC2_EditForm::submit());
            $flexy->setData('editFormReset', IC2_EditForm::reset());
            $flexy->setData('editFormRemove', IC2_EditForm::remove());
            $flexy->setData('editFormBlackList', IC2_EditForm::toblack());
        } elseif ($mode == 2) {
            $flexy->setData('editForm', new IC2_EditForm_Object);
        }
    }

    // DB����擾����͈͂�ݒ肵�Č���
    $from = ($page - 1) * $ipp;
    if ($order == 'pixels') {
        $orderBy = '(width * height) ' . $sort;
    } elseif ($order == 'date_uri' || $order == 'date_uri2') {
        if ($db_class == 'db_sqlite') {
            /*
            function iv2_sqlite_unix2date($ts)
            {
                return date('Ymd', (int)$ts);
            }
            sqlite_create_function($db->connection, 'unix2date', 'iv2_sqlite_unix2date', 1);
            $time2date = 'unix2date("time")';
            */
            $time2date = 'php(\'date\', \'Ymd\', "time")';
        } else {
            // 32400 = 9*60*60 (�����␳)
            $time2date = sprintf('floor((%s + 32400) / 86400)', $db->quoteIdentifier('time'));
        }
        $orderBy .= sprintf('%s %s, %s ', $time2date, $sort, $db->quoteIdentifier('uri'));
        if ($order == 'date_uri') {
            $orderBy .= $sort;
        } else {
            $orderBy .= ($sort == 'ASC') ? 'DESC' : 'ASC';
        }
    } else {
        $orderBy = $db->quoteIdentifier($order) . ' ' . $sort;
    }
    $orderBy .= ' , id ' . $sort;
    $icdb->orderBy($orderBy);
    $icdb->limit($from, $ipp);
    $found = $icdb->find();

    // �e�[�u���̃u���b�N�ɕ\������l���擾&�I�u�W�F�N�g�ɑ��
    $flexy->setData('all',  $all);
    $flexy->setData('cols', $cols);
    $flexy->setData('last', $last_page);
    $flexy->setData('from', $from + 1);
    $flexy->setData('to',   $from + $found);
    $flexy->setData('submit', array());
    $flexy->setData('reset', array());

    if ($_conf['ktai']) {
        $show_exif = false;
        $popup = false;
        $r_type = ($ini['General']['redirect'] == 1) ? 1 : 2;
    } else {
        switch ($mode) {
            case 3:
                $show_exif = false;
            case 2:
                $popup = false;
                break;
            default:
                $popup = true;
        }
        $r_type = 1;
    }
    $items = array();
    if (!empty($_SERVER['REQUEST_URI'])) {
        $k_backto = '&from=' . rawurlencode($_SERVER['REQUEST_URI']);
    } else {
        $k_backto = '';
    }

    while ($icdb->fetch()) {
        // �������ʂ�z��ɂ��A�����_�����O�p�̗v�f��t��
        // �z��ǂ����Ȃ�+���Z�q�ŗv�f��ǉ��ł���
        // �i�L�[�̏d������l���㏑���������Ƃ���array_merge()���g���j
        $img = $icdb->toArray();
        mb_convert_variables('CP932', 'UTF-8', $img);
        // �����N�E�����͕ύX����邱�Ƃ������A�ꗗ�p�̃f�[�^�L���b�V���ɉe����^���Ȃ��悤�ɕʂɏ�������
        $status = array();
        $status['rank'] = $img['rank'];
        $status['rank_f'] = ($img['rank'] == -1) ? '���ځ[��' : $img['rank'];
        if ($img['rank'] == -1) {
            $status['rank_i'] = '<img src="img/sn1a.png" width="16" height="16">';
        } elseif ($img['rank'] > 0 && $img['rank'] <= 5) {
            $status['rank_i'] = str_repeat('<img src="img/s1a.png" width="16" height="16">', $img['rank']);
        } else {
            $status['rank_i'] = '';
        }
        $status['memo'] = $img['memo'];
        unset($img['rank'], $img['memo']);

        // �\���p�ϐ���ݒ�
        if ($use_cache) {
            $add = $imageInfo_getExtraInfo->invoke($img);
            if ($mode == 1) {
                $chk = IC2_EditForm::imgChecker($img); // ��r�I�y���̂ŃL���b�V�����Ȃ�
                $add += $chk;
            } elseif ($mode == 2) {
                $mng = $editForm_imgManager->invoke($img, $status);
                $add += $mng;
            }
        } else {
            $add = IC2_ImageInfo::getExtraInfo($img);
            if ($mode == 1) {
                $chk = IC2_EditForm::imgChecker($img);
                $add += $chk;
            } elseif ($mode == 2) {
                $mng = IC2_EditForm::imgManager($img, $status);
                $add += $mng;
            }
        }
        // �I���W�i���摜�����݂��Ȃ����R�[�h�������ō폜
        if ($ini['Viewer']['delete_src_not_exists'] && !file_exists($add['src'])) {
            $add['thumb_k'] = $add['thumb'] = 'img/ic_removed.png';
            $add['t_width'] = $add['t_height'] = 32;
            $to_blacklist = false;
            $removed_files = array_merge($removed_files,
                                         IC2_DatabaseManager::remove(array($img['id'], $to_blacklist)));
            $flexy->setData('toBlackList', $to_blacklist);
        } else {
            if (!file_exists($add['thumb'])) {
                // �����_�����O���Ɏ�����htmlspecialchars()�����̂�&amp;�ɂ��Ȃ�
                $add['thumb'] = 'ic2.php?r=' . $r_type . '&t=1';
                if (file_exists($add['src'])) {
                    $add['thumb'] .= '&id=' . $img['id'];
                } else {
                    $add['thumb'] .= '&uri=' . rawurlencode($img['uri']);
                }
            }
            if ($_conf['ktai']) {
                $add['thumb_k'] = 'ic2.php?r=0&t=2';
                if (file_exists($add['src'])) {
                    $add['thumb_k'] .= '&id=' . $img['id'];
                } else {
                    $add['thumb_k'] .= '&uri=' . rawurlencode($img['uri']);
                }
                $add['thumb_k'] .= $k_backto;
            }
        }
        $item = array_merge($img, $add, $status);

        // Exif�����擾
        if ($show_exif && file_exists($add['src']) && $img['mime'] == 'image/jpeg') {
            if ($use_cache) {
                $item['exif'] = $imageInfo_getExifData->invoke($add['src']);
            } else {
                $item['exif'] = IC2_ImageInfo::getExifData($add['src']);
            }
        } else {
            $item['exif'] = null;
        }

        // Lightbox Plus �p�p�����[�^��ݒ�
        if ($lightbox) {
            $item['lightbox_attrs'] = ' rel="lightbox[iv2]" class="ineffectable"';
            $item['lightbox_attrs'] .= ' title="' . htmlspecialchars($item['memo'], ENT_QUOTES) . '"';
        } else {
            $item['lightbox_attrs'] = '';
        }

        $items[] = $item;
    }

    $i = count($items); // == $found
    // �e�[�u���̗]���𖄂߂邽�߂�null��}��
    if (!$_conf['ktai'] && $i > $cols && ($j = $i % $cols) > 0) {
        for ($k = 0; $k < $cols - $j; $k++) {
            $items[] = null;
            $i++;
        }
    }
    // ���̎��_�� $i == $cols * ���R��

    $flexy->setData('items', $items);
    $flexy->setData('popup', $popup);
    $flexy->setData('matrix', new IC2_Matrix($cols, $rows, $i));
}

$flexy->setData('removedFiles', $removed_files);

// }}}
// {{{ output

// ���[�h�ʂ̍ŏI����
if ($_conf['ktai']) {
    $title = str_replace('ImageCache2', 'IC2', $ini['Viewer']['title']);
    $list_template = ($_conf['iphone']) ? 'iv2ip.tpl.html' : 'iv2i.tpl.html';
} else {
    switch ($mode) {
        case 2:
            $title = $ini['Manager']['title'];
            $list_template = 'iv2m.tpl.html';
            break;
        case 1:
            $title = $ini['Viewer']['title'];
            $list_template = 'iv2a.tpl.html';
            break;
        default:
            $title = $ini['Viewer']['title'];
            $list_template = 'iv2.tpl.html';
    }
}

// �t�H�[�����ŏI�������A�e���v���[�g�p�I�u�W�F�N�g�ɕϊ�
$r = new HTML_QuickForm_Renderer_ObjectFlexy($flexy);
//$r->setLabelTemplate('_label.tpl.html');
//$r->setHtmlTemplate('_html.tpl.html');
$qf->updateAttributes(array('method' => 'get')); // ���N�G�X�g��POST�ł��󂯓���邽�߁A�����ŕύX
/*if ($_conf['input_type_search']) {
    $input_type_search_attributes = array(
        'type' => 'search',
        'autosave' => 'rep2.expack.search.imgcache',
        'results' => '10',
        'placeholder' => '',
    );
    $qfe['key']->updateAttributes($input_type_search_attributes);
}*/
$qf->accept($r);
$qfObj = $r->toObject();

// �ϐ���Assign
$js = $qf->getValidationScript() . <<<EOJS
\n<script type="text/javascript">
// <![CDATA[
var ic2_cols = {$cols};
var ic2_rows = {$rows};
var ic2_lightbox_options = {
    no_loop: false,
    no_updown: false
};
p2BindReady(function(){
    var toolbar = document.getElementById('toolbar');
    var toolbarHeight = getCurrentStyle(toolbar).height;
    if (toolbarHeight == 'auto') {
        toolbarHeight = toolbar.clientHeight;
    } else {
        toolbarHeight = parsePixels(toolbarHeight);
    }
    document.getElementById('header').style.height = toolbarHeight + 'px';
}, null);
// ]]>
</script>\n
EOJS;
$flexy->setData('title', $title);
$flexy->setData('mode', $mode);
$flexy->setData('js', $js);
$flexy->setData('page', $page);
$flexy->setData('move', $qfObj);
$flexy->setData('lightbox', $lightbox);

// �y�[�W��\��
P2Util::header_nocache();
$flexy->compile($list_template);
if ($list_template == 'iv2ip.tpl.html') {
    if (!isset($prev_page)) {
        $prev_page = $page;
    }
    if (!isset($next_page)) {
        $next_page = $page;
    }
    $ll_autoactivate = empty($_GET['ll_autoactivate']) ? 'false' : 'true';
    $limelight_header = <<<EOP
<link rel="stylesheet" type="text/css" href="css/limelight.css?{$_conf['p2_version_id']}">
<script type="text/javascript" src="js/limelight.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript">
// <![CDATA[
window.addEventListener('DOMContentLoaded', function(event) {
    this.removeEventListener(event.type, arguments.callee, false);

    var limelight = new Limelight({ 'savable': true });
    var slide = limelight.bind();

    if ({$page} != {$prev_page}) {
        slide.onNoPrev = function(limelight, slide) {
            limelight.deactivate();
            window.location.href = 'iv2.php?page={$prev_page}&ll_autoactivate=1#bottom';
       };
    }

    if ({$page} != {$next_page}) {
        slide.onNoNext = function(limelight, slide) {
            limelight.deactivate();
            window.location.href = 'iv2.php?page={$next_page}&ll_autoactivate=1#top';
       };
    }

    if ({$ll_autoactivate}) {
        window.setTimeout(function(cursor) {
            limelight.activateSlide(slide, cursor);
        }, 100, (window.location.hash == '#bottom') ? -1 : 0);
    }
}, false);
// ]]>
</script>\n
EOP;
    $thumb_width = (int)$ini['Thumb1']['width'];
    $thumb_height = (int)$ini['Thumb1']['height'];
    $flexy->setData('thumb_width', $thumb_width);
    $flexy->setData('thumb_height', $thumb_height);
    $flexy->setData('title_width_v', 320 - (10 * 2) - $thumb_width);
    $flexy->setData('title_width_h', 480 - (10 * 2) - $thumb_width);
    $flexy->setData('info_vertical', $thumb_width > 80);
    $flexy->setData('limelight_header', $limelight_header);
    $flexy->output();
} elseif ($list_template == 'iv2i.tpl.html') {
    $mobile = Net_UserAgent_Mobile::singleton();
    $elements = $flexy->getElements();
    if ($mobile->isDoCoMo()) {
        $elements['page']->setAttributes('istyle="4"');
    } elseif ($mobile->isEZweb()) {
        $elements['page']->setAttributes('format="*N"');
    } elseif ($mobile->isSoftBank()) {
        $elements['page']->setAttributes('mode="numeric"');
    }
    $view = null;
    $flexy->outputObject($view, $elements);
} else {
    $flexy->output();
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
