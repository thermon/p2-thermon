<?php
require_once dirname(__FILE__) . '/KeyValueStore.php';

// {{{ BinaryStore

/**
 * �o�C�i���f�[�^���i��������
 */
class BinaryStore extends KeyValueStore
{
    // {{{ _encodeValue()

    /**
     * �f�[�^��Base64�G���R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _encodeValue($value)
    {
        return base64_encode($value);
    }

    // }}}
    // {{{ _decodeValue()

    /**
     * �f�[�^��Base64�f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _decodeValue($value)
    {
        return base64_decode($value);
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
