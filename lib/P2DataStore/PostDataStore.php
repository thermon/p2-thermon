<?php
/**
 * rep2expack - �������݃f�[�^�Ǘ��N���X
 */

// {{{ PostDataStore

class PostDataStore extends AbstractDataStore
{
    // {{{ getKVS()

    /**
     * �������݃f�[�^��ۑ�����P2KeyValueStore�I�u�W�F�N�g���擾����
     *
     * @param void
     * @return P2KeyValueStore
     */
    static public function getKVS()
    {
        return self::_getKVS($GLOBALS['_conf']['post_db_path'],
                             P2KeyValueStore::CODEC_ARRAYSHIFTJIS);
    }

    // }}}
    // {{{ AbstractDataStore.php ����̃R�s�y / PHP 5.3 �̒x���ÓI�������g���č폜������
    // {{{ get()

    /**
     * �f�[�^���擾����
     *
     * @param string $key
     * @return mixed
     * @see P2KeyValueStore::get()
     */
    static public function get($key)
    {
        return self::getKVS()->get($key);
            // static::getKVS()
    }

    // }}}
    // {{{ set()

    /**
     * �f�[�^��ۑ�����
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     * @see P2KeyValueStore::exists(),
     *      P2KeyValueStore::set(),
     *      P2KeyValueStore::update()
     */
    static public function set($key, $value)
    {
        $kvs = self::getKVS();
            // static::getKVS()
        if ($kvs->exists($key)) {
            return $kvs->update($key, $value);
        } else {
            return $kvs->set($key, $value);
        }
    }

    // }}}
    // {{{ delete()

    /**
     * �f�[�^���폜����
     *
     * @param string $key
     * @return bool
     * @see P2KeyValueStore::delete()
     */
    static public function delete($key)
    {
        return self::getKVS()->delete($key);
            // static::getKVS()
    }

    // }}}
    // {{{ clear()

    /**
     * ���ׂẴf�[�^�܂��̓L�[���w�肳�ꂽ�ړ����Ŏn�܂�f�[�^���폜����
     *
     * @param string $prefix
     * @return int
     * @see P2KeyValueStore::clear()
     */
    static public function clear($prefix = null)
    {
        return self::getKVS()->clear($prefix);
            // static::getKVS();
    }

    // }}}
    // }}} �R�s�y�����܂�
    // {{{ getKeyForBackup()

    /**
     * �������݃o�b�N�A�b�v�̂��߂̃L�[���擾����
     *
     * @param string $host
     * @param string $bbs
     * @param numeric $key
     * @param bool $newthread
     */
    static public function getKeyForBackup($host, $bbs, $key, $newthread = false)
    {
        if ($newthread) {
            $key = 'new';
        }
        return 'backup:' . self::_getKeySuffix($host, $bbs, $key);
    }

    // }}}
    // {{{ getKeyForConfig()

    /**
     * ��/�X�����Ƃ̏������ݐݒ�̂��߂̃L�[���擾����
     *
     * @param string $host
     * @param string $bbs
     * @param numeric $key
     * @param bool $newthread
     */
    static public function getKeyForConfig($host, $bbs, $key = null)
    {
        if ($key === null) {
            $key = '';
        }
        return 'config:' . self::_getKeySuffix($host, $bbs, $key);
    }

    // }}}
    // {{{ _getKeySuffix()

    /**
     * �L�[�̐ڔ����𐶐�����
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @param bool $newthread
     */
    static private function _getKeySuffix($host, $bbs, $key)
    {
        global $_login;

        return rtrim($_login->user_u . P2Util::pathForHostBbs($host, $bbs) . $key, '/');
    }

    // }}}
    // {{{ clearBackup()

    /**
     * ���ׂĂ̏������݃o�b�N�A�b�v�܂���
     * �w�肳�ꂽ���[�U�[�̏������݃o�b�N�A�b�v���폜����
     *
     * @param string $user
     * @return int
     * @see AbstractDataStore::clear()
     */
    static public function clearBackup($user = null)
    {
        $prefix = 'backup:';
        if ($user !== null) {
            $prefix .= $user . '/';
        }
        return self::clear($prefix);
    }

    // }}}
    // {{{ clearConfig()

    /**
     * ���ׂĂ̏������ݐݒ�܂��͎w�肳�ꂽ���[�U�[�̏������ݐݒ���폜����
     *
     * @param string $user
     * @return int
     * @see AbstractDataStore::clear()
     */
    static public function clearConfig($user = null)
    {
        $prefix = 'config:';
        if ($user !== null) {
            $prefix .= $user . '/';
        }
        return self::clear($prefix);
    }

    // }}}
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
