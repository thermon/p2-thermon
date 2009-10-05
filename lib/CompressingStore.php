<?php
require_once dirname(__FILE__) . '/BinaryStore.php';

// {{{ CompressingStore

/**
 * �T�C�Y�̑傫���f�[�^�����k���ĉi��������
 */
class CompressingStore extends BinaryStore
{
    // {{{ _encodeValue()

    /**
     * �f�[�^�����k����
     *
     * @param string $value
     * @return string
     */
    protected function _encodeValue($value)
    {
        return parent::_encodeValue(gzdeflate($value, 6));
    }

    // }}}
    // {{{ _decodeValue()

    /**
     * �f�[�^��W�J����
     *
     * @param string $value
     * @return string
     */
    protected function _decodeValue($value)
    {
        return gzinflate(parent::_decodeValue($value));
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
