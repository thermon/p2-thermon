<?php
/**
 * rep2expack - P2KeyValueStore�����b�v����
 * ���[�e�B���e�B�N���X�̂��߂̊�ꒊ�ۃN���X
 *
 * P2KeyValueStore��rep2�Ɉˑ������P�̂Ŏg���邪�A
 * P2DataStore��rep2�Ŏg�����߂ɐ݌v����Ă���B
 */

// {{{ AbstractDataStore

abstract class AbstractDataStore
{
    // {{{ properties

    /**
     * P2KeyValueStore�I�u�W�F�N�g��ێ�����A�z�z��
     *
     * @var array
     */
    static private $_kvs = array();

    // }}}
    // {{{ _getKVS()

    /**
     * �f�[�^��ۑ�����P2KeyValueStore�I�u�W�F�N�g���擾����
     *
     * @param string $databasePath
     * @param string $codec
     * @param string $tableName
     * @return P2KeyValueStore
     */
    static protected function _getKVS($databasePath,
                                      $codec = P2KeyValueStore::CODEC_SERIALIZING,
                                      $tableName = null)
    {
        global $_conf;

        $id = $codec . ':' . $databasePath;

        if (array_key_exists($id, self::$_kvs)) {
            return self::$_kvs[$id];
        }

        if (!file_exists($databasePath) && !is_dir(dirname($databasePath))) {
            FileCtl::mkdirFor($databasePath);
        }

        try {
            $kvs = P2KeyValueStore::getStore($databasePath, $codec, $tableName);
            self::$_kvs[$id] = $kvs;
        } catch (Exception $e) {
            p2die(get_class($e) . ': ' . $e->getMessage());
        }

        return $kvs;
    }

    // }}}
    // {{{ getKVS()

    /**
     * _getKVS() ���Ăяo����P2KeyValueStore�I�u�W�F�N�g���擾����
     *
     * ���݂� self::getKVS() �̓s���ł����艺�̃��\�b�h��
     * �T�u�N���X�ɃR�s�y�Ƃ����ێ琫���ɂ߂Ĉ��������ƂȂ��Ă���B
     * ������ PHP 5.3 ����ɂ��� static::getKVS() �ɕύX�������B
     *
     * @param void
     * @return P2KeyValueStore
     */
    abstract static public function getKVS();

    // }}}
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
