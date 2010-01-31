<?php
/**
 * rep2expack - �o�[�W�����A�b�v���̈ڍs�x��
 */

// {{{ p2_migrate_100113_1300()

/**
 * rev.100113.1300
 *
 * @param array $core_config rep2�R�A�̐ݒ�
 * @param array $user_config �Â����[�U�[�ݒ�
 * @return array �V�������[�U�[�ݒ�
 */
function p2_migrate_100113_1300(array $core_config, array $user_config)
{
    $data_dir   = $core_config['data_dir'];
    $db_dir     = $core_config['db_dir'];
    $cache_dir  = $core_config['cache_dir'];
    $cookie_dir = $core_config['cookie_dir'];

    // {{{ �z�X�g�`�F�b�N��gethostbyaddr()�L���b�V��

    $old_hostcheck_db = $cache_dir . '/hostcheck_gethostby.sq3';
    $new_hostcheck_db = $core_config['hostcheck_db_path'];

    _100113_1300_rename_db($old_hostcheck_db, $new_hostcheck_db);
    _100113_1300_rename_table($new_hostcheck_db, 'kvs_p2keyvaluestore', 'kvs_default');
    _100113_1300_rename_table($new_hostcheck_db, 'kvs_keyvaluestore', 'kvs_default');

    // }}}
    // {{{ ����p2�N���C�A���g��Cookie�X�g���[�W

    $old_p2_cookie_db1 = $cookie_dir . '/p2_2ch_net_cookies.sqlite3';
    $old_p2_cookie_db2 = $cookie_dir . '/p2_2ch_net_cookie.sq3';
    $new_p2_cookie_db = $db_dir . '/p2_2ch_net_cookies.sqlite3';

    _100113_1300_rename_db($old_p2_cookie_db1, $new_p2_cookie_db);
    _100113_1300_rename_db($old_p2_cookie_db2, $new_p2_cookie_db);
    _100113_1300_rename_table($new_p2_cookie_db, 'kvs_p2keyvaluestore_serializing', 'kvs_serializing');

    // }}}
    // {{{ ���e�pCookie�X�g���[�W

    $old_cookie_db = $cookie_dir . '/p2_cookies.sqlite3';
    $new_cookie_db = $core_config['cookie_db_path'];

    _100113_1300_rename_db($old_cookie_db, $new_cookie_db);
    _100113_1300_rename_table($new_cookie_db, 'kvs_p2keyvaluestore_serializing', 'kvs_serializing');

    // }}}
    // {{{ �������݃f�[�^�̃o�b�N�A�b�v�X�g���[�W

    $old_post_db = $cookie_dir . '/p2_post_data.sqlite3';
    $new_post_db = $core_config['post_db_path'];

    _100113_1300_rename_db($old_post_db, $new_post_db);
    _100113_1300_rename_table($new_post_db, 'kvs_p2keyvaluestore_serializing', 'kvs_serializing');

    // }}}

    return $user_config;
}

// }}}
// {{{ _100113_1300_rename_db

/**
 * SQLite3�f�[�^�x�[�X�����l�[������
 *
 * @param string $old_name
 * @param string $new_name
 * @return void
 */
function _100113_1300_rename_db($old_name, $new_name)
{
    if (DIRECTORY_SEPARATOR != '/') {
        $old_name = str_replace('/', DIRECTORY_SEPARATOR, $old_name);
        $new_name = str_replace('/', DIRECTORY_SEPARATOR, $new_name);
    }

    if ($old_name == $new_name) {
        return;
    }

    if (file_exists($old_name)) {
        if (file_exists($new_name)) {
            unlink($old_name);
        } else {
            $dir = dirname($new_name);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            rename($old_name, $new_name);
        }
        clearstatcache();
    }
}

// }}}
// {{{ _100113_1300_rename_table

/**
 * SQLite3�e�[�u�������l�[������
 *
 * @param string $database
 * @param string $old_name
 * @param string $new_name
 * @return void
 */
function _100113_1300_rename_table($database, $old_name, $new_name)
{
    if (!file_exists($database) || $old_name == $new_name) {
        return;
    }

    $pdo = new PDO('sqlite:' . realpath($database));
    $tableChecker = $pdo->prepare('SELECT 1 FROM sqlite_master WHERE type = \'table\' AND name = :name LIMIT 1');

    $tableChecker->bindValue(':name', $old_name);
    $tableChecker->execute();
    if ($tableChecker->fetchColumn()) {
        $tableChecker->closeCursor();
        $tableChecker->bindValue(':name', $new_name);
        $tableChecker->execute();
        if ($tableChecker->fetchColumn()) {
            $tableChecker->closeCursor();
            $query = 'DROP TABLE ' . _100113_1300_quote_identifier($old_name);
        } else {
            $query = 'ALTER TABLE ' . _100113_1300_quote_identifier($old_name)
                   . ' RENAME TO ' . _100113_1300_quote_identifier($new_name);
        }
        unset($tableChecker);
        $pdo->exec($query);
    }
}

// }}}
// {{{ _100113_1300_quote_identifier

/**
 * SQLite3�e�[�u�������N�H�[�g����
 *
 * @param string $identifier
 * @return string
 */
function _100113_1300_quote_identifier($identifier)
{
    return '"' . str_replace('"', '""', $identifier) . '"';
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
