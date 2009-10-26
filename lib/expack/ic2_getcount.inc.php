<?php
/**
 * ImageCache2 - �������猏�����擾����
 */

function getIC2ImageCount($key, $threshold = null) {
    require_once 'DB.php';
    require_once 'DB/DataObject.php';
    require_once P2EX_LIB_DIR . '/ic2/loadconfig.inc.php';
    require_once P2EX_LIB_DIR . '/ic2/DataObject/Common.php';
    require_once P2EX_LIB_DIR . '/ic2/DataObject/Images.php';
    // �ݒ�t�@�C���ǂݍ���
    $ini = ic2_loadconfig();

    $icdb = new IC2_DataObject_Images;
    // 臒l�Ńt�B���^�����O
    if ($threshold === null) $threshold = $ini['Viewer']['threshold'];
    if (!($threshold == -1)) {
        $icdb->whereAddQuoted('rank', '>=', $threshold);
    }

    $db = $icdb->getDatabaseConnection();
    $db_class = strtolower(get_class($db));
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
        $icdb->whereAddQuoted('memo', $operator, $expr);
    }

    $sql = sprintf('SELECT COUNT(*) FROM %s %s', $db->quoteIdentifier($ini['General']['table']), $icdb->_query['condition']);
    $all = $db->getOne($sql);
    if (DB::isError($all)) {
        p2die($all->getMessage());
    }
    return $all;
}

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
