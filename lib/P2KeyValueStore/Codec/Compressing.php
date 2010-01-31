<?php

// {{{ P2KeyValueStore_Codec_Compressing

/**
 * �T�C�Y�̑傫���f�[�^�����k����Codec
 */
class P2KeyValueStore_Codec_Compressing extends P2KeyValueStore_Codec_Binary
{
    // {{{ encodeValue()

    /**
     * �f�[�^�����k����
     *
     * @param string $value
     * @return string
     */
    public function encodeValue($value)
    {
        return parent::encodeValue(gzdeflate($value, 6));
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �f�[�^��W�J����
     *
     * @param string $value
     * @return string
     */
    public function decodeValue($value)
    {
        return gzinflate(parent::decodeValue($value));
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
