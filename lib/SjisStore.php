<?php
require_once dirname(__FILE__) . '/KeyValueStore.php';

// {{{ SjisStore

/**
 * Shift_JIS�̕������UTF-8�ɕϊ����ĉi��������
 */
class SjisStore extends KeyValueStore
{
    // {{{ _encode()

    /**
     * Shift_JIS (CP932) �̕������UTF-8�ɕϊ�����
     *
     * @param string $str
     * @return string
     */
    private function _encode($str)
    {
        return mb_convert_encoding($str, 'UTF-8', 'CP932');
    }

    // }}}
    // {{{ _decode()

    /**
     * UTF-8�̕������Shift_JIS (CP932) �ɕϊ�����
     *
     * @param string $str
     * @return string
     */
    private function _decode($str)
    {
        return mb_convert_encoding($str, 'CP932', 'UTF-8');
    }

    // }}}
    // {{{ _encodeKey()

    /**
     * �L�[���G���R�[�h����
     *
     * @param string $key
     * @return string
     */
    protected function _encodeKey($key)
    {
        return $this->_encode($key);
    }

    // }}}
    // {{{ _decodeKey()

    /**
     * �L�[���f�R�[�h����
     *
     * @param string $key
     * @return string
     */
    protected function _decodeKey($key)
    {
        return $this->_decode($key);
    }

    // }}}
    // {{{ _encodeValue()

    /**
     * �l���G���R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _encodeValue($value)
    {
        return $this->_encode($value);
    }

    // }}}
    // {{{ _decodeValue()

    /**
     * �l���f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    protected function _decodeValue($value)
    {
        return $this->_decode($value);
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
