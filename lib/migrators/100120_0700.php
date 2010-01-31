<?php
/**
 * rep2expack - �o�[�W�����A�b�v���̈ڍs�x��
 */

// {{{ p2_migrate_100120_0700()

/**
 * �s�v�ɂȂ����V���܂Ƃߓǂ݃L���b�V���t�@�C�����폜���A
 * �������݃f�[�^�̕ۑ��`����ύX����B
 *
 * @param array $core_config rep2�R�A�̐ݒ�
 * @param array $user_config �Â����[�U�[�ݒ�
 * @return array �V�������[�U�[�ݒ�
 */
function p2_migrate_100120_0700(array $core_config, array $user_config)
{
    _100120_0700_unlink_matome_caches($core_config['pref_dir']);
    _100120_0700_convert_post_data_store($core_config['post_db_path']);

    return $user_config;
}

// }}}
// {{{ _100120_0700_unlink_matome_caches()

/**
 * �s�v�ɂȂ����V���܂Ƃߓǂ݃L���b�V���t�@�C�����폜����
 *
 * @param string $pattern
 * @return void
 */
function _100120_0700_unlink_matome_caches($pref_dir)
{
    if (is_dir($pref_dir)) {
        $current_dir = getcwd();
        if ($current_dir === false) {
            $current_dir = P2_BASE_DIR;
        }
        if (chdir($pref_dir)) {
            _100120_0700_glob_unlink('./matome_cache.htm');
            _100120_0700_glob_unlink('./matome_cache.*.htm');
            _100120_0700_glob_unlink('./matome_cache.*.lck');
            chdir($current_dir);
        }
    }
}

// }}}
// {{{ _100120_0700_glob_unlink()

/**
 * glob()�Ō��������t�@�C�����폜����
 *
 * @param string $pattern
 * @return void
 */
function _100120_0700_glob_unlink($pattern)
{
    if ($files = glob($pattern, GLOB_NOSORT)) {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// }}}
// {{{ _100120_0700_convert_post_data_store()

/**
 * �������݃f�[�^�̕ۑ��`����ύX����
 *
 * @param string $post_db_path
 * @return void
 */
function _100120_0700_convert_post_data_store($post_db_path)
{
    if (!file_exists($post_db_path)) {
        return;
    }

    $oldKvs = P2KeyValueStore::getStore($post_db_path,
                                        P2KeyValueStore::CODEC_SERIALIZING);
    $newKvs = PostDataStore::getKVS();

    foreach ($oldKvs as $key => $value) {
        $newKvs->set($key, $value);
    }

    if ($oldKvs->getTableName() != $newKvs->getTableName()) {
        $oldKvs->prepare('DROP TABLE $__table')->execute();
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
